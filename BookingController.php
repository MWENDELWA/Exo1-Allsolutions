//namespace App\Http\Controllers;

    use App\Models\Room;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;

    class RoomController extends Controller
    {
        public function index()
        {
            $rooms = Room::all();
            return response()->json($rooms);
        }

        public function show(Room $room)
        {
            return response()->json($room);
        }
    }
    ```

* `BookingController.php` :
    ```php
    namespace App\Http\Controllers;

    use App\Models\Booking;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;

    class BookingController extends Controller
    {
        public function index()
        {
            $bookings = Booking::where('user_id', auth()->id())->with('room')->orderBy('start_time')->get();
            return response()->json($bookings);
        }

        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'room_id' => 'required|exists:rooms,id',
                'start_time' => 'required|date|after_or_equal:now',
                'end_time' => 'required|date|after:start_time',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            // Vérifier la disponibilité
            $isBooked = Booking::where('room_id', $request->room_id)
                ->where(function ($query) use ($request) {
                    $query->whereBetween('start_time', [$request->start_time, $request->end_time])
                        ->orWhereBetween('end_time', [$request->start_time, $request->end_time])
                        ->orWhere(function ($q) use ($request) {
                            $q->where('start_time', '<=', $request->start_time)
                                ->where('end_time', '>=', $request->end_time);
                        });
                })
                ->exists();

            if ($isBooked) {
                return response()->json(['message' => 'La salle n\'est pas disponible pour cette période.'], 409);
            }

            $booking = Booking::create([
                'user_id' => auth()->id(),
                'room_id' => $request->room_id,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ]);

            return response()->json($booking, 201);
        }

        public function availability(Request $request, Room $room)
        {
            $validator = Validator::make($request->all(), [
                'start_date' => 'required|date',
                'end_date' => 'required|date|after_or_equal:start_date',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $bookings = Booking::where('room_id', $room->id)
                ->where('start_time', '<', $request->end_date . ' 23:59:59')
                ->where('end_time', '>', $request->start_date . ' 00:00:00')
                ->get();

            return response()->json($bookings);
        }

        public function destroy(Booking $booking)
        {
            if ($booking->user_id !== auth()->id()) {
                return response()->json(['message' => 'Non autorisé.'], 403);
            }
            $booking->delete();
            return response()->json(['message' => 'Réservation annulée avec succès.']);
        }
    }
    ```
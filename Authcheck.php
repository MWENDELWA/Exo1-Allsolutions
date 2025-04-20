se Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthCheck
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Non authentifié.'], 401);
        }
        return $next($request);
    }
}
```
Enregistrez le middleware dans `app/Http/Kernel.php` :
```php
protected $routeMiddleware = [
    'auth.api' => \App\Http\Middleware\AuthCheck::class,
    // ... autres middlewares
];
```
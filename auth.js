import axios from 'axios';

const API_BASE_URL = import.meta.env.VITE_API_BASE_URL;

const api = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'Content-Type': 'application/json',
    },
});

api.interceptors.request.use(config => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

api.interceptors.response.use(
    response => response,
    error => {
        // Gestion globale des erreurs (peut être affiné par composant)
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('token');
            localStorage.removeItem('user');
            router.push('/login'); // Rediriger vers la page de connexion
        }
        return Promise.reject(error);
    }
);

export const register = async (userData) => {
    try {
        const response = await api.post('/register', userData);
        return response.data;
    } catch (error) {
        throw error.response ? error.response.data : error;
    }
};

export const login = async (credentials) => {
    try {
        const response = await api.post('/login', credentials);
        localStorage.setItem('token', response.data.access_token);
        return response.data;
    } catch (error) {
        throw error.response ? error.response.data : error;
    }
};

export const logout = async () => {
    try {
        await api.post('/logout');
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    } catch (error) {
        console.error('Erreur lors de la déconnexion:', error);
        throw error.response ? error.response.data : error;
    }
};

export const getMe = async () => {
    try {
        const response = await api.get('/me');
        localStorage.setItem('user', JSON.stringify(response.data));
        return response.data;
    } catch (error) {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
        throw error.response ? error.response.data : error;
    }
};

export default api;
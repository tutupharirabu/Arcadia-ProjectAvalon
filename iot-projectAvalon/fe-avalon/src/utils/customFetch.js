import axios from 'axios';

const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api/v1';
const NODE_URL = import.meta.env.VITE_NODE_URL || 'http://localhost:3000';

// Baca token dari localStorage (toleran terhadap data korup / akses diblokir)
const getToken = () => {
    try {
        const raw = localStorage.getItem('token');
        if (!raw) return null;
        try {
            return JSON.parse(raw);
        } catch {
            return raw;
        }
    } catch (error) {
        console.warn('Gagal membaca token dari localStorage:', error);
        return null;
    }
};

const clearStoredSession = () => {
    try {
        localStorage.removeItem('token');
        localStorage.removeItem('user');
    } catch (error) {
        console.warn('Gagal membersihkan sesi dari localStorage:', error);
    }
};

const redirectToLogin = () => {
    if (window.location.pathname !== '/monitoring-arcadia/login') {
        window.location.assign('/monitoring-arcadia/login');
    }
};

// Endpoint auth yang TIDAK memerlukan token (401 di sini tidak boleh redirect login)
const isPublicAuthUrl = (url) =>
    url.includes('/auth/login') ||
    url.includes('/auth/register') ||
    url.includes('/auth/forgot-password');

// Inject Authorization: Bearer <token> otomatis dari localStorage
const authRequestInterceptor = (config) => {
    const token = getToken();
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
};

const customFetch = axios.create({
    baseURL: API_URL,
    timeout: 15000,
});

customFetch.interceptors.request.use(authRequestInterceptor);

customFetch.interceptors.response.use(
    (response) => response,
    (error) => {
        const status = error.response?.status;
        const url = error.config?.url || '';

        // 401 pada endpoint non-publik → sesi tidak valid/expired → bersihkan & redirect ke login
        if (status === 401 && !error.config?.skipAuthRedirect && !isPublicAuthUrl(url)) {
            clearStoredSession();
            redirectToLogin();
        }

        return Promise.reject(error);
    }
);

// Instance terpusat untuk node server monitoring
const nodeFetch = axios.create({
    baseURL: NODE_URL,
    timeout: 15000,
});

nodeFetch.interceptors.request.use(authRequestInterceptor);

export { nodeFetch };
export default customFetch;

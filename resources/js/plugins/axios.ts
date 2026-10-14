import axios from 'axios';

// Base URL set to /api for local dev flexibility across ports and Herd
const apiClient = axios.create({
    baseURL: '/api',
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
});

// Request Interceptor: Attach bearer token from localStorage
apiClient.interceptors.request.use(
    (config) => {
        const token = localStorage.getItem('access_token');
        if (token) {
            config.headers.Authorization = `Bearer ${token}`;
        }
        return config;
    },
    (error) => Promise.reject(error),
);

// Response Interceptor: Handle 401 Unauthorized
apiClient.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response && error.response.status === 401) {
            localStorage.removeItem('access_token');
            localStorage.removeItem('user_data');
            // If current route requires auth, redirect to login
            if (
                window.location.pathname !== '/login' &&
                window.location.pathname !== '/register'
            ) {
                window.location.href = '/login';
            }
        }
        return Promise.reject(error);
    },
);

export default apiClient;

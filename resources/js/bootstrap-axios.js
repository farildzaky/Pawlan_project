import axios from 'axios';

axios.defaults.baseURL = '/api';
axios.defaults.headers.common['Accept'] = 'application/json';

axios.interceptors.request.use((config) => {
    const token = localStorage.getItem('fitflow_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

axios.interceptors.response.use(
    (res) => res,
    (err) => {
        if (err.response && err.response.status === 401) {
            localStorage.removeItem('fitflow_token');
            localStorage.removeItem('fitflow_user');
            if (window.location.pathname !== '/login' && window.location.pathname !== '/register') {
                window.location.href = '/login';
            }
        }
        return Promise.reject(err);
    }
);

export default axios;

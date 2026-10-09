import axios from 'axios';

const client = axios.create({
    baseURL: '/api/v1',
    headers: { Accept: 'application/json' },
});

client.interceptors.request.use((config) => {
    const token = localStorage.getItem('kampuslms_token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export function setToken(token) {
    if (token) {
        localStorage.setItem('kampuslms_token', token);
    } else {
        localStorage.removeItem('kampuslms_token');
    }
}

export function getErrorMessage(error) {
    return error.response?.data?.message
        ?? Object.values(error.response?.data?.errors ?? {}).flat()[0]
        ?? error.message
        ?? 'Terjadi kesalahan. Silakan coba lagi.';
}

export default client;

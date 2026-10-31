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
    if (error.response?.status === 413) {
        return 'Ukuran file terlalu besar dan melebihi batas maksimal server.';
    }

    const firstFieldError = Object.values(error.response?.data?.errors ?? {}).flat()[0];
    if (firstFieldError) {
        return firstFieldError;
    }

    return error.response?.data?.message
        ?? error.message
        ?? 'Terjadi kesalahan. Silakan coba lagi.';
}

export default client;

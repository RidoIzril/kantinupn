import './bootstrap';
import '../css/app.css';
import axios from 'axios';

window.axios = axios;

axios.defaults.baseURL = 'http://127.0.0.1:8000';

axios.interceptors.request.use(function (config) {

    const token = localStorage.getItem('token');

    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }

    return config;
});
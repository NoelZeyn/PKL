import './bootstrap';
import { createApp } from 'vue';
import App from './App.vue';
import router from './router';
import '../css/app.css';
import axios from 'axios';

// Interceptor global: redirect ke login kalau token habis / invalid
axios.interceptors.response.use(
  response => response,
  error => {
    if (error.response && error.response.status === 401) {
      localStorage.clear();
      sessionStorage.clear();
      router.push('/login');
    }
    return Promise.reject(error);
  }
);

const app = createApp(App);
app.use(router);
app.mount('#app');

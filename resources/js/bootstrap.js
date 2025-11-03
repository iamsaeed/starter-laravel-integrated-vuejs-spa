import axios from 'axios';
import { useToast } from '@/composables/useToast';

window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.headers.common['Accept'] = 'application/json';

// Request interceptor to add auth token
window.axios.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token');
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  },
  (error) => {
    return Promise.reject(error);
  }
);

// Response interceptor for global error handling and success messages
window.axios.interceptors.response.use(
  (response) => {
    // Show success toast if response contains a message
    if (response.data?.message) {
      const toast = useToast();
      toast.success(response.data.message);
    }
    return response;
  },
  (error) => {
    // Handle 401 Unauthorized
    if (error.response?.status === 401) {
      // Clear auth state
      localStorage.removeItem('auth_token');
      delete window.axios.defaults.headers.common['Authorization'];

      // Redirect to login if not already on login page
      if (window.location.pathname !== '/admin') {
        window.location.href = '/admin';
      }
      return Promise.reject(error);
    }

    // Show toast notifications for other errors
    const toast = useToast();
    const message = error.response?.data?.message
      || error.response?.data?.error
      || error.message
      || 'An error occurred';

    const status = error.response?.status;

    if (status >= 500) {
      // Server errors
      toast.error(message);
    } else if (status >= 400 && status < 500) {
      // Client errors (validation, not found, etc.)
      toast.warning(message);
    } else if (!error.response) {
      // Network errors
      toast.error('Network error. Please check your connection.');
    }

    return Promise.reject(error);
  }
);

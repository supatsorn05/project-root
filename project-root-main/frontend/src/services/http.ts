import axios from 'axios';

const http = axios.create({
  baseURL: '/api',
  withCredentials: true,
});

// Add a request interceptor to inject the token
http.interceptors.request.use(config => {
  const token = localStorage.getItem('token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
}, error => {
  return Promise.reject(error);
});

import { setToken } from '@/composables/useAuth';

// Add a response interceptor to handle auth errors
http.interceptors.response.use(
  (response) => response,
  (error) => {
    // Check if the error is because of an invalid token
    if (error.response && [401, 403].includes(error.response.status)) {
      // The token is invalid or expired. Clear it.
      // This will trigger reactive changes in the app, and the router guard
      // will then redirect to the login page.
      console.log('Authentication error detected. Clearing token.');
      setToken(null);
    }
    return Promise.reject(error);
  }
);

export default http;

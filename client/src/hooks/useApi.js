// src/hooks/useApi.js
import axios from 'axios';
import { useAuth } from '../contexts/AuthContext';

export const useApi = () => {
  const { token } = useAuth();
  console.log('tokenuseAPi',token)

  const instance = axios.create({
    baseURL: 'http://127.0.0.1:8000/api/v1',
    timeout: 10000,
  });

  instance.interceptors.request.use((config) => {
    if (token) {
      config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
  });

  return instance;
};

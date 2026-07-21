import axios from 'axios';
import { auth } from './firebase';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
  },
});

// Interceptor: adiciona token de autenticacao em todas as requisicoes
api.interceptors.request.use(async (config) => {
  const user = auth.currentUser;
  if (user) {
    const token = await user.getIdToken();
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Interceptor: trata erros 401 (token expirado)
api.interceptors.response.use(
  (response) => response,
  async (error) => {
    if (error.response?.status === 401) {
      const user = auth.currentUser;
      if (user) {
        // Tenta renovar o token
        await user.getIdToken(true);
        // Re-tenta a requisicao original
        const token = await user.getIdToken();
        error.config.headers.Authorization = `Bearer ${token}`;
        return api(error.config);
      }
    }
    return Promise.reject(error);
  },
);

export default api;

// ─── Auth API ───

export async function syncUser() {
  const user = auth.currentUser;
  if (!user) throw new Error('Usuario nao autenticado');
  const token = await user.getIdToken();
  const { data } = await api.post('/auth/sync', { token });
  return data;
}

export async function getMe() {
  const { data } = await api.post('/auth/me');
  return data;
}

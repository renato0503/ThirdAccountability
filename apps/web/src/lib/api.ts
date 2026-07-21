import axios from 'axios';
import { auth } from './firebase';

const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL || '/api',
  headers: {
    'Content-Type': 'application/json',
  },
  timeout: 8000,
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
        await user.getIdToken(true);
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
  if (!user) return null;

  const token = await user.getIdToken();

  try {
    const { data } = await api.post('/auth/sync', { token });
    return data;
  } catch {
    // API nao disponivel — login funciona apenas com Firebase Auth
    // O documento users/{uid} sera criado quando a API estiver no ar
    return null;
  }
}

export async function getMe() {
  try {
    const { data } = await api.post('/auth/me');
    return data;
  } catch {
    return null;
  }
}

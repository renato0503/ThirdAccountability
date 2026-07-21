import { create } from 'zustand';
import { auth } from '@/lib/firebase';
import { onAuthStateChanged, User } from 'firebase/auth';

interface AuthState {
  user: User | null;
  loading: boolean;
  initialized: boolean;
  setUser: (user: User | null) => void;
  setLoading: (loading: boolean) => void;
}

export const useAuthStore = create<AuthState>((set) => ({
  user: null,
  loading: true,
  initialized: false,
  setUser: (user) => set({ user, loading: false, initialized: true }),
  setLoading: (loading) => set({ loading }),
}));

// Inicializa o listener de auth fora do componente
onAuthStateChanged(auth, (user) => {
  useAuthStore.getState().setUser(user);
});

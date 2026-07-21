import { useState, useEffect } from 'react';
import {
  signInWithEmailAndPassword,
  createUserWithEmailAndPassword,
  signOut,
  sendPasswordResetEmail,
  GoogleAuthProvider,
  signInWithRedirect,
  getRedirectResult,
  updateProfile,
} from 'firebase/auth';
import { auth } from '@/lib/firebase';
import { useAuthStore } from '@/stores/auth-store';
import { syncUser } from '@/lib/api';

export function useAuth() {
  const { user, loading, initialized } = useAuthStore();
  const [error, setError] = useState<string | null>(null);

  const clearError = () => setError(null);

  // Handle redirect result (Google login)
  useEffect(() => {
    getRedirectResult(auth).then(async (result) => {
      if (result?.user) {
        syncUser().catch(() => {});
      }
    }).catch(() => {});
  }, []);

  const doSync = () => syncUser().catch(() => {});

  const login = async (email: string, password: string) => {
    try {
      setError(null);
      const result = await signInWithEmailAndPassword(auth, email, password);
      doSync();
      return result.user;
    } catch (err: any) {
      setError(getFirebaseErrorMessage(err.code));
      throw err;
    }
  };

  const register = async (email: string, password: string, name: string) => {
    try {
      setError(null);
      const result = await createUserWithEmailAndPassword(auth, email, password);
      await updateProfile(result.user, { displayName: name });
      doSync();
      return result.user;
    } catch (err: any) {
      setError(getFirebaseErrorMessage(err.code));
      throw err;
    }
  };

  const loginWithGoogle = async () => {
    try {
      setError(null);
      const provider = new GoogleAuthProvider();
      await signInWithRedirect(auth, provider);
    } catch (err: any) {
      setError(getFirebaseErrorMessage(err.code));
      throw err;
    }
  };

  const logout = async () => {
    await signOut(auth);
  };

  const resetPassword = async (email: string) => {
    try {
      setError(null);
      await sendPasswordResetEmail(auth, email);
    } catch (err: any) {
      setError(getFirebaseErrorMessage(err.code));
      throw err;
    }
  };

  return {
    user, loading, initialized, error, clearError,
    login, register, loginWithGoogle, logout, resetPassword,
  };
}

function getFirebaseErrorMessage(code: string): string {
  const map: Record<string, string> = {
    'auth/user-not-found': 'Usuario nao encontrado',
    'auth/wrong-password': 'Senha incorreta',
    'auth/invalid-credential': 'Email ou senha invalidos',
    'auth/email-already-in-use': 'Email ja esta em uso',
    'auth/weak-password': 'Senha deve ter no minimo 6 caracteres',
    'auth/invalid-email': 'Email invalido',
    'auth/too-many-requests': 'Muitas tentativas. Tente novamente mais tarde',
    'auth/network-request-failed': 'Erro de conexao. Verifique sua internet',
  };
  return map[code] || 'Erro de autenticacao. Tente novamente.';
}

import { Navigate } from 'react-router-dom';
import { useAuthStore } from '@/stores/auth-store';
import { Skeleton } from '@/components/ui/skeleton';

interface ProtectedRouteProps {
  children: React.ReactNode;
  requiredRole?: string[];
}

export function ProtectedRoute({ children }: ProtectedRouteProps) {
  const { user, loading } = useAuthStore();

  if (loading) {
    return (
      <div className="flex h-screen items-center justify-center">
        <div className="space-y-4">
          <Skeleton className="h-12 w-64" />
          <Skeleton className="h-4 w-48" />
        </div>
      </div>
    );
  }

  if (!user) {
    return <Navigate to="/login" replace />;
  }

  // TODO: Verificar papel do usuario quando implementado
  // if (requiredRole && !requiredRole.includes(user.role)) {
  //   return <Navigate to="/dashboard" replace />;
  // }

  return <>{children}</>;
}

import { createBrowserRouter, Navigate } from 'react-router-dom';
import { AppLayout } from '@/components/layout/app-layout';
import { ProtectedRoute } from '@/components/shared/protected-route';
import { LoginPage } from '@/features/auth/login-page';
import { RegisterPage } from '@/features/auth/register-page';
import { ForgotPasswordPage } from '@/features/auth/forgot-password-page';
import { DashboardPage } from '@/features/dashboard/dashboard-page';
import { ProfilePage } from '@/features/profile/profile-page';
import { UsersPage } from '@/features/users/users-page';
import { InstitutionsListPage } from '@/features/institutions/institutions-list-page';
import { InstitutionFormPage } from '@/features/institutions/institution-form-page';
import { InstitutionDetailPage } from '@/features/institutions/institution-detail-page';

export const router = createBrowserRouter([
  {
    path: '/login',
    element: <LoginPage />,
  },
  {
    path: '/register',
    element: <RegisterPage />,
  },
  {
    path: '/forgot-password',
    element: <ForgotPasswordPage />,
  },
  {
    path: '/',
    element: (
      <ProtectedRoute>
        <AppLayout />
      </ProtectedRoute>
    ),
    children: [
      { index: true, element: <Navigate to="/dashboard" replace /> },
      { path: 'dashboard', element: <DashboardPage /> },
      { path: 'perfil', element: <ProfilePage /> },
      { path: 'usuarios', element: <UsersPage /> },
      { path: 'instituicoes', element: <InstitutionsListPage /> },
      { path: 'instituicoes/nova', element: <InstitutionFormPage /> },
      { path: 'instituicoes/:id', element: <InstitutionDetailPage /> },
      { path: 'instituicoes/:id/editar', element: <InstitutionFormPage /> },
      {
        path: 'projetos',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Modulo de Projetos — Sprint 4</div>,
      },
      {
        path: 'projetos/novo',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Novo Projeto — Sprint 4</div>,
      },
      {
        path: 'projetos/:id',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Detalhes do Projeto — Sprint 4</div>,
      },
      {
        path: 'financeiro',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Modulo Financeiro — Sprint 5</div>,
      },
      {
        path: 'documentos',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Modulo de Documentos — Sprint 6</div>,
      },
      {
        path: 'diligencias',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Modulo de Diligencias — Sprint 6</div>,
      },
      {
        path: 'prestacao-contas',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Modulo de Prestacao de Contas — Sprint 6</div>,
      },
      {
        path: 'pesquisa-precos',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Pesquisa de Precos — Sprint 7</div>,
      },
      {
        path: 'pesquisa-precos/chat-ia',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Chat IA (Cotacao) — Sprint 7</div>,
      },
      {
        path: 'auditoria',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Auditoria — Sprint 8</div>,
      },
      {
        path: 'relatorios',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Relatorios — Sprint 8</div>,
      },
      {
        path: 'configuracoes',
        element: <div className="flex items-center justify-center h-full text-muted-foreground">Configuracoes — Sprint 8</div>,
      },
    ],
  },
]);

import { useState, useEffect } from 'react';
import api from '@/lib/api';
import { useAuth } from '@/hooks/use-auth';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { toast } from '@/hooks/use-toast';
import { Search, UserCog } from 'lucide-react';

interface UserData {
  id: string;
  uid: string;
  email: string;
  name: string;
  role: string;
  institutionId?: string;
  ativo: boolean;
  createdAt: { toDate: () => Date };
}

const ROLE_OPTIONS = [
  { value: 'ADMIN_GERAL', label: 'Administrador Geral' },
  { value: 'ADMIN_INSTITUICAO', label: 'Administrador da Instituicao' },
  { value: 'GESTOR_PROJETO', label: 'Gestor de Projeto' },
  { value: 'FISCAL_PROJETO', label: 'Fiscal de Projeto' },
  { value: 'CONSELHO_FISCAL_1', label: 'Conselho Fiscal 1' },
  { value: 'CONSELHO_FISCAL_2', label: 'Conselho Fiscal 2' },
  { value: 'CONSELHO_FISCAL_3', label: 'Conselho Fiscal 3' },
  { value: 'FISCAL_EXTERNO', label: 'Fiscal Externo' },
];

export function UsersPage() {
  const { user: currentUser } = useAuth();
  const [isAdmin, setIsAdmin] = useState(false);
  const [users, setUsers] = useState<UserData[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);
  const [editingId, setEditingId] = useState<string | null>(null);
  const [editRole, setEditRole] = useState('');

  useEffect(() => {
    (async () => {
      if (currentUser) {
        const result = await currentUser.getIdTokenResult();
        setIsAdmin(result.claims.role === 'ADMIN_GERAL');
      }
    })();
  }, [currentUser]);

  useEffect(() => {
    loadUsers();
  }, [page]);

  const loadUsers = async () => {
    setLoading(true);
    try {
      const { data } = await api.get(`/users?page=${page}&limit=15`);
      setUsers(data.users);
      setTotalPages(data.totalPages);
    } catch {
      toast({ title: 'Erro ao carregar usuarios', variant: 'destructive' });
    } finally {
      setLoading(false);
    }
  };

  const handleRoleChange = async (uid: string) => {
    try {
      await api.patch(`/users/${uid}/role`, { role: editRole });
      toast({ title: 'Papel atualizado com sucesso' });
      setEditingId(null);
      loadUsers();
    } catch {
      toast({ title: 'Erro ao atualizar papel', variant: 'destructive' });
    }
  };

  const filtered = users.filter(
    (u) =>
      u.name?.toLowerCase().includes(search.toLowerCase()) ||
      u.email?.toLowerCase().includes(search.toLowerCase()),
  );

  const getRoleBadge = (role: string) => {
    const roleConfig: Record<string, { label: string; variant: 'default' | 'secondary' | 'info' }> = {
      ADMIN_GERAL: { label: 'Admin Geral', variant: 'default' },
      ADMIN_INSTITUICAO: { label: 'Admin Instituicao', variant: 'info' },
      GESTOR_PROJETO: { label: 'Gestor', variant: 'secondary' },
      FISCAL_PROJETO: { label: 'Fiscal', variant: 'secondary' },
      CONSELHO_FISCAL_1: { label: 'Conselho 1', variant: 'secondary' },
      CONSELHO_FISCAL_2: { label: 'Conselho 2', variant: 'secondary' },
      CONSELHO_FISCAL_3: { label: 'Conselho 3', variant: 'secondary' },
      FISCAL_EXTERNO: { label: 'Fiscal Ext.', variant: 'secondary' },
    };
    const config = roleConfig[role];
    return config ? (
      <Badge variant={config.variant}>{config.label}</Badge>
    ) : (
      <Badge variant="secondary">{role}</Badge>
    );
  };

  const canEdit = isAdmin;

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Usuarios</h2>
          <p className="text-muted-foreground">Gerencie os usuarios do sistema</p>
        </div>
      </div>

      <div className="flex items-center gap-4">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por nome ou email..."
            className="pl-10"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Todos os Usuarios</CardTitle>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="py-8 text-center text-muted-foreground">Carregando...</div>
          ) : filtered.length === 0 ? (
            <div className="py-8 text-center text-muted-foreground">Nenhum usuario encontrado</div>
          ) : (
            <div className="space-y-3">
              {filtered.map((u) => (
                <div
                  key={u.id}
                  className="flex items-center justify-between rounded-lg border p-4"
                >
                  <div className="flex items-center gap-4">
                    <Avatar className="h-10 w-10">
                      <AvatarFallback className="text-xs">
                        {u.name
                          ? u.name.split(' ').map((n) => n[0]).join('').toUpperCase().slice(0, 2)
                          : u.email.slice(0, 2).toUpperCase()}
                      </AvatarFallback>
                    </Avatar>
                    <div>
                      <p className="font-medium">{u.name || 'Sem nome'}</p>
                      <p className="text-sm text-muted-foreground">{u.email}</p>
                    </div>
                  </div>

                  <div className="flex items-center gap-3">
                    {editingId === u.id ? (
                      <div className="flex items-center gap-2">
                        <select
                          className="rounded-md border px-3 py-1 text-sm"
                          value={editRole}
                          onChange={(e) => setEditRole(e.target.value)}
                        >
                          {ROLE_OPTIONS.map((opt) => (
                            <option key={opt.value} value={opt.value}>
                              {opt.label}
                            </option>
                          ))}
                        </select>
                        <Button size="sm" onClick={() => handleRoleChange(u.id)}>
                          Salvar
                        </Button>
                        <Button size="sm" variant="ghost" onClick={() => setEditingId(null)}>
                          Cancelar
                        </Button>
                      </div>
                    ) : (
                      <>
                        {getRoleBadge(u.role)}
                        {canEdit && (
                          <Button
                            variant="ghost"
                            size="icon"
                            onClick={() => {
                              setEditingId(u.id);
                              setEditRole(u.role);
                            }}
                          >
                            <UserCog className="h-4 w-4" />
                          </Button>
                        )}
                      </>
                    )}
                  </div>
                </div>
              ))}
            </div>
          )}

          {totalPages > 1 && (
            <div className="mt-4 flex items-center justify-center gap-2">
              <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage(page - 1)}>
                Anterior
              </Button>
              <span className="text-sm text-muted-foreground">
                Pagina {page} de {totalPages}
              </span>
              <Button variant="outline" size="sm" disabled={page >= totalPages} onClick={() => setPage(page + 1)}>
                Proxima
              </Button>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}

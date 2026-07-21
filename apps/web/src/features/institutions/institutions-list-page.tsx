import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '@/lib/api';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Search, Plus, Building2, MapPin } from 'lucide-react';

interface Institution {
  id: string;
  razaoSocial: string;
  nomeFantasia?: string;
  cnpj: string;
  email?: string;
  telefone?: string;
  municipio?: string;
  estado?: string;
  active: boolean;
  createdAt: { toDate?: () => Date } | string;
}

export function InstitutionsListPage() {
  const navigate = useNavigate();
  const [institutions, setInstitutions] = useState<Institution[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => {
    loadInstitutions();
  }, [page]);

  const loadInstitutions = async () => {
    setLoading(true);
    try {
      const params: any = { page, limit: 15 };
      if (search) params.search = search;
      const { data } = await api.get('/institutions', { params });
      setInstitutions(data.institutions);
      setTotalPages(data.totalPages);
    } catch {
      toast({ title: 'Erro ao carregar instituicoes', variant: 'destructive' });
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setPage(1);
    loadInstitutions();
  };

  const formatCnpj = (cnpj: string) => {
    const d = cnpj.replace(/\D/g, '');
    return d.replace(/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/, '$1.$2.$3/$4-$5');
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Instituicoes</h2>
          <p className="text-muted-foreground">Gerencie as organizacoes do sistema</p>
        </div>
        <Button onClick={() => navigate('/instituicoes/nova')}>
          <Plus className="mr-2 h-4 w-4" /> Nova Instituicao
        </Button>
      </div>

      <form onSubmit={handleSearch} className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input
            placeholder="Buscar por razao social..."
            className="pl-10"
            value={search}
            onChange={(e) => setSearch(e.target.value)}
          />
        </div>
        <Button type="submit" variant="secondary">Buscar</Button>
      </form>

      <Card>
        <CardHeader>
          <CardTitle className="text-lg">Todas as Instituicoes</CardTitle>
        </CardHeader>
        <CardContent>
          {loading ? (
            <div className="py-8 text-center text-muted-foreground">Carregando...</div>
          ) : institutions.length === 0 ? (
            <div className="py-8 text-center text-muted-foreground">Nenhuma instituicao encontrada</div>
          ) : (
            <div className="space-y-3">
              {institutions.map((inst) => (
                <div
                  key={inst.id}
                  className="flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors hover:bg-accent/50"
                  onClick={() => navigate(`/instituicoes/${inst.id}`)}
                >
                  <div className="flex items-start gap-4">
                    <div className="rounded-lg bg-primary/10 p-2">
                      <Building2 className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                      <p className="font-medium">{inst.razaoSocial}</p>
                      <p className="text-sm text-muted-foreground">
                        {formatCnpj(inst.cnpj)}
                      </p>
                      <div className="mt-1 flex items-center gap-3 text-xs text-muted-foreground">
                        {inst.municipio && inst.estado && (
                          <span className="flex items-center gap-1">
                            <MapPin className="h-3 w-3" />
                            {inst.municipio}/{inst.estado}
                          </span>
                        )}
                        {inst.email && (
                          <span>{inst.email}</span>
                        )}
                      </div>
                    </div>
                  </div>
                  <div className="flex items-center gap-2">
                    <Badge variant={inst.active ? 'success' : 'secondary'}>
                      {inst.active ? 'Ativa' : 'Inativa'}
                    </Badge>
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

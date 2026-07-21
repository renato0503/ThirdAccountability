import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import { formatDate, getStatusColor, getStatusText, formatCurrency } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Search, Plus, FolderKanban, CalendarDays } from 'lucide-react';

interface Project {
  id: string;
  nome: string;
  codigo?: string;
  status: string;
  institutionId: string;
  institutionName?: string;
  valorTotal?: number;
  dataInicio?: string;
  dataFim?: string;
  createdAt?: any;
}

export function ProjectsListPage() {
  const navigate = useNavigate();
  const [projects, setProjects] = useState<Project[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => {
    loadProjects();
  }, [page, statusFilter]);

  const loadProjects = async () => {
    setLoading(true);
    try {
      const params: any = { page, limit: 15 };
      if (search) params.search = search;
      if (statusFilter) params.status = statusFilter;
      const { data } = await api.get('/projects', { params });
      setProjects(data.projects);
      setTotalPages(data.totalPages);
    } catch {
      toast({ title: 'Erro ao carregar projetos', variant: 'destructive' });
    } finally {
      setLoading(false);
    }
  };

  const handleSearch = (e: React.FormEvent) => {
    e.preventDefault();
    setPage(1);
    loadProjects();
  };

  const STATUSES = ['', 'RASCUNHO', 'EM_ANALISE', 'APROVADO', 'EM_EXECUCAO', 'SUSPENSO', 'FINALIZADO', 'PRESTACAO_CONTAS', 'PRESTACAO_APROVADA', 'PRESTACAO_REPROVADA'];

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Projetos</h2>
          <p className="text-muted-foreground">Gerencie os projetos da sua instituicao</p>
        </div>
        <Button onClick={() => navigate('/projetos/novo')}>
          <Plus className="mr-2 h-4 w-4" /> Novo Projeto
        </Button>
      </div>

      <form onSubmit={handleSearch} className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input placeholder="Buscar por nome..." className="pl-10" value={search} onChange={(e) => setSearch(e.target.value)} />
        </div>
        <select className="rounded-md border px-3 py-2 text-sm" value={statusFilter} onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }}>
          <option value="">Todos os status</option>
          {STATUSES.filter(Boolean).map((s) => (
            <option key={s} value={s}>{getStatusText(s)}</option>
          ))}
        </select>
        <Button type="submit" variant="secondary">Buscar</Button>
      </form>

      <Card>
        <CardHeader><CardTitle className="text-lg">Todos os Projetos</CardTitle></CardHeader>
        <CardContent>
          {loading ? (
            <div className="py-8 text-center text-muted-foreground">Carregando...</div>
          ) : projects.length === 0 ? (
            <div className="py-8 text-center text-muted-foreground">Nenhum projeto encontrado</div>
          ) : (
            <div className="space-y-3">
              {projects.map((p) => (
                <div key={p.id} className="flex cursor-pointer items-center justify-between rounded-lg border p-4 transition-colors hover:bg-accent/50" onClick={() => navigate(`/projetos/${p.id}`)}>
                  <div className="flex items-start gap-4">
                    <div className="rounded-lg bg-primary/10 p-2">
                      <FolderKanban className="h-5 w-5 text-primary" />
                    </div>
                    <div>
                      <p className="font-medium">{p.nome}</p>
                      <p className="text-sm text-muted-foreground">
                        {p.codigo && <>{p.codigo} &middot; </>}
                        {p.institutionName}
                      </p>
                      <div className="mt-1 flex items-center gap-3 text-xs text-muted-foreground">
                        {p.valorTotal != null && <span>{formatCurrency(p.valorTotal)}</span>}
                        {p.dataInicio && <span className="flex items-center gap-1"><CalendarDays className="h-3 w-3" /> {formatDate(p.dataInicio)}</span>}
                      </div>
                    </div>
                  </div>
                  <Badge className={getStatusColor(p.status)}>{getStatusText(p.status)}</Badge>
                </div>
              ))}
            </div>
          )}
          {totalPages > 1 && (
            <div className="mt-4 flex items-center justify-center gap-2">
              <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage(page - 1)}>Anterior</Button>
              <span className="text-sm text-muted-foreground">Pagina {page} de {totalPages}</span>
              <Button variant="outline" size="sm" disabled={page >= totalPages} onClick={() => setPage(page + 1)}>Proxima</Button>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}

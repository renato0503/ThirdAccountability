import { useState, useEffect } from 'react';
import api from '@/lib/api';
import { formatDateTime } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { ShieldCheck, Search } from 'lucide-react';

interface LogEntry {
  id: string; userId: string; userEmail?: string;
  acao: string; entidade: string; entidadeId?: string;
  ip?: string; timestamp: any; dados?: string;
}

export function AuditPage() {
  const [logs, setLogs] = useState<LogEntry[]>([]);
  const [loading, setLoading] = useState(true);
  const [filters, setFilters] = useState({ userId: '', acao: '', entidade: '' });
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => { load(); }, [page]);

  const load = async () => {
    setLoading(true);
    try {
      const params: any = { page, limit: 30 };
      if (filters.userId) params.userId = filters.userId;
      if (filters.acao) params.acao = filters.acao;
      if (filters.entidade) params.entidade = filters.entidade;
      const { data } = await api.get('/audit', { params });
      setLogs(data.logs);
      setTotalPages(data.totalPages);
    } catch { toast({ title: 'Erro ao carregar logs', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  const acaoColors: Record<string, 'info' | 'success' | 'warning' | 'destructive'> = {
    POST: 'success', CREATE: 'success',
    PUT: 'info', PATCH: 'info', UPDATE: 'info',
    DELETE: 'destructive',
    GET: 'warning', SEARCH: 'warning',
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight">Auditoria</h2>
        <p className="text-muted-foreground">Registro de todas as acoes no sistema</p>
      </div>

      <Card>
        <CardHeader>
          <div className="flex items-center gap-3">
            <CardTitle className="text-lg">Filtros</CardTitle>
            <div className="flex gap-2 flex-1">
              <Input placeholder="User ID" value={filters.userId} onChange={(e) => setFilters((p) => ({ ...p, userId: e.target.value }))} />
              <Input placeholder="Acao (POST, PUT, DELETE...)" value={filters.acao} onChange={(e) => setFilters((p) => ({ ...p, acao: e.target.value }))} />
              <Input placeholder="Entidade (project, user...)" value={filters.entidade} onChange={(e) => setFilters((p) => ({ ...p, entidade: e.target.value }))} />
              <Button onClick={load}><Search className="h-4 w-4 mr-1" /> Filtrar</Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          {loading ? <p className="text-muted-foreground">Carregando...</p> : logs.length === 0 ? (
            <p className="text-muted-foreground">Nenhum log encontrado</p>
          ) : (
            <div className="space-y-2">
              {logs.map((log) => (
                <div key={log.id} className="flex items-center justify-between rounded-lg border p-3">
                  <div className="flex items-center gap-3">
                    <ShieldCheck className="h-4 w-4 text-muted-foreground shrink-0" />
                    <div>
                      <div className="flex items-center gap-2">
                        <Badge variant={acaoColors[log.acao] || 'secondary'} className="text-xs">{log.acao}</Badge>
                        <span className="text-sm font-medium">{log.entidade}</span>
                        {log.entidadeId && <span className="text-xs text-muted-foreground">#{log.entidadeId}</span>}
                      </div>
                      <p className="text-xs text-muted-foreground">
                        {log.userEmail || log.userId} &middot; {log.ip}
                      </p>
                    </div>
                  </div>
                  <div className="text-right">
                    <p className="text-xs text-muted-foreground">{log.timestamp?.toDate ? formatDateTime(log.timestamp.toDate()) : ''}</p>
                    {log.dados && <p className="text-xs text-muted-foreground truncate max-w-[200px]">{log.dados.slice(0, 60)}</p>}
                  </div>
                </div>
              ))}
            </div>
          )}
          {totalPages > 1 && (
            <div className="mt-4 flex justify-center gap-2">
              <Button variant="outline" size="sm" disabled={page <= 1} onClick={() => setPage(page - 1)}>Anterior</Button>
              <span className="text-sm text-muted-foreground self-center">Pagina {page} de {totalPages}</span>
              <Button variant="outline" size="sm" disabled={page >= totalPages} onClick={() => setPage(page + 1)}>Proxima</Button>
            </div>
          )}
        </CardContent>
      </Card>
    </div>
  );
}

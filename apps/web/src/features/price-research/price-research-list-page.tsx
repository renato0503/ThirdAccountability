import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import { getStatusText, getStatusColor, formatCurrency } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Search, SearchIcon, MessageSquareText, Plus } from 'lucide-react';

export function PriceResearchListPage() {
  const navigate = useNavigate();
  const [items, setItems] = useState<any[]>([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');
  const [statusFilter, setStatusFilter] = useState('');
  const [page, setPage] = useState(1);
  const [totalPages, setTotalPages] = useState(1);

  useEffect(() => { load(); }, [page, statusFilter]);

  const load = async () => {
    setLoading(true);
    try {
      const params: any = { page, limit: 15 };
      if (search) params.search = search;
      if (statusFilter) params.status = statusFilter;
      const { data } = await api.get('/price-research', { params });
      setItems(data.researches);
      setTotalPages(data.totalPages);
    } catch { toast({ title: 'Erro ao carregar', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Pesquisa de Precos</h2>
          <p className="text-muted-foreground">Cote precos em fontes publicas e privadas</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => navigate('/pesquisa-precos/chat-ia')}>
            <MessageSquareText className="mr-2 h-4 w-4" /> Chat IA
          </Button>
          <Button onClick={() => navigate('/pesquisa-precos/nova')}>
            <Plus className="mr-2 h-4 w-4" /> Nova Pesquisa
          </Button>
        </div>
      </div>

      <div className="flex gap-2">
        <div className="relative flex-1">
          <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
          <Input placeholder="Buscar por termo..." className="pl-10" value={search} onChange={(e) => setSearch(e.target.value)} />
        </div>
        <select className="rounded-md border px-3 py-2 text-sm" value={statusFilter} onChange={(e) => { setStatusFilter(e.target.value); setPage(1); }}>
          <option value="">Todos</option>
          {['RASCUNHO', 'BUSCADA', 'COM_RESULTADOS', 'SEM_RESULTADOS', 'SELECIONADA', 'FINALIZADA', 'CANCELADA'].map((s) => (
            <option key={s} value={s}>{getStatusText(s)}</option>
          ))}
        </select>
        <Button variant="secondary" onClick={load}>Buscar</Button>
      </div>

      <Card>
        <CardHeader><CardTitle className="text-lg">Pesquisas de Preco</CardTitle></CardHeader>
        <CardContent>
          {loading ? <p className="text-muted-foreground">Carregando...</p> : items.length === 0 ? (
            <p className="text-muted-foreground">Nenhuma pesquisa encontrada</p>
          ) : (
            <div className="space-y-3">
              {items.map((p: any) => (
                <div key={p.id} className="flex cursor-pointer items-center justify-between rounded-lg border p-4 hover:bg-accent/50" onClick={() => navigate(`/pesquisa-precos/${p.id}`)}>
                  <div className="flex items-start gap-4">
                    <div className="rounded-lg bg-primary/10 p-2"><SearchIcon className="h-5 w-5 text-primary" /></div>
                    <div>
                      <p className="font-medium">{p.searchTerm}</p>
                      <p className="text-sm text-muted-foreground">
                        {p.resultsCount || 0} resultados &middot; Media: {formatCurrency(p.averagePrice || 0)}
                      </p>
                      {p.selectedReferencePrice > 0 && (
                        <p className="text-sm font-medium text-green-600">Ref: {formatCurrency(p.selectedReferencePrice)}</p>
                      )}
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

import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import api from '@/lib/api';
import { formatDate, formatCurrency, getStatusText } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Plus, DollarSign, Filter } from 'lucide-react';

interface Expense {
  id: string; projectId: string; projectName?: string;
  categoria: string; descricao: string; valor: number;
  dataGasto: string; fornecedor?: string; status: string;
}

export function ExpensesListPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const projectId = searchParams.get('projectId');
  const [expenses, setExpenses] = useState<Expense[]>([]);
  const [loading, setLoading] = useState(true);
  const [statusFilter, setStatusFilter] = useState('');

  useEffect(() => { loadExpenses(); }, [projectId, statusFilter]);

  const loadExpenses = async () => {
    setLoading(true);
    try {
      if (projectId) {
        const params: any = {};
        if (statusFilter) params.status = statusFilter;
        const { data } = await api.get(`/projects/${projectId}/expenses`, { params });
        setExpenses(data);
      } else {
        setExpenses([]);
      }
    } catch { toast({ title: 'Erro ao carregar despesas', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  const updateStatus = async (expenseId: string, status: string) => {
    try {
      await api.patch(`/projects/${projectId}/expenses/${expenseId}/status`, { status });
      loadExpenses();
      toast({ title: `Status alterado para ${getStatusText(status)}` });
    } catch { toast({ title: 'Erro ao atualizar status', variant: 'destructive' }); }
  };

  const statusColors: Record<string, 'warning' | 'success' | 'destructive' | 'secondary'> = {
    PENDENTE: 'warning', APROVADO: 'success', REPROVADO: 'destructive', PAGO: 'success',
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Despesas</h2>
          <p className="text-muted-foreground">{projectId ? 'Despesas do projeto' : 'Selecione um projeto para ver as despesas'}</p>
        </div>
        {projectId && (
          <Button onClick={() => navigate(`/despesas/nova?projectId=${projectId}`)}>
            <Plus className="mr-2 h-4 w-4" /> Nova Despesa
          </Button>
        )}
      </div>

      {!projectId ? (
        <Card>
          <CardContent className="py-12 text-center text-muted-foreground">
            Selecione um projeto para visualizar as despesas
          </CardContent>
        </Card>
      ) : (
        <>
          <div className="flex items-center gap-2">
            <Filter className="h-4 w-4 text-muted-foreground" />
            <select className="rounded-md border px-3 py-2 text-sm" value={statusFilter} onChange={(e) => setStatusFilter(e.target.value)}>
              <option value="">Todos os status</option>
              <option value="PENDENTE">Pendente</option>
              <option value="APROVADO">Aprovado</option>
              <option value="REPROVADO">Reprovado</option>
              <option value="PAGO">Pago</option>
            </select>
          </div>

          <Card>
            <CardHeader><CardTitle className="text-lg">Todas as Despesas</CardTitle></CardHeader>
            <CardContent>
              {loading ? <p className="text-muted-foreground">Carregando...</p> : expenses.length === 0 ? (
                <p className="text-muted-foreground">Nenhuma despesa encontrada</p>
              ) : (
                <div className="space-y-3">
                  {expenses.map((e) => (
                    <div key={e.id} className="flex items-center justify-between rounded-lg border p-4">
                      <div className="flex items-start gap-4">
                        <div className="rounded-lg bg-primary/10 p-2">
                          <DollarSign className="h-5 w-5 text-primary" />
                        </div>
                        <div>
                          <p className="font-medium">{e.descricao}</p>
                          <p className="text-sm text-muted-foreground">
                            {e.categoria} &middot; {e.fornecedor} &middot; {formatDate(e.dataGasto)}
                          </p>
                          <p className="text-lg font-bold mt-1">{formatCurrency(e.valor)}</p>
                        </div>
                      </div>
                      <div className="flex items-center gap-2">
                        <Badge variant={statusColors[e.status] || 'secondary'}>{getStatusText(e.status)}</Badge>
                        <div className="flex flex-col gap-1 ml-2">
                          {e.status === 'PENDENTE' && (
                            <>
                              <Button size="sm" variant="outline" className="text-green-600 text-xs h-7" onClick={() => updateStatus(e.id, 'APROVADO')}>Aprovar</Button>
                              <Button size="sm" variant="outline" className="text-red-600 text-xs h-7" onClick={() => updateStatus(e.id, 'REPROVADO')}>Reprovar</Button>
                            </>
                          )}
                          {e.status === 'APROVADO' && (
                            <Button size="sm" variant="outline" className="text-xs h-7" onClick={() => updateStatus(e.id, 'PAGO')}>Pago</Button>
                          )}
                        </div>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </>
      )}
    </div>
  );
}

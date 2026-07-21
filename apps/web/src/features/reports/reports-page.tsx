import { useState } from 'react';
import api from '@/lib/api';
import { formatCurrency, formatDate, getStatusText } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { ClipboardList } from 'lucide-react';

export function ReportsPage() {
  const [projectId, setProjectId] = useState('');
  const [report, setReport] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  const generate = async () => {
    if (!projectId.trim()) { toast({ title: 'Informe o ID do projeto', variant: 'destructive' }); return; }
    setLoading(true);
    try {
      const { data } = await api.get(`/reports/project/${projectId}`);
      setReport(data);
    } catch { toast({ title: 'Erro ao gerar relatorio', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight">Relatorios</h2>
        <p className="text-muted-foreground">Gere relatorios consolidados dos projetos</p>
      </div>

      <Card>
        <CardHeader><CardTitle className="text-lg">Gerar Relatorio de Projeto</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <div className="flex gap-3">
            <div className="flex-1 space-y-1">
              <Label>ID do Projeto</Label>
              <Input value={projectId} onChange={(e) => setProjectId(e.target.value)} placeholder="Cole o ID do projeto" />
            </div>
            <Button className="self-end" onClick={generate} disabled={loading}>
              <ClipboardList className="mr-2 h-4 w-4" /> {loading ? 'Gerando...' : 'Gerar'}
            </Button>
          </div>
        </CardContent>
      </Card>

      {report && (
        <div className="space-y-4">
          <Card>
            <CardHeader>
              <div className="flex items-center justify-between">
                <CardTitle className="text-lg">{report.project.nome}</CardTitle>
                <Badge>{getStatusText(report.project.status)}</Badge>
              </div>
              <p className="text-sm text-muted-foreground">{report.project.codigo}</p>
            </CardHeader>
            <CardContent className="grid grid-cols-2 gap-4">
              <div>
                <p className="text-xs text-muted-foreground">Valor Total</p>
                <p className="font-medium">{formatCurrency(report.project.valorTotal)}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Valor Recebido</p>
                <p className="font-medium">{formatCurrency(report.project.valorRecebido)}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Valor Executado</p>
                <p className="font-medium">{formatCurrency(report.project.valorExecutado)}</p>
              </div>
              <div>
                <p className="text-xs text-muted-foreground">Periodo</p>
                <p className="font-medium">{report.project.dataInicio} a {report.project.dataFim}</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader><CardTitle className="text-lg">Metas ({report.goals.length})</CardTitle></CardHeader>
            <CardContent>
              {report.goals.length === 0 ? <p className="text-muted-foreground">Nenhuma meta</p> : (
                <div className="space-y-2">
                  {report.goals.map((g: any) => (
                    <div key={g.id} className="flex justify-between rounded-lg border p-3">
                      <div>
                        <p className="font-medium">#{g.numero} {g.titulo}</p>
                        {g.descricao && <p className="text-sm text-muted-foreground">{g.descricao}</p>}
                      </div>
                      <Badge variant={g.status === 'Concluída' ? 'success' : g.status === 'Em análise' ? 'warning' : 'secondary'}>{g.status}</Badge>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader><CardTitle className="text-lg">Despesas ({report.expenses.length})</CardTitle></CardHeader>
            <CardContent>
              {report.expenses.length === 0 ? <p className="text-muted-foreground">Nenhuma despesa</p> : (
                <div className="space-y-2">
                  {report.expenses.map((e: any) => (
                    <div key={e.id} className="flex justify-between rounded-lg border p-3">
                      <div>
                        <p className="font-medium">{e.descricao}</p>
                        <p className="text-sm text-muted-foreground">{e.categoria} &middot; {e.fornecedor} &middot; {formatDate(e.dataGasto)}</p>
                      </div>
                      <div className="text-right">
                        <p className="font-bold">{formatCurrency(e.valor)}</p>
                        <Badge variant={e.status === 'PAGO' ? 'success' : e.status === 'PENDENTE' ? 'warning' : 'secondary'}>{e.status}</Badge>
                      </div>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>

          <Card>
            <CardHeader><CardTitle className="text-lg">Diligencias ({report.diligences.length})</CardTitle></CardHeader>
            <CardContent>
              {report.diligences.length === 0 ? <p className="text-muted-foreground">Nenhuma diligencia</p> : (
                <div className="space-y-2">
                  {report.diligences.map((d: any) => (
                    <div key={d.id} className="flex justify-between rounded-lg border p-3">
                      <div>
                        <p className="font-medium">{d.descricao}</p>
                        <p className="text-sm text-muted-foreground">{d.tipo}</p>
                      </div>
                      <Badge variant={d.status === 'FECHADA' ? 'success' : d.status === 'ABERTA' ? 'destructive' : 'warning'}>{d.status}</Badge>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </div>
      )}
    </div>
  );
}

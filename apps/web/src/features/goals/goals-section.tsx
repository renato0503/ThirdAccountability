import { useState, useEffect } from 'react';
import api from '@/lib/api';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from '@/hooks/use-toast';
import { Plus, CheckCircle, XCircle, Send, Upload, ChevronDown, ChevronUp } from 'lucide-react';

interface Goal {
  id: string; numero: number; titulo: string; descricao?: string;
  orcamento?: number; percentualExecucao?: number;
  status: string; afericao?: string; activities?: any[];
  proofs?: any[]; approvals?: any[];
}

interface GoalsSectionProps {
  projectId: string;
}

export function GoalsSection({ projectId }: GoalsSectionProps) {
  const [goals, setGoals] = useState<Goal[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [expandedId, setExpandedId] = useState<string | null>(null);
  const [goalForm, setGoalForm] = useState({ numero: goals.length + 1, titulo: '', descricao: '', orcamento: '', afericao: '' });
  const [activityForm, setActivityForm] = useState<Record<string, string>>({});
  const [proofFiles, setProofFiles] = useState<Record<string, FileList | null>>({});

  useEffect(() => { loadGoals(); }, [projectId]);

  const loadGoals = async () => {
    setLoading(true);
    try {
      const { data } = await api.get(`/projects/${projectId}/goals`);
      setGoals(data);
    } catch { toast({ title: 'Erro ao carregar metas', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  const createGoal = async () => {
    if (!goalForm.titulo) { toast({ title: 'Titulo obrigatorio', variant: 'destructive' }); return; }
    try {
      await api.post(`/projects/${projectId}/goals`, {
        ...goalForm,
        orcamento: goalForm.orcamento ? Number(goalForm.orcamento) : undefined,
      });
      setShowForm(false);
      setGoalForm({ numero: goals.length + 2, titulo: '', descricao: '', orcamento: '', afericao: '' });
      loadGoals();
      toast({ title: 'Meta criada' });
    } catch { toast({ title: 'Erro ao criar meta', variant: 'destructive' }); }
  };

  const sendToAnalysis = async (goalId: string) => {
    try {
      await api.post(`/projects/${projectId}/goals/${goalId}/send-analysis`);
      loadGoals();
      toast({ title: 'Meta enviada para analise' });
    } catch { toast({ title: 'Erro', variant: 'destructive' }); }
  };

  const approveGoal = async (goalId: string) => {
    try {
      await api.post(`/projects/${projectId}/goals/${goalId}/approve`);
      loadGoals();
      toast({ title: 'Meta aprovada' });
    } catch { toast({ title: 'Erro', variant: 'destructive' }); }
  };

  const disapproveGoal = async (goalId: string) => {
    try {
      await api.post(`/projects/${projectId}/goals/${goalId}/disapprove`);
      loadGoals();
      toast({ title: 'Meta desaprovada' });
    } catch { toast({ title: 'Erro', variant: 'destructive' }); }
  };

  const addActivity = async (goalId: string) => {
    const descricao = activityForm[goalId];
    if (!descricao?.trim()) return;
    try {
      await api.post(`/projects/${projectId}/goals/${goalId}/activities`, { descricao });
      setActivityForm((p) => ({ ...p, [goalId]: '' }));
      loadGoals();
      toast({ title: 'Atividade adicionada' });
    } catch { toast({ title: 'Erro', variant: 'destructive' }); }
  };

  const uploadProof = async (goalId: string) => {
    const files = proofFiles[goalId];
    if (!files?.length) { toast({ title: 'Selecione um arquivo', variant: 'destructive' }); return; }
    const formData = new FormData();
    for (const f of files) formData.append('files', f);
    try {
      await api.post(`/projects/${projectId}/goals/${goalId}/proofs`, formData);
      setProofFiles((p) => ({ ...p, [goalId]: null }));
      loadGoals();
      toast({ title: 'Comprovacao enviada' });
    } catch { toast({ title: 'Erro ao enviar', variant: 'destructive' }); }
  };

  const getStatusBadge = (status: string) => {
    const variants: Record<string, 'secondary' | 'warning' | 'success' | 'info' | 'default'> = {
      'Pendente': 'secondary', 'Em análise': 'warning', 'Concluída': 'success',
      'Prestação de Contas': 'info', 'Em andamento': 'default',
    };
    return <Badge variant={variants[status] || 'secondary'}>{status}</Badge>;
  };

  return (
    <div className="space-y-4">
      <div className="flex items-center justify-between">
        <h3 className="text-lg font-semibold">Metas ({goals.length})</h3>
        <Button size="sm" onClick={() => setShowForm(!showForm)}>
          <Plus className="mr-2 h-4 w-4" /> Nova Meta
        </Button>
      </div>

      {showForm && (
        <Card>
          <CardHeader><CardTitle className="text-sm">Nova Meta</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <div className="grid grid-cols-3 gap-3">
              <div className="space-y-1">
                <Label>Numero</Label>
                <Input type="number" value={goalForm.numero} onChange={(e) => setGoalForm((p) => ({ ...p, numero: Number(e.target.value) }))} />
              </div>
              <div className="col-span-2 space-y-1">
                <Label>Titulo</Label>
                <Input value={goalForm.titulo} onChange={(e) => setGoalForm((p) => ({ ...p, titulo: e.target.value }))} />
              </div>
            </div>
            <div className="space-y-1">
              <Label>Descricao</Label>
              <textarea className="flex h-16 w-full rounded-md border bg-transparent px-3 py-2 text-sm" value={goalForm.descricao} onChange={(e) => setGoalForm((p) => ({ ...p, descricao: e.target.value }))} />
            </div>
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1">
                <Label>Orcamento</Label>
                <Input type="number" step="0.01" value={goalForm.orcamento} onChange={(e) => setGoalForm((p) => ({ ...p, orcamento: e.target.value }))} />
              </div>
              <div className="space-y-1">
                <Label>Afericao</Label>
                <Input value={goalForm.afericao} onChange={(e) => setGoalForm((p) => ({ ...p, afericao: e.target.value }))} />
              </div>
            </div>
            <div className="flex gap-2">
              <Button size="sm" onClick={createGoal}>Salvar</Button>
              <Button size="sm" variant="ghost" onClick={() => setShowForm(false)}>Cancelar</Button>
            </div>
          </CardContent>
        </Card>
      )}

      {loading ? <p className="text-muted-foreground">Carregando...</p> : goals.length === 0 ? (
        <p className="text-muted-foreground">Nenhuma meta cadastrada</p>
      ) : (
        <div className="space-y-3">
          {goals.map((goal) => (
            <Card key={goal.id}>
              <CardHeader className="cursor-pointer py-3" onClick={() => setExpandedId(expandedId === goal.id ? null : goal.id)}>
                <div className="flex items-center justify-between">
                  <div className="flex items-center gap-3">
                    <span className="text-sm font-bold text-muted-foreground">#{goal.numero}</span>
                    <CardTitle className="text-sm font-medium">{goal.titulo}</CardTitle>
                    {getStatusBadge(goal.status)}
                  </div>
                  <div className="flex items-center gap-2">
                    {goal.status === 'Pendente' && (
                      <Button size="sm" variant="outline" onClick={(e) => { e.stopPropagation(); sendToAnalysis(goal.id); }}>
                        <Send className="h-3 w-3 mr-1" /> Enviar
                      </Button>
                    )}
                    {goal.status === 'Em análise' && (
                      <>
                        <Button size="sm" variant="outline" className="text-green-600" onClick={(e) => { e.stopPropagation(); approveGoal(goal.id); }}>
                          <CheckCircle className="h-3 w-3 mr-1" /> Aprovar
                        </Button>
                        <Button size="sm" variant="outline" className="text-red-600" onClick={(e) => { e.stopPropagation(); disapproveGoal(goal.id); }}>
                          <XCircle className="h-3 w-3 mr-1" /> Reprovar
                        </Button>
                      </>
                    )}
                    {expandedId === goal.id ? <ChevronUp className="h-4 w-4" /> : <ChevronDown className="h-4 w-4" />}
                  </div>
                </div>
              </CardHeader>
              {expandedId === goal.id && (
                <CardContent className="space-y-4 pt-0">
                  {goal.descricao && <p className="text-sm text-muted-foreground">{goal.descricao}</p>}
                  {goal.orcamento != null && <p className="text-sm">Orcamento: R$ {goal.orcamento.toFixed(2)}</p>}
                  {goal.afericao && <p className="text-sm">Afericao: {goal.afericao}</p>}

                  {/* Activities */}
                  <div>
                    <p className="text-sm font-medium mb-2">Atividades</p>
                    <div className="flex gap-2 mb-2">
                      <Input placeholder="Nova atividade..." value={activityForm[goal.id] || ''} onChange={(e) => setActivityForm((p) => ({ ...p, [goal.id]: e.target.value }))} />
                      <Button size="sm" onClick={() => addActivity(goal.id)}>Adicionar</Button>
                    </div>
                    {goal.activities && goal.activities.map((a: any) => (
                      <div key={a.id} className="flex items-center gap-2 text-sm py-1">
                        <span className="h-1.5 w-1.5 rounded-full bg-primary" />
                        <span>{a.descricao}</span>
                      </div>
                    ))}
                  </div>

                  {/* Proofs */}
                  <div>
                    <p className="text-sm font-medium mb-2">Comprovacoes</p>
                    <div className="flex gap-2">
                      <Input type="file" multiple onChange={(e) => setProofFiles((p) => ({ ...p, [goal.id]: e.target.files }))} />
                      <Button size="sm" onClick={() => uploadProof(goal.id)}><Upload className="h-3 w-3 mr-1" /> Enviar</Button>
                    </div>
                  </div>

                  {/* Approvals */}
                  {goal.approvals && goal.approvals.length > 0 && (
                    <div>
                      <p className="text-sm font-medium mb-1">Aprovacoes</p>
                      {goal.approvals.map((a: any) => (
                        <div key={a.id} className="flex items-center gap-2 text-sm">
                          {a.aprovado ? <CheckCircle className="h-3 w-3 text-green-600" /> : <XCircle className="h-3 w-3 text-red-600" />}
                          <span>{a.observacao || (a.aprovado ? 'Aprovado' : 'Desaprovado')}</span>
                        </div>
                      ))}
                    </div>
                  )}
                </CardContent>
              )}
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}

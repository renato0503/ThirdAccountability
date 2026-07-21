import { useState, useEffect } from 'react';
import { useNavigate, useSearchParams } from 'react-router-dom';
import api from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/hooks/use-toast';
import { ArrowLeft, Save } from 'lucide-react';

export function ExpenseFormPage() {
  const navigate = useNavigate();
  const [searchParams] = useSearchParams();
  const projectId = searchParams.get('projectId');
  const [project, setProject] = useState<any>(null);
  const [saving, setSaving] = useState(false);

  const [form, setForm] = useState({
    categoria: '', descricao: '', valor: '', dataGasto: '', fornecedor: '',
  });

  useEffect(() => {
    if (projectId) loadProject();
  }, [projectId]);

  const loadProject = async () => {
    try {
      const { data } = await api.get(`/projects/${projectId}`);
      setProject(data);
    } catch { navigate('/financeiro'); }
  };

  const update = (f: string, v: any) => setForm((p) => ({ ...p, [f]: v }));

  const handleSubmit = async () => {
    if (!form.descricao || !form.valor || !form.dataGasto) {
      toast({ title: 'Preencha descricao, valor e data', variant: 'destructive' });
      return;
    }
    setSaving(true);
    try {
      await api.post(`/projects/${projectId}/expenses`, {
        ...form, valor: Number(form.valor),
      });
      toast({ title: 'Despesa criada' });
      navigate(`/financeiro?projectId=${projectId}`);
    } catch { toast({ title: 'Erro ao criar despesa', variant: 'destructive' }); }
    finally { setSaving(false); }
  };

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate(`/financeiro?projectId=${projectId}`)}>
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Nova Despesa</h2>
          <p className="text-muted-foreground">{project?.nome && `Projeto: ${project.nome}`}</p>
        </div>
      </div>

      <Card>
        <CardHeader><CardTitle>Dados da Despesa</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label>Descricao *</Label>
            <Input value={form.descricao} onChange={(e) => update('descricao', e.target.value)} />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label>Categoria</Label>
              <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={form.categoria} onChange={(e) => update('categoria', e.target.value)}>
                <option value="">Selecione...</option>
                <option value="Material de Consumo">Material de Consumo</option>
                <option value="Servico de Terceiros">Servico de Terceiros</option>
                <option value="Equipamento">Equipamento</option>
                <option value="Transporte">Transporte</option>
                <option value="Alimentacao">Alimentacao</option>
                <option value="Locacao">Locacao</option>
                <option value="Outro">Outro</option>
              </select>
            </div>
            <div className="space-y-2">
              <Label>Valor *</Label>
              <Input type="number" step="0.01" value={form.valor} onChange={(e) => update('valor', e.target.value)} />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label>Data do Gasto *</Label>
              <Input type="date" value={form.dataGasto} onChange={(e) => update('dataGasto', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Fornecedor</Label>
              <Input value={form.fornecedor} onChange={(e) => update('fornecedor', e.target.value)} />
            </div>
          </div>
          <Button onClick={handleSubmit} disabled={saving}>
            <Save className="mr-2 h-4 w-4" /> {saving ? 'Salvando...' : 'Criar Despesa'}
          </Button>
        </CardContent>
      </Card>
    </div>
  );
}

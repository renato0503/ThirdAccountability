import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '@/lib/api';

import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/hooks/use-toast';
import { ChevronLeft, ChevronRight, Save, ArrowLeft, Plus, Trash2 } from 'lucide-react';

const STEPS = [
  { id: 1, label: 'Dados Basicos' },
  { id: 2, label: 'Detalhamento' },
  { id: 3, label: 'Locais de Execucao' },
  { id: 4, label: 'Objetivos Especificos' },
  { id: 5, label: 'Equipe do Projeto' },
  { id: 6, label: 'Servicos Contratados' },
];

interface Location { cidade: string; estado: string; }
interface TeamMember { funcao: string; quantidade: number; descricao: string; }
interface ContractedService { tipoContratacao: string; descricao: string; periodoExecucao: string; unidadePeriodo: string; tipoPagamento: string; valor: string; }

export function ProjectFormPage() {
  const navigate = useNavigate();
  const { id } = useParams();
  const isEditing = !!id;
  const [step, setStep] = useState(1);
  const [saving, setSaving] = useState(false);
  const [institutions, setInstitutions] = useState<any[]>([]);
  const [fundingSources, setFundingSources] = useState<any[]>([]);

  const [form, setForm] = useState({
    nome: '', institutionId: '', fundingSourceId: '', codigo: '', numeroProposta: '',
    fonte: '', parlamentar: '', secretaria: '', descricao: '', objetivoGeral: '',
    publicoAlvo: '', quantidadePublico: '', valorTotal: '', valorRecebido: '',
    dataInicio: '', dataFim: '', status: 'RASCUNHO', responsavel: '', localExecucao: '',
    gerarCodigo: true, anoCodigo: new Date().getFullYear().toString(),
  });

  const [locations, setLocations] = useState<Location[]>([{ cidade: '', estado: '' }]);
  const [objectives, setObjectives] = useState<string[]>(['']);
  const [teamMembers, setTeamMembers] = useState<TeamMember[]>([{ funcao: '', quantidade: 1, descricao: '' }]);
  const [contractedServices, setContractedServices] = useState<ContractedService[]>([{ tipoContratacao: 'PF', descricao: '', periodoExecucao: '', unidadePeriodo: 'mes', tipoPagamento: 'unico', valor: '' }]);

  useEffect(() => {
    loadInitialData();
    if (isEditing) loadProject();
  }, [id]);

  const loadInitialData = async () => {
    try {
      const [instRes, fsRes] = await Promise.all([
        api.get('/institutions?limit=100'),
        api.get('/funding-sources'),
      ]);
      setInstitutions(instRes.data.institutions);
      setFundingSources(fsRes.data);
    } catch {}
  };

  const loadProject = async () => {
    try {
      const { data } = await api.get(`/projects/${id}`);
      setForm({
        nome: data.nome || '', institutionId: data.institutionId || '',
        fundingSourceId: data.fundingSourceId || '', codigo: data.codigo || '',
        numeroProposta: data.numeroProposta || '', fonte: data.fonte || '',
        parlamentar: data.parlamentar || '', secretaria: data.secretaria || '',
        descricao: data.descricao || '', objetivoGeral: data.objetivoGeral || '',
        publicoAlvo: data.publicoAlvo || '', quantidadePublico: data.quantidadePublico?.toString() || '',
        valorTotal: data.valorTotal?.toString() || '', valorRecebido: data.valorRecebido?.toString() || '',
        dataInicio: data.dataInicio || '', dataFim: data.dataFim || '',
        status: data.status || 'RASCUNHO', responsavel: data.responsavel || '',
        localExecucao: data.localExecucao || '', gerarCodigo: false, anoCodigo: '',
      });
      if (data.executionLocations) setLocations(data.executionLocations);
      if (data.specificObjectives) setObjectives(data.specificObjectives.map((o: any) => o.objetivo || o));
      if (data.teamMembers) setTeamMembers(data.teamMembers);
      if (data.contractedServices) setContractedServices(data.contractedServices);
    } catch {
      toast({ title: 'Erro ao carregar projeto', variant: 'destructive' });
      navigate('/projetos');
    }
  };

  const update = (field: string, value: any) => setForm((p) => ({ ...p, [field]: value }));

  const handleSubmit = async () => {
    if (!form.nome || !form.institutionId) {
      toast({ title: 'Nome e instituicao sao obrigatorios', variant: 'destructive' });
      return;
    }
    setSaving(true);
    try {
      const payload = {
        ...form,
        quantidadePublico: form.quantidadePublico ? Number(form.quantidadePublico) : undefined,
        valorTotal: form.valorTotal ? Number(form.valorTotal) : undefined,
        valorRecebido: form.valorRecebido ? Number(form.valorRecebido) : undefined,
        locations: locations.filter((l) => l.cidade),
        objectives: objectives.filter(Boolean),
        teamMembers: teamMembers.filter((t) => t.funcao),
        contractedServices: contractedServices.filter((s) => s.descricao),
      };

      if (isEditing) {
        await api.put(`/projects/${id}`, payload);
        toast({ title: 'Projeto atualizado' });
        navigate(`/projetos/${id}`);
      } else {
        const { data } = await api.post('/projects', payload);
        toast({ title: 'Projeto criado' });
        navigate(`/projetos/${data.id}`);
      }
    } catch {
      toast({ title: 'Erro ao salvar projeto', variant: 'destructive' });
    } finally {
      setSaving(false);
    }
  };

  const addLocation = () => setLocations((p) => [...p, { cidade: '', estado: '' }]);
  const updLocation = (i: number, f: string, v: string) => setLocations((p) => p.map((item, idx) => idx === i ? { ...item, [f]: v } : item));
  const rmLocation = (i: number) => setLocations((p) => p.filter((_, idx) => idx !== i));

  const addObjective = () => setObjectives((p) => [...p, '']);
  const updObjective = (i: number, v: string) => setObjectives((p) => p.map((o, idx) => idx === i ? v : o));
  const rmObjective = (i: number) => setObjectives((p) => p.filter((_, idx) => idx !== i));

  const addMember = () => setTeamMembers((p) => [...p, { funcao: '', quantidade: 1, descricao: '' }]);
  const updMember = (i: number, f: string, v: any) => setTeamMembers((p) => p.map((m, idx) => idx === i ? { ...m, [f]: v } : m));
  const rmMember = (i: number) => setTeamMembers((p) => p.filter((_, idx) => idx !== i));

  const addService = () => setContractedServices((p) => [...p, { tipoContratacao: 'PF', descricao: '', periodoExecucao: '', unidadePeriodo: 'mes', tipoPagamento: 'unico', valor: '' }]);
  const updService = (i: number, f: string, v: any) => setContractedServices((p) => p.map((s, idx) => idx === i ? { ...s, [f]: v } : s));
  const rmService = (i: number) => setContractedServices((p) => p.filter((_, idx) => idx !== i));

  const next = () => setStep((s) => Math.min(s + 1, 6));
  const prev = () => setStep((s) => Math.max(s - 1, 1));

  return (
    <div className="mx-auto max-w-4xl space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/projetos')}><ArrowLeft className="h-4 w-4" /></Button>
        <div>
          <h2 className="text-3xl font-bold tracking-tight">{isEditing ? 'Editar Projeto' : 'Novo Projeto'}</h2>
          <p className="text-muted-foreground">{isEditing ? 'Altere os dados do projeto' : 'Preencha os dados para cadastrar'}</p>
        </div>
      </div>

      <div className="flex gap-2">
        {STEPS.map((s) => (
          <button key={s.id} onClick={() => setStep(s.id)} className={`flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors ${step === s.id ? 'bg-primary text-primary-foreground' : step > s.id ? 'bg-primary/20 text-primary' : 'bg-muted text-muted-foreground'}`}>
            {s.label}
          </button>
        ))}
      </div>

      <Card>
        <CardHeader><CardTitle>{STEPS[step - 1]?.label}</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          {step === 1 && (
            <>
              <div className="space-y-2">
                <Label>Instituicao *</Label>
                <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={form.institutionId} onChange={(e) => update('institutionId', e.target.value)}>
                  <option value="">Selecione...</option>
                  {institutions.map((i: any) => <option key={i.id} value={i.id}>{i.razaoSocial}</option>)}
                </select>
              </div>
              <div className="space-y-2">
                <Label>Nome do Projeto *</Label>
                <Input value={form.nome} onChange={(e) => update('nome', e.target.value)} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Fonte do Recurso</Label>
                  <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={form.fonte} onChange={(e) => update('fonte', e.target.value)}>
                    <option value="">Selecione...</option>
                    <option value="Emenda Federal">Emenda Federal</option>
                    <option value="Emenda Estadual">Emenda Estadual</option>
                    <option value="Emenda Municipal">Emenda Municipal</option>
                    <option value="Recurso Proprio">Recurso Proprio</option>
                    <option value="Convenio">Convenio</option>
                    <option value="Termo de Fomento">Termo de Fomento</option>
                  </select>
                </div>
                <div className="space-y-2">
                  <Label>Fonte de Recurso (vinculo)</Label>
                  <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={form.fundingSourceId} onChange={(e) => update('fundingSourceId', e.target.value)}>
                    <option value="">Nenhuma</option>
                    {fundingSources.map((fs: any) => <option key={fs.id} value={fs.id}>{fs.nome}</option>)}
                  </select>
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Codigo do Projeto</Label>
                  <Input value={form.codigo} onChange={(e) => update('codigo', e.target.value)} placeholder="Ex: 001/2026" />
                </div>
                <div className="space-y-2">
                  <Label>Numero da Proposta</Label>
                  <Input value={form.numeroProposta} onChange={(e) => update('numeroProposta', e.target.value)} />
                </div>
              </div>
              <div className="flex items-center gap-2">
                <input type="checkbox" id="gerarCodigo" checked={form.gerarCodigo} onChange={(e) => update('gerarCodigo', e.target.checked)} className="rounded" />
                <Label htmlFor="gerarCodigo">Gerar codigo automaticamente</Label>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Status</Label>
                  <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={form.status} onChange={(e) => update('status', e.target.value)}>
                    {['RASCUNHO', 'EM_ANALISE', 'APROVADO', 'EM_EXECUCAO', 'SUSPENSO', 'FINALIZADO'].map((s) => <option key={s} value={s}>{s}</option>)}
                  </select>
                </div>
                <div className="space-y-2">
                  <Label>Responsavel</Label>
                  <Input value={form.responsavel} onChange={(e) => update('responsavel', e.target.value)} />
                </div>
              </div>
            </>
          )}

          {step === 2 && (
            <>
              <div className="space-y-2">
                <Label>Descricao</Label>
                <textarea className="flex h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm" value={form.descricao} onChange={(e) => update('descricao', e.target.value)} />
              </div>
              <div className="space-y-2">
                <Label>Objetivo Geral</Label>
                <textarea className="flex h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm" value={form.objetivoGeral} onChange={(e) => update('objetivoGeral', e.target.value)} />
              </div>
              <div className="space-y-2">
                <Label>Publico Alvo</Label>
                <Input value={form.publicoAlvo} onChange={(e) => update('publicoAlvo', e.target.value)} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Quantidade de Publico</Label>
                  <Input type="number" value={form.quantidadePublico} onChange={(e) => update('quantidadePublico', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Parlamentar (emenda)</Label>
                  <Input value={form.parlamentar} onChange={(e) => update('parlamentar', e.target.value)} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Secretaria</Label>
                <Input value={form.secretaria} onChange={(e) => update('secretaria', e.target.value)} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Valor Total</Label>
                  <Input type="number" step="0.01" value={form.valorTotal} onChange={(e) => update('valorTotal', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Valor Recebido</Label>
                  <Input type="number" step="0.01" value={form.valorRecebido} onChange={(e) => update('valorRecebido', e.target.value)} />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Data de Inicio</Label>
                  <Input type="date" value={form.dataInicio} onChange={(e) => update('dataInicio', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Data de Fim</Label>
                  <Input type="date" value={form.dataFim} onChange={(e) => update('dataFim', e.target.value)} />
                </div>
              </div>
            </>
          )}

          {step === 3 && (
            <div className="space-y-4">
              {locations.map((loc, i) => (
                <div key={i} className="flex gap-2 items-end">
                  <div className="flex-1 space-y-2">
                    <Label>Cidade</Label>
                    <Input value={loc.cidade} onChange={(e) => updLocation(i, 'cidade', e.target.value)} />
                  </div>
                  <div className="w-20 space-y-2">
                    <Label>UF</Label>
                    <Input value={loc.estado} onChange={(e) => updLocation(i, 'estado', e.target.value.toUpperCase().slice(0, 2))} maxLength={2} />
                  </div>
                  {locations.length > 1 && <Button variant="ghost" size="icon" onClick={() => rmLocation(i)}><Trash2 className="h-4 w-4" /></Button>}
                </div>
              ))}
              <Button variant="outline" size="sm" onClick={addLocation}><Plus className="mr-2 h-4 w-4" /> Adicionar Local</Button>
            </div>
          )}

          {step === 4 && (
            <div className="space-y-4">
              {objectives.map((obj, i) => (
                <div key={i} className="flex gap-2 items-end">
                  <div className="flex-1 space-y-2">
                    <Label>Objetivo Especifico {i + 1}</Label>
                    <textarea className="flex h-16 w-full rounded-md border bg-transparent px-3 py-2 text-sm" value={obj} onChange={(e) => updObjective(i, e.target.value)} />
                  </div>
                  {objectives.length > 1 && <Button variant="ghost" size="icon" onClick={() => rmObjective(i)}><Trash2 className="h-4 w-4" /></Button>}
                </div>
              ))}
              <Button variant="outline" size="sm" onClick={addObjective}><Plus className="mr-2 h-4 w-4" /> Adicionar Objetivo</Button>
            </div>
          )}

          {step === 5 && (
            <div className="space-y-4">
              {teamMembers.map((m, i) => (
                <div key={i} className="flex gap-2 items-end rounded-lg border p-3">
                  <div className="flex-1 space-y-2">
                    <Label>Funcao</Label>
                    <Input value={m.funcao} onChange={(e) => updMember(i, 'funcao', e.target.value)} />
                  </div>
                  <div className="w-24 space-y-2">
                    <Label>Quantidade</Label>
                    <Input type="number" min={1} value={m.quantidade} onChange={(e) => updMember(i, 'quantidade', Number(e.target.value))} />
                  </div>
                  <div className="flex-[2] space-y-2">
                    <Label>Descricao</Label>
                    <Input value={m.descricao} onChange={(e) => updMember(i, 'descricao', e.target.value)} />
                  </div>
                  {teamMembers.length > 1 && <Button variant="ghost" size="icon" onClick={() => rmMember(i)}><Trash2 className="h-4 w-4" /></Button>}
                </div>
              ))}
              <Button variant="outline" size="sm" onClick={addMember}><Plus className="mr-2 h-4 w-4" /> Adicionar Membro</Button>
            </div>
          )}

          {step === 6 && (
            <div className="space-y-4">
              {contractedServices.map((s, i) => (
                <div key={i} className="space-y-3 rounded-lg border p-3">
                  <div className="flex gap-2 items-start">
                    <div className="flex-1 space-y-2">
                      <Label>Descricao do Servico</Label>
                      <Input value={s.descricao} onChange={(e) => updService(i, 'descricao', e.target.value)} />
                    </div>
                    {contractedServices.length > 1 && <Button variant="ghost" size="icon" onClick={() => rmService(i)}><Trash2 className="h-4 w-4" /></Button>}
                  </div>
                  <div className="grid grid-cols-4 gap-3">
                    <div className="space-y-2">
                      <Label>Tipo</Label>
                      <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={s.tipoContratacao} onChange={(e) => updService(i, 'tipoContratacao', e.target.value)}>
                        <option value="PF">PF</option>
                        <option value="PJ">PJ</option>
                      </select>
                    </div>
                    <div className="space-y-2">
                      <Label>Periodo</Label>
                      <Input type="number" value={s.periodoExecucao} onChange={(e) => updService(i, 'periodoExecucao', e.target.value)} />
                    </div>
                    <div className="space-y-2">
                      <Label>Unidade</Label>
                      <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={s.unidadePeriodo} onChange={(e) => updService(i, 'unidadePeriodo', e.target.value)}>
                        <option value="dia">Dia</option><option value="semana">Semana</option><option value="mes">Mes</option><option value="ano">Ano</option>
                      </select>
                    </div>
                    <div className="space-y-2">
                      <Label>Valor</Label>
                      <Input type="number" step="0.01" value={s.valor} onChange={(e) => updService(i, 'valor', e.target.value)} />
                    </div>
                  </div>
                  <div className="space-y-2">
                    <Label>Pagamento</Label>
                    <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={s.tipoPagamento} onChange={(e) => updService(i, 'tipoPagamento', e.target.value)}>
                      <option value="mensal">Mensal</option><option value="unico">Unico</option>
                    </select>
                  </div>
                </div>
              ))}
              <Button variant="outline" size="sm" onClick={addService}><Plus className="mr-2 h-4 w-4" /> Adicionar Servico</Button>
            </div>
          )}

          <div className="flex justify-between pt-4">
            <Button variant="outline" onClick={step === 1 ? () => navigate('/projetos') : prev}>
              <ChevronLeft className="mr-2 h-4 w-4" /> {step === 1 ? 'Cancelar' : 'Anterior'}
            </Button>
            {step < 6 ? (
              <Button onClick={next}>Proximo <ChevronRight className="ml-2 h-4 w-4" /></Button>
            ) : (
              <Button onClick={handleSubmit} disabled={saving}>
                <Save className="mr-2 h-4 w-4" /> {saving ? 'Salvando...' : isEditing ? 'Atualizar' : 'Criar Projeto'}
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

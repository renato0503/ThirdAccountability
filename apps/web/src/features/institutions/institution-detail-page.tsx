import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import { maskCnpj, maskPhone } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Input } from '@/components/ui/input';
import { toast } from '@/hooks/use-toast';
import {
  ArrowLeft, Edit, Trash2, Building2, MapPin, Mail, Phone, Globe,
  Instagram, Banknote, User, FileText,
} from 'lucide-react';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';

interface Institution {
  id: string;
  razaoSocial: string;
  nomeFantasia?: string;
  cnpj: string;
  email?: string;
  telefone?: string;
  site?: string;
  instagram?: string;
  endereco?: string;
  numero?: string;
  complemento?: string;
  bairro?: string;
  municipio?: string;
  estado?: string;
  cep?: string;
  banco?: string;
  agencia?: string;
  contaCorrente?: string;
  tipoConta?: string;
  chavePix?: string;
  representanteLegal?: string;
  presidenteCpf?: string;
  presidenteRg?: string;
  presidenteTelefone?: string;
  presidenteEmail?: string;
  active: boolean;
  createdAt: any;
}

interface Director {
  id: string;
  nome: string;
  cargo: string;
  dataInicio?: string;
  dataFim?: string;
  ementa?: string;
}

interface ProjectHistory {
  id: string;
  projeto: string;
  financiador?: string;
  periodo?: string;
  valor?: number;
}

export function InstitutionDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [institution, setInstitution] = useState<Institution | null>(null);
  const [directors, setDirectors] = useState<Director[]>([]);
  const [history, setHistory] = useState<ProjectHistory[]>([]);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState('dados');

  // Director form state
  const [showDirectorForm, setShowDirectorForm] = useState(false);
  const [editingDirector, setEditingDirector] = useState<Director | null>(null);
  const [directorForm, setDirectorForm] = useState({ nome: '', cargo: '', dataInicio: '', dataFim: '', ementa: '' });

  // History form state
  const [showHistoryForm, setShowHistoryForm] = useState(false);
  const [historyForm, setHistoryForm] = useState({ projeto: '', financiador: '', periodo: '', valor: '' });

  useEffect(() => {
    loadAll();
  }, [id]);

  const loadAll = async () => {
    if (!id) return;
    setLoading(true);
    try {
      const [instRes, dirRes, histRes] = await Promise.all([
        api.get(`/institutions/${id}`),
        api.get(`/institutions/${id}/directors`),
        api.get(`/institutions/${id}/project-history`),
      ]);
      setInstitution(instRes.data);
      setDirectors(dirRes.data);
      setHistory(histRes.data);
    } catch {
      toast({ title: 'Erro ao carregar instituicao', variant: 'destructive' });
      navigate('/instituicoes');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!confirm('Tem certeza que deseja excluir esta instituicao?')) return;
    try {
      await api.delete(`/institutions/${id}`);
      toast({ title: 'Instituicao excluida' });
      navigate('/instituicoes');
    } catch {
      toast({ title: 'Erro ao excluir', variant: 'destructive' });
    }
  };

  const saveDirector = async () => {
    if (!directorForm.nome || !directorForm.cargo) {
      toast({ title: 'Nome e cargo sao obrigatorios', variant: 'destructive' });
      return;
    }
    try {
      if (editingDirector) {
        await api.put(`/institutions/${id}/directors/${editingDirector.id}`, directorForm);
      } else {
        await api.post(`/institutions/${id}/directors`, directorForm);
      }
      setShowDirectorForm(false);
      setEditingDirector(null);
      setDirectorForm({ nome: '', cargo: '', dataInicio: '', dataFim: '', ementa: '' });
      const { data } = await api.get(`/institutions/${id}/directors`);
      setDirectors(data);
      toast({ title: editingDirector ? 'Diretor atualizado' : 'Diretor adicionado' });
    } catch {
      toast({ title: 'Erro ao salvar diretor', variant: 'destructive' });
    }
  };

  const deleteDirector = async (directorId: string) => {
    if (!confirm('Excluir este diretor?')) return;
    try {
      await api.delete(`/institutions/${id}/directors/${directorId}`);
      setDirectors((prev) => prev.filter((d) => d.id !== directorId));
      toast({ title: 'Diretor excluido' });
    } catch {
      toast({ title: 'Erro ao excluir diretor', variant: 'destructive' });
    }
  };

  const saveHistory = async () => {
    if (!historyForm.projeto) {
      toast({ title: 'Nome do projeto e obrigatorio', variant: 'destructive' });
      return;
    }
    try {
      await api.post(`/institutions/${id}/project-history`, historyForm);
      setShowHistoryForm(false);
      setHistoryForm({ projeto: '', financiador: '', periodo: '', valor: '' });
      const { data } = await api.get(`/institutions/${id}/project-history`);
      setHistory(data);
      toast({ title: 'Historico adicionado' });
    } catch {
      toast({ title: 'Erro ao salvar historico', variant: 'destructive' });
    }
  };

  if (loading) {
    return <div className="flex items-center justify-center h-64 text-muted-foreground">Carregando...</div>;
  }
  if (!institution) return null;

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/instituicoes')}>
            <ArrowLeft className="h-4 w-4" />
          </Button>
          <div>
            <div className="flex items-center gap-3">
              <h2 className="text-3xl font-bold tracking-tight">{institution.razaoSocial}</h2>
              <Badge variant={institution.active ? 'success' : 'secondary'}>
                {institution.active ? 'Ativa' : 'Inativa'}
              </Badge>
            </div>
            <p className="text-muted-foreground">{maskCnpj(institution.cnpj)}</p>
          </div>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => navigate(`/instituicoes/${id}/editar`)}>
            <Edit className="mr-2 h-4 w-4" /> Editar
          </Button>
          <Button variant="destructive" onClick={handleDelete}>
            <Trash2 className="mr-2 h-4 w-4" /> Excluir
          </Button>
        </div>
      </div>

      <Tabs value={tab} onValueChange={setTab}>
        <TabsList>
          <TabsTrigger value="dados">Dados Cadastrais</TabsTrigger>
          <TabsTrigger value="endereco">Endereco</TabsTrigger>
          <TabsTrigger value="banco">Dados Bancarios</TabsTrigger>
          <TabsTrigger value="presidente">Presidente</TabsTrigger>
          <TabsTrigger value="diretores">Diretores ({directors.length})</TabsTrigger>
          <TabsTrigger value="historico">Historico de Projetos ({history.length})</TabsTrigger>
        </TabsList>

        <TabsContent value="dados">
          <Card>
            <CardContent className="grid grid-cols-2 gap-6 pt-6">
              <InfoItem icon={Building2} label="Razao Social" value={institution.razaoSocial} />
              <InfoItem icon={Building2} label="Nome Fantasia" value={institution.nomeFantasia} />
              <InfoItem icon={FileText} label="CNPJ" value={maskCnpj(institution.cnpj)} />
              <InfoItem icon={Mail} label="Email" value={institution.email} />
              <InfoItem icon={Phone} label="Telefone" value={maskPhone(institution.telefone || '')} />
              <InfoItem icon={Globe} label="Site" value={institution.site} />
              <InfoItem icon={Instagram} label="Instagram" value={institution.instagram} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="endereco">
          <Card>
            <CardContent className="space-y-4 pt-6">
              <InfoItem icon={MapPin} label="Logradouro" value={`${institution.endereco || ''}, ${institution.numero || ''}`} />
              <InfoItem icon={MapPin} label="Complemento" value={institution.complemento} />
              <InfoItem icon={MapPin} label="Bairro" value={institution.bairro} />
              <InfoItem icon={MapPin} label="Cidade/UF" value={`${institution.municipio || ''}/${institution.estado || ''}`} />
              <InfoItem icon={MapPin} label="CEP" value={institution.cep} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="banco">
          <Card>
            <CardContent className="space-y-4 pt-6">
              <InfoItem icon={Banknote} label="Banco" value={institution.banco} />
              <InfoItem icon={Banknote} label="Agencia" value={institution.agencia} />
              <InfoItem icon={Banknote} label="Conta" value={institution.contaCorrente} />
              <InfoItem icon={Banknote} label="Tipo" value={institution.tipoConta} />
              <InfoItem icon={Banknote} label="Chave PIX" value={institution.chavePix} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="presidente">
          <Card>
            <CardContent className="space-y-4 pt-6">
              <InfoItem icon={User} label="Representante Legal" value={institution.representanteLegal} />
              <InfoItem icon={User} label="CPF" value={institution.presidenteCpf} />
              <InfoItem icon={User} label="RG" value={institution.presidenteRg} />
              <InfoItem icon={Phone} label="Telefone" value={maskPhone(institution.presidenteTelefone || '')} />
              <InfoItem icon={Mail} label="Email" value={institution.presidenteEmail} />
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="diretores">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <CardTitle className="text-lg">Diretores</CardTitle>
              <Button size="sm" onClick={() => { setEditingDirector(null); setDirectorForm({ nome: '', cargo: '', dataInicio: '', dataFim: '', ementa: '' }); setShowDirectorForm(true); }}>
                Novo Diretor
              </Button>
            </CardHeader>
            <CardContent className="space-y-3">
              {showDirectorForm && (
                <div className="rounded-lg border p-4 space-y-3">
                  <div className="grid grid-cols-2 gap-3">
                    <Input placeholder="Nome" value={directorForm.nome} onChange={(e) => setDirectorForm((p) => ({ ...p, nome: e.target.value }))} />
                    <Input placeholder="Cargo" value={directorForm.cargo} onChange={(e) => setDirectorForm((p) => ({ ...p, cargo: e.target.value }))} />
                    <Input type="date" placeholder="Data Inicio" value={directorForm.dataInicio} onChange={(e) => setDirectorForm((p) => ({ ...p, dataInicio: e.target.value }))} />
                    <Input type="date" placeholder="Data Fim" value={directorForm.dataFim} onChange={(e) => setDirectorForm((p) => ({ ...p, dataFim: e.target.value }))} />
                    <Input className="col-span-2" placeholder="Ementa (opcional)" value={directorForm.ementa} onChange={(e) => setDirectorForm((p) => ({ ...p, ementa: e.target.value }))} />
                  </div>
                  <div className="flex gap-2">
                    <Button size="sm" onClick={saveDirector}>Salvar</Button>
                    <Button size="sm" variant="ghost" onClick={() => setShowDirectorForm(false)}>Cancelar</Button>
                  </div>
                </div>
              )}
              {directors.length === 0 ? (
                <p className="text-sm text-muted-foreground">Nenhum diretor cadastrado</p>
              ) : (
                directors.map((dir) => (
                  <div key={dir.id} className="flex items-center justify-between rounded-lg border p-3">
                    <div>
                      <p className="font-medium">{dir.nome}</p>
                      <p className="text-sm text-muted-foreground">{dir.cargo}</p>
                      {dir.dataInicio && (
                        <p className="text-xs text-muted-foreground">
                          {dir.dataInicio} {dir.dataFim ? `a ${dir.dataFim}` : ''}
                        </p>
                      )}
                    </div>
                    <div className="flex gap-1">
                      <Button variant="ghost" size="sm" onClick={() => {
                        setEditingDirector(dir);
                        setDirectorForm({ nome: dir.nome, cargo: dir.cargo, dataInicio: dir.dataInicio || '', dataFim: dir.dataFim || '', ementa: dir.ementa || '' });
                        setShowDirectorForm(true);
                      }}>Editar</Button>
                      <Button variant="ghost" size="sm" onClick={() => deleteDirector(dir.id)}>Excluir</Button>
                    </div>
                  </div>
                ))
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="historico">
          <Card>
            <CardHeader className="flex flex-row items-center justify-between">
              <CardTitle className="text-lg">Historico de Projetos</CardTitle>
              <Button size="sm" onClick={() => setShowHistoryForm(true)}>Adicionar</Button>
            </CardHeader>
            <CardContent className="space-y-3">
              {showHistoryForm && (
                <div className="rounded-lg border p-4 space-y-3">
                  <div className="grid grid-cols-2 gap-3">
                    <Input placeholder="Nome do Projeto" value={historyForm.projeto} onChange={(e) => setHistoryForm((p) => ({ ...p, projeto: e.target.value }))} />
                    <Input placeholder="Financiador" value={historyForm.financiador} onChange={(e) => setHistoryForm((p) => ({ ...p, financiador: e.target.value }))} />
                    <Input placeholder="Periodo" value={historyForm.periodo} onChange={(e) => setHistoryForm((p) => ({ ...p, periodo: e.target.value }))} />
                    <Input type="number" placeholder="Valor" value={historyForm.valor} onChange={(e) => setHistoryForm((p) => ({ ...p, valor: e.target.value }))} />
                  </div>
                  <div className="flex gap-2">
                    <Button size="sm" onClick={saveHistory}>Salvar</Button>
                    <Button size="sm" variant="ghost" onClick={() => setShowHistoryForm(false)}>Cancelar</Button>
                  </div>
                </div>
              )}
              {history.length === 0 ? (
                <p className="text-sm text-muted-foreground">Nenhum historico cadastrado</p>
              ) : (
                history.map((h) => (
                  <div key={h.id} className="rounded-lg border p-3">
                    <p className="font-medium">{h.projeto}</p>
                    <p className="text-sm text-muted-foreground">{h.financiador} {h.periodo ? `- ${h.periodo}` : ''}</p>
                  </div>
                ))
              )}
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  );
}

function InfoItem({ icon: Icon, label, value }: { icon: any; label: string; value?: string }) {
  if (!value) return null;
  return (
    <div className="flex items-center gap-3">
      <Icon className="h-4 w-4 text-muted-foreground shrink-0" />
      <div>
        <p className="text-xs text-muted-foreground">{label}</p>
        <p className="text-sm font-medium">{value}</p>
      </div>
    </div>
  );
}

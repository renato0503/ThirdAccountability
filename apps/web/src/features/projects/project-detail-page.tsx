import { useState, useEffect } from 'react';
import { useParams, useNavigate } from 'react-router-dom';
import api from '@/lib/api';
import { formatDate, formatCurrency, getStatusColor, getStatusText } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs';
import { ArrowLeft, Edit, Trash2, FolderKanban, MapPin, Target, Users, Briefcase, CalendarDays, DollarSign } from 'lucide-react';
import { GoalsSection } from '@/features/goals/goals-section';

interface Project {
  id: string; nome: string; codigo?: string; status: string;
  institutionId: string; institutionName?: string;
  fundingSourceId?: string; numeroProposta?: string; fonte?: string;
  parlamentar?: string; secretaria?: string; descricao?: string;
  objetivoGeral?: string; publicoAlvo?: string;
  quantidadePublico?: number; valorTotal?: number; valorRecebido?: number;
  valorExecutado?: number; dataInicio?: string; dataFim?: string;
  responsavel?: string; localExecucao?: string;
  createdAt?: any; executionLocations?: any[]; specificObjectives?: any[];
  teamMembers?: any[]; contractedServices?: any[];
}

export function ProjectDetailPage() {
  const { id } = useParams();
  const navigate = useNavigate();
  const [project, setProject] = useState<Project | null>(null);
  const [loading, setLoading] = useState(true);
  const [tab, setTab] = useState('geral');

  useEffect(() => { loadProject(); }, [id]);

  const loadProject = async () => {
    if (!id) return;
    setLoading(true);
    try {
      const [projRes, locRes, objRes, teamRes, svcRes] = await Promise.all([
        api.get(`/projects/${id}`),
        api.get(`/projects/${id}/execution-locations`),
        api.get(`/projects/${id}/specific-objectives`),
        api.get(`/projects/${id}/team-members`),
        api.get(`/projects/${id}/contracted-services`),
      ]);
      setProject({ ...projRes.data, executionLocations: locRes.data, specificObjectives: objRes.data, teamMembers: teamRes.data, contractedServices: svcRes.data });
    } catch {
      toast({ title: 'Erro ao carregar projeto', variant: 'destructive' });
      navigate('/projetos');
    } finally {
      setLoading(false);
    }
  };

  const handleDelete = async () => {
    if (!confirm('Excluir este projeto?')) return;
    try {
      await api.delete(`/projects/${id}`);
      toast({ title: 'Projeto excluido' });
      navigate('/projetos');
    } catch {
      toast({ title: 'Erro ao excluir', variant: 'destructive' });
    }
  };

  if (loading) return <div className="flex items-center justify-center h-64 text-muted-foreground">Carregando...</div>;
  if (!project) return null;

  const progress = project.valorTotal ? Math.round((((project.valorExecutado || 0) / project.valorTotal) * 100)) : 0;

  return (
    <div className="space-y-6">
      <div className="flex items-start justify-between">
        <div className="flex items-center gap-4">
          <Button variant="ghost" size="icon" onClick={() => navigate('/projetos')}><ArrowLeft className="h-4 w-4" /></Button>
          <div>
            <div className="flex items-center gap-3">
              <h2 className="text-3xl font-bold tracking-tight">{project.nome}</h2>
              <Badge className={getStatusColor(project.status)}>{getStatusText(project.status)}</Badge>
            </div>
            <p className="text-muted-foreground">{project.codigo && <>{project.codigo} &middot; </>}{project.fonte}</p>
          </div>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => navigate(`/projetos/${id}/editar`)}><Edit className="mr-2 h-4 w-4" /> Editar</Button>
          <Button variant="destructive" onClick={handleDelete}><Trash2 className="mr-2 h-4 w-4" /> Excluir</Button>
        </div>
      </div>

      {/* Stats cards */}
      <div className="grid gap-4 md:grid-cols-4">
        <Card><CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Valor Total</CardTitle></CardHeader><CardContent><div className="text-2xl font-bold">{formatCurrency(project.valorTotal || 0)}</div></CardContent></Card>
        <Card><CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Valor Recebido</CardTitle></CardHeader><CardContent><div className="text-2xl font-bold text-green-600">{formatCurrency(project.valorRecebido || 0)}</div></CardContent></Card>
        <Card><CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Valor Executado</CardTitle></CardHeader><CardContent><div className="text-2xl font-bold text-blue-600">{formatCurrency(project.valorExecutado || 0)}</div></CardContent></Card>
        <Card><CardHeader className="pb-2"><CardTitle className="text-sm font-medium">Exec. Financeira</CardTitle></CardHeader><CardContent><div className="text-2xl font-bold">{progress}%</div></CardContent></Card>
      </div>

      <Tabs value={tab} onValueChange={setTab}>
        <TabsList>
          <TabsTrigger value="geral">Geral</TabsTrigger>
          <TabsTrigger value="locais">Locais</TabsTrigger>
          <TabsTrigger value="objetivos">Objetivos</TabsTrigger>
          <TabsTrigger value="equipe">Equipe</TabsTrigger>
          <TabsTrigger value="metas">Metas</TabsTrigger>
          <TabsTrigger value="servicos">Servicos</TabsTrigger>
        </TabsList>

        <TabsContent value="geral">
          <Card>
            <CardContent className="space-y-4 pt-6">
              <div className="grid grid-cols-2 gap-4">
                <InfoItem icon={FolderKanban} label="Nome" value={project.nome} />
                <InfoItem icon={FolderKanban} label="Codigo" value={project.codigo} />
                <InfoItem icon={FolderKanban} label="Instituicao" value={project.institutionName} />
                <InfoItem icon={DollarSign} label="Fonte" value={project.fonte} />
                <InfoItem icon={DollarSign} label="Parlamentar" value={project.parlamentar} />
                <InfoItem icon={DollarSign} label="Secretaria" value={project.secretaria} />
                <InfoItem icon={FileText} label="Numero Proposta" value={project.numeroProposta} />
                <InfoItem icon={CalendarDays} label="Periodo" value={project.dataInicio && project.dataFim ? `${formatDate(project.dataInicio)} a ${formatDate(project.dataFim)}` : undefined} />
                <InfoItem icon={Users} label="Publico Alvo" value={project.publicoAlvo} />
                <InfoItem icon={Users} label="Quantidade" value={project.quantidadePublico?.toString()} />
                <InfoItem icon={User} label="Responsavel" value={project.responsavel} />
              </div>
              {project.descricao && (
                <div><p className="text-xs text-muted-foreground">Descricao</p><p className="text-sm mt-1">{project.descricao}</p></div>
              )}
              {project.objetivoGeral && (
                <div><p className="text-xs text-muted-foreground">Objetivo Geral</p><p className="text-sm mt-1">{project.objetivoGeral}</p></div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="locais">
          <Card>
            <CardContent className="pt-6">
              {project.executionLocations?.length === 0 ? <p className="text-muted-foreground">Nenhum local cadastrado</p> : (
                <div className="space-y-2">
                  {project.executionLocations?.map((loc: any, i: number) => (
                    <div key={i} className="flex items-center gap-3 rounded-lg border p-3">
                      <MapPin className="h-4 w-4 text-muted-foreground" />
                      <span>{loc.cidade}{loc.estado ? `/${loc.estado}` : ''}</span>
                    </div>
                  ))}
                </div>
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="objetivos">
          <Card>
            <CardContent className="pt-6 space-y-3">
              {project.specificObjectives?.length === 0 ? <p className="text-muted-foreground">Nenhum objetivo cadastrado</p> : (
                project.specificObjectives?.map((obj: any, i: number) => (
                  <div key={i} className="flex items-start gap-3 rounded-lg border p-3">
                    <Target className="h-4 w-4 mt-1 text-muted-foreground shrink-0" />
                    <p className="text-sm">{obj.objetivo || obj}</p>
                  </div>
                ))
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="equipe">
          <Card>
            <CardContent className="pt-6 space-y-3">
              {project.teamMembers?.length === 0 ? <p className="text-muted-foreground">Nenhum membro cadastrado</p> : (
                project.teamMembers?.map((m: any, i: number) => (
                  <div key={i} className="flex items-center justify-between rounded-lg border p-3">
                    <div className="flex items-center gap-3">
                      <Users className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="font-medium">{m.funcao}</p>
                        <p className="text-sm text-muted-foreground">{m.quantidade}x {m.descricao}</p>
                      </div>
                    </div>
                  </div>
                ))
              )}
            </CardContent>
          </Card>
        </TabsContent>

        <TabsContent value="metas">
          <GoalsSection projectId={project.id} />
        </TabsContent>
        <TabsContent value="servicos">
          <Card>
            <CardContent className="pt-6 space-y-3">
              {project.contractedServices?.length === 0 ? <p className="text-muted-foreground">Nenhum servico cadastrado</p> : (
                project.contractedServices?.map((s: any, i: number) => (
                  <div key={i} className="rounded-lg border p-3">
                    <div className="flex items-center gap-3">
                      <Briefcase className="h-4 w-4 text-muted-foreground" />
                      <div>
                        <p className="font-medium">{s.descricao}</p>
                        <p className="text-sm text-muted-foreground">
                          {s.tipoContratacao} &middot; {s.periodoExecucao ? `${s.periodoExecucao} ${s.unidadePeriodo}(s)` : ''} &middot; {s.tipoPagamento} &middot; {s.valor ? formatCurrency(Number(s.valor)) : ''}
                        </p>
                      </div>
                    </div>
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
  return <div className="flex items-center gap-3"><Icon className="h-4 w-4 text-muted-foreground shrink-0" /><div><p className="text-xs text-muted-foreground">{label}</p><p className="text-sm font-medium">{value}</p></div></div>;
}

// Missing icons used
import { FileText, User } from 'lucide-react';

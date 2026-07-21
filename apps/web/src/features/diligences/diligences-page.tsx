import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '@/lib/api';
import { formatDate, getStatusText } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Scale, MessageSquare, CheckCircle, RotateCcw } from 'lucide-react';

interface Diligence {
  id: string; tipo: string; descricao: string; prazoResposta?: string;
  status: string; resposta?: string; parecer?: string;
  dataResposta?: any; dataParecer?: any; createdAt: any;
  anexoPath?: string;
}

export function DiligencesPage() {
  const [searchParams] = useSearchParams();
  const projectId = searchParams.get('projectId');
  const [diligences, setDiligences] = useState<Diligence[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ tipo: 'DOCUMENTAL', descricao: '', prazoResposta: '' });
  const [responding, setResponding] = useState<string | null>(null);
  const [respostaText, setRespostaText] = useState('');
  const [closing, setClosing] = useState<string | null>(null);
  const [parecerText, setParecerText] = useState('');

  useEffect(() => { if (projectId) loadDiligences(); }, [projectId]);

  const loadDiligences = async () => {
    setLoading(true);
    try {
      const { data } = await api.get(`/projects/${projectId}/diligences`);
      setDiligences(data);
    } catch { toast({ title: 'Erro ao carregar diligencias', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  const createDiligence = async () => {
    if (!form.descricao) return;
    try {
      await api.post(`/projects/${projectId}/diligences`, form);
      setShowForm(false);
      setForm({ tipo: 'DOCUMENTAL', descricao: '', prazoResposta: '' });
      loadDiligences();
      toast({ title: 'Diligencia criada' });
    } catch { toast({ title: 'Erro ao criar', variant: 'destructive' }); }
  };

  const respond = async (dilId: string) => {
    try {
      await api.post(`/projects/${projectId}/diligences/${dilId}/respond`, { resposta: respostaText });
      setResponding(null);
      setRespostaText('');
      loadDiligences();
      toast({ title: 'Resposta enviada' });
    } catch { toast({ title: 'Erro ao responder', variant: 'destructive' }); }
  };

  const closeDiligence = async (dilId: string) => {
    try {
      await api.post(`/projects/${projectId}/diligences/${dilId}/close`, { parecer: parecerText });
      setClosing(null);
      setParecerText('');
      loadDiligences();
      toast({ title: 'Diligencia fechada' });
    } catch { toast({ title: 'Erro ao fechar', variant: 'destructive' }); }
  };

  const reopen = async (dilId: string) => {
    try {
      await api.post(`/projects/${projectId}/diligences/${dilId}/reopen`);
      loadDiligences();
      toast({ title: 'Diligencia reaberta' });
    } catch { toast({ title: 'Erro ao reabrir', variant: 'destructive' }); }
  };

  const statusVariants: Record<string, 'destructive' | 'warning' | 'success' | 'default'> = {
    ABERTA: 'destructive', RESPONDIDA: 'warning', FECHADA: 'success',
  };

  const tipoVariants: Record<string, 'info' | 'warning' | 'default'> = {
    DOCUMENTAL: 'info', TECNICA: 'warning', FINANCEIRA: 'default',
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Diligencias</h2>
          <p className="text-muted-foreground">{projectId ? 'Acompanhe as diligencias do projeto' : 'Selecione um projeto'}</p>
        </div>
        {projectId && (
          <Button onClick={() => setShowForm(!showForm)}>
            <Scale className="mr-2 h-4 w-4" /> Nova Diligencia
          </Button>
        )}
      </div>

      {showForm && projectId && (
        <Card>
          <CardHeader><CardTitle className="text-sm">Nova Diligencia</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <div className="space-y-1">
              <Label>Tipo</Label>
              <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={form.tipo} onChange={(e) => setForm((p) => ({ ...p, tipo: e.target.value }))}>
                <option value="DOCUMENTAL">Documental</option>
                <option value="TECNICA">Tecnica</option>
                <option value="FINANCEIRA">Financeira</option>
              </select>
            </div>
            <div className="space-y-1">
              <Label>Descricao</Label>
              <textarea className="flex h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm" value={form.descricao} onChange={(e) => setForm((p) => ({ ...p, descricao: e.target.value }))} />
            </div>
            <div className="space-y-1">
              <Label>Prazo de Resposta</Label>
              <Input type="date" value={form.prazoResposta} onChange={(e) => setForm((p) => ({ ...p, prazoResposta: e.target.value }))} />
            </div>
            <div className="flex gap-2">
              <Button size="sm" onClick={createDiligence}>Criar</Button>
              <Button size="sm" variant="ghost" onClick={() => setShowForm(false)}>Cancelar</Button>
            </div>
          </CardContent>
        </Card>
      )}

      {!projectId ? (
        <Card><CardContent className="py-12 text-center text-muted-foreground">Selecione um projeto</CardContent></Card>
      ) : (
        <Card>
          <CardHeader><CardTitle className="text-lg">Diligencias ({diligences.length})</CardTitle></CardHeader>
          <CardContent>
            {loading ? <p className="text-muted-foreground">Carregando...</p> : diligences.length === 0 ? (
              <p className="text-muted-foreground">Nenhuma diligencia</p>
            ) : (
              <div className="space-y-4">
                {diligences.map((d) => (
                  <div key={d.id} className="rounded-lg border p-4 space-y-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <Scale className="h-4 w-4 text-muted-foreground" />
                        <Badge variant={tipoVariants[d.tipo]}>{d.tipo}</Badge>
                        <Badge variant={statusVariants[d.status]}>{getStatusText(d.status)}</Badge>
                      </div>
                      <div className="flex items-center gap-1">
                        {d.status === 'FECHADA' && (
                          <Button size="sm" variant="ghost" onClick={() => reopen(d.id)}>
                            <RotateCcw className="h-3 w-3 mr-1" /> Reabrir
                          </Button>
                        )}
                      </div>
                    </div>
                    <p className="text-sm">{d.descricao}</p>
                    <div className="flex gap-4 text-xs text-muted-foreground">
                      <span>Criada: {d.createdAt?.toDate ? formatDate(d.createdAt.toDate()) : ''}</span>
                      {d.prazoResposta && <span>Prazo: {d.prazoResposta}</span>}
                    </div>

                    {/* Timeline */}
                    <div className="space-y-2 border-l-2 pl-4 ml-2">
                      {d.status === 'ABERTA' && responding !== d.id && (
                        <Button size="sm" variant="outline" onClick={() => setResponding(d.id)}>
                          <MessageSquare className="h-3 w-3 mr-1" /> Responder
                        </Button>
                      )}
                      {responding === d.id && (
                        <div className="space-y-2">
                          <textarea className="flex h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm" placeholder="Sua resposta..." value={respostaText} onChange={(e) => setRespostaText(e.target.value)} />
                          <div className="flex gap-2">
                            <Button size="sm" onClick={() => respond(d.id)}>Enviar Resposta</Button>
                            <Button size="sm" variant="ghost" onClick={() => setResponding(null)}>Cancelar</Button>
                          </div>
                        </div>
                      )}

                      {d.resposta && (
                        <div className="text-sm">
                          <p className="font-medium">Resposta:</p>
                          <p className="text-muted-foreground">{d.resposta}</p>
                          {d.dataResposta?.toDate && <p className="text-xs text-muted-foreground">{formatDate(d.dataResposta.toDate())}</p>}
                        </div>
                      )}

                      {d.status === 'RESPONDIDA' && closing !== d.id && (
                        <Button size="sm" variant="outline" onClick={() => setClosing(d.id)}>
                          <CheckCircle className="h-3 w-3 mr-1" /> Dar Parecer
                        </Button>
                      )}
                      {closing === d.id && (
                        <div className="space-y-2">
                          <textarea className="flex h-20 w-full rounded-md border bg-transparent px-3 py-2 text-sm" placeholder="Parecer..." value={parecerText} onChange={(e) => setParecerText(e.target.value)} />
                          <div className="flex gap-2">
                            <Button size="sm" onClick={() => closeDiligence(d.id)}>Fechar Diligencia</Button>
                            <Button size="sm" variant="ghost" onClick={() => setClosing(null)}>Cancelar</Button>
                          </div>
                        </div>
                      )}

                      {d.parecer && (
                        <div className="text-sm">
                          <p className="font-medium">Parecer:</p>
                          <p className="text-muted-foreground">{d.parecer}</p>
                          {d.dataParecer?.toDate && <p className="text-xs text-muted-foreground">{formatDate(d.dataParecer.toDate())}</p>}
                        </div>
                      )}
                    </div>
                  </div>
                ))}
              </div>
            )}
          </CardContent>
        </Card>
      )}
    </div>
  );
}

import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '@/lib/api';
import { formatDate, formatCurrency, getStatusText } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Receipt, Plus, Trash2, Camera } from 'lucide-react';
import { ref, uploadBytes, getDownloadURL } from 'firebase/storage';
import { storage } from '@/lib/firebase';

interface Report {
  id: string; periodo: string; descricao?: string; status: string;
  valor?: number; fotos: string[]; createdAt: any;
}

export function AccountingPage() {
  const [searchParams] = useSearchParams();
  const projectId = searchParams.get('projectId');
  const [reports, setReports] = useState<Report[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [form, setForm] = useState({ periodo: '', descricao: '', valor: '' });
  const [uploadingPhoto, setUploadingPhoto] = useState<string | null>(null);

  useEffect(() => { if (projectId) loadReports(); }, [projectId]);

  const loadReports = async () => {
    setLoading(true);
    try {
      const { data } = await api.get(`/projects/${projectId}/accounting`);
      setReports(data);
    } catch { toast({ title: 'Erro ao carregar relatorios', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  const createReport = async () => {
    if (!form.periodo) return;
    try {
      await api.post(`/projects/${projectId}/accounting`, {
        ...form, valor: form.valor ? Number(form.valor) : undefined,
      });
      setShowForm(false);
      setForm({ periodo: '', descricao: '', valor: '' });
      loadReports();
      toast({ title: 'Relatorio criado' });
    } catch { toast({ title: 'Erro ao criar', variant: 'destructive' }); }
  };

  const uploadPhoto = async (reportId: string, file: File) => {
    setUploadingPhoto(reportId);
    try {
      const path = `projects/${projectId}/accounting/${reportId}/${Date.now()}_${file.name}`;
      const storageRef = ref(storage, path);
      await uploadBytes(storageRef, file);
      const downloadURL = await getDownloadURL(storageRef);
      await api.post(`/projects/${projectId}/accounting/${reportId}/photos`, { photoPath: downloadURL });
      loadReports();
      toast({ title: 'Foto adicionada' });
    } catch { toast({ title: 'Erro ao enviar foto', variant: 'destructive' }); }
    finally { setUploadingPhoto(null); }
  };

  const removePhoto = async (reportId: string, photoIndex: number) => {
    try {
      await api.delete(`/projects/${projectId}/accounting/${reportId}/photos/${photoIndex}`);
      loadReports();
      toast({ title: 'Foto removida' });
    } catch { toast({ title: 'Erro ao remover foto', variant: 'destructive' }); }
  };

  const statusVariants: Record<string, 'warning' | 'success' | 'destructive'> = {
    PENDENTE: 'warning', APROVADA: 'success', REPROVADA: 'destructive',
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Prestacao de Contas</h2>
          <p className="text-muted-foreground">{projectId ? 'Relatorios financeiros do projeto' : 'Selecione um projeto'}</p>
        </div>
        {projectId && (
          <Button onClick={() => setShowForm(!showForm)}>
            <Plus className="mr-2 h-4 w-4" /> Novo Relatorio
          </Button>
        )}
      </div>

      {showForm && projectId && (
        <Card>
          <CardHeader><CardTitle className="text-sm">Novo Relatorio</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <div className="space-y-1">
              <Label>Periodo de Competencia *</Label>
              <Input type="month" value={form.periodo} onChange={(e) => setForm((p) => ({ ...p, periodo: e.target.value }))} />
            </div>
            <div className="space-y-1">
              <Label>Descricao</Label>
              <textarea className="flex h-16 w-full rounded-md border bg-transparent px-3 py-2 text-sm" value={form.descricao} onChange={(e) => setForm((p) => ({ ...p, descricao: e.target.value }))} />
            </div>
            <div className="space-y-1">
              <Label>Valor</Label>
              <Input type="number" step="0.01" value={form.valor} onChange={(e) => setForm((p) => ({ ...p, valor: e.target.value }))} />
            </div>
            <div className="flex gap-2">
              <Button size="sm" onClick={createReport}>Criar</Button>
              <Button size="sm" variant="ghost" onClick={() => setShowForm(false)}>Cancelar</Button>
            </div>
          </CardContent>
        </Card>
      )}

      {!projectId ? (
        <Card><CardContent className="py-12 text-center text-muted-foreground">Selecione um projeto</CardContent></Card>
      ) : (
        <Card>
          <CardHeader><CardTitle className="text-lg">Relatorios ({reports.length})</CardTitle></CardHeader>
          <CardContent>
            {loading ? <p className="text-muted-foreground">Carregando...</p> : reports.length === 0 ? (
              <p className="text-muted-foreground">Nenhum relatorio</p>
            ) : (
              <div className="space-y-4">
                {reports.map((r) => (
                  <div key={r.id} className="rounded-lg border p-4 space-y-3">
                    <div className="flex items-center justify-between">
                      <div className="flex items-center gap-2">
                        <Receipt className="h-4 w-4 text-muted-foreground" />
                        <span className="font-medium">{r.periodo}</span>
                        <Badge variant={statusVariants[r.status] || 'secondary'}>{getStatusText(r.status)}</Badge>
                      </div>
                    </div>
                    {r.descricao && <p className="text-sm">{r.descricao}</p>}
                    {r.valor != null && <p className="text-sm font-medium">{formatCurrency(r.valor)}</p>}

                    {/* Photos */}
                    <div>
                      <p className="text-sm font-medium mb-2">Fotos Comprovatorias</p>
                      <div className="flex flex-wrap gap-2">
                        {r.fotos?.map((url, i) => (
                          <div key={i} className="relative group">
                            <img src={url} alt={`Foto ${i + 1}`} className="h-20 w-20 rounded-lg object-cover border" />
                            <button onClick={() => removePhoto(r.id, i)} className="absolute -top-1 -right-1 rounded-full bg-destructive p-0.5 text-destructive-foreground opacity-0 group-hover:opacity-100 transition-opacity">
                              <Trash2 className="h-3 w-3" />
                            </button>
                          </div>
                        ))}
                        <label className="flex h-20 w-20 cursor-pointer items-center justify-center rounded-lg border border-dashed hover:bg-accent">
                          <Camera className="h-5 w-5 text-muted-foreground" />
                          <input type="file" accept="image/*" className="hidden" disabled={uploadingPhoto === r.id} onChange={(e) => { const f = e.target.files?.[0]; if (f) uploadPhoto(r.id, f); }} />
                        </label>
                      </div>
                    </div>

                    {r.createdAt?.toDate && <p className="text-xs text-muted-foreground">Criado: {formatDate(r.createdAt.toDate())}</p>}
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

import { useState, useEffect } from 'react';
import { useSearchParams } from 'react-router-dom';
import api from '@/lib/api';
import { formatDate } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { FileText, Upload, Trash2, Download } from 'lucide-react';
import { ref, uploadBytes, getDownloadURL } from 'firebase/storage';
import { storage } from '@/lib/firebase';

interface Document {
  id: string; nome: string; categoria: string; storagePath?: string;
  downloadURL?: string; projectId: string; createdAt: any;
}

export function DocumentsPage() {
  const [searchParams] = useSearchParams();
  const projectId = searchParams.get('projectId');
  const [documents, setDocuments] = useState<Document[]>([]);
  const [loading, setLoading] = useState(true);
  const [uploading, setUploading] = useState(false);
  const [showUpload, setShowUpload] = useState(false);
  const [uploadName, setUploadName] = useState('');
  const [uploadCategory, setUploadCategory] = useState('PROJETO');
  const [uploadFile, setUploadFile] = useState<File | null>(null);
  const [uploadProgress, setUploadProgress] = useState(0);

  useEffect(() => { if (projectId) loadDocuments(); }, [projectId]);

  const loadDocuments = async () => {
    setLoading(true);
    try {
      const { data } = await api.get(`/projects/${projectId}/documents`);
      setDocuments(data);
    } catch { toast({ title: 'Erro ao carregar documentos', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  const handleUpload = async () => {
    if (!uploadFile || !projectId) return;
    setUploading(true);
    setUploadProgress(0);
    try {
      const path = `projects/${projectId}/documents/${Date.now()}_${uploadFile.name}`;
      const storageRef = ref(storage, path);
      await uploadBytes(storageRef, uploadFile);
      const downloadURL = await getDownloadURL(storageRef);

      await api.post(`/projects/${projectId}/documents`, {
        nome: uploadName || uploadFile.name,
        categoria: uploadCategory,
        storagePath: path,
        downloadURL,
        projectId,
      });

      setShowUpload(false);
      setUploadName('');
      setUploadFile(null);
      setUploadProgress(100);
      loadDocuments();
      toast({ title: 'Documento enviado com sucesso' });
    } catch {
      toast({ title: 'Erro ao enviar documento', variant: 'destructive' });
    } finally {
      setUploading(false);
    }
  };

  const handleDelete = async (doc: Document) => {
    if (!confirm(`Excluir ${doc.nome}?`)) return;
    try {
      await api.delete(`/projects/${projectId}/documents/${doc.id}`);
      loadDocuments();
      toast({ title: 'Documento excluido' });
    } catch { toast({ title: 'Erro ao excluir', variant: 'destructive' }); }
  };

  const getCategoryBadge = (cat: string) => {
    const colors: Record<string, 'info' | 'success' | 'secondary'> = {
      JURIDICO: 'info', PROJETO: 'success', RELATORIO: 'secondary',
    };
    return <Badge variant={colors[cat] || 'secondary'}>{cat}</Badge>;
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Documentos</h2>
          <p className="text-muted-foreground">{projectId ? 'Documentos do projeto' : 'Selecione um projeto para ver os documentos'}</p>
        </div>
        {projectId && (
          <Button onClick={() => setShowUpload(!showUpload)} disabled={uploading}>
            <Upload className="mr-2 h-4 w-4" /> {showUpload ? 'Fechar' : 'Upload'}
          </Button>
        )}
      </div>

      {showUpload && projectId && (
        <Card>
          <CardHeader><CardTitle className="text-sm">Upload de Documento</CardTitle></CardHeader>
          <CardContent className="space-y-3">
            <div className="grid grid-cols-2 gap-3">
              <div className="space-y-1">
                <Label>Nome do documento</Label>
                <Input value={uploadName} onChange={(e) => setUploadName(e.target.value)} placeholder="Deixe em branco para usar o nome do arquivo" />
              </div>
              <div className="space-y-1">
                <Label>Categoria</Label>
                <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={uploadCategory} onChange={(e) => setUploadCategory(e.target.value)}>
                  <option value="PROJETO">Projeto</option>
                  <option value="JURIDICO">Juridico</option>
                  <option value="RELATORIO">Relatorio</option>
                </select>
              </div>
            </div>
            <div className="space-y-1">
              <Label>Arquivo</Label>
              <Input type="file" onChange={(e) => setUploadFile(e.target.files?.[0] || null)} />
            </div>
            {uploadProgress > 0 && uploadProgress < 100 && (
              <div className="h-2 rounded-full bg-muted"><div className="h-full rounded-full bg-primary transition-all" style={{ width: `${uploadProgress}%` }} /></div>
            )}
            <Button onClick={handleUpload} disabled={!uploadFile || uploading}>
              {uploading ? 'Enviando...' : 'Enviar'}
            </Button>
          </CardContent>
        </Card>
      )}

      {!projectId ? (
        <Card><CardContent className="py-12 text-center text-muted-foreground">Selecione um projeto para visualizar os documentos</CardContent></Card>
      ) : (
        <Card>
          <CardHeader><CardTitle className="text-lg">Todos os Documentos ({documents.length})</CardTitle></CardHeader>
          <CardContent>
            {loading ? <p className="text-muted-foreground">Carregando...</p> : documents.length === 0 ? (
              <p className="text-muted-foreground">Nenhum documento encontrado</p>
            ) : (
              <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                {documents.map((doc) => (
                  <div key={doc.id} className="rounded-lg border p-4 space-y-3">
                    <div className="flex items-start justify-between">
                      <div className="flex items-center gap-2">
                        <FileText className="h-5 w-5 text-primary shrink-0" />
                        <p className="font-medium text-sm truncate">{doc.nome}</p>
                      </div>
                      {getCategoryBadge(doc.categoria)}
                    </div>
                    <p className="text-xs text-muted-foreground">{doc.createdAt?.toDate ? formatDate(doc.createdAt.toDate()) : ''}</p>
                    <div className="flex gap-2">
                      {doc.downloadURL && (
                        <a href={doc.downloadURL} target="_blank" rel="noreferrer">
                          <Button size="sm" variant="outline"><Download className="h-3 w-3 mr-1" /> Download</Button>
                        </a>
                      )}
                      <Button size="sm" variant="ghost" className="text-red-600" onClick={() => handleDelete(doc)}>
                        <Trash2 className="h-3 w-3" />
                      </Button>
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

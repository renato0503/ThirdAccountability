import { useState, useEffect } from 'react';
import api from '@/lib/api';
import { formatCurrency } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { toast } from '@/hooks/use-toast';
import { Send, Loader2, Check, X, Plus, FileText } from 'lucide-react';

export function ChatIaPage() {
  const [texto, setTexto] = useState('');
  const [processing, setProcessing] = useState(false);
  const [itens, setItens] = useState<any[]>([]);
  const [institutions, setInstitutions] = useState<any[]>([]);
  const [selectedInst, setSelectedInst] = useState('');
  const [selectedProj, setSelectedProj] = useState('');
  const [showManualForm, setShowManualForm] = useState<string | null>(null);
  const [manualForm, setManualForm] = useState({ cnpj: '', razaoSocial: '', descricao: '', valor: '', observacoes: '' });

  useEffect(() => {
    api.get('/institutions?limit=100').then(({ data }) => setInstitutions(data.institutions)).catch(() => {});
  }, []);

  const processar = async () => {
    if (!texto.trim() || !selectedInst) { toast({ title: 'Preencha o texto e selecione uma instituicao', variant: 'destructive' }); return; }
    setProcessing(true);
    try {
      const { data } = await api.post('/chat-ia/processar', {
        texto,
        institutionId: selectedInst,
        projectId: selectedProj || undefined,
      });
      if (data.error) { toast({ title: data.error, variant: 'destructive' }); return; }
      setItens(data.itens || []);
      toast({ title: `${data.total} itens processados` });
    } catch { toast({ title: 'Erro ao processar', variant: 'destructive' }); }
    finally { setProcessing(false); }
  };

  const toggleSelect = async (pesquisaId: string, resultadoId: string, selected: boolean) => {
    try {
      await api.patch(`/price-research/${pesquisaId}/results/${resultadoId}/select`, { selected });
      setItens((prev) => prev.map((item) => {
        if (item.id !== pesquisaId) return item;
        return {
          ...item,
          results: (item.results || []).map((r: any) =>
            r.id === resultadoId ? { ...r, selected } : r
          ),
        };
      }));
    } catch { toast({ title: 'Erro ao selecionar', variant: 'destructive' }); }
  };

  const addManual = async (pesquisaId: string) => {
    if (!manualForm.cnpj || !manualForm.valor) { toast({ title: 'Preencha CNPJ e valor', variant: 'destructive' }); return; }
    try {
      await api.post('/chat-ia/orcamento-manual', {
        pesquisaId,
        cnpjFornecedor: manualForm.cnpj,
        razaoSocial: manualForm.razaoSocial,
        itemDescricao: manualForm.descricao,
        unitPrice: Number(manualForm.valor),
        observacoes: manualForm.observacoes,
      });
      const { data } = await api.get(`/chat-ia/status/${pesquisaId}`);
      setItens((prev) => prev.map((i) => i.id === pesquisaId ? data : i));
      setShowManualForm(null);
      setManualForm({ cnpj: '', razaoSocial: '', descricao: '', valor: '', observacoes: '' });
      toast({ title: 'Orcamento manual adicionado' });
    } catch { toast({ title: 'Erro ao adicionar', variant: 'destructive' }); }
  };

  const finalizar = async (pesquisaId: string) => {
    const item = itens.find((i) => i.id === pesquisaId);
    if (!item) return;
    const selectedResults = (item.results || []).filter((r: any) => r.selected);
    if (!selectedResults.length && !item.results?.some((r: any) => r.source === 'MANUAL')) {
      toast({ title: 'Selecione ao menos uma cotacao', variant: 'destructive' }); return;
    }

    const type = prompt('Tipo de referencia (MENOR, MAIOR, MEDIA, MEDIANA, MANUAL, ITEM):') || 'MEDIA';
    const justification = prompt('Justificativa:') || '';

    if (!justification) { toast({ title: 'Justificativa obrigatoria', variant: 'destructive' }); return; }

    try {
      await api.post(`/price-research/${pesquisaId}/set-reference`, { type, justification });
      const { data } = await api.get(`/chat-ia/status/${pesquisaId}`);
      setItens((prev) => prev.map((i) => i.id === pesquisaId ? data : i));
      toast({ title: 'Pesquisa finalizada' });
    } catch { toast({ title: 'Erro ao finalizar', variant: 'destructive' }); }
  };

  return (
    <div className="mx-auto max-w-5xl space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight">Chat IA (Cotacao)</h2>
        <p className="text-muted-foreground">Descreva os itens em linguagem natural e o sistema busca precos automaticamente</p>
      </div>

      <Card>
        <CardHeader><CardTitle className="text-lg">Entrada</CardTitle></CardHeader>
        <CardContent className="space-y-3">
          <div className="grid grid-cols-2 gap-3">
            <div className="space-y-1">
              <Label>Instituicao</Label>
              <select className="flex h-9 w-full rounded-md border bg-transparent px-3 py-1 text-sm" value={selectedInst} onChange={(e) => setSelectedInst(e.target.value)}>
                <option value="">Selecione...</option>
                {institutions.map((i: any) => <option key={i.id} value={i.id}>{i.razaoSocial}</option>)}
              </select>
            </div>
            <div className="space-y-1">
              <Label>Projeto (opcional)</Label>
              <Input value={selectedProj} onChange={(e) => setSelectedProj(e.target.value)} placeholder="ID do projeto" />
            </div>
          </div>
          <div className="space-y-1">
            <Label>Descreva os itens para cotacao</Label>
            <textarea
              className="flex h-24 w-full rounded-md border bg-transparent px-3 py-2 text-sm"
              placeholder='Ex: Cota 5 cotações de cada item: Bola MAX 200, Bola de Vôlei Penalty VP500, Rede de Vôlei 4 faixas, Coletes salva-vidas'
              value={texto}
              onChange={(e) => setTexto(e.target.value)}
            />
          </div>
          <Button onClick={processar} disabled={processing || !texto.trim()}>
            {processing ? <Loader2 className="mr-2 h-4 w-4 animate-spin" /> : <Send className="mr-2 h-4 w-4" />}
            {processing ? 'Processando...' : 'Processar com IA'}
          </Button>
        </CardContent>
      </Card>

      {itens.length > 0 && (
        <div className="space-y-4">
          <h3 className="text-xl font-semibold">Resultados ({itens.length} itens)</h3>
          {itens.map((item: any) => (
            <Card key={item.id}>
              <CardHeader className="pb-2">
                <div className="flex items-center justify-between">
                  <CardTitle className="text-base">{item.searchTerm}</CardTitle>
                  <Badge className={item.status === 'FINALIZADA' ? 'bg-green-500' : 'bg-blue-500'}>
                    {item.status === 'FINALIZADA' ? 'Finalizada' : 'Com Resultados'}
                  </Badge>
                </div>
                {item.quantity > 0 && <p className="text-sm text-muted-foreground">Quantidade: {item.quantity} {item.unit}</p>}
                {item.averagePrice > 0 && (
                  <div className="flex gap-4 text-sm">
                    <span>Min: {formatCurrency(item.minPrice)}</span>
                    <span>Max: {formatCurrency(item.maxPrice)}</span>
                    <span>Media: {formatCurrency(item.averagePrice)}</span>
                    <span>Mediana: {formatCurrency(item.medianPrice)}</span>
                  </div>
                )}
                {item.selectedReferencePrice > 0 && (
                  <p className="text-sm font-bold text-green-600">
                    Preco Referencia ({item.referenceType}): {formatCurrency(item.selectedReferencePrice)}
                  </p>
                )}
              </CardHeader>
              <CardContent className="space-y-3">
                {(item.results || []).map((r: any) => (
                  <div key={r.id} className={`flex items-center justify-between rounded-lg border p-3 ${r.selected ? 'border-green-500 bg-green-50 dark:bg-green-950' : ''}`}>
                    <div className="flex-1">
                      <div className="flex items-center gap-2">
                        <Badge variant={r.source === 'PNCP' ? 'info' : r.source === 'MANUAL' ? 'secondary' : 'default'} className="text-xs">
                          {r.source}
                        </Badge>
                        <p className="font-medium text-sm">{r.originalDescription?.slice(0, 80)}</p>
                      </div>
                      <p className="text-sm text-muted-foreground">
                        {r.buyerName && <>{r.buyerName} &middot; </>}
                        {r.city && r.state && <>{r.city}/{r.state} &middot; </>}
                        {r.contractNumber && <>Contrato: {r.contractNumber} &middot; </>}
                      </p>
                    </div>
                    <div className="flex items-center gap-3">
                      <span className="text-lg font-bold">{formatCurrency(r.unitPrice)}</span>
                      {item.status !== 'FINALIZADA' && (
                        <Button
                          size="sm"
                          variant={r.selected ? 'default' : 'outline'}
                          onClick={() => toggleSelect(item.id, r.id, !r.selected)}
                        >
                          {r.selected ? <Check className="h-3 w-3" /> : <X className="h-3 w-3" />}
                        </Button>
                      )}
                    </div>
                  </div>
                ))}

                <div className="flex gap-2">
                  {item.status !== 'FINALIZADA' && (
                    <>
                      <Button size="sm" variant="outline" onClick={() => setShowManualForm(showManualForm === item.id ? null : item.id)}>
                        <Plus className="h-3 w-3 mr-1" /> Orcamento Manual
                      </Button>
                      <Button size="sm" onClick={() => finalizar(item.id)}>
                        <FileText className="h-3 w-3 mr-1" /> Finalizar
                      </Button>
                    </>
                  )}
                </div>

                {showManualForm === item.id && (
                  <div className="space-y-3 rounded-lg border p-4">
                    <p className="font-medium">Adicionar Orcamento Manual</p>
                    <div className="grid grid-cols-2 gap-3">
                      <div className="space-y-1">
                        <Label>CNPJ</Label>
                        <Input value={manualForm.cnpj} onChange={(e) => setManualForm((p) => ({ ...p, cnpj: e.target.value }))} />
                      </div>
                      <div className="space-y-1">
                        <Label>Razao Social</Label>
                        <Input value={manualForm.razaoSocial} onChange={(e) => setManualForm((p) => ({ ...p, razaoSocial: e.target.value }))} />
                      </div>
                      <div className="space-y-1">
                        <Label>Item</Label>
                        <Input value={manualForm.descricao} onChange={(e) => setManualForm((p) => ({ ...p, descricao: e.target.value }))} />
                      </div>
                      <div className="space-y-1">
                        <Label>Valor Unitario</Label>
                        <Input type="number" step="0.01" value={manualForm.valor} onChange={(e) => setManualForm((p) => ({ ...p, valor: e.target.value }))} />
                      </div>
                    </div>
                    <Input placeholder="Observacoes" value={manualForm.observacoes} onChange={(e) => setManualForm((p) => ({ ...p, observacoes: e.target.value }))} />
                    <div className="flex gap-2">
                      <Button size="sm" onClick={() => addManual(item.id)}>Adicionar</Button>
                      <Button size="sm" variant="ghost" onClick={() => setShowManualForm(null)}>Cancelar</Button>
                    </div>
                  </div>
                )}
              </CardContent>
            </Card>
          ))}
        </div>
      )}
    </div>
  );
}

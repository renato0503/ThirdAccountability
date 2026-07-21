import { useState } from 'react';
import api from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Activity, CheckCircle, XCircle } from 'lucide-react';

export function DiagnosticsPage() {
  const [result, setResult] = useState<any>(null);
  const [loading, setLoading] = useState(false);

  const run = async () => {
    setLoading(true);
    try {
      const { data } = await api.get('/diagnostics');
      setResult(data);
    } catch {
      setResult({ status: 'error', checks: { api: { status: 'error', message: 'API nao disponivel' } } });
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-3xl font-bold tracking-tight">Diagnostico do Sistema</h2>
          <p className="text-muted-foreground">Verifique a saude dos servicos (admin apenas)</p>
        </div>
        <Button onClick={run} disabled={loading}>
          <Activity className="mr-2 h-4 w-4" /> {loading ? 'Verificando...' : 'Executar Diagnostico'}
        </Button>
      </div>

      {result && (
        <Card>
          <CardHeader>
            <div className="flex items-center gap-3">
              <CardTitle className="text-lg">Resultado</CardTitle>
              <Badge variant={result.status === 'healthy' ? 'success' : 'destructive'}>
                {result.status === 'healthy' ? 'Saudavel' : 'Degradado'}
              </Badge>
            </div>
          </CardHeader>
          <CardContent className="space-y-3">
            {Object.entries(result.checks || {}).map(([key, check]: [string, any]) => (
              <div key={key} className="flex items-center justify-between rounded-lg border p-3">
                <div className="flex items-center gap-3">
                  {check.status === 'ok' ? (
                    <CheckCircle className="h-5 w-5 text-green-500" />
                  ) : (
                    <XCircle className="h-5 w-5 text-red-500" />
                  )}
                  <div>
                    <p className="font-medium capitalize">{key.replace(/([A-Z])/g, ' $1').trim()}</p>
                    <p className="text-sm text-muted-foreground">{check.message}</p>
                  </div>
                </div>
                {check.latency != null && (
                  <span className="text-sm text-muted-foreground">{check.latency}ms</span>
                )}
              </div>
            ))}
          </CardContent>
        </Card>
      )}
    </div>
  );
}

import { useState, useEffect } from 'react';
import api from '@/lib/api';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/hooks/use-toast';
import { Settings, Save } from 'lucide-react';

export function SettingsPage() {
  const [settings, setSettings] = useState<any>({});
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);

  useEffect(() => { load(); }, []);

  const load = async () => {
    try {
      const { data } = await api.get('/settings');
      setSettings(data);
    } catch { toast({ title: 'Erro ao carregar configuracoes', variant: 'destructive' }); }
    finally { setLoading(false); }
  };

  const update = (field: string, value: string) => setSettings((p: any) => ({ ...p, [field]: value }));

  const save = async () => {
    setSaving(true);
    try {
      await api.put('/settings', settings);
      toast({ title: 'Configuracoes salvas' });
    } catch { toast({ title: 'Erro ao salvar', variant: 'destructive' }); }
    finally { setSaving(false); }
  };

  if (loading) return <div className="text-muted-foreground">Carregando...</div>;

  return (
    <div className="mx-auto max-w-2xl space-y-6">
      <div>
        <h2 className="text-3xl font-bold tracking-tight">Configuracoes do Sistema</h2>
        <p className="text-muted-foreground">Gerenciar configuracoes globais (apenas ADMIN_GERAL)</p>
      </div>

      <Card>
        <CardHeader><CardTitle className="flex items-center gap-2"><Settings className="h-5 w-5" /> SMTP / Email</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label>Host SMTP</Label>
              <Input value={settings.smtpHost || ''} onChange={(e) => update('smtpHost', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Porta SMTP</Label>
              <Input value={settings.smtpPort || ''} onChange={(e) => update('smtpPort', e.target.value)} />
            </div>
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label>Usuario SMTP</Label>
              <Input value={settings.smtpUser || ''} onChange={(e) => update('smtpUser', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Senha SMTP</Label>
              <Input type="password" value={settings.smtpPass || ''} onChange={(e) => update('smtpPass', e.target.value)} />
            </div>
          </div>
          <div className="space-y-2">
            <Label>Email de origem</Label>
            <Input value={settings.smtpFrom || ''} onChange={(e) => update('smtpFrom', e.target.value)} />
          </div>
        </CardContent>
      </Card>

      <Card>
        <CardHeader><CardTitle>API Keys</CardTitle></CardHeader>
        <CardContent className="space-y-4">
          <div className="space-y-2">
            <Label>Groq IA API Key</Label>
            <Input type="password" value={settings.groqApiKey || ''} onChange={(e) => update('groqApiKey', e.target.value)} placeholder="gsk_..." />
          </div>
          <div className="grid grid-cols-2 gap-4">
            <div className="space-y-2">
              <Label>Asaas API Key</Label>
              <Input type="password" value={settings.asaasApiKey || ''} onChange={(e) => update('asaasApiKey', e.target.value)} />
            </div>
            <div className="space-y-2">
              <Label>Z-API Token</Label>
              <Input type="password" value={settings.zapiToken || ''} onChange={(e) => update('zapiToken', e.target.value)} />
            </div>
          </div>
        </CardContent>
      </Card>

      <Button onClick={save} disabled={saving}>
        <Save className="mr-2 h-4 w-4" /> {saving ? 'Salvando...' : 'Salvar Configuracoes'}
      </Button>
    </div>
  );
}

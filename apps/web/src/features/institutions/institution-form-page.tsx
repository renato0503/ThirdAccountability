import { useState, useEffect } from 'react';
import { useNavigate, useParams } from 'react-router-dom';
import api from '@/lib/api';
import { maskCnpj, maskCep, maskPhone, validateCnpj } from '@/lib/utils';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { toast } from '@/hooks/use-toast';
import { ChevronLeft, ChevronRight, Save, ArrowLeft } from 'lucide-react';

const STEPS = [
  { id: 1, label: 'Dados Cadastrais' },
  { id: 2, label: 'Endereco' },
  { id: 3, label: 'Dados Bancarios' },
  { id: 4, label: 'Presidente' },
];

export function InstitutionFormPage() {
  const navigate = useNavigate();
  const { id } = useParams();
  const isEditing = !!id;
  const [step, setStep] = useState(1);
  const [saving, setSaving] = useState(false);
  const [loadingCnpj, setLoadingCnpj] = useState(false);
  const [loadingCep, setLoadingCep] = useState(false);

  const [form, setForm] = useState({
    razaoSocial: '',
    nomeFantasia: '',
    cnpj: '',
    email: '',
    telefone: '',
    site: '',
    instagram: '',
    cep: '',
    endereco: '',
    numero: '',
    complemento: '',
    bairro: '',
    municipio: '',
    estado: '',
    banco: '',
    agencia: '',
    contaCorrente: '',
    tipoConta: '',
    chavePix: '',
    representanteLegal: '',
    presidenteCpf: '',
    presidenteRg: '',
    presidenteTelefone: '',
    presidenteEmail: '',
  });

  useEffect(() => {
    if (isEditing) {
      loadInstitution();
    }
  }, [id]);

  const loadInstitution = async () => {
    try {
      const { data } = await api.get(`/institutions/${id}`);
      setForm({
        razaoSocial: data.razaoSocial || '',
        nomeFantasia: data.nomeFantasia || '',
        cnpj: maskCnpj(data.cnpj || ''),
        email: data.email || '',
        telefone: data.telefone || '',
        site: data.site || '',
        instagram: data.instagram || '',
        cep: data.cep || '',
        endereco: data.endereco || '',
        numero: data.numero || '',
        complemento: data.complemento || '',
        bairro: data.bairro || '',
        municipio: data.municipio || '',
        estado: data.estado || '',
        banco: data.banco || '',
        agencia: data.agencia || '',
        contaCorrente: data.contaCorrente || '',
        tipoConta: data.tipoConta || '',
        chavePix: data.chavePix || '',
        representanteLegal: data.representanteLegal || '',
        presidenteCpf: data.presidenteCpf || '',
        presidenteRg: data.presidenteRg || '',
        presidenteTelefone: data.presidenteTelefone || '',
        presidenteEmail: data.presidenteEmail || '',
      });
    } catch {
      toast({ title: 'Erro ao carregar instituicao', variant: 'destructive' });
      navigate('/instituicoes');
    }
  };

  const handleCnpjBlur = async () => {
    const digits = form.cnpj.replace(/\D/g, '');
    if (digits.length !== 14) return;

    if (!validateCnpj(digits)) {
      toast({ title: 'CNPJ invalido', variant: 'destructive' });
      return;
    }

    setLoadingCnpj(true);
    try {
      const { data } = await api.get(`/integrations/cnpj/${digits}`);
      if (!data.error) {
        setForm((prev) => ({
          ...prev,
          razaoSocial: data.razao_social || prev.razaoSocial,
          nomeFantasia: data.nome_fantasia || prev.nomeFantasia,
          endereco: data.logradouro || prev.endereco,
          bairro: data.bairro || prev.bairro,
          municipio: data.municipio || prev.municipio,
          estado: data.uf || prev.estado,
          cep: data.cep || prev.cep,
          telefone: data.ddd_telefone_1 ? `(${data.ddd_telefone_1.slice(0, 2)}) ${data.ddd_telefone_1.slice(2)}` : prev.telefone,
          email: data.email || prev.email,
        }));
      }
    } catch {
      // Silently fail - CNPJ autocomplete is optional
    } finally {
      setLoadingCnpj(false);
    }
  };

  const handleCepBlur = async () => {
    const digits = form.cep.replace(/\D/g, '');
    if (digits.length !== 8) return;

    setLoadingCep(true);
    try {
      const { data } = await api.get(`/integrations/cep/${digits}`);
      if (!data.erro) {
        setForm((prev) => ({
          ...prev,
          endereco: data.logradouro || prev.endereco,
          bairro: data.bairro || prev.bairro,
          municipio: data.localidade || prev.municipio,
          estado: data.uf || prev.estado,
        }));
      }
    } catch {
      // Silently fail
    } finally {
      setLoadingCep(false);
    }
  };

  const updateField = (field: string, value: string) => {
    setForm((prev) => ({ ...prev, [field]: value }));
  };

  const handleSubmit = async () => {
    if (!form.razaoSocial) {
      toast({ title: 'Razao Social e obrigatoria', variant: 'destructive' });
      return;
    }

    setSaving(true);
    try {
      const payload = {
        ...form,
        cnpj: form.cnpj.replace(/\D/g, ''),
        cep: form.cep.replace(/\D/g, ''),
        telefone: form.telefone.replace(/\D/g, ''),
        presidenteTelefone: form.presidenteTelefone.replace(/\D/g, ''),
      };

      if (isEditing) {
        await api.put(`/institutions/${id}`, payload);
        toast({ title: 'Instituicao atualizada com sucesso' });
        navigate(`/instituicoes/${id}`);
      } else {
        const { data } = await api.post('/institutions', payload);
        toast({ title: 'Instituicao criada com sucesso' });
        navigate(`/instituicoes/${data.id}`);
      }
    } catch {
      toast({ title: 'Erro ao salvar instituicao', variant: 'destructive' });
    } finally {
      setSaving(false);
    }
  };

  const nextStep = () => setStep((s) => Math.min(s + 1, 4));
  const prevStep = () => setStep((s) => Math.max(s - 1, 1));

  return (
    <div className="mx-auto max-w-3xl space-y-6">
      <div className="flex items-center gap-4">
        <Button variant="ghost" size="icon" onClick={() => navigate('/instituicoes')}>
          <ArrowLeft className="h-4 w-4" />
        </Button>
        <div>
          <h2 className="text-3xl font-bold tracking-tight">
            {isEditing ? 'Editar Instituicao' : 'Nova Instituicao'}
          </h2>
          <p className="text-muted-foreground">
            {isEditing ? 'Altere os dados da instituicao' : 'Preencha os dados para cadastrar'}
          </p>
        </div>
      </div>

      {/* Steps indicator */}
      <div className="flex gap-2">
        {STEPS.map((s) => (
          <button
            key={s.id}
            onClick={() => setStep(s.id)}
            className={`flex-1 rounded-lg px-4 py-2 text-sm font-medium transition-colors ${
              step === s.id
                ? 'bg-primary text-primary-foreground'
                : step > s.id
                  ? 'bg-primary/20 text-primary'
                  : 'bg-muted text-muted-foreground'
            }`}
          >
            {s.label}
          </button>
        ))}
      </div>

      <Card>
        <CardHeader>
          <CardTitle>{STEPS[step - 1]?.label}</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          {step === 1 && (
            <>
              <div className="space-y-2">
                <Label>CNPJ *</Label>
                <div className="flex gap-2">
                  <Input
                    value={form.cnpj}
                    onChange={(e) => updateField('cnpj', maskCnpj(e.target.value))}
                    onBlur={handleCnpjBlur}
                    placeholder="00.000.000/0000-00"
                    maxLength={18}
                  />
                  {loadingCnpj && <span className="text-sm text-muted-foreground self-center">Buscando...</span>}
                </div>
              </div>
              <div className="space-y-2">
                <Label>Razao Social *</Label>
                <Input value={form.razaoSocial} onChange={(e) => updateField('razaoSocial', e.target.value)} />
              </div>
              <div className="space-y-2">
                <Label>Nome Fantasia</Label>
                <Input value={form.nomeFantasia} onChange={(e) => updateField('nomeFantasia', e.target.value)} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Email</Label>
                  <Input type="email" value={form.email} onChange={(e) => updateField('email', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Telefone</Label>
                  <Input value={form.telefone} onChange={(e) => updateField('telefone', maskPhone(e.target.value))} />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Site</Label>
                  <Input value={form.site} onChange={(e) => updateField('site', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Instagram</Label>
                  <Input value={form.instagram} onChange={(e) => updateField('instagram', e.target.value)} />
                </div>
              </div>
            </>
          )}

          {step === 2 && (
            <>
              <div className="space-y-2">
                <Label>CEP</Label>
                <div className="flex gap-2">
                  <Input
                    value={form.cep}
                    onChange={(e) => updateField('cep', maskCep(e.target.value))}
                    onBlur={handleCepBlur}
                    placeholder="00000-000"
                    maxLength={9}
                  />
                  {loadingCep && <span className="text-sm text-muted-foreground self-center">Buscando...</span>}
                </div>
              </div>
              <div className="space-y-2">
                <Label>Logradouro</Label>
                <Input value={form.endereco} onChange={(e) => updateField('endereco', e.target.value)} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Numero</Label>
                  <Input value={form.numero} onChange={(e) => updateField('numero', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Complemento</Label>
                  <Input value={form.complemento} onChange={(e) => updateField('complemento', e.target.value)} />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Bairro</Label>
                  <Input value={form.bairro} onChange={(e) => updateField('bairro', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Municipio</Label>
                  <Input value={form.municipio} onChange={(e) => updateField('municipio', e.target.value)} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Estado</Label>
                <Input value={form.estado} onChange={(e) => updateField('estado', e.target.value.toUpperCase().slice(0, 2))} maxLength={2} placeholder="MT" />
              </div>
            </>
          )}

          {step === 3 && (
            <>
              <div className="space-y-2">
                <Label>Banco</Label>
                <Input value={form.banco} onChange={(e) => updateField('banco', e.target.value)} placeholder="Nome do banco" />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Agencia</Label>
                  <Input value={form.agencia} onChange={(e) => updateField('agencia', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>Conta Corrente</Label>
                  <Input value={form.contaCorrente} onChange={(e) => updateField('contaCorrente', e.target.value)} />
                </div>
              </div>
              <div className="space-y-2">
                <Label>Tipo de Conta</Label>
                <Input value={form.tipoConta} onChange={(e) => updateField('tipoConta', e.target.value)} placeholder="Corrente, Poupanca, etc." />
              </div>
              <div className="space-y-2">
                <Label>Chave PIX</Label>
                <Input value={form.chavePix} onChange={(e) => updateField('chavePix', e.target.value)} />
              </div>
            </>
          )}

          {step === 4 && (
            <>
              <div className="space-y-2">
                <Label>Representante Legal</Label>
                <Input value={form.representanteLegal} onChange={(e) => updateField('representanteLegal', e.target.value)} />
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>CPF do Presidente</Label>
                  <Input value={form.presidenteCpf} onChange={(e) => updateField('presidenteCpf', e.target.value)} />
                </div>
                <div className="space-y-2">
                  <Label>RG do Presidente</Label>
                  <Input value={form.presidenteRg} onChange={(e) => updateField('presidenteRg', e.target.value)} />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div className="space-y-2">
                  <Label>Telefone do Presidente</Label>
                  <Input value={form.presidenteTelefone} onChange={(e) => updateField('presidenteTelefone', maskPhone(e.target.value))} />
                </div>
                <div className="space-y-2">
                  <Label>Email do Presidente</Label>
                  <Input type="email" value={form.presidenteEmail} onChange={(e) => updateField('presidenteEmail', e.target.value)} />
                </div>
              </div>
            </>
          )}

          {/* Navigation buttons */}
          <div className="flex justify-between pt-4">
            <Button variant="outline" onClick={step === 1 ? () => navigate('/instituicoes') : prevStep}>
              <ChevronLeft className="mr-2 h-4 w-4" />
              {step === 1 ? 'Cancelar' : 'Anterior'}
            </Button>

            {step < 4 ? (
              <Button onClick={nextStep}>
                Proximo
                <ChevronRight className="ml-2 h-4 w-4" />
              </Button>
            ) : (
              <Button onClick={handleSubmit} disabled={saving}>
                <Save className="mr-2 h-4 w-4" />
                {saving ? 'Salvando...' : isEditing ? 'Atualizar' : 'Criar Instituicao'}
              </Button>
            )}
          </div>
        </CardContent>
      </Card>
    </div>
  );
}

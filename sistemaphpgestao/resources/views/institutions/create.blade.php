@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h4 class="fw-bold mb-1">Nova Instituição</h4>
        <p class="text-muted mb-0" style="font-size:13px">Cadastre uma nova organização parceira</p>
    </div>
    <a href="{{ route('instituicoes.index') }}" class="btn btn-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('instituicoes.store') }}" enctype="multipart/form-data">
    @csrf
    <div class="row g-3">
        <div class="col-md-8">

            {{-- Dados Principais da Entidade --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">Dados Principais da Entidade</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">CNPJ *</label>
                            <div class="input-group">
                                <input type="text" id="cnpj" name="cnpj" value="{{ old('cnpj') }}"
                                    class="form-control" placeholder="00.000.000/0000-00" required
                                    maxlength="18">
                                <button type="button" class="btn btn-secondary" id="btn-consultar-cnpj"
                                    title="Consultar CNPJ na Receita Federal">
                                    <i class="bi bi-search" id="cnpj-icon"></i>
                                </button>
                            </div>
                            <div id="cnpj-status" class="form-text"></div>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Razão Social *</label>
                            <input type="text" id="razao_social" name="razao_social" value="{{ old('razao_social') }}"
                                class="form-control" required placeholder="Nome jurídico da instituição">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nome Fantasia</label>
                            <input type="text" id="nome_fantasia" name="nome_fantasia" value="{{ old('nome_fantasia') }}"
                                class="form-control" placeholder="Nome pelo qual é conhecida">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Área de Atuação</label>
                            <input type="text" name="area_atuacao" value="{{ old('area_atuacao') }}"
                                class="form-control" placeholder="ex: Assistência Social, Educação">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                class="form-control" placeholder="contato@instituicao.org.br">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" id="telefone" name="telefone" value="{{ old('telefone') }}"
                                class="form-control" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Site</label>
                            <input type="text" name="site" value="{{ old('site') }}"
                                class="form-control" placeholder="www.site.org.br">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Instagram</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" name="instagram" value="{{ old('instagram') }}"
                                    class="form-control" placeholder="perfil ou URL do Instagram">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Endereço da Sede --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">Endereço da Sede</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">CEP</label>
                            <div class="input-group">
                                <input type="text" id="cep" name="cep" value="{{ old('cep') }}"
                                    class="form-control" placeholder="00000-000" maxlength="9">
                                <button type="button" class="btn btn-secondary" id="btn-consultar-cep"
                                    title="Buscar endereço pelo CEP">
                                    <i class="bi bi-search" id="cep-icon"></i>
                                </button>
                            </div>
                            <div id="cep-status" class="form-text"></div>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Logradouro</label>
                            <input type="text" id="endereco" name="endereco" value="{{ old('endereco') }}"
                                class="form-control" placeholder="Rua, Avenida, Travessa...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" id="numero" name="numero" value="{{ old('numero') }}"
                                class="form-control" placeholder="Nº">
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Complemento</label>
                            <input type="text" id="complemento" name="complemento" value="{{ old('complemento') }}"
                                class="form-control" placeholder="Apto, Sala, Bloco...">
                        </div>
                        <div class="col-md-7">
                            <label class="form-label">Bairro</label>
                            <input type="text" id="bairro" name="bairro" value="{{ old('bairro') }}"
                                class="form-control" placeholder="Nome do bairro">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Município</label>
                            <input type="text" id="municipio" name="municipio" value="{{ old('municipio') }}"
                                class="form-control" placeholder="Nome da cidade">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Estado (UF)</label>
                            <select id="estado" name="estado" class="form-select">
                                <option value="">Selecione</option>
                                @foreach(['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'] as $uf)
                                <option value="{{ $uf }}" {{ old('estado') == $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Dados do Presidente --}}
            <div class="card mb-3">
                <div class="card-header fw-semibold">
                    <i class="bi bi-person-badge me-1"></i> Dados do Presidente
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Nome</label>
                            <input type="text" name="representante_legal" value="{{ old('representante_legal') }}"
                                class="form-control" placeholder="Nome completo do presidente">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="presidente_nascimento" value="{{ old('presidente_nascimento') }}"
                                class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">CPF</label>
                            <input type="text" id="presidente_cpf" name="presidente_cpf" value="{{ old('presidente_cpf') }}"
                                class="form-control" placeholder="000.000.000-00" maxlength="14">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">RG</label>
                            <input type="text" name="presidente_rg" value="{{ old('presidente_rg') }}"
                                class="form-control" placeholder="00.000.000-0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Data de Expedição do RG</label>
                            <input type="date" name="presidente_rg_expedicao" value="{{ old('presidente_rg_expedicao') }}"
                                class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="presidente_telefone" value="{{ old('presidente_telefone') }}"
                                class="form-control" placeholder="(00) 00000-0000">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="presidente_email" value="{{ old('presidente_email') }}"
                                class="form-control" placeholder="email do presidente">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="presidente_endereco" value="{{ old('presidente_endereco') }}"
                                class="form-control" placeholder="Rua, número, bairro, cidade — endereço residencial">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Foto <span class="text-muted fw-normal" style="font-size:12px">(opcional — JPG/PNG, máx. 2 MB)</span></label>
                            <input type="file" name="presidente_foto" class="form-control" accept="image/*">
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-md-4">
            <div class="card mb-3">
                <div class="card-header"><i class="bi bi-bank me-1"></i> Dados Bancários</div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Banco</label>
                            <input type="text" name="banco" value="{{ old('banco') }}"
                                class="form-control" placeholder="Nome do banco">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Agência</label>
                            <input type="text" name="agencia" value="{{ old('agencia') }}"
                                class="form-control" placeholder="0000-0">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Conta Corrente</label>
                            <input type="text" name="conta_corrente" value="{{ old('conta_corrente') }}"
                                class="form-control" placeholder="00000-0">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Tipo de Conta</label>
                            <select name="tipo_conta" class="form-select">
                                <option value="">Selecione...</option>
                                <option value="Conta Corrente" {{ old('tipo_conta') == 'Conta Corrente' ? 'selected' : '' }}>Conta Corrente</option>
                                <option value="Conta Poupança" {{ old('tipo_conta') == 'Conta Poupança' ? 'selected' : '' }}>Conta Poupança</option>
                                <option value="Conta Salário" {{ old('tipo_conta') == 'Conta Salário' ? 'selected' : '' }}>Conta Salário</option>
                                <option value="Conta de Pagamento" {{ old('tipo_conta') == 'Conta de Pagamento' ? 'selected' : '' }}>Conta de Pagamento</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Chave PIX</label>
                            <input type="text" name="chave_pix" value="{{ old('chave_pix') }}"
                                class="form-control" placeholder="CNPJ, CPF, e-mail ou telefone">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Salvar Instituição
                        </button>
                        <a href="{{ route('instituicoes.index') }}" class="btn btn-secondary">Cancelar</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

@push('scripts')
<script>
function mascaraCnpj(v) {
    v = v.replace(/\D/g,'').slice(0,14);
    v = v.replace(/^(\d{2})(\d)/,'$1.$2');
    v = v.replace(/^(\d{2})\.(\d{3})(\d)/,'$1.$2.$3');
    v = v.replace(/\.(\d{3})(\d)/,'.$1/$2');
    v = v.replace(/(\d{4})(\d)/,'$1-$2');
    return v;
}
function mascaraCep(v) {
    v = v.replace(/\D/g,'').slice(0,8);
    if (v.length > 5) v = v.slice(0,5) + '-' + v.slice(5);
    return v;
}
function mascaraCpf(v) {
    v = v.replace(/\D/g,'').slice(0,11);
    v = v.replace(/(\d{3})(\d)/,'$1.$2');
    v = v.replace(/(\d{3})\.(\d{3})(\d)/,'$1.$2.$3');
    v = v.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/,'$1.$2.$3-$4');
    return v;
}

document.getElementById('cnpj').addEventListener('input', function() {
    this.value = mascaraCnpj(this.value);
});
document.getElementById('cep').addEventListener('input', function() {
    this.value = mascaraCep(this.value);
    if (this.value.replace(/\D/g,'').length === 8) buscarCep();
});
document.getElementById('presidente_cpf').addEventListener('input', function() {
    this.value = mascaraCpf(this.value);
});

document.getElementById('btn-consultar-cnpj').addEventListener('click', async function() {
    const cnpj  = document.getElementById('cnpj').value.replace(/\D/g,'');
    const status = document.getElementById('cnpj-status');
    const icon   = document.getElementById('cnpj-icon');
    if (cnpj.length !== 14) { status.textContent = 'Digite um CNPJ válido (14 dígitos).'; status.style.color = 'var(--destructive)'; return; }

    icon.className = 'bi bi-hourglass-split';
    status.textContent = 'Consultando Receita Federal...';
    status.style.color = 'var(--text-muted)';

    try {
        const res  = await fetch(`/api/cnpj/${cnpj}`);
        const data = await res.json();

        if (data.erro) {
            status.textContent = data.erro;
            status.style.color = 'var(--destructive)';
        } else {
            if (data.razao_social) document.getElementById('razao_social').value = data.razao_social;
            if (data.nome_fantasia) document.getElementById('nome_fantasia').value = data.nome_fantasia;
            if (data.email) document.getElementById('email').value = data.email;
            if (data.telefone) document.getElementById('telefone').value = data.telefone;
            if (data.municipio) document.getElementById('municipio').value = data.municipio;
            if (data.estado) {
                const sel = document.getElementById('estado');
                for (let o of sel.options) { if (o.value === data.estado) { o.selected = true; break; } }
            }
            if (data.logradouro) document.getElementById('endereco').value = data.logradouro;
            if (data.numero)     document.getElementById('numero').value    = data.numero;
            if (data.bairro)     document.getElementById('bairro').value    = data.bairro;
            status.textContent = '✓ Dados preenchidos automaticamente.';
            status.style.color = '#16a34a';
        }
    } catch(e) {
        status.textContent = 'Erro ao consultar. Tente novamente.';
        status.style.color = 'var(--destructive)';
    } finally {
        icon.className = 'bi bi-search';
    }
});

async function buscarCep() {
    const cep    = document.getElementById('cep').value.replace(/\D/g,'');
    const status = document.getElementById('cep-status');
    const icon   = document.getElementById('cep-icon');
    if (cep.length !== 8) { status.textContent = 'Digite um CEP válido (8 dígitos).'; status.style.color = 'var(--destructive)'; return; }

    icon.className = 'bi bi-hourglass-split';
    status.textContent = 'Buscando endereço...';
    status.style.color = 'var(--text-muted)';

    try {
        const res  = await fetch(`https://brasilapi.com.br/api/cep/v1/${cep}`);
        if (!res.ok) throw new Error('CEP não encontrado.');
        const data = await res.json();

        if (data.street)       document.getElementById('endereco').value  = data.street;
        if (data.neighborhood) document.getElementById('bairro').value    = data.neighborhood;
        if (data.city)         document.getElementById('municipio').value = data.city;
        if (data.state) {
            const sel = document.getElementById('estado');
            for (let o of sel.options) { if (o.value === data.state) { o.selected = true; break; } }
        }
        status.textContent = '✓ Endereço preenchido automaticamente.';
        status.style.color = '#16a34a';
    } catch(e) {
        status.textContent = 'CEP não encontrado.';
        status.style.color = 'var(--destructive)';
    } finally {
        icon.className = 'bi bi-search';
    }
}

document.getElementById('btn-consultar-cep').addEventListener('click', buscarCep);
</script>
@endpush

@endsection

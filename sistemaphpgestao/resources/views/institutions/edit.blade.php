@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Editar Instituição</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">{{ $institution->razao_social }}</p>
    </div>
    <a href="{{ route('instituicoes.show', ['instituico' => $institution]) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

<form method="POST" action="{{ route('instituicoes.update', ['instituico' => $institution]) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    <div class="row g-3">

        {{-- ══════════════════════════════════════════
             COLUNA PRINCIPAL — abas
        ═══════════════════════════════════════════ --}}
        <div class="col-md-8">

            {{-- Navegação de abas --}}
            <ul class="nav nav-tabs mb-0" id="instTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-dados-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-dados" type="button" role="tab">
                        <i class="bi bi-building me-1"></i> Dados Principais
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-endereco-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-endereco" type="button" role="tab">
                        <i class="bi bi-geo-alt me-1"></i> Endereço da Sede
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-bancario-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-bancario" type="button" role="tab">
                        <i class="bi bi-bank me-1"></i> Dados Bancários
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-presidente-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-presidente" type="button" role="tab">
                        <i class="bi bi-person-badge me-1"></i> Presidente
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-historico-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-historico" type="button" role="tab">
                        <i class="bi bi-journal-text me-1"></i> Histórico e Estrutura
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-utilidade-btn" data-bs-toggle="tab"
                        data-bs-target="#tab-utilidade" type="button" role="tab">
                        <i class="bi bi-award me-1"></i> Utilidade Pública
                    </button>
                </li>
            </ul>

            <div class="tab-content card" style="border-top: none; border-radius: 0 0 calc(var(--radius) + 2px) calc(var(--radius) + 2px);">

                {{-- ─────────────────────────────────────────────
                     ABA 1 — Dados Principais
                ──────────────────────────────────────────────── --}}
                <div class="tab-pane fade show active p-4" id="tab-dados" role="tabpanel">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">Razão Social *</label>
                            <input type="text" name="razao_social"
                                value="{{ old('razao_social', $institution->razao_social) }}"
                                class="form-control @error('razao_social') is-invalid @enderror" required>
                            @error('razao_social')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-md-4">
                            @if(auth()->user()->isAdmin())
                                <label class="form-label">CNPJ <span style="color: var(--warning, #ca8a04); font-weight: 400; font-size: 12px;">(editável apenas para administrador)</span></label>
                                <input type="text" id="cnpj_edit" name="cnpj"
                                    value="{{ $institution->cnpj }}"
                                    class="form-control @error('cnpj') is-invalid @enderror"
                                    placeholder="00.000.000/0000-00" maxlength="18">
                                @error('cnpj')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            @else
                                <label class="form-label">CNPJ <span style="color: var(--text-muted); font-weight: 400;">(não editável)</span></label>
                                <input type="text" value="{{ $institution->cnpj }}" class="form-control" disabled>
                            @endif
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nome Fantasia</label>
                            <input type="text" name="nome_fantasia"
                                value="{{ old('nome_fantasia', $institution->nome_fantasia) }}"
                                class="form-control">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Área de Atuação</label>
                            <input type="text" name="area_atuacao"
                                value="{{ old('area_atuacao', $institution->area_atuacao) }}"
                                class="form-control" placeholder="ex: Assistência Social, Educação">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="email"
                                value="{{ old('email', $institution->email) }}"
                                class="form-control" placeholder="contato@instituicao.org.br">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone"
                                value="{{ old('telefone', $institution->telefone) }}"
                                class="form-control" placeholder="(00) 00000-0000">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Site</label>
                            <input type="text" name="site"
                                value="{{ old('site', $institution->site) }}"
                                class="form-control" placeholder="www.site.org.br">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Instagram</label>
                            <div class="input-group">
                                <span class="input-group-text">@</span>
                                <input type="text" name="instagram"
                                    value="{{ old('instagram', $institution->instagram) }}"
                                    class="form-control" placeholder="usuario ou URL do perfil">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Situação</label>
                            <div class="d-flex align-items-center gap-3" style="padding-top: 8px;">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" name="active" value="1"
                                        id="activeToggle"
                                        {{ old('active', $institution->active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="activeToggle" style="font-size: 13.5px;">
                                        Instituição ativa
                                    </label>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>{{-- /tab-dados --}}

                {{-- ─────────────────────────────────────────────
                     ABA 2 — Endereço da Sede
                ──────────────────────────────────────────────── --}}
                <div class="tab-pane fade p-4" id="tab-endereco" role="tabpanel">
                    <div class="row g-3">

                        <div class="col-md-3">
                            <label class="form-label">CEP</label>
                            <div class="input-group">
                                <input type="text" id="cep_edit" name="cep"
                                    value="{{ old('cep', $institution->cep) }}"
                                    class="form-control" placeholder="00000-000" maxlength="9">
                                <button type="button" class="btn btn-secondary" id="btn-consultar-cep-edit"
                                    title="Buscar endereço pelo CEP">
                                    <i class="bi bi-search" id="cep-edit-icon"></i>
                                </button>
                            </div>
                            <div id="cep-edit-status" class="form-text"></div>
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">Logradouro</label>
                            <input type="text" id="endereco_edit" name="endereco"
                                value="{{ old('endereco', $institution->endereco) }}"
                                class="form-control" placeholder="Rua, Avenida, Travessa...">
                        </div>

                        <div class="col-md-2">
                            <label class="form-label">Número</label>
                            <input type="text" id="numero_edit" name="numero"
                                value="{{ old('numero', $institution->numero) }}"
                                class="form-control" placeholder="Nº">
                        </div>

                        <div class="col-md-5">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento"
                                value="{{ old('complemento', $institution->complemento) }}"
                                class="form-control" placeholder="Apto, Sala, Bloco...">
                        </div>

                        <div class="col-md-7">
                            <label class="form-label">Bairro</label>
                            <input type="text" id="bairro_edit" name="bairro"
                                value="{{ old('bairro', $institution->bairro) }}"
                                class="form-control" placeholder="Nome do bairro">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Município</label>
                            <input type="text" id="municipio_edit" name="municipio"
                                value="{{ old('municipio', $institution->municipio) }}"
                                class="form-control" placeholder="Nome da cidade">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Estado (UF)</label>
                            <select id="estado_edit" name="estado" class="form-select">
                                <option value="">Selecione</option>
                                @foreach(['AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO'] as $uf)
                                <option value="{{ $uf }}" {{ old('estado', $institution->estado) == $uf ? 'selected' : '' }}>{{ $uf }}</option>
                                @endforeach
                            </select>
                        </div>

                    </div>
                </div>{{-- /tab-endereco --}}

                {{-- ─────────────────────────────────────────────
                     ABA 3 — Dados Bancários
                ──────────────────────────────────────────────── --}}
                <div class="tab-pane fade p-4" id="tab-bancario" role="tabpanel">
                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Banco</label>
                            <input type="text" name="banco"
                                value="{{ old('banco', $institution->banco) }}"
                                class="form-control" placeholder="Nome do banco">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Agência</label>
                            <input type="text" name="agencia"
                                value="{{ old('agencia', $institution->agencia) }}"
                                class="form-control" placeholder="0000-0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Conta Corrente</label>
                            <input type="text" name="conta_corrente"
                                value="{{ old('conta_corrente', $institution->conta_corrente) }}"
                                class="form-control" placeholder="00000-0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Tipo de Conta</label>
                            <select name="tipo_conta" class="form-select">
                                <option value="">Selecione...</option>
                                <option value="Conta Corrente"  {{ old('tipo_conta', $institution->tipo_conta) == 'Conta Corrente'  ? 'selected' : '' }}>Conta Corrente</option>
                                <option value="Conta Poupança"  {{ old('tipo_conta', $institution->tipo_conta) == 'Conta Poupança'  ? 'selected' : '' }}>Conta Poupança</option>
                                <option value="Conta Pagamento" {{ old('tipo_conta', $institution->tipo_conta) == 'Conta Pagamento' ? 'selected' : '' }}>Conta Pagamento</option>
                            </select>
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Chave Pix</label>
                            <input type="text" name="chave_pix"
                                value="{{ old('chave_pix', $institution->chave_pix) }}"
                                class="form-control" placeholder="CPF, CNPJ, e-mail, telefone ou chave aleatória">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Informações Bancárias</label>
                            <textarea name="dados_bancarios" class="form-control" rows="4"
                                placeholder="Informações adicionais...">{{ old('dados_bancarios', $institution->dados_bancarios) }}</textarea>
                        </div>

                    </div>
                </div>{{-- /tab-bancario --}}

                {{-- ─────────────────────────────────────────────
                     ABA 4 — Presidente
                ──────────────────────────────────────────────── --}}
                <div class="tab-pane fade p-4" id="tab-presidente" role="tabpanel">
                    <div class="row g-3">

                        <div class="col-md-8">
                            <label class="form-label">Nome</label>
                            <input type="text" name="representante_legal"
                                value="{{ old('representante_legal', $institution->representante_legal) }}"
                                class="form-control" placeholder="Nome completo do presidente">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Nascimento</label>
                            <input type="date" name="presidente_nascimento"
                                value="{{ old('presidente_nascimento', $institution->presidente_nascimento?->format('Y-m-d') ?? '') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">CPF</label>
                            <input type="text" id="presidente_cpf" name="presidente_cpf"
                                value="{{ old('presidente_cpf', $institution->presidente_cpf) }}"
                                class="form-control" placeholder="000.000.000-00" maxlength="14">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">RG</label>
                            <input type="text" name="presidente_rg"
                                value="{{ old('presidente_rg', $institution->presidente_rg) }}"
                                class="form-control" placeholder="00.000.000-0">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Data de Expedição do RG</label>
                            <input type="date" name="presidente_rg_expedicao"
                                value="{{ old('presidente_rg_expedicao', $institution->presidente_rg_expedicao?->format('Y-m-d') ?? '') }}"
                                class="form-control">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="presidente_telefone"
                                value="{{ old('presidente_telefone', $institution->presidente_telefone) }}"
                                class="form-control" placeholder="(00) 00000-0000">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">E-mail</label>
                            <input type="email" name="presidente_email"
                                value="{{ old('presidente_email', $institution->presidente_email) }}"
                                class="form-control" placeholder="email do presidente">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Endereço</label>
                            <input type="text" name="presidente_endereco"
                                value="{{ old('presidente_endereco', $institution->presidente_endereco) }}"
                                class="form-control" placeholder="Rua, número, bairro, cidade — endereço residencial">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                Foto
                                <span class="text-muted fw-normal" style="font-size: 12px;">(opcional — JPG/PNG, máx. 2 MB)</span>
                            </label>
                            @if($institution->presidente_foto)
                            <div class="mb-2 d-flex align-items-center gap-3">
                                <img src="{{ Storage::url($institution->presidente_foto) }}"
                                    alt="Foto do Presidente"
                                    style="width: 64px; height: 64px; object-fit: cover; border-radius: 50%; border: 2px solid var(--border);">
                                <span style="font-size: 12px; color: var(--text-muted);">Foto atual. Envie uma nova para substituir.</span>
                            </div>
                            @endif
                            <input type="file" name="presidente_foto" class="form-control" accept="image/*">
                        </div>

                    </div>
                </div>{{-- /tab-presidente --}}

                {{-- ─────────────────────────────────────────────
                     ABA 5 — Histórico e Estrutura
                ──────────────────────────────────────────────── --}}
                <div class="tab-pane fade p-4" id="tab-historico" role="tabpanel">
                    <div class="row g-3">

                        <div class="col-12">
                            <label class="form-label">Histórico da Entidade</label>
                            <textarea name="historico_institucional" class="form-control" rows="6"
                                placeholder="Conte a história da entidade, sua fundação e trajetória...">{{ old('historico_institucional', $institution->historico_institucional) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Descrição da Estrutura Física</label>
                            <textarea name="descricao_estrutura_fisica" class="form-control" rows="5"
                                placeholder="Descreva as instalações, salas, capacidade...">{{ old('descricao_estrutura_fisica', $institution->descricao_estrutura_fisica) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Observações de Compliance</label>
                            <textarea name="observacoes_compliance" class="form-control" rows="4">{{ old('observacoes_compliance', $institution->observacoes_compliance) }}</textarea>
                        </div>

                    </div>
                </div>{{-- /tab-historico --}}

                {{-- ─────────────────────────────────────────────
                     ABA 6 — Utilidade Pública
                ──────────────────────────────────────────────── --}}
                <div class="tab-pane fade p-4" id="tab-utilidade" role="tabpanel">

                    {{-- Municipal --}}
                    <div class="mb-4 pb-4" style="border-bottom: 1px solid var(--border);">
                        <div class="form-check mb-3">
                            <input class="form-check-input utilidade-check" type="checkbox"
                                name="utilidade_publica_municipal" value="1"
                                id="utilidade_municipal"
                                data-target="campos_municipal"
                                {{ old('utilidade_publica_municipal', $institution->utilidade_publica_municipal) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="utilidade_municipal">
                                Possui utilidade pública Municipal?
                            </label>
                        </div>
                        <div id="campos_municipal" class="row g-3"
                            style="{{ old('utilidade_publica_municipal', $institution->utilidade_publica_municipal) ? '' : 'display: none;' }}">
                            <div class="col-md-6">
                                <label class="form-label">Número da Lei</label>
                                <input type="text" name="lei_municipal_numero"
                                    value="{{ old('lei_municipal_numero', $institution->lei_municipal_numero) }}"
                                    class="form-control" placeholder="Ex: 1.234/2010">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data da Lei</label>
                                <input type="date" name="lei_municipal_data"
                                    value="{{ old('lei_municipal_data', $institution->lei_municipal_data?->format('Y-m-d') ?? '') }}"
                                    class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Arquivo / Anexo</label>
                                @if($institution->lei_municipal_arquivo)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($institution->lei_municipal_arquivo) }}"
                                        target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Ver arquivo atual
                                    </a>
                                </div>
                                @endif
                                <input type="file" name="lei_municipal_arquivo" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- Estadual --}}
                    <div class="mb-4 pb-4" style="border-bottom: 1px solid var(--border);">
                        <div class="form-check mb-3">
                            <input class="form-check-input utilidade-check" type="checkbox"
                                name="utilidade_publica_estadual" value="1"
                                id="utilidade_estadual"
                                data-target="campos_estadual"
                                {{ old('utilidade_publica_estadual', $institution->utilidade_publica_estadual) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="utilidade_estadual">
                                Possui utilidade pública Estadual?
                            </label>
                        </div>
                        <div id="campos_estadual" class="row g-3"
                            style="{{ old('utilidade_publica_estadual', $institution->utilidade_publica_estadual) ? '' : 'display: none;' }}">
                            <div class="col-md-6">
                                <label class="form-label">Número da Lei</label>
                                <input type="text" name="lei_estadual_numero"
                                    value="{{ old('lei_estadual_numero', $institution->lei_estadual_numero) }}"
                                    class="form-control" placeholder="Ex: 15.678/2015">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data da Lei</label>
                                <input type="date" name="lei_estadual_data"
                                    value="{{ old('lei_estadual_data', $institution->lei_estadual_data?->format('Y-m-d') ?? '') }}"
                                    class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Arquivo / Anexo</label>
                                @if($institution->lei_estadual_arquivo)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($institution->lei_estadual_arquivo) }}"
                                        target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Ver arquivo atual
                                    </a>
                                </div>
                                @endif
                                <input type="file" name="lei_estadual_arquivo" class="form-control">
                            </div>
                        </div>
                    </div>

                    {{-- Federal --}}
                    <div>
                        <div class="form-check mb-3">
                            <input class="form-check-input utilidade-check" type="checkbox"
                                name="utilidade_publica_federal" value="1"
                                id="utilidade_federal"
                                data-target="campos_federal"
                                {{ old('utilidade_publica_federal', $institution->utilidade_publica_federal) ? 'checked' : '' }}>
                            <label class="form-check-label fw-semibold" for="utilidade_federal">
                                Possui utilidade pública Federal?
                            </label>
                        </div>
                        <div id="campos_federal" class="row g-3"
                            style="{{ old('utilidade_publica_federal', $institution->utilidade_publica_federal) ? '' : 'display: none;' }}">
                            <div class="col-md-6">
                                <label class="form-label">Número da Lei</label>
                                <input type="text" name="lei_federal_numero"
                                    value="{{ old('lei_federal_numero', $institution->lei_federal_numero) }}"
                                    class="form-control" placeholder="Ex: 10.406/2002">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data da Lei</label>
                                <input type="date" name="lei_federal_data"
                                    value="{{ old('lei_federal_data', $institution->lei_federal_data?->format('Y-m-d') ?? '') }}"
                                    class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Arquivo / Anexo</label>
                                @if($institution->lei_federal_arquivo)
                                <div class="mb-2">
                                    <a href="{{ Storage::url($institution->lei_federal_arquivo) }}"
                                        target="_blank" class="btn btn-outline-secondary btn-sm">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Ver arquivo atual
                                    </a>
                                </div>
                                @endif
                                <input type="file" name="lei_federal_arquivo" class="form-control">
                            </div>
                        </div>
                    </div>

                </div>{{-- /tab-utilidade --}}

            </div>{{-- /tab-content --}}

        </div>{{-- /col-md-8 --}}

        {{-- ══════════════════════════════════════════
             COLUNA LATERAL — sidebar (sempre visível)
        ═══════════════════════════════════════════ --}}
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <i class="bi bi-floppy me-1"></i> Salvar Alterações
                </div>
                <div class="card-body">
                    <p style="font-size: 12.5px; color: var(--text-muted); margin-bottom: 14px;">
                        As alterações serão salvas em todas as abas ao clicar em salvar.
                    </p>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-check-lg"></i> Salvar Alterações
                        </button>
                        <a href="{{ route('instituicoes.show', ['instituico' => $institution]) }}"
                            class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </div>
            </div>
        </div>{{-- /col-md-4 --}}

    </div>{{-- /row --}}
</form>

@push('scripts')
<script>
(function () {
    'use strict';

    function mascaraCpf(v) {
        v = v.replace(/\D/g, '').slice(0, 11);
        v = v.replace(/(\d{3})(\d)/, '$1.$2');
        v = v.replace(/(\d{3})\.(\d{3})(\d)/, '$1.$2.$3');
        v = v.replace(/(\d{3})\.(\d{3})\.(\d{3})(\d)/, '$1.$2.$3-$4');
        return v;
    }

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

    var cpfInput = document.getElementById('presidente_cpf');
    if (cpfInput) {
        cpfInput.addEventListener('input', function () { this.value = mascaraCpf(this.value); });
    }

    var cnpjInput = document.getElementById('cnpj_edit');
    if (cnpjInput) {
        cnpjInput.addEventListener('input', function () { this.value = mascaraCnpj(this.value); });
    }

    var cepInput = document.getElementById('cep_edit');
    if (cepInput) {
        cepInput.addEventListener('input', function () {
            this.value = mascaraCep(this.value);
            if (this.value.replace(/\D/g,'').length === 8) buscarCepEdit();
        });
    }

    async function buscarCepEdit() {
        var cep    = document.getElementById('cep_edit').value.replace(/\D/g,'');
        var status = document.getElementById('cep-edit-status');
        var icon   = document.getElementById('cep-edit-icon');
        if (cep.length !== 8) { status.textContent = 'Digite um CEP válido (8 dígitos).'; status.style.color = 'var(--destructive)'; return; }
        icon.className = 'bi bi-hourglass-split';
        status.textContent = 'Buscando endereço...';
        status.style.color = 'var(--text-muted)';
        try {
            var res = await fetch('https://brasilapi.com.br/api/cep/v1/' + cep);
            if (!res.ok) throw new Error('CEP não encontrado.');
            var data = await res.json();
            if (data.street)       document.getElementById('endereco_edit').value  = data.street;
            if (data.neighborhood) document.getElementById('bairro_edit').value    = data.neighborhood;
            if (data.city)         document.getElementById('municipio_edit').value = data.city;
            if (data.state) {
                var sel = document.getElementById('estado_edit');
                for (var o of sel.options) { if (o.value === data.state) { o.selected = true; break; } }
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

    var btnCep = document.getElementById('btn-consultar-cep-edit');
    if (btnCep) { btnCep.addEventListener('click', buscarCepEdit); }

    // ── Utilidade pública — mostrar/ocultar campos por checkbox ───────────────
    document.querySelectorAll('.utilidade-check').forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            var targetId = this.getAttribute('data-target');
            var target   = document.getElementById(targetId);
            if (target) {
                target.style.display = this.checked ? '' : 'none';
            }
        });
    });

})();
</script>
@endpush

@endsection

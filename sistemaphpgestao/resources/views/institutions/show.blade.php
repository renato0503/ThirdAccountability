@extends('layouts.app')
@section('content')

{{-- ── Header ─────────────────────────────────────────── --}}
<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">{{ $institution->razao_social }}</h1>
        @if($institution->nome_fantasia)
            <p style="font-size: 13px; color: var(--text-muted); margin: 3px 0 0;">{{ $institution->nome_fantasia }}</p>
        @endif
    </div>
    <div class="d-flex gap-2 flex-wrap">
        <a href="{{ route('instituicoes.edit', ['instituico' => $institution]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('instituicoes.export-pdf', ['instituico' => $institution]) }}" class="btn btn-outline-secondary" data-turbo="false">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>
        <a href="{{ route('instituicoes.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

{{-- ── Tabs ─────────────────────────────────────────────── --}}
<ul class="nav nav-tabs mb-0" id="institutionTabs" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-dados" type="button" role="tab">
            <i class="bi bi-building me-1"></i> Dados Principais
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-presidente" type="button" role="tab">
            <i class="bi bi-person-badge me-1"></i> Presidente
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-diretoria" type="button" role="tab">
            <i class="bi bi-people me-1"></i> Diretoria
            @if($institution->diretoria->count())
                <span class="badge bg-secondary ms-1" style="font-size:10px;">{{ $institution->diretoria->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-conselho" type="button" role="tab">
            <i class="bi bi-shield-check me-1"></i> Conselho Fiscal
            @if($institution->conselhoFiscal->count())
                <span class="badge bg-secondary ms-1" style="font-size:10px;">{{ $institution->conselhoFiscal->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-historico" type="button" role="tab">
            <i class="bi bi-journal-text me-1"></i> Histórico
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-hist-projetos" type="button" role="tab">
            <i class="bi bi-clock-history me-1"></i> Hist. Projetos
            @if($institution->projectHistories->count())
                <span class="badge bg-secondary ms-1" style="font-size:10px;">{{ $institution->projectHistories->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-utilidade" type="button" role="tab">
            <i class="bi bi-award me-1"></i> Utilidade Pública
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-documentos" type="button" role="tab">
            <i class="bi bi-folder2-open me-1"></i> Documentos
            @if($institution->documents->count())
                <span class="badge bg-secondary ms-1" style="font-size:10px;">{{ $institution->documents->count() }}</span>
            @endif
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-projetos" type="button" role="tab">
            <i class="bi bi-kanban me-1"></i> Projetos
            @if($institution->projects->count())
                <span class="badge bg-secondary ms-1" style="font-size:10px;">{{ $institution->projects->count() }}</span>
            @endif
        </button>
    </li>
</ul>

<div class="tab-content" style="padding-top: 0;">

    {{-- ════════════════════════════════════════════════════════
         TAB 1 — Dados Principais
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade show active" id="tab-dados" role="tabpanel">
        <div class="row g-3 mt-1">

            {{-- Identificação --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-building me-1"></i> Identificação</div>
                    <div class="card-body">
                        <dl class="row mb-0" style="font-size: 13.5px; row-gap: 10px;">
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Razão Social</dt>
                            <dd class="col-7 mb-0 fw-semibold">{{ $institution->razao_social }}</dd>

                            @if($institution->nome_fantasia)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Nome Fantasia</dt>
                            <dd class="col-7 mb-0">{{ $institution->nome_fantasia }}</dd>
                            @endif

                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">CNPJ</dt>
                            <dd class="col-7 mb-0">
                                <span style="font-family: monospace;">
                                    @php
                                        $cnpj = $institution->cnpj;
                                        echo strlen($cnpj) === 14
                                            ? substr($cnpj,0,2).'.'.substr($cnpj,2,3).'.'.substr($cnpj,5,3).'/'.substr($cnpj,8,4).'-'.substr($cnpj,12,2)
                                            : $cnpj;
                                    @endphp
                                </span>
                            </dd>

                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Área de Atuação</dt>
                            <dd class="col-7 mb-0">{{ $institution->area_atuacao ?? '—' }}</dd>

                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Situação</dt>
                            <dd class="col-7 mb-0">
                                <span class="badge bg-{{ $institution->active ? 'success' : 'secondary' }}">
                                    {{ $institution->active ? 'Ativa' : 'Inativa' }}
                                </span>
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Contato --}}
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-telephone me-1"></i> Contato &amp; Localização</div>
                    <div class="card-body">
                        <dl class="row mb-0" style="font-size: 13.5px; row-gap: 10px;">
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">E-mail</dt>
                            <dd class="col-7 mb-0">
                                @if($institution->email)
                                    <a href="mailto:{{ $institution->email }}">{{ $institution->email }}</a>
                                @else
                                    —
                                @endif
                            </dd>

                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Telefone</dt>
                            <dd class="col-7 mb-0">{{ $institution->telefone ?? '—' }}</dd>

                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Site</dt>
                            <dd class="col-7 mb-0">
                                @if($institution->site)
                                    <a href="{{ $institution->site }}" target="_blank" rel="noopener">{{ $institution->site }}</a>
                                @else
                                    —
                                @endif
                            </dd>

                            @if($institution->instagram)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Instagram</dt>
                            <dd class="col-7 mb-0">
                                <i class="bi bi-instagram me-1"></i>{{ $institution->instagram }}
                            </dd>
                            @endif

                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Endereço</dt>
                            <dd class="col-7 mb-0">
                                @php
                                    $endParts = array_filter([
                                        $institution->endereco,
                                        $institution->numero  ? 'nº ' . $institution->numero : null,
                                        $institution->complemento,
                                    ]);
                                @endphp
                                {{ implode(', ', $endParts) ?: '—' }}
                                @if($institution->bairro || $institution->municipio)
                                    <br><span style="font-size: 12px; color: var(--text-muted);">
                                        @if($institution->bairro){{ $institution->bairro }}@endif
                                        @if($institution->bairro && $institution->municipio) — @endif
                                        @if($institution->municipio){{ $institution->municipio }}{{ $institution->estado ? '/' . $institution->estado : '' }}@endif
                                        @if($institution->cep) &nbsp;·&nbsp; CEP {{ $institution->cep }}@endif
                                    </span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>
            </div>

            {{-- Dados Bancários --}}
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-bank me-1"></i> Dados Bancários</div>
                    <div class="card-body">
                        @php
                            $hasBankFields = $institution->banco || $institution->agencia || $institution->conta_corrente || $institution->chave_pix;
                        @endphp
                        @if($hasBankFields)
                            <div class="row g-3">
                                <div class="col-sm-6 col-md-3">
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .04em;">Banco</div>
                                    <div style="font-size: 13.5px;">{{ $institution->banco ?? '—' }}</div>
                                </div>
                                <div class="col-sm-6 col-md-2">
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .04em;">Agência</div>
                                    <div style="font-size: 13.5px; font-family: monospace;">{{ $institution->agencia ?? '—' }}</div>
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .04em;">Conta Corrente</div>
                                    <div style="font-size: 13.5px; font-family: monospace;">{{ $institution->conta_corrente ?? '—' }}</div>
                                </div>
                                <div class="col-sm-6 col-md-2">
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .04em;">Tipo de Conta</div>
                                    <div style="font-size: 13.5px;">{{ $institution->tipo_conta ?? '—' }}</div>
                                </div>
                                @if($institution->chave_pix)
                                <div class="col-sm-12 col-md-2">
                                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; margin-bottom: 3px; text-transform: uppercase; letter-spacing: .04em;">Chave PIX</div>
                                    <div style="font-size: 13.5px; font-family: monospace;">{{ $institution->chave_pix }}</div>
                                </div>
                                @endif
                            </div>
                        @elseif($institution->dados_bancarios)
                            <div style="font-size: 13.5px; white-space: pre-line; color: var(--text);">{{ $institution->dados_bancarios }}</div>
                        @else
                            <p style="color: var(--text-muted); font-size: 13.5px; margin: 0;">Nenhum dado bancário cadastrado.</p>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 2 — Presidente
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-presidente" role="tabpanel">
        <div class="row g-3 mt-1">
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-body text-center" style="padding: 32px 18px;">
                        @if($institution->presidente_foto)
                            <img src="{{ Storage::url($institution->presidente_foto) }}"
                                 alt="Foto do Presidente"
                                 style="width: 100px; height: 100px; object-fit: cover; border-radius: 50%; border: 3px solid var(--border); margin-bottom: 12px; display: block; margin-left: auto; margin-right: auto;">
                        @else
                            <div style="width: 100px; height: 100px; border-radius: 50%; background: var(--bg-muted); border: 2px solid var(--border); display: flex; align-items: center; justify-content: center; margin: 0 auto 12px;">
                                <i class="bi bi-person" style="font-size: 40px; color: var(--text-muted);"></i>
                            </div>
                        @endif
                        <div style="font-size: 15px; font-weight: 700; color: var(--text);">{{ $institution->representante_legal ?? '—' }}</div>
                        <div style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Representante Legal / Presidente</div>
                    </div>
                </div>
            </div>
            <div class="col-lg-8">
                <div class="card h-100">
                    <div class="card-header"><i class="bi bi-person-badge me-1"></i> Dados do Presidente</div>
                    <div class="card-body">
                        <dl class="row mb-0" style="font-size: 13.5px; row-gap: 12px;">
                            <dt class="col-4" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Nome Completo</dt>
                            <dd class="col-8 mb-0 fw-semibold">{{ $institution->representante_legal ?? '—' }}</dd>

                            <dt class="col-4" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Data de Nascimento</dt>
                            <dd class="col-8 mb-0">{{ $institution->presidente_nascimento ? $institution->presidente_nascimento->format('d/m/Y') : '—' }}</dd>

                            <dt class="col-4" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">CPF</dt>
                            <dd class="col-8 mb-0" style="font-family: monospace;">{{ $institution->presidente_cpf ?? '—' }}</dd>

                            <dt class="col-4" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">RG</dt>
                            <dd class="col-8 mb-0" style="font-family: monospace;">
                                {{ $institution->presidente_rg ?? '—' }}
                                @if($institution->presidente_rg_expedicao)
                                    <span style="color: var(--text-muted); font-size: 12px; font-family: inherit;">
                                        (expedido em {{ $institution->presidente_rg_expedicao->format('d/m/Y') }})
                                    </span>
                                @endif
                            </dd>

                            <dt class="col-4" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Telefone</dt>
                            <dd class="col-8 mb-0">{{ $institution->presidente_telefone ?? '—' }}</dd>

                            <dt class="col-4" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">E-mail</dt>
                            <dd class="col-8 mb-0">
                                @if($institution->presidente_email)
                                    <a href="mailto:{{ $institution->presidente_email }}">{{ $institution->presidente_email }}</a>
                                @else
                                    —
                                @endif
                            </dd>

                            <dt class="col-4" style="color: var(--text-muted); font-weight: 500; font-size: 12px;">Endereço</dt>
                            <dd class="col-8 mb-0">{{ $institution->presidente_endereco ?? '—' }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 3 — Diretoria
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-diretoria" role="tabpanel">
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-people me-1"></i> Membros da Diretoria</span>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#formAddDiretoria">
                    <i class="bi bi-plus"></i> Adicionar Membro
                </button>
            </div>

            {{-- Formulário de adição (Diretoria) --}}
            <div class="collapse" id="formAddDiretoria">
                <div style="padding: 18px; border-bottom: 1px solid var(--border); background: var(--bg-muted);">
                    <div style="font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 14px;">
                        <i class="bi bi-person-plus me-1"></i> Novo Membro da Diretoria
                    </div>
                    <form method="POST"
                          action="{{ route('instituicoes.directors.store', ['instituico' => $institution]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tipo" value="DIRETORIA">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Nome Completo *</label>
                                <input type="text" name="nome" class="form-control form-control-sm" placeholder="Nome completo" required>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Cargo</label>
                                <input type="text" name="cargo" class="form-control form-control-sm" placeholder="Ex: Diretor Executivo">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">CPF</label>
                                <input type="text" name="cpf" class="form-control form-control-sm" placeholder="000.000.000-00">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Telefone</label>
                                <input type="text" name="telefone" class="form-control form-control-sm" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">E-mail</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="email@exemplo.com">
                            </div>
                            <div class="col-12">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Endereço</label>
                                <input type="text" name="endereco" class="form-control form-control-sm" placeholder="Endereço completo">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Início do Mandato</label>
                                <input type="date" name="mandato_inicio" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Fim do Mandato</label>
                                <input type="date" name="mandato_fim" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Foto (opcional)</label>
                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Observações</label>
                                <textarea name="observacoes" class="form-control form-control-sm" rows="2" placeholder="Observações sobre o membro..."></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check-lg"></i> Salvar Membro
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#formAddDiretoria">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Lista de membros da Diretoria --}}
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Cargo</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Mandato</th>
                            <th style="width: 90px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institution->diretoria as $d)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 13.5px; color: var(--text);">{{ $d->nome }}</div>
                            </td>
                            <td style="font-size: 13.5px;">{{ $d->cargo ?? '—' }}</td>
                            <td style="font-family: monospace; font-size: 13px;">{{ $d->cpf ?? '—' }}</td>
                            <td style="font-size: 13.5px;">{{ $d->telefone ?? '—' }}</td>
                            <td style="font-size: 13.5px;">{{ $d->email ?? '—' }}</td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                @if($d->mandato_inicio)
                                    {{ \Carbon\Carbon::parse($d->mandato_inicio)->format('d/m/Y') }}
                                    →
                                    {{ $d->mandato_fim ? \Carbon\Carbon::parse($d->mandato_fim)->format('d/m/Y') : 'em curso' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-director" title="Editar"
                                            data-bs-toggle="modal" data-bs-target="#modalEditDirector"
                                            data-director="{{ json_encode($d->only(['id','nome','cpf','cargo','email','telefone','endereco','mandato_inicio','mandato_fim','observacoes','tipo'])) }}"
                                            data-foto-url="{{ $d->foto ? asset('storage/'.$d->foto) : '' }}"
                                            data-action="{{ route('instituicoes.directors.update', ['instituico' => $institution, 'diretor' => $d]) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST"
                                          action="{{ route('instituicoes.directors.destroy', ['instituico' => $institution, 'diretor' => $d]) }}"
                                          onsubmit="return confirm('Remover este membro da diretoria?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 40px 16px; color: var(--text-muted);">
                                <i class="bi bi-people" style="font-size: 24px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                                Nenhum membro da diretoria cadastrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 4 — Conselho Fiscal
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-conselho" role="tabpanel">
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-check me-1"></i> Membros do Conselho Fiscal</span>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#formAddConselho">
                    <i class="bi bi-plus"></i> Adicionar Membro
                </button>
            </div>

            {{-- Formulário de adição (Conselho Fiscal) --}}
            <div class="collapse" id="formAddConselho">
                <div style="padding: 18px; border-bottom: 1px solid var(--border); background: var(--bg-muted);">
                    <div style="font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 14px;">
                        <i class="bi bi-person-plus me-1"></i> Novo Membro do Conselho Fiscal
                    </div>
                    <form method="POST"
                          action="{{ route('instituicoes.directors.store', ['instituico' => $institution]) }}"
                          enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="tipo" value="CONSELHO_FISCAL">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Nome Completo *</label>
                                <input type="text" name="nome" class="form-control form-control-sm" placeholder="Nome completo" required>
                            </div>
                            <div class="col-md-6">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Cargo</label>
                                <input type="text" name="cargo" class="form-control form-control-sm" placeholder="Ex: Conselheiro Fiscal">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">CPF</label>
                                <input type="text" name="cpf" class="form-control form-control-sm" placeholder="000.000.000-00">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Telefone</label>
                                <input type="text" name="telefone" class="form-control form-control-sm" placeholder="(00) 00000-0000">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">E-mail</label>
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="email@exemplo.com">
                            </div>
                            <div class="col-12">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Endereço</label>
                                <input type="text" name="endereco" class="form-control form-control-sm" placeholder="Endereço completo">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Início do Mandato</label>
                                <input type="date" name="mandato_inicio" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-3">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Fim do Mandato</label>
                                <input type="date" name="mandato_fim" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-6">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Foto (opcional)</label>
                                <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                            </div>
                            <div class="col-12">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Observações</label>
                                <textarea name="observacoes" class="form-control form-control-sm" rows="2" placeholder="Observações sobre o membro..."></textarea>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check-lg"></i> Salvar Membro
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#formAddConselho">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Lista de membros do Conselho Fiscal --}}
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Cargo</th>
                            <th>CPF</th>
                            <th>Telefone</th>
                            <th>E-mail</th>
                            <th>Mandato</th>
                            <th style="width: 90px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institution->conselhoFiscal as $d)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 13.5px; color: var(--text);">{{ $d->nome }}</div>
                            </td>
                            <td style="font-size: 13.5px;">{{ $d->cargo ?? '—' }}</td>
                            <td style="font-family: monospace; font-size: 13px;">{{ $d->cpf ?? '—' }}</td>
                            <td style="font-size: 13.5px;">{{ $d->telefone ?? '—' }}</td>
                            <td style="font-size: 13.5px;">{{ $d->email ?? '—' }}</td>
                            <td style="font-size: 12px; color: var(--text-muted);">
                                @if($d->mandato_inicio)
                                    {{ \Carbon\Carbon::parse($d->mandato_inicio)->format('d/m/Y') }}
                                    →
                                    {{ $d->mandato_fim ? \Carbon\Carbon::parse($d->mandato_fim)->format('d/m/Y') : 'em curso' }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <button type="button" class="btn btn-sm btn-outline-primary btn-edit-director" title="Editar"
                                            data-bs-toggle="modal" data-bs-target="#modalEditDirector"
                                            data-director="{{ json_encode($d->only(['id','nome','cpf','cargo','email','telefone','endereco','mandato_inicio','mandato_fim','observacoes','tipo'])) }}"
                                            data-foto-url="{{ $d->foto ? asset('storage/'.$d->foto) : '' }}"
                                            data-action="{{ route('instituicoes.directors.update', ['instituico' => $institution, 'diretor' => $d]) }}">
                                        <i class="bi bi-pencil"></i>
                                    </button>
                                    <form method="POST"
                                          action="{{ route('instituicoes.directors.destroy', ['instituico' => $institution, 'diretor' => $d]) }}"
                                          onsubmit="return confirm('Remover este membro do conselho fiscal?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center" style="padding: 40px 16px; color: var(--text-muted);">
                                <i class="bi bi-shield-check" style="font-size: 24px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                                Nenhum membro do conselho fiscal cadastrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 5 — Histórico Institucional
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-historico" role="tabpanel">
        <div class="row g-3 mt-1">

            @if($institution->historico_institucional)
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-journal-text me-1"></i> Histórico Institucional</div>
                    <div class="card-body">
                        <div style="font-size: 13.5px; color: var(--text); white-space: pre-line; line-height: 1.7;">{{ $institution->historico_institucional }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if($institution->descricao_estrutura_fisica)
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-building me-1"></i> Estrutura Física</div>
                    <div class="card-body">
                        <div style="font-size: 13.5px; color: var(--text); white-space: pre-line; line-height: 1.7;">{{ $institution->descricao_estrutura_fisica }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if($institution->observacoes_compliance)
            <div class="col-12">
                <div class="card">
                    <div class="card-header"><i class="bi bi-clipboard-check me-1"></i> Observações de Compliance</div>
                    <div class="card-body">
                        <div style="font-size: 13.5px; color: var(--text); white-space: pre-line; line-height: 1.7;">{{ $institution->observacoes_compliance }}</div>
                    </div>
                </div>
            </div>
            @endif

            @if(!$institution->historico_institucional && !$institution->descricao_estrutura_fisica && !$institution->observacoes_compliance)
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center" style="padding: 48px 16px; color: var(--text-muted);">
                        <i class="bi bi-journal-x" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: .4;"></i>
                        Nenhum histórico ou observação cadastrado para esta instituição.
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 6 — Histórico de Projetos
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-hist-projetos" role="tabpanel">
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-1"></i> Histórico de Projetos / Convênios</span>
                <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#formAddHistProjeto">
                    <i class="bi bi-plus"></i> Adicionar Registro
                </button>
            </div>

            {{-- Formulário de adição de histórico de projeto --}}
            <div class="collapse" id="formAddHistProjeto">
                <div style="padding: 18px; border-bottom: 1px solid var(--border); background: var(--bg-muted);">
                    <div style="font-size: 13px; font-weight: 600; color: var(--text); margin-bottom: 14px;">
                        <i class="bi bi-plus-circle me-1"></i> Novo Registro de Histórico
                    </div>
                    <form method="POST"
                          action="{{ route('instituicoes.project-history.store', ['instituico' => $institution]) }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Nome do Projeto / Convênio *</label>
                                <input type="text" name="nome" class="form-control form-control-sm" placeholder="Nome do projeto" required>
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Programa Estadual</label>
                                <input type="text" name="programa_estadual" class="form-control form-control-sm" placeholder="Nome do programa">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Fonte de Recurso</label>
                                <input type="text" name="fonte" class="form-control form-control-sm" placeholder="Ex: CMDCA, FIA...">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Valor (R$)</label>
                                <input type="text" name="valor" class="form-control form-control-sm" placeholder="0,00">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Nº do Convênio</label>
                                <input type="text" name="numero_convenio" class="form-control form-control-sm" placeholder="Número do convênio">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Nº do Processo</label>
                                <input type="text" name="numero_processo" class="form-control form-control-sm" placeholder="Número do processo">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Nº da Proposta</label>
                                <input type="text" name="numero_proposta" class="form-control form-control-sm" placeholder="Número da proposta">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Data de Assinatura</label>
                                <input type="date" name="data_assinatura" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Data de Publicação</label>
                                <input type="date" name="data_publicacao" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-4">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Vigência</label>
                                <input type="text" name="vigencia" class="form-control form-control-sm" placeholder="Ex: 12 meses">
                            </div>
                            <div class="col-md-8">
                                <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Publicidade / Parceria</label>
                                <input type="text" name="publicidade_parceria" class="form-control form-control-sm" placeholder="Informações sobre publicidade ou parceria">
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary">
                                    <i class="bi bi-check-lg"></i> Salvar Registro
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#formAddHistProjeto">
                                    Cancelar
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Tabela de histórico --}}
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Projeto / Convênio</th>
                            <th>Fonte</th>
                            <th>Valor</th>
                            <th>Nº Convênio</th>
                            <th>Nº Processo</th>
                            <th>Assinatura</th>
                            <th>Vigência</th>
                            <th style="width: 50px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institution->projectHistories as $h)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 13.5px; color: var(--text);">{{ $h->nome }}</div>
                                @if($h->programa_estadual)
                                    <div style="font-size: 12px; color: var(--text-muted);">{{ $h->programa_estadual }}</div>
                                @endif
                            </td>
                            <td style="font-size: 13px;">{{ $h->fonte ?? '—' }}</td>
                            <td style="font-size: 13.5px; font-weight: 600;">
                                @if($h->valor)
                                    R$ {{ number_format($h->valor, 2, ',', '.') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-family: monospace; font-size: 13px;">{{ $h->numero_convenio ?? '—' }}</td>
                            <td style="font-family: monospace; font-size: 13px;">{{ $h->numero_processo ?? '—' }}</td>
                            <td style="font-size: 13px; color: var(--text-muted);">
                                @if($h->data_assinatura)
                                    {{ \Carbon\Carbon::parse($h->data_assinatura)->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-size: 13px;">{{ $h->vigencia ?? '—' }}</td>
                            <td>
                                <form method="POST"
                                      action="{{ route('instituicoes.project-history.destroy', ['instituico' => $institution, 'historia' => $h]) }}"
                                      onsubmit="return confirm('Remover este registro do histórico?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center" style="padding: 40px 16px; color: var(--text-muted);">
                                <i class="bi bi-clock-history" style="font-size: 24px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                                Nenhum histórico de projeto cadastrado.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 7 — Utilidade Pública
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-utilidade" role="tabpanel">
        <div class="row g-3 mt-1">

            {{-- Municipal --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-award me-1"></i>
                        <span>Utilidade Pública Municipal</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            @if($institution->utilidade_publica_municipal)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Declarada</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Não declarada</span>
                            @endif
                        </div>
                        @if($institution->utilidade_publica_municipal)
                        <dl class="row mb-0" style="font-size: 13px; row-gap: 8px;">
                            @if($institution->lei_municipal_numero)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Nº da Lei</dt>
                            <dd class="col-7 mb-0">{{ $institution->lei_municipal_numero }}</dd>
                            @endif
                            @if($institution->lei_municipal_data)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Data</dt>
                            <dd class="col-7 mb-0">{{ \Carbon\Carbon::parse($institution->lei_municipal_data)->format('d/m/Y') }}</dd>
                            @endif
                            @if($institution->lei_municipal_arquivo)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Arquivo</dt>
                            <dd class="col-7 mb-0">
                                <a href="{{ Storage::url($institution->lei_municipal_arquivo) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" style="padding: 3px 8px; font-size: 11px;" data-turbo="false">
                                    <i class="bi bi-download me-1"></i> Baixar
                                </a>
                            </dd>
                            @endif
                        </dl>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Estadual --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-award-fill me-1"></i>
                        <span>Utilidade Pública Estadual</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            @if($institution->utilidade_publica_estadual)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Declarada</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Não declarada</span>
                            @endif
                        </div>
                        @if($institution->utilidade_publica_estadual)
                        <dl class="row mb-0" style="font-size: 13px; row-gap: 8px;">
                            @if($institution->lei_estadual_numero)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Nº da Lei</dt>
                            <dd class="col-7 mb-0">{{ $institution->lei_estadual_numero }}</dd>
                            @endif
                            @if($institution->lei_estadual_data)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Data</dt>
                            <dd class="col-7 mb-0">{{ \Carbon\Carbon::parse($institution->lei_estadual_data)->format('d/m/Y') }}</dd>
                            @endif
                            @if($institution->lei_estadual_arquivo)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Arquivo</dt>
                            <dd class="col-7 mb-0">
                                <a href="{{ Storage::url($institution->lei_estadual_arquivo) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" style="padding: 3px 8px; font-size: 11px;" data-turbo="false">
                                    <i class="bi bi-download me-1"></i> Baixar
                                </a>
                            </dd>
                            @endif
                        </dl>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Federal --}}
            <div class="col-md-4">
                <div class="card h-100">
                    <div class="card-header d-flex align-items-center gap-2">
                        <i class="bi bi-patch-check-fill me-1"></i>
                        <span>Utilidade Pública Federal</span>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            @if($institution->utilidade_publica_federal)
                                <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i> Declarada</span>
                            @else
                                <span class="badge bg-secondary"><i class="bi bi-x-circle me-1"></i> Não declarada</span>
                            @endif
                        </div>
                        @if($institution->utilidade_publica_federal)
                        <dl class="row mb-0" style="font-size: 13px; row-gap: 8px;">
                            @if($institution->lei_federal_numero)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Nº da Lei</dt>
                            <dd class="col-7 mb-0">{{ $institution->lei_federal_numero }}</dd>
                            @endif
                            @if($institution->lei_federal_data)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Data</dt>
                            <dd class="col-7 mb-0">{{ \Carbon\Carbon::parse($institution->lei_federal_data)->format('d/m/Y') }}</dd>
                            @endif
                            @if($institution->lei_federal_arquivo)
                            <dt class="col-5" style="color: var(--text-muted); font-weight: 500; font-size: 11px;">Arquivo</dt>
                            <dd class="col-7 mb-0">
                                <a href="{{ Storage::url($institution->lei_federal_arquivo) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" style="padding: 3px 8px; font-size: 11px;" data-turbo="false">
                                    <i class="bi bi-download me-1"></i> Baixar
                                </a>
                            </dd>
                            @endif
                        </dl>
                        @endif
                    </div>
                </div>
            </div>

        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 8 — Documentos
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-documentos" role="tabpanel">
        <div class="card mt-3">
            <div class="card-header"><i class="bi bi-folder2-open me-1"></i> Documentos da Instituição</div>
            @if($institution->documents->count())
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nome / Tipo</th>
                            <th>Descrição</th>
                            <th>Data</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($institution->documents as $doc)
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 13.5px; color: var(--text);">
                                    @if($doc->file_path)
                                        <i class="bi bi-file-earmark me-1" style="color: var(--text-muted);"></i>
                                    @endif
                                    {{ $doc->nome ?? $doc->title ?? $doc->name ?? 'Documento' }}
                                </div>
                                @if(isset($doc->tipo) && $doc->tipo)
                                    <div style="font-size: 11px; color: var(--text-muted);">{{ $doc->tipo }}</div>
                                @endif
                            </td>
                            <td style="font-size: 13px; color: var(--text-muted);">
                                {{ $doc->descricao ?? $doc->description ?? '—' }}
                            </td>
                            <td style="font-size: 13px; color: var(--text-muted);">
                                @if($doc->created_at)
                                    {{ $doc->created_at->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if(isset($doc->file_path) && $doc->file_path)
                                <a href="{{ Storage::url($doc->file_path) }}"
                                   target="_blank" rel="noopener"
                                   class="btn btn-sm btn-outline-secondary"
                                   data-turbo="false">
                                    <i class="bi bi-download"></i> Baixar
                                </a>
                                @else
                                <span style="font-size: 12px; color: var(--text-muted);">Sem arquivo</span>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="card-body text-center" style="padding: 48px 16px; color: var(--text-muted);">
                <i class="bi bi-folder2" style="font-size: 32px; display: block; margin-bottom: 10px; opacity: .4;"></i>
                Nenhum documento cadastrado para esta instituição.
            </div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════════════════════
         TAB 9 — Projetos
    ════════════════════════════════════════════════════════ --}}
    <div class="tab-pane fade" id="tab-projetos" role="tabpanel">
        <div class="card mt-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span><i class="bi bi-kanban me-1"></i> Projetos vinculados</span>
                <a href="{{ route('projetos.create') }}?institution_id={{ $institution->id }}" class="btn btn-sm btn-primary">
                    <i class="bi bi-plus"></i> Novo Projeto
                </a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Nome do Projeto</th>
                            <th>Status</th>
                            <th>Início</th>
                            <th>Término</th>
                            <th>Valor Total</th>
                            <th style="width: 60px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($institution->projects as $p)
                        @php
                            $statusColors = [
                                'EM_EXECUCAO'      => 'success',
                                'APROVADO'         => 'primary',
                                'FINALIZADO'       => 'secondary',
                                'SUSPENSO'         => 'danger',
                                'EM_ANALISE'       => 'warning',
                                'RASCUNHO'         => 'secondary',
                                'PRESTACAO_CONTAS' => 'info',
                                'CONCLUIDO'        => 'success',
                                'CANCELADO'        => 'danger',
                            ];
                        @endphp
                        <tr>
                            <td>
                                <div style="font-weight: 600; font-size: 13.5px; color: var(--text);">{{ Str::limit($p->nome, 55) }}</div>
                                @if(isset($p->codigo) && $p->codigo)
                                    <div style="font-size: 12px; color: var(--text-muted); font-family: monospace;">{{ $p->codigo }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-{{ $statusColors[$p->status] ?? 'secondary' }}">
                                    {{ $p->status_label }}
                                </span>
                            </td>
                            <td style="font-size: 13px; color: var(--text-muted);">
                                @if($p->data_inicio)
                                    {{ \Carbon\Carbon::parse($p->data_inicio)->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-size: 13px; color: var(--text-muted);">
                                @if($p->data_fim)
                                    {{ \Carbon\Carbon::parse($p->data_fim)->format('d/m/Y') }}
                                @else
                                    —
                                @endif
                            </td>
                            <td style="font-weight: 600; font-size: 13.5px;">
                                R$ {{ number_format($p->valor_total ?? 0, 2, ',', '.') }}
                            </td>
                            <td>
                                <a href="{{ route('projetos.show', ['projeto' => $p]) }}" class="btn btn-sm btn-outline-secondary" title="Visualizar projeto">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center" style="padding: 48px 16px; color: var(--text-muted);">
                                <i class="bi bi-kanban" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                                Nenhum projeto cadastrado para esta instituição.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>{{-- .tab-content --}}

{{-- ════════════════════════════════════════════════════════
     Modal: Editar Membro (Diretoria / Conselho Fiscal)
════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="modalEditDirector" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="formEditDirector" method="POST" enctype="multipart/form-data" action="">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil-square me-1"></i> <span id="editDirectorTitle">Editar Membro</span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Nome Completo *</label>
                            <input type="text" name="nome" class="form-control form-control-sm" required>
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Cargo</label>
                            <input type="text" name="cargo" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">CPF</label>
                            <input type="text" name="cpf" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Telefone</label>
                            <input type="text" name="telefone" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-4">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">E-mail</label>
                            <input type="email" name="email" class="form-control form-control-sm">
                        </div>
                        <div class="col-12">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Endereço</label>
                            <input type="text" name="endereco" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Início do Mandato</label>
                            <input type="date" name="mandato_inicio" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-3">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Fim do Mandato</label>
                            <input type="date" name="mandato_fim" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-6">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Foto (substituir)</label>
                            <input type="file" name="foto" class="form-control form-control-sm" accept="image/*">
                        </div>
                        <div class="col-12" id="editDirectorFotoAtual" style="display:none;">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Foto atual</label>
                            <div class="d-flex align-items-center gap-3" style="padding: 8px; border: 1px solid var(--border); border-radius: 4px; background: var(--bg-muted);">
                                <img id="editDirectorFotoPreview" src="" alt="Foto atual" style="max-height: 56px; max-width: 80px; border-radius: 4px;">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remover_foto" id="editDirectorRemoverFoto" value="1">
                                    <label class="form-check-label" for="editDirectorRemoverFoto" style="font-size: 12px;">Remover foto atual</label>
                                </div>
                            </div>
                        </div>
                        <div class="col-12">
                            <label style="font-size: 11px; color: var(--text-muted); font-weight: 500;">Observações</label>
                            <textarea name="observacoes" class="form-control form-control-sm" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary"><i class="bi bi-check-lg"></i> Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Preserve active tab across page visits (Turbo-compatible)
    document.addEventListener('turbo:load', restoreTab);
    document.addEventListener('DOMContentLoaded', restoreTab);

    function restoreTab() {
        const activeTab = sessionStorage.getItem('institution_active_tab_{{ $institution->id }}');
        if (activeTab) {
            const btn = document.querySelector('[data-bs-target="' + activeTab + '"]');
            if (btn) {
                const tab = bootstrap.Tab.getOrCreateInstance(btn);
                tab.show();
            }
        }

        document.querySelectorAll('#institutionTabs [data-bs-toggle="tab"]').forEach(function(btn) {
            btn.addEventListener('shown.bs.tab', function(e) {
                sessionStorage.setItem('institution_active_tab_{{ $institution->id }}', e.target.getAttribute('data-bs-target'));
            });
        });
    }

    // Edit director modal — populate fields from data-* attributes on the edit button
    (function() {
        const modalEl = document.getElementById('modalEditDirector');
        if (!modalEl) return;
        const form = document.getElementById('formEditDirector');
        const title = document.getElementById('editDirectorTitle');
        const fotoAtualWrap = document.getElementById('editDirectorFotoAtual');
        const fotoPreview = document.getElementById('editDirectorFotoPreview');
        const removerFotoChk = document.getElementById('editDirectorRemoverFoto');

        modalEl.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            if (!btn) return;
            let data = {};
            try { data = JSON.parse(btn.getAttribute('data-director') || '{}'); } catch (e) {}
            const fotoUrl = btn.getAttribute('data-foto-url') || '';
            const action = btn.getAttribute('data-action') || '';

            form.setAttribute('action', action);
            title.textContent = data.tipo === 'CONSELHO_FISCAL'
                ? 'Editar Membro do Conselho Fiscal'
                : 'Editar Membro da Diretoria';

            ['nome','cpf','cargo','email','telefone','endereco','mandato_inicio','mandato_fim','observacoes'].forEach(function(f) {
                const inp = form.querySelector('[name="'+f+'"]');
                if (!inp) return;
                let v = data[f] != null ? data[f] : '';
                if ((f === 'mandato_inicio' || f === 'mandato_fim') && typeof v === 'string' && v.length >= 10) {
                    v = v.slice(0, 10);
                }
                inp.value = v;
            });

            const fotoInp = form.querySelector('[name="foto"]');
            if (fotoInp) fotoInp.value = '';

            if (fotoUrl) {
                fotoPreview.src = fotoUrl;
                fotoAtualWrap.style.display = '';
            } else {
                fotoAtualWrap.style.display = 'none';
                fotoPreview.src = '';
            }
            removerFotoChk.checked = false;
        });
    })();
</script>
@endpush

@endsection

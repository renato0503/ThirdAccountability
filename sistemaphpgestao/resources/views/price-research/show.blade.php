@extends('layouts.app')
@section('content')

@php
    $sources    = $pesquisa->sources ?? [];
    $hasResults = $pesquisa->results->count() > 0;
@endphp

<div class="d-flex align-items-start justify-content-between mb-3">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-{{ $pesquisa->status_color }}">{{ $pesquisa->status_label }}</span>
            @foreach($sources as $src)
                <span class="badge bg-light">{{ $src }}</span>
            @endforeach
        </div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">{{ $pesquisa->search_term }}</h1>
        <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
            Pesquisa #{{ $pesquisa->id }} — {{ $pesquisa->institution?->razao_social ?? '—' }}
            @if($pesquisa->project)
                · Projeto: <a href="{{ route('projetos.show', $pesquisa->project) }}">{{ $pesquisa->project->nome }}</a>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2 flex-wrap justify-content-end">
        <form method="POST" action="{{ route('pesquisa-precos.search', $pesquisa) }}">
            @csrf
            <button class="btn btn-primary"><i class="bi bi-search"></i> Buscar preços</button>
        </form>
        <a href="{{ route('pesquisa-precos.export-pdf', $pesquisa) }}" class="btn btn-outline-secondary" data-turbo="false">
            <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
        </a>
        <a href="{{ route('pesquisa-precos.edit', $pesquisa) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('pesquisa-precos.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

{{-- Estatísticas --}}
<div class="row g-3 mb-3">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Menor preço</div>
            <div class="stat-value" style="font-size: 18px;">
                @if($stats['min'] !== null) R$ {{ number_format($stats['min'], 2, ',', '.') }} @else — @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Maior preço</div>
            <div class="stat-value" style="font-size: 18px;">
                @if($stats['max'] !== null) R$ {{ number_format($stats['max'], 2, ',', '.') }} @else — @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Média</div>
            <div class="stat-value" style="font-size: 18px;">
                @if($stats['avg'] !== null) R$ {{ number_format($stats['avg'], 2, ',', '.') }} @else — @endif
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="stat-label">Mediana</div>
            <div class="stat-value" style="font-size: 18px;">
                @if($stats['median'] !== null) R$ {{ number_format($stats['median'], 2, ',', '.') }} @else — @endif
            </div>
        </div>
    </div>
</div>

<div class="alert alert-info d-flex flex-wrap align-items-center gap-2 mb-3" style="font-size: 12.5px;">
    <i class="bi bi-info-circle"></i>
    <span><strong>Compliance:</strong> nenhum resultado é escondido. Todos os preços encontrados são exibidos para análise.</span>
    <span class="ms-auto">
        Pesquisado em: <strong>{{ $pesquisa->searched_at?->format('d/m/Y H:i') ?? 'ainda não' }}</strong>
        · Resultados: <strong>{{ $stats['count'] }}</strong>
    </span>
</div>

{{-- Tabs --}}
<ul class="nav nav-tabs mb-3" id="prTabs">
    <li class="nav-item">
        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-results">
            <i class="bi bi-list-ul me-1"></i> Resultados
            <span class="badge bg-secondary ms-1">{{ $stats['count'] }}</span>
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-reference">
            <i class="bi bi-bullseye me-1"></i> Preço de referência
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-manual">
            <i class="bi bi-plus-square me-1"></i> Adicionar manualmente
        </button>
    </li>
    <li class="nav-item">
        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-info">
            <i class="bi bi-info-circle me-1"></i> Parâmetros e fontes
        </button>
    </li>
</ul>

<div class="tab-content">

{{-- TAB: RESULTADOS --}}
<div class="tab-pane fade show active" id="tab-results">

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Filtrar por descrição</label>
                    <input type="text" name="filter_text" value="{{ request('filter_text') }}" class="form-control" placeholder="palavra na descrição">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Fonte</label>
                    <select name="filter_source" class="form-select">
                        <option value="">Todas</option>
                        <option value="PNCP" @selected(request('filter_source')=='PNCP')>PNCP</option>
                        <option value="RADAR_TCE_MT" @selected(request('filter_source')=='RADAR_TCE_MT')>Radar TCE-MT</option>
                        <option value="MANUAL" @selected(request('filter_source')=='MANUAL')>Manual</option>
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label">UF</label>
                    <input type="text" maxlength="2" name="filter_state" value="{{ request('filter_state') }}" class="form-control" style="text-transform:uppercase">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Município</label>
                    <input type="text" name="filter_city" value="{{ request('filter_city') }}" class="form-control">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Ordenar</label>
                    <select name="sort" class="form-select">
                        <option value="unit_price_asc"  @selected(request('sort','unit_price_asc')=='unit_price_asc')>Menor preço</option>
                        <option value="unit_price_desc" @selected(request('sort')=='unit_price_desc')>Maior preço</option>
                        <option value="date_desc"       @selected(request('sort')=='date_desc')>Mais recente</option>
                        <option value="similarity_desc" @selected(request('sort')=='similarity_desc')>Maior similaridade</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button class="btn btn-secondary"><i class="bi bi-funnel"></i> Aplicar</button>
                    <a href="{{ route('pesquisa-precos.show', $pesquisa) }}" class="btn btn-outline-secondary">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    @if(!$hasResults)
        <div class="card">
            <div class="card-body text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size: 28px;"></i>
                <div class="mt-2">Nenhum resultado registrado ainda.</div>
                <form method="POST" action="{{ route('pesquisa-precos.search', $pesquisa) }}" class="mt-3 d-inline">
                    @csrf
                    <button class="btn btn-primary"><i class="bi bi-search"></i> Executar busca agora</button>
                </form>
            </div>
        </div>
    @else
        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0 align-middle">
                    <thead>
                        <tr>
                            <th style="width: 36px;">Sel.</th>
                            <th>Fonte</th>
                            <th>Descrição original</th>
                            <th class="text-end">Valor unit.</th>
                            <th class="text-end">Qtd / Un.</th>
                            <th class="text-end">Total</th>
                            <th>Órgão / Município</th>
                            <th>Refs.</th>
                            <th>Data</th>
                            <th class="text-end" style="width: 60px;">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($results as $r)
                            <tr class="{{ $r->selected ? 'table-active' : '' }}">
                                <td>
                                    <form method="POST" action="{{ route('pesquisa-precos.results.select', [$pesquisa, $r]) }}" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <input type="hidden" name="selected" value="{{ $r->selected ? 0 : 1 }}">
                                        <button class="btn btn-sm btn-outline-{{ $r->selected ? 'success' : 'secondary' }}" title="{{ $r->selected ? 'Desmarcar' : 'Selecionar' }}">
                                            <i class="bi bi-{{ $r->selected ? 'check-square-fill' : 'square' }}"></i>
                                        </button>
                                    </form>
                                </td>
                                <td><span class="badge bg-light">{{ $r->source_label }}</span></td>
                                <td>
                                    <div style="font-size: 13px; line-height: 1.4;">{{ \Illuminate\Support\Str::limit($r->original_description, 180) }}</div>
                                    @if($r->similarity_score)
                                        <div class="text-muted" style="font-size: 11px;">Similaridade: {{ number_format($r->similarity_score * 100, 0) }}%</div>
                                    @endif
                                    @if($r->selected && $r->selection_justification)
                                        <div class="text-success mt-1" style="font-size: 11px;"><i class="bi bi-quote"></i> {{ $r->selection_justification }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold">R$ {{ number_format($r->unit_price, 2, ',', '.') }}</td>
                                <td class="text-end">
                                    @if($r->quantity) {{ rtrim(rtrim(number_format($r->quantity, 2, ',', '.'), '0'), ',') }} @endif
                                    @if($r->unit) <div class="text-muted" style="font-size:11px;">{{ $r->unit }}</div> @endif
                                </td>
                                <td class="text-end">
                                    @if($r->total_price) R$ {{ number_format($r->total_price, 2, ',', '.') }} @else — @endif
                                </td>
                                <td>
                                    <div>{{ $r->buyer_name ?? '—' }}</div>
                                    <div class="text-muted" style="font-size: 11px;">
                                        {{ trim(($r->city ?? '') . ' / ' . ($r->state ?? ''), ' /') ?: '—' }}
                                    </div>
                                </td>
                                <td style="font-size: 11px;">
                                    @if($r->ata_number)      <div>Ata: {{ $r->ata_number }}</div>      @endif
                                    @if($r->bid_number)      <div>Lic.: {{ $r->bid_number }}</div>     @endif
                                    @if($r->contract_number) <div>Contr.: {{ $r->contract_number }}</div> @endif
                                    @if($r->process_number)  <div>Proc.: {{ $r->process_number }}</div> @endif
                                </td>
                                <td>{{ $r->purchase_date?->format('d/m/Y') ?? '—' }}</td>
                                <td class="text-end">
                                    @if($r->source_url)
                                        <a href="{{ $r->source_url }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-secondary" title="Abrir fonte" data-turbo="false">
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('pesquisa-precos.results.destroy', [$pesquisa, $r]) }}" class="d-inline" onsubmit="return confirm('Remover este resultado?')">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Remover"><i class="bi bi-x"></i></button>
                                    </form>
                                </td>
                            </tr>
                            @if($r->selected)
                                <tr class="collapse show table-active">
                                    <td colspan="10" style="background: #f0fdf4;">
                                        <form method="POST" action="{{ route('pesquisa-precos.results.select', [$pesquisa, $r]) }}" class="row g-2 align-items-end">
                                            @csrf
                                            @method('PATCH')
                                            <input type="hidden" name="selected" value="1">
                                            <div class="col-md-10">
                                                <label class="form-label">Justificativa da escolha (compliance)</label>
                                                <input type="text" name="selection_justification" value="{{ $r->selection_justification }}"
                                                       class="form-control"
                                                       placeholder="Ex.: especificação compatível com o item do projeto">
                                            </div>
                                            <div class="col-md-2 d-grid">
                                                <button class="btn btn-success btn-sm"><i class="bi bi-save"></i> Salvar justificativa</button>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- TAB: PREÇO DE REFERÊNCIA --}}
<div class="tab-pane fade" id="tab-reference">
    <div class="card">
        <div class="card-header">Definir preço de referência da cotação</div>
        <div class="card-body">
            <form method="POST" action="{{ route('pesquisa-precos.set-reference', $pesquisa) }}">
                @csrf
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Tipo de referência *</label>
                        <select name="reference_type" id="ref-type" class="form-select" required>
                            <option value="MENOR"   @selected($pesquisa->reference_type === 'MENOR')>Menor preço encontrado</option>
                            <option value="MAIOR"   @selected($pesquisa->reference_type === 'MAIOR')>Maior preço encontrado</option>
                            <option value="MEDIA"   @selected($pesquisa->reference_type === 'MEDIA')>Média</option>
                            <option value="MEDIANA" @selected($pesquisa->reference_type === 'MEDIANA')>Mediana</option>
                            <option value="ITEM"    @selected($pesquisa->reference_type === 'ITEM')>Item selecionado da lista</option>
                            <option value="MANUAL"  @selected($pesquisa->reference_type === 'MANUAL')>Valor manual justificado</option>
                        </select>
                    </div>

                    <div class="col-md-4" id="ref-manual" style="display:none;">
                        <label class="form-label">Valor manual (R$)</label>
                        <input type="number" step="0.01" min="0" name="selected_reference_price" value="{{ $pesquisa->selected_reference_price }}" class="form-control">
                    </div>

                    <div class="col-md-4" id="ref-item" style="display:none;">
                        <label class="form-label">Resultado escolhido</label>
                        <select name="reference_result_id" class="form-select">
                            <option value="">—</option>
                            @foreach($pesquisa->results as $r)
                                <option value="{{ $r->id }}">
                                    R$ {{ number_format($r->unit_price, 2, ',', '.') }}
                                    — {{ \Illuminate\Support\Str::limit($r->original_description, 60) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Justificativa *</label>
                        <textarea name="justification" rows="3" class="form-control" required minlength="10"
                                  placeholder="Ex.: Preço utilizado como referência por apresentar especificação compatível com o item do projeto.">{{ $pesquisa->justification }}</textarea>
                        <div class="text-muted mt-1" style="font-size: 11.5px;">
                            Obrigatório para auditoria e prestação de contas.
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3 gap-2">
                    <button class="btn btn-primary"><i class="bi bi-save"></i> Salvar preço de referência</button>
                </div>
            </form>

            @if($pesquisa->selected_reference_price !== null)
                <hr class="my-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <div class="text-muted" style="font-size: 11px; text-transform: uppercase;">Preço de referência atual</div>
                        <div style="font-size: 22px; font-weight: 700;">R$ {{ number_format($pesquisa->selected_reference_price, 2, ',', '.') }}</div>
                        <div class="text-muted" style="font-size: 12px;">{{ $pesquisa->reference_type_label }}</div>
                    </div>
                    <div class="col-md-8">
                        <div class="text-muted" style="font-size: 11px; text-transform: uppercase;">Justificativa</div>
                        <div style="font-size: 13.5px; line-height: 1.5;">{{ $pesquisa->justification }}</div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- TAB: MANUAL --}}
<div class="tab-pane fade" id="tab-manual">
    <div class="row g-3">
        <div class="col-md-7">
            <div class="card">
                <div class="card-header">Adicionar resultado manualmente</div>
                <div class="card-body">
                    <form method="POST" action="{{ route('pesquisa-precos.results.store', $pesquisa) }}">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label">Fonte *</label>
                                <select name="source" class="form-select" required>
                                    <option value="MANUAL">Manual</option>
                                    <option value="RADAR_TCE_MT">Radar TCE-MT</option>
                                    <option value="PNCP">PNCP</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Valor unitário (R$) *</label>
                                <input type="number" step="0.01" min="0" name="unit_price" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Data</label>
                                <input type="date" name="purchase_date" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descrição *</label>
                                <textarea name="original_description" rows="2" class="form-control" required></textarea>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Quantidade</label>
                                <input type="number" step="0.01" min="0" name="quantity" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Unidade</label>
                                <select name="unit" class="form-select">
                                    <option value="">—</option>
                                    @foreach($units as $u)<option value="{{ $u }}">{{ $u }}</option>@endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Total (R$)</label>
                                <input type="number" step="0.01" min="0" name="total_price" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">UF</label>
                                <input type="text" maxlength="2" style="text-transform: uppercase" name="state" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Município</label>
                                <input type="text" name="city" class="form-control">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Órgão comprador</label>
                                <input type="text" name="buyer_name" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nº Ata</label>
                                <input type="text" name="ata_number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nº Licitação</label>
                                <input type="text" name="bid_number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nº Contrato</label>
                                <input type="text" name="contract_number" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Nº Processo</label>
                                <input type="text" name="process_number" class="form-control">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Link da fonte</label>
                                <input type="url" name="source_url" class="form-control" placeholder="https://...">
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button class="btn btn-primary"><i class="bi bi-plus"></i> Adicionar resultado</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="card mb-3">
                <div class="card-header">Consultar manualmente nas fontes</div>
                <div class="card-body">
                    <p class="text-muted" style="font-size: 12.5px;">
                        Use os links abaixo para abrir o termo "<strong>{{ $pesquisa->search_term }}</strong>" diretamente no painel
                        público da fonte. Depois traga os preços via formulário ao lado.
                    </p>
                    <a href="{{ $pncpUrl }}"  class="btn btn-outline-secondary w-100 mb-2" target="_blank" rel="noopener" data-turbo="false">
                        <i class="bi bi-box-arrow-up-right"></i> Abrir PNCP — Atas
                    </a>
                    <a href="{{ $radarUrl }}" class="btn btn-outline-secondary w-100" target="_blank" rel="noopener" data-turbo="false">
                        <i class="bi bi-box-arrow-up-right"></i> Abrir Radar TCE-MT
                    </a>
                </div>
            </div>
            <div class="card">
                <div class="card-body" style="font-size: 12.5px; color: var(--text-muted);">
                    <i class="bi bi-shield-check"></i>
                    A integração com o Radar TCE-MT é feita em modo <strong>consulta assistida</strong>:
                    o sistema não burla captcha nem login — você consulta o painel manualmente e
                    registra os preços encontrados aqui, mantendo a auditabilidade.
                </div>
            </div>
        </div>
    </div>
</div>

{{-- TAB: INFO --}}
<div class="tab-pane fade" id="tab-info">
    <div class="row g-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">Parâmetros da pesquisa</div>
                <div class="card-body">
                    <dl class="row mb-0" style="font-size: 13.5px; row-gap: 8px;">
                        <dt class="col-5 text-muted">Termo</dt>           <dd class="col-7">{{ $pesquisa->search_term }}</dd>
                        <dt class="col-5 text-muted">Categoria</dt>       <dd class="col-7">{{ $pesquisa->category ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Quantidade</dt>      <dd class="col-7">{{ $pesquisa->quantity ?? '—' }} {{ $pesquisa->unit }}</dd>
                        <dt class="col-5 text-muted">Estado/Município</dt><dd class="col-7">{{ trim(($pesquisa->city ?? '').' / '.($pesquisa->state ?? ''), ' /') ?: '—' }}</dd>
                        <dt class="col-5 text-muted">Período</dt>         <dd class="col-7">{{ $pesquisa->date_start?->format('d/m/Y') ?? '—' }} a {{ $pesquisa->date_end?->format('d/m/Y') ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Fontes</dt>          <dd class="col-7">{{ implode(', ', $sources) }}</dd>
                        <dt class="col-5 text-muted">Pesquisado em</dt>   <dd class="col-7">{{ $pesquisa->searched_at?->format('d/m/Y H:i') ?? '—' }}</dd>
                        <dt class="col-5 text-muted">Responsável</dt>     <dd class="col-7">{{ $pesquisa->user?->name ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header">Observações internas</div>
                <div class="card-body" style="font-size: 13.5px; line-height: 1.5;">
                    {{ $pesquisa->notes ?: 'Sem observações.' }}
                </div>
            </div>
        </div>
    </div>
</div>

</div>{{-- /tab-content --}}

@push('scripts')
<script>
    function toggleRefFields() {
        const t = document.getElementById('ref-type');
        if (!t) return;
        document.getElementById('ref-manual').style.display = (t.value === 'MANUAL') ? '' : 'none';
        document.getElementById('ref-item').style.display   = (t.value === 'ITEM')   ? '' : 'none';
    }
    document.addEventListener('DOMContentLoaded', () => {
        toggleRefFields();
        document.getElementById('ref-type')?.addEventListener('change', toggleRefFields);
    });
    document.addEventListener('turbo:load', () => {
        toggleRefFields();
        document.getElementById('ref-type')?.addEventListener('change', toggleRefFields);
    });
</script>
@endpush

@endsection

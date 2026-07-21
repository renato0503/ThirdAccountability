@extends('layouts.app')
@section('content')

<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Pesquisa de Preços Públicos</h1>
        <div style="font-size: 13px; color: var(--text-muted); margin-top: 2px;">
            Cotações de referência baseadas em fontes públicas (PNCP / Radar TCE-MT).
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('pesquisa-precos.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nova pesquisa
        </a>
    </div>
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <label class="form-label">Termo</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="ex.: bola, uniforme esportivo, notebook">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">Todos</option>
                    @foreach($statuses as $st)
                        <option value="{{ $st }}" @selected(request('status') === $st)>{{ $st }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button class="btn btn-secondary"><i class="bi bi-search"></i> Filtrar</button>
                @if(request('search') || request('status'))
                    <a href="{{ route('pesquisa-precos.index') }}" class="btn btn-outline-secondary">Limpar</a>
                @endif
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Termo / Categoria</th>
                    <th>Projeto</th>
                    <th>Fontes</th>
                    <th>Resultados</th>
                    <th>Preço Ref.</th>
                    <th>Status</th>
                    <th>Pesquisada em</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($pesquisas as $p)
                    <tr>
                        <td>
                            <div class="fw-semibold">{{ $p->search_term }}</div>
                            @if($p->category)
                                <div class="text-muted" style="font-size: 12px;">{{ $p->category }}</div>
                            @endif
                        </td>
                        <td>
                            @if($p->project)
                                <a href="{{ route('projetos.show', $p->project) }}">{{ $p->project->nome }}</a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>
                            @foreach(($p->sources ?? []) as $src)
                                <span class="badge bg-light">{{ $src }}</span>
                            @endforeach
                        </td>
                        <td>
                            <span class="badge bg-secondary">{{ $p->results_count }}</span>
                            @if($p->selected_results_count)
                                <span class="badge bg-success ms-1">{{ $p->selected_results_count }} sel.</span>
                            @endif
                        </td>
                        <td>
                            @if($p->selected_reference_price !== null)
                                <span class="fw-semibold">R$ {{ number_format($p->selected_reference_price, 2, ',', '.') }}</span>
                                <div class="text-muted" style="font-size: 11px;">{{ $p->reference_type_label }}</div>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td><span class="badge bg-{{ $p->status_color }}">{{ $p->status_label }}</span></td>
                        <td>{{ $p->searched_at?->format('d/m/Y H:i') ?? '—' }}</td>
                        <td class="text-end">
                            <a href="{{ route('pesquisa-precos.show', $p) }}" class="btn btn-sm btn-outline-secondary" title="Ver">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('pesquisa-precos.export-pdf', $p) }}" class="btn btn-sm btn-outline-secondary" title="PDF" data-turbo="false">
                                <i class="bi bi-file-earmark-pdf"></i>
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            Nenhuma pesquisa registrada ainda.
                            <a href="{{ route('pesquisa-precos.create') }}">Criar a primeira</a>.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($pesquisas->hasPages())
        <div class="card-footer d-flex justify-content-end">
            {{ $pesquisas->links() }}
        </div>
    @endif
</div>

@endsection

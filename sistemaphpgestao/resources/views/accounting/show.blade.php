@extends('layouts.app')
@section('content')

@php $sc = ['RASCUNHO' => 'secondary', 'ENVIADA' => 'info', 'EM_ANALISE' => 'warning', 'APROVADA' => 'success', 'REPROVADA' => 'danger']; @endphp

<div class="d-flex align-items-start justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Prestação de Contas #{{ $report->id }}</h1>
        <div class="mt-1 d-flex align-items-center gap-2">
            <span class="badge bg-{{ $sc[$report->status] ?? 'secondary' }}">{{ $report->status_label }}</span>
            @if($report->project)
                <span style="font-size: 13px; color: var(--text-muted);">{{ $report->project->nome }}</span>
            @endif
        </div>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('prestacao-contas.edit', ['prestacao_conta' => $report]) }}" class="btn btn-outline-secondary">
            <i class="bi bi-pencil"></i> Editar
        </a>
        <a href="{{ route('prestacao-contas.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Voltar
        </a>
    </div>
</div>

<div class="row g-3">

    {{-- ── Coluna lateral ── --}}
    <div class="col-md-4">

        <div class="card mb-3">
            <div class="card-header">Informações</div>
            <div class="card-body">
                <dl class="row mb-0" style="font-size: 13.5px; row-gap: 8px;">
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Projeto</dt>
                    <dd class="col-7 mb-0">{{ $report->project?->nome ?? '—' }}</dd>
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Instituição</dt>
                    <dd class="col-7 mb-0">{{ $report->project?->institution?->razao_social ?? '—' }}</dd>
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Data Envio</dt>
                    <dd class="col-7 mb-0">{{ $report->data_envio?->format('d/m/Y') ?? '—' }}</dd>
                    <dt class="col-5" style="color: var(--text-muted); font-weight: 500;">Data Aprovação</dt>
                    <dd class="col-7 mb-0">{{ $report->data_aprovacao?->format('d/m/Y') ?? '—' }}</dd>
                </dl>
            </div>
        </div>

        {{-- Resumo Financeiro --}}
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-cash-stack me-1"></i> Financeiro do Projeto</div>
            <div class="card-body">
                <div class="mb-2">
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: .04em;">Valor Total</div>
                    <div style="font-size: 16px; font-weight: 700;">R$ {{ number_format($report->project?->valor_total ?? 0, 2, ',', '.') }}</div>
                </div>
                <div>
                    <div style="font-size: 11px; color: var(--text-muted); font-weight: 500; text-transform: uppercase; letter-spacing: .04em;">Valor Executado</div>
                    <div style="font-size: 16px; font-weight: 700; color: #d97706;">R$ {{ number_format($report->project?->valor_executado ?? 0, 2, ',', '.') }}</div>
                </div>
            </div>
        </div>

        @if($report->observacoes)
        <div class="card mb-3">
            <div class="card-header">Observações</div>
            <div class="card-body" style="font-size: 13.5px; line-height: 1.6;">{{ $report->observacoes }}</div>
        </div>
        @endif

        {{-- Links de Vídeos --}}
        @if($report->links_videos)
        <div class="card">
            <div class="card-header"><i class="bi bi-play-circle me-1"></i> Links de Vídeos</div>
            <div class="card-body" style="font-size: 13.5px;">
                @foreach(array_filter(explode("\n", $report->links_videos)) as $link)
                    @php $link = trim($link); @endphp
                    @if($link)
                    <div class="mb-1">
                        <a href="{{ $link }}" target="_blank" rel="noopener" style="word-break: break-all;">
                            <i class="bi bi-link-45deg me-1"></i>{{ $link }}
                        </a>
                    </div>
                    @endif
                @endforeach
            </div>
        </div>
        @endif

    </div>

    {{-- ── Coluna principal ── --}}
    <div class="col-md-8">

        {{-- Relatório Textual --}}
        @if($report->relatorio_texto)
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-file-text me-1"></i> Relatório de Atividades</div>
            <div class="card-body" style="font-size: 14px; line-height: 1.75; white-space: pre-line;">{{ $report->relatorio_texto }}</div>
        </div>
        @endif

        {{-- Metas Aprovadas --}}
        @if($metasAprovadas->count() > 0)
        <div class="card mb-3">
            <div class="card-header d-flex align-items-center gap-2">
                <i class="bi bi-bullseye me-1 text-success"></i>
                <span>Metas Aprovadas do Projeto</span>
                <span class="badge bg-success ms-1">{{ $metasAprovadas->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th style="width: 60px;">Nº</th>
                            <th>Meta</th>
                            <th>Tipo</th>
                            <th>Qtd. Prevista</th>
                            <th>Qtd. Realizada</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($metasAprovadas as $meta)
                        @php
                            $gcols = ['APROVADA' => 'success', 'APROVADA_COM_RESSALVA' => 'warning'];
                        @endphp
                        <tr>
                            <td style="font-family: monospace; font-weight: 600;">{{ $meta->numero ?? $loop->iteration }}</td>
                            <td>
                                <div style="font-weight: 600; font-size: 13px;">{{ $meta->titulo }}</div>
                                @if($meta->descricao)
                                    <div style="font-size: 12px; color: var(--text-muted);">{{ Str::limit($meta->descricao, 80) }}</div>
                                @endif
                            </td>
                            <td>
                                <span class="badge {{ $meta->tipo_meta === 'QUANTITATIVA' ? 'bg-info' : 'bg-secondary' }}" style="font-size: 10px;">
                                    {{ $meta->tipo_meta ?? '—' }}
                                </span>
                            </td>
                            <td style="font-size: 13px;">{{ $meta->quantidade_prevista ?? '—' }} {{ $meta->unidade_medida }}</td>
                            <td style="font-size: 13px; font-weight: 600; color: #16a34a;">{{ $meta->quantidade_realizada ?? '—' }}</td>
                            <td>
                                <span class="badge bg-{{ $gcols[$meta->status] ?? 'success' }}">{{ $meta->status_label }}</span>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        {{-- Fotos --}}
        @if($report->fotos && count($report->fotos) > 0)
        <div class="card mb-3">
            <div class="card-header"><i class="bi bi-images me-1"></i> Fotos</div>
            <div class="card-body">
                <div class="row g-3">
                    @foreach($report->fotos as $idx => $foto)
                    <div class="col-sm-6 col-md-4">
                        <div class="position-relative">
                            <img src="{{ Storage::url($foto) }}" alt="Foto {{ $idx + 1 }}"
                                style="width: 100%; height: 160px; object-fit: cover; border-radius: var(--radius); border: 1px solid var(--border);"
                                onerror="this.style.display='none'">
                            <form method="POST" action="{{ route('prestacao-contas.remove-photo', ['prestacao_conta' => $report]) }}"
                                class="position-absolute top-0 end-0 m-1"
                                onsubmit="return confirm('Remover esta foto?')">
                                @csrf
                                @method('DELETE')
                                <input type="hidden" name="index" value="{{ $idx }}">
                                <button type="submit" class="btn btn-sm btn-danger" style="padding: 2px 6px;" title="Remover foto">
                                    <i class="bi bi-x-lg"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Despesas do Projeto --}}
        <div class="card">
            <div class="card-header"><i class="bi bi-receipt me-1"></i> Despesas do Projeto</div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Descrição</th>
                            <th>Fornecedor</th>
                            <th>Data</th>
                            <th>Valor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($report->project?->expenses ?? [] as $e)
                        @php $sc2 = ['PAGO' => 'success', 'APROVADO' => 'primary', 'PENDENTE' => 'warning', 'REJEITADO' => 'danger']; @endphp
                        <tr>
                            <td style="font-size: 13px;">{{ Str::limit($e->descricao, 40) }}</td>
                            <td style="font-size: 13px;">{{ $e->fornecedor ?? '—' }}</td>
                            <td style="font-size: 13px;">{{ $e->data_despesa?->format('d/m/Y') ?? '—' }}</td>
                            <td style="font-size: 13px; font-weight: 600;">R$ {{ number_format($e->valor, 2, ',', '.') }}</td>
                            <td>
                                <span class="badge bg-{{ $sc2[$e->status] ?? 'secondary' }}">{{ $e->status }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center" style="padding: 32px 16px; color: var(--text-muted);">
                                Nenhuma despesa registrada para este projeto.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>

@endsection

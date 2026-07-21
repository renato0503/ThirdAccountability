@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Diligências</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Acompanhe as solicitações, respostas e análise de metas</p>
    </div>
    <a href="{{ route('diligencias.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Nova Diligência
    </a>
</div>

{{-- ── Metas Aguardando Análise ─────────────────────────────────── --}}
@if($metasEmAnalise->count() > 0)
<div class="card mb-4" style="border-color: #f59e0b;">
    <div class="card-header d-flex align-items-center gap-2" style="background: #fffbeb; border-color: #f59e0b;">
        <i class="bi bi-hourglass-split text-warning"></i>
        <span style="font-weight: 600; color: #92400e;">Metas Aguardando Análise</span>
        <span class="badge bg-warning text-dark ms-1">{{ $metasEmAnalise->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Meta</th>
                    <th>Projeto</th>
                    <th>Tipo</th>
                    <th>Responsável</th>
                    <th>Enviada em</th>
                    <th style="min-width: 340px;">Parecer</th>
                </tr>
            </thead>
            <tbody>
                @foreach($metasEmAnalise as $meta)
                <tr>
                    <td>
                        <div style="font-weight: 600; font-size: 13.5px;">
                            @if($meta->numero)<span class="badge bg-dark me-1" style="font-size: 10px;">{{ $meta->numero }}</span>@endif
                            {{ Str::limit($meta->titulo, 50) }}
                        </div>
                        @if($meta->descricao)
                            <div style="font-size: 12px; color: var(--text-muted);">{{ Str::limit($meta->descricao, 60) }}</div>
                        @endif
                    </td>
                    <td style="font-size: 13px;">
                        <a href="{{ route('projetos.show', ['projeto' => $meta->project]) }}" style="text-decoration: none;">
                            {{ Str::limit($meta->project->nome ?? '—', 40) }}
                        </a>
                    </td>
                    <td>
                        <span class="badge {{ $meta->tipo_meta === 'QUANTITATIVA' ? 'bg-info' : 'bg-secondary' }}">
                            {{ $meta->tipo_meta ?? '—' }}
                        </span>
                    </td>
                    <td style="font-size: 13px;">{{ $meta->responsavel ?? '—' }}</td>
                    <td style="font-size: 13px; color: var(--text-muted);">{{ $meta->updated_at->format('d/m/Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('diligencias.metas.parecer', ['meta' => $meta]) }}" class="d-flex align-items-center gap-2 flex-wrap">
                            @csrf
                            <input type="text" name="observacoes" class="form-control form-control-sm"
                                placeholder="Observações (opcional)" style="min-width: 150px; max-width: 200px;">
                            <button type="submit" name="novo_status" value="APROVADA"
                                class="btn btn-sm btn-success" title="Aprovar">
                                <i class="bi bi-check-circle"></i> Aprovar
                            </button>
                            <button type="submit" name="novo_status" value="APROVADA_COM_RESSALVA"
                                class="btn btn-sm btn-warning text-dark" title="Aprovada com ressalva">
                                <i class="bi bi-exclamation-circle"></i> Ressalva
                            </button>
                            <button type="submit" name="novo_status" value="REPROVADA"
                                class="btn btn-sm btn-danger" title="Reprovar"
                                onclick="return confirm('Confirmar reprovação desta meta?')">
                                <i class="bi bi-x-circle"></i> Reprovar
                            </button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

{{-- ── Filtros ─────────────────────────────────────────────────── --}}
<div class="card mb-3">
    <div class="card-body" style="padding: 12px 18px;">
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select" style="max-width: 220px;">
                <option value="">Todos os Status</option>
                @foreach(['ABERTA', 'RESPONDIDA', 'ENCERRADA'] as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-search"></i> Filtrar
            </button>
            @if(request('status'))
            <a href="{{ route('diligencias.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </form>
    </div>
</div>

{{-- ── Tabela de Diligências ───────────────────────────────────── --}}
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Projeto</th>
                    <th>Meta Vinculada</th>
                    <th>Tipo</th>
                    <th>Descrição</th>
                    <th>Responsável</th>
                    <th>Prazo</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($diligences as $d)
                @php $dc = ['ABERTA' => 'danger', 'RESPONDIDA' => 'warning', 'ENCERRADA' => 'success']; @endphp
                <tr>
                    <td style="font-size: 13px;">{{ Str::limit($d->project?->nome ?? '—', 35) }}</td>
                    <td style="font-size: 12px; color: var(--text-muted);">
                        @if($d->goal)
                            <i class="bi bi-bullseye me-1"></i>{{ Str::limit($d->goal->titulo, 30) }}
                        @else
                            —
                        @endif
                    </td>
                    <td style="font-size: 13px; font-weight: 500;">{{ $d->tipo ?? '—' }}</td>
                    <td style="font-size: 13px; color: var(--text-muted);">{{ Str::limit($d->descricao, 55) }}</td>
                    <td style="font-size: 13px;">{{ $d->responsavel ?? '—' }}</td>
                    <td style="font-size: 13px;">{{ $d->prazo?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $dc[$d->status] ?? 'secondary' }}">{{ $d->status_label }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('diligencias.show', ['diligencia' => $d]) }}" class="btn btn-sm btn-outline-secondary" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('diligencias.edit', ['diligencia' => $d]) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 48px 16px; color: var(--text-muted);">
                        <i class="bi bi-clipboard-check" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                        Nenhuma diligência encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($diligences->hasPages())
    <div class="card-footer">
        {{ $diligences->links() }}
    </div>
    @endif
</div>

@endsection

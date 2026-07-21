@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Projetos</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Acompanhe e gerencie os projetos</p>
    </div>
    <a href="{{ route('projetos.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Novo Projeto
    </a>
</div>

<div class="card mb-3">
    <div class="card-body" style="padding: 12px 18px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-4">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Buscar por nome ou código...">
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos os Status</option>
                    @foreach($statuses as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isAdmin())
            <div class="col-md-3">
                <select name="institution_id" class="form-select">
                    <option value="">Todas as Instituições</option>
                    @foreach($institutions as $i)
                    <option value="{{ $i->id }}" {{ request('institution_id') == $i->id ? 'selected' : '' }}>
                        {{ $i->nome_fantasia ?? $i->razao_social }}
                    </option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Nome do Projeto</th>
                    <th>Código</th>
                    <th>Instituição</th>
                    <th>Status</th>
                    <th>Valor Total</th>
                    <th>% Execução</th>
                    <th>Prazo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($projects as $p)
                @php
                    $colors = [
                        'EM_EXECUCAO' => 'success',
                        'APROVADO' => 'primary',
                        'FINALIZADO' => 'secondary',
                        'SUSPENSO' => 'danger',
                        'EM_ANALISE' => 'warning',
                        'RASCUNHO' => 'secondary',
                        'PRESTACAO_CONTAS' => 'info',
                        'PRESTACAO_APROVADA' => 'success',
                        'PRESTACAO_REPROVADA' => 'danger',
                        'CONCLUIDO' => 'success',
                        'CANCELADO' => 'danger',
                    ];
                    $execucao = $p->valor_total > 0 ? min(100, round(($p->valor_executado / $p->valor_total) * 100)) : 0;
                @endphp
                <tr>
                    <td>
                        <div style="font-weight: 600; color: var(--text);">{{ Str::limit($p->nome, 50) }}</div>
                    </td>
                    <td>
                        <span style="font-size: 12px; color: var(--text-muted); font-family: monospace;">{{ $p->codigo ?? '—' }}</span>
                    </td>
                    <td style="font-size: 13px;">{{ $p->institution?->nome_fantasia ?? $p->institution?->razao_social ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $colors[$p->status] ?? 'secondary' }}">{{ $p->status_label }}</span>
                    </td>
                    <td style="font-weight: 600; font-size: 13px;">R$ {{ number_format($p->valor_total, 2, ',', '.') }}</td>
                    <td style="min-width: 100px;">
                        <div class="progress mb-1" style="height: 5px;">
                            <div class="progress-bar bg-success" style="width: {{ $execucao }}%;"></div>
                        </div>
                        <span style="font-size: 11px; color: var(--text-muted);">{{ $execucao }}%</span>
                    </td>
                    <td style="font-size: 13px;">{{ $p->data_fim?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('projetos.show', ['projeto' => $p]) }}" class="btn btn-sm btn-outline-secondary" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('projetos.edit', ['projeto' => $p]) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if(auth()->user()->isAdmin())
                            <form method="POST" action="{{ route('projetos.destroy', ['projeto' => $p]) }}" onsubmit="return confirm('Excluir este projeto permanentemente?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 48px 16px; color: var(--text-muted);">
                        <i class="bi bi-folder2-open" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                        Nenhum projeto encontrado.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($projects->hasPages())
    <div class="card-footer">
        {{ $projects->links() }}
    </div>
    @endif
</div>

@endsection

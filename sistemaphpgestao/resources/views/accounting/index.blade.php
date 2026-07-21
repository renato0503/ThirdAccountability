@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Prestação de Contas</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Acompanhe e gerencie as prestações de contas</p>
    </div>
    <a href="{{ route('prestacao-contas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Nova Prestação
    </a>
</div>

<div class="card mb-3">
    <div class="card-body" style="padding: 12px 18px;">
        <form method="GET" class="d-flex gap-2">
            <select name="status" class="form-select" style="max-width: 240px;">
                <option value="">Todos os Status</option>
                @foreach($statuses as $s)
                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button class="btn btn-outline-secondary">
                <i class="bi bi-search"></i> Filtrar
            </button>
            @if(request('status'))
            <a href="{{ route('prestacao-contas.index') }}" class="btn btn-outline-secondary">Limpar</a>
            @endif
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Projeto</th>
                    <th>Instituição</th>
                    <th>Status</th>
                    <th>Data Envio</th>
                    <th>Data Aprovação</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reports as $r)
                @php $sc = ['RASCUNHO' => 'secondary', 'ENVIADA' => 'info', 'EM_ANALISE' => 'warning', 'APROVADA' => 'success', 'REPROVADA' => 'danger']; @endphp
                <tr>
                    <td style="color: var(--text-muted); font-size: 13px;">{{ $r->id }}</td>
                    <td style="font-size: 13px;">{{ Str::limit($r->project?->nome ?? '—', 45) }}</td>
                    <td style="font-size: 13px;">{{ $r->project?->institution?->nome_fantasia ?? $r->project?->institution?->razao_social ?? '—' }}</td>
                    <td>
                        <span class="badge bg-{{ $sc[$r->status] ?? 'secondary' }}">{{ $r->status_label }}</span>
                    </td>
                    <td style="font-size: 13px;">{{ $r->data_envio?->format('d/m/Y') ?? '—' }}</td>
                    <td style="font-size: 13px;">{{ $r->data_aprovacao?->format('d/m/Y') ?? '—' }}</td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('prestacao-contas.show', ['prestacao_conta' => $r]) }}" class="btn btn-sm btn-outline-secondary" title="Visualizar">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="{{ route('prestacao-contas.edit', ['prestacao_conta' => $r]) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 48px 16px; color: var(--text-muted);">
                        <i class="bi bi-file-earmark-ruled" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                        Nenhuma prestação de contas encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reports->hasPages())
    <div class="card-footer">
        {{ $reports->links() }}
    </div>
    @endif
</div>

@endsection

@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Controle Financeiro</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">Gerencie despesas e pagamentos</p>
    </div>
    <a href="{{ route('despesas.create') }}" class="btn btn-primary">
        <i class="bi bi-plus"></i> Nova Despesa
    </a>
</div>

{{-- Cards de resumo --}}
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-label">Valor Total Aprovado</div>
            <div class="stat-value" style="font-size: 22px;">R$ {{ number_format($totalAprovado, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Recebido</div>
            <div class="stat-value" style="font-size: 22px; color: #16a34a;">R$ {{ number_format($totalRecebido, 2, ',', '.') }}</div>
        </div>
    </div>
    <div class="col-sm-6 col-md-4">
        <div class="stat-card">
            <div class="stat-label">Total Executado</div>
            <div class="stat-value" style="font-size: 22px; color: #d97706;">R$ {{ number_format($totalExecutado, 2, ',', '.') }}</div>
        </div>
    </div>
</div>

{{-- Filtros --}}
<div class="card mb-3">
    <div class="card-body" style="padding: 12px 18px;">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-5">
                <select name="project_id" class="form-select">
                    <option value="">Todos os Projetos</option>
                    @foreach($projects as $p)
                    <option value="{{ $p->id }}" {{ request('project_id') == $p->id ? 'selected' : '' }}>
                        {{ Str::limit($p->nome, 50) }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Todos os Status</option>
                    @foreach(['PENDENTE', 'APROVADO', 'PAGO', 'REJEITADO'] as $s)
                    <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>{{ $s }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button class="btn btn-outline-secondary w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
            @if(request()->hasAny(['project_id', 'status']))
            <div class="col-md-2">
                <a href="{{ route('financeiro.index') }}" class="btn btn-outline-secondary w-100">Limpar</a>
            </div>
            @endif
        </form>
    </div>
</div>

{{-- Tabela --}}
<div class="card">
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Projeto</th>
                    <th>Fornecedor</th>
                    <th>Data</th>
                    <th>Valor</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $e)
                @php $sc = ['PAGO' => 'success', 'APROVADO' => 'primary', 'REJEITADO' => 'danger', 'PENDENTE' => 'warning']; @endphp
                <tr>
                    <td style="font-size: 13px;">{{ Str::limit($e->descricao, 40) }}</td>
                    <td style="font-size: 13px;">{{ Str::limit($e->project?->nome ?? '—', 35) }}</td>
                    <td style="font-size: 13px;">{{ $e->fornecedor ?? '—' }}</td>
                    <td style="font-size: 13px;">{{ $e->data_despesa?->format('d/m/Y') ?? '—' }}</td>
                    <td style="font-size: 13px; font-weight: 600;">R$ {{ number_format($e->valor, 2, ',', '.') }}</td>
                    <td>
                        <span class="badge bg-{{ $sc[$e->status] ?? 'secondary' }}">{{ $e->status_label }}</span>
                    </td>
                    <td>
                        <div class="d-flex gap-1">
                            <a href="{{ route('despesas.edit', ['despesa' => $e]) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            @if($e->status === 'PENDENTE')
                            <form method="POST" action="{{ route('despesas.status', ['expense' => $e]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="APROVADO">
                                <button class="btn btn-sm btn-outline-success" title="Aprovar">
                                    <i class="bi bi-check-lg"></i>
                                </button>
                            </form>
                            @elseif($e->status === 'APROVADO')
                            <form method="POST" action="{{ route('despesas.status', ['expense' => $e]) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="PAGO">
                                <button class="btn btn-sm btn-outline-secondary" title="Marcar como Pago" style="color: #0369a1; border-color: #7dd3fc;">
                                    <i class="bi bi-currency-dollar"></i>
                                </button>
                            </form>
                            @endif
                            <form method="POST" action="{{ route('despesas.destroy', ['despesa' => $e]) }}" onsubmit="return confirm('Excluir esta despesa?')">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger" title="Excluir">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 48px 16px; color: var(--text-muted);">
                        <i class="bi bi-receipt" style="font-size: 28px; display: block; margin-bottom: 8px; opacity: .4;"></i>
                        Nenhuma despesa encontrada.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($expenses->hasPages())
    <div class="card-footer">
        {{ $expenses->links() }}
    </div>
    @endif
</div>

@endsection

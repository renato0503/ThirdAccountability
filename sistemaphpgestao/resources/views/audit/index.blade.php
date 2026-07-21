@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-shield-check me-2 text-primary"></i>Log de Auditoria</h4>
</div>
<div class="card mb-3"><div class="card-body py-2"><form method="GET" class="row g-2 align-items-center">
    <div class="col-auto"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Buscar ação ou entidade..." style="min-width:220px"></div>
    <div class="col-auto"><button class="btn btn-outline-primary"><i class="bi bi-search"></i> Buscar</button></div>
    @if(request('q'))<div class="col-auto"><a href="{{ route('auditoria.index') }}" class="btn btn-outline-secondary">Limpar</a></div>@endif
</form></div></div>
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th style="width:160px">Data/Hora</th>
                    <th>Usuário</th>
                    <th>Ação</th>
                    <th>Entidade</th>
                    <th>ID</th>
                    <th>IP</th>
                    <th>Detalhes</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-size:.8rem;white-space:nowrap">{{ $log->created_at->format('d/m/Y H:i:s') }}</td>
                    <td style="font-size:.875rem">{{ $log->user?->name ?? '<em class="text-muted">Sistema</em>' }}</td>
                    <td>
                        @php
                            $ac = $log->acao;
                            $badge = match(true) {
                                str_contains($ac,'CRIAR') => 'success',
                                str_contains($ac,'ATUALIZAR') || str_contains($ac,'APROVAR') || str_contains($ac,'PAGAR') => 'primary',
                                str_contains($ac,'EXCLUIR') || str_contains($ac,'DESATIVAR') => 'danger',
                                str_contains($ac,'LOGIN') => 'info',
                                default => 'secondary'
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }}" style="font-size:.75rem">{{ $log->acao }}</span>
                    </td>
                    <td style="font-size:.875rem">{{ $log->entidade }}</td>
                    <td style="font-size:.875rem">{{ $log->entidade_id ?? '—' }}</td>
                    <td style="font-size:.8rem;color:#64748b">{{ $log->ip ?? '—' }}</td>
                    <td>
                        @if($log->dados)
                        <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#log-{{ $log->id }}">
                            <i class="bi bi-chevron-down"></i>
                        </button>
                        @endif
                    </td>
                </tr>
                @if($log->dados)
                <tr class="collapse" id="log-{{ $log->id }}">
                    <td colspan="7" class="bg-light">
                        <pre class="mb-0" style="font-size:.78rem;max-height:200px;overflow:auto">{{ json_encode(json_decode($log->dados), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                    </td>
                </tr>
                @endif
                @empty
                <tr><td colspan="7" class="text-center text-muted py-5">Nenhum registro encontrado.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())<div class="card-footer">{{ $logs->links() }}</div>@endif
</div>
@endsection

@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Relatórios</h4>
</div>

<div class="row g-4">
    {{-- Resumo Financeiro --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-cash-stack me-2 text-success"></i>Resumo Financeiro</div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted mb-1" style="font-size:.8rem">Total Aprovado</div>
                            <div class="fw-bold fs-5 text-primary">R$ {{ number_format($stats['total_aprovado'],2,',','.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted mb-1" style="font-size:.8rem">Total Recebido</div>
                            <div class="fw-bold fs-5 text-success">R$ {{ number_format($stats['total_recebido'],2,',','.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted mb-1" style="font-size:.8rem">Total Executado</div>
                            <div class="fw-bold fs-5 text-warning">R$ {{ number_format($stats['total_executado'],2,',','.') }}</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="border rounded p-3 text-center">
                            <div class="text-muted mb-1" style="font-size:.8rem">Saldo Disponível</div>
                            <div class="fw-bold fs-5 text-{{ $stats['saldo'] >= 0 ? 'success' : 'danger' }}">R$ {{ number_format($stats['saldo'],2,',','.') }}</div>
                        </div>
                    </div>
                </div>

                {{-- Projetos por Status --}}
                <h6 class="fw-semibold mb-3">Projetos por Status</h6>
                <div class="row g-2 mb-4">
                    @foreach($stats['por_status'] as $status => $qtd)
                    @php
                        $colors = ['EM_ELABORACAO'=>'secondary','EM_EXECUCAO'=>'primary','CONCLUIDO'=>'success','SUSPENSO'=>'warning','CANCELADO'=>'danger'];
                        $color = $colors[$status] ?? 'secondary';
                    @endphp
                    <div class="col-auto">
                        <div class="border rounded px-3 py-2 d-flex align-items-center gap-2">
                            <span class="badge bg-{{ $color }}">{{ $qtd }}</span>
                            <span style="font-size:.85rem">{{ str_replace('_',' ',$status) }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Exportar CSV --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-download me-2 text-primary"></i>Exportar Dados</div>
            <div class="card-body">
                <p class="text-muted mb-4" style="font-size:.9rem">Exporte os dados do sistema para planilha (formato CSV), compatível com Excel e Google Sheets.</p>
                <div class="d-grid gap-2">
                    <a href="{{ route('relatorios.export',['tipo'=>'projetos']) }}" class="btn btn-outline-primary" data-turbo="false">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar Projetos (CSV)
                    </a>
                    <a href="{{ route('relatorios.export',['tipo'=>'financeiro']) }}" class="btn btn-outline-success" data-turbo="false">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar Despesas (CSV)
                    </a>
                    <a href="{{ route('relatorios.export',['tipo'=>'diligencias']) }}" class="btn btn-outline-warning" data-turbo="false">
                        <i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar Diligências (CSV)
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Despesas por Categoria --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header fw-semibold"><i class="bi bi-pie-chart me-2 text-warning"></i>Despesas por Categoria</div>
            <div class="card-body">
                @forelse($stats['por_categoria'] as $cat)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span style="font-size:.875rem">{{ $cat->categoria ?? 'Sem categoria' }}</span>
                    <div class="d-flex align-items-center gap-2">
                        <div class="progress" style="width:120px;height:8px">
                            <div class="progress-bar bg-warning" style="width:{{ $stats['total_executado'] > 0 ? round(($cat->total/$stats['total_executado'])*100) : 0 }}%"></div>
                        </div>
                        <span class="fw-semibold" style="font-size:.85rem;min-width:90px;text-align:right">R$ {{ number_format($cat->total,2,',','.') }}</span>
                    </div>
                </div>
                @empty
                <p class="text-muted text-center py-3 mb-0">Nenhuma despesa registrada.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Projetos recentes --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header fw-semibold"><i class="bi bi-folder2-open me-2 text-primary"></i>Todos os Projetos</div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead><tr><th>Projeto</th><th>Instituição</th><th>Status</th><th>Valor Total</th><th>Executado</th><th>% Exec.</th></tr></thead>
                    <tbody>
                        @forelse($projects as $p)
                        <tr>
                            <td class="fw-semibold" style="font-size:.875rem">
                                <a href="{{ route('projetos.show',['projeto'=>$p]) }}" class="text-decoration-none">{{ $p->nome }}</a>
                            </td>
                            <td style="font-size:.875rem">{{ $p->institution?->nome_fantasia??$p->institution?->razao_social??'—' }}</td>
                            <td><span class="badge bg-{{ $p->status_color }}" style="font-size:.75rem">{{ $p->status_label }}</span></td>
                            <td style="font-size:.875rem">R$ {{ number_format($p->valor_total??0,2,',','.') }}</td>
                            <td style="font-size:.875rem">R$ {{ number_format($p->valor_executado??0,2,',','.') }}</td>
                            <td>
                                @php $pct = $p->valor_total > 0 ? round(($p->valor_executado/$p->valor_total)*100) : 0; @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:6px"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                                    <small>{{ $pct }}%</small>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="6" class="text-center text-muted py-4">Nenhum projeto encontrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

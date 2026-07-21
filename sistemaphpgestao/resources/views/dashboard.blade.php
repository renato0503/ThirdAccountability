@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <div>
        <h1 style="font-size: 20px; font-weight: 700; margin: 0;">Painel Principal</h1>
        <p style="font-size: 13px; color: var(--text-muted); margin: 2px 0 0;">{{ now()->format('d \d\e F \d\e Y') }}</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Instituições</div>
                    <div class="stat-value">{{ $stats['total_instituicoes'] }}</div>
                    <div class="stat-sub">ativas no sistema</div>
                </div>
                <div class="stat-icon"><i class="bi bi-building"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Projetos</div>
                    <div class="stat-value">{{ $stats['total_projetos'] }}</div>
                    <div class="stat-sub">{{ $stats['projetos_execucao'] }} em execução</div>
                </div>
                <div class="stat-icon"><i class="bi bi-folder2-open"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Pendências</div>
                    <div class="stat-value">{{ $stats['pendencias'] }}</div>
                    <div class="stat-sub">diligências em aberto</div>
                </div>
                <div class="stat-icon"><i class="bi bi-clipboard-check"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="stat-card">
            <div class="d-flex align-items-start justify-content-between">
                <div>
                    <div class="stat-label">Saldo</div>
                    <div class="stat-value" style="font-size: 20px;">R$ {{ number_format($stats['saldo'],0,',','.') }}</div>
                    <div class="stat-sub">recebido − executado</div>
                </div>
                <div class="stat-icon"><i class="bi bi-wallet2"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card"><div class="card-body" style="padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px;">Total Aprovado</div>
            <div style="font-size: 22px; font-weight: 700;">R$ {{ number_format($stats['total_aprovado'],2,',','.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body" style="padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px;">Total Recebido</div>
            <div style="font-size: 22px; font-weight: 700;">R$ {{ number_format($stats['total_recebido'],2,',','.') }}</div>
        </div></div>
    </div>
    <div class="col-md-4">
        <div class="card"><div class="card-body" style="padding: 16px 18px;">
            <div style="font-size: 11px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; margin-bottom: 4px;">Total Executado</div>
            <div style="font-size: 22px; font-weight: 700;">R$ {{ number_format($stats['total_executado'],2,',','.') }}</div>
        </div></div>
    </div>
</div>

{{-- ─────────── FILA DE METAS POR PRAZO ─────────── --}}
<div class="card mb-4">
    <div class="card-header d-flex align-items-center justify-content-between" style="background: var(--primary); color:#fff;">
        <span>
            <i class="bi bi-flag-fill me-1"></i>
            Fila de Metas por Prazo
        </span>
        <span class="badge bg-warning text-dark">
            {{ count($goalsQueue) }} {{ count($goalsQueue) === 1 ? 'meta' : 'metas' }}
        </span>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th style="width: 28%;">Meta</th>
                    <th>Projeto</th>
                    <th>Entidade</th>
                    <th style="width: 110px;">Prazo</th>
                    <th style="width: 150px;" class="text-center">Dias Restantes</th>
                    <th style="width: 130px;">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($goalsQueue as $g)
                    @php
                        $dias    = $g['dias_restantes'];
                        $rowBg   = '';
                        $diasBg  = 'bg-secondary';
                        $diasTxt = '—';
                        if ($dias !== null) {
                            if ($dias < 0) {
                                $rowBg   = 'background: #fee2e2;';
                                $diasBg  = 'bg-danger';
                                $diasTxt = abs($dias).' dia(s) atrasada';
                            } elseif ($dias < 7) {
                                $rowBg   = 'background: #fef3c7;';
                                $diasBg  = 'bg-danger';
                                $diasTxt = $dias.' dia(s)';
                            } elseif ($dias < 30) {
                                $diasBg  = 'bg-warning text-dark';
                                $diasTxt = $dias.' dia(s)';
                            } else {
                                $diasBg  = 'bg-success';
                                $diasTxt = $dias.' dia(s)';
                            }
                        }
                    @endphp
                    <tr style="{{ $rowBg }}">
                        <td>
                            <a href="{{ route('projetos.show', ['projeto' => $g['project_id'], 'tab' => 'metas']) }}"
                               class="fw-semibold text-decoration-none">
                                @if($g['numero'])<span class="text-muted">#{{ $g['numero'] }}</span>@endif
                                {{ $g['titulo'] }}
                            </a>
                        </td>
                        <td>
                            <a href="{{ route('projetos.show', $g['project_id']) }}" class="text-decoration-none">
                                {{ $g['project_nome'] }}
                            </a>
                            @if($g['project_codigo'])
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $g['project_codigo'] }}</div>
                            @endif
                        </td>
                        <td>{{ $g['institution_name'] }}</td>
                        <td>{{ $g['prazo'] ?? '—' }}</td>
                        <td class="text-center">
                            <span class="badge {{ $diasBg }}">
                                @if($dias !== null && $dias < 7)
                                    <i class="bi bi-exclamation-triangle-fill"></i>
                                @endif
                                {{ $diasTxt }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-{{ $g['status_color'] ?? 'secondary' }}">{{ $g['status_label'] }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center" style="padding: 32px; color: var(--text-muted);">
                            Nenhuma meta com prazo pendente.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="row g-3">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Projetos Recentes</span>
                <a href="{{ route('projetos.index') }}" class="btn btn-outline-secondary btn-sm">Ver todos</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead><tr><th>Nome</th><th>Instituição</th><th>Status</th><th>Execução</th></tr></thead>
                    <tbody>
                        @forelse($recentProjects as $p)
                        <tr>
                            <td>
                                <a href="{{ route('projetos.show', $p['id']) }}" class="fw-semibold text-decoration-none">{{ $p['nome'] }}</a>
                                <div style="font-size: 11px; color: var(--text-muted);">{{ $p['codigo'] }}</div>
                            </td>
                            <td>{{ $p['institution_name'] }}</td>
                            <td><span class="badge bg-{{ $p['status_color'] }}">{{ $p['status_label'] }}</span></td>
                            <td style="min-width: 110px;">
                                @php $pct = ($p['valor_total'] ?? 0) > 0 ? round(($p['valor_executado']/$p['valor_total'])*100) : 0 @endphp
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 5px;"><div class="progress-bar" style="width:{{ $pct }}%"></div></div>
                                    <span style="font-size: 12px; color: var(--text-muted); min-width: 28px;">{{ $pct }}%</span>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center" style="padding: 32px; color: var(--text-muted);">Nenhum projeto cadastrado.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header">Status dos Projetos</div>
            <div class="card-body">
                @php
                    $statusColors = ['EM_ELABORACAO'=>'secondary','EM_EXECUCAO'=>'primary','CONCLUIDO'=>'success','SUSPENSO'=>'warning','CANCELADO'=>'danger'];
                    $statusLabels = ['EM_ELABORACAO'=>'Em Elaboração','EM_EXECUCAO'=>'Em Execução','CONCLUIDO'=>'Concluído','SUSPENSO'=>'Suspenso','CANCELADO'=>'Cancelado'];
                    $totalP = $stats['por_status']->sum();
                @endphp
                @foreach($stats['por_status'] as $status => $qtd)
                @php $pct = $totalP > 0 ? round(($qtd/$totalP)*100) : 0; @endphp
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span style="font-size: 13px;">{{ $statusLabels[$status] ?? $status }}</span>
                        <span class="badge bg-{{ $statusColors[$status] ?? 'secondary' }}">{{ $qtd }}</span>
                    </div>
                    <div class="progress" style="height: 5px;"><div class="progress-bar bg-{{ $statusColors[$status] ?? 'secondary' }}" style="width:{{ $pct }}%"></div></div>
                </div>
                @endforeach
                @if($stats['por_status']->isEmpty())
                <p class="text-center mb-0" style="color: var(--text-muted); font-size: 13px; padding: 20px 0;">Sem dados.</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

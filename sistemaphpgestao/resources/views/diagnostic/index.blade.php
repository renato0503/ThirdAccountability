@extends('layouts.app')
@section('content')

<div class="d-flex align-items-center justify-content-between mb-4">
    <h1 style="font-size: 20px; font-weight: 700; margin: 0;">
        <i class="bi bi-clipboard2-pulse me-1"></i> Diagnóstico do Sistema
    </h1>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Voltar
    </a>
</div>

@if(session('diag_output'))
<div class="card mb-3">
    <div class="card-header bg-light"><i class="bi bi-terminal me-1"></i> Resultado</div>
    <pre class="m-0 p-3" style="white-space:pre-wrap; font-size:12px;">{{ session('diag_output') }}</pre>
</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="row g-3">

    {{-- Banco --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-database me-1"></i> Conexão de Banco</div>
            <div class="card-body">
                @if($dbOk)
                    <span class="badge bg-success">OK</span>
                    <div class="small text-muted mt-2">Versão: {{ $dbVersion }}</div>
                @else
                    <span class="badge bg-danger">FALHA</span>
                    <pre class="mt-2 small">{{ $dbErr }}</pre>
                @endif
            </div>
        </div>
    </div>

    {{-- Symlink storage --}}
    <div class="col-md-6">
        <div class="card h-100">
            <div class="card-header"><i class="bi bi-link-45deg me-1"></i> Symlink public/storage</div>
            <div class="card-body">
                @if($symlinkOk)
                    <span class="badge bg-success">Presente</span>
                @else
                    <span class="badge bg-danger">Ausente</span>
                @endif
                <div class="small text-muted mt-2" style="word-break:break-all;">{{ $publicStorage }}</div>
                @if(!$symlinkOk)
                <form method="POST" action="{{ route('diagnostico.storage-link') }}" class="mt-2">
                    @csrf
                    <button class="btn btn-sm btn-primary"><i class="bi bi-link"></i> Criar symlink agora</button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Colunas faltando --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-columns-gap me-1"></i> Colunas em <code>institutions</code>
                <span class="badge bg-secondary ms-1">{{ count($presentCols) }} colunas presentes</span>
                @if(count($missingCols))
                    <span class="badge bg-danger ms-1">{{ count($missingCols) }} faltando</span>
                @endif
            </div>
            <div class="card-body">
                @if(count($missingCols))
                    <div class="alert alert-warning small">
                        <strong>Colunas faltando</strong> (provavelmente migrations pendentes):<br>
                        <code>{{ implode(', ', $missingCols) }}</code>
                    </div>
                @else
                    <div class="text-success small"><i class="bi bi-check-circle"></i> Todas as colunas esperadas estão presentes.</div>
                @endif
                @if(count($extraCols))
                    <div class="small text-muted mt-2">
                        Colunas extras (não documentadas): <code>{{ implode(', ', $extraCols) }}</code>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Migrations --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <i class="bi bi-arrow-up-circle me-1"></i> Migrations
                <span class="badge bg-secondary ms-1">{{ $migrationsRun }} executadas</span>
                @if(count($migrationsPending))
                    <span class="badge bg-warning text-dark ms-1">{{ count($migrationsPending) }} pendentes</span>
                @endif
            </div>
            <div class="card-body">
                @if(count($migrationsPending))
                    <div class="alert alert-warning small">
                        <strong>Pendentes:</strong>
                        <ul class="mb-2">
                            @foreach($migrationsPending as $m)
                                <li><code>{{ $m }}</code></li>
                            @endforeach
                        </ul>
                    </div>
                    <form method="POST" action="{{ route('diagnostico.migrate') }}"
                          onsubmit="return confirm('Rodar php artisan migrate --force agora? Faça backup antes!')">
                        @csrf
                        <button class="btn btn-sm btn-warning">
                            <i class="bi bi-arrow-up-square"></i> Rodar migrate --force agora
                        </button>
                    </form>
                @else
                    <div class="text-success small"><i class="bi bi-check-circle"></i> Nenhuma migration pendente.</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Tabelas relacionadas --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-table me-1"></i> Tabelas relacionadas</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr><th>Tabela</th><th>Existe?</th><th>Registros</th></tr>
                    </thead>
                    <tbody>
                        @foreach($relatedTables as $name => $info)
                        <tr>
                            <td><code>{{ $name }}</code></td>
                            <td>
                                @if($info['exists'])
                                    <span class="badge bg-success">sim</span>
                                @else
                                    <span class="badge bg-danger">não</span>
                                @endif
                            </td>
                            <td>{{ $info['count'] ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Permissões --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-shield-lock me-1"></i> Permissões de pastas</div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Pasta</th><th>Existe</th><th>Gravável</th></tr></thead>
                    <tbody>
                        @foreach($perms as $p)
                        <tr>
                            <td style="word-break:break-all;"><code>{{ $p['path'] }}</code></td>
                            <td>
                                @if($p['exists'])<span class="badge bg-success">sim</span>
                                @else <span class="badge bg-danger">não</span> @endif
                            </td>
                            <td>
                                @if($p['writable'])<span class="badge bg-success">sim</span>
                                @else <span class="badge bg-danger">não</span> @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Ações de cache --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-arrow-clockwise me-1"></i> Limpar cache do framework</div>
            <div class="card-body">
                <form method="POST" action="{{ route('diagnostico.clear-caches') }}">
                    @csrf
                    <button class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-eraser"></i> Limpar config / route / view / cache
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Últimas linhas do log --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><i class="bi bi-file-text me-1"></i> Últimas linhas do <code>storage/logs/laravel.log</code></div>
            <pre class="m-0 p-3" style="white-space:pre-wrap; font-size:11px; max-height:480px; overflow:auto;">{{ $logTail ?: '(log vazio ou inacessível)' }}</pre>
        </div>
    </div>

</div>

@endsection

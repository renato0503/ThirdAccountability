<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: DejaVu Sans, Arial, sans-serif; font-size: 11px; color: #09090b; }
    .header { padding: 20px 24px 16px; border-bottom: 2px solid #18181b; margin-bottom: 20px; }
    .header h1 { font-size: 18px; font-weight: 700; }
    .header p { font-size: 10px; color: #71717a; margin-top: 2px; }
    .header .meta { float: right; text-align: right; font-size: 10px; color: #71717a; }
    .stats { display: table; width: 100%; margin-bottom: 20px; border-collapse: collapse; }
    .stat { display: table-cell; width: 25%; text-align: center; padding: 12px 8px; border: 1px solid #e4e4e7; }
    .stat-label { font-size: 9px; color: #71717a; text-transform: uppercase; letter-spacing: .05em; }
    .stat-value { font-size: 15px; font-weight: 700; margin-top: 3px; }
    table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
    th { font-size: 9px; font-weight: 700; color: #71717a; text-transform: uppercase; letter-spacing: .04em; padding: 7px 10px; background: #f4f4f5; border-bottom: 1px solid #e4e4e7; text-align: left; }
    td { font-size: 10px; padding: 7px 10px; border-bottom: 1px solid #f4f4f5; vertical-align: middle; }
    tr:last-child td { border-bottom: none; }
    .badge { display: inline-block; padding: 2px 7px; border-radius: 9999px; font-size: 9px; font-weight: 600; border: 1px solid #d4d4d8; background: #f4f4f5; }
    .section-title { font-size: 13px; font-weight: 700; margin-bottom: 8px; padding-bottom: 4px; border-bottom: 1px solid #e4e4e7; }
    .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 8px 24px; font-size: 9px; color: #a1a1aa; border-top: 1px solid #e4e4e7; text-align: center; }
</style>
</head>
<body>

<div class="header">
    <div class="meta">
        Gerado em {{ now()->format('d/m/Y H:i') }}<br>
        {{ config('app.name') }}
    </div>
    <h1>{{ $titulo }}</h1>
    <p>{{ $subtitulo ?? '' }}</p>
</div>

@if(isset($stats))
<div class="stats">
    <div class="stat">
        <div class="stat-label">Total Aprovado</div>
        <div class="stat-value">R$ {{ number_format($stats['total_aprovado'], 2, ',', '.') }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Total Recebido</div>
        <div class="stat-value">R$ {{ number_format($stats['total_recebido'], 2, ',', '.') }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Total Executado</div>
        <div class="stat-value">R$ {{ number_format($stats['total_executado'], 2, ',', '.') }}</div>
    </div>
    <div class="stat">
        <div class="stat-label">Saldo</div>
        <div class="stat-value">R$ {{ number_format($stats['saldo'], 2, ',', '.') }}</div>
    </div>
</div>
@endif

@if(isset($projetos) && count($projetos))
<div class="section-title">Projetos</div>
<table>
    <thead>
        <tr>
            <th>Nome</th>
            <th>Instituição</th>
            <th>Status</th>
            <th style="text-align:right">Valor Total</th>
            <th style="text-align:right">Executado</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projetos as $p)
        <tr>
            <td>{{ $p->nome }}</td>
            <td>{{ $p->institution?->nome_fantasia ?? $p->institution?->razao_social ?? '—' }}</td>
            <td><span class="badge">{{ $p->status_label }}</span></td>
            <td style="text-align:right">R$ {{ number_format($p->valor_total ?? 0, 2, ',', '.') }}</td>
            <td style="text-align:right">R$ {{ number_format($p->valor_executado ?? 0, 2, ',', '.') }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

@if(isset($despesas) && count($despesas))
<div class="section-title">Despesas</div>
<table>
    <thead>
        <tr>
            <th>Descrição</th>
            <th>Projeto</th>
            <th>Fornecedor</th>
            <th>Data</th>
            <th style="text-align:right">Valor</th>
            <th>Status</th>
        </tr>
    </thead>
    <tbody>
        @foreach($despesas as $e)
        <tr>
            <td>{{ $e->descricao }}</td>
            <td>{{ $e->project?->nome ?? '—' }}</td>
            <td>{{ $e->fornecedor ?? '—' }}</td>
            <td>{{ $e->data_despesa?->format('d/m/Y') ?? '—' }}</td>
            <td style="text-align:right">R$ {{ number_format($e->valor, 2, ',', '.') }}</td>
            <td><span class="badge">{{ $e->status }}</span></td>
        </tr>
        @endforeach
    </tbody>
</table>
@endif

<div class="footer">
    {{ config('app.name') }} &mdash; {{ config('app.url') }} &mdash; Página <span class="pagenum"></span>
</div>
</body>
</html>

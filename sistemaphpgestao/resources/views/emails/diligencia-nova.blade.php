<!DOCTYPE html>
<html lang="pt-BR">
<head><meta charset="utf-8"><style>
body { font-family: -apple-system, sans-serif; background: #f4f4f5; padding: 40px 20px; color: #09090b; }
.card { background: #fff; border-radius: 8px; padding: 32px; max-width: 520px; margin: 0 auto; border: 1px solid #e4e4e7; }
.logo { font-weight: 700; font-size: 18px; margin-bottom: 24px; }
h2 { font-size: 20px; margin: 0 0 8px; }
p { font-size: 14px; color: #52525b; line-height: 1.6; }
.info { background: #f4f4f5; border-radius: 6px; padding: 14px 18px; margin: 20px 0; }
.info p { margin: 4px 0; font-size: 13.5px; }
.badge { display: inline-block; background: #fef9c3; color: #a16207; border: 1px solid #fde047; border-radius: 9999px; padding: 2px 10px; font-size: 12px; font-weight: 600; }
.btn { display: inline-block; background: #18181b; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 500; margin-top: 20px; }
.footer { text-align: center; font-size: 12px; color: #a1a1aa; margin-top: 24px; }
</style></head>
<body>
<div class="card">
    <div class="logo">{{ config('app.name') }}</div>
    <h2>Nova Diligência Aberta</h2>
    <p>Uma diligência foi registrada e requer sua atenção:</p>
    <div class="info">
        <p><strong>Tipo:</strong> {{ $diligencia->tipo ?? 'Geral' }}</p>
        <p><strong>Projeto:</strong> {{ $diligencia->project?->nome ?? '—' }}</p>
        <p><strong>Responsável:</strong> {{ $diligencia->responsavel ?? '—' }}</p>
        <p><strong>Prazo:</strong> {{ $diligencia->prazo?->format('d/m/Y') ?? '—' }}</p>
        <p><strong>Status:</strong> <span class="badge">{{ $diligencia->status }}</span></p>
    </div>
    @if($diligencia->descricao)
    <p><strong>Descrição:</strong><br>{{ $diligencia->descricao }}</p>
    @endif
    <a href="{{ config('app.url') }}/diligencias/{{ $diligencia->id }}" class="btn">Ver Diligência</a>
</div>
<div class="footer">{{ config('app.name') }} &mdash; {{ config('app.url') }}</div>
</body>
</html>

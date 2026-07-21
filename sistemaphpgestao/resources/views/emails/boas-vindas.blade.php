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
.btn { display: inline-block; background: #18181b; color: #fff; text-decoration: none; padding: 10px 20px; border-radius: 6px; font-size: 14px; font-weight: 500; margin-top: 20px; }
.footer { text-align: center; font-size: 12px; color: #a1a1aa; margin-top: 24px; }
</style></head>
<body>
<div class="card">
    <div class="logo">{{ config('app.name') }}</div>
    <h2>Bem-vindo, {{ $user->name }}!</h2>
    <p>Sua conta foi criada com sucesso. Abaixo estão suas credenciais de acesso:</p>
    <div class="info">
        <p><strong>E-mail:</strong> {{ $user->email }}</p>
        <p><strong>Senha:</strong> {{ $senha }}</p>
    </div>
    <p>Por segurança, altere sua senha no primeiro acesso.</p>
    <a href="{{ config('app.url') }}" class="btn">Acessar o sistema</a>
</div>
<div class="footer">{{ config('app.name') }} &mdash; {{ config('app.url') }}</div>
</body>
</html>

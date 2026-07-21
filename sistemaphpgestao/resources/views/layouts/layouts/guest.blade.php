<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name','Gestão Terceiro Setor') }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; }
        :root {
            --border: #e4e4e7;
            --border-input: #d4d4d8;
            --text: #09090b;
            --text-muted: #71717a;
            --bg-muted: #fafafa;
            --radius: 6px;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            background: #f4f4f5;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            -webkit-font-smoothing: antialiased;
        }
        .auth-wrap { width: 100%; max-width: 400px; }
        .auth-logo {
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 24px;
        }
        .auth-logo img { height: 38px; width: auto; display: block; }
        .auth-card {
            background: #fff;
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) + 2px);
            padding: 28px;
        }
        .auth-title { font-size: 20px; font-weight: 700; color: var(--text); margin-bottom: 4px; }
        .auth-subtitle { font-size: 13px; color: var(--text-muted); margin-bottom: 24px; }
        .form-label { font-size: 13.5px; font-weight: 500; color: var(--text); margin-bottom: 5px; }
        .form-control {
            font-family: inherit;
            font-size: 14px;
            border: 1px solid var(--border-input);
            border-radius: var(--radius);
            padding: 9px 12px;
            transition: border-color .15s, box-shadow .15s;
        }
        .form-control:focus { border-color: #18181b; box-shadow: 0 0 0 2px rgba(24,24,27,.1); outline: none; }
        .form-control::placeholder { color: #a1a1aa; }
        .btn-primary {
            background: #18181b;
            border-color: #18181b;
            font-family: inherit;
            font-size: 14px;
            font-weight: 500;
            padding: 9px 16px;
            border-radius: var(--radius);
            transition: background .15s;
        }
        .btn-primary:hover { background: #27272a; border-color: #27272a; }
        .alert { border-radius: var(--radius); font-size: 13px; border: 1px solid; padding: 10px 14px; }
        .alert-danger  { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
        .alert-success { background: #f0fdf4; border-color: #86efac; color: #15803d; }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 12px; color: var(--text-muted); }
        a { color: #18181b; }
    </style>
</head>
<body>
    <div class="auth-wrap">
        <div class="text-center">
            <div class="auth-logo"><img src="{{ asset('img/logoatualizada.png') }}" alt="Gestão Terceiro"></div>
        </div>
        <div class="auth-card">
            {{ $slot }}
        </div>
        <div class="auth-footer">Gestão Terceiro Setor &copy; {{ date('Y') }}</div>
    </div>
    <script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
</body>
</html>

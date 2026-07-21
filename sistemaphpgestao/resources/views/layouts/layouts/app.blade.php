<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('css/bootstrap-icons.min.css') }}">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        :root {
            --sidebar-w: 218px;
            --bg: #ffffff;
            --bg-muted: #fafafa;
            --border: #e4e4e7;
            --border-input: #d4d4d8;
            --text: #09090b;
            --text-muted: #71717a;
            --text-sm: #52525b;
            --accent: #18181b;
            --accent-fg: #fafafa;
            --primary: #18181b;
            --primary-hover: #27272a;
            --destructive: #ef4444;
            --ring: #a1a1aa;
            --radius: 6px;
            --sidebar-bg: #fafafa;
            --sidebar-border: #e4e4e7;
            --nav-active-bg: #f4f4f5;
            --nav-active-text: #09090b;
            --nav-hover-bg: #f4f4f5;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', system-ui, sans-serif;
            font-size: 14px;
            background: var(--bg);
            color: var(--text);
            margin: 0;
            -webkit-font-smoothing: antialiased;
        }

        /* ── Sidebar ─────────────────────────────────────── */
        #sidebar {
            width: var(--sidebar-w);
            height: 100vh;
            background: var(--sidebar-bg);
            border-right: 1px solid var(--sidebar-border);
            position: fixed;
            top: 0; left: 0;
            z-index: 1000;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform .25s ease;
        }

        .sidebar-header {
            padding: 9px 14px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            min-height: 57px;
        }
        .sidebar-logo img { height: 42px; width: auto; max-width: 100%; display: block; object-fit: contain; }

        .nav-section-label {
            padding: 14px 10px 4px;
            font-size: 10.5px;
            font-weight: 600;
            color: var(--text-muted);
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 7px;
            padding: 7px 10px;
            margin: 1px 6px;
            border-radius: var(--radius);
            color: var(--text-sm);
            text-decoration: none;
            font-size: 13px;
            font-weight: 500;
            white-space: nowrap;
            overflow: hidden;
            transition: background .15s, color .15s;
            cursor: pointer;
        }
        .nav-item i { font-size: 14px; min-width: 15px; color: var(--text-muted); flex-shrink: 0; transition: color .15s; }
        .nav-item:hover { background: var(--nav-hover-bg); color: var(--text); }
        .nav-item:hover i { color: var(--text); }
        .nav-item.active { background: var(--nav-active-bg); color: var(--nav-active-text); font-weight: 600; }
        .nav-item.active i { color: var(--nav-active-text); }

        /* ── Main ─────────────────────────────────────────── */
        #main { margin-left: var(--sidebar-w); min-height: 100vh; display: flex; flex-direction: column; }

        #topbar {
            height: 57px;
            background: var(--bg);
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 24px;
            position: sticky; top: 0; z-index: 999;
        }

        .topbar-domain { font-size: 12.5px; color: var(--text-muted); font-weight: 500; letter-spacing: .01em; }

        .user-info { font-size: 13px; color: var(--text-sm); font-weight: 500; }
        .role-badge {
            font-size: 11px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 9999px;
            background: var(--bg-muted);
            border: 1px solid var(--border);
            color: var(--text-sm);
        }

        .page-content { padding: 24px; flex: 1; }

        /* ── Cards ─────────────────────────────────────────── */
        .card {
            background: var(--bg);
            border: 1px solid var(--border) !important;
            border-radius: calc(var(--radius) + 2px);
            box-shadow: none !important;
        }
        .card-header {
            background: transparent;
            border-bottom: 1px solid var(--border);
            padding: 14px 18px;
            font-weight: 600;
            font-size: 14px;
            color: var(--text);
        }
        .card-body { padding: 18px; }
        .card-footer { background: transparent; border-top: 1px solid var(--border); padding: 12px 18px; }

        /* ── Buttons ──────────────────────────────────────── */
        .btn {
            font-family: inherit;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: var(--radius);
            padding: 7px 14px;
            line-height: 1.4;
            transition: all .15s;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid transparent;
            cursor: pointer;
        }
        .btn-primary {
            background: var(--primary);
            color: #fff;
            border-color: var(--primary);
        }
        .btn-primary:hover { background: var(--primary-hover); border-color: var(--primary-hover); color: #fff; }
        .btn-secondary {
            background: var(--bg);
            color: var(--text);
            border-color: var(--border-input);
        }
        .btn-secondary:hover { background: var(--bg-muted); color: var(--text); }
        .btn-outline-secondary {
            background: transparent;
            color: var(--text-sm);
            border-color: var(--border-input);
        }
        .btn-outline-secondary:hover { background: var(--bg-muted); color: var(--text); border-color: var(--border-input); }
        .btn-outline-primary {
            background: transparent;
            color: var(--text);
            border-color: var(--border-input);
        }
        .btn-outline-primary:hover { background: var(--bg-muted); color: var(--text); border-color: var(--border-input); }
        .btn-outline-danger {
            background: transparent;
            color: var(--destructive);
            border-color: #fca5a5;
        }
        .btn-outline-danger:hover { background: #fef2f2; border-color: var(--destructive); color: var(--destructive); }
        .btn-danger { background: var(--destructive); color: #fff; border-color: var(--destructive); }
        .btn-danger:hover { background: #dc2626; border-color: #dc2626; color: #fff; }
        .btn-success { background: #16a34a; color: #fff; border-color: #16a34a; }
        .btn-success:hover { background: #15803d; border-color: #15803d; color: #fff; }
        .btn-outline-success { background: transparent; color: #16a34a; border-color: #86efac; }
        .btn-outline-success:hover { background: #f0fdf4; color: #15803d; }
        .btn-warning { background: #d97706; color: #fff; border-color: #d97706; }
        .btn-warning:hover { background: #b45309; color: #fff; }
        .btn-sm { padding: 5px 10px; font-size: 12.5px; }
        .btn-lg { padding: 10px 20px; font-size: 14.5px; }

        /* ── Forms ─────────────────────────────────────────── */
        .form-label { font-size: 13.5px; font-weight: 500; color: var(--text); margin-bottom: 5px; }
        .form-control, .form-select {
            font-family: inherit;
            font-size: 13.5px;
            border: 1px solid var(--border-input);
            border-radius: var(--radius);
            padding: 8px 12px;
            color: var(--text);
            background: var(--bg);
            transition: border-color .15s, box-shadow .15s;
            height: auto;
        }
        .form-control::placeholder { color: var(--ring); }
        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(24,24,27,.1);
            outline: none;
        }
        .form-control:disabled, .form-select:disabled { background: var(--bg-muted); color: var(--text-muted); }
        textarea.form-control { resize: vertical; }

        /* ── Tables ─────────────────────────────────────────── */
        .table { font-size: 13.5px; border-color: var(--border); }
        .table th {
            font-size: 12px;
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: .04em;
            padding: 10px 16px;
            background: var(--bg-muted);
            border-bottom: 1px solid var(--border);
        }
        .table td { padding: 12px 16px; vertical-align: middle; border-color: var(--border); color: var(--text-sm); }
        .table tbody tr:hover { background: #fafafa; }
        .table-responsive { border-radius: calc(var(--radius) + 2px); }

        /* ── Badges ─────────────────────────────────────────── */
        .badge {
            font-size: 11px;
            font-weight: 500;
            padding: 2px 8px;
            border-radius: 9999px;
            border: 1px solid transparent;
        }
        .bg-primary { background: #18181b !important; color: #fff !important; }
        .bg-success { background: #dcfce7 !important; color: #15803d !important; border-color: #86efac !important; }
        .bg-danger  { background: #fee2e2 !important; color: #dc2626 !important; border-color: #fca5a5 !important; }
        .bg-warning { background: #fef9c3 !important; color: #a16207 !important; border-color: #fde047 !important; }
        .bg-secondary { background: #f4f4f5 !important; color: #52525b !important; border-color: #d4d4d8 !important; }
        .bg-info    { background: #e0f2fe !important; color: #0369a1 !important; border-color: #7dd3fc !important; }
        .bg-light   { background: #f4f4f5 !important; color: #52525b !important; }

        /* ── Alerts ─────────────────────────────────────────── */
        .alert { border-radius: var(--radius); font-size: 13.5px; border: 1px solid; padding: 10px 14px; }
        .alert-success { background: #f0fdf4; border-color: #86efac; color: #15803d; }
        .alert-danger  { background: #fef2f2; border-color: #fca5a5; color: #dc2626; }
        .alert-warning { background: #fffbeb; border-color: #fde047; color: #a16207; }
        .alert-info    { background: #f0f9ff; border-color: #7dd3fc; color: #0369a1; }

        /* ── Pagination ─────────────────────────────────────── */
        .pagination { gap: 4px; }
        .page-link {
            border: 1px solid var(--border-input);
            border-radius: var(--radius) !important;
            color: var(--text-sm);
            font-size: 13px;
            padding: 6px 12px;
            background: var(--bg);
        }
        .page-link:hover { background: var(--bg-muted); color: var(--text); border-color: var(--border-input); }
        .page-item.active .page-link { background: var(--accent); border-color: var(--accent); color: #fff; }
        .page-item.disabled .page-link { background: var(--bg-muted); color: var(--ring); }

        /* ── Progress ────────────────────────────────────────── */
        .progress { background: #f4f4f5; border-radius: 9999px; height: 6px; }
        .progress-bar { background: var(--accent); border-radius: 9999px; }
        .progress-bar.bg-success { background: #16a34a !important; }
        .progress-bar.bg-warning { background: #d97706 !important; }
        .progress-bar.bg-danger  { background: var(--destructive) !important; }

        /* ── Misc ─────────────────────────────────────────────── */
        .text-muted { color: var(--text-muted) !important; }
        .fw-semibold { font-weight: 600; }
        a { color: var(--text); }
        a:hover { color: var(--text-muted); }
        .nav-tabs { border-color: var(--border); gap: 2px; }
        .nav-tabs .nav-link { font-size: 13.5px; color: var(--text-muted); border: none; border-bottom: 2px solid transparent; border-radius: 0; padding: 10px 16px; font-weight: 500; }
        .nav-tabs .nav-link:hover { color: var(--text); border-bottom-color: var(--border-input); }
        .nav-tabs .nav-link.active { color: var(--text); border-bottom-color: var(--accent); font-weight: 600; }
        .tab-content { padding-top: 1px; }
        pre { background: var(--bg-muted); border: 1px solid var(--border); border-radius: var(--radius); padding: 10px 14px; font-size: 12px; }
        .collapse { display: none; }
        .collapse.show { display: table-row; }

        /* ── Stat cards ──────────────────────────────────────── */
        .stat-card {
            background: var(--bg);
            border: 1px solid var(--border);
            border-radius: calc(var(--radius) + 2px);
            padding: 20px;
        }
        .stat-card .stat-label { font-size: 12px; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: .04em; }
        .stat-card .stat-value { font-size: 26px; font-weight: 700; color: var(--text); margin-top: 4px; line-height: 1.2; }
        .stat-card .stat-sub { font-size: 12px; color: var(--text-muted); margin-top: 4px; }
        .stat-card .stat-icon { width: 36px; height: 36px; border-radius: var(--radius); background: var(--bg-muted); border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; }
        .stat-card .stat-icon i { font-size: 17px; color: var(--text-sm); }

        /* ── Mobile ──────────────────────────────────────────── */
        @media (max-width: 991px) {
            #sidebar { transform: translateX(-100%); }
            #sidebar.open { transform: translateX(0); box-shadow: 0 0 0 100vw rgba(0,0,0,.3); }
            #main { margin-left: 0; }
        }
    </style>
    @stack('styles')
</head>
<body>

<div id="sidebar">
    <div class="sidebar-header">
        <div class="sidebar-logo">
            <img src="{{ asset('img/logoatualizada.png') }}" alt="Gestão Terceiro">
        </div>
    </div>

    <nav style="padding: 8px 0 24px; flex: 1;">
        <div class="nav-section-label">Geral</div>
        <a href="{{ route('dashboard') }}" class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-grid-1x2"></i> Painel Principal
        </a>

        @if(auth()->user()->isAdmin())
        <div class="nav-section-label">Cadastros</div>
        <a href="{{ route('instituicoes.index') }}" class="nav-item {{ request()->routeIs('instituicoes.*') ? 'active' : '' }}">
            <i class="bi bi-building"></i> Instituições
        </a>
        @endif

        <div class="nav-section-label">Projetos</div>
        <a href="{{ route('projetos.index') }}" class="nav-item {{ request()->routeIs('projetos.*') ? 'active' : '' }}">
            <i class="bi bi-folder2-open"></i> Projetos
        </a>

        <div class="nav-section-label">Financeiro</div>
        <a href="{{ route('financeiro.index') }}" class="nav-item {{ request()->routeIs('financeiro.*') || (request()->routeIs('despesas.*') && !request()->routeIs('despesas.create')) ? 'active' : '' }}">
            <i class="bi bi-credit-card"></i> Despesas
        </a>
        <a href="{{ route('despesas.create') }}" class="nav-item {{ request()->routeIs('despesas.create') ? 'active' : '' }}">
            <i class="bi bi-plus-square"></i> Nova Despesa
        </a>

        <div class="nav-section-label">Conformidade</div>
        <a href="{{ route('diligencias.index') }}" class="nav-item {{ request()->routeIs('diligencias.*') ? 'active' : '' }}">
            <i class="bi bi-clipboard-check"></i> Diligências
        </a>
        <a href="{{ route('prestacao-contas.index') }}" class="nav-item {{ request()->routeIs('prestacao-contas.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-ruled"></i> Prestação de Contas
        </a>
        <a href="{{ route('documentos.index') }}" class="nav-item {{ request()->routeIs('documentos.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-text"></i> Documentos
        </a>

        <div class="nav-section-label">Relatórios</div>
        <a href="{{ route('relatorios.index') }}" class="nav-item {{ request()->routeIs('relatorios.*') ? 'active' : '' }}">
            <i class="bi bi-bar-chart-line"></i> Relatórios
        </a>

        @if(in_array(auth()->user()->role, ['ADMIN_GERAL','ADMIN_INSTITUICAO']))
        <div class="nav-section-label">Administração</div>
        <a href="{{ route('usuarios.index') }}" class="nav-item {{ request()->routeIs('usuarios.*') ? 'active' : '' }}">
            <i class="bi bi-people"></i> Usuários
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('auditoria.index') }}" class="nav-item {{ request()->routeIs('auditoria.*') ? 'active' : '' }}">
            <i class="bi bi-shield-check"></i> Auditoria
        </a>
        <a href="{{ route('configuracoes.index') }}" class="nav-item {{ request()->routeIs('configuracoes.*') ? 'active' : '' }}">
            <i class="bi bi-sliders"></i> Configurações
        </a>
        @endif
        @endif
    </nav>

    <div style="padding: 12px 8px; border-top: 1px solid var(--border);">
        <form method="POST" action="{{ route('logout') }}" data-turbo="false">
            @csrf
            <button type="submit" class="nav-item w-100 border-0 bg-transparent text-start" style="color: var(--text-muted);">
                <i class="bi bi-box-arrow-left"></i> Sair do sistema
            </button>
        </form>
    </div>
</div>

<div id="main">
    <header id="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-secondary btn-sm d-lg-none" id="sidebarToggle" style="padding: 5px 8px;">
                <i class="bi bi-list" style="font-size: 16px;"></i>
            </button>
            <span class="topbar-domain d-none d-md-block">{{ request()->getHost() }}</span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="user-info d-none d-sm-flex align-items-center gap-2">
                <i class="bi bi-person-circle" style="color: var(--text-muted);"></i>
                {{ auth()->user()->name }}
            </span>
            <span class="role-badge">{{ auth()->user()->role }}</span>
        </div>
    </header>

    <main class="page-content">
        @if(session('success'))
        <div class="alert alert-success d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-check-circle-fill flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="font-size: 11px;"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger d-flex align-items-center gap-2 mb-4">
            <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" style="font-size: 11px;"></button>
        </div>
        @endif
        @if($errors->any())
        <div class="alert alert-danger mb-4">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-exclamation-triangle-fill flex-shrink-0"></i>
                <strong>Verifique os campos abaixo:</strong>
            </div>
            <ul class="mb-0 ps-3" style="margin-top: 6px;">
                @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
            </ul>
        </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="{{ asset('js/turbo.min.js') }}"></script>
<script src="{{ asset('js/bootstrap.bundle.min.js') }}"></script>
<script>
    // Turbo Drive: navegação sem reload total — mantém sidebar, elimina piscar
    // Desabilitar Turbo em links de download e logout
    document.addEventListener('turbo:load', initPage);
    document.addEventListener('DOMContentLoaded', initPage);

    function initPage() {
        // Sidebar toggle mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (sidebarToggle && sidebar) {
            sidebarToggle.onclick = () => sidebar.classList.toggle('open');
            document.addEventListener('click', (e) => {
                if (sidebar.classList.contains('open') && !sidebar.contains(e.target) && e.target !== sidebarToggle) {
                    sidebar.classList.remove('open');
                }
            });
        }

        // Auto-fechar alerts após 4s
        document.querySelectorAll('.alert.alert-success').forEach(el => {
            setTimeout(() => el.style.transition = 'opacity .4s', 3600);
            setTimeout(() => el.style.opacity = '0', 4000);
            setTimeout(() => el.remove(), 4400);
        });
    }

    // Indicador de progresso no topo durante navegação
    const bar = document.createElement('div');
    bar.id = 'turbo-bar';
    bar.style.cssText = 'position:fixed;top:0;left:0;height:2px;background:#18181b;width:0;z-index:9999;transition:width .3s,opacity .3s;opacity:0;';
    document.body.appendChild(bar);

    document.addEventListener('turbo:visit', () => {
        bar.style.width = '0'; bar.style.opacity = '1';
        requestAnimationFrame(() => { bar.style.width = '70%'; });
    });
    document.addEventListener('turbo:load', () => {
        bar.style.width = '100%';
        setTimeout(() => { bar.style.opacity = '0'; bar.style.width = '0'; }, 300);
    });
</script>
@stack('scripts')
</body>
</html>

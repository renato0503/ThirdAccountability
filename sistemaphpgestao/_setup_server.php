<?php
// Script temporário de setup do servidor — DELETAR APÓS USO
$token = $_GET['token'] ?? '';
if ($token !== 'setup2026gestao') {
    http_response_code(403);
    die('Acesso negado.');
}

$base = __DIR__;
$results = [];

// 1. Criar diretórios necessários do storage
$dirs = [
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/testing',
    'storage/app/public',
    'storage/app/private',
    'storage/logs',
    'bootstrap/cache',
];
foreach ($dirs as $dir) {
    $path = $base . '/' . $dir;
    if (!is_dir($path)) {
        mkdir($path, 0775, true);
        $results[] = "✅ Criado: $dir";
    } else {
        $results[] = "OK (já existe): $dir";
    }
}

// 2. Criar arquivos .gitignore nos diretórios de storage
$gitignores = [
    'storage/framework/views'   => "*\n!.gitignore\n",
    'storage/framework/cache'   => "*\n!.gitignore\n",
    'storage/framework/sessions'=> "*\n!.gitignore\n",
    'storage/logs'              => "*\n!.gitignore\n",
];
foreach ($gitignores as $dir => $content) {
    $path = $base . '/' . $dir . '/.gitignore';
    if (!file_exists($path)) {
        file_put_contents($path, $content);
    }
}

// 3. Limpar caches de bootstrap
$cachFiles = [
    'bootstrap/cache/config.php',
    'bootstrap/cache/routes-v7.php',
    'bootstrap/cache/packages.php',
    'bootstrap/cache/services.php',
    'bootstrap/cache/events.php',
];
foreach ($cachFiles as $f) {
    $path = $base . '/' . $f;
    if (file_exists($path)) {
        unlink($path);
        $results[] = "🗑️ Cache removido: $f";
    }
}

// 4. Testar conexão com BD
try {
    $env = file_get_contents($base . '/.env');
    preg_match('/DB_HOST=(.+)/m', $env, $m1);
    preg_match('/DB_DATABASE=(.+)/m', $env, $m2);
    preg_match('/DB_USERNAME=(.+)/m', $env, $m3);
    preg_match('/DB_PASSWORD=(.+)/m', $env, $m4);
    preg_match('/DB_PORT=(.+)/m', $env, $m5);

    $host = trim($m1[1] ?? '127.0.0.1');
    $db   = trim($m2[1] ?? '');
    $user = trim($m3[1] ?? '');
    $pass = trim($m4[1] ?? '');
    $port = trim($m5[1] ?? '3306');

    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $results[] = "✅ Banco de dados: conectado ($db@$host)";

    // Verificar tabelas existentes
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $results[] = "📋 Tabelas no banco: " . implode(', ', $tables);

    // Verificar migrations pendentes
    $migDir = $base . '/database/migrations';
    $migFiles = array_map('basename', glob($migDir . '/*.php'));

    if (in_array('migrations', $tables)) {
        $ran = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
        $ran = array_map(fn($m) => $m . '.php', $ran);
        $pending = array_diff($migFiles, $ran);
        if ($pending) {
            $results[] = "⚠️ Migrations PENDENTES (" . count($pending) . "):";
            foreach (array_values($pending) as $p) {
                $results[] = "   → $p";
            }
            $results[] = '<strong>👉 Acesse: <a href="?token=setup2026gestao&run_migrations=1">Rodar Migrations</a></strong>';
        } else {
            $results[] = "✅ Todas as migrations já foram executadas.";
        }
    } else {
        $results[] = "⚠️ Tabela 'migrations' não encontrada — banco vazio?";
    }
} catch (Exception $e) {
    $results[] = "❌ Erro BD: " . $e->getMessage();
}

// 5. Rodar migrations se solicitado
if (isset($_GET['run_migrations'])) {
    try {
        require_once $base . '/vendor/autoload.php';
        $app = require_once $base . '/bootstrap/app.php';
        $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
        $artisan = $app->make(Illuminate\Contracts\Console\Kernel::class);
        ob_start();
        $artisan->call('migrate', ['--force' => true]);
        $output = ob_get_clean();
        $results[] = "✅ Migrations executadas:";
        $results[] = "<pre>" . htmlspecialchars($output) . "</pre>";
    } catch (Exception $e) {
        $results[] = "❌ Erro ao migrar: " . $e->getMessage();
    }
}

// 6. Testar link do storage
$storagePath = $base . '/public/storage';
if (!file_exists($storagePath)) {
    if (symlink($base . '/storage/app/public', $storagePath)) {
        $results[] = "✅ storage:link criado";
    } else {
        $results[] = "⚠️ Não foi possível criar storage:link (crie manualmente)";
    }
} else {
    $results[] = "OK: public/storage link já existe";
}

?><!DOCTYPE html>
<html>
<head><meta charset="utf-8"><title>Setup Servidor</title>
<style>body{font-family:monospace;padding:20px;background:#1a1a1a;color:#e0e0e0}
.ok{color:#4ade80}.err{color:#f87171}.warn{color:#fbbf24}
pre{background:#2a2a2a;padding:10px;border-radius:4px}
a{color:#60a5fa}
</style>
</head>
<body>
<h2>⚙️ Setup Servidor — Gestão Terceiro</h2>
<p style="color:#f87171"><strong>⚠️ DELETAR ESTE ARQUIVO APÓS USO!</strong></p>
<hr>
<?php foreach ($results as $r): ?>
<div><?= $r ?></div>
<?php endforeach; ?>
<hr>
<p style="color:#9ca3af;font-size:12px">Para rodar migrations: adicione <code>&run_migrations=1</code> na URL</p>
</body>
</html>

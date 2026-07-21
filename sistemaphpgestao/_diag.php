<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->bind('path.public', fn() => __DIR__.'/public');
$app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();

echo "=== DIAGNÓSTICO DE ESTADO ATUAL ===\n";
echo "Hora servidor: " . date('Y-m-d H:i:s') . "\n\n";

// 1. PHP upload limits
echo "[1] LIMITES PHP:\n";
echo "  upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "  post_max_size: " . ini_get('post_max_size') . "\n";
echo "  max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "  memory_limit: " . ini_get('memory_limit') . "\n";
echo "  max_execution_time: " . ini_get('max_execution_time') . "\n";

// 2. Cache de config (se config estiver cacheado, nossa mudança não vale)
echo "\n[2] CACHE LARAVEL:\n";
$bcache = __DIR__.'/bootstrap/cache';
foreach (['config.php','routes-v7.php','events.php','services.php','packages.php'] as $f) {
    $p = "{$bcache}/{$f}";
    if (file_exists($p)) {
        echo "  ⚠ EXISTE {$f} (mtime " . date('Y-m-d H:i:s', filemtime($p)) . ", " . filesize($p) . " B)\n";
    } else {
        echo "  · sem {$f}\n";
    }
}

// 3. Disk public REAL
echo "\n[3] CONFIG FILESYSTEMS (em runtime):\n";
$cfg = config('filesystems.disks.public');
echo "  driver: {$cfg['driver']}\n";
echo "  root:   {$cfg['root']}\n";
echo "  url:    {$cfg['url']}\n";

// 4. APP URL
echo "\n[4] APP URL: " . config('app.url') . "\n";

// 5. Documentos atuais
echo "\n[5] DOCUMENTOS NO BANCO:\n";
foreach (\App\Models\Document::orderBy('id')->get() as $d) {
    echo "  #{$d->id} {$d->nome} | file_path=" . ($d->file_path ?? 'null') . " | url=" . ($d->url ?? 'null') . "\n";
}

// 6. AuditLog mais recentes (últimos 15)
echo "\n[6] ÚLTIMOS 15 AUDIT LOGS:\n";
foreach (\App\Models\AuditLog::orderBy('id','desc')->take(15)->get() as $a) {
    echo "  #{$a->id} [{$a->created_at}] u={$a->user_id} {$a->acao} {$a->entidade}#{$a->entidade_id}\n";
}

// 7. Laravel log
echo "\n[7] LARAVEL.LOG (últimas 60 linhas):\n";
$log = __DIR__.'/storage/logs/laravel.log';
if (file_exists($log)) {
    echo "  tamanho: " . filesize($log) . " B · mtime: " . date('Y-m-d H:i:s', filemtime($log)) . "\n";
    $lines = file($log);
    foreach (array_slice($lines, -60) as $l) echo "    " . rtrim($l) . "\n";
} else {
    echo "  (não existe)\n";
}

// 8. Error log do PHP/cPanel
echo "\n[8] error_log no diretório (últimas 30 linhas):\n";
foreach ([__DIR__.'/error_log', __DIR__.'/public/error_log'] as $el) {
    if (file_exists($el)) {
        echo "  --- {$el} (" . filesize($el) . " B mtime " . date('Y-m-d H:i:s', filemtime($el)) . ") ---\n";
        $lines = file($el);
        foreach (array_slice($lines, -30) as $l) echo "    " . rtrim($l) . "\n";
    }
}

// 9. Permissões críticas
echo "\n[9] PERMISSÕES:\n";
$paths = [
    __DIR__.'/storage',
    __DIR__.'/storage/logs',
    __DIR__.'/storage/framework',
    __DIR__.'/storage/framework/sessions',
    __DIR__.'/storage/framework/views',
    __DIR__.'/storage/framework/cache',
    __DIR__.'/bootstrap/cache',
    __DIR__.'/public/storage',
    __DIR__.'/public/storage/documentos',
];
foreach ($paths as $p) {
    if (is_dir($p)) {
        $perm = substr(sprintf('%o', fileperms($p)), -4);
        $w = is_writable($p) ? 'W' : '-';
        echo "  {$perm} [{$w}] {$p}\n";
    } else {
        echo "  ❌ NÃO EXISTE: {$p}\n";
    }
}

echo "\n=== FIM ===\n";

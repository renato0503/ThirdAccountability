<?php
/**
 * SETUP — Gestão Terceiro
 * Acesse: https://project.byrees.com/sistemaphpgestao/setup.php
 * APAGUE este arquivo após o setup!
 */

define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->bind('path.public', fn() => __DIR__.'/public');
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$results = [];

function run($kernel, $cmd, $params = []) {
    ob_start();
    $code = $kernel->call($cmd, $params);
    $out  = trim(ob_get_clean());
    return ['cmd' => $cmd, 'code' => $code, 'out' => $out];
}

// 1. Migrate
$results[] = run($kernel, 'migrate', ['--force' => true]);

// 2. Seed settings
$results[] = run($kernel, 'db:seed', ['--class' => 'SettingSeeder', '--force' => true]);

// 3. Storage link via PHP (sem exec)
$link   = __DIR__.'/public/storage';
$target = __DIR__.'/storage/app/public';
if (!file_exists($link)) {
    if (symlink($target, $link)) {
        $results[] = ['cmd' => 'storage:link', 'code' => 0, 'out' => 'Symlink criado.'];
    } else {
        $results[] = ['cmd' => 'storage:link', 'code' => 1, 'out' => 'Falhou — tente via File Manager criar link manualmente.'];
    }
} else {
    $results[] = ['cmd' => 'storage:link', 'code' => 0, 'out' => 'Já existe.'];
}

// 4. Caches
$results[] = run($kernel, 'config:clear');
$results[] = run($kernel, 'route:clear');
$results[] = run($kernel, 'view:clear');

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Setup — Gestão Terceiro</title>
<style>
body { font-family: monospace; background: #09090b; color: #e4e4e7; padding: 40px; max-width: 700px; margin: 0 auto; }
h1 { color: #fff; font-size: 18px; border-bottom: 1px solid #27272a; padding-bottom: 12px; }
.item { background: #18181b; border: 1px solid #27272a; border-radius: 6px; padding: 14px 18px; margin: 10px 0; }
.ok   { border-left: 3px solid #16a34a; }
.fail { border-left: 3px solid #ef4444; }
.cmd  { font-weight: bold; color: #a1a1aa; font-size: 13px; }
.out  { margin-top: 6px; color: #d4d4d8; font-size: 12.5px; white-space: pre-wrap; }
.warn { background: #422006; border: 1px solid #92400e; border-radius: 6px; padding: 14px 18px; margin-top: 20px; color: #fde68a; font-size: 13px; }
</style>
</head>
<body>
<h1>⚙ Setup — Gestão Terceiro</h1>

<?php foreach ($results as $r): ?>
<div class="item <?= $r['code'] == 0 ? 'ok' : 'fail' ?>">
    <div class="cmd"><?= $r['code'] == 0 ? '✓' : '✗' ?> php artisan <?= htmlspecialchars($r['cmd']) ?></div>
    <?php if ($r['out']): ?>
    <div class="out"><?= htmlspecialchars($r['out']) ?></div>
    <?php endif ?>
</div>
<?php endforeach ?>

<div class="warn">
    ⚠ <strong>APAGUE este arquivo após o setup!</strong><br>
    Delete <code>setup.php</code> via FTP ou File Manager do cPanel.
</div>

</body>
</html>

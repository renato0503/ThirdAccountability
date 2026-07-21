<?php
// Diagnóstico rápido — apague após usar
$checks = [];

// PHP version
$phpOk = version_compare(PHP_VERSION, '8.1.0', '>=');
$checks[] = ['PHP >= 8.1', $phpOk, PHP_VERSION];

// Extensions
foreach (['pdo', 'pdo_mysql', 'mbstring', 'openssl', 'tokenizer', 'xml', 'ctype', 'json', 'bcmath', 'fileinfo', 'curl'] as $ext) {
    $checks[] = ["ext-$ext", extension_loaded($ext), extension_loaded($ext) ? 'OK' : 'FALTANDO'];
}

// Permissões de escrita
foreach ([
    'storage/framework/views',
    'storage/framework/cache',
    'storage/framework/sessions',
    'storage/logs',
    'bootstrap/cache',
] as $dir) {
    $path = __DIR__.'/'.$dir;
    $writable = is_writable($path);
    $checks[] = ["Escrita: $dir", $writable, $writable ? 'OK' : 'SEM PERMISSÃO'];
}

// .env existe
$checks[] = ['.env existe', file_exists(__DIR__.'/.env'), file_exists(__DIR__.'/.env') ? 'OK' : 'NÃO ENCONTRADO'];

// vendor existe
$checks[] = ['vendor/autoload.php', file_exists(__DIR__.'/vendor/autoload.php'), file_exists(__DIR__.'/vendor/autoload.php') ? 'OK' : 'NÃO ENCONTRADO'];

$allOk = !in_array(false, array_column($checks, 1));
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Check — Gestão Terceiro</title>
<style>
body { font-family: monospace; background: #09090b; color: #e4e4e7; padding: 40px; max-width: 600px; margin: 0 auto; }
h1 { color: #fff; font-size: 18px; border-bottom: 1px solid #27272a; padding-bottom: 12px; }
.item { display: flex; justify-content: space-between; padding: 8px 14px; border-radius: 4px; margin: 4px 0; font-size: 13px; }
.ok   { background: #052e16; color: #86efac; }
.fail { background: #450a0a; color: #fca5a5; }
.warn { background: #422006; border: 1px solid #92400e; border-radius: 6px; padding: 14px 18px; margin-top: 20px; color: #fde68a; font-size: 13px; }
</style>
</head>
<body>
<h1>🔍 Diagnóstico do Servidor</h1>
<?php foreach ($checks as [$label, $ok, $val]): ?>
<div class="item <?= $ok ? 'ok' : 'fail' ?>">
    <span><?= $ok ? '✓' : '✗' ?> <?= htmlspecialchars($label) ?></span>
    <span><?= htmlspecialchars($val) ?></span>
</div>
<?php endforeach ?>
<?php if ($allOk): ?>
<div class="warn" style="background:#052e16; border-color:#16a34a; color:#86efac;">
    ✓ Tudo OK! Acesse <a href="setup.php" style="color:#4ade80;">setup.php</a> para configurar.
</div>
<?php else: ?>
<div class="warn">
    ✗ Corrija os itens em vermelho antes de continuar.
</div>
<?php endif ?>
<div class="warn">⚠ Apague este arquivo após o diagnóstico.</div>
</body>
</html>

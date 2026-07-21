<?php
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->bind('path.public', fn() => __DIR__.'/public');
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->call('view:clear');
$kernel->call('config:clear');
echo 'Cache limpo.';

<?php
// Entrada alternativa quando document root não pode ser alterado para /public
define('LARAVEL_START', microtime(true));

if (file_exists($maintenance = __DIR__.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

// Ajusta os caminhos para rodar da raiz em vez de /public
$app->bind('path.public', fn() => __DIR__.'/public');

$app->handleRequest(\Illuminate\Http\Request::capture());

<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo '<pre style="background:#111;color:#eee;padding:20px;font-size:13px;">';

try {
    echo "1. Carregando autoload...\n";
    require __DIR__.'/vendor/autoload.php';
    echo "   OK\n";

    echo "2. Carregando bootstrap/app.php...\n";
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->bind('path.public', fn() => __DIR__.'/public');
    echo "   OK\n";

    echo "3. Testando conexão com banco de dados...\n";
    $app->make('Illuminate\Contracts\Http\Kernel')->bootstrap();
    $pdo = \Illuminate\Support\Facades\DB::connection()->getPdo();
    echo "   OK — MySQL conectado\n";

    echo "4. Verificando tabelas...\n";
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "   Tabelas: " . implode(', ', $tables) . "\n";

    echo "\n✓ Laravel bootstrap OK. Pode rodar setup.php\n";

} catch (\Throwable $e) {
    echo "\n✗ ERRO: " . $e->getMessage() . "\n";
    echo "Arquivo: " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo '</pre>';

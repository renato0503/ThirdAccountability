<?php
ini_set('display_errors', 1);
set_time_limit(60);
define('LARAVEL_START', microtime(true));
require __DIR__.'/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$app = require_once __DIR__.'/bootstrap/app.php';
$app->bind('path.public', fn() => __DIR__.'/public');

$app->bootstrapWith([
    \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
    \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
    \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
    \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
    \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
    \Illuminate\Foundation\Bootstrap\BootProviders::class,
]);

try {
    \DB::connection()->getPdo();
    echo "Banco: " . env('DB_DATABASE') . "\n";

    $migrator = app('migrator');
    if (!$migrator->repositoryExists()) {
        $migrator->getRepository()->createRepository();
    }

    $files   = $migrator->getMigrationFiles([database_path('migrations')]);
    $ran     = $migrator->getRepository()->getRan();
    $pending = array_diff_key($files, array_flip($ran));

    echo count($pending) . " migrations pendentes\n";
    $batch = $migrator->getRepository()->getNextBatchNumber();

    foreach ($pending as $name => $file) {
        try {
            $migration = require $file;
            if (!is_object($migration)) {
                $class = Illuminate\Support\Str::studly(implode('_', array_slice(explode('_', $name), 4)));
                $migration = new $class;
            }
            $migration->up();
            $migrator->getRepository()->log($name, $batch);
            echo "  OK: $name\n";
        } catch (\Throwable $e) {
            echo "  ERRO: $name — " . $e->getMessage() . "\n";
        }
    }
    echo "Done!\n";
} catch (\Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

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

    $email = 'andrerees@gmail.com';
    $exists = \DB::table('users')->where('email', $email)->first();

    if ($exists) {
        echo "Usuário já existe: $email\n";
    } else {
        \DB::table('users')->insert([
            'name'      => 'Administrador',
            'email'     => $email,
            'password'  => \Illuminate\Support\Facades\Hash::make('Admin@2026'),
            'role'      => 'admin',
            'active'    => 1,
            'created_at'=> now(),
            'updated_at'=> now(),
        ]);
        echo "Admin criado!\n";
        echo "Email: $email\n";
        echo "Senha: Admin@2026\n";
    }
} catch (\Throwable $e) {
    echo "ERRO: " . $e->getMessage() . "\n";
}

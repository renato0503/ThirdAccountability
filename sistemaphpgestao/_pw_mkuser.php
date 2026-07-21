<?php
/**
 * ATENCAO: Credenciais removidas do repositorio por seguranca.
 * Edite antes de usar.
 */
require __DIR__ . '/bootstrap/app.php';
$kernel = app(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$u = App\Models\User::updateOrCreate(
  ['email' => 'teste@SEU-DOMINIO.com'],
  ['name' => 'Usuario Teste', 'role' => 'ADMIN_GERAL', 'active' => 1, 'password' => bcrypt(getenv('ADMIN_PASSWORD') ?: '__DEFINA_A_SENHA__')]
);
echo "ID=" . $u->id . PHP_EOL;

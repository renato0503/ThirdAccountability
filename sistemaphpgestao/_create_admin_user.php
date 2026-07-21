<?php
/**
 * Cria/atualiza um usuario ADMIN_GERAL.
 *
 * Uso:
 *   1) Edite EMAIL, SENHA e NOME abaixo se quiser
 *   2) Suba para sistemaphpgestao/
 *   3) Acesse:
 *        https://project.byrees.com/sistemaphpgestao/_create_admin_user.php?token=SEU-TOKEN
 *   4) APAGUE o arquivo apos uso
 */

/**
 * ATENCAO: Credenciais removidas do repositorio por seguranca.
 * Edite as variaveis abaixo com dados reais ANTES de enviar ao servidor.
 */
$EXPECTED_TOKEN = 'SEU-TOKEN-AQUI';

// ── Credenciais do admin (preencha via env ou editando aqui) ──
$ADMIN_NAME     = 'Administrador Geral';
$ADMIN_EMAIL    = 'admin@SEU-DOMINIO.com';
$ADMIN_PASSWORD = getenv('ADMIN_PASSWORD') ?: '__DEFINA_A_SENHA__';
// ────────────────────────────────────────────────────────────────

if (!isset($_GET['token']) || !hash_equals($EXPECTED_TOKEN, (string) $_GET['token'])) {
    http_response_code(403);
    exit('Token invalido.');
}

@set_time_limit(60);
header('Content-Type: text/plain; charset=utf-8');

echo "== Criar/atualizar admin ==\n";
echo "Data: " . date('Y-m-d H:i:s') . "\n\n";

// Bootstrap minimo (sem Symfony Console)
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';

try {
    $app->bootstrapWith([
        \Illuminate\Foundation\Bootstrap\LoadEnvironmentVariables::class,
        \Illuminate\Foundation\Bootstrap\LoadConfiguration::class,
        \Illuminate\Foundation\Bootstrap\HandleExceptions::class,
        \Illuminate\Foundation\Bootstrap\RegisterFacades::class,
        \Illuminate\Foundation\Bootstrap\RegisterProviders::class,
        \Illuminate\Foundation\Bootstrap\BootProviders::class,
    ]);
} catch (\Throwable $e) {
    exit('Bootstrap falhou: ' . $e->getMessage() . "\n");
}

try {
    \Illuminate\Support\Facades\DB::connection()->getPdo();
} catch (\Throwable $e) {
    exit('DB falhou: ' . $e->getMessage() . "\n");
}

if (!\Illuminate\Support\Facades\Schema::hasTable('users')) {
    exit("Tabela 'users' nao existe. Rode primeiro o _deploy_v2 ou as migrations base.\n");
}

$cols = \Illuminate\Support\Facades\Schema::getColumnListing('users');
echo "Colunas detectadas em users: " . implode(', ', $cols) . "\n\n";

$payload = [
    'name'     => $ADMIN_NAME,
    'password' => password_hash($ADMIN_PASSWORD, PASSWORD_BCRYPT),
    'role'     => 'ADMIN_GERAL',
];

if (in_array('active', $cols, true))           $payload['active'] = 1;
if (in_array('institution_id', $cols, true))   $payload['institution_id'] = null;
if (in_array('email_verified_at', $cols, true))$payload['email_verified_at'] = date('Y-m-d H:i:s');

$existing = \Illuminate\Support\Facades\DB::table('users')
    ->where('email', $ADMIN_EMAIL)
    ->first();

if ($existing) {
    $payload['updated_at'] = date('Y-m-d H:i:s');
    \Illuminate\Support\Facades\DB::table('users')
        ->where('id', $existing->id)
        ->update($payload);
    echo "[ATUALIZADO]\n";
    echo "    ID:    {$existing->id}\n";
} else {
    $payload['email']      = $ADMIN_EMAIL;
    $payload['created_at'] = date('Y-m-d H:i:s');
    $payload['updated_at'] = date('Y-m-d H:i:s');
    $id = \Illuminate\Support\Facades\DB::table('users')->insertGetId($payload);
    echo "[CRIADO]\n";
    echo "    ID:    {$id}\n";
}

echo "    Nome:  {$ADMIN_NAME}\n";
echo "    Email: {$ADMIN_EMAIL}\n";
echo "    Senha: {$ADMIN_PASSWORD}\n";
echo "    Role:  ADMIN_GERAL\n\n";

echo str_repeat('=', 60) . "\n";
echo "ATENCAO:\n";
echo "  1) Faca login em /login com email/senha acima\n";
echo "  2) Troque a senha imediatamente\n";
echo "  3) APAGUE este arquivo do servidor\n";
echo "Caminho: " . __FILE__ . "\n";

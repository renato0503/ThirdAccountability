<?php
// Script temporário de criação de admin — DELETAR APÓS USO
$token = $_GET['token'] ?? '';
if ($token !== 'admin2026gestao') {
    http_response_code(403);
    die('Acesso negado.');
}

$base = __DIR__;

require_once $base . '/vendor/autoload.php';
$app = require_once $base . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->boot();

$email    = $_GET['email']    ?? 'admin@gestaoterciario.com.br';
$password = $_GET['password'] ?? 'Admin@2026!';
$name     = $_GET['name']     ?? 'Administrador Geral';

try {
    $exists = \App\Models\User::where('email', $email)->first();
    if ($exists) {
        $exists->update([
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'role'     => 'ADMIN_GERAL',
        ]);
        echo "<p style='color:green;font-family:monospace'>✅ Usuário atualizado: <strong>{$email}</strong> | Senha: <strong>{$password}</strong> | Role: ADMIN_GERAL</p>";
    } else {
        \App\Models\User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => \Illuminate\Support\Facades\Hash::make($password),
            'role'     => 'ADMIN_GERAL',
        ]);
        echo "<p style='color:green;font-family:monospace'>✅ Admin criado: <strong>{$email}</strong> | Senha: <strong>{$password}</strong> | Role: ADMIN_GERAL</p>";
    }
    echo "<p style='font-family:monospace;color:#f87171'><strong>⚠️ DELETE ESTE ARQUIVO AGORA via cPanel!</strong></p>";
    echo "<p style='font-family:monospace'><a href='/sistemaphpgestao/login'>→ Ir para o login</a></p>";
} catch (Exception $e) {
    echo "<p style='color:red;font-family:monospace'>❌ Erro: " . htmlspecialchars($e->getMessage()) . "</p>";
}

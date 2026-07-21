<?php
/**
 * ATENCAO: Credenciais removidas por seguranca.
 * Preencha com dados reais do seu ambiente Dbaas antes de usar.
 */
set_time_limit(30);
$host = getenv('DB_HOST') ?: 'SEU_HOST_DBAAS';
$port = 3306;

// Preencha com credenciais de admin do seu banco
$attempts = [
    ['root', getenv('DB_ROOT_PASSWORD') ?: '__DEFINA_ROOT__'],
];

foreach ($attempts as [$user, $pass]) {
    try {
        $pdo = new PDO("mysql:host=$host;port=$port", $user, $pass, [PDO::ATTR_TIMEOUT => 5]);
        echo "CONECTADO como $user\n";
        // Tenta desbloquear
        try {
            $pdo->exec("ALTER USER 'gestao3setor'@'%' ACCOUNT UNLOCK");
            echo "DESBLOQUEADO!\n";
        } catch (Exception $e2) {
            echo "Conectou mas sem permissão de UNLOCK: " . $e2->getMessage() . "\n";
            // Tenta listar usuários
            $rows = $pdo->query("SELECT user, host, account_locked FROM mysql.user WHERE user='gestao3setor'")->fetchAll();
            print_r($rows);
        }
        break;
    } catch (PDOException $e) {
        echo "Falhou $user: " . substr($e->getMessage(), 0, 60) . "\n";
    }
}

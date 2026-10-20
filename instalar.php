<?php
/**
 * SGRH — Instalador do banco de dados.
 *
 * Execute pelo navegador:  http://localhost/Sistema-Gerenciamento-RH/instalar.php
 * Ou via linha de comando: php instalar.php
 *
 * O script cria o banco, todas as tabelas e popula com dados de exemplo.
 * A instalação também acontece automaticamente na primeira visita ao index.php.
 */

if (PHP_SAPI === 'cli') {
    define('SGRH_CLI', true);
} else {
    define('SGRH_CLI', false);
}

require __DIR__ . '/app/bootstrap.php';

use App\Core\Database;
use App\Core\Installer;

Database::config($GLOBALS['config']['db']);

$acao = $_GET['acao'] ?? ($argv[1] ?? 'instalar');

if ($acao === 'status') {
    $tabelas = [];
    $existe = false;
    try {
        $pdo = Database::conexao();
        $existe = (int) $pdo->query(
            "SELECT COUNT(*) FROM information_schema.tables
             WHERE table_schema = " . $pdo->quote(Database::nomeBanco())
        )->fetchColumn() > 0;
    } catch (Throwable $e) {
        $erro = $e->getMessage();
    }
    if ($existe) {
        $pdo = Database::conectar(Database::nomeBanco());
        $tabelas = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    }
    $saida = [
        'Banco configurado' => Database::nomeBanco(),
        'Conexão'            => isset($erro) ? 'FALHOU: ' . $erro : 'OK',
        'Banco criado'       => $existe ? 'SIM' : 'NÃO',
        'Tabelas'            => count($tabelas),
        'Lista'              => $tabelas ?: [],
    ];
    if (SGRH_CLI) {
        foreach ($saida as $k => $v) {
            echo str_pad($k, 20) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . PHP_EOL;
        }
        exit;
    }
    header('Content-Type: text/plain; charset=utf-8');
    foreach ($saida as $k => $v) {
        echo str_pad($k, 20) . ': ' . (is_array($v) ? implode(', ', $v) : $v) . PHP_EOL;
    }
    exit;
}

$log = [];
$erro = null;
try {
    $instalador = new Installer($GLOBALS['config']['db']);
    $instalador->instalar(true);
    $log = $instalador->log();
    Database::desconectar();
} catch (Throwable $e) {
    $erro = $e->getMessage();
}

if (SGRH_CLI) {
    echo PHP_EOL . '=== INSTALAÇÃO DO SGRH ===' . PHP_EOL;
    foreach ($log as $l) {
        echo '  [ok] ' . $l . PHP_EOL;
    }
    if ($erro) {
        echo PHP_EOL . '  [ERRO] ' . $erro . PHP_EOL;
        exit(1);
    }
    echo PHP_EOL . 'Instalação concluída. Acesse: ' . BASE_URL . '/' . PHP_EOL;
    exit(0);
}

header('Content-Type: text/html; charset=utf-8');
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SGRH — Instalação do banco de dados</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px">
<div class="card" style="max-width:640px;width:100%">
    <h1 class="card__title">Instalação do SGRH</h1>
    <p class="text-muted mt-2">Criação do banco de dados, tabelas e dados iniciais.</p>

    <?php if ($erro): ?>
        <div class="alert alert--danger mt-4">
            <?= icon('alert', 18) ?>
            <div>
                <strong>Não foi possível concluir:</strong>
                <div style="margin-top:4px;font-size:13px"><?= e($erro) ?></div>
            </div>
        </div>
        <p class="mt-4 text-muted" style="font-size:13px">
            Verifique se o MySQL está rodando e se as credenciais em <code>config/config.php</code> estão corretas.
        </p>
    <?php else: ?>
        <div class="alert alert--success mt-4">
            <?= icon('check', 18) ?>
            <div>
                <strong>Instalação concluída com sucesso.</strong>
                <div style="margin-top:4px;font-size:13px">
                    Banco <code><?= e(Database::nomeBanco()) ?></code> criado com todas as tabelas e dados de exemplo.
                </div>
            </div>
        </div>
        <ul class="list-plain mt-4">
            <?php foreach ($log as $l): ?>
                <li class="list-row"><span class="list-row__meta"><?= icon('check', 14) ?></span><span class="list-row__title"><?= e($l) ?></span></li>
            <?php endforeach; ?>
        </ul>
        <div class="auth-demo mt-4">
            <strong>Credenciais de acesso</strong><br>
            Administrador: <code>admin@sgrh.local</code> / <code>sgrh@2026</code><br>
            RH: <code>rh@sgrh.local</code> / <code>sgrh@2026</code>
        </div>
    <?php endif; ?>

    <div class="form-actions">
        <a class="btn btn--secondary" href="<?= url('instalar.php') ?>?acao=status">Ver status</a>
        <a class="btn btn--primary" href="<?= url('login') ?>">Ir para o login</a>
    </div>
</div>
</body>
</html>

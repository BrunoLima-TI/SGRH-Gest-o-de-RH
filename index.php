<?php
/**
 * SGRH — Sistema de Gestão de Recursos Humanos
 * Front Controller (ponto de entrada único).
 *
 * Na primeira execução o banco de dados, as tabelas e os dados iniciais
 * são criados automaticamente.
 */

require __DIR__ . '/app/bootstrap.php';

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Database;
use App\Core\Installer;
use App\Core\Session;

Database::config($GLOBALS['config']['db']);

/* ------------------------------------------------------------------
 *  1. Garante que o banco de dados e as tabelas existam
 * ------------------------------------------------------------------ */
$instalado = false;
$erroInstalacao = null;

try {
    $pdo = Database::conectar('');
    $tabela = $pdo->query(
        "SELECT COUNT(*) FROM information_schema.tables
         WHERE table_schema = " . $pdo->quote(Database::nomeBanco()) . " AND table_name = 'colaboradores'"
    )->fetchColumn();
    $instalado = ((int) $tabela) > 0;
} catch (Throwable $e) {
    $erroInstalacao = $e->getMessage();
}

if (!$instalado) {
    try {
        $instalador = new Installer($GLOBALS['config']['db']);
        $instalador->instalar(true);
        Database::desconectar();
        Session::flash('sucesso', 'Banco de dados criado e populado com sucesso. Faça login para começar.');
    } catch (Throwable $e) {
        $erroInstalacao = $e->getMessage();
    }
}

/* ------------------------------------------------------------------
 *  2. Se o banco ainda não estiver pronto, mostra a tela de instalação
 * ------------------------------------------------------------------ */
if ($erroInstalacao !== null) {
    http_response_code(500);
    $dbCfg = $GLOBALS['config']['db'];
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8">'
        . '<meta name="viewport" content="width=device-width,initial-scale=1">'
        . '<title>SGRH — Instalação</title><link rel="stylesheet" href="' . asset('css/app.css') . '"></head>'
        . '<body style="display:flex;align-items:center;justify-content:center;min-height:100vh;padding:24px">'
        . '<div class="card" style="max-width:620px;width:100%">'
        . '<h1 class="card__title">Não foi possível preparar o banco de dados</h1>'
        . '<p class="text-muted" style="margin-top:8px">Verifique se o MySQL está rodando no XAMPP e se as credenciais em <code>config/config.php</code> estão corretas.</p>'
        . '<div class="alert alert--danger" style="margin-top:16px">' . e($erroInstalacao) . '</div>'
        . '<div class="card" style="margin-top:16px;background:#F8FAFC">'
        . '<strong style="font-size:13px">Credenciais usadas</strong>'
        . '<table class="table table--compact" style="margin-top:8px">'
        . '<tr><td>Host</td><td class="mono">' . e($dbCfg['host'] . ':' . $dbCfg['porta']) . '</td></tr>'
        . '<tr><td>Banco</td><td class="mono">' . e($dbCfg['nome']) . '</td></tr>'
        . '<tr><td>Usuário</td><td class="mono">' . e($dbCfg['usuario']) . '</td></tr>'
        . '</table></div>'
        . '<p style="margin-top:16px"><a class="btn btn--primary" href="' . BASE_URL . '/">Tentar novamente</a></p>'
        . '</div></body></html>';
    exit;
}

/* ------------------------------------------------------------------
 *  3. Segurança e roteamento
 * ------------------------------------------------------------------ */
Csrf::verificar();

$router = require APP_PATH . '/../config/routes.php';

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$base = BASE_PATH === '/' ? '' : rtrim(BASE_PATH, '/');
if ($base !== '' && str_starts_with($uri, $base)) {
    $uri = substr($uri, strlen($base));
}

$router->despachar($_SERVER['REQUEST_METHOD'] ?? 'GET', $uri);

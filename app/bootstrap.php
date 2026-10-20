<?php
/**
 * Bootstrap da aplicação: constantes, autoloader, sessão, tratamento de erros.
 */

define('APP_ROOT', dirname(__DIR__));
define('ROOT_PATH', APP_ROOT);
define('APP_PATH', APP_ROOT . '/app');

// Caminho base da aplicação dentro do servidor web (ex.: /Sistema-Gerenciamento-RH)
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/index.php'));
$scriptDir = rtrim($scriptDir, '/');
if ($scriptDir === '' || $scriptDir === '.') {
    $scriptDir = '';
}
define('BASE_URL', $scriptDir);
define('BASE_PATH', $scriptDir === '' ? '/' : $scriptDir . '/');

$config = require APP_PATH . '/../config/config.php';
date_default_timezone_set($config['app']['timezone']);
$GLOBALS['config'] = $config;

/** Autoloader PSR-4 simplificado para o namespace App\ */
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    if (!str_starts_with($class, $prefix)) {
        return;
    }
    $relative = substr($class, strlen($prefix));
    $path = APP_PATH . '/' . str_replace('\\', '/', $relative) . '.php';
    if (is_file($path)) {
        require_once $path;
    }
});

require APP_PATH . '/Core/Helpers.php';

if ($config['app']['debug']) {
    error_reporting(E_ALL);
    ini_set('display_errors', '1');
} else {
    error_reporting(E_ALL & ~E_DEPRECATED & ~E_NOTICE);
    ini_set('display_errors', '0');
}

date_default_timezone_set($config['app']['timezone']);

if (session_status() === PHP_SESSION_NONE) {
    session_name($config['sessao']['nome']);
    session_set_cookie_params([
        'lifetime' => $config['sessao']['tempo'],
        'path'     => BASE_PATH,
        'httponly' => true,
        'secure'   => $config['sessao']['seguro'],
        'samesite' => 'Lax',
    ]);
    session_start();
}

set_exception_handler(function (Throwable $e) {
    error_log('[SGRH] ' . $e->getMessage() . ' em ' . $e->getFile() . ':' . $e->getLine());
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    if (http_response_code() === 200) {
        http_response_code(500);
    }
    if (is_ajax()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => false, 'erro' => 'Erro interno do servidor.']);
        exit;
    }
    $debug = $GLOBALS['config']['app']['debug'];
    $detalhe = $debug
        ? '<pre style="white-space:pre-wrap;text-align:left;background:#0F172A;color:#F8FAFC;padding:16px;border-radius:12px;overflow:auto;font-size:12px">'
            . htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString()) . '</pre>'
        : '<p>Tente novamente ou contate o administrador.</p>';
    echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><title>Erro</title>'
        . '<style>body{font-family:system-ui,sans-serif;background:#F6F7FB;color:#0F172A;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:24px}'
        . '.box{background:#fff;border:1px solid #E2E8F0;border-radius:14px;padding:32px;max-width:760px;box-shadow:0 12px 32px rgba(15,23,42,.12)}'
        . 'h1{font-size:20px;margin:0 0 8px}p{color:#475569;font-size:14px;line-height:1.6}</style></head><body>'
        . '<div class="box"><h1>Ops, algo deu errado</h1>' . $detalhe
        . '<p><a href="' . BASE_URL . '/" style="color:#4F46E5;font-weight:600">Voltar ao início</a></p></div></body></html>';
    exit;
});

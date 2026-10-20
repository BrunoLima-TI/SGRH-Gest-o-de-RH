<?php

namespace App\Core;

/**
 * Proteção CSRF por token de sessão.
 */
class Csrf
{
    private const CHAVE = '_csrf_token';

    public static function token(): string
    {
        if (empty($_SESSION[self::CHAVE])) {
            $_SESSION[self::CHAVE] = bin2hex(random_bytes(32));
        }
        return $_SESSION[self::CHAVE];
    }

    public static function campo(): string
    {
        return '<input type="hidden" name="_token" value="' . e(self::token()) . '">';
    }

    public static function valido(?string $token): bool
    {
        return !empty($_SESSION[self::CHAVE])
            && is_string($token)
            && hash_equals($_SESSION[self::CHAVE], $token);
    }

    public static function verificar(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }
        if (!self::valido($_POST['_token'] ?? null)) {
            http_response_code(403);
            $erro = 'Sessão expirada ou requisição inválida (token CSRF). Recarregue a página e tente novamente.';
            if (is_ajax()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['ok' => false, 'erro' => $erro]);
                exit;
            }
            echo '<!doctype html><meta charset="utf-8"><title>Sessão expirada</title>'
                . '<div style="font-family:system-ui;padding:40px"><h1>403 — Sessão expirada</h1>'
                . '<p>' . e($erro) . '</p><p><a href="' . BASE_URL . '/">Voltar</a></p></div>';
            exit;
        }
    }
}

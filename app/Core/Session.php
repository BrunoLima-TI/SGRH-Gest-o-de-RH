<?php

namespace App\Core;

/**
 * Sessão com suporte a flash messages.
 */
class Session
{
    public static function iniciar(array $dados = []): void
    {
        if (!isset($_SESSION)) {
            session_start();
        }
        foreach ($dados as $chave => $valor) {
            $_SESSION[$chave] = $valor;
        }
    }

    public static function get(string $chave, $padrao = null)
    {
        return $_SESSION[$chave] ?? $padrao;
    }

    public static function set(string $chave, $valor): void
    {
        $_SESSION[$chave] = $valor;
    }

    public static function tem(string $chave): bool
    {
        return isset($_SESSION[$chave]);
    }

    public static function remover(string $chave): void
    {
        unset($_SESSION[$chave]);
    }

    public static function flash(string $chave, $valor = null)
    {
        if ($valor !== null) {
            $_SESSION['_flash'][$chave] = $valor;
            return null;
        }
        $v = $_SESSION['_flash'][$chave] ?? null;
        unset($_SESSION['_flash'][$chave]);
        return $v;
    }

    /** Guarda todos os pares dotados com ponto. */
    public static function dot(string $prefixo, array $dados): void
    {
        foreach ($dados as $chave => $valor) {
            $_SESSION[$prefixo . '.' . $chave] = $valor;
        }
    }

    public static function limparPrefixo(string $prefixo): void
    {
        foreach (array_keys($_SESSION) as $chave) {
            if (str_starts_with($chave, $prefixo . '.')) {
                unset($_SESSION[$chave]);
            }
        }
    }

    public static function encerrar(): void
    {
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], (bool) $p['secure'], (bool) $p['httponly']);
        }
        session_destroy();
    }
}

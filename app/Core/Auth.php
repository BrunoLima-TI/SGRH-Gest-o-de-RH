<?php

namespace App\Core;

/**
 * Autenticação e autorização (perfis: admin, rh, gestor).
 */
class Auth
{
    public const PERFIS = [
        'admin'  => 'Administrador',
        'rh'     => 'Recursos Humanos',
        'gestor' => 'Gestor',
        'colaborador' => 'Colaborador',
    ];

    public static function usuario(): ?array
    {
        return $_SESSION['usuario'] ?? null;
    }

    public static function id(): ?int
    {
        return self::usuario()['id'] ?? null;
    }

    public static function nome(): string
    {
        return self::usuario()['nome'] ?? 'Visitante';
    }

    public static function perfil(): string
    {
        return self::usuario()['perfil'] ?? 'colaborador';
    }

    public static function logado(): bool
    {
        return self::usuario() !== null;
    }

    public static function entrar(int $usuarioId, string $perfil): void
    {
        Session::set('usuario_id', $usuarioId);
        Session::set('perfil', $perfil);
    }

    public static function sair(): void
    {
        Session::encerrar();
    }

    /** Verifica se o usuário logado tem um dos perfis permitidos. */
    public static function pode(string ...$perfis): bool
    {
        if (!self::logado()) {
            return false;
        }
        return in_array(self::perfil(), $perfis, true);
    }

    public static function ehAdmin(): bool
    {
        return self::perfil() === 'admin';
    }

    public static function ehRh(): bool
    {
        return in_array(self::perfil(), ['admin', 'rh'], true);
    }
}

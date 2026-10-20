<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Camada de conexão com o banco de dados (PDO/MySQL).
 */
class Database
{
    private static ?PDO $conexao = null;
    private static array $config = [];

    public static function config(array $config): void
    {
        self::$config = $config;
    }

    public static function conexao(): PDO
    {
        if (self::$conexao instanceof PDO) {
            return self::$conexao;
        }

        $cfg = self::$config;
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $cfg['host'],
            $cfg['porta'],
            $cfg['nome'],
            $cfg['charset']
        );

        try {
            self::$conexao = new PDO($dsn, $cfg['usuario'], $cfg['senha'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
                PDO::ATTR_STRINGIFY_FETCHES  => false,
            ]);
        } catch (PDOException $e) {
            throw new \RuntimeException(
                'Não foi possível conectar ao banco de dados. Verifique o MySQL do XAMPP e as credenciais em config/config.php. Detalhe: ' . $e->getMessage(),
                0,
                $e
            );
        }

        return self::$conexao;
    }

    /** Conecta a um banco específico (usado pelo instalador). */
    public static function conectar(string $banco = '', array $sobrescrita = []): PDO
    {
        $cfg = array_merge(self::$config, $sobrescrita);
        $dsn = sprintf(
            'mysql:host=%s;port=%d;charset=%s%s',
            $cfg['host'],
            $cfg['porta'],
            $cfg['charset'],
            $banco !== '' ? ';dbname=' . $banco : ''
        );

        return new PDO($dsn, $cfg['usuario'], $cfg['senha'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function desconectar(): void
    {
        self::$conexao = null;
    }

    public static function nomeBanco(): string
    {
        return self::$config['nome'] ?? 'sgrh';
    }
}

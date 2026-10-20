<?php

namespace App\Core;

/**
 * Renderização de views com layout.
 */
class View
{
    private static string $layout = 'layouts/main';
    private static array $compartilhado = [];

    public static function compartilhar(string $chave, $valor): void
    {
        self::$compartilhado[$chave] = $valor;
    }

    public static function renderizar(string $view, array $dados = [], ?string $layout = null): string
    {
        if ($layout !== null) {
            self::$layout = $layout;
        }

        $conteudo = self::capturar($view, $dados);
        return self::capturar(self::$layout, array_merge(self::$compartilhado, $dados, ['conteudo' => $conteudo]));
    }

    private static function capturar(string $view, array $dados): string
    {
        $arquivo = APP_PATH . '/Views/' . str_replace(['..', '\\'], '', $view) . '.php';
        if (!is_file($arquivo)) {
            throw new \RuntimeException("View não encontrada: {$view}");
        }
        extract($dados, EXTR_SKIP);
        ob_start();
        require $arquivo;
        return (string) ob_get_clean();
    }
}

<?php

namespace App\Core;

/**
 * Controller base: renderização, redirecionamento, verificação de acesso.
 */
abstract class Controller
{
    protected string $layout = 'layouts/main';

    public function executar(string $acao, array $params = []): void
    {
        $this->antes();
        $metodo = lcfirst(str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $acao))));
        if (!method_exists($this, $metodo)) {
            throw new \RuntimeException("Ação inválida: {$acao}");
        }
        $this->{$metodo}($params);
    }

    protected function antes(): void
    {
    }

    protected function view(string $view, array $dados = [], ?string $layout = null): void
    {
        echo View::renderizar($view, $dados, $layout ?? $this->layout);
    }

    protected function json(array $dados, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function redirecionar(string $caminho): void
    {
        redirect($caminho);
    }

    protected function voltar(): void
    {
        redirect($_SERVER['HTTP_REFERER'] ?? url('dashboard'));
    }

    /** Exige usuário logado. */
    protected function exigirLogin(): void
    {
        if (!Auth::logado()) {
            Session::flash('erro', 'Faça login para acessar o sistema.');
            redirect('login');
        }
    }

    /** Exige um dos perfis informados. */
    protected function exigirPerfil(string ...$perfis): void
    {
        $this->exigirLogin();
        if (!Auth::pode(...$perfis)) {
            http_response_code(403);
            echo '<!doctype html><meta charset="utf-8"><title>Acesso restrito</title>'
                . '<link rel="stylesheet" href="' . asset('css/app.css') . '">'
                . '<body style="display:flex;align-items:center;justify-content:center;min-height:100vh">'
                . '<div class="card" style="text-align:center;max-width:420px;padding:40px">'
                . '<h1 style="font-size:20px;margin:0 0 8px">Acesso restrito</h1>'
                . '<p style="color:#64748B;font-size:14px">Você não tem permissão para acessar esta área.</p>'
                . '<a class="btn btn--primary" href="' . BASE_URL . '/">Voltar ao painel</a></div></body></html>';
            exit;
        }
    }

    /** Recupera dados validados do POST com regras simples. */
    protected function entrada(array $campos): array
    {
        $dados = [];
        foreach ($campos as $campo) {
            $dados[$campo] = isset($_POST[$campo]) ? trim((string) $_POST[$campo]) : '';
        }
        return $dados;
    }

    protected function paginaAtual(): int
    {
        return max(1, (int) ($_GET['pagina'] ?? 1));
    }

    protected function porPagina(): int
    {
        $n = (int) ($_GET['por_pagina'] ?? 10);
        return in_array($n, [10, 25, 50, 100], true) ? $n : 10;
    }

    /** Redirect pós-form com mensagens flash (PRG). */
    protected function redirecionarCom(string $tipo, string $mensagem, string $caminho, array $dados = []): void
    {
        Session::flash($tipo, $mensagem);
        if ($dados) {
            Session::dot('old', $dados);
        }
        redirect($caminho);
    }
}

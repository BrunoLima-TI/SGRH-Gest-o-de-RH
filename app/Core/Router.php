<?php

namespace App\Core;

/**
 * Roteador com suporte a parâmetros nomeados: /colaboradores/editar/{id}
 */
class Router
{
    private array $rotas = [];

    public function get(string $padrao, string $controlador, string $acao): void
    {
        $this->adicionar('GET', $padrao, $controlador, $acao);
    }

    public function post(string $padrao, string $controlador, string $acao): void
    {
        $this->adicionar('POST', $padrao, $controlador, $acao);
    }

    public function qualquer(string $padrao, string $controlador, string $acao): void
    {
        $this->adicionar('GET', $padrao, $controlador, $acao);
        $this->adicionar('POST', $padrao, $controlador, $acao);
    }

    public function adicionar(string $metodo, string $padrao, string $controlador, string $acao): void
    {
        $this->rotas[] = compact('metodo', 'padrao', 'controlador', 'acao');
    }

    public function despachar(string $metodo, string $caminho): void
    {
        $caminho = '/' . trim($caminho, '/');
        if ($caminho === '/') {
            $caminho = '';
        }

        foreach ($this->rotas as $rota) {
            if ($rota['metodo'] !== $metodo) {
                continue;
            }
            $padrao = '/' . trim($rota['padrao'], '/');
            $params = $this->casar($padrao, $caminho);
            if ($params === null) {
                continue;
            }

            $classe = 'App\\Controllers\\' . $rota['controlador'] . 'Controller';
            if (!class_exists($classe)) {
                throw new \RuntimeException("Controlador não encontrado: {$classe}");
            }
            $controlador = new $classe();
            $controlador->executar($rota['acao'], $params);
            return;
        }

        http_response_code(404);
        $this->naoEncontrado();
    }

    private function casar(string $padrao, string $caminho): ?array
    {
        $p = explode('/', trim($padrao, '/'));
        $c = explode('/', trim($caminho, '/'));
        if ($padrao === '' && $caminho === '') {
            return [];
        }
        if (count($p) !== count($c)) {
            return null;
        }
        $params = [];
        foreach ($p as $i => $parte) {
            if (preg_match('/^\{(\w+)\}$/', $parte, $m)) {
                $params[$m[1]] = urldecode($c[$i]);
                continue;
            }
            if ($parte !== $c[$i]) {
                return null;
            }
        }
        return $params;
    }

    private function naoEncontrado(): void
    {
        if (is_ajax()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['ok' => false, 'erro' => 'Recurso não encontrado.']);
            exit;
        }
        http_response_code(404);
        echo '<!doctype html><html lang="pt-BR"><head><meta charset="utf-8"><title>Página não encontrada</title>'
            . '<link rel="stylesheet" href="' . asset('css/app.css') . '"></head>'
            . '<body style="display:flex;align-items:center;justify-content:center;min-height:100vh">'
            . '<div class="card" style="text-align:center;max-width:420px;padding:40px">'
            . '<div style="font-size:56px;font-weight:700;color:#4F46E5">404</div>'
            . '<h1 style="font-size:20px;margin:8px 0">Página não encontrada</h1>'
            . '<p style="color:#64748B;font-size:14px">O endereço acessado não existe no SGRH.</p>'
            . '<a class="btn btn--primary" href="' . BASE_URL . '/">Ir para o painel</a></div></body></html>';
        exit;
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\AvaliacaoDesempenho;
use App\Models\Colaborador;
use App\Models\Usuario;

class DesempenhoController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $modelo = new AvaliacaoDesempenho();
        $filtros = [
            'ciclo'         => (string) ($_GET['ciclo'] ?? ''),
            'status'        => (string) ($_GET['status'] ?? ''),
            'colaborador_id'=> (string) ($_GET['colaborador_id'] ?? ''),
        ];

        $this->view('desempenho/index', [
            'titulo'      => 'Avaliação de desempenho',
            'avaliacoes'  => $modelo->listarComFiltros($filtros),
            'filtros'     => $filtros,
            'ciclos'      => $modelo->ciclos(),
            'mediaGeral'  => $modelo->mediaGeral(),
            'porCriterio' => $modelo->mediaPorCriterio(),
            'distribuicao'=> $modelo->distribuicao(),
            'colaboradores'=> (new Colaborador())->ativos(),
        ]);
    }

    public function form(array $params = []): void
    {
        $this->exigirPerfil('admin', 'rh', 'gestor');
        $id = (int) ($params['id'] ?? 0);
        $modelo = new AvaliacaoDesempenho();
        $avaliacao = $id ? $modelo->buscar($id) : null;

        $this->view('desempenho/form', [
            'titulo'       => $id ? 'Editar avaliação' : 'Nova avaliação',
            'avaliacao'    => $avaliacao,
            'colaboradores'=> (new Colaborador())->ativos(),
            'avaliadores'  => (new Usuario())->todos('nome ASC'),
            'action'       => $id ? url('desempenho/' . $id . '/salvar') : url('desempenho/salvar'),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPerfil('admin', 'rh', 'gestor');
        $id = (int) ($_POST['id'] ?? 0);

        $dados = $this->entrada(['colaborador_id', 'ciclo', 'avaliador_id', 'data_avaliacao', 'comentarios', 'status']);
        $dados['colaborador_id'] = (int) $dados['colaborador_id'];
        $dados['avaliador_id'] = $dados['avaliador_id'] ?: (\App\Core\Auth::id() ?: null);
        $dados['data_avaliacao'] = $dados['data_avaliacao'] ?: date('Y-m-d');
        $dados['status'] = $dados['status'] ?: 'pendente';

        $criterios = [];
        foreach (($_POST['criterios'] ?? []) as $crit => $nota) {
            if ($nota !== '' && is_numeric($nota)) {
                $criterios[$crit] = (float) $nota;
            }
        }

        $v = Validator::criar($dados);
        $v->obrigatorio('colaborador_id', 'colaborador')
          ->obrigatorio('ciclo', 'ciclo')
          ->data('data_avaliacao', 'data da avaliação');

        if (!$criterios) {
            $v->adicionarErro('criterios', 'Avalie ao menos um critério.');
        }

        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            redirect($id ? 'desempenho/' . $id . '/editar' : 'desempenho/nova');
        }

        $dados['criterios'] = json_encode($criterios, JSON_UNESCAPED_UNICODE);
        $dados['nota_geral'] = round(array_sum($criterios) / max(1, count($criterios)), 2);

        $modelo = new AvaliacaoDesempenho();
        if ($id) {
            $modelo->atualizar($id, $dados);
            Session::flash('sucesso', 'Avaliação atualizada.');
            redirect('desempenho');
        }
        $modelo->inserir($dados);
        Session::flash('sucesso', 'Avaliação registrada com sucesso.');
        redirect('desempenho');
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        (new AvaliacaoDesempenho())->excluir((int) ($_POST['id'] ?? 0));
        Session::flash('sucesso', 'Avaliação removida.');
        redirect('desempenho');
    }
}

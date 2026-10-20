<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Ausencia;
use App\Models\Colaborador;

class AusenciaController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $modelo = new Ausencia();
        $filtros = [
            'status' => (string) ($_GET['status'] ?? ''),
            'tipo'   => (string) ($_GET['tipo'] ?? ''),
            'ano'    => (string) ($_GET['ano'] ?? date('Y')),
        ];

        $this->view('ferias/index', [
            'titulo'   => 'Férias e ausências',
            'ausencias'=> $modelo->listarComFiltros($filtros),
            'filtros'  => $filtros,
            'saldo'    => $modelo->saldoFerias(),
            'pendentes'=> $modelo->pendentes(),
        ]);
    }

    public function form(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $this->view('ferias/form', [
            'titulo'       => 'Registrar ausência',
            'colaboradores'=> (new Colaborador())->ativos(),
            'action'       => url('ferias/salvar'),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPerfil('admin', 'rh');

        $dados = $this->entrada(['colaborador_id', 'tipo', 'data_inicio', 'data_fim', 'justificativa']);
        $dados['colaborador_id'] = (int) $dados['colaborador_id'];

        $v = Validator::criar($dados);
        $v->obrigatorio('colaborador_id', 'colaborador')
          ->obrigatorio('data_inicio', 'data de início')
          ->obrigatorio('data_fim', 'data de fim')
          ->data('data_inicio', 'data de início')
          ->data('data_fim', 'data de fim');

        if ($dados['data_inicio'] && $dados['data_fim'] && strtotime($dados['data_fim']) < strtotime($dados['data_inicio'])) {
            $v->adicionarErro('data_fim', 'A data final deve ser igual ou posterior à inicial.');
        }

        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            redirect('ferias/nova');
        }

        $modelo = new Ausencia();
        if (!$modelo->validarPeriodo($dados['colaborador_id'], $dados['data_inicio'], $dados['data_fim'])) {
            Session::set('errors', ['data_inicio' => 'Este colaborador já possui um registro de ausência neste período.']);
            Session::dot('old', $dados);
            redirect('ferias/nova');
        }

        $dias = (int) round((strtotime($dados['data_fim']) - strtotime($dados['data_inicio'])) / 86400) + 1;
        $dados['dias'] = max(1, $dias);
        $dados['status'] = 'pendente';

        $modelo->inserir($dados);
        Session::flash('sucesso', 'Ausência registrada e aguardando aprovação.');
        redirect('ferias');
    }

    public function decidir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($_POST['id'] ?? 0);
        $decisao = (string) ($_POST['decisao'] ?? '');

        if (!in_array($decisao, ['aprovado', 'reprovado'], true)) {
            Session::flash('erro', 'Decisão inválida.');
            $this->voltar();
        }

        (new Ausencia())->atualizar($id, ['status' => $decisao]);
        Session::flash('sucesso', 'Solicitação ' . $decisao . '.');
        redirect('ferias');
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        (new Ausencia())->excluir((int) ($_POST['id'] ?? 0));
        Session::flash('sucesso', 'Registro removido.');
        redirect('ferias');
    }
}

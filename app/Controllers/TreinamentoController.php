<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Colaborador;
use App\Models\Treinamento;

class TreinamentoController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $modelo = new Treinamento();

        $this->view('treinamentos/index', [
            'titulo'       => 'Treinamentos',
            'treinamentos' => $modelo->listarComInscricoes(),
            'inscricoes'   => $modelo->inscricoes(),
            'indicadores'  => $modelo->indicadores(),
            'categorias'   => $modelo->categorias(),
        ]);
    }

    public function form(array $params = []): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($params['id'] ?? 0);
        $modelo = new Treinamento();
        $treinamento = $id ? $modelo->buscar($id) : null;

        $this->view('treinamentos/form', [
            'titulo'       => $id ? 'Editar treinamento' : 'Novo treinamento',
            'treinamento'  => $treinamento,
            'categorias'   => $modelo->categorias(),
            'action'       => $id ? url('treinamentos/' . $id . '/salvar') : url('treinamentos/salvar'),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($_POST['id'] ?? 0);

        $dados = $this->entrada([
            'titulo', 'descricao', 'categoria', 'carga_horaria', 'data_inicio', 'data_fim', 'instrutor', 'vagas', 'custo',
        ]);
        $dados['carga_horaria'] = numero($dados['carga_horaria'] ?? 0);
        $dados['custo'] = numero($dados['custo'] ?? 0);
        $dados['vagas'] = max(1, (int) ($dados['vagas'] ?: 1));
        $dados['categoria'] = $dados['categoria'] ?: 'Geral';
        $dados['data_inicio'] = $dados['data_inicio'] ?: null;
        $dados['data_fim'] = $dados['data_fim'] ?: null;

        $v = Validator::criar($dados);
        $v->obrigatorio('titulo', 'título')->minimo('titulo', 3, 'título')
          ->data('data_inicio', 'data de início')->data('data_fim', 'data de fim');

        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            redirect($id ? 'treinamentos/' . $id . '/editar' : 'treinamentos/novo');
        }

        $modelo = new Treinamento();
        if ($id) {
            $modelo->atualizar($id, $dados);
            Session::flash('sucesso', 'Treinamento atualizado.');
            redirect('treinamentos');
        }
        $modelo->inserir($dados);
        Session::flash('sucesso', 'Treinamento criado com sucesso.');
        redirect('treinamentos');
    }

    public function inscricao(): void
    {
        $this->exigirPerfil('admin', 'rh', 'gestor');
        $treinamentoId = (int) ($_POST['treinamento_id'] ?? 0);
        $colaboradorId = (int) ($_POST['colaborador_id'] ?? 0);

        if (!$treinamentoId || !$colaboradorId) {
            Session::flash('erro', 'Selecione treinamento e colaborador.');
            $this->voltar();
        }

        $inscricoes = new \App\Models\InscricaoTreinamento();
        $existe = $inscricoes->valor(
            'SELECT id FROM inscricoes_treinamento WHERE treinamento_id = ? AND colaborador_id = ?',
            [$treinamentoId, $colaboradorId]
        );

        if ($existe) {
            $inscricoes->atualizar((int) $existe, [
                'status'         => 'inscrito',
                'progresso'      => 0,
                'data_conclusao' => null,
                'nota'           => null,
            ]);
            Session::flash('sucesso', 'Inscrição atualizada.');
        } else {
            $inscricoes->inserir([
                'treinamento_id' => $treinamentoId,
                'colaborador_id' => $colaboradorId,
                'status'         => 'inscrito',
                'progresso'      => 0,
                'data_inscricao' => date('Y-m-d'),
            ]);
            Session::flash('sucesso', 'Colaborador inscrito com sucesso.');
        }

        $this->voltar();
    }

    public function atualizarInscricao(): void
    {
        $this->exigirPerfil('admin', 'rh', 'gestor');
        $id = (int) ($_POST['id'] ?? 0);
        $status = (string) ($_POST['status'] ?? 'inscrito');
        $progresso = max(0, min(100, (int) ($_POST['progresso'] ?? 0)));
        $nota = $status === 'concluido' ? (float) ($_POST['nota'] ?? 0) : null;

        (new \App\Models\InscricaoTreinamento())->atualizar($id, [
            'status'         => $status,
            'progresso'      => $progresso,
            'nota'           => $nota,
            'data_conclusao' => $status === 'concluido' ? date('Y-m-d') : null,
        ]);

        Session::flash('sucesso', 'Progresso da inscrição atualizado.');
        $this->voltar();
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        (new Treinamento())->excluir((int) ($_POST['id'] ?? 0));
        Session::flash('sucesso', 'Treinamento removido.');
        redirect('treinamentos');
    }
}

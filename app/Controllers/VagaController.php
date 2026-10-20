<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Candidato;
use App\Models\Departamento;
use App\Models\Usuario;
use App\Models\Vaga;

class VagaController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $modelo = new Vaga();
        $filtros = [
            'busca'  => trim((string) ($_GET['busca'] ?? '')),
            'status' => (string) ($_GET['status'] ?? ''),
        ];

        $this->view('vagas/index', [
            'titulo'   => 'Recrutamento',
            'vagas'    => $modelo->listarComFiltros($filtros),
            'filtros'  => $filtros,
            'pipeline' => $modelo->pipeline(),
            'totalPorStatus' => $modelo->totalPorStatus(),
        ]);
    }

    public function form(array $params = []): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($params['id'] ?? 0);
        $modelo = new Vaga();
        $vaga = $id ? $modelo->completa($id) : null;

        $this->view('vagas/form', [
            'titulo'       => $id ? 'Editar vaga' : 'Nova vaga',
            'vaga'         => $vaga,
            'departamentos'=> (new Departamento())->todos('nome ASC'),
            'responsaveis' => (new Usuario())->todos('nome ASC'),
            'action'       => $id ? url('vagas/' . $id . '/salvar') : url('vagas/salvar'),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($_POST['id'] ?? 0);

        $dados = $this->entrada([
            'titulo', 'departamento_id', 'tipo_contrato', 'descricao', 'requisitos',
            'quantidade', 'status', 'data_abertura', 'data_fechamento', 'responsavel_id',
        ]);
        $dados['quantidade'] = max(1, (int) ($dados['quantidade'] ?: 1));
        $dados['departamento_id'] = $dados['departamento_id'] ?: null;
        $dados['responsavel_id'] = $dados['responsavel_id'] ?: null;
        $dados['data_fechamento'] = $dados['data_fechamento'] ?: null;

        $v = Validator::criar($dados);
        $v->obrigatorio('titulo', 'título da vaga')
          ->minimo('titulo', 3, 'título')
          ->obrigatorio('data_abertura', 'data de abertura')
          ->data('data_abbertura', 'data de abertura');

        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            redirect($id ? 'vagas/' . $id . '/editar' : 'vagas/nova');
        }

        $modelo = new Vaga();
        if ($id) {
            $modelo->atualizar($id, $dados);
            Session::flash('sucesso', 'Vaga atualizada com sucesso.');
            redirect('vagas');
        }
        $modelo->inserir($dados);
        Session::flash('sucesso', 'Vaga criada com sucesso.');
        redirect('vagas');
    }

    public function ver(array $params): void
    {
        $this->exigirLogin();
        $id = (int) ($params['id'] ?? 0);
        $modelo = new Vaga();
        $vaga = $modelo->completa($id);
        if (!$vaga) {
            Session::flash('erro', 'Vaga não encontrada.');
            redirect('vagas');
        }

        $this->view('vagas/ver', [
            'titulo'    => $vaga['titulo'],
            'vaga'      => $vaga,
            'candidatos'=> (new Candidato())->porVaga($id),
        ]);
    }

    public function candidato(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $vagaId = (int) ($_POST['vaga_id'] ?? 0);
        $dados = $this->entrada(['nome', 'email', 'telefone', 'experiencia', 'observacoes']);
        $dados['vaga_id'] = $vagaId;
        $dados['etapa'] = 'candidatura';
        $dados['status'] = 'ativo';
        $dados['data_candidatura'] = date('Y-m-d');

        $v = Validator::criar($dados);
        $v->obrigatorio('nome', 'nome do candidato')->email('email');
        if ($v->falha()) {
            Session::flash('erro', implode(' ', $v->erros()));
            $this->voltar();
        }

        (new Candidato())->inserir($dados);
        Session::flash('sucesso', 'Candidato adicionado ao processo.');
        $this->voltar();
    }

    public function etapa(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($_POST['id'] ?? 0);
        $etapa = (string) ($_POST['etapa'] ?? '');
        $permitidas = ['candidatura', 'analise', 'entrevista', 'ofertado', 'contratado', 'descartado'];
        if (!in_array($etapa, $permitidas, true)) {
            Session::flash('erro', 'Etapa inválida.');
            $this->voltar();
        }
        (new Candidato())->atualizar($id, ['etapa' => $etapa]);
        Session::flash('sucesso', 'Candidato movido para a etapa selecionada.');
        redirect('vagas');
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        (new Vaga())->excluir((int) ($_POST['id'] ?? 0));
        Session::flash('sucesso', 'Vaga removida.');
        redirect('vagas');
    }
}

<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Cargo;
use App\Models\Departamento;

class EstruturaController extends Controller
{
    public function index(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $this->view('estrutura/index', [
            'titulo'        => 'Departamentos e cargos',
            'departamentos' => (new Departamento())->comContagem(),
            'cargos'        => (new Cargo())->comDepartamento(),
        ]);
    }

    public function departamento(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $dados = $this->entrada(['nome', 'descricao']);
        $modelo = new Departamento();
        $v = Validator::criar($dados);
        $v->obrigatorio('nome', 'nome do departamento')
          ->minimo('nome', 3, 'nome')
          ->unico('nome', fn($nome) => $modelo->nomeEmUso($nome), 'departamento');
        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            $this->voltar();
        }
        $dados['descricao'] = $dados['descricao'] ?: null;
        $modelo->inserir($dados);
        Session::flash('sucesso', 'Departamento criado.');
        redirect('estrutura');
    }

    public function cargo(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $dados = $this->entrada(['titulo', 'departamento_id', 'descricao']);
        $dados['departamento_id'] = $dados['departamento_id'] ?: null;
        $v = Validator::criar($dados);
        $v->obrigatorio('titulo', 'título do cargo')->minimo('titulo', 3, 'título');
        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            $this->voltar();
        }
        $dados['descricao'] = $dados['descricao'] ?: null;
        (new Cargo())->inserir($dados);
        Session::flash('sucesso', 'Cargo criado.');
        redirect('estrutura');
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $tabela = (string) ($_POST['tabela'] ?? '');
        $id = (int) ($_POST['id'] ?? 0);
        if ($tabela === 'departamentos') {
            (new Departamento())->excluir($id);
        } elseif ($tabela === 'cargos') {
            (new Cargo())->excluir($id);
        } else {
            Session::flash('erro', 'Registro inválido.');
        }
        Session::flash('sucesso', 'Registro removido.');
        redirect('estrutura');
    }
}

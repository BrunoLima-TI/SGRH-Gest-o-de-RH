<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Usuario;

class UsuarioController extends Controller
{
    public function index(): void
    {
        $this->exigirPerfil('admin');
        $this->view('usuarios/index', [
            'titulo'   => 'Usuários do sistema',
            'usuarios' => (new Usuario())->comContagem(),
            'perfis'   => Auth::PERFIS,
        ]);
    }

    public function form(array $params = []): void
    {
        $this->exigirPerfil('admin');
        $id = (int) ($params['id'] ?? 0);
        $modelo = new Usuario();
        $usuario = $id ? $modelo->buscar($id) : null;

        $this->view('usuarios/form', [
            'titulo'  => $id ? 'Editar usuário' : 'Novo usuário',
            'usuario' => $usuario,
            'perfis'  => Auth::PERFIS,
            'action'  => $id ? url('usuarios/' . $id . '/salvar') : url('usuarios/salvar'),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPerfil('admin');
        $id = (int) ($_POST['id'] ?? 0);
        $modelo = new Usuario();

        $dados = $this->entrada(['nome', 'email', 'perfil', 'cargo', 'status', 'senha', 'confirmar_senha']);
        $v = Validator::criar($dados);
        $v->obrigatorio('nome', 'nome')
          ->minimo('nome', 3, 'nome')
          ->obrigatorio('email', 'e-mail')
          ->email('email')
          ->unico('email', fn($e) => $modelo->emailEmUso($e, $id ?: null), 'e-mail');

        if (!$id) {
            $v->obrigatorio('senha', 'senha')->minimo('senha', 6, 'senha')
              ->igual('confirmar_senha', 'senha', 'senha');
        } elseif ($dados['senha'] !== '') {
            $v->minimo('senha', 6, 'senha')->igual('confirmar_senha', 'senha', 'senha');
        }

        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            redirect($id ? 'usuarios/' . $id . '/editar' : 'usuarios/novo');
        }

        unset($dados['confirmar_senha']);
        if ($dados['senha'] === '') {
            unset($dados['senha']);
        } else {
            $dados['senha'] = password_hash($dados['senha'], PASSWORD_DEFAULT);
        }
        $dados['cargo'] = $dados['cargo'] ?: null;

        if ($id) {
            $modelo->atualizar($id, $dados);
            Session::flash('sucesso', 'Usuário atualizado com sucesso.');
            redirect('usuarios');
        }

        $modelo->inserir($dados);
        Session::flash('sucesso', 'Usuário criado com sucesso.');
        redirect('usuarios');
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin');
        $id = (int) ($_POST['id'] ?? 0);
        if ($id === Auth::id()) {
            Session::flash('erro', 'Você não pode excluir o próprio usuário.');
            redirect('usuarios');
        }
        (new Usuario())->excluir($id);
        Session::flash('sucesso', 'Usuário removido.');
        redirect('usuarios');
    }
}

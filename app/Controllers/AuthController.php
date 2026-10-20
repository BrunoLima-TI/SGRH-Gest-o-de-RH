<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Usuario;

class AuthController extends Controller
{
    protected string $layout = 'layouts/auth';

    public function index(): void
    {
        if (Auth::logado()) {
            redirect('dashboard');
        }
        $this->view('auth/login', ['titulo' => 'Entrar'], 'layouts/auth');
    }

    public function entrar(): void
    {
        $email = trim((string) ($_POST['email'] ?? ''));
        $senha = (string) ($_POST['senha'] ?? '');
        $lembrar = !empty($_POST['lembrar']);

        $erros = [];
        $v = Validator::criar(['email' => $email, 'senha' => $senha]);
        $v->obrigatorio('email', 'e-mail')->email('email')->obrigatorio('senha', 'senha');
        if ($v->falha()) {
            $erros = $v->erros();
        } else {
            $modelo = new Usuario();
            $usuario = $modelo->autenticar($email, $senha);
            if (!$usuario) {
                $erros['email'] = 'E-mail ou senha incorretos.';
            } else {
                $_SESSION['usuario'] = $usuario;
                Session::set('usuario_id', $usuario['id']);
                Session::set('perfil', $usuario['perfil']);
                if ($lembrar) {
                    setcookie('sgrh_email', $email, time() + 60 * 60 * 24 * 30, BASE_PATH);
                }
                Session::flash('sucesso', 'Bem-vindo(a) de volta, ' . $usuario['nome'] . '!');
                redirect('dashboard');
            }
        }

        Session::set('errors', $erros);
        Session::dot('old', ['email' => $email]);
        redirect('login');
    }

    public function sair(): void
    {
        Auth::sair();
        redirect('login');
    }
}

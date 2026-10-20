<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Cargo;
use App\Models\Colaborador;
use App\Models\Departamento;

class ColaboradorController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $modelo = new Colaborador();
        $filtros = [
            'busca'           => trim((string) ($_GET['busca'] ?? '')),
            'status'          => (string) ($_GET['status'] ?? ''),
            'departamento_id' => (string) ($_GET['departamento_id'] ?? ''),
            'tipo_contrato'   => (string) ($_GET['tipo_contrato'] ?? ''),
        ];

        $porPagina = $this->porPagina();
        $pagina = $this->paginaAtual();
        $total = $modelo->contarListagem($filtros);
        $lista = $modelo->listarComFiltros($filtros, $porPagina, ($pagina - 1) * $porPagina);

        $this->view('colaboradores/index', [
            'titulo'      => 'Colaboradores',
            'colaboradores'=> $lista,
            'filtros'     => $filtros,
            'departamentos'=> (new Departamento())->comContagem(),
            'pagina'      => paginacao($total, $pagina, $porPagina),
        ]);
    }

    public function ver(array $params): void
    {
        $this->exigirLogin();
        $id = (int) ($params['id'] ?? 0);
        $modelo = new Colaborador();
        $colaborador = $modelo->completo($id);
        if (!$colaborador) {
            Session::flash('erro', 'Colaborador não encontrado.');
            redirect('colaboradores');
        }

        $frequencias = array_values(array_filter(
            (new \App\Models\Frequencia())->doMes((int) date('Y'), (int) date('n')),
            fn($f) => (int) $f['colaborador_id'] === $id
        ));
        $ausencias = array_values(array_filter(
            (new \App\Models\Ausencia())->listarComFiltros([]),
            fn($a) => (int) $a['colaborador_id'] === $id
        ));

        $this->view('colaboradores/ver', [
            'titulo'       => $colaborador['nome'],
            'colaborador'  => $colaborador,
            'frequencias'  => $frequencias,
            'ausencias'    => $ausencias,
            'treinamentos' => (new \App\Models\Treinamento())->inscricoes($id),
            'avaliacoes'   => (new \App\Models\AvaliacaoDesempenho())->porColaborador($id),
            'documentos'   => (new \App\Models\Documento())->porColaborador($id),
        ]);
    }

    public function form(array $params = []): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($params['id'] ?? 0);
        $modelo = new Colaborador();
        $colaborador = $id ? $modelo->completo($id) : null;

        $this->view('colaboradores/form', [
            'titulo'       => $id ? 'Editar colaborador' : 'Novo colaborador',
            'colaborador'  => $colaborador,
            'departamentos'=> (new Departamento())->todos('nome ASC'),
            'cargos'       => (new Cargo())->comDepartamento(),
            'action'       => $id ? url('colaboradores/' . $id . '/salvar') : url('colaboradores/salvar'),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($_POST['id'] ?? 0);
        $modelo = new Colaborador();

        $dados = $this->entrada([
            'nome', 'email', 'cpf', 'telefone', 'data_nascimento', 'data_admissao',
            'departamento_id', 'cargo_id', 'tipo_contrato', 'salario', 'status',
            'endereco', 'estado_civil', 'nivel_educacao', 'observacoes',
        ]);

        $cpf = preg_replace('/\D/', '', $dados['cpf'] ?? '') ?? '';
        $dados['cpf'] = $cpf !== '' ? $cpf : null;

        $v = Validator::criar($dados);
        $v->obrigatorio('nome', 'nome')
          ->minimo('nome', 3, 'nome')
          ->email('email')
          ->cpf('cpf')
          ->dataValida('data_nascimento', 'data de nascimento')
          ->data('data_admissao', 'data de admissão')
          ->unico('cpf', fn($cpf) => $modelo->cpfEmUso($cpf, $id ?: null), 'CPF');

        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            redirect($id ? 'colaboradores/' . $id . '/editar' : 'colaboradores/novo');
        }

        $dados['salario'] = numero($dados['salario'] ?? '0');
        $dados['departamento_id'] = $dados['departamento_id'] ?: null;
        $dados['cargo_id'] = $dados['cargo_id'] ?: null;
        foreach (['data_nascimento', 'data_admissao'] as $campo) {
            $dados[$campo] = $dados[$campo] ?: null;
        }
        foreach (['email', 'telefone', 'endereco', 'estado_civil', 'nivel_educacao', 'observacoes'] as $campo) {
            $dados[$campo] = $dados[$campo] ?: null;
        }

        if ($id) {
            $modelo->atualizar($id, $dados);
            Session::flash('sucesso', 'Colaborador atualizado com sucesso.');
            redirect('colaboradores/' . $id);
        }

        $novoId = $modelo->inserir($dados);
        Session::flash('sucesso', 'Colaborador cadastrado com sucesso.');
        redirect('colaboradores/' . $novoId);
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $id = (int) ($_POST['id'] ?? 0);
        (new Colaborador())->excluir($id);
        Session::flash('sucesso', 'Colaborador removido.');
        redirect('colaboradores');
    }
}

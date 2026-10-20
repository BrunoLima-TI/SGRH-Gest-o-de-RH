<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Core\Validator;
use App\Models\Colaborador;
use App\Models\Documento;

class DocumentoController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $modelo = new Documento();
        $filtros = [
            'tipo'          => (string) ($_GET['tipo'] ?? ''),
            'colaborador_id'=> (string) ($_GET['colaborador_id'] ?? ''),
            'busca'         => trim((string) ($_GET['busca'] ?? '')),
        ];

        $this->view('documentos/index', [
            'titulo'       => 'Documentos',
            'documentos'   => $modelo->listarComFiltros($filtros),
            'filtros'      => $filtros,
            'tipos'        => $modelo->tipos(),
            'colaboradores'=> (new Colaborador())->ativos(),
            'total'        => $modelo->total(),
            'vencendo'     => $modelo->vencendoEm(30),
        ]);
    }

    public function form(): void
    {
        $this->exigirPerfil('admin', 'rh');
        $this->view('documentos/form', [
            'titulo'       => 'Novo documento',
            'colaboradores'=> (new Colaborador())->ativos(),
            'action'       => url('documentos/salvar'),
        ]);
    }

    public function salvar(): void
    {
        $this->exigirPerfil('admin', 'rh');

        $dados = $this->entrada(['colaborador_id', 'nome', 'tipo', 'data_emissao', 'data_validade', 'observacoes']);
        $dados['colaborador_id'] = $dados['colaborador_id'] ?: null;
        $dados['data_emissao'] = $dados['data_emissao'] ?: null;
        $dados['data_validade'] = $dados['data_validade'] ?: null;

        $v = Validator::criar($dados);
        $v->obrigatorio('nome', 'nome do documento')
          ->minimo('nome', 3, 'nome do documento')
          ->data('data_emissao', 'data de emissão')
          ->data('data_validade', 'data de validade');

        if ($v->falha()) {
            Session::set('errors', $v->erros());
            Session::dot('old', $dados);
            redirect('documentos/novo');
        }

        $dados['tipo'] = $dados['tipo'] ?: 'outro';

        (new Documento())->inserir($dados);
        Session::flash('sucesso', 'Documento registrado com sucesso.');
        redirect('documentos');
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh');
        (new Documento())->excluir((int) ($_POST['id'] ?? 0));
        Session::flash('sucesso', 'Documento removido.');
        redirect('documentos');
    }
}

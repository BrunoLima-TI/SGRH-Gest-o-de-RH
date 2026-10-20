<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Session;
use App\Models\Colaborador;
use App\Models\Frequencia;

class FrequenciaController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $ano = (int) ($_GET['ano'] ?? date('Y'));
        $mes = (int) ($_GET['mes'] ?? date('n'));
        if ($mes < 1 || $mes > 12) {
            $mes = (int) date('n');
        }
        $ano = $ano > 2000 && $ano < 2100 ? $ano : (int) date('Y');

        $modelo = new Frequencia();
        $colaboradores = (new Colaborador())->ativos();

        $this->view('frequencia/index', [
            'titulo'        => 'Frequência',
            'ano'           => $ano,
            'mes'           => $mes,
            'colaboradores' => $colaboradores,
            'registros'     => $modelo->doMes($ano, $mes),
            'mapa'          => $modelo->matrizDoMes($ano, $mes),
            'indicadores'   => $modelo->indicadoresDoMes($ano, $mes),
            'diasMes'       => (int) date('t', mktime(0, 0, 0, $mes, 1, $ano)),
        ]);
    }

    public function registrar(): void
    {
        $this->exigirPerfil('admin', 'rh', 'gestor');

        $colaboradorId = (int) ($_POST['colaborador_id'] ?? 0);
        $data = (string) ($_POST['data'] ?? '');
        $dados = $this->entrada(['entrada', 'saida', 'horas_trabalhadas', 'horas_extras', 'status', 'observacoes']);

        if (!$colaboradorId || !$data) {
            Session::flash('erro', 'Selecione o colaborador e a data do registro.');
            redirect('frequencia');
        }

        $entrada = $dados['entrada'] ?: null;
        $saida = $dados['saida'] ?: null;
        $horas = 0.0;
        if ($entrada && $saida) {
            $in = strtotime($entrada);
            $out = strtotime($saida);
            if ($out > $in) {
                $horas = round(($out - $in) / 3600, 2);
            }
        }
        $dados['horas_trabalhadas'] = $dados['horas_trabalhadas'] !== '' ? $dados['horas_trabalhadas'] : $horas;

        (new Frequencia())->registrar($colaboradorId, $data, $dados);
        Session::flash('sucesso', 'Frequência registrada com sucesso.');
        redirect('frequencia?' . http_build_query(['ano' => substr($data, 0, 4), 'mes' => (int) substr($data, 5, 2)]));
    }

    public function excluir(): void
    {
        $this->exigirPerfil('admin', 'rh', 'gestor');
        $id = (int) ($_POST['id'] ?? 0);
        (new Frequencia())->excluir($id);
        Session::flash('sucesso', 'Registro de frequência removido.');
        $this->voltar();
    }
}

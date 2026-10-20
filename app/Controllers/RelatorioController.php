<?php

namespace App\Controllers;

use App\Core\Charts;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Ausencia;
use App\Models\AvaliacaoDesempenho;
use App\Models\Colaborador;
use App\Models\Frequencia;
use App\Models\Treinamento;

class RelatorioController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $filtros = [
            'departamento_id' => (string) ($_GET['departamento_id'] ?? ''),
            'periodo'         => (string) ($_GET['periodo'] ?? '12'),
        ];
        $meses = max(1, min(24, (int) $filtros['periodo']));

        $colaboradores = new Colaborador();
        $frequencia = new Frequencia();
        $ausencia = new Ausencia();
        $avaliacoes = new AvaliacaoDesempenho();
        $treinamentos = new Treinamento();

        $absenteismo = $frequencia->absenteismoPorMes($meses);
        $horas = $frequencia->horasPorMes($meses);
        $admitidos = $colaboradores->admitidosPorMes($meses);
        $desligados = $colaboradores->desligamentosPorMes($meses);
        $porDepartamento = $colaboradores->porDepartamento();
        $porContrato = $colaboradores->porTipoContrato();
        $distribuicao = $avaliacoes->distribuicao();
        $porCriterio = $avaliacoes->mediaPorCriterio();

        $this->view('relatorios/index', [
            'titulo'      => 'Relatórios e indicadores',
            'filtros'     => $filtros,
            'meses'       => $meses,
            'absenteismo' => $absenteismo,
            'horas'       => $horas,
            'admitidos'   => $admitidos,
            'desligados'  => $desligados,
            'porDepartamento' => $porDepartamento,
            'porContrato' => $porContrato,
            'distribuicao'=> $distribuicao,
            'porCriterio' => $porCriterio,
            'indicadoresTreinamento' => $treinamentos->indicadores(),
            'graficoAbsenteismo' => Charts::linha($absenteismo, ['titulo' => 'Taxa de absenteísmo por mês', 'chaves' => ['percentual']]),
            'graficoHoras' => Charts::linha($horas, ['titulo' => 'Horas trabalhadas por mês', 'cor' => Charts::CORES[2], 'chaves' => ['horas']]),
            'graficoDepartamentos' => Charts::barras($porDepartamento, ['titulo' => 'Quadro por departamento']),
            'graficoContrato' => Charts::rosca($porContrato, ['titulo' => 'Distribuição por tipo de contrato', 'legendaCentro' => 'Pessoas']),
            'graficoDesempenho' => Charts::barras($distribuicao, ['titulo' => 'Distribuição das notas de desempenho', 'cor' => Charts::CORES[5]]),
            'graficoCriterios' => Charts::barras($porCriterio, ['titulo' => 'Média por critério de avaliação', 'cor' => Charts::CORES[3]]),
        ]);
    }

    public function exportar(): void
    {
        $this->exigirLogin();

        $tipo = (string) ($_GET['tipo'] ?? 'colaboradores');
        $modelo = new Colaborador();
        $colunas = [];
        $linhas = [];

        switch ($tipo) {
            case 'frequencia':
                $colunas = ['colaborador', 'data', 'entrada', 'saida', 'horas_trabalhadas', 'horas_extras', 'status'];
                $linhas = (new Frequencia())->doMes((int) date('Y'), (int) date('n'));
                break;
            case 'ferias':
                $colunas = ['colaborador', 'departamento', 'tipo', 'data_inicio', 'data_fim', 'dias', 'status'];
                $linhas = (new Ausencia())->listarComFiltros([]);
                break;
            case 'desempenho':
                $colunas = ['colaborador', 'ciclo', 'nota_geral', 'data_avaliacao', 'status'];
                $linhas = (new AvaliacaoDesempenho())->listarComFiltros([]);
                break;
            default:
                $colunas = ['nome', 'email', 'cpf', 'departamento', 'cargo', 'tipo_contrato', 'status', 'data_admissao'];
                $linhas = $modelo->listarComFiltros([]);
        }

        $nomeArquivo = 'sgrh_' . $tipo . '_' . date('Ymd_His') . '.csv';
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');

        $saida = fopen('php://output', 'w');
        fwrite($saida, "\xEF\xBB\xBF");
        fputcsv($saida, $colunas, ';');
        foreach ($linhas as $linha) {
            $valores = [];
            foreach ($colunas as $coluna) {
                $valores[] = $linha[$coluna] ?? '';
            }
            fputcsv($saida, $valores, ';');
        }
        fclose($saida);
        exit;
    }
}

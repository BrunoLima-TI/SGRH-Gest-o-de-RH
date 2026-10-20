<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Charts;
use App\Core\Controller;
use App\Core\Session;
use App\Models\Ausencia;
use App\Models\AvaliacaoDesempenho;
use App\Models\Colaborador;
use App\Models\Documento;
use App\Models\Frequencia;
use App\Models\Treinamento;
use App\Models\Vaga;

class DashboardController extends Controller
{
    public function index(): void
    {
        $this->exigirLogin();

        $colaboradores = new Colaborador();
        $frequencia = new Frequencia();
        $ausencia = new Ausencia();
        $vagas = new Vaga();
        $treinamentos = new Treinamento();
        $avaliacoes = new AvaliacaoDesempenho();
        $documentos = new Documento();

        $porStatus = $colaboradores->totalPorStatus();
        $totalAtivos = $colaboradores->totalAtivos();

        $indicadoresMes = $frequencia->indicadoresDoMes((int) date('Y'), (int) date('n'));
        $registrosMes = max(1, (int) ($indicadoresMes['total'] ?? 0));
        $absenteismo = round((($indicadoresMes['falta'] + $indicadoresMes['ausente']) / $registrosMes) * 100, 1);

        $admitidos = $colaboradores->admitidosPorMes(12);
        $desligados = $colaboradores->desligamentosPorMes(12);
        $saidas = array_sum(array_map(fn($l) => (int) $l['total'], $desligados));
        $entradas = array_sum(array_map(fn($l) => (int) $l['total'], $admitidos));
        $base = max(1, $totalAtivos - $entradas + $saidas);
        $rotatividade = round(($saidas / $base) * 100, 1);

        $absenteismoSerie = $frequencia->absenteismoPorMes(12);

        $this->view('dashboard/index', [
            'titulo'  => 'Painel de indicadores',
            'totalAtivos' => $totalAtivos,
            'porStatus'   => $porStatus,
            'absenteismo' => $absenteismo,
            'rotatividade'=> $rotatividade,
            'vagasAbertas' => (int) ($vagas->totalPorStatus()['aberta'] ?? 0),
            'totalVagas'  => array_sum($vagas->totalPorStatus()),
            'indicadoresMes' => $indicadoresMes,
            'absenteismoSerie' => $absenteismoSerie,
            'graficoAbsenteismo' => Charts::linha($absenteismoSerie, ['titulo' => 'Absenteísmo nos últimos 12 meses', 'chaves' => ['percentual']]),
            'departamentos' => $colaboradores->porDepartamento(),
            'graficoDepartamentos' => Charts::rosca($colaboradores->porDepartamento(), ['titulo' => 'Distribuição por departamento', 'legendaCentro' => 'Pessoas']),
            'graficoDepartamentosBarras' => Charts::barras($colaboradores->porDepartamento(), ['titulo' => 'Pessoas por departamento']),
            'proximasFerias' => $colaboradores->proximasFerias(5),
            'pendenciasAusencia' => $ausencia->pendentes(),
            'treinamentos' => array_slice($treinamentos->listarComInscricoes(), 0, 4),
            'indicadoresTreinamento' => $treinamentos->indicadores(),
            'mediaDesempenho' => $avaliacoes->mediaGeral(),
            'aniversariantes' => $colaboradores->aniversariantesDoMes(),
            'documentosVencendo' => $documentos->vencendoEm(30),
            'admitidosSerie' => $admitidos,
            'sparklineAdmissoes' => Charts::sparkline(array_map(fn($l) => (float) $l['total'], $admitidos)),
            'sparklineAbsenteismo' => Charts::sparkline(array_map(fn($l) => (float) $l['percentual'], $absenteismoSerie), '#DC2626'),
            'sparklineRotatividade' => Charts::sparkline(array_map(fn($l) => (float) $l['total'], $desligados), '#D97706'),
        ]);
    }
}

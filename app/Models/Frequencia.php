<?php

namespace App\Models;

use App\Core\Model;

class Frequencia extends Model
{
    protected string $tabela = 'frequencias';

    public function registrar(int $colaboradorId, string $data, array $dados): void
    {
        $sql = 'INSERT INTO frequencias (colaborador_id, data, entrada, saida, horas_trabalhadas, horas_extras, status, observacoes)
                VALUES (:colaborador_id, :data, :entrada, :saida, :horas_trabalhadas, :horas_extras, :status, :observacoes)
                ON DUPLICATE KEY UPDATE
                    entrada = VALUES(entrada), saida = VALUES(saida),
                    horas_trabalhadas = VALUES(horas_trabalhadas),
                    horas_extras = VALUES(horas_extras), status = VALUES(status),
                    observacoes = VALUES(observacoes)';
        $this->query($sql, [
            'colaborador_id'    => $colaboradorId,
            'data'              => $data,
            'entrada'           => $dados['entrada'] ?: null,
            'saida'             => $dados['saida'] ?: null,
            'horas_trabalhadas' => $dados['horas_trabalhadas'] ?: 0,
            'horas_extras'      => $dados['horas_extras'] ?: 0,
            'status'            => $dados['status'] ?: 'presente',
            'observacoes'       => $dados['observacoes'] ?: null,
        ]);
    }

    public function doDia(string $data): array
    {
        return $this->linhas(
            "SELECT f.*, c.nome AS colaborador, d.nome AS departamento
             FROM frequencias f
             INNER JOIN colaboradores c ON c.id = f.colaborador_id
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             WHERE f.data = ?
             ORDER BY c.nome",
            [$data]
        );
    }

    public function doMes(int $ano, int $mes): array
    {
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        return $this->linhas(
            "SELECT f.*, c.nome AS colaborador, d.nome AS departamento
             FROM frequencias f
             INNER JOIN colaboradores c ON c.id = f.colaborador_id
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             WHERE f.data BETWEEN ? AND ?
             ORDER BY c.nome, f.data",
            [$inicio, $fim]
        );
    }

    public function matrizDoMes(int $ano, int $mes): array
    {
        $mapa = [];
        foreach ($this->doMes($ano, $mes) as $linha) {
            $mapa[$linha['colaborador_id']][$linha['data']] = $linha;
        }
        return $mapa;
    }

    public function indicadoresDoMes(int $ano, int $mes): array
    {
        $inicio = sprintf('%04d-%02d-01', $ano, $mes);
        $fim = date('Y-m-t', strtotime($inicio));
        $linhas = $this->linhas(
            "SELECT status, COUNT(*) AS total, SUM(horas_extras) AS extras
             FROM frequencias WHERE data BETWEEN ? AND ? GROUP BY status",
            [$inicio, $fim]
        );
        $saida = ['presente' => 0, 'falta' => 0, 'atraso' => 0, 'ausente' => 0, 'ferias' => 0, 'meio_periodo' => 0, 'extras' => 0];
        foreach ($linhas as $l) {
            $saida[$l['status']] = (int) $l['total'];
            $saida['extras'] = (float) $l['extras'];
        }
        $saida['total'] = array_sum(array_intersect_key($saida, array_flip(['presente', 'falta', 'atraso', 'ausente', 'ferias', 'meio_periodo'])));
        return $saida;
    }

    public function absenteismoPorMes(int $meses = 12): array
    {
        return $this->linhas(
            "SELECT DATE_FORMAT(data, '%Y-%m') AS mes,
                    DATE_FORMAT(data, '%b/%y') AS rotulo,
                    SUM(status IN ('falta','ausente')) AS faltas,
                    COUNT(*) AS registros,
                    ROUND(SUM(status IN ('falta','ausente')) / NULLIF(COUNT(*),0) * 100, 2) AS percentual
             FROM frequencias
             WHERE data >= DATE_SUB(CURDATE(), INTERVAL {$meses} MONTH)
             GROUP BY mes, rotulo ORDER BY mes ASC"
        );
    }

    public function horasPorMes(int $meses = 12): array
    {
        return $this->linhas(
            "SELECT DATE_FORMAT(data, '%Y-%m') AS mes,
                    DATE_FORMAT(data, '%b/%y') AS rotulo,
                    ROUND(SUM(horas_trabalhadas), 1) AS horas
             FROM frequencias
             WHERE data >= DATE_SUB(CURDATE(), INTERVAL {$meses} MONTH)
             GROUP BY mes, rotulo ORDER BY mes ASC"
        );
    }
}

<?php

namespace App\Models;

use App\Core\Model;

class AvaliacaoDesempenho extends Model
{
    protected string $tabela = 'avaliacoes_desempenho';

    public function listarComFiltros(array $filtros = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filtros['ciclo'])) {
            $where[] = 'a.ciclo = ?';
            $params[] = $filtros['ciclo'];
        }
        if (!empty($filtros['colaborador_id'])) {
            $where[] = 'a.colaborador_id = ?';
            $params[] = (int) $filtros['colaborador_id'];
        }
        if (!empty($filtros['status'])) {
            $where[] = 'a.status = ?';
            $params[] = $filtros['status'];
        }
        $w = implode(' AND ', $where);
        return $this->linhas(
            "SELECT a.*, c.nome AS colaborador, d.nome AS departamento, u.nome AS avaliador
             FROM avaliacoes_desempenho a
             INNER JOIN colaboradores c ON c.id = a.colaborador_id
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             LEFT JOIN usuarios u ON u.id = a.avaliador_id
             WHERE {$w}
             ORDER BY a.data_avaliacao DESC",
            $params
        );
    }

    public function ciclos(): array
    {
        return $this->linhas('SELECT DISTINCT ciclo FROM avaliacoes_desempenho ORDER BY ciclo DESC');
    }

    public function porColaborador(int $colaboradorId): array
    {
        return $this->linhas(
            'SELECT * FROM avaliacoes_desempenho WHERE colaborador_id = ? ORDER BY data_avaliacao DESC',
            [$colaboradorId]
        );
    }

    public function mediaGeral(): float
    {
        return (float) ($this->valor('SELECT AVG(nota_geral) FROM avaliacoes_desempenho') ?? 0);
    }

    public function mediaPorCriterio(): array
    {
        $linhas = $this->linhas('SELECT criterios FROM avaliacoes_desempenho WHERE criterios IS NOT NULL');
        $soma = [];
        $cont = [];
        foreach ($linhas as $l) {
            $dados = json_decode($l['criterios'], true) ?: [];
            foreach ($dados as $crit => $nota) {
                $soma[$crit] = ($soma[$crit] ?? 0) + (float) $nota;
                $cont[$crit] = ($cont[$crit] ?? 0) + 1;
            }
        }
        $saida = [];
        foreach ($soma as $crit => $total) {
            $saida[] = ['rotulo' => $crit, 'total' => round($total / max(1, $cont[$crit]), 2)];
        }
        usort($saida, fn($a, $b) => $b['total'] <=> $a['total']);
        return $saida;
    }

    public function distribuicao(): array
    {
        return $this->linhas(
            'SELECT ROUND(nota_geral) AS nota, COUNT(*) AS total
             FROM avaliacoes_desempenho GROUP BY nota ORDER BY nota ASC'
        );
    }
}

<?php

namespace App\Models;

use App\Core\Model;

class Ausencia extends Model
{
    protected string $tabela = 'ausencias';

    public function listarComFiltros(array $filtros = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filtros['status'])) {
            $where[] = 'a.status = ?';
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['tipo'])) {
            $where[] = 'a.tipo = ?';
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['ano'])) {
            $where[] = 'YEAR(a.data_inicio) = ?';
            $params[] = (int) $filtros['ano'];
        }
        $w = implode(' AND ', $where);
        return $this->linhas(
            "SELECT a.*, c.nome AS colaborador, d.nome AS departamento
             FROM ausencias a
             INNER JOIN colaboradores c ON c.id = a.colaborador_id
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             WHERE {$w}
             ORDER BY a.data_inicio DESC",
            $params
        );
    }

    public function pendentes(): array
    {
        return $this->linhas(
            "SELECT a.*, c.nome AS colaborador, d.nome AS departamento
             FROM ausencias a
             INNER JOIN colaboradores c ON c.id = a.colaborador_id
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             WHERE a.status = 'pendente'
             ORDER BY a.data_inicio ASC"
        );
    }

    public function indicadores(): array
    {
        return $this->linhas(
            "SELECT tipo, status, COUNT(*) AS total, SUM(dias) AS dias
             FROM ausencias GROUP BY tipo, status"
        );
    }

    public function saldoFerias(): array
    {
        $linhas = $this->linhas(
            "SELECT c.id, c.nome,
                    SUM(CASE WHEN a.status = 'aprovado' AND a.tipo = 'ferias' THEN a.dias ELSE 0 END) AS gozadas,
                    SUM(CASE WHEN a.status = 'pendente' AND a.tipo = 'ferias' THEN a.dias ELSE 0 END) AS solicitadas
             FROM colaboradores c
             LEFT JOIN ausencias a ON a.colaborador_id = c.id
             WHERE c.status <> 'inativo'
             GROUP BY c.id, c.nome ORDER BY c.nome"
        );
        foreach ($linhas as &$l) {
            $l['saldo'] = 30 - (int) $l['gozadas'];
        }
        return $linhas;
    }

    public function validarPeriodo(int $colaboradorId, string $inicio, string $fim): bool
    {
        $total = (int) $this->valor(
            'SELECT COUNT(*) FROM ausencias
             WHERE colaborador_id = ? AND status <> "reprovado"
               AND NOT (data_fim < ? OR data_inicio > ?)',
            [$colaboradorId, $inicio, $fim]
        );
        return $total === 0;
    }
}

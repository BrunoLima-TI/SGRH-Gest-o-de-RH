<?php

namespace App\Models;

use App\Core\Model;

class Documento extends Model
{
    protected string $tabela = 'documentos';

    public function listarComFiltros(array $filtros = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filtros['tipo'])) {
            $where[] = 'd.tipo = ?';
            $params[] = $filtros['tipo'];
        }
        if (!empty($filtros['colaborador_id'])) {
            $where[] = 'd.colaborador_id = ?';
            $params[] = (int) $filtros['colaborador_id'];
        }
        if (!empty($filtros['busca'])) {
            $where[] = 'd.nome LIKE ?';
            $params[] = '%' . $filtros['busca'] . '%';
        }
        $w = implode(' AND ', $where);
        return $this->linhas(
            "SELECT d.*, c.nome AS colaborador, dep.nome AS departamento
             FROM documentos d
             LEFT JOIN colaboradores c ON c.id = d.colaborador_id
             LEFT JOIN departamentos dep ON dep.id = c.departamento_id
             WHERE {$w}
             ORDER BY d.data_validade IS NULL, d.data_validade ASC",
            $params
        );
    }

    public function porColaborador(int $colaboradorId): array
    {
        return $this->linhas(
            'SELECT * FROM documentos WHERE colaborador_id = ? ORDER BY data_validade IS NULL, data_validade ASC',
            [$colaboradorId]
        );
    }

    public function tipos(): array
    {
        return $this->linhas('SELECT DISTINCT tipo FROM documentos ORDER BY tipo');
    }

    public function vencendoEm(int $dias = 30): array
    {
        return $this->linhas(
            "SELECT d.*, c.nome AS colaborador
             FROM documentos d
             LEFT JOIN colaboradores c ON c.id = d.colaborador_id
             WHERE d.data_validade IS NOT NULL
               AND d.data_validade BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL {$dias} DAY)
             ORDER BY d.data_validade ASC"
        );
    }

    public function total(): int
    {
        return (int) $this->valor('SELECT COUNT(*) FROM documentos');
    }
}

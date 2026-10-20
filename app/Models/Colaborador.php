<?php

namespace App\Models;

use App\Core\Model;

class Colaborador extends Model
{
    protected string $tabela = 'colaboradores';

    /** Listagem com filtros e paginação. */
    public function listarComFiltros(array $filtros = [], int $limite = 10, int $offset = 0): array
    {
        [$where, $params] = $this->montarFiltros($filtros);
        return $this->linhas(
            "SELECT c.*, d.nome AS departamento, cg.titulo AS cargo
             FROM colaboradores c
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             LEFT JOIN cargos cg ON cg.id = c.cargo_id
             WHERE {$where}
             ORDER BY c.nome ASC
             LIMIT {$limite} OFFSET {$offset}",
            $params
        );
    }

    public function contarListagem(array $filtros = []): int
    {
        [$where, $params] = $this->montarFiltros($filtros);
        return (int) $this->valor("SELECT COUNT(*) FROM colaboradores c WHERE {$where}", $params);
    }

    private function montarFiltros(array $filtros): array
    {
        $where = ['1=1'];
        $params = [];

        if (!empty($filtros['busca'])) {
            $where[] = '(c.nome LIKE ? OR c.email LIKE ? OR c.cpf LIKE ?)';
            $t = '%' . $filtros['busca'] . '%';
            array_push($params, $t, $t, $t);
        }
        if (!empty($filtros['status'])) {
            $where[] = 'c.status = ?';
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['departamento_id'])) {
            $where[] = 'c.departamento_id = ?';
            $params[] = (int) $filtros['departamento_id'];
        }
        if (!empty($filtros['tipo_contrato'])) {
            $where[] = 'c.tipo_contrato = ?';
            $params[] = $filtros['tipo_contrato'];
        }

        return [implode(' AND ', $where), $params];
    }

    public function completo(int $id): ?array
    {
        return $this->primeiraLinha(
            'SELECT c.*, d.nome AS departamento, cg.titulo AS cargo
             FROM colaboradores c
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             LEFT JOIN cargos cg ON cg.id = c.cargo_id
             WHERE c.id = ? LIMIT 1',
            [$id]
        );
    }

    public function porEmail(string $email): ?array
    {
        return $this->primeiraLinha('SELECT * FROM colaboradores WHERE email = ? LIMIT 1', [$email]);
    }

    public function cpfEmUso(string $cpf, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM colaboradores WHERE cpf = ?';
        $params = [$cpf];
        if ($ignorarId) {
            $sql .= ' AND id <> ?';
            $params[] = $ignorarId;
        }
        return (int) $this->valor($sql, $params) > 0;
    }

    public function ativos(): array
    {
        return $this->linhas(
            "SELECT c.*, d.nome AS departamento, cg.titulo AS cargo
             FROM colaboradores c
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             LEFT JOIN cargos cg ON cg.id = c.cargo_id
             WHERE c.status <> 'inativo'
             ORDER BY c.nome"
        );
    }

    public function totalAtivos(): int
    {
        return (int) $this->valor("SELECT COUNT(*) FROM colaboradores WHERE status <> 'inativo'");
    }

    public function totalPorStatus(): array
    {
        $linhas = $this->linhas('SELECT status, COUNT(*) AS total FROM colaboradores GROUP BY status');
        $saida = [];
        foreach ($linhas as $l) {
            $saida[$l['status']] = (int) $l['total'];
        }
        return $saida;
    }

    public function admitidosPorMes(int $meses = 12): array
    {
        return $this->linhas(
            "SELECT DATE_FORMAT(data_admissao, '%Y-%m') AS mes,
                    DATE_FORMAT(data_admissao, '%b/%y') AS rotulo,
                    COUNT(*) AS total
             FROM colaboradores
             WHERE data_admissao IS NOT NULL
               AND data_admissao >= DATE_SUB(CURDATE(), INTERVAL {$meses} MONTH)
             GROUP BY mes, rotulo
             ORDER BY mes ASC"
        );
    }

    public function desligamentosPorMes(int $meses = 12): array
    {
        return $this->linhas(
            "SELECT DATE_FORMAT(data_demissao, '%Y-%m') AS mes,
                    DATE_FORMAT(data_demissao, '%b/%y') AS rotulo,
                    COUNT(*) AS total
             FROM colaboradores
             WHERE data_demissao IS NOT NULL
               AND data_demissao >= DATE_SUB(CURDATE(), INTERVAL {$meses} MONTH)
             GROUP BY mes, rotulo
             ORDER BY mes ASC"
        );
    }

    public function porDepartamento(): array
    {
        return $this->linhas(
            "SELECT COALESCE(d.nome, 'Sem departamento') AS rotulo, COUNT(c.id) AS total
             FROM colaboradores c
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             WHERE c.status <> 'inativo'
             GROUP BY rotulo ORDER BY total DESC"
        );
    }

    public function porTipoContrato(): array
    {
        return $this->linhas(
            "SELECT tipo_contrato AS rotulo, COUNT(*) AS total
             FROM colaboradores WHERE status <> 'inativo'
             GROUP BY tipo_contrato ORDER BY total DESC"
        );
    }

    public function aniversariantesDoMes(int $mes = null): array
    {
        $mes = $mes ?? (int) date('n');
        return $this->linhas(
            "SELECT c.*, d.nome AS departamento
             FROM colaboradores c
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             WHERE c.status <> 'inativo' AND MONTH(c.data_nascimento) = ?
             ORDER BY DAY(c.data_nascimento) ASC",
            [$mes]
        );
    }

    public function proximasFerias(int $limite = 6): array
    {
        return $this->linhas(
            "SELECT a.*, c.nome AS colaborador, d.nome AS departamento
             FROM ausencias a
             INNER JOIN colaboradores c ON c.id = a.colaborador_id
             LEFT JOIN departamentos d ON d.id = c.departamento_id
             WHERE a.tipo = 'ferias' AND a.status = 'aprovado' AND a.data_inicio >= CURDATE()
             ORDER BY a.data_inicio ASC LIMIT {$limite}"
        );
    }

    public function departamentosComPessoal(): array
    {
        return $this->linhas(
            "SELECT DISTINCT d.id, d.nome
             FROM departamentos d
             INNER JOIN colaboradores c ON c.departamento_id = d.id
             ORDER BY d.nome"
        );
    }
}

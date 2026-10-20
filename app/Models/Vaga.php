<?php

namespace App\Models;

use App\Core\Model;

class Vaga extends Model
{
    protected string $tabela = 'vagas';

    public function listarComFiltros(array $filtros = []): array
    {
        $where = ['1=1'];
        $params = [];
        if (!empty($filtros['status'])) {
            $where[] = 'v.status = ?';
            $params[] = $filtros['status'];
        }
        if (!empty($filtros['busca'])) {
            $where[] = '(v.titulo LIKE ? OR v.descricao LIKE ?)';
            $t = '%' . $filtros['busca'] . '%';
            array_push($params, $t, $t);
        }
        $w = implode(' AND ', $where);
        return $this->linhas(
            "SELECT v.*, d.nome AS departamento, u.nome AS responsavel,
                    (SELECT COUNT(*) FROM candidatos cd WHERE cd.vaga_id = v.id) AS total_candidatos
             FROM vagas v
             LEFT JOIN departamentos d ON d.id = v.departamento_id
             LEFT JOIN usuarios u ON u.id = v.responsavel_id
             WHERE {$w}
             ORDER BY v.status = 'aberta' DESC, v.data_abertura DESC",
            $params
        );
    }

    public function completa(int $id): ?array
    {
        return $this->primeiraLinha(
            'SELECT v.*, d.nome AS departamento, u.nome AS responsavel
             FROM vagas v
             LEFT JOIN departamentos d ON d.id = v.departamento_id
             LEFT JOIN usuarios u ON u.id = v.responsavel_id
             WHERE v.id = ? LIMIT 1',
            [$id]
        );
    }

    public function pipeline(): array
    {
        $etapas = [
            'candidatura' => 'Candidaturas',
            'analise'     => 'Em análise',
            'entrevista'  => 'Entrevistas',
            'ofertado'    => 'Ofertados',
            'contratado'  => 'Contratados',
        ];
        $dados = [];
        foreach ($etapas as $chave => $rotulo) {
            $dados[$chave] = [
                'rotulo' => $rotulo,
                'total'  => (int) $this->valor(
                    'SELECT COUNT(*) FROM candidatos WHERE etapa = ? AND status = "ativo"',
                    [$chave]
                ),
                'itens'  => $this->linhas(
                    'SELECT cd.*, v.titulo AS vaga
                     FROM candidatos cd
                     INNER JOIN vagas v ON v.id = cd.vaga_id
                     WHERE cd.etapa = ? AND cd.status = "ativo"
                     ORDER BY cd.data_candidatura DESC LIMIT 20',
                    [$chave]
                ),
            ];
        }
        return $dados;
    }

    public function totalPorStatus(): array
    {
        $saida = [];
        foreach ($this->linhas('SELECT status, COUNT(*) AS total FROM vagas GROUP BY status') as $l) {
            $saida[$l['status']] = (int) $l['total'];
        }
        return $saida;
    }
}

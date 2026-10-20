<?php

namespace App\Models;

use App\Core\Model;

class Treinamento extends Model
{
    protected string $tabela = 'treinamentos';

    public function listarComInscricoes(): array
    {
        return $this->linhas(
            "SELECT t.*,
                    COUNT(i.id) AS total_inscritos,
                    SUM(i.status = 'concluido') AS concluidos,
                    AVG(NULLIF(i.progresso,0)) AS progresso_medio
             FROM treinamentos t
             LEFT JOIN inscricoes_treinamento i ON i.treinamento_id = t.id
             GROUP BY t.id
             ORDER BY t.data_inicio DESC, t.id DESC"
        );
    }

    public function inscricoes(?int $treinamentoId = null): array
    {
        $sql = "SELECT i.*, t.titulo AS treinamento, t.categoria, t.carga_horaria, t.data_inicio,
                       c.nome AS colaborador, d.nome AS departamento
                FROM inscricoes_treinamento i
                INNER JOIN treinamentos t ON t.id = i.treinamento_id
                INNER JOIN colaboradores c ON c.id = i.colaborador_id
                LEFT JOIN departamentos d ON d.id = c.departamento_id";
        $params = [];
        if ($treinamentoId) {
            $sql .= ' WHERE i.treinamento_id = ?';
            $params[] = $treinamentoId;
        }
        return $this->linhas($sql . ' ORDER BY i.status, c.nome', $params);
    }

    public function categorias(): array
    {
        return $this->linhas('SELECT DISTINCT categoria FROM treinamentos ORDER BY categoria');
    }

    public function indicadores(): array
    {
        $total = (int) $this->valor('SELECT COUNT(*) FROM treinamentos');
        $inscritos = (int) $this->valor('SELECT COUNT(*) FROM inscricoes_treinamento');
        $concluidos = (int) $this->valor("SELECT COUNT(*) FROM inscricoes_treinamento WHERE status = 'concluido'");
        $horas = (float) ($this->valor("SELECT SUM(carga_horaria) FROM treinamentos WHERE data_inicio <= CURDATE()") ?? 0);
        return [
            'total'       => $total,
            'inscritos'   => $inscritos,
            'concluidos'  => $concluidos,
            'em_andamento'=> (int) $this->valor("SELECT COUNT(*) FROM inscricoes_treinamento WHERE status = 'em_andamento'"),
            'horas'       => $horas,
            'taxa'        => $inscritos > 0 ? round(($concluidos / $inscritos) * 100, 1) : 0.0,
        ];
    }

    public function horasPorCategoria(): array
    {
        return $this->linhas(
            "SELECT t.categoria AS rotulo, SUM(t.carga_horaria) AS total
             FROM treinamentos t GROUP BY t.categoria ORDER BY total DESC"
        );
    }
}

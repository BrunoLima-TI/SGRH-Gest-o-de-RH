<?php

namespace App\Models;

use App\Core\Model;

class Departamento extends Model
{
    protected string $tabela = 'departamentos';

    public function nomeEmUso(string $nome): bool
    {
        return (int) $this->valor(
            'SELECT COUNT(*) FROM departamentos WHERE nome = ?',
            [$nome]
        ) > 0;
    }

    public function comContagem(): array
    {
        return $this->linhas(
            'SELECT d.*, COUNT(c.id) AS total_colaboradores
             FROM departamentos d
             LEFT JOIN colaboradores c ON c.departamento_id = d.id AND c.status <> "inativo"
             GROUP BY d.id
             ORDER BY total_colaboradores DESC, d.nome ASC'
        );
    }
}

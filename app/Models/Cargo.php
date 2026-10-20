<?php

namespace App\Models;

use App\Core\Model;

class Cargo extends Model
{
    protected string $tabela = 'cargos';

    public function comDepartamento(): array
    {
        return $this->linhas(
            'SELECT cg.*, d.nome AS departamento
             FROM cargos cg
             LEFT JOIN departamentos d ON d.id = cg.departamento_id
             ORDER BY d.nome, cg.titulo'
        );
    }
}

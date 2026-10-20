<?php

namespace App\Models;

use App\Core\Model;

class Candidato extends Model
{
    protected string $tabela = 'candidatos';

    public function porVaga(int $vagaId): array
    {
        return $this->linhas(
            'SELECT * FROM candidatos WHERE vaga_id = ? ORDER BY data_candidatura DESC',
            [$vagaId]
        );
    }
}

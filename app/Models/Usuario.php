<?php

namespace App\Models;

use App\Core\Model;

class Usuario extends Model
{
    protected string $tabela = 'usuarios';

    public function autenticar(string $email, string $senha): ?array
    {
        $st = $this->query('SELECT * FROM usuarios WHERE email = ? LIMIT 1', [$email]);
        $usuario = $st->fetch();
        if (!$usuario || !password_verify($senha, $usuario['senha'])) {
            return null;
        }
        if ($usuario['status'] !== 'ativo') {
            return null;
        }
        $this->query('UPDATE usuarios SET ultimo_acesso = NOW() WHERE id = ?', [$usuario['id']]);
        unset($usuario['senha']);
        return $usuario;
    }

    public function emailEmUso(string $email, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM usuarios WHERE email = ?';
        $params = [$email];
        if ($ignorarId) {
            $sql .= ' AND id <> ?';
            $params[] = $ignorarId;
        }
        return (int) $this->valor($sql, $params) > 0;
    }

    public function comContagem(): array
    {
        return $this->linhas(
            'SELECT u.id, u.nome, u.email, u.perfil, u.cargo, u.status, u.criado_em, u.ultimo_acesso,
                    (SELECT COUNT(*) FROM avaliacoes_desempenho a WHERE a.avaliador_id = u.id) AS avaliacoes
             FROM usuarios u ORDER BY u.nome'
        );
    }
}

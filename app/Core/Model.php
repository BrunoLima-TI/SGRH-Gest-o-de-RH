<?php

namespace App\Core;

use PDO;

/**
 * Model base —Active Record leve sobre PDO.
 */
abstract class Model
{
    protected string $tabela;
    protected string $pk = 'id';
    protected PDO $db;

    public function __construct()
    {
        $this->db = Database::conexao();
    }

    public function todos(string $ordem = 'id ASC'): array
    {
        return $this->db->query("SELECT * FROM {$this->tabela} ORDER BY {$ordem}")->fetchAll();
    }

    public function buscar(int $id): ?array
    {
        $st = $this->db->prepare("SELECT * FROM {$this->tabela} WHERE {$this->pk} = ? LIMIT 1");
        $st->execute([$id]);
        $linha = $st->fetch();
        return $linha ?: null;
    }

    public function inserir(array $dados): int
    {
        $colunas = array_keys($dados);
        $marcadores = array_map(fn($c) => ':' . $c, $colunas);
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $this->tabela,
            implode(', ', $colunas),
            implode(', ', $marcadores)
        );
        $st = $this->db->prepare($sql);
        $st->execute($dados);
        return (int) $this->db->lastInsertId();
    }

    public function atualizar(int $id, array $dados): bool
    {
        if (!$dados) {
            return false;
        }
        $atribuicoes = [];
        $valores = [];
        foreach ($dados as $coluna => $valor) {
            $atribuicoes[] = "{$coluna} = :{$coluna}";
            $valores[$coluna] = $valor;
        }
        $valores['__id'] = $id;
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s = :__id',
            $this->tabela,
            implode(', ', $atribuicoes),
            $this->pk
        );
        $st = $this->db->prepare($sql);
        return $st->execute($valores);
    }

    public function excluir(int $id): bool
    {
        $st = $this->db->prepare("DELETE FROM {$this->tabela} WHERE {$this->pk} = ?");
        return $st->execute([$id]);
    }

    public function contar(string $where = '1=1', array $params = []): int
    {
        $st = $this->db->prepare("SELECT COUNT(*) FROM {$this->tabela} WHERE {$where}");
        $st->execute($params);
        return (int) $st->fetchColumn();
    }

    public function listar(string $where = '1=1', array $params = [], string $ordem = 'id ASC', ?int $limite = null, ?int $offset = null): array
    {
        $sql = "SELECT * FROM {$this->tabela} WHERE {$where} ORDER BY {$ordem}";
        $st = $this->db->prepare($sql);
        $st->execute($params);
        $resultado = $st->fetchAll();
        if ($limite !== null) {
            $resultado = array_slice($resultado, (int) $offset, (int) $limite);
        }
        return $resultado;
    }

    /** Executa uma query arbitrária com prepared statement. */
    protected function query(string $sql, array $params = []): \PDOStatement
    {
        $st = $this->db->prepare($sql);
        $st->execute($params);
        return $st;
    }

    protected function valor(string $sql, array $params = [])
    {
        $st = $this->query($sql, $params);
        $v = $st->fetchColumn();
        return $v === false ? null : $v;
    }

    /** Primeira linha do resultado (ou null). */
    protected function primeiraLinha(string $sql, array $params = []): ?array
    {
        $linhas = $this->query($sql, $params)->fetchAll();
        return $linhas[0] ?? null;
    }

    protected function linhas(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }
}

<?php

namespace App\Core;

/**
 * Validador simples com mensagens em português.
 */
class Validator
{
    private array $erros = [];
    private array $dados = [];

    public function __construct(array $dados)
    {
        $this->dados = $dados;
    }

    public static function criar(array $dados): self
    {
        return new self($dados);
    }

    public function obrigatorio(string $campo, string $rotulo): self
    {
        $v = $this->valor($campo);
        if ($v === '' || $v === null) {
            $this->erros[$campo] = "O campo {$rotulo} é obrigatório.";
        }
        return $this;
    }

    public function email(string $campo, string $rotulo = 'e-mail'): self
    {
        $v = $this->valor($campo);
        if ($v !== '' && !filter_var($v, FILTER_VALIDATE_EMAIL)) {
            $this->erros[$campo] = "Informe um {$rotulo} válido.";
        }
        return $this;
    }

    public function cpf(string $campo, string $rotulo = 'CPF'): self
    {
        $v = preg_replace('/\D/', '', (string) $this->valor($campo)) ?? '';
        if ($v !== '' && !self::cpfValido($v)) {
            $this->erros[$campo] = "{$rotulo} inválido.";
        }
        return $this;
    }

    public function minimo(string $campo, int $tamanho, string $rotulo): self
    {
        if (mb_strlen((string) $this->valor($campo)) < $tamanho) {
            $this->erros[$campo] = "{$rotulo} deve ter ao menos {$tamanho} caracteres.";
        }
        return $this;
    }

    public function maximo(string $campo, int $tamanho, string $rotulo): self
    {
        if (mb_strlen((string) $this->valor($campo)) > $tamanho) {
            $this->erros[$campo] = "{$rotulo} deve ter no máximo {$tamanho} caracteres.";
        }
        return $this;
    }

    public function numerico(string $campo, string $rotulo): self
    {
        $v = $this->valor($campo);
        if ($v !== '' && !is_numeric(str_replace(',', '.', $v))) {
            $this->erros[$campo] = "{$rotulo} deve ser um número.";
        }
        return $this;
    }

    public function data(string $campo, string $rotulo): self
    {
        $v = $this->valor($campo);
        if ($v !== '' && !strtotime($v)) {
            $this->erros[$campo] = "{$rotulo} inválida.";
        }
        return $this;
    }

    public function dataValida(string $campo, string $rotulo): self
    {
        $this->data($campo, $rotulo);
        $v = $this->valor($campo);
        if ($v !== null && $v !== '' && strtotime((string) $v) > time()) {
            $this->erros[$campo] = "{$rotulo} não pode ser uma data futura.";
        }
        return $this;
    }

    public function unico(string $campo, callable $existe, string $rotulo): self
    {
        if ($this->valor($campo) !== '' && $existe((string) $this->valor($campo))) {
            $this->erros[$campo] = "Já existe um registro com este {$rotulo}.";
        }
        return $this;
    }

    public function igual(string $campo, string $outro, string $rotulo): self
    {
        if ($this->valor($campo) !== $this->valor($outro)) {
            $this->erros[$campo] = "A confirmação de {$rotulo} não confere.";
        }
        return $this;
    }

    public function adicionarErro(string $campo, string $mensagem): self
    {
        $this->erros[$campo] = $mensagem;
        return $this;
    }

    public function valor(string $campo)
    {
        return $this->dados[$campo] ?? null;
    }

    public function falha(): bool
    {
        return !empty($this->erros);
    }

    public function erros(): array
    {
        return $this->erros;
    }

    public static function cpfValido(string $cpf): bool
    {
        $cpf = preg_replace('/\D/', '', $cpf) ?? '';
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }
        for ($t = 9; $t < 11; $t++) {
            $d = 0;
            for ($c = 0; $c < $t; $c++) {
                $d += (int) $cpf[$c] * (($t + 1) - $c);
            }
            $dv = ((10 * $d) % 11) % 10;
            if ((int) $cpf[$t] !== $dv) {
                return false;
            }
        }
        return true;
    }
}

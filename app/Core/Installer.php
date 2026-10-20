<?php

namespace App\Core;

use PDO;

/**
 * Instalador do banco de dados.
 *
 * Cria o banco, todas as tabelas e popula com dados iniciais.
 * Executado automaticamente na primeira visita ao sistema.
 */
class Installer
{
    private array $log = [];
    private PDO $pdo;

    public function __construct(array $configDb)
    {
        $this->pdo = Database::conectar('', $configDb);
    }

    public function log(): array
    {
        return $this->log;
    }

    public function instalar(bool $comDadosExemplo = true): void
    {
        $nome = $this->pdo->query('SELECT DATABASE()')->fetchColumn() ?: 'sgrh';
        $this->log[] = "Conectado ao servidor MySQL (banco atual: {$nome}).";

        $banco = $GLOBALS['config']['db']['nome'];
        $this->pdo->exec(
            "CREATE DATABASE IF NOT EXISTS `{$banco}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
        );
        $this->log[] = "Banco de dados `{$banco}` criado/verificado.";

        $this->pdo->exec("USE `{$banco}`");

        $schema = file_get_contents(APP_PATH . '/../database/schema.sql');
        if ($schema === false) {
            throw new \RuntimeException('Arquivo database/schema.sql não encontrado.');
        }

        foreach ($this->dividir($schema) as $comando) {
            $comando = trim($comando);
            if ($comando === '' || str_starts_with($comando, '--')) {
                continue;
            }
            try {
                $this->pdo->exec($comando);
            } catch (\PDOException $e) {
                $this->log[] = 'Aviso ao executar: ' . substr($e->getMessage(), 0, 160);
            }
        }
        $this->log[] = 'Tabelas criadas com sucesso.';

        $this->criarUsuarioAdmin();
        $this->criarCategoriasBase();

        if ($comDadosExemplo && $this->total('colaboradores') === 0) {
            $this->popularDadosExemplo();
            $this->log[] = 'Dados de exemplo populationados.';
        }

        $this->registrarInstalacao();
    }

    /** Divide um SQL em comandos, respeitando strings e comentários. */
    private function dividir(string $sql): array
    {
        $comandos = [];
        $buffer = '';
        $emString = false;
        $aspas = '';
        $len = strlen($sql);

        for ($i = 0; $i < $len; $i++) {
            $c = $sql[$i];
            $prox = $i + 1 < $len ? $sql[$i + 1] : '';

            if (!$emString) {
                if ($c === '-' && $prox === '-') {
                    while ($i < $len && $sql[$i] !== "\n") {
                        $i++;
                    }
                    $buffer .= "\n";
                    continue;
                }
                if ($c === '#') {
                    while ($i < $len && $sql[$i] !== "\n") {
                        $i++;
                    }
                    continue;
                }
                if ($c === "'" || $c === '"' || $c === '`') {
                    $emString = true;
                    $aspas = $c;
                }
                if ($c === ';') {
                    $comandos[] = $buffer;
                    $buffer = '';
                    continue;
                }
            } else {
                if ($c === '\\') {
                    $buffer .= $c . $prox;
                    $i++;
                    continue;
                }
                if ($c === $aspas) {
                    $emString = false;
                }
            }
            $buffer .= $c;
        }

        if (trim($buffer) !== '') {
            $comandos[] = $buffer;
        }

        return $comandos;
    }

    private function total(string $tabela): int
    {
        try {
            return (int) $this->pdo->query("SELECT COUNT(*) FROM `{$tabela}`")->fetchColumn();
        } catch (\PDOException $e) {
            return 0;
        }
    }

    private function criarUsuarioAdmin(): void
    {
        if ($this->total('usuarios') > 0) {
            $this->log[] = 'Usuário administrador já existente.';
            return;
        }
        $senha = 'sgrh@2026';
        $st = $this->pdo->prepare(
            'INSERT INTO usuarios (nome, email, senha, perfil, cargo, status)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $st->execute(['Administrador', 'admin@sgrh.local', password_hash($senha, PASSWORD_DEFAULT), 'admin', 'Administrador do sistema', 'ativo']);
        $this->log[] = "Usuário administrador criado: admin@sgrh.local / {$senha}";
    }

    private function criarCategoriasBase(): void
    {
        $categorias = ['Integração', 'Técnico', 'Liderança', 'Compliance', 'Segurança do Trabalho', 'Desenvolvimento'];
        $st = $this->pdo->prepare('SELECT COUNT(*) FROM treinamentos WHERE categoria = ?');
        foreach ($categorias as $c) {
            $st->execute([$c]);
            if ((int) $st->fetchColumn() === 0) {
                $this->pdo->prepare('INSERT INTO treinamentos (titulo, categoria, carga_horaria, vagas) VALUES (?, ?, ?, ?)')
                    ->execute(["Trilha {$c}", $c, 8, 20]);
            }
        }
    }

    private function registrarInstalacao(): void
    {
        $arquivo = APP_PATH . '/../storage/instalado.txt';
        if (!is_dir(dirname($arquivo))) {
            @mkdir(dirname($arquivo), 0775, true);
        }
        @file_put_contents($arquivo, date('Y-m-d H:i:s'));
    }

    // ---------------------------------------------------------------
    //  Dados de exemplo
    // ---------------------------------------------------------------

    private function popularDadosExemplo(): void
    {
        $this->pdo->beginTransaction();
        try {
            $departamentos = [
                ['Operações', 'Lojas, logística e atendimento ao cliente'],
                ['Administrativo', 'Financeiro, jurídico e pessoas'],
                ['Comercial', 'Vendas e relacionamento com clientes'],
                ['Tecnologia', 'Sistemas, infraestrutura e dados'],
                ['Marketing', 'Comunicação, marca e conteúdo'],
                ['Suprimentos', 'Compras, estoque e contratos'],
            ];
            foreach ($departamentos as [$nome, $desc]) {
                $this->pdo->prepare('INSERT INTO departamentos (nome, descricao) VALUES (?, ?)')->execute([$nome, $desc]);
            }
            $depIds = $this->pdo->query('SELECT id FROM departamentos')->fetchAll(PDO::FETCH_COLUMN);

            $cargos = [
                ['Operações', 'Supervisor de Loja'], ['Operações', 'Atendente'],
                ['Operações', 'Operador de Caixa'], ['Operações', 'Estoquista'],
                ['Administrativo', 'Analista Administrativo'], ['Administrativo', 'Contador'],
                ['Administrativo', 'Analista de RH'], ['Administrativo', 'Assistente Administrativo'],
                ['Comercial', 'Vendedor'], ['Comercial', 'Gerente Comercial'],
                ['Comercial', 'Assistente de Vendas'],
                ['Tecnologia', 'Desenvolvedor PHP'], ['Tecnologia', 'Analista de Sistemas'],
                ['Tecnologia', 'Suporte de TI'], ['Tecnologia', 'Cientista de Dados'],
                ['Marketing', 'Designer'], ['Marketing', 'Analista de Marketing'],
                ['Marketing', 'Social Media'],
                ['Suprimentos', 'Comprador'], ['Suprimentos', 'Analista de Suprimentos'],
            ];
            foreach ($cargos as [$dep, $titulo]) {
                $depId = $this->idDepartamento($depIds, $dep);
                $this->pdo->prepare('INSERT INTO cargos (departamento_id, titulo) VALUES (?, ?)')->execute([$depId, $titulo]);
            }
            $cargoIds = $this->pdo->query('SELECT id FROM cargos')->fetchAll(PDO::FETCH_COLUMN);

            $nomesF = ['Ana', 'Beatriz', 'Camila', 'Daniela', 'Eduarda', 'Fernanda', 'Gabriela', 'Helena', 'Isabela', 'Juliana', 'Larissa', 'Mariana', 'Natália', 'Olívia', 'Patrícia', 'Rafaela', 'Sabrina', 'Tatiana', 'Vanessa', 'Yasmin'];
            $nomesM = ['André', 'Bruno', 'Caio', 'Diego', 'Eduardo', 'Felipe', 'Gustavo', 'Henrique', 'Igor', 'João', 'Lucas', 'Marcelo', 'Nicolas', 'Otávio', 'Paulo', 'Rafael', 'Sergio', 'Thiago', 'Vinícius', 'Wesley'];
            $sobrenomes = ['Silva', 'Santos', 'Oliveira', 'Souza', 'Rodrigues', 'Ferreira', 'Alves', 'Pereira', 'Lima', 'Gomes', 'Ribeiro', 'Carvalho', 'Almeida', 'Lopes', 'Soares', 'Fernandes', 'Vieira', 'Barbosa', 'Rocha', 'Dias'];

            $cargosNomes = ['Supervisor de Loja', 'Atendente', 'Operador de Caixa', 'Estoquista', 'Analista Administrativo', 'Contador', 'Analista de RH', 'Assistente Administrativo', 'Vendedor', 'Gerente Comercial', 'Assistente de Vendas', 'Desenvolvedor PHP', 'Analista de Sistemas', 'Suporte de TI', 'Cientista de Dados', 'Designer', 'Analista de Marketing', 'Social Media', 'Comprador', 'Analista de Suprimentos'];

            $stColab = $this->pdo->prepare(
                'INSERT INTO colaboradores
                 (nome, email, cpf, telefone, data_nascimento, data_admissao, departamento_id, cargo_id,
                  tipo_contrato, salario, status, endereco, estado_civil, nivel_educacao)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)'
            );

            $colaboradores = [];
            for ($i = 0; $i < 42; $i++) {
                $feminino = $i % 2 === 0;
                $primeiro = $nomesF[$i % 20];
                $primeiroM = $nomesM[$i % 20];
                $sobrenome = $sobrenomes[intdiv($i, 2) % 20];
                $nome = $feminino ? "{$primeiro} {$sobrenome} {$sobrenomes[($i + 7) % 20]}" : "{$primeiroM} {$sobrenome} {$sobrenomes[($i + 3) % 20]}";
                $nome = trim($nome);

                $cargoNome = $cargosNomes[$i % 20];
                $cargoId = $this->idCargoPorNome($cargoIds, $cargoNome);
                $depId = $this->idDepartamentoPorCargo($cargoId);

                $admissao = date('Y-m-d', strtotime('-' . random_int(30, 2200) . ' days'));
                $nasc = date('Y-m-d', strtotime($admissao . ' -' . random_int(8000, 15000) . ' days'));

                $status = 'ativo';
                $r = random_int(1, 100);
                if ($r <= 8) {
                    $status = 'ferias';
                } elseif ($r <= 14) {
                    $status = 'afastado';
                } elseif ($r <= 26) {
                    $status = 'inativo';
                }

                $salario = match ($cargoNome) {
                    'Gerente Comercial' => 9500.00,
                    'Supervisor de Loja' => 4200.00,
                    'Analista de RH' => 4800.00,
                    'Desenvolvedor PHP', 'Cientista de Dados' => 7800.00,
                    'Designer', 'Analista de Marketing' => 4300.00,
                    'Contador' => 4600.00,
                    'Analista de Sistemas' => 6200.00,
                    'Vendedor' => 2400.00,
                    default => 2100.00,
                };

                $email = strtolower(str_replace(' ', '.', mb_strtolower($nome))) . '@empresa.com.br';
                $cpf = $this->gerarCpf();

                $stColab->execute([
                    $nome, $email, $cpf,
                    '(11) 9' . random_int(1000, 9999) . '-' . random_int(1000, 9999),
                    $nasc, $admissao, $depId, $cargoId,
                    $i % 13 === 0 ? 'pj' : ($i % 17 === 0 ? 'estagio' : 'clt'),
                    $salario, $status,
                    'Rua ' . $sobrenomes[$i % 20] . ', ' . random_int(10, 1999) . ' - Centro',
                    $i % 3 === 0 ? 'Solteiro(a)' : 'Casado(a)',
                    ['Ensino Médio', 'Ensino Superior', 'Pós-graduação', 'Mestrado'][$i % 4],
                ]);
                $colaboradores[] = (int) $this->pdo->lastInsertId();

                if ($status === 'inativo') {
                    $demissao = date('Y-m-d', strtotime('-' . random_int(20, 340) . ' days'));
                    $this->pdo->prepare('UPDATE colaboradores SET data_demissao = ? WHERE id = ?')
                        ->execute([$demissao, $colaboradores[count($colaboradores) - 1]]);
                }
            }

            // Frequências dos últimos 3 meses
            $stFreq = $this->pdo->prepare(
                'INSERT INTO frequencias (colaborador_id, data, entrada, saida, horas_trabalhadas, horas_extras, status)
                 VALUES (?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE status = VALUES(status)'
            );
            $hoje = new \DateTimeImmutable('today');
            foreach ($colaboradores as $cid) {
                for ($d = 0; $d < 92; $d++) {
                    $data = $hoje->modify("-{$d} days");
                    $ts = $data->getTimestamp();
                    if (date('N', $ts) >= 6) {
                        continue;
                    }
                    $sorteio = random_int(1, 100);
                    if ($sorteio <= 4) {
                        $stFreq->execute([$cid, $data->format('Y-m-d'), null, null, 0, 0, 'falta']);
                        continue;
                    }
                    if ($sorteio <= 8) {
                        $stFreq->execute([$cid, $data->format('Y-m-d'), null, null, 0, 0, 'ausente']);
                        continue;
                    }
                    $atraso = $sorteio <= 13;
                    $entrada = $atraso ? sprintf('%02d:%02d:00', random_int(8, 9), random_int(10, 59)) : sprintf('%02d:%02d:00', random_int(7, 8), random_int(0, 55));
                    $saida = sprintf('%02d:%02d:00', random_int(17, 19), random_int(0, 59));
                    $extras = random_int(1, 100) <= 18 ? random_int(1, 90) / 10 : 0;
                    $stFreq->execute([
                        $cid,
                        $data->format('Y-m-d'),
                        $entrada,
                        $saida,
                        8,
                        $extras,
                        $atraso ? 'atraso' : 'presente',
                    ]);
                }
            }

            // Férias e afastamentos
            $stAus = $this->pdo->prepare(
                'INSERT INTO ausencias (colaborador_id, tipo, data_inicio, data_fim, dias, status, justificativa)
                 VALUES (?,?,?,?,?,?,?)'
            );
            $justificativas = [
                'Férias anual conforme política interna',
                'Atestado médico apresentação de 2 dias',
                'Licença maternidade',
                'Férias programada no calendário do setor',
                'Afastamento por resolucao tecnica de equipamento',
            ];
            foreach ($colaboradores as $cid) {
                $qtd = random_int(0, 2);
                for ($i = 0; $i < $qtd; $i++) {
                    $inicio = $hoje->modify('+' . random_int(-120, 120) . ' days');
                    $dias = random_int(1, 20);
                    $tipo = ['ferias', 'ferias', 'atestado', 'licenca', 'falta'][random_int(0, 4)];
                    $status = ['pendente', 'aprovado', 'aprovado', 'reprovado'][random_int(0, 3)];
                    $stAus->execute([
                        $cid, $tipo,
                        $inicio->format('Y-m-d'),
                        $inicio->modify("+{$dias} days")->format('Y-m-d'),
                        $dias, $status,
                        $justificativas[random_int(0, 4)],
                    ]);
                }
            }

            // Vagas e candidatos
            $vagas = [
                ['Vendedor Comercial', 'Comercial', 'clt', 'Atuação em loja, prospecção de clientes e fechamento de vendas.', 'Ensino médio completo e experiência com vendas.', 3, 'aberta', -25],
                ['Desenvolvedor PHP', 'Tecnologia', 'clt', 'Desenvolvimento e manutenção de aplicações web em PHP.', 'Conhecimento em PHP, MySQL, MVC e boas práticas.', 2, 'aberta', -40],
                ['Analista de RH', 'Administrativo', 'clt', 'Atuar com recrutamento, treinamento e DP.', 'Formação em RH ou areas correlatas.', 1, 'em_analise', -15],
                ['Estoquista', 'Operações', 'clt', 'Controle de estoque, recebimento e separação de mercadorias.', 'Ensino médio e organização.', 2, 'aberta', -8],
                ['Designer de Produto', 'Tecnologia', 'pj', 'Design de interfaces e experiências digitais.', 'Portfólio e domínio de UI/UX.', 1, 'fechada', -90],
            ];
            $stVaga = $this->pdo->prepare(
                'INSERT INTO vagas (titulo, departamento_id, tipo_contrato, descricao, requisitos, quantidade, status, data_abertura, data_fechamento)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            );
            $stCand = $this->pdo->prepare(
                'INSERT INTO candidatos (vaga_id, nome, email, telefone, etapa, status, experiencia, data_candidatura)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $etapas = ['candidatura', 'analise', 'entrevista', 'ofertado', 'contratado', 'descartado'];
            foreach ($vagas as [$titulo, $dep, $tipo, $desc, $req, $qtd, $status, $dias]) {
                $abertura = $hoje->modify("{$dias} days");
                $fechamento = $status === 'fechada' ? $abertura->modify('+45 days')->format('Y-m-d') : null;
                $stVaga->execute([$titulo, $this->idDepartamento($depIds, $dep), $tipo, $desc, $req, $qtd, $status, $abertura->format('Y-m-d'), $fechamento]);
                $vagaId = (int) $this->pdo->lastInsertId();
                for ($c = 0; $c < random_int(2, 6); $c++) {
                    $nomeC = $nomesF[random_int(0, 19)] . ' ' . $sobrenomes[random_int(0, 19)] . ' ' . $sobrenomes[random_int(0, 19)];
                    $stCand->execute([
                        $vagaId, $nomeC,
                        strtolower(str_replace(' ', '.', mb_strtolower($nomeC))) . '@email.com',
                        '(11) 9' . random_int(1000, 9999) . '-' . random_int(1000, 9999),
                        $etapas[random_int(0, 5)],
                        'ativo',
                        random_int(1, 12) . ' anos de experiência na área',
                        $abertura->modify('+' . random_int(0, 30) . ' days')->format('Y-m-d'),
                    ]);
                }
            }

            // Treinamentos e inscrições
            $treinamentos = [
                ['Integração e Cultura Organizacional', 'Integração', 8, 'Rhiannon Dias', '2026-01-15', '2026-01-17'],
                ['Excel Avançado para Gestão', 'Técnico', 16, 'Carlos Menezes', '2026-02-02', '2026-02-06'],
                ['Liderança e Gestão de Equipe', 'Liderança', 24, 'Ana Paula Souza', '2026-02-10', '2026-03-10'],
                ['LGPD e Compliance', 'Compliance', 4, 'Advogado Parceiro', '2026-03-01', '2026-03-01'],
                ['Segurança do Trabalho e NR-12', 'Segurança do Trabalho', 12, 'Eng. Segurança', '2026-03-10', '2026-03-12'],
                ['Gestão de Vendas e CRM', 'Técnico', 20, 'Marcos Ribeiro', '2026-03-15', '2026-04-15'],
                ['Feedback e Avaliação de Desempenho', 'Desenvolvimento', 6, 'RH Corporativo', '2026-04-01', '2026-04-02'],
            ];
            $stTrein = $this->pdo->prepare(
                'INSERT INTO treinamentos (titulo, descricao, categoria, carga_horaria, data_inicio, data_fim, instrutor, vagas, custo)
                 VALUES (?,?,?,?,?,?,?,?,?)'
            );
            foreach ($treinamentos as [$titulo, $cat, $ch, $instr, $di, $df]) {
                $stTrein->execute([
                    $titulo,
                    'Treinamento de ' . mb_strtolower($cat) . ' com foco em aplicabilidade prática no dia a dia.',
                    $cat, $ch, $di, $df, $instr, 25, random_int(300, 2500),
                ]);
            }
            $treinIds = $this->pdo->query('SELECT id FROM treinamentos ORDER BY id DESC LIMIT ' . count($treinamentos))->fetchAll(PDO::FETCH_COLUMN);
            $treinIds = array_reverse($treinIds);

            $stInsc = $this->pdo->prepare(
                'INSERT INTO inscricoes_treinamento (treinamento_id, colaborador_id, status, progresso, nota, data_inscricao, data_conclusao)
                 VALUES (?,?,?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE status = VALUES(status)'
            );
            foreach ($treinIds as $tid) {
                foreach (array_slice($colaboradores, 0, random_int(5, 14)) as $cid) {
                    $r = random_int(1, 100);
                    if ($r <= 40) {
                        $status = 'concluido';
                        $prog = 100;
                        $nota = random_int(60, 100) / 10;
                        $conclusao = date('Y-m-d', strtotime('-' . random_int(5, 200) . ' days'));
                    } elseif ($r <= 80) {
                        $status = 'em_andamento';
                        $prog = random_int(20, 90);
                        $nota = null;
                        $conclusao = null;
                    } else {
                        $status = 'inscrito';
                        $prog = 0;
                        $nota = null;
                        $conclusao = null;
                    }
                    $stInsc->execute([$tid, $cid, $status, $prog, $nota, date('Y-m-d', strtotime('-' . random_int(30, 300) . ' days')), $conclusao]);
                }
            }

            // Avaliações de desempenho
            $criterios = ['Qualidade do trabalho', 'Relacionamento interpessoal', 'Proatividade', 'Cumprimento de metas', 'Conhecimento técnico'];
            $stAval = $this->pdo->prepare(
                'INSERT INTO avaliacoes_desempenho (colaborador_id, ciclo, avaliador_id, data_avaliacao, nota_geral, criterios, comentarios, status)
                 VALUES (?,?,?,?,?,?,?,?)'
            );
            $usuarioId = (int) $this->pdo->query("SELECT id FROM usuarios WHERE perfil IN ('admin','rh') LIMIT 1")->fetchColumn();
            foreach (array_slice($colaboradores, 0, 26) as $idx => $cid) {
                $notas = [];
                foreach ($criterios as $crit) {
                    $notas[$crit] = random_int(5, 10) / 2;
                }
                $media = array_sum($notas) / count($notas);
                $stAval->execute([
                    $cid,
                    'Ciclo ' . date('Y', strtotime('-' . random_int(0, 2) . ' year')) . ' - ' . (random_int(1, 2) === 1 ? '1º Semestre' : '2º Semestre'),
                    $usuarioId ?: null,
                    date('Y-m-d', strtotime('-' . random_int(10, 300) . ' days')),
                    round($media, 2),
                    json_encode($notas, JSON_UNESCAPED_UNICODE),
                    'Desempenho consistente, com destaque em organizacao e atendimento ao cliente. Recomenda-se ampliar participação em projetos de equipa.',
                    $idx % 5 === 0 ? 'pendente' : 'concluida',
                ]);
            }

            // Documentos
            $tipos = ['CTPS', 'RG', 'CPF', 'Diploma', 'Contrato', 'Certificado', 'Laudo'];
            $stDoc = $this->pdo->prepare(
                'INSERT INTO documentos (colaborador_id, nome, tipo, data_emissao, data_validade, observacoes)
                 VALUES (?,?,?,?,?,?)'
            );
            foreach ($colaboradores as $cid) {
                $qtd = random_int(0, 3);
                for ($i = 0; $i < $qtd; $i++) {
                    $tipo = $tipos[random_int(0, 6)];
                    $emissao = date('Y-m-d', strtotime('-' . random_int(60, 2000) . ' days'));
                    $validade = in_array($tipo, ['Contrato', 'Laudo', 'Certificado'], true)
                        ? date('Y-m-d', strtotime($emissao . ' +' . random_int(30, 900) . ' days'))
                        : null;
                    $stDoc->execute([$cid, "{$tipo} - " . strtoupper(substr(md5((string) $cid), 0, 6)), $tipo, $emissao, $validade, 'Documento digitalizado']);
                }
            }

            // Usuários de exemplo
            $usuarios = [
                ['Rh Supervisor', 'rh@sgrh.local', 'rh', 'Analista de RH'],
                ['Gestor Operações', 'gestor@sgrh.local', 'gestor', 'Supervisor de Loja'],
            ];
            foreach ($usuarios as [$nome, $email, $perfil, $cargo]) {
                $this->pdo->prepare('INSERT INTO usuarios (nome, email, senha, perfil, cargo, status) VALUES (?,?,?,?,?,?)')
                    ->execute([$nome, $email, password_hash('sgrh@2026', PASSWORD_DEFAULT), $perfil, $cargo, 'ativo']);
            }

            $this->pdo->commit();
        } catch (\Throwable $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    private function idDepartamento(array $ids, string $nome): ?int
    {
        $st = $this->pdo->prepare('SELECT id FROM departamentos WHERE nome = ?');
        $st->execute([$nome]);
        $id = $st->fetchColumn();
        return $id ?: ($ids[0] ?? null);
    }

    private function idDepartamentoPorCargo(?int $cargoId): ?int
    {
        if (!$cargoId) {
            return null;
        }
        $st = $this->pdo->prepare('SELECT departamento_id FROM cargos WHERE id = ?');
        $st->execute([$cargoId]);
        return $st->fetchColumn() ?: null;
    }

    private function idCargoPorNome(array $ids, string $nome): ?int
    {
        $st = $this->pdo->prepare('SELECT id FROM cargos WHERE titulo = ? LIMIT 1');
        $st->execute([$nome]);
        $id = $st->fetchColumn();
        return $id ?: ($ids[0] ?? null);
    }

    private function gerarCpf(): string
    {
        $n = [];
        for ($i = 0; $i < 9; $i++) {
            $n[] = random_int(0, 9);
        }
        for ($dv = 0; $dv < 2; $dv++) {
            $s = 0;
            for ($i = 0; $i < 9 + $dv; $i++) {
                $s += $n[$i] * (10 - $i);
            }
            $r = ((10 * $s) % 11) % 10;
            $n[] = $r;
        }
        return implode('', $n);
    }
}

-- ============================================================
-- SGRH - Sistema de Gestão de Recursos Humanos
-- Schema do banco de dados (MySQL 5.7+ / MariaDB 10.4+)
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------- Departamentos -----------------------
CREATE TABLE IF NOT EXISTS departamentos (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome        VARCHAR(80) NOT NULL,
    descricao   VARCHAR(255) DEFAULT NULL,
    criado_em   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_departamentos_nome (nome)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------- Cargos -------------------------
CREATE TABLE IF NOT EXISTS cargos (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    departamento_id INT UNSIGNED DEFAULT NULL,
    titulo          VARCHAR(120) NOT NULL,
    descricao       VARCHAR(255) DEFAULT NULL,
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_cargos_departamento (departamento_id),
    CONSTRAINT fk_cargos_departamento FOREIGN KEY (departamento_id)
        REFERENCES departamentos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------- Usuarios -------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome           VARCHAR(120) NOT NULL,
    email          VARCHAR(160) NOT NULL,
    senha          VARCHAR(255) NOT NULL,
    perfil         ENUM('admin','rh','gestor','colaborador') NOT NULL DEFAULT 'rh',
    cargo          VARCHAR(120) DEFAULT NULL,
    status         ENUM('ativo','inativo') NOT NULL DEFAULT 'ativo',
    criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ultimo_acesso  DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_usuarios_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------- Colaboradores -----------------------
CREATE TABLE IF NOT EXISTS colaboradores (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nome             VARCHAR(140) NOT NULL,
    email            VARCHAR(160) DEFAULT NULL,
    cpf              CHAR(11) DEFAULT NULL,
    telefone         VARCHAR(20) DEFAULT NULL,
    data_nascimento  DATE DEFAULT NULL,
    data_admissao    DATE DEFAULT NULL,
    data_demissao    DATE DEFAULT NULL,
    departamento_id  INT UNSIGNED DEFAULT NULL,
    cargo_id         INT UNSIGNED DEFAULT NULL,
    tipo_contrato    ENUM('clt','pj','estagio','temporario') NOT NULL DEFAULT 'clt',
    salario          DECIMAL(10,2) DEFAULT 0,
    status           ENUM('ativo','ferias','afastado','inativo') NOT NULL DEFAULT 'ativo',
    endereco         VARCHAR(255) DEFAULT NULL,
    estado_civil     VARCHAR(30) DEFAULT NULL,
    nivel_educacao   VARCHAR(60) DEFAULT NULL,
    observacoes      TEXT DEFAULT NULL,
    criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    atualizado_em    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_colaboradores_cpf (cpf),
    KEY idx_colaboradores_status (status),
    KEY idx_colaboradores_departamento (departamento_id),
    KEY idx_colaboradores_cargo (cargo_id),
    CONSTRAINT fk_colaboradores_departamento FOREIGN KEY (departamento_id)
        REFERENCES departamentos (id) ON DELETE SET NULL,
    CONSTRAINT fk_colaboradores_cargo FOREIGN KEY (cargo_id)
        REFERENCES cargos (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------ Frequencia -------------------------
CREATE TABLE IF NOT EXISTS frequencias (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    colaborador_id    INT UNSIGNED NOT NULL,
    data              DATE NOT NULL,
    entrada           TIME DEFAULT NULL,
    saida             TIME DEFAULT NULL,
    horas_trabalhadas DECIMAL(5,2) DEFAULT 0,
    horas_extras      DECIMAL(5,2) DEFAULT 0,
    status            ENUM('presente','falta','atraso','ausente','ferias','meio_periodo') NOT NULL DEFAULT 'presente',
    observacoes       VARCHAR(255) DEFAULT NULL,
    criado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uk_frequencia (colaborador_id, data),
    KEY idx_frequencias_data (data),
    CONSTRAINT fk_frequencias_colaborador FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------ Ausencias / Ferias / Afastamentos -------
CREATE TABLE IF NOT EXISTS ausencias (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    colaborador_id   INT UNSIGNED NOT NULL,
    tipo             ENUM('ferias','atestado','falta','licenca','afastamento') NOT NULL DEFAULT 'ferias',
    data_inicio      DATE NOT NULL,
    data_fim         DATE NOT NULL,
    dias             SMALLINT NOT NULL DEFAULT 1,
    status           ENUM('pendente','aprovado','reprovado') NOT NULL DEFAULT 'pendente',
    justificativa    TEXT DEFAULT NULL,
    criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_ausencias_colaborador (colaborador_id),
    KEY idx_ausencias_status (status),
    CONSTRAINT fk_ausencias_colaborador FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------- Recrutamento -------------------------
CREATE TABLE IF NOT EXISTS vagas (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo            VARCHAR(140) NOT NULL,
    departamento_id   INT UNSIGNED DEFAULT NULL,
    tipo_contrato     ENUM('clt','pj','estagio','temporario') NOT NULL DEFAULT 'clt',
    descricao         TEXT DEFAULT NULL,
    requisitos        TEXT DEFAULT NULL,
    quantidade        SMALLINT NOT NULL DEFAULT 1,
    status            ENUM('aberta','em_analise','fechada','cancelada') NOT NULL DEFAULT 'aberta',
    data_abertura     DATE NOT NULL,
    data_fechamento   DATE DEFAULT NULL,
    responsavel_id    INT UNSIGNED DEFAULT NULL,
    criado_em         DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_vagas_status (status),
    CONSTRAINT fk_vagas_departamento FOREIGN KEY (departamento_id)
        REFERENCES departamentos (id) ON DELETE SET NULL,
    CONSTRAINT fk_vagas_responsavel FOREIGN KEY (responsavel_id)
        REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS candidatos (
    id               INT UNSIGNED NOT NULL AUTO_INCREMENT,
    vaga_id          INT UNSIGNED NOT NULL,
    nome             VARCHAR(140) NOT NULL,
    email            VARCHAR(160) DEFAULT NULL,
    telefone         VARCHAR(20) DEFAULT NULL,
    etapa            ENUM('candidatura','analise','entrevista','ofertado','contratado','descartado') NOT NULL DEFAULT 'candidatura',
    status           ENUM('ativo','encerrado') NOT NULL DEFAULT 'ativo',
    experiencia      VARCHAR(255) DEFAULT NULL,
    observacoes      TEXT DEFAULT NULL,
    data_candidatura DATE NOT NULL,
    criado_em        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_candidatos_vaga (vaga_id),
    CONSTRAINT fk_candidatos_vaga FOREIGN KEY (vaga_id)
        REFERENCES vagas (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -------------------- Treinamentos --------------------------
CREATE TABLE IF NOT EXISTS treinamentos (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    titulo         VARCHAR(160) NOT NULL,
    descricao      TEXT DEFAULT NULL,
    categoria      VARCHAR(60) NOT NULL DEFAULT 'Geral',
    carga_horaria  DECIMAL(5,2) NOT NULL DEFAULT 0,
    data_inicio    DATE DEFAULT NULL,
    data_fim       DATE DEFAULT NULL,
    instrutor      VARCHAR(140) DEFAULT NULL,
    vagas          SMALLINT NOT NULL DEFAULT 20,
    custo          DECIMAL(10,2) DEFAULT 0,
    criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_treinamentos_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS inscricoes_treinamento (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT,
    treinamento_id    INT UNSIGNED NOT NULL,
    colaborador_id    INT UNSIGNED NOT NULL,
    status            ENUM('inscrito','em_andamento','concluido','reprovado') NOT NULL DEFAULT 'inscrito',
    progresso         SMALLINT NOT NULL DEFAULT 0,
    nota              DECIMAL(4,2) DEFAULT NULL,
    data_inscricao    DATE NOT NULL,
    data_conclusao    DATE DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_inscricao (treinamento_id, colaborador_id),
    KEY idx_inscricoes_colaborador (colaborador_id),
    CONSTRAINT fk_inscricoes_treinamento FOREIGN KEY (treinamento_id)
        REFERENCES treinamentos (id) ON DELETE CASCADE,
    CONSTRAINT fk_inscricoes_colaborador FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------- Desempenho / Avaliacoes -------------------
CREATE TABLE IF NOT EXISTS avaliacoes_desempenho (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    colaborador_id  INT UNSIGNED NOT NULL,
    ciclo           VARCHAR(60) NOT NULL,
    avaliador_id    INT UNSIGNED DEFAULT NULL,
    data_avaliacao  DATE NOT NULL,
    nota_geral      DECIMAL(4,2) NOT NULL DEFAULT 0,
    criterios       TEXT DEFAULT NULL,
    comentarios     TEXT DEFAULT NULL,
    status          ENUM('pendente','em_andamento','concluida') NOT NULL DEFAULT 'pendente',
    criado_em       DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_avaliacoes_colaborador (colaborador_id),
    KEY idx_avaliacoes_ciclo (ciclo),
    CONSTRAINT fk_avaliacoes_colaborador FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores (id) ON DELETE CASCADE,
    CONSTRAINT fk_avaliacoes_avaliador FOREIGN KEY (avaliador_id)
        REFERENCES usuarios (id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------ Documentos -------------------------
CREATE TABLE IF NOT EXISTS documentos (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    colaborador_id INT UNSIGNED DEFAULT NULL,
    nome           VARCHAR(180) NOT NULL,
    tipo           VARCHAR(60) NOT NULL DEFAULT 'outro',
    caminho        VARCHAR(255) DEFAULT NULL,
    data_emissao   DATE DEFAULT NULL,
    data_validade  DATE DEFAULT NULL,
    observacoes    VARCHAR(255) DEFAULT NULL,
    criado_em      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_documentos_colaborador (colaborador_id),
    KEY idx_documentos_validade (data_validade),
    CONSTRAINT fk_documentos_colaborador FOREIGN KEY (colaborador_id)
        REFERENCES colaboradores (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;

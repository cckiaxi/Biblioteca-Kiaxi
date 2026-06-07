-- ============================================================
--  SISTEMA DE GESTÃO DE BIBLIOTECA — ISPKIAXI
--  Script SQL — Criação do Banco de Dados
--  Autor : Jose António Fonseca
--  Curso : Engenharia de Software II — 3.º Ano
--  Data  : Junho 2026
-- ============================================================

CREATE DATABASE IF NOT EXISTS biblioteca_ispkiaxi
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE biblioteca_ispkiaxi;

-- ────────────────────────────────────────────────────────────
--  TABELA: funcionarios     (Classe Funcionario.php)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS funcionarios (
    id              INT              AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100)     NOT NULL,
    bi              VARCHAR(20)      NOT NULL UNIQUE COMMENT 'Bilhete de Identidade',
    email           VARCHAR(120)     NOT NULL UNIQUE,
    telefone        VARCHAR(20)      NOT NULL,
    cargo           ENUM(
                        'bibliotecario',
                        'auxiliar',
                        'gestor',
                        'administrador'
                    )                NOT NULL DEFAULT 'bibliotecario',
    senha           VARCHAR(255)     NOT NULL COMMENT 'Hash bcrypt',
    data_admissao   DATE             NOT NULL,
    activo          TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_func_cargo  (cargo),
    INDEX idx_func_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ────────────────────────────────────────────────────────────
--  TABELA: alunos           (criada pelo colega — Classe Aluno.php)
--  Incluída aqui para garantir a integridade referencial
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS alunos (
    id              INT              AUTO_INCREMENT PRIMARY KEY,
    nome            VARCHAR(100)     NOT NULL,
    numero_aluno    VARCHAR(30)      NOT NULL UNIQUE COMMENT 'Nº de matrícula ISPKIAXI',
    bi              VARCHAR(20)      NOT NULL UNIQUE,
    email           VARCHAR(120)     NOT NULL UNIQUE,
    telefone        VARCHAR(20)      NOT NULL,
    curso           VARCHAR(80)      NOT NULL,
    ano_frequencia  TINYINT UNSIGNED NOT NULL DEFAULT 1,
    periodo         ENUM('manha','tarde','noite') NOT NULL DEFAULT 'noite',
    activo          TINYINT(1)       NOT NULL DEFAULT 1,
    created_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_aluno_curso  (curso),
    INDEX idx_aluno_activo (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ────────────────────────────────────────────────────────────
--  TABELA: livros           (criada pelo colega — Classe Livro.php)
--  Incluída aqui para garantir a integridade referencial
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS livros (
    id              INT              AUTO_INCREMENT PRIMARY KEY,
    titulo          VARCHAR(200)     NOT NULL,
    autor           VARCHAR(150)     NOT NULL,
    isbn            VARCHAR(20)      NOT NULL UNIQUE,
    editora         VARCHAR(100)     NOT NULL,
    ano_publicacao  YEAR             NOT NULL,
    categoria       VARCHAR(60)      NOT NULL,
    quantidade      INT UNSIGNED     NOT NULL DEFAULT 1 COMMENT 'Exemplares no acervo',
    disponivel      INT UNSIGNED     NOT NULL DEFAULT 1 COMMENT 'Exemplares disponíveis',
    localizacao     VARCHAR(50)      NULL COMMENT 'Estante/prateleira',
    created_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
                                     ON UPDATE CURRENT_TIMESTAMP,

    INDEX idx_livro_categoria (categoria),
    FULLTEXT INDEX ft_livro_titulo (titulo),
    FULLTEXT INDEX ft_livro_autor  (autor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ────────────────────────────────────────────────────────────
--  TABELA: emprestimos      (Classe Emprestimo.php)
-- ────────────────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS emprestimos (
    id               INT              AUTO_INCREMENT PRIMARY KEY,
    aluno_id         INT              NOT NULL,
    livro_id         INT              NOT NULL,
    funcionario_id   INT              NOT NULL,
    data_emprestimo  DATE             NOT NULL,
    data_prevista    DATE             NOT NULL COMMENT 'Data limite de devolução',
    data_devolucao   DATE             NULL     COMMENT 'Preenchida ao devolver',
    estado           ENUM(
                         'activo',
                         'devolvido',
                         'atrasado',
                         'renovado'
                     )                NOT NULL DEFAULT 'activo',
    multa            DECIMAL(8,2)     NOT NULL DEFAULT 0.00 COMMENT 'Multa em AOA',
    observacoes      TEXT             NULL,
    created_at       TIMESTAMP        DEFAULT CURRENT_TIMESTAMP,
    updated_at       TIMESTAMP        DEFAULT CURRENT_TIMESTAMP
                                      ON UPDATE CURRENT_TIMESTAMP,

    -- Chaves estrangeiras
    CONSTRAINT fk_emp_aluno
        FOREIGN KEY (aluno_id)
        REFERENCES alunos(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_emp_livro
        FOREIGN KEY (livro_id)
        REFERENCES livros(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_emp_funcionario
        FOREIGN KEY (funcionario_id)
        REFERENCES funcionarios(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    -- Índices de performance
    INDEX idx_emp_aluno     (aluno_id),
    INDEX idx_emp_livro     (livro_id),
    INDEX idx_emp_func      (funcionario_id),
    INDEX idx_emp_estado    (estado),
    INDEX idx_emp_prevista  (data_prevista)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- ────────────────────────────────────────────────────────────
--  DADOS DE EXEMPLO
-- ────────────────────────────────────────────────────────────

-- Funcionário administrador padrão (senha: Admin@ISPK2026)
INSERT INTO funcionarios (nome, bi, email, telefone, cargo, senha, data_admissao)
VALUES (
    'Jose António Fonseca',
    '005123456LA041',
    'jose.fonseca@ispkiaxi.ao',
    '+244923000001',
    'administrador',
    '$2y$12$ExemploBcryptHashAqui.substitua.pelo.hash.real',
    '2026-01-15'
);

-- Aluno de exemplo
INSERT INTO alunos (nome, numero_aluno, bi, email, telefone, curso, ano_frequencia, periodo)
VALUES (
    'Ana Maria Rodrigues',
    'ISPK-2024-001',
    '003987654LA042',
    'ana.rodrigues@estudante.ispkiaxi.ao',
    '+244912000002',
    'Engenharia de Software',
    3,
    'noite'
);

-- Livro de exemplo
INSERT INTO livros (titulo, autor, isbn, editora, ano_publicacao, categoria, quantidade, disponivel, localizacao)
VALUES (
    'Engenharia de Software',
    'Ian Sommerville',
    '978-85-7605-344-4',
    'Pearson',
    2019,
    'Informática',
    3,
    2,
    'A-12'
);

-- ────────────────────────────────────────────────────────────
--  FIM DO SCRIPT
-- ────────────────────────────────────────────────────────────

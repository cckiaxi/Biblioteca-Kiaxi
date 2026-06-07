<?php

/**
 * ============================================================
 *  SISTEMA DE GESTÃO DE BIBLIOTECA — ISPKIAXI
 *  Classe: Emprestimo
 *  Autor : Jose António Fonseca
 *  Curso : Engenharia de Software II — 3.º Ano
 *  Data  : Junho 2026
 * ============================================================
 *
 *  RELACIONAMENTOS:
 *    emprestimos.aluno_id       → alunos.id        (criada por colega)
 *    emprestimos.livro_id       → livros.id         (criada por colega)
 *    emprestimos.funcionario_id → funcionarios.id   (esta classe)
 *
 *  TABELA REFLECTIDA: emprestimos
 *
 *  CREATE TABLE emprestimos (
 *      id               INT AUTO_INCREMENT PRIMARY KEY,
 *      aluno_id         INT          NOT NULL,
 *      livro_id         INT          NOT NULL,
 *      funcionario_id   INT          NOT NULL,
 *      data_emprestimo  DATE         NOT NULL,
 *      data_prevista    DATE         NOT NULL,
 *      data_devolucao   DATE             NULL,
 *      estado           ENUM(
 *                           'activo',
 *                           'devolvido',
 *                           'atrasado',
 *                           'renovado'
 *                       )            NOT NULL DEFAULT 'activo',
 *      multa            DECIMAL(8,2) NOT NULL DEFAULT 0.00,
 *      observacoes      TEXT             NULL,
 *      created_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
 *      updated_at       TIMESTAMP    DEFAULT CURRENT_TIMESTAMP
 *                                    ON UPDATE CURRENT_TIMESTAMP,
 *
 *      CONSTRAINT fk_emp_aluno
 *          FOREIGN KEY (aluno_id)       REFERENCES alunos(id),
 *      CONSTRAINT fk_emp_livro
 *          FOREIGN KEY (livro_id)       REFERENCES livros(id),
 *      CONSTRAINT fk_emp_funcionario
 *          FOREIGN KEY (funcionario_id) REFERENCES funcionarios(id)
 *  );
 *
 *  ÍNDICES RECOMENDADOS:
 *      CREATE INDEX idx_emp_aluno      ON emprestimos (aluno_id);
 *      CREATE INDEX idx_emp_livro      ON emprestimos (livro_id);
 *      CREATE INDEX idx_emp_estado     ON emprestimos (estado);
 *      CREATE INDEX idx_emp_devolucao  ON emprestimos (data_prevista);
 */

require_once __DIR__ . '/Database.php';

class Emprestimo
{
    /* ── Constantes de negócio ──────────────────────────── */

    /** Dias padrão de empréstimo para alunos */
    public const PRAZO_PADRAO_DIAS  = 14;

    /** Multa por dia de atraso (em kwanzas) */
    public const MULTA_POR_DIA_AOA  = 50.00;

    /** Estados possíveis */
    private const ESTADOS_VALIDOS   = ['activo', 'devolvido', 'atrasado', 'renovado'];

    /* ── Atributos ─────────────────────────────────────── */
    private ?int    $id             = null;
    private int     $alunoId;          // FK → alunos.id
    private int     $livroId;          // FK → livros.id
    private int     $funcionarioId;    // FK → funcionarios.id (quem registou)
    private string  $dataEmprestimo;   // YYYY-MM-DD
    private string  $dataPrevista;     // YYYY-MM-DD (data limite de devolução)
    private ?string $dataDevolucao    = null; // preenchida ao devolver
    private string  $estado           = 'activo';
    private float   $multa            = 0.00;
    private ?string $observacoes      = null;

    /* ── Construtor ────────────────────────────────────── */
    public function __construct(
        int    $alunoId,
        int    $livroId,
        int    $funcionarioId,
        string $dataEmprestimo = '',
        int    $prazoDias      = self::PRAZO_PADRAO_DIAS
    ) {
        $this->setAlunoId($alunoId);
        $this->setLivroId($livroId);
        $this->setFuncionarioId($funcionarioId);

        $this->dataEmprestimo = $dataEmprestimo ?: date('Y-m-d');
        $this->dataPrevista   = date(
            'Y-m-d',
            strtotime($this->dataEmprestimo . " +{$prazoDias} days")
        );
    }

    /* ═══════════════════════════════════════════════════
     *  GETTERS
     * ═══════════════════════════════════════════════════ */
    public function getId(): ?int            { return $this->id; }
    public function getAlunoId(): int        { return $this->alunoId; }
    public function getLivroId(): int        { return $this->livroId; }
    public function getFuncionarioId(): int  { return $this->funcionarioId; }
    public function getDataEmprestimo(): string { return $this->dataEmprestimo; }
    public function getDataPrevista(): string   { return $this->dataPrevista; }
    public function getDataDevolucao(): ?string { return $this->dataDevolucao; }
    public function getEstado(): string      { return $this->estado; }
    public function getMulta(): float        { return $this->multa; }
    public function getObservacoes(): ?string { return $this->observacoes; }

    /* ═══════════════════════════════════════════════════
     *  SETTERS
     * ═══════════════════════════════════════════════════ */
    public function setAlunoId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("alunoId deve ser um inteiro positivo.");
        }
        $this->alunoId = $id;
    }

    public function setLivroId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("livroId deve ser um inteiro positivo.");
        }
        $this->livroId = $id;
    }

    public function setFuncionarioId(int $id): void
    {
        if ($id <= 0) {
            throw new InvalidArgumentException("funcionarioId deve ser um inteiro positivo.");
        }
        $this->funcionarioId = $id;
    }

    public function setEstado(string $estado): void
    {
        $estado = strtolower(trim($estado));
        if (!in_array($estado, self::ESTADOS_VALIDOS, true)) {
            throw new InvalidArgumentException(
                "Estado inválido. Valores aceites: " . implode(', ', self::ESTADOS_VALIDOS)
            );
        }
        $this->estado = $estado;
    }

    public function setObservacoes(?string $obs): void
    {
        $this->observacoes = $obs ? trim($obs) : null;
    }

    /* ═══════════════════════════════════════════════════
     *  LÓGICA DE NEGÓCIO
     * ═══════════════════════════════════════════════════ */

    /**
     * Verifica se o empréstimo está em atraso em relação à data actual.
     */
    public function estaAtrasado(): bool
    {
        if ($this->estado === 'devolvido') {
            return false;
        }
        return strtotime(date('Y-m-d')) > strtotime($this->dataPrevista);
    }

    /**
     * Calcula o número de dias em atraso (0 se não houver atraso).
     */
    public function diasAtraso(): int
    {
        if (!$this->estaAtrasado()) {
            return 0;
        }
        $referencia = $this->dataDevolucao ?? date('Y-m-d');
        $diff = (strtotime($referencia) - strtotime($this->dataPrevista)) / 86400;
        return (int) max(0, $diff);
    }

    /**
     * Calcula e actualiza a multa com base nos dias em atraso.
     */
    public function calcularMulta(): float
    {
        $this->multa = $this->diasAtraso() * self::MULTA_POR_DIA_AOA;
        return $this->multa;
    }

    /**
     * Regista a devolução do livro:
     *   – define a data de devolução
     *   – calcula a multa (se houver atraso)
     *   – actualiza o estado para 'devolvido'
     *   – persiste as alterações no banco
     */
    public function registarDevolucao(?string $data = null): bool
    {
        if ($this->estado === 'devolvido') {
            throw new RuntimeException("Este empréstimo já foi devolvido.");
        }

        $this->dataDevolucao = $data ?? date('Y-m-d');
        $this->calcularMulta();
        $this->estado = 'devolvido';

        return $this->actualizar();
    }

    /**
     * Renova o empréstimo por mais N dias (só permitido 1 vez e sem atraso).
     */
    public function renovar(int $diasExtra = self::PRAZO_PADRAO_DIAS): bool
    {
        if ($this->estado !== 'activo') {
            throw new RuntimeException("Apenas empréstimos activos podem ser renovados.");
        }
        if ($this->estaAtrasado()) {
            throw new RuntimeException("Não é possível renovar: empréstimo em atraso.");
        }

        $this->dataPrevista = date(
            'Y-m-d',
            strtotime($this->dataPrevista . " +{$diasExtra} days")
        );
        $this->estado = 'renovado';

        return $this->actualizar();
    }

    /* ═══════════════════════════════════════════════════
     *  PERSISTÊNCIA — CRUD
     * ═══════════════════════════════════════════════════ */

    /**
     * Insere o empréstimo no banco de dados.
     */
    public function salvar(): int
    {
        $pdo = Database::getConnection();

        // Verifica se o livro já está emprestado (activo ou renovado)
        $chk = $pdo->prepare(
            "SELECT COUNT(*) FROM emprestimos
             WHERE livro_id = :livro_id
               AND estado IN ('activo','renovado')"
        );
        $chk->execute([':livro_id' => $this->livroId]);
        if ($chk->fetchColumn() > 0) {
            throw new RuntimeException(
                "O livro (ID {$this->livroId}) já se encontra emprestado."
            );
        }

        $sql = "INSERT INTO emprestimos
                    (aluno_id, livro_id, funcionario_id,
                     data_emprestimo, data_prevista,
                     data_devolucao, estado, multa, observacoes)
                VALUES
                    (:aluno_id, :livro_id, :funcionario_id,
                     :data_emprestimo, :data_prevista,
                     :data_devolucao, :estado, :multa, :observacoes)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':aluno_id'        => $this->alunoId,
            ':livro_id'        => $this->livroId,
            ':funcionario_id'  => $this->funcionarioId,
            ':data_emprestimo' => $this->dataEmprestimo,
            ':data_prevista'   => $this->dataPrevista,
            ':data_devolucao'  => $this->dataDevolucao,
            ':estado'          => $this->estado,
            ':multa'           => $this->multa,
            ':observacoes'     => $this->observacoes,
        ]);

        $this->id = (int) $pdo->lastInsertId();
        return $this->id;
    }

    /**
     * Actualiza o registo existente no banco.
     */
    public function actualizar(): bool
    {
        if ($this->id === null) {
            throw new RuntimeException("Não é possível actualizar: empréstimo sem ID.");
        }

        $pdo = Database::getConnection();

        $sql = "UPDATE emprestimos SET
                    aluno_id        = :aluno_id,
                    livro_id        = :livro_id,
                    funcionario_id  = :funcionario_id,
                    data_emprestimo = :data_emprestimo,
                    data_prevista   = :data_prevista,
                    data_devolucao  = :data_devolucao,
                    estado          = :estado,
                    multa           = :multa,
                    observacoes     = :observacoes
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':aluno_id'        => $this->alunoId,
            ':livro_id'        => $this->livroId,
            ':funcionario_id'  => $this->funcionarioId,
            ':data_emprestimo' => $this->dataEmprestimo,
            ':data_prevista'   => $this->dataPrevista,
            ':data_devolucao'  => $this->dataDevolucao,
            ':estado'          => $this->estado,
            ':multa'           => $this->multa,
            ':observacoes'     => $this->observacoes,
            ':id'              => $this->id,
        ]);
    }

    /* ── Métodos estáticos de leitura ───────────────────── */

    /**
     * Busca empréstimo pelo ID.
     */
    public static function buscarPorId(int $id): ?self
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM emprestimos WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? self::hidratarDeArray($row) : null;
    }

    /**
     * Lista todos os empréstimos activos (activo + renovado).
     * @return self[]
     */
    public static function listarActivos(): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT * FROM emprestimos
             WHERE estado IN ('activo','renovado')
             ORDER BY data_prevista ASC"
        );
        return array_map([self::class, 'hidratarDeArray'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Lista empréstimos em atraso (data_prevista < hoje e não devolvidos).
     * @return self[]
     */
    public static function listarAtrasados(): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM emprestimos
             WHERE data_prevista < CURDATE()
               AND estado IN ('activo','renovado')
             ORDER BY data_prevista ASC"
        );
        $stmt->execute();
        return array_map([self::class, 'hidratarDeArray'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Histórico de empréstimos de um aluno.
     * @return self[]
     */
    public static function listarPorAluno(int $alunoId): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT * FROM emprestimos
             WHERE aluno_id = :aluno_id
             ORDER BY data_emprestimo DESC"
        );
        $stmt->execute([':aluno_id' => $alunoId]);
        return array_map([self::class, 'hidratarDeArray'], $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    /**
     * Actualiza o estado de todos os empréstimos expirados para 'atrasado'.
     * Deve ser chamado por um cron job diário.
     */
    public static function actualizarAtrasados(): int
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE emprestimos
             SET estado = 'atrasado'
             WHERE data_prevista < CURDATE()
               AND estado IN ('activo','renovado')"
        );
        $stmt->execute();
        return $stmt->rowCount();
    }

    /* ── Hidratação interna ─────────────────────────────── */
    private static function hidratarDeArray(array $row): self
    {
        $obj = new self(
            (int) $row['aluno_id'],
            (int) $row['livro_id'],
            (int) $row['funcionario_id'],
            $row['data_emprestimo']
        );
        $obj->id             = (int) $row['id'];
        $obj->dataPrevista   = $row['data_prevista'];
        $obj->dataDevolucao  = $row['data_devolucao'];
        $obj->estado         = $row['estado'];
        $obj->multa          = (float) $row['multa'];
        $obj->observacoes    = $row['observacoes'];
        return $obj;
    }

    /* ── Representação legível ──────────────────────────── */
    public function __toString(): string
    {
        return sprintf(
            "[Empréstimo #%d] Aluno: %d | Livro: %d | Estado: %s | Devolução prevista: %s | Multa: %.2f AOA",
            $this->id ?? 0,
            $this->alunoId,
            $this->livroId,
            $this->estado,
            $this->dataPrevista,
            $this->multa
        );
    }
}

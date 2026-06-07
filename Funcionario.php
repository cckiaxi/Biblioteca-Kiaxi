<?php

/**
 * ============================================================
 *  SISTEMA DE GESTÃO DE BIBLIOTECA — ISPKIAXI
 *  Classe: Funcionario
 *  Autor : Jose António Fonseca
 *  Curso : Engenharia de Software II — 3.º Ano
 *  Data  : Junho 2026
 * ============================================================
 *
 *  TABELA REFLECTIDA: funcionarios
 *
 *  CREATE TABLE funcionarios (
 *      id              INT AUTO_INCREMENT PRIMARY KEY,
 *      nome            VARCHAR(100)        NOT NULL,
 *      bi              VARCHAR(20)         NOT NULL UNIQUE,
 *      email           VARCHAR(120)        NOT NULL UNIQUE,
 *      telefone        VARCHAR(20)         NOT NULL,
 *      cargo           ENUM(
 *                          'bibliotecario',
 *                          'auxiliar',
 *                          'gestor',
 *                          'administrador'
 *                      )                   NOT NULL DEFAULT 'bibliotecario',
 *      senha           VARCHAR(255)        NOT NULL,
 *      data_admissao   DATE                NOT NULL,
 *      activo          TINYINT(1)          NOT NULL DEFAULT 1,
 *      created_at      TIMESTAMP           DEFAULT CURRENT_TIMESTAMP,
 *      updated_at      TIMESTAMP           DEFAULT CURRENT_TIMESTAMP
 *                                          ON UPDATE CURRENT_TIMESTAMP
 *  );
 */

require_once __DIR__ . '/Database.php';

class Funcionario
{
    /* ── Atributos ─────────────────────────────────────── */
    private ?int    $id            = null;
    private string  $nome;
    private string  $bi;            // Bilhete de Identidade
    private string  $email;
    private string  $telefone;
    private string  $cargo;         // bibliotecario | auxiliar | gestor | administrador
    private string  $senha;         // hash bcrypt
    private string  $dataAdmissao; // YYYY-MM-DD
    private bool    $activo        = true;

    /** Cargos permitidos no sistema */
    private const CARGOS_VALIDOS = [
        'bibliotecario',
        'auxiliar',
        'gestor',
        'administrador',
    ];

    /* ── Construtor ────────────────────────────────────── */
    public function __construct(
        string $nome,
        string $bi,
        string $email,
        string $telefone,
        string $cargo,
        string $senhaPlana,
        string $dataAdmissao = ''
    ) {
        $this->setNome($nome);
        $this->setBi($bi);
        $this->setEmail($email);
        $this->setTelefone($telefone);
        $this->setCargo($cargo);
        $this->setSenha($senhaPlana);
        $this->dataAdmissao = $dataAdmissao ?: date('Y-m-d');
    }

    /* ═══════════════════════════════════════════════════
     *  GETTERS
     * ═══════════════════════════════════════════════════ */
    public function getId(): ?int    { return $this->id; }
    public function getNome(): string { return $this->nome; }
    public function getBi(): string   { return $this->bi; }
    public function getEmail(): string { return $this->email; }
    public function getTelefone(): string { return $this->telefone; }
    public function getCargo(): string    { return $this->cargo; }
    public function getDataAdmissao(): string { return $this->dataAdmissao; }
    public function isActivo(): bool  { return $this->activo; }

    /* ═══════════════════════════════════════════════════
     *  SETTERS  (com validação básica)
     * ═══════════════════════════════════════════════════ */
    public function setNome(string $nome): void
    {
        $nome = trim($nome);
        if (strlen($nome) < 3) {
            throw new InvalidArgumentException("O nome deve ter pelo menos 3 caracteres.");
        }
        $this->nome = $nome;
    }

    public function setBi(string $bi): void
    {
        $bi = trim($bi);
        if (empty($bi)) {
            throw new InvalidArgumentException("O BI não pode estar vazio.");
        }
        $this->bi = strtoupper($bi);
    }

    public function setEmail(string $email): void
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("E-mail inválido: $email");
        }
        $this->email = strtolower(trim($email));
    }

    public function setTelefone(string $telefone): void
    {
        // Aceita formatos angolanos: +244 9XXXXXXXX ou 9XXXXXXXX
        $limpo = preg_replace('/\s+/', '', $telefone);
        if (!preg_match('/^(\+244)?9\d{8}$/', $limpo)) {
            throw new InvalidArgumentException("Telefone inválido. Use o formato angolano: 9XXXXXXXX ou +2449XXXXXXXX");
        }
        $this->telefone = $limpo;
    }

    public function setCargo(string $cargo): void
    {
        $cargo = strtolower(trim($cargo));
        if (!in_array($cargo, self::CARGOS_VALIDOS, true)) {
            throw new InvalidArgumentException(
                "Cargo inválido. Valores aceites: " . implode(', ', self::CARGOS_VALIDOS)
            );
        }
        $this->cargo = $cargo;
    }

    public function setSenha(string $senhaPlana): void
    {
        if (strlen($senhaPlana) < 8) {
            throw new InvalidArgumentException("A senha deve ter pelo menos 8 caracteres.");
        }
        $this->senha = password_hash($senhaPlana, PASSWORD_BCRYPT);
    }

    public function setActivo(bool $activo): void
    {
        $this->activo = $activo;
    }

    /* ═══════════════════════════════════════════════════
     *  AUTENTICAÇÃO
     * ═══════════════════════════════════════════════════ */

    /**
     * Verifica se a senha informada corresponde ao hash armazenado.
     */
    public function verificarSenha(string $senhaPlana): bool
    {
        return password_verify($senhaPlana, $this->senha);
    }

    /* ═══════════════════════════════════════════════════
     *  PERSISTÊNCIA — CRUD
     * ═══════════════════════════════════════════════════ */

    /**
     * Insere o funcionário no banco de dados.
     * Retorna o ID gerado ou lança excepção em caso de erro.
     */
    public function salvar(): int
    {
        $pdo = Database::getConnection();

        $sql = "INSERT INTO funcionarios
                    (nome, bi, email, telefone, cargo, senha, data_admissao, activo)
                VALUES
                    (:nome, :bi, :email, :telefone, :cargo, :senha, :data_admissao, :activo)";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nome'          => $this->nome,
            ':bi'            => $this->bi,
            ':email'         => $this->email,
            ':telefone'      => $this->telefone,
            ':cargo'         => $this->cargo,
            ':senha'         => $this->senha,
            ':data_admissao' => $this->dataAdmissao,
            ':activo'        => (int) $this->activo,
        ]);

        $this->id = (int) $pdo->lastInsertId();
        return $this->id;
    }

    /**
     * Actualiza os dados do funcionário já existente.
     */
    public function actualizar(): bool
    {
        if ($this->id === null) {
            throw new RuntimeException("Não é possível actualizar: funcionário sem ID.");
        }

        $pdo = Database::getConnection();

        $sql = "UPDATE funcionarios SET
                    nome          = :nome,
                    bi            = :bi,
                    email         = :email,
                    telefone      = :telefone,
                    cargo         = :cargo,
                    senha         = :senha,
                    data_admissao = :data_admissao,
                    activo        = :activo
                WHERE id = :id";

        $stmt = $pdo->prepare($sql);
        return $stmt->execute([
            ':nome'          => $this->nome,
            ':bi'            => $this->bi,
            ':email'         => $this->email,
            ':telefone'      => $this->telefone,
            ':cargo'         => $this->cargo,
            ':senha'         => $this->senha,
            ':data_admissao' => $this->dataAdmissao,
            ':activo'        => (int) $this->activo,
            ':id'            => $this->id,
        ]);
    }

    /**
     * Desactiva o funcionário (soft delete — não apaga o registo).
     */
    public function desactivar(): bool
    {
        if ($this->id === null) {
            throw new RuntimeException("Funcionário sem ID.");
        }
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("UPDATE funcionarios SET activo = 0 WHERE id = :id");
        $ok   = $stmt->execute([':id' => $this->id]);
        if ($ok) {
            $this->activo = false;
        }
        return $ok;
    }

    /* ── Métodos estáticos de leitura ───────────────────── */

    /**
     * Devolve um Funcionario pelo seu ID ou null se não existir.
     */
    public static function buscarPorId(int $id): ?self
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::hidratarDeArray($row) : null;
    }

    /**
     * Devolve um Funcionario pelo e-mail (usado no login).
     */
    public static function buscarPorEmail(string $email): ?self
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("SELECT * FROM funcionarios WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => strtolower(trim($email))]);
        $row  = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? self::hidratarDeArray($row) : null;
    }

    /**
     * Lista todos os funcionários activos.
     * @return self[]
     */
    public static function listarActivos(): array
    {
        $pdo  = Database::getConnection();
        $stmt = $pdo->query("SELECT * FROM funcionarios WHERE activo = 1 ORDER BY nome");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map([self::class, 'hidratarDeArray'], $rows);
    }

    /* ── Hidratação interna ─────────────────────────────── */
    private static function hidratarDeArray(array $row): self
    {
        // Cria objecto sem passar pela validação de senha (já é hash)
        $obj = new self(
            $row['nome'],
            $row['bi'],
            $row['email'],
            $row['telefone'],
            $row['cargo'],
            'placeholder_12345', // senha temporária; será substituída abaixo
            $row['data_admissao']
        );
        // Sobrescreve o hash directamente (evita novo hash)
        $obj->senha  = $row['senha'];
        $obj->id     = (int) $row['id'];
        $obj->activo = (bool) $row['activo'];
        return $obj;
    }

    /* ── Representação legível ──────────────────────────── */
    public function __toString(): string
    {
        return sprintf(
            "[Funcionario #%d] %s | %s | %s | Activo: %s",
            $this->id ?? 0,
            $this->nome,
            $this->cargo,
            $this->email,
            $this->activo ? 'Sim' : 'Não'
        );
    }
}

<?php

/**
 * ============================================================
 *  SISTEMA DE GESTÃO DE BIBLIOTECA — ISPKIAXI
 *  Classe auxiliar: Database  (Singleton PDO)
 *  Autor: Jose António Fonseca
 * ============================================================
 */

class Database
{
    private static ?PDO $instance = null;

    // ── Configurações (ajustar conforme o ambiente) ──────
    private const DB_HOST    = 'localhost';
    private const DB_PORT    = '3306';
    private const DB_NAME    = 'biblioteca_ispkiaxi';
    private const DB_USER    = 'root';
    private const DB_PASS    = '';
    private const DB_CHARSET = 'utf8mb4';

    /** Impede instanciação directa */
    private function __construct() {}

    /**
     * Retorna (ou cria) a única instância PDO.
     */
    public static function getConnection(): PDO
    {
        if (self::$instance === null) {
            $dsn = sprintf(
                "mysql:host=%s;port=%s;dbname=%s;charset=%s",
                self::DB_HOST,
                self::DB_PORT,
                self::DB_NAME,
                self::DB_CHARSET
            );
            self::$instance = new PDO($dsn, self::DB_USER, self::DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$instance;
    }
}

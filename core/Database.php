<?php
// core/Database.php
namespace Core;

use PDO;
use PDOException;

class Database {
    private static ?PDO $instance = null;

    public static function connect(): PDO {
        if (self::$instance === null) {
            $config = require dirname(__DIR__) . '/config/database.php';
            $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['database']};charset={$config['charset']}";
            
            try {
                self::$instance = new PDO($dsn, $config['username'], $config['password'], $config['options']);
                // Nonaktifkan ONLY_FULL_GROUP_BY agar kompatibel penuh dengan MySQL 8.0 di Linux VPS
                @self::$instance->exec("SET SESSION sql_mode = (SELECT REPLACE(REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY,', ''), ',ONLY_FULL_GROUP_BY', ''))");
                // Pastikan koneksi selalu utf8mb4 agar karakter bahasa Arab (Mushaf) tidak berubah jadi tanda tanya/mojibake
                @self::$instance->exec("SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci");
            } catch (PDOException $e) {
                // Jangan ekspos kredensial database
                error_log("Database connection error: " . $e->getMessage());
                die("Koneksi ke database gagal. Pastikan MySQL telah berjalan dan database '{$config['database']}' tersedia.");
            }
        }
        return self::$instance;
    }

    public static function query(string $sql, array $params = []): \PDOStatement {
        $stmt = self::connect()->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public static function fetch(string $sql, array $params = []): ?array {
        $stmt = self::query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    public static function fetchAll(string $sql, array $params = []): array {
        $stmt = self::query($sql, $params);
        return $stmt->fetchAll();
    }

    public static function execute(string $sql, array $params = []): int {
        $stmt = self::query($sql, $params);
        return $stmt->rowCount();
    }

    public static function lastInsertId(): string {
        return self::connect()->lastInsertId();
    }

    public static function beginTransaction(): bool {
        return self::connect()->beginTransaction();
    }

    public static function commit(): bool {
        return self::connect()->commit();
    }

    public static function rollBack(): bool {
        return self::connect()->rollBack();
    }
}

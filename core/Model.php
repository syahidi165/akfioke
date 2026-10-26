<?php
// core/Model.php
namespace Core;

abstract class Model {
    protected static string $table = '';
    protected static string $primaryKey = 'id';

    /**
     * Membangun klausa ORDER BY yang aman dengan whitelist kolom dan arah sort
     */
    public static function buildSafeOrderBy(array $whitelist, ?string $sortCol, ?string $sortDir, string $defaultCol = 'id', string $defaultDir = 'DESC'): string {
        $column = in_array($sortCol, $whitelist, true) ? $sortCol : $defaultCol;
        $direction = strtoupper((string)$sortDir) === 'ASC' ? 'ASC' : 'DESC';
        return "ORDER BY {$column} {$direction}";
    }

    /**
     * Membangun klausa LIMIT & OFFSET dengan casting integer aman
     */
    public static function buildSafeLimit(int $page = 1, int $perPage = 15): array {
        $page = max(1, (int)$page);
        $perPage = max(1, min(100, (int)$perPage));
        $offset = ($page - 1) * $perPage;
        return [
            'limit_clause' => "LIMIT {$perPage} OFFSET {$offset}",
            'page'         => $page,
            'per_page'     => $perPage,
            'offset'       => $offset
        ];
    }

    public static function find($id): ?array {
        return Database::fetch("SELECT * FROM " . static::$table . " WHERE " . static::$primaryKey . " = ?", [$id]);
    }

    public static function all(): array {
        return Database::fetchAll("SELECT * FROM " . static::$table . " ORDER BY " . static::$primaryKey . " DESC");
    }

    public static function where(string $condition, array $params = []): array {
        return Database::fetchAll("SELECT * FROM " . static::$table . " WHERE {$condition}", $params);
    }

    public static function firstWhere(string $condition, array $params = []): ?array {
        return Database::fetch("SELECT * FROM " . static::$table . " WHERE {$condition} LIMIT 1", $params);
    }
}

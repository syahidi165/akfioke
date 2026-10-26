<?php
// core/Setting.php
namespace Core;

class Setting {
    private static array $cache = [];
    private static bool $loaded = false;

    private static array $defaults = [
        'app_name'       => 'Manajemen Sekolah Berbasis Komputer (MASEKO)',
        'app_short_name' => 'MASEKO',
        'app_sub_name'   => 'Manajemen Sekolah Berbasis Komputer',
        'app_logo'       => '',
    ];

    public static function ensureTable(): void {
        static $checked = false;
        if ($checked) return;
        Database::execute("CREATE TABLE IF NOT EXISTS app_settings (
            setting_key VARCHAR(50) PRIMARY KEY,
            setting_value TEXT NULL,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
        $checked = true;
    }

    private static function load(): void {
        self::ensureTable();
        $rows = Database::fetchAll("SELECT setting_key, setting_value FROM app_settings");
        foreach ($rows as $r) {
            self::$cache[$r['setting_key']] = $r['setting_value'];
        }
        self::$loaded = true;
    }

    public static function get(string $key, $default = null): ?string {
        if (!self::$loaded) {
            self::load();
        }
        if (array_key_exists($key, self::$cache)) {
            return self::$cache[$key];
        }
        return $default ?? (self::$defaults[$key] ?? null);
    }

    public static function set(string $key, ?string $value): void {
        self::ensureTable();
        Database::execute(
            "INSERT INTO app_settings (setting_key, setting_value, updated_at) 
             VALUES (?, ?, NOW()) 
             ON DUPLICATE KEY UPDATE setting_value = ?, updated_at = NOW()",
            [$key, $value, $value]
        );
        self::$cache[$key] = $value;
    }

    public static function all(): array {
        if (!self::$loaded) {
            self::load();
        }
        return array_merge(self::$defaults, self::$cache);
    }
}

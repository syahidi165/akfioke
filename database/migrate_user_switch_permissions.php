<?php
// database/migrate_user_switch_permissions.php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

use Core\Database;

try {
    Database::connect();
    echo "=== MIGRASI TABEL USER_SWITCH_PERMISSIONS ===\n";

    Database::execute("
        CREATE TABLE IF NOT EXISTS `user_switch_permissions` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `target_user_id` int(11) NOT NULL,
            `created_by` int(11) DEFAULT NULL,
            `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `user_target_unique` (`user_id`, `target_user_id`),
            KEY `user_id` (`user_id`),
            KEY `target_user_id` (`target_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    echo "[OK] Tabel user_switch_permissions berhasil dibuat/dipastikan ada.\n";
} catch (\Throwable $e) {
    echo "[ERROR] Migrasi gagal: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
// database/migrate_cross_unit_roles.php
declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';
require_once dirname(__DIR__) . '/core/Database.php';

use Core\Database;

try {
    Database::connect();
    echo "=== MIGRASI PENUGASAN PERAN LINTAS UNIT (user_roles.unit_id) ===\n";

    $col = Database::fetch(
        "SELECT COUNT(*) as c FROM information_schema.COLUMNS 
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'user_roles' AND COLUMN_NAME = 'unit_id'"
    );

    if ((int)($col['c'] ?? 0) === 0) {
        // Pastikan index biasa untuk FK user_id dan role_id tersedia sebelum mengubah PRIMARY KEY
        $indexes = Database::fetchAll("SHOW INDEX FROM `user_roles`");
        $idxNames = array_unique(array_column($indexes, 'Key_name'));

        if (!in_array('idx_ur_user_id', $idxNames, true)) {
            Database::execute("ALTER TABLE `user_roles` ADD INDEX `idx_ur_user_id` (`user_id`)");
        }
        if (!in_array('idx_ur_role_id', $idxNames, true)) {
            Database::execute("ALTER TABLE `user_roles` ADD INDEX `idx_ur_role_id` (`role_id`)");
        }

        Database::execute("ALTER TABLE `user_roles` ADD COLUMN `unit_id` INT NOT NULL DEFAULT 0 AFTER `role_id`");
        Database::execute("ALTER TABLE `user_roles` DROP PRIMARY KEY, ADD PRIMARY KEY (`user_id`, `role_id`, `unit_id`)");
        Database::execute("ALTER TABLE `user_roles` ADD INDEX `idx_ur_unit_id` (`unit_id`)");

        echo "[OK] Kolom unit_id dan PRIMARY KEY (user_id, role_id, unit_id) berhasil ditambahkan ke user_roles.\n";
    } else {
        echo "[SKIP] Kolom user_roles.unit_id sudah tersedia.\n";
    }
} catch (\Throwable $e) {
    echo "[ERROR] Migrasi lintas unit gagal: " . $e->getMessage() . "\n";
    exit(1);
}

<?php
// database/run_migration.php
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();
echo "=== MENJALANKAN MIGRASI DATABASE (2026-10-28) ===\n";

function columnExists(string $table, string $column): bool {
    $row = Database::fetch(
        "SELECT COUNT(*) as c FROM information_schema.COLUMNS 
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
        [$table, $column]
    );
    return ($row['c'] ?? 0) > 0;
}

// 1. Units
$unitCols = [
    'npsn'                        => "VARCHAR(50) NULL AFTER `level`",
    'akreditasi'                  => "VARCHAR(20) NULL DEFAULT 'A' AFTER `npsn`",
    'nomor_akreditasi'            => "VARCHAR(100) NULL AFTER `akreditasi`",
    'akreditasi_expired_at'       => "DATE NULL AFTER `nomor_akreditasi`",
    'nomor_izin_operasional'      => "VARCHAR(100) NULL AFTER `akreditasi_expired_at`",
    'izin_operasional_expired_at' => "DATE NULL AFTER `nomor_izin_operasional`",
];

foreach ($unitCols as $col => $def) {
    if (!columnExists('units', $col)) {
        Database::execute("ALTER TABLE `units` ADD COLUMN `{$col}` {$def}");
        echo "[OK] units.{$col} ditambahkan.\n";
    } else {
        echo "[SKIP] units.{$col} sudah ada.\n";
    }
}

// 2. unit_administrasi_deadlines
if (!columnExists('unit_administrasi_deadlines', 'deadline_datetime')) {
    Database::execute("ALTER TABLE `unit_administrasi_deadlines` ADD COLUMN `deadline_datetime` DATETIME NULL AFTER `deadline_date`");
    echo "[OK] unit_administrasi_deadlines.deadline_datetime ditambahkan.\n";
} else {
    echo "[SKIP] unit_administrasi_deadlines.deadline_datetime sudah ada.\n";
}

// 3. guru_berprestasi
Database::execute("ALTER TABLE `guru_berprestasi` MODIFY COLUMN `status` ENUM('Menunggu Verifikasi','Terverifikasi','Ditolak','Dalam Proses','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Verifikasi'");
echo "[OK] guru_berprestasi.status ENUM diperbarui.\n";

$guruCols = [
    'verified_by_user_id' => "INT(11) NULL AFTER `created_by_user_id`",
    'verified_at'         => "DATETIME NULL AFTER `verified_by_user_id`",
    'verification_notes'  => "TEXT NULL AFTER `verified_at`",
];

foreach ($guruCols as $col => $def) {
    if (!columnExists('guru_berprestasi', $col)) {
        Database::execute("ALTER TABLE `guru_berprestasi` ADD COLUMN `{$col}` {$def}");
        echo "[OK] guru_berprestasi.{$col} ditambahkan.\n";
    } else {
        echo "[SKIP] guru_berprestasi.{$col} sudah ada.\n";
    }
}

// 4. smtp_settings table
Database::execute(
    "CREATE TABLE IF NOT EXISTS `smtp_settings` (
        `id` INT(11) NOT NULL AUTO_INCREMENT,
        `unit_id` INT(11) NULL,
        `host` VARCHAR(150) NOT NULL,
        `port` INT(11) NOT NULL DEFAULT 587,
        `username` VARCHAR(150) NOT NULL,
        `password` VARCHAR(255) NOT NULL,
        `encryption` VARCHAR(20) NOT NULL DEFAULT 'tls',
        `from_email` VARCHAR(150) NOT NULL,
        `from_name` VARCHAR(150) NOT NULL,
        `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
        `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_unit_id` (`unit_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
);
echo "[OK] Tabel smtp_settings dipastikan tersedia.\n";

echo "=== MIGRASI SELESAI DENGAN SUKSES! ===\n";

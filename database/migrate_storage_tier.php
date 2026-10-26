<?php
// database/migrate_storage_tier.php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Core\Database;

try {
    echo "Memulai migrasi skema Hot & Cold Storage...\n";

    // 1. Tambah kolom di academic_years jika belum ada
    $cols = Database::fetchAll("SHOW COLUMNS FROM academic_years");
    $colNames = array_column($cols, 'Field');

    if (!in_array('storage_tier', $colNames)) {
        Database::execute("ALTER TABLE academic_years ADD COLUMN storage_tier VARCHAR(20) NOT NULL DEFAULT 'hot' AFTER is_locked");
        echo "- Kolom 'storage_tier' berhasil ditambahkan ke academic_years.\n";
    }

    if (!in_array('archived_at', $colNames)) {
        Database::execute("ALTER TABLE academic_years ADD COLUMN archived_at DATETIME NULL AFTER storage_tier");
        echo "- Kolom 'archived_at' berhasil ditambahkan ke academic_years.\n";
    }

    if (!in_array('archive_file_count', $colNames)) {
        Database::execute("ALTER TABLE academic_years ADD COLUMN archive_file_count INT NOT NULL DEFAULT 0 AFTER archived_at");
        echo "- Kolom 'archive_file_count' berhasil ditambahkan ke academic_years.\n";
    }

    if (!in_array('archive_total_size', $colNames)) {
        Database::execute("ALTER TABLE academic_years ADD COLUMN archive_total_size BIGINT NOT NULL DEFAULT 0 AFTER archive_file_count");
        echo "- Kolom 'archive_total_size' berhasil ditambahkan ke academic_years.\n";
    }

    if (!in_array('archive_meta', $colNames)) {
        Database::execute("ALTER TABLE academic_years ADD COLUMN archive_meta TEXT NULL AFTER archive_total_size");
        echo "- Kolom 'archive_meta' berhasil ditambahkan ke academic_years.\n";
    }

    // 2. Buat tabel archived_files untuk melacak pemindahan file individual
    Database::execute("CREATE TABLE IF NOT EXISTS archived_files (
        id BIGINT AUTO_INCREMENT PRIMARY KEY,
        academic_year_id INT NOT NULL,
        table_name VARCHAR(64) NOT NULL,
        record_id BIGINT NOT NULL,
        original_path VARCHAR(255) NOT NULL,
        cold_path VARCHAR(255) NOT NULL,
        file_size BIGINT NOT NULL DEFAULT 0,
        archived_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_archived_ta (academic_year_id),
        INDEX idx_archived_orig_path (original_path),
        INDEX idx_archived_table_record (table_name, record_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
    echo "- Tabel 'archived_files' berhasil diverifikasi/dibuat.\n";

    echo "Migrasi berhasil selesai tanpa kendala!\n";
} catch (\Throwable $e) {
    echo "Gagal migrasi: " . $e->getMessage() . "\n";
    exit(1);
}

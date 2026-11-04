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
    'is_active'                   => "TINYINT(1) NOT NULL DEFAULT 1 AFTER `logo_path`",
    'deleted_at'                  => "DATETIME NULL DEFAULT NULL AFTER `updated_at`",
    'deleted_by'                  => "INT NULL DEFAULT NULL AFTER `deleted_at`",
    'deletion_reason'             => "VARCHAR(255) NULL DEFAULT NULL AFTER `deleted_by`",
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

// 5. tahfidz_halaqah tutor_employee_id nullable
try {
    Database::execute("ALTER TABLE `tahfidz_halaqah` MODIFY COLUMN `tutor_employee_id` INT(11) NULL");
    echo "[OK] tahfidz_halaqah.tutor_employee_id dijadikan NULLABLE.\n";
} catch (\Throwable $e) {
    echo "[SKIP] tahfidz_halaqah.tutor_employee_id: " . $e->getMessage() . "\n";
}

// 6. Cleanup erroneous KS draft assessment for non-KS employees (e.g. Kepala Lab IPA)
try {
    Database::execute("
        DELETE a FROM akfi_assessments a
        JOIN employees e ON a.employee_id = e.id
        LEFT JOIN users u ON e.user_id = u.id
        LEFT JOIN user_roles ur ON u.id = ur.user_id
        LEFT JOIN roles r ON ur.role_id = r.id AND r.name = 'kepala_sekolah'
        WHERE a.template_id IN (8, 9, 10)
          AND a.status = 'Draft'
          AND r.id IS NULL
          AND e.level_posisi NOT LIKE '%Kepala Sekolah%'
          AND e.level_posisi NOT LIKE '%Kepsek%'
          AND e.level_posisi NOT LIKE '%Kanit%'
          AND e.level_posisi NOT LIKE '%Kepala Satuan%'
    ");
    echo "[OK] Pembersihan draft KS keliru untuk pegawai non-KS selesai.\n";
} catch (\Throwable $e) {
    echo "[SKIP] Cleanup draft KS: " . $e->getMessage() . "\n";
}

// 7. Role Guru TK/KB
try {
    $exists = Database::fetch("SELECT id FROM roles WHERE name = 'guru_tk'");
    if (!$exists) {
        Database::execute(
            "INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)",
            ['guru_tk', 'Guru TK/KB', 'Guru Satuan Pendidikan TK dan KB Al-Kahfi']
        );
        echo "[OK] Role guru_tk (Guru TK/KB) berhasil ditambahkan ke tabel roles.\n";
    } else {
        Database::execute("UPDATE roles SET display_name = 'Guru TK/KB' WHERE name = 'guru_tk'");
        echo "[SKIP] Role guru_tk sudah ada di tabel roles.\n";
    }
} catch (\Throwable $e) {
    echo "[SKIP] Role guru_tk: " . $e->getMessage() . "\n";
}

// 8. Role HRD Yayasan, Signature Path, dan 4-Pihak Tanda Tangan AKFI
try {
    if (!columnExists('employees', 'signature_path')) {
        Database::execute("ALTER TABLE employees ADD COLUMN signature_path VARCHAR(255) NULL AFTER email");
        echo "[OK] employees.signature_path ditambahkan.\n";
    }
    if (!columnExists('users', 'signature_path')) {
        Database::execute("ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) NULL AFTER phone");
        echo "[OK] users.signature_path ditambahkan.\n";
    }
    if (!columnExists('akfi_assessments', 'employee_signed_by')) {
        Database::execute("ALTER TABLE akfi_assessments ADD COLUMN employee_signed_by INT NULL AFTER absensi_score");
        Database::execute("ALTER TABLE akfi_assessments ADD COLUMN employee_signed_at DATETIME NULL AFTER employee_signed_by");
        echo "[OK] akfi_assessments.employee_signed_by ditambahkan.\n";
    }
    $hrdRole = Database::fetch("SELECT id FROM roles WHERE name = 'hrd'");
    if (!$hrdRole) {
        Database::execute(
            "INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)",
            ['hrd', 'HRD Yayasan', 'Human Resources Department Yayasan: verifikasi kepegawaian & pengesahan final rapor AKFI']
        );
        echo "[OK] Role hrd (HRD Yayasan) berhasil dibuat.\n";
    }
} catch (\Throwable $e) {
    echo "[SKIP] HRD/Signature migration: " . $e->getMessage() . "\n";
}

// 9. Tabel Kurikulum Struktur (FR - 9023a)
try {
    Database::execute("
        CREATE TABLE IF NOT EXISTS `kurikulum_struktur_meta` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `unit_id` int(11) NOT NULL,
          `academic_year_id` int(11) NOT NULL,
          `doc_number` varchar(50) DEFAULT 'FR – 9023a',
          `revision` varchar(20) DEFAULT '-',
          `title` varchar(200) DEFAULT 'PENGAJUAN STRUKTUR KURIKULUM',
          `kepsek_nama` varchar(150) DEFAULT NULL,
          `kepsek_telp` varchar(50) DEFAULT NULL,
          `sign_date_kepsek` date DEFAULT NULL,
          `deputi_nama` varchar(150) DEFAULT NULL,
          `sign_date_deputi` date DEFAULT NULL,
          `notes` text DEFAULT NULL,
          `status` enum('Draft','Diajukan','Disetujui') DEFAULT 'Disetujui',
          `created_at` datetime DEFAULT current_timestamp(),
          `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          UNIQUE KEY `uk_unit_ta` (`unit_id`,`academic_year_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "[OK] Tabel kurikulum_struktur_meta siap.\n";

    Database::execute("
        CREATE TABLE IF NOT EXISTS `kurikulum_struktur_items` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `unit_id` int(11) NOT NULL,
          `academic_year_id` int(11) NOT NULL,
          `subject_id` int(11) DEFAULT NULL,
          `order_num` int(11) DEFAULT 0,
          `item_number` varchar(20) DEFAULT NULL,
          `kelompok` varchar(100) NOT NULL,
          `nama_mapel` varchar(255) NOT NULL,
          `jp_1` varchar(20) DEFAULT NULL,
          `jp_2` varchar(20) DEFAULT NULL,
          `jp_3` varchar(20) DEFAULT NULL,
          `jp_4` varchar(20) DEFAULT NULL,
          `jp_5` varchar(20) DEFAULT NULL,
          `jp_6` varchar(20) DEFAULT NULL,
          `jp_7` varchar(20) DEFAULT NULL,
          `jp_8` varchar(20) DEFAULT NULL,
          `jp_9` varchar(20) DEFAULT NULL,
          `jp_10` varchar(20) DEFAULT NULL,
          `jp_11` varchar(20) DEFAULT NULL,
          `jp_12` varchar(20) DEFAULT NULL,
          `keterangan` varchar(255) DEFAULT NULL,
          `is_sub_item` tinyint(1) DEFAULT 0,
          `created_at` datetime DEFAULT current_timestamp(),
          `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
          PRIMARY KEY (`id`),
          KEY `idx_unit_ta` (`unit_id`,`academic_year_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    echo "[OK] Tabel kurikulum_struktur_items siap.\n";
} catch (\Throwable $e) {
    echo "[SKIP] Kurikulum Struktur migration: " . $e->getMessage() . "\n";
}

// 10. Migrasi Deputi & Direktur Pendidikan
try {
    require_once __DIR__ . '/migrate_direktur_deputi_units.php';
    echo "[OK] Migrasi Direktur & Deputi Units selesai.\n";
} catch (\Throwable $e) {
    echo "[SKIP] Migrasi Direktur/Deputi: " . $e->getMessage() . "\n";
}

// 11. Migrasi RAPBS & Master Indikator Rapor Deputi
try {
    require_once __DIR__ . '/migrate_deputi_rapbs.php';
    echo "[OK] Migrasi RAPBS & Master Indikator Rapor Deputi selesai.\n";
} catch (\Throwable $e) {
    echo "[SKIP] Migrasi RAPBS/Indikator Deputi: " . $e->getMessage() . "\n";
}

echo "=== MIGRASI SELESAI DENGAN SUKSES! ===\n";

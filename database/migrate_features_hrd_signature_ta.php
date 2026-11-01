<?php
// database/migrate_features_hrd_signature_ta.php
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();
echo "=== MIGRASI FITUR HRD, TANDA TANGAN DIGITAL, & TAHUN AJARAN ===\n";

function colExists(string $table, string $column): bool {
    $row = Database::fetch(
        "SELECT COUNT(*) as c FROM information_schema.COLUMNS 
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?",
        [$table, $column]
    );
    return ($row['c'] ?? 0) > 0;
}

// 1. Kolom signature_path di employees dan users
if (!colExists('employees', 'signature_path')) {
    Database::execute("ALTER TABLE employees ADD COLUMN signature_path VARCHAR(255) NULL AFTER email");
    echo "[OK] employees.signature_path ditambahkan.\n";
} else {
    echo "[SKIP] employees.signature_path sudah ada.\n";
}

if (!colExists('users', 'signature_path')) {
    Database::execute("ALTER TABLE users ADD COLUMN signature_path VARCHAR(255) NULL AFTER phone");
    echo "[OK] users.signature_path ditambahkan.\n";
} else {
    echo "[SKIP] users.signature_path sudah ada.\n";
}

// 2. Kolom tanda tangan di akfi_assessments untuk 4 pihak:
// - employee (self)
// - superior (KS untuk guru / Deputi untuk KS)
// - next_superior / direktur (Deputi untuk guru / Direktur untuk KS)
// - hr (HRD Yayasan)
if (!colExists('akfi_assessments', 'employee_signed_by')) {
    Database::execute("ALTER TABLE akfi_assessments ADD COLUMN employee_signed_by INT NULL AFTER absensi_score");
    Database::execute("ALTER TABLE akfi_assessments ADD COLUMN employee_signed_at DATETIME NULL AFTER employee_signed_by");
    echo "[OK] akfi_assessments.employee_signed_by & employee_signed_at ditambahkan.\n";
} else {
    echo "[SKIP] akfi_assessments.employee_signed_by sudah ada.\n";
}

// 3. Role HRD Yayasan
$hrdRole = Database::fetch("SELECT id FROM roles WHERE name = 'hrd'");
if (!$hrdRole) {
    Database::execute(
        "INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)",
        ['hrd', 'HRD Yayasan', 'Human Resources Department Yayasan: verifikasi kepegawaian & pengesahan final rapor AKFI']
    );
    echo "[OK] Role hrd (HRD Yayasan) berhasil dibuat.\n";
} else {
    echo "[SKIP] Role hrd sudah ada.\n";
}

echo "=== SELESAI ===\n";

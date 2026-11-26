<?php
// database/migrate_fix_walas_teacher_ids.php
// Migrasi idempoten: Normalisasi classes.walas_id dan tahfidz_halaqah.tutor_employee_id
// apabila masih menyimpan users.id (bukan employees.id) sehingga seluruh menu Walas/Guru konsisten.

require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();

// 1. Normalisasi classes.walas_id dari users.id -> employees.id (tanpa menyentuh kolom virtual walas_employee_id)
try {
    Database::execute("
        UPDATE classes c
        JOIN employees e ON e.user_id = c.walas_id AND e.is_deleted = 0
        SET c.walas_id = e.id
        WHERE c.walas_id IS NOT NULL
          AND NOT EXISTS (
              SELECT 1 FROM employees e0
              WHERE e0.id = c.walas_id AND e0.is_deleted = 0
          )
    ");
    echo "[OK] Normalisasi classes.walas_id (users.id -> employees.id) selesai.\n";
} catch (\Throwable $e) {
    echo "[SKIP] Normalisasi classes.walas_id: " . $e->getMessage() . "\n";
}

// 2. Normalisasi tahfidz_halaqah.tutor_employee_id dari users.id -> employees.id
try {
    Database::execute("
        UPDATE tahfidz_halaqah h
        JOIN employees e ON e.user_id = h.tutor_employee_id AND e.is_deleted = 0
        SET h.tutor_employee_id = e.id
        WHERE h.tutor_employee_id IS NOT NULL
          AND NOT EXISTS (
              SELECT 1 FROM employees e0
              WHERE e0.id = h.tutor_employee_id AND e0.is_deleted = 0
          )
    ");
    echo "[OK] Normalisasi tahfidz_halaqah.tutor_employee_id (users.id -> employees.id) selesai.\n";
} catch (\Throwable $e) {
    echo "[SKIP] Normalisasi tahfidz_halaqah.tutor_employee_id: " . $e->getMessage() . "\n";
}

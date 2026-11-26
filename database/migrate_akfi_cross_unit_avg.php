<?php
// database/migrate_akfi_cross_unit_avg.php
// Migrasi idempoten: Ubah indeks unik akfi_assessments agar mendukung rapor per unit penugasan
// sehingga pegawai/Kepala Sekolah lintas unit memiliki rapor di tiap unit dan nilai akhir rata-rata gabungan.

require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();

$indexes = Database::fetchAll("SHOW INDEX FROM `akfi_assessments`");
$indexNames = array_values(array_unique(array_column($indexes, 'Key_name')));

// 1. Tambahkan indeks unik baru (employee_id, unit_id, academic_year_id) terlebih dahulu agar FK employee_id tetap punya indeks pendukung
if (!in_array('uq_emp_unit_akfi_year', $indexNames, true)) {
    Database::execute("ALTER TABLE `akfi_assessments` ADD UNIQUE KEY `uq_emp_unit_akfi_year` (`employee_id`, `unit_id`, `academic_year_id`)");
    echo "[OK] UNIQUE KEY uq_emp_unit_akfi_year ditambahkan pada akfi_assessments.\n";
} else {
    echo "[SKIP] uq_emp_unit_akfi_year sudah ada pada akfi_assessments.\n";
}

// 2. Hapus indeks unik lama (employee_id, academic_year_id) agar pegawai lintas unit dapat memiliki >1 rapor unit per TA
if (in_array('uq_emp_akfi_year', $indexNames, true)) {
    Database::execute("ALTER TABLE `akfi_assessments` DROP INDEX `uq_emp_akfi_year`");
    echo "[OK] UNIQUE KEY lama uq_emp_akfi_year dihapus dari akfi_assessments.\n";
} else {
    echo "[SKIP] uq_emp_akfi_year sudah dihapus.\n";
}

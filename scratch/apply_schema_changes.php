<?php
require 'core/Database.php';
$db = Core\Database::connect();

try {
    // 1. Check if column tingkat_pendidikan exists in employees
    $cols = $db->query("DESCRIBE employees")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('tingkat_pendidikan', $cols)) {
        $db->exec("ALTER TABLE employees ADD COLUMN tingkat_pendidikan VARCHAR(20) NOT NULL DEFAULT 'S1' AFTER gender");
        echo "Column tingkat_pendidikan added to employees.\n";
    } else {
        echo "Column tingkat_pendidikan already exists.\n";
    }

    // 2. Create student_movement_logs table if not exists
    $db->exec("CREATE TABLE IF NOT EXISTS student_movement_logs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        unit_id INT NOT NULL,
        academic_year_id INT NOT NULL,
        month_num INT NOT NULL,
        month_name VARCHAR(20) NOT NULL,
        masuk INT NOT NULL DEFAULT 0,
        keluar INT NOT NULL DEFAULT 0,
        current_count INT NOT NULL DEFAULT 0,
        notes VARCHAR(255) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        UNIQUE KEY uq_unit_ta_month (unit_id, academic_year_id, month_num),
        FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
        FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    echo "Table student_movement_logs created or exists.\n";

    // 3. Configure academic years:
    // Make sure we have:
    // id=1: 2024/2025 Genap (Aktif, is_locked=0, is_active=1)
    // id=2: 2024/2025 Ganjil (Arsip, is_locked=1, is_active=0)
    $db->exec("UPDATE academic_years SET name = '2024/2025', semester = 'Genap', is_active = 1, is_locked = 0 WHERE id = 1");
    
    // Check if 2024/2025 Ganjil exists
    $taGanjil = $db->query("SELECT id FROM academic_years WHERE name = '2024/2025' AND semester = 'Ganjil'")->fetch(PDO::FETCH_ASSOC);
    if (!$taGanjil) {
        $db->exec("UPDATE academic_years SET name = '2024/2025', semester = 'Ganjil', is_active = 0, is_locked = 1 WHERE id = 2");
    } else {
        $db->exec("UPDATE academic_years SET is_active = 0, is_locked = 1 WHERE id = " . (int)$taGanjil['id']);
    }

    // Check if 2023/2024 exists
    $taPrev = $db->query("SELECT id FROM academic_years WHERE name = '2023/2024'")->fetch(PDO::FETCH_ASSOC);
    if (!$taPrev) {
        $db->exec("INSERT INTO academic_years (id, name, semester, target_workdays, target_kajian, is_active, is_locked) 
                   VALUES (10, '2023/2024', 'Genap', 264, 48, 0, 1)
                   ON DUPLICATE KEY UPDATE is_locked = 1, is_active = 0");
    }

    echo "Academic years updated.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}

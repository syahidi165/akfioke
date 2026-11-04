<?php
// database/migrate_direktur_deputi_units.php
require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/core/Database.php';

$pdo = Core\Database::connect();

echo "1. Checking table units columns...\n";
$cols = Core\Database::fetchAll("SHOW COLUMNS FROM units");
$colNames = array_column($cols, 'Field');

if (!in_array('is_active', $colNames)) {
    echo "Adding is_active to units...\n";
    $pdo->exec("ALTER TABLE units ADD COLUMN is_active TINYINT(1) NOT NULL DEFAULT 1 AFTER logo_path");
}
if (!in_array('deleted_at', $colNames)) {
    echo "Adding deleted_at to units...\n";
    $pdo->exec("ALTER TABLE units ADD COLUMN deleted_at DATETIME NULL DEFAULT NULL AFTER updated_at");
}
if (!in_array('deleted_by', $colNames)) {
    echo "Adding deleted_by to units...\n";
    $pdo->exec("ALTER TABLE units ADD COLUMN deleted_by INT NULL DEFAULT NULL AFTER deleted_at");
}
if (!in_array('deletion_reason', $colNames)) {
    echo "Adding deletion_reason to units...\n";
    $pdo->exec("ALTER TABLE units ADD COLUMN deletion_reason VARCHAR(255) NULL DEFAULT NULL AFTER deleted_by");
}

echo "Columns of units ready!\n";

// 2. Setup Akun Direktur Pendidikan
echo "2. Setting up Direktur Pendidikan...\n";
$direkturUser = Core\Database::fetch("SELECT * FROM users WHERE username = 'direktur.pendidikan'");
if (!$direkturUser) {
    $passHash = password_hash('password123', PASSWORD_BCRYPT);
    $pdo->prepare("
        INSERT INTO users (username, password_hash, email, full_name, phone, unit_id, is_active, created_at, updated_at)
        VALUES ('direktur.pendidikan', ?, 'direktur@alkahfi.sch.id', 'Dr. H. Muhammad Ridwan, M.Pd', '08117000002', NULL, 1, NOW(), NOW())
    ")->execute([$passHash]);
    $direkturUserId = (int)$pdo->lastInsertId();
    echo "Created user direktur.pendidikan (ID: $direkturUserId)\n";
} else {
    $direkturUserId = (int)$direkturUser['id'];
}

$hasRole = Core\Database::fetch("SELECT * FROM user_roles WHERE user_id = ? AND role_id = 2", [$direkturUserId]);
if (!$hasRole) {
    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, 2)")->execute([$direkturUserId]);
}

$direkturEmp = Core\Database::fetch("SELECT * FROM employees WHERE user_id = ?", [$direkturUserId]);
if (!$direkturEmp) {
    $pdo->prepare("
        INSERT INTO employees (user_id, nipy, nama, gender, tingkat_pendidikan, level_posisi, position, contact_phone, email, status, is_deleted, created_at, updated_at)
        VALUES (?, '197508152005011001', 'Dr. H. Muhammad Ridwan, M.Pd', 'L', 'S3', 'Direktur Pendidikan', 'Direktur Pendidikan Yayasan', '08117000002', 'direktur@alkahfi.sch.id', 'Aktif', 0, NOW(), NOW())
    ")->execute([$direkturUserId]);
    $direkturEmpId = (int)$pdo->lastInsertId();
} else {
    $direkturEmpId = (int)$direkturEmp['id'];
}

// 3. Setup Akun Deputi Pendidikan B (Pendidikan Menengah: SMP & SMA)
echo "3. Setting up Deputi Pendidikan B...\n";
$deputiBUser = Core\Database::fetch("SELECT * FROM users WHERE username = 'deputi.menengah'");
if (!$deputiBUser) {
    $passHash = password_hash('password123', PASSWORD_BCRYPT);
    $pdo->prepare("
        INSERT INTO users (username, password_hash, email, full_name, phone, unit_id, is_active, created_at, updated_at)
        VALUES ('deputi.menengah', ?, 'deputi.menengah@alkahfi.sch.id', 'Dr. Hendra Wijaya, M.Pd', '08127000003', NULL, 1, NOW(), NOW())
    ")->execute([$passHash]);
    $deputiBUserId = (int)$pdo->lastInsertId();
    echo "Created user deputi.menengah (ID: $deputiBUserId)\n";
} else {
    $deputiBUserId = (int)$deputiBUser['id'];
}

$hasRoleDepB = Core\Database::fetch("SELECT * FROM user_roles WHERE user_id = ? AND role_id = 3", [$deputiBUserId]);
if (!$hasRoleDepB) {
    $pdo->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, 3)")->execute([$deputiBUserId]);
}

$deputiBEmp = Core\Database::fetch("SELECT * FROM employees WHERE user_id = ?", [$deputiBUserId]);
if (!$deputiBEmp) {
    $pdo->prepare("
        INSERT INTO employees (user_id, nipy, nama, gender, tingkat_pendidikan, level_posisi, position, atasan_langsung_id, contact_phone, email, status, is_deleted, created_at, updated_at)
        VALUES (?, '198503152015071001', 'Dr. Hendra Wijaya, M.Pd', 'L', 'S3', 'Deputi Pendidikan Menengah', 'Deputi Pendidikan Menengah (SMP & SMA)', ?, '08127000003', 'deputi.menengah@alkahfi.sch.id', 'Aktif', 0, NOW(), NOW())
    ")->execute([$deputiBUserId, $direkturEmpId]);
    $deputiBEmpId = (int)$pdo->lastInsertId();
} else {
    $pdo->prepare("UPDATE employees SET atasan_langsung_id = ? WHERE id = ?")->execute([$direkturEmpId, $deputiBEmp['id']]);
}

// Update Deputi A (User 38 - Yona Melinda) atasan_langsung_id to direkturEmpId
$deputiAEmp = Core\Database::fetch("SELECT * FROM employees WHERE user_id = 38");
if ($deputiAEmp) {
    $pdo->prepare("UPDATE employees SET atasan_langsung_id = ? WHERE id = ?")->execute([$direkturEmpId, $deputiAEmp['id']]);
}

// 4. Update deputi_units assignments
echo "4. Assigning distinct units...\n";
// Deputi A (User 38): KB (1), TK (2), SD (6)
$pdo->prepare("DELETE FROM deputi_units WHERE user_id = 38")->execute();
$pdo->prepare("INSERT INTO deputi_units (user_id, unit_id, created_at) VALUES (38, 1, NOW()), (38, 2, NOW()), (38, 6, NOW())")->execute();

// Deputi B (Dr. Hendra Wijaya): SMP (7), SMA (8)
$pdo->prepare("DELETE FROM deputi_units WHERE user_id = ?")->execute([$deputiBUserId]);
$pdo->prepare("INSERT INTO deputi_units (user_id, unit_id, created_at) VALUES (?, 7, NOW()), (?, 8, NOW())")->execute([$deputiBUserId, $deputiBUserId]);

echo "Migration completed successfully!\n";


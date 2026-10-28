<?php
// database/seed_sekolah_test.php
require_once __DIR__ . '/../core/Database.php';

use Core\Database;

Database::connect();
echo "=== SEEDING DUMMY UNIT 'SEKOLAH TEST' & USER ACCOUNTS ===\n";

// 1. Create or Update Unit 'SEKOLAH TEST'
$unit = Database::fetch("SELECT * FROM units WHERE code = 'TEST'");
if (!$unit) {
    Database::execute(
        "INSERT INTO units (code, name, level, kop_subtitle, address, phone, email, npsn, akreditasi, nomor_akreditasi, akreditasi_expired_at, nomor_izin_operasional, izin_operasional_expired_at, created_at)
         VALUES ('TEST', 'SEKOLAH TEST', 'SMA', 'SMAS AL KAHFI TEST BATAM', 'The Capitol Imperium Blok A No. 40-47 Superblok Imperium Jl. Jend. Soedirman, Kota Batam', '0778-123456', 'test@alkahfi.sch.id', '69999999', 'A', '001/BAN-SM/TEST/2024', '2028-12-31', '421.3/TEST-01/2024', '2027-10-28', NOW())"
    );
    $unitId = (int)Database::lastInsertId();
    echo "[OK] Unit 'SEKOLAH TEST' created with ID: {$unitId}\n";
} else {
    $unitId = (int)$unit['id'];
    Database::execute(
        "UPDATE units SET 
            name = 'SEKOLAH TEST',
            level = 'SMA',
            kop_subtitle = 'SMAS AL KAHFI TEST BATAM',
            address = 'The Capitol Imperium Blok A No. 40-47 Superblok Imperium Jl. Jend. Soedirman, Kota Batam',
            phone = '0778-123456',
            email = 'test@alkahfi.sch.id',
            npsn = '69999999',
            akreditasi = 'A',
            nomor_akreditasi = '001/BAN-SM/TEST/2024',
            akreditasi_expired_at = '2028-12-31',
            nomor_izin_operasional = '421.3/TEST-01/2024',
            izin_operasional_expired_at = '2027-10-28'
         WHERE id = ?",
        [$unitId]
    );
    echo "[OK] Unit 'SEKOLAH TEST' updated with ID: {$unitId}\n";
}

// Helper to get role ID
$roleCache = [];
function getRoleId(string $roleName): int {
    global $roleCache;
    if (!isset($roleCache[$roleName])) {
        $row = Database::fetch("SELECT id FROM roles WHERE name = ?", [$roleName]);
        if (!$row) {
            throw new Exception("Role {$roleName} not found!");
        }
        $roleCache[$roleName] = (int)$row['id'];
    }
    return $roleCache[$roleName];
}

// 2. Accounts List to Seed
$defaultPassword = password_hash('password123', PASSWORD_BCRYPT);

$accounts = [
    [
        'username'  => 'kepsek.test',
        'full_name' => 'Drs. H. Ahmad Test, M.Pd',
        'email'     => 'kepsek.test@alkahfi.sch.id',
        'role'      => 'kepala_sekolah',
        'posisi'    => 'Kepala Sekolah',
        'gender'    => 'L',
        'nipy'      => 'TEST-001'
    ],
    [
        'username'  => 'waka.test',
        'full_name' => 'Muhammad Waka Kurikulum, S.Pd',
        'email'     => 'waka.test@alkahfi.sch.id',
        'role'      => 'waka_kurikulum',
        'posisi'    => 'Wakil Kurikulum',
        'gender'    => 'L',
        'nipy'      => 'TEST-002'
    ],
    [
        'username'  => 'tu.test',
        'full_name' => 'Siti Rahmah, A.Md (TU Test)',
        'email'     => 'tu.test@alkahfi.sch.id',
        'role'      => 'tata_usaha',
        'posisi'    => 'Tata Usaha',
        'gender'    => 'P',
        'nipy'      => 'TEST-003'
    ],
    [
        'username'  => 'guru.test',
        'full_name' => 'Budi Santoso, M.Si (Guru Test)',
        'email'     => 'guru.test@alkahfi.sch.id',
        'role'      => 'guru_mapel',
        'posisi'    => 'Guru Mapel Fisika',
        'gender'    => 'L',
        'nipy'      => 'TEST-004'
    ],
    [
        'username'  => 'walas.test',
        'full_name' => 'Dewi Lestari, S.Pd (Walas Test)',
        'email'     => 'walas.test@alkahfi.sch.id',
        'role'      => 'walas',
        'posisi'    => 'Guru Wali Kelas X-A',
        'gender'    => 'P',
        'nipy'      => 'TEST-005'
    ],
    [
        'username'  => 'tahfidz.test',
        'full_name' => 'Ustadz Faisal, Al-Hafidz',
        'email'     => 'tahfidz.test@alkahfi.sch.id',
        'role'      => 'guru_tahfidz',
        'posisi'    => 'Guru Tahfidz',
        'gender'    => 'L',
        'nipy'      => 'TEST-006'
    ]
];

$userMap = [];

foreach ($accounts as $acc) {
    $existingUser = Database::fetch("SELECT id FROM users WHERE username = ?", [$acc['username']]);
    if (!$existingUser) {
        Database::execute(
            "INSERT INTO users (username, password_hash, email, full_name, unit_id, is_active, created_at)
             VALUES (?, ?, ?, ?, ?, 1, NOW())",
            [$acc['username'], $defaultPassword, $acc['email'], $acc['full_name'], $unitId]
        );
        $userId = (int)Database::lastInsertId();
        echo "[OK] User '{$acc['username']}' created with ID: {$userId}\n";
    } else {
        $userId = (int)$existingUser['id'];
        Database::execute(
            "UPDATE users SET 
                password_hash = ?, 
                full_name = ?, 
                email = ?, 
                unit_id = ?, 
                is_active = 1 
             WHERE id = ?",
            [$defaultPassword, $acc['full_name'], $acc['email'], $unitId, $userId]
        );
        echo "[OK] User '{$acc['username']}' updated with ID: {$userId}\n";
    }
    $userMap[$acc['username']] = $userId;

    // Assign Role
    $roleId = getRoleId($acc['role']);
    $userRole = Database::fetch("SELECT user_id FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);
    if (!$userRole) {
        Database::execute("INSERT INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 1)", [$userId, $roleId]);
    }

    // Assign Employee Record
    $existingEmp = Database::fetch("SELECT id FROM employees WHERE user_id = ? OR nipy = ?", [$userId, $acc['nipy']]);
    if (!$existingEmp) {
        Database::execute(
            "INSERT INTO employees (user_id, nipy, nama, gender, tingkat_pendidikan, tmt, level_posisi, unit_id, email, status, is_deleted, created_at)
             VALUES (?, ?, ?, ?, 'S1', '2022-07-01', ?, ?, ?, 'Aktif', 0, NOW())",
            [$userId, $acc['nipy'], $acc['full_name'], $acc['gender'], $acc['posisi'], $unitId, $acc['email']]
        );
        echo "[OK] Employee '{$acc['full_name']}' created.\n";
    } else {
        Database::execute(
            "UPDATE employees SET 
                user_id = ?,
                nama = ?,
                gender = ?,
                level_posisi = ?,
                unit_id = ?,
                email = ?,
                status = 'Aktif',
                is_deleted = 0
             WHERE id = ?",
            [$userId, $acc['full_name'], $acc['gender'], $acc['posisi'], $unitId, $acc['email'], $existingEmp['id']]
        );
        echo "[OK] Employee '{$acc['full_name']}' updated.\n";
    }
}

// 3. Seed Students for SEKOLAH TEST (Active & Alumni)
$students = [
    [
        'nis'    => '2024TEST001',
        'nisn'   => '0012345601',
        'no_kk'  => '2171000000000001',
        'nama'   => 'Ahmad Zaki Mubarak',
        'gender' => 'L',
        'status' => 'Alumni'
    ],
    [
        'nis'    => '2024TEST002',
        'nisn'   => '0012345602',
        'no_kk'  => '2171000000000002',
        'nama'   => 'Fatimah Az-Zahra',
        'gender' => 'P',
        'status' => 'Alumni'
    ],
    [
        'nis'    => '2024TEST003',
        'nisn'   => '0012345603',
        'no_kk'  => '2171000000000003',
        'nama'   => 'Muhammad Rayhan Pratama',
        'gender' => 'L',
        'status' => 'Lulus'
    ],
    [
        'nis'    => '2024TEST004',
        'nisn'   => '0012345604',
        'no_kk'  => '2171000000000004',
        'nama'   => 'Siti Nurhaliza',
        'gender' => 'P',
        'status' => 'Alumni'
    ],
    [
        'nis'    => '2025TEST001',
        'nisn'   => '0012345605',
        'no_kk'  => '2171000000000005',
        'nama'   => 'Bilal Habibie (Siswa Aktif X-A)',
        'gender' => 'L',
        'status' => 'Aktif'
    ]
];

foreach ($students as $stu) {
    $existingStu = Database::fetch("SELECT id FROM students WHERE nis = ? AND unit_id = ?", [$stu['nis'], $unitId]);
    if (!$existingStu) {
        Database::execute(
            "INSERT INTO students (nis, nisn, no_kk, nama, gender, unit_id, status, created_at)
             VALUES (?, ?, ?, ?, ?, ?, ?, NOW())",
            [$stu['nis'], $stu['nisn'], $stu['no_kk'], $stu['nama'], $stu['gender'], $unitId, $stu['status']]
        );
        echo "[OK] Student {$stu['nama']} ({$stu['status']}) created.\n";
    } else {
        Database::execute(
            "UPDATE students SET nisn = ?, no_kk = ?, nama = ?, gender = ?, status = ? WHERE id = ?",
            [$stu['nisn'], $stu['no_kk'], $stu['nama'], $stu['gender'], $stu['status'], $existingStu['id']]
        );
        echo "[OK] Student {$stu['nama']} ({$stu['status']}) updated.\n";
    }
}

// 4. Seed Test Teacher Achievement in guru_berprestasi
$empGuru = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userMap['guru.test']]);
if ($empGuru) {
    $existingAch = Database::fetch("SELECT id FROM guru_berprestasi WHERE employee_id = ? AND title LIKE '%OSN Guru%'", [$empGuru['id']]);
    if (!$existingAch) {
        Database::execute(
            "INSERT INTO guru_berprestasi (unit_id, academic_year_id, employee_id, title, level, year, status, notes, created_by_user_id, created_at)
             VALUES (?, 1, ?, 'Juara 2 OSN Guru Tingkat Nasional (Fisika Terapan)', 'Nasional', '2026', 'Menunggu Verifikasi', 'Daftar lomba melalui Pusat Prestasi Nasional Kemdikbud', ?, NOW())",
            [$unitId, $empGuru['id'], $userMap['guru.test']]
        );
        echo "[OK] Test Teacher Achievement 'Juara 2 OSN Guru' created (Menunggu Verifikasi).\n";
    }
}

// 5. Seed Test Administration Deadlines with Timestamp for SEKOLAH TEST
$sampleDeadlines = [
    [
        'code'     => 'GUR-02',
        'due_info' => 'Pekan ke-1 Semester Ganjil',
        'datetime' => '2026-11-15 23:59:00'
    ],
    [
        'code'     => 'GUR-03',
        'due_info' => 'Pekan ke-2 Semester Ganjil',
        'datetime' => '2026-11-20 17:00:00'
    ]
];

foreach ($sampleDeadlines as $sd) {
    $jenis = Database::fetch("SELECT id FROM master_jenis_administrasi WHERE code = ? LIMIT 1", [$sd['code']]);
    if ($jenis) {
        $jenisId = (int)$jenis['id'];
        Database::execute(
            "INSERT INTO unit_administrasi_deadlines (unit_id, academic_year_id, jenis_admin_id, due_info, deadline_date, deadline_datetime, updated_by_user_id)
             VALUES (?, 1, ?, ?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE due_info = VALUES(due_info), deadline_date = VALUES(deadline_date), deadline_datetime = VALUES(deadline_datetime)",
            [$unitId, $jenisId, $sd['due_info'], substr($sd['datetime'], 0, 10), $sd['datetime'], $userMap['waka.test']]
        );
        echo "[OK] Deadline for '{$sd['code']}' set to {$sd['datetime']}.\n";
    }
}

echo "\n=== SEEDING COMPLETED SUCCESSFULLY! ===\n";

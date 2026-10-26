<?php
// database/seed_sarpras_demo.php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Core\Database;

try {
    echo "Seeding Sarpras Demo Data...\n";

    $passwordHash = password_hash('password123', PASSWORD_DEFAULT);

    // 1. Roles IDs
    $sarprasRole = Database::fetch("SELECT id FROM roles WHERE name = 'sarpras'");
    $pengadaanRole = Database::fetch("SELECT id FROM roles WHERE name = 'direktur_pengadaan'");
    $sarprasDirRole = Database::fetch("SELECT id FROM roles WHERE name = 'direktur_sarpras'");

    // 2. Demo Users & Employees
    $demoUsers = [
        [
            'username' => 'sarpras.tk',
            'full_name' => 'Raka Valevi, S.Hum',
            'email' => 'sarpras.tk@alkahfi.sch.id',
            'phone' => '08117009257',
            'unit_id' => 2, // TK
            'role_id' => $sarprasRole['id'],
            'nipy' => 'TK-SAR-001',
            'gender' => 'L',
            'tmt' => '2021-07-01',
            'tingkat_pendidikan' => 'S1 Humaniora',
            'level_posisi' => 'Sarpras Unit TK'
        ],
        [
            'username' => 'direktur.pengadaan',
            'full_name' => 'H. Ahmad Fauzi, S.T',
            'email' => 'pengadaan@alkahfi.sch.id',
            'phone' => '08127008899',
            'unit_id' => 3, // Yayasan/SD
            'role_id' => $pengadaanRole['id'],
            'nipy' => 'YAY-PENG-001',
            'gender' => 'L',
            'tmt' => '2019-03-01',
            'tingkat_pendidikan' => 'S1 Teknik Industri',
            'level_posisi' => 'Direktur Pengadaan'
        ],
        [
            'username' => 'direktur.sarpras',
            'full_name' => 'Yona Melinda, B.Eng',
            'email' => 'dir.sarpras@alkahfi.sch.id',
            'phone' => '08137007788',
            'unit_id' => 3, // Yayasan/SD
            'role_id' => $sarprasDirRole['id'],
            'nipy' => 'YAY-SAR-001',
            'gender' => 'P',
            'tmt' => '2018-01-15',
            'tingkat_pendidikan' => 'S1 Teknik Sipil',
            'level_posisi' => 'Direktur Sarpras'
        ]
    ];

    foreach ($demoUsers as $du) {
        $u = Database::fetch("SELECT id FROM users WHERE username = ?", [$du['username']]);
        if (!$u) {
            Database::execute(
                "INSERT INTO users (username, password_hash, email, full_name, phone, unit_id, is_active) VALUES (?, ?, ?, ?, ?, ?, 1)",
                [$du['username'], $passwordHash, $du['email'], $du['full_name'], $du['phone'], $du['unit_id']]
            );
            $userId = (int)Database::lastInsertId();
            Database::execute(
                "INSERT INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 1)",
                [$userId, $du['role_id']]
            );
        } else {
            $userId = (int)$u['id'];
            $urCheck = Database::fetch("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $du['role_id']]);
            if (!$urCheck) {
                Database::execute("INSERT INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 1)", [$userId, $du['role_id']]);
            }
        }

        // Employee record
        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? OR nipy = ?", [$userId, $du['nipy']]);
        if (!$emp) {
            Database::execute(
                "INSERT INTO employees (user_id, nipy, nama, gender, tingkat_pendidikan, tmt, level_posisi, unit_id, contact_phone, email, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')",
                [$userId, $du['nipy'], $du['full_name'], $du['gender'], $du['tingkat_pendidikan'], $du['tmt'], $du['level_posisi'], $du['unit_id'], $du['phone'], $du['email']]
            );
        } else {
            Database::execute("UPDATE employees SET user_id = ?, unit_id = ? WHERE id = ?", [$userId, $du['unit_id'], $emp['id']]);
        }
        echo "Prepared account: {$du['username']}\n";
    }

    // Also grant operator.yayasan access to these roles in user_roles so testing via switchRole is seamless
    $opYayasan = Database::fetch("SELECT id FROM users WHERE username = 'operator.yayasan'");
    if ($opYayasan) {
        foreach ([$sarprasRole['id'], $pengadaanRole['id'], $sarprasDirRole['id']] as $rId) {
            $check = Database::fetch("SELECT * FROM user_roles WHERE user_id = ? AND role_id = ?", [$opYayasan['id'], $rId]);
            if (!$check) {
                Database::execute("INSERT INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 0)", [$opYayasan['id'], $rId]);
            }
        }
        echo "Granted roles to operator.yayasan for switcher testing.\n";
    }

    // 3. Seed sample assets
    $cats = Database::fetchAll("SELECT id, name FROM asset_categories");
    $catMap = [];
    foreach ($cats as $c) {
        $catMap[$c['name']] = $c['id'];
    }

    $empRaka = Database::fetch("SELECT id FROM employees WHERE nipy = 'TK-SAR-001'");
    $empYona = Database::fetch("SELECT id FROM employees WHERE nipy = 'YAY-SAR-001'");
    $empSri = Database::fetch("SELECT id FROM employees WHERE nama LIKE '%Sri Untari%' OR nama LIKE '%Maryam Zahra%' LIMIT 1");

    $sampleAssets = [
        [
            'asset_code' => 'AST/TK/2026/001',
            'name' => 'Printer Epson EcoTank L1210',
            'category_id' => $catMap['Elektronik & IT'] ?? 1,
            'purchase_date' => '2026-08-21',
            'unit_id' => 2, // TK
            'condition_status' => 'Baik',
            'user_holder_id' => $empRaka['id'] ?? null,
            'user_holder_name' => 'Raka Valevi, S.Hum',
            'location' => 'Kantor Guru TK',
            'price' => 2150000,
            'is_consumable' => 0,
            'stock_status' => 'Aktif',
            'quantity' => 1
        ],
        [
            'asset_code' => 'AST/TK/2026/002',
            'name' => 'Meja Guru Kayu Jati Minimalis',
            'category_id' => $catMap['Mebel & Furniture'] ?? 2,
            'purchase_date' => '2026-08-21',
            'unit_id' => 2,
            'condition_status' => 'Baik',
            'user_holder_id' => $empSri['id'] ?? null,
            'user_holder_name' => 'Sri Untari, S.Pd.Aud., Gr.',
            'location' => 'Ruang Kepala Sekolah TK',
            'price' => 850000,
            'is_consumable' => 0,
            'stock_status' => 'Aktif',
            'quantity' => 1
        ],
        [
            'asset_code' => 'AST/TK/2026/003',
            'name' => 'Laci Besi Kolong Meja 3 Susun',
            'category_id' => $catMap['Mebel & Furniture'] ?? 2,
            'purchase_date' => '2026-08-21',
            'unit_id' => 2,
            'condition_status' => 'Baik',
            'user_holder_id' => $empSri['id'] ?? null,
            'user_holder_name' => 'Sri Untari, S.Pd.Aud., Gr.',
            'location' => 'Ruang Kepala Sekolah TK',
            'price' => 450000,
            'is_consumable' => 0,
            'stock_status' => 'Aktif',
            'quantity' => 4
        ],
        [
            'asset_code' => 'AST/TK/2024/015',
            'name' => 'Kursi Guru Putar Hidrolik',
            'category_id' => $catMap['Mebel & Furniture'] ?? 2,
            'purchase_date' => '2024-02-10',
            'unit_id' => 2,
            'condition_status' => 'Rusak Berat',
            'user_holder_id' => $empYona['id'] ?? null,
            'user_holder_name' => 'Yona Melinda, B.Eng',
            'location' => 'Gudang Belakang TK',
            'price' => 550000,
            'is_consumable' => 0,
            'stock_status' => 'Aktif',
            'quantity' => 5
        ],
        [
            'asset_code' => 'AST/TK/2023/008',
            'name' => 'Lemari Besi 2 Pintu Arsip',
            'category_id' => $catMap['Mebel & Furniture'] ?? 2,
            'purchase_date' => '2023-01-15',
            'unit_id' => 2,
            'condition_status' => 'Rusak Berat',
            'user_holder_id' => $empYona['id'] ?? null,
            'user_holder_name' => 'Yona Melinda, B.Eng',
            'location' => 'Gudang Belakang TK',
            'price' => 1750000,
            'is_consumable' => 0,
            'stock_status' => 'Aktif',
            'quantity' => 1
        ],
        [
            'asset_code' => 'ATK/TK/2026/001',
            'name' => 'Kertas HVS PaperOne A4 75gsm',
            'category_id' => $catMap['ATK / Habis Pakai'] ?? 4,
            'purchase_date' => '2026-08-01',
            'unit_id' => 2,
            'condition_status' => 'Baik',
            'user_holder_id' => null,
            'user_holder_name' => 'Tata Usaha / Umum',
            'location' => 'Ruang TU TK',
            'price' => 55000,
            'is_consumable' => 1,
            'stock_status' => 'Ready',
            'quantity' => 25
        ],
        // SD (unit 3)
        ['asset_code' => 'AST/SD/2026/001', 'name' => 'Proyektor Epson EB-X500', 'category_id' => 1, 'purchase_date' => '2026-01-10', 'unit_id' => 3, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Waka Kurikulum SD', 'location' => 'Ruang Multimedia SD', 'price' => 5800000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 2],
        ['asset_code' => 'AST/SD/2026/002', 'name' => 'Sound System Portable Wireless', 'category_id' => 1, 'purchase_date' => '2026-02-15', 'unit_id' => 3, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Tata Usaha SD', 'location' => 'Ruang TU SD', 'price' => 2400000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 3],
        ['asset_code' => 'AST/SD/2026/003', 'name' => 'Meja Siswa Kayu Ganda', 'category_id' => 2, 'purchase_date' => '2025-07-20', 'unit_id' => 3, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Sarpras SD', 'location' => 'Kelas 1A Abu Bakar', 'price' => 650000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 30],
        ['asset_code' => 'AST/SD/2026/004', 'name' => 'Whiteboard Magnetic 120x240cm', 'category_id' => 5, 'purchase_date' => '2025-08-10', 'unit_id' => 3, 'condition_status' => 'Baik', 'user_holder_name' => 'Sarpras SD', 'location' => 'Kantor Guru SD', 'price' => 850000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 6],
        ['asset_code' => 'ATK/SD/2026/001', 'name' => 'Spidol Boardmarker Snowman Hitam', 'category_id' => 4, 'purchase_date' => '2026-08-01', 'unit_id' => 3, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Tata Usaha SD', 'location' => 'Ruang TU SD', 'price' => 95000, 'is_consumable' => 1, 'stock_status' => 'Ready', 'quantity' => 40],
        ['asset_code' => 'AST/SD/2026/005', 'name' => 'Bola Futsal Molten F9V', 'category_id' => 6, 'purchase_date' => '2026-03-05', 'unit_id' => 3, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Guru PJOK SD', 'location' => 'Gudang Olahraga SD', 'price' => 220000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 8],
        // SMP (unit 4)
        ['asset_code' => 'AST/SMP/2026/001', 'name' => 'Mikroskop Binokuler Siswa', 'category_id' => 3, 'purchase_date' => '2026-01-20', 'unit_id' => 4, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Laboran SMP', 'location' => 'Laboratorium IPA SMP', 'price' => 1850000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 12],
        ['asset_code' => 'AST/SMP/2026/002', 'name' => 'PC All-in-One Lenovo IdeaCentre', 'category_id' => 1, 'purchase_date' => '2025-11-15', 'unit_id' => 4, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Laboran Komputer', 'location' => 'Lab Komputer SMP', 'price' => 7500000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 15],
        ['asset_code' => 'AST/SMP/2026/003', 'name' => 'AC Daikin Standard 1.5 PK', 'category_id' => 1, 'purchase_date' => '2025-06-10', 'unit_id' => 4, 'condition_status' => 'Baik', 'user_holder_id' => null, 'user_holder_name' => 'Sarpras SMP', 'location' => 'Ruang Guru SMP', 'price' => 4800000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 4],
        ['asset_code' => 'AST/SMP/2026/004', 'name' => 'Bola Basket Molten BG3800', 'category_id' => 6, 'purchase_date' => '2026-02-12', 'unit_id' => 4, 'condition_status' => 'Baik', 'user_holder_name' => 'Guru Penjas SMP', 'location' => 'Gudang Sarpras SMP', 'price' => 450000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 6],
        ['asset_code' => 'ATK/SMP/2026/001', 'name' => 'Kertas HVS SiDU A4 70gsm', 'category_id' => 4, 'purchase_date' => '2026-08-01', 'unit_id' => 4, 'condition_status' => 'Baik', 'user_holder_name' => 'Tata Usaha SMP', 'location' => 'Ruang Tata Usaha SMP', 'price' => 52000, 'is_consumable' => 1, 'stock_status' => 'Ready', 'quantity' => 50],
        // SMA (unit 5)
        ['asset_code' => 'AST/SMA/2026/001', 'name' => 'Smart TV Samsung 55 Inch 4K', 'category_id' => 1, 'purchase_date' => '2026-01-25', 'unit_id' => 5, 'condition_status' => 'Baik', 'user_holder_name' => 'Sarpras SMA', 'location' => 'Kelas 10 MIPA Al-Biruni', 'price' => 7200000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 2],
        ['asset_code' => 'AST/SMA/2026/002', 'name' => 'Gitar Akustik Yamaha F310', 'category_id' => 6, 'purchase_date' => '2025-09-01', 'unit_id' => 5, 'condition_status' => 'Baik', 'user_holder_name' => 'Guru Seni SMA', 'location' => 'Ruang Seni SMA', 'price' => 1350000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 5],
        ['asset_code' => 'AST/SMA/2026/003', 'name' => 'Server Rack 24U Indorack', 'category_id' => 1, 'purchase_date' => '2025-05-18', 'unit_id' => 5, 'condition_status' => 'Baik', 'user_holder_name' => 'Admin IT SMA', 'location' => 'Ruang Server SMA', 'price' => 4200000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 1],
        ['asset_code' => 'AST/SMA/2026/004', 'name' => 'Lemari Kaca Display Piala', 'category_id' => 2, 'purchase_date' => '2025-04-10', 'unit_id' => 5, 'condition_status' => 'Baik', 'user_holder_name' => 'Humas SMA', 'location' => 'Lobby SMA', 'price' => 2800000, 'is_consumable' => 0, 'stock_status' => 'Aktif', 'quantity' => 2],
        ['asset_code' => 'ATK/SMA/2026/001', 'name' => 'Tinta Botol Epson 003 Black', 'category_id' => 4, 'purchase_date' => '2026-08-01', 'unit_id' => 5, 'condition_status' => 'Baik', 'user_holder_name' => 'Tata Usaha SMA', 'location' => 'Ruang TU SMA', 'price' => 85000, 'is_consumable' => 1, 'stock_status' => 'Ready', 'quantity' => 15]
    ];

    foreach ($sampleAssets as $sa) {
        $chk = Database::fetch("SELECT id FROM assets WHERE asset_code = ?", [$sa['asset_code']]);
        if (!$chk) {
            Database::execute(
                "INSERT INTO assets (asset_code, name, category_id, purchase_date, unit_id, condition_status, user_holder_id, user_holder_name, location, price, is_consumable, stock_status, quantity, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')",
                [$sa['asset_code'], $sa['name'], $sa['category_id'], $sa['purchase_date'], $sa['unit_id'], $sa['condition_status'], $sa['user_holder_id'], $sa['user_holder_name'], $sa['location'], $sa['price'], $sa['is_consumable'], $sa['stock_status'], $sa['quantity']]
            );
            echo "Seeded asset: {$sa['asset_code']} - {$sa['name']}\n";
        }
    }

    echo "Seeding completed successfully!\n";
} catch (\Throwable $e) {
    echo "Seeding error: " . $e->getMessage() . "\n";
    exit(1);
}

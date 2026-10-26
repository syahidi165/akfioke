<?php
// database/migrate_sarpras.php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Core\Database;

try {
    echo "Starting Sarpras & Role Migration...\n";

    // 1. Insert New Roles
    $newRoles = [
        [
            'name' => 'sarpras',
            'display_name' => 'Sarpras',
            'description' => 'Sarpras Unit Sekolah: pendataan & penghapusan aset, PO barang, berita acara scrap'
        ],
        [
            'name' => 'direktur_pengadaan',
            'display_name' => 'Direktur Pengadaan',
            'description' => 'Direktur Pengadaan Yayasan: verifikasi & persetujuan PO pengadaan barang'
        ],
        [
            'name' => 'direktur_sarpras',
            'display_name' => 'Direktur Sarpras',
            'description' => 'Direktur Sarpras Yayasan: approval PO kontraktor/pengerjaan lanjutan & tata kelola sarpras'
        ]
    ];

    foreach ($newRoles as $r) {
        $existing = Database::fetch("SELECT id FROM roles WHERE name = ?", [$r['name']]);
        if (!$existing) {
            Database::execute(
                "INSERT INTO roles (name, display_name, description) VALUES (?, ?, ?)",
                [$r['name'], $r['display_name'], $r['description']]
            );
            echo "Added role: {$r['name']}\n";
        } else {
            echo "Role already exists: {$r['name']}\n";
        }
    }

    // 2. Table: asset_categories
    Database::execute("CREATE TABLE IF NOT EXISTS asset_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        description TEXT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table asset_categories ready.\n";

    // Seed default categories
    $defaultCategories = [
        'Elektronik & IT' => 'Perangkat komputer, printer, laptop, proyektor, CCTV',
        'Mebel & Furniture' => 'Meja guru, kursi siswa, lemari besi, rak buku, whiteboard',
        'Peralatan Kantor & Sekolah' => 'Dispenser, AC, kipas angin, mesin fotokopi, bel sekolah',
        'ATK / Habis Pakai' => 'Kertas HVS, spidol, pulpen, map, tinta printer (Barang pecah belah / habis)',
        'Perlengkapan Kelas & Karpet' => 'Karpet busa, kasur busa, tirai, jam dinding',
        'Sarana Olahraga & Seni' => 'Matras, bola, sound system, mikrofon, alat musik'
    ];
    foreach ($defaultCategories as $catName => $catDesc) {
        $cExists = Database::fetch("SELECT id FROM asset_categories WHERE name = ?", [$catName]);
        if (!$cExists) {
            Database::execute("INSERT INTO asset_categories (name, description) VALUES (?, ?)", [$catName, $catDesc]);
        }
    }

    // 3. Table: assets
    Database::execute("CREATE TABLE IF NOT EXISTS assets (
        id INT AUTO_INCREMENT PRIMARY KEY,
        asset_code VARCHAR(100) NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        category_id INT NOT NULL,
        purchase_date DATE NOT NULL,
        unit_id INT NOT NULL,
        condition_status ENUM('Baik', 'Rusak Ringan', 'Rusak Berat', 'Afkir/Scrap') DEFAULT 'Baik',
        user_holder_id INT NULL,
        user_holder_name VARCHAR(150) NULL,
        location VARCHAR(150) NULL,
        price DECIMAL(15,2) DEFAULT 0,
        photo VARCHAR(255) NULL,
        is_consumable TINYINT(1) DEFAULT 0,
        stock_status ENUM('Ready', 'Habis Stock', 'Aktif', 'Scrapped') DEFAULT 'Aktif',
        quantity INT DEFAULT 1,
        po_id INT NULL,
        status ENUM('Aktif', 'Scrapped', 'Mutasi') DEFAULT 'Aktif',
        notes TEXT NULL,
        created_by_user_id INT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_assets_unit (unit_id),
        INDEX idx_assets_cat (category_id),
        INDEX idx_assets_holder (user_holder_id),
        INDEX idx_assets_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table assets ready.\n";

    // 4. Table: purchase_orders
    Database::execute("CREATE TABLE IF NOT EXISTS purchase_orders (
        id INT AUTO_INCREMENT PRIMARY KEY,
        po_number VARCHAR(100) NOT NULL UNIQUE,
        unit_id INT NOT NULL,
        created_by_user_id INT NOT NULL,
        request_date DATE NOT NULL,
        reason TEXT NOT NULL,
        procurement_division VARCHAR(100) DEFAULT 'Divisi Pengadaan Barang',
        delivery_date DATE NULL,
        delivery_location VARCHAR(255) NOT NULL,
        special_notes TEXT NULL,
        needs_contractor TINYINT(1) DEFAULT 0,
        status ENUM(
            'Draft',
            'Menunggu_Kepsek',
            'Menunggu_Direktur_Pengadaan',
            'Menunggu_Direktur_Sarpras',
            'Disetujui',
            'Dalam_Pengerjaan',
            'Selesai',
            'Ditolak'
        ) DEFAULT 'Menunggu_Kepsek',
        
        -- Signature & Timestamps
        requested_by_name VARCHAR(150) NOT NULL,
        requested_by_title VARCHAR(100) DEFAULT 'Sarpras',
        requested_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        
        approved_kepsek_id INT NULL,
        approved_kepsek_name VARCHAR(150) NULL,
        approved_kepsek_at DATETIME NULL,
        kepsek_notes TEXT NULL,
        
        approved_pengadaan_id INT NULL,
        approved_pengadaan_name VARCHAR(150) NULL,
        approved_pengadaan_at DATETIME NULL,
        pengadaan_notes TEXT NULL,
        
        approved_sarpras_dir_id INT NULL,
        approved_sarpras_dir_name VARCHAR(150) NULL,
        approved_sarpras_dir_at DATETIME NULL,
        sarpras_dir_notes TEXT NULL,
        
        -- Realisasi & Pengerjaan (Kontraktor / Toko)
        work_started_at DATETIME NULL,
        completed_at DATETIME NULL,
        delivery_letter_file VARCHAR(255) NULL,
        work_documentation_file VARCHAR(255) NULL,
        realization_notes TEXT NULL,
        realized_by_user_id INT NULL,
        auto_stocked_at DATETIME NULL,
        
        rejection_reason TEXT NULL,
        rejected_by_user_id INT NULL,
        rejected_at DATETIME NULL,
        
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_po_unit (unit_id),
        INDEX idx_po_status (status)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table purchase_orders ready.\n";

    // 5. Table: purchase_order_items
    Database::execute("CREATE TABLE IF NOT EXISTS purchase_order_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        po_id INT NOT NULL,
        item_no INT NOT NULL,
        description VARCHAR(255) NOT NULL,
        specs VARCHAR(255) NULL,
        rapbs ENUM('YA', 'TIDAK') DEFAULT 'YA',
        unit_of_measure VARCHAR(50) DEFAULT 'unit',
        stock_position INT DEFAULT 0,
        quantity_ordered INT NOT NULL DEFAULT 1,
        procurement_time VARCHAR(100) NULL,
        cost_burden_unit VARCHAR(100) NULL,
        photo VARCHAR(255) NULL,
        is_consumable TINYINT(1) DEFAULT 0,
        category_id INT NULL,
        status ENUM('Pending', 'Terpenuhi', 'Batal') DEFAULT 'Pending',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_poi_po (po_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table purchase_order_items ready.\n";

    // 6. Table: scrap_reports
    Database::execute("CREATE TABLE IF NOT EXISTS scrap_reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        report_number VARCHAR(100) NOT NULL UNIQUE,
        form_code VARCHAR(50) DEFAULT 'F30',
        unit_id INT NOT NULL,
        report_date DATE NOT NULL,
        day_name VARCHAR(30) NOT NULL,
        reason TEXT NOT NULL,
        status ENUM('Draft', 'Disetujui', 'Selesai') DEFAULT 'Disetujui',
        taken_by_name VARCHAR(150) NULL,
        taken_by_date DATE NULL,
        prepared_by_name VARCHAR(150) NOT NULL,
        prepared_by_title VARCHAR(100) DEFAULT 'Sarpras',
        approved_by_name VARCHAR(150) NOT NULL,
        approved_by_title VARCHAR(100) DEFAULT 'Deputi Direktur AIS',
        created_by_user_id INT NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_scrap_unit (unit_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table scrap_reports ready.\n";

    // 7. Table: scrap_report_items
    Database::execute("CREATE TABLE IF NOT EXISTS scrap_report_items (
        id INT AUTO_INCREMENT PRIMARY KEY,
        scrap_report_id INT NOT NULL,
        asset_id INT NULL,
        item_name VARCHAR(255) NOT NULL,
        quantity INT NOT NULL DEFAULT 1,
        quantity_text VARCHAR(100) NULL,
        responsible_person VARCHAR(150) NOT NULL,
        photo VARCHAR(255) NULL,
        notes TEXT NULL,
        INDEX idx_sri_report (scrap_report_id),
        INDEX idx_sri_asset (asset_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo "Table scrap_report_items ready.\n";

    echo "Migration completed successfully!\n";
} catch (\Throwable $e) {
    echo "Migration error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}

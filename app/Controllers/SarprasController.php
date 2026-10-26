<?php
// app/Controllers/SarprasController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\ExportHelper;
use Core\SecurityHelper;
use Core\Uploader;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class SarprasController extends Controller {

    protected function checkSarprasAccess(): array {
        $this->requireAuth();
        $this->requireRole([
            'sarpras',
            'kepala_sekolah',
            'direktur_pengadaan',
            'direktur_sarpras',
            'operator_yayasan',
            'direktur_pendidikan',
            'deputi_pendidikan'
        ]);

        $activeRole = Auth::activeRoleName();
        $isYayasan = in_array($activeRole, ['direktur_pengadaan', 'direktur_sarpras', 'operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan'], true);
        $userUnitId = Auth::unitId();

        return [
            'activeRole' => $activeRole,
            'isYayasan'  => $isYayasan,
            'unitId'     => $userUnitId
        ];
    }

    // =========================================================================
    // 1. MASTER KATEGORI ASET
    // =========================================================================
    public function categories(): void {
        $access = $this->checkSarprasAccess();
        $categories = Database::fetchAll("SELECT c.*, COUNT(a.id) as total_assets 
                                          FROM asset_categories c 
                                          LEFT JOIN assets a ON c.id = a.category_id AND a.status = 'Aktif'
                                          GROUP BY c.id 
                                          ORDER BY c.name ASC");

        $this->view('sarpras/categories', [
            'pageTitle'  => 'Kategori Aset & Inventaris',
            'categories' => $categories,
            'access'     => $access
        ]);
    }

    public function saveCategory(): void {
        $access = $this->checkSarprasAccess();
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $description = trim($_POST['description'] ?? '');

        if (empty($name)) {
            $this->setFlash('danger', 'Nama kategori wajib diisi.');
            $this->redirect(Auth::baseUrl() . '/sarpras/categories');
            return;
        }

        if ($id > 0) {
            Database::execute("UPDATE asset_categories SET name = ?, description = ?, updated_at = NOW() WHERE id = ?", [$name, $description, $id]);
            AuditLog::log('UPDATE_ASSET_CATEGORY', 'asset_categories', $id, null, ['name' => $name]);
            $this->setFlash('success', 'Kategori aset berhasil diperbarui.');
        } else {
            $exists = Database::fetch("SELECT id FROM asset_categories WHERE LOWER(name) = LOWER(?)", [$name]);
            if ($exists) {
                $this->setFlash('danger', 'Kategori dengan nama tersebut sudah ada.');
                $this->redirect(Auth::baseUrl() . '/sarpras/categories');
                return;
            }
            Database::execute("INSERT INTO asset_categories (name, description) VALUES (?, ?)", [$name, $description]);
            $newId = Database::lastInsertId();
            AuditLog::log('CREATE_ASSET_CATEGORY', 'asset_categories', $newId, null, ['name' => $name]);
            $this->setFlash('success', 'Kategori aset baru berhasil ditambahkan.');
        }

        $this->redirect(Auth::baseUrl() . '/sarpras/categories');
    }

    public function deleteCategory(): void {
        $access = $this->checkSarprasAccess();
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $used = Database::fetch("SELECT COUNT(*) as c FROM assets WHERE category_id = ?", [$id])['c'] ?? 0;
            if ($used > 0) {
                $this->setFlash('danger', "Kategori tidak dapat dihapus karena sedang digunakan oleh {$used} aset.");
            } else {
                Database::execute("DELETE FROM asset_categories WHERE id = ?", [$id]);
                AuditLog::log('DELETE_ASSET_CATEGORY', 'asset_categories', $id, null, ['id' => $id]);
                $this->setFlash('success', 'Kategori aset berhasil dihapus.');
            }
        }
        $this->redirect(Auth::baseUrl() . '/sarpras/categories');
    }

    // =========================================================================
    // 2. DATA ASET SEKOLAH (PENDATAAN, FILTER, IMPORT, EXPORT)
    // =========================================================================
    public function assets(): void {
        $access = $this->checkSarprasAccess();
        $isYayasan = $access['isYayasan'];
        $userUnitId = $access['unitId'];

        $unitFilter = $isYayasan ? (isset($_GET['unit_id']) && $_GET['unit_id'] !== '' ? (int)$_GET['unit_id'] : null) : $userUnitId;
        $categoryFilter = isset($_GET['category_id']) && $_GET['category_id'] !== '' ? (int)$_GET['category_id'] : null;
        $conditionFilter = trim($_GET['condition'] ?? '');
        $statusFilter = trim($_GET['status'] ?? 'Aktif'); // default hanya aset aktif

        $where = ["1=1"];
        $params = [];

        if ($unitFilter !== null) {
            $where[] = "a.unit_id = ?";
            $params[] = $unitFilter;
        }
        if ($categoryFilter !== null) {
            $where[] = "a.category_id = ?";
            $params[] = $categoryFilter;
        }
        if (!empty($conditionFilter)) {
            $where[] = "a.condition_status = ?";
            $params[] = $conditionFilter;
        }
        if (!empty($statusFilter)) {
            $where[] = "a.status = ?";
            $params[] = $statusFilter;
        }

        $whereClause = implode(" AND ", $where);
        $assets = Database::fetchAll(
            "SELECT a.*, c.name as category_name, u.name as unit_name, u.code as unit_code,
                    e.nama as employee_holder_name, e.nipy as employee_holder_nipy
             FROM assets a
             LEFT JOIN asset_categories c ON a.category_id = c.id
             LEFT JOIN units u ON a.unit_id = u.id
             LEFT JOIN employees e ON a.user_holder_id = e.id
             WHERE {$whereClause}
             ORDER BY a.id DESC",
            $params
        );

        $categories = Database::fetchAll("SELECT * FROM asset_categories ORDER BY name ASC");
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        
        // Employees for dropdown pemegang
        $empWhere = ($unitFilter !== null) ? "WHERE (unit_id = ? OR unit_id IS NULL) AND is_deleted = 0" : "WHERE is_deleted = 0";
        $empParams = ($unitFilter !== null) ? [$unitFilter] : [];
        $employees = Database::fetchAll("SELECT id, nipy, nama, level_posisi FROM employees {$empWhere} ORDER BY nama ASC", $empParams);

        $this->view('sarpras/assets', [
            'pageTitle'       => 'Daftar Aset & Inventaris Sekolah',
            'assets'          => $assets,
            'categories'      => $categories,
            'units'           => $units,
            'employees'       => $employees,
            'access'          => $access,
            'selectedUnit'    => $unitFilter,
            'selectedCat'     => $categoryFilter,
            'selectedCond'    => $conditionFilter,
            'selectedStatus'  => $statusFilter
        ]);
    }

    public function saveAsset(): void {
        $access = $this->checkSarprasAccess();
        $id = (int)($_POST['id'] ?? 0);
        $assetCode = trim($_POST['asset_code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $categoryId = (int)($_POST['category_id'] ?? 0);
        $purchaseDate = trim($_POST['purchase_date'] ?? date('Y-m-d'));
        $unitId = $access['isYayasan'] ? (int)($_POST['unit_id'] ?? $access['unitId']) : $access['unitId'];
        $conditionStatus = trim($_POST['condition_status'] ?? 'Baik');
        $userHolderId = !empty($_POST['user_holder_id']) ? (int)$_POST['user_holder_id'] : null;
        $userHolderName = trim($_POST['user_holder_name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $price = (float)str_replace(['.', ','], ['', '.'], $_POST['price'] ?? '0');
        $isConsumable = !empty($_POST['is_consumable']) ? 1 : 0;
        $stockStatus = trim($_POST['stock_status'] ?? ($isConsumable ? 'Ready' : 'Aktif'));
        $quantity = max(1, (int)($_POST['quantity'] ?? 1));
        $notes = trim($_POST['notes'] ?? '');

        // Resolve holder name if holder_id selected
        if ($userHolderId > 0) {
            $emp = Database::fetch("SELECT nama FROM employees WHERE id = ?", [$userHolderId]);
            if ($emp && empty($userHolderName)) {
                $userHolderName = $emp['nama'];
            }
        }

        // Auto-generate asset code if empty
        if (empty($assetCode)) {
            $unit = Database::fetch("SELECT code FROM units WHERE id = ?", [$unitId]);
            $uCode = $unit['code'] ?? 'GEN';
            $year = date('Y', strtotime($purchaseDate));
            $prefix = $isConsumable ? "ATK/{$uCode}/{$year}" : "AST/{$uCode}/{$year}";
            $last = Database::fetch("SELECT id FROM assets ORDER BY id DESC LIMIT 1");
            $nextNum = sprintf("%03d", ($last['id'] ?? 0) + 1);
            $assetCode = "{$prefix}/{$nextNum}";
        }

        // Handle Photo Upload
        $photoPath = null;
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $up = Uploader::upload($_FILES['photo'], 'assets');
            if ($up['success']) {
                $photoPath = $up['file_path'];
            }
        }

        if ($id > 0) {
            $existing = Database::fetch("SELECT * FROM assets WHERE id = ?", [$id]);
            if (!$existing) {
                $this->setFlash('danger', 'Aset tidak ditemukan.');
                $this->redirect(Auth::baseUrl() . '/sarpras/assets');
                return;
            }
            $finalPhoto = $photoPath ?: $existing['photo'];
            Database::execute(
                "UPDATE assets SET asset_code = ?, name = ?, category_id = ?, purchase_date = ?, unit_id = ?, 
                 condition_status = ?, user_holder_id = ?, user_holder_name = ?, location = ?, price = ?, 
                 photo = ?, is_consumable = ?, stock_status = ?, quantity = ?, notes = ?, updated_at = NOW() 
                 WHERE id = ?",
                [$assetCode, $name, $categoryId, $purchaseDate, $unitId, $conditionStatus, $userHolderId, $userHolderName, $location, $price, $finalPhoto, $isConsumable, $stockStatus, $quantity, $notes, $id]
            );
            AuditLog::log('UPDATE_ASSET', 'assets', $id, null, ['code' => $assetCode, 'name' => $name]);
            $this->setFlash('success', "Aset {$name} ({$assetCode}) berhasil diperbarui.");
        } else {
            // Check unique code
            $codeCheck = Database::fetch("SELECT id FROM assets WHERE asset_code = ?", [$assetCode]);
            if ($codeCheck) {
                $assetCode .= '-' . time();
            }

            Database::execute(
                "INSERT INTO assets (asset_code, name, category_id, purchase_date, unit_id, condition_status, 
                 user_holder_id, user_holder_name, location, price, photo, is_consumable, stock_status, quantity, 
                 notes, created_by_user_id, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')",
                [$assetCode, $name, $categoryId, $purchaseDate, $unitId, $conditionStatus, $userHolderId, $userHolderName, $location, $price, $photoPath, $isConsumable, $stockStatus, $quantity, $notes, Auth::id()]
            );
            $newId = Database::lastInsertId();
            AuditLog::log('CREATE_ASSET', 'assets', $newId, null, ['code' => $assetCode, 'name' => $name]);
            $this->setFlash('success', "Aset baru {$name} ({$assetCode}) berhasil ditambahkan ke sistem.");
        }

        $this->redirect(Auth::baseUrl() . '/sarpras/assets');
    }

    public function deleteAsset(): void {
        $access = $this->checkSarprasAccess();
        $id = (int)($_POST['id'] ?? 0);

        if ($id > 0) {
            $asset = Database::fetch("SELECT * FROM assets WHERE id = ?", [$id]);
            if ($asset) {
                Database::execute("DELETE FROM assets WHERE id = ?", [$id]);
                AuditLog::log('DELETE_ASSET', 'assets', $id, null, ['code' => $asset['asset_code'], 'name' => $asset['name']]);
                $this->setFlash('success', "Aset {$asset['name']} ({$asset['asset_code']}) berhasil dihapus dari sistem.");
            }
        }
        $this->redirect(Auth::baseUrl() . '/sarpras/assets');
    }

    // --- TEMPLATE IMPORT EXCEL ---
    public function templateAssets(): void {
        $this->checkSarprasAccess();
        
        $categories = Database::fetchAll("SELECT name FROM asset_categories ORDER BY name ASC");
        $catList = implode(', ', array_column($categories, 'name'));

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Import Aset');

        // Headers
        $headers = [
            'No Aset (Opsional)',
            'Nama/Merk/Brand*',
            'Kategori*',
            'Tgl Beli (YYYY-MM-DD)*',
            'Kondisi (Baik/Rusak Ringan/Rusak Berat)*',
            'User/Pemegang (Nama Pegawai)',
            'Lokasi/Ruangan',
            'Harga Satuan (Rp)',
            'Barang Pecah Belah/ATK? (YA/TIDAK)',
            'Jumlah/Stock',
            'Catatan/Keterangan'
        ];

        $colIdx = 1;
        foreach ($headers as $h) {
            $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . '1';
            $sheet->setCellValue($cellCoord, $h);
            $colIdx++;
        }

        // Header style
        $headerRange = 'A1:K1';
        $sheet->getStyle($headerRange)->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']], // Teal 700
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Sample Rows
        $sampleData = [
            [
                'AST/TK/2026/101',
                'Laptop Asus Core i5',
                'Elektronik & IT',
                date('Y-m-d'),
                'Baik',
                'Raka Valevi, S.Hum',
                'Ruang Guru TK',
                '8500000',
                'TIDAK',
                '1',
                'Inventaris KBM guru'
            ],
            [
                'ATK/TK/2026/102',
                'Spidol Whiteboard Snowman Hitam',
                'ATK / Habis Pakai',
                date('Y-m-d'),
                'Baik',
                'Tata Usaha',
                'Lemari ATK',
                '120000',
                'YA',
                '12',
                'Pecah belah / habis pakai (Ready)'
            ]
        ];

        $rowIdx = 2;
        foreach ($sampleData as $row) {
            $colIdx = 1;
            foreach ($row as $val) {
                $cellCoord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx) . $rowIdx;
                $sheet->setCellValue($cellCoord, $val);
                $colIdx++;
            }
            $rowIdx++;
        }

        foreach (range(1, count($headers)) as $c) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="Template_Import_Aset_Sarpras.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // --- IMPORT EXCEL ---
    public function importAssets(): void {
        $access = $this->checkSarprasAccess();
        $targetUnitId = $access['isYayasan'] ? (int)($_POST['unit_id'] ?? $access['unitId']) : $access['unitId'];

        if (empty($_FILES['excel_file']['tmp_name'])) {
            $this->setFlash('danger', 'Silakan pilih berkas Excel (.xlsx / .xls) untuk diimpor.');
            $this->redirect(Auth::baseUrl() . '/sarpras/assets');
            return;
        }

        $categories = Database::fetchAll("SELECT id, name FROM asset_categories");
        $catMap = [];
        foreach ($categories as $c) {
            $catMap[strtolower(trim($c['name']))] = (int)$c['id'];
        }

        $unit = Database::fetch("SELECT code FROM units WHERE id = ?", [$targetUnitId]);
        $uCode = $unit['code'] ?? 'GEN';

        try {
            $spreadsheet = IOFactory::load($_FILES['excel_file']['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            $this->redirect(Auth::baseUrl() . '/sarpras/assets');
            return;
        }

        $imported = 0;
        $skipped = 0;

        for ($row = 2; $row <= $highestRow; $row++) {
            $name = trim((string)$sheet->getCell("B{$row}")->getValue());
            if (empty($name)) continue;

            $assetCode = trim((string)$sheet->getCell("A{$row}")->getValue());
            $catText = strtolower(trim((string)$sheet->getCell("C{$row}")->getValue()));
            $purchaseDate = trim((string)$sheet->getCell("D{$row}")->getValue());
            if (empty($purchaseDate) || !strtotime($purchaseDate)) {
                $purchaseDate = date('Y-m-d');
            }

            $condition = trim((string)$sheet->getCell("E{$row}")->getValue());
            if (!in_array($condition, ['Baik', 'Rusak Ringan', 'Rusak Berat', 'Afkir/Scrap'])) {
                $condition = 'Baik';
            }

            $holderName = trim((string)$sheet->getCell("F{$row}")->getValue());
            $location = trim((string)$sheet->getCell("G{$row}")->getValue());
            $price = (float)preg_replace('/[^0-9.]/', '', (string)$sheet->getCell("H{$row}")->getValue());
            
            $atkText = strtoupper(trim((string)$sheet->getCell("I{$row}")->getValue()));
            $isConsumable = ($atkText === 'YA' || $atkText === '1' || $atkText === 'TRUE') ? 1 : 0;
            
            $qty = max(1, (int)$sheet->getCell("J{$row}")->getValue());
            $notes = trim((string)$sheet->getCell("K{$row}")->getValue());

            // Resolve Category
            $catId = $catMap[$catText] ?? null;
            if (!$catId) {
                // Buat kategori baru bila belum ada
                $cName = !empty($catText) ? ucwords($catText) : 'Lain-lain';
                Database::execute("INSERT INTO asset_categories (name) VALUES (?)", [$cName]);
                $catId = (int)Database::lastInsertId();
                $catMap[$catText] = $catId;
            }

            // Resolve Holder ID
            $holderId = null;
            if (!empty($holderName)) {
                $emp = Database::fetch("SELECT id FROM employees WHERE LOWER(nama) LIKE LOWER(?) OR LOWER(nipy) = LOWER(?) LIMIT 1", ['%' . $holderName . '%', $holderName]);
                if ($emp) {
                    $holderId = (int)$emp['id'];
                }
            }

            // Generate asset code if empty
            if (empty($assetCode)) {
                $year = date('Y', strtotime($purchaseDate));
                $prefix = $isConsumable ? "ATK/{$uCode}/{$year}" : "AST/{$uCode}/{$year}";
                $last = Database::fetch("SELECT id FROM assets ORDER BY id DESC LIMIT 1");
                $nextNum = sprintf("%03d", ($last['id'] ?? 0) + $imported + 1);
                $assetCode = "{$prefix}/{$nextNum}";
            }

            // Check duplicate asset code
            $chk = Database::fetch("SELECT id FROM assets WHERE asset_code = ?", [$assetCode]);
            if ($chk) {
                $assetCode .= '-' . $row . '-' . rand(10, 99);
            }

            $stockStatus = $isConsumable ? 'Ready' : 'Aktif';

            Database::execute(
                "INSERT INTO assets (asset_code, name, category_id, purchase_date, unit_id, condition_status, 
                 user_holder_id, user_holder_name, location, price, is_consumable, stock_status, quantity, 
                 notes, created_by_user_id, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')",
                [$assetCode, $name, $catId, $purchaseDate, $targetUnitId, $condition, $holderId, $holderName, $location, $price, $isConsumable, $stockStatus, $qty, $notes, Auth::id()]
            );

            $imported++;
        }

        AuditLog::log('IMPORT_ASSETS_EXCEL', 'assets', null, null, ['imported_count' => $imported]);
        $this->setFlash('success', "Proses import berhasil! {$imported} aset telah dimasukkan ke dalam sistem.");
        $this->redirect(Auth::baseUrl() . '/sarpras/assets');
    }

    // --- EKSPOR DATA ASET KE EXCEL ---
    public function exportAssets(): void {
        $access = $this->checkSarprasAccess();
        $unitFilter = $access['isYayasan'] ? (isset($_GET['unit_id']) && $_GET['unit_id'] !== '' ? (int)$_GET['unit_id'] : null) : $access['unitId'];

        $where = ["1=1"];
        $params = [];
        if ($unitFilter !== null) {
            $where[] = "a.unit_id = ?";
            $params[] = $unitFilter;
        }

        $whereClause = implode(" AND ", $where);
        $assets = Database::fetchAll(
            "SELECT a.asset_code, a.name, c.name as category_name, a.purchase_date, u.name as unit_name,
                    a.condition_status, COALESCE(e.nama, a.user_holder_name, '-') as holder_name,
                    a.location, a.price, IF(a.is_consumable=1, 'ATK / Pecah Belah', 'Inventaris') as item_type,
                    a.quantity, a.stock_status, a.status
             FROM assets a
             LEFT JOIN asset_categories c ON a.category_id = c.id
             LEFT JOIN units u ON a.unit_id = u.id
             LEFT JOIN employees e ON a.user_holder_id = e.id
             WHERE {$whereClause}
             ORDER BY a.id DESC",
            $params
        );

        $headers = [
            'No Aset',
            'Nama / Merk / Brand',
            'Kategori',
            'Tgl Beli',
            'Unit Sekolah',
            'Kondisi',
            'User / Pemegang',
            'Lokasi',
            'Harga (Rp)',
            'Tipe Barang',
            'Jumlah / Stok',
            'Status Stok',
            'Status Aset'
        ];

        $rows = [];
        foreach ($assets as $a) {
            $rows[] = [
                $a['asset_code'],
                $a['name'],
                $a['category_name'] ?? '-',
                $a['purchase_date'],
                $a['unit_name'],
                $a['condition_status'],
                $a['holder_name'],
                $a['location'] ?? '-',
                $a['price'],
                $a['item_type'],
                $a['quantity'],
                $a['stock_status'],
                $a['status']
            ];
        }

        ExportHelper::excel('Rekap_Inventaris_Aset_' . date('Ymd'), $headers, $rows, 'DAFTAR INVENTARIS & ASET SEKOLAH YAYASAN ISLAM AL KAHFI BATAM');
    }

    // =========================================================================
    // 3. PURCHASE ORDER (PO) / FORMULIR PERMINTAAN BARANG
    // =========================================================================
    public function poIndex(): void {
        $access = $this->checkSarprasAccess();
        $isYayasan = $access['isYayasan'];
        $userUnitId = $access['unitId'];

        $unitFilter = $isYayasan ? (isset($_GET['unit_id']) && $_GET['unit_id'] !== '' ? (int)$_GET['unit_id'] : null) : $userUnitId;
        $statusFilter = trim($_GET['status'] ?? '');
        $contractorFilter = isset($_GET['filter']) && $_GET['filter'] === 'contractor';

        $where = ["1=1"];
        $params = [];

        if ($unitFilter !== null) {
            $where[] = "po.unit_id = ?";
            $params[] = $unitFilter;
        }
        if (!empty($statusFilter)) {
            $where[] = "po.status = ?";
            $params[] = $statusFilter;
        }
        if ($contractorFilter) {
            $where[] = "po.needs_contractor = 1";
        }

        $whereClause = implode(" AND ", $where);
        $orders = Database::fetchAll(
            "SELECT po.*, u.name as unit_name, u.code as unit_code,
                    COUNT(poi.id) as total_items,
                    SUM(poi.quantity_ordered) as total_ordered_qty
             FROM purchase_orders po
             LEFT JOIN units u ON po.unit_id = u.id
             LEFT JOIN purchase_order_items poi ON po.id = poi.po_id
             WHERE {$whereClause}
             GROUP BY po.id
             ORDER BY po.id DESC",
            $params
        );

        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");

        $this->view('sarpras/po_index', [
            'pageTitle'    => 'Formulir Permintaan Barang (Purchase Order)',
            'orders'       => $orders,
            'units'        => $units,
            'access'       => $access,
            'selectedUnit' => $unitFilter,
            'statusFilter' => $statusFilter
        ]);
    }

    public function poCreate(): void {
        $access = $this->checkSarprasAccess();
        $unitId = $access['unitId'];
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!$unit && !empty($access['isYayasan'])) {
            $unit = Database::fetch("SELECT * FROM units ORDER BY id ASC LIMIT 1");
            $unitId = (int)($unit['id'] ?? 2);
        }
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $categories = Database::fetchAll("SELECT * FROM asset_categories ORDER BY name ASC");

        // Prepare locations, sub-units, and assets per unit
        $assetsByUnit = [];
        $locationsByUnit = [];
        $subUnitsByUnit = [];

        foreach ($units as $u) {
            $uid = (int)$u['id'];
            $uCode = $u['code'] ?? 'AIS';
            $uName = $u['name'] ?? 'Unit Sekolah';

            // Assets of this unit with total active stock
            $assets = Database::fetchAll(
                "SELECT a.id, a.name, a.category_id, 
                        COALESCE(c.name, 'Umum') as category_name,
                        COALESCE(a.location, '') as location,
                        SUM(a.quantity) as total_stock,
                        MAX(a.is_consumable) as is_consumable
                 FROM assets a
                 LEFT JOIN asset_categories c ON a.category_id = c.id
                 WHERE a.unit_id = ? AND a.status = 'Aktif'
                 GROUP BY a.name, a.category_id
                 ORDER BY a.name ASC",
                [$uid]
            );
            $assetsByUnit[$uid] = $assets;

            // Locations for this unit
            $dbLocs = Database::fetchAll(
                "SELECT DISTINCT location FROM assets WHERE unit_id = ? AND location IS NOT NULL AND TRIM(location) != ''",
                [$uid]
            );
            $classes = Database::fetchAll(
                "SELECT name FROM classes WHERE unit_id = ? ORDER BY name ASC",
                [$uid]
            );

            $locList = [
                $uName,
                "Kantor Guru {$uCode}",
                "Ruang TU {$uCode}",
                "Ruang Kepala Sekolah {$uCode}",
                "Gudang Sarpras {$uCode}",
                "Laboratorium {$uCode}",
                "Perpustakaan {$uCode}"
            ];
            foreach ($dbLocs as $dl) {
                $val = trim($dl['location'] ?? '');
                if (!empty($val)) $locList[] = $val;
            }
            foreach ($classes as $cl) {
                $val = trim($cl['name'] ?? '');
                if (!empty($val)) $locList[] = $val;
            }
            $locationsByUnit[$uid] = array_values(array_unique(array_filter($locList)));

            // Sub Units / Beban for this unit
            $subUnitsByUnit[$uid] = [
                "{$uCode} AIS (Utama)",
                "Tata Usaha / Keuangan {$uCode}",
                "Kurikulum {$uCode}",
                "Kesiswaan {$uCode}",
                "Tahfidz & Diniyah {$uCode}",
                "Sarana & Prasarana {$uCode}",
                "Kebersihan / CS {$uCode}",
                "Keamanan / Security {$uCode}",
                "SPMB / Panitia {$uCode}"
            ];
        }

        // Generate suggested PO Number: REQ/{UNIT}/MONTHYEAR/{SEQ}
        $uCode = $unit['code'] ?? 'AIS';
        $monthIndo = [
            'January' => 'JANUARI', 'February' => 'FEBRUARI', 'March' => 'MARET',
            'April' => 'APRIL', 'May' => 'MEI', 'June' => 'JUNI',
            'July' => 'JULI', 'August' => 'AGUSTUS', 'September' => 'SEPTEMBER',
            'October' => 'OKTOBER', 'November' => 'NOVEMBER', 'December' => 'DESEMBER'
        ];
        $mName = $monthIndo[date('F')] ?? strtoupper(date('F'));
        $myText = $mName . date('Y');
        $suggestedRef = "REQ/{$uCode}/{$myText}";

        $this->view('sarpras/po_create', [
            'pageTitle'       => 'Buat Formulir Permintaan Barang (PO)',
            'unit'            => $unit,
            'units'           => $units,
            'categories'      => $categories,
            'suggestedRef'    => $suggestedRef,
            'access'          => $access,
            'assetsByUnit'    => $assetsByUnit,
            'locationsByUnit' => $locationsByUnit,
            'subUnitsByUnit'  => $subUnitsByUnit
        ]);
    }

    public function poSave(): void {
        $access = $this->checkSarprasAccess();
        $unitId = $access['isYayasan'] ? (int)($_POST['unit_id'] ?? $access['unitId']) : $access['unitId'];
        
        $poNumber = trim($_POST['po_number'] ?? '');
        $requestDate = trim($_POST['request_date'] ?? date('Y-m-d'));
        $reason = trim($_POST['reason'] ?? '');
        $procurementDivision = trim($_POST['procurement_division'] ?? 'Divisi Pengadaan Barang');
        $deliveryDate = !empty($_POST['delivery_date']) ? trim($_POST['delivery_date']) : null;
        $deliveryLocation = trim($_POST['delivery_location'] ?? '');
        if (($deliveryLocation === '__CUSTOM__' || empty($deliveryLocation)) && !empty($_POST['custom_delivery_location'])) {
            $deliveryLocation = trim($_POST['custom_delivery_location']);
        }
        $specialNotes = trim($_POST['special_notes'] ?? '');
        $needsContractor = !empty($_POST['needs_contractor']) ? 1 : 0;

        if (empty($poNumber) || empty($reason) || empty($deliveryLocation)) {
            $this->setFlash('danger', 'Mohon lengkapi Nomor PO, Alasan Permintaan, dan Lokasi Delivery.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po/create');
            return;
        }

        // Check unique PO number
        $chk = Database::fetch("SELECT id FROM purchase_orders WHERE po_number = ?", [$poNumber]);
        if ($chk) {
            $poNumber .= '/' . rand(10, 99);
        }

        $user = Auth::user();
        $employee = Database::fetch("SELECT nama FROM employees WHERE user_id = ?", [Auth::id()]);
        $requestedByName = $employee['nama'] ?? ($user['name'] ?? 'Sarpras Unit');

        Database::execute(
            "INSERT INTO purchase_orders (po_number, unit_id, created_by_user_id, request_date, reason, 
             procurement_division, delivery_date, delivery_location, special_notes, needs_contractor, 
             status, requested_by_name, requested_by_title, requested_at) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu_Kepsek', ?, 'Sarpras', NOW())",
            [$poNumber, $unitId, Auth::id(), $requestDate, $reason, $procurementDivision, $deliveryDate, $deliveryLocation, $specialNotes, $needsContractor, $requestedByName]
        );

        $poId = (int)Database::lastInsertId();

        // Save Items
        $descriptions = $_POST['description'] ?? [];
        $specs = $_POST['specs'] ?? [];
        $rapbs = $_POST['rapbs'] ?? [];
        $uom = $_POST['unit_of_measure'] ?? [];
        $stockPos = $_POST['stock_position'] ?? [];
        $qtyOrder = $_POST['quantity_ordered'] ?? [];
        $procTime = $_POST['procurement_time'] ?? [];
        $costBurden = $_POST['cost_burden_unit'] ?? [];
        $isConsumables = $_POST['is_consumable'] ?? [];
        $catIds = $_POST['item_category_id'] ?? [];

        foreach ($descriptions as $i => $desc) {
            $desc = trim($desc);
            if (empty($desc)) continue;

            $itemNo = $i + 1;
            $specVal = trim($specs[$i] ?? '');
            $rapbsVal = (isset($rapbs[$i]) && $rapbs[$i] === 'TIDAK') ? 'TIDAK' : 'YA';
            $uomVal = trim($uom[$i] ?? 'unit');
            $stockPosVal = (int)($stockPos[$i] ?? 0);
            $qtyVal = max(1, (int)($qtyOrder[$i] ?? 1));
            $costBurdenVal = trim($costBurden[$i] ?? '');
            if (($costBurdenVal === '__CUSTOM__' || empty($costBurdenVal)) && !empty($_POST['custom_cost_burden'][$i])) {
                $costBurdenVal = trim($_POST['custom_cost_burden'][$i]);
            }
            $isConsVal = !empty($isConsumables[$i]) ? 1 : 0;
            $catIdVal = !empty($catIds[$i]) ? (int)$catIds[$i] : null;

            // Upload photo for item if provided
            $itemPhotoPath = null;
            if (!empty($_FILES['item_photo']['name'][$i]) && $_FILES['item_photo']['error'][$i] === UPLOAD_ERR_OK) {
                $fileArr = [
                    'name'     => $_FILES['item_photo']['name'][$i],
                    'type'     => $_FILES['item_photo']['type'][$i],
                    'tmp_name' => $_FILES['item_photo']['tmp_name'][$i],
                    'error'    => $_FILES['item_photo']['error'][$i],
                    'size'     => $_FILES['item_photo']['size'][$i]
                ];
                $up = Uploader::upload($fileArr, 'po_items');
                if ($up['success']) {
                    $itemPhotoPath = $up['file_path'];
                }
            }

            Database::execute(
                "INSERT INTO purchase_order_items (po_id, item_no, description, specs, rapbs, unit_of_measure, 
                 stock_position, quantity_ordered, procurement_time, cost_burden_unit, photo, is_consumable, category_id, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')",
                [$poId, $itemNo, $desc, $specVal, $rapbsVal, $uomVal, $stockPosVal, $qtyVal, $procTimeVal, $costBurdenVal, $itemPhotoPath, $isConsVal, $catIdVal]
            );
        }

        AuditLog::log('CREATE_PURCHASE_ORDER', 'purchase_orders', $poId, null, [
            'po_number'        => $poNumber,
            'needs_contractor' => $needsContractor,
            'items_count'      => count($descriptions)
        ]);

        $this->setFlash('success', "Formulir Permintaan Barang ({$poNumber}) berhasil dibuat dan diteruskan ke Kepala Sekolah untuk persetujuan.");
        $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
    }

    public function poDetail(int $id): void {
        $access = $this->checkSarprasAccess();
        $po = Database::fetch(
            "SELECT po.*, u.name as unit_name, u.code as unit_code, u.address as unit_address, u.phone as unit_phone 
             FROM purchase_orders po 
             LEFT JOIN units u ON po.unit_id = u.id 
             WHERE po.id = ?",
            [$id]
        );

        if (!$po) {
            $this->setFlash('danger', 'Data Purchase Order tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po');
            return;
        }

        $items = Database::fetchAll("SELECT poi.*, c.name as category_name 
                                     FROM purchase_order_items poi 
                                     LEFT JOIN asset_categories c ON poi.category_id = c.id 
                                     WHERE poi.po_id = ? 
                                     ORDER BY poi.item_no ASC", [$id]);

        $this->view('sarpras/po_detail', [
            'pageTitle' => 'Detail PO ' . $po['po_number'],
            'po'        => $po,
            'items'     => $items,
            'access'    => $access
        ]);
    }

    // --- APPROVAL ALUR PO ---
    public function poApproveKepsek(): void {
        $this->requireRole(['kepala_sekolah', 'operator_yayasan']);
        $poId = (int)($_POST['po_id'] ?? 0);
        $notes = trim($_POST['kepsek_notes'] ?? '');

        $po = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$poId]);
        if (!$po) {
            $this->setFlash('danger', 'PO tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po');
            return;
        }

        $user = Auth::user();
        $emp = Database::fetch("SELECT nama FROM employees WHERE user_id = ?", [Auth::id()]);
        $name = $emp['nama'] ?? ($user['name'] ?? 'Kepala Sekolah');

        Database::execute(
            "UPDATE purchase_orders SET status = 'Menunggu_Direktur_Pengadaan', 
             approved_kepsek_id = ?, approved_kepsek_name = ?, approved_kepsek_at = NOW(), 
             kepsek_notes = ?, updated_at = NOW() 
             WHERE id = ?",
            [Auth::id(), $name, $notes, $poId]
        );

        AuditLog::log('APPROVE_PO_KEPSEK', 'purchase_orders', $poId, null, ['approver' => $name]);
        $this->setFlash('success', "PO {$po['po_number']} berhasil disetujui oleh Kepala Sekolah dan diteruskan ke Direktur Pengadaan.");
        $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
    }

    public function poApprovePengadaan(): void {
        $this->requireRole(['direktur_pengadaan', 'operator_yayasan']);
        $poId = (int)($_POST['po_id'] ?? 0);
        $notes = trim($_POST['pengadaan_notes'] ?? '');

        $po = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$poId]);
        if (!$po) {
            $this->setFlash('danger', 'PO tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po');
            return;
        }

        $user = Auth::user();
        $emp = Database::fetch("SELECT nama FROM employees WHERE user_id = ?", [Auth::id()]);
        $name = $emp['nama'] ?? ($user['name'] ?? 'Direktur Pengadaan');

        // Jika butuh pengerjaan lanjutan kontraktor -> teruskan ke Direktur Sarpras!
        // Jika tidak butuh kontraktor -> langsung Disetujui (siap realisasi)
        $nextStatus = ($po['needs_contractor'] == 1) ? 'Menunggu_Direktur_Sarpras' : 'Disetujui';

        Database::execute(
            "UPDATE purchase_orders SET status = ?, 
             approved_pengadaan_id = ?, approved_pengadaan_name = ?, approved_pengadaan_at = NOW(), 
             pengadaan_notes = ?, updated_at = NOW() 
             WHERE id = ?",
            [$nextStatus, Auth::id(), $name, $notes, $poId]
        );

        AuditLog::log('APPROVE_PO_PENGADAAN', 'purchase_orders', $poId, null, [
            'approver'    => $name,
            'next_status' => $nextStatus
        ]);

        if ($nextStatus === 'Menunggu_Direktur_Sarpras') {
            $this->setFlash('success', "PO {$po['po_number']} disetujui oleh Direktur Pengadaan. Karena butuh kontraktor/pengerjaan lanjutan, PO diteruskan ke Direktur Sarpras.");
        } else {
            $this->setFlash('success', "PO {$po['po_number']} telah disetujui penuh oleh Direktur Pengadaan dan siap untuk pengerjaan/realisasi.");
        }

        $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
    }

    public function poApproveSarprasDir(): void {
        $this->requireRole(['direktur_sarpras', 'operator_yayasan']);
        $poId = (int)($_POST['po_id'] ?? 0);
        $notes = trim($_POST['sarpras_dir_notes'] ?? '');

        $po = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$poId]);
        if (!$po) {
            $this->setFlash('danger', 'PO tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po');
            return;
        }

        $user = Auth::user();
        $emp = Database::fetch("SELECT nama FROM employees WHERE user_id = ?", [Auth::id()]);
        $name = $emp['nama'] ?? ($user['name'] ?? 'Direktur Sarpras');

        Database::execute(
            "UPDATE purchase_orders SET status = 'Disetujui', 
             approved_sarpras_dir_id = ?, approved_sarpras_dir_name = ?, approved_sarpras_dir_at = NOW(), 
             sarpras_dir_notes = ?, updated_at = NOW() 
             WHERE id = ?",
            [Auth::id(), $name, $notes, $poId]
        );

        AuditLog::log('APPROVE_PO_SARPRAS_DIR', 'purchase_orders', $poId, null, ['approver' => $name]);
        $this->setFlash('success', "Pengerjaan kontraktor PO {$po['po_number']} telah disetujui oleh Direktur Sarpras. Pengerjaan dapat segera dilaksanakan.");
        $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
    }

    public function poReject(): void {
        $this->requireRole(['kepala_sekolah', 'direktur_pengadaan', 'direktur_sarpras', 'operator_yayasan']);
        $poId = (int)($_POST['po_id'] ?? 0);
        $reason = trim($_POST['rejection_reason'] ?? 'Tidak memenuhi syarat pengadaan.');

        $po = Database::fetch("SELECT * FROM purchase_orders WHERE id = ?", [$poId]);
        if ($po) {
            Database::execute(
                "UPDATE purchase_orders SET status = 'Ditolak', rejection_reason = ?, rejected_by_user_id = ?, rejected_at = NOW(), updated_at = NOW() WHERE id = ?",
                [$reason, Auth::id(), $poId]
            );
            AuditLog::log('REJECT_PO', 'purchase_orders', $poId, null, ['reason' => $reason]);
            $this->setFlash('warning', "PO {$po['po_number']} telah ditolak dengan catatan: {$reason}");
        }
        $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
    }

    // --- REALISASI & SURAT JALAN & AUTO STOCKED ---
    public function poRealize(): void {
        $this->requireRole(['sarpras', 'operator_yayasan']);
        $poId = (int)($_POST['po_id'] ?? 0);
        $workStartedAt = !empty($_POST['work_started_at']) ? trim($_POST['work_started_at']) : date('Y-m-d H:i:s');
        $completedAt = !empty($_POST['completed_at']) ? trim($_POST['completed_at']) : date('Y-m-d H:i:s');
        $notes = trim($_POST['realization_notes'] ?? '');

        $po = Database::fetch("SELECT po.*, u.code as unit_code, u.name as unit_name FROM purchase_orders po LEFT JOIN units u ON po.unit_id = u.id WHERE po.id = ?", [$poId]);
        if (!$po) {
            $this->setFlash('danger', 'PO tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po');
            return;
        }

        if ($po['status'] !== 'Disetujui') {
            $this->setFlash('warning', 'PO belum disetujui atau sudah selesai diproses.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
            return;
        }

        // Hanya Sarpras dari unit terkait atau operator yayasan yang berwenang mengunggah bukti terima
        if (Auth::hasRole('sarpras') && !Auth::hasRole('operator_yayasan') && (int)$po['unit_id'] !== (int)Auth::unitId()) {
            $this->setFlash('danger', 'Akses ditolak: Hanya petugas Sarpras dari ' . ($po['unit_name'] ?? 'unit sekolah terkait') . ' yang berwenang mengunggah bukti terima dan menyelesaikan PO.');
            $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
            return;
        }

        // Upload Surat Jalan
        $deliveryLetterPath = $po['delivery_letter_file'];
        if (!empty($_FILES['delivery_letter_file']['name']) && $_FILES['delivery_letter_file']['error'] === UPLOAD_ERR_OK) {
            $up = Uploader::upload($_FILES['delivery_letter_file'], 'surat_jalan');
            if ($up['success']) {
                $deliveryLetterPath = $up['file_path'];
            }
        }

        // Upload Dokumentasi Pengerjaan / Serah Terima
        $workDocPath = $po['work_documentation_file'];
        if (!empty($_FILES['work_documentation_file']['name']) && $_FILES['work_documentation_file']['error'] === UPLOAD_ERR_OK) {
            $up = Uploader::upload($_FILES['work_documentation_file'], 'dokumentasi_realisasi');
            if ($up['success']) {
                $workDocPath = $up['file_path'];
            }
        }

        // 1. Update status PO menjadi Selesai & rekam timestamp
        Database::execute(
            "UPDATE purchase_orders SET status = 'Selesai', work_started_at = ?, completed_at = ?, 
             delivery_letter_file = ?, work_documentation_file = ?, realization_notes = ?, 
             realized_by_user_id = ?, auto_stocked_at = NOW(), updated_at = NOW() 
             WHERE id = ?",
            [$workStartedAt, $completedAt, $deliveryLetterPath, $workDocPath, $notes, Auth::id(), $poId]
        );

        // 2. OTOMATIS MASUKKAN KE SISTEM ASET & TERHITUNG SEBAGAI STOCK
        // "selanjutnya barang barang yang telah di penuhi akan otomatis masuk ke system dan terhitung sebagai stock,
        //  untuk barang pecah belah seperti ATK tidak perlu di buat stock nya cukup (Ready/Habis Stock)"
        $items = Database::fetchAll("SELECT * FROM purchase_order_items WHERE po_id = ?", [$poId]);
        $uCode = $po['unit_code'] ?? 'AIS';
        $year = date('Y', strtotime($completedAt));
        $stockAdded = 0;

        foreach ($items as $item) {
            $isCons = (int)$item['is_consumable'];
            $qty = (int)$item['quantity_ordered'];
            $catId = $item['category_id'] ?: 1;

            if ($isCons === 1) {
                // Barang ATK / Pecah belah -> status Ready (tidak perlu no seri individual)
                $atkCode = "ATK/{$uCode}/{$year}/PO" . $poId . '-' . $item['item_no'];
                Database::execute(
                    "INSERT INTO assets (asset_code, name, category_id, purchase_date, unit_id, condition_status, 
                     user_holder_name, location, photo, is_consumable, stock_status, quantity, po_id, notes, status, created_by_user_id) 
                     VALUES (?, ?, ?, ?, ?, 'Baik', 'Gudang Sarpras', ?, ?, 1, 'Ready', ?, ?, ?, 'Aktif', ?)",
                    [$atkCode, $item['description'] . ' (' . $item['specs'] . ')', $catId, date('Y-m-d', strtotime($completedAt)), $po['unit_id'], $po['delivery_location'], $item['photo'], $qty, $poId, 'Otomatis dari PO ' . $po['po_number'], Auth::id()]
                );
                $stockAdded++;
            } else {
                // Barang aset non-ATK -> dihitung sebagai stok/aset aktif per unit
                // Jika memesan lebih dari 1, kita bisa buat record per aset agar memiliki No Aset terpisah atau record dengan quantity
                for ($k = 1; $k <= $qty; $k++) {
                    $last = Database::fetch("SELECT id FROM assets ORDER BY id DESC LIMIT 1");
                    $nextSeq = sprintf("%03d", ($last['id'] ?? 0) + 1);
                    $assetCode = "AST/{$uCode}/{$year}/{$nextSeq}";

                    Database::execute(
                        "INSERT INTO assets (asset_code, name, category_id, purchase_date, unit_id, condition_status, 
                         user_holder_name, location, photo, is_consumable, stock_status, quantity, po_id, notes, status, created_by_user_id) 
                         VALUES (?, ?, ?, ?, ?, 'Baik', 'Sarpras Unit', ?, ?, 0, 'Aktif', 1, ?, ?, 'Aktif', ?)",
                        [$assetCode, $item['description'] . ($item['specs'] ? ' ' . $item['specs'] : ''), $catId, date('Y-m-d', strtotime($completedAt)), $po['unit_id'], $po['delivery_location'], $item['photo'], $poId, 'Realisasi PO ' . $po['po_number'], Auth::id()]
                    );
                    $stockAdded++;
                }
            }

            // Tandai item PO telah terpenuhi
            Database::execute("UPDATE purchase_order_items SET status = 'Terpenuhi' WHERE id = ?", [$item['id']]);
        }

        AuditLog::log('REALIZE_PO_COMPLETED', 'purchase_orders', $poId, null, [
            'po_number'    => $po['po_number'],
            'assets_added' => $stockAdded
        ]);

        $this->setFlash('success', "Pengerjaan PO {$po['po_number']} selesai dan surat jalan telah diunggah! Barang-barang telah otomatis dimasukkan ke dalam stok/inventaris sistem ({$stockAdded} item tercatat).");
        $this->redirect(Auth::baseUrl() . '/sarpras/po/detail/' . $poId);
    }

    // --- CETAK / PRINT FORMULIR PERMINTAAN BARANG (PO) ---
    public function poPrint(int $id): void {
        $this->checkSarprasAccess();
        $po = Database::fetch(
            "SELECT po.*, u.name as unit_name, u.code as unit_code, u.address as unit_address, u.phone as unit_phone, u.kop_title, u.kop_subtitle, u.logo_path 
             FROM purchase_orders po 
             LEFT JOIN units u ON po.unit_id = u.id 
             WHERE po.id = ?",
            [$id]
        );

        if (!$po) {
            die('Data PO tidak ditemukan.');
        }

        $items = Database::fetchAll("SELECT poi.*, c.name as category_name 
                                     FROM purchase_order_items poi 
                                     LEFT JOIN asset_categories c ON poi.category_id = c.id 
                                     WHERE poi.po_id = ? 
                                     ORDER BY poi.item_no ASC", [$id]);

        $this->view('sarpras/po_print', [
            'pageTitle' => 'Formulir Permintaan Barang - ' . $po['po_number'],
            'po'        => $po,
            'items'     => $items
        ], null);
    }

    // =========================================================================
    // 4. BERITA ACARA SCRAP (PENYATAAN BARANG SCRAP - FORM F30)
    // =========================================================================
    public function scrapIndex(): void {
        $access = $this->checkSarprasAccess();
        $isYayasan = $access['isYayasan'];
        $userUnitId = $access['unitId'];

        $unitFilter = $isYayasan ? (isset($_GET['unit_id']) && $_GET['unit_id'] !== '' ? (int)$_GET['unit_id'] : null) : $userUnitId;

        $where = ["1=1"];
        $params = [];
        if ($unitFilter !== null) {
            $where[] = "sr.unit_id = ?";
            $params[] = $unitFilter;
        }

        $whereClause = implode(" AND ", $where);
        $reports = Database::fetchAll(
            "SELECT sr.*, u.name as unit_name, u.code as unit_code,
                    COUNT(sri.id) as total_items,
                    SUM(sri.quantity) as total_scrapped_qty
             FROM scrap_reports sr
             LEFT JOIN units u ON sr.unit_id = u.id
             LEFT JOIN scrap_report_items sri ON sr.id = sri.scrap_report_id
             WHERE {$whereClause}
             GROUP BY sr.id
             ORDER BY sr.id DESC",
            $params
        );

        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");

        $this->view('sarpras/scrap_index', [
            'pageTitle'    => 'Berita Acara Pernyataan Barang Scrap (F30)',
            'reports'      => $reports,
            'units'        => $units,
            'access'       => $access,
            'selectedUnit' => $unitFilter
        ]);
    }

    public function scrapCreate(): void {
        $access = $this->checkSarprasAccess();
        $unitId = $access['unitId'];
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");

        // Daftar aset aktif di unit ini yang bisa di-scrap (mengambil dari data barang yang sudah masuk sistem)
        $assets = Database::fetchAll(
            "SELECT a.*, c.name as category_name, COALESCE(e.nama, a.user_holder_name) as holder_name 
             FROM assets a 
             LEFT JOIN asset_categories c ON a.category_id = c.id 
             LEFT JOIN employees e ON a.user_holder_id = e.id 
             WHERE a.unit_id = ? AND a.status = 'Aktif' 
             ORDER BY a.name ASC",
            [$unitId]
        );

        // Hari dan tanggal hari ini dalam bahasa Indonesia
        $hariIndo = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $dayName = $hariIndo[date('w')];
        $todayDate = date('Y-m-d');

        // Suggest Report Number: F30/BAS/{UNIT}/{YEAR}/{SEQ}
        $uCode = $unit['code'] ?? 'AIS';
        $year = date('Y');
        $last = Database::fetch("SELECT id FROM scrap_reports ORDER BY id DESC LIMIT 1");
        $nextSeq = sprintf("%03d", ($last['id'] ?? 0) + 1);
        $suggestedNum = "F30/BAS/{$uCode}/{$year}/{$nextSeq}";

        $this->view('sarpras/scrap_create', [
            'pageTitle'    => 'Buat Berita Acara Pernyataan Barang Scrap',
            'unit'         => $unit,
            'units'        => $units,
            'assets'       => $assets,
            'dayName'      => $dayName,
            'todayDate'    => $todayDate,
            'suggestedNum' => $suggestedNum,
            'access'       => $access
        ]);
    }

    public function scrapSave(): void {
        $access = $this->checkSarprasAccess();
        $unitId = $access['isYayasan'] ? (int)($_POST['unit_id'] ?? $access['unitId']) : $access['unitId'];
        
        $reportNumber = trim($_POST['report_number'] ?? '');
        $formCode = trim($_POST['form_code'] ?? 'F30');
        $reportDate = trim($_POST['report_date'] ?? date('Y-m-d'));
        $dayName = trim($_POST['day_name'] ?? 'Rabu');
        $reason = trim($_POST['reason'] ?? '');
        $takenByName = trim($_POST['taken_by_name'] ?? '');
        $takenByDate = !empty($_POST['taken_by_date']) ? trim($_POST['taken_by_date']) : null;
        $preparedByName = trim($_POST['prepared_by_name'] ?? 'Hasan Basri');
        $preparedByTitle = trim($_POST['prepared_by_title'] ?? 'Sarpras');
        $approvedByName = trim($_POST['approved_by_name'] ?? 'Yona Melinda, B.Eng');
        $approvedByTitle = trim($_POST['approved_by_title'] ?? 'Deputi Direktur AIS');

        if (empty($reportNumber) || empty($reason)) {
            $this->setFlash('danger', 'Nomor Berita Acara dan Sebab di-scrap wajib diisi.');
            $this->redirect(Auth::baseUrl() . '/sarpras/scrap/create');
            return;
        }

        // Check unique report number
        $chk = Database::fetch("SELECT id FROM scrap_reports WHERE report_number = ?", [$reportNumber]);
        if ($chk) {
            $reportNumber .= '/' . rand(10, 99);
        }

        Database::execute(
            "INSERT INTO scrap_reports (report_number, form_code, unit_id, report_date, day_name, reason, 
             status, taken_by_name, taken_by_date, prepared_by_name, prepared_by_title, approved_by_name, 
             approved_by_title, created_by_user_id) 
             VALUES (?, ?, ?, ?, ?, ?, 'Disetujui', ?, ?, ?, ?, ?, ?, ?)",
            [$reportNumber, $formCode, $unitId, $reportDate, $dayName, $reason, $takenByName, $takenByDate, $preparedByName, $preparedByTitle, $approvedByName, $approvedByTitle, Auth::id()]
        );

        $reportId = (int)Database::lastInsertId();

        // Process Scrap Items:
        // "barang yang di muat ke dalam berita acara scrap otomatis akan langsung dikeluarkan dari system dan sistem akan menghapus dari stock"
        // "sedangkan berita acara scrap hanya tinggal mengambil dari data barang yang sudah masuk ke system"
        $assetIds = $_POST['asset_id'] ?? [];
        $itemNames = $_POST['item_name'] ?? [];
        $quantities = $_POST['quantity'] ?? [];
        $respPersons = $_POST['responsible_person'] ?? [];
        $notes = $_POST['notes'] ?? [];

        $scrappedCount = 0;

        foreach ($itemNames as $i => $name) {
            $name = trim($name);
            if (empty($name)) continue;

            $assetId = !empty($assetIds[$i]) ? (int)$assetIds[$i] : null;
            $qty = max(1, (int)($quantities[$i] ?? 1));
            $resp = trim($respPersons[$i] ?? $approvedByName);
            $note = trim($notes[$i] ?? '');

            // Foto barang diambil otomatis dari data barang yang sudah ada di sistem
            $photoPath = null;
            if ($assetId > 0) {
                $existAsset = Database::fetch("SELECT photo, name FROM assets WHERE id = ?", [$assetId]);
                if ($existAsset && !empty($existAsset['photo'])) {
                    $photoPath = $existAsset['photo'];
                }
            }

            // Jika ada upload foto fisik baru saat scrap
            if (!empty($_FILES['scrap_photo']['name'][$i]) && $_FILES['scrap_photo']['error'][$i] === UPLOAD_ERR_OK) {
                $fileArr = [
                    'name'     => $_FILES['scrap_photo']['name'][$i],
                    'type'     => $_FILES['scrap_photo']['type'][$i],
                    'tmp_name' => $_FILES['scrap_photo']['tmp_name'][$i],
                    'error'    => $_FILES['scrap_photo']['error'][$i],
                    'size'     => $_FILES['scrap_photo']['size'][$i]
                ];
                $up = Uploader::upload($fileArr, 'scrap_photos');
                if ($up['success']) {
                    $photoPath = $up['file_path'];
                }
            }

            Database::execute(
                "INSERT INTO scrap_report_items (scrap_report_id, asset_id, item_name, quantity, responsible_person, photo, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$reportId, $assetId, $name, $qty, $resp, $photoPath, $note]
            );

            // OTOMATIS KELUARKAN DARI SISTEM & HAPUS DARI STOK AKTIF
            if ($assetId > 0) {
                Database::execute(
                    "UPDATE assets SET status = 'Scrapped', stock_status = 'Scrapped', condition_status = 'Afkir/Scrap', 
                     notes = CONCAT(COALESCE(notes, ''), ' [Di-scrap via BA ', ? , ' tgl ', ? , ']'), updated_at = NOW() 
                     WHERE id = ?",
                    [$reportNumber, $reportDate, $assetId]
                );
                $scrappedCount++;
            }
        }

        AuditLog::log('CREATE_SCRAP_REPORT', 'scrap_reports', $reportId, null, [
            'report_number'   => $reportNumber,
            'scrapped_assets' => $scrappedCount
        ]);

        $this->setFlash('success', "Berita Acara Scrap ({$reportNumber}) berhasil diterbitkan! {$scrappedCount} barang otomatis telah dikeluarkan dan dihapus dari stok aktif sistem.");
        $this->redirect(Auth::baseUrl() . '/sarpras/scrap/detail/' . $reportId);
    }

    public function scrapDetail(int $id): void {
        $access = $this->checkSarprasAccess();
        $report = Database::fetch(
            "SELECT sr.*, u.name as unit_name, u.code as unit_code, u.address as unit_address 
             FROM scrap_reports sr 
             LEFT JOIN units u ON sr.unit_id = u.id 
             WHERE sr.id = ?",
            [$id]
        );

        if (!$report) {
            $this->setFlash('danger', 'Data Berita Acara Scrap tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/sarpras/scrap');
            return;
        }

        $items = Database::fetchAll(
            "SELECT sri.*, a.asset_code 
             FROM scrap_report_items sri 
             LEFT JOIN assets a ON sri.asset_id = a.id 
             WHERE sri.scrap_report_id = ? 
             ORDER BY sri.id ASC",
            [$id]
        );

        $this->view('sarpras/scrap_detail', [
            'pageTitle' => 'Detail Berita Acara Scrap ' . $report['report_number'],
            'report'    => $report,
            'items'     => $items,
            'access'    => $access
        ]);
    }

    // --- CETAK / PRINT FORMULIR BERITA ACARA SCRAP (F30) ---
    public function scrapPrint(int $id): void {
        $this->checkSarprasAccess();
        $report = Database::fetch(
            "SELECT sr.*, u.name as unit_name, u.code as unit_code, u.address as unit_address, u.phone as unit_phone, u.kop_title, u.kop_subtitle, u.logo_path 
             FROM scrap_reports sr 
             LEFT JOIN units u ON sr.unit_id = u.id 
             WHERE sr.id = ?",
            [$id]
        );

        if (!$report) {
            die('Data Berita Acara Scrap tidak ditemukan.');
        }

        $items = Database::fetchAll(
            "SELECT sri.*, a.asset_code 
             FROM scrap_report_items sri 
             LEFT JOIN assets a ON sri.asset_id = a.id 
             WHERE sri.scrap_report_id = ? 
             ORDER BY sri.id ASC",
            [$id]
        );

        $this->view('sarpras/scrap_print', [
            'pageTitle' => 'Berita Acara Pernyataan Barang Scrap - ' . $report['report_number'],
            'report'    => $report,
            'items'     => $items
        ], null);
    }

    // --- VIEW / STREAM FILE UPLOADS ---
    public function viewFile(): void {
        $this->requireAuth();
        $file = $_GET['file'] ?? '';
        if (empty($file)) {
            http_response_code(400);
            die('Berkas tidak ditentukan.');
        }

        $cleanFile = ltrim(str_replace(['..', '\\'], ['', '/'], $file), '/');

        // 1. Cek storage/uploads
        $storageRel = preg_replace('#^(storage/uploads/|uploads/)#', '', $cleanFile);
        $storageBase = dirname(__DIR__, 2) . '/storage/uploads';
        $storageFull = realpath($storageBase . '/' . $storageRel);
        $realStorageBase = realpath($storageBase);

        if ($storageFull && $realStorageBase && strpos($storageFull, $realStorageBase) === 0 && is_file($storageFull)) {
            Uploader::stream($storageRel, null, false);
            return;
        }

        // 2. Cek public/uploads atau public/
        $publicRel = preg_replace('#^public/#', '', $cleanFile);
        $publicBase = dirname(__DIR__, 2) . '/public';
        $publicFull = realpath($publicBase . '/' . $publicRel);
        $realPublicBase = realpath($publicBase);

        if ($publicFull && $realPublicBase && strpos($publicFull, $realPublicBase) === 0 && is_file($publicFull)) {
            while (ob_get_level() > 0) ob_end_clean();
            $mime = mime_content_type($publicFull) ?: 'application/octet-stream';
            header('Content-Type: ' . $mime);
            header('Content-Length: ' . filesize($publicFull));
            header('Cache-Control: public, max-age=86400');
            readfile($publicFull);
            exit;
        }

        // 3. Fallback standar Uploader::stream
        Uploader::stream($cleanFile, null, false);
    }
}

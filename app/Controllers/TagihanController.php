<?php
// app/Controllers/TagihanController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\ExportHelper;
use Core\SecurityHelper;
use Core\Notification;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class TagihanController extends Controller {

    // =========================================================================
    // 1. MANAGE BILLS (INPUT TAGIHAN TU DENGAN MULTI-KOLOM FR 8001A)
    // =========================================================================
    public function manage(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan', 'keuangan_yayasan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $bills = Database::fetchAll(
            "SELECT b.*, s.nama as student_name, s.nis, s.parent_email, 
                    COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, 
                    s.parent_phone, s.address as alamat, c.name as class_name 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE b.unit_id = ? AND b.academic_year_id = ? 
             ORDER BY b.status DESC, b.due_date ASC, b.id DESC",
            [$unitId, $taId]
        );

        $students = Database::fetchAll(
            "SELECT s.*, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, 
                    s.address as alamat, c.name as class_name 
             FROM students s 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             ORDER BY s.nama ASC", 
            [$unitId]
        );

        // Hitung rasio tunggakan
        $totalStudents = count($students);
        $tunggakanCount = Database::fetch(
            "SELECT COUNT(DISTINCT student_id) as cnt FROM bills WHERE unit_id = ? AND academic_year_id = ? AND status = 'Tertunggak'",
            [$unitId, $taId]
        )['cnt'] ?? 0;
        $rasioTunggakan = $totalStudents > 0 ? round(($tunggakanCount / $totalStudents) * 100, 1) : 0;

        // Ambil info unit untuk data rekening BSI & Kop
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);

        $this->view('tagihan/manage', [
            'pageTitle'       => 'Input Tagihan & Rekap SPP Siswa (FR 8001A)',
            'bills'           => $bills,
            'students'        => $students,
            'totalStudents'   => $totalStudents,
            'tunggakanCount'  => $tunggakanCount,
            'rasioTunggakan'  => $rasioTunggakan,
            'unit'            => $unit
        ]);
    }

    public function saveBill(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $title = trim($_POST['title'] ?? 'Tagihan Siswa & SPP');
        $billMonth = (int)($_POST['bill_month'] ?? date('n'));
        $billYear = (int)($_POST['bill_year'] ?? date('Y'));
        $dueDate = $_POST['due_date'] ?? date('Y-m-10');
        $status = $_POST['status'] ?? 'Tertunggak';
        $notes = trim($_POST['notes'] ?? '');

        // 7 Kolom Rincian Biaya Bawaan (FR 8001A)
        $bukuPaket       = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['buku_paket'] ?? '0'));
        $kegiatanSiswa   = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['kegiatan_siswa'] ?? '0'));
        $komite          = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['komite'] ?? '0'));
        $peningkatanMutu = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['peningkatan_mutu'] ?? '0'));
        $spp             = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['spp'] ?? '0'));
        $uangUjian       = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['uang_ujian'] ?? '0'));
        $tunggakanLalu   = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['tunggakan_lalu'] ?? '0'));

        // Custom items tambahan jika ada
        $customNames = $_POST['custom_item_name'] ?? [];
        $customAmounts = $_POST['custom_item_amount'] ?? [];
        $customItems = [];
        $customTotal = 0;

        if (is_array($customNames)) {
            foreach ($customNames as $idx => $cName) {
                $cName = trim((string)$cName);
                $cVal = max(0, (float)str_replace(['Rp', '.', ' '], '', $customAmounts[$idx] ?? '0'));
                if (!empty($cName) && $cVal > 0) {
                    $customItems[] = ['name' => $cName, 'amount' => $cVal];
                    $customTotal += $cVal;
                }
            }
        }

        // Porsi SPP & Non SPP
        $amountSpp = $spp;
        $amountNonSpp = $bukuPaket + $kegiatanSiswa + $komite + $peningkatanMutu + $uangUjian + $tunggakanLalu + $customTotal;
        $totalAmount = $amountSpp + $amountNonSpp;

        $itemsDetail = [
            'buku_paket'       => $bukuPaket,
            'kegiatan_siswa'   => $kegiatanSiswa,
            'komite'           => $komite,
            'peningkatan_mutu' => $peningkatanMutu,
            'spp'              => $spp,
            'uang_ujian'       => $uangUjian,
            'tunggakan_lalu'   => $tunggakanLalu,
            'custom_items'     => $customItems
        ];

        if ($studentId && $totalAmount > 0) {
            $paidDate = ($status === 'Lunas') ? date('Y-m-d') : null;
            $paidAmount = ($status === 'Lunas') ? $totalAmount : 0.0;

            Database::execute(
                "INSERT INTO bills 
                    (unit_id, academic_year_id, student_id, bill_month, bill_year, title, amount, items_detail, amount_spp, amount_non_spp, due_date, status, pipeline_stage, paid_date, paid_amount, notes, created_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'tu_draft', ?, ?, ?, ?)",
                [
                    $unitId, $taId, $studentId, $billMonth, $billYear, $title, $totalAmount, 
                    json_encode($itemsDetail), $amountSpp, $amountNonSpp, $dueDate, $status, 
                    $paidDate, $paidAmount, $notes, $userId
                ]
            );

            AuditLog::log('CREATE_BILL_MULTI', 'bills', Database::lastInsertId(), null, [
                'amount' => $totalAmount, 
                'student_id' => $studentId,
                'items_detail' => $itemsDetail
            ]);
            $this->setFlash('success', 'Data tagihan (FR 8001A) berhasil disimpan dengan total ' . SecurityHelper::formatRupiah($totalAmount) . '.');
        } else {
            $this->setFlash('danger', 'Harap pilih santri dan isi minimal satu rincian uraian tagihan bernominal.');
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    public function deleteBill(): void {
        $this->requireRole('tata_usaha');
        $id = (int)($_POST['id'] ?? 0);
        $unitId = Auth::unitId();

        $bill = Database::fetch("SELECT * FROM bills WHERE id = ? AND unit_id = ?", [$id, $unitId]);
        if ($bill) {
            Database::execute("DELETE FROM bills WHERE id = ?", [$id]);
            AuditLog::log('DELETE_BILL', 'bills', $id, $bill, null);
            $this->setFlash('success', 'Tagihan berhasil dihapus.');
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    // =========================================================================
    // 2. TEMPLATE EXCEL & IMPORT MULTI-KOLOM (FR 8001A)
    // =========================================================================
    public function downloadTemplate(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $unit = Database::fetch("SELECT name, code FROM units WHERE id = ?", [$unitId]);
        $unitName = $unit['name'] ?? 'Unit';

        $students = Database::fetchAll(
            "SELECT s.nis, s.nama, s.gender, 
                    COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, 
                    s.parent_phone, c.name as class_name 
             FROM students s 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             ORDER BY c.name ASC, s.nama ASC",
            [$unitId]
        );

        $headers = [
            'No Urut', 'Induk', 'Nama Siswa', 'L/P', 'Nama Orang Tua/Wali', 'Nomor HP Orang Tua/Wali',
            'Buku Paket (Rp)', 'Kegiatan Siswa (Rp)', 'Komite (Rp)', 'Peningkatan Mutu (Rp)',
            'SPP (Rp)', 'Uang Ujian (Rp)', 'Tunggakan Tahun Lalu (Rp)', 'JUMLAH (Rp)'
        ];

        $rows = [];
        foreach ($students as $idx => $st) {
            $rows[] = [
                $idx + 1,
                (string)$st['nis'],
                $st['nama'],
                $st['jenis_kelamin'] ?? 'L',
                $st['parent_name'] ?? 'Wali Santri',
                $st['parent_phone'] ?? '',
                0, // Buku Paket
                0, // Kegiatan Siswa
                0, // Komite
                0, // Peningkatan Mutu
                500000, // SPP default
                0, // Uang Ujian
                0, // Tunggakan Tahun Lalu
                500000 // Total Jumlah
            ];
        }

        $filename = 'Format_FR8001A_Tagihan_' . preg_replace('/[^a-zA-Z0-9]/', '_', $unitName) . '_' . date('Y_m');
        ExportHelper::xlsx($headers, $rows, $filename, "Format Tagihan / Jenis Biaya (FR 8001A) - {$unitName}");
    }

    public function importBills(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        if (empty($_FILES['excel_file']['tmp_name'])) {
            $this->setFlash('danger', 'Harap pilih berkas Excel (.xlsx) untuk diimpor.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $filePath = $_FILES['excel_file']['tmp_name'];

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            $this->setFlash('danger', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $students = Database::fetchAll("SELECT id, nis FROM students WHERE unit_id = ? AND status = 'Aktif'", [$unitId]);
        $studentMapByNis = [];
        foreach ($students as $st) {
            $cleanNis = trim((string)$st['nis']);
            if ($cleanNis !== '') {
                $studentMapByNis[$cleanNis] = (int)$st['id'];
            }
        }

        $importedCount = 0;
        $updatedCount = 0;
        $skippedCount = 0;

        $month = (int)date('n');
        $year = (int)date('Y');
        $dueDate = date('Y-m-10');

        foreach ($rows as $rowIndex => $row) {
            $nis = trim((string)($row['B'] ?? ''));
            $colA = trim((string)($row['A'] ?? ''));

            if (empty($nis) || strtolower($nis) === 'induk' || strtolower($nis) === 'nis' || strtolower($colA) === 'no urut' || !is_numeric($colA)) {
                continue;
            }

            if (!isset($studentMapByNis[$nis])) {
                $skippedCount++;
                continue;
            }

            $studentId = $studentMapByNis[$nis];

            // 7 Kolom Biaya FR 8001A
            $bukuPaket       = max(0, (float)str_replace(['Rp', '.', ',', ' '], '', (string)($row['G'] ?? '0')));
            $kegiatanSiswa   = max(0, (float)str_replace(['Rp', '.', ',', ' '], '', (string)($row['H'] ?? '0')));
            $komite          = max(0, (float)str_replace(['Rp', '.', ',', ' '], '', (string)($row['I'] ?? '0')));
            $peningkatanMutu = max(0, (float)str_replace(['Rp', '.', ',', ' '], '', (string)($row['J'] ?? '0')));
            $spp             = max(0, (float)str_replace(['Rp', '.', ',', ' '], '', (string)($row['K'] ?? '0')));
            $uangUjian       = max(0, (float)str_replace(['Rp', '.', ',', ' '], '', (string)($row['L'] ?? '0')));
            $tunggakanLalu   = max(0, (float)str_replace(['Rp', '.', ',', ' '], '', (string)($row['M'] ?? '0')));

            $amountSpp = $spp;
            $amountNonSpp = $bukuPaket + $kegiatanSiswa + $komite + $peningkatanMutu + $uangUjian + $tunggakanLalu;
            $totalAmount = $amountSpp + $amountNonSpp;

            if ($totalAmount <= 0) {
                $skippedCount++;
                continue;
            }

            $itemsDetail = [
                'buku_paket'       => $bukuPaket,
                'kegiatan_siswa'   => $kegiatanSiswa,
                'komite'           => $komite,
                'peningkatan_mutu' => $peningkatanMutu,
                'spp'              => $spp,
                'uang_ujian'       => $uangUjian,
                'tunggakan_lalu'   => $tunggakanLalu,
                'custom_items'     => []
            ];

            $title = 'Tagihan Siswa & SPP';
            $status = 'Tertunggak';

            $existing = Database::fetch(
                "SELECT id FROM bills WHERE unit_id = ? AND academic_year_id = ? AND student_id = ? AND bill_month = ? AND bill_year = ?",
                [$unitId, $taId, $studentId, $month, $year]
            );

            if ($existing) {
                Database::execute(
                    "UPDATE bills SET amount = ?, items_detail = ?, amount_spp = ?, amount_non_spp = ?, due_date = ?, status = ? WHERE id = ?",
                    [$totalAmount, json_encode($itemsDetail), $amountSpp, $amountNonSpp, $dueDate, $status, $existing['id']]
                );
                $updatedCount++;
            } else {
                Database::execute(
                    "INSERT INTO bills 
                        (unit_id, academic_year_id, student_id, bill_month, bill_year, title, amount, items_detail, amount_spp, amount_non_spp, due_date, status, pipeline_stage, created_by_user_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'tu_draft', ?)",
                    [$unitId, $taId, $studentId, $month, $year, $title, $totalAmount, json_encode($itemsDetail), $amountSpp, $amountNonSpp, $dueDate, $status, $userId]
                );
                $importedCount++;
            }
        }

        AuditLog::log('IMPORT_BILLS_FR8001A', 'bills', $unitId, null, [
            'imported' => $importedCount,
            'updated'  => $updatedCount,
            'skipped'  => $skippedCount
        ]);

        $this->setFlash('success', "Import Excel FR 8001A Selesai: {$importedCount} tagihan baru ditambahkan, {$updatedCount} diperbarui, {$skippedCount} dilewati.");
        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    // =========================================================================
    // 3. ALUR PENGIRIMAN SURAT PENAGIHAN LEVEL TATA USAHA (FR 8001B)
    // =========================================================================
    public function printSuratFR8001B(string $id): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan']);
        $billId = (int)$id;

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    c.name as class_name, u.name as unit_name, u.phone as unit_phone, u.bank_name, 
                    u.bank_account, u.bank_holder, u.kop_title, u.kop_subtitle, u.address, u.email as unit_email,
                    u.code, u.level, u.npsn, u.akreditasi, u.nomor_akreditasi, u.nomor_izin_operasional, u.logo_path
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }

        $formattedAmount = number_format((float)$bill['amount'], 0, ',', '.');
        $bsiAcc = $bill['bank_account'] ?: '1033667414';
        $bsiHolder = $bill['bank_holder'] ?: $bill['unit_name'];
        $unitPhone = $bill['unit_phone'] ?: '-';

        // Ambil data staf Tata Usaha aktif
        $tuEmp = Database::fetch(
            "SELECT e.nama, e.nipy 
             FROM employees e 
             JOIN user_roles ur ON e.user_id = ur.user_id 
             JOIN roles r ON ur.role_id = r.id 
             WHERE e.unit_id = ? AND r.name = 'tata_usaha' AND e.status = 'Aktif' 
             LIMIT 1",
            [$bill['unit_id']]
        );
        if (!$tuEmp) {
            $tuEmp = Database::fetch(
                "SELECT nama, nipy FROM employees 
                 WHERE unit_id = ? AND (level_posisi LIKE '%Tata Usaha%' OR position LIKE '%Tata Usaha%') AND status = 'Aktif' 
                 LIMIT 1", 
                [$bill['unit_id']]
            );
        }
        $tuName = $tuEmp['nama'] ?? 'Staff Tata Usaha';
        $tuNipy = $tuEmp['nipy'] ?? '-';

        // Render Kop Surat standar dual logo
        $kopHtml = ExportHelper::renderKop($bill);
        $letterNo = !empty($bill['tu_reminder_letter_no']) ? $bill['tu_reminder_letter_no'] : ('REM/TU/' . date('Ym') . '/' . sprintf('%04d', $billId));

        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Format Surat Reminder Jumlah Tunggakan - FR 8001B</title>
            <style>
                @page { margin: 15mm 20mm 15mm 20mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.45; color: #111; }
                .kop-wrapper { margin-bottom: 16px; }
                .meta-table { width: 100%; margin-bottom: 16px; font-size: 11pt; }
                .meta-table td { padding: 2px 0; vertical-align: top; }
                .salutation { margin-bottom: 14px; }
                .content-p { text-align: justify; margin-bottom: 12px; line-height: 1.5; }
                .amount-highlight { font-weight: bold; font-size: 12pt; color: #b91c1c; }
                .bank-box { background: #f8fafc; border: 1px solid #cbd5e1; padding: 10px 14px; margin: 12px 0; line-height: 1.5; border-radius: 4px; }
                .contact-info { margin-top: 10px; margin-bottom: 14px; }
                .closing { margin-top: 14px; margin-bottom: 18px; }
                .signature-table { width: 100%; border-collapse: collapse; margin-top: 20px; font-size: 11pt; }
            </style>
        </head>
        <body>
            <div class='kop-wrapper'>
                {$kopHtml}
            </div>

            <table class='meta-table'>
                <tr>
                    <td style='width: 15%;'>Nomor</td>
                    <td style='width: 3%;'>:</td>
                    <td style='width: 47%;'>{$letterNo}</td>
                    <td style='width: 35%; text-align: right;'>Batam, " . date('d F Y') . "</td>
                </tr>
                <tr>
                    <td>Lampiran</td>
                    <td>:</td>
                    <td>-</td>
                    <td style='text-align: right; color: #475569; font-size: 10pt;'><strong>FR 8001B</strong></td>
                </tr>
                <tr>
                    <td>Hal</td>
                    <td>:</td>
                    <td colspan='2'><strong>Surat Reminder Jumlah Tunggakan SPP / Biaya Pendidikan</strong></td>
                </tr>
            </table>

            <div class='salutation'>
                Kepada Yth. :<br>
                <strong>Orang Tua / Wali Dari ananda " . SecurityHelper::escape($bill['student_name']) . " (NIS: " . SecurityHelper::escape($bill['nis']) . ")</strong><br>
                Kelas: " . SecurityHelper::escape($bill['class_name'] ?? '-') . "<br>
                Di - Tempat
            </div>

            <div class='content-p' style='font-style: italic;'>
                Assalamu'alaikum warahmatullahi wabarakatuh,
            </div>

            <div class='content-p'>
                Segala puji hanyalah milik Allah Subhaanahu wa Ta'ala, kita memuji-Nya dengan nama-nama dan sifat-sifat-Nya yang mulia. Shalawat dan salam senantiasa tercurah atas junjungan dan panutan kita, Nabi Muhammad Shallallahu 'alaihi wa sallam beserta keluarga dan sahabatnya. Melalui surat ini kami sampaikan kepada Bapak/Ibu wali murid, bahwa berkaitan dengan kelancaran Kelangsungan Belajar Mengajar (KBM) di sekolah, sudi kiranya Bapak/Ibu berkenan menjadi pembuka jalan kebaikan.
            </div>

            <div class='content-p'>
                Berdasarkan data keuangan kami sampai dengan tanggal " . date('d F Y') . ", terdapat beberapa pembiayaan Bapak/Ibu yang belum diselesaikan dengan rincian kewajiban sebesar:
                <div style='margin-top: 6px; font-size: 13pt; text-align: center;' class='amount-highlight'>
                    Rp {$formattedAmount},00
                </div>
            </div>

            <div class='content-p'>
                Untuk itu kami mendo'akan semoga Allah Subhaanahu wa Ta'ala senantiasa melimpahkan kemudahan, kelapangan rezeki, dan barakah sehingga biaya yang belum diselesaikan dapat segera dipenuhi.
            </div>

            <div class='bank-box'>
                Pembayaran dapat ditransfer melalui rekening resmi sekolah:<br>
                Bank: <strong>Bank Syariah Indonesia (BSI)</strong><br>
                Nomor Rekening: <strong style='font-size: 12pt;'>{$bsiAcc}</strong><br>
                Atas Nama: <strong>{$bsiHolder}</strong>
            </div>

            <div class='contact-info'>
                Untuk keterangan lebih lanjut dan konfirmasi bukti pembayaran, silakan menghubungi bagian keuangan (TU " . SecurityHelper::escape($bill['unit_name']) . ") di nomor telepon: <strong>{$unitPhone}</strong>.
            </div>

            <div class='content-p'>
                Demikian surat reminder ini kami sampaikan, atas perhatian dan kerjasama Bapak/Ibu kami ucapkan <em>Jazakumullahu khairan katsiran</em>.
            </div>

            <div class='closing' style='font-style: italic;'>
                Wassalamu'alaikum warahmatullahi wabarakatuh.
            </div>

            <table class='signature-table'>
                <tr>
                    <td style='width: 55%;'></td>
                    <td style='width: 45%; text-align: center; line-height: 1.4;'>
                        Bagian Tata Usaha & Keuangan<br>
                        <strong>" . SecurityHelper::escape($bill['unit_name']) . "</strong>
                        <br><br><br><br><br>
                        <strong><u>" . SecurityHelper::escape($tuName) . "</u></strong><br>
                        <span>NIPY. " . SecurityHelper::escape($tuNipy) . "</span>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Surat_Reminder_FR8001B_' . $bill['nis'], 'A4', 'portrait', true);
    }

    private function getSmtpConfig(int $unitId): ?array {
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!empty($unit['smtp_host']) && !empty($unit['smtp_user'])) {
            return [
                'host'       => $unit['smtp_host'],
                'port'       => (int)($unit['smtp_port'] ?: 587),
                'username'   => $unit['smtp_user'],
                'password'   => SecurityHelper::decrypt($unit['smtp_pass']) ?: $unit['smtp_pass'],
                'encryption' => $unit['smtp_enc'] ?: 'tls',
                'from_email' => $unit['smtp_user'],
                'from_name'  => $unit['name']
            ];
        }

        try {
            $smtp = Database::fetch("SELECT * FROM smtp_settings WHERE unit_id = ? OR unit_id IS NULL ORDER BY unit_id DESC LIMIT 1", [$unitId]);
            if ($smtp && !empty($smtp['host'])) {
                return [
                    'host'       => $smtp['host'],
                    'port'       => (int)($smtp['port'] ?: 587),
                    'username'   => $smtp['username'],
                    'password'   => $smtp['password'],
                    'encryption' => $smtp['encryption'] ?: 'tls',
                    'from_email' => $smtp['from_email'],
                    'from_name'  => $smtp['from_name']
                ];
            }
        } catch (\Exception $e) {
            // Abaikan error tabel tidak ditemukan
        }

        return null;
    }

    public function kirimSuratFR8001B(): void {
        $this->requireRole('tata_usaha');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, s.parent_email, u.name as unit_name 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             WHERE b.id = ? AND b.unit_id = ?",
            [$billId, $unitId]
        );

        if (!$bill) {
            $this->setFlash('danger', 'Data tagihan tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $letterNo = 'REM/TU/' . date('Ym') . '/' . sprintf('%04d', $billId);

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = 'tu_reminder_sent', 
                 tu_reminder_sent_at = NOW(), 
                 tu_reminder_letter_no = ? 
             WHERE id = ?",
            [$letterNo, $billId]
        );

        // Kirim email notifikasi jika SMTP tersedia secara aman
        $smtp = $this->getSmtpConfig($unitId);
        $emailSent = false;
        if ($smtp && !empty($bill['parent_email'])) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $smtp['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp['username'];
                $mail->Password   = $smtp['password'];
                $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = (int)$smtp['port'];

                $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                $mail->addAddress($bill['parent_email'], $bill['student_name']);
                $mail->isHTML(true);
                $mail->Subject = "[Reminder Tunggakan - FR 8001B] " . $bill['student_name'] . " - " . $bill['unit_name'];
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                        <h4 style='color: #0f766e; margin-top: 0;'>Pemberitahuan Tagihan Sekolah (FR 8001B)</h4>
                        <p>Yth. Orang Tua dari ananda <strong>{$bill['student_name']}</strong>,</p>
                        <p>Berdasarkan data kami, terdapat kewajiban pembiayaan sekolah yang belum terselesaikan sebesar <strong>" . SecurityHelper::formatRupiah((float)$bill['amount']) . "</strong>.</p>
                        <p>Mohon dapat melakukan konfirmasi atau penyelesaian pembayaran melalui loket Tata Usaha sekolah kami.</p>
                        <p><em>Jazakumullah Khairan Katsiran.</em></p>
                    </div>";
                $mail->send();
                $emailSent = true;
            } catch (\Exception $e) {
                // Email error dicatat namun alur sistem tetap sukses
            }
        }

        AuditLog::log('SEND_REMINDER_FR8001B', 'bills', $billId, null, ['letter_no' => $letterNo, 'email_sent' => $emailSent]);
        $msg = 'Surat reminder penagihan FR 8001B berhasil diterbitkan oleh TU (' . $letterNo . ').';
        if ($emailSent) {
            $msg .= ' Email pemberitahuan juga telah berhasil dikirim ke orang tua.';
        } elseif (empty($smtp)) {
            $msg .= ' (Catatan: Pengiriman email dilewati karena SMTP unit belum dikonfigurasi).';
        }
        $this->setFlash('success', $msg);
        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    public function markSelesaiTU(): void {
        $this->requireRole('tata_usaha');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();

        $bill = Database::fetch("SELECT * FROM bills WHERE id = ? AND unit_id = ?", [$billId, $unitId]);
        if ($bill) {
            Database::execute(
                "UPDATE bills 
                 SET status = 'Lunas', 
                     paid_date = NOW(), 
                     paid_amount = amount, 
                     pipeline_stage = 'selesai_tu', 
                     pipeline_resolved_at = NOW(), 
                     pipeline_resolved_level = 'tata_usaha' 
                 WHERE id = ?",
                [$billId]
            );

            AuditLog::log('MARK_RESOLVED_TU', 'bills', $billId, null, ['amount' => $bill['amount']]);
            $this->setFlash('success', 'Tagihan berhasil ditandai SELESAI / LUNAS di tingkat Tata Usaha.');
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    public function eskalasiKepsek(): void {
        $this->requireRole('tata_usaha');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();
        $notes = trim($_POST['escalation_notes'] ?? 'Orang tua belum merespons pengingat TU, diajukan pemanggilan Kepala Sekolah.');

        $bill = Database::fetch("SELECT * FROM bills WHERE id = ? AND unit_id = ?", [$billId, $unitId]);
        if ($bill) {
            Database::execute(
                "UPDATE bills 
                 SET pipeline_stage = 'escalated_kepsek', 
                     notes = CONCAT(COALESCE(notes, ''), '\n[Eskalasi ke KS]: ', ?) 
                 WHERE id = ?",
                [$notes, $billId]
            );

            // Kirim notifikasi ke Kepala Sekolah
            $kepsek = Database::fetch(
                "SELECT u.id FROM users u 
                 JOIN user_roles ur ON u.id = ur.user_id 
                 JOIN roles r ON ur.role_id = r.id 
                 JOIN employees e ON u.id = e.user_id 
                 WHERE r.name = 'kepala_sekolah' AND e.unit_id = ? LIMIT 1",
                [$unitId]
            );
            if ($kepsek) {
                Notification::send((int)$kepsek['id'], 'Pengajuan Penagihan dari TU (FR 8001C)', 'TU telah mengajukan tagihan tertunggak untuk dilakukan pemanggilan orang tua oleh Kepala Sekolah.', 'warning', '/tagihan/approval');
            }

            AuditLog::log('ESCALATE_TO_KEPSEK', 'bills', $billId, null, ['notes' => $notes]);
            $this->setFlash('success', 'Tagihan berhasil diajukan ke Kepala Sekolah untuk pemanggilan orang tua (FR 8001C).');
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    public function bulkEscalateToKepsek(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $billIds = $_POST['bill_ids'] ?? [];

        if (empty($billIds) || !is_array($billIds)) {
            $this->setFlash('warning', 'Pilih minimal satu tagihan untuk diajukan ke Kepala Sekolah.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $count = 0;
        foreach ($billIds as $bId) {
            $billId = (int)$bId;
            Database::execute(
                "UPDATE bills SET pipeline_stage = 'escalated_kepsek' WHERE id = ? AND unit_id = ? AND status = 'Tertunggak'",
                [$billId, $unitId]
            );
            $count++;
        }

        if ($count > 0) {
            $kepsek = Database::fetch(
                "SELECT u.id FROM users u 
                 JOIN user_roles ur ON u.id = ur.user_id 
                 JOIN roles r ON ur.role_id = r.id 
                 JOIN employees e ON u.id = e.user_id 
                 WHERE r.name = 'kepala_sekolah' AND e.unit_id = ? LIMIT 1",
                [$unitId]
            );
            if ($kepsek) {
                Notification::send((int)$kepsek['id'], 'Pengajuan Masal Penagihan dari TU', "TU telah mengajukan {$count} tagihan untuk ditindaklanjuti dengan pemanggilan orang tua.", 'warning', '/tagihan/approval');
            }
            $this->setFlash('success', "Berhasil mengajukan {$count} tagihan ke Kepala Sekolah.");
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    // =========================================================================
    // 4. ALUR KEPALA SEKOLAH (FR 8001C PEMANGGILAN & FR 8001D KESEPAKATAN)
    // =========================================================================
    public function approvalList(): void {
        $this->requireRole(['kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();

        $bills = Database::fetchAll(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik, c.name as class_name, u.name as unit_name, u.phone as unit_phone,
                    u.bank_name, u.bank_account, u.bank_holder, ks.nama as kepsek_name, ks.contact_phone as kepsek_phone
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN employees ks ON u.id = ks.unit_id AND (ks.position LIKE '%Kepala%' OR ks.level_posisi LIKE '%Kepala%')
             WHERE b.unit_id = ? AND (b.pipeline_stage IN ('escalated_kepsek', 'kepsek_summon_sent', 'selesai_kepsek') OR b.status = 'Tertunggak')
             ORDER BY FIELD(b.pipeline_stage, 'escalated_kepsek', 'kepsek_summon_sent', 'selesai_kepsek') ASC, b.due_date ASC",
            [$unitId]
        );

        $pendingCount = 0;
        $totalNominalPending = 0;
        foreach ($bills as $b) {
            if ($b['pipeline_stage'] === 'escalated_kepsek' || $b['pipeline_stage'] === 'kepsek_summon_sent') {
                $pendingCount++;
                $totalNominalPending += (float)$b['amount'];
            }
        }

        // Ambil info Kepala Sekolah untuk default no HP
        $kepsekEmp = Database::fetch(
            "SELECT e.nama, e.contact_phone as phone FROM employees e WHERE e.unit_id = ? AND (e.position LIKE '%Kepala%' OR e.level_posisi LIKE '%Kepala%') LIMIT 1",
            [$unitId]
        );

        $this->view('tagihan/approval', [
            'pageTitle'           => 'Penagihan Kepala Sekolah & Pemanggilan Orang Tua (FR 8001C/D)',
            'bills'               => $bills,
            'pendingCount'        => $pendingCount,
            'totalNominalPending' => $totalNominalPending,
            'kepsekEmp'           => $kepsekEmp
        ]);
    }

    public function saveKepsekSchedule(): void {
        $this->requireRole('kepala_sekolah');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();
        $date = trim($_POST['schedule_date'] ?? '');
        $time = trim($_POST['schedule_time'] ?? '09:00');
        $place = trim($_POST['schedule_place'] ?? 'Ruang Kepala Sekolah');
        $kepsekPhone = trim($_POST['kepsek_phone'] ?? '');

        if (!$billId || empty($date)) {
            $this->setFlash('danger', 'Harap tentukan tanggal schedule pemanggilan.');
            $this->redirect(Auth::baseUrl() . '/tagihan/approval');
            return;
        }

        $fullDateTime = $date . ' ' . $time . ':00';
        $letterNo = 'PANG/KS/' . date('Ym') . '/' . sprintf('%04d', $billId);

        Database::execute(
            "UPDATE bills 
             SET kepsek_schedule_date = ?, 
                 kepsek_schedule_place = ?, 
                 kepsek_letter_no = ?, 
                 kepsek_summon_sent_at = NOW(), 
                 pipeline_stage = 'kepsek_summon_sent' 
             WHERE id = ? AND unit_id = ?",
            [$fullDateTime, $place, $letterNo, $billId, $unitId]
        );

        AuditLog::log('SET_KEPSEK_SUMMON', 'bills', $billId, null, [
            'schedule' => $fullDateTime,
            'place' => $place,
            'letter_no' => $letterNo
        ]);

        $this->setFlash('success', 'Jadwal pemanggilan orang tua (FR 8001C) berhasil ditetapkan. Anda dapat langsung mencetak surat pemanggilan.');
        $this->redirect(Auth::baseUrl() . '/tagihan/approval');
    }

    public function printSuratFR8001C(string $id): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan']);
        $billId = (int)$id;

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    c.name as class_name, u.name as unit_name, u.phone as unit_phone, u.bank_name, 
                    u.bank_account, u.bank_holder, u.kop_title, u.kop_subtitle, u.address, u.email as unit_email,
                    ks.nama as kepsek_name, ks.contact_phone as kepsek_phone 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN employees ks ON u.id = ks.unit_id AND (ks.position LIKE '%Kepala%' OR ks.level_posisi LIKE '%Kepala%')
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }

        $kop = ExportHelper::renderKop($bill);
        $letterNo = $bill['kepsek_letter_no'] ?: ('PANG/KS/' . date('Ym') . '/' . sprintf('%04d', $billId));
        $bsiAcc = $bill['bank_account'] ?: '1033667414';
        $bsiHolder = $bill['bank_holder'] ?: $bill['unit_name'];
        $kepsekPhone = $bill['kepsek_phone'] ?: ($bill['unit_phone'] ?: '0812-XXXX-XXXX');
        $kepsekName = $bill['kepsek_name'] ?: 'Kepala Sekolah';

        $sppFmt = number_format((float)$bill['amount_spp'], 0, ',', '.');
        $nonSppFmt = number_format((float)$bill['amount_non_spp'], 0, ',', '.');
        $totalFmt = number_format((float)$bill['amount'], 0, ',', '.');

        $schedText = '';
        if (!empty($bill['kepsek_schedule_date'])) {
            $ts = strtotime($bill['kepsek_schedule_date']);
            $schedText = "
            <div style='margin: 15px 0; padding: 10px 14px; background: #f8fafc; border-left: 4px solid #0f766e;'>
                <strong>Jadwal Pertemuan / Pertemuan Langsung:</strong><br>
                Hari / Tanggal : <strong>" . SecurityHelper::formatTanggalIndo(date('Y-m-d', $ts)) . "</strong><br>
                Pukul / Waktu &nbsp;: <strong>" . date('H:i', $ts) . " WIB</strong><br>
                Tempat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <strong>" . SecurityHelper::escape($bill['kepsek_schedule_place'] ?: 'Ruang Kepala Sekolah') . "</strong>
            </div>";
        }

        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Format Surat Pemanggilan Orangtua - FR 8001C</title>
            <style>
                @page { margin: 15mm 20mm 20mm 20mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 11.5pt; line-height: 1.45; color: #111; }
                .doc-code { font-size: 10pt; font-weight: normal; margin-bottom: 8px; color: #444; }
                .meta-table { width: 100%; margin-top: 10px; margin-bottom: 12px; }
                .meta-table td { padding: 2px; vertical-align: top; }
                .data-table { width: 100%; border-collapse: collapse; margin: 12px 0; }
                .data-table th, .data-table td { border: 1px solid #222; padding: 6px 8px; font-size: 11pt; }
                .data-table th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .signatures { width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 11.5pt; }
            </style>
        </head>
        <body>
            <div class='doc-code'>7.3 Format Surat Pemanggilan Orangtua - <strong>FR 8001C</strong></div>
            {$kop}

            <table class='meta-table'>
                <tr>
                    <td width='12%'>No.</td><td width='2%'>:</td><td width='46%'>{$letterNo}</td>
                    <td width='40%' style='text-align: right;'>Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "</td>
                </tr>
                <tr>
                    <td>Lamp.</td><td>:</td><td>-</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Perihal</td><td>:</td><td><strong>Informasi Nominal Tunggakan dan Tagihan</strong></td>
                    <td></td>
                </tr>
            </table>

            <div style='margin-top: 10px; margin-bottom: 12px;'>
                Kepada Ykh. :<br>
                <strong>Orang Tua Dari " . SecurityHelper::escape($bill['student_name']) . "</strong><br>
                Kelas " . SecurityHelper::escape($bill['class_name'] ?? 'Aktif') . "<br>
                Di &ndash; Tempat
            </div>

            <div style='font-style: italic; margin-bottom: 8px;'>Assalamu'alaikum warahmatullahi wabarakaatuh,</div>

            <div style='text-align: justify; margin-bottom: 8px;'>
                Segala puji hanyalah milik Allah, kita memujinya dengan nama-nama dan sifat-sifat Nya yang baik, Shalawat dan salam senantiasa tercurah atas junjungan dan panutan hidup kita, Nabi Muhammad Shallallahu'alaihi wa sallam.
            </div>

            <div style='text-align: justify; margin-bottom: 10px;'>
                Melalui surat ini kami beritahukan kepada Bapak/Ibu wali murid, bahwa berkaitan dengan Kelangsungan Belajar Mengajar (KBM), maka sudi kiranya Bapak/Ibu berkenan menjadi pembuka jalan kebaikan. Berdasarkan data yang ada pada kami, ada beberapa pembiayaan Bapak/Ibu tertunggak, untuk itu kami bahagia jika tunggakan tersebut bisa segera di penuhi. Pembayaran bisa ditransfer ke <strong>Bank BSI, No Rekening {$bsiAcc}, AN: {$bsiHolder} ({$bill['unit_name']})</strong>. Adapun rinciannya yaitu:
            </div>

            <!-- Tabel 2 Kolom Tunggakan SPP & Non SPP (FR 8001C) -->
            <table class='data-table'>
                <thead>
                    <tr>
                        <th width='8%'>No</th>
                        <th width='32%'>Tunggakan SPP (Rp.)</th>
                        <th width='35%'>Tunggakan Non SPP (Rp)</th>
                        <th width='25%'>JUMLAH</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td align='center'>1</td>
                        <td align='right' style='font-weight: bold;'>Rp {$sppFmt}</td>
                        <td align='right' style='font-weight: bold;'>Rp {$nonSppFmt}</td>
                        <td align='right' style='font-weight: bold; color: #b91c1c;'>Rp {$totalFmt}</td>
                    </tr>
                </tbody>
            </table>
            <div style='font-size: 9.5pt; font-style: italic; color: #555; margin-bottom: 10px;'>
                *Data di ambil per tanggal " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . " / Abaikan surat ini jika sudah dibayar
            </div>

            {$schedText}

            <div style='text-align: justify; margin-bottom: 10px;'>
                <strong>Kami menunggu respon Bapak/Ibu langkah penyelesaian pembayaran tunggakan diatas dengan menghubungi kami selaku kepala sekolah, di no Hp. {$kepsekPhone}</strong>
            </div>

            <div style='text-align: justify; margin-bottom: 15px;'>
                Demikian yang dapat kami sampaikan, semoga Allah mudahkan kita dalam meraih kebaikan. Atas perhatiannya kami ucapkan Jazaakumullaahu khairan (semoga Allah membalas dengan yang lebih baik).<br>
                <em style='display: block; margin-top: 6px;'>Wassalamu'alaikum warahmatullahi wabarakaatuh</em>
            </div>

            <table class='signatures'>
                <tr>
                    <td class='sig-box'>&nbsp;</td>
                    <td class='sig-box'>
                        Kepala Satuan Pendidikan,<br>
                        <strong>{$bill['unit_name']}</strong><br><br><br><br>
                        <strong><u>{$kepsekName}</u></strong>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Surat_Panggilan_FR8001C_' . $bill['nis'], 'A4', 'portrait', true);
    }

    public function printSuratFR8001D(string $id): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan']);
        $billId = (int)$id;

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik, c.name as class_name, u.name as unit_name, u.kop_title, u.kop_subtitle, 
                    u.address, u.phone, u.email 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }

        $kop = ExportHelper::renderKop($bill);
        $parentName = $bill['parent_name'] ?: '..........................................';
        $parentKtp = $bill['nik'] ?: '..........................................';
        $parentAddress = $bill['alamat'] ?: '..........................................';
        $parentPhone = $bill['parent_phone'] ?: '..........................................';
        $studentName = $bill['student_name'];
        $className = $bill['class_name'] ?: '..........................................';

        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Format Surat Kesepakatan Pembayaran Tunggakan - FR 8001D</title>
            <style>
                @page { margin: 15mm 20mm 20mm 20mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #111; }
                .doc-code { font-size: 10pt; font-weight: normal; margin-bottom: 6px; color: #444; }
                .title-box { text-align: center; font-weight: bold; font-size: 12.5pt; text-decoration: underline; margin-bottom: 18px; }
                .form-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
                .form-table td { padding: 3px 2px; vertical-align: top; font-size: 11.5pt; }
                .options-list { margin-top: 10px; margin-bottom: 20px; line-height: 1.8; }
                .closing { margin-top: 15px; margin-bottom: 30px; text-align: justify; }
                .signatures { width: 100%; margin-top: 10px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 11.5pt; }
            </style>
        </head>
        <body>
            <div class='doc-code'>7.4 Format Surat Kesepakatan Pembayaran Tunggakan - <strong>FR 8001D</strong></div>
            {$kop}

            <div class='title-box'>SURAT PERNYATAAN PEMBAYARAN TUNGGAKAN BIAYA SEKOLAH</div>

            <div style='margin-bottom: 10px;'>Saya yang bertanda tangan di bawah ini:</div>

            <table class='form-table'>
                <tr>
                    <td width='22%'>Nama</td><td width='2%'>:</td>
                    <td><strong>{$parentName}</strong></td>
                </tr>
                <tr>
                    <td>No KTP</td><td>:</td>
                    <td>{$parentKtp}</td>
                </tr>
                <tr>
                    <td>Alamat</td><td>:</td>
                    <td>{$parentAddress}</td>
                </tr>
                <tr>
                    <td>No Tlp/ HP</td><td>:</td>
                    <td>{$parentPhone}</td>
                </tr>
            </table>

            <div style='margin-top: 10px; margin-bottom: 6px;'>Wali/Orang tua murid dari siswa</div>
            <table class='form-table'>
                <tr>
                    <td width='22%'>Nama</td><td width='2%'>:</td>
                    <td><strong>{$studentName}</strong> (NIS: {$bill['nis']})</td>
                </tr>
                <tr>
                    <td>Kelas</td><td>:</td>
                    <td>{$className}</td>
                </tr>
            </table>

            <div style='margin-top: 12px; text-align: justify;'>
                Dengan ini Insya Alloh akan menyelesaikan tunggakan sesuai dengan surat pemberitahuan dengan cara :
            </div>

            <div class='options-list'>
                <div><strong>A.</strong> Membayar lunas pada tanggal ..........................................</div>
                <div><strong>B.</strong> Dicicil selama ......bulan terhitung mulai tanggal ..........................................</div>
                <div><strong>C.</strong> Dicicil dengan minimal Rp................../bulan sampai dengan tanggal ..........................................</div>
            </div>

            <div class='closing'>
                Demikian surat pernyataan ini diajukan. Atas bantuan dan kerjasama yang diberikan, saya ucapkan <em>jazaakumullaahu khairan</em>.
            </div>

            <table class='signatures'>
                <tr>
                    <td class='sig-box'>&nbsp;</td>
                    <td class='sig-box'>
                        Batam, ..........................................<br><br>
                        Yang mengajukan<br>
                        <strong>Orangtua</strong><br><br><br><br><br>
                        ( .......................................... )
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Form_Kesepakatan_FR8001D_' . $bill['nis'], 'A4', 'portrait', true);
    }

    public function approveKesepakatanFR8001D(): void {
        $this->requireRole(['kepala_sekolah', 'deputi_pendidikan']);
        $userId = Auth::id();
        $userLevel = Auth::hasRole('deputi_pendidikan') ? 'deputi_pendidikan' : 'kepala_sekolah';

        $billId = (int)($_POST['bill_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $unitId = (int)($_POST['unit_id'] ?? Auth::unitId());

        $parentName = trim($_POST['parent_name'] ?? '');
        $parentKtp = trim($_POST['parent_ktp'] ?? '');
        $parentAddress = trim($_POST['parent_address'] ?? '');
        $parentPhone = trim($_POST['parent_phone'] ?? '');
        $studentClass = trim($_POST['student_class'] ?? '');

        $schemeType = $_POST['scheme_type'] ?? 'cicil_bulan'; // lunas_tanggal / cicil_bulan / cicil_nominal
        $schemeLunasDate = !empty($_POST['scheme_lunas_date']) ? $_POST['scheme_lunas_date'] : null;
        $schemeMonths = !empty($_POST['scheme_months']) ? (int)$_POST['scheme_months'] : null;
        $schemeStartDate = !empty($_POST['scheme_start_date']) ? $_POST['scheme_start_date'] : null;
        $schemeMinMonthly = !empty($_POST['scheme_min_monthly']) ? (float)str_replace(['Rp', '.', ' '], '', $_POST['scheme_min_monthly']) : null;
        $schemeEndDate = !empty($_POST['scheme_end_date']) ? $_POST['scheme_end_date'] : null;

        $totalDebt = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['total_debt'] ?? '0'));
        $notes = trim($_POST['agreement_notes'] ?? '');

        if (!$studentId || $totalDebt <= 0 || empty($parentName)) {
            $this->setFlash('danger', 'Harap lengkapi data kesepakatan orang tua dan nominal tunggakan.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/tagihan/approval'));
            return;
        }

        $agreementNumber = 'FR8001D/' . date('Ym') . '/' . sprintf('%04d', rand(100, 9999));

        Database::execute(
            "INSERT INTO bill_installments 
                (agreement_number, unit_id, bill_id, student_id, parent_name, parent_ktp, parent_address, parent_phone, 
                 student_class, scheme_type, scheme_lunas_date, scheme_months, scheme_start_date, scheme_min_monthly, 
                 scheme_end_date, total_debt, paid_total, remaining_debt, status, approved_by_level, approved_by_user_id, agreement_date, notes) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0.00, ?, 'Aktif', ?, ?, CURDATE(), ?)",
            [
                $agreementNumber, $unitId, ($billId ?: null), $studentId, $parentName, $parentKtp, $parentAddress, $parentPhone,
                $studentClass, $schemeType, $schemeLunasDate, $schemeMonths, $schemeStartDate, $schemeMinMonthly,
                $schemeEndDate, $totalDebt, $totalDebt, $userLevel, $userId, $notes
            ]
        );

        $installmentId = Database::lastInsertId();

        // Update stage pada bill terkait
        if ($billId > 0) {
            $targetStage = ($userLevel === 'deputi_pendidikan') ? 'selesai_deputi' : 'selesai_kepsek';
            Database::execute(
                "UPDATE bills 
                 SET pipeline_stage = ?, 
                     pipeline_resolved_at = NOW(), 
                     pipeline_resolved_level = ? 
                 WHERE id = ?",
                [$targetStage, $userLevel, $billId]
            );
        }

        // Notifikasi ke staf Tata Usaha
        $tuUsers = Database::fetchAll(
            "SELECT u.id FROM users u 
             JOIN user_roles ur ON u.id = ur.user_id 
             JOIN roles r ON ur.role_id = r.id 
             JOIN employees e ON u.id = e.user_id 
             WHERE r.name = 'tata_usaha' AND e.unit_id = ?",
            [$unitId]
        );
        foreach ($tuUsers as $tu) {
            Notification::send(
                (int)$tu['id'], 
                'Kesepakatan Cicilan Baru (FR 8001D)', 
                "Kesepakatan cicilan baru disetujui ({$agreementNumber}) untuk {$parentName}. Silakan kelola angsuran pada Menu Buku Cicilan.",
                'info',
                '/tagihan/cicilan'
            );
        }

        AuditLog::log('APPROVE_FR8001D_AGREEMENT', 'bill_installments', $installmentId, null, [
            'agreement_number' => $agreementNumber,
            'total_debt' => $totalDebt,
            'approved_by' => $userLevel
        ]);

        $this->setFlash('success', "Kesepakatan FR 8001D ({$agreementNumber}) berhasil disetujui dan diteruskan ke Buku Cicilan Tata Usaha!");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/tagihan/approval'));
    }

    public function markSelesaiKepsek(): void {
        $this->requireRole('kepala_sekolah');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = 'selesai_kepsek', 
                 pipeline_resolved_at = NOW(), 
                 pipeline_resolved_level = 'kepala_sekolah' 
             WHERE id = ? AND unit_id = ?",
            [$billId, $unitId]
        );

        AuditLog::log('MARK_RESOLVED_KEPSEK', 'bills', $billId, null);
        $this->setFlash('success', 'Penagihan telah ditandai SELESAI di tingkat Kepala Sekolah.');
        $this->redirect(Auth::baseUrl() . '/tagihan/approval');
    }

    public function eskalasiDeputi(): void {
        $this->requireRole('kepala_sekolah');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();
        $notes = trim($_POST['escalation_notes'] ?? 'Orang tua tidak juga datang memenuhi panggilan Kepala Sekolah. Dieskalasi ke Deputi Direktur Pendidikan Yayasan.');

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = 'escalated_deputi', 
                 notes = CONCAT(COALESCE(notes, ''), '\n[Eskalasi ke Yayasan/Deputi]: ', ?) 
             WHERE id = ? AND unit_id = ?",
            [$notes, $billId, $unitId]
        );

        // Notifikasi ke Deputi Direktur Pendidikan
        $deputiUsers = Database::fetchAll(
            "SELECT u.id FROM users u 
             JOIN user_roles ur ON u.id = ur.user_id 
             JOIN roles r ON ur.role_id = r.id 
             WHERE r.name = 'deputi_pendidikan'"
        );
        foreach ($deputiUsers as $dp) {
            Notification::send(
                (int)$dp['id'],
                'Eskalasi Penagihan dari Kepala Sekolah',
                'Kepala Sekolah telah mengeskalasi penagihan tertunggak ke Deputi Direktur Pendidikan untuk pemanggilan tingkat Yayasan.',
                'warning',
                '/tagihan/eskalasi-deputi'
            );
        }

        AuditLog::log('ESCALATE_TO_DEPUTI', 'bills', $billId, null, ['notes' => $notes]);
        $this->setFlash('success', 'Tagihan berhasil dieskalasikan ke Deputi Direktur Pendidikan Yayasan.');
        $this->redirect(Auth::baseUrl() . '/tagihan/approval');
    }

    // =========================================================================
    // 5. ALUR YAYASAN: DEPUTI DIREKTUR PENDIDIKAN (FR 8001C KOP YAYASAN & D)
    // =========================================================================
    public function deputiEskalasiList(): void {
        $this->requireRole('deputi_pendidikan');

        $bills = Database::fetchAll(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik, c.name as class_name, u.name as unit_name, u.phone as unit_phone,
                    u.bank_name, u.bank_account, u.bank_holder, ks.nama as kepsek_name 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN employees ks ON u.id = ks.unit_id AND (ks.position LIKE '%Kepala%' OR ks.level_posisi LIKE '%Kepala%')
             WHERE b.pipeline_stage IN ('escalated_deputi', 'deputi_summon_sent', 'selesai_deputi') 
             ORDER BY FIELD(b.pipeline_stage, 'escalated_deputi', 'deputi_summon_sent', 'selesai_deputi') ASC, b.due_date ASC"
        );

        $pendingCount = 0;
        $totalNominal = 0;
        foreach ($bills as $b) {
            if ($b['pipeline_stage'] === 'escalated_deputi' || $b['pipeline_stage'] === 'deputi_summon_sent') {
                $pendingCount++;
                $totalNominal += (float)$b['amount'];
            }
        }

        $this->view('tagihan/eskalasi_deputi', [
            'pageTitle'    => 'Eskalasi Penagihan Yayasan & Pemanggilan Deputi (FR 8001C Kop Yayasan)',
            'bills'        => $bills,
            'pendingCount' => $pendingCount,
            'totalNominal' => $totalNominal
        ]);
    }

    public function saveDeputiSchedule(): void {
        $this->requireRole('deputi_pendidikan');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $date = trim($_POST['schedule_date'] ?? '');
        $time = trim($_POST['schedule_time'] ?? '10:00');
        $place = trim($_POST['schedule_place'] ?? 'Ruang Rapat Direktorat Pendidikan Yayasan Al-Kahfi');

        if (!$billId || empty($date)) {
            $this->setFlash('danger', 'Harap tentukan tanggal schedule pemanggilan yayasan.');
            $this->redirect(Auth::baseUrl() . '/tagihan/eskalasi-deputi');
            return;
        }

        $fullDateTime = $date . ' ' . $time . ':00';
        $letterNo = 'PANG/YAYASAN/' . date('Ym') . '/' . sprintf('%04d', $billId);

        Database::execute(
            "UPDATE bills 
             SET deputi_schedule_date = ?, 
                 deputi_schedule_place = ?, 
                 deputi_letter_no = ?, 
                 deputi_summon_sent_at = NOW(), 
                 pipeline_stage = 'deputi_summon_sent' 
             WHERE id = ?",
            [$fullDateTime, $place, $letterNo, $billId]
        );

        AuditLog::log('SET_DEPUTI_SUMMON', 'bills', $billId, null, [
            'schedule' => $fullDateTime,
            'place' => $place,
            'letter_no' => $letterNo
        ]);

        $this->setFlash('success', 'Jadwal pemanggilan tingkat Yayasan (Kop Yayasan) berhasil ditetapkan.');
        $this->redirect(Auth::baseUrl() . '/tagihan/eskalasi-deputi');
    }

    public function printSuratFR8001CDeputi(string $id): void {
        $this->requireRole(['deputi_pendidikan', 'kepala_sekolah', 'tata_usaha']);
        $billId = (int)$id;

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    c.name as class_name, u.name as unit_name, u.phone as unit_phone, u.bank_name, 
                    u.bank_account, u.bank_holder, ks.nama as kepsek_name 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN employees ks ON u.id = ks.unit_id AND (ks.position LIKE '%Kepala%' OR ks.level_posisi LIKE '%Kepala%')
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }

        // KOP YAYASAN
        $kop = ExportHelper::renderKopYayasan();
        $letterNo = $bill['deputi_letter_no'] ?: ('PANG/YAYASAN/' . date('Ym') . '/' . sprintf('%04d', $billId));
        $bsiAcc = $bill['bank_account'] ?: '1033667414';
        $bsiHolder = $bill['bank_holder'] ?: $bill['unit_name'];
        $kepsekName = $bill['kepsek_name'] ?: 'Kepala Sekolah';

        $sppFmt = number_format((float)$bill['amount_spp'], 0, ',', '.');
        $nonSppFmt = number_format((float)$bill['amount_non_spp'], 0, ',', '.');
        $totalFmt = number_format((float)$bill['amount'], 0, ',', '.');

        $schedText = '';
        if (!empty($bill['deputi_schedule_date'])) {
            $ts = strtotime($bill['deputi_schedule_date']);
            $schedText = "
            <div style='margin: 15px 0; padding: 10px 14px; background: #ecfdf5; border-left: 4px solid #059669;'>
                <strong>Jadwal Pemanggilan Orang Tua di Kantor Yayasan:</strong><br>
                Hari / Tanggal : <strong>" . SecurityHelper::formatTanggalIndo(date('Y-m-d', $ts)) . "</strong><br>
                Pukul / Waktu &nbsp;: <strong>" . date('H:i', $ts) . " WIB</strong><br>
                Tempat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <strong>" . SecurityHelper::escape($bill['deputi_schedule_place'] ?: 'Ruang Rapat Direktorat Pendidikan Yayasan') . "</strong>
            </div>";
        }

        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Format Surat Pemanggilan Orangtua Yayasan - FR 8001C</title>
            <style>
                @page { margin: 15mm 20mm 20mm 20mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 11.5pt; line-height: 1.45; color: #111; }
                .doc-code { font-size: 10pt; font-weight: normal; margin-bottom: 8px; color: #444; }
                .meta-table { width: 100%; margin-top: 10px; margin-bottom: 12px; }
                .meta-table td { padding: 2px; vertical-align: top; }
                .data-table { width: 100%; border-collapse: collapse; margin: 12px 0; }
                .data-table th, .data-table td { border: 1px solid #222; padding: 6px 8px; font-size: 11pt; }
                .data-table th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .signatures { width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 11pt; }
            </style>
        </head>
        <body>
            <div class='doc-code'>7.3 Format Surat Pemanggilan Orangtua - <strong>FR 8001C (Kop Yayasan)</strong></div>
            {$kop}

            <table class='meta-table'>
                <tr>
                    <td width='12%'>No.</td><td width='2%'>:</td><td width='46%'>{$letterNo}</td>
                    <td width='40%' style='text-align: right;'>Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "</td>
                </tr>
                <tr>
                    <td>Lamp.</td><td>:</td><td>-</td>
                    <td></td>
                </tr>
                <tr>
                    <td>Perihal</td><td>:</td><td><strong>Pemanggilan Orang Tua / Wali - Tagihan Biaya Sekolah</strong></td>
                    <td></td>
                </tr>
            </table>

            <div style='margin-top: 10px; margin-bottom: 12px;'>
                Kepada Ykh. :<br>
                <strong>Orang Tua Dari " . SecurityHelper::escape($bill['student_name']) . "</strong><br>
                Unit Satuan: {$bill['unit_name']} &ndash; Kelas {$bill['class_name']}<br>
                Di &ndash; Tempat
            </div>

            <div style='font-style: italic; margin-bottom: 8px;'>Assalamu'alaikum warahmatullahi wabarakaatuh,</div>

            <div style='text-align: justify; margin-bottom: 8px;'>
                Segala puji hanyalah milik Allah, kita memujinya dengan nama-nama dan sifat-sifat Nya yang baik, Shalawat dan salam senantiasa tercurah atas junjungan dan panutan hidup kita, Nabi Muhammad Shallallahu'alaihi wa sallam.
            </div>

            <div style='text-align: justify; margin-bottom: 10px;'>
                Melalui surat ini Direktorat Pendidikan Yayasan memberitahukan kepada Bapak/Ibu wali murid, bahwa berkaitan dengan kelangsungan belajar mengajar di lingkungan Yayasan Islam Al Kahfi Batam, bersama ini kami sampaikan data pembiayaan yang belum terselesaikan pada rekening Bank BSI, No Rekening {$bsiAcc}, AN: {$bsiHolder} ({$bill['unit_name']}). Rincian kewajiban adalah:
            </div>

            <!-- Tabel 2 Kolom Tunggakan SPP & Non SPP (FR 8001C) -->
            <table class='data-table'>
                <thead>
                    <tr>
                        <th width='8%'>No</th>
                        <th width='32%'>Tunggakan SPP (Rp.)</th>
                        <th width='35%'>Tunggakan Non SPP (Rp)</th>
                        <th width='25%'>JUMLAH</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td align='center'>1</td>
                        <td align='right' style='font-weight: bold;'>Rp {$sppFmt}</td>
                        <td align='right' style='font-weight: bold;'>Rp {$nonSppFmt}</td>
                        <td align='right' style='font-weight: bold; color: #b91c1c;'>Rp {$totalFmt}</td>
                    </tr>
                </tbody>
            </table>
            <div style='font-size: 9.5pt; font-style: italic; color: #555; margin-bottom: 10px;'>
                *Data di ambil per tanggal " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . " / Abaikan surat ini jika sudah diselesaikan.
            </div>

            {$schedText}

            <div style='text-align: justify; margin-bottom: 10px;'>
                Mengingat pentingnya agenda ini demi kelancaran pendidikan ananda, kami mengharapkan kehadiran Bapak/Ibu tepat waktu di Kantor Yayasan.
            </div>

            <div style='text-align: justify; margin-bottom: 15px;'>
                Demikian yang dapat kami sampaikan, atas perhatian dan kehadirannya diucapkan <em>Jazaakumullaahu khairan</em>.<br>
                <em style='display: block; margin-top: 6px;'>Wassalamu'alaikum warahmatullahi wabarakaatuh</em>
            </div>

            <!-- Tanda Tangan 2 Pihak: Kepala Sekolah & Deputi Direktur Pendidikan -->
            <table class='signatures'>
                <tr>
                    <td class='sig-box'>
                        Mengetahui,<br>
                        <strong>Kepala Satuan Pendidikan</strong><br><br><br><br>
                        <strong><u>{$kepsekName}</u></strong>
                    </td>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "<br>
                        <strong>Deputi Direktur Pendidikan Yayasan</strong><br><br><br><br>
                        <strong><u>Ust. Syahidi Rahman, S.Pd.I</u></strong>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Surat_Panggilan_Yayasan_FR8001C_' . $bill['nis'], 'A4', 'portrait', true);
    }

    public function printSuratFR8001DDeputi(string $id): void {
        $this->requireRole(['deputi_pendidikan', 'tata_usaha']);
        $billId = (int)$id;

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik, c.name as class_name, u.name as unit_name 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }

        $kop = ExportHelper::renderKopYayasan();
        $parentName = $bill['parent_name'] ?: '..........................................';
        $parentKtp = $bill['nik'] ?: '..........................................';
        $parentAddress = $bill['alamat'] ?: '..........................................';
        $parentPhone = $bill['parent_phone'] ?: '..........................................';

        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Format Surat Kesepakatan Pembayaran Tunggakan Yayasan - FR 8001D</title>
            <style>
                @page { margin: 15mm 20mm 20mm 20mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 12pt; line-height: 1.5; color: #111; }
                .doc-code { font-size: 10pt; font-weight: normal; margin-bottom: 6px; color: #444; }
                .title-box { text-align: center; font-weight: bold; font-size: 12.5pt; text-decoration: underline; margin-bottom: 18px; }
                .form-table { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
                .form-table td { padding: 3px 2px; vertical-align: top; font-size: 11.5pt; }
                .options-list { margin-top: 10px; margin-bottom: 20px; line-height: 1.8; }
                .closing { margin-top: 15px; margin-bottom: 30px; text-align: justify; }
                .signatures { width: 100%; margin-top: 10px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 11.5pt; }
            </style>
        </head>
        <body>
            <div class='doc-code'>7.4 Format Surat Kesepakatan Pembayaran Tunggakan - <strong>FR 8001D (Kop Yayasan)</strong></div>
            {$kop}

            <div class='title-box'>SURAT PERNYATAAN PEMBAYARAN TUNGGAKAN BIAYA SEKOLAH</div>

            <div style='margin-bottom: 10px;'>Saya yang bertanda tangan di bawah ini:</div>

            <table class='form-table'>
                <tr>
                    <td width='22%'>Nama</td><td width='2%'>:</td>
                    <td><strong>{$parentName}</strong></td>
                </tr>
                <tr>
                    <td>No KTP</td><td>:</td>
                    <td>{$parentKtp}</td>
                </tr>
                <tr>
                    <td>Alamat</td><td>:</td>
                    <td>{$parentAddress}</td>
                </tr>
                <tr>
                    <td>No Tlp/ HP</td><td>:</td>
                    <td>{$parentPhone}</td>
                </tr>
            </table>

            <div style='margin-top: 10px; margin-bottom: 6px;'>Wali/Orang tua murid dari siswa</div>
            <table class='form-table'>
                <tr>
                    <td width='22%'>Nama</td><td width='2%'>:</td>
                    <td><strong>{$bill['student_name']}</strong> (NIS: {$bill['nis']})</td>
                </tr>
                <tr>
                    <td>Unit / Kelas</td><td>:</td>
                    <td>{$bill['unit_name']} &ndash; Kelas " . ($bill['class_name'] ?: 'Aktif') . "</td>
                </tr>
            </table>

            <div style='margin-top: 12px; text-align: justify;'>
                Dengan ini Insya Alloh akan menyelesaikan tunggakan sesuai dengan surat pemberitahuan dengan cara :
            </div>

            <div class='options-list'>
                <div><strong>A.</strong> Membayar lunas pada tanggal ..........................................</div>
                <div><strong>B.</strong> Dicicil selama ......bulan terhitung mulai tanggal ..........................................</div>
                <div><strong>C.</strong> Dicicil dengan minimal Rp................../bulan sampai dengan tanggal ..........................................</div>
            </div>

            <div class='closing'>
                Demikian surat pernyataan ini diajukan. Atas bantuan dan kerjasama yang diberikan, saya ucapkan <em>jazaakumullaahu khairan</em>.
            </div>

            <table class='signatures'>
                <tr>
                    <td class='sig-box'>&nbsp;</td>
                    <td class='sig-box'>
                        Batam, ..........................................<br><br>
                        Yang mengajukan<br>
                        <strong>Orangtua</strong><br><br><br><br><br>
                        ( .......................................... )
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Form_Kesepakatan_Yayasan_FR8001D_' . $bill['nis'], 'A4', 'portrait', true);
    }

    public function markSelesaiDeputi(): void {
        $this->requireRole('deputi_pendidikan');
        $billId = (int)($_POST['bill_id'] ?? 0);

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = 'selesai_deputi', 
                 pipeline_resolved_at = NOW(), 
                 pipeline_resolved_level = 'deputi_pendidikan' 
             WHERE id = ?",
            [$billId]
        );

        AuditLog::log('MARK_RESOLVED_DEPUTI', 'bills', $billId, null);
        $this->setFlash('success', 'Penagihan telah ditandai SELESAI di tingkat Deputi Direktur Pendidikan Yayasan.');
        $this->redirect(Auth::baseUrl() . '/tagihan/eskalasi-deputi');
    }

    // =========================================================================
    // 6. MENU BARU AKUN TU: BUKU & KARTU CICILAN SISWA (FR 8001D)
    // SIFAT: TIDAK BERUBAH WALAUPUN TAHUN AJARAN BERGANTI KECUALI LUNAS!
    // =========================================================================
    public function cicilanList(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan', 'keuangan_yayasan']);
        $unitId = Auth::unitId();

        // Kueri TIDAK difilter oleh academic_year_id!
        // Data tetap ada melintasi pergantian tahun ajaran sampai lunas.
        $installments = Database::fetchAll(
            "SELECT i.*, s.nama as student_name, s.nis, s.parent_email, c.name as class_name, 
                    u.name as unit_name, u_app.full_name as approver_name 
             FROM bill_installments i 
             JOIN students s ON i.student_id = s.id 
             JOIN units u ON i.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN users u_app ON i.approved_by_user_id = u_app.id 
             WHERE i.unit_id = ? 
             ORDER BY FIELD(i.status, 'Aktif', 'Lunas', 'Batal') ASC, i.created_at DESC",
            [$unitId]
        );

        // Ambil riwayat pembayaran angsuran untuk tiap cicilan
        $paymentsByInstallment = [];
        $instIds = array_column($installments, 'id');
        if (!empty($instIds)) {
            $inClause = implode(',', array_map('intval', $instIds));
            $allPayments = Database::fetchAll(
                "SELECT p.*, u.full_name as receiver_name 
                 FROM bill_installment_payments p 
                 JOIN users u ON p.received_by_user_id = u.id 
                 WHERE p.installment_id IN ({$inClause}) 
                 ORDER BY p.payment_date ASC, p.id ASC"
            );
            foreach ($allPayments as $p) {
                $paymentsByInstallment[$p['installment_id']][] = $p;
            }
        }

        $totalTunggakanAktif = 0;
        $totalTerbayar = 0;
        $totalSisa = 0;
        $activeCount = 0;
        $lunasCount = 0;

        foreach ($installments as $ins) {
            if ($ins['status'] === 'Aktif') {
                $activeCount++;
                $totalTunggakanAktif += (float)$ins['total_debt'];
                $totalTerbayar += (float)$ins['paid_total'];
                $totalSisa += (float)$ins['remaining_debt'];
            } else {
                $lunasCount++;
            }
        }

        $this->view('tagihan/cicilan', [
            'pageTitle'             => 'Buku & Kartu Cicilan Pembayaran Tunggakan (FR 8001D)',
            'installments'          => $installments,
            'paymentsByInstallment' => $paymentsByInstallment,
            'totalTunggakanAktif'   => $totalTunggakanAktif,
            'totalTerbayar'         => $totalTerbayar,
            'totalSisa'             => $totalSisa,
            'activeCount'           => $activeCount,
            'lunasCount'            => $lunasCount
        ]);
    }

    public function saveCicilanPayment(): void {
        $this->requireRole('tata_usaha');
        $userId = Auth::id();
        $unitId = Auth::unitId();

        $installmentId = (int)($_POST['installment_id'] ?? 0);
        $amount = max(0, (float)str_replace(['Rp', '.', ' '], '', $_POST['amount'] ?? '0'));
        $paymentDate = trim($_POST['payment_date'] ?? date('Y-m-d'));
        $paymentMethod = trim($_POST['payment_method'] ?? 'Cash');
        $notes = trim($_POST['notes'] ?? '');

        $installment = Database::fetch("SELECT * FROM bill_installments WHERE id = ? AND unit_id = ?", [$installmentId, $unitId]);
        if (!$installment) {
            $this->setFlash('danger', 'Data kesepakatan cicilan tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
            return;
        }

        if ($amount <= 0) {
            $this->setFlash('danger', 'Nominal pembayaran cicilan harus lebih dari 0.');
            $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
            return;
        }

        if ($amount > (float)$installment['remaining_debt']) {
            $this->setFlash('warning', 'Nominal pembayaran melebihi sisa tunggakan (' . SecurityHelper::formatRupiah((float)$installment['remaining_debt']) . ').');
            $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
            return;
        }

        $receiptNumber = 'KW-CICIL/' . date('Ym') . '/' . sprintf('%04d', rand(100, 9999));

        Database::execute(
            "INSERT INTO bill_installment_payments 
                (installment_id, receipt_number, payment_date, amount, payment_method, notes, received_by_user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$installmentId, $receiptNumber, $paymentDate, $amount, $paymentMethod, $notes, $userId]
        );

        $newPaidTotal = (float)$installment['paid_total'] + $amount;
        $newRemaining = (float)$installment['total_debt'] - $newPaidTotal;
        $newStatus = ($newRemaining <= 0) ? 'Lunas' : 'Aktif';

        Database::execute(
            "UPDATE bill_installments 
             SET paid_total = ?, 
                 remaining_debt = ?, 
                 status = ? 
             WHERE id = ?",
            [$newPaidTotal, max(0, $newRemaining), $newStatus, $installmentId]
        );

        // Jika lunas dan memiliki referensi bill_id, update status bills
        if ($newStatus === 'Lunas' && !empty($installment['bill_id'])) {
            Database::execute(
                "UPDATE bills 
                 SET status = 'Lunas', 
                     paid_date = ?, 
                     paid_amount = amount 
                 WHERE id = ?",
                [$paymentDate, $installment['bill_id']]
            );
        }

        AuditLog::log('RECEIVE_INSTALLMENT_PAYMENT', 'bill_installment_payments', Database::lastInsertId(), null, [
            'installment_id' => $installmentId,
            'receipt'        => $receiptNumber,
            'amount'         => $amount,
            'status'         => $newStatus
        ]);

        $this->setFlash('success', "Pembayaran angsuran sebesar " . SecurityHelper::formatRupiah($amount) . " berhasil dicatat ({$receiptNumber})." . ($newStatus === 'Lunas' ? ' KESEPAKATAN TELAH LUNAS!' : ''));
        $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
    }

    public function printKartuCicilanPdf(string $id): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan', 'orang_tua']);
        $installmentId = (int)$id;

        $installment = Database::fetch(
            "SELECT i.*, s.nama as student_name, s.nis, s.parent_email, c.name as class_name, 
                    u.name as unit_name, u.kop_title, u.kop_subtitle, u.address, u.phone, u.email as unit_email,
                    u_app.full_name as approver_name 
             FROM bill_installments i 
             JOIN students s ON i.student_id = s.id 
             JOIN units u ON i.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN users u_app ON i.approved_by_user_id = u_app.id 
             WHERE i.id = ?",
            [$installmentId]
        );

        if (!$installment) {
            die('Data cicilan tidak ditemukan.');
        }

        // Ambil histori pembayaran angsuran
        $payments = Database::fetchAll(
            "SELECT p.*, u.full_name as receiver_name 
             FROM bill_installment_payments p 
             JOIN users u ON p.received_by_user_id = u.id 
             WHERE p.installment_id = ? 
             ORDER BY p.payment_date ASC, p.id ASC",
            [$installmentId]
        );

        $kop = ($installment['approved_by_level'] === 'deputi_pendidikan')
            ? ExportHelper::renderKopYayasan()
            : ExportHelper::renderKop($installment);

        // Skema Text
        $skemaDesc = '';
        if ($installment['scheme_type'] === 'lunas_tanggal') {
            $skemaDesc = 'Membayar lunas pada tanggal: <strong>' . SecurityHelper::formatTanggalIndo($installment['scheme_lunas_date']) . '</strong>';
        } elseif ($installment['scheme_type'] === 'cicil_bulan') {
            $skemaDesc = 'Dicicil selama <strong>' . $installment['scheme_months'] . ' bulan</strong> mulai tanggal <strong>' . SecurityHelper::formatTanggalIndo($installment['scheme_start_date']) . '</strong>';
        } elseif ($installment['scheme_type'] === 'cicil_nominal') {
            $skemaDesc = 'Dicicil minimal <strong>' . SecurityHelper::formatRupiah((float)$installment['scheme_min_monthly']) . '/bulan</strong> s.d tanggal <strong>' . SecurityHelper::formatTanggalIndo($installment['scheme_end_date']) . '</strong>';
        }

        $totalTunggakan = number_format((float)$installment['total_debt'], 0, ',', '.');
        $totalTerbayar = number_format((float)$installment['paid_total'], 0, ',', '.');
        $sisaHutang = number_format((float)$installment['remaining_debt'], 0, ',', '.');

        $rowsHtml = '';
        if (empty($payments)) {
            $rowsHtml = "<tr><td colspan='6' align='center' style='padding: 18px; color: #64748b; font-style: italic;'>Belum ada rekaman angsuran pembayaran yang disetorkan.</td></tr>";
        } else {
            $runningBalance = (float)$installment['total_debt'];
            foreach ($payments as $idx => $p) {
                $runningBalance -= (float)$p['amount'];
                $rowsHtml .= "
                <tr>
                    <td align='center'>" . ($idx + 1) . "</td>
                    <td align='center'>" . SecurityHelper::formatTanggalIndo($p['payment_date']) . "</td>
                    <td align='center'><strong>" . SecurityHelper::escape($p['receipt_number']) . "</strong></td>
                    <td align='right' style='font-weight: bold; color: #047857;'>Rp " . number_format((float)$p['amount'], 0, ',', '.') . "</td>
                    <td align='right' style='font-weight: bold;'>Rp " . number_format(max(0, $runningBalance), 0, ',', '.') . "</td>
                    <td align='center' style='font-size: 10pt;'>" . SecurityHelper::escape($p['receiver_name']) . "</td>
                </tr>";
            }
        }

        $statusBadge = ($installment['status'] === 'Lunas')
            ? "<span style='padding: 4px 12px; background: #dcfce7; color: #15803d; border: 1px solid #86efac; border-radius: 4px; font-weight: bold;'>LUNAS</span>"
            : "<span style='padding: 4px 12px; background: #fee2e2; color: #b91c1c; border: 1px solid #fca5a5; border-radius: 4px; font-weight: bold;'>SEDANG DICICIL (AKTIF)</span>";

        $html = "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Buku & Kartu Cicilan Pembayaran Tunggakan</title>
            <style>
                @page { margin: 15mm 18mm 18mm 18mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #111; }
                .card-title { text-align: center; font-weight: bold; font-size: 13pt; text-transform: uppercase; margin-bottom: 4px; }
                .card-subtitle { text-align: center; font-size: 10pt; color: #475569; margin-bottom: 16px; }
                .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
                .meta-table td { padding: 3px 4px; vertical-align: top; font-size: 10.5pt; }
                .summary-box { width: 100%; margin: 12px 0; border: 1px solid #cbd5e1; border-radius: 6px; padding: 10px; background: #f8fafc; }
                .ledger-table { width: 100%; border-collapse: collapse; margin-top: 14px; }
                .ledger-table th, .ledger-table td { border: 1px solid #334155; padding: 6px 8px; font-size: 10.5pt; }
                .ledger-table th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .signatures { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 10.5pt; }
            </style>
        </head>
        <body>
            {$kop}

            <div class='card-title'>KARTU BUKU KONTROL CICILAN TUNGGAKAN BIAYA SEKOLAH</div>
            <div class='card-subtitle'>Dokumen Perjanjian Kesepakatan: <strong>{$installment['agreement_number']}</strong></div>

            <table class='meta-table'>
                <tr>
                    <td width='20%'>Nama Santri</td><td width='2%'>:</td>
                    <td width='38%'><strong>" . SecurityHelper::escape($installment['student_name']) . "</strong> (NIS: {$installment['nis']})</td>
                    <td width='18%'>Status Pembayaran</td><td width='2%'>:</td>
                    <td width='20%'>{$statusBadge}</td>
                </tr>
                <tr>
                    <td>Kelas / Unit</td><td>:</td>
                    <td>" . SecurityHelper::escape($installment['class_name'] ?? 'Aktif') . " &ndash; {$installment['unit_name']}</td>
                    <td>Tgl Kesepakatan</td><td>:</td>
                    <td>" . SecurityHelper::formatTanggalIndo($installment['agreement_date']) . "</td>
                </tr>
                <tr>
                    <td>Orang Tua / Wali</td><td>:</td>
                    <td><strong>" . SecurityHelper::escape($installment['parent_name']) . "</strong> (HP: " . SecurityHelper::escape($installment['parent_phone'] ?: '-') . ")</td>
                    <td>Disetujui Oleh</td><td>:</td>
                    <td>" . ($installment['approved_by_level'] === 'deputi_pendidikan' ? 'Deputi Pendidikan' : 'Kepala Sekolah') . "</td>
                </tr>
                <tr>
                    <td>Skema Disepakati</td><td>:</td>
                    <td colspan='4'>{$skemaDesc}</td>
                </tr>
            </table>

            <!-- Ringkasan Saldo Hutang -->
            <table style='width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 12px;'>
                <tr>
                    <td style='width: 33.3%; padding: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; text-align: center;'>
                        <span style='font-size: 9pt; color: #64748b; text-transform: uppercase;'>Total Tunggakan Awal</span><br>
                        <strong style='font-size: 13pt; color: #0f172a;'>Rp {$totalTunggakan}</strong>
                    </td>
                    <td style='width: 33.3%; padding: 8px; background: #ecfdf5; border: 1px solid #a7f3d0; text-align: center;'>
                        <span style='font-size: 9pt; color: #047857; text-transform: uppercase;'>Total Telah Dicicil</span><br>
                        <strong style='font-size: 13pt; color: #047857;'>Rp {$totalTerbayar}</strong>
                    </td>
                    <td style='width: 33.3%; padding: 8px; background: #fef2f2; border: 1px solid #fecaca; text-align: center;'>
                        <span style='font-size: 9pt; color: #b91c1c; text-transform: uppercase;'>Sisa Saldo Tunggakan</span><br>
                        <strong style='font-size: 13pt; color: #b91c1c;'>Rp {$sisaHutang}</strong>
                    </td>
                </tr>
            </table>

            <!-- Tabel Histori Angsuran Cicilan -->
            <table class='ledger-table'>
                <thead>
                    <tr>
                        <th width='6%'>No</th>
                        <th width='20%'>Tanggal Bayar</th>
                        <th width='24%'>No. Kuitansi Bukti</th>
                        <th width='20%'>Jumlah Bayar</th>
                        <th width='20%'>Sisa Tunggakan</th>
                        <th width='10%'>Paraf TU</th>
                    </tr>
                </thead>
                <tbody>
                    {$rowsHtml}
                </tbody>
            </table>

            <table class='signatures'>
                <tr>
                    <td class='sig-box'>
                        Mengetahui & Menyetujui,<br>
                        <strong>Orang Tua / Wali Santri</strong><br><br><br><br>
                        ( <strong>" . SecurityHelper::escape($installment['parent_name']) . "</strong> )
                    </td>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "<br>
                        Bagian Keuangan / Tata Usaha,<br>
                        <strong>{$installment['unit_name']}</strong><br><br><br><br>
                        ( .................................................. )
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Buku_Kartu_Cicilan_' . $installment['nis'] . '_' . date('Ymd'), 'A4', 'portrait', true);
    }

    public function sendKartuCicilanEmail(): void {
        $this->requireRole('tata_usaha');
        $installmentId = (int)($_POST['installment_id'] ?? 0);
        $unitId = Auth::unitId();

        $installment = Database::fetch(
            "SELECT i.*, s.nama as student_name, s.nis, s.parent_email, u.name as unit_name 
             FROM bill_installments i 
             JOIN students s ON i.student_id = s.id 
             JOIN units u ON i.unit_id = u.id 
             WHERE i.id = ? AND i.unit_id = ?",
            [$installmentId, $unitId]
        );

        if (!$installment || empty($installment['parent_email'])) {
            $this->setFlash('warning', 'Data cicilan atau email orang tua tidak tersedia.');
            $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
            return;
        }

        $smtp = $this->getSmtpConfig($unitId);
        if ($smtp) {
            try {
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $smtp['host'];
                $mail->SMTPAuth   = true;
                $mail->Username   = $smtp['username'];
                $mail->Password   = $smtp['password'];
                $mail->SMTPSecure = $smtp['encryption'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = (int)$smtp['port'];

                $mail->setFrom($smtp['from_email'], $smtp['from_name']);
                $mail->addAddress($installment['parent_email'], $installment['student_name']);
                $mail->isHTML(true);
                $mail->Subject = "[Kartu Cicilan Pembayaran SPP] " . $installment['student_name'] . " - " . $installment['unit_name'];
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                        <h3 style='color: #0f766e;'>Buku & Kartu Kontrol Cicilan Tunggakan</h3>
                        <p>Yth. Orang Tua dari ananda <strong>{$installment['student_name']}</strong>,</p>
                        <p>Berikut ringkasan status cicilan pembayaran sekolah berdasarkan kesepakatan <strong>{$installment['agreement_number']}</strong>:</p>
                        <ul>
                            <li>Total Tunggakan Awal: <strong>" . SecurityHelper::formatRupiah((float)$installment['total_debt']) . "</strong></li>
                            <li>Total Telah Dibayar: <strong style='color: #059669;'>" . SecurityHelper::formatRupiah((float)$installment['paid_total']) . "</strong></li>
                            <li>Sisa Saldo Tunggakan: <strong style='color: #b91c1c;'>" . SecurityHelper::formatRupiah((float)$installment['remaining_debt']) . "</strong></li>
                            <li>Status: <strong>{$installment['status']}</strong></li>
                        </ul>
                        <p>Silakan mengunduh Buku/Kartu Cicilan resmi melalui tautan portal orang tua atau loket Tata Usaha sekolah.</p>
                        <p><em>Jazakumullah Khairan Katsiran.</em></p>
                    </div>";
                $mail->send();
                $this->setFlash('success', 'Informasi Kartu Cicilan berhasil dikirim ke email orang tua (' . $installment['parent_email'] . ').');
            } catch (\Exception $e) {
                $this->setFlash('danger', 'Gagal mengirim email: ' . $e->getMessage());
            }
        } else {
            $this->setFlash('info', 'Konfigurasi SMTP belum aktif di unit ini.');
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
    }

    // =========================================================================
    // 7. SURAT TAGIHAN RESMI LAMA (BACKWARD COMPATIBILITY)
    // =========================================================================
    public function printSuratTagihan(string $id): void {
        $this->printSuratFR8001B($id);
    }
}

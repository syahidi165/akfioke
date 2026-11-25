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
    // HELPER UMUM PENAGIHAN (PARSING RUPIAH, PENANDATANGAN UNIT, TERBILANG)
    // =========================================================================
    private function parseRupiah(mixed $val): float {
        if (is_int($val) || is_float($val)) {
            return max(0.0, (float)$val);
        }
        $str = trim((string)$val);
        if ($str === '') {
            return 0.0;
        }
        $str = str_ireplace('Rp', '', $str);
        $str = trim($str);
        // Hilangkan desimal ,00 atau .00 di akhir string
        if (preg_match('/[,\.]\d{2}$/', $str)) {
            $str = preg_replace('/[,\.]\d{2}$/', '', $str);
        }
        $digits = preg_replace('/[^0-9]/', '', $str);
        return $digits !== '' ? (float)$digits : 0.0;
    }

    /**
     * Ambil identitas pejabat penandatangan resmi langsung dari database (berdasar unit_id, bukan session switch user).
     */
    private function resolveUnitSigner(int $unitId, string $roleName): array {
        if ($roleName === 'tata_usaha') {
            $emp = Database::fetch(
                "SELECT e.nama, e.nipy, e.contact_phone as phone
                 FROM employees e
                 JOIN user_roles ur ON e.user_id = ur.user_id
                 JOIN roles r ON ur.role_id = r.id
                 WHERE e.is_deleted = 0 AND e.status = 'Aktif'
                   AND r.name = 'tata_usaha'
                   AND (e.unit_id = ? OR ur.unit_id = ?)
                 ORDER BY CASE WHEN e.unit_id = ? THEN 0 ELSE 1 END, ur.is_primary DESC, e.id ASC
                 LIMIT 1",
                [$unitId, $unitId, $unitId]
            );
            if (!$emp) {
                $emp = Database::fetch(
                    "SELECT nama, nipy, contact_phone as phone FROM employees
                     WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif'
                       AND (level_posisi LIKE '%Tata Usaha%' OR position LIKE '%Tata Usaha%')
                     ORDER BY id ASC LIMIT 1",
                    [$unitId]
                );
            }
            return [
                'nama'  => $emp['nama'] ?? 'Staff Tata Usaha',
                'nipy'  => $emp['nipy'] ?? '-',
                'phone' => $emp['phone'] ?? ''
            ];
        }

        if ($roleName === 'kepala_sekolah') {
            $emp = Database::fetch(
                "SELECT e.nama, e.nipy, e.contact_phone as phone
                 FROM employees e
                 JOIN user_roles ur ON e.user_id = ur.user_id
                 JOIN roles r ON ur.role_id = r.id
                 WHERE e.is_deleted = 0 AND e.status = 'Aktif'
                   AND r.name = 'kepala_sekolah'
                   AND (e.unit_id = ? OR ur.unit_id = ?)
                 ORDER BY CASE WHEN e.unit_id = ? THEN 0 ELSE 1 END, ur.is_primary DESC, e.id ASC
                 LIMIT 1",
                [$unitId, $unitId, $unitId]
            );
            if (!$emp) {
                $emp = Database::fetch(
                    "SELECT nama, nipy, contact_phone as phone FROM employees
                     WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif'
                       AND (position LIKE '%Kepala Sekolah%' OR level_posisi LIKE '%Kepala Sekolah%' OR level_posisi LIKE '%Kepsek%' OR level_posisi LIKE '%Kanit%')
                     ORDER BY id ASC LIMIT 1",
                    [$unitId]
                );
            }
            return [
                'nama'  => $emp['nama'] ?? 'Kepala Sekolah',
                'nipy'  => $emp['nipy'] ?? '-',
                'phone' => $emp['phone'] ?? ''
            ];
        }

        if ($roleName === 'deputi_pendidikan') {
            try {
                $dep = Database::fetch(
                    "SELECT COALESCE(e.nama, u.full_name) as nama, COALESCE(e.nipy, '-') as nipy, COALESCE(e.contact_phone, u.phone, '') as phone
                     FROM deputi_units du
                     JOIN users u ON du.user_id = u.id AND u.is_active = 1
                     LEFT JOIN employees e ON e.user_id = u.id AND e.is_deleted = 0
                     WHERE du.unit_id = ?
                     ORDER BY du.id ASC LIMIT 1",
                    [$unitId]
                );
                if ($dep && !empty($dep['nama'])) {
                    return $dep;
                }
            } catch (\Throwable $e) {
                // Abaikan jika tabel deputi_units belum dibuat
            }
            $depFallback = Database::fetch(
                "SELECT COALESCE(e.nama, u.full_name) as nama, COALESCE(e.nipy, '-') as nipy, COALESCE(e.contact_phone, u.phone, '') as phone
                 FROM users u
                 JOIN user_roles ur ON u.id = ur.user_id
                 JOIN roles r ON ur.role_id = r.id
                 LEFT JOIN employees e ON e.user_id = u.id AND e.is_deleted = 0
                 WHERE r.name = 'deputi_pendidikan' AND u.is_active = 1
                 ORDER BY u.id ASC LIMIT 1"
            );
            return [
                'nama'  => $depFallback['nama'] ?? 'Deputi Direktur Pendidikan',
                'nipy'  => $depFallback['nipy'] ?? '-',
                'phone' => $depFallback['phone'] ?? ''
            ];
        }

        return ['nama' => '-', 'nipy' => '-', 'phone' => ''];
    }

    private function terbilang(float $angka): string {
        $angka = (int)round(abs($angka));
        if ($angka === 0) {
            return 'nol rupiah';
        }
        $words = ['', 'satu', 'dua', 'tiga', 'empat', 'lima', 'enam', 'tujuh', 'delapan', 'sembilan', 'sepuluh', 'sebelas'];
        $conv = function (int $n) use (&$conv, $words): string {
            if ($n < 12) return $words[$n];
            if ($n < 20) return $conv($n - 10) . ' belas';
            if ($n < 100) return $conv((int)floor($n / 10)) . ' puluh' . ($n % 10 ? ' ' . $conv($n % 10) : '');
            if ($n < 200) return 'seratus' . ($n - 100 ? ' ' . $conv($n - 100) : '');
            if ($n < 1000) return $conv((int)floor($n / 100)) . ' ratus' . ($n % 100 ? ' ' . $conv($n % 100) : '');
            if ($n < 2000) return 'seribu' . ($n - 1000 ? ' ' . $conv($n - 1000) : '');
            if ($n < 1000000) return $conv((int)floor($n / 1000)) . ' ribu' . ($n % 1000 ? ' ' . $conv($n % 1000) : '');
            if ($n < 1000000000) return $conv((int)floor($n / 1000000)) . ' juta' . ($n % 1000000 ? ' ' . $conv($n % 1000000) : '');
            return $conv((int)floor($n / 1000000000)) . ' miliar' . ($n % 1000000000 ? ' ' . $conv($n % 1000000000) : '');
        };
        return trim(preg_replace('/\s+/', ' ', $conv($angka))) . ' rupiah';
    }

    // =========================================================================
    // 1. MANAGE BILLS (INPUT TAGIHAN TU DENGAN MULTI-KOLOM FR 8001A)
    // =========================================================================
    public function manage(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan', 'keuangan_yayasan']);
        self::ensureSignedColumns();
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $bills = Database::fetchAll(
            "SELECT b.*, s.nama as student_name, s.nis, s.gender as student_gender, s.parent_email, 
                    COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, 
                    s.parent_phone, s.address as alamat,
                    COALESCE(
                        (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                         WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                         ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1),
                        'Non-Kelas'
                    ) as class_name
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             WHERE b.unit_id = ? AND b.academic_year_id = ? 
             ORDER BY class_name ASC, s.nama ASC, b.id DESC",
            [$unitId, $taId]
        );

        $students = Database::fetchAll(
            "SELECT s.*, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, 
                    s.address as alamat,
                    COALESCE(
                        (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                         WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = s.unit_id 
                         ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1),
                        'Tanpa Rombel'
                    ) as class_name
             FROM students s 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             ORDER BY class_name ASC, s.nama ASC", 
            [$unitId]
        );

        $classes = Database::fetchAll(
            "SELECT id, name, level FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC",
            [$unitId, $taId]
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

        $importPreview = $_SESSION['bill_import_preview'] ?? null;

        $this->view('tagihan/manage', [
            'pageTitle'       => 'Input Tagihan & Rekap SPP Siswa (FR 8001A)',
            'bills'           => $bills,
            'students'        => $students,
            'classes'         => $classes,
            'totalStudents'   => $totalStudents,
            'tunggakanCount'  => $tunggakanCount,
            'rasioTunggakan'  => $rasioTunggakan,
            'unit'            => $unit,
            'importPreview'   => $importPreview
        ]);
    }

    public function saveBill(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $title = trim($_POST['title'] ?? 'Tagihan Siswa & SPP');
        if ($title === '') {
            $title = 'Tagihan Siswa & SPP';
        }
        $billMonth = max(1, min(12, (int)($_POST['bill_month'] ?? date('n'))));
        $billYear = max(2020, (int)($_POST['bill_year'] ?? date('Y')));
        $dueDate = trim((string)($_POST['due_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $dueDate = date('Y-m-10');
        }
        $status = ($_POST['status'] ?? 'Tertunggak') === 'Lunas' ? 'Lunas' : 'Tertunggak';
        $notes = trim($_POST['notes'] ?? '');

        if ($studentId > 0) {
            $stRow = Database::fetch("SELECT id, unit_id FROM students WHERE id = ?", [$studentId]);
            if ($stRow && ($unitId <= 0 || (int)$stRow['unit_id'] === $unitId)) {
                $unitId = (int)$stRow['unit_id'];
            } else {
                $this->setFlash('danger', 'Data santri tidak valid atau bukan berasal dari unit sekolah Anda.');
                $this->redirect(Auth::baseUrl() . '/tagihan/manage');
                return;
            }
        }

        // 7 Kolom Rincian Biaya Bawaan (FR 8001A)
        $bukuPaket       = $this->parseRupiah($_POST['buku_paket'] ?? '0');
        $kegiatanSiswa   = $this->parseRupiah($_POST['kegiatan_siswa'] ?? '0');
        $komite          = $this->parseRupiah($_POST['komite'] ?? '0');
        $peningkatanMutu = $this->parseRupiah($_POST['peningkatan_mutu'] ?? '0');
        $spp             = $this->parseRupiah($_POST['spp'] ?? '0');
        $uangUjian       = $this->parseRupiah($_POST['uang_ujian'] ?? '0');
        $tunggakanLalu   = $this->parseRupiah($_POST['tunggakan_lalu'] ?? '0');

        // Custom items tambahan jika ada
        $customNames = $_POST['custom_item_name'] ?? [];
        $customAmounts = $_POST['custom_item_amount'] ?? [];
        $customItems = [];
        $customTotal = 0;

        if (is_array($customNames)) {
            foreach ($customNames as $idx => $cName) {
                $cName = trim((string)$cName);
                $cVal = $this->parseRupiah($customAmounts[$idx] ?? '0');
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

        if ($studentId > 0 && $totalAmount > 0 && $unitId > 0) {
            try {
                $paidDate = ($status === 'Lunas') ? date('Y-m-d') : null;
                $paidAmount = ($status === 'Lunas') ? $totalAmount : 0.0;
                $pipelineStage = ($status === 'Lunas') ? 'selesai_tu' : 'tu_draft';

                Database::execute(
                    "INSERT INTO bills 
                        (unit_id, academic_year_id, student_id, bill_month, bill_year, title, amount, items_detail, amount_spp, amount_non_spp, due_date, status, pipeline_stage, paid_date, paid_amount, notes, created_by_user_id) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                    [
                        $unitId, $taId, $studentId, $billMonth, $billYear, $title, $totalAmount, 
                        json_encode($itemsDetail), $amountSpp, $amountNonSpp, $dueDate, $status, 
                        $pipelineStage, $paidDate, $paidAmount, $notes, $userId
                    ]
                );

                AuditLog::log('CREATE_BILL_MULTI', 'bills', Database::lastInsertId(), null, [
                    'amount' => $totalAmount, 
                    'student_id' => $studentId,
                    'items_detail' => $itemsDetail
                ]);
                $this->setFlash('success', 'Data tagihan (FR 8001A) berhasil disimpan dengan total ' . SecurityHelper::formatRupiah($totalAmount) . '.');
            } catch (\Throwable $e) {
                error_log('Gagal menyimpan tagihan FR8001A: ' . $e->getMessage());
                $this->setFlash('danger', 'Terjadi kesalahan saat menyimpan tagihan: ' . htmlspecialchars($e->getMessage()));
            }
        } else {
            $this->setFlash('danger', 'Harap pilih santri yang valid dan isi minimal satu rincian uraian tagihan bernominal (> 0).');
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
    // 2. TEMPLATE EXCEL, VERIFIKASI & IMPORT AKUMULATIF BULANAN (FR 8001A)
    // =========================================================================
    public function downloadTemplate(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $unit = Database::fetch("SELECT name, code FROM units WHERE id = ?", [$unitId]);
        $unitName = $unit['name'] ?? 'Unit';

        $students = Database::fetchAll(
            "SELECT s.nis, s.nama, s.gender, 
                    COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, 
                    s.parent_phone,
                    COALESCE(
                        (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                         WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = s.unit_id 
                         ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1),
                        'Non-Kelas'
                    ) as class_name
             FROM students s 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             ORDER BY class_name ASC, s.nama ASC",
            [$unitId]
        );

        $headers = [
            'No Urut', 'Induk', 'Nama Siswa', 'Kelas', 'L/P', 'Nama Orang Tua/Wali', 'Nomor HP Orang Tua/Wali',
            'Buku Paket (Rp)', 'Kegiatan Siswa (Rp)', 'Komite (Rp)', 'Peningkatan Mutu (Rp)',
            'SPP (Rp)', 'Uang Ujian (Rp)', 'Tunggakan Tahun Lalu (Rp)', 'JUMLAH (Rp)'
        ];

        $rows = [];
        foreach ($students as $idx => $st) {
            $g = strtoupper(trim((string)($st['gender'] ?? 'L')));
            if (!in_array($g, ['L', 'P'], true)) {
                $g = 'L';
            }
            $rows[] = [
                $idx + 1,
                (string)$st['nis'],
                $st['nama'],
                $st['class_name'] ?? 'Non-Kelas',
                $g,
                $st['parent_name'] ?? 'Wali Santri',
                $st['parent_phone'] ?? '',
                0, // Buku Paket
                0, // Kegiatan Siswa
                0, // Komite
                0, // Peningkatan Mutu
                0, // SPP bulan berjalan
                0, // Uang Ujian
                0, // Tunggakan Tahun Lalu
                0  // Total Jumlah bulan berjalan
            ];
        }

        $filename = 'Format_FR8001A_Tagihan_' . preg_replace('/[^a-zA-Z0-9]/', '_', $unitName) . '_' . date('Y_m');
        ExportHelper::xlsx($headers, $rows, $filename, "Format Tagihan Siswa (FR 8001A) - {$unitName}");
    }

    /**
     * Parsing file laporan Braja (.xlsx/.xls/.csv) atau format FR 8001A (Model Timpa / Sinkronisasi Saldo).
     * - Mengabaikan kop, judul, baris kosong, dan baris TOTAL.
     * - Memetakan kolom biaya dinamis (kolom tambahan seperti GEDUNG / SERAGAM masuk ke custom_items).
     * - Menjaga isolasi unit secara ketat (baris siswa milik unit lain dilewati tanpa dicampur).
     */
    private function parseImportExcelRows(string $filePath, int $unitId, int $taId, string $originalFilename = ''): array {
        $ext = strtolower(pathinfo($originalFilename ?: $filePath, PATHINFO_EXTENSION));
        if ($ext === 'csv') {
            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Csv();
            $sample = (string)@file_get_contents($filePath, false, null, 0, 2048);
            $reader->setDelimiter(substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',');
            $spreadsheet = $reader->load($filePath);
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($filePath);
        }
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        $students = Database::fetchAll(
            "SELECT s.id, s.nis, s.nama, s.gender,
                    COALESCE(
                        (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                         WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = s.unit_id 
                         ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1),
                        'Non-Kelas'
                    ) as class_name
             FROM students s
             WHERE s.unit_id = ? AND s.status = 'Aktif'",
            [$unitId]
        );

        $studentByNis = [];
        $studentByNisTrimmed = [];
        $studentByName = [];
        foreach ($students as $st) {
            $cleanNis = trim((string)$st['nis']);
            if ($cleanNis !== '') {
                $studentByNis[$cleanNis] = $st;
                $trimNis = ltrim($cleanNis, '0');
                if ($trimNis !== '') {
                    $studentByNisTrimmed[$trimNis] = $st;
                }
            }
            $cleanName = strtolower(preg_replace('/\s+/', ' ', trim((string)$st['nama'])));
            if ($cleanName !== '') {
                $studentByName[$cleanName] = $st;
            }
        }

        // Peta siswa unit lain agar file lintas jenjang (misal KB+TK di 1 ekspor Braja) tetap terpisah ketat per unit
        $otherStudents = Database::fetchAll(
            "SELECT s.nis, s.nama, u.name as unit_name
             FROM students s
             JOIN units u ON s.unit_id = u.id
             WHERE s.unit_id != ? AND s.status = 'Aktif'",
            [$unitId]
        );
        $otherByNis = [];
        $otherByNisTrimmed = [];
        $otherByName = [];
        foreach ($otherStudents as $os) {
            $oNis = trim((string)$os['nis']);
            if ($oNis !== '') {
                $otherByNis[$oNis] = $os['unit_name'];
                $oTrim = ltrim($oNis, '0');
                if ($oTrim !== '') {
                    $otherByNisTrimmed[$oTrim] = $os['unit_name'];
                }
            }
            $oName = strtolower(preg_replace('/\s+/', ' ', trim((string)$os['nama'])));
            if ($oName !== '') {
                $otherByName[$oName] = $os['unit_name'];
            }
        }

        // Ambil tunggakan aktif & buku cicilan aktif per student_id untuk kalkulasi Timpa & Skenario A
        $openBills = Database::fetchAll(
            "SELECT id, student_id, amount FROM bills WHERE unit_id = ? AND academic_year_id = ? AND status = 'Tertunggak' ORDER BY id DESC",
            [$unitId, $taId]
        );
        $openBillMap = [];
        foreach ($openBills as $ob) {
            $sid = (int)$ob['student_id'];
            if (!isset($openBillMap[$sid])) {
                $openBillMap[$sid] = (float)$ob['amount'];
            }
        }

        $activeInsts = Database::fetchAll(
            "SELECT id, student_id, agreement_number, total_debt, paid_total, remaining_debt FROM bill_installments WHERE unit_id = ? AND status = 'Aktif' ORDER BY id DESC",
            [$unitId]
        );
        $activeInstMap = [];
        foreach ($activeInsts as $ai) {
            $sid = (int)$ai['student_id'];
            if (!isset($activeInstMap[$sid])) {
                $activeInstMap[$sid] = $ai;
            }
        }

        // Deteksi format Braja vs FR 8001A
        $isBraja = false;
        $hasClassCol = false;
        foreach ($rows as $rCheck) {
            $colA = trim((string)($rCheck['A'] ?? ''));
            $colB = trim((string)($rCheck['B'] ?? ''));
            $colD = strtolower(trim((string)($rCheck['D'] ?? '')));
            $colG = strtoupper(trim((string)($rCheck['G'] ?? '')));
            if (preg_match('/^kelas\s*:/i', $colA)
                || (strtolower($colA) === 'urut' && strtolower($colB) === 'induk')
                || str_contains($colG, 'TAGIHAN / JENIS BIAYA')) {
                $isBraja = true;
                break;
            }
            if (str_contains($colD, 'kelas')) {
                $hasClassCol = true;
            }
        }

        $valid = [];
        $invalid = [];
        $skippedZero = 0;
        $skippedOtherUnit = 0;
        $otherUnitBreakdown = [];

        $currentClass = '';
        $brajaCostCols = [];
        $jumlahColIdx = 0;

        foreach ($rows as $rowIndex => $row) {
            $colA = trim((string)($row['A'] ?? ''));
            $nis  = trim((string)($row['B'] ?? ''));
            $namaExcel = trim((string)($row['C'] ?? ''));

            if ($isBraja) {
                // Tangkap blok KELAS : ...
                if (preg_match('/^kelas\s*:\s*(.+)$/i', $colA, $mCls)) {
                    $currentClass = trim($mCls[1]);
                    continue;
                }

                // Tangkap kolom JUMLAH dari baris header NOMOR ...
                if (strtoupper($colA) === 'NOMOR') {
                    $jumlahColIdx = 0;
                    foreach ($row as $colLet => $cellVal) {
                        if (strtoupper(trim((string)$cellVal)) === 'JUMLAH') {
                            $jumlahColIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLet);
                        }
                    }
                    continue;
                }

                // Tangkap daftar nama kolom biaya dinamis pada baris sub-header URUT | INDUK ...
                if (strtolower($colA) === 'urut' && strtolower($nis) === 'induk') {
                    $brajaCostCols = [];
                    foreach ($row as $colLet => $cellVal) {
                        $cIdx = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($colLet);
                        if ($cIdx < 7) continue; // Kolom biaya dimulai setelah F (HP Ortu)
                        $lbl = trim((string)$cellVal);
                        if (strtoupper($lbl) === 'JUMLAH') {
                            $jumlahColIdx = $cIdx;
                            continue;
                        }
                        if ($jumlahColIdx > 0 && $cIdx >= $jumlahColIdx) {
                            continue;
                        }
                        if ($lbl !== '') {
                            $brajaCostCols[$colLet] = $lbl;
                        }
                    }
                    continue;
                }

                // Abaikan baris TOTAL, kop sekolah, judul, dan baris non-siswa
                $upperA = strtoupper($colA);
                $upperC = strtoupper($namaExcel);
                if ($upperA === 'TOTAL' || $upperC === 'TOTAL' || $colA === '' || !is_numeric($colA)) {
                    continue;
                }
            } else {
                if ($colA === '' || strtolower($colA) === 'no urut' || strtolower($nis) === 'induk' || strtolower($nis) === 'nis' || !is_numeric($colA)) {
                    continue;
                }
            }

            if ($nis === '' && $namaExcel === '') {
                continue;
            }

            $bukuPaket = 0.0;
            $kegiatanSiswa = 0.0;
            $komite = 0.0;
            $peningkatanMutu = 0.0;
            $spp = 0.0;
            $uangUjian = 0.0;
            $tunggakanLalu = 0.0;
            $customMap = [];
            $excelClass = $currentClass;

            if ($isBraja && !empty($brajaCostCols)) {
                foreach ($brajaCostCols as $colLet => $headerLbl) {
                    $val = $this->parseRupiah($row[$colLet] ?? '0');
                    if ($val <= 0) continue;

                    $lowLbl = strtolower($headerLbl);
                    if (str_contains($lowLbl, 'buku paket')) {
                        $bukuPaket += $val;
                    } elseif (str_contains($lowLbl, 'kegiatan')) {
                        $kegiatanSiswa += $val;
                    } elseif (str_contains($lowLbl, 'komite')) {
                        $komite += $val;
                    } elseif (str_contains($lowLbl, 'mutu')) {
                        $peningkatanMutu += $val;
                    } elseif (str_contains($lowLbl, 'spp')) {
                        $spp += $val;
                    } elseif (str_contains($lowLbl, 'ujian')) {
                        $uangUjian += $val;
                    } elseif (str_contains($lowLbl, 'tunggakan')) {
                        $tunggakanLalu += $val;
                    } else {
                        $cleanLbl = trim(preg_replace('/\s*(?:PG|TK|KB|SD|SMP|SMA)?\s*TA\.?\s*\d{4}\s*\/\s*\d{4}/i', '', $headerLbl));
                        if ($cleanLbl === '') $cleanLbl = $headerLbl;
                        if ($cleanLbl === strtoupper($cleanLbl)) {
                            $cleanLbl = ucwords(strtolower($cleanLbl));
                        }
                        $customMap[$cleanLbl] = ($customMap[$cleanLbl] ?? 0.0) + $val;
                    }
                }
            } else {
                if (!$hasClassCol) {
                    $valD = trim((string)($row['D'] ?? ''));
                    $valE = strtoupper(trim((string)($row['E'] ?? '')));
                    if (!in_array(strtoupper($valD), ['L', 'P', 'LAKI-LAKI', 'PEREMPUAN'], true) && in_array($valE, ['L', 'P'], true)) {
                        $hasClassCol = true;
                    }
                }

                $colKeys = $hasClassCol
                    ? ['kelas' => 'D', 'bp' => 'H', 'ks' => 'I', 'km' => 'J', 'pm' => 'K', 'spp' => 'L', 'uj' => 'M', 'tl' => 'N']
                    : ['kelas' => null, 'bp' => 'G', 'ks' => 'H', 'km' => 'I', 'pm' => 'J', 'spp' => 'K', 'uj' => 'L', 'tl' => 'M'];

                $excelClass      = $colKeys['kelas'] ? trim((string)($row[$colKeys['kelas']] ?? '')) : $currentClass;
                $bukuPaket       = $this->parseRupiah($row[$colKeys['bp']] ?? '0');
                $kegiatanSiswa   = $this->parseRupiah($row[$colKeys['ks']] ?? '0');
                $komite          = $this->parseRupiah($row[$colKeys['km']] ?? '0');
                $peningkatanMutu = $this->parseRupiah($row[$colKeys['pm']] ?? '0');
                $spp             = $this->parseRupiah($row[$colKeys['spp']] ?? '0');
                $uangUjian       = $this->parseRupiah($row[$colKeys['uj']] ?? '0');
                $tunggakanLalu   = $this->parseRupiah($row[$colKeys['tl']] ?? '0');
            }

            $customItems = [];
            $customSum = 0.0;
            foreach ($customMap as $cName => $cVal) {
                if ($cVal > 0) {
                    $customItems[] = ['name' => $cName, 'amount' => $cVal];
                    $customSum += $cVal;
                }
            }

            $amountSpp    = $spp;
            $amountNonSpp = $bukuPaket + $kegiatanSiswa + $komite + $peningkatanMutu + $uangUjian + $tunggakanLalu + $customSum;
            $totalAmount  = $amountSpp + $amountNonSpp;

            // Cocokkan siswa di unit aktif: prioritas exact NIS -> NIS tanpa 0 depan -> Nama lengkap
            $matchedStudent = null;
            $matchNote = 'NIS cocok';
            $trimNis = ltrim($nis, '0');
            $normName = strtolower(preg_replace('/\s+/', ' ', $namaExcel));

            if ($nis !== '' && isset($studentByNis[$nis])) {
                $matchedStudent = $studentByNis[$nis];
            } elseif ($trimNis !== '' && isset($studentByNisTrimmed[$trimNis])) {
                $matchedStudent = $studentByNisTrimmed[$trimNis];
                $matchNote = 'NIS cocok (format angka)';
            } elseif ($normName !== '' && isset($studentByName[$normName])) {
                $matchedStudent = $studentByName[$normName];
                $matchNote = 'Cocok via Nama (NIS file: ' . ($nis ?: '-') . ')';
            }

            if (!$matchedStudent) {
                // Cek apakah siswa milik unit lain (KB/TK/SD/SMP/SMA tetap terpisah)
                $otherUnitName = null;
                if ($nis !== '' && isset($otherByNis[$nis])) {
                    $otherUnitName = $otherByNis[$nis];
                } elseif ($trimNis !== '' && isset($otherByNisTrimmed[$trimNis])) {
                    $otherUnitName = $otherByNisTrimmed[$trimNis];
                } elseif ($normName !== '' && isset($otherByName[$normName])) {
                    $otherUnitName = $otherByName[$normName];
                }

                if ($otherUnitName !== null) {
                    $skippedOtherUnit++;
                    $otherUnitBreakdown[$otherUnitName] = ($otherUnitBreakdown[$otherUnitName] ?? 0) + 1;
                    continue;
                }

                if ($totalAmount <= 0) {
                    $skippedZero++;
                    continue;
                }

                $invalid[] = [
                    'row_num'    => $rowIndex,
                    'nis'        => $nis,
                    'nama'       => $namaExcel,
                    'class_name' => $excelClass ?: '-',
                    'amount'     => $totalAmount,
                    'reason'     => 'Siswa tidak ditemukan di Master Siswa unit ini (periksa ejaan nama/NIS).'
                ];
                continue;
            }

            $sid = (int)$matchedStudent['id'];
            $existingAmount = $openBillMap[$sid] ?? 0.0;
            $hasOpenBill = isset($openBillMap[$sid]);
            $instInfo = $activeInstMap[$sid] ?? null;

            $oldInstTotal = $instInfo ? (float)$instInfo['total_debt'] : 0.0;
            $oldInstRemain = $instInfo ? (float)$instInfo['remaining_debt'] : 0.0;
            $instDelta = $instInfo ? ($totalAmount - $oldInstRemain) : 0.0;
            $newInstTotal = ($instInfo && $instDelta > 0.5) ? ($oldInstTotal + $instDelta) : $oldInstTotal;

            if ($totalAmount <= 0) {
                if ($hasOpenBill || $instInfo) {
                    $valid[] = [
                        'row_num'            => $rowIndex,
                        'student_id'         => $sid,
                        'nis'                => $matchedStudent['nis'],
                        'nama'               => $matchedStudent['nama'],
                        'class_name'         => $matchedStudent['class_name'] ?: ($excelClass ?: 'Non-Kelas'),
                        'gender'             => $matchedStudent['gender'] ?? 'L',
                        'match_note'         => $matchNote,
                        'action_type'        => 'mark_lunas',
                        'buku_paket'         => 0.0,
                        'kegiatan_siswa'     => 0.0,
                        'komite'             => 0.0,
                        'peningkatan_mutu'   => 0.0,
                        'spp'                => 0.0,
                        'uang_ujian'         => 0.0,
                        'tunggakan_lalu'     => 0.0,
                        'custom_items'       => [],
                        'amount_spp'         => 0.0,
                        'amount_non_spp'     => 0.0,
                        'amount'             => 0.0,
                        'existing_amount'    => $existingAmount,
                        'new_total'          => 0.0,
                        'has_installment'    => (bool)$instInfo,
                        'agreement_number'   => $instInfo['agreement_number'] ?? null,
                        'inst_old_total'     => $oldInstTotal,
                        'inst_old_remaining' => $oldInstRemain,
                        'inst_delta'         => -$oldInstRemain,
                        'inst_new_total'     => $oldInstTotal
                    ];
                } else {
                    $skippedZero++;
                }
                continue;
            }

            $valid[] = [
                'row_num'            => $rowIndex,
                'student_id'         => $sid,
                'nis'                => $matchedStudent['nis'],
                'nama'               => $matchedStudent['nama'],
                'class_name'         => $matchedStudent['class_name'] ?: ($excelClass ?: 'Non-Kelas'),
                'gender'             => $matchedStudent['gender'] ?? 'L',
                'match_note'         => $matchNote,
                'action_type'        => $hasOpenBill ? 'overwrite' : 'new',
                'buku_paket'         => $bukuPaket,
                'kegiatan_siswa'     => $kegiatanSiswa,
                'komite'             => $komite,
                'peningkatan_mutu'   => $peningkatanMutu,
                'spp'                => $spp,
                'uang_ujian'         => $uangUjian,
                'tunggakan_lalu'     => $tunggakanLalu,
                'custom_items'       => $customItems,
                'amount_spp'         => $amountSpp,
                'amount_non_spp'     => $amountNonSpp,
                'amount'             => $totalAmount,
                'existing_amount'    => $existingAmount,
                'new_total'          => $totalAmount,
                'has_installment'    => (bool)$instInfo,
                'agreement_number'   => $instInfo['agreement_number'] ?? null,
                'inst_old_total'     => $oldInstTotal,
                'inst_old_remaining' => $oldInstRemain,
                'inst_delta'         => $instDelta,
                'inst_new_total'     => $newInstTotal
            ];
        }

        return [
            'format'               => $isBraja ? 'braja' : 'fr8001a',
            'valid'                => $valid,
            'invalid'              => $invalid,
            'skipped_zero'         => $skippedZero,
            'skipped_other_unit'   => $skippedOtherUnit,
            'other_unit_breakdown' => $otherUnitBreakdown
        ];
    }

    /**
     * Terapkan hasil verifikasi impor dengan MODEL TIMPA / SINKRONISASI SALDO:
     * - Saldo Rp 0 pada siswa yang sebelumnya menunggak -> otomatis ditandai Lunas (+ tutup cicilan aktif bila ada).
     * - Tagihan tertunggak yang sudah ada -> DITIMPA rincian & totalnya sesuai saldo Braja terbaru.
     * - Siswa dengan Buku Cicilan Aktif (Skenario A): penambahan hutang (saldoBaru - sisaCicilan) menambah total_debt & remaining_debt + menulis catatan otomatis.
     */
    private function applyBillImportRows(array $validRows, int $unitId, int $taId, int $userId, int $billMonth, int $billYear, string $dueDate): array {
        $importedCount = 0;
        $overwrittenCount = 0;
        $lunasCount = 0;
        $installmentUpdatedCount = 0;
        $periodLabel = sprintf('%02d/%04d', $billMonth, $billYear);
        $nowStr = date('d/m/Y H:i');

        foreach ($validRows as $r) {
            $studentId  = (int)$r['student_id'];
            $actionType = $r['action_type'] ?? 'new';
            $newSpp     = (float)($r['amount_spp'] ?? 0);
            $newNonSpp  = (float)($r['amount_non_spp'] ?? 0);
            $newTotal   = (float)($r['amount'] ?? 0);

            if ($studentId <= 0) {
                continue;
            }

            // 1. Jika saldo Braja terbaru Rp 0 untuk siswa yang masih punya tunggakan/cicilan -> tandai Lunas otomatis
            if ($actionType === 'mark_lunas' || $newTotal <= 0) {
                $lunasNote = "\n[{$nowStr}] Sync Impor ({$periodLabel}): Saldo laporan Rp 0 — Otomatis ditandai Lunas.";
                Database::execute(
                    "UPDATE bills 
                     SET status = 'Lunas',
                         pipeline_stage = 'selesai_tu',
                         paid_date = CURDATE(),
                         paid_amount = amount,
                         pipeline_resolved_at = NOW(),
                         pipeline_resolved_level = 'tata_usaha',
                         notes = TRIM(CONCAT(COALESCE(notes, ''), ?))
                     WHERE unit_id = ? AND academic_year_id = ? AND student_id = ? AND status = 'Tertunggak'",
                    [$lunasNote, $unitId, $taId, $studentId]
                );
                $lunasCount++;

                $activeInst = Database::fetch(
                    "SELECT id, remaining_debt, notes FROM bill_installments WHERE unit_id = ? AND student_id = ? AND status = 'Aktif' ORDER BY id DESC LIMIT 1",
                    [$unitId, $studentId]
                );
                if ($activeInst) {
                    $oldRemain = (float)$activeInst['remaining_debt'];
                    $instNote = "[{$nowStr}] Update Impor ({$periodLabel}): Saldo laporan Rp 0 — Cicilan ditandai LUNAS otomatis (sisa sebelumnya " . SecurityHelper::formatRupiah($oldRemain) . ").";
                    $combinedNotes = trim((!empty($activeInst['notes']) ? $activeInst['notes'] . "\n" : '') . $instNote);
                    Database::execute(
                        "UPDATE bill_installments SET remaining_debt = 0, status = 'Lunas', notes = ? WHERE id = ?",
                        [$combinedNotes, $activeInst['id']]
                    );
                    $installmentUpdatedCount++;
                }
                continue;
            }

            // 2. Bangun rincian biaya (termasuk kolom dinamis seperti Gedung & Seragam di custom_items)
            $newItems = [
                'buku_paket'       => (float)($r['buku_paket'] ?? 0),
                'kegiatan_siswa'   => (float)($r['kegiatan_siswa'] ?? 0),
                'komite'           => (float)($r['komite'] ?? 0),
                'peningkatan_mutu' => (float)($r['peningkatan_mutu'] ?? 0),
                'spp'              => (float)($r['spp'] ?? 0),
                'uang_ujian'       => (float)($r['uang_ujian'] ?? 0),
                'tunggakan_lalu'   => (float)($r['tunggakan_lalu'] ?? 0),
                'custom_items'     => $r['custom_items'] ?? []
            ];

            $existing = Database::fetch(
                "SELECT id, amount, notes FROM bills WHERE unit_id = ? AND academic_year_id = ? AND student_id = ? AND status = 'Tertunggak' ORDER BY id DESC LIMIT 1",
                [$unitId, $taId, $studentId]
            );

            if ($existing) {
                $oldAmt = (float)$existing['amount'];
                $logNote = "[{$nowStr}] Sync Timpa Impor ({$periodLabel}): " . SecurityHelper::formatRupiah($oldAmt) . " -> " . SecurityHelper::formatRupiah($newTotal);
                $updatedNotes = trim((!empty($existing['notes']) ? $existing['notes'] . "\n" : '') . $logNote);

                Database::execute(
                    "UPDATE bills 
                     SET bill_month = ?, bill_year = ?, amount = ?, items_detail = ?, amount_spp = ?, amount_non_spp = ?, due_date = ?, notes = ? 
                     WHERE id = ?",
                    [$billMonth, $billYear, $newTotal, json_encode($newItems), $newSpp, $newNonSpp, $dueDate, $updatedNotes, $existing['id']]
                );
                $overwrittenCount++;
            } else {
                $logNote = "[{$nowStr}] Impor Tagihan ({$periodLabel}): " . SecurityHelper::formatRupiah($newTotal);
                Database::execute(
                    "INSERT INTO bills 
                        (unit_id, academic_year_id, student_id, bill_month, bill_year, title, amount, items_detail, amount_spp, amount_non_spp, due_date, status, pipeline_stage, notes, created_by_user_id) 
                     VALUES (?, ?, ?, ?, ?, 'Tagihan Siswa & SPP', ?, ?, ?, ?, ?, 'Tertunggak', 'tu_draft', ?, ?)",
                    [$unitId, $taId, $studentId, $billMonth, $billYear, $newTotal, json_encode($newItems), $newSpp, $newNonSpp, $dueDate, $logNote, $userId]
                );
                $importedCount++;
            }

            // 3. Sinkronisasi Buku Cicilan Aktif (Skenario A: pembayaran cicilan juga tercatat di Braja)
            $activeInst = Database::fetch(
                "SELECT id, total_debt, paid_total, remaining_debt, notes FROM bill_installments WHERE unit_id = ? AND student_id = ? AND status = 'Aktif' ORDER BY id DESC LIMIT 1",
                [$unitId, $studentId]
            );
            if ($activeInst) {
                $oldTotal  = (float)$activeInst['total_debt'];
                $oldRemain = (float)$activeInst['remaining_debt'];
                $delta     = $newTotal - $oldRemain;

                if ($delta > 0.5) {
                    $newInstTotal  = $oldTotal + $delta;
                    $newInstRemain = $newTotal;
                    $instNote = "[{$nowStr}] Update Impor ({$periodLabel}): Penambahan hutang +" . SecurityHelper::formatRupiah($delta)
                              . ". Total hutang diperbarui " . SecurityHelper::formatRupiah($oldTotal) . " -> " . SecurityHelper::formatRupiah($newInstTotal)
                              . ", sisa hutang " . SecurityHelper::formatRupiah($newInstRemain) . ".";
                    $combinedNotes = trim((!empty($activeInst['notes']) ? $activeInst['notes'] . "\n" : '') . $instNote);
                    Database::execute(
                        "UPDATE bill_installments SET total_debt = ?, remaining_debt = ?, notes = ? WHERE id = ?",
                        [$newInstTotal, $newInstRemain, $combinedNotes, $activeInst['id']]
                    );
                    $installmentUpdatedCount++;
                } elseif ($delta < -0.5) {
                    $newInstRemain = $newTotal;
                    $instNote = "[{$nowStr}] Update Impor ({$periodLabel}): Penyesuaian saldo turun -" . SecurityHelper::formatRupiah(abs($delta))
                              . " (pembayaran tercatat di Braja). Sisa hutang diperbarui " . SecurityHelper::formatRupiah($oldRemain) . " -> " . SecurityHelper::formatRupiah($newInstRemain) . ".";
                    $combinedNotes = trim((!empty($activeInst['notes']) ? $activeInst['notes'] . "\n" : '') . $instNote);
                    Database::execute(
                        "UPDATE bill_installments SET remaining_debt = ?, notes = ? WHERE id = ?",
                        [$newInstRemain, $combinedNotes, $activeInst['id']]
                    );
                    $installmentUpdatedCount++;
                }
            }
        }

        return [
            'imported'            => $importedCount,
            'overwritten'         => $overwrittenCount,
            'marked_lunas'        => $lunasCount,
            'installment_updated' => $installmentUpdatedCount
        ];
    }

    public function verifyImportBills(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        if (empty($_FILES['excel_file']['tmp_name'])) {
            $this->setFlash('danger', 'Harap pilih berkas Braja / FR 8001A (.xlsx / .xls / .csv) untuk diverifikasi.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $billMonth = max(1, min(12, (int)($_POST['bill_month'] ?? date('n'))));
        $billYear  = max(2020, (int)($_POST['bill_year'] ?? date('Y')));
        $dueDate   = trim((string)($_POST['due_date'] ?? ''));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
            $dueDate = date('Y-m-10');
        }

        $origName = (string)($_FILES['excel_file']['name'] ?? 'import.xlsx');

        try {
            $parsed = $this->parseImportExcelRows($_FILES['excel_file']['tmp_name'], $unitId, $taId, $origName);
            $_SESSION['bill_import_preview'] = [
                'filename'             => $origName,
                'format'               => $parsed['format'],
                'bill_month'           => $billMonth,
                'bill_year'            => $billYear,
                'due_date'             => $dueDate,
                'valid'                => $parsed['valid'],
                'invalid'              => $parsed['invalid'],
                'skipped_zero'         => $parsed['skipped_zero'],
                'skipped_other_unit'   => $parsed['skipped_other_unit'],
                'other_unit_breakdown' => $parsed['other_unit_breakdown']
            ];
            $vCnt = count($parsed['valid']);
            $iCnt = count($parsed['invalid']);
            $fmtLabel = ($parsed['format'] === 'braja') ? 'Format Braja' : 'Format FR 8001A';
            $extraInfo = '';
            if ($parsed['skipped_other_unit'] > 0) {
                $extraInfo .= ", {$parsed['skipped_other_unit']} baris unit lain dilewati otomatis";
            }
            $this->setFlash('info', "Verifikasi {$fmtLabel}: {$vCnt} data siap diproses, {$iCnt} bermasalah{$extraInfo}. Silakan tinjau sebelum menyimpan.");
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Gagal membaca berkas impor: ' . $e->getMessage());
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    public function confirmImportBills(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $action = $_POST['action'] ?? 'commit';
        if ($action === 'cancel') {
            unset($_SESSION['bill_import_preview']);
            $this->setFlash('info', 'Pratinjau impor tagihan dibatalkan.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $preview = $_SESSION['bill_import_preview'] ?? null;
        if (!$preview || empty($preview['valid'])) {
            unset($_SESSION['bill_import_preview']);
            $this->setFlash('warning', 'Tidak ada data tagihan valid untuk disimpan.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $selectedIndices = $_POST['keep_indices'] ?? null;
        $rowsToProcess = [];
        if (is_array($selectedIndices)) {
            $keepSet = array_flip(array_map('intval', $selectedIndices));
            foreach ($preview['valid'] as $idx => $row) {
                if (isset($keepSet[(int)$idx])) {
                    $rowsToProcess[] = $row;
                }
            }
        } else {
            $rowsToProcess = $preview['valid'];
        }

        $stats = $this->applyBillImportRows(
            $rowsToProcess,
            $unitId,
            $taId,
            $userId,
            (int)$preview['bill_month'],
            (int)$preview['bill_year'],
            (string)$preview['due_date']
        );
        unset($_SESSION['bill_import_preview']);

        AuditLog::log('IMPORT_BILLS_SNAPSHOT_SYNC', 'bills', $unitId, null, $stats);

        $msg = "Sinkronisasi Tagihan Selesai: {$stats['imported']} baru dibuat, {$stats['overwritten']} diperbarui (timpa saldo), {$stats['marked_lunas']} otomatis Lunas.";
        if ($stats['installment_updated'] > 0) {
            $msg .= " ({$stats['installment_updated']} Buku Cicilan aktif diperbarui beserta catatan otomatis).";
        }
        $this->setFlash('success', $msg);
        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    public function importBills(): void {
        // Fallback langsung ke tahap verifikasi agar tidak ada data masuk tanpa verifikasi
        $this->verifyImportBills();
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
        $this->assertUnitOwnership('bills', $billId, 'unit_id');

        $letterNo = !empty($bill['tu_reminder_letter_no']) ? $bill['tu_reminder_letter_no'] : ('REM/TU/' . date('Ym') . '/' . sprintf('%04d', $billId));
        $html = $this->generateFR8001BHtml($bill, $letterNo);

        ExportHelper::pdf($html, 'Surat_Reminder_FR8001B_' . $bill['nis'], 'A4', 'portrait', true);
    }

    private function generateFR8001BHtml(array $bill, string $letterNo): string {
        $formattedAmount = number_format((float)$bill['amount'], 0, ',', '.');
        $bsiAcc = $bill['bank_account'] ?: '1033667414';
        $bsiHolder = $bill['bank_holder'] ?: $bill['unit_name'];
        $unitPhone = $bill['unit_phone'] ?: '-';

        // Ambil data staf Tata Usaha aktif langsung dari DB unit (konsisten walau admin switch user)
        $tuEmp = $this->resolveUnitSigner((int)$bill['unit_id'], 'tata_usaha');
        $tuName = $tuEmp['nama'];
        $tuNipy = $tuEmp['nipy'];

        // Render Kop Surat standar dual logo
        $kopHtml = ExportHelper::renderKop($bill);
        if (empty($letterNo)) {
            $letterNo = !empty($bill['tu_reminder_letter_no']) ? $bill['tu_reminder_letter_no'] : ('REM/TU/' . date('Ym') . '/' . sprintf('%04d', $bill['id'] ?? 0));
        }

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

        return $html;
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

    private function configureMailer(PHPMailer $mail, array $smtp): void {
        $mail->isSMTP();
        $mail->Host       = trim($smtp['host']);
        $mail->SMTPAuth   = true;
        $mail->Username   = trim($smtp['username']);
        $mail->Password   = $smtp['password'];
        $mail->Timeout    = 25;
        $mail->CharSet    = 'UTF-8';

        $port = (int)($smtp['port'] ?: 587);
        $enc  = strtolower(trim($smtp['encryption'] ?? ''));
        if ($enc === 'ssl' || $port === 465) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = $port ?: 465;
        } elseif ($enc === 'tls' || $port === 587) {
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port ?: 587;
        } else {
            $mail->SMTPSecure  = false;
            $mail->SMTPAutoTLS = false;
            $mail->Port        = $port ?: 25;
        }

        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ]
        ];
    }

    private function sendSingleFR8001B(int $billId, int $unitId, ?array $smtp = null): array {
        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, s.parent_email, 
                    (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                     WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                     ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1) as class_name,
                    u.name as unit_name, u.phone as unit_phone, u.bank_name, 
                    u.bank_account, u.bank_holder, u.kop_title, u.kop_subtitle, u.address, u.email as unit_email,
                    u.code, u.level, u.npsn, u.akreditasi, u.nomor_akreditasi, u.nomor_izin_operasional, u.logo_path
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             WHERE b.id = ? AND b.unit_id = ?",
            [$billId, $unitId]
        );

        if (!$bill) {
            return ['found' => false, 'email_sent' => false, 'reason' => 'not_found', 'error' => 'Data tagihan tidak ditemukan.'];
        }

        $letterNo = !empty($bill['tu_reminder_letter_no'])
            ? $bill['tu_reminder_letter_no']
            : ('REM/TU/' . date('Ym') . '/' . sprintf('%04d', $billId));
        $bill['tu_reminder_letter_no'] = $letterNo;

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = CASE WHEN pipeline_stage = 'tu_draft' THEN 'tu_reminder_sent' ELSE pipeline_stage END, 
                 tu_reminder_sent_at = COALESCE(tu_reminder_sent_at, NOW()), 
                 tu_reminder_letter_no = ? 
             WHERE id = ?",
            [$letterNo, $billId]
        );

        $smtp = $smtp ?? $this->getSmtpConfig($unitId);
        $parentEmail = trim($bill['parent_email'] ?? '');

        if (empty($parentEmail)) {
            AuditLog::log('SEND_REMINDER_FR8001B', 'bills', $billId, null, ['letter_no' => $letterNo, 'email_sent' => false, 'reason' => 'no_parent_email']);
            return ['found' => true, 'email_sent' => false, 'reason' => 'no_email', 'letter_no' => $letterNo, 'parent_email' => ''];
        }
        if (empty($smtp)) {
            AuditLog::log('SEND_REMINDER_FR8001B', 'bills', $billId, null, ['letter_no' => $letterNo, 'email_sent' => false, 'reason' => 'no_smtp']);
            return ['found' => true, 'email_sent' => false, 'reason' => 'no_smtp', 'letter_no' => $letterNo, 'parent_email' => $parentEmail];
        }

        $emailSent = false;
        $emailError = null;
        try {
            @ini_set('memory_limit', '512M');
            @set_time_limit(120);

            $mail = new PHPMailer(true);
            $this->configureMailer($mail, $smtp);

            $fromEmail = !empty($smtp['from_email']) ? trim($smtp['from_email']) : trim($smtp['username']);
            $fromName  = !empty($smtp['from_name']) ? trim($smtp['from_name']) : ($bill['unit_name'] ?? 'Tata Usaha');
            $mail->setFrom($fromEmail, $fromName);
            $mail->addAddress($parentEmail, $bill['student_name']);
            $mail->isHTML(true);
            $mail->Subject = "[Reminder Tunggakan - FR 8001B] " . $bill['student_name'] . " - " . $bill['unit_name'];
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                    <h4 style='color: #0f766e; margin-top: 0;'>Pemberitahuan Tagihan Sekolah (FR 8001B)</h4>
                    <p>Yth. Orang Tua dari ananda <strong>" . htmlspecialchars($bill['student_name']) . "</strong>,</p>
                    <p>Berdasarkan data keuangan kami, terdapat kewajiban pembiayaan sekolah yang belum diselesaikan sebesar <strong>" . SecurityHelper::formatRupiah((float)$bill['amount']) . "</strong>.</p>
                    <p>Dokumen resmi Surat Pemberitahuan Tunggakan (FR 8001B) terlampir dalam format PDF pada email ini.</p>
                    <p>Mohon dapat melakukan konfirmasi atau penyelesaian pembayaran melalui loket Tata Usaha sekolah atau rekening resmi sekolah.</p>
                    <p><em>Jazakumullah Khairan Katsiran.</em></p>
                </div>";

            try {
                $htmlPdf = $this->generateFR8001BHtml($bill, $letterNo);
                $pdfBytes = ExportHelper::getPdfBytes($htmlPdf, 'A4', 'portrait');
                if (!empty($pdfBytes)) {
                    $cleanNis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $bill['nis']);
                    $mail->addStringAttachment($pdfBytes, 'Surat_Pemberitahuan_FR8001B_' . $cleanNis . '.pdf', 'base64', 'application/pdf');
                }
            } catch (\Throwable $pdfEx) {
                error_log('Gagal membuat lampiran PDF FR8001B: ' . $pdfEx->getMessage());
            }

            $mail->send();
            $emailSent = true;
        } catch (\Throwable $e) {
            $emailError = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
            error_log('Gagal mengirim email FR 8001B: ' . $emailError);
        }

        AuditLog::log('SEND_REMINDER_FR8001B', 'bills', $billId, null, [
            'letter_no'   => $letterNo,
            'email_sent'  => $emailSent,
            'email_error' => $emailError
        ]);

        return [
            'found'        => true,
            'email_sent'   => $emailSent,
            'reason'       => $emailSent ? 'sent' : 'smtp_error',
            'error'        => $emailError,
            'letter_no'    => $letterNo,
            'parent_email' => $parentEmail
        ];
    }

    public function kirimSuratFR8001B(): void {
        $this->requireRole('tata_usaha');
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();

        $res = $this->sendSingleFR8001B($billId, $unitId);
        if (!$res['found']) {
            $this->setFlash('danger', 'Data tagihan tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $letterNo = $res['letter_no'];
        if ($res['email_sent']) {
            $this->setFlash('success', "Surat reminder FR 8001B berhasil diterbitkan ({$letterNo}) dan email beserta lampiran PDF berhasil dikirim ke orang tua ({$res['parent_email']}).");
        } elseif ($res['reason'] === 'no_email') {
            $this->setFlash('warning', "Surat reminder FR 8001B berhasil diterbitkan ({$letterNo}), namun email TIDAK dikirim karena alamat email orang tua pada siswa ini belum terdaftar di data siswa.");
        } elseif ($res['reason'] === 'no_smtp') {
            $this->setFlash('warning', "Surat reminder FR 8001B berhasil diterbitkan ({$letterNo}), namun pengiriman email dilewati karena pengaturan SMTP unit belum dikonfigurasi.");
        } else {
            $this->setFlash('warning', "Surat reminder FR 8001B berhasil diterbitkan ({$letterNo}), namun EMAIL GAGAL DIKIRIM ke {$res['parent_email']}. Kendala: " . htmlspecialchars($res['error'] ?? 'Koneksi gagal'));
        }
        $this->redirect(Auth::baseUrl() . '/tagihan/manage');
    }

    public function bulkKirimSuratFR8001B(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $billIds = $_POST['bill_ids'] ?? [];

        if (empty($billIds) || !is_array($billIds)) {
            $this->setFlash('warning', 'Pilih minimal satu santri dengan mencentang kotak di tabel untuk kirim massal FR 8001B.');
            $this->redirect(Auth::baseUrl() . '/tagihan/manage');
            return;
        }

        $smtp = $this->getSmtpConfig($unitId);
        $sentCount = 0;
        $noEmailCount = 0;
        $failedCount = 0;
        $processed = 0;

        foreach ($billIds as $bId) {
            $id = (int)$bId;
            if ($id <= 0) continue;
            $res = $this->sendSingleFR8001B($id, $unitId, $smtp);
            if (!$res['found']) continue;
            $processed++;
            if ($res['email_sent']) {
                $sentCount++;
            } elseif ($res['reason'] === 'no_email') {
                $noEmailCount++;
            } else {
                $failedCount++;
            }
        }

        if ($processed === 0) {
            $this->setFlash('warning', 'Tidak ada data tagihan valid yang diproses.');
        } elseif (empty($smtp)) {
            $this->setFlash('warning', "{$processed} surat FR 8001B diterbitkan, namun email tidak terkirim karena SMTP unit belum dikonfigurasi.");
        } else {
            $type = ($sentCount > 0 && $failedCount === 0) ? 'success' : 'warning';
            $this->setFlash($type, "Kirim Massal FR 8001B selesai ({$processed} tagihan): {$sentCount} email+PDF terkirim, {$noEmailCount} dilewati (email ortu kosong), {$failedCount} gagal.");
        }

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
                     paid_date = CURDATE(), 
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
    public static function ensureSignedColumns(): void {
        try {
            $cols = array_column(Database::fetchAll("SHOW COLUMNS FROM bills"), 'Field');
            if (!in_array('signed_fr8001c_path', $cols, true)) {
                Database::execute("ALTER TABLE bills ADD COLUMN signed_fr8001c_path VARCHAR(255) NULL AFTER notes");
            }
            if (!in_array('signed_fr8001d_path', $cols, true)) {
                Database::execute("ALTER TABLE bills ADD COLUMN signed_fr8001d_path VARCHAR(255) NULL AFTER signed_fr8001c_path");
            }
            if (!in_array('signed_uploaded_at', $cols, true)) {
                Database::execute("ALTER TABLE bills ADD COLUMN signed_uploaded_at DATETIME NULL AFTER signed_fr8001d_path");
            }
            if (!in_array('signed_uploaded_by_level', $cols, true)) {
                Database::execute("ALTER TABLE bills ADD COLUMN signed_uploaded_by_level VARCHAR(50) NULL AFTER signed_uploaded_at");
            }
        } catch (\Throwable $e) {
            error_log('Gagal memastikan kolom signed bills: ' . $e->getMessage());
        }
    }

    private function handleSignedUploads(int $billId, string $level): array {
        $paths = [];
        $uploadDir = dirname(__DIR__, 2) . '/public/uploads/tagihan/signed';
        if (!is_dir($uploadDir)) {
            @mkdir($uploadDir, 0777, true);
        }

        $filesToProcess = [
            'signed_fr8001c' => 'signed_fr8001c_path',
            'signed_fr8001d' => 'signed_fr8001d_path'
        ];

        foreach ($filesToProcess as $inputName => $colName) {
            if (!empty($_FILES[$inputName]['name']) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
                $ext = strtolower(pathinfo($_FILES[$inputName]['name'], PATHINFO_EXTENSION));
                $allowed = ['pdf', 'jpg', 'jpeg', 'png'];
                if (in_array($ext, $allowed, true)) {
                    $newFilename = 'signed_' . $inputName . '_' . $billId . '_' . time() . '.' . $ext;
                    $targetPath = $uploadDir . '/' . $newFilename;
                    if (move_uploaded_file($_FILES[$inputName]['tmp_name'], $targetPath)) {
                        $paths[$colName] = 'uploads/tagihan/signed/' . $newFilename;
                    }
                }
            }
        }

        return $paths;
    }

    /**
     * Unggah opsional dokumen FR 8001C & FR 8001D bertanda tangan orang tua oleh TU, Kepala Sekolah, atau Deputi.
     */
    public function uploadSignedDoc(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan']);
        self::ensureSignedColumns();

        $billId = (int)($_POST['bill_id'] ?? 0);
        $role = Auth::activeRoleName();
        $unitId = Auth::unitId();

        $bill = ($role === 'deputi_pendidikan')
            ? Database::fetch("SELECT id, unit_id FROM bills WHERE id = ?", [$billId])
            : Database::fetch("SELECT id, unit_id FROM bills WHERE id = ? AND unit_id = ?", [$billId, $unitId]);

        if (!$bill) {
            $this->setFlash('danger', 'Data tagihan tidak ditemukan atau bukan milik unit Anda.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/tagihan/manage'));
            return;
        }

        $signedFiles = $this->handleSignedUploads($billId, $role);
        if (empty($signedFiles)) {
            $this->setFlash('warning', 'Harap pilih file PDF/JPG/PNG untuk FR 8001C atau FR 8001D sebelum menyimpan.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/tagihan/manage'));
            return;
        }

        $setParts = [];
        $params = [];
        foreach ($signedFiles as $col => $val) {
            $setParts[] = "{$col} = ?";
            $params[] = $val;
        }
        $setParts[] = "signed_uploaded_at = NOW()";
        $setParts[] = "signed_uploaded_by_level = ?";
        $params[] = $role;
        $params[] = $billId;

        Database::execute("UPDATE bills SET " . implode(', ', $setParts) . " WHERE id = ?", $params);

        AuditLog::log('UPLOAD_SIGNED_AGREEMENT_DOCS', 'bills', $billId, null, [
            'role'  => $role,
            'files' => array_keys($signedFiles)
        ]);

        $this->setFlash('success', 'Dokumen perjanjian/pemanggilan bertanda tangan orang tua berhasil diunggah & tersimpan.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/tagihan/manage'));
    }

    public function approvalList(): void {
        $this->requireRole(['kepala_sekolah', 'deputi_pendidikan']);
        self::ensureSignedColumns();
        $unitId = Auth::unitId();
        $kepsekInfo = $this->resolveUnitSigner($unitId, 'kepala_sekolah');

        $bills = Database::fetchAll(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik,
                    (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                     WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                     ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1) as class_name,
                    u.name as unit_name, u.phone as unit_phone, u.bank_name, u.bank_account, u.bank_holder
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             WHERE b.unit_id = ? AND (b.pipeline_stage IN ('escalated_kepsek', 'kepsek_summon_sent', 'selesai_kepsek') OR b.status = 'Tertunggak')
             ORDER BY FIELD(b.pipeline_stage, 'escalated_kepsek', 'kepsek_summon_sent', 'selesai_kepsek') ASC, class_name ASC, s.nama ASC",
            [$unitId]
        );
        foreach ($bills as &$bRow) {
            $bRow['kepsek_name'] = $kepsekInfo['nama'];
            $bRow['kepsek_phone'] = $kepsekInfo['phone'];
        }
        unset($bRow);

        $pendingCount = 0;
        $totalNominalPending = 0;
        foreach ($bills as $b) {
            if ($b['pipeline_stage'] === 'escalated_kepsek' || $b['pipeline_stage'] === 'kepsek_summon_sent') {
                $pendingCount++;
                $totalNominalPending += (float)$b['amount'];
            }
        }

        $kepsekEmp = ['nama' => $kepsekInfo['nama'], 'phone' => $kepsekInfo['phone']];

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
                    s.address as alamat, s.nik,
                    (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                     WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                     ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1) as class_name,
                    u.name as unit_name, u.phone as unit_phone, u.bank_name, 
                    u.bank_account, u.bank_holder, u.kop_title, u.kop_subtitle, u.address, u.email as unit_email,
                    u.code, u.level, u.npsn, u.akreditasi, u.nomor_akreditasi, u.nomor_izin_operasional, u.logo_path
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }
        $this->assertUnitOwnership('bills', $billId, 'unit_id');

        $html = $this->generateFR8001CHtml($bill, false);
        ExportHelper::pdf($html, 'Surat_Panggilan_FR8001C_' . $bill['nis'], 'A4', 'portrait', true);
    }

    public function printSuratFR8001D(string $id): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan']);
        $billId = (int)$id;

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik,
                    (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                     WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                     ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1) as class_name,
                    u.name as unit_name, u.kop_title, u.kop_subtitle, 
                    u.address, u.phone, u.email, u.code, u.level, u.npsn, u.akreditasi, u.nomor_akreditasi, u.nomor_izin_operasional, u.logo_path
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }
        $this->assertUnitOwnership('bills', $billId, 'unit_id');

        $html = $this->generateFR8001DHtml($bill, false);
        ExportHelper::pdf($html, 'Berita_Acara_dan_Pernyataan_FR8001D_' . $bill['nis'], 'A4', 'portrait', true);
    }

    private function renderKopAisEducation(): string {
        $logoPath = dirname(__DIR__, 2) . '/public/images/logo_alkahfi.png';
        if (!file_exists($logoPath)) {
            $logoPath = dirname(__DIR__, 2) . '/public/uploads/logos/app_logo_1790375160.png';
        }
        $logoHtml = '';
        if (file_exists($logoPath)) {
            $base64 = base64_encode(file_get_contents($logoPath));
            $logoHtml = "<img src='data:image/png;base64,{$base64}' style='max-height: 75px; max-width: 82px; object-fit: contain; margin: 0 auto; display: block;'>";
        } else {
            $logoHtml = "<div style='width: 66px; height: 66px; border-radius: 50%; background: #0f766e; color: #fff; font-weight: bold; line-height: 66px; font-size: 18px; text-align: center; margin: 0 auto;'>AIS</div>";
        }

        return "
        <div style='padding-bottom: 6px; margin-bottom: 14px; font-family: \"Times New Roman\", Times, serif;'>
            <table style='width: 100%; border-collapse: collapse;'>
                <tr>
                    <td style='width: 88px; text-align: center; vertical-align: middle;'>
                        {$logoHtml}
                    </td>
                    <td style='text-align: center; vertical-align: middle; padding: 0 8px;'>
                        <div style='font-size: 15pt; font-weight: bold; text-transform: uppercase; color: #111; letter-spacing: 0.4px;'>EDUCATION DEPARTMENT</div>
                        <div style='font-size: 14.5pt; font-weight: bold; text-transform: uppercase; color: #111; margin-top: 1px;'>AL-KAHFI ISLAMIC SCHOOL (AIS)</div>
                        <div style='font-size: 9.5pt; color: #222; margin-top: 3px; line-height: 1.35;'>
                            Address : The Capitol Imperium Blok A No.40-47, Superblok Imperium<br>
                            Jl. Jend.Soedirman &ndash; Batam, Indonesia, Call Centre : +628-2392-6020-11,<br>
                            Email : <span style='color: #1d4ed8; text-decoration: underline;'>edu@aisbatam.sch.id</span>, web : <span style='color: #1d4ed8; text-decoration: underline;'>www.aisbatam.sch.id</span>
                        </div>
                    </td>
                </tr>
            </table>
            <div style='border-top: 2.5px solid #000; border-bottom: 1px solid #000; height: 2px; margin-top: 6px;'></div>
        </div>";
    }

    private function generateFR8001CHtml(array $bill, bool $isYayasan): string {
        $unitId = (int)$bill['unit_id'];
        $kepsekInfo = $this->resolveUnitSigner($unitId, 'kepala_sekolah');
        $deputiInfo = $this->resolveUnitSigner($unitId, 'deputi_pendidikan');

        $kop = $isYayasan ? ExportHelper::renderKopYayasan() : ExportHelper::renderKop($bill);
        $billId = (int)$bill['id'];
        $letterNo = $isYayasan
            ? ($bill['deputi_letter_no'] ?: ('PANG/YAYASAN/' . date('Ym') . '/' . sprintf('%04d', $billId)))
            : ($bill['kepsek_letter_no'] ?: ('PANG/KS/' . date('Ym') . '/' . sprintf('%04d', $billId)));

        $bsiAcc = SecurityHelper::escape($bill['bank_account'] ?: '1033667414');
        $bsiHolder = SecurityHelper::escape($bill['bank_holder'] ?: $bill['unit_name']);
        $kepsekPhone = SecurityHelper::escape($kepsekInfo['phone'] ?: ($bill['unit_phone'] ?: '-'));
        $kepsekName = SecurityHelper::escape($kepsekInfo['nama']);
        $deputiName = SecurityHelper::escape($deputiInfo['nama']);
        $unitName = SecurityHelper::escape($bill['unit_name']);

        $sppFmt = number_format((float)$bill['amount_spp'], 0, ',', '.');
        $nonSppFmt = number_format((float)$bill['amount_non_spp'], 0, ',', '.');
        $totalFmt = number_format((float)$bill['amount'], 0, ',', '.');

        $schedText = '';
        if ($isYayasan && !empty($bill['deputi_schedule_date'])) {
            $ts = strtotime($bill['deputi_schedule_date']);
            $schedText = "
            <div style='margin: 14px 0; padding: 10px 14px; background: #ecfdf5; border-left: 4px solid #059669;'>
                <strong>Jadwal Pertemuan Orang Tua di Kantor Yayasan:</strong><br>
                Hari / Tanggal : <strong>" . SecurityHelper::formatTanggalIndo(date('Y-m-d', $ts)) . "</strong><br>
                Pukul / Waktu &nbsp;: <strong>" . date('H:i', $ts) . " WIB</strong><br>
                Tempat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <strong>" . SecurityHelper::escape($bill['deputi_schedule_place'] ?: 'Ruang Rapat Direktorat Pendidikan Yayasan') . "</strong>
            </div>";
        } elseif (!$isYayasan && !empty($bill['kepsek_schedule_date'])) {
            $ts = strtotime($bill['kepsek_schedule_date']);
            $schedText = "
            <div style='margin: 14px 0; padding: 10px 14px; background: #f8fafc; border-left: 4px solid #0f766e;'>
                <strong>Jadwal Pertemuan / Silaturahmi Orang Tua:</strong><br>
                Hari / Tanggal : <strong>" . SecurityHelper::formatTanggalIndo(date('Y-m-d', $ts)) . "</strong><br>
                Pukul / Waktu &nbsp;: <strong>" . date('H:i', $ts) . " WIB</strong><br>
                Tempat &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;: <strong>" . SecurityHelper::escape($bill['kepsek_schedule_place'] ?: 'Ruang Kepala Sekolah') . "</strong>
            </div>";
        }

        $docCode = $isYayasan ? 'FR 8001C (Tingkat Yayasan)' : 'FR 8001C (Tingkat Sekolah)';
        $perihal = $isYayasan ? 'Pemanggilan Orang Tua / Wali &ndash; Penyelesaian Tunggakan Biaya Sekolah' : 'Informasi Nominal Tunggakan dan Undangan Silaturahmi';
        $introParagraph = $isYayasan
            ? "Melalui surat ini, Direktorat Pendidikan Yayasan memberitahukan kepada Bapak/Ibu orang tua/wali murid terkait kewajiban pembiayaan pendidikan ananda di {$unitName} yang belum diselesaikan. Pembayaran dapat ditransfer ke rekening resmi <strong>Bank Syariah Indonesia (BSI), No. Rekening {$bsiAcc} a.n. {$bsiHolder}</strong>, dengan rincian sebagai berikut:"
            : "Melalui surat ini kami sampaikan kepada Bapak/Ibu orang tua/wali murid bahwa demi kelancaran Kegiatan Belajar Mengajar (KBM) di {$unitName}, kami memohon kesediaan Bapak/Ibu untuk menyelesaikan kewajiban pembiayaan pendidikan yang masih tertunggak. Pembayaran dapat ditransfer melalui rekening resmi sekolah: <strong>Bank Syariah Indonesia (BSI), No. Rekening {$bsiAcc} a.n. {$bsiHolder}</strong>, dengan rincian sebagai berikut:";

        $closingCall = $isYayasan
            ? "Mengingat pentingnya penyelesaian administrasi pendidikan ananda, kami mengharapkan kehadiran Bapak/Ibu sesuai jadwal tersebut di atas."
            : "<strong>Kami menunggu konfirmasi Bapak/Ibu terkait rencana penyelesaian pembayaran tunggakan tersebut atau menghubungi Kepala Sekolah di nomor HP {$kepsekPhone}.</strong>";

        $sigBlock = $isYayasan
            ? "<tr>
                    <td class='sig-box'>
                        Mengetahui,<br>
                        <strong>Kepala Satuan Pendidikan</strong><br>
                        {$unitName}<br><br><br><br>
                        <strong><u>{$kepsekName}</u></strong>
                    </td>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "<br>
                        <strong>Deputi Direktur Pendidikan Yayasan</strong><br>
                        Al-Kahfi Islamic School Batam<br><br><br><br>
                        <strong><u>{$deputiName}</u></strong>
                    </td>
               </tr>"
            : "<tr>
                    <td class='sig-box'>&nbsp;</td>
                    <td class='sig-box'>
                        Kepala Satuan Pendidikan,<br>
                        <strong>{$unitName}</strong><br><br><br><br>
                        <strong><u>{$kepsekName}</u></strong>
                    </td>
               </tr>";

        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Surat Pemanggilan Orangtua - {$docCode}</title>
            <style>
                @page { margin: 15mm 20mm 20mm 20mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 11.5pt; line-height: 1.45; color: #111; }
                .doc-code { font-size: 9.5pt; margin-bottom: 6px; color: #475569; }
                .meta-table { width: 100%; margin-top: 8px; margin-bottom: 12px; }
                .meta-table td { padding: 2px; vertical-align: top; }
                .data-table { width: 100%; border-collapse: collapse; margin: 12px 0; }
                .data-table th, .data-table td { border: 1px solid #222; padding: 6px 8px; font-size: 11pt; }
                .data-table th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .signatures { width: 100%; margin-top: 24px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 11pt; }
            </style>
        </head>
        <body>
            <div class='doc-code'>Formulir Penagihan Resmi &ndash; <strong>{$docCode}</strong></div>
            {$kop}

            <table class='meta-table'>
                <tr>
                    <td width='12%'>No.</td><td width='2%'>:</td><td width='46%'>{$letterNo}</td>
                    <td width='40%' style='text-align: right;'>Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "</td>
                </tr>
                <tr><td>Lamp.</td><td>:</td><td>-</td><td></td></tr>
                <tr><td>Perihal</td><td>:</td><td colspan='2'><strong>{$perihal}</strong></td></tr>
            </table>

            <div style='margin-top: 8px; margin-bottom: 12px;'>
                Kepada Yth. :<br>
                <strong>Bapak/Ibu Orang Tua / Wali dari " . SecurityHelper::escape($bill['student_name']) . "</strong><br>
                NIS: " . SecurityHelper::escape($bill['nis']) . " &bull; Unit {$unitName} &ndash; Kelas " . SecurityHelper::escape($bill['class_name'] ?? 'Aktif') . "<br>
                Di &ndash; Tempat
            </div>

            <div style='font-style: italic; margin-bottom: 8px;'>Assalamu'alaikum warahmatullahi wabarakatuh,</div>

            <div style='text-align: justify; margin-bottom: 8px;'>
                Segala puji hanyalah milik Allah Subhaanahu wa Ta'ala. Shalawat dan salam senantiasa tercurah kepada junjungan kita Nabi Muhammad Shallallahu 'alaihi wa sallam beserta keluarga dan sahabatnya.
            </div>

            <div style='text-align: justify; margin-bottom: 10px;'>{$introParagraph}</div>

            <table class='data-table'>
                <thead>
                    <tr>
                        <th width='8%'>No</th>
                        <th width='32%'>Tunggakan SPP (Rp)</th>
                        <th width='35%'>Tunggakan Non-SPP (Rp)</th>
                        <th width='25%'>JUMLAH TOTAL</th>
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
                *Data diambil per tanggal " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . ". Abaikan surat ini apabila pembayaran telah diselesaikan.
            </div>

            {$schedText}

            <div style='text-align: justify; margin-bottom: 10px;'>{$closingCall}</div>

            <div style='text-align: justify; margin-bottom: 14px;'>
                Demikian surat ini kami sampaikan. Atas perhatian dan kerja sama Bapak/Ibu kami ucapkan <em>Jazakumullahu khairan katsiran</em>.<br>
                <em style='display: block; margin-top: 6px;'>Wassalamu'alaikum warahmatullahi wabarakatuh.</em>
            </div>

            <table class='signatures'>
                {$sigBlock}
            </table>
        </body>
        </html>";
    }

    /**
     * Format Baru FR 8001D (2 Dokumen Resmi dalam 1 PDF):
     * 1. Berita Acara Kesepakatan Pembayaran Tunggakan Biaya Sekolah
     * 2. Surat Pernyataan Komitmen Pembayaran Tunggakan Biaya Sekolah (Halaman Baru)
     */
    private function generateFR8001DHtml(array $bill, bool $isYayasan): string {
        $unitId = (int)$bill['unit_id'];
        $billId = (int)$bill['id'];
        $kepsekInfo = $this->resolveUnitSigner($unitId, 'kepala_sekolah');
        $deputiInfo = $this->resolveUnitSigner($unitId, 'deputi_pendidikan');

        $kop = $isYayasan ? $this->renderKopAisEducation() : ExportHelper::renderKop($bill);

        // Cek apakah sudah pernah dibuatkan kesepakatan di bill_installments
        $inst = Database::fetch(
            "SELECT * FROM bill_installments WHERE (bill_id = ? OR (student_id = ? AND unit_id = ? AND status = 'Aktif')) ORDER BY id DESC LIMIT 1",
            [$billId, (int)$bill['student_id'], $unitId]
        );

        $parentName    = SecurityHelper::escape(($inst['parent_name'] ?? '') ?: (($bill['parent_name'] ?? '') ?: '................................................................'));
        $parentKtp     = SecurityHelper::escape(($inst['parent_ktp'] ?? '') ?: (($bill['nik'] ?? '') ?: '................................................................'));
        $parentAddress = SecurityHelper::escape(($inst['parent_address'] ?? '') ?: (($bill['alamat'] ?? '') ?: '................................................................'));
        $parentPhone   = SecurityHelper::escape(($inst['parent_phone'] ?? '') ?: (($bill['parent_phone'] ?? '') ?: '................................................................'));
        $studentName   = SecurityHelper::escape($bill['student_name'] ?? '-');
        $studentNis    = SecurityHelper::escape($bill['nis'] ?? '-');
        $className     = SecurityHelper::escape(($inst['student_class'] ?? '') ?: (($bill['class_name'] ?? '') ?: '................................'));
        $unitName      = SecurityHelper::escape($bill['unit_name'] ?? '-');

        $totalNominal  = (float)($bill['amount'] ?? ($inst['total_debt'] ?? 0));
        $totalFmt      = number_format($totalNominal, 0, ',', '.');
        $totalTerbilang = ucfirst($this->terbilang($totalNominal));
        $todayIndo     = SecurityHelper::formatTanggalIndo(date('Y-m-d'));

        $baNumber = !empty($inst['agreement_number'])
            ? str_replace('FR8001D', 'BA-KEU/AIS', $inst['agreement_number'])
            : (sprintf('%03d', $billId) . '/BA-KEU/AIS/' . date('m/Y'));

        $checkLunas = ($inst && $inst['scheme_type'] === 'lunas_tanggal') ? '[ v ]' : '[ &nbsp; ]';
        $checkCicil = ($inst && $inst['scheme_type'] !== 'lunas_tanggal') ? '[ v ]' : '[ &nbsp; ]';
        $lunasDateStr = ($inst && !empty($inst['scheme_lunas_date']))
            ? SecurityHelper::formatTanggalIndo($inst['scheme_lunas_date'])
            : '........................................................';

        // Bangun daftar Angsuran I..IV bila kesepakatan cicilan sudah ada
        $angsuranLines = '';
        $numAngsuran = 4;
        if ($inst && $inst['scheme_type'] === 'cicil_bulan' && (int)$inst['scheme_months'] > 0) {
            $numAngsuran = min(12, max(2, (int)$inst['scheme_months']));
            $perBulan = round($totalNominal / $numAngsuran);
            $startTs = !empty($inst['scheme_start_date']) ? strtotime($inst['scheme_start_date']) : time();
            $roman = ['I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
            for ($i = 0; $i < $numAngsuran; $i++) {
                $tgl = SecurityHelper::formatTanggalIndo(date('Y-m-d', strtotime("+{$i} month", $startTs)));
                $nom = number_format($perBulan, 0, ',', '.');
                $rom = $roman[$i] ?? ($i + 1);
                $angsuranLines .= "<tr><td style='width: 140px; padding: 3px 0;'>" . ($i + 1) . ". Angsuran {$rom}</td><td>: Rp <strong>{$nom}</strong> &nbsp;&nbsp; pada tanggal <strong>{$tgl}</strong></td></tr>";
            }
        } else {
            $roman = ['I','II','III','IV'];
            for ($i = 0; $i < 4; $i++) {
                $angsuranLines .= "<tr><td style='width: 130px; padding: 4px 0;'>" . ($i + 1) . ". Angsuran {$roman[$i]}</td><td>: Rp ........................................ &nbsp;&nbsp; pada tanggal ........................................................</td></tr>";
            }
        }

        $schoolSignerTitle = $isYayasan ? 'Deputi Pendidikan' : 'Kepala Sekolah';
        $schoolSignerName  = $isYayasan ? SecurityHelper::escape($deputiInfo['nama']) : SecurityHelper::escape($kepsekInfo['nama']);

        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Berita Acara Kesepakatan &amp; Surat Pernyataan - FR 8001D</title>
            <style>
                @page { margin: 14mm 20mm 16mm 20mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.48; color: #111; }
                .doc-title { text-align: center; font-weight: bold; font-size: 12pt; text-transform: uppercase; margin-top: 6px; margin-bottom: 4px; }
                .doc-number { text-align: center; font-size: 11pt; margin-bottom: 16px; }
                .section-title { font-weight: bold; margin-top: 12px; margin-bottom: 8px; }
                .id-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                .id-table td { padding: 3px 2px; vertical-align: top; }
                .sig-grid { width: 100%; border-collapse: collapse; margin-top: 18px; page-break-inside: avoid; }
                .sig-grid th, .sig-grid td { border: 1px solid #94a3b8; padding: 10px 14px; width: 50%; vertical-align: top; text-align: left; }
                .sig-grid th { background: #f8fafc; font-weight: bold; }
                .page-break { page-break-before: always; }
            </style>
        </head>
        <body>
            <!-- ==================== LEMBAR 1: BERITA ACARA KESEPAKATAN ==================== -->
            {$kop}

            <div class='doc-title'>BERITA ACARA KESEPAKATAN PEMBAYARAN TUNGGAKAN BIAYA SEKOLAH</div>
            <div class='doc-number'>Nomor: <strong>{$baNumber}</strong></div>

            <div style='text-align: justify; margin-bottom: 10px;'>
                Pada hari ini, ............................. tanggal ......... bulan ..................... tahun ...................., telah dilaksanakan pertemuan antara pihak Sekolah <strong>Al-Kahfi Islamic School (AIS) &ndash; {$unitName}</strong> dengan Orang Tua / Wali Siswa:
            </div>

            <div class='section-title'>I. IDENTITAS ORANG TUA / WALI SISWA</div>
            <table class='id-table' style='margin-left: 14px;'>
                <tr><td width='28%'>&bull; Nama</td><td width='2%'>:</td><td><strong>{$parentName}</strong></td></tr>
                <tr><td>&bull; Alamat / Tempat Tinggal</td><td>:</td><td>{$parentAddress}</td></tr>
                <tr><td>&bull; No. Telepon / HP</td><td>:</td><td>{$parentPhone}</td></tr>
                <tr><td>&bull; Orang Tua / Wali dari</td><td>:</td><td><strong>{$studentName}</strong> (NIS: {$studentNis} &bull; Kelas: {$className})</td></tr>
            </table>

            <div class='section-title'>II. POKOK BAHASAN &amp; KESEPAKATAN</div>
            <div style='text-align: justify; margin-bottom: 10px;'>
                Berdasarkan catatan Bagian Keuangan sekolah, terdapat total tunggakan biaya pendidikan siswa sebesar <strong>Rp {$totalFmt}</strong> (<em>{$totalTerbilang}</em>) per tanggal <strong>{$todayIndo}</strong>.<br>
                Dari hasil pertemuan tersebut, pihak Orang Tua / Wali Siswa menyatakan <strong>bersedia dan berkomitmen</strong> untuk menyelesaikan kewajiban pembayaran tersebut dengan skema:
            </div>

            <div style='margin-left: 14px; margin-bottom: 16px; line-height: 1.8;'>
                <div>{$checkLunas} <strong>Lunas Sekaligus</strong> pada tanggal: <strong>{$lunasDateStr}</strong></div>
                <div>{$checkCicil} <strong>Dicicil</strong> dengan rincian skema terlampir pada Surat Pernyataan Komitmen Pembayaran.</div>
            </div>

            <div style='text-align: justify; margin-bottom: 14px;'>
                Demikian Berita Acara ini dibuat dengan sebenarnya untuk dipergunakan sebagaimana mestinya dan ditindaklanjuti oleh Bagian Keuangan AIS serta dilaporkan kepada Yayasan Islam Al-Kahfi Batam.
            </div>

            <div style='margin-bottom: 8px;'>Batam, {$todayIndo}</div>

            <table class='sig-grid'>
                <tr>
                    <th>Pihak Orang Tua / Wali</th>
                    <th>Perwakilan Sekolah</th>
                </tr>
                <tr>
                    <td style='height: 75px;'></td>
                    <td style='height: 75px;'></td>
                </tr>
                <tr>
                    <td>( <strong>{$parentName}</strong> )</td>
                    <td><strong>{$schoolSignerTitle}</strong><br>{$schoolSignerName}</td>
                </tr>
            </table>

            <!-- ==================== LEMBAR 2: SURAT PERNYATAAN KOMITMEN ==================== -->
            <div class='page-break'></div>
            {$kop}

            <div class='doc-title' style='margin-bottom: 14px;'>SURAT PERNYATAAN KOMITMEN PEMBAYARAN TUNGGAKAN BIAYA SEKOLAH</div>

            <div style='margin-bottom: 8px;'>Saya yang bertanda tangan di bawah ini:</div>
            <table class='id-table' style='margin-left: 14px;'>
                <tr><td width='28%'>&bull; Nama Lengkap</td><td width='2%'>:</td><td><strong>{$parentName}</strong></td></tr>
                <tr><td>&bull; No. KTP / NIK</td><td>:</td><td>{$parentKtp}</td></tr>
                <tr><td>&bull; Alamat Domisili</td><td>:</td><td>{$parentAddress}</td></tr>
                <tr><td>&bull; No. Telepon / HP</td><td>:</td><td>{$parentPhone}</td></tr>
            </table>

            <div style='margin-top: 10px; margin-bottom: 8px;'>Merupakan Orang Tua / Wali dari siswa:</div>
            <table class='id-table' style='margin-left: 14px;'>
                <tr><td width='28%'>&bull; Nama Siswa</td><td width='2%'>:</td><td><strong>{$studentName}</strong> (NIS: {$studentNis})</td></tr>
                <tr><td>&bull; Kelas / Jenjang</td><td>:</td><td>{$className} / {$unitName}</td></tr>
            </table>

            <div style='text-align: justify; margin-top: 10px; margin-bottom: 12px;'>
                Dengan ini menyatakan dengan sadar dan tanpa paksaan dari pihak manapun, bahwa saya bertekad kuat untuk menyelesaikan seluruh tunggakan biaya sekolah anak saya sebesar <strong>Rp {$totalFmt}</strong> (<em>{$totalTerbilang}</em>) dengan ketentuan/skema pembayaran sebagai berikut:
            </div>

            <div style='font-weight: bold; margin-bottom: 8px;'>Opsi Pembayaran (Pilih salah satu):</div>
            <div style='margin-left: 14px; margin-bottom: 12px;'>
                <div style='margin-bottom: 10px;'>
                    <strong>{$checkLunas} A. Pembayaran Lunas</strong><br>
                    <span style='margin-left: 26px; display: inline-block;'>Akan dilunasi seluruhnya paling lambat pada tanggal: <strong>{$lunasDateStr}</strong></span>
                </div>
                <div>
                    <strong>{$checkCicil} B. Pembayaran Dicicil</strong><br>
                    <span style='margin-left: 26px; display: inline-block; margin-bottom: 4px;'>Akan diangsur dengan skema jadwal sebagai berikut:</span>
                    <table style='width: 100%; border-collapse: collapse; margin-left: 26px;'>
                        {$angsuranLines}
                    </table>
                </div>
            </div>

            <div style='text-align: justify; margin-top: 12px; margin-bottom: 16px;'>
                Demikian surat pernyataan komitmen ini saya buat dengan sebenarnya. Apabila saya lalai atau tidak memenuhi komitmen di atas, saya bersedia menerima ketentuan dan kebijakan administratif yang berlaku di Sekolah Al-Kahfi Islamic School.<br>
                Atas bantuan, perhatian, dan kerja sama yang diberikan, saya ucapkan <em>Jazakumullahu Khayran</em>.
            </div>

            <div style='margin-top: 8px;'>
                Batam, {$todayIndo}<br>
                Yang Membuat Pernyataan,<br>
                <em>Orang Tua / Wali Siswa</em><br><br><br><br>
                ( <strong>{$parentName}</strong> )
            </div>
        </body>
        </html>";
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
        self::ensureSignedColumns();
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();

        $signedFiles = $this->handleSignedUploads($billId, 'kepala_sekolah');
        $setSigned = "";
        if (!empty($signedFiles)) {
            $setParts = [];
            foreach ($signedFiles as $col => $val) {
                $setParts[] = "$col = " . Database::pdo()->quote($val);
            }
            $setParts[] = "signed_uploaded_at = NOW()";
            $setParts[] = "signed_uploaded_by_level = 'kepala_sekolah'";
            $setSigned = ", " . implode(', ', $setParts);
        }

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = 'selesai_kepsek', 
                 pipeline_resolved_at = NOW(), 
                 pipeline_resolved_level = 'kepala_sekolah'
                 {$setSigned}
             WHERE id = ? AND unit_id = ?",
            [$billId, $unitId]
        );

        AuditLog::log('MARK_RESOLVED_KEPSEK', 'bills', $billId, null, [
            'has_c' => !empty($signedFiles['signed_fr8001c_path']),
            'has_d' => !empty($signedFiles['signed_fr8001d_path'])
        ]);
        $this->setFlash('success', 'Penagihan telah ditandai SELESAI di tingkat Kepala Sekolah' . (!empty($signedFiles) ? ' beserta berkas TTD tersimpan.' : '.'));
        $this->redirect(Auth::baseUrl() . '/tagihan/approval');
    }

    public function eskalasiDeputi(): void {
        $this->requireRole('kepala_sekolah');
        self::ensureSignedColumns();
        $billId = (int)($_POST['bill_id'] ?? 0);
        $unitId = Auth::unitId();
        $notes = trim($_POST['escalation_notes'] ?? 'Orang tua tidak juga datang memenuhi panggilan Kepala Sekolah. Dieskalasi ke Deputi Direktur Pendidikan Yayasan.');

        $signedFiles = $this->handleSignedUploads($billId, 'kepala_sekolah');
        $setSigned = "";
        if (!empty($signedFiles)) {
            $setParts = [];
            foreach ($signedFiles as $col => $val) {
                $setParts[] = "$col = " . Database::pdo()->quote($val);
            }
            $setParts[] = "signed_uploaded_at = NOW()";
            $setParts[] = "signed_uploaded_by_level = 'kepala_sekolah'";
            $setSigned = ", " . implode(', ', $setParts);
        }

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = 'escalated_deputi', 
                 notes = CONCAT(COALESCE(notes, ''), '\n[Eskalasi ke Yayasan/Deputi]: ', ?)
                 {$setSigned}
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

        AuditLog::log('ESCALATE_TO_DEPUTI', 'bills', $billId, null, [
            'notes' => $notes,
            'has_c' => !empty($signedFiles['signed_fr8001c_path']),
            'has_d' => !empty($signedFiles['signed_fr8001d_path'])
        ]);
        $this->setFlash('success', 'Tagihan berhasil dieskalasikan ke Deputi Direktur Pendidikan Yayasan' . (!empty($signedFiles) ? ' beserta berkas TTD tersimpan.' : '.'));
        $this->redirect(Auth::baseUrl() . '/tagihan/approval');
    }

    // =========================================================================
    // 5. ALUR YAYASAN: DEPUTI DIREKTUR PENDIDIKAN (FR 8001C KOP YAYASAN & D)
    // =========================================================================
    public function deputiEskalasiList(): void {
        $this->requireRole('deputi_pendidikan');
        self::ensureSignedColumns();

        $bills = Database::fetchAll(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik, c.name as class_name, u.name as unit_name, u.phone as unit_phone,
                    u.bank_name, u.bank_account, u.bank_holder, ks.nama as kepsek_name 
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN employees ks ON u.id = ks.unit_id AND (ks.position LIKE '%Kepala Sekolah%' OR ks.level_posisi LIKE '%Kepala Sekolah%' OR ks.level_posisi LIKE '%Kepsek%' OR ks.level_posisi LIKE '%Kanit%')
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
                    s.address as alamat, s.nik,
                    (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                     WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                     ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1) as class_name,
                    u.name as unit_name, u.phone as unit_phone, u.bank_name, 
                    u.bank_account, u.bank_holder
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }
        $this->assertUnitOwnership('bills', $billId, 'unit_id');

        $html = $this->generateFR8001CHtml($bill, true);
        ExportHelper::pdf($html, 'Surat_Panggilan_Yayasan_FR8001C_' . $bill['nis'], 'A4', 'portrait', true);
    }

    public function printSuratFR8001DDeputi(string $id): void {
        $this->requireRole(['deputi_pendidikan', 'tata_usaha']);
        $billId = (int)$id;

        $bill = Database::fetch(
            "SELECT b.*, s.nama as student_name, s.nis, COALESCE(NULLIF(s.father_name, ''), NULLIF(s.mother_name, ''), 'Wali Santri') as parent_name, s.parent_phone, s.parent_email, 
                    s.address as alamat, s.nik,
                    (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                     WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                     ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1) as class_name,
                    u.name as unit_name, u.kop_title, u.kop_subtitle, u.address, u.phone, u.email,
                    u.code, u.level, u.npsn, u.akreditasi, u.nomor_akreditasi, u.nomor_izin_operasional, u.logo_path
             FROM bills b 
             JOIN students s ON b.student_id = s.id 
             JOIN units u ON b.unit_id = u.id 
             WHERE b.id = ?",
            [$billId]
        );

        if (!$bill) {
            die('Data tagihan tidak ditemukan.');
        }
        $this->assertUnitOwnership('bills', $billId, 'unit_id');

        $html = $this->generateFR8001DHtml($bill, true);
        ExportHelper::pdf($html, 'Berita_Acara_dan_Pernyataan_Yayasan_FR8001D_' . $bill['nis'], 'A4', 'portrait', true);
    }

    public function markSelesaiDeputi(): void {
        $this->requireRole('deputi_pendidikan');
        self::ensureSignedColumns();
        $billId = (int)($_POST['bill_id'] ?? 0);

        $signedFiles = $this->handleSignedUploads($billId, 'deputi_pendidikan');
        $setSigned = "";
        if (!empty($signedFiles)) {
            $setParts = [];
            foreach ($signedFiles as $col => $val) {
                $setParts[] = "$col = " . Database::pdo()->quote($val);
            }
            $setParts[] = "signed_uploaded_at = NOW()";
            $setParts[] = "signed_uploaded_by_level = 'deputi_pendidikan'";
            $setSigned = ", " . implode(', ', $setParts);
        }

        Database::execute(
            "UPDATE bills 
             SET pipeline_stage = 'selesai_deputi', 
                 pipeline_resolved_at = NOW(), 
                 pipeline_resolved_level = 'deputi_pendidikan'
                 {$setSigned}
             WHERE id = ?",
            [$billId]
        );

        AuditLog::log('MARK_RESOLVED_DEPUTI', 'bills', $billId, null, [
            'has_c' => !empty($signedFiles['signed_fr8001c_path']),
            'has_d' => !empty($signedFiles['signed_fr8001d_path'])
        ]);
        $this->setFlash('success', 'Penagihan telah ditandai SELESAI di tingkat Deputi Direktur Pendidikan Yayasan' . (!empty($signedFiles) ? ' beserta berkas TTD tersimpan.' : '.'));
        $this->redirect(Auth::baseUrl() . '/tagihan/eskalasi-deputi');
    }

    // =========================================================================
    // 6. MENU BARU AKUN TU: BUKU & KARTU CICILAN SISWA (FR 8001D)
    // SIFAT: TIDAK BERUBAH WALAUPUN TAHUN AJARAN BERGANTI KECUALI LUNAS!
    // =========================================================================
    public function cicilanList(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan', 'keuangan_yayasan']);
        self::ensureSignedColumns();
        $unitId = Auth::unitId();

        // Kueri TIDAK difilter oleh academic_year_id!
        // Data tetap ada melintasi pergantian tahun ajaran sampai lunas.
        $installments = Database::fetchAll(
            "SELECT i.*, s.nama as student_name, s.nis, s.parent_email,
                    COALESCE(
                        (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                         WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = i.unit_id 
                         ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1),
                        i.student_class,
                        'Aktif'
                    ) as class_name,
                    u.name as unit_name, u_app.full_name as approver_name,
                    b.signed_fr8001c_path, b.signed_fr8001d_path
             FROM bill_installments i 
             JOIN students s ON i.student_id = s.id 
             JOIN units u ON i.unit_id = u.id 
             LEFT JOIN bills b ON i.bill_id = b.id
             LEFT JOIN users u_app ON i.approved_by_user_id = u_app.id 
             WHERE i.unit_id = ? 
             ORDER BY FIELD(i.status, 'Aktif', 'Lunas', 'Batal') ASC, class_name ASC, s.nama ASC",
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
        $amount = $this->parseRupiah($_POST['amount'] ?? '0');
        $paymentDate = trim($_POST['payment_date'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $paymentDate)) {
            $paymentDate = date('Y-m-d');
        }
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

        $currentRemaining = round((float)$installment['remaining_debt'], 2);
        if ($amount > ($currentRemaining + 1)) {
            $this->setFlash('warning', 'Nominal pembayaran melebihi sisa tunggakan (' . SecurityHelper::formatRupiah($currentRemaining) . ').');
            $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
            return;
        }

        // Pastikan pelunasan pas (termasuk selisih pembulatan < Rp 1) langsung mengunci status LUNAS
        if ($amount >= $currentRemaining) {
            $amount = $currentRemaining;
            $newRemaining = 0.0;
            $newStatus = 'Lunas';
        } else {
            $newRemaining = max(0.0, round($currentRemaining - $amount, 2));
            $newStatus = ($newRemaining <= 0.0) ? 'Lunas' : 'Aktif';
        }

        $receiptNumber = 'KW-CICIL/' . date('Ym') . '/' . sprintf('%04d', rand(100, 9999));

        Database::execute(
            "INSERT INTO bill_installment_payments 
                (installment_id, receipt_number, payment_date, amount, payment_method, notes, received_by_user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$installmentId, $receiptNumber, $paymentDate, $amount, $paymentMethod, $notes, $userId]
        );

        $newPaidTotal = (float)$installment['paid_total'] + $amount;

        Database::execute(
            "UPDATE bill_installments 
             SET paid_total = ?, 
                 remaining_debt = ?, 
                 status = ? 
             WHERE id = ?",
            [$newPaidTotal, $newRemaining, $newStatus, $installmentId]
        );

        // Jika lunas, perbarui status bill terkait (dan tagihan tertunggak siswa yang sudah tertutup)
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
            'remaining'      => $newRemaining,
            'status'         => $newStatus
        ]);

        $this->setFlash('success', "Pembayaran angsuran sebesar " . SecurityHelper::formatRupiah($amount) . " berhasil dicatat ({$receiptNumber})." . ($newStatus === 'Lunas' ? ' KESEPAKATAN TELAH LUNAS!' : ''));
        $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
    }

    private function generateKartuCicilanHtml(array $installment, array $payments): string {
        $kop = ($installment['approved_by_level'] === 'deputi_pendidikan')
            ? ExportHelper::renderKopYayasan()
            : ExportHelper::renderKop($installment);

        $tuEmp = $this->resolveUnitSigner((int)$installment['unit_id'], 'tata_usaha');
        $tuName = SecurityHelper::escape($tuEmp['nama']);

        $skemaDesc = '';
        if ($installment['scheme_type'] === 'lunas_tanggal') {
            $skemaDesc = 'Membayar lunas pada tanggal: <strong>' . SecurityHelper::formatTanggalIndo($installment['scheme_lunas_date']) . '</strong>';
        } elseif ($installment['scheme_type'] === 'cicil_bulan') {
            $skemaDesc = 'Dicicil selama <strong>' . $installment['scheme_months'] . ' bulan</strong> mulai tanggal <strong>' . SecurityHelper::formatTanggalIndo($installment['scheme_start_date']) . '</strong>';
        } elseif ($installment['scheme_type'] === 'cicil_nominal') {
            $skemaDesc = 'Dicicil minimal <strong>' . SecurityHelper::formatRupiah((float)$installment['scheme_min_monthly']) . '/bulan</strong> s.d tanggal <strong>' . SecurityHelper::formatTanggalIndo($installment['scheme_end_date']) . '</strong>';
        }

        $totalTunggakan = number_format((float)$installment['total_debt'], 0, ',', '.');
        $totalTerbayar  = number_format((float)$installment['paid_total'], 0, ',', '.');
        $sisaHutang     = number_format((float)$installment['remaining_debt'], 0, ',', '.');
        $totalBerjalan  = (float)$installment['paid_total'] + (float)$installment['remaining_debt'];

        $rowsHtml = '';
        if (empty($payments)) {
            $rowsHtml = "<tr><td colspan='6' align='center' style='padding: 18px; color: #64748b; font-style: italic;'>Belum ada rekaman angsuran pembayaran yang disetorkan.</td></tr>";
        } else {
            $runningBalance = $totalBerjalan;
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

        return "
        <html>
        <head>
            <meta charset='UTF-8'>
            <title>Buku &amp; Kartu Cicilan Pembayaran Tunggakan</title>
            <style>
                @page { margin: 15mm 18mm 18mm 18mm; }
                body { font-family: 'Times New Roman', Times, serif; font-size: 11pt; line-height: 1.4; color: #111; }
                .card-title { text-align: center; font-weight: bold; font-size: 13pt; text-transform: uppercase; margin-bottom: 4px; }
                .card-subtitle { text-align: center; font-size: 10pt; color: #475569; margin-bottom: 16px; }
                .meta-table { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
                .meta-table td { padding: 3px 4px; vertical-align: top; font-size: 10.5pt; }
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
                    <td>" . SecurityHelper::escape($installment['class_name'] ?? 'Aktif') . " &ndash; " . SecurityHelper::escape($installment['unit_name']) . "</td>
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

            <table style='width: 100%; border-collapse: collapse; margin-top: 6px; margin-bottom: 12px;'>
                <tr>
                    <td style='width: 33.3%; padding: 8px; background: #f1f5f9; border: 1px solid #cbd5e1; text-align: center;'>
                        <span style='font-size: 9pt; color: #64748b; text-transform: uppercase;'>Nominal Awal Perjanjian</span><br>
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
                        Mengetahui &amp; Menyetujui,<br>
                        <strong>Orang Tua / Wali Santri</strong><br><br><br><br>
                        ( <strong>" . SecurityHelper::escape($installment['parent_name']) . "</strong> )
                    </td>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "<br>
                        Bagian Keuangan / Tata Usaha,<br>
                        <strong>" . SecurityHelper::escape($installment['unit_name']) . "</strong><br><br><br><br>
                        ( <strong>{$tuName}</strong> )
                    </td>
                </tr>
            </table>
        </body>
        </html>";
    }

    public function printKartuCicilanPdf(string $id): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan', 'orang_tua']);
        $installmentId = (int)$id;

        $installment = Database::fetch(
            "SELECT i.*, s.nama as student_name, s.nis, s.parent_email,
                    COALESCE(
                        (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                         WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = i.unit_id 
                         ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1),
                        i.student_class,
                        'Aktif'
                    ) as class_name,
                    u.name as unit_name, u.kop_title, u.kop_subtitle, u.address, u.phone, u.email as unit_email,
                    u.code, u.level, u.npsn, u.akreditasi, u.nomor_akreditasi, u.nomor_izin_operasional, u.logo_path,
                    u_app.full_name as approver_name 
             FROM bill_installments i 
             JOIN students s ON i.student_id = s.id 
             JOIN units u ON i.unit_id = u.id 
             LEFT JOIN users u_app ON i.approved_by_user_id = u_app.id 
             WHERE i.id = ?",
            [$installmentId]
        );

        if (!$installment) {
            die('Data cicilan tidak ditemukan.');
        }
        $this->assertStudentAccess((int)$installment['student_id']);

        $payments = Database::fetchAll(
            "SELECT p.*, u.full_name as receiver_name 
             FROM bill_installment_payments p 
             JOIN users u ON p.received_by_user_id = u.id 
             WHERE p.installment_id = ? 
             ORDER BY p.payment_date ASC, p.id ASC",
            [$installmentId]
        );

        $html = $this->generateKartuCicilanHtml($installment, $payments);
        ExportHelper::pdf($html, 'Buku_Kartu_Cicilan_' . $installment['nis'] . '_' . date('Ymd'), 'A4', 'portrait', true);
    }

    public function sendKartuCicilanEmail(): void {
        $this->requireRole('tata_usaha');
        $installmentId = (int)($_POST['installment_id'] ?? 0);
        $unitId = Auth::unitId();

        $installment = Database::fetch(
            "SELECT i.*, s.nama as student_name, s.nis, s.parent_email,
                    COALESCE(
                        (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                         WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = i.unit_id 
                         ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1),
                        i.student_class,
                        'Aktif'
                    ) as class_name,
                    u.name as unit_name, u.kop_title, u.kop_subtitle, u.address, u.phone, u.email as unit_email,
                    u.code, u.level, u.npsn, u.akreditasi, u.nomor_akreditasi, u.nomor_izin_operasional, u.logo_path
             FROM bill_installments i 
             JOIN students s ON i.student_id = s.id 
             JOIN units u ON i.unit_id = u.id 
             WHERE i.id = ? AND i.unit_id = ?",
            [$installmentId, $unitId]
        );

        if (!$installment || empty(trim($installment['parent_email'] ?? ''))) {
            $this->setFlash('warning', 'Data cicilan atau email orang tua tidak tersedia.');
            $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
            return;
        }

        $payments = Database::fetchAll(
            "SELECT p.*, u.full_name as receiver_name 
             FROM bill_installment_payments p 
             JOIN users u ON p.received_by_user_id = u.id 
             WHERE p.installment_id = ? 
             ORDER BY p.payment_date ASC, p.id ASC",
            [$installmentId]
        );

        $smtp = $this->getSmtpConfig($unitId);
        if ($smtp) {
            try {
                @ini_set('memory_limit', '512M');
                @set_time_limit(120);

                $mail = new PHPMailer(true);
                $this->configureMailer($mail, $smtp);

                $fromEmail = !empty($smtp['from_email']) ? trim($smtp['from_email']) : trim($smtp['username']);
                $fromName  = !empty($smtp['from_name']) ? trim($smtp['from_name']) : ($installment['unit_name'] ?? 'Tata Usaha');
                $parentEmail = trim($installment['parent_email']);
                $mail->setFrom($fromEmail, $fromName);
                $mail->addAddress($parentEmail, $installment['student_name']);
                $mail->isHTML(true);
                $mail->Subject = "[Kartu Cicilan Pembayaran SPP] " . $installment['student_name'] . " - " . $installment['unit_name'];
                $mail->Body = "
                    <div style='font-family: Arial, sans-serif; line-height: 1.6; max-width: 600px; padding: 20px; border: 1px solid #e2e8f0; border-radius: 8px;'>
                        <h3 style='color: #0f766e;'>Buku &amp; Kartu Kontrol Cicilan Tunggakan</h3>
                        <p>Yth. Orang Tua dari ananda <strong>" . htmlspecialchars($installment['student_name']) . "</strong>,</p>
                        <p>Berikut ringkasan status cicilan pembayaran sekolah berdasarkan kesepakatan <strong>" . htmlspecialchars($installment['agreement_number']) . "</strong>:</p>
                        <ul>
                            <li>Nominal Awal Perjanjian: <strong>" . SecurityHelper::formatRupiah((float)$installment['total_debt']) . "</strong></li>
                            <li>Total Telah Dibayar: <strong style='color: #059669;'>" . SecurityHelper::formatRupiah((float)$installment['paid_total']) . "</strong></li>
                            <li>Sisa Saldo Tunggakan: <strong style='color: #b91c1c;'>" . SecurityHelper::formatRupiah((float)$installment['remaining_debt']) . "</strong></li>
                            <li>Status: <strong>" . htmlspecialchars($installment['status']) . "</strong></li>
                        </ul>
                        <p>Dokumen resmi <strong>Kartu Buku Kontrol Cicilan (PDF)</strong> telah kami lampirkan pada email ini.</p>
                        <p><em>Jazakumullah Khairan Katsiran.</em></p>
                    </div>";

                // Lampirkan PDF Buku & Kartu Cicilan
                try {
                    $htmlPdf = $this->generateKartuCicilanHtml($installment, $payments);
                    $pdfBytes = ExportHelper::getPdfBytes($htmlPdf, 'A4', 'portrait');
                    if (!empty($pdfBytes)) {
                        $cleanNis = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $installment['nis']);
                        $mail->addStringAttachment($pdfBytes, 'Kartu_Cicilan_' . $cleanNis . '_' . date('Ymd') . '.pdf', 'base64', 'application/pdf');
                    }
                } catch (\Throwable $pdfEx) {
                    error_log('Gagal membuat lampiran PDF Kartu Cicilan: ' . $pdfEx->getMessage());
                }

                $mail->send();
                $this->setFlash('success', 'Informasi & lampiran PDF Kartu Cicilan berhasil dikirim ke email orang tua (' . htmlspecialchars($parentEmail) . ').');
            } catch (\Throwable $e) {
                $err = !empty($mail->ErrorInfo) ? $mail->ErrorInfo : $e->getMessage();
                $this->setFlash('danger', 'Gagal mengirim email: ' . htmlspecialchars($err));
            }
        } else {
            $this->setFlash('info', 'Konfigurasi SMTP belum aktif di unit ini.');
        }

        $this->redirect(Auth::baseUrl() . '/tagihan/cicilan');
    }

    /**
     * Ekspor Backup Offline data Tagihan FR 8001A, Buku Cicilan FR 8001D, dan Histori Pembayaran ke Excel Multi-Sheet (.xlsx).
     */
    public function exportOfflineBackup(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $isAllUnits = ($unitId <= 0);
        $unitFilterSql = $isAllUnits ? '' : ' AND b.unit_id = ? ';
        $billParams = $isAllUnits ? [$taId] : [$taId, $unitId];

        $bills = Database::fetchAll(
            "SELECT b.*, u.name as unit_name, s.nis, s.nama as student_name, s.gender,
                    (SELECT c2.name FROM class_students cs2 JOIN classes c2 ON cs2.class_id = c2.id 
                     WHERE cs2.student_id = s.id AND cs2.status = 'Aktif' AND c2.unit_id = b.unit_id 
                     ORDER BY cs2.academic_year_id DESC, cs2.id DESC LIMIT 1) as class_name
             FROM bills b
             JOIN students s ON b.student_id = s.id
             JOIN units u ON b.unit_id = u.id
             WHERE b.academic_year_id = ? {$unitFilterSql}
             ORDER BY u.id ASC, class_name ASC, s.nama ASC",
            $billParams
        );

        $instSql = $isAllUnits ? '' : ' WHERE i.unit_id = ? ';
        $instParams = $isAllUnits ? [] : [$unitId];
        $installments = Database::fetchAll(
            "SELECT i.*, u.name as unit_name, s.nis, s.nama as student_name
             FROM bill_installments i
             JOIN students s ON i.student_id = s.id
             JOIN units u ON i.unit_id = u.id
             {$instSql}
             ORDER BY u.id ASC, i.status ASC, s.nama ASC",
            $instParams
        );

        $paySql = $isAllUnits ? '' : ' WHERE i.unit_id = ? ';
        $payments = Database::fetchAll(
            "SELECT p.*, i.agreement_number, s.nis, s.nama as student_name, u.name as unit_name, usr.full_name as receiver_name
             FROM bill_installment_payments p
             JOIN bill_installments i ON p.installment_id = i.id
             JOIN students s ON i.student_id = s.id
             JOIN units u ON i.unit_id = u.id
             JOIN users usr ON p.received_by_user_id = usr.id
             {$paySql}
             ORDER BY p.payment_date DESC, p.id DESC",
            $instParams
        );

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Sheet 1: Tagihan FR 8001A
        $s1 = $spreadsheet->getActiveSheet();
        $s1->setTitle('Tagihan FR8001A');
        $h1 = ['No', 'Unit', 'Kelas', 'NIS', 'Nama Siswa', 'L/P', 'Buku Paket', 'Kegiatan', 'Komite', 'Mutu', 'SPP', 'Uang Ujian', 'Tunggakan Lalu', 'Total Tagihan', 'Status', 'Tahap Alur', 'Catatan'];
        foreach ($h1 as $cIdx => $h) {
            $s1->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . '1', SecurityHelper::cleanFormula($h));
        }
        $rowNum = 2;
        foreach ($bills as $i => $b) {
            $det = json_decode($b['items_detail'] ?? '{}', true) ?: [];
            $vals = [
                $i + 1, $b['unit_name'], $b['class_name'] ?? '-', $b['nis'], $b['student_name'], $b['gender'] ?? 'L',
                (float)($det['buku_paket'] ?? 0), (float)($det['kegiatan_siswa'] ?? 0), (float)($det['komite'] ?? 0),
                (float)($det['peningkatan_mutu'] ?? 0), (float)($det['spp'] ?? 0), (float)($det['uang_ujian'] ?? 0),
                (float)($det['tunggakan_lalu'] ?? 0), (float)$b['amount'], $b['status'], $b['pipeline_stage'], $b['notes'] ?? ''
            ];
            foreach ($vals as $cIdx => $v) {
                $s1->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $rowNum, is_numeric($v) ? $v : SecurityHelper::cleanFormula((string)$v));
            }
            $rowNum++;
        }

        // Sheet 2: Buku Cicilan FR 8001D
        $s2 = $spreadsheet->createSheet();
        $s2->setTitle('Buku Cicilan FR8001D');
        $h2 = ['No', 'Unit', 'No Kesepakatan', 'NIS', 'Nama Siswa', 'Orang Tua', 'No KTP', 'HP', 'Nominal Anchor Awal', 'Total Dibayar', 'Sisa Tunggakan', 'Status', 'Tgl Akad'];
        foreach ($h2 as $cIdx => $h) {
            $s2->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . '1', SecurityHelper::cleanFormula($h));
        }
        $rowNum = 2;
        foreach ($installments as $i => $ins) {
            $vals = [
                $i + 1, $ins['unit_name'], $ins['agreement_number'], $ins['nis'], $ins['student_name'],
                $ins['parent_name'], $ins['parent_ktp'] ?? '-', $ins['parent_phone'] ?? '-',
                (float)$ins['total_debt'], (float)$ins['paid_total'], (float)$ins['remaining_debt'],
                $ins['status'], $ins['agreement_date']
            ];
            foreach ($vals as $cIdx => $v) {
                $s2->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $rowNum, is_numeric($v) ? $v : SecurityHelper::cleanFormula((string)$v));
            }
            $rowNum++;
        }

        // Sheet 3: Histori Angsuran
        $s3 = $spreadsheet->createSheet();
        $s3->setTitle('Histori Angsuran');
        $h3 = ['No', 'Unit', 'No Kuitansi', 'No Kesepakatan', 'NIS', 'Nama Siswa', 'Tanggal Bayar', 'Nominal Bayar', 'Metode', 'Penerima TU', 'Catatan'];
        foreach ($h3 as $cIdx => $h) {
            $s3->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . '1', SecurityHelper::cleanFormula($h));
        }
        $rowNum = 2;
        foreach ($payments as $i => $p) {
            $vals = [
                $i + 1, $p['unit_name'], $p['receipt_number'], $p['agreement_number'], $p['nis'], $p['student_name'],
                $p['payment_date'], (float)$p['amount'], $p['payment_method'], $p['receiver_name'], $p['notes'] ?? ''
            ];
            foreach ($vals as $cIdx => $v) {
                $s3->setCellValue(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($cIdx + 1) . $rowNum, is_numeric($v) ? $v : SecurityHelper::cleanFormula((string)$v));
            }
            $rowNum++;
        }

        $spreadsheet->setActiveSheetIndex(0);
        if (ob_get_length()) ob_end_clean();
        $fname = 'Backup_Offline_Keuangan_Tagihan_' . date('Ymd_His') . '.xlsx';
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $fname . '"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // =========================================================================
    // 7. SURAT TAGIHAN RESMI LAMA (BACKWARD COMPATIBILITY)
    // =========================================================================
    public function printSuratTagihan(string $id): void {
        $this->printSuratFR8001B($id);
    }
}

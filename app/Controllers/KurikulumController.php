<?php
// app/Controllers/KurikulumController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;
use Dompdf\Dompdf;
use Dompdf\Options;

class KurikulumController extends Controller {

    /**
     * Tampilan Formulir Pengaturan & Pratinjau Dokumen Struktur Kurikulum (FR - 9023a)
     */
    public function struktur(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'operator_sekolah', 'deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan']);
        $taId = Auth::academicYearId();
        $role = Auth::activeRoleName();

        // Resolusi Unit berdasarkan wewenang
        $isPimpinan = in_array($role, ['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan'], true);
        if ($isPimpinan) {
            if ($role === 'deputi_pendidikan') {
                $deputiUnitIds = DeputiController::getDeputiUnitIds(Auth::id());
                $inClause = !empty($deputiUnitIds) ? implode(',', array_map('intval', $deputiUnitIds)) : '0';
                $availableUnits = Database::fetchAll("SELECT * FROM units WHERE id IN ($inClause) ORDER BY id ASC");
            } else {
                $availableUnits = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
            }
            $unitId = (int)($_GET['unit_id'] ?? ($availableUnits[0]['id'] ?? 1));
            if ($role === 'deputi_pendidikan' && !empty($deputiUnitIds) && !in_array($unitId, $deputiUnitIds, true)) {
                $unitId = (int)$deputiUnitIds[0];
            }
        } else {
            $unitId = Auth::unitId();
            $availableUnits = Database::fetchAll("SELECT * FROM units WHERE id = ?", [$unitId]);
        }

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!$unit) {
            $this->setFlash('danger', 'Unit sekolah tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/dashboard');
            return;
        }

        // Inisialisasi default dari Lampiran Approved jika belum pernah disimpan
        self::ensureStrukturInitialized($unitId, $taId);

        $meta = Database::fetch("SELECT * FROM kurikulum_struktur_meta WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        $items = Database::fetchAll("SELECT * FROM kurikulum_struktur_items WHERE unit_id = ? AND academic_year_id = ? ORDER BY order_num ASC, id ASC", [$unitId, $taId]);
        
        // Ambil mata pelajaran yang ada di master subjects unit ini
        $masterSubjects = Database::fetchAll("SELECT * FROM subjects WHERE unit_id = ? ORDER BY type ASC, name ASC", [$unitId]);

        // Cari info Kepala Sekolah & Deputi
        $ksEmp = Database::fetch("
            SELECT e.* FROM employees e 
            WHERE e.unit_id = ? AND e.is_deleted = 0 
              AND (e.level_posisi LIKE '%Kepala Sekolah%' OR e.position LIKE '%Kepala Sekolah%' OR e.level_posisi LIKE '%Kepsek%')
            LIMIT 1
        ", [$unitId]);

        $deputiUser = null;
        $depMapping = Database::fetch("SELECT user_id FROM deputi_units WHERE unit_id = ? LIMIT 1", [$unitId]);
        if ($depMapping && !empty($depMapping['user_id'])) {
            $deputiUser = Database::fetch("SELECT id, full_name, username, signature_path FROM users WHERE id = ?", [$depMapping['user_id']]);
        }
        if (!$deputiUser) {
            $deputiUser = Database::fetch("
                SELECT u.id, u.full_name, u.username, u.signature_path 
                FROM users u 
                JOIN user_roles ur ON u.id = ur.user_id 
                JOIN roles r ON ur.role_id = r.id 
                WHERE r.name = 'deputi_pendidikan' AND u.is_active = 1 LIMIT 1
            ");
        }

        $this->view('kurikulum/struktur', [
            'pageTitle'       => 'Struktur Kurikulum Unit ' . ($unit['code'] ?? $unit['name']) . ' (FR – 9023a)',
            'unit'            => $unit,
            'unitId'          => $unitId,
            'availableUnits'  => $availableUnits,
            'isPimpinan'      => $isPimpinan,
            'meta'            => $meta,
            'items'           => $items,
            'masterSubjects'  => $masterSubjects,
            'ksEmp'           => $ksEmp,
            'deputiUser'      => $deputiUser,
            'canEdit'         => in_array($role, ['kepala_sekolah', 'waka_kurikulum', 'operator_yayasan', 'operator_sekolah'], true)
        ]);
    }

    /**
     * Simpan Pengaturan Struktur Kurikulum
     */
    public function saveStruktur(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'operator_yayasan', 'operator_sekolah']);
        $taId = Auth::academicYearId();
        $role = Auth::activeRoleName();

        $unitId = in_array($role, ['operator_yayasan'], true) ? (int)($_POST['unit_id'] ?? 0) : Auth::unitId();
        if ($unitId <= 0) {
            $unitId = Auth::unitId();
        }

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!$unit) {
            $this->setFlash('danger', 'Unit tidak valid.');
            $this->redirect(Auth::baseUrl() . '/kurikulum/struktur');
            return;
        }

        $docNumber = trim($_POST['doc_number'] ?? 'FR – 9023a');
        $revision = trim($_POST['revision'] ?? '-');
        $notes = trim($_POST['notes'] ?? '');
        $kepsekNama = trim($_POST['kepsek_nama'] ?? '');
        $kepsekTelp = trim($_POST['kepsek_telp'] ?? '');
        $signDateKepsek = !empty($_POST['sign_date_kepsek']) ? $_POST['sign_date_kepsek'] : date('Y-m-d');
        $deputiNama = trim($_POST['deputi_nama'] ?? 'Yona Melinda, B.Eng., M.Ed');
        $signDateDeputi = !empty($_POST['sign_date_deputi']) ? $_POST['sign_date_deputi'] : date('Y-m-d');

        // Simpan / Update Meta Header
        Database::execute("
            INSERT INTO kurikulum_struktur_meta 
            (unit_id, academic_year_id, doc_number, revision, notes, kepsek_nama, kepsek_telp, sign_date_kepsek, deputi_nama, sign_date_deputi, updated_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
            ON DUPLICATE KEY UPDATE 
                doc_number = VALUES(doc_number),
                revision = VALUES(revision),
                notes = VALUES(notes),
                kepsek_nama = VALUES(kepsek_nama),
                kepsek_telp = VALUES(kepsek_telp),
                sign_date_kepsek = VALUES(sign_date_kepsek),
                deputi_nama = VALUES(deputi_nama),
                sign_date_deputi = VALUES(sign_date_deputi),
                updated_at = NOW()
        ", [$unitId, $taId, $docNumber, $revision, $notes, $kepsekNama, $kepsekTelp, $signDateKepsek, $deputiNama, $signDateDeputi]);

        // Simpan Baris Item Struktur Kurikulum
        $itemIds    = $_POST['item_id'] ?? [];
        $kelompoks  = $_POST['kelompok'] ?? [];
        $itemNums   = $_POST['item_number'] ?? [];
        $namaMapels = $_POST['nama_mapel'] ?? [];
        $keterangans = $_POST['keterangan'] ?? [];
        $orderNums  = $_POST['order_num'] ?? [];

        // JP arrays
        $jp1  = $_POST['jp_1'] ?? [];
        $jp2  = $_POST['jp_2'] ?? [];
        $jp3  = $_POST['jp_3'] ?? [];
        $jp4  = $_POST['jp_4'] ?? [];
        $jp5  = $_POST['jp_5'] ?? [];
        $jp6  = $_POST['jp_6'] ?? [];
        $jp7  = $_POST['jp_7'] ?? [];
        $jp8  = $_POST['jp_8'] ?? [];
        $jp9  = $_POST['jp_9'] ?? [];
        $jp10 = $_POST['jp_10'] ?? [];
        $jp11 = $_POST['jp_11'] ?? [];
        $jp12 = $_POST['jp_12'] ?? [];

        $existingIds = [];
        $orderCounter = 1;

        foreach ($namaMapels as $idx => $nama) {
            $nama = trim($nama);
            if (empty($nama)) continue;

            $idVal     = (int)($itemIds[$idx] ?? 0);
            $kelompok  = trim($kelompoks[$idx] ?? 'Kelompok A');
            $itemNum   = trim($itemNums[$idx] ?? (string)$orderCounter);
            $ket       = trim($keterangans[$idx] ?? '');
            $order     = !empty($orderNums[$idx]) ? (int)$orderNums[$idx] : $orderCounter;

            $v_jp1  = trim($jp1[$idx] ?? '');
            $v_jp2  = trim($jp2[$idx] ?? '');
            $v_jp3  = trim($jp3[$idx] ?? '');
            $v_jp4  = trim($jp4[$idx] ?? '');
            $v_jp5  = trim($jp5[$idx] ?? '');
            $v_jp6  = trim($jp6[$idx] ?? '');
            $v_jp7  = trim($jp7[$idx] ?? '');
            $v_jp8  = trim($jp8[$idx] ?? '');
            $v_jp9  = trim($jp9[$idx] ?? '');
            $v_jp10 = trim($jp10[$idx] ?? '');
            $v_jp11 = trim($jp11[$idx] ?? '');
            $v_jp12 = trim($jp12[$idx] ?? '');

            $isSub = (strpos($itemNum, '.') !== false) ? 1 : 0;

            if ($idVal > 0) {
                Database::execute("
                    UPDATE kurikulum_struktur_items SET
                        item_number = ?, kelompok = ?, nama_mapel = ?, order_num = ?,
                        jp_1 = ?, jp_2 = ?, jp_3 = ?, jp_4 = ?, jp_5 = ?, jp_6 = ?,
                        jp_7 = ?, jp_8 = ?, jp_9 = ?, jp_10 = ?, jp_11 = ?, jp_12 = ?,
                        keterangan = ?, is_sub_item = ?, updated_at = NOW()
                    WHERE id = ? AND unit_id = ? AND academic_year_id = ?
                ", [
                    $itemNum, $kelompok, $nama, $order,
                    $v_jp1, $v_jp2, $v_jp3, $v_jp4, $v_jp5, $v_jp6,
                    $v_jp7, $v_jp8, $v_jp9, $v_jp10, $v_jp11, $v_jp12,
                    $ket, $isSub, $idVal, $unitId, $taId
                ]);
                $existingIds[] = $idVal;
            } else {
                Database::execute("
                    INSERT INTO kurikulum_struktur_items (
                        unit_id, academic_year_id, item_number, kelompok, nama_mapel, order_num,
                        jp_1, jp_2, jp_3, jp_4, jp_5, jp_6,
                        jp_7, jp_8, jp_9, jp_10, jp_11, jp_12,
                        keterangan, is_sub_item, created_at, updated_at
                    ) VALUES (
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, ?, ?, ?, ?,
                        ?, ?, NOW(), NOW()
                    )
                ", [
                    $unitId, $taId, $itemNum, $kelompok, $nama, $order,
                    $v_jp1, $v_jp2, $v_jp3, $v_jp4, $v_jp5, $v_jp6,
                    $v_jp7, $v_jp8, $v_jp9, $v_jp10, $v_jp11, $v_jp12,
                    $ket, $isSub
                ]);
                $existingIds[] = (int)Database::lastInsertId();
            }

            // Sinkronkan nama mapel ke tabel subjects unit jika belum ada
            $chkSub = Database::fetch("SELECT id FROM subjects WHERE unit_id = ? AND name = ?", [$unitId, $nama]);
            if (!$chkSub) {
                Database::execute("
                    INSERT INTO subjects (unit_id, code, name, type, created_at)
                    VALUES (?, ?, ?, 'Umum', NOW())
                ", [$unitId, 'MAPEL-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $nama), 0, 6)), $nama]);
            }

            $orderCounter++;
        }

        // Hapus baris yang dibuang di form
        if (!empty($existingIds)) {
            $inDel = implode(',', array_map('intval', $existingIds));
            Database::execute("DELETE FROM kurikulum_struktur_items WHERE unit_id = ? AND academic_year_id = ? AND id NOT IN ($inDel)", [$unitId, $taId]);
        }

        AuditLog::log('SAVE_STRUKTUR_KURIKULUM', 'kurikulum_struktur_meta', $unitId, null, [
            'unit' => $unit['name'],
            'total_items' => count($existingIds)
        ]);

        $this->setFlash('success', 'Struktur Kurikulum Unit ' . $unit['name'] . ' (FR – 9023a) berhasil disimpan.');
        $redirectUrl = Auth::baseUrl() . '/kurikulum/struktur';
        if (in_array($role, ['operator_yayasan', 'deputi_pendidikan', 'direktur_pendidikan'], true)) {
            $redirectUrl .= '?unit_id=' . $unitId;
        }
        $this->redirect($redirectUrl);
    }

    /**
     * Reset Struktur Kurikulum kembali ke standar approved lampiran
     */
    public function resetDefault(): void {
        $this->requireRole(['kepala_sekolah', 'operator_yayasan']);
        $taId = Auth::academicYearId();
        $role = Auth::activeRoleName();
        $unitId = in_array($role, ['operator_yayasan'], true) ? (int)($_POST['unit_id'] ?? 0) : Auth::unitId();
        if ($unitId <= 0) $unitId = Auth::unitId();

        Database::execute("DELETE FROM kurikulum_struktur_items WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        Database::execute("DELETE FROM kurikulum_struktur_meta WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);

        self::ensureStrukturInitialized($unitId, $taId);

        $this->setFlash('success', 'Struktur Kurikulum berhasil di-reset ke format standar resmi approved.');
        $redirectUrl = Auth::baseUrl() . '/kurikulum/struktur';
        if (in_array($role, ['operator_yayasan', 'deputi_pendidikan', 'direktur_pendidikan'], true)) {
            $redirectUrl .= '?unit_id=' . $unitId;
        }
        $this->redirect($redirectUrl);
    }

    /**
     * Download Berkas PDF Resmi Struktur Kurikulum (FR - 9023a)
     */
    public function pdfStruktur(): void {
        $this->requireAuth();
        $unitId = (int)($_GET['unit_id'] ?? Auth::unitId());
        $taId = Auth::academicYearId();

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!$unit) {
            die('Unit tidak ditemukan.');
        }

        self::ensureStrukturInitialized($unitId, $taId);
        $meta = Database::fetch("SELECT * FROM kurikulum_struktur_meta WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        $items = Database::fetchAll("SELECT * FROM kurikulum_struktur_items WHERE unit_id = ? AND academic_year_id = ? ORDER BY order_num ASC, id ASC", [$unitId, $taId]);
        $academicYear = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);

        // Logo Base64
        $logoBase64 = '';
        if (!empty($unit['logo_path'])) {
            $lPath = dirname(__DIR__, 2) . '/public/' . ltrim($unit['logo_path'], '/');
            if (file_exists($lPath)) {
                $ext = strtolower(pathinfo($lPath, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($lPath));
            }
        }

        // TTD Deputi Base64
        $deputiSigBase64 = '';
        $deputiUser = Database::fetch("SELECT * FROM users WHERE full_name LIKE '%Yona Melinda%' LIMIT 1");
        if ($deputiUser && !empty($deputiUser['signature_path'])) {
            $sPath = dirname(__DIR__, 2) . '/public/' . ltrim($deputiUser['signature_path'], '/');
            if (file_exists($sPath)) {
                $ext = strtolower(pathinfo($sPath, PATHINFO_EXTENSION));
                $mime = $ext === 'png' ? 'image/png' : 'image/jpeg';
                $deputiSigBase64 = 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($sPath));
            }
        }

        ob_start();
        include dirname(__DIR__, 2) . '/views/kurikulum/pdf_struktur.php';
        $html = ob_get_clean();

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'DejaVu Sans');

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '_', $unit['code'] ?: $unit['name']);
        $filename = "Struktur_Kurikulum_{$safeName}_FR9023a.pdf";

        $dompdf->stream($filename, ['Attachment' => false]);
        exit;
    }

    /**
     * Cetak Tampilan Web Struktur Kurikulum
     */
    public function printStruktur(): void {
        $this->requireAuth();
        $unitId = (int)($_GET['unit_id'] ?? Auth::unitId());
        $taId = Auth::academicYearId();

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!$unit) {
            die('Unit tidak ditemukan.');
        }

        self::ensureStrukturInitialized($unitId, $taId);
        $meta = Database::fetch("SELECT * FROM kurikulum_struktur_meta WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        $items = Database::fetchAll("SELECT * FROM kurikulum_struktur_items WHERE unit_id = ? AND academic_year_id = ? ORDER BY order_num ASC, id ASC", [$unitId, $taId]);
        $academicYear = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);

        $this->view('kurikulum/print_struktur', [
            'pageTitle'    => 'Cetak Struktur Kurikulum ' . $unit['name'],
            'unit'         => $unit,
            'meta'         => $meta,
            'items'        => $items,
            'academicYear' => $academicYear
        ], 'layouts/plain');
    }

    /**
     * Memastikan data Struktur Kurikulum terinisialisasi dari Dokumen Resmi Approved di D:\LAMPIRAN AKFI DEPUTI
     */
    public static function ensureStrukturInitialized(int $unitId, int $taId): void {
        $chk = Database::fetch("SELECT id FROM kurikulum_struktur_meta WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        if ($chk) {
            return;
        }

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!$unit) return;

        $level = strtoupper($unit['level'] ?? '');

        // 1. Inisialisasi Meta
        $docNo = 'FR – 9023a';
        $ksName = '';
        $ksTelp = '';
        $notes = '';
        $deputiName = 'Yona Melinda, B.Eng., M.Ed';
        $signDate = '2026-07-02';

        if (in_array($level, ['KB', 'TK'], true) || $unitId == 1 || $unitId == 2) {
            $ksName = 'SRI UNTARI, S.Pd.AUD';
            $ksTelp = '081372157751';
            $notes  = "1 JAM PELAJARAN = 15 MENIT\nAlokasi waktu pelaksanaan menyesuaikan jadwal pembiasaan harian santri.";
            $signDate = '2026-06-29';
        } elseif ($level === 'SD' || $unitId == 6) {
            $ksName = 'Putra Perdana Tanjung, S.Pd.,G.r.';
            $ksTelp = '085273570129';
            $notes  = "Pagi Hari jam 07.30-08.00 (1 JP) siswa sudah mulai masuk untuk pembiasaan Dzikir pagi, Muroja’ah dan drilling Bahasa.\nTotal JP tanpa memperhitungkan kelompok C (Kokurikuler) dan D (Pengembangan Diri).";
            $signDate = '2026-07-02';
        } elseif ($level === 'SMP' || $unitId == 7) {
            $ksName = 'Sumandri.,M.Pd.';
            $ksTelp = '+62 812-7612-5936';
            $notes  = "Alokasi waktu tatap muka per JP = 40 menit.\nProgram P5 dan Sinkronisasi Muatan KTSP dilaksanakan secara terintegrasi.";
            $signDate = '2026-07-04';
        } elseif ($level === 'SMA' || $unitId == 8) {
            $ksName = 'Sumandri, M.Pd';
            $ksTelp = '+62 812-7612-5936';
            $notes  = "Alokasi waktu tatap muka per JP = 45 menit.\nStruktur Kurikulum mencakup Fase E (Kelas 10) dan Fase F (Kelas 11 & 12 Pilihan MIPA/IPS serta Pembekalan UTBK).";
            $signDate = '2026-07-04';
        }

        Database::execute("
            INSERT INTO kurikulum_struktur_meta 
            (unit_id, academic_year_id, doc_number, revision, notes, kepsek_nama, kepsek_telp, sign_date_kepsek, deputi_nama, sign_date_deputi, status)
            VALUES (?, ?, ?, '-', ?, ?, ?, ?, ?, ?, 'Disetujui')
        ", [$unitId, $taId, $docNo, $notes, $ksName, $ksTelp, $signDate, $deputiName, $signDate]);

        // 2. Inisialisasi Items Spesifik per Level Satuan Pendidikan
        if (in_array($level, ['KB', 'TK'], true) || $unitId == 1 || $unitId == 2) {
            $kbtkItems = [
                ['num' => '1',  'kel' => 'Muatan Kurikulum', 'nama' => 'Pembiasaan (Physic Exercise)', 'jp1' => '7',  'jp2' => '7',  'ket' => 'Senin s/d Kamis = 45/30 menit/pertemuan (TK/KB)'],
                ['num' => '2',  'kel' => 'Muatan Kurikulum', 'nama' => 'Tahfidz – Tahsin - Baca Latin', 'jp1' => '12', 'jp2' => '16', 'ket' => 'Senin s/d Kamis = 30/15 menit / pertemuan (TK / KB)'],
                ['num' => '3',  'kel' => 'Muatan Kurikulum', 'nama' => 'Pembiasaan Shalat – Wudhu', 'jp1' => '4',  'jp2' => '6',  'ket' => 'Senin s/d Rabu = 15 mnt (TK); Rabu & Jum’at = 15 mnt (KB)'],
                ['num' => '4',  'kel' => 'Muatan Kurikulum', 'nama' => 'Pendidikan Aqidah/Keimanan/Akhlaq', 'jp1' => '1',  'jp2' => '2',  'ket' => 'Setiap Senin = 15/15 menit (TK/KB)'],
                ['num' => '5',  'kel' => 'Muatan Kurikulum', 'nama' => 'Fiqh Ibadah', 'jp1' => '1',  'jp2' => '1',  'ket' => 'Setiap Kamis = 15 Menit'],
                ['num' => '6',  'kel' => 'Muatan Kurikulum', 'nama' => 'Hadits-hadits pendek', 'jp1' => '1',  'jp2' => '2',  'ket' => '1x dalam seminggu 15/15 menit (TK/KB)'],
                ['num' => '7',  'kel' => 'Muatan Kurikulum', 'nama' => 'Do\'a Sehari-hari', 'jp1' => '1',  'jp2' => '2',  'ket' => '1x dalam seminggu 15/15 menit (TK/KB)'],
                ['num' => '8',  'kel' => 'Muatan Kurikulum', 'nama' => 'ARABIC', 'jp1' => '1',  'jp2' => '2',  'ket' => '1x dalam seminggu 15/15 menit (TK/KB)'],
                ['num' => '9',  'kel' => 'Muatan Kurikulum', 'nama' => 'Baca Latin', 'jp1' => '4',  'jp2' => '4',  'ket' => '1x dalam seminggu 15/15 menit (TK/KB)'],
                ['num' => '10', 'kel' => 'Muatan Kurikulum', 'nama' => 'SIROH', 'jp1' => '3',  'jp2' => '2',  'ket' => '1x dalam seminggu 30 menit (TK/KB)'],
                ['num' => '11', 'kel' => 'Muatan Kurikulum', 'nama' => 'IKM', 'jp1' => '12', 'jp2' => '20', 'ket' => 'Senin s/d Kamis = 60/45 menit/pertemuan (TK/KB)'],
            ];

            foreach ($kbtkItems as $ord => $it) {
                Database::execute("
                    INSERT INTO kurikulum_struktur_items 
                    (unit_id, academic_year_id, item_number, kelompok, nama_mapel, order_num, jp_1, jp_2, keterangan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$unitId, $taId, $it['num'], $it['kel'], $it['nama'], $ord + 1, $it['jp1'], $it['jp2'], $it['ket']]);
            }

        } elseif ($level === 'SD' || $unitId == 6) {
            $sdItems = [
                // Kelompok A
                ['num' => '1',   'kel' => 'Kelompok A', 'nama' => 'Pendidikan Agama Islam (PAI):', 'jp' => ['', '', '', '', '', ''], 'sub' => 0, 'ket' => ''],
                ['num' => '1.1', 'kel' => 'Kelompok A', 'nama' => 'Al Qur\'an dan Al Hadits', 'jp' => ['1', '1', '1', '1', '1', '1'], 'sub' => 1, 'ket' => ''],
                ['num' => '1.2', 'kel' => 'Kelompok A', 'nama' => 'Aqidah Akhlaq Adab Islami', 'jp' => ['1', '1', '1', '1', '1', '1'], 'sub' => 1, 'ket' => ''],
                ['num' => '1.3', 'kel' => 'Kelompok A', 'nama' => 'Fiqih', 'jp' => ['1', '1', '2', '2', '2', '2'], 'sub' => 1, 'ket' => ''],
                ['num' => '1.4', 'kel' => 'Kelompok A', 'nama' => 'Sejarah Kebudayaan Islam', 'jp' => ['', '', '1', '1', '1', '1'], 'sub' => 1, 'ket' => ''],
                ['num' => '2',   'kel' => 'Kelompok A', 'nama' => 'Pendidikan Kewarganegaraan', 'jp' => ['3', '3', '3', '3', '3', '3'], 'sub' => 0, 'ket' => ''],
                ['num' => '3',   'kel' => 'Kelompok A', 'nama' => 'Bahasa Indonesia', 'jp' => ['3', '3', '3', '3', '3', '3'], 'sub' => 0, 'ket' => ''],
                ['num' => '4',   'kel' => 'Kelompok A', 'nama' => 'Ilmu Pengetahuan Alam dan Sosial', 'jp' => ['2', '2', '2', '2', '2', '2'], 'sub' => 0, 'ket' => ''],
                ['num' => '5',   'kel' => 'Kelompok A', 'nama' => 'Seni Budaya dan Keterampilan/Informatika', 'jp' => ['2', '2', '2', '2', '2', '2'], 'sub' => 0, 'ket' => ''],
                ['num' => '6',   'kel' => 'Kelompok A', 'nama' => 'Pendidikan Jasmani, Olahraga & Kesehatan', 'jp' => ['', '', '2', '2', '2', '2'], 'sub' => 0, 'ket' => ''],
                ['num' => '7',   'kel' => 'Kelompok A', 'nama' => 'Matematika', 'jp' => ['6', '6', '6', '6', '6', '6'], 'sub' => 0, 'ket' => ''],
                // Kelompok B
                ['num' => '8',   'kel' => 'Kelompok B', 'nama' => 'Bahasa Inggris', 'jp' => ['10', '10', '8', '8', '8', '8'], 'sub' => 0, 'ket' => ''],
                ['num' => '9',   'kel' => 'Kelompok B', 'nama' => 'Bahasa Arab', 'jp' => ['5', '5', '5', '5', '5', '5'], 'sub' => 0, 'ket' => ''],
                ['num' => '10',  'kel' => 'Kelompok B', 'nama' => 'Tahsin dan Tahfidz', 'jp' => ['10', '10', '10', '10', '10', '10'], 'sub' => 0, 'ket' => ''],
                ['num' => '11',  'kel' => 'Kelompok B', 'nama' => 'Science', 'jp' => ['3', '3', '4', '4', '4', '4'], 'sub' => 0, 'ket' => ''],
                // Kelompok C
                ['num' => '12',  'kel' => 'Kelompok C', 'nama' => 'Kokurikuler', 'jp' => ['6', '6', '7', '7', '7', '7'], 'sub' => 0, 'ket' => 'P5 & Penguatan Karakter'],
                // Kelompok D
                ['num' => '13',  'kel' => 'Kelompok D', 'nama' => 'Pengembangan Diri (Ekstrakurikuler)*', 'jp' => ['2', '2', '2', '2', '2', '2'], 'sub' => 0, 'ket' => 'Pramuka, English Club, Arabic Club, Bina Prestasi, Taekwondo, Tataboga, TAP'],
            ];

            foreach ($sdItems as $ord => $it) {
                Database::execute("
                    INSERT INTO kurikulum_struktur_items 
                    (unit_id, academic_year_id, item_number, kelompok, nama_mapel, order_num, jp_1, jp_2, jp_3, jp_4, jp_5, jp_6, keterangan, is_sub_item)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$unitId, $taId, $it['num'], $it['kel'], $it['nama'], $ord + 1, $it['jp'][0], $it['jp'][1], $it['jp'][2], $it['jp'][3], $it['jp'][4], $it['jp'][5], $it['ket'], $it['sub']]);
            }

        } elseif ($level === 'SMP' || $unitId == 7) {
            $smpItems = [
                // Kelompok A (Umum)
                ['num' => '1',  'kel' => 'Kelompok A (Umum)', 'nama' => 'Pendidikan Agama dan Budi Pekerti', 'jp' => ['', '', ''], 'ket' => 'Sinkron dengan Aqidah, Fiqh, SKI'],
                ['num' => '2',  'kel' => 'Kelompok A (Umum)', 'nama' => 'Pendidikan Pancasila dan Kewarganegaraan', 'jp' => ['1', '1', '1'], 'ket' => ''],
                ['num' => '3',  'kel' => 'Kelompok A (Umum)', 'nama' => 'Bahasa Indonesia', 'jp' => ['2', '2', '2'], 'ket' => ''],
                ['num' => '4',  'kel' => 'Kelompok A (Umum)', 'nama' => 'Matematika', 'jp' => ['4', '4', '4'], 'ket' => ''],
                ['num' => '5',  'kel' => 'Kelompok A (Umum)', 'nama' => 'Ilmu Pengetahuan Alam', 'jp' => ['4', '4', '4'], 'ket' => ''],
                ['num' => '6',  'kel' => 'Kelompok A (Umum)', 'nama' => 'Ilmu Pengetahuan Sosial', 'jp' => ['', '', ''], 'ket' => 'Sinkron dengan PKn'],
                ['num' => '7',  'kel' => 'Kelompok A (Umum)', 'nama' => 'Bahasa Inggris', 'jp' => ['4', '4', '4'], 'ket' => ''],
                // Kelompok B (Umum)
                ['num' => '8',  'kel' => 'Kelompok B (Umum)', 'nama' => 'Seni Budaya', 'jp' => ['2', '2', '2'], 'ket' => ''],
                ['num' => '9',  'kel' => 'Kelompok B (Umum)', 'nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'jp' => ['', '', ''], 'ket' => 'Sinkron dengan Seni Budaya'],
                ['num' => '10', 'kel' => 'Kelompok B (Umum)', 'nama' => 'Prakarya', 'jp' => ['', '', ''], 'ket' => ''],
                ['num' => '11', 'kel' => 'Kelompok B (Umum)', 'nama' => 'Informatika', 'jp' => ['', '', ''], 'ket' => ''],
                // Kurikulum Tingkat Satuan Pendidikan (Ciri Khas)
                ['num' => '12', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'Aqidah', 'jp' => ['2', '2', '2'], 'ket' => ''],
                ['num' => '13', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'Fiqih', 'jp' => ['2', '2', '2'], 'ket' => ''],
                ['num' => '14', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'Sejarah Kebudayaan Islam', 'jp' => ['1', '1', '1'], 'ket' => ''],
                ['num' => '15', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'Praktik Ibadah', 'jp' => ['', '', ''], 'ket' => 'Sinkron dengan Fiqih'],
                ['num' => '16', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'Tahsin', 'jp' => ['10', '10', '10'], 'ket' => ''],
                ['num' => '17', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'Tahfidz', 'jp' => ['', '', ''], 'ket' => 'Sinkron dengan Tahsin'],
                ['num' => '18', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'Bahasa Arab', 'jp' => ['4', '4', '4'], 'ket' => ''],
                ['num' => '19', 'kel' => 'Kurikulum Tingkat Satuan Pendidikan', 'nama' => 'P5', 'jp' => ['-', '-', '-'], 'ket' => 'Projek Penguatan Profil Pelajar Pancasila'],
            ];

            foreach ($smpItems as $ord => $it) {
                Database::execute("
                    INSERT INTO kurikulum_struktur_items 
                    (unit_id, academic_year_id, item_number, kelompok, nama_mapel, order_num, jp_7, jp_8, jp_9, keterangan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$unitId, $taId, $it['num'], $it['kel'], $it['nama'], $ord + 1, $it['jp'][0], $it['jp'][1], $it['jp'][2], $it['ket']]);
            }

        } elseif ($level === 'SMA' || $unitId == 8) {
            $smaItems = [
                // Kelas 10 (Fase E)
                ['num' => '1',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Pendidikan Agama dan Budi Pekerti', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => 'Sinkron dengan Aqidah, Fiqh, SKI'],
                ['num' => '2',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Pendidikan Pancasila dan Kewarganegaraan / Sejarah', 'jp10' => '1', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '3',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Bahasa Indonesia', 'jp10' => '2', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '4',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Matematika', 'jp10' => '3', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '5',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Ilmu Pengetahuan Alam (Fisika, Kimia, Biologi)', 'jp10' => '6', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '6',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Ilmu Pengetahuan Sosial (Sosiologi, Geografi, Ekonomi)', 'jp10' => '3', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '7',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Bahasa Inggris', 'jp10' => '3', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '8',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'jp10' => '2', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '9',  'kel' => 'Fase E (Kelas 10)', 'nama' => 'Informatika', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => 'Sinkron dengan Seni Budaya'],
                ['num' => '10', 'kel' => 'Fase E (Kelas 10)', 'nama' => 'Seni Budaya', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '11', 'kel' => 'Fase E (Kelas 10)', 'nama' => 'Bahasa Arab (Muatan Lokal)', 'jp10' => '2', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '12', 'kel' => 'Fase E (Kelas 10)', 'nama' => 'Aqidah', 'jp10' => '2', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '13', 'kel' => 'Fase E (Kelas 10)', 'nama' => 'Fiqih', 'jp10' => '2', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '14', 'kel' => 'Fase E (Kelas 10)', 'nama' => 'Sejarah Kebudayaan Islam', 'jp10' => '1', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '15', 'kel' => 'Fase E (Kelas 10)', 'nama' => 'Tahsin', 'jp10' => '10', 'jp11' => '', 'jp12' => '', 'ket' => ''],
                ['num' => '16', 'kel' => 'Fase E (Kelas 10)', 'nama' => 'Tahfidz', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => 'Sinkron dengan Tahsin'],

                // Kelas 11 & 12 (Fase F - Umum)
                ['num' => '1',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Pendidikan Agama dan Budi Pekerti', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => 'Sinkron dengan Aqidah, Fiqh, SKI'],
                ['num' => '2',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Pendidikan Pancasila dan Kewarganegaraan', 'jp10' => '', 'jp11' => '1', 'jp12' => '1', 'ket' => ''],
                ['num' => '3',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Bahasa Indonesia', 'jp10' => '', 'jp11' => '2', 'jp12' => '2', 'ket' => ''],
                ['num' => '4',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Matematika', 'jp10' => '', 'jp11' => '3', 'jp12' => '3', 'ket' => ''],
                ['num' => '5',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Sejarah Indonesia', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => 'Sinkron dengan PKn'],
                ['num' => '6',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Bahasa Inggris', 'jp10' => '', 'jp11' => '3', 'jp12' => '2', 'ket' => ''],
                ['num' => '7',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Seni Budaya', 'jp10' => '', 'jp11' => '2', 'jp12' => '2', 'ket' => ''],
                ['num' => '8',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Pendidikan Jasmani, Olahraga, dan Kesehatan', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => 'Sinkron dengan Seni Budaya'],
                ['num' => '9',  'kel' => 'Fase F (Kelompok A - Umum)', 'nama' => 'Prakarya', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => ''],

                // Kelas 11 & 12 (Fase F - Kelompok B Pilihan)
                ['num' => '10', 'kel' => 'Fase F (Kelompok B - Pilihan)', 'nama' => 'Matematika / Sosiologi', 'jp10' => '', 'jp11' => '3', 'jp12' => '3', 'ket' => 'Mata pelajaran peminatan'],
                ['num' => '11', 'kel' => 'Fase F (Kelompok B - Pilihan)', 'nama' => 'Fisika / Biologi', 'jp10' => '', 'jp11' => '4', 'jp12' => '4', 'ket' => 'Mata pelajaran peminatan'],
                ['num' => '12', 'kel' => 'Fase F (Kelompok B - Pilihan)', 'nama' => 'Kimia', 'jp10' => '', 'jp11' => '3', 'jp12' => '3', 'ket' => 'Mata pelajaran peminatan'],
                ['num' => '13', 'kel' => 'Fase F (Kelompok B - Pilihan)', 'nama' => 'Bahasa Arab', 'jp10' => '', 'jp11' => '2', 'jp12' => '2', 'ket' => 'Pilihan bahasa asing'],

                // Kelas 11 & 12 (Fase F - Muatan Lokal & Karakter)
                ['num' => '14', 'kel' => 'Fase F (Muatan Lokal & Ciri Khas)', 'nama' => 'Aqidah', 'jp10' => '', 'jp11' => '1', 'jp12' => '1', 'ket' => ''],
                ['num' => '15', 'kel' => 'Fase F (Muatan Lokal & Ciri Khas)', 'nama' => 'Fiqih', 'jp10' => '', 'jp11' => '2', 'jp12' => '1', 'ket' => ''],
                ['num' => '16', 'kel' => 'Fase F (Muatan Lokal & Ciri Khas)', 'nama' => 'Sejarah Kebudayaan Islam', 'jp10' => '', 'jp11' => '1', 'jp12' => '1', 'ket' => ''],
                ['num' => '17', 'kel' => 'Fase F (Muatan Lokal & Ciri Khas)', 'nama' => 'Tahsin', 'jp10' => '', 'jp11' => '10', 'jp12' => '8', 'ket' => ''],
                ['num' => '18', 'kel' => 'Fase F (Muatan Lokal & Ciri Khas)', 'nama' => 'Tahfidz', 'jp10' => '', 'jp11' => '', 'jp12' => '', 'ket' => 'Sinkron dengan Tahsin'],
                ['num' => '19', 'kel' => 'Fase F (Muatan Lokal & Ciri Khas)', 'nama' => 'UTBK', 'jp10' => '', 'jp11' => '', 'jp12' => '4', 'ket' => 'Bimbingan Sukses PTN Kelas 12'],
            ];

            foreach ($smaItems as $ord => $it) {
                Database::execute("
                    INSERT INTO kurikulum_struktur_items 
                    (unit_id, academic_year_id, item_number, kelompok, nama_mapel, order_num, jp_10, jp_11, jp_12, keterangan)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ", [$unitId, $taId, $it['num'], $it['kel'], $it['nama'], $ord + 1, $it['jp10'], $it['jp11'], $it['jp12'], $it['ket']]);
            }
        }
    }
}

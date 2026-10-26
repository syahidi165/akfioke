<?php
// app/Controllers/AdministrasiController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\Uploader;
use Core\SecurityHelper;
use Core\Notification;

class AdministrasiController extends Controller {

    // --- UPLOAD OLEH KEPALA SEKOLAH (18 ARSIP) ---
    public function ksUploads(): void {
        $this->requireRole('kepala_sekolah');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $masterItems = Database::fetchAll("SELECT * FROM master_jenis_administrasi WHERE target_role = 'KEPALA_SEKOLAH' ORDER BY order_num ASC");
        
        $uploads = Database::fetchAll(
            "SELECT * FROM administrasi_uploads 
             WHERE unit_id = ? AND academic_year_id = ? AND user_id = ? 
             ORDER BY id DESC",
            [$unitId, $taId, $userId]
        );
        $uploadsByJenis = [];
        $uploadsByJenisMonth = [];
        foreach ($uploads as $u) {
            $uploadsByJenis[$u['jenis_admin_id']][] = $u;
            $m = (int)($u['month_num'] ?? 1);
            if (!isset($uploadsByJenisMonth[$u['jenis_admin_id']][$m])) {
                $uploadsByJenisMonth[$u['jenis_admin_id']][$m] = $u;
            }
        }

        $this->view('administrasi/ks_uploads', [
            'pageTitle'           => 'Upload Dokumen Administrasi Kepala Sekolah (18 Item)',
            'masterItems'         => $masterItems,
            'uploadsByJenis'      => $uploadsByJenis,
            'uploadsByJenisMonth' => $uploadsByJenisMonth
        ]);
    }

    // --- UPLOAD OLEH GURU MAPEL (7 ITEM TK, 8 ITEM SD/SMP/SMA) ---
    public function mapelUploads(): void {
        $this->requireRole(['guru_mapel', 'guru_diniyah', 'guru_tahfidz']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $unit = Database::fetch("SELECT level FROM units WHERE id = ?", [$unitId]);
        $targetRole = ($unit['level'] ?? '') === 'TK' ? 'GURU_TK' : 'GURU_MAPEL';

        $masterItems = Database::fetchAll("SELECT * FROM master_jenis_administrasi WHERE target_role = ? ORDER BY order_num ASC", [$targetRole]);
        $uploads = Database::fetchAll(
            "SELECT * FROM administrasi_uploads 
             WHERE unit_id = ? AND academic_year_id = ? AND user_id = ? 
             ORDER BY id DESC",
            [$unitId, $taId, $userId]
        );
        $uploadsByJenis = [];
        $uploadsByJenisMonth = [];
        foreach ($uploads as $u) {
            $uploadsByJenis[$u['jenis_admin_id']][] = $u;
            $m = (int)($u['month_num'] ?? 1);
            if (!isset($uploadsByJenisMonth[$u['jenis_admin_id']][$m])) {
                $uploadsByJenisMonth[$u['jenis_admin_id']][$m] = $u;
            }
        }

        $this->view('administrasi/mapel_uploads', [
            'pageTitle'           => 'Upload Perangkat Pembelajaran Guru',
            'masterItems'         => $masterItems,
            'uploadsByJenis'      => $uploadsByJenis,
            'uploadsByJenisMonth' => $uploadsByJenisMonth
        ]);
    }

    // --- UPLOAD OLEH WALI KELAS (6 ARSIP) ---
    public function walasUploads(): void {
        $this->requireRole('walas');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $masterItems = Database::fetchAll("SELECT * FROM master_jenis_administrasi WHERE target_role = 'WALAS' ORDER BY order_num ASC");
        $uploads = Database::fetchAll(
            "SELECT * FROM administrasi_uploads 
             WHERE unit_id = ? AND academic_year_id = ? AND user_id = ? 
             ORDER BY id DESC",
            [$unitId, $taId, $userId]
        );
        $uploadsByJenis = [];
        $uploadsByJenisMonth = [];
        foreach ($uploads as $u) {
            $uploadsByJenis[$u['jenis_admin_id']][] = $u;
            $m = (int)($u['month_num'] ?? 1);
            if (!isset($uploadsByJenisMonth[$u['jenis_admin_id']][$m])) {
                $uploadsByJenisMonth[$u['jenis_admin_id']][$m] = $u;
            }
        }

        $this->view('administrasi/walas_uploads', [
            'pageTitle'           => 'Upload Administrasi Wali Kelas (6 Item & Auto-Generate)',
            'masterItems'         => $masterItems,
            'uploadsByJenis'      => $uploadsByJenis,
            'uploadsByJenisMonth' => $uploadsByJenisMonth
        ]);
    }

    // --- UPLOAD OLEH TATA USAHA (4 ARSIP) ---
    public function tuUploads(): void {
        $this->requireRole('tata_usaha');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $masterItems = Database::fetchAll("SELECT * FROM master_jenis_administrasi WHERE target_role = 'TU' ORDER BY order_num ASC");
        $uploads = Database::fetchAll(
            "SELECT * FROM administrasi_uploads 
             WHERE unit_id = ? AND academic_year_id = ? AND user_id = ? 
             ORDER BY id DESC",
            [$unitId, $taId, $userId]
        );
        $uploadsByJenis = [];
        $uploadsByJenisMonth = [];
        foreach ($uploads as $u) {
            $uploadsByJenis[$u['jenis_admin_id']][] = $u;
            $m = (int)($u['month_num'] ?? 1);
            if (!isset($uploadsByJenisMonth[$u['jenis_admin_id']][$m])) {
                $uploadsByJenisMonth[$u['jenis_admin_id']][$m] = $u;
            }
        }

        $this->view('administrasi/tu_uploads', [
            'pageTitle'           => 'Upload Perangkat & Laporan Keuangan Tata Usaha',
            'masterItems'         => $masterItems,
            'uploadsByJenis'      => $uploadsByJenis,
            'uploadsByJenisMonth' => $uploadsByJenisMonth
        ]);
    }

    // --- PROSES UPLOAD DOKUMEN ---
    public function uploadDocument(): void {
        $this->requireAuth();
        $userId = Auth::id();
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $jenisId = (int)($_POST['jenis_admin_id'] ?? 0);
        $subTitle = trim($_POST['sub_title'] ?? '');
        $monthNum = !empty($_POST['month_num']) ? (int)$_POST['month_num'] : 1;

        if (!isset($_FILES['file'])) {
            $this->setFlash('error', 'Tidak ada berkas yang dipilih.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard'));
            return;
        }

        $uploadRes = Uploader::upload($_FILES['file'], 'administrasi');
        if (!$uploadRes['success']) {
            $this->setFlash('error', $uploadRes['message']);
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard'));
            return;
        }

        $now = date('Y-m-d');
        $isOnTime = 1;

        // Hitung versi unggahan berdasarkan jenis dan period/month_num
        $prev = Database::fetch(
            "SELECT MAX(version_num) as v FROM administrasi_uploads 
             WHERE user_id = ? AND jenis_admin_id = ? AND academic_year_id = ? AND month_num = ?",
            [$userId, $jenisId, $taId, $monthNum]
        );
        $versionNum = ($prev['v'] ?? 0) + 1;

        Database::execute(
            "INSERT INTO administrasi_uploads (unit_id, academic_year_id, user_id, jenis_admin_id, sub_title, month_num, file_path, original_filename, file_size, uploaded_at, is_on_time, status, version_num) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, 'Menunggu Review', ?)",
            [$unitId, $taId, $userId, $jenisId, $subTitle, $monthNum, $uploadRes['file_path'], $uploadRes['original_filename'], $uploadRes['file_size'], $isOnTime, $versionNum]
        );

        AuditLog::log('UPLOAD_ADMINISTRASI', 'administrasi_uploads', Database::lastInsertId(), null, ['file' => $uploadRes['original_filename'], 'version' => $versionNum]);
        $this->setFlash('success', 'Dokumen berhasil diunggah. Menunggu review atasan.');
        $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard'));
    }

    // --- APPROVAL ADMINISTRASI KS OLEH DEPUTI (18 ARSIP) ---
    public function reviewKs(): void {
        $this->requireRole('deputi_pendidikan');
        $unitFilter = (int)($_GET['unit_id'] ?? 0);
        $taId = Auth::academicYearId();

        // 1. Ambil 18 Master Jenis Administrasi untuk Kepala Sekolah
        $masterItems = Database::fetchAll("SELECT * FROM master_jenis_administrasi WHERE target_role = 'KEPALA_SEKOLAH' ORDER BY order_num ASC");
        $masterById = [];
        foreach ($masterItems as $m) {
            $masterById[(int)$m['id']] = $m;
        }

        // 2. Ambil seluruh upload dokumen KS pada TA aktif
        $sql = "SELECT au.*, m.title as jenis_title, m.code as jenis_code, m.frequency, m.order_num,
                       u.name as unit_name, usr.full_name as kepsek_name 
                FROM administrasi_uploads au 
                JOIN master_jenis_administrasi m ON au.jenis_admin_id = m.id 
                JOIN units u ON au.unit_id = u.id 
                JOIN users usr ON au.user_id = usr.id 
                WHERE m.target_role = 'KEPALA_SEKOLAH' AND au.academic_year_id = ?
                ORDER BY au.uploaded_at DESC";
        $allUploads = Database::fetchAll($sql, [$taId]);

        $uploadsByUnit = [];
        foreach ($allUploads as $up) {
            $uploadsByUnit[(int)$up['unit_id']][] = $up;
        }

        // 3. Ambil data Unit & Kepala Sekolah
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $kepsekRows = Database::fetchAll("
            SELECT u.id as unit_id, u.name as unit_name, u.level as unit_level,
                   e.id as employee_id, e.nama as kepsek_nama, e.nipy as kepsek_nipy,
                   usr.id as user_id, usr.full_name as user_full_name, usr.email as user_email
            FROM units u
            LEFT JOIN employees e ON u.id = e.unit_id 
                 AND (e.level_posisi = 'Kepala Sekolah' OR e.position LIKE '%Kepala Sekolah%')
                 AND e.is_deleted = 0
            LEFT JOIN users usr ON e.user_id = usr.id
            ORDER BY u.id ASC
        ");

        $kepsekByUnit = [];
        foreach ($kepsekRows as $kr) {
            $kepsekByUnit[(int)$kr['unit_id']] = $kr;
        }

        // 4. Susun struktur kartu ($ksCards) dan $summary
        $ksCards = [];
        $summary = [
            'total_units'        => 0,
            'total_files'        => count($allUploads),
            'total_pending'      => 0,
            'total_approved'     => 0,
            'total_revision'     => 0,
            'units_with_pending' => 0,
            'units_complete'     => 0,
            'units_incomplete'   => 0,
        ];

        $monthNames = [
            1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
            5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
            9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
        ];
        $currentMonth = (int)date('n');

        $monthlyMasterItems = [];
        foreach ($masterItems as $mItem) {
            if (str_contains(strtolower($mItem['frequency'] ?? ''), 'bulan')) {
                $monthlyMasterItems[] = $mItem;
            }
        }

        foreach ($units as $u) {
            $uId = (int)$u['id'];
            if ($unitFilter > 0 && $uId !== $unitFilter) {
                continue;
            }

            $summary['total_units']++;
            $kInfo = $kepsekByUnit[$uId] ?? [
                'unit_id'        => $uId,
                'unit_name'      => $u['name'],
                'unit_level'     => $u['level'] ?? '',
                'employee_id'    => null,
                'kepsek_nama'    => null,
                'kepsek_nipy'    => '-',
                'user_id'        => null,
                'user_full_name' => null
            ];

            $unitUploads = $uploadsByUnit[$uId] ?? [];

            // Jika kepsek_nama kosong, cek apakah ada nama pengunggah di unitUploads
            $displayName = $kInfo['kepsek_nama'] ?: ($kInfo['user_full_name'] ?? '');
            $displayUserId = $kInfo['user_id'];
            if (empty($displayName) && !empty($unitUploads)) {
                $displayName = $unitUploads[0]['kepsek_name'] ?? 'Kepala Sekolah';
                $displayUserId = $unitUploads[0]['user_id'] ?? null;
            }
            if (empty($displayName)) {
                $displayName = 'Kepala Sekolah ' . $u['name'];
            }

            $uploadedJenisIds = [];
            $pendingCount = 0;
            $approvedCount = 0;
            $revisionCount = 0;

            foreach ($unitUploads as $up) {
                $uploadedJenisIds[(int)$up['jenis_admin_id']] = true;
                if ($up['status'] === 'Menunggu Review') {
                    $pendingCount++;
                } elseif ($up['status'] === 'Disetujui') {
                    $approvedCount++;
                } elseif ($up['status'] === 'Revisi') {
                    $revisionCount++;
                }
            }

            $missingItems = [];
            foreach ($masterItems as $mItem) {
                $mid = (int)$mItem['id'];
                if (!isset($uploadedJenisIds[$mid])) {
                    $missingItems[] = $mItem;
                }
            }

            $totalExpected = count($masterItems); // 18 item
            $totalUploaded = count($uploadedJenisIds);
            $totalMissing = count($missingItems);
            $completionPct = $totalExpected > 0 ? (int)round(($totalUploaded / $totalExpected) * 100) : 100;

            if ($pendingCount > 0) {
                $summary['units_with_pending']++;
            }
            if ($completionPct >= 100 && $totalExpected > 0) {
                $summary['units_complete']++;
            } else {
                $summary['units_incomplete']++;
            }

            $summary['total_pending'] += $pendingCount;
            $summary['total_approved'] += $approvedCount;
            $summary['total_revision'] += $revisionCount;

            // Klasifikasi periode (Bulanan, Tahunan, 2x Setahun, 3x Setahun/Lainnya)
            $periodStats = [
                'bulanan'  => ['expected' => 0, 'uploaded' => 0, 'missing' => 0],
                'tahunan'  => ['expected' => 0, 'uploaded' => 0, 'missing' => 0],
                'semester' => ['expected' => 0, 'uploaded' => 0, 'missing' => 0],
                'lainnya'  => ['expected' => 0, 'uploaded' => 0, 'missing' => 0]
            ];

            foreach ($masterItems as $mItem) {
                $freq = strtolower(trim($mItem['frequency'] ?? ''));
                $pKey = 'lainnya';
                if (str_contains($freq, 'bulan')) $pKey = 'bulanan';
                elseif (str_contains($freq, '1x') || str_contains($freq, 'tahun')) $pKey = 'tahunan';
                elseif (str_contains($freq, '2x') || str_contains($freq, 'semester')) $pKey = 'semester';
                elseif (str_contains($freq, '3x')) $pKey = 'lainnya';

                $periodStats[$pKey]['expected']++;
                if (isset($uploadedJenisIds[(int)$mItem['id']])) {
                    $periodStats[$pKey]['uploaded']++;
                } else {
                    $periodStats[$pKey]['missing']++;
                }
            }

            // Tag period_key ke uploads dan missing
            foreach ($unitUploads as &$uRef) {
                $freq = strtolower(trim($uRef['frequency'] ?? ''));
                $pKey = 'lainnya';
                if (str_contains($freq, 'bulan')) $pKey = 'bulanan';
                elseif (str_contains($freq, '1x') || str_contains($freq, 'tahun')) $pKey = 'tahunan';
                elseif (str_contains($freq, '2x') || str_contains($freq, 'semester')) $pKey = 'semester';
                elseif (str_contains($freq, '3x')) $pKey = 'lainnya';
                $uRef['period_key'] = $pKey;
            }
            unset($uRef);

            foreach ($missingItems as &$mRef) {
                $freq = strtolower(trim($mRef['frequency'] ?? ''));
                $pKey = 'lainnya';
                if (str_contains($freq, 'bulan')) $pKey = 'bulanan';
                elseif (str_contains($freq, '1x') || str_contains($freq, 'tahun')) $pKey = 'tahunan';
                elseif (str_contains($freq, '2x') || str_contains($freq, 'semester')) $pKey = 'semester';
                elseif (str_contains($freq, '3x')) $pKey = 'lainnya';
                $mRef['period_key'] = $pKey;
            }
            unset($mRef);

            // Matriks Bulanan (12 Bulan)
            $monthsSummary = [];
            for ($m = 1; $m <= 12; $m++) {
                $monthsSummary[$m] = [
                    'month_num'      => $m,
                    'month_name'     => $monthNames[$m],
                    'is_past'        => ($m < $currentMonth),
                    'is_current'     => ($m === $currentMonth),
                    'is_future'      => ($m > $currentMonth),
                    'expected_count' => count($monthlyMasterItems),
                    'uploaded_count' => 0,
                    'approved_count' => 0,
                    'pending_count'  => 0,
                    'revision_count' => 0,
                    'missing_count'  => 0,
                    'status'         => 'upcoming'
                ];
            }

            $uploadsByJenisAndMonth = [];
            foreach ($unitUploads as $up) {
                $jId = (int)$up['jenis_admin_id'];
                $mNum = !empty($up['month_num']) ? (int)$up['month_num'] : 1;
                $uploadsByJenisAndMonth[$jId][$mNum] = $up;
            }

            $monthlyMatrix = [];
            $pastMissingMonthly = 0;
            $currentMissingMonthly = 0;

            foreach ($monthlyMasterItems as $mItem) {
                $jId = (int)$mItem['id'];
                $row = [
                    'jenis_id'   => $jId,
                    'title'      => $mItem['title'],
                    'code'       => $mItem['code'] ?? '',
                    'due_info'   => $mItem['frequency'],
                    'months'     => []
                ];

                for ($m = 1; $m <= 12; $m++) {
                    $up = $uploadsByJenisAndMonth[$jId][$m] ?? null;
                    $isPast = ($m < $currentMonth);
                    $isCurrent = ($m === $currentMonth);

                    if ($up) {
                        $monthsSummary[$m]['uploaded_count']++;
                        if ($up['status'] === 'Disetujui') {
                            $cellState = 'approved';
                            $monthsSummary[$m]['approved_count']++;
                        } elseif ($up['status'] === 'Revisi') {
                            $cellState = 'revision';
                            $monthsSummary[$m]['revision_count']++;
                        } else {
                            $cellState = 'pending';
                            $monthsSummary[$m]['pending_count']++;
                        }
                    } else {
                        if ($isPast) {
                            $cellState = 'missing_past';
                            $pastMissingMonthly++;
                            $monthsSummary[$m]['missing_count']++;
                        } elseif ($isCurrent) {
                            $cellState = 'missing_current';
                            $currentMissingMonthly++;
                            $monthsSummary[$m]['missing_count']++;
                        } else {
                            $cellState = 'upcoming';
                        }
                    }

                    $row['months'][$m] = [
                        'state'      => $cellState,
                        'upload'     => $up,
                        'month_name' => $monthNames[$m]
                    ];
                }

                $monthlyMatrix[] = $row;
            }

            for ($m = 1; $m <= 12; $m++) {
                $mSum = &$monthsSummary[$m];
                if ($mSum['expected_count'] === 0) {
                    $mSum['status'] = 'empty';
                } elseif ($mSum['uploaded_count'] >= $mSum['expected_count']) {
                    $mSum['status'] = ($mSum['pending_count'] > 0) ? 'pending' : 'complete';
                } elseif ($mSum['uploaded_count'] > 0) {
                    $mSum['status'] = ($mSum['pending_count'] > 0) ? 'pending' : 'partial';
                } else {
                    if ($mSum['is_past']) {
                        $mSum['status'] = 'missing_past';
                    } elseif ($mSum['is_current']) {
                        $mSum['status'] = 'missing_current';
                    } else {
                        $mSum['status'] = 'upcoming';
                    }
                }
            }
            unset($mSum);

            $ksCards[] = [
                'unit_id'                 => $uId,
                'unit_name'               => $u['name'],
                'unit_level'              => $u['level'] ?? '',
                'user_id'                 => $displayUserId,
                'employee_id'             => $kInfo['employee_id'] ?? null,
                'name'                    => $displayName,
                'nipy'                    => !empty($kInfo['kepsek_nipy']) ? $kInfo['kepsek_nipy'] : '-',
                'role_display'            => 'Kepala Sekolah',
                'expected_count'          => $totalExpected,
                'uploaded_count'          => $totalUploaded,
                'missing_count'           => $totalMissing,
                'percent'                 => $completionPct,
                'total_files'             => count($unitUploads),
                'pending_count'           => $pendingCount,
                'approved_count'          => $approvedCount,
                'revision_count'          => $revisionCount,
                'period_stats'            => $periodStats,
                'monthly_matrix'          => $monthlyMatrix,
                'monthly_items_count'     => count($monthlyMasterItems),
                'monthly_past_missing'    => $pastMissingMonthly,
                'monthly_current_missing' => $currentMissingMonthly,
                'months_summary'          => $monthsSummary,
                'current_month_num'       => $currentMonth,
                'uploads'                 => $unitUploads,
                'missing_items'           => $missingItems
            ];
        }

        // Urutkan kartu: yang butuh review ditaruh di urutan paling atas
        usort($ksCards, function($a, $b) {
            if ($a['pending_count'] !== $b['pending_count']) {
                return $b['pending_count'] <=> $a['pending_count'];
            }
            return strcmp($a['unit_name'], $b['unit_name']);
        });

        $this->view('administrasi/review_ks', [
            'pageTitle'  => 'Approval Administrasi Kepala Sekolah (Deputi Direktur)',
            'ksCards'    => $ksCards,
            'summary'    => $summary,
            'units'      => $units,
            'unitFilter' => $unitFilter
        ]);
    }

    // --- APPROVAL ADMINISTRASI GURU & TU OLEH KEPALA SEKOLAH ---
    public function reviewGuru(): void {
        $this->requireRole('kepala_sekolah');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $unitLevel = $unit['level'] ?? '';
        $isTK = ($unitLevel === 'TK');
        $defaultTeacherRole = $isTK ? 'GURU_TK' : 'GURU_MAPEL';

        // 1. Ambil master jenis administrasi (selain KEPALA_SEKOLAH)
        $allMaster = Database::fetchAll("SELECT * FROM master_jenis_administrasi WHERE target_role != 'KEPALA_SEKOLAH' ORDER BY order_num ASC");
        $masterByRole = [];
        foreach ($allMaster as $m) {
            $masterByRole[$m['target_role']][$m['id']] = $m;
        }

        // 2. Ambil data pegawai/staf di unit yang bersangkutan (guru, walas, TU, dsb.)
        $employees = Database::fetchAll("
            SELECT e.id as employee_id, e.user_id, e.nipy, e.nama, e.level_posisi, e.position, e.unit_id,
                   u.username, u.full_name,
                   r.name as role_name, r.display_name as role_display
            FROM employees e
            JOIN users u ON e.user_id = u.id
            LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_primary = 1
            LEFT JOIN roles r ON ur.role_id = r.id
            WHERE e.unit_id = ? AND e.is_deleted = 0
              AND (r.name NOT IN ('kepala_sekolah', 'operator_sekolah', 'cleaning_service', 'security', 'orang_tua', 'siswa') OR r.name IS NULL)
            ORDER BY e.nama ASC
        ", [$unitId]);

        // 3. Ambil semua berkas administrasi yang diunggah untuk unit & TA aktif
        $sql = "SELECT au.*, m.title as jenis_title, m.target_role, m.frequency,
                       usr.full_name as teacher_name, r.display_name as role_title 
                FROM administrasi_uploads au 
                JOIN master_jenis_administrasi m ON au.jenis_admin_id = m.id 
                JOIN users usr ON au.user_id = usr.id 
                LEFT JOIN user_roles ur ON usr.id = ur.user_id AND ur.is_primary = 1 
                LEFT JOIN roles r ON ur.role_id = r.id 
                WHERE au.unit_id = ? AND au.academic_year_id = ? AND m.target_role != 'KEPALA_SEKOLAH' 
                ORDER BY au.uploaded_at DESC";
        $uploads = Database::fetchAll($sql, [$unitId, $taId]);

        // 4. Ambil semua karya ilmiah guru (PTK / Best Practice)
        $karyaUploads = Database::fetchAll(
            "SELECT k.*, e.nama as teacher_name, e.level_posisi, r.display_name as role_title 
             FROM karya_ilmiah_guru k 
             JOIN employees e ON k.employee_id = e.id 
             JOIN users u ON k.user_id = u.id 
             LEFT JOIN user_roles ur ON u.id = ur.user_id AND ur.is_primary = 1 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE k.unit_id = ? AND k.academic_year_id = ? 
             ORDER BY k.created_at DESC",
            [$unitId, $taId]
        );

        // Petakan uploads & karya per user_id
        $uploadsByUser = [];
        foreach ($uploads as $up) {
            $uploadsByUser[(int)$up['user_id']][] = $up;
        }

        $karyaByUser = [];
        foreach ($karyaUploads as $k) {
            $karyaByUser[(int)$k['user_id']][] = $k;
        }

        // Kumpulkan map user_id dari employees
        $knownUserIds = [];
        foreach ($employees as $emp) {
            $knownUserIds[(int)$emp['user_id']] = true;
        }

        // Jika ada user yang mengunggah dokumen di unit ini tapi belum ada di employees, tambahkan
        foreach ($uploads as $up) {
            $uid = (int)$up['user_id'];
            if (!isset($knownUserIds[$uid])) {
                $knownUserIds[$uid] = true;
                $employees[] = [
                    'employee_id'  => null,
                    'user_id'      => $uid,
                    'nipy'         => '-',
                    'nama'         => $up['teacher_name'],
                    'level_posisi' => $up['role_title'] ?? 'Guru / Staf',
                    'position'     => $up['role_title'] ?? 'Guru / Staf',
                    'unit_id'      => $unitId,
                    'username'     => '',
                    'full_name'    => $up['teacher_name'],
                    'role_name'    => '',
                    'role_display' => $up['role_title'] ?? 'Guru / Staf'
                ];
            }
        }

        // 5. Susun struktur kartu per guru / pegawai ($teacherCards)
        $teacherCards = [];
        $summary = [
            'total_teachers'       => count($employees),
            'total_files'          => count($uploads),
            'total_pending'        => 0,
            'total_approved'       => 0,
            'total_revision'       => 0,
            'total_karya'          => count($karyaUploads),
            'teachers_with_pending'=> 0,
            'teachers_complete'    => 0,
            'teachers_incomplete'  => 0,
        ];

        foreach ($employees as $emp) {
            $uid = (int)$emp['user_id'];
            $roleName = $emp['role_name'] ?? '';
            $posisi = ($emp['level_posisi'] ?? '') . ' ' . ($emp['position'] ?? '');
            $userUploads = $uploadsByUser[$uid] ?? [];
            $userKarya = $karyaByUser[$uid] ?? [];

            // Klasifikasi kategori untuk filter tab
            $category = 'mapel';
            if ($roleName === 'tata_usaha' || stripos($posisi, 'tata usaha') !== false || stripos($posisi, 'tu ') !== false) {
                $category = 'tu';
            } elseif ($roleName === 'walas' || stripos($posisi, 'wali kelas') !== false || stripos($posisi, 'walas') !== false) {
                $category = 'walas';
            } elseif (in_array($roleName, ['guru_tahfidz', 'koor_tahfidz'], true) || stripos($posisi, 'tahfidz') !== false) {
                $category = 'tahfidz';
            } elseif (in_array($roleName, ['guru_mapel', 'guru_diniyah', 'guru_mapel_kbtk'], true) || stripos($posisi, 'diniyah') !== false || stripos($posisi, 'mapel') !== false) {
                $category = 'mapel';
            } else {
                $category = 'lainnya';
            }

            // Tentukan kelompok master jenis yang wajib bagi pegawai ini
            $targetRoles = [];
            if ($category === 'tu') {
                $targetRoles['TU'] = true;
            } elseif ($category === 'walas') {
                $targetRoles['WALAS'] = true;
                if (stripos($posisi, 'guru') !== false || stripos($roleName, 'guru') !== false) {
                    $targetRoles[$defaultTeacherRole] = true;
                }
            } else {
                $targetRoles[$defaultTeacherRole] = true;
            }

            // Tambahkan juga target_role jika pegawai ini sudah mengunggah berkas kategori tersebut
            foreach ($userUploads as $u) {
                if (!empty($u['target_role']) && $u['target_role'] !== 'KEPALA_SEKOLAH') {
                    $targetRoles[$u['target_role']] = true;
                }
            }

            // Kumpulkan daftar master item yang diharapkan
            $expectedMaster = [];
            foreach (array_keys($targetRoles) as $tr) {
                if (isset($masterByRole[$tr])) {
                    foreach ($masterByRole[$tr] as $mid => $mItem) {
                        $expectedMaster[$mid] = $mItem;
                    }
                }
            }

            // Hitung status approval & kelengkapan
            $uploadedJenisIds = [];
            $pendingCount = 0;
            $approvedCount = 0;
            $revisionCount = 0;

            foreach ($userUploads as $u) {
                $uploadedJenisIds[(int)$u['jenis_admin_id']] = true;
                if ($u['status'] === 'Menunggu Review') {
                    $pendingCount++;
                } elseif ($u['status'] === 'Disetujui') {
                    $approvedCount++;
                } elseif ($u['status'] === 'Revisi') {
                    $revisionCount++;
                }
            }

            // Tentukan master item yang belum diunggah sama sekali
            $missingItems = [];
            foreach ($expectedMaster as $mid => $mItem) {
                if (!isset($uploadedJenisIds[$mid])) {
                    $missingItems[] = $mItem;
                }
            }

            $totalExpected = count($expectedMaster);
            $totalUploaded = count($uploadedJenisIds);
            $totalMissing = count($missingItems);
            $completionPct = $totalExpected > 0 ? (int)round(($totalUploaded / $totalExpected) * 100) : 100;

            if ($pendingCount > 0) {
                $summary['teachers_with_pending']++;
            }
            if ($completionPct >= 100 && $totalExpected > 0) {
                $summary['teachers_complete']++;
            } else {
                $summary['teachers_incomplete']++;
            }

            $summary['total_pending'] += $pendingCount;
            $summary['total_approved'] += $approvedCount;
            $summary['total_revision'] += $revisionCount;

            // Klasifikasi periode (Bulanan, Tahunan, 2x Setahun, 3x Setahun/Lainnya)
            $periodStats = [
                'bulanan'  => ['expected' => 0, 'uploaded' => 0, 'missing' => 0],
                'tahunan'  => ['expected' => 0, 'uploaded' => 0, 'missing' => 0],
                'semester' => ['expected' => 0, 'uploaded' => 0, 'missing' => 0],
                'lainnya'  => ['expected' => 0, 'uploaded' => 0, 'missing' => 0]
            ];

            foreach ($expectedMaster as $mid => $mItem) {
                $freq = strtolower(trim($mItem['frequency'] ?? ''));
                $pKey = 'lainnya';
                if (str_contains($freq, 'bulan')) $pKey = 'bulanan';
                elseif (str_contains($freq, '1x') || str_contains($freq, 'tahun')) $pKey = 'tahunan';
                elseif (str_contains($freq, '2x') || str_contains($freq, 'semester')) $pKey = 'semester';
                elseif (str_contains($freq, '3x')) $pKey = 'lainnya';

                $periodStats[$pKey]['expected']++;
                if (isset($uploadedJenisIds[$mid])) {
                    $periodStats[$pKey]['uploaded']++;
                } else {
                    $periodStats[$pKey]['missing']++;
                }
            }

            // Lampirkan tag period_key ke masing-masing item upload dan missing
            foreach ($userUploads as &$uRef) {
                $freq = strtolower(trim($uRef['frequency'] ?? ''));
                $pKey = 'lainnya';
                if (str_contains($freq, 'bulan')) $pKey = 'bulanan';
                elseif (str_contains($freq, '1x') || str_contains($freq, 'tahun')) $pKey = 'tahunan';
                elseif (str_contains($freq, '2x') || str_contains($freq, 'semester')) $pKey = 'semester';
                elseif (str_contains($freq, '3x')) $pKey = 'lainnya';
                $uRef['period_key'] = $pKey;
            }
            unset($uRef);

            foreach ($missingItems as &$mRef) {
                $freq = strtolower(trim($mRef['frequency'] ?? ''));
                $pKey = 'lainnya';
                if (str_contains($freq, 'bulan')) $pKey = 'bulanan';
                elseif (str_contains($freq, '1x') || str_contains($freq, 'tahun')) $pKey = 'tahunan';
                elseif (str_contains($freq, '2x') || str_contains($freq, 'semester')) $pKey = 'semester';
                elseif (str_contains($freq, '3x')) $pKey = 'lainnya';
                $mRef['period_key'] = $pKey;
            }
            unset($mRef);

            // 6. Matriks Riwayat Pengumpulan Bulanan (12 Bulan: Jan s/d Des)
            $monthNames = [
                1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
                5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu',
                9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'
            ];
            $currentMonth = (int)date('n'); // 1..12

            $monthlyMasterItems = [];
            foreach ($expectedMaster as $mid => $mItem) {
                if (str_contains(strtolower($mItem['frequency'] ?? ''), 'bulan')) {
                    $monthlyMasterItems[] = $mItem;
                }
            }

            $monthlyMatrix = [];
            $pastMissingMonthly = 0;
            $currentMissingMonthly = 0;
            $monthsSummary = [];

            for ($m = 1; $m <= 12; $m++) {
                $monthsSummary[$m] = [
                    'month_num'      => $m,
                    'month_name'     => $monthNames[$m],
                    'is_past'        => ($m < $currentMonth),
                    'is_current'     => ($m === $currentMonth),
                    'is_future'      => ($m > $currentMonth),
                    'expected_count' => count($monthlyMasterItems),
                    'uploaded_count' => 0,
                    'approved_count' => 0,
                    'pending_count'  => 0,
                    'revision_count' => 0,
                    'missing_count'  => 0,
                    'status'         => 'upcoming'
                ];
            }

            // Index uploads by [jenis_admin_id][month_num]
            $uploadsByJenisAndMonth = [];
            foreach ($userUploads as $u) {
                $jId = (int)$u['jenis_admin_id'];
                $mNum = !empty($u['month_num']) ? (int)$u['month_num'] : 1;
                $uploadsByJenisAndMonth[$jId][$mNum] = $u;
            }

            foreach ($monthlyMasterItems as $mItem) {
                $jId = (int)$mItem['id'];
                $row = [
                    'jenis_id'   => $jId,
                    'title'      => $mItem['title'],
                    'code'       => $mItem['code'] ?? '',
                    'due_info'   => $mItem['due_info'] ?? 'Bulanan',
                    'months'     => []
                ];

                for ($m = 1; $m <= 12; $m++) {
                    $isPast = ($m < $currentMonth);
                    $isCurrent = ($m === $currentMonth);
                    $isFuture = ($m > $currentMonth);
                    $up = $uploadsByJenisAndMonth[$jId][$m] ?? null;

                    if ($up) {
                        $monthsSummary[$m]['uploaded_count']++;
                        if ($up['status'] === 'Disetujui') {
                            $monthsSummary[$m]['approved_count']++;
                            $state = 'approved';
                        } elseif ($up['status'] === 'Menunggu Review') {
                            $monthsSummary[$m]['pending_count']++;
                            $state = 'pending';
                        } else {
                            $monthsSummary[$m]['revision_count']++;
                            $state = 'revision';
                        }
                    } else {
                        $monthsSummary[$m]['missing_count']++;
                        if ($isPast) {
                            $pastMissingMonthly++;
                            $state = 'missing_past'; // Belum kumpul di bulan lalu
                        } elseif ($isCurrent) {
                            $currentMissingMonthly++;
                            $state = 'missing_current'; // Belum kumpul bulan ini
                        } else {
                            $state = 'upcoming'; // Bulan mendatang
                        }
                    }

                    $row['months'][$m] = [
                        'month_num'   => $m,
                        'month_name'  => $monthNames[$m],
                        'state'       => $state,
                        'is_past'     => $isPast,
                        'is_current'  => $isCurrent,
                        'is_future'   => $isFuture,
                        'upload'      => $up ? [
                            'id'                => $up['id'],
                            'file_path'         => $up['file_path'],
                            'original_filename' => $up['original_filename'],
                            'status'            => $up['status'],
                            'uploaded_at'       => $up['uploaded_at'],
                            'version_num'       => $up['version_num'] ?? 1
                        ] : null
                    ];
                }
                $monthlyMatrix[] = $row;
            }

            foreach ($monthsSummary as $m => &$mSum) {
                if ($mSum['expected_count'] === 0) {
                    $mSum['status'] = 'empty';
                } elseif ($mSum['uploaded_count'] >= $mSum['expected_count']) {
                    $mSum['status'] = ($mSum['pending_count'] > 0) ? 'pending' : 'complete';
                } elseif ($mSum['uploaded_count'] > 0) {
                    $mSum['status'] = ($mSum['pending_count'] > 0) ? 'pending' : 'partial';
                } else {
                    if ($mSum['is_past']) {
                        $mSum['status'] = 'missing_past';
                    } elseif ($mSum['is_current']) {
                        $mSum['status'] = 'missing_current';
                    } else {
                        $mSum['status'] = 'upcoming';
                    }
                }
            }
            unset($mSum);

            $teacherCards[] = [
                'user_id'                 => $uid,
                'employee_id'             => $emp['employee_id'] ?? null,
                'name'                    => $emp['nama'] ?: ($emp['full_name'] ?? 'Pegawai'),
                'nipy'                    => !empty($emp['nipy']) ? $emp['nipy'] : '-',
                'role_name'               => $roleName,
                'role_display'            => $emp['role_display'] ?: ($emp['level_posisi'] ?: 'Guru / Staf'),
                'category'                => $category,
                'expected_count'          => $totalExpected,
                'uploaded_count'          => $totalUploaded,
                'missing_count'           => $totalMissing,
                'percent'                 => $completionPct,
                'total_files'             => count($userUploads),
                'pending_count'           => $pendingCount,
                'approved_count'          => $approvedCount,
                'revision_count'          => $revisionCount,
                'karya_count'             => count($userKarya),
                'period_stats'            => $periodStats,
                'monthly_matrix'          => $monthlyMatrix,
                'monthly_items_count'     => count($monthlyMasterItems),
                'monthly_past_missing'    => $pastMissingMonthly,
                'monthly_current_missing' => $currentMissingMonthly,
                'months_summary'          => $monthsSummary,
                'current_month_num'       => $currentMonth,
                'uploads'                 => $userUploads,
                'missing_items'           => $missingItems,
                'karya'                   => $userKarya
            ];
        }

        // Urutkan kartu: yang memiliki berkas "Menunggu Review" diprioritaskan di atas
        usort($teacherCards, function($a, $b) {
            if ($a['pending_count'] !== $b['pending_count']) {
                return $b['pending_count'] <=> $a['pending_count'];
            }
            return strcmp($a['name'], $b['name']);
        });

        $this->view('administrasi/review_guru', [
            'pageTitle'    => 'Approval Administrasi Guru, Walas & Staf TU',
            'unit'         => $unit,
            'summary'      => $summary,
            'teacherCards' => $teacherCards,
            'uploads'      => $uploads,
            'karyaUploads' => $karyaUploads
        ]);
    }

    public function batchApproveTeacher(): void {
        $this->requireRole(['kepala_sekolah', 'deputi_pendidikan']);
        $isDeputi = Auth::hasRole('deputi_pendidikan');
        $unitId = $isDeputi ? (int)($_POST['unit_id'] ?? 0) : Auth::unitId();
        $taId = Auth::academicYearId();
        $targetUserId = (int)($_POST['user_id'] ?? 0);
        $roleTitle = $isDeputi ? 'Deputi Direktur Pendidikan' : 'Kepala Sekolah';
        $notes = trim($_POST['batch_notes'] ?? "Disetujui menyeluruh oleh {$roleTitle}.");
        $reviewerId = Auth::id();

        if ($targetUserId <= 0 && !$isDeputi) {
            $this->setFlash('error', 'Pegawai tidak valid.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/administrasi/review-guru'));
            return;
        }

        $sql = "SELECT id, original_filename FROM administrasi_uploads 
                WHERE academic_year_id = ? AND status = 'Menunggu Review'";
        $params = [$taId];
        if ($unitId > 0) {
            $sql .= " AND unit_id = ?";
            $params[] = $unitId;
        }
        if ($targetUserId > 0) {
            $sql .= " AND user_id = ?";
            $params[] = $targetUserId;
        }

        $pending = Database::fetchAll($sql, $params);
        $pendingCount = count($pending);

        if ($pendingCount > 0) {
            $updateSql = "UPDATE administrasi_uploads 
                          SET status = 'Disetujui', reviewer_user_id = ?, review_notes = ?, reviewed_at = NOW() 
                          WHERE academic_year_id = ? AND status = 'Menunggu Review'";
            $updateParams = [$reviewerId, $notes, $taId];
            if ($unitId > 0) {
                $updateSql .= " AND unit_id = ?";
                $updateParams[] = $unitId;
            }
            if ($targetUserId > 0) {
                $updateSql .= " AND user_id = ?";
                $updateParams[] = $targetUserId;
            }

            Database::execute($updateSql, $updateParams);

            if ($targetUserId > 0) {
                Notification::send(
                    $targetUserId,
                    "Approval Administrasi Disetujui",
                    "{$roleTitle} telah menyetujui {$pendingCount} berkas administrasi sekaligus.",
                    'success',
                    Auth::baseUrl() . '/dashboard'
                );
            }

            AuditLog::log('BATCH_APPROVE_ADMINISTRASI', 'administrasi_uploads', $targetUserId ?: $unitId, null, [
                'count' => $pendingCount,
                'notes' => $notes
            ]);

            $this->setFlash('success', "Berhasil menyetujui {$pendingCount} berkas administrasi sekaligus.");
        } else {
            $this->setFlash('info', 'Tidak ada berkas yang berstatus menunggu review.');
        }

        $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . ($isDeputi ? '/administrasi/review-ks' : '/administrasi/review-guru')));
    }

    public function approveKaryaIlmiah(): void {
        $this->requireRole('kepala_sekolah');
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Disetujui'; // Disetujui / Revisi
        $notes = trim($_POST['review_notes'] ?? '');
        $userId = Auth::id();

        Database::execute(
            "UPDATE karya_ilmiah_guru SET status = ?, reviewer_user_id = ?, review_notes = ?, reviewed_at = NOW() WHERE id = ?",
            [$status, $userId, $notes, $id]
        );

        $karya = Database::fetch("SELECT * FROM karya_ilmiah_guru WHERE id = ?", [$id]);
        if ($karya) {
            Notification::send(
                (int)$karya['user_id'],
                "Persetujuan {$karya['type']}",
                "Dokumen {$karya['type']} Anda '{$karya['title']}' telah di-{$status} oleh Kepala Sekolah.",
                $status === 'Disetujui' ? 'success' : 'warning',
                Auth::baseUrl() . ($karya['type'] === 'PTK' ? '/walas/ptk' : '/guru/best-practice')
            );
        }

        AuditLog::log('APPROVE_KARYA_ILMIAH', 'karya_ilmiah_guru', $id, null, ['status' => $status, 'notes' => $notes]);
        $this->setFlash('success', "Status dokumen berhasil diperbarui ({$status}). Capaian otomatis masuk ke AKFI.");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/administrasi/review-guru'));
    }

    public function downloadKaryaIlmiah(string $id): void {
        $this->requireAuth();
        $doc = Database::fetch("SELECT * FROM karya_ilmiah_guru WHERE id = ?", [(int)$id]);
        if (!$doc) {
            die('Dokumen tidak ditemukan.');
        }

        $userId = Auth::id();
        $userUnitId = Auth::unitId();
        $role = Auth::activeRoleName();

        $canDownload = ((int)$doc['user_id'] === $userId)
            || in_array($role, ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan'], true)
            || (in_array($role, ['kepala_sekolah', 'operator_sekolah', 'waka_kurikulum'], true) && (int)$doc['unit_id'] === $userUnitId);

        if (!$canDownload) {
            die('Akses Ditolak.');
        }

        Uploader::stream($doc['file_path'], $doc['original_filename']);
    }

    public function approveDocument(): void {
        $this->requireRole(['deputi_pendidikan', 'kepala_sekolah']);
        $id = (int)($_POST['id'] ?? 0);
        $status = $_POST['status'] ?? 'Disetujui'; // Disetujui / Revisi
        $notes = trim($_POST['review_notes'] ?? '');
        $userId = Auth::id();

        Database::execute(
            "UPDATE administrasi_uploads SET status = ?, reviewer_user_id = ?, review_notes = ?, reviewed_at = NOW() WHERE id = ?",
            [$status, $userId, $notes, $id]
        );

        AuditLog::log('APPROVE_ADMINISTRASI', 'administrasi_uploads', $id, null, ['status' => $status, 'notes' => $notes]);
        $this->setFlash('success', "Status dokumen berhasil diperbarui ({$status}).");
        $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard'));
    }

    public function downloadDocument(string $id): void {
        $this->requireAuth();
        $doc = Database::fetch("SELECT * FROM administrasi_uploads WHERE id = ?", [(int)$id]);
        if (!$doc) {
            http_response_code(404);
            die('Dokumen tidak ditemukan.');
        }

        // Cek IDOR / Hak Akses:
        // Pengunggah dokumen, atau Kepsek di unit yang sama, atau Deputi/Direktur/Operator Yayasan
        $userId = Auth::id();
        $userUnitId = Auth::unitId();
        $role = Auth::activeRoleName();

        $canDownload = ((int)$doc['user_id'] === $userId)
            || in_array($role, ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan'], true)
            || (in_array($role, ['kepala_sekolah', 'operator_sekolah', 'waka_kurikulum'], true) && (int)$doc['unit_id'] === $userUnitId);

        if (!$canDownload) {
            http_response_code(403);
            die('Akses Ditolak: Anda tidak memiliki hak akses untuk mengunduh dokumen ini.');
        }

        Uploader::stream($doc['file_path'], $doc['original_filename']);
    }

    public function deadlines(): void {
        $this->requireRole(['waka_kurikulum', 'deputi_pendidikan', 'kepala_sekolah', 'direktur_pendidikan', 'operator_yayasan']);
        $role = Auth::activeRoleName();
        $taId = Auth::academicYearId();
        $userUnitId = Auth::unitId();

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$userUnitId]);
        $unitLevel = $unit['level'] ?? 'SD';

        $selectedUnitId = $userUnitId;
        $units = [];

        if (in_array($role, ['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan'], true)) {
            $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
            $selectedUnitId = (int)($_GET['unit_id'] ?? ($units[0]['id'] ?? $userUnitId));
            $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$selectedUnitId]);
            $unitLevel = $unit['level'] ?? 'SD';
        }

        // Query master items filtered by permission
        $query = "SELECT m.*, uad.due_info as unit_due_info, uad.deadline_date,
                         COALESCE(uad.due_info, m.due_info) as effective_due_info
                  FROM master_jenis_administrasi m
                  LEFT JOIN unit_administrasi_deadlines uad 
                       ON uad.jenis_admin_id = m.id 
                       AND uad.unit_id = ? 
                       AND uad.academic_year_id = ?
                  WHERE 1=1 ";
        $params = [$selectedUnitId, $taId];

        if ($role === 'waka_kurikulum') {
            // Waka Kurikulum TIDAK BISA set deadline untuk atasannya (Kepala Sekolah)
            $query .= " AND m.target_role != 'KEPALA_SEKOLAH' ";
            // Filter target_role sesuai level unit yang ditugaskan
            if ($unitLevel === 'TK') {
                $query .= " AND m.target_role IN ('GURU_TK', 'WALAS') ";
            } else {
                $query .= " AND m.target_role IN ('GURU_MAPEL', 'WALAS') ";
            }
        } elseif ($role === 'kepala_sekolah') {
            // Kepala Sekolah tidak set deadline untuk dirinya sendiri (diset oleh Deputi Yayasan)
            $query .= " AND m.target_role != 'KEPALA_SEKOLAH' ";
            if ($unitLevel === 'TK') {
                $query .= " AND m.target_role IN ('GURU_TK', 'WALAS', 'TU') ";
            } else {
                $query .= " AND m.target_role IN ('GURU_MAPEL', 'WALAS', 'TU') ";
            }
        }

        $query .= " ORDER BY m.target_role ASC, m.order_num ASC ";
        $masterItems = Database::fetchAll($query, $params);

        $this->view('administrasi/deadlines', [
            'pageTitle'      => 'Atur Batas Waktu (Deadline) Pengumpulan Administrasi',
            'masterItems'    => $masterItems,
            'selectedUnitId' => $selectedUnitId,
            'units'          => $units,
            'unit'           => $unit,
            'role'           => $role
        ]);
    }

    public function saveDeadline(): void {
        $this->requireRole(['waka_kurikulum', 'deputi_pendidikan', 'kepala_sekolah', 'direktur_pendidikan', 'operator_yayasan']);
        $role = Auth::activeRoleName();
        $taId = Auth::academicYearId();
        $userUnitId = Auth::unitId();
        $userId = Auth::id();

        $jenisId = (int)($_POST['jenis_admin_id'] ?? 0);
        $dueInfo = trim($_POST['due_info'] ?? '');
        $deadlineDate = !empty($_POST['deadline_date']) ? $_POST['deadline_date'] : null;

        $item = Database::fetch("SELECT * FROM master_jenis_administrasi WHERE id = ?", [$jenisId]);
        if (!$item) {
            $this->setFlash('danger', 'Jenis administrasi tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/administrasi/deadlines');
            return;
        }

        if ($role === 'waka_kurikulum') {
            $targetUnitId = $userUnitId; // Selalu unit yang ditugaskan saja
            if ($item['target_role'] === 'KEPALA_SEKOLAH') {
                $this->setFlash('danger', 'Akses Ditolak: Wakil Kurikulum tidak memiliki wewenang mengatur deadline administrasi Kepala Sekolah.');
                $this->redirect(Auth::baseUrl() . '/administrasi/deadlines');
                return;
            }
        } elseif ($role === 'kepala_sekolah') {
            $targetUnitId = $userUnitId;
            if ($item['target_role'] === 'KEPALA_SEKOLAH') {
                $this->setFlash('danger', 'Akses Ditolak: Deadline administrasi Kepala Sekolah diatur oleh Deputi / Yayasan.');
                $this->redirect(Auth::baseUrl() . '/administrasi/deadlines');
                return;
            }
        } else {
            $targetUnitId = (int)($_POST['unit_id'] ?? $userUnitId);
        }

        if ($jenisId > 0 && !empty($dueInfo)) {
            Database::execute(
                "INSERT INTO unit_administrasi_deadlines 
                    (unit_id, academic_year_id, jenis_admin_id, due_info, deadline_date, updated_by_user_id)
                 VALUES (?, ?, ?, ?, ?, ?)
                 ON DUPLICATE KEY UPDATE 
                    due_info = VALUES(due_info), deadline_date = VALUES(deadline_date), updated_by_user_id = VALUES(updated_by_user_id)",
                [$targetUnitId, $taId, $jenisId, $dueInfo, $deadlineDate, $userId]
            );

            AuditLog::log('UPDATE_UNIT_DEADLINE', 'unit_administrasi_deadlines', $targetUnitId, null, [
                'jenis_admin_id' => $jenisId,
                'due_info'       => $dueInfo,
                'deadline_date'  => $deadlineDate
            ]);
            $this->setFlash('success', 'Batas waktu administrasi untuk unit berhasil disimpan.');
        }
        $redirectUrl = Auth::baseUrl() . '/administrasi/deadlines';
        if (!empty($_POST['unit_id']) && in_array($role, ['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan'], true)) {
            $redirectUrl .= '?unit_id=' . $targetUnitId;
        }
        $this->redirect($redirectUrl);
    }
}

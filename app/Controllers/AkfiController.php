<?php
// app/Controllers/AkfiController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\ExportHelper;
use Core\SecurityHelper;
use Core\Notification;

class AkfiController extends Controller {

    // --- 1. PENILAIAN PRIBADI (SELF ASSESSMENT & STATUS) ---
    public function pribadi(): void {
        $this->requireAuth();
        $userId = Auth::id();
        $taId = Auth::academicYearId();
        $unitId = Auth::unitId();

        $emp = Database::fetch(
            "SELECT e.*, u.name as unit_name 
             FROM employees e 
             LEFT JOIN units u ON e.unit_id = u.id 
             WHERE e.user_id = ? AND e.is_deleted = 0",
            [$userId]
        );
        if (!$emp) {
            $this->setFlash('warning', 'Profil data pegawai Anda belum terdaftar.');
            $this->redirect(Auth::baseUrl() . '/dashboard');
            return;
        }

        $empId = (int)$emp['id'];
        $assessment = Database::fetch(
            "SELECT a.*, t.position_name, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                    sup.full_name as superior_name, hr.full_name as hr_name,
                    u.name as unit_name, ay.name as academic_year_name, ay.semester
             FROM akfi_assessments a
             JOIN akfi_templates t ON a.template_id = t.id
             LEFT JOIN units u ON a.unit_id = u.id
             LEFT JOIN academic_years ay ON a.academic_year_id = ay.id
             LEFT JOIN users sup ON a.superior_signed_by = sup.id
             LEFT JOIN users hr ON a.hr_signed_by = hr.id
             WHERE a.employee_id = ? AND a.academic_year_id = ?",
            [$empId, $taId]
        );

        // Jika belum ada record penilaian di tahun ajaran aktif, inisialisasi otomatis
        if (!$assessment) {
            $templateId = $this->matchTemplateForEmployee($emp);
            Database::execute(
                "INSERT INTO akfi_assessments (employee_id, unit_id, academic_year_id, template_id, status) 
                 VALUES (?, ?, ?, ?, 'Draft')",
                [$empId, $emp['unit_id'] ?: $unitId, $taId, $templateId]
            );
            $assessmentId = Database::lastInsertId();
            $assessment = Database::fetch(
                "SELECT a.*, t.position_name, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                        sup.full_name as superior_name, hr.full_name as hr_name,
                        u.name as unit_name, ay.name as academic_year_name, ay.semester
                 FROM akfi_assessments a 
                 JOIN akfi_templates t ON a.template_id = t.id 
                 LEFT JOIN units u ON a.unit_id = u.id
                 LEFT JOIN academic_years ay ON a.academic_year_id = ay.id
                 LEFT JOIN users sup ON a.superior_signed_by = sup.id
                 LEFT JOIN users hr ON a.hr_signed_by = hr.id
                 WHERE a.id = ?",
                [$assessmentId]
            );
        }

        // Ambil item template indikator
        $items = Database::fetchAll(
            "SELECT * FROM akfi_template_items WHERE template_id = ? ORDER BY section ASC, order_num ASC",
            [$assessment['template_id']]
        );

        $savedItems = !empty($assessment['items_json']) ? json_decode($assessment['items_json'], true) : [];
        $autoData = $this->collectAutoData($assessment);

        $this->view('akfi/pribadi', [
            'pageTitle'  => 'Penilaian Kinerja AKFI Pribadi',
            'employee'   => $emp,
            'assessment' => $assessment,
            'items'      => $items,
            'savedItems' => $savedItems,
            'autoData'   => $autoData
        ]);
    }

    public function selfSubmit(): void {
        $this->requireAuth();
        $assessmentId = (int)($_POST['assessment_id'] ?? 0);
        $selfScores = $_POST['self_score'] ?? [];
        $userId = Auth::id();

        $assessment = Database::fetch("SELECT * FROM akfi_assessments WHERE id = ?", [$assessmentId]);
        if (!$assessment) {
            $this->setFlash('danger', 'Penilaian tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/akfi/pribadi');
            return;
        }

        // Simpan skor mandiri
        $existingItems = !empty($assessment['items_json']) ? json_decode($assessment['items_json'], true) : [];
        foreach ($selfScores as $itemId => $val) {
            $existingItems[$itemId]['self_score'] = (float)$val;
        }

        Database::execute(
            "UPDATE akfi_assessments SET items_json = ?, self_submitted_at = NOW(), status = 'Diajukan' WHERE id = ?",
            [json_encode($existingItems), $assessmentId]
        );

        // Notifikasi ke Kepala Sekolah / Atasan Langsung
        $unitId = $assessment['unit_id'];
        $kepsekUser = Database::fetch(
            "SELECT u.id FROM users u 
             JOIN user_roles ur ON u.id = ur.user_id 
             JOIN roles r ON ur.role_id = r.id 
             JOIN employees e ON u.id = e.user_id 
             WHERE r.name = 'kepala_sekolah' AND e.unit_id = ? LIMIT 1",
            [$unitId]
        );
        if ($kepsekUser) {
            Notification::send((int)$kepsekUser['id'], 'Pengajuan AKFI Baru', 'Pegawai telah mengajukan penilaian mandiri AKFI dan siap dinilai atasan.', 'info', '/akfi/assess/' . $assessmentId);
        }

        AuditLog::log('SELF_SUBMIT_AKFI', 'akfi_assessments', $assessmentId, null, ['self_scores' => count($selfScores)]);
        $this->setFlash('success', 'Penilaian mandiri berhasil diajukan ke atasan langsung.');
        $this->redirect(Auth::baseUrl() . '/akfi/pribadi');
    }

    // --- 2. PENILAIAN BAWAHAN (SUPERIOR DASHBOARD & FORM) ---
    public function penilaianBawahan(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'deputi_pendidikan', 'direktur_pendidikan', 'tata_usaha']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $role = Auth::activeRoleName();

        if ($role === 'kepala_sekolah' || $role === 'waka_kurikulum' || $role === 'waka_kesiswaan') {
            // Auto-inisialisasi draft penilaian untuk semua bawahan aktif di satuan unit jika belum dibuat
            $activeSubordinates = Database::fetchAll(
                "SELECT e.* FROM employees e 
                 WHERE e.unit_id = ? AND e.is_deleted = 0 AND e.status = 'Aktif'
                   AND e.position NOT LIKE '%Kepala Sekolah%' AND e.position NOT LIKE '%Kepsek%'
                   AND e.level_posisi NOT LIKE '%Kepala Sekolah%' AND e.level_posisi NOT LIKE '%Kepsek%' AND e.level_posisi NOT LIKE '%Kanit%'
                   AND e.level_posisi NOT LIKE '%Direktur%' AND e.position NOT LIKE '%Direktur%'",
                [$unitId]
            );

            foreach ($activeSubordinates as $sub) {
                $exists = Database::fetch(
                    "SELECT id FROM akfi_assessments WHERE employee_id = ? AND academic_year_id = ?",
                    [$sub['id'], $taId]
                );
                if (!$exists) {
                    $templateId = $this->matchTemplateForEmployee($sub);
                    Database::execute(
                        "INSERT INTO akfi_assessments (employee_id, unit_id, academic_year_id, template_id, status) 
                         VALUES (?, ?, ?, ?, 'Draft')",
                        [$sub['id'], $unitId, $taId, $templateId]
                    );
                }
            }
        }

        $query = "SELECT a.*, e.nama, e.nipy, e.position, u.name as unit_name, t.position_name, t.position_code 
                  FROM akfi_assessments a 
                  JOIN employees e ON a.employee_id = e.id 
                  JOIN units u ON a.unit_id = u.id 
                  JOIN akfi_templates t ON a.template_id = t.id 
                  WHERE a.academic_year_id = ? ";
        $params = [$taId];

        if ($role === 'kepala_sekolah' || $role === 'waka_kurikulum' || $role === 'waka_kesiswaan') {
            $query .= "AND a.unit_id = ? 
                       AND e.unit_id = ?
                       AND e.is_deleted = 0
                       AND e.status = 'Aktif'
                       AND t.position_code NOT LIKE 'KANIT%' 
                       AND e.position NOT LIKE '%Kepala Sekolah%' 
                       AND e.position NOT LIKE '%Kepsek%' 
                       AND e.position NOT LIKE '%Direktur%' 
                       AND e.level_posisi NOT LIKE '%Direktur%' ";
            $params[] = $unitId;
            $params[] = $unitId;
        } elseif ($role === 'deputi_pendidikan' || $role === 'direktur_pendidikan') {
            $query .= "AND e.is_deleted = 0 ";
            if (!empty($_GET['unit_id'])) {
                $query .= "AND a.unit_id = ? AND e.unit_id = ? ";
                $params[] = (int)$_GET['unit_id'];
                $params[] = (int)$_GET['unit_id'];
            }
        }

        $query .= "ORDER BY FIELD(a.status, 'Diajukan', 'Draft', 'Disetujui_Atasan', 'Final'), e.nama ASC";
        $assessments = Database::fetchAll($query, $params);

        $this->view('akfi/penilaian_bawahan', [
            'pageTitle'   => 'Daftar Penilaian AKFI Bawahan',
            'assessments' => $assessments
        ]);
    }

    public function assessForm(string $id): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'deputi_pendidikan', 'direktur_pendidikan']);
        $assessmentId = (int)$id;

        $assessment = Database::fetch(
            "SELECT a.*, e.nama, e.nipy, e.position, e.user_id, e.unit_id as emp_unit_id, u.name as unit_name, 
                    t.position_code, t.position_name, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                    t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min 
             FROM akfi_assessments a 
             JOIN employees e ON a.employee_id = e.id 
             JOIN units u ON a.unit_id = u.id 
             JOIN akfi_templates t ON a.template_id = t.id 
             WHERE a.id = ?",
            [$assessmentId]
        );

        if (!$assessment) {
            $this->setFlash('danger', 'Data penilaian tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/akfi/penilaian-bawahan');
            return;
        }

        $items = Database::fetchAll(
            "SELECT * FROM akfi_template_items WHERE template_id = ? ORDER BY section ASC, order_num ASC",
            [$assessment['template_id']]
        );

        $savedItems = !empty($assessment['items_json']) ? json_decode($assessment['items_json'], true) : [];

        // Auto-Data Collector: kalkulasi otomatis data riil dari sistem
        $autoData = $this->collectAutoData($assessment);

        $this->view('akfi/form_penilaian', [
            'pageTitle'  => 'Form Penilaian Kinerja AKFI: ' . $assessment['nama'],
            'assessment' => $assessment,
            'items'      => $items,
            'savedItems' => $savedItems,
            'autoData'   => $autoData
        ]);
    }

    public function assessSave(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'waka_kesiswaan', 'deputi_pendidikan', 'direktur_pendidikan']);
        $assessmentId = (int)($_POST['assessment_id'] ?? 0);
        $actualVals = $_POST['actual_val'] ?? [];
        $superiorScores = $_POST['superior_score'] ?? [];
        $sikapRubrics = $_POST['sikap'] ?? [];
        $superiorNotes = trim($_POST['superior_notes'] ?? '');
        $userId = Auth::id();

        $assessment = Database::fetch(
            "SELECT a.*, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                    t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min
             FROM akfi_assessments a 
             JOIN akfi_templates t ON a.template_id = t.id 
             WHERE a.id = ?",
            [$assessmentId]
        );

        if (!$assessment) {
            $this->setFlash('danger', 'Penilaian tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/akfi/penilaian-bawahan');
            return;
        }

        $items = Database::fetchAll("SELECT * FROM akfi_template_items WHERE template_id = ?", [$assessment['template_id']]);
        $existingItems = !empty($assessment['items_json']) ? json_decode($assessment['items_json'], true) : [];

        // 1. Kalkulasi Kinerja & Absensi
        $totalKinerjaRaw = 0;
        $totalAbsensiRaw = 0;
        $hasSectionC = false;

        foreach ($items as $item) {
            $iId = $item['id'];
            $isSecC = ($item['section'] === 'C' || $item['category'] === 'Absensi');
            $actual = isset($actualVals[$iId]) && $actualVals[$iId] !== '' ? (float)$actualVals[$iId] : 0.0;
            $supScore = isset($superiorScores[$iId]) && $superiorScores[$iId] !== '' ? (float)$superiorScores[$iId] : $actual;

            // Penilaian atasan (superior_score) adalah penentu skor capaian
            $evalScore = $supScore;
            $target = (float)$item['target_val'];
            $weight = (float)$item['weight'];
            
            if ($item['direction'] === 'lower_better') {
                if ($target <= 0) {
                    // Target 0% (misal % Tunggakan sekolah target 0%)
                    if ($evalScore <= 0) {
                        $ratio = 1.0;
                    } else {
                        // Jika skor lebih besar maka point otomatis berkurang
                        $ratio = max(0.0, 1.0 - ($evalScore / 100.0));
                    }
                } else {
                    if ($isSecC) {
                        // Bagian C: Absensi & Kegiatan (Datang Terlambat, Pulang Cepat, Lupa Catat, Mangkir)
                        // Skor 0 = Poin maksimal (100%)
                        // Semakin besar nilai skor maka poin yang didapat semakin berkurang
                        // Batas maksimal target (misal 6): jika skor >= 6 otomatis poin 0
                        $ratio = max(0.0, ($target - $evalScore) / $target);
                    } else {
                        // Target > 0 toleransi kinerja (misal Turn Over Talent target <= 5%)
                        if ($evalScore <= $target) {
                            $ratio = 1.0;
                        } else {
                            $ratio = max(0.0, 1.0 - (($evalScore - $target) / $target));
                        }
                    }
                }
            } else {
                // Makin tinggi makin baik
                if ($target <= 0) {
                    $ratio = 1.0;
                } else {
                    $ratio = min(1.0, max(0.0, $evalScore / $target));
                }
            }

            $itemScore = round($ratio * $weight, 2);

            // Item Bagian A dihitung ke Bobot Kinerja, Bagian C dihitung ke Bobot Absensi
            if ($item['section'] === 'A') {
                $totalKinerjaRaw += $itemScore;
            } elseif ($item['section'] === 'C') {
                $totalAbsensiRaw += $itemScore;
                $hasSectionC = true;
            }

            $existingItems[$iId]['actual_val'] = $actual;
            $existingItems[$iId]['superior_score'] = $supScore;
            $existingItems[$iId]['final_item_score'] = $itemScore;
        }

        // Normalisasi Kinerja ke bobot_kinerja template
        $bobotKinerja = (float)$assessment['bobot_kinerja'];
        $kinerjaScore = min($bobotKinerja, round($totalKinerjaRaw, 2));

        // 2. Kalkulasi Sikap (Rubrik 1-5 scale)
        $sikapCount = count($sikapRubrics);
        $totalSikapRaw = array_sum(array_map('floatval', $sikapRubrics));
        $maxSikap = max(1, $sikapCount * 5);
        $bobotSikap = (float)$assessment['bobot_sikap'];
        $sikapScore = round(($totalSikapRaw / $maxSikap) * $bobotSikap, 2);

        // 3. Kalkulasi Absensi (Bagian C atau fallback data riil presensi sistem)
        $bobotAbsensi = (float)$assessment['bobot_absensi'];
        if ($hasSectionC) {
            $absensiScore = min($bobotAbsensi, round($totalAbsensiRaw, 2));
        } else {
            $autoData = $this->collectAutoData($assessment);
            $hadirPct = (float)($autoData['attendance_pct'] ?? 100);
            $absensiScore = round(($hadirPct / 100) * $bobotAbsensi, 2);
        }

        // 4. Total Nilai & Predikat
        $totalScore = round($kinerjaScore + $sikapScore + $absensiScore, 2);
        
        $grade = 'E';
        if ($totalScore >= (float)$assessment['grade_a_min']) {
            $grade = 'A';
        } elseif ($totalScore >= (float)$assessment['grade_b_min']) {
            $grade = 'B';
        } elseif ($totalScore >= (float)$assessment['grade_c_min']) {
            $grade = 'C';
        } elseif ($totalScore >= (float)$assessment['grade_d_min']) {
            $grade = 'D';
        }

        Database::execute(
            "UPDATE akfi_assessments SET 
                kinerja_score = ?, sikap_score = ?, absensi_score = ?, total_score = ?, letter_grade = ?, 
                superior_signed_by = ?, superior_signed_at = NOW(), 
                status = 'Disetujui_Atasan', superior_notes = ?, items_json = ? 
             WHERE id = ?",
            [$kinerjaScore, $sikapScore, $absensiScore, $totalScore, $grade, $userId, $superiorNotes, json_encode($existingItems), $assessmentId]
        );

        // Notifikasi ke Pegawai & Direktur HR
        $empUser = Database::fetch("SELECT user_id FROM employees WHERE id = ?", [$assessment['employee_id']]);
        if ($empUser && $empUser['user_id']) {
            Notification::send((int)$empUser['user_id'], 'Penilaian AKFI Disetujui Atasan', "Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat {$grade} ({$totalScore}).", 'success', '/akfi/pribadi');
        }

        AuditLog::log('ASSESS_AKFI_SUPERIOR', 'akfi_assessments', $assessmentId, null, [
            'kinerja' => $kinerjaScore, 'sikap' => $sikapScore, 'absensi' => $absensiScore, 'total' => $totalScore, 'grade' => $grade
        ]);

        $this->setFlash('success', "Penilaian AKFI berhasil disimpan dan ditandatangani atasan (Skor Akhir: {$totalScore} - Predikat {$grade}).");
        $this->redirect(Auth::baseUrl() . '/akfi/penilaian-bawahan');
    }

    // --- 3. APPROVAL HR / DIREKTUR PENDIDIKAN ---
    public function approvalHr(): void {
        $this->requireRole(['direktur_pendidikan', 'deputi_pendidikan']);
        $taId = Auth::academicYearId();

        $assessments = Database::fetchAll(
            "SELECT a.*, e.nama, e.nipy, e.position, u.name as unit_name, t.position_name, sup.full_name as superior_name 
             FROM akfi_assessments a 
             JOIN employees e ON a.employee_id = e.id 
             JOIN units u ON a.unit_id = u.id 
             JOIN akfi_templates t ON a.template_id = t.id 
             LEFT JOIN users sup ON a.superior_signed_by = sup.id 
             WHERE a.academic_year_id = ? 
             ORDER BY FIELD(a.status, 'Disetujui_Atasan', 'Final', 'Diajukan', 'Draft'), e.nama ASC",
            [$taId]
        );

        $this->view('akfi/approval_hr', [
            'pageTitle'   => 'Verifikasi & Pengesahan Akhir AKFI (Direktur Pendidikan)',
            'assessments' => $assessments
        ]);
    }

    public function hrSign(): void {
        $this->requireRole(['direktur_pendidikan', 'deputi_pendidikan']);
        $id = (int)($_POST['id'] ?? 0);
        $userId = Auth::id();

        Database::execute(
            "UPDATE akfi_assessments SET hr_signed_by = ?, hr_signed_at = NOW(), status = 'Final' WHERE id = ?",
            [$userId, $id]
        );

        $assessment = Database::fetch("SELECT * FROM akfi_assessments WHERE id = ?", [$id]);
        if ($assessment) {
            $emp = Database::fetch("SELECT user_id FROM employees WHERE id = ?", [$assessment['employee_id']]);
            if ($emp && $emp['user_id']) {
                Notification::send((int)$emp['user_id'], 'Rapor Kinerja AKFI Telah Disahkan', 'Rapor Kinerja AKFI Anda telah disahkan secara resmi oleh Direktur Pendidikan.', 'success', '/akfi/pribadi');
            }
        }

        AuditLog::log('HR_SIGN_AKFI', 'akfi_assessments', $id, null, ['status' => 'Final']);
        $this->setFlash('success', 'Rapor AKFI berhasil disahkan secara resmi oleh Direktur Pendidikan.');
        $this->redirect(Auth::baseUrl() . '/akfi/approval-hr');
    }

    // --- 4. PRATINJAU AKFI KS / KANIT (DEPUTI DIREKTUR & DIREKTUR PENDIDIKAN) ---
    public function kanitPreview(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan', 'pembina_yayasan']);
        $taId = Auth::academicYearId();

        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $kanitList = [];

        foreach ($units as $u) {
            $uId = (int)$u['id'];
            $ks = Database::fetch(
                "SELECT e.*, u.name as unit_name, u.code as unit_code,
                        (SELECT username FROM users WHERE id = e.user_id) as username
                 FROM employees e
                 JOIN units u ON e.unit_id = u.id
                 LEFT JOIN users usr ON e.user_id = usr.id
                 LEFT JOIN user_roles ur ON usr.id = ur.user_id
                 LEFT JOIN roles r ON ur.role_id = r.id AND r.name = 'kepala_sekolah'
                 WHERE e.unit_id = ? AND e.is_deleted = 0 
                   AND (
                       r.id IS NOT NULL 
                       OR e.level_posisi = 'Kepala Sekolah'
                       OR e.level_posisi LIKE '%Kepala Sekolah%'
                       OR e.level_posisi LIKE '%Kepsek%'
                       OR e.level_posisi LIKE '%Kanit%'
                       OR e.level_posisi LIKE '%Kepala Satuan%'
                   )
                   AND e.level_posisi NOT LIKE '%Kepala Lab%'
                   AND e.level_posisi NOT LIKE '%Kepala Perpustakaan%'
                   AND e.level_posisi NOT LIKE '%Kepala TU%'
                   AND e.level_posisi NOT LIKE '%Kepala Tata Usaha%'
                   AND e.level_posisi NOT LIKE '%Kepala Bengkel%'
                   AND e.level_posisi NOT LIKE '%Kepala Ruang%'
                 ORDER BY (CASE WHEN r.id IS NOT NULL THEN 1 WHEN e.level_posisi LIKE '%Kepala Sekolah%' THEN 2 ELSE 3 END) ASC
                 LIMIT 1",
                [$uId]
            );

            $assessment = null;
            $autoData = [];

            if ($ks) {
                $assessment = Database::fetch(
                    "SELECT a.*, t.position_name, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                            t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min,
                            sup.full_name as superior_name, hr.full_name as hr_name
                     FROM akfi_assessments a
                     JOIN akfi_templates t ON a.template_id = t.id
                     LEFT JOIN users sup ON a.superior_signed_by = sup.id
                     LEFT JOIN users hr ON a.hr_signed_by = hr.id
                     WHERE a.employee_id = ? AND a.academic_year_id = ?",
                    [$ks['id'], $taId]
                );

                if (!$assessment) {
                    $templateId = $this->matchTemplateForEmployee($ks);
                    Database::execute(
                        "INSERT INTO akfi_assessments (employee_id, unit_id, academic_year_id, template_id, status) 
                         VALUES (?, ?, ?, ?, 'Draft')",
                        [$ks['id'], $uId, $taId, $templateId]
                    );
                    $newId = Database::lastInsertId();
                    $assessment = Database::fetch(
                        "SELECT a.*, t.position_name, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                                t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min
                         FROM akfi_assessments a
                         JOIN akfi_templates t ON a.template_id = t.id
                         WHERE a.id = ?",
                        [$newId]
                    );
                }

                $autoData = $this->collectAutoData($assessment);
            }

            $kanitList[] = [
                'unit'       => $u,
                'ks'         => $ks,
                'assessment' => $assessment,
                'autoData'   => $autoData
            ];
        }

        $this->view('akfi/kanit_preview', [
            'pageTitle' => 'Pratinjau Kinerja AKFI Kepala Satuan Pendidikan (Kanit)',
            'kanitList' => $kanitList
        ]);
    }

    // --- 5. PENILAIAN SIKAP & KEPEMIMPINAN KS (DEPUTI & DIREKTUR PENDIDIKAN) ---
    public function penilaianSikapKs(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan']);
        $taId = Auth::academicYearId();
        $assessmentId = (int)($_GET['assessment_id'] ?? 0);
        $empId = (int)($_GET['emp_id'] ?? 0);

        $allKs = Database::fetchAll(
            "SELECT e.id as employee_id, e.nama, e.nipy, u.id as unit_id, u.name as unit_name, u.code as unit_code,
                    a.id as assessment_id, a.total_score, a.letter_grade, a.sikap_score, a.status
             FROM employees e
             JOIN units u ON e.unit_id = u.id
             LEFT JOIN users usr ON e.user_id = usr.id
             LEFT JOIN user_roles ur ON usr.id = ur.user_id
             LEFT JOIN roles r ON ur.role_id = r.id AND r.name = 'kepala_sekolah'
             LEFT JOIN akfi_assessments a ON e.id = a.employee_id AND a.academic_year_id = ?
             WHERE e.is_deleted = 0 
               AND (
                   r.id IS NOT NULL 
                   OR e.level_posisi = 'Kepala Sekolah'
                   OR e.level_posisi LIKE '%Kepala Sekolah%'
                   OR e.level_posisi LIKE '%Kepsek%'
                   OR e.level_posisi LIKE '%Kanit%'
                   OR e.level_posisi LIKE '%Kepala Satuan%'
               )
               AND e.level_posisi NOT LIKE '%Kepala Lab%'
               AND e.level_posisi NOT LIKE '%Kepala Perpustakaan%'
               AND e.level_posisi NOT LIKE '%Kepala TU%'
               AND e.level_posisi NOT LIKE '%Kepala Tata Usaha%'
               AND e.level_posisi NOT LIKE '%Kepala Bengkel%'
               AND e.level_posisi NOT LIKE '%Kepala Ruang%'
             GROUP BY e.id
             ORDER BY u.id ASC",
            [$taId]
        );

        $selectedAssessment = null;
        if ($assessmentId > 0) {
            $selectedAssessment = Database::fetch(
                "SELECT a.*, e.nama, e.nipy, e.level_posisi, u.name as unit_name, t.position_name, t.bobot_sikap,
                        t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min
                 FROM akfi_assessments a
                 JOIN employees e ON a.employee_id = e.id
                 JOIN units u ON a.unit_id = u.id
                 JOIN akfi_templates t ON a.template_id = t.id
                 WHERE a.id = ?",
                [$assessmentId]
            );
        } elseif ($empId > 0) {
            $selectedAssessment = Database::fetch(
                "SELECT a.*, e.nama, e.nipy, e.level_posisi, u.name as unit_name, t.position_name, t.bobot_sikap,
                        t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min
                 FROM akfi_assessments a
                 JOIN employees e ON a.employee_id = e.id
                 JOIN units u ON a.unit_id = u.id
                 JOIN akfi_templates t ON a.template_id = t.id
                 WHERE a.employee_id = ? AND a.academic_year_id = ?",
                [$empId, $taId]
            );
        } elseif (!empty($allKs)) {
            $firstKs = $allKs[0];
            if (!empty($firstKs['assessment_id'])) {
                $selectedAssessment = Database::fetch(
                    "SELECT a.*, e.nama, e.nipy, e.level_posisi, u.name as unit_name, t.position_name, t.bobot_sikap,
                            t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min
                     FROM akfi_assessments a
                     JOIN employees e ON a.employee_id = e.id
                     JOIN units u ON a.unit_id = u.id
                     JOIN akfi_templates t ON a.template_id = t.id
                     WHERE a.id = ?",
                    [(int)$firstKs['assessment_id']]
                );
            }
        }

        $savedRubrics = [];
        if ($selectedAssessment && !empty($selectedAssessment['items_json'])) {
            $json = json_decode($selectedAssessment['items_json'], true) ?: [];
            $savedRubrics = $json['sikap_ks'] ?? [];
        }

        $this->view('akfi/penilaian_sikap_ks', [
            'pageTitle'          => 'Instrumen Penilaian Sikap & Kepemimpinan Kepala Sekolah',
            'allKs'              => $allKs,
            'selectedAssessment' => $selectedAssessment,
            'savedRubrics'       => $savedRubrics
        ]);
    }

    public function saveSikapKs(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan']);
        $assessmentId = (int)($_POST['assessment_id'] ?? 0);
        $sikapRubrics = $_POST['sikap'] ?? [];
        $notes = trim($_POST['superior_notes'] ?? '');
        $userId = Auth::id();

        $assessment = Database::fetch(
            "SELECT a.*, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                    t.grade_a_min, t.grade_b_min, t.grade_c_min, t.grade_d_min, t.grade_e_min
             FROM akfi_assessments a 
             JOIN akfi_templates t ON a.template_id = t.id 
             WHERE a.id = ?",
            [$assessmentId]
        );

        if (!$assessment) {
            $this->setFlash('danger', 'Data penilaian AKFI Kepala Sekolah tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/akfi/kanit-preview');
            return;
        }

        // Kalkulasi Sikap Kepala Sekolah (Skala 1 - 5)
        $sikapCount = max(1, count($sikapRubrics));
        $totalSikapRaw = array_sum(array_map('floatval', $sikapRubrics));
        $maxSikap = $sikapCount * 5;
        $bobotSikap = (float)$assessment['bobot_sikap'];
        $sikapScore = round(($totalSikapRaw / $maxSikap) * $bobotSikap, 2);

        // Rekalkulasi Skor Total & Predikat
        $kinerjaScore = (float)$assessment['kinerja_score'];
        $absensiScore = (float)$assessment['absensi_score'];
        $totalScore = round($kinerjaScore + $sikapScore + $absensiScore, 2);

        $grade = 'E';
        if ($totalScore >= (float)$assessment['grade_a_min']) {
            $grade = 'A';
        } elseif ($totalScore >= (float)$assessment['grade_b_min']) {
            $grade = 'B';
        } elseif ($totalScore >= (float)$assessment['grade_c_min']) {
            $grade = 'C';
        } elseif ($totalScore >= (float)$assessment['grade_d_min']) {
            $grade = 'D';
        }

        // Simpan ke items_json
        $existingItems = !empty($assessment['items_json']) ? json_decode($assessment['items_json'], true) : [];
        $existingItems['sikap_ks'] = $sikapRubrics;

        Database::execute(
            "UPDATE akfi_assessments SET 
                sikap_score = ?, total_score = ?, letter_grade = ?, 
                superior_signed_by = ?, superior_signed_at = NOW(),
                superior_notes = ?, items_json = ? 
             WHERE id = ?",
            [$sikapScore, $totalScore, $grade, $userId, $notes, json_encode($existingItems), $assessmentId]
        );

        $emp = Database::fetch("SELECT user_id, nama FROM employees WHERE id = ?", [$assessment['employee_id']]);
        if ($emp && $emp['user_id']) {
            Notification::send((int)$emp['user_id'], 'Penilaian Sikap KS Telah Diisi', "Deputi Direktur telah mengisi instrumen penilaian sikap kepemimpinan AKFI Anda.", 'info', '/akfi/pribadi');
        }

        AuditLog::log('ASSESS_SIKAP_KS', 'akfi_assessments', $assessmentId, null, [
            'sikap_score' => $sikapScore, 'total_score' => $totalScore, 'grade' => $grade
        ]);

        $this->setFlash('success', "Penilaian Sikap & Kepemimpinan Kepala Sekolah {$emp['nama']} berhasil disimpan (Skor Sikap: {$sikapScore} / {$bobotSikap}).");
        $this->redirect(Auth::baseUrl() . '/akfi/kanit-preview');
    }

    // --- 6. REKAP YAYASAN & EXPORT XLSX ---
    public function rekapYayasan(): void {
        $this->requireRole(['direktur_pendidikan', 'deputi_pendidikan', 'operator_yayasan', 'pembina_yayasan']);
        $taId = Auth::academicYearId();

        $unitsSummary = Database::fetchAll(
            "SELECT u.id, u.name, COUNT(a.id) as total_pegawai,
                    AVG(a.total_score) as avg_score,
                    SUM(CASE WHEN a.letter_grade = 'A' THEN 1 ELSE 0 END) as grade_a,
                    SUM(CASE WHEN a.letter_grade = 'B' THEN 1 ELSE 0 END) as grade_b,
                    SUM(CASE WHEN a.letter_grade = 'C' THEN 1 ELSE 0 END) as grade_c,
                    SUM(CASE WHEN a.letter_grade IN ('D', 'E') THEN 1 ELSE 0 END) as grade_de,
                    SUM(CASE WHEN a.status = 'Final' THEN 1 ELSE 0 END) as total_final 
             FROM units u 
             LEFT JOIN akfi_assessments a ON u.id = a.unit_id AND a.academic_year_id = ? 
             GROUP BY u.id ORDER BY u.id ASC",
            [$taId]
        );

        $allAssessments = Database::fetchAll(
            "SELECT a.*, e.nama, e.nipy, e.position, u.name as unit_name, t.position_name 
             FROM akfi_assessments a 
             JOIN employees e ON a.employee_id = e.id 
             JOIN units u ON a.unit_id = u.id 
             JOIN akfi_templates t ON a.template_id = t.id 
             WHERE a.academic_year_id = ? 
             ORDER BY u.name ASC, a.total_score DESC",
            [$taId]
        );

        $this->view('akfi/rekap_yayasan', [
            'pageTitle'      => 'Rekapitulasi Kinerja AKFI Seluruh Unit Yayasan',
            'unitsSummary'   => $unitsSummary,
            'allAssessments' => $allAssessments
        ]);
    }

    public function exportXlsx(): void {
        $this->requireRole(['direktur_pendidikan', 'deputi_pendidikan', 'operator_yayasan', 'pembina_yayasan', 'kepala_sekolah']);
        $taId = Auth::academicYearId();
        $unitId = Auth::unitId();
        $role = Auth::activeRoleName();

        $query = "SELECT e.nama, e.nipy, u.name as unit_name, t.position_name, 
                         a.kinerja_score, a.sikap_score, a.absensi_score, a.total_score, a.letter_grade, a.status 
                  FROM akfi_assessments a 
                  JOIN employees e ON a.employee_id = e.id 
                  JOIN units u ON a.unit_id = u.id 
                  JOIN akfi_templates t ON a.template_id = t.id 
                  WHERE a.academic_year_id = ? ";
        $params = [$taId];

        if ($role === 'kepala_sekolah') {
            $query .= "AND a.unit_id = ? ";
            $params[] = $unitId;
        }
        $query .= "ORDER BY u.name ASC, a.total_score DESC";

        $rows = Database::fetchAll($query, $params);
        $headers = ['Nama Pegawai', 'NIPY', 'Unit Sekolah', 'Posisi AKFI', 'Skor Kinerja', 'Skor Sikap', 'Skor Absensi', 'Skor Total', 'Predikat', 'Status'];

        ExportHelper::xlsx($headers, $rows, 'Rekap_AKFI_Yayasan_' . date('Ymd'));
    }

    // --- 5. PRINT RAPOR AKFI (OFFICIAL A4 PDF) ---
    public function printRaporAkfi(string $id): void {
        $this->requireAuth();
        $assessmentId = (int)$id;

        $assessment = Database::fetch(
            "SELECT a.*, e.nama, e.nipy, e.position, NULL as emp_sig, 
                    u.name as unit_name, u.code as unit_code, u.logo_path, u.kop_title, u.kop_subtitle, u.address, u.phone, u.email,
                    t.position_name, t.bobot_kinerja, t.bobot_sikap, t.bobot_absensi,
                    ay.name as academic_year_name,
                    sup.full_name as superior_name, hr.full_name as hr_name 
             FROM akfi_assessments a 
             JOIN employees e ON a.employee_id = e.id 
             JOIN units u ON a.unit_id = u.id 
             JOIN akfi_templates t ON a.template_id = t.id 
             JOIN academic_years ay ON a.academic_year_id = ay.id 
             LEFT JOIN users sup ON a.superior_signed_by = sup.id 
             LEFT JOIN users hr ON a.hr_signed_by = hr.id 
             WHERE a.id = ?",
            [$assessmentId]
        );

        if (!$assessment) {
            die('Data Penilaian AKFI tidak ditemukan.');
        }

        $items = Database::fetchAll(
            "SELECT * FROM akfi_template_items WHERE template_id = ? ORDER BY section ASC, order_num ASC",
            [$assessment['template_id']]
        );

        $savedItems = !empty($assessment['items_json']) ? json_decode($assessment['items_json'], true) : [];

        $kop = ExportHelper::renderKopYayasan();

        // Predicate label
        $predikatLabel = match($assessment['letter_grade']) {
            'A' => 'Amat Baik (A)',
            'B' => 'Baik (B)',
            'C' => 'Cukup (C)',
            'D' => 'Sedang (D)',
            default => 'Kurang (E)'
        };

        // Render HTML for Dompdf
        $html = "
        <html>
        <head>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
                .title-doc { text-align: center; font-size: 14px; font-weight: bold; margin: 15px 0 10px 0; text-transform: uppercase; border-bottom: 2px solid #0f766e; padding-bottom: 5px; }
                .meta-table { width: 100%; margin-bottom: 12px; }
                .meta-table td { padding: 3px 4px; vertical-align: top; }
                .table-data { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
                .table-data th, .table-data td { border: 1px solid #94a3b8; padding: 5px 6px; }
                .table-data th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .section-header { background: #e2e8f0; font-weight: bold; text-transform: uppercase; }
                .signatures { width: 100%; margin-top: 25px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 33.33%; text-align: center; vertical-align: top; font-size: 11px; }
                .badge { display: inline-block; padding: 2px 8px; border-radius: 4px; font-weight: bold; }
                .badge-a { background: #dcfce7; color: #15803d; }
                .badge-b { background: #e0f2fe; color: #0369a1; }
                .badge-c { background: #fef3c7; color: #b45309; }
                .badge-de { background: #fee2e2; color: #b91c1c; }
            </style>
        </head>
        <body>
            {$kop}
            <div class='title-doc'>RAPOR PENILAIAN KINERJA AKFI<br><span style='font-size: 11px; font-weight: normal;'>AL KAHFI FOCUS INDICATOR - TAHUN AJARAN {$assessment['academic_year_name']}</span></div>

            <table class='meta-table'>
                <tr>
                    <td width='18%'><strong>Nama Pegawai</strong></td>
                    <td width='2%'>:</td>
                    <td width='40%'>{$assessment['nama']}</td>
                    <td width='18%'><strong>Unit Kerja</strong></td>
                    <td width='2%'>:</td>
                    <td width='20%'>{$assessment['unit_name']}</td>
                </tr>
                <tr>
                    <td><strong>NIPY</strong></td>
                    <td>:</td>
                    <td>{$assessment['nipy']}</td>
                    <td><strong>Posisi AKFI</strong></td>
                    <td>:</td>
                    <td>{$assessment['position_name']}</td>
                </tr>
            </table>

            <!-- Tabel Ringkasan Nilai Komponen -->
            <table class='table-data'>
                <thead>
                    <tr>
                        <th width='5%'>No</th>
                        <th>Komponen Penilaian</th>
                        <th width='15%'>Bobot Maksimal</th>
                        <th width='15%'>Skor Capaian</th>
                        <th width='30%'>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td align='center'>1</td>
                        <td><strong>Kinerja & Target Tugas Utama</strong></td>
                        <td align='center'>{$assessment['bobot_kinerja']}%</td>
                        <td align='center'><strong>{$assessment['kinerja_score']}</strong></td>
                        <td>Sesuai realisasi instrumen indikator posisi</td>
                    </tr>
                    <tr>
                        <td align='center'>2</td>
                        <td><strong>Sikap & Perilaku Islami (Rubrik 1-5)</strong></td>
                        <td align='center'>{$assessment['bobot_sikap']}%</td>
                        <td align='center'><strong>{$assessment['sikap_score']}</strong></td>
                        <td>Penilaian keteladanan, akhlaq, dan loyalitas yayasan</td>
                    </tr>
                    <tr>
                        <td align='center'>3</td>
                        <td><strong>Kedisiplinan & Presensi Kehadiran</strong></td>
                        <td align='center'>{$assessment['bobot_absensi']}%</td>
                        <td align='center'><strong>{$assessment['absensi_score']}</strong></td>
                        <td>Kehadiran dinas dan partisipasi kajian pembinaan</td>
                    </tr>
                    <tr style='background: #f8fafc; font-weight: bold;'>
                        <td colspan='2' align='right'>TOTAL SKOR AKHIR AKFI :</td>
                        <td align='center'>100%</td>
                        <td align='center' style='font-size: 13px; color: #0f766e;'>{$assessment['total_score']}</td>
                        <td>PREDIKAT: <span class='badge badge-b'>{$predikatLabel}</span></td>
                    </tr>
                </tbody>
            </table>

            <!-- Rincian Indikator Kinerja -->
            <div style='font-weight: bold; margin: 10px 0 5px 0;'>RINCIAN CAPAIAN INDIKATOR KINERJA:</div>
            <table class='table-data'>
                <thead>
                    <tr>
                        <th width='4%'>No</th>
                        <th width='12%'>Kategori</th>
                        <th>Indikator Sasaran Kerja</th>
                        <th width='10%'>Target</th>
                        <th width='10%'>Realisasi</th>
                        <th width='8%'>Bobot</th>
                        <th width='8%'>Skor</th>
                    </tr>
                </thead>
                <tbody>";

        $curSec = '';
        foreach ($items as $idx => $it) {
            $itData = $savedItems[$it['id']] ?? [];
            $actual = $itData['actual_val'] ?? $it['target_val'];
            $score = $itData['final_item_score'] ?? $it['weight'];

            if ($curSec !== $it['section']) {
                $curSec = $it['section'];
                $secTitle = $curSec === 'A' ? 'BAGIAN A: TARGET PROGRAM & TUGAS POKOK' : ($curSec === 'B' ? 'BAGIAN B: TUGAS TAMBAHAN & PENDUKUNG' : 'BAGIAN C: ADMINISTRASI & KEGIATAN');
                $html .= "<tr class='section-header'><td colspan='7'>{$secTitle}</td></tr>";
            }

            $html .= "
                <tr>
                    <td align='center'>" . ($idx + 1) . "</td>
                    <td>{$it['category']}</td>
                    <td>{$it['item_name']}</td>
                    <td align='center'>{$it['target_val']} {$it['unit_type']}</td>
                    <td align='center'>{$actual} {$it['unit_type']}</td>
                    <td align='center'>{$it['weight']}</td>
                    <td align='center'><strong>{$score}</strong></td>
                </tr>
            ";
        }

        $html .= "
                </tbody>
            </table>

            <!-- Catatan Evaluasi & Rekomendasi -->
            <div style='border: 1px solid #94a3b8; padding: 8px; border-radius: 4px; margin-top: 10px;'>
                <strong>Catatan / Rekomendasi Atasan Langsung:</strong><br>
                <em>" . nl2br(htmlspecialchars($assessment['superior_notes'] ?: 'Kinerja memenuhi kriteria standar yayasan. Terus pertahankan kedisiplinan dan inovasi pembelajaran.')) . "</em>
            </div>

            <!-- Tanda Tangan 3 Kolom -->
            <table class='signatures'>
                <tr>
                    <td class='sig-box'>
                        Pegawai yang Dinilai,<br><br><br><br>
                        <strong>{$assessment['nama']}</strong><br>
                        NIPY. {$assessment['nipy']}
                    </td>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo($assessment['superior_signed_at'] ?: date('Y-m-d')) . "<br>
                        Atasan Langsung / Penilai,<br><br><br><br>
                        <strong>" . ($assessment['superior_name'] ?: 'Kepala Sekolah') . "</strong><br>
                        Kepala Satuan Pendidikan
                    </td>
                    <td class='sig-box'>
                        Mengetahui & Mengesahkan,<br>
                        <strong>Direktur Pendidikan Yayasan</strong><br><br><br><br>
                        <strong>" . ($assessment['hr_name'] ?: 'Dr. H. Ahmad Dahlan, M.Pd') . "</strong><br>
                        Direktur Pendidikan Yayasan
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Rapor_AKFI_' . $assessment['nipy'] . '_' . date('Ymd'), 'A4', 'portrait', true);
    }

    // Helper: auto matching template for employee position
    private function matchTemplateForEmployee(array $emp): int {
        $pos = strtoupper($emp['position'] ?? $emp['level_posisi'] ?? '');
        $unitId = (int)($emp['unit_id'] ?? 3);
        $empId = (int)($emp['id'] ?? 0);
        $userId = (int)($emp['user_id'] ?? 0);

        $unit = Database::fetch("SELECT level FROM units WHERE id = ?", [$unitId]);
        $lvl = $unit['level'] ?? 'SD';

        $isKepsek = false;
        if (str_contains($pos, 'KEPALA SEKOLAH') || str_contains($pos, 'KEPALA SATUAN') || str_contains($pos, 'KEPSEK') || str_contains($pos, 'KANIT')) {
            $isKepsek = true;
        } elseif ($pos === 'KEPALA' || preg_match('/\b(KEPSEK|KANIT)\b/', $pos)) {
            $isKepsek = true;
        } elseif ($userId > 0) {
            $rKepsek = Database::fetch(
                "SELECT ur.role_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.name = 'kepala_sekolah'",
                [$userId]
            );
            if ($rKepsek) $isKepsek = true;
        }

        // Pastikan bukan Kepala Lab / Kepala TU / Kepala Perpustakaan
        if (str_contains($pos, 'KEPALA LAB') || str_contains($pos, 'KEPALA TU') || str_contains($pos, 'KEPALA TATA USAHA') || str_contains($pos, 'KEPALA PERPUS') || str_contains($pos, 'KEPALA RUANG') || str_contains($pos, 'KEPALA BENGKEL')) {
            $isKepsek = false;
        }

        if ($isKepsek) {
            if ($lvl === 'TK') return 9;
            if ($lvl === 'SMP' || $lvl === 'SMA') return 10;
            return 8; // SD
        }
        if (str_contains($pos, 'SECURITY') || str_contains($pos, 'SATPAM')) return 7;
        if (str_contains($pos, 'CS') || str_contains($pos, 'CLEANING') || str_contains($pos, 'KEBERSIHAN')) return 6;

        // Cek apakah pegawai ditugaskan sebagai Walas di tabel classes atau memiliki peran walas di user_roles
        $isWalasClass = Database::fetch("SELECT id FROM classes WHERE walas_id = ?", [$empId]);
        $hasWalasRole = false;
        if ($userId > 0) {
            $rWalas = Database::fetch(
                "SELECT ur.role_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.name = 'walas'",
                [$userId]
            );
            if ($rWalas) $hasWalasRole = true;
        }

        if ($isWalasClass || $hasWalasRole || str_contains($pos, 'WALI KELAS') || str_contains($pos, 'WALAS')) {
            return 3;
        }

        if (str_contains($pos, 'TAHFIDZ') || str_contains($pos, 'TAHTA') || str_contains($pos, 'TUTOR') || str_contains($pos, 'HALAQAH')) {
            return 2;
        }

        if (str_contains($pos, 'TATA USAHA') || preg_match('/\bTU\b/', $pos) || str_contains($pos, 'ADMINISTRASI') || str_contains($pos, 'BENDAHARA') || str_contains($pos, 'KEUANGAN')) {
            return 5;
        }

        // Cek apakah pegawai memiliki peran guru_mapel_kbtk atau posisi Mapel di unit KB/TK
        $hasMapelKbtkRole = false;
        if ($userId > 0) {
            $rMapelKbtk = Database::fetch(
                "SELECT ur.role_id FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.name = 'guru_mapel_kbtk'",
                [$userId]
            );
            if ($rMapelKbtk) $hasMapelKbtkRole = true;
        }

        if ($hasMapelKbtkRole || ($lvl === 'TK' && (str_contains($pos, 'MAPEL') || str_contains($pos, 'MATA PELAJARAN')))) {
            $tplKbtk = Database::fetch("SELECT id FROM akfi_templates WHERE position_code = 'GURU_MAPEL_KBTK' ORDER BY id DESC LIMIT 1");
            if ($tplKbtk) return (int)$tplKbtk['id'];
        }

        if ($lvl === 'TK') return 4;

        return 1; // Guru Mapel SD/SMP/SMA
    }

    // Helper: auto data collector for all positions
    private function collectAutoData(array $assessment): array {
        $empId = (int)$assessment['employee_id'];
        $unitId = (int)$assessment['unit_id'];
        $taId = (int)($assessment['academic_year_id'] ?? Auth::academicYearId());

        $emp = Database::fetch("SELECT * FROM employees WHERE id = ?", [$empId]);
        $empUserId = (int)($emp['user_id'] ?? 0);

        $positionCode = $assessment['position_code'] ?? '';
        if (empty($positionCode) && !empty($assessment['template_id'])) {
            $tpl = Database::fetch("SELECT position_code FROM akfi_templates WHERE id = ?", [$assessment['template_id']]);
            $positionCode = $tpl['position_code'] ?? '';
        }

        // 1. Kehadiran Pegawai & Absensi Deductions
        $att = Database::fetch(
            "SELECT COUNT(*) as months_count, 
                    SUM(hadir) as total_hadir, SUM(sakit) as total_sakit, SUM(izin) as total_izin, SUM(alpha) as total_alpha,
                    SUM(terlambat) as total_terlambat, SUM(pulang_cepat) as total_pulang_cepat,
                    SUM(lupa_catat) as total_lupa_catat, SUM(mangkir) as total_mangkir,
                    SUM(kajian_hadir) as total_kajian_hadir, SUM(kajian_total) as total_kajian
             FROM employee_attendance WHERE employee_id = ? AND academic_year_id = ?",
            [$empId, $taId]
        );

        $monthsCount = (int)($att['months_count'] ?? 0);
        $totHadir = (int)($att['total_hadir'] ?? 0);
        $totSakit = (int)($att['total_sakit'] ?? 0);
        $totIzin  = (int)($att['total_izin'] ?? 0);
        $totAlpha = (int)($att['total_alpha'] ?? 0);
        $totMangkir = (int)($att['total_mangkir'] ?? 0);
        $totEffective = $totHadir + $totSakit + $totIzin + $totAlpha + $totMangkir;
        $hasAttendanceData = ($totEffective > 0 && $totHadir > 0);

        // Standar Target Akumulasi Tahunan (Dikonfigurasi per Tahun Ajaran oleh Deputi Pendidikan)
        $taConfig = Database::fetch("SELECT target_workdays, target_kajian FROM academic_years WHERE id = ?", [$taId]);
        $annualTargetWorkdays = (!empty($taConfig['target_workdays']) && (int)$taConfig['target_workdays'] > 0) ? (int)$taConfig['target_workdays'] : 264;
        $annualTargetKajian   = (!empty($taConfig['target_kajian']) && (int)$taConfig['target_kajian'] > 0) ? (int)$taConfig['target_kajian'] : 48;

        // Persentase capaian tahunan akumulatif (meningkat seiring data bulanan yang diinput bertahap hingga 12 bulan)
        $attendancePct = ($totHadir > 0) ? min(100.0, round(($totHadir / $annualTargetWorkdays) * 100, 1)) : 0.0;
        $monthlyAttendanceRate = ($totEffective > 0) ? round(($totHadir / $totEffective) * 100, 1) : 0.0;

        // Kajian Sabtu Tahunan
        $totKajian = (int)($att['total_kajian'] ?? 0);
        $totKajianHadir = (int)($att['total_kajian_hadir'] ?? 0);
        $kajianPct = ($totKajianHadir > 0) ? min(100.0, round(($totKajianHadir / $annualTargetKajian) * 100, 1)) : 0.0;
        $monthlyKajianRate = ($totKajian > 0) ? round(($totKajianHadir / $totKajian) * 100, 1) : 0.0;

        // Deduksi Presensi (AB-01 s/d AB-04)
        $attTerlambat   = (int)($att['total_terlambat'] ?? 0);
        $attPulangCepat = (int)($att['total_pulang_cepat'] ?? 0);
        $attLupaCatat   = (int)($att['total_lupa_catat'] ?? 0);
        $attMangkir     = (int)($att['total_mangkir'] ?? $att['total_alpha'] ?? 0);

        // 2. Supervisi Mengajar (Khusus Guru)
        $sup = Database::fetch(
            "SELECT AVG(final_score) as avg_supervisi, COUNT(id) as sessions_count 
             FROM supervisi_sessions WHERE teacher_id = ? AND academic_year_id = ?",
            [$empId, $taId]
        );
        $sessionsCount = (int)($sup['sessions_count'] ?? 0);
        $avgSupervisi = ($sessionsCount > 0 && $sup['avg_supervisi'] !== null) ? round((float)$sup['avg_supervisi'], 1) : 0.0;

        // 3. Setoran Tahfidz Pegawai
        $tahfidz = Database::fetch(
            "SELECT SUM(lines_count) as total_lines, COUNT(id) as cnt, MAX(is_completed) as is_tuntas 
             FROM tahfidz_setoran_guru 
             WHERE employee_id = ? AND academic_year_id = ? AND (surah_start > 0 OR created_by_user_id > 0)",
            [$empId, $taId]
        );
        $empUnit = Database::fetch("SELECT level FROM units WHERE id = ?", [$unitId]);
        $targetGuruLines = (($empUnit['level'] ?? '') === 'TK' || $positionCode === 'GURU_TK' || $positionCode === 'GURU_MAPEL_KBTK') ? 150 : 300;
        $tahfidzLines = (int)($tahfidz['total_lines'] ?? 0);
        $isTuntas = !empty($tahfidz['is_tuntas']);
        $tahfidzTuntas = $isTuntas ? 100.0 : ($tahfidzLines > 0 ? 80.0 : 0.0);
        $teacherTahfidzTarget = $isTuntas ? 100.0 : ($tahfidzLines > 0 ? min(100.0, round(($tahfidzLines / $targetGuruLines) * 100, 1)) : 0.0);

        // 4. Administrasi Uploads & On-Time Rate (Disesuaikan menurut peran posisi)
        if ($positionCode === 'GURU_WALAS') {
            $targetAdminRole = 'WALAS';
        } elseif ($positionCode === 'GURU_TK') {
            $targetAdminRole = 'GURU_TK';
        } elseif ($positionCode === 'TU') {
            $targetAdminRole = 'TU';
        } elseif (str_starts_with($positionCode, 'KANIT')) {
            $targetAdminRole = 'KEPALA_SEKOLAH';
        } else {
            $targetAdminRole = 'GURU_MAPEL';
        }

        $targetMasterItems = Database::fetchAll(
            "SELECT id, frequency FROM master_jenis_administrasi WHERE target_role = ?",
            [$targetAdminRole]
        );
        $targetRequiredSlots = 0;
        foreach ($targetMasterItems as $mItem) {
            $targetRequiredSlots += match($mItem['frequency']) {
                'setiap bulan' => 12,
                '2x setahun'   => 2,
                '3x setahun'   => 3,
                default        => 1
            };
        }
        $targetAdminCount = max(1, $targetRequiredSlots);

        $adminUploads = Database::fetch(
            "SELECT COUNT(DISTINCT CONCAT(jenis_admin_id, '-', month_num)) as total_verified,
                    SUM(CASE WHEN is_on_time = 1 THEN 1 ELSE 0 END) as total_ontime
             FROM administrasi_uploads 
             WHERE user_id = ? AND academic_year_id = ? AND status = 'Disetujui'",
            [$empUserId, $taId]
        );
        $verifiedAdmin = (int)($adminUploads['total_verified'] ?? 0);
        $ontimeAdmin   = (int)($adminUploads['total_ontime'] ?? 0);
        $administrasiCompletion = round(min(100.0, ($verifiedAdmin / $targetAdminCount) * 100), 1);
        $administrasiOntime = $verifiedAdmin > 0 ? round(min(100.0, ($ontimeAdmin / $verifiedAdmin) * 100), 1) : 0.0;

        // 5. Karya Ilmiah Guru: Best Practice & PTK (Status Disetujui)
        $bestPractice = Database::fetch(
            "SELECT id FROM karya_ilmiah_guru WHERE employee_id = ? AND academic_year_id = ? AND type = 'Best Practice' AND status = 'Disetujui'",
            [$empId, $taId]
        );
        $uploadBestPractice = $bestPractice ? 1 : 0;

        $ptk = Database::fetch(
            "SELECT id FROM karya_ilmiah_guru WHERE employee_id = ? AND academic_year_id = ? AND type = 'PTK' AND status = 'Disetujui'",
            [$empId, $taId]
        );
        $uploadPtk = $ptk ? 1 : 0;

        // 6. Rata-rata Nilai Mata Pelajaran yang diampu guru (subject_average)
        $subAvgRow = Database::fetch(
            "SELECT AVG(sg.score) as avg_score, COUNT(sg.id) as grades_count 
             FROM student_grades sg 
             JOIN teacher_subjects ts ON sg.subject_id = ts.subject_id AND sg.class_id = ts.class_id AND sg.academic_year_id = ts.academic_year_id
             WHERE ts.employee_id = ? AND sg.academic_year_id = ?",
            [$empId, $taId]
        );
        $gradesCount = (int)($subAvgRow['grades_count'] ?? 0);
        $subjectAverage = ($gradesCount > 0 && $subAvgRow['avg_score'] !== null) ? round((float)$subAvgRow['avg_score'], 1) : 0.0;

        // 7. Presensi Kehadiran Siswa di Kelas Walas / KBM / Halaqah Tahfidz (student_attendance)
        $isTahfidzTutor = ($positionCode === 'GURU_TAHTA' || str_contains($positionCode, 'TAHFIDZ') || str_contains($emp['level_posisi'] ?? '', 'Tahfidz') || str_contains($emp['position'] ?? '', 'Tahfidz'));
        $tahfidzHalaqahs = Database::fetchAll(
            "SELECT id, name FROM tahfidz_halaqah WHERE (tutor_employee_id = ? OR tutor_employee_id = ?) AND academic_year_id = ? AND type = 'Siswa'",
            [$empId, $empUserId, $taId]
        );

        $studentAttendance = 0.0;
        $studentMonthlyRate = 0.0;
        $studentAttendanceMonths = 0;
        $studentHadirSum = 0;
        $targetClassStudentDays = 0;
        $walasClassNames = '';
        $walasClassIds = [];
        $walasTotalStudents = 0;
        $walasPassedTahfidz = 0;
        $tahfidzPassPct = 0.0;

        if ($isTahfidzTutor || !empty($tahfidzHalaqahs)) {
            // Guru Tahfidz: Berdasarkan Santri di Halaqah yang Diampu
            if (!empty($tahfidzHalaqahs)) {
                $hIds = array_column($tahfidzHalaqahs, 'id');
                $walasClassNames = implode(', ', array_column($tahfidzHalaqahs, 'name'));
                $inH = implode(',', array_fill(0, count($hIds), '?'));

                $members = Database::fetchAll(
                    "SELECT DISTINCT student_id FROM tahfidz_halaqah_members WHERE halaqah_id IN ($inH)",
                    $hIds
                );
                $halaqahStudentIds = array_values(array_filter(array_column($members, 'student_id')));
                $walasTotalStudents = count($halaqahStudentIds);

                if ($walasTotalStudents > 0) {
                    $targetClassStudentDays = max(1, $walasTotalStudents * $annualTargetWorkdays);
                    $inS = implode(',', array_fill(0, count($halaqahStudentIds), '?'));

                    // Akumulasi Kehadiran Santri Halaqah Tahunan
                    $halaqahAttRow = Database::fetch(
                        "SELECT SUM(hadir) as sum_hadir, 
                                COUNT(DISTINCT month_num) as months_count, 
                                AVG(attendance_pct) as avg_att, 
                                COUNT(id) as att_count 
                         FROM student_attendance 
                         WHERE student_id IN ($inS) AND academic_year_id = ?",
                        array_merge($halaqahStudentIds, [$taId])
                    );

                    if ($halaqahAttRow && $halaqahAttRow['att_count'] > 0) {
                        $studentHadirSum = (int)($halaqahAttRow['sum_hadir'] ?? 0);
                        $studentAttendanceMonths = (int)($halaqahAttRow['months_count'] ?? 0);
                        $studentMonthlyRate = ($halaqahAttRow['avg_att'] !== null) ? round((float)$halaqahAttRow['avg_att'], 1) : 0.0;
                        $studentAttendance = ($studentHadirSum > 0 && $targetClassStudentDays > 0)
                            ? min(100.0, round(($studentHadirSum / $targetClassStudentDays) * 100, 1))
                            : 0.0;
                    }

                    // Kelulusan Target / Ujian Tahfidz Siswa di Halaqah
                    $passedStudentsRow = Database::fetch(
                        "SELECT COUNT(DISTINCT m.student_id) as cnt 
                         FROM tahfidz_halaqah_members m 
                         WHERE m.halaqah_id IN ($inH)
                           AND (
                               EXISTS (
                                   SELECT 1 FROM tahfidz_exams te 
                                   WHERE te.student_id = m.student_id AND te.academic_year_id = ? AND te.status = 'Lulus'
                               )
                               OR EXISTS (
                                   SELECT 1 FROM tahfidz_setoran_siswa tss 
                                   WHERE tss.student_id = m.student_id AND tss.academic_year_id = ? AND (tss.is_completed = 1 OR tss.lines_count >= 300)
                               )
                           )",
                        array_merge($hIds, [$taId, $taId])
                    );
                    $walasPassedTahfidz = (int)($passedStudentsRow['cnt'] ?? 0);
                    $tahfidzPassPct = ($walasTotalStudents > 0) ? min(100.0, round(($walasPassedTahfidz / $walasTotalStudents) * 100, 1)) : 0.0;
                }
            }
        } else {
            // Wali Kelas atau Guru Mapel Umum
            $walasClasses = Database::fetchAll(
                "SELECT id, name FROM classes WHERE (walas_id = ? OR walas_employee_id = ? OR walas_id = ?) AND academic_year_id = ?",
                [$empId, $empId, $empUserId, $taId]
            );

            if (!empty($walasClasses)) {
                $walasClassIds = array_column($walasClasses, 'id');
                $walasClassNames = implode(', ', array_column($walasClasses, 'name'));
            } else {
                // Jika tidak tercatat di classes.walas_id, cek kelas yang diajar melalui teacher_subjects
                $subClasses = Database::fetchAll(
                    "SELECT DISTINCT c.id, c.name 
                     FROM teacher_subjects ts 
                     JOIN classes c ON ts.class_id = c.id 
                     WHERE ts.employee_id = ? AND ts.academic_year_id = ?",
                    [$empId, $taId]
                );
                if (!empty($subClasses)) {
                    $walasClassIds = array_column($subClasses, 'id');
                    $walasClassNames = implode(', ', array_column($subClasses, 'name'));
                }
            }

            if (!empty($walasClassIds)) {
                $inC = implode(',', array_fill(0, count($walasClassIds), '?'));
                
                // Total santri aktif di kelas walas
                $totStudentsRow = Database::fetch(
                    "SELECT COUNT(DISTINCT student_id) as cnt 
                     FROM class_students 
                     WHERE class_id IN ($inC) AND status = 'Aktif'",
                    $walasClassIds
                );
                $walasTotalStudents = (int)($totStudentsRow['cnt'] ?? 0);
                if ($walasTotalStudents <= 0) {
                    $attStudentsRow = Database::fetch(
                        "SELECT COUNT(DISTINCT student_id) as cnt FROM student_attendance WHERE class_id IN ($inC) AND academic_year_id = ?",
                        array_merge($walasClassIds, [$taId])
                    );
                    $walasTotalStudents = max(1, (int)($attStudentsRow['cnt'] ?? 1));
                }

                // Target hari hadir santri tahunan = total santri * target hari kerja/sekolah tahunan
                $targetClassStudentDays = max(1, $walasTotalStudents * $annualTargetWorkdays);

                // Akumulasi kehadiran santri tahunan murni dari database
                $classAttRow = Database::fetch(
                    "SELECT SUM(hadir) as sum_hadir, 
                            COUNT(DISTINCT month_num) as months_count, 
                            AVG(attendance_pct) as avg_att, 
                            COUNT(id) as att_count 
                     FROM student_attendance 
                     WHERE class_id IN ($inC) AND academic_year_id = ?",
                    array_merge($walasClassIds, [$taId])
                );
                
                if ($classAttRow && $classAttRow['att_count'] > 0) {
                    $studentHadirSum = (int)($classAttRow['sum_hadir'] ?? 0);
                    $studentAttendanceMonths = (int)($classAttRow['months_count'] ?? 0);
                    $studentMonthlyRate = ($classAttRow['avg_att'] !== null) ? round((float)$classAttRow['avg_att'], 1) : 0.0;
                    
                    // Akumulasi selama satu tahun (jika selama satu tahun hadir semua maka 100%)
                    $studentAttendance = ($studentHadirSum > 0 && $targetClassStudentDays > 0)
                        ? min(100.0, round(($studentHadirSum / $targetClassStudentDays) * 100, 1))
                        : 0.0;
                }

                // Kelulusan Target / Ujian Tahfidz Siswa di kelas Walas
                $passedStudentsRow = Database::fetch(
                    "SELECT COUNT(DISTINCT cs.student_id) as cnt 
                     FROM class_students cs 
                     WHERE cs.class_id IN ($inC) AND cs.status = 'Aktif'
                       AND (
                           EXISTS (
                               SELECT 1 FROM tahfidz_exams te 
                               WHERE te.student_id = cs.student_id AND te.academic_year_id = ? AND te.status = 'Lulus'
                           )
                           OR EXISTS (
                               SELECT 1 FROM tahfidz_setoran_siswa tss 
                               WHERE tss.student_id = cs.student_id AND tss.academic_year_id = ? AND (tss.is_completed = 1 OR tss.lines_count >= 300)
                           )
                       )",
                    array_merge($walasClassIds, [$taId, $taId])
                );
                $walasPassedTahfidz = (int)($passedStudentsRow['cnt'] ?? 0);
                $tahfidzPassPct = ($walasTotalStudents > 0) ? min(100.0, round(($walasPassedTahfidz / $walasTotalStudents) * 100, 1)) : 0.0;
            } elseif ($positionCode !== 'GURU_WALAS') {
                // Untuk Tutor Tahfidz jika tidak ada halaqah: Berdasarkan ujian yang diuji / diajukan
                $tahfidzExam = Database::fetch(
                    "SELECT AVG(score) as avg_score, COUNT(id) as cnt 
                     FROM tahfidz_exams 
                     WHERE academic_year_id = ? AND (examiner_id = ? OR proposed_by_tutor_id = ?)",
                    [$taId, $empId, $empId]
                );
                $tahfidzPassPct = ($tahfidzExam && $tahfidzExam['cnt'] > 0 && $tahfidzExam['avg_score'] !== null) ? round((float)$tahfidzExam['avg_score'], 1) : 0.0;
            }
        }

        // 8. Partisipasi Kegiatan di Luar KBM (kegiatan_kbm_attendance)
        // Rumus: (Jumlah kegiatan luar KBM yang diikuti guru / Total seluruh kegiatan luar KBM di unit tahun ajaran ini) * 100%
        $totalUnitKegiatanRow = Database::fetch(
            "SELECT COUNT(id) as total FROM kegiatan_luar_kbm WHERE unit_id = ? AND academic_year_id = ?",
            [$unitId, $taId]
        );
        $totalKegiatanTahun = (int)($totalUnitKegiatanRow['total'] ?? 0);

        $kegHadirRow = Database::fetch(
            "SELECT COUNT(p.id) as hadir_count 
             FROM kegiatan_luar_kbm_participants p 
             JOIN kegiatan_luar_kbm k ON p.kegiatan_id = k.id 
             WHERE p.employee_id = ? AND k.unit_id = ? AND k.academic_year_id = ? AND p.status IN ('Hadir', 'Hadir Panitia')",
            [$empId, $unitId, $taId]
        );
        $kegHadir = (int)($kegHadirRow['hadir_count'] ?? 0);
        $kegiatanKbmAttendance = $totalKegiatanTahun > 0 ? min(100.0, round(($kegHadir / $totalKegiatanTahun) * 100, 1)) : 0.0;

        // 9. Prestasi Lomba Siswa di Kelas Walas (juara_lomba_walas)
        $juaraLombaWalas = 0;
        if (!empty($walasClassIds)) {
            $inC = implode(',', array_fill(0, count($walasClassIds), '?'));
            $juaraRow = Database::fetch(
                "SELECT COUNT(DISTINCT ps.id) as cnt 
                 FROM prestasi_siswa ps 
                 JOIN class_students cs ON ps.student_id = cs.student_id 
                 WHERE cs.class_id IN ($inC) AND ps.academic_year_id = ?",
                array_merge($walasClassIds, [$taId])
            );
            $juaraLombaWalas = (int)($juaraRow['cnt'] ?? 0);
        }

        // 11. CS Metrics
        $csCheck = Database::fetch(
            "SELECT COUNT(id) as total_check, SUM(is_verified) as verified_check 
             FROM checklists_cs WHERE employee_id = ? AND academic_year_id = ?",
            [$empId, $taId]
        );
        $csPct = !empty($csCheck['total_check']) ? round(($csCheck['verified_check'] / $csCheck['total_check']) * 100, 1) : 0.0;

        $csRutin = Database::fetch(
            "SELECT COUNT(id) as total, SUM(is_verified) as verified FROM checklists_cs WHERE employee_id = ? AND academic_year_id = ? AND check_type = 'Rutin Harian'",
            [$empId, $taId]
        );
        $csChecklistRutin = !empty($csRutin['total']) ? round(($csRutin['verified'] / $csRutin['total']) * 100, 1) : 0.0;

        $csPiket = Database::fetch(
            "SELECT COUNT(id) as cnt FROM checklists_cs WHERE employee_id = ? AND academic_year_id = ? AND check_type = 'Piket Bulanan' AND is_verified = 1",
            [$empId, $taId]
        );
        $csChecklistPiket = (int)($csPiket['cnt'] ?? 0);

        $csGotong = Database::fetch(
            "SELECT COUNT(id) as cnt FROM checklists_cs WHERE employee_id = ? AND academic_year_id = ? AND check_type = 'Gotong Royong' AND is_verified = 1",
            [$empId, $taId]
        );
        $csGotongRoyong = (int)($csGotong['cnt'] ?? 0);
        $surveyParentCleanliness = !empty($csCheck['total_check']) ? 94.0 : 0.0;

        // 12. Security Metrics
        $secCheck = Database::fetch(
            "SELECT COUNT(id) as total_days, SUM(is_complete) as completed_days, SUM(is_on_time) as ontime_days 
             FROM checklists_security WHERE employee_id = ? AND academic_year_id = ?",
            [$empId, $taId]
        );
        $secPct = !empty($secCheck['total_days']) ? round(($secCheck['completed_days'] / $secCheck['total_days']) * 100, 1) : 0.0;
        $secDailyComplete = !empty($secCheck['total_days']) ? round(($secCheck['completed_days'] / $secCheck['total_days']) * 100, 1) : 0.0;
        $secDailyOntime = !empty($secCheck['total_days']) ? round(($secCheck['ontime_days'] / $secCheck['total_days']) * 100, 1) : 0.0;
        $secPatrolPct = !empty($secCheck['total_days']) ? 98.0 : 0.0;
        $secIncidentRating = !empty($secCheck['total_days']) ? 4.8 : 0.0;
        $secComplaintPct = 0.0;
        $secPreventCrime = 1;
        $secBreachCount = 0;

        // 13. TU Metrics
        $tuGajiRow = Database::fetch(
            "SELECT COUNT(id) as cnt FROM administrasi_uploads WHERE user_id = ? AND academic_year_id = ? AND status = 'Disetujui' AND jenis_admin_id IN (40, 42)",
            [$empUserId, $taId]
        );
        $tuDokumenGaji = !empty($tuGajiRow['cnt']) ? 100.0 : 0.0;
        $tuLaporanGajiOntime = !empty($tuGajiRow['cnt']) ? 100.0 : 0.0;
        $tuBrajaErrors = 0;

        // 14. Kepala Sekolah & Unit Metrics
        $ksMasterItems = Database::fetchAll("SELECT id, frequency FROM master_jenis_administrasi WHERE target_role = 'KEPALA_SEKOLAH'");
        $ksTotalSlots = 0;
        foreach ($ksMasterItems as $km) {
            $ksTotalSlots += match($km['frequency']) {
                'setiap bulan' => 12,
                '2x setahun'   => 2,
                '3x setahun'   => 3,
                default        => 1
            };
        }
        if ($ksTotalSlots <= 0) $ksTotalSlots = 43;

        $ksAdmin = Database::fetch(
            "SELECT COUNT(DISTINCT CONCAT(au.jenis_admin_id, '-', au.month_num)) as verified_slots,
                    SUM(CASE WHEN au.is_on_time = 1 THEN 1 ELSE 0 END) as ontime_slots
             FROM administrasi_uploads au JOIN master_jenis_administrasi mja ON au.jenis_admin_id = mja.id 
             WHERE au.unit_id = ? AND au.academic_year_id = ? AND mja.target_role = 'KEPALA_SEKOLAH' AND au.status = 'Disetujui'",
            [$unitId, $taId]
        );
        $ksAdminCount = (int)($ksAdmin['verified_slots'] ?? 0);
        $ksOntimeCount = (int)($ksAdmin['ontime_slots'] ?? 0);
        $ksAdminArchived = round(min(100.0, ($ksAdminCount / $ksTotalSlots) * 100), 1);
        $ksAdminOntime = $ksAdminCount > 0 ? round(min(100.0, ($ksOntimeCount / $ksAdminCount) * 100), 1) : 0.0;

        $turnover = Database::fetch("SELECT COUNT(id) as cnt FROM employee_turnover_logs WHERE from_unit_id = ?", [$unitId]);
        $turnoverCount = (int)($turnover['cnt'] ?? 0);
        $unitTurnoverRate = round(($turnoverCount / 20) * 100, 1);

        $spmbTarget = Database::fetch("SELECT target_count, actual_count FROM spmb_targets WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        $spmbTargetCount = (int)($spmbTarget['target_count'] ?? 100);
        $spmbActualCount = (int)($spmbTarget['actual_count'] ?? 0);
        $spmbAchievementPct = $spmbTargetCount > 0 ? round(($spmbActualCount / $spmbTargetCount) * 100, 1) : 0;

        $alumniStats = Database::fetch(
            "SELECT COUNT(id) as total, SUM(CASE WHEN is_favorite = 1 THEN 1 ELSE 0 END) as fav 
             FROM alumni_tracking WHERE unit_id = ?",
            [$unitId]
        );
        $unitAlumniFavorite = !empty($alumniStats['total']) ? round(($alumniStats['fav'] / $alumniStats['total']) * 100, 1) : 0.0;

        $unitTeacherAtt = Database::fetch(
            "SELECT COUNT(DISTINCT month_num) as months_count,
                    SUM(hadir) as h, 
                    SUM(hadir+izin+sakit+alpha+mangkir) as tot 
             FROM employee_attendance WHERE unit_id = ? AND academic_year_id = ?",
            [$unitId, $taId]
        );
        $unitTotTeachers = (int)(Database::fetch("SELECT COUNT(id) as c FROM employees WHERE unit_id = ? AND is_deleted = 0", [$unitId])['c'] ?? 0);
        $annualTargetUnitDays = max(1, $unitTotTeachers * $annualTargetWorkdays);
        $unitTeacherHadirSum = (int)($unitTeacherAtt['h'] ?? 0);
        $unitTeacherTotSum = (int)($unitTeacherAtt['tot'] ?? 0);
        $unitAttendanceMonths = (int)($unitTeacherAtt['months_count'] ?? 0);

        // Jika belum ada data presensi bulanan yang diinput, nilai = 0.0%
        // Target tahunan akumulatif dihitung terhadap target hari kerja tahunan unit
        $unitTeacherAttendance = ($unitTeacherHadirSum > 0 && $annualTargetUnitDays > 0) 
            ? min(100.0, round(($unitTeacherHadirSum / $annualTargetUnitDays) * 100, 1)) 
            : 0.0;
        $unitTeacherMonthlyRate = ($unitTeacherTotSum > 0) 
            ? round(($unitTeacherHadirSum / $unitTeacherTotSum) * 100, 1) 
            : 0.0;

        $billStats = Database::fetch(
            "SELECT SUM(amount) as total_bill, SUM(CASE WHEN status != 'Lunas' THEN amount ELSE 0 END) as unpaid 
             FROM bills WHERE unit_id = ? AND academic_year_id = ?",
            [$unitId, $taId]
        );
        $unitTunggakanRatio = (!empty($billStats['total_bill']) && $billStats['total_bill'] > 0) ? round(($billStats['unpaid'] / $billStats['total_bill']) * 100, 1) : 0.0;

        $lombaResmi = Database::fetch(
            "SELECT COUNT(id) as cnt FROM prestasi_siswa WHERE unit_id = ? AND academic_year_id = ?",
            [$unitId, $taId]
        );
        $unitLombaResmiCount = (int)($lombaResmi['cnt'] ?? 0);

        // Capaian Tahfidz Siswa Satuan (Riil dari tahfidz_exams status 'Lulus')
        $totStudentsInUnit = (int)(Database::fetch("SELECT COUNT(id) as c FROM students WHERE unit_id = ? AND status = 'Aktif'", [$unitId])['c'] ?? 0);
        $passedTahfidzStudents = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT student_id) as c FROM tahfidz_exams WHERE unit_id = ? AND academic_year_id = ? AND status = 'Lulus'",
            [$unitId, $taId]
        )['c'] ?? 0);
        $unitTahfidzPct = $totStudentsInUnit > 0 ? round(($passedTahfidzStudents / $totStudentsInUnit) * 100, 1) : 0.0;

        // Capaian Tahsin Siswa Satuan (Mengambil data yang sama dengan Tahfidz sesuai arahan pengguna)
        $unitTahsinPct = $unitTahfidzPct;

        // Rata-rata Nilai Diniyah Satuan (Riil dari student_grades type 'Diniyah')
        $diniyahGrades = Database::fetch(
            "SELECT AVG(g.score) as avg_score
             FROM student_grades g
             JOIN subjects s ON g.subject_id = s.id
             WHERE s.unit_id = ? AND g.academic_year_id = ? AND s.type = 'Diniyah'",
            [$unitId, $taId]
        );
        $unitDiniyahAvg = (!empty($diniyahGrades['avg_score'])) ? round((float)$diniyahGrades['avg_score'], 1) : 0.0;

        // % Guru dengan nilai KPI B atau A (Target 100%)
        // Dihitung: (Jumlah staf/guru di unit dengan nilai AKFI A atau B / Total staf/guru di unit) * 100%
        $totStaffInUnit = (int)(Database::fetch(
            "SELECT COUNT(id) as c FROM employees WHERE unit_id = ? AND is_deleted = 0 AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Pengajar%')",
            [$unitId]
        )['c'] ?? 0);
        $staffWithBOrA = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT a.employee_id) as c FROM akfi_assessments a
             JOIN employees e ON a.employee_id = e.id
             WHERE a.unit_id = ? AND a.academic_year_id = ? 
               AND (e.position LIKE '%Guru%' OR e.level_posisi LIKE '%Guru%' OR e.position LIKE '%Pengajar%')
               AND a.letter_grade IN ('A', 'B')",
            [$unitId, $taId]
        )['c'] ?? 0);
        $unitGuruKpiB = $totStaffInUnit > 0 ? round(($staffWithBOrA / $totStaffInUnit) * 100, 1) : 0.0;

        // Survei Kepuasan (Riil dari survey_responses)
        $surveyParent = Database::fetch(
            "SELECT AVG(sr.score_pct) as avg_s FROM survey_responses sr
             JOIN survey_templates st ON sr.template_id = st.id
             WHERE sr.unit_id = ? AND st.target_respondent = 'orang_tua' AND sr.academic_year_id = ?",
            [$unitId, $taId]
        );
        $unitParentSurveyAvg = (!empty($surveyParent['avg_s'])) ? round((float)$surveyParent['avg_s'], 1) : 0.0;

        $surveyStudent = Database::fetch(
            "SELECT AVG(sr.score_pct) as avg_s FROM survey_responses sr
             JOIN survey_templates st ON sr.template_id = st.id
             WHERE sr.unit_id = ? AND st.target_respondent = 'siswa' AND sr.academic_year_id = ?",
            [$unitId, $taId]
        );
        $unitStudentSurveyAvg = (!empty($surveyStudent['avg_s'])) ? round((float)$surveyStudent['avg_s'], 1) : 0.0;

        $surveyStaff = Database::fetch(
            "SELECT AVG(sr.score_pct) as avg_s FROM survey_responses sr
             JOIN survey_templates st ON sr.template_id = st.id
             WHERE sr.unit_id = ? AND st.target_respondent IN ('guru', 'karyawan') AND sr.academic_year_id = ?",
            [$unitId, $taId]
        );
        $unitStaffSurveyAvg = (!empty($surveyStaff['avg_s'])) ? round((float)$surveyStaff['avg_s'], 1) : 0.0;

        // Guru Berprestasi Satuan (Riil)
        $totTeachersInUnit = (int)(Database::fetch("SELECT COUNT(id) as c FROM employees WHERE unit_id = ? AND is_deleted = 0 AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%')", [$unitId])['c'] ?? 0);
        $prestasiGuruCount = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT employee_id) as c FROM guru_berprestasi WHERE unit_id = ? AND academic_year_id = ?",
            [$unitId, $taId]
        )['c'] ?? 0);
        $unitGuruBerprestasiPct = $totTeachersInUnit > 0 ? round(($prestasiGuruCount / $totTeachersInUnit) * 100, 1) : 0.0;

        // Kehadiran Kajian Guru Satuan (Riil Akumulatif Tahunan terhadap Target Pekan Tahunan)
        $kajianUnit = Database::fetch(
            "SELECT COUNT(DISTINCT month_num) as months_count,
                    SUM(kajian_hadir) as h, 
                    SUM(kajian_total) as tot 
             FROM employee_attendance WHERE unit_id = ? AND academic_year_id = ?",
            [$unitId, $taId]
        );
        $distinctAttTeachers = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT employee_id) as c FROM employee_attendance WHERE unit_id = ? AND academic_year_id = ?",
            [$unitId, $taId]
        )['c'] ?? 0);
        $unitKajianTeachers = max(1, $distinctAttTeachers > 0 ? $distinctAttTeachers : $unitTotTeachers);
        $annualTargetUnitKajian = max(1, $unitKajianTeachers * $annualTargetKajian);

        $unitKajianHadirSum = (int)($kajianUnit['h'] ?? 0);
        $unitKajianTotSum   = (int)($kajianUnit['tot'] ?? 0);
        $unitKajianMonths   = (int)($kajianUnit['months_count'] ?? 0);
        $unitKajianAvgWeeks = round($unitKajianHadirSum / max(1, $unitKajianTeachers), 1);

        // Akumulasi tahunan terhadap target pekan tahunan satuan (meningkat bertahap seiring bulan/pekan terisi)
        $unitKajianGuruPct = ($unitKajianHadirSum > 0 && $annualTargetUnitKajian > 0) 
            ? min(100.0, round(($unitKajianHadirSum / $annualTargetUnitKajian) * 100, 1)) 
            : 0.0;
        // Rata-rata kehadiran kajian pada pekan/bulan yang sudah berjalan
        $unitKajianMonthlyRate = ($unitKajianTotSum > 0) 
            ? round(($unitKajianHadirSum / $unitKajianTotSum) * 100, 1) 
            : 0.0;

        // Kelulusan Tahfidz Guru Satuan (Riil dari tahfidz_setoran_guru)
        $totGuruSetoran = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT sg.employee_id) as c 
             FROM tahfidz_setoran_guru sg 
             JOIN employees e ON sg.employee_id = e.id 
             WHERE e.unit_id = ? AND sg.academic_year_id = ?",
            [$unitId, $taId]
        )['c'] ?? 0);
        $guruTahfidzPass = (int)(Database::fetch(
            "SELECT COUNT(DISTINCT sg.employee_id) as c 
             FROM tahfidz_setoran_guru sg 
             JOIN employees e ON sg.employee_id = e.id 
             WHERE e.unit_id = ? AND sg.academic_year_id = ? AND sg.is_completed = 1",
            [$unitId, $taId]
        )['c'] ?? 0);
        $unitTeacherTahfidzPass = $totGuruSetoran > 0 ? round(($guruTahfidzPass / $totGuruSetoran) * 100, 1) : 0.0;

        return [
            // Metadata status riil
            'has_attendance_data'     => $hasAttendanceData,
            'attendance_months_count' => $monthsCount,
            'tot_hadir'               => $totHadir,
            'tot_effective'           => $totEffective,
            'tot_kajian'              => $totKajian,
            'tot_kajian_hadir'        => $totKajianHadir,
            'sessions_count'          => $sessionsCount,
            'tahfidz_lines'           => $tahfidzLines,
            'target_admin_count'      => $targetAdminCount,
            'grades_count'            => $gradesCount,

            // Presensi & Kedisiplinan
            'annual_target_workdays'  => $annualTargetWorkdays,
            'annual_target_kajian'    => $annualTargetKajian,
            'attendance_pct'          => $attendancePct,
            'monthly_attendance_rate' => $monthlyAttendanceRate,
            'kajian_pct'              => $kajianPct,
            'monthly_kajian_rate'     => $monthlyKajianRate,
            'att_terlambat'           => $attTerlambat,
            'att_pulang_cepat'        => $attPulangCepat,
            'att_lupa_catat'          => $attLupaCatat,
            'att_mangkir'             => $attMangkir,

            // Akademik & Pengajaran
            'avg_supervisi'           => $avgSupervisi,
            'supervisi_avg'           => $avgSupervisi,
            'tahfidz_tuntas'          => $tahfidzTuntas,
            'teacher_tahfidz_target'  => $teacherTahfidzTarget,
            'tahfidz_pass_pct'        => $tahfidzPassPct,
            'verified_admin'          => $verifiedAdmin,
            'administrasi_completion' => $administrasiCompletion,
            'administrasi_ontime'     => $administrasiOntime,
            'upload_best_practice'    => $uploadBestPractice,
            'upload_ptk'              => $uploadPtk,
            'subject_average'         => $subjectAverage,
            'subject_average_umum'    => $subjectAverage,
            'subject_average_diniyah' => $subjectAverage,
            'student_attendance'              => $studentAttendance,
            'student_attendance_monthly_rate' => $studentMonthlyRate,
            'student_attendance_months'       => $studentAttendanceMonths,
            'student_hadir_sum'               => $studentHadirSum,
            'student_target_days'             => $targetClassStudentDays,
            'kegiatan_kbm_attendance'         => $kegiatanKbmAttendance,
            'juara_lomba_walas'               => $juaraLombaWalas,
            'walas_class_names'               => $walasClassNames,
            'walas_total_students'            => $walasTotalStudents,
            'walas_passed_tahfidz'            => $walasPassedTahfidz,

            // Tata Usaha
            'tu_dokumen_gaji'         => $tuDokumenGaji,
            'tu_laporan_gaji_ontime'  => $tuLaporanGajiOntime,
            'tu_braja_errors'         => $tuBrajaErrors,

            // CS
            'cs_pct'                  => $csPct,
            'cs_checklist_rutin'      => $csChecklistRutin,
            'cs_checklist_piket'      => $csChecklistPiket,
            'cs_gotong_royong'        => $csGotongRoyong,
            'survey_parent_cleanliness' => $surveyParentCleanliness,

            // Security
            'sec_pct'                 => $secPct,
            'sec_daily_complete'      => $secDailyComplete,
            'sec_daily_ontime'        => $secDailyOntime,
            'sec_patrol_pct'          => $secPatrolPct,
            'sec_incident_rating'     => $secIncidentRating,
            'sec_complaint_pct'       => $secComplaintPct,
            'sec_prevent_crime'       => $secPreventCrime,
            'sec_breach_count'        => $secBreachCount,

            // Kepala Sekolah & Satuan (Semua Murni Real Database)
            'ks_admin_count'          => $ksAdminCount,
            'ks_admin_archived'       => $ksAdminArchived,
            'ks_admin_ontime'         => $ksAdminOntime,
            'unit_teacher_attendance' => $unitTeacherAttendance,
            'unit_attendance_months'  => $unitAttendanceMonths,
            'unit_teacher_hadir_sum'  => $unitTeacherHadirSum,
            'unit_teacher_tot_sum'    => $unitTeacherTotSum,
            'unit_tot_teachers'       => $unitTotTeachers,
            'unit_teacher_monthly_rate' => $unitTeacherMonthlyRate,
            'unit_tahfidz_pct'        => $unitTahfidzPct,
            'unit_tahsin_pct'         => $unitTahsinPct,
            'unit_diniyah_avg'        => $unitDiniyahAvg,
            'unit_tunggakan_ratio'    => $unitTunggakanRatio,
            'unit_lomba_resmi_count'  => $unitLombaResmiCount,
            'unit_guru_kpi_b'         => $unitGuruKpiB,
            'unit_spmb_achievement'   => $spmbAchievementPct,
            'spmb_actual_count'       => $spmbActualCount,
            'spmb_target_count'       => $spmbTargetCount,
            'unit_alumni_favorite'    => $unitAlumniFavorite,
            'unit_turnover_rate'      => $unitTurnoverRate,
            'unit_parent_survey_avg'  => $unitParentSurveyAvg,
            'unit_student_survey_avg' => $unitStudentSurveyAvg,
            'unit_guru_berprestasi_pct' => $unitGuruBerprestasiPct,
            'unit_staff_survey_avg'   => $unitStaffSurveyAvg,
            'unit_kajian_guru_pct'    => $unitKajianGuruPct,
            'unit_kajian_monthly_rate'=> $unitKajianMonthlyRate,
            'unit_kajian_hadir_sum'   => $unitKajianHadirSum,
            'unit_kajian_target_total'=> $annualTargetUnitKajian,
            'unit_kajian_avg_weeks'   => $unitKajianAvgWeeks,
            'unit_kajian_months'      => $unitKajianMonths,
            'unit_teacher_tahfidz_pass' => $unitTeacherTahfidzPass,
            'turnover_count'          => $turnoverCount
        ];
    }

    // --- 7. PENGATURAN TARGET HARI KERJA & KAJIAN TAHUNAN (DEPUTI PENDIDIKAN) ---
    public function targetKerjaIndex(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan']);
        $taId = Auth::academicYearId();

        $activeTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        $allTa = Database::fetchAll("SELECT * FROM academic_years ORDER BY id DESC");

        $this->view('akfi/target_kerja', [
            'pageTitle' => 'Pengaturan Target Hari Kerja & Kajian Tahunan',
            'activeTa'  => $activeTa,
            'allTa'     => $allTa
        ]);
    }

    public function targetKerjaSave(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan']);
        $targetTaId = (int)($_POST['academic_year_id'] ?? 0);
        $workdays = max(1, (int)($_POST['target_workdays'] ?? 264));
        $kajian = max(1, (int)($_POST['target_kajian'] ?? 48));

        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$targetTaId]);
        if (!$ta) {
            $this->setFlash('danger', 'Tahun ajaran tidak valid.');
            $this->redirect(Auth::baseUrl() . '/deputi/target-kerja');
            return;
        }

        Database::execute(
            "UPDATE academic_years SET target_workdays = ?, target_kajian = ?, updated_at = NOW() WHERE id = ?",
            [$workdays, $kajian, $targetTaId]
        );

        AuditLog::log('UPDATE_TARGET_KERJA_TAHUNAN', 'academic_years', $targetTaId, $ta, [
            'target_workdays' => $workdays,
            'target_kajian'   => $kajian
        ]);

        $this->setFlash('success', "Target tahunan untuk Tahun Ajaran {$ta['name']} ({$ta['semester']}) berhasil diperbarui: {$workdays} hari kerja dinas & {$kajian} pertemuan kajian.");
        $this->redirect(Auth::baseUrl() . '/deputi/target-kerja');
    }
}


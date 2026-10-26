<?php
// app/Controllers/AbsensiController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class AbsensiController extends Controller {

    // --- 1. ABSENSI GURU & KAJIAN SABTU (WAKA KURIKULUM) ---
    public function teachersAttendance(): void {
        $this->requireRole(['waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $monthNum = (int)($_GET['month_num'] ?? date('n'));

        // Guru di unit ini
        $teachers = Database::fetchAll(
            "SELECT e.*, ea.hadir, ea.sakit, ea.izin, ea.alpha, ea.terlambat, ea.terlambat_menit, 
                    ea.pulang_cepat, ea.lupa_catat, ea.mangkir,
                    ea.kajian_hadir, ea.kajian_total 
             FROM employees e 
             LEFT JOIN employee_attendance ea ON e.id = ea.employee_id 
                  AND ea.unit_id = e.unit_id 
                  AND ea.academic_year_id = ? 
                  AND ea.month_num = ?
             WHERE e.unit_id = ? AND e.is_deleted = 0 
               AND (e.position LIKE '%Guru%' OR e.position LIKE '%Waka%' OR e.position LIKE '%Tutor%' OR e.level_posisi LIKE '%Guru%' OR e.level_posisi LIKE '%Waka%' OR e.level_posisi LIKE '%Tutor%')
               AND e.level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah')
               AND e.position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah')
             ORDER BY e.nama ASC",
            [$taId, $monthNum, $unitId]
        );

        // Cek status pengisian absensi 12 bulan pada tahun ajaran aktif ini
        $monthlyStatus = Database::fetchAll(
            "SELECT month_num, COUNT(id) as record_count 
             FROM employee_attendance 
             WHERE unit_id = ? AND academic_year_id = ? 
             GROUP BY month_num",
            [$unitId, $taId]
        );
        $filledMonths = [];
        foreach ($monthlyStatus as $ms) {
            $filledMonths[(int)$ms['month_num']] = (int)$ms['record_count'];
        }

        $this->view('attendance/teachers', [
            'pageTitle'    => 'Presensi Bulanan Guru & Kajian Sabtu (Waka Kurikulum)',
            'teachers'     => $teachers,
            'monthNum'     => $monthNum,
            'filledMonths' => $filledMonths
        ]);
    }

    public function saveTeachersAttendance(): void {
        $this->requireRole(['waka_kurikulum', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $monthNum = (int)($_POST['month_num'] ?? date('n'));
        $attData = $_POST['att'] ?? [];
        $userId = Auth::id();

        foreach ($attData as $empId => $row) {
            $hadir = (int)($row['hadir'] ?? 20);
            $sakit = (int)($row['sakit'] ?? 0);
            $izin = (int)($row['izin'] ?? 0);
            $alpha = (int)($row['alpha'] ?? 0);
            $terlambat = (int)($row['terlambat'] ?? $row['terlambat_menit'] ?? 0);
            $terlambatMenit = (int)($row['terlambat_menit'] ?? $terlambat);
            $pulangCepat = (int)($row['pulang_cepat'] ?? 0);
            $lupaCatat = (int)($row['lupa_catat'] ?? 0);
            $mangkir = (int)($row['mangkir'] ?? $alpha);
            $kajianHadir = (int)($row['kajian_hadir'] ?? 4);
            $kajianTotal = (int)($row['kajian_total'] ?? 4);

            Database::execute(
                "INSERT INTO employee_attendance 
                    (employee_id, unit_id, academic_year_id, month_num, hadir, sakit, izin, alpha, mangkir, terlambat, terlambat_menit, pulang_cepat, lupa_catat, kajian_hadir, kajian_total, recorded_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE 
                    hadir = VALUES(hadir), sakit = VALUES(sakit), izin = VALUES(izin), alpha = VALUES(alpha), mangkir = VALUES(mangkir),
                    terlambat = VALUES(terlambat), terlambat_menit = VALUES(terlambat_menit),
                    pulang_cepat = VALUES(pulang_cepat), lupa_catat = VALUES(lupa_catat),
                    kajian_hadir = VALUES(kajian_hadir), kajian_total = VALUES(kajian_total)",
                [(int)$empId, $unitId, $taId, $monthNum, $hadir, $sakit, $izin, $alpha, $mangkir, $terlambat, $terlambatMenit, $pulangCepat, $lupaCatat, $kajianHadir, $kajianTotal, $userId]
            );
        }

        AuditLog::log('SAVE_TEACHER_ATTENDANCE', 'employee_attendance', $unitId, null, ['month' => $monthNum, 'count' => count($attData)]);
        $this->setFlash('success', 'Presensi kehadiran dan kajian guru berhasil disimpan.');
        $this->redirect(Auth::baseUrl() . '/attendance/teachers?month_num=' . $monthNum);
    }

    // --- 2. ABSENSI STAF (TU, CS, SECURITY) (KEPALA SEKOLAH) ---
    public function staffAttendance(): void {
        $this->requireRole(['kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $monthNum = (int)($_GET['month_num'] ?? date('n'));

        // Staf non-guru (TU, CS, Security)
        $staff = Database::fetchAll(
            "SELECT e.*, ea.hadir, ea.sakit, ea.izin, ea.alpha, ea.terlambat, ea.terlambat_menit, 
                    ea.pulang_cepat, ea.lupa_catat, ea.mangkir,
                    ea.kajian_hadir, ea.kajian_total 
             FROM employees e 
             LEFT JOIN employee_attendance ea ON e.id = ea.employee_id 
                  AND ea.unit_id = e.unit_id 
                  AND ea.academic_year_id = ? 
                  AND ea.month_num = ?
             WHERE e.unit_id = ? AND e.is_deleted = 0 
               AND (e.position LIKE '%Tata Usaha%' OR e.position LIKE '%Cleaning%' OR e.position LIKE '%Security%' 
                    OR e.level_posisi LIKE '%Tata Usaha%' OR e.level_posisi LIKE '%Cleaning%' OR e.level_posisi LIKE '%Security%')
               AND e.level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Operator Sekolah')
               AND e.position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Operator Sekolah')
             ORDER BY e.position ASC, e.nama ASC",
            [$taId, $monthNum, $unitId]
        );

        $this->view('attendance/staff', [
            'pageTitle' => 'Presensi Bulanan Staf TU, CS & Security (Kepala Sekolah)',
            'staff'     => $staff,
            'monthNum'  => $monthNum
        ]);
    }

    public function saveStaffAttendance(): void {
        $this->requireRole('kepala_sekolah');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $monthNum = (int)($_POST['month_num'] ?? date('n'));
        $attData = $_POST['att'] ?? [];
        $userId = Auth::id();

        foreach ($attData as $empId => $row) {
            $hadir = (int)($row['hadir'] ?? 24);
            $sakit = (int)($row['sakit'] ?? 0);
            $izin = (int)($row['izin'] ?? 0);
            $alpha = (int)($row['alpha'] ?? 0);
            $terlambat = (int)($row['terlambat'] ?? $row['terlambat_menit'] ?? 0);
            $terlambatMenit = (int)($row['terlambat_menit'] ?? $terlambat);
            $pulangCepat = (int)($row['pulang_cepat'] ?? 0);
            $lupaCatat = (int)($row['lupa_catat'] ?? 0);
            $mangkir = (int)($row['mangkir'] ?? $alpha);
            $kajianHadir = (int)($row['kajian_hadir'] ?? 4);
            $kajianTotal = (int)($row['kajian_total'] ?? 4);

            Database::execute(
                "INSERT INTO employee_attendance 
                    (employee_id, unit_id, academic_year_id, month_num, hadir, sakit, izin, alpha, mangkir, terlambat, terlambat_menit, pulang_cepat, lupa_catat, kajian_hadir, kajian_total, recorded_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE 
                    hadir = VALUES(hadir), sakit = VALUES(sakit), izin = VALUES(izin), alpha = VALUES(alpha), mangkir = VALUES(mangkir),
                    terlambat = VALUES(terlambat), terlambat_menit = VALUES(terlambat_menit),
                    pulang_cepat = VALUES(pulang_cepat), lupa_catat = VALUES(lupa_catat),
                    kajian_hadir = VALUES(kajian_hadir), kajian_total = VALUES(kajian_total)",
                [(int)$empId, $unitId, $taId, $monthNum, $hadir, $sakit, $izin, $alpha, $mangkir, $terlambat, $terlambatMenit, $pulangCepat, $lupaCatat, $kajianHadir, $kajianTotal, $userId]
            );
        }

        AuditLog::log('SAVE_STAFF_ATTENDANCE', 'employee_attendance', $unitId, null, ['month' => $monthNum, 'count' => count($attData)]);
        $this->setFlash('success', 'Presensi kehadiran staf berhasil disimpan.');
        $this->redirect(Auth::baseUrl() . '/attendance/staff?month_num=' . $monthNum);
    }

    // --- 3. ABSENSI SANTRI (WALI KELAS & GURU TAHFIDZ) ---
    public function studentsAttendance(): void {
        $this->requireRole(['walas', 'waka_kesiswaan', 'kepala_sekolah', 'deputi_pendidikan', 'direktur_pendidikan', 'guru_tahfidz']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $empId = Auth::employeeId() ?? 0;
        $activeRole = Auth::activeRoleName();
        $isWalasOnly = ($activeRole === 'walas' && !Auth::hasAnyRole(['waka_kesiswaan', 'kepala_sekolah', 'deputi_pendidikan']));
        $isTahfidzOnly = ($activeRole === 'guru_tahfidz');

        $classes = [];
        $halaqahList = [];
        $selectedClassId = 0;
        $selectedHalaqahId = 0;

        if ($isTahfidzOnly) {
            $halaqahList = Database::fetchAll(
                "SELECT * FROM tahfidz_halaqah 
                 WHERE tutor_employee_id = ? AND academic_year_id = ? AND type = 'Siswa' 
                 ORDER BY name ASC",
                [$empId, $taId]
            );
            if (empty($halaqahList)) {
                $halaqahList = Database::fetchAll(
                    "SELECT * FROM tahfidz_halaqah WHERE unit_id = ? AND academic_year_id = ? AND type = 'Siswa' ORDER BY name ASC",
                    [$unitId, $taId]
                );
            }
            $selectedHalaqahId = (int)($_GET['halaqah_id'] ?? ($halaqahList[0]['id'] ?? 0));
        } else {
            if ($isWalasOnly) {
                $classes = Database::fetchAll(
                    "SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? AND (walas_id = ? OR walas_employee_id = ? OR walas_id = ?) ORDER BY name ASC",
                    [$unitId, $taId, $empId, $empId, Auth::id()]
                );
                if (empty($classes)) {
                    // Fallback jika tidak tercatat di walas_id, ambil dari unit
                    $classes = Database::fetchAll("SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, $taId]);
                }
            } else {
                $classes = Database::fetchAll("SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, $taId]);
            }
            $selectedClassId = (int)($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
        }

        $monthNum = (int)($_GET['month_num'] ?? date('n'));

        // 12-Month Status Tracker untuk kelas / halaqah yang dipilih
        $monthStatus = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStatus[$m] = [
                'is_filled'   => false,
                'count'       => 0,
                'avg_pct'     => 0.0,
                'sum_hadir'   => 0,
                'sum_sakit'   => 0,
                'sum_izin'    => 0,
                'sum_alpha'   => 0
            ];
        }

        if ($isTahfidzOnly && $selectedHalaqahId) {
            $memberRows = Database::fetchAll("SELECT student_id FROM tahfidz_halaqah_members WHERE halaqah_id = ?", [$selectedHalaqahId]);
            $sIds = array_column($memberRows, 'student_id');
            if (!empty($sIds)) {
                $inS = implode(',', array_fill(0, count($sIds), '?'));
                $monthlySummary = Database::fetchAll(
                    "SELECT month_num, COUNT(id) as cnt, AVG(attendance_pct) as avg_att, 
                            SUM(hadir) as sum_hadir, SUM(sakit) as sum_sakit, SUM(izin) as sum_izin, SUM(alpha) as sum_alpha 
                     FROM student_attendance 
                     WHERE student_id IN ($inS) AND academic_year_id = ? 
                     GROUP BY month_num",
                    array_merge($sIds, [$taId])
                );
                foreach ($monthlySummary as $ms) {
                    $mNum = (int)$ms['month_num'];
                    if (isset($monthStatus[$mNum])) {
                        $monthStatus[$mNum]['is_filled'] = ((int)$ms['cnt'] > 0);
                        $monthStatus[$mNum]['count']     = (int)$ms['cnt'];
                        $monthStatus[$mNum]['avg_pct']   = round((float)$ms['avg_att'], 1);
                        $monthStatus[$mNum]['sum_hadir'] = (int)($ms['sum_hadir'] ?? 0);
                        $monthStatus[$mNum]['sum_sakit'] = (int)($ms['sum_sakit'] ?? 0);
                        $monthStatus[$mNum]['sum_izin']  = (int)($ms['sum_izin'] ?? 0);
                        $monthStatus[$mNum]['sum_alpha'] = (int)($ms['sum_alpha'] ?? 0);
                    }
                }
            }
        } elseif ($selectedClassId) {
            $monthlySummary = Database::fetchAll(
                "SELECT month_num, COUNT(id) as cnt, AVG(attendance_pct) as avg_att, 
                        SUM(hadir) as sum_hadir, SUM(sakit) as sum_sakit, SUM(izin) as sum_izin, SUM(alpha) as sum_alpha 
                 FROM student_attendance 
                 WHERE class_id = ? AND academic_year_id = ? 
                 GROUP BY month_num",
                [$selectedClassId, $taId]
            );
            foreach ($monthlySummary as $ms) {
                $mNum = (int)$ms['month_num'];
                if (isset($monthStatus[$mNum])) {
                    $monthStatus[$mNum]['is_filled'] = ((int)$ms['cnt'] > 0);
                    $monthStatus[$mNum]['count']     = (int)$ms['cnt'];
                    $monthStatus[$mNum]['avg_pct']   = round((float)$ms['avg_att'], 1);
                    $monthStatus[$mNum]['sum_hadir'] = (int)($ms['sum_hadir'] ?? 0);
                    $monthStatus[$mNum]['sum_sakit'] = (int)($ms['sum_sakit'] ?? 0);
                    $monthStatus[$mNum]['sum_izin']  = (int)($ms['sum_izin'] ?? 0);
                    $monthStatus[$mNum]['sum_alpha'] = (int)($ms['sum_alpha'] ?? 0);
                }
            }
        }

        $students = [];
        if ($isTahfidzOnly && $selectedHalaqahId) {
            $fallbackClass = Database::fetch("SELECT id FROM classes WHERE unit_id = ? AND academic_year_id = ? LIMIT 1", [$unitId, $taId]);
            $defaultClassId = (int)($fallbackClass['id'] ?? 1);

            $students = Database::fetchAll(
                "SELECT s.id as student_id, s.nama, s.nis, s.gender, 
                        COALESCE(c.name, 'Kelas Santri') as class_name,
                        COALESCE(cs.class_id, ?) as class_id,
                        sa.hadir, sa.sakit, sa.izin, sa.alpha 
                 FROM tahfidz_halaqah_members m 
                 JOIN students s ON m.student_id = s.id 
                 LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                 LEFT JOIN classes c ON cs.class_id = c.id 
                 LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                      AND sa.academic_year_id = ? 
                      AND sa.month_num = ? 
                 WHERE m.halaqah_id = ? 
                 ORDER BY s.nama ASC",
                [$defaultClassId, $taId, $monthNum, $selectedHalaqahId]
            );
        } elseif ($selectedClassId) {
            $students = Database::fetchAll(
                "SELECT s.id as student_id, cs.class_id, s.nama, s.nis, s.gender, 
                        sa.hadir, sa.sakit, sa.izin, sa.alpha 
                 FROM class_students cs 
                 JOIN students s ON cs.student_id = s.id 
                 LEFT JOIN student_attendance sa ON s.id = sa.student_id 
                      AND sa.class_id = cs.class_id 
                      AND sa.academic_year_id = ? 
                      AND sa.month_num = ? 
                 WHERE cs.class_id = ? AND cs.status = 'Aktif' 
                 ORDER BY s.nama ASC",
                [$taId, $monthNum, $selectedClassId]
            );
        }

        $pageTitle = $isTahfidzOnly ? 'Presensi Bulanan Santri Halaqah (Guru Tahfidz)' : 'Presensi Bulanan Santri Kelas (Wali Kelas)';

        $this->view('attendance/students', [
            'pageTitle'         => $pageTitle,
            'classes'           => $classes,
            'selectedClassId'   => $selectedClassId,
            'monthNum'          => $monthNum,
            'students'          => $students,
            'monthStatus'       => $monthStatus,
            'isTahfidzOnly'     => $isTahfidzOnly,
            'halaqahList'       => $halaqahList,
            'selectedHalaqahId' => $selectedHalaqahId
        ]);
    }

    public function saveStudentsAttendance(): void {
        $this->requireRole(['walas', 'waka_kesiswaan', 'guru_tahfidz']);
        $classId = (int)($_POST['class_id'] ?? 0);
        $halaqahId = (int)($_POST['halaqah_id'] ?? 0);
        $monthNum = (int)($_POST['month_num'] ?? date('n'));
        $attData = $_POST['att'] ?? [];
        $taId = Auth::academicYearId();
        $unitId = Auth::unitId();
        $userId = Auth::id();

        foreach ($attData as $sId => $row) {
            $studentClassId = (int)($row['class_id'] ?? $classId);
            if ($studentClassId <= 0) {
                $cs = Database::fetch("SELECT class_id FROM class_students WHERE student_id = ? AND status = 'Aktif' LIMIT 1", [(int)$sId]);
                $studentClassId = (int)($cs['class_id'] ?? 0);
            }
            if ($studentClassId <= 0) {
                $firstClass = Database::fetch("SELECT id FROM classes WHERE unit_id = ? AND academic_year_id = ? LIMIT 1", [$unitId, $taId]);
                $studentClassId = (int)($firstClass['id'] ?? 1);
            }

            $hadir = (int)($row['hadir'] ?? 20);
            $sakit = (int)($row['sakit'] ?? 0);
            $izin = (int)($row['izin'] ?? 0);
            $alpha = (int)($row['alpha'] ?? 0);
            $totalDays = max(1, $hadir + $sakit + $izin + $alpha);
            $pct = round(($hadir / $totalDays) * 100, 2);

            Database::execute(
                "INSERT INTO student_attendance 
                    (student_id, class_id, academic_year_id, month_num, hadir, effective_days, sakit, izin, alpha, alfa, attendance_pct, created_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE 
                    hadir = VALUES(hadir), effective_days = VALUES(effective_days), sakit = VALUES(sakit), 
                    izin = VALUES(izin), alpha = VALUES(alpha), alfa = VALUES(alfa), attendance_pct = VALUES(attendance_pct)",
                [(int)$sId, $studentClassId, $taId, $monthNum, $hadir, $totalDays, $sakit, $izin, $alpha, $alpha, $pct, $userId]
            );
        }

        AuditLog::log('SAVE_STUDENT_ATTENDANCE', 'student_attendance', $halaqahId ?: $classId, null, ['month' => $monthNum, 'count' => count($attData)]);
        $this->setFlash('success', 'Presensi santri berhasil disimpan.');
        if ($halaqahId > 0) {
            $this->redirect(Auth::baseUrl() . "/attendance/students?halaqah_id={$halaqahId}&month_num={$monthNum}");
        } else {
            $this->redirect(Auth::baseUrl() . "/attendance/students?class_id={$classId}&month_num={$monthNum}");
        }
    }
}

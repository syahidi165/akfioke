<?php
// app/Controllers/AbsensiController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AbsensiController extends Controller {

    // --- 1. ABSENSI GURU & KAJIAN SABTU (DIINPUT OLEH TU) ---
    public function teachersAttendance(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'waka_kurikulum', 'deputi_pendidikan']);
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
            'pageTitle'    => 'Presensi Bulanan Guru & Kajian Sabtu',
            'teachers'     => $teachers,
            'monthNum'     => $monthNum,
            'filledMonths' => $filledMonths
        ]);
    }

    public function saveTeachersAttendance(): void {
        $this->requireRole(['tata_usaha', 'waka_kurikulum', 'kepala_sekolah']);
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

    // --- 2. ABSENSI STAF (TU, CS, SECURITY) (DIINPUT OLEH TU) ---
    public function staffAttendance(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'deputi_pendidikan']);
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
            'pageTitle' => 'Presensi Bulanan Karyawan & Staf (TU, CS & Security)',
            'staff'     => $staff,
            'monthNum'  => $monthNum
        ]);
    }

    public function saveStaffAttendance(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah']);
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

    // --- 4. EXCEL IMPORT & TEMPLATE PRESENSI GURU ---
    public function teachersAttendanceTemplate(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'waka_kurikulum']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $monthNum = (int)($_GET['month_num'] ?? date('n'));
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $unitCode = $unit['code'] ?? 'UNIT';

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNames[$monthNum] ?? "Bulan-{$monthNum}";

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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Guru-{$monthName}", 0, 30));

        $headers = [
            'ID Pegawai (Jangan Diubah)',
            'NIPY',
            'Nama Lengkap Guru',
            'Posisi / Jabatan',
            'Hadir (Hari)',
            'Sakit (Hari)',
            'Izin (Hari)',
            'Alpha (Hari)',
            'Terlambat (Menit)',
            'Pulang Cepat (Hari)',
            'Lupa Catat (Hari)',
            'Kajian Hadir (Pekan)',
            'Kajian Total (Pekan)'
        ];

        // Header Styling
        $colIndex = 1;
        foreach ($headers as $h) {
            $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '1';
            $sheet->setCellValue($coord, $h);
            $colIndex++;
        }

        $headerEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1';
        $sheet->getStyle("A1:{$headerEnd}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0284C7']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Pre-fill Teachers data
        $rowNum = 2;
        foreach ($teachers as $t) {
            $sheet->setCellValue("A{$rowNum}", (int)$t['id']);
            $sheet->setCellValueExplicit("B{$rowNum}", SecurityHelper::cleanFormula($t['nipy'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$rowNum}", SecurityHelper::cleanFormula($t['nama']));
            $sheet->setCellValue("D{$rowNum}", SecurityHelper::cleanFormula($t['level_posisi'] ?: ($t['position'] ?: 'Guru')));
            $sheet->setCellValue("E{$rowNum}", $t['hadir'] !== null ? (int)$t['hadir'] : 20);
            $sheet->setCellValue("F{$rowNum}", $t['sakit'] !== null ? (int)$t['sakit'] : 0);
            $sheet->setCellValue("G{$rowNum}", $t['izin'] !== null ? (int)$t['izin'] : 0);
            $sheet->setCellValue("H{$rowNum}", $t['alpha'] !== null ? (int)$t['alpha'] : 0);
            $sheet->setCellValue("I{$rowNum}", $t['terlambat_menit'] !== null ? (int)$t['terlambat_menit'] : ($t['terlambat'] ?? 0));
            $sheet->setCellValue("J{$rowNum}", $t['pulang_cepat'] !== null ? (int)$t['pulang_cepat'] : 0);
            $sheet->setCellValue("K{$rowNum}", $t['lupa_catat'] !== null ? (int)$t['lupa_catat'] : 0);
            $sheet->setCellValue("L{$rowNum}", $t['kajian_hadir'] !== null ? (int)$t['kajian_hadir'] : 4);
            $sheet->setCellValue("M{$rowNum}", $t['kajian_total'] !== null ? (int)$t['kajian_total'] : 4);
            $rowNum++;
        }

        $lastRow = max(2, $rowNum - 1);
        $dataEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . $lastRow;
        $sheet->getStyle("A2:{$dataEnd}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        // Auto-fit columns
        foreach (range(1, count($headers)) as $c) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = "Format_Absensi_Guru_{$unitCode}_Bulan_{$monthNum}_{$monthName}";
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function teachersAttendanceImport(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah', 'waka_kurikulum']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $monthNum = (int)($_POST['month_num'] ?? date('n'));

        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Gagal mengunggah berkas. Pastikan Anda telah memilih berkas Excel.');
            $this->redirect(Auth::baseUrl() . '/attendance/teachers?month_num=' . $monthNum);
            return;
        }

        $tmpFile = $_FILES['excel_file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->setFlash('danger', 'Hanya berkas Excel dengan ekstensi .xlsx atau .xls yang diperbolehkan.');
            $this->redirect(Auth::baseUrl() . '/attendance/teachers?month_num=' . $monthNum);
            return;
        }

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            $this->redirect(Auth::baseUrl() . '/attendance/teachers?month_num=' . $monthNum);
            return;
        }

        $saved = 0;
        $skipped = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $empId = (int)$sheet->getCell("A{$r}")->getValue();
            $nipy = trim((string)$sheet->getCell("B{$r}")->getValue());
            $nama = trim((string)$sheet->getCell("C{$r}")->getValue());

            $hadir = (int)$sheet->getCell("E{$r}")->getValue();
            $sakit = (int)$sheet->getCell("F{$r}")->getValue();
            $izin = (int)$sheet->getCell("G{$r}")->getValue();
            $alpha = (int)$sheet->getCell("H{$r}")->getValue();
            $terlambat = (int)$sheet->getCell("I{$r}")->getValue();
            $pulangCepat = (int)$sheet->getCell("J{$r}")->getValue();
            $lupaCatat = (int)$sheet->getCell("K{$r}")->getValue();
            $kajianHadir = (int)$sheet->getCell("L{$r}")->getValue();
            $kajianTotal = (int)$sheet->getCell("M{$r}")->getValue();

            // Match Pegawai
            $emp = null;
            if ($empId > 0) {
                $emp = Database::fetch("SELECT id, nama FROM employees WHERE id = ? AND unit_id = ?", [$empId, $unitId]);
            }
            if (!$emp && !empty($nipy) && $nipy !== '-') {
                $emp = Database::fetch("SELECT id, nama FROM employees WHERE nipy = ? AND unit_id = ?", [$nipy, $unitId]);
            }
            if (!$emp && !empty($nama)) {
                $emp = Database::fetch("SELECT id, nama FROM employees WHERE nama = ? AND unit_id = ?", [$nama, $unitId]);
            }

            if (!$emp) {
                $skipped++;
                continue;
            }

            $targetEmpId = (int)$emp['id'];
            if ($kajianTotal <= 0) $kajianTotal = 4;

            Database::execute(
                "INSERT INTO employee_attendance 
                    (employee_id, unit_id, academic_year_id, month_num, hadir, sakit, izin, alpha, mangkir, terlambat, terlambat_menit, pulang_cepat, lupa_catat, kajian_hadir, kajian_total, recorded_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE 
                    hadir = VALUES(hadir), sakit = VALUES(sakit), izin = VALUES(izin), alpha = VALUES(alpha), mangkir = VALUES(mangkir),
                    terlambat = VALUES(terlambat), terlambat_menit = VALUES(terlambat_menit),
                    pulang_cepat = VALUES(pulang_cepat), lupa_catat = VALUES(lupa_catat),
                    kajian_hadir = VALUES(kajian_hadir), kajian_total = VALUES(kajian_total),
                    recorded_by_user_id = VALUES(recorded_by_user_id)",
                [$targetEmpId, $unitId, $taId, $monthNum, $hadir, $sakit, $izin, $alpha, $alpha, $terlambat, $terlambat, $pulangCepat, $lupaCatat, $kajianHadir, $kajianTotal, $userId]
            );
            $saved++;
        }

        AuditLog::log('IMPORT_TEACHERS_ATTENDANCE', 'employee_attendance', $unitId, null, [
            'month' => $monthNum, 'saved' => $saved, 'skipped' => $skipped
        ]);

        $this->setFlash('success', "Alhamdulillah, berhasil mengimpor presensi {$saved} guru untuk bulan ke-{$monthNum}. (Dilewati: {$skipped})");
        $this->redirect(Auth::baseUrl() . '/attendance/teachers?month_num=' . $monthNum);
    }

    // --- 5. EXCEL IMPORT & TEMPLATE PRESENSI STAF ---
    public function staffAttendanceTemplate(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $monthNum = (int)($_GET['month_num'] ?? date('n'));
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $unitCode = $unit['code'] ?? 'UNIT';

        $monthNames = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        $monthName = $monthNames[$monthNum] ?? "Bulan-{$monthNum}";

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

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle(substr("Staf-{$monthName}", 0, 30));

        $headers = [
            'ID Pegawai (Jangan Diubah)',
            'NIPY',
            'Nama Lengkap Staf',
            'Posisi / Divisi',
            'Hadir (Hari)',
            'Sakit (Hari)',
            'Izin (Hari)',
            'Alpha (Hari)',
            'Terlambat (Menit)',
            'Pulang Cepat (Hari)',
            'Lupa Catat (Hari)',
            'Kajian Hadir (Pekan)',
            'Kajian Total (Pekan)'
        ];

        // Header Styling
        $colIndex = 1;
        foreach ($headers as $h) {
            $coord = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex) . '1';
            $sheet->setCellValue($coord, $h);
            $colIndex++;
        }

        $headerEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . '1';
        $sheet->getStyle("A1:{$headerEnd}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F766E']], // Teal
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // Pre-fill Staff data
        $rowNum = 2;
        foreach ($staff as $s) {
            $sheet->setCellValue("A{$rowNum}", (int)$s['id']);
            $sheet->setCellValueExplicit("B{$rowNum}", SecurityHelper::cleanFormula($s['nipy'] ?? '-'), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
            $sheet->setCellValue("C{$rowNum}", SecurityHelper::cleanFormula($s['nama']));
            $sheet->setCellValue("D{$rowNum}", SecurityHelper::cleanFormula($s['level_posisi'] ?: ($s['position'] ?: 'Staf')));
            $sheet->setCellValue("E{$rowNum}", $s['hadir'] !== null ? (int)$s['hadir'] : 24);
            $sheet->setCellValue("F{$rowNum}", $s['sakit'] !== null ? (int)$s['sakit'] : 0);
            $sheet->setCellValue("G{$rowNum}", $s['izin'] !== null ? (int)$s['izin'] : 0);
            $sheet->setCellValue("H{$rowNum}", $s['alpha'] !== null ? (int)$s['alpha'] : 0);
            $sheet->setCellValue("I{$rowNum}", $s['terlambat_menit'] !== null ? (int)$s['terlambat_menit'] : ($s['terlambat'] ?? 0));
            $sheet->setCellValue("J{$rowNum}", $s['pulang_cepat'] !== null ? (int)$s['pulang_cepat'] : 0);
            $sheet->setCellValue("K{$rowNum}", $s['lupa_catat'] !== null ? (int)$s['lupa_catat'] : 0);
            $sheet->setCellValue("L{$rowNum}", $s['kajian_hadir'] !== null ? (int)$s['kajian_hadir'] : 4);
            $sheet->setCellValue("M{$rowNum}", $s['kajian_total'] !== null ? (int)$s['kajian_total'] : 4);
            $rowNum++;
        }

        $lastRow = max(2, $rowNum - 1);
        $dataEnd = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers)) . $lastRow;
        $sheet->getStyle("A2:{$dataEnd}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        foreach (range(1, count($headers)) as $c) {
            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($c);
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }

        $filename = "Format_Absensi_Staf_{$unitCode}_Bulan_{$monthNum}_{$monthName}";
        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function staffAttendanceImport(): void {
        $this->requireRole(['tata_usaha', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $monthNum = (int)($_POST['month_num'] ?? date('n'));

        if (!isset($_FILES['excel_file']) || $_FILES['excel_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Gagal mengunggah berkas. Pastikan Anda telah memilih berkas Excel.');
            $this->redirect(Auth::baseUrl() . '/attendance/staff?month_num=' . $monthNum);
            return;
        }

        $tmpFile = $_FILES['excel_file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['excel_file']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->setFlash('danger', 'Hanya berkas Excel dengan ekstensi .xlsx atau .xls yang diperbolehkan.');
            $this->redirect(Auth::baseUrl() . '/attendance/staff?month_num=' . $monthNum);
            return;
        }

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            $this->redirect(Auth::baseUrl() . '/attendance/staff?month_num=' . $monthNum);
            return;
        }

        $saved = 0;
        $skipped = 0;

        for ($r = 2; $r <= $highestRow; $r++) {
            $empId = (int)$sheet->getCell("A{$r}")->getValue();
            $nipy = trim((string)$sheet->getCell("B{$r}")->getValue());
            $nama = trim((string)$sheet->getCell("C{$r}")->getValue());

            $hadir = (int)$sheet->getCell("E{$r}")->getValue();
            $sakit = (int)$sheet->getCell("F{$r}")->getValue();
            $izin = (int)$sheet->getCell("G{$r}")->getValue();
            $alpha = (int)$sheet->getCell("H{$r}")->getValue();
            $terlambat = (int)$sheet->getCell("I{$r}")->getValue();
            $pulangCepat = (int)$sheet->getCell("J{$r}")->getValue();
            $lupaCatat = (int)$sheet->getCell("K{$r}")->getValue();
            $kajianHadir = (int)$sheet->getCell("L{$r}")->getValue();
            $kajianTotal = (int)$sheet->getCell("M{$r}")->getValue();

            // Match Pegawai
            $emp = null;
            if ($empId > 0) {
                $emp = Database::fetch("SELECT id, nama FROM employees WHERE id = ? AND unit_id = ?", [$empId, $unitId]);
            }
            if (!$emp && !empty($nipy) && $nipy !== '-') {
                $emp = Database::fetch("SELECT id, nama FROM employees WHERE nipy = ? AND unit_id = ?", [$nipy, $unitId]);
            }
            if (!$emp && !empty($nama)) {
                $emp = Database::fetch("SELECT id, nama FROM employees WHERE nama = ? AND unit_id = ?", [$nama, $unitId]);
            }

            if (!$emp) {
                $skipped++;
                continue;
            }

            $targetEmpId = (int)$emp['id'];
            if ($kajianTotal <= 0) $kajianTotal = 4;

            Database::execute(
                "INSERT INTO employee_attendance 
                    (employee_id, unit_id, academic_year_id, month_num, hadir, sakit, izin, alpha, mangkir, terlambat, terlambat_menit, pulang_cepat, lupa_catat, kajian_hadir, kajian_total, recorded_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE 
                    hadir = VALUES(hadir), sakit = VALUES(sakit), izin = VALUES(izin), alpha = VALUES(alpha), mangkir = VALUES(mangkir),
                    terlambat = VALUES(terlambat), terlambat_menit = VALUES(terlambat_menit),
                    pulang_cepat = VALUES(pulang_cepat), lupa_catat = VALUES(lupa_catat),
                    kajian_hadir = VALUES(kajian_hadir), kajian_total = VALUES(kajian_total),
                    recorded_by_user_id = VALUES(recorded_by_user_id)",
                [$targetEmpId, $unitId, $taId, $monthNum, $hadir, $sakit, $izin, $alpha, $alpha, $terlambat, $terlambat, $pulangCepat, $lupaCatat, $kajianHadir, $kajianTotal, $userId]
            );
            $saved++;
        }

        AuditLog::log('IMPORT_STAFF_ATTENDANCE', 'employee_attendance', $unitId, null, [
            'month' => $monthNum, 'saved' => $saved, 'skipped' => $skipped
        ]);

        $this->setFlash('success', "Alhamdulillah, berhasil mengimpor presensi {$saved} staf untuk bulan ke-{$monthNum}. (Dilewati: {$skipped})");
        $this->redirect(Auth::baseUrl() . '/attendance/staff?month_num=' . $monthNum);
    }
}

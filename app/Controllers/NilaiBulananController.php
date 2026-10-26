<?php
// app/Controllers/NilaiBulananController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\ExportHelper;
use Core\SecurityHelper;

class NilaiBulananController extends Controller {

    // --- 1. INPUT NILAI BULANAN MAPEL ---
    // --- 1. INPUT NILAI BULANAN MAPEL ---
    public function index(): void {
        $this->requireRole(['guru_mapel', 'guru_mapel_kbtk', 'guru_tahta', 'guru_tk', 'guru_diniyah', 'waka_kurikulum', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $isExecutive = Auth::hasAnyRole(['waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan']);

        if (!$isExecutive) {
            // Guru Mapel / Guru Diniyah isolation: hanya kelas & mapel yang diajarkan
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [Auth::id()]);
            $empId = (int)($emp['id'] ?? 0);

            $classes = Database::fetchAll(
                "SELECT DISTINCT c.* 
                 FROM classes c 
                 JOIN teacher_subjects ts ON c.id = ts.class_id 
                 WHERE ts.employee_id = ? AND ts.academic_year_id = ? 
                 ORDER BY c.name ASC",
                [$empId, $taId]
            );

            $allowedClassIds = array_column($classes, 'id');
            $reqClassId = (int)($_GET['class_id'] ?? 0);
            if ($reqClassId > 0 && in_array($reqClassId, $allowedClassIds, true)) {
                $selectedClassId = $reqClassId;
            } else {
                $selectedClassId = (int)($classes[0]['id'] ?? 0);
            }

            $subjects = [];
            if ($selectedClassId > 0) {
                $subjects = Database::fetchAll(
                    "SELECT DISTINCT s.* 
                     FROM subjects s 
                     JOIN teacher_subjects ts ON s.id = ts.subject_id 
                     WHERE ts.employee_id = ? AND ts.class_id = ? AND ts.academic_year_id = ? 
                     ORDER BY s.name ASC",
                    [$empId, $selectedClassId, $taId]
                );
            }

            $allowedSubjectIds = array_column($subjects, 'id');
            $reqSubjectId = (int)($_GET['subject_id'] ?? 0);
            if ($reqSubjectId > 0 && in_array($reqSubjectId, $allowedSubjectIds, true)) {
                $selectedSubjectId = $reqSubjectId;
            } else {
                $selectedSubjectId = (int)($subjects[0]['id'] ?? 0);
            }
        } else {
            // Executive / Waka / Kepsek: dapat melihat seluruh kelas & mapel pada unit
            $classes = Database::fetchAll("SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, $taId]);
            $subjects = Database::fetchAll("SELECT * FROM subjects WHERE unit_id = ? ORDER BY name ASC", [$unitId]);

            $selectedClassId = (int)($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
            $selectedSubjectId = (int)($_GET['subject_id'] ?? ($subjects[0]['id'] ?? 0));
        }

        $selectedMonth = (int)($_GET['month_num'] ?? date('n'));

        $students = [];
        if ($selectedClassId && $selectedSubjectId) {
            $students = Database::fetchAll(
                "SELECT s.id as student_id, s.nama, s.nis, s.gender,
                        g.score, g.kkm, g.is_locked
                 FROM class_students cs 
                 JOIN students s ON cs.student_id = s.id 
                 LEFT JOIN student_grades g ON s.id = g.student_id 
                      AND g.class_id = cs.class_id 
                      AND g.subject_id = ? 
                      AND g.academic_year_id = ? 
                      AND g.month_num = ?
                 WHERE cs.class_id = ? AND cs.status = 'Aktif'
                 ORDER BY s.nama ASC",
                [$selectedSubjectId, $taId, $selectedMonth, $selectedClassId]
            );
        }

        $monthStatus = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStatus[$m] = [
                'is_filled' => false,
                'count'     => 0,
                'avg'       => 0,
                'kkm'       => 75.0
            ];
        }

        if ($selectedClassId && $selectedSubjectId) {
            $monthlySummary = Database::fetchAll(
                "SELECT month_num, COUNT(id) as cnt, AVG(score) as avg_score, MAX(kkm) as kkm_val 
                 FROM student_grades 
                 WHERE class_id = ? AND subject_id = ? AND academic_year_id = ? AND score IS NOT NULL 
                 GROUP BY month_num",
                [$selectedClassId, $selectedSubjectId, $taId]
            );
            foreach ($monthlySummary as $ms) {
                $mNum = (int)$ms['month_num'];
                if (isset($monthStatus[$mNum])) {
                    $monthStatus[$mNum]['is_filled'] = ((int)$ms['cnt'] > 0);
                    $monthStatus[$mNum]['count']     = (int)$ms['cnt'];
                    $monthStatus[$mNum]['avg']       = round((float)$ms['avg_score'], 1);
                    $monthStatus[$mNum]['kkm']       = (float)($ms['kkm_val'] ?? 75);
                }
            }
        }

        $this->view('nilai/index', [
            'pageTitle'         => 'Input Nilai Akademik Bulanan Siswa',
            'classes'           => $classes,
            'subjects'          => $subjects,
            'selectedClassId'   => $selectedClassId,
            'selectedSubjectId' => $selectedSubjectId,
            'selectedMonth'     => $selectedMonth,
            'students'          => $students,
            'monthStatus'       => $monthStatus,
            'isExecutive'       => $isExecutive
        ]);
    }

    public function saveGrades(): void {
        $this->requireRole(['guru_mapel', 'guru_mapel_kbtk', 'guru_tahta', 'guru_tk', 'guru_diniyah', 'waka_kurikulum']);
        $classId = (int)($_POST['class_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $monthNum = (int)($_POST['month_num'] ?? date('n'));
        $kkm = (float)($_POST['kkm'] ?? 75.0);
        $scores = $_POST['score'] ?? [];

        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $isExecutive = Auth::hasAnyRole(['waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan']);

        // Validasi hak mengajar guru mapel
        if (!$isExecutive) {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [$userId]);
            $empId = (int)($emp['id'] ?? 0);

            $isAssigned = Database::fetch(
                "SELECT id FROM teacher_subjects WHERE employee_id = ? AND subject_id = ? AND class_id = ? AND academic_year_id = ?",
                [$empId, $subjectId, $classId, $taId]
            );

            if (!$isAssigned) {
                $this->setFlash('danger', 'Akses Ditolak: Anda tidak ditugaskan mengajar mata pelajaran ini pada kelas tersebut.');
                $this->redirect(Auth::baseUrl() . "/nilai/bulanan");
                return;
            }
        }

        foreach ($scores as $studentId => $val) {
            if ($val === '' || $val === null) continue;
            $score = (float)$val;

            Database::execute(
                "INSERT INTO student_grades (student_id, class_id, subject_id, academic_year_id, month_num, score, kkm, created_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?) 
                 ON DUPLICATE KEY UPDATE score = VALUES(score), kkm = VALUES(kkm)",
                [(int)$studentId, $classId, $subjectId, $taId, $monthNum, $score, $kkm, $userId]
            );
        }

        AuditLog::log('SAVE_STUDENT_GRADES', 'student_grades', $classId, null, [
            'subject_id' => $subjectId, 'month' => $monthNum, 'count' => count($scores)
        ]);

        $this->setFlash('success', 'Nilai bulanan siswa berhasil disimpan.');
        $this->redirect(Auth::baseUrl() . "/nilai/bulanan?class_id={$classId}&subject_id={$subjectId}&month_num={$monthNum}");
    }

    // --- 2. LEDGER NILAI KELAS ---
    public function ledger(): void {
        $this->requireRole(['walas', 'waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $isExecutive = Auth::hasAnyRole(['waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan']);

        if (!$isExecutive && Auth::hasRole('walas')) {
            // Isolasi Walas: hanya kelas yang diampunya sebagai wali kelas
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [Auth::id()]);
            $empId = (int)($emp['id'] ?? 0);

            $classes = Database::fetchAll(
                "SELECT * FROM classes WHERE walas_id = ? AND academic_year_id = ? ORDER BY name ASC",
                [$empId, $taId]
            );

            $allowedClassIds = array_column($classes, 'id');
            $reqClassId = (int)($_GET['class_id'] ?? 0);
            if ($reqClassId > 0 && in_array($reqClassId, $allowedClassIds, true)) {
                $selectedClassId = $reqClassId;
            } else {
                $selectedClassId = (int)($classes[0]['id'] ?? 0);
            }
        } else {
            $classes = Database::fetchAll("SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, $taId]);
            $selectedClassId = (int)($_GET['class_id'] ?? ($classes[0]['id'] ?? 0));
        }

        $selectedMonth = (int)($_GET['month_num'] ?? date('n'));

        $data = $this->getLedgerData($selectedClassId, $selectedMonth, $taId);

        $monthStatus = [];
        for ($m = 1; $m <= 12; $m++) {
            $monthStatus[$m] = false;
        }
        if ($selectedClassId > 0) {
            $filledMonths = Database::fetchAll(
                "SELECT DISTINCT month_num 
                 FROM student_grades 
                 WHERE class_id = ? AND academic_year_id = ?",
                [$selectedClassId, $taId]
            );
            foreach ($filledMonths as $fm) {
                $monthStatus[(int)$fm['month_num']] = true;
            }
        }

        $this->view('nilai/ledger', [
            'pageTitle'       => 'Ledger Rekap Nilai Bulanan Kelas',
            'classes'         => $classes,
            'selectedClassId' => $selectedClassId,
            'selectedMonth'   => $selectedMonth,
            'subjects'        => $data['subjects'],
            'students'        => $data['students'],
            'className'       => $data['className'],
            'monthStatus'     => $monthStatus
        ]);
    }

    public function exportLedgerXlsx(): void {
        $this->requireRole(['walas', 'waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan']);
        $taId = Auth::academicYearId();
        $classId = (int)($_GET['class_id'] ?? 0);
        $monthNum = (int)($_GET['month_num'] ?? date('n'));
        $isExecutive = Auth::hasAnyRole(['waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan']);

        if (!$isExecutive && Auth::hasRole('walas')) {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [Auth::id()]);
            $empId = (int)($emp['id'] ?? 0);
            $isAssigned = Database::fetch("SELECT id FROM classes WHERE id = ? AND walas_id = ? AND academic_year_id = ?", [$classId, $empId, $taId]);
            if (!$isAssigned) {
                http_response_code(403);
                die('Akses Ditolak: Anda hanya memiliki wewenang mengunduh ledger untuk kelas binaan Anda.');
            }
        }

        $data = $this->getLedgerData($classId, $monthNum, $taId);

        $headers = ['No', 'NIS', 'Nama Siswa'];
        foreach ($data['subjects'] as $sub) {
            $headers[] = $sub['code'];
        }
        $headers[] = 'Total Nilai';
        $headers[] = 'Rata-rata';

        $rows = [];
        foreach ($data['students'] as $idx => $st) {
            $r = [
                $idx + 1,
                $st['nis'],
                $st['nama']
            ];
            foreach ($data['subjects'] as $sub) {
                $r[] = $st['grades'][$sub['id']] ?? 0;
            }
            $r[] = $st['total'];
            $r[] = $st['avg'];
            $rows[] = $r;
        }

        ExportHelper::xlsx($headers, $rows, 'Ledger_Kelas_' . preg_replace('/[^a-zA-Z0-9]/', '_', $data['className']) . '_Bulan_' . $monthNum);
    }

    public function exportLedgerPdf(): void {
        $this->requireRole(['walas', 'waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan']);
        $taId = Auth::academicYearId();
        $unitId = Auth::unitId();
        $classId = (int)($_GET['class_id'] ?? 0);
        $monthNum = (int)($_GET['month_num'] ?? date('n'));
        $isExecutive = Auth::hasAnyRole(['waka_kurikulum', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan']);

        if (!$isExecutive && Auth::hasRole('walas')) {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [Auth::id()]);
            $empId = (int)($emp['id'] ?? 0);
            $isAssigned = Database::fetch("SELECT id FROM classes WHERE id = ? AND walas_id = ? AND academic_year_id = ?", [$classId, $empId, $taId]);
            if (!$isAssigned) {
                http_response_code(403);
                die('Akses Ditolak: Anda hanya memiliki wewenang mengunduh ledger untuk kelas binaan Anda.');
            }
        }

        $data = $this->getLedgerData($classId, $monthNum, $taId);
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $kop = ExportHelper::renderKop($unit ?: []);

        $monthName = SecurityHelper::getNamaBulan($monthNum);

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 9px; color: #1e293b; }
                .title-doc { text-align: center; font-size: 13px; font-weight: bold; margin: 10px 0; text-transform: uppercase; }
                .meta-table { width: 100%; margin-bottom: 8px; font-size: 10px; }
                .ledger-table { width: 100%; border-collapse: collapse; }
                .ledger-table th, .ledger-table td { border: 1px solid #64748b; padding: 4px; text-align: center; }
                .ledger-table th { background: #f1f5f9; font-weight: bold; }
                .text-start { text-align: left !important; }
            </style>
        </head>
        <body>
            {$kop}
            <div class='title-doc'>LEDGER REKAPITULASI NILAI AKADEMIK KELAS<br><span style='font-size: 10px; font-weight: normal;'>Bulan: {$monthName} | Kelas: {$data['className']}</span></div>

            <table class='ledger-table'>
                <thead>
                    <tr>
                        <th width='4%'>No</th>
                        <th width='10%'>NIS</th>
                        <th width='25%' class='text-start'>Nama Siswa</th>";
        
        foreach ($data['subjects'] as $sub) {
            $html .= "<th>{$sub['code']}</th>";
        }

        $html .= "
                        <th width='8%'>Total</th>
                        <th width='8%'>Rata-rata</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($data['students'] as $idx => $st) {
            $html .= "<tr>
                <td>" . ($idx + 1) . "</td>
                <td>{$st['nis']}</td>
                <td class='text-start'><strong>{$st['nama']}</strong></td>";

            foreach ($data['subjects'] as $sub) {
                $sc = $st['grades'][$sub['id']] ?? '-';
                $html .= "<td>{$sc}</td>";
            }

            $html .= "
                <td><strong>{$st['total']}</strong></td>
                <td><strong>{$st['avg']}</strong></td>
            </tr>";
        }

        $html .= "
                </tbody>
            </table>
        </body>
        </html>";

        ExportHelper::pdf($html, 'Ledger_' . $data['className'] . '_Bulan_' . $monthNum, 'A4', 'landscape', true);
    }

    // --- 3. PRINT RAPOR BULANAN SISWA (PDF) ---
    public function printRaporBulanan(string $id): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'orang_tua']);
        $studentId = (int)$id;
        $monthNum = (int)($_GET['month_num'] ?? date('n'));
        $taId = Auth::academicYearId();

        $student = Database::fetch(
            "SELECT s.*, c.name as class_name, c.id as class_id, u.name as unit_name, 
                    u.kop_title, u.kop_subtitle, u.address, u.phone, u.email,
                    ay.name as academic_year_name, w.nama as walas_name 
             FROM students s 
             JOIN units u ON s.unit_id = u.id 
             JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             JOIN classes c ON cs.class_id = c.id 
             JOIN academic_years ay ON cs.academic_year_id = ay.id 
             LEFT JOIN employees w ON c.walas_id = w.id 
             WHERE s.id = ?",
            [$studentId]
        );

        if (!$student) {
            die('Data santri tidak ditemukan.');
        }

        if (Auth::hasRole('orang_tua')) {
            $userId = Auth::id();
            $user = Auth::user();
            $username = $user['username'] ?? '';
            $checkChild = Database::fetch(
                "SELECT id FROM students WHERE id = ? AND (parent_user_id = ? OR (no_kk = ? AND ? != ''))",
                [$studentId, $userId, $username, $username]
            );
            if (!$checkChild) {
                die('Akses ditolak: Anda tidak memiliki wewenang untuk melihat rapor santri ini.');
            }
        }

        if (Auth::hasRole('walas')) {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [Auth::id()]);
            $empId = (int)($emp['id'] ?? 0);
            $checkWalas = Database::fetch(
                "SELECT id FROM classes WHERE id = ? AND walas_id = ?",
                [(int)$student['class_id'], $empId]
            );
            if (!$checkWalas) {
                http_response_code(403);
                die('Akses ditolak: Anda hanya berwenang mencetak rapor santri pada kelas binaan Anda.');
            }
        }

        $grades = Database::fetchAll(
            "SELECT g.*, sub.name as subject_name, sub.code as subject_code, sub.type as subject_type 
             FROM student_grades g 
             JOIN subjects sub ON g.subject_id = sub.id 
             WHERE g.student_id = ? AND g.academic_year_id = ? AND g.month_num = ? 
             ORDER BY sub.type ASC, sub.name ASC",
            [$studentId, $taId, $monthNum]
        );

        $kop = ExportHelper::renderKop($student);
        $monthName = SecurityHelper::getNamaBulan($monthNum);

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
                .title-doc { text-align: center; font-size: 13px; font-weight: bold; margin: 10px 0; text-transform: uppercase; }
                .meta-table { width: 100%; margin-bottom: 12px; }
                .meta-table td { padding: 3px; vertical-align: top; }
                .table-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .table-data th, .table-data td { border: 1px solid #94a3b8; padding: 6px; }
                .table-data th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .signatures { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; }
            </style>
        </head>
        <body>
            {$kop}
            <div class='title-doc'>LAPORAN HASIL BELAJAR BULANAN SANTRI<br><span style='font-size: 10px; font-weight: normal;'>Bulan: {$monthName} | Tahun Pelajaran {$student['academic_year_name']}</span></div>

            <table class='meta-table'>
                <tr>
                    <td width='18%'><strong>Nama Santri</strong></td><td width='2%'>:</td><td width='40%'>{$student['nama']}</td>
                    <td width='18%'><strong>Kelas</strong></td><td width='2%'>:</td><td width='20%'>{$student['class_name']}</td>
                </tr>
                <tr>
                    <td><strong>NIS</strong></td><td>:</td><td>{$student['nis']}</td>
                    <td><strong>Satuan Unit</strong></td><td>:</td><td>{$student['unit_name']}</td>
                </tr>
            </table>

            <table class='table-data'>
                <thead>
                    <tr>
                        <th width='5%'>No</th>
                        <th>Mata Pelajaran</th>
                        <th width='15%'>Kelompok</th>
                        <th width='12%'>KKM</th>
                        <th width='12%'>Nilai Angka</th>
                        <th width='15%'>Ketercapaian</th>
                    </tr>
                </thead>
                <tbody>";

        $totalScore = 0;
        $cnt = count($grades);
        foreach ($grades as $idx => $g) {
            $totalScore += (float)$g['score'];
            $isTuntas = (float)$g['score'] >= (float)$g['kkm'];
            $pred = $isTuntas ? '<span style="color: green; font-weight: bold;">Tuntas</span>' : '<span style="color: red;">Remedial</span>';

            $html .= "<tr>
                <td align='center'>" . ($idx + 1) . "</td>
                <td>{$g['subject_name']}</td>
                <td align='center'>{$g['subject_type']}</td>
                <td align='center'>{$g['kkm']}</td>
                <td align='center'><strong>{$g['score']}</strong></td>
                <td align='center'>{$pred}</td>
            </tr>";
        }

        $avg = $cnt > 0 ? round($totalScore / $cnt, 1) : 0;

        $html .= "
                    <tr style='background: #f8fafc; font-weight: bold;'>
                        <td colspan='4' align='right'>RATA-RATA NILAI BULANAN :</td>
                        <td align='center' style='color: #0f766e; font-size: 12px;'>{$avg}</td>
                        <td align='center'>" . ($avg >= 75 ? 'Memenuhi Standar' : 'Perlu Bimbingan') . "</td>
                    </tr>
                </tbody>
            </table>

            <table class='signatures'>
                <tr>
                    <td class='sig-box'>
                        Mengetahui,<br><strong>Orang Tua / Wali Santri</strong><br><br><br><br>
                        ( .................................................... )
                    </td>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "<br>
                        <strong>Wali Kelas</strong><br><br><br><br>
                        <strong>( " . ($student['walas_name'] ?: 'Wali Kelas') . " )</strong>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        ExportHelper::pdf($html, 'Rapor_Bulanan_' . $student['nis'] . '_Bulan_' . $monthNum, 'A4', 'portrait', true);
    }

    public function printRaporGabungan(string $id): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'orang_tua']);
        $studentId = (int)$id;
        $monthNum = (int)($_GET['month_num'] ?? date('n'));
        $taId = Auth::academicYearId();

        $student = Database::fetch(
            "SELECT s.*, c.name as class_name, c.id as class_id, u.name as unit_name, 
                    u.kop_title, u.kop_subtitle, u.address, u.phone, u.email,
                    ay.name as academic_year_name, w.nama as walas_name 
             FROM students s 
             JOIN units u ON s.unit_id = u.id 
             JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             JOIN classes c ON cs.class_id = c.id 
             JOIN academic_years ay ON cs.academic_year_id = ay.id 
             LEFT JOIN employees w ON c.walas_id = w.id 
             WHERE s.id = ?",
            [$studentId]
        );

        if (!$student) {
            die('Data santri tidak ditemukan.');
        }

        if (Auth::hasRole('orang_tua')) {
            $userId = Auth::id();
            $user = Auth::user();
            $username = $user['username'] ?? '';
            $checkChild = Database::fetch(
                "SELECT id FROM students WHERE id = ? AND (parent_user_id = ? OR (no_kk = ? AND ? != ''))",
                [$studentId, $userId, $username, $username]
            );
            if (!$checkChild) {
                die('Akses ditolak: Anda tidak memiliki wewenang untuk melihat rapor santri ini.');
            }
        }

        if (Auth::hasRole('walas')) {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [Auth::id()]);
            $empId = (int)($emp['id'] ?? 0);
            $checkWalas = Database::fetch(
                "SELECT id FROM classes WHERE id = ? AND walas_id = ?",
                [(int)$student['class_id'], $empId]
            );
            if (!$checkWalas) {
                http_response_code(403);
                die('Akses ditolak: Anda hanya berwenang mencetak rapor santri pada kelas binaan Anda.');
            }
        }

        $grades = Database::fetchAll(
            "SELECT g.*, sub.name as subject_name, sub.code as subject_code, sub.type as subject_type 
             FROM student_grades g 
             JOIN subjects sub ON g.subject_id = sub.id 
             WHERE g.student_id = ? AND g.academic_year_id = ? AND g.month_num = ? 
             ORDER BY sub.type ASC, sub.name ASC",
            [$studentId, $taId, $monthNum]
        );

        $att = Database::fetch(
            "SELECT hadir, sakit, izin, alpha FROM student_attendance WHERE student_id = ? AND academic_year_id = ? AND month_num = ?",
            [$studentId, $taId, $monthNum]
        );

        $kop = ExportHelper::renderKop($student);
        $monthName = SecurityHelper::getNamaBulan($monthNum);

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
                .title-doc { text-align: center; font-size: 13px; font-weight: bold; margin: 10px 0; text-transform: uppercase; }
                .meta-table { width: 100%; margin-bottom: 12px; }
                .meta-table td { padding: 3px; vertical-align: top; }
                .table-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .table-data th, .table-data td { border: 1px solid #94a3b8; padding: 6px; }
                .table-data th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .signatures { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; }
            </style>
        </head>
        <body>
            {$kop}
            <div class='title-doc'>RAPOR GABUNGAN HASIL BELAJAR & KEHADIRAN SANTRI<br><span style='font-size: 10px; font-weight: normal;'>Bulan: {$monthName} | Tahun Pelajaran {$student['academic_year_name']}</span></div>

            <table class='meta-table'>
                <tr>
                    <td width='18%'><strong>Nama Santri</strong></td><td width='2%'>:</td><td width='40%'>{$student['nama']}</td>
                    <td width='18%'><strong>Kelas</strong></td><td width='2%'>:</td><td width='20%'>{$student['class_name']}</td>
                </tr>
                <tr>
                    <td><strong>NIS</strong></td><td>:</td><td>{$student['nis']}</td>
                    <td><strong>Satuan Unit</strong></td><td>:</td><td>{$student['unit_name']}</td>
                </tr>
            </table>

            <table class='table-data'>
                <thead>
                    <tr>
                        <th width='5%'>No</th>
                        <th>Mata Pelajaran</th>
                        <th width='15%'>Kelompok</th>
                        <th width='12%'>KKM</th>
                        <th width='12%'>Nilai Angka</th>
                        <th width='15%'>Ketercapaian</th>
                    </tr>
                </thead>
                <tbody>";

        $totalScore = 0;
        $cnt = count($grades);
        foreach ($grades as $idx => $g) {
            $totalScore += (float)$g['score'];
            $isTuntas = (float)$g['score'] >= (float)$g['kkm'];
            $pred = $isTuntas ? '<span style="color: green; font-weight: bold;">Tuntas</span>' : '<span style="color: red;">Remedial</span>';

            $html .= "<tr>
                <td align='center'>" . ($idx + 1) . "</td>
                <td>{$g['subject_name']}</td>
                <td align='center'>{$g['subject_type']}</td>
                <td align='center'>{$g['kkm']}</td>
                <td align='center'><strong>{$g['score']}</strong></td>
                <td align='center'>{$pred}</td>
            </tr>";
        }

        $avg = $cnt > 0 ? round($totalScore / $cnt, 1) : 0;

        $html .= "
                    <tr style='background: #f8fafc; font-weight: bold;'>
                        <td colspan='4' align='right'>RATA-RATA NILAI BULANAN :</td>
                        <td align='center' style='color: #0f766e; font-size: 12px;'>{$avg}</td>
                        <td align='center'>" . ($avg >= 75 ? 'Memenuhi Standar' : 'Perlu Bimbingan') . "</td>
                    </tr>
                </tbody>
            </table>";

        // Rekap Kehadiran
        $hadir = $att['hadir'] ?? 0;
        $sakit = $att['sakit'] ?? 0;
        $izin = $att['izin'] ?? 0;
        $alpha = $att['alpha'] ?? 0;

        $html .= "
            <div style='font-weight: bold; margin-bottom: 6px;'>Rekapitulasi Presensi Kehadiran Siswa:</div>
            <table class='table-data' style='width: 60%; margin-bottom: 20px;'>
                <thead>
                    <tr>
                        <th>Hadir</th>
                        <th>Sakit</th>
                        <th>Izin</th>
                        <th>Alpha / Tanpa Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <tr align='center'>
                        <td><strong>{$hadir}</strong> Hari</td>
                        <td>{$sakit} Hari</td>
                        <td>{$izin} Hari</td>
                        <td>{$alpha} Hari</td>
                    </tr>
                </tbody>
            </table>

            <table class='signatures'>
                <tr>
                    <td class='sig-box'>
                        Mengetahui,<br><strong>Orang Tua / Wali Santri</strong><br><br><br><br>
                        ( .................................................... )
                    </td>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo(date('Y-m-d')) . "<br>
                        <strong>Wali Kelas</strong><br><br><br><br>
                        <strong>( " . ($student['walas_name'] ?: 'Wali Kelas') . " )</strong>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        ExportHelper::pdf($html, 'Rapor_Gabungan_' . $student['nis'] . '_Bulan_' . $monthNum, 'A4', 'portrait', true);
    }

    // Helper: calculate ledger data
    private function getLedgerData(int $classId, int $monthNum, int $taId): array {
        $class = Database::fetch("SELECT name, unit_id FROM classes WHERE id = ?", [$classId]);
        $unitId = $class['unit_id'] ?? 3;

        $subjects = Database::fetchAll("SELECT id, code, name FROM subjects WHERE unit_id = ? ORDER BY type ASC, name ASC", [$unitId]);
        
        $studentsRaw = Database::fetchAll(
            "SELECT s.id, s.nama, s.nis 
             FROM class_students cs 
             JOIN students s ON cs.student_id = s.id 
             WHERE cs.class_id = ? AND cs.status = 'Aktif' 
             ORDER BY s.nama ASC",
            [$classId]
        );

        $gradesRaw = Database::fetchAll(
            "SELECT student_id, subject_id, score 
             FROM student_grades 
             WHERE class_id = ? AND academic_year_id = ? AND month_num = ?",
            [$classId, $taId, $monthNum]
        );

        $gradeMap = [];
        foreach ($gradesRaw as $gr) {
            $gradeMap[$gr['student_id']][$gr['subject_id']] = (float)$gr['score'];
        }

        $students = [];
        foreach ($studentsRaw as $st) {
            $sId = $st['id'];
            $stGrades = $gradeMap[$sId] ?? [];
            $tot = array_sum($stGrades);
            $cnt = count($stGrades);
            $avg = $cnt > 0 ? round($tot / $cnt, 1) : 0;

            $students[] = [
                'id'     => $sId,
                'nama'   => $st['nama'],
                'nis'    => $st['nis'],
                'grades' => $stGrades,
                'total'  => $tot,
                'avg'    => $avg
            ];
        }

        return [
            'className' => $class['name'] ?? 'Kelas',
            'subjects'  => $subjects,
            'students'  => $students
        ];
    }
}

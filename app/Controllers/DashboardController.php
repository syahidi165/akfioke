<?php
// app/Controllers/DashboardController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;

class DashboardController extends Controller {
    public function index(): void {
        $this->requireAuth();
        $role = Auth::activeRoleName();
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $data = [
            'pageTitle' => 'Dashboard - ' . Auth::activeRole()['display_name'],
            'role'      => $role,
            'unitId'    => $unitId,
            'taId'      => $taId
        ];

        // Gather metrics according to active role
        if ($role === 'operator_yayasan') {
            $data['totalUnits'] = Database::fetch("SELECT COUNT(*) as c FROM units")['c'];
            $data['totalEmployees'] = Database::fetch("SELECT COUNT(*) as c FROM employees WHERE is_deleted = 0")['c'];
            $data['totalStudents'] = Database::fetch("SELECT COUNT(*) as c FROM students WHERE status = 'Aktif'")['c'];
            $data['totalTunggakan'] = Database::fetch("SELECT SUM(amount) as s FROM bills WHERE status = 'Tertunggak' AND academic_year_id = ?", [$taId])['s'] ?? 0;
            
            // Student distribution by unit for Chart.js
            $data['unitStudents'] = Database::fetchAll(
                "SELECT u.name, COUNT(s.id) as count FROM units u 
                 LEFT JOIN students s ON u.id = s.unit_id AND s.status = 'Aktif' 
                 GROUP BY u.id"
            );
            
            // Approvals status
            $data['taApprovals'] = Database::fetchAll(
                "SELECT a.*, u.name as unit_name FROM academic_year_approvals a 
                 JOIN units u ON a.unit_id = u.id 
                 WHERE a.academic_year_id = ?",
                [$taId]
            );
        } elseif ($role === 'direktur_pendidikan') {
            $data['totalUnits'] = Database::fetch("SELECT COUNT(*) as c FROM units")['c'] ?? 5;
            $data['totalEmployees'] = Database::fetch("SELECT COUNT(*) as c FROM employees WHERE is_deleted = 0")['c'];
            $data['totalStudents'] = Database::fetch("SELECT COUNT(*) as c FROM students WHERE status = 'Aktif'")['c'];
            $data['avgAkfi'] = Database::fetch("SELECT AVG(total_score) as a FROM akfi_assessments WHERE academic_year_id = ? AND status = 'Final'", [$taId])['a'] ?? 88.5;
            $data['spmbTotal'] = Database::fetch("SELECT SUM(actual_count) as c FROM spmb_targets WHERE academic_year_id = ?", [$taId])['c'] ?? 0;
            $data['pendingHrApproval'] = Database::fetch("SELECT COUNT(*) as c FROM akfi_assessments WHERE academic_year_id = ? AND status = 'Disetujui_Atasan'", [$taId])['c'] ?? 0;
            $data['finalAkfiCount'] = Database::fetch("SELECT COUNT(*) as c FROM akfi_assessments WHERE academic_year_id = ? AND status = 'Final'", [$taId])['c'] ?? 0;
            $data['unitAkfiStats'] = Database::fetchAll(
                "SELECT u.name, AVG(a.total_score) as avg_score, COUNT(a.id) as total_emp 
                 FROM units u 
                 LEFT JOIN akfi_assessments a ON u.id = a.unit_id AND a.academic_year_id = ? 
                 GROUP BY u.id",
                [$taId]
            );
        } elseif ($role === 'deputi_pendidikan') {
            $data['pendingAdminKs'] = Database::fetch("SELECT COUNT(*) as c FROM administrasi_uploads WHERE status = 'Menunggu Review'")['c'];
            $data['totalJuaraOsn'] = Database::fetch("SELECT COUNT(*) as c FROM prestasi_siswa WHERE category IN ('OSN','O2SN','FLS2N') AND academic_year_id = ?", [$taId])['c'];
            $data['spmbAchievement'] = Database::fetchAll(
                "SELECT u.name, COALESCE(t.target_count, 100) as target_count, COALESCE(t.actual_count, 0) as registered 
                 FROM units u 
                 LEFT JOIN spmb_targets t ON u.id = t.unit_id AND t.academic_year_id = ? 
                 GROUP BY u.id",
                [$taId]
            );
            $data['ksAssessments'] = Database::fetchAll(
                "SELECT a.*, e.nama, u.name as unit_name, t.position_name 
                 FROM akfi_assessments a 
                 JOIN employees e ON a.employee_id = e.id 
                 JOIN units u ON a.unit_id = u.id 
                 JOIN akfi_templates t ON a.template_id = t.id 
                 WHERE a.academic_year_id = ? AND t.id IN (8, 9, 10)",
                [$taId]
            );

            // --- 1. PERSENTASE KEHADIRAN GURU & SISWA SETIAP SEKOLAH ---
            $allUnits = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
            $teacherAttendanceByUnit = [];
            $studentAttendanceByUnit = [];

            $totalGuruYayasan = 0;
            $sumGuruHadir = 0;
            $sumGuruTotalDays = 0;

            $totalSiswaYayasan = 0;
            $sumSiswaHadir = 0;
            $sumSiswaTotalDays = 0;

            foreach ($allUnits as $u) {
                $uid = (int)$u['id'];

                // Kehadiran Guru Unit Ini
                $totGuru = (int)(Database::fetch("SELECT COUNT(*) as c FROM employees WHERE unit_id = ? AND is_deleted = 0 AND (level_posisi LIKE '%Guru%' OR position LIKE '%Guru%')", [$uid])['c'] ?? 0);
                $guruAtt = Database::fetch(
                    "SELECT SUM(ea.hadir) as sum_hadir, SUM(ea.sakit) as sum_sakit, SUM(ea.izin) as sum_izin, SUM(ea.alpha) as sum_alpha, SUM(ea.terlambat_menit) as sum_terlambat
                     FROM employee_attendance ea 
                     JOIN employees e ON ea.employee_id = e.id 
                     WHERE ea.unit_id = ? AND ea.academic_year_id = ? AND e.is_deleted = 0 AND (e.level_posisi LIKE '%Guru%' OR e.position LIKE '%Guru%')",
                    [$uid, $taId]
                );
                $gHadir = (int)($guruAtt['sum_hadir'] ?? 0);
                $gSakit = (int)($guruAtt['sum_sakit'] ?? 0);
                $gIzin = (int)($guruAtt['sum_izin'] ?? 0);
                $gAlpha = (int)($guruAtt['sum_alpha'] ?? 0);
                $gTotal = $gHadir + $gSakit + $gIzin + $gAlpha;
                $gPct = $gTotal > 0 ? round(($gHadir / $gTotal) * 100, 1) : 0;

                $totalGuruYayasan += $totGuru;
                $sumGuruHadir += $gHadir;
                $sumGuruTotalDays += $gTotal;

                $teacherAttendanceByUnit[] = [
                    'unit_id'    => $uid,
                    'unit_code'  => $u['code'],
                    'unit_name'  => $u['name'],
                    'total_guru' => $totGuru,
                    'hadir'      => $gHadir,
                    'sakit'      => $gSakit,
                    'izin'       => $gIzin,
                    'alpha'      => $gAlpha,
                    'terlambat'  => (int)($guruAtt['sum_terlambat'] ?? 0),
                    'pct'        => $gPct
                ];

                // Kehadiran Siswa Unit Ini
                $totSiswa = (int)(Database::fetch("SELECT COUNT(*) as c FROM students WHERE unit_id = ? AND status = 'Aktif'", [$uid])['c'] ?? 0);
                $stdAtt = Database::fetch(
                    "SELECT SUM(sa.hadir) as sum_hadir, SUM(sa.sakit) as sum_sakit, SUM(sa.izin) as sum_izin, SUM(sa.alpha) as sum_alpha, AVG(sa.attendance_pct) as avg_pct
                     FROM student_attendance sa 
                     JOIN students s ON sa.student_id = s.id 
                     WHERE s.unit_id = ? AND sa.academic_year_id = ? AND s.status = 'Aktif'",
                    [$uid, $taId]
                );
                $sHadir = (int)($stdAtt['sum_hadir'] ?? 0);
                $sSakit = (int)($stdAtt['sum_sakit'] ?? 0);
                $sIzin = (int)($stdAtt['sum_izin'] ?? 0);
                $sAlpha = (int)($stdAtt['sum_alpha'] ?? 0);
                $sTotal = $sHadir + $sSakit + $sIzin + $sAlpha;
                $sPct = $sTotal > 0 ? round(($sHadir / $sTotal) * 100, 1) : round((float)($stdAtt['avg_pct'] ?? 0), 1);

                $totalSiswaYayasan += $totSiswa;
                $sumSiswaHadir += $sHadir;
                $sumSiswaTotalDays += $sTotal;

                $studentAttendanceByUnit[] = [
                    'unit_id'     => $uid,
                    'unit_code'   => $u['code'],
                    'unit_name'   => $u['name'],
                    'total_siswa' => $totSiswa,
                    'hadir'       => $sHadir,
                    'sakit'       => $sSakit,
                    'izin'        => $sIzin,
                    'alpha'       => $sAlpha,
                    'pct'         => $sPct
                ];
            }

            $data['teacherAttendanceByUnit'] = $teacherAttendanceByUnit;
            $data['studentAttendanceByUnit'] = $studentAttendanceByUnit;
            $data['avgTeacherAttOverall'] = $sumGuruTotalDays > 0 ? round(($sumGuruHadir / $sumGuruTotalDays) * 100, 1) : 0;
            $data['avgStudentAttOverall'] = $sumSiswaTotalDays > 0 ? round(($sumSiswaHadir / $sumSiswaTotalDays) * 100, 1) : 0;
            $data['totalGuruYayasan'] = $totalGuruYayasan;
            $data['totalSiswaYayasan'] = $totalSiswaYayasan;

        } elseif ($role === 'kepala_sekolah') {
            $currentUnit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]) ?: [];
            $data['currentUnit'] = $currentUnit;

            // Countdown Akreditasi & Izin Operasional
            $now = new \DateTime();

            $akreditasiDaysLeft = null;
            $akreditasiStatus = 'none';
            if (!empty($currentUnit['akreditasi_expired_at'])) {
                $expA = new \DateTime($currentUnit['akreditasi_expired_at']);
                $diffA = $now->diff($expA);
                $akreditasiDaysLeft = (int)$diffA->format('%r%a');
                if ($akreditasiDaysLeft < 0) {
                    $akreditasiStatus = 'expired';
                } elseif ($akreditasiDaysLeft <= 90) {
                    $akreditasiStatus = 'danger';
                } elseif ($akreditasiDaysLeft <= 180) {
                    $akreditasiStatus = 'warning';
                } else {
                    $akreditasiStatus = 'valid';
                }
            }
            $data['akreditasiDaysLeft'] = $akreditasiDaysLeft;
            $data['akreditasiStatus'] = $akreditasiStatus;

            $izinDaysLeft = null;
            $izinStatus = 'none';
            if (!empty($currentUnit['izin_operasional_expired_at'])) {
                $expI = new \DateTime($currentUnit['izin_operasional_expired_at']);
                $diffI = $now->diff($expI);
                $izinDaysLeft = (int)$diffI->format('%r%a');
                if ($izinDaysLeft < 0) {
                    $izinStatus = 'expired';
                } elseif ($izinDaysLeft <= 90) {
                    $izinStatus = 'danger';
                } elseif ($izinDaysLeft <= 180) {
                    $izinStatus = 'warning';
                } else {
                    $izinStatus = 'valid';
                }
            }
            $data['izinDaysLeft'] = $izinDaysLeft;
            $data['izinStatus'] = $izinStatus;

            // Live Student Count: hanya menghitung siswa yang ada di sekolahnya saja
            $data['liveStudentCount'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM students WHERE unit_id = ? AND status = 'Aktif'", [$unitId])['c'] ?? 0);
            $data['totalStudents'] = $data['liveStudentCount'];
            $data['totalTeachers'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM employees WHERE unit_id = ? AND is_deleted = 0", [$unitId])['c'] ?? 0);
            $data['unpaidBills'] = Database::fetch("SELECT COUNT(*) as c, SUM(amount) as s FROM bills WHERE unit_id = ? AND status = 'Tertunggak' AND academic_year_id = ?", [$unitId, $taId]);
            $data['pendingAdminTeacher'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM administrasi_uploads WHERE unit_id = ? AND status = 'Menunggu Review'", [$unitId])['c'] ?? 0);

            // 1. Grafik Pergerakan Jumlah Siswa (Live Count & Rekap Bulanan - Siswa Masuk, Keluar, Saat Ini)
            $movements = Database::fetchAll(
                "SELECT * FROM student_movement_logs 
                 WHERE unit_id = ? AND academic_year_id = ? 
                 ORDER BY FIELD(month_num, 7,8,9,10,11,12,1,2,3,4,5,6), id ASC",
                [$unitId, $taId]
            );

            if (empty($movements)) {
                $monthsDef = [
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni'
                ];
                $runningCount = $data['liveStudentCount'];
                foreach ($monthsDef as $mNum => $mName) {
                    $movements[] = [
                        'month_num'     => $mNum,
                        'month_name'    => $mName,
                        'masuk'         => ($mNum === 7 || $mNum === 1) ? 2 : 0,
                        'keluar'        => ($mNum === 12 || $mNum === 6) ? 1 : 0,
                        'current_count' => $runningCount
                    ];
                }
            }
            $data['studentMovements'] = $movements;
            $data['totalMasuk'] = array_sum(array_column($movements, 'masuk'));
            $data['totalKeluar'] = array_sum(array_column($movements, 'keluar'));

            // 2. Kondisi Tenaga Pendidik: Gender & Tingkat Pendidikan
            $genderRows = Database::fetchAll(
                "SELECT gender, COUNT(*) as count FROM employees WHERE unit_id = ? AND is_deleted = 0 GROUP BY gender",
                [$unitId]
            );
            $genderCounts = ['L' => 0, 'P' => 0];
            foreach ($genderRows as $gr) {
                $genderCounts[$gr['gender']] = (int)$gr['count'];
            }
            $data['empGenderCounts'] = $genderCounts;

            $eduRows = Database::fetchAll(
                "SELECT COALESCE(NULLIF(tingkat_pendidikan, ''), 'S1') as tingkat, COUNT(*) as count 
                 FROM employees 
                 WHERE unit_id = ? AND is_deleted = 0 
                 GROUP BY tingkat 
                 ORDER BY FIELD(tingkat, 'S3', 'S2', 'S1', 'D3', 'D1/D2', 'SMA/SMK')",
                [$unitId]
            );
            $data['empEduCounts'] = $eduRows;

            // 3. Capaian Nilai Seluruh Mapel (Filter Mapel & Periodik: Bulan / 3 Bulan / Semester / Tahun)
            $subjects = Database::fetchAll("SELECT id, code, name FROM subjects WHERE unit_id = ? ORDER BY name ASC", [$unitId]);
            $data['subjects'] = $subjects;

            $gradesMonthly = Database::fetchAll(
                "SELECT sg.subject_id, s.name as subject_name, sg.month_num, 
                        ROUND(AVG(sg.score), 1) as avg_score, ROUND(AVG(sg.kkm), 1) as avg_kkm, COUNT(sg.id) as cnt
                 FROM student_grades sg 
                 JOIN subjects s ON sg.subject_id = s.id 
                 WHERE s.unit_id = ? AND sg.academic_year_id = ?
                 GROUP BY sg.subject_id, sg.month_num
                 ORDER BY FIELD(sg.month_num, 7,8,9,10,11,12,1,2,3,4,5,6)",
                [$unitId, $taId]
            );
            $data['gradesMonthly'] = $gradesMonthly;

            $allSubjectsMonthly = Database::fetchAll(
                "SELECT sg.month_num, ROUND(AVG(sg.score), 1) as avg_score, ROUND(AVG(sg.kkm), 1) as avg_kkm, COUNT(sg.id) as cnt
                 FROM student_grades sg 
                 JOIN subjects s ON sg.subject_id = s.id 
                 WHERE s.unit_id = ? AND sg.academic_year_id = ?
                 GROUP BY sg.month_num
                 ORDER BY FIELD(sg.month_num, 7,8,9,10,11,12,1,2,3,4,5,6)",
                [$unitId, $taId]
            );
            $data['allSubjectsMonthly'] = $allSubjectsMonthly;

            $gradesYearly = Database::fetchAll(
                "SELECT ay.id as ta_id, ay.name as ta_name, ay.semester, ay.is_active, ay.is_locked,
                        ROUND(AVG(sg.score), 1) as avg_score, ROUND(AVG(sg.kkm), 1) as avg_kkm, COUNT(sg.id) as cnt
                 FROM student_grades sg 
                 JOIN subjects s ON sg.subject_id = s.id 
                 JOIN academic_years ay ON sg.academic_year_id = ay.id
                 WHERE s.unit_id = ?
                 GROUP BY ay.id
                 ORDER BY ay.name ASC, ay.semester ASC",
                [$unitId]
            );
            $data['gradesYearly'] = $gradesYearly;

            // 4. Laporan Tunggakan dengan Grafik
            $billsStats = Database::fetch(
                "SELECT 
                    COUNT(id) as total_bills,
                    SUM(amount) as total_amount,
                    SUM(CASE WHEN status = 'Lunas' THEN amount ELSE 0 END) as total_paid,
                    SUM(CASE WHEN status != 'Lunas' THEN amount ELSE 0 END) as total_unpaid,
                    SUM(CASE WHEN status = 'Lunas' THEN 1 ELSE 0 END) as count_paid,
                    SUM(CASE WHEN status != 'Lunas' THEN 1 ELSE 0 END) as count_unpaid
                 FROM bills 
                 WHERE unit_id = ? AND academic_year_id = ?",
                [$unitId, $taId]
            );
            $data['billsStats'] = $billsStats;

            $billsMonthly = Database::fetchAll(
                "SELECT bill_month, 
                        SUM(amount) as total_amount,
                        SUM(CASE WHEN status = 'Lunas' THEN amount ELSE 0 END) as paid_amount,
                        SUM(CASE WHEN status != 'Lunas' THEN amount ELSE 0 END) as unpaid_amount,
                        COUNT(CASE WHEN status != 'Lunas' THEN 1 ELSE NULL END) as unpaid_count
                 FROM bills 
                 WHERE unit_id = ? AND academic_year_id = ?
                 GROUP BY bill_month
                 ORDER BY FIELD(bill_month, 7,8,9,10,11,12,1,2,3,4,5,6), bill_month ASC",
                [$unitId, $taId]
            );
            $data['billsMonthly'] = $billsMonthly;

            // 5. Laporan Supervisi dengan Grafik
            $supStats = Database::fetch(
                "SELECT 
                    COUNT(id) as total_sessions,
                    ROUND(AVG(final_score), 1) as avg_final_score,
                    ROUND(AVG(score_a), 1) as avg_score_a,
                    ROUND(AVG(score_b), 1) as avg_score_b,
                    ROUND(AVG(score_c), 1) as avg_score_c
                 FROM supervisi_sessions 
                 WHERE unit_id = ? AND academic_year_id = ?",
                [$unitId, $taId]
            );
            $data['supStats'] = $supStats;

            $supPredicates = Database::fetchAll(
                "SELECT achievement_predicate, COUNT(*) as count 
                 FROM supervisi_sessions 
                 WHERE unit_id = ? AND academic_year_id = ?
                 GROUP BY achievement_predicate
                 ORDER BY FIELD(achievement_predicate, 'SANGAT BAIK', 'BAIK', 'CUKUP', 'PERLU PENINGKATAN')",
                [$unitId, $taId]
            );
            $data['supPredicates'] = $supPredicates;

            $supList = Database::fetchAll(
                "SELECT ss.*, e.nama as teacher_name, sub.name as subject_name, c.name as class_name
                 FROM supervisi_sessions ss 
                 JOIN employees e ON ss.teacher_id = e.id 
                 JOIN subjects sub ON ss.subject_id = sub.id 
                 LEFT JOIN classes c ON ss.class_id = c.id
                 WHERE ss.unit_id = ? AND ss.academic_year_id = ?
                 ORDER BY ss.final_score DESC",
                [$unitId, $taId]
            );
            $data['supList'] = $supList;

            // Perbandingan Tahun Ajaran Aktif vs Arsip untuk Unit Ini
            $taComparison = Database::fetchAll(
                "SELECT ay.id as ta_id, ay.name as ta_name, ay.semester, ay.is_active, ay.is_locked,
                        (SELECT COUNT(*) FROM student_movement_logs WHERE unit_id = ? AND academic_year_id = ay.id) as movement_count,
                        (SELECT AVG(final_score) FROM supervisi_sessions WHERE unit_id = ? AND academic_year_id = ay.id) as avg_supervisi,
                        (SELECT SUM(amount) FROM bills WHERE unit_id = ? AND academic_year_id = ay.id AND status != 'Lunas') as total_tunggakan,
                        (SELECT AVG(attendance_pct) FROM student_attendance sa JOIN students s ON sa.student_id = s.id WHERE s.unit_id = ? AND sa.academic_year_id = ay.id) as avg_att
                 FROM academic_years ay
                 WHERE ay.id IN (1, 2, 10)
                 ORDER BY ay.name ASC, ay.semester ASC",
                [$unitId, $unitId, $unitId, $unitId]
            );
            $data['taComparison'] = $taComparison;
        } elseif ($role === 'operator_sekolah') {
            $data['totalStudents'] = Database::fetch("SELECT COUNT(*) as c FROM students WHERE unit_id = ? AND status = 'Aktif'", [$unitId])['c'] ?? 0;
            $data['totalTeachers'] = Database::fetch("SELECT COUNT(*) as c FROM employees WHERE unit_id = ? AND is_deleted = 0", [$unitId])['c'] ?? 0;
            $data['totalClasses'] = Database::fetch("SELECT COUNT(*) as c FROM classes WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId])['c'] ?? 0;
            $data['unpaidBills'] = Database::fetch("SELECT COUNT(*) as c, SUM(amount) as s FROM bills WHERE unit_id = ? AND status = 'Tertunggak' AND academic_year_id = ?", [$unitId, $taId]);
            $data['unitInfo'] = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
            $data['recentStudents'] = Database::fetchAll("SELECT s.*, c.name as class_name FROM students s LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' LEFT JOIN classes c ON cs.class_id = c.id WHERE s.unit_id = ? AND s.status = 'Aktif' ORDER BY s.id DESC LIMIT 5", [$unitId]);
        } elseif ($role === 'sarpras') {
            $data['totalAssets'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM assets WHERE unit_id = ? AND status = 'Aktif'", [$unitId])['c'] ?? 0);
            $data['totalDamaged'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM assets WHERE unit_id = ? AND condition_status IN ('Rusak Ringan', 'Rusak Berat') AND status = 'Aktif'", [$unitId])['c'] ?? 0);
            $data['totalPo'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM purchase_orders WHERE unit_id = ?", [$unitId])['c'] ?? 0);
            $data['pendingPo'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM purchase_orders WHERE unit_id = ? AND status NOT IN ('Selesai', 'Ditolak')", [$unitId])['c'] ?? 0);
            $data['totalScrapped'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM assets WHERE unit_id = ? AND status = 'Scrapped'", [$unitId])['c'] ?? 0);
            $data['recentAssets'] = Database::fetchAll("SELECT a.*, c.name as category_name FROM assets a LEFT JOIN asset_categories c ON a.category_id = c.id WHERE a.unit_id = ? ORDER BY a.id DESC LIMIT 5", [$unitId]);
            $data['recentPos'] = Database::fetchAll("SELECT * FROM purchase_orders WHERE unit_id = ? ORDER BY id DESC LIMIT 5", [$unitId]);
        } elseif ($role === 'direktur_pengadaan') {
            $data['pendingApprovalPo'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM purchase_orders WHERE status = 'Menunggu_Direktur_Pengadaan'")['c'] ?? 0);
            $data['approvedPo'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM purchase_orders WHERE status IN ('Disetujui', 'Dalam_Pengerjaan', 'Selesai')")['c'] ?? 0);
            $data['contractorPo'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM purchase_orders WHERE needs_contractor = 1")['c'] ?? 0);
            $data['totalAllAssets'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM assets WHERE status = 'Aktif'")['c'] ?? 0);
            $data['actionPos'] = Database::fetchAll("SELECT po.*, u.name as unit_name FROM purchase_orders po LEFT JOIN units u ON po.unit_id = u.id WHERE po.status = 'Menunggu_Direktur_Pengadaan' ORDER BY po.id ASC");
            $data['readyRealizePos'] = Database::fetchAll("SELECT po.*, u.name as unit_name FROM purchase_orders po LEFT JOIN units u ON po.unit_id = u.id WHERE po.status = 'Disetujui' ORDER BY po.id DESC LIMIT 5");
        } elseif ($role === 'direktur_sarpras') {
            $data['pendingContractorPo'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM purchase_orders WHERE status = 'Menunggu_Direktur_Sarpras'")['c'] ?? 0);
            $data['totalYayasanAssets'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM assets WHERE status = 'Aktif'")['c'] ?? 0);
            $data['totalBrokenYayasan'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM assets WHERE condition_status IN ('Rusak Ringan', 'Rusak Berat') AND status = 'Aktif'")['c'] ?? 0);
            $data['totalScrapReports'] = (int)(Database::fetch("SELECT COUNT(*) as c FROM scrap_reports")['c'] ?? 0);
            $data['actionContractorPos'] = Database::fetchAll("SELECT po.*, u.name as unit_name FROM purchase_orders po LEFT JOIN units u ON po.unit_id = u.id WHERE po.status = 'Menunggu_Direktur_Sarpras' ORDER BY po.id ASC");
            $data['assetsByUnit'] = Database::fetchAll("SELECT u.name, COUNT(a.id) as count FROM units u LEFT JOIN assets a ON u.id = a.unit_id AND a.status = 'Aktif' GROUP BY u.id");
        } elseif ($role === 'orang_tua') {
            // Ambil anak-anak dari orang tua berdasarkan parent_user_id atau No. KK
            $username = $user['username'] ?? '';
            $data['children'] = Database::fetchAll(
                "SELECT s.*, u.name as unit_name, c.name as class_name 
                 FROM students s 
                 JOIN units u ON s.unit_id = u.id 
                 LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                 LEFT JOIN classes c ON cs.class_id = c.id 
                 WHERE s.parent_user_id = ? OR (s.parent_user_id IS NULL AND s.no_kk = ? AND ? != '')
                 ORDER BY s.nama ASC",
                [$userId, $username, $username]
            );

            $studentIds = array_column($data['children'], 'id');
            $data['unpaidBills'] = [];
            if (!empty($studentIds)) {
                $inClause = implode(',', array_fill(0, count($studentIds), '?'));
                $params = array_merge($studentIds, ['Tertunggak']);
                $data['unpaidBills'] = Database::fetchAll(
                    "SELECT b.*, s.nama as student_name, u.name as unit_name 
                     FROM bills b 
                     JOIN students s ON b.student_id = s.id 
                     JOIN units u ON b.unit_id = u.id 
                     WHERE b.student_id IN ($inClause) AND b.status = ? 
                     ORDER BY b.due_date ASC",
                    $params
                );
            }
        } else {
            // Guru, Staf, Siswa umum
            $emp = Database::fetch("SELECT * FROM employees WHERE user_id = ?", [$userId]);
            $data['employee'] = $emp;
            if ($emp) {
                $data['personalAkfi'] = Database::fetch("SELECT * FROM akfi_assessments WHERE employee_id = ? AND academic_year_id = ?", [$emp['id'], $taId]);
                $data['myAttendance'] = Database::fetch("SELECT SUM(terlambat) as t, SUM(pulang_cepat) as pc, SUM(lupa_catat) as lc, SUM(mangkir) as m FROM employee_attendance WHERE employee_id = ? AND academic_year_id = ?", [$emp['id'], $taId]);
                $data['myPrestasi'] = Database::fetchAll(
                    "SELECT * FROM guru_berprestasi WHERE employee_id = ? AND status != 'Dibatalkan' ORDER BY year DESC, id DESC",
                    [$emp['id']]
                );
            }
        }

        $this->view('dashboard/index', $data);
    }

    public function downloadPanduan(): void {
        $this->requireAuth();
        $filePath = dirname(__DIR__, 2) . '/public/docs/Buku_Panduan_Pengoperasian_AKFIOKE.pdf';
        if (!file_exists($filePath)) {
            // Auto generate if missing
            exec('php ' . escapeshellarg(dirname(__DIR__, 2) . '/bin/generate_docs.php'));
        }

        if (file_exists($filePath)) {
            if (ob_get_length()) ob_end_clean();
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Buku_Panduan_Pengoperasian_AKFIOKE.pdf"');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        }

        $this->setFlash('danger', 'Berkas panduan belum tersedia.');
        $this->redirect(Auth::baseUrl() . '/dashboard');
    }
}

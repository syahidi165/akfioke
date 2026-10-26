<?php
// app/Controllers/TahfidzController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\ExportHelper;
use Core\SecurityHelper;

class TahfidzController extends Controller {

    // --- 1. SETORAN BULANAN SISWA (GURU TAHFIDZ) ---
    public function setoranSiswa(): void {
        $this->requireRole(['guru_tahfidz', 'koor_tahfidz']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        // Cari halaqah yang diampu guru saat ini
        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = $emp['id'] ?? 0;

        $halaqahList = Database::fetchAll("SELECT * FROM tahfidz_halaqah WHERE unit_id = ? AND academic_year_id = ? AND type = 'Siswa'", [$unitId, $taId]);
        $currentHalaqah = Database::fetch("SELECT * FROM tahfidz_halaqah WHERE tutor_employee_id = ? AND academic_year_id = ? AND type = 'Siswa' LIMIT 1", [$empId, $taId]) ?: ($halaqahList[0] ?? null);

        $students = [];
        $setoranLogs = [];
        if ($currentHalaqah) {
            $students = Database::fetchAll(
                "SELECT s.*, c.name as class_name FROM tahfidz_halaqah_members m 
                 JOIN students s ON m.student_id = s.id 
                 LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                 LEFT JOIN classes c ON cs.class_id = c.id 
                 WHERE m.halaqah_id = ? ORDER BY s.nama ASC",
                [$currentHalaqah['id']]
            );

            $setoranLogs = Database::fetchAll(
                "SELECT ss.*, s.nama as student_name, s.nis, s1.name_id as surah_start_name, s2.name_id as surah_end_name,
                        s1.verses_count as surah_start_verses, s2.verses_count as surah_end_verses 
                 FROM tahfidz_setoran_siswa ss 
                 JOIN students s ON ss.student_id = s.id 
                 JOIN mushaf_surahs s1 ON ss.surah_start = s1.surah_number 
                 JOIN mushaf_surahs s2 ON ss.surah_end = s2.surah_number 
                 WHERE ss.halaqah_id = ? AND ss.academic_year_id = ? 
                 ORDER BY ss.id DESC",
                [$currentHalaqah['id'], $taId]
            );
        }

        $surahs = Database::fetchAll("SELECT * FROM mushaf_surahs ORDER BY surah_number ASC");

        $this->view('tahfidz/setoran_siswa', [
            'pageTitle'      => 'Input Setoran Hafalan Santri (Kalkulator Madinah)',
            'currentHalaqah' => $currentHalaqah,
            'halaqahList'    => $halaqahList,
            'students'       => $students,
            'setoranLogs'    => $setoranLogs,
            'surahs'         => $surahs
        ]);
    }

    public function saveSetoranSiswa(): void {
        $this->requireRole(['guru_tahfidz', 'koor_tahfidz']);
        $halaqahId = (int)($_POST['halaqah_id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $monthNum = (int)($_POST['month_num'] ?? date('n'));
        $studentType = $_POST['student_type'] ?? 'MHML';
        $surahStart = (int)($_POST['surah_start'] ?? 114);
        $ayahStart = (int)($_POST['ayah_start'] ?? 1);
        $surahEnd = (int)($_POST['surah_end'] ?? 114);
        $ayahEnd = (int)($_POST['ayah_end'] ?? 6);
        $tutorNotes = trim($_POST['tutor_notes'] ?? '');
        $isExamPrep = !empty($_POST['is_exam_prep']) ? 1 : 0;
        $examJuz = !empty($_POST['exam_juz']) ? (int)$_POST['exam_juz'] : null;

        $taId = Auth::academicYearId();
        $userId = Auth::id();

        // Validasi dan batasi ayat maksimal sesuai surah
        $s1 = Database::fetch("SELECT start_page, end_page, verses_count FROM mushaf_surahs WHERE surah_number = ?", [$surahStart]);
        $s2 = Database::fetch("SELECT start_page, end_page, verses_count FROM mushaf_surahs WHERE surah_number = ?", [$surahEnd]);

        $maxVerses1 = (int)($s1['verses_count'] ?? 1);
        $maxVerses2 = (int)($s2['verses_count'] ?? 1);
        $ayahStart = max(1, min($ayahStart, $maxVerses1));
        $ayahEnd = max(1, min($ayahEnd, $maxVerses2));

        $pageStart = (int)($s1['start_page'] ?? 604);
        $pageEnd = (int)($s2['end_page'] ?? 604);
        if ($pageEnd < $pageStart) {
            $tmp = $pageStart; $pageStart = $pageEnd; $pageEnd = $tmp;
        }

        $pagesCount = max(1, ($pageEnd - $pageStart + 1));
        $linesCount = $pagesCount * 15;
        $isCompleted = $linesCount >= 150 ? 1 : 0; // Tuntas minimal 150 baris (setengah juz)

        Database::execute(
            "INSERT INTO tahfidz_setoran_siswa (halaqah_id, student_id, academic_year_id, month_num, student_type, surah_start, ayah_start, page_start, surah_end, ayah_end, page_end, lines_count, is_completed, is_exam_prep, exam_juz, tutor_notes, created_by_user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$halaqahId, $studentId, $taId, $monthNum, $studentType, $surahStart, $ayahStart, $pageStart, $surahEnd, $ayahEnd, $pageEnd, $linesCount, $isCompleted, $isExamPrep, $examJuz, $tutorNotes, $userId]
        );

        $setoranId = Database::lastInsertId();

        // Jika persiapan ujian, otomatis buat antrean ujian untuk Tutor Siswa!
        if ($isExamPrep && $examJuz) {
            $unitId = Auth::unitId();
            Database::execute(
                "INSERT INTO tahfidz_exams (student_id, unit_id, academic_year_id, proposed_by_tutor_id, juz_number, status, notes) 
                 VALUES (?, ?, ?, ?, ?, 'Menunggu', ?)",
                [$studentId, $unitId, $taId, $userId, $examJuz, $tutorNotes]
            );
        }

        AuditLog::log('SAVE_SETORAN_SISWA', 'tahfidz_setoran_siswa', $setoranId, null, ['lines' => $linesCount, 'is_completed' => $isCompleted]);
        $this->setFlash('success', "Setoran hafalan santri berhasil disimpan ({$linesCount} baris).");
        $this->redirect(Auth::baseUrl() . '/tahfidz/setoran-siswa');
    }

    public function updateSetoranSiswa(): void {
        $this->requireRole(['guru_tahfidz', 'koor_tahfidz']);
        $id = (int)($_POST['id'] ?? 0);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $monthNum = (int)($_POST['month_num'] ?? date('n'));
        $studentType = $_POST['student_type'] ?? 'MHML';
        $surahStart = (int)($_POST['surah_start'] ?? 114);
        $ayahStart = (int)($_POST['ayah_start'] ?? 1);
        $surahEnd = (int)($_POST['surah_end'] ?? 114);
        $ayahEnd = (int)($_POST['ayah_end'] ?? 6);
        $tutorNotes = trim($_POST['tutor_notes'] ?? '');

        $old = Database::fetch("SELECT * FROM tahfidz_setoran_siswa WHERE id = ?", [$id]);
        if (!$old) {
            $this->setFlash('error', 'Catatan setoran tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/tahfidz/setoran-siswa');
            return;
        }

        // Validasi dan batasi ayat maksimal sesuai surah
        $s1 = Database::fetch("SELECT start_page, end_page, verses_count FROM mushaf_surahs WHERE surah_number = ?", [$surahStart]);
        $s2 = Database::fetch("SELECT start_page, end_page, verses_count FROM mushaf_surahs WHERE surah_number = ?", [$surahEnd]);

        $maxVerses1 = (int)($s1['verses_count'] ?? 1);
        $maxVerses2 = (int)($s2['verses_count'] ?? 1);
        $ayahStart = max(1, min($ayahStart, $maxVerses1));
        $ayahEnd = max(1, min($ayahEnd, $maxVerses2));

        $pageStart = (int)($s1['start_page'] ?? 604);
        $pageEnd = (int)($s2['end_page'] ?? 604);
        if ($pageEnd < $pageStart) {
            $tmp = $pageStart; $pageStart = $pageEnd; $pageEnd = $tmp;
        }

        $pagesCount = max(1, ($pageEnd - $pageStart + 1));
        $linesCount = $pagesCount * 15;
        $isCompleted = $linesCount >= 150 ? 1 : 0;

        Database::execute(
            "UPDATE tahfidz_setoran_siswa SET 
                student_id = ?, month_num = ?, student_type = ?, 
                surah_start = ?, ayah_start = ?, page_start = ?, 
                surah_end = ?, ayah_end = ?, page_end = ?, 
                lines_count = ?, is_completed = ?, tutor_notes = ? 
             WHERE id = ?",
            [$studentId, $monthNum, $studentType, $surahStart, $ayahStart, $pageStart, $surahEnd, $ayahEnd, $pageEnd, $linesCount, $isCompleted, $tutorNotes, $id]
        );

        AuditLog::log('UPDATE_SETORAN_SISWA', 'tahfidz_setoran_siswa', $id, $old, ['lines' => $linesCount]);
        $this->setFlash('success', "Catatan riwayat setoran santri berhasil diperbarui ({$linesCount} baris).");
        $this->redirect(Auth::baseUrl() . '/tahfidz/setoran-siswa');
    }

    public function deleteSetoranSiswa(): void {
        $this->requireRole(['guru_tahfidz', 'koor_tahfidz']);
        $id = (int)($_POST['id'] ?? 0);

        $old = Database::fetch("SELECT * FROM tahfidz_setoran_siswa WHERE id = ?", [$id]);
        if (!$old) {
            $this->setFlash('error', 'Catatan setoran tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/tahfidz/setoran-siswa');
            return;
        }

        Database::execute("DELETE FROM tahfidz_setoran_siswa WHERE id = ?", [$id]);
        AuditLog::log('DELETE_SETORAN_SISWA', 'tahfidz_setoran_siswa', $id, $old, null);
        $this->setFlash('success', 'Catatan riwayat setoran santri berhasil dihapus.');
        $this->redirect(Auth::baseUrl() . '/tahfidz/setoran-siswa');
    }

    // --- 2. PENGAJUAN UJIAN TAHFIDZ OLEH GURU TAHFIDZ ---
    public function ajukanUjianForm(): void {
        $this->requireRole(['guru_tahfidz', 'tutor_siswa', 'koor_tahfidz', 'walas', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $activeRole = Auth::activeRoleName();

        // Cari data pegawai guru ini
        $emp = Database::fetch("SELECT id, nama FROM employees WHERE user_id = ?", [$userId]);
        $empId = (int)($emp['id'] ?? 0);

        // Ambil halaqah santri yang diampu guru ini pada tahun ajaran aktif (type = 'Siswa')
        $myHalaqahs = Database::fetchAll(
            "SELECT * FROM tahfidz_halaqah WHERE tutor_employee_id = ? AND academic_year_id = ? AND type = 'Siswa'",
            [$empId, $taId]
        );
        $myHalaqahIds = array_column($myHalaqahs, 'id');

        // Batasi hanya halaqah ustadz bersangkutan jika login sebagai guru_tahfidz atau peran guru
        $isGuruTahfidz = ($activeRole === 'guru_tahfidz' || !Auth::hasRole(['koor_tahfidz', 'kepala_sekolah', 'operator_sekolah', 'operator_yayasan']));

        if ($isGuruTahfidz) {
            // HANYA santri yang terdaftar di kelompok halaqah ustadz ini
            if (!empty($myHalaqahIds)) {
                $inClause = implode(',', array_fill(0, count($myHalaqahIds), '?'));
                $students = Database::fetchAll(
                    "SELECT DISTINCT s.id, s.nama, s.nis, c.name as class_name, h.name as halaqah_name 
                     FROM tahfidz_halaqah_members hm 
                     JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id 
                     JOIN students s ON hm.student_id = s.id 
                     LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                     LEFT JOIN classes c ON cs.class_id = c.id 
                     WHERE hm.halaqah_id IN ($inClause) AND s.status = 'Aktif' 
                     ORDER BY s.nama ASC",
                    $myHalaqahIds
                );

                // Riwayat pengajuan ujian: HANYA santri dari halaqah ustadz ini
                $exams = Database::fetchAll(
                    "SELECT te.*, s.nama as student_name, s.nis, u.name as unit_name, prop.full_name as proposer_name, exm.nama as examiner_name, h.name as halaqah_name 
                     FROM tahfidz_exams te 
                     JOIN students s ON te.student_id = s.id 
                     JOIN units u ON te.unit_id = u.id 
                     JOIN users prop ON te.proposed_by_tutor_id = prop.id 
                     LEFT JOIN employees exm ON te.examiner_id = exm.id 
                     JOIN tahfidz_halaqah_members hm ON s.id = hm.student_id 
                     JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id 
                     WHERE te.unit_id = ? AND te.academic_year_id = ? 
                       AND hm.halaqah_id IN ($inClause) 
                     GROUP BY te.id 
                     ORDER BY te.id DESC",
                    array_merge([$unitId, $taId], $myHalaqahIds)
                );
            } else {
                $students = [];
                $exams = [];
            }
        } else {
            // Koordinator Tahfidz / Kepala Sekolah dapat memantau seluruh santri unit
            $students = Database::fetchAll(
                "SELECT s.id, s.nama, s.nis, c.name as class_name, h.name as halaqah_name 
                 FROM students s 
                 LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                 LEFT JOIN classes c ON cs.class_id = c.id 
                 LEFT JOIN tahfidz_halaqah_members hm ON s.id = hm.student_id 
                 LEFT JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id AND h.academic_year_id = ? 
                 WHERE s.unit_id = ? AND s.status = 'Aktif' 
                 ORDER BY s.nama ASC",
                [$taId, $unitId]
            );

            $exams = Database::fetchAll(
                "SELECT te.*, s.nama as student_name, s.nis, u.name as unit_name, prop.full_name as proposer_name, exm.nama as examiner_name, h.name as halaqah_name 
                 FROM tahfidz_exams te 
                 JOIN students s ON te.student_id = s.id 
                 JOIN units u ON te.unit_id = u.id 
                 JOIN users prop ON te.proposed_by_tutor_id = prop.id 
                 LEFT JOIN employees exm ON te.examiner_id = exm.id 
                 LEFT JOIN tahfidz_halaqah_members hm ON s.id = hm.student_id 
                 LEFT JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id AND h.academic_year_id = te.academic_year_id 
                 WHERE te.unit_id = ? AND te.academic_year_id = ? 
                 GROUP BY te.id 
                 ORDER BY te.id DESC",
                [$unitId, $taId]
            );
        }

        $this->view('tahfidz/ajukan_ujian', [
            'pageTitle'      => 'Pengajuan Ujian Tahfidz ke Tutor Siswa',
            'students'       => $students,
            'exams'          => $exams,
            'myHalaqahs'     => $myHalaqahs,
            'isGuruTahfidz'  => $isGuruTahfidz
        ]);
    }

    public function submitUjian(): void {
        $this->requireRole(['guru_tahfidz', 'tutor_siswa', 'koor_tahfidz', 'walas']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $activeRole = Auth::activeRoleName();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $juzNumber = (int)($_POST['juz_number'] ?? 30);
        $notes = trim($_POST['notes'] ?? '');

        if ($studentId <= 0 || $juzNumber <= 0) {
            $this->setFlash('danger', 'Harap pilih santri dan juz yang akan diujikan.');
            $this->redirect(Auth::baseUrl() . '/tahfidz/ajukan-ujian');
            return;
        }

        // Validasi kepemilikan halaqah untuk guru tahfidz
        $isGuruTahfidz = ($activeRole === 'guru_tahfidz' || !Auth::hasRole(['koor_tahfidz', 'kepala_sekolah', 'operator_sekolah', 'operator_yayasan']));
        if ($isGuruTahfidz) {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
            $empId = (int)($emp['id'] ?? 0);

            $isMember = Database::fetch(
                "SELECT 1 FROM tahfidz_halaqah_members hm 
                 JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id 
                 WHERE hm.student_id = ? AND h.tutor_employee_id = ? AND h.academic_year_id = ? AND h.type = 'Siswa'",
                [$studentId, $empId, $taId]
            );

            if (!$isMember) {
                $this->setFlash('danger', 'Santri yang diajukan harus berasal dari kelompok halaqah binaan Anda.');
                $this->redirect(Auth::baseUrl() . '/tahfidz/ajukan-ujian');
                return;
            }
        }

        Database::execute(
            "INSERT INTO tahfidz_exams (student_id, unit_id, academic_year_id, proposed_by_tutor_id, juz_number, status, notes) 
             VALUES (?, ?, ?, ?, ?, 'Menunggu', ?)",
            [$studentId, $unitId, $taId, $userId, $juzNumber, $notes]
        );

        $examId = Database::lastInsertId();
        AuditLog::log('PROPOSE_TAHFIDZ_EXAM', 'tahfidz_exams', $examId, null, [
            'student_id' => $studentId, 'juz' => $juzNumber
        ]);

        $this->setFlash('success', 'Pengajuan ujian tahfidz santri berhasil dikirim ke antrean penguji Tutor Siswa.');
        $this->redirect(Auth::baseUrl() . '/tahfidz/ajukan-ujian');
    }

    // --- 3. ANTREAN UJIAN TAHFIDZ & PENILAIAN TUTOR SISWA ---
    public function antreanUjian(): void {
        $this->requireRole('tutor_siswa');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $exams = Database::fetchAll(
            "SELECT te.*, s.nama as student_name, s.nis, u.name as unit_name, prop.full_name as proposer_name 
             FROM tahfidz_exams te 
             JOIN students s ON te.student_id = s.id 
             JOIN units u ON te.unit_id = u.id 
             JOIN users prop ON te.proposed_by_tutor_id = prop.id 
             WHERE te.academic_year_id = ? 
             ORDER BY te.status ASC, te.created_at DESC",
            [$taId]
        );

        $this->view('tahfidz/ujian_antrean', [
            'pageTitle' => 'Antrean Pengujian Tahfidz Santri (Tutor Siswa)',
            'exams'     => $exams
        ]);
    }

    public function gradeUjian(): void {
        $this->requireRole('tutor_siswa');
        $id = (int)($_POST['id'] ?? 0);
        $score = (float)($_POST['score'] ?? 0);
        $status = $_POST['status'] ?? 'Lulus'; // Lulus / Tidak Lulus
        $notes = trim($_POST['notes'] ?? '');
        $examDate = !empty($_POST['exam_date']) ? $_POST['exam_date'] : date('Y-m-d');
        $examinerId = Auth::id();

        // Sertifikat kelulusan nomor otomatis bila lulus
        $certNo = null;
        if ($status === 'Lulus') {
            $certNo = 'SERT-TAH/' . date('Ym') . '/' . sprintf('%04d', $id);
        }

        Database::execute(
            "UPDATE tahfidz_exams SET examiner_id = ?, exam_date = ?, score = ?, status = ?, notes = ?, certificate_number = ? WHERE id = ?",
            [$examinerId, $examDate, $score, $status, $notes, $certNo, $id]
        );

        AuditLog::log('GRADE_TAHFIDZ_EXAM', 'tahfidz_exams', $id, null, ['score' => $score, 'status' => $status, 'cert' => $certNo]);
        $this->setFlash('success', "Penilaian ujian berhasil disimpan ({$status}).");
        $this->redirect(Auth::baseUrl() . '/tahfidz/ujian-antrean');
    }

    public function printSertifikat(string $id): void {
        $this->requireRole(['tutor_siswa', 'koor_tahfidz', 'orang_tua', 'walas']);
        $exam = Database::fetch(
            "SELECT te.*, s.nama as student_name, s.nis, s.gender, u.name as unit_name, u.kop_title, u.kop_subtitle, u.address, u.phone, u.email, ex.full_name as examiner_name 
             FROM tahfidz_exams te 
             JOIN students s ON te.student_id = s.id 
             JOIN units u ON te.unit_id = u.id 
             LEFT JOIN users ex ON te.examiner_id = ex.id 
             WHERE te.id = ?",
            [(int)$id]
        );

        if (!$exam || $exam['status'] !== 'Lulus') {
            die('Sertifikat hanya diterbitkan untuk santri yang telah dinyatakan Lulus Ujian.');
        }

        $kop = ExportHelper::renderKop($exam);
        $binBinti = $exam['gender'] === 'L' ? 'Bin' : 'Binti';

        $html = "
        <html>
        <head>
            <style>
                body { font-family: 'Times New Roman', serif; text-align: center; color: #1e293b; padding: 20px; }
                .cert-border { border: 8px double #1e3a8a; padding: 30px; border-radius: 16px; background: #fffdf5; }
                h1 { font-size: 26px; color: #1e3a8a; letter-spacing: 2px; margin-top: 10px; text-transform: uppercase; }
                .cert-no { font-size: 13px; color: #64748b; margin-top: -6px; margin-bottom: 20px; font-family: Helvetica, Arial; }
                .cert-name { font-size: 24px; font-weight: bold; color: #0d9488; text-decoration: underline; margin: 16px 0; }
                .cert-body { font-size: 16px; line-height: 1.8; margin: 20px 40px; }
                .signatures { width: 100%; margin-top: 40px; border-collapse: collapse; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 14px; }
            </style>
        </head>
        <body>
            <div class='cert-border'>
                {$kop}
                <h1>SYAHADAH TAHFIDZUL QUR'AN</h1>
                <div class='cert-no'>Nomor: {$exam['certificate_number']}</div>
                
                <div class='cert-body'>
                    Dewan Penguji Tahfidzul Qur'an Yayasan Islam Al Kahfi Batam menerangkan bahwa:
                </div>
                
                <div class='cert-name'>{$exam['student_name']}</div>
                <div style='font-size: 14px; font-weight: bold;'>NIS: {$exam['nis']} | {$exam['unit_name']}</div>
                
                <div class='cert-body'>
                    Telah mengikuti Ujian Tasmi' & Hifdzil Qur'an dan dinyatakan <strong>LULUS (MUMTAZ)</strong> pada hafalan:
                    <br><strong style='font-size: 20px; color: #1e3a8a;'>JUZ {$exam['juz_number']}</strong>
                    <br>dengan perolehan nilai: <strong>{$exam['score']} / 100</strong>.
                </div>
                
                <table class='signatures'>
                    <tr>
                        <td class='sig-box'>
                            Mengetahui,<br><strong>Koordinator Tahfidz</strong><br><br><br><br>
                            ( Ust. Bilal Syahid, S.Q )
                        </td>
                        <td class='sig-box'>
                            Batam, " . SecurityHelper::formatTanggalIndo($exam['exam_date']) . "<br><strong>Tutor / Penguji Siswa</strong><br><br><br><br>
                            ( " . ($exam['examiner_name'] ?: 'Ust. Hanif Al Hafidz') . " )
                        </td>
                    </tr>
                </table>
            </div>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Syahadah_Tahfidz_' . $exam['nis'] . '_Juz_' . $exam['juz_number'], 'A4', 'landscape', true);
    }

    // --- 3. INPUT NILAI TAHFIDZ GURU (TUTOR SEBAYA / TUTOR SISWA) ---
    public function guruNilai(): void {
        $this->requireRole(['tutor_sebaya', 'tutor_siswa']);
        $taId = Auth::academicYearId();
        $unitId = Auth::unitId();

        $teachers = Database::fetchAll(
            "SELECT * FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 
               AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Waka%' OR level_posisi LIKE '%Waka%' OR position LIKE '%Tutor%' OR level_posisi LIKE '%Tutor%')
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY nama ASC",
            [$unitId]
        );
        $surahs = Database::fetchAll("SELECT * FROM mushaf_surahs ORDER BY surah_number ASC");

        $logs = Database::fetchAll(
            "SELECT tg.*, e.nama as teacher_name, e.nipy, u.name as unit_name, s1.name_id as surah_start_name, s2.name_id as surah_end_name 
             FROM tahfidz_setoran_guru tg 
             JOIN employees e ON tg.employee_id = e.id 
             JOIN units u ON e.unit_id = u.id 
             LEFT JOIN mushaf_surahs s1 ON tg.surah_start = s1.surah_number 
             LEFT JOIN mushaf_surahs s2 ON tg.surah_end = s2.surah_number 
             WHERE tg.academic_year_id = ? AND e.unit_id = ? 
             ORDER BY tg.id DESC",
            [$taId, $unitId]
        );

        $this->view('tahfidz/guru_nilai', [
            'pageTitle' => 'Input Nilai Tahfidz Guru Bulanan (Kalkulator Madinah)',
            'teachers'  => $teachers,
            'surahs'    => $surahs,
            'logs'      => $logs
        ]);
    }

    public function saveGuruNilai(): void {
        $this->requireRole(['tutor_sebaya', 'tutor_siswa']);
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $monthNum = (int)($_POST['month_num'] ?? date('n'));
        $teacherType = $_POST['teacher_type'] ?? 'MHML';
        $surahStart = (int)($_POST['surah_start'] ?? 114);
        $ayahStart = (int)($_POST['ayah_start'] ?? 1);
        $surahEnd = (int)($_POST['surah_end'] ?? 114);
        $ayahEnd = (int)($_POST['ayah_end'] ?? 6);
        $tutorNotes = trim($_POST['tutor_notes'] ?? '');

        $taId = Auth::academicYearId();
        $userId = Auth::id();

        // Validasi dan batasi ayat maksimal sesuai surah
        $s1 = Database::fetch("SELECT start_page, end_page, verses_count FROM mushaf_surahs WHERE surah_number = ?", [$surahStart]);
        $s2 = Database::fetch("SELECT start_page, end_page, verses_count FROM mushaf_surahs WHERE surah_number = ?", [$surahEnd]);

        $maxVerses1 = (int)($s1['verses_count'] ?? 1);
        $maxVerses2 = (int)($s2['verses_count'] ?? 1);
        $ayahStart = max(1, min($ayahStart, $maxVerses1));
        $ayahEnd = max(1, min($ayahEnd, $maxVerses2));

        $pageStart = (int)($s1['start_page'] ?? 604);
        $pageEnd = (int)($s2['end_page'] ?? 604);
        if ($pageEnd < $pageStart) {
            $tmp = $pageStart; $pageStart = $pageEnd; $pageEnd = $tmp;
        }

        $pagesCount = max(1, ($pageEnd - $pageStart + 1));
        $linesCount = $pagesCount * 15;

        // Ambil target guru (TK: 150 baris, SD/SMP/SMA: 300 baris)
        $emp = Database::fetch("SELECT unit_id FROM employees WHERE id = ?", [$employeeId]);
        $unit = Database::fetch("SELECT level FROM units WHERE id = ?", [$emp['unit_id'] ?? 3]);
        $targetLines = ($unit['level'] ?? '') === 'TK' ? 150 : 300;
        $isCompleted = $linesCount >= $targetLines ? 1 : 0;

        // Ambil halaqah guru jika ada
        $hq = Database::fetch("SELECT id FROM tahfidz_halaqah WHERE type = 'Guru' LIMIT 1");
        $halaqahId = $hq ? (int)$hq['id'] : 2;

        Database::execute(
            "INSERT INTO tahfidz_setoran_guru (halaqah_id, employee_id, academic_year_id, month_num, teacher_type, surah_start, ayah_start, page_start, surah_end, ayah_end, page_end, lines_count, is_completed, tutor_notes, created_by_user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$halaqahId, $employeeId, $taId, $monthNum, $teacherType, $surahStart, $ayahStart, $pageStart, $surahEnd, $ayahEnd, $pageEnd, $linesCount, $isCompleted, $tutorNotes, $userId]
        );

        AuditLog::log('SAVE_GURU_TAHFIDZ', 'tahfidz_setoran_guru', Database::lastInsertId(), null, ['lines' => $linesCount, 'is_completed' => $isCompleted]);
        $this->setFlash('success', "Nilai tahfidz guru berhasil disimpan ({$linesCount} baris / target: {$targetLines} baris).");
        $this->redirect(Auth::baseUrl() . '/tahfidz/guru-nilai');
    }

    // --- 4. HALAQAH SISWA & BASELINE 3X SETAHUN ---
    public function halaqahSiswa(): void {
        $this->requireRole('koor_tahfidz');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $halaqahs = Database::fetchAll(
            "SELECT h.*, e.nama as tutor_name, COUNT(m.student_id) as member_count 
             FROM tahfidz_halaqah h 
             JOIN employees e ON h.tutor_employee_id = e.id 
             LEFT JOIN tahfidz_halaqah_members m ON h.id = m.halaqah_id 
             WHERE h.unit_id = ? AND h.academic_year_id = ? AND h.type = 'Siswa' 
             GROUP BY h.id 
             ORDER BY h.name ASC",
            [$unitId, $taId]
        );

        // Ambil seluruh santri anggota kelompok halaqah siswa pada TA ini
        $membersRaw = Database::fetchAll(
            "SELECT hm.halaqah_id, s.id as student_id, s.nama, s.nis, c.name as class_name 
             FROM tahfidz_halaqah_members hm 
             JOIN students s ON hm.student_id = s.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE hm.halaqah_id IN (
                 SELECT id FROM tahfidz_halaqah WHERE unit_id = ? AND academic_year_id = ? AND type = 'Siswa'
             )
             ORDER BY s.nama ASC",
            [$unitId, $taId]
        );

        $halaqahMembers = [];
        foreach ($membersRaw as $m) {
            $halaqahMembers[$m['halaqah_id']][] = [
                'id'         => (int)$m['student_id'],
                'nama'       => $m['nama'],
                'nis'        => $m['nis'],
                'class_name' => $m['class_name'] ?? 'Non-Kelas'
            ];
        }

        $tutors = Database::fetchAll("SELECT id, nama FROM employees WHERE unit_id = ? AND is_deleted = 0 ORDER BY nama ASC", [$unitId]);
        $unassignedStudents = Database::fetchAll(
            "SELECT s.*, c.name as class_name FROM students s 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             AND s.id NOT IN (
                 SELECT hm.student_id FROM tahfidz_halaqah_members hm 
                 JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id 
                 WHERE h.unit_id = ? AND h.academic_year_id = ? AND h.type = 'Siswa' AND hm.student_id IS NOT NULL
             )
             ORDER BY s.nama ASC",
            [$unitId, $unitId, $taId]
        );

        $this->view('tahfidz/halaqah_siswa', [
            'pageTitle'          => 'Kelola Halaqah Santri & Pembagian Guru Tahfidz',
            'halaqahs'           => $halaqahs,
            'halaqahMembers'     => $halaqahMembers,
            'tutors'             => $tutors,
            'unassignedStudents' => $unassignedStudents
        ]);
    }

    public function saveHalaqahSiswa(): void {
        $this->requireRole('koor_tahfidz');
        $name = trim($_POST['name'] ?? '');
        $tutorId = (int)($_POST['tutor_employee_id'] ?? 0);
        $studentIds = $_POST['student_ids'] ?? [];
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        if (!empty($name) && $tutorId > 0) {
            Database::execute(
                "INSERT INTO tahfidz_halaqah (unit_id, academic_year_id, tutor_employee_id, type, name) VALUES (?, ?, ?, 'Siswa', ?)",
                [$unitId, $taId, $tutorId, $name]
            );
            $hqId = (int)Database::lastInsertId();

            if (is_array($studentIds)) {
                foreach ($studentIds as $sId) {
                    Database::execute("INSERT INTO tahfidz_halaqah_members (halaqah_id, student_id) VALUES (?, ?)", [$hqId, (int)$sId]);
                }
            }
            $this->setFlash('success', 'Halaqah baru dan penempatan santri berhasil disimpan.');
        } else {
            $this->setFlash('danger', 'Nama halaqah dan guru pengampu wajib diisi.');
        }
        $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-siswa');
    }

    public function updateHalaqahSiswa(): void {
        $this->requireRole('koor_tahfidz');
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $tutorId = (int)($_POST['tutor_employee_id'] ?? 0);
        $removeStudentIds = $_POST['remove_student_ids'] ?? [];
        $addStudentIds = $_POST['add_student_ids'] ?? [];
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $hq = Database::fetch(
            "SELECT * FROM tahfidz_halaqah WHERE id = ? AND unit_id = ? AND academic_year_id = ? AND type = 'Siswa'",
            [$id, $unitId, $taId]
        );

        if (!$hq) {
            $this->setFlash('danger', 'Halaqah tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-siswa');
            return;
        }

        if (empty($name) || $tutorId <= 0) {
            $this->setFlash('danger', 'Nama halaqah dan guru pengampu wajib diisi.');
            $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-siswa');
            return;
        }

        // 1. Update nama halaqah dan guru pengampu
        Database::execute(
            "UPDATE tahfidz_halaqah SET name = ?, tutor_employee_id = ? WHERE id = ?",
            [$name, $tutorId, $id]
        );

        // 2. Kurangi siswa jika ada yang dipilih untuk dikeluarkan
        $removedCount = 0;
        if (!empty($removeStudentIds) && is_array($removeStudentIds)) {
            foreach ($removeStudentIds as $sId) {
                Database::execute(
                    "DELETE FROM tahfidz_halaqah_members WHERE halaqah_id = ? AND student_id = ?",
                    [$id, (int)$sId]
                );
                $removedCount++;
            }
        }

        // 3. Tambah siswa baru ke halaqah
        $addedCount = 0;
        if (!empty($addStudentIds) && is_array($addStudentIds)) {
            foreach ($addStudentIds as $sId) {
                $stId = (int)$sId;
                if ($stId > 0) {
                    $exists = Database::fetch(
                        "SELECT 1 FROM tahfidz_halaqah_members WHERE halaqah_id = ? AND student_id = ?",
                        [$id, $stId]
                    );
                    if (!$exists) {
                        Database::execute(
                            "INSERT INTO tahfidz_halaqah_members (halaqah_id, student_id) VALUES (?, ?)",
                            [$id, $stId]
                        );
                        $addedCount++;
                    }
                }
            }
        }

        AuditLog::log('UPDATE_HALAQAH_SISWA', 'tahfidz_halaqah', $id, $hq, [
            'name' => $name, 'tutor_id' => $tutorId, 'removed' => $removedCount, 'added' => $addedCount
        ]);

        $msg = "Halaqah '{$name}' berhasil diperbarui.";
        if ($addedCount > 0 || $removedCount > 0) {
            $msg .= " ({$addedCount} santri ditambahkan, {$removedCount} santri dikeluarkan)";
        }
        $this->setFlash('success', $msg);
        $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-siswa');
    }

    public function deleteHalaqahSiswa(): void {
        $this->requireRole('koor_tahfidz');
        $id = (int)($_POST['id'] ?? 0);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $hq = Database::fetch(
            "SELECT * FROM tahfidz_halaqah WHERE id = ? AND unit_id = ? AND academic_year_id = ? AND type = 'Siswa'",
            [$id, $unitId, $taId]
        );

        if (!$hq) {
            $this->setFlash('danger', 'Halaqah tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-siswa');
            return;
        }

        // Cek histori setoran
        $setoranCount = Database::fetch(
            "SELECT COUNT(*) as cnt FROM tahfidz_setoran_siswa WHERE halaqah_id = ?",
            [$id]
        );

        if (($setoranCount['cnt'] ?? 0) > 0) {
            $this->setFlash('danger', "Halaqah '{$hq['name']}' tidak dapat dihapus karena sudah memiliki histori catatan setoran santri.");
            $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-siswa');
            return;
        }

        // Hapus santri dari halaqah ini (mereka kembali menjadi unassigned)
        Database::execute("DELETE FROM tahfidz_halaqah_members WHERE halaqah_id = ?", [$id]);

        // Hapus halaqah
        Database::execute("DELETE FROM tahfidz_halaqah WHERE id = ?", [$id]);

        AuditLog::log('DELETE_HALAQAH_SISWA', 'tahfidz_halaqah', $id, $hq, null);
        $this->setFlash('success', "Halaqah '{$hq['name']}' berhasil dihapus.");
        $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-siswa');
    }

    // --- 4B. HALAQAH GURU & TARGET HAFALAN GURU (TUTOR SEBAYA) ---
    public function halaqahGuru(): void {
        $this->requireRole(['tutor_sebaya', 'tutor_siswa', 'koor_tahfidz', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $halaqahs = Database::fetchAll(
            "SELECT h.*, e.nama as tutor_name, COUNT(m.employee_id) as member_count 
             FROM tahfidz_halaqah h 
             LEFT JOIN employees e ON h.tutor_employee_id = e.id 
             LEFT JOIN tahfidz_halaqah_members m ON h.id = m.halaqah_id AND m.employee_id IS NOT NULL 
             WHERE h.unit_id = ? AND h.academic_year_id = ? AND h.type = 'Guru' 
             GROUP BY h.id",
            [$unitId, $taId]
        );

        $tutors = Database::fetchAll(
            "SELECT DISTINCT e.id, e.nama, e.level_posisi 
             FROM employees e 
             LEFT JOIN users u ON e.user_id = u.id 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE e.unit_id = ? AND e.is_deleted = 0 
               AND (r.name IN ('tutor_sebaya', 'tutor_siswa', 'koor_tahfidz') 
                    OR e.level_posisi LIKE '%Tutor%' OR e.position LIKE '%Tutor%')
               AND e.level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah')
               AND e.position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah')
             ORDER BY e.nama ASC", 
            [$unitId]
        );

        $teachers = Database::fetchAll(
            "SELECT id, nama, level_posisi FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 
               AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Waka%' OR level_posisi LIKE '%Waka%' OR position LIKE '%Tutor%' OR level_posisi LIKE '%Tutor%')
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY nama ASC", 
            [$unitId]
        );

        $this->view('tahfidz/halaqah_guru', [
            'pageTitle' => 'Kelola Halaqah Guru & Tutor Sebaya',
            'halaqahs'  => $halaqahs,
            'tutors'    => $tutors,
            'teachers'  => $teachers
        ]);
    }

    public function saveHalaqahGuru(): void {
        $this->requireRole(['tutor_sebaya', 'tutor_siswa', 'koor_tahfidz', 'kepala_sekolah']);
        $name = trim($_POST['name'] ?? '');
        $tutorId = (int)($_POST['tutor_employee_id'] ?? 0);
        $teacherIds = $_POST['teacher_ids'] ?? [];
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        if (!empty($name) && $tutorId > 0) {
            Database::execute(
                "INSERT INTO tahfidz_halaqah (unit_id, academic_year_id, tutor_employee_id, type, name) VALUES (?, ?, ?, 'Guru', ?)",
                [$unitId, $taId, $tutorId, $name]
            );
            $hqId = (int)Database::lastInsertId();

            if (is_array($teacherIds)) {
                foreach ($teacherIds as $tId) {
                    Database::execute("INSERT INTO tahfidz_halaqah_members (halaqah_id, employee_id) VALUES (?, ?)", [$hqId, (int)$tId]);
                }
            }
            $this->setFlash('success', 'Halaqah guru dan penempatan anggota berhasil disimpan.');
        } else {
            $this->setFlash('danger', 'Nama halaqah dan tutor wajib diisi.');
        }
        $this->redirect(Auth::baseUrl() . '/tahfidz/halaqah-guru');
    }

    public function guruTarget(): void {
        $this->requireRole(['tutor_sebaya', 'tutor_siswa', 'koor_tahfidz', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $teachers = Database::fetchAll(
            "SELECT e.*, tt.target_lines, tt.baseline_info, tt.target_info, tt.id as target_id 
             FROM employees e 
             LEFT JOIN tahfidz_targets tt ON e.id = tt.grade_or_employee_id AND tt.target_for = 'Guru' AND tt.academic_year_id = ? 
             WHERE e.unit_id = ? AND e.is_deleted = 0 
               AND (e.position LIKE '%Guru%' OR e.level_posisi LIKE '%Guru%' OR e.position LIKE '%Waka%' OR e.level_posisi LIKE '%Waka%' OR e.position LIKE '%Tutor%' OR e.level_posisi LIKE '%Tutor%')
               AND e.level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND e.position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY e.nama ASC",
            [$taId, $unitId]
        );

        $this->view('tahfidz/guru_target', [
            'pageTitle' => 'Target & Baseline Hafalan Guru (Tutor Sebaya)',
            'teachers'  => $teachers
        ]);
    }

    public function saveGuruTarget(): void {
        $this->requireRole(['tutor_sebaya', 'tutor_siswa', 'koor_tahfidz', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $targetLines = (int)($_POST['target_lines'] ?? 300);
        $baselineInfo = trim($_POST['baseline_info'] ?? '');
        $targetInfo = trim($_POST['target_info'] ?? '');

        if ($employeeId > 0) {
            $existing = Database::fetch(
                "SELECT id FROM tahfidz_targets WHERE academic_year_id = ? AND target_for = 'Guru' AND grade_or_employee_id = ?",
                [$taId, (string)$employeeId]
            );

            if ($existing) {
                Database::execute(
                    "UPDATE tahfidz_targets SET target_lines = ?, baseline_info = ?, target_info = ? WHERE id = ?",
                    [$targetLines, $baselineInfo, $targetInfo, $existing['id']]
                );
            } else {
                Database::execute(
                    "INSERT INTO tahfidz_targets (academic_year_id, target_for, unit_id, grade_or_employee_id, target_lines, baseline_info, target_info) 
                     VALUES (?, 'Guru', ?, ?, ?, ?, ?)",
                    [$taId, $unitId, (string)$employeeId, $targetLines, $baselineInfo, $targetInfo]
                );
            }
            $this->setFlash('success', 'Target hafalan guru berhasil disimpan.');
        }
        $this->redirect(Auth::baseUrl() . '/tahfidz/guru-target');
    }

    public function baseline(): void {
        $this->requireRole('koor_tahfidz');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $students = Database::fetchAll(
            "SELECT s.id, s.nama, s.nis, c.name as class_name, 
                    MAX(CASE WHEN month_num <= 4 THEN lines_count ELSE 0 END) as p1,
                    MAX(CASE WHEN month_num > 4 AND month_num <= 8 THEN lines_count ELSE 0 END) as p2,
                    MAX(CASE WHEN month_num > 8 THEN lines_count ELSE 0 END) as p3 
             FROM students s 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN tahfidz_setoran_siswa ss ON s.id = ss.student_id AND ss.academic_year_id = ? 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             GROUP BY s.id ORDER BY c.name ASC, s.nama ASC",
            [$taId, $unitId]
        );

        $this->view('tahfidz/baseline', [
            'pageTitle' => 'Baseline Tahfidz Siswa (3x Setahun - Dokumen Administrasi No. 11)',
            'students'  => $students
        ]);
    }

    public function rekapSiswa(): void {
        $this->requireRole(['koor_tahfidz', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $rekap = Database::fetchAll(
            "SELECT s.id, s.nama, s.nis, c.name as class_name, h.name as halaqah_name, 
                    SUM(ss.lines_count) as total_lines, 
                    COUNT(ss.id) as setoran_count, 
                    MAX(ss.is_completed) as is_tuntas 
             FROM students s 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             LEFT JOIN tahfidz_halaqah_members hm ON s.id = hm.student_id 
             LEFT JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id 
             LEFT JOIN tahfidz_setoran_siswa ss ON s.id = ss.student_id AND ss.academic_year_id = ? 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             GROUP BY s.id ORDER BY c.name ASC, s.nama ASC",
            [$taId, $unitId]
        );

        $this->view('tahfidz/rekap_siswa', [
            'pageTitle' => 'Monitoring Capaian & Rekapitulasi Tahfidz Santri',
            'rekap'     => $rekap
        ]);
    }

    public function printRaporTahfidz(string $id): void {
        $this->requireRole(['guru_tahfidz', 'koordinator_tahfidz', 'tutor_siswa', 'walas', 'kepala_sekolah', 'orang_tua']);
        $studentId = (int)$id;
        $taId = Auth::academicYearId();

        $student = Database::fetch(
            "SELECT s.*, c.name as class_name, u.name as unit_name, 
                    u.kop_title, u.kop_subtitle, u.address, u.phone, u.email,
                    ay.name as academic_year_name, h.name as halaqah_name, e.nama as tutor_name 
             FROM students s 
             JOIN units u ON s.unit_id = u.id 
             JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             JOIN classes c ON cs.class_id = c.id 
             JOIN academic_years ay ON cs.academic_year_id = ay.id 
             LEFT JOIN tahfidz_halaqah_members hm ON s.id = hm.student_id 
             LEFT JOIN tahfidz_halaqah h ON hm.halaqah_id = h.id 
             LEFT JOIN employees e ON h.tutor_employee_id = e.id 
             WHERE s.id = ?",
            [$studentId]
        );

        if (!$student) {
            die('Data santri tidak ditemukan.');
        }

        $setorans = Database::fetchAll(
            "SELECT ss.*, ms1.name_id as surah_start_name, ms2.name_id as surah_end_name 
             FROM tahfidz_setoran_siswa ss 
             LEFT JOIN mushaf_surahs ms1 ON ss.surah_start = ms1.surah_number 
             LEFT JOIN mushaf_surahs ms2 ON ss.surah_end = ms2.surah_number 
             WHERE ss.student_id = ? AND ss.academic_year_id = ? 
             ORDER BY ss.month_num ASC, ss.id ASC",
            [$studentId, $taId]
        );

        $exams = Database::fetchAll(
            "SELECT te.*, exm.nama as examiner_name 
             FROM tahfidz_exams te 
             LEFT JOIN employees exm ON te.examiner_id = exm.id 
             WHERE te.student_id = ? AND te.academic_year_id = ? 
             ORDER BY te.juz_number ASC",
            [$studentId, $taId]
        );

        $kop = ExportHelper::renderKop($student);
        $totalLines = array_sum(array_column($setorans, 'lines_count'));
        $totalHalaman = round($totalLines / 15, 1);

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; }
                .title-doc { text-align: center; font-size: 13px; font-weight: bold; margin: 10px 0; text-transform: uppercase; }
                .meta-table { width: 100%; margin-bottom: 12px; }
                .meta-table td { padding: 3px; vertical-align: top; }
                .table-data { width: 100%; border-collapse: collapse; margin-bottom: 14px; }
                .table-data th, .table-data td { border: 1px solid #94a3b8; padding: 6px; }
                .table-data th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .signatures { width: 100%; margin-top: 30px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; }
            </style>
        </head>
        <body>
            {$kop}
            <div class='title-doc'>LAPORAN CAPAIAN MUTABA'AH TAHFIDZ AL-QUR'AN SANTRI<br><span style='font-size: 10px; font-weight: normal;'>Tahun Pelajaran: {$student['academic_year_name']}</span></div>

            <table class='meta-table'>
                <tr>
                    <td width='18%'><strong>Nama Santri</strong></td><td width='2%'>:</td><td width='40%'>{$student['nama']}</td>
                    <td width='18%'><strong>Kelas</strong></td><td width='2%'>:</td><td width='20%'>{$student['class_name']}</td>
                </tr>
                <tr>
                    <td><strong>NIS</strong></td><td>:</td><td>{$student['nis']}</td>
                    <td><strong>Kelompok Halaqah</strong></td><td>:</td><td>" . ($student['halaqah_name'] ?: 'Umum') . "</td>
                </tr>
                <tr>
                    <td><strong>Satuan Unit</strong></td><td>:</td><td>{$student['unit_name']}</td>
                    <td><strong>Guru Pembimbing</strong></td><td>:</td><td>" . ($student['tutor_name'] ?: '-') . "</td>
                </tr>
            </table>

            <div style='font-weight: bold; margin-bottom: 6px;'>I. Rekapitulasi Riwayat Setoran Hafalan:</div>
            <table class='table-data'>
                <thead>
                    <tr>
                        <th width='5%'>No</th>
                        <th>Bulan</th>
                        <th>Jangkauan Hafalan</th>
                        <th>Halaman Mushaf</th>
                        <th>Jumlah Baris</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>";

        if (empty($setorans)) {
            $html .= "<tr><td colspan='6' align='center' style='padding: 12px; color: #64748b;'>Belum ada data setoran tahfidz yang dicatat.</td></tr>";
        } else {
            foreach ($setorans as $idx => $st) {
                $monthName = SecurityHelper::getNamaBulan((int)$st['month_num']);
                $isPass = $st['is_completed'] ? '<span style="color: green; font-weight: bold;">Tuntas Target</span>' : 'Berproses';
                $html .= "<tr>
                    <td align='center'>" . ($idx + 1) . "</td>
                    <td align='center'>{$monthName}</td>
                    <td>{$st['surah_start_name']}:{$st['ayah_start']} s/d {$st['surah_end_name']}:{$st['ayah_end']}</td>
                    <td align='center'>Hal. {$st['page_start']} - {$st['page_end']}</td>
                    <td align='center'><strong>{$st['lines_count']}</strong></td>
                    <td align='center'>{$isPass}</td>
                </tr>";
            }
        }

        $html .= "
                    <tr style='background: #f8fafc; font-weight: bold;'>
                        <td colspan='4' align='right'>TOTAL AKUMULASI CAPAIAN :</td>
                        <td align='center' style='color: #0f766e;'>{$totalLines} Baris</td>
                        <td align='center'>&plusmn; {$totalHalaman} Halaman</td>
                    </tr>
                </tbody>
            </table>

            <div style='font-weight: bold; margin-bottom: 6px;'>II. Hasil Ujian Tahfidz Bersertifikasi:</div>
            <table class='table-data'>
                <thead>
                    <tr>
                        <th width='5%'>No</th>
                        <th>Juz Diujikan</th>
                        <th>Tanggal Ujian</th>
                        <th>Penguji (Tutor Siswa)</th>
                        <th>Nilai Skor</th>
                        <th>Status Kelulusan</th>
                    </tr>
                </thead>
                <tbody>";

        if (empty($exams)) {
            $html .= "<tr><td colspan='6' align='center' style='padding: 12px; color: #64748b;'>Belum ada riwayat ujian tahfidz.</td></tr>";
        } else {
            foreach ($exams as $idx => $ex) {
                $tgl = $ex['exam_date'] ? SecurityHelper::formatTanggalIndo($ex['exam_date']) : '-';
                $score = $ex['score'] !== null ? $ex['score'] : '-';
                $statusColor = match($ex['status']) {
                    'Lulus' => 'color: green; font-weight: bold;',
                    'Tidak Lulus' => 'color: red; font-weight: bold;',
                    default => 'color: #d97706;'
                };
                $html .= "<tr>
                    <td align='center'>" . ($idx + 1) . "</td>
                    <td align='center'><strong>Juz {$ex['juz_number']}</strong></td>
                    <td align='center'>{$tgl}</td>
                    <td>" . ($ex['examiner_name'] ?: '-') . "</td>
                    <td align='center'><strong>{$score}</strong></td>
                    <td align='center' style='{$statusColor}'>{$ex['status']}</td>
                </tr>";
            }
        }

        $html .= "
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
                        <strong>Guru Pembimbing Tahfidz</strong><br><br><br><br>
                        <strong>( " . ($student['tutor_name'] ?: 'Guru Tahfidz') . " )</strong>
                    </td>
                </tr>
            </table>
        </body>
        </html>";

        ExportHelper::pdf($html, 'Rapor_Tahfidz_' . $student['nis'] . '_' . date('Ymd'), 'A4', 'portrait', true);
    }

    // --- 5. API MUSHAF LINES CALCULATION ---
    public function apiCalculateLines(): void {
        header('Content-Type: application/json');
        $s1 = (int)($_GET['surah_start'] ?? 114);
        $s2 = (int)($_GET['surah_end'] ?? 114);

        $row1 = Database::fetch("SELECT start_page, end_page FROM mushaf_surahs WHERE surah_number = ?", [$s1]);
        $row2 = Database::fetch("SELECT start_page, end_page FROM mushaf_surahs WHERE surah_number = ?", [$s2]);

        $pageStart = (int)($row1['start_page'] ?? 604);
        $pageEnd = (int)($row2['end_page'] ?? 604);
        if ($pageEnd < $pageStart) {
            $tmp = $pageStart; $pageStart = $pageEnd; $pageEnd = $tmp;
        }

        $pages = max(1, ($pageEnd - $pageStart + 1));
        $lines = $pages * 15;

        echo json_encode([
            'success'    => true,
            'page_start' => $pageStart,
            'page_end'   => $pageEnd,
            'pages'      => $pages,
            'lines'      => $lines,
            'is_juz'     => $lines >= 300,
            'is_half'    => $lines >= 150
        ]);
        exit;
    }

    // --- 6. TARGET TAHUNAN CAPAIAN TAHFIDZ SISWA PER JENJANG/SATUAN ---
    public function targetSiswa(): void {
        $this->requireRole(['koor_tahfidz', 'kepala_sekolah', 'deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan']);
        $taId = Auth::academicYearId();
        
        $userUnitId = Auth::unitId();
        $isExecutive = Auth::hasAnyRole(['deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan']);
        $selectedUnitId = $userUnitId;
        if (isset($_GET['unit_id'])) {
            $selectedUnitId = (int)$_GET['unit_id'];
        }

        // Definisi konfigurasi jenjang berdasarkan unit
        $unitConfigs = [
            1 => [
                'unit_id' => 1,
                'name' => 'KB Islam Al Kahfi Batam',
                'grades' => [
                    ['code' => 'KB', 'label' => 'Kelompok Bermain (KB)', 'default_lines' => 50, 'default_baseline' => 'Surah Pendek / Al-Fatihah', 'default_target' => '50 Baris (Surah Pendek)']
                ]
            ],
            2 => [
                'unit_id' => 2,
                'name' => 'TK & KB Islam Al Kahfi Batam',
                'grades' => [
                    ['code' => 'KB', 'label' => 'Kelompok Bermain (KB)', 'default_lines' => 50, 'default_baseline' => 'Surah Pendek / Al-Fatihah', 'default_target' => '50 Baris (Surah Pendek)'],
                    ['code' => 'TK A', 'label' => 'Taman Kanak-Kanak A (TK A)', 'default_lines' => 100, 'default_baseline' => 'An-Nas s.d Al-Fiil', 'default_target' => '100 Baris (An-Nas s.d Al-Fiil)'],
                    ['code' => 'TK B', 'label' => 'Taman Kanak-Kanak B (TK B)', 'default_lines' => 150, 'default_baseline' => 'An-Nas s.d At-Takatsur', 'default_target' => '150 Baris (An-Nas s.d At-Takatsur)']
                ]
            ],
            3 => [
                'unit_id' => 3,
                'name' => 'SD Islam Al Kahfi Batam',
                'grades' => [
                    ['code' => 'SD 1', 'label' => 'Kelas 1 SD', 'default_lines' => 300, 'default_baseline' => 'Surah An-Nas', 'default_target' => 'Juz 30 (300 Baris)'],
                    ['code' => 'SD 2', 'label' => 'Kelas 2 SD', 'default_lines' => 300, 'default_baseline' => 'Juz 30 Selesai', 'default_target' => 'Juz 29 (300 Baris)'],
                    ['code' => 'SD 3', 'label' => 'Kelas 3 SD', 'default_lines' => 300, 'default_baseline' => 'Juz 29 Selesai', 'default_target' => 'Juz 28 (300 Baris)'],
                    ['code' => 'SD 4', 'label' => 'Kelas 4 SD', 'default_lines' => 300, 'default_baseline' => 'Juz 28 Selesai', 'default_target' => 'Juz 1 (300 Baris)'],
                    ['code' => 'SD 5', 'label' => 'Kelas 5 SD', 'default_lines' => 300, 'default_baseline' => 'Juz 1 Selesai', 'default_target' => 'Juz 2 (300 Baris)'],
                    ['code' => 'SD 6', 'label' => 'Kelas 6 SD', 'default_lines' => 300, 'default_baseline' => 'Juz 2 Selesai', 'default_target' => 'Juz 3 (300 Baris)']
                ]
            ],
            4 => [
                'unit_id' => 4,
                'name' => 'SMP Islam Al Kahfi Batam',
                'grades' => [
                    ['code' => 'SMP 7', 'label' => 'Kelas 1 SMP (Kelas 7)', 'default_lines' => 450, 'default_baseline' => 'Juz 30 Selesai', 'default_target' => '1.5 Juz (450 Baris)'],
                    ['code' => 'SMP 8', 'label' => 'Kelas 2 SMP (Kelas 8)', 'default_lines' => 450, 'default_baseline' => 'Juz 1 - 2', 'default_target' => '1.5 Juz (450 Baris)'],
                    ['code' => 'SMP 9', 'label' => 'Kelas 3 SMP (Kelas 9)', 'default_lines' => 600, 'default_baseline' => 'Juz 3 - 4', 'default_target' => '2 Juz (600 Baris)']
                ]
            ],
            5 => [
                'unit_id' => 5,
                'name' => 'SMA Islam Al Kahfi Batam',
                'grades' => [
                    ['code' => 'SMA 10', 'label' => 'Kelas 1 SMA (Kelas 10)', 'default_lines' => 600, 'default_baseline' => 'Juz 1 - 3', 'default_target' => '2 Juz (600 Baris)'],
                    ['code' => 'SMA 11', 'label' => 'Kelas 2 SMA (Kelas 11)', 'default_lines' => 600, 'default_baseline' => 'Juz 4 - 5', 'default_target' => '2 Juz (600 Baris)'],
                    ['code' => 'SMA 12', 'label' => 'Kelas 3 SMA (Kelas 12)', 'default_lines' => 600, 'default_baseline' => 'Juz 6 - 7', 'default_target' => '2 Juz (600 Baris)']
                ]
            ]
        ];

        // Jika unit 1 (KB), satukan dengan unit 2 (TK)
        if ($selectedUnitId === 1) {
            $selectedUnitId = 2;
        }

        $activeConfig = $unitConfigs[$selectedUnitId] ?? $unitConfigs[3];

        $savedTargets = Database::fetchAll(
            "SELECT * FROM tahfidz_targets WHERE academic_year_id = ? AND target_for = 'Siswa' AND (unit_id = ? OR unit_id = ?)",
            [$taId, $selectedUnitId, ($selectedUnitId === 2 ? 1 : $selectedUnitId)]
        );

        $targetsByGrade = [];
        foreach ($savedTargets as $st) {
            $targetsByGrade[$st['grade_or_employee_id']] = $st;
        }

        $allUnits = Database::fetchAll("SELECT id, code, name FROM units ORDER BY id ASC");

        $this->view('tahfidz/target_siswa', [
            'pageTitle'      => 'Target Tahunan Capaian Tahfidz Santri',
            'activeConfig'   => $activeConfig,
            'selectedUnitId' => $selectedUnitId,
            'userUnitId'     => $userUnitId,
            'targetsByGrade' => $targetsByGrade,
            'allUnits'       => $allUnits,
            'isExecutive'    => $isExecutive
        ]);
    }

    public function saveTargetSiswa(): void {
        $this->requireRole(['koor_tahfidz', 'kepala_sekolah', 'deputi_pendidikan', 'direktur_pendidikan', 'operator_yayasan']);
        $taId = Auth::academicYearId();
        $unitId = (int)($_POST['unit_id'] ?? Auth::unitId());
        $grades = $_POST['grades'] ?? [];

        if (is_array($grades)) {
            foreach ($grades as $gradeCode => $val) {
                $targetLines = (int)($val['target_lines'] ?? 300);
                $baselineInfo = trim($val['baseline_info'] ?? '');
                $targetInfo = trim($val['target_info'] ?? '');

                $existing = Database::fetch(
                    "SELECT id FROM tahfidz_targets WHERE academic_year_id = ? AND target_for = 'Siswa' AND unit_id = ? AND grade_or_employee_id = ?",
                    [$taId, $unitId, (string)$gradeCode]
                );

                if ($existing) {
                    Database::execute(
                        "UPDATE tahfidz_targets SET target_lines = ?, baseline_info = ?, target_info = ? WHERE id = ?",
                        [$targetLines, $baselineInfo, $targetInfo, $existing['id']]
                    );
                } else {
                    Database::execute(
                        "INSERT INTO tahfidz_targets (academic_year_id, target_for, unit_id, grade_or_employee_id, target_lines, baseline_info, target_info) 
                         VALUES (?, 'Siswa', ?, ?, ?, ?, ?)",
                        [$taId, $unitId, (string)$gradeCode, $targetLines, $baselineInfo, $targetInfo]
                    );
                }
            }

            AuditLog::log('UPDATE_TAHFIDZ_TARGET_SISWA', 'tahfidz_targets', $unitId, null, [
                'unit_id' => $unitId, 'ta_id' => $taId, 'grades_count' => count($grades)
            ]);
            $this->setFlash('success', 'Target tahunan capaian tahfidz santri berhasil disimpan.');
        } else {
            $this->setFlash('danger', 'Tidak ada data target yang disimpan.');
        }

        $this->redirect(Auth::baseUrl() . '/tahfidz/target-siswa?unit_id=' . $unitId);
    }
}

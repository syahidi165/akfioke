<?php
// app/Controllers/SupervisiController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\ExportHelper;
use Core\SecurityHelper;
use Core\Notification;

class SupervisiController extends Controller {

    // --- 1. JADWAL SUPERVISI ---
    public function jadwal(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $sessions = Database::fetchAll(
            "SELECT ss.*, t.nama as teacher_name, t.nipy as teacher_nipy, 
                    s.nama as supervisor_name, sub.name as subject_name, c.name as class_name 
             FROM supervisi_sessions ss 
             JOIN employees t ON ss.teacher_id = t.id AND t.unit_id = ss.unit_id AND t.is_deleted = 0 AND t.status = 'Aktif'
             JOIN employees s ON ss.supervisor_id = s.id AND s.is_deleted = 0
             JOIN subjects sub ON ss.subject_id = sub.id 
             JOIN classes c ON ss.class_id = c.id 
             WHERE ss.unit_id = ? AND ss.academic_year_id = ? 
             ORDER BY ss.session_date DESC",
            [$unitId, $taId]
        );

        $teachers = Database::fetchAll(
            "SELECT * FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif'
               AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Waka%' OR level_posisi LIKE '%Waka%' OR position LIKE '%Tutor%' OR level_posisi LIKE '%Tutor%')
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY nama ASC", 
            [$unitId]
        );

        $supervisors = Database::fetchAll(
            "SELECT DISTINCT e.* FROM employees e 
             LEFT JOIN users u ON e.user_id = u.id 
             LEFT JOIN user_roles ur ON u.id = ur.user_id 
             LEFT JOIN roles r ON ur.role_id = r.id 
             WHERE e.unit_id = ? AND e.is_deleted = 0 AND e.status = 'Aktif'
               AND (r.name IN ('kepala_sekolah', 'waka_kurikulum', 'guru_supervisor') 
                    OR e.position LIKE '%Kepala%' OR e.level_posisi LIKE '%Kepala%'
                    OR e.position LIKE '%Supervisor%' OR e.level_posisi LIKE '%Supervisor%'
                    OR e.position LIKE '%Waka Kurikulum%' OR e.level_posisi LIKE '%Waka Kurikulum%')
               AND e.level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur')
               AND e.position NOT IN ('Direktur Pendidikan', 'Deputi Direktur')
             ORDER BY e.nama ASC", 
            [$unitId]
        );
        $subjects = Database::fetchAll("SELECT * FROM subjects WHERE unit_id = ? ORDER BY name ASC", [$unitId]);
        $classes = Database::fetchAll("SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, $taId]);

        $this->view('supervisi/jadwal', [
            'pageTitle'   => 'Jadwal Supervisi Mengajar Kelas',
            'sessions'    => $sessions,
            'teachers'    => $teachers,
            'supervisors' => $supervisors,
            'subjects'    => $subjects,
            'classes'     => $classes
        ]);
    }

    public function saveJadwal(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $supervisorId = (int)($_POST['supervisor_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $sessionDate = $_POST['session_date'] ?? date('Y-m-d');

        if ($teacherId && $supervisorId && $subjectId && $classId) {
            Database::execute(
                "INSERT INTO supervisi_sessions (unit_id, academic_year_id, supervisor_id, teacher_id, subject_id, class_id, session_date, achievement_predicate) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, 'PERLU PENINGKATAN')",
                [$unitId, $taId, $supervisorId, $teacherId, $subjectId, $classId, $sessionDate]
            );

            $id = Database::lastInsertId();
            AuditLog::log('SCHEDULE_SUPERVISI', 'supervisi_sessions', $id, null, ['teacher_id' => $teacherId, 'date' => $sessionDate]);

            // Notifikasi ke guru yang akan disupervisi
            $tEmp = Database::fetch("SELECT user_id FROM employees WHERE id = ?", [$teacherId]);
            if ($tEmp && $tEmp['user_id']) {
                Notification::send((int)$tEmp['user_id'], 'Jadwal Supervisi Kelas', "Anda dijadwalkan supervisi pembelajaran pada tanggal {$sessionDate}.", 'info', '/supervisi/riwayat');
            }

            $this->setFlash('success', 'Jadwal supervisi pembelajaran berhasil ditambahkan.');
        } else {
            $this->setFlash('danger', 'Harap lengkapi semua kolom jadwal supervisi.');
        }

        $this->redirect(Auth::baseUrl() . '/supervisi/jadwal');
    }

    public function deleteJadwal(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum']);
        $id = (int)($_POST['id'] ?? 0);
        $unitId = Auth::unitId();

        if ($id > 0) {
            $session = Database::fetch("SELECT * FROM supervisi_sessions WHERE id = ? AND unit_id = ?", [$id, $unitId]);
            if ($session) {
                Database::execute("DELETE FROM supervisi_rubric_scores WHERE supervisi_id = ?", [$id]);
                Database::execute("DELETE FROM supervisi_sessions WHERE id = ?", [$id]);
                AuditLog::log('DELETE_SUPERVISI_SESSION', 'supervisi_sessions', $id, null, ['deleted_session' => $session]);
                $this->setFlash('success', 'Jadwal supervisi pembelajaran berhasil dihapus.');
            } else {
                $this->setFlash('danger', 'Jadwal supervisi tidak ditemukan atau bukan milik unit Anda.');
            }
        }
        $this->redirect(Auth::baseUrl() . '/supervisi/jadwal');
    }

    // --- 2. DAFTAR PENILAIAN SUPERVISI ---
    public function penilaianList(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'guru_supervisor', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();
        $activeRole = Auth::activeRoleName();

        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = $emp['id'] ?? 0;

        // Jika Guru Supervisor, hanya boleh melihat jadwal di mana dia ditunjuk sebagai supervisor dan bukan dirinya sendiri
        if ($activeRole === 'guru_supervisor') {
            $sessions = Database::fetchAll(
                "SELECT ss.*, t.nama as teacher_name, t.nipy as teacher_nipy, 
                        s.nama as supervisor_name, sub.name as subject_name, c.name as class_name 
                 FROM supervisi_sessions ss 
                 JOIN employees t ON ss.teacher_id = t.id 
                 JOIN employees s ON ss.supervisor_id = s.id 
                 JOIN subjects sub ON ss.subject_id = sub.id 
                 JOIN classes c ON ss.class_id = c.id 
                 WHERE ss.unit_id = ? AND ss.academic_year_id = ? AND ss.supervisor_id = ? AND ss.teacher_id != ?
                 ORDER BY ss.session_date DESC",
                [$unitId, $taId, $empId, $empId]
            );
        } else {
            $sessions = Database::fetchAll(
                "SELECT ss.*, t.nama as teacher_name, t.nipy as teacher_nipy, 
                        s.nama as supervisor_name, sub.name as subject_name, c.name as class_name 
                 FROM supervisi_sessions ss 
                 JOIN employees t ON ss.teacher_id = t.id 
                 JOIN employees s ON ss.supervisor_id = s.id 
                 JOIN subjects sub ON ss.subject_id = sub.id 
                 JOIN classes c ON ss.class_id = c.id 
                 WHERE ss.unit_id = ? AND ss.academic_year_id = ? 
                 ORDER BY ss.session_date DESC",
                [$unitId, $taId]
            );
        }

        $this->view('supervisi/penilaian_list', [
            'pageTitle' => 'Penilaian Instrumen Supervisi Kelas',
            'sessions'  => $sessions
        ]);
    }

    public function form(string $id): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'guru_supervisor', 'deputi_pendidikan']);
        $sessionId = (int)$id;
        $userId = Auth::id();
        $activeRole = Auth::activeRoleName();

        $session = Database::fetch(
            "SELECT ss.*, t.nama as teacher_name, t.nipy as teacher_nipy, 
                    s.nama as supervisor_name, sub.name as subject_name, c.name as class_name, u.name as unit_name 
             FROM supervisi_sessions ss 
             JOIN employees t ON ss.teacher_id = t.id 
             JOIN employees s ON ss.supervisor_id = s.id 
             JOIN subjects sub ON ss.subject_id = sub.id 
             JOIN classes c ON ss.class_id = c.id 
             JOIN units u ON ss.unit_id = u.id 
             WHERE ss.id = ?",
            [$sessionId]
        );

        if (!$session) {
            $this->setFlash('danger', 'Sesi supervisi tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/supervisi/penilaian');
            return;
        }

        // Validasi pembatasan untuk guru_supervisor
        if ($activeRole === 'guru_supervisor') {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
            $empId = (int)($emp['id'] ?? 0);
            if ((int)$session['supervisor_id'] !== $empId || (int)$session['teacher_id'] === $empId) {
                $this->setFlash('danger', 'Akses ditolak: Anda hanya berwenang menilai guru yang dijadwalkan untuk Anda.');
                $this->redirect(Auth::baseUrl() . '/supervisi/penilaian');
                return;
            }
        }

        if (!$session) {
            $this->setFlash('danger', 'Sesi supervisi tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/supervisi/penilaian');
            return;
        }

        // Ambil skor tersimpan jika ada
        $scoresRaw = Database::fetchAll("SELECT * FROM supervisi_rubric_scores WHERE supervisi_id = ?", [$sessionId]);
        $savedScores = [];
        foreach ($scoresRaw as $sc) {
            $savedScores[$sc['section']][$sc['item_number']] = (int)$sc['score_val'];
        }

        $this->view('supervisi/form', [
            'pageTitle'   => 'Instrumen Penilaian Supervisi Kelas: ' . $session['teacher_name'],
            'session'     => $session,
            'savedScores' => $savedScores
        ]);
    }

    public function saveScore(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kurikulum', 'guru_supervisor', 'deputi_pendidikan']);
        $sessionId = (int)($_POST['session_id'] ?? 0);
        $scoresA = $_POST['sec_a'] ?? [];
        $scoresB = $_POST['sec_b'] ?? [];
        $scoresC = $_POST['sec_c'] ?? [];
        $notesGood = trim($_POST['notes_good'] ?? '');
        $notesImprove = trim($_POST['notes_improve'] ?? '');

        $session = Database::fetch("SELECT * FROM supervisi_sessions WHERE id = ?", [$sessionId]);
        if (!$session) {
            $this->setFlash('danger', 'Sesi tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/supervisi/penilaian');
            return;
        }

        $activeRole = Auth::activeRoleName();
        if ($activeRole === 'guru_supervisor') {
            $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [Auth::id()]);
            $empId = (int)($emp['id'] ?? 0);
            if ((int)$session['supervisor_id'] !== $empId || (int)$session['teacher_id'] === $empId) {
                $this->setFlash('danger', 'Akses ditolak: Anda hanya berwenang menilai guru yang dijadwalkan untuk Anda.');
                $this->redirect(Auth::baseUrl() . '/supervisi/penilaian');
                return;
            }
        }

        // --- VALIDASI ATURAN KHUSUS INDIKATOR 12 (KEGIATAN INTI) ---
        // Jika ada dari 11 indikator kegiatan inti bernilai kurang dari 2, maka indikator ke-12 tidak boleh bernilai Sangat Baik (3 point)
        $hasLessThan2InC = false;
        for ($i = 1; $i <= 11; $i++) {
            $val = isset($scoresC[$i]) ? (int)$scoresC[$i] : 0;
            if ($val < 2) {
                $hasLessThan2InC = true;
                break;
            }
        }

        $item12Val = isset($scoresC[12]) ? (int)$scoresC[12] : 0;
        if ($hasLessThan2InC && $item12Val >= 3) {
            // Pelanggaran aturan instrumen resmi
            $this->setFlash('danger', 'Validasi Gagal: Terdapat indikator 1-11 Kegiatan Inti bernilai kurang dari 2 (Cukup/Kurang), sehingga Indikator 12 tidak boleh bernilai Sangat Baik.');
            $this->redirect(Auth::baseUrl() . '/supervisi/form/' . $sessionId);
            return;
        }

        // Weights Definition according to official INSTRUMEN SUPERVISI KELAS.pdf
        $weightsA = [1 => 1, 2 => 1, 3 => 4, 4 => 1, 5 => 2, 6 => 1]; // Max sum = 10, scale 0-2 (Lengkap=2)
        $weightsB = [1 => 3, 2 => 7, 3 => 10]; // Max sum = 20, scale 0-3 (Sangat Baik=3)
        $weightsC = [
            1 => 6, 2 => 6, 3 => 6, 4 => 6, 5 => 6, 6 => 6,
            7 => 7, 8 => 6, 9 => 6, 10 => 7, 11 => 7, 12 => 1
        ]; // Max sum = 70, scale 0-3

        // Hitung Seksi A
        $totalA = 0;
        Database::execute("DELETE FROM supervisi_rubric_scores WHERE supervisi_id = ?", [$sessionId]);

        foreach ($weightsA as $itemNum => $w) {
            $scVal = isset($scoresA[$itemNum]) ? (int)$scoresA[$itemNum] : 2;
            $itemScore = round(($scVal / 2) * $w, 2);
            $totalA += $itemScore;
            Database::execute(
                "INSERT INTO supervisi_rubric_scores (supervisi_id, section, item_number, score_val, weight_val, final_item_score) 
                 VALUES (?, 'A', ?, ?, ?, ?)",
                [$sessionId, $itemNum, $scVal, $w, $itemScore]
            );
        }

        // Hitung Seksi B
        $totalB = 0;
        foreach ($weightsB as $itemNum => $w) {
            $scVal = isset($scoresB[$itemNum]) ? (int)$scoresB[$itemNum] : 3;
            $itemScore = round(($scVal / 3) * $w, 2);
            $totalB += $itemScore;
            Database::execute(
                "INSERT INTO supervisi_rubric_scores (supervisi_id, section, item_number, score_val, weight_val, final_item_score) 
                 VALUES (?, 'B', ?, ?, ?, ?)",
                [$sessionId, $itemNum, $scVal, $w, $itemScore]
            );
        }

        // Hitung Seksi C
        $totalC = 0;
        foreach ($weightsC as $itemNum => $w) {
            $scVal = isset($scoresC[$itemNum]) ? (int)$scoresC[$itemNum] : 3;
            $itemScore = round(($scVal / 3) * $w, 2);
            $totalC += $itemScore;
            Database::execute(
                "INSERT INTO supervisi_rubric_scores (supervisi_id, section, item_number, score_val, weight_val, final_item_score) 
                 VALUES (?, 'C', ?, ?, ?, ?)",
                [$sessionId, $itemNum, $scVal, $w, $itemScore]
            );
        }

        $totalScore = round($totalA + $totalB + $totalC, 2);
        
        // Predikat resmi instrumen:
        // 96 - 100 : SANGAT BAIK
        // 86 - 95  : BAIK
        // 71 - 85  : CUKUP
        // <= 70    : PERLU PENINGKATAN
        if ($totalScore >= 96.0) {
            $predicate = 'SANGAT BAIK';
        } elseif ($totalScore >= 86.0) {
            $predicate = 'BAIK';
        } elseif ($totalScore >= 71.0) {
            $predicate = 'CUKUP';
        } else {
            $predicate = 'PERLU PENINGKATAN';
        }

        Database::execute(
            "UPDATE supervisi_sessions SET 
                score_a = ?, score_b = ?, score_c = ?, total_score = ?, final_score = ?, 
                achievement_predicate = ?, notes_good = ?, notes_improve = ? 
             WHERE id = ?",
            [$totalA, $totalB, $totalC, $totalScore, $totalScore, $predicate, $notesGood, $notesImprove, $sessionId]
        );

        // Notifikasi ke guru
        $tEmp = Database::fetch("SELECT user_id FROM employees WHERE id = ?", [$session['teacher_id']]);
        if ($tEmp && $tEmp['user_id']) {
            Notification::send((int)$tEmp['user_id'], 'Hasil Supervisi Kelas Selesai', "Supervisi pembelajaran Anda telah dinilai dengan skor {$totalScore} ({$predicate}).", 'success', '/supervisi/riwayat');
        }

        AuditLog::log('GRADE_SUPERVISI', 'supervisi_sessions', $sessionId, null, ['total_score' => $totalScore, 'predicate' => $predicate]);
        $this->setFlash('success', "Penilaian supervisi berhasil disimpan: Nilai {$totalScore} - {$predicate}.");
        $this->redirect(Auth::baseUrl() . '/supervisi/penilaian');
    }

    // --- 3. PRINT INSTRUMEN SUPERVISI (OFFICIAL PDF A4) ---
    public function printInstrumen(string $id): void {
        $this->requireAuth();
        $sessionId = (int)$id;

        $session = Database::fetch(
            "SELECT ss.*, t.nama as teacher_name, t.nipy as teacher_nipy, 
                    s.nama as supervisor_name, sub.name as subject_name, c.name as class_name, 
                    u.name as unit_name, u.kop_title, u.kop_subtitle, u.address, u.phone, u.email,
                    ay.name as academic_year_name 
             FROM supervisi_sessions ss 
             JOIN employees t ON ss.teacher_id = t.id 
             JOIN employees s ON ss.supervisor_id = s.id 
             JOIN subjects sub ON ss.subject_id = sub.id 
             JOIN classes c ON ss.class_id = c.id 
             JOIN units u ON ss.unit_id = u.id 
             JOIN academic_years ay ON ss.academic_year_id = ay.id 
             WHERE ss.id = ?",
            [$sessionId]
        );

        if (!$session) {
            die('Data supervisi tidak ditemukan.');
        }

        $scoresRaw = Database::fetchAll("SELECT * FROM supervisi_rubric_scores WHERE supervisi_id = ?", [$sessionId]);
        $scores = [];
        foreach ($scoresRaw as $sc) {
            $scores[$sc['section']][$sc['item_number']] = $sc;
        }

        $kop = ExportHelper::renderKop($session);

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #1e293b; line-height: 1.3; }
                .title-doc { text-align: center; font-size: 13px; font-weight: bold; margin: 10px 0; text-transform: uppercase; }
                .meta-table { width: 100%; margin-bottom: 10px; }
                .meta-table td { padding: 2px 4px; vertical-align: top; }
                .table-rubric { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                .table-rubric th, .table-rubric td { border: 1px solid #64748b; padding: 4px; }
                .table-rubric th { background: #f1f5f9; text-align: center; font-weight: bold; }
                .section-head { background: #e2e8f0; font-weight: bold; }
                .signatures { width: 100%; margin-top: 20px; border-collapse: collapse; page-break-inside: avoid; }
                .sig-box { width: 50%; text-align: center; vertical-align: top; font-size: 10.5px; }
            </style>
        </head>
        <body>
            {$kop}
            <div class='title-doc'>INSTRUMEN PENILAIAN PELAKSANAAN PEMBELAJARAN (SUPERVISI KELAS)<br><span style='font-size: 10px; font-weight: normal;'>TAHUN PELAJARAN {$session['academic_year_name']}</span></div>

            <table class='meta-table'>
                <tr>
                    <td width='22%'><strong>Nama Sekolah</strong></td><td width='2%'>:</td><td width='36%'>{$session['unit_name']}</td>
                    <td width='20%'><strong>Kelas</strong></td><td width='2%'>:</td><td width='18%'>{$session['class_name']}</td>
                </tr>
                <tr>
                    <td><strong>Nama Guru</strong></td><td>:</td><td>{$session['teacher_name']} (NIPY: {$session['teacher_nipy']})</td>
                    <td><strong>Tanggal Supervisi</strong></td><td>:</td><td>" . SecurityHelper::formatTanggalIndo($session['session_date']) . "</td>
                </tr>
                <tr>
                    <td><strong>Mata Pelajaran</strong></td><td>:</td><td>{$session['subject_name']}</td>
                    <td><strong>Nama Asessor</strong></td><td>:</td><td>{$session['supervisor_name']}</td>
                </tr>
            </table>

            <table class='table-rubric'>
                <thead>
                    <tr>
                        <th width='4%'>No</th>
                        <th>ASPEK YANG DINILAI : PELAKSANAAN PEMBELAJARAN</th>
                        <th width='12%'>Skala Penilaian</th>
                        <th width='8%'>Bobot</th>
                        <th width='8%'>Skor Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class='section-head'><td colspan='5'>A. ADMINISTRASI PERENCANAAN PEMBELAJARAN (Bobot: 10)</td></tr>";

        $itemsA = [
            1 => ['Alur Tujuan Pembelajaran / Prota', 1],
            2 => ['Modul Ajar', 1],
            3 => ['Media Pembelajaran', 4],
            4 => ['Daftar Nilai / Hasil Asesmen', 1],
            5 => ['Agenda Harian / Jurnal Mengajar', 2],
            6 => ['Absensi Peserta Didik', 1]
        ];
        foreach ($itemsA as $i => $row) {
            $scVal = $scores['A'][$i]['score_val'] ?? 2;
            $fScore = $scores['A'][$i]['final_item_score'] ?? $row[1];
            $scText = $scVal == 2 ? 'Lengkap (2)' : ($scVal == 1 ? 'Kurang (1)' : 'Tidak Ada (0)');
            $html .= "<tr>
                <td align='center'>{$i}</td>
                <td>{$row[0]}</td>
                <td align='center'>{$scText}</td>
                <td align='center'>{$row[1]}</td>
                <td align='center'><strong>{$fScore}</strong></td>
            </tr>";
        }

        $html .= "<tr class='section-head'><td colspan='5'>B. KEGIATAN PENDAHULUAN (Bobot: 20)</td></tr>";
        $itemsB = [
            1 => ['Pelajaran diawali dengan ucapan salam dan do\'a bersama', 3],
            2 => ['Menyampaikan tujuan pembelajaran dan manfaatnya dengan kehidupan sehari-hari', 7],
            3 => ['Mengajukan pertanyaan yang menantang untuk memotivasi (imaniah) peserta didik', 10]
        ];
        foreach ($itemsB as $i => $row) {
            $scVal = $scores['B'][$i]['score_val'] ?? 3;
            $fScore = $scores['B'][$i]['final_item_score'] ?? $row[1];
            $scText = $scVal == 3 ? 'Sangat Baik (3)' : ($scVal == 2 ? 'Baik (2)' : ($scVal == 1 ? 'Cukup (1)' : 'Kurang (0)'));
            $html .= "<tr>
                <td align='center'>{$i}</td>
                <td>{$row[0]}</td>
                <td align='center'>{$scText}</td>
                <td align='center'>{$row[1]}</td>
                <td align='center'><strong>{$fScore}</strong></td>
            </tr>";
        }

        $html .= "<tr class='section-head'><td colspan='5'>C. KEGIATAN INTI (Bobot: 70)</td></tr>";
        $itemsC = [
            1 => ['Guru menunjukkan sikap yang baik (antusias, ramah, percaya diri) dan sikap tubuh yang sesuai', 6],
            2 => ['Guru menguasai materi dan menggunakan buku pegangan dengan baik (sesuai, tepat guna)', 6],
            3 => ['Aktivitas di kelas bervariasi dan dinamis (interaksi murid-murid, murid-guru)', 6],
            4 => ['Guru menanamkan nilai-nilai ruh keislaman, adab dan nilai positif lainnya', 6],
            5 => ['Guru berbicara dengan suara yang jelas, intonasi bervariasi dan porsi seimbang', 6],
            6 => ['Guru mengecek pemahaman siswa akan konsep yang diajarkan selama pelajaran', 6],
            7 => ['Instruksi yang diberikan jelas dan mudah dipahami siswa', 7],
            8 => ['Manajemen waktu pada pembelajaran ini sudah efektif', 6],
            9 => ['Sarana dan prasarana pendukung pembelajaran digunakan dengan tepat', 6],
            10 => ['Masukan/koreksi dari guru ke siswa diberikan dengan tepat, sesuai kebutuhan', 7],
            11 => ['Guru melakukan asesmen hasil pembelajaran di akhir pembelajaran', 7],
            12 => ['Pelajaran ini berlangsung lancar dan sukses (Syarat: jika ada indikator 1-11 bernilai < 2, tidak boleh Sangat Baik)', 1]
        ];
        foreach ($itemsC as $i => $row) {
            $scVal = $scores['C'][$i]['score_val'] ?? 3;
            $fScore = $scores['C'][$i]['final_item_score'] ?? $row[1];
            $scText = $scVal == 3 ? 'Sangat Baik (3)' : ($scVal == 2 ? 'Baik (2)' : ($scVal == 1 ? 'Cukup (1)' : 'Kurang (0)'));
            $html .= "<tr>
                <td align='center'>{$i}</td>
                <td>{$row[0]}</td>
                <td align='center'>{$scText}</td>
                <td align='center'>{$row[1]}</td>
                <td align='center'><strong>{$fScore}</strong></td>
            </tr>";
        }

        $html .= "
                    <tr style='background: #f8fafc; font-weight: bold;'>
                        <td colspan='3' align='right'>TOTAL SKOR PEROLEHAN (A + B + C) :</td>
                        <td align='center'>100</td>
                        <td align='center' style='font-size: 12px; color: #0f766e;'>{$session['total_score']}</td>
                    </tr>
                    <tr style='background: #f1f5f9; font-weight: bold;'>
                        <td colspan='3' align='right'>PREDIKAT KETERCAPAIAN :</td>
                        <td colspan='2' align='center' style='font-size: 11px;'>{$session['achievement_predicate']}</td>
                    </tr>
                </tbody>
            </table>

            <div style='font-size: 9px; color: #475569; margin-bottom: 8px;'>
                <em>Standar Ketercapaian: 96-100 = SANGAT BAIK | 86-95 = BAIK | 71-85 = CUKUP | <= 70 = PERLU PENINGKATAN</em>
            </div>

            <!-- Catatan Asessor -->
            <table style='width: 100%; border: 1px solid #64748b; border-collapse: collapse; margin-bottom: 12px;'>
                <tr>
                    <td style='padding: 6px; border-bottom: 1px solid #64748b;'>
                        <strong>Hal yang sudah baik:</strong><br>
                        " . nl2br(htmlspecialchars($session['notes_good'] ?: '- Interaksi dengan murid dinamis dan pembukaan salam serta motivasi berjalan baik.')) . "
                    </td>
                </tr>
                <tr>
                    <td style='padding: 6px;'>
                        <strong>Hal yang perlu ditingkatkan/diperbaiki:</strong><br>
                        " . nl2br(htmlspecialchars($session['notes_improve'] ?: '- Manajemen waktu apersepsi dan penguatan asesmen di akhir sesi pembelajaran.')) . "
                    </td>
                </tr>
            </table>

            <table class='signatures'>
                <tr>
                    <td class='sig-box'>
                        Batam, " . SecurityHelper::formatTanggalIndo($session['session_date']) . "<br>
                        <strong>Guru Mata Pelajaran</strong><br><br><br><br>
                        <strong>{$session['teacher_name']}</strong><br>
                        NIPY. {$session['teacher_nipy']}
                    </td>
                    <td class='sig-box'>
                        <br>
                        <strong>Asessor / Supervisor</strong><br><br><br><br>
                        <strong>{$session['supervisor_name']}</strong><br>
                        Kepala Sekolah / Tim Penilai
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ";

        ExportHelper::pdf($html, 'Instrumen_Supervisi_' . $session['teacher_nipy'] . '_' . date('Ymd'), 'A4', 'portrait', true);
    }

    // --- 4. RIWAYAT SUPERVISI SAYA (GURU) ---
    public function riwayatSaya(): void {
        $this->requireAuth();
        $userId = Auth::id();
        $taId = Auth::academicYearId();

        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = $emp['id'] ?? 0;

        $sessions = Database::fetchAll(
            "SELECT ss.*, s.nama as supervisor_name, sub.name as subject_name, c.name as class_name 
             FROM supervisi_sessions ss 
             JOIN employees s ON ss.supervisor_id = s.id 
             JOIN subjects sub ON ss.subject_id = sub.id 
             JOIN classes c ON ss.class_id = c.id 
             WHERE ss.teacher_id = ? AND ss.academic_year_id = ? 
             ORDER BY ss.session_date DESC",
            [$empId, $taId]
        );

        $this->view('supervisi/riwayat_saya', [
            'pageTitle' => 'Riwayat Hasil Supervisi Mengajar Saya',
            'sessions'  => $sessions
        ]);
    }
}

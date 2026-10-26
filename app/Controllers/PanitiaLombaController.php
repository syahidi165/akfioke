<?php
// app/Controllers/PanitiaLombaController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\Uploader;
use Core\ExportHelper;
use Core\SecurityHelper;
use Core\Notification;

class PanitiaLombaController extends Controller {

    // --- 1. KEGIATAN LUAR KBM & PRESENSI PEGAWAI ---
    public function kegiatanIndex(): void {
        $this->requireRole(['waka_kesiswaan', 'kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $kegiatan = Database::fetchAll(
            "SELECT k.*, COUNT(p.employee_id) as participant_count 
             FROM kegiatan_luar_kbm k 
             LEFT JOIN kegiatan_luar_kbm_participants p ON k.id = p.kegiatan_id 
             WHERE k.unit_id = ? AND k.academic_year_id = ? 
             GROUP BY k.id ORDER BY k.event_date DESC",
            [$unitId, $taId]
        );

        $employees = Database::fetchAll(
            "SELECT * FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah') 
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah') 
             ORDER BY nama ASC", 
            [$unitId]
        );

        $participantsRaw = Database::fetchAll(
            "SELECT p.kegiatan_id, p.status, e.id as employee_id, e.nama, e.nipy, e.position 
             FROM kegiatan_luar_kbm_participants p 
             JOIN employees e ON p.employee_id = e.id 
             JOIN kegiatan_luar_kbm k ON p.kegiatan_id = k.id 
             WHERE k.unit_id = ? AND k.academic_year_id = ? 
             ORDER BY e.nama ASC",
            [$unitId, $taId]
        );
        $participantsByKegiatan = [];
        foreach ($participantsRaw as $pr) {
            $participantsByKegiatan[$pr['kegiatan_id']][] = $pr;
        }

        $this->view('panitia/kegiatan', [
            'pageTitle'              => 'Kegiatan di Luar KBM & Presensi Pegawai',
            'kegiatan'               => $kegiatan,
            'employees'              => $employees,
            'participantsByKegiatan' => $participantsByKegiatan
        ]);
    }

    public function kegiatanSave(): void {
        $this->requireRole(['waka_kesiswaan', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $title = trim($_POST['title'] ?? '');
        $eventDate = $_POST['event_date'] ?? date('Y-m-d');
        $location = trim($_POST['location'] ?? 'Lingkungan Sekolah');
        $participants = $_POST['emp_ids'] ?? [];

        if (!empty($title)) {
            Database::execute(
                "INSERT INTO kegiatan_luar_kbm (unit_id, academic_year_id, title, event_date, location, created_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?)",
                [$unitId, $taId, $title, $eventDate, $location, $userId]
            );
            $kegId = Database::lastInsertId();

            foreach ($participants as $empId) {
                Database::execute(
                    "INSERT INTO kegiatan_luar_kbm_participants (kegiatan_id, employee_id, status) VALUES (?, ?, 'Hadir')",
                    [$kegId, (int)$empId]
                );
            }

            AuditLog::log('CREATE_KEGIATAN_LUAR_KBM', 'kegiatan_luar_kbm', $kegId, null, ['title' => $title]);
            $this->setFlash('success', 'Kegiatan luar KBM dan daftar kepesertaan berhasil disimpan.');
        }

        $this->redirect(Auth::baseUrl() . '/kegiatan/luar-kbm');
    }

    // --- 2. PENUGASAN PANITIA LOMBA (OSN, O2SN, FLS2N) ---
    public function penugasanIndex(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kesiswaan', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $assignments = Database::fetchAll(
            "SELECT pla.*, e.nama as teacher_name, e.nipy, e.position as teacher_position, u.name as unit_name 
             FROM panitia_lomba_assignments pla 
             JOIN employees e ON pla.teacher_id = e.id 
             JOIN units u ON pla.unit_id = u.id 
             WHERE pla.unit_id = ? AND pla.academic_year_id = ? 
             ORDER BY pla.event_type ASC, e.nama ASC",
            [$unitId, $taId]
        );

        $teachers = Database::fetchAll(
            "SELECT * FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 
               AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Waka%' OR level_posisi LIKE '%Waka%' OR position LIKE '%Tutor%' OR level_posisi LIKE '%Tutor%')
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY nama ASC", 
            [$unitId]
        );

        $students = Database::fetchAll(
            "SELECT s.id, s.nis, s.nama, c.name as class_name 
             FROM students s 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             ORDER BY c.name ASC, s.nama ASC", 
            [$unitId]
        );

        $studentMap = [];
        foreach ($students as $st) {
            $studentMap[$st['id']] = $st;
        }

        $this->view('panitia/penugasan', [
            'pageTitle'   => 'SK Penugasan Guru Pembina Lomba (OSN/O2SN/FLS2N)',
            'assignments' => $assignments,
            'teachers'    => $teachers,
            'students'    => $students,
            'studentMap'  => $studentMap
        ]);
    }

    public function penugasanSave(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kesiswaan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $teacherId = (int)($_POST['teacher_id'] ?? 0);
        $eventType = $_POST['event_type'] ?? 'OSN';
        $skNumber = trim($_POST['sk_number'] ?? '');
        $studentIds = $_POST['student_ids'] ?? [];
        $studentIdsJson = !empty($studentIds) ? json_encode(array_map('intval', (array)$studentIds)) : null;

        if (empty($skNumber)) {
            $count = Database::fetch("SELECT COUNT(*) as c FROM panitia_lomba_assignments WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId])['c'] ?? 0;
            $skNumber = sprintf("421.1/SK-LOMBA/%s/%03d", date('Y'), $count + 1);
        }

        if ($teacherId) {
            Database::execute(
                "INSERT INTO panitia_lomba_assignments (unit_id, academic_year_id, teacher_id, event_type, is_active, status, sk_number, student_ids) 
                 VALUES (?, ?, ?, ?, 1, 'Aktif', ?, ?)",
                [$unitId, $taId, $teacherId, $eventType, $skNumber, $studentIdsJson]
            );
            $this->setFlash('success', "Penugasan guru pembina lomba {$eventType} berhasil ditetapkan.");
        }

        $this->redirect(Auth::baseUrl() . '/panitia/penugasan');
    }

    public function penugasanToggleStatus(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kesiswaan']);
        $id = (int)($_POST['assignment_id'] ?? 0);
        $unitId = Auth::unitId();

        $assignment = Database::fetch("SELECT * FROM panitia_lomba_assignments WHERE id = ? AND unit_id = ?", [$id, $unitId]);
        if ($assignment) {
            $newStatus = ($assignment['status'] === 'Selesai') ? 'Aktif' : 'Selesai';
            Database::execute("UPDATE panitia_lomba_assignments SET status = ? WHERE id = ?", [$newStatus, $id]);
            $msg = ($newStatus === 'Selesai') ? 'Status lomba berhasil ditandai telah Selesai.' : 'Status penugasan lomba diaktifkan kembali.';
            $this->setFlash('success', $msg);
        }
        $this->redirect(Auth::baseUrl() . '/panitia/penugasan');
    }

    public function penugasanUpdateStudents(): void {
        $this->requireRole(['kepala_sekolah', 'waka_kesiswaan']);
        $id = (int)($_POST['assignment_id'] ?? 0);
        $unitId = Auth::unitId();
        $studentIds = $_POST['student_ids'] ?? [];
        $skNumber = trim($_POST['sk_number'] ?? '');
        $status = trim($_POST['status'] ?? '');

        $assignment = Database::fetch("SELECT * FROM panitia_lomba_assignments WHERE id = ? AND unit_id = ?", [$id, $unitId]);
        if ($assignment) {
            $studentIdsJson = !empty($studentIds) ? json_encode(array_map('intval', (array)$studentIds)) : null;
            $newStatus = in_array($status, ['Aktif', 'Selesai']) ? $status : ($assignment['status'] ?? 'Aktif');
            Database::execute(
                "UPDATE panitia_lomba_assignments SET student_ids = ?, sk_number = ?, status = ? WHERE id = ?",
                [$studentIdsJson, $skNumber, $newStatus, $id]
            );
            $this->setFlash('success', 'Data santri bimbingan, No SK, dan status lomba berhasil diperbarui.');
        }
        $this->redirect(Auth::baseUrl() . '/panitia/penugasan');
    }

    public function penugasanCetakSk($id = null): void {
        $this->requireRole(['kepala_sekolah', 'waka_kesiswaan', 'deputi_pendidikan', 'walas', 'guru_mapel', 'guru_mapel_kbtk']);
        $id = (int)($id ?? $_GET['id'] ?? 0);
        $unitId = Auth::unitId();

        $assignment = Database::fetch(
            "SELECT pla.*, e.nama as teacher_name, e.nipy, e.position as teacher_position, u.name as unit_name 
             FROM panitia_lomba_assignments pla 
             JOIN employees e ON pla.teacher_id = e.id 
             JOIN units u ON pla.unit_id = u.id 
             WHERE pla.id = ? AND pla.unit_id = ?",
            [$id, $unitId]
        );

        if (!$assignment) {
            $this->setFlash('danger', 'Data penugasan tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/panitia/penugasan');
            return;
        }

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]) ?? [];
        $kopHtml = ExportHelper::renderKop($unit);

        // Fetch students
        $studentList = [];
        $rawIds = json_decode($assignment['student_ids'] ?? '[]', true);
        if (!empty($rawIds) && is_array($rawIds)) {
            $inClause = implode(',', array_fill(0, count($rawIds), '?'));
            $studentList = Database::fetchAll(
                "SELECT s.id, s.nis, s.nama, c.name as class_name 
                 FROM students s 
                 LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                 LEFT JOIN classes c ON cs.class_id = c.id 
                 WHERE s.id IN ($inClause) 
                 ORDER BY c.name ASC, s.nama ASC",
                $rawIds
            );
        }

        // Fetch principal
        $principal = Database::fetch(
            "SELECT e.nama, e.nipy, e.position 
             FROM employees e 
             WHERE e.unit_id = ? AND (e.level_posisi = 'Kepala Sekolah' OR e.position LIKE '%Kepala Sekolah%') AND e.is_deleted = 0 
             LIMIT 1",
            [$unitId]
        );

        $skNo = htmlspecialchars($assignment['sk_number'] ?: ('421.1/SK-LOMBA/' . date('Y') . '/' . sprintf('%03d', $id)), ENT_QUOTES, 'UTF-8');
        $teacherName = htmlspecialchars($assignment['teacher_name'], ENT_QUOTES, 'UTF-8');
        $teacherNipy = htmlspecialchars($assignment['nipy'] ?? '-', ENT_QUOTES, 'UTF-8');
        $eventType = htmlspecialchars($assignment['event_type'], ENT_QUOTES, 'UTF-8');
        $unitName = htmlspecialchars($assignment['unit_name'], ENT_QUOTES, 'UTF-8');
        $principalName = htmlspecialchars($principal['nama'] ?? 'Kepala Sekolah', ENT_QUOTES, 'UTF-8');
        $principalNipy = htmlspecialchars($principal['nipy'] ?? '-', ENT_QUOTES, 'UTF-8');
        $tanggalSurat = SecurityHelper::formatTanggalIndo(date('Y-m-d'));

        $fullEventName = match($assignment['event_type']) {
            'OSN' => 'Olimpiade Sains Nasional (OSN)',
            'O2SN' => 'Olimpiade Olahraga Siswa Nasional (O2SN)',
            'FLS2N' => 'Festival dan Lomba Seni Siswa Nasional (FLS2N)',
            default => $assignment['event_type']
        };

        $studentRowsHtml = '';
        if (empty($studentList)) {
            $studentRowsHtml = '<tr><td colspan="4" style="text-align: center; color: #666; padding: 12px; border: 1px solid #333;">(Belum ada santri yang didaftarkan pada SK ini)</td></tr>';
        } else {
            foreach ($studentList as $idx => $st) {
                $no = $idx + 1;
                $sNis = htmlspecialchars($st['nis'] ?? '-', ENT_QUOTES, 'UTF-8');
                $sNama = htmlspecialchars($st['nama'], ENT_QUOTES, 'UTF-8');
                $sKelas = htmlspecialchars($st['class_name'] ?? '-', ENT_QUOTES, 'UTF-8');
                $studentRowsHtml .= "
                <tr>
                    <td style='text-align: center; border: 1px solid #333; padding: 6px 8px;'>{$no}</td>
                    <td style='text-align: center; border: 1px solid #333; padding: 6px 8px; font-family: monospace;'>{$sNis}</td>
                    <td style='border: 1px solid #333; padding: 6px 8px;'><strong>{$sNama}</strong></td>
                    <td style='text-align: center; border: 1px solid #333; padding: 6px 8px;'>{$sKelas}</td>
                </tr>";
            }
        }

        $statusLomba = $assignment['status'] ?? 'Aktif';

        $html = "
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset='utf-8'>
            <title>SK Pembina Lomba</title>
            <style>
                body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 11pt; color: #111; line-height: 1.5; margin: 20px; }
                h2, h3, h4 { margin: 0; text-align: center; text-transform: uppercase; }
                .table-data { width: 100%; border-collapse: collapse; margin-top: 10px; margin-bottom: 15px; }
                .table-data th { background-color: #f1f5f9; border: 1px solid #333; padding: 8px; font-size: 10pt; text-align: center; }
                .table-data td { font-size: 10pt; }
            </style>
        </head>
        <body>
            {$kopHtml}

            <div style='text-align: center; margin-bottom: 20px;'>
                <h3 style='font-size: 13pt; text-decoration: underline; letter-spacing: 0.5px;'>SURAT KEPUTUSAN KEPALA SEKOLAH</h3>
                <div style='font-size: 10.5pt; font-weight: bold; margin-top: 2px;'>Nomor: {$skNo}</div>
                <div style='font-size: 10pt; font-weight: bold; text-transform: uppercase; margin-top: 6px;'>
                    TENTANG<br>
                    PENUGASAN GURU PEMBINA DAN PESERTA DIDIK PADA AJANG TALENTA<br>
                    {$fullEventName} TINGKAT KOTA / WILAYAH
                </div>
            </div>

            <table style='width: 100%; margin-bottom: 12px; font-size: 10.5pt;'>
                <tr>
                    <td style='width: 120px; vertical-align: top; font-weight: bold;'>Menimbang</td>
                    <td style='width: 15px; vertical-align: top;'>:</td>
                    <td style='text-align: justify;'>
                        Bahwa dalam rangka mengembangkan potensi, bakat, dan prestasi peserta didik pada bidang talenta kompetisi resmi nasional, dipandang perlu menetapkan guru pembina dan peserta didik yang mewakili satuan pendidikan {$unitName}.
                    </td>
                </tr>
                <tr>
                    <td style='vertical-align: top; font-weight: bold; padding-top: 6px;'>Mengingat</td>
                    <td style='vertical-align: top; padding-top: 6px;'>:</td>
                    <td style='text-align: justify; padding-top: 6px;'>
                        1. Program Kerja Kesiswaan dan Prestasi Santri {$unitName};<br>
                        2. Petunjuk Teknis Pelaksanaan Ajang Talenta Nasional Puspresnas / Balai Pengembangan Talenta Indonesia.
                    </td>
                </tr>
            </table>

            <div style='text-align: center; font-weight: bold; margin: 15px 0 10px 0; font-size: 11pt;'>MEMUTUSKAN</div>

            <table style='width: 100%; margin-bottom: 14px; font-size: 10.5pt;'>
                <tr>
                    <td style='width: 120px; vertical-align: top; font-weight: bold;'>Menetapkan</td>
                    <td style='width: 15px; vertical-align: top;'>:</td>
                    <td></td>
                </tr>
                <tr>
                    <td style='vertical-align: top; font-weight: bold;'>PERTAMA</td>
                    <td style='vertical-align: top;'>:</td>
                    <td>
                        Menugaskan Guru/Pendidik di bawah ini sebagai <strong>Guru Pembina Lomba {$eventType}</strong>:
                        <table style='margin-top: 6px; margin-left: 10px; font-size: 10pt;'>
                            <tr><td style='width: 120px;'>Nama Lengkap</td><td style='width: 10px;'>:</td><td><strong>{$teacherName}</strong></td></tr>
                            <tr><td>NIPY</td><td>:</td><td style='font-family: monospace;'>{$teacherNipy}</td></tr>
                            <tr><td>Tugas Bimbingan</td><td>:</td><td>Pembina Cabang {$fullEventName}</td></tr>
                            <tr><td>Status Penugasan</td><td>:</td><td><strong style='color: #0d9488;'>{$statusLomba}</strong></td></tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style='vertical-align: top; font-weight: bold; padding-top: 10px;'>KEDUA</td>
                    <td style='vertical-align: top; padding-top: 10px;'>:</td>
                    <td style='padding-top: 10px;'>
                        Menetapkan Peserta Didik yang dibina dan diutus sebagai kontingen resmi sekolah:
                    </td>
                </tr>
            </table>

            <table class='table-data'>
                <thead>
                    <tr>
                        <th style='width: 35px;'>No</th>
                        <th style='width: 100px;'>NIS</th>
                        <th>Nama Lengkap Peserta Didik</th>
                        <th style='width: 120px;'>Rombel / Kelas</th>
                    </tr>
                </thead>
                <tbody>
                    {$studentRowsHtml}
                </tbody>
            </table>

            <table style='width: 100%; font-size: 10.5pt; margin-top: 5px;'>
                <tr>
                    <td style='width: 120px; vertical-align: top; font-weight: bold;'>KETIGA</td>
                    <td style='width: 15px; vertical-align: top;'>:</td>
                    <td style='text-align: justify;'>
                        Surat Keputusan ini berlaku sejak tanggal ditetapkan hingga seluruh rangkaian kegiatan lomba selesai, dengan ketentuan apabila terdapat kekeliruan akan diperbaiki sebagaimana mestinya.
                    </td>
                </tr>
            </table>

            <div style='margin-top: 30px; float: right; width: 240px; text-align: left; font-size: 10.5pt;'>
                <div>Ditetapkan di : Batam</div>
                <div>Pada tanggal : {$tanggalSurat}</div>
                <div style='margin-top: 6px; font-weight: bold;'>Kepala Sekolah,</div>
                <div style='height: 55px;'></div>
                <div style='font-weight: bold; text-decoration: underline;'>{$principalName}</div>
                <div style='font-family: monospace; font-size: 9.5pt;'>NIPY. {$principalNipy}</div>
            </div>
            <div style='clear: both;'></div>
        </body>
        </html>";

        ExportHelper::pdf($html, "SK_Pembina_Lomba_{$eventType}_" . preg_replace('/[^a-zA-Z0-9]/', '_', $teacherName));
    }

    // --- 3. PRESTASI SISWA OLEH PANITIA LOMBA ---
    public function prestasiPanitiaIndex(): void {
        $this->requireRole(['panitia_lomba', 'waka_kesiswaan', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $prestasi = Database::fetchAll(
            "SELECT ps.*, s.nama as student_name, s.nis 
             FROM prestasi_siswa ps 
             JOIN students s ON ps.student_id = s.id 
             WHERE ps.unit_id = ? AND ps.academic_year_id = ? AND ps.category IN ('OSN', 'O2SN', 'FLS2N') 
             ORDER BY ps.id DESC",
            [$unitId, $taId]
        );

        $students = Database::fetchAll("SELECT * FROM students WHERE unit_id = ? AND status = 'Aktif' ORDER BY nama ASC", [$unitId]);

        $this->view('panitia/prestasi_panitia', [
            'pageTitle' => 'Input Prestasi Kejuaraan Lomba Resmi (OSN/O2SN/FLS2N)',
            'prestasi'  => $prestasi,
            'students'  => $students
        ]);
    }

    public function prestasiPanitiaSave(): void {
        $this->requireRole(['panitia_lomba', 'waka_kesiswaan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $category = $_POST['category'] ?? 'OSN';
        $eventName = trim($_POST['event_name'] ?? '');
        $fieldName = trim($_POST['field_name'] ?? '');
        $rank = trim($_POST['rank_position'] ?? 'Juara 1');
        $level = trim($_POST['level'] ?? 'Kota Batam');

        $certPath = null;
        if (!empty($_FILES['certificate']['name'])) {
            $up = Uploader::upload($_FILES['certificate'], 'prestasi');
            if ($up['success']) $certPath = $up['file_path'];
        }

        Database::execute(
            "INSERT INTO prestasi_siswa (unit_id, student_id, academic_year_id, category, event_name, field_name, rank_position, level, certificate_path, recorded_by_user_id) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
            [$unitId, $studentId, $taId, $category, $eventName, $fieldName, $rank, $level, $certPath, $userId]
        );

        $this->setFlash('success', 'Data prestasi kejuaraan santri berhasil dicatat.');
        $this->redirect(Auth::baseUrl() . '/prestasi/panitia');
    }

    // --- 4. PRESTASI SISWA OLEH WALI KELAS (LOMBA SWASTA / EKSTERNAL) ---
    public function prestasiWalasIndex(): void {
        $this->requireRole(['walas', 'waka_kesiswaan', 'kepala_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $activeRole = Auth::activeRoleName();
        $isWalas = ($activeRole === 'walas');

        $myClass = null;
        if ($isWalas) {
            $userId = Auth::id();
            $userEmp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
            $empId = (int)($userEmp['id'] ?? 0);
            $myClass = Database::fetch(
                "SELECT * FROM classes WHERE (walas_employee_id = ? OR walas_id = ? OR walas_id = ?) AND academic_year_id = ? AND unit_id = ?",
                [$empId, $empId, $userId, $taId, $unitId]
            );

            if ($myClass) {
                // Santri khusus kelas yang diampu walas
                $students = Database::fetchAll(
                    "SELECT s.*, c.name as class_name 
                     FROM students s 
                     JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                     JOIN classes c ON cs.class_id = c.id 
                     WHERE cs.class_id = ? AND s.unit_id = ? 
                     ORDER BY s.nama ASC",
                    [$myClass['id'], $unitId]
                );
                // Prestasi santri di kelasnya
                $prestasi = Database::fetchAll(
                    "SELECT ps.*, s.nama as student_name, s.nis 
                     FROM prestasi_siswa ps 
                     JOIN students s ON ps.student_id = s.id 
                     JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
                     WHERE cs.class_id = ? AND ps.academic_year_id = ? AND ps.category = 'Swasta' 
                     ORDER BY ps.id DESC",
                    [$myClass['id'], $taId]
                );
            } else {
                $students = [];
                $prestasi = [];
            }
        } else {
            $prestasi = Database::fetchAll(
                "SELECT ps.*, s.nama as student_name, s.nis 
                 FROM prestasi_siswa ps 
                 JOIN students s ON ps.student_id = s.id 
                 WHERE ps.unit_id = ? AND ps.academic_year_id = ? AND ps.category = 'Swasta' 
                 ORDER BY ps.id DESC",
                [$unitId, $taId]
            );
            $students = Database::fetchAll("SELECT * FROM students WHERE unit_id = ? AND status = 'Aktif' ORDER BY nama ASC", [$unitId]);
        }

        $this->view('panitia/prestasi_walas', [
            'pageTitle' => 'Input Prestasi Santri Non-Kedinasan / Swasta (Wali Kelas)',
            'prestasi'  => $prestasi,
            'students'  => $students,
            'myClass'   => $myClass
        ]);
    }

    public function prestasiWalasSave(): void {
        $this->requireRole('walas');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $eventName = trim($_POST['event_name'] ?? '');
        $rank = trim($_POST['rank_position'] ?? 'Juara 1');
        $level = trim($_POST['level'] ?? 'Tingkat Kota / Wilayah');

        // Validasi: pastikan santri terdaftar di kelas walas
        $userEmp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
        $empId = (int)($userEmp['id'] ?? 0);
        $myClass = Database::fetch(
            "SELECT * FROM classes WHERE (walas_employee_id = ? OR walas_id = ? OR walas_id = ?) AND academic_year_id = ? AND unit_id = ?",
            [$empId, $empId, $userId, $taId, $unitId]
        );
        if (!$myClass) {
            $this->setFlash('danger', 'Akses ditolak: Anda tidak memiliki rombel kelas aktif.');
            $this->redirect(Auth::baseUrl() . '/prestasi/walas');
            return;
        }

        $inClass = Database::fetch(
            "SELECT id FROM class_students WHERE student_id = ? AND class_id = ? AND status = 'Aktif'",
            [$studentId, $myClass['id']]
        );
        if (!$inClass) {
            $this->setFlash('danger', 'Akses ditolak: Anda hanya dapat mencatat prestasi untuk santri di kelas Anda sendiri.');
            $this->redirect(Auth::baseUrl() . '/prestasi/walas');
            return;
        }

        $certPath = null;
        if (!empty($_FILES['certificate']['name'])) {
            $up = Uploader::upload($_FILES['certificate'], 'prestasi_swasta');
            if ($up['success']) $certPath = $up['file_path'];
        }

        Database::execute(
            "INSERT INTO prestasi_siswa (unit_id, student_id, academic_year_id, category, event_name, rank_position, level, certificate_path, recorded_by_user_id) 
             VALUES (?, ?, ?, 'Swasta', ?, ?, ?, ?, ?)",
            [$unitId, $studentId, $taId, $eventName, $rank, $level, $certPath, $userId]
        );

        $this->setFlash('success', 'Prestasi kejuaraan swasta santri berhasil dicatat.');
        $this->redirect(Auth::baseUrl() . '/prestasi/walas');
    }

    // --- 5. GURU BERPRESTASI (DITETAPKAN KEPSEK) ---
    public function guruPrestasiIndex(): void {
        $this->requireRole(['kepala_sekolah', 'deputi_pendidikan']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $list = Database::fetchAll(
            "SELECT gb.*, e.nama as teacher_name, e.nipy, u.name as unit_name 
             FROM guru_berprestasi gb 
             JOIN employees e ON gb.employee_id = e.id 
             JOIN units u ON gb.unit_id = u.id 
             WHERE gb.unit_id = ? AND gb.academic_year_id = ? 
             ORDER BY gb.id DESC",
            [$unitId, $taId]
        );

        $teachers = Database::fetchAll(
            "SELECT * FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 
               AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Waka%' OR level_posisi LIKE '%Waka%' OR position LIKE '%Tutor%' OR level_posisi LIKE '%Tutor%')
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY nama ASC", 
            [$unitId]
        );

        $this->view('panitia/guru_prestasi', [
            'pageTitle' => 'Penetapan Guru Berprestasi Tingkat Satuan Pendidikan',
            'list'      => $list,
            'teachers'  => $teachers
        ]);
    }

    public function guruPrestasiSave(): void {
        $this->requireRole('kepala_sekolah');
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $level = trim($_POST['level'] ?? 'Tingkat Yayasan');
        $year = $_POST['year'] ?? date('Y');
        $notes = trim($_POST['notes'] ?? '');
        $status = $_POST['status'] ?? 'Terverifikasi';

        $proofDocument = null;
        if (!empty($_FILES['certificate']['name'])) {
            $up = Uploader::upload($_FILES['certificate'], 'prestasi');
            if ($up['success']) {
                $proofDocument = $up['file_path'];
            }
        }

        if ($employeeId && !empty($title)) {
            Database::execute(
                "INSERT INTO guru_berprestasi (unit_id, academic_year_id, employee_id, title, level, year, notes, status, proof_document, created_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$unitId, $taId, $employeeId, $title, $level, $year, $notes, $status, $proofDocument, $userId]
            );
            $this->setFlash('success', 'Guru berprestasi berhasil ditetapkan.');
        }

        $this->redirect(Auth::baseUrl() . '/guru/prestasi-tetapkan');
    }

    public function guruPrestasiUpdate(): void {
        $this->requireRole('kepala_sekolah');
        $unitId = Auth::unitId();
        $id = (int)($_POST['id'] ?? 0);

        $gp = Database::fetch("SELECT * FROM guru_berprestasi WHERE id = ? AND unit_id = ?", [$id, $unitId]);
        if (!$gp) {
            $this->setFlash('danger', 'Data guru berprestasi tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/guru/prestasi-tetapkan');
            return;
        }

        $title = trim($_POST['title'] ?? $gp['title']);
        $level = trim($_POST['level'] ?? $gp['level']);
        $year = trim($_POST['year'] ?? $gp['year']);
        $notes = trim($_POST['notes'] ?? $gp['notes']);
        $status = $_POST['status'] ?? $gp['status'];

        $proofDocument = $gp['proof_document'];
        if (!empty($_FILES['certificate']['name'])) {
            $up = Uploader::upload($_FILES['certificate'], 'prestasi');
            if ($up['success']) {
                $proofDocument = $up['file_path'];
            }
        }

        Database::execute(
            "UPDATE guru_berprestasi SET title = ?, level = ?, year = ?, notes = ?, status = ?, proof_document = ? WHERE id = ? AND unit_id = ?",
            [$title, $level, $year, $notes, $status, $proofDocument, $id, $unitId]
        );

        $this->setFlash('success', 'Data status dan berkas bukti guru berprestasi berhasil diperbarui.');
        $this->redirect(Auth::baseUrl() . '/guru/prestasi-tetapkan');
    }

    // --- 6. BEST PRACTICE / PTK (GURU & WALAS) ---
    public function bestPracticeIndex(): void {
        $this->requireRole(['guru_mapel', 'guru_mapel_kbtk', 'guru_diniyah', 'guru_tahfidz', 'guru_tk', 'walas']);
        $userId = Auth::id();
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $emp = Database::fetch("SELECT * FROM employees WHERE user_id = ?", [$userId]);
        $empId = (int)($emp['id'] ?? 0);

        $karyaList = Database::fetchAll(
            "SELECT * FROM karya_ilmiah_guru 
             WHERE employee_id = ? AND academic_year_id = ? 
             ORDER BY id DESC",
            [$empId, $taId]
        );

        $isWalas = Auth::hasRole('walas');
        $defaultType = $isWalas ? 'PTK' : 'Best Practice';
        $pageTitle = $isWalas ? 'Upload Laporan PTK (Penelitian Tindakan Kelas)' : 'Upload Dokumen Best Practice Pembelajaran';

        $this->view('panitia/best_practice', [
            'pageTitle'   => $pageTitle,
            'karyaList'   => $karyaList,
            'defaultType' => $defaultType,
            'isWalas'     => $isWalas,
            'emp'         => $emp
        ]);
    }

    public function bestPracticeUpload(): void {
        $this->requireRole(['guru_mapel', 'guru_mapel_kbtk', 'guru_diniyah', 'guru_tahfidz', 'guru_tk', 'walas']);
        $userId = Auth::id();
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        $emp = Database::fetch("SELECT * FROM employees WHERE user_id = ?", [$userId]);
        $empId = (int)($emp['id'] ?? 0);

        $type = $_POST['type'] ?? (Auth::hasRole('walas') ? 'PTK' : 'Best Practice');
        if (!in_array($type, ['Best Practice', 'PTK'], true)) {
            $type = Auth::hasRole('walas') ? 'PTK' : 'Best Practice';
        }
        $title = trim($_POST['title'] ?? '');

        if (empty($title)) {
            $this->setFlash('error', 'Judul karya tulis ilmiah wajib diisi.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard'));
            return;
        }

        if (!isset($_FILES['doc_file']) || $_FILES['doc_file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('error', 'Silakan pilih berkas dokumen PDF yang valid.');
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard'));
            return;
        }

        $uploadRes = Uploader::upload($_FILES['doc_file'], 'karya_ilmiah');
        if (!$uploadRes['success']) {
            $this->setFlash('error', $uploadRes['message']);
            $this->redirect($_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard'));
            return;
        }

        Database::execute(
            "INSERT INTO karya_ilmiah_guru (user_id, employee_id, unit_id, academic_year_id, type, title, file_path, original_filename, file_size, status) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'Menunggu Review')",
            [$userId, $empId, $unitId, $taId, $type, $title, $uploadRes['file_path'], $uploadRes['original_filename'], $uploadRes['file_size']]
        );

        $karyaId = Database::lastInsertId();
        AuditLog::log('UPLOAD_KARYA_ILMIAH', 'karya_ilmiah_guru', $karyaId, null, ['type' => $type, 'title' => $title]);

        // Broadcast notifikasi ke Kepala Sekolah
        Notification::broadcastToRole(
            'kepala_sekolah',
            "Pengajuan Berkas {$type} Guru",
            "Pegawai " . ($emp['nama'] ?? 'Guru') . " mengunggah berkas {$type}: \"{$title}\". Menunggu verifikasi Anda.",
            'info',
            Auth::baseUrl() . '/administrasi/review-guru'
        );

        $this->setFlash('success', "Dokumen {$type} berhasil diunggah! Menunggu review dan persetujuan Kepala Sekolah.");
        $redirectUrl = (Auth::hasRole('walas') && $type === 'PTK') ? (Auth::baseUrl() . '/walas/ptk') : (Auth::baseUrl() . '/guru/best-practice');
        $this->redirect($redirectUrl);
    }
}

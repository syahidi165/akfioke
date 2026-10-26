<?php
// app/Controllers/AlumniController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class AlumniController extends Controller {

    // --- 1. REKOMENDASI STUDI LANJUT OLEH WALAS ---
    public function walasRekomendasi(): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'operator_sekolah']);
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();
        $userId = Auth::id();

        // Cari informasi akun walas dan kelas yang diampu
        $userEmp = Database::fetch("SELECT * FROM employees WHERE user_id = ?", [$userId]);
        $myClass = null;
        if ($userEmp) {
            $myClass = Database::fetch(
                "SELECT * FROM classes WHERE (walas_employee_id = ? OR walas_id = ? OR walas_id = ?) AND academic_year_id = ? AND unit_id = ?",
                [$userEmp['id'], $userEmp['id'], $userId, $taId, $unitId]
            );
        }

        $activeRole = Auth::activeRoleName();
        $isWalas = ($activeRole === 'walas');

        $isFinalGrade = false;
        if ($myClass) {
            $lvl = strtoupper($myClass['level'] ?? '');
            $nm = strtoupper($myClass['name'] ?? '');
            $isFinalGrade = (str_contains($lvl, '6') || str_contains($lvl, '9') || 
                             str_contains($lvl, '12') || str_contains($lvl, 'TK B') || 
                             str_contains($nm, 'KELAS 6') || str_contains($nm, 'KELAS 9') || 
                             str_contains($nm, 'KELAS 12') || str_contains($nm, 'TK B') ||
                             str_contains($nm, ' 6') || str_contains($nm, ' 9') || str_contains($nm, ' 12'));
        }

        $students = [];
        if ($isWalas) {
            if ($isFinalGrade && $myClass) {
                // Walas tingkat akhir: HANYA santri di kelasnya sendiri
                $query = "SELECT s.*, cs.class_id, c.name as class_name, c.level as class_level, u.name as unit_name,
                                 at.id as tracking_id, at.destination_type, at.destination_name, at.is_favorite, at.major, at.notes as alumni_notes
                          FROM students s
                          JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                          JOIN classes c ON cs.class_id = c.id
                          JOIN units u ON s.unit_id = u.id
                          LEFT JOIN alumni_tracking at ON s.id = at.student_id
                          WHERE s.status = 'Aktif'
                            AND cs.class_id = ?
                          ORDER BY s.nama ASC";
                $students = Database::fetchAll($query, [$taId, $myClass['id']]);
            } else {
                // Bukan walas tingkat akhir: kosongkan daftar santri
                $students = [];
            }
        } else {
            // Kepala sekolah atau Operator Sekolah: seluruh santri tingkat akhir di unit
            $query = "SELECT s.*, cs.class_id, c.name as class_name, c.level as class_level, u.name as unit_name,
                             at.id as tracking_id, at.destination_type, at.destination_name, at.is_favorite, at.major, at.notes as alumni_notes
                      FROM students s
                      JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                      JOIN classes c ON cs.class_id = c.id
                      JOIN units u ON s.unit_id = u.id
                      LEFT JOIN alumni_tracking at ON s.id = at.student_id
                      WHERE s.status = 'Aktif'
                        AND s.unit_id = ?
                        AND (c.level IN ('SD 6', 'SMP 9', 'SMA 12', 'TK B')
                             OR c.level LIKE '%6%' OR c.level LIKE '%9%' OR c.level LIKE '%12%' OR c.level LIKE '%TK B%'
                             OR c.name LIKE '%Kelas 6%' OR c.name LIKE '%Kelas 9%' OR c.name LIKE '%Kelas 12%' OR c.name LIKE '%TK B%')
                      ORDER BY c.name ASC, s.nama ASC";
            $students = Database::fetchAll($query, [$taId, $unitId]);
        }

        // Rekap riil seluruh rekomendasi yang telah tersimpan
        if ($isWalas && $myClass) {
            $savedRecommendations = Database::fetchAll(
                "SELECT at.*, s.nama as student_name, s.nis, c.name as class_name, c.level as class_level 
                 FROM alumni_tracking at 
                 JOIN students s ON at.student_id = s.id 
                 JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                 JOIN classes c ON cs.class_id = c.id
                 WHERE cs.class_id = ?
                 ORDER BY at.id DESC",
                [$taId, $myClass['id']]
            );
        } else {
            $savedRecommendations = Database::fetchAll(
                "SELECT at.*, s.nama as student_name, s.nis, c.name as class_name, c.level as class_level 
                 FROM alumni_tracking at 
                 JOIN students s ON at.student_id = s.id 
                 LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ?
                 LEFT JOIN classes c ON cs.class_id = c.id
                 WHERE at.unit_id = ?
                 ORDER BY at.id DESC",
                [$taId, $unitId]
            );
        }

        $this->view('alumni/walas_rekomendasi', [
            'pageTitle'            => 'Rekomendasi Studi Lanjut Santri Tingkat Akhir',
            'students'             => $students,
            'savedRecommendations' => $savedRecommendations,
            'myClass'              => $myClass,
            'userEmp'              => $userEmp,
            'isWalas'              => $isWalas,
            'isFinalGrade'         => $isFinalGrade
        ]);
    }

    public function walasRekomendasiSubmit(): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'operator_sekolah']);
        $unitId = Auth::unitId();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $destType = trim($_POST['destination_type'] ?? 'Sekolah');
        $destName = trim($_POST['destination_name'] ?? '');
        $major = trim($_POST['major'] ?? '');
        $isFav = !empty($_POST['is_favorite']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');
        $gradYear = date('Y');

        $st = Database::fetch("SELECT id, nama, unit_id FROM students WHERE id = ? AND unit_id = ?", [$studentId, $unitId]);
        if (!$st || empty($destName)) {
            $this->setFlash('danger', 'Mohon pilih santri dan lengkapi nama lembaga lanjutan yang direkomendasikan.');
            $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
            return;
        }

        $activeRole = Auth::activeRoleName();
        if ($activeRole === 'walas') {
            $userId = Auth::id();
            $taId = Auth::academicYearId();
            $userEmp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
            $empId = (int)($userEmp['id'] ?? 0);
            $myClass = Database::fetch(
                "SELECT * FROM classes WHERE (walas_employee_id = ? OR walas_id = ? OR walas_id = ?) AND academic_year_id = ? AND unit_id = ?",
                [$empId, $empId, $userId, $taId, $unitId]
            );
            if (!$myClass) {
                $this->setFlash('danger', 'Akses ditolak: Anda tidak memiliki rombel kelas aktif.');
                $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
                return;
            }
            $inClass = Database::fetch(
                "SELECT id FROM class_students WHERE student_id = ? AND class_id = ? AND academic_year_id = ?",
                [$studentId, $myClass['id'], $taId]
            );
            if (!$inClass) {
                $this->setFlash('danger', 'Akses ditolak: Santri ini bukan siswa di kelas yang Anda ampu.');
                $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
                return;
            }
        }

        $existing = Database::fetch("SELECT id FROM alumni_tracking WHERE student_id = ?", [$studentId]);
        if ($existing) {
            Database::execute(
                "UPDATE alumni_tracking SET destination_type = ?, destination_name = ?, is_favorite = ?, major = ?, notes = ?, graduation_year = ? WHERE id = ?",
                [$destType, $destName, $isFav, $major, $notes, $gradYear, $existing['id']]
            );
            AuditLog::log('UPDATE_REKOMENDASI_ALUMNI', 'alumni_tracking', $existing['id'], null, $_POST);
            $this->setFlash('success', "Rekomendasi studi lanjut untuk santri {$st['nama']} berhasil diperbarui.");
        } else {
            Database::execute(
                "INSERT INTO alumni_tracking (student_id, unit_id, graduation_year, destination_type, destination_name, is_favorite, major, notes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$studentId, $unitId, $gradYear, $destType, $destName, $isFav, $major, $notes]
            );
            AuditLog::log('CREATE_REKOMENDASI_ALUMNI', 'alumni_tracking', Database::lastInsertId(), null, $_POST);
            $this->setFlash('success', "Rekomendasi studi lanjut untuk santri {$st['nama']} berhasil disimpan.");
        }

        $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
    }

    public function walasRekomendasiDelete(): void {
        $this->requireRole(['walas', 'kepala_sekolah', 'operator_sekolah']);
        $trackingId = (int)($_POST['tracking_id'] ?? 0);
        $unitId = Auth::unitId();

        $rec = Database::fetch("SELECT at.*, s.nama FROM alumni_tracking at JOIN students s ON at.student_id = s.id WHERE at.id = ? AND at.unit_id = ?", [$trackingId, $unitId]);
        if ($rec) {
            Database::execute("DELETE FROM alumni_tracking WHERE id = ?", [$trackingId]);
            AuditLog::log('DELETE_REKOMENDASI_ALUMNI', 'alumni_tracking', $trackingId, $rec);
            $this->setFlash('success', "Rekomendasi santri {$rec['nama']} berhasil dihapus.");
        }
        $this->redirect(Auth::baseUrl() . '/walas/rekomendasi');
    }

    // --- 2. PENELUSURAN ALUMNI (FAVORIT & JURUSAN) ---
    public function alumniIndex(): void {
        $this->requireRole(['alumni', 'operator_sekolah', 'kepala_sekolah', 'deputi_pendidikan', 'operator_yayasan', 'walas']);
        $unitId = Auth::unitId();

        $alumniList = Database::fetchAll(
            "SELECT a.*, s.nama as student_name, s.nis, u.name as unit_name 
             FROM alumni_tracking a 
             JOIN students s ON a.student_id = s.id 
             JOIN units u ON a.unit_id = u.id 
             WHERE a.unit_id = ? 
             ORDER BY a.graduation_year DESC, s.nama ASC",
            [$unitId]
        );

        // Ambil hanya siswa yang telah berstatus Lulus, Pindah, atau Alumni
        $students = Database::fetchAll(
            "SELECT id, nis, nama, status, unit_id 
             FROM students 
             WHERE unit_id = ? AND status IN ('Lulus', 'Pindah', 'Alumni') 
             ORDER BY status ASC, nama ASC",
            [$unitId]
        );

        $favoriteCount = 0;
        foreach ($alumniList as $al) {
            if (!empty($al['is_favorite'])) $favoriteCount++;
        }

        $this->view('alumni/index', [
            'pageTitle'     => 'Penelusuran Jejak Alumni (Sekolah & Kampus Favorit)',
            'alumniList'    => $alumniList,
            'students'      => $students,
            'favoriteCount' => $favoriteCount
        ]);
    }

    public function alumniSave(): void {
        $this->requireRole(['alumni', 'operator_sekolah', 'operator_yayasan', 'walas', 'kepala_sekolah']);
        $unitId = Auth::unitId();

        $studentId = (int)($_POST['student_id'] ?? 0);
        $gradYear = $_POST['graduation_year'] ?? date('Y');
        $destType = $_POST['destination_type'] ?? 'Sekolah';
        $destName = trim($_POST['destination_name'] ?? '');
        $major = trim($_POST['major'] ?? '');
        $isFav = !empty($_POST['is_favorite']) ? 1 : 0;
        $notes = trim($_POST['notes'] ?? '');

        if ($studentId && !empty($destName)) {
            Database::execute(
                "INSERT INTO alumni_tracking (student_id, unit_id, graduation_year, destination_type, destination_name, is_favorite, major, notes) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$studentId, $unitId, $gradYear, $destType, $destName, $isFav, $major, $notes]
            );

            AuditLog::log('CREATE_ALUMNI_TRACKING', 'alumni_tracking', Database::lastInsertId(), null, ['student_id' => $studentId, 'dest' => $destName]);
            $this->setFlash('success', 'Data kelanjutan studi alumni berhasil dicatat.');
        }

        $this->redirect(Auth::baseUrl() . '/alumni');
    }
}

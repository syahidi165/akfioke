<?php
// app/Controllers/ProfileController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class ProfileController extends Controller {

    public function index(): void {
        $this->requireAuth();

        $userId = Auth::id();
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        $activeRole = Auth::activeRole();
        $roles = Auth::roles();

        // 1. Ambil data Pegawai terkait
        $employee = Database::fetch(
            "SELECT * FROM employees WHERE (user_id = ? OR LOWER(email) = LOWER(?) OR LOWER(nipy) = LOWER(?)) AND is_deleted = 0 LIMIT 1",
            [$userId, $user['email'] ?? '', $user['username'] ?? '']
        );

        // Satuan Pendidikan / Unit Kerja
        $isYayasan = Auth::hasAnyRole(['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'ketua_yayasan', 'pembina_yayasan'])
            || (!empty($employee['level_posisi']) && (stripos($employee['level_posisi'], 'Direktur') !== false || stripos($employee['level_posisi'], 'Yayasan') !== false || stripos($employee['level_posisi'], 'Deputi') !== false))
            || (!empty($employee['nipy']) && str_starts_with($employee['nipy'], 'YAY-'));

        $unitId = null;
        if (!$isYayasan) {
            $unitId = $employee['unit_id'] ?? ($user['unit_id'] ?? null);
        }
        $unit = $unitId ? Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]) : null;

        // 2. Data Penugasan Mengajar (Mapel & Kelas)
        $teachingAssignments = [];
        $waliKelasList = [];

        if ($employee) {
            $empId = (int)$employee['id'];

            // Query teacher_subjects
            $teachingAssignments = Database::fetchAll(
                "SELECT ts.*, s.name as subject_name, s.code as subject_code, s.type as subject_cat,
                        c.name as class_name, c.level as class_level,
                        ay.name as academic_year_name, ay.semester
                 FROM teacher_subjects ts
                 JOIN subjects s ON ts.subject_id = s.id
                 JOIN classes c ON ts.class_id = c.id
                 JOIN academic_years ay ON ts.academic_year_id = ay.id
                 WHERE ts.employee_id = ?
                 ORDER BY ay.id DESC, c.name ASC",
                [$empId]
            );

            // Query Wali Kelas
            $waliKelasList = Database::fetchAll(
                "SELECT c.*, ay.name as academic_year_name, ay.semester
                 FROM classes c
                 JOIN academic_years ay ON c.academic_year_id = ay.id
                 WHERE (c.walas_id = ? OR c.walas_employee_id = ?)
                 ORDER BY ay.id DESC, c.name ASC",
                [$empId, $empId]
            );
        }

        // 3. Aset Sekolah yang Sedang Digunakan (dari Sarpras)
        $holderQueries = [];
        $holderParams = [];

        if ($employee) {
            $holderQueries[] = "a.user_holder_id = ?";
            $holderParams[] = $employee['id'];
        }
        $holderQueries[] = "a.user_holder_id = ?";
        $holderParams[] = $userId;

        $nameToMatch = $employee['nama'] ?? ($user['full_name'] ?? '');
        if (!empty($nameToMatch)) {
            $holderQueries[] = "LOWER(a.user_holder_name) LIKE LOWER(?)";
            $holderParams[] = '%' . trim($nameToMatch) . '%';
        }

        $holderWhere = implode(' OR ', $holderQueries);
        $assignedAssets = Database::fetchAll(
            "SELECT a.*, c.name as category_name, u.name as unit_name, u.code as unit_code
             FROM assets a
             LEFT JOIN asset_categories c ON a.category_id = c.id
             LEFT JOIN units u ON a.unit_id = u.id
             WHERE ({$holderWhere}) AND a.status = 'Aktif'
             ORDER BY a.id DESC",
            $holderParams
        );

        $this->view('profile/index', [
            'pageTitle'           => 'Profil Pengguna - ' . ($employee['nama'] ?? $user['full_name']),
            'user'                => $user,
            'employee'            => $employee,
            'unit'                => $unit,
            'activeRole'          => $activeRole,
            'roles'               => $roles,
            'teachingAssignments' => $teachingAssignments,
            'waliKelasList'       => $waliKelasList,
            'assignedAssets'      => $assignedAssets
        ]);
    }

    public function update(): void {
        $this->requireAuth();
        $userId = Auth::id();

        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('danger', 'Format email tidak valid.');
            $this->redirect(Auth::baseUrl() . '/profile');
            return;
        }

        Database::execute(
            "UPDATE users SET phone = ?, email = ?, updated_at = NOW() WHERE id = ?",
            [$phone, $email, $userId]
        );

        $employee = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [$userId]);
        if ($employee) {
            Database::execute(
                "UPDATE employees SET contact_phone = ?, email = ?, updated_at = NOW() WHERE id = ?",
                [$phone, $email, $employee['id']]
            );
        }

        AuditLog::log('UPDATE_PROFILE', 'users', $userId, null, ['phone' => $phone, 'email' => $email]);
        $this->setFlash('success', 'Kontak profil berhasil diperbarui.');
        $this->redirect(Auth::baseUrl() . '/profile');
    }
}

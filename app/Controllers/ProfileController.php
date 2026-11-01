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

        // 1. Ambil data Pegawai terkait (Prioritaskan user_id, cegah fallback jika email atau username kosong)
        $employee = Database::fetch(
            "SELECT * FROM employees WHERE user_id = ? AND is_deleted = 0 LIMIT 1",
            [$userId]
        );

        if (!$employee) {
            $userEmail = trim((string)($user['email'] ?? ''));
            $username = trim((string)($user['username'] ?? ''));
            $conds = [];
            $params = [];

            if (!empty($userEmail)) {
                $conds[] = "(email IS NOT NULL AND email != '' AND LOWER(email) = LOWER(?))";
                $params[] = $userEmail;
            }

            if (!empty($username)) {
                $conds[] = "(nipy IS NOT NULL AND nipy != '' AND LOWER(nipy) = LOWER(?))";
                $params[] = $username;
            }

            if (!empty($conds)) {
                $employee = Database::fetch(
                    "SELECT * FROM employees WHERE (" . implode(' OR ', $conds) . ") AND is_deleted = 0 LIMIT 1",
                    $params
                );
            }
        }

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

        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        if (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->setFlash('danger', 'Format email tidak valid.');
            $this->redirect(Auth::baseUrl() . '/profile');
            return;
        }

        $userUpdates = ["phone = ?", "email = ?", "updated_at = NOW()"];
        $userParams = [$phone, $email];

        if (!empty($fullName)) {
            $userUpdates[] = "full_name = ?";
            $userParams[] = $fullName;
            $_SESSION['user_name'] = $fullName;
        }
        $userParams[] = $userId;

        Database::execute(
            "UPDATE users SET " . implode(', ', $userUpdates) . " WHERE id = ?",
            $userParams
        );

        $employee = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [$userId]);
        if ($employee) {
            $empUpdates = ["contact_phone = ?", "email = ?", "updated_at = NOW()"];
            $empParams = [$phone, $email];
            if (!empty($fullName)) {
                $empUpdates[] = "nama = ?";
                $empParams[] = $fullName;
            }
            $empParams[] = $employee['id'];
            Database::execute(
                "UPDATE employees SET " . implode(', ', $empUpdates) . " WHERE id = ?",
                $empParams
            );
        }

        AuditLog::log('UPDATE_PROFILE', 'users', $userId, null, ['full_name' => $fullName, 'phone' => $phone, 'email' => $email]);
        $this->setFlash('success', 'Data profil & kontak berhasil diperbarui.');
        $this->redirect(Auth::baseUrl() . '/profile');
    }

    public function uploadSignature(): void {
        $this->requireAuth();
        $userId = Auth::id();

        if (!isset($_FILES['signature']) || $_FILES['signature']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Berkas gambar tanda tangan tidak valid atau gagal diunggah.');
            $this->redirect(Auth::baseUrl() . '/profile');
            return;
        }

        $file = $_FILES['signature'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['png', 'jpg', 'jpeg', 'webp'];

        if (!in_array($ext, $allowedExts, true)) {
            $this->setFlash('danger', 'Hanya berkas gambar format PNG, JPG, JPEG, atau WEBP yang diperbolehkan.');
            $this->redirect(Auth::baseUrl() . '/profile');
            return;
        }

        if ($file['size'] > 3 * 1024 * 1024) {
            $this->setFlash('danger', 'Ukuran berkas tanda tangan maksimal 3 MB.');
            $this->redirect(Auth::baseUrl() . '/profile');
            return;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/signatures';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $filename = 'sig_user_' . $userId . '_' . time() . '.' . $ext;
        $destPath = $uploadDir . '/' . $filename;
        $dbPath = '/uploads/signatures/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $destPath)) {
            $this->setFlash('danger', 'Gagal memindahkan berkas tanda tangan ke server.');
            $this->redirect(Auth::baseUrl() . '/profile');
            return;
        }

        // Hapus file tanda tangan lama jika ada
        $oldUser = Database::fetch("SELECT signature_path FROM users WHERE id = ?", [$userId]);
        if (!empty($oldUser['signature_path'])) {
            $oldFilePath = __DIR__ . '/../../public' . $oldUser['signature_path'];
            if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                @unlink($oldFilePath);
            }
        }

        Database::execute("UPDATE users SET signature_path = ?, updated_at = NOW() WHERE id = ?", [$dbPath, $userId]);

        $employee = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [$userId]);
        if ($employee) {
            Database::execute("UPDATE employees SET signature_path = ?, updated_at = NOW() WHERE id = ?", [$dbPath, $employee['id']]);
        }

        AuditLog::log('UPLOAD_SIGNATURE', 'users', $userId, null, ['signature_path' => $dbPath]);
        $this->setFlash('success', 'Tanda tangan digital berhasil disimpan dan siap digunakan pada seluruh dokumen cetak resmi.');
        $this->redirect(Auth::baseUrl() . '/profile');
    }

    public function deleteSignature(): void {
        $this->requireAuth();
        $userId = Auth::id();

        $user = Database::fetch("SELECT signature_path FROM users WHERE id = ?", [$userId]);
        if (!empty($user['signature_path'])) {
            $oldFilePath = __DIR__ . '/../../public' . $user['signature_path'];
            if (file_exists($oldFilePath) && is_file($oldFilePath)) {
                @unlink($oldFilePath);
            }
        }

        Database::execute("UPDATE users SET signature_path = NULL, updated_at = NOW() WHERE id = ?", [$userId]);

        $employee = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0", [$userId]);
        if ($employee) {
            Database::execute("UPDATE employees SET signature_path = NULL, updated_at = NOW() WHERE id = ?", [$employee['id']]);
        }

        AuditLog::log('DELETE_SIGNATURE', 'users', $userId);
        $this->setFlash('success', 'Tanda tangan digital berhasil dihapus.');
        $this->redirect(Auth::baseUrl() . '/profile');
    }
}

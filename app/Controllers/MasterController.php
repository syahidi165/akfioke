<?php
// app/Controllers/MasterController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\ExportHelper;
use Core\SecurityHelper;
use Core\Uploader;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PHPMailer\PHPMailer\PHPMailer;
use Core\Setting;
use Core\PromotionHelper;

class MasterController extends Controller {

    // --- 1. UNIT & SEKOLAH ---
    public function units(): void {
        $this->requireRole('operator_yayasan');
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $this->view('master/units', [
            'pageTitle' => 'Kelola Yayasan & Unit Sekolah',
            'units'     => $units
        ]);
    }

    public function saveUnit(): void {
        $this->requireRole('operator_yayasan');
        $id = (int)($_POST['id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $level = trim($_POST['level'] ?? 'SD');
        $kopTitle = trim($_POST['kop_title'] ?? 'YAYASAN ISLAM AL KAHFI BATAM');
        $kopSubtitle = trim($_POST['kop_subtitle'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bankName = trim($_POST['bank_name'] ?? '');
        $bankAccount = trim($_POST['bank_account'] ?? '');
        $bankHolder = trim($_POST['bank_holder'] ?? '');
        $deleteLogo = !empty($_POST['delete_logo']);

        // Handle upload berkas logo jika ada
        $uploadedLogoPath = null;
        if (!empty($_FILES['logo']['tmp_name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['logo'];
            $maxSize = 2 * 1024 * 1024; // 2MB
            if ($file['size'] > $maxSize) {
                $this->setFlash('danger', 'Ukuran logo unit melebihi batas maksimal 2MB.');
                $this->redirect(Auth::baseUrl() . '/master/units');
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
            if (!in_array($ext, $allowedExts, true)) {
                $this->setFlash('danger', 'Format logo tidak diizinkan. Gunakan format PNG, JPG, JPEG, WEBP, atau SVG.');
                $this->redirect(Auth::baseUrl() . '/master/units');
                return;
            }

            $targetDir = dirname(__DIR__, 2) . '/public/uploads/logos';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $unitPrefix = $id > 0 ? $id : 'new_' . time();
            $filename = 'logo_unit_' . $unitPrefix . '_' . time() . '.' . $ext;
            $targetPath = $targetDir . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $uploadedLogoPath = 'uploads/logos/' . $filename;
            }
        }

        if ($id > 0) {
            $old = Database::fetch("SELECT * FROM units WHERE id = ?", [$id]);
            $finalLogoPath = $old['logo_path'] ?? null;

            if ($uploadedLogoPath !== null) {
                // Hapus file logo lama
                if (!empty($old['logo_path'])) {
                    $oldFile = dirname(__DIR__, 2) . '/public/' . ltrim($old['logo_path'], '/');
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $finalLogoPath = $uploadedLogoPath;
            } elseif ($deleteLogo) {
                if (!empty($old['logo_path'])) {
                    $oldFile = dirname(__DIR__, 2) . '/public/' . ltrim($old['logo_path'], '/');
                    if (is_file($oldFile)) {
                        @unlink($oldFile);
                    }
                }
                $finalLogoPath = null;
            }

            Database::execute(
                "UPDATE units SET code = ?, name = ?, level = ?, kop_title = ?, kop_subtitle = ?, address = ?, phone = ?, email = ?, bank_name = ?, bank_account = ?, bank_holder = ?, logo_path = ?, updated_at = NOW() WHERE id = ?",
                [$code, $name, $level, $kopTitle, $kopSubtitle, $address, $phone, $email, $bankName, $bankAccount, $bankHolder, $finalLogoPath, $id]
            );
            AuditLog::log('UPDATE_UNIT', 'units', $id, $old, $_POST);
            $this->setFlash('success', 'Data unit sekolah dan logo berhasil diperbarui.');
        } else {
            Database::execute(
                "INSERT INTO units (code, name, level, kop_title, kop_subtitle, address, phone, email, bank_name, bank_account, bank_holder, logo_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$code, $name, $level, $kopTitle, $kopSubtitle, $address, $phone, $email, $bankName, $bankAccount, $bankHolder, $uploadedLogoPath]
            );
            $newId = Database::lastInsertId();
            AuditLog::log('CREATE_UNIT', 'units', $newId, null, $_POST);
            $this->setFlash('success', 'Unit sekolah baru berhasil ditambahkan.');
        }
        $this->redirect(Auth::baseUrl() . '/master/units');
    }

    public function uploadUnitLogo(): void {
        $this->requireRole('operator_yayasan');
        $unitId = (int)($_POST['unit_id'] ?? 0);
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        if (!$unit) {
            $this->setFlash('danger', 'Unit tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/master/units');
            return;
        }

        if (!empty($_POST['delete_logo'])) {
            if (!empty($unit['logo_path'])) {
                $oldFile = dirname(__DIR__, 2) . '/public/' . ltrim($unit['logo_path'], '/');
                if (is_file($oldFile)) {
                    @unlink($oldFile);
                }
            }
            Database::execute("UPDATE units SET logo_path = NULL, updated_at = NOW() WHERE id = ?", [$unitId]);
            AuditLog::log('DELETE_UNIT_LOGO', 'units', $unitId, ['old_logo' => $unit['logo_path']], null);
            $this->setFlash('success', "Logo unit {$unit['name']} berhasil dihapus.");
            $this->redirect(Auth::baseUrl() . '/master/units');
            return;
        }

        if (empty($_FILES['logo']['tmp_name']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Silakan pilih berkas gambar logo.');
            $this->redirect(Auth::baseUrl() . '/master/units');
            return;
        }

        $file = $_FILES['logo'];
        $maxSize = 2 * 1024 * 1024; // 2MB
        if ($file['size'] > $maxSize) {
            $this->setFlash('danger', 'Ukuran logo melebihi batas maksimal 2MB.');
            $this->redirect(Auth::baseUrl() . '/master/units');
            return;
        }

        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'svg'];
        if (!in_array($ext, $allowedExts, true)) {
            $this->setFlash('danger', 'Format logo tidak diizinkan. Gunakan PNG, JPG, JPEG, WEBP, atau SVG.');
            $this->redirect(Auth::baseUrl() . '/master/units');
            return;
        }

        $targetDir = dirname(__DIR__, 2) . '/public/uploads/logos';
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

        $filename = 'logo_unit_' . $unitId . '_' . time() . '.' . $ext;
        $targetPath = $targetDir . '/' . $filename;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            $this->setFlash('danger', 'Gagal menyimpan berkas logo ke server.');
            $this->redirect(Auth::baseUrl() . '/master/units');
            return;
        }

        if (!empty($unit['logo_path'])) {
            $oldFile = dirname(__DIR__, 2) . '/public/' . ltrim($unit['logo_path'], '/');
            if (is_file($oldFile)) {
                @unlink($oldFile);
            }
        }

        $relativePath = 'uploads/logos/' . $filename;
        Database::execute("UPDATE units SET logo_path = ?, updated_at = NOW() WHERE id = ?", [$relativePath, $unitId]);
        AuditLog::log('UPLOAD_UNIT_LOGO', 'units', $unitId, ['old_logo' => $unit['logo_path']], ['new_logo' => $relativePath]);

        $this->setFlash('success', "Logo resmi untuk unit {$unit['name']} berhasil diperbarui.");
        $this->redirect(Auth::baseUrl() . '/master/units');
    }

    // --- 2. TAHUN AJARAN & PERSETUJUAN PINDAH TA ---
    public function academicYears(): void {
        $this->requireRole(['operator_yayasan', 'operator_sekolah']);
        $taList = Database::fetchAll("SELECT * FROM academic_years ORDER BY id DESC");
        $userUnitId = Auth::unitId();
        
        $approvals = Database::fetchAll(
            "SELECT a.*, u.name as unit_name, ta.name as ta_name, ta.semester 
             FROM academic_year_approvals a 
             JOIN units u ON a.unit_id = u.id 
             JOIN academic_years ta ON a.academic_year_id = ta.id 
             ORDER BY a.academic_year_id DESC, a.unit_id ASC"
        );

        $myPendingApproval = Database::fetch(
            "SELECT a.*, u.name as unit_name, ta.name as ta_name, ta.semester 
             FROM academic_year_approvals a 
             JOIN units u ON a.unit_id = u.id 
             JOIN academic_years ta ON a.academic_year_id = ta.id 
             WHERE a.unit_id = ? AND a.status = 'Menunggu' AND ta.is_active = 0 
             ORDER BY a.academic_year_id DESC LIMIT 1",
            [$userUnitId]
        );

        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");

        $storageOverview = \App\Services\AcademicYearArchiveService::getStorageOverview();

        $this->view('master/academic_years', [
            'pageTitle'         => 'Tahun Ajaran & Status Persetujuan Unit',
            'taList'            => $taList,
            'approvals'         => $approvals,
            'userUnitId'        => $userUnitId,
            'myPendingApproval' => $myPendingApproval,
            'units'             => $units,
            'storageOverview'   => $storageOverview,
        ]);
    }

    public function saveAcademicYear(): void {
        $this->requireRole('operator_yayasan');
        $name = trim($_POST['name'] ?? '');
        $semester = trim($_POST['semester'] ?? 'Ganjil');

        if (!empty($name)) {
            Database::execute("INSERT INTO academic_years (name, semester, is_active) VALUES (?, ?, 0)", [$name, $semester]);
            $newId = Database::lastInsertId();
            
            // Buat slot approval untuk seluruh 5 unit sekolah
            $units = Database::fetchAll("SELECT id FROM units");
            foreach ($units as $u) {
                Database::execute(
                    "INSERT INTO academic_year_approvals (academic_year_id, unit_id, status) VALUES (?, ?, 'Menunggu')",
                    [$newId, $u['id']]
                );
            }
            AuditLog::log('CREATE_ACADEMIC_YEAR', 'academic_years', $newId, null, ['name' => $name, 'semester' => $semester]);
            $this->setFlash('success', 'Tahun ajaran baru diajukan. Menunggu persetujuan seluruh operator unit.');
        }
        $this->redirect(Auth::baseUrl() . '/master/academic-years');
    }

    public function switchActiveYear(): void {
        $this->requireRole('operator_yayasan');
        $targetTaId = (int)($_POST['target_ta_id'] ?? 0);

        // Periksa apakah SELURUH operator sekolah telah menyetujui
        $pendingOrRejected = Database::fetchAll(
            "SELECT a.*, u.name as unit_name FROM academic_year_approvals a 
             JOIN units u ON a.unit_id = u.id 
             WHERE a.academic_year_id = ? AND a.status != 'Setuju'",
            [$targetTaId]
        );

        if (!empty($pendingOrRejected)) {
            $unitNames = array_column($pendingOrRejected, 'unit_name');
            $this->setFlash('error', 'Gagal pindah tahun ajaran! Masih ada unit yang belum menyetujui: ' . implode(', ', $unitNames));
            $this->redirect(Auth::baseUrl() . '/master/academic-years');
            return;
        }

        $previousTa = Database::fetch("SELECT * FROM academic_years WHERE is_active = 1");
        $targetTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$targetTaId]);

        // Checklist Kesiapan & Nonaktifkan TA lama, aktifkan TA baru
        Database::execute("UPDATE academic_years SET is_active = 0");
        Database::execute("UPDATE academic_years SET is_active = 1 WHERE id = ?", [$targetTaId]);

        // Ketika tahun ajaran baru terbentuk/diaktifkan, seluruh siswa dikeluarkan dari kelas dan halaqah
        // agar operator sekolah dapat melakukan penempatan siswa baru (fresh)
        Database::execute("DELETE FROM class_students WHERE academic_year_id = ?", [$targetTaId]);
        Database::execute("DELETE thm FROM tahfidz_halaqah_members thm 
                           JOIN tahfidz_halaqah th ON thm.halaqah_id = th.id 
                           WHERE th.academic_year_id = ?", [$targetTaId]);

        AuditLog::log('SWITCH_ACTIVE_YEAR', 'academic_years', $targetTaId);
        $this->setFlash('success', 'Tahun ajaran aktif berhasil diganti! Seluruh unit telah menyetujui. Seluruh siswa telah disiapkan fresh (dikeluarkan dari kelas & halaqah pada tahun ajaran baru).');
        $this->redirect(Auth::baseUrl() . '/master/academic-years');
    }

    public function approveAcademicYear(): void {
        $this->requireRole('operator_sekolah');
        $approvalId = (int)($_POST['approval_id'] ?? 0);
        $status = $_POST['status'] ?? 'Setuju'; // Setuju / Tidak Setuju
        $reason = trim($_POST['rejection_reason'] ?? '');
        $userId = Auth::id();
        $unitId = Auth::unitId();

        $app = Database::fetch("SELECT * FROM academic_year_approvals WHERE id = ? AND unit_id = ?", [$approvalId, $unitId]);
        if (!$app) {
            $this->setFlash('error', 'Data persetujuan tidak valid untuk unit Anda.');
            $this->redirect(Auth::baseUrl() . '/master/academic-years');
            return;
        }

        if ($status === 'Tidak Setuju' && empty($reason)) {
            $this->setFlash('error', 'Wajib menyertakan alasan jika menolak pengajuan pindah tahun ajaran.');
            $this->redirect(Auth::baseUrl() . '/master/academic-years');
            return;
        }

        Database::execute(
            "UPDATE academic_year_approvals SET status = ?, rejection_reason = ?, decided_by_user_id = ?, updated_at = NOW() WHERE id = ?",
            [$status, $reason, $userId, $approvalId]
        );

        AuditLog::log('APPROVE_ACADEMIC_YEAR', 'academic_year_approvals', $approvalId, $app, ['status' => $status, 'reason' => $reason]);
        $this->setFlash('success', "Keputusan persetujuan berhasil disimpan ({$status}).");
        $this->redirect(Auth::baseUrl() . '/master/academic-years');
    }

    // --- 2.1 HOT & COLD NAS STORAGE ARCHIVING (KHUSUS OPERATOR YAYASAN) ---
    public function archiveTaToNas(): void {
        $this->requireRole('operator_yayasan');
        $taId = (int)($_POST['ta_id'] ?? 0);
        $userId = Auth::id();

        $res = \App\Services\AcademicYearArchiveService::archiveAcademicYear($taId, $userId);
        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('error', $res['message']);
        }
        $this->redirect(Auth::baseUrl() . '/master/academic-years');
    }

    public function rehydrateTaFromNas(): void {
        $this->requireRole('operator_yayasan');
        $taId = (int)($_POST['ta_id'] ?? 0);
        $userId = Auth::id();

        $res = \App\Services\AcademicYearArchiveService::rehydrateAcademicYear($taId, $userId);
        if ($res['success']) {
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('error', $res['message']);
        }
        $this->redirect(Auth::baseUrl() . '/master/academic-years');
    }

    public function storageTransferInit(): void {
        $this->requireRole('operator_yayasan');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $taId = (int)($input['ta_id'] ?? 0);
        $direction = ($input['direction'] ?? 'to_cold') === 'to_hot' ? 'to_hot' : 'to_cold';

        $res = \App\Services\AcademicYearArchiveService::prepareTransfer($taId, $direction);
        $this->json($res);
    }

    public function storageTransferStep(): void {
        $this->requireRole('operator_yayasan');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $taId = (int)($input['ta_id'] ?? 0);
        $direction = ($input['direction'] ?? 'to_cold') === 'to_hot' ? 'to_hot' : 'to_cold';
        $fileItem = $input['file'] ?? [];

        $res = \App\Services\AcademicYearArchiveService::transferSingleFile($taId, $direction, $fileItem);
        $this->json($res);
    }

    public function storageTransferFinalize(): void {
        $this->requireRole('operator_yayasan');
        $input = json_decode(file_get_contents('php://input'), true) ?? $_POST;
        $taId = (int)($input['ta_id'] ?? 0);
        $direction = ($input['direction'] ?? 'to_cold') === 'to_hot' ? 'to_hot' : 'to_cold';
        $stats = $input['stats'] ?? [];
        $userId = Auth::id();

        $res = \App\Services\AcademicYearArchiveService::finalizeTransfer($taId, $direction, $userId, $stats);
        $this->json($res);
    }

    public function testNasConnectionAjax(): void {
        $this->requireRole('operator_yayasan');
        $test = \Core\Storage\StorageManager::testNasConnection();
        $this->json($test);
    }

    public function saveNasConfig(): void {
        $this->requireRole('operator_yayasan');
        
        $driver = trim($_POST['storage_cold_driver'] ?? 'local_path');
        $localPath = trim($_POST['storage_cold_local_path'] ?? '');
        $webdavUrl = trim($_POST['storage_cold_webdav_url'] ?? '');
        $webdavPath = trim($_POST['storage_cold_webdav_path'] ?? '');
        $webdavUser = trim($_POST['storage_cold_webdav_user'] ?? '');
        $webdavPass = trim($_POST['storage_cold_webdav_pass'] ?? '');
        $enabled = !empty($_POST['storage_cold_enabled']) ? 'true' : 'false';

        \Core\Setting::set('storage_cold_enabled', $enabled);
        \Core\Setting::set('storage_cold_driver', $driver);
        if ($localPath !== '') {
            \Core\Setting::set('storage_cold_local_path', $localPath);
        }
        \Core\Setting::set('storage_cold_webdav_url', $webdavUrl);
        \Core\Setting::set('storage_cold_webdav_path', $webdavPath);
        \Core\Setting::set('storage_cold_webdav_user', $webdavUser);
        if ($webdavPass !== '') {
            \Core\Setting::set('storage_cold_webdav_pass', $webdavPass);
        }

        \Core\Storage\StorageManager::reload();

        \Core\AuditLog::log('UPDATE_NAS_STORAGE_CONFIG', 'app_settings', 0, null, [
            'driver' => $driver,
            'enabled' => $enabled,
            'local_path' => $localPath
        ]);

        $this->setFlash('success', 'Konfigurasi Cold Storage NAS berhasil disimpan dan diperbarui.');
        $this->redirect(Auth::baseUrl() . '/master/academic-years');
    }

    public function downloadTaDbSnapshot(): void {
        $this->requireRole('operator_yayasan');
        $taId = (int)($_GET['id'] ?? 0);
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        if (!$ta || empty($ta['archive_meta'])) {
            $this->setFlash('error', 'Snapshot database arsip tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/master/academic-years');
            return;
        }

        $meta = json_decode($ta['archive_meta'], true);
        $relPath = $meta['db_snapshot'] ?? null;
        if (!$relPath) {
            $this->setFlash('error', 'Berkas snapshot belum tercatat untuk tahun ajaran ini.');
            $this->redirect(Auth::baseUrl() . '/master/academic-years');
            return;
        }

        $safeName = 'snapshot_ta_' . $taId . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $ta['name']) . '.sql';
        \Core\Storage\StorageManager::stream($relPath, $safeName, true);
    }

    // --- 3. PEGAWAI (GURU & KARYAWAN) & TURNOVER ---
    public function employees(): void {
        $this->requireRole(['operator_yayasan', 'operator_sekolah', 'direktur_pendidikan']);
        $unitFilter = (int)($_GET['unit_id'] ?? (Auth::hasRole('operator_sekolah') ? Auth::unitId() : 0));
        
        $sql = "SELECT e.*, u.name as unit_name, sup.nama as atasan_nama,
                       usr.id as account_user_id, usr.username, usr.is_active as user_is_active, usr.last_login_at,
                       GROUP_CONCAT(DISTINCT r.display_name ORDER BY ur.is_primary DESC, r.name ASC SEPARATOR ', ') as role_names,
                       GROUP_CONCAT(DISTINCT CONCAT(r.id, ':', r.name, ':', r.display_name) SEPARATOR '; ') as role_data
                FROM employees e 
                LEFT JOIN units u ON e.unit_id = u.id 
                LEFT JOIN employees sup ON e.atasan_langsung_id = sup.id 
                LEFT JOIN users usr ON e.user_id = usr.id 
                LEFT JOIN user_roles ur ON usr.id = ur.user_id 
                LEFT JOIN roles r ON ur.role_id = r.id 
                WHERE e.is_deleted = 0";
        $params = [];
        if ($unitFilter > 0) {
            $sql .= " AND e.unit_id = ?";
            $params[] = $unitFilter;
        } elseif ($unitFilter === -1) {
            $sql .= " AND (e.unit_id IS NULL OR e.unit_id = 0)";
        }
        $sql .= " GROUP BY e.id ORDER BY e.unit_id ASC, e.nama ASC";

        $employees = Database::fetchAll($sql, $params);
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $superiors = Database::fetchAll("SELECT id, nama, level_posisi FROM employees WHERE is_deleted = 0 ORDER BY nama ASC");
        $roles = Database::fetchAll("SELECT * FROM roles WHERE name NOT IN ('operator_yayasan', 'direktur_pendidikan', 'pembina_yayasan') ORDER BY display_name ASC");

        $this->view('master/employees', [
            'pageTitle'  => 'Data Master Guru, Karyawan & Akun Pengguna Yayasan',
            'employees'  => $employees,
            'units'      => $units,
            'superiors'  => $superiors,
            'roles'      => $roles,
            'unitFilter' => $unitFilter
        ]);
    }

    public function saveEmployee(): void {
        $this->requireRole('operator_yayasan');
        $id = (int)($_POST['id'] ?? 0);
        $nipy = trim($_POST['nipy'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $gender = $_POST['gender'] ?? 'L';
        $tingkatPendidikan = trim($_POST['tingkat_pendidikan'] ?? 'S1');
        $birthplace = trim($_POST['birthplace'] ?? '');
        $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
        $tmt = !empty($_POST['tmt']) ? $_POST['tmt'] : date('Y-m-d');
        $levelPosisi = trim($_POST['level_posisi'] ?? 'Guru Mapel');
        $unitId = !empty($_POST['unit_id']) ? (int)$_POST['unit_id'] : null;
        $atasanId = !empty($_POST['atasan_langsung_id']) ? (int)$_POST['atasan_langsung_id'] : null;
        $noTelpAtasan = trim($_POST['no_telp_atasan'] ?? '');
        $contactPhone = trim($_POST['contact_phone'] ?? '');
        $email = trim($_POST['email'] ?? '');

        // Auto-generate username dari NIPY (huruf kecil) untuk menjamin keunikan akun tanpa duplikasi nama
        $generatedUsername = !empty($nipy) ? strtolower(trim($nipy)) : '';
        $username = $generatedUsername ?: trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $userIsActive = isset($_POST['user_is_active']) ? (int)$_POST['user_is_active'] : 1;
        $selectedRoles = $_POST['roles'] ?? [];

        if ($id > 0) {
            $old = $this->assertUnitOwnership('employees', $id);
            $oldUnitId = (int)($old['unit_id'] ?? 0);

            // Deteksi jika unit sekolah dipindahkan secara manual oleh operator yayasan
            if ($oldUnitId > 0 && $oldUnitId !== $unitId) {
                // Otomatis bersihkan jadwal supervisi, mapel pengampu, dan walas kelas di unit lama
                $this->cleanupEmployeeFormerUnitData($id, $oldUnitId);

                // Update unit_id akun user ke sekolah baru dan reset role unit lama jika role tidak dipilih ulang
                $oldUserId = (int)($old['user_id'] ?? 0);
                if ($oldUserId > 0 && empty($selectedRoles)) {
                    Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$oldUserId]);
                }

                // Pindahkan draft AKFI berjalan ke unit baru
                Database::execute(
                    "UPDATE akfi_assessments SET unit_id = ? WHERE employee_id = ? AND status IN ('Draft', 'Diajukan')",
                    [$unitId, $id]
                );
            }

            Database::execute(
                "UPDATE employees SET nipy = ?, nik = ?, nama = ?, gender = ?, tingkat_pendidikan = ?, birthplace = ?, birthdate = ?, tmt = ?, level_posisi = ?, unit_id = ?, atasan_langsung_id = ?, no_telp_atasan = ?, contact_phone = ?, email = ?, updated_at = NOW() WHERE id = ?",
                [$nipy, $nik, $nama, $gender, $tingkatPendidikan, $birthplace, $birthdate, $tmt, $levelPosisi, $unitId, $atasanId, $noTelpAtasan, $contactPhone, $email, $id]
            );

            // Jika ada akun terkait atau username diisi
            $userId = (int)($old['user_id'] ?? 0);
            if ($userId > 0) {
                // Update akun user yang sudah ada (username disinkronkan dengan NIPY)
                if (!empty($username)) {
                    $uCheck = Database::fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $userId]);
                    if ($uCheck) {
                        $this->setFlash('warning', "Data pegawai diperbarui, namun username '{$username}' sudah dipakai akun lain.");
                        $this->redirect(Auth::baseUrl() . '/master/employees');
                        return;
                    }
                }

                $passSql = "";
                $userParams = [$nama, $email ?: null, $contactPhone ?: null, $unitId, $userIsActive];
                if (!empty($username)) {
                    $passSql .= ", username = ?";
                    $userParams[] = $username;
                }
                if (!empty($password)) {
                    $passSql .= ", password_hash = ?";
                    $userParams[] = password_hash($password, PASSWORD_DEFAULT);
                }
                $userParams[] = $userId;

                Database::execute(
                    "UPDATE users SET full_name = ?, email = ?, phone = ?, unit_id = ?, is_active = ? {$passSql}, updated_at = NOW() WHERE id = ?",
                    $userParams
                );

                // Update roles jika ada yang dipilih
                if (!empty($selectedRoles) && is_array($selectedRoles)) {
                    Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$userId]);
                    foreach ($selectedRoles as $idx => $rId) {
                        $isPri = $idx === 0 ? 1 : 0;
                        Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, ?)", [$userId, (int)$rId, $isPri]);
                    }
                }
            } elseif (!empty($username)) {
                // Pegawai belum punya akun, buatkan akun baru otomatis dari NIPY
                $uCheck = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
                if ($uCheck) {
                    $this->setFlash('warning', "Pegawai diperbarui, namun username '{$username}' sudah digunakan.");
                    $this->redirect(Auth::baseUrl() . '/master/employees');
                    return;
                }

                $pwd = !empty($password) ? $password : 'password123';
                $passHash = password_hash($pwd, PASSWORD_DEFAULT);
                Database::execute(
                    "INSERT INTO users (username, password_hash, full_name, email, phone, unit_id, is_active, force_change_password) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 0)",
                    [$username, $passHash, $nama, $email ?: null, $contactPhone ?: null, $unitId, $userIsActive]
                );
                $newUserId = (int)Database::lastInsertId();
                Database::execute("UPDATE employees SET user_id = ? WHERE id = ?", [$newUserId, $id]);

                // Assign roles
                if (!empty($selectedRoles) && is_array($selectedRoles)) {
                    foreach ($selectedRoles as $idx => $rId) {
                        $isPri = $idx === 0 ? 1 : 0;
                        Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, ?)", [$newUserId, (int)$rId, $isPri]);
                    }
                }
            }

            AuditLog::log('UPDATE_EMPLOYEE', 'employees', $id, $old, $_POST);
            $this->setFlash('success', 'Data pegawai dan akun login berhasil diperbarui.');
        } else {
            // Pegawai baru - Akun login otomatis dibuat dari NIPY
            $newUserId = null;
            if (!empty($username)) {
                $uCheck = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
                if ($uCheck) {
                    $this->setFlash('danger', "Username auto-generated '{$username}' sudah digunakan akun lain. Mohon periksa kembali NIPY.");
                    $this->redirect(Auth::baseUrl() . '/master/employees');
                    return;
                }

                $pwd = !empty($password) ? $password : 'password123';
                $passHash = password_hash($pwd, PASSWORD_DEFAULT);
                Database::execute(
                    "INSERT INTO users (username, password_hash, full_name, email, phone, unit_id, is_active, force_change_password) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, 0)",
                    [$username, $passHash, $nama, $email ?: null, $contactPhone ?: null, $unitId, $userIsActive]
                );
                $newUserId = (int)Database::lastInsertId();

                if (!empty($selectedRoles) && is_array($selectedRoles)) {
                    foreach ($selectedRoles as $idx => $rId) {
                        $isPri = $idx === 0 ? 1 : 0;
                        Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, ?)", [$newUserId, (int)$rId, $isPri]);
                    }
                }
            }

            Database::execute(
                "INSERT INTO employees (user_id, nipy, nik, nama, gender, tingkat_pendidikan, birthplace, birthdate, tmt, level_posisi, unit_id, atasan_langsung_id, no_telp_atasan, contact_phone, email, status) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')",
                [$newUserId, $nipy, $nik, $nama, $gender, $tingkatPendidikan, $birthplace, $birthdate, $tmt, $levelPosisi, $unitId, $atasanId, $noTelpAtasan, $contactPhone, $email]
            );
            $newId = Database::lastInsertId();
            AuditLog::log('CREATE_EMPLOYEE', 'employees', $newId, null, $_POST);
            $this->setFlash('success', 'Pegawai baru beserta akun auto-generated dari NIPY berhasil ditambahkan.');
        }
        $this->redirect(Auth::baseUrl() . '/master/employees');
    }

    public function deleteEmployee(): void {
        $this->requireRole('operator_yayasan');
        $id = (int)($_POST['id'] ?? 0);
        // Soft delete agar histori AKFI dan turnover tetap utuh
        $old = Database::fetch("SELECT * FROM employees WHERE id = ?", [$id]);
        if ($old) {
            Database::execute("UPDATE employees SET is_deleted = 1, status = 'Nonaktif' WHERE id = ?", [$id]);
            // Nonaktifkan juga user login terkait
            if (!empty($old['user_id'])) {
                Database::execute("UPDATE users SET is_active = 0 WHERE id = ?", [$old['user_id']]);
                Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$old['user_id']]);
            }
            // Bersihkan penugasan walas, jadwal supervisi, dan mapel
            $this->cleanupEmployeeFormerUnitData($id, null);

            AuditLog::log('SOFT_DELETE_EMPLOYEE', 'employees', $id, $old);
            $this->setFlash('success', 'Pegawai berhasil dinonaktifkan dan penugasan kelas/jadwal telah dibersihkan.');
        }
        $this->redirect(Auth::baseUrl() . '/master/employees');
    }

    public function exportEmployees(): void {
        $this->requireRole(['operator_yayasan', 'operator_sekolah']);
        $unitFilter = (int)($_GET['unit_id'] ?? 0);
        $sql = "SELECT e.nipy, e.nik, e.nama, e.gender, e.tmt, e.level_posisi, u.name as unit_name, sup.nama as atasan_nama, e.contact_phone, e.email 
                FROM employees e 
                JOIN units u ON e.unit_id = u.id 
                LEFT JOIN employees sup ON e.atasan_langsung_id = sup.id 
                WHERE e.is_deleted = 0";
        $params = [];
        if ($unitFilter > 0) {
            $sql .= " AND e.unit_id = ?";
            $params[] = $unitFilter;
        }
        $sql .= " ORDER BY e.unit_id ASC, e.nama ASC";

        $rows = Database::fetchAll($sql, $params);
        $headers = ['NIPY', 'NIK', 'Nama Lengkap', 'JK', 'TMT', 'Level/Posisi', 'Unit Tugas', 'Atasan Langsung', 'No. HP', 'Email'];
        ExportHelper::excel('Data_Pegawai_AlKahfi', $headers, $rows, 'DAFTAR GURU & KARYAWAN YAYASAN ISLAM AL KAHFI BATAM');
    }

    public function templateEmployees(): void {
        $this->requireRole(['operator_yayasan', 'operator_sekolah']);
        $isYayasan = Auth::hasRole('operator_yayasan');
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Pegawai');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Lengkap *',
            'C1' => 'NIPY (Opsional)',
            'D1' => 'NIK (Opsional)',
            'E1' => 'Jenis Kelamin (L/P) *',
            'F1' => 'Posisi / Jabatan',
            'G1' => 'Peran Utama (Role) *',
            'H1' => 'Username Login *',
            'I1' => 'Password Awal (Default: password123)',
            'J1' => 'No. WhatsApp / HP',
            'K1' => 'Email'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style Headers
        $sheet->getStyle('A1:K1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E3A8A']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Fetch valid roles
        if ($isYayasan) {
            $roles = Database::fetchAll("SELECT id, name, display_name FROM roles WHERE name NOT IN ('operator_yayasan', 'direktur_pendidikan', 'pembina_yayasan') ORDER BY display_name ASC");
        } else {
            $roles = Database::fetchAll("SELECT id, name, display_name FROM roles WHERE name NOT IN ('operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan', 'operator_sekolah') ORDER BY display_name ASC");
        }

        // Create hidden reference sheet for roles to prevent typos
        $refSheet = $spreadsheet->createSheet();
        $refSheet->setTitle('_RefRoles');
        foreach ($roles as $idx => $r) {
            $rowNum = $idx + 1;
            $refSheet->setCellValue("A{$rowNum}", $r['display_name']);
        }
        $refSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        // Sample rows
        $sampleRole1 = $roles[0]['display_name'] ?? 'Guru Mapel';
        $sampleRole2 = isset($roles[1]) ? $roles[1]['display_name'] : $sampleRole1;
        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'Ahmad Fauzi, S.Pd');
        $sheet->setCellValue('C2', '');
        $sheet->setCellValue('D2', '');
        $sheet->setCellValue('E2', 'L');
        $sheet->setCellValue('F2', 'Guru Mapel');
        $sheet->setCellValue('G2', $sampleRole1);
        $sheet->setCellValue('H2', 'fauzi.guru');
        $sheet->setCellValue('I2', 'password123');
        $sheet->setCellValue('J2', '081234567890');
        $sheet->setCellValue('K2', 'fauzi@alkahfi.sch.id');

        $sheet->setCellValue('A3', 2);
        $sheet->setCellValue('B3', 'Siti Rahmah, S.Pd');
        $sheet->setCellValue('C3', '');
        $sheet->setCellValue('D3', '');
        $sheet->setCellValue('E3', 'P');
        $sheet->setCellValue('F3', 'Guru Wali Kelas');
        $sheet->setCellValue('G3', $sampleRole2);
        $sheet->setCellValue('H3', 'siti.guru');
        $sheet->setCellValue('I3', 'password123');
        $sheet->setCellValue('J3', '081298765432');
        $sheet->setCellValue('K3', 'siti@alkahfi.sch.id');

        // Setup Data Validation for Gender (Column E)
        $valGender = $sheet->getDataValidation('E2:E200');
        $valGender->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $valGender->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $valGender->setAllowBlank(false);
        $valGender->setShowDropDown(true);
        $valGender->setShowErrorMessage(true);
        $valGender->setErrorTitle('Jenis Kelamin Tidak Valid');
        $valGender->setError('Pilih L (Laki-laki) atau P (Perempuan).');
        $valGender->setFormula1('"L,P"');

        // Setup Data Validation for Role (Column G)
        $roleCount = count($roles);
        if ($roleCount > 0) {
            $valRole = $sheet->getDataValidation('G2:G200');
            $valRole->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $valRole->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $valRole->setAllowBlank(false);
            $valRole->setShowDropDown(true);
            $valRole->setShowErrorMessage(true);
            $valRole->setErrorTitle('Peran Tidak Valid');
            $valRole->setError('Silakan pilih salah satu peran dari daftar dropdown.');
            $valRole->setFormula1("_RefRoles!\$A\$1:\$A\${$roleCount}");
        }

        foreach (range('A', 'K') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Template_Import_Pegawai_AlKahfi.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importEmployees(): void {
        $this->requireRole(['operator_yayasan', 'operator_sekolah']);
        $isYayasan = Auth::hasRole('operator_yayasan');

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Berkas Excel tidak valid atau gagal diunggah.');
            $this->redirect(Auth::baseUrl() . ($isYayasan ? '/master/employees' : '/users/school-accounts'));
            return;
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->setFlash('danger', 'Hanya berkas Excel dengan ekstensi .xlsx atau .xls yang diperbolehkan.');
            $this->redirect(Auth::baseUrl() . ($isYayasan ? '/master/employees' : '/users/school-accounts'));
            return;
        }

        if ($isYayasan) {
            $targetUnitId = (int)($_POST['unit_id'] ?? 0);
            if ($targetUnitId <= 0) {
                $targetUnitId = 3; // Default SD
            }
        } else {
            $targetUnitId = Auth::unitId();
        }

        $unitRow = Database::fetch("SELECT code FROM units WHERE id = ?", [$targetUnitId]);
        $uCode = $unitRow['code'] ?? 'SCH';

        $rolesRaw = Database::fetchAll("SELECT id, name, display_name FROM roles");
        $roleMap = [];
        $forbiddenRoles = $isYayasan 
            ? ['operator_yayasan', 'direktur_pendidikan', 'pembina_yayasan'] 
            : ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan', 'operator_sekolah'];

        foreach ($rolesRaw as $r) {
            if (!in_array($r['name'], $forbiddenRoles)) {
                $roleMap[strtolower(trim($r['display_name']))] = (int)$r['id'];
                $roleMap[strtolower(trim($r['name']))] = (int)$r['id'];
            }
        }

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            $this->redirect(Auth::baseUrl() . ($isYayasan ? '/master/employees' : '/users/school-accounts'));
            return;
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $nama = trim((string)$sheet->getCell("B{$row}")->getValue());
            if (empty($nama)) continue;

            $nipy = trim((string)$sheet->getCell("C{$row}")->getValue());
            $nik = trim((string)$sheet->getCell("D{$row}")->getValue());
            $gender = strtoupper(trim((string)$sheet->getCell("E{$row}")->getValue()));
            if (!in_array($gender, ['L', 'P'])) $gender = 'L';

            $posisi = trim((string)$sheet->getCell("F{$row}")->getValue());
            if (empty($posisi)) $posisi = 'Guru Mapel';

            $roleText = strtolower(trim((string)$sheet->getCell("G{$row}")->getValue()));
            $username = trim((string)$sheet->getCell("H{$row}")->getValue());
            $password = trim((string)$sheet->getCell("I{$row}")->getValue());
            if (empty($password)) $password = 'password123';

            $phone = trim((string)$sheet->getCell("J{$row}")->getValue());
            $email = trim((string)$sheet->getCell("K{$row}")->getValue());

            $roleId = $roleMap[$roleText] ?? null;
            if (!$roleId) {
                $roleId = $roleMap['guru mapel'] ?? ($roleMap['guru_mapel'] ?? 1);
            }

            if (empty($username)) {
                $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', substr($nama, 0, 8))) . '.' . strtolower($uCode);
            }

            $uCheck = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
            if ($uCheck) {
                $skipped++;
                $errors[] = "Baris {$row} ({$nama}): Username '{$username}' sudah digunakan.";
                continue;
            }

            $passHash = password_hash($password, PASSWORD_DEFAULT);
            Database::execute(
                "INSERT INTO users (username, password_hash, full_name, email, phone, unit_id, is_active, force_change_password)
                 VALUES (?, ?, ?, ?, ?, ?, 1, 0)",
                [$username, $passHash, $nama, $email ?: null, $phone ?: null, $targetUnitId]
            );
            $newUserId = (int)Database::lastInsertId();

            Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 1)", [$newUserId, $roleId]);

            if (empty($nipy)) {
                $cleanUser = strtoupper(preg_replace('/[^a-zA-Z]/', '', $username));
                $nipy = $uCode . '-' . substr($cleanUser, 0, 4) . '-' . rand(100, 999);
            }

            Database::execute(
                "INSERT INTO employees (user_id, nipy, nik, nama, gender, tmt, level_posisi, unit_id, contact_phone, email, status)
                 VALUES (?, ?, ?, ?, ?, CURDATE(), ?, ?, ?, ?, 'Aktif')",
                [$newUserId, $nipy, $nik ?: null, $nama, $gender, $posisi, $targetUnitId, $phone ?: null, $email ?: null]
            );
            $newEmpId = Database::lastInsertId();

            AuditLog::log('IMPORT_EMPLOYEE', 'employees', $newEmpId, null, [
                'username' => $username, 'unit_id' => $targetUnitId, 'role_id' => $roleId
            ]);

            $imported++;
        }

        if ($imported > 0) {
            $msg = "Berhasil mengimpor {$imported} data pegawai dan akun baru.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} baris dilewati karena duplikasi/tidak valid).";
            }
            $this->setFlash('success', $msg);
        } else {
            $this->setFlash('warning', 'Tidak ada data pegawai yang berhasil diimpor. ' . implode(' ', array_slice($errors, 0, 3)));
        }

        $this->redirect(Auth::baseUrl() . ($isYayasan ? '/master/employees' : '/users/school-accounts'));
    }

    public function turnover(): void {
        $this->requireRole(['operator_yayasan', 'deputi_pendidikan', 'kepala_sekolah']);
        $unitFilter = (int)($_GET['unit_id'] ?? (Auth::hasRole('kepala_sekolah') ? Auth::unitId() : 0));

        $sql = "SELECT t.*, e.nama as employee_name, e.nipy, u_from.name as from_unit_name, u_to.name as to_unit_name, u_creator.full_name as creator_name 
                FROM employee_turnover_logs t 
                JOIN employees e ON t.employee_id = e.id 
                LEFT JOIN units u_from ON t.from_unit_id = u_from.id 
                LEFT JOIN units u_to ON t.to_unit_id = u_to.id 
                LEFT JOIN users u_creator ON t.created_by_user_id = u_creator.id";
        $params = [];
        if ($unitFilter > 0) {
            $sql .= " WHERE t.from_unit_id = ? OR t.to_unit_id = ?";
            $params = [$unitFilter, $unitFilter];
        }
        $sql .= " ORDER BY t.effective_date DESC";
        $logs = Database::fetchAll($sql, $params);

        // Hitung persentase turnover per unit
        // Rumus: jumlah pegawai keluar setahun / rata-rata jumlah pegawai * 100%
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $turnoverStats = [];
        foreach ($units as $u) {
            $totalActive = (int)Database::fetch("SELECT COUNT(*) as c FROM employees WHERE unit_id = ? AND is_deleted = 0", [$u['id']])['c'];
            $outCount = (int)Database::fetch(
                "SELECT COUNT(*) as c FROM employee_turnover_logs 
                 WHERE from_unit_id = ? AND action_type IN ('Mutasi Keluar', 'Resign', 'Diberhentikan', 'Pensiun') 
                 AND YEAR(effective_date) = YEAR(CURDATE())",
                [$u['id']]
            )['c'];
            $rate = ($totalActive + $outCount) > 0 ? round(($outCount / max(1, ($totalActive + $outCount / 2))) * 100, 2) : 0;
            $turnoverStats[$u['id']] = [
                'unit_name'   => $u['name'],
                'active'      => $totalActive,
                'out'         => $outCount,
                'rate'        => $rate,
                'is_safe'     => $rate <= 5.0
            ];
        }

        $employees = Database::fetchAll("SELECT e.id, e.nama, e.nipy, e.unit_id, e.level_posisi, u.name as unit_name 
                                          FROM employees e 
                                          LEFT JOIN units u ON e.unit_id = u.id 
                                          WHERE e.is_deleted = 0 
                                          ORDER BY e.nama ASC");

        $this->view('master/turnover', [
            'pageTitle'     => 'Turn Over Talent & Mutasi Pegawai',
            'logs'          => $logs,
            'turnoverStats' => $turnoverStats,
            'employees'     => $employees,
            'units'         => $units,
            'unitFilter'    => $unitFilter
        ]);
    }

    public function saveTurnover(): void {
        $this->requireRole('operator_yayasan');
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $actionType = $_POST['action_type'] ?? 'Mutasi Keluar';
        $fromUnitId = !empty($_POST['from_unit_id']) ? (int)$_POST['from_unit_id'] : null;
        $toUnitId = !empty($_POST['to_unit_id']) ? (int)$_POST['to_unit_id'] : null;
        $effectiveDate = !empty($_POST['effective_date']) ? $_POST['effective_date'] : date('Y-m-d');
        $reason = trim($_POST['reason'] ?? '');
        $userId = Auth::id();

        if ($employeeId > 0 && !empty($reason)) {
            Database::execute(
                "INSERT INTO employee_turnover_logs (employee_id, action_type, from_unit_id, to_unit_id, effective_date, reason, created_by_user_id) 
                 VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$employeeId, $actionType, $fromUnitId, $toUnitId, $effectiveDate, $reason, $userId]
            );

            // Ambil data user pegawai
            $empRecord = Database::fetch("SELECT user_id, unit_id FROM employees WHERE id = ?", [$employeeId]);
            $empUserId = (int)($empRecord['user_id'] ?? 0);

            // Update unit pegawai jika mutasi
            if ($actionType === 'Mutasi Keluar' && $toUnitId) {
                Database::execute("UPDATE employees SET unit_id = ? WHERE id = ?", [$toUnitId, $employeeId]);
                
                if ($empUserId > 0) {
                    // Update unit_id akun user ke sekolah baru
                    Database::execute("UPDATE users SET unit_id = ? WHERE id = ?", [$toUnitId, $empUserId]);
                    // Reset peran dari sekolah lama sehingga belum punya peran di unit baru sampai ditetapkan operator sekolah baru
                    Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$empUserId]);
                }

                // Otomatis bersihkan jadwal supervisi, mapel pengampu, dan walas kelas di unit lama
                $this->cleanupEmployeeFormerUnitData($employeeId, $fromUnitId);

                // Pindahkan draft AKFI berjalan ke unit baru agar tidak membebani unit lama
                Database::execute(
                    "UPDATE akfi_assessments SET unit_id = ? WHERE employee_id = ? AND status IN ('Draft', 'Diajukan')",
                    [$toUnitId, $employeeId]
                );
            } elseif (in_array($actionType, ['Resign', 'Diberhentikan', 'Pensiun'])) {
                Database::execute("UPDATE employees SET status = ?, is_deleted = 1 WHERE id = ?", [$actionType, $employeeId]);
                
                if ($empUserId > 0) {
                    Database::execute("UPDATE users SET is_active = 0 WHERE id = ?", [$empUserId]);
                    Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$empUserId]);
                }

                // Otomatis bersihkan penugasan guru di semua unit karena pegawai keluar/nonaktif
                $this->cleanupEmployeeFormerUnitData($employeeId, null);

                // Bersihkan draft penilaian berjalan
                Database::execute("DELETE FROM akfi_assessments WHERE employee_id = ? AND status IN ('Draft', 'Diajukan')", [$employeeId]);
            }

            AuditLog::log('RECORD_TURNOVER', 'employee_turnover_logs', Database::lastInsertId(), null, $_POST);
            $this->setFlash('success', 'Catatan mutasi & turnover talent berhasil disimpan. Seluruh jadwal supervisi, mapel, dan rombel walas di unit lama telah otomatis dibersihkan.');
        }
        $this->redirect(Auth::baseUrl() . '/master/turnover');
    }

    /**
     * Otomatis membersihkan seluruh penugasan guru di unit lama (atau semua unit jika keluar)
     * Meliputi: Walas di kelas, Jadwal Supervisi, Penugasan Mata Pelajaran, dan Halaqah Tahfidz
     */
    private function cleanupEmployeeFormerUnitData(int $employeeId, ?int $formerUnitId): void {
        if ($employeeId <= 0) return;

        // 1. Keluarkan dari Jabatan Wali Kelas (classes) sehingga status rombel menjadi 'Wali kelas belum dipilih'
        if ($formerUnitId !== null && $formerUnitId > 0) {
            Database::execute(
                "UPDATE classes SET walas_id = NULL, walas_employee_id = NULL 
                 WHERE unit_id = ? AND (walas_employee_id = ? OR walas_id = ?)",
                [$formerUnitId, $employeeId, $employeeId]
            );
        } else {
            Database::execute(
                "UPDATE classes SET walas_id = NULL, walas_employee_id = NULL 
                 WHERE walas_employee_id = ? OR walas_id = ?",
                [$employeeId, $employeeId]
            );
        }

        // 2. Hapus dari Jadwal Supervisi Mengajar (supervisi_sessions) di mana guru menjadi peserta maupun supervisor
        if ($formerUnitId !== null && $formerUnitId > 0) {
            Database::execute(
                "DELETE FROM supervisi_rubric_scores 
                 WHERE supervisi_id IN (SELECT id FROM supervisi_sessions WHERE unit_id = ? AND (teacher_id = ? OR supervisor_id = ?))",
                [$formerUnitId, $employeeId, $employeeId]
            );
            Database::execute(
                "DELETE FROM supervisi_sessions WHERE unit_id = ? AND (teacher_id = ? OR supervisor_id = ?)",
                [$formerUnitId, $employeeId, $employeeId]
            );
        } else {
            Database::execute(
                "DELETE FROM supervisi_rubric_scores 
                 WHERE supervisi_id IN (SELECT id FROM supervisi_sessions WHERE teacher_id = ? OR supervisor_id = ?)",
                [$employeeId, $employeeId]
            );
            Database::execute(
                "DELETE FROM supervisi_sessions WHERE teacher_id = ? OR supervisor_id = ?",
                [$employeeId, $employeeId]
            );
        }

        // 3. Hapus Penugasan Pengampu Mata Pelajaran (teacher_subjects) di unit lama
        if ($formerUnitId !== null && $formerUnitId > 0) {
            Database::execute(
                "DELETE FROM teacher_subjects 
                 WHERE employee_id = ? AND class_id IN (SELECT id FROM classes WHERE unit_id = ?)",
                [$employeeId, $formerUnitId]
            );
        } else {
            Database::execute("DELETE FROM teacher_subjects WHERE employee_id = ?", [$employeeId]);
        }

        // 4. Lepaskan Penugasan Tutor Halaqah Tahfidz (tahfidz_halaqah)
        if ($formerUnitId !== null && $formerUnitId > 0) {
            Database::execute(
                "UPDATE tahfidz_halaqah SET tutor_employee_id = NULL WHERE tutor_employee_id = ? AND unit_id = ?",
                [$employeeId, $formerUnitId]
            );
        } else {
            Database::execute(
                "UPDATE tahfidz_halaqah SET tutor_employee_id = NULL WHERE tutor_employee_id = ?",
                [$employeeId]
            );
        }
    }

    // --- 4. MASTER TEMPLATE AKFI & MUSHAF MADINAH ---
    public function akfiTemplates(): void {
        $this->requireRole('operator_yayasan');
        $taId = Auth::academicYearId();
        $templates = Database::fetchAll("SELECT * FROM akfi_templates WHERE academic_year_id = ? ORDER BY id ASC", [$taId]);
        if (empty($templates)) {
            $templates = Database::fetchAll("SELECT * FROM akfi_templates ORDER BY id ASC");
        }
        $this->view('master/akfi_templates', [
            'pageTitle' => 'Master Template AKFI Seluruh Posisi',
            'templates' => $templates
        ]);
    }

    public function editAkfiTemplate(string $id): void {
        $this->requireRole('operator_yayasan');
        $template = Database::fetch("SELECT * FROM akfi_templates WHERE id = ?", [(int)$id]);
        if (!$template) {
            $this->setFlash('error', 'Template tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/master/akfi-templates');
            return;
        }

        $items = Database::fetchAll("SELECT * FROM akfi_template_items WHERE template_id = ? ORDER BY section ASC, order_num ASC", [(int)$id]);
        $this->view('master/akfi_template_edit', [
            'pageTitle' => 'Edit Indikator Template: ' . $template['position_name'],
            'template'  => $template,
            'items'     => $items
        ]);
    }

    public function updateAkfiTemplate(): void {
        $this->requireRole('operator_yayasan');
        $templateId = (int)($_POST['template_id'] ?? 0);

        // Update item weights and targets first
        if (isset($_POST['items']) && is_array($_POST['items'])) {
            foreach ($_POST['items'] as $itemId => $val) {
                $targetVal = (float)($val['target_val'] ?? 100);
                $weight = (float)($val['weight'] ?? 5);
                Database::execute(
                    "UPDATE akfi_template_items SET target_val = ?, weight = ? WHERE id = ? AND template_id = ?",
                    [$targetVal, $weight, (int)$itemId, $templateId]
                );
            }
        }

        // Akumulasi otomatis bobot komponen utama dari butir indikator per bagian
        $sectionSums = Database::fetchAll(
            "SELECT section, SUM(weight) as total_weight FROM akfi_template_items WHERE template_id = ? GROUP BY section",
            [$templateId]
        );
        $bobotKinerja = 0.0;
        $bobotSikap = 0.0;
        $bobotAbsensi = 0.0;
        foreach ($sectionSums as $ss) {
            $sec = strtoupper(trim($ss['section']));
            $w = (float)$ss['total_weight'];
            if ($sec === 'A') $bobotKinerja = $w;
            elseif ($sec === 'B') $bobotSikap = $w;
            elseif ($sec === 'C') $bobotAbsensi = $w;
        }

        Database::execute(
            "UPDATE akfi_templates SET bobot_kinerja = ?, bobot_sikap = ?, bobot_absensi = ?, updated_at = NOW() WHERE id = ?",
            [$bobotKinerja, $bobotSikap, $bobotAbsensi, $templateId]
        );

        AuditLog::log('UPDATE_AKFI_TEMPLATE', 'akfi_templates', $templateId, null, [
            'bobot_kinerja' => $bobotKinerja,
            'bobot_sikap'   => $bobotSikap,
            'bobot_absensi' => $bobotAbsensi
        ]);
        $this->setFlash('success', 'Master Template AKFI berhasil diperbarui. Bobot komponen utama otomatis disinkronkan.');
        $this->redirect(Auth::baseUrl() . '/master/akfi-templates/edit/' . $templateId);
    }

    public function mushaf(): void {
        $this->requireRole(['operator_yayasan', 'koor_tahfidz', 'tutor_siswa', 'tutor_sebaya']);
        $surahs = Database::fetchAll("SELECT * FROM mushaf_surahs ORDER BY surah_number ASC");
        $this->view('master/mushaf', [
            'pageTitle' => 'Master Mushaf Al-Qur\'an Madinah (604 Halaman, 15 Baris)',
            'surahs'    => $surahs
        ]);
    }

    // --- 5. SISWA & AKUN ORTU (BERBASIS NO. KK) ---
    public function students(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan', 'kepala_sekolah']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());
        $taId = Auth::academicYearId();

        $filterClassId = isset($_GET['class_id']) && $_GET['class_id'] !== '' ? (int)$_GET['class_id'] : null;
        $filterNoKk = trim($_GET['no_kk'] ?? '');
        $filterQuery = trim($_GET['q'] ?? '');

        // Hitung total siswa lulus / pindah untuk referensi tautan di header
        $inactiveCount = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM students WHERE unit_id = ? AND status IN ('Lulus', 'Pindah', 'Alumni')",
            [$unitId]
        )['c'] ?? 0);

        // HANYA AMBIL SISWA AKTIF DI DATA SISWA
        $sql = "SELECT s.*, cs.class_id, u.name as unit_name, c.name as class_name, pu.username as parent_username 
                FROM students s 
                JOIN units u ON s.unit_id = u.id 
                LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' AND cs.academic_year_id = ? 
                LEFT JOIN classes c ON cs.class_id = c.id 
                LEFT JOIN users pu ON s.parent_user_id = pu.id 
                WHERE s.unit_id = ? AND s.status = 'Aktif'";
        $params = [$taId, $unitId];

        if ($filterClassId !== null) {
            if ($filterClassId === 0) {
                $sql .= " AND cs.class_id IS NULL";
            } elseif ($filterClassId > 0) {
                $sql .= " AND cs.class_id = ?";
                $params[] = $filterClassId;
            }
        }

        if (!empty($filterNoKk)) {
            $sql .= " AND s.no_kk LIKE ?";
            $params[] = '%' . $filterNoKk . '%';
        }

        if (!empty($filterQuery)) {
            $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)";
            $params[] = '%' . $filterQuery . '%';
            $params[] = '%' . $filterQuery . '%';
            $params[] = '%' . $filterQuery . '%';
        }

        $sql .= " ORDER BY s.nama ASC";
        $students = Database::fetchAll($sql, $params);

        $classes = Database::fetchAll("SELECT * FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, $taId]);
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");

        $this->view('master/students', [
            'pageTitle'     => 'Data Siswa Aktif & Akun Orang Tua Berbasis No. KK',
            'students'      => $students,
            'classes'       => $classes,
            'units'         => $units,
            'unitId'        => $unitId,
            'inactiveCount' => $inactiveCount,
            'filterClassId' => $filterClassId,
            'filterNoKk'    => $filterNoKk,
            'filterQuery'   => $filterQuery
        ]);
    }

    // --- MENU TERSENDIRI: DATA SISWA LULUS & PINDAH (ALUMNI & MUTASI KELUAR) ---
    public function inactiveStudents(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan', 'kepala_sekolah']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());

        $filterStatus = trim($_GET['status'] ?? '');
        $filterNoKk = trim($_GET['no_kk'] ?? '');
        $filterQuery = trim($_GET['q'] ?? '');

        // Hitung statistik Lulus & Pindah
        $stats = [
            'total'  => 0,
            'lulus'  => 0,
            'pindah' => 0
        ];
        $countRows = Database::fetchAll(
            "SELECT status, COUNT(*) as c FROM students WHERE unit_id = ? AND status IN ('Lulus', 'Pindah', 'Alumni') GROUP BY status",
            [$unitId]
        );
        foreach ($countRows as $cr) {
            $stats['total'] += (int)$cr['c'];
            if ($cr['status'] === 'Lulus' || $cr['status'] === 'Alumni') $stats['lulus'] += (int)$cr['c'];
            elseif ($cr['status'] === 'Pindah') $stats['pindah'] += (int)$cr['c'];
        }

        $sql = "SELECT s.*, u.name as unit_name, pu.username as parent_username,
                       (SELECT c.name FROM class_students cs2 JOIN classes c ON cs2.class_id = c.id WHERE cs2.student_id = s.id ORDER BY cs2.id DESC LIMIT 1) as last_class_name,
                       (SELECT at.destination_name FROM alumni_tracking at WHERE at.student_id = s.id ORDER BY at.id DESC LIMIT 1) as alumni_destination,
                       (SELECT at.graduation_year FROM alumni_tracking at WHERE at.student_id = s.id ORDER BY at.id DESC LIMIT 1) as alumni_grad_year
                FROM students s 
                JOIN units u ON s.unit_id = u.id 
                LEFT JOIN users pu ON s.parent_user_id = pu.id 
                WHERE s.unit_id = ? AND s.status IN ('Lulus', 'Pindah', 'Alumni')";
        $params = [$unitId];

        if (!empty($filterStatus) && in_array($filterStatus, ['Lulus', 'Pindah', 'Alumni'])) {
            if ($filterStatus === 'Lulus') {
                $sql .= " AND s.status IN ('Lulus', 'Alumni')";
            } else {
                $sql .= " AND s.status = ?";
                $params[] = $filterStatus;
            }
        }

        if (!empty($filterNoKk)) {
            $sql .= " AND s.no_kk LIKE ?";
            $params[] = '%' . $filterNoKk . '%';
        }

        if (!empty($filterQuery)) {
            $sql .= " AND (s.nama LIKE ? OR s.nis LIKE ? OR s.nisn LIKE ?)";
            $params[] = '%' . $filterQuery . '%';
            $params[] = '%' . $filterQuery . '%';
            $params[] = '%' . $filterQuery . '%';
        }

        $sql .= " ORDER BY s.updated_at DESC, s.nama ASC";
        $students = Database::fetchAll($sql, $params);
        $units = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");

        $this->view('master/students_inactive', [
            'pageTitle'     => 'Data Siswa Lulus & Pindah (Alumni & Mutasi)',
            'students'      => $students,
            'stats'         => $stats,
            'units'         => $units,
            'unitId'        => $unitId,
            'filterStatus'  => $filterStatus,
            'filterNoKk'    => $filterNoKk,
            'filterQuery'   => $filterQuery
        ]);
    }

    public function reactivateStudent(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_POST['unit_id'] ?? Auth::unitId());

        $std = Database::fetch("SELECT * FROM students WHERE id = ? AND unit_id = ?", [$studentId, $unitId]);
        if ($std) {
            Database::execute("UPDATE students SET status = 'Aktif', updated_at = NOW() WHERE id = ?", [$studentId]);
            AuditLog::log('REACTIVATE_STUDENT', 'students', $studentId, $std, ['status' => 'Aktif']);
            $this->setFlash('success', "Siswa '{$std['nama']}' (NIS: {$std['nis']}) berhasil diaktifkan kembali dan kini muncul di Data Siswa Aktif & Penempatan Rombel.");
        } else {
            $this->setFlash('danger', "Siswa tidak ditemukan atau bukan bagian dari unit ini.");
        }
        $this->redirect(Auth::baseUrl() . '/students/alumni-mutasi');
    }

    public function exportInactiveStudents(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());

        $rows = Database::fetchAll(
            "SELECT s.nis, s.nisn, s.nik, s.no_kk, s.nama, s.gender, s.status, s.birthplace, s.birthdate, s.father_name, s.parent_phone, u.name as unit_name 
             FROM students s 
             JOIN units u ON s.unit_id = u.id 
             WHERE s.unit_id = ? AND s.status IN ('Lulus', 'Pindah', 'Alumni') 
             ORDER BY s.status ASC, s.nama ASC",
            [$unitId]
        );
        $headers = ['NIS', 'NISN', 'NIK', 'Nomor KK', 'Nama Siswa', 'JK', 'Status', 'Tempat Lahir', 'Tgl Lahir', 'Nama Ayah/Wali', 'No. HP Ortu', 'Unit'];
        ExportHelper::excel('Data_Siswa_Lulus_Pindah_' . date('Ymd'), $headers, $rows, 'DATA SISWA LULUS & PINDAH YAYASAN ISLAM AL KAHFI BATAM');
    }

    public function saveStudent(): void {
        $this->requireRole('operator_sekolah');
        $id = (int)($_POST['id'] ?? 0);
        $nis = trim($_POST['nis'] ?? '');
        $nisn = trim($_POST['nisn'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $noKk = trim($_POST['no_kk'] ?? '');
        $nama = trim($_POST['nama'] ?? '');
        $gender = $_POST['gender'] ?? 'L';
        $birthplace = trim($_POST['birthplace'] ?? '');
        $birthdate = !empty($_POST['birthdate']) ? $_POST['birthdate'] : null;
        $address = trim($_POST['address'] ?? '');
        $fatherName = trim($_POST['father_name'] ?? '');
        $motherName = trim($_POST['mother_name'] ?? '');
        $parentPhone = trim($_POST['parent_phone'] ?? '');
        $parentEmail = trim($_POST['parent_email'] ?? '');
        $classId = (int)($_POST['class_id'] ?? 0);
        $status = $_POST['status'] ?? 'Aktif';
        if (!in_array($status, ['Aktif', 'Lulus', 'Pindah', 'Alumni'], true)) {
            $status = 'Aktif';
        }
        $unitId = Auth::unitId();
        $taId = Auth::academicYearId();

        // Cek akun orang tua berbasis No. KK
        // Jika orang tua sudah punya akun karena anak lain, hubungkan otomatis!
        $parentUser = Database::fetch("SELECT id FROM users WHERE username = ?", [$noKk]);
        if (!$parentUser) {
            // Buat akun orang tua baru dengan username No. KK dan default password 'password123'
            $pwdHash = password_hash('password123', PASSWORD_DEFAULT);
            Database::execute(
                "INSERT INTO users (username, password_hash, email, full_name, phone, is_active, force_change_password) VALUES (?, ?, ?, ?, ?, 1, 1)",
                [$noKk, $pwdHash, $parentEmail, $fatherName ?: "Orang Tua {$nama}", $parentPhone]
            );
            $parentUserId = (int)Database::lastInsertId();
            Database::execute("INSERT INTO user_roles (user_id, role_id, is_primary) SELECT ?, id, 1 FROM roles WHERE name = 'orang_tua'", [$parentUserId]);
        } else {
            $parentUserId = (int)$parentUser['id'];
        }

        if ($id > 0) {
            $old = $this->assertUnitOwnership('students', $id);
            Database::execute(
                "UPDATE students SET nis = ?, nisn = ?, nik = ?, no_kk = ?, nama = ?, gender = ?, birthplace = ?, birthdate = ?, address = ?, father_name = ?, mother_name = ?, parent_phone = ?, parent_email = ?, parent_user_id = ?, status = ?, updated_at = NOW() WHERE id = ?",
                [$nis, $nisn, $nik, $noKk, $nama, $gender, $birthplace, $birthdate, $address, $fatherName, $motherName, $parentPhone, $parentEmail, $parentUserId, $status, $id]
            );
            $studentId = $id;
            AuditLog::log('UPDATE_STUDENT', 'students', $id, $old, $_POST);
            $this->setFlash('success', 'Data siswa berhasil diperbarui.');
        } else {
            Database::execute(
                "INSERT INTO students (nis, nisn, nik, no_kk, nama, gender, birthplace, birthdate, address, father_name, mother_name, parent_phone, parent_email, parent_user_id, unit_id, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$nis, $nisn, $nik, $noKk, $nama, $gender, $birthplace, $birthdate, $address, $fatherName, $motherName, $parentPhone, $parentEmail, $parentUserId, $unitId, $status]
            );
            $studentId = (int)Database::lastInsertId();
            AuditLog::log('CREATE_STUDENT', 'students', $studentId, null, $_POST);
            $this->setFlash('success', 'Siswa baru berhasil ditambahkan dan diintegrasikan ke akun Orang Tua (No. KK: ' . $noKk . ').');
        }

        // Jika status bukan Aktif, keluarkan siswa dari kelas/rombel
        if ($status !== 'Aktif') {
            $classId = 0;
        }

        // Penempatan kelas siswa (Single Active Enrollment per Tahun Ajaran)
        if ($studentId > 0) {
            if ($classId > 0) {
                Database::execute(
                    "INSERT INTO class_students (class_id, student_id, academic_year_id, status) 
                     VALUES (?, ?, ?, 'Aktif') 
                     ON DUPLICATE KEY UPDATE class_id = VALUES(class_id), status = 'Aktif'",
                    [$classId, $studentId, $taId]
                );
            } else {
                // Jika dipilih 'Belum Ditentukan' (0), keluarkan dari kelas aktif di tahun ajaran ini
                Database::execute(
                    "DELETE FROM class_students WHERE student_id = ? AND academic_year_id = ?",
                    [$studentId, $taId]
                );
            }
        }

        $this->redirect(Auth::baseUrl() . '/students');
    }

    public function printParentCard(string $id): void {
        $this->requireRole('operator_sekolah');
        $this->assertUnitOwnership('students', (int)$id);
        $std = Database::fetch(
            "SELECT s.*, u.name as unit_name, pu.username as kk_user 
             FROM students s 
             JOIN units u ON s.unit_id = u.id 
             JOIN users pu ON s.parent_user_id = pu.id 
             WHERE s.id = ?",
            [(int)$id]
        );
        if (!$std) {
            die('Data siswa tidak ditemukan.');
        }

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$std['unit_id']]);
        $kop = ExportHelper::renderKop($unit);

        $html = "
        <html>
        <head>
            <style>
                body { font-family: Helvetica, Arial, sans-serif; font-size: 13px; color: #111; }
                .card-box { border: 2px solid #1e3a8a; border-radius: 12px; padding: 20px; max-width: 500px; margin: 20px auto; background: #f8fafc; }
                table { width: 100%; border-collapse: collapse; margin-top: 10px; }
                td { padding: 6px 4px; vertical-align: top; }
            </style>
        </head>
        <body>
            <div class='card-box'>
                {$kop}
                <h3 style='text-align: center; color: #1e3a8a; margin-top: 0;'>KARTU AKUN AKSES ORANG TUA SANTRI</h3>
                <p style='text-align: center; font-size: 11px; color: #555;'>Gunakan informasi login berikut untuk mengakses Rapor, Tagihan SPP, dan Survei Sekolah:</p>
                <table>
                    <tr><td style='width: 140px; font-weight: bold;'>Nama Santri</td><td>: {$std['nama']}</td></tr>
                    <tr><td style='font-weight: bold;'>NIS / NISN</td><td>: {$std['nis']} / " . ($std['nisn'] ?: '-') . "</td></tr>
                    <tr><td style='font-weight: bold;'>Unit Sekolah</td><td>: {$std['unit_name']}</td></tr>
                    <tr><td style='font-weight: bold;'>Nama Orang Tua</td><td>: {$std['father_name']}</td></tr>
                    <tr style='background: #e0f2fe;'><td style='font-weight: bold; color: #0369a1;'>Username (No. KK)</td><td style='font-weight: bold; color: #0369a1;'>: {$std['kk_user']}</td></tr>
                    <tr style='background: #e0f2fe;'><td style='font-weight: bold; color: #0369a1;'>Password Sementara</td><td style='font-weight: bold; color: #0369a1;'>: password123</td></tr>
                </table>
                <div style='margin-top: 16px; font-size: 10px; color: #666; border-top: 1px dashed #ccc; padding-top: 8px;'>
                    * Wajib ganti password saat login pertama kali. Satu akun No. KK berlaku untuk seluruh anak di Yayasan Islam Al Kahfi Batam.
                </div>
            </div>
        </body>
        </html>
        ";
        ExportHelper::pdf($html, 'Kartu_Akun_Ortu_' . $std['nis'], 'A4', 'portrait', true);
    }

    public function exportStudents(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());

        $rows = Database::fetchAll(
            "SELECT s.nis, s.nisn, s.nik, s.no_kk, s.nama, s.gender, s.birthplace, s.birthdate, s.father_name, s.parent_phone, u.name as unit_name, c.name as class_name 
             FROM students s 
             JOIN units u ON s.unit_id = u.id 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.status = 'Aktif' 
             LEFT JOIN classes c ON cs.class_id = c.id 
             WHERE s.unit_id = ? AND s.status = 'Aktif' 
             ORDER BY s.nama ASC",
            [$unitId]
        );
        $headers = ['NIS', 'NISN', 'NIK', 'Nomor KK', 'Nama Siswa', 'JK', 'Tempat Lahir', 'Tgl Lahir', 'Nama Ayah/Wali', 'No. HP Ortu', 'Unit', 'Kelas'];
        ExportHelper::excel('Data_Siswa_' . date('Ymd'), $headers, $rows, 'DATA SISWA YAYASAN ISLAM AL KAHFI BATAM');
    }

    public function templateStudents(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $isYayasan = Auth::hasRole('operator_yayasan');
        $unitId = $isYayasan ? (int)($_GET['unit_id'] ?? Auth::unitId()) : Auth::unitId();
        $taId = Auth::academicYearId();

        $unit = Database::fetch("SELECT code, name FROM units WHERE id = ?", [$unitId]);
        $uCode = $unit['code'] ?? 'SCH';

        // Dynamic classes available at this moment in this unit and active academic year
        $classes = Database::fetchAll("SELECT id, name FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, $taId]);

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        // Headers
        $headers = [
            'A1' => 'No',
            'B1' => 'Nama Lengkap Siswa *',
            'C1' => 'NIS (Wajib/Unik) *',
            'D1' => 'NISN (Opsional)',
            'E1' => 'NIK Siswa (Opsional)',
            'F1' => 'Jenis Kelamin (L/P) *',
            'G1' => 'Tempat Lahir',
            'H1' => 'Tanggal Lahir (YYYY-MM-DD)',
            'I1' => 'Nomor Kartu Keluarga (No. KK - Username Ortu) *',
            'J1' => 'Nama Ayah / Wali *',
            'K1' => 'No. WhatsApp Orang Tua',
            'L1' => 'Kelas Penempatan (Pilih Dropdown)'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        // Style Headers
        $sheet->getStyle('A1:L1')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '047857']], // Dark Emerald Green
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, 'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]]
        ]);
        $sheet->getRowDimension(1)->setRowHeight(28);

        // Populate hidden worksheet for dynamic classes
        $classCount = count($classes);
        if ($classCount > 0) {
            $refSheet = $spreadsheet->createSheet();
            $refSheet->setTitle('_RefClasses');
            foreach ($classes as $idx => $c) {
                $rowNum = $idx + 1;
                $refSheet->setCellValue("A{$rowNum}", $c['name']);
            }
            $refSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
        }

        // Sample row
        $sampleClass = $classCount > 0 ? $classes[0]['name'] : '';
        $sheet->setCellValue('A2', 1);
        $sheet->setCellValue('B2', 'Muhammad Fatih');
        $sheet->setCellValue('C2', $uCode . '-' . date('Y') . '001');
        $sheet->setCellValue('D2', '0123456789');
        $sheet->setCellValue('E2', '2171010101010001');
        $sheet->setCellValue('F2', 'L');
        $sheet->setCellValue('G2', 'Batam');
        $sheet->setCellValue('H2', '2018-05-15');
        $sheet->setCellValue('I2', '2171010101010000');
        $sheet->setCellValue('J2', 'Fauzi Rahmat');
        $sheet->setCellValue('K2', '081234567890');
        $sheet->setCellValue('L2', $sampleClass);

        // Validation: Gender (Column F)
        $valGender = $sheet->getDataValidation('F2:F500');
        $valGender->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
        $valGender->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
        $valGender->setAllowBlank(false);
        $valGender->setShowDropDown(true);
        $valGender->setShowErrorMessage(true);
        $valGender->setErrorTitle('Jenis Kelamin Tidak Valid');
        $valGender->setError('Pilih L (Laki-laki) atau P (Perempuan).');
        $valGender->setFormula1('"L,P"');

        // Validation: Classes (Column L)
        if ($classCount > 0) {
            $valClass = $sheet->getDataValidation('L2:L500');
            $valClass->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $valClass->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_STOP);
            $valClass->setAllowBlank(true);
            $valClass->setShowDropDown(true);
            $valClass->setShowErrorMessage(true);
            $valClass->setErrorTitle('Kelas Tidak Valid');
            $valClass->setError('Pilih kelas dari daftar dropdown kelas aktif di unit sekolah ini.');
            $valClass->setFormula1("_RefClasses!\$A\$1:\$A\${$classCount}");
        }

        // Auto width
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="Template_Import_Siswa_' . $uCode . '.xlsx"');
        header('Cache-Control: max-age=0');
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function importStudents(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $isYayasan = Auth::hasRole('operator_yayasan');
        $unitId = $isYayasan ? (int)($_POST['unit_id'] ?? Auth::unitId()) : Auth::unitId();
        $taId = Auth::academicYearId();

        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            $this->setFlash('danger', 'Berkas Excel tidak valid atau gagal diunggah.');
            $this->redirect(Auth::baseUrl() . '/students');
            return;
        }

        $tmpFile = $_FILES['file']['tmp_name'];
        $ext = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['xlsx', 'xls'])) {
            $this->setFlash('danger', 'Hanya berkas Excel dengan ekstensi .xlsx atau .xls yang diperbolehkan.');
            $this->redirect(Auth::baseUrl() . '/students');
            return;
        }

        // Load classes map for this unit & academic year
        $classesRaw = Database::fetchAll("SELECT id, name FROM classes WHERE unit_id = ? AND academic_year_id = ?", [$unitId, $taId]);
        $classMap = [];
        foreach ($classesRaw as $c) {
            $classMap[strtolower(trim($c['name']))] = (int)$c['id'];
        }

        $parentRole = Database::fetch("SELECT id FROM roles WHERE name = 'orang_tua'");
        $parentRoleId = $parentRole ? (int)$parentRole['id'] : 13;

        try {
            $spreadsheet = IOFactory::load($tmpFile);
            $sheet = $spreadsheet->getActiveSheet();
            $highestRow = $sheet->getHighestRow();
        } catch (\Throwable $e) {
            $this->setFlash('danger', 'Gagal membaca berkas Excel: ' . $e->getMessage());
            $this->redirect(Auth::baseUrl() . '/students');
            return;
        }

        $imported = 0;
        $skipped = 0;
        $errors = [];

        for ($row = 2; $row <= $highestRow; $row++) {
            $nama = trim((string)$sheet->getCell("B{$row}")->getValue());
            if (empty($nama)) continue;

            $nis = trim((string)$sheet->getCell("C{$row}")->getValue());
            $nisn = trim((string)$sheet->getCell("D{$row}")->getValue());
            $nik = trim((string)$sheet->getCell("E{$row}")->getValue());
            $gender = strtoupper(trim((string)$sheet->getCell("F{$row}")->getValue()));
            if (!in_array($gender, ['L', 'P'])) $gender = 'L';

            $birthplace = trim((string)$sheet->getCell("G{$row}")->getValue());
            $birthdate = trim((string)$sheet->getCell("H{$row}")->getValue());
            if (is_numeric($birthdate) && $birthdate > 0) {
                $birthdate = date('Y-m-d', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp($birthdate));
            } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $birthdate)) {
                $birthdate = null;
            }

            $noKk = trim((string)$sheet->getCell("I{$row}")->getValue());
            $fatherName = trim((string)$sheet->getCell("J{$row}")->getValue());
            $parentPhone = trim((string)$sheet->getCell("K{$row}")->getValue());
            $className = strtolower(trim((string)$sheet->getCell("L{$row}")->getValue()));

            if (empty($nis)) {
                $skipped++;
                $errors[] = "Baris {$row} ({$nama}): NIS wajib diisi.";
                continue;
            }

            $sCheck = Database::fetch("SELECT id FROM students WHERE nis = ?", [$nis]);
            if ($sCheck) {
                $skipped++;
                $errors[] = "Baris {$row}: NIS '{$nis}' sudah terdaftar untuk siswa lain.";
                continue;
            }

            if (empty($noKk)) {
                $noKk = 'KK-' . $nis;
            }

            $parentUser = Database::fetch("SELECT id FROM users WHERE username = ?", [$noKk]);
            if (!$parentUser) {
                $pHash = password_hash('password123', PASSWORD_DEFAULT);
                $pName = !empty($fatherName) ? $fatherName : ('Orang Tua ' . $nama);
                Database::execute(
                    "INSERT INTO users (username, password_hash, full_name, phone, unit_id, is_active, force_change_password)
                     VALUES (?, ?, ?, ?, ?, 1, 0)",
                    [$noKk, $pHash, $pName, $parentPhone ?: null, $unitId]
                );
                $parentUserId = (int)Database::lastInsertId();
                Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 1)", [$parentUserId, $parentRoleId]);
            } else {
                $parentUserId = (int)$parentUser['id'];
                if (!empty($parentPhone)) {
                    Database::execute("UPDATE users SET phone = ? WHERE id = ? AND (phone IS NULL OR phone = '')", [$parentPhone, $parentUserId]);
                }
            }

            Database::execute(
                "INSERT INTO students (unit_id, parent_user_id, nis, nisn, nik, no_kk, nama, gender, birthplace, birthdate, father_name, parent_phone, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Aktif')",
                [$unitId, $parentUserId, $nis, $nisn ?: null, $nik ?: null, $noKk, $nama, $gender, $birthplace ?: null, $birthdate, $fatherName ?: null, $parentPhone ?: null]
            );
            $newStudentId = (int)Database::lastInsertId();

            if (!empty($className) && isset($classMap[$className])) {
                $targetClassId = $classMap[$className];
                Database::execute(
                    "INSERT INTO class_students (class_id, student_id, academic_year_id, status)
                     VALUES (?, ?, ?, 'Aktif')
                     ON DUPLICATE KEY UPDATE class_id = VALUES(class_id)",
                    [$targetClassId, $newStudentId, $taId]
                );
            }

            AuditLog::log('IMPORT_STUDENT', 'students', $newStudentId, null, [
                'nis' => $nis, 'no_kk' => $noKk, 'unit_id' => $unitId
            ]);

            $imported++;
        }

        if ($imported > 0) {
            $msg = "Berhasil mengimpor {$imported} data santri baru.";
            if ($skipped > 0) {
                $msg .= " ({$skipped} baris dilewati).";
            }
            $this->setFlash('success', $msg);
        } else {
            $this->setFlash('warning', 'Tidak ada data siswa yang berhasil diimpor. ' . implode(' ', array_slice($errors, 0, 3)));
        }

        $this->redirect(Auth::baseUrl() . '/students');
    }

    // --- 6. KELAS & MATA PELAJARAN ---
    public function classes(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());
        $taId = Auth::academicYearId();

        $classes = Database::fetchAll(
            "SELECT c.*, e.nama as walas_name, e.contact_phone as walas_phone, e.position as walas_position, COUNT(cs.student_id) as student_count 
             FROM classes c 
             LEFT JOIN employees e ON c.walas_id = e.id AND e.unit_id = c.unit_id AND e.is_deleted = 0 AND e.status = 'Aktif'
             LEFT JOIN class_students cs ON c.id = cs.class_id AND cs.status = 'Aktif' 
             WHERE c.unit_id = ? AND c.academic_year_id = ? 
             GROUP BY c.id ORDER BY c.level ASC, c.name ASC",
            [$unitId, $taId]
        );

        $teachers = Database::fetchAll(
            "SELECT id, nama, level_posisi, position, contact_phone FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif'
               AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Waka%' OR level_posisi LIKE '%Waka%' OR position LIKE '%Tutor%' OR level_posisi LIKE '%Tutor%')
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY nama ASC", 
            [$unitId]
        );

        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);
        $unitLevel = $unit['level'] ?? 'SD';
        $fixedLevels = PromotionHelper::getFixedLevelsForUnit($unitLevel);
        
        $currentTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        $previousGenapYears = Database::fetchAll(
            "SELECT * FROM academic_years WHERE id != ? AND semester = 'Genap' ORDER BY id DESC", 
            [$taId]
        );

        $totalStudentsInClasses = 0;
        $assignedWalasCount = 0;
        foreach ($classes as $c) {
            $totalStudentsInClasses += (int)$c['student_count'];
            if (!empty($c['walas_id'])) {
                $assignedWalasCount++;
            }
        }

        $this->view('master/classes', [
            'pageTitle'              => 'Kelola Rombongan Belajar (Kelas) & Wali Kelas',
            'classes'                => $classes,
            'teachers'               => $teachers,
            'unit'                   => $unit,
            'unitId'                 => $unitId,
            'unitLevel'              => $unitLevel,
            'fixedLevels'            => $fixedLevels,
            'allFixedLevels'         => PromotionHelper::FIXED_LEVELS,
            'currentTa'              => $currentTa,
            'previousGenapYears'     => $previousGenapYears,
            'totalStudentsInClasses' => $totalStudentsInClasses,
            'assignedWalasCount'     => $assignedWalasCount
        ]);
    }

    public function saveClass(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $level = PromotionHelper::normalizeLevel(trim($_POST['level'] ?? ''));
        $walasId = !empty($_POST['walas_id']) ? (int)$_POST['walas_id'] : null;
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_POST['unit_id'] ?? Auth::unitId());
        $taId = Auth::academicYearId();

        // Validasi: pastikan wali kelas berasal dari unit sekolah yang sama
        if ($walasId) {
            $validWalas = Database::fetch("SELECT id FROM employees WHERE id = ? AND unit_id = ? AND is_deleted = 0", [$walasId, $unitId]);
            if (!$validWalas) {
                $this->setFlash('danger', 'Validasi Gagal: Guru yang dipilih sebagai wali kelas bukan berasal dari unit sekolah Anda.');
                $this->redirect(Auth::baseUrl() . '/classes');
                return;
            }
        }

        if ($id > 0) {
            if (Auth::hasRole('operator_sekolah')) {
                $checkClass = Database::fetch("SELECT id FROM classes WHERE id = ? AND unit_id = ?", [$id, $unitId]);
                if (!$checkClass) {
                    $this->setFlash('danger', 'Akses Ditolak: Anda tidak dapat mengubah kelas dari unit sekolah lain.');
                    $this->redirect(Auth::baseUrl() . '/classes');
                    return;
                }
            }
            Database::execute("UPDATE classes SET name = ?, level = ?, walas_id = ?, updated_at = NOW() WHERE id = ?", [$name, $level, $walasId, $id]);
            AuditLog::log('UPDATE_CLASS', 'classes', $id);
            $this->setFlash('success', 'Data kelas berhasil diperbarui.');
        } else {
            Database::execute(
                "INSERT INTO classes (unit_id, academic_year_id, name, level, walas_id) VALUES (?, ?, ?, ?, ?)",
                [$unitId, $taId, $name, $level, $walasId]
            );
            AuditLog::log('CREATE_CLASS', 'classes', Database::lastInsertId());
            $this->setFlash('success', 'Kelas baru berhasil ditambahkan.');
        }
        $this->redirect(Auth::baseUrl() . '/classes');
    }

    public function promoteClasses(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_POST['unit_id'] ?? Auth::unitId());
        $toTaId = Auth::academicYearId();
        $fromTaId = (int)($_POST['from_ta_id'] ?? 0);
        $retainedIds = array_map('intval', $_POST['retained_students'] ?? []);

        if ($fromTaId <= 0 || $fromTaId === $toTaId) {
            $this->setFlash('danger', 'Pilih tahun ajaran asal (Semester Genap) yang valid untuk proses kenaikan kelas.');
            $this->redirect(Auth::baseUrl() . '/classes');
            return;
        }

        $fromTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$fromTaId]);
        $toTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$toTaId]);

        if (!$fromTa || !$toTa) {
            $this->setFlash('danger', 'Data tahun ajaran tidak valid.');
            $this->redirect(Auth::baseUrl() . '/classes');
            return;
        }

        if (!PromotionHelper::isPromotionEligible($fromTa, $toTa)) {
            $this->setFlash('danger', 'Kenaikan kelas hanya diperbolehkan untuk perpindahan dari Semester Genap ke Semester Ganjil di tahun ajaran yang berbeda.');
            $this->redirect(Auth::baseUrl() . '/classes');
            return;
        }

        $stats = PromotionHelper::executePromotion($fromTaId, $toTaId, $unitId, $retainedIds);

        AuditLog::log('MANUAL_CLASS_PROMOTION', 'classes', $toTaId, null, [
            'unit_id' => $unitId,
            'from_ta_id' => $fromTaId,
            'to_ta_id' => $toTaId,
            'stats' => $stats
        ]);

        $this->setFlash('success', "Proses kenaikan kelas berhasil! {$stats['promoted_count']} siswa naik kelas, {$stats['graduated_count']} siswa lulus, {$stats['retained_count']} siswa tinggal kelas, dan {$stats['classes_created']} rombel baru dibuat.");
        $this->redirect(Auth::baseUrl() . '/classes');
    }

    public function promotePreview(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());
        $fromTaId = (int)($_GET['from_ta_id'] ?? 0);
        $toTaId = Auth::academicYearId();

        $fromTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$fromTaId]);
        $toTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$toTaId]);

        if (!$fromTa || !$toTa || !PromotionHelper::isPromotionEligible($fromTa, $toTa)) {
            echo json_encode(['success' => false, 'message' => 'Kenaikan kelas hanya berlaku dari Semester Genap ke Semester Ganjil di tahun ajaran yang berbeda.']);
            exit;
        }

        $classes = Database::fetchAll(
            "SELECT c.* FROM classes c WHERE c.unit_id = ? AND c.academic_year_id = ? ORDER BY c.level ASC, c.name ASC",
            [$unitId, $fromTaId]
        );

        $preview = [];
        foreach ($classes as $c) {
            $norm = PromotionHelper::normalizeLevel($c['level']);
            $targetLevel = PromotionHelper::PROMOTION_TARGETS[$norm] ?? '-';
            $targetName = ($targetLevel === 'LULUS') ? 'LULUS (Alumni)' : PromotionHelper::getTargetClassName($c['name'], $c['level'], $targetLevel);

            $students = Database::fetchAll(
                "SELECT s.id, s.nama, s.nis FROM class_students cs 
                 JOIN students s ON cs.student_id = s.id 
                 WHERE cs.class_id = ? AND cs.academic_year_id = ? AND cs.status = 'Aktif' ORDER BY s.nama ASC",
                [$c['id'], $fromTaId]
            );

            $preview[] = [
                'class_id'     => $c['id'],
                'current_name' => $c['name'],
                'current_level'=> $norm,
                'target_level' => $targetLevel,
                'target_name'  => $targetName,
                'is_graduating'=> ($targetLevel === 'LULUS'),
                'students'     => $students
            ];
        }

        echo json_encode(['success' => true, 'classes' => $preview]);
        exit;
    }

    // --- 7. PENEMPATAN & PEMINDAHAN SISWA KE DALAM KELAS (ROMBEL MANAGER) ---
    public function classPlacement(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());
        $taId = Auth::academicYearId();
        $selectedClassId = isset($_GET['class_id']) ? (int)$_GET['class_id'] : 0;

        // Ambil daftar kelas di unit dan tahun ajaran ini
        $classes = Database::fetchAll(
            "SELECT c.*, e.nama as walas_name, 
                    COUNT(cs.student_id) as student_count 
             FROM classes c 
             LEFT JOIN employees e ON c.walas_id = e.id 
             LEFT JOIN class_students cs ON c.id = cs.class_id AND cs.status = 'Aktif' 
             WHERE c.unit_id = ? AND c.academic_year_id = ? 
             GROUP BY c.id ORDER BY c.level ASC, c.name ASC",
            [$unitId, $taId]
        );

        // Ambil semua siswa aktif di unit ini beserta kelas aktifnya
        $sql = "SELECT s.id, s.nis, s.nisn, s.nama, s.gender, s.nik, s.no_kk, 
                       cs.id as class_student_id, cs.class_id, c.name as class_name, c.level as class_level 
                FROM students s 
                LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ? AND cs.status = 'Aktif' 
                LEFT JOIN classes c ON cs.class_id = c.id 
                WHERE s.unit_id = ? AND s.status = 'Aktif'";
        $params = [$taId, $unitId];

        if ($selectedClassId > 0) {
            $sql .= " AND cs.class_id = ?";
            $params[] = $selectedClassId;
        } elseif ($selectedClassId === -1) {
            // Filter santri yang belum masuk kelas
            $sql .= " AND cs.class_id IS NULL";
        }

        $sql .= " ORDER BY (cs.class_id IS NULL) DESC, c.name ASC, s.nama ASC";
        $students = Database::fetchAll($sql, $params);

        // Hitung total santri, sudah berkelas, belum berkelas
        $stats = Database::fetch(
            "SELECT 
                COUNT(s.id) as total_students,
                COUNT(cs.id) as assigned_students,
                SUM(CASE WHEN cs.id IS NULL THEN 1 ELSE 0 END) as unassigned_students
             FROM students s 
             LEFT JOIN class_students cs ON s.id = cs.student_id AND cs.academic_year_id = ? AND cs.status = 'Aktif' 
             WHERE s.unit_id = ? AND s.status = 'Aktif'",
            [$taId, $unitId]
        );

        $graduatedCount = (int)(Database::fetch(
            "SELECT COUNT(*) as c FROM students WHERE unit_id = ? AND status = 'Lulus'",
            [$unitId]
        )['c'] ?? 0);

        $this->view('master/class_placement', [
            'pageTitle'        => 'Penempatan Siswa Kelas & Rombongan Belajar',
            'classes'          => $classes,
            'students'         => $students,
            'stats'            => $stats,
            'graduatedCount'   => $graduatedCount,
            'selectedClassId'  => $selectedClassId,
            'unitId'           => $unitId
        ]);
    }

    public function assignStudentClass(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $taId = Auth::academicYearId();
        $redirectUrl = $_POST['redirect_url'] ?? (Auth::baseUrl() . '/classes/placement');

        if ($studentId > 0 && $classId > 0) {
            Database::execute(
                "INSERT INTO class_students (class_id, student_id, academic_year_id, status) 
                 VALUES (?, ?, ?, 'Aktif') 
                 ON DUPLICATE KEY UPDATE class_id = VALUES(class_id), status = 'Aktif'",
                [$classId, $studentId, $taId]
            );
            $class = Database::fetch("SELECT name FROM classes WHERE id = ?", [$classId]);
            $student = Database::fetch("SELECT nama FROM students WHERE id = ?", [$studentId]);
            AuditLog::log('ASSIGN_STUDENT_CLASS', 'class_students', $studentId, null, ['class_id' => $classId]);
            $this->setFlash('success', "Santri " . ($student['nama'] ?? '') . " berhasil dimasukkan/dipindahkan ke kelas " . ($class['name'] ?? '') . ".");
        } else {
            $this->setFlash('danger', 'Santri dan kelas tujuan wajib dipilih.');
        }

        $this->redirect($redirectUrl);
    }

    public function removeStudentClass(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $studentId = (int)($_POST['student_id'] ?? 0);
        $taId = Auth::academicYearId();
        $redirectUrl = $_POST['redirect_url'] ?? (Auth::baseUrl() . '/classes/placement');

        if ($studentId > 0) {
            $student = Database::fetch("SELECT nama FROM students WHERE id = ?", [$studentId]);
            Database::execute(
                "DELETE FROM class_students WHERE student_id = ? AND academic_year_id = ?",
                [$studentId, $taId]
            );
            AuditLog::log('REMOVE_STUDENT_CLASS', 'class_students', $studentId);
            $this->setFlash('success', "Santri " . ($student['nama'] ?? '') . " berhasil dikeluarkan dari kelas.");
        } else {
            $this->setFlash('danger', 'ID Santri tidak valid.');
        }

        $this->redirect($redirectUrl);
    }

    public function batchPlacement(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $studentIds = $_POST['student_ids'] ?? [];
        $actionType = $_POST['action_type'] ?? 'assign'; // assign / remove
        $targetClassId = (int)($_POST['target_class_id'] ?? 0);
        $taId = Auth::academicYearId();
        $redirectUrl = $_POST['redirect_url'] ?? (Auth::baseUrl() . '/classes/placement');

        if (empty($studentIds) || !is_array($studentIds)) {
            $this->setFlash('danger', 'Pilih minimal satu santri untuk diproses.');
            $this->redirect($redirectUrl);
            return;
        }

        $count = count($studentIds);
        if ($actionType === 'remove') {
            foreach ($studentIds as $sid) {
                Database::execute(
                    "DELETE FROM class_students WHERE student_id = ? AND academic_year_id = ?",
                    [(int)$sid, $taId]
                );
            }
            $this->setFlash('success', "Sebanyak {$count} santri berhasil dikeluarkan dari kelas.");
        } elseif ($actionType === 'assign' && $targetClassId > 0) {
            $class = Database::fetch("SELECT name FROM classes WHERE id = ?", [$targetClassId]);
            foreach ($studentIds as $sid) {
                Database::execute(
                    "INSERT INTO class_students (class_id, student_id, academic_year_id, status) 
                     VALUES (?, ?, ?, 'Aktif') 
                     ON DUPLICATE KEY UPDATE class_id = VALUES(class_id), status = 'Aktif'",
                    [$targetClassId, (int)$sid, $taId]
                );
            }
            $this->setFlash('success', "Sebanyak {$count} santri berhasil dimasukkan ke kelas " . ($class['name'] ?? '') . ".");
        } else {
            $this->setFlash('danger', 'Aksi atau kelas tujuan tidak valid.');
        }

        $this->redirect($redirectUrl);
    }

    public function subjects(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_GET['unit_id'] ?? Auth::unitId());

        $subjects = Database::fetchAll("SELECT * FROM subjects WHERE unit_id = ? ORDER BY type ASC, name ASC", [$unitId]);
        $teachers = Database::fetchAll(
            "SELECT id, nama, level_posisi, position FROM employees 
             WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif'
               AND (position LIKE '%Guru%' OR level_posisi LIKE '%Guru%' OR position LIKE '%Waka%' OR level_posisi LIKE '%Waka%' OR position LIKE '%Tutor%' OR level_posisi LIKE '%Tutor%')
               AND level_posisi NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
               AND position NOT IN ('Direktur Pendidikan', 'Deputi Direktur', 'Kepala Sekolah', 'Tata Usaha Keuangan', 'Cleaning Services', 'Security', 'Operator Sekolah')
             ORDER BY nama ASC", 
            [$unitId]
        );
        $classes = Database::fetchAll("SELECT id, name FROM classes WHERE unit_id = ? AND academic_year_id = ? ORDER BY name ASC", [$unitId, Auth::academicYearId()]);
        
        $assignments = Database::fetchAll(
            "SELECT ts.id, ts.employee_id, ts.subject_id, ts.class_id, ts.academic_year_id,
                    e.nama as teacher_name, e.nipy as teacher_nipy, e.position as teacher_position,
                    s.name as subject_name, s.code as subject_code, s.type as subject_type,
                    c.name as class_name
             FROM teacher_subjects ts
             JOIN subjects s ON ts.subject_id = s.id
             JOIN employees e ON ts.employee_id = e.id AND e.unit_id = s.unit_id AND e.is_deleted = 0 AND e.status = 'Aktif'
             JOIN classes c ON ts.class_id = c.id
             WHERE s.unit_id = ? AND ts.academic_year_id = ?
             ORDER BY c.name ASC, s.name ASC, e.nama ASC",
            [$unitId, Auth::academicYearId()]
        );

        $this->view('master/subjects', [
            'pageTitle'   => 'Mata Pelajaran & Penugasan Guru Pengampu',
            'subjects'    => $subjects,
            'teachers'    => $teachers,
            'classes'     => $classes,
            'assignments' => $assignments,
            'unitId'      => $unitId
        ]);
    }

    public function saveSubject(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $id = (int)($_POST['id'] ?? 0);
        $code = trim($_POST['code'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $type = $_POST['type'] ?? 'Umum';
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_POST['unit_id'] ?? Auth::unitId());

        if ($id > 0) {
            if (Auth::hasRole('operator_sekolah')) {
                $checkSub = Database::fetch("SELECT id FROM subjects WHERE id = ? AND unit_id = ?", [$id, $unitId]);
                if (!$checkSub) {
                    $this->setFlash('danger', 'Akses Ditolak: Anda tidak dapat mengubah mata pelajaran unit sekolah lain.');
                    $this->redirect(Auth::baseUrl() . '/subjects');
                    return;
                }
            }
            Database::execute("UPDATE subjects SET code = ?, name = ?, type = ?, updated_at = NOW() WHERE id = ?", [$code, $name, $type, $id]);
            AuditLog::log('UPDATE_SUBJECT', 'subjects', $id);
            $this->setFlash('success', 'Mata pelajaran berhasil diperbarui.');
        } else {
            Database::execute("INSERT INTO subjects (unit_id, code, name, type) VALUES (?, ?, ?, ?)", [$unitId, $code, $name, $type]);
            AuditLog::log('CREATE_SUBJECT', 'subjects', Database::lastInsertId());
            $this->setFlash('success', 'Mata pelajaran baru berhasil ditambahkan.');
        }
        $this->redirect(Auth::baseUrl() . '/subjects');
    }

    public function assignTeacherSubject(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_POST['unit_id'] ?? Auth::unitId());
        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $subjectId = (int)($_POST['subject_id'] ?? 0);
        $classId = (int)($_POST['class_id'] ?? 0);
        $taId = Auth::academicYearId();

        if ($employeeId && $subjectId && $classId) {
            // Validasi isolasi unit: guru, mapel, dan kelas harus sama-sama berada di unit sekolah yang bersangkutan
            $emp = Database::fetch("SELECT id, nama FROM employees WHERE id = ? AND unit_id = ? AND is_deleted = 0", [$employeeId, $unitId]);
            $sub = Database::fetch("SELECT id, name FROM subjects WHERE id = ? AND unit_id = ?", [$subjectId, $unitId]);
            $cls = Database::fetch("SELECT id, name FROM classes WHERE id = ? AND unit_id = ?", [$classId, $unitId]);

            if (!$emp || !$sub || !$cls) {
                $this->setFlash('danger', 'Gagal menugaskan: Guru, Mata Pelajaran, atau Kelas yang dipilih bukan berasal dari unit sekolah Anda.');
                $this->redirect(Auth::baseUrl() . '/subjects');
                return;
            }

            $existing = Database::fetch(
                "SELECT id FROM teacher_subjects WHERE subject_id = ? AND class_id = ? AND academic_year_id = ?",
                [$subjectId, $classId, $taId]
            );

            if ($existing) {
                Database::execute("UPDATE teacher_subjects SET employee_id = ? WHERE id = ?", [$employeeId, $existing['id']]);
                AuditLog::log('ASSIGN_TEACHER_SUBJECT', 'teacher_subjects', $existing['id'], null, [
                    'employee' => $emp['nama'],
                    'subject'  => $sub['name'],
                    'class'    => $cls['name']
                ]);
            } else {
                Database::execute(
                    "INSERT INTO teacher_subjects (employee_id, subject_id, class_id, academic_year_id) VALUES (?, ?, ?, ?)",
                    [$employeeId, $subjectId, $classId, $taId]
                );
                AuditLog::log('ASSIGN_TEACHER_SUBJECT', 'teacher_subjects', Database::lastInsertId(), null, [
                    'employee' => $emp['nama'],
                    'subject'  => $sub['name'],
                    'class'    => $cls['name']
                ]);
            }

            $this->setFlash('success', "Berhasil menugaskan {$emp['nama']} mengampu mapel {$sub['name']} di kelas {$cls['name']}.");
        } else {
            $this->setFlash('danger', 'Mohon lengkapi semua isian penugasan guru, mapel, dan kelas.');
        }
        $this->redirect(Auth::baseUrl() . '/subjects');
    }

    public function unassignTeacherSubject(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $assignmentId = (int)($_POST['assignment_id'] ?? 0);
        $unitId = Auth::hasRole('operator_sekolah') ? Auth::unitId() : (int)($_POST['unit_id'] ?? Auth::unitId());

        if ($assignmentId > 0) {
            $ts = Database::fetch(
                "SELECT ts.id, ts.employee_id, e.nama as teacher_name, s.name as subject_name, c.name as class_name
                 FROM teacher_subjects ts
                 JOIN employees e ON ts.employee_id = e.id
                 JOIN subjects s ON ts.subject_id = s.id
                 JOIN classes c ON ts.class_id = c.id
                 WHERE ts.id = ? AND s.unit_id = ?",
                [$assignmentId, $unitId]
            );

            if ($ts) {
                Database::execute("DELETE FROM teacher_subjects WHERE id = ?", [$assignmentId]);
                AuditLog::log('UNASSIGN_TEACHER_SUBJECT', 'teacher_subjects', $assignmentId, null, [
                    'teacher' => $ts['teacher_name'],
                    'subject' => $ts['subject_name'],
                    'class'   => $ts['class_name']
                ]);
                $this->setFlash('success', "Penugasan {$ts['teacher_name']} untuk mapel {$ts['subject_name']} di kelas {$ts['class_name']} berhasil dibatalkan (unassigned).");
            } else {
                $this->setFlash('danger', 'Data penugasan tidak ditemukan atau bukan milik unit sekolah Anda.');
            }
        }
        $this->redirect(Auth::baseUrl() . '/subjects');
    }

    // --- 7. AKUN PENGGUNA SEKOLAH (GURU, KARYAWAN, OPERATOR SEKOLAH) ---
    public function schoolAccounts(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan']);
        
        $isYayasan = Auth::hasRole('operator_yayasan') || Auth::hasRole('direktur_pendidikan') || Auth::hasRole('deputi_pendidikan');
        $allUnits = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        
        // Multi-Unit Isolation:
        // Jika Operator Sekolah, unit_id dikunci mutlak ke Auth::unitId()
        if (!$isYayasan) {
            $unitId = Auth::unitId();
            $unitFilter = $unitId;
        } else {
            // Operator Yayasan bisa memilih unit (default semua atau unit tertentu)
            $unitFilter = isset($_GET['unit_id']) && $_GET['unit_id'] !== '' ? (int)$_GET['unit_id'] : null;
            $unitId = $unitFilter ?: Auth::unitId();
        }

        $query = "SELECT u.id, u.username, u.full_name, u.email, u.phone, u.unit_id as user_unit_id, u.is_active, u.last_login_at, 
                         GROUP_CONCAT(CONCAT(r.id, ':', r.name, ':', r.display_name) SEPARATOR '; ') as role_data,
                         GROUP_CONCAT(r.display_name SEPARATOR ', ') as role_names,
                         e.id as employee_id, e.level_posisi, e.nipy,
                         COALESCE(u_unit.name, e_unit.name) as unit_name,
                         COALESCE(u.unit_id, e.unit_id) as actual_unit_id
                  FROM users u 
                  JOIN user_roles ur ON u.id = ur.user_id 
                  JOIN roles r ON ur.role_id = r.id 
                  LEFT JOIN employees e ON u.id = e.user_id 
                  LEFT JOIN units u_unit ON u.unit_id = u_unit.id
                  LEFT JOIN units e_unit ON e.unit_id = e_unit.id
                  WHERE 1=1 ";
        
        $params = [];
        if ($unitFilter !== null) {
            $query .= "AND (u.unit_id = ? OR e.unit_id = ?) ";
            $params[] = $unitFilter;
            $params[] = $unitFilter;
        } elseif (!$isYayasan) {
            $query .= "AND (u.unit_id = ? OR e.unit_id = ?) ";
            $params[] = $unitId;
            $params[] = $unitId;
        }

        // Jangan tampilkan akun yayasan ke Operator Sekolah
        if (!$isYayasan) {
            $query .= "AND r.name NOT IN ('operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan') ";
        }

        $query .= "GROUP BY u.id ORDER BY actual_unit_id ASC, u.full_name ASC";
        $users = Database::fetchAll($query, $params);

        // Ambil daftar peran (roles)
        if ($isYayasan) {
            $roles = Database::fetchAll("SELECT * FROM roles WHERE name NOT IN ('operator_yayasan', 'direktur_pendidikan', 'pembina_yayasan') ORDER BY display_name ASC");
        } else {
            $roles = Database::fetchAll("SELECT * FROM roles WHERE name NOT IN ('operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan', 'operator_sekolah') ORDER BY display_name ASC");
        }

        // Ambil daftar pegawai yang sudah ada untuk pembuatan akun tanpa input nama baru
        $empSql = "SELECT e.id, e.nama, e.level_posisi, e.nipy, e.unit_id, e.email, e.contact_phone, e.user_id, u.name as unit_name, usr.username
                   FROM employees e
                   LEFT JOIN units u ON e.unit_id = u.id
                   LEFT JOIN users usr ON e.user_id = usr.id
                   WHERE e.is_deleted = 0 AND e.status = 'Aktif'";
        $empParams = [];
        if (!$isYayasan) {
            $empSql .= " AND e.unit_id = ?";
            $empParams[] = $unitId;
        }
        $empSql .= " ORDER BY e.unit_id ASC, e.nama ASC";
        $existingEmployees = Database::fetchAll($empSql, $empParams);

        $this->view('master/school_accounts', [
            'pageTitle'         => $isYayasan ? 'Kelola Akun Operator & Pengguna Seluruh Satuan Pendidikan' : 'Kelola Akun Guru & Karyawan Sekolah',
            'users'             => $users,
            'roles'             => $roles,
            'allUnits'          => $allUnits,
            'unitFilter'        => $unitFilter,
            'isYayasan'         => $isYayasan,
            'currentUnit'       => $unitId,
            'existingEmployees' => $existingEmployees
        ]);
    }

    public function createSchoolAccount(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $isYayasan = Auth::hasRole('operator_yayasan');

        $employeeId = (int)($_POST['employee_id'] ?? 0);
        $fullName = trim($_POST['full_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? 'password123');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $posisi = trim($_POST['level_posisi'] ?? 'Guru Mapel');
        $selectedRoles = $_POST['roles'] ?? [];

        if ($isYayasan) {
            $targetUnitId = (int)($_POST['unit_id'] ?? 0);
        } else {
            $targetUnitId = Auth::unitId();
        }

        $forbiddenRoles = $isYayasan 
            ? ['operator_yayasan', 'direktur_pendidikan', 'pembina_yayasan'] 
            : ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan', 'operator_sekolah'];

        $validRoleIds = [];
        foreach ($selectedRoles as $rId) {
            $r = Database::fetch("SELECT * FROM roles WHERE id = ?", [(int)$rId]);
            if ($r && !in_array($r['name'], $forbiddenRoles)) {
                $validRoleIds[] = (int)$r['id'];
            }
        }

        if (empty($validRoleIds)) {
            $this->setFlash('danger', 'Pilih minimal satu peran (role) yang valid untuk akun ini.');
            $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
            return;
        }

        // Operator sekolah WAJIB memilih dari daftar pegawai yang ada
        if (!$isYayasan && $employeeId <= 0) {
            $this->setFlash('danger', 'Operator Sekolah wajib memilih pegawai dari daftar pegawai yang ada.');
            $this->redirect(Auth::baseUrl() . '/users/school-accounts');
            return;
        }

        // KASUS 1: Mengambil dari daftar pegawai yang sudah ada (tidak membuat baris pegawai duplikat)
        if ($employeeId > 0) {
            $empQuery = "SELECT * FROM employees WHERE id = ? AND is_deleted = 0";
            $empParams = [$employeeId];
            if (!$isYayasan) {
                $empQuery .= " AND unit_id = ?";
                $empParams[] = Auth::unitId();
            }
            $emp = Database::fetch($empQuery, $empParams);
            if ($emp) {
                $targetUnitId = (int)$emp['unit_id'];
                $fullName = $emp['nama'];
                $email = $emp['email'] ?? '';
                $phone = $emp['contact_phone'] ?? '';
                // Username auto-generated dari NIPY
                if (!empty($emp['nipy'])) {
                    $username = strtolower(trim($emp['nipy']));
                }

                // A. Pegawai sudah memiliki akun login -> Tambahkan role ke akun ini
                if (!empty($emp['user_id'])) {
                    $userId = (int)$emp['user_id'];
                    foreach ($validRoleIds as $rId) {
                        Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 0)", [$userId, $rId]);
                    }
                    if (!empty($password) && $password !== 'password123') {
                        Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [password_hash($password, PASSWORD_DEFAULT), $userId]);
                    }
                    AuditLog::log('ASSIGN_ROLE_TO_EXISTING_EMPLOYEE', 'users', $userId, null, ['employee_id' => $employeeId, 'roles' => $validRoleIds]);
                    $this->setFlash('success', "Peran baru berhasil ditambahkan untuk akun {$emp['nama']}.");
                    $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
                    return;
                }

                // B. Pegawai belum memiliki akun login -> Buatkan akun login dan hubungkan ke ID pegawai ini
                if (empty($username)) {
                    $this->setFlash('danger', 'Username login wajib diisi.');
                    $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
                    return;
                }

                $exists = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
                if ($exists) {
                    $this->setFlash('danger', "Username '{$username}' sudah digunakan. Silakan gunakan username lain.");
                    $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
                    return;
                }

                $passwordHash = password_hash($password, PASSWORD_DEFAULT);
                Database::execute(
                    "INSERT INTO users (username, password_hash, full_name, email, phone, unit_id, is_active, force_change_password) 
                     VALUES (?, ?, ?, ?, ?, ?, 1, 0)",
                    [$username, $passwordHash, $fullName, $email ?: null, $phone ?: null, $targetUnitId]
                );
                $newUserId = (int)Database::lastInsertId();

                foreach ($validRoleIds as $idx => $rId) {
                    $isPrimary = $idx === 0 ? 1 : 0;
                    Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, ?)", [$newUserId, $rId, $isPrimary]);
                }

                // Tautkan user_id ke baris pegawai yang sudah ada tanpa insert pegawai baru!
                Database::execute("UPDATE employees SET user_id = ?, updated_at = NOW() WHERE id = ?", [$newUserId, $employeeId]);

                AuditLog::log('LINK_USER_TO_EMPLOYEE', 'employees', $employeeId, null, ['user_id' => $newUserId, 'username' => $username]);
                $this->setFlash('success', "Akun Operator Sekolah berhasil dibuat dan ditautkan ke data pegawai {$fullName}.");
                $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
                return;
            }
        }

        // KASUS 2: Input manual akun dan pegawai baru dari awal
        if ($isYayasan && $targetUnitId <= 0) {
            $this->setFlash('danger', 'Unit sekolah wajib dipilih.');
            $this->redirect(Auth::baseUrl() . '/users/school-accounts');
            return;
        }

        if (empty($fullName) || empty($username)) {
            $this->setFlash('danger', 'Nama lengkap dan username wajib diisi.');
            $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
            return;
        }

        $exists = Database::fetch("SELECT id FROM users WHERE username = ?", [$username]);
        if ($exists) {
            $this->setFlash('danger', "Username '{$username}' sudah digunakan. Silakan gunakan username lain.");
            $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
            return;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        // 1. Insert user
        Database::execute(
            "INSERT INTO users (username, password_hash, full_name, email, phone, unit_id, is_active, force_change_password) 
             VALUES (?, ?, ?, ?, ?, ?, 1, 0)",
            [$username, $passwordHash, $fullName, $email ?: null, $phone ?: null, $targetUnitId]
        );
        $newUserId = (int)Database::lastInsertId();

        // 2. Insert user_roles
        foreach ($validRoleIds as $idx => $rId) {
            $isPrimary = $idx === 0 ? 1 : 0;
            Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, ?)", [$newUserId, $rId, $isPrimary]);
        }

        // 3. Insert employees
        $unitRow = Database::fetch("SELECT code FROM units WHERE id = ?", [$targetUnitId]);
        $uCode = $unitRow['code'] ?? 'SCH';
        $nipy = $uCode . '-' . strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $username), 0, 4)) . '-' . rand(100, 999);

        Database::execute(
            "INSERT INTO employees (user_id, nipy, nama, gender, tmt, level_posisi, unit_id, contact_phone, email, status) 
             VALUES (?, ?, ?, 'L', CURDATE(), ?, ?, ?, ?, 'Aktif')",
            [$newUserId, $nipy, $fullName, $posisi, $targetUnitId, $phone ?: null, $email ?: null]
        );

        AuditLog::log('CREATE_USER_ACCOUNT', 'users', $newUserId, null, [
            'username' => $username,
            'unit_id'  => $targetUnitId,
            'roles'    => $validRoleIds
        ]);

        $this->setFlash('success', "Akun pengguna '{$username}' berhasil dibuat untuk unit {$uCode}.");
        $this->redirect(Auth::baseUrl() . '/users/school-accounts' . ($isYayasan ? "?unit_id={$targetUnitId}" : ''));
    }

    public function assignRole(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $isYayasan = Auth::hasRole('operator_yayasan');
        $userId = (int)($_POST['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);

        $targetUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        $targetRole = Database::fetch("SELECT * FROM roles WHERE id = ?", [$roleId]);

        if (!$targetUser || !$targetRole) {
            $this->setFlash('danger', 'Data pengguna atau peran tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/users/school-accounts');
            return;
        }

        if (!$isYayasan) {
            $userEmp = Database::fetch("SELECT unit_id FROM employees WHERE user_id = ?", [$userId]);
            $userUnitId = $targetUser['unit_id'] ?: ($userEmp['unit_id'] ?? 0);
            if ($userUnitId != Auth::unitId()) {
                $this->setFlash('danger', 'Anda tidak memiliki otoritas mengubah akun di unit sekolah lain.');
                $this->redirect(Auth::baseUrl() . '/users/school-accounts');
                return;
            }

            $forbidden = ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan', 'operator_sekolah'];
            if (in_array($targetRole['name'], $forbidden)) {
                $this->setFlash('danger', 'Anda tidak memiliki hak memberikan peran tersebut.');
                $this->redirect(Auth::baseUrl() . '/users/school-accounts');
                return;
            }
        }

        Database::execute(
            "INSERT INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 0) 
             ON DUPLICATE KEY UPDATE role_id = VALUES(role_id)",
            [$userId, $roleId]
        );

        AuditLog::log('ASSIGN_ROLE', 'user_roles', $userId, null, ['role_id' => $roleId, 'role_name' => $targetRole['name']]);
        $this->setFlash('success', "Peran '{$targetRole['display_name']}' berhasil ditambahkan ke akun {$targetUser['username']}.");
        $this->redirect(Auth::baseUrl() . '/users/school-accounts');
    }

    public function unassignRole(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $isYayasan = Auth::hasRole('operator_yayasan');
        $userId = (int)($_POST['user_id'] ?? 0);
        $roleId = (int)($_POST['role_id'] ?? 0);

        $targetUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);
        $targetRole = Database::fetch("SELECT * FROM roles WHERE id = ?", [$roleId]);

        if (!$targetUser || !$targetRole) {
            $this->setFlash('danger', 'Data pengguna atau peran tidak ditemukan.');
            $this->redirect(Auth::baseUrl() . '/users/school-accounts');
            return;
        }

        if (!$isYayasan) {
            $userEmp = Database::fetch("SELECT unit_id FROM employees WHERE user_id = ?", [$userId]);
            $userUnitId = $targetUser['unit_id'] ?: ($userEmp['unit_id'] ?? 0);
            if ($userUnitId != Auth::unitId()) {
                $this->setFlash('danger', 'Anda tidak memiliki otoritas mengubah akun di unit sekolah lain.');
                $this->redirect(Auth::baseUrl() . '/users/school-accounts');
                return;
            }

            $forbidden = ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan', 'operator_sekolah'];
            if (in_array($targetRole['name'], $forbidden)) {
                $this->setFlash('danger', 'Anda tidak memiliki hak mencabut peran tersebut.');
                $this->redirect(Auth::baseUrl() . '/users/school-accounts');
                return;
            }
        }

        $roleCount = Database::fetch("SELECT COUNT(*) as cnt FROM user_roles WHERE user_id = ?", [$userId]);
        if (($roleCount['cnt'] ?? 0) <= 1) {
            $this->setFlash('warning', 'Peran tidak dapat dicabut karena pengguna wajib memiliki minimal satu peran aktif.');
            $this->redirect(Auth::baseUrl() . '/users/school-accounts');
            return;
        }

        Database::execute("DELETE FROM user_roles WHERE user_id = ? AND role_id = ?", [$userId, $roleId]);

        $hasPrimary = Database::fetch("SELECT role_id FROM user_roles WHERE user_id = ? AND is_primary = 1", [$userId]);
        if (!$hasPrimary) {
            Database::execute("UPDATE user_roles SET is_primary = 1 WHERE user_id = ? LIMIT 1", [$userId]);
        }

        AuditLog::log('UNASSIGN_ROLE', 'user_roles', $userId, null, ['role_id' => $roleId, 'role_name' => $targetRole['name']]);
        $this->setFlash('success', "Peran '{$targetRole['display_name']}' berhasil dicabut dari akun {$targetUser['username']}.");
        $this->redirect(Auth::baseUrl() . '/users/school-accounts');
    }

    public function saveSchoolAccount(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $id = (int)($_POST['id'] ?? 0);
        $action = $_POST['action'] ?? 'reset_password';
        $returnTo = $_POST['return_to'] ?? '';
        $redirectUrl = ($returnTo === 'employees') 
            ? Auth::baseUrl() . '/master/employees' 
            : Auth::baseUrl() . '/users/school-accounts';

        if ($id > 0 && $action === 'reset_password') {
            $newHash = password_hash('password123', PASSWORD_DEFAULT);
            Database::execute("UPDATE users SET password_hash = ?, force_change_password = 1 WHERE id = ?", [$newHash, $id]);
            AuditLog::log('RESET_USER_PASSWORD', 'users', $id);
            $this->setFlash('success', 'Kata sandi akun berhasil direset ke "password123". Pengguna wajib mengganti password saat login.');
        } elseif ($id > 0 && $action === 'toggle_active') {
            $u = Database::fetch("SELECT is_active FROM users WHERE id = ?", [$id]);
            $newStatus = $u['is_active'] ? 0 : 1;
            Database::execute("UPDATE users SET is_active = ? WHERE id = ?", [$newStatus, $id]);
            AuditLog::log('TOGGLE_USER_ACTIVE', 'users', $id, null, ['is_active' => $newStatus]);
            $this->setFlash('success', 'Status akun berhasil diubah.');
        } elseif ($id > 0 && $action === 'edit_account') {
            $isYayasan = Auth::hasRole('operator_yayasan');
            $targetUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
            if (!$targetUser) {
                $this->setFlash('danger', 'Data akun pengguna tidak ditemukan.');
                $this->redirect($redirectUrl);
                return;
            }

            // Otoritas unit untuk Operator Sekolah
            if (!$isYayasan) {
                $userEmp = Database::fetch("SELECT unit_id FROM employees WHERE user_id = ?", [$id]);
                $userUnitId = $targetUser['unit_id'] ?: ($userEmp['unit_id'] ?? 0);
                if ($userUnitId != Auth::unitId()) {
                    $this->setFlash('danger', 'Anda tidak memiliki hak mengubah data akun di unit sekolah lain.');
                    $this->redirect($redirectUrl);
                    return;
                }
            }

            if (!$isYayasan) {
                // Username, nama lengkap, alamat email, no hp, unit dikunci untuk operator sekolah
                $username = $targetUser['username'];
                $fullName = $targetUser['full_name'];
                $email = $targetUser['email'];
                $phone = $targetUser['phone'];
                $unitId = (int)$targetUser['unit_id'];
            } else {
                $username = trim($_POST['username'] ?? '');
                $fullName = trim($_POST['full_name'] ?? '');
                $email = trim($_POST['email'] ?? '');
                $phone = trim($_POST['phone'] ?? '');
                $unitId = !empty($_POST['unit_id']) ? (int)$_POST['unit_id'] : (int)$targetUser['unit_id'];
            }
            $password = trim($_POST['password'] ?? '');
            $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : (int)$targetUser['is_active'];

            if (empty($username) || empty($fullName)) {
                $this->setFlash('danger', 'Nama lengkap dan username tidak boleh kosong.');
                $this->redirect($redirectUrl);
                return;
            }

            // Periksa keunikan username
            $checkUser = Database::fetch("SELECT id FROM users WHERE username = ? AND id != ?", [$username, $id]);
            if ($checkUser) {
                $this->setFlash('danger', "Username '{$username}' sudah digunakan oleh pengguna lain.");
                $this->redirect($redirectUrl);
                return;
            }

            $updateParams = [$username, $fullName, $email ?: null, $phone ?: null, $isActive];
            $sqlExtra = "";
            if ($isYayasan && $unitId > 0) {
                $sqlExtra .= ", unit_id = ?";
                $updateParams[] = $unitId;
            }
            if (!empty($password)) {
                $sqlExtra .= ", password_hash = ?";
                $updateParams[] = password_hash($password, PASSWORD_DEFAULT);
            }
            $updateParams[] = $id;

            Database::execute(
                "UPDATE users SET username = ?, full_name = ?, email = ?, phone = ?, is_active = ?, updated_at = NOW() {$sqlExtra} WHERE id = ?",
                $updateParams
            );

            // Sinkronisasi data ke tabel employees jika akun ini ditautkan
            $empUpdateSql = "UPDATE employees SET nama = ?, email = ?, contact_phone = ?, updated_at = NOW()";
            $empParams = [$fullName, $email ?: null, $phone ?: null];
            if ($isYayasan && $unitId > 0) {
                $empUpdateSql .= ", unit_id = ?";
                $empParams[] = $unitId;
            }
            $empUpdateSql .= " WHERE user_id = ?";
            $empParams[] = $id;
            Database::execute($empUpdateSql, $empParams);

            // Kelola peran jika dikirimkan
            if (isset($_POST['roles']) && is_array($_POST['roles'])) {
                $forbiddenRoles = $isYayasan 
                    ? ['direktur_pendidikan', 'pembina_yayasan'] 
                    : ['direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan', 'operator_yayasan', 'operator_sekolah'];

                $selectedRoles = $_POST['roles'];
                $validRoleIds = [];
                foreach ($selectedRoles as $rId) {
                    $r = Database::fetch("SELECT * FROM roles WHERE id = ?", [(int)$rId]);
                    if ($r && !in_array($r['name'], $forbiddenRoles)) {
                        $validRoleIds[] = (int)$r['id'];
                    }
                }

                if (!empty($validRoleIds)) {
                    Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$id]);
                    foreach ($validRoleIds as $idx => $rId) {
                        $isPri = $idx === 0 ? 1 : 0;
                        Database::execute("INSERT IGNORE INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, ?)", [$id, $rId, $isPri]);
                    }
                }
            }

            AuditLog::log('EDIT_USER_ACCOUNT', 'users', $id, $targetUser, $_POST);
            $this->setFlash('success', "Data akun dan profil pengguna '{$username}' berhasil diperbarui.");
        }
        $this->redirect($redirectUrl);
    }

    public function deleteSchoolAccount(): void {
        $this->requireRole('operator_yayasan');
        $id = (int)($_POST['id'] ?? 0);
        $returnTo = $_POST['return_to'] ?? '';
        $redirectUrl = ($returnTo === 'employees') 
            ? Auth::baseUrl() . '/master/employees' 
            : Auth::baseUrl() . '/users/school-accounts';

        if ($id <= 0) {
            $this->setFlash('danger', 'ID pengguna tidak valid.');
            $this->redirect($redirectUrl);
            return;
        }

        if ($id === (int)Auth::id()) {
            $this->setFlash('danger', 'Anda tidak dapat menghapus akun Anda sendiri.');
            $this->redirect($redirectUrl);
            return;
        }

        $targetUser = Database::fetch("SELECT * FROM users WHERE id = ?", [$id]);
        if (!$targetUser) {
            $this->setFlash('danger', 'Akun pengguna tidak ditemukan.');
            $this->redirect($redirectUrl);
            return;
        }

        $targetRoles = Database::fetchAll(
            "SELECT r.name FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?",
            [$id]
        );
        $roleNames = array_column($targetRoles, 'name');
        if (array_intersect($roleNames, ['operator_yayasan', 'direktur_pendidikan', 'deputi_pendidikan', 'pembina_yayasan'])) {
            $this->setFlash('danger', 'Akun dengan peran pengurus yayasan/direksi tidak dapat dihapus melalui menu ini.');
            $this->redirect($redirectUrl);
            return;
        }

        Database::execute("DELETE FROM user_roles WHERE user_id = ?", [$id]);
        Database::execute("UPDATE employees SET user_id = NULL WHERE user_id = ?", [$id]);
        Database::execute("DELETE FROM users WHERE id = ?", [$id]);

        AuditLog::log('DELETE_USER_ACCOUNT', 'users', $id, $targetUser, ['username' => $targetUser['username']]);
        $this->setFlash('success', "Akun operator/pengguna '{$targetUser['username']}' berhasil dihapus permanen.");
        $this->redirect($redirectUrl);
    }

    // --- 8. SETTING SMTP & TEMPLATE SURAT PENAGIHAN ---
    public function smtpSettings(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::unitId();
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);

        $this->view('master/smtp_settings', [
            'pageTitle' => 'Pengaturan SMTP & Template Surat Penagihan SPP',
            'unit'      => $unit
        ]);
    }

    public function saveSmtpSettings(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $unitId = Auth::unitId();
        $smtpHost = trim($_POST['smtp_host'] ?? '');
        $smtpPort = (int)($_POST['smtp_port'] ?? 587);
        $smtpUser = trim($_POST['smtp_user'] ?? '');
        $smtpPass = trim($_POST['smtp_pass'] ?? '');
        $smtpEnc = $_POST['smtp_enc'] ?? 'tls';
        $template = trim($_POST['letter_template'] ?? '');

        // Simpan password SMTP secara terenkripsi
        $encryptedPass = !empty($smtpPass) ? SecurityHelper::encrypt($smtpPass) : null;

        $sql = "UPDATE units SET smtp_host = ?, smtp_port = ?, smtp_user = ?, smtp_enc = ?, letter_template = ?";
        $params = [$smtpHost, $smtpPort, $smtpUser, $smtpEnc, $template];

        if ($encryptedPass !== null) {
            $sql .= ", smtp_pass = ?";
            $params[] = $encryptedPass;
        }
        $sql .= " WHERE id = ?";
        $params[] = $unitId;

        Database::execute($sql, $params);
        AuditLog::log('UPDATE_SMTP_SETTINGS', 'units', $unitId);
        $this->setFlash('success', 'Pengaturan SMTP dan template surat berhasil disimpan.');
        $this->redirect(Auth::baseUrl() . '/settings/smtp');
    }

    public function testSmtp(): void {
        $this->requireRole(['operator_sekolah', 'operator_yayasan']);
        $targetEmail = trim($_POST['test_email'] ?? '');
        $unitId = Auth::unitId();
        $unit = Database::fetch("SELECT * FROM units WHERE id = ?", [$unitId]);

        if (empty($targetEmail) || empty($unit['smtp_host'])) {
            $this->setFlash('error', 'Konfigurasi SMTP belum lengkap atau email tujuan kosong.');
            $this->redirect(Auth::baseUrl() . '/settings/smtp');
            return;
        }

        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = $unit['smtp_host'];
            $mail->SMTPAuth   = true;
            $mail->Username   = $unit['smtp_user'];
            $mail->Password   = SecurityHelper::decrypt($unit['smtp_pass']) ?: '';
            $mail->SMTPSecure = $unit['smtp_enc'] === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$unit['smtp_port'];

            $mail->setFrom($unit['smtp_user'], $unit['name']);
            $mail->addAddress($targetEmail);
            $mail->isHTML(true);
            $mail->Subject = 'Uji Coba Pengiriman Email SMTP - ' . $unit['name'];
            $mail->Body    = '<h3>Alhamdulillah, Pengiriman Email Berhasil!</h3><p>Server SMTP unit ' . htmlspecialchars($unit['name']) . ' telah terkonfigurasi dengan benar.</p>';

            $mail->send();
            $this->setFlash('success', "Email uji coba berhasil dikirim ke: {$targetEmail}");
        } catch (\Exception $e) {
            $this->setFlash('error', 'Gagal mengirim email: ' . $mail->ErrorInfo);
        }
        $this->redirect(Auth::baseUrl() . '/settings/smtp');
    }

    // --- 9. PENGATURAN IDENTITAS & LOGO APLIKASI (MASEKO) ---
    public function appSettings(): void {
        $this->requireRole('operator_yayasan');

        $this->view('master/app_settings', [
            'pageTitle' => 'Identitas & Logo Aplikasi (MASEKO)',
            'settings'  => Setting::all()
        ]);
    }

    public function saveAppSettings(): void {
        $this->requireRole('operator_yayasan');

        $appName = trim($_POST['app_name'] ?? 'Manajemen Sekolah Berbasis Komputer (MASEKO)');
        $appShortName = trim($_POST['app_short_name'] ?? 'MASEKO');
        $appSubName = trim($_POST['app_sub_name'] ?? 'Manajemen Sekolah Berbasis Komputer');
        $deleteLogo = !empty($_POST['delete_logo']);

        Setting::set('app_name', $appName);
        Setting::set('app_short_name', $appShortName);
        Setting::set('app_sub_name', $appSubName);

        $currentLogo = Setting::get('app_logo', '');

        // Hapus logo jika dipilih
        if ($deleteLogo) {
            if (!empty($currentLogo)) {
                $oldPath = dirname(__DIR__, 2) . '/public/' . ltrim($currentLogo, '/');
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }
            Setting::set('app_logo', '');
            AuditLog::log('DELETE_APP_LOGO', 'app_settings', 0, ['logo' => $currentLogo], []);
        }

        // Upload logo baru
        if (!empty($_FILES['app_logo']['tmp_name']) && $_FILES['app_logo']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['app_logo'];

            if ($file['size'] > 2 * 1024 * 1024) {
                $this->setFlash('error', 'Ukuran berkas logo terlalu besar (maksimal 2MB).');
                $this->redirect(Auth::baseUrl() . '/settings/app');
                return;
            }

            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowedExts = ['png', 'jpg', 'jpeg', 'webp', 'svg', 'ico'];

            if (!in_array($ext, $allowedExts, true)) {
                $this->setFlash('error', 'Format berkas logo tidak valid. Hanya diperbolehkan JPG, PNG, WEBP, SVG, atau ICO.');
                $this->redirect(Auth::baseUrl() . '/settings/app');
                return;
            }

            // Validasi MIME type jika finfo tersedia
            if (class_exists('\finfo')) {
                $finfo = new \finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($file['tmp_name']) ?: '';
                $validMimes = [
                    'image/jpeg', 'image/pjpeg', 'image/jpg',
                    'image/png', 'image/x-png',
                    'image/webp',
                    'image/svg+xml', 'image/svg', 'text/xml', 'text/plain', 'application/xml',
                    'image/x-icon', 'image/vnd.microsoft.icon', 'image/x-ico', 'image/ico',
                    'application/octet-stream'
                ];
                if (!empty($mime) && !in_array($mime, $validMimes, true) && !str_starts_with($mime, 'image/')) {
                    $this->setFlash('error', 'Format isi berkas tidak valid sebagai gambar logo (' . htmlspecialchars($mime) . ').');
                    $this->redirect(Auth::baseUrl() . '/settings/app');
                    return;
                }
            }

            $targetDir = dirname(__DIR__, 2) . '/public/uploads/logos';
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $filename = 'app_logo_' . time() . '.' . $ext;
            $targetPath = $targetDir . '/' . $filename;

            // Hapus berkas lama jika ada
            if (!empty($currentLogo)) {
                $oldPath = dirname(__DIR__, 2) . '/public/' . ltrim($currentLogo, '/');
                if (file_exists($oldPath)) {
                    @unlink($oldPath);
                }
            }

            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                $relativePath = 'uploads/logos/' . $filename;
                Setting::set('app_logo', $relativePath);
                AuditLog::log('UPLOAD_APP_LOGO', 'app_settings', 0, ['old' => $currentLogo], ['new' => $relativePath]);
            }
        }

        // Handle Favicon
        $deleteFavicon = !empty($_POST['delete_favicon']);
        $currentFavicon = Setting::get('app_favicon', '');

        if ($deleteFavicon) {
            if (!empty($currentFavicon)) {
                $oldFavPath = dirname(__DIR__, 2) . '/public/' . ltrim($currentFavicon, '/');
                if (file_exists($oldFavPath)) {
                    @unlink($oldFavPath);
                }
            }
            Setting::set('app_favicon', '');
            AuditLog::log('DELETE_APP_FAVICON', 'app_settings', 0, ['favicon' => $currentFavicon], []);
        }

        if (!empty($_FILES['app_favicon']['tmp_name']) && $_FILES['app_favicon']['error'] === UPLOAD_ERR_OK) {
            $favFile = $_FILES['app_favicon'];
            $favExt = strtolower(pathinfo($favFile['name'], PATHINFO_EXTENSION));
            $allowedFavExts = ['ico', 'png', 'svg', 'webp', 'jpg', 'jpeg'];

            if (in_array($favExt, $allowedFavExts, true) && $favFile['size'] <= 1 * 1024 * 1024) {
                $targetDir = dirname(__DIR__, 2) . '/public/uploads/logos';
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }

                $favFilename = 'favicon_' . time() . '.' . $favExt;
                $targetFavPath = $targetDir . '/' . $favFilename;

                if (!empty($currentFavicon)) {
                    $oldFavPath = dirname(__DIR__, 2) . '/public/' . ltrim($currentFavicon, '/');
                    if (file_exists($oldFavPath)) {
                        @unlink($oldFavPath);
                    }
                }

                if (move_uploaded_file($favFile['tmp_name'], $targetFavPath)) {
                    $relFavPath = 'uploads/logos/' . $favFilename;
                    Setting::set('app_favicon', $relFavPath);
                    AuditLog::log('UPLOAD_APP_FAVICON', 'app_settings', 0, ['old' => $currentFavicon], ['new' => $relFavPath]);
                }
            }
        }

        AuditLog::log('UPDATE_APP_SETTINGS', 'app_settings', 0, null, [
            'app_name' => $appName,
            'app_short_name' => $appShortName,
            'app_sub_name' => $appSubName
        ]);

        $this->setFlash('success', 'Identitas, logo, dan favicon website berhasil diperbarui.');
        $this->redirect(Auth::baseUrl() . '/settings/app');
    }
}

<?php
// app/Controllers/AuditBackupController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class AuditBackupController extends Controller {

    // --- 1. AUDIT LOGS (APPEND-ONLY) ---
    public function logs(): void {
        $this->requireRole(['operator_yayasan', 'direktur_pendidikan', 'pembina_yayasan']);
        
        $actionFilter = trim($_GET['action'] ?? '');
        $userFilter = trim($_GET['username'] ?? '');

        $query = "SELECT * FROM audit_logs WHERE 1=1 ";
        $params = [];

        if (!empty($actionFilter)) {
            $query .= "AND action LIKE ? ";
            $params[] = "%{$actionFilter}%";
        }
        if (!empty($userFilter)) {
            $query .= "AND username LIKE ? ";
            $params[] = "%{$userFilter}%";
        }

        $query .= "ORDER BY id DESC LIMIT 100";
        $logs = Database::fetchAll($query, $params);

        $this->view('audit/logs', [
            'pageTitle'    => 'Pusat Log Audit Keamanan (Append-Only)',
            'logs'         => $logs,
            'actionFilter' => $actionFilter,
            'userFilter'   => $userFilter
        ]);
    }

    // --- 2. BACKUP & RESTORE DATABASE ---
    public function backupIndex(): void {
        $this->requireRole(['operator_yayasan']);

        $backups = Database::fetchAll(
            "SELECT b.*, u.full_name as creator_name 
             FROM database_backups b 
             LEFT JOIN users u ON b.created_by_user_id = u.id 
             ORDER BY b.created_at DESC"
        );

        $totalBackups = count($backups);
        $totalBytes = array_sum(array_column($backups, 'file_size'));
        $encryptedCount = count(array_filter($backups, function($b) {
            return stripos($b['backup_type'] ?? '', 'Terenkripsi') !== false || str_ends_with(strtolower($b['filename']), '.enc');
        }));
        $fullCount = count(array_filter($backups, function($b) {
            return stripos($b['backup_type'] ?? '', 'Full') !== false || str_ends_with(strtolower($b['filename']), '.zip');
        }));

        $nasStatus = \Core\Storage\StorageManager::testNasConnection();
        $coldEnabled = \Core\Storage\StorageManager::isColdEnabled();
        $coldConfig = \Core\Storage\StorageManager::getConfig()['cold'] ?? [];
        $hotConfig = \Core\Storage\StorageManager::getConfig()['hot'] ?? [];
        $hotFree = @disk_free_space($hotConfig['path'] ?? (dirname(__DIR__, 2) . '/storage/uploads'));
        $hotTotal = @disk_total_space($hotConfig['path'] ?? (dirname(__DIR__, 2) . '/storage/uploads'));

        $this->view('audit/backup', [
            'pageTitle'      => 'Pusat Manajemen Cadangan & Pemulihan (Backup & Restore)',
            'backups'        => $backups,
            'totalBackups'   => $totalBackups,
            'totalBytes'     => $totalBytes,
            'encryptedCount' => $encryptedCount,
            'fullCount'      => $fullCount,
            'nasStatus'      => $nasStatus,
            'coldEnabled'    => $coldEnabled,
            'coldConfig'     => $coldConfig,
            'hotFree'        => $hotFree,
            'hotTotal'       => $hotTotal,
        ]);
    }

    public function createBackup(): void {
        $this->requireRole('operator_yayasan');
        $userId = Auth::id();

        $backupDir = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $encrypt = !empty($_POST['encrypt']);
        $copyToNas = !empty($_POST['copy_to_nas']);
        $rawFilename = 'backup_akfioke_' . date('Ymd_His') . '.sql';
        $rawFile = $backupDir . DIRECTORY_SEPARATOR . $rawFilename;

        \App\Services\BackupService::dumpDatabase($rawFile);

        $finalFile = $rawFile;
        $finalFilename = $rawFilename;
        $backupType = 'Manual';

        if ($encrypt) {
            $encFilename = $rawFilename . '.enc';
            $encFile = $backupDir . DIRECTORY_SEPARATOR . $encFilename;
            if (\App\Services\BackupService::encryptFile($rawFile, $encFile)) {
                @unlink($rawFile);
                $finalFile = $encFile;
                $finalFilename = $encFilename;
                $backupType = 'Terenkripsi AES-256';
            }
        }

        $fileSize = file_exists($finalFile) ? filesize($finalFile) : 0;

        Database::execute(
            "INSERT INTO database_backups (filename, file_path, file_size, backup_type, created_by_user_id) 
             VALUES (?, ?, ?, ?, ?)",
            [$finalFilename, $finalFile, $fileSize, $backupType, $userId]
        );

        $backupId = (int)Database::lastInsertId();
        AuditLog::log('CREATE_DB_BACKUP', 'database_backups', $backupId, null, [
            'file' => $finalFilename,
            'size' => $fileSize,
            'encrypted' => $encrypt,
            'copy_nas' => $copyToNas
        ]);

        $nasMsg = '';
        if ($copyToNas && \Core\Storage\StorageManager::isColdEnabled()) {
            $nasRes = \App\Services\BackupService::copyBackupToNas($backupId);
            $nasMsg = $nasRes['success'] ? ' & berhasil disalin ke Cold Storage NAS' : ' (namun gagal disalin ke NAS: ' . $nasRes['message'] . ')';
        }

        $statusLabel = $encrypt ? "Terenkripsi AES-256" : "SQL Standar";
        $this->setFlash('success', "Cadangan database ({$statusLabel}) berhasil dibuat: {$finalFilename} (" . round($fileSize / 1024, 1) . " KB){$nasMsg}.");
        $this->redirect(Auth::baseUrl() . '/backup');
    }

    public function createFullBackup(): void {
        $this->requireRole('operator_yayasan');
        $userId = Auth::id();
        $copyToNas = !empty($_POST['copy_to_nas']);

        $result = \App\Services\BackupService::createFullBackup($userId, 'Full (DB + Storage)');

        if ($result['success']) {
            AuditLog::log('CREATE_FULL_BACKUP', 'database_backups', $result['id'] ?? 0, null, [
                'file'     => $result['filename'],
                'size'     => $result['file_size'],
                'files'    => $result['files_count'],
                'copy_nas' => $copyToNas
            ]);

            $nasMsg = '';
            if ($copyToNas && !empty($result['id']) && \Core\Storage\StorageManager::isColdEnabled()) {
                $nasRes = \App\Services\BackupService::copyBackupToNas((int)$result['id']);
                $nasMsg = $nasRes['success'] ? ' & berhasil disinkronkan ke Cold Storage NAS' : ' (namun sinkronisasi NAS gagal: ' . $nasRes['message'] . ')';
            }

            $this->setFlash('success', "Cadangan penuh berhasil dibuat: {$result['filename']} (" . round($result['file_size'] / (1024 * 1024), 2) . " MB, {$result['files_count']} berkas storage){$nasMsg}.");
        } else {
            $this->setFlash('danger', "Gagal membuat cadangan penuh: " . $result['message']);
        }

        $this->redirect(Auth::baseUrl() . '/backup');
    }

    public function downloadBackup(string $id): void {
        $this->requireRole('operator_yayasan');
        $backup = Database::fetch("SELECT * FROM database_backups WHERE id = ?", [(int)$id]);

        if (!$backup || !file_exists($backup['file_path'])) {
            http_response_code(404);
            die('Berkas cadangan tidak ditemukan.');
        }

        $filePath = realpath($backup['file_path']);
        $backupDir = realpath(dirname(__DIR__, 2) . '/storage/backups');

        // Pastikan tidak ada path traversal ke luar storage/backups
        if (!$filePath || !$backupDir || strpos($filePath, $backupDir) !== 0 || !is_file($filePath)) {
            http_response_code(403);
            die('Akses berkas tidak sah.');
        }

        AuditLog::log('DOWNLOAD_DB_BACKUP', 'database_backups', $backup['id'], null, ['file' => $backup['filename']]);

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mime = ($extension === 'zip') ? 'application/zip' : 'application/octet-stream';
        $filename = basename($backup['filename']);
        $fileSize = filesize($filePath);

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: attachment; filename="' . $filename . '"; filename*=UTF-8\'\'' . rawurlencode($filename));
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0, private');
        header('Pragma: public');
        if ($fileSize !== false) {
            header('Content-Length: ' . $fileSize);
        }

        $fp = @fopen($filePath, 'rb');
        if ($fp) {
            while (!feof($fp) && !connection_aborted()) {
                echo fread($fp, 65536);
                flush();
            }
            fclose($fp);
        } else {
            readfile($filePath);
        }
        exit;
    }

    public function copyToNas(): void {
        $this->requireRole('operator_yayasan');
        $id = (int)($_POST['backup_id'] ?? 0);
        $res = \App\Services\BackupService::copyBackupToNas($id);
        if ($res['success']) {
            AuditLog::log('SYNC_BACKUP_TO_NAS', 'database_backups', $id);
            $this->setFlash('success', $res['message']);
        } else {
            $this->setFlash('danger', $res['message']);
        }
        $this->redirect(Auth::baseUrl() . '/backup');
    }

    public function restoreBackup(): void {
        $this->requireRole('operator_yayasan');

        if (!empty($_FILES['backup_file']['tmp_name'])) {
            $tmpFile = $_FILES['backup_file']['tmp_name'];
            $origName = $_FILES['backup_file']['name'] ?? 'backup.sql';
            $isEncrypted = (str_ends_with(strtolower($origName), '.enc'));

            $restoreSqlPath = $tmpFile;
            $decryptedTmp = null;

            if ($isEncrypted) {
                $decryptedTmp = $tmpFile . '_decrypted.sql';
                if (!\App\Services\BackupService::decryptFile($tmpFile, $decryptedTmp)) {
                    $this->setFlash('danger', 'Gagal memulihkan: Berkas terenkripsi tidak dapat didekripsi. Pastikan berkas utuh dan kunci enkripsi (APP_KEY) sesuai.');
                    $this->redirect(Auth::baseUrl() . '/backup');
                    return;
                }
                $restoreSqlPath = $decryptedTmp;
            }

            $res = \App\Services\BackupService::restoreDatabase($restoreSqlPath);
            if ($decryptedTmp && file_exists($decryptedTmp)) {
                @unlink($decryptedTmp);
            }

            if ($res['success']) {
                AuditLog::log('RESTORE_DB_BACKUP', 'database_backups', 0, null, ['file' => $origName, 'was_encrypted' => $isEncrypted]);
                $this->setFlash('success', 'Database berhasil dipulihkan (Restore) dari berkas yang diunggah ' . ($isEncrypted ? '(Terenkripsi AES-256).' : '.'));
            } else {
                $this->setFlash('danger', 'Gagal memulihkan database: ' . $res['message']);
            }
        } else {
            $this->setFlash('danger', 'Silakan pilih berkas cadangan (.SQL atau .ENC) yang valid untuk dipulihkan.');
        }

        $this->redirect(Auth::baseUrl() . '/backup');
    }

    public function restoreExistingBackup(): void {
        $this->requireRole('operator_yayasan');
        $id = (int)($_POST['backup_id'] ?? 0);
        $backup = Database::fetch("SELECT * FROM database_backups WHERE id = ?", [$id]);

        if (!$backup || !file_exists($backup['file_path'])) {
            $this->setFlash('danger', 'Berkas cadangan tidak ditemukan di server.');
            $this->redirect(Auth::baseUrl() . '/backup');
            return;
        }

        $filePath = $backup['file_path'];
        $isEncrypted = (str_ends_with(strtolower($backup['filename']), '.enc') || stripos($backup['backup_type'], 'Terenkripsi') !== false);
        $restoreSqlPath = $filePath;
        $decryptedTmp = null;

        if ($isEncrypted) {
            $decryptedTmp = dirname($filePath) . DIRECTORY_SEPARATOR . 'tmp_restore_' . uniqid() . '.sql';
            if (!\App\Services\BackupService::decryptFile($filePath, $decryptedTmp)) {
                $this->setFlash('danger', 'Gagal mendekripsi berkas cadangan. Kunci enkripsi (APP_KEY) tidak cocok.');
                $this->redirect(Auth::baseUrl() . '/backup');
                return;
            }
            $restoreSqlPath = $decryptedTmp;
        }

        $res = \App\Services\BackupService::restoreDatabase($restoreSqlPath);
        if ($decryptedTmp && file_exists($decryptedTmp)) {
            @unlink($decryptedTmp);
        }

        if ($res['success']) {
            AuditLog::log('RESTORE_DB_BACKUP', 'database_backups', $id, null, ['file' => $backup['filename'], 'was_encrypted' => $isEncrypted]);
            $this->setFlash('success', "Database berhasil dipulihkan dari arsip: {$backup['filename']}.");
        } else {
            $this->setFlash('danger', "Gagal memulihkan database: " . $res['message']);
        }

        $this->redirect(Auth::baseUrl() . '/backup');
    }

    public function deleteBackup(): void {
        $this->requireRole('operator_yayasan');
        $id = (int)($_POST['backup_id'] ?? 0);
        $backup = Database::fetch("SELECT * FROM database_backups WHERE id = ?", [$id]);

        if ($backup) {
            if (!empty($backup['file_path']) && file_exists($backup['file_path'])) {
                @unlink($backup['file_path']);
            }
            Database::execute("DELETE FROM database_backups WHERE id = ?", [$id]);
            AuditLog::log('DELETE_DB_BACKUP', 'database_backups', $id, null, ['file' => $backup['filename']]);
            $this->setFlash('success', "Berkas cadangan '{$backup['filename']}' berhasil dihapus.");
        } else {
            $this->setFlash('danger', 'Berkas cadangan tidak ditemukan.');
        }

        $this->redirect(Auth::baseUrl() . '/backup');
    }

    // --- 3. REKAP EKSEKUTIF DEPUTI PENDIDIKAN ---
    public function rekapDeputi(): void {
        $this->requireRole(['deputi_pendidikan', 'direktur_pendidikan', 'pembina_yayasan']);
        $taId = Auth::academicYearId();
        $activeTa = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);

        $rawUnits = Database::fetchAll("SELECT * FROM units ORDER BY id ASC");
        $unitsOverview = [];

        $totSiswaYayasan = 0;
        $totPegawaiYayasan = 0;
        $totSpmbTarget = 0;
        $totSpmbActual = 0;
        $allAkfiScores = [];

        foreach ($rawUnits as $u) {
            $uid = (int)$u['id'];

            $siswa = (int)(Database::fetch("SELECT COUNT(id) as c FROM students WHERE unit_id = ? AND status = 'Aktif'", [$uid])['c'] ?? 0);
            $pegawai = (int)(Database::fetch("SELECT COUNT(id) as c FROM employees WHERE unit_id = ? AND is_deleted = 0", [$uid])['c'] ?? 0);
            
            $akfiRow = Database::fetch("SELECT AVG(total_score) as a, COUNT(id) as cnt FROM akfi_assessments WHERE unit_id = ? AND academic_year_id = ? AND total_score > 0", [$uid, $taId]);
            $avgAkfi = (!empty($akfiRow['cnt']) && $akfiRow['a'] !== null) ? round((float)$akfiRow['a'], 1) : null;
            if ($avgAkfi !== null) {
                $allAkfiScores[] = $avgAkfi;
            }

            $spmb = Database::fetch("SELECT target_count, actual_count FROM spmb_targets WHERE unit_id = ? AND academic_year_id = ?", [$uid, $taId]);
            $spmbTarget = (int)($spmb['target_count'] ?? 0);
            $spmbActual = (int)($spmb['actual_count'] ?? 0);
            $spmbPct = $spmbTarget > 0 ? round(($spmbActual / $spmbTarget) * 100, 1) : 0.0;

            $bills = Database::fetch("SELECT SUM(amount) as tot, SUM(CASE WHEN status != 'Lunas' THEN amount ELSE 0 END) as unp FROM bills WHERE unit_id = ? AND academic_year_id = ?", [$uid, $taId]);
            $totBill = (float)($bills['tot'] ?? 0);
            $unpaidBill = (float)($bills['unp'] ?? 0);
            $tunggakanPct = $totBill > 0 ? round(($unpaidBill / $totBill) * 100, 1) : 0.0;

            $prestasi = (int)(Database::fetch("SELECT COUNT(id) as c FROM prestasi_siswa WHERE unit_id = ? AND academic_year_id = ?", [$uid, $taId])['c'] ?? 0);
            $turnover = (int)(Database::fetch("SELECT COUNT(id) as c FROM employee_turnover_logs WHERE from_unit_id = ?", [$uid])['c'] ?? 0);

            $tahfidz = (int)(Database::fetch("SELECT COUNT(DISTINCT student_id) as c FROM tahfidz_exams WHERE unit_id = ? AND academic_year_id = ? AND status = 'Lulus'", [$uid, $taId])['c'] ?? 0);
            $tahfidzPct = $siswa > 0 ? round(($tahfidz / $siswa) * 100, 1) : 0.0;

            $u['total_siswa'] = $siswa;
            $u['total_pegawai'] = $pegawai;
            $u['avg_akfi'] = $avgAkfi;
            $u['spmb_target'] = $spmbTarget;
            $u['spmb_actual'] = $spmbActual;
            $u['spmb_pct'] = $spmbPct;
            $u['total_bill'] = $totBill;
            $u['unpaid_bill'] = $unpaidBill;
            $u['tunggakan_pct'] = $tunggakanPct;
            $u['prestasi_count'] = $prestasi;
            $u['total_turnover'] = $turnover;
            $u['tahfidz_pass'] = $tahfidz;
            $u['tahfidz_pct'] = $tahfidzPct;

            $unitsOverview[] = $u;

            $totSiswaYayasan += $siswa;
            $totPegawaiYayasan += $pegawai;
            $totSpmbTarget += $spmbTarget;
            $totSpmbActual += $spmbActual;
        }

        $avgAkfiYayasan = !empty($allAkfiScores) ? round(array_sum($allAkfiScores) / count($allAkfiScores), 1) : 0.0;
        $spmbPctYayasan = $totSpmbTarget > 0 ? round(($totSpmbActual / $totSpmbTarget) * 100, 1) : 0.0;

        $this->view('audit/rekap_deputi', [
            'pageTitle'              => 'Rekap Indikator Mutu Satuan Pendidikan (Deputi)',
            'unitsOverview'          => $unitsOverview,
            'activeTa'               => $activeTa,
            'totSiswaYayasan'        => $totSiswaYayasan,
            'totPegawaiYayasan'      => $totPegawaiYayasan,
            'avgAkfiYayasan'         => $avgAkfiYayasan,
            'totSpmbTarget'          => $totSpmbTarget,
            'totSpmbActual'          => $totSpmbActual,
            'spmbPctYayasan'         => $spmbPctYayasan
        ]);
    }
}

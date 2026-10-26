<?php
/**
 * ==============================================================================
 * YAYASAN ISLAM AL KAHFI BATAM - AKFIOKE
 * SKRIP CRON JOB MINGGUAN: CADANGAN PENUH (DATABASE SQL + STORAGE UPLOADS .ZIP)
 * ==============================================================================
 * Jalankan via CLI atau jadwalkan di Crontab / Task Scheduler:
 * Contoh Linux Crontab (Setiap Minggu pukul 02:00 dini hari):
 * 0 2 * * 0 /usr/bin/php /path/to/akfioke/bin/cron_backup_weekly.php >> /path/to/akfioke/storage/logs/cron_backup.log 2>&1
 *
 * Contoh Windows Task Scheduler:
 * Program/script: C:\xampp\php\php.exe
 * Arguments: C:\xampp\htdocs\akfioke\bin\cron_backup_weekly.php
 * ==============================================================================
 */

// Pastikan skrip dijalankan dari baris perintah (CLI)
if (php_sapi_name() !== 'cli' && empty($_SERVER['argv'])) {
    http_response_code(403);
    die("Akses ditolak. Skrip ini hanya dapat dijalankan melalui CLI / Cron job.\n");
}

@ini_set('memory_limit', '512M');
@set_time_limit(600); // 10 menit batas waktu

$rootDir = dirname(__DIR__);
require_once $rootDir . '/vendor/autoload.php';

use App\Services\BackupService;
use Core\Database;
use Core\AuditLog;

echo "[" . date('Y-m-d H:i:s') . "] [CRON BACKUP] Memulai proses pencadangan penuh mingguan...\n";

try {
    // Ambil ID user operator yayasan untuk pelaporan audit (default ID 1 jika tidak ada)
    $operator = Database::fetch("SELECT u.id FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.name = 'operator_yayasan' LIMIT 1");
    $operatorId = $operator['id'] ?? 1;

    $result = BackupService::createFullBackup((int)$operatorId, 'Weekly Cron');

    if ($result['success']) {
        echo "[" . date('Y-m-d H:i:s') . "] [CRON BACKUP BERHASIL] " . $result['message'] . "\n";
        echo "  - Berkas: " . $result['file_path'] . "\n";
        echo "  - Ukuran: " . round($result['file_size'] / (1024 * 1024), 2) . " MB\n";
        echo "  - Jumlah berkas storage: " . $result['files_count'] . " berkas\n";

        // Catat di Audit Log
        if (class_exists(AuditLog::class)) {
            AuditLog::log('CRON_WEEKLY_BACKUP', 'database_backups', $result['id'] ?? 0, null, [
                'file'  => $result['filename'],
                'size'  => $result['file_size'],
                'files' => $result['files_count']
            ]);
        }
        exit(0);
    } else {
        echo "[" . date('Y-m-d H:i:s') . "] [CRON BACKUP GAGAL] " . $result['message'] . "\n";
        exit(1);
    }
} catch (\Throwable $e) {
    echo "[" . date('Y-m-d H:i:s') . "] [CRON BACKUP ERROR] Terjadi eksepsi: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}

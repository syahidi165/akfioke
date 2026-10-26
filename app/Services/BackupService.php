<?php
// app/Services/BackupService.php
namespace App\Services;

use Core\Database;
use ZipArchive;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BackupService {
    /**
     * Ekspor database ke file SQL (mysqldump atau PDO fallback)
     */
    public static function dumpDatabase(string $outputSqlPath): bool {
        $dbConfig = require dirname(__DIR__, 2) . '/config/database.php';
        $host = $dbConfig['host'] ?? 'localhost';
        $port = $dbConfig['port'] ?? 3306;
        $user = $dbConfig['username'] ?? 'root';
        $pass = $dbConfig['password'] ?? '';
        $dbname = $dbConfig['database'] ?? 'akfioke_db';

        // Deteksi executable mysqldump
        $mysqldumpCandidates = [
            'C:\\xampp\\mysql\\bin\\mysqldump.exe',
            '/usr/bin/mysqldump',
            '/usr/local/bin/mysqldump',
            'mysqldump'
        ];

        $mysqldumpBin = null;
        foreach ($mysqldumpCandidates as $candidate) {
            if (file_exists($candidate)) {
                $mysqldumpBin = $candidate;
                break;
            }
        }

        if ($mysqldumpBin) {
            $passArg = !empty($pass) ? "-p\"{$pass}\"" : '';
            $cmd = "\"{$mysqldumpBin}\" -h {$host} -P {$port} -u {$user} {$passArg} {$dbname} > \"{$outputSqlPath}\"";
            @shell_exec($cmd);

            if (file_exists($outputSqlPath) && filesize($outputSqlPath) > 500) {
                // Bersihkan DEFINER agar portabel saat dipulihkan di server lain
                $content = file_get_contents($outputSqlPath);
                $cleaned = preg_replace('/DEFINER\s*=\s*`?[^`@]+`?@`?[^`\s]+`?/i', '', $content);
                file_put_contents($outputSqlPath, $cleaned);
                return true;
            }
        }

        // Fallback: Ekspor melalui koneksi PDO native PHP
        return self::dumpDatabaseViaPdo($outputSqlPath);
    }

    /**
     * Fallback ekspor database tabel demi tabel via PDO
     */
    private static function dumpDatabaseViaPdo(string $outputSqlPath): bool {
        try {
            $pdo = Database::connect();
            $fp = fopen($outputSqlPath, 'w');
            if (!$fp) return false;

            fwrite($fp, "-- ===========================================================\n");
            fwrite($fp, "-- YAYASAN ISLAM AL KAHFI BATAM - AKFIOKE DATABASE BACKUP\n");
            fwrite($fp, "-- Tanggal Pembuatan: " . date('Y-m-d H:i:s') . "\n");
            fwrite($fp, "-- ===========================================================\n\n");
            fwrite($fp, "SET FOREIGN_KEY_CHECKS = 0;\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");

            $tables = Database::fetchAll("SHOW TABLES");
            foreach ($tables as $tRow) {
                $tableName = reset($tRow);

                // DDL Create Table
                $createRow = Database::fetch("SHOW CREATE TABLE `{$tableName}`");
                if (!empty($createRow['Create Table'])) {
                    fwrite($fp, "DROP TABLE IF EXISTS `{$tableName}`;\n");
                    fwrite($fp, $createRow['Create Table'] . ";\n\n");
                }

                // DML Dump Data
                $rows = Database::fetchAll("SELECT * FROM `{$tableName}`");
                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $escapedValues = array_map(function ($val) use ($pdo) {
                            if ($val === null) return 'NULL';
                            return $pdo->quote($val);
                        }, array_values($row));

                        $cols = array_map(function($c) { return "`{$c}`"; }, array_keys($row));
                        $colList = implode(', ', $cols);
                        $valList = implode(', ', $escapedValues);
                        fwrite($fp, "INSERT INTO `{$tableName}` ({$colList}) VALUES ({$valList});\n");
                    }
                    fwrite($fp, "\n");
                }
            }

            fwrite($fp, "SET FOREIGN_KEY_CHECKS = 1;\n");
            fclose($fp);
            return true;
        } catch (\Throwable $e) {
            error_log("Dump DB via PDO failed: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Buat Cadangan Penuh (Database SQL Dump + Seluruh Berkas storage/uploads/ ke dalam ZIP)
     *
     * @param int|null $userId ID pengguna yang memicu
     * @param string $backupType 'Full (DB + Storage)' atau 'Weekly Cron'
     * @return array
     */
    public static function createFullBackup(?int $userId = null, string $backupType = 'Full (DB + Storage)'): array {
        $baseDir = dirname(__DIR__, 2);
        $backupDir = $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'backups';
        $uploadsDir = $baseDir . DIRECTORY_SEPARATOR . 'storage' . DIRECTORY_SEPARATOR . 'uploads';

        if (!is_dir($backupDir)) {
            mkdir($backupDir, 0755, true);
        }

        $timestamp = date('Ymd_His');
        $zipFilename = "full_backup_{$timestamp}.zip";
        $zipFilePath = $backupDir . DIRECTORY_SEPARATOR . $zipFilename;
        $tmpSqlPath = $backupDir . DIRECTORY_SEPARATOR . "tmp_db_{$timestamp}.sql";

        // 1. Ekspor Database
        $dbDumpSuccess = self::dumpDatabase($tmpSqlPath);
        if (!$dbDumpSuccess || !file_exists($tmpSqlPath)) {
            return ['success' => false, 'message' => 'Gagal membuat salinan database SQL.'];
        }

        // 2. Paketkan ke dalam ZIP menggunakan ZipArchive
        if (!class_exists('ZipArchive')) {
            @unlink($tmpSqlPath);
            return ['success' => false, 'message' => 'Ekstensi PHP ZipArchive tidak aktif di server.'];
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            @unlink($tmpSqlPath);
            return ['success' => false, 'message' => 'Gagal menginisialisasi berkas arsip ZIP.'];
        }

        // Masukkan berkas database ke dalam zip
        $zip->addFile($tmpSqlPath, "database/backup_database_{$timestamp}.sql");

        // Masukkan seluruh berkas di storage/uploads/ ke dalam zip
        $uploadedFilesCount = 0;
        if (is_dir($uploadsDir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($uploadsDir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::LEAVES_ONLY
            );

            foreach ($files as $file) {
                if (!$file->isDir()) {
                    $filePath = $file->getRealPath();
                    // Lewati file .htaccess jika diinginkan atau sertakan
                    $relPath = 'storage/uploads/' . str_replace('\\', '/', substr($filePath, strlen($uploadsDir) + 1));
                    $zip->addFile($filePath, $relPath);
                    $uploadedFilesCount++;
                }
            }
        }

        // Tambahkan Manifest Metadata Cadangan
        $manifest = [
            'system'           => 'AKFIOKE - Yayasan Islam Al Kahfi Batam',
            'backup_type'      => $backupType,
            'created_at'       => date('Y-m-d H:i:s'),
            'database_dump'    => "database/backup_database_{$timestamp}.sql",
            'total_uploads'    => $uploadedFilesCount,
            'php_version'      => PHP_VERSION,
        ];
        $zip->addFromString('backup_manifest.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $zip->close();

        // Bersihkan file sementara
        @unlink($tmpSqlPath);

        if (!file_exists($zipFilePath)) {
            return ['success' => false, 'message' => 'Gagal memproses berkas ZIP arsip cadangan.'];
        }

        $fileSize = filesize($zipFilePath);
        $creatorId = $userId ?: 1; // Default ke user ID 1 (Admin/Operator) jika dari cron CLI

        // 3. Catat di tabel database_backups
        Database::execute(
            "INSERT INTO database_backups (filename, file_path, file_size, backup_type, created_by_user_id) 
             VALUES (?, ?, ?, ?, ?)",
            [$zipFilename, $zipFilePath, $fileSize, $backupType, $creatorId]
        );

        $backupId = (int)Database::lastInsertId();

        // 4. Bersihkan cadangan lama (> 30 hari)
        self::pruneOldBackups(30);

        return [
            'success'   => true,
            'id'        => $backupId,
            'filename'  => $zipFilename,
            'file_path' => $zipFilePath,
            'file_size' => $fileSize,
            'files_count' => $uploadedFilesCount,
            'message'   => "Cadangan penuh berhasil dibuat ({$zipFilename}, " . round($fileSize / (1024 * 1024), 2) . " MB, {$uploadedFilesCount} berkas storage)."
        ];
    }

    /**
     * Hapus arsip cadangan yang telah berusia lebih dari $keepDays hari
     */
    public static function pruneOldBackups(int $keepDays = 30): int {
        $cutoffDate = date('Y-m-d H:i:s', strtotime("-{$keepDays} days"));
        $oldBackups = Database::fetchAll(
            "SELECT * FROM database_backups WHERE created_at < ?",
            [$cutoffDate]
        );

        $deletedCount = 0;
        foreach ($oldBackups as $ob) {
            if (!empty($ob['file_path']) && file_exists($ob['file_path'])) {
                @unlink($ob['file_path']);
            }
            Database::execute("DELETE FROM database_backups WHERE id = ?", [$ob['id']]);
            $deletedCount++;
        }

        return $deletedCount;
    }

    /**
     * Enkripsi berkas cadangan (Database Dump / Arsip ZIP) menggunakan AES-256-CBC + HMAC-SHA256
     */
    public static function encryptFile(string $sourcePath, string $destPath, ?string $key = null): bool {
        if (!file_exists($sourcePath)) return false;
        $key = $key ?: \Core\SecurityHelper::getEncryptionKey();
        $cipher = 'AES-256-CBC';
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);

        $data = file_get_contents($sourcePath);
        if ($data === false) return false;

        $ciphertext = openssl_encrypt($data, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext, $key, true);

        // Format berkas terenkripsi: IV (16 bytes) + HMAC-SHA256 (32 bytes) + Ciphertext
        $payload = $iv . $hmac . $ciphertext;
        return file_put_contents($destPath, $payload) !== false;
    }

    /**
     * Dekripsi berkas cadangan terenkripsi
     */
    public static function decryptFile(string $sourcePath, string $destPath, ?string $key = null): bool {
        if (!file_exists($sourcePath)) return false;
        $key = $key ?: \Core\SecurityHelper::getEncryptionKey();
        $cipher = 'AES-256-CBC';
        $ivlen = openssl_cipher_iv_length($cipher);

        $payload = file_get_contents($sourcePath);
        if ($payload === false || strlen($payload) < $ivlen + 32) return false;

        $iv = substr($payload, 0, $ivlen);
        $hmac = substr($payload, $ivlen, 32);
        $ciphertext = substr($payload, $ivlen + 32);

        $expectedHmac = hash_hmac('sha256', $ciphertext, $key, true);
        if (!hash_equals($hmac, $expectedHmac)) {
            return false; // Verifikasi integritas gagal (data telah dirusak/kunci salah)
        }

        $decrypted = openssl_decrypt($ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) return false;

        return file_put_contents($destPath, $decrypted) !== false;
    }

    /**
     * Pulihkan (Restore) database dari berkas SQL secara handal
     * (Mendukung executable mysql client CLI dengan fallback PDO native PHP)
     */
    public static function restoreDatabase(string $sqlFilePath): array {
        if (!file_exists($sqlFilePath) || !is_file($sqlFilePath)) {
            return ['success' => false, 'message' => 'Berkas SQL cadangan tidak ditemukan di server.'];
        }

        $dbConfig = require dirname(__DIR__, 2) . '/config/database.php';
        $host = $dbConfig['host'] ?? 'localhost';
        $port = $dbConfig['port'] ?? 3306;
        $user = $dbConfig['username'] ?? 'root';
        $pass = $dbConfig['password'] ?? '';
        $dbname = $dbConfig['database'] ?? 'akfioke_db';

        // 1. Coba eksekusi melalui mysql client CLI jika tersedia
        $mysqlCandidates = [
            'C:\\xampp\\mysql\\bin\\mysql.exe',
            '/usr/bin/mysql',
            '/usr/local/bin/mysql',
            'mysql'
        ];

        $mysqlBin = null;
        foreach ($mysqlCandidates as $candidate) {
            if (file_exists($candidate)) {
                $mysqlBin = $candidate;
                break;
            }
        }

        if ($mysqlBin) {
            $passArg = !empty($pass) ? "-p\"{$pass}\"" : '';
            $cmd = "\"{$mysqlBin}\" -h {$host} -P {$port} -u {$user} {$passArg} {$dbname} < \"{$sqlFilePath}\" 2>&1";
            $output = @shell_exec($cmd);

            // Periksa apakah eksekusi CLI berhasil
            if (empty($output) || stripos($output, 'error') === false) {
                return [
                    'success' => true,
                    'message' => 'Database berhasil dipulihkan (Restore) melalui MySQL Client CLI.'
                ];
            }
        }

        // 2. Fallback: Eksekusi melalui koneksi PDO native PHP (statement by statement)
        try {
            $pdo = Database::connect();
            $pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, 0);

            // Matikan pengecekan Foreign Key untuk proses restore
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
            $pdo->exec("SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';");

            $fp = fopen($sqlFilePath, 'r');
            if (!$fp) {
                return ['success' => false, 'message' => 'Gagal membuka berkas SQL untuk proses pembacaan.'];
            }

            $query = '';
            $executedQueries = 0;

            while (!feof($fp)) {
                $line = fgets($fp);
                if ($line === false) continue;

                $trimmed = trim($line);
                // Lewati baris kosong atau komentar
                if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*') || str_starts_with($trimmed, '#')) {
                    continue;
                }

                $query .= $line;

                // Jika baris diakhiri titik koma, eksekusi query
                if (str_ends_with($trimmed, ';')) {
                    try {
                        $pdo->exec($query);
                        $executedQueries++;
                    } catch (\Throwable $qe) {
                        // Abaikan warning drop table yang belum ada
                        if (stripos($qe->getMessage(), "Unknown table") === false) {
                            error_log("Restore query warning: " . $qe->getMessage());
                        }
                    }
                    $query = '';
                }
            }
            fclose($fp);

            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

            return [
                'success' => true,
                'message' => "Database berhasil dipulihkan via PDO ({$executedQueries} perintah SQL dieksekusi)."
            ];
        } catch (\Throwable $e) {
            return [
                'success' => false,
                'message' => 'Gagal memulihkan database: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Salin berkas cadangan dari Hot Storage (Hosting) ke Cold Storage (NAS Lokal)
     */
    public static function copyBackupToNas(int $backupId): array {
        $backup = Database::fetch("SELECT * FROM database_backups WHERE id = ?", [$backupId]);
        if (!$backup || !file_exists($backup['file_path'])) {
            return ['success' => false, 'message' => 'Berkas cadangan tidak ditemukan di server hosting.'];
        }

        if (!\Core\Storage\StorageManager::isColdEnabled()) {
            return ['success' => false, 'message' => 'Cold Storage NAS sedang dinonaktifkan dalam konfigurasi sistem.'];
        }

        $nasTest = \Core\Storage\StorageManager::testNasConnection();
        if (!$nasTest['success']) {
            return ['success' => false, 'message' => 'Gagal terhubung ke NAS: ' . $nasTest['message']];
        }

        $targetRelative = 'backups/' . $backup['filename'];
        $copied = \Core\Storage\StorageManager::cold()->putFile($backup['file_path'], $targetRelative);

        if ($copied) {
            Database::execute("UPDATE database_backups SET synced_to_nas = 1 WHERE id = ?", [$backupId]);
            return [
                'success' => true,
                'message' => "Berkas cadangan '{$backup['filename']}' berhasil disalin ke Cold Storage NAS."
            ];
        }

        return ['success' => false, 'message' => 'Gagal menyalin berkas cadangan ke Cold Storage NAS.'];
    }
}

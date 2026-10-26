<?php
// app/Services/AcademicYearArchiveService.php
namespace App\Services;

use Core\Database;
use Core\Storage\StorageManager;
use Core\AuditLog;

class AcademicYearArchiveService {

    /**
     * Daftar tabel yang memiliki data spesifik per Tahun Ajaran (academic_year_id)
     */
    protected static array $taTables = [
        'academic_year_approvals',
        'administrasi_uploads',
        'akfi_assessments',
        'akfi_templates',
        'bills',
        'checklists_braja',
        'checklists_cs',
        'checklists_security',
        'class_schedules',
        'class_students',
        'classes',
        'employee_attendance',
        'guru_berprestasi',
        'karya_ilmiah_guru',
        'kegiatan_luar_kbm',
        'panitia_lomba_assignments',
        'prestasi_siswa',
        'schedule_time_slots',
        'spmb_registrations',
        'spmb_targets',
        'student_attendance',
        'student_grades',
        'student_movement_logs',
        'supervisi_sessions',
        'survey_responses',
        'tahfidz_exams',
        'tahfidz_halaqah',
        'tahfidz_setoran_guru',
        'tahfidz_setoran_siswa',
        'tahfidz_targets',
        'teacher_subjects',
        'unit_administrasi_deadlines',
    ];

    /**
     * Pindahkan seluruh data arsip Tahun Ajaran lampau dari Hot Storage (Hosting)
     * ke Cold Storage (NAS Lokal Sekolah).
     *
     * @param int $taId ID Tahun Ajaran
     * @param int|null $userId ID Operator Yayasan
     * @return array Status hasil pengarsipan
     */
    public static function archiveAcademicYear(int $taId, ?int $userId = null): array {
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        if (!$ta) {
            return ['success' => false, 'message' => "Tahun Ajaran ID #{$taId} tidak ditemukan."];
        }

        // VALIDASI KEAMANAN: Tahun ajaran yang sedang aktif TIDAK BOLEH diarsipkan ke Cold Storage!
        if (!empty($ta['is_active'])) {
            return [
                'success' => false,
                'message' => "Tahun Ajaran '{$ta['name']} ({$ta['semester']})' SEDANG AKTIF BERJALAN. Tahun ajaran aktif harus tetap berada di Hot Storage (Hosting) untuk kelancaran operasional harian!"
            ];
        }

        // Cek kesiapan koneksi Cold Storage NAS
        $nasTest = StorageManager::testNasConnection();
        if (!$nasTest['success']) {
            return [
                'success' => false,
                'message' => "Gagal terhubung ke Cold Storage NAS: " . $nasTest['message'] . ". Pastikan perangkat NAS menyala dan terhubung ke jaringan."
            ];
        }

        $movedFilesCount = 0;
        $totalBytesMoved = 0;
        $skippedFiles = 0;
        $errors = [];

        // 1. CARI DAN PINDAHKAN SELURUH BERKAS UPLOAD TERKAIT TAHUN AJARAN INI
        // a. Dari tabel administrasi_uploads
        $adminUploads = Database::fetchAll(
            "SELECT id, file_path, file_size FROM administrasi_uploads WHERE academic_year_id = ? AND file_path != '' AND file_path IS NOT NULL",
            [$taId]
        );

        foreach ($adminUploads as $doc) {
            $path = $doc['file_path'];
            $loc = StorageManager::locate($path);

            if ($loc['exists'] && $loc['tier'] === 'hot') {
                $fileSize = $loc['size'];
                $moved = StorageManager::moveToCold($path);
                if ($moved) {
                    $movedFilesCount++;
                    $totalBytesMoved += $fileSize;

                    // Catat ke tabel riwayat archived_files
                    Database::execute(
                        "INSERT INTO archived_files (academic_year_id, table_name, record_id, original_path, cold_path, file_size) 
                         VALUES (?, 'administrasi_uploads', ?, ?, ?, ?)",
                        [$taId, $doc['id'], $path, $path, $fileSize]
                    );
                } else {
                    $errors[] = "Gagal memindahkan berkas administrasi: {$path}";
                }
            } elseif ($loc['exists'] && $loc['tier'] === 'cold') {
                $skippedFiles++; // Sudah berada di cold storage
            }
        }

        // b. Dari tabel karya_ilmiah_guru
        try {
            $karyaDocs = Database::fetchAll(
                "SELECT id, doc_file FROM karya_ilmiah_guru WHERE academic_year_id = ? AND doc_file != '' AND doc_file IS NOT NULL",
                [$taId]
            );
            foreach ($karyaDocs as $karya) {
                $path = $karya['doc_file'];
                $loc = StorageManager::locate($path);
                if ($loc['exists'] && $loc['tier'] === 'hot') {
                    $fileSize = $loc['size'];
                    if (StorageManager::moveToCold($path)) {
                        $movedFilesCount++;
                        $totalBytesMoved += $fileSize;
                        Database::execute(
                            "INSERT INTO archived_files (academic_year_id, table_name, record_id, original_path, cold_path, file_size) 
                             VALUES (?, 'karya_ilmiah_guru', ?, ?, ?, ?)",
                            [$taId, $karya['id'], $path, $path, $fileSize]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            // Lewati jika tabel/kolom tidak ditemukan
        }

        // 2. BUAT DATABASE SNAPSHOT KHUSUS TAHUN AJARAN INI KE COLD STORAGE NAS
        $snapshotFile = null;
        $snapshotSize = 0;
        try {
            $snapshotResult = self::exportTaDatabaseSnapshot($taId, $ta['name'], $ta['semester']);
            if ($snapshotResult['success']) {
                $snapshotFile = $snapshotResult['relative_path'];
                $snapshotSize = $snapshotResult['size'];
            }
        } catch (\Throwable $e) {
            $errors[] = "Peringatan snapshot database: " . $e->getMessage();
        }

        // 3. PERBARUI STATUS TAHUN AJARAN MENJADI 'cold'
        $meta = [
            'archived_by'     => $userId,
            'archived_at'     => date('Y-m-d H:i:s'),
            'moved_files'     => $movedFilesCount,
            'total_size'      => $totalBytesMoved,
            'skipped_files'   => $skippedFiles,
            'db_snapshot'     => $snapshotFile,
            'db_snapshot_size'=> $snapshotSize,
            'nas_driver'      => StorageManager::getConfig()['cold']['driver'] ?? 'local_path',
        ];

        Database::execute(
            "UPDATE academic_years 
             SET storage_tier = 'cold', 
                 archived_at = NOW(), 
                 archive_file_count = ?, 
                 archive_total_size = ?, 
                 archive_meta = ? 
             WHERE id = ?",
            [$movedFilesCount, $totalBytesMoved, json_encode($meta, JSON_UNESCAPED_SLASHES), $taId]
        );

        // 4. CATAT LOG AUDIT
        AuditLog::log('ARCHIVE_TA_TO_NAS', 'academic_years', $taId, null, [
            'ta_name'      => $ta['name'],
            'semester'     => $ta['semester'],
            'files_moved'  => $movedFilesCount,
            'bytes_moved'  => $totalBytesMoved,
            'mb_saved'     => round($totalBytesMoved / (1024 * 1024), 2),
            'db_snapshot'  => $snapshotFile
        ]);

        $mbSaved = round($totalBytesMoved / (1024 * 1024), 2);
        return [
            'success'          => true,
            'message'          => "Arsip Tahun Ajaran '{$ta['name']} ({$ta['semester']})' BERHASIL dipindahkan ke Cold Storage NAS! Berhasil menghemat {$mbSaved} MB ruang hosting ({$movedFilesCount} berkas fisik dipindahkan ke NAS).",
            'files_moved'      => $movedFilesCount,
            'bytes_moved'      => $totalBytesMoved,
            'db_snapshot'      => $snapshotFile,
            'warnings'         => $errors,
        ];
    }

    /**
     * Kembalikan (Rehydrate) berkas Tahun Ajaran dari Cold Storage NAS ke Hot Storage Hosting
     */
    public static function rehydrateAcademicYear(int $taId, ?int $userId = null): array {
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        if (!$ta) {
            return ['success' => false, 'message' => "Tahun Ajaran ID #{$taId} tidak ditemukan."];
        }

        $records = Database::fetchAll("SELECT * FROM archived_files WHERE academic_year_id = ?", [$taId]);
        $restoredCount = 0;
        $restoredBytes = 0;

        foreach ($records as $rec) {
            $path = $rec['original_path'];
            if (StorageManager::moveToHot($path)) {
                $restoredCount++;
                $restoredBytes += $rec['file_size'];
            }
        }

        // Update status kembali ke 'hot'
        Database::execute(
            "UPDATE academic_years 
             SET storage_tier = 'hot', 
                 archived_at = NULL, 
                 archive_file_count = 0, 
                 archive_total_size = 0, 
                 archive_meta = NULL 
             WHERE id = ?",
            [$taId]
        );

        // Hapus catatan relasi archived_files untuk TA ini
        Database::execute("DELETE FROM archived_files WHERE academic_year_id = ?", [$taId]);

        AuditLog::log('REHYDRATE_TA_FROM_NAS', 'academic_years', $taId, null, [
            'ta_name'        => $ta['name'],
            'restored_files' => $restoredCount,
            'restored_bytes' => $restoredBytes,
        ]);

        return [
            'success'        => true,
            'message'        => "Tahun Ajaran '{$ta['name']} ({$ta['semester']})' berhasil dikembalikan (Rehydrate) ke Hot Storage Hosting ({$restoredCount} berkas dipulihkan).",
            'restored_files' => $restoredCount,
        ];
    }

    /**
     * Ekspor Snapshot Database khusus data tahun ajaran bersangkutan ke Cold Storage NAS
     */
    protected static function exportTaDatabaseSnapshot(int $taId, string $taName, string $semester): array {
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $taName);
        $timestamp = date('Ymd_His');
        $relativeFile = "database_snapshots/snapshot_ta_{$taId}_{$safeName}_{$semester}_{$timestamp}.sql";

        $pdo = Database::connect();
        $sql = "-- ================================================================\n";
        $sql .= "-- COLD STORAGE ARCHIVE: DATABASE SNAPSHOT TAHUN AJARAN\n";
        $sql .= "-- Tahun Ajaran : {$taName} ({$semester}) [ID: {$taId}]\n";
        $sql .= "-- Tanggal Arsip: " . date('Y-m-d H:i:s') . "\n";
        $sql .= "-- Yayasan Islam Al Kahfi Batam - Sistem MASEKO\n";
        $sql .= "-- ================================================================\n\n";
        $sql .= "SET FOREIGN_KEY_CHECKS = 0;\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n";

        $totalRows = 0;
        foreach (self::$taTables as $table) {
            try {
                // Periksa apakah tabel ada dan memiliki kolom academic_year_id
                $cols = Database::fetchAll("SHOW COLUMNS FROM `{$table}` LIKE 'academic_year_id'");
                if (empty($cols)) continue;

                $rows = Database::fetchAll("SELECT * FROM `{$table}` WHERE academic_year_id = ?", [$taId]);
                if (!empty($rows)) {
                    $sql .= "-- Tabel: `{$table}` (" . count($rows) . " data baris)\n";
                    foreach ($rows as $row) {
                        $escapedValues = array_map(function ($val) use ($pdo) {
                            if ($val === null) return 'NULL';
                            return $pdo->quote($val);
                        }, array_values($row));

                        $colsEscaped = array_map(function($c) { return "`{$c}`"; }, array_keys($row));
                        $colList = implode(', ', $colsEscaped);
                        $valList = implode(', ', $escapedValues);
                        $sql .= "INSERT INTO `{$table}` ({$colList}) VALUES ({$valList});\n";
                        $totalRows++;
                    }
                    $sql .= "\n";
                }
            } catch (\Throwable $e) {
                // Lanjutkan jika tabel opsional tidak ada
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";

        // Simpan langsung ke Cold Storage NAS
        $coldDriver = StorageManager::cold();
        $saved = $coldDriver->put($relativeFile, $sql);

        if (!$saved) {
            return ['success' => false, 'message' => 'Gagal menyimpan berkas snapshot SQL ke NAS.'];
        }

        return [
            'success'       => true,
            'relative_path' => $relativeFile,
            'size'          => strlen($sql),
            'rows_count'    => $totalRows,
        ];
    }

    /**
     * Dapatkan Statistik & Ringkasan Storage (Hot Hosting vs Cold NAS)
     */
    public static function getStorageOverview(): array {
        // Hot Storage Info
        $hotCfg = StorageManager::getConfig()['hot'];
        $hotPath = $hotCfg['path'];
        $hotFree = @disk_free_space($hotPath);
        $hotTotal = @disk_total_space($hotPath);

        // Cold Storage NAS Info
        $nasTest = StorageManager::testNasConnection();
        $coldCfg = StorageManager::getConfig()['cold'];

        // Statistik Berkas Terarsip di Database
        $archivedStats = Database::fetch(
            "SELECT COUNT(id) as total_files, COALESCE(SUM(file_size), 0) as total_bytes 
             FROM archived_files"
        );

        // Daftar status per tahun ajaran
        $taStorageList = Database::fetchAll(
            "SELECT id, name, semester, is_active, storage_tier, archived_at, archive_file_count, archive_total_size, archive_meta 
             FROM academic_years 
             ORDER BY id DESC"
        );

        return [
            'hot' => [
                'name'        => $hotCfg['name'] ?? 'Hot Storage (Hosting)',
                'path'        => $hotPath,
                'free_space'  => ($hotFree !== false) ? (int)$hotFree : null,
                'total_space' => ($hotTotal !== false) ? (int)$hotTotal : null,
                'used_space'  => ($hotTotal && $hotFree) ? max(0, $hotTotal - $hotFree) : null,
            ],
            'cold' => [
                'name'        => $coldCfg['name'] ?? 'Cold Storage (NAS Lokal)',
                'enabled'     => StorageManager::isColdEnabled(),
                'driver'      => $coldCfg['driver'] ?? 'local_path',
                'path'        => $coldCfg['local_path'] ?? ($coldCfg['webdav']['base_url'] ?? '-'),
                'is_online'   => $nasTest['success'],
                'status_msg'  => $nasTest['message'],
                'free_space'  => $nasTest['free_space'] ?? null,
                'total_space' => $nasTest['total_space'] ?? null,
                'used_space'  => $nasTest['used_space'] ?? null,
            ],
            'archived_summary' => [
                'total_files' => (int)($archivedStats['total_files'] ?? 0),
                'total_bytes' => (int)($archivedStats['total_bytes'] ?? 0),
                'total_mb'    => round(($archivedStats['total_bytes'] ?? 0) / (1024 * 1024), 2),
            ],
            'academic_years' => $taStorageList,
        ];
    }

    /**
     * Siapkan daftar berkas untuk proses perpindahan (Chunked AJAX Transfer dengan Progress Bar)
     *
     * @param int $taId ID Tahun Ajaran
     * @param string $direction 'to_cold' (Arsip ke NAS) atau 'to_hot' (Balik ke Hosting)
     * @return array
     */
    public static function prepareTransfer(int $taId, string $direction = 'to_cold'): array {
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        if (!$ta) {
            return ['success' => false, 'message' => "Tahun Ajaran ID #{$taId} tidak ditemukan."];
        }

        if ($direction === 'to_cold' && !empty($ta['is_active'])) {
            return [
                'success' => false,
                'message' => "Tahun Ajaran '{$ta['name']} ({$ta['semester']})' SEDANG AKTIF BERJALAN. Tahun ajaran aktif harus tetap berada di Hot Storage (Hosting) untuk kelancaran operasional harian!"
            ];
        }

        // Cek kesiapan koneksi NAS
        $nasTest = StorageManager::testNasConnection();
        if (!$nasTest['success']) {
            return [
                'success' => false,
                'message' => "Gagal terhubung ke Cold Storage NAS: " . $nasTest['message'] . ". Pastikan NAS menyala dan terhubung."
            ];
        }

        $items = [];
        $totalBytes = 0;

        if ($direction === 'to_cold') {
            // Berkas dari administrasi_uploads
            $adminUploads = Database::fetchAll(
                "SELECT id, file_path, file_size FROM administrasi_uploads WHERE academic_year_id = ? AND file_path != '' AND file_path IS NOT NULL",
                [$taId]
            );
            foreach ($adminUploads as $doc) {
                $path = $doc['file_path'];
                $loc = StorageManager::locate($path);
                if ($loc['exists'] && $loc['tier'] === 'hot') {
                    $items[] = [
                        'id'         => (int)$doc['id'],
                        'table'      => 'administrasi_uploads',
                        'path'       => $path,
                        'name'       => basename($path),
                        'size'       => (int)$loc['size'],
                        'size_fmt'   => self::formatBytes($loc['size']),
                    ];
                    $totalBytes += (int)$loc['size'];
                }
            }

            // Berkas dari karya_ilmiah_guru
            try {
                $karyaDocs = Database::fetchAll(
                    "SELECT id, doc_file FROM karya_ilmiah_guru WHERE academic_year_id = ? AND doc_file != '' AND doc_file IS NOT NULL",
                    [$taId]
                );
                foreach ($karyaDocs as $karya) {
                    $path = $karya['doc_file'];
                    $loc = StorageManager::locate($path);
                    if ($loc['exists'] && $loc['tier'] === 'hot') {
                        $items[] = [
                            'id'         => (int)$karya['id'],
                            'table'      => 'karya_ilmiah_guru',
                            'path'       => $path,
                            'name'       => basename($path),
                            'size'       => (int)$loc['size'],
                            'size_fmt'   => self::formatBytes($loc['size']),
                        ];
                        $totalBytes += (int)$loc['size'];
                    }
                }
            } catch (\Throwable $e) {}
        } else {
            // to_hot (rehydrate)
            $records = Database::fetchAll("SELECT * FROM archived_files WHERE academic_year_id = ?", [$taId]);
            foreach ($records as $rec) {
                $path = $rec['original_path'];
                $loc = StorageManager::locate($path);
                if ($loc['exists'] && $loc['tier'] === 'cold') {
                    $items[] = [
                        'id'         => (int)$rec['id'],
                        'table'      => $rec['table_name'],
                        'record_id'  => (int)$rec['record_id'],
                        'path'       => $path,
                        'name'       => basename($path),
                        'size'       => (int)$loc['size'],
                        'size_fmt'   => self::formatBytes($loc['size']),
                    ];
                    $totalBytes += (int)$loc['size'];
                }
            }
        }

        return [
            'success'     => true,
            'ta_id'       => $taId,
            'ta_name'     => "{$ta['name']} ({$ta['semester']})",
            'direction'   => $direction,
            'total_files' => count($items),
            'total_bytes' => $totalBytes,
            'total_mb'    => round($totalBytes / (1024 * 1024), 2),
            'files'       => $items,
        ];
    }

    /**
     * Pindahkan satu berkas dengan aman (Copy-Verify-Delete) per step AJAX
     */
    public static function transferSingleFile(int $taId, string $direction, array $item): array {
        $path = $item['path'] ?? '';
        $table = $item['table'] ?? '';
        $recordId = (int)($item['id'] ?? 0);

        if (empty($path)) {
            return ['success' => false, 'message' => 'Path berkas kosong.', 'source_safe' => true];
        }

        if ($direction === 'to_cold') {
            $loc = StorageManager::locate($path);
            if (!$loc['exists'] || $loc['tier'] !== 'hot') {
                return ['success' => true, 'already_done' => true, 'message' => "Berkas {$path} sudah berada di Cold NAS.", 'size' => 0];
            }

            $sourceSize = $loc['size'];
            $success = StorageManager::moveToCold($path);

            if ($success) {
                Database::execute(
                    "INSERT INTO archived_files (academic_year_id, table_name, record_id, original_path, cold_path, file_size) 
                     VALUES (?, ?, ?, ?, ?, ?)",
                    [$taId, $table, $recordId, $path, $path, $sourceSize]
                );
                return [
                    'success'  => true,
                    'message'  => "Tersalin & Terverifikasi: " . basename($path),
                    'size'     => $sourceSize,
                    'path'     => $path
                ];
            } else {
                return [
                    'success'     => false,
                    'message'     => "Gagal menyalin " . basename($path) . ". Berkas asli di Hosting tetap aman tersimpan.",
                    'source_safe' => true
                ];
            }
        } else {
            // to_hot
            $loc = StorageManager::locate($path);
            if (!$loc['exists'] || $loc['tier'] !== 'cold') {
                return ['success' => true, 'already_done' => true, 'message' => "Berkas {$path} sudah berada di Hot Hosting.", 'size' => 0];
            }

            $sourceSize = $loc['size'];
            $success = StorageManager::moveToHot($path);

            if ($success) {
                Database::execute(
                    "DELETE FROM archived_files WHERE academic_year_id = ? AND original_path = ?",
                    [$taId, $path]
                );
                return [
                    'success'  => true,
                    'message'  => "Dipulihkan & Terverifikasi: " . basename($path),
                    'size'     => $sourceSize,
                    'path'     => $path
                ];
            } else {
                return [
                    'success'     => false,
                    'message'     => "Gagal menyalin " . basename($path) . ". Berkas asli di NAS tetap aman tersimpan.",
                    'source_safe' => true
                ];
            }
        }
    }

    /**
     * Finalisasi proses transfer data storage
     */
    public static function finalizeTransfer(int $taId, string $direction, ?int $userId, array $stats): array {
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        if (!$ta) {
            return ['success' => false, 'message' => 'Tahun Ajaran tidak ditemukan.'];
        }

        $filesCount = (int)($stats['files_transferred'] ?? 0);
        $totalBytes = (int)($stats['bytes_transferred'] ?? 0);
        $errorsCount = (int)($stats['errors_count'] ?? 0);

        if ($direction === 'to_cold') {
            // Snapshot database
            $snapshotFile = null;
            $snapshotSize = 0;
            try {
                $snapshotResult = self::exportTaDatabaseSnapshot($taId, $ta['name'], $ta['semester']);
                if ($snapshotResult['success']) {
                    $snapshotFile = $snapshotResult['relative_path'];
                    $snapshotSize = $snapshotResult['size'];
                }
            } catch (\Throwable $e) {}

            $meta = [
                'archived_by'     => $userId,
                'archived_at'     => date('Y-m-d H:i:s'),
                'moved_files'     => $filesCount,
                'total_size'      => $totalBytes,
                'db_snapshot'     => $snapshotFile,
                'db_snapshot_size'=> $snapshotSize,
                'nas_driver'      => StorageManager::getConfig()['cold']['driver'] ?? 'local_path',
            ];

            Database::execute(
                "UPDATE academic_years 
                 SET storage_tier = 'cold', 
                     archived_at = NOW(), 
                     archive_file_count = ?, 
                     archive_total_size = ?, 
                     archive_meta = ? 
                 WHERE id = ?",
                [$filesCount, $totalBytes, json_encode($meta, JSON_UNESCAPED_SLASHES), $taId]
            );

            AuditLog::log('ARCHIVE_TA_TO_NAS', 'academic_years', $taId, null, [
                'ta_name'      => $ta['name'],
                'files_moved'  => $filesCount,
                'bytes_moved'  => $totalBytes,
                'db_snapshot'  => $snapshotFile
            ]);

            $mbSaved = round($totalBytes / (1024 * 1024), 2);
            return [
                'success' => true,
                'message' => "Pengarsipan selesai 100%! Sebanyak {$filesCount} berkas ({$mbSaved} MB) berhasil dipindahkan ke Cold Storage NAS. Berkas sumber di hosting telah dibersihkan secara aman.",
                'files_moved' => $filesCount,
                'mb_saved' => $mbSaved,
                'errors_count' => $errorsCount
            ];
        } else {
            // to_hot
            Database::execute(
                "UPDATE academic_years 
                 SET storage_tier = 'hot', 
                     archived_at = NULL, 
                     archive_file_count = 0, 
                     archive_total_size = 0, 
                     archive_meta = NULL 
                 WHERE id = ?",
                [$taId]
            );

            Database::execute("DELETE FROM archived_files WHERE academic_year_id = ?", [$taId]);

            AuditLog::log('REHYDRATE_TA_FROM_NAS', 'academic_years', $taId, null, [
                'ta_name'        => $ta['name'],
                'restored_files' => $filesCount,
                'restored_bytes' => $totalBytes
            ]);

            $mbRestored = round($totalBytes / (1024 * 1024), 2);
            return [
                'success' => true,
                'message' => "Pemulihan data selesai 100%! Sebanyak {$filesCount} berkas ({$mbRestored} MB) berhasil dikembalikan ke Hot Storage Hosting.",
                'files_restored' => $filesCount,
                'mb_restored' => $mbRestored,
                'errors_count' => $errorsCount
            ];
        }
    }

    public static function formatBytes(int $bytes): string {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 2) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' KB';
        return $bytes . ' B';
    }
}

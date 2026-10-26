<?php
// core/Uploader.php
namespace Core;

class Uploader {
    /**
     * Unggah berkas secara aman dengan pengelompokan hierarkis direktori (tanggal/user),
     * validasi Magic Bytes via finfo, dan pencegahan injeksi kode PHP.
     *
     * @param array $file Array $_FILES['input_name']
     * @param string $subfolder Folder utama (misal: 'administrasi', 'prestasi', 'karya_ilmiah')
     * @param string $grouping 'date' (YYYY/mm/dd), 'user', 'user_date', atau 'none'
     * @param int|null $userId ID pengguna opsional untuk grouping 'user'
     * @return array
     */
    public static function upload(
        array $file, 
        string $subfolder = 'administrasi', 
        string $grouping = 'date', 
        ?int $userId = null
    ): array {
        if (!isset($file['error']) || is_array($file['error'])) {
            return ['success' => false, 'message' => 'Parameter berkas tidak valid.'];
        }

        switch ($file['error']) {
            case UPLOAD_ERR_OK:
                break;
            case UPLOAD_ERR_NO_FILE:
                return ['success' => false, 'message' => 'Tidak ada berkas yang diunggah.'];
            case UPLOAD_ERR_INI_SIZE:
            case UPLOAD_ERR_FORM_SIZE:
                return ['success' => false, 'message' => 'Ukuran berkas melebihi batas maksimum server (5MB).'];
            default:
                return ['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah berkas.'];
        }

        $config = require dirname(__DIR__) . '/config/security.php';
        $maxSize = $config['max_upload_size'] ?? (5 * 1024 * 1024);
        $allowedMimes = $config['allowed_upload_mimes'] ?? [];

        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'Ukuran berkas melebihi batas maksimal (' . round($maxSize / (1024 * 1024)) . 'MB).'];
        }

        // Cegah null-byte poisoning pada nama berkas
        $origName = (string)($file['name'] ?? 'file');
        if (strpos($origName, "\0") !== false) {
            return ['success' => false, 'message' => 'Nama berkas tidak valid (mengandung null-byte).'];
        }

        // 1. Cek Magic Bytes & MIME asli menggunakan finfo
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($file['tmp_name']);

        if (!array_key_exists($mime, $allowedMimes)) {
            return ['success' => false, 'message' => "Jenis berkas ({$mime}) tidak diizinkan."];
        }

        // 2. Validasi Magic Bytes konten biner spesifik
        $validationResult = self::validateMagicBytes($file['tmp_name'], $mime);
        if (!$validationResult['valid']) {
            return ['success' => false, 'message' => $validationResult['message']];
        }

        // 3. Pindai indikasi PHP polyglot / script injection
        if (self::containsPhpCode($file['tmp_name'])) {
            return ['success' => false, 'message' => 'Berkas ditolak karena terdeteksi mengandung kode skrip tersembunyi.'];
        }

        $extension = $allowedMimes[$mime];
        $randomName = bin2hex(random_bytes(16)) . '.' . $extension;

        // 4. Bangun hierarki pengelompokan folder (Date / User / Flat)
        $subfolderClean = trim(str_replace(['..', '\\'], ['', '/'], $subfolder), '/');
        $groupPath = '';

        if ($grouping === 'date') {
            // Contoh: administrasi/2026/09/25/{hash}.pdf
            $groupPath = date('Y/m/d');
        } elseif ($grouping === 'user') {
            // Contoh: administrasi/{user_id}/{hash}.pdf
            $uid = $userId ?: (Auth::id() ?? 'general');
            $groupPath = (string)$uid;
        } elseif ($grouping === 'user_date') {
            // Contoh: administrasi/{user_id}/2026/09/25/{hash}.pdf
            $uid = $userId ?: (Auth::id() ?? 'general');
            $groupPath = (string)$uid . '/' . date('Y/m/d');
        }

        $relativeDirectory = $subfolderClean;
        if ($groupPath !== '') {
            $relativeDirectory .= '/' . $groupPath;
        }

        $uploadBasePath = dirname(__DIR__) . '/storage/uploads';
        $targetDir = $uploadBasePath . '/' . $relativeDirectory;

        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . '/' . $randomName;

        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            return ['success' => false, 'message' => 'Gagal memindahkan berkas ke direktori penyimpanan.'];
        }

        // Atur permission berkas agar tidak dapat dieksekusi
        @chmod($targetPath, 0644);

        $storedRelativePath = $relativeDirectory . '/' . $randomName;

        return [
            'success'           => true,
            'original_filename' => htmlspecialchars($origName, ENT_QUOTES, 'UTF-8'),
            'file_path'         => $storedRelativePath,
            'file_size'         => $file['size'],
            'mime_type'         => $mime,
            'extension'         => $extension
        ];
    }

    /**
     * Validasi Magic Bytes biner sesuai format yang diizinkan
     */
    private static function validateMagicBytes(string $filePath, string $mime): array {
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return ['valid' => false, 'message' => 'Gagal membaca isi berkas untuk verifikasi keamanan.'];
        }

        $bytes = fread($handle, 16);
        fclose($handle);

        if ($bytes === false || strlen($bytes) < 4) {
            return ['valid' => false, 'message' => 'Berkas rusak atau kosong.'];
        }

        switch ($mime) {
            case 'application/pdf':
                // Magic byte: %PDF-
                if (strncmp($bytes, "%PDF-", 5) !== 0) {
                    return ['valid' => false, 'message' => 'Header berkas PDF tidak valid (Magic Bytes mismatch).'];
                }
                break;

            case 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet':
            case 'application/vnd.openxmlformats-officedocument.wordprocessingml.document':
                // OpenXML adalah zip archive: PK\x03\x04
                if (strncmp($bytes, "PK\x03\x04", 4) !== 0) {
                    return ['valid' => false, 'message' => 'Header berkas dokumen Office tidak valid (Bukan format ZIP OpenXML sah).'];
                }
                break;

            case 'application/vnd.ms-excel':
            case 'application/msword':
                // OLE2 Compound Document: \xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1
                $oleHeader = "\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1";
                if (strncmp($bytes, $oleHeader, 8) !== 0) {
                    return ['valid' => false, 'message' => 'Header berkas dokumen biner MS Office tidak valid.'];
                }
                break;

            case 'image/jpeg':
                // JPEG: \xFF\xD8\xFF
                if (strncmp($bytes, "\xFF\xD8\xFF", 3) !== 0) {
                    return ['valid' => false, 'message' => 'Header berkas JPEG/JPG tidak valid.'];
                }
                break;

            case 'image/png':
                // PNG: \x89PNG\r\n\x1a\n
                if (strncmp($bytes, "\x89PNG\x0D\x0A\x1A\x0A", 8) !== 0) {
                    return ['valid' => false, 'message' => 'Header berkas PNG tidak valid.'];
                }
                break;

            case 'image/webp':
                // WebP: RIFF....WEBP
                if (strncmp($bytes, "RIFF", 4) !== 0) {
                    return ['valid' => false, 'message' => 'Header berkas WebP tidak valid.'];
                }
                break;
        }

        return ['valid' => true, 'message' => 'OK'];
    }

    /**
     * Memeriksa apakah berkas non-PHP disusupi kode PHP (polyglot attack)
     */
    private static function containsPhpCode(string $filePath): bool {
        $handle = @fopen($filePath, 'rb');
        if (!$handle) {
            return false;
        }

        // Pindai 64KB pertama dan 64KB terakhir (vektor serangan umum steganografi/polyglot)
        $buffer = fread($handle, 65536);
        
        $fileSize = filesize($filePath);
        if ($fileSize > 65536) {
            fseek($handle, max(0, $fileSize - 65536));
            $buffer .= fread($handle, 65536);
        }
        fclose($handle);

        $patterns = [
            '/<\?php/i',
            '/<\?=/i',
            '/<script\s+language\s*=\s*["\']?php["\']?/i',
            '/__halt_compiler\s*\(/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $buffer)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Alirkan (stream) berkas dari folder storage/uploads secara aman dan hemat RAM.
     * Menggunakan chunk streaming (64KB) serta membersihkan output buffer (ob_end_clean).
     *
     * @param string $relativePath Path relatif dari storage/uploads/
     * @param string|null $downloadName Nama berkas saat diunduh pengguna
     * @param bool $asAttachment True untuk memaksa download, False untuk preview inline
     */
    public static function stream(string $relativePath, ?string $downloadName = null, bool $asAttachment = false): void {
        \Core\Storage\StorageManager::stream($relativePath, $downloadName, $asAttachment);
    }

    /**
     * Unduh berkas langsung sebagai attachment (hemat RAM)
     */
    public static function download(string $relativePath, ?string $downloadName = null): void {
        self::stream($relativePath, $downloadName, true);
    }
}


<?php
// core/Storage/LocalStorageDriver.php
namespace Core\Storage;

class LocalStorageDriver implements StorageDriverInterface {
    protected string $basePath;
    protected string $name;

    public function __construct(string $basePath, string $name = 'Local Storage') {
        $this->basePath = rtrim(str_replace('\\', '/', $basePath), '/');
        $this->name = $name;

        if (!is_dir($this->basePath)) {
            @mkdir($this->basePath, 0755, true);
        }
    }

    public function getBasePath(): string {
        return $this->basePath;
    }

    public function getName(): string {
        return $this->name;
    }

    public function getAbsolutePath(string $relativePath): ?string {
        $clean = ltrim(str_replace('\\', '/', $relativePath), '/');
        // Cegah directory traversal
        if (strpos($clean, '..') !== false) {
            return null;
        }
        return $this->basePath . '/' . $clean;
    }

    public function exists(string $relativePath): bool {
        $abs = $this->getAbsolutePath($relativePath);
        return $abs !== null && file_exists($abs) && is_file($abs);
    }

    public function get(string $relativePath): ?string {
        $abs = $this->getAbsolutePath($relativePath);
        if ($abs && file_exists($abs) && is_file($abs)) {
            return file_get_contents($abs);
        }
        return null;
    }

    public function put(string $relativePath, $contents): bool {
        $abs = $this->getAbsolutePath($relativePath);
        if (!$abs) return false;

        $dir = dirname($abs);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return false;
            }
        }

        if (is_resource($contents)) {
            $dest = @fopen($abs, 'wb');
            if (!$dest) return false;
            $res = stream_copy_to_stream($contents, $dest);
            fclose($dest);
            @chmod($abs, 0644);
            return $res !== false;
        }

        $res = @file_put_contents($abs, $contents);
        if ($res !== false) {
            @chmod($abs, 0644);
            return true;
        }
        return false;
    }

    public function putFile(string $sourceAbsolutePath, string $targetRelativePath): bool {
        if (!file_exists($sourceAbsolutePath) || !is_file($sourceAbsolutePath)) {
            return false;
        }

        $abs = $this->getAbsolutePath($targetRelativePath);
        if (!$abs) return false;

        $dir = dirname($abs);
        if (!is_dir($dir)) {
            if (!@mkdir($dir, 0755, true)) {
                return false;
            }
        }

        if (@copy($sourceAbsolutePath, $abs)) {
            @chmod($abs, 0644);
            return true;
        }
        return false;
    }

    public function delete(string $relativePath): bool {
        $abs = $this->getAbsolutePath($relativePath);
        if ($abs && file_exists($abs) && is_file($abs)) {
            return @unlink($abs);
        }
        return false;
    }

    public function size(string $relativePath): int {
        $abs = $this->getAbsolutePath($relativePath);
        if ($abs && file_exists($abs) && is_file($abs)) {
            $sz = @filesize($abs);
            return $sz !== false ? $sz : 0;
        }
        return 0;
    }

    public function stream(string $relativePath, ?string $downloadName = null, bool $asAttachment = false): void {
        $abs = $this->getAbsolutePath($relativePath);
        if (!$abs || !file_exists($abs) || !is_file($abs)) {
            http_response_code(404);
            die('Berkas tidak ditemukan pada media penyimpanan.');
        }

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($abs) ?: 'application/octet-stream';
        $filename = $downloadName ? basename($downloadName) : basename($abs);
        $encodedName = rawurlencode($filename);
        $fileSize = filesize($abs);
        $disposition = $asAttachment ? 'attachment' : 'inline';

        header('Content-Description: File Transfer');
        header('Content-Type: ' . $mime);
        header('Content-Disposition: ' . $disposition . '; filename="' . $filename . '"; filename*=UTF-8\'\'' . $encodedName);
        header('Content-Transfer-Encoding: binary');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0, private');
        header('Pragma: public');
        if ($fileSize !== false) {
            header('Content-Length: ' . $fileSize);
        }

        $fp = @fopen($abs, 'rb');
        if ($fp) {
            while (!feof($fp) && !connection_aborted()) {
                echo fread($fp, 65536);
                flush();
            }
            fclose($fp);
        } else {
            readfile($abs);
        }
        exit;
    }

    public function testConnection(): array {
        if (!is_dir($this->basePath)) {
            if (!@mkdir($this->basePath, 0755, true)) {
                return [
                    'success'     => false,
                    'message'     => "Direktori penyimpanan '{$this->basePath}' tidak dapat diakses atau dibuat. Periksa izin akses (permission).",
                    'free_space'  => null,
                    'total_space' => null,
                    'used_space'  => null,
                ];
            }
        }

        if (!is_writable($this->basePath)) {
            return [
                'success'     => false,
                'message'     => "Direktori penyimpanan '{$this->basePath}' tidak memiliki izin tulis (write permission).",
                'free_space'  => null,
                'total_space' => null,
                'used_space'  => null,
            ];
        }

        // Tes tulis berkas sementara
        $probeFile = $this->basePath . '/.probe_' . uniqid();
        $writeOk = @file_put_contents($probeFile, 'akfioke_storage_probe');
        if (!$writeOk) {
            return [
                'success'     => false,
                'message'     => "Gagal melakukan uji tulis ke path '{$this->basePath}'.",
                'free_space'  => null,
                'total_space' => null,
                'used_space'  => null,
            ];
        }
        @unlink($probeFile);

        $free = @disk_free_space($this->basePath);
        $total = @disk_total_space($this->basePath);
        $freeBytes = ($free !== false) ? (int)$free : null;
        $totalBytes = ($total !== false) ? (int)$total : null;
        $usedBytes = ($totalBytes !== null && $freeBytes !== null) ? max(0, $totalBytes - $freeBytes) : null;

        return [
            'success'     => true,
            'message'     => "Koneksi ke penyimpanan '{$this->name}' BERHASIL (Path: {$this->basePath}).",
            'free_space'  => $freeBytes,
            'total_space' => $totalBytes,
            'used_space'  => $usedBytes,
        ];
    }
}

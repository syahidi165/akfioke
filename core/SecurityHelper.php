<?php
// core/SecurityHelper.php
namespace Core;

class SecurityHelper {
    public static function baseUrl(): string {
        return Auth::baseUrl();
    }

    public static function escape(?string $string): string {
        return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
    }

    public static function cleanFormula($value): string {
        $str = (string)$value;
        // Tangkal CSV/Excel Formula Injection, termasuk karakter whitespace/control seperti tab, CR, LF di awal
        $trimmed = ltrim($str, " \t\n\r\0\x0B");
        $firstChar = substr($trimmed, 0, 1);
        if (in_array($firstChar, ['=', '+', '-', '@'], true) || preg_match('/^[\t\r\n]/', $str)) {
            return "'" . $str;
        }
        return $str;
    }

    public static function getClientIp(): string {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        return filter_var($ip, FILTER_VALIDATE_IP) ? $ip : '127.0.0.1';
    }

    public static function maskNik(?string $nik, bool $reveal = false): string {
        if (!$nik) return '-';
        if ($reveal) return $nik;
        $len = strlen($nik);
        if ($len < 8) return str_repeat('*', $len);
        return substr($nik, 0, 4) . str_repeat('*', max(0, $len - 8)) . substr($nik, -4);
    }

    public static function maskKk(?string $kk, bool $reveal = false): string {
        return self::maskNik($kk, $reveal);
    }

    public static function formatRupiah($amount): string {
        return 'Rp ' . number_format((float)$amount, 0, ',', '.');
    }

    public static function formatTanggalIndo(?string $dateStr, bool $withDay = false): string {
        if (!$dateStr || $dateStr === '0000-00-00') return '-';
        $timestamp = strtotime($dateStr);
        if (!$timestamp) return '-';

        $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $bulan = [
            1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
            'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
        ];

        $d = date('j', $timestamp);
        $m = $bulan[(int)date('n', $timestamp)];
        $y = date('Y', $timestamp);

        if ($withDay) {
            $dayName = $hari[(int)date('w', $timestamp)];
            return "{$dayName}, {$d} {$m} {$y}";
        }

        return "{$d} {$m} {$y}";
    }

    public static function formatDateIndo(?string $dateStr, bool $withDay = false): string {
        return self::formatTanggalIndo($dateStr, $withDay);
    }

    public static function formatBulanIndo(int $monthNum): string {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        return $bulan[$monthNum] ?? "Bulan {$monthNum}";
    }

    public static function getNamaBulan(int $monthNum): string {
        return self::formatBulanIndo($monthNum);
    }

    public static function sendSecurityHeaders(): void {
        if (!headers_sent()) {
            header('Content-Type: text/html; charset=UTF-8');
            header('X-Frame-Options: DENY');
            header('X-Content-Type-Options: nosniff');
            // X-XSS-Protection dinonaktifkan (0) sesuai rekomendasi modern OWASP, andalkan CSP
            header('X-XSS-Protection: 0');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('X-Permitted-Cross-Domain-Policies: none');

            // HSTS aktif saat koneksi aman (HTTPS)
            $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                || (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
            if ($isHttps) {
                header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            }

            // CSP terstruktur dan membatasi sumber eksekusi
            $cspDirectives = [
                "default-src 'self'",
                "base-uri 'self'",
                "object-src 'none'",
                "form-action 'self'",
                "frame-ancestors 'none'",
                "script-src 'self' 'unsafe-inline'",
                "style-src 'self' 'unsafe-inline'",
                "font-src 'self' data:",
                "img-src 'self' data:",
            ];

            if ($isHttps) {
                $cspDirectives[] = "upgrade-insecure-requests";
            }

            header("Content-Security-Policy: " . implode('; ', $cspDirectives) . ";");
            header('Permissions-Policy: camera=(), microphone=(), geolocation=()');
        }
    }

    public static function getEncryptionKey(): string {
        // Prioritas kunci: environment variable (APP_KEY), jika tidak ada fallback ke config
        $envKey = $_ENV['APP_KEY'] ?? getenv('APP_KEY') ?: null;
        if (!empty($envKey)) {
            return $envKey;
        }
        $config = require dirname(__DIR__) . '/config/security.php';
        return $config['encryption_key'];
    }

    /**
     * Derivasi sub-key kriptografi terpisah menggunakan HKDF-SHA256 (32-byte penuh untuk AES-256 & HMAC)
     * sekaligus menyertakan legacy_key untuk kompatibilitas mundur (backward compatibility).
     */
    public static function deriveKeys(?string $masterKey = null): array {
        $rawKey = $masterKey ?: self::getEncryptionKey();
        return [
            'enc_key'    => hash_hkdf('sha256', $rawKey, 32, 'maseko-aes-256-cbc'),
            'hmac_key'   => hash_hkdf('sha256', $rawKey, 32, 'maseko-hmac-sha256'),
            'legacy_key' => $rawKey,
        ];
    }

    public static function encrypt(string $plainText): string {
        $config = require dirname(__DIR__) . '/config/security.php';
        $keys = self::deriveKeys();
        $cipher = $config['cipher'] ?? 'AES-256-CBC';
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $cipherTextRaw = openssl_encrypt($plainText, $cipher, $keys['enc_key'], OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $cipherTextRaw, $keys['hmac_key'], true);
        return base64_encode($iv . $hmac . $cipherTextRaw);
    }

    public static function decrypt(?string $cipherText): ?string {
        if (empty($cipherText)) return null;
        $config = require dirname(__DIR__) . '/config/security.php';
        $keys = self::deriveKeys();
        $cipher = $config['cipher'] ?? 'AES-256-CBC';
        $c = base64_decode($cipherText, true);
        if ($c === false) return null;
        $ivlen = openssl_cipher_iv_length($cipher);
        if (strlen($c) < $ivlen + 32) return null;
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, 32);
        $cipherTextRaw = substr($c, $ivlen + 32);

        // 1. Coba dekripsi dengan kunci HKDF 32-byte baru
        $calcHmacNew = hash_hmac('sha256', $cipherTextRaw, $keys['hmac_key'], true);
        if (hash_equals($hmac, $calcHmacNew)) {
            $dec = openssl_decrypt($cipherTextRaw, $cipher, $keys['enc_key'], OPENSSL_RAW_DATA, $iv);
            return $dec !== false ? $dec : null;
        }

        // 2. Fallback Legacy Key: agar data lama (seperti smtp_pass yang sudah tersimpan) tetap terbaca mulus
        $calcHmacLegacy = hash_hmac('sha256', $cipherTextRaw, $keys['legacy_key'], true);
        if (hash_equals($hmac, $calcHmacLegacy)) {
            $dec = openssl_decrypt($cipherTextRaw, $cipher, $keys['legacy_key'], OPENSSL_RAW_DATA, $iv);
            return $dec !== false ? $dec : null;
        }

        return null;
    }

    /**
     * Validasi ketat untuk unggahan gambar publik (Logo, Favicon, Tanda Tangan)
     * Menolak SVG/script XSS, memverifikasi MIME asli via finfo + dimensi gambar via getimagesize + scan payload.
     */
    public static function validatePublicImageUpload(array $file, int $maxSizeBytes = 2097152, bool $allowIco = false): array {
        if (empty($file['tmp_name']) || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Berkas gambar tidak valid atau gagal diunggah.'];
        }

        if (($file['size'] ?? 0) > $maxSizeBytes) {
            $maxMb = round($maxSizeBytes / (1024 * 1024), 1);
            return ['valid' => false, 'message' => "Ukuran berkas gambar melebihi batas maksimal {$maxMb} MB."];
        }

        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $allowedExts = ['png', 'jpg', 'jpeg', 'webp'];
        if ($allowIco) {
            $allowedExts[] = 'ico';
        }

        if (!in_array($ext, $allowedExts, true)) {
            $extList = strtoupper(implode(', ', $allowedExts));
            return ['valid' => false, 'message' => "Format berkas tidak diizinkan (demi keamanan anti-XSS, SVG tidak diperbolehkan). Gunakan format: {$extList}."];
        }

        // Validasi MIME asli via finfo
        if (class_exists('\finfo')) {
            $finfo = new \finfo(FILEINFO_MIME_TYPE);
            $mime = strtolower((string)$finfo->file($file['tmp_name']));
            $allowedMimes = [
                'image/png', 'image/x-png',
                'image/jpeg', 'image/pjpeg', 'image/jpg',
                'image/webp',
            ];
            if ($allowIco) {
                $allowedMimes = array_merge($allowedMimes, [
                    'image/x-icon', 'image/vnd.microsoft.icon', 'image/ico', 'image/x-ico'
                ]);
            }
            if (!in_array($mime, $allowedMimes, true)) {
                return ['valid' => false, 'message' => 'Tipe konten berkas (' . htmlspecialchars($mime) . ') bukan gambar raster yang valid.'];
            }
        }

        // Validasi struktur header gambar via getimagesize (kecuali .ico tertentu yang kadang tidak dikenali getimagesize lama)
        $imgInfo = @getimagesize($file['tmp_name']);
        if ($imgInfo === false && $ext !== 'ico') {
            return ['valid' => false, 'message' => 'Berkas rusak atau bukan berkas gambar asli.'];
        }

        // Scan 8KB pertama dari kemungkinan injeksi tag PHP / HTML / Script
        $headBytes = (string)@file_get_contents($file['tmp_name'], false, null, 0, 8192);
        if (preg_match('/(<\?php|<\?=|<script\b|onload\s*=|onerror\s*=)/i', $headBytes)) {
            return ['valid' => false, 'message' => 'Berkas ditolak karena terdeteksi mengandung kode skrip berbahaya.'];
        }

        return ['valid' => true, 'ext' => $ext];
    }

    /**
     * Memastikan request state-changing via metode GET berasal dari navigasi internal (Same-Origin)
     * untuk memitigasi serangan Cross-Site GET / <img src="..."> tanpa memutus tautan UI yang ada.
     */
    public static function isSameOriginRequest(): bool {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        if ($method === 'POST') {
            return true; // Sudah divalidasi oleh CSRF::verify() di Router
        }

        // Jika menyertakan csrf_token yang valid di query string
        if (!empty($_GET['csrf_token']) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$_GET['csrf_token'])) {
            return true;
        }

        // Periksa header modern Sec-Fetch-Site jika dikirim oleh browser
        $secFetchSite = strtolower($_SERVER['HTTP_SEC_FETCH_SITE'] ?? '');
        if ($secFetchSite !== '') {
            if (in_array($secFetchSite, ['same-origin', 'none'], true)) {
                return true;
            }
            if ($secFetchSite === 'cross-site') {
                return false;
            }
        }

        // Periksa HTTP_REFERER harus memiliki host yang sama
        $referer = $_SERVER['HTTP_REFERER'] ?? '';
        $currentHost = strtolower((string)parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), PHP_URL_HOST));
        if ($referer !== '') {
            $refHost = strtolower((string)parse_url($referer, PHP_URL_HOST));
            return $refHost !== '' && $refHost === $currentHost;
        }

        // Jika navigasi langsung di address bar (tanpa referer dan bukan cross-site)
        return true;
    }
}

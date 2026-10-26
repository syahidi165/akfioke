<?php
// core/SecurityHelper.php
namespace Core;

class SecurityHelper {
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

    public static function encrypt(string $plainText): string {
        $config = require dirname(__DIR__) . '/config/security.php';
        $key = self::getEncryptionKey();
        $cipher = $config['cipher'] ?? 'AES-256-CBC';
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $cipherTextRaw = openssl_encrypt($plainText, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $cipherTextRaw, $key, true);
        return base64_encode($iv . $hmac . $cipherTextRaw);
    }

    public static function decrypt(?string $cipherText): ?string {
        if (empty($cipherText)) return null;
        $config = require dirname(__DIR__) . '/config/security.php';
        $key = self::getEncryptionKey();
        $cipher = $config['cipher'] ?? 'AES-256-CBC';
        $c = base64_decode($cipherText);
        $ivlen = openssl_cipher_iv_length($cipher);
        if (strlen($c) < $ivlen + 32) return null;
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, 32);
        $cipherTextRaw = substr($c, $ivlen + 32);
        $calcmac = hash_hmac('sha256', $cipherTextRaw, $key, true);
        if (hash_equals($hmac, $calcmac)) {
            return openssl_decrypt($cipherTextRaw, $cipher, $key, OPENSSL_RAW_DATA, $iv);
        }
        return null;
    }
}

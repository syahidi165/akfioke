<?php
// core/Auth.php
namespace Core;

class Auth {
    public static function startSession(): void {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        $config = require dirname(__DIR__) . '/config/security.php';
        
        ini_set('session.use_strict_mode', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Lax');
        if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
            ini_set('session.cookie_secure', '1');
        }

        session_start();

        // Cek timeout idle (30 menit)
        $lifetime = $config['session_lifetime'];
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > $lifetime)) {
            self::logout();
            header('Location: ' . self::baseUrl() . '/login?timeout=1');
            exit;
        }
        $_SESSION['last_activity'] = time();

        // Cek batas absolut sesi (8 jam)
        $absoluteLimit = $config['session_absolute_limit'];
        if (isset($_SESSION['created_time']) && (time() - $_SESSION['created_time'] > $absoluteLimit)) {
            self::logout();
            header('Location: ' . self::baseUrl() . '/login?timeout=2');
            exit;
        }
    }

    public static function attempt(string $username, string $password): array {
        self::startSession();
        $config = require dirname(__DIR__) . '/config/security.php';
        $maxAttempts = $config['max_login_attempts'];
        $lockoutSecs = $config['lockout_time'];
        $ipMaxAttempts = $config['ip_max_login_attempts'] ?? 15;
        $ipLockoutSecs = $config['ip_lockout_time'] ?? 900;

        // 1. Rate Limiting berbasis Alamat IP (mencegah Password Spraying & Lockout DoS)
        $clientIp = SecurityHelper::getClientIp();
        $recentIpFails = Database::fetch(
            "SELECT COUNT(*) as fail_count FROM audit_logs 
             WHERE ip_address = ? AND action = 'LOGIN_FAILED' 
             AND created_at >= DATE_SUB(NOW(), INTERVAL ? SECOND)",
            [$clientIp, $ipLockoutSecs]
        );

        if ($recentIpFails && (int)$recentIpFails['fail_count'] >= $ipMaxAttempts) {
            AuditLog::log('IP_RATE_LIMITED', 'users', null, null, [
                'ip' => $clientIp,
                'fail_count' => (int)$recentIpFails['fail_count'],
                'attempted_username' => $username
            ]);
            return [
                'success' => false,
                'message' => 'Terlalu banyak percobaan login gagal dari jaringan/IP Anda. Demi alasan keamanan, silakan tunggu 15 menit sebelum mencoba kembali.'
            ];
        }

        $usernameClean = trim($username);
        $user = Database::fetch(
            "SELECT u.* FROM users u 
             LEFT JOIN employees e ON e.user_id = u.id 
             WHERE u.username = ? OR LOWER(u.username) = LOWER(?) OR LOWER(e.nipy) = LOWER(?) OR LOWER(u.email) = LOWER(?) 
             LIMIT 1", 
            [$usernameClean, $usernameClean, $usernameClean, $usernameClean]
        );

        if (!$user) {
            AuditLog::log('LOGIN_FAILED', 'users', null, null, ['username' => $username, 'reason' => 'User not found']);
            return ['success' => false, 'message' => 'Username atau password salah.'];
        }

        // Cek status aktif
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'Akun Anda dinonaktifkan. Silakan hubungi Administrator.'];
        }

        // Cek lockout time
        if ($user['lockout_until'] && strtotime($user['lockout_until']) > time()) {
            $remaining = ceil((strtotime($user['lockout_until']) - time()) / 60);
            return ['success' => false, 'message' => "Akun terkunci sementara karena terlalu banyak percobaan gagal. Silakan tunggu {$remaining} menit."];
        }

        // Verifikasi password
        if (!password_verify($password, $user['password_hash'])) {
            $newAttempts = $user['failed_login_attempts'] + 1;
            $lockoutSql = "failed_login_attempts = ?";
            $params = [$newAttempts];

            if ($newAttempts >= $maxAttempts) {
                $lockoutUntil = date('Y-m-d H:i:s', time() + $lockoutSecs);
                $lockoutSql .= ", lockout_until = '{$lockoutUntil}'";
            }
            Database::execute("UPDATE users SET {$lockoutSql} WHERE id = ?", array_merge($params, [$user['id']]));

            AuditLog::log('LOGIN_FAILED', 'users', $user['id'], null, ['attempts' => $newAttempts]);
            return ['success' => false, 'message' => 'Username atau password salah.'];
        }

        // Reset lockout & catat last login
        Database::execute("UPDATE users SET failed_login_attempts = 0, lockout_until = NULL, last_login_at = NOW() WHERE id = ?", [$user['id']]);

        // Cek rehash jika cost berubah
        if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($password, PASSWORD_DEFAULT);
            Database::execute("UPDATE users SET password_hash = ? WHERE id = ?", [$newHash, $user['id']]);
        }

        // Session Regeneration (Hardening Sesi)
        session_regenerate_id(true);

        // Ambil roles user
        $roles = Database::fetchAll(
            "SELECT r.*, ur.is_primary FROM roles r 
             JOIN user_roles ur ON r.id = ur.role_id 
             WHERE ur.user_id = ? ORDER BY ur.is_primary DESC, r.id ASC",
            [$user['id']]
        );

        if (empty($roles)) {
            return ['success' => false, 'message' => 'Akun Anda belum memiliki peran (role) yang ditugaskan.'];
        }

        // Ambil data pegawai bila ada (untuk unit_id default)
        $employee = Database::fetch("SELECT * FROM employees WHERE user_id = ? AND is_deleted = 0", [$user['id']]);
        // Ambil data siswa bila ada
        $student = Database::fetch("SELECT * FROM students WHERE student_user_id = ?", [$user['id']]);

        // Tentukan Tahun Ajaran Aktif
        $activeTa = Database::fetch("SELECT * FROM academic_years WHERE is_active = 1 LIMIT 1");
        if (!$activeTa) {
            $activeTa = Database::fetch("SELECT * FROM academic_years ORDER BY id DESC LIMIT 1");
        }

        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_username'] = $user['username'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['force_change_password'] = (bool)$user['force_change_password'];
        $_SESSION['roles'] = $roles;
        $_SESSION['active_role'] = $roles[0]; // Set primary role as initial active role
        $_SESSION['created_time'] = time();
        $_SESSION['last_activity'] = time();
        $_SESSION['academic_year_id'] = $activeTa ? (int)$activeTa['id'] : 1;
        $_SESSION['academic_year_name'] = $activeTa ? "{$activeTa['name']} ({$activeTa['semester']})" : '2024/2025 (Genap)';
        $_SESSION['unit_id'] = $user['unit_id'] ?? ($employee['unit_id'] ?? ($student['unit_id'] ?? 3));

        AuditLog::log('LOGIN_SUCCESS', 'users', $user['id'], null, ['active_role' => $roles[0]['name']]);

        return [
            'success'               => true,
            'force_change_password' => (bool)$user['force_change_password'],
            'redirect'              => (bool)$user['force_change_password'] ? self::baseUrl() . '/change-password' : self::baseUrl() . '/dashboard'
        ];
    }

    public static function switchRole(int $roleId): bool {
        self::startSession();
        if (empty($_SESSION['roles'])) {
            return false;
        }

        foreach ($_SESSION['roles'] as $r) {
            if ((int)$r['id'] === $roleId) {
                $_SESSION['active_role'] = $r;
                session_regenerate_id(true);
                AuditLog::log('SWITCH_ROLE', 'roles', $roleId, null, ['new_role' => $r['name']]);
                return true;
            }
        }
        return false;
    }

    public static function check(): bool {
        self::startSession();
        return isset($_SESSION['user_id']);
    }

    public static function id(): ?int {
        self::startSession();
        return $_SESSION['user_id'] ?? null;
    }

    public static function employeeId(): ?int {
        self::startSession();
        $userId = self::id();
        if (!$userId) return null;
        $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ? AND is_deleted = 0 LIMIT 1", [$userId]);
        return $emp ? (int)$emp['id'] : null;
    }

    public static function user(): ?array {
        self::startSession();
        if (!isset($_SESSION['user_id'])) return null;
        return [
            'id'       => $_SESSION['user_id'],
            'username' => $_SESSION['user_username'] ?? '',
            'name'     => $_SESSION['user_name'] ?? '',
            'email'    => $_SESSION['user_email'] ?? '',
        ];
    }

    public static function roles(): array {
        self::startSession();
        return $_SESSION['roles'] ?? [];
    }

    public static function activeRole(): array {
        self::startSession();
        return $_SESSION['active_role'] ?? ['id' => 0, 'name' => 'guest', 'display_name' => 'Tamu'];
    }

    public static function activeRoleName(): string {
        return self::activeRole()['name'] ?? 'guest';
    }

    public static function role(): string {
        return self::activeRoleName();
    }

    public static function hasRole(string $roleName): bool {
        return self::activeRoleName() === $roleName;
    }

    public static function hasAnyRole(array $roleNames): bool {
        return in_array(self::activeRoleName(), $roleNames, true);
    }

    public static function requireAuth(): void {
        self::startSession();
        if (!self::check()) {
            header('Location: ' . self::baseUrl() . '/login');
            exit;
        }

        if (!empty($_SESSION['force_change_password'])) {
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            if (strpos($currentPath, '/change-password') === false && strpos($currentPath, '/logout') === false) {
                header('Location: ' . self::baseUrl() . '/change-password');
                exit;
            }
        }
    }

    public static function requireRole(array|string $roles): void {
        self::requireAuth();
        $roleList = is_array($roles) ? $roles : [$roles];
        
        // Operator Yayasan memiliki hak supervisi master
        if (self::hasRole('operator_yayasan')) {
            return;
        }

        if (!in_array(self::activeRoleName(), $roleList, true)) {
            http_response_code(403);
            die('Akses Ditolak (403): Peran aktif Anda (' . htmlspecialchars(self::activeRole()['display_name']) . ') tidak memiliki wewenang untuk membuka halaman ini.');
        }
    }

    public static function unitId(): int {
        self::startSession();
        return (int)($_SESSION['unit_id'] ?? 3);
    }

    public static function setUnitId(int $unitId): void {
        self::startSession();
        $_SESSION['unit_id'] = $unitId;
    }

    public static function academicYearId(): int {
        self::startSession();
        return (int)($_SESSION['academic_year_id'] ?? 1);
    }

    public static function academicYearName(): string {
        self::startSession();
        if (!empty($_SESSION['academic_year_name'])) {
            return $_SESSION['academic_year_name'];
        }
        $taId = self::academicYearId();
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        if ($ta) {
            $_SESSION['academic_year_name'] = "{$ta['name']} ({$ta['semester']})";
            return $_SESSION['academic_year_name'];
        }
        return 'Tahun Ajaran Aktif';
    }

    public static function setAcademicYear(int $taId): void {
        self::startSession();
        $ta = Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);
        if ($ta) {
            $_SESSION['academic_year_id'] = (int)$ta['id'];
            $_SESSION['academic_year_name'] = "{$ta['name']} ({$ta['semester']})";
        }
    }

    /**
     * Anti-IDOR Authorization Check:
     * Cek apakah entity ID tersebut berhak diakses oleh user saat ini
     */
    public static function checkIdor(string $entityType, $entityId): bool {
        self::startSession();
        $userId = self::id();
        $role = self::activeRoleName();
        $unitId = self::unitId();

        // Operator Yayasan dan Direktur Pendidikan boleh melihat semua
        if (in_array($role, ['operator_yayasan', 'direktur_pendidikan'], true)) {
            return true;
        }

        switch ($entityType) {
            case 'unit':
                return (int)$entityId === $unitId || in_array($role, ['deputi_pendidikan'], true);

            case 'student':
                if ($role === 'orang_tua') {
                    $std = Database::fetch("SELECT id FROM students WHERE id = ? AND parent_user_id = ?", [$entityId, $userId]);
                    return (bool)$std;
                }
                if ($role === 'siswa') {
                    $std = Database::fetch("SELECT id FROM students WHERE id = ? AND student_user_id = ?", [$entityId, $userId]);
                    return (bool)$std;
                }
                // Jika guru/staf sekolah, periksa apakah siswa di unit yang sama
                $std = Database::fetch("SELECT id FROM students WHERE id = ? AND unit_id = ?", [$entityId, $unitId]);
                return (bool)$std;

            case 'class':
                $cls = Database::fetch("SELECT id, walas_id FROM classes WHERE id = ? AND unit_id = ?", [$entityId, $unitId]);
                if (!$cls) return false;
                if ($role === 'walas') {
                    $emp = Database::fetch("SELECT id FROM employees WHERE user_id = ?", [$userId]);
                    return $emp && (int)$cls['walas_id'] === (int)$emp['id'];
                }
                return true;

            case 'employee':
                if (in_array($role, ['kepala_sekolah', 'operator_sekolah', 'waka_kurikulum', 'waka_kesiswaan'], true)) {
                    $emp = Database::fetch("SELECT id FROM employees WHERE id = ? AND unit_id = ?", [$entityId, $unitId]);
                    return (bool)$emp;
                }
                // Pegawai sendiri
                $emp = Database::fetch("SELECT id FROM employees WHERE id = ? AND user_id = ?", [$entityId, $userId]);
                return (bool)$emp;

            case 'bill':
                if ($role === 'orang_tua') {
                    $b = Database::fetch("SELECT b.id FROM bills b JOIN students s ON b.student_id = s.id WHERE b.id = ? AND s.parent_user_id = ?", [$entityId, $userId]);
                    return (bool)$b;
                }
                $b = Database::fetch("SELECT id FROM bills WHERE id = ? AND unit_id = ?", [$entityId, $unitId]);
                return (bool)$b;

            default:
                return true;
        }
    }

    public static function logout(): void {
        self::startSession();
        if (isset($_SESSION['user_id'])) {
            AuditLog::log('LOGOUT', 'users', $_SESSION['user_id']);
        }
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    public static function baseUrl(): string {
        $config = require dirname(__DIR__) . '/config/app.php';
        return rtrim($config['base_path'], '/');
    }

    /**
     * Memvalidasi kompleksitas password sesuai standar keamanan modern
     */
    public static function validatePasswordStrength(string $password, ?string $username = null): array {
        $config = require dirname(__DIR__) . '/config/security.php';
        $minLen = $config['min_password_length'] ?? 10;
        $banned = $config['banned_passwords'] ?? [];

        if (strlen($password) < $minLen) {
            return [
                'valid' => false,
                'message' => "Kata sandi baru minimal harus {$minLen} karakter."
            ];
        }

        // Cek daftar kata sandi umum / pasaran
        $pwdLower = strtolower($password);
        foreach ($banned as $bad) {
            if ($pwdLower === strtolower($bad) || strpos($pwdLower, strtolower($bad)) !== false) {
                return [
                    'valid' => false,
                    'message' => "Kata sandi terlalu umum/mudah ditebak. Hindari kata seperti '{$bad}'."
                ];
            }
        }

        // Cek jangan mengandung username
        if (!empty($username) && strlen($username) >= 3) {
            if (strpos($pwdLower, strtolower($username)) !== false) {
                return [
                    'valid' => false,
                    'message' => "Kata sandi tidak boleh mengandung username Anda."
                ];
            }
        }

        // Cek ragam karakter: kombinasi huruf besar, huruf kecil, angka, atau simbol
        $hasUpper   = preg_match('/[A-Z]/', $password);
        $hasLower   = preg_match('/[a-z]/', $password);
        $hasDigit   = preg_match('/[0-9]/', $password);
        $hasSpecial = preg_match('/[^A-Za-z0-9]/', $password);

        $setsCount = ($hasUpper ? 1 : 0) + ($hasLower ? 1 : 0) + ($hasDigit ? 1 : 0) + ($hasSpecial ? 1 : 0);
        if ($setsCount < 3) {
            return [
                'valid' => false,
                'message' => "Kata sandi harus mengandung kombinasi setidaknya 3 dari jenis berikut: huruf besar, huruf kecil, angka, atau karakter khusus."
            ];
        }

        return ['valid' => true, 'message' => 'Kata sandi memenuhi standar keamanan.'];
    }
}

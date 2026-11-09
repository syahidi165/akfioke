<?php
// core/Controller.php
namespace Core;

abstract class Controller {
    protected function view(string $viewPath, array $data = [], ?string $layout = 'layouts/main'): void {
        View::render($viewPath, $data, $layout);
    }

    protected function json(array $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function redirect(string $url): void {
        $url = trim(str_replace(["\r", "\n"], '', $url));

        // Cegah skema berbahaya (javascript:, data:, vbscript:)
        if (preg_match('/^(javascript|data|vbscript):/i', $url)) {
            $url = Auth::baseUrl() . '/dashboard';
        } elseif (preg_match('#^(https?:)?//#i', $url)) {
            // Validasi host untuk mencegah Open Redirect ke domain eksternal
            $targetHost = strtolower((string)parse_url($url, PHP_URL_HOST));
            $currentHost = strtolower((string)parse_url('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost'), PHP_URL_HOST));
            if ($targetHost === '' || $targetHost !== $currentHost) {
                $url = Auth::baseUrl() . '/dashboard';
            }
        }

        header('Location: ' . $url);
        exit;
    }

    protected function setFlash(string $type, string $message): void {
        Auth::startSession();
        $_SESSION['flash'] = [
            'type'    => $type, // success, error, warning, info
            'message' => $message
        ];
    }

    protected function getFlash(): ?array {
        Auth::startSession();
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    protected function requireAuth(): void {
        Auth::requireAuth();
    }

    protected function requireRole(array|string $roles): void {
        Auth::requireRole($roles);
    }

    /**
     * Anti-IDOR Centralized Ownership Assertion:
     * Memastikan entitas di $table dengan ID $recordId berada di unit kerja yang sama dengan user yang login.
     * Mencegah eksploitasi manipulasi parameter ID (IDOR) pada seluruh endpoint edit/hapus/detail.
     */
    protected function assertUnitOwnership(string $table, int $recordId, string $unitColumn = 'unit_id'): array {
        $this->requireAuth();
        $record = Database::fetch("SELECT * FROM `{$table}` WHERE id = ? LIMIT 1", [$recordId]);
        if (!$record) {
            http_response_code(404);
            die("Data tidak ditemukan (ID: {$recordId}).");
        }

        $activeRole = Auth::activeRoleName();
        // Super roles (Yayasan, Direktur, HRD, Pembina) diizinkan lintas unit
        $crossUnitRoles = [
            'operator_yayasan', 'direktur_pendidikan', 'hrd',
            'direktur_pengadaan', 'direktur_sarpras', 'ketua_yayasan', 'pembina_yayasan'
        ];
        if (in_array($activeRole, $crossUnitRoles, true)) {
            return $record;
        }

        if (isset($record[$unitColumn]) && $record[$unitColumn] !== null) {
            $recordUnitId = (int)$record[$unitColumn];

            // Deputi Pendidikan diizinkan mengakses unit yang berada dalam cakupan binaannya
            if ($activeRole === 'deputi_pendidikan') {
                $deputiUnits = class_exists('\App\Controllers\DeputiController')
                    ? \App\Controllers\DeputiController::getDeputiUnitIds(Auth::id())
                    : [];
                if (in_array($recordUnitId, $deputiUnits, true)) {
                    return $record;
                }
            }

            $userUnitId = Auth::unitId();
            if ($recordUnitId !== (int)$userUnitId) {
                AuditLog::log('IDOR_ATTEMPT_BLOCKED', $table, $recordId, null, [
                    'user_unit'   => $userUnitId,
                    'record_unit' => $record[$unitColumn],
                    'uri'         => $_SERVER['REQUEST_URI'] ?? ''
                ]);
                http_response_code(403);
                die("Akses Ditolak (403 IDOR Blocked): Anda tidak memiliki izin untuk mengelola atau mengakses data dari unit sekolah lain.");
            }
        }

        return $record;
    }

    /**
     * Memastikan pengguna yang login memiliki hak akses terhadap data seorang santri/siswa:
     * - Orang Tua: Hanya santri yang terhubung dengan akun orang tua tersebut (parent_user_id / KK / nama ortu)
     * - Santri/Siswa: Hanya dirinya sendiri
     * - Staf/Guru Unit: Hanya santri di unit sekolah yang sama (atau lintas unit bagi Yayasan/Deputi)
     */
    protected function assertStudentAccess(int $studentId): array {
        $this->requireAuth();
        $student = Database::fetch("SELECT * FROM students WHERE id = ? LIMIT 1", [$studentId]);
        if (!$student) {
            http_response_code(404);
            die("Data santri tidak ditemukan.");
        }

        $activeRole = Auth::activeRoleName();
        $userId = Auth::id();

        if ($activeRole === 'orang_tua') {
            $primaryChildren = Database::fetchAll(
                "SELECT id, no_kk, father_name, mother_name, parent_phone FROM students WHERE parent_user_id = ?",
                [$userId]
            );
            $allowedIds = array_map('intval', array_column($primaryChildren, 'id'));
            if (in_array((int)$student['id'], $allowedIds, true)) {
                return $student;
            }

            // Cek kecocokan KK / nama orang tua (sibling linkage)
            foreach ($primaryChildren as $pc) {
                if (!empty($pc['no_kk']) && strlen(trim($pc['no_kk'])) >= 10 && trim((string)$student['no_kk']) === trim($pc['no_kk'])) {
                    return $student;
                }
                if (!empty($pc['father_name']) && !empty($pc['mother_name'])
                    && strcasecmp(trim((string)$student['father_name']), trim($pc['father_name'])) === 0
                    && strcasecmp(trim((string)$student['mother_name']), trim($pc['mother_name'])) === 0) {
                    return $student;
                }
            }

            AuditLog::log('IDOR_STUDENT_ACCESS_BLOCKED', 'students', $studentId, null, [
                'user_id' => $userId,
                'role'    => $activeRole,
                'uri'     => $_SERVER['REQUEST_URI'] ?? ''
            ]);
            http_response_code(403);
            die("Akses Ditolak (403): Anda hanya dapat mengakses dokumen milik putra/putri Anda sendiri.");
        }

        if (in_array($activeRole, ['santri', 'siswa'], true)) {
            if ((int)($student['student_user_id'] ?? 0) !== (int)$userId) {
                http_response_code(403);
                die("Akses Ditolak (403): Anda hanya dapat mengakses data milik Anda sendiri.");
            }
            return $student;
        }

        return $this->assertUnitOwnership('students', $studentId, 'unit_id');
    }
}

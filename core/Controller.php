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
        // Super roles (Yayasan & Direktur Pendidikan) diizinkan lintas unit
        if (in_array($activeRole, ['operator_yayasan', 'direktur_pendidikan'], true)) {
            return $record;
        }

        if (isset($record[$unitColumn])) {
            $userUnitId = Auth::unitId();
            if ((int)$record[$unitColumn] !== (int)$userUnitId) {
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
}

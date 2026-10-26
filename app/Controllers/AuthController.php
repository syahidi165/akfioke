<?php
// app/Controllers/AuthController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\CSRF;
use Core\Database;
use Core\AuditLog;
use Core\SecurityHelper;

class AuthController extends Controller {
    public function showLogin(): void {
        Auth::startSession();
        if (Auth::check()) {
            $this->redirect(Auth::baseUrl() . '/dashboard');
        }

        $timeout = $_GET['timeout'] ?? null;
        $errorMsg = null;
        if ($timeout == 1) {
            $errorMsg = 'Sesi Anda telah berakhir karena tidak aktif selama 30 menit. Silakan login kembali.';
        } elseif ($timeout == 2) {
            $errorMsg = 'Batas durasi login harian (8 jam) telah berakhir. Silakan login kembali demi keamanan.';
        }

        $this->view('auth/login', [
            'pageTitle' => 'Login - Sistem Informasi Yayasan Islam Al Kahfi Batam',
            'errorMsg'  => $errorMsg
        ], 'layouts/plain');
    }

    public function login(): void {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $this->view('auth/login', [
                'pageTitle' => 'Login - AKFIOKE',
                'errorMsg'  => 'Username dan password wajib diisi.'
            ], 'layouts/plain');
            return;
        }

        $result = Auth::attempt($username, $password);

        if (!$result['success']) {
            $this->view('auth/login', [
                'pageTitle' => 'Login - AKFIOKE',
                'errorMsg'  => $result['message'],
                'oldUser'   => $username
            ], 'layouts/plain');
            return;
        }

        $this->redirect($result['redirect']);
    }

    public function showChangePassword(): void {
        Auth::requireAuth();
        $this->view('auth/change_password', [
            'pageTitle' => 'Ganti Kata Sandi - AKFIOKE'
        ], 'layouts/plain');
    }

    public function changePassword(): void {
        Auth::requireAuth();
        $oldPassword = $_POST['old_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        $userId = Auth::id();
        $user = Database::fetch("SELECT * FROM users WHERE id = ?", [$userId]);

        // Verifikasi password lama jika bukan force change pertama kali
        if (!$user['force_change_password']) {
            if (!password_verify($oldPassword, $user['password_hash'])) {
                $this->setFlash('error', 'Kata sandi saat ini tidak sesuai.');
                $this->redirect(Auth::baseUrl() . '/change-password');
            }
        }

        // Larangan memakai ulang kata sandi saat ini
        if (password_verify($newPassword, $user['password_hash'])) {
            $this->setFlash('error', 'Kata sandi baru tidak boleh sama dengan kata sandi saat ini.');
            $this->redirect(Auth::baseUrl() . '/change-password');
        }

        // Validasi kompleksitas password (panjang minimal 10, anti-kamus, kombinasi karakter)
        $val = Auth::validatePasswordStrength($newPassword, $user['username'] ?? null);
        if (!$val['valid']) {
            $this->setFlash('error', $val['message']);
            $this->redirect(Auth::baseUrl() . '/change-password');
        }

        if ($newPassword !== $confirmPassword) {
            $this->setFlash('error', 'Konfirmasi kata sandi baru tidak cocok.');
            $this->redirect(Auth::baseUrl() . '/change-password');
        }

        $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
        Database::execute(
            "UPDATE users SET password_hash = ?, force_change_password = 0, updated_at = NOW() WHERE id = ?",
            [$newHash, $userId]
        );

        $_SESSION['force_change_password'] = false;
        AuditLog::log('CHANGE_PASSWORD', 'users', $userId);

        $this->setFlash('success', 'Kata sandi berhasil diperbarui.');
        $this->redirect(Auth::baseUrl() . '/dashboard');
    }

    public function switchRole(): void {
        Auth::requireAuth();
        $roleId = (int)($_POST['role_id'] ?? 0);
        if ($roleId > 0) {
            Auth::switchRole($roleId);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard');
        $this->redirect(Auth::baseUrl() . '/dashboard');
    }

    public function switchTa(): void {
        Auth::requireAuth();
        $taId = (int)($_POST['academic_year_id'] ?? 0);
        if ($taId > 0) {
            Auth::setAcademicYear($taId);
        }
        $referer = $_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard');
        $this->redirect($referer);
    }

    public function logout(): void {
        Auth::logout();
        $this->redirect(Auth::baseUrl() . '/login');
    }
}

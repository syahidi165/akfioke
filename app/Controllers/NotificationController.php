<?php
// app/Controllers/NotificationController.php
namespace App\Controllers;

use Core\Controller;
use Core\Auth;
use Core\Database;
use Core\Notification;

class NotificationController extends Controller {

    /**
     * Halaman daftar semua riwayat notifikasi pengguna
     */
    public function index(): void {
        $this->requireAuth();
        $userId = Auth::id();

        $notifications = Database::fetchAll(
            "SELECT * FROM notifications 
             WHERE user_id = ? 
             ORDER BY created_at DESC 
             LIMIT 100",
            [$userId]
        );

        $unreadCount = Notification::countUnread($userId);

        $this->view('notifications/index', [
            'pageTitle'     => 'Notifikasi Sistem',
            'notifications' => $notifications,
            'unreadCount'   => $unreadCount
        ]);
    }

    /**
     * Tandai satu notifikasi telah dibaca lalu arahkan ke URL tujuan
     */
    public function read(string $id): void {
        $this->requireAuth();
        $userId = Auth::id();
        $notifId = (int)$id;

        if ($userId && $notifId > 0) {
            $notif = Database::fetch(
                "SELECT * FROM notifications WHERE id = ? AND user_id = ?",
                [$notifId, $userId]
            );

            if ($notif) {
                Notification::markAsRead($notifId, $userId);

                if (!empty($notif['url'])) {
                    $targetUrl = trim($notif['url']);
                    // Jika URL adalah path relatif, tambahkan baseUrl
                    if (!str_starts_with($targetUrl, 'http://') && !str_starts_with($targetUrl, 'https://')) {
                        $targetUrl = Auth::baseUrl() . '/' . ltrim($targetUrl, '/');
                    }
                    $this->redirect($targetUrl);
                    return;
                }
            }
        }

        $referer = $_SERVER['HTTP_REFERER'] ?? (Auth::baseUrl() . '/dashboard');
        $this->redirect($referer);
    }

    /**
     * Tandai satu notifikasi telah dibaca tanpa meninggalkan halaman
     */
    public function markRead(string $id): void {
        $this->requireAuth();
        $userId = Auth::id();
        $notifId = (int)$id;

        if ($userId && $notifId > 0) {
            Notification::markAsRead($notifId, $userId);
            $this->setFlash('success', 'Notifikasi telah ditandai sudah dibaca.');
        }

        $this->redirect(Auth::baseUrl() . '/notifications');
    }

    /**
     * Tandai semua notifikasi belum dibaca sebagai telah dibaca
     */
    public function markAllRead(): void {
        $this->requireAuth();
        $userId = Auth::id();

        if ($userId) {
            Notification::markAllRead($userId);
            $this->setFlash('success', 'Semua notifikasi telah ditandai sudah dibaca.');
        }

        $this->redirect(Auth::baseUrl() . '/notifications');
    }

    /**
     * Hapus satu notifikasi milik pengguna
     */
    public function delete(string $id): void {
        $this->requireAuth();
        $userId = Auth::id();
        $notifId = (int)$id;

        if ($userId && $notifId > 0) {
            Notification::delete($notifId, $userId);
            $this->setFlash('success', 'Notifikasi berhasil dihapus.');
        }

        $this->redirect(Auth::baseUrl() . '/notifications');
    }

    /**
     * Hapus seluruh notifikasi milik pengguna
     */
    public function deleteAll(): void {
        $this->requireAuth();
        $userId = Auth::id();

        if ($userId) {
            Notification::deleteAll($userId);
            $this->setFlash('success', 'Seluruh riwayat notifikasi berhasil dibersihkan.');
        }

        $this->redirect(Auth::baseUrl() . '/notifications');
    }
}

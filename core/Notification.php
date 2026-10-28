<?php
// core/Notification.php
namespace Core;

class Notification {
    public static function send(int $userId, string $title, string $message, string $type = 'info', ?string $url = null): void {
        Database::execute(
            "INSERT INTO notifications (user_id, title, message, type, url, is_read, created_at) VALUES (?, ?, ?, ?, ?, 0, NOW())",
            [$userId, $title, $message, $type, $url]
        );
    }

    public static function broadcastToRole(string $roleName, string $title, string $message, string $type = 'info', ?string $url = null): void {
        $users = Database::fetchAll(
            "SELECT u.id FROM users u 
             JOIN user_roles ur ON u.id = ur.user_id 
             JOIN roles r ON ur.role_id = r.id 
             WHERE r.name = ? AND u.is_active = 1",
            [$roleName]
        );
        foreach ($users as $u) {
            self::send((int)$u['id'], $title, $message, $type, $url);
        }
    }

    public static function getUnread(int $userId, int $limit = 10): array {
        return Database::fetchAll(
            "SELECT * FROM notifications WHERE user_id = ? AND is_read = 0 ORDER BY created_at DESC LIMIT ?",
            [$userId, $limit]
        );
    }

    public static function countUnread(int $userId): int {
        $res = Database::fetch(
            "SELECT COUNT(*) as cnt FROM notifications WHERE user_id = ? AND is_read = 0",
            [$userId]
        );
        return (int)($res['cnt'] ?? 0);
    }

    public static function markAsRead(int $notificationId, int $userId): void {
        Database::execute(
            "UPDATE notifications SET is_read = 1 WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
    }

    public static function markAllRead(int $userId): void {
        Database::execute(
            "UPDATE notifications SET is_read = 1 WHERE user_id = ?",
            [$userId]
        );
    }

    public static function delete(int $notificationId, int $userId): void {
        Database::execute(
            "DELETE FROM notifications WHERE id = ? AND user_id = ?",
            [$notificationId, $userId]
        );
    }

    public static function deleteAll(int $userId): void {
        Database::execute(
            "DELETE FROM notifications WHERE user_id = ?",
            [$userId]
        );
    }
}

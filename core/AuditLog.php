<?php
// core/AuditLog.php
namespace Core;

class AuditLog {
    public static function log(string $action, string $tableName, $recordId = null, $oldValues = null, $newValues = null): void {
        try {
            $userId = $_SESSION['user_id'] ?? null;
            $username = $_SESSION['user_username'] ?? 'guest';
            $roleName = $_SESSION['active_role']['name'] ?? 'none';
            $ipAddress = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

            $oldJson = $oldValues !== null ? json_encode($oldValues, JSON_UNESCAPED_UNICODE) : null;
            $newJson = $newValues !== null ? json_encode($newValues, JSON_UNESCAPED_UNICODE) : null;

            Database::execute(
                "INSERT INTO audit_logs (user_id, username, role_name, ip_address, action, table_name, record_id, old_values, new_values, created_at) 
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())",
                [
                    $userId,
                    $username,
                    $roleName,
                    $ipAddress,
                    $action,
                    $tableName,
                    $recordId !== null ? (string)$recordId : null,
                    $oldJson,
                    $newJson
                ]
            );
        } catch (\Throwable $e) {
            // Catat ke log server agar kegagalan audit tidak mematikan flow aplikasi utama
            error_log("Gagal mencatat audit log: " . $e->getMessage());
        }
    }
}

<?php
require 'core/Database.php';
$db = Core\Database::connect();

try {
    // Alter database_backups backup_type to VARCHAR(50) so it supports 'Full (DB + Storage)'
    $db->exec("ALTER TABLE database_backups MODIFY COLUMN backup_type VARCHAR(50) DEFAULT 'Manual'");
    echo "database_backups backup_type modified to VARCHAR(50).\n";
} catch (Exception $e) {
    echo "Notice: " . $e->getMessage() . "\n";
}

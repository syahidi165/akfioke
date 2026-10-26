<?php
require 'core/Database.php';
$db = Core\Database::connect();
$h = $db->query('SELECT h.*, e.nama as tutor_name FROM tahfidz_halaqah h JOIN employees e ON h.tutor_employee_id = e.id')->fetchAll(PDO::FETCH_ASSOC);
echo "=== HALAQAH ===\n" . json_encode($h, JSON_PRETTY_PRINT) . "\n";

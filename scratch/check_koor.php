<?php
require 'core/Database.php';
$db = Core\Database::connect();
$users = $db->query("
    SELECT u.id as user_id, u.username, u.full_name, r.name as role_name, e.id as emp_id, e.nama as emp_name, e.unit_id as emp_unit_id, e.position, un.name as unit_name
    FROM users u
    JOIN user_roles ur ON u.id = ur.user_id
    JOIN roles r ON ur.role_id = r.id
    LEFT JOIN employees e ON u.id = e.user_id
    LEFT JOIN units un ON e.unit_id = un.id
    WHERE r.name IN ('koor_tahfidz', 'koordinator_tahfidz')
")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users, JSON_PRETTY_PRINT);

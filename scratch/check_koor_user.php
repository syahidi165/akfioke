<?php
require 'core/Database.php';
$db = Core\Database::connect();
$user = $db->query("SELECT u.*, r.name as role_name FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.name = 'koor_tahfidz'")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($user, JSON_PRETTY_PRINT);

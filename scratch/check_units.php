<?php
require 'core/Database.php';
$db = Core\Database::connect();
$units = $db->query("SELECT id, code, name FROM units ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($units, JSON_PRETTY_PRINT);

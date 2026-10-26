<?php
require 'core/Database.php';
$db = Core\Database::connect();
$cols = $db->query("DESCRIBE employees")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(array_column($cols, 'Field'), JSON_PRETTY_PRINT);

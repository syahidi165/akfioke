<?php
require 'core/Database.php';
$db = Core\Database::connect();
print_r($db->query("SELECT id, unit_id, code, name, type FROM subjects WHERE unit_id = 3")->fetchAll(PDO::FETCH_ASSOC));

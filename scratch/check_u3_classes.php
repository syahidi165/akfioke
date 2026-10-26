<?php
require 'core/Database.php';
$db = Core\Database::connect();
$c = $db->query('SELECT * FROM classes WHERE unit_id = 3 AND academic_year_id = 1')->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($c, JSON_PRETTY_PRINT);

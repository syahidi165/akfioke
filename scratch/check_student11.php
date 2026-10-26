<?php
require 'core/Database.php';
$db = Core\Database::connect();
print_r($db->query("SELECT cs.*, c.name as class_name, s.nama FROM class_students cs JOIN classes c ON cs.class_id = c.id JOIN students s ON cs.student_id = s.id WHERE s.id = 11")->fetchAll(PDO::FETCH_ASSOC));

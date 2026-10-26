<?php
require 'core/Database.php';
$db = Core\Database::connect();
echo "COLUMNS:\n";
print_r($db->query("DESCRIBE class_students")->fetchAll(PDO::FETCH_ASSOC));

echo "INDEXES:\n";
print_r($db->query("SHOW INDEX FROM class_students")->fetchAll(PDO::FETCH_ASSOC));

echo "DATA FOR KHADIJAH:\n";
print_r($db->query("
    SELECT cs.*, s.nama, c.name as class_name 
    FROM class_students cs
    JOIN students s ON cs.student_id = s.id
    JOIN classes c ON cs.class_id = c.id
    WHERE s.nama LIKE '%Khadijah%'
")->fetchAll(PDO::FETCH_ASSOC));

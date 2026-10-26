<?php
require 'core/Database.php';
$db = Core\Database::connect();
$dups = $db->query("
    SELECT student_id, academic_year_id, COUNT(*) as count, GROUP_CONCAT(id) as ids, GROUP_CONCAT(class_id) as class_ids
    FROM class_students
    WHERE status = 'Aktif'
    GROUP BY student_id, academic_year_id
    HAVING count > 1
")->fetchAll(PDO::FETCH_ASSOC);

echo "DUPLICATES IN CLASS_STUDENTS:\n";
print_r($dups);

<?php
require 'core/Database.php';
$db = Core\Database::connect();

echo "=== SUBJECTS ===\n";
$subjects = $db->query("SELECT id, unit_id, code, name, type FROM subjects")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($subjects, JSON_PRETTY_PRINT) . "\n\n";

echo "=== TEACHER SUBJECTS ===\n";
$ts = $db->query("
    SELECT ts.id, ts.employee_id, e.nama as teacher_name, ts.subject_id, s.name as subject_name, ts.class_id, c.name as class_name, ts.academic_year_id
    FROM teacher_subjects ts
    JOIN employees e ON ts.employee_id = e.id
    JOIN subjects s ON ts.subject_id = s.id
    JOIN classes c ON ts.class_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($ts, JSON_PRETTY_PRINT) . "\n\n";

echo "=== EMPLOYEES PER UNIT ===\n";
$emps = $db->query("
    SELECT id, nipy, nama, level_posisi, unit_id 
    FROM employees 
    WHERE status = 'Aktif'
    ORDER BY unit_id, nama
")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($emps, JSON_PRETTY_PRINT) . "\n";

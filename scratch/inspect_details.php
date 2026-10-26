<?php
require 'core/Database.php';
$db = Core\Database::connect();

echo "=== CLASSES ===\n";
$classes = $db->query("SELECT id, unit_id, academic_year_id, name, level FROM classes")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($classes, JSON_PRETTY_PRINT) . "\n\n";

echo "=== SUBJECTS ===\n";
$subjects = $db->query("SELECT id, unit_id, code, name FROM subjects")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($subjects, JSON_PRETTY_PRINT) . "\n\n";

echo "=== STUDENTS PER UNIT ===\n";
$stdPerUnit = $db->query("SELECT unit_id, status, count(*) as cnt FROM students GROUP BY unit_id, status")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($stdPerUnit, JSON_PRETTY_PRINT) . "\n\n";

echo "=== EMPLOYEES PER UNIT ===\n";
$empPerUnit = $db->query("SELECT unit_id, count(*) as cnt FROM employees WHERE is_deleted = 0 GROUP BY unit_id")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($empPerUnit, JSON_PRETTY_PRINT) . "\n\n";

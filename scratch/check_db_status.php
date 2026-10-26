<?php
require 'core/Database.php';
$db = Core\Database::connect();

echo "=== ACADEMIC YEARS ===\n";
$ta = $db->query("SELECT * FROM academic_years")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($ta, JSON_PRETTY_PRINT) . "\n\n";

echo "=== UNITS ===\n";
$units = $db->query("SELECT id, code, name FROM units")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($units, JSON_PRETTY_PRINT) . "\n\n";

echo "=== COUNTS ===\n";
$counts = [
    'employees' => $db->query("SELECT COUNT(*) FROM employees")->fetchColumn(),
    'students' => $db->query("SELECT COUNT(*) FROM students")->fetchColumn(),
    'classes' => $db->query("SELECT COUNT(*) FROM classes")->fetchColumn(),
    'subjects' => $db->query("SELECT COUNT(*) FROM subjects")->fetchColumn(),
    'student_grades' => $db->query("SELECT COUNT(*) FROM student_grades")->fetchColumn(),
    'student_attendance' => $db->query("SELECT COUNT(*) FROM student_attendance")->fetchColumn(),
    'employee_attendance' => $db->query("SELECT COUNT(*) FROM employee_attendance")->fetchColumn(),
    'bills' => $db->query("SELECT COUNT(*) FROM bills")->fetchColumn(),
    'supervisi_sessions' => $db->query("SELECT COUNT(*) FROM supervisi_sessions")->fetchColumn()
];
echo json_encode($counts, JSON_PRETTY_PRINT) . "\n";

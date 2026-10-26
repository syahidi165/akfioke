<?php
// scratch/test_jadwal_api.php
require 'vendor/autoload.php';
require 'core/Database.php';

session_start();

$db = Core\Database::connect();
$user = $db->query("SELECT * FROM users WHERE username = 'waka.kurikulum'")->fetch(PDO::FETCH_ASSOC);
$emp = $db->query("SELECT * FROM employees WHERE user_id = {$user['id']}")->fetch(PDO::FETCH_ASSOC);
$role = $db->query("
    SELECT r.*, ur.is_primary FROM roles r 
    JOIN user_roles ur ON r.id = ur.role_id 
    WHERE ur.user_id = {$user['id']}
")->fetch(PDO::FETCH_ASSOC);

$_SESSION['user_id'] = $user['id'];
$_SESSION['user_username'] = $user['username'];
$_SESSION['user_name'] = $user['full_name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['active_role'] = $role;
$_SESSION['roles'] = [$role];
$_SESSION['unit_id'] = $emp['unit_id'];
$_SESSION['academic_year_id'] = 1;

$_GET['day'] = 'Senin';
$_GET['jam_ke'] = 1;

// Simulate API call
$ctrl = new App\Controllers\JadwalController();

echo "Testing API call via reflection or buffer...\n";
// Since json() exits, let's test the database query logic
$unitId = 3;
$taId = 1;
$day = 'Senin';
$jamKe = 1;

$allTeachers = Core\Database::fetchAll(
    "SELECT id, nipy, nama, level_posisi FROM employees WHERE unit_id = ? AND is_deleted = 0 AND status = 'Aktif' ORDER BY nama ASC",
    [$unitId]
);

$busyRows = Core\Database::fetchAll(
    "SELECT cs.teacher_id, cs.substitute_teacher_id, cs.class_id, c.name as class_name, s.name as subject_name,
            t.nama as teacher_name, sub_t.nama as substitute_teacher_name
     FROM class_schedules cs
     JOIN classes c ON cs.class_id = c.id
     LEFT JOIN subjects s ON cs.subject_id = s.id
     LEFT JOIN employees t ON cs.teacher_id = t.id
     LEFT JOIN employees sub_t ON cs.substitute_teacher_id = sub_t.id
     WHERE cs.unit_id = ? AND cs.academic_year_id = ? AND cs.day = ? AND cs.jam_ke = ? AND cs.session_type = 'pelajaran'",
    [$unitId, $taId, $day, $jamKe]
);

echo "Total teachers in unit: " . count($allTeachers) . "\n";
echo "Busy schedule rows on Senin Jam 1: " . count($busyRows) . "\n";
foreach ($busyRows as $br) {
    echo "- " . ($br['substitute_teacher_name'] ?: $br['teacher_name']) . " -> " . $br['class_name'] . " (" . $br['subject_name'] . ")\n";
}

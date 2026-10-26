<?php
// scratch/test_slot_save.php
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

echo "Logged in as: " . $_SESSION['user_name'] . "\n";

// Let's test updating slot for Senin Jam 1: change from 07.45 - 08.20 to 07.30 - 08.15
$unitId = 3;
$taId = 1;
$day = 'Senin';
$jamKe = 1;
$timeStart = '07.30';
$timeEnd = '08.15';
$label = 'Jam Ke-1 (Pagi)';

// Check existing slot
$before = $db->query("SELECT * FROM schedule_time_slots WHERE unit_id = {$unitId} AND academic_year_id = {$taId} AND day = '{$day}' AND jam_ke = {$jamKe}")->fetch(PDO::FETCH_ASSOC);
echo "Before: " . ($before ? "{$before['time_start']} - {$before['time_end']} ({$before['label']})" : "Not found") . "\n";

// Update slot
$db->prepare("UPDATE schedule_time_slots SET time_start = ?, time_end = ?, label = ? WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?")
   ->execute([$timeStart, $timeEnd, $label, $unitId, $taId, $day, $jamKe]);

// Sync class schedules
$db->prepare("UPDATE class_schedules SET time_start = ?, time_end = ? WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?")
   ->execute([$timeStart, $timeEnd, $unitId, $taId, $day, $jamKe]);

$after = $db->query("SELECT * FROM schedule_time_slots WHERE unit_id = {$unitId} AND academic_year_id = {$taId} AND day = '{$day}' AND jam_ke = {$jamKe}")->fetch(PDO::FETCH_ASSOC);
echo "After: " . ($after ? "{$after['time_start']} - {$after['time_end']} ({$after['label']})" : "Not found") . "\n";

$schedCount = $db->query("SELECT COUNT(*) FROM class_schedules WHERE unit_id = {$unitId} AND academic_year_id = {$taId} AND day = '{$day}' AND jam_ke = {$jamKe} AND time_start = '{$timeStart}'")->fetchColumn();
echo "Synced schedules count: {$schedCount}\n";

// Restore back to original 07.45 - 08.20
$db->prepare("UPDATE schedule_time_slots SET time_start = '07.45', time_end = '08.20', label = 'Jam Ke-1' WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?")
   ->execute([$unitId, $taId, $day, $jamKe]);
$db->prepare("UPDATE class_schedules SET time_start = '07.45', time_end = '08.20' WHERE unit_id = ? AND academic_year_id = ? AND day = ? AND jam_ke = ?")
   ->execute([$unitId, $taId, $day, $jamKe]);

echo "Restored successfully!\n";

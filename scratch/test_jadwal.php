<?php
// scratch/test_jadwal.php
require 'vendor/autoload.php';
require 'core/Database.php';

session_start();

// Emulate login as waka.kurikulum
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
$_SESSION['academic_year_name'] = '2024/2025 (Genap)';

echo "Logged in as: " . $_SESSION['user_name'] . " with role: " . $role['name'] . "\n";
echo "Unit ID: " . $_SESSION['unit_id'] . "\n";

// Test JadwalController instantiation
$ctrl = new App\Controllers\JadwalController();

// Test API Teacher Status
$_GET['day'] = 'Senin';
$_GET['jam_ke'] = 1;

ob_start();
// Test apiTeacherStatus without exit by capturing output
try {
    // Call controller index method
    ob_start();
    $ctrl->index();
    $html = ob_get_clean();
    echo "index() executed successfully! Output HTML length: " . strlen($html) . " bytes\n";
    if (strpos($html, 'Tabel Jadwal Pelajaran Kelas') !== false) {
        echo "Found 'Tabel Jadwal Pelajaran Kelas' in HTML!\n";
    }
    if (strpos($html, 'BENTROK!') !== false) {
        echo "Found 'BENTROK!' detection in HTML!\n";
    }
    if (strpos($html, 'INVAL') !== false) {
        echo "Found 'INVAL' substitution in HTML!\n";
    }
} catch (\Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}

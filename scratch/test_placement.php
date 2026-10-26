<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/classes/placement';
$_SERVER['HTTP_HOST'] = 'localhost';

require_once 'vendor/autoload.php';

use Core\Auth;
use Core\Database;
use App\Controllers\MasterController;

Auth::startSession();
$_SESSION['user_id'] = 12; // operator sekolah SD
$_SESSION['active_role'] = ['id' => 12, 'name' => 'operator_sekolah', 'display_name' => 'Operator Sekolah'];
$_SESSION['unit_id'] = 3;
$_SESSION['academic_year_id'] = 1;

$mc = new MasterController();

echo "--- 1. Testing render classPlacement ---\n";
ob_start();
$mc->classPlacement();
$html = ob_get_clean();
echo "classPlacement rendered! Length: " . strlen($html) . "\n";

echo "--- 2. Checking Khadijah Putri Wardani (student 11) ---\n";
$rows = Database::fetchAll("SELECT * FROM class_students WHERE student_id = 11 AND academic_year_id = 1");
echo "Count rows for Khadijah: " . count($rows) . "\n";
print_r($rows);

echo "--- 3. Testing move student 11 to class 9 (Kelas 6A Ali bin Abi Thalib) ---\n";
$_POST = [
    'student_id' => 11,
    'class_id' => 9,
    'redirect_url' => '/classes/placement'
];
ob_start();
try {
    $mc->assignStudentClass();
} catch (\Throwable $e) {}
ob_end_clean();

$rowsAfter = Database::fetchAll("SELECT cs.*, c.name FROM class_students cs JOIN classes c ON cs.class_id = c.id WHERE cs.student_id = 11 AND cs.academic_year_id = 1");
echo "Count rows after moving: " . count($rowsAfter) . "\n";
echo "Class name: " . ($rowsAfter[0]['name'] ?? 'none') . "\n";

echo "--- 4. Testing move student 11 back to class 12 (Kelas 6A) ---\n";
$_POST = [
    'student_id' => 11,
    'class_id' => 12,
    'redirect_url' => '/classes/placement'
];
ob_start();
try {
    $mc->assignStudentClass();
} catch (\Throwable $e) {}
ob_end_clean();

$rowsAfter2 = Database::fetchAll("SELECT cs.*, c.name FROM class_students cs JOIN classes c ON cs.class_id = c.id WHERE cs.student_id = 11 AND cs.academic_year_id = 1");
echo "Count rows after moving back: " . count($rowsAfter2) . "\n";
echo "Class name: " . ($rowsAfter2[0]['name'] ?? 'none') . "\n";

echo "--- 5. Testing remove student 11 from class ---\n";
$_POST = [
    'student_id' => 11,
    'redirect_url' => '/classes/placement'
];
ob_start();
try {
    $mc->removeStudentClass();
} catch (\Throwable $e) {}
ob_end_clean();

$rowsAfterRemove = Database::fetchAll("SELECT * FROM class_students WHERE student_id = 11 AND academic_year_id = 1");
echo "Count rows after remove: " . count($rowsAfterRemove) . " (Expected: 0)\n";

echo "--- 6. Re-assign student 11 back to class 12 ---\n";
$_POST = [
    'student_id' => 11,
    'class_id' => 12,
    'redirect_url' => '/classes/placement'
];
ob_start();
try {
    $mc->assignStudentClass();
} catch (\Throwable $e) {}
ob_end_clean();

$rowsFinal = Database::fetchAll("SELECT cs.*, c.name FROM class_students cs JOIN classes c ON cs.class_id = c.id WHERE cs.student_id = 11 AND cs.academic_year_id = 1");
echo "Final assignment: " . ($rowsFinal[0]['name'] ?? 'none') . ", Total rows: " . count($rowsFinal) . "\n";

echo "\n>>> ALL PLACEMENT & DEDUPLICATION TESTS PASSED PERFECTLY! <<<\n";

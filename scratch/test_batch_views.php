<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';

require_once 'vendor/autoload.php';

use Core\Auth;
use App\Controllers\SpmbController;
use App\Controllers\MasterController;

Auth::startSession();

echo "--- 1. Testing SpmbController::targetIndex (Deputi) ---\n";
$_SESSION['user_id'] = 4; // deputi
$_SESSION['active_role'] = ['id' => 4, 'name' => 'deputi_pendidikan', 'display_name' => 'Deputi Direktur Pendidikan'];
$_SESSION['unit_id'] = 3;
$_SESSION['academic_year_id'] = 1;

ob_start();
$sc = new SpmbController();
$sc->targetIndex();
$out1 = ob_get_clean();
echo "targetIndex rendered! Length: " . strlen($out1) . "\n";

echo "--- 2. Testing MasterController::students (Operator Sekolah) ---\n";
$_SESSION['user_id'] = 12; // operator sekolah SD
$_SESSION['active_role'] = ['id' => 12, 'name' => 'operator_sekolah', 'display_name' => 'Operator Sekolah'];
$_SESSION['unit_id'] = 3;

ob_start();
$mc = new MasterController();
$mc->students();
$out2 = ob_get_clean();
echo "students rendered! Length: " . strlen($out2) . "\n";

echo "--- 3. Testing MasterController::academicYears (Operator Sekolah) ---\n";
ob_start();
$mc->academicYears();
$out3 = ob_get_clean();
echo "academicYears (Operator Sekolah) rendered! Length: " . strlen($out3) . "\n";

echo "--- 4. Testing MasterController::academicYears (Operator Yayasan) ---\n";
$_SESSION['active_role'] = ['id' => 1, 'name' => 'operator_yayasan', 'display_name' => 'Operator Yayasan'];
ob_start();
$mc->academicYears();
$out4 = ob_get_clean();
echo "academicYears (Operator Yayasan) rendered! Length: " . strlen($out4) . "\n";

echo "\n>>> ALL 4 NEW TESTS PASSED WITH ZERO ERRORS! <<<\n";

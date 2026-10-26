<?php
$_SERVER['REQUEST_METHOD'] = 'POST';
$_SERVER['REQUEST_URI'] = '/master/academic-years/approve';
$_SERVER['HTTP_HOST'] = 'localhost';

require_once 'vendor/autoload.php';

use Core\Auth;
use Core\Database;
use App\Controllers\MasterController;

Auth::startSession();
$_SESSION['user_id'] = 12;
$_SESSION['active_role'] = ['id' => 12, 'name' => 'operator_sekolah', 'display_name' => 'Operator Sekolah'];
$_SESSION['unit_id'] = 3;

echo "--- Testing rejection with reason ---\n";
$_POST = [
    'approval_id' => 17,
    'status' => 'Tidak Setuju',
    'rejection_reason' => 'Input nilai ledger kelas 6 dan pencetakan rapor akhir santri masih dalam proses penyelesaian.'
];

ob_start();
$mc = new MasterController();
try {
    $mc->approveAcademicYear();
} catch (\Throwable $e) {}
ob_end_clean();

$check = Database::fetch("SELECT * FROM academic_year_approvals WHERE id = 17");
echo "Status after reject: " . $check['status'] . "\n";
echo "Reason: " . $check['rejection_reason'] . "\n";

echo "--- Testing approval ---\n";
$_POST = [
    'approval_id' => 17,
    'status' => 'Setuju',
    'rejection_reason' => ''
];
ob_start();
try {
    $mc->approveAcademicYear();
} catch (\Throwable $e) {}
ob_end_clean();

$check2 = Database::fetch("SELECT * FROM academic_year_approvals WHERE id = 17");
echo "Status after approve: " . $check2['status'] . "\n";

// Reset to Menunggu for user testing
Database::execute("UPDATE academic_year_approvals SET status = 'Menunggu', rejection_reason = NULL WHERE id = 17");
echo "Reset to Menunggu successfully!\n";

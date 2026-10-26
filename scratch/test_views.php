<?php
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['HTTP_HOST'] = 'localhost';

require_once 'vendor/autoload.php';

use Core\Auth;
use App\Controllers\TahfidzController;
use App\Controllers\PanitiaLombaController;

Auth::startSession();
$_SESSION['user_id'] = 16;
$_SESSION['active_role'] = ['id' => 13, 'name' => 'koor_tahfidz', 'display_name' => 'Koordinator Tahfidz'];
$_SESSION['unit_id'] = 3;
$_SESSION['academic_year_id'] = 1;
$_SESSION['academic_year_name'] = '2024/2025 (Genap)';

echo "--- 1. Testing TahfidzController::baseline ---\n";
ob_start();
$tc = new TahfidzController();
$tc->baseline();
$out1 = ob_get_clean();
echo "Baseline rendered! Length: " . strlen($out1) . "\n";

echo "--- 2. Testing TahfidzController::targetSiswa (SD) ---\n";
ob_start();
$_GET['unit_id'] = 3;
$tc->targetSiswa();
$out2 = ob_get_clean();
echo "targetSiswa (SD) rendered! Length: " . strlen($out2) . "\n";

echo "--- 3. Testing TahfidzController::targetSiswa (SMP) ---\n";
ob_start();
$_GET['unit_id'] = 4;
$tc->targetSiswa();
$out3 = ob_get_clean();
echo "targetSiswa (SMP) rendered! Length: " . strlen($out3) . "\n";

echo "--- 4. Testing TahfidzController::targetSiswa (SMA) ---\n";
ob_start();
$_GET['unit_id'] = 5;
$tc->targetSiswa();
$out4 = ob_get_clean();
echo "targetSiswa (SMA) rendered! Length: " . strlen($out4) . "\n";

echo "--- 5. Testing TahfidzController::targetSiswa (KB/TK) ---\n";
ob_start();
$_GET['unit_id'] = 2;
$tc->targetSiswa();
$out5 = ob_get_clean();
echo "targetSiswa (KB/TK) rendered! Length: " . strlen($out5) . "\n";

echo "--- 6. Testing TahfidzController::halaqahSiswa ---\n";
ob_start();
$tc->halaqahSiswa();
$out6 = ob_get_clean();
echo "halaqahSiswa rendered! Length: " . strlen($out6) . "\n";

echo "--- 7. Testing Tutor Siswa: antreanUjian ---\n";
$_SESSION['active_role'] = ['id' => 14, 'name' => 'tutor_siswa', 'display_name' => 'Tutor Siswa'];
ob_start();
$tc->antreanUjian();
$out7 = ob_get_clean();
echo "antreanUjian rendered! Length: " . strlen($out7) . "\n";

echo "--- 8. Testing Tutor Siswa: guruNilai ---\n";
ob_start();
$tc->guruNilai();
$out8 = ob_get_clean();
echo "guruNilai rendered! Length: " . strlen($out8) . "\n";

echo "--- 9. Testing Kepala Sekolah: guruPrestasiIndex ---\n";
$_SESSION['active_role'] = ['id' => 6, 'name' => 'kepala_sekolah', 'display_name' => 'Kepala Sekolah'];
$plc = new PanitiaLombaController();
ob_start();
$plc->guruPrestasiIndex();
$out9 = ob_get_clean();
echo "guruPrestasiIndex rendered! Length: " . strlen($out9) . "\n";

echo "--- 10. Testing Kepala Sekolah: penugasanIndex ---\n";
ob_start();
$plc->penugasanIndex();
$out10 = ob_get_clean();
echo "penugasanIndex rendered! Length: " . strlen($out10) . "\n";

echo "--- 11. Testing Kepala Sekolah: kegiatanIndex ---\n";
ob_start();
$plc->kegiatanIndex();
$out11 = ob_get_clean();
echo "kegiatanIndex rendered! Length: " . strlen($out11) . "\n";

echo "\n>>> ALL 11 CONTROLLER & VIEW TESTS PASSED SUCCESSFULLY! <<<\n";

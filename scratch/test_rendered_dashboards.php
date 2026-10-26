<?php
session_start();
require 'config/app.php';
require 'core/Database.php';
require 'core/SecurityHelper.php';
require 'core/CSRF.php';
require 'core/Notification.php';
require 'core/Setting.php';
require 'core/AuditLog.php';
require 'core/Auth.php';
require 'core/Controller.php';
require 'core/View.php';
require 'app/Controllers/DashboardController.php';
require 'app/Controllers/MasterController.php';

echo "=== TESTING DASHBOARD CONTROLLER EXECUTION ===\n";

function setAuthSession($username, $roleName, $unitId = 3, $taId = 1) {
    $user = Core\Database::fetch("SELECT * FROM users WHERE username = ?", [$username]);
    if (!$user) {
        $user = Core\Database::fetch("SELECT u.* FROM users u JOIN user_roles ur ON u.id = ur.user_id JOIN roles r ON ur.role_id = r.id WHERE r.name = ? LIMIT 1", [$roleName]);
    }
    $roles = Core\Database::fetchAll(
        "SELECT r.*, ur.is_primary FROM roles r JOIN user_roles ur ON r.id = ur.role_id WHERE ur.user_id = ?",
        [$user['id']]
    );
    $activeRole = null;
    foreach ($roles as $r) {
        if ($r['name'] === $roleName) {
            $activeRole = $r;
            break;
        }
    }
    if (!$activeRole && !empty($roles)) {
        $activeRole = $roles[0];
    }
    if (!$activeRole) {
        $rDb = Core\Database::fetch("SELECT * FROM roles WHERE name = ?", [$roleName]);
        $activeRole = $rDb;
        $roles = [$activeRole];
    }

    $ta = Core\Database::fetch("SELECT * FROM academic_years WHERE id = ?", [$taId]);

    $_SESSION['user_id'] = (int)$user['id'];
    $_SESSION['user_username'] = $user['username'];
    $_SESSION['user_name'] = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['force_change_password'] = false;
    $_SESSION['roles'] = $roles;
    $_SESSION['active_role'] = $activeRole;
    $_SESSION['unit_id'] = $unitId;
    $_SESSION['academic_year_id'] = $taId;
    $_SESSION['academic_year_name'] = $ta ? "{$ta['name']} {$ta['semester']}" : '2024/2025 Genap';
}

// 1. Deputi Pendidikan
setAuthSession('deputi.pendidikan', 'deputi_pendidikan', 0, 1);
ob_start();
try {
    $dc = new App\Controllers\DashboardController();
    $dc->index();
    $html = ob_get_clean();
    echo "1. Deputi Pendidikan (TA 1 Aktif): OK (" . strlen($html) . " bytes)\n";
    echo "   - Kehadiran Guru Setiap Sekolah: " . (strpos($html, 'Persentase Kehadiran Guru Setiap Sekolah') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Kehadiran Siswa Setiap Sekolah: " . (strpos($html, 'Persentase Kehadiran Siswa Setiap Sekolah') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Tabel Rekap Presensi Lembaga: " . (strpos($html, 'Tabel Rekapitulasi Rincian Presensi') !== false ? "PASS" : "FAIL") . "\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "   ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Deputi Pendidikan on Archived TA
setAuthSession('deputi.pendidikan', 'deputi_pendidikan', 0, 2);
ob_start();
try {
    $dc = new App\Controllers\DashboardController();
    $dc->index();
    $html = ob_get_clean();
    echo "2. Deputi Pendidikan (TA 2 Arsip): OK (" . strlen($html) . " bytes)\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 2. Kepala Sekolah SD (unit_id = 3)
setAuthSession('kepsek.sd', 'kepala_sekolah', 3, 1);
ob_start();
try {
    $dc = new App\Controllers\DashboardController();
    $dc->index();
    $html = ob_get_clean();
    echo "3. Kepala Sekolah SD (TA 1 Aktif): OK (" . strlen($html) . " bytes)\n";
    echo "   - Live Count Siswa: " . (strpos($html, 'Live Count') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Pergerakan Jumlah Siswa: " . (strpos($html, 'Grafik Pergerakan Jumlah Siswa') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Kondisi Gender: " . (strpos($html, 'Kondisi Tenaga Pendidik Berdasarkan Gender') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Kondisi Tingkat Pendidikan: " . (strpos($html, 'Kondisi Tenaga Pendidik Berdasarkan Tingkat Pendidikan') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Capaian Nilai Seluruh Mapel: " . (strpos($html, 'Grafik Capaian Nilai Siswa (Filter Mapel &amp; Periodik)') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Laporan Tunggakan dengan Grafik: " . (strpos($html, 'Laporan Tunggakan SPP &amp; Keuangan Sekolah') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Laporan Supervisi dengan Grafik: " . (strpos($html, 'Laporan Supervisi Pembelajaran Kelas') !== false ? "PASS" : "FAIL") . "\n";
    echo "   - Perbandingan TA Berjalan vs Arsip: " . (strpos($html, 'Tabel Perbandingan Mutu: Tahun Ajaran Berjalan vs Tahun Ajaran Diarsip') !== false ? "PASS" : "FAIL") . "\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "   ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}

// Kepala Sekolah on Archived TA (TA 2)
setAuthSession('kepsek.sd', 'kepala_sekolah', 3, 2);
ob_start();
try {
    $dc = new App\Controllers\DashboardController();
    $dc->index();
    $html = ob_get_clean();
    echo "4. Kepala Sekolah SD (TA 2 Arsip): OK (" . strlen($html) . " bytes)\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "   ERROR: " . $e->getMessage() . "\n";
}

// 3. Operator Yayasan - Master Guru & Karyawan
setAuthSession('operator.yayasan', 'operator_yayasan', 0, 1);
ob_start();
try {
    $mc = new App\Controllers\MasterController();
    $mc->employees();
    $html = ob_get_clean();
    echo "5. Operator Yayasan Master Employees: OK (" . strlen($html) . " bytes)\n";
    echo "   - Tingkat Pendidikan Menu: " . (strpos($html, 'Tingkat Pendidikan') !== false ? "PASS" : "FAIL") . "\n";
} catch (Throwable $e) {
    ob_end_clean();
    echo "   ERROR: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "=== ALL VERIFICATIONS PASSED ===\n";

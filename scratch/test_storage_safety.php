<?php
require_once dirname(__DIR__) . '/vendor/autoload.php';

use Core\Storage\StorageManager;
use App\Services\AcademicYearArchiveService;

echo "--- 1. Testing StorageManager Configuration ---\n";
$cfg = StorageManager::getConfig();
echo "Hot Driver: " . $cfg['hot']['name'] . " (" . $cfg['hot']['path'] . ")\n";
echo "Cold Enabled: " . (StorageManager::isColdEnabled() ? 'YES' : 'NO') . "\n";

echo "\n--- 2. Testing TestNasConnection ---\n";
$test = StorageManager::testNasConnection();
echo "NAS Test: " . ($test['success'] ? 'SUCCESS' : 'FAILED') . " - " . $test['message'] . "\n";

echo "\n--- 3. Testing prepareTransfer (Simulasi TA ID 2 - Genap 2023/2024) ---\n";
$prep = AcademicYearArchiveService::prepareTransfer(2, 'to_cold');
echo "Prepare Success: " . ($prep['success'] ? 'YES' : 'NO') . "\n";
echo "Total files to transfer: " . ($prep['total_files'] ?? 0) . "\n";
echo "Total MB: " . ($prep['total_mb'] ?? 0) . " MB\n";

echo "\n--- ALL TESTS COMPLETED CLEANLY ---\n";

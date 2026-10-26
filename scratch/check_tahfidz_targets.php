<?php
require 'core/Database.php';
$db = Core\Database::connect();
$cols = $db->query("DESCRIBE tahfidz_targets")->fetchAll(PDO::FETCH_ASSOC);
echo "TAHFIDZ TARGETS COLUMNS:\n" . json_encode($cols, JSON_PRETTY_PRINT) . "\n";
$rows = $db->query("SELECT * FROM tahfidz_targets")->fetchAll(PDO::FETCH_ASSOC);
echo "TAHFIDZ TARGETS ROWS:\n" . json_encode($rows, JSON_PRETTY_PRINT) . "\n";

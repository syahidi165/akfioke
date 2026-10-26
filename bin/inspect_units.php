<?php
$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

$stmt = $pdo->query("SELECT id, code, name, level, logo_path FROM units");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "ID: {$r['id']} | Code: {$r['code']} | Name: {$r['name']} | Logo: " . ($r['logo_path'] ?? 'NULL') . "\n";
}

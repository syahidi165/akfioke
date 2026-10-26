<?php
$dbConfig = require __DIR__ . '/../config/database.php';
$dsn = "mysql:host={$dbConfig['host']};port={$dbConfig['port']};dbname={$dbConfig['database']};charset={$dbConfig['charset']}";
$pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], $dbConfig['options']);

$stmt = $pdo->query("SELECT u.username, r.name as role_name, e.unit_id, un.name as unit_name
                     FROM users u 
                     JOIN user_roles ur ON u.id = ur.user_id 
                     JOIN roles r ON ur.role_id = r.id 
                     LEFT JOIN employees e ON u.id = e.user_id 
                     LEFT JOIN units un ON e.unit_id = un.id
                     WHERE u.username IN ('kepsek.sma', 'operator.yayasan', 'operator.sd', 'operator.tk')");
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo "User: {$r['username']} | Role: {$r['role_name']} | Unit ID: " . ($r['unit_id'] ?? 'NULL') . " ({$r['unit_name']})\n";
}

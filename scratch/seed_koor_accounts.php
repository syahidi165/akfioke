<?php
require 'core/Database.php';
$db = Core\Database::connect();

$coordinators = [
    [
        'username' => 'koor.tahfidz.kbtk',
        'full_name' => 'Usth. Maryam Al-Hafidzah, S.Pd.I',
        'unit_id' => 2, // TK Islam Al Kahfi Batam (covers KB & TK)
        'position' => 'Koordinator Tahfidz KB/TK',
        'level_posisi' => 'Koordinator Tahfidz'
    ],
    [
        'username' => 'koor.tahfidz.smp',
        'full_name' => 'Ust. Zulkifli Al-Hafidz, Lc',
        'unit_id' => 4, // SMP Islam Al Kahfi Batam
        'position' => 'Koordinator Tahfidz SMP',
        'level_posisi' => 'Koordinator Tahfidz'
    ],
    [
        'username' => 'koor.tahfidz.sma',
        'full_name' => 'Ust. Salman Al-Farisi, S.Q',
        'unit_id' => 5, // SMA Islam Al Kahfi Batam
        'position' => 'Koordinator Tahfidz SMA',
        'level_posisi' => 'Koordinator Tahfidz'
    ]
];

$role = $db->query("SELECT id FROM roles WHERE name = 'koor_tahfidz'")->fetch(PDO::FETCH_ASSOC);
$roleId = $role['id'];
$hash = password_hash('password', PASSWORD_DEFAULT);

foreach ($coordinators as $c) {
    $existing = $db->prepare("SELECT id FROM users WHERE username = ?");
    $existing->execute([$c['username']]);
    $user = $existing->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        $stmt = $db->prepare("INSERT INTO users (username, password_hash, full_name, email, is_active, created_at) VALUES (?, ?, ?, ?, 1, NOW())");
        $email = $c['username'] . '@alkahfi.sch.id';
        $stmt->execute([$c['username'], $hash, $c['full_name'], $email]);
        $userId = $db->lastInsertId();

        $db->prepare("INSERT INTO user_roles (user_id, role_id, is_primary) VALUES (?, ?, 1)")->execute([$userId, $roleId]);

        $db->prepare("INSERT INTO employees (user_id, unit_id, nipy, nama, position, level_posisi, status, is_deleted, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Aktif', 0, NOW())")
           ->execute([$userId, $c['unit_id'], 'KT' . $c['unit_id'] . '001', $c['full_name'], $c['position'], $c['level_posisi']]);

        echo "Created user & employee for {$c['username']} in unit {$c['unit_id']}\n";
    } else {
        // Ensure employee row exists
        $emp = $db->prepare("SELECT id FROM employees WHERE user_id = ?");
        $emp->execute([$user['id']]);
        if (!$emp->fetch()) {
            $db->prepare("INSERT INTO employees (user_id, unit_id, nipy, nama, position, level_posisi, status, is_deleted, created_at) VALUES (?, ?, ?, ?, ?, ?, 'Aktif', 0, NOW())")
               ->execute([$user['id'], $c['unit_id'], 'KT' . $c['unit_id'] . '001', $c['full_name'], $c['position'], $c['level_posisi']]);
            echo "Linked employee for {$c['username']}\n";
        }
        echo "User {$c['username']} already exists\n";
    }
}

<?php
require 'core/Database.php';
$db = Core\Database::connect();
$emps = $db->query("
    SELECT e.id, e.user_id, e.nama, e.unit_id, u.name as unit_name, e.position, e.level_posisi, usr.username
    FROM employees e
    JOIN units u ON e.unit_id = u.id
    LEFT JOIN users usr ON e.user_id = usr.id
    WHERE e.position LIKE '%Tahfidz%' OR e.level_posisi LIKE '%Tahfidz%'
    ORDER BY e.unit_id ASC, e.nama ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($emps, JSON_PRETTY_PRINT);

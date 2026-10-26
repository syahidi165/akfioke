<?php
require 'core/Database.php';
$db = Core\Database::connect();
$classes = $db->query("
    SELECT c.id, c.unit_id, u.name as unit_name, c.name as class_name, c.level
    FROM classes c
    JOIN units u ON c.unit_id = u.id
    ORDER BY c.unit_id ASC, c.level ASC, c.name ASC
")->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($classes, JSON_PRETTY_PRINT);

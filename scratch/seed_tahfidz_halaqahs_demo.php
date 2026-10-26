<?php
// scratch/seed_tahfidz_halaqahs_demo.php
require 'core/Database.php';
$db = Core\Database::connect();

$unitId = 3;
$taId = 1;

// Find all Tahfidz or Tahsin schedules in Unit 3
$tahfidzSchedules = $db->query("
    SELECT cs.*, s.name as subject_name 
    FROM class_schedules cs
    JOIN subjects s ON cs.subject_id = s.id
    WHERE cs.unit_id = {$unitId} AND cs.academic_year_id = {$taId}
      AND (s.name LIKE '%Tahfidz%' OR s.name LIKE '%Tahsin%')
")->fetchAll(PDO::FETCH_ASSOC);

echo "Found " . count($tahfidzSchedules) . " Tahfidz/Tahsin schedules.\n";

// Clear existing halaqahs
$db->exec("DELETE FROM class_schedule_halaqahs");

// Guru Tahfidz in Unit 3:
// Usth. Khadijah (15), Ust. Hanif (9), Ust. Bilal (11), Ust. Muadz (10)
$halaqahTeachers = [15, 9, 11, 10];

foreach ($tahfidzSchedules as $sched) {
    // Seed 2 or 3 halaqahs for each Tahfidz session
    $sId = $sched['id'];
    $cId = $sched['class_id'];
    
    // Halaqah 1
    $t1 = $sched['teacher_id'] ?: 15;
    $db->prepare("INSERT INTO class_schedule_halaqahs (schedule_id, halaqah_number, halaqah_name, teacher_id) VALUES (?, 1, 'Halaqah 1', ?)")
       ->execute([$sId, $t1]);

    // Halaqah 2
    $t2 = ($t1 == 15) ? 9 : 15;
    $db->prepare("INSERT INTO class_schedule_halaqahs (schedule_id, halaqah_number, halaqah_name, teacher_id) VALUES (?, 2, 'Halaqah 2', ?)")
       ->execute([$sId, $t2]);

    // Halaqah 3
    $t3 = ($t1 != 11 && $t2 != 11) ? 11 : 10;
    $db->prepare("INSERT INTO class_schedule_halaqahs (schedule_id, halaqah_number, halaqah_name, teacher_id) VALUES (?, 3, 'Halaqah 3', ?)")
       ->execute([$sId, $t3]);

    echo "Seeded 3 halaqahs for Schedule ID {$sId} (Class ID {$cId}, Subject: {$sched['subject_name']})\n";
}

echo "Done seeding halaqahs!\n";

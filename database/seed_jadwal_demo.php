<?php
// database/seed_jadwal_demo.php
require_once dirname(__DIR__) . '/core/Database.php';

$db = Core\Database::connect();
$unitId = 3; // SD
$taId = 1;   // 2024/2025 Genap

echo "Seeding classes for Unit 3...\n";

// Ensure standard classes exist for levels 1 to 6
$standardClasses = [
    ['name' => 'Kelas 1A Abu Bakar', 'level' => 'Kelas 1', 'walas_id' => 12],
    ['name' => 'Kelas 1B Umar bin Khattab', 'level' => 'Kelas 1', 'walas_id' => 13],
    ['name' => 'Kelas 2A Utsman bin Affan', 'level' => 'Kelas 2', 'walas_id' => 14],
    ['name' => 'Kelas 2B Ali bin Abi Thalib', 'level' => 'Kelas 2', 'walas_id' => 25],
    ['name' => 'Kelas 3A Khalid bin Walid', 'level' => 'Kelas 3', 'walas_id' => 16],
    ['name' => 'Kelas 3B Sa\'ad bin Abi Waqqas', 'level' => 'Kelas 3', 'walas_id' => 17],
    ['name' => 'Kelas 4A Bilal bin Rabah', 'level' => 'Kelas 4', 'walas_id' => 15],
    ['name' => 'Kelas 4B Salman Al Farisi', 'level' => 'Kelas 4', 'walas_id' => 9],
    ['name' => 'Kelas 5A Thariq bin Ziyad', 'level' => 'Kelas 5', 'walas_id' => 10],
    ['name' => 'Kelas 5B Salahuddin Al Ayyubi', 'level' => 'Kelas 5', 'walas_id' => 11],
    ['name' => 'Kelas 6A Zubair bin Awwam', 'level' => 'Kelas 6', 'walas_id' => 7],
    ['name' => 'Kelas 6B Thalhah bin Ubaidillah', 'level' => 'Kelas 6', 'walas_id' => 25],
];

foreach ($standardClasses as $sc) {
    $checkStmt = $db->prepare("SELECT id FROM classes WHERE unit_id = ? AND academic_year_id = ? AND name = ?");
    $checkStmt->execute([$unitId, $taId, $sc['name']]);
    $existing = $checkStmt->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        $stmt = $db->prepare("INSERT INTO classes (unit_id, academic_year_id, name, level, walas_id) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$unitId, $taId, $sc['name'], $sc['level'], $sc['walas_id']]);
    }
}

// Ensure standard subjects exist for Unit 3
$standardSubjects = [
    ['code' => 'MTK', 'name' => 'Matematika', 'type' => 'Umum'],
    ['code' => 'BIN', 'name' => 'Bahasa Indonesia', 'type' => 'Umum'],
    ['code' => 'IPA', 'name' => 'Ilmu Pengetahuan Alam (IPA)', 'type' => 'Umum'],
    ['code' => 'IPS', 'name' => 'Ilmu Pengetahuan Sosial (IPS)', 'type' => 'Umum'],
    ['code' => 'PAI', 'name' => 'Pendidikan Agama Islam', 'type' => 'Diniyah'],
    ['code' => 'BAR', 'name' => 'Bahasa Arab', 'type' => 'Diniyah'],
    ['code' => 'TFZ', 'name' => 'Tahfidzul Qur\'an', 'type' => 'Tahfidz'],
    ['code' => 'THS', 'name' => 'Tahsin & Tilawah', 'type' => 'Tahsin'],
    ['code' => 'BIG', 'name' => 'Bahasa Inggris', 'type' => 'Umum'],
    ['code' => 'PJK', 'name' => 'PJOK / Penjasorkes', 'type' => 'Umum'],
    ['code' => 'PKN', 'name' => 'Pendidikan Pancasila / PKn', 'type' => 'Umum'],
    ['code' => 'SBD', 'name' => 'Seni Budaya & Prakarya (SBdP)', 'type' => 'Umum'],
];

foreach ($standardSubjects as $sb) {
    $existing = $db->query("SELECT id FROM subjects WHERE unit_id = {$unitId} AND name = " . $db->quote($sb['name']))->fetch(PDO::FETCH_ASSOC);
    if (!$existing) {
        $stmt = $db->prepare("INSERT INTO subjects (unit_id, code, name, type) VALUES (?, ?, ?, ?)");
        $stmt->execute([$unitId, $sb['code'], $sb['name'], $sb['type']]);
    }
}

// Fetch all classes and subjects for Unit 3
$classes = $db->query("SELECT id, name, level FROM classes WHERE unit_id = {$unitId} AND academic_year_id = {$taId} ORDER BY level ASC, name ASC")->fetchAll(PDO::FETCH_ASSOC);
$subjects = $db->query("SELECT id, code, name FROM subjects WHERE unit_id = {$unitId}")->fetchAll(PDO::FETCH_ASSOC);
$subjMap = [];
foreach ($subjects as $s) {
    $subjMap[$s['name']] = $s['id'];
}

// Fetch active teachers in Unit 3
$teachers = $db->query("
    SELECT id, nama, level_posisi 
    FROM employees 
    WHERE unit_id = {$unitId} AND status = 'Aktif' AND is_deleted = 0
")->fetchAll(PDO::FETCH_ASSOC);

// Map some teachers to subjects in teacher_subjects if not mapped
foreach ($classes as $cls) {
    foreach ($subjects as $sub) {
        $exists = $db->query("SELECT id FROM teacher_subjects WHERE subject_id = {$sub['id']} AND class_id = {$cls['id']} AND academic_year_id = {$taId}")->fetch();
        if (!$exists) {
            // Assign a teacher based on subject or random teacher
            $teacherId = 12; // Default Aisyah
            if (strpos($sub['name'], 'Agama') !== false || strpos($sub['name'], 'Arab') !== false) {
                $teacherId = 14; // Hamzah
            } elseif (strpos($sub['name'], 'Tahfidz') !== false || strpos($sub['name'], 'Tahsin') !== false) {
                $teacherId = 15; // Khadijah
            } elseif (strpos($sub['name'], 'Matematika') !== false) {
                $teacherId = 13; // Fajar
            } elseif (strpos($sub['name'], 'IPA') !== false) {
                $teacherId = 25; // Syahidi
            } elseif (strpos($sub['name'], 'Penjas') !== false || strpos($sub['name'], 'PJOK') !== false) {
                $teacherId = 17; // Indra
            } elseif (strpos($sub['name'], 'Indonesia') !== false) {
                $teacherId = 7; // Umar Hamzah (Waka Kurikulum)
            }
            $db->prepare("INSERT INTO teacher_subjects (employee_id, subject_id, class_id, academic_year_id) VALUES (?, ?, ?, ?)")
               ->execute([$teacherId, $sub['id'], $cls['id'], $taId]);
        }
    }
}

// Time Slots Configuration per Day (Matching Lampiran colors & slots)
echo "Seeding schedule_time_slots...\n";
$db->exec("DELETE FROM schedule_time_slots WHERE unit_id = {$unitId} AND academic_year_id = {$taId}");

$days = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat'];

// Slots definition
// jam_ke: 0 = pembiasaan/upacara, 1-3 = lesson, 4 = break1, 5-7 = lesson, 8 = break2, 9-10 = lesson
$slotDefinitions = [
    'Senin' => [
        ['jam_ke' => 0, 'time_start' => '07.00', 'time_end' => '07.45', 'label' => 'Upacara Bendera / Ikrar Pagi', 'is_break' => 1, 'break_type' => 'upacara'],
        ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
        ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
        ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
    ],
    'Selasa' => [
        ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Tahsin Pagi & Doa', 'is_break' => 1, 'break_type' => 'upacara'],
        ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
        ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
        ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
    ],
    'Rabu' => [
        ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Literasi & Al-Ma\'tsurat', 'is_break' => 1, 'break_type' => 'upacara'],
        ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
        ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
        ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
    ],
    'Kamis' => [
        ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Kultum Siswa & Asmaul Husna', 'is_break' => 1, 'break_type' => 'upacara'],
        ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Sholat Dhuha', 'is_break' => 1, 'break_type' => 'istirahat'],
        ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 7, 'time_start' => '11.10', 'time_end' => '11.45', 'label' => 'Jam Ke-6', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 8, 'time_start' => '11.45', 'time_end' => '12.45', 'label' => 'Istirahat II, Sholat Dzuhur & Makan', 'is_break' => 1, 'break_type' => 'sholat'],
        ['jam_ke' => 9, 'time_start' => '12.45', 'time_end' => '13.20', 'label' => 'Jam Ke-7', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 10, 'time_start' => '13.20', 'time_end' => '13.55', 'label' => 'Jam Ke-8', 'is_break' => 0, 'break_type' => 'none'],
    ],
    'Jumat' => [
        ['jam_ke' => 0, 'time_start' => '07.15', 'time_end' => '07.45', 'label' => 'Senam Sehat / Dhuha Bersama', 'is_break' => 1, 'break_type' => 'upacara'],
        ['jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'label' => 'Jam Ke-1', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'label' => 'Jam Ke-2', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'label' => 'Jam Ke-3', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 4, 'time_start' => '09.30', 'time_end' => '10.00', 'label' => 'Istirahat I & Snack', 'is_break' => 1, 'break_type' => 'istirahat'],
        ['jam_ke' => 5, 'time_start' => '10.00', 'time_end' => '10.35', 'label' => 'Jam Ke-4', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 6, 'time_start' => '10.35', 'time_end' => '11.10', 'label' => 'Jam Ke-5', 'is_break' => 0, 'break_type' => 'none'],
        ['jam_ke' => 8, 'time_start' => '11.10', 'time_end' => '13.00', 'label' => 'Sholat Jum\'at & Keputrian', 'is_break' => 1, 'break_type' => 'sholat'],
    ],
];

$slotStmt = $db->prepare("
    INSERT INTO schedule_time_slots (unit_id, academic_year_id, day, jam_ke, time_start, time_end, label, is_break, break_type)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
");

foreach ($slotDefinitions as $day => $slots) {
    foreach ($slots as $sl) {
        $slotStmt->execute([
            $unitId, $taId, $day, $sl['jam_ke'], $sl['time_start'], $sl['time_end'], $sl['label'], $sl['is_break'], $sl['break_type']
        ]);
    }
}

// Clear existing demo schedules
$db->exec("DELETE FROM class_schedules WHERE unit_id = {$unitId} AND academic_year_id = {$taId}");

echo "Seeding class schedules for Unit 3...\n";

// Sample teacher mappings:
// Aisyah (12), Fajar (13), Hamzah (14), Khadijah (15), Supriyadi (16), Indra (17), Syahidi (25), Umar Hamzah (7), Hanif (9), Muadz (10)
$schedStmt = $db->prepare("
    INSERT INTO class_schedules (
        unit_id, academic_year_id, class_id, day, jam_ke, time_start, time_end, subject_id, teacher_id, session_type, custom_label, substitute_teacher_id, substitute_date, substitute_reason, substitute_note
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

// Let's create realistic schedule entries
// On Senin:
// Jam 1:
// Kelas 1A: MTK (Aisyah)
// Kelas 1B: BIN (Umar Hamzah)
// Kelas 2A: PAI (Hamzah Robbani)
// Kelas 2B: TFZ (Khadijah)
// Kelas 3A: IPA (Syahidi)
// Kelas 3B: PJK (Indra)
// Kelas 4A: MTK (Fajar)
// Kelas 4B: BAR (Hamzah Robbani) -> BENTROK DEMO! Hamzah is in Kelas 2A and Kelas 4B at Jam 1! Perfect!
// Kelas 5A: BIG (Aisyah) -> BENTROK DEMO! Aisyah is in 1A and 5A at Jam 1! Perfect!
// Kelas 6A: BIN (Umar Hamzah)

$schedData = [
    // Senin Jam 1
    ['class_name' => 'Kelas 1A Abu Bakar', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Matematika', 'teacher_id' => 12],
    ['class_name' => 'Kelas 1B Umar bin Khattab', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Bahasa Indonesia', 'teacher_id' => 7],
    ['class_name' => 'Kelas 2A Utsman bin Affan', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Pendidikan Agama Islam', 'teacher_id' => 14, 'substitute_teacher_id' => 16, 'substitute_date' => date('Y-m-d'), 'substitute_reason' => 'Sakit Flu', 'substitute_note' => 'Digantikan Drs. Supriyadi (Materi Bab 2 Hal 28)'],
    ['class_name' => 'Kelas 2B Ali bin Abi Thalib', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Tahfidzul Qur\'an', 'teacher_id' => 15],
    ['class_name' => 'Kelas 3A Khalid bin Walid', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Ilmu Pengetahuan Alam (IPA)', 'teacher_id' => 25],
    ['class_name' => 'Kelas 3B Sa\'ad bin Abi Waqqas', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'PJOK / Penjasorkes', 'teacher_id' => 17],
    ['class_name' => 'Kelas 4A Bilal bin Rabah', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Matematika', 'teacher_id' => 13],
    ['class_name' => 'Kelas 4B Salman Al Farisi', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Bahasa Arab', 'teacher_id' => 14], // BENTROK with 2A (Hamzah Robbani)
    ['class_name' => 'Kelas 5A Thariq bin Ziyad', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Bahasa Inggris', 'teacher_id' => 12], // BENTROK with 1A (Aisyah)
    ['class_name' => 'Kelas 5B Salahuddin Al Ayyubi', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Tahsin & Tilawah', 'teacher_id' => 9],
    ['class_name' => 'Kelas 6A Zubair bin Awwam', 'day' => 'Senin', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Ilmu Pengetahuan Sosial (IPS)', 'teacher_id' => 10],

    // Senin Jam 2
    ['class_name' => 'Kelas 1A Abu Bakar', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Matematika', 'teacher_id' => 12],
    ['class_name' => 'Kelas 1B Umar bin Khattab', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Bahasa Indonesia', 'teacher_id' => 7],
    ['class_name' => 'Kelas 2A Utsman bin Affan', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Pendidikan Agama Islam', 'teacher_id' => 14, 'substitute_teacher_id' => 16, 'substitute_date' => date('Y-m-d'), 'substitute_reason' => 'Sakit Flu', 'substitute_note' => 'Digantikan Drs. Supriyadi'],
    ['class_name' => 'Kelas 2B Ali bin Abi Thalib', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Tahfidzul Qur\'an', 'teacher_id' => 15],
    ['class_name' => 'Kelas 3A Khalid bin Walid', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Ilmu Pengetahuan Alam (IPA)', 'teacher_id' => 25],
    ['class_name' => 'Kelas 3B Sa\'ad bin Abi Waqqas', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'PJOK / Penjasorkes', 'teacher_id' => 17],
    ['class_name' => 'Kelas 4A Bilal bin Rabah', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Matematika', 'teacher_id' => 13],
    ['class_name' => 'Kelas 4B Salman Al Farisi', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Bahasa Arab', 'teacher_id' => 10],
    ['class_name' => 'Kelas 5A Thariq bin Ziyad', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Tahsin & Tilawah', 'teacher_id' => 9],
    ['class_name' => 'Kelas 5B Salahuddin Al Ayyubi', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Tahsin & Tilawah', 'teacher_id' => 11],
    ['class_name' => 'Kelas 6A Zubair bin Awwam', 'day' => 'Senin', 'jam_ke' => 2, 'time_start' => '08.20', 'time_end' => '08.55', 'subject' => 'Ilmu Pengetahuan Sosial (IPS)', 'teacher_id' => 25],

    // Senin Jam 3
    ['class_name' => 'Kelas 1A Abu Bakar', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'Bahasa Indonesia', 'teacher_id' => 13],
    ['class_name' => 'Kelas 1B Umar bin Khattab', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'Matematika', 'teacher_id' => 12],
    ['class_name' => 'Kelas 2A Utsman bin Affan', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'Tahfidzul Qur\'an', 'teacher_id' => 15],
    ['class_name' => 'Kelas 2B Ali bin Abi Thalib', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'Pendidikan Agama Islam', 'teacher_id' => 14],
    ['class_name' => 'Kelas 3A Khalid bin Walid', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'PJOK / Penjasorkes', 'teacher_id' => 17],
    ['class_name' => 'Kelas 3B Sa\'ad bin Abi Waqqas', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'Ilmu Pengetahuan Alam (IPA)', 'teacher_id' => 25],
    ['class_name' => 'Kelas 4A Bilal bin Rabah', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'Bahasa Inggris', 'teacher_id' => 7],
    ['class_name' => 'Kelas 4B Salman Al Farisi', 'day' => 'Senin', 'jam_ke' => 3, 'time_start' => '08.55', 'time_end' => '09.30', 'subject' => 'Matematika', 'teacher_id' => 13],

    // Selasa Jam 1
    ['class_name' => 'Kelas 1A Abu Bakar', 'day' => 'Selasa', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Tahfidzul Qur\'an', 'teacher_id' => 15],
    ['class_name' => 'Kelas 1B Umar bin Khattab', 'day' => 'Selasa', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Tahfidzul Qur\'an', 'teacher_id' => 9],
    ['class_name' => 'Kelas 2A Utsman bin Affan', 'day' => 'Selasa', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Matematika', 'teacher_id' => 13],
    ['class_name' => 'Kelas 2B Ali bin Abi Thalib', 'day' => 'Selasa', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Bahasa Indonesia', 'teacher_id' => 12],
    ['class_name' => 'Kelas 3A Khalid bin Walid', 'day' => 'Selasa', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Bahasa Arab', 'teacher_id' => 14],
    ['class_name' => 'Kelas 3B Sa\'ad bin Abi Waqqas', 'day' => 'Selasa', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Pendidikan Agama Islam', 'teacher_id' => 10],

    // Rabu Jam 1
    ['class_name' => 'Kelas 1A Abu Bakar', 'day' => 'Rabu', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Ilmu Pengetahuan Alam (IPA)', 'teacher_id' => 25],
    ['class_name' => 'Kelas 1B Umar bin Khattab', 'day' => 'Rabu', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'PJOK / Penjasorkes', 'teacher_id' => 17],
    ['class_name' => 'Kelas 2A Utsman bin Affan', 'day' => 'Rabu', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Bahasa Arab', 'teacher_id' => 14],
    ['class_name' => 'Kelas 2B Ali bin Abi Thalib', 'day' => 'Rabu', 'jam_ke' => 1, 'time_start' => '07.45', 'time_end' => '08.20', 'subject' => 'Matematika', 'teacher_id' => 13],
];

// Insert schedules
$classIdMap = [];
foreach ($classes as $c) {
    $classIdMap[$c['name']] = $c['id'];
}

foreach ($schedData as $sd) {
    if (!isset($classIdMap[$sd['class_name']])) continue;
    $cId = $classIdMap[$sd['class_name']];
    $sId = $subjMap[$sd['subject']] ?? null;

    $schedStmt->execute([
        $unitId,
        $taId,
        $cId,
        $sd['day'],
        $sd['jam_ke'],
        $sd['time_start'],
        $sd['time_end'],
        $sId,
        $sd['teacher_id'],
        'pelajaran',
        null,
        $sd['substitute_teacher_id'] ?? null,
        $sd['substitute_date'] ?? null,
        $sd['substitute_reason'] ?? null,
        $sd['substitute_note'] ?? null,
    ]);
}

echo "Demo schedules seeded successfully!\n";

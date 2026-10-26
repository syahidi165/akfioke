<?php
require 'core/Database.php';
$db = Core\Database::connect();

// 1. Delete duplicate row 11 (keep row 19 which is her latest assignment to Kelas 6A)
$db->query("DELETE FROM class_students WHERE id = 11");
echo "Deleted duplicate row 11\n";

// 2. Drop old index uq_class_student_year if exists
try {
    $db->query("ALTER TABLE class_students DROP INDEX uq_class_student_year");
    echo "Dropped old index uq_class_student_year\n";
} catch (\Throwable $e) {
    echo "Error dropping index: " . $e->getMessage() . "\n";
}

// 3. Create new unique index on (student_id, academic_year_id)
try {
    $db->query("ALTER TABLE class_students ADD UNIQUE KEY uq_student_year (student_id, academic_year_id)");
    echo "Created new unique index uq_student_year (student_id, academic_year_id)\n";
} catch (\Throwable $e) {
    echo "Error creating index: " . $e->getMessage() . "\n";
}

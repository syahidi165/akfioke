<?php
// database/migrate_tahfidz_halaqahs.php
require_once dirname(__DIR__) . '/core/Database.php';

$db = Core\Database::connect();

echo "Creating class_schedule_halaqahs table...\n";
$db->exec("
CREATE TABLE IF NOT EXISTS `class_schedule_halaqahs` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `schedule_id` INT NOT NULL,
    `halaqah_number` INT NOT NULL DEFAULT 1,
    `halaqah_name` VARCHAR(100) NOT NULL DEFAULT 'Halaqah 1',
    `teacher_id` INT NOT NULL,
    `substitute_teacher_id` INT NULL,
    `substitute_date` DATE NULL,
    `substitute_reason` VARCHAR(100) NULL,
    `substitute_note` VARCHAR(255) NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_sched_halaqah_num` (`schedule_id`, `halaqah_number`),
    INDEX `idx_csh_teacher` (`teacher_id`),
    INDEX `idx_csh_substitute` (`substitute_teacher_id`),
    FOREIGN KEY (`schedule_id`) REFERENCES `class_schedules`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`teacher_id`) REFERENCES `employees`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`substitute_teacher_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "class_schedule_halaqahs created successfully!\n";

<?php
// database/migrate_jadwal.php
require_once dirname(__DIR__) . '/core/Database.php';

$db = Core\Database::connect();

echo "Creating class_schedules table...\n";
$db->exec("
CREATE TABLE IF NOT EXISTS `class_schedules` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unit_id` INT NOT NULL,
    `academic_year_id` INT NOT NULL,
    `class_id` INT NOT NULL,
    `day` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL,
    `jam_ke` INT NOT NULL,
    `time_start` VARCHAR(10) NOT NULL DEFAULT '07:15',
    `time_end` VARCHAR(10) NOT NULL DEFAULT '07:55',
    `subject_id` INT NULL,
    `teacher_id` INT NULL,
    `session_type` ENUM('pelajaran', 'upacara', 'istirahat', 'sholat', 'kegiatan') NOT NULL DEFAULT 'pelajaran',
    `custom_label` VARCHAR(100) NULL,
    `substitute_teacher_id` INT NULL,
    `substitute_date` DATE NULL,
    `substitute_reason` VARCHAR(100) NULL,
    `substitute_note` VARCHAR(255) NULL,
    `notes` TEXT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_schedule_cell` (`unit_id`, `academic_year_id`, `class_id`, `day`, `jam_ke`),
    INDEX `idx_sched_unit_ta` (`unit_id`, `academic_year_id`),
    INDEX `idx_sched_teacher` (`teacher_id`, `day`, `jam_ke`),
    INDEX `idx_sched_substitute` (`substitute_teacher_id`, `day`, `jam_ke`),
    FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`class_id`) REFERENCES `classes`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`subject_id`) REFERENCES `subjects`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`teacher_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL,
    FOREIGN KEY (`substitute_teacher_id`) REFERENCES `employees`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Creating schedule_time_slots table...\n";
$db->exec("
CREATE TABLE IF NOT EXISTS `schedule_time_slots` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `unit_id` INT NOT NULL,
    `academic_year_id` INT NOT NULL,
    `day` ENUM('Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu') NOT NULL DEFAULT 'Senin',
    `jam_ke` INT NOT NULL,
    `time_start` VARCHAR(10) NOT NULL,
    `time_end` VARCHAR(10) NOT NULL,
    `label` VARCHAR(100) NULL,
    `is_break` TINYINT(1) DEFAULT 0,
    `break_type` ENUM('none', 'upacara', 'istirahat', 'sholat', 'kegiatan') DEFAULT 'none',
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_slot_unit_day_jam` (`unit_id`, `academic_year_id`, `day`, `jam_ke`),
    FOREIGN KEY (`unit_id`) REFERENCES `units`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");

echo "Tables created successfully.\n";

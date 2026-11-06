<?php
// database/migrate_deputi_rapbs.php
require dirname(__DIR__) . '/vendor/autoload.php';
use Core\Database;

$pdo = Database::connect();

echo "Running migrations for Deputi Pendidikan & RAPBS...\n";

// 1. deputi_units
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    unit_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_deputi_unit (user_id, unit_id),
    INDEX idx_user (user_id),
    INDEX idx_unit (unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 2. deputi_rapor_indicators
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_rapor_indicators (
    id INT AUTO_INCREMENT PRIMARY KEY,
    area_no INT NOT NULL,
    area_name VARCHAR(150) NOT NULL,
    indicator_code VARCHAR(50) NOT NULL,
    indicator_name TEXT NOT NULL,
    target_standard TEXT NOT NULL,
    default_weight DECIMAL(6,4) NOT NULL DEFAULT 0.0400,
    data_source_type ENUM('auto_system', 'app_calendar', 'app_rapbs', 'app_kpi', 'subjective') NOT NULL DEFAULT 'auto_system',
    guidance_note TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_area (area_no)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 3. deputi_rapor_weights
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_rapor_weights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    indicator_id INT NOT NULL,
    weight DECIMAL(6,4) NOT NULL,
    updated_by_user_id INT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_ta_indicator (academic_year_id, indicator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 4. deputi_calendar_events
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_calendar_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    deputi_user_id INT NOT NULL,
    unit_id INT NULL,
    title VARCHAR(255) NOT NULL,
    category ENUM('Kajian Pekanan', 'Sosialisasi Visi Misi', 'Weekly Meeting', 'Monthly Review', 'Management Review', 'Pelatihan SDM', 'Timeplan SPMB/PGB/RAPBS', 'Lainnya') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    location VARCHAR(255) NULL,
    description TEXT NULL,
    invitation_sent_at DATETIME NULL,
    invitation_h_days INT NULL,
    status ENUM('Scheduled', 'Completed', 'Cancelled') NOT NULL DEFAULT 'Scheduled',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ta_deputi (academic_year_id, deputi_user_id),
    INDEX idx_date (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 5. deputi_meeting_moms
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_meeting_moms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    meeting_date DATE NOT NULL,
    mom_content LONGTEXT NULL,
    file_path VARCHAR(255) NULL,
    uploaded_at DATETIME NULL,
    is_on_time_24h TINYINT(1) DEFAULT 1,
    total_commitments INT DEFAULT 0,
    completed_commitments INT DEFAULT 0,
    realization_pct DECIMAL(5,2) DEFAULT 0.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 6. deputi_meeting_actions
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_meeting_actions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mom_id INT NOT NULL,
    source_action_id INT NULL DEFAULT NULL,
    action_topic VARCHAR(255) NOT NULL,
    pic_name VARCHAR(150) NULL,
    due_date DATE NULL,
    progress_pct TINYINT UNSIGNED NOT NULL DEFAULT 0,
    status ENUM('Pending', 'In Progress', 'Completed') NOT NULL DEFAULT 'Pending',
    notes TEXT NULL,
    carried_to_event_id INT NULL,
    INDEX idx_mom (mom_id),
    INDEX idx_source (source_action_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

$chkColAct = $pdo->query("SHOW COLUMNS FROM deputi_meeting_actions LIKE 'progress_pct'")->fetch();
if (!$chkColAct) {
    $pdo->exec("ALTER TABLE deputi_meeting_actions ADD COLUMN progress_pct TINYINT UNSIGNED NOT NULL DEFAULT 0 AFTER due_date");
    $pdo->exec("UPDATE deputi_meeting_actions SET progress_pct = 100 WHERE status = 'Completed'");
}
$chkColSrc = $pdo->query("SHOW COLUMNS FROM deputi_meeting_actions LIKE 'source_action_id'")->fetch();
if (!$chkColSrc) {
    $pdo->exec("ALTER TABLE deputi_meeting_actions ADD COLUMN source_action_id INT NULL DEFAULT NULL AFTER mom_id");
}

// 7. rapbs_headers
$pdo->exec("CREATE TABLE IF NOT EXISTS rapbs_headers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    status ENUM('Draft', 'Diajukan', 'Review Deputi', 'Disetujui Yayasan', 'Revisi') NOT NULL DEFAULT 'Draft',
    submitted_at DATETIME NULL,
    reviewed_by_deputi_id INT NULL,
    reviewed_at DATETIME NULL,
    approved_by_yayasan_id INT NULL,
    approved_at DATETIME NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_unit_ta (unit_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 8. rapbs_items
$pdo->exec("CREATE TABLE IF NOT EXISTS rapbs_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    header_id INT NOT NULL,
    pos_code VARCHAR(20) NOT NULL,
    sub_code VARCHAR(20) NULL,
    keterangan VARCHAR(255) NOT NULL,
    plan_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    real_amount DECIMAL(15,2) NOT NULL DEFAULT 0.00,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_header (header_id),
    INDEX idx_pos (pos_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 9. deputi_kpi_standards
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_kpi_standards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    deputi_user_id INT NOT NULL,
    unit_id INT NOT NULL,
    kpi_category VARCHAR(100) NOT NULL,
    kkm_score DECIMAL(5,2) NOT NULL DEFAULT 80.00,
    target_achievement_pct DECIMAL(5,2) NOT NULL DEFAULT 80.00,
    objective_description VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_ta_unit (academic_year_id, unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 10. add deputi_kpi_category to subjects
$chkCol = $pdo->query("SHOW COLUMNS FROM subjects LIKE 'deputi_kpi_category'")->fetch();
if (!$chkCol) {
    $pdo->exec("ALTER TABLE subjects ADD COLUMN deputi_kpi_category VARCHAR(100) NULL AFTER type");
    echo "Added column deputi_kpi_category to subjects.\n";
}

// 11. employee_badges
$pdo->exec("CREATE TABLE IF NOT EXISTS employee_badges (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    badge_type VARCHAR(50) NOT NULL DEFAULT 'Best Performance',
    title VARCHAR(150) NOT NULL,
    awarded_by_user_id INT NOT NULL,
    awarded_at DATE NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_emp (employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 12. deputi_rapor_evaluations
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_rapor_evaluations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    deputi_user_id INT NOT NULL,
    evaluator_user_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    semester ENUM('Ganjil', 'Genap') NOT NULL DEFAULT 'Genap',
    evaluation_date DATE NOT NULL,
    final_score DECIMAL(6,2) NOT NULL DEFAULT 0.00,
    predicate VARCHAR(50) NOT NULL DEFAULT 'Baik (B)',
    evaluator_notes TEXT NULL,
    status ENUM('Draft', 'Final', 'Disetujui Yayasan') NOT NULL DEFAULT 'Draft',
    approved_by_ketua_yayasan_id INT NULL,
    approved_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_deputi_ta (deputi_user_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 13. deputi_rapor_details
$pdo->exec("CREATE TABLE IF NOT EXISTS deputi_rapor_details (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evaluation_id INT NOT NULL,
    indicator_id INT NOT NULL,
    weight DECIMAL(6,4) NOT NULL,
    scale_1_to_5 INT NOT NULL DEFAULT 4,
    achievement_pct DECIMAL(5,2) NOT NULL DEFAULT 80.00,
    weighted_score DECIMAL(6,4) NOT NULL DEFAULT 0.0320,
    notes TEXT NULL,
    INDEX idx_eval (evaluation_id),
    INDEX idx_ind (indicator_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

// 14. Seed master indicators for deputi_rapor_indicators
require __DIR__ . '/seed_deputi_indicators.php';

echo "Tables checked.\n";


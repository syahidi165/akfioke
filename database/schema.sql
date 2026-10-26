-- Database Schema for AKFIOKE (Yayasan Islam Al Kahfi Batam)
-- Engine: InnoDB, Charset: utf8mb4

CREATE DATABASE IF NOT EXISTS akfioke_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE akfioke_db;

SET FOREIGN_KEY_CHECKS = 0;

-- 1. USERS
DROP TABLE IF EXISTS users;
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    email VARCHAR(150) NULL,
    full_name VARCHAR(150) NOT NULL,
    phone VARCHAR(30) NULL,
    unit_id INT NULL,
    is_active TINYINT(1) DEFAULT 1,
    force_change_password TINYINT(1) DEFAULT 0,
    failed_login_attempts INT DEFAULT 0,
    lockout_until DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_user_username (username),
    INDEX idx_user_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. ROLES
DROP TABLE IF EXISTS roles;
CREATE TABLE roles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    display_name VARCHAR(100) NOT NULL,
    description TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. USER_ROLES
DROP TABLE IF EXISTS user_roles;
CREATE TABLE user_roles (
    user_id INT NOT NULL,
    role_id INT NOT NULL,
    is_primary TINYINT(1) DEFAULT 0,
    PRIMARY KEY (user_id, role_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. UNITS
DROP TABLE IF EXISTS units;
CREATE TABLE units (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    level VARCHAR(20) NOT NULL, -- KB, TK, SD, SMP, SMA
    kop_title VARCHAR(200) NOT NULL DEFAULT 'YAYASAN ISLAM AL KAHFI BATAM',
    kop_subtitle VARCHAR(255) NOT NULL,
    address TEXT NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(100) NULL,
    bank_name VARCHAR(50) NULL,
    bank_account VARCHAR(50) NULL,
    bank_holder VARCHAR(100) NULL,
    letter_template TEXT NULL,
    smtp_host VARCHAR(100) NULL,
    smtp_port INT DEFAULT 587,
    smtp_user VARCHAR(100) NULL,
    smtp_pass VARCHAR(255) NULL,
    smtp_enc VARCHAR(10) DEFAULT 'tls',
    logo_path VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. ACADEMIC YEARS
DROP TABLE IF EXISTS academic_years;
CREATE TABLE academic_years (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(20) NOT NULL,
    semester ENUM('Ganjil', 'Genap') NOT NULL,
    is_active TINYINT(1) DEFAULT 0,
    is_locked TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_ta_sem (name, semester)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. ACADEMIC YEAR APPROVALS
DROP TABLE IF EXISTS academic_year_approvals;
CREATE TABLE academic_year_approvals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    unit_id INT NOT NULL,
    status ENUM('Menunggu', 'Setuju', 'Tidak Setuju') DEFAULT 'Menunggu',
    rejection_reason TEXT NULL,
    decided_by_user_id INT NULL,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. EMPLOYEES
DROP TABLE IF EXISTS employees;
CREATE TABLE employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    nipy VARCHAR(50) NOT NULL UNIQUE,
    nik VARCHAR(30) NULL,
    nama VARCHAR(150) NOT NULL,
    gender ENUM('L', 'P') NOT NULL DEFAULT 'L',
    tingkat_pendidikan ENUM('SMA/SMK', 'D1/D2', 'D3', 'D4/S1', 'S2', 'S3') DEFAULT 'D4/S1',
    birthplace VARCHAR(100) NULL,
    birthdate DATE NULL,
    tmt DATE NOT NULL,
    level_posisi VARCHAR(100) NOT NULL,
    position VARCHAR(100) AS (level_posisi) VIRTUAL,
    unit_id INT NULL,
    atasan_langsung_id INT NULL,
    no_telp_atasan VARCHAR(50) NULL,
    contact_phone VARCHAR(50) NULL,
    email VARCHAR(150) NULL,
    status ENUM('Aktif', 'Nonaktif', 'Resign', 'Mutasi Keluar', 'Pensiun') DEFAULT 'Aktif',
    is_deleted TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (atasan_langsung_id) REFERENCES employees(id) ON DELETE SET NULL,
    INDEX idx_emp_unit (unit_id),
    INDEX idx_emp_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. EMPLOYEE TURNOVER LOGS
DROP TABLE IF EXISTS employee_turnover_logs;
CREATE TABLE employee_turnover_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    action_type ENUM('Mutasi Masuk', 'Mutasi Keluar', 'Resign', 'Diberhentikan', 'Pensiun') NOT NULL,
    from_unit_id INT NULL,
    to_unit_id INT NULL,
    effective_date DATE NOT NULL,
    reason TEXT NOT NULL,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. STUDENTS
DROP TABLE IF EXISTS students;
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nis VARCHAR(30) NOT NULL,
    nisn VARCHAR(30) NULL UNIQUE,
    nik VARCHAR(30) NULL,
    no_kk VARCHAR(30) NOT NULL,
    nama VARCHAR(150) NOT NULL,
    gender ENUM('L', 'P') NOT NULL,
    birthplace VARCHAR(100) NULL,
    birthdate DATE NULL,
    address TEXT NULL,
    father_name VARCHAR(150) NULL,
    mother_name VARCHAR(150) NULL,
    parent_phone VARCHAR(50) NULL,
    parent_email VARCHAR(150) NULL,
    parent_user_id INT NULL,
    student_user_id INT NULL,
    unit_id INT NOT NULL,
    status ENUM('Aktif', 'Pindah', 'Lulus', 'Alumni') DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (parent_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (student_user_id) REFERENCES users(id) ON DELETE SET NULL,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    INDEX idx_std_kk (no_kk),
    INDEX idx_std_nis (nis),
    INDEX idx_std_unit (unit_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 10. CLASSES
DROP TABLE IF EXISTS classes;
CREATE TABLE classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    name VARCHAR(50) NOT NULL,
    level VARCHAR(20) NOT NULL,
    walas_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (walas_id) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11. CLASS_STUDENTS
DROP TABLE IF EXISTS class_students;
CREATE TABLE class_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    status ENUM('Aktif', 'Pindah', 'Lulus', 'Tinggal') DEFAULT 'Aktif',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE,
    UNIQUE KEY uq_class_student_year (class_id, student_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 11b. STUDENT MOVEMENT LOGS (PERGERAKAN SISWA BULANAN & LIVE COUNT)
DROP TABLE IF EXISTS student_movement_logs;
CREATE TABLE student_movement_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    month_num INT NOT NULL,
    month_name VARCHAR(20) NOT NULL,
    masuk INT NOT NULL DEFAULT 0,
    keluar INT NOT NULL DEFAULT 0,
    current_count INT NOT NULL DEFAULT 0,
    notes VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_unit_ta_month (unit_id, academic_year_id, month_num),
    FOREIGN KEY (unit_id) REFERENCES units(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 12. SUBJECTS
DROP TABLE IF EXISTS subjects;
CREATE TABLE subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    code VARCHAR(30) NOT NULL,
    name VARCHAR(150) NOT NULL,
    type ENUM('Diniyah', 'Umum', 'Tahfidz', 'Tahsin', 'Baca Latin', 'National Kurikulum') NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 13. TEACHER_SUBJECTS
DROP TABLE IF EXISTS teacher_subjects;
CREATE TABLE teacher_subjects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    FOREIGN KEY (subject_id) REFERENCES subjects(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 14. MUSHAF SURAHS
DROP TABLE IF EXISTS mushaf_surahs;
CREATE TABLE mushaf_surahs (
    surah_number INT PRIMARY KEY,
    name_ar VARCHAR(100) NOT NULL,
    name_id VARCHAR(100) NOT NULL,
    verses_count INT NOT NULL,
    start_page INT NOT NULL,
    end_page INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 15. MUSHAF MADINAH PAGES
DROP TABLE IF EXISTS mushaf_madinah;
CREATE TABLE mushaf_madinah (
    page_number INT PRIMARY KEY,
    juz_number INT NOT NULL,
    surah_start INT NOT NULL,
    ayah_start INT NOT NULL,
    surah_end INT NOT NULL,
    ayah_end INT NOT NULL,
    lines_count INT DEFAULT 15
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 16. MASTER TEMPLATE AKFI
DROP TABLE IF EXISTS akfi_templates;
CREATE TABLE akfi_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_code VARCHAR(50) NOT NULL,
    position_name VARCHAR(100) NOT NULL,
    academic_year_id INT NOT NULL,
    bobot_kinerja DECIMAL(5,2) NOT NULL DEFAULT 65.00,
    bobot_sikap DECIMAL(5,2) NOT NULL DEFAULT 20.00,
    bobot_absensi DECIMAL(5,2) NOT NULL DEFAULT 15.00,
    grade_a_min DECIMAL(5,2) DEFAULT 91.00,
    grade_b_min DECIMAL(5,2) DEFAULT 81.00,
    grade_c_min DECIMAL(5,2) DEFAULT 61.00,
    grade_d_min DECIMAL(5,2) DEFAULT 31.00,
    grade_e_min DECIMAL(5,2) DEFAULT 11.00,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 17. MASTER TEMPLATE AKFI ITEMS
DROP TABLE IF EXISTS akfi_template_items;
CREATE TABLE akfi_template_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    section ENUM('A', 'B', 'C') NOT NULL,
    category VARCHAR(100) NOT NULL,
    item_code VARCHAR(50) NOT NULL,
    item_name TEXT NOT NULL,
    description TEXT NULL,
    unit_type VARCHAR(30) NOT NULL DEFAULT '%',
    target_val DECIMAL(10,2) NOT NULL DEFAULT 100.00,
    weight DECIMAL(5,2) NOT NULL,
    direction ENUM('higher_better', 'lower_better') DEFAULT 'higher_better',
    auto_source VARCHAR(100) NULL,
    order_num INT DEFAULT 0,
    FOREIGN KEY (template_id) REFERENCES akfi_templates(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 18. MASTER JENIS ADMINISTRASI
DROP TABLE IF EXISTS master_jenis_administrasi;
CREATE TABLE master_jenis_administrasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    target_role VARCHAR(50) NOT NULL,
    code VARCHAR(50) NOT NULL,
    title TEXT NOT NULL,
    frequency ENUM('1x setahun', '2x setahun', '3x setahun', 'setiap bulan') NOT NULL,
    due_info VARCHAR(150) NULL,
    order_num INT DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19. ADMINISTRASI UPLOADS
DROP TABLE IF EXISTS administrasi_uploads;
CREATE TABLE administrasi_uploads (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    user_id INT NOT NULL,
    jenis_admin_id INT NOT NULL,
    sub_title VARCHAR(150) NULL,
    month_num INT NULL,
    file_path VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255) NOT NULL,
    file_size INT NOT NULL,
    deadline_date DATE NULL,
    uploaded_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    is_on_time TINYINT(1) DEFAULT 1,
    status ENUM('Menunggu Review', 'Disetujui', 'Revisi') DEFAULT 'Menunggu Review',
    reviewer_user_id INT NULL,
    review_notes TEXT NULL,
    reviewed_at DATETIME NULL,
    version_num INT DEFAULT 1,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (jenis_admin_id) REFERENCES master_jenis_administrasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 19B. UNIT ADMINISTRASI DEADLINES
DROP TABLE IF EXISTS unit_administrasi_deadlines;
CREATE TABLE unit_administrasi_deadlines (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    jenis_admin_id INT NOT NULL,
    due_info VARCHAR(150) NOT NULL,
    deadline_date DATE NULL,
    updated_by_user_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_unit_ta_jenis (unit_id, academic_year_id, jenis_admin_id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (jenis_admin_id) REFERENCES master_jenis_administrasi(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 20. TAHFIDZ HALAQAH
DROP TABLE IF EXISTS tahfidz_halaqah;
CREATE TABLE tahfidz_halaqah (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    tutor_employee_id INT NOT NULL,
    type ENUM('Siswa', 'Guru') NOT NULL,
    name VARCHAR(100) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (tutor_employee_id) REFERENCES employees(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 21. TAHFIDZ HALAQAH MEMBERS
DROP TABLE IF EXISTS tahfidz_halaqah_members;
CREATE TABLE tahfidz_halaqah_members (
    id INT AUTO_INCREMENT PRIMARY KEY,
    halaqah_id INT NOT NULL,
    student_id INT NULL,
    employee_id INT NULL,
    FOREIGN KEY (halaqah_id) REFERENCES tahfidz_halaqah(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 22. TAHFIDZ TARGETS
DROP TABLE IF EXISTS tahfidz_targets;
CREATE TABLE tahfidz_targets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    academic_year_id INT NOT NULL,
    target_for ENUM('Siswa', 'Guru') NOT NULL,
    unit_id INT NOT NULL,
    grade_or_employee_id VARCHAR(50) NOT NULL,
    target_lines INT NOT NULL DEFAULT 300,
    baseline_info VARCHAR(150) NULL,
    target_info VARCHAR(150) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 23. TAHFIDZ SETORAN SISWA
DROP TABLE IF EXISTS tahfidz_setoran_siswa;
CREATE TABLE tahfidz_setoran_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    halaqah_id INT NOT NULL,
    student_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    month_num INT NOT NULL,
    student_type ENUM('SHML', 'SHSL', 'MHSL', 'MHML') NOT NULL,
    surah_start INT NOT NULL,
    ayah_start INT NOT NULL,
    page_start INT NOT NULL,
    surah_end INT NOT NULL,
    ayah_end INT NOT NULL,
    page_end INT NOT NULL,
    lines_count INT NOT NULL DEFAULT 0,
    is_completed TINYINT(1) DEFAULT 0,
    is_exam_prep TINYINT(1) DEFAULT 0,
    exam_juz INT NULL,
    tutor_notes TEXT NULL,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (halaqah_id) REFERENCES tahfidz_halaqah(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 24. TAHFIDZ SETORAN GURU
DROP TABLE IF EXISTS tahfidz_setoran_guru;
CREATE TABLE tahfidz_setoran_guru (
    id INT AUTO_INCREMENT PRIMARY KEY,
    halaqah_id INT NOT NULL,
    employee_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    month_num INT NOT NULL,
    teacher_type ENUM('SHML', 'SHSL', 'MHSL', 'MHML') NOT NULL,
    surah_start INT NOT NULL,
    ayah_start INT NOT NULL,
    page_start INT NOT NULL,
    surah_end INT NOT NULL,
    ayah_end INT NOT NULL,
    page_end INT NOT NULL,
    lines_count INT NOT NULL DEFAULT 0,
    is_completed TINYINT(1) DEFAULT 0,
    tutor_notes TEXT NULL,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (halaqah_id) REFERENCES tahfidz_halaqah(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 25. TAHFIDZ EXAMS
DROP TABLE IF EXISTS tahfidz_exams;
CREATE TABLE tahfidz_exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    proposed_by_tutor_id INT NOT NULL,
    examiner_id INT NULL,
    juz_number INT NOT NULL,
    exam_date DATE NULL,
    score DECIMAL(5,2) NULL,
    status ENUM('Menunggu', 'Lulus', 'Tidak Lulus') DEFAULT 'Menunggu',
    notes TEXT NULL,
    certificate_number VARCHAR(100) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 26. STUDENT GRADES (NILAI BULANAN)
DROP TABLE IF EXISTS student_grades;
CREATE TABLE student_grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    subject_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    month_num INT NOT NULL,
    score DECIMAL(5,2) NOT NULL,
    kkm DECIMAL(5,2) NOT NULL DEFAULT 75.00,
    is_locked TINYINT(1) DEFAULT 0,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY uq_std_grade (student_id, class_id, subject_id, academic_year_id, month_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 27. STUDENT ATTENDANCE (ABSENSI BULANAN)
DROP TABLE IF EXISTS student_attendance;
CREATE TABLE student_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    month_num INT NOT NULL,
    hadir INT NOT NULL DEFAULT 20,
    effective_days INT NOT NULL DEFAULT 20,
    sakit INT NOT NULL DEFAULT 0,
    izin INT NOT NULL DEFAULT 0,
    alpha INT NOT NULL DEFAULT 0,
    alfa INT NOT NULL DEFAULT 0,
    attendance_pct DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    is_locked TINYINT(1) DEFAULT 0,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (class_id) REFERENCES classes(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY uq_std_att (student_id, class_id, academic_year_id, month_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 28. EMPLOYEE ATTENDANCE (ABSENSI PEGAWAI)
DROP TABLE IF EXISTS employee_attendance;
CREATE TABLE employee_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    month_num INT NOT NULL,
    hadir INT NOT NULL DEFAULT 20,
    terlambat INT NOT NULL DEFAULT 0,
    pulang_cepat INT NOT NULL DEFAULT 0,
    lupa_catat INT NOT NULL DEFAULT 0,
    mangkir INT NOT NULL DEFAULT 0,
    izin INT NOT NULL DEFAULT 0,
    alpha INT NOT NULL DEFAULT 0,
    terlambat_menit INT NOT NULL DEFAULT 0,
    sakit INT NOT NULL DEFAULT 0,
    cuti INT NOT NULL DEFAULT 0,
    dinas_luar INT NOT NULL DEFAULT 0,
    kajian_hadir INT NOT NULL DEFAULT 4,
    kajian_total INT NOT NULL DEFAULT 4,
    kajian_pekanan_hadir INT NOT NULL DEFAULT 4,
    kajian_pekanan_total INT NOT NULL DEFAULT 4,
    recorded_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY uq_emp_att (employee_id, unit_id, academic_year_id, month_num)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 29. SUPERVISI SESSIONS
DROP TABLE IF EXISTS supervisi_sessions;
CREATE TABLE supervisi_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    teacher_id INT NOT NULL,
    subject_id INT NOT NULL,
    class_id INT NOT NULL,
    session_date DATE NOT NULL,
    score_a DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    score_b DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    score_c DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    total_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    max_score DECIMAL(5,2) NOT NULL DEFAULT 100.00,
    final_score DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    achievement_predicate ENUM('SANGAT BAIK', 'BAIK', 'CUKUP', 'PERLU PENINGKATAN') NOT NULL,
    notes_good TEXT NULL,
    notes_improve TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (supervisor_id) REFERENCES employees(id),
    FOREIGN KEY (teacher_id) REFERENCES employees(id),
    FOREIGN KEY (subject_id) REFERENCES subjects(id),
    FOREIGN KEY (class_id) REFERENCES classes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 30. SUPERVISI SCORES PER ITEM
DROP TABLE IF EXISTS supervisi_rubric_scores;
CREATE TABLE supervisi_rubric_scores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supervisi_id INT NOT NULL,
    section ENUM('A', 'B', 'C') NOT NULL,
    item_number INT NOT NULL,
    score_val INT NOT NULL,
    weight_val INT NOT NULL,
    final_item_score DECIMAL(5,2) NOT NULL,
    FOREIGN KEY (supervisi_id) REFERENCES supervisi_sessions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 31. BILLS (TAGIHAN SPP & KEUANGAN)
DROP TABLE IF EXISTS bills;
CREATE TABLE bills (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    student_id INT NOT NULL,
    bill_month INT NOT NULL,
    bill_year INT NOT NULL,
    title VARCHAR(150) NOT NULL DEFAULT 'SPP Bulanan',
    amount DECIMAL(12,2) NOT NULL,
    items_detail LONGTEXT NULL,
    amount_spp DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    amount_non_spp DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    due_date DATE NOT NULL,
    status ENUM('Lunas', 'Tertunggak') NOT NULL DEFAULT 'Tertunggak',
    pipeline_stage VARCHAR(50) NOT NULL DEFAULT 'tu_draft',
    tu_reminder_sent_at DATETIME NULL,
    tu_reminder_letter_no VARCHAR(100) NULL,
    kepsek_schedule_date DATETIME NULL,
    kepsek_schedule_place VARCHAR(255) NULL,
    kepsek_letter_no VARCHAR(100) NULL,
    kepsek_summon_sent_at DATETIME NULL,
    deputi_schedule_date DATETIME NULL,
    deputi_schedule_place VARCHAR(255) NULL,
    deputi_letter_no VARCHAR(100) NULL,
    deputi_summon_sent_at DATETIME NULL,
    pipeline_resolved_at DATETIME NULL,
    pipeline_resolved_level VARCHAR(50) NULL,
    paid_date DATE NULL,
    paid_amount DECIMAL(12,2) DEFAULT 0.00,
    payment_method VARCHAR(50) NULL,
    notes TEXT NULL,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 32. BILL REMINDERS & MAILS
DROP TABLE IF EXISTS bill_reminders;
CREATE TABLE bill_reminders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    bill_id INT NOT NULL,
    student_id INT NOT NULL,
    status ENUM('Diajukan', 'Disetujui', 'Terkirim', 'Ditolak') DEFAULT 'Diajukan',
    proposed_by_user_id INT NOT NULL,
    approved_by_user_id INT NULL,
    letter_number VARCHAR(100) NULL,
    email_sent_to VARCHAR(150) NULL,
    sent_at DATETIME NULL,
    error_message TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 32b. BILL INSTALLMENTS (FR 8001D & BUKU CICILAN TU)
DROP TABLE IF EXISTS bill_installment_payments;
DROP TABLE IF EXISTS bill_installments;
CREATE TABLE bill_installments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    agreement_number VARCHAR(100) NOT NULL UNIQUE,
    unit_id INT NOT NULL,
    bill_id INT NULL,
    student_id INT NOT NULL,
    parent_name VARCHAR(150) NOT NULL,
    parent_ktp VARCHAR(50) NULL,
    parent_address TEXT NULL,
    parent_phone VARCHAR(50) NULL,
    student_class VARCHAR(100) NULL,
    scheme_type ENUM('lunas_tanggal', 'cicil_bulan', 'cicil_nominal') NOT NULL DEFAULT 'cicil_bulan',
    scheme_lunas_date DATE NULL,
    scheme_months INT NULL,
    scheme_start_date DATE NULL,
    scheme_min_monthly DECIMAL(12,2) NULL,
    scheme_end_date DATE NULL,
    total_debt DECIMAL(12,2) NOT NULL,
    paid_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    remaining_debt DECIMAL(12,2) NOT NULL,
    status ENUM('Aktif', 'Lunas', 'Batal') NOT NULL DEFAULT 'Aktif',
    approved_by_level ENUM('kepala_sekolah', 'deputi_pendidikan', 'tata_usaha') NOT NULL,
    approved_by_user_id INT NOT NULL,
    approved_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    agreement_date DATE NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (student_id) REFERENCES students(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE bill_installment_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    installment_id INT NOT NULL,
    receipt_number VARCHAR(100) NOT NULL,
    payment_date DATE NOT NULL,
    amount DECIMAL(12,2) NOT NULL,
    payment_method VARCHAR(50) NOT NULL DEFAULT 'Cash',
    notes TEXT NULL,
    received_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (installment_id) REFERENCES bill_installments(id) ON DELETE CASCADE,
    FOREIGN KEY (received_by_user_id) REFERENCES users(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 33. SURVEY TEMPLATES
DROP TABLE IF EXISTS survey_templates;
CREATE TABLE survey_templates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(50) NOT NULL UNIQUE,
    title VARCHAR(150) NOT NULL,
    target_respondent ENUM('Orang Tua', 'Guru', 'Siswa') NOT NULL,
    questions_json LONGTEXT NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 34. SURVEY RESPONSES
DROP TABLE IF EXISTS survey_responses;
CREATE TABLE survey_responses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    template_id INT NOT NULL,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    respondent_user_id INT NULL,
    answers_json LONGTEXT NOT NULL,
    total_score INT NOT NULL,
    max_score INT NOT NULL,
    score_pct DECIMAL(5,2) NOT NULL,
    suggestions TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (template_id) REFERENCES survey_templates(id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 35. CHECKLISTS CS
DROP TABLE IF EXISTS checklists_cs;
CREATE TABLE checklists_cs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    employee_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    check_type ENUM('Rutin Harian', 'Piket Bulanan', 'Gotong Royong') NOT NULL,
    check_date DATE NOT NULL,
    month_num INT NOT NULL,
    is_verified TINYINT(1) DEFAULT 0,
    verified_by_user_id INT NULL,
    photo_proof VARCHAR(255) NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 36. CHECKLISTS SECURITY
DROP TABLE IF EXISTS checklists_security;
CREATE TABLE checklists_security (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    employee_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    check_date DATE NOT NULL,
    is_complete TINYINT(1) DEFAULT 1,
    is_on_time TINYINT(1) DEFAULT 1,
    check_items_json TEXT NOT NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY uq_sec_date (employee_id, check_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 37. SECURITY PATROLS
DROP TABLE IF EXISTS security_patrols;
CREATE TABLE security_patrols (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    employee_id INT NOT NULL,
    patrol_date DATE NOT NULL,
    schedule_time TIME NOT NULL,
    actual_time TIME NOT NULL,
    location VARCHAR(150) NOT NULL,
    findings TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 38. SECURITY INCIDENTS
DROP TABLE IF EXISTS security_incidents;
CREATE TABLE security_incidents (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    incident_date DATE NOT NULL,
    incident_time TIME NOT NULL,
    category VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    action_taken TEXT NOT NULL,
    is_prevented TINYINT(1) DEFAULT 0,
    is_breach TINYINT(1) DEFAULT 0,
    rating_response INT DEFAULT 5,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 39. KEGIATAN LUAR KBM
DROP TABLE IF EXISTS kegiatan_luar_kbm;
CREATE TABLE kegiatan_luar_kbm (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    event_date DATE NOT NULL,
    location VARCHAR(150) NULL,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 40. KEGIATAN LUAR KBM PARTICIPANTS
DROP TABLE IF EXISTS kegiatan_luar_kbm_participants;
CREATE TABLE kegiatan_luar_kbm_participants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kegiatan_id INT NOT NULL,
    employee_id INT NOT NULL,
    status ENUM('Hadir', 'Hadir Panitia', 'Tidak Hadir') NOT NULL DEFAULT 'Hadir',
    FOREIGN KEY (kegiatan_id) REFERENCES kegiatan_luar_kbm(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY uq_kegiatan_emp (kegiatan_id, employee_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 41. GURU BERPRESTASI
DROP TABLE IF EXISTS guru_berprestasi;
CREATE TABLE guru_berprestasi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    employee_id INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    level VARCHAR(50) NOT NULL,
    year VARCHAR(10) NOT NULL,
    proof_document VARCHAR(255) NULL,
    notes TEXT NULL,
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (employee_id) REFERENCES employees(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 42. PANITIA LOMBA ASSIGNMENTS
DROP TABLE IF EXISTS panitia_lomba_assignments;
CREATE TABLE panitia_lomba_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    teacher_id INT NOT NULL,
    event_type ENUM('OSN', 'O2SN', 'FLS2N') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (teacher_id) REFERENCES employees(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 43. PRESTASI SISWA
DROP TABLE IF EXISTS prestasi_siswa;
CREATE TABLE prestasi_siswa (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    student_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    category ENUM('OSN', 'O2SN', 'FLS2N', 'Swasta') NOT NULL,
    event_name VARCHAR(150) NOT NULL,
    field_name VARCHAR(100) NULL,
    rank_position VARCHAR(50) NOT NULL,
    level VARCHAR(50) NOT NULL,
    certificate_path VARCHAR(255) NULL,
    recorded_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 44. SPMB TARGETS
DROP TABLE IF EXISTS spmb_targets;
CREATE TABLE spmb_targets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    target_count INT NOT NULL DEFAULT 100,
    set_by_deputi_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    UNIQUE KEY uq_spmb_target (unit_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 45. SPMB REGISTRATIONS
DROP TABLE IF EXISTS spmb_registrations;
CREATE TABLE spmb_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    reg_number VARCHAR(50) NOT NULL UNIQUE,
    student_name VARCHAR(150) NOT NULL,
    gender ENUM('L', 'P') NOT NULL,
    parent_name VARCHAR(150) NOT NULL,
    phone VARCHAR(50) NOT NULL,
    email VARCHAR(150) NULL,
    previous_school VARCHAR(150) NULL,
    status ENUM('Terdaftar', 'Diterima', 'Ditolak') DEFAULT 'Terdaftar',
    converted_student_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (converted_student_id) REFERENCES students(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 46. ALUMNI TRACKING
DROP TABLE IF EXISTS alumni_tracking;
CREATE TABLE alumni_tracking (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    unit_id INT NOT NULL,
    graduation_year VARCHAR(10) NOT NULL,
    destination_type ENUM('Sekolah', 'Universitas', 'Kerja/Lainnya') NOT NULL,
    destination_name VARCHAR(150) NOT NULL,
    is_favorite TINYINT(1) DEFAULT 0,
    major VARCHAR(100) NULL,
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id),
    FOREIGN KEY (unit_id) REFERENCES units(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 47. AKFI ASSESSMENTS
DROP TABLE IF EXISTS akfi_assessments;
CREATE TABLE akfi_assessments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    unit_id INT NOT NULL,
    academic_year_id INT NOT NULL,
    template_id INT NOT NULL,
    status ENUM('Draft', 'Diajukan', 'Disetujui_Atasan', 'Final') DEFAULT 'Draft',
    kinerja_score DECIMAL(5,2) DEFAULT 0.00,
    sikap_score DECIMAL(5,2) DEFAULT 0.00,
    absensi_score DECIMAL(5,2) DEFAULT 0.00,
    total_score DECIMAL(5,2) DEFAULT 0.00,
    letter_grade VARCHAR(5) DEFAULT 'E',
    self_submitted_at DATETIME NULL,
    superior_signed_by INT NULL,
    superior_signed_at DATETIME NULL,
    next_superior_signed_by INT NULL,
    next_superior_signed_at DATETIME NULL,
    hr_signed_by INT NULL,
    hr_signed_at DATETIME NULL,
    items_json LONGTEXT NULL,
    superior_notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (employee_id) REFERENCES employees(id),
    FOREIGN KEY (unit_id) REFERENCES units(id),
    FOREIGN KEY (academic_year_id) REFERENCES academic_years(id),
    FOREIGN KEY (template_id) REFERENCES akfi_templates(id),
    UNIQUE KEY uq_emp_akfi_year (employee_id, academic_year_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 48. NOTIFICATIONS
DROP TABLE IF EXISTS notifications;
CREATE TABLE notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type VARCHAR(50) DEFAULT 'info',
    url VARCHAR(255) NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_notif_user (user_id, is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 49. AUDIT LOGS (APPEND-ONLY)
DROP TABLE IF EXISTS audit_logs;
CREATE TABLE audit_logs (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    username VARCHAR(100) NULL,
    role_name VARCHAR(100) NULL,
    ip_address VARCHAR(45) NOT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    record_id VARCHAR(50) NULL,
    old_values LONGTEXT NULL,
    new_values LONGTEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_time (created_at),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_table (table_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 50. DATABASE BACKUPS
DROP TABLE IF EXISTS database_backups;
CREATE TABLE database_backups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_size BIGINT NOT NULL,
    backup_type VARCHAR(50) DEFAULT 'Manual',
    created_by_user_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;

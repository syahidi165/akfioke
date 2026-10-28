-- database/migration_20261028_features.sql
-- Penambahan kolom akreditasi, izin operasional, timestamp deadline administrasi, dan verifikasi prestasi guru

-- 1. Unit Sekolah: Izin Operasional & Akreditasi
ALTER TABLE `units` 
    ADD COLUMN IF NOT EXISTS `npsn` VARCHAR(50) NULL AFTER `level`,
    ADD COLUMN IF NOT EXISTS `akreditasi` VARCHAR(20) NULL DEFAULT 'A' AFTER `npsn`,
    ADD COLUMN IF NOT EXISTS `nomor_akreditasi` VARCHAR(100) NULL AFTER `akreditasi`,
    ADD COLUMN IF NOT EXISTS `akreditasi_expired_at` DATE NULL AFTER `nomor_akreditasi`,
    ADD COLUMN IF NOT EXISTS `nomor_izin_operasional` VARCHAR(100) NULL AFTER `akreditasi_expired_at`,
    ADD COLUMN IF NOT EXISTS `izin_operasional_expired_at` DATE NULL AFTER `nomor_izin_operasional`;

-- 2. Deadline Administrasi: Timestamp Detail (Date + Time)
ALTER TABLE `unit_administrasi_deadlines`
    ADD COLUMN IF NOT EXISTS `deadline_datetime` DATETIME NULL AFTER `deadline_date`;

-- 3. Guru Berprestasi: Status Menunggu Verifikasi & Approval KS
ALTER TABLE `guru_berprestasi`
    MODIFY COLUMN `status` ENUM('Menunggu Verifikasi','Terverifikasi','Ditolak','Dalam Proses','Selesai','Dibatalkan') NOT NULL DEFAULT 'Menunggu Verifikasi',
    ADD COLUMN IF NOT EXISTS `verified_by_user_id` INT(11) NULL AFTER `created_by_user_id`,
    ADD COLUMN IF NOT EXISTS `verified_at` DATETIME NULL AFTER `verified_by_user_id`,
    ADD COLUMN IF NOT EXISTS `verification_notes` TEXT NULL AFTER `verified_at`;

-- 4. Tabel Pengaturan SMTP (Fail-safe jika query smtp_settings terpanggil)
CREATE TABLE IF NOT EXISTS `smtp_settings` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `unit_id` INT(11) NULL,
    `host` VARCHAR(150) NOT NULL,
    `port` INT(11) NOT NULL DEFAULT 587,
    `username` VARCHAR(150) NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    `encryption` VARCHAR(20) NOT NULL DEFAULT 'tls',
    `from_email` VARCHAR(150) NOT NULL,
    `from_name` VARCHAR(150) NOT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_unit_id` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

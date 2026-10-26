-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: akfioke_db
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `academic_year_approvals`
--

DROP TABLE IF EXISTS `academic_year_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_year_approvals` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `status` enum('Menunggu','Setuju','Tidak Setuju') DEFAULT 'Menunggu',
  `rejection_reason` text DEFAULT NULL,
  `decided_by_user_id` int(11) DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `academic_year_approvals_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `academic_year_approvals_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_year_approvals`
--

LOCK TABLES `academic_year_approvals` WRITE;
/*!40000 ALTER TABLE `academic_year_approvals` DISABLE KEYS */;
/*!40000 ALTER TABLE `academic_year_approvals` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `academic_years`
--

DROP TABLE IF EXISTS `academic_years`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(20) NOT NULL,
  `semester` enum('Ganjil','Genap') NOT NULL,
  `target_workdays` int(11) NOT NULL DEFAULT 264,
  `target_kajian` int(11) NOT NULL DEFAULT 48,
  `is_active` tinyint(1) DEFAULT 0,
  `is_locked` tinyint(1) DEFAULT 0,
  `storage_tier` varchar(20) NOT NULL DEFAULT 'hot',
  `archived_at` datetime DEFAULT NULL,
  `archive_file_count` int(11) NOT NULL DEFAULT 0,
  `archive_total_size` bigint(20) NOT NULL DEFAULT 0,
  `archive_meta` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ta_sem` (`name`,`semester`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,'2027/2028','Ganjil',264,48,1,0,'hot',NULL,0,0,NULL,'2026-10-26 14:42:48','2026-10-26 14:42:48');
/*!40000 ALTER TABLE `academic_years` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `administrasi_uploads`
--

DROP TABLE IF EXISTS `administrasi_uploads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `administrasi_uploads` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_admin_id` int(11) NOT NULL,
  `sub_title` varchar(150) DEFAULT NULL,
  `month_num` int(11) DEFAULT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `deadline_date` date DEFAULT NULL,
  `uploaded_at` datetime DEFAULT current_timestamp(),
  `is_on_time` tinyint(1) DEFAULT 1,
  `status` enum('Menunggu Review','Disetujui','Revisi') DEFAULT 'Menunggu Review',
  `reviewer_user_id` int(11) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `version_num` int(11) DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `user_id` (`user_id`),
  KEY `jenis_admin_id` (`jenis_admin_id`),
  CONSTRAINT `administrasi_uploads_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `administrasi_uploads_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `administrasi_uploads_ibfk_3` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  CONSTRAINT `administrasi_uploads_ibfk_4` FOREIGN KEY (`jenis_admin_id`) REFERENCES `master_jenis_administrasi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrasi_uploads`
--

LOCK TABLES `administrasi_uploads` WRITE;
/*!40000 ALTER TABLE `administrasi_uploads` DISABLE KEYS */;
/*!40000 ALTER TABLE `administrasi_uploads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akfi_assessments`
--

DROP TABLE IF EXISTS `akfi_assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akfi_assessments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `template_id` int(11) NOT NULL,
  `status` enum('Draft','Diajukan','Disetujui_Atasan','Final') DEFAULT 'Draft',
  `kinerja_score` decimal(5,2) DEFAULT 0.00,
  `sikap_score` decimal(5,2) DEFAULT 0.00,
  `absensi_score` decimal(5,2) DEFAULT 0.00,
  `total_score` decimal(5,2) DEFAULT 0.00,
  `letter_grade` varchar(5) DEFAULT 'E',
  `self_submitted_at` datetime DEFAULT NULL,
  `superior_signed_by` int(11) DEFAULT NULL,
  `superior_signed_at` datetime DEFAULT NULL,
  `next_superior_signed_by` int(11) DEFAULT NULL,
  `next_superior_signed_at` datetime DEFAULT NULL,
  `hr_signed_by` int(11) DEFAULT NULL,
  `hr_signed_at` datetime DEFAULT NULL,
  `items_json` longtext DEFAULT NULL,
  `superior_notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_akfi_year` (`employee_id`,`academic_year_id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `akfi_assessments_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `akfi_assessments_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `akfi_assessments_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `akfi_assessments_ibfk_4` FOREIGN KEY (`template_id`) REFERENCES `akfi_templates` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akfi_assessments`
--

LOCK TABLES `akfi_assessments` WRITE;
/*!40000 ALTER TABLE `akfi_assessments` DISABLE KEYS */;
/*!40000 ALTER TABLE `akfi_assessments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akfi_template_items`
--

DROP TABLE IF EXISTS `akfi_template_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akfi_template_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `section` enum('A','B','C') NOT NULL,
  `category` varchar(100) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `item_name` text NOT NULL,
  `description` text DEFAULT NULL,
  `unit_type` varchar(30) NOT NULL DEFAULT '%',
  `target_val` decimal(10,2) NOT NULL DEFAULT 100.00,
  `weight` decimal(5,2) NOT NULL,
  `direction` enum('higher_better','lower_better') DEFAULT 'higher_better',
  `auto_source` varchar(100) DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  CONSTRAINT `akfi_template_items_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `akfi_templates` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=228 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akfi_template_items`
--

LOCK TABLES `akfi_template_items` WRITE;
/*!40000 ALTER TABLE `akfi_template_items` DISABLE KEYS */;
INSERT INTO `akfi_template_items` VALUES (1,1,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(2,1,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(3,1,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa di kelas yang diajarkan',NULL,'%',90.00,9.00,'higher_better','student_attendance',3),(4,1,'A','Pembelajaran','K-PEM-02','Terkumpulnya Best Practice',NULL,'jumlah',1.00,10.00,'higher_better','upload_best_practice',4),(5,1,'A','Pembelajaran','K-PEM-03','Capaian nilai rata-rata mapel yang diajarkan',NULL,'angka',75.00,10.00,'higher_better','subject_average',5),(6,1,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',88.00,10.00,'higher_better','supervisi_avg',6),(7,1,'A','Performance','K-PRF-02','% target hafalan tahunan guru (1 Juz)',NULL,'%',100.00,6.00,'higher_better','teacher_tahfidz_target',7),(8,1,'A','Performance','K-PRF-03','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,10.00,'higher_better','kegiatan_kbm_attendance',8),(9,1,'B','Sikap','S-01','Communication (Lisan & tulisan jelas dan mudah dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,9),(10,1,'B','Sikap','S-02','Profesionalisme (Refleksi diri & pemanfaatan TIK)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,10),(11,1,'B','Sikap','S-03','Teamwork (Kerjasama rekan kerja)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(12,1,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(13,1,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(14,1,'B','Sikap','S-06','Keteladanan & Disiplin Islami',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(15,1,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',15),(16,1,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',16),(17,1,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',17),(18,1,'C','Absensi','AB-04','Tidak Hadir / Mangkir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',18),(19,2,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(20,2,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(21,2,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa di kelas yang diajarkan',NULL,'%',90.00,9.00,'higher_better','student_attendance',3),(22,2,'A','Pembelajaran','K-PEM-02','Terkumpulnya Best Practice',NULL,'jumlah',1.00,10.00,'higher_better','upload_best_practice',4),(23,2,'A','Pembelajaran','K-PEM-03','% siswa lulus target tahfidz',NULL,'%',80.00,10.00,'higher_better','tahfidz_pass_pct',5),(24,2,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',88.00,10.00,'higher_better','supervisi_avg',6),(25,2,'A','Performance','K-PRF-02','% target hafalan tahunan guru (1 Juz)',NULL,'%',100.00,6.00,'higher_better','teacher_tahfidz_target',7),(26,2,'A','Performance','K-PRF-03','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,10.00,'higher_better','kegiatan_kbm_attendance',8),(27,2,'B','Sikap','S-01','Communication (Lisan & tulisan jelas dan mudah dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,9),(28,2,'B','Sikap','S-02','Profesionalisme (Refleksi diri & pemanfaatan TIK)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,10),(29,2,'B','Sikap','S-03','Teamwork (Kerjasama rekan kerja)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(30,2,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(31,2,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(32,2,'B','Sikap','S-06','Keteladanan & Disiplin Islami',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(33,2,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',15),(34,2,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',16),(35,2,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',17),(36,2,'C','Absensi','AB-04','Tidak Hadir / Mangkir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',18),(37,3,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(38,3,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(39,3,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa (target 100%)',NULL,'%',100.00,9.00,'higher_better','student_attendance',3),(40,3,'A','Pembelajaran','K-PEM-02','Terkumpulnya PTK (Penelitian Tindakan Kelas)',NULL,'jumlah',1.00,10.00,'higher_better','upload_ptk',4),(41,3,'A','Pembelajaran','K-PEM-03','Capaian nilai rata-rata mapel',NULL,'angka',95.00,10.00,'higher_better','subject_average',5),(42,3,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',90.00,8.00,'higher_better','supervisi_avg',6),(43,3,'A','Performance','K-PRF-02','Jumlah siswa yang menjuarai lomba (juara 1)',NULL,'Orang',3.00,4.00,'higher_better','juara_lomba_walas',7),(44,3,'A','Performance','K-PRF-03','% siswa lulus target tahfidz',NULL,'%',100.00,1.00,'higher_better','tahfidz_pass_pct',8),(45,3,'A','Performance','K-PRF-04','% target hafalan tahunan guru (1 Juz)',NULL,'%',100.00,5.00,'higher_better','teacher_tahfidz_target',9),(46,3,'A','Performance','K-PRF-05','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,8.00,'higher_better','kegiatan_kbm_attendance',10),(47,3,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,11),(48,3,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,12),(49,3,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(50,3,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(51,3,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,15),(52,3,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,16),(53,3,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',17),(54,3,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',18),(55,3,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',19),(56,3,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',20),(57,4,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(58,4,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(59,4,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa (target 90%)',NULL,'%',90.00,9.00,'higher_better','student_attendance',3),(60,4,'A','Pembelajaran','K-PEM-02','Capaian nilai rata-rata pembelajaran umum',NULL,'angka',90.00,10.00,'higher_better','subject_average_umum',4),(61,4,'A','Pembelajaran','K-PEM-03','Capaian nilai rata-rata pembelajaran diniyah',NULL,'angka',90.00,10.00,'higher_better','subject_average_diniyah',5),(62,4,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',85.00,10.00,'higher_better','supervisi_avg',6),(63,4,'A','Performance','K-PRF-02','% target hafalan tahunan guru (1/2 Juz)',NULL,'%',100.00,6.00,'higher_better','teacher_tahfidz_target',7),(64,4,'A','Performance','K-PRF-03','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,10.00,'higher_better','kegiatan_kbm_attendance',8),(65,4,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,9),(66,4,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,10),(67,4,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(68,4,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(69,4,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(70,4,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(71,4,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',15),(72,4,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',16),(73,4,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',17),(74,4,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',18),(75,5,'A','Administrasi','K-TU-01','Terkumpulnya dokumen keuangan bulanan berikut lampiran pemakaian uang',NULL,'%',100.00,15.00,'higher_better','tu_dokumen_keuangan',1),(76,5,'A','Administrasi','K-TU-02','Penyampaian laporan keuangan bulanan tepat waktu',NULL,'%',100.00,15.00,'higher_better','tu_laporan_keuangan_ontime',2),(77,5,'A','Administrasi','K-TU-03','Terkumpulnya dokumen penggajian bulanan berikut lampiran pemakaian uang',NULL,'%',100.00,10.00,'higher_better','tu_dokumen_gaji',3),(78,5,'A','Administrasi','K-TU-04','Penyampaian laporan penggajian bulanan tepat waktu',NULL,'%',100.00,10.00,'higher_better','tu_laporan_gaji_ontime',4),(79,5,'A','Performance','K-TU-05','Jumlah kesalahan input Braja (Makin sedikit makin baik)',NULL,'Freq',0.00,5.00,'lower_better','tu_braja_errors',5),(80,5,'A','Performance','K-TU-06','% Dalam kegiatan sekolah',NULL,'%',100.00,5.00,'higher_better','kegiatan_kbm_attendance',6),(81,5,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,7),(82,5,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,8),(83,5,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,9),(84,5,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,10),(85,5,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(86,5,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(87,5,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',13),(88,5,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',14),(89,5,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',15),(90,5,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,15.00,'lower_better','att_mangkir',16),(91,6,'A','Administrasi','K-CS-01','Checklist pekerjaan rutin harian',NULL,'Freq',200.00,15.00,'higher_better','cs_checklist_rutin',1),(92,6,'A','Administrasi','K-CS-02','Jumlah piket bulanan yang dilaksanakan',NULL,'Freq',200.00,10.00,'higher_better','cs_checklist_piket',2),(93,6,'A','Administrasi','K-CS-03','Jumlah kegiatan gotong royong Sabtu yang terlaksana',NULL,'Freq',24.00,10.00,'higher_better','cs_gotong_royong',3),(94,6,'A','Administrasi','K-CS-04','Tingkat kepuasan kebersihan wali murid (Survei E.5)',NULL,'%',100.00,10.00,'higher_better','survey_parent_cleanliness',4),(95,6,'A','Performance','K-CS-05','% kehadiran dalam kegiatan sekolah',NULL,'%',100.00,15.00,'higher_better','kegiatan_kbm_attendance',5),(96,6,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,6),(97,6,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,7),(98,6,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,8),(99,6,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,9),(100,6,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,10),(101,6,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(102,6,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',12),(103,6,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',13),(104,6,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',14),(105,6,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,15.00,'lower_better','att_mangkir',15),(106,7,'A','Administrasi','K-SEC-01','Laporan harian lengkap',NULL,'Freq',200.00,5.00,'higher_better','sec_daily_complete',1),(107,7,'A','Administrasi','K-SEC-02','Laporan tepat waktu',NULL,'%',100.00,5.00,'higher_better','sec_daily_ontime',2),(108,7,'A','Performance','K-SEC-03','Patroli & Kesiapsiagaan sesuai jadwal',NULL,'%',100.00,10.00,'higher_better','sec_patrol_pct',3),(109,7,'A','Performance','K-SEC-04','Respon terhadap insiden (Bintang 1-5)',NULL,'Bintang',5.00,10.00,'higher_better','sec_incident_rating',4),(110,7,'A','Performance','K-SEC-05','% Keluhan dari pengguna jasa (makin rendah makin baik)',NULL,'%',0.00,10.00,'lower_better','sec_complaint_pct',5),(111,7,'A','Performance','K-SEC-06','Jumlah keberhasilan menggagalkan kejadian kriminalitas',NULL,'%',100.00,5.00,'higher_better','sec_prevent_crime',6),(112,7,'A','Performance','K-SEC-07','Jumlah Kebobolan (makin sedikit makin baik)',NULL,'%',0.00,5.00,'lower_better','sec_breach_count',7),(113,7,'B','Sikap','S-01','Sikap dan Penampilan (Fisik prima & seragam rapi)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,8),(114,7,'B','Sikap','S-02','Komunikasi (Senyum sapa dan salam)',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,9),(115,7,'B','Sikap','S-03','Pelayanan (Sikap melayani tamu & civitas)',NULL,'SKALA 1-5',5.00,9.00,'higher_better',NULL,10),(116,7,'B','Sikap','S-04','Profesionalisme (Pengaturan, penjagaan, patroli)',NULL,'SKALA 1-5',5.00,8.00,'higher_better',NULL,11),(117,7,'B','Sikap','S-05','Loyalitas (Siap & sigap menerima tugas)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,12),(118,7,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',7.00,2.00,'lower_better','att_terlambat',13),(119,7,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',7.00,2.00,'lower_better','att_pulang_cepat',14),(120,7,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',7.00,1.00,'lower_better','att_lupa_catat',15),(121,7,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',7.00,15.00,'lower_better','att_mangkir',16),(122,8,'A','Administrasi','K-KS-01','% Dokumen administrasi sekolah terarsip',NULL,'%',100.00,3.00,'higher_better','ks_admin_archived',1),(123,8,'A','Administrasi','K-KS-02','% Penyelesaian tugas sesuai deadline',NULL,'%',100.00,3.00,'higher_better','ks_admin_ontime',2),(124,8,'A','Hasil','K-KS-03','% Kehadiran guru (Target 90%)',NULL,'%',90.00,3.00,'higher_better','unit_teacher_attendance',3),(125,8,'A','Hasil','K-KS-04','% Capaian Tahfidz (Target 95%)',NULL,'%',95.00,4.00,'higher_better','unit_tahfidz_pct',4),(126,8,'A','Hasil','K-KS-05','% Capaian Tahsin (Target 65%)',NULL,'%',65.00,5.00,'higher_better','unit_tahsin_pct',5),(127,8,'A','Hasil','K-KS-06','Capaian nilai rata-rata Diniyah (Target 100%)',NULL,'%',100.00,5.00,'higher_better','unit_diniyah_avg',6),(128,8,'A','Hasil','K-KS-07','% Tunggakan sekolah (Makin rendah makin baik, target 0%)',NULL,'%',0.00,4.00,'lower_better','unit_tunggakan_ratio',7),(129,8,'A','Performance','K-KS-08','Jumlah siswa menjuarai OSN/O2SN/FLS2N tingkat kota',NULL,'Orang',3.00,3.00,'higher_better','unit_lomba_resmi_count',8),(130,8,'A','Performance','K-KS-09','% Guru dengan nilai KPI B atau A',NULL,'%',100.00,4.00,'higher_better','unit_guru_kpi_b',9),(131,8,'A','Performance','K-KS-10','% Siswa baru dalam SPMB vs Target Deputi',NULL,'%',100.00,4.00,'higher_better','unit_spmb_achievement',10),(132,8,'A','Performance','K-KS-11','% Siswa yang melanjutkan ke sekolah favorit',NULL,'%',45.00,4.00,'higher_better','unit_alumni_favorite',11),(133,8,'A','Hubungan','K-KS-12','Rata-rata Turn Over Talent (Target <= 5%, makin rendah makin baik)',NULL,'%',5.00,3.00,'lower_better','unit_turnover_rate',12),(134,8,'A','Hubungan','K-KS-13','% Kepuasan walimurid rata-rata unit',NULL,'%',85.00,3.00,'higher_better','unit_parent_survey_avg',13),(135,8,'A','SDM','K-KS-14','% Guru berprestasi',NULL,'%',5.00,3.00,'higher_better','unit_guru_berprestasi_pct',14),(136,8,'A','SDM','K-KS-15','% Kepuasan guru & karyawan terhadap Kepala Sekolah',NULL,'%',90.00,3.00,'higher_better','unit_staff_survey_avg',15),(137,8,'A','SDM','K-KS-16','% Kehadiran guru di kajian pekanan',NULL,'%',100.00,3.00,'higher_better','unit_kajian_guru_pct',16),(138,8,'A','SDM','K-KS-17','% Guru mencapai target hafalan tahunan (1/2 - 1 Juz)',NULL,'%',50.00,3.00,'higher_better','unit_teacher_tahfidz_pass',17),(139,8,'B','Sikap','S-01','Communication (Penyampaian ide/maksud jelas & dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,18),(140,8,'B','Sikap','S-02','Coaching & Counseling (Penanganan guru)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,19),(141,8,'B','Sikap','S-03','Problem Solving & Decision Making',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,20),(142,8,'B','Sikap','S-04','Teamwork',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,21),(143,8,'B','Sikap','S-05','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,22),(144,8,'B','Sikap','S-06','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,23),(145,8,'B','Sikap','S-07','Pengelolaan SDM',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,24),(146,8,'B','Sikap','S-08','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,25),(147,8,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',26),(148,8,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',27),(149,8,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',28),(150,8,'C','Absensi','AB-04','Mangkir',NULL,'Hari/Thn',6.00,5.00,'lower_better','att_mangkir',29),(151,9,'A','Administrasi','K-KS-01','% Dokumen administrasi sekolah terarsip',NULL,'%',100.00,3.00,'higher_better','ks_admin_archived',1),(152,9,'A','Administrasi','K-KS-02','% Penyelesaian tugas sesuai deadline',NULL,'%',100.00,3.00,'higher_better','ks_admin_ontime',2),(153,9,'A','Hasil','K-KS-03','% Kehadiran guru (Target 90%)',NULL,'%',90.00,3.00,'higher_better','unit_teacher_attendance',3),(154,9,'A','Hasil','K-KS-04','% Capaian Tahfidz (Target 95%)',NULL,'%',95.00,4.00,'higher_better','unit_tahfidz_pct',4),(155,9,'A','Hasil','K-KS-05','% Capaian Baca Latin (Target 90%)',NULL,'%',90.00,4.00,'higher_better','unit_baca_latin_pct',5),(156,9,'A','Hasil','K-KS-06','% Capaian National Kurikulum (Target 90%)',NULL,'%',90.00,4.00,'higher_better','unit_national_kurikulum_pct',6),(157,9,'A','Hasil','K-KS-07','% Tunggakan sekolah (Makin rendah makin baik, target 0%)',NULL,'%',0.00,4.00,'lower_better','unit_tunggakan_ratio',7),(158,9,'A','Performance','K-KS-08','Jumlah siswa menjuarai OSN/O2SN/FLS2N tingkat kota',NULL,'Orang',3.00,3.00,'higher_better','unit_lomba_resmi_count',8),(159,9,'A','Performance','K-KS-09','% Guru dengan nilai KPI B atau A',NULL,'%',100.00,4.00,'higher_better','unit_guru_kpi_b',9),(160,9,'A','Performance','K-KS-10','% Siswa baru dalam SPMB vs Target Deputi',NULL,'%',100.00,4.00,'higher_better','unit_spmb_achievement',10),(161,9,'A','Performance','K-KS-11','% Siswa yang melanjutkan ke sekolah favorit',NULL,'%',45.00,4.00,'higher_better','unit_alumni_favorite',11),(162,9,'A','Hubungan','K-KS-12','Rata-rata Turn Over Talent (Target <= 5%, makin rendah makin baik)',NULL,'%',5.00,4.00,'lower_better','unit_turnover_rate',12),(163,9,'A','Hubungan','K-KS-13','% Kepuasan walimurid rata-rata unit',NULL,'%',85.00,3.00,'higher_better','unit_parent_survey_avg',13),(164,9,'A','SDM','K-KS-14','% Guru berprestasi',NULL,'%',5.00,3.00,'higher_better','unit_guru_berprestasi_pct',14),(165,9,'A','SDM','K-KS-15','% Kepuasan guru & karyawan terhadap Kepala Sekolah',NULL,'%',90.00,3.00,'higher_better','unit_staff_survey_avg',15),(166,9,'A','SDM','K-KS-16','% Kehadiran guru di kajian pekanan',NULL,'%',100.00,3.00,'higher_better','unit_kajian_guru_pct',16),(167,9,'A','SDM','K-KS-17','% Guru mencapai target hafalan tahunan (1/2 - 1 Juz)',NULL,'%',50.00,3.00,'higher_better','unit_teacher_tahfidz_pass',17),(168,9,'B','Sikap','S-01','Communication (Penyampaian ide/maksud jelas & dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,18),(169,9,'B','Sikap','S-02','Coaching & Counseling (Penanganan guru)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,19),(170,9,'B','Sikap','S-03','Problem Solving & Decision Making',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,20),(171,9,'B','Sikap','S-04','Teamwork',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,21),(172,9,'B','Sikap','S-05','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,22),(173,9,'B','Sikap','S-06','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,23),(174,9,'B','Sikap','S-07','Pengelolaan SDM',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,24),(175,9,'B','Sikap','S-08','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,25),(176,9,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',26),(177,9,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',27),(178,9,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',28),(179,9,'C','Absensi','AB-04','Mangkir',NULL,'Hari/Thn',6.00,5.00,'lower_better','att_mangkir',29),(180,10,'A','Administrasi','K-KS-01','% Dokumen administrasi sekolah terarsip',NULL,'%',100.00,3.00,'higher_better','ks_admin_archived',1),(181,10,'A','Administrasi','K-KS-02','% Penyelesaian tugas sesuai deadline',NULL,'%',100.00,3.00,'higher_better','ks_admin_ontime',2),(182,10,'A','Hasil','K-KS-03','% Kehadiran guru (Target 90%)',NULL,'%',90.00,3.00,'higher_better','unit_teacher_attendance',3),(183,10,'A','Hasil','K-KS-04','% Capaian Tahfidz (Target 95%)',NULL,'%',95.00,4.00,'higher_better','unit_tahfidz_pct',4),(184,10,'A','Hasil','K-KS-05','% Capaian Tahsin (Target 65%)',NULL,'%',65.00,5.00,'higher_better','unit_tahsin_pct',5),(185,10,'A','Hasil','K-KS-06','Capaian nilai rata-rata Diniyah (Target 100%)',NULL,'%',100.00,5.00,'higher_better','unit_diniyah_avg',6),(186,10,'A','Hasil','K-KS-07','% Tunggakan sekolah (Makin rendah makin baik, target 0%)',NULL,'%',0.00,4.00,'lower_better','unit_tunggakan_ratio',7),(187,10,'A','Performance','K-KS-08','Jumlah siswa menjuarai OSN/O2SN/FLS2N tingkat kota',NULL,'Orang',3.00,3.00,'higher_better','unit_lomba_resmi_count',8),(188,10,'A','Performance','K-KS-09','% Guru dengan nilai KPI B atau A',NULL,'%',100.00,4.00,'higher_better','unit_guru_kpi_b',9),(189,10,'A','Performance','K-KS-10','% Siswa baru dalam SPMB vs Target Deputi',NULL,'%',100.00,4.00,'higher_better','unit_spmb_achievement',10),(190,10,'A','Performance','K-KS-11','% Siswa yang melanjutkan ke sekolah favorit',NULL,'%',45.00,4.00,'higher_better','unit_alumni_favorite',11),(191,10,'A','Hubungan','K-KS-12','Rata-rata Turn Over Talent',NULL,'%',5.00,2.00,'lower_better','unit_turnover_rate',12),(192,10,'A','Hubungan','K-KS-13','% Kepuasan walimurid rata-rata unit',NULL,'%',85.00,2.00,'higher_better','unit_parent_survey_avg',13),(193,10,'A','Hubungan','K-KS-13B','% Kepuasan siswa terhadap layanan pembelajaran',NULL,'%',85.00,2.00,'higher_better','unit_student_survey_avg',14),(194,10,'A','SDM','K-KS-14','% Guru berprestasi',NULL,'%',5.00,3.00,'higher_better','unit_guru_berprestasi_pct',14),(195,10,'A','SDM','K-KS-15','% Kepuasan guru & karyawan terhadap Kepala Sekolah',NULL,'%',90.00,3.00,'higher_better','unit_staff_survey_avg',15),(196,10,'A','SDM','K-KS-16','% Kehadiran guru di kajian pekanan',NULL,'%',100.00,3.00,'higher_better','unit_kajian_guru_pct',16),(197,10,'A','SDM','K-KS-17','% Guru mencapai target hafalan tahunan (1/2 - 1 Juz)',NULL,'%',50.00,3.00,'higher_better','unit_teacher_tahfidz_pass',17),(198,10,'B','Sikap','S-01','Communication (Penyampaian ide/maksud jelas & dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,18),(199,10,'B','Sikap','S-02','Coaching & Counseling (Penanganan guru)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,19),(200,10,'B','Sikap','S-03','Problem Solving & Decision Making',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,20),(201,10,'B','Sikap','S-04','Teamwork',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,21),(202,10,'B','Sikap','S-05','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,22),(203,10,'B','Sikap','S-06','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,23),(204,10,'B','Sikap','S-07','Pengelolaan SDM',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,24),(205,10,'B','Sikap','S-08','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,25),(206,10,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',26),(207,10,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',27),(208,10,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',28),(209,10,'C','Absensi','AB-04','Mangkir',NULL,'Hari/Thn',6.00,5.00,'lower_better','att_mangkir',29),(210,11,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,7.00,'higher_better','administrasi_completion',1),(211,11,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,7.00,'higher_better','administrasi_ontime',2),(212,11,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa di kelas yang diajarkan',NULL,'%',90.00,10.00,'higher_better','student_attendance',3),(213,11,'A','Pembelajaran','K-PEM-02','Terkumpulnya Best Practice',NULL,'jumlah',1.00,10.00,'higher_better','upload_best_practice',4),(214,11,'A','Pembelajaran','K-PEM-03','Capaian nilai rata-rata mapel yang diajarkan',NULL,'angka',75.00,10.00,'higher_better','subject_average',5),(215,11,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',88.00,8.00,'higher_better','supervisi_avg',6),(216,11,'A','Performance','K-PRF-02','% target hafalan tahunan guru (1 Juz)',NULL,'%',100.00,5.00,'higher_better','teacher_tahfidz_target',7),(217,11,'A','Performance','K-PRF-03','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,8.00,'higher_better','kegiatan_kbm_attendance',8),(218,11,'B','Sikap','S-01','Communication (Lisan & tulisan jelas dan mudah dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,9),(219,11,'B','Sikap','S-02','Profesionalisme (Refleksi diri & pemanfaatan TIK)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,10),(220,11,'B','Sikap','S-03','Teamwork (Kerjasama rekan kerja)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(221,11,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(222,11,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(223,11,'B','Sikap','S-06','Keteladanan & Disiplin Islami',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(224,11,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',15),(225,11,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',16),(226,11,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',17),(227,11,'C','Absensi','AB-04','Tidak Hadir / Mangkir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',18);
/*!40000 ALTER TABLE `akfi_template_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `akfi_templates`
--

DROP TABLE IF EXISTS `akfi_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `akfi_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `position_code` varchar(50) NOT NULL,
  `position_name` varchar(100) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `bobot_kinerja` decimal(5,2) NOT NULL DEFAULT 65.00,
  `bobot_sikap` decimal(5,2) NOT NULL DEFAULT 20.00,
  `bobot_absensi` decimal(5,2) NOT NULL DEFAULT 15.00,
  `grade_a_min` decimal(5,2) DEFAULT 91.00,
  `grade_b_min` decimal(5,2) DEFAULT 81.00,
  `grade_c_min` decimal(5,2) DEFAULT 61.00,
  `grade_d_min` decimal(5,2) DEFAULT 31.00,
  `grade_e_min` decimal(5,2) DEFAULT 11.00,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `akfi_templates_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akfi_templates`
--

LOCK TABLES `akfi_templates` WRITE;
/*!40000 ALTER TABLE `akfi_templates` DISABLE KEYS */;
INSERT INTO `akfi_templates` VALUES (1,'GURU_MAPEL','Guru Mata Pelajaran (SD/SMP/SMA)',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(2,'GURU_TAHTA','Guru Tahfidz / Tahta (SD/SMP/SMA)',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(3,'GURU_WALAS','Guru Wali Kelas (SD/SMP/SMA)',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(4,'GURU_TK','Guru TK / KB',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(5,'TU','Tata Usaha',1,60.00,20.00,20.00,90.00,70.00,50.00,30.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(6,'CS','Cleaning Services',1,60.00,20.00,20.00,90.00,70.00,50.00,30.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(7,'SECURITY','Security',1,50.00,30.00,20.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(8,'KANIT_SD','Kepala Sekolah (SD)',1,60.00,30.00,10.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(9,'KANIT_TK','Kepala Sekolah (TK)',1,60.00,30.00,10.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(10,'KANIT_SMP_SMA','Kepala Sekolah (SMP & SMA)',1,60.00,30.00,10.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(11,'GURU_MAPEL_KBTK','Guru Mata Pelajaran (KB/TK)',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-10-24 07:00:06','2026-10-24 07:14:19');
/*!40000 ALTER TABLE `akfi_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `alumni_tracking`
--

DROP TABLE IF EXISTS `alumni_tracking`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alumni_tracking` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `graduation_year` varchar(10) NOT NULL,
  `destination_type` enum('Sekolah','Universitas','Kerja/Lainnya') NOT NULL,
  `destination_name` varchar(150) NOT NULL,
  `is_favorite` tinyint(1) DEFAULT 0,
  `major` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `alumni_tracking_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `alumni_tracking_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumni_tracking`
--

LOCK TABLES `alumni_tracking` WRITE;
/*!40000 ALTER TABLE `alumni_tracking` DISABLE KEYS */;
/*!40000 ALTER TABLE `alumni_tracking` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES ('app_favicon','uploads/logos/favicon_1790468135.png','2026-09-27 00:15:35'),('app_logo','uploads/logos/app_logo_1790375160.png','2026-09-25 22:26:00'),('app_name','Manajemen Sekolah Berbasis Komputer (MASEKO)','2026-09-29 13:51:40'),('app_short_name','AKFI','2026-09-29 13:51:40'),('app_sub_name','Manajemen Sekolah Berbasis Komputer','2026-09-29 13:51:40'),('storage_cold_driver','webdav','2026-10-20 22:25:29'),('storage_cold_enabled','true','2026-10-20 22:25:29'),('storage_cold_local_path','C:/xampp/htdocs/akfioke/storage/nas_cold','2026-10-20 22:25:29'),('storage_cold_webdav_pass','D@RKknight3','2026-10-20 22:25:29'),('storage_cold_webdav_path','/','2026-10-20 22:25:29'),('storage_cold_webdav_url','https://nas.godigital.cyou/','2026-10-20 22:25:29'),('storage_cold_webdav_user','admin','2026-10-20 22:25:29');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `archived_files`
--

DROP TABLE IF EXISTS `archived_files`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `archived_files` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `table_name` varchar(64) NOT NULL,
  `record_id` bigint(20) NOT NULL,
  `original_path` varchar(255) NOT NULL,
  `cold_path` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL DEFAULT 0,
  `archived_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_archived_ta` (`academic_year_id`),
  KEY `idx_archived_orig_path` (`original_path`),
  KEY `idx_archived_table_record` (`table_name`,`record_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `archived_files`
--

LOCK TABLES `archived_files` WRITE;
/*!40000 ALTER TABLE `archived_files` DISABLE KEYS */;
/*!40000 ALTER TABLE `archived_files` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `asset_categories`
--

DROP TABLE IF EXISTS `asset_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `asset_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `asset_categories`
--

LOCK TABLES `asset_categories` WRITE;
/*!40000 ALTER TABLE `asset_categories` DISABLE KEYS */;
INSERT INTO `asset_categories` VALUES (1,'Elektronik & IT','Perangkat komputer, printer, laptop, proyektor, CCTV','2026-10-08 11:03:58','2026-10-08 11:03:58'),(2,'Mebel & Furniture','Meja guru, kursi siswa, lemari besi, rak buku, whiteboard','2026-10-08 11:03:58','2026-10-08 11:03:58'),(3,'Peralatan Kantor & Sekolah','Dispenser, AC, kipas angin, mesin fotokopi, bel sekolah','2026-10-08 11:03:58','2026-10-08 11:03:58'),(4,'ATK / Habis Pakai','Kertas HVS, spidol, pulpen, map, tinta printer (Barang pecah belah / habis)','2026-10-08 11:03:58','2026-10-08 11:03:58'),(5,'Perlengkapan Kelas & Karpet','Karpet busa, kasur busa, tirai, jam dinding','2026-10-08 11:03:58','2026-10-08 11:03:58'),(6,'Sarana Olahraga & Seni','Matras, bola, sound system, mikrofon, alat musik','2026-10-08 11:03:58','2026-10-08 11:03:58');
/*!40000 ALTER TABLE `asset_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `assets`
--

DROP TABLE IF EXISTS `assets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `assets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asset_code` varchar(100) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `purchase_date` date NOT NULL,
  `unit_id` int(11) NOT NULL,
  `condition_status` enum('Baik','Rusak Ringan','Rusak Berat','Afkir/Scrap') DEFAULT 'Baik',
  `user_holder_id` int(11) DEFAULT NULL,
  `user_holder_name` varchar(150) DEFAULT NULL,
  `location` varchar(150) DEFAULT NULL,
  `price` decimal(15,2) DEFAULT 0.00,
  `photo` varchar(255) DEFAULT NULL,
  `is_consumable` tinyint(1) DEFAULT 0,
  `stock_status` enum('Ready','Habis Stock','Aktif','Scrapped') DEFAULT 'Aktif',
  `quantity` int(11) DEFAULT 1,
  `po_id` int(11) DEFAULT NULL,
  `status` enum('Aktif','Scrapped','Mutasi') DEFAULT 'Aktif',
  `notes` text DEFAULT NULL,
  `created_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `asset_code` (`asset_code`),
  KEY `idx_assets_unit` (`unit_id`),
  KEY `idx_assets_cat` (`category_id`),
  KEY `idx_assets_holder` (`user_holder_id`),
  KEY `idx_assets_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `assets`
--

LOCK TABLES `assets` WRITE;
/*!40000 ALTER TABLE `assets` DISABLE KEYS */;
/*!40000 ALTER TABLE `assets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `username` varchar(100) DEFAULT NULL,
  `role_name` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) NOT NULL,
  `action` varchar(50) NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `record_id` varchar(50) DEFAULT NULL,
  `old_values` longtext DEFAULT NULL,
  `new_values` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_audit_time` (`created_at`),
  KEY `idx_audit_user` (`user_id`),
  KEY `idx_audit_table` (`table_name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'admin','operator_yayasan','127.0.0.1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-10-26 14:43:10');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_installment_payments`
--

DROP TABLE IF EXISTS `bill_installment_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_installment_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `installment_id` int(11) NOT NULL,
  `receipt_number` varchar(100) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(12,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Cash',
  `notes` text DEFAULT NULL,
  `received_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `installment_id` (`installment_id`),
  KEY `received_by_user_id` (`received_by_user_id`),
  CONSTRAINT `bill_installment_payments_ibfk_1` FOREIGN KEY (`installment_id`) REFERENCES `bill_installments` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bill_installment_payments_ibfk_2` FOREIGN KEY (`received_by_user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_installment_payments`
--

LOCK TABLES `bill_installment_payments` WRITE;
/*!40000 ALTER TABLE `bill_installment_payments` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_installment_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_installments`
--

DROP TABLE IF EXISTS `bill_installments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_installments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `agreement_number` varchar(100) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `bill_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `parent_name` varchar(150) NOT NULL,
  `parent_ktp` varchar(50) DEFAULT NULL,
  `parent_address` text DEFAULT NULL,
  `parent_phone` varchar(50) DEFAULT NULL,
  `student_class` varchar(100) DEFAULT NULL,
  `scheme_type` enum('lunas_tanggal','cicil_bulan','cicil_nominal') NOT NULL DEFAULT 'cicil_bulan',
  `scheme_lunas_date` date DEFAULT NULL,
  `scheme_months` int(11) DEFAULT NULL,
  `scheme_start_date` date DEFAULT NULL,
  `scheme_min_monthly` decimal(12,2) DEFAULT NULL,
  `scheme_end_date` date DEFAULT NULL,
  `total_debt` decimal(12,2) NOT NULL,
  `paid_total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remaining_debt` decimal(12,2) NOT NULL,
  `status` enum('Aktif','Lunas','Batal') NOT NULL DEFAULT 'Aktif',
  `approved_by_level` enum('kepala_sekolah','deputi_pendidikan','tata_usaha') NOT NULL,
  `approved_by_user_id` int(11) NOT NULL,
  `approved_at` datetime NOT NULL DEFAULT current_timestamp(),
  `agreement_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `agreement_number` (`agreement_number`),
  KEY `unit_id` (`unit_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `bill_installments_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `bill_installments_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_installments`
--

LOCK TABLES `bill_installments` WRITE;
/*!40000 ALTER TABLE `bill_installments` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_installments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bill_reminders`
--

DROP TABLE IF EXISTS `bill_reminders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bill_reminders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `status` enum('Diajukan','Disetujui','Terkirim','Ditolak') DEFAULT 'Diajukan',
  `proposed_by_user_id` int(11) NOT NULL,
  `approved_by_user_id` int(11) DEFAULT NULL,
  `letter_number` varchar(100) DEFAULT NULL,
  `email_sent_to` varchar(150) DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  `error_message` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `bill_id` (`bill_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `bill_reminders_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `bill_reminders_ibfk_2` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bill_reminders_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_reminders`
--

LOCK TABLES `bill_reminders` WRITE;
/*!40000 ALTER TABLE `bill_reminders` DISABLE KEYS */;
/*!40000 ALTER TABLE `bill_reminders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `bills`
--

DROP TABLE IF EXISTS `bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `bill_month` int(11) NOT NULL,
  `bill_year` int(11) NOT NULL,
  `title` varchar(150) NOT NULL DEFAULT 'SPP Bulanan',
  `amount` decimal(12,2) NOT NULL,
  `items_detail` longtext DEFAULT NULL,
  `amount_spp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `amount_non_spp` decimal(12,2) NOT NULL DEFAULT 0.00,
  `due_date` date NOT NULL,
  `status` enum('Lunas','Tertunggak') NOT NULL DEFAULT 'Tertunggak',
  `pipeline_stage` varchar(50) NOT NULL DEFAULT 'tu_draft',
  `tu_reminder_sent_at` datetime DEFAULT NULL,
  `tu_reminder_letter_no` varchar(100) DEFAULT NULL,
  `kepsek_schedule_date` datetime DEFAULT NULL,
  `kepsek_schedule_place` varchar(255) DEFAULT NULL,
  `kepsek_letter_no` varchar(100) DEFAULT NULL,
  `kepsek_summon_sent_at` datetime DEFAULT NULL,
  `deputi_schedule_date` datetime DEFAULT NULL,
  `deputi_schedule_place` varchar(255) DEFAULT NULL,
  `deputi_letter_no` varchar(100) DEFAULT NULL,
  `deputi_summon_sent_at` datetime DEFAULT NULL,
  `pipeline_resolved_at` datetime DEFAULT NULL,
  `pipeline_resolved_level` varchar(50) DEFAULT NULL,
  `paid_date` date DEFAULT NULL,
  `paid_amount` decimal(12,2) DEFAULT 0.00,
  `payment_method` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `student_id` (`student_id`),
  CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `bills_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `bills_ibfk_3` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bills`
--

LOCK TABLES `bills` WRITE;
/*!40000 ALTER TABLE `bills` DISABLE KEYS */;
/*!40000 ALTER TABLE `bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklists_braja`
--

DROP TABLE IF EXISTS `checklists_braja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checklists_braja` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `trx_date` date NOT NULL,
  `item_desc` varchar(255) NOT NULL,
  `amount` decimal(15,2) NOT NULL DEFAULT 0.00,
  `is_error` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_unit` (`unit_id`),
  KEY `idx_ta` (`academic_year_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklists_braja`
--

LOCK TABLES `checklists_braja` WRITE;
/*!40000 ALTER TABLE `checklists_braja` DISABLE KEYS */;
/*!40000 ALTER TABLE `checklists_braja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklists_cs`
--

DROP TABLE IF EXISTS `checklists_cs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checklists_cs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `check_type` enum('Rutin Harian','Piket Bulanan','Gotong Royong') NOT NULL,
  `check_date` date NOT NULL,
  `month_num` int(11) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `verified_by_user_id` int(11) DEFAULT NULL,
  `photo_proof` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `employee_id` (`employee_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `checklists_cs_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `checklists_cs_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `checklists_cs_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklists_cs`
--

LOCK TABLES `checklists_cs` WRITE;
/*!40000 ALTER TABLE `checklists_cs` DISABLE KEYS */;
/*!40000 ALTER TABLE `checklists_cs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `checklists_security`
--

DROP TABLE IF EXISTS `checklists_security`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `checklists_security` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `check_date` date NOT NULL,
  `is_complete` tinyint(1) DEFAULT 1,
  `is_on_time` tinyint(1) DEFAULT 1,
  `check_items_json` text NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sec_date` (`employee_id`,`check_date`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `checklists_security_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `checklists_security_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `checklists_security_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklists_security`
--

LOCK TABLES `checklists_security` WRITE;
/*!40000 ALTER TABLE `checklists_security` DISABLE KEYS */;
/*!40000 ALTER TABLE `checklists_security` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_schedule_halaqahs`
--

DROP TABLE IF EXISTS `class_schedule_halaqahs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_schedule_halaqahs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `schedule_id` int(11) NOT NULL,
  `halaqah_number` int(11) NOT NULL DEFAULT 1,
  `halaqah_name` varchar(100) NOT NULL DEFAULT 'Halaqah 1',
  `teacher_id` int(11) NOT NULL,
  `substitute_teacher_id` int(11) DEFAULT NULL,
  `substitute_date` date DEFAULT NULL,
  `substitute_reason` varchar(100) DEFAULT NULL,
  `substitute_note` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sched_halaqah_num` (`schedule_id`,`halaqah_number`),
  KEY `idx_csh_teacher` (`teacher_id`),
  KEY `idx_csh_substitute` (`substitute_teacher_id`),
  CONSTRAINT `class_schedule_halaqahs_ibfk_1` FOREIGN KEY (`schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_schedule_halaqahs_ibfk_2` FOREIGN KEY (`teacher_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_schedule_halaqahs_ibfk_3` FOREIGN KEY (`substitute_teacher_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_schedule_halaqahs`
--

LOCK TABLES `class_schedule_halaqahs` WRITE;
/*!40000 ALTER TABLE `class_schedule_halaqahs` DISABLE KEYS */;
/*!40000 ALTER TABLE `class_schedule_halaqahs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_schedules`
--

DROP TABLE IF EXISTS `class_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `day` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL,
  `jam_ke` int(11) NOT NULL,
  `time_start` varchar(10) NOT NULL DEFAULT '07:15',
  `time_end` varchar(10) NOT NULL DEFAULT '07:55',
  `subject_id` int(11) DEFAULT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `session_type` enum('pelajaran','upacara','istirahat','sholat','kegiatan') NOT NULL DEFAULT 'pelajaran',
  `custom_label` varchar(100) DEFAULT NULL,
  `substitute_teacher_id` int(11) DEFAULT NULL,
  `substitute_date` date DEFAULT NULL,
  `substitute_reason` varchar(100) DEFAULT NULL,
  `substitute_note` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_schedule_cell` (`unit_id`,`academic_year_id`,`class_id`,`day`,`jam_ke`),
  KEY `idx_sched_unit_ta` (`unit_id`,`academic_year_id`),
  KEY `idx_sched_teacher` (`teacher_id`,`day`,`jam_ke`),
  KEY `idx_sched_substitute` (`substitute_teacher_id`,`day`,`jam_ke`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  CONSTRAINT `class_schedules_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_schedules_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_schedules_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_schedules_ibfk_4` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE SET NULL,
  CONSTRAINT `class_schedules_ibfk_5` FOREIGN KEY (`teacher_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `class_schedules_ibfk_6` FOREIGN KEY (`substitute_teacher_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_schedules`
--

LOCK TABLES `class_schedules` WRITE;
/*!40000 ALTER TABLE `class_schedules` DISABLE KEYS */;
/*!40000 ALTER TABLE `class_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `class_students`
--

DROP TABLE IF EXISTS `class_students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `class_students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `status` enum('Aktif','Pindah','Lulus','Tinggal') DEFAULT 'Aktif',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_class_student_year` (`class_id`,`student_id`,`academic_year_id`),
  UNIQUE KEY `uq_student_year` (`student_id`,`academic_year_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `class_students_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_students_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `class_students_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_students`
--

LOCK TABLES `class_students` WRITE;
/*!40000 ALTER TABLE `class_students` DISABLE KEYS */;
/*!40000 ALTER TABLE `class_students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `classes`
--

DROP TABLE IF EXISTS `classes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `classes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `level` varchar(20) NOT NULL,
  `walas_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `walas_employee_id` int(11) GENERATED ALWAYS AS (`walas_id`) VIRTUAL,
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `walas_id` (`walas_id`),
  CONSTRAINT `classes_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `classes_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `classes_ibfk_3` FOREIGN KEY (`walas_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
/*!40000 ALTER TABLE `classes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `database_backups`
--

DROP TABLE IF EXISTS `database_backups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `database_backups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_size` bigint(20) NOT NULL,
  `backup_type` varchar(50) DEFAULT 'Manual',
  `synced_to_nas` tinyint(1) DEFAULT 0,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `database_backups`
--

LOCK TABLES `database_backups` WRITE;
/*!40000 ALTER TABLE `database_backups` DISABLE KEYS */;
/*!40000 ALTER TABLE `database_backups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_attendance`
--

DROP TABLE IF EXISTS `employee_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `month_num` int(11) NOT NULL,
  `hadir` int(11) NOT NULL DEFAULT 20,
  `terlambat` int(11) NOT NULL DEFAULT 0,
  `pulang_cepat` int(11) NOT NULL DEFAULT 0,
  `lupa_catat` int(11) NOT NULL DEFAULT 0,
  `mangkir` int(11) NOT NULL DEFAULT 0,
  `izin` int(11) NOT NULL DEFAULT 0,
  `alpha` int(11) NOT NULL DEFAULT 0,
  `terlambat_menit` int(11) NOT NULL DEFAULT 0,
  `sakit` int(11) NOT NULL DEFAULT 0,
  `cuti` int(11) NOT NULL DEFAULT 0,
  `dinas_luar` int(11) NOT NULL DEFAULT 0,
  `kajian_hadir` int(11) NOT NULL DEFAULT 4,
  `kajian_total` int(11) NOT NULL DEFAULT 4,
  `kajian_pekanan_hadir` int(11) NOT NULL DEFAULT 4,
  `kajian_pekanan_total` int(11) NOT NULL DEFAULT 4,
  `recorded_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_att` (`employee_id`,`unit_id`,`academic_year_id`,`month_num`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `employee_attendance_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `employee_attendance_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `employee_attendance_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_attendance`
--

LOCK TABLES `employee_attendance` WRITE;
/*!40000 ALTER TABLE `employee_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `employee_turnover_logs`
--

DROP TABLE IF EXISTS `employee_turnover_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employee_turnover_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `action_type` enum('Mutasi Masuk','Mutasi Keluar','Resign','Diberhentikan','Pensiun') NOT NULL,
  `from_unit_id` int(11) DEFAULT NULL,
  `to_unit_id` int(11) DEFAULT NULL,
  `effective_date` date NOT NULL,
  `reason` text NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `employee_turnover_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_turnover_logs`
--

LOCK TABLES `employee_turnover_logs` WRITE;
/*!40000 ALTER TABLE `employee_turnover_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `employee_turnover_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary table structure for view `employee_turnovers`
--

DROP TABLE IF EXISTS `employee_turnovers`;
/*!50001 DROP VIEW IF EXISTS `employee_turnovers`*/;
SET @saved_cs_client     = @@character_set_client;
SET character_set_client = utf8;
/*!50001 CREATE VIEW `employee_turnovers` AS SELECT
 1 AS `id`,
  1 AS `employee_id`,
  1 AS `action_type`,
  1 AS `from_unit_id`,
  1 AS `to_unit_id`,
  1 AS `effective_date`,
  1 AS `reason`,
  1 AS `created_by_user_id`,
  1 AS `created_at` */;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `employees` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `nipy` varchar(50) NOT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `nama` varchar(150) NOT NULL,
  `gender` enum('L','P') NOT NULL DEFAULT 'L',
  `tingkat_pendidikan` varchar(20) NOT NULL DEFAULT 'S1',
  `birthplace` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `tmt` date NOT NULL,
  `level_posisi` varchar(100) NOT NULL,
  `position` varchar(100) GENERATED ALWAYS AS (`level_posisi`) VIRTUAL,
  `unit_id` int(11) DEFAULT NULL,
  `atasan_langsung_id` int(11) DEFAULT NULL,
  `no_telp_atasan` varchar(50) DEFAULT NULL,
  `contact_phone` varchar(50) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('Aktif','Nonaktif','Resign','Mutasi Keluar','Pensiun') DEFAULT 'Aktif',
  `is_deleted` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nipy` (`nipy`),
  KEY `user_id` (`user_id`),
  KEY `atasan_langsung_id` (`atasan_langsung_id`),
  KEY `idx_emp_unit` (`unit_id`),
  KEY `idx_emp_status` (`status`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `employees_ibfk_3` FOREIGN KEY (`atasan_langsung_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
/*!40000 ALTER TABLE `employees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `guru_berprestasi`
--

DROP TABLE IF EXISTS `guru_berprestasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `guru_berprestasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `level` varchar(50) NOT NULL,
  `year` varchar(10) NOT NULL,
  `proof_document` varchar(255) DEFAULT NULL,
  `status` enum('Terverifikasi','Dalam Proses','Selesai','Dibatalkan') NOT NULL DEFAULT 'Terverifikasi',
  `notes` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `guru_berprestasi_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `guru_berprestasi_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `guru_berprestasi_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guru_berprestasi`
--

LOCK TABLES `guru_berprestasi` WRITE;
/*!40000 ALTER TABLE `guru_berprestasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `guru_berprestasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `karya_ilmiah_guru`
--

DROP TABLE IF EXISTS `karya_ilmiah_guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `karya_ilmiah_guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `type` enum('Best Practice','PTK') NOT NULL DEFAULT 'Best Practice',
  `title` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL DEFAULT 0,
  `status` enum('Menunggu Review','Disetujui','Revisi') NOT NULL DEFAULT 'Menunggu Review',
  `reviewer_user_id` int(11) DEFAULT NULL,
  `review_notes` text DEFAULT NULL,
  `reviewed_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_karya_emp` (`employee_id`),
  KEY `idx_karya_unit` (`unit_id`),
  KEY `idx_karya_ta` (`academic_year_id`),
  KEY `idx_karya_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karya_ilmiah_guru`
--

LOCK TABLES `karya_ilmiah_guru` WRITE;
/*!40000 ALTER TABLE `karya_ilmiah_guru` DISABLE KEYS */;
/*!40000 ALTER TABLE `karya_ilmiah_guru` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kegiatan_luar_kbm`
--

DROP TABLE IF EXISTS `kegiatan_luar_kbm`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kegiatan_luar_kbm` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `event_date` date NOT NULL,
  `location` varchar(150) DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `kegiatan_luar_kbm_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `kegiatan_luar_kbm_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kegiatan_luar_kbm`
--

LOCK TABLES `kegiatan_luar_kbm` WRITE;
/*!40000 ALTER TABLE `kegiatan_luar_kbm` DISABLE KEYS */;
/*!40000 ALTER TABLE `kegiatan_luar_kbm` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kegiatan_luar_kbm_participants`
--

DROP TABLE IF EXISTS `kegiatan_luar_kbm_participants`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kegiatan_luar_kbm_participants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kegiatan_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `status` enum('Hadir','Hadir Panitia','Tidak Hadir') NOT NULL DEFAULT 'Hadir',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kegiatan_emp` (`kegiatan_id`,`employee_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `kegiatan_luar_kbm_participants_ibfk_1` FOREIGN KEY (`kegiatan_id`) REFERENCES `kegiatan_luar_kbm` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kegiatan_luar_kbm_participants_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kegiatan_luar_kbm_participants`
--

LOCK TABLES `kegiatan_luar_kbm_participants` WRITE;
/*!40000 ALTER TABLE `kegiatan_luar_kbm_participants` DISABLE KEYS */;
/*!40000 ALTER TABLE `kegiatan_luar_kbm_participants` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `master_jenis_administrasi`
--

DROP TABLE IF EXISTS `master_jenis_administrasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `master_jenis_administrasi` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `target_role` varchar(50) NOT NULL,
  `code` varchar(50) NOT NULL,
  `title` text NOT NULL,
  `frequency` enum('1x setahun','2x setahun','3x setahun','setiap bulan') NOT NULL,
  `due_info` varchar(150) DEFAULT NULL,
  `order_num` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=44 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `master_jenis_administrasi`
--

LOCK TABLES `master_jenis_administrasi` WRITE;
/*!40000 ALTER TABLE `master_jenis_administrasi` DISABLE KEYS */;
INSERT INTO `master_jenis_administrasi` VALUES (1,'KEPALA_SEKOLAH','KS-01','Struktur Kurikulum yang disahkan Divisi Pendidikan','1x setahun','Awal Tahun Ajaran',1),(2,'KEPALA_SEKOLAH','KS-02','Struktur Beban Mengajar Guru yang disahkan Divisi Pendidikan','1x setahun','Awal Tahun Ajaran',2),(3,'KEPALA_SEKOLAH','KS-03','Kalender Pendidikan Unit yang disahkan Divisi Pendidikan','1x setahun','Awal Tahun Ajaran',3),(4,'KEPALA_SEKOLAH','KS-04','Struktur Organisasi Sekolah','1x setahun','Awal Tahun Ajaran',4),(5,'KEPALA_SEKOLAH','KS-05','SK Komite Sekolah','1x setahun','Awal Tahun Ajaran',5),(6,'KEPALA_SEKOLAH','KS-06','PPT Parents Meeting','1x setahun','Semester Ganjil',6),(7,'KEPALA_SEKOLAH','KS-07','Laporan Pelaksanaan Parent Meeting','1x setahun','Semester Ganjil',7),(8,'KEPALA_SEKOLAH','KS-08','KKM (KPI) Mata Pelajaran','1x setahun','Awal Tahun Ajaran',8),(9,'KEPALA_SEKOLAH','KS-09','Dokumen 1 Sekolah','1x setahun','Awal Tahun Ajaran',9),(10,'KEPALA_SEKOLAH','KS-10','Administrasi Supervisi - SK Assessor, Jadwal, Blanko Penilaian, laporan nilai','1x setahun','Akhir Semester Genap',10),(11,'KEPALA_SEKOLAH','KS-11','Base line Tahfidz siswa','3x setahun','Awal, Tengah, Akhir Tahun',11),(12,'KEPALA_SEKOLAH','KS-12','Raport Pendidikan','1x setahun','Akhir Tahun',12),(13,'KEPALA_SEKOLAH','KS-13','Laporan Bulanan','setiap bulan','Tiap akhir bulan berjalan',13),(14,'KEPALA_SEKOLAH','KS-14','Laporan Closing Laporan Keuangan Bulanan (CA)','setiap bulan','Tiap tanggal 5 bulan berikutnya',14),(15,'KEPALA_SEKOLAH','KS-15','Laporan Penilaian Kinerja Guru (AKFI)','1x setahun','Akhir Tahun Ajaran',15),(16,'KEPALA_SEKOLAH','KS-16','Administrasi Pelaksanaan SPMB (Standard dan Pedoman Kerja SPMB, laporan pelaksanaan)','1x setahun','Periode SPMB',16),(17,'KEPALA_SEKOLAH','KS-17','Laporan Pelaksanaan Kegiatan UAS (Nilai Murni) dan Ijazah kelas akhir','2x setahun','Tiap Semester',17),(18,'KEPALA_SEKOLAH','KS-18','Dokumen pelacakan alumni','1x setahun','Akhir Tahun Ajaran',18),(19,'GURU_TK','TK-ADM-01','Program Tahunan (Prota)','1x setahun','Awal Tahun Ajaran',1),(20,'GURU_TK','TK-ADM-02','Program Semester (Promes)','2x setahun','Awal Tiap Semester',2),(21,'GURU_TK','TK-ADM-03','Silabus','1x setahun','Awal Tahun Ajaran',3),(22,'GURU_TK','TK-ADM-04','Rencana Pelaksanaan Pembelajaran (RPP) / Modul Ajar','setiap bulan','Tiap awal bulan',4),(23,'GURU_TK','TK-ADM-05','Kalender Pendidikan','1x setahun','Awal Tahun Ajaran',5),(24,'GURU_TK','TK-ADM-06','Analisis Ketercapaian Kurikulum','2x setahun','Tiap Akhir Semester',6),(25,'GURU_TK','TK-ADM-07','Administrasi Kelas','1x setahun','Berjalan',7),(26,'GURU_MAPEL','GUR-01','Perencanaan Pembelajaran: Alur Tujuan Pembelajaran (ATP) / Prota','1x setahun','Awal Tahun Ajaran',1),(27,'GURU_MAPEL','GUR-02','Perencanaan Pembelajaran: Silabus','1x setahun','Awal Tahun Ajaran',2),(28,'GURU_MAPEL','GUR-03','Perencanaan Pembelajaran: RPP / Modul Ajar (max 3 mapel diampu)','setiap bulan','Bulanan',3),(29,'GURU_MAPEL','GUR-04','Pelaksanaan Pembelajaran: Daftar Hadir Siswa per Mata Pelajaran','setiap bulan','Bulanan',4),(30,'GURU_MAPEL','GUR-05','Pelaksanaan Pembelajaran: Jurnal Mengajar','setiap bulan','Bulanan',5),(31,'GURU_MAPEL','GUR-06','Penilaian: Kisi-kisi Soal (Ujian Semester)','2x setahun','Tiap Ujian Semester',6),(32,'GURU_MAPEL','GUR-07','Penilaian: Daftar Nilai Harian, Tugas, Ujian, Nilai Raport','setiap bulan','Bulanan',7),(33,'GURU_MAPEL','GUR-08','Administrasi Tambahan: Dokumentasi Kegiatan Pembelajaran (min 2x)','2x setahun','Tiap Semester',8),(34,'WALAS','WALAS-01','Data dan Identitas Siswa (Buku Induk, Biodata, Struktur Organisasi, Denah, Foto)','1x setahun','Awal Tahun Ajaran',1),(35,'WALAS','WALAS-02','Kehadiran dan Ketertiban (Daftar Hadir Harian, Rekap, Catatan Pelanggaran, Reward & Punishment)','setiap bulan','Bulanan',2),(36,'WALAS','WALAS-03','Komunikasi dengan Orangtua (Buku Kontak, Notulen Pertemuan, Buku Penghubung, Perkembangan)','setiap bulan','Bulanan',3),(37,'WALAS','WALAS-04','Penilaian dan Rapor (Rekap Nilai Guru Mapel, Analisis Nilai, Rapor Siswa, Leger)','2x setahun','Tiap Semester',4),(38,'WALAS','WALAS-05','Kegiatan Kesiswaan (Jadwal Piket, Dokumentasi Kelas, Agenda Harian/Mingguan)','setiap bulan','Bulanan',5),(39,'WALAS','WALAS-06','Administrasi Tambahan (Buku Kas Kelas, Inventaris Barang, Catatan Khusus Siswa)','1x setahun','Berjalan',6),(40,'TU','TU-01','Terkumpulnya dokumen keuangan bulanan berikut lampiran pemakaian uang','setiap bulan','Bulanan',1),(41,'TU','TU-02','Penyampaian laporan keuangan bulanan tepat waktu','setiap bulan','Tiap tanggal 5',2),(42,'TU','TU-03','Terkumpulnya dokumen penggajian bulanan berikut lampiran pemakaian uang','setiap bulan','Bulanan',3),(43,'TU','TU-04','Penyampaian laporan penggajian bulanan tepat waktu','setiap bulan','Tiap tanggal 25',4);
/*!40000 ALTER TABLE `master_jenis_administrasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mushaf_madinah`
--

DROP TABLE IF EXISTS `mushaf_madinah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mushaf_madinah` (
  `page_number` int(11) NOT NULL,
  `juz_number` int(11) NOT NULL,
  `surah_start` int(11) NOT NULL,
  `ayah_start` int(11) NOT NULL,
  `surah_end` int(11) NOT NULL,
  `ayah_end` int(11) NOT NULL,
  `lines_count` int(11) DEFAULT 15,
  PRIMARY KEY (`page_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mushaf_madinah`
--

LOCK TABLES `mushaf_madinah` WRITE;
/*!40000 ALTER TABLE `mushaf_madinah` DISABLE KEYS */;
INSERT INTO `mushaf_madinah` VALUES (1,1,1,1,1,10,15),(2,1,2,1,2,10,15),(3,1,2,1,2,10,15),(4,1,2,1,2,10,15),(5,1,2,1,2,10,15),(6,1,2,1,2,10,15),(7,1,2,1,2,10,15),(8,1,2,1,2,10,15),(9,1,2,1,2,10,15),(10,1,2,1,2,10,15),(11,1,2,1,2,10,15),(12,1,2,1,2,10,15),(13,1,2,1,2,10,15),(14,1,2,1,2,10,15),(15,1,2,1,2,10,15),(16,1,2,1,2,10,15),(17,1,2,1,2,10,15),(18,1,2,1,2,10,15),(19,1,2,1,2,10,15),(20,1,2,1,2,10,15),(21,2,2,1,2,10,15),(22,2,2,1,2,10,15),(23,2,2,1,2,10,15),(24,2,2,1,2,10,15),(25,2,2,1,2,10,15),(26,2,2,1,2,10,15),(27,2,2,1,2,10,15),(28,2,2,1,2,10,15),(29,2,2,1,2,10,15),(30,2,2,1,2,10,15),(31,2,2,1,2,10,15),(32,2,2,1,2,10,15),(33,2,2,1,2,10,15),(34,2,2,1,2,10,15),(35,2,2,1,2,10,15),(36,2,2,1,2,10,15),(37,2,2,1,2,10,15),(38,2,2,1,2,10,15),(39,2,2,1,2,10,15),(40,2,2,1,2,10,15),(41,3,2,1,2,10,15),(42,3,2,1,2,10,15),(43,3,2,1,2,10,15),(44,3,2,1,2,10,15),(45,3,2,1,2,10,15),(46,3,2,1,2,10,15),(47,3,2,1,2,10,15),(48,3,2,1,2,10,15),(49,3,2,1,2,10,15),(50,3,3,1,3,10,15),(51,3,3,1,3,10,15),(52,3,3,1,3,10,15),(53,3,3,1,3,10,15),(54,3,3,1,3,10,15),(55,3,3,1,3,10,15),(56,3,3,1,3,10,15),(57,3,3,1,3,10,15),(58,3,3,1,3,10,15),(59,3,3,1,3,10,15),(60,3,3,1,3,10,15),(61,4,3,1,3,10,15),(62,4,3,1,3,10,15),(63,4,3,1,3,10,15),(64,4,3,1,3,10,15),(65,4,3,1,3,10,15),(66,4,3,1,3,10,15),(67,4,3,1,3,10,15),(68,4,3,1,3,10,15),(69,4,3,1,3,10,15),(70,4,3,1,3,10,15),(71,4,3,1,3,10,15),(72,4,3,1,3,10,15),(73,4,3,1,3,10,15),(74,4,3,1,3,10,15),(75,4,3,1,3,10,15),(76,4,3,1,3,10,15),(77,4,4,1,4,10,15),(78,4,4,1,4,10,15),(79,4,4,1,4,10,15),(80,4,4,1,4,10,15),(81,5,4,1,4,10,15),(82,5,4,1,4,10,15),(83,5,4,1,4,10,15),(84,5,4,1,4,10,15),(85,5,4,1,4,10,15),(86,5,4,1,4,10,15),(87,5,4,1,4,10,15),(88,5,4,1,4,10,15),(89,5,4,1,4,10,15),(90,5,4,1,4,10,15),(91,5,4,1,4,10,15),(92,5,4,1,4,10,15),(93,5,4,1,4,10,15),(94,5,4,1,4,10,15),(95,5,4,1,4,10,15),(96,5,4,1,4,10,15),(97,5,4,1,4,10,15),(98,5,4,1,4,10,15),(99,5,4,1,4,10,15),(100,5,4,1,4,10,15),(101,6,4,1,4,10,15),(102,6,4,1,4,10,15),(103,6,4,1,4,10,15),(104,6,4,1,4,10,15),(105,6,4,1,4,10,15),(106,6,4,1,4,10,15),(107,6,5,1,5,10,15),(108,6,5,1,5,10,15),(109,6,5,1,5,10,15),(110,6,5,1,5,10,15),(111,6,5,1,5,10,15),(112,6,5,1,5,10,15),(113,6,5,1,5,10,15),(114,6,5,1,5,10,15),(115,6,5,1,5,10,15),(116,6,5,1,5,10,15),(117,6,5,1,5,10,15),(118,6,5,1,5,10,15),(119,6,5,1,5,10,15),(120,6,5,1,5,10,15),(121,7,5,1,5,10,15),(122,7,5,1,5,10,15),(123,7,5,1,5,10,15),(124,7,5,1,5,10,15),(125,7,5,1,5,10,15),(126,7,5,1,5,10,15),(127,7,5,1,5,10,15),(128,7,6,1,6,10,15),(129,7,6,1,6,10,15),(130,7,6,1,6,10,15),(131,7,6,1,6,10,15),(132,7,6,1,6,10,15),(133,7,6,1,6,10,15),(134,7,6,1,6,10,15),(135,7,6,1,6,10,15),(136,7,6,1,6,10,15),(137,7,6,1,6,10,15),(138,7,6,1,6,10,15),(139,7,6,1,6,10,15),(140,7,6,1,6,10,15),(141,8,6,1,6,10,15),(142,8,6,1,6,10,15),(143,8,6,1,6,10,15),(144,8,6,1,6,10,15),(145,8,6,1,6,10,15),(146,8,6,1,6,10,15),(147,8,6,1,6,10,15),(148,8,6,1,6,10,15),(149,8,6,1,6,10,15),(150,8,6,1,6,10,15),(151,8,7,1,7,10,15),(152,8,7,1,7,10,15),(153,8,7,1,7,10,15),(154,8,7,1,7,10,15),(155,8,7,1,7,10,15),(156,8,7,1,7,10,15),(157,8,7,1,7,10,15),(158,8,7,1,7,10,15),(159,8,7,1,7,10,15),(160,8,7,1,7,10,15),(161,9,7,1,7,10,15),(162,9,7,1,7,10,15),(163,9,7,1,7,10,15),(164,9,7,1,7,10,15),(165,9,7,1,7,10,15),(166,9,7,1,7,10,15),(167,9,7,1,7,10,15),(168,9,7,1,7,10,15),(169,9,7,1,7,10,15),(170,9,7,1,7,10,15),(171,9,7,1,7,10,15),(172,9,7,1,7,10,15),(173,9,7,1,7,10,15),(174,9,7,1,7,10,15),(175,9,7,1,7,10,15),(176,9,7,1,7,10,15),(177,9,8,1,8,10,15),(178,9,8,1,8,10,15),(179,9,8,1,8,10,15),(180,9,8,1,8,10,15),(181,10,8,1,8,10,15),(182,10,8,1,8,10,15),(183,10,8,1,8,10,15),(184,10,8,1,8,10,15),(185,10,8,1,8,10,15),(186,10,8,1,8,10,15),(187,10,9,1,9,10,15),(188,10,9,1,9,10,15),(189,10,9,1,9,10,15),(190,10,9,1,9,10,15),(191,10,9,1,9,10,15),(192,10,9,1,9,10,15),(193,10,9,1,9,10,15),(194,10,9,1,9,10,15),(195,10,9,1,9,10,15),(196,10,9,1,9,10,15),(197,10,9,1,9,10,15),(198,10,9,1,9,10,15),(199,10,9,1,9,10,15),(200,10,9,1,9,10,15),(201,11,9,1,9,10,15),(202,11,9,1,9,10,15),(203,11,9,1,9,10,15),(204,11,9,1,9,10,15),(205,11,9,1,9,10,15),(206,11,9,1,9,10,15),(207,11,9,1,9,10,15),(208,11,10,1,10,10,15),(209,11,10,1,10,10,15),(210,11,10,1,10,10,15),(211,11,10,1,10,10,15),(212,11,10,1,10,10,15),(213,11,10,1,10,10,15),(214,11,10,1,10,10,15),(215,11,10,1,10,10,15),(216,11,10,1,10,10,15),(217,11,10,1,10,10,15),(218,11,10,1,10,10,15),(219,11,10,1,10,10,15),(220,11,10,1,10,10,15),(221,12,10,1,10,10,15),(222,12,11,1,11,10,15),(223,12,11,1,11,10,15),(224,12,11,1,11,10,15),(225,12,11,1,11,10,15),(226,12,11,1,11,10,15),(227,12,11,1,11,10,15),(228,12,11,1,11,10,15),(229,12,11,1,11,10,15),(230,12,11,1,11,10,15),(231,12,11,1,11,10,15),(232,12,11,1,11,10,15),(233,12,11,1,11,10,15),(234,12,11,1,11,10,15),(235,12,11,1,11,10,15),(236,12,12,1,12,10,15),(237,12,12,1,12,10,15),(238,12,12,1,12,10,15),(239,12,12,1,12,10,15),(240,12,12,1,12,10,15),(241,13,12,1,12,10,15),(242,13,12,1,12,10,15),(243,13,12,1,12,10,15),(244,13,12,1,12,10,15),(245,13,12,1,12,10,15),(246,13,12,1,12,10,15),(247,13,12,1,12,10,15),(248,13,12,1,12,10,15),(249,13,13,1,13,10,15),(250,13,13,1,13,10,15),(251,13,13,1,13,10,15),(252,13,13,1,13,10,15),(253,13,13,1,13,10,15),(254,13,13,1,13,10,15),(255,13,13,1,13,10,15),(256,13,14,1,14,10,15),(257,13,14,1,14,10,15),(258,13,14,1,14,10,15),(259,13,14,1,14,10,15),(260,13,14,1,14,10,15),(261,14,14,1,14,10,15),(262,14,15,1,15,10,15),(263,14,15,1,15,10,15),(264,14,15,1,15,10,15),(265,14,15,1,15,10,15),(266,14,15,1,15,10,15),(267,14,15,1,15,10,15),(268,14,16,1,16,10,15),(269,14,16,1,16,10,15),(270,14,16,1,16,10,15),(271,14,16,1,16,10,15),(272,14,16,1,16,10,15),(273,14,16,1,16,10,15),(274,14,16,1,16,10,15),(275,14,16,1,16,10,15),(276,14,16,1,16,10,15),(277,14,16,1,16,10,15),(278,14,16,1,16,10,15),(279,14,16,1,16,10,15),(280,14,16,1,16,10,15),(281,15,16,1,16,10,15),(282,15,17,1,17,10,15),(283,15,17,1,17,10,15),(284,15,17,1,17,10,15),(285,15,17,1,17,10,15),(286,15,17,1,17,10,15),(287,15,17,1,17,10,15),(288,15,17,1,17,10,15),(289,15,17,1,17,10,15),(290,15,17,1,17,10,15),(291,15,17,1,17,10,15),(292,15,17,1,17,10,15),(293,15,17,1,17,10,15),(294,15,18,1,18,10,15),(295,15,18,1,18,10,15),(296,15,18,1,18,10,15),(297,15,18,1,18,10,15),(298,15,18,1,18,10,15),(299,15,18,1,18,10,15),(300,15,18,1,18,10,15),(301,16,18,1,18,10,15),(302,16,18,1,18,10,15),(303,16,18,1,18,10,15),(304,16,18,1,18,10,15),(305,16,19,1,19,10,15),(306,16,19,1,19,10,15),(307,16,19,1,19,10,15),(308,16,19,1,19,10,15),(309,16,19,1,19,10,15),(310,16,19,1,19,10,15),(311,16,19,1,19,10,15),(312,16,19,1,19,10,15),(313,16,20,1,20,10,15),(314,16,20,1,20,10,15),(315,16,20,1,20,10,15),(316,16,20,1,20,10,15),(317,16,20,1,20,10,15),(318,16,20,1,20,10,15),(319,16,20,1,20,10,15),(320,16,20,1,20,10,15),(321,17,20,1,20,10,15),(322,17,21,1,21,10,15),(323,17,21,1,21,10,15),(324,17,21,1,21,10,15),(325,17,21,1,21,10,15),(326,17,21,1,21,10,15),(327,17,21,1,21,10,15),(328,17,21,1,21,10,15),(329,17,21,1,21,10,15),(330,17,21,1,21,10,15),(331,17,21,1,21,10,15),(332,17,22,1,22,10,15),(333,17,22,1,22,10,15),(334,17,22,1,22,10,15),(335,17,22,1,22,10,15),(336,17,22,1,22,10,15),(337,17,22,1,22,10,15),(338,17,22,1,22,10,15),(339,17,22,1,22,10,15),(340,17,22,1,22,10,15),(341,18,22,1,22,10,15),(342,18,23,1,23,10,15),(343,18,23,1,23,10,15),(344,18,23,1,23,10,15),(345,18,23,1,23,10,15),(346,18,23,1,23,10,15),(347,18,23,1,23,10,15),(348,18,23,1,23,10,15),(349,18,23,1,23,10,15),(350,18,24,1,24,10,15),(351,18,24,1,24,10,15),(352,18,24,1,24,10,15),(353,18,24,1,24,10,15),(354,18,24,1,24,10,15),(355,18,24,1,24,10,15),(356,18,24,1,24,10,15),(357,18,24,1,24,10,15),(358,18,24,1,24,10,15),(359,18,24,1,24,10,15),(360,18,25,1,25,10,15),(361,19,25,1,25,10,15),(362,19,25,1,25,10,15),(363,19,25,1,25,10,15),(364,19,25,1,25,10,15),(365,19,25,1,25,10,15),(366,19,25,1,25,10,15),(367,19,26,1,26,10,15),(368,19,26,1,26,10,15),(369,19,26,1,26,10,15),(370,19,26,1,26,10,15),(371,19,26,1,26,10,15),(372,19,26,1,26,10,15),(373,19,26,1,26,10,15),(374,19,26,1,26,10,15),(375,19,26,1,26,10,15),(376,19,26,1,26,10,15),(377,19,27,1,27,10,15),(378,19,27,1,27,10,15),(379,19,27,1,27,10,15),(380,19,27,1,27,10,15),(381,20,27,1,27,10,15),(382,20,27,1,27,10,15),(383,20,27,1,27,10,15),(384,20,27,1,27,10,15),(385,20,27,1,27,10,15),(386,20,28,1,28,10,15),(387,20,28,1,28,10,15),(388,20,28,1,28,10,15),(389,20,28,1,28,10,15),(390,20,28,1,28,10,15),(391,20,28,1,28,10,15),(392,20,28,1,28,10,15),(393,20,28,1,28,10,15),(394,20,28,1,28,10,15),(395,20,28,1,28,10,15),(396,20,28,1,28,10,15),(397,20,29,1,29,10,15),(398,20,29,1,29,10,15),(399,20,29,1,29,10,15),(400,20,29,1,29,10,15),(401,21,29,1,29,10,15),(402,21,29,1,29,10,15),(403,21,29,1,29,10,15),(404,21,29,1,29,10,15),(405,21,30,1,30,10,15),(406,21,30,1,30,10,15),(407,21,30,1,30,10,15),(408,21,30,1,30,10,15),(409,21,30,1,30,10,15),(410,21,30,1,30,10,15),(411,21,31,1,31,10,15),(412,21,31,1,31,10,15),(413,21,31,1,31,10,15),(414,21,31,1,31,10,15),(415,21,32,1,32,10,15),(416,21,32,1,32,10,15),(417,21,32,1,32,10,15),(418,21,33,1,33,10,15),(419,21,33,1,33,10,15),(420,21,33,1,33,10,15),(421,22,33,1,33,10,15),(422,22,33,1,33,10,15),(423,22,33,1,33,10,15),(424,22,33,1,33,10,15),(425,22,33,1,33,10,15),(426,22,33,1,33,10,15),(427,22,33,1,33,10,15),(428,22,34,1,34,10,15),(429,22,34,1,34,10,15),(430,22,34,1,34,10,15),(431,22,34,1,34,10,15),(432,22,34,1,34,10,15),(433,22,34,1,34,10,15),(434,22,34,1,34,10,15),(435,22,35,1,35,10,15),(436,22,35,1,35,10,15),(437,22,35,1,35,10,15),(438,22,35,1,35,10,15),(439,22,35,1,35,10,15),(440,22,35,1,35,10,15),(441,23,36,1,36,10,15),(442,23,36,1,36,10,15),(443,23,36,1,36,10,15),(444,23,36,1,36,10,15),(445,23,36,1,36,10,15),(446,23,37,1,37,10,15),(447,23,37,1,37,10,15),(448,23,37,1,37,10,15),(449,23,37,1,37,10,15),(450,23,37,1,37,10,15),(451,23,37,1,37,10,15),(452,23,37,1,37,10,15),(453,23,38,1,38,10,15),(454,23,38,1,38,10,15),(455,23,38,1,38,10,15),(456,23,38,1,38,10,15),(457,23,38,1,38,10,15),(458,23,38,1,38,10,15),(459,23,39,1,39,10,15),(460,23,39,1,39,10,15),(461,24,39,1,39,10,15),(462,24,39,1,39,10,15),(463,24,39,1,39,10,15),(464,24,39,1,39,10,15),(465,24,39,1,39,10,15),(466,24,39,1,39,10,15),(467,24,39,1,39,10,15),(468,24,40,1,40,10,15),(469,24,40,1,40,10,15),(470,24,40,1,40,10,15),(471,24,40,1,40,10,15),(472,24,40,1,40,10,15),(473,24,40,1,40,10,15),(474,24,40,1,40,10,15),(475,24,40,1,40,10,15),(476,24,40,1,40,10,15),(477,24,41,1,41,10,15),(478,24,41,1,41,10,15),(479,24,41,1,41,10,15),(480,24,41,1,41,10,15),(481,25,41,1,41,10,15),(482,25,41,1,41,10,15),(483,25,42,1,42,10,15),(484,25,42,1,42,10,15),(485,25,42,1,42,10,15),(486,25,42,1,42,10,15),(487,25,42,1,42,10,15),(488,25,42,1,42,10,15),(489,25,42,1,42,10,15),(490,25,43,1,43,10,15),(491,25,43,1,43,10,15),(492,25,43,1,43,10,15),(493,25,43,1,43,10,15),(494,25,43,1,43,10,15),(495,25,43,1,43,10,15),(496,25,44,1,44,10,15),(497,25,44,1,44,10,15),(498,25,44,1,44,10,15),(499,25,45,1,45,10,15),(500,25,45,1,45,10,15),(501,26,45,1,45,10,15),(502,26,45,1,45,10,15),(503,26,46,1,46,10,15),(504,26,46,1,46,10,15),(505,26,46,1,46,10,15),(506,26,46,1,46,10,15),(507,26,47,1,47,10,15),(508,26,47,1,47,10,15),(509,26,47,1,47,10,15),(510,26,47,1,47,10,15),(511,26,48,1,48,10,15),(512,26,48,1,48,10,15),(513,26,48,1,48,10,15),(514,26,48,1,48,10,15),(515,26,48,1,48,10,15),(516,26,49,1,49,10,15),(517,26,49,1,49,10,15),(518,26,50,1,50,10,15),(519,26,50,1,50,10,15),(520,26,50,1,50,10,15),(521,27,51,1,51,10,15),(522,27,51,1,51,10,15),(523,27,51,1,51,10,15),(524,27,52,1,52,10,15),(525,27,52,1,52,10,15),(526,27,53,1,53,10,15),(527,27,53,1,53,10,15),(528,27,53,1,53,10,15),(529,27,54,1,54,10,15),(530,27,54,1,54,10,15),(531,27,54,1,54,10,15),(532,27,55,1,55,10,15),(533,27,55,1,55,10,15),(534,27,55,1,55,10,15),(535,27,56,1,56,10,15),(536,27,56,1,56,10,15),(537,27,56,1,56,10,15),(538,27,57,1,57,10,15),(539,27,57,1,57,10,15),(540,27,57,1,57,10,15),(541,28,57,1,57,10,15),(542,28,58,1,58,10,15),(543,28,58,1,58,10,15),(544,28,58,1,58,10,15),(545,28,58,1,58,10,15),(546,28,59,1,59,10,15),(547,28,59,1,59,10,15),(548,28,59,1,59,10,15),(549,28,60,1,60,10,15),(550,28,60,1,60,10,15),(551,28,60,1,60,10,15),(552,28,61,1,61,10,15),(553,28,62,1,62,10,15),(554,28,62,1,62,10,15),(555,28,63,1,63,10,15),(556,28,64,1,64,10,15),(557,28,64,1,64,10,15),(558,28,65,1,65,10,15),(559,28,65,1,65,10,15),(560,28,66,1,66,10,15),(561,29,66,1,66,10,15),(562,29,67,1,67,10,15),(563,29,67,1,67,10,15),(564,29,67,1,67,10,15),(565,29,68,1,68,10,15),(566,29,68,1,68,10,15),(567,29,69,1,69,10,15),(568,29,69,1,69,10,15),(569,29,70,1,70,10,15),(570,29,70,1,70,10,15),(571,29,71,1,71,10,15),(572,29,72,1,72,10,15),(573,29,72,1,72,10,15),(574,29,73,1,73,10,15),(575,29,73,1,73,10,15),(576,29,74,1,74,10,15),(577,29,74,1,74,10,15),(578,29,75,1,75,10,15),(579,29,76,1,76,10,15),(580,29,76,1,76,10,15),(581,30,77,1,77,10,15),(582,30,78,1,78,10,15),(583,30,78,1,78,10,15),(584,30,79,1,79,10,15),(585,30,80,1,80,10,15),(586,30,80,1,80,10,15),(587,30,82,1,82,10,15),(588,30,83,1,83,10,15),(589,30,83,1,83,10,15),(590,30,84,1,84,10,15),(591,30,86,1,86,10,15),(592,30,87,1,87,10,15),(593,30,88,1,88,10,15),(594,30,89,1,89,10,15),(595,30,90,1,90,10,15),(596,30,92,1,92,10,15),(597,30,95,1,95,10,15),(598,30,97,1,97,10,15),(599,30,98,1,98,10,15),(600,30,100,1,100,10,15),(601,30,103,1,103,10,15),(602,30,106,1,106,10,15),(603,30,109,1,109,10,15),(604,30,112,1,112,10,15);
/*!40000 ALTER TABLE `mushaf_madinah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mushaf_surahs`
--

DROP TABLE IF EXISTS `mushaf_surahs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `mushaf_surahs` (
  `surah_number` int(11) NOT NULL,
  `name_ar` varchar(100) NOT NULL,
  `name_id` varchar(100) NOT NULL,
  `verses_count` int(11) NOT NULL,
  `start_page` int(11) NOT NULL,
  `end_page` int(11) NOT NULL,
  PRIMARY KEY (`surah_number`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mushaf_surahs`
--

LOCK TABLES `mushaf_surahs` WRITE;
/*!40000 ALTER TABLE `mushaf_surahs` DISABLE KEYS */;
INSERT INTO `mushaf_surahs` VALUES (1,'Ïº┘ä┘üÏºÏ¬Ï¡Ï®','Al-Fatihah',7,1,1),(2,'Ïº┘äÏ¿┘éÏ▒Ï®','Al-Baqarah',286,2,49),(3,'Ïó┘ä Ï╣┘àÏ▒Ïº┘å','Ali Imran',200,50,76),(4,'Ïº┘ä┘åÏ│ÏºÏí','An-Nisa',176,77,106),(5,'Ïº┘ä┘àÏºÏªÏ»Ï®','Al-Maidah',120,106,127),(6,'Ïº┘äÏú┘åÏ╣Ïº┘à','Al-Anam',165,128,150),(7,'Ïº┘äÏúÏ╣Ï▒Ïº┘ü','Al-Araf',206,151,176),(8,'Ïº┘äÏú┘å┘üÏº┘ä','Al-Anfal',75,177,186),(9,'Ïº┘äÏ¬┘êÏ¿Ï®','At-Taubah',129,187,207),(10,'┘è┘ê┘åÏ│','Yunus',109,208,221),(11,'┘ç┘êÏ»','Hud',123,221,235),(12,'┘è┘êÏ│┘ü','Yusuf',111,235,248),(13,'Ïº┘äÏ▒Ï╣Ï»','Ar-Rad',43,249,255),(14,'ÏÑÏ¿Ï▒Ïº┘ç┘è┘à','Ibrahim',52,255,261),(15,'Ïº┘äÏ¡Ï¼Ï▒','Al-Hijr',99,262,267),(16,'Ïº┘ä┘åÏ¡┘ä','An-Nahl',128,267,281),(17,'Ïº┘äÏÑÏ│Ï▒ÏºÏí','Al-Isra',111,282,293),(18,'Ïº┘ä┘â┘ç┘ü','Al-Kahf',110,293,304),(19,'┘àÏ▒┘è┘à','Maryam',98,305,312),(20,'ÏÀ┘ç','Taha',135,312,321),(21,'Ïº┘äÏú┘åÏ¿┘èÏºÏí','Al-Anbiya',112,322,331),(22,'Ïº┘äÏ¡Ï¼','Al-Hajj',78,332,341),(23,'Ïº┘ä┘àÏñ┘à┘å┘ê┘å','Al-Muminun',118,342,349),(24,'Ïº┘ä┘å┘êÏ▒','An-Nur',64,350,359),(25,'Ïº┘ä┘üÏ▒┘éÏº┘å','Al-Furqan',77,359,366),(26,'Ïº┘äÏ┤Ï╣Ï▒ÏºÏí','Asy-Syuara',227,367,376),(27,'Ïº┘ä┘å┘à┘ä','An-Naml',93,377,385),(28,'Ïº┘ä┘éÏÁÏÁ','Al-Qasas',88,385,396),(29,'Ïº┘äÏ╣┘å┘âÏ¿┘êÏ¬','Al-Ankabut',69,396,404),(30,'Ïº┘äÏ▒┘ê┘à','Ar-Rum',60,404,410),(31,'┘ä┘é┘àÏº┘å','Luqman',34,411,414),(32,'Ïº┘äÏ│Ï¼Ï»Ï®','As-Sajdah',30,415,417),(33,'Ïº┘äÏúÏ¡Ï▓ÏºÏ¿','Al-Ahzab',73,418,427),(34,'Ï│Ï¿Ïú','Saba',54,428,434),(35,'┘üÏºÏÀÏ▒','Fatir',45,434,440),(36,'┘èÏ│','Yasin',83,440,445),(37,'Ïº┘äÏÁÏº┘üÏºÏ¬','As-Saffat',182,446,452),(38,'ÏÁ','Sad',88,453,458),(39,'Ïº┘äÏ▓┘àÏ▒','Az-Zumar',75,458,467),(40,'Ï║Ïº┘üÏ▒','Ghafir',85,467,476),(41,'┘üÏÁ┘äÏ¬','Fussilat',54,477,482),(42,'Ïº┘äÏ┤┘êÏ▒┘ë','Asy-Syura',53,483,489),(43,'Ïº┘äÏ▓Ï«Ï▒┘ü','Az-Zukhruf',89,489,495),(44,'Ïº┘äÏ»Ï«Ïº┘å','Ad-Dukhan',59,496,498),(45,'Ïº┘äÏ¼ÏºÏ½┘èÏ®','Al-Jatsiyah',37,499,502),(46,'Ïº┘äÏúÏ¡┘éÏº┘ü','Al-Ahqaf',35,502,506),(47,'┘àÏ¡┘àÏ»','Muhammad',38,507,510),(48,'Ïº┘ä┘üÏ¬Ï¡','Al-Fath',29,511,515),(49,'Ïº┘äÏ¡Ï¼Ï▒ÏºÏ¬','Al-Hujurat',18,515,517),(50,'┘é','Qaf',45,518,520),(51,'Ïº┘äÏ░ÏºÏ▒┘èÏºÏ¬','Adz-Dzariyat',60,520,523),(52,'Ïº┘äÏÀ┘êÏ▒','At-Tur',49,523,525),(53,'Ïº┘ä┘åÏ¼┘à','An-Najm',62,526,528),(54,'Ïº┘ä┘é┘àÏ▒','Al-Qamar',55,528,531),(55,'Ïº┘äÏ▒Ï¡┘à┘å','Ar-Rahman',78,531,534),(56,'Ïº┘ä┘êÏº┘éÏ╣Ï®','Al-Waqiah',96,534,537),(57,'Ïº┘äÏ¡Ï»┘èÏ»','Al-Hadid',29,537,541),(58,'Ïº┘ä┘àÏ¼ÏºÏ»┘äÏ®','Al-Mujadilah',22,542,545),(59,'Ïº┘äÏ¡Ï┤Ï▒','Al-Hasyr',24,545,548),(60,'Ïº┘ä┘à┘àÏ¬Ï¡┘åÏ®','Al-Mumtahanah',13,549,551),(61,'Ïº┘äÏÁ┘ü','As-Saff',14,551,552),(62,'Ïº┘äÏ¼┘àÏ╣Ï®','Al-Jumuah',11,553,554),(63,'Ïº┘ä┘à┘åÏº┘ü┘é┘ê┘å','Al-Munafiqun',11,554,555),(64,'Ïº┘äÏ¬Ï║ÏºÏ¿┘å','At-Taghabun',18,556,557),(65,'Ïº┘äÏÀ┘äÏº┘é','At-Talaq',12,558,559),(66,'Ïº┘äÏ¬Ï¡Ï▒┘è┘à','At-Tahrim',12,560,561),(67,'Ïº┘ä┘à┘ä┘â','Al-Mulk',30,562,564),(68,'Ïº┘ä┘é┘ä┘à','Al-Qalam',52,564,566),(69,'Ïº┘äÏ¡Ïº┘éÏ®','Al-Haqqah',52,566,568),(70,'Ïº┘ä┘àÏ╣ÏºÏ▒Ï¼','Al-Maarij',44,568,570),(71,'┘å┘êÏ¡','Nuh',28,570,571),(72,'Ïº┘äÏ¼┘å','Al-Jinn',28,572,573),(73,'Ïº┘ä┘àÏ▓┘à┘ä','Al-Muzzammil',20,574,575),(74,'Ïº┘ä┘àÏ»Ï½Ï▒','Al-Muddatstsir',56,575,577),(75,'Ïº┘ä┘é┘èÏº┘àÏ®','Al-Qiyamah',40,577,578),(76,'Ïº┘äÏÑ┘åÏ│Ïº┘å','Al-Insan',31,578,580),(77,'Ïº┘ä┘àÏ▒Ï│┘äÏºÏ¬','Al-Mursalat',50,580,581),(78,'Ïº┘ä┘åÏ¿Ïú','An-Naba',40,582,583),(79,'Ïº┘ä┘åÏºÏ▓Ï╣ÏºÏ¬','An-Naziat',46,583,584),(80,'Ï╣Ï¿Ï│','Abasa',42,585,586),(81,'Ïº┘äÏ¬┘â┘ê┘èÏ▒','At-Takwir',29,586,586),(82,'Ïº┘äÏº┘å┘üÏÀÏºÏ▒','Al-Infitar',19,587,587),(83,'Ïº┘ä┘àÏÀ┘ü┘ü┘è┘å','Al-Muthaffifin',36,587,589),(84,'Ïº┘äÏº┘åÏ┤┘éÏº┘é','Al-Insyiqaq',25,589,590),(85,'Ïº┘äÏ¿Ï▒┘êÏ¼','Al-Buruj',22,590,590),(86,'Ïº┘äÏÀÏºÏ▒┘é','At-Thariq',17,591,591),(87,'Ïº┘äÏúÏ╣┘ä┘ë','Al-Ala',19,591,592),(88,'Ïº┘äÏ║ÏºÏ┤┘èÏ®','Al-Ghasyiyah',26,592,593),(89,'Ïº┘ä┘üÏ¼Ï▒','Al-Fajr',30,593,594),(90,'Ïº┘äÏ¿┘äÏ»','Al-Balad',20,594,595),(91,'Ïº┘äÏ┤┘àÏ│','Asy-Syams',15,595,595),(92,'Ïº┘ä┘ä┘è┘ä','Al-Lail',21,595,596),(93,'Ïº┘äÏÂÏ¡┘ë','Ad-Dhuha',11,596,596),(94,'Ïº┘äÏ┤Ï▒Ï¡','Asy-Syarh',8,596,596),(95,'Ïº┘äÏ¬┘è┘å','At-Tin',8,597,597),(96,'Ïº┘äÏ╣┘ä┘é','Al-Alaq',19,597,597),(97,'Ïº┘ä┘éÏ»Ï▒','Al-Qadr',5,598,598),(98,'Ïº┘äÏ¿┘è┘åÏ®','Al-Bayyinah',8,598,599),(99,'Ïº┘äÏ▓┘äÏ▓┘äÏ®','Az-Zalzalah',8,599,599),(100,'Ïº┘äÏ╣ÏºÏ»┘èÏºÏ¬','Al-Adiyat',11,599,600),(101,'Ïº┘ä┘éÏºÏ▒Ï╣Ï®','Al-Qariah',11,600,600),(102,'Ïº┘äÏ¬┘âÏºÏ½Ï▒','At-Takatsur',8,600,600),(103,'Ïº┘äÏ╣ÏÁÏ▒','Al-Asr',3,601,601),(104,'Ïº┘ä┘ç┘àÏ▓Ï®','Al-Humazah',9,601,601),(105,'Ïº┘ä┘ü┘è┘ä','Al-Fil',5,601,601),(106,'┘éÏ▒┘èÏ┤','Quraisy',4,602,602),(107,'Ïº┘ä┘àÏºÏ╣┘ê┘å','Al-Maun',7,602,602),(108,'Ïº┘ä┘â┘êÏ½Ï▒','Al-Kautsar',3,602,602),(109,'Ïº┘ä┘âÏº┘üÏ▒┘ê┘å','Al-Kafirun',6,603,603),(110,'Ïº┘ä┘åÏÁÏ▒','An-Nasr',3,603,603),(111,'Ïº┘ä┘àÏ│Ï»','Al-Lahab',5,603,603),(112,'Ïº┘äÏÑÏ«┘äÏºÏÁ','Al-Ikhlas',4,604,604),(113,'Ïº┘ä┘ü┘ä┘é','Al-Falaq',5,604,604),(114,'Ïº┘ä┘åÏºÏ│','An-Nas',6,604,604);
/*!40000 ALTER TABLE `mushaf_surahs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `title` varchar(150) NOT NULL,
  `message` text NOT NULL,
  `type` varchar(50) DEFAULT 'info',
  `url` varchar(255) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_notif_user` (`user_id`,`is_read`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `panitia_lomba_assignments`
--

DROP TABLE IF EXISTS `panitia_lomba_assignments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `panitia_lomba_assignments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `event_type` enum('OSN','O2SN','FLS2N') NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `status` enum('Aktif','Selesai') NOT NULL DEFAULT 'Aktif',
  `student_ids` text DEFAULT NULL,
  `sk_number` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `teacher_id` (`teacher_id`),
  CONSTRAINT `panitia_lomba_assignments_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `panitia_lomba_assignments_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `panitia_lomba_assignments_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `panitia_lomba_assignments`
--

LOCK TABLES `panitia_lomba_assignments` WRITE;
/*!40000 ALTER TABLE `panitia_lomba_assignments` DISABLE KEYS */;
/*!40000 ALTER TABLE `panitia_lomba_assignments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prestasi_siswa`
--

DROP TABLE IF EXISTS `prestasi_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `prestasi_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `category` enum('OSN','O2SN','FLS2N','Swasta') NOT NULL,
  `event_name` varchar(150) NOT NULL,
  `field_name` varchar(100) DEFAULT NULL,
  `rank_position` varchar(50) NOT NULL,
  `level` varchar(50) NOT NULL,
  `certificate_path` varchar(255) DEFAULT NULL,
  `recorded_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `student_id` (`student_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `prestasi_siswa_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `prestasi_siswa_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `prestasi_siswa_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestasi_siswa`
--

LOCK TABLES `prestasi_siswa` WRITE;
/*!40000 ALTER TABLE `prestasi_siswa` DISABLE KEYS */;
/*!40000 ALTER TABLE `prestasi_siswa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_order_items`
--

DROP TABLE IF EXISTS `purchase_order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_id` int(11) NOT NULL,
  `item_no` int(11) NOT NULL,
  `description` varchar(255) NOT NULL,
  `specs` varchar(255) DEFAULT NULL,
  `rapbs` enum('YA','TIDAK') DEFAULT 'YA',
  `unit_of_measure` varchar(50) DEFAULT 'unit',
  `stock_position` int(11) DEFAULT 0,
  `quantity_ordered` int(11) NOT NULL DEFAULT 1,
  `procurement_time` varchar(100) DEFAULT NULL,
  `cost_burden_unit` varchar(100) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `is_consumable` tinyint(1) DEFAULT 0,
  `category_id` int(11) DEFAULT NULL,
  `status` enum('Pending','Terpenuhi','Batal') DEFAULT 'Pending',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_poi_po` (`po_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_order_items`
--

LOCK TABLES `purchase_order_items` WRITE;
/*!40000 ALTER TABLE `purchase_order_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_order_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `purchase_orders`
--

DROP TABLE IF EXISTS `purchase_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `purchase_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `po_number` varchar(100) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `request_date` date NOT NULL,
  `reason` text NOT NULL,
  `procurement_division` varchar(100) DEFAULT 'Divisi Pengadaan Barang',
  `delivery_date` date DEFAULT NULL,
  `delivery_location` varchar(255) NOT NULL,
  `special_notes` text DEFAULT NULL,
  `needs_contractor` tinyint(1) DEFAULT 0,
  `status` enum('Draft','Menunggu_Kepsek','Menunggu_Direktur_Pengadaan','Menunggu_Direktur_Sarpras','Disetujui','Dalam_Pengerjaan','Selesai','Ditolak') DEFAULT 'Menunggu_Kepsek',
  `requested_by_name` varchar(150) NOT NULL,
  `requested_by_title` varchar(100) DEFAULT 'Sarpras',
  `requested_at` datetime DEFAULT current_timestamp(),
  `approved_kepsek_id` int(11) DEFAULT NULL,
  `approved_kepsek_name` varchar(150) DEFAULT NULL,
  `approved_kepsek_at` datetime DEFAULT NULL,
  `kepsek_notes` text DEFAULT NULL,
  `approved_pengadaan_id` int(11) DEFAULT NULL,
  `approved_pengadaan_name` varchar(150) DEFAULT NULL,
  `approved_pengadaan_at` datetime DEFAULT NULL,
  `pengadaan_notes` text DEFAULT NULL,
  `approved_sarpras_dir_id` int(11) DEFAULT NULL,
  `approved_sarpras_dir_name` varchar(150) DEFAULT NULL,
  `approved_sarpras_dir_at` datetime DEFAULT NULL,
  `sarpras_dir_notes` text DEFAULT NULL,
  `work_started_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `delivery_letter_file` varchar(255) DEFAULT NULL,
  `work_documentation_file` varchar(255) DEFAULT NULL,
  `realization_notes` text DEFAULT NULL,
  `realized_by_user_id` int(11) DEFAULT NULL,
  `auto_stocked_at` datetime DEFAULT NULL,
  `rejection_reason` text DEFAULT NULL,
  `rejected_by_user_id` int(11) DEFAULT NULL,
  `rejected_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `po_number` (`po_number`),
  KEY `idx_po_unit` (`unit_id`),
  KEY `idx_po_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `purchase_orders`
--

LOCK TABLES `purchase_orders` WRITE;
/*!40000 ALTER TABLE `purchase_orders` DISABLE KEYS */;
/*!40000 ALTER TABLE `purchase_orders` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `display_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=68 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'operator_yayasan','Operator Yayasan','Akses penuh yayasan, kelola sekolah, pegawai, mutasi, master AKFI, backup'),(2,'direktur_pendidikan','Direktur Pendidikan','Dashboard yayasan, rekap AKFI, Atasan Berikut / HR'),(3,'deputi_pendidikan','Deputi Direktur Pendidikan','Approval administrasi KS, deadline, SPMB target, AKFI KS, sikap KS'),(4,'operator_sekolah','Operator Sekolah','Kelola siswa, kelas, mapel, akun ortu/guru, persetujuan pindah TA, SPMB'),(5,'tutor_siswa','Tutor Siswa','Uji tahfidz siswa, penentuan lulus juz, input nilai bulanan tahfidz guru'),(6,'tutor_sebaya','Tutor Sebaya','Assign halaqah guru, target hafalan guru, input nilai tahfidz guru bulanan'),(7,'tata_usaha','Tata Usaha','Tagihan manual siswa, pengajuan penagihan, upload berkas keuangan, checklist braja'),(8,'cleaning_service','Cleaning Service','Checklist harian, piket bulanan, gotong royong Sabtu, kepuasan kebersihan'),(9,'security','Security','Checklist laporan harian per hari ini, patroli, insiden kebobolan/gagalkan kriminalitas'),(10,'waka_kurikulum','Wakil Kurikulum','Pencatatan pelanggaran kehadiran guru, absen kajian guru, jadwal supervisi, deadline admin'),(11,'guru_supervisor','Guru Supervisor','Penilaian supervisi kelas (rubrik A/B/C), cetak instrumen'),(12,'waka_kesiswaan','Wakil Kesiswaan','Kehadiran, kajian, verifikasi prestasi lomba siswa'),(13,'koor_tahfidz','Koordinator Tahfidz','Assign halaqah siswa, rekap tuntas siswa, baseline 3x setahun, cetak rapor tahfidz'),(14,'walas','Guru Wali Kelas','Nilai bulanan, absensi siswa, leger, rapor bulanan, PTK, rekomendasi kenaikan/alumni'),(15,'guru_mapel','Guru Mapel Umum','Nilai bulanan, KKM, Best Practice, administrasi perangkat 8 item'),(16,'guru_diniyah','Guru Mapel Diniyah','Nilai diniyah bulanan, Best Practice, administrasi perangkat 8 item'),(17,'guru_tahfidz','Guru Tahfidz','Input hafalan siswa bulanan (kalkulator madinah), pengajuan ujian tutor siswa'),(18,'orang_tua','Orang Tua','Unduh rapor bulanan/tahfidz/diniyah/gabungan, survei ortu 53 butir, cek tagihan'),(19,'panitia_lomba','Ketua Panitia Lomba','Input siswa juara lomba OSN/O2SN/FLS2N tingkat kota/provinsi/nasional'),(20,'kepala_sekolah','Kepala Sekolah','Approval admin guru/TU, rekap hafalan/kegiatan/tunggakan, absensi TU/CS/Security, sikap guru'),(21,'siswa','Siswa (SMP/SMA)','Akses siswa untuk survei kepuasan pembelajaran'),(64,'sarpras','Sarpras','Sarpras Unit Sekolah: pendataan & penghapusan aset, PO barang, berita acara scrap'),(65,'direktur_pengadaan','Direktur Pengadaan','Direktur Pengadaan Yayasan: verifikasi & persetujuan PO pengadaan barang'),(66,'direktur_sarpras','Direktur Sarpras','Direktur Sarpras Yayasan: approval PO kontraktor/pengerjaan lanjutan & tata kelola sarpras'),(67,'guru_mapel_kbtk','Guru Mapel KB/TK',NULL);
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schedule_time_slots`
--

DROP TABLE IF EXISTS `schedule_time_slots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `schedule_time_slots` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `day` enum('Senin','Selasa','Rabu','Kamis','Jumat','Sabtu') NOT NULL DEFAULT 'Senin',
  `jam_ke` int(11) NOT NULL,
  `time_start` varchar(10) NOT NULL,
  `time_end` varchar(10) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `is_break` tinyint(1) DEFAULT 0,
  `break_type` enum('none','upacara','istirahat','sholat','kegiatan') DEFAULT 'none',
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_slot_unit_day_jam` (`unit_id`,`academic_year_id`,`day`,`jam_ke`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `schedule_time_slots_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `schedule_time_slots_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schedule_time_slots`
--

LOCK TABLES `schedule_time_slots` WRITE;
/*!40000 ALTER TABLE `schedule_time_slots` DISABLE KEYS */;
/*!40000 ALTER TABLE `schedule_time_slots` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scrap_report_items`
--

DROP TABLE IF EXISTS `scrap_report_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scrap_report_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `scrap_report_id` int(11) NOT NULL,
  `asset_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `quantity_text` varchar(100) DEFAULT NULL,
  `responsible_person` varchar(150) NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_sri_report` (`scrap_report_id`),
  KEY `idx_sri_asset` (`asset_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scrap_report_items`
--

LOCK TABLES `scrap_report_items` WRITE;
/*!40000 ALTER TABLE `scrap_report_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `scrap_report_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scrap_reports`
--

DROP TABLE IF EXISTS `scrap_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `scrap_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report_number` varchar(100) NOT NULL,
  `form_code` varchar(50) DEFAULT 'F30',
  `unit_id` int(11) NOT NULL,
  `report_date` date NOT NULL,
  `day_name` varchar(30) NOT NULL,
  `reason` text NOT NULL,
  `status` enum('Draft','Disetujui','Selesai') DEFAULT 'Disetujui',
  `taken_by_name` varchar(150) DEFAULT NULL,
  `taken_by_date` date DEFAULT NULL,
  `prepared_by_name` varchar(150) NOT NULL,
  `prepared_by_title` varchar(100) DEFAULT 'Sarpras',
  `approved_by_name` varchar(150) NOT NULL,
  `approved_by_title` varchar(100) DEFAULT 'Deputi Direktur AIS',
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `report_number` (`report_number`),
  KEY `idx_scrap_unit` (`unit_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scrap_reports`
--

LOCK TABLES `scrap_reports` WRITE;
/*!40000 ALTER TABLE `scrap_reports` DISABLE KEYS */;
/*!40000 ALTER TABLE `scrap_reports` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_incidents`
--

DROP TABLE IF EXISTS `security_incidents`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_incidents` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_time` time NOT NULL,
  `category` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `action_taken` text NOT NULL,
  `is_prevented` tinyint(1) DEFAULT 0,
  `is_breach` tinyint(1) DEFAULT 0,
  `rating_response` int(11) DEFAULT 5,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `security_incidents_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_incidents`
--

LOCK TABLES `security_incidents` WRITE;
/*!40000 ALTER TABLE `security_incidents` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_incidents` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `security_patrols`
--

DROP TABLE IF EXISTS `security_patrols`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `security_patrols` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `patrol_date` date NOT NULL,
  `schedule_time` time NOT NULL,
  `actual_time` time NOT NULL,
  `location` varchar(150) NOT NULL,
  `findings` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `security_patrols_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `security_patrols_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_patrols`
--

LOCK TABLES `security_patrols` WRITE;
/*!40000 ALTER TABLE `security_patrols` DISABLE KEYS */;
/*!40000 ALTER TABLE `security_patrols` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spmb_registrations`
--

DROP TABLE IF EXISTS `spmb_registrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spmb_registrations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `reg_number` varchar(50) NOT NULL,
  `student_name` varchar(150) NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `parent_name` varchar(150) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `previous_school` varchar(150) DEFAULT NULL,
  `status` enum('Terdaftar','Diterima','Ditolak') DEFAULT 'Terdaftar',
  `converted_student_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `reg_number` (`reg_number`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `converted_student_id` (`converted_student_id`),
  CONSTRAINT `spmb_registrations_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `spmb_registrations_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `spmb_registrations_ibfk_3` FOREIGN KEY (`converted_student_id`) REFERENCES `students` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spmb_registrations`
--

LOCK TABLES `spmb_registrations` WRITE;
/*!40000 ALTER TABLE `spmb_registrations` DISABLE KEYS */;
/*!40000 ALTER TABLE `spmb_registrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `spmb_targets`
--

DROP TABLE IF EXISTS `spmb_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `spmb_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `target_count` int(11) NOT NULL DEFAULT 100,
  `actual_count` int(11) NOT NULL DEFAULT 0,
  `set_by_deputi_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_spmb_target` (`unit_id`,`academic_year_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `spmb_targets_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `spmb_targets_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spmb_targets`
--

LOCK TABLES `spmb_targets` WRITE;
/*!40000 ALTER TABLE `spmb_targets` DISABLE KEYS */;
/*!40000 ALTER TABLE `spmb_targets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_attendance`
--

DROP TABLE IF EXISTS `student_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `month_num` int(11) NOT NULL,
  `hadir` int(11) NOT NULL DEFAULT 20,
  `effective_days` int(11) NOT NULL DEFAULT 20,
  `sakit` int(11) NOT NULL DEFAULT 0,
  `izin` int(11) NOT NULL DEFAULT 0,
  `alpha` int(11) NOT NULL DEFAULT 0,
  `alfa` int(11) NOT NULL DEFAULT 0,
  `attendance_pct` decimal(5,2) NOT NULL DEFAULT 100.00,
  `is_locked` tinyint(1) DEFAULT 0,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_std_att` (`student_id`,`class_id`,`academic_year_id`,`month_num`),
  KEY `class_id` (`class_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `student_attendance_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `student_attendance_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `student_attendance_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_attendance`
--

LOCK TABLES `student_attendance` WRITE;
/*!40000 ALTER TABLE `student_attendance` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_grades`
--

DROP TABLE IF EXISTS `student_grades`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `month_num` int(11) NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `kkm` decimal(5,2) NOT NULL DEFAULT 75.00,
  `is_locked` tinyint(1) DEFAULT 0,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_std_grade` (`student_id`,`class_id`,`subject_id`,`academic_year_id`,`month_num`),
  KEY `class_id` (`class_id`),
  KEY `subject_id` (`subject_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`),
  CONSTRAINT `student_grades_ibfk_3` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `student_grades_ibfk_4` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_grades`
--

LOCK TABLES `student_grades` WRITE;
/*!40000 ALTER TABLE `student_grades` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_grades` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `student_movement_logs`
--

DROP TABLE IF EXISTS `student_movement_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `student_movement_logs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `month_num` int(11) NOT NULL,
  `month_name` varchar(20) NOT NULL,
  `masuk` int(11) NOT NULL DEFAULT 0,
  `keluar` int(11) NOT NULL DEFAULT 0,
  `current_count` int(11) NOT NULL DEFAULT 0,
  `notes` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_unit_ta_month` (`unit_id`,`academic_year_id`,`month_num`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `student_movement_logs_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE CASCADE,
  CONSTRAINT `student_movement_logs_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_movement_logs`
--

LOCK TABLES `student_movement_logs` WRITE;
/*!40000 ALTER TABLE `student_movement_logs` DISABLE KEYS */;
/*!40000 ALTER TABLE `student_movement_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `students`
--

DROP TABLE IF EXISTS `students`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `students` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nis` varchar(30) NOT NULL,
  `nisn` varchar(30) DEFAULT NULL,
  `nik` varchar(30) DEFAULT NULL,
  `no_kk` varchar(30) NOT NULL,
  `nama` varchar(150) NOT NULL,
  `gender` enum('L','P') NOT NULL,
  `birthplace` varchar(100) DEFAULT NULL,
  `birthdate` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `father_name` varchar(150) DEFAULT NULL,
  `mother_name` varchar(150) DEFAULT NULL,
  `parent_phone` varchar(50) DEFAULT NULL,
  `parent_email` varchar(150) DEFAULT NULL,
  `parent_user_id` int(11) DEFAULT NULL,
  `student_user_id` int(11) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `status` enum('Aktif','Pindah','Lulus','Alumni') DEFAULT 'Aktif',
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nisn` (`nisn`),
  KEY `parent_user_id` (`parent_user_id`),
  KEY `student_user_id` (`student_user_id`),
  KEY `idx_std_kk` (`no_kk`),
  KEY `idx_std_nis` (`nis`),
  KEY `idx_std_unit` (`unit_id`),
  CONSTRAINT `students_ibfk_1` FOREIGN KEY (`parent_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_ibfk_2` FOREIGN KEY (`student_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `students_ibfk_3` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
/*!40000 ALTER TABLE `students` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `subjects`
--

DROP TABLE IF EXISTS `subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `code` varchar(30) NOT NULL,
  `name` varchar(150) NOT NULL,
  `type` enum('Diniyah','Umum','Tahfidz','Tahsin','Baca Latin','National Kurikulum') NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supervisi_rubric_scores`
--

DROP TABLE IF EXISTS `supervisi_rubric_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supervisi_rubric_scores` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `supervisi_id` int(11) NOT NULL,
  `section` enum('A','B','C') NOT NULL,
  `item_number` int(11) NOT NULL,
  `score_val` int(11) NOT NULL,
  `weight_val` int(11) NOT NULL,
  `final_item_score` decimal(5,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `supervisi_id` (`supervisi_id`),
  CONSTRAINT `supervisi_rubric_scores_ibfk_1` FOREIGN KEY (`supervisi_id`) REFERENCES `supervisi_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supervisi_rubric_scores`
--

LOCK TABLES `supervisi_rubric_scores` WRITE;
/*!40000 ALTER TABLE `supervisi_rubric_scores` DISABLE KEYS */;
/*!40000 ALTER TABLE `supervisi_rubric_scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supervisi_sessions`
--

DROP TABLE IF EXISTS `supervisi_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `supervisi_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `score_a` decimal(5,2) NOT NULL DEFAULT 0.00,
  `score_b` decimal(5,2) NOT NULL DEFAULT 0.00,
  `score_c` decimal(5,2) NOT NULL DEFAULT 0.00,
  `total_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `max_score` decimal(5,2) NOT NULL DEFAULT 100.00,
  `final_score` decimal(5,2) NOT NULL DEFAULT 0.00,
  `achievement_predicate` enum('SANGAT BAIK','BAIK','CUKUP','PERLU PENINGKATAN') NOT NULL,
  `notes_good` text DEFAULT NULL,
  `notes_improve` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `supervisor_id` (`supervisor_id`),
  KEY `teacher_id` (`teacher_id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  CONSTRAINT `supervisi_sessions_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `supervisi_sessions_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `supervisi_sessions_ibfk_3` FOREIGN KEY (`supervisor_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `supervisi_sessions_ibfk_4` FOREIGN KEY (`teacher_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `supervisi_sessions_ibfk_5` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  CONSTRAINT `supervisi_sessions_ibfk_6` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supervisi_sessions`
--

LOCK TABLES `supervisi_sessions` WRITE;
/*!40000 ALTER TABLE `supervisi_sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `supervisi_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_responses`
--

DROP TABLE IF EXISTS `survey_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_responses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `template_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `respondent_user_id` int(11) DEFAULT NULL,
  `answers_json` longtext NOT NULL,
  `total_score` int(11) NOT NULL,
  `max_score` int(11) NOT NULL,
  `score_pct` decimal(5,2) NOT NULL,
  `suggestions` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `survey_responses_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `survey_templates` (`id`),
  CONSTRAINT `survey_responses_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `survey_responses_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_responses`
--

LOCK TABLES `survey_responses` WRITE;
/*!40000 ALTER TABLE `survey_responses` DISABLE KEYS */;
/*!40000 ALTER TABLE `survey_responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `survey_templates`
--

DROP TABLE IF EXISTS `survey_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `survey_templates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(50) NOT NULL,
  `title` varchar(150) NOT NULL,
  `target_respondent` enum('Orang Tua','Guru','Siswa') NOT NULL,
  `questions_json` longtext NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_templates`
--

LOCK TABLES `survey_templates` WRITE;
/*!40000 ALTER TABLE `survey_templates` DISABLE KEYS */;
INSERT INTO `survey_templates` VALUES (1,'SURVEY_ORTU','Kuesioner Kepuasan Layanan Sekolah (Orang Tua)','Orang Tua','{\"A\": {\"title\": \"A. Layanan Pembayaran\", \"items\": [\"Respon komunikasi petugas dalam melayani pembayaran\", \"Kecepatan layanan pembayaran di tempat\", \"Frekuensi pengingat kewajiban pembayaran\", \"Kemudahan proses pembayaran via transfer\", \"Kejelasan informasi biaya dan tagihan sekolah\", \"Pilihan metode pembayaran yang tersedia\", \"Kelengkapan dan ketepatan bukti pembayaran\"]}, \"B\": {\"title\": \"B. Layanan Kesehatan (UKS)\", \"items\": [\"Ketepatan penanganan pertama saat siswa sakit\", \"Komunikasi kepada orangtua terkait tindak lanjut kesehatan\", \"Keramahan petugas kesehatan\"]}, \"C\": {\"title\": \"C. Fasilitas\", \"items\": [\"Ketersediaan & kondisi ruang kelas\", \"Ketersediaan & kondisi lapangan / tempat bermain\", \"Ketersediaan & kondisi tempat ibadah / masjid\", \"Ketersediaan & kondisi tempat makan siswa\", \"Ketersediaan & kondisi tempat jajan santri / mini market\", \"Ketersediaan & kondisi suplai air bersih\"]}, \"D1\": {\"title\": \"D.1 Komunikasi Wali Kelas dan Guru dengan Orangtua\", \"items\": [\"Responsif dalam komunikasi\", \"Bahasa yang digunakan jelas & santun\", \"Frekuensi & kejelasan informasi terkait siswa/kegiatan sekolah\"]}, \"D2\": {\"title\": \"D.2 Layanan Pembelajaran oleh Guru\", \"items\": [\"Metode pengajaran dalam kelas\", \"Metode evaluasi & penilaian\", \"Kehadiran & kedisiplinan guru\", \"Keteladanan guru dalam berakhlak karimah\", \"Perhatian guru terhadap siswa\", \"Media pembelajaran yang digunakan\", \"Pembiasaan penggunaan bahasa asing\"]}, \"D3\": {\"title\": \"D.3 Ketercapaian Pembelajaran Siswa\", \"items\": [\"Ketercapaian pembelajaran Tahfidzul Qur\'an\", \"Ketercapaian pembelajaran Bahasa Arab\", \"Ketercapaian pembelajaran Ilmu Agama\", \"Ketercapaian pembelajaran Ilmu Umum & Teknologi\", \"Anak memahami materi Pelajaran sesuai jenjangnya\", \"Anak mampu memanfaatkan teknologi secara tepat dan bertanggung jawab\", \"Anak menunjukkan kreativitas dalam belajar\", \"Anak mampu bekerja sama dengan teman (kolaborasi)\", \"Anak mampu mengelola waktu dan menyelesaikan tugas dengan baik (produktivitas)\", \"Anak mampu berkomunikasi dengan sopan dan efektif\"]}, \"D4\": {\"title\": \"D.4 Ketercapaian Perubahan Sikap, Akhlak, dan Ibadah Siswa\", \"items\": [\"Ibadah siswa sesuai tuntunan (Anak menunjukkan perilaku religious dan akhlak yang baik)\", \"Sikap siswa terhadap orangtua & keluarga\", \"Sikap siswa terhadap orang lain\", \"Akhlak & adab siswa dalam kegiatan sehari-hari\", \"Anak bersikap disiplin dalam belajar dan aktivitas harian\", \"Anak bertanggung jawab terhadap tugas dan kewajibannya\", \"Anak bersikap jujur dalam perkataan dan perbuatan\"]}, \"D5\": {\"title\": \"D.5 Sarana & Prasarana Pembelajaran\", \"items\": [\"Media pembelajaran / alat peraga di kelas\", \"Laboratorium IPA (Biologi, Fisika, Kimia)\", \"Laboratorium Komputer\", \"Perpustakaan / pojok literasi\"]}, \"E\": {\"title\": \"E. Kepuasan Secara Umum\", \"items\": [\"Sikap staf & guru dalam membantu dan melayani\", \"Keramahan staf & guru\", \"Kejelasan alur penyampaian keluhan atau saran\", \"Respons sekolah terhadap keluhan\", \"Kebersihan lingkungan sekolah\", \"Keamanan lingkungan sekolah\"]}}',1,'2026-09-23 18:43:17'),(2,'SURVEY_KEPSEK','Kuesioner Kepuasan Guru & Staf terhadap Kepala Sekolah','Guru','{\"A\": {\"title\": \"Kepemimpinan & Keteladanan\", \"items\": [\"Kepala sekolah memberikan arahan program kerja dengan jelas dan terarah\", \"Kepala sekolah menjadi teladan dalam kedisiplinan dan ibadah\", \"Kepala sekolah terbuka terhadap masukan dan kritik konstruktif\"]}, \"B\": {\"title\": \"Manajemen & Dukungan Pembelajaran\", \"items\": [\"Kepala sekolah memberikan dukungan fasilitas untuk proses KBM dan tahfidz\", \"Kepala sekolah mengapresiasi pencapaian dan kinerja guru\", \"Kepala sekolah adil dan bijaksana dalam penyelesaian permasalahan guru/staf\"]}}',1,'2026-09-23 18:43:17'),(3,'SURVEY_SISWA','Kuesioner Kepuasan Siswa terhadap Layanan Sekolah (SMP/SMA)','Siswa','{\"A\": {\"title\": \"Kepuasan Layanan Pembelajaran di Sekolah\", \"items\": [\"Guru mengajar dengan jelas, menarik, dan mudah dipahami\", \"Fasilitas kelas dan laboratorium mendukung pembelajaran dengan baik\", \"Program tahfidz dan kegiatan keagamaan membimbing ibadah saya\", \"Sekolah memberikan kesempatan menyalurkan minat, bakat, dan lomba\", \"Lingkungan sekolah aman, nyaman, dan bersih untuk belajar\"]}}',1,'2026-09-23 18:43:17');
/*!40000 ALTER TABLE `survey_templates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahfidz_exams`
--

DROP TABLE IF EXISTS `tahfidz_exams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahfidz_exams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `student_id` int(11) NOT NULL,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `proposed_by_tutor_id` int(11) NOT NULL,
  `examiner_id` int(11) DEFAULT NULL,
  `juz_number` int(11) NOT NULL,
  `exam_date` date DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `status` enum('Menunggu','Lulus','Tidak Lulus') DEFAULT 'Menunggu',
  `notes` text DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `student_id` (`student_id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `tahfidz_exams_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `tahfidz_exams_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `tahfidz_exams_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_exams`
--

LOCK TABLES `tahfidz_exams` WRITE;
/*!40000 ALTER TABLE `tahfidz_exams` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahfidz_exams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahfidz_halaqah`
--

DROP TABLE IF EXISTS `tahfidz_halaqah`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahfidz_halaqah` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `tutor_employee_id` int(11) NOT NULL,
  `type` enum('Siswa','Guru') NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `unit_id` (`unit_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `tutor_employee_id` (`tutor_employee_id`),
  CONSTRAINT `tahfidz_halaqah_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `tahfidz_halaqah_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `tahfidz_halaqah_ibfk_3` FOREIGN KEY (`tutor_employee_id`) REFERENCES `employees` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_halaqah`
--

LOCK TABLES `tahfidz_halaqah` WRITE;
/*!40000 ALTER TABLE `tahfidz_halaqah` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahfidz_halaqah` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahfidz_halaqah_members`
--

DROP TABLE IF EXISTS `tahfidz_halaqah_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahfidz_halaqah_members` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `halaqah_id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `employee_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `halaqah_id` (`halaqah_id`),
  KEY `student_id` (`student_id`),
  KEY `employee_id` (`employee_id`),
  CONSTRAINT `tahfidz_halaqah_members_ibfk_1` FOREIGN KEY (`halaqah_id`) REFERENCES `tahfidz_halaqah` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tahfidz_halaqah_members_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tahfidz_halaqah_members_ibfk_3` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_halaqah_members`
--

LOCK TABLES `tahfidz_halaqah_members` WRITE;
/*!40000 ALTER TABLE `tahfidz_halaqah_members` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahfidz_halaqah_members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahfidz_setoran_guru`
--

DROP TABLE IF EXISTS `tahfidz_setoran_guru`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahfidz_setoran_guru` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `halaqah_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `month_num` int(11) NOT NULL,
  `teacher_type` enum('SHML','SHSL','MHSL','MHML') NOT NULL,
  `surah_start` int(11) NOT NULL,
  `ayah_start` int(11) NOT NULL,
  `page_start` int(11) NOT NULL,
  `surah_end` int(11) NOT NULL,
  `ayah_end` int(11) NOT NULL,
  `page_end` int(11) NOT NULL,
  `lines_count` int(11) NOT NULL DEFAULT 0,
  `is_completed` tinyint(1) DEFAULT 0,
  `tutor_notes` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `halaqah_id` (`halaqah_id`),
  KEY `employee_id` (`employee_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `tahfidz_setoran_guru_ibfk_1` FOREIGN KEY (`halaqah_id`) REFERENCES `tahfidz_halaqah` (`id`),
  CONSTRAINT `tahfidz_setoran_guru_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  CONSTRAINT `tahfidz_setoran_guru_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_setoran_guru`
--

LOCK TABLES `tahfidz_setoran_guru` WRITE;
/*!40000 ALTER TABLE `tahfidz_setoran_guru` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahfidz_setoran_guru` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahfidz_setoran_siswa`
--

DROP TABLE IF EXISTS `tahfidz_setoran_siswa`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahfidz_setoran_siswa` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `halaqah_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `month_num` int(11) NOT NULL,
  `student_type` enum('SHML','SHSL','MHSL','MHML') NOT NULL,
  `surah_start` int(11) NOT NULL,
  `ayah_start` int(11) NOT NULL,
  `page_start` int(11) NOT NULL,
  `surah_end` int(11) NOT NULL,
  `ayah_end` int(11) NOT NULL,
  `page_end` int(11) NOT NULL,
  `lines_count` int(11) NOT NULL DEFAULT 0,
  `is_completed` tinyint(1) DEFAULT 0,
  `is_exam_prep` tinyint(1) DEFAULT 0,
  `exam_juz` int(11) DEFAULT NULL,
  `tutor_notes` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `halaqah_id` (`halaqah_id`),
  KEY `student_id` (`student_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `tahfidz_setoran_siswa_ibfk_1` FOREIGN KEY (`halaqah_id`) REFERENCES `tahfidz_halaqah` (`id`),
  CONSTRAINT `tahfidz_setoran_siswa_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  CONSTRAINT `tahfidz_setoran_siswa_ibfk_3` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_setoran_siswa`
--

LOCK TABLES `tahfidz_setoran_siswa` WRITE;
/*!40000 ALTER TABLE `tahfidz_setoran_siswa` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahfidz_setoran_siswa` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahfidz_targets`
--

DROP TABLE IF EXISTS `tahfidz_targets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tahfidz_targets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `academic_year_id` int(11) NOT NULL,
  `target_for` enum('Siswa','Guru') NOT NULL,
  `unit_id` int(11) NOT NULL,
  `grade_or_employee_id` varchar(50) NOT NULL,
  `target_lines` int(11) NOT NULL DEFAULT 300,
  `baseline_info` varchar(150) DEFAULT NULL,
  `target_info` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `tahfidz_targets_ibfk_1` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `tahfidz_targets_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_targets`
--

LOCK TABLES `tahfidz_targets` WRITE;
/*!40000 ALTER TABLE `tahfidz_targets` DISABLE KEYS */;
/*!40000 ALTER TABLE `tahfidz_targets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teacher_subjects`
--

DROP TABLE IF EXISTS `teacher_subjects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `teacher_subjects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `employee_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `employee_id` (`employee_id`),
  KEY `subject_id` (`subject_id`),
  KEY `class_id` (`class_id`),
  KEY `academic_year_id` (`academic_year_id`),
  CONSTRAINT `teacher_subjects_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subjects_ibfk_3` FOREIGN KEY (`class_id`) REFERENCES `classes` (`id`) ON DELETE CASCADE,
  CONSTRAINT `teacher_subjects_ibfk_4` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_subjects`
--

LOCK TABLES `teacher_subjects` WRITE;
/*!40000 ALTER TABLE `teacher_subjects` DISABLE KEYS */;
/*!40000 ALTER TABLE `teacher_subjects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unit_administrasi_deadlines`
--

DROP TABLE IF EXISTS `unit_administrasi_deadlines`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `unit_administrasi_deadlines` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `unit_id` int(11) NOT NULL,
  `academic_year_id` int(11) NOT NULL,
  `jenis_admin_id` int(11) NOT NULL,
  `due_info` varchar(150) NOT NULL,
  `deadline_date` date DEFAULT NULL,
  `updated_by_user_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_unit_ta_jenis` (`unit_id`,`academic_year_id`,`jenis_admin_id`),
  KEY `academic_year_id` (`academic_year_id`),
  KEY `jenis_admin_id` (`jenis_admin_id`),
  CONSTRAINT `unit_administrasi_deadlines_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`),
  CONSTRAINT `unit_administrasi_deadlines_ibfk_2` FOREIGN KEY (`academic_year_id`) REFERENCES `academic_years` (`id`),
  CONSTRAINT `unit_administrasi_deadlines_ibfk_3` FOREIGN KEY (`jenis_admin_id`) REFERENCES `master_jenis_administrasi` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unit_administrasi_deadlines`
--

LOCK TABLES `unit_administrasi_deadlines` WRITE;
/*!40000 ALTER TABLE `unit_administrasi_deadlines` DISABLE KEYS */;
/*!40000 ALTER TABLE `unit_administrasi_deadlines` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `units`
--

DROP TABLE IF EXISTS `units`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `units` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(20) NOT NULL,
  `name` varchar(100) NOT NULL,
  `level` varchar(20) NOT NULL,
  `kop_title` varchar(200) NOT NULL DEFAULT 'YAYASAN ISLAM AL KAHFI BATAM',
  `kop_subtitle` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `bank_name` varchar(50) DEFAULT NULL,
  `bank_account` varchar(50) DEFAULT NULL,
  `bank_holder` varchar(100) DEFAULT NULL,
  `letter_template` text DEFAULT NULL,
  `smtp_host` varchar(100) DEFAULT NULL,
  `smtp_port` int(11) DEFAULT 587,
  `smtp_user` varchar(100) DEFAULT NULL,
  `smtp_pass` varchar(255) DEFAULT NULL,
  `smtp_enc` varchar(10) DEFAULT 'tls',
  `logo_path` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
/*!40000 ALTER TABLE `units` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_roles`
--

DROP TABLE IF EXISTS `user_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_roles` (
  `user_id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`user_id`,`role_id`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `user_roles_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_roles_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_roles`
--

LOCK TABLES `user_roles` WRITE;
/*!40000 ALTER TABLE `user_roles` DISABLE KEYS */;
INSERT INTO `user_roles` VALUES (1,1,1);
/*!40000 ALTER TABLE `user_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `email` varchar(150) DEFAULT NULL,
  `full_name` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `unit_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `force_change_password` tinyint(1) DEFAULT 0,
  `failed_login_attempts` int(11) DEFAULT 0,
  `lockout_until` datetime DEFAULT NULL,
  `last_login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  KEY `idx_user_username` (`username`),
  KEY `idx_user_email` (`email`),
  KEY `unit_id` (`unit_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`unit_id`) REFERENCES `units` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','$2y$10$LmCTKs1aNUM7upz2QzVAu.YznF1W354kFZmyeOktclSyTPTFFyOOy','admin@alkahfi.sch.id','Administrator Operator Yayasan','08117000001',NULL,1,0,0,NULL,'2026-10-26 14:43:10','2026-10-26 14:42:48','2026-10-26 14:43:10');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `employee_turnovers`
--

/*!50001 DROP VIEW IF EXISTS `employee_turnovers`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
CREATE OR REPLACE VIEW `employee_turnovers` AS select `employee_turnover_logs`.`id` AS `id`,`employee_turnover_logs`.`employee_id` AS `employee_id`,`employee_turnover_logs`.`action_type` AS `action_type`,`employee_turnover_logs`.`from_unit_id` AS `from_unit_id`,`employee_turnover_logs`.`to_unit_id` AS `to_unit_id`,`employee_turnover_logs`.`effective_date` AS `effective_date`,`employee_turnover_logs`.`reason` AS `reason`,`employee_turnover_logs`.`created_by_user_id` AS `created_by_user_id`,`employee_turnover_logs`.`created_at` AS `created_at` from `employee_turnover_logs`;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26 14:45:29

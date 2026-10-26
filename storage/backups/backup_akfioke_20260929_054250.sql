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
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_year_approvals`
--

LOCK TABLES `academic_year_approvals` WRITE;
/*!40000 ALTER TABLE `academic_year_approvals` DISABLE KEYS */;
INSERT INTO `academic_year_approvals` VALUES (1,1,1,'Setuju',NULL,NULL,'2026-09-23 18:42:58'),(2,1,2,'Setuju',NULL,NULL,'2026-09-23 18:42:58'),(3,1,3,'Setuju',NULL,NULL,'2026-09-23 18:42:58'),(4,1,4,'Setuju',NULL,NULL,'2026-09-23 18:42:58'),(5,1,5,'Setuju',NULL,NULL,'2026-09-23 18:42:58'),(6,1,1,'Setuju',NULL,NULL,'2026-09-23 18:43:08'),(7,1,2,'Setuju',NULL,NULL,'2026-09-23 18:43:08'),(8,1,3,'Setuju',NULL,NULL,'2026-09-23 18:43:08'),(9,1,4,'Setuju',NULL,NULL,'2026-09-23 18:43:08'),(10,1,5,'Setuju',NULL,NULL,'2026-09-23 18:43:08'),(11,1,1,'Setuju',NULL,NULL,'2026-09-23 18:43:16'),(12,1,2,'Setuju',NULL,NULL,'2026-09-23 18:43:16'),(13,1,3,'Setuju',NULL,NULL,'2026-09-23 18:43:16'),(14,1,4,'Setuju',NULL,NULL,'2026-09-23 18:43:16'),(15,1,5,'Setuju',NULL,NULL,'2026-09-23 18:43:16'),(16,2,1,'Menunggu',NULL,NULL,'2026-09-24 09:59:19'),(17,2,3,'Menunggu',NULL,NULL,'2026-09-24 21:40:34'),(18,2,5,'Menunggu',NULL,NULL,'2026-09-24 09:59:19'),(19,2,4,'Menunggu',NULL,NULL,'2026-09-24 09:59:19'),(20,2,2,'Menunggu',NULL,NULL,'2026-09-24 09:59:19'),(21,3,1,'Menunggu',NULL,NULL,'2026-09-27 17:25:55'),(22,3,3,'Setuju','',4,'2026-09-27 19:44:00'),(23,3,16,'Menunggu',NULL,NULL,'2026-09-27 17:25:55'),(24,3,17,'Menunggu',NULL,NULL,'2026-09-27 17:25:55'),(25,3,5,'Menunggu',NULL,NULL,'2026-09-27 17:25:55'),(26,3,4,'Menunggu',NULL,NULL,'2026-09-27 17:25:55'),(27,3,2,'Menunggu',NULL,NULL,'2026-09-27 17:25:55');
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
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ta_sem` (`name`,`semester`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `academic_years`
--

LOCK TABLES `academic_years` WRITE;
/*!40000 ALTER TABLE `academic_years` DISABLE KEYS */;
INSERT INTO `academic_years` VALUES (1,'2024/2025','Genap',264,48,1,0,'2026-09-23 18:42:58','2026-09-24 20:04:42'),(2,'2026/2027','Ganjil',264,48,0,0,'2026-09-24 09:59:19','2026-09-24 09:59:19'),(3,'2027/2028','Ganjil',264,48,0,0,'2026-09-27 17:25:55','2026-09-27 17:25:55');
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
) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `administrasi_uploads`
--

LOCK TABLES `administrasi_uploads` WRITE;
/*!40000 ALTER TABLE `administrasi_uploads` DISABLE KEYS */;
INSERT INTO `administrasi_uploads` VALUES (116,3,1,12,26,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(117,3,1,12,27,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(118,3,1,12,28,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(119,3,1,12,29,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(120,3,1,12,30,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(121,3,1,12,31,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(122,3,1,12,32,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(123,3,1,12,33,'Dokumen Administrasi Perencanaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(124,3,1,17,34,'Dokumen Wali Kelas Binaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(125,3,1,17,35,'Dokumen Wali Kelas Binaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(126,3,1,17,36,'Dokumen Wali Kelas Binaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(127,3,1,17,37,'Dokumen Wali Kelas Binaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(128,3,1,17,38,'Dokumen Wali Kelas Binaan',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(129,3,1,18,26,'Dokumen Administrasi Guru',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(130,3,1,18,27,'Dokumen Administrasi Guru',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(131,3,1,18,28,'Dokumen Administrasi Guru',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(132,3,1,18,29,'Dokumen Administrasi Guru',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(133,3,1,14,26,'Dokumen Administrasi Tahfidz',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(134,3,1,14,27,'Dokumen Administrasi Tahfidz',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(135,3,1,14,28,'Dokumen Administrasi Tahfidz',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(136,3,1,14,29,'Dokumen Administrasi Tahfidz',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(137,3,1,14,30,'Dokumen Administrasi Tahfidz',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(138,3,1,14,31,'Dokumen Administrasi Tahfidz',NULL,'','',0,NULL,'2026-09-24 13:06:58',1,'Disetujui',NULL,NULL,NULL,1),(139,16,1,37,27,'Silabus',1,'administrasi/15621006666d37170a989fbdfadead2a.pdf','Kartu Akun SPMB - Sarah Batrisya (AIS-2027-97A3).pdf',671072,NULL,'2026-09-25 21:24:07',1,'Disetujui',36,'','2026-09-27 13:00:53',1),(140,16,1,37,28,'RPP Modul Ajar',2,'administrasi/d1f2bc4710e56053c810e73d7d88e529.pdf','Kartu Akun SPMB - Sarah Batrisya (AIS-2027-97A3).pdf',671072,NULL,'2026-09-25 21:24:31',1,'Disetujui',36,'','2026-09-25 21:25:33',1),(141,16,1,37,31,'Jurnal mengajar',1,'administrasi/2fe78293741c476b158b44b30d58f706.xlsx','Template_Import_Pegawai_AlKahfi.xlsx',8494,NULL,'2026-09-25 21:24:46',1,'Revisi',36,'salah dokumen','2026-09-25 21:25:30',1);
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
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akfi_assessments`
--

LOCK TABLES `akfi_assessments` WRITE;
/*!40000 ALTER TABLE `akfi_assessments` DISABLE KEYS */;
INSERT INTO `akfi_assessments` VALUES (1,20,3,1,7,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-23 20:08:03','2026-09-24 12:07:15'),(2,7,3,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-23 20:19:20','2026-09-23 20:19:20'),(3,15,3,1,2,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-23 20:20:32','2026-09-24 12:07:15'),(4,12,3,1,3,'Disetujui_Atasan',65.00,20.00,14.70,99.70,'A',NULL,8,'2026-09-24 10:34:44',NULL,NULL,NULL,NULL,'{\"1\":{\"actual_val\":100,\"superior_score\":100,\"final_item_score\":5},\"2\":{\"actual_val\":100,\"superior_score\":100,\"final_item_score\":5},\"3\":{\"actual_val\":90,\"superior_score\":90,\"final_item_score\":9},\"4\":{\"actual_val\":1,\"superior_score\":1,\"final_item_score\":10},\"5\":{\"actual_val\":75,\"superior_score\":75,\"final_item_score\":10},\"6\":{\"actual_val\":88,\"superior_score\":88,\"final_item_score\":10},\"7\":{\"actual_val\":100,\"superior_score\":100,\"final_item_score\":6},\"8\":{\"actual_val\":95,\"superior_score\":95,\"final_item_score\":10},\"9\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"10\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"11\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"12\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"13\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"14\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"15\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":2},\"16\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":2},\"17\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":1},\"18\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":10}}','','2026-09-23 20:21:10','2026-09-24 11:13:06'),(5,22,2,1,4,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 06:39:18','2026-09-24 06:39:18'),(6,13,3,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 07:20:06','2026-09-24 07:20:06'),(7,4,2,1,9,'Disetujui_Atasan',34.30,30.00,0.00,64.30,'C',NULL,3,'2026-09-26 04:08:06',NULL,NULL,NULL,NULL,'{\"151\":{\"actual_val\":100,\"superior_score\":50,\"final_item_score\":1.5},\"152\":{\"actual_val\":100,\"superior_score\":60,\"final_item_score\":1.8},\"153\":{\"actual_val\":90,\"superior_score\":50,\"final_item_score\":1.67},\"154\":{\"actual_val\":95,\"superior_score\":40,\"final_item_score\":1.68},\"155\":{\"actual_val\":90,\"superior_score\":60,\"final_item_score\":2.67},\"156\":{\"actual_val\":90,\"superior_score\":60,\"final_item_score\":2.67},\"157\":{\"actual_val\":0,\"superior_score\":44,\"final_item_score\":2.24},\"158\":{\"actual_val\":3,\"superior_score\":2,\"final_item_score\":2},\"159\":{\"actual_val\":100,\"superior_score\":20,\"final_item_score\":0.8},\"160\":{\"actual_val\":100,\"superior_score\":80,\"final_item_score\":3.2},\"161\":{\"actual_val\":45,\"superior_score\":50,\"final_item_score\":4},\"162\":{\"actual_val\":5,\"superior_score\":6,\"final_item_score\":3.2},\"163\":{\"actual_val\":85,\"superior_score\":55,\"final_item_score\":1.94},\"164\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"165\":{\"actual_val\":90,\"superior_score\":4,\"final_item_score\":0.13},\"166\":{\"actual_val\":100,\"superior_score\":20,\"final_item_score\":0.6},\"167\":{\"actual_val\":50,\"superior_score\":20,\"final_item_score\":1.2},\"168\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"169\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"170\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"171\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"172\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"173\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":5},\"174\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"175\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"176\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":2},\"177\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":2},\"178\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":1},\"179\":{\"actual_val\":6,\"superior_score\":6,\"final_item_score\":5}}','','2026-09-24 07:31:51','2026-09-26 04:08:06'),(8,18,3,1,5,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 07:35:02','2026-09-24 07:35:02'),(9,16,3,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 07:42:01','2026-09-24 07:42:01'),(11,3,3,1,8,'Disetujui_Atasan',53.00,30.00,0.00,83.00,'B',NULL,3,'2026-09-25 21:43:27',NULL,NULL,NULL,NULL,'{\"122\":{\"actual_val\":0,\"superior_score\":100,\"final_item_score\":0},\"123\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"124\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"125\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"126\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"127\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"128\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":4},\"129\":{\"actual_val\":2,\"superior_score\":0,\"final_item_score\":2},\"130\":{\"actual_val\":10,\"superior_score\":0,\"final_item_score\":0.4},\"131\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"132\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"133\":{\"actual_val\":5,\"superior_score\":0,\"final_item_score\":3},\"134\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"135\":{\"actual_val\":10,\"superior_score\":0,\"final_item_score\":3.6},\"136\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"137\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"138\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"139\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"140\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"141\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"142\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"143\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"144\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":5},\"145\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"146\":{\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"147\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":2},\"148\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":2},\"149\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":1},\"150\":{\"actual_val\":0,\"superior_score\":0,\"final_item_score\":5}}','','2026-09-24 08:22:02','2026-09-25 21:43:27'),(12,5,4,1,10,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 08:22:02','2026-09-24 08:22:02'),(13,6,5,1,10,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 08:22:02','2026-09-24 08:22:02'),(14,19,3,1,6,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 12:00:55','2026-09-24 12:00:55'),(15,9,3,1,2,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 12:09:58','2026-09-24 12:09:58'),(16,8,3,1,3,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-24 18:36:39'),(17,10,3,1,2,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-24 18:36:39'),(18,11,3,1,2,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-24 18:36:39'),(19,14,3,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-24 18:36:39'),(20,17,3,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-24 18:36:39'),(21,21,3,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-24 18:36:39'),(22,25,3,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-24 18:36:39'),(23,26,16,1,3,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 18:36:39','2026-09-27 07:20:34'),(24,27,3,1,2,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 20:45:34','2026-09-24 20:45:34'),(25,28,2,1,2,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-24 21:27:18','2026-09-24 21:27:18'),(26,32,16,1,3,'Disetujui_Atasan',49.23,20.00,0.00,69.23,'C','2026-09-25 21:22:16',36,'2026-09-25 21:32:09',NULL,NULL,NULL,NULL,'{\"37\":{\"self_score\":100,\"actual_val\":2.5,\"superior_score\":2.5,\"final_item_score\":0.13},\"38\":{\"self_score\":100,\"actual_val\":100,\"superior_score\":100,\"final_item_score\":5},\"39\":{\"self_score\":100,\"actual_val\":7.6,\"superior_score\":7.6,\"final_item_score\":0.68},\"40\":{\"self_score\":1,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"41\":{\"self_score\":95,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"42\":{\"self_score\":90,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"43\":{\"self_score\":0,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"44\":{\"self_score\":0,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"45\":{\"self_score\":0,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":0},\"46\":{\"self_score\":0,\"actual_val\":100,\"superior_score\":95,\"final_item_score\":8.42},\"47\":{\"self_score\":5,\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"48\":{\"self_score\":5,\"actual_val\":5,\"superior_score\":5,\"final_item_score\":4},\"49\":{\"self_score\":5,\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"50\":{\"self_score\":5,\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"51\":{\"self_score\":5,\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"52\":{\"self_score\":5,\"actual_val\":5,\"superior_score\":5,\"final_item_score\":3},\"53\":{\"self_score\":0,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":2},\"54\":{\"self_score\":0,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":2},\"55\":{\"self_score\":0,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":1},\"56\":{\"self_score\":0,\"actual_val\":0,\"superior_score\":0,\"final_item_score\":10}}','','2026-09-25 21:21:47','2026-09-25 21:32:09'),(27,33,16,1,1,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-25 21:30:32','2026-09-25 21:30:32'),(28,31,16,1,8,'Draft',0.00,0.00,0.00,0.00,'E',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2026-09-25 21:35:32','2026-09-25 21:35:32');
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
) ENGINE=InnoDB AUTO_INCREMENT=210 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akfi_template_items`
--

LOCK TABLES `akfi_template_items` WRITE;
/*!40000 ALTER TABLE `akfi_template_items` DISABLE KEYS */;
INSERT INTO `akfi_template_items` VALUES (1,1,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(2,1,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(3,1,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa di kelas yang diajarkan',NULL,'%',90.00,9.00,'higher_better','student_attendance',3),(4,1,'A','Pembelajaran','K-PEM-02','Terkumpulnya Best Practice',NULL,'jumlah',1.00,10.00,'higher_better','upload_best_practice',4),(5,1,'A','Pembelajaran','K-PEM-03','Capaian nilai rata-rata mapel yang diajarkan',NULL,'angka',75.00,10.00,'higher_better','subject_average',5),(6,1,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',88.00,10.00,'higher_better','supervisi_avg',6),(7,1,'A','Performance','K-PRF-02','% target hafalan tahunan guru (1 Juz)',NULL,'%',100.00,6.00,'higher_better','teacher_tahfidz_target',7),(8,1,'A','Performance','K-PRF-03','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,10.00,'higher_better','kegiatan_kbm_attendance',8),(9,1,'B','Sikap','S-01','Communication (Lisan & tulisan jelas dan mudah dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,9),(10,1,'B','Sikap','S-02','Profesionalisme (Refleksi diri & pemanfaatan TIK)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,10),(11,1,'B','Sikap','S-03','Teamwork (Kerjasama rekan kerja)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(12,1,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(13,1,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(14,1,'B','Sikap','S-06','Keteladanan & Disiplin Islami',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(15,1,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',15),(16,1,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',16),(17,1,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',17),(18,1,'C','Absensi','AB-04','Tidak Hadir / Mangkir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',18),(19,2,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(20,2,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(21,2,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa di kelas yang diajarkan',NULL,'%',90.00,9.00,'higher_better','student_attendance',3),(22,2,'A','Pembelajaran','K-PEM-02','Terkumpulnya Best Practice',NULL,'jumlah',1.00,10.00,'higher_better','upload_best_practice',4),(23,2,'A','Pembelajaran','K-PEM-03','% siswa lulus target tahfidz',NULL,'%',80.00,10.00,'higher_better','tahfidz_pass_pct',5),(24,2,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',88.00,10.00,'higher_better','supervisi_avg',6),(25,2,'A','Performance','K-PRF-02','% target hafalan tahunan guru (1 Juz)',NULL,'%',100.00,6.00,'higher_better','teacher_tahfidz_target',7),(26,2,'A','Performance','K-PRF-03','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,10.00,'higher_better','kegiatan_kbm_attendance',8),(27,2,'B','Sikap','S-01','Communication (Lisan & tulisan jelas dan mudah dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,9),(28,2,'B','Sikap','S-02','Profesionalisme (Refleksi diri & pemanfaatan TIK)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,10),(29,2,'B','Sikap','S-03','Teamwork (Kerjasama rekan kerja)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(30,2,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(31,2,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(32,2,'B','Sikap','S-06','Keteladanan & Disiplin Islami',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(33,2,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',15),(34,2,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',16),(35,2,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',17),(36,2,'C','Absensi','AB-04','Tidak Hadir / Mangkir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',18),(37,3,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(38,3,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(39,3,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa (target 100%)',NULL,'%',100.00,9.00,'higher_better','student_attendance',3),(40,3,'A','Pembelajaran','K-PEM-02','Terkumpulnya PTK (Penelitian Tindakan Kelas)',NULL,'jumlah',1.00,10.00,'higher_better','upload_ptk',4),(41,3,'A','Pembelajaran','K-PEM-03','Capaian nilai rata-rata mapel',NULL,'angka',95.00,10.00,'higher_better','subject_average',5),(42,3,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',90.00,8.00,'higher_better','supervisi_avg',6),(43,3,'A','Performance','K-PRF-02','Jumlah siswa yang menjuarai lomba (juara 1)',NULL,'Orang',3.00,4.00,'higher_better','juara_lomba_walas',7),(44,3,'A','Performance','K-PRF-03','% siswa lulus target tahfidz',NULL,'%',100.00,1.00,'higher_better','tahfidz_pass_pct',8),(45,3,'A','Performance','K-PRF-04','% target hafalan tahunan guru (1 Juz)',NULL,'%',100.00,5.00,'higher_better','teacher_tahfidz_target',9),(46,3,'A','Performance','K-PRF-05','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,8.00,'higher_better','kegiatan_kbm_attendance',10),(47,3,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,11),(48,3,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,12),(49,3,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(50,3,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(51,3,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,15),(52,3,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,16),(53,3,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',17),(54,3,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',18),(55,3,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',19),(56,3,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',20),(57,4,'A','Administrasi','K-ADM-01','Terkumpulnya dokumen perangkat perencanaan pembelajaran',NULL,'%',100.00,5.00,'higher_better','administrasi_completion',1),(58,4,'A','Administrasi','K-ADM-02','% penyelesaian tugas sesuai deadline & kualitas',NULL,'%',100.00,5.00,'higher_better','administrasi_ontime',2),(59,4,'A','Pembelajaran','K-PEM-01','% capaian kehadiran siswa (target 90%)',NULL,'%',90.00,9.00,'higher_better','student_attendance',3),(60,4,'A','Pembelajaran','K-PEM-02','Capaian nilai rata-rata pembelajaran umum',NULL,'angka',90.00,10.00,'higher_better','subject_average_umum',4),(61,4,'A','Pembelajaran','K-PEM-03','Capaian nilai rata-rata pembelajaran diniyah',NULL,'angka',90.00,10.00,'higher_better','subject_average_diniyah',5),(62,4,'A','Performance','K-PRF-01','Capaian nilai supervisi mengajar',NULL,'angka',85.00,10.00,'higher_better','supervisi_avg',6),(63,4,'A','Performance','K-PRF-02','% target hafalan tahunan guru (1/2 Juz)',NULL,'%',100.00,6.00,'higher_better','teacher_tahfidz_target',7),(64,4,'A','Performance','K-PRF-03','% kehadiran dalam kegiatan sekolah',NULL,'%',95.00,10.00,'higher_better','kegiatan_kbm_attendance',8),(65,4,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,9),(66,4,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,10),(67,4,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(68,4,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(69,4,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,13),(70,4,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,14),(71,4,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',15),(72,4,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',16),(73,4,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',17),(74,4,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,10.00,'lower_better','att_mangkir',18),(75,5,'A','Administrasi','K-TU-01','Terkumpulnya dokumen keuangan bulanan berikut lampiran pemakaian uang',NULL,'%',100.00,15.00,'higher_better','tu_dokumen_keuangan',1),(76,5,'A','Administrasi','K-TU-02','Penyampaian laporan keuangan bulanan tepat waktu',NULL,'%',100.00,15.00,'higher_better','tu_laporan_keuangan_ontime',2),(77,5,'A','Administrasi','K-TU-03','Terkumpulnya dokumen penggajian bulanan berikut lampiran pemakaian uang',NULL,'%',100.00,10.00,'higher_better','tu_dokumen_gaji',3),(78,5,'A','Administrasi','K-TU-04','Penyampaian laporan penggajian bulanan tepat waktu',NULL,'%',100.00,10.00,'higher_better','tu_laporan_gaji_ontime',4),(79,5,'A','Performance','K-TU-05','Jumlah kesalahan input Braja (Makin sedikit makin baik)',NULL,'Freq',0.00,5.00,'lower_better','tu_braja_errors',5),(80,5,'A','Performance','K-TU-06','% Dalam kegiatan sekolah',NULL,'%',100.00,5.00,'higher_better','kegiatan_kbm_attendance',6),(81,5,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,7),(82,5,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,8),(83,5,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,9),(84,5,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,10),(85,5,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(86,5,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,12),(87,5,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',13),(88,5,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',14),(89,5,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',15),(90,5,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,15.00,'lower_better','att_mangkir',16),(91,6,'A','Administrasi','K-CS-01','Checklist pekerjaan rutin harian',NULL,'Freq',200.00,15.00,'higher_better','cs_checklist_rutin',1),(92,6,'A','Administrasi','K-CS-02','Jumlah piket bulanan yang dilaksanakan',NULL,'Freq',200.00,10.00,'higher_better','cs_checklist_piket',2),(93,6,'A','Administrasi','K-CS-03','Jumlah kegiatan gotong royong Sabtu yang terlaksana',NULL,'Freq',24.00,10.00,'higher_better','cs_gotong_royong',3),(94,6,'A','Administrasi','K-CS-04','Tingkat kepuasan kebersihan wali murid (Survei E.5)',NULL,'%',100.00,10.00,'higher_better','survey_parent_cleanliness',4),(95,6,'A','Performance','K-CS-05','% kehadiran dalam kegiatan sekolah',NULL,'%',100.00,15.00,'higher_better','kegiatan_kbm_attendance',5),(96,6,'B','Sikap','S-01','Communication',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,6),(97,6,'B','Sikap','S-02','Profesionalisme',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,7),(98,6,'B','Sikap','S-03','Teamwork',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,8),(99,6,'B','Sikap','S-04','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,9),(100,6,'B','Sikap','S-05','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,10),(101,6,'B','Sikap','S-06','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,11),(102,6,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',12),(103,6,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',13),(104,6,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',14),(105,6,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',6.00,15.00,'lower_better','att_mangkir',15),(106,7,'A','Administrasi','K-SEC-01','Laporan harian lengkap',NULL,'Freq',200.00,5.00,'higher_better','sec_daily_complete',1),(107,7,'A','Administrasi','K-SEC-02','Laporan tepat waktu',NULL,'%',100.00,5.00,'higher_better','sec_daily_ontime',2),(108,7,'A','Performance','K-SEC-03','Patroli & Kesiapsiagaan sesuai jadwal',NULL,'%',100.00,10.00,'higher_better','sec_patrol_pct',3),(109,7,'A','Performance','K-SEC-04','Respon terhadap insiden (Bintang 1-5)',NULL,'Bintang',5.00,10.00,'higher_better','sec_incident_rating',4),(110,7,'A','Performance','K-SEC-05','% Keluhan dari pengguna jasa (makin rendah makin baik)',NULL,'%',0.00,10.00,'lower_better','sec_complaint_pct',5),(111,7,'A','Performance','K-SEC-06','Jumlah keberhasilan menggagalkan kejadian kriminalitas',NULL,'%',100.00,5.00,'higher_better','sec_prevent_crime',6),(112,7,'A','Performance','K-SEC-07','Jumlah Kebobolan (makin sedikit makin baik)',NULL,'%',0.00,5.00,'lower_better','sec_breach_count',7),(113,7,'B','Sikap','S-01','Sikap dan Penampilan (Fisik prima & seragam rapi)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,8),(114,7,'B','Sikap','S-02','Komunikasi (Senyum sapa dan salam)',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,9),(115,7,'B','Sikap','S-03','Pelayanan (Sikap melayani tamu & civitas)',NULL,'SKALA 1-5',5.00,9.00,'higher_better',NULL,10),(116,7,'B','Sikap','S-04','Profesionalisme (Pengaturan, penjagaan, patroli)',NULL,'SKALA 1-5',5.00,8.00,'higher_better',NULL,11),(117,7,'B','Sikap','S-05','Loyalitas (Siap & sigap menerima tugas)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,12),(118,7,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',7.00,2.00,'lower_better','att_terlambat',13),(119,7,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',7.00,2.00,'lower_better','att_pulang_cepat',14),(120,7,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',7.00,1.00,'lower_better','att_lupa_catat',15),(121,7,'C','Absensi','AB-04','Tidak Hadir',NULL,'Hari/Thn',7.00,15.00,'lower_better','att_mangkir',16),(122,8,'A','Administrasi','K-KS-01','% Dokumen administrasi sekolah terarsip',NULL,'%',100.00,3.00,'higher_better','ks_admin_archived',1),(123,8,'A','Administrasi','K-KS-02','% Penyelesaian tugas sesuai deadline',NULL,'%',100.00,3.00,'higher_better','ks_admin_ontime',2),(124,8,'A','Hasil','K-KS-03','% Kehadiran guru (Target 90%)',NULL,'%',90.00,3.00,'higher_better','unit_teacher_attendance',3),(125,8,'A','Hasil','K-KS-04','% Capaian Tahfidz (Target 95%)',NULL,'%',95.00,4.00,'higher_better','unit_tahfidz_pct',4),(126,8,'A','Hasil','K-KS-05','% Capaian Tahsin (Target 65%)',NULL,'%',65.00,5.00,'higher_better','unit_tahsin_pct',5),(127,8,'A','Hasil','K-KS-06','Capaian nilai rata-rata Diniyah (Target 100%)',NULL,'%',100.00,5.00,'higher_better','unit_diniyah_avg',6),(128,8,'A','Hasil','K-KS-07','% Tunggakan sekolah (Makin rendah makin baik, target 0%)',NULL,'%',0.00,4.00,'lower_better','unit_tunggakan_ratio',7),(129,8,'A','Performance','K-KS-08','Jumlah siswa menjuarai OSN/O2SN/FLS2N tingkat kota',NULL,'Orang',3.00,3.00,'higher_better','unit_lomba_resmi_count',8),(130,8,'A','Performance','K-KS-09','% Guru dengan nilai KPI B atau A',NULL,'%',100.00,4.00,'higher_better','unit_guru_kpi_b',9),(131,8,'A','Performance','K-KS-10','% Siswa baru dalam SPMB vs Target Deputi',NULL,'%',100.00,4.00,'higher_better','unit_spmb_achievement',10),(132,8,'A','Performance','K-KS-11','% Siswa yang melanjutkan ke sekolah favorit',NULL,'%',45.00,4.00,'higher_better','unit_alumni_favorite',11),(133,8,'A','Hubungan','K-KS-12','Rata-rata Turn Over Talent (Target <= 5%, makin rendah makin baik)',NULL,'%',5.00,3.00,'lower_better','unit_turnover_rate',12),(134,8,'A','Hubungan','K-KS-13','% Kepuasan walimurid rata-rata unit',NULL,'%',85.00,3.00,'higher_better','unit_parent_survey_avg',13),(135,8,'A','SDM','K-KS-14','% Guru berprestasi',NULL,'%',5.00,3.00,'higher_better','unit_guru_berprestasi_pct',14),(136,8,'A','SDM','K-KS-15','% Kepuasan guru & karyawan terhadap Kepala Sekolah',NULL,'%',90.00,3.00,'higher_better','unit_staff_survey_avg',15),(137,8,'A','SDM','K-KS-16','% Kehadiran guru di kajian pekanan',NULL,'%',100.00,3.00,'higher_better','unit_kajian_guru_pct',16),(138,8,'A','SDM','K-KS-17','% Guru mencapai target hafalan tahunan (1/2 - 1 Juz)',NULL,'%',50.00,3.00,'higher_better','unit_teacher_tahfidz_pass',17),(139,8,'B','Sikap','S-01','Communication (Penyampaian ide/maksud jelas & dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,18),(140,8,'B','Sikap','S-02','Coaching & Counseling (Penanganan guru)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,19),(141,8,'B','Sikap','S-03','Problem Solving & Decision Making',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,20),(142,8,'B','Sikap','S-04','Teamwork',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,21),(143,8,'B','Sikap','S-05','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,22),(144,8,'B','Sikap','S-06','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,23),(145,8,'B','Sikap','S-07','Pengelolaan SDM',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,24),(146,8,'B','Sikap','S-08','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,25),(147,8,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',26),(148,8,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',27),(149,8,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',28),(150,8,'C','Absensi','AB-04','Mangkir',NULL,'Hari/Thn',6.00,5.00,'lower_better','att_mangkir',29),(151,9,'A','Administrasi','K-KS-01','% Dokumen administrasi sekolah terarsip',NULL,'%',100.00,3.00,'higher_better','ks_admin_archived',1),(152,9,'A','Administrasi','K-KS-02','% Penyelesaian tugas sesuai deadline',NULL,'%',100.00,3.00,'higher_better','ks_admin_ontime',2),(153,9,'A','Hasil','K-KS-03','% Kehadiran guru (Target 90%)',NULL,'%',90.00,3.00,'higher_better','unit_teacher_attendance',3),(154,9,'A','Hasil','K-KS-04','% Capaian Tahfidz (Target 95%)',NULL,'%',95.00,4.00,'higher_better','unit_tahfidz_pct',4),(155,9,'A','Hasil','K-KS-05','% Capaian Baca Latin (Target 90%)',NULL,'%',90.00,4.00,'higher_better','unit_baca_latin_pct',5),(156,9,'A','Hasil','K-KS-06','% Capaian National Kurikulum (Target 90%)',NULL,'%',90.00,4.00,'higher_better','unit_national_kurikulum_pct',6),(157,9,'A','Hasil','K-KS-07','% Tunggakan sekolah (Makin rendah makin baik, target 0%)',NULL,'%',0.00,4.00,'lower_better','unit_tunggakan_ratio',7),(158,9,'A','Performance','K-KS-08','Jumlah siswa menjuarai OSN/O2SN/FLS2N tingkat kota',NULL,'Orang',3.00,3.00,'higher_better','unit_lomba_resmi_count',8),(159,9,'A','Performance','K-KS-09','% Guru dengan nilai KPI B atau A',NULL,'%',100.00,4.00,'higher_better','unit_guru_kpi_b',9),(160,9,'A','Performance','K-KS-10','% Siswa baru dalam SPMB vs Target Deputi',NULL,'%',100.00,4.00,'higher_better','unit_spmb_achievement',10),(161,9,'A','Performance','K-KS-11','% Siswa yang melanjutkan ke sekolah favorit',NULL,'%',45.00,4.00,'higher_better','unit_alumni_favorite',11),(162,9,'A','Hubungan','K-KS-12','Rata-rata Turn Over Talent (Target <= 5%, makin rendah makin baik)',NULL,'%',5.00,4.00,'lower_better','unit_turnover_rate',12),(163,9,'A','Hubungan','K-KS-13','% Kepuasan walimurid rata-rata unit',NULL,'%',85.00,3.00,'higher_better','unit_parent_survey_avg',13),(164,9,'A','SDM','K-KS-14','% Guru berprestasi',NULL,'%',5.00,3.00,'higher_better','unit_guru_berprestasi_pct',14),(165,9,'A','SDM','K-KS-15','% Kepuasan guru & karyawan terhadap Kepala Sekolah',NULL,'%',90.00,3.00,'higher_better','unit_staff_survey_avg',15),(166,9,'A','SDM','K-KS-16','% Kehadiran guru di kajian pekanan',NULL,'%',100.00,3.00,'higher_better','unit_kajian_guru_pct',16),(167,9,'A','SDM','K-KS-17','% Guru mencapai target hafalan tahunan (1/2 - 1 Juz)',NULL,'%',50.00,3.00,'higher_better','unit_teacher_tahfidz_pass',17),(168,9,'B','Sikap','S-01','Communication (Penyampaian ide/maksud jelas & dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,18),(169,9,'B','Sikap','S-02','Coaching & Counseling (Penanganan guru)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,19),(170,9,'B','Sikap','S-03','Problem Solving & Decision Making',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,20),(171,9,'B','Sikap','S-04','Teamwork',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,21),(172,9,'B','Sikap','S-05','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,22),(173,9,'B','Sikap','S-06','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,23),(174,9,'B','Sikap','S-07','Pengelolaan SDM',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,24),(175,9,'B','Sikap','S-08','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,25),(176,9,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',26),(177,9,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',27),(178,9,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',28),(179,9,'C','Absensi','AB-04','Mangkir',NULL,'Hari/Thn',6.00,5.00,'lower_better','att_mangkir',29),(180,10,'A','Administrasi','K-KS-01','% Dokumen administrasi sekolah terarsip',NULL,'%',100.00,3.00,'higher_better','ks_admin_archived',1),(181,10,'A','Administrasi','K-KS-02','% Penyelesaian tugas sesuai deadline',NULL,'%',100.00,3.00,'higher_better','ks_admin_ontime',2),(182,10,'A','Hasil','K-KS-03','% Kehadiran guru (Target 90%)',NULL,'%',90.00,3.00,'higher_better','unit_teacher_attendance',3),(183,10,'A','Hasil','K-KS-04','% Capaian Tahfidz (Target 95%)',NULL,'%',95.00,4.00,'higher_better','unit_tahfidz_pct',4),(184,10,'A','Hasil','K-KS-05','% Capaian Tahsin (Target 65%)',NULL,'%',65.00,5.00,'higher_better','unit_tahsin_pct',5),(185,10,'A','Hasil','K-KS-06','Capaian nilai rata-rata Diniyah (Target 100%)',NULL,'%',100.00,5.00,'higher_better','unit_diniyah_avg',6),(186,10,'A','Hasil','K-KS-07','% Tunggakan sekolah (Makin rendah makin baik, target 0%)',NULL,'%',0.00,4.00,'lower_better','unit_tunggakan_ratio',7),(187,10,'A','Performance','K-KS-08','Jumlah siswa menjuarai OSN/O2SN/FLS2N tingkat kota',NULL,'Orang',3.00,3.00,'higher_better','unit_lomba_resmi_count',8),(188,10,'A','Performance','K-KS-09','% Guru dengan nilai KPI B atau A',NULL,'%',100.00,4.00,'higher_better','unit_guru_kpi_b',9),(189,10,'A','Performance','K-KS-10','% Siswa baru dalam SPMB vs Target Deputi',NULL,'%',100.00,4.00,'higher_better','unit_spmb_achievement',10),(190,10,'A','Performance','K-KS-11','% Siswa yang melanjutkan ke sekolah favorit',NULL,'%',45.00,4.00,'higher_better','unit_alumni_favorite',11),(191,10,'A','Hubungan','K-KS-12','Rata-rata Turn Over Talent',NULL,'%',5.00,2.00,'lower_better','unit_turnover_rate',12),(192,10,'A','Hubungan','K-KS-13','% Kepuasan walimurid rata-rata unit',NULL,'%',85.00,2.00,'higher_better','unit_parent_survey_avg',13),(193,10,'A','Hubungan','K-KS-13B','% Kepuasan siswa terhadap layanan pembelajaran',NULL,'%',85.00,2.00,'higher_better','unit_student_survey_avg',14),(194,10,'A','SDM','K-KS-14','% Guru berprestasi',NULL,'%',5.00,3.00,'higher_better','unit_guru_berprestasi_pct',14),(195,10,'A','SDM','K-KS-15','% Kepuasan guru & karyawan terhadap Kepala Sekolah',NULL,'%',90.00,3.00,'higher_better','unit_staff_survey_avg',15),(196,10,'A','SDM','K-KS-16','% Kehadiran guru di kajian pekanan',NULL,'%',100.00,3.00,'higher_better','unit_kajian_guru_pct',16),(197,10,'A','SDM','K-KS-17','% Guru mencapai target hafalan tahunan (1/2 - 1 Juz)',NULL,'%',50.00,3.00,'higher_better','unit_teacher_tahfidz_pass',17),(198,10,'B','Sikap','S-01','Communication (Penyampaian ide/maksud jelas & dipahami)',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,18),(199,10,'B','Sikap','S-02','Coaching & Counseling (Penanganan guru)',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,19),(200,10,'B','Sikap','S-03','Problem Solving & Decision Making',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,20),(201,10,'B','Sikap','S-04','Teamwork',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,21),(202,10,'B','Sikap','S-05','Sikap Empati & Sense of Belonging',NULL,'SKALA 1-5',5.00,3.00,'higher_better',NULL,22),(203,10,'B','Sikap','S-06','Loyalitas & Militansi',NULL,'SKALA 1-5',5.00,5.00,'higher_better',NULL,23),(204,10,'B','Sikap','S-07','Pengelolaan SDM',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,24),(205,10,'B','Sikap','S-08','Keteladanan & Disiplin',NULL,'SKALA 1-5',5.00,4.00,'higher_better',NULL,25),(206,10,'C','Absensi','AB-01','Datang Terlambat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_terlambat',26),(207,10,'C','Absensi','AB-02','Pulang Cepat',NULL,'Hari/Thn',6.00,2.00,'lower_better','att_pulang_cepat',27),(208,10,'C','Absensi','AB-03','Lupa Catat',NULL,'Hari/Thn',6.00,1.00,'lower_better','att_lupa_catat',28),(209,10,'C','Absensi','AB-04','Mangkir',NULL,'Hari/Thn',6.00,5.00,'lower_better','att_mangkir',29);
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `akfi_templates`
--

LOCK TABLES `akfi_templates` WRITE;
/*!40000 ALTER TABLE `akfi_templates` DISABLE KEYS */;
INSERT INTO `akfi_templates` VALUES (1,'GURU_MAPEL','Guru Mata Pelajaran (SD/SMP/SMA)',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(2,'GURU_TAHTA','Guru Tahfidz / Tahta (SD/SMP/SMA)',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(3,'GURU_WALAS','Guru Wali Kelas (SD/SMP/SMA)',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(4,'GURU_TK','Guru TK / KB',1,65.00,20.00,15.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(5,'TU','Tata Usaha',1,60.00,20.00,20.00,90.00,70.00,50.00,30.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(6,'CS','Cleaning Services',1,60.00,20.00,20.00,90.00,70.00,50.00,30.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(7,'SECURITY','Security',1,50.00,30.00,20.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(8,'KANIT_SD','Kepala Sekolah (SD)',1,60.00,30.00,10.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(9,'KANIT_TK','Kepala Sekolah (TK)',1,60.00,30.00,10.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(10,'KANIT_SMP_SMA','Kepala Sekolah (SMP & SMA)',1,60.00,30.00,10.00,91.00,81.00,61.00,31.00,11.00,'2026-09-23 18:43:17','2026-09-23 18:43:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alumni_tracking`
--

LOCK TABLES `alumni_tracking` WRITE;
/*!40000 ALTER TABLE `alumni_tracking` DISABLE KEYS */;
INSERT INTO `alumni_tracking` VALUES (1,5,4,'2024','Sekolah','SMA Negeri 1 Batam',1,'MIPA','Sekolah Favorit Kota Batam','2026-09-23 18:43:17'),(2,20,16,'2026','Sekolah','SMP AIS',1,'','','2026-09-25 21:15:59');
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
INSERT INTO `app_settings` VALUES ('app_favicon','uploads/logos/favicon_1790468135.png','2026-09-27 00:15:35'),('app_logo','uploads/logos/app_logo_1790375160.png','2026-09-25 22:26:00'),('app_name','Manajemen Sekolah Berbasis Komputer (MASEKO)','2026-09-27 00:15:35'),('app_short_name','MASEKO','2026-09-27 00:15:35'),('app_sub_name','Manajemen Sekolah Berbasis Komputer','2026-09-27 00:15:35');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=863 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES (1,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-23 19:47:30'),(2,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-23 19:50:14'),(3,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-23 19:50:20'),(4,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-23 19:52:03'),(5,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-23 19:52:09'),(6,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-23 19:52:19'),(7,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-23 19:52:24'),(8,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-23 19:53:35'),(9,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-23 19:53:48'),(10,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-23 19:53:52'),(11,NULL,'guest','none','::1','LOGIN_FAILED','users',NULL,NULL,'{\"username\":\"operator\",\"reason\":\"User not found\"}','2026-09-23 19:53:55'),(12,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-23 19:53:59'),(13,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-23 19:58:11'),(14,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-23 19:58:14'),(15,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-23 19:58:37'),(16,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-23 19:58:41'),(17,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-23 20:04:29'),(18,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-23 20:04:37'),(19,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-23 20:05:59'),(20,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-23 20:06:04'),(21,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-23 20:06:59'),(22,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-23 20:07:03'),(23,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-23 20:07:37'),(24,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-23 20:07:41'),(25,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-23 20:08:23'),(26,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-23 20:08:26'),(27,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-23 20:08:43'),(28,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-23 20:08:51'),(29,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-23 20:09:31'),(30,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-23 20:09:36'),(31,4,'operator.sd','operator_sekolah','::1','TOGGLE_USER_ACTIVE','users','17',NULL,'{\"is_active\":0}','2026-09-23 20:09:41'),(32,4,'operator.sd','operator_sekolah','::1','TOGGLE_USER_ACTIVE','users','17',NULL,'{\"is_active\":1}','2026-09-23 20:09:44'),(33,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-23 20:17:10'),(34,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-23 20:17:14'),(35,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-23 20:17:53'),(36,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-23 20:18:05'),(37,12,'waka.kurikulum','guru_mapel','::1','SWITCH_ROLE','roles','15',NULL,'{\"new_role\":\"guru_mapel\"}','2026-09-23 20:18:53'),(38,12,'waka.kurikulum','guru_mapel','::1','LOGOUT','users','12',NULL,NULL,'2026-09-23 20:20:04'),(39,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-23 20:20:14'),(40,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-23 20:20:38'),(41,27,'siswa.smp','siswa','::1','LOGIN_SUCCESS','users','27',NULL,'{\"active_role\":\"siswa\"}','2026-09-23 20:20:42'),(42,27,'siswa.smp','siswa','::1','LOGOUT','users','27',NULL,NULL,'2026-09-23 20:21:01'),(43,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-23 20:21:07'),(44,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-23 20:21:40'),(45,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-23 20:21:46'),(46,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-23 20:21:58'),(47,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 05:51:51'),(48,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 05:53:28'),(49,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-24 05:53:33'),(50,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-24 05:53:38'),(51,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 05:53:45'),(52,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 06:37:09'),(53,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 06:37:13'),(54,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 06:38:31'),(55,5,'operator.tk','operator_sekolah','::1','LOGIN_SUCCESS','users','5',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 06:38:37'),(56,5,'operator.tk','operator_sekolah','::1','LOGOUT','users','5',NULL,NULL,'2026-09-24 06:39:43'),(57,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 06:39:47'),(58,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 06:40:07'),(59,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 06:40:14'),(60,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 07:19:59'),(61,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 07:20:05'),(62,18,'guru.mapel','guru_mapel','::1','SAVE_STUDENT_GRADES','student_grades','3',NULL,'{\"subject_id\":10,\"month\":9,\"count\":3}','2026-09-24 07:21:17'),(63,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-24 07:21:26'),(64,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-24 07:21:30'),(65,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-24 07:23:15'),(66,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 07:23:23'),(67,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 07:31:42'),(68,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 07:31:47'),(69,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 07:32:54'),(70,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-24 07:32:57'),(71,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-24 07:34:12'),(72,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-24 07:34:22'),(73,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-24 07:35:12'),(74,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 07:35:17'),(75,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 07:35:37'),(76,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 07:35:42'),(77,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 07:38:49'),(78,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 07:38:52'),(79,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 07:38:56'),(80,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 07:39:02'),(81,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 07:39:16'),(82,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 07:39:24'),(83,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 07:40:00'),(84,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 07:40:03'),(85,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 07:41:12'),(86,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-24 07:41:18'),(87,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-24 07:42:18'),(88,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-24 07:42:25'),(89,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-24 07:43:41'),(90,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-24 07:43:44'),(91,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-24 07:46:13'),(92,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-24 07:46:31'),(93,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-24 07:48:33'),(94,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-24 07:49:39'),(95,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-24 07:49:45'),(96,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 07:49:55'),(97,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 08:19:50'),(98,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-24 08:19:54'),(99,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-24 08:21:50'),(100,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 08:21:55'),(101,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 08:24:17'),(102,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 08:24:34'),(103,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 08:25:30'),(104,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 08:25:43'),(105,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 08:26:14'),(106,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-24 08:26:18'),(107,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-24 08:27:00'),(108,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-24 08:28:36'),(109,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-24 08:31:25'),(110,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-24 08:31:28'),(111,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-24 08:35:18'),(112,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 08:35:25'),(113,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 08:50:50'),(114,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 08:50:54'),(115,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 08:52:21'),(116,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 08:52:26'),(117,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 08:52:32'),(118,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 08:52:36'),(119,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 08:55:51'),(120,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 08:55:56'),(121,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 08:56:22'),(122,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 08:56:25'),(123,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 08:56:57'),(124,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 08:57:01'),(125,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 08:58:37'),(126,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 08:58:42'),(127,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 09:00:11'),(128,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 09:00:16'),(129,4,'operator.sd','operator_sekolah','::1','CREATE_USER_ACCOUNT','users','28',NULL,'{\"username\":\"syahidi\",\"unit_id\":3,\"roles\":[15]}','2026-09-24 09:02:37'),(130,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 09:02:43'),(131,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 09:02:48'),(132,1,'operator.yayasan','operator_yayasan','::1','UPDATE_EMPLOYEE','employees','25','{\"id\":25,\"user_id\":28,\"nipy\":\"SD-SYAH-637\",\"nik\":null,\"nama\":\"Syahidi Rahman, S,Pd, Gr\",\"gender\":\"L\",\"birthplace\":null,\"birthdate\":null,\"tmt\":\"2026-09-24\",\"level_posisi\":\"Guru Mapel\",\"position\":\"Guru Mapel\",\"unit_id\":3,\"atasan_langsung_id\":null,\"no_telp_atasan\":null,\"contact_phone\":\"08566691395\",\"email\":\"syahidi@aisbatam.sch.id\",\"status\":\"Aktif\",\"is_deleted\":0,\"created_at\":\"2026-09-24 09:02:37\",\"updated_at\":\"2026-09-24 09:02:37\"}','{\"csrf_token\":\"ecfafa051e29a86234383b3c90bd971ee2ab70bf914691f2dfda2c22044b492a\",\"id\":\"25\",\"nipy\":\"SD-SYAH-637\",\"nik\":\"\",\"nama\":\"Syahidi Rahman, S,Pd, Gr\",\"gender\":\"L\",\"tmt\":\"2026-09-24\",\"level_posisi\":\"Guru Mapel\",\"unit_id\":\"3\",\"atasan_langsung_id\":\"3\",\"no_telp_atasan\":\"\",\"contact_phone\":\"08566691395\",\"email\":\"syahidi@aisbatam.sch.id\"}','2026-09-24 09:03:27'),(133,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 09:40:53'),(134,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 09:40:57'),(135,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 09:43:00'),(136,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 09:43:05'),(137,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 09:45:17'),(138,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 09:45:21'),(139,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 09:45:55'),(140,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 09:46:03'),(141,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 09:58:04'),(142,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 09:58:07'),(143,1,'operator.yayasan','operator_yayasan','::1','CREATE_ACADEMIC_YEAR','academic_years','2',NULL,'{\"name\":\"2026\\/2027\",\"semester\":\"Ganjil\"}','2026-09-24 09:59:19'),(144,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 10:00:46'),(145,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 10:00:51'),(146,4,'operator.sd','operator_sekolah','::1','CREATE_STUDENT','students','6',NULL,'{\"csrf_token\":\"7d2792c1585f76130b37ad3942a5a6e7d4bca244c1cb46083118cf8f6480f49b\",\"id\":\"0\",\"nis\":\"0123123\",\"nisn\":\"0123123\",\"nama\":\"Sarah Batrisya\",\"gender\":\"P\",\"nik\":\"1171092110939001\",\"no_kk\":\"2171092110939001\",\"class_id\":\"3\",\"father_name\":\"Syahidi Rahman\",\"parent_phone\":\"08566691395\",\"parent_email\":\"saidi165@gmail.com\"}','2026-09-24 10:02:04'),(147,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 10:02:31'),(148,29,'2171092110939001','orang_tua','::1','LOGIN_SUCCESS','users','29',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-24 10:02:37'),(149,29,'2171092110939001','orang_tua','::1','CHANGE_PASSWORD','users','29',NULL,NULL,'2026-09-24 10:02:44'),(150,29,'2171092110939001','orang_tua','::1','LOGOUT','users','29',NULL,NULL,'2026-09-24 10:15:09'),(151,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 10:15:13'),(152,4,'operator.sd','operator_sekolah','::1','CREATE_STUDENT','students','7',NULL,'{\"csrf_token\":\"7ef695ef699e245ef20651aa82bffc6e40dcad73b3e1bef8c25a1dabcd91e20a\",\"id\":\"0\",\"nis\":\"0456456\",\"nisn\":\"0456456\",\"nama\":\"Batrisya Al Sarah\",\"gender\":\"L\",\"nik\":\"2171091221454\",\"no_kk\":\"2171092110939001\",\"class_id\":\"4\",\"father_name\":\"Syahidi Rahman\",\"parent_phone\":\"08566691395\",\"parent_email\":\"saidi165@gmail.com\"}','2026-09-24 10:16:18'),(153,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 10:16:28'),(154,29,'2171092110939001','orang_tua','::1','LOGIN_SUCCESS','users','29',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-24 10:16:33'),(155,29,'2171092110939001','orang_tua','::1','LOGOUT','users','29',NULL,NULL,'2026-09-24 10:16:45'),(156,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 10:16:48'),(157,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 10:18:28'),(158,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-24 10:18:32'),(159,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-24 10:19:47'),(160,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-24 10:19:54'),(161,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-24 10:20:19'),(162,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 10:20:26'),(163,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 10:25:00'),(164,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 10:25:03'),(165,4,'operator.sd','operator_sekolah','::1','CREATE_USER_ACCOUNT','users','30',NULL,'{\"username\":\"rambejaya\",\"unit_id\":3,\"roles\":[16,15,14]}','2026-09-24 10:25:54'),(166,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 10:25:59'),(167,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 10:26:04'),(168,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 10:26:33'),(169,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 10:26:38'),(170,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 10:31:46'),(171,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 10:31:53'),(172,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 10:34:14'),(173,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 10:34:23'),(174,8,'kepsek.sd','kepala_sekolah','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','4',NULL,'{\"kinerja\":65,\"sikap\":20,\"absensi\":14.7,\"total\":99.7,\"grade\":\"A\"}','2026-09-24 10:34:44'),(175,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 10:37:21'),(176,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 10:37:26'),(177,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 10:37:52'),(178,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 10:37:57'),(179,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 10:41:40'),(180,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 10:41:44'),(181,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-24 10:45:48'),(182,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 10:45:51'),(183,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 11:22:42'),(184,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 11:43:27'),(185,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 11:43:31'),(186,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 11:43:36'),(187,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 11:43:48'),(188,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 11:43:52'),(189,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 12:00:35'),(190,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 12:00:39'),(191,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-24 12:00:48'),(192,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-24 12:00:54'),(193,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-24 12:01:00'),(194,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 12:01:03'),(195,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 12:01:18'),(196,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 12:01:30'),(197,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 12:02:53'),(198,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 12:02:57'),(199,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 12:03:21'),(200,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-24 12:03:25'),(201,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-24 12:03:40'),(202,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-24 12:03:48'),(203,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-24 12:04:03'),(204,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-24 12:04:07'),(205,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-24 12:04:27'),(206,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-24 12:04:32'),(207,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-24 12:07:45'),(208,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 12:07:48'),(209,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 12:08:18'),(210,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 12:08:22'),(211,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-24 12:09:16'),(212,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 12:09:20'),(213,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 12:09:36'),(214,NULL,'guest','none','::1','LOGIN_FAILED','users',NULL,NULL,'{\"username\":\"operator\",\"reason\":\"User not found\"}','2026-09-24 12:23:49'),(215,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 12:23:53'),(216,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 12:26:23'),(217,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-24 12:26:26'),(218,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-24 12:27:22'),(219,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 12:27:25'),(220,17,'walas.sd','guru_mapel','::1','SWITCH_ROLE','roles','15',NULL,'{\"new_role\":\"guru_mapel\"}','2026-09-24 12:27:44'),(221,17,'walas.sd','guru_mapel','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 12:27:51'),(222,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 12:27:56'),(223,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-24 12:28:33'),(224,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 12:28:37'),(225,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 13:19:35'),(226,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 17:55:00'),(227,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 17:55:17'),(228,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 17:55:28'),(229,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 17:59:27'),(230,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-24 17:59:33'),(231,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-24 17:59:42'),(232,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-24 17:59:47'),(233,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-24 18:00:26'),(234,NULL,'guest','none','::1','LOGIN_FAILED','users','28',NULL,'{\"attempts\":1}','2026-09-24 18:00:35'),(235,NULL,'guest','none','::1','LOGIN_FAILED','users',NULL,NULL,'{\"username\":\"salehrambe\",\"reason\":\"User not found\"}','2026-09-24 18:00:42'),(236,29,'2171092110939001','orang_tua','::1','LOGIN_SUCCESS','users','29',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-24 18:00:56'),(237,29,'2171092110939001','orang_tua','::1','LOGOUT','users','29',NULL,NULL,'2026-09-24 18:01:03'),(238,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 18:01:08'),(239,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 18:01:36'),(240,NULL,'guest','none','::1','LOGIN_FAILED','users','28',NULL,'{\"attempts\":2}','2026-09-24 18:01:41'),(241,NULL,'guest','none','::1','LOGIN_FAILED','users','28',NULL,'{\"attempts\":3}','2026-09-24 18:01:45'),(242,NULL,'guest','none','::1','LOGIN_FAILED','users','28',NULL,'{\"attempts\":4}','2026-09-24 18:01:48'),(243,NULL,'guest','none','::1','LOGIN_FAILED','users','28',NULL,'{\"attempts\":5}','2026-09-24 18:01:53'),(244,NULL,'guest','none','::1','LOGIN_FAILED','users','30',NULL,'{\"attempts\":1}','2026-09-24 18:02:03'),(245,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 18:02:07'),(246,4,'operator.sd','operator_sekolah','::1','EDIT_USER_ACCOUNT','users','28','{\"id\":28,\"username\":\"syahidi\",\"password_hash\":\"$2y$10$7GK7V7aa3vuPxmWUbn\\/3U.VtofNSiT.j8cGFOewl5Eod5kCDcsgt.\",\"email\":\"syahidi@aisbatam.sch.id\",\"full_name\":\"Syahidi Rahman, S,Pd, Gr\",\"phone\":\"08566691395\",\"unit_id\":3,\"is_active\":1,\"force_change_password\":0,\"failed_login_attempts\":5,\"lockout_until\":\"2026-09-24 18:16:53\",\"last_login_at\":null,\"created_at\":\"2026-09-24 09:02:37\",\"updated_at\":\"2026-09-24 18:01:53\"}','{\"csrf_token\":\"c8037128fc73300e88b759f06f5455e4b4b4e2e89a47c2f12ee98a5ca52f582f\",\"id\":\"28\",\"action\":\"edit_account\",\"username\":\"syahidi\",\"full_name\":\"Syahidi Rahman, S,Pd, Gr\",\"is_active\":\"1\",\"unit_id\":\"3\",\"email\":\"syahidi@aisbatam.sch.id\",\"phone\":\"08566691395\",\"password\":\"D@RKknight3\",\"roles\":[\"15\"]}','2026-09-24 18:02:23'),(247,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 18:02:25'),(248,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 18:02:47'),(249,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 18:39:02'),(250,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 18:39:06'),(251,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 18:39:10'),(252,28,'syahidi','guru_mapel','::1','LOGIN_SUCCESS','users','28',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 18:39:18'),(253,28,'syahidi','guru_mapel','::1','LOGOUT','users','28',NULL,NULL,'2026-09-24 18:42:17'),(254,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 18:42:25'),(255,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 18:52:25'),(256,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-24 18:52:38'),(257,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-24 18:53:59'),(258,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-24 18:54:05'),(259,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-24 18:54:30'),(260,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 18:54:37'),(261,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 18:55:04'),(262,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 18:55:17'),(263,4,'operator.sd','operator_sekolah','::1','ASSIGN_ROLE','user_roles','28',NULL,'{\"role_id\":11,\"role_name\":\"guru_supervisor\"}','2026-09-24 18:56:31'),(264,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 18:56:39'),(265,28,'syahidi','guru_mapel','::1','LOGIN_SUCCESS','users','28',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 18:56:45'),(266,28,'syahidi','guru_supervisor','::1','SWITCH_ROLE','roles','11',NULL,'{\"new_role\":\"guru_supervisor\"}','2026-09-24 18:56:52'),(267,28,'syahidi','guru_supervisor','::1','SCHEDULE_SUPERVISI','supervisi_sessions','37',NULL,'{\"teacher_id\":26,\"date\":\"2026-09-24\"}','2026-09-24 18:57:15'),(268,28,'syahidi','guru_supervisor','::1','GRADE_SUPERVISI','supervisi_sessions','37',NULL,'{\"total_score\":100,\"predicate\":\"SANGAT BAIK\"}','2026-09-24 18:57:41'),(269,28,'syahidi','guru_mapel','::1','SWITCH_ROLE','roles','15',NULL,'{\"new_role\":\"guru_mapel\"}','2026-09-24 18:57:54'),(270,28,'syahidi','guru_mapel','::1','LOGOUT','users','28',NULL,NULL,'2026-09-24 18:57:58'),(271,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-24 18:58:02'),(272,12,'waka.kurikulum','waka_kurikulum','::1','SAVE_TEACHER_ATTENDANCE','employee_attendance','3',NULL,'{\"month\":9,\"count\":11}','2026-09-24 18:58:25'),(273,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-24 18:59:15'),(274,NULL,'guest','none','::1','LOGIN_FAILED','users','30',NULL,'{\"attempts\":2}','2026-09-24 18:59:23'),(275,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 18:59:33'),(276,4,'operator.sd','operator_sekolah','::1','EDIT_USER_ACCOUNT','users','30','{\"id\":30,\"username\":\"rambejaya\",\"password_hash\":\"$2y$10$I2Dy.iETDk.HruibWoFgcOa8UxarRnPOojRY2WXWRoc\\/foHuVhrYC\",\"email\":\"salehrambe@aisbatam.sch.id\",\"full_name\":\"Ahmad Saleh Rambe\",\"phone\":null,\"unit_id\":3,\"is_active\":1,\"force_change_password\":0,\"failed_login_attempts\":2,\"lockout_until\":null,\"last_login_at\":null,\"created_at\":\"2026-09-24 10:25:54\",\"updated_at\":\"2026-09-24 18:59:23\"}','{\"csrf_token\":\"89af3812a9e19b9b96049f00d1b32a43ef9449e4be3f614563eb2a067aa7db43\",\"id\":\"30\",\"action\":\"edit_account\",\"username\":\"rambejaya\",\"full_name\":\"Ahmad Saleh Rambe\",\"is_active\":\"1\",\"unit_id\":\"3\",\"email\":\"salehrambe@aisbatam.sch.id\",\"phone\":\"\",\"password\":\"D@RKknight3\",\"roles\":[\"16\",\"15\",\"14\"]}','2026-09-24 18:59:44'),(277,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 18:59:46'),(278,30,'rambejaya','guru_diniyah','::1','LOGIN_SUCCESS','users','30',NULL,'{\"active_role\":\"guru_diniyah\"}','2026-09-24 18:59:54'),(279,30,'rambejaya','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-24 19:00:45'),(280,30,'rambejaya','guru_diniyah','::1','SWITCH_ROLE','roles','16',NULL,'{\"new_role\":\"guru_diniyah\"}','2026-09-24 19:02:11'),(281,30,'rambejaya','guru_diniyah','::1','LOGOUT','users','30',NULL,NULL,'2026-09-24 19:02:18'),(282,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 19:02:23'),(283,4,'operator.sd','operator_sekolah','::1','ASSIGN_TEACHER_SUBJECT','teacher_subjects','0',NULL,'{\"employee_id\":26,\"subject_id\":10,\"class_id\":3,\"unit_id\":3}','2026-09-24 19:02:51'),(284,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 19:12:11'),(285,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 19:12:14'),(286,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 19:12:35'),(287,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 19:12:38'),(288,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 19:12:50'),(289,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 19:12:58'),(290,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 19:14:46'),(291,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 19:14:49'),(292,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 19:15:04'),(293,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 19:15:12'),(294,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 19:16:04'),(295,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 19:16:08'),(296,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 19:16:56'),(297,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-24 19:17:00'),(298,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-24 19:18:26'),(299,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-24 19:18:31'),(300,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-24 19:19:50'),(301,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 19:19:55'),(302,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 19:55:35'),(303,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 19:55:40'),(304,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 19:57:05'),(305,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-24 19:57:10'),(306,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-24 20:05:25'),(307,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 20:05:30'),(308,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 20:10:33'),(309,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-24 20:10:37'),(310,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-24 20:10:46'),(311,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-24 20:10:51'),(312,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-24 20:12:03'),(313,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 20:12:10'),(314,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-24 20:12:30'),(315,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-24 20:12:36'),(316,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-24 20:43:56'),(317,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 20:44:04'),(318,4,'operator.sd','operator_sekolah','::1','CREATE_USER_ACCOUNT','users','31',NULL,'{\"username\":\"azmi\",\"unit_id\":3,\"roles\":[17,13]}','2026-09-24 20:45:08'),(319,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 20:45:18'),(320,31,'azmi','guru_tahfidz','::1','LOGIN_SUCCESS','users','31',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-24 20:45:27'),(321,31,'azmi','koor_tahfidz','::1','SWITCH_ROLE','roles','13',NULL,'{\"new_role\":\"koor_tahfidz\"}','2026-09-24 20:45:46'),(322,31,'azmi','guru_tahfidz','::1','SWITCH_ROLE','roles','17',NULL,'{\"new_role\":\"guru_tahfidz\"}','2026-09-24 20:52:48'),(323,31,'azmi','guru_tahfidz','::1','LOGOUT','users','31',NULL,NULL,'2026-09-24 20:52:53'),(324,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 20:53:03'),(325,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-24 20:54:25'),(326,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-24 20:54:32'),(327,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-24 20:54:42'),(328,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-24 20:54:49'),(329,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-24 21:21:56'),(330,31,'azmi','guru_tahfidz','::1','LOGIN_SUCCESS','users','31',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-24 21:22:07'),(331,31,'azmi','koor_tahfidz','::1','SWITCH_ROLE','roles','13',NULL,'{\"new_role\":\"koor_tahfidz\"}','2026-09-24 21:22:17'),(332,31,'azmi','koor_tahfidz','::1','LOGOUT','users','31',NULL,NULL,'2026-09-24 21:24:10'),(333,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 21:26:03'),(334,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 21:26:43'),(335,9,'kepsek.tk','kepala_sekolah','::1','LOGIN_SUCCESS','users','9',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-24 21:26:51'),(336,9,'kepsek.tk','kepala_sekolah','::1','LOGOUT','users','9',NULL,NULL,'2026-09-24 21:28:19'),(337,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 21:28:23'),(338,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 21:28:36'),(339,5,'operator.tk','operator_sekolah','::1','LOGIN_SUCCESS','users','5',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 21:28:42'),(340,5,'operator.tk','operator_sekolah','::1','LOGOUT','users','5',NULL,NULL,'2026-09-24 21:29:36'),(341,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 21:29:40'),(342,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 21:32:30'),(343,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 21:32:36'),(344,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 21:33:37'),(345,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 21:33:41'),(346,12,'guest','operator_sekolah','127.0.0.1','APPROVE_ACADEMIC_YEAR','academic_year_approvals','17','{\"id\":17,\"academic_year_id\":2,\"unit_id\":3,\"status\":\"Menunggu\",\"rejection_reason\":null,\"decided_by_user_id\":null,\"updated_at\":\"2026-09-24 09:59:19\"}','{\"status\":\"Tidak Setuju\",\"reason\":\"Input nilai ledger kelas 6 dan pencetakan rapor akhir santri masih dalam proses penyelesaian.\"}','2026-09-24 21:40:08'),(347,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 21:41:05'),(348,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 21:41:13'),(349,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 21:43:13'),(350,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-24 21:43:21'),(351,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-24 21:44:06'),(352,27,'siswa.smp','siswa','::1','LOGIN_SUCCESS','users','27',NULL,'{\"active_role\":\"siswa\"}','2026-09-24 21:44:10'),(353,27,'siswa.smp','siswa','::1','LOGOUT','users','27',NULL,NULL,'2026-09-24 21:44:24'),(354,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-24 21:44:28'),(355,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-24 21:45:00'),(356,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-24 21:45:04'),(357,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-24 21:45:46'),(358,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-24 21:45:53'),(359,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-24 21:47:32'),(360,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-24 21:47:46'),(361,4,'operator.sd','operator_sekolah','::1','ASSIGN_ROLE','user_roles','28',NULL,'{\"role_id\":14,\"role_name\":\"walas\"}','2026-09-24 21:48:40'),(362,4,'operator.sd','operator_sekolah','::1','CREATE_CLASS','classes','12',NULL,NULL,'2026-09-24 21:49:02'),(363,4,'operator.sd','operator_sekolah','::1','UPDATE_STUDENT','students','11','{\"id\":11,\"nis\":\"20190304\",\"nisn\":null,\"nik\":null,\"no_kk\":\"2171000000000000\",\"nama\":\"Khadijah Putri Wardani\",\"gender\":\"P\",\"birthplace\":null,\"birthdate\":null,\"address\":null,\"father_name\":null,\"mother_name\":null,\"parent_phone\":null,\"parent_email\":null,\"parent_user_id\":null,\"student_user_id\":null,\"unit_id\":3,\"status\":\"Aktif\",\"created_at\":\"2026-09-24 20:36:59\",\"updated_at\":\"2026-09-24 20:36:59\"}','{\"csrf_token\":\"bd45659de29aec66854130059121899f1e8b8b8e740b5292f856f889033e9715\",\"id\":\"11\",\"nis\":\"20190304\",\"nisn\":\"\",\"nama\":\"Khadijah Putri Wardani\",\"gender\":\"P\",\"nik\":\"\",\"no_kk\":\"2171000000000000\",\"class_id\":\"12\",\"father_name\":\"\",\"parent_phone\":\"\",\"parent_email\":\"\"}','2026-09-24 21:49:29'),(364,12,'guest','operator_sekolah','127.0.0.1','ASSIGN_STUDENT_CLASS','class_students','11',NULL,'{\"class_id\":9}','2026-09-24 21:56:25'),(365,4,'operator.sd','operator_sekolah','::1','UPDATE_STUDENT','students','11','{\"id\":11,\"nis\":\"20190304\",\"nisn\":\"\",\"nik\":\"\",\"no_kk\":\"2171000000000000\",\"nama\":\"Khadijah Putri Wardani\",\"gender\":\"P\",\"birthplace\":\"\",\"birthdate\":null,\"address\":\"\",\"father_name\":\"\",\"mother_name\":\"\",\"parent_phone\":\"\",\"parent_email\":\"\",\"parent_user_id\":35,\"student_user_id\":null,\"unit_id\":3,\"status\":\"Aktif\",\"created_at\":\"2026-09-24 20:36:59\",\"updated_at\":\"2026-09-24 21:49:29\"}','{\"csrf_token\":\"bd45659de29aec66854130059121899f1e8b8b8e740b5292f856f889033e9715\",\"id\":\"11\",\"nis\":\"20190304\",\"nisn\":\"\",\"nama\":\"Khadijah Putri Wardani\",\"gender\":\"P\",\"nik\":\"\",\"no_kk\":\"2171000000000000\",\"class_id\":\"12\",\"father_name\":\"\",\"parent_phone\":\"\",\"parent_email\":\"\"}','2026-09-24 21:58:18'),(366,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-24 21:59:33'),(367,28,'syahidi','guru_mapel','::1','LOGIN_SUCCESS','users','28',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-24 21:59:43'),(368,28,'syahidi','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-24 22:00:27'),(369,28,'syahidi','guru_mapel','::1','SWITCH_ROLE','roles','15',NULL,'{\"new_role\":\"guru_mapel\"}','2026-09-24 22:02:23'),(370,28,'syahidi','guru_mapel','::1','LOGOUT','users','28',NULL,NULL,'2026-09-25 03:52:55'),(371,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-25 03:53:02'),(372,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-25 03:53:18'),(373,28,'syahidi','guru_mapel','::1','LOGIN_SUCCESS','users','28',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-25 03:53:24'),(374,28,'syahidi','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-25 03:53:44'),(375,28,'syahidi','guru_supervisor','::1','SWITCH_ROLE','roles','11',NULL,'{\"new_role\":\"guru_supervisor\"}','2026-09-25 03:55:00'),(376,28,'syahidi','guru_supervisor','::1','LOGOUT','users','28',NULL,NULL,'2026-09-25 03:55:30'),(377,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 03:55:34'),(378,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 03:55:55'),(379,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 03:56:00'),(380,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 04:03:45'),(381,30,'rambejaya','guru_diniyah','::1','LOGIN_SUCCESS','users','30',NULL,'{\"active_role\":\"guru_diniyah\"}','2026-09-25 04:03:51'),(382,30,'rambejaya','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-25 04:14:59'),(383,30,'rambejaya','walas','::1','LOGOUT','users','30',NULL,NULL,'2026-09-25 04:15:29'),(384,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 04:15:32'),(385,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 04:16:01'),(386,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-25 04:16:07'),(387,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-25 04:36:59'),(388,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 04:37:07'),(389,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 04:46:29'),(390,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-25 04:46:32'),(391,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-25 05:14:10'),(392,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:14:17'),(393,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 05:21:22'),(394,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-25 05:21:25'),(395,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-25 05:21:43'),(396,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:21:46'),(397,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 05:22:30'),(398,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-25 05:22:37'),(399,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-25 05:23:20'),(400,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:23:26'),(401,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 05:23:31'),(402,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:23:36'),(403,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 05:26:01'),(404,30,'rambejaya','guru_diniyah','::1','LOGIN_SUCCESS','users','30',NULL,'{\"active_role\":\"guru_diniyah\"}','2026-09-25 05:26:10'),(405,30,'rambejaya','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-25 05:26:15'),(406,30,'rambejaya','walas','::1','LOGOUT','users','30',NULL,NULL,'2026-09-25 05:35:16'),(407,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:35:19'),(408,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 05:35:43'),(409,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:35:46'),(410,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 05:36:27'),(411,31,'azmi','guru_tahfidz','::1','LOGIN_SUCCESS','users','31',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-25 05:36:37'),(412,31,'azmi','koor_tahfidz','::1','SWITCH_ROLE','roles','13',NULL,'{\"new_role\":\"koor_tahfidz\"}','2026-09-25 05:36:47'),(413,31,'azmi','guru_tahfidz','::1','SWITCH_ROLE','roles','17',NULL,'{\"new_role\":\"guru_tahfidz\"}','2026-09-25 05:36:49'),(414,31,'azmi','guru_tahfidz','::1','LOGOUT','users','31',NULL,NULL,'2026-09-25 05:38:20'),(415,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-25 05:38:24'),(416,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-25 05:39:12'),(417,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:40:16'),(418,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 05:43:18'),(419,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-25 05:43:22'),(420,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-25 05:44:44'),(421,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 05:44:47'),(422,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 06:05:37'),(423,31,'azmi','guru_tahfidz','::1','LOGIN_SUCCESS','users','31',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-25 06:05:43'),(424,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-25 07:16:06'),(425,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-25 07:20:18'),(426,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-25 07:20:21'),(427,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-25 07:20:28'),(428,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-25 07:20:31'),(429,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-25 07:21:37'),(430,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-25 07:21:43'),(431,4,'operator.sd','operator_sekolah','::1','UPDATE_SPMB_ACTUAL','spmb_targets','3',NULL,'{\"actual_count\":80,\"unit_id\":3,\"notes\":\"\"}','2026-09-25 07:23:00'),(432,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-25 07:23:13'),(433,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 07:23:17'),(434,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 07:29:37'),(435,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-25 07:29:40'),(436,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-25 07:31:06'),(437,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-25 07:31:10'),(438,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-25 07:32:50'),(439,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-25 07:32:53'),(440,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-25 07:33:03'),(441,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-25 07:33:09'),(442,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-25 07:34:06'),(443,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-25 07:34:09'),(444,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-25 07:34:37'),(445,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-25 07:34:41'),(446,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-25 07:35:05'),(447,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-25 07:35:11'),(448,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-25 07:35:19'),(449,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-25 07:35:27'),(450,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-25 07:42:11'),(451,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-25 07:42:17'),(452,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-25 07:43:11'),(453,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-25 07:43:15'),(454,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-25 07:44:15'),(455,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-25 07:44:20'),(456,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-25 07:45:30'),(457,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-25 07:45:34'),(458,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-25 07:45:47'),(459,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-25 07:45:53'),(460,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-25 07:45:57'),(461,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-25 07:46:00'),(462,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-25 07:46:34'),(463,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-25 09:56:08'),(464,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-25 09:56:43'),(465,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 09:56:46'),(466,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 09:59:08'),(467,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-25 09:59:11'),(468,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-25 09:59:40'),(469,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-25 09:59:44'),(470,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-25 10:00:11'),(471,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-25 10:00:14'),(472,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-25 10:01:25'),(473,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-25 10:01:28'),(474,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-25 10:01:43'),(475,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-25 10:01:46'),(476,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-25 10:02:04'),(477,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-25 10:02:08'),(478,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-25 10:02:22'),(479,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-25 10:02:26'),(480,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-25 10:11:01'),(481,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 10:11:04'),(482,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 11:51:09'),(483,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 11:51:13'),(484,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 11:55:07'),(485,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-25 11:55:12'),(486,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-25 11:56:35'),(487,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 11:56:40'),(488,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 11:57:35'),(489,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 11:57:41'),(490,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 11:58:17'),(491,30,'rambejaya','guru_diniyah','::1','LOGIN_SUCCESS','users','30',NULL,'{\"active_role\":\"guru_diniyah\"}','2026-09-25 11:58:26'),(492,30,'rambejaya','guru_diniyah','::1','LOGOUT','users','30',NULL,NULL,'2026-09-25 15:05:20'),(493,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 15:05:26'),(494,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-25 15:07:51'),(495,NULL,'guest','none','::1','LOGIN_FAILED','users',NULL,NULL,'{\"username\":\"operator\",\"reason\":\"User not found\"}','2026-09-25 16:03:12'),(496,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-25 16:03:17'),(497,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-25 20:57:58'),(498,1,'operator.yayasan','operator_yayasan','::1','CREATE_UNIT','units','16',NULL,'{\"csrf_token\":\"90d9674dc68625e8b52f0b726d97af30c8c6184a4cc2bfeeed2aa8b2ccca2815\",\"id\":\"0\",\"code\":\"SD-01\",\"level\":\"SD\",\"name\":\"SD ALKAHFI ISLAMIC SCHOOL\",\"kop_subtitle\":\"SD ALKAHFI ISLAMIC SCHOOL\",\"phone\":\"0000000000\",\"email\":\"humas@aisbatam.sch.id\",\"address\":\"Jl Ruko Imperium Superblock\",\"bank_name\":\"BSI\",\"bank_account\":\"1033667414\",\"bank_holder\":\"Syahidi Rahman\"}','2026-09-25 20:59:22'),(499,1,'operator.yayasan','operator_yayasan','::1','CREATE_EMPLOYEE','employees','31',NULL,'{\"csrf_token\":\"90d9674dc68625e8b52f0b726d97af30c8c6184a4cc2bfeeed2aa8b2ccca2815\",\"id\":\"0\",\"nipy\":\"08566691395\",\"nik\":\"2171092110939001\",\"nama\":\"Putra Perdana Tanjung\",\"gender\":\"L\",\"tmt\":\"2026-09-25\",\"level_posisi\":\"Guru Kepala Sekolah\",\"unit_id\":\"16\",\"atasan_langsung_id\":\"2\",\"no_telp_atasan\":\"\",\"contact_phone\":\"\",\"email\":\"\",\"username\":\"putra.perdana.tanjung\",\"password\":\"D@RKknight3\",\"user_is_active\":\"1\",\"roles\":[\"20\"]}','2026-09-25 21:03:00'),(500,1,'operator.yayasan','operator_yayasan','::1','CREATE_EMPLOYEE','employees','32',NULL,'{\"csrf_token\":\"90d9674dc68625e8b52f0b726d97af30c8c6184a4cc2bfeeed2aa8b2ccca2815\",\"id\":\"0\",\"nipy\":\"0000000000\",\"nik\":\"0000000000\",\"nama\":\"Faisal Danu\",\"gender\":\"L\",\"tmt\":\"2026-09-25\",\"level_posisi\":\"Guru Kelas\",\"unit_id\":\"16\",\"atasan_langsung_id\":\"31\",\"no_telp_atasan\":\"\",\"contact_phone\":\"\",\"email\":\"\",\"username\":\"faisal.danu\",\"password\":\"D@RKknight3\",\"user_is_active\":\"1\",\"roles\":[\"15\",\"14\"]}','2026-09-25 21:04:06'),(501,1,'operator.yayasan','operator_yayasan','::1','CREATE_USER_ACCOUNT','users','38',NULL,'{\"username\":\"operator.sd.ais\",\"unit_id\":16,\"roles\":[4]}','2026-09-25 21:05:00'),(502,1,'operator.yayasan','operator_yayasan','::1','RECORD_TURNOVER','employee_turnover_logs','0',NULL,'{\"csrf_token\":\"90d9674dc68625e8b52f0b726d97af30c8c6184a4cc2bfeeed2aa8b2ccca2815\",\"employee_id\":\"26\",\"action_type\":\"Mutasi Keluar\",\"from_unit_id\":\"3\",\"to_unit_id\":\"16\",\"effective_date\":\"2026-09-25\",\"reason\":\"pindah unit\"}','2026-09-25 21:06:29'),(503,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-25 21:09:14'),(504,NULL,'guest','none','::1','LOGIN_FAILED','users',NULL,NULL,'{\"username\":\"erzayani\",\"reason\":\"User not found\"}','2026-09-25 21:09:26'),(505,NULL,'guest','none','::1','LOGIN_FAILED','users',NULL,NULL,'{\"username\":\"erzayani\",\"reason\":\"User not found\"}','2026-09-25 21:09:31'),(506,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-25 21:09:35'),(507,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-25 21:09:44'),(508,38,'operator.sd.ais','operator_sekolah','::1','LOGIN_SUCCESS','users','38',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-25 21:09:52'),(509,38,'operator.sd.ais','operator_sekolah','::1','LOGOUT','users','38',NULL,NULL,'2026-09-25 21:10:11'),(510,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-25 21:10:15'),(511,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-25 21:10:36'),(512,38,'operator.sd.ais','operator_sekolah','::1','LOGIN_SUCCESS','users','38',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-25 21:10:44'),(513,38,'operator.sd.ais','operator_sekolah','::1','CREATE_STUDENT','students','19',NULL,'{\"csrf_token\":\"f0eda737e5a9c100c1f50f030acad93a14e24418ab44cebd7cab4260e7b41f4a\",\"id\":\"0\",\"nis\":\"00000000\",\"nisn\":\"00000000\",\"nama\":\"Sarah Batrisya AIS\",\"gender\":\"P\",\"nik\":\"2171092110939001\",\"no_kk\":\"2171092110939001\",\"class_id\":\"0\",\"father_name\":\"Syahidi Rahman\",\"parent_phone\":\"\",\"parent_email\":\"saidi165@gmail.com\"}','2026-09-25 21:12:22'),(514,38,'operator.sd.ais','operator_sekolah','::1','CREATE_CLASS','classes','13',NULL,NULL,'2026-09-25 21:12:52'),(515,38,'operator.sd.ais','operator_sekolah','::1','CREATE_SUBJECT','subjects','18',NULL,NULL,'2026-09-25 21:13:40'),(516,38,'operator.sd.ais','operator_sekolah','::1','ASSIGN_TEACHER_SUBJECT','teacher_subjects','8',NULL,'{\"employee\":\"Ahmad Saleh Rambe\",\"subject\":\"Matematika\",\"class\":\"Kelas 1A\"}','2026-09-25 21:13:51'),(517,38,'operator.sd.ais','operator_sekolah','::1','CREATE_STUDENT','students','20',NULL,'{\"csrf_token\":\"f0eda737e5a9c100c1f50f030acad93a14e24418ab44cebd7cab4260e7b41f4a\",\"id\":\"0\",\"nis\":\"213123123\",\"nisn\":\"123123123\",\"nama\":\"Alumni A\",\"gender\":\"L\",\"nik\":\"123213123123\",\"no_kk\":\"123213213123123123\",\"class_id\":\"0\",\"father_name\":\"ASAS\",\"parent_phone\":\"\",\"parent_email\":\"\"}','2026-09-25 21:15:24'),(518,38,'operator.sd.ais','operator_sekolah','::1','CREATE_CLASS','classes','14',NULL,NULL,'2026-09-25 21:15:45'),(519,38,'operator.sd.ais','operator_sekolah','::1','CREATE_ALUMNI_TRACKING','alumni_tracking','2',NULL,'{\"student_id\":20,\"dest\":\"SMP AIS\"}','2026-09-25 21:15:59'),(520,38,'operator.sd.ais','operator_sekolah','::1','UPDATE_SPMB_ACTUAL','spmb_targets','16',NULL,'{\"actual_count\":60,\"unit_id\":16,\"notes\":\"\"}','2026-09-25 21:16:27'),(521,38,'operator.sd.ais','operator_sekolah','::1','LOGOUT','users','38',NULL,NULL,'2026-09-25 21:17:38'),(522,37,'faisal.danu','guru_mapel','::1','LOGIN_SUCCESS','users','37',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-25 21:17:48'),(523,37,'faisal.danu','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-25 21:18:04'),(524,37,'faisal.danu','walas','::1','SAVE_STUDENT_ATTENDANCE','student_attendance','13',NULL,'{\"month\":9,\"count\":1}','2026-09-25 21:19:15'),(525,37,'faisal.danu','walas','::1','SELF_SUBMIT_AKFI','akfi_assessments','26',NULL,'{\"self_scores\":20}','2026-09-25 21:22:16'),(526,37,'faisal.danu','walas','::1','LOGOUT','users','37',NULL,NULL,'2026-09-25 21:22:46'),(527,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGIN_SUCCESS','users','36',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 21:23:01'),(528,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGOUT','users','36',NULL,NULL,'2026-09-25 21:23:38'),(529,37,'faisal.danu','guru_mapel','::1','LOGIN_SUCCESS','users','37',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-25 21:23:49'),(530,37,'faisal.danu','guru_mapel','::1','UPLOAD_ADMINISTRASI','administrasi_uploads','139',NULL,'{\"file\":\"Kartu Akun SPMB - Sarah Batrisya (AIS-2027-97A3).pdf\",\"version\":1}','2026-09-25 21:24:07'),(531,37,'faisal.danu','guru_mapel','::1','UPLOAD_ADMINISTRASI','administrasi_uploads','140',NULL,'{\"file\":\"Kartu Akun SPMB - Sarah Batrisya (AIS-2027-97A3).pdf\",\"version\":1}','2026-09-25 21:24:31'),(532,37,'faisal.danu','guru_mapel','::1','UPLOAD_ADMINISTRASI','administrasi_uploads','141',NULL,'{\"file\":\"Template_Import_Pegawai_AlKahfi.xlsx\",\"version\":1}','2026-09-25 21:24:46'),(533,37,'faisal.danu','guru_mapel','::1','LOGOUT','users','37',NULL,NULL,'2026-09-25 21:24:51'),(534,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGIN_SUCCESS','users','36',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-25 21:25:00'),(535,36,'putra.perdana.tanjung','kepala_sekolah','::1','APPROVE_ADMINISTRASI','administrasi_uploads','141',NULL,'{\"status\":\"Revisi\",\"notes\":\"salah dokumen\"}','2026-09-25 21:25:30'),(536,36,'putra.perdana.tanjung','kepala_sekolah','::1','APPROVE_ADMINISTRASI','administrasi_uploads','140',NULL,'{\"status\":\"Disetujui\",\"notes\":\"\"}','2026-09-25 21:25:33'),(537,36,'putra.perdana.tanjung','kepala_sekolah','::1','CREATE_KEGIATAN_LUAR_KBM','kegiatan_luar_kbm','2',NULL,'{\"title\":\"Kampung Ramadhan\"}','2026-09-25 21:28:33'),(538,36,'putra.perdana.tanjung','kepala_sekolah','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','26',NULL,'{\"kinerja\":49.23,\"sikap\":20,\"absensi\":0,\"total\":69.23,\"grade\":\"C\"}','2026-09-25 21:32:09'),(539,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGOUT','users','36',NULL,NULL,'2026-09-25 21:33:22'),(540,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-25 21:33:38'),(541,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":59.8,\"sikap\":30,\"absensi\":0,\"total\":89.8,\"grade\":\"B\"}','2026-09-25 21:36:28'),(542,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":59.8,\"sikap\":30,\"absensi\":0,\"total\":89.8,\"grade\":\"B\"}','2026-09-25 21:38:53'),(543,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":59.8,\"sikap\":30,\"absensi\":0,\"total\":89.8,\"grade\":\"B\"}','2026-09-25 21:40:43'),(544,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":56.6,\"sikap\":30,\"absensi\":0,\"total\":86.6,\"grade\":\"B\"}','2026-09-25 21:41:16'),(545,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":53,\"sikap\":30,\"absensi\":0,\"total\":83,\"grade\":\"B\"}','2026-09-25 21:41:32'),(546,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":53,\"sikap\":30,\"absensi\":0,\"total\":83,\"grade\":\"B\"}','2026-09-25 21:42:09'),(547,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":53,\"sikap\":30,\"absensi\":0,\"total\":83,\"grade\":\"B\"}','2026-09-25 21:42:26'),(548,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":53,\"sikap\":30,\"absensi\":0,\"total\":83,\"grade\":\"B\"}','2026-09-25 21:42:48'),(549,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','11',NULL,'{\"kinerja\":53,\"sikap\":30,\"absensi\":0,\"total\":83,\"grade\":\"B\"}','2026-09-25 21:43:27'),(550,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','7',NULL,'{\"kinerja\":55,\"sikap\":30,\"absensi\":0,\"total\":85,\"grade\":\"B\"}','2026-09-25 21:44:04'),(551,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-26 04:05:36'),(552,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-26 04:05:41'),(553,3,'deputi.pendidikan','deputi_pendidikan','::1','ASSESS_AKFI_SUPERIOR','akfi_assessments','7',NULL,'{\"kinerja\":34.3,\"sikap\":30,\"absensi\":0,\"total\":64.3,\"grade\":\"C\"}','2026-09-26 04:08:06'),(554,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-26 04:09:02'),(555,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-26 04:09:07'),(556,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-26 04:47:24'),(557,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-26 04:47:28'),(558,1,'operator.yayasan','operator_yayasan','::1','UPDATE_UNIT','units','16','{\"id\":16,\"code\":\"SD-01\",\"name\":\"SD ALKAHFI ISLAMIC SCHOOL\",\"level\":\"SD\",\"kop_title\":\"YAYASAN ISLAM AL KAHFI BATAM\",\"kop_subtitle\":\"SD ALKAHFI ISLAMIC SCHOOL\",\"address\":\"Jl Ruko Imperium Superblock\",\"phone\":\"0000000000\",\"email\":\"humas@aisbatam.sch.id\",\"bank_name\":\"BSI\",\"bank_account\":\"1033667414\",\"bank_holder\":\"Syahidi Rahman\",\"letter_template\":null,\"smtp_host\":null,\"smtp_port\":587,\"smtp_user\":null,\"smtp_pass\":null,\"smtp_enc\":\"tls\",\"logo_path\":null,\"created_at\":\"2026-09-25 20:59:22\",\"updated_at\":\"2026-09-25 20:59:22\"}','{\"csrf_token\":\"719543b5185c37f485b33b0b8e2084352755e5975efe713b0eec915bf6fd189a\",\"id\":\"16\",\"code\":\"SD-01\",\"level\":\"SD\",\"name\":\"SD ALKAHFI ISLAMIC SCHOOL\",\"kop_subtitle\":\"SD ALKAHFI ISLAMIC SCHOOL\",\"phone\":\"0000000000\",\"email\":\"humas@aisbatam.sch.id\",\"address\":\"Jl Ruko Imperium Superblock\",\"bank_name\":\"BSI\",\"bank_account\":\"1033667414\",\"bank_holder\":\"Syahidi Rahman\"}','2026-09-26 04:47:59'),(559,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-26 05:09:58'),(560,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGIN_SUCCESS','users','36',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-26 05:10:07'),(561,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGOUT','users','36',NULL,NULL,'2026-09-26 05:21:29'),(562,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-26 05:21:32'),(563,1,'operator.yayasan','operator_yayasan','::1','UPLOAD_APP_LOGO','app_settings','0','{\"old\":\"\"}','{\"new\":\"uploads\\/logos\\/app_logo_1790375160.png\"}','2026-09-26 05:26:00'),(564,1,'operator.yayasan','operator_yayasan','::1','UPDATE_APP_SETTINGS','app_settings','0',NULL,'{\"app_name\":\"Manajemen Sekolah Berbasis Komputer (MASEKO)\",\"app_short_name\":\"MASEKO\",\"app_sub_name\":\"Manajemen Sekolah Berbasis Komputer\"}','2026-09-26 05:26:00'),(565,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-26 05:40:04'),(566,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-26 05:40:08'),(567,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-26 05:40:41'),(568,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-26 05:40:44'),(569,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-26 05:41:26'),(570,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-26 05:41:37'),(571,12,'waka.kurikulum','guru_supervisor','::1','SWITCH_ROLE','roles','11',NULL,'{\"new_role\":\"guru_supervisor\"}','2026-09-26 05:43:18'),(572,12,'waka.kurikulum','guru_supervisor','::1','LOGOUT','users','12',NULL,NULL,'2026-09-26 05:44:16'),(573,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-26 05:44:26'),(574,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-26 05:46:17'),(575,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-26 05:46:20'),(576,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-26 05:48:29'),(577,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-26 05:48:35'),(578,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-26 05:48:45'),(579,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-26 05:48:48'),(580,20,'guru.tahfidz','guru_tahfidz','::1','PROPOSE_TAHFIDZ_EXAM','tahfidz_exams','1',NULL,'{\"student_id\":1,\"juz\":30}','2026-09-26 05:48:59'),(581,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-26 05:49:02'),(582,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-26 05:49:06'),(583,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-26 05:50:14'),(584,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-26 05:50:18'),(585,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-26 05:51:26'),(586,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-26 05:51:35'),(587,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-26 05:51:56'),(588,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-26 05:52:05'),(589,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-26 05:52:51'),(590,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-26 05:52:53'),(591,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-26 05:55:13'),(592,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-26 05:55:20'),(593,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-26 05:55:58'),(594,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-26 05:56:02'),(595,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-26 05:56:34'),(596,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-26 05:56:41'),(597,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-26 05:59:43'),(598,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-26 05:59:50'),(599,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-26 06:00:23'),(600,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-26 06:01:00'),(601,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-26 06:01:24'),(602,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-26 06:55:42'),(603,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-26 07:06:07'),(604,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-26 07:06:16'),(605,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-26 07:19:04'),(606,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-26 07:19:09'),(607,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 05:48:54'),(608,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 05:48:59'),(609,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 05:53:05'),(610,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-27 05:53:09'),(611,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-27 05:53:47'),(612,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 05:53:51'),(613,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 05:54:19'),(614,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-27 05:54:23'),(615,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-27 05:54:38'),(616,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-27 05:54:42'),(617,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-27 05:54:45'),(618,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-27 05:54:47'),(619,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-27 05:55:05'),(620,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 05:55:10'),(621,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 05:56:18'),(622,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-27 05:56:24'),(623,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-27 05:56:48'),(624,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-27 05:56:59'),(625,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-27 05:57:11'),(626,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-27 05:57:15'),(627,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-27 05:57:23'),(628,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-27 05:57:28'),(629,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-27 05:57:30'),(630,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-27 05:57:33'),(631,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-27 05:57:48'),(632,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 05:57:52'),(633,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 05:59:43'),(634,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-27 05:59:47'),(635,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-27 06:00:16'),(636,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 06:00:19'),(637,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 06:00:45'),(638,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 06:00:50'),(639,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 06:04:09'),(640,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-27 06:04:17'),(641,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-27 06:04:33'),(642,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-27 06:04:39'),(643,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-27 06:05:45'),(644,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-27 06:05:50'),(645,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-27 06:06:38'),(646,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-27 06:06:48'),(647,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-27 06:09:33'),(648,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-27 06:09:44'),(649,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-27 06:10:28'),(650,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 06:10:46'),(651,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 06:13:36'),(652,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 06:13:41'),(653,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 06:19:15'),(654,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-27 06:19:25'),(655,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-27 06:21:48'),(656,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 06:21:53'),(657,17,'walas.sd','guru_mapel','::1','SWITCH_ROLE','roles','15',NULL,'{\"new_role\":\"guru_mapel\"}','2026-09-27 06:26:43'),(658,17,'walas.sd','guru_mapel','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 06:27:34'),(659,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-27 06:27:36'),(660,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-27 06:27:44'),(661,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-27 06:27:47'),(662,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-27 06:28:42'),(663,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 06:28:50'),(664,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 06:28:57'),(665,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 06:28:59'),(666,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 06:29:33'),(667,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 06:29:38'),(668,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 06:46:27'),(669,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 06:46:34'),(670,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 06:47:44'),(671,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 06:47:48'),(672,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 06:48:48'),(673,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 06:48:53'),(674,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 06:49:03'),(675,28,'syahidi','guru_mapel','::1','LOGIN_SUCCESS','users','28',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-27 06:49:08'),(676,28,'syahidi','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-27 06:49:14'),(677,28,'syahidi','walas','::1','LOGOUT','users','28',NULL,NULL,'2026-09-27 06:49:58'),(678,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 06:50:01'),(679,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 06:50:30'),(680,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 06:50:34'),(681,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 06:50:45'),(682,38,'operator.sd.ais','operator_sekolah','::1','LOGIN_SUCCESS','users','38',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 06:50:50'),(683,38,'operator.sd.ais','operator_sekolah','::1','LOGOUT','users','38',NULL,NULL,'2026-09-27 06:52:13'),(684,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 06:52:17'),(685,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 06:58:28'),(686,38,'operator.sd.ais','operator_sekolah','::1','LOGIN_SUCCESS','users','38',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 06:58:38'),(687,38,'operator.sd.ais','operator_sekolah','::1','LOGOUT','users','38',NULL,NULL,'2026-09-27 06:58:49'),(688,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 06:58:53'),(689,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 07:00:45'),(690,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 07:00:51'),(691,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 07:00:57'),(692,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 07:01:00'),(693,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 07:02:50'),(694,NULL,'guest','none','::1','LOGIN_FAILED','users',NULL,NULL,'{\"username\":\"erzayani\",\"reason\":\"User not found\"}','2026-09-27 07:02:57'),(695,38,'operator.sd.ais','operator_sekolah','::1','LOGIN_SUCCESS','users','38',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 07:03:08'),(696,38,'operator.sd.ais','operator_sekolah','::1','LOGOUT','users','38',NULL,NULL,'2026-09-27 07:04:09'),(697,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-27 07:04:15'),(698,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-27 07:04:37'),(699,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-27 07:04:40'),(700,12,'waka.kurikulum','guru_supervisor','::1','SWITCH_ROLE','roles','11',NULL,'{\"new_role\":\"guru_supervisor\"}','2026-09-27 07:06:47'),(701,12,'waka.kurikulum','waka_kurikulum','::1','SWITCH_ROLE','roles','10',NULL,'{\"new_role\":\"waka_kurikulum\"}','2026-09-27 07:08:23'),(702,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-27 07:09:15'),(703,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-27 07:09:27'),(704,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-27 07:10:37'),(705,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 07:10:41'),(706,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 07:11:08'),(707,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 07:11:12'),(708,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 07:11:25'),(709,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 07:11:29'),(710,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 07:12:15'),(711,NULL,'guest','none','::1','LOGIN_FAILED','users','28',NULL,'{\"attempts\":1}','2026-09-27 07:12:24'),(712,28,'syahidi','guru_mapel','::1','LOGIN_SUCCESS','users','28',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-27 07:12:28'),(713,28,'syahidi','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-27 07:12:33'),(714,28,'syahidi','walas','::1','LOGOUT','users','28',NULL,NULL,'2026-09-27 07:15:09'),(715,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 07:15:12'),(716,1,'operator.yayasan','operator_yayasan','::1','UPLOAD_APP_FAVICON','app_settings','0','{\"old\":\"\"}','{\"new\":\"uploads\\/logos\\/favicon_1790468135.png\"}','2026-09-27 07:15:35'),(717,1,'operator.yayasan','operator_yayasan','::1','UPDATE_APP_SETTINGS','app_settings','0',NULL,'{\"app_name\":\"Manajemen Sekolah Berbasis Komputer (MASEKO)\",\"app_short_name\":\"MASEKO\",\"app_sub_name\":\"Manajemen Sekolah Berbasis Komputer\"}','2026-09-27 07:15:35'),(718,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 07:16:24'),(719,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 07:24:39'),(720,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 07:28:17'),(721,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 07:28:30'),(722,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 07:30:26'),(723,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-27 07:30:31'),(724,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-27 07:30:45'),(725,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-27 07:30:50'),(726,12,'waka.kurikulum','guru_supervisor','::1','SWITCH_ROLE','roles','11',NULL,'{\"new_role\":\"guru_supervisor\"}','2026-09-27 07:32:38'),(727,12,'waka.kurikulum','waka_kurikulum','::1','SWITCH_ROLE','roles','10',NULL,'{\"new_role\":\"waka_kurikulum\"}','2026-09-27 07:32:40'),(728,12,'waka.kurikulum','waka_kurikulum','::1','SCHEDULE_SUPERVISI','supervisi_sessions','38',NULL,'{\"teacher_id\":25,\"date\":\"2026-09-27\"}','2026-09-27 07:33:00'),(729,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-27 07:33:02'),(730,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-27 07:33:15'),(731,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-27 07:33:33'),(732,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 07:33:50'),(733,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 07:34:31'),(734,28,'syahidi','guru_mapel','::1','LOGIN_SUCCESS','users','28',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-27 07:34:45'),(735,28,'syahidi','walas','::1','SWITCH_ROLE','roles','14',NULL,'{\"new_role\":\"walas\"}','2026-09-27 07:34:58'),(736,28,'syahidi','walas','::1','LOGOUT','users','28',NULL,NULL,'2026-09-27 07:35:15'),(737,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 07:35:18'),(738,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 07:44:24'),(739,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 07:44:28'),(740,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 07:55:21'),(741,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 07:55:27'),(742,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 08:00:05'),(743,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 08:00:09'),(744,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 08:09:26'),(745,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 08:09:30'),(746,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 08:10:39'),(747,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-27 08:10:47'),(748,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-27 08:11:14'),(749,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-27 08:11:17'),(750,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-27 08:11:28'),(751,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 08:11:32'),(752,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 08:11:36'),(753,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 08:11:43'),(754,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 10:08:32'),(755,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 10:08:41'),(756,1,'operator.yayasan','operator_yayasan','::1','UPDATE_UNIT','units','1','{\"id\":1,\"code\":\"KB\",\"name\":\"KB Islam Al Kahfi Batam\",\"level\":\"KB\",\"kop_title\":\"YAYASAN ISLAM AL KAHFI BATAM\",\"kop_subtitle\":\"PENDIDIKAN ANAK USIA DINI (KB) ISLAM AL KAHFI\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"phone\":\"0778-123456\",\"email\":\"kb@alkahfi.sch.id\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450001\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\",\"letter_template\":null,\"smtp_host\":null,\"smtp_port\":587,\"smtp_user\":null,\"smtp_pass\":null,\"smtp_enc\":\"tls\",\"logo_path\":null,\"created_at\":\"2026-09-23 18:42:58\",\"updated_at\":\"2026-09-23 18:42:58\"}','{\"csrf_token\":\"bbf598afc8b425f07218555cfddafb5ec27c22609c2e98fa09c6f5427838f704\",\"id\":\"1\",\"code\":\"KB\",\"level\":\"KB\",\"name\":\"KB ALKAHFI ISLAMIC SCHOOL\",\"kop_subtitle\":\"PENDIDIKAN ANAK USIA DINI (KB) ISLAM AL KAHFI\",\"phone\":\"0778-123456\",\"email\":\"kb@alkahfi.sch.id\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450001\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\"}','2026-09-27 10:10:13'),(757,1,'operator.yayasan','operator_yayasan','::1','UPDATE_UNIT','units','2','{\"id\":2,\"code\":\"TK\",\"name\":\"TK Islam Al Kahfi Batam\",\"level\":\"TK\",\"kop_title\":\"YAYASAN ISLAM AL KAHFI BATAM\",\"kop_subtitle\":\"TAMAN KANAK-KANAK ISLAM AL KAHFI\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"phone\":\"0778-123457\",\"email\":\"tk@alkahfi.sch.id\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450002\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\",\"letter_template\":null,\"smtp_host\":null,\"smtp_port\":587,\"smtp_user\":null,\"smtp_pass\":null,\"smtp_enc\":\"tls\",\"logo_path\":null,\"created_at\":\"2026-09-23 18:42:58\",\"updated_at\":\"2026-09-23 18:42:58\"}','{\"csrf_token\":\"bbf598afc8b425f07218555cfddafb5ec27c22609c2e98fa09c6f5427838f704\",\"id\":\"2\",\"code\":\"TK\",\"level\":\"TK\",\"name\":\"TK ALKAHFI ISLAMIC SCHOOL\",\"kop_subtitle\":\"TAMAN KANAK-KANAK ISLAM AL KAHFI\",\"phone\":\"0778-123457\",\"email\":\"tk@alkahfi.sch.id\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450002\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\"}','2026-09-27 10:10:29'),(758,1,'operator.yayasan','operator_yayasan','::1','UPDATE_UNIT','units','4','{\"id\":4,\"code\":\"SMP\",\"name\":\"SMP Islam Al Kahfi Batam\",\"level\":\"SMP\",\"kop_title\":\"YAYASAN ISLAM AL KAHFI BATAM\",\"kop_subtitle\":\"SEKOLAH MENENGAH PERTAMA ISLAM AL KAHFI\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"phone\":\"0778-123459\",\"email\":\"smp@alkahfi.sch.id\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450004\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\",\"letter_template\":null,\"smtp_host\":null,\"smtp_port\":587,\"smtp_user\":null,\"smtp_pass\":null,\"smtp_enc\":\"tls\",\"logo_path\":null,\"created_at\":\"2026-09-23 18:42:58\",\"updated_at\":\"2026-09-23 18:42:58\"}','{\"csrf_token\":\"bbf598afc8b425f07218555cfddafb5ec27c22609c2e98fa09c6f5427838f704\",\"id\":\"4\",\"code\":\"SMP\",\"level\":\"SMP\",\"name\":\"SMP ALKAHFI ISLAMIC SCHOOL\",\"kop_subtitle\":\"SEKOLAH MENENGAH PERTAMA ISLAM AL KAHFI\",\"phone\":\"0778-123459\",\"email\":\"smp@alkahfi.sch.id\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450004\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\"}','2026-09-27 10:10:43'),(759,1,'operator.yayasan','operator_yayasan','::1','UPLOAD_UNIT_LOGO','units','1','{\"old_logo\":null}','{\"new_logo\":\"uploads\\/logos\\/logo_unit_1_1790478650.png\"}','2026-09-27 10:10:50'),(760,1,'operator.yayasan','operator_yayasan','::1','UPLOAD_UNIT_LOGO','units','2','{\"old_logo\":null}','{\"new_logo\":\"uploads\\/logos\\/logo_unit_2_1790478656.png\"}','2026-09-27 10:10:56'),(761,1,'operator.yayasan','operator_yayasan','::1','UPLOAD_UNIT_LOGO','units','4','{\"old_logo\":null}','{\"new_logo\":\"uploads\\/logos\\/logo_unit_4_1790478666.png\"}','2026-09-27 10:11:06'),(762,1,'operator.yayasan','operator_yayasan','::1','UPLOAD_UNIT_LOGO','units','5','{\"old_logo\":null}','{\"new_logo\":\"uploads\\/logos\\/logo_unit_5_1790478672.png\"}','2026-09-27 10:11:12'),(763,1,'operator.yayasan','operator_yayasan','::1','UPDATE_UNIT','units','5','{\"id\":5,\"code\":\"SMA\",\"name\":\"SMA Islam Al Kahfi Batam\",\"level\":\"SMA\",\"kop_title\":\"YAYASAN ISLAM AL KAHFI BATAM\",\"kop_subtitle\":\"SEKOLAH MENENGAH ATAS ISLAM AL KAHFI\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"phone\":\"0778-123460\",\"email\":\"sma@alkahfi.sch.id\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450005\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\",\"letter_template\":null,\"smtp_host\":null,\"smtp_port\":587,\"smtp_user\":null,\"smtp_pass\":null,\"smtp_enc\":\"tls\",\"logo_path\":\"uploads\\/logos\\/logo_unit_5_1790478672.png\",\"created_at\":\"2026-09-23 18:42:58\",\"updated_at\":\"2026-09-27 10:11:12\"}','{\"csrf_token\":\"bbf598afc8b425f07218555cfddafb5ec27c22609c2e98fa09c6f5427838f704\",\"id\":\"5\",\"code\":\"SMA\",\"level\":\"SMA\",\"name\":\"SMA ALKAHFI ISLAMIC SCHOOL\",\"kop_subtitle\":\"SEKOLAH MENENGAH ATAS ISLAM AL KAHFI\",\"phone\":\"0778-123460\",\"email\":\"sma@alkahfi.sch.id\",\"address\":\"Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam\",\"bank_name\":\"Bank Syariah Indonesia (BSI)\",\"bank_account\":\"7123450005\",\"bank_holder\":\"Yayasan Islam Al Kahfi Batam\"}','2026-09-27 10:12:17'),(764,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 10:13:54'),(765,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-27 10:13:58'),(766,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-27 10:14:21'),(767,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-27 10:14:25'),(768,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-27 10:28:20'),(769,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-27 10:28:25'),(770,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-27 10:30:00'),(771,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 10:30:05'),(772,17,'walas.sd','guru_mapel','::1','SWITCH_ROLE','roles','15',NULL,'{\"new_role\":\"guru_mapel\"}','2026-09-27 10:31:45'),(773,17,'walas.sd','guru_mapel','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 10:33:20'),(774,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-27 10:33:26'),(775,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-27 10:34:04'),(776,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-27 10:34:11'),(777,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-27 10:34:40'),(778,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-27 10:34:45'),(779,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-27 10:36:37'),(780,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 10:36:43'),(781,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 12:30:50'),(782,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 12:30:54'),(783,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 12:34:13'),(784,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGIN_SUCCESS','users','36',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 12:34:24'),(785,36,'putra.perdana.tanjung','kepala_sekolah','::1','APPROVE_ADMINISTRASI','administrasi_uploads','139',NULL,'{\"status\":\"Disetujui\",\"notes\":\"\"}','2026-09-27 13:00:53'),(786,36,'putra.perdana.tanjung','kepala_sekolah','::1','LOGOUT','users','36',NULL,NULL,'2026-09-27 14:56:07'),(787,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 14:56:11'),(788,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 15:00:36'),(789,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 15:00:39'),(790,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 15:03:40'),(791,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 15:03:43'),(792,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 15:08:26'),(793,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 15:08:30'),(794,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 15:08:53'),(795,38,'operator.sd.ais','operator_sekolah','::1','LOGIN_SUCCESS','users','38',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 15:09:04'),(796,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 16:49:42'),(797,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 17:06:25'),(798,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 17:13:11'),(799,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 17:16:06'),(800,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 17:24:18'),(801,1,'operator.yayasan','operator_yayasan','::1','CREATE_UNIT','units','17',NULL,'{\"csrf_token\":\"12ee07e8f564ace027771af69e330a0c244006f0de2ea9e96d574c4b1caf4ebc\",\"id\":\"0\",\"code\":\"SD-FI 1\",\"level\":\"SD\",\"name\":\"SD FAJAR ILAHI 1\",\"kop_subtitle\":\"SEKOLAH DASAR FAJAR ILAHI 1\",\"phone\":\"0000000\",\"email\":\"sditfi1@gmail.com\",\"address\":\"batu aji\",\"bank_name\":\"BSI\",\"bank_account\":\"1002010202\",\"bank_holder\":\"SDIT FI 1\"}','2026-09-27 17:25:16'),(802,1,'operator.yayasan','operator_yayasan','::1','UPLOAD_UNIT_LOGO','units','17','{\"old_logo\":null}','{\"new_logo\":\"uploads\\/logos\\/logo_unit_17_1790504740.png\"}','2026-09-27 17:25:40'),(803,1,'operator.yayasan','operator_yayasan','::1','CREATE_ACADEMIC_YEAR','academic_years','3',NULL,'{\"name\":\"2027\\/2028\",\"semester\":\"Ganjil\"}','2026-09-27 17:25:55'),(804,1,'operator.yayasan','operator_yayasan','::1','CREATE_USER_ACCOUNT','users','41',NULL,'{\"username\":\"operator.sd.fi.1\",\"unit_id\":17,\"roles\":[4]}','2026-09-27 17:31:16'),(805,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 19:36:48'),(806,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 19:38:47'),(807,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 19:40:34'),(808,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGIN_SUCCESS','users','2',NULL,'{\"active_role\":\"direktur_pendidikan\"}','2026-09-27 19:40:42'),(809,2,'direktur.pendidikan','direktur_pendidikan','::1','LOGOUT','users','2',NULL,NULL,'2026-09-27 19:40:59'),(810,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGIN_SUCCESS','users','3',NULL,'{\"active_role\":\"deputi_pendidikan\"}','2026-09-27 19:41:05'),(811,3,'deputi.pendidikan','deputi_pendidikan','::1','LOGOUT','users','3',NULL,NULL,'2026-09-27 19:42:03'),(812,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-27 19:42:10'),(813,4,'operator.sd','operator_sekolah','::1','APPROVE_ACADEMIC_YEAR','academic_year_approvals','22','{\"id\":22,\"academic_year_id\":3,\"unit_id\":3,\"status\":\"Menunggu\",\"rejection_reason\":null,\"decided_by_user_id\":null,\"updated_at\":\"2026-09-27 17:25:55\"}','{\"status\":\"Setuju\",\"reason\":\"\"}','2026-09-27 19:44:00'),(814,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-27 19:44:08'),(815,8,'kepsek.sd','kepala_sekolah','::1','LOGIN_SUCCESS','users','8',NULL,'{\"active_role\":\"kepala_sekolah\"}','2026-09-27 19:44:18'),(816,8,'kepsek.sd','kepala_sekolah','::1','LOGOUT','users','8',NULL,NULL,'2026-09-27 19:46:01'),(817,12,'waka.kurikulum','waka_kurikulum','::1','LOGIN_SUCCESS','users','12',NULL,'{\"active_role\":\"waka_kurikulum\"}','2026-09-27 19:46:12'),(818,12,'waka.kurikulum','waka_kurikulum','::1','LOGOUT','users','12',NULL,NULL,'2026-09-27 19:46:44'),(819,17,'walas.sd','walas','::1','LOGIN_SUCCESS','users','17',NULL,'{\"active_role\":\"walas\"}','2026-09-27 19:46:50'),(820,17,'walas.sd','walas','::1','LOGOUT','users','17',NULL,NULL,'2026-09-27 19:47:44'),(821,18,'guru.mapel','guru_mapel','::1','LOGIN_SUCCESS','users','18',NULL,'{\"active_role\":\"guru_mapel\"}','2026-09-27 19:47:50'),(822,18,'guru.mapel','guru_mapel','::1','LOGOUT','users','18',NULL,NULL,'2026-09-27 19:48:30'),(823,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-27 19:48:40'),(824,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-27 19:49:37'),(825,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-27 19:49:43'),(826,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-27 19:50:23'),(827,14,'tutor.siswa','tutor_siswa','::1','LOGIN_SUCCESS','users','14',NULL,'{\"active_role\":\"tutor_siswa\"}','2026-09-27 19:50:31'),(828,14,'tutor.siswa','tutor_siswa','::1','LOGOUT','users','14',NULL,NULL,'2026-09-27 19:50:56'),(829,15,'tutor.sebaya','tutor_sebaya','::1','LOGIN_SUCCESS','users','15',NULL,'{\"active_role\":\"tutor_sebaya\"}','2026-09-27 19:51:06'),(830,15,'tutor.sebaya','tutor_sebaya','::1','LOGOUT','users','15',NULL,NULL,'2026-09-27 19:51:36'),(831,23,'tu.sd','tata_usaha','::1','LOGIN_SUCCESS','users','23',NULL,'{\"active_role\":\"tata_usaha\"}','2026-09-27 19:51:45'),(832,23,'tu.sd','tata_usaha','::1','LOGOUT','users','23',NULL,NULL,'2026-09-27 19:52:14'),(833,24,'cs.sd','cleaning_service','::1','LOGIN_SUCCESS','users','24',NULL,'{\"active_role\":\"cleaning_service\"}','2026-09-27 19:52:20'),(834,24,'cs.sd','cleaning_service','::1','LOGOUT','users','24',NULL,NULL,'2026-09-27 19:52:33'),(835,25,'security.sd','security','::1','LOGIN_SUCCESS','users','25',NULL,'{\"active_role\":\"security\"}','2026-09-27 19:52:38'),(836,25,'security.sd','security','::1','LOGOUT','users','25',NULL,NULL,'2026-09-27 19:53:01'),(837,21,'guru.supervisor','guru_supervisor','::1','LOGIN_SUCCESS','users','21',NULL,'{\"active_role\":\"guru_supervisor\"}','2026-09-27 19:53:11'),(838,21,'guru.supervisor','guru_supervisor','::1','LOGOUT','users','21',NULL,NULL,'2026-09-27 19:53:24'),(839,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-27 19:53:30'),(840,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-27 19:53:52'),(841,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-27 19:54:21'),(842,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-27 19:55:37'),(843,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-27 19:55:40'),(844,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-27 20:03:44'),(845,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-27 20:03:47'),(846,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-27 20:59:21'),(847,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-27 20:59:24'),(848,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-27 21:00:14'),(849,26,'ortu.santri','orang_tua','::1','LOGIN_SUCCESS','users','26',NULL,'{\"active_role\":\"orang_tua\"}','2026-09-27 21:00:17'),(850,26,'ortu.santri','orang_tua','::1','LOGOUT','users','26',NULL,NULL,'2026-09-28 06:12:38'),(851,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-28 11:50:53'),(852,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-28 12:31:08'),(853,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-28 12:31:12'),(854,1,'operator.yayasan','operator_yayasan','::1','LOGOUT','users','1',NULL,NULL,'2026-09-28 13:10:46'),(855,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-28 13:10:50'),(856,20,'guru.tahfidz','guru_tahfidz','::1','LOGIN_SUCCESS','users','20',NULL,'{\"active_role\":\"guru_tahfidz\"}','2026-09-29 05:22:17'),(857,20,'guru.tahfidz','guru_tahfidz','::1','LOGOUT','users','20',NULL,NULL,'2026-09-29 05:28:34'),(858,16,'koor.tahfidz','koor_tahfidz','::1','LOGIN_SUCCESS','users','16',NULL,'{\"active_role\":\"koor_tahfidz\"}','2026-09-29 05:28:44'),(859,16,'koor.tahfidz','koor_tahfidz','::1','LOGOUT','users','16',NULL,NULL,'2026-09-29 05:29:33'),(860,4,'operator.sd','operator_sekolah','::1','LOGIN_SUCCESS','users','4',NULL,'{\"active_role\":\"operator_sekolah\"}','2026-09-29 05:29:43'),(861,4,'operator.sd','operator_sekolah','::1','LOGOUT','users','4',NULL,NULL,'2026-09-29 05:33:04'),(862,1,'operator.yayasan','operator_yayasan','::1','LOGIN_SUCCESS','users','1',NULL,'{\"active_role\":\"operator_yayasan\"}','2026-09-29 05:33:23');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bill_reminders`
--

LOCK TABLES `bill_reminders` WRITE;
/*!40000 ALTER TABLE `bill_reminders` DISABLE KEYS */;
INSERT INTO `bill_reminders` VALUES (1,3,2,1,'Diajukan',23,NULL,'TAG/202609/0002','ortu.santri@alkahfi.sch.id',NULL,NULL,'2026-09-26 05:53:47'),(2,3,3,3,'Diajukan',23,NULL,'TAG/202609/0003','bambang@gmail.com',NULL,NULL,'2026-09-26 05:53:47');
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
  `due_date` date NOT NULL,
  `status` enum('Lunas','Tertunggak') NOT NULL DEFAULT 'Tertunggak',
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bills`
--

LOCK TABLES `bills` WRITE;
/*!40000 ALTER TABLE `bills` DISABLE KEYS */;
INSERT INTO `bills` VALUES (1,3,1,1,1,2025,'SPP Bulan Januari 2025',750000.00,'2025-01-10','Lunas','2025-01-08',750000.00,'Transfer BSI','Lunas tepat waktu',23,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(2,3,1,1,2,2025,'SPP Bulan Februari 2025',750000.00,'2025-02-10','Tertunggak',NULL,0.00,NULL,'Menunggu pembayaran orang tua',23,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(3,3,1,3,2,2025,'SPP Bulan Februari 2025',750000.00,'2025-02-10','Tertunggak',NULL,0.00,NULL,'Tunggakan 1 bulan',23,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(4,2,1,2,2,2025,'SPP TK Bulan Februari 2025',600000.00,'2025-02-10','Lunas','2025-02-05',600000.00,'Transfer BSI','Lunas',23,'2026-09-23 18:43:17','2026-09-23 18:43:17'),(5,4,1,5,2,2025,'SPP SMP Bulan Februari 2025',850000.00,'2025-02-10','Tertunggak',NULL,0.00,NULL,'Tunggakan siswa SMP',23,'2026-09-23 18:43:17','2026-09-23 18:43:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=97 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklists_cs`
--

LOCK TABLES `checklists_cs` WRITE;
/*!40000 ALTER TABLE `checklists_cs` DISABLE KEYS */;
INSERT INTO `checklists_cs` VALUES (65,3,19,1,'Rutin Harian','2026-02-01',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(66,3,19,1,'Rutin Harian','2026-02-02',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(67,3,19,1,'Rutin Harian','2026-02-03',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(68,3,19,1,'Rutin Harian','2026-02-04',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(69,3,19,1,'Rutin Harian','2026-02-05',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(70,3,19,1,'Rutin Harian','2026-02-06',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(71,3,19,1,'Rutin Harian','2026-02-07',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(72,3,19,1,'Rutin Harian','2026-02-08',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(73,3,19,1,'Rutin Harian','2026-02-09',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(74,3,19,1,'Rutin Harian','2026-02-10',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(75,3,19,1,'Rutin Harian','2026-02-11',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(76,3,19,1,'Rutin Harian','2026-02-12',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(77,3,19,1,'Rutin Harian','2026-02-13',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(78,3,19,1,'Rutin Harian','2026-02-14',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(79,3,19,1,'Rutin Harian','2026-02-15',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(80,3,19,1,'Rutin Harian','2026-02-16',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(81,3,19,1,'Rutin Harian','2026-02-17',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(82,3,19,1,'Rutin Harian','2026-02-18',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(83,3,19,1,'Rutin Harian','2026-02-19',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(84,3,19,1,'Rutin Harian','2026-02-20',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(85,3,19,1,'Rutin Harian','2026-02-21',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(86,3,19,1,'Rutin Harian','2026-02-22',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(87,3,19,1,'Rutin Harian','2026-02-23',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(88,3,19,1,'Rutin Harian','2026-02-24',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(89,3,19,1,'Rutin Harian','2026-02-25',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(90,3,19,1,'Rutin Harian','2026-02-26',0,0,NULL,NULL,NULL,'2026-09-24 13:06:58'),(91,3,19,1,'Piket Bulanan','2026-02-01',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(92,3,19,1,'Piket Bulanan','2026-02-02',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(93,3,19,1,'Piket Bulanan','2026-02-03',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(94,3,19,1,'Piket Bulanan','2026-02-04',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(95,3,19,1,'Gotong Royong','2026-02-01',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58'),(96,3,19,1,'Gotong Royong','2026-02-02',0,1,NULL,NULL,NULL,'2026-09-24 13:06:58');
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
) ENGINE=InnoDB AUTO_INCREMENT=59 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `checklists_security`
--

LOCK TABLES `checklists_security` WRITE;
/*!40000 ALTER TABLE `checklists_security` DISABLE KEYS */;
INSERT INTO `checklists_security` VALUES (31,3,20,1,'2026-02-01',1,1,'',NULL,'2026-09-24 13:06:58'),(32,3,20,1,'2026-02-02',1,1,'',NULL,'2026-09-24 13:06:58'),(33,3,20,1,'2026-02-03',1,1,'',NULL,'2026-09-24 13:06:58'),(34,3,20,1,'2026-02-04',1,1,'',NULL,'2026-09-24 13:06:58'),(35,3,20,1,'2026-02-05',1,1,'',NULL,'2026-09-24 13:06:58'),(36,3,20,1,'2026-02-06',1,1,'',NULL,'2026-09-24 13:06:58'),(37,3,20,1,'2026-02-07',1,1,'',NULL,'2026-09-24 13:06:58'),(38,3,20,1,'2026-02-08',1,1,'',NULL,'2026-09-24 13:06:58'),(39,3,20,1,'2026-02-09',1,1,'',NULL,'2026-09-24 13:06:58'),(40,3,20,1,'2026-02-10',1,1,'',NULL,'2026-09-24 13:06:58'),(41,3,20,1,'2026-02-11',1,1,'',NULL,'2026-09-24 13:06:58'),(42,3,20,1,'2026-02-12',1,1,'',NULL,'2026-09-24 13:06:58'),(43,3,20,1,'2026-02-13',1,1,'',NULL,'2026-09-24 13:06:58'),(44,3,20,1,'2026-02-14',1,0,'',NULL,'2026-09-24 13:06:58'),(45,3,20,1,'2026-02-15',1,1,'',NULL,'2026-09-24 13:06:58'),(46,3,20,1,'2026-02-16',1,1,'',NULL,'2026-09-24 13:06:58'),(47,3,20,1,'2026-02-17',1,1,'',NULL,'2026-09-24 13:06:58'),(48,3,20,1,'2026-02-18',1,1,'',NULL,'2026-09-24 13:06:58'),(49,3,20,1,'2026-02-19',1,1,'',NULL,'2026-09-24 13:06:58'),(50,3,20,1,'2026-02-20',1,1,'',NULL,'2026-09-24 13:06:58'),(51,3,20,1,'2026-02-21',1,1,'',NULL,'2026-09-24 13:06:58'),(52,3,20,1,'2026-02-22',1,1,'',NULL,'2026-09-24 13:06:58'),(53,3,20,1,'2026-02-23',1,1,'',NULL,'2026-09-24 13:06:58'),(54,3,20,1,'2026-02-24',1,1,'',NULL,'2026-09-24 13:06:58'),(55,3,20,1,'2026-02-25',1,1,'',NULL,'2026-09-24 13:06:58'),(56,3,20,1,'2026-02-26',1,1,'',NULL,'2026-09-24 13:06:58'),(57,3,20,1,'2026-02-27',1,1,'',NULL,'2026-09-24 13:06:58'),(58,3,20,1,'2026-02-28',1,1,'',NULL,'2026-09-24 13:06:58');
/*!40000 ALTER TABLE `checklists_security` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `class_students`
--

LOCK TABLES `class_students` WRITE;
/*!40000 ALTER TABLE `class_students` DISABLE KEYS */;
INSERT INTO `class_students` VALUES (1,3,1,1,'Aktif','2026-09-23 18:43:17'),(2,1,2,1,'Aktif','2026-09-23 18:43:17'),(3,3,3,1,'Aktif','2026-09-23 18:43:17'),(4,3,4,1,'Aktif','2026-09-23 18:43:17'),(5,7,5,1,'Aktif','2026-09-23 18:43:17'),(6,3,6,1,'Aktif','2026-09-24 10:02:04'),(7,4,7,1,'Aktif','2026-09-24 10:16:18'),(8,9,8,1,'Aktif','2026-09-24 20:36:59'),(9,9,9,1,'Aktif','2026-09-24 20:36:59'),(10,9,10,1,'Aktif','2026-09-24 20:36:59'),(12,9,12,1,'Aktif','2026-09-24 20:36:59'),(13,2,13,1,'Aktif','2026-09-24 20:36:59'),(14,2,14,1,'Aktif','2026-09-24 20:36:59'),(15,10,15,1,'Aktif','2026-09-24 20:36:59'),(16,10,16,1,'Aktif','2026-09-24 20:36:59'),(17,11,17,1,'Aktif','2026-09-24 20:36:59'),(18,11,18,1,'Aktif','2026-09-24 20:36:59'),(19,12,11,1,'Aktif','2026-09-24 21:49:29'),(22,13,19,1,'Aktif','2026-09-25 21:13:06');
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `classes`
--

LOCK TABLES `classes` WRITE;
/*!40000 ALTER TABLE `classes` DISABLE KEYS */;
INSERT INTO `classes` VALUES (1,2,1,'TK A Al-Fatihah','TK A',4,'2026-09-23 18:42:59','2026-09-23 18:42:59',4),(2,2,1,'TK B Al-Baqarah','TK B',4,'2026-09-23 18:42:59','2026-09-23 18:42:59',4),(3,3,1,'Kelas 1A Abu Bakar','Kelas 1',12,'2026-09-23 18:42:59','2026-09-26 06:52:30',12),(4,3,1,'Kelas 1B Umar bin Khattab','Kelas 1',12,'2026-09-23 18:42:59','2026-09-26 06:52:30',12),(5,3,1,'Kelas 2A Utsman bin Affan','Kelas 2',12,'2026-09-23 18:42:59','2026-09-26 06:52:30',12),(6,4,1,'Kelas 7A Thariq bin Ziyad','Kelas 7',8,'2026-09-23 18:42:59','2026-09-26 06:52:30',8),(7,4,1,'Kelas 8A Salahuddin Al-Ayyubi','Kelas 8',8,'2026-09-23 18:42:59','2026-09-26 06:52:30',8),(8,5,1,'Kelas 10 MIPA Al-Biruni','Kelas 10',8,'2026-09-23 18:42:59','2026-09-26 06:52:30',8),(9,3,1,'Kelas 6A Ali bin Abi Thalib','Kelas 6',12,'2026-09-24 20:36:59','2026-09-26 06:52:30',12),(10,4,1,'Kelas 9A Thariq bin Ziyad','Kelas 9',8,'2026-09-24 20:36:59','2026-09-26 06:52:30',8),(11,5,1,'Kelas 12 MIPA Al-Khawarizmi','Kelas 12',8,'2026-09-24 20:36:59','2026-09-26 06:52:30',8),(12,3,1,'Kelas 6A','Kelas 6',25,'2026-09-24 21:49:02','2026-09-24 21:49:02',25),(13,16,1,'Kelas 1A','Kelas 1',32,'2026-09-25 21:12:52','2026-09-25 21:12:52',32),(14,16,1,'Kelas 6A','Kelas 6',26,'2026-09-25 21:15:45','2026-09-25 21:15:45',26);
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
  `backup_type` enum('Manual','Auto') DEFAULT 'Manual',
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
) ENGINE=InnoDB AUTO_INCREMENT=84 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employee_turnover_logs`
--

LOCK TABLES `employee_turnover_logs` WRITE;
/*!40000 ALTER TABLE `employee_turnover_logs` DISABLE KEYS */;
INSERT INTO `employee_turnover_logs` VALUES (1,26,'Mutasi Keluar',3,16,'2026-09-25','pindah unit',1,'2026-09-25 21:06:29');
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
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `employees`
--

LOCK TABLES `employees` WRITE;
/*!40000 ALTER TABLE `employees` DISABLE KEYS */;
INSERT INTO `employees` VALUES (1,2,'YAY-DIR-001','2171010101700001','Dr. H. Muhammad Ridwan, M.Pd','L','Batam','1970-01-01','2015-07-01','Direktur Pendidikan','Direktur Pendidikan',NULL,NULL,'08110000001','08110000002','direktur@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-24 08:44:14'),(2,3,'YAY-DEP-002','2171010101750002','Ust. Fathurrahman, S.Pd.I','L','Padang','1975-05-10','2016-07-01','Deputi Direktur','Deputi Direktur',NULL,1,'08110000002','08110000003','deputi@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-24 08:44:14'),(3,8,'SD-KS-001','2171010101800003','Ust. Abdullah Syafiq, M.Pd.I','L','Medan','1980-08-15','2017-07-01','Kepala Sekolah','Kepala Sekolah',3,2,'08110000003','08110000008','kepsek.sd@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(4,9,'TK-KS-001','2171010101820004','Usth. Maryam Zahra, S.Pd','P','Batam','1982-03-20','2018-07-01','Kepala Sekolah','Kepala Sekolah',2,2,'08110000003','08110000009','kepsek.tk@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(5,10,'SMP-KS-001','2171010101810005','Ust. Salman Al Farisi, Lc','L','Solo','1981-11-12','2017-07-01','Kepala Sekolah','Kepala Sekolah',4,2,'08110000003','08110000010','kepsek.smp@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(6,11,'SMA-KS-001','2171010101780006','Ust. H. Zainuddin, M.A','L','Jakarta','1978-04-25','2016-07-01','Kepala Sekolah','Kepala Sekolah',5,2,'08110000003','08110000011','kepsek.sma@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(7,12,'SD-GUR-001','2171010101850007','Umar Hamzah, M.Pd','L','Batam','1985-09-09','2019-07-01','Wakil Kurikulum / Guru Mapel','Wakil Kurikulum / Guru Mapel',3,3,'08110000008','08110000012','waka.kurikulum@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(8,13,'SD-GUR-002','2171010101860008','Khalid Basalamah, S.Pd','L','Batam','1986-06-14','2019-07-01','Wakil Kesiswaan','Wakil Kesiswaan',3,3,'08110000008','08110000013','waka.kesiswaan@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(9,14,'SD-GUR-003','2171010101880009','Ust. Hanif Al Hafidz','L','Kudus','1988-12-01','2020-07-01','Tutor Siswa / Guru Tahfidz','Tutor Siswa / Guru Tahfidz',3,3,'08110000008','08110000014','tutor.siswa@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(10,15,'SD-GUR-004','2171010101870010','Ust. Muadz Al Hafidz','L','Jombang','1987-07-07','2020-07-01','Tutor Sebaya Guru','Tutor Sebaya Guru',3,3,'08110000008','08110000015','tutor.sebaya@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(11,16,'SD-GUR-005','2171010101840011','Ust. Bilal Syahid, S.Q','L','Batam','1984-02-18','2018-07-01','Koordinator Tahfidz','Koordinator Tahfidz',3,3,'08110000008','08110000016','koor.tahfidz@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(12,17,'SD-GUR-006','2171010101900012','Aisyah Wardani, S.Pd','P','Tanjungpinang','1990-05-15','2021-07-01','Wali Kelas / Guru Mapel','Wali Kelas / Guru Mapel',3,3,'08110000008','08110000017','walas.sd@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(13,18,'SD-GUR-007','2171010101920013','Fajar Pratama, S.Pd','L','Batam','1992-10-10','2022-07-01','Guru Mapel Umum','Guru Mapel Umum',3,3,'08110000008','08110000018','guru.mapel@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(14,19,'SD-GUR-008','2171010101890014','Ust. Hamzah Robbani, Lc','L','Batam','1989-08-20','2021-07-01','Guru Mapel Diniyah','Guru Mapel Diniyah',3,3,'08110000008','08110000019','guru.diniyah@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(15,20,'SD-GUR-009','2171010101930015','Usth. Khadijah Al Hafidzah','P','Batam','1993-01-30','2022-07-01','Guru Tahfidz','Guru Tahfidz',3,3,'08110000008','08110000020','guru.tahfidz@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(16,21,'SD-GUR-010','2171010101760016','Drs. Supriyadi, M.Pd','L','Yogyakarta','1976-03-12','2016-07-01','Guru Supervisor','Guru Supervisor',3,3,'08110000008','08110000021','guru.supervisor@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(17,22,'SD-GUR-011','2171010101910017','Indra Gunawan, S.Pd','L','Batam','1991-04-05','2021-07-01','Guru / Ketua Panitia Lomba','Guru / Ketua Panitia Lomba',3,3,'08110000008','08110000022','panitia.lomba@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(18,23,'SD-STF-001','2171010101940018','Sri Wahyuni, A.Md','P','Batam','1994-07-19','2022-01-01','Tata Usaha Keuangan','Tata Usaha Keuangan',3,3,'08110000008','08110000023','tu.sd@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(19,24,'SD-STF-002','2171010101830019','Pak Joko Sutrisno','L','Batam','1983-11-23','2020-01-01','Cleaning Services','Cleaning Services',3,3,'08110000008','08110000024','cs.sd@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(20,25,'SD-STF-003','2171010101850020','Pak Agus Salim','L','Batam','1985-02-14','2019-01-01','Security','Security',3,3,'08110000008','08110000025','security.sd@alkahfi.sch.id','Aktif',0,'2026-09-23 18:42:59','2026-09-23 18:42:59'),(21,4,'SD-OPS-001',NULL,'Rian Hidayat, S.Kom','L',NULL,NULL,'2026-09-24','Operator Sekolah','Operator Sekolah',3,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 05:49:07','2026-09-24 05:49:07'),(22,5,'TK-OPS-001',NULL,'Siti Rahma','L',NULL,NULL,'2026-09-24','Operator Sekolah','Operator Sekolah',2,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 05:49:07','2026-09-24 05:49:07'),(23,6,'SMP-OPS-001',NULL,'Budi Santoso','L',NULL,NULL,'2026-09-24','Operator Sekolah','Operator Sekolah',4,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 05:49:07','2026-09-24 05:49:07'),(24,7,'SMA-OPS-001',NULL,'Dewi Lestari','L',NULL,NULL,'2026-09-24','Operator Sekolah','Operator Sekolah',5,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 05:49:07','2026-09-24 05:49:07'),(25,28,'SD-SYAH-637','','Syahidi Rahman, S,Pd, Gr','L','',NULL,'2026-09-24','Guru Mapel','Guru Mapel',3,3,'','08566691395','syahidi@aisbatam.sch.id','Aktif',0,'2026-09-24 09:02:37','2026-09-24 18:02:23'),(26,30,'SD-RAMB-204',NULL,'Ahmad Saleh Rambe','L',NULL,NULL,'2026-09-24','Guru Mapel','Guru Mapel',16,NULL,NULL,NULL,'salehrambe@aisbatam.sch.id','Aktif',0,'2026-09-24 10:25:54','2026-09-25 21:06:29'),(27,31,'SD-AZMI-471',NULL,'Azmi','L',NULL,NULL,'2026-09-24','Koordinator Tahfidz','Koordinator Tahfidz',3,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 20:45:08','2026-09-24 20:45:08'),(28,32,'KT2001',NULL,'Usth. Maryam Al-Hafidzah, S.Pd.I','L',NULL,NULL,'0000-00-00','Koordinator Tahfidz','Koordinator Tahfidz',2,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 21:17:12','2026-09-24 21:17:12'),(29,33,'KT4001',NULL,'Ust. Zulkifli Al-Hafidz, Lc','L',NULL,NULL,'0000-00-00','Koordinator Tahfidz','Koordinator Tahfidz',4,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 21:17:12','2026-09-24 21:17:12'),(30,34,'KT5001',NULL,'Ust. Salman Al-Farisi, S.Q','L',NULL,NULL,'0000-00-00','Koordinator Tahfidz','Koordinator Tahfidz',5,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-24 21:17:12','2026-09-24 21:17:12'),(31,36,'08566691395','2171092110939001','Putra Perdana Tanjung','L','',NULL,'2026-09-25','Guru Kepala Sekolah','Guru Kepala Sekolah',16,2,'','','','Aktif',0,'2026-09-25 21:03:00','2026-09-25 21:03:00'),(32,37,'0000000000','0000000000','Faisal Danu','L','',NULL,'2026-09-25','Guru Kelas','Guru Kelas',16,31,'','','','Aktif',0,'2026-09-25 21:04:06','2026-09-25 21:04:06'),(33,38,'SD-01-OPER-672',NULL,'Erzayani','L',NULL,NULL,'2026-09-25','Operator Sekolah','Operator Sekolah',16,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-25 21:05:00','2026-09-25 21:05:00'),(35,41,'SD-FI 1-OPER-331',NULL,'Fikri','L',NULL,NULL,'2026-09-27','Operator Sekolah','Operator Sekolah',17,NULL,NULL,NULL,NULL,'Aktif',0,'2026-09-27 17:31:16','2026-09-27 17:31:16');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `guru_berprestasi`
--

LOCK TABLES `guru_berprestasi` WRITE;
/*!40000 ALTER TABLE `guru_berprestasi` DISABLE KEYS */;
INSERT INTO `guru_berprestasi` VALUES (1,3,1,26,'Guru teladan tahfidzul qur\'an','Tingkat Yayasan','2026',NULL,'Terverifikasi','',8,'2026-09-25 07:27:14'),(2,16,1,32,'Juara Lomba Renang','Kota / Nasional','2026',NULL,'Terverifikasi','',36,'2026-09-25 21:30:21');
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
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `karya_ilmiah_guru`
--

LOCK TABLES `karya_ilmiah_guru` WRITE;
/*!40000 ALTER TABLE `karya_ilmiah_guru` DISABLE KEYS */;
INSERT INTO `karya_ilmiah_guru` VALUES (21,17,12,3,1,'PTK','Peningkatan Keterampilan Membaca Siswa Kelas 1 Melalui Media Kartu Phonics Bergambar','','',0,'Disetujui',8,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58','2026-09-24 13:06:58'),(22,12,7,3,1,'Best Practice','Implementasi Model Pembelajaran Diferensiasi Berbasis Proyek Kurikulum Merdeka','','',0,'Disetujui',8,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58','2026-09-24 13:06:58'),(23,14,9,3,1,'Best Practice','Optimalisasi Metode Tikrar dan Talaqqi Efektif Dalam Pencapaian Mutqin Juz 30','','',0,'Disetujui',8,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58','2026-09-24 13:06:58'),(24,20,15,3,1,'Best Practice','Strategi Murojaah Berpasangan Dalam Menjaga Hafalan Al-Quran Santri','','',0,'Disetujui',8,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58','2026-09-24 13:06:58');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kegiatan_luar_kbm`
--

LOCK TABLES `kegiatan_luar_kbm` WRITE;
/*!40000 ALTER TABLE `kegiatan_luar_kbm` DISABLE KEYS */;
INSERT INTO `kegiatan_luar_kbm` VALUES (1,3,1,'Kajian Tarhib Ramadhan 1446 H','2025-02-20','Masjid Al Kahfi Batam',8,'2026-09-23 18:43:17'),(2,16,1,'Kampung Ramadhan','2026-09-25','Masjid / Luar Lingkungan Sekolah',36,'2026-09-25 21:28:33');
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kegiatan_luar_kbm_participants`
--

LOCK TABLES `kegiatan_luar_kbm_participants` WRITE;
/*!40000 ALTER TABLE `kegiatan_luar_kbm_participants` DISABLE KEYS */;
INSERT INTO `kegiatan_luar_kbm_participants` VALUES (1,1,7,'Hadir'),(2,1,8,'Hadir'),(3,1,9,'Hadir'),(4,1,10,'Hadir'),(5,1,11,'Hadir'),(6,1,12,'Hadir'),(7,1,13,'Hadir'),(8,1,14,'Hadir'),(9,1,15,'Hadir'),(10,1,16,'Hadir'),(11,1,17,'Hadir'),(12,1,18,'Hadir'),(13,1,19,'Hadir'),(14,1,20,'Hadir'),(15,2,26,'Hadir'),(16,2,32,'Hadir');
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
INSERT INTO `mushaf_surahs` VALUES (1,'الفاتحة','Al-Fatihah',7,1,1),(2,'البقرة','Al-Baqarah',286,2,49),(3,'آل عمران','Ali Imran',200,50,76),(4,'النساء','An-Nisa',176,77,106),(5,'المائدة','Al-Maidah',120,106,127),(6,'الأنعام','Al-Anam',165,128,150),(7,'الأعراف','Al-Araf',206,151,176),(8,'الأنفال','Al-Anfal',75,177,186),(9,'التوبة','At-Taubah',129,187,207),(10,'يونس','Yunus',109,208,221),(11,'هود','Hud',123,221,235),(12,'يوسف','Yusuf',111,235,248),(13,'الرعد','Ar-Rad',43,249,255),(14,'إبراهيم','Ibrahim',52,255,261),(15,'الحجر','Al-Hijr',99,262,267),(16,'النحل','An-Nahl',128,267,281),(17,'الإسراء','Al-Isra',111,282,293),(18,'الكهف','Al-Kahf',110,293,304),(19,'مريم','Maryam',98,305,312),(20,'طه','Taha',135,312,321),(21,'الأنبياء','Al-Anbiya',112,322,331),(22,'الحج','Al-Hajj',78,332,341),(23,'المؤمنون','Al-Muminun',118,342,349),(24,'النور','An-Nur',64,350,359),(25,'الفرقان','Al-Furqan',77,359,366),(26,'الشعراء','Asy-Syuara',227,367,376),(27,'النمل','An-Naml',93,377,385),(28,'القصص','Al-Qasas',88,385,396),(29,'العنكبوت','Al-Ankabut',69,396,404),(30,'الروم','Ar-Rum',60,404,410),(31,'لقمان','Luqman',34,411,414),(32,'السجدة','As-Sajdah',30,415,417),(33,'الأحزاب','Al-Ahzab',73,418,427),(34,'سبأ','Saba',54,428,434),(35,'فاطر','Fatir',45,434,440),(36,'يس','Yasin',83,440,445),(37,'الصافات','As-Saffat',182,446,452),(38,'ص','Sad',88,453,458),(39,'الزمر','Az-Zumar',75,458,467),(40,'غافر','Ghafir',85,467,476),(41,'فصلت','Fussilat',54,477,482),(42,'الشورى','Asy-Syura',53,483,489),(43,'الزخرف','Az-Zukhruf',89,489,495),(44,'الدخان','Ad-Dukhan',59,496,498),(45,'الجاثية','Al-Jatsiyah',37,499,502),(46,'الأحقاف','Al-Ahqaf',35,502,506),(47,'محمد','Muhammad',38,507,510),(48,'الفتح','Al-Fath',29,511,515),(49,'الحجرات','Al-Hujurat',18,515,517),(50,'ق','Qaf',45,518,520),(51,'الذاريات','Adz-Dzariyat',60,520,523),(52,'الطور','At-Tur',49,523,525),(53,'النجم','An-Najm',62,526,528),(54,'القمر','Al-Qamar',55,528,531),(55,'الرحمن','Ar-Rahman',78,531,534),(56,'الواقعة','Al-Waqiah',96,534,537),(57,'الحديد','Al-Hadid',29,537,541),(58,'المجادلة','Al-Mujadilah',22,542,545),(59,'الحشر','Al-Hasyr',24,545,548),(60,'الممتحنة','Al-Mumtahanah',13,549,551),(61,'الصف','As-Saff',14,551,552),(62,'الجمعة','Al-Jumuah',11,553,554),(63,'المنافقون','Al-Munafiqun',11,554,555),(64,'التغابن','At-Taghabun',18,556,557),(65,'الطلاق','At-Talaq',12,558,559),(66,'التحريم','At-Tahrim',12,560,561),(67,'الملك','Al-Mulk',30,562,564),(68,'القلم','Al-Qalam',52,564,566),(69,'الحاقة','Al-Haqqah',52,566,568),(70,'المعارج','Al-Maarij',44,568,570),(71,'نوح','Nuh',28,570,571),(72,'الجن','Al-Jinn',28,572,573),(73,'المزمل','Al-Muzzammil',20,574,575),(74,'المدثر','Al-Muddatstsir',56,575,577),(75,'القيامة','Al-Qiyamah',40,577,578),(76,'الإنسان','Al-Insan',31,578,580),(77,'المرسلات','Al-Mursalat',50,580,581),(78,'النبأ','An-Naba',40,582,583),(79,'النازعات','An-Naziat',46,583,584),(80,'عبس','Abasa',42,585,586),(81,'التكوير','At-Takwir',29,586,586),(82,'الانفطار','Al-Infitar',19,587,587),(83,'المطففين','Al-Muthaffifin',36,587,589),(84,'الانشقاق','Al-Insyiqaq',25,589,590),(85,'البروج','Al-Buruj',22,590,590),(86,'الطارق','At-Thariq',17,591,591),(87,'الأعلى','Al-Ala',19,591,592),(88,'الغاشية','Al-Ghasyiyah',26,592,593),(89,'الفجر','Al-Fajr',30,593,594),(90,'البلد','Al-Balad',20,594,595),(91,'الشمس','Asy-Syams',15,595,595),(92,'الليل','Al-Lail',21,595,596),(93,'الضحى','Ad-Dhuha',11,596,596),(94,'الشرح','Asy-Syarh',8,596,596),(95,'التين','At-Tin',8,597,597),(96,'العلق','Al-Alaq',19,597,597),(97,'القدر','Al-Qadr',5,598,598),(98,'البينة','Al-Bayyinah',8,598,599),(99,'الزلزلة','Az-Zalzalah',8,599,599),(100,'العاديات','Al-Adiyat',11,599,600),(101,'القارعة','Al-Qariah',11,600,600),(102,'التكاثر','At-Takatsur',8,600,600),(103,'العصر','Al-Asr',3,601,601),(104,'الهمزة','Al-Humazah',9,601,601),(105,'الفيل','Al-Fil',5,601,601),(106,'قريش','Quraisy',4,602,602),(107,'الماعون','Al-Maun',7,602,602),(108,'الكوثر','Al-Kautsar',3,602,602),(109,'الكافرون','Al-Kafirun',6,603,603),(110,'النصر','An-Nasr',3,603,603),(111,'المسد','Al-Lahab',5,603,603),(112,'الإخلاص','Al-Ikhlas',4,604,604),(113,'الفلق','Al-Falaq',5,604,604),(114,'الناس','An-Nas',6,604,604);
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,17,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat A (99.7).','success','/akfi/pribadi',0,'2026-09-24 10:34:44'),(2,30,'Jadwal Supervisi Kelas','Anda dijadwalkan supervisi pembelajaran pada tanggal 2026-09-24.','info','/supervisi/riwayat',0,'2026-09-24 18:57:15'),(3,30,'Hasil Supervisi Kelas Selesai','Supervisi pembelajaran Anda telah dinilai dengan skor 100 (SANGAT BAIK).','success','/supervisi/riwayat',0,'2026-09-24 18:57:41'),(4,36,'Pengajuan AKFI Baru','Pegawai telah mengajukan penilaian mandiri AKFI dan siap dinilai atasan.','info','/akfi/assess/26',0,'2026-09-25 21:22:16'),(5,37,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat C (69.23).','success','/akfi/pribadi',0,'2026-09-25 21:32:09'),(6,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (89.8).','success','/akfi/pribadi',0,'2026-09-25 21:36:28'),(7,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (89.8).','success','/akfi/pribadi',0,'2026-09-25 21:38:53'),(8,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (89.8).','success','/akfi/pribadi',0,'2026-09-25 21:40:43'),(9,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (86.6).','success','/akfi/pribadi',0,'2026-09-25 21:41:16'),(10,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (83).','success','/akfi/pribadi',0,'2026-09-25 21:41:32'),(11,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (83).','success','/akfi/pribadi',0,'2026-09-25 21:42:09'),(12,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (83).','success','/akfi/pribadi',0,'2026-09-25 21:42:26'),(13,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (83).','success','/akfi/pribadi',0,'2026-09-25 21:42:48'),(14,8,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (83).','success','/akfi/pribadi',0,'2026-09-25 21:43:27'),(15,9,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat B (85).','success','/akfi/pribadi',0,'2026-09-25 21:44:04'),(16,9,'Penilaian AKFI Disetujui Atasan','Penilaian AKFI Anda telah diverifikasi oleh atasan dengan predikat C (64.3).','success','/akfi/pribadi',0,'2026-09-26 04:08:06'),(17,8,'Pengajuan Masal Surat Tagihan SPP','TU telah mengajukan 2 rekomendasi pengiriman surat tagihan SPP untuk ditinjau.','warning','/tagihan/approval',0,'2026-09-26 05:53:47'),(18,28,'Jadwal Supervisi Kelas','Anda dijadwalkan supervisi pembelajaran pada tanggal 2026-09-27.','info','/supervisi/riwayat',0,'2026-09-27 07:33:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `panitia_lomba_assignments`
--

LOCK TABLES `panitia_lomba_assignments` WRITE;
/*!40000 ALTER TABLE `panitia_lomba_assignments` DISABLE KEYS */;
INSERT INTO `panitia_lomba_assignments` VALUES (1,3,1,26,'OSN',1,'Selesai',NULL,'','2026-09-25 07:24:18'),(2,16,1,26,'O2SN',1,'Selesai','[19]','10/SD/AIS/2026','2026-09-25 21:29:41'),(3,3,1,25,'OSN',1,'Aktif','[1,4,6]','422.1/SK-LOMBA/2026/001','2026-09-27 12:33:30');
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prestasi_siswa`
--

LOCK TABLES `prestasi_siswa` WRITE;
/*!40000 ALTER TABLE `prestasi_siswa` DISABLE KEYS */;
INSERT INTO `prestasi_siswa` VALUES (3,3,1,1,'OSN','Olimpiade Sains Kuark',NULL,'Juara 1','Kota Batam',NULL,0,'2026-09-24 13:06:58'),(4,3,3,1,'OSN','Lomba Tahfidz Cilik',NULL,'Juara 2','Tingkat Kecamatan',NULL,0,'2026-09-24 13:06:58'),(5,16,20,1,'Swasta','Lomba Panahan',NULL,'Juara 1','Tingkat Kota Batam',NULL,37,'2026-09-25 21:21:43');
/*!40000 ALTER TABLE `prestasi_siswa` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=64 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'operator_yayasan','Operator Yayasan','Akses penuh yayasan, kelola sekolah, pegawai, mutasi, master AKFI, backup'),(2,'direktur_pendidikan','Direktur Pendidikan','Dashboard yayasan, rekap AKFI, Atasan Berikut / HR'),(3,'deputi_pendidikan','Deputi Direktur Pendidikan','Approval administrasi KS, deadline, SPMB target, AKFI KS, sikap KS'),(4,'operator_sekolah','Operator Sekolah','Kelola siswa, kelas, mapel, akun ortu/guru, persetujuan pindah TA, SPMB'),(5,'tutor_siswa','Tutor Siswa','Uji tahfidz siswa, penentuan lulus juz, input nilai bulanan tahfidz guru'),(6,'tutor_sebaya','Tutor Sebaya','Assign halaqah guru, target hafalan guru, input nilai tahfidz guru bulanan'),(7,'tata_usaha','Tata Usaha','Tagihan manual siswa, pengajuan penagihan, upload berkas keuangan, checklist braja'),(8,'cleaning_service','Cleaning Service','Checklist harian, piket bulanan, gotong royong Sabtu, kepuasan kebersihan'),(9,'security','Security','Checklist laporan harian per hari ini, patroli, insiden kebobolan/gagalkan kriminalitas'),(10,'waka_kurikulum','Wakil Kurikulum','Pencatatan pelanggaran kehadiran guru, absen kajian guru, jadwal supervisi, deadline admin'),(11,'guru_supervisor','Guru Supervisor','Penilaian supervisi kelas (rubrik A/B/C), cetak instrumen'),(12,'waka_kesiswaan','Wakil Kesiswaan','Kehadiran, kajian, verifikasi prestasi lomba siswa'),(13,'koor_tahfidz','Koordinator Tahfidz','Assign halaqah siswa, rekap tuntas siswa, baseline 3x setahun, cetak rapor tahfidz'),(14,'walas','Guru Wali Kelas','Nilai bulanan, absensi siswa, leger, rapor bulanan, PTK, rekomendasi kenaikan/alumni'),(15,'guru_mapel','Guru Mapel Umum','Nilai bulanan, KKM, Best Practice, administrasi perangkat 8 item'),(16,'guru_diniyah','Guru Mapel Diniyah','Nilai diniyah bulanan, Best Practice, administrasi perangkat 8 item'),(17,'guru_tahfidz','Guru Tahfidz','Input hafalan siswa bulanan (kalkulator madinah), pengajuan ujian tutor siswa'),(18,'orang_tua','Orang Tua','Unduh rapor bulanan/tahfidz/diniyah/gabungan, survei ortu 53 butir, cek tagihan'),(19,'panitia_lomba','Ketua Panitia Lomba','Input siswa juara lomba OSN/O2SN/FLS2N tingkat kota/provinsi/nasional'),(20,'kepala_sekolah','Kepala Sekolah','Approval admin guru/TU, rekap hafalan/kegiatan/tunggakan, absensi TU/CS/Security, sikap guru'),(21,'siswa','Siswa (SMP/SMA)','Akses siswa untuk survei kepuasan pembelajaran');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `security_patrols`
--

LOCK TABLES `security_patrols` WRITE;
/*!40000 ALTER TABLE `security_patrols` DISABLE KEYS */;
INSERT INTO `security_patrols` VALUES (1,3,20,'2026-09-24','12:00:00','12:26:48','Perimeter & Pagar Luar','test','2026-09-24 12:26:48');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spmb_registrations`
--

LOCK TABLES `spmb_registrations` WRITE;
/*!40000 ALTER TABLE `spmb_registrations` DISABLE KEYS */;
INSERT INTO `spmb_registrations` VALUES (1,3,1,'SPMB-SD-2025-001','Muhammad Salman','L','Iskandar Zulkarnain','081277112233',NULL,'TK Al-Furqan Batam','Terdaftar',NULL,'2026-09-23 18:43:17'),(2,3,1,'SPMB-SD-2025-002','Nabila Zahira','P','Rahmat Hidayat','081277112234',NULL,'TK Pertiwi Batam','Diterima',NULL,'2026-09-23 18:43:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `spmb_targets`
--

LOCK TABLES `spmb_targets` WRITE;
/*!40000 ALTER TABLE `spmb_targets` DISABLE KEYS */;
INSERT INTO `spmb_targets` VALUES (1,1,1,50,0,3,'2026-09-23 18:43:17'),(2,2,1,100,0,3,'2026-09-23 18:43:17'),(3,3,1,100,80,3,'2026-09-23 18:43:17'),(4,4,1,100,0,3,'2026-09-23 18:43:17'),(5,5,1,100,0,3,'2026-09-23 18:43:17'),(8,16,1,100,60,1,'2026-09-25 21:16:27');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_attendance`
--

LOCK TABLES `student_attendance` WRITE;
/*!40000 ALTER TABLE `student_attendance` DISABLE KEYS */;
INSERT INTO `student_attendance` VALUES (9,1,3,1,1,24,25,0,0,0,0,96.00,0,0,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(10,3,3,1,1,25,25,0,0,0,0,100.00,0,0,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(11,4,3,1,1,23,25,0,0,0,0,92.00,0,0,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(12,19,13,1,9,20,20,0,0,0,0,100.00,0,37,'2026-09-25 21:19:15','2026-09-25 21:19:15');
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `student_grades`
--

LOCK TABLES `student_grades` WRITE;
/*!40000 ALTER TABLE `student_grades` DISABLE KEYS */;
INSERT INTO `student_grades` VALUES (10,1,3,6,1,0,88.00,75.00,0,0,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(11,2,3,6,1,0,85.00,75.00,0,0,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(12,3,3,6,1,0,86.00,75.00,0,0,'2026-09-24 13:06:58','2026-09-24 13:06:58');
/*!40000 ALTER TABLE `student_grades` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `students`
--

LOCK TABLES `students` WRITE;
/*!40000 ALTER TABLE `students` DISABLE KEYS */;
INSERT INTO `students` VALUES (1,'20240101','0012345671','2171011505180001','2171012345670001','Ahmad Faris Gunawan','L','Batam','2018-05-15','Jl. Sukajadi No. 12, Batam','Hendra Gunawan','Siti Fatimah','08127000001','ortu.santri@alkahfi.sch.id',26,NULL,3,'Aktif','2026-09-23 18:43:17','2026-09-23 18:43:17'),(2,'20240201','0012345672','2171012008200002','2171012345670001','Fatimah Az-Zahra Gunawan','P','Batam','2020-08-20','Jl. Sukajadi No. 12, Batam','Hendra Gunawan','Siti Fatimah','08127000001','ortu.santri@alkahfi.sch.id',26,NULL,2,'Aktif','2026-09-23 18:43:17','2026-09-23 18:43:17'),(3,'20240102','0012345673','2171011003180003','2171012345670002','Zaidan Al-Ghifari','L','Batam','2018-03-10','Batam Centre Blok B No. 5','Bambang Irawan','Nurul Hidayah','08127000003','bambang@gmail.com',NULL,NULL,3,'Aktif','2026-09-23 18:43:17','2026-09-23 18:43:17'),(4,'20240103','0012345674','2171012507180004','2171012345670003','Aisyah Putri Maulida','P','Batam','2018-07-25','Tiban Indah Permai C-8','Maulana Malik','Ratna Sari','08127000004','maulana@gmail.com',NULL,NULL,3,'Aktif','2026-09-23 18:43:17','2026-09-23 18:43:17'),(5,'20240301','0012345675','2171011111100005','2171012345670004','Muhammad Rayyan','L','Batam','2010-11-11','Nongsa Asri Blok D No. 2','Ridwan Kamil','Laila Sari','08127000005','ridwan@gmail.com',NULL,27,4,'Aktif','2026-09-23 18:43:17','2026-09-23 18:43:17'),(6,'0123123','0123123','1171092110939001','2171092110939001','Sarah Batrisya','P','',NULL,'','Syahidi Rahman','','08566691395','saidi165@gmail.com',29,NULL,3,'Aktif','2026-09-24 10:02:04','2026-09-24 10:02:04'),(7,'0456456','0456456','2171091221454','2171092110939001','Batrisya Al Sarah','L','',NULL,'','Syahidi Rahman','','08566691395','saidi165@gmail.com',29,NULL,3,'Aktif','2026-09-24 10:16:18','2026-09-24 10:16:18'),(8,'20190301',NULL,NULL,'2171000000000000','Muhammad Fatih Pratama','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(9,'20190302',NULL,NULL,'2171000000000000','Nabila Syakirah Al-Zahra','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(10,'20190303',NULL,NULL,'2171000000000000','Rayhan Al-Fatih Syahputra','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(11,'20190304','','','2171000000000000','Khadijah Putri Wardani','P','',NULL,'','','','','',35,NULL,3,'Aktif','2026-09-24 20:36:59','2026-09-24 21:58:18'),(12,'20190305',NULL,NULL,'2171000000000000','Salman Al-Farisi Ramadhan','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,3,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(13,'20230201',NULL,NULL,'2171000000000000','Ibrahim Hasan Al-Banna','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(14,'20230202',NULL,NULL,'2171000000000000','Maryam Salsabila','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,2,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(15,'20210401',NULL,NULL,'2171000000000000','Abdullah Azzam Hidayat','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(16,'20210402',NULL,NULL,'2171000000000000','Salma Nailaturrahmah','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,4,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(17,'20210501',NULL,NULL,'2171000000000000','Zulfa Khairunnisa','P',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(18,'20210502',NULL,NULL,'2171000000000000','Thoriq Ziyad Al-Farabi','L',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,5,'Aktif','2026-09-24 20:36:59','2026-09-24 20:36:59'),(19,'00000000','00000000','2171092110939001','2171092110939001','Sarah Batrisya AIS','P','',NULL,'','Syahidi Rahman','','','saidi165@gmail.com',29,NULL,16,'Aktif','2026-09-25 21:12:22','2026-09-25 21:12:22'),(20,'213123123','123123123','123213123123','123213213123123123','Alumni A','L','',NULL,'','ASAS','','','',39,NULL,16,'Aktif','2026-09-25 21:15:24','2026-09-25 21:15:24');
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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `subjects`
--

LOCK TABLES `subjects` WRITE;
/*!40000 ALTER TABLE `subjects` DISABLE KEYS */;
INSERT INTO `subjects` VALUES (1,2,'TK-01','Baca Latin','Baca Latin','2026-09-23 18:42:59','2026-09-23 18:42:59'),(2,2,'TK-02','Tahsin Al-Quran','Tahsin','2026-09-23 18:43:17','2026-09-23 18:43:17'),(3,2,'TK-03','Tahfidz Al-Quran','Tahfidz','2026-09-23 18:43:17','2026-09-23 18:43:17'),(4,2,'TK-04','National Kurikulum TK','National Kurikulum','2026-09-23 18:42:59','2026-09-23 18:42:59'),(5,2,'TK-05','Diniyah & Doa Harian','Diniyah','2026-09-23 18:42:59','2026-09-23 18:42:59'),(6,3,'SD-MAT','Matematika','Umum','2026-09-23 18:42:59','2026-09-23 18:42:59'),(7,3,'SD-BIN','Bahasa Indonesia','Umum','2026-09-23 18:42:59','2026-09-23 18:42:59'),(8,3,'SD-IPA','Ilmu Pengetahuan Alam','Umum','2026-09-23 18:42:59','2026-09-23 18:42:59'),(9,3,'SD-PAI','Pendidikan Agama Islam','Diniyah','2026-09-23 18:42:59','2026-09-23 18:42:59'),(10,3,'SD-ARB','Bahasa Arab','Diniyah','2026-09-23 18:42:59','2026-09-23 18:42:59'),(11,3,'SD-TAH','Tahfidzul Quran','Tahfidz','2026-09-23 18:43:17','2026-09-23 18:43:17'),(12,3,'SD-THS','Tahsin & Tilawah','Tahsin','2026-09-23 18:43:17','2026-09-23 18:43:17'),(13,4,'SMP-MAT','Matematika','Umum','2026-09-23 18:43:17','2026-09-23 18:43:17'),(14,4,'SMP-ING','Bahasa Inggris','Umum','2026-09-23 18:43:17','2026-09-23 18:43:17'),(15,4,'SMP-TAH','Tahfidzul Quran','Tahfidz','2026-09-23 18:43:17','2026-09-23 18:43:17'),(16,4,'SMP-THS','Tahsin Al-Quran','Tahsin','2026-09-23 18:43:17','2026-09-23 18:43:17'),(17,4,'SMP-DIN','Fiqih & Aqidah Akhlak','Diniyah','2026-09-23 18:43:17','2026-09-23 18:43:17'),(18,16,'MATH','Matematika','Umum','2026-09-25 21:13:40','2026-09-25 21:13:40');
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
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
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supervisi_sessions`
--

LOCK TABLES `supervisi_sessions` WRITE;
/*!40000 ALTER TABLE `supervisi_sessions` DISABLE KEYS */;
INSERT INTO `supervisi_sessions` VALUES (31,3,1,3,12,6,3,'2026-02-15',28.50,37.00,28.00,93.50,100.00,93.50,'SANGAT BAIK',NULL,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(32,3,1,3,7,6,3,'2026-02-15',26.00,35.00,27.00,88.00,100.00,88.00,'BAIK',NULL,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(33,3,1,3,13,6,4,'2026-02-15',23.50,32.00,24.00,79.50,100.00,79.50,'CUKUP',NULL,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(34,3,1,3,9,11,3,'2026-02-15',27.00,37.00,27.00,91.00,100.00,91.00,'SANGAT BAIK',NULL,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(35,3,1,3,15,12,3,'2026-02-15',26.00,34.50,26.00,86.50,100.00,86.50,'BAIK',NULL,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(36,3,1,3,16,6,3,'2026-02-15',29.00,38.00,28.00,95.00,100.00,95.00,'SANGAT BAIK',NULL,NULL,'2026-09-24 13:06:58','2026-09-24 13:06:58'),(38,3,1,16,25,7,3,'2026-09-27',0.00,0.00,0.00,0.00,100.00,0.00,'PERLU PENINGKATAN',NULL,NULL,'2026-09-27 07:33:00','2026-09-27 07:33:00');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `survey_responses`
--

LOCK TABLES `survey_responses` WRITE;
/*!40000 ALTER TABLE `survey_responses` DISABLE KEYS */;
INSERT INTO `survey_responses` VALUES (1,2,16,1,37,'{\"1\":\"2\",\"2\":\"2\",\"3\":\"3\",\"4\":\"2\",\"5\":\"2\",\"6\":\"2\",\"7\":\"2\"}',15,35,42.86,'','2026-09-25 21:22:36');
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
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_exams`
--

LOCK TABLES `tahfidz_exams` WRITE;
/*!40000 ALTER TABLE `tahfidz_exams` DISABLE KEYS */;
INSERT INTO `tahfidz_exams` VALUES (1,1,3,1,20,NULL,30,NULL,NULL,'Menunggu','',NULL,'2026-09-26 05:48:59');
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
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_halaqah`
--

LOCK TABLES `tahfidz_halaqah` WRITE;
/*!40000 ALTER TABLE `tahfidz_halaqah` DISABLE KEYS */;
INSERT INTO `tahfidz_halaqah` VALUES (1,3,1,9,'Siswa','Halaqah Imam Nafi (SD)','2026-09-23 18:43:17'),(2,3,1,10,'Guru','Halaqah Guru SD Al Kahfi','2026-09-23 18:43:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_halaqah_members`
--

LOCK TABLES `tahfidz_halaqah_members` WRITE;
/*!40000 ALTER TABLE `tahfidz_halaqah_members` DISABLE KEYS */;
INSERT INTO `tahfidz_halaqah_members` VALUES (1,1,1,NULL),(2,1,3,NULL),(3,1,4,NULL),(4,2,NULL,7),(5,2,NULL,8),(6,2,NULL,12),(7,2,NULL,13),(8,2,NULL,14),(9,2,NULL,18),(10,2,NULL,19),(11,2,NULL,20);
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
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_setoran_guru`
--

LOCK TABLES `tahfidz_setoran_guru` WRITE;
/*!40000 ALTER TABLE `tahfidz_setoran_guru` DISABLE KEYS */;
INSERT INTO `tahfidz_setoran_guru` VALUES (26,1,9,1,1,'SHML',0,0,0,0,0,0,300,1,NULL,0,'2026-09-24 13:06:58'),(27,1,15,1,1,'SHML',0,0,0,0,0,0,300,1,NULL,0,'2026-09-24 13:06:58'),(28,1,12,1,1,'SHML',0,0,0,0,0,0,300,1,NULL,0,'2026-09-24 13:06:58'),(29,1,7,1,1,'SHML',0,0,0,0,0,0,240,0,NULL,0,'2026-09-24 13:06:58'),(30,1,13,1,1,'SHML',0,0,0,0,0,0,180,0,NULL,0,'2026-09-24 13:06:58');
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahfidz_targets`
--

LOCK TABLES `tahfidz_targets` WRITE;
/*!40000 ALTER TABLE `tahfidz_targets` DISABLE KEYS */;
INSERT INTO `tahfidz_targets` VALUES (1,1,'Siswa',3,'SD 1',300,'Surah An-Nas','Juz 30 (300 Baris)','2026-09-23 18:43:17'),(2,1,'Guru',3,'GURU_SD',300,'Juz 29','1 Juz (300 Baris)','2026-09-23 18:43:17'),(3,1,'Guru',2,'GURU_TK',150,'Juz 30','1/2 Juz (150 Baris)','2026-09-23 18:43:17');
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teacher_subjects`
--

LOCK TABLES `teacher_subjects` WRITE;
/*!40000 ALTER TABLE `teacher_subjects` DISABLE KEYS */;
INSERT INTO `teacher_subjects` VALUES (1,12,6,3,1),(2,12,7,3,1),(3,13,6,4,1),(4,14,9,3,1),(6,15,11,3,1),(7,15,12,3,1),(8,26,18,13,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `units`
--

LOCK TABLES `units` WRITE;
/*!40000 ALTER TABLE `units` DISABLE KEYS */;
INSERT INTO `units` VALUES (1,'KB','KB ALKAHFI ISLAMIC SCHOOL','KB','YAYASAN ISLAM AL KAHFI BATAM','PENDIDIKAN ANAK USIA DINI (KB) ISLAM AL KAHFI','Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam','0778-123456','kb@alkahfi.sch.id','Bank Syariah Indonesia (BSI)','7123450001','Yayasan Islam Al Kahfi Batam',NULL,NULL,587,NULL,NULL,'tls','uploads/logos/logo_unit_1_1790478650.png','2026-09-23 18:42:58','2026-09-27 10:10:50'),(2,'TK','TK ALKAHFI ISLAMIC SCHOOL','TK','YAYASAN ISLAM AL KAHFI BATAM','TAMAN KANAK-KANAK ISLAM AL KAHFI','Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam','0778-123457','tk@alkahfi.sch.id','Bank Syariah Indonesia (BSI)','7123450002','Yayasan Islam Al Kahfi Batam',NULL,NULL,587,NULL,NULL,'tls','uploads/logos/logo_unit_2_1790478656.png','2026-09-23 18:42:58','2026-09-27 10:10:56'),(3,'SD','SD Islam Al Kahfi Batam','SD','YAYASAN ISLAM AL KAHFI BATAM','SEKOLAH DASAR ISLAM AL KAHFI','Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam','0778-123458','sd@alkahfi.sch.id','Bank Syariah Indonesia (BSI)','7123450003','Yayasan Islam Al Kahfi Batam',NULL,NULL,587,NULL,NULL,'tls',NULL,'2026-09-23 18:42:58','2026-09-23 18:42:58'),(4,'SMP','SMP ALKAHFI ISLAMIC SCHOOL','SMP','YAYASAN ISLAM AL KAHFI BATAM','SEKOLAH MENENGAH PERTAMA ISLAM AL KAHFI','Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam','0778-123459','smp@alkahfi.sch.id','Bank Syariah Indonesia (BSI)','7123450004','Yayasan Islam Al Kahfi Batam',NULL,NULL,587,NULL,NULL,'tls','uploads/logos/logo_unit_4_1790478666.png','2026-09-23 18:42:58','2026-09-27 10:11:06'),(5,'SMA','SMA ALKAHFI ISLAMIC SCHOOL','SMA','YAYASAN ISLAM AL KAHFI BATAM','SEKOLAH MENENGAH ATAS ISLAM AL KAHFI','Komp. Al Kahfi Islamic Center, Batam Centre, Kota Batam','0778-123460','sma@alkahfi.sch.id','Bank Syariah Indonesia (BSI)','7123450005','Yayasan Islam Al Kahfi Batam',NULL,NULL,587,NULL,NULL,'tls','uploads/logos/logo_unit_5_1790478672.png','2026-09-23 18:42:58','2026-09-27 10:12:17'),(16,'SD-01','SD ALKAHFI ISLAMIC SCHOOL','SD','YAYASAN ISLAM AL KAHFI BATAM','SD ALKAHFI ISLAMIC SCHOOL','Jl Ruko Imperium Superblock','0000000000','humas@aisbatam.sch.id','BSI','1033667414','Syahidi Rahman',NULL,NULL,587,NULL,NULL,'tls','uploads/logos/logo_unit_16_1790372879.png','2026-09-25 20:59:22','2026-09-26 04:47:59'),(17,'SD-FI 1','SD FAJAR ILAHI 1','SD','YAYASAN ISLAM AL KAHFI BATAM','SEKOLAH DASAR FAJAR ILAHI 1','batu aji','0000000','sditfi1@gmail.com','BSI','1002010202','SDIT FI 1',NULL,NULL,587,NULL,NULL,'tls','uploads/logos/logo_unit_17_1790504740.png','2026-09-27 17:25:16','2026-09-27 17:25:40');
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
INSERT INTO `user_roles` VALUES (1,1,1),(2,2,1),(3,3,1),(4,4,1),(5,4,1),(6,4,1),(7,4,1),(8,20,1),(9,20,1),(10,20,1),(11,20,1),(12,10,1),(12,11,0),(12,15,0),(13,12,1),(14,5,1),(14,17,0),(15,6,1),(16,13,1),(17,14,1),(17,15,0),(18,15,1),(19,16,1),(20,17,1),(21,11,1),(22,19,1),(23,7,1),(24,8,1),(25,9,1),(26,18,1),(27,21,1),(28,11,0),(28,14,0),(28,15,1),(29,18,1),(31,13,0),(31,17,1),(32,13,1),(33,13,1),(34,13,1),(35,18,1),(36,20,1),(37,14,0),(37,15,1),(38,4,1),(39,18,1),(40,20,1),(41,4,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'operator.yayasan','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','admin@alkahfi.sch.id','Ahmad Operator Yayasan','08110000001',NULL,1,0,0,NULL,'2026-09-29 05:33:23','2026-09-23 18:42:59','2026-09-29 05:33:23'),(2,'direktur.pendidikan','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','direktur@alkahfi.sch.id','Dr. H. Muhammad Ridwan, M.Pd (Direktur/HR)','08110000002',NULL,1,0,0,NULL,'2026-09-27 19:40:42','2026-09-23 18:42:59','2026-09-27 19:40:42'),(3,'deputi.pendidikan','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','deputi@alkahfi.sch.id','Ust. Fathurrahman, S.Pd.I (Deputi Direktur)','08110000003',NULL,1,0,0,NULL,'2026-09-27 19:41:05','2026-09-23 18:42:59','2026-09-27 19:41:05'),(4,'operator.sd','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','ops.sd@alkahfi.sch.id','Rian Hidayat, S.Kom (Operator SD)','08110000004',3,1,0,0,NULL,'2026-09-29 05:29:43','2026-09-23 18:42:59','2026-09-29 05:29:43'),(5,'operator.tk','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','ops.tk@alkahfi.sch.id','Siti Rahma (Operator TK)','08110000005',2,1,0,0,NULL,'2026-09-24 21:28:42','2026-09-23 18:42:59','2026-09-24 21:28:42'),(6,'operator.smp','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','ops.smp@alkahfi.sch.id','Budi Santoso (Operator SMP)','08110000006',4,1,0,0,NULL,NULL,'2026-09-23 18:42:59','2026-09-24 05:49:07'),(7,'operator.sma','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','ops.sma@alkahfi.sch.id','Dewi Lestari (Operator SMA)','08110000007',5,1,0,0,NULL,NULL,'2026-09-23 18:42:59','2026-09-24 05:49:07'),(8,'kepsek.sd','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','kepsek.sd@alkahfi.sch.id','Ust. Abdullah Syafiq, M.Pd.I (Kepsek SD)','08110000008',3,1,0,0,NULL,'2026-09-27 19:44:18','2026-09-23 18:42:59','2026-09-27 19:44:18'),(9,'kepsek.tk','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','kepsek.tk@alkahfi.sch.id','Usth. Maryam Zahra, S.Pd (Kepsek TK)','08110000009',2,1,0,0,NULL,'2026-09-24 21:26:51','2026-09-23 18:42:59','2026-09-24 21:26:51'),(10,'kepsek.smp','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','kepsek.smp@alkahfi.sch.id','Ust. Salman Al Farisi, Lc (Kepsek SMP)','08110000010',4,1,0,0,NULL,NULL,'2026-09-23 18:42:59','2026-09-24 05:49:07'),(11,'kepsek.sma','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','kepsek.sma@alkahfi.sch.id','Ust. H. Zainuddin, M.A (Kepsek SMA)','08110000011',5,1,0,0,NULL,NULL,'2026-09-23 18:42:59','2026-09-24 05:49:07'),(12,'waka.kurikulum','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','waka.kurikulum@alkahfi.sch.id','Umar Hamzah, M.Pd (Waka Kurikulum SD)','08110000012',3,1,0,0,NULL,'2026-09-27 19:46:12','2026-09-23 18:42:59','2026-09-27 19:46:12'),(13,'waka.kesiswaan','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','waka.kesiswaan@alkahfi.sch.id','Khalid Basalamah, S.Pd (Waka Kesiswaan SD)','08110000013',3,1,0,0,NULL,NULL,'2026-09-23 18:42:59','2026-09-24 05:49:07'),(14,'tutor.siswa','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','tutor.siswa@alkahfi.sch.id','Ust. Hanif Al Hafidz (Tutor Siswa & Guru Tahfidz)','08110000014',3,1,0,0,NULL,'2026-09-27 19:50:31','2026-09-23 18:42:59','2026-09-27 19:50:31'),(15,'tutor.sebaya','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','tutor.sebaya@alkahfi.sch.id','Ust. Muadz Al Hafidz (Tutor Sebaya Guru)','08110000015',3,1,0,0,NULL,'2026-09-27 19:51:06','2026-09-23 18:42:59','2026-09-27 19:51:06'),(16,'koor.tahfidz','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','koor.tahfidz@alkahfi.sch.id','Ust. Bilal Syahid, S.Q (Koordinator Tahfidz)','08110000016',3,1,0,0,NULL,'2026-09-29 05:28:44','2026-09-23 18:42:59','2026-09-29 05:28:44'),(17,'walas.sd','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','walas.sd@alkahfi.sch.id','Aisyah Wardani, S.Pd (Guru Walas 1A)','08110000017',3,1,0,0,NULL,'2026-09-27 19:46:50','2026-09-23 18:42:59','2026-09-27 19:46:50'),(18,'guru.mapel','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','guru.mapel@alkahfi.sch.id','Fajar Pratama, S.Pd (Guru Matematika)','08110000018',3,1,0,0,NULL,'2026-09-27 19:47:50','2026-09-23 18:42:59','2026-09-27 19:47:50'),(19,'guru.diniyah','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','guru.diniyah@alkahfi.sch.id','Ust. Hamzah Robbani, Lc (Guru PAI/Fiqih)','08110000019',3,1,0,0,NULL,NULL,'2026-09-23 18:42:59','2026-09-24 05:49:07'),(20,'guru.tahfidz','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','guru.tahfidz@alkahfi.sch.id','Usth. Khadijah Al Hafidzah (Guru Tahfidz SD)','08110000020',3,1,0,0,NULL,'2026-09-29 05:22:17','2026-09-23 18:42:59','2026-09-29 05:22:17'),(21,'guru.supervisor','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','guru.supervisor@alkahfi.sch.id','Drs. Supriyadi, M.Pd (Guru Supervisor)','08110000021',3,1,0,0,NULL,'2026-09-27 19:53:11','2026-09-23 18:42:59','2026-09-27 19:53:11'),(22,'panitia.lomba','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','panitia.lomba@alkahfi.sch.id','Indra Gunawan, S.Pd (Ketua Panitia OSN)','08110000022',3,1,0,0,NULL,NULL,'2026-09-23 18:42:59','2026-09-24 05:49:07'),(23,'tu.sd','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','tu.sd@alkahfi.sch.id','Sri Wahyuni, A.Md (Staf TU Keuangan SD)','08110000023',3,1,0,0,NULL,'2026-09-27 19:51:45','2026-09-23 18:42:59','2026-09-27 19:51:45'),(24,'cs.sd','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','cs.sd@alkahfi.sch.id','Pak Joko Sutrisno (Cleaning Service SD)','08110000024',3,1,0,0,NULL,'2026-09-27 19:52:20','2026-09-23 18:42:59','2026-09-27 19:52:20'),(25,'security.sd','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','security.sd@alkahfi.sch.id','Pak Agus Salim (Komandan Security SD)','08110000025',3,1,0,0,NULL,'2026-09-27 19:52:38','2026-09-23 18:42:59','2026-09-27 19:52:38'),(26,'ortu.santri','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','ortu.santri@alkahfi.sch.id','Bpk. Hendra Gunawan (Wali Santri No. KK 2171012345670001)','08127000001',NULL,1,0,0,NULL,'2026-09-27 21:00:17','2026-09-23 18:42:59','2026-09-27 21:00:17'),(27,'siswa.smp','$2y$10$ZBjEuAo13K8d7567rki5cujPiaxxHR8mI6/f1uQkxm2I6OiYpVnia','siswa.smp@alkahfi.sch.id','Muhammad Rayyan (Siswa SMP Kelas 8A)','08127000002',NULL,1,0,0,NULL,'2026-09-24 21:44:10','2026-09-23 18:42:59','2026-09-24 21:44:10'),(28,'syahidi','$2y$10$jL9FH8pvAw6K4y/LEj9lQO4X6IwP0KG67S6JIHfFN9ziCqH9eylhi','syahidi@aisbatam.sch.id','Syahidi Rahman, S,Pd, Gr','08566691395',3,1,0,0,NULL,'2026-09-27 07:34:45','2026-09-24 09:02:37','2026-09-27 07:34:45'),(29,'2171092110939001','$2y$10$NDeiqfo6EQDuVhDp3Wu14e.SO2GvQrjEX3onrBVyZb0JP6F5Y5AYS','saidi165@gmail.com','Syahidi Rahman','08566691395',NULL,1,0,0,NULL,'2026-09-24 18:00:56','2026-09-24 10:02:04','2026-09-24 18:00:56'),(30,'sd-ramb-204','$2y$10$wAeBKwmddjIkH5uzarhhyuyHyiHjy9w/xiO00/NVchpGIP54UHlUi','salehrambe@aisbatam.sch.id','Ahmad Saleh Rambe',NULL,16,1,0,0,NULL,'2026-09-25 11:58:26','2026-09-24 10:25:54','2026-09-27 07:50:55'),(31,'azmi','$2y$10$7QTLv4wMpCaPD8CbsWzTuez1CtxFGLgTQSPg36PfdJs17OQva.Cxi',NULL,'Azmi',NULL,3,1,0,0,NULL,'2026-09-25 06:05:43','2026-09-24 20:45:08','2026-09-25 06:05:43'),(32,'koor.tahfidz.kbtk','$2y$10$0H5feev.4pZHUzRZtJjQAOquIP39DZbgrsFZj2F1D9vv7Z4UbaU.6','koor.tahfidz.kbtk@alkahfi.sch.id','Usth. Maryam Al-Hafidzah, S.Pd.I',NULL,NULL,1,0,0,NULL,NULL,'2026-09-24 21:16:40','2026-09-24 21:16:40'),(33,'koor.tahfidz.smp','$2y$10$PbJJ2We/wv5dlQNpUU58WuWNbu0ZdPJjodJLmjILGcotuNWQQRBFS','koor.tahfidz.smp@alkahfi.sch.id','Ust. Zulkifli Al-Hafidz, Lc',NULL,NULL,1,0,0,NULL,NULL,'2026-09-24 21:17:12','2026-09-24 21:17:12'),(34,'koor.tahfidz.sma','$2y$10$PbJJ2We/wv5dlQNpUU58WuWNbu0ZdPJjodJLmjILGcotuNWQQRBFS','koor.tahfidz.sma@alkahfi.sch.id','Ust. Salman Al-Farisi, S.Q',NULL,NULL,1,0,0,NULL,NULL,'2026-09-24 21:17:12','2026-09-24 21:17:12'),(35,'2171000000000000','$2y$10$4iwcnsv72RL9OnU0tJX4V.Nn4HBBFBNAqF4jch7CUQ19e0ce5tmBW','','Orang Tua Khadijah Putri Wardani','',NULL,1,1,0,NULL,NULL,'2026-09-24 21:49:29','2026-09-24 21:49:29'),(36,'putra.perdana.tanjung','$2y$10$Uxo818Rieh1WpkUnznVjo.2duOmJJFninol5JSWYTeA8plifW1wxS',NULL,'Putra Perdana Tanjung',NULL,16,1,0,0,NULL,'2026-09-27 12:34:24','2026-09-25 21:03:00','2026-09-27 12:34:24'),(37,'faisal.danu','$2y$10$o66Nbpd.QCYVQQXmGZBC1.HYrSsnr0tJPUZYqXBneUZT1IIe0Ibie',NULL,'Faisal Danu',NULL,16,1,0,0,NULL,'2026-09-25 21:23:49','2026-09-25 21:04:06','2026-09-25 21:23:49'),(38,'operator.sd.ais','$2y$10$47D5AJcqy6krKZe6oSx4/OLysM.7sIum.id.GK2VaXd4K7Rc5Qkia',NULL,'Erzayani',NULL,16,1,0,0,NULL,'2026-09-27 15:09:04','2026-09-25 21:05:00','2026-09-27 15:09:04'),(39,'123213213123123123','$2y$10$WSJPp9d7sKjjC2Q6oLGaQeFsEfrm0Dr9sdlJFSwlCQC67FJn98P1m','','ASAS','',NULL,1,1,0,NULL,NULL,'2026-09-25 21:15:24','2026-09-25 21:15:24'),(40,'08566691395','$2y$10$s08TFm3aooBOWjnMpw7Dhu/q8DXIXxwtOujMi.LmDOZIIwoqoyuOS',NULL,'Hanifan Ramadhan',NULL,17,1,0,0,NULL,NULL,'2026-09-27 17:27:21','2026-09-27 17:27:21'),(41,'operator.sd.fi.1','$2y$10$BjJeusT9eXaoavrB1yz02uYOUPWJ.x13sx1bg/lo0g31/Nypjtheq',NULL,'Fikri',NULL,17,1,0,0,NULL,NULL,'2026-09-27 17:31:16','2026-09-27 17:31:16');
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
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013  SQL SECURITY DEFINER */
/*!50001 VIEW `employee_turnovers` AS select `employee_turnover_logs`.`id` AS `id`,`employee_turnover_logs`.`employee_id` AS `employee_id`,`employee_turnover_logs`.`action_type` AS `action_type`,`employee_turnover_logs`.`from_unit_id` AS `from_unit_id`,`employee_turnover_logs`.`to_unit_id` AS `to_unit_id`,`employee_turnover_logs`.`effective_date` AS `effective_date`,`employee_turnover_logs`.`reason` AS `reason`,`employee_turnover_logs`.`created_by_user_id` AS `created_by_user_id`,`employee_turnover_logs`.`created_at` AS `created_at` from `employee_turnover_logs` */;
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

-- Dump completed on 2026-09-29  5:42:51

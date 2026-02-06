-- MySQL dump 10.13  Distrib 8.0.45, for Linux (aarch64)
--
-- Host: localhost    Database: pt_migas_finance
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2025_10_17_071954_create_permission_tables',1),(5,'2025_10_17_094743_create_tb_karyawan_table',1),(6,'2025_10_17_094743_create_tb_pangkalan_table',1),(7,'2025_10_17_094744_create_tb_sumber_kas',1),(8,'2025_10_17_094745_create_tb_asset_table',1),(9,'2025_10_17_094745_create_tb_kas_perusahaan_table',1),(10,'2025_10_17_094745_create_tb_tabung_table',1),(11,'2025_10_17_094745_create_tb_transaksi_operasional_table',1),(12,'2025_10_17_094746_create_tb_lembur_karyawan_table',1),(13,'2025_10_17_094746_create_tb_pendapatan_table',1),(14,'2025_10_17_094746_create_tb_potongan_table',1),(15,'2025_10_17_094755_create_tb_gaji_karyawan_table',1);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_permissions`
--

DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_permissions`
--

LOCK TABLES `model_has_permissions` WRITE;
/*!40000 ALTER TABLE `model_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `model_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `model_has_roles`
--

DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `model_has_roles`
--

LOCK TABLES `model_has_roles` WRITE;
/*!40000 ALTER TABLE `model_has_roles` DISABLE KEYS */;
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User','35974443-092e-4886-9097-d662a0026c3f'),(2,'App\\Models\\User','f91d7b07-08b8-42ab-b27d-14271838acec');
/*!40000 ALTER TABLE `model_has_roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `permissions`
--

DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `refresh_tokens`
--

DROP TABLE IF EXISTS `refresh_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `refresh_tokens` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '8e5d2c61-50d6-437c-aa64-1f025e0905f7',
  `user_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `is_revoked` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `refresh_tokens_token_unique` (`token`),
  KEY `refresh_tokens_user_id_expires_at_index` (`user_id`,`expires_at`),
  CONSTRAINT `refresh_tokens_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `refresh_tokens`
--

LOCK TABLES `refresh_tokens` WRITE;
/*!40000 ALTER TABLE `refresh_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `refresh_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_has_permissions`
--

DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_has_permissions`
--

LOCK TABLES `role_has_permissions` WRITE;
/*!40000 ALTER TABLE `role_has_permissions` DISABLE KEYS */;
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'super_admin','api','2026-01-25 15:59:12','2026-01-25 15:59:12'),(2,'admin','api','2026-01-25 15:59:12','2026-01-25 15:59:12');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_asset`
--

DROP TABLE IF EXISTS `tb_asset`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_asset` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `identitas` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` int NOT NULL DEFAULT '0',
  `catatan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_asset`
--

LOCK TABLES `tb_asset` WRITE;
/*!40000 ALTER TABLE `tb_asset` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_asset` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_gaji_karyawan`
--

DROP TABLE IF EXISTS `tb_gaji_karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_gaji_karyawan` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `karyawan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pendapatan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `potongan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pph21` decimal(15,2) NOT NULL DEFAULT '0.00',
  `periode` date NOT NULL,
  `subtotal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `gaji_bersih` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_gaji_karyawan_karyawan_id_periode_unique` (`karyawan_id`,`periode`),
  KEY `tb_gaji_karyawan_pendapatan_id_foreign` (`pendapatan_id`),
  KEY `tb_gaji_karyawan_potongan_id_foreign` (`potongan_id`),
  KEY `tb_gaji_karyawan_periode_index` (`periode`),
  KEY `tb_gaji_karyawan_karyawan_id_periode_index` (`karyawan_id`,`periode`),
  CONSTRAINT `tb_gaji_karyawan_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `tb_karyawan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tb_gaji_karyawan_pendapatan_id_foreign` FOREIGN KEY (`pendapatan_id`) REFERENCES `tb_pendapatan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `tb_gaji_karyawan_potongan_id_foreign` FOREIGN KEY (`potongan_id`) REFERENCES `tb_potongan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_gaji_karyawan`
--

LOCK TABLES `tb_gaji_karyawan` WRITE;
/*!40000 ALTER TABLE `tb_gaji_karyawan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_gaji_karyawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_karyawan`
--

DROP TABLE IF EXISTS `tb_karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_karyawan` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nik` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jabatan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `gaji_pokok` decimal(15,2) NOT NULL DEFAULT '0.00',
  `bpjs_kesehatan` decimal(5,2) NOT NULL DEFAULT '0.00',
  `bpjs_tenagakerja` decimal(5,2) NOT NULL DEFAULT '0.00',
  `tgl_masuk` date NOT NULL,
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `alamat` longtext COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_karyawan_nik_unique` (`nik`),
  KEY `tb_karyawan_nik_index` (`nik`),
  KEY `tb_karyawan_aktif_index` (`aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_karyawan`
--

LOCK TABLES `tb_karyawan` WRITE;
/*!40000 ALTER TABLE `tb_karyawan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_karyawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_kas_perusahaan`
--

DROP TABLE IF EXISTS `tb_kas_perusahaan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_kas_perusahaan` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` date NOT NULL,
  `sumber_kas_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe_transaksi` enum('DEBIT','KREDIT') COLLATE utf8mb4_unicode_ci NOT NULL,
  `jumlah` decimal(15,2) NOT NULL,
  `transaksi_operasional_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saldo_sebelum` decimal(15,2) NOT NULL,
  `saldo_sesudah` decimal(15,2) NOT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_kas_perusahaan_tanggal_index` (`tanggal`),
  KEY `tb_kas_perusahaan_sumber_kas_id_index` (`sumber_kas_id`),
  KEY `tb_kas_perusahaan_tipe_transaksi_index` (`tipe_transaksi`),
  KEY `tb_kas_perusahaan_tanggal_sumber_kas_id_index` (`tanggal`,`sumber_kas_id`),
  CONSTRAINT `tb_kas_perusahaan_sumber_kas_id_foreign` FOREIGN KEY (`sumber_kas_id`) REFERENCES `tb_sumber_kas` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_kas_perusahaan`
--

LOCK TABLES `tb_kas_perusahaan` WRITE;
/*!40000 ALTER TABLE `tb_kas_perusahaan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_kas_perusahaan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_lembur_karyawan`
--

DROP TABLE IF EXISTS `tb_lembur_karyawan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_lembur_karyawan` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` date NOT NULL,
  `hari` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `karyawan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jam_lembur` decimal(5,2) NOT NULL DEFAULT '0.00',
  `total_jam_lembur` decimal(5,2) NOT NULL DEFAULT '0.00',
  `upah_lembur_perjam` decimal(15,2) NOT NULL,
  `rupiah_lembur` decimal(15,2) NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `tb_lembur_karyawan_tanggal_index` (`tanggal`),
  KEY `tb_lembur_karyawan_karyawan_id_index` (`karyawan_id`),
  KEY `tb_lembur_karyawan_karyawan_id_tanggal_index` (`karyawan_id`,`tanggal`),
  CONSTRAINT `tb_lembur_karyawan_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `tb_karyawan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_lembur_karyawan`
--

LOCK TABLES `tb_lembur_karyawan` WRITE;
/*!40000 ALTER TABLE `tb_lembur_karyawan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_lembur_karyawan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_pangkalan`
--

DROP TABLE IF EXISTS `tb_pangkalan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pangkalan` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `regist_id` bigint NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `no_ktp` bigint NOT NULL,
  `alamat` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `harga_satuan` decimal(10,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_pangkalan_regist_id_unique` (`regist_id`),
  UNIQUE KEY `tb_pangkalan_no_ktp_unique` (`no_ktp`),
  KEY `tb_pangkalan_regist_id_index` (`regist_id`),
  KEY `tb_pangkalan_no_ktp_index` (`no_ktp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_pangkalan`
--

LOCK TABLES `tb_pangkalan` WRITE;
/*!40000 ALTER TABLE `tb_pangkalan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_pangkalan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_pendapatan`
--

DROP TABLE IF EXISTS `tb_pendapatan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_pendapatan` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `karyawan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tunjangan` decimal(15,2) NOT NULL DEFAULT '0.00',
  `periode` date NOT NULL,
  `total_pendapatan` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_pendapatan_karyawan_id_periode_unique` (`karyawan_id`,`periode`),
  KEY `tb_pendapatan_periode_index` (`periode`),
  KEY `tb_pendapatan_karyawan_id_periode_index` (`karyawan_id`,`periode`),
  CONSTRAINT `tb_pendapatan_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `tb_karyawan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_pendapatan`
--

LOCK TABLES `tb_pendapatan` WRITE;
/*!40000 ALTER TABLE `tb_pendapatan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_pendapatan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_potongan`
--

DROP TABLE IF EXISTS `tb_potongan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_potongan` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `karyawan_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periode` date NOT NULL,
  `rp_bpjs_kesehatan` decimal(15,2) NOT NULL,
  `rp_bpjs_tenagakerja` decimal(15,2) NOT NULL,
  `total_potongan` decimal(15,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_potongan_karyawan_id_periode_unique` (`karyawan_id`,`periode`),
  KEY `tb_potongan_periode_index` (`periode`),
  CONSTRAINT `tb_potongan_karyawan_id_foreign` FOREIGN KEY (`karyawan_id`) REFERENCES `tb_karyawan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_potongan`
--

LOCK TABLES `tb_potongan` WRITE;
/*!40000 ALTER TABLE `tb_potongan` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_potongan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_sumber_kas`
--

DROP TABLE IF EXISTS `tb_sumber_kas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_sumber_kas` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('BANK','CASH') COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_bank` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nomor_rekening` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `atas_nama` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `saldo_awal` decimal(15,2) NOT NULL DEFAULT '0.00',
  `saldo_terakhir` decimal(15,2) NOT NULL DEFAULT '0.00',
  `aktif` tinyint(1) NOT NULL DEFAULT '1',
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_bank_account` (`nama_bank`,`nomor_rekening`),
  KEY `tb_sumber_kas_tipe_index` (`tipe`),
  KEY `tb_sumber_kas_aktif_index` (`aktif`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_sumber_kas`
--

LOCK TABLES `tb_sumber_kas` WRITE;
/*!40000 ALTER TABLE `tb_sumber_kas` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_sumber_kas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_tabung`
--

DROP TABLE IF EXISTS `tb_tabung`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_tabung` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `berat` double NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_tabung`
--

LOCK TABLES `tb_tabung` WRITE;
/*!40000 ALTER TABLE `tb_tabung` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_tabung` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tb_transaksi_operasional`
--

DROP TABLE IF EXISTS `tb_transaksi_operasional`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tb_transaksi_operasional` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tanggal` date NOT NULL,
  `no_ref` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `jenis_transaksi` enum('PEMBELIAN_GAS','MAINTENANCE','PENJUALAN_GAS','LAINNYA') COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pangkalan_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tabung_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_pemasukan` tinyint(1) NOT NULL DEFAULT '0',
  `qty` int DEFAULT '0',
  `unit` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `harga_satuan` decimal(15,2) DEFAULT '0.00',
  `jumlah` decimal(15,2) NOT NULL,
  `kas_perusahaan_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tb_transaksi_operasional_no_ref_unique` (`no_ref`),
  KEY `tb_transaksi_operasional_pangkalan_id_foreign` (`pangkalan_id`),
  KEY `tb_transaksi_operasional_tabung_id_foreign` (`tabung_id`),
  KEY `tb_transaksi_operasional_kas_perusahaan_id_foreign` (`kas_perusahaan_id`),
  KEY `tb_transaksi_operasional_jenis_transaksi_index` (`jenis_transaksi`),
  KEY `tb_transaksi_operasional_is_pemasukan_index` (`is_pemasukan`),
  CONSTRAINT `tb_transaksi_operasional_kas_perusahaan_id_foreign` FOREIGN KEY (`kas_perusahaan_id`) REFERENCES `tb_kas_perusahaan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tb_transaksi_operasional_pangkalan_id_foreign` FOREIGN KEY (`pangkalan_id`) REFERENCES `tb_pangkalan` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tb_transaksi_operasional_tabung_id_foreign` FOREIGN KEY (`tabung_id`) REFERENCES `tb_tabung` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tb_transaksi_operasional`
--

LOCK TABLES `tb_transaksi_operasional` WRITE;
/*!40000 ALTER TABLE `tb_transaksi_operasional` DISABLE KEYS */;
/*!40000 ALTER TABLE `tb_transaksi_operasional` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('35974443-092e-4886-9097-d662a0026c3f','CEO Super Admin','ceo@ptmigas.com','2026-01-25 15:59:14','$2y$12$ph3bAX18fwgO2tz6CYTlfeNU0infqheBzMh0jstEwDe7sx2xhfAau',1,NULL,'2026-01-25 15:59:14','2026-01-25 15:59:14'),('f91d7b07-08b8-42ab-b27d-14271838acec','Staff Admin','admin@ptmigas.com','2026-01-25 15:59:14','$2y$12$GGWS7OqfHv.2BAmtSaj5aeAhGcfSuO308RvHR4XNlnJovZGa61zJS',1,NULL,'2026-01-25 15:59:14','2026-01-25 15:59:14');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-01-25 15:59:29

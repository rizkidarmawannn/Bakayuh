-- MySQL dump 10.13  Distrib 8.4.3, for Win64 (x86_64)
--
-- Host: localhost    Database: bakayuh
-- ------------------------------------------------------
-- Server version	8.4.3

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
-- Current Database: `bakayuh`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `bakayuh` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `bakayuh`;

--
-- Table structure for table `bukti_dukung_renaksi`
--

DROP TABLE IF EXISTS `bukti_dukung_renaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `bukti_dukung_renaksi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `realisasi_renaksi_id` bigint unsigned NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ukuran_file` int unsigned NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `bukti_dukung_renaksi_uploaded_by_foreign` (`uploaded_by`),
  KEY `bukti_dukung_renaksi_realisasi_renaksi_id_index` (`realisasi_renaksi_id`),
  CONSTRAINT `bukti_dukung_renaksi_realisasi_renaksi_id_foreign` FOREIGN KEY (`realisasi_renaksi_id`) REFERENCES `realisasi_renaksi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bukti_dukung_renaksi_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bukti_dukung_renaksi`
--

LOCK TABLES `bukti_dukung_renaksi` WRITE;
/*!40000 ALTER TABLE `bukti_dukung_renaksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `bukti_dukung_renaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
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
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
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
-- Table structure for table `evaluasi_sakip`
--

DROP TABLE IF EXISTS `evaluasi_sakip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `evaluasi_sakip` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_anggaran_id` bigint unsigned NOT NULL,
  `satker_id` bigint unsigned NOT NULL,
  `nilai_perencanaan` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nilai_pengukuran` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nilai_pelaporan` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nilai_evaluasi` decimal(5,2) NOT NULL DEFAULT '0.00',
  `nilai_total` decimal(5,2) NOT NULL DEFAULT '0.00',
  `predikat` enum('AA','A','BB','B','CC','C','D') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'D',
  `catatan` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_sakip_satker_tahun` (`tahun_anggaran_id`,`satker_id`),
  KEY `evaluasi_sakip_satker_id_foreign` (`satker_id`),
  KEY `evaluasi_sakip_created_by_foreign` (`created_by`),
  KEY `evaluasi_sakip_tahun_anggaran_id_predikat_index` (`tahun_anggaran_id`,`predikat`),
  CONSTRAINT `evaluasi_sakip_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluasi_sakip_satker_id_foreign` FOREIGN KEY (`satker_id`) REFERENCES `satuan_kerja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `evaluasi_sakip_tahun_anggaran_id_foreign` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `tahun_anggaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `evaluasi_sakip`
--

LOCK TABLES `evaluasi_sakip` WRITE;
/*!40000 ALTER TABLE `evaluasi_sakip` DISABLE KEYS */;
/*!40000 ALTER TABLE `evaluasi_sakip` ENABLE KEYS */;
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
  `connection` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`),
  KEY `failed_jobs_connection_queue_failed_at_index` (`connection`,`queue`,`failed_at`)
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
-- Table structure for table `indikator_kinerja`
--

DROP TABLE IF EXISTS `indikator_kinerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `indikator_kinerja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `satuan` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `polaritas` enum('positif','negatif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'positif',
  `level` enum('strategis','program','kegiatan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'kegiatan',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `indikator_kinerja_kode_unique` (`kode`),
  KEY `indikator_kinerja_level_polaritas_index` (`level`,`polaritas`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `indikator_kinerja`
--

LOCK TABLES `indikator_kinerja` WRITE;
/*!40000 ALTER TABLE `indikator_kinerja` DISABLE KEYS */;
INSERT INTO `indikator_kinerja` VALUES (1,'IKU-01','Persentase Satuan Kerja Berpredikat WBK/WBBM','%','positif','strategis','2026-10-04 19:05:59','2026-10-04 19:05:59'),(2,'IKU-02','Nilai Sistem Akuntabilitas Kinerja Instansi Pemerintah (SAKIP)','Poin','positif','strategis','2026-10-04 19:05:59','2026-10-04 19:05:59'),(3,'IKU-03','Indeks Kepuasan Masyarakat (IKM) Pelayanan Publik','Skala 1-4','positif','program','2026-10-04 19:05:59','2026-10-04 19:05:59'),(4,'IKU-04','Indeks Persepsi Korupsi (IPK) di Lingkungan Satker','Skala 1-4','positif','program','2026-10-04 19:05:59','2026-10-04 19:05:59'),(5,'IKU-05','Tingkat Kepatuhan Pelaporan LHKPN dan LHKASN Pegawai','%','positif','kegiatan','2026-10-04 19:05:59','2026-10-04 19:05:59'),(6,'IKU-06','Persentase Realisasi Anggaran Sesuai Target Triwulanan','%','positif','kegiatan','2026-10-04 19:05:59','2026-10-04 19:05:59'),(7,'IKU-07','Tingkat Penyelesaian Tindak Lanjut Temuan Pengawasan Internal (Itjen/BPK)','%','positif','kegiatan','2026-10-04 19:05:59','2026-10-04 19:05:59');
/*!40000 ALTER TABLE `indikator_kinerja` ENABLE KEYS */;
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
  `attempts` smallint unsigned NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_10_05_025731_create_personal_access_tokens_table',1),(5,'2026_10_05_030001_create_satuan_kerja_table',1),(6,'2026_10_05_030002_create_tahun_anggaran_table',1),(7,'2026_10_05_030003_add_role_and_satker_to_users_table',1),(8,'2026_10_05_030004_create_indikator_kinerja_table',1),(9,'2026_10_05_030005_create_target_iku_table',1),(10,'2026_10_05_030006_create_realisasi_iku_table',1),(11,'2026_10_05_030007_create_rencana_aksi_table',1),(12,'2026_10_05_030008_create_realisasi_renaksi_table',1),(13,'2026_10_05_030009_create_bukti_dukung_renaksi_table',1),(14,'2026_10_05_030010_create_evaluasi_sakip_table',1),(15,'2026_10_05_030011_create_rb_area_table',1),(16,'2026_10_05_030012_create_rb_indikator_table',1),(17,'2026_10_05_030013_create_rb_sub_indikator_table',1),(18,'2026_10_05_030014_create_rb_target_periode_table',1),(19,'2026_10_05_030015_create_rb_dokumen_daduk_table',1),(20,'2026_10_05_030016_create_rb_catatan_verifikasi_table',1),(21,'2026_10_14_023229_add_username_to_users_table',2);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
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
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (1,'App\\Models\\User',1,'bakayuh_auth_token','276828785b18c907b2f735456d395a662de00b180cdbe024285fcb479d57562c','[\"*\"]','2026-10-04 20:07:58',NULL,'2026-10-04 20:06:55','2026-10-04 20:07:58'),(4,'App\\Models\\User',1,'bakayuh_auth_token','26e1b118890c0eb796904306c37a0f41956ab809ca45411c37974928d49a35b2','[\"*\"]','2026-10-04 22:47:40',NULL,'2026-10-04 21:42:21','2026-10-04 22:47:40'),(5,'App\\Models\\User',1,'bakayuh_auth_token','0a76255abeaeefd9b844875d96e365a3223862ad2da48508114ad621c6f9e1b1','[\"*\"]','2026-10-04 22:47:15',NULL,'2026-10-04 22:46:34','2026-10-04 22:47:15'),(6,'App\\Models\\User',1,'bakayuh_auth_token','f7d87343c124b65da18caf9c8e81bc26b653313b7d290785a8370e8147991e2b','[\"*\"]','2026-10-04 22:50:11',NULL,'2026-10-04 22:48:22','2026-10-04 22:50:11'),(8,'App\\Models\\User',4,'bakayuh_auth_token','98b191eb5b62dda1b5985d810fed9888287eba7e9c2eab60829cc5c20b797323','[\"*\"]','2026-10-06 16:06:13',NULL,'2026-10-06 16:03:25','2026-10-06 16:06:13'),(10,'App\\Models\\User',1,'bakayuh_auth_token','60408cd37bb955835ce02adecb4c9c29f3d83198594775431f683238e46652cf','[\"*\"]','2026-10-06 17:11:24',NULL,'2026-10-06 17:09:41','2026-10-06 17:11:24'),(13,'App\\Models\\User',2,'bakayuh_auth_token','46e7d928b16fb20faf0d90b02ed854342b4297ed80b5e950bff643309bf0c6f4','[\"*\"]','2026-10-06 23:39:01',NULL,'2026-10-06 23:39:01','2026-10-06 23:39:01'),(14,'App\\Models\\User',5,'bakayuh_auth_token','4f920ddfcc0809d04dabe1ee862c4dfe3b9139964a2f7181deab95a2c6189ac6','[\"*\"]',NULL,NULL,'2026-10-13 19:08:09','2026-10-13 19:08:09'),(15,'App\\Models\\User',6,'bakayuh_auth_token','ddaddd940952f8e6bc1a6b0dd0e4cdf6a499618b0de611837bb6b66d72d09894','[\"*\"]',NULL,NULL,'2026-10-13 19:08:16','2026-10-13 19:08:16'),(16,'App\\Models\\User',7,'bakayuh_auth_token','10f5667a24c59555d61c2bf90147a755796feaa4330920126990485575e263a8','[\"*\"]',NULL,NULL,'2026-10-13 19:08:31','2026-10-13 19:08:31');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rb_area`
--

DROP TABLE IF EXISTS `rb_area`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rb_area` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kategori` enum('lke_wbk_wbbm','rkt_general','rkt_tematik','rkt_meso') COLLATE utf8mb4_unicode_ci NOT NULL,
  `komponen` enum('pengungkit','hasil','none') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_area` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `rb_area_kode_unique` (`kode`),
  KEY `rb_area_kategori_komponen_urutan_index` (`kategori`,`komponen`,`urutan`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rb_area`
--

LOCK TABLES `rb_area` WRITE;
/*!40000 ALTER TABLE `rb_area` DISABLE KEYS */;
INSERT INTO `rb_area` VALUES (1,'lke_wbk_wbbm','pengungkit','LKE-A1','I. Manajemen Perubahan',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(2,'lke_wbk_wbbm','pengungkit','LKE-A2','II. Penataan Tatalaksana',2,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(3,'lke_wbk_wbbm','pengungkit','LKE-A3','III. Penataan Sistem Manajemen SDM',3,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(4,'lke_wbk_wbbm','pengungkit','LKE-A4','IV. Penguatan Akuntabilitas Kinerja',4,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(5,'lke_wbk_wbbm','pengungkit','LKE-A5','V. Penguatan Pengawasan',5,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(6,'lke_wbk_wbbm','pengungkit','LKE-A6','VI. Peningkatan Kualitas Pelayanan Publik',6,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(7,'lke_wbk_wbbm','pengungkit','LKE-DOK-TAMBAHAN','VII. SPTJM, Video Profil, & Bahan Paparan',7,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(8,'lke_wbk_wbbm','hasil','LKE-HASIL-1','Hasil I: Birokrasi yang Bersih dan Akuntabel',8,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(9,'lke_wbk_wbbm','hasil','LKE-HASIL-2','Hasil II: Pelayanan Publik yang Prima',9,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(10,'rkt_general','none','RKT-GEN','RKT Reformasi Birokrasi General',10,'2026-10-04 19:05:59','2026-10-04 19:05:59');
/*!40000 ALTER TABLE `rb_area` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rb_catatan_verifikasi`
--

DROP TABLE IF EXISTS `rb_catatan_verifikasi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rb_catatan_verifikasi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rb_target_periode_id` bigint unsigned NOT NULL,
  `sender_id` bigint unsigned NOT NULL,
  `pesan` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rb_catatan_verifikasi_sender_id_foreign` (`sender_id`),
  KEY `rb_catatan_verifikasi_rb_target_periode_id_index` (`rb_target_periode_id`),
  CONSTRAINT `rb_catatan_verifikasi_rb_target_periode_id_foreign` FOREIGN KEY (`rb_target_periode_id`) REFERENCES `rb_target_periode` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rb_catatan_verifikasi_sender_id_foreign` FOREIGN KEY (`sender_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rb_catatan_verifikasi`
--

LOCK TABLES `rb_catatan_verifikasi` WRITE;
/*!40000 ALTER TABLE `rb_catatan_verifikasi` DISABLE KEYS */;
/*!40000 ALTER TABLE `rb_catatan_verifikasi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rb_dokumen_daduk`
--

DROP TABLE IF EXISTS `rb_dokumen_daduk`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rb_dokumen_daduk` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rb_target_periode_id` bigint unsigned NOT NULL,
  `nama_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `path_file` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ukuran_file` bigint unsigned NOT NULL,
  `mime_type` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `uploaded_by` bigint unsigned NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `rb_dokumen_daduk_uploaded_by_foreign` (`uploaded_by`),
  KEY `rb_dokumen_daduk_rb_target_periode_id_index` (`rb_target_periode_id`),
  CONSTRAINT `rb_dokumen_daduk_rb_target_periode_id_foreign` FOREIGN KEY (`rb_target_periode_id`) REFERENCES `rb_target_periode` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rb_dokumen_daduk_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rb_dokumen_daduk`
--

LOCK TABLES `rb_dokumen_daduk` WRITE;
/*!40000 ALTER TABLE `rb_dokumen_daduk` DISABLE KEYS */;
INSERT INTO `rb_dokumen_daduk` VALUES (1,2,'ERB.pdf','dokumen_daduk/2/1791175439_erb.pdf',518673,'application/pdf',4,'2026-10-05 04:44:00');
/*!40000 ALTER TABLE `rb_dokumen_daduk` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rb_indikator`
--

DROP TABLE IF EXISTS `rb_indikator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rb_indikator` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rb_area_id` bigint unsigned NOT NULL,
  `aspek` enum('reform','pemenuhan','hasil','none') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama_indikator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `keterangan_juknis` text COLLATE utf8mb4_unicode_ci,
  `urutan` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rb_indikator_rb_area_id_aspek_urutan_index` (`rb_area_id`,`aspek`,`urutan`),
  CONSTRAINT `rb_indikator_rb_area_id_foreign` FOREIGN KEY (`rb_area_id`) REFERENCES `rb_area` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rb_indikator`
--

LOCK TABLES `rb_indikator` WRITE;
/*!40000 ALTER TABLE `rb_indikator` DISABLE KEYS */;
INSERT INTO `rb_indikator` VALUES (1,1,'pemenuhan','A1-IND-01','Penyusunan Tim Kerja Pembangunan ZI','Pembentukan Tim Kerja melalui seleksi terbuka, SK Tim Kerja, dan pembagian tugas yang jelas.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(2,1,'pemenuhan','A1-IND-02','Rencana Pembangunan Zona Integritas','Dokumen rencana kerja pembangunan ZI yang memuat target prioritas dan target waktu.',2,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(3,1,'reform','A1-IND-03','Pola Pikir dan Budaya Kerja (Role Model & Agen Perubahan)','Keteladanan pimpinan sebagai role model dan penetapan Agen Perubahan yang aktif membawa inovasi.',3,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(4,2,'pemenuhan','A2-IND-01','Standar Operasional Prosedur (SOP) Kegiatan Utama','Penerapan, evaluasi, dan pemutakhiran SOP kegiatan utama organisasi.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(5,2,'reform','A2-IND-02','Sistem Pemerintahan Berbasis Elektronik (SPBE / E-Gov)','Pemanfaatan sistem digital terintegrasi dalam manajemen persuratan, kepegawaian, dan pelayanan publik.',2,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(6,3,'reform','A3-IND-01','Pengembangan Pegawai Berbasis Kompetensi & Pola Mutasi Internal','Pelaksanaan rotasi berkala dan diklat/pelatihan pengembangan kapasitas aparatur.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(7,3,'pemenuhan','A3-IND-02','Penegakan Disiplin dan Kode Etik Pegawai','Penerapan aturan disiplin, rekapitulasi absensi elektronik, dan penjatuhan sanksi jika ada pelanggaran.',2,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(8,4,'pemenuhan','A4-IND-01','Keterlibatan Pimpinan dalam Akuntabilitas Kinerja','Keterlibatan langsung Kepala Satker dalam penyusunan Renstra, IKU, dan Perjanjian Kinerja.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(9,5,'pemenuhan','A5-IND-01','Pengendalian Gratifikasi dan Unit Pengendalian Gratifikasi (UPG)','Public campaign tolak gratifikasi, pembentukan UPG satker, dan pelaporan gratifikasi.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(10,5,'reform','A5-IND-02','Penanganan Pengaduan Masyarakat (WBS & SP4N-LAPOR!)','Pemanfaatan aplikasi SP4N-LAPOR!, kotak pengaduan, dan tindak lanjut aduan.',2,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(11,6,'pemenuhan','A6-IND-01','Standar Pelayanan Publik dan Maklumat Pelayanan','Penyusunan standar pelayanan yang melibatkan pengguna dan maklumat pelayanan terpampang jelas.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(12,6,'reform','A6-IND-02','Inovasi Pelayanan Publik dan Fasilitas Kelompok Rentan','Penyediaan inovasi unggulan dan sarana ramah disabilitas, lansia, ibu menyusui.',2,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(13,7,'pemenuhan','DOK-IND-01','Kelengkapan Dokumen Wajib Usulan WBK/WBBM','Surat Pernyataan Tanggung Jawab Mutlak (SPTJM), tautan video profil, dan presentasi paparan pimpinan.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(14,8,'hasil','H1-IND-01','Survei Persepsi Korupsi (IPK) dan Capaian SAKIP','Nilai survei IPK mandiri/eksternal dan predikat evaluasi SAKIP satker.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(15,9,'hasil','H2-IND-01','Survei Kepuasan Masyarakat (IKM) & Indeks Pelayanan Publik','Hasil survei IKM dan pengakuan/penghargaan eksternal pelayanan publik.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(16,10,'none','RKT-IND-01','Peningkatan Indeks SPBE & Digitalisasi Layanan','Pelaksanaan arsitektur SPBE dan interoperabilitas data kementerian.',1,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(17,10,'none','RKT-IND-02','Tingkat Maturitas SPIP Terintegrasi','Penyusunan risk register (daftar risiko) dan monitoring pengendalian intern.',2,'2026-10-04 19:05:59','2026-10-04 19:05:59');
/*!40000 ALTER TABLE `rb_indikator` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rb_sub_indikator`
--

DROP TABLE IF EXISTS `rb_sub_indikator`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rb_sub_indikator` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rb_indikator_id` bigint unsigned NOT NULL,
  `nomor_poin` smallint unsigned NOT NULL DEFAULT '1',
  `judul_poin` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `checklist_daduk` text COLLATE utf8mb4_unicode_ci,
  `catatan_tpi` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rb_sub_indikator_rb_indikator_id_nomor_poin_index` (`rb_indikator_id`,`nomor_poin`),
  CONSTRAINT `rb_sub_indikator_rb_indikator_id_foreign` FOREIGN KEY (`rb_indikator_id`) REFERENCES `rb_indikator` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rb_sub_indikator`
--

LOCK TABLES `rb_sub_indikator` WRITE;
/*!40000 ALTER TABLE `rb_sub_indikator` DISABLE KEYS */;
INSERT INTO `rb_sub_indikator` VALUES (1,1,1,'Pembentukan Tim Kerja Pembangunan ZI secara transparan dan akuntabel','a. Undangan rapat pembentukan Tim Kerja ZI\nb. Notula rapat & daftar hadir\nc. SK Penetapan Tim Kerja ZI ditandatangani Kepala Satker\nd. Foto/dokumentasi proses seleksi dan pembentukan','Pastikan SK memuat seluruh anggota 6 pokja dan tidak ada duplikasi fungsi.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(2,2,1,'Penyusunan Dokumen Rencana Kerja Pembangunan ZI','a. Dokumen Rencana Kerja ZI tahun berjalan\nb. Matriks kegiatan 6 pokja pengungkit\nc. Notula rapat pembahasan rencana kerja','Rencana kerja harus selaras dengan target kinerja tahunan satker.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(3,3,1,'Pimpinan sebagai Role Model dalam Pembangunan ZI','a. Dokumentasi pimpinan memimpin apel/briefing integritas\nb. Laporan keteladanan jam kerja pimpinan (absensi)\nc. Arahan tertulis pimpinan terkait pencegahan gratifikasi & pungli','Tampilkan konsistensi keteladanan pimpinan sepanjang periode pelaporan.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(4,3,2,'Penetapan dan Rencana Aksi Agen Perubahan (Agent of Change)','a. SK Penetapan Agen Perubahan\nb. Rencana aksi inovasi Agen Perubahan\nc. Laporan capaian hasil inovasi agen perubahan','Inovasi agen perubahan harus memiliki dampak nyata pada efisiensi layanan atau integritas.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(5,4,1,'Penyusunan dan Evaluasi SOP Kegiatan Utama','a. Dokumen SOP kegiatan utama yang telah disahkan\nb. Laporan monitoring & evaluasi penerapan SOP berkala\nc. Berita acara revisi SOP jika terdapat perubahan proses bisnis','Pastikan SOP memuat alur waktu, biaya (Rp 0 jika bebas biaya), dan penanggung jawab.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(6,5,1,'Pemanfaatan Sistem Informasi Manajemen Terpadu','a. Bukti pemanfaatan aplikasi persuratan dinas elektronik (SISUMAKER/SRIKANDI)\nb. Bukti integrasi sistem informasi layanan publik\nc. Screenshot dashboard pemantauan pelayanan secara real-time','Lengkapi dengan user log atau statistik utilisasi aplikasi.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(7,6,1,'Pelaksanaan Rotasi / Mutasi Internal Pegawai','a. Pola mutasi/rotasi internal berkala\nb. SK mutasi/rotasi staf antar unit/pos\nc. Notula rapat Tim Penilai Kinerja (TPK)','Rotasi ditujukan untuk penyegaran dan pencegahan kejenuhan serta potensi penyimpangan.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(8,7,1,'Monitoring dan Rekapitulasi Absensi serta Penegakan Disiplin','a. Rekap absensi finger/aplikasi SIMPEG bulanan\nb. Tindak lanjut keterlambatan / ketidakhadiran pegawai\nc. Laporan majelis kode etik (jika ada)','Persentase kehadiran harus terdokumentasi rapi setiap bulan.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(9,8,1,'Keterlibatan Pimpinan dalam Penetapan dan Pemantauan Kinerja','a. Dokumen Perjanjian Kinerja (PK) berjenjang ditandatangani pimpinan\nb. Notula rapat monitoring capaian kinerja triwulanan dipimpin Kakanwil/Ka.UPT\nc. Laporan Kinerja Instansi Pemerintah (LKjIP) tahunan','Sertakan foto pimpinan memimpin rapat evaluasi capaian kinerja triwulanan.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(10,9,1,'Pembentukan UPG dan Kampanye Publik Tolak Gratifikasi','a. SK Penetapan Unit Pengendalian Gratifikasi (UPG) Satker\nb. Banner/spanduk/banner tolak gratifikasi dan pungli di area pelayanan publik\nc. Laporan rekapitulasi penerimaan/penolakan gratifikasi bulanan','Wajib ada kanal pelaporan gratifikasi yang mudah diakses pengunjung.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(11,10,1,'Penerapan Sistem Pengaduan Masyarakat Terpadu dan WBS','a. SK Pejabat Pengelola Pengaduan\nb. Tanda terima atau rekap penanganan aduan SP4N-LAPOR!\nc. Dokumentasi sarana pengaduan (kotak aduan, WhatsApp info, loket)','Setiap aduan wajib ditindaklanjuti maksimal dalam batas SOP (contoh 3-5 hari kerja).','2026-10-04 19:05:59','2026-10-04 19:05:59'),(12,11,1,'Penyusunan dan Publikasi Standar Pelayanan & Maklumat Pelayanan','a. Dokumen Standar Pelayanan Publik (SPP) seluruh jenis layanan\nb. Foto maklumat pelayanan terpampang di ruang tunggu utama\nc. Laporan Forum Konsultasi Publik (FKP) bersama stakeholder','FKP wajib melibatkan perwakilan masyarakat, akademisi, atau media.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(13,12,1,'Penyediaan Fasilitas Layanan Ramah HAM & Kelompok Rentan','a. Foto jalur pemandu (guiding block), ramp kursi roda, toilet disabilitas\nb. Ruang laktasi / pojok ASI dan area bermain anak\nc. Buku pedoman atau SOP perlakuan khusus kelompok rentan','Fasilitas kelompok rentan harus berfungsi aktif dan bersih.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(14,13,1,'SPTJM, Video Profil Satker, dan Bahan Paparan Pimpinan','a. SPTJM bermaterai ditandatangani Kepala Satuan Kerja\nb. Tautan video profil pembangunan ZI (YouTube / Cloud Drive)\nc. Bahan tayang paparan pimpinan (format PPTX/PDF maksimal 20 slide)','Pastikan video profil menampilkan testimoni penerima layanan nyata.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(15,14,1,'Laporan Hasil Survei IPK dan Nilai Evaluasi SAKIP','a. Laporan hasil survei IPK triwulanan (minimal predikat Baik)\nb. LHE SAKIP dari Inspektorat Jenderal\nc. Rencana aksi tindak lanjut rekomendasi hasil survei','Minimal responden survei mencukupi batas metodologi.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(16,15,1,'Laporan Hasil Survei Kepuasan Masyarakat (IKM)','a. Laporan berkala survei kepuasan masyarakat (aplikasi 3AS/Balitbangham)\nb. Publikasi nilai IKM di papan pengumuman/medsos\nc. Dokumentasi penghargaan eksternal (jika ada)','Nilai IKM minimal berkategori Sangat Baik atau Memuaskan.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(17,16,1,'Pemenuhan Standar Keamanan Informasi dan Pemanfaatan SPBE Satker','a. Bukti audit keamanan SPBE / CSIRT\nb. Laporan integrasi data satker ke portal Kanwil','Pastikan domain resmi go.id dan sertifikat SSL aktif.','2026-10-04 19:05:59','2026-10-04 19:05:59'),(18,17,1,'Dokumen Risk Register dan Rencana Pengendalian Risiko','a. Dokumen register risiko seluruh bidang operasional\nb. Laporan pemantauan pengendalian risiko triwulanan','Lengkapi dengan mitigasi risiko operasional utama.','2026-10-04 19:05:59','2026-10-04 19:05:59');
/*!40000 ALTER TABLE `rb_sub_indikator` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rb_target_periode`
--

DROP TABLE IF EXISTS `rb_target_periode`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rb_target_periode` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rb_sub_indikator_id` bigint unsigned NOT NULL,
  `tahun_anggaran_id` bigint unsigned NOT NULL,
  `satker_id` bigint unsigned NOT NULL,
  `periode` enum('B03','B06','B09','B12') COLLATE utf8mb4_unicode_ci NOT NULL,
  `batas_waktu_upload` datetime DEFAULT NULL,
  `status_verifikasi` enum('belum_upload','belum_verif','lengkap','perlu_perbaikan','tercapai') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_upload',
  `penjelasan_zi` text COLLATE utf8mb4_unicode_ci,
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rb_target_periode` (`rb_sub_indikator_id`,`tahun_anggaran_id`,`satker_id`,`periode`),
  KEY `rb_target_periode_tahun_anggaran_id_foreign` (`tahun_anggaran_id`),
  KEY `rb_target_periode_verified_by_foreign` (`verified_by`),
  KEY `idx_rb_target_filter` (`satker_id`,`tahun_anggaran_id`,`periode`,`status_verifikasi`),
  CONSTRAINT `rb_target_periode_rb_sub_indikator_id_foreign` FOREIGN KEY (`rb_sub_indikator_id`) REFERENCES `rb_sub_indikator` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rb_target_periode_satker_id_foreign` FOREIGN KEY (`satker_id`) REFERENCES `satuan_kerja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rb_target_periode_tahun_anggaran_id_foreign` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `tahun_anggaran` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rb_target_periode_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rb_target_periode`
--

LOCK TABLES `rb_target_periode` WRITE;
/*!40000 ALTER TABLE `rb_target_periode` DISABLE KEYS */;
INSERT INTO `rb_target_periode` VALUES (1,17,2,1,'B03',NULL,'belum_upload',NULL,NULL,NULL,'2026-10-04 20:31:56','2026-10-04 20:31:56'),(2,1,2,1,'B03',NULL,'belum_upload',NULL,NULL,NULL,'2026-10-04 20:43:49','2026-10-04 20:43:49'),(3,15,2,1,'B03',NULL,'belum_upload',NULL,NULL,NULL,'2026-10-04 20:44:26','2026-10-04 20:44:26'),(4,6,2,1,'B03',NULL,'belum_upload',NULL,NULL,NULL,'2026-10-04 21:41:40','2026-10-04 21:41:40'),(5,4,2,1,'B03',NULL,'belum_upload',NULL,NULL,NULL,'2026-10-06 23:32:44','2026-10-06 23:32:44');
/*!40000 ALTER TABLE `rb_target_periode` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realisasi_iku`
--

DROP TABLE IF EXISTS `realisasi_iku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realisasi_iku` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `target_iku_id` bigint unsigned NOT NULL,
  `nilai_realisasi` decimal(15,4) NOT NULL,
  `persentase_capaian` decimal(8,4) DEFAULT NULL,
  `keterangan` text COLLATE utf8mb4_unicode_ci,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realisasi_iku_target_iku_id_unique` (`target_iku_id`),
  KEY `realisasi_iku_created_by_foreign` (`created_by`),
  KEY `realisasi_iku_persentase_capaian_index` (`persentase_capaian`),
  CONSTRAINT `realisasi_iku_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `realisasi_iku_target_iku_id_foreign` FOREIGN KEY (`target_iku_id`) REFERENCES `target_iku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realisasi_iku`
--

LOCK TABLES `realisasi_iku` WRITE;
/*!40000 ALTER TABLE `realisasi_iku` DISABLE KEYS */;
/*!40000 ALTER TABLE `realisasi_iku` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `realisasi_renaksi`
--

DROP TABLE IF EXISTS `realisasi_renaksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `realisasi_renaksi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rencana_aksi_id` bigint unsigned NOT NULL,
  `deskripsi_realisasi` text COLLATE utf8mb4_unicode_ci,
  `persentase_selesai` tinyint unsigned NOT NULL DEFAULT '0',
  `status` enum('belum_lapor','menunggu_verifikasi','terverifikasi','perlu_perbaikan') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'belum_lapor',
  `catatan_verifikasi` text COLLATE utf8mb4_unicode_ci,
  `verified_by` bigint unsigned DEFAULT NULL,
  `verified_at` timestamp NULL DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `realisasi_renaksi_rencana_aksi_id_unique` (`rencana_aksi_id`),
  KEY `realisasi_renaksi_verified_by_foreign` (`verified_by`),
  KEY `realisasi_renaksi_created_by_foreign` (`created_by`),
  KEY `realisasi_renaksi_status_persentase_selesai_index` (`status`,`persentase_selesai`),
  CONSTRAINT `realisasi_renaksi_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `realisasi_renaksi_rencana_aksi_id_foreign` FOREIGN KEY (`rencana_aksi_id`) REFERENCES `rencana_aksi` (`id`) ON DELETE CASCADE,
  CONSTRAINT `realisasi_renaksi_verified_by_foreign` FOREIGN KEY (`verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `realisasi_renaksi`
--

LOCK TABLES `realisasi_renaksi` WRITE;
/*!40000 ALTER TABLE `realisasi_renaksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `realisasi_renaksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rencana_aksi`
--

DROP TABLE IF EXISTS `rencana_aksi`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rencana_aksi` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_anggaran_id` bigint unsigned NOT NULL,
  `satker_id` bigint unsigned NOT NULL,
  `indikator_id` bigint unsigned NOT NULL,
  `nama_aksi` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `triwulan` enum('TW1','TW2','TW3','TW4') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_output` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `rencana_aksi_tahun_anggaran_id_foreign` (`tahun_anggaran_id`),
  KEY `rencana_aksi_indikator_id_foreign` (`indikator_id`),
  KEY `rencana_aksi_satker_id_tahun_anggaran_id_triwulan_index` (`satker_id`,`tahun_anggaran_id`,`triwulan`),
  CONSTRAINT `rencana_aksi_indikator_id_foreign` FOREIGN KEY (`indikator_id`) REFERENCES `indikator_kinerja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rencana_aksi_satker_id_foreign` FOREIGN KEY (`satker_id`) REFERENCES `satuan_kerja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `rencana_aksi_tahun_anggaran_id_foreign` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `tahun_anggaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rencana_aksi`
--

LOCK TABLES `rencana_aksi` WRITE;
/*!40000 ALTER TABLE `rencana_aksi` DISABLE KEYS */;
/*!40000 ALTER TABLE `rencana_aksi` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `satuan_kerja`
--

DROP TABLE IF EXISTS `satuan_kerja`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `satuan_kerja` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `kode` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nama` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipe` enum('kanwil','upt','satker') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'upt',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `satuan_kerja_kode_unique` (`kode`),
  KEY `satuan_kerja_tipe_is_active_index` (`tipe`,`is_active`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `satuan_kerja`
--

LOCK TABLES `satuan_kerja` WRITE;
/*!40000 ALTER TABLE `satuan_kerja` DISABLE KEYS */;
INSERT INTO `satuan_kerja` VALUES (1,'KANWIL-KALSEN','Kantor Wilayah Kemenkum Kalimantan Selatan','kanwil',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(2,'LP-BJM','Lapas Kelas IIA Banjarmasin','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(3,'LP-NKR-BJM','Lapas Narkotika Kelas IIA Karang Intan','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(4,'LP-KOTABARU','Lapas Kelas IIA Kotabaru','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(5,'LP-AMUNTAI','Lapas Kelas IIB Amuntai','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(6,'LP-TANJUNG','Lapas Kelas IIB Tanjung','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(7,'RT-BARABAI','Rutan Kelas IIB Barabai','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(8,'RT-KANDANGAN','Rutan Kelas IIB Kandangan','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(9,'RT-RANTAU','Rutan Kelas IIB Rantau','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(10,'RT-MARABAHAN','Rutan Kelas IIB Marabahan','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(11,'RT-PELAIHARI','Rutan Kelas IIB Pelaihari','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(12,'KANIM-BJM','Kantor Imigrasi Kelas I TPI Banjarmasin','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(13,'KANIM-BATULICIN','Kantor Imigrasi Kelas II TPI Batulicin','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(14,'BAPAS-BJM','Bapas Kelas I Banjarmasin','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(15,'BAPAS-AMUNTAI','Bapas Kelas II Amuntai','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(16,'RUPBASAN-BJM','Rupbasan Kelas I Banjarmasin','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(17,'BHP-BJM','Balai Harta Peninggalan Banjarmasin','upt',1,'2026-10-04 19:05:58','2026-10-04 19:05:58');
/*!40000 ALTER TABLE `satuan_kerja` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
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
INSERT INTO `sessions` VALUES ('CgoC3VdL8mo494m3ZdcWWt6fJBZ6UnXv80CYbOBX',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36 Edg/154.0.0.0','eyJfdG9rZW4iOiJWdnFkMklGdFExdnVvQllTNVZ0V1BDaGdMZlNkYWhsOHBMR29meUdNIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1791173335),('F3LheDeaXqr3lDXep3K4dAtxLhwdC6ZCJA57EJD0',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.141.0 Chrome/150.0.7871.250 Electron/43.7.7 Safari/537.36','eyJfdG9rZW4iOiJjZVh4QVVER2p4TW5SbzM0ZEhhT0VQU2xxYWVaWE5Ib1NpSEN2bnFGIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1791939233),('TEa70EwDQgbiJttvT18x9mRqY8cG2JA0B2jIVzyt',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Code/1.139.1 Chrome/150.0.7871.250 Electron/43.6.0 Safari/537.36','eyJfdG9rZW4iOiJkUFp0Sk1lNWVYZW95ZHQyQmxsRk8wS1ZIN0NEdTkzOHhxendIU1BJIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1791173041),('WiKZjlPRYoS7HAAmFmmVRzpTFekGgjUfKqA4kkqX',NULL,'127.0.0.1','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36','eyJfdG9rZW4iOiJOQzdoWWpmanZBeHQyQnBLTWlYcUNQVkRWQUgyTUZXVk1XbXdQU05jIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19',1791939242),('ZAe6TqGkbz2xHhD1smYAp4d5ZEtWDiGCz9D574Jo',NULL,'127.0.0.1','curl/8.21.0','eyJfdG9rZW4iOiJ4N0dxMkpUZzlNeVkwckpHb1lod1JtY01OMldBM2J3UlFsZG13QXg4IiwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119fQ==',1791173587);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tahun_anggaran`
--

DROP TABLE IF EXISTS `tahun_anggaran`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tahun_anggaran` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun` year NOT NULL,
  `is_aktif` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `tahun_anggaran_tahun_unique` (`tahun`),
  KEY `tahun_anggaran_is_aktif_index` (`is_aktif`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tahun_anggaran`
--

LOCK TABLES `tahun_anggaran` WRITE;
/*!40000 ALTER TABLE `tahun_anggaran` DISABLE KEYS */;
INSERT INTO `tahun_anggaran` VALUES (1,2025,0,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(2,2026,1,'2026-10-04 19:05:58','2026-10-04 19:05:58');
/*!40000 ALTER TABLE `tahun_anggaran` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `target_iku`
--

DROP TABLE IF EXISTS `target_iku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `target_iku` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tahun_anggaran_id` bigint unsigned NOT NULL,
  `satker_id` bigint unsigned NOT NULL,
  `indikator_id` bigint unsigned NOT NULL,
  `nilai_target` decimal(15,4) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_target_iku` (`tahun_anggaran_id`,`satker_id`,`indikator_id`),
  KEY `target_iku_satker_id_foreign` (`satker_id`),
  KEY `target_iku_indikator_id_foreign` (`indikator_id`),
  KEY `target_iku_tahun_anggaran_id_satker_id_index` (`tahun_anggaran_id`,`satker_id`),
  CONSTRAINT `target_iku_indikator_id_foreign` FOREIGN KEY (`indikator_id`) REFERENCES `indikator_kinerja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `target_iku_satker_id_foreign` FOREIGN KEY (`satker_id`) REFERENCES `satuan_kerja` (`id`) ON DELETE CASCADE,
  CONSTRAINT `target_iku_tahun_anggaran_id_foreign` FOREIGN KEY (`tahun_anggaran_id`) REFERENCES `tahun_anggaran` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `target_iku`
--

LOCK TABLES `target_iku` WRITE;
/*!40000 ALTER TABLE `target_iku` DISABLE KEYS */;
/*!40000 ALTER TABLE `target_iku` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('super_admin','admin_kanwil','operator_satker','viewer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'operator_satker',
  `satker_id` bigint unsigned DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  UNIQUE KEY `users_username_unique` (`username`),
  KEY `users_satker_id_foreign` (`satker_id`),
  KEY `users_role_is_active_index` (`role`,`is_active`),
  CONSTRAINT `users_satker_id_foreign` FOREIGN KEY (`satker_id`) REFERENCES `satuan_kerja` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Super Administrator',NULL,'superadmin@kemenkum.go.id','super_admin',1,1,NULL,'$2y$12$ZfbtcmjtLFyOgWlQLtHLyuNP2ccIthfsiu2VJvN.9..Nz3rxycqku',NULL,'2026-10-04 19:05:58','2026-10-04 19:05:58'),(2,'Tim Pengelola Kinerja & Verifikator RB',NULL,'verifikator@kemenkum.go.id','admin_kanwil',1,1,NULL,'$2y$12$jR9DCeZ5I9itnTTs0Y/ey.VJsQrtfMme8xOkZmhydJzkZgagSPw3m',NULL,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(3,'Operator Pokja Lapas Banjarmasin',NULL,'operator.lpbjm@kemenkum.go.id','operator_satker',2,1,NULL,'$2y$12$mJbh3F6.eh1qS48gCbsbj.3/04I8Ue5s64C3AiJ6t1p40skZ9iE2i',NULL,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(4,'Kepala Kantor Wilayah',NULL,'pimpinan@kemenkum.go.id','viewer',1,1,NULL,'$2y$12$5u9lHE5MpqRQy4fjeiCnVOD4j3/Gur.EMJ/hSXF0/l/ISf/RKn4a2',NULL,'2026-10-04 19:05:59','2026-10-04 19:05:59'),(5,'Administrator Kanwil','admin','admin@kemenkum.go.id','super_admin',NULL,1,NULL,'$2y$12$Eh06t6tJVfGeL1O/k5SXPuvAD8rj/PSHxH1qP8AZ94i437iiu6QYa',NULL,'2026-10-13 18:45:28','2026-10-13 18:45:28'),(6,'Operator Administrasi Hukum Umum (AHU)','ahu','ahu@kemenkum.go.id','operator_satker',NULL,1,NULL,'$2y$12$rZBUzCshPhTj6.kYoMjUTughyIzayxK22TmQT4FRGrARkliIUxyVu',NULL,'2026-10-13 18:45:29','2026-10-13 18:45:29'),(7,'Operator Kekayaan Intelektual (KI)','ki','ki@kemenkum.go.id','operator_satker',NULL,1,NULL,'$2y$12$HsPhkfb4wAzcBxm18I6kSOu3L75RL4P2v8Q7ift3qQTe9QZHnaFWi',NULL,'2026-10-13 18:45:29','2026-10-13 18:45:29'),(8,'Operator Sekretariat Jenderal (Setjen)','setjen','setjen@kemenkum.go.id','operator_satker',NULL,1,NULL,'$2y$12$RhghjT2gkZwkDfZ4Jhwj1.q.7P95agdb3ZfrvAtPT3FwQy.stBQMq',NULL,'2026-10-13 18:45:29','2026-10-13 18:45:29'),(9,'Operator Badan Strategi Kebijakan (BSK)','bsk','bsk@kemenkum.go.id','operator_satker',NULL,1,NULL,'$2y$12$cJNuBwvcO9PXJPKtZTOdsuqrwCgYRKyFQjOcnK4qQFWrx4Yz38vsW',NULL,'2026-10-13 18:45:29','2026-10-13 18:45:29'),(10,'Operator Badan Pembinaan Hukum Nasional (BHPN)','bhpn','bhpn@kemenkum.go.id','operator_satker',NULL,1,NULL,'$2y$12$fHa0wuHDu5hOZER3AeRkdeLiZISaWvtI0DOUqeqWkW2WuQqwQZNde',NULL,'2026-10-13 18:45:30','2026-10-13 18:45:30'),(11,'Operator Peraturan Perundang-undangan (PP)','pp','pp@kemenkum.go.id','operator_satker',NULL,1,NULL,'$2y$12$znl0N7z.6ZvB6jRrgAyYx.iOd/dqkZ/8aUo57.egqc8zN2sweAifO',NULL,'2026-10-13 18:45:30','2026-10-13 18:45:30');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'bakayuh'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-15  8:36:53

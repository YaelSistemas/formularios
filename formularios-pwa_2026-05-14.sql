-- MySQL dump 10.13  Distrib 8.0.45, for Linux (x86_64)
--
-- Host: localhost    Database: formularios-pwa
-- ------------------------------------------------------
-- Server version	8.0.45-0ubuntu0.22.04.1

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
  `expiration` int NOT NULL,
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
-- Table structure for table `empresa_histories`
--

DROP TABLE IF EXISTS `empresa_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `empresa_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `empresa_histories_empresa_id_created_at_index` (`empresa_id`,`created_at`),
  KEY `empresa_histories_user_id_index` (`user_id`),
  KEY `empresa_histories_action_index` (`action`),
  CONSTRAINT `empresa_histories_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `empresa_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa_histories`
--

LOCK TABLES `empresa_histories` WRITE;
/*!40000 ALTER TABLE `empresa_histories` DISABLE KEYS */;
INSERT INTO `empresa_histories` VALUES (1,1,1,'created','{\"estado\": \"Activo\", \"nombre\": \"Vysisa\", \"razon_social\": \"Vulcanizacion y Servicios Industriales\"}',NULL,'2026-03-31 18:09:56','2026-03-31 18:09:56');
/*!40000 ALTER TABLE `empresa_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresa_user`
--

DROP TABLE IF EXISTS `empresa_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresa_user` (
  `empresa_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `principal` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`empresa_id`,`user_id`),
  KEY `empresa_user_user_id_foreign` (`user_id`),
  CONSTRAINT `empresa_user_empresa_id_foreign` FOREIGN KEY (`empresa_id`) REFERENCES `empresas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `empresa_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresa_user`
--

LOCK TABLES `empresa_user` WRITE;
/*!40000 ALTER TABLE `empresa_user` DISABLE KEYS */;
INSERT INTO `empresa_user` VALUES (1,1,0,'2026-03-31 18:17:16','2026-03-31 18:17:16'),(1,2,0,'2026-03-31 18:11:09','2026-03-31 18:11:09'),(1,3,0,'2026-03-31 18:21:17','2026-03-31 18:21:17');
/*!40000 ALTER TABLE `empresa_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `empresas`
--

DROP TABLE IF EXISTS `empresas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `empresas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `razon_social` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `empresas`
--

LOCK TABLES `empresas` WRITE;
/*!40000 ALTER TABLE `empresas` DISABLE KEYS */;
INSERT INTO `empresas` VALUES (1,'Vysisa','Vulcanizacion y Servicios Industriales',1,'2026-03-31 18:09:56','2026-03-31 18:09:56');
/*!40000 ALTER TABLE `empresas` ENABLE KEYS */;
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
-- Table structure for table `form_histories`
--

DROP TABLE IF EXISTS `form_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `form_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `details` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_histories_form_id_created_at_index` (`form_id`,`created_at`),
  KEY `form_histories_user_id_index` (`user_id`),
  KEY `form_histories_action_index` (`action`),
  CONSTRAINT `form_histories_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `form_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_histories`
--

LOCK TABLES `form_histories` WRITE;
/*!40000 ALTER TABLE `form_histories` DISABLE KEYS */;
INSERT INTO `form_histories` VALUES (1,3,1,'published','{\"title\": \"SST-POP-TA-07-FO-01 Inspección de Compresor\", \"status\": \"PUBLICADO\"}',NULL,'2026-03-31 18:11:48','2026-03-31 18:11:48'),(2,3,1,'assigned_users','{\"title\": \"SST-POP-TA-07-FO-01 Inspección de Compresor\", \"status\": \"PUBLICADO\"}','{\"users\": [{\"id\": 2, \"name\": \"Leonardo Daniel Centeno Guerrero\", \"email\": \"soporte.sistemas2@grupo-vysisa.mx\"}]}','2026-03-31 18:12:02','2026-03-31 18:12:02'),(3,4,1,'published','{\"title\": \"SST-POP-TA-08-FO-01 Checklist de Herramienta Eléctrica Portátil\", \"status\": \"PUBLICADO\"}',NULL,'2026-04-09 19:31:51','2026-04-09 19:31:51'),(4,4,1,'assigned_users','{\"title\": \"SST-POP-TA-08-FO-01 Checklist de Herramienta Eléctrica Portátil\", \"status\": \"PUBLICADO\"}','{\"users\": [{\"id\": 2, \"name\": \"Leonardo Daniel Centeno Guerrero\", \"email\": \"soporte.sistemas2@grupo-vysisa.mx\"}, {\"id\": 3, \"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\"}]}','2026-04-09 19:32:14','2026-04-09 19:32:14'),(5,2,1,'assigned_users','{\"title\": \"SST-POP-TA-05-FO-03 Checklist Máquina de Soldar\", \"status\": \"BORRADOR\"}','{\"users\": [{\"id\": 2, \"name\": \"Leonardo Daniel Centeno Guerrero\", \"email\": \"soporte.sistemas2@grupo-vysisa.mx\"}, {\"id\": 3, \"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\"}]}','2026-04-09 20:49:22','2026-04-09 20:49:22'),(6,1,1,'assigned_users','{\"title\": \"SST-POP-TA-05-FO-02 Inspección de Equipo de Oxicorte\", \"status\": \"BORRADOR\"}','{\"users\": [{\"id\": 2, \"name\": \"Leonardo Daniel Centeno Guerrero\", \"email\": \"soporte.sistemas2@grupo-vysisa.mx\"}, {\"id\": 3, \"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\"}]}','2026-04-09 20:49:26','2026-04-09 20:49:26'),(7,2,1,'published','{\"title\": \"SST-POP-TA-05-FO-03 Checklist Máquina de Soldar\", \"status\": \"PUBLICADO\"}',NULL,'2026-04-09 20:49:28','2026-04-09 20:49:28'),(8,1,1,'published','{\"title\": \"SST-POP-TA-05-FO-02 Inspección de Equipo de Oxicorte\", \"status\": \"PUBLICADO\"}',NULL,'2026-04-09 20:49:29','2026-04-09 20:49:29'),(9,2,1,'unpublished','{\"title\": \"SST-POP-TA-05-FO-03 Checklist Máquina de Soldar\", \"status\": \"BORRADOR\"}',NULL,'2026-04-09 20:50:01','2026-04-09 20:50:01'),(10,1,1,'unpublished','{\"title\": \"SST-POP-TA-05-FO-02 Inspección de Equipo de Oxicorte\", \"status\": \"BORRADOR\"}',NULL,'2026-04-09 20:50:04','2026-04-09 20:50:04'),(11,3,1,'assigned_users','{\"title\": \"SST-POP-TA-07-FO-01 Inspección de Compresor\", \"status\": \"PUBLICADO\"}','{\"users\": [{\"id\": 3, \"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\"}]}','2026-04-09 20:53:43','2026-04-09 20:53:43'),(12,5,1,'published','{\"title\": \"SST-POP-TA-04-FO-04 Checklist Línea Retráctil y Puntos Fijos\", \"status\": \"PUBLICADO\"}',NULL,'2026-04-15 20:11:59','2026-04-15 20:11:59'),(13,5,1,'assigned_users','{\"title\": \"SST-POP-TA-04-FO-04 Checklist Línea Retráctil y Puntos Fijos\", \"status\": \"PUBLICADO\"}','{\"users\": [{\"id\": 3, \"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\"}]}','2026-04-15 20:12:10','2026-04-15 20:12:10'),(14,5,1,'unassigned_users','{\"title\": \"SST-POP-TA-04-FO-04 Checklist Línea Retráctil y Puntos Fijos\", \"status\": \"PUBLICADO\"}','{\"users\": [{\"id\": 3, \"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\"}]}','2026-04-15 20:12:19','2026-04-15 20:12:19'),(15,5,1,'assigned_users','{\"title\": \"SST-POP-TA-04-FO-04 Checklist Línea Retráctil y Puntos Fijos\", \"status\": \"PUBLICADO\"}','{\"users\": [{\"id\": 3, \"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\"}]}','2026-04-21 20:46:25','2026-04-21 20:46:25'),(16,5,1,'unpublished','{\"title\": \"SST-POP-TA-04-FO-04 Checklist Línea Retráctil y Puntos Fijos\", \"status\": \"BORRADOR\"}',NULL,'2026-04-21 20:46:45','2026-04-21 20:46:45');
/*!40000 ALTER TABLE `form_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_submission_histories`
--

DROP TABLE IF EXISTS `form_submission_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_submission_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `form_submission_id` bigint unsigned NOT NULL,
  `form_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `form_submission_histories_form_submission_id_created_at_index` (`form_submission_id`,`created_at`),
  KEY `form_submission_histories_form_id_created_at_index` (`form_id`,`created_at`),
  KEY `form_submission_histories_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `form_submission_histories_action_index` (`action`),
  CONSTRAINT `form_submission_histories_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE SET NULL,
  CONSTRAINT `form_submission_histories_form_submission_id_foreign` FOREIGN KEY (`form_submission_id`) REFERENCES `form_submissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_submission_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_submission_histories`
--

LOCK TABLES `form_submission_histories` WRITE;
/*!40000 ALTER TABLE `form_submission_histories` DISABLE KEYS */;
INSERT INTO `form_submission_histories` VALUES (1,1,3,2,'created','{\"taller\": \"Cedis Pachuca Calidad/PTS\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260331_181313_W0hU6Oo7.png\", \"tabla_compresor\": [{\"tipo\": \"ada\", \"marca\": \"afafsa\", \"modelo\": \"afafa\", \"carcasa\": \"Bien\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"fafafa\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"YO544\", \"responsable_seguridad\": \"Yomero\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260331_181313_yFZ4fdkz.png\"}',NULL,'2026-03-31 18:13:13','2026-03-31 18:13:13'),(2,2,3,1,'created','{\"taller\": \"Cedis Pachuca Tip Top\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u1_20260331_181615_8HOToPtB.png\", \"tabla_compresor\": [{\"tipo\": \"Hhg\", \"marca\": \"Vk n\", \"modelo\": \"Kvnv\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Gvv\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Gjvhl\", \"responsable_seguridad\": \"Jjk\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u1_20260331_181615_xCamMhJh.png\"}',NULL,'2026-03-31 18:16:15','2026-03-31 18:16:15'),(3,3,3,2,'created','{\"taller\": \"Apaxco\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_174833_LJ6WrRDD.png\", \"tabla_compresor\": [{\"tipo\": \"Iska\", \"marca\": \"Jajsjs\", \"modelo\": \"Bsbsba\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Jsjsja\", \"observaciones\": \"Ididis\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Juan\", \"responsable_seguridad\": \"Jsjsjs\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_174833_OViiQDXS.png\"}',NULL,'2026-04-07 17:48:33','2026-04-07 17:48:33'),(4,4,3,2,'created','{\"taller\": \"Cedis Pachuca\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_174953_sn1ZFGP4.png\", \"tabla_compresor\": [{\"tipo\": \"Hdjdj\", \"marca\": \"Hdjdjs\", \"modelo\": \"Jdksloxkx\", \"carcasa\": \"Bien\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Ndjdjd\", \"observaciones\": \"Ninguna\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Off Mobil\", \"responsable_seguridad\": \"Alvaro\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_174953_0Hur8E9N.png\"}',NULL,'2026-04-07 17:49:53','2026-04-07 17:49:53'),(5,5,3,2,'created','{\"taller\": \"San Luis Potosi\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_175209_vSFDzoO3.png\", \"tabla_compresor\": [{\"tipo\": \"Q\", \"marca\": \"Qwe\", \"modelo\": \"Qwer\", \"carcasa\": \"Bien\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Qw\", \"observaciones\": \"Dijdkd\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Miriam\", \"responsable_seguridad\": \"Yo\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_175209_CoyoweHQ.png\"}',NULL,'2026-04-07 17:52:09','2026-04-07 17:52:09'),(6,6,3,2,'created','{\"taller\": \"Morelos\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_175346_VharXbwd.png\", \"tabla_compresor\": [{\"tipo\": \"H\", \"marca\": \"V\", \"modelo\": \"Hh\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Mal\", \"numero_serie\": \"V\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Gh\", \"responsable_seguridad\": \"Jh\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_175346_JT0EHoR1.png\"}',NULL,'2026-04-07 17:53:46','2026-04-07 17:53:46'),(7,7,3,2,'created','{\"taller\": \"Tamuin\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_165220_5bozjSBP.png\", \"tabla_compresor\": [{\"tipo\": \"Tam\", \"marca\": \"Tam\", \"modelo\": \"Tam\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Mal\", \"numero_serie\": \"Tam\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Tam\", \"responsable_seguridad\": \"Tan\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_165220_4Coqi6zW.png\"}',NULL,'2026-04-08 16:52:20','2026-04-08 16:52:20'),(8,8,3,1,'created','{\"taller\": \"Zacatecas\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u1_20260408_181610_iz8aaXXy.png\", \"tabla_compresor\": [{\"tipo\": \"Eud\", \"marca\": \"Fhhf\", \"modelo\": \"Hdjd\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Mal\", \"numero_serie\": \"Fjfh\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Zaca\", \"responsable_seguridad\": \"Luis\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u1_20260408_181610_fIWjc5Vj.png\"}',NULL,'2026-04-08 18:16:10','2026-04-08 18:16:10'),(9,9,3,2,'created','{\"taller\": \"Xoxtla\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_193822_WDBXU3n0.png\", \"tabla_compresor\": [{\"tipo\": \"Hhg\", \"marca\": \"Hijvvi\", \"modelo\": \"Vkvk\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Yvuvv\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Pedro\", \"responsable_seguridad\": \"Bcdjkbkb\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_193822_mXCF55c2.png\"}',NULL,'2026-04-08 19:38:22','2026-04-08 19:38:22'),(10,10,3,2,'created','{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_194320_xEnWEwAn.png\", \"tabla_compresor\": [{\"tipo\": \"Cfguug\", \"marca\": \"Vjcci\", \"modelo\": \"Cjcjcjc\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Mal\", \"numero_serie\": \"Cujchc\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Bien\"}], \"nombre_inspector\": \"Hui\", \"responsable_seguridad\": \"Koko\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_194320_CPIQPlUL.png\"}',NULL,'2026-04-08 19:43:20','2026-04-08 19:43:20'),(11,11,3,2,'created','{\"taller\": \"Apaxco\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_211215_7ClHBsbd.png\", \"tabla_compresor\": [{\"tipo\": \"Hhd\", \"marca\": \"Xbzbbz\", \"modelo\": \"Xbxxb\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Dbdbbz\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Bien\"}], \"nombre_inspector\": \"Lalo\", \"responsable_seguridad\": \"Julian\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_211215_YEVHJHyH.png\"}',NULL,'2026-04-08 21:12:15','2026-04-08 21:12:15'),(12,12,3,2,'created','{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_211441_FaPWzI8v.png\", \"tabla_compresor\": [{\"tipo\": \"Fjf\", \"marca\": \"Brbdb\", \"modelo\": \"Bfbxbx\", \"carcasa\": \"Bien\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Bfbdbx\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Jose\", \"responsable_seguridad\": \"Djfhdh\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_211441_upp22rMM.png\"}',NULL,'2026-04-08 21:14:41','2026-04-08 21:14:41'),(13,13,3,1,'created','{\"taller\": \"Peñasquito\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u1_20260408_230825_5SDQrWZK.png\", \"tabla_compresor\": [{\"tipo\": \"wsws\", \"marca\": \"qwsqw\", \"modelo\": \"swqws\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"wsws\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Roberto\", \"responsable_seguridad\": \"Gerardo\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u1_20260408_230825_jfnRSM38.png\"}',NULL,'2026-04-08 23:08:25','2026-04-08 23:08:25'),(14,14,3,2,'created','{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260409_191357_E8SSTTXQ.png\", \"tabla_compresor\": [{\"tipo\": \"Juliaks\", \"marca\": \"Dbbdd\", \"modelo\": \"Bendnd\", \"carcasa\": \"Bien\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Dbdbd\", \"observaciones\": \"Ninguna\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Alondra\", \"responsable_seguridad\": \"Julia\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260409_191357_53RPiYYO.png\"}',NULL,'2026-04-09 19:13:57','2026-04-09 19:13:57'),(15,15,4,1,'created','{\"taller\": \"Cedis Pachuca Tip Top\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u1_20260409_203255_ODBkmPPG.png\", \"nombre_inspector\": \"Jose Ramon\", \"tabla_herramientas\": [{\"serie\": \"sqqw\", \"acciones\": \"La Herramienta esta en buenas condiciones\", \"aditamientos\": \"NA\", \"interruptores\": \"Si\", \"observaciones\": \"wqsqws\", \"mango_sujecion\": \"Si\", \"tipo_herramienta\": \"Dremel Multimax MM40\", \"condiciones_fisicas\": \"No\", \"conexiones_electricas\": \"No\", \"prueba_funcionamiento\": \"NA\"}]}',NULL,'2026-04-09 20:32:55','2026-04-09 20:32:55'),(16,16,4,1,'created','{\"taller\": \"Cedis Pachuca Calidad/PTS\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u1_20260409_204406_KKxmUgM8.png\", \"nombre_inspector\": \"Juliet\", \"tabla_herramientas\": [{\"serie\": \"ws\", \"acciones\": \"La Herramienta se identifica como dañada\", \"aditamientos\": \"NA\", \"interruptores\": \"Si\", \"observaciones\": \"\", \"mango_sujecion\": \"No\", \"tipo_herramienta\": \"Extensiones\", \"condiciones_fisicas\": \"NA\", \"conexiones_electricas\": \"NA\", \"prueba_funcionamiento\": \"Si\"}]}',NULL,'2026-04-09 20:44:06','2026-04-09 20:44:06'),(17,17,3,2,'created','{\"taller\": \"Monterrey\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260409_213159_2mg2iP44.png\", \"tabla_compresor\": [{\"tipo\": \"Urue\", \"marca\": \"Bxbx\", \"modelo\": \"Ndjd\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Jdjd\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Karla\", \"responsable_seguridad\": \"Kevin\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260409_213159_KCMHlYMf.png\"}',NULL,'2026-04-09 21:31:59','2026-04-09 21:31:59'),(18,18,3,2,'created','{\"taller\": \"Cedis Pachuca Calidad/PTS\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260420_205921_2g3HIS4k.png\", \"tabla_compresor\": [{\"tipo\": \"hh\", \"marca\": \"ggg\", \"modelo\": \"frt\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Mal\", \"numero_serie\": \"ttuyh\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Bien\"}], \"nombre_inspector\": \"fdf\", \"responsable_seguridad\": \"hgf\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260420_205921_b37dOLA6.png\"}',NULL,'2026-04-20 20:59:21','2026-04-20 20:59:21'),(19,19,4,3,'created','{\"taller\": \"Morelos\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u3_20260421_203636_06eJK7dw.png\", \"nombre_inspector\": \"Juan Pablo\", \"tabla_herramientas\": [{\"serie\": \"aaaa\", \"acciones\": \"La Herramienta esta en buenas condiciones\", \"aditamientos\": \"Si\", \"interruptores\": \"No\", \"observaciones\": \"Sin Observaciones\", \"mango_sujecion\": \"No\", \"tipo_herramienta\": \"Maquina Dremel\", \"condiciones_fisicas\": \"No\", \"conexiones_electricas\": \"NA\", \"prueba_funcionamiento\": \"NA\"}]}',NULL,'2026-04-21 20:36:36','2026-04-21 20:36:36'),(20,20,4,3,'created','{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u3_20260421_203811_aVjR2ABa.png\", \"nombre_inspector\": \"Sergio\", \"tabla_herramientas\": [{\"serie\": \"7855963\", \"acciones\": \"La Herramienta esta en buenas condiciones\", \"aditamientos\": \"Si\", \"interruptores\": \"Si\", \"observaciones\": \"\", \"mango_sujecion\": \"Si\", \"tipo_herramienta\": \"Extensiones\", \"condiciones_fisicas\": \"Si\", \"conexiones_electricas\": \"Si\", \"prueba_funcionamiento\": \"Si\"}]}',NULL,'2026-04-21 20:38:11','2026-04-21 20:38:11');
/*!40000 ALTER TABLE `form_submission_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_submissions`
--

DROP TABLE IF EXISTS `form_submissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_submissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `form_id` bigint unsigned NOT NULL,
  `consecutive` int unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `answers` json NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_submissions_form_id_consecutive_unique` (`form_id`,`consecutive`),
  KEY `form_submissions_user_id_foreign` (`user_id`),
  CONSTRAINT `form_submissions_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_submissions_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_submissions`
--

LOCK TABLES `form_submissions` WRITE;
/*!40000 ALTER TABLE `form_submissions` DISABLE KEYS */;
INSERT INTO `form_submissions` VALUES (1,3,1,2,'{\"taller\": \"Cedis Pachuca Calidad/PTS\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260331_181313_W0hU6Oo7.png\", \"tabla_compresor\": [{\"tipo\": \"ada\", \"marca\": \"afafsa\", \"modelo\": \"afafa\", \"carcasa\": \"Bien\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"fafafa\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"YO544\", \"responsable_seguridad\": \"Yomero\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260331_181313_yFZ4fdkz.png\"}','2026-03-31 18:13:13','2026-03-31 18:13:13'),(2,3,2,1,'{\"taller\": \"Cedis Pachuca Tip Top\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u1_20260331_181615_8HOToPtB.png\", \"tabla_compresor\": [{\"tipo\": \"Hhg\", \"marca\": \"Vk n\", \"modelo\": \"Kvnv\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Gvv\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Gjvhl\", \"responsable_seguridad\": \"Jjk\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u1_20260331_181615_xCamMhJh.png\"}','2026-03-31 18:16:15','2026-03-31 18:16:15'),(3,3,3,2,'{\"taller\": \"Apaxco\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_174833_LJ6WrRDD.png\", \"tabla_compresor\": [{\"tipo\": \"Iska\", \"marca\": \"Jajsjs\", \"modelo\": \"Bsbsba\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Jsjsja\", \"observaciones\": \"Ididis\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Juan\", \"responsable_seguridad\": \"Jsjsjs\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_174833_OViiQDXS.png\"}','2026-04-07 17:48:33','2026-04-07 17:48:33'),(4,3,4,2,'{\"taller\": \"Cedis Pachuca\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_174953_sn1ZFGP4.png\", \"tabla_compresor\": [{\"tipo\": \"Hdjdj\", \"marca\": \"Hdjdjs\", \"modelo\": \"Jdksloxkx\", \"carcasa\": \"Bien\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Ndjdjd\", \"observaciones\": \"Ninguna\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Off Mobil\", \"responsable_seguridad\": \"Alvaro\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_174953_0Hur8E9N.png\"}','2026-04-07 17:49:53','2026-04-07 17:49:53'),(5,3,5,2,'{\"taller\": \"San Luis Potosi\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_175209_vSFDzoO3.png\", \"tabla_compresor\": [{\"tipo\": \"Q\", \"marca\": \"Qwe\", \"modelo\": \"Qwer\", \"carcasa\": \"Bien\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Qw\", \"observaciones\": \"Dijdkd\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Miriam\", \"responsable_seguridad\": \"Yo\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_175209_CoyoweHQ.png\"}','2026-04-07 17:52:09','2026-04-07 17:52:09'),(6,3,6,2,'{\"taller\": \"Morelos\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260407_175346_VharXbwd.png\", \"tabla_compresor\": [{\"tipo\": \"H\", \"marca\": \"V\", \"modelo\": \"Hh\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Mal\", \"numero_serie\": \"V\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Gh\", \"responsable_seguridad\": \"Jh\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260407_175346_JT0EHoR1.png\"}','2026-04-07 17:53:46','2026-04-07 17:53:46'),(7,3,7,2,'{\"taller\": \"Tamuin\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_165220_5bozjSBP.png\", \"tabla_compresor\": [{\"tipo\": \"Tam\", \"marca\": \"Tam\", \"modelo\": \"Tam\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Mal\", \"numero_serie\": \"Tam\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Tam\", \"responsable_seguridad\": \"Tan\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_165220_4Coqi6zW.png\"}','2026-04-08 16:52:20','2026-04-08 16:52:20'),(8,3,8,1,'{\"taller\": \"Zacatecas\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u1_20260408_181610_iz8aaXXy.png\", \"tabla_compresor\": [{\"tipo\": \"Eud\", \"marca\": \"Fhhf\", \"modelo\": \"Hdjd\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Mal\", \"numero_serie\": \"Fjfh\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Zaca\", \"responsable_seguridad\": \"Luis\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u1_20260408_181610_fIWjc5Vj.png\"}','2026-04-08 18:16:10','2026-04-08 18:16:10'),(9,3,9,2,'{\"taller\": \"Xoxtla\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_193822_WDBXU3n0.png\", \"tabla_compresor\": [{\"tipo\": \"Hhg\", \"marca\": \"Hijvvi\", \"modelo\": \"Vkvk\", \"carcasa\": \"Mal\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Yvuvv\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Pedro\", \"responsable_seguridad\": \"Bcdjkbkb\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_193822_mXCF55c2.png\"}','2026-04-08 19:38:22','2026-04-08 19:38:22'),(10,3,10,2,'{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_194320_xEnWEwAn.png\", \"tabla_compresor\": [{\"tipo\": \"Cfguug\", \"marca\": \"Vjcci\", \"modelo\": \"Cjcjcjc\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Mal\", \"numero_serie\": \"Cujchc\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Bien\"}], \"nombre_inspector\": \"Hui\", \"responsable_seguridad\": \"Koko\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_194320_CPIQPlUL.png\"}','2026-04-08 19:43:20','2026-04-08 19:43:20'),(11,3,11,2,'{\"taller\": \"Apaxco\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_211215_7ClHBsbd.png\", \"tabla_compresor\": [{\"tipo\": \"Hhd\", \"marca\": \"Xbzbbz\", \"modelo\": \"Xbxxb\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Dbdbbz\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Bien\"}], \"nombre_inspector\": \"Lalo\", \"responsable_seguridad\": \"Julian\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_211215_YEVHJHyH.png\"}','2026-04-08 21:12:15','2026-04-08 21:12:15'),(12,3,12,2,'{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260408_211441_FaPWzI8v.png\", \"tabla_compresor\": [{\"tipo\": \"Fjf\", \"marca\": \"Brbdb\", \"modelo\": \"Bfbxbx\", \"carcasa\": \"Bien\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Bfbdbx\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Mal\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Mal\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Jose\", \"responsable_seguridad\": \"Djfhdh\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260408_211441_upp22rMM.png\"}','2026-04-08 21:14:41','2026-04-08 21:14:41'),(13,3,13,1,'{\"taller\": \"Peñasquito\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u1_20260408_230825_5SDQrWZK.png\", \"tabla_compresor\": [{\"tipo\": \"wsws\", \"marca\": \"qwsqw\", \"modelo\": \"swqws\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"wsws\", \"observaciones\": \"\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Mal\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Roberto\", \"responsable_seguridad\": \"Gerardo\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u1_20260408_230825_jfnRSM38.png\"}','2026-04-08 23:08:25','2026-04-08 23:08:25'),(14,3,14,2,'{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260409_191357_E8SSTTXQ.png\", \"tabla_compresor\": [{\"tipo\": \"Juliaks\", \"marca\": \"Dbbdd\", \"modelo\": \"Bendnd\", \"carcasa\": \"Bien\", \"regulador\": \"Mal\", \"contenedor\": \"Bien\", \"numero_serie\": \"Dbdbd\", \"observaciones\": \"Ninguna\", \"valvula_control\": \"Mal\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Mal\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Bien\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Alondra\", \"responsable_seguridad\": \"Julia\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260409_191357_53RPiYYO.png\"}','2026-04-09 19:13:57','2026-04-09 19:13:57'),(15,4,1,1,'{\"taller\": \"Cedis Pachuca Tip Top\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u1_20260409_203255_ODBkmPPG.png\", \"nombre_inspector\": \"Jose Ramon\", \"tabla_herramientas\": [{\"serie\": \"sqqw\", \"acciones\": \"La Herramienta esta en buenas condiciones\", \"aditamientos\": \"NA\", \"interruptores\": \"Si\", \"observaciones\": \"wqsqws\", \"mango_sujecion\": \"Si\", \"tipo_herramienta\": \"Dremel Multimax MM40\", \"condiciones_fisicas\": \"No\", \"conexiones_electricas\": \"No\", \"prueba_funcionamiento\": \"NA\"}]}','2026-04-09 20:32:55','2026-04-09 20:32:55'),(16,4,2,1,'{\"taller\": \"Cedis Pachuca Calidad/PTS\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u1_20260409_204406_KKxmUgM8.png\", \"nombre_inspector\": \"Juliet\", \"tabla_herramientas\": [{\"serie\": \"ws\", \"acciones\": \"La Herramienta se identifica como dañada\", \"aditamientos\": \"NA\", \"interruptores\": \"Si\", \"observaciones\": \"\", \"mango_sujecion\": \"No\", \"tipo_herramienta\": \"Extensiones\", \"condiciones_fisicas\": \"NA\", \"conexiones_electricas\": \"NA\", \"prueba_funcionamiento\": \"Si\"}]}','2026-04-09 20:44:06','2026-04-09 20:44:06'),(17,3,15,2,'{\"taller\": \"Monterrey\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260409_213159_2mg2iP44.png\", \"tabla_compresor\": [{\"tipo\": \"Urue\", \"marca\": \"Bxbx\", \"modelo\": \"Ndjd\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Bien\", \"numero_serie\": \"Jdjd\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Mal\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Mal\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Mal\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Mal\"}], \"nombre_inspector\": \"Karla\", \"responsable_seguridad\": \"Kevin\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260409_213159_KCMHlYMf.png\"}','2026-04-09 21:31:59','2026-04-09 21:31:59'),(18,3,16,2,'{\"taller\": \"Cedis Pachuca Calidad/PTS\", \"firma_inspector\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/firma_firma_inspector_u2_20260420_205921_2g3HIS4k.png\", \"tabla_compresor\": [{\"tipo\": \"hh\", \"marca\": \"ggg\", \"modelo\": \"frt\", \"carcasa\": \"Mal\", \"regulador\": \"Bien\", \"contenedor\": \"Mal\", \"numero_serie\": \"ttuyh\", \"observaciones\": \"\", \"valvula_control\": \"Bien\", \"valvula_drenaje\": \"Bien\", \"manometro_salida\": \"Bien\", \"manometro_tanque\": \"Bien\", \"valvula_seguridad\": \"Bien\", \"interruptor_on_off\": \"Bien\", \"manguera_alimentacion\": \"Mal\", \"enrolla_cable_electrico\": \"Bien\", \"cable_alimentacion_electrica\": \"Bien\", \"conectores_rapidos_universales\": \"Bien\"}], \"nombre_inspector\": \"fdf\", \"responsable_seguridad\": \"hgf\", \"firma_responsable_seguridad\": \"forms/signatures/SSTPOPTA07FO01_InspeccionCompresor/Responsable_Seguridad/firma_firma_responsable_seguridad_u2_20260420_205921_b37dOLA6.png\"}','2026-04-20 20:59:21','2026-04-20 20:59:21'),(19,4,3,3,'{\"taller\": \"Morelos\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u3_20260421_203636_06eJK7dw.png\", \"nombre_inspector\": \"Juan Pablo\", \"tabla_herramientas\": [{\"serie\": \"aaaa\", \"acciones\": \"La Herramienta esta en buenas condiciones\", \"aditamientos\": \"Si\", \"interruptores\": \"No\", \"observaciones\": \"Sin Observaciones\", \"mango_sujecion\": \"No\", \"tipo_herramienta\": \"Maquina Dremel\", \"condiciones_fisicas\": \"No\", \"conexiones_electricas\": \"NA\", \"prueba_funcionamiento\": \"NA\"}]}','2026-04-21 20:36:36','2026-04-21 20:36:36'),(20,4,4,3,'{\"taller\": \"Huichapan\", \"firma_inspector\": \"forms/signatures/SSTPOPTA08F001_CheckListHerramientaElectricaPortatil/firma_firma_inspector_u3_20260421_203811_aVjR2ABa.png\", \"nombre_inspector\": \"Sergio\", \"tabla_herramientas\": [{\"serie\": \"7855963\", \"acciones\": \"La Herramienta esta en buenas condiciones\", \"aditamientos\": \"Si\", \"interruptores\": \"Si\", \"observaciones\": \"\", \"mango_sujecion\": \"Si\", \"tipo_herramienta\": \"Extensiones\", \"condiciones_fisicas\": \"Si\", \"conexiones_electricas\": \"Si\", \"prueba_funcionamiento\": \"Si\"}]}','2026-04-21 20:38:11','2026-04-21 20:38:11');
/*!40000 ALTER TABLE `form_submissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `form_user`
--

DROP TABLE IF EXISTS `form_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `form_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `form_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `form_user_form_id_user_id_unique` (`form_id`,`user_id`),
  KEY `form_user_user_id_foreign` (`user_id`),
  CONSTRAINT `form_user_form_id_foreign` FOREIGN KEY (`form_id`) REFERENCES `forms` (`id`) ON DELETE CASCADE,
  CONSTRAINT `form_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `form_user`
--

LOCK TABLES `form_user` WRITE;
/*!40000 ALTER TABLE `form_user` DISABLE KEYS */;
INSERT INTO `form_user` VALUES (1,3,2,'2026-03-31 18:12:02','2026-03-31 18:12:02'),(2,4,2,'2026-04-09 19:32:14','2026-04-09 19:32:14'),(3,4,3,'2026-04-09 19:32:14','2026-04-09 19:32:14'),(4,2,2,'2026-04-09 20:49:22','2026-04-09 20:49:22'),(5,2,3,'2026-04-09 20:49:22','2026-04-09 20:49:22'),(6,1,3,'2026-04-09 20:49:26','2026-04-09 20:49:26'),(7,1,2,'2026-04-09 20:49:26','2026-04-09 20:49:26'),(8,3,3,'2026-04-09 20:53:43','2026-04-09 20:53:43'),(10,5,3,'2026-04-21 20:46:25','2026-04-21 20:46:25');
/*!40000 ALTER TABLE `form_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `forms`
--

DROP TABLE IF EXISTS `forms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `forms` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'BORRADOR',
  `payload` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `forms_user_id_foreign` (`user_id`),
  CONSTRAINT `forms_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `forms`
--

LOCK TABLES `forms` WRITE;
/*!40000 ALTER TABLE `forms` DISABLE KEYS */;
INSERT INTO `forms` VALUES (1,1,'SST-POP-TA-05-FO-02 Inspección de Equipo de Oxicorte','BORRADOR','{\"meta\": {\"layout\": \"inspeccion_equipo_oxicorte\"}, \"fields\": [{\"id\": \"encabezado_logo\", \"url\": \"/images/forms/Encabezado-vysisa.png\", \"type\": \"fixed_image\", \"label\": \"Encabezado\", \"required\": false}, {\"id\": \"header_line_1\", \"text\": \"VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.\", \"type\": \"static_text\", \"label\": \"Empresa\", \"required\": false}, {\"id\": \"header_line_2\", \"text\": \"SISTEMA DE GESTIÓN INTEGRAL\", \"type\": \"static_text\", \"label\": \"Sistema\", \"required\": false}, {\"id\": \"header_line_3\", \"text\": \"Inspección de Equipo de Oxicorte\", \"type\": \"static_text\", \"label\": \"Nombre del formato\", \"required\": false}, {\"id\": \"header_line_4\", \"text\": \"Código: SST-POP-TA-05-FO-02\", \"type\": \"static_text\", \"label\": \"Código\", \"required\": false}, {\"id\": \"header_line_5\", \"text\": \"Fecha de Emisión: 27/03/2025\", \"type\": \"static_text\", \"label\": \"Fecha de emisión\", \"required\": false}, {\"id\": \"header_line_6\", \"text\": \"Número de Revisión: 03\", \"type\": \"static_text\", \"label\": \"Número de revisión\", \"required\": false}, {\"id\": \"taller\", \"type\": \"select\", \"label\": \"Taller\", \"options\": [\"Apaxco\", \"Aztecas\", \"Cedis Pachuca\", \"Cedis Pachuca Calidad/PTS\", \"Cedis Pachuca Tip Top\", \"Colima\", \"Huichapan\", \"Monterrey\", \"Morelos\", \"Orizaba\", \"Peñasquito\", \"San Luis Potosi\", \"Tamuin\", \"Tepeaca\", \"Torreon\", \"Vysisa Sureste (Merida)\", \"Xoxtla\", \"Zacatecas\"], \"required\": true}, {\"id\": \"nombre_inspector\", \"type\": \"text\", \"label\": \"Nombre del inspector\", \"required\": true}, {\"id\": \"firma_inspector\", \"type\": \"signature\", \"label\": \"Firma del inspector\", \"required\": true}, {\"id\": \"nombre_supervisor\", \"type\": \"text\", \"label\": \"Nombre del supervisor\", \"required\": true}, {\"id\": \"firma_supervisor\", \"type\": \"signature\", \"label\": \"Firma del supervisor\", \"required\": true}, {\"id\": \"indicaciones_toggle\", \"text\": \"Indicaciones de llenado\", \"type\": \"static_text\", \"label\": \"Indicaciones de llenado\", \"required\": false}, {\"id\": \"guia_inspeccion_text\", \"text\": \"Guía de Inspección\", \"type\": \"static_text\", \"label\": \"Guía de inspección\", \"required\": false}, {\"id\": \"imagen_equipo_oxicorte\", \"url\": \"/images/forms/SST_POP_TA_05_FO_02_Inspeccion_de_Equipo_de_Oxicorte/Imagen_Oxicorte.png\", \"type\": \"fixed_image\", \"label\": \"Imagen equipo de oxicorte\", \"required\": false}, {\"id\": \"indicaciones_line_1\", \"text\": \"Marque según lo que aplique.\", \"type\": \"static_text\", \"label\": \"Indicacion 1\", \"required\": false}, {\"id\": \"numero_identificacion_equipo_oxicorte\", \"type\": \"text\", \"label\": \"Número de Identificación de Equipo de Oxicorte\", \"required\": true}, {\"id\": \"carro_porta_cilindros_cadena_estado\", \"type\": \"radio\", \"label\": \"1. Carro Porta Cilindros con Cadena\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"estado_fisico_cilindros_estado\", \"type\": \"radio\", \"label\": \"2. Estado Físico de los Cilindros\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"regulador_oxigeno_estado\", \"type\": \"radio\", \"label\": \"3. Regulador de Oxígeno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manometro_alta_presion_oxigeno_estado\", \"type\": \"radio\", \"label\": \"4. Manómetro de Alta Presión, Contenido\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manometro_baja_presion_oxigeno_estado\", \"type\": \"radio\", \"label\": \"5. Manómetro de Baja Presión, Trabajo\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"valvula_check_regulador_oxigeno_estado\", \"type\": \"radio\", \"label\": \"6. Válvula Check Regulador de Oxígeno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"regulador_acetileno_estado\", \"type\": \"radio\", \"label\": \"7. Regulador de Acetileno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manometro_alta_presion_acetileno_estado\", \"type\": \"radio\", \"label\": \"8. Manómetro de Alta Presión, Contenido\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manometro_baja_presion_acetileno_estado\", \"type\": \"radio\", \"label\": \"9. Manómetro de Baja Presión, Trabajo\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"valvula_check_regulador_acetileno_estado\", \"type\": \"radio\", \"label\": \"10. Válvula Check Regulador de Acetileno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manguera_oxigeno_estado\", \"type\": \"radio\", \"label\": \"11. Manguera de Oxígeno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"valvula_check_maneral_oxigeno_estado\", \"type\": \"radio\", \"label\": \"12. Válvula Check Maneral de Oxígeno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manguera_acetileno_estado\", \"type\": \"radio\", \"label\": \"13. Manguera de Acetileno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"valvula_check_maneral_acetileno_estado\", \"type\": \"radio\", \"label\": \"14. Válvula Check Maneral de Acetileno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"abrazaderas_estado\", \"type\": \"radio\", \"label\": \"15. Abrazaderas\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"maneral_mezclador_gases_estado\", \"type\": \"radio\", \"label\": \"16. Maneral Mezclador de Gases\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"llave_dosificadora_oxigeno_estado\", \"type\": \"radio\", \"label\": \"17. Llave Dosificadora de Oxígeno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"llave_dosificadora_acetileno_estado\", \"type\": \"radio\", \"label\": \"18. Llave Dosificadora de Acetileno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"boquilla_corte_soldadura_estado\", \"type\": \"radio\", \"label\": \"19. Boquilla de Corte o Soldadura\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"tuercas_roscadas_union_empaques_estado\", \"type\": \"radio\", \"label\": \"20. Tuercas Roscadas de Unión y Empaques\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"limpia_boquillas_estado\", \"type\": \"radio\", \"label\": \"21. Limpia Boquillas\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"chispero_estado\", \"type\": \"radio\", \"label\": \"22. Chispero\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"llave_cuadro_acetileno_estado\", \"type\": \"radio\", \"label\": \"23. Llave de Cuadro de Acetileno\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"extintor_cercano_area_trabajo_estado\", \"type\": \"radio\", \"label\": \"24. Extintor Cercano al Área de Trabajo\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"verificar_jabonadura_text\", \"text\": \"Verificar con Jabonadura Todas las Conexiones del Equipo\", \"type\": \"static_text\", \"label\": \"Texto jabonadura\", \"required\": false}, {\"id\": \"observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": false}, {\"id\": \"nota_final\", \"text\": \"NOTA: SI EL EQUIPO TIENE DEFICIENCIAS, SUSPENDER SU USO DE INMEDIATO.\", \"type\": \"static_text\", \"label\": \"Nota final\", \"required\": false}], \"_code_key\": \"sst_pop_ta_05_fo_02_inspeccion_de_equipo_de_oxicorte\"}','2026-03-31 18:09:12','2026-05-07 20:22:27'),(2,1,'SST-POP-TA-05-FO-03 Checklist Máquina de Soldar','BORRADOR','{\"meta\": {\"layout\": \"checklist_maquina_de_soldar\"}, \"fields\": [{\"id\": \"encabezado_logo\", \"url\": \"/images/forms/Encabezado-vysisa.png\", \"type\": \"fixed_image\", \"label\": \"Encabezado\", \"required\": false}, {\"id\": \"header_line_1\", \"text\": \"VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.\", \"type\": \"static_text\", \"label\": \"Empresa\", \"required\": false}, {\"id\": \"header_line_2\", \"text\": \"SISTEMA DE GESTIÓN INTEGRAL\", \"type\": \"static_text\", \"label\": \"Sistema\", \"required\": false}, {\"id\": \"header_line_3\", \"text\": \"Checklist Máquina de Soldar\", \"type\": \"static_text\", \"label\": \"Nombre del formato\", \"required\": false}, {\"id\": \"header_line_4\", \"text\": \"Código: SST-POP-TA-05-FO-03\", \"type\": \"static_text\", \"label\": \"Código\", \"required\": false}, {\"id\": \"header_line_5\", \"text\": \"Fecha de Emisión: 27/03/2025\", \"type\": \"static_text\", \"label\": \"Fecha de emisión\", \"required\": false}, {\"id\": \"header_line_6\", \"text\": \"Número de Revisión: 02\", \"type\": \"static_text\", \"label\": \"Número de revisión\", \"required\": false}, {\"id\": \"taller\", \"type\": \"select\", \"label\": \"Taller\", \"options\": [\"Apaxco\", \"Aztecas\", \"Cedis Pachuca\", \"Cedis Pachuca Calidad/PTS\", \"Cedis Pachuca Tip Top\", \"Colima\", \"Huichapan\", \"Monterrey\", \"Morelos\", \"Orizaba\", \"Peñasquito\", \"San Luis Potosi\", \"Tamuin\", \"Tepeaca\", \"Torreon\", \"Vysisa Sureste (Merida)\", \"Xoxtla\", \"Zacatecas\"], \"required\": true}, {\"id\": \"nombre_inspector\", \"type\": \"text\", \"label\": \"Nombre del inspector\", \"required\": true}, {\"id\": \"firma_inspector\", \"type\": \"signature\", \"label\": \"Firma del inspector\", \"required\": true}, {\"id\": \"nombre_supervisor\", \"type\": \"text\", \"label\": \"Nombre del supervisor\", \"required\": true}, {\"id\": \"firma_supervisor\", \"type\": \"signature\", \"label\": \"Firma del supervisor\", \"required\": true}, {\"id\": \"indicaciones_toggle\", \"text\": \"Indicaciones de llenado\", \"type\": \"static_text\", \"label\": \"Indicaciones de llenado\", \"required\": false}, {\"id\": \"imagen_maquina_soldar\", \"url\": \"/images/forms/SST_POP_TA_05_FO_03_Checklist_Maquina_de_Soldar/Imagen_Maquina_Soldar.png\", \"type\": \"fixed_image\", \"label\": \"Imagen máquina de soldar\", \"required\": false}, {\"id\": \"indicaciones_line_1\", \"text\": \"Marque según lo que aplique.\", \"type\": \"static_text\", \"label\": \"Indicacion 1\", \"required\": false}, {\"id\": \"numero_serie_maquina\", \"type\": \"text\", \"label\": \"No. de Serie\", \"required\": true}, {\"id\": \"tipo_modelo_maquina\", \"type\": \"text\", \"label\": \"Tipo y modelo de maquina\", \"required\": true}, {\"id\": \"voltimetro_estado\", \"type\": \"radio\", \"label\": \"1. Voltímetro\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"voltimetro_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"interruptor_encendido_apagado_estado\", \"type\": \"radio\", \"label\": \"2. Interruptor de Encendido y Apagado\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"interruptor_encendido_apagado_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"control_inductancia_estado\", \"type\": \"radio\", \"label\": \"3. Control de Inductancia\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"control_inductancia_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"selector_rotativo_proceso_estado\", \"type\": \"radio\", \"label\": \"4. Selector Rotativo de Proceso\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"selector_rotativo_proceso_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"amperimetro_estado\", \"type\": \"radio\", \"label\": \"5. Amperímetro\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"amperimetro_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"control_ajuste_amperaje_voltaje_estado\", \"type\": \"radio\", \"label\": \"6. Control de Ajuste de Amperaje/Voltaje\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"control_ajuste_amperaje_voltaje_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"sector_control_amperaje_voltaje_estado\", \"type\": \"radio\", \"label\": \"7. Sector de Control de Amperaje/Voltaje\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"sector_control_amperaje_voltaje_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"carcasa_metalica_proteccion_estado\", \"type\": \"radio\", \"label\": \"8. Carcasa Metálica de Protección\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"carcasa_metalica_proteccion_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"pantalla_estado\", \"type\": \"radio\", \"label\": \"9. Pantalla\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"pantalla_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"dispositivo_bloqueo_estado\", \"type\": \"radio\", \"label\": \"10. Dispositivo de Bloqueo\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"dispositivo_bloqueo_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"cable_tierra_estado\", \"type\": \"radio\", \"label\": \"11. Cable a Tierra\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"cable_tierra_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"pinza_cable_tierra_estado\", \"type\": \"radio\", \"label\": \"12. Pinza de Cable a Tierra\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"pinza_cable_tierra_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"cable_porta_electrodos_estado\", \"type\": \"radio\", \"label\": \"13. Cable Porta Electrodos\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"cable_porta_electrodos_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"pinza_porta_electrodos_estado\", \"type\": \"radio\", \"label\": \"14. Pinza Porta Electrodos\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"pinza_porta_electrodos_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"cables_alimentacion_aislados_estado\", \"type\": \"radio\", \"label\": \"15. Cables de Alimentación Aislados\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"cables_alimentacion_aislados_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"aislamiento_humedad_estado\", \"type\": \"radio\", \"label\": \"16. Aislamiento de Humedad\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"aislamiento_humedad_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}, {\"id\": \"limpieza_estado\", \"type\": \"radio\", \"label\": \"17. Limpieza\", \"options\": [\"(B) Bueno\", \"(M) Malo\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"limpieza_observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}], \"_code_key\": \"sst_pop_ta_05_fo_03_checklist_maquina_de_soldar\"}','2026-03-31 18:09:12','2026-05-07 20:22:27'),(3,1,'SST-POP-TA-07-FO-01 Inspección de Compresor','PUBLICADO','{\"meta\": {\"layout\": \"inspeccion_compresor\"}, \"fields\": [{\"id\": \"encabezado_logo\", \"url\": \"/images/forms/Encabezado-vysisa.png\", \"type\": \"fixed_image\", \"label\": \"Encabezado\", \"required\": false}, {\"id\": \"header_line_1\", \"text\": \"VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.\", \"type\": \"static_text\", \"label\": \"Empresa\", \"required\": false}, {\"id\": \"header_line_2\", \"text\": \"SISTEMA DE GESTIÓN INTEGRAL\", \"type\": \"static_text\", \"label\": \"Sistema\", \"required\": false}, {\"id\": \"header_line_3\", \"text\": \"Inspección de Compresor\", \"type\": \"static_text\", \"label\": \"Nombre del formato\", \"required\": false}, {\"id\": \"header_line_4\", \"text\": \"Código: SST-POP-TA-07-FO-01\", \"type\": \"static_text\", \"label\": \"Código\", \"required\": false}, {\"id\": \"header_line_5\", \"text\": \"Fecha de Emisión: 27/03/2025\", \"type\": \"static_text\", \"label\": \"Fecha de emisión\", \"required\": false}, {\"id\": \"header_line_6\", \"text\": \"Número de Revisión: 03\", \"type\": \"static_text\", \"label\": \"Número de revisión\", \"required\": false}, {\"id\": \"taller\", \"type\": \"select\", \"label\": \"Taller\", \"options\": [\"Apaxco\", \"Aztecas\", \"Cedis Pachuca\", \"Cedis Pachuca Calidad/PTS\", \"Cedis Pachuca Tip Top\", \"Colima\", \"Huichapan\", \"Monterrey\", \"Morelos\", \"Orizaba\", \"Peñasquito\", \"San Luis Potosi\", \"Tamuin\", \"Tepeaca\", \"Torreon\", \"Vysisa Sureste (Merida)\", \"Xoxtla\", \"Zacatecas\"], \"required\": true}, {\"id\": \"nombre_inspector\", \"type\": \"text\", \"label\": \"Nombre del inspector\", \"required\": true}, {\"id\": \"firma_inspector\", \"type\": \"signature\", \"label\": \"Firma del inspector\", \"required\": true}, {\"id\": \"indicaciones_toggle\", \"text\": \"Indicaciones de llenado\", \"type\": \"static_text\", \"label\": \"Indicaciones de llenado\", \"required\": false}, {\"id\": \"indicaciones_line_1\", \"text\": \"Presion **: PRESIÓN DE CALIBRACIÓN EN SUS DISPOSITIVOS DE RELEVO DE PRESIÓN\", \"type\": \"static_text\", \"label\": \"Indicacion 1\", \"required\": false}, {\"id\": \"indicaciones_line_2\", \"text\": \"Este formato deberá llenarse una vez al mes.\", \"type\": \"static_text\", \"label\": \"Indicacion 2\", \"required\": false}, {\"id\": \"indicaciones_line_3\", \"text\": \"Marque según lo que aplique\", \"type\": \"static_text\", \"label\": \"Indicacion 3\", \"required\": false}, {\"id\": \"tabla_compresor\", \"type\": \"table\", \"label\": \"Criterios a inspeccionar\", \"columns\": [\"Tipo\", \"Número de serie\", \"Marca\", \"Modelo\", \"A) INTERRUPTOR DE ON (I) OFF (O)\", \"B) MANÓMETRO DE TANQUE\", \"C) MANÓMETRO (MEDIDOR DE PRESIÓN) DE SALIDA\", \"D) REGULADOR\", \"E) CONECTORES RÁPIDOS UNIVERSALES\", \"F) VÁLVULA DE SEGURIDAD\", \"G) VÁLVULA DE DRENAJE\", \"H) ENROLLA CABLE ELÉCTRICO\", \"I) VÁLVULA DE CONTROL\", \"J) CABLE DE ALIMENTACIÓN ELÉCTRICA\", \"K) CONTENEDOR\", \"L) CARCASA\", \"M) MANGUERA DE ALIMENTACIÓN\", \"Observaciones\"], \"required\": true, \"row_schema\": [{\"id\": \"tipo\", \"type\": \"text\", \"label\": \"Tipo\", \"required\": true}, {\"id\": \"numero_serie\", \"type\": \"text\", \"label\": \"Número de serie\", \"required\": true}, {\"id\": \"marca\", \"type\": \"text\", \"label\": \"Marca\", \"required\": true}, {\"id\": \"modelo\", \"type\": \"text\", \"label\": \"Modelo\", \"required\": true}, {\"id\": \"interruptor_on_off\", \"type\": \"radio\", \"label\": \"A) INTERRUPTOR DE ON (I) OFF (O)\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manometro_tanque\", \"type\": \"radio\", \"label\": \"B) MANÓMETRO DE TANQUE\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manometro_salida\", \"type\": \"radio\", \"label\": \"C) MANÓMETRO (MEDIDOR DE PRESIÓN) DE SALIDA\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"regulador\", \"type\": \"radio\", \"label\": \"D) REGULADOR\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"conectores_rapidos_universales\", \"type\": \"radio\", \"label\": \"E) CONECTORES RÁPIDOS UNIVERSALES\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"valvula_seguridad\", \"type\": \"radio\", \"label\": \"F) VÁLVULA DE SEGURIDAD\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"valvula_drenaje\", \"type\": \"radio\", \"label\": \"G) VÁLVULA DE DRENAJE\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"enrolla_cable_electrico\", \"type\": \"radio\", \"label\": \"H) ENROLLA CABLE ELÉCTRICO\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"valvula_control\", \"type\": \"radio\", \"label\": \"I) VÁLVULA DE CONTROL\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"cable_alimentacion_electrica\", \"type\": \"radio\", \"label\": \"J) CABLE DE ALIMENTACIÓN ELÉCTRICA\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"contenedor\", \"type\": \"radio\", \"label\": \"K) CONTENEDOR\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"carcasa\", \"type\": \"radio\", \"label\": \"L) CARCASA\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"manguera_alimentacion\", \"type\": \"radio\", \"label\": \"M) MANGUERA DE ALIMENTACIÓN\", \"options\": [\"Bien\", \"Mal\"], \"required\": true}, {\"id\": \"observaciones\", \"type\": \"text\", \"label\": \"Observaciones\", \"required\": false}]}, {\"id\": \"responsable_seguridad\", \"type\": \"text\", \"label\": \"Nombre del Supervisor\", \"required\": true}, {\"id\": \"firma_responsable_seguridad\", \"type\": \"signature\", \"label\": \"Firma del Supervisor\", \"required\": true}], \"_code_key\": \"sst_pop_ta_07_fo_01_inspeccion_de_compresor\"}','2026-03-31 18:09:12','2026-05-07 20:22:27'),(4,1,'SST-POP-TA-08-FO-01 Checklist de Herramienta Eléctrica Portátil','PUBLICADO','{\"meta\": {\"layout\": \"checklist_herramienta_electrica_portatil\"}, \"fields\": [{\"id\": \"encabezado_logo\", \"url\": \"/images/forms/Encabezado-vysisa.png\", \"type\": \"fixed_image\", \"label\": \"Encabezado\", \"required\": false}, {\"id\": \"header_line_1\", \"text\": \"VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.\", \"type\": \"static_text\", \"label\": \"Empresa\", \"required\": false}, {\"id\": \"header_line_2\", \"text\": \"SISTEMA DE GESTIÓN INTEGRAL\", \"type\": \"static_text\", \"label\": \"Sistema\", \"required\": false}, {\"id\": \"header_line_3\", \"text\": \"Checklist de Herramienta Eléctrica Portátil\", \"type\": \"static_text\", \"label\": \"Nombre del formato\", \"required\": false}, {\"id\": \"header_line_4\", \"text\": \"Código: SST-POP-TA-08-FO-01\", \"type\": \"static_text\", \"label\": \"Código\", \"required\": false}, {\"id\": \"header_line_5\", \"text\": \"Fecha de Emisión: 27/03/2025\", \"type\": \"static_text\", \"label\": \"Fecha de emisión\", \"required\": false}, {\"id\": \"header_line_6\", \"text\": \"Número de Revisión: 03\", \"type\": \"static_text\", \"label\": \"Número de revisión\", \"required\": false}, {\"id\": \"taller\", \"type\": \"select\", \"label\": \"Taller\", \"options\": [\"Apaxco\", \"Aztecas\", \"Cedis Pachuca\", \"Cedis Pachuca Calidad/PTS\", \"Cedis Pachuca Tip Top\", \"Colima\", \"Huichapan\", \"Monterrey\", \"Morelos\", \"Orizaba\", \"Peñasquito\", \"San Luis Potosi\", \"Tamuin\", \"Tepeaca\", \"Torreon\", \"Vysisa Sureste (Merida)\", \"Xoxtla\", \"Zacatecas\"], \"required\": true}, {\"id\": \"nombre_inspector\", \"type\": \"text\", \"label\": \"Nombre del inspector\", \"required\": true}, {\"id\": \"firma_inspector\", \"type\": \"signature\", \"label\": \"Firma del inspector\", \"required\": false}, {\"id\": \"indicaciones_toggle\", \"text\": \"Indicaciones de llenado\", \"type\": \"static_text\", \"label\": \"Indicaciones de llenado\", \"required\": false}, {\"id\": \"indicaciones_line_1\", \"text\": \"Este formato debera llenarse una vez al mes\", \"type\": \"static_text\", \"label\": \"Indicacion 1\", \"required\": false}, {\"id\": \"indicaciones_line_2\", \"text\": \"Marque segun lo que aplique\", \"type\": \"static_text\", \"label\": \"Indicacion 2\", \"required\": false}, {\"id\": \"tabla_herramientas\", \"type\": \"table\", \"label\": \"Herramientas\", \"columns\": [\"Tipo de Herramienta\", \"# Serie\", \"Conexiones electricas (Cables, clavijas, extensiones)\", \"Interruptores (Gallitos)\", \"Condiciones fisicas (Carcaza y guarda de protección)\", \"Mango de sujeción\", \"Aditamientos (discos, brocas, puntas, etc.)\", \"Prueba de funcionamiento\", \"Acciones\", \"Observaciones\"], \"required\": false, \"row_schema\": [{\"id\": \"tipo_herramienta\", \"type\": \"radio\", \"label\": \"Tipo de Herramienta\", \"options\": [\"Desbrozadora\", \"Dremel Multimax MM40\", \"Esmeril de Banco 1/2 Hp\", \"Esmeriladora Angular\", \"Extensiones\", \"Maquina Dremel\", \"Multimetro de Gancho\", \"Pistola de Impacto\", \"Pulidora o Manipuladora** Alta, Baja\", \"Reflectores\", \"Sierra Circular 7 1/4\", \"Taladro\", \"Otro\"], \"required\": true}, {\"id\": \"serie\", \"type\": \"text\", \"label\": \"# Serie\", \"required\": true}, {\"id\": \"conexiones_electricas\", \"type\": \"radio\", \"label\": \"Conexiones electricas (Cables, clavijas, extensiones)\", \"options\": [\"Si\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"interruptores\", \"type\": \"radio\", \"label\": \"Interruptores (Gallitos)\", \"options\": [\"Si\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"condiciones_fisicas\", \"type\": \"radio\", \"label\": \"Condiciones fisicas (Carcaza y guarda de protección)\", \"options\": [\"Si\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"mango_sujecion\", \"type\": \"radio\", \"label\": \"Mango de sujecion\", \"options\": [\"Si\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"aditamientos\", \"type\": \"radio\", \"label\": \"Aditamientos (discos, brocas, puntas, etc.)\", \"options\": [\"Si\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"prueba_funcionamiento\", \"type\": \"radio\", \"label\": \"Prueba de funcionamiento\", \"options\": [\"Si\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"acciones\", \"type\": \"radio\", \"label\": \"Acciones\", \"options\": [\"La Herramienta esta en buenas condiciones\", \"La Herramienta se identifica como dañada\"], \"required\": true}, {\"id\": \"observaciones\", \"type\": \"text\", \"label\": \"Observaciones\", \"required\": false}]}], \"_code_key\": \"sst_pop_ta_08_fo_01_checklist_herramienta_electrica_portatil\"}','2026-03-31 18:09:12','2026-05-07 20:22:27'),(5,1,'SST-POP-TA-04-FO-04 Checklist Línea Retráctil y Puntos Fijos','BORRADOR','{\"meta\": {\"layout\": \"checklist_linea_retractil_y_puntos_fijos\"}, \"fields\": [{\"id\": \"encabezado_logo\", \"url\": \"/images/forms/Encabezado-vysisa.png\", \"type\": \"fixed_image\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_1\", \"text\": \"VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_2\", \"text\": \"SISTEMA DE GESTION INTEGRAL\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_3\", \"text\": \"Checklist Línea Retráctil y Puntos Fijos\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_4\", \"text\": \"Código: SST-POP-TA-04-FO-04\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_5\", \"text\": \"Fecha de Emisión: 27/03/2025\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_6\", \"text\": \"Número de Revisión: 01\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"taller\", \"type\": \"select\", \"label\": \"Taller\", \"options\": [\"Apaxco\", \"Aztecas\", \"Cedis Pachuca\", \"Cedis Pachuca Calidad/PTS\", \"Cedis Pachuca Tip Top\", \"Colima\", \"Huichapan\", \"Monterrey\", \"Morelos\", \"Orizaba\", \"Peñasquito\", \"San Luis Potosi\", \"Tamuin\", \"Tepeaca\", \"Torreon\", \"Vysisa Sureste (Merida)\", \"Xoxtla\", \"Zacatecas\"], \"required\": true}, {\"id\": \"nombre_inspector\", \"type\": \"text\", \"label\": \"Nombre del Inspector\", \"required\": true}, {\"id\": \"firma_inspector\", \"type\": \"signature\", \"label\": \"Firma del Inspector\", \"required\": true}, {\"id\": \"indicaciones_toggle\", \"text\": \"Indicaciones de Llenado\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"indicacion_1\", \"text\": \"Este checklist deberá llenarse cada que se use el equipo y en caso de no usarse llenarse una vez al mes.\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"indicacion_2\", \"text\": \"Considerar los siguientes criterios de acuerdo a las condiciones del equipo\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"criterios_titulo\", \"text\": \"Criterios a inspeccionar\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"tabla_linea_retractil\", \"type\": \"table\", \"label\": \"Criterios a inspeccionar\", \"columns\": [\"Imagen\", \"N° de Identificación\", \"Marca / Modelo del Arnés\", \"Condiciones Generales\", \"1. Mosquetón\", \"2. Gancho de Seguridad de Cierre Automático\", \"3. Conector de Punto Fijo / Punto de Anclaje Fijo\", \"Acciones\", \"Observaciones\"], \"required\": true, \"row_schema\": [{\"id\": \"imagen_linea\", \"type\": \"fixed_image\", \"label\": \"Imagen\", \"required\": false}, {\"id\": \"numero_identificacion\", \"type\": \"text\", \"label\": \"N° de Identificación\", \"required\": true}, {\"id\": \"marca_modelo\", \"type\": \"text\", \"label\": \"Marca / Modelo del Arnés\", \"required\": true}, {\"id\": \"condiciones_generales\", \"type\": \"static_text\", \"label\": \"Condiciones Generales\", \"required\": false}, {\"id\": \"manija_anclaje\", \"type\": \"radio\", \"label\": \"Manija de Anclaje\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"carcaza_termoplastica\", \"type\": \"radio\", \"label\": \"Carcaza Termoplástica\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"linea_vida_acero_textil\", \"type\": \"radio\", \"label\": \"Línea de Vida Acero Galvanizado o Textil\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"activacion_sistema_bloqueo\", \"type\": \"radio\", \"label\": \"Activación de Sistema de Bloqueo\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"mosqueton_titulo\", \"type\": \"static_text\", \"label\": \"1. MOSQUETÓN\", \"required\": false}, {\"id\": \"mosqueton_1_1\", \"type\": \"radio\", \"label\": \"1.1. Desgaste, Deformaciones\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"mosqueton_1_2\", \"type\": \"radio\", \"label\": \"1.2. Picaduras, Grietas\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"mosqueton_1_3\", \"type\": \"radio\", \"label\": \"1.3. Corrosión\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"gancho_titulo\", \"type\": \"static_text\", \"label\": \"2. GANCHO DE SEGURIDAD DE CIERRE AUTOMATICO\", \"required\": false}, {\"id\": \"gancho_2_1\", \"type\": \"radio\", \"label\": \"2.1. Desgaste, Deformaciones\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"gancho_2_2\", \"type\": \"radio\", \"label\": \"2.2. Picadura, Grietas\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"gancho_2_3\", \"type\": \"radio\", \"label\": \"2.3. Ajuste Inadecuado o Incorrecto de los Cierres de Seguridad (Enganches)\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"gancho_2_4\", \"type\": \"radio\", \"label\": \"2.4. Corrosión\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"conector_titulo\", \"type\": \"static_text\", \"label\": \"3. CONECTOR DE PUNTO FIJO/PUNTO DE ANCLAJE FIJO\", \"required\": false}, {\"id\": \"conector_3_1\", \"type\": \"radio\", \"label\": \"3.1. Forro del Cable se Encuentra Desgastado\", \"options\": [\"Sí\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"conector_3_2\", \"type\": \"radio\", \"label\": \"3.2. Cuerpo de línea Presencia de Daño\", \"options\": [\"Sí\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"conector_3_3\", \"type\": \"radio\", \"label\": \"3.3. Costuras Rotas o Dañadas\", \"options\": [\"Sí\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"conector_3_4\", \"type\": \"radio\", \"label\": \"3.4. Argollas o Deformaciones\", \"options\": [\"Sí\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"conector_3_5\", \"type\": \"radio\", \"label\": \"3.5. Presencia de Aceites, Grasas o Químicos\", \"options\": [\"Sí\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"acciones\", \"type\": \"radio\", \"label\": \"Acciones\", \"options\": [\"El Equipo se Marca como Dañado y es Sacado de Uso\", \"El Equipo está en Buenas Condiciones\"], \"required\": true}, {\"id\": \"observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": false}]}], \"_code_key\": \"sst_pop_ta_04_fo_04_checklist_linea_retractil_y_puntos_fijos\"}','2026-04-15 20:11:38','2026-05-07 20:22:27'),(6,1,'SST-POP-TA-04-FO-03 Inspección de Línea de Vida','BORRADOR','{\"meta\": {\"layout\": \"inspeccion_de_linea_de_vida\"}, \"fields\": [{\"id\": \"encabezado_logo\", \"url\": \"/images/forms/Encabezado-vysisa.png\", \"type\": \"fixed_image\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_1\", \"text\": \"VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_2\", \"text\": \"SISTEMA DE GESTION INTEGRAL\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_3\", \"text\": \"INSPECCIÓN DE LÍNEA DE VIDA\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_4\", \"text\": \"Código: SST-POP-TA-04-FO-03\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_5\", \"text\": \"Fecha de Emisión: 27/03/2025\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_6\", \"text\": \"Número de Revisión: 03\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"taller\", \"type\": \"select\", \"label\": \"Taller\", \"options\": [\"Apaxco\", \"Aztecas\", \"Cedis Pachuca\", \"Cedis Pachuca Calidad/PTS\", \"Cedis Pachuca Tip Top\", \"Colima\", \"Huichapan\", \"Monterrey\", \"Morelos\", \"Orizaba\", \"Peñasquito\", \"San Luis Potosi\", \"Tamuin\", \"Tepeaca\", \"Torreon\", \"Vysisa Sureste (Merida)\", \"Xoxtla\", \"Zacatecas\"], \"required\": true}, {\"id\": \"nombre_inspector\", \"type\": \"text\", \"label\": \"Nombre del Inspector\", \"required\": true}, {\"id\": \"firma_inspector\", \"type\": \"signature\", \"label\": \"Firma del Inspector\", \"required\": true}, {\"id\": \"indicaciones_toggle\", \"text\": \"Indicaciones de Llenado\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"indicacion_1\", \"text\": \"Este checklist deberá llenarse cada que se use línea de vida y en caso de no usarse llenarse una vez al mes.\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"indicacion_2\", \"text\": \"Considerar los siguientes criterios de acuerdo a las condiciones de la línea de vida.\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"criterios_titulo\", \"text\": \"Criterios a inspeccionar\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"tabla_linea_vida\", \"type\": \"table\", \"label\": \"Criterios a inspeccionar\", \"columns\": [\"Número de Línea de Vida\", \"1. Línea de Vida\", \"2. Amortiguador\", \"3. Ganchos\", \"Etiqueta\", \"Acciones\", \"Observaciones\"], \"required\": true, \"row_schema\": [{\"id\": \"numero_linea_vida\", \"type\": \"text\", \"label\": \"Número de Línea de Vida\", \"required\": true}, {\"id\": \"linea_vida_titulo\", \"type\": \"static_text\", \"label\": \"1. Línea de Vida\", \"required\": false}, {\"id\": \"linea_vida_1_1\", \"type\": \"radio\", \"label\": \"1.1. Costuras (Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas)\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"linea_vida_1_2\", \"type\": \"radio\", \"label\": \"1.2. Terminación (Cortada, Quemada, Agujerada, Deshilachada, Decolorada, Empalmada)\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"linea_vida_1_3\", \"type\": \"radio\", \"label\": \"1.3. Cuerpo de la Línea de Vida (Cortado, Quemado, Agujerado, Deshilachado, Decolorado, Empalmado)\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"amortiguador_titulo\", \"type\": \"static_text\", \"label\": \"2. Amortiguador\", \"required\": false}, {\"id\": \"amortiguador_2_1\", \"type\": \"radio\", \"label\": \"2.1. Daño en Cubierta\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"amortiguador_2_2\", \"type\": \"radio\", \"label\": \"2.2. Deformación\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"amortiguador_2_3\", \"type\": \"radio\", \"label\": \"2.3. Señales de Activación\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"ganchos_titulo\", \"type\": \"static_text\", \"label\": \"3. Ganchos\", \"required\": false}, {\"id\": \"ganchos_3_1\", \"type\": \"radio\", \"label\": \"3.1. Desgaste Excesivo, Deformaciones\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"ganchos_3_2\", \"type\": \"radio\", \"label\": \"3.2. Picaduras, Grietas\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"ganchos_3_3\", \"type\": \"radio\", \"label\": \"3.3. Resorte con Fallas\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"ganchos_3_4\", \"type\": \"radio\", \"label\": \"3.4. Función de Bloqueo de Conector\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"ganchos_3_5\", \"type\": \"radio\", \"label\": \"3.5. Corrosión\", \"options\": [\"(✓) Buen Estado\", \"(X) Mal Estado\", \"(NA) No Aplica\"], \"required\": true}, {\"id\": \"etiqueta_titulo\", \"type\": \"static_text\", \"label\": \"Etiqueta\", \"required\": false}, {\"id\": \"etiqueta_faltante\", \"type\": \"radio\", \"label\": \"Faltante\", \"options\": [\"Sí\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"etiqueta_legible\", \"type\": \"radio\", \"label\": \"Legible\", \"options\": [\"Sí\", \"No\", \"NA\"], \"required\": true}, {\"id\": \"acciones\", \"type\": \"radio\", \"label\": \"Acciones\", \"options\": [\"La Línea de Vida se Marca como Dañada y es Sacado de Uso\", \"La Línea de Vida está en Buenas Condiciones\"], \"required\": true}, {\"id\": \"observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": false}]}], \"_code_key\": \"sst_pop_ta_04_fo_03_inspeccion_de_linea_de_vida\"}','2026-05-07 17:14:47','2026-05-07 20:22:27'),(7,1,'SST-POP-TA-04-FO-02 Inspección de Arnés de Seguridad','BORRADOR','{\"meta\": {\"layout\": \"inspeccion_de_arnes_de_seguridad\"}, \"fields\": [{\"id\": \"encabezado_logo\", \"url\": \"/images/forms/Encabezado-vysisa.png\", \"type\": \"fixed_image\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_1\", \"text\": \"VULCANIZACIÓN Y SERVICIOS INDUSTRIALES S.A. DE C.V.\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_2\", \"text\": \"SISTEMA DE GESTION INTEGRAL\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_3\", \"text\": \"INSPECCIÓN DE ARNÉS DE SEGURIDAD\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_4\", \"text\": \"Código: SST-POP-TA-04-FO-02\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_5\", \"text\": \"Fecha de Emisión: 27/03/2025\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"header_line_6\", \"text\": \"Número de Revisión: 04\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"taller\", \"type\": \"select\", \"label\": \"Taller\", \"options\": [\"Apaxco\", \"Aztecas\", \"Cedis Pachuca\", \"Cedis Pachuca Calidad/PTS\", \"Cedis Pachuca Tip Top\", \"Colima\", \"Huichapan\", \"Monterrey\", \"Morelos\", \"Orizaba\", \"Peñasquito\", \"San Luis Potosi\", \"Tamuin\", \"Tepeaca\", \"Torreon\", \"Vysisa Sureste (Merida)\", \"Xoxtla\", \"Zacatecas\"], \"required\": true}, {\"id\": \"nombre_responsable_inspeccion\", \"type\": \"text\", \"label\": \"Nombre del Responsable de Inspección\", \"required\": true}, {\"id\": \"firma_responsable_inspeccion\", \"type\": \"signature\", \"label\": \"Firma del Responsable de Inspección\", \"required\": true}, {\"id\": \"indicaciones_toggle\", \"text\": \"Indicaciones de Llenado\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"indicacion_1\", \"text\": \"Considerar los siguientes criterios de acuerdo a la Inspección de Arnés.\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"criterios_titulo\", \"text\": \"Criterios a Inspeccionar\", \"type\": \"static_text\", \"label\": \"\", \"required\": false}, {\"id\": \"tabla_arnes_seguridad\", \"type\": \"table\", \"label\": \"Criterios a Inspeccionar\", \"columns\": [\"Número de Arnés\", \"Correas y Costuras\", \"D-Ring\", \"Hebillas\", \"Etiqueta\", \"Acciones\", \"Observaciones\"], \"required\": true, \"row_schema\": [{\"id\": \"numero_arnes\", \"type\": \"text\", \"label\": \"Número de Arnés\", \"required\": true}, {\"id\": \"correas_costuras_titulo\", \"type\": \"static_text\", \"label\": \"CORREAS Y COSTURAS\", \"required\": false}, {\"id\": \"correas_1_hombros\", \"type\": \"radio\", \"label\": \"1. De Hombros: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"correas_2_pecho\", \"type\": \"radio\", \"label\": \"2. Del Pecho: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"correas_3_espalda\", \"type\": \"radio\", \"label\": \"3. De Espalda: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"correas_4_piernas\", \"type\": \"radio\", \"label\": \"4. De Piernas: Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"correas_5_cintura\", \"type\": \"radio\", \"label\": \"5. De Cintura (Sí Aplica): Cortadas, Quemadas, Agujeradas, Deshilachadas, Decoloradas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"d_ring_titulo\", \"type\": \"static_text\", \"label\": \"D-RING\", \"required\": false}, {\"id\": \"d_ring_6_dorsal\", \"type\": \"radio\", \"label\": \"6. Dorsal: Gastados, Oxidados\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"d_ring_7_cintura\", \"type\": \"radio\", \"label\": \"7. De Cintura (Sí Aplica): Gastados, Oxidados\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"d_ring_8_esternon\", \"type\": \"radio\", \"label\": \"8. De Esternón (Sí Aplica): Gastados, Oxidados\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"hebillas_titulo\", \"type\": \"static_text\", \"label\": \"HEBILLAS\", \"required\": false}, {\"id\": \"hebillas_9_ajuste_hombros\", \"type\": \"radio\", \"label\": \"9. Ajuste en Hombros: Flojas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"hebillas_10_pecho_espalda\", \"type\": \"radio\", \"label\": \"10. Pecho y Espalda: Flojas, Oxidadas, Gastadas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"hebillas_11_mosqueton_pecho\", \"type\": \"radio\", \"label\": \"11. Mosquetón de Pecho (Sí Aplica): Flojas, Oxidadas, Gastadas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"hebillas_12_ajuste_piernas\", \"type\": \"radio\", \"label\": \"12. Ajuste en Piernas: Flojas\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"etiqueta_titulo\", \"type\": \"static_text\", \"label\": \"ETIQUETA\", \"required\": false}, {\"id\": \"etiqueta_faltante\", \"type\": \"radio\", \"label\": \"Faltante\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"etiqueta_legible\", \"type\": \"radio\", \"label\": \"Legible\", \"options\": [\"SI\", \"NO\", \"NA\"], \"required\": true}, {\"id\": \"acciones\", \"type\": \"radio\", \"label\": \"Acciones\", \"options\": [\"El Arnés se marca como dañado y es sacado de uso\", \"El Arnés está en buenas condiciones\"], \"required\": true}, {\"id\": \"observaciones\", \"type\": \"textarea\", \"label\": \"Observaciones\", \"required\": true}]}], \"_code_key\": \"sst_pop_ta_04_fo_02_inspeccion_de_arnes_de_seguridad\"}','2026-05-07 19:37:06','2026-05-07 20:22:27');
/*!40000 ALTER TABLE `forms` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo_histories`
--

DROP TABLE IF EXISTS `grupo_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `grupo_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `grupo_histories_grupo_id_created_at_index` (`grupo_id`,`created_at`),
  KEY `grupo_histories_user_id_index` (`user_id`),
  KEY `grupo_histories_action_index` (`action`),
  CONSTRAINT `grupo_histories_grupo_id_foreign` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `grupo_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_histories`
--

LOCK TABLES `grupo_histories` WRITE;
/*!40000 ALTER TABLE `grupo_histories` DISABLE KEYS */;
INSERT INTO `grupo_histories` VALUES (1,1,1,'created','{\"estado\": \"Activo\", \"nombre\": \"Sistemas\", \"descripcion\": \"Sistemas\", \"nombre_mostrar\": \"sistemas\"}',NULL,'2026-03-31 18:09:38','2026-03-31 18:09:38');
/*!40000 ALTER TABLE `grupo_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupo_user`
--

DROP TABLE IF EXISTS `grupo_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupo_user` (
  `grupo_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`grupo_id`,`user_id`),
  KEY `grupo_user_user_id_foreign` (`user_id`),
  CONSTRAINT `grupo_user_grupo_id_foreign` FOREIGN KEY (`grupo_id`) REFERENCES `grupos` (`id`) ON DELETE CASCADE,
  CONSTRAINT `grupo_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupo_user`
--

LOCK TABLES `grupo_user` WRITE;
/*!40000 ALTER TABLE `grupo_user` DISABLE KEYS */;
INSERT INTO `grupo_user` VALUES (1,1,'2026-03-31 18:17:16','2026-03-31 18:17:16'),(1,2,'2026-03-31 18:11:09','2026-03-31 18:11:09'),(1,3,'2026-03-31 18:21:17','2026-03-31 18:21:17');
/*!40000 ALTER TABLE `grupo_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `grupos`
--

DROP TABLE IF EXISTS `grupos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `grupos` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nombre_mostrar` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `grupos`
--

LOCK TABLES `grupos` WRITE;
/*!40000 ALTER TABLE `grupos` DISABLE KEYS */;
INSERT INTO `grupos` VALUES (1,'Sistemas','sistemas','Sistemas',1,'2026-03-31 18:09:38','2026-03-31 18:09:38');
/*!40000 ALTER TABLE `grupos` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (1,'0001_01_01_000000_create_users_table',1),(2,'0001_01_01_000001_create_cache_table',1),(3,'0001_01_01_000002_create_jobs_table',1),(4,'2026_03_02_220306_create_permission_tables',1),(5,'2026_03_02_220306_create_personal_access_tokens_table',1),(6,'2026_03_04_000001_create_forms_table',1),(7,'2026_03_04_000002_create_form_submissions_table',1),(8,'2026_03_04_000003_create_empresas_table',1),(9,'2026_03_04_000004_create_grupos_table',1),(10,'2026_03_04_000005_add_activo_to_users_table',1),(11,'2026_03_04_000006_create_empresa_user_table',1),(12,'2026_03_04_000007_create_grupo_user_table',1),(13,'2026_03_17_000001_add_nombre_mostrar_and_descripcion_to_roles_table',1),(14,'2026_03_17_000002_create_unidades_servicio_table',1),(15,'2026_03_17_000003_create_unidad_servicio_user_table',1),(16,'2026_03_17_000004_create_form_user_table',1),(17,'2026_03_18_000001_create_form_submission_histories_table',1),(18,'2026_03_20_173833_add_consecutive_to_form_submissions_table',1),(19,'2026_03_24_000001_create_user_histories_table',1),(20,'2026_03_24_000002_create_role_histories_table',1),(21,'2026_03_24_000003_create_permission_histories_table',1),(22,'2026_03_24_000004_create_unidad_servicio_histories_table',1),(23,'2026_03_24_000005_create_empresa_histories_table',1),(24,'2026_03_24_000006_create_grupo_histories_table',1),(25,'2026_03_24_000007_create_form_histories_table',1);
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
  `model_id` bigint unsigned NOT NULL,
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
  `model_id` bigint unsigned NOT NULL,
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
INSERT INTO `model_has_roles` VALUES (1,'App\\Models\\User',1),(2,'App\\Models\\User',2),(2,'App\\Models\\User',3);
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
-- Table structure for table `permission_histories`
--

DROP TABLE IF EXISTS `permission_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permission_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `permission_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `permission_histories_permission_id_created_at_index` (`permission_id`,`created_at`),
  KEY `permission_histories_user_id_index` (`user_id`),
  KEY `permission_histories_action_index` (`action`),
  CONSTRAINT `permission_histories_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permission_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permission_histories`
--

LOCK TABLES `permission_histories` WRITE;
/*!40000 ALTER TABLE `permission_histories` DISABLE KEYS */;
/*!40000 ALTER TABLE `permission_histories` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `permissions`
--

LOCK TABLES `permissions` WRITE;
/*!40000 ALTER TABLE `permissions` DISABLE KEYS */;
INSERT INTO `permissions` VALUES (1,'formularios.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(2,'formularios.create','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(3,'formularios.edit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(4,'formularios.delete','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(5,'formularios.submit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(6,'formularios.admin.publish','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(7,'formularios.submissions.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(8,'admin.panel.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(9,'formularios.admin.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(10,'formularios.admin.assign','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(11,'usuarios.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(12,'usuarios.create','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(13,'usuarios.edit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(14,'usuarios.delete','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(15,'roles.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(16,'roles.create','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(17,'roles.edit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(18,'roles.delete','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(19,'permisos.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(20,'permisos.create','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(21,'permisos.edit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(22,'permisos.delete','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(23,'empresas.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(24,'empresas.create','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(25,'empresas.edit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(26,'empresas.delete','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(27,'grupos.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(28,'grupos.create','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(29,'grupos.edit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(30,'grupos.delete','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(31,'unidades_servicio.view','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(32,'unidades_servicio.create','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(33,'unidades_servicio.edit','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(34,'unidades_servicio.delete','sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30');
/*!40000 ALTER TABLE `permissions` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
INSERT INTO `personal_access_tokens` VALUES (41,'App\\Models\\User',1,'pwa','53aa5c2b8ff617eaef738128398dfabce0f62129f669062a508c778e82b5b269','[\"*\"]','2026-05-07 20:22:27',NULL,'2026-04-08 21:36:15','2026-05-07 20:22:27'),(51,'App\\Models\\User',3,'pwa','392b15ebb27a2308b7a526bac4047dc05ecb5397ae2dd970b02f1ecb53ffe062','[\"*\"]','2026-04-28 15:18:40',NULL,'2026-04-21 20:33:27','2026-04-28 15:18:40'),(54,'App\\Models\\User',2,'pwa','2fbcf1b60acd6f51f8a9f3e6ac4790976d783f491c3db5aa52e0c849a6cbc1b7','[\"*\"]','2026-04-29 23:36:19',NULL,'2026-04-29 23:34:44','2026-04-29 23:36:19');
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
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
INSERT INTO `role_has_permissions` VALUES (1,1),(2,1),(3,1),(4,1),(5,1),(6,1),(7,1),(8,1),(9,1),(10,1),(11,1),(12,1),(13,1),(14,1),(15,1),(16,1),(17,1),(18,1),(19,1),(20,1),(21,1),(22,1),(23,1),(24,1),(25,1),(26,1),(27,1),(28,1),(29,1),(30,1),(31,1),(32,1),(33,1),(34,1),(1,2),(2,2),(5,2),(7,2);
/*!40000 ALTER TABLE `role_has_permissions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `role_histories`
--

DROP TABLE IF EXISTS `role_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `role_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `role_histories_role_id_created_at_index` (`role_id`,`created_at`),
  KEY `role_histories_user_id_index` (`user_id`),
  KEY `role_histories_action_index` (`action`),
  CONSTRAINT `role_histories_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `role_histories`
--

LOCK TABLES `role_histories` WRITE;
/*!40000 ALTER TABLE `role_histories` DISABLE KEYS */;
INSERT INTO `role_histories` VALUES (1,2,1,'created','{\"name\": \"Usuario\", \"descripcion\": \"Usuario Prueba\", \"permissions\": [\"formularios.create\", \"formularios.submissions.view\", \"formularios.submit\", \"formularios.view\"], \"nombre_mostrar\": \"User\"}',NULL,'2026-03-31 18:10:42','2026-03-31 18:10:42');
/*!40000 ALTER TABLE `role_histories` ENABLE KEYS */;
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
  `nombre_mostrar` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
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
INSERT INTO `roles` VALUES (1,'Administrador',NULL,NULL,'sanctum','2026-03-31 17:55:30','2026-03-31 17:55:30'),(2,'Usuario','User','Usuario Prueba','sanctum','2026-03-31 18:10:42','2026-03-31 18:10:42');
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
INSERT INTO `sessions` VALUES ('0tfWdN9Q3iCtDOUawAThkaS3dNiNUx6517NzJcha',NULL,'124.117.193.38','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMWQwVUdTRWJjd1d2MXZKUlFocGlZVzk2Nm4xTnZ6aUxUa2Y4d1MwaSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778603056),('2nN5iE05YujGISp13fmCI1N89DGVlJnE7xBx1YKb',NULL,'46.138.250.165','','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRGZPdVlna3pndlNmeFVwWnVTcXdwRERTRGQydGxxeEIxcFB4eG51TiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778723944),('4FdlqB9XBapqX3rVNw2vrIQptWdv5PCb57GR5YH4',NULL,'52.53.222.68','Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieHRlQ2tjRUtyUHRzckE2YW1QQWw3bVRyU1AzQzUxdHBwZVFVRUhQMSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778644183),('5179PlTpuAa67q2GGPg6AvtzKO7ptJU7oUznxJTD',NULL,'66.249.72.37','Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.7727.137 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ1B1MTJPNG4yUlNqU1JqOWlLTDBoejdUVmRtMFlrRnU3S2ZtRU1BViI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778578487),('5pf3AWkkVpQOA14VG7JqeAaONEkqEvUHqLvbCaqU',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSmRzc0p3cHFYMlo4RlZsdjFZOTRLR09wZXJUeWJ6OWNpUE9yY044eCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTA6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20vc3JjL2F3cy1leHBvcnRzLmpzIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778620974),('8jkEDQvT4jHK0OnOUSzXjQb1UPNsQv0Y05xxapuY',NULL,'2607:8500:faca:de::119','Mozilla/5.0 (compatible; Nmap Scripting Engine; https://nmap.org/book/nse.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUGI4TFpISERkYjFMc2pLYUlVZW4wNjI2WEZzQWRBaTJvaTFxWjBOciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTg6Imh0dHBzOi8vc3J2MTU0NjIyMS5oc3Rnci5jbG91ZC9ObWFwL2ZvbGRlci9jaGVjazE3Nzg3MTc5NzUiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778717976),('8w979X1hXx356Uav8AkXXcoPYffzwA4TgPmUHLot',NULL,'2607:8500:faca:de::119','Mozilla/5.0 (compatible; Nmap Scripting Engine; https://nmap.org/book/nse.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQW1lSHpKTnYyWUNzWmRER3ZlRmdmQVBIc1hsSUVVV0w5VlEyMGZaYyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHBzOi8vc3J2MTU0NjIyMS5oc3Rnci5jbG91ZC9ubWFwbG93ZXJjaGVjazE3Nzg3MTc5NzUiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778717975),('9yEsxOfSLWOsteMbR0vpZ3CIZnTIXhlFNbMS4RvH',NULL,'165.245.231.9','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGlkU0Y4NGlrSG9xODlFR1h6aEluZm1tSVlMMUhrcW5mYnV2VVhwMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778769867),('A71RjW1TJPOlSigZMZmELAANN3sJ6R9euNNHm3zt',NULL,'165.22.104.209','Mozilla/5.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ0d4SnlLU2E3NWZUQTIxR0ExYXZmOFVjVHlPZ0c2bEpNTmpvNTdnbSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778605636),('aD6PpHwtJSxXmJkVLKXba1PqKxhufpdgehq12CnL',NULL,'204.101.161.15','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoielhxTjNiSGh1Wkg4aWFKcThLSkJiYWFPSHJkdHREM3Y1bklhOFdDNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778654157),('aiTu1IasxUu6CeaF1e23J2W48iIFlpEDV0Mt4aKx',NULL,'43.156.66.8','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUnh6RlpyaHRQZHNwNzdxdHpkaEFwZUYydkpJelFnQW9Cem5pOFIxUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778587186),('ajDJnzmfJTKyE0qyKNvBFq33aRkAurYBCzDJnBmp',NULL,'46.138.250.165','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 AISearchIndex.space','YTozOntzOjY6Il90b2tlbiI7czo0MDoieHdyRTd5Y3g3WW83YXpyemtZUndRZzBLSlRiS25jb0Zsd1lnbUQ5WSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778723944),('aQaK761AgKQRLjrzbxTyoVbY8vQhZxx2D41ddOfF',NULL,'143.198.35.3','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiYWNhRXNrNmJYZ3RuQjZoVFYyS1NVeW9IaE1WNkc1ZkhNd3FoRG0wWSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzc6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwL3NlcnZlci1zdGF0dXMiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778768013),('aZx9aRqc2B4nhQ3M9NDcHbeXiYPbREQJICUnR6H3',NULL,'191.96.106.25','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoidXBCNGFMYjVVYWxqck1HQmpmVDU1dWhJazltQUtZRzlnWEdhc1FZVyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778573209),('b8PwzhRJNJSA5pr1dzNOe2CDqNMC464IOk82VxPL',NULL,'2607:8500:faca:de::119','','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTDhOaTg0eEFlMEY4c25kOWkyUmNBWjZYRFFSS3o0MllXSWQ0UzJhSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778717976),('BmYIeMEpBdlMms6NY3TUhWn04NSgbiVTQRi4PptC',NULL,'170.106.74.215','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVW1tZmpUc0ZMM2N4SHJDVEpRYWJJV2w5TjJrME9XWDVLS2podlYzcyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTE6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwL2luZGV4LnBocD9sYW5nPS4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkZ0bXAlMkZpbmRleDEiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778771223),('cWkDJDI6LV7uxnjg4oiSyK9FzhU4BhWEurGmvJLy',NULL,'2607:8500:faca:de::119','','YTozOntzOjY6Il90b2tlbiI7czo0MDoiOGk1NXBSbGY5Z2dlNEV1andkUVM2aVQyZ2x5WjNadm9aNEFKbjladyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzA6Imh0dHBzOi8vc3J2MTU0NjIyMS5oc3Rnci5jbG91ZCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778717977),('dkWhk3R2e3yFnd1xh3buB9ETwEMdmb0PLvpccSr1',NULL,'62.171.162.232','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRXc0c0xKVkV6WVRYMHNNNDE4YTR3RTB2TjF2ZU5NRE0yUkNad0NiNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTQ5OiJodHRwczovLzE4Ny4xMjQuMTE4LjIxMC9pbmRleC5waHA/ZnVuY3Rpb249Y2FsbF91c2VyX2Z1bmNfYXJyYXkmcz0lMkZpbmRleCUyRiU1Q3RoaW5rJTVDYXBwJTJGaW52b2tlZnVuY3Rpb24mdmFycyU1QjAlNUQ9bWQ1JnZhcnMlNUIxJTVEJTVCMCU1RD1IZWxsbyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778750029),('ewDKnlUMmHgjxLVPwwbUUEtNsuZMPqn0VjmLzhsL',NULL,'2607:8500:faca:de::119','Mozilla/5.0 (compatible; Nmap Scripting Engine; https://nmap.org/book/nse.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSGlMZUtIZEp6ek9KeklCY1FWb0tzRFB1bVg3T3FtUHdveWtSbEYzUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTU6Imh0dHBzOi8vc3J2MTU0NjIyMS5oc3Rnci5jbG91ZC9ObWFwVXBwZXJDaGVjazE3Nzg3MTc5NzUiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778717975),('FauDDew61qEicdbAFjzzxiCucS4oOD2bUy8qYdUg',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWGRjVDd1VHkwSUs1UGJnYU5JOHBSNVo4T0huWVZxR3ZIR0xTVHlLTyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NTI6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20vYXdzX2NyZWRlbnRpYWxzLmpzb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778620971),('FlvobIp3J9ohj0qAVeYGTd4xpYSlaUazcf0nEErT',NULL,'123.138.79.105','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiamRqYXdhVmpmbWp3SGpKMk5DNURaSXQ3ZFQ3MlNFZEpWZGR0Ynp0MyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778603271),('G1i9TtPu3JXNQLr9qUyrrfvu7oKWerwCA3tqpxei',NULL,'62.171.162.232','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRHhDdlpraEF0ZWV2QU9IdUdHeUFvR1FLM3JEMnBQT2J5Q3Y5UVBHRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6OTE6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwL2luZGV4LnBocD9sYW5nPS4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkZ0bXAlMkZpbmRleDEiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778750030),('gEIElEldrd2LpXEh2fLgonB7Mn4ADsBPf57qY4Cv',NULL,'62.171.162.232','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiak1DQnU0b2dGY2hiblJJd3dkZWRjUnkza3hxRDVXM011OGxKMVE2ayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwL2NvbnRhaW5lcnMvanNvbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778750030),('GHfNVzO4jcsyk0DTXT2R1tQR24B99PwAmuG0G95e',NULL,'4.43.184.113','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/44.0.2403.157 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoieE16aGpJY2ExZ3NTNU1xY3Q3YlpJRVVqRm1obFdDeWRZTTRYaFpsYSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778739041),('gLdTCXmjFNJ4h7rLCDD4IqPAIpo6wtoYrVCgNrTT',NULL,'172.236.122.62','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWHhoYnVvTVJ5S1FKVTBoZEVvaW1uT0tFdWJLcjRqMzNzdEw4TFRKUSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778660653),('hE0memB7FiaJz0J3mO1hdFhfQq1ZDggJqMm21vim',NULL,'20.98.104.241','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.71 Safari/534.24','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM3NyVXhaWFlFNWNkVkpHRXE4V0hYTWx6cTdzYTBxakVuVEJNQ1hzRSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778623917),('HqMo5tLo1ilxSxFU4v28Ke3CTqmuUnVcWlvQXoE4',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2wyWWRnRTZaQVdLcjh1Y2ExdDd0MGJlQUcxekZkNWVGYmlhMVVzeSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20vYXdzLmNvbmZpZy5qcyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778620973),('HYZ1ECbd85DdSOvNxZG9ZtWc49sJ4LIAz8fEHEWe',NULL,'220.197.78.172','','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS1RBQ0xZUm9PTjV3YTF3WHQ2enV2V3NXeHBwSmlSWHpWUWxINUFqSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778602871),('i9wUUfrfu00e8ImNKEifWrykXygWilaXrbTQ97Ec',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiY0VBNTZVaWdoTEE1ODJabExXakVyY2g0TnlFOTJLV2xPekdSQWQ5VCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDA6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20vYXdzLmpzb24iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778620974),('jGiwPAG1znojRDB8HRWshC8irxloNrjCBTPIjRgf',NULL,'46.228.199.158','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.114 Safari/537.36 Edg/91.0.864.54','YTozOntzOjY6Il90b2tlbiI7czo0MDoiU0ZrZW42U2xoRmlTd0pJVjBHT0ZVZVQyTUtwVkJMUjR5OGJqcTZwOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778569878),('jXwzhIZUdWF9cp583iw2vio98JJ0ZrJJUNLN9n40',NULL,'205.210.31.42','Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity','YTozOntzOjY6Il90b2tlbiI7czo0MDoia3dhdlpieXJKVzB3Vm1Ia25Dd3BRWFlQbndROGtVWFdJZ0piRncyMyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778753981),('jZ2PXGQR7DfTYD9bm9fv4X184biPo63ILaARGhUE',NULL,'192.178.6.4','Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.7727.137 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiR0l3c1JZc2VjaXlueFdOU1AzZkdQa3lEWFpHOTZnd043M2t3REJyWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778626053),('kwSQlKAtkZHMLKRe71TOIDafGOYCjYT4rfG7NyHb',NULL,'62.171.162.232','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSXh2dWc1OUQ3V1p4eGtjaE5tSXRKdFBtd1cxS2ZCUWFFNGxJbUNuayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjAyOiJodHRwczovLzE4Ny4xMjQuMTE4LjIxMC9pbmRleC5waHA/JTJGJTNDJTNGZWNobyUyOG1kNSUyOCUyMmhpJTIyJTI5JTI5JTNCJTNGJTNFJTIwJTJGdG1wJTJGaW5kZXgxLnBocD0mY29uZmlnLWNyZWF0ZSUyMCUyRj0mbGFuZz0uLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGdXNyJTJGbG9jYWwlMkZsaWIlMkZwaHAlMkZwZWFyY21kIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778750030),('L3lTKsHnD94123HJ1FFkJfe0ujxnY0ORMDvpCrxV',NULL,'45.67.217.1','Go-http-client/1.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWkxZMmxZaHlTYk00WjFiZzdvbFByWHpBU0hBc2JjNkQzd0lzb0J1UiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778685205),('l3XaW3RYzzxKLylFEif8uC3HIOky9HjpbbAdGlKa',NULL,'198.244.183.8','Mozilla/5.0 (compatible; AhrefsBot/7.0; +http://ahrefs.com/robot/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT1pOZTlZYm0wVVYzcVBLUHZoRkZGc2VPUEZ4U0tCZW50OW44eE1LWCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778762379),('l4G3xfTJBh9tuO9wGBEUyBCL9CewWXsBE8ECXrju',NULL,'184.33.238.56','Mozilla/5.0 (compatible; wpbot/1.4; +https://forms.gle/ajBaxygz9jSR8p8G9)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSkMxOUc4OGFUanhVSWlRWHZXOUZjUEwxRlZialJ3cW1XaEF0UW9uZCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778618683),('l57LzkvmmsOpoEIFUb3Dfr8P83oDpFWlXsBUjEry',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUW1CNmwzMkZMdUNNeGRZSlp0MWZYUUQ4WkZlUnozV0l3aldmYWNaVyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDc6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20vYXdzLWNyZWRlbnRpYWxzIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778620974),('LDYfyxAxfIJbRdmMfw3FBXJACXaGUBo0G0e6wHlZ',NULL,'211.188.49.157','Go-http-client/1.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiblRhTXdDYnhoQ3FXYmhQMWtNVlE3QXVHMDU2S2dNNTlOaWpINkVkbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778644663),('M7eCYkhdYsHYqUlhLku6Vi6MLrVT8gd15hWE6I14',NULL,'144.123.77.90','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWFFtVDZCSXJ6aHIzUXliWXFtakxXelkwSW1tV0M3ZmY4d3pzUE52UyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778602952),('MRQDqckvbk3aUPlv8xH364FnwOzSS8p6slUBCDsG',NULL,'120.36.16.122','Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiT1VoQjZMMjdlUFhyVnB4UzVzVkFQYjJGZHNnVHpHeDlEdmtDdHFCSiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778603171),('Nb0Q0BK0PTXCHAM86o20CPUdjaF0UQoVYKwPHxae',NULL,'46.138.250.165','','YTozOntzOjY6Il90b2tlbiI7czo0MDoidFcyS2NpUE4zZkJrRWNkejJQT3VtRk1yTUM4WFpRTmdpUE9sNXUzbyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778645001),('nhNhlRV6J5LVGoGJsRe6ameaEvhV4DEfLNHCLcVu',NULL,'54.226.159.49','Mozilla/5.0 (Windows; U; Windows NT 6.0; en-GB; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.11 (.NET CLR 3.5.30729)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQ2x4Q3pVTEY2SDJvTzZDNVFseXZ3MGV2Q0R6V3dWbkFweWh3WThxUiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778608583),('nUo20IFtDq5tcuFW1Z3GB7UTcs1SU0lek75wyGi1',NULL,'167.172.77.26','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/129.0.6668.71 Safari/537.36','YToyOntzOjY6Il90b2tlbiI7czo0MDoiclJrc0RvdWtzN2pYTnR3NjNaSmZTdU5MUWg5NnNWd0VxZUlNOFZjeiI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778681463),('nyNE4ZojJqbmo3xgj2W8TI6QzKQWmrqdYlN5YLZK',NULL,'66.249.72.37','Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/99.0.4844.84 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoia0pCaUdDNFd0a3E1YWx2bEdrNzBIZGszVnZGMHlHdFdtRUVOalRCbSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778570918),('OX8MYPtfDthqapvcvrtR44CFkjm2601WcYJ4HlfW',NULL,'205.169.39.111','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/79.0.3945.79 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSzVwZjRlR2p4bUowTVZHOHY2UXo3Um5kWUFsdk8wcWZMOVNTa3dJeiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778638897),('PdRqGmgDiVM2Yw5tJwJwLgkdCOJuvGXmp2q5bESD',NULL,'191.96.106.25','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiendrRGxWdDhkeHZ5S1FTV0RqYWwxWk4xM3A0U2JLRHNmNG56emM4ZSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778573208),('pjmg7usIuXb5dBEV4XHS3UrUavtz6pGOOwqM027E',NULL,'66.249.72.38','Mozilla/5.0 (Linux; Android 6.0.1; Nexus 5X Build/MMB29P) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/147.0.7727.137 Mobile Safari/537.36 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoieDVlMkRSUnhzcnY1WFBFN0t3S1hjSGM2ZUtoR0hUTjZaWm1EeEw2ciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778570889),('PNS1m2oUcWo0y6VDJHHgdzBhAubffriHqApE3ZWM',NULL,'2a06:4883:3000::36','Mozilla/5.0 (compatible; InternetMeasurement/1.0; +https://internet-measurement.com/)','YTozOntzOjY6Il90b2tlbiI7czo0MDoidGRZaUlNZzZ6MkRwQ2FIOEZZalFEOXgzNXNQek8zNVJZRXIwTE5CSSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjk6Imh0dHBzOi8vWzJhMDI6NDc4MDpmOmNhNDQ6OjFdIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778709019),('pOzdThTuWOZ8mdn4tiUQrBkGnW693XEwH5nUrDBA',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiVTk2SElDUUFsSUpGczBiM1d2VjVSRk9EdVQzOFliS2dVYTdMaDhySSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778620966),('QuWiskjBL31h72FWQVNfXowmiDykV2igtgHwdWNj',NULL,'205.169.39.111','Mozilla/5.0 (Windows NT 6.1; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/83.0.4103.61 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM2tXNkI3S3dJb1N0ZmY5eG9jVHV5NUlwNlhmYXFOYjZ0QzFRb1ExNiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778638891),('qw9Ewu0LKAg4tRfqjgqAIoLJAxVjGJ7FmConlNF2',NULL,'191.96.106.25','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoicnJoWFZXTENHWDBTYm5PNDhzQjJ0MldabVF0eFlJSlMwTjlYcXYwQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778573207),('R6Js8iRUMyyh0qg0x4RHVIsEyftC3LNuI8GWmFCv',NULL,'66.249.72.38','Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)','YTozOntzOjY6Il90b2tlbiI7czo0MDoiRnpPdk5ENmppZHZFREE3WFJ3YnEyWERnY3NVdEYxalpsSWd5SW80dyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778570918),('RRwz4SWpQsguNGEp9oM6Kfz4IOxqVAWvefWJIPb0',NULL,'143.198.149.82','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZ091TWJOem95UG9EMVhQbWRIcllmcmpsZHYwc1JPY3g0VXdJWEdaSCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778598145),('tboTPbgzovxhbZQJzL6pLCvzhO3ABfh698LpUBzT',NULL,'170.106.74.215','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoieDQwV2pOUWU0NVR1NGtMa01kS2lscFFQM1BBSEZSTE4zMmFWRWpiNCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjAyOiJodHRwczovLzE4Ny4xMjQuMTE4LjIxMC9pbmRleC5waHA/JTJGJTNDJTNGZWNobyUyOG1kNSUyOCUyMmhpJTIyJTI5JTI5JTNCJTNGJTNFJTIwJTJGdG1wJTJGaW5kZXgxLnBocD0mY29uZmlnLWNyZWF0ZSUyMCUyRj0mbGFuZz0uLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGLi4lMkYuLiUyRi4uJTJGdXNyJTJGbG9jYWwlMkZsaWIlMkZwaHAlMkZwZWFyY21kIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778771223),('udiGdm8M1e8GVeMyGZiYHw7fFEfxLJecgt7aEBW5',NULL,'74.7.243.204','Mozilla/5.0 AppleWebKit/537.36 (KHTML, like Gecko; compatible; GPTBot/1.3; +https://openai.com/gptbot)','YTozOntzOjY6Il90b2tlbiI7czo0MDoia0lQM3NHMW5xRGg1RklsMDZGajR4WXE1SWNWM1RQd3RBWXIwakc1UiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778683121),('vUxOmJwA7QVZ41PAUd12OMXtJZcEjiNGGPEJuZAp',NULL,'43.156.116.44','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTUFjY3pGSm14Wm5UdXE2eHFUVk1Ca0haTU82Nnl3ekROZnNTZUxrNSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778602834),('WNoDxHVYEB8NhtX0WzEZxIo441JOKbeTAgYrRi79',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoicWM0SkdVTDYyZ3N2T2lrZWpHS1dTOERhUkhXTnJFMUxOTTZic1FLZiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDU6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20vYXdzLWNvbmZpZy5qcyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778620973),('wO6b6WH1xP90Jrmvf1uQoYyJJVvOtaOfVYy57b6Y',NULL,'206.189.200.233','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/131.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoielAyTDNtMXRVaUZKZll5aTY2Qm9tbzY3WGhqZjB1cFViOG1oSnR0SyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDY6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20vYXdzLWV4cG9ydHMuanMiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778620973),('XeEmCYF0ERMUG1liS7FP8OjyGImWLEUCWEaAohSJ',NULL,'192.71.126.245','Mozilla/5.0 (Windows NT 6.3; Win64; x64; rv:109.0) Gecko/20100101 Firefox/115','YTozOntzOjY6Il90b2tlbiI7czo0MDoiUzF1RUdOYlhtUzR3MUhMYUdUTVREVWV5QXh5cmsxbTFnSGZmTHh2WiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778594731),('xj8GAQLRexJP8KW4xGcqhMvSZbiNBFfvkfUHlYdO',NULL,'20.98.104.241','Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/534.24 (KHTML, like Gecko) Chrome/11.0.696.71 Safari/534.24','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTlVqWTZnT2FaNFBnd3U5RHpqWFBDWVVDanlST0V0bnFtaWx2YldQbiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MzE6Imh0dHBzOi8vd3d3LnNpZi1ncnVwb3Z5c2lzYS5jb20iO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778627479),('XpPNWEK4v35i6ohJEvJVH3nAYRqGJx0YMpkvGPdR',NULL,'2607:8500:faca:de::119','','YTozOntzOjY6Il90b2tlbiI7czo0MDoiREVwWFRVdEZvaXhmQlZ2RlNVa2MzNm1YdnRkbHdtN01LcDRyY29qbSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778717975),('xqCSqcVm3PsLxgBqNlPkESjxa6T6whjaHcUPqrmK',NULL,'143.244.188.98','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZmhKYkIwdDhRN1haTmtBeUdDeU1VWUxGRUl4U3lybGRBRERxcXNPSyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778653042),('xQuOFRebkyB88mHMAQOVPSsgD7KDfuFkJji8vs3c',NULL,'170.106.74.215','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiSTB2amM1SjJMWnlieFFaYWk3UERCNGMwY2N5ZVFOOUZJSHlEY2hQRiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MTQ5OiJodHRwczovLzE4Ny4xMjQuMTE4LjIxMC9pbmRleC5waHA/ZnVuY3Rpb249Y2FsbF91c2VyX2Z1bmNfYXJyYXkmcz0lMkZpbmRleCUyRiU1Q3RoaW5rJTVDYXBwJTJGaW52b2tlZnVuY3Rpb24mdmFycyU1QjAlNUQ9bWQ1JnZhcnMlNUIxJTVEJTVCMCU1RD1IZWxsbyI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778771222),('Y78CsE0YKu3I7sVMWjQOYQekOJvtm8SQXd6024Bh',NULL,'191.96.106.25','Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:126.0) Gecko/20100101 Firefox/126.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiMGt4cmlCOHBRb2R0a0RyVERJVXV4TVJRY2NyV1F6emdxUmNjYmpxayI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778573208),('YidsvCmURwLRwwKtkHMol8qKO3CiainJ9WofGUxg',NULL,'46.138.250.165','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Safari/537.36 AISearchIndex.space','YTozOntzOjY6Il90b2tlbiI7czo0MDoiQUFuNnRES1dodnFiTEpGMHBiTmNueXp1SXhtUnNVVXptSFFMcWtMcCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778645001),('YoazYPy82rGHjJu60p4EEFea5kNRZyVVu2BbthhX',NULL,'102.129.232.111','Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) HeadlessChrome/139.0.7258.5 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiTVI3dnhQbklFcUFydDE5cGhaMVZtc0dtQkw1OVBLdFJta0RGOE1mMiI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778611166),('YSMGcVJJZDmCVOTSXeSSterY7pSyTTedYGhtCdKX',NULL,'170.106.74.215','libredtail-http','YTozOntzOjY6Il90b2tlbiI7czo0MDoiZDVRU3VJTU8zMzhwZ0s0SXRXRmdacmpvTlNNcmQ1ejN3NE42NEVWQyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mzk6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwL2NvbnRhaW5lcnMvanNvbiI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778771223),('YU9jLUjXwUZLxFqesbZYhfRajc61foXvmmsqgIto',NULL,'101.32.128.113','Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1','YTozOntzOjY6Il90b2tlbiI7czo0MDoiM0MwOWRoemN4QnVVeVgxbmZja0p2QmlibXRLUGQ2bmhtaVJNOHc2UyI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6Mjc6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbSI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==',1778573773),('zCjl4Wby2J7YWJ2j3RiqrHuxk0THdSpYT6vsrmLO',NULL,'44.243.61.7','Mozilla/5.0 (compatible; wpbot/1.4; +https://forms.gle/ajBaxygz9jSR8p8G9)','YTozOntzOjY6Il90b2tlbiI7czo0MDoidlJmdkJnd09wS2NuZ3NnQ0l3dnU2dlZtZ2lDN0hUZ2o4NEFHdDBxOSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NDc6Imh0dHBzOi8vMTg3LTEyNC0xMTgtMjEwLnVzZXIzZy52ZWxveHpvbmUuY29tLmJyIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778666385),('zsv95SrK07jVZ88e2pZjXZyr9NUtqM0cu8cC87aT',NULL,'172.237.117.141','Mozilla/5.0 (Windows NT 6.1; WOW64; rv:8.0) Gecko/20100101 Firefox/8.0','YTozOntzOjY6Il90b2tlbiI7czo0MDoiS3hobjRxY21DS0ROY2tKanVDb0w0WXloYTJrRzNTYm1UQ2J6bXBONCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjM6Imh0dHBzOi8vMTg3LjEyNC4xMTguMjEwIjtzOjU6InJvdXRlIjtOO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19',1778711940),('ZwjHrVsPY0r6Hjwqr7thAPoVces5vXuozxHMAIrf',NULL,'35.229.194.241','Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/88.0.4240.193 Safari/537.36','YTozOntzOjY6Il90b2tlbiI7czo0MDoiWW12OFR0ZHU1YjRTeVY4R21Wb0xFRTd0YTV0QkIzSVVZTzhGWDJ6ZSI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6NjE6Imh0dHBzOi8vc2lmLWdydXBvdnlzaXNhLmNvbS8vc2l0by93cC1pbmNsdWRlcy93bHdtYW5pZmVzdC54bWwiO3M6NToicm91dGUiO047fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=',1778573673);
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidad_servicio_histories`
--

DROP TABLE IF EXISTS `unidad_servicio_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidad_servicio_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `unidad_servicio_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `unidad_servicio_histories_unidad_servicio_id_created_at_index` (`unidad_servicio_id`,`created_at`),
  KEY `unidad_servicio_histories_user_id_index` (`user_id`),
  KEY `unidad_servicio_histories_action_index` (`action`),
  CONSTRAINT `unidad_servicio_histories_unidad_servicio_id_foreign` FOREIGN KEY (`unidad_servicio_id`) REFERENCES `unidades_servicio` (`id`) ON DELETE SET NULL,
  CONSTRAINT `unidad_servicio_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidad_servicio_histories`
--

LOCK TABLES `unidad_servicio_histories` WRITE;
/*!40000 ALTER TABLE `unidad_servicio_histories` DISABLE KEYS */;
INSERT INTO `unidad_servicio_histories` VALUES (1,1,1,'created','{\"estado\": \"Activo\", \"nombre\": \"Aztecas\", \"descripcion\": \"Oficinas Centrales\"}',NULL,'2026-03-31 18:10:10','2026-03-31 18:10:10'),(2,2,1,'created','{\"estado\": \"Activo\", \"nombre\": \"Morelos\", \"descripcion\": \"Morelos\"}',NULL,'2026-03-31 18:49:56','2026-03-31 18:49:56');
/*!40000 ALTER TABLE `unidad_servicio_histories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidad_servicio_user`
--

DROP TABLE IF EXISTS `unidad_servicio_user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidad_servicio_user` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `unidad_servicio_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidad_servicio_user_user_id_unidad_servicio_id_unique` (`user_id`,`unidad_servicio_id`),
  KEY `unidad_servicio_user_unidad_servicio_id_foreign` (`unidad_servicio_id`),
  CONSTRAINT `unidad_servicio_user_unidad_servicio_id_foreign` FOREIGN KEY (`unidad_servicio_id`) REFERENCES `unidades_servicio` (`id`) ON DELETE CASCADE,
  CONSTRAINT `unidad_servicio_user_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidad_servicio_user`
--

LOCK TABLES `unidad_servicio_user` WRITE;
/*!40000 ALTER TABLE `unidad_servicio_user` DISABLE KEYS */;
INSERT INTO `unidad_servicio_user` VALUES (2,1,1,'2026-03-31 18:17:16','2026-03-31 18:17:16'),(3,3,1,'2026-03-31 18:21:17','2026-03-31 18:21:17'),(4,2,2,'2026-03-31 18:50:06','2026-03-31 18:50:06'),(8,1,2,'2026-04-09 20:42:30','2026-04-09 20:42:30'),(9,3,2,'2026-04-21 20:18:29','2026-04-21 20:18:29');
/*!40000 ALTER TABLE `unidad_servicio_user` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `unidades_servicio`
--

DROP TABLE IF EXISTS `unidades_servicio`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `unidades_servicio` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unidades_servicio_nombre_unique` (`nombre`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `unidades_servicio`
--

LOCK TABLES `unidades_servicio` WRITE;
/*!40000 ALTER TABLE `unidades_servicio` DISABLE KEYS */;
INSERT INTO `unidades_servicio` VALUES (1,'Aztecas','Oficinas Centrales',1,'2026-03-31 18:10:10','2026-03-31 18:10:10'),(2,'Morelos','Morelos',1,'2026-03-31 18:49:56','2026-03-31 18:49:56');
/*!40000 ALTER TABLE `unidades_servicio` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_histories`
--

DROP TABLE IF EXISTS `user_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned DEFAULT NULL,
  `actor_user_id` bigint unsigned DEFAULT NULL,
  `action` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `snapshot` json DEFAULT NULL,
  `changes` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_histories_user_id_created_at_index` (`user_id`,`created_at`),
  KEY `user_histories_actor_user_id_index` (`actor_user_id`),
  KEY `user_histories_action_index` (`action`),
  CONSTRAINT `user_histories_actor_user_id_foreign` FOREIGN KEY (`actor_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_histories_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_histories`
--

LOCK TABLES `user_histories` WRITE;
/*!40000 ALTER TABLE `user_histories` DISABLE KEYS */;
INSERT INTO `user_histories` VALUES (1,2,1,'created','{\"name\": \"Leonardo Daniel Centeno Guerrero\", \"email\": \"soporte.sistemas2@grupo-vysisa.mx\", \"roles\": [\"Usuario\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\"]}','[{\"new\": \"Establecida\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-03-31 18:11:09','2026-03-31 18:11:09'),(2,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\"]}','[{\"new\": [\"Aztecas\"], \"old\": [], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": [\"Vysisa\"], \"old\": [], \"type\": \"list\", \"field\": \"empresas\", \"label\": \"Empresa\"}, {\"new\": [\"sistemas\"], \"old\": [], \"type\": \"list\", \"field\": \"grupos\", \"label\": \"Grupo\"}]','2026-03-31 18:17:16','2026-03-31 18:17:16'),(3,3,1,'created','{\"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\", \"roles\": [\"Usuario\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\"]}','[{\"new\": \"Establecida\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-03-31 18:21:17','2026-03-31 18:21:17'),(4,2,1,'updated','{\"name\": \"Leonardo Daniel Centeno Guerrero\", \"email\": \"soporte.sistemas2@grupo-vysisa.mx\", \"roles\": [\"Usuario\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Morelos\"]}','[{\"new\": [\"Morelos\"], \"old\": [\"Aztecas\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}]','2026-03-31 18:50:06','2026-03-31 18:50:06'),(5,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\", \"Morelos\"]}','[{\"new\": [\"Aztecas\", \"Morelos\"], \"old\": [\"Aztecas\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": \"Actualizada\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-04-08 23:06:54','2026-04-08 23:06:54'),(6,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\"]}','[{\"new\": [\"Aztecas\"], \"old\": [\"Aztecas\", \"Morelos\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": \"Actualizada\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-04-08 23:07:05','2026-04-08 23:07:05'),(7,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\", \"Morelos\"]}','[{\"new\": [\"Aztecas\", \"Morelos\"], \"old\": [\"Aztecas\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": \"Actualizada\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-04-08 23:08:54','2026-04-08 23:08:54'),(8,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\"]}','[{\"new\": [\"Aztecas\"], \"old\": [\"Aztecas\", \"Morelos\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": \"Actualizada\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-04-09 20:34:15','2026-04-09 20:34:15'),(9,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\", \"Morelos\"]}','[{\"new\": [\"Aztecas\", \"Morelos\"], \"old\": [\"Aztecas\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": \"Actualizada\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-04-09 20:35:47','2026-04-09 20:35:47'),(10,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\"]}','[{\"new\": [\"Aztecas\"], \"old\": [\"Aztecas\", \"Morelos\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": \"Actualizada\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-04-09 20:42:17','2026-04-09 20:42:17'),(11,1,1,'updated','{\"name\": \"Yael Alain Romero Cazarez\", \"email\": \"soporte.sistemas3@grupo-vysisa.mx\", \"roles\": [\"Administrador\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\", \"Morelos\"]}','[{\"new\": [\"Aztecas\", \"Morelos\"], \"old\": [\"Aztecas\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}, {\"new\": \"Actualizada\", \"old\": null, \"type\": \"password\", \"field\": \"password\", \"label\": \"Contraseña\"}]','2026-04-09 20:42:30','2026-04-09 20:42:30'),(12,3,1,'updated','{\"name\": \"Pruebas\", \"email\": \"prueba@grupo-vysisa.mx\", \"roles\": [\"Usuario\"], \"activo\": true, \"grupos\": [\"sistemas\"], \"empresas\": [\"Vysisa\"], \"unidades_servicio\": [\"Aztecas\", \"Morelos\"]}','[{\"new\": [\"Aztecas\", \"Morelos\"], \"old\": [\"Aztecas\"], \"type\": \"list\", \"field\": \"unidades_servicio\", \"label\": \"Unidad de servicio\"}]','2026-04-21 20:18:29','2026-04-21 20:18:29');
/*!40000 ALTER TABLE `user_histories` ENABLE KEYS */;
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
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT '1',
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Yael Alain Romero Cazarez','soporte.sistemas3@grupo-vysisa.mx',NULL,'$2y$12$kggH6woeXGcJss86FuIEG.nTYTlxNI4qIEhoo72G4Nfs/Ksr2x6uG',1,NULL,'2026-03-31 17:55:30','2026-04-09 20:42:30'),(2,'Leonardo Daniel Centeno Guerrero','soporte.sistemas2@grupo-vysisa.mx',NULL,'$2y$12$SvYvE3WNavwjNKOCNySNoOSiZpfDa6cs9yr/kGSBt0w.QxkTUcK/a',1,NULL,'2026-03-31 18:11:09','2026-03-31 18:11:09'),(3,'Pruebas','prueba@grupo-vysisa.mx',NULL,'$2y$12$23vdf7z17mCr7WDmI/re9OnjcMhTBu4Eltr/a5XEX5lnXdzqonuY6',1,NULL,'2026-03-31 18:21:17','2026-03-31 18:21:17');
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

-- Dump completed on 2026-05-14 15:48:40

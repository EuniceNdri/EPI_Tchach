-- MySQL dump 10.13  Distrib 8.4.11, for Linux (x86_64)
--
-- Host: 127.0.0.1    Database: forum_ecole
-- ------------------------------------------------------
-- Server version	8.4.11-0ubuntu0.26.04.1

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
-- Table structure for table `appartenance_zones`
--

DROP TABLE IF EXISTS `appartenance_zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `appartenance_zones` (
  `utilisateur_id` int unsigned NOT NULL,
  `zone_id` int unsigned NOT NULL,
  `role_dans_zone` enum('membre','delegue_de_classe','delegue_general') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'membre',
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `delegation_exception` tinyint(1) NOT NULL DEFAULT '0',
  `delegue_general_slot` tinyint GENERATED ALWAYS AS ((case when (`role_dans_zone` = _utf8mb4'delegue_general') then 1 else NULL end)) STORED,
  `delegue_classe_zone_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`utilisateur_id`,`zone_id`),
  UNIQUE KEY `uq_appartenance_class_delegate_zone` (`delegue_classe_zone_id`),
  UNIQUE KEY `uq_appartenance_global_delegate` (`delegue_general_slot`),
  KEY `zone_id` (`zone_id`),
  KEY `idx_appartenance_role` (`role_dans_zone`),
  CONSTRAINT `appartenance_zones_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `appartenance_zones_ibfk_2` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `appartenance_zones`
--

LOCK TABLES `appartenance_zones` WRITE;
/*!40000 ALTER TABLE `appartenance_zones` DISABLE KEYS */;
INSERT INTO `appartenance_zones` (`utilisateur_id`, `zone_id`, `role_dans_zone`, `date_ajout`, `delegation_exception`, `delegue_classe_zone_id`) VALUES (1,1,'membre','2026-10-28 20:53:26',0,NULL),(1,2,'membre','2026-10-28 20:53:26',0,NULL),(1,5,'membre','2026-10-28 20:53:26',0,NULL),(2,1,'membre','2026-10-28 20:53:26',0,NULL),(2,2,'membre','2026-10-28 20:53:26',0,NULL),(3,1,'membre','2026-10-28 20:53:26',0,NULL),(3,3,'membre','2026-10-28 20:53:26',0,NULL),(4,1,'membre','2026-10-28 20:53:26',0,NULL),(4,2,'delegue_de_classe','2026-10-28 20:53:26',0,2),(4,5,'membre','2026-10-28 20:53:26',0,NULL),(5,1,'membre','2026-10-28 20:53:26',0,NULL),(5,2,'membre','2026-10-28 20:53:26',0,NULL),(5,5,'delegue_general','2026-10-28 20:53:26',0,NULL),(6,1,'membre','2026-10-28 20:53:26',0,NULL),(6,2,'membre','2026-10-28 20:53:26',0,NULL),(6,4,'membre','2026-10-28 20:53:26',0,NULL),(7,1,'membre','2026-10-28 20:53:26',0,NULL),(7,3,'membre','2026-10-28 20:53:26',0,NULL),(7,4,'membre','2026-10-28 20:53:26',0,NULL),(8,1,'membre','2026-10-28 20:53:26',0,NULL),(8,4,'membre','2026-10-28 20:53:26',0,NULL),(9,1,'membre','2026-10-28 20:53:26',0,NULL),(9,5,'membre','2026-10-28 20:53:26',0,NULL),(10,1,'membre','2026-10-28 20:53:26',0,NULL),(11,1,'membre','2026-10-28 20:55:32',0,NULL),(11,2,'membre','2026-10-28 20:55:32',0,NULL),(11,5,'membre','2026-11-08 12:07:32',0,NULL),(12,1,'membre','2026-11-04 08:47:32',0,NULL),(12,2,'membre','2026-11-04 08:47:32',0,NULL),(13,1,'membre','2026-11-05 12:19:34',0,NULL),(13,2,'membre','2026-11-05 12:19:34',0,NULL),(14,1,'membre','2026-11-05 12:29:45',0,NULL),(14,2,'membre','2026-11-05 12:29:45',0,NULL),(15,1,'membre','2026-11-05 12:33:58',0,NULL),(15,2,'membre','2026-11-05 12:33:58',0,NULL),(16,1,'membre','2026-11-07 20:10:49',0,NULL),(16,2,'membre','2026-11-07 20:10:49',0,NULL),(17,1,'membre','2026-11-08 10:13:57',0,NULL),(17,2,'membre','2026-11-08 10:13:57',0,NULL),(19,1,'membre','2026-11-09 14:21:24',0,NULL),(19,2,'membre','2026-11-09 14:21:24',0,NULL),(20,1,'membre','2026-11-09 14:56:17',0,NULL),(20,2,'membre','2026-11-09 14:56:17',0,NULL),(21,1,'membre','2026-11-09 15:37:17',0,NULL),(21,2,'membre','2026-11-09 15:37:17',0,NULL);
/*!40000 ALTER TABLE `appartenance_zones` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `zone_id` int unsigned NOT NULL,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `ordre` int NOT NULL DEFAULT '0',
  `est_active` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_slug_zone` (`zone_id`,`slug`),
  CONSTRAINT `categories_ibfk_1` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES (1,1,'Annonces Officielles','annonces-officielles','Communications de la direction.',1,1),(2,1,'Vie Étudiante & Événements','vie-etudiante','Discussions générales et activités.',2,1),(3,2,'Cours & TP','cours-tp-ida','Questions et entraide sur les cours.',1,1),(4,2,'Projets de Classe','projets-ida','Suivi des projets tuteurés.',2,1),(5,3,'Cours & TP','cours-tp-licence','Questions sur les modules de Licence.',1,1),(6,4,'Pédagogie & Syllabus','pedagogie','Échanges entre enseignants.',1,1),(7,5,'Réunions & Décisions','reunions-delegation','Comptes-rendus de la Délégation.',1,1),(8,4,'Organisation','organisation-profs','Coordination entre professeurs.',2,1);
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `commentaires`
--

DROP TABLE IF EXISTS `commentaires`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `commentaires` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` int unsigned NOT NULL,
  `utilisateur_id` int unsigned NOT NULL,
  `parent_comment_id` int unsigned DEFAULT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `est_solution` tinyint(1) NOT NULL DEFAULT '0',
  `est_epingle` tinyint(1) NOT NULL DEFAULT '0',
  `est_signale` tinyint(1) NOT NULL DEFAULT '0',
  `nb_likes` int unsigned NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `utilisateur_id` (`utilisateur_id`),
  KEY `idx_com_publication` (`publication_id`),
  KEY `idx_com_signale` (`est_signale`),
  KEY `idx_com_nb_likes` (`nb_likes`),
  KEY `idx_commentaires_parent` (`parent_comment_id`),
  CONSTRAINT `commentaires_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `commentaires_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_commentaires_parent` FOREIGN KEY (`parent_comment_id`) REFERENCES `commentaires` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `commentaires`
--

LOCK TABLES `commentaires` WRITE;
/*!40000 ALTER TABLE `commentaires` DISABLE KEYS */;
INSERT INTO `commentaires` VALUES (1,2,1,NULL,'C\'est bien noté Chef !',0,0,0,0,'2026-10-28 20:53:26',NULL),(2,3,14,NULL,'bas ceest genial',1,0,0,0,'2026-11-07 20:02:32','2026-11-09 11:33:32'),(3,3,11,NULL,'salut',0,0,0,0,'2026-11-09 11:09:32',NULL),(4,5,11,NULL,'salut',0,0,0,0,'2026-11-09 13:42:16',NULL),(5,6,21,NULL,'KPOIII',0,0,0,0,'2026-11-09 15:41:31',NULL);
/*!40000 ALTER TABLE `commentaires` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filiere_group_messages`
--

DROP TABLE IF EXISTS `filiere_group_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filiere_group_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `group_id` int unsigned NOT NULL,
  `sender_id` int unsigned NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_filiere_group_messages_history` (`group_id`,`date_creation`,`id`),
  KEY `fk_filiere_group_messages_sender` (`sender_id`),
  CONSTRAINT `fk_filiere_group_messages_group` FOREIGN KEY (`group_id`) REFERENCES `filiere_message_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_filiere_group_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filiere_group_messages`
--

LOCK TABLES `filiere_group_messages` WRITE;
/*!40000 ALTER TABLE `filiere_group_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `filiere_group_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filiere_group_professors`
--

DROP TABLE IF EXISTS `filiere_group_professors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filiere_group_professors` (
  `group_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `assigned_by` int unsigned DEFAULT NULL,
  `assigned_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `idx_filiere_group_professors_user` (`user_id`),
  KEY `fk_filiere_group_professors_admin` (`assigned_by`),
  CONSTRAINT `fk_filiere_group_professors_admin` FOREIGN KEY (`assigned_by`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_filiere_group_professors_group` FOREIGN KEY (`group_id`) REFERENCES `filiere_message_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_filiere_group_professors_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filiere_group_professors`
--

LOCK TABLES `filiere_group_professors` WRITE;
/*!40000 ALTER TABLE `filiere_group_professors` DISABLE KEYS */;
/*!40000 ALTER TABLE `filiere_group_professors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filiere_group_reads`
--

DROP TABLE IF EXISTS `filiere_group_reads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filiere_group_reads` (
  `group_id` int unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `last_read_at` datetime NOT NULL,
  PRIMARY KEY (`group_id`,`user_id`),
  KEY `fk_filiere_group_reads_user` (`user_id`),
  CONSTRAINT `fk_filiere_group_reads_group` FOREIGN KEY (`group_id`) REFERENCES `filiere_message_groups` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_filiere_group_reads_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filiere_group_reads`
--

LOCK TABLES `filiere_group_reads` WRITE;
/*!40000 ALTER TABLE `filiere_group_reads` DISABLE KEYS */;
INSERT INTO `filiere_group_reads` VALUES (1,14,'2026-11-16 19:38:15');
/*!40000 ALTER TABLE `filiere_group_reads` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filiere_message_groups`
--

DROP TABLE IF EXISTS `filiere_message_groups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filiere_message_groups` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `filiere_id` int unsigned NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `filiere_id` (`filiere_id`),
  CONSTRAINT `fk_filiere_message_groups_filiere` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=49 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filiere_message_groups`
--

LOCK TABLES `filiere_message_groups` WRITE;
/*!40000 ALTER TABLE `filiere_message_groups` DISABLE KEYS */;
INSERT INTO `filiere_message_groups` VALUES (1,1,'2026-11-16 14:32:09'),(2,2,'2026-11-16 14:32:09'),(3,3,'2026-11-16 14:32:09'),(4,4,'2026-11-16 14:32:09'),(5,5,'2026-11-16 14:32:09'),(8,31,'2026-11-16 14:32:51'),(9,32,'2026-11-16 14:32:51'),(10,33,'2026-11-16 14:32:51'),(11,34,'2026-11-16 14:32:51'),(12,35,'2026-11-16 14:32:51'),(13,36,'2026-11-16 14:32:51'),(14,37,'2026-11-16 14:32:51'),(15,38,'2026-11-16 14:32:51'),(16,39,'2026-11-16 14:32:51'),(17,40,'2026-11-16 14:32:51'),(18,41,'2026-11-16 14:32:51'),(19,42,'2026-11-16 14:32:51'),(20,43,'2026-11-16 14:32:51'),(21,44,'2026-11-16 14:32:51'),(22,45,'2026-11-16 14:32:51'),(23,46,'2026-11-16 14:32:51');
/*!40000 ALTER TABLE `filiere_message_groups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `filieres`
--

DROP TABLE IF EXISTS `filieres`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `filieres` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `slogan` varchar(180) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `zone_id` int unsigned DEFAULT NULL,
  `est_active` tinyint(1) NOT NULL DEFAULT '1',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `fk_filieres_zone` (`zone_id`),
  CONSTRAINT `fk_filieres_zone` FOREIGN KEY (`zone_id`) REFERENCES `zones` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `filieres`
--

LOCK TABLES `filieres` WRITE;
/*!40000 ALTER TABLE `filieres` DISABLE KEYS */;
INSERT INTO `filieres` VALUES (1,'BTS IDA','IDA','BTS Informatique de Développement d\'Applications',NULL,NULL,NULL,1,'2026-11-02 21:17:36'),(2,'Licence Informatique','LIC-INFO','Licence professionnelle Informatique',NULL,NULL,NULL,1,'2026-11-02 21:17:36'),(3,'Génie Mécanique','GM','Filière Génie Mécanique',NULL,NULL,NULL,1,'2026-11-02 21:17:36'),(4,'Génie Civil','GC','BTS / Licence Génie Civil & BTP',NULL,NULL,NULL,1,'2026-11-02 21:17:36'),(5,'Finance Comptabilité','FCGE','Finance, Comptabilité et Gestion des Entreprises',NULL,NULL,NULL,1,'2026-11-02 21:17:36'),(31,'Agriculture Tropicale option Production Animale','ATPA','Élevage, santé animale, aviculture et gestion de fermes agricoles.','images/filiere-industrielle.jpg','Élevage, santé animale et agrobusiness moderne',NULL,1,'2026-11-16 14:32:51'),(32,'Agriculture Tropicale option Production Végétale','ATPV','Agronomie, cultures vivrières et d\'exportation, gestion d\'exploitations agricoles.','images/filiere-industrielle.jpg','Agronomie, cultures vivrières et développement agricole',NULL,1,'2026-11-16 14:32:51'),(33,'Gestion de l\'Environnement et des Ressources Naturelles','GERNA','Écologie, traitement des déchets, préservation des écosystèmes et développement durable.','images/filiere-industrielle.jpg','Écologie, préservation des écosystèmes et développement durable',NULL,1,'2026-11-16 14:32:51'),(34,'Génie Civil option Bâtiment','GBAT','Conception de plans, calcul de structures, suivi et réalisation de chantiers de construction.','images/filiere-industrielle.jpg','Conception de plans, calcul de structures et chantiers durables',NULL,1,'2026-11-16 14:32:51'),(35,'Génie Civil option Travaux Publics','GTP','Construction et entretien des routes, ponts, barrages et réseaux d\'assainissement.','images/filiere-industrielle.jpg','Grandes infrastructures, voiries, ponts et assainissement',NULL,1,'2026-11-16 14:32:51'),(36,'Génie Civil option Géomètre Topographe','GGT','Relevés de terrains, cartographie, bornage et implantation d\'ouvrages.','images/filiere-industrielle.jpg','Topographie de pointe, cartographie et bornage territorial',NULL,1,'2026-11-16 14:32:51'),(37,'Informatique Développeur d\'Applications','IDA','Conception de logiciels, développement web et mobile, bases de données.','images/filiere-industrielle.jpg','Développement logiciel, web, mobile et bases de données',NULL,1,'2026-11-16 14:32:51'),(38,'Réseaux Informatiques et Télécommunications','RIT','Installation, administration et sécurité des réseaux informatiques et systèmes télécoms.','images/filiere-industrielle.jpg','Administration réseaux, cybersécurité et télécommunications',NULL,1,'2026-11-16 14:32:51'),(39,'Électrotechnique','ELT','Installation, distribution et maintenance des équipements électriques industriels et domestiques.','images/filiere-industrielle.jpg','Énergie électrique, automatismes et maintenance industrielle',NULL,1,'2026-11-16 14:32:51'),(40,'Maintenance des Systèmes de Production','MSP','Entretien, diagnostic et réparation des machines et lignes de production en usine.','images/filiere-industrielle.jpg','Diagnostic machine, maintenance avancée et productivité industrielle',NULL,1,'2026-11-16 14:32:51'),(41,'Ressources Humaines et Communication','RHCOM','Recrutement, gestion du personnel, paie et communication interne ou externe.','images/filiere-tertiaire.jpg','Capital humain, relations professionnelles et communication stratégique',NULL,1,'2026-11-16 14:32:51'),(42,'Assistanat de Direction','AD','Organisation administrative, secrétariat de haut niveau, gestion d\'agenda et communication professionnelle.','images/filiere-tertiaire.jpg','Excellence administrative, secrétariat de haut niveau et coordination',NULL,1,'2026-11-16 14:32:51'),(43,'Tourisme - Hôtellerie','TH','Gestion hôtelière, restauration, organisation de voyages et accueil touristique.','images/filiere-tertiaire.jpg','Accueil haut de gamme, hébergement et management touristique',NULL,1,'2026-11-16 14:32:51'),(44,'Logistique','LOG','Gestion des stocks, approvisionnement, transport de marchandises et chaîne logistique.','images/filiere-tertiaire.jpg','Supply chain, optimisation des flux et gestion des transports',NULL,1,'2026-11-16 14:32:51'),(45,'Finances Comptabilité et Gestion des Entreprises','FCGE','Tenue comptable, fiscalité, audit et analyse financière.','images/filiere-tertiaire.jpg','Gestion financière, audit, fiscalité et pilotage d\'entreprise',NULL,1,'2026-11-16 14:32:51'),(46,'Gestion Commerciale','GEC','Marketing, techniques de vente, négociation commerciale et relation client.','images/filiere-tertiaire.jpg','Marketing opérationnel, négociation commerciale et relation client',NULL,1,'2026-11-16 14:32:51');
/*!40000 ALTER TABLE `filieres` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `friendships`
--

DROP TABLE IF EXISTS `friendships`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `friendships` (
  `user_one_id` int unsigned NOT NULL,
  `user_two_id` int unsigned NOT NULL,
  `requested_by` int unsigned NOT NULL,
  `status` enum('pending','accepted','declined') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`user_one_id`,`user_two_id`),
  KEY `idx_friendships_requested_by_status` (`requested_by`,`status`),
  KEY `fk_friendships_user_two` (`user_two_id`),
  CONSTRAINT `fk_friendships_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_friendships_user_one` FOREIGN KEY (`user_one_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_friendships_user_two` FOREIGN KEY (`user_two_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `friendships`
--

LOCK TABLES `friendships` WRITE;
/*!40000 ALTER TABLE `friendships` DISABLE KEYS */;
/*!40000 ALTER TABLE `friendships` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `landing_settings`
--

DROP TABLE IF EXISTS `landing_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `landing_settings` (
  `config_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `config_value` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`config_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `landing_settings`
--

LOCK TABLES `landing_settings` WRITE;
/*!40000 ALTER TABLE `landing_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `landing_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `mots_interdits`
--

DROP TABLE IF EXISTS `mots_interdits`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `mots_interdits` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `mot` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_mot` enum('interdit','suspect','insulte','spam') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'interdit',
  `action` enum('bloquer','signaler','remplacer') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'signaler',
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `date_ajout` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `mots_interdits`
--

LOCK TABLES `mots_interdits` WRITE;
/*!40000 ALTER TABLE `mots_interdits` DISABLE KEYS */;
INSERT INTO `mots_interdits` VALUES (1,'insulte','insulte','bloquer',1,'2026-10-28 20:53:26'),(2,'spam','spam','signaler',1,'2026-10-28 20:53:26');
/*!40000 ALTER TABLE `mots_interdits` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `notifications`
--

DROP TABLE IF EXISTS `notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `notifications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `type_notification` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lien` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lu` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_notif_user_lu` (`utilisateur_id`,`lu`),
  CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=80 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `notifications`
--

LOCK TABLES `notifications` WRITE;
/*!40000 ALTER TABLE `notifications` DISABLE KEYS */;
INSERT INTO `notifications` VALUES (1,1,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(2,2,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(3,4,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(4,5,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(5,6,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(6,11,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(7,13,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(8,14,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(9,15,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(10,16,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',0,'2026-11-08 10:41:56'),(11,17,'publication','Darck Shadow a publié « salut » dans un salon que vous avez rejoint.','/salon?pub=4',1,'2026-11-08 10:41:56'),(12,14,'reaction','Darck Shadow a aimé 👍 votre publication : \"mohamed\"','/salon?pub=3',0,'2026-11-09 11:01:35'),(13,14,'reaction','Darck Shadow a aimé 👍 votre publication : \"mohamed\"','/salon?pub=3',0,'2026-11-09 11:02:26'),(14,14,'reaction','Darck Shadow a aimé 👍 votre publication : \"un commentaire sur : mohamed\"','/salon?pub=3',0,'2026-11-09 11:02:33'),(15,2,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:26'),(16,3,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:26'),(17,4,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:26'),(18,5,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:26'),(19,7,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:26'),(20,9,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:26'),(21,10,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:26'),(22,13,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(23,15,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(24,6,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(25,1,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(26,14,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(27,8,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(28,16,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(29,12,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(30,17,'publication','Darck Shadow a publié « sujet deconomie » dans un salon que vous avez rejoint.','/salon?pub=5',0,'2026-11-09 11:07:27'),(31,14,'reponse','Darck Shadow a commenté votre publication : « mohamed »','/salon?pub=3',0,'2026-11-09 11:09:32'),(32,8,'reaction','Darck Shadow a aimé 👍 votre publication : \"Rentrée 2026 - Informations importantes\"','/salon?pub=1',0,'2026-11-09 11:19:23'),(33,14,'reaction','Darck Shadow a aimé 👍 votre publication : \"un commentaire sur : mohamed\"','/salon?pub=3',0,'2026-11-09 11:33:15'),(34,14,'reaction','Darck Shadow a aimé 👍 votre publication : \"un commentaire sur : mohamed\"','/salon?pub=3',0,'2026-11-09 11:33:18'),(35,14,'reaction','Darck Shadow a aimé 👍 votre publication : \"mohamed\"','/salon?pub=3',0,'2026-11-09 11:34:19'),(36,8,'reaction','Darck Shadow a aimé 👍 votre publication : \"Rentrée 2026 - Informations importantes\"','/salon?pub=1',0,'2026-11-09 12:45:39'),(37,4,'reaction','Darck Shadow a aimé 👍 votre publication : \"Rappel : Rendu du projet Laravel\"','/salon?pub=2',0,'2026-11-09 12:45:42'),(38,2,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(39,3,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(40,4,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(41,5,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(42,7,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(43,9,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(44,10,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(45,13,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(46,15,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(47,6,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(48,1,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(49,14,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(50,8,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(51,16,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(52,12,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(53,17,'publication','Darck Shadow a publié « mohamed » dans un salon que vous avez rejoint.','/salon?pub=6',0,'2026-11-09 13:44:40'),(54,14,'reaction','Capelo L\'homme qui pleure jamais a aimé 👍 votre publication : \"mohamed\"','/salon?pub=3',0,'2026-11-09 15:06:53'),(55,11,'reaction','kata sala a aimé 👍 votre publication : \"mohamed\"','/salon?pub=6',0,'2026-11-09 15:14:47'),(56,11,'reaction','kata sala a aimé 👍 votre publication : \"sujet deconomie\"','/salon?pub=5',0,'2026-11-09 15:40:34'),(57,11,'reponse','ariel kouasso a commenté votre publication : « mohamed »','/salon?pub=6',0,'2026-11-09 15:41:31'),(58,11,'reaction','Darck Shadow a aimé 👍 votre publication : \"mohamed\"','/salon?pub=6',0,'2026-11-16 14:44:42'),(59,12,'reaction','Darck Shadow a aimé 👍 votre publication : \"salut\"','/salon?pub=4',0,'2026-11-16 14:55:18'),(60,12,'reaction','Darck Shadow a aimé 👍 votre publication : \"salut\"','/salon?pub=4',0,'2026-11-16 14:55:19'),(61,2,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(62,3,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(63,4,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(64,7,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(65,9,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(66,10,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(67,13,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(68,15,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(69,6,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(70,1,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(71,16,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(72,12,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(73,11,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(74,5,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(75,8,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(76,19,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(77,17,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(78,21,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39'),(79,20,'publication','Darck Shadow a publié « je sui dan le public » dans un salon que vous avez rejoint.','/salon?pub=7',0,'2026-11-16 15:33:39');
/*!40000 ALTER TABLE `notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pieces_jointes`
--

DROP TABLE IF EXISTS `pieces_jointes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pieces_jointes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `type_cible` enum('publication','commentaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cible_id` int unsigned NOT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin_fichier` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_mime` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_media` enum('image','document') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'document',
  `taille` int unsigned NOT NULL DEFAULT '0',
  `ordre_affichage` int unsigned NOT NULL DEFAULT '0',
  `date_upload` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pieces_cible` (`type_cible`,`cible_id`),
  KEY `idx_pieces_cible_ordre` (`type_cible`,`cible_id`,`ordre_affichage`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pieces_jointes`
--

LOCK TABLES `pieces_jointes` WRITE;
/*!40000 ALTER TABLE `pieces_jointes` DISABLE KEYS */;
INSERT INTO `pieces_jointes` VALUES (1,'publication',3,'TV5aB.jpg','/uploads/4618c31209df964ea73f2912f24e346f.jpg','image/jpeg','image',220805,0,'2026-11-07 15:08:16'),(2,'publication',5,'Capital_Humain_Entreprise_EPI_BTS2_IDA_enrichi (1).docx','private/attachments/d6cd1e0c3d87fdcedf5ac42078c4023c.docx','application/vnd.openxmlformats-officedocument.wordprocessingml.document','document',293752,0,'2026-11-09 11:07:26'),(3,'publication',7,'Capital_Humain_Entreprise_EPI_BTS2_IDA_enrichi (1) (1).docx','private/attachments/abe0df0a2606021e3a9a10e7f1b66220.docx','application/vnd.openxmlformats-officedocument.wordprocessingml.document','document',293752,0,'2026-11-16 15:33:39');
/*!40000 ALTER TABLE `pieces_jointes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pieces_jointes_sauvegardees`
--

DROP TABLE IF EXISTS `pieces_jointes_sauvegardees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pieces_jointes_sauvegardees` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `piece_jointe_id` int unsigned NOT NULL,
  `date_sauvegarde` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_piece_jointe_sauvegardee` (`utilisateur_id`,`piece_jointe_id`),
  KEY `piece_jointe_id` (`piece_jointe_id`),
  CONSTRAINT `pieces_jointes_sauvegardees_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `pieces_jointes_sauvegardees_ibfk_2` FOREIGN KEY (`piece_jointe_id`) REFERENCES `pieces_jointes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pieces_jointes_sauvegardees`
--

LOCK TABLES `pieces_jointes_sauvegardees` WRITE;
/*!40000 ALTER TABLE `pieces_jointes_sauvegardees` DISABLE KEYS */;
/*!40000 ALTER TABLE `pieces_jointes_sauvegardees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `private_message_attachments`
--

DROP TABLE IF EXISTS `private_message_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `private_message_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `message_id` bigint unsigned NOT NULL,
  `nom_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `chemin_fichier` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_mime` varchar(127) COLLATE utf8mb4_unicode_ci NOT NULL,
  `taille` bigint unsigned NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_private_message_attachments_message` (`message_id`),
  CONSTRAINT `fk_private_message_attachments_message` FOREIGN KEY (`message_id`) REFERENCES `private_messages` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `private_message_attachments`
--

LOCK TABLES `private_message_attachments` WRITE;
/*!40000 ALTER TABLE `private_message_attachments` DISABLE KEYS */;
/*!40000 ALTER TABLE `private_message_attachments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `private_message_deletions`
--

DROP TABLE IF EXISTS `private_message_deletions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `private_message_deletions` (
  `message_id` bigint unsigned NOT NULL,
  `user_id` int unsigned NOT NULL,
  `deleted_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`message_id`,`user_id`),
  KEY `idx_private_message_deletions_user` (`user_id`,`message_id`),
  CONSTRAINT `fk_private_message_deletions_message` FOREIGN KEY (`message_id`) REFERENCES `private_messages` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_private_message_deletions_user` FOREIGN KEY (`user_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `private_message_deletions`
--

LOCK TABLES `private_message_deletions` WRITE;
/*!40000 ALTER TABLE `private_message_deletions` DISABLE KEYS */;
/*!40000 ALTER TABLE `private_message_deletions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `private_messages`
--

DROP TABLE IF EXISTS `private_messages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `private_messages` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `sender_id` int unsigned NOT NULL,
  `recipient_id` int unsigned NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `read_at` datetime DEFAULT NULL,
  `deleted_for_everyone_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_private_messages_thread` (`sender_id`,`recipient_id`,`date_creation`,`id`),
  KEY `idx_private_messages_unread` (`recipient_id`,`sender_id`,`read_at`),
  CONSTRAINT `fk_private_messages_recipient` FOREIGN KEY (`recipient_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_private_messages_sender` FOREIGN KEY (`sender_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `private_messages`
--

LOCK TABLES `private_messages` WRITE;
/*!40000 ALTER TABLE `private_messages` DISABLE KEYS */;
/*!40000 ALTER TABLE `private_messages` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publication_tags`
--

DROP TABLE IF EXISTS `publication_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publication_tags` (
  `publication_id` int unsigned NOT NULL,
  `tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`publication_id`,`tag_id`),
  KEY `tag_id` (`tag_id`),
  CONSTRAINT `publication_tags_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `publication_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publication_tags`
--

LOCK TABLES `publication_tags` WRITE;
/*!40000 ALTER TABLE `publication_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `publication_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publications`
--

DROP TABLE IF EXISTS `publications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publications` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `categorie_id` int unsigned NOT NULL,
  `titre` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contenu` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_publication` enum('discussion','question','annonce') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'discussion',
  `statut` enum('ouvert','resolu','ferme','epingle') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'ouvert',
  `commentaires_fermes` tinyint(1) NOT NULL DEFAULT '0',
  `est_signale` tinyint(1) NOT NULL DEFAULT '0',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modification` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pub_categorie` (`categorie_id`),
  KEY `idx_pub_utilisateur` (`utilisateur_id`),
  KEY `idx_pub_statut` (`statut`),
  KEY `idx_pub_signale` (`est_signale`),
  KEY `idx_pub_type` (`type_publication`),
  CONSTRAINT `publications_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `publications_ibfk_2` FOREIGN KEY (`categorie_id`) REFERENCES `categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publications`
--

LOCK TABLES `publications` WRITE;
/*!40000 ALTER TABLE `publications` DISABLE KEYS */;
INSERT INTO `publications` VALUES (1,8,1,'Rentrée 2026 - Informations importantes','Chers étudiants, la rentrée officielle est fixée au 5 octobre 2026.','annonce','epingle',0,0,'2026-10-28 20:53:26',NULL),(2,4,3,'Rappel : Rendu du projet Laravel','N\'oubliez pas de rendre vos projets avant vendredi minuit.','annonce','ouvert',0,0,'2026-10-28 20:53:26',NULL),(3,14,1,'mohamed','sjnpsjnjvouvn','discussion','ouvert',0,0,'2026-11-07 15:08:16',NULL),(4,12,3,'salut','momo','question','ouvert',0,0,'2026-11-08 10:41:56',NULL),(5,11,1,'sujet deconomie','un sujet deconomie bien fait','discussion','ouvert',0,0,'2026-11-09 11:07:26',NULL),(6,11,1,'mohamed','momomo','discussion','ouvert',0,0,'2026-11-09 13:44:40',NULL),(7,14,1,'je sui dan le public','jipdiship;','discussion','ouvert',0,0,'2026-11-16 15:33:39',NULL);
/*!40000 ALTER TABLE `publications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publications_mentions`
--

DROP TABLE IF EXISTS `publications_mentions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publications_mentions` (
  `publication_id` int unsigned NOT NULL,
  `utilisateur_id` int unsigned NOT NULL,
  `mentionne_par` int unsigned NOT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`publication_id`,`utilisateur_id`),
  KEY `idx_publications_mentions_user` (`utilisateur_id`,`publication_id`),
  KEY `mentionne_par` (`mentionne_par`),
  CONSTRAINT `publications_mentions_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `publications_mentions_ibfk_2` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `publications_mentions_ibfk_3` FOREIGN KEY (`mentionne_par`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publications_mentions`
--

LOCK TABLES `publications_mentions` WRITE;
/*!40000 ALTER TABLE `publications_mentions` DISABLE KEYS */;
/*!40000 ALTER TABLE `publications_mentions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publications_sauvegardees`
--

DROP TABLE IF EXISTS `publications_sauvegardees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publications_sauvegardees` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `publication_id` int unsigned NOT NULL,
  `date_sauvegarde` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_publication_sauvegardee` (`utilisateur_id`,`publication_id`),
  KEY `publication_id` (`publication_id`),
  CONSTRAINT `publications_sauvegardees_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `publications_sauvegardees_ibfk_2` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publications_sauvegardees`
--

LOCK TABLES `publications_sauvegardees` WRITE;
/*!40000 ALTER TABLE `publications_sauvegardees` DISABLE KEYS */;
INSERT INTO `publications_sauvegardees` VALUES (3,14,3,'2026-11-07 19:49:58');
/*!40000 ALTER TABLE `publications_sauvegardees` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `publications_tags`
--

DROP TABLE IF EXISTS `publications_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `publications_tags` (
  `publication_id` int unsigned NOT NULL,
  `tag_id` int unsigned NOT NULL,
  PRIMARY KEY (`publication_id`,`tag_id`),
  KEY `idx_publications_tags_tag` (`tag_id`,`publication_id`),
  CONSTRAINT `publications_tags_ibfk_1` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE,
  CONSTRAINT `publications_tags_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tags_publication` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `publications_tags`
--

LOCK TABLES `publications_tags` WRITE;
/*!40000 ALTER TABLE `publications_tags` DISABLE KEYS */;
INSERT INTO `publications_tags` VALUES (6,1);
/*!40000 ALTER TABLE `publications_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reactions`
--

DROP TABLE IF EXISTS `reactions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reactions` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `utilisateur_id` int unsigned NOT NULL,
  `type_cible` enum('publication','commentaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cible_id` int unsigned NOT NULL,
  `type_reaction` enum('like','jadore','utile','haha','wouah','triste') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'like',
  `date_reaction` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_reaction` (`utilisateur_id`,`type_cible`,`cible_id`),
  KEY `idx_reactions_cible` (`type_cible`,`cible_id`),
  CONSTRAINT `reactions_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reactions`
--

LOCK TABLES `reactions` WRITE;
/*!40000 ALTER TABLE `reactions` DISABLE KEYS */;
INSERT INTO `reactions` VALUES (1,1,'publication',1,'utile','2026-10-28 20:53:26'),(2,2,'publication',1,'like','2026-10-28 20:53:26'),(3,14,'commentaire',2,'like','2026-11-07 20:02:35'),(17,20,'publication',3,'like','2026-11-09 15:06:53'),(24,14,'publication',7,'like','2026-11-16 19:28:20');
/*!40000 ALTER TABLE `reactions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `signalements`
--

DROP TABLE IF EXISTS `signalements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `signalements` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `auteur_id` int unsigned NOT NULL,
  `type_cible` enum('publication','commentaire') COLLATE utf8mb4_unicode_ci NOT NULL,
  `cible_id` int unsigned NOT NULL,
  `motif` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('en_attente','traite','rejete') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `traite_par` int unsigned DEFAULT NULL,
  `date_signalement` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_traitement` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `auteur_id` (`auteur_id`),
  KEY `idx_signalements_statut` (`statut`),
  KEY `fk_signalements_traite_par` (`traite_par`),
  CONSTRAINT `fk_signalements_traite_par` FOREIGN KEY (`traite_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `signalements_ibfk_1` FOREIGN KEY (`auteur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE,
  CONSTRAINT `signalements_ibfk_2` FOREIGN KEY (`traite_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `signalements`
--

LOCK TABLES `signalements` WRITE;
/*!40000 ALTER TABLE `signalements` DISABLE KEYS */;
INSERT INTO `signalements` VALUES (3,1,'commentaire',3,'Contenu suspect ou inapproprié.','en_attente',NULL,'2026-11-16 14:21:40',NULL),(4,2,'publication',3,'Titre non explicite et contenu vide.','en_attente',NULL,'2026-11-16 14:21:40',NULL);
/*!40000 ALTER TABLE `signalements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sondage_options`
--

DROP TABLE IF EXISTS `sondage_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sondage_options` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `publication_id` int unsigned NOT NULL,
  `texte` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ordre` int NOT NULL DEFAULT '0',
  `nb_votes` int unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `fk_sondage_options_publication` (`publication_id`),
  CONSTRAINT `fk_sondage_options_publication` FOREIGN KEY (`publication_id`) REFERENCES `publications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sondage_options`
--

LOCK TABLES `sondage_options` WRITE;
/*!40000 ALTER TABLE `sondage_options` DISABLE KEYS */;
INSERT INTO `sondage_options` VALUES (1,4,'oui',0,0),(2,4,'non',1,0);
/*!40000 ALTER TABLE `sondage_options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sondage_votes`
--

DROP TABLE IF EXISTS `sondage_votes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sondage_votes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `option_id` int unsigned NOT NULL,
  `utilisateur_id` int unsigned NOT NULL,
  `date_vote` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_vote_user_publication` (`option_id`,`utilisateur_id`),
  KEY `fk_sondage_votes_utilisateur` (`utilisateur_id`),
  CONSTRAINT `fk_sondage_votes_option` FOREIGN KEY (`option_id`) REFERENCES `sondage_options` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_sondage_votes_utilisateur` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sondage_votes`
--

LOCK TABLES `sondage_votes` WRITE;
/*!40000 ALTER TABLE `sondage_votes` DISABLE KEYS */;
INSERT INTO `sondage_votes` VALUES (1,1,17,'2026-11-08 10:42:38'),(2,1,11,'2026-11-08 12:33:13'),(3,2,14,'2026-11-16 14:55:14');
/*!40000 ALTER TABLE `sondage_votes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags`
--

DROP TABLE IF EXISTS `tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags`
--

LOCK TABLES `tags` WRITE;
/*!40000 ALTER TABLE `tags` DISABLE KEYS */;
INSERT INTO `tags` VALUES (6,'examen'),(5,'html-css'),(4,'javascript'),(1,'laravel'),(3,'mysql'),(2,'php'),(8,'projet'),(10,'reseaux'),(9,'stage'),(7,'tp');
/*!40000 ALTER TABLE `tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `tags_publication`
--

DROP TABLE IF EXISTS `tags_publication`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `tags_publication` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `statut` enum('en_attente','actif','inactif') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'en_attente',
  `cree_par` int unsigned DEFAULT NULL,
  `valide_par` int unsigned DEFAULT NULL,
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nom` (`nom`),
  KEY `cree_par` (`cree_par`),
  KEY `valide_par` (`valide_par`),
  CONSTRAINT `tags_publication_ibfk_1` FOREIGN KEY (`cree_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL,
  CONSTRAINT `tags_publication_ibfk_2` FOREIGN KEY (`valide_par`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tags_publication`
--

LOCK TABLES `tags_publication` WRITE;
/*!40000 ALTER TABLE `tags_publication` DISABLE KEYS */;
INSERT INTO `tags_publication` VALUES (1,'momo','actif',11,NULL,'2026-11-09 13:44:40'),(2,'laravel','actif',NULL,NULL,'2026-11-16 14:18:15'),(3,'php','actif',NULL,NULL,'2026-11-16 14:18:15'),(4,'mysql','actif',NULL,NULL,'2026-11-16 14:18:15'),(5,'javascript','actif',NULL,NULL,'2026-11-16 14:18:15'),(6,'html-css','actif',NULL,NULL,'2026-11-16 14:18:15'),(7,'examen','actif',NULL,NULL,'2026-11-16 14:18:15'),(8,'tp','actif',NULL,NULL,'2026-11-16 14:18:15'),(9,'projet','actif',NULL,NULL,'2026-11-16 14:18:15'),(10,'stage','actif',NULL,NULL,'2026-11-16 14:18:15'),(11,'reseaux','actif',NULL,NULL,'2026-11-16 14:18:15'),(12,'python','actif',NULL,NULL,'2026-11-16 14:18:15'),(13,'fastapi','actif',NULL,NULL,'2026-11-16 14:18:15'),(14,'git','actif',NULL,NULL,'2026-11-16 14:18:15'),(15,'api','actif',NULL,NULL,'2026-11-16 14:18:15'),(16,'debug','actif',NULL,NULL,'2026-11-16 14:18:15');
/*!40000 ALTER TABLE `tags_publication` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `utilisateurs`
--

DROP TABLE IF EXISTS `utilisateurs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `utilisateurs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `prenom` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mot_de_passe` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `avatar_url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `biographie` text COLLATE utf8mb4_unicode_ci,
  `filiere` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `modules_enseignes` text COLLATE utf8mb4_unicode_ci,
  `role` enum('etudiant','professeur','administrateur') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'etudiant',
  `est_actif` tinyint(1) NOT NULL DEFAULT '1',
  `date_inscription` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `derniere_connexion` datetime DEFAULT NULL,
  `derniere_activite` datetime DEFAULT NULL,
  `filiere_id` int unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `fk_utilisateurs_filiere` (`filiere_id`),
  KEY `idx_utilisateurs_derniere_activite` (`est_actif`,`derniere_activite`),
  CONSTRAINT `fk_utilisateurs_filiere` FOREIGN KEY (`filiere_id`) REFERENCES `filieres` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `utilisateurs`
--

LOCK TABLES `utilisateurs` WRITE;
/*!40000 ALTER TABLE `utilisateurs` DISABLE KEYS */;
INSERT INTO `utilisateurs` VALUES (1,'Kouassi','Momo','momo.kouassi@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Étudiant en BTS IDA, passionné de développement web.','BTS IDA',NULL,'etudiant',1,'2026-10-28 20:53:26',NULL,'2026-11-07 13:58:07',1),(2,'Traoré','Sarah','sarah.traore@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Étudiante en BTS IDA.','BTS IDA',NULL,'etudiant',1,'2026-10-28 20:53:26',NULL,NULL,1),(3,'Diallo','Jean','jean.diallo@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Étudiant en Licence Informatique.','Licence Informatique',NULL,'etudiant',1,'2026-10-28 20:53:26',NULL,NULL,2),(4,'N\'Guessan','Awa','awa.nguessan@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Chef de classe (Modérateur) BTS IDA.','BTS IDA',NULL,'etudiant',1,'2026-10-28 20:53:26',NULL,NULL,1),(5,'Yao','Kofi','kofi.yao@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Délégué Général des étudiants.','Licence Informatique',NULL,'etudiant',1,'2026-10-28 20:53:26',NULL,'2026-11-09 14:38:19',2),(6,'Koné','Dr. Ibrahim','ibrahim.kone@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Professeur de Développement Web.',NULL,'Développement Web, Base de données','professeur',1,'2026-10-28 20:53:26',NULL,'2026-11-07 12:55:43',NULL),(7,'Bamba','Fatou','fatou.bamba@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Professeure de Réseaux.',NULL,'Réseaux, Sécurité','professeur',1,'2026-10-28 20:53:26',NULL,NULL,NULL),(8,'Admin','Système','admin@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Compte administrateur principal.',NULL,NULL,'administrateur',1,'2026-10-28 20:53:26',NULL,'2026-11-09 14:39:36',NULL),(9,'Modo','Paul','paul.modo@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Modérateur global de la plateforme.',NULL,NULL,'etudiant',1,'2026-10-28 20:53:26',NULL,NULL,NULL),(10,'Bakayoko','Amadou','amadou.bakayoko@epi-yakro.ci','$2y$12$8F/UiuqNU1NEs611GD4lBOxmZHqqD/4bwZhn3kNpevRAluU0S6ILG',NULL,'Étudiant en Génie Mécanique.','Génie Mécanique',NULL,'etudiant',1,'2026-10-28 20:53:26',NULL,NULL,3),(11,'Shadow','Darck','darckshafow@gmail.com','$2y$12$XNz8Fj6GMAB0aWTHGJOvcuGmkuj9aTv2UKjTvmWvD7nuvXCXnMeYy','uploads/avatars/c4afc035fa0be24b29e53f942343a417.jpg','','BTS IDA',NULL,'etudiant',1,'2026-10-28 20:55:32',NULL,'2026-11-09 14:07:10',1),(12,'Shadow','Darck','mohamed@gmail.com','$2y$12$K6TZscQU/i.EP6odoElf8uxF9Q1wdGLSII5Vi5MqZs.CkCdqahu5e',NULL,NULL,'BTS IDA',NULL,'etudiant',1,'2026-11-04 08:47:32',NULL,'2026-11-08 10:45:01',1),(13,'Test','Etudiant','test.etudiant.20261105.1216@epi-yakro.ci','$2y$12$Ewsc0qCV0gBXVgszuoFDi.Mib1F./Fz/L2aDi7Fmba/8HJdevdZ2O',NULL,NULL,'BTS IDA',NULL,'etudiant',1,'2026-11-05 12:19:34',NULL,NULL,1),(14,'Shadow','Darck','darckshafow@epi-yakro.ci','$2y$12$PSk6wC7O6R1SpU0PdckJsOCUlvNBFPjGKQ13Ika1JfK4WDkomHJcO','uploads/avatars/2def71d6a7c053b9215d8c876557622c.jpg','je suis le meilleur','BTS IDA',NULL,'etudiant',1,'2026-11-05 12:29:45',NULL,'2026-11-16 19:43:28',1),(15,'TestGmail','Etudiant','epi.test.gmail.1793882035771@gmail.com','$2y$12$BKhS6ufToH2FcZer/R6WSOWKBcMhzC1atzhVp/jxxBMPvIRwum3xa',NULL,NULL,'BTS IDA',NULL,'etudiant',1,'2026-11-05 12:33:58',NULL,'2026-11-05 13:07:40',1),(16,'mohamed','momo','mohamed@yakro-epi.ci','$2y$12$nKeo22yCDJBbL0wZwI6euuTovqhQx/5.x.40NxkP3KEN8uaq7MjGm','uploads/avatars/d95617443c94424dd50ef9b70439f1d2.jpg','','BTS IDA',NULL,'etudiant',1,'2026-11-07 20:10:49',NULL,'2026-11-07 20:23:39',1),(17,'sala','kata','darckshafo@gmail.com','$2y$12$AxtnMgPjSTG0cBDw/lOmUOu/9TodVoRiYic2Ihh6xaLKKP4WpZLiO','uploads/avatars/b86cb55241f59bca75a5ee67e1f12c61.jpg','','BTS IDA',NULL,'etudiant',1,'2026-11-08 10:13:57',NULL,'2026-11-09 15:56:42',1),(19,'Saad','Yasmine','yasminesad@gmail.com','$2y$12$VEvdGA065Gx/XKOLnmzLauqMj3u2qyB9NqvUpSFFVyTYd2KH6qczO','uploads/avatars/dde98e1b614e09e1bd4f2eb137f0a922.jpg',NULL,'BTS IDA',NULL,'etudiant',1,'2026-11-09 14:21:24',NULL,'2026-11-09 14:45:00',1),(20,'L\'homme qui pleure jamais','Capelo','romariccapelo8@gmail.com','$2y$12$JYySYFGbmJW1OcMkayBNj.XFpyYc5/5eB3F8vk3fCWVOU6Q8EHVJK','uploads/avatars/31582d75b7770e7904a01382da18f7d4.jpg','','BTS IDA',NULL,'etudiant',1,'2026-11-09 14:56:17',NULL,'2026-11-09 16:04:29',1),(21,'kouasso','ariel','jdupon@gmail.com','$2y$12$PUDRTgSl/8/lCHkNPK1zjefuUwLMeju7BBUFeZEVCfd7gdTz.PYaO',NULL,NULL,'BTS IDA',NULL,'etudiant',1,'2026-11-09 15:37:17',NULL,'2026-11-09 15:58:46',1);
/*!40000 ALTER TABLE `utilisateurs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `zones`
--

DROP TABLE IF EXISTS `zones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `zones` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `nom` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_zone` enum('generale','filiere','professeurs','delegation') COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `createur_id` int unsigned DEFAULT NULL,
  `est_active` tinyint(1) NOT NULL DEFAULT '1',
  `date_creation` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `createur_id` (`createur_id`),
  CONSTRAINT `zones_ibfk_1` FOREIGN KEY (`createur_id`) REFERENCES `utilisateurs` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `zones`
--

LOCK TABLES `zones` WRITE;
/*!40000 ALTER TABLE `zones` DISABLE KEYS */;
INSERT INTO `zones` VALUES (1,'Zone Générale - Vie de l\'École','generale','Espace public pour les annonces officielles et la vie étudiante.',8,1,'2026-10-28 20:53:26'),(2,'Filière BTS IDA','filiere','Espace dédié aux étudiants et professeurs de BTS IDA.',4,1,'2026-10-28 20:53:26'),(3,'Filière Licence Informatique','filiere','Espace dédié aux étudiants de Licence Informatique.',8,1,'2026-10-28 20:53:26'),(4,'Espace Professeurs','professeurs','Zone restreinte réservée aux enseignants.',8,1,'2026-10-28 20:53:26'),(5,'Bureau de la Délégation','delegation','Espace de travail de la Délégation étudiante.',5,1,'2026-10-28 20:53:26');
/*!40000 ALTER TABLE `zones` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-16 19:43:34

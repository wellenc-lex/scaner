/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Current Database: `scaner`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `scaner` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */;

USE `scaner`;

--
-- Table structure for table `gitscanpassive`
--

DROP TABLE IF EXISTS `gitscanpassive`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `gitscanpassive` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `companyid` int(11) NOT NULL,
  `companyname` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `repourl` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `companyurl` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `userid` int(11) NOT NULL,
  `is_active` int(11) NOT NULL DEFAULT '1',
  `last_scan_monthday` int(11) DEFAULT NULL,
  `gitscan_previous` longtext COLLATE utf8mb4_unicode_ci,
  `gitscan_new` longtext COLLATE utf8mb4_unicode_ci,
  `needs_to_notify` int(11) NOT NULL,
  `token` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '0',
  `viewed` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `gitscanpassive`
--

LOCK TABLES `gitscanpassive` WRITE;
/*!40000 ALTER TABLE `gitscanpassive` DISABLE KEYS */;
/*!40000 ALTER TABLE `gitscanpassive` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `passive_scan`
--

DROP TABLE IF EXISTS `passive_scan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `passive_scan` (
  `PassiveScanid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `notifications_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `scanday` tinyint(1) NOT NULL,
  `dirscanUrl` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dirscanIP` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amassDomain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nmapDomain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amass_previous` longtext COLLATE utf8mb4_unicode_ci,
  `amass_new` longtext COLLATE utf8mb4_unicode_ci,
  `nmap_previous` longtext COLLATE utf8mb4_unicode_ci,
  `nmap_new` longtext COLLATE utf8mb4_unicode_ci,
  `dirscan_previous` longtext COLLATE utf8mb4_unicode_ci,
  `dirscan_new` longtext COLLATE utf8mb4_unicode_ci,
  `gitscan` longtext COLLATE utf8mb4_unicode_ci,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `user_notified` tinyint(1) NOT NULL DEFAULT '0',
  `needs_to_notify` tinyint(1) NOT NULL DEFAULT '0',
  `notify_instrument` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_scan_monthday` int(11) NOT NULL DEFAULT '0',
  `viewed` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`PassiveScanid`),
  KEY `userid` (`userid`),
  KEY `scanid` (`PassiveScanid`),
  KEY `last_scan_monthday` (`last_scan_monthday`),
  KEY `notifications_enabled` (`notifications_enabled`),
  CONSTRAINT `passive_scan_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10219 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `taskid` int(11) DEFAULT NULL,
  `passivescan` tinyint(1) NOT NULL DEFAULT '0',
  `instrument` int(11) NOT NULL,
  `working` int(11) NOT NULL DEFAULT '0',
  `todelete` int(11) NOT NULL DEFAULT '0',
  `amassdomain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wordlist` tinyint(4) DEFAULT '0',
  `dirscanUrl` mediumtext COLLATE utf8mb4_unicode_ci,
  `dirscanIP` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nucleiUrl` varchar(1000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nmap` varchar(6000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vhostport` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vhostip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vhostdomain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vhostssl` tinyint(1) DEFAULT NULL,
  UNIQUE KEY `id` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=13458 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


--
-- Table structure for table `sent_email`
--

DROP TABLE IF EXISTS `sent_email`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sent_email` (
  `emailid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL,
  `type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` datetime NOT NULL,
  `scanid` int(11) NOT NULL,
  PRIMARY KEY (`emailid`),
  KEY `userid` (`userid`),
  CONSTRAINT `sent_email_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;


DROP TABLE IF EXISTS `tasks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tasks` (
  `taskid` int(11) NOT NULL AUTO_INCREMENT,
  `userid` int(11) NOT NULL DEFAULT '10',
  `notification_enabled` tinyint(1) DEFAULT '1',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'Working',
  `host` mediumtext COLLATE utf8mb4_unicode_ci,
  `nmap` longtext COLLATE utf8mb4_unicode_ci,
  `amass` longtext COLLATE utf8mb4_unicode_ci,
  `nuclei` longtext COLLATE utf8mb4_unicode_ci,
  `amass_intel` longtext COLLATE utf8mb4_unicode_ci,
  `subtakeover` mediumtext COLLATE utf8mb4_unicode_ci,
  `aquatone` longtext COLLATE utf8mb4_unicode_ci,
  `dirscan` longtext COLLATE utf8mb4_unicode_ci,
  `wayback` longtext COLLATE utf8mb4_unicode_ci,
  `gitscan` longtext COLLATE utf8mb4_unicode_ci,
  `ips` mediumtext COLLATE utf8mb4_unicode_ci,
  `vhost` longtext COLLATE utf8mb4_unicode_ci,
  `vhostwordlist` longtext COLLATE utf8mb4_unicode_ci,
  `js` longtext COLLATE utf8mb4_unicode_ci,
  `reverseip` longtext COLLATE utf8mb4_unicode_ci,
  `nmap_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amass_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dirscan_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gitscan_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ips_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vhost_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `js_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reverseip_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` datetime DEFAULT NULL,
  `notified` mediumint(9) NOT NULL DEFAULT '0',
  `notify_instrument` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `hidden` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`taskid`),
  UNIQUE KEY `taskid` (`taskid`),
  KEY `userid` (`userid`),
  KEY `notification_enabled` (`notification_enabled`),
  KEY `status` (`status`),
  CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`userid`) REFERENCES `user` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11232 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tools_amount`
--

DROP TABLE IF EXISTS `tools_amount`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tools_amount` (
  `id` smallint(6) NOT NULL AUTO_INCREMENT,
  `amass` smallint(6) NOT NULL DEFAULT '0',
  `nmap` smallint(6) NOT NULL DEFAULT '0',
  `vhosts` smallint(6) NOT NULL DEFAULT '0',
  `dirscan` smallint(6) NOT NULL DEFAULT '0',
  `googlescan` smallint(6) NOT NULL DEFAULT '0',
  `gitscan` smallint(6) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `tools_amount`
--

LOCK TABLES `tools_amount` WRITE;
/*!40000 ALTER TABLE `tools_amount` DISABLE KEYS */;
INSERT INTO `tools_amount` VALUES (1,0,0,0,0,0,0);
/*!40000 ALTER TABLE `tools_amount` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `auth_key` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_reset_token` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` int(11) NOT NULL DEFAULT '10',
  `rights` int(11) NOT NULL DEFAULT '0',
  `created_at` int(11) NOT NULL,
  `updated_at` int(11) DEFAULT NULL,
  `scans_counter` int(11) NOT NULL DEFAULT '0',
  `scan_timeout` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `password_reset_token` (`password_reset_token`),
  KEY `id` (`id`),
  KEY `status` (`status`),
  KEY `scans_counter` (`scans_counter`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user`
--
ALTER TABLE `queue` ADD `ipscan` TEXT NULL DEFAULT NULL AFTER `nmap`; 
ALTER TABLE `tasks` ADD `aquatone_status` VARCHAR(20) NULL DEFAULT NULL AFTER `amass_status`;
ALTER TABLE `tasks` ADD `whatweb` MEDIUMTEXT NULL DEFAULT NULL AFTER `js`; 
ALTER TABLE `tasks` ADD `forbiddenbypass` MEDIUMTEXT NULL DEFAULT NULL AFTER `js`; 
ALTER TABLE `queue` CHANGE `nmap` `nmap` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NULL DEFAULT NULL; 
ALTER TABLE `queue` ADD `date_modified` DATE NULL DEFAULT NULL AFTER `vhostssl`; 
ALTER TABLE `tasks` ADD `vhostwordlistmanual` LONGTEXT NULL DEFAULT NULL AFTER `vhostwordlist`; 
ALTER TABLE `passive_scan` ADD `amass_ips` LONGTEXT NULL DEFAULT NULL AFTER `amass_new`;
ALTER TABLE `passive_scan`  ADD `amass_ips_new` LONGTEXT NULL DEFAULT NULL  AFTER `amass_new`;
ALTER TABLE `passive_scan`  ADD `amass_ips_old` LONGTEXT NULL DEFAULT NULL  AFTER `amass_new`;
LOCK TABLES `user` WRITE;
/*!40000 ALTER TABLE `user` DISABLE KEYS */;
INSERT INTO `user` VALUES (10,'k8RkFpLhU44Bqgal0tKQNYp-e7mE-e9A','$2y$12$5A6Y7v1gKaNtYsRrZsHiUe7VXsxe.v2iiprJm/2tH5RMVSKCIvtYe',NULL,'admin@admin.com',10,0,1575122687,1633015109,3720,1630432689);
/*!40000 ALTER TABLE `user` ENABLE KEYS */;
UNLOCK TABLES;

CREATE TABLE `amassintel` (
  `id` int(11) NOT NULL,
  `domains` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `amassintel`
  ADD PRIMARY KEY (`id`);

INSERT INTO `amassintel` (`id`, `domains`) VALUES ('1', '[]');

ALTER TABLE `passive_scan` ADD `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `viewed`;
ALTER TABLE `passive_scan` CHANGE `last_scan_monthday` `last_scan_monthday` VARCHAR(55) NOT NULL DEFAULT '0';

CREATE TABLE `scaner`.`whatweb` ( `id` INT NOT NULL AUTO_INCREMENT , `url` VARCHAR(16000) NOT NULL , `ip` VARCHAR(50) NOT NULL , `tech` JSON NULL DEFAULT NULL , `favicon` VARCHAR(100) NOT NULL , `date` DATE NULL DEFAULT NULL, PRIMARY KEY (`id`)) ENGINE = InnoDB; 
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

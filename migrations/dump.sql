-- MySQL dump 10.13  Distrib 5.7.27, for Win32 (AMD64)
--
-- Host: localhost    Database: vepay
-- ------------------------------------------------------
-- Server version	5.7.29-0ubuntu0.18.04.1

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
-- Table structure for table `access_sms`
--

DROP TABLE IF EXISTS `access_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `access_sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) DEFAULT NULL,
  `public_key` varchar(255) DEFAULT NULL,
  `secret_key` varchar(255) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_id_idx` (`partner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `act_bank`
--

DROP TABLE IF EXISTS `act_bank`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `act_bank` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ActPeriod` int(10) unsigned NOT NULL,
  `DateCreate` int(10) unsigned NOT NULL,
  `FileName` varchar(250) NOT NULL,
  `SumOCT` int(11) NOT NULL,
  `SumPerevod` int(11) NOT NULL,
  `SumPaysJkh` int(11) NOT NULL,
  `SumPaysEcom` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ActPeriod` (`ActPeriod`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `act_mfo`
--

DROP TABLE IF EXISTS `act_mfo`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `act_mfo` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partner mfo',
  `NumAct` int(10) unsigned NOT NULL DEFAULT '1' COMMENT 'nomer acta',
  `ActPeriod` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'period unixts',
  `CntPerevod` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'chislo perevodov',
  `SumPerevod` bigint(20) NOT NULL DEFAULT '0' COMMENT 'summa perevodov',
  `ComisPerevod` bigint(20) NOT NULL DEFAULT '0' COMMENT 'komissia po perevodam',
  `DateCreate` int(10) unsigned NOT NULL COMMENT 'data formirovania',
  `FileName` varchar(250) DEFAULT NULL COMMENT 'fail',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  `SumVozvrat` bigint(20) NOT NULL DEFAULT '0' COMMENT 'summa vozvrata perevodov',
  `CntVyplata` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'chislo vyplat',
  `SumVyplata` bigint(20) NOT NULL DEFAULT '0' COMMENT 'summa vyplat',
  `ComisVyplata` bigint(20) NOT NULL DEFAULT '0' COMMENT 'komissia po vyplatam',
  `BeginOstatokPerevod` bigint(20) NOT NULL DEFAULT '0' COMMENT ' nachalnyii ostatok po perevodam',
  `BeginOstatokVyplata` bigint(20) NOT NULL DEFAULT '0' COMMENT 'nachalnyii ostatok po vyplate',
  `EndOstatokPerevod` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ostatok po perevodam',
  `EndOstatokVyplata` bigint(20) NOT NULL DEFAULT '0' COMMENT 'ostatok po vyplate',
  `SumPerechislen` bigint(20) NOT NULL DEFAULT '0' COMMENT 'perechsilennaya summa po perevodam',
  `SumPostuplen` bigint(20) NOT NULL DEFAULT '0' COMMENT 'postupivshaya summa dlia vydachi',
  `BeginOstatokVoznag` bigint(20) NOT NULL DEFAULT '0',
  `EndOstatokVoznag` bigint(20) NOT NULL DEFAULT '0',
  `SumPodlejUderzOspariv` bigint(20) NOT NULL DEFAULT '0',
  `SumPodlejVozmeshOspariv` bigint(20) NOT NULL DEFAULT '0',
  `SumPerechKontrag` bigint(20) NOT NULL DEFAULT '0',
  `SumPerechObespech` bigint(20) NOT NULL DEFAULT '0',
  `IsPublic` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - opublicovan',
  `IsOplat` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - oplachen',
  `SumSchetComisVyplata` bigint(20) NOT NULL DEFAULT '0',
  `SumSchetComisPerevod` bigint(20) NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IdPartner` (`IdPartner`,`ActPeriod`)
) ENGINE=InnoDB AUTO_INCREMENT=107 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `act_schet`
--

DROP TABLE IF EXISTS `act_schet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `act_schet` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL,
  `IdAct` int(10) unsigned NOT NULL DEFAULT '0',
  `NumSchet` int(10) unsigned NOT NULL DEFAULT '0',
  `SumSchet` bigint(20) NOT NULL DEFAULT '0',
  `DateSchet` int(10) unsigned NOT NULL DEFAULT '0',
  `Komment` varchar(255) DEFAULT NULL,
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IdPartner_idx` (`IdPartner`),
  KEY `IdAct_idx` (`IdAct`),
  KEY `DateSchet_idx` (`DateSchet`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alarms_send`
--

DROP TABLE IF EXISTS `alarms_send`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alarms_send` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdSetting` int(10) unsigned NOT NULL COMMENT 'id alarms_settings',
  `IdPay` int(10) unsigned NOT NULL COMMENT 'id pay_schet',
  `TypeSend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip 0 - proverka 1 - otpravka ob oshibke',
  `DateSend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data intravki',
  PRIMARY KEY (`ID`),
  KEY `pay_schet_idx` (`IdSetting`,`IdPay`)
) ENGINE=InnoDB AUTO_INCREMENT=173 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `alarms_settings`
--

DROP TABLE IF EXISTS `alarms_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alarms_settings` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `TypeAlarm` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip opoveshenia',
  `TimeAlarm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'nastroika minut',
  `EmailAlarm` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'email dlia opoveschenia',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `TypeAlarm` (`TypeAlarm`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_asn`
--

DROP TABLE IF EXISTS `antifraud_asn`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_asn` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `asn` varchar(255) DEFAULT NULL,
  `provider` varchar(255) DEFAULT NULL,
  `num_ips` int(11) DEFAULT NULL,
  `num_fails` int(11) DEFAULT '0',
  `is_black` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `asn` (`asn`)
) ENGINE=InnoDB AUTO_INCREMENT=2501 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_bin_banks`
--

DROP TABLE IF EXISTS `antifraud_bin_banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_bin_banks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bin` int(11) DEFAULT NULL,
  `payment_system` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `bin` (`bin`)
) ENGINE=InnoDB AUTO_INCREMENT=343064 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_card_ips`
--

DROP TABLE IF EXISTS `antifraud_card_ips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_card_ips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_hash` varchar(255) DEFAULT NULL,
  `ip_num` bigint(20) DEFAULT NULL,
  `finger_print_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `finger_print_id` (`finger_print_id`),
  KEY `ip_num` (`ip_num`),
  KEY `card_hash` (`card_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=143 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_cards`
--

DROP TABLE IF EXISTS `antifraud_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_cards` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_hash` varchar(255) DEFAULT NULL,
  `is_black` tinyint(1) DEFAULT NULL,
  `finger_print_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `card_hash` (`card_hash`),
  KEY `finger_print_id` (`finger_print_id`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_country`
--

DROP TABLE IF EXISTS `antifraud_country`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_country` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `country` varchar(255) DEFAULT NULL,
  `finger_print_id` int(11) DEFAULT NULL,
  `user_hash` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `finger_print_id` (`finger_print_id`),
  KEY `user_hash` (`user_hash`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_finger_print`
--

DROP TABLE IF EXISTS `antifraud_finger_print`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_finger_print` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_hash` varchar(255) DEFAULT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `status` tinyint(1) DEFAULT NULL,
  `weight` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_hash` (`user_hash`),
  KEY `transaction_id_idx` (`transaction_id`)
) ENGINE=InnoDB AUTO_INCREMENT=438 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_ip`
--

DROP TABLE IF EXISTS `antifraud_ip`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_ip` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `is_black` tinyint(1) DEFAULT NULL,
  `finger_print_id` int(11) DEFAULT NULL,
  `ip_number` bigint(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `finger_print_id` (`finger_print_id`),
  KEY `ip_number` (`ip_number`)
) ENGINE=InnoDB AUTO_INCREMENT=148 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_refund_card`
--

DROP TABLE IF EXISTS `antifraud_refund_card`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_refund_card` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `validated` tinyint(1) DEFAULT NULL,
  `card_hash` varchar(255) DEFAULT NULL,
  `partner_id` int(11) DEFAULT NULL,
  `ext_id` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `card_hash` (`card_hash`),
  KEY `partner_id` (`partner_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_rule_info`
--

DROP TABLE IF EXISTS `antifraud_rule_info`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_rule_info` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rule` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `rule_title` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `critical_value` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_settings`
--

DROP TABLE IF EXISTS `antifraud_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `key` varchar(255) DEFAULT NULL,
  `value` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `antifraud_stat`
--

DROP TABLE IF EXISTS `antifraud_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `antifraud_stat` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `finger_print_id` int(11) DEFAULT NULL,
  `rule` varchar(255) DEFAULT NULL,
  `success` tinyint(1) DEFAULT NULL,
  `current_weight` float DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `finger_print_id` (`finger_print_id`),
  KEY `rule_idx` (`rule`,`success`)
) ENGINE=InnoDB AUTO_INCREMENT=1362 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `banks`
--

DROP TABLE IF EXISTS `banks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `banks` (
  `ID` int(10) unsigned NOT NULL,
  `Name` varchar(250) NOT NULL,
  `JkhComis` double NOT NULL DEFAULT '0',
  `JkhComisMin` double NOT NULL DEFAULT '0',
  `EcomComis` double NOT NULL DEFAULT '0',
  `EcomComisMin` double NOT NULL DEFAULT '0',
  `AFTComis` double NOT NULL DEFAULT '0',
  `AFTComisMin` double NOT NULL DEFAULT '0',
  `OCTComis` double NOT NULL DEFAULT '0',
  `OCTComisMin` double NOT NULL DEFAULT '0',
  `OCTVozn` double NOT NULL DEFAULT '0',
  `OCTVoznMin` double NOT NULL DEFAULT '0',
  `FreepayComis` double NOT NULL DEFAULT '0',
  `FreepayComisMin` double NOT NULL DEFAULT '0',
  `FreepayVozn` double NOT NULL DEFAULT '0',
  `FreepayVoznMin` double NOT NULL DEFAULT '0',
  `VyvodBankComis` double DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cardactivate`
--

DROP TABLE IF EXISTS `cardactivate`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cardactivate` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - full 1 - simple',
  `ExtId` varchar(50) DEFAULT NULL,
  `Org` int(10) unsigned NOT NULL,
  `CardNum` varchar(20) NOT NULL,
  `ControlWord` varchar(30) NOT NULL,
  `IdClientInfo` int(10) unsigned NOT NULL DEFAULT '0',
  `IdTrancact` int(10) unsigned NOT NULL DEFAULT '0',
  `DateAdd` int(10) unsigned NOT NULL DEFAULT '0',
  `DateActivate` int(10) unsigned NOT NULL DEFAULT '0',
  `State` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - in process 1 - ok 2 - error',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `cards`
--

DROP TABLE IF EXISTS `cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cards` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL COMMENT 'id user',
  `NameCard` varchar(150) DEFAULT NULL COMMENT 'naimenovanie karty',
  `ExtCardIDP` varchar(150) DEFAULT NULL COMMENT 'vneshniii id karty 128 chisel',
  `CardNumber` varchar(40) DEFAULT NULL COMMENT 'nomer karty',
  `CardType` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip karty: 0 - visa, 1 - mastercard',
  `SrokKard` int(4) unsigned NOT NULL DEFAULT '0' COMMENT 'srok deistvia karty - MMYY',
  `Status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'status karty: 0 - ne podtvejdena 1 - aktivna 2 - zablokirovana',
  `CheckSumm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa dlia proverki privyazki',
  `DateAdd` int(10) unsigned NOT NULL COMMENT 'data dobavlenia',
  `Default` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'po umolchaniu',
  `TypeCard` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - dlia oplaty 1 - dlia popolnenia',
  `CardHolder` varchar(100) DEFAULT NULL,
  `IdPan` int(10) unsigned NOT NULL DEFAULT '0',
  `IdBank` int(10) unsigned NOT NULL DEFAULT '0',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - activna 1 - udalena',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`),
  KEY `idx_IdPan` (`IdPan`),
  KEY `idx_IdBank` (`IdBank`)
) ENGINE=InnoDB AUTO_INCREMENT=290 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `crypto_keys_table`
--

DROP TABLE IF EXISTS `crypto_keys_table`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `crypto_keys_table` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `EncryptedKeyValue` varchar(250) DEFAULT NULL,
  `CreatedDate` int(11) NOT NULL,
  `UpdatedDate` int(11) NOT NULL,
  `Counter` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_Counter` (`Counter`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `distribution_reports`
--

DROP TABLE IF EXISTS `distribution_reports`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `distribution_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `partner_id` int(11) DEFAULT NULL,
  `payment` tinyint(3) DEFAULT NULL,
  `repayment` tinyint(3) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `last_send` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `partner_id` (`partner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `drafts`
--

DROP TABLE IF EXISTS `drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `drafts` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPaySchet` int(10) unsigned DEFAULT NULL COMMENT 'id pay_schet',
  `Urlico` varchar(200) DEFAULT NULL COMMENT 'ur lico',
  `Inn` varchar(20) DEFAULT NULL COMMENT 'inn',
  `Sno` varchar(20) DEFAULT NULL COMMENT 'sistema nalogooblajenia',
  `NumDocument` varchar(20) DEFAULT NULL COMMENT 'dokument',
  `NumDraft` varchar(20) DEFAULT NULL COMMENT 'check',
  `Smena` varchar(20) DEFAULT NULL COMMENT 'smena',
  `DateDraft` varchar(20) DEFAULT NULL COMMENT 'data i vremia cheka',
  `FDNumber` varchar(20) DEFAULT NULL COMMENT 'fd',
  `FPCode` varchar(20) DEFAULT NULL COMMENT 'fp',
  `KassaRegNumber` varchar(20) DEFAULT NULL COMMENT 'rn',
  `KassaSerialNumber` varchar(20) DEFAULT NULL COMMENT 'zn',
  `FNSerialNumber` varchar(20) DEFAULT NULL COMMENT 'fn',
  `Tovar` varchar(400) DEFAULT NULL COMMENT 'tovar',
  `Summ` int(10) unsigned DEFAULT '0' COMMENT 'summa',
  `SummNoNds` int(10) unsigned DEFAULT '0' COMMENT 'summa bez nds',
  `Email` varchar(50) DEFAULT NULL COMMENT 'email klienta',
  PRIMARY KEY (`ID`),
  KEY `IdPaySchet` (`IdPaySchet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `export_pay`
--

DROP TABLE IF EXISTS `export_pay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `export_pay` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdSchet` int(10) unsigned NOT NULL COMMENT 'id pay_schet',
  `DateExport` int(10) unsigned NOT NULL COMMENT 'data eksporta',
  `Transaction` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'id tranzakcii plateja',
  `IdReestrBankplat` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id systemdorod.reestr_bankplat',
  `DateSendEmail` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data otpavki uvedomlenia po email',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IdSchet` (`IdSchet`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `help`
--

DROP TABLE IF EXISTS `help`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `help` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL COMMENT 'id user',
  `Message` text NOT NULL COMMENT 'soobshenie',
  `Direct` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - vopros 1 - otvet',
  `DateMesg` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`,`Direct`,`IsDeleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `history`
--

DROP TABLE IF EXISTS `history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `history` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'istoria shtrihkodov',
  `IdUser` int(10) unsigned NOT NULL COMMENT 'id user',
  `Type` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - oplata kuponom 1 - oplata jkh 2 - bilet 3 - oplata po qr kody',
  `IdOperation` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id operacii dlia tipa 0 - qr_oplatauslugi 1 - reestr_schet 2 - id bilet_oplata 3 - id pay_schet',
  `InfoHead` varchar(100) DEFAULT NULL COMMENT 'info 1',
  `InfoText` varchar(100) DEFAULT NULL COMMENT 'info 2',
  `DateAdd` int(10) unsigned NOT NULL COMMENT 'data',
  `Summa` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `key_log`
--

DROP TABLE IF EXISTS `key_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `key_log` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data vhoda',
  `IdUser` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id user',
  `Type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '1 - ok 2 - err 3 - change pw 4 - set key1 5 - set key 2 6 - set key 3 7 - change keys 9 - exit',
  `IPLogin` varchar(30) NOT NULL DEFAULT '' COMMENT 'ip adres',
  `DopInfo` varchar(500) NOT NULL COMMENT 'info o brauzere',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`)
) ENGINE=InnoDB AUTO_INCREMENT=137 DEFAULT CHARSET=utf8 COMMENT='логи входа';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `key_users`
--

DROP TABLE IF EXISTS `key_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `key_users` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Login` varchar(20) NOT NULL COMMENT 'login',
  `Password` varchar(100) NOT NULL COMMENT 'pw sha2',
  `FIO` varchar(100) DEFAULT NULL COMMENT 'fio',
  `Email` varchar(50) DEFAULT NULL COMMENT 'email',
  `Key1Admin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'admin vvoda klucha1',
  `Key2Admin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'admin vvoda klucha2',
  `Key3Admin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'admin vvoda klucha3',
  `KeyChageAdmin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'admin zameny kychei',
  `DateChange` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data izmemenia',
  `AutoLockDate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data avtoblokirovki',
  `DateLastLogin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data poslednego vhoda',
  `IsActive` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - off 1 - on',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  `ErrorLoginCnt` int(10) unsigned DEFAULT '0',
  `DateErrorLogin` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Login` (`Login`),
  KEY `IdPartner` (`IsDeleted`,`IsActive`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `keys`
--

DROP TABLE IF EXISTS `keys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `keys` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Value` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_investor`
--

DROP TABLE IF EXISTS `kf_investor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_investor` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdOrg` int(10) NOT NULL DEFAULT '0',
  `IdUser` int(10) unsigned NOT NULL DEFAULT '0',
  `Balance` bigint(10) NOT NULL DEFAULT '0',
  `Type` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - investor 1 - zayiomshik',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `kf_orders`
--

DROP TABLE IF EXISTS `kf_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `kf_orders` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdInvestor` int(10) unsigned NOT NULL,
  `DateOp` int(10) unsigned NOT NULL,
  `SummOp` bigint(20) NOT NULL,
  `SummAfter` bigint(20) NOT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `loglogin`
--

DROP TABLE IF EXISTS `loglogin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `loglogin` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Date` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data vhoda',
  `IdUser` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id user',
  `Type` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '1 - ok 2 - err, 3 - change pw',
  `IPLogin` varchar(30) NOT NULL DEFAULT '' COMMENT 'ip adres',
  `DopInfo` varchar(500) NOT NULL COMMENT 'info o brauzere',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`)
) ENGINE=InnoDB AUTO_INCREMENT=1600 DEFAULT CHARSET=utf8 COMMENT='логи входа';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `migration`
--

DROP TABLE IF EXISTS `migration`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `migration` (
  `version` varchar(180) NOT NULL,
  `apply_time` int(11) DEFAULT NULL,
  PRIMARY KEY (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `notification_pay`
--

DROP TABLE IF EXISTS `notification_pay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `notification_pay` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPay` int(10) unsigned NOT NULL COMMENT 'id pay_schet',
  `Email` varchar(1000) DEFAULT NULL COMMENT 'to email or url',
  `TypeNotif` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - dlia polzovatelia 1 - dlia magazina',
  `DateCreate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data sozdania',
  `DateSend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data otparavki uvedomlenia',
  `SendCount` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'chislo popytok',
  `DateLastReq` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data zaprosa',
  `FullReq` text COMMENT 'polnuii adres zaprosa',
  `HttpCode` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'kod http otveta',
  `HttpAns` text COMMENT 'tekst http otveta',
  PRIMARY KEY (`ID`),
  KEY `IdPay` (`IdPay`,`DateSend`),
  KEY `idx_DateLastReq` (`DateLastReq`)
) ENGINE=InnoDB AUTO_INCREMENT=1794 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `oplatatakze`
--

DROP TABLE IF EXISTS `oplatatakze`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `oplatatakze` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FromUser` int(10) unsigned NOT NULL COMMENT 'id user',
  `FromOrg` int(10) NOT NULL DEFAULT '0' COMMENT 'id uslugatovar',
  `FromLs` varchar(100) NOT NULL DEFAULT '' COMMENT 'shablon acount',
  `IdShablon` int(10) unsigned NOT NULL COMMENT 'shanlon',
  `Period` int(10) unsigned NOT NULL COMMENT 'period',
  `DateShown` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data otobrajenia',
  PRIMARY KEY (`ID`),
  KEY `FromOrg` (`FromOrg`,`FromLs`),
  KEY `FromUser` (`FromUser`),
  KEY `Period` (`Period`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `options` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Name` varchar(255) DEFAULT NULL COMMENT 'opcia',
  `Value` varchar(255) DEFAULT NULL COMMENT 'znachenie',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Name` (`Name`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_notif`
--

DROP TABLE IF EXISTS `order_notif`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_notif` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdOrder` int(10) unsigned NOT NULL COMMENT 'id order_pay',
  `DateAdd` int(10) unsigned NOT NULL COMMENT 'data sozdania',
  `DateSended` int(10) unsigned NOT NULL COMMENT 'data otpravki',
  `TypeSend` tinyint(1) unsigned NOT NULL COMMENT 'tip otpravki - 0 - email 1 - sms',
  `StateSend` tinyint(1) unsigned NOT NULL COMMENT 'status otravki: 0 - v ocheredi 1 - uspeshno 2 - oshibka',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `order_pay`
--

DROP TABLE IF EXISTS `order_pay`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `order_pay` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `DateAdd` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data vystavlenia',
  `DateEnd` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data okonchania deistvia scheta',
  `DateOplata` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data oplaty',
  `SumOrder` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa scheta',
  `Comment` text COMMENT 'komentarii',
  `EmailTo` varchar(50) DEFAULT NULL COMMENT 'nomer dlia email',
  `EmailSended` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data otpravki email',
  `SmsTo` varchar(50) DEFAULT NULL COMMENT 'nomer dlia sms',
  `SmsSended` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data otpravki sms',
  `StateOrder` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'status - 0 - ojidaet 1 - oplachen 2 - otshibka',
  `IdPaySchet` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id pay_schet',
  `IdDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  PRIMARY KEY (`ID`),
  KEY `IdPartner` (`IdPartner`,`DateAdd`,`StateOrder`,`IdDeleted`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pan_token`
--

DROP TABLE IF EXISTS `pan_token`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pan_token` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `FirstSixDigits` varchar(10) DEFAULT NULL,
  `LastFourDigits` varchar(10) DEFAULT NULL,
  `EncryptedPAN` varchar(250) DEFAULT NULL,
  `ExpDateMonth` varchar(10) DEFAULT NULL,
  `ExpDateYear` varchar(10) DEFAULT NULL,
  `CreatedDate` int(11) NOT NULL,
  `UpdatedDate` int(11) NOT NULL,
  `CryptoKeyId` int(11) NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `idx_CryptoKeyId` (`CryptoKeyId`),
  KEY `findkard_indx` (`FirstSixDigits`,`LastFourDigits`,`ExpDateMonth`,`ExpDateYear`)
) ENGINE=InnoDB AUTO_INCREMENT=251 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `part_user_access`
--

DROP TABLE IF EXISTS `part_user_access`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `part_user_access` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL,
  `IdRazdel` int(10) unsigned NOT NULL,
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`,`IdRazdel`)
) ENGINE=InnoDB AUTO_INCREMENT=90 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner`
--

DROP TABLE IF EXISTS `partner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'partnery',
  `Name` varchar(250) DEFAULT NULL COMMENT 'naimenovanie v sisteme',
  `UrState` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'urid.status - 0 - ul 1 - ip 2 - fl',
  `UrLico` varchar(250) DEFAULT NULL,
  `INN` varchar(20) DEFAULT NULL,
  `KPP` varchar(20) DEFAULT NULL,
  `OGRN` varchar(20) DEFAULT NULL,
  `UrAdres` varchar(1000) DEFAULT NULL COMMENT 'uridicheskii adres - index|oblast|raion|gorod|ylica|dom|ofis',
  `PostAdres` varchar(1000) DEFAULT NULL COMMENT 'pochtovyii adres - index|oblast|raion|gorod|ylica|dom|ofis',
  `DateRegister` int(10) unsigned NOT NULL COMMENT 'data registracii',
  `NumDogovor` varchar(20) DEFAULT NULL,
  `DateDogovor` varchar(20) DEFAULT NULL,
  `PodpisantFull` varchar(100) DEFAULT NULL COMMENT 'podpisant',
  `PodpisantShort` varchar(50) DEFAULT NULL COMMENT '-',
  `PodpDoljpost` varchar(100) DEFAULT NULL COMMENT '-',
  `PodpDoljpostRod` varchar(100) DEFAULT NULL COMMENT '-',
  `PodpOsnovan` varchar(100) DEFAULT NULL COMMENT '-',
  `PodpOsnovanRod` varchar(100) DEFAULT NULL COMMENT '-',
  `URLSite` varchar(200) DEFAULT NULL COMMENT 'sait',
  `Phone` varchar(50) DEFAULT NULL COMMENT 'telefon',
  `Email` varchar(50) DEFAULT NULL COMMENT 'pochta',
  `KontTehFio` varchar(100) DEFAULT NULL COMMENT 'fio po teh voporosam',
  `KontTehEmail` varchar(50) DEFAULT NULL COMMENT 'email po teh voporosam',
  `KontTehPhone` varchar(50) DEFAULT NULL COMMENT 'phone po teh voporosam',
  `KontFinansFio` varchar(100) DEFAULT NULL COMMENT 'fio po finansovym voporosam',
  `KontFinansEmail` varchar(50) DEFAULT NULL COMMENT 'email po finansovym voporosam',
  `KontFinansPhone` varchar(50) DEFAULT NULL COMMENT 'email po finansovym voporosam',
  `RSchet` varchar(50) DEFAULT NULL COMMENT 'raschethyii schet',
  `KSchet` varchar(50) DEFAULT NULL COMMENT 'kor schet',
  `BankName` varchar(100) DEFAULT NULL COMMENT 'bank naimenovanie',
  `BikBank` varchar(20) DEFAULT NULL COMMENT 'bik banka',
  `PaaswordApi` varchar(50) DEFAULT NULL COMMENT 'parol api mfo',
  `IpAccesApi` varchar(300) DEFAULT NULL COMMENT 'ogranichenie po IP adresu',
  `IsMfo` tinyint(1) DEFAULT '0' COMMENT '1 - mfo',
  `SchetTcb` varchar(40) DEFAULT NULL COMMENT 'r.schet tcb',
  `SchetTcbTransit` varchar(40) DEFAULT NULL COMMENT 'transit schet tcb',
  `SchetTcbNominal` varchar(40) DEFAULT NULL,
  `LoginTkbAft` varchar(40) DEFAULT NULL COMMENT 'tck terminal atf oct',
  `KeyTkbAft` varchar(300) DEFAULT NULL,
  `LoginTkbEcom` varchar(40) DEFAULT NULL COMMENT 'tck terminal ecom',
  `KeyTkbEcom` varchar(300) DEFAULT NULL,
  `LoginTkbOct` varchar(40) DEFAULT NULL COMMENT 'oct gate',
  `KeyTkbOct` varchar(300) DEFAULT NULL,
  `LoginTkbVyvod` varchar(40) DEFAULT NULL,
  `KeyTkbVyvod` varchar(300) DEFAULT NULL,
  `LoginTkbJkh` varchar(40) DEFAULT NULL,
  `KeyTkbJkh` varchar(300) DEFAULT NULL,
  `IsBlocked` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - aktiven 1 - zablokirovan',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - rabotaet 1 - udalen',
  `IsAftOnly` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `LoginTkbAuto1` varchar(40) DEFAULT NULL,
  `LoginTkbAuto2` varchar(40) DEFAULT NULL,
  `LoginTkbAuto3` varchar(40) DEFAULT NULL,
  `LoginTkbAuto4` varchar(40) DEFAULT NULL,
  `LoginTkbAuto5` varchar(40) DEFAULT NULL,
  `LoginTkbAuto6` varchar(40) DEFAULT NULL,
  `LoginTkbAuto7` varchar(40) DEFAULT NULL,
  `KeyTkbAuto1` varchar(300) DEFAULT NULL,
  `KeyTkbAuto2` varchar(300) DEFAULT NULL,
  `KeyTkbAuto3` varchar(300) DEFAULT NULL,
  `KeyTkbAuto4` varchar(300) DEFAULT NULL,
  `KeyTkbAuto5` varchar(300) DEFAULT NULL,
  `KeyTkbAuto6` varchar(300) DEFAULT NULL,
  `KeyTkbAuto7` varchar(300) DEFAULT NULL,
  `LoginTkbPerevod` varchar(40) DEFAULT NULL,
  `KeyTkbPerevod` varchar(300) DEFAULT NULL,
  `IsUnreserveComis` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `SchetTCBUnreserve` varchar(40) DEFAULT NULL,
  `BalanceIn` bigint(19) NOT NULL DEFAULT '0' COMMENT 'balans pogashenia v kopeikah',
  `BalanceOut` bigint(19) NOT NULL DEFAULT '0' COMMENT 'balans vydachi v kopeikah',
  `TypeMerchant` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'type merchanta: 0 - merchant 1 - partner',
  `VoznagVyplatDirect` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'voznag po vyplatam 0 - oplata po schety cheta 1 - vyvod so scheta',
  `LoginTkbOctVyvod` varchar(40) DEFAULT NULL,
  `KeyTkbOctVyvod` varchar(300) DEFAULT NULL,
  `LoginTkbOctPerevod` varchar(40) DEFAULT NULL,
  `KeyTkbOctPerevod` varchar(300) DEFAULT NULL,
  `IsAutoPerevodToVydacha` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'avtoperevod na vydachy',
  `IsCommonSchetVydacha` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - odin schet v tcb na vydacy 0 - raznye scheta',
  PRIMARY KEY (`ID`),
  KEY `IsAftOnly_idx` (`IsAftOnly`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner_bank_rekviz`
--

DROP TABLE IF EXISTS `partner_bank_rekviz`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_bank_rekviz` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `NamePoluchat` varchar(200) DEFAULT NULL,
  `INNPolushat` varchar(20) DEFAULT NULL,
  `KPPPoluchat` varchar(20) DEFAULT NULL,
  `KorShetPolushat` varchar(40) DEFAULT NULL,
  `RaschShetPolushat` varchar(40) DEFAULT NULL,
  `NameBankPoluchat` varchar(150) DEFAULT NULL,
  `SityBankPoluchat` varchar(80) DEFAULT NULL,
  `BIKPoluchat` varchar(20) DEFAULT NULL,
  `PokazKBK` varchar(40) DEFAULT NULL,
  `OKATO` varchar(20) DEFAULT NULL,
  `NaznachenPlatez` varchar(300) DEFAULT NULL,
  `SummReestrAutoOplat` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'сумма к перечислению (для предоплаты), 0 - долг оплатить',
  `MinSummReestrToOplat` int(10) NOT NULL DEFAULT '0' COMMENT 'сумма долга после которой оплату производить',
  `MaxIntervalOplat` int(10) NOT NULL DEFAULT '0' COMMENT 'максимальный срок между перечислениями в днях',
  `IsDecVoznagPerecisl` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - удерживать вознаграждение при перечислении',
  `ExportBankType` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'банк для выгрузки',
  `DateLastExport` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'дата последней оплаты',
  `BalanceSumm` int(11) NOT NULL DEFAULT '0' COMMENT 'сумма долга перед партнером',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner_dogovor`
--

DROP TABLE IF EXISTS `partner_dogovor`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_dogovor` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `NameMagazin` varchar(100) DEFAULT NULL COMMENT 'naimenovane magazina',
  `TypeMagazin` int(11) DEFAULT NULL COMMENT 'tip: 0 - inet 1 - torgov 2 - mobile',
  `NumDogovor` varchar(20) DEFAULT NULL COMMENT 'nomer',
  `DateDogovor` varchar(20) DEFAULT NULL COMMENT 'data',
  `PodpisantFull` varchar(100) DEFAULT NULL COMMENT 'podpisant polnostyu',
  `PodpisantShort` varchar(50) DEFAULT NULL COMMENT 'podpisant kratko',
  `PodpDoljpost` varchar(50) DEFAULT NULL COMMENT 'doljnost',
  `PodpOsnovan` varchar(100) DEFAULT NULL COMMENT 'osnodanie',
  `Adres` varchar(1000) DEFAULT NULL COMMENT 'adres ili url',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udalen',
  PRIMARY KEY (`ID`),
  KEY `IdPartner` (`IdPartner`,`IsDeleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner_orderin`
--

DROP TABLE IF EXISTS `partner_orderin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_orderin` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `Comment` varchar(250) DEFAULT NULL COMMENT 'info',
  `Summ` bigint(19) NOT NULL DEFAULT '0' COMMENT 'summa',
  `DateOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data',
  `TypeOrder` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip',
  `SummAfter` bigint(19) NOT NULL DEFAULT '0' COMMENT 'summa balansin posle operacii',
  `IdPay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id pay_schet',
  `IdStatm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id statements_account',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=966 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner_orderout`
--

DROP TABLE IF EXISTS `partner_orderout`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_orderout` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `Comment` varchar(250) DEFAULT NULL COMMENT 'info',
  `Summ` bigint(19) NOT NULL DEFAULT '0' COMMENT 'summa',
  `DateOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data',
  `TypeOrder` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip',
  `SummAfter` bigint(19) NOT NULL DEFAULT '0' COMMENT 'summa balansout posle operacii',
  `IdPay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id pay_schet',
  `IdStatm` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id statements_account',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner_reg`
--

DROP TABLE IF EXISTS `partner_reg`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_reg` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `UrState` tinyint(1) unsigned NOT NULL COMMENT 'urid.status - 0 - ul 1 - ip 2 - fl',
  `Email` varchar(50) DEFAULT NULL COMMENT 'email dlia activacii',
  `EmailCode` varchar(50) DEFAULT NULL COMMENT 'kod dlia activacii email',
  `DateReg` int(10) unsigned NOT NULL COMMENT 'data registracii',
  `State` tinyint(1) unsigned NOT NULL COMMENT 'status - 0 - novyii 1 - zaregistrirovan',
  `IdPay` int(10) unsigned NOT NULL COMMENT 'id pay_schet - proverochnaya registracia karty fl',
  PRIMARY KEY (`ID`),
  KEY `Email_idx` (`Email`,`State`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner_sumorder`
--

DROP TABLE IF EXISTS `partner_sumorder`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_sumorder` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `IdRekviz` int(10) unsigned NOT NULL COMMENT 'id partner_bank_rekviz',
  `Comment` varchar(250) DEFAULT NULL COMMENT 'info',
  `Summ` bigint(19) NOT NULL DEFAULT '0' COMMENT 'summa',
  `DateOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data',
  `TypeOrder` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip 0 - oplata reestr 1 - sozdanie reestra 3 - otmena oplaty',
  `SummAfter` bigint(19) NOT NULL DEFAULT '0' COMMENT 'summa balansa posle operacii',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `partner_users`
--

DROP TABLE IF EXISTS `partner_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `partner_users` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Login` varchar(20) NOT NULL COMMENT 'login',
  `Password` varchar(100) NOT NULL COMMENT 'pw sha2',
  `IsAdmin` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - admin 0 - partner',
  `RoleUser` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - user 1 - partner admin',
  `IdPartner` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partner',
  `FIO` varchar(100) DEFAULT NULL COMMENT 'fio',
  `Email` varchar(50) DEFAULT NULL COMMENT 'email',
  `Doljnost` varchar(100) DEFAULT NULL COMMENT 'doljnost',
  `IsActive` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - off 1 - on',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  `DateLastLogin` int(10) unsigned DEFAULT '0',
  `ErrorLoginCnt` int(10) unsigned DEFAULT '0',
  `DateErrorLogin` int(10) unsigned DEFAULT '0',
  `AutoLockDate` int(10) unsigned DEFAULT '0',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Login` (`Login`),
  KEY `IsAdmin` (`IsAdmin`),
  KEY `IdPartner` (`IdPartner`,`IsDeleted`,`IsActive`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pay_bonus`
--

DROP TABLE IF EXISTS `pay_bonus`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_bonus` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id user',
  `IdPay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id pay_schet',
  `Summ` int(11) NOT NULL DEFAULT '0' COMMENT 'summa',
  `TypeOp` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - popolnen 1 - spisan',
  `DateOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data',
  `Comment` varchar(250) DEFAULT '' COMMENT 'kommentraiii',
  `BonusAfter` int(11) NOT NULL DEFAULT '0' COMMENT 'summa posle operacii',
  PRIMARY KEY (`ID`),
  KEY `IsUser` (`IdUser`,`TypeOp`,`DateOp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pay_schet`
--

DROP TABLE IF EXISTS `pay_schet`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_schet` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id user',
  `IdKard` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id kard, esli privaizanoi kartoi oplata',
  `IdUsluga` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id usluga',
  `IdShablon` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id shablon',
  `IdOrder` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id order_pay',
  `IdOrg` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'custom id partner',
  `Extid` varchar(40) DEFAULT NULL COMMENT 'custom partner vneshnii id',
  `IdGroupOplat` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'gruppovaya oplata po pay_schgroup',
  `Period` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'period reestra',
  `Schetcheks` varchar(100) DEFAULT NULL COMMENT 'pokazania schetchikov. razdelenie |',
  `IdQrProv` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'uslugatovar.ProfitIdProvider, esli bez shablona oplata',
  `QrParams` varchar(500) DEFAULT NULL COMMENT 'rekvizity dlia oplaty',
  `SummPay` bigint(19) unsigned NOT NULL DEFAULT '0' COMMENT 'summa plateja v kopeikah',
  `ComissSumm` bigint(19) unsigned NOT NULL DEFAULT '0' COMMENT 'summa komissii v kopeikah',
  `MerchVozn` bigint(19) NOT NULL DEFAULT '0' COMMENT 'komissia vepay, kop',
  `BankComis` bigint(19) NOT NULL DEFAULT '0' COMMENT 'komissia banka, kop',
  `Status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'status: 0 - sozdan, 1 - oplachen, 2 - oshibka oplaty',
  `ErrorInfo` varchar(250) DEFAULT NULL COMMENT 'soobchenie oshibki',
  `DateCreate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'date create',
  `DateOplat` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'date oplata',
  `DateLastUpdate` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data poslednego obnovlenia zapisi',
  `PayType` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip oplaty: 0 - bankovskaya karta, 1 - qiwi, 2 - mail.ru',
  `TimeElapsed` int(10) unsigned NOT NULL DEFAULT '1800' COMMENT 'srok oplaty v sec',
  `ExtBillNumber` varchar(50) DEFAULT NULL COMMENT 'nomer transakcii uniteller',
  `ExtKeyAcces` bigint(20) unsigned NOT NULL DEFAULT '0' COMMENT 'kod scheta uniteller',
  `ApprovalCode` varchar(20) DEFAULT NULL COMMENT 'kod avtorizacii',
  `RRN` varchar(20) DEFAULT NULL COMMENT 'nomer RRN',
  `CardNum` varchar(30) DEFAULT NULL COMMENT 'nomer karty',
  `CardType` varchar(30) DEFAULT NULL COMMENT 'tip karty',
  `CardHolder` varchar(100) DEFAULT NULL COMMENT 'derjatel karty',
  `CardExp` int(4) unsigned NOT NULL DEFAULT '0' COMMENT 'srok deistvia karty - MMYY',
  `BankName` varchar(100) DEFAULT NULL COMMENT 'bank karty',
  `IPAddressUser` varchar(30) DEFAULT NULL COMMENT 'ip adres platelshika',
  `CountryUser` varchar(100) DEFAULT NULL COMMENT 'strana platelshika',
  `CityUser` varchar(100) DEFAULT NULL COMMENT 'gorod platelshika',
  `UserClickPay` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - ne klikal oplatu 1 - klikal oplatu',
  `CountSendOK` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT 'kollichestvo poslanyh zaprosov v magazin ob uspeshnoi oplate',
  `SendKvitMail` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'otpravit kvitanciuu ob oplate na pochtu',
  `IdAgent` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id agent site',
  `GisjkhGuid` varchar(50) DEFAULT NULL COMMENT 'guid gis jkh',
  `TypeWidget` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - web communal 1 - mobile 2 - shop 3 - qr schet',
  `Bank` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'bank: 0 - rsb 1 - rossia',
  `IsAutoPay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - avtoplatej',
  `AutoPayIdGate` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `UrlFormPay` varchar(2000) DEFAULT NULL COMMENT 'url dlia perehoda k oplate',
  `UserUrlInform` varchar(1000) DEFAULT NULL COMMENT 'url dlia kollbeka pletelshiky',
  `UserKeyInform` varchar(50) DEFAULT NULL COMMENT 'kluch dlia kollbeka pletelshiky',
  `SuccessUrl` varchar(1000) DEFAULT NULL COMMENT 'url dlia vozvrata pri uspehe',
  `FailedUrl` varchar(1000) DEFAULT NULL COMMENT 'url dlia vozvrata pri otkaze',
  `CancelUrl` varchar(1000) DEFAULT NULL,
  `sms_accept` tinyint(1) DEFAULT '0',
  `Dogovor` varchar(255) DEFAULT NULL,
  `FIO` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`ID`),
  KEY `Status` (`Status`,`DateOplat`),
  KEY `ExtBillNumber` (`ExtBillNumber`),
  KEY `sms_accept_idx` (`sms_accept`),
  KEY `IdOrg` (`DateCreate`,`IdOrg`,`Extid`,`IdUsluga`)
) ENGINE=InnoDB AUTO_INCREMENT=12044 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `pay_schgroup`
--

DROP TABLE IF EXISTS `pay_schgroup`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pay_schgroup` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DateAdd` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data cozdania',
  `DateOplat` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data oplaty',
  `SummPays` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa platejeii',
  `ComisPays` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'komissia po platejam',
  `CountPays` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'chislo platejei',
  `Status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - novyii 1 - oplachen 2 - otmenen',
  `IdPayOrig` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB AUTO_INCREMENT=23 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `qr_group`
--

DROP TABLE IF EXISTS `qr_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `qr_group` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `NameGroup` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `queue`
--

DROP TABLE IF EXISTS `queue`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `queue` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `channel` varchar(255) NOT NULL,
  `job` blob NOT NULL,
  `pushed_at` int(11) NOT NULL,
  `ttr` int(11) NOT NULL,
  `delay` int(11) NOT NULL DEFAULT '0',
  `priority` int(11) unsigned NOT NULL DEFAULT '1024',
  `reserved_at` int(11) DEFAULT NULL,
  `attempt` int(11) DEFAULT NULL,
  `done_at` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel` (`channel`),
  KEY `reserved_at` (`reserved_at`),
  KEY `priority` (`priority`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `reestr_bankpl`
--

DROP TABLE IF EXISTS `reestr_bankpl`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `reestr_bankpl` (
  `IdBankpl` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DatePayd` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '0 - ne oplachen / > 0 - data oplaty',
  `DateCreate` int(10) unsigned NOT NULL DEFAULT '0',
  `Summ` int(11) NOT NULL DEFAULT '0',
  `FilePl` varchar(40) NOT NULL DEFAULT '',
  `CountPls` tinyint(4) NOT NULL DEFAULT '0',
  PRIMARY KEY (`IdBankpl`),
  KEY `IdProv` (`DatePayd`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='платежные поручения в банк';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `send_sms`
--

DROP TABLE IF EXISTS `send_sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `send_sms` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DateIn` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data sozdania',
  `DateSend` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data otpravki',
  `NumPhone` varchar(12) NOT NULL DEFAULT '' COMMENT 'nomer bez 8',
  `StateSend` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - v obrabotke 1 - otpravleno 2 - oshibka',
  `MsgText` varchar(600) NOT NULL DEFAULT '' COMMENT 'tekst',
  PRIMARY KEY (`ID`),
  KEY `StateSend` (`StateSend`),
  KEY `DateIn` (`DateIn`,`StateSend`),
  KEY `DateSend` (`DateSend`)
) ENGINE=InnoDB DEFAULT CHARSET=cp1251 COMMENT='Рассылка SMS оповещений жильцам';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `session`
--

DROP TABLE IF EXISTS `session`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session` (
  `id` char(40) NOT NULL,
  `expire` int(11) DEFAULT NULL,
  `data` blob,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms`
--

DROP TABLE IF EXISTS `sms`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `phone` varchar(50) DEFAULT NULL,
  `code` varchar(50) DEFAULT NULL,
  `confirm` tinyint(1) DEFAULT NULL,
  `partner_id` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `code_idx` (`code`,`partner_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `sms_via_orders`
--

DROP TABLE IF EXISTS `sms_via_orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sms_via_orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sms_id` int(11) DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `sms_id_idx` (`sms_id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statements_account`
--

DROP TABLE IF EXISTS `statements_account`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statements_account` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partner',
  `TypeAccount` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip schet partnera - 0 - vydacha 1 - pogashenie 2 - nominalnyii',
  `BnkId` bigint(20) unsigned DEFAULT '0' COMMENT 'id',
  `NumberPP` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'number',
  `DatePP` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data',
  `SummPP` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa',
  `SummComis` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'komissia vepay',
  `Description` varchar(500) DEFAULT NULL COMMENT 'naznachenie',
  `IsCredit` tinyint(3) unsigned NOT NULL DEFAULT '0' COMMENT '0 - spisanie, 1 - popolnenie',
  `Name` varchar(250) DEFAULT NULL COMMENT 'kontragent',
  `Inn` varchar(50) DEFAULT NULL COMMENT 'inn',
  `Account` varchar(50) DEFAULT NULL COMMENT 'rsch.schet',
  `Bic` varchar(50) DEFAULT NULL COMMENT 'bik banka',
  `Bank` varchar(250) DEFAULT NULL COMMENT 'bank',
  `BankAccount` varchar(50) DEFAULT NULL COMMENT 'kor.schet',
  `Kpp` varchar(50) DEFAULT NULL,
  `DateRead` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data poluchenia ot tkb',
  `DateDoc` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `DatePP` (`DatePP`,`IdPartner`),
  KEY `BnkId` (`BnkId`,`IdPartner`,`TypeAccount`),
  KEY `DateRead_idx` (`DateRead`,`IdPartner`,`TypeAccount`),
  KEY `DateDoc_idx` (`DateDoc`,`IdPartner`,`TypeAccount`)
) ENGINE=InnoDB AUTO_INCREMENT=1646 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `statements_planner`
--

DROP TABLE IF EXISTS `statements_planner`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `statements_planner` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL DEFAULT '0',
  `IdTypeAcc` int(10) unsigned NOT NULL DEFAULT '0',
  `DateUpdateFrom` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data nachala vypiski',
  `DateUpdateTo` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data obnovlenia vypiski',
  PRIMARY KEY (`ID`),
  KEY `IdPartner` (`IdPartner`,`IdTypeAcc`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user`
--

DROP TABLE IF EXISTS `user`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `Login` varchar(50) DEFAULT NULL COMMENT 'login',
  `Password` varchar(100) DEFAULT NULL COMMENT 'parol dostypa sha',
  `Pinpay` varchar(100) DEFAULT NULL COMMENT 'pin plateja sha',
  `BonusBalance` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'bonusy',
  `ExtOrg` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'ext partner',
  `ExtCustomerIDP` varchar(128) DEFAULT NULL COMMENT 'vneshniii id klienta 64 simvola',
  `Fam` varchar(100) DEFAULT NULL COMMENT 'familia',
  `Name` varchar(100) DEFAULT NULL COMMENT 'imia',
  `Otch` varchar(100) DEFAULT NULL COMMENT 'otchestvo',
  `Inn` varchar(20) DEFAULT NULL COMMENT 'inn',
  `Snils` varchar(40) DEFAULT NULL COMMENT 'snils',
  `Email` varchar(100) DEFAULT NULL COMMENT 'pochta / mobile identificator',
  `TempEmail` varchar(100) DEFAULT NULL COMMENT 'vremennyii email',
  `VerificCode` varchar(128) DEFAULT NULL COMMENT 'kod podtverjdenia',
  `Phone` varchar(20) DEFAULT NULL COMMENT 'telefon',
  `DateRegister` int(10) unsigned NOT NULL COMMENT 'data registracii',
  `IMEI` varchar(36) NOT NULL DEFAULT '0' COMMENT 'IMEI nomer telefona / UUID',
  `UserDeviceType` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip ustroistva: 0 - n/a 1 - web 2 - android 3 - iphone',
  `SendUvedolmen` tinyint(1) unsigned DEFAULT '1' COMMENT 'Poluchat uvedomleniia',
  `SendPush` tinyint(1) unsigned DEFAULT '1' COMMENT 'Ispolzovat PUSH',
  `SendInSchets` tinyint(1) unsigned DEFAULT '1' COMMENT 'O postupivshikh schetakh',
  `SendInfoOplata` tinyint(1) unsigned DEFAULT '1' COMMENT 'Ob oplate',
  `SendReclPartner` tinyint(1) unsigned DEFAULT '1' COMMENT 'Reclamnye soobshcheniia partnerov',
  `IsBonusCopy` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 - nakaplivat bonusy',
  `IsUsePassw` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - ispolzovat parol dlia vhoda',
  `IsUsePinpay` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - ispolzovat pin dlia oplaty',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - activen 1 - udalen',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `Email` (`Email`,`IMEI`),
  UNIQUE KEY `Login` (`Login`,`ExtOrg`)
) ENGINE=InnoDB AUTO_INCREMENT=4431 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_address`
--

DROP TABLE IF EXISTS `user_address`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_address` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL COMMENT 'id user',
  `Name` varchar(150) DEFAULT NULL COMMENT 'naimenovanie',
  `CoordX` double unsigned NOT NULL DEFAULT '0' COMMENT 'position X',
  `CoordY` double unsigned NOT NULL DEFAULT '0' COMMENT 'position Y',
  `TypeRegion` varchar(30) DEFAULT NULL COMMENT 'adres',
  `Region` varchar(150) DEFAULT NULL,
  `Raion` varchar(150) DEFAULT NULL,
  `TypeCity` varchar(30) DEFAULT NULL,
  `City` varchar(150) DEFAULT NULL,
  `TypeStreet` varchar(30) DEFAULT NULL,
  `Street` varchar(150) DEFAULT NULL,
  `House` varchar(30) DEFAULT NULL,
  `Copr` varchar(30) DEFAULT NULL,
  `Stroen` varchar(30) DEFAULT NULL,
  `Podiezd` varchar(30) DEFAULT NULL,
  `Etaj` varchar(30) DEFAULT NULL,
  `Flat` varchar(30) DEFAULT NULL,
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`,`IsDeleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_car`
--

DROP TABLE IF EXISTS `user_car`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_car` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL COMMENT 'id user',
  `CarName` varchar(100) DEFAULT NULL COMMENT 'carname',
  `Number` varbinary(10) DEFAULT NULL COMMENT 'gos number car',
  `Vud` varchar(10) DEFAULT NULL COMMENT 'vodit udostoverenie',
  `Sts` varchar(10) DEFAULT NULL COMMENT 'cvid o registr auto',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`,`IsDeleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_favor_uslug`
--

DROP TABLE IF EXISTS `user_favor_uslug`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_favor_uslug` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id user',
  `IdUslug` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id uslugatovar',
  `Name` varchar(150) DEFAULT NULL COMMENT 'naimenovanie',
  `Rekviz` varchar(1500) DEFAULT NULL COMMENT 'rekvizity',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`,`IsDeleted`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_identification`
--

DROP TABLE IF EXISTS `user_identification`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_identification` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL,
  `IdOrg` int(10) NOT NULL,
  `TransNum` int(10) NOT NULL DEFAULT '0',
  `DateOp` int(10) NOT NULL DEFAULT '0',
  `Name` varchar(50) DEFAULT NULL,
  `Fam` varchar(50) DEFAULT NULL,
  `Otch` varchar(50) DEFAULT NULL,
  `BirthDay` bigint(20) NOT NULL DEFAULT '0',
  `Inn` varchar(20) DEFAULT NULL,
  `Snils` varchar(50) DEFAULT NULL,
  `PaspSer` varchar(10) DEFAULT NULL,
  `PaspNum` varchar(10) DEFAULT NULL,
  `PaspPodr` varchar(10) DEFAULT NULL,
  `PaspDate` int(11) NOT NULL DEFAULT '0',
  `PaspVidan` varchar(200) DEFAULT NULL,
  `Phone` varchar(20) DEFAULT NULL,
  `PhoneCode` varchar(20) DEFAULT NULL,
  `StateOp` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 - создан 1 - подтвержден 2 - отклонен',
  `ErrorMessage` varchar(250) DEFAULT NULL,
  PRIMARY KEY (`ID`,`IdUser`,`IdOrg`,`TransNum`,`DateOp`,`BirthDay`,`PaspDate`,`StateOp`),
  KEY `IdUser` (`IdUser`,`StateOp`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `user_quest`
--

DROP TABLE IF EXISTS `user_quest`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `user_quest` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdUser` int(10) unsigned NOT NULL COMMENT 'id user',
  `Quest` varchar(200) DEFAULT NULL COMMENT 'tekst voporsa',
  `Answer` varchar(100) DEFAULT NULL COMMENT 'tekst otveta',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - udaleno',
  PRIMARY KEY (`ID`),
  KEY `IdUser` (`IdUser`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usluga_reestr_group`
--

DROP TABLE IF EXISTS `usluga_reestr_group`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usluga_reestr_group` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdMainUsluga` int(10) unsigned NOT NULL COMMENT 'id glavnoi uslugi',
  `IdUsluga` int(10) unsigned NOT NULL COMMENT 'id uslugi',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `IdMainUsluga` (`IdMainUsluga`,`IdUsluga`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uslugatovar`
--

DROP TABLE IF EXISTS `uslugatovar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uslugatovar` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IDPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `IdMagazin` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partner_dogovor',
  `IsCustom` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - obshaia 1 - widget order 2 - merchant 10 - mfo in 11 - mfo out',
  `CustomData` text COMMENT 'danuue customnogo',
  `ExtReestrIDUsluga` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id uslugi v reestrah',
  `NameUsluga` varchar(200) DEFAULT NULL COMMENT 'naimenovanie uslugi',
  `InfoUsluga` varchar(500) DEFAULT NULL COMMENT 'opisanie uslugi',
  `SitePoint` varchar(50) DEFAULT NULL COMMENT 'sait ustanovki',
  `PatternFind` varchar(250) DEFAULT NULL COMMENT 'pattern dlia poiska provaidera po qr-cody',
  `ProfitExportFormat` varchar(250) DEFAULT NULL COMMENT 'format eksporta: LS, PERIOD, FIO, ADDRESS',
  `QrcodeExportFormat` varchar(500) DEFAULT NULL COMMENT 'qr code format eksporta: LS, PERIOD, FIO, ADDRESS',
  `SchetchikFormat` varchar(250) DEFAULT NULL COMMENT 'schetchiki uslugi, razdelenie |, format - regexp',
  `SchetchikNames` varchar(250) DEFAULT NULL COMMENT 'naimenovanie schetchikov uslugi, razdelenie |',
  `SchetchikIzm` varchar(250) DEFAULT NULL COMMENT 'edinicy izmerenia schetchikov uslugi, razdelenie |',
  `PartnerSiteReferer` varchar(250) DEFAULT NULL COMMENT 'referer dlia freima saita partnera po usluge',
  `PcComission` double unsigned DEFAULT '0' COMMENT 'procent komissii',
  `MinsumComiss` double unsigned DEFAULT '0' COMMENT 'minimalnaya komissiia v rub',
  `Information` varchar(500) DEFAULT NULL COMMENT 'informacia po usluge',
  `Group` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id qr_group',
  `Region` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id uslugi_regions',
  `LogoProv` varchar(100) DEFAULT NULL COMMENT 'logotip',
  `MinSumm` int(10) unsigned NOT NULL DEFAULT '100' COMMENT 'minimalnaya symma plateja',
  `MaxSumm` int(10) unsigned NOT NULL DEFAULT '1500000' COMMENT 'maksimalnaya symma plateja',
  `Labels` varchar(500) DEFAULT NULL COMMENT 'podpis vvoda - |',
  `Comments` varchar(500) DEFAULT NULL COMMENT 'kommentarii vvoda - |',
  `Example` varchar(500) DEFAULT NULL COMMENT 'primer vvoda - |',
  `Mask` varchar(500) DEFAULT NULL COMMENT 'maska vvoda - |',
  `Regex` varchar(300) DEFAULT NULL COMMENT 'regularki - |||',
  `LabelsInfo` varchar(300) DEFAULT NULL COMMENT 'podpis info - |',
  `CommentsInfo` varchar(300) DEFAULT NULL COMMENT 'kommentarii info - |',
  `ExampleInfo` varchar(100) DEFAULT NULL COMMENT 'primer info - |',
  `MaskInfo` varchar(100) DEFAULT NULL COMMENT 'maska info - |',
  `RegexInfo` varchar(100) DEFAULT NULL COMMENT 'regularki info - |||',
  `ProvVoznagPC` double unsigned NOT NULL DEFAULT '0' COMMENT 'voznag %',
  `ProvVoznagMin` double unsigned NOT NULL DEFAULT '0',
  `ProvComisPC` double unsigned NOT NULL DEFAULT '0' COMMENT 'prov komis %',
  `ProvComisMin` double unsigned NOT NULL DEFAULT '0',
  `TypeExport` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip eksporta plateja: 0 - v teleport 1 - po banky po reestram 2 - online',
  `ProfitIdProvider` int(10) unsigned DEFAULT NULL COMMENT 'id systemgorod.providers',
  `TypeReestr` int(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip reestra: 0 - teleport 1 - sber full 2 - sber gv 3 - sber hv 4 - kes 5 - ds kirov 6 - fkr43 7 - gaz 8 - sber new',
  `EmailReestr` varchar(100) DEFAULT NULL COMMENT 'email dlia reestra',
  `KodPoluchat` varchar(20) DEFAULT NULL COMMENT 'kod poluchatela v resstre',
  `ReestrNameFormat` varchar(100) DEFAULT NULL COMMENT 'format imeni reestra',
  `GroupReestrMain` tinyint(10) unsigned NOT NULL DEFAULT '0' COMMENT '1 - gruppirovka poiska po reestram',
  `UrlCheckReq` varchar(500) DEFAULT NULL COMMENT 'url dlia proverki vozmojnosti oplaty',
  `UrlInform` varchar(500) DEFAULT NULL COMMENT 'url dlia informacii o plateje',
  `KeyInform` varchar(20) DEFAULT NULL COMMENT 'kod informirovania',
  `UrlReturn` varchar(500) DEFAULT NULL COMMENT 'url dlia vozvrata v magazin',
  `UrlReturnFail` varchar(500) DEFAULT NULL COMMENT 'url dlia vozvrata v magazin pri oshibke',
  `UrlReturnCancel` varchar(1000) DEFAULT NULL,
  `SupportInfo` varchar(100) DEFAULT NULL COMMENT 'email slujby podderjki magazina',
  `IdBankRekviz` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partner_bank_rekviz',
  `SendToGisjkh` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - otpravliat v gis jkh',
  `EnabledStatus` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - novaya 1 - activnaya 2 - zablokirovana',
  `EmailShablon` text COMMENT 'tekst shablona uvedmlenia',
  `ColorWdtMain` varchar(10) DEFAULT NULL COMMENT 'ocnovnoi cvet v vidgete',
  `ColorWdtActive` varchar(10) DEFAULT NULL COMMENT 'cvet vydelenia v vidgete',
  `IsKommunal` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '1 - jkh 0 - ecomm',
  `HideFromList` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '1 - skryt iz spiska',
  `IsDeleted` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '0 - activen 1 - udalen',
  PRIMARY KEY (`ID`),
  UNIQUE KEY `ProfitIdProvider` (`ProfitIdProvider`),
  KEY `IDPartner` (`IDPartner`,`IsCustom`)
) ENGINE=InnoDB AUTO_INCREMENT=250 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `uslugi_regions`
--

DROP TABLE IF EXISTS `uslugi_regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `uslugi_regions` (
  `Id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `NameRegion` varchar(100) NOT NULL COMMENT 'region uslugi',
  PRIMARY KEY (`Id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `usluglinks`
--

DROP TABLE IF EXISTS `usluglinks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usluglinks` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'sviaz uslug',
  `IdUsl1` int(11) DEFAULT NULL COMMENT 'id uslugatovar 1',
  `IdUsl2` int(11) DEFAULT NULL COMMENT 'id uslugatovar 2',
  PRIMARY KEY (`ID`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vozvr_comis`
--

DROP TABLE IF EXISTS `vozvr_comis`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vozvr_comis` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL DEFAULT '0',
  `DateFrom` int(10) unsigned NOT NULL DEFAULT '0',
  `DateTo` int(10) unsigned NOT NULL DEFAULT '0',
  `DateOp` int(10) unsigned NOT NULL DEFAULT '0',
  `SumOp` int(10) unsigned NOT NULL DEFAULT '0',
  `StateOp` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `IdPay` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID`),
  KEY `IdPartner` (`IdPartner`,`DateFrom`,`DateTo`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vyvod_reestr`
--

DROP TABLE IF EXISTS `vyvod_reestr`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vyvod_reestr` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `IdPartner` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id partners',
  `DateFrom` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data c',
  `DateTo` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data po',
  `DateOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'data operacii',
  `SumOp` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'summa',
  `StateOp` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'status - 0 - v obrabotke 1 - ispolnena 2 - otmeneno',
  `IdPay` int(10) unsigned NOT NULL DEFAULT '0' COMMENT 'id pay_schet',
  `TypePerechisl` tinyint(1) unsigned NOT NULL DEFAULT '1' COMMENT '0 - perevod na vydachu 1 - perechislene na schet',
  PRIMARY KEY (`ID`),
  KEY `IdPartner` (`IdPartner`,`DateFrom`,`DateTo`),
  KEY `IdPay` (`IdPay`)
) ENGINE=InnoDB AUTO_INCREMENT=48 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `vyvod_system`
--

DROP TABLE IF EXISTS `vyvod_system`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `vyvod_system` (
  `ID` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `DateOp` int(10) unsigned NOT NULL COMMENT 'data operacii',
  `IdPartner` int(10) unsigned NOT NULL COMMENT 'id partner',
  `DateFrom` int(10) unsigned NOT NULL COMMENT 'data s',
  `DateTo` int(10) unsigned NOT NULL COMMENT 'data po',
  `Summ` int(10) unsigned NOT NULL COMMENT 'summa v kop',
  `SatateOp` tinyint(3) unsigned NOT NULL COMMENT 'status - 1 - ispolneno 0 - v rabote 2 - ne ispolneno',
  `IdPay` int(10) unsigned NOT NULL COMMENT 'id pay_schet',
  `TypeVyvod` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT 'tip - 0 - pogashenie 1 - vyplaty',
  PRIMARY KEY (`ID`),
  KEY `IdPartner` (`IdPartner`,`DateFrom`,`DateTo`,`SatateOp`,`TypeVyvod`)
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2020-04-09 12:51:09

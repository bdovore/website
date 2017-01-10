-- MySQL dump 10.13  Distrib 5.5.53, for debian-linux-gnu (i686)
--
-- Host: localhost    Database: bdovore1prod
-- ------------------------------------------------------
-- Server version	5.5.53-0ubuntu0.14.04.1

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
-- Table structure for table `bd_auteur`
--

DROP TABLE IF EXISTS `bd_auteur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_auteur` (
  `ID_AUTEUR` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `PSEUDO` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `PRENOM` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NOM` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FLG_SCENAR` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FLG_DESSIN` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FLG_COLOR` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `COMMENT` mediumtext COLLATE utf8_unicode_ci,
  `DTE_NAIS` date DEFAULT NULL,
  `DTE_DECES` date DEFAULT NULL,
  `NATIONALITE` varchar(20) COLLATE utf8_unicode_ci DEFAULT NULL,
  `IMG_AUT` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Image de l''auteur',
  `VALIDATOR` mediumint(8) DEFAULT NULL COMMENT 'Utilisateur dernière modification',
  `VALID_DTE` date DEFAULT NULL COMMENT 'Date dernière modification',
  PRIMARY KEY (`ID_AUTEUR`),
  KEY `PSEUDO` (`PSEUDO`),
  KEY `NOM` (`NOM`),
  KEY `PRENOM` (`PRENOM`)
) ENGINE=MyISAM AUTO_INCREMENT=20135 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_collection`
--

DROP TABLE IF EXISTS `bd_collection`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_collection` (
  `ID_COLLECTION` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `NOM` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_EDITEUR` smallint(5) unsigned DEFAULT NULL,
  PRIMARY KEY (`ID_COLLECTION`),
  KEY `ID_EDITEUR` (`ID_EDITEUR`),
  KEY `NOM` (`NOM`)
) ENGINE=MyISAM AUTO_INCREMENT=5126 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_editeur`
--

DROP TABLE IF EXISTS `bd_editeur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_editeur` (
  `ID_EDITEUR` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `NOM` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `URL_SITE` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_EDITEUR`),
  KEY `NOM` (`NOM`)
) ENGINE=MyISAM AUTO_INCREMENT=3232 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='Liste des éditeurs';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_edition`
--

DROP TABLE IF EXISTS `bd_edition`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_edition` (
  `ID_EDITION` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ID_TOME` mediumint(8) unsigned NOT NULL,
  `ID_EDITEUR` smallint(5) unsigned DEFAULT NULL,
  `ID_COLLECTION` smallint(5) unsigned DEFAULT NULL,
  `DTE_PARUTION` date DEFAULT NULL,
  `FLAG_DTE_PARUTION` tinyint(1) unsigned DEFAULT NULL,
  `EAN` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ISBN` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FLG_EO` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `FLG_TT` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `IMG_COUV` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `COMMENT` mediumtext COLLATE utf8_unicode_ci,
  `USER_ID` mediumint(8) unsigned DEFAULT NULL,
  `PROP_DTE` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PROP_STATUS` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `VALIDATOR` mediumint(8) unsigned DEFAULT NULL,
  `VALID_DTE` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`ID_EDITION`),
  KEY `DTE_PARUTION` (`DTE_PARUTION`),
  KEY `ID_COLLECTION` (`ID_COLLECTION`),
  KEY `IMG_COUV` (`IMG_COUV`),
  KEY `ISBN` (`ISBN`),
  KEY `ID_TOME` (`ID_TOME`),
  KEY `EAN` (`EAN`)
) ENGINE=MyISAM AUTO_INCREMENT=229273 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_edition_stat`
--

DROP TABLE IF EXISTS `bd_edition_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_edition_stat` (
  `ID_EDITION` mediumint(8) unsigned NOT NULL,
  `ID_TOME` mediumint(8) unsigned NOT NULL,
  `ID_SERIE` mediumint(8) unsigned NOT NULL,
  `ID_GENRE` tinyint(3) unsigned NOT NULL,
  `ID_EDITEUR` smallint(5) unsigned NOT NULL,
  `ID_COLLECTION` smallint(5) unsigned NOT NULL,
  `NBR_USER_ID_EDITION` int(10) unsigned NOT NULL DEFAULT '0',
  `NBR_USER_ID_TOME` int(10) unsigned NOT NULL DEFAULT '0',
  `NBR_USER_ID_SERIE` int(10) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_EDITION`),
  KEY `ID_TOME` (`ID_TOME`),
  KEY `ID_SERIE` (`ID_SERIE`),
  KEY `ID_GENRE` (`ID_GENRE`),
  KEY `ID_COLLECTION` (`ID_COLLECTION`),
  KEY `ID_EDITEUR` (`ID_EDITEUR`),
  KEY `ID_EDITION` (`ID_EDITION`,`ID_TOME`,`ID_SERIE`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_genre`
--

DROP TABLE IF EXISTS `bd_genre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_genre` (
  `ID_GENRE` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `LIBELLE` varchar(100) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `ORIGINE` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'BD',
  PRIMARY KEY (`ID_GENRE`),
  KEY `LIBELLE` (`LIBELLE`)
) ENGINE=MyISAM AUTO_INCREMENT=107 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_serie`
--

DROP TABLE IF EXISTS `bd_serie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_serie` (
  `ID_SERIE` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `NOM` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_GENRE` tinyint(3) unsigned DEFAULT '0',
  `NOTE` tinyint(3) unsigned DEFAULT '0',
  `FLG_FINI` char(1) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT '0: Finie, 1: En cours, 2: One-shot, 3:Interrompue',
  `NB_TOME` tinyint(3) unsigned DEFAULT NULL,
  `NB_NOTE` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `TRI` char(3) COLLATE utf8_unicode_ci DEFAULT NULL,
  `HISTOIRE` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`ID_SERIE`),
  KEY `NOM` (`NOM`),
  KEY `TRI` (`TRI`),
  KEY `ID_GENRE` (`ID_GENRE`)
) ENGINE=MyISAM AUTO_INCREMENT=32931 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_tome`
--

DROP TABLE IF EXISTS `bd_tome`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_tome` (
  `ID_TOME` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `ID_EDITION` mediumint(8) unsigned DEFAULT NULL COMMENT 'id edition par defaut',
  `TITRE` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NUM_TOME` int(11) DEFAULT NULL,
  `ID_SERIE` mediumint(8) unsigned NOT NULL,
  `ID_GENRE` tinyint(3) unsigned DEFAULT NULL,
  `ID_SCENAR` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_SCENAR_ALT` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_DESSIN` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_DESSIN_ALT` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_COLOR` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_COLOR_ALT` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `FLG_INT` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `FLG_TYPE` tinyint(4) NOT NULL DEFAULT '0',
  `PRIX_BDNET` decimal(7,2) DEFAULT NULL,
  `NBR_USER_ID` int(10) unsigned DEFAULT '0' COMMENT 'nbr de users possedant ce tome',
  `moyenne` decimal(4,2) DEFAULT NULL,
  `nb_vote` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `HISTOIRE` mediumtext COLLATE utf8_unicode_ci,
  PRIMARY KEY (`ID_TOME`),
  KEY `TITRE` (`TITRE`),
  KEY `ALL_FK` (`ID_SERIE`,`ID_SCENAR`,`ID_DESSIN`),
  KEY `ID_SCENAR` (`ID_SCENAR`),
  KEY `ID_DESSIN` (`ID_DESSIN`),
  KEY `ID_SCENAR_ALT` (`ID_SCENAR_ALT`),
  KEY `ID_DESSIN_ALT` (`ID_DESSIN_ALT`),
  KEY `ID_COLOR_ALT` (`ID_COLOR_ALT`),
  KEY `FLG_INT` (`FLG_INT`),
  KEY `FLG_TYPE` (`FLG_TYPE`),
  KEY `ID_COLOR` (`ID_COLOR`),
  KEY `ID_GENRE` (`ID_GENRE`),
  KEY `NUM_TOME` (`NUM_TOME`),
  KEY `ID_SERIE` (`ID_SERIE`),
  KEY `ID_EDITION` (`ID_EDITION`),
  KEY `IDX_TOME_SCENAR` (`ID_SCENAR`,`ID_SCENAR_ALT`),
  KEY `IDX_TOME_DESSIN` (`ID_DESSIN`,`ID_DESSIN_ALT`),
  KEY `IDX_TOME_COLOR` (`ID_COLOR`,`ID_COLOR_ALT`)
) ENGINE=MyISAM AUTO_INCREMENT=217072 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_tome_auteur`
--

DROP TABLE IF EXISTS `bd_tome_auteur`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_tome_auteur` (
  `ID_TOME` mediumint(8) unsigned NOT NULL,
  `ID_AUTEUR` mediumint(8) unsigned NOT NULL,
  `ROLE_TOME_AUTEUR` enum('scenar','dessin','color') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'scenar',
  PRIMARY KEY (`ID_TOME`,`ID_AUTEUR`),
  KEY `ROLE_TOME_AUTEUR` (`ROLE_TOME_AUTEUR`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `bd_tome_simil`
--

DROP TABLE IF EXISTS `bd_tome_simil`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bd_tome_simil` (
  `ID_TOME` mediumint(8) unsigned NOT NULL,
  `ID_TOME_SIMIL` mediumint(8) unsigned NOT NULL,
  `SCORE_TOME_SIMIL` float NOT NULL DEFAULT '0',
  `TSMP_TOME_SIMIL` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`ID_TOME`,`ID_TOME_SIMIL`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_column`
--

DROP TABLE IF EXISTS `db_column`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_column` (
  `TABLE_NAME` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `COLUMN_NAME` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `ORDINAL_POSITION` bigint(21) unsigned NOT NULL DEFAULT '0',
  `COLUMN_DEFAULT` longtext COLLATE utf8_unicode_ci,
  `IS_NULLABLE` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `DATA_TYPE` varchar(64) COLLATE utf8_unicode_ci NOT NULL,
  `CHARACTER_MAXIMUM_LENGTH` bigint(21) unsigned DEFAULT NULL,
  `CHARACTER_OCTET_LENGTH` bigint(21) unsigned DEFAULT NULL,
  `NUMERIC_PRECISION` bigint(21) unsigned DEFAULT NULL,
  `NUMERIC_SCALE` bigint(21) unsigned DEFAULT NULL,
  `CHARACTER_SET_NAME` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `COLLATION_NAME` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
  `COLUMN_TYPE` longtext COLLATE utf8_unicode_ci NOT NULL,
  `COLUMN_KEY` varchar(3) COLLATE utf8_unicode_ci NOT NULL,
  `EXTRA` varchar(27) COLLATE utf8_unicode_ci NOT NULL,
  `COLUMN_COMMENT` varchar(1024) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `db_column_constraint`
--

DROP TABLE IF EXISTS `db_column_constraint`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `db_column_constraint` (
  `TABLE_NAME` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `COLUMN_NAME` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `CONSTRAINT_NAME` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `CONSTRAINT_TYPE` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`TABLE_NAME`,`COLUMN_NAME`,`CONSTRAINT_NAME`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lang`
--

DROP TABLE IF EXISTS `lang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lang` (
  `ID_LANG` char(3) NOT NULL,
  `NOM_LANG_FR` varchar(30) NOT NULL,
  `NOM_LANG_EN` varchar(30) NOT NULL,
  PRIMARY KEY (`ID_LANG`),
  UNIQUE KEY `uk_lang_1` (`NOM_LANG_FR`),
  UNIQUE KEY `uk_lang_2` (`NOM_LANG_EN`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lang_constant`
--

DROP TABLE IF EXISTS `lang_constant`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lang_constant` (
  `NAME_CONSTANT` varchar(200) NOT NULL,
  `ID_LANG` char(3) NOT NULL,
  `VAL_CONSTANT` text NOT NULL,
  PRIMARY KEY (`NAME_CONSTANT`,`ID_LANG`),
  KEY `fk_lang_constant_1` (`ID_LANG`),
  CONSTRAINT `fk_lang_constant_1` FOREIGN KEY (`ID_LANG`) REFERENCES `lang` (`ID_LANG`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `lang_field`
--

DROP TABLE IF EXISTS `lang_field`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `lang_field` (
  `COLUMN_NAME` varchar(40) NOT NULL,
  `ID_LANG` char(3) NOT NULL DEFAULT '_FR',
  `TITRE_CHAMP` varchar(40) NOT NULL,
  `EXTRA_CHAMP` varchar(255) DEFAULT NULL,
  `INFO_CHAMP` text,
  `DESC_CHAMP` text,
  PRIMARY KEY (`COLUMN_NAME`,`ID_LANG`),
  KEY `fk_lang_field_1` (`ID_LANG`),
  CONSTRAINT `fk_lang_field_1` FOREIGN KEY (`ID_LANG`) REFERENCES `lang` (`ID_LANG`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_actualite`
--

DROP TABLE IF EXISTS `media_actualite`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_actualite` (
  `ID_ACTUALITE` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `ID_LANG` char(3) NOT NULL,
  `ID_ACTUALITE_TYPE` tinyint(3) unsigned NOT NULL,
  `MSG_ACTUALITE` text NOT NULL,
  `DEBUT_ACTUALITE` date DEFAULT NULL,
  `FIN_ACTUALITE` date DEFAULT NULL,
  PRIMARY KEY (`ID_ACTUALITE`),
  KEY `fk_media_actualite_1` (`ID_LANG`),
  KEY `fk_media_actualite_2` (`ID_ACTUALITE_TYPE`),
  CONSTRAINT `fk_media_actualite_1` FOREIGN KEY (`ID_LANG`) REFERENCES `lang` (`ID_LANG`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_media_actualite_2` FOREIGN KEY (`ID_ACTUALITE_TYPE`) REFERENCES `media_actualite_type` (`ID_ACTUALITE_TYPE`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_actualite_type`
--

DROP TABLE IF EXISTS `media_actualite_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_actualite_type` (
  `ID_ACTUALITE_TYPE` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `ORDRE_ACTUALITE_TYPE` tinyint(3) unsigned NOT NULL,
  `DISPLAY_ACTUALITE_TYPE` enum('block','none') NOT NULL DEFAULT 'block',
  `POSITION_ACTUALITE_TYPE` enum('gauche','haut','droite','bas') NOT NULL DEFAULT 'gauche',
  `NOM_ACTUALITE_TYPE_FR` varchar(50) NOT NULL,
  `NOM_ACTUALITE_TYPE_EN` varchar(50) NOT NULL,
  PRIMARY KEY (`ID_ACTUALITE_TYPE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_faq`
--

DROP TABLE IF EXISTS `media_faq`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_faq` (
  `ID_FAQ` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ID_LANG` char(3) NOT NULL,
  `ID_FAQ_SECTION` tinyint(3) unsigned NOT NULL,
  `QUES_FAQ` varchar(255) NOT NULL,
  `MSG_FAQ` text NOT NULL,
  PRIMARY KEY (`ID_FAQ`),
  KEY `fk_media_faq_1` (`ID_LANG`),
  KEY `fk_media_faq_2` (`ID_FAQ_SECTION`),
  CONSTRAINT `fk_media_faq_1` FOREIGN KEY (`ID_LANG`) REFERENCES `lang` (`ID_LANG`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_media_faq_2` FOREIGN KEY (`ID_FAQ_SECTION`) REFERENCES `media_faq_section` (`ID_FAQ_SECTION`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_faq_section`
--

DROP TABLE IF EXISTS `media_faq_section`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_faq_section` (
  `ID_FAQ_SECTION` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `NOM_FAQ_SECTION_FR` varchar(150) NOT NULL,
  `NOM_FAQ_SECTION_EN` varchar(150) NOT NULL,
  PRIMARY KEY (`ID_FAQ_SECTION`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_lien`
--

DROP TABLE IF EXISTS `media_lien`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_lien` (
  `ID_LIEN` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `ORDRE_LIEN` smallint(5) unsigned DEFAULT NULL,
  `ID_LIEN_TYPE` tinyint(3) unsigned NOT NULL,
  `NOM_LIEN` varchar(100) NOT NULL,
  `URL_LIEN` varchar(255) NOT NULL,
  `DESC_LIEN_FR` text,
  `DESC_LIEN_EN` text,
  PRIMARY KEY (`ID_LIEN`),
  KEY `fk_media_lien_1` (`ID_LIEN_TYPE`),
  CONSTRAINT `fk_media_lien_1` FOREIGN KEY (`ID_LIEN_TYPE`) REFERENCES `media_lien_type` (`ID_LIEN_TYPE`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `media_lien_type`
--

DROP TABLE IF EXISTS `media_lien_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `media_lien_type` (
  `ID_LIEN_TYPE` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `ORDRE_LIEN_TYPE` smallint(5) unsigned DEFAULT NULL,
  `NOM_LIEN_TYPE_FR` varchar(100) NOT NULL,
  `NOM_LIEN_TYPE_EN` varchar(100) NOT NULL,
  PRIMARY KEY (`ID_LIEN_TYPE`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `menu_admin`
--

DROP TABLE IF EXISTS `menu_admin`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `menu_admin` (
  `ORDRE` tinyint(3) unsigned DEFAULT NULL,
  `DESCR` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `URL` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `RANG_MIN` tinyint(3) unsigned NOT NULL DEFAULT '2',
  KEY `DESCR` (`DESCR`),
  KEY `ORDRE` (`ORDRE`),
  KEY `RANG_MIN` (`RANG_MIN`),
  KEY `URL` (`URL`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news` (
  `news_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `ID_NEWS_TYPE` tinyint(3) unsigned NOT NULL DEFAULT '1',
  `news_level` tinyint(3) unsigned NOT NULL DEFAULT '2',
  `news_posteur` varchar(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `news_date` date NOT NULL DEFAULT '0000-00-00',
  `news_titre` varchar(255) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `news_text` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `news_on_date` date DEFAULT NULL,
  `news_off_date` date DEFAULT NULL,
  `USER_ID` mediumint(8) unsigned NOT NULL DEFAULT '4180',
  PRIMARY KEY (`news_id`),
  KEY `news_date` (`news_date`),
  KEY `news_level` (`news_level`),
  KEY `news_posteur` (`news_posteur`),
  KEY `news_titre` (`news_titre`)
) ENGINE=MyISAM AUTO_INCREMENT=141 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `news_type`
--

DROP TABLE IF EXISTS `news_type`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `news_type` (
  `ID_NEWS_TYPE` tinyint(3) unsigned NOT NULL AUTO_INCREMENT,
  `NAME_NEWS_TYPE` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`ID_NEWS_TYPE`),
  UNIQUE KEY `NAME_NEWS_TYPE` (`NAME_NEWS_TYPE`)
) ENGINE=MyISAM AUTO_INCREMENT=6 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `note_serie`
--

DROP TABLE IF EXISTS `note_serie`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note_serie` (
  `ID_SERIE` mediumint(8) unsigned NOT NULL,
  `MOYENNE_NOTE_SERIE` decimal(4,2) NOT NULL DEFAULT '0.00',
  `NB_NOTE_SERIE` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_SERIE`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `note_tome`
--

DROP TABLE IF EXISTS `note_tome`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `note_tome` (
  `ID_TOME` mediumint(8) unsigned NOT NULL,
  `MOYENNE_NOTE_TOME` decimal(4,2) NOT NULL DEFAULT '0.00',
  `NB_NOTE_TOME` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`ID_TOME`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `serie_comment`
--

DROP TABLE IF EXISTS `serie_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `serie_comment` (
  `id_serie` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `comment` mediumtext COLLATE utf8_unicode_ci,
  `note` tinyint(3) unsigned DEFAULT NULL,
  `dte_post` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  UNIQUE KEY `idx_cmt_serie` (`id_serie`,`user_id`),
  KEY `dte_post` (`dte_post`),
  KEY `note` (`note`),
  KEY `user_id` (`user_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci COMMENT='commentaire sur une série';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `tmp_stat`
--

DROP TABLE IF EXISTS `tmp_stat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `tmp_stat` (
  `ID_EDITION` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_TOME` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_SERIE` mediumint(8) unsigned NOT NULL,
  `nbr` bigint(21) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `last_connect` datetime DEFAULT NULL,
  `nb_connect` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `username` varchar(25) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `password` varchar(60) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `level` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `email` varchar(40) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `birthday` varchar(10) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `location` varchar(20) COLLATE utf8_unicode_ci DEFAULT '',
  `image` varchar(15) COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
  `OPEN_COLLEC` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `MSG_COLLEC` text COLLATE utf8_unicode_ci,
  `CARRE_TYPE` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ROW_DISPLAY` tinyint(3) unsigned NOT NULL DEFAULT '10',
  `ABT_NEWS` enum('0','1') COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `VAL_ALB` double(5,2) DEFAULT '12.00',
  `VAL_INT` double(5,2) DEFAULT '30.00',
  `VAL_COF` double(5,2) DEFAULT '5.00',
  `VAL_COF_TYPE` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT '0',
  `ROWSERIE` tinyint(3) unsigned NOT NULL DEFAULT '5',
  `PREF_EXPORT` varchar(20) COLLATE utf8_unicode_ci DEFAULT '111111111111111111',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `username` (`username`),
  KEY `ABT_NEWS` (`ABT_NEWS`),
  KEY `CARRE_TYPE` (`CARRE_TYPE`),
  KEY `OPEN_COLLEC` (`OPEN_COLLEC`),
  KEY `ROWSERIE` (`ROWSERIE`),
  KEY `ROW_DISPLAY` (`ROW_DISPLAY`),
  KEY `VAL_ALB` (`VAL_ALB`),
  KEY `VAL_COF` (`VAL_COF`),
  KEY `VAL_COF_TYPE` (`VAL_COF_TYPE`),
  KEY `VAL_INT` (`VAL_INT`),
  KEY `birthday` (`birthday`),
  KEY `email` (`email`),
  KEY `image` (`image`),
  KEY `last_connect` (`last_connect`),
  KEY `level` (`level`),
  KEY `location` (`location`),
  KEY `nb_connect` (`nb_connect`),
  KEY `password` (`password`)
) ENGINE=MyISAM AUTO_INCREMENT=16873 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_alb_prop`
--

DROP TABLE IF EXISTS `users_alb_prop`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_alb_prop` (
  `ID_PROPOSAL` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `USER_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `PROP_DTE` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  `PROP_TYPE` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  `ACTION` tinyint(3) unsigned DEFAULT '0',
  `NOTIF_MAIL` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ID_TOME` mediumint(8) unsigned DEFAULT NULL,
  `ID_EDITION` mediumint(8) unsigned DEFAULT NULL,
  `TITRE` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `NUM_TOME` mediumint(8) unsigned DEFAULT NULL,
  `FLG_INT` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `FLG_TYPE` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `ID_SERIE` mediumint(8) unsigned DEFAULT NULL,
  `SERIE` varchar(150) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FLG_FINI` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DTE_PARUTION` date DEFAULT NULL,
  `ID_GENRE` tinyint(3) unsigned DEFAULT NULL,
  `GENRE` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_EDITEUR` smallint(5) unsigned DEFAULT NULL,
  `EDITEUR` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_SCENAR` mediumint(8) unsigned DEFAULT NULL,
  `SCENAR` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_SCENAR_ALT` mediumint(8) unsigned DEFAULT NULL,
  `SCENAR_ALT` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_DESSIN` mediumint(8) unsigned DEFAULT NULL,
  `DESSIN` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_DESSIN_ALT` mediumint(8) unsigned DEFAULT NULL,
  `DESSIN_ALT` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_COLOR` mediumint(8) unsigned DEFAULT NULL,
  `COLOR` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ID_COLOR_ALT` mediumint(8) unsigned DEFAULT NULL,
  `COLOR_ALT` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `DESCRIB_EDITION` mediumtext COLLATE utf8_unicode_ci,
  `ID_COLLECTION` smallint(5) unsigned DEFAULT NULL,
  `COLLECTION` varchar(50) COLLATE utf8_unicode_ci DEFAULT NULL,
  `FLG_EO` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `FLG_TT` char(1) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'N',
  `HISTOIRE` mediumtext COLLATE utf8_unicode_ci,
  `IMG_COUV` varchar(100) COLLATE utf8_unicode_ci DEFAULT NULL,
  `COMMENTAIRE` mediumtext COLLATE utf8_unicode_ci,
  `PRIX` double DEFAULT NULL,
  `EAN` varchar(13) COLLATE utf8_unicode_ci DEFAULT NULL,
  `ISBN` varchar(10) COLLATE utf8_unicode_ci DEFAULT NULL,
  `URL_BDNET` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `URL_AMAZON` varchar(250) COLLATE utf8_unicode_ci DEFAULT NULL,
  `STATUS` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `VALIDATOR` mediumint(8) unsigned DEFAULT NULL,
  `VALID_DTE` datetime DEFAULT NULL,
  `CORR_COMMENT` mediumtext COLLATE utf8_unicode_ci,
  `CORR_STATUT` char(1) COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_PROPOSAL`),
  KEY `ACTION` (`ACTION`),
  KEY `COLOR` (`COLOR`),
  KEY `DESSIN` (`DESSIN`),
  KEY `DESSIN_ALT` (`DESSIN_ALT`),
  KEY `DTE_PARUTION` (`DTE_PARUTION`),
  KEY `EAN` (`EAN`),
  KEY `EDITEUR` (`EDITEUR`),
  KEY `FLG_EO` (`FLG_EO`),
  KEY `FLG_FINI` (`FLG_FINI`),
  KEY `FLG_INT` (`FLG_INT`),
  KEY `FLG_TT` (`FLG_TT`),
  KEY `FLG_TYPE` (`FLG_TYPE`),
  KEY `GENRE` (`GENRE`),
  KEY `ID_COLLECTION` (`ID_COLLECTION`),
  KEY `ID_COLOR` (`ID_COLOR`),
  KEY `ID_DESSIN` (`ID_DESSIN`),
  KEY `ID_DESSIN_ALT` (`ID_DESSIN_ALT`),
  KEY `ID_EDITEUR` (`ID_EDITEUR`),
  KEY `ID_EDITION` (`ID_EDITION`),
  KEY `ID_GENRE` (`ID_GENRE`),
  KEY `ID_SCENAR` (`ID_SCENAR`),
  KEY `ID_SCENAR_ALT` (`ID_SCENAR_ALT`),
  KEY `ID_SERIE` (`ID_SERIE`),
  KEY `ID_TOME` (`ID_TOME`),
  KEY `IMG_COUV` (`IMG_COUV`),
  KEY `ISBN` (`ISBN`),
  KEY `NOTIF_MAIL` (`NOTIF_MAIL`),
  KEY `NUM_TOME` (`NUM_TOME`),
  KEY `PRIX` (`PRIX`),
  FULLTEXT KEY `COLLECTION` (`COLLECTION`)
) ENGINE=MyISAM AUTO_INCREMENT=75347 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_album`
--

DROP TABLE IF EXISTS `users_album`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_album` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `id_edition` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `flg_pret` char(1) DEFAULT NULL,
  `nom_pret` varchar(100) DEFAULT NULL,
  `email_pret` varchar(100) DEFAULT NULL,
  `flg_dedicace` char(1) DEFAULT NULL,
  `flg_tete` char(1) NOT NULL DEFAULT 'N' COMMENT 'Edition originale',
  `comment` text,
  `date_ajout` datetime DEFAULT NULL,
  `flg_achat` char(1) DEFAULT 'N',
  `date_achat` date DEFAULT NULL,
  `cote` float DEFAULT NULL,
  `flg_cadeau` char(1) NOT NULL DEFAULT 'N',
  `FLG_LU` char(1) NOT NULL DEFAULT 'N',
  PRIMARY KEY (`user_id`,`id_edition`),
  KEY `id_edition` (`id_edition`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Liste des albums par utilisateur';
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_comment`
--

DROP TABLE IF EXISTS `users_comment`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_comment` (
  `USER_ID` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `ID_TOME` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `NOTE` tinyint(3) unsigned DEFAULT NULL,
  `COMMENT` mediumtext COLLATE utf8_unicode_ci,
  `DTE_POST` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
  PRIMARY KEY (`USER_ID`,`ID_TOME`),
  KEY `DTE_POST` (`DTE_POST`),
  KEY `ID_TOME` (`ID_TOME`),
  KEY `NOTE` (`NOTE`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_exclusions`
--

DROP TABLE IF EXISTS `users_exclusions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_exclusions` (
  `user_id` mediumint(8) unsigned NOT NULL,
  `id_tome` mediumint(8) unsigned NOT NULL,
  `id_serie` mediumint(8) unsigned NOT NULL,
  PRIMARY KEY (`user_id`,`id_tome`,`id_serie`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_list_aut`
--

DROP TABLE IF EXISTS `users_list_aut`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_list_aut` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `id_auteur` mediumint(8) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`,`id_auteur`),
  KEY `id_auteur` (`id_auteur`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Table structure for table `users_list_carre`
--

DROP TABLE IF EXISTS `users_list_carre`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users_list_carre` (
  `user_id` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `id_tome` mediumint(8) unsigned NOT NULL DEFAULT '0',
  `rang` tinyint(3) unsigned DEFAULT NULL,
  PRIMARY KEY (`user_id`,`id_tome`),
  KEY `id_tome` (`id_tome`),
  KEY `rang` (`rang`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2017-01-10 20:35:16
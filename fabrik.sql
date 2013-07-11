-- phpMyAdmin SQL Dump
-- version 3.5.7
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 11. Jul 2013 um 10:36
-- Server Version: 5.5.27
-- PHP-Version: 5.3.15

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `fabrik`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `anmeldung_postdoktoranden`
--

CREATE TABLE IF NOT EXISTS `anmeldung_postdoktoranden` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime DEFAULT NULL,
  `benutzer_name` varchar(255) DEFAULT NULL,
  `vollstaendiger_name` varchar(255) DEFAULT NULL,
  `passwort` varchar(255) DEFAULT NULL,
  `email_adresse` varchar(255) DEFAULT NULL,
  `geburtstag` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=2 ;

--
-- Daten für Tabelle `anmeldung_postdoktoranden`
--

INSERT INTO `anmeldung_postdoktoranden` (`id`, `date_time`, `benutzer_name`, `vollstaendiger_name`, `passwort`, `email_adresse`, `geburtstag`) VALUES
(1, '2013-07-10 14:02:13', 'olafzieger72', 'Olaf Zieger', 'ef5d2f5db96ec2dfb5d8e6fb99044b2e:4ANIGquf6U2ghFnBxlbIKLLDVrUv61ap', 'ziegerolaf@googlemail.com', NULL);

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `test`
--

CREATE TABLE IF NOT EXISTS `test` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `date_time` datetime DEFAULT NULL,
  `testeintrag_1` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `test_fabrik_notification`
--

CREATE TABLE IF NOT EXISTS `test_fabrik_notification` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL COMMENT 'tableid.formid.rowid reference',
  `user_id` int(6) NOT NULL,
  `reason` varchar(40) NOT NULL,
  `message` text NOT NULL,
  `label` varchar(200) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniquereason` (`user_id`,`reason`(20),`reference`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `test_fabrik_notification_event`
--

CREATE TABLE IF NOT EXISTS `test_fabrik_notification_event` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reference` varchar(50) NOT NULL COMMENT 'tableid.formid.rowid reference',
  `event` varchar(255) NOT NULL,
  `user_id` int(6) NOT NULL,
  `date_time` datetime NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur für Tabelle `test_fabrik_notification_event_sent`
--

CREATE TABLE IF NOT EXISTS `test_fabrik_notification_event_sent` (
  `id` int(6) NOT NULL AUTO_INCREMENT,
  `notification_event_id` int(6) NOT NULL,
  `user_id` int(6) NOT NULL,
  `date_sent` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `sent` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_notified` (`notification_event_id`,`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 3.3.10deb1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Erstellungszeit: 04. Dezember 2012 um 13:39
-- Server Version: 5.1.54
-- PHP-Version: 5.3.5-1ubuntu7.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Datenbank: `cloudhost`
--

-- --------------------------------------------------------

--
-- Tabellenstruktur f√ºr Tabelle `files`
--

CREATE TABLE IF NOT EXISTS `files` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `filename` varchar(244) NOT NULL,
  `extension` varchar(15) NOT NULL,
  `hashname` varchar(255) NOT NULL,
  `channel_name` varchar(255) NOT NULL,
  `data_storage` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `acl` varchar(15) NOT NULL,
  `filesize` int(255) NOT NULL,
  `shorturl` varchar(255) NOT NULL,
  `direct_shorturl` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `hits` int(255) NOT NULL DEFAULT '0',
  `downloads` int(255) NOT NULL DEFAULT '0',
  `enabled` int(1) NOT NULL DEFAULT '1',
  `unique_hash` varchar(32) NOT NULL,
  `deleted_at` int(35) NOT NULL DEFAULT '0',
  `item_type` varchar(255) NOT NULL DEFAULT 'unknown',
  `download_shorturl` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f√ºr Tabelle `keys`
--

CREATE TABLE IF NOT EXISTS `keys` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `AWSAccessKeyId` varchar(255) NOT NULL,
  `uploads_remaining` int(255) NOT NULL,
  `key` varchar(255) NOT NULL,
  `policy` text NOT NULL,
  `signature` varchar(255) NOT NULL,
  `acl` varchar(15) NOT NULL,
  `max_upload_size` int(255) NOT NULL,
  `success_action_redirect` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f√ºr Tabelle `user`
--

CREATE TABLE IF NOT EXISTS `user` (
  `id` int(255) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `enabled` int(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=6 ;

-- --------------------------------------------------------

--
-- Tabellenstruktur f√ºr Tabelle `user_session`
--

CREATE TABLE IF NOT EXISTS `user_session` (
  `email` varchar(255) NOT NULL,
  `cookie` varchar(500) NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`cookie`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;


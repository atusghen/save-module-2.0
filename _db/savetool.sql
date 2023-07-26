-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Creato il: Lug 26, 2023 alle 10:27
-- Versione del server: 10.4.10-MariaDB
-- Versione PHP: 8.0.23

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pell_ip_v2`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `save_analysis`
--

DROP TABLE IF EXISTS `save_analysis`;
CREATE TABLE IF NOT EXISTS `save_analysis` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `investment_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `save_clusters`
--

DROP TABLE IF EXISTS `save_clusters`;
CREATE TABLE IF NOT EXISTS `save_clusters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ha_id` int(11) DEFAULT NULL,
  `label` varchar(256) DEFAULT NULL,
  `technology` varchar(256) DEFAULT NULL,
  `lamp_num` int(11) DEFAULT NULL,
  `device_num` int(11) DEFAULT NULL,
  `average_device_power` decimal(10,2) DEFAULT NULL,
  `dimmering` int(11) DEFAULT NULL,
  `hours_full_lighting` int(11) DEFAULT NULL,
  `hours_dimmer_lighting` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `save_has`
--

DROP TABLE IF EXISTS `save_has`;
CREATE TABLE IF NOT EXISTS `save_has` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plant_id` int(11) DEFAULT NULL,
  `label` varchar(256) DEFAULT NULL,
  `type` varchar(4) DEFAULT NULL,
  `lamp_cost` decimal(10,2) DEFAULT NULL,
  `lamp_disposal` decimal(10,2) DEFAULT NULL,
  `maintenance_interval` int(11) DEFAULT NULL,
  `panel_cost` decimal(10,2) DEFAULT NULL,
  `create_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `save_investments`
--

DROP TABLE IF EXISTS `save_investments`;
CREATE TABLE IF NOT EXISTS `save_investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `label` varchar(256) DEFAULT NULL,
  `wacc` decimal(10,2) DEFAULT NULL,
  `share_municipality` decimal(10,2) DEFAULT NULL,
  `share_bank` decimal(10,2) DEFAULT NULL,
  `mortgage_installment` decimal(10,2) DEFAULT NULL,
  `fee_esco` decimal(10,2) DEFAULT NULL,
  `share_esco` decimal(10,2) DEFAULT NULL,
  `energy_unit_cost` decimal(10,2) DEFAULT NULL,
  `incentives_duration` decimal(10,2) DEFAULT NULL,
  `tep_kwh` decimal(10,2) DEFAULT NULL,
  `tep_value` decimal(10,2) DEFAULT NULL,
  `management_cost` decimal(10,2) DEFAULT NULL,
  `share_funded` decimal(10,2) DEFAULT NULL,
  `cost_funded` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `save_plants`
--

DROP TABLE IF EXISTS `save_plants`;
CREATE TABLE IF NOT EXISTS `save_plants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(256) DEFAULT NULL,
  `municipality_code` varchar(256) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_plants`
--

INSERT INTO `save_plants` (`id`, `label`, `municipality_code`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Prova', '19087004', 3, '2023-07-25 16:57:27', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

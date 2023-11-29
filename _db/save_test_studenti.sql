-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Creato il: Ott 25, 2023 alle 06:48
-- Versione del server: 8.0.31
-- Versione PHP: 8.0.26

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `save`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `save_analysis`
--

DROP TABLE IF EXISTS `save_analysis`;
CREATE TABLE IF NOT EXISTS `save_analysis` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label_analysis` varchar(256) DEFAULT NULL,
  `user_id` int NOT NULL,
  `plant_id` int NOT NULL,
  `investment_id` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Struttura della tabella `save_clusters`
--

DROP TABLE IF EXISTS `save_clusters`;
CREATE TABLE IF NOT EXISTS `save_clusters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `ha_id` int DEFAULT NULL,
  `label_cluster` varchar(256) DEFAULT NULL,
  `type_cluster` varchar(5) DEFAULT NULL,
  `ref_as_is_id_cluster` int DEFAULT NULL,
  `is_to_be_featured` int DEFAULT '0',
  `lamp_technology` varchar(256) DEFAULT NULL,
  `lamp_num` int DEFAULT NULL,
  `device_num` int DEFAULT NULL,
  `average_device_power` decimal(10,2) DEFAULT NULL,
  `dimmering` int DEFAULT NULL,
  `hours_full_light` int DEFAULT NULL,
  `hours_dimmer_light` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_clusters`
--

INSERT INTO `save_clusters` (`id`, `ha_id`, `label_cluster`, `type_cluster`, `ref_as_is_id_cluster`, `is_to_be_featured`, `lamp_technology`, `lamp_num`, `device_num`, `average_device_power`, `dimmering`, `hours_full_light`, `hours_dimmer_light`, `created_at`, `updated_at`) VALUES
(1, 1, 'CLUSTER ASIS 1 verso HA ASIS 1', 'ASIS', NULL, 0, NULL, 8, 2, '2.00', 0, 5000, 0, '2023-10-10 21:06:22', NULL),
(2, 2, 'CLUSTER ASIS 2 verso HA ASIS 2', 'ASIS', NULL, 0, NULL, 6, 2, '1.00', 0, 5000, 0, '2023-10-10 21:06:22', NULL),
(3, 3, 'CLUSTER TOBE 3 verso HA ASIS 1', 'TOBE', 1, 1, NULL, 8, 2, '0.10', 50, 2500, 2500, '2023-10-10 21:06:22', NULL),
(4, 4, 'CLUSTER TOBE 4 verso HA TOBE 2', 'TOBE', 2, 1, NULL, 2, 4, '0.20', 20, 4000, 1000, '2023-10-10 21:06:22', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `save_has`
--

DROP TABLE IF EXISTS `save_has`;
CREATE TABLE IF NOT EXISTS `save_has` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plant_id` int DEFAULT NULL,
  `label_ha` varchar(256) DEFAULT NULL,
  `type_ha` varchar(5) DEFAULT NULL,
  `ref_as_is_id_ha` int DEFAULT NULL,
  `is_ready` int DEFAULT '0',
  `lamp_cost` decimal(10,2) DEFAULT NULL,
  `lamp_disposal` decimal(10,2) DEFAULT NULL,
  `lamp_maintenance_interval` int DEFAULT NULL,
  `panel_cost` decimal(10,2) DEFAULT NULL,
  `panel_num` int DEFAULT NULL,
  `prodromal_activities_cost` decimal(10,2) DEFAULT NULL,
  `system_renovation_cost` decimal(10,2) DEFAULT NULL,
  `infrastructure_maintenance_cost` decimal(10,2) DEFAULT NULL,
  `infrastructure_maintenance_interval` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_has`
--

INSERT INTO `save_has` (`id`, `plant_id`, `label_ha`, `type_ha`, `ref_as_is_id_ha`, `is_ready`, `lamp_cost`, `lamp_disposal`, `lamp_maintenance_interval`, `panel_cost`, `panel_num`, `prodromal_activities_cost`, `system_renovation_cost`, `infrastructure_maintenance_cost`, `infrastructure_maintenance_interval`, `created_at`, `updated_at`) VALUES
(1, 1, 'HAS ASIS 1', 'ASIS', NULL, 0, '250.00', '30.00', 3, '200.00', 1, '50.00', '400.00', '300.00', 4, '2023-10-10 20:55:53', NULL),
(2, 1, 'HAS ASIS 2', 'ASIS', NULL, 0, '100.00', '15.00', 4, '150.00', 2, '50.00', '400.00', '300.00', 3, '2023-10-10 20:55:53', NULL),
(3, 1, 'HAS TOBE 3', 'TOBE', 1, 0, '90.00', '10.00', 7, '150.00', 1, '40.00', '300.00', '200.00', 5, '2023-10-10 20:55:53', NULL),
(4, 1, 'HAS TOBE 4', 'TOBE', 2, 0, '50.00', '3.00', 12, '200.00', 1, '50.00', '300.00', '150.00', 6, '2023-10-10 20:55:53', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `save_investments`
--

DROP TABLE IF EXISTS `save_investments`;
CREATE TABLE IF NOT EXISTS `save_investments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `label_investment` varchar(256) DEFAULT NULL,
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
  `duration_amortization` decimal(10,2) DEFAULT NULL,
  `project_duration` int DEFAULT NULL,
  `taxes` decimal(10,2) DEFAULT NULL,
  `share_funded` decimal(10,2) DEFAULT NULL,
  `cost_funded` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_investments`
--

INSERT INTO `save_investments` (`id`, `user_id`, `label_investment`, `wacc`, `share_municipality`, `share_bank`, `mortgage_installment`, `fee_esco`, `share_esco`, `energy_unit_cost`, `incentives_duration`, `tep_kwh`, `tep_value`, `management_cost`, `duration_amortization`, `project_duration`, `taxes`, `share_funded`, `cost_funded`, `created_at`, `updated_at`) VALUES
(1, 1, 'Investment 1', '3.00', '100.00', '0.00', '0.00', '0.00', '0.00', '0.19', '5.00', '5347.49', '100.00', '200.00', '30.00', 30, '35.00', '0.00', '0.00', '2023-10-10 20:53:24', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `save_plants`
--

DROP TABLE IF EXISTS `save_plants`;
CREATE TABLE IF NOT EXISTS `save_plants` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label_plant` varchar(256) DEFAULT NULL,
  `municipality_code` varchar(256) DEFAULT NULL,
  `user_id` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_plants`
--

INSERT INTO `save_plants` (`id`, `label_plant`, `municipality_code`, `user_id`, `created_at`, `updated_at`) VALUES
(1, 'Plant 1', '24023', 1, '2023-10-10 20:49:51', NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

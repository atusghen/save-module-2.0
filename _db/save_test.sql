-- phpMyAdmin SQL Dump
-- version 4.9.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Creato il: Nov 07, 2023 alle 14:44
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
  `label_analysis` varchar(256) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `plant_id` int(11) NOT NULL,
  `investment_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_analysis`
--

INSERT INTO `save_analysis` (`id`, `label_analysis`, `user_id`, `plant_id`, `investment_id`, `created_at`, `updated_at`) VALUES
(1, 'Test', 3, 5, 1, NULL, '2023-08-07 20:58:55'),
(2, 'Prova 2', 3, 9, 2, NULL, '2023-10-30 12:30:45');

-- --------------------------------------------------------

--
-- Struttura della tabella `save_clusters`
--

DROP TABLE IF EXISTS `save_clusters`;
CREATE TABLE IF NOT EXISTS `save_clusters` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `ha_id` int(11) DEFAULT NULL,
  `label_cluster` varchar(256) DEFAULT NULL,
  `type_cluster` varchar(5) DEFAULT NULL,
  `ref_as_is_id_cluster` int(11) DEFAULT NULL,
  `is_to_be_featured` int(1) DEFAULT 0,
  `lamp_technology` varchar(256) DEFAULT NULL,
  `lamp_num` int(11) DEFAULT NULL,
  `device_num` int(11) DEFAULT NULL,
  `average_device_power` decimal(10,2) DEFAULT NULL,
  `dimmering` int(11) DEFAULT NULL,
  `hours_full_light` int(11) DEFAULT NULL,
  `hours_dimmering_light` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_clusters`
--

INSERT INTO `save_clusters` (`id`, `ha_id`, `label_cluster`, `type_cluster`, `ref_as_is_id_cluster`, `is_to_be_featured`, `lamp_technology`, `lamp_num`, `device_num`, `average_device_power`, `dimmering`, `hours_full_light`, `hours_dimmering_light`, `created_at`, `updated_at`) VALUES
(1, 1, 'ZO0001_CU_001', 'as_is', NULL, 0, 'option_lamp_06', 100, 30, '10.00', 10, 3000, 234, '2023-08-07 10:46:38', NULL),
(3, 1, 'ZO0001_CU_001', 'to_be', 1, 0, 'option_lamp_05', 100, 30, '10.00', 10, 3000, 234, '2023-08-07 17:59:50', '2023-08-07 19:29:26'),
(4, 1, 'ZO0001_CU_001_TEST2', 'to_be', 1, 1, 'option_lamp_01', 100, 30, '10.00', 10, 3000, 234, '2023-08-07 18:11:10', '2023-08-07 19:29:37'),
(5, 5, 'CLUSTER ASIS 5 verso HA ASIS 5', 'as_is', NULL, 0, 'option_lamp_01', 8, 2, '2.00', 0, 5000, 0, '2023-10-10 19:06:22', NULL),
(6, 6, 'CLUSTER ASIS 6 verso HA ASIS 6', 'as_is', NULL, 0, 'option_lamp_01', 6, 2, '1.00', 0, 5000, 0, '2023-10-10 19:06:22', NULL),
(7, 5, 'CLUSTER TOBE 7 verso HA ASIS 5', 'to_be', 5, 1, 'option_lamp_01', 8, 2, '0.10', 50, 2500, 2500, '2023-10-10 19:06:22', NULL),
(8, 6, 'CLUSTER TOBE 8 verso HA TOBE 6', 'to_be', 6, 1, 'option_lamp_01', 2, 4, '0.20', 20, 4000, 1000, '2023-10-10 19:06:22', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `save_has`
--

DROP TABLE IF EXISTS `save_has`;
CREATE TABLE IF NOT EXISTS `save_has` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plant_id` int(11) DEFAULT NULL,
  `label_ha` varchar(256) DEFAULT NULL,
  `type_ha` varchar(5) DEFAULT NULL,
  `ref_as_is_id_ha` int(11) DEFAULT NULL,
  `is_ready` int(1) DEFAULT 0,
  `lamp_cost` decimal(10,2) DEFAULT NULL,
  `lamp_disposal` decimal(10,2) DEFAULT NULL,
  `lamp_maintenance_interval` int(11) DEFAULT NULL,
  `panel_cost` decimal(10,2) DEFAULT NULL,
  `panel_num` int(11) DEFAULT NULL,
  `prodromal_activities_cost` decimal(10,2) DEFAULT NULL,
  `system_renovation_cost` decimal(10,2) DEFAULT NULL,
  `infrastructure_maintenance_cost` decimal(10,2) DEFAULT NULL,
  `infrastructure_maintenance_interval` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_has`
--

INSERT INTO `save_has` (`id`, `plant_id`, `label_ha`, `type_ha`, `ref_as_is_id_ha`, `is_ready`, `lamp_cost`, `lamp_disposal`, `lamp_maintenance_interval`, `panel_cost`, `panel_num`, `prodromal_activities_cost`, `system_renovation_cost`, `infrastructure_maintenance_cost`, `infrastructure_maintenance_interval`, `created_at`, `updated_at`) VALUES
(1, 8, 'ASCOLI_ZO.86', 'as_is', NULL, 1, '1.00', '1.00', 0, '0.00', 1, NULL, NULL, NULL, NULL, '2023-08-04 16:22:37', NULL),
(2, 8, 'Prova TOBE', 'to_be', 1, 0, '1.00', '1.00', 0, '0.00', 1, '5.00', '5.00', '5.00', 5, '2023-08-05 13:43:52', '2023-08-10 09:51:52'),
(4, 8, 'TEST2', 'as_is', NULL, 0, '1.00', '1.00', 0, '0.00', 1, NULL, NULL, NULL, NULL, '2023-08-06 12:50:19', NULL),
(5, 9, 'HAS ASIS 5', 'as_is', NULL, 0, '250.00', '30.00', 3, '200.00', 1, '50.00', '400.00', '300.00', 4, '2023-10-10 18:55:53', NULL),
(6, 9, 'HAS ASIS 6', 'as_is', NULL, 0, '100.00', '15.00', 4, '150.00', 2, '50.00', '400.00', '300.00', 3, '2023-10-10 18:55:53', NULL),
(7, 9, 'HAS TOBE 7', 'to_be', 5, 0, '90.00', '10.00', 7, '150.00', 1, '40.00', '300.00', '200.00', 5, '2023-10-10 18:55:53', NULL),
(8, 9, 'HAS TOBE 8', 'to_be', 6, 0, '50.00', '3.00', 12, '200.00', 1, '50.00', '300.00', '150.00', 6, '2023-10-10 18:55:53', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `save_investments`
--

DROP TABLE IF EXISTS `save_investments`;
CREATE TABLE IF NOT EXISTS `save_investments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
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
  `project_duration` int(11) DEFAULT NULL,
  `taxes` decimal(10,2) DEFAULT NULL,
  `share_funded` decimal(10,2) DEFAULT NULL,
  `cost_funded` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_investments`
--

INSERT INTO `save_investments` (`id`, `user_id`, `label_investment`, `wacc`, `share_municipality`, `share_bank`, `mortgage_installment`, `fee_esco`, `share_esco`, `energy_unit_cost`, `incentives_duration`, `tep_kwh`, `tep_value`, `management_cost`, `duration_amortization`, `project_duration`, `taxes`, `share_funded`, `cost_funded`, `created_at`, `updated_at`) VALUES
(1, 3, 'Prova', '3.00', '100.00', '0.00', '0.00', '0.00', '0.00', '0.19', '5.00', '5347.49', '100.00', '0.00', '30.00', 30, '35.00', '0.00', '0.00', '2023-08-07 20:45:13', NULL),
(2, 3, 'Investment 1', '3.00', '100.00', '0.00', '0.00', '0.00', '0.00', '0.19', '5.00', '5347.49', '100.00', '200.00', '30.00', 30, '35.00', '0.00', '0.00', '2023-10-10 18:53:24', NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `save_plants`
--

DROP TABLE IF EXISTS `save_plants`;
CREATE TABLE IF NOT EXISTS `save_plants` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `label_plant` varchar(256) DEFAULT NULL,
  `municipality_code` varchar(256) DEFAULT NULL,
  `user_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=latin1;

--
-- Dump dei dati per la tabella `save_plants`
--

INSERT INTO `save_plants` (`id`, `label_plant`, `municipality_code`, `user_id`, `created_at`, `updated_at`) VALUES
(9, 'Plant 9', '11044015', 3, '2023-10-10 18:49:51', NULL),
(5, 'Roma 1', '12058091', 3, '2023-08-02 15:49:35', '2023-08-03 10:43:11'),
(8, 'Comunanza 1', '11044015', 3, '2023-08-03 10:43:25', '2023-08-07 20:04:13');

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `view_save_analysis`
-- (Vedi sotto per la vista effettiva)
--
DROP VIEW IF EXISTS `view_save_analysis`;
CREATE TABLE IF NOT EXISTS `view_save_analysis` (
`id` int(11)
,`label_analysis` varchar(256)
,`label_plant` varchar(256)
,`label_investment` varchar(256)
,`user_id` int(11)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Struttura stand-in per le viste `view_save_plants`
-- (Vedi sotto per la vista effettiva)
--
DROP VIEW IF EXISTS `view_save_plants`;
CREATE TABLE IF NOT EXISTS `view_save_plants` (
`id` int(11)
,`user_id` int(11)
,`label_plant` varchar(256)
,`municipality` varchar(60)
,`municipality_code` varchar(256)
,`created_at` timestamp
,`updated_at` timestamp
);

-- --------------------------------------------------------

--
-- Struttura per vista `view_save_analysis`
--
DROP TABLE IF EXISTS `view_save_analysis`;

CREATE ALGORITHM=UNDEFINED DEFINER=`edo`@`localhost` SQL SECURITY DEFINER VIEW `view_save_analysis`  AS  select `save_analysis`.`id` AS `id`,`save_analysis`.`label_analysis` AS `label_analysis`,`save_plants`.`label_plant` AS `label_plant`,`save_investments`.`label_investment` AS `label_investment`,`save_analysis`.`user_id` AS `user_id`,`save_analysis`.`created_at` AS `created_at`,`save_analysis`.`updated_at` AS `updated_at` from ((`save_analysis` left join `save_plants` on(`save_analysis`.`plant_id` = `save_plants`.`id`)) left join `save_investments` on(`save_analysis`.`investment_id` = `save_investments`.`id`)) ;

-- --------------------------------------------------------

--
-- Struttura per vista `view_save_plants`
--
DROP TABLE IF EXISTS `view_save_plants`;

CREATE ALGORITHM=UNDEFINED DEFINER=`edo`@`localhost` SQL SECURITY DEFINER VIEW `view_save_plants`  AS  select `save_plants`.`id` AS `id`,`save_plants`.`user_id` AS `user_id`,`save_plants`.`label_plant` AS `label_plant`,`municipalities`.`comune` AS `municipality`,`save_plants`.`municipality_code` AS `municipality_code`,`save_plants`.`created_at` AS `created_at`,`save_plants`.`updated_at` AS `updated_at` from (`save_plants` left join `municipalities` on(`save_plants`.`municipality_code` = `municipalities`.`istat`)) ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

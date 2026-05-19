-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: May 19, 2026 at 10:59 AM
-- Server version: 8.3.0
-- PHP Version: 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `jrj_travel_travelport`
--

-- --------------------------------------------------------

--
-- Table structure for table `api_logs`
--

DROP TABLE IF EXISTS `api_logs`;
CREATE TABLE IF NOT EXISTS `api_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int DEFAULT NULL,
  `endpoint` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `method` varchar(10) COLLATE utf8mb4_general_ci DEFAULT 'POST',
  `request_body` longtext COLLATE utf8mb4_general_ci,
  `response_body` longtext COLLATE utf8mb4_general_ci,
  `http_status` int DEFAULT NULL,
  `execution_time` float DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_log` (`booking_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_ref` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `pnr` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reservation_id` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `session_id` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `status` enum('pending','confirmed','ticketed','cancelled','failed') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `trip_type` enum('oneway','roundtrip','multicity') COLLATE utf8mb4_general_ci DEFAULT 'oneway',
  `origin` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `destination` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `cabin_class` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'Economy',
  `total_price` decimal(12,2) DEFAULT '0.00',
  `currency` varchar(5) COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `offer_data` longtext COLLATE utf8mb4_general_ci,
  `api_response` longtext COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `booking_ref` (`booking_ref`),
  KEY `idx_pnr` (`pnr`),
  KEY `idx_booking_ref` (`booking_ref`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `checkout_customers`
--

DROP TABLE IF EXISTS `checkout_customers`;
CREATE TABLE IF NOT EXISTS `checkout_customers` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `cko_customer_id` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flight_segments`
--

DROP TABLE IF EXISTS `flight_segments`;
CREATE TABLE IF NOT EXISTS `flight_segments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `segment_sequence` int DEFAULT '1',
  `carrier` varchar(5) COLLATE utf8mb4_general_ci NOT NULL,
  `flight_number` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `origin` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `destination` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `departure_date` date NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_date` date NOT NULL,
  `arrival_time` time NOT NULL,
  `cabin` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `class_of_service` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `duration` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `aircraft` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `stops` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_segment` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `oauth_tokens`
--

DROP TABLE IF EXISTS `oauth_tokens`;
CREATE TABLE IF NOT EXISTS `oauth_tokens` (
  `id` int NOT NULL AUTO_INCREMENT,
  `access_token` text COLLATE utf8mb4_general_ci NOT NULL,
  `token_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT 'Bearer',
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `passengers`
--

DROP TABLE IF EXISTS `passengers`;
CREATE TABLE IF NOT EXISTS `passengers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `traveler_id` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `passenger_type` enum('ADT','CNN','INF') COLLATE utf8mb4_general_ci DEFAULT 'ADT',
  `title` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `first_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `last_name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `gender` enum('Male','Female','Other') COLLATE utf8mb4_general_ci NOT NULL,
  `date_of_birth` date NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone_code` varchar(10) COLLATE utf8mb4_general_ci DEFAULT '1',
  `phone_number` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `passport_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `passport_country` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nationality` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `frequent_flyer_number` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `frequent_flyer_airline` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_primary` tinyint(1) DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
CREATE TABLE IF NOT EXISTS `payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `payment_method` enum('card','cash') COLLATE utf8mb4_general_ci DEFAULT 'card',
  `card_type` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_code` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_last_four` varchar(4) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `card_holder_name` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `amount` decimal(12,2) NOT NULL,
  `currency` varchar(5) COLLATE utf8mb4_general_ci DEFAULT 'USD',
  `status` enum('pending','completed','failed','refunded') COLLATE utf8mb4_general_ci DEFAULT 'pending',
  `transaction_ref` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_booking_payment` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payment_sessions`
--

DROP TABLE IF EXISTS `payment_sessions`;
CREATE TABLE IF NOT EXISTS `payment_sessions` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` int DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Checkout.com session ID (ps_xxx)',
  `payment_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Checkout.com payment ID (pay_xxx)',
  `reference` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `amount` int UNSIGNED NOT NULL COMMENT 'Amount in minor units',
  `charged_amount` int UNSIGNED DEFAULT NULL COMMENT 'Final charged amount (may include surcharge)',
  `currency` char(3) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'GBP',
  `customer_name` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `customer_email` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `description` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('pending','authorized','captured','declined','cancelled','expired') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pending',
  `payment_method` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cko_customer_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `raw_response` json DEFAULT NULL,
  `debug_log` json DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reference` (`reference`),
  KEY `idx_session` (`session_id`),
  KEY `idx_booking` (`booking_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `search_history`
--

DROP TABLE IF EXISTS `search_history`;
CREATE TABLE IF NOT EXISTS `search_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) COLLATE utf8mb4_general_ci NOT NULL,
  `trip_type` enum('oneway','roundtrip','multicity') COLLATE utf8mb4_general_ci DEFAULT 'oneway',
  `origin` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `destination` varchar(10) COLLATE utf8mb4_general_ci NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `adults` int DEFAULT '1',
  `children` int DEFAULT '0',
  `infants` int DEFAULT '0',
  `cabin_class` varchar(30) COLLATE utf8mb4_general_ci DEFAULT 'Economy',
  `search_response` longtext COLLATE utf8mb4_general_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_session` (`session_id`),
  KEY `idx_search_date` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `seat_assignments`
--

DROP TABLE IF EXISTS `seat_assignments`;
CREATE TABLE IF NOT EXISTS `seat_assignments` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `passenger_id` int DEFAULT NULL,
  `flight_segment` varchar(10) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g. EY68',
  `flight_from` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `flight_to` varchar(5) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `flight_date` date DEFAULT NULL,
  `seat_number` varchar(10) COLLATE utf8mb4_general_ci NOT NULL COMMENT 'e.g. 15A',
  `seat_price` decimal(10,2) DEFAULT '0.00',
  `currency` char(3) COLLATE utf8mb4_general_ci DEFAULT 'GBP',
  `status` enum('confirmed','pending','cancelled') COLLATE utf8mb4_general_ci DEFAULT 'confirmed',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_booking_flight_pax` (`booking_id`,`flight_segment`,`passenger_id`),
  KEY `idx_booking` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tickets`
--

DROP TABLE IF EXISTS `tickets`;
CREATE TABLE IF NOT EXISTS `tickets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `passenger_id` int NOT NULL,
  `ticket_number` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `status` enum('issued','voided','exchanged','refunded') COLLATE utf8mb4_general_ci DEFAULT 'issued',
  `issued_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `passenger_id` (`passenger_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `flight_segments`
--
ALTER TABLE `flight_segments`
  ADD CONSTRAINT `flight_segments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `passengers`
--
ALTER TABLE `passengers`
  ADD CONSTRAINT `passengers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payment_sessions`
--
ALTER TABLE `payment_sessions`
  ADD CONSTRAINT `payment_sessions_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `tickets`
--
ALTER TABLE `tickets`
  ADD CONSTRAINT `tickets_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tickets_ibfk_2` FOREIGN KEY (`passenger_id`) REFERENCES `passengers` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

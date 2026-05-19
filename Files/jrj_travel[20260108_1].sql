-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 08, 2026 at 04:46 PM
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
-- Database: `jrj_travel`
--

-- --------------------------------------------------------

--
-- Table structure for table `applicants`
--

DROP TABLE IF EXISTS `applicants`;
CREATE TABLE IF NOT EXISTS `applicants` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `ref_no` varchar(30) DEFAULT NULL,
  `full_name` varchar(120) NOT NULL,
  `mobile` varchar(30) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `nationality` varchar(80) NOT NULL,
  `current_location` enum('UAE','Outside UAE') NOT NULL,
  `city` varchar(80) NOT NULL,
  `position_category` varchar(80) NOT NULL,
  `other_position` varchar(120) DEFAULT NULL,
  `years_experience` tinyint UNSIGNED DEFAULT NULL,
  `preferred_work_location` varchar(40) DEFAULT NULL,
  `availability` enum('Immediately','Within 7 days','Within 15 days','Within 30 days','More than 30 days') NOT NULL,
  `visa_status` varchar(60) DEFAULT NULL,
  `notice_period` varchar(30) DEFAULT NULL,
  `expected_salary_aed` int UNSIGNED DEFAULT NULL,
  `communication_preference` enum('WhatsApp','Phone','Email') DEFAULT NULL,
  `consent` tinyint(1) NOT NULL DEFAULT '0',
  `lead_source` enum('WhatsApp','Email','Phone','Walk-in','Website','Other') NOT NULL DEFAULT 'Website',
  `status` enum('NEW','CV_RECEIVED','UNDER_REVIEW','SHORTLISTED','INTERVIEW','SELECTED','OFFERED','JOINED','REJECTED') NOT NULL DEFAULT 'CV_RECEIVED',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ref_no` (`ref_no`),
  KEY `idx_mobile` (`mobile`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_source` (`lead_source`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applicants_interviews`
--

DROP TABLE IF EXISTS `applicants_interviews`;
CREATE TABLE IF NOT EXISTS `applicants_interviews` (
  `id` int NOT NULL AUTO_INCREMENT,
  `applicant_id` int NOT NULL,
  `interview_at` datetime NOT NULL,
  `mode` enum('Phone','WhatsApp','Video','In-person') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `interviewer` varchar(120) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` enum('SCHEDULED','COMPLETED','CANCELLED') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'SCHEDULED',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_applicant` (`applicant_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applicants_reminders`
--

DROP TABLE IF EXISTS `applicants_reminders`;
CREATE TABLE IF NOT EXISTS `applicants_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `applicant_id` int NOT NULL,
  `reminder_at` datetime NOT NULL,
  `type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call, Email, Meeting, General',
  `contact_id` int DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `completed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_activity_logs`
--

DROP TABLE IF EXISTS `applicant_activity_logs`;
CREATE TABLE IF NOT EXISTS `applicant_activity_logs` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint UNSIGNED NOT NULL,
  `activity_type` enum('CREATED','DOC_UPLOADED','STATUS_CHANGED','NOTE','WHATSAPP_SENT','EMAIL_SENT','CALL_LOG','INTERVIEW_SCHEDULED') NOT NULL,
  `title` varchar(120) NOT NULL,
  `details` text,
  `meta_json` longtext,
  `created_by` varchar(80) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_applicant` (`applicant_id`),
  KEY `idx_type` (`activity_type`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_documents`
--

DROP TABLE IF EXISTS `applicant_documents`;
CREATE TABLE IF NOT EXISTS `applicant_documents` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint UNSIGNED NOT NULL,
  `doc_type` enum('CV','PASSPORT','VISA','CERTIFICATE','OTHER') NOT NULL DEFAULT 'OTHER',
  `doc_label` varchar(80) DEFAULT NULL,
  `original_filename` varchar(255) NOT NULL,
  `stored_filename` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `mime_type` varchar(120) NOT NULL,
  `size_bytes` bigint UNSIGNED NOT NULL,
  `sha256` char(64) DEFAULT NULL,
  `uploaded_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_applicant` (`applicant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applicant_status_history`
--

DROP TABLE IF EXISTS `applicant_status_history`;
CREATE TABLE IF NOT EXISTS `applicant_status_history` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `applicant_id` bigint UNSIGNED NOT NULL,
  `old_status` varchar(30) DEFAULT NULL,
  `new_status` varchar(30) NOT NULL,
  `note` varchar(255) DEFAULT NULL,
  `changed_by` varchar(80) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_applicant` (`applicant_id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `autologin_tokens`
--

DROP TABLE IF EXISTS `autologin_tokens`;
CREATE TABLE IF NOT EXISTS `autologin_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `person_id` int NOT NULL,
  `token` char(64) NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `used_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`),
  KEY `person_id` (`person_id`),
  KEY `token_2` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

DROP TABLE IF EXISTS `bank_accounts`;
CREATE TABLE IF NOT EXISTS `bank_accounts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `bank_name` varchar(255) NOT NULL,
  `account_number` varchar(150) NOT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `branch_id` varchar(150) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `bank_name`, `account_number`, `branch_name`, `branch_id`, `created_at`, `updated_at`) VALUES
(1, 'Emirates NBD', '1234567890123456', 'Dubai Main Branch', 'EBILAEAD', '2025-11-27 10:20:26', NULL),
(2, 'Dubai Islamic Bank', '1020304050607080', 'Bur Dubai Branch', 'DUIBAEAD', '2025-11-27 10:20:26', NULL),
(3, 'bname', '562354562345', 'USDYHFJKSD', '7GSDHF', '2025-11-28 06:35:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
CREATE TABLE IF NOT EXISTS `bookings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itype` enum('IN','OUT') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'IN=interaction from user, OUT=interaction to user',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `channel_id` int DEFAULT NULL,
  `contact_type_id` int DEFAULT NULL,
  `type_id` int DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `department` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `owner_id` int DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'open',
  `priority` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'normal',
  `nature` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'nature of interaction - when closed',
  `follow_date` date DEFAULT NULL,
  `follow_time` time DEFAULT NULL,
  `document_label` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `document_file` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `contact_entity_id` int DEFAULT NULL COMMENT 'id of chosen customer/employee/recruiter/supplier',
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entity_contact_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `related_employee_ids` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `related_customer_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  KEY `contact_type_id` (`contact_type_id`),
  KEY `scenario_id` (`type_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `itype`, `date`, `time`, `contact_name`, `channel_id`, `contact_type_id`, `type_id`, `subject`, `notes`, `department`, `owner_id`, `assigned_to`, `status`, `priority`, `nature`, `follow_date`, `follow_time`, `document_label`, `document_file`, `created_at`, `updated_at`, `contact_entity_id`, `contact_phone`, `contact_email`, `entity_contact_id`, `related_employee_ids`, `related_customer_id`) VALUES
(1, 'IN', '2026-01-08', '16:11:00', 'Customer 1', 1, 1, 1, 'sdfsd', 'sdfsdf', NULL, 2, 2, 'open', 'normal', '', '0000-00-00', '00:00:00', NULL, NULL, '2026-01-08 16:35:57', '2026-01-08 16:35:57', 1, '9876453663', 'customer1@gmail.com', '', '', ''),
(2, 'IN', '2026-01-08', '16:42:00', 'contact11', 1, 6, NULL, 'test', 'testnott', NULL, 2, 2, 'open', 'normal', '', '0000-00-00', '00:00:00', NULL, NULL, '2026-01-08 16:43:17', '2026-01-08 16:43:17', 1, '364634663', 'sdhfhsdfh@dfg.dfg', '1', '', ''),
(3, 'IN', '2026-01-08', '16:43:00', 'Joice1', 1, 4, 1, 'test', 'notes', NULL, 2, 2, 'open', 'normal', '', '0000-00-00', '00:00:00', NULL, NULL, '2026-01-08 16:46:02', '2026-01-08 16:46:02', 2, '2364562364', 'joice@gmail.com', '1', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `bookings_documents`
--

DROP TABLE IF EXISTS `bookings_documents`;
CREATE TABLE IF NOT EXISTS `bookings_documents` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` int UNSIGNED NOT NULL,
  `label` varchar(150) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` enum('pdf','image','video') NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings_flights`
--

DROP TABLE IF EXISTS `bookings_flights`;
CREATE TABLE IF NOT EXISTS `bookings_flights` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `booking_id` int UNSIGNED DEFAULT NULL,
  `amadeus_order_id` varchar(64) DEFAULT NULL,
  `pnr` varchar(32) DEFAULT NULL,
  `origin` char(3) NOT NULL,
  `destination` char(3) NOT NULL,
  `departure_date` date NOT NULL,
  `return_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `currency` char(3) NOT NULL DEFAULT 'GBP',
  `traveller_name` varchar(150) NOT NULL,
  `traveller_email` varchar(150) NOT NULL,
  `traveller_phone` varchar(30) DEFAULT NULL,
  `order_json` longtext NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `passport_number` varchar(32) DEFAULT NULL,
  `passport_expiry` date DEFAULT NULL,
  `passport_nationality` char(2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_booking_id` (`booking_id`),
  KEY `idx_pnr` (`pnr`),
  KEY `idx_route` (`origin`,`destination`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `bookings_flights`
--

INSERT INTO `bookings_flights` (`id`, `booking_id`, `amadeus_order_id`, `pnr`, `origin`, `destination`, `departure_date`, `return_date`, `total_amount`, `currency`, `traveller_name`, `traveller_email`, `traveller_phone`, `order_json`, `created_at`, `passport_number`, `passport_expiry`, `passport_nationality`) VALUES
(1, 1, 'eJzTd9e3DHZziTIBAAryAko', '9SFDZ4', 'CDG', 'DUB', '2026-01-08', '2026-01-14', 169.90, 'GBP', 'Customer Lname', 'customer1@gmail.com', '9876453663', '{\"type\":\"flight-order\",\"id\":\"eJzTd9e3DHZziTIBAAryAko\",\"queuingOfficeId\":\"NCE4D31SB\",\"associatedRecords\":[{\"reference\":\"9SFDZ4\",\"creationDate\":\"2026-01-08T16:35:00.000\",\"originSystemCode\":\"GDS\",\"flightOfferId\":\"2\"}],\"flightOffers\":[{\"type\":\"flight-offer\",\"id\":\"2\",\"source\":\"GDS\",\"nonHomogeneous\":false,\"lastTicketingDate\":\"2026-01-08\",\"itineraries\":[{\"segments\":[{\"departure\":{\"iataCode\":\"CDG\",\"terminal\":\"1\",\"at\":\"2026-01-08T21:20:00\"},\"arrival\":{\"iataCode\":\"DUB\",\"terminal\":\"2\",\"at\":\"2026-01-08T22:05:00\"},\"carrierCode\":\"EI\",\"number\":\"529\",\"aircraft\":{\"code\":\"320\"},\"duration\":\"PT1H45M\",\"id\":\"9\",\"numberOfStops\":0,\"co2Emissions\":[{\"weight\":72,\"weightUnit\":\"KG\",\"cabin\":\"ECONOMY\"}]}]},{\"segments\":[{\"departure\":{\"iataCode\":\"DUB\",\"terminal\":\"2\",\"at\":\"2026-01-14T09:50:00\"},\"arrival\":{\"iataCode\":\"CDG\",\"terminal\":\"1\",\"at\":\"2026-01-14T12:35:00\"},\"carrierCode\":\"EI\",\"number\":\"524\",\"aircraft\":{\"code\":\"32Q\"},\"duration\":\"PT1H45M\",\"id\":\"17\",\"numberOfStops\":0,\"co2Emissions\":[{\"weight\":72,\"weightUnit\":\"KG\",\"cabin\":\"ECONOMY\"}]}]}],\"price\":{\"currency\":\"GBP\",\"total\":\"169.90\",\"base\":\"81.00\",\"fees\":[{\"amount\":\"0.00\",\"type\":\"TICKETING\"},{\"amount\":\"0.00\",\"type\":\"SUPPLIER\"},{\"amount\":\"0.00\",\"type\":\"FORM_OF_PAYMENT\"}],\"grandTotal\":\"169.90\",\"billingCurrency\":\"GBP\"},\"pricingOptions\":{\"fareType\":[\"PUBLISHED\"],\"includedCheckedBagsOnly\":true},\"validatingAirlineCodes\":[\"EI\"],\"travelerPricings\":[{\"travelerId\":\"1\",\"fareOption\":\"STANDARD\",\"travelerType\":\"ADULT\",\"price\":{\"currency\":\"GBP\",\"total\":\"169.90\",\"base\":\"81.00\",\"taxes\":[{\"amount\":\"15.80\",\"code\":\"FR\"},{\"amount\":\"6.40\",\"code\":\"O4\"},{\"amount\":\"13.00\",\"code\":\"QX\"},{\"amount\":\"8.70\",\"code\":\"UP\"},{\"amount\":\"1.80\",\"code\":\"YQ\"},{\"amount\":\"43.20\",\"code\":\"YR\"}],\"refundableTaxes\":\"88.90\"},\"fareDetailsBySegment\":[{\"segmentId\":\"9\",\"cabin\":\"ECONOMY\",\"fareBasis\":\"GEUOW13G\",\"class\":\"G\",\"includedCheckedBags\":{\"weight\":20,\"weightUnit\":\"KG\"}},{\"segmentId\":\"17\",\"cabin\":\"ECONOMY\",\"fareBasis\":\"AEUOW13G\",\"class\":\"A\",\"includedCheckedBags\":{\"weight\":20,\"weightUnit\":\"KG\"}}]}]}],\"travelers\":[{\"id\":\"1\",\"name\":{\"firstName\":\"CUSTOMER\",\"lastName\":\"LNAME\"},\"contact\":{\"purpose\":\"STANDARD\",\"phones\":[{\"deviceType\":\"MOBILE\",\"countryCallingCode\":\"44\",\"number\":\"9876453663\"}],\"emailAddress\":\"customer1@gmail.com\"}}],\"remarks\":{\"general\":[{\"subType\":\"GENERAL_MISCELLANEOUS\",\"text\":\"ONLINE FLIGHT BOOKING\"}]},\"ticketingAgreement\":{\"option\":\"CONFIRM\"},\"automatedProcess\":[{\"code\":\"IMMEDIATE\",\"queue\":{\"number\":\"0\",\"category\":\"0\"},\"officeId\":\"NCE4D31SB\"}]}', '2026-01-08 16:35:57', NULL, NULL, NULL),
(2, 3, 'eJzTd9e3DHYLcYwEAAsVAmY', '9SFTAY', 'CDG', 'DUB', '2026-01-08', '2026-01-14', 169.90, 'GBP', 'Customer Name', 'customer1@gmail.com', '9876453663', '{\"type\":\"flight-order\",\"id\":\"eJzTd9e3DHYLcYwEAAsVAmY\",\"queuingOfficeId\":\"NCE4D31SB\",\"associatedRecords\":[{\"reference\":\"9SFTAY\",\"creationDate\":\"2026-01-08T16:46:00.000\",\"originSystemCode\":\"GDS\",\"flightOfferId\":\"2\"}],\"flightOffers\":[{\"type\":\"flight-offer\",\"id\":\"2\",\"source\":\"GDS\",\"nonHomogeneous\":false,\"lastTicketingDate\":\"2026-01-08\",\"itineraries\":[{\"segments\":[{\"departure\":{\"iataCode\":\"CDG\",\"terminal\":\"1\",\"at\":\"2026-01-08T21:20:00\"},\"arrival\":{\"iataCode\":\"DUB\",\"terminal\":\"2\",\"at\":\"2026-01-08T22:05:00\"},\"carrierCode\":\"EI\",\"number\":\"529\",\"aircraft\":{\"code\":\"320\"},\"duration\":\"PT1H45M\",\"id\":\"1\",\"numberOfStops\":0,\"co2Emissions\":[{\"weight\":72,\"weightUnit\":\"KG\",\"cabin\":\"ECONOMY\"}]}]},{\"segments\":[{\"departure\":{\"iataCode\":\"DUB\",\"terminal\":\"2\",\"at\":\"2026-01-14T09:50:00\"},\"arrival\":{\"iataCode\":\"CDG\",\"terminal\":\"1\",\"at\":\"2026-01-14T12:35:00\"},\"carrierCode\":\"EI\",\"number\":\"524\",\"aircraft\":{\"code\":\"32Q\"},\"duration\":\"PT1H45M\",\"id\":\"3\",\"numberOfStops\":0,\"co2Emissions\":[{\"weight\":72,\"weightUnit\":\"KG\",\"cabin\":\"ECONOMY\"}]}]}],\"price\":{\"currency\":\"GBP\",\"total\":\"169.90\",\"base\":\"81.00\",\"fees\":[{\"amount\":\"0.00\",\"type\":\"TICKETING\"},{\"amount\":\"0.00\",\"type\":\"SUPPLIER\"},{\"amount\":\"0.00\",\"type\":\"FORM_OF_PAYMENT\"}],\"grandTotal\":\"169.90\",\"billingCurrency\":\"GBP\"},\"pricingOptions\":{\"fareType\":[\"PUBLISHED\"],\"includedCheckedBagsOnly\":true},\"validatingAirlineCodes\":[\"EI\"],\"travelerPricings\":[{\"travelerId\":\"1\",\"fareOption\":\"STANDARD\",\"travelerType\":\"ADULT\",\"price\":{\"currency\":\"GBP\",\"total\":\"169.90\",\"base\":\"81.00\",\"taxes\":[{\"amount\":\"15.80\",\"code\":\"FR\"},{\"amount\":\"6.40\",\"code\":\"O4\"},{\"amount\":\"13.00\",\"code\":\"QX\"},{\"amount\":\"8.70\",\"code\":\"UP\"},{\"amount\":\"1.80\",\"code\":\"YQ\"},{\"amount\":\"43.20\",\"code\":\"YR\"}],\"refundableTaxes\":\"88.90\"},\"fareDetailsBySegment\":[{\"segmentId\":\"1\",\"cabin\":\"ECONOMY\",\"fareBasis\":\"GEUOW13G\",\"class\":\"G\",\"includedCheckedBags\":{\"weight\":20,\"weightUnit\":\"KG\"}},{\"segmentId\":\"3\",\"cabin\":\"ECONOMY\",\"fareBasis\":\"AEUOW13G\",\"class\":\"A\",\"includedCheckedBags\":{\"weight\":20,\"weightUnit\":\"KG\"}}]}]}],\"travelers\":[{\"id\":\"1\",\"name\":{\"firstName\":\"CUSTOMER\",\"lastName\":\"NAME\"},\"contact\":{\"purpose\":\"STANDARD\",\"phones\":[{\"deviceType\":\"MOBILE\",\"countryCallingCode\":\"44\",\"number\":\"9876453663\"}],\"emailAddress\":\"customer1@gmail.com\"}}],\"remarks\":{\"general\":[{\"subType\":\"GENERAL_MISCELLANEOUS\",\"text\":\"ONLINE FLIGHT BOOKING\"}]},\"ticketingAgreement\":{\"option\":\"CONFIRM\"},\"automatedProcess\":[{\"code\":\"IMMEDIATE\",\"queue\":{\"number\":\"0\",\"category\":\"0\"},\"officeId\":\"NCE4D31SB\"}]}', '2026-01-08 16:46:02', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bookings_followup`
--

DROP TABLE IF EXISTS `bookings_followup`;
CREATE TABLE IF NOT EXISTS `bookings_followup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `booking_id` int NOT NULL,
  `note_text` text NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_by_name` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `incident_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bookings_types`
--

DROP TABLE IF EXISTS `bookings_types`;
CREATE TABLE IF NOT EXISTS `bookings_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `inter_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings_types`
--

INSERT INTO `bookings_types` (`id`, `name`, `slug`, `inter_type`, `created_at`) VALUES
(1, 'Flights', 'flights', 'customer', '2025-12-08 14:58:34'),
(2, 'Tours', 'tours', 'customer', '2025-12-08 14:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `channels`
--

DROP TABLE IF EXISTS `channels`;
CREATE TABLE IF NOT EXISTS `channels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `channels`
--

INSERT INTO `channels` (`id`, `name`, `slug`, `created_at`) VALUES
(1, 'Phone', 'phone', '2025-12-08 14:58:34'),
(2, 'WhatsApp', 'whatsapp', '2025-12-08 14:58:34'),
(3, 'Email', 'email', '2025-12-08 14:58:34'),
(4, 'Walk-in', 'walkin', '2025-12-08 14:58:34');

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

DROP TABLE IF EXISTS `contacts`;
CREATE TABLE IF NOT EXISTS `contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(20) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `company` varchar(150) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `photo1` text,
  `enable_email` int NOT NULL DEFAULT '0',
  `enable_whatsapp` int DEFAULT '0',
  `archived` int NOT NULL DEFAULT '0',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `agent_id`, `name`, `company`, `phone`, `email`, `photo`, `photo1`, `enable_email`, `enable_whatsapp`, `archived`, `date_added`) VALUES
(1, NULL, 'Contact1', 'Company1', '449562498181', 'contact1@dfjg.fg', NULL, NULL, 0, 0, 0, '2026-01-08 15:49:26');

-- --------------------------------------------------------

--
-- Table structure for table `contacts_contacts`
--

DROP TABLE IF EXISTS `contacts_contacts`;
CREATE TABLE IF NOT EXISTS `contacts_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contact_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `photo2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `designation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`contact_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts_contacts`
--

INSERT INTO `contacts_contacts` (`id`, `contact_id`, `name`, `phone`, `email`, `photo1`, `photo2`, `designation`, `created_by`, `created_at`) VALUES
(1, 1, 'contact11', '364634663', 'sdhfhsdfh@dfg.dfg', NULL, NULL, 'des11', NULL, '2026-01-08 15:50:06');

-- --------------------------------------------------------

--
-- Table structure for table `contacts_logs`
--

DROP TABLE IF EXISTS `contacts_logs`;
CREATE TABLE IF NOT EXISTS `contacts_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contact_id` int NOT NULL,
  `agent_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call,Email,Meeting,General',
  `visibility` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Public' COMMENT 'Private,Public',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts_logs`
--

INSERT INTO `contacts_logs` (`id`, `contact_id`, `agent_id`, `name`, `notes`, `type`, `visibility`, `created_at`) VALUES
(1, 1, 0, 'contact11', 'Channel: Phone. Summary: test. Notes: testnott. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2026-01-08 11:13:18');

-- --------------------------------------------------------

--
-- Table structure for table `contacts_reminders`
--

DROP TABLE IF EXISTS `contacts_reminders`;
CREATE TABLE IF NOT EXISTS `contacts_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reminder_at` datetime NOT NULL,
  `type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call, Email, Meeting, General',
  `contact_id` int DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `completed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `contact_types`
--

DROP TABLE IF EXISTS `contact_types`;
CREATE TABLE IF NOT EXISTS `contact_types` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `edit_url` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'contacts_view',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_types`
--

INSERT INTO `contact_types` (`id`, `name`, `slug`, `edit_url`, `created_at`) VALUES
(1, 'Existing Customer', 'customer', 'customers_view', '2025-12-08 14:58:34'),
(2, 'New Contact', 'new', 'contacts_view', '2025-12-08 14:58:34'),
(4, 'Existing Supplier', 'vendor', 'suppliers_view', '2025-12-08 14:58:34'),
(6, 'Existing Contact', 'existing-contact', 'contacts_view', '2025-12-09 12:36:18');

-- --------------------------------------------------------

--
-- Table structure for table `created_documents`
--

DROP TABLE IF EXISTS `created_documents`;
CREATE TABLE IF NOT EXISTS `created_documents` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_version_id` int UNSIGNED NOT NULL,
  `entity_type` enum('employee','customer','recruiter','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `entity_identifier` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `add_to_profile` int NOT NULL DEFAULT '0' COMMENT '1=added to profile',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `template_version_id` (`template_version_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trn` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'all phone numbers',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `whatsapp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `industry` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `services` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `google_rating` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `favourite` int NOT NULL DEFAULT '0',
  `archived` int NOT NULL DEFAULT '0',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Suspect' COMMENT 'Suspect — you have a name, not verified yet.\r\nLead (Active) — you’ve reached out / they responded.\r\nOpportunity — quote/proposal/demo sent; decision pending.\r\nWon — became a customer.\r\nArchive — not a fit or chose someone else (capture reason).',
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `timezone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'from excel' COMMENT 'from ',
  `enable_email` int NOT NULL DEFAULT '0' COMMENT 'enable feature',
  `enable_whatsapp` int NOT NULL DEFAULT '0' COMMENT 'enable feature',
  `fil_domains` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter domains comma seperated',
  `fil_emails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter emails comma seperated',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `agent_id`, `name`, `trn`, `company`, `phones`, `phone`, `whatsapp`, `email`, `industry`, `address`, `city`, `state`, `country`, `website`, `services`, `google_rating`, `favourite`, `archived`, `type`, `photo`, `photo1`, `timezone`, `source`, `enable_email`, `enable_whatsapp`, `fil_domains`, `fil_emails`, `created_at`, `updated_at`) VALUES
(1, '2', 'Customer 1', NULL, 'Company 1', '[\"9876453663\"]', '9876453663', NULL, 'customer1@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Work in progress', 'vaOu2EqIY0s7y8aLJHr4FYsZXQNtn3ZddS7gzSvM251104114045.jpg', NULL, NULL, 'from excel', 0, 0, NULL, 'customer1@gmail.com', '2025-11-04 17:10:46', NULL),
(2, '2', 'Customer 2', NULL, 'Company 2', '[\"7834566345\"]', '7834566345', NULL, 'customer2xx@gmail.com', 'Pipes and Fittings ', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Active', 'tqnvdBWlmcQvsg7T4a93AUUHb4bIc3p57Rubw0dk251106062146.jpg', 'O7yYpk1m9DndwEAsEGbtmLuXhjnGRoordt4FWMOB251107092105.jpg', NULL, 'from excel', 0, 1, NULL, 'customer2xx@gmail.com', '2025-11-04 17:16:13', NULL),
(3, '2', 'Customer 3', 'TRN67346343X', 'Company 3', '[\"7636726354\"]', '7636726354', NULL, 'customer3@gmail.com', 'New Industry', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Active', 'josnNCMJV79TFjrWIfOndoqndLAH0FIDaEssldm6251105080622.jpg', NULL, NULL, 'from excel', 1, 1, NULL, 'customer3@gmail.com', '2025-11-05 13:36:23', NULL),
(4, '2', 'CRahul', NULL, 'ABC Company', '[]', '', NULL, '', 'Industry477', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Active', NULL, NULL, NULL, 'from excel', 0, 0, NULL, '', '2025-11-18 13:14:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers_contacts`
--

DROP TABLE IF EXISTS `customers_contacts`;
CREATE TABLE IF NOT EXISTS `customers_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `photo2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `designation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_documents`
--

DROP TABLE IF EXISTS `customers_documents`;
CREATE TABLE IF NOT EXISTS `customers_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `label` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_type` enum('pdf','image') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_invoices`
--

DROP TABLE IF EXISTS `customers_invoices`;
CREATE TABLE IF NOT EXISTS `customers_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `invoice_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `vat_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `payment_status` enum('Paid','Unpaid','Partial Paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Unpaid',
  `document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_logs`
--

DROP TABLE IF EXISTS `customers_logs`;
CREATE TABLE IF NOT EXISTS `customers_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `agent_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call,Email,Meeting,General',
  `visibility` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Public' COMMENT 'Private,Public',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_payments`
--

DROP TABLE IF EXISTS `customers_payments`;
CREATE TABLE IF NOT EXISTS `customers_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Income/Expense',
  `category` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `payment_status` enum('Paid','Unpaid','Partial Paid','Pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Unpaid',
  `invoice_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `invoice_partial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `invoice_payment_method` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reclaim_by` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Company,Employee',
  `reimbursable` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Yes, No',
  `card_last4` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cheque_bank` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cheque_issuer` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reimbursement_amount` decimal(10,2) DEFAULT NULL,
  `document` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'file names json',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_quotations`
--

DROP TABLE IF EXISTS `customers_quotations`;
CREATE TABLE IF NOT EXISTS `customers_quotations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `ref_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `version` int DEFAULT '1',
  `quotation_name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quotation_date` date DEFAULT NULL,
  `attention` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `jobs_json` json DEFAULT NULL,
  `terms_json` json DEFAULT NULL,
  `closing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` enum('draft','final') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_reminders`
--

DROP TABLE IF EXISTS `customers_reminders`;
CREATE TABLE IF NOT EXISTS `customers_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `reminder_at` datetime NOT NULL,
  `type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call, Email, Meeting, General',
  `contact_id` int DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `completed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_requirements`
--

DROP TABLE IF EXISTS `customers_requirements`;
CREATE TABLE IF NOT EXISTS `customers_requirements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `job_title` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `num_employees` int NOT NULL,
  `rate_pay` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `expiry` date DEFAULT NULL,
  `expiry_alert` int NOT NULL DEFAULT '0' COMMENT '1=show expiry alert in dashboard',
  `req_type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Enquiry',
  `accommodation` tinyint(1) DEFAULT '0',
  `transport` tinyint(1) DEFAULT '0',
  `overtime` tinyint(1) DEFAULT '0',
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `attachment` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accommodation_details` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transport_details` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `overtime_policies` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_sites`
--

DROP TABLE IF EXISTS `customers_sites`;
CREATE TABLE IF NOT EXISTS `customers_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `site_name` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `site_contact` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `site_address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `site_location` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_soa`
--

DROP TABLE IF EXISTS `customers_soa`;
CREATE TABLE IF NOT EXISTS `customers_soa` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `date` date DEFAULT NULL,
  `invoiceno` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `type` enum('Invoice','Payment') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  `ref_no` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_invoice` (`invoiceno`),
  KEY `idx_type` (`type`),
  KEY `idx_ref` (`ref_no`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customers_trades`
--

DROP TABLE IF EXISTS `customers_trades`;
CREATE TABLE IF NOT EXISTS `customers_trades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `trade_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_trades`
--

INSERT INTO `customers_trades` (`id`, `trade_name`, `status`) VALUES
(1, 'Carpenter', 1),
(2, 'Bricklayer', 1),
(3, 'Construction Carpenter Assistant', 1),
(4, 'Construction Worker', 1),
(5, 'Brick Mason Assistant', 1),
(6, 'Tile Layer', 1),
(7, 'Pipe Fitter', 1),
(8, 'TradeY', 1),
(9, 'TradeY1', 1),
(10, 'TradeY2', 1),
(11, 'TestY3', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer_invoices`
--

DROP TABLE IF EXISTS `customer_invoices`;
CREATE TABLE IF NOT EXISTS `customer_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `month` char(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `invoice_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `reference_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `invoice_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `vat_amount` decimal(10,2) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `paid` int NOT NULL DEFAULT '0' COMMENT '1=marked as paid',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `balance_amount` decimal(12,2) NOT NULL DEFAULT '0.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_invoice` (`customer_id`,`month`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_invoice_timesheets`
--

DROP TABLE IF EXISTS `customer_invoice_timesheets`;
CREATE TABLE IF NOT EXISTS `customer_invoice_timesheets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_id` int NOT NULL,
  `timesheet_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_map` (`invoice_id`,`timesheet_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_site_trades`
--

DROP TABLE IF EXISTS `customer_site_trades`;
CREATE TABLE IF NOT EXISTS `customer_site_trades` (
  `id` int NOT NULL AUTO_INCREMENT,
  `site_id` int NOT NULL,
  `trade_id` int NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_site_trade` (`site_id`,`trade_id`),
  KEY `trade_id` (`trade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_timesheets`
--

DROP TABLE IF EXISTS `customer_timesheets`;
CREATE TABLE IF NOT EXISTS `customer_timesheets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `invoice_no` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `customer_id` int NOT NULL,
  `month` char(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `site_id` int DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT '0.00',
  `vat_amount` decimal(10,2) DEFAULT '0.00',
  `total_amount` decimal(10,2) DEFAULT '0.00',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_customer_month` (`customer_id`,`month`,`site_id`) USING BTREE
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_timesheet_items`
--

DROP TABLE IF EXISTS `customer_timesheet_items`;
CREATE TABLE IF NOT EXISTS `customer_timesheet_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `timesheet_id` int NOT NULL,
  `employee_id` int NOT NULL,
  `emp_code` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `employee_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `trade_id` int DEFAULT NULL COMMENT 'customers trades id',
  `trade` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `normal_hours` decimal(8,2) DEFAULT NULL,
  `ot_hours` decimal(8,2) DEFAULT NULL,
  `hot_hours` decimal(8,2) DEFAULT NULL,
  `rate_normal` decimal(8,2) DEFAULT NULL,
  `rate_ot` decimal(8,2) DEFAULT NULL,
  `rate_hot` decimal(8,2) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `timesheet_id` (`timesheet_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_trade_rates`
--

DROP TABLE IF EXISTS `customer_trade_rates`;
CREATE TABLE IF NOT EXISTS `customer_trade_rates` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `customer_id` int UNSIGNED NOT NULL,
  `site_id` int UNSIGNED NOT NULL,
  `trade_id` int UNSIGNED NOT NULL,
  `rate_per_hour` decimal(10,3) DEFAULT NULL,
  `is_fixed_rate` int NOT NULL DEFAULT '0' COMMENT '1=rate_per_hour is fixed for month',
  `food_allowance` tinyint NOT NULL DEFAULT '0',
  `travel_allowance` tinyint NOT NULL DEFAULT '0',
  `accommodation_allowance` tinyint NOT NULL DEFAULT '0',
  `allow_overtime` int NOT NULL DEFAULT '1' COMMENT '1=allowed, 0=not allowed',
  `not_rate` decimal(10,3) DEFAULT NULL,
  `hot_rate` decimal(10,3) DEFAULT NULL,
  `phot_rate` decimal(10,3) DEFAULT NULL,
  `default_hours` decimal(8,2) NOT NULL DEFAULT '8.00',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_customer_site_trade` (`customer_id`,`site_id`,`trade_id`),
  KEY `idx_site` (`site_id`),
  KEY `idx_trade` (`trade_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_followup`
--

DROP TABLE IF EXISTS `daily_followup`;
CREATE TABLE IF NOT EXISTS `daily_followup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(20) NOT NULL,
  `contact_id` varchar(20) NOT NULL,
  `date_followup` varchar(20) NOT NULL,
  `status_followup` varchar(50) NOT NULL,
  `count_of` int NOT NULL DEFAULT '0',
  `reminder_done` tinyint(1) NOT NULL DEFAULT '0',
  `note_done` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `daily_followup`
--

INSERT INTO `daily_followup` (`id`, `agent_id`, `contact_id`, `date_followup`, `status_followup`, `count_of`, `reminder_done`, `note_done`) VALUES
(1, '2', '1', '2025-10-30', 'Prospect', 2, 1, 1),
(2, '2', '8', '2025-10-30', 'Suspect', 2, 1, 1),
(3, '2', '9', '2025-10-31', 'Active', 1, 1, 0),
(4, '2', '7', '2025-10-31', 'Prospect', 1, 1, 0),
(5, '2', '1', '2025-10-31', 'Prospect', 1, 1, 0),
(6, '2', '43', '2025-11-03', 'Inactive', 2, 1, 1),
(7, '2', '2', '2025-11-05', 'Inactive', 2, 1, 1),
(8, '2', '2', '2025-11-07', 'Active', 2, 1, 1),
(9, '2', '3', '2025-11-07', 'Suspect', 1, 0, 1),
(10, '4', '3', '2025-11-07', 'Suspect', 1, 0, 1),
(11, '4', '2', '2025-11-07', 'Active', 1, 0, 1),
(12, '2', '1', '2025-11-10', 'Active', 1, 0, 1),
(13, '2', '1', '2025-11-11', 'Active', 1, 0, 1),
(14, '2', '2', '2025-11-24', 'Active', 1, 1, 0),
(15, '2', '4', '2025-12-04', 'Active', 1, 1, 0),
(16, '2', '10', '2025-12-10', 'Suspect', 1, 0, 1),
(17, '2', '4', '2025-12-10', 'Active', 2, 1, 1),
(18, '2', '1', '2025-12-30', 'Active', 1, 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `daily_routine_config`
--

DROP TABLE IF EXISTS `daily_routine_config`;
CREATE TABLE IF NOT EXISTS `daily_routine_config` (
  `id` int NOT NULL AUTO_INCREMENT,
  `status` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `total_followups` int NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `daily_routine_config`
--

INSERT INTO `daily_routine_config` (`id`, `status`, `total_followups`, `created_at`, `updated_at`) VALUES
(1, 'Active', 5, '2025-10-28 14:06:27', '2025-10-28 14:06:27'),
(2, 'Prospect', 5, '2025-10-28 14:06:27', '2025-10-28 14:06:27'),
(3, 'Suspect', 5, '2025-10-28 14:06:27', '2025-10-28 14:06:27'),
(4, 'Work in progress', 5, '2025-10-28 14:06:27', '2025-10-28 14:06:27'),
(5, 'Inactive', 5, '2025-10-28 14:06:27', '2025-10-28 14:06:27'),
(6, 'Dead', 5, '2025-10-28 14:06:27', '2025-10-28 14:06:27');

-- --------------------------------------------------------

--
-- Table structure for table `document_labels`
--

DROP TABLE IF EXISTS `document_labels`;
CREATE TABLE IF NOT EXISTS `document_labels` (
  `id` int NOT NULL AUTO_INCREMENT,
  `label` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `label` (`label`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `document_labels`
--

INSERT INTO `document_labels` (`id`, `label`, `created_at`) VALUES
(1, 'Offer letter', '2025-12-17 08:37:16'),
(2, 'Emirates ID', '2025-12-17 08:37:16'),
(3, 'Visa', '2025-12-17 08:37:16'),
(4, 'Passport', '2025-12-17 08:37:16'),
(5, 'Labour card', '2025-12-17 08:37:16'),
(6, 'Security pass', '2025-12-17 08:37:16'),
(7, 'Insurance card', '2025-12-17 08:37:16'),
(8, 'Driving Licence', '2025-12-17 08:37:16'),
(9, 'Final Settlement', '2025-12-17 08:37:16'),
(10, 'New label', '2025-12-17 11:45:52'),
(11, 'ee', '2025-12-17 11:56:10'),
(12, 'TestL', '2025-12-17 11:57:25');

-- --------------------------------------------------------

--
-- Table structure for table `email_attachments`
--

DROP TABLE IF EXISTS `email_attachments`;
CREATE TABLE IF NOT EXISTS `email_attachments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email_id` int NOT NULL,
  `filename` varchar(255) DEFAULT NULL,
  `path` varchar(1024) DEFAULT NULL,
  `size` int DEFAULT NULL,
  `mime` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `email_log`
--

DROP TABLE IF EXISTS `email_log`;
CREATE TABLE IF NOT EXISTS `email_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mailbox_id` int DEFAULT '0',
  `folder` varchar(64) DEFAULT 'INBOX',
  `message_id` varchar(255) DEFAULT '',
  `in_reply_to` varchar(255) DEFAULT NULL,
  `references_chain` text,
  `subject` text,
  `from_name` varchar(255) DEFAULT '',
  `from_email` varchar(255) DEFAULT '',
  `to_emails` text,
  `cc` text,
  `bcc` text,
  `body_html` longtext,
  `body_text` longtext,
  `is_sent` tinyint(1) DEFAULT '0',
  `sent_via` varchar(32) DEFAULT NULL,
  `thread_id` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3;

-- --------------------------------------------------------

--
-- Table structure for table `general_sites`
--

DROP TABLE IF EXISTS `general_sites`;
CREATE TABLE IF NOT EXISTS `general_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `site_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `general_sites`
--

INSERT INTO `general_sites` (`id`, `site_name`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Office', NULL, '2025-11-26 11:54:38', '2025-11-26 11:54:38'),
(2, 'Vacation', NULL, '2025-11-26 11:54:46', '2025-11-26 11:55:24'),
(3, 'Camp', NULL, '2025-11-26 11:55:14', '2025-11-26 11:55:14');

-- --------------------------------------------------------

--
-- Table structure for table `inbound_emails`
--

DROP TABLE IF EXISTS `inbound_emails`;
CREATE TABLE IF NOT EXISTS `inbound_emails` (
  `id` int NOT NULL AUTO_INCREMENT,
  `mailbox_id` int DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `sender` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `recipient` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `subject` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `body_html` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `body_text` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `message_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `in_reply_to` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `references_chain` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `thread_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email_log_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `email_log_id` (`email_log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `incident_categories`
--

DROP TABLE IF EXISTS `incident_categories`;
CREATE TABLE IF NOT EXISTS `incident_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `category` varchar(150) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `incident_categories`
--

INSERT INTO `incident_categories` (`id`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Late', '2025-12-04 10:22:53', '2025-12-04 10:24:08'),
(2, 'Absent', '2025-12-04 10:22:53', NULL),
(3, 'Misconduct', '2025-12-04 10:22:53', NULL),
(4, 'Warning', '2025-12-04 10:22:53', NULL),
(5, 'Accident', '2025-12-04 10:22:53', NULL),
(6, 'Test8', '2025-12-04 11:33:25', NULL),
(7, 'Test37', '2025-12-04 11:37:42', NULL),
(8, 'tes63', '2025-12-04 11:40:42', NULL),
(9, 'test7', '2025-12-04 11:45:01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `interactions`
--

DROP TABLE IF EXISTS `interactions`;
CREATE TABLE IF NOT EXISTS `interactions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `itype` enum('IN','OUT') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'IN=interaction from user, OUT=interaction to user',
  `date` date NOT NULL,
  `time` time NOT NULL,
  `contact_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `channel_id` int DEFAULT NULL,
  `contact_type_id` int DEFAULT NULL,
  `scenario_id` int DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `department` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `owner_id` int DEFAULT NULL,
  `assigned_to` int DEFAULT NULL,
  `status` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'open',
  `priority` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'normal',
  `nature` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'nature of interaction - when closed',
  `follow_date` date DEFAULT NULL,
  `follow_time` time DEFAULT NULL,
  `document_label` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `document_file` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `contact_entity_id` int DEFAULT NULL COMMENT 'id of chosen customer/employee/recruiter/supplier',
  `contact_phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `contact_email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `entity_contact_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `related_employee_ids` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `related_customer_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `channel_id` (`channel_id`),
  KEY `contact_type_id` (`contact_type_id`),
  KEY `scenario_id` (`scenario_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interactions_documents`
--

DROP TABLE IF EXISTS `interactions_documents`;
CREATE TABLE IF NOT EXISTS `interactions_documents` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `interaction_id` int UNSIGNED NOT NULL,
  `label` varchar(150) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_type` enum('pdf','image','video') NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `interactions_followup`
--

DROP TABLE IF EXISTS `interactions_followup`;
CREATE TABLE IF NOT EXISTS `interactions_followup` (
  `id` int NOT NULL AUTO_INCREMENT,
  `interaction_id` int NOT NULL,
  `note_text` text NOT NULL,
  `created_by` int DEFAULT NULL,
  `created_by_name` varchar(150) DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `incident_id` (`interaction_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoice_categories`
--

DROP TABLE IF EXISTS `invoice_categories`;
CREATE TABLE IF NOT EXISTS `invoice_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Expense' COMMENT 'Income,Expense',
  `category` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=47 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoice_categories`
--

INSERT INTO `invoice_categories` (`id`, `type`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Expense', 'Recruitment', '2025-11-11 13:54:17', NULL),
(2, 'Expense', 'Training', '2025-11-11 13:54:17', NULL),
(3, 'Expense', 'Paperwork', '2025-11-11 13:54:17', NULL),
(4, 'Expense', 'Visa', '2025-11-11 13:54:17', NULL),
(5, 'Expense', 'Medical', '2025-11-11 13:54:17', NULL),
(6, 'Expense', 'Transport', '2025-11-11 13:54:17', NULL),
(7, 'Expense', 'Candidate Payment', '2025-11-11 13:54:17', NULL),
(8, 'Expense', 'Sim Card', '2025-11-11 13:55:37', NULL),
(9, 'Expense', 'Traffic fine', '2025-11-11 13:55:37', NULL),
(10, 'Expense', 'Sim Expire Fine', '2025-11-11 13:55:37', NULL),
(11, 'Expense', 'Car Repair Cost', '2025-11-11 13:55:37', NULL),
(12, 'Expense', 'Testc', '2025-11-11 10:07:54', NULL),
(13, 'Expense', 'Other', '2025-11-11 10:17:31', NULL),
(14, 'Income', 'newcat45', '2025-11-12 15:07:50', '2025-11-15 19:11:42'),
(15, 'Income', 'newcat1', '2025-11-12 15:10:26', NULL),
(16, 'Income', 'newcat2', '2025-11-12 15:15:04', NULL),
(17, 'Income', 'newcat3', '2025-11-12 15:19:50', NULL),
(18, 'Income', 'newcat4', '2025-11-12 15:20:26', NULL),
(19, 'Income', 'newcat5', '2025-11-12 15:21:56', NULL),
(20, 'Income', 'General', '2025-11-12 10:26:56', NULL),
(21, 'Income', 'Test5', '2025-11-13 10:08:03', NULL),
(23, 'Expense', 'newCat65', '2025-11-14 11:02:37', '2025-11-15 19:12:13'),
(24, 'Income', 'Cat35', '2025-11-14 20:22:39', NULL),
(25, 'Income', 'Newi1', '2025-11-15 12:47:42', NULL),
(26, 'Expense', 'tes445', '2025-11-15 14:11:40', NULL),
(27, 'Income', 'icat36', '2025-11-15 14:12:19', NULL),
(28, 'Income', 'sss', '2025-11-15 14:15:30', NULL),
(29, 'Income', 'icat3', '2025-11-15 14:17:46', NULL),
(30, 'Income', 'icat4', '2025-11-15 14:19:03', NULL),
(31, 'Income', 'icat5', '2025-11-15 14:20:59', NULL),
(32, 'Income', 'icat6', '2025-11-15 14:26:05', NULL),
(33, 'Income', 'icat7', '2025-11-15 14:26:23', NULL),
(34, 'Income', 'icat8', '2025-11-15 14:27:31', NULL),
(35, 'Income', 'NewIcat364', '2025-11-18 10:16:26', NULL),
(36, 'Received', 'NewIca6', '2025-11-18 12:20:48', NULL),
(37, 'Received', 'newCat56', '2025-11-18 12:23:21', NULL),
(38, 'Received', 'cate6', '2025-11-18 12:26:36', NULL),
(39, 'Received', 'Tets63', '2025-11-18 12:55:01', NULL),
(40, 'Sent', 'test6', '2025-11-18 12:56:04', NULL),
(41, 'Received', 'Newcat4', '2025-11-19 11:21:21', NULL),
(42, 'Received', 'Newcat35', '2025-11-24 10:22:57', NULL),
(43, 'Received', 'new cat53', '2025-11-24 10:23:51', NULL),
(44, 'Received', 'newcat', '2025-11-24 10:25:37', NULL),
(45, 'Received', 'New63', '2025-11-24 10:27:09', NULL),
(46, 'Received', 'new76', '2025-11-24 14:16:55', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `job_titles`
--

DROP TABLE IF EXISTS `job_titles`;
CREATE TABLE IF NOT EXISTS `job_titles` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `title` (`title`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_titles`
--

INSERT INTO `job_titles` (`id`, `title`, `created_at`) VALUES
(1, 'General Helper', '2025-11-13 10:42:59'),
(2, 'Construction Worker', '2025-11-13 10:42:59'),
(3, 'Security Guard', '2025-11-13 10:42:59'),
(4, 'Cleaner', '2025-11-13 10:42:59'),
(5, 'Truck Operator', '2025-11-13 10:42:59'),
(6, 'New1', '2025-11-13 11:05:19'),
(7, 'test6', '2025-11-13 11:35:15'),
(8, 'Test Title', '2025-11-15 13:37:05'),
(10, 'Test Job37', '2025-11-15 13:38:06'),
(11, 'current trade1', '2025-11-21 07:41:42');

-- --------------------------------------------------------

--
-- Table structure for table `mailboxes`
--

DROP TABLE IF EXISTS `mailboxes`;
CREATE TABLE IF NOT EXISTS `mailboxes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) NOT NULL,
  `host` varchar(255) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` text NOT NULL,
  `port` int DEFAULT '465',
  `smtp_secure` varchar(10) DEFAULT 'ssl',
  `folder_inbox` varchar(64) DEFAULT 'INBOX',
  `folder_sent` varchar(64) DEFAULT 'Sent',
  `active` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `sent_host` varchar(255) DEFAULT NULL,
  `sent_username` varchar(255) DEFAULT NULL,
  `sent_password` varchar(255) DEFAULT NULL,
  `sent_port` int DEFAULT NULL,
  `sent_smtp_secure` varchar(10) DEFAULT NULL,
  `sent_folder_inbox` varchar(128) DEFAULT 'INBOX',
  `person_id` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `person_id` (`person_id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `mailboxes`
--

INSERT INTO `mailboxes` (`id`, `name`, `host`, `username`, `password`, `port`, `smtp_secure`, `folder_inbox`, `folder_sent`, `active`, `created_at`, `sent_host`, `sent_username`, `sent_password`, `sent_port`, `sent_smtp_secure`, `sent_folder_inbox`, `person_id`) VALUES
(1, 'Dileep', 'mail.jrjconnect.com', 'dileep@jrjconnect.com', 'fdhghdf', 465, 'ssl', 'Inbox', 'Sent', 1, '2025-10-17 06:25:47', 'mail.jrjconnect.com', 'dileep_out', 'hgsdf', 993, 'ssl', 'INBOX', 1),
(2, 'Nithin', 'mail.jrjconnect.com', 'nithin@jrjconnect.com', 'hdfjkgd', 465, 'ssl', 'Inbox', 'Sent', 1, '2025-10-17 10:25:10', 'mail.jrjconnect.com', 'nithin_out', 'jhdfg', 993, 'ssl', 'INBOX', 2);

-- --------------------------------------------------------

--
-- Table structure for table `payment_categories`
--

DROP TABLE IF EXISTS `payment_categories`;
CREATE TABLE IF NOT EXISTS `payment_categories` (
  `id` int NOT NULL AUTO_INCREMENT,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Expense' COMMENT 'Income,Expense',
  `category` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=42 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_categories`
--

INSERT INTO `payment_categories` (`id`, `type`, `category`, `created_at`, `updated_at`) VALUES
(1, 'Expense', 'Recruitment', '2025-11-11 13:54:17', NULL),
(2, 'Expense', 'Training', '2025-11-11 13:54:17', NULL),
(3, 'Expense', 'Paperwork', '2025-11-11 13:54:17', NULL),
(4, 'Expense', 'Visa', '2025-11-11 13:54:17', NULL),
(5, 'Expense', 'Medical', '2025-11-11 13:54:17', NULL),
(6, 'Expense', 'Transport', '2025-11-11 13:54:17', NULL),
(7, 'Expense', 'Candidate Payment', '2025-11-11 13:54:17', NULL),
(8, 'Expense', 'Sim Card', '2025-11-11 13:55:37', NULL),
(9, 'Expense', 'Traffic fine', '2025-11-11 13:55:37', NULL),
(10, 'Expense', 'Sim Expire Fine', '2025-11-11 13:55:37', NULL),
(11, 'Expense', 'Car Repair Cost', '2025-11-11 13:55:37', NULL),
(12, 'Expense', 'Testc', '2025-11-11 10:07:54', NULL),
(13, 'Expense', 'Other', '2025-11-11 10:17:31', NULL),
(14, 'Income', 'newcat45', '2025-11-12 15:07:50', '2025-11-15 19:11:42'),
(15, 'Income', 'newcat1', '2025-11-12 15:10:26', NULL),
(16, 'Income', 'newcat2', '2025-11-12 15:15:04', NULL),
(17, 'Income', 'newcat3', '2025-11-12 15:19:50', NULL),
(18, 'Income', 'newcat4', '2025-11-12 15:20:26', NULL),
(19, 'Income', 'newcat5', '2025-11-12 15:21:56', NULL),
(20, 'Income', 'General', '2025-11-12 10:26:56', NULL),
(21, 'Income', 'Test5', '2025-11-13 10:08:03', NULL),
(23, 'Expense', 'newCat65', '2025-11-14 11:02:37', '2025-11-15 19:12:13'),
(24, 'Income', 'Cat35', '2025-11-14 20:22:39', NULL),
(25, 'Income', 'Newi1', '2025-11-15 12:47:42', NULL),
(26, 'Expense', 'tes445', '2025-11-15 14:11:40', NULL),
(27, 'Income', 'icat36', '2025-11-15 14:12:19', NULL),
(28, 'Income', 'sss', '2025-11-15 14:15:30', NULL),
(29, 'Income', 'icat3', '2025-11-15 14:17:46', NULL),
(30, 'Income', 'icat4', '2025-11-15 14:19:03', NULL),
(31, 'Income', 'icat5', '2025-11-15 14:20:59', NULL),
(32, 'Income', 'icat6', '2025-11-15 14:26:05', NULL),
(33, 'Income', 'icat7', '2025-11-15 14:26:23', NULL),
(34, 'Income', 'icat8', '2025-11-15 14:27:31', NULL),
(35, 'Income', 'NewIcat364', '2025-11-18 10:16:26', NULL),
(36, 'Expense', 'Advance Salary Deduction', '2025-11-27 11:58:24', NULL),
(37, 'Expense', 'Performance Bonus', '2025-11-27 12:00:27', NULL),
(38, 'Expense', 'Uniform Deduction', '2025-11-27 12:43:46', NULL),
(39, 'Expense', 'Fines / Penalties', '2025-11-27 13:07:01', NULL),
(40, 'Expense', 'Medical Expense', '2025-11-27 13:13:02', NULL),
(41, 'Expense', 'Accommodation Expense', '2025-11-28 13:45:43', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `people`
--

DROP TABLE IF EXISTS `people`;
CREATE TABLE IF NOT EXISTS `people` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phone` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'staff',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `login_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'generated in cron_send_reminders daily',
  PRIMARY KEY (`id`),
  KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `people`
--

INSERT INTO `people` (`id`, `name`, `email`, `phone`, `role`, `created_at`, `password_hash`, `login_token`) VALUES
(1, 'Dileep', 'dileep@jrjconnect.com', '', 'staff', '2025-10-17 16:06:35', '$2y$12$qn443sAnDD3rsXWyjx1y4.IscsRMWHmuO9jBxezOInlc9zkEhljMm', NULL),
(2, 'Nithin1', 'nithin@jrjconnect.com', '', 'staff', '2025-10-17 16:07:04', '$2y$12$Jhfc8VmICnD3YncgyflMbeRZ38Y.rXYvxkY/ep38u6bXGCdoBXjFS', 'Y3vjDx1INbyFJ8Fuhsr321DBvdkJJY1TLvZycoRCpHejx5rxdHQsIg1oBYzO');

-- --------------------------------------------------------

--
-- Table structure for table `people_logs`
--

DROP TABLE IF EXISTS `people_logs`;
CREATE TABLE IF NOT EXISTS `people_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` int NOT NULL,
  `admin` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'auto login admin session array',
  `customer_id` int DEFAULT NULL,
  `recruiter_id` int DEFAULT NULL,
  `employee_id` int DEFAULT NULL,
  `supplier_id` int DEFAULT NULL,
  `contact_id` int DEFAULT NULL,
  `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'general' COMMENT 'general/timeline',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `people_logs`
--

INSERT INTO `people_logs` (`id`, `agent_id`, `admin`, `customer_id`, `recruiter_id`, `employee_id`, `supplier_id`, `contact_id`, `log`, `type`, `ip`, `timestamp`) VALUES
(1, 2, NULL, 1, NULL, NULL, NULL, NULL, 'New booking with summary sdfsd [#1] is added ', 'general', '::1', '2026-01-08 11:05:52'),
(2, 2, NULL, NULL, NULL, NULL, NULL, 1, 'New booking with summary test [#2] is added ', 'general', '::1', '2026-01-08 11:13:17'),
(3, 2, NULL, NULL, NULL, NULL, 2, NULL, 'New booking with summary test [#3] is added ', 'general', '::1', '2026-01-08 11:15:57');

-- --------------------------------------------------------

--
-- Table structure for table `scenarios`
--

DROP TABLE IF EXISTS `scenarios`;
CREATE TABLE IF NOT EXISTS `scenarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `slug` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `inter_type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `scenarios`
--

INSERT INTO `scenarios` (`id`, `name`, `slug`, `inter_type`, `created_at`) VALUES
(1, 'Flights', 'flights', 'customer', '2025-12-08 14:58:34'),
(2, 'Tours', 'tours', 'customer', '2025-12-08 14:58:34'),
(3, 'Flights', 'supplier-flights', 'supplier', '2025-12-08 14:58:34'),
(4, 'Tours', 'supplier-tours', 'supplier', '2025-12-09 08:59:16');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
CREATE TABLE IF NOT EXISTS `settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `value` varchar(255) NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `value`, `updated_at`) VALUES
(1, 'daily_followup_target', '20', '2025-10-28 12:46:25'),
(2, 'Active', '5', '2025-10-31 07:29:45'),
(3, 'Inactive', '5', '2025-10-31 06:24:28'),
(4, 'Work in Progress', '5', '2025-10-31 06:24:56'),
(5, 'Prospect', '5', '2025-10-31 06:24:56'),
(6, 'Suspect', '5', '2025-10-31 06:25:21'),
(7, 'Dead', '5', '2025-10-31 06:25:21');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

DROP TABLE IF EXISTS `suppliers`;
CREATE TABLE IF NOT EXISTS `suppliers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'all phone numbers',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `whatsapp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `industry` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `address` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `city` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `state` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `country` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(250) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `services` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `google_rating` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `favourite` int NOT NULL DEFAULT '0',
  `archived` int NOT NULL DEFAULT '0',
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Suspect' COMMENT 'Suspect — you have a name, not verified yet.\r\nLead (Active) — you’ve reached out / they responded.\r\nOpportunity — quote/proposal/demo sent; decision pending.\r\nWon — became a customer.\r\nArchive — not a fit or chose someone else (capture reason).',
  `photo` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `timezone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'from excel' COMMENT 'from ',
  `enable_email` int NOT NULL DEFAULT '0' COMMENT 'enable feature',
  `enable_whatsapp` int NOT NULL DEFAULT '0' COMMENT 'enable feature',
  `fil_domains` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter domains comma seperated',
  `fil_emails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter emails comma seperated',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`id`, `agent_id`, `name`, `company`, `phones`, `phone`, `whatsapp`, `email`, `industry`, `address`, `city`, `state`, `country`, `website`, `services`, `google_rating`, `favourite`, `archived`, `type`, `photo`, `photo1`, `timezone`, `source`, `enable_email`, `enable_whatsapp`, `fil_domains`, `fil_emails`, `created_at`, `updated_at`) VALUES
(1, '2', 'Supplier 1', 'Company 1', '[\"9876453663\"]', '9876453663', NULL, 'supplier1@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Work in progress', 'vaOu2EqIY0s7y8aLJHr4FYsZXQNtn3ZddS7gzSvM251104114045.jpg', NULL, NULL, 'from excel', 0, 0, NULL, 'supplier1@gmail.com', '2025-11-04 17:10:46', NULL),
(2, '2', 'Supplier 2', 'Company 2', '[\"7834566345\"]', '7834566345', NULL, 'supplier2xx@gmail.com', 'Pipes and Fittings ', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Active', 'tqnvdBWlmcQvsg7T4a93AUUHb4bIc3p57Rubw0dk251106062146.jpg', 'O7yYpk1m9DndwEAsEGbtmLuXhjnGRoordt4FWMOB251107092105.jpg', NULL, 'from excel', 0, 0, NULL, 'supplier2xx@gmail.com', '2025-11-04 17:16:13', NULL),
(3, '2', 'Supplier 3', 'Company 3', '[\"7636726354\"]', '7636726354', NULL, 'supplier3@gmail.com', 'New Industry', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Suspect', 'josnNCMJV79TFjrWIfOndoqndLAH0FIDaEssldm6251105080622.jpg', NULL, NULL, 'from excel', 1, 1, NULL, 'supplier3@gmail.com', '2025-11-05 13:36:23', NULL),
(4, '2', 'SRahul', 'ABS Company', '[]', '', NULL, '', 'Industry477', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Active', NULL, NULL, NULL, 'from excel', 0, 1, NULL, '', '2025-11-18 13:14:27', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_contacts`
--

DROP TABLE IF EXISTS `suppliers_contacts`;
CREATE TABLE IF NOT EXISTS `suppliers_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo1` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `photo2` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `designation` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`supplier_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers_contacts`
--

INSERT INTO `suppliers_contacts` (`id`, `supplier_id`, `name`, `phone`, `email`, `photo1`, `photo2`, `designation`, `created_by`, `created_at`) VALUES
(1, 2, 'Joice1', '2364562364', 'joice@gmail.com', NULL, NULL, 'Manager', 'Nithin', '2025-11-05 10:20:28'),
(2, 2, 'Nithin', '32784788488', 'nithin@gmail.com', '1762538590_bc-16.jpg', '1762538647_bc-11.jpg', 'Head', 'Nithin', '2025-11-05 10:21:14'),
(3, 2, 'tectc', '2364632', 'tetsh@jsdfhgf.fdg', NULL, NULL, 'Des', 'Nithin', '2025-11-07 13:00:44'),
(4, 4, 'dfgdfg', '', '', '1765371909_bc-15.jpg', NULL, 'sdfgdg', 'Nithin1', '2025-12-10 13:05:09');

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_documents`
--

DROP TABLE IF EXISTS `suppliers_documents`;
CREATE TABLE IF NOT EXISTS `suppliers_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `label` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_type` enum('pdf','image') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`supplier_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_invoices`
--

DROP TABLE IF EXISTS `suppliers_invoices`;
CREATE TABLE IF NOT EXISTS `suppliers_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `invoice_date` date NOT NULL,
  `due_date` date DEFAULT NULL,
  `invoice_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `type` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `category` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '',
  `payment_status` enum('Paid','Unpaid','Partial Paid') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Unpaid',
  `document` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_logs`
--

DROP TABLE IF EXISTS `suppliers_logs`;
CREATE TABLE IF NOT EXISTS `suppliers_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `agent_id` int DEFAULT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `type` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call,Email,Meeting,General',
  `visibility` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Public' COMMENT 'Private,Public',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_payments`
--

DROP TABLE IF EXISTS `suppliers_payments`;
CREATE TABLE IF NOT EXISTS `suppliers_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'Income/Expense',
  `category` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `invoice_date` date NOT NULL,
  `payment_status` enum('Paid','Unpaid','Partial Paid','Pending') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'Unpaid',
  `invoice_amount` decimal(10,2) NOT NULL DEFAULT '0.00',
  `invoice_partial` decimal(10,2) NOT NULL DEFAULT '0.00',
  `invoice_payment_method` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reclaim_by` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Company,Employee',
  `reimbursable` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL COMMENT 'Yes, No',
  `card_last4` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cheque_bank` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `cheque_issuer` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reimbursement_amount` decimal(10,2) DEFAULT NULL,
  `document` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'file names json',
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_quotations`
--

DROP TABLE IF EXISTS `suppliers_quotations`;
CREATE TABLE IF NOT EXISTS `suppliers_quotations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `ref_no` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `version` int DEFAULT '1',
  `quotation_name` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quotation_date` date DEFAULT NULL,
  `attention` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `jobs_json` json DEFAULT NULL,
  `terms_json` json DEFAULT NULL,
  `closing` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `status` enum('draft','final') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers_reminders`
--

DROP TABLE IF EXISTS `suppliers_reminders`;
CREATE TABLE IF NOT EXISTS `suppliers_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `supplier_id` int NOT NULL,
  `reminder_at` datetime NOT NULL,
  `type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call, Email, Meeting, General',
  `contact_id` int DEFAULT NULL,
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `completed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `system_kv`
--

DROP TABLE IF EXISTS `system_kv`;
CREATE TABLE IF NOT EXISTS `system_kv` (
  `k` varchar(191) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `v` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`k`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_kv`
--

INSERT INTO `system_kv` (`k`, `v`, `updated_at`) VALUES
('last_inbound_email_at', '2025-10-16 12:55:19', '2025-10-16 12:55:19');

-- --------------------------------------------------------

--
-- Table structure for table `templates`
--

DROP TABLE IF EXISTS `templates`;
CREATE TABLE IF NOT EXISTS `templates` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `category` enum('employee','customer','other') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'employee',
  `subtype` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `templates`
--

INSERT INTO `templates` (`id`, `category`, `subtype`, `slug`, `description`, `created_at`, `updated_at`) VALUES
(1, 'employee', 'Offer Letter', 'offer-letter', 'offer letter description', '2025-12-09 05:41:46', '2025-12-11 05:32:39'),
(2, 'employee', 'Appointment Letter', 'appointment-letter', 'This is appointment letter template', '2025-12-09 05:56:41', '2025-12-09 09:10:26'),
(3, 'customer', 'Company Profile', 'company-profile', 'This is Company Profile template', '2025-12-09 05:57:33', '2025-12-09 05:57:33'),
(4, 'employee', 'Leave Request', 'leave-request', 'Leave Request Form', '2025-12-09 06:10:34', '2025-12-11 10:09:02'),
(5, 'employee', 'Resignation Letter', 'resignation', 'just description', '2025-12-09 07:15:18', '2025-12-09 07:15:18'),
(6, 'employee', 'Warning Letter', 'warning-letter', 'Warning letter template', '2025-12-09 07:41:27', '2025-12-11 09:35:32');

-- --------------------------------------------------------

--
-- Table structure for table `template_versions`
--

DROP TABLE IF EXISTS `template_versions`;
CREATE TABLE IF NOT EXISTS `template_versions` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` int UNSIGNED NOT NULL,
  `version` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `content` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `notes` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '0',
  `show_header` int NOT NULL DEFAULT '0',
  `created_by` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`)
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `template_versions`
--

INSERT INTO `template_versions` (`id`, `template_id`, `version`, `content`, `notes`, `is_active`, `show_header`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 1, 'v1.0', '\r\nHi {{name}}\r\nThis is offer letter template\r\n\r\ndate : {{date}}', '', 0, 0, NULL, '2025-12-09 05:43:29', '2025-12-09 18:36:40'),
(2, 1, 'v2.0', '\r\nHi {{name}}\r\nThis is offer letter template version 2\r\n\r\ndate : {{date}}', 'version 2', 0, 0, NULL, '2025-12-09 05:47:39', NULL),
(3, 1, 'v3.0', '\r\nHi {{name}}\r\nThis is offer letter template\r\n\r\ndate : {{date}}', '', 0, 0, NULL, '2025-12-09 07:19:09', '2025-12-11 10:56:44'),
(4, 1, 'v4.0', '\r\nHi {{name}}\r\nThis is offer letter template\r\n\r\n{{newdata}}\r\n\r\n{{phone}}\r\ndate : {{date}}', '', 0, 1, NULL, '2025-12-09 08:47:35', '2025-12-11 11:10:38'),
(5, 1, 'v4.0-copy', '\r\nHi {{name}}\r\nThis is offer letter template v4\r\n\r\n{{newdata}}\r\n{{test}}\r\n\r\n{{phone}}\r\ndate : {{date}}', '', 0, 0, NULL, '2025-12-11 05:26:59', '2025-12-11 16:12:57'),
(6, 5, 'v1.0', 'To  \r\nThe Manager  \r\nALNASR GENERAL SERVICE EST  \r\nABU DHABI – UAE\r\n\r\nDate: {{date}}\r\n\r\nSub:     Request for resignation\r\n\r\nDear Sir,\r\n\r\nWith due respect, I, {{name}} (Emirates ID No: {{emirates_id}}), have been working in your esteemed organization as a {{position}} for {{experience}} years. My visa is expiring on {{visa_expiry_date}}. I regret to inform you that I hereby tender my resignation effective from {{resignation_effective_date}} due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.\r\n\r\nI humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully,  \r\n\r\n\r\n\r\n{{name}}  \r\nMob: {{phone}}\r\n', 'version1', 0, 0, NULL, '2025-12-11 08:52:33', '2025-12-11 14:45:44'),
(7, 5, 'v2.0', '<!doctype html>\r\n<html lang=\"en\">\r\n<head>\r\n  <meta charset=\"utf-8\" />\r\n  <title>Resignation Letter</title>\r\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />\r\n  <style>\r\n    /* A4 page sizing for print */\r\n    @page { size: A4; margin: 28mm 20mm; }\r\n    html, body {\r\n      height: 100%;\r\n      margin: 0;\r\n      padding: 0;\r\n      font-family: \"DejaVu Sans\", \"Arial\", \"Helvetica\", sans-serif;\r\n      color: #111;\r\n      -webkit-font-smoothing: antialiased;\r\n      -moz-osx-font-smoothing: grayscale;\r\n    }\r\n\r\n    /* Container that keeps content inside printable area */\r\n    .page {\r\n      box-sizing: border-box;\r\n      width: 210mm;\r\n      min-height: 297mm;\r\n      padding: 20mm;\r\n      margin: 0 auto;\r\n      background: white;\r\n    }\r\n\r\n    .to-block {\r\n      margin-bottom: 12px;\r\n      line-height: 1.35;\r\n    }\r\n\r\n    .date-right {\r\n      float: right;\r\n      text-align: right;\r\n      font-weight: 600;\r\n    }\r\n\r\n    .subject {\r\n      margin-top: 18px;\r\n      margin-bottom: 18px;\r\n      letter-spacing: 0.2px;\r\n      font-weight: 600;\r\n    }\r\n\r\n    p {\r\n      margin: 0 0 12px 0;\r\n      line-height: 1.55;\r\n      text-align: justify;\r\n    }\r\n\r\n    .signature-block {\r\n      margin-top: 34px;\r\n    }\r\n\r\n    .signature-line {\r\n      margin-top: 48px;\r\n      height: 2.2cm; /* space for handwritten signature */\r\n    }\r\n\r\n    .name {\r\n      margin-top: 8px;\r\n      font-weight: 700;\r\n    }\r\n\r\n    .phone {\r\n      margin-top: 2px;\r\n    }\r\n\r\n    /* ensure the floated date doesn\'t collide on small screens */\r\n    @media print, screen and (min-width: 480px) {\r\n      .date-right { float: right; }\r\n    }\r\n  </style>\r\n</head>\r\n<body>\r\n  <div class=\"page\">\r\n    <div style=\"overflow: hidden;\">\r\n      <!-- Left side \"To\" block -->\r\n      <div class=\"to-block\" style=\"width:60%; float:left;\">\r\n        <div>To</div>\r\n        <div><strong>The Manager</strong></div>\r\n        <div><strong>ALNASR GENERAL SERVICE EST</strong></div>\r\n        <div><strong>ABU DHABI – UAE</strong></div>\r\n      </div>\r\n\r\n      <!-- Right side date -->\r\n      <div class=\"date-right\">\r\n        Date: <span>{{date}}</span>\r\n      </div>\r\n    </div>\r\n\r\n    <div style=\"clear: both;\"></div>\r\n\r\n    <div class=\"subject\">\r\n      Sub: &nbsp;&nbsp;&nbsp;&nbsp; Request for resignation\r\n    </div>\r\n\r\n    <p>Dear Sir,</p>\r\n\r\n    <p>\r\n      With due respect, I, <strong>{{name}}</strong> (Emirates ID No: <strong>{{emirates_id}}</strong>), have been working in your esteemed organization as a <strong>{{position}}</strong> for <strong>{{experience}}</strong> years. My visa is expiring on <strong>{{visa_expiry_date}}</strong>. I regret to inform you that I hereby tender my resignation effective from <strong>{{resignation_effective_date}}</strong> due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.\r\n    </p>\r\n\r\n    <p>\r\n      I humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n    </p>\r\n\r\n    <p>Thanking you,</p>\r\n\r\n    <p>Yours faithfully,</p>\r\n\r\n    <div class=\"signature-block\">\r\n      <div class=\"signature-line\"></div>\r\n      <div class=\"name\">{{name}}</div>\r\n      <div class=\"phone\">Mob: {{phone}}</div>\r\n    </div>\r\n  </div>\r\n</body>\r\n</html>\r\n', '', 0, 0, NULL, '2025-12-11 08:56:59', NULL),
(8, 5, 'v3.0', 'To\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\nDate: {{date}}\r\n\r\nSub: Request for resignation\r\n\r\nDear Sir,\r\n\r\nWith due respect, I, {{name}} (Emirates ID No: {{emirates_id}}), have been working in your esteemed organization as a {{position}} for {{experience}} years. My visa is expiring on {{visa_expiry_date}}. I regret to inform you that I hereby tender my resignation effective from {{resignation_effective_date}} due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.\r\n\r\nI humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully,\r\n\r\n{{name}}\r\nMob: {{phone}}', '', 0, 0, NULL, '2025-12-11 08:58:59', '2025-12-11 14:33:19'),
(9, 5, 'v4', 'To\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\n                                                                                 Date: {{date}}\r\n\r\nSub:        <strong>Request for resignation</strong>\r\n\r\nDear Sir,\r\n\r\n        With due respect, <strong>{{name}}</strong>  (<strong>EMIRATES ID NO: {{emirates_id}}</strong>) have been working in your esteemed organization as a <strong>{{position}}</strong> for {{experience}} years. <strong>My visa is expired to the \r\ndate of {{visa_expiry_date}}</strong>. I regret to inform you that I hereby tender my resignation \r\n(<strong>{{resignation_effective_date}}</strong>) due to some serious family problem. So, please consider \r\nthis letter as my prior notice for the same and send me back after settle my final accounts.\r\n\r\nI humbly request you to do the needful in this regard and I shall be grateful to you \r\nfor the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully\r\n\r\n\r\n\r\n<strong>{{name}}</strong>\r\nMob: {{phone}}\r\n', '', 0, 0, NULL, '2025-12-11 09:04:38', '2025-12-11 14:53:59'),
(10, 5, 'v5', '<div style=\"display:flex; justify-content:space-between; width:100%;\">\r\n  <div>To</div>\r\n  <div>Date: {{date}}</div>\r\n</div>\r\n\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\nSub:        <strong>Request for resignation</strong>\r\n\r\nDear Sir,\r\n\r\n        With due respect, <strong>{{name}}</strong> (<strong>EMIRATES ID NO: {{emirates_id}}</strong>) have been working in your esteemed \r\norganization as a <strong>{{position}}</strong> for {{experience}} years. <strong>My visa is expired to the \r\ndate of {{visa_expiry_date}}</strong>. I regret to inform you that I hereby tender my resignation \r\n(<strong>{{resignation_effective_date}}</strong>) due to some serious family problem. So, please consider \r\nthis letter as my prior notice for the same and send me back after settle my final accounts.\r\n\r\nI humbly request you to do the needful in this regard and I shall be grateful to you \r\nfor the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully\r\n\r\n\r\n<strong>{{name}}</strong>\r\nMob: {{phone}}\r\n', '', 0, 0, NULL, '2025-12-11 09:13:42', '2025-12-11 14:45:04'),
(11, 5, 'v6', '\r\n\r\nTo\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\nDate: {{date}}\r\n\r\nSub: <strong>Request for resignation</strong>\r\n\r\nDear Sir,\r\n\r\n<div style=\"text-align: justify;\">With due respect, I, <strong>{{name}}</strong> (Emirates ID No: <strong>{{emirates_id}}</strong>), have been working in your esteemed organization as a <strong>{{position}}</strong> for <strong>{{experience}}</strong> years. My visa is expiring on <strong>{{visa_expiry_date}}</strong>. I regret to inform you that I hereby tender my resignation effective from <strong>{{resignation_effective_date}}</strong> due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.</div>\r\n\r\nI humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully,\r\n\r\n\r\n\r\n\r\n<strong>{{name}}</strong>\r\nMob: {{phone}}\r\n', '', 1, 0, NULL, '2025-12-11 09:21:23', '2025-12-11 14:55:12'),
(12, 6, 'v1.0', 'Ref No: {{ref_no}}\r\nDate: {{date}}\r\n\r\nTo,\r\n\r\nMr. {{employee_name}}\r\nEmp. ID: {{employee_id}}\r\nPassport No: {{passport_no}}\r\nNationality: {{nationality}}\r\n\r\nSubject: - <strong>Warning Letter For Absenteeism</strong>\r\n\r\nAttention: <strong>{{employee_name}}</strong>\r\n\r\nI am writing to bring to your attention that you have been absent from the workplace\r\nevery week. Please understand that this absence directly impacts the productivity of our\r\norganization and we are receiving complaints from the clients that it affects their\r\nregular site work as well.\r\n\r\nThis is the official warning letter from the management regarding your absenteeism at\r\nwork, as we have received complaint from your site manager.\r\nWe hereby warn you not to repeat such incidence in future, or else the company will\r\ntake necessary action further. The absenteeism will result of\r\n<strong>AED 20/- deduction per day</strong> which will be affected in your monthly pay.\r\n\r\nWe kindly request that you contact us by the next working day upon receiving this letter\r\nand give us an explanation on this regard.\r\n\r\nThanks & Regards,\r\n\r\n<strong>{{manager_name}}</strong>  \r\nOperation Manager  \r\nM/s. Alnasr General Services Est.\r\n', '', 0, 0, NULL, '2025-12-11 09:35:50', '2025-12-11 15:17:26'),
(13, 6, 'v2.0', '\r\nRef No: <strong>{{ref_no}}</strong>\r\nDate: <strong>{{date}}</strong>\r\n\r\nTo,\r\nMr. <strong>{{name}}</strong>\r\nEmp. ID: <strong>{{emp_id}}</strong>\r\nPassport No: <strong>{{passport_number}}</strong>\r\nNationality: <strong>{{country}}</strong>\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Warning Letter For Absenteeism</u></strong>\r\n</div>\r\nAttention: <strong>{{name}}</strong>\r\n<div style=\"display:block; text-align:justify;\">\r\nI am writing to bring to your attention that you have been absent from the workplace every week. Please understand that this absence directly impacts the productivity of our organization and we are receiving complaints from the clients that it affects their regular site work as well.</div><div style=\"display:block; text-align:justify;\">\r\nThis is the official warning letter from the management regarding your absenteeism at work, as we have received complaint from your site manager. We hereby warn you not to repeat such incidence in future, or else the company will take necessary action further. The absenteeism will result of <strong>AED {{deduction_per_day}}/- deduction per day</strong> which will be affected in your monthly pay. </div><div style=\"display:block; text-align:justify;\">\r\nWe kindly request that you contact us by the next working day upon receiving this letter and give us an explanation on this regard.\r\n</div>\r\n\r\nThanks & Regards,\r\n\r\n\r\n\r\n\r\n{{manager_name}}\r\nOperation Manager\r\nM/s. Alnasr General Services Est.\r\n', '', 1, 0, NULL, '2025-12-11 09:40:42', '2025-12-11 15:30:39'),
(14, 4, 'v1.0', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>{{name}}</strong>                     BADGE#: <strong>{{badge_no}}</strong>\r\nCATEGORY: <strong>{{category}}</strong>            NATIONALITY: <strong>{{nationality}}</strong>\r\nLOCATION: <strong>{{location}}</strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n\r\nSubject:\r\n<strong><u>Request For Annual / Sick / Emergency Leave</u></strong>\r\n\r\nDear Sir,\r\n\r\nPlease grant me Annual/Sick/Emergency Leave for <strong>{{leave_days}}</strong> Days maximum with effect from <strong>{{leave_start_date}}</strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong>{{last_day_of_work}}</strong>\r\nReturn Date: <strong>{{return_date}}</strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong>{{home_address_line1}}</strong>\r\n<strong>{{home_address_line2}}</strong>\r\n\r\nTelephone No. in Home Country: <strong>{{home_phone}}</strong>\r\n\r\nOffice Use Only\r\nLast Leave Date: {{last_leave_date}}    From: {{leave_from}}    To: {{leave_to}}    Total Days: {{total_days}}\r\n\r\nWP Card Exp: <strong>{{wp_expiry}}</strong>\r\nVisa Exp: <strong>{{visa_expiry}}</strong>\r\n\r\nSignature of Applicant:\r\n<strong>{{applicant_signature_name}}</strong>\r\n\r\nApproved:\r\nGeneral Manager\r\n\r\n', 'Leave Request Form', 0, 0, NULL, '2025-12-11 10:10:32', '2025-12-11 15:43:22'),
(15, 4, 'v2.0', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>{{name}}</strong>                     BADGE#: <strong>{{emp_id}}</strong>\r\nCATEGORY: <strong>{{category}}</strong>            NATIONALITY: <strong>{{country}}</strong>\r\nLOCATION: <strong>{{location}}</strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Request For Annual / Sick / Emergency Leave</u></strong>\r\n</div>\r\nDear Sir,\r\nPlease grant me Annual/Sick/Emergency Leave for <strong>{{leave_days}}</strong> Days maximum with effect from <strong>{{leave_start_date}}</strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong>{{last_day_of_work}}</strong>\r\nReturn Date: <strong>{{return_date}}</strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong>{{home_address_line1}}</strong>\r\n<strong>{{home_address_line2}}</strong>\r\n\r\nTelephone No. in Home Country: <strong>{{home_phone}}</strong>\r\n\r\nOffice Use Only\r\n\r\nLast Leave Date: {{last_leave_date}} | From: {{leave_from}} | To: {{leave_to}} | Total Days: {{total_days}}\r\n\r\nWP Card Exp: <strong>{{wp_expiry}}</strong>\r\nVisa Exp: <strong>{{visa_expiry}}</strong>\r\n\r\n<table style=\"width:100%; border-collapse:collapse;\"><tr><td style=\"vertical-align:top; width:50%;\">Signature of Applicant:<br><br>\r\n\r\n\r\n<strong>{{name}}</strong></td><td style=\"vertical-align:top; text-align:right; width:50%;\">Approved:<br><br>\r\n\r\n\r\nGeneral Manager</td></tr></table>\r\n\r\n', '', 1, 0, NULL, '2025-12-11 10:12:51', '2025-12-11 16:08:25'),
(16, 1, 'v5.0', 'Ref No: <strong>{{ref_no}}</strong>\r\nDate: <strong>{{date}}</strong>\r\n\r\nTo,\r\n\r\nMr. <strong>{{employee_name}}</strong>\r\nPassport No: <strong>{{passport_no}}</strong>\r\nMob: <strong>{{mobile}}</strong>\r\n<div style=\"margin-top:10px;\">\r\n  <strong>Subject: Offer Letter</strong>\r\n</div>\r\nDear Mr. <strong>{{employee_name}}</strong>,\r\n<div style=\"display:block; text-align:justify;\">Further to the evaluation of your CV, we are pleased to offer you the following position in our organization on the below mentioned terms & conditions:</div>\r\n<table style=\"width:100%; border-collapse:collapse; margin-top:10px;\"><tr><td style=\"width:40%;\">Job Title</td><td><strong>{{job_title}}</strong></td></tr><tr><td>Basic Salary</td><td>AED: {{basic_salary}}/-</td></tr><tr><td>Additional Allowances</td><td>AED: {{additional_allowances}}/-</td></tr><tr><td>Total Offered Salary</td><td><strong>AED: {{total_salary}}/-</strong></td></tr><tr><td>Location</td><td>{{location}}</td></tr><tr><td>Probation Period</td><td>{{probation_period}}</td></tr><tr><td>Contract Duration</td><td>{{contract_duration}}</td></tr><tr><td>Working Hours</td><td>{{working_hours}}</td></tr><tr><td>Leave Salary</td><td>{{leave_salary}}</td></tr><tr><td>End of Service Benefit</td><td>{{eosb}}</td></tr><tr><td>Medical</td><td>{{medical}}</td></tr></table>\r\n<div style=\"display:block; text-align:justify;\">All other terms and conditions of services shall be in accordance with UAE Labour Law.\r\nThe individual contracts will be signed after obtaining the employment visas only.</div>\r\n<div style=\"display:block; text-align:justify;\">Please also note that, your qualification and experience is considered as true and fair as stated in your CV. Any untrue or misstatement in the CV will be considered as breach of basic criteria upon which your selection is made.</div>\r\n<div style=\"display:block; text-align:justify;\">In case of resignation by the employee during the initial 2-year period, the employee shall bear all the training charges.</div>\r\n<div style=\"display:block; text-align:justify;\">If you agree with the above terms and conditions, please sign in the space provided and send us back immediately.</div>\r\n\r\nBest Regards,\r\n<strong>{{employer_representative}}</strong><br>\r\nSeal and signature of the Employer\r\n\r\n<table style=\"width:100%; border-collapse:collapse; margin-top:20px;\"><tr><td style=\"width:50%;\"></td><td style=\"width:50%; text-align:right;\">Name and Signature of the Employee<br><br> I accept above terms and conditions<br>Date: ______________________</td></tr></table>\r\n', 'Offer Letter', 0, 0, NULL, '2025-12-11 10:42:53', '2025-12-11 16:27:47'),
(17, 1, 'v6.0', '\r\nRef No: <strong>{{ref_no}}</strong>\r\nDate: <strong>{{date}}</strong>\r\n\r\nTo,\r\nMr. <strong>{{name}}</strong>\r\nPassport No: <strong>{{passport_number}}</strong>\r\nMob: <strong>{{mobile}}</strong>\r\n<div style=\"margin-top:10px;\"><strong>Subject: Offer Letter</strong></div>\r\nDear Mr. <strong>{{name}}</strong>,\r\n<div style=\"display:block; text-align:justify;\">Further to the evaluation of your CV, we are pleased to offer you the following position in our organization on the below mentioned terms & conditions:</div>\r\n<table style=\"width:100%; border-collapse:collapse; margin-top:10px;\"><tr><td style=\"width:40%;\">Job Title</td><td><strong>{{Job_title}}</strong></td></tr><tr><td>Basic Salary</td><td>AED: {{basic_salary}}/-</td></tr><tr><td>Additional Allowances</td><td>AED: {{additional_allowances}}/-</td></tr><tr><td>Total Offered Salary</td><td><strong>AED: {{total_salary}}/-</strong></td></tr><tr><td>Location</td><td>{{location}}</td></tr><tr><td>Probation Period</td><td>{{probation_period}}</td></tr><tr><td>Contract Duration</td><td>{{contract_duration}}</td></tr><tr><td>Working Hours</td><td>{{working_hours}}</td></tr><tr><td>Leave Salary</td><td>{{leave_salary}}</td></tr><tr><td>End of Service Benefit</td><td>{{eosb}}</td></tr><tr><td>Medical</td><td>{{medical}}</td></tr></table>\r\n<div style=\"display:block; text-align:justify;\">All other terms and conditions of services shall be in accordance with UAE Labour Law.\r\nThe individual contracts will be signed after obtaining the employment visas only.</div><div style=\"display:block; text-align:justify;\">Please also note that, your qualification and experience is considered as true and fair as stated in your CV. Any untrue or misstatement in the CV will be considered as breach of basic criteria upon which your selection is made.</div><div style=\"display:block; text-align:justify;\">In case of resignation by the employee during the initial 2-year period, the employee shall bear all the training charges.</div><div style=\"display:block; text-align:justify;\">If you agree with the above terms and conditions, please sign in the space provided and send us back immediately.</div>\r\nBest Regards,\r\n\r\n\r\n\r\n<table style=\"width:100%; border-collapse:collapse; margin-top:20px;\"><tr><!-- LEFT: Employer Section --><td style=\"width:50%; vertical-align:top;\"><strong>{{employer_representative}}</strong><br>Seal and signature of the Employer</td><!-- RIGHT: Employee Section --><td style=\"width:50%; text-align:right; vertical-align:top;\">Name and Signature of the Employee<br><br>I accept above terms and conditions<br>Date: ______________________</td></tr></table>\r\n\r\n', 'Offer Letter', 1, 0, NULL, '2025-12-11 10:54:38', '2025-12-11 16:34:09');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','manager','staff') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'admin',
  `login_token` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `login_at` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password_hash`, `role`, `login_token`, `login_at`, `created_at`) VALUES
(1, 'Admin', 'crmleads@jrjapp.com', '$2y$10$p44vxeYhUDJ3MtMCOFy/DuhsctK8QhNW56eykICtffeq0ApdQBZvC', 'admin', NULL, '2025-12-11 05:25:07', '2025-09-02 15:04:35');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_contacts`
--

DROP TABLE IF EXISTS `whatsapp_contacts`;
CREATE TABLE IF NOT EXISTS `whatsapp_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `company` varchar(100) NOT NULL,
  `contact_name` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `assigned` varchar(11) NOT NULL DEFAULT 'No',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `whatsapp_contacts`
--

INSERT INTO `whatsapp_contacts` (`id`, `company`, `contact_name`, `phone`, `assigned`, `date_added`) VALUES
(1, 'Nithin', 'Nithin', '919188071759', 'Yes', '2025-12-04 09:05:16'),
(2, 'JO', 'JO', '971559103109', 'No', '2025-12-04 09:26:36'),
(3, '??????? - ?????? ??????', '??????? - ?????? ??????', '971569647484', 'No', '2025-12-04 09:52:45'),
(4, 'Hammad Javed', 'Hammad Javed', '971508163215', 'No', '2025-12-05 10:51:56'),
(5, 'Kelum prasad', 'Kelum prasad', '971545680565', 'No', '2025-12-10 16:39:10'),
(6, 'Dileep Joseph', 'Dileep Joseph', '447790998833', 'No', '2025-12-14 11:45:27'),
(7, 'khadkachandra484', 'khadkachandra484', '9779863154188', 'No', '2025-12-15 11:27:48'),
(8, 'George Vettikuzhi (GV )', 'George Vettikuzhi (GV )', '917907534405', 'No', '2025-12-16 06:34:08'),
(9, 'Libs', 'Libs', '971555919609', 'No', '2025-12-16 13:36:41'),
(10, ',????? munauvar azmi', ',????? munauvar azmi', '918933941024', 'No', '2025-12-18 04:10:44'),
(11, 'Good burger Br3', 'Good burger Br3', '971521015277', 'No', '2025-12-19 01:27:23'),
(12, 'Anjali A J', 'Anjali A J', '971565302721', 'No', '2025-12-19 11:38:14'),
(13, 'akhilaa', 'akhilaa', '971545696292', 'No', '2025-12-19 13:01:38'),
(14, 'pokhrelram01', 'pokhrelram01', '971504453488', 'No', '2025-12-22 07:38:13'),
(15, 'CRO- Tawakkal muroor', 'CRO- Tawakkal muroor', '971506910630', 'No', '2025-12-23 05:15:40'),
(16, 'Edu skills', 'Edu skills', '971501526869', 'No', '2025-12-29 10:44:02');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_customer_session`
--

DROP TABLE IF EXISTS `whatsapp_customer_session`;
CREATE TABLE IF NOT EXISTS `whatsapp_customer_session` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contact_id` varchar(20) NOT NULL,
  `contact_type` varchar(20) DEFAULT 'Contact',
  `phone` varchar(20) NOT NULL,
  `session_started` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `whatsapp_customer_session`
--

INSERT INTO `whatsapp_customer_session` (`id`, `contact_id`, `contact_type`, `phone`, `session_started`) VALUES
(1, '229', 'Employee', '919846370759', '2025-12-02 21:13:29'),
(2, '230', 'Employee', '918967041980', '2025-12-10 06:13:06'),
(5, '152', 'Employee', '971509657209', '2025-12-15 05:26:38'),
(7, '39', 'Employee', '971504857525', '2025-12-22 07:57:28'),
(8, '235', 'Employee', '919188071759', '2025-12-04 09:05:16'),
(9, '2', 'Contact', '971559103109', '2025-12-04 09:26:36'),
(10, '3', 'Contact', '971569647484', '2025-12-04 09:52:45'),
(11, '4', 'Contact', '971508163215', '2025-12-05 10:51:56'),
(12, '70', 'Employee', '971504601320', '2025-12-10 07:18:46'),
(13, '5', 'Contact', '971545680565', '2025-12-12 05:28:56'),
(14, '6', 'Contact', '447790998833', '2025-12-14 11:45:27'),
(15, '7', 'Contact', '9779863154188', '2025-12-15 11:27:48'),
(16, '8', 'Contact', '917907534405', '2025-12-16 06:34:08'),
(17, '38', 'Employee', '971588090223', '2025-12-17 11:48:47'),
(18, '9', 'Contact', '971555919609', '2025-12-18 11:29:10'),
(19, '10', 'Contact', '918933941024', '2025-12-20 04:04:54'),
(20, '11', 'Contact', '971521015277', '2025-12-19 01:27:23'),
(21, '12', 'Contact', '971565302721', '2025-12-22 03:37:27'),
(22, '13', 'Contact', '971545696292', '2025-12-19 13:01:38'),
(23, '114', 'Employee', '971543233720', '2025-12-20 08:37:41'),
(24, '14', 'Contact', '971504453488', '2025-12-22 07:38:13'),
(25, '15', 'Contact', '971506910630', '2025-12-23 05:15:40'),
(26, '16', 'Contact', '971501526869', '2025-12-29 10:44:02');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_message_logs`
--

DROP TABLE IF EXISTS `whatsapp_message_logs`;
CREATE TABLE IF NOT EXISTS `whatsapp_message_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `contacts_id` varchar(20) NOT NULL,
  `contact_type` varchar(50) DEFAULT NULL,
  `direction` varchar(20) NOT NULL,
  `msg_id` varchar(150) DEFAULT NULL,
  `message_body` text NOT NULL,
  `interactive_reply_title` varchar(50) DEFAULT NULL,
  `interactive_reply_description` varchar(50) DEFAULT NULL,
  `msg_type` varchar(50) DEFAULT NULL,
  `media_fileUrl` varchar(250) DEFAULT NULL,
  `document_fileUrl` varchar(250) DEFAULT NULL,
  `document_caption` varchar(250) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=155 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `whatsapp_message_logs`
--

INSERT INTO `whatsapp_message_logs` (`id`, `contacts_id`, `contact_type`, `direction`, `msg_id`, `message_body`, `interactive_reply_title`, `interactive_reply_description`, `msg_type`, `media_fileUrl`, `document_fileUrl`, `document_caption`, `date_added`) VALUES
(1, '229', 'Employee', 'Incoming', 'wamid.HBgMOTE5ODQ2MzcwNzU5FQIAEhgWM0VCMEM1MzUzNTAyOTlFQ0VGQUYzQwA=', '', 'Job Enquiry', '', 'interactive', '', '', '', '2025-11-30 12:52:30'),
(2, '229', 'Employee', 'Outgoing', NULL, 'Hello,\r\nWe need a quick verification from your end regarding a recent request.\r\nPlease reply to this message when possible.', '', '', NULL, NULL, NULL, NULL, '2025-11-30 13:00:55'),
(3, '229', 'Employee', 'Outgoing', NULL, 'Hi', '', '', NULL, NULL, NULL, NULL, '2025-11-30 13:01:35'),
(4, '229', 'Employee', 'Outgoing', NULL, 'testing', '', '', NULL, NULL, NULL, NULL, '2025-11-30 13:01:44'),
(5, '229', 'Employee', 'Outgoing', NULL, 'Hi', '', '', NULL, NULL, NULL, NULL, '2025-11-30 13:01:51'),
(6, '229', 'Employee', 'Outgoing', NULL, 'Hello,\r\nWe need a quick verification from your end regarding a recent request.\r\nPlease reply to this message when possible.', '', '', NULL, NULL, NULL, NULL, '2025-11-30 13:02:09'),
(7, '229', 'Employee', 'Incoming', 'wamid.HBgMOTE5ODQ2MzcwNzU5FQIAEhgWM0VCMEU5Q0I2QjFGRTg1QzkwODdDOAA=', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/692c40b2a6512.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/692c40b2a6512.jpeg', 'test', '2025-11-30 13:03:47'),
(8, '229', 'Employee', 'Outgoing', NULL, '', NULL, NULL, 'image', 'https://erp.jrjapp.com/uploads/whatsapp/1764508071_9626.jpeg', 'https://erp.jrjapp.com/uploads/whatsapp/1764508071_9626.jpeg', '', '2025-11-30 13:07:54'),
(9, '229', 'Employee', 'Outgoing', NULL, 'Hello,\r\nWe need a quick verification from your end regarding a recent request.\r\nPlease reply to this message when possible.', '', '', NULL, NULL, NULL, NULL, '2025-11-30 15:36:49'),
(10, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM1QjUxQzAxNDhFQTJGRDEyQzMzQjAzRjk4QTg3MDAA', '', 'Job Enquiry', '', 'interactive', '', '', '', '2025-12-03 07:34:13'),
(11, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUMyREFEOThGNDAyRkY0QjBCQjU1OTg0MEFDODlBMkMA', 'Mr Ritesh has spoken to you regarding some requirement.', '', '', 'text', '', '', '', '2025-12-03 07:36:57'),
(12, '230', 'Employee', 'Outgoing', NULL, 'Hello . how can i help ?', '', '', NULL, NULL, NULL, NULL, '2025-12-03 08:00:15'),
(13, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM2NUY2NTc0OTQ4NzFEQzA1ODM2NUJDNDQ1RDJERDUA', 'Hello Sir', '', '', 'text', '', '', '', '2025-12-03 08:03:25'),
(14, '230', 'Employee', 'Outgoing', NULL, 'hello. whats the nest step to do ?', '', '', NULL, NULL, NULL, NULL, '2025-12-03 08:04:54'),
(15, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMEQzOTNBRjI0OTIzMEIxQTk2RAA=', 'you had a discussion with Mr. Ritesh and Mr. Jobby', '', '', 'text', '', '', '', '2025-12-03 08:06:24'),
(16, '230', 'Employee', 'Outgoing', NULL, 'yes', '', '', NULL, NULL, NULL, NULL, '2025-12-03 08:06:38'),
(17, '230', 'Employee', 'Outgoing', NULL, 'i also send email on the same', '', '', NULL, NULL, NULL, NULL, '2025-12-03 08:07:06'),
(18, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMEMxRjQxREQzNkUxQjA5NzZDNwA=', 'you were suppose to share an email', '', '', 'text', '', '', '', '2025-12-03 08:07:08'),
(19, '230', 'Employee', 'Outgoing', NULL, 'please see', '', '', NULL, NULL, NULL, NULL, '2025-12-03 08:10:27'),
(20, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMDhDRTU4NjU3QkE0NTlCRUJENgA=', 'ok sir', '', '', 'text', '', '', '', '2025-12-03 08:11:03'),
(21, '229', 'Employee', 'Incoming', 'wamid.HBgMOTE5ODQ2MzcwNzU5FQIAEhgUM0E1QjBGRTRDNkM0NTY4NjU0OTcA', 'Hi test', '', '', 'text', '', '', '', '2025-12-03 08:13:29'),
(22, '229', 'Employee', 'Outgoing', NULL, '', NULL, NULL, 'image', 'https://erp.jrjapp.com/uploads/whatsapp/1764749955_4876.png', 'https://erp.jrjapp.com/uploads/whatsapp/1764749955_4876.png', 'test', '2025-12-03 08:19:18'),
(23, '230', 'Employee', 'Outgoing', NULL, '', NULL, NULL, 'image', 'https://erp.jrjapp.com/uploads/whatsapp/1764752313_4809.jpg', 'https://erp.jrjapp.com/uploads/whatsapp/1764752313_4809.jpg', '', '2025-12-03 08:58:37'),
(24, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM1Nzc4RTVGOEU4QzI5NUE5QjcwMTRGNzU4NTMyN0UA', 'Ohk sir', '', '', 'text', '', '', '', '2025-12-03 08:59:03'),
(25, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNGNDFERkVEMTQwNjVBRURFREVDMzkwODQyMzI1RDgA', 'Okay sar', '', '', 'text', '', '', '', '2025-12-03 12:02:06'),
(26, '229', 'Employee', 'Outgoing', NULL, 'Hello,\r\nWe need a quick verification from your end regarding a recent request.\r\nPlease reply to this message when possible.', '', '', NULL, NULL, NULL, NULL, '2025-12-04 03:47:38'),
(27, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMDlENEE0NDBDQkNDNjQyREFFRQA=', 'Good Morning Sir', '', '', 'text', '', '', '', '2025-12-04 05:47:53'),
(28, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMEY3NkY2N0M0OTg1NkU5ODFBOQA=', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693120a08ea5b.pdf', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693120a08ea5b.pdf', 'English Translation Licence Lucky Overseas Pvt. Ltd 2025.pdf', '2025-12-04 05:48:17'),
(29, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNFMjk2NkZCRDQwRkFFOEUyNkE0MzdGQTE0NkYzMDAA', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693120d5ea117.vnd.openxmlformats-officedocument.wordprocessingml.document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693120d5ea117.vnd.openxmlformats-officedocument.wordprocessingml.document', 'DEMAND FILE The first fitness maker sports center foundation.docx', '2025-12-04 05:49:10'),
(30, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM4NUMyNjIzRTE5QTA3OThEQkM0OTlENDdBQ0FFNTgA', 'We have the details of Nepal agency', '', '', 'text', '', '', '', '2025-12-04 05:49:35'),
(31, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUMwQUVDMTk5MTBCNUVEQTVGMzYxQUQyRDE1MkNGNDcA', 'Please share the demand letter', '', '', 'text', '', '', '', '2025-12-04 05:50:09'),
(32, '39', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0ODU3NTI1FQIAEhggQTVBRTlCQjFGODgxN0VGNkY4ODBGQUYwOUIwMEU3RDYA', 'Hi', '', '', 'text', '', '', '', '2025-12-04 07:24:51'),
(33, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMERDQjBGRDRBREZFNDg3N0NDRgA=', 'https://we.tl/t-3TtauXwMOe', '', '', 'text', '', '', '', '2025-12-04 08:49:48'),
(34, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMEI5NDVEMTQzNzc0RkIwQjNEQgA=', 'We are sharing some CVs from Nepal.', '', '', 'text', '', '', '', '2025-12-04 08:50:20'),
(35, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMDlBNjdDQkUyRUZEODVBMDJEMwA=', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69314b6207d5d.vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69314b6207d5d.vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'CANDIDATES FOR UAE- AbuDhabi.xlsx', '2025-12-04 08:50:42'),
(36, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNCMjNFRjBCMUZFOTJEMTRFNTE4RTExNkQzRjNFMzYA', 'Sir can I call you', '', '', 'text', '', '', '', '2025-12-04 08:52:57'),
(37, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNGODA3QUQzNEM1MUNGMDY0RDU2OTYwNTE4NTU3MzEA', 'To join the meeting on Google Meet, click this link: \nhttps://meet.google.com/gef-hgpb-pyu \n \nOr open Meet and enter this code: gef-hgpb-pyu', '', '', 'text', '', '', '', '2025-12-04 08:54:03'),
(38, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM2QUFDNzJCREUwM0E5RjZGMDZBMjM5M0FBNDkzOUUA', 'Sir, could you please join the link', '', '', 'text', '', '', '', '2025-12-04 08:54:18'),
(39, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMEY4MTdFMzM5MEQzQ0E1MTg4NgA=', 'ok', '', '', 'text', '', '', '', '2025-12-04 09:03:35'),
(40, '235', 'Employee', 'Incoming', 'wamid.HBgMOTE5MTg4MDcxNzU5FQIAEhggQUMyRTkxRkM4NjQyQjE3QTM2NTZCMzMxOEY3OENBOEUA', 'Testing a new contact', '', '', 'text', '', '', '', '2025-12-04 09:05:16'),
(41, '235', 'Employee', 'Outgoing', NULL, 'Hello,\r\nWe need a quick verification from your end regarding a recent request.\r\nPlease reply to this message when possible.', '', '', NULL, NULL, NULL, NULL, '2025-12-04 09:08:07'),
(42, '235', 'Employee', 'Outgoing', NULL, '', NULL, NULL, 'image', 'https://erp.jrjapp.com/uploads/whatsapp/1764839562_6413.png', 'https://erp.jrjapp.com/uploads/whatsapp/1764839562_6413.png', '', '2025-12-04 09:12:46'),
(44, '2', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU5MTAzMTA5FQIAEhgUMkFDNUQ1OUJFNjc0MjFEMUM2RkQA', 'Hi', '', '', 'text', '', '', '', '2025-12-04 09:26:36'),
(45, '3', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY5NjQ3NDg0FQIAEhggQUMxQ0Y3Rjk5QTdDOEZEOTlFRjJBODRCN0Q3MkZBNEEA', 'Hi', '', '', 'text', '', '', '', '2025-12-04 09:52:45'),
(46, '2', 'Contact', 'Outgoing', NULL, 'hello how are you', '', '', NULL, NULL, NULL, NULL, '2025-12-04 12:06:31'),
(47, '2', 'Contact', 'Outgoing', NULL, 'just testing', '', '', NULL, NULL, NULL, NULL, '2025-12-04 12:06:42'),
(48, '2', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU5MTAzMTA5FQIAEhgUMkExQTA4NDg3RDJDRjY1MDk2NDYA', '?', '', '', 'text', '', '', '', '2025-12-04 13:14:58'),
(49, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM5MTg0QTVBRUIwRDc0NDg0MTM2QTFFNDdBOTdFOTYA', 'Good morning sir', '', '', 'text', '', '', '', '2025-12-05 06:31:41'),
(50, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUMzOUNGRjlDQzQ3M0JBRkFBQUFGOThCNjQ3MkI0MTEA', 'Are you using botim or any other app for calling', '', '', 'text', '', '', '', '2025-12-05 06:32:17'),
(51, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUMzNUE2QTcyODBBODkzRUQyQTFBN0I2OTJFOTJFQkYA', 'Same number', '', '', 'text', '', '', '', '2025-12-05 06:43:54'),
(52, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM5Q0QxMDMyRDUxMzYxNUE4NUIxRkRGOEQ3REE4Q0MA', 'Ohk sir', '', '', 'text', '', '', '', '2025-12-05 06:45:13'),
(53, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMEI2NTI5NzBENEYyNURBM0YxMQA=', 'corporate@futureplacements.in', '', '', 'text', '', '', '', '2025-12-05 07:14:26'),
(54, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUMxQkNEOTJBQTZGODQ0MUFBNjA0OTRBNzZCMzRBRDQA', 'Sir,  could you please give us some time. Mr Ritesh wants to connect for a short call.', '', '', 'text', '', '', '', '2025-12-05 07:29:42'),
(55, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM1OTlFQTI2OTMxQUFBNjRBMTk1ODZDRDM1NDdFODUA', 'To join the meeting on Google Meet, click this link: \nhttps://meet.google.com/joa-utci-kav \n \nOr open Meet and enter this code: joa-utci-kav', '', '', 'text', '', '', '', '2025-12-05 07:30:19'),
(56, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNGNUY2MDI5MUY4QzZGODBCNjJEMDkzODIyMDU5ODkA', 'Yes sir', '', '', 'text', '', '', '', '2025-12-05 07:38:41'),
(57, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNEQkVFMEVGMTkxNUQzMTZFQkFCOTgwODQzMTlBRjgA', 'We are waiting', '', '', 'text', '', '', '', '2025-12-05 07:38:47'),
(58, '3', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY5NjQ3NDg0FQIAEhggQUM2NDg3RDlBREIxRDZFQjY4MEQyNzE0MjZDRTA4ODIA', 'Size pls', '', '', 'text', '', '', '', '2025-12-05 10:47:24'),
(59, '4', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTA4MTYzMjE1FQIAEhggQTVGODQyOTU1RjhCQTg1MzA2QTUxNjc2NDlCMzBGMUEA', 'Ok', '', '', 'text', '', '', '', '2025-12-05 10:51:56'),
(60, '2', 'Contact', 'Outgoing', NULL, 'hi', '', '', NULL, NULL, NULL, NULL, '2025-12-05 13:20:51'),
(61, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNDQkFGMDIyRjM1RTYwRjU1REU2M0NDNzdBNTQzM0IA', 'Good afternoon Sir,', '', '', 'text', '', '', '', '2025-12-08 10:16:51'),
(62, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUMwMkY5RDg0NzBBQTEyQjE0NTNDMzBERUIxNjhGMkYA', 'Sorry for responding late.', '', '', 'text', '', '', '', '2025-12-08 10:17:11'),
(63, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNFNTJGN0JFQ0I4NkI2QUE0NkI5RkZGOEM4Q0MyNjQA', 'I am checking with the agency, and update you', '', '', 'text', '', '', '', '2025-12-08 10:17:28'),
(64, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM2QzQ2QkRDMDExN0ZCQ0NGNzEwNzI3Q0U2M0VENEUA', 'Good morning sir', '', '', 'text', '', '', '', '2025-12-09 06:29:17'),
(65, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM0NTg1NDYyOTNFNkQxOTE3RkVBQUZFMzQ4OUQ5RTAA', 'We checked with the Nepal agency, they require POE/ Demand letter for arranging the interviews.', '', '', 'text', '', '', '', '2025-12-09 06:30:51'),
(66, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMDRBRjgzMjEwMDM0QTJFMkY0OAA=', 'Good morning Sir,', '', '', 'text', '', '', '', '2025-12-10 06:13:06'),
(67, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUNGMDIzNTI0MTAwQzVCRTA4MzE1NDY1QjUyQjgxREEA', 'This the Demand letter which needs to be edited with your company details and take print on the letter head.', '', '', 'text', '', '', '', '2025-12-10 06:16:23'),
(68, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhggQUM3RkY4NEREQTk3MkY3RjIzRjlCREMwQjY5NjIxQ0EA', 'These are the details of the Nepal agency and their license details. Take print and attached with the Demand letter.', '', '', 'text', '', '', '', '2025-12-10 06:18:04'),
(69, '70', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0NjAxMzIwFQIAEhggQTU4OEFDRkM3RDFGRDRFQzAyRTY4MzBEMzk2OUQwQjQA', 'Hy', '', '', 'text', '', '', '', '2025-12-10 07:18:46'),
(70, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMDE5NEZEMjQyQjFBMTc0OUEzQQA=', 'Good evening Sir', '', '', 'text', '', '', '', '2025-12-10 11:31:14'),
(71, '230', 'Employee', 'Incoming', 'wamid.HBgMOTE4OTY3MDQxOTgwFQIAEhgWM0VCMDE3QUI1NEExODVERUY0MDIzNQA=', 'please let us know once the documents are ready at your end.', '', '', 'text', '', '', '', '2025-12-10 11:32:21'),
(72, '5', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTQ1NjgwNTY1FQIAEhggQTVBNjUzRDkxNEIxMDAwMUEyMkY0RjFDNTZDODI5Q0IA', 'Hi, \nHow are you Mr. Dilip?\nDo you have any vacancies in the garment industry? As a sewing machine operator, because I have one lady looking for a job. If you have any requirements, please let me know.', '', '', 'text', '', '', '', '2025-12-10 16:39:10'),
(73, '5', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTQ1NjgwNTY1FQIAEhggQTVDM0U1QjczQ0I3RUU0QkE1RDJERkFENDExMDdGQjUA', 'No.\nIn Sri Lanka \nI will send CV and video clips', '', '', 'text', '', '', '', '2025-12-11 03:09:57'),
(74, '5', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTQ1NjgwNTY1FQIAEhggQTU0Qjk3Q0JDMTEzQkFFMkY4NTk4MEFENEY2NjJCNjQA', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693ba8184e712.pdf', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693ba8184e712.pdf', 'Gray and Green Simple Professional  CV Resume.pdf', '2025-12-12 05:28:56'),
(75, '5', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTQ1NjgwNTY1FQIAEhggQTVGQ0M4ODQzOTFEMzRCODUzRkUwNTBBRUU5MTk1OUMA', 'Can you find any vacancies', '', '', 'text', '', '', '', '2025-12-12 08:54:27'),
(76, '6', 'Contact', 'Incoming', 'wamid.HBgMNDQ3NzkwOTk4ODMzFQIAEhggQUNFQjNBNzU2ODE5NDIyNDU2QzY5MkNEMjJFRjhGMjAA', 'Hi', '', '', 'text', '', '', '', '2025-12-14 11:45:27'),
(77, '6', 'Contact', 'Incoming', 'wamid.HBgMNDQ3NzkwOTk4ODMzFQIAEhggQUM4OUFBNTk1RDY4MThFNzEzQUNDRUJEMkIzMDE0OTUA', 'a', '', '', 'text', '', '', '', '2025-12-14 11:46:06'),
(78, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNCRDc1QzMyQzQ1Rjg5QUEwN0RDNUE3MzM1QUE1NjIA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c0e57577.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c0e57577.jpeg', '2929', '2025-12-15 05:26:38'),
(79, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUMxMDVCMkJFQzU5ODVBNzZDOTY1MDBFNjcwRjU2OTYA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c4929c8a.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c4929c8a.jpeg', '2790', '2025-12-15 05:27:37'),
(80, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUM2REM2QjQwMzJFQTY5Nzc2NEI5NERDNzQxOTc4RDMA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c6a3846d.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c6a3846d.jpeg', '3284', '2025-12-15 05:28:10'),
(81, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUM5ODZERDExNDQ2QkZCNjlCRURDQTJDQjg5QzkzRDcA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c84247f8.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c84247f8.jpeg', '', '2025-12-15 05:28:36'),
(82, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNCNTQxQUE4NDhGRjEwRjY4REM0MTJEMDkxMDk5NUMA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c9a77ad1.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9c9a77ad1.jpeg', '', '2025-12-15 05:28:58'),
(83, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNENUQzMUIyMTgyNTZFNzdGNDI3QzgxRTVEQjEzNjgA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9ca76b642.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9ca76b642.jpeg', '', '2025-12-15 05:29:11'),
(84, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUM3OTI0M0M1RUUzREFDOEJBMUQ4OTU3ODE4NzgzNjgA', '3068', '', '', 'text', '', '', '', '2025-12-15 05:29:22'),
(85, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNFM0RENEY3MTU1QjVGNjA4MkZGMjU1M0FCMDc1QTgA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9cc58ec76.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9cc58ec76.jpeg', '', '2025-12-15 05:29:42'),
(86, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNDOERDMTUzQjMzQzMxQ0UwMzMyMTk2NjM2RDlBNDQA', '2790 ram pavesh', '', '', 'text', '', '', '', '2025-12-15 05:34:43'),
(87, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNBQUYxRDI2MDE1Nzg4NzlCN0NBNEJEMUZDREQ5MEEA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1454448.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1454448.jpeg', '', '2025-12-15 05:35:16'),
(88, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNGMEFCNzZCMkFGMTdGM0VFMTc0MUJDMDRCMzA1MjgA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e15c97f9.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e15c97f9.jpeg', '', '2025-12-15 05:35:18'),
(89, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUMxREE4ODI5QkI3QjFEQkQ2QTdBODJFNkNCREMzMTQA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e17dbbf1.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e17dbbf1.jpeg', '', '2025-12-15 05:35:20'),
(90, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNGMzk0OTAwM0IzQ0FFNkRFM0NGNDg2Qjg5NzhBQzEA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1912759.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1912759.jpeg', '', '2025-12-15 05:35:21'),
(91, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUM0MENDMUI3RDcxMDgyMkNBMTZBQTQ1MTJBMjdGOEYA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1944552.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1944552.jpeg', '', '2025-12-15 05:35:21'),
(92, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUM4RjNFNDFFMjE3NURENDVFNUI4RjI4RjAxQUQ4OTEA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1b0e90c.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1b0e90c.jpeg', '', '2025-12-15 05:35:23'),
(93, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUNDNzkzOTYyNTUwQzYwM0VCQThBMjZCNEJBNTJDQkQA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1c4927a.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1c4927a.jpeg', '', '2025-12-15 05:35:24'),
(94, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUMyMTY1RDBCRDk5MzAwNDcwM0VFN0UyNTkzRkE4NkMA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1d00a86.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1d00a86.jpeg', '', '2025-12-15 05:35:25'),
(95, '152', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA5NjU3MjA5FQIAEhggQUMzRUQ3Q0VBNjVFRTVERTU3RTE2OEI2QkE4RDgwM0YA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1d20d22.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693f9e1d20d22.jpeg', '', '2025-12-15 05:35:25'),
(96, '7', 'Contact', 'Incoming', 'wamid.HBgNOTc3OTg2MzE1NDE4OBUCABIYIEFDRUNDMDBDQjAyMEUwNjczQjg1RDhENTVDODExQkYzAA==', 'Hi', '', '', 'text', '', '', '', '2025-12-15 11:27:48'),
(97, '7', 'Contact', 'Incoming', 'wamid.HBgNOTc3OTg2MzE1NDE4OBUCABIYIEFDNTVBQTZFMzlFMTI0OTFDODdDODQzQTg1NzcwNENGAA==', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693fface21902.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/693fface21902.jpeg', '', '2025-12-15 12:10:54'),
(98, '8', 'Contact', 'Incoming', 'wamid.HBgMOTE3OTA3NTM0NDA1FQIAEhggQUMzRkZGRUU5MUI4NzNBMTMzMjlGQjdGRjczRjY1QTgA', 'hai', '', '', 'text', '', '', '', '2025-12-16 06:34:08'),
(99, '8', 'Contact', 'Incoming', 'wamid.HBgMOTE3OTA3NTM0NDA1FQIAEhggQUNDODM1MzEzMEE2MkQwQUVEM0IyMkZBQkRGMUEzNjQA', 'നമുക്ക് എല്ലാം ഒന്ന് പ്ലാൻ ചെയ്യേണ്ടേ', '', '', 'text', '', '', '', '2025-12-16 06:38:57'),
(100, '38', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTg4MDkwMjIzFQIAEhggQUMxOTg1RTBBQTgzNDVDNjk3MzY4RDkyNDZDQ0YyNTYA', 'Hi', '', '', 'text', '', '', '', '2025-12-16 07:07:38'),
(101, '38', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTg4MDkwMjIzFQIAEhggQUNEQTdCQjU4NDJCRENCRjJFOTU1Mjc3Rjg2QUY5NTYA', 'Jitender Nemi chand', '', '', 'text', '', '', '', '2025-12-16 07:07:46'),
(102, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTVCQTBGNzdGQjJBRUY0NjRBNUUyRjIwMUVENDE4NkMA', 'Hi,', '', '', 'text', '', '', '', '2025-12-16 13:36:41'),
(103, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTU4MkRENTBENzk1MTEwODM5NTEwMTIzNTAyQkQ3OUMA', 'Libi here', '', '', 'text', '', '', '', '2025-12-16 13:36:46'),
(104, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTU0OEY0MUQ2QTVFNDFCRTJDRDc5MEUzQjA5RTIzNzQA', 'Thank you for your time.', '', '', 'text', '', '', '', '2025-12-16 13:37:00'),
(105, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTUwRTE2RkVDREEwMjJFOUE3ODRFMzBDQkZBRUUxOUYA', 'Nice talking to you. Thanks', '', '', 'text', '', '', '', '2025-12-16 13:39:13'),
(106, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTU5MzgxNUNCNEQ1NjA0RDU1RjFGNDg0RjNBNjI3NkYA', 'If things go well, is it okay to join from Jan?', '', '', 'text', '', '', '', '2025-12-16 13:41:32'),
(107, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTU5RjlGMUM5RUMyNzQxNzhDMDc2QkUzNTZDNUYxQ0MA', 'Okay.', '', '', 'text', '', '', '', '2025-12-16 13:43:10'),
(108, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTUzMTIwQUMyNjU2NzZBNTZEQTZDRjNEOUZGREY0ODkA', 'Hi', '', '', 'text', '', '', '', '2025-12-17 08:29:12'),
(109, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTVFNUQ5QzYxODNGMTc2MTk0MzkyRTRDNUYxMTQ4NjEA', 'When should I expect your call', '', '', 'text', '', '', '', '2025-12-17 08:29:29'),
(110, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTVFNkNGNzBFODdFRTU3RDhGMDJBODI1QUVERkMxNUIA', '✋?', '', '', 'text', '', '', '', '2025-12-17 10:30:47'),
(111, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTVDQ0JGMjRFQ0RFNDUyREYwNTVDOEM2ODJCQ0I5QkEA', 'Okay', '', '', 'text', '', '', '', '2025-12-17 11:32:20'),
(112, '38', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTg4MDkwMjIzFQIAEhggQUM1QTBCQTY2RUNEOUIzM0E1N0JDMzQ4NzEzQ0ZDNTMA', 'Hello', '', '', 'text', '', '', '', '2025-12-17 11:48:47'),
(113, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTU3MDk2OUM3M0E2QzgyOUUwQUU5NTk4ODZDOUQ1NjQA', 'Dear Mr Dileep,\n\nI had several work commitments and a personal meeting scheduled for today, however, I put everything on hold and remained available throughout the day in anticipation of this interview.', '', '', 'text', '', '', '', '2025-12-17 13:48:27'),
(114, '10', 'Contact', 'Incoming', 'wamid.HBgMOTE4OTMzOTQxMDI0FQIAEhggQUMyQkRDMzdGRDRFQjc4QkQ0OTVEOTNEOTZDMjAwMEMA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69437ec43058d.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69437ec43058d.jpeg', '', '2025-12-18 04:10:44'),
(115, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTVBMDM0OUUwQTM2NURDQUQ4Mzg2NEQxQTVFNzA2QTkA', 'Noted with thanks.', '', '', 'text', '', '', '', '2025-12-18 11:29:10'),
(116, '9', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTU1OTE5NjA5FQIAEhggQTU2MEZCRDA2ODhCMzE2M0QzNDMxNTVCOUU5RkRDNzgA', 'Yes...Just noticed', '', '', 'text', '', '', '', '2025-12-18 11:55:44'),
(117, '11', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTIxMDE1Mjc3FQIAEhggQUMwMDY3RUM0QzVEQjU0NUVEREQ4QUQ5Q0IyMTA5NTYA', 'Good burger location sandme', '', '', 'text', '', '', '', '2025-12-19 01:27:23'),
(118, '11', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTIxMDE1Mjc3FQIAEhggQUM1NkZGMDA4MTdFN0I2MzQ2NzMxRjFGNEQ5Q0UyQzUA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/6944abbe4fe37.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/6944abbe4fe37.jpeg', '', '2025-12-19 01:34:54'),
(119, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTUzMDJCMTEzMDlBMkUzNDc5MEQzRjFFMjk4QkVBOTgA', 'Hi ,I am Anjali', '', '', 'text', '', '', '', '2025-12-19 11:38:14'),
(120, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTVCRDIzQjAzNkNERkJBOUEzQTgyOTk2RUI1M0VBRjcA', 'No still seeking job,\nOnly got the offer letter from Sales job in Credit card/loans& insurance', '', '', 'text', '', '', '', '2025-12-19 11:39:49'),
(121, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTU0NUY2RDFBNDNCNTk2MEQwOTYxMUFFQUE2NjExQjIA', 'Okay \nAre you Dileep sir?', '', '', 'text', '', '', '', '2025-12-19 11:40:32'),
(122, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTVFMjJFRTFEMkQ0OUJFODY1RjI3QUZGODJCNDk1OTAA', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69453a0d0e30b.pdf', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69453a0d0e30b.pdf', 'Anjali A J Accounts.pdf', '2025-12-19 11:42:05'),
(123, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTVGN0QxQzg3NzM4ODBGM0JFN0YxRDBGRkFDOUFCRDEA', 'Okay', '', '', 'text', '', '', '', '2025-12-19 11:42:19'),
(124, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTUxN0M5RjNEMTEwRjEzNjFENzJDOTRDMjUwRkI5NUUA', 'Sir,do you have any option to recruit for banking operations job', '', '', 'text', '', '', '', '2025-12-19 11:42:56'),
(125, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTUzQzJCQUI1ODFCRThEQjQyOUU4MTVFNUYyRkYyNUIA', 'Sure', '', '', 'text', '', '', '', '2025-12-19 11:49:22'),
(126, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTVBMTQyMEM1MzQ0MTY5NzhCRjEzRjlEM0Q1NTMxODcA', 'I hold an MBA in Finance & Marketing and bring hands-on experience in banking operations for 3.8years from ICICI Bank Ltd and also transaction processing, customer handling, regulatory compliance support, and service quality management. I am particularly interested in roles where I can contribute to operational efficiency, compliance adherence, and customer satisfaction.\n\nCurrently I am residing in Dubai,UAE and Iam actively seeking opportunities in banking operations, compliance/officer roles within exchanges, or customer service/client care positions across banking, financial services, or other professional organizations.', '', '', 'text', '', '', '', '2025-12-19 11:53:33'),
(127, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTU4MEM2MzQ3NDlDMTIyMTQ2OTdGODlEQTJBMDYwQUUA', 'Skills I do have was;\n\n•Banking Operations & Back-Office Processing\n•Transaction Processing & Reconciliation\n•KYC / AML Compliance Support\n•Customer Due Diligence (CDD & EDD)\n•Regulatory Documentation & Reporting\n•Policy & Procedure Adherence\n•Risk Assessment & •Operational Controls\n•Account Opening & Maintenance\n•Forex & Remittance Operations (Basic–Intermediate)\n•Customer Relationship Management (CRM)\n•Client Onboarding & Support\n•Complaint Handling & Resolution\n•Service Quality & TAT Management\n•Cross-Functional Coordination\n•Call Handling (Inbound & Outbound)\n•Service Recovery & Customer Retention\n\n\nSystem Skills include;\n\n•Core Banking Systems \n•MS Excel \n•MS Word & PowerPoint\n•CRM Tools\n•Data Accuracy & Record Management', '', '', 'text', '', '', '', '2025-12-19 11:59:47'),
(128, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTUzNTVFMjZBQjU2RjU2QzEyMzUzNzdDNUNEMDU5RkEA', '•Banking Operations / Back Office Officer\n\n•Compliance Officer / Compliance Analyst (Banks, Exchanges, FinTech)\n\n•Operations Officer – Money Exchange / Forex\n\n•Customer Service Officer / Client Care Executive\n\n•Transaction Processing / Operations Support Officer', '', '', 'text', '', '', '', '2025-12-19 12:33:05'),
(129, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTUwQzJBQzg3NkFFMEQ4RTFDNzA3NEFDQkNDOERFRjIA', 'Expecting a salary of 4000AED and above', '', '', 'text', '', '', '', '2025-12-19 12:33:33'),
(130, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTUxQkEzRTkzMTJFM0RDNzU5MEVGMzNBRkI4OUJBMDkA', 'Okay', '', '', 'text', '', '', '', '2025-12-19 12:35:40'),
(131, '13', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTQ1Njk2MjkyFQIAEhggQTVBMTdERTMyNTQ1OEM1MkM0OTA0OEEwRDdDRUVFMEMA', 'Hlo sir', '', '', 'text', '', '', '', '2025-12-19 13:01:38'),
(132, '13', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTQ1Njk2MjkyFQIAEhggQTUwODFGNjVFRDdFN0EwNDcwMDdCODJBNjgyNTg5QjIA', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69454ccae1b4b.pdf', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69454ccae1b4b.pdf', 'Akhila_A-1.pdf', '2025-12-19 13:02:03'),
(133, '10', 'Contact', 'Incoming', 'wamid.HBgMOTE4OTMzOTQxMDI0FQIAEhggQUM4NzRCODUyREZFQjIzQjhCODJCNjU0OTcyMTJBNkUA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/694620660aff7.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/694620660aff7.jpeg', '', '2025-12-20 04:04:54'),
(134, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTU5MTM5NzM4N0JFQkM3QzhENjM1Nzg4RjE1RTc2NjIA', 'Good morning sir', '', '', 'text', '', '', '', '2025-12-20 04:42:34'),
(135, '114', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTQzMjMzNzIwFQIAEhggQTUzQTk2NTQwQTYyNTIzRDVGMzY1N0VEODYwNDc3NTIA', 'Hi', '', '', 'text', '', '', '', '2025-12-20 08:37:41'),
(136, '114', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTQzMjMzNzIwFQIAEhggQTU1REQwOUEyMkNDMENEMzY4Mzk4NzQ5QkVEREJBMzIA', 'Good afternoon', '', '', 'text', '', '', '', '2025-12-20 08:37:44'),
(137, '12', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTY1MzAyNzIxFQIAEhggQTVFMEM5NTM5RTY1RTI2QjI1MTcxMDYwMDgwOTg5NTkA', 'Good morning sir \nIs there any option sir', '', '', 'text', '', '', '', '2025-12-22 03:37:27'),
(138, '14', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTA0NDUzNDg4FQIAEhggQUNBMEM5MUE5Q0JBRTc1MTlFQUU2NEI5QUEyMjI5OUQA', 'Hi', '', '', 'text', '', '', '', '2025-12-22 07:38:13'),
(139, '39', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0ODU3NTI1FQIAEhggQTVCRTMxNDk3MkVEMThBQjJCNzJFQzdCMjI2ODlBRDYA', 'Hi', '', '', 'text', '', '', '', '2025-12-22 07:57:28'),
(140, '39', 'Employee', 'Outgoing', NULL, 'ok', '', '', NULL, NULL, NULL, NULL, '2025-12-22 08:06:58'),
(141, '39', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0ODU3NTI1FQIAEhggQTUwRTIzNTkxQkM2MjExRUZBQkM1RTY0OENEMDRFNzAA', 'Ashok  kampelli', '', '', 'text', '', '', '', '2025-12-22 09:38:18'),
(142, '39', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0ODU3NTI1FQIAEhggQTVCOTlDQjVENUZDOTk1NUU3NDc0MUQ5NDY4OTE5RDEA', '2877', '', '', 'text', '', '', '', '2025-12-22 09:38:25'),
(143, '39', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0ODU3NTI1FQIAEhggQTVDRDNBNTdFRUU5NjBBQUY2MTEzRjBEMDUxNkYzMjcA', '', '', '', 'image', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/694911a9047ee.jpeg', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/694911a9047ee.jpeg', '', '2025-12-22 09:38:49'),
(144, '39', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0ODU3NTI1FQIAEhggQTU3MUVGNjYxN0MwMUJGRjFEQkFBMjAwQzM0QUU4NzcA', 'I plan my annual leave next month 5th. Please accept my annual vacation.', '', '', 'text', '', '', '', '2025-12-22 09:43:07'),
(145, '39', 'Employee', 'Incoming', 'wamid.HBgMOTcxNTA0ODU3NTI1FQIAEhggQTVFQ0EzRkVGMDZGRDFBQkU4NkNDREZGMkI2QTA1MzQA', '60  days', '', '', 'text', '', '', '', '2025-12-22 10:04:31'),
(146, '14', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTA0NDUzNDg4FQIAEhggQUNERDI4MTNBOTcyRUI0MUFGMTYyRDBFOUM5REYzMzcA', 'Dear Employee, Complete the Signing of job offer and contract of MB306520509AE using the following link. \n\nيمكنك امضاء عرض وعقد العمل MB306520509AE من خلال الرابط \n\nhttps://eservices.mohre.gov.ae/TasheelWeb/d/bprfa2xz6tun \n\n\nللاستفسار يرجى التواصل على الرقم 600590000', '', '', 'text', '', '', '', '2025-12-22 15:56:30'),
(147, '15', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTA2OTEwNjMwFQIAEhggQTU2QTIwMjU1ODIyNjgxN0E1NDhGQjkwMkMzQUM0NDEA', 'Hello\nGood morning', '', '', 'text', '', '', '', '2025-12-23 05:15:40'),
(148, '15', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTA2OTEwNjMwFQIAEhggQTU4MzE5Q0U5NkI5RTFCRDMwOTgxQTMxQ0M2QzVEQjAA', 'This is from Al Tawakkal Typing', '', '', 'text', '', '', '', '2025-12-23 05:16:43'),
(149, '16', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTAxNTI2ODY5FQIAEhggQTU5QTFDQUM5REFEMEMwN0Q3Qjg0OUIzMUVERDY5M0MA', 'Hello dileep', '', '', 'text', '', '', '', '2025-12-29 10:44:02'),
(150, '16', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTAxNTI2ODY5FQIAEhggQTU3QTk5M0I5RENDQjMxQjA2OTBBQzU5MkQwMTcwNzQA', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69525b935dc17.pdf', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69525b935dc17.pdf', 'List of trainings.pdf', '2025-12-29 10:44:35'),
(151, '16', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTAxNTI2ODY5FQIAEhggQTU2REExNjUxREVGNTFFNjFENTc5OEE3ODUxRDZGMzMA', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69525b93c4a77.pdf', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69525b93c4a77.pdf', 'ACTVET Training License 2026 (3).pdf', '2025-12-29 10:44:36'),
(152, '16', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTAxNTI2ODY5FQIAEhggQTUzMzZGNDY1QzU2QzY5QzQ5QzRBRTg3NzFERjkyNDUA', '', '', '', 'document', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69525b9435665.pdf', 'https://d3ou8w1nhk5snj.cloudfront.net/user_chats/9836/69525b9435665.pdf', 'KHDA Permit Eduskills 2025.pdf', '2025-12-29 10:44:36'),
(153, '16', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTAxNTI2ODY5FQIAEhggQTUyNzQ0QzcwMTIxQjQ0MDk1OEE4MzhFMEI2Nzg1OEQA', 'If u need any training or certification in the future just let me know', '', '', 'text', '', '', '', '2025-12-29 10:45:22'),
(154, '16', 'Contact', 'Incoming', 'wamid.HBgMOTcxNTAxNTI2ODY5FQIAEhggQTUxRThDMjk4NUMwNDExOUUyNjdDREJEQkQ3MDNCM0EA', 'And if you know anyone who needs this, kindly share my number to them', '', '', 'text', '', '', '', '2025-12-29 10:45:51');

-- --------------------------------------------------------

--
-- Table structure for table `whatsapp_templates`
--

DROP TABLE IF EXISTS `whatsapp_templates`;
CREATE TABLE IF NOT EXISTS `whatsapp_templates` (
  `id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `tmp_id` varchar(50) NOT NULL,
  `content` text NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `whatsapp_templates`
--

INSERT INTO `whatsapp_templates` (`id`, `name`, `tmp_id`, `content`, `date_added`) VALUES
(1, 'Start Chat', 'startchat', 'Hello,\nWe need a quick verification from your end regarding a recent request.\nPlease reply to this message when possible.', '2025-11-27 06:06:46'),
(2, 'Hi', 'hi', 'Hi', '2025-11-20 08:05:58');

--
-- Constraints for dumped tables
--

--
-- Constraints for table `applicant_activity_logs`
--
ALTER TABLE `applicant_activity_logs`
  ADD CONSTRAINT `fk_activity_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applicant_documents`
--
ALTER TABLE `applicant_documents`
  ADD CONSTRAINT `fk_docs_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `applicant_status_history`
--
ALTER TABLE `applicant_status_history`
  ADD CONSTRAINT `fk_status_applicant` FOREIGN KEY (`applicant_id`) REFERENCES `applicants` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `created_documents`
--
ALTER TABLE `created_documents`
  ADD CONSTRAINT `created_documents_ibfk_1` FOREIGN KEY (`template_version_id`) REFERENCES `template_versions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `customers_soa`
--
ALTER TABLE `customers_soa`
  ADD CONSTRAINT `customers_soa_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE ON UPDATE RESTRICT;

--
-- Constraints for table `interactions`
--
ALTER TABLE `interactions`
  ADD CONSTRAINT `interactions_ibfk_1` FOREIGN KEY (`channel_id`) REFERENCES `channels` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interactions_ibfk_2` FOREIGN KEY (`contact_type_id`) REFERENCES `contact_types` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `interactions_ibfk_3` FOREIGN KEY (`scenario_id`) REFERENCES `scenarios` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `template_versions`
--
ALTER TABLE `template_versions`
  ADD CONSTRAINT `template_versions_ibfk_1` FOREIGN KEY (`template_id`) REFERENCES `templates` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 07, 2025 at 06:04 PM
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
-- Database: `jrj_crm_erp`
--

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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `autologin_tokens`
--

INSERT INTO `autologin_tokens` (`id`, `person_id`, `token`, `created_by`, `created_at`, `expires_at`, `used`, `used_at`) VALUES
(2, 4, '97f4eac631ddb156be84fa932aa68f7afd3f7f14949b9c7bf2586b6e19771f9f', NULL, '2025-10-27 10:32:29', '2025-10-27 10:37:29', 1, '2025-10-27 10:32:29'),
(3, 4, 'd0e2c0d229751f4908ddb1150a70fa86fc5bab406a97f32696ba242d7599f817', NULL, '2025-10-27 10:46:43', '2025-10-27 10:51:43', 1, '2025-10-27 10:46:43'),
(4, 4, '2f7bfc549a4264decf857e4871ada7ee5f13da3e053d6c99dc66df02248be20c', NULL, '2025-10-27 11:33:38', '2025-10-27 11:38:38', 1, '2025-10-27 11:33:38'),
(5, 2, 'cef3bb4f8600cd0fa70b6f103299e1a7b4494ad0158ac89d341f6f97a868158b', NULL, '2025-10-27 13:04:58', '2025-10-27 13:09:58', 1, '2025-10-27 13:04:58'),
(6, 2, 'a2a110b1286b8b2997f396533d933d56d4841b1919863055c9913fbb7c973e02', NULL, '2025-10-27 17:07:29', '2025-10-27 17:12:29', 1, '2025-10-27 17:07:29'),
(7, 2, 'af1887df30259f6ddd7970cfa8f10f720b7eb43d9acd165dedbf792dc0692834', NULL, '2025-10-27 19:26:25', '2025-10-27 19:31:25', 1, '2025-10-27 19:26:25'),
(8, 1, '84fb70a64424344fda4489b36a94475b7bc1c574628d7d49545fcc49a8da6b4a', 2, '2025-10-27 20:12:48', '2025-10-27 20:17:48', 1, '2025-10-27 20:12:48'),
(9, 2, 'b3165e576b87373b7867967d33424bdc5b2de68fa92db913eb842575868442b3', 1, '2025-10-27 20:38:06', '2025-10-27 20:43:06', 1, '2025-10-27 20:38:07'),
(10, 2, 'e539128e774ba158310c9ffdb63bcae874bb58abff44225d162aed198cb0b3e5', NULL, '2025-10-28 07:33:48', '2025-10-28 07:38:48', 1, '2025-10-28 07:33:49'),
(11, 2, 'fd453b6a33e76bec62f8e1c0868a407c9aa21d5370cf371af3a21ca4fb4d0673', NULL, '2025-10-28 09:13:57', '2025-10-28 09:18:57', 1, '2025-10-28 09:13:57'),
(12, 2, 'e1903c37a6b060b5c627960dac0e35a8b71d60a9f0216612f6d4f72a696cf83e', NULL, '2025-10-28 11:37:29', '2025-10-28 11:42:29', 1, '2025-10-28 11:37:30'),
(13, 2, 'b0ef234d993bcbd6eb860dd94d1c8b65feed65b522c4716cf5bcd60a4b50506b', NULL, '2025-10-29 08:44:22', '2025-10-29 08:49:22', 1, '2025-10-29 08:44:22'),
(14, 4, '38705ed192218d3ab36dba457f6c2f60601903d814033752e4f46ca31a94fc3e', 2, '2025-10-29 20:29:51', '2025-10-29 15:04:51', 0, NULL),
(15, 4, '45847e693c2a3e26febf32896d8f2510d2223cd32c3d6605afbe4a637afbcabc', 2, '2025-10-29 20:31:10', '2025-10-29 15:06:10', 0, NULL),
(16, 4, '88974cee91c7e0d6507da0a953eaef91b0ae4d10d851585adeb2084104ae1412', 2, '2025-10-29 20:31:56', '2025-10-29 15:06:56', 1, '2025-10-29 20:33:43'),
(17, 2, '155d9a908fc01eddac6fadcc7e801222ada7f6324c973a1159c400eacf829fbd', 2, '2025-10-29 21:03:19', '2025-10-29 15:38:19', 0, NULL),
(18, 4, '84c5b706078897fd563193126a2442db1e66ee4fb7e6cc52ba477e9d3bda9b21', 2, '2025-10-31 23:14:25', '2025-10-31 17:49:25', 0, NULL),
(19, 2, 'e70a1d1cf94ce996e0730fe7a54d8cc31dac881cb6e5fec9b16feb45df834c06', 2, '2025-10-31 23:19:54', '2025-10-31 17:54:54', 1, '2025-10-31 23:20:13'),
(20, 2, '6ee2820b490206eb04702720a0b6750163a7e3d31ca7880375f373e3faecb401', 2, '2025-10-31 23:58:07', '2025-10-31 18:33:07', 1, '2025-10-31 23:59:02'),
(21, 2, '65725db00fb2cb7858cca26ca5180d49856d11ad6b799b8f64f4d781e17fa17f', 2, '2025-11-07 22:46:42', '2025-11-07 17:21:42', 0, NULL),
(22, 2, '37c220b16a1d568f344a1419930e576d6eb465965e905b2c4be022d62f95da11', 2, '2025-11-07 22:46:53', '2025-11-07 17:21:53', 1, '2025-11-07 22:47:08'),
(23, 4, '581670114a574b2e75847e8c78ae225a3062e14c125957a31549c8e407f648ce', 2, '2025-11-07 23:10:55', '2025-11-07 17:45:55', 1, '2025-11-07 23:11:11'),
(24, 4, 'c9873dac29b2cd8dfaad488b7dac13a9933df1d80f669d3fba11e466398ba4b1', NULL, '2025-11-07 23:15:11', '2025-11-07 17:50:11', 0, NULL),
(25, 4, '2422b49c439556a166c6e2a3c30a787e8d96146d156ad2a83c8e9ca7bb52b7bb', NULL, '2025-11-07 23:16:35', '2025-11-07 17:51:35', 1, '2025-11-07 23:16:35'),
(26, 4, 'c230bb818125e8df2bb5c6952132ba0225759b8c5081d2024069398bc529a55d', 4, '2025-11-07 23:17:01', '2025-11-07 17:52:01', 0, NULL),
(27, 4, 'bcee36f1d88f4d7edaff9a34107757224680eaaaba275f19f57a69b21c977a07', 4, '2025-11-07 23:17:28', '2025-11-07 17:52:28', 1, '2025-11-07 23:17:28'),
(28, 1, '73b00b35935041d78a3089f18d2d427334bed0f88414d41728f268b4c919dc93', NULL, '2025-11-07 23:19:59', '2025-11-07 17:54:59', 0, NULL),
(29, 4, '5812dd799b38d6cc92d7d91d3daaba076facda9dd5a054377059e5595b838f02', NULL, '2025-11-07 23:20:36', '2025-11-07 17:55:36', 0, NULL),
(30, 4, '723d5122de276c47718f05d494b586a8eb22f9f108b6c0479e9729adcab3a2aa', NULL, '2025-11-07 23:21:23', '2025-11-07 17:56:23', 1, '2025-11-07 23:21:23'),
(31, 4, '6a1f15d0bad9df07aff0a933077d0744a1e2d025a60052ea7226167cbdcf8fbb', 4, '2025-11-07 23:22:05', '2025-11-07 17:57:05', 1, '2025-11-07 23:22:05');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
CREATE TABLE IF NOT EXISTS `customers` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'all phone numbers',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `whatsapp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `industry` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `agent_id`, `name`, `company`, `phones`, `phone`, `whatsapp`, `email`, `industry`, `address`, `city`, `state`, `country`, `website`, `services`, `google_rating`, `favourite`, `archived`, `type`, `photo`, `photo1`, `timezone`, `source`, `enable_email`, `enable_whatsapp`, `fil_domains`, `fil_emails`, `created_at`, `updated_at`) VALUES
(1, '2', 'Customer 1', 'Company 1', '[\"9876453663\"]', '9876453663', NULL, 'customer1@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Work in progress', 'vaOu2EqIY0s7y8aLJHr4FYsZXQNtn3ZddS7gzSvM251104114045.jpg', NULL, NULL, 'from excel', 0, 0, NULL, 'customer1@gmail.com', '2025-11-04 17:10:46', NULL),
(2, '2', 'Customer 2', 'Company 2', '[\"7834566345\"]', '7834566345', NULL, 'customer2xx@gmail.com', 'Pipes and Fittings ', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Active', 'tqnvdBWlmcQvsg7T4a93AUUHb4bIc3p57Rubw0dk251106062146.jpg', 'O7yYpk1m9DndwEAsEGbtmLuXhjnGRoordt4FWMOB251107092105.jpg', NULL, 'from excel', 0, 0, NULL, 'customer2xx@gmail.com', '2025-11-04 17:16:13', NULL),
(3, '2', 'Customer 3', 'Company 3', '[\"7636726354\"]', '7636726354', NULL, 'customer3@gmail.com', 'New Industry', NULL, NULL, NULL, NULL, NULL, NULL, '', 0, 0, 'Suspect', 'josnNCMJV79TFjrWIfOndoqndLAH0FIDaEssldm6251105080622.jpg', NULL, NULL, 'from excel', 0, 0, NULL, 'customer3@gmail.com', '2025-11-05 13:36:23', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers_contacts`
--

DROP TABLE IF EXISTS `customers_contacts`;
CREATE TABLE IF NOT EXISTS `customers_contacts` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `photo1` text COLLATE utf8mb4_general_ci,
  `photo2` text COLLATE utf8mb4_general_ci,
  `designation` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_contacts`
--

INSERT INTO `customers_contacts` (`id`, `customer_id`, `name`, `phone`, `email`, `photo1`, `photo2`, `designation`, `created_by`, `created_at`) VALUES
(1, 2, 'Joice1', '2364562364', 'joice@gmail.com', NULL, NULL, 'Manager', 'Nithin', '2025-11-05 10:20:28'),
(2, 2, 'Nithin', '32784788488', 'nithin@gmail.com', '1762538590_bc-16.jpg', '1762538647_bc-11.jpg', 'Head', 'Nithin', '2025-11-05 10:21:14'),
(3, 2, 'tectc', '2364632', 'tetsh@jsdfhgf.fdg', NULL, NULL, 'Des', 'Nithin', '2025-11-07 13:00:44');

-- --------------------------------------------------------

--
-- Table structure for table `customers_documents`
--

DROP TABLE IF EXISTS `customers_documents`;
CREATE TABLE IF NOT EXISTS `customers_documents` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `label` varchar(150) COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `file_type` enum('pdf','image') COLLATE utf8mb4_general_ci NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_documents`
--

INSERT INTO `customers_documents` (`id`, `customer_id`, `label`, `file_name`, `file_type`, `expiry_date`, `created_by`, `created_at`) VALUES
(1, 2, 'Experience', '1762338667_sample.pdf', 'pdf', NULL, 'Nithin', '2025-11-05 10:31:07'),
(2, 2, 'Residence ID', '1762354987_Sample-png-Image-for-Testing.png', 'image', NULL, 'Nithin', '2025-11-05 15:03:07'),
(4, 2, 'Test ID', '1762441601_Sample.png', 'image', '2025-11-28', 'Nithin', '2025-11-06 15:06:41'),
(6, 2, 'idcard', '1762519750_bc-15.jpg', 'image', NULL, 'Nithin', '2025-11-07 12:49:10');

-- --------------------------------------------------------

--
-- Table structure for table `customers_invoices`
--

DROP TABLE IF EXISTS `customers_invoices`;
CREATE TABLE IF NOT EXISTS `customers_invoices` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `invoice_date` date NOT NULL,
  `payment_status` enum('Paid','Unpaid','Partial Paid') COLLATE utf8mb4_general_ci DEFAULT 'Unpaid',
  `document` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `notes` text COLLATE utf8mb4_general_ci,
  `created_by` int DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_invoices`
--

INSERT INTO `customers_invoices` (`id`, `customer_id`, `invoice_date`, `payment_status`, `document`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-11-14', 'Unpaid', 'inv_690df81d1a6ce.pdf', 'test', 2, '2025-11-07 19:16:05', '2025-11-07 19:16:05'),
(2, 2, '2025-11-01', 'Unpaid', '', 'no file invoice', 2, '2025-11-07 19:37:14', '2025-11-07 19:37:14'),
(3, 3, '2025-11-14', 'Paid', 'inv_690e2ed842edd.pdf', 'this is test invoice', 2, '2025-11-07 23:09:21', '2025-11-07 23:09:36');

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
  `type` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'General' COMMENT 'Call,Email,Meeting,General',
  `visibility` varchar(20) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'Public' COMMENT 'Private,Public',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_logs`
--

INSERT INTO `customers_logs` (`id`, `customer_id`, `agent_id`, `name`, `notes`, `type`, `visibility`, `created_at`) VALUES
(1, 2, NULL, 'Nithin', 'this sis a test note1', 'General', 'Public', '2025-11-05 08:42:17'),
(2, 2, NULL, 'Nithin', 'this sis a test note1', 'General', 'Public', '2025-11-05 08:45:18'),
(3, 2, NULL, 'Nithin', 'Status changed from \'Inactive\' to \'Active\'.\n\nNote: this is a test note223', 'General', 'Public', '2025-11-05 09:18:21'),
(4, 2, NULL, 'Nithin', 'Status changed from \'Active\' to \'Inactive\'.\n\nNote: test', 'General', 'Public', '2025-11-05 09:44:55'),
(5, 2, NULL, 'Nithin', 'Status changed from \'Inactive\' to \'Active\'.\n\nNote: test note838', 'General', 'Public', '2025-11-06 01:12:50'),
(6, 2, NULL, 'Nithin', 'Email Feature changed from \'Disabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 01:36:31'),
(7, 2, NULL, 'Nithin', 'WhatsApp Featuer changed from \'Disabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 01:36:31'),
(8, 2, NULL, 'Nithin', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-07 01:59:24'),
(9, 2, NULL, 'Nithin', 'Email Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 02:02:37'),
(10, 2, NULL, 'Nithin', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-07 02:02:43'),
(11, 2, NULL, 'Nithin', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-07 02:02:43'),
(12, 2, NULL, 'Nithin', 'Email Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 02:02:50'),
(13, 2, NULL, 'Nithin', 'WhatsApp Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 02:02:50'),
(14, 2, NULL, 'Nithin', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-07 02:04:27'),
(15, 2, NULL, 'Nithin', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-07 02:04:32'),
(16, 2, NULL, 'Nithin', 'Email Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 02:04:40'),
(17, 2, NULL, 'Nithin', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'Meeting', 'Public', '2025-11-07 02:04:49'),
(18, 2, NULL, 'Nithin', 'Email Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 02:04:55'),
(19, 2, NULL, 'Nithin', 'WhatsApp Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 02:05:34'),
(20, 2, NULL, 'Nithin', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-07 04:05:25'),
(21, 2, NULL, 'Nithin', 'Email Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 04:05:33'),
(22, 2, NULL, 'Nithin', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-07 04:05:33'),
(23, 2, NULL, 'Nithin', 'WhatsApp Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-07 04:05:38'),
(24, 2, 2, 'Nithin', 'test6636privatex', 'Meeting', 'Private', '2025-11-07 06:19:29'),
(25, 2, 2, 'Nithin', 'testprivate636', 'Call', 'Private', '2025-11-07 06:53:01'),
(26, 3, 2, 'Nithin1', 'testprivate1', 'Email', 'Private', '2025-11-07 12:10:31'),
(27, 3, 2, 'Nithin1', 'testpublic1', 'Meeting', 'Public', '2025-11-07 12:10:44'),
(28, 3, 4, 'Naren Jayabalu', 'Test note22', 'General', 'Public', '2025-11-07 12:25:40'),
(29, 2, 4, 'Naren Jayabalu', 'This is a test note774', 'Call', 'Public', '2025-11-07 12:25:59'),
(30, 1, NULL, 'Naren Jayabalu', 'Status changed from \'Active\' to \'Work in progress\'.\n\nNote: tetsttst', 'General', 'Public', '2025-11-07 12:26:58');

-- --------------------------------------------------------

--
-- Table structure for table `customers_quotations`
--

DROP TABLE IF EXISTS `customers_quotations`;
CREATE TABLE IF NOT EXISTS `customers_quotations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `ref_no` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `version` int DEFAULT '1',
  `quotation_name` varchar(40) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `quotation_date` date DEFAULT NULL,
  `attention` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `subject` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `message` text COLLATE utf8mb4_general_ci,
  `jobs_json` json DEFAULT NULL,
  `terms_json` json DEFAULT NULL,
  `closing` text COLLATE utf8mb4_general_ci,
  `status` enum('draft','final') COLLATE utf8mb4_general_ci DEFAULT 'draft',
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_quotations`
--

INSERT INTO `customers_quotations` (`id`, `customer_id`, `ref_no`, `version`, `quotation_name`, `quotation_date`, `attention`, `subject`, `message`, `jobs_json`, `terms_json`, `closing`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, 'QTN2511305', 1, 'Quotation', '2025-11-05', 'HR Manager', 'QUOTATION FOR MAN POWER SUPPLY', 'Dear Sir,\r\n\r\nWe thank and acknowledge the receipt of your mail and are pleased to quote below our most competitive rates as follows: -', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}]', '[]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always.\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 12:56:27', '2025-11-06 11:37:13'),
(2, 2, 'QTN2511305', 2, 'Quotation', '2025-11-05', 'HR Manager', 'QUOTATION FOR MAN POWER SUPPLY1', 'Dear Sir,\r\n\r\nWe thank and acknowledge the receipt of your mail and are pleased to quote below our most competitive rates as follows: -', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}]', '[]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always.\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 12:57:13', '2025-11-06 11:37:13'),
(3, 2, 'QTN2511305', 3, 'Quotation', '2025-11-05', 'HR Manager', 'QUOTATION FOR MAN POWER SUPPLY2', 'Dear Sir,\r\n\r\nWe thank and acknowledge the receipt of your mail and are pleased to quote below our most competitive rates as follows: -', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}]', '[]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always.\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 13:47:13', '2025-11-06 11:37:13'),
(4, 2, 'QTN2511305', 4, 'Quotation', '0000-00-00', '', '', '', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[]', '', 'draft', 'Nithin', '2025-11-05 13:49:09', '2025-11-06 11:37:13'),
(5, 2, 'QTN2511305', 5, 'Quotation', '2025-11-05', 'Hr Manager', 'Subject22', 'This is a message', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)1\", \"title\": \"New Termxx\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always.\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 14:08:16', '2025-11-06 11:37:13'),
(6, 2, 'QTN2511305', 6, 'Quotation', '2025-11-05', 'HR', 'Test SUbject3', 'This is a teast messghavjhsgf673\r\nsdfuisdygifusdhuifsd', '[{\"rate_pay\": \"13.99\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated\", \"title\": \"New Termx\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 14:34:23', '2025-11-06 11:37:13'),
(7, 2, 'QTN2511305', 7, 'Quotation', '2025-11-05', 'HR', 'test SUbjec6736', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 14:40:00', '2025-11-06 11:37:13'),
(8, 2, 'QTN2511305', 8, 'Quotation', '0000-00-00', 'HR', 'test SUbjec6736', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract22\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 15:31:47', '2025-11-06 11:37:13'),
(9, 2, 'QTN2511305', 9, 'Quotation', '2025-11-05', '', 'sdfsdf', 'sdfsdfsdf', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', '', 'draft', 'Nithin', '2025-11-05 15:32:41', '2025-11-06 11:37:13'),
(10, 2, 'QTN2511305', 10, 'Quotation', '2025-11-05', 'HR', 'test SUbjec6736', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract22\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 15:40:31', '2025-11-06 11:37:13'),
(11, 2, 'QTN2511305', 11, 'Quotation', '2025-11-05', 'HR', 'test SUbjec6736updated', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract22u\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22up\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-05 15:50:17', '2025-11-06 11:37:13'),
(12, 2, 'QTN2511305', 12, 'Quotation', '2025-11-06', 'HR', 'test SUbjec6736updated', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract22u\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22up\"}, {\"text\": \"description jhsdbfjhb34\", \"title\": \"New Termx\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-06 05:04:26', '2025-11-06 11:37:13'),
(13, 2, 'QTN2511305', 13, 'Quotation', '2025-11-06', 'Hr5324', 'Subject366', 'Thsis is a  essahggjwegty3 n', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', '', 'draft', 'Nithin', '2025-11-06 05:26:15', '2025-11-06 11:37:13'),
(14, 2, 'QTN2511305', 14, 'Quotation', '2025-11-06', 'Hr5324', 'Subject366', 'Thsis is a  essahggjwegty3 n11', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', '', 'draft', 'Nithin', '2025-11-06 11:09:08', '2025-11-06 11:37:13'),
(15, 2, 'QTN2511062891', 1, 'Quotation', '2025-11-06', 'HR', 'test subejct3367', 'this sis meshdjsdgufi364897234 \r\nSDUFGUYSD', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)new\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"test727278\", \"title\": \"New Term1\"}]', 'this is closing messagejdskjfksd\r\ntest7373', 'draft', 'Nithin', '2025-11-06 11:15:15', '2025-11-06 11:37:13'),
(16, 2, 'QTN2511305', 16, 'Quotation', '2025-11-06', 'HR', 'test SUbjec6736updated', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract22u\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22up\"}, {\"text\": \"description jhsdbfjhb34\", \"title\": \"New Termx\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-06 11:17:38', '2025-11-06 11:37:13'),
(17, 2, 'QTN2511305', 17, 'Quotation', '2025-11-06', 'Hr5324', 'Subject366112', 'Thsis is a  essahggjwegty3 n11', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', '', 'draft', 'Nithin', '2025-11-06 11:25:37', '2025-11-06 11:37:13'),
(18, 2, 'QTN2511305', 18, 'Quotation', '2025-11-06', 'Hr5324', 'Subject3661121xx', 'Thsis is a  essahggjwegty3 n11x', '[{\"rate_pay\": \"14.01\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', '', 'draft', 'Nithin', '2025-11-06 11:29:43', '2025-11-06 11:37:13'),
(19, 2, 'QTN2511305', 19, NULL, '2025-11-06', 'Hr5324', 'Subject3661121xx', 'Thsis is a  essahggjwegty3 n11x', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"25.00\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'This is a closing message3883', 'draft', 'Nithin', '2025-11-06 11:51:24', '2025-11-06 11:51:24'),
(20, 2, 'QTN2511305', 20, 'QuotationY', '2025-11-06', 'HR', 'test SUbjec6736updated', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract22u\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22up\"}, {\"text\": \"description jhsdbfjhb34\", \"title\": \"New Termx\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-06 11:53:10', '2025-11-06 11:53:10'),
(21, 2, 'QTN2511062820', 1, 'Test Quotationw', '2025-11-06', 'Manager', 'This is a subject', 'This si smesahguy23ut64', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation', 'draft', 'Nithin', '2025-11-06 11:54:48', '2025-11-06 11:54:48'),
(22, 2, 'QTN2511062820', 2, 'Test Quotationwe', '2025-11-06', 'Manager', 'This is a subject', 'This si smesahguy23ut64q', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation22', 'draft', 'Nithin', '2025-11-06 12:00:55', '2025-11-06 12:00:55'),
(23, 2, 'QTN2511062891', 2, 'QuotationUP', '2025-11-06', 'HR', 'test subejct3367', 'this sis meshdjsdgufi364897234 \r\nSDUFGUYSD33', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)new\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"test727278\", \"title\": \"New Term1\"}]', 'this is closing messagejdskjfksd\r\ntest7373', 'draft', 'Nithin', '2025-11-06 13:20:05', '2025-11-06 13:20:05'),
(24, 2, 'QTN2511305', 21, 'QuotationR', '2025-11-06', 'HR', 'test SUbjec6736223', 'This is new messagah3563h', '[{\"rate_pay\": \"13.98\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.98\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)updated2\", \"title\": \"Duration of Contract22\"}, {\"text\": \"Applicable 5% on Invoice Amount\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}, {\"text\": \"this is term description22\", \"title\": \"New Term22\"}]', 'We hope our proposal will have your favourable consideration and look forward to your reply.\r\nAssuring you our best services and prompt attention always. [updated2]\r\n\r\nThanks & Regards\r\nFor M/s Al Nasr General Services Est\r\n\r\nDILEEP JOSEPH\r\nManager\r\n301, Sohail Mazroui Bldg,\r\nSalam Street, Abu Dhabi,\r\nMob: +971 58 55 69491\r\nsales@nasruae.com\r\nwww.nasruae.com', 'draft', 'Nithin', '2025-11-06 13:44:03', '2025-11-06 13:44:03'),
(25, 2, 'QTN2511062820', 3, 'Test Quotationwe', '2025-11-06', 'Manager', 'This is a subject', 'This si smesahguy23ut64q', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation22', 'draft', 'Nithin', '2025-11-06 13:44:23', '2025-11-06 13:44:23'),
(26, 2, 'QTN2511062820', 4, 'Test Quotationwe', '2025-11-07', 'Manager', 'This is a subjectnew3', 'This si smesahguy23ut64q', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation22', 'draft', 'Nithin', '2025-11-07 12:43:16', '2025-11-07 12:43:16');

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
) ENGINE=InnoDB AUTO_INCREMENT=65 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_reminders`
--

INSERT INTO `customers_reminders` (`id`, `customer_id`, `reminder_at`, `type`, `contact_id`, `note`, `created_at`, `updated_at`, `completed`) VALUES
(56, 3, '2025-11-12 10:00:00', 'General', NULL, 'reminder333', '2025-11-05 13:36:23', '2025-11-07 14:41:34', 0),
(57, 2, '2025-11-12 10:00:00', 'General', NULL, 'test remidner1', '2025-11-05 09:34:56', '2025-11-07 14:41:34', 0),
(58, 2, '2025-11-12 10:00:00', 'General', NULL, 'test remidner2', '2025-11-05 09:37:21', '2025-11-07 14:41:34', 0),
(59, 2, '2025-11-06 10:00:00', 'General', NULL, 'test remidner3', '2025-11-05 09:37:57', '2025-11-07 14:41:34', 0),
(60, 2, '2025-11-14 10:00:00', 'Email', 1, 'test remidner334', '2025-11-07 10:13:35', '2025-11-07 15:47:29', 0),
(61, 2, '2025-11-08 10:00:00', 'Call', 1, 'tesgh66211', '2025-11-07 10:17:15', NULL, 0),
(62, 2, '2025-11-14 10:00:00', 'Call', 1, 'te63563', '2025-11-07 10:17:43', '2025-11-07 15:50:08', 0),
(63, 2, '2025-11-14 10:00:00', 'Call', 1, 'trtr55', '2025-11-07 10:18:49', '2025-11-07 15:50:10', 0),
(64, 2, '2025-11-14 10:00:00', 'Email', 2, 'ytuytu89xt1', '2025-11-07 10:19:57', '2025-11-07 11:17:15', 0);

-- --------------------------------------------------------

--
-- Table structure for table `customers_requirements`
--

DROP TABLE IF EXISTS `customers_requirements`;
CREATE TABLE IF NOT EXISTS `customers_requirements` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `job_title` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `num_employees` int NOT NULL,
  `rate_pay` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `accommodation` tinyint(1) DEFAULT '0',
  `transport` tinyint(1) DEFAULT '0',
  `overtime` tinyint(1) DEFAULT '0',
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `attachment` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `accommodation_details` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `transport_details` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `overtime_policies` json DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_requirements`
--

INSERT INTO `customers_requirements` (`id`, `customer_id`, `job_title`, `num_employees`, `rate_pay`, `start_date`, `accommodation`, `transport`, `overtime`, `created_by`, `created_at`, `attachment`, `accommodation_details`, `transport_details`, `overtime_policies`) VALUES
(1, 2, 'General Helper', 2, 14.01, '2025-11-06', 1, 0, 0, 'Nithin', '2025-11-05 10:06:58', NULL, NULL, NULL, NULL),
(2, 2, 'Security Guard', 6, 25.00, '2025-11-13', 1, 0, 0, 'Nithin', '2025-11-05 10:08:05', NULL, NULL, NULL, NULL),
(3, 2, 'Construction Worker', 5, 25.00, '2025-10-29', 1, 1, 1, 'Nithin', '2025-11-07 09:06:22', 'req_690e03e69abdb.pdf', 'adetails', 'tdetails', '[{\"rate\": \"29\", \"policy\": \"p1\"}, {\"rate\": \"23\", \"policy\": \"p2\"}]'),
(4, 2, 'Test Job', 5, 25.00, '2025-10-26', 1, 0, 1, 'Nithin', '2025-11-07 09:25:42', 'req_690e13ac368de.jpg', 'adetails', '', '[{\"rate\": \"29\", \"policy\": \"p1\"}, {\"rate\": \"23\", \"policy\": \"p2\"}]'),
(5, 2, 'General Helper', 1, 4.00, '0000-00-00', 1, 1, 1, 'Nithin', '2025-11-07 10:21:25', 'req_690e157d52b9c.jpg', '', '', '[{\"rate\": \"4\", \"policy\": \"p1\"}]'),
(6, 2, 'Tets65', 2, 25.00, '2025-11-03', 0, 1, 0, 'Nithin', '2025-11-07 10:21:25', 'req_690e157d5579c.pdf', '', '', '[{\"rate\": \"4\", \"policy\": \"p1\"}]'),
(7, 3, 'Construction Worker', 10, 1.00, '2025-11-09', 1, 1, 1, 'Nithin1', '2025-11-07 11:58:36', 'req_690e2c4434fa1.png', '', '', '[{\"rate\": \"19\", \"policy\": \"p1\"}]'),
(8, 3, 'General Helper', 20, 2.00, '2025-11-05', 0, 0, 0, 'Nithin1', '2025-11-07 11:58:36', 'req_690e2c4437fe9.pdf', '', '', '[{\"rate\": \"19\", \"policy\": \"p1\"}]'),
(9, 3, 'Security Guard', 1, 2.00, '2025-11-11', 1, 1, 0, 'Nithin1', '2025-11-07 12:04:11', '', '', 'tr', NULL),
(10, 3, 'Test', 2, 4.00, '2025-11-12', 0, 0, 0, 'Nithin1', '2025-11-07 12:04:11', 'req_690e2d93ca5f5.png', 'acc', '', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `customers_sites`
--

DROP TABLE IF EXISTS `customers_sites`;
CREATE TABLE IF NOT EXISTS `customers_sites` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `site_name` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `site_contact` varchar(150) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `site_address` text COLLATE utf8mb4_general_ci,
  `site_location` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_by` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_sites`
--

INSERT INTO `customers_sites` (`id`, `customer_id`, `site_name`, `site_contact`, `site_address`, `site_location`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 2, 'Site 1', 'contact1', 'address of site1', 'location1', 'Admin', '2025-11-06 15:56:35', '2025-11-06 16:02:31'),
(6, 2, 'Site2 Name', 'sitecontat2', 'site 2 address', 'location 2', 'Admin', '2025-11-06 15:56:43', '2025-11-06 16:03:35'),
(9, 2, 'Site 3', 'contact 3', 'this is address of site3', 'Location 3', 'Admin', '2025-11-06 15:58:55', '2025-11-06 16:03:22'),
(11, 2, 'name', 'cotnact', 'test address', '', 'Admin', '2025-11-07 04:39:08', NULL),
(12, 2, 'new 23', 'cotnact233', 'adaredss', '', 'Admin', '2025-11-07 04:39:46', '2025-11-07 04:39:56');

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
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb3;

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
(11, '4', '2', '2025-11-07', 'Active', 1, 0, 1);

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `email_attachments`
--

INSERT INTO `email_attachments` (`id`, `email_id`, `filename`, `path`, `size`, `mime`, `created_at`) VALUES
(1, 3, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_1/1761326643_122ca56939_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:03'),
(2, 6, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_1/1761326643_c0a4e59bcd_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:03'),
(3, 7, 'WhatsApp Image 2025-10-16 at 2.20.47 PM.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_1/1761326643_66525ac827_WhatsApp_Image_2025-10-16_at_2.20.47_PM.jpeg', 75550, 'OCTET-STREAM', '2025-10-24 18:24:03'),
(4, 17, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_2/1761326646_2ef4246318_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:06'),
(5, 20, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_2/1761326646_c5768f2fb7_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:06'),
(6, 23, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_2/1761326647_c4e1e420c2_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:07'),
(7, 24, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_2/1761326647_83b48c9ec2_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:07'),
(8, 26, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_2/1761326648_1aa9f59161_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:08'),
(9, 30, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_2/1761326649_48df933df4_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:09'),
(10, 34, 'terminal.jpeg', '/var/www/vhosts/crmcust8.jrjapp.com/httpdocs/inbound/../storage/mail_attachments/mb_2/1761326650_ca6f51c506_terminal.jpeg', 165118, 'JPEG', '2025-10-24 18:24:10'),
(11, 2, 'terminal.jpeg', 'C:\\wamp\\www\\jrj\\crm-erp\\inbound/../storage/mail_attachments/mb_2/\\1762536004_c3674d36ba_terminal.jpeg', 165118, 'JPEG', '2025-11-07 22:50:04'),
(12, 5, 'terminal.jpeg', 'C:\\wamp\\www\\jrj\\crm-erp\\inbound/../storage/mail_attachments/mb_2/\\1762536007_503f5590b8_terminal.jpeg', 165118, 'JPEG', '2025-11-07 22:50:07'),
(13, 8, 'terminal.jpeg', 'C:\\wamp\\www\\jrj\\crm-erp\\inbound/../storage/mail_attachments/mb_2/\\1762536010_39f2809aea_terminal.jpeg', 165118, 'JPEG', '2025-11-07 22:50:10'),
(14, 9, 'terminal.jpeg', 'C:\\wamp\\www\\jrj\\crm-erp\\inbound/../storage/mail_attachments/mb_2/\\1762536011_002488e605_terminal.jpeg', 165118, 'JPEG', '2025-11-07 22:50:11'),
(15, 11, 'terminal.jpeg', 'C:\\wamp\\www\\jrj\\crm-erp\\inbound/../storage/mail_attachments/mb_2/\\1762536013_09010cdee7_terminal.jpeg', 165118, 'JPEG', '2025-11-07 22:50:13'),
(16, 15, 'terminal.jpeg', 'C:\\wamp\\www\\jrj\\crm-erp\\inbound/../storage/mail_attachments/mb_2/\\1762536017_b76ee6925e_terminal.jpeg', 165118, 'JPEG', '2025-11-07 22:50:17');

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `email_log`
--

INSERT INTO `email_log` (`id`, `mailbox_id`, `folder`, `message_id`, `in_reply_to`, `references_chain`, `subject`, `from_name`, `from_email`, `to_emails`, `cc`, `bcc`, `body_html`, `body_text`, `is_sent`, `sent_via`, `thread_id`, `created_at`) VALUES
(1, 1, 'Inbox', '', NULL, NULL, 'Testin', 'naren', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', '', NULL, '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', 0, 'primary', '', '2025-10-22 06:28:07'),
(2, 2, 'Inbox', '<CANwJKfHOtnsOU=GDEehg6yRR69deH5UkWE8OkGU30G+KT9bZBw@mail.gmail.com>', NULL, NULL, 'attach 13', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi<br><br>attach 13</div>', 'Hi\r\n\r\nattach 13', 0, 'primary', '<CANwJKfHOtnsOU=GDEehg6yRR69deH5UkWE8OkGU30G+KT9bZBw@mail.gmail.com>', '2025-10-23 09:32:42'),
(3, 2, 'Inbox', '<CANwJKfFg3YAkYG=+=b-ceZSgsWz4E5wm-+NNBe-GRyxz_ucy-w@mail.gmail.com>', NULL, NULL, 'attach 12', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi<br>attach 12</div>', 'Hi\r\nattach 12', 0, 'primary', '<CANwJKfFg3YAkYG=+=b-ceZSgsWz4E5wm-+NNBe-GRyxz_ucy-w@mail.gmail.com>', '2025-10-23 09:31:05'),
(4, 2, 'Inbox', ' <PN3P287MB32436CB1072E4690CC44F193BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', NULL, NULL, 'attach11', 'Nithin P Jose', 'nj@jrjconnect.com', 'nithin@mediatel.com', '', NULL, '<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\r\n<style type=\"text/css\" style=\"display:none;\"> P {margin-top:0;margin-bottom:0;} </style>\r\n</head>\r\n<body dir=\"ltr\">\r\n<div class=\"elementToProof\" style=\"font-family: Aptos, Aptos_EmbeddedFont, Aptos_MSFontService, Calibri, Helvetica, sans-serif; font-size: 12pt; color: rgb(0, 0, 0);\">\r\nattach11</div>\r\n</body>\r\n</html>', 'P {margin-top:0;margin-bottom:0;} \r\n\r\n\r\n\r\nattach11', 0, 'primary', ' <PN3P287MB32436CB1072E4690CC44F193BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', '2025-10-23 09:20:43'),
(5, 2, 'Inbox', '<CANwJKfErDJt470LVk77p2KELY5SwiS3A+OsN6wPPjZ9LDJEyPA@mail.gmail.com>', NULL, NULL, 'attach4', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">attach4</div>', 'attach4', 0, 'primary', '<CANwJKfErDJt470LVk77p2KELY5SwiS3A+OsN6wPPjZ9LDJEyPA@mail.gmail.com>', '2025-10-23 09:02:39'),
(6, 2, 'Inbox', '<CANwJKfEzc4+36E9RM8Cor3vPXLPC8o7dxi-pGydoJ=2BpuG2xA@mail.gmail.com>', NULL, NULL, 'attach3', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">attach3</div>', 'attach3', 0, 'primary', '<CANwJKfEzc4+36E9RM8Cor3vPXLPC8o7dxi-pGydoJ=2BpuG2xA@mail.gmail.com>', '2025-10-23 09:02:21'),
(7, 2, 'Inbox', '<CANwJKfFH9RYoVJ-MYnD-r6yRU=1Reqma0Lz9K-W_Cb-yKbN3Zg@mail.gmail.com>', NULL, NULL, 'attachment2', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi <br><br>attach2</div>', 'Hi\r\n\r\nattach2', 0, 'primary', '<CANwJKfFH9RYoVJ-MYnD-r6yRU=1Reqma0Lz9K-W_Cb-yKbN3Zg@mail.gmail.com>', '2025-10-23 08:42:31'),
(8, 2, 'Inbox', '<CANwJKfHWDGGzZ1gdN3WhqY3ueAbg4Xn4nGPrL=Ua3VmvpsnTcw@mail.gmail.com>', NULL, NULL, 'attachment1', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi<div><br></div><div>attach 1</div></div>', 'Hi\r\n\r\nattach 1', 0, 'primary', '<CANwJKfHWDGGzZ1gdN3WhqY3ueAbg4Xn4nGPrL=Ua3VmvpsnTcw@mail.gmail.com>', '2025-10-23 08:39:15'),
(9, 2, 'Inbox', ' <PN3P287MB3243BA52EF2A24AEA6FDE5E9BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', NULL, NULL, 'contact test jrj to nithin with attach', 'Nithin P Jose', 'nj@jrjconnect.com', 'nithin@mediatel.com', '', NULL, '<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\r\n<style type=\"text/css\" style=\"display:none;\"> P {margin-top:0;margin-bottom:0;} </style>\r\n</head>\r\n<body dir=\"ltr\">\r\n<div class=\"elementToProof\" style=\"font-family: Aptos, Aptos_EmbeddedFont, Aptos_MSFontService, Calibri, Helvetica, sans-serif; font-size: 12pt; color: rgb(0, 0, 0);\">\r\nThis email from nithin jrj to nithin mediatel with attach</div>\r\n</body>\r\n</html>', 'P {margin-top:0;margin-bottom:0;} \r\n\r\n\r\n\r\nThis email from nithin jrj to nithin mediatel with attach', 0, 'primary', ' <PN3P287MB3243BA52EF2A24AEA6FDE5E9BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', '2025-10-23 04:55:13'),
(10, 2, 'Inbox', ' <PN3P287MB324333E496FFA507DC1CF76CBDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', NULL, NULL, 'Contact test to nithin mediatel', 'Nithin P Jose', 'nj@jrjconnect.com', 'nithin@mediatel.com', '', NULL, '<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\r\n<style type=\"text/css\" style=\"display:none;\"> P {margin-top:0;margin-bottom:0;} </style>\r\n</head>\r\n<body dir=\"ltr\">\r\n<div class=\"elementToProof\" style=\"font-family: Aptos, Aptos_EmbeddedFont, Aptos_MSFontService, Calibri, Helvetica, sans-serif; font-size: 12pt; color: rgb(0, 0, 0);\">\r\nThis mail is from nithin jrjconnect to nithin mediatel</div>\r\n</body>\r\n</html>', 'P {margin-top:0;margin-bottom:0;} \r\n\r\n\r\n\r\nThis mail is from nithin jrjconnect to nithin mediatel', 0, 'primary', ' <PN3P287MB324333E496FFA507DC1CF76CBDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', '2025-10-23 04:54:23'),
(11, 2, 'Inbox', '<CANwJKfGscabXC_f42SfHKW69OuP9zQn062+RcXTWFmbmhjutgg@mail.gmail.com>', NULL, NULL, 'contact test with attachement', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi,<div><br></div><div>this is email to mediatel mailbox of nithin from nithin gamil with attachement</div></div>', 'Hi,\r\n\r\nthis is email to mediatel mailbox of nithin from nithin gamil with\r\nattachement', 0, 'primary', '<CANwJKfGscabXC_f42SfHKW69OuP9zQn062+RcXTWFmbmhjutgg@mail.gmail.com>', '2025-10-23 04:53:39'),
(12, 2, 'Inbox', '<CANwJKfF+oZ1ihzaJ8DBr1yYKKAJR=jz4ykm08vjLwF0Ca2y6Dg@mail.gmail.com>', NULL, NULL, 'contact test to nithin mediatel', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi <div><br></div><div>this email to mediatel mailbox from nithin gmail</div></div>', 'Hi\r\n\r\nthis email to mediatel mailbox from nithin gmail', 0, 'primary', '<CANwJKfF+oZ1ihzaJ8DBr1yYKKAJR=jz4ykm08vjLwF0Ca2y6Dg@mail.gmail.com>', '2025-10-23 04:52:49'),
(13, 2, 'Inbox', '', NULL, NULL, 'Testin', 'naren', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', '', NULL, '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', 0, 'primary', '', '2025-10-22 06:28:07'),
(14, 2, 'Inbox', '<CANwJKfGaPTwqos5Ht==1F+WRE+5hJYGdeuS1jMu26WicPDAAtg@mail.gmail.com>', NULL, NULL, 'email 3 2210', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi<br><br>email 3 2210</div>', 'Hi\r\n\r\nemail 3 2210', 0, 'primary', '<CANwJKfGaPTwqos5Ht==1F+WRE+5hJYGdeuS1jMu26WicPDAAtg@mail.gmail.com>', '2025-10-22 03:23:34'),
(15, 2, 'Inbox', '<CANwJKfGjZKsDZwz3Wf5+2AC6hYz8MDFh-EWTJUCet8BKrfjp3A@mail.gmail.com>', NULL, NULL, 'test with attach', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">hi attached</div>', 'hi attached', 0, 'primary', '<CANwJKfGjZKsDZwz3Wf5+2AC6hYz8MDFh-EWTJUCet8BKrfjp3A@mail.gmail.com>', '2025-10-22 03:19:34'),
(16, 2, 'Inbox', '<CANwJKfFL-k=qgzBuvFPDg+A-+Da3rej+s50K5uX8cJtA-yeqQg@mail.gmail.com>', NULL, NULL, 'test mail from nithin', 'Nithin P Jose', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', '', NULL, '<div dir=\"ltr\">Hi<div><br></div><div>test</div></div>', 'Hi\r\n\r\ntest', 0, 'primary', '<CANwJKfFL-k=qgzBuvFPDg+A-+Da3rej+s50K5uX8cJtA-yeqQg@mail.gmail.com>', '2025-10-20 13:18:19'),
(17, 1, 'Inbox', '', NULL, NULL, 'Testin', 'naren', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', '', NULL, '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', 0, 'primary', '', '2025-10-22 06:28:07'),
(18, 2, 'Inbox', '', NULL, NULL, 'Testin', 'naren', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', '', NULL, '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', 0, 'primary', '', '2025-10-22 06:28:07');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

DROP TABLE IF EXISTS `employees`;
CREATE TABLE IF NOT EXISTS `employees` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'all phone numbers',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `whatsapp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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
  `fil_domains` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter domains comma seperated',
  `fil_emails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter emails comma seperated',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees_logs`
--

DROP TABLE IF EXISTS `employees_logs`;
CREATE TABLE IF NOT EXISTS `employees_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `employee_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees_reminders`
--

DROP TABLE IF EXISTS `employees_reminders`;
CREATE TABLE IF NOT EXISTS `employees_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `reminder_at` datetime NOT NULL,
  `type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'callback/followup/send quote etc',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `completed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inbound_emails`
--

INSERT INTO `inbound_emails` (`id`, `mailbox_id`, `created_at`, `sender`, `recipient`, `subject`, `body_html`, `body_text`, `message_id`, `in_reply_to`, `references_chain`, `thread_id`, `email_log_id`) VALUES
(1, 1, '2025-10-22 06:28:07', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', 'Testin', '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', NULL, NULL, NULL, NULL, 1),
(2, 2, '2025-10-23 09:32:42', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'attach 13', '<div dir=\"ltr\">Hi<br><br>attach 13</div>', 'Hi\r\n\r\nattach 13', '<CANwJKfHOtnsOU=GDEehg6yRR69deH5UkWE8OkGU30G+KT9bZBw@mail.gmail.com>', NULL, NULL, '<CANwJKfHOtnsOU=GDEehg6yRR69deH5UkWE8OkGU30G+KT9bZBw@mail.gmail.com>', 2),
(3, 2, '2025-10-23 09:31:05', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'attach 12', '<div dir=\"ltr\">Hi<br>attach 12</div>', 'Hi\r\nattach 12', '<CANwJKfFg3YAkYG=+=b-ceZSgsWz4E5wm-+NNBe-GRyxz_ucy-w@mail.gmail.com>', NULL, NULL, '<CANwJKfFg3YAkYG=+=b-ceZSgsWz4E5wm-+NNBe-GRyxz_ucy-w@mail.gmail.com>', 3),
(4, 2, '2025-10-23 09:20:43', 'nj@jrjconnect.com', 'nithin@mediatel.com', 'attach11', '<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\r\n<style type=\"text/css\" style=\"display:none;\"> P {margin-top:0;margin-bottom:0;} </style>\r\n</head>\r\n<body dir=\"ltr\">\r\n<div class=\"elementToProof\" style=\"font-family: Aptos, Aptos_EmbeddedFont, Aptos_MSFontService, Calibri, Helvetica, sans-serif; font-size: 12pt; color: rgb(0, 0, 0);\">\r\nattach11</div>\r\n</body>\r\n</html>', 'P {margin-top:0;margin-bottom:0;} \r\n\r\n\r\n\r\nattach11', ' <PN3P287MB32436CB1072E4690CC44F193BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', NULL, NULL, ' <PN3P287MB32436CB1072E4690CC44F193BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', 4),
(5, 2, '2025-10-23 09:02:39', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'attach4', '<div dir=\"ltr\">attach4</div>', 'attach4', '<CANwJKfErDJt470LVk77p2KELY5SwiS3A+OsN6wPPjZ9LDJEyPA@mail.gmail.com>', NULL, NULL, '<CANwJKfErDJt470LVk77p2KELY5SwiS3A+OsN6wPPjZ9LDJEyPA@mail.gmail.com>', 5),
(6, 2, '2025-10-23 09:02:21', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'attach3', '<div dir=\"ltr\">attach3</div>', 'attach3', '<CANwJKfEzc4+36E9RM8Cor3vPXLPC8o7dxi-pGydoJ=2BpuG2xA@mail.gmail.com>', NULL, NULL, '<CANwJKfEzc4+36E9RM8Cor3vPXLPC8o7dxi-pGydoJ=2BpuG2xA@mail.gmail.com>', 6),
(7, 2, '2025-10-23 08:42:31', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'attachment2', '<div dir=\"ltr\">Hi <br><br>attach2</div>', 'Hi\r\n\r\nattach2', '<CANwJKfFH9RYoVJ-MYnD-r6yRU=1Reqma0Lz9K-W_Cb-yKbN3Zg@mail.gmail.com>', NULL, NULL, '<CANwJKfFH9RYoVJ-MYnD-r6yRU=1Reqma0Lz9K-W_Cb-yKbN3Zg@mail.gmail.com>', 7),
(8, 2, '2025-10-23 08:39:15', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'attachment1', '<div dir=\"ltr\">Hi<div><br></div><div>attach 1</div></div>', 'Hi\r\n\r\nattach 1', '<CANwJKfHWDGGzZ1gdN3WhqY3ueAbg4Xn4nGPrL=Ua3VmvpsnTcw@mail.gmail.com>', NULL, NULL, '<CANwJKfHWDGGzZ1gdN3WhqY3ueAbg4Xn4nGPrL=Ua3VmvpsnTcw@mail.gmail.com>', 8),
(9, 2, '2025-10-23 04:55:13', 'nj@jrjconnect.com', 'nithin@mediatel.com', 'contact test jrj to nithin with attach', '<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\r\n<style type=\"text/css\" style=\"display:none;\"> P {margin-top:0;margin-bottom:0;} </style>\r\n</head>\r\n<body dir=\"ltr\">\r\n<div class=\"elementToProof\" style=\"font-family: Aptos, Aptos_EmbeddedFont, Aptos_MSFontService, Calibri, Helvetica, sans-serif; font-size: 12pt; color: rgb(0, 0, 0);\">\r\nThis email from nithin jrj to nithin mediatel with attach</div>\r\n</body>\r\n</html>', 'P {margin-top:0;margin-bottom:0;} \r\n\r\n\r\n\r\nThis email from nithin jrj to nithin mediatel with attach', ' <PN3P287MB3243BA52EF2A24AEA6FDE5E9BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', NULL, NULL, ' <PN3P287MB3243BA52EF2A24AEA6FDE5E9BDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', 9),
(10, 2, '2025-10-23 04:54:23', 'nj@jrjconnect.com', 'nithin@mediatel.com', 'Contact test to nithin mediatel', '<html>\r\n<head>\r\n<meta http-equiv=\"Content-Type\" content=\"text/html; charset=iso-8859-1\">\r\n<style type=\"text/css\" style=\"display:none;\"> P {margin-top:0;margin-bottom:0;} </style>\r\n</head>\r\n<body dir=\"ltr\">\r\n<div class=\"elementToProof\" style=\"font-family: Aptos, Aptos_EmbeddedFont, Aptos_MSFontService, Calibri, Helvetica, sans-serif; font-size: 12pt; color: rgb(0, 0, 0);\">\r\nThis mail is from nithin jrjconnect to nithin mediatel</div>\r\n</body>\r\n</html>', 'P {margin-top:0;margin-bottom:0;} \r\n\r\n\r\n\r\nThis mail is from nithin jrjconnect to nithin mediatel', ' <PN3P287MB324333E496FFA507DC1CF76CBDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', NULL, NULL, ' <PN3P287MB324333E496FFA507DC1CF76CBDF0A@PN3P287MB3243.INDP287.PROD.OUTLOOK.COM>', 10),
(11, 2, '2025-10-23 04:53:39', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'contact test with attachement', '<div dir=\"ltr\">Hi,<div><br></div><div>this is email to mediatel mailbox of nithin from nithin gamil with attachement</div></div>', 'Hi,\r\n\r\nthis is email to mediatel mailbox of nithin from nithin gamil with\r\nattachement', '<CANwJKfGscabXC_f42SfHKW69OuP9zQn062+RcXTWFmbmhjutgg@mail.gmail.com>', NULL, NULL, '<CANwJKfGscabXC_f42SfHKW69OuP9zQn062+RcXTWFmbmhjutgg@mail.gmail.com>', 11),
(12, 2, '2025-10-23 04:52:49', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'contact test to nithin mediatel', '<div dir=\"ltr\">Hi <div><br></div><div>this email to mediatel mailbox from nithin gmail</div></div>', 'Hi\r\n\r\nthis email to mediatel mailbox from nithin gmail', '<CANwJKfF+oZ1ihzaJ8DBr1yYKKAJR=jz4ykm08vjLwF0Ca2y6Dg@mail.gmail.com>', NULL, NULL, '<CANwJKfF+oZ1ihzaJ8DBr1yYKKAJR=jz4ykm08vjLwF0Ca2y6Dg@mail.gmail.com>', 12),
(13, 2, '2025-10-22 06:28:07', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', 'Testin', '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', NULL, NULL, NULL, NULL, 13),
(14, 2, '2025-10-22 03:23:34', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'email 3 2210', '<div dir=\"ltr\">Hi<br><br>email 3 2210</div>', 'Hi\r\n\r\nemail 3 2210', '<CANwJKfGaPTwqos5Ht==1F+WRE+5hJYGdeuS1jMu26WicPDAAtg@mail.gmail.com>', NULL, NULL, '<CANwJKfGaPTwqos5Ht==1F+WRE+5hJYGdeuS1jMu26WicPDAAtg@mail.gmail.com>', 14),
(15, 2, '2025-10-22 03:19:34', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'test with attach', '<div dir=\"ltr\">hi attached</div>', 'hi attached', '<CANwJKfGjZKsDZwz3Wf5+2AC6hYz8MDFh-EWTJUCet8BKrfjp3A@mail.gmail.com>', NULL, NULL, '<CANwJKfGjZKsDZwz3Wf5+2AC6hYz8MDFh-EWTJUCet8BKrfjp3A@mail.gmail.com>', 15),
(16, 2, '2025-10-20 13:18:19', 'nithinpjose1990@gmail.com', 'nithin@mediatel.com', 'test mail from nithin', '<div dir=\"ltr\">Hi<div><br></div><div>test</div></div>', 'Hi\r\n\r\ntest', '<CANwJKfFL-k=qgzBuvFPDg+A-+Da3rej+s50K5uX8cJtA-yeqQg@mail.gmail.com>', NULL, NULL, '<CANwJKfFL-k=qgzBuvFPDg+A-+Da3rej+s50K5uX8cJtA-yeqQg@mail.gmail.com>', 16),
(17, 1, '2025-10-22 06:28:07', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', 'Testin', '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', NULL, NULL, NULL, NULL, 17),
(18, 2, '2025-10-22 06:28:07', 'naren@mediatel.com', 'nithin@mediatel.com,dileep@mediatel.com', 'Testin', '<html><head><meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\"></head><body dir=\"auto\"><div><br></div><div><br></div><div><br></div><div><br></div><div id=\"composer_signature\"><div style=\"font-size:14px;color:#909090\" dir=\"auto\">Sent from my Galaxy</div></div><div><br></div></body></html>', 'Sent from my Galaxy', NULL, NULL, NULL, NULL, 18);

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
(1, 'Dileep', 'mail.mediatel.com', 'dileep@mediatel.com', 'JRJGlobal_123', 465, 'ssl', 'Inbox', 'Sent', 1, '2025-10-17 06:25:47', 'mail.mediatel.com', 'dileep_out', 'JRJGlobal_123', 993, 'ssl', 'INBOX', 1),
(2, 'Nithin', 'mail.mediatel.com', 'nithin@mediatel.com', 'JRJGlobal_321', 465, 'ssl', 'Inbox', 'Sent', 1, '2025-10-17 10:25:10', 'mail.mediatel.com', 'nithin_out', 'JRJGlobal_321', 993, 'ssl', 'INBOX', 2);

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
(1, 'Dileep', 'dileep@mediatel.com', '', 'staff', '2025-10-17 16:06:35', '$2y$12$qn443sAnDD3rsXWyjx1y4.IscsRMWHmuO9jBxezOInlc9zkEhljMm', NULL),
(2, 'Nithin1', 'nithin@mediatel.com', '', 'staff', '2025-10-17 16:07:04', '$2y$12$Jhfc8VmICnD3YncgyflMbeRZ38Y.rXYvxkY/ep38u6bXGCdoBXjFS', 'Y3vjDx1INbyFJ8Fuhsr321DBvdkJJY1TLvZycoRCpHejx5rxdHQsIg1oBYzO'),
(4, 'Naren Jayabalu', 'naren@mediatel.com', '', 'staff', '2025-10-27 10:17:19', '$2y$12$RzDM/bRtmvPhM/1WEdHFTOBSfrUQ2zj8Qpv2gjCy/ClhiISQATMXm', NULL);

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
  `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=234 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `people_logs`
--

INSERT INTO `people_logs` (`id`, `agent_id`, `admin`, `customer_id`, `recruiter_id`, `employee_id`, `log`, `timestamp`) VALUES
(1, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Reminder is marked as completed', '2025-10-31 12:56:34'),
(2, 2, NULL, 2, NULL, NULL, 'Reminder is marked as completed', '2025-10-31 12:57:22'),
(3, 2, NULL, 2, NULL, NULL, 'Reminder is marked as completed', '2025-10-31 12:57:57'),
(4, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Reminder is marked as completed', '2025-10-31 12:59:05'),
(5, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added new contact 8732784', '2025-11-03 02:06:54'),
(6, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added a reminder for new contact 8732784', '2025-11-10 04:30:00'),
(7, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added new contact cname8737', '2025-11-03 02:07:53'),
(8, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added a reminder for new contact cname8737', '2025-11-10 04:30:00'),
(9, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added new contact cname8737', '2025-11-03 02:10:37'),
(10, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added a reminder for new contact cname8737', '2025-11-10 04:30:00'),
(11, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added new contact cname8737', '2025-11-03 02:10:49'),
(12, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Added a reminder for new contact cname8737', '2025-11-10 04:30:00'),
(13, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'New Contact [43] Reminder is added for 10 Nov 2025 10:00 AM', '2025-11-03 02:14:29'),
(14, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact [43] Reminder is updated to 10 Nov 2025 10:00 AM', '2025-11-03 02:14:36'),
(15, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Reminder updated on Contact cname8737[43] to 10 Nov 2025 10:00 AM', '2025-11-03 06:28:44'),
(16, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'New Reminder added on Contact cname8737[43] for 03 Dec 2025 10:00 AM', '2025-11-03 06:29:20'),
(17, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact[43] note is added new note223', '2025-11-03 06:30:46'),
(18, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact[43] note is added sdgdfgdfg', '2025-11-03 06:30:57'),
(19, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact[43] note is added sdufysdf', '2025-11-03 06:32:55'),
(20, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Note added for Contact cname8737[] (Note: sdufysdf)', '2025-11-03 06:32:55'),
(21, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Note added for Contact cname8737[43] (Note: sdufysdfdf33)', '2025-11-03 06:33:21'),
(22, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Note added for Contact cname8737[43] (Note: dsfsd)', '2025-11-03 06:33:27'),
(23, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Note added for Contact cname8737[43] (Note: sdfsdfgsd)', '2025-11-03 06:33:33'),
(24, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact Address ID 1 is updated for Contact cname8737[43]', '2025-11-03 06:34:43'),
(25, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact cname873722[43] name is updated to cname873722', '2025-11-03 06:35:31'),
(26, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact cname873722[43] name is updated to cname873722', '2025-11-03 06:35:34'),
(27, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact cname873722[43] company is updated to sdfgsdhftx122332', '2025-11-03 06:35:50'),
(28, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Contact cname873722[43] company is updated to sdfgsdhftx122332', '2025-11-03 06:35:51'),
(39, 2, NULL, 2, NULL, NULL, 'Added new Customer Customer 1', '2025-11-04 06:10:45'),
(40, 2, NULL, 2, NULL, NULL, 'Added new Customer Customer 2', '2025-11-04 06:16:13'),
(41, 2, NULL, 2, NULL, NULL, 'Added new Customer Customer 3', '2025-11-05 02:36:22'),
(42, 2, NULL, 2, NULL, NULL, 'Added a reminder for new contact Customer 3', '2025-11-12 04:30:00'),
(43, 2, NULL, 2, NULL, NULL, 'Contact Address ID 1 is updated for Contact Customer 2[2]', '2025-11-05 03:50:33'),
(44, 2, NULL, 2, NULL, NULL, 'Contact Address ID 1 is updated for Contact Customer 2[2]', '2025-11-05 03:51:55'),
(45, 2, NULL, 2, NULL, NULL, 'Contact Address ID 1 is updated for Contact Customer 2[2]', '2025-11-05 03:53:47'),
(46, 2, NULL, 2, NULL, NULL, 'Contact Customer 22[2] name is updated to Customer 22', '2025-11-05 03:57:44'),
(47, 2, NULL, 2, NULL, NULL, 'Contact Customer 22[2] name is updated to Customer 22', '2025-11-05 03:57:47'),
(48, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-05 03:58:28'),
(49, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-05 03:58:29'),
(50, 2, NULL, 2, NULL, NULL, 'New Reminder added on Contact Customer 2[2] for 12 Nov 2025 10:00 AM', '2025-11-05 04:04:56'),
(51, 2, NULL, 2, NULL, NULL, 'New Reminder added on Contact Customer 2[2] for 12 Nov 2025 10:00 AM', '2025-11-05 04:07:21'),
(52, 2, NULL, 2, NULL, NULL, 'New Reminder added on Contact Customer 2[2] for 12 Nov 2025 10:00 AM', '2025-11-05 04:07:57'),
(53, 2, NULL, 2, NULL, NULL, 'Reminder updated on Contact Customer 2[2] to 12 Nov 2025 10:00 AM', '2025-11-05 04:08:09'),
(54, 2, NULL, 2, NULL, NULL, 'Reminder updated on Contact Customer 2[2] to 12 Nov 2025 10:00 AM', '2025-11-05 04:08:14'),
(55, 2, NULL, 2, NULL, NULL, 'Reminder updated on Contact Customer 2[2] to 06 Nov 2025 10:00 AM', '2025-11-05 04:08:20'),
(56, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings1', '2025-11-05 05:05:56'),
(57, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings', '2025-11-05 05:05:58'),
(58, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-05 05:06:22'),
(59, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-05 05:06:25'),
(60, 2, NULL, 2, NULL, NULL, 'Note added for Contact Customer 2[2] (Note: this sis a test note1)', '2025-11-05 08:42:17'),
(61, 2, NULL, 2, NULL, NULL, 'Note added for Contact Customer 2[2] (Note: this sis a test note1)', '2025-11-05 08:45:18'),
(62, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-05 09:16:10'),
(63, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-05 09:17:14'),
(64, 2, NULL, 2, NULL, NULL, 'Status of Contact Customer 2[2] is updated to Active', '2025-11-05 09:18:21'),
(65, 2, NULL, 2, NULL, NULL, 'Status of Contact Customer 2[2] is updated to Inactive', '2025-11-05 09:44:55'),
(66, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-05 09:50:45'),
(67, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-05 23:24:23'),
(68, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:43:27'),
(69, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:43:44'),
(70, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:43:59'),
(71, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:44:08'),
(72, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:45:06'),
(73, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2dsfd', '2025-11-06 00:45:31'),
(74, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:46:02'),
(75, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:51:26'),
(76, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-06 00:51:26'),
(77, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-06 00:51:26'),
(78, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-06 00:51:26'),
(79, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-06 00:51:26'),
(80, 2, NULL, 2, NULL, NULL, 'Contact Address ID 1 is updated for Contact Customer 2[2]', '2025-11-06 00:51:46'),
(81, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 22', '2025-11-06 00:55:51'),
(82, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 00:58:08'),
(83, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-06 00:58:08'),
(84, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-06 00:58:08'),
(85, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-06 00:58:08'),
(86, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-06 00:58:08'),
(87, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2n', '2025-11-06 00:59:57'),
(88, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-06 00:59:57'),
(89, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-06 00:59:57'),
(90, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-06 00:59:57'),
(91, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-06 00:59:57'),
(92, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 01:01:49'),
(93, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-06 01:01:49'),
(94, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-06 01:01:49'),
(95, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-06 01:01:49'),
(96, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-06 01:01:49'),
(97, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 21', '2025-11-06 01:08:14'),
(98, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-06 01:10:27'),
(99, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-06 01:10:27'),
(100, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-06 01:10:27'),
(101, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-06 01:10:27'),
(102, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-06 01:10:27'),
(103, 2, NULL, 2, NULL, NULL, 'Status of Contact Customer 2[2] is updated to Active', '2025-11-06 01:12:50'),
(104, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-07 01:30:14'),
(105, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-07 01:30:14'),
(106, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-07 01:30:14'),
(107, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-07 01:30:14'),
(108, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-07 01:30:14'),
(109, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-07 01:30:49'),
(110, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-07 01:30:49'),
(111, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-07 01:30:49'),
(112, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-07 01:30:49'),
(113, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-07 01:30:49'),
(114, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-07 01:36:31'),
(115, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-07 01:36:31'),
(116, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-07 01:36:31'),
(117, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-07 01:36:31'),
(118, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-07 01:36:31'),
(119, 2, NULL, NULL, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Disabled', '2025-11-07 01:36:31'),
(120, 2, NULL, NULL, NULL, NULL, 'WhatsApp Featuer for Contact Customer 2 [2] changed to Disabled', '2025-11-07 01:36:31'),
(121, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-07 01:41:01'),
(122, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-07 01:41:01'),
(123, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-07 01:41:01'),
(124, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-07 01:41:01'),
(125, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-07 01:41:01'),
(126, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-07 01:42:23'),
(127, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-07 01:42:23'),
(128, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-07 01:42:23'),
(129, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-07 01:42:23'),
(130, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2@gmail.com', '2025-11-07 01:42:23'),
(131, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-07 01:43:45'),
(132, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-07 01:43:45'),
(133, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-07 01:43:45'),
(134, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2x@gmail.com', '2025-11-07 01:43:45'),
(135, 2, NULL, NULL, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-07 01:43:45'),
(136, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] company is updated to Company 2', '2025-11-07 01:47:07'),
(137, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] name is updated to Customer 2', '2025-11-07 01:47:07'),
(138, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] industry is updated to Pipes and Fittings ', '2025-11-07 01:47:07'),
(139, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] email is updated to customer2x@gmail.com', '2025-11-07 01:47:07'),
(140, 2, NULL, 2, NULL, NULL, 'Contact Customer 2[2] phone is updated to 7834566345', '2025-11-07 01:47:07'),
(141, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Enabled', '2025-11-07 01:59:24'),
(142, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Disabled', '2025-11-07 02:02:37'),
(143, 2, NULL, 2, NULL, NULL, 'WhatsApp Feature for Contact Customer 2 [2] changed to Enabled', '2025-11-07 02:02:43'),
(144, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Enabled', '2025-11-07 02:02:43'),
(145, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Disabled', '2025-11-07 02:02:50'),
(146, 2, NULL, 2, NULL, NULL, 'WhatsApp Feature for Contact Customer 2 [2] changed to Disabled', '2025-11-07 02:02:50'),
(147, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Enabled', '2025-11-07 02:04:27'),
(148, 2, NULL, 2, NULL, NULL, 'WhatsApp Feature for Contact Customer 2 [2] changed to Enabled', '2025-11-07 02:04:32'),
(149, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Disabled', '2025-11-07 02:04:40'),
(150, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Enabled', '2025-11-07 02:04:49'),
(151, 2, NULL, 2, NULL, NULL, 'Email Feature for Contact Customer 2 [2] changed to Disabled', '2025-11-07 02:04:54'),
(152, 2, NULL, 2, NULL, NULL, 'WhatsApp Feature for Contact Customer 2 [2] changed to Disabled', '2025-11-07 02:05:33'),
(153, 2, NULL, 2, NULL, NULL, 'Contact Address ID 2 is updated for Contact Customer 2[2]', '2025-11-07 03:38:19'),
(154, 2, NULL, 2, NULL, NULL, 'Contact Address ID 2 is updated for Contact Customer 2[2]', '2025-11-07 03:38:25'),
(155, 2, NULL, 2, NULL, NULL, 'Customer Address ID 2 is updated', '2025-11-07 03:50:41'),
(156, 2, NULL, 2, NULL, NULL, 'Customer Address ID 2 is updated', '2025-11-07 03:51:05'),
(157, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 03:52:10'),
(158, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 03:52:47'),
(159, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 03:52:55'),
(160, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 03:53:15'),
(161, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer22@gmail.com', '2025-11-07 03:53:30'),
(162, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 03:55:07'),
(163, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 03:55:53'),
(164, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 03:57:01'),
(165, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2x@gmail.com', '2025-11-07 03:57:48'),
(166, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 03:58:58'),
(167, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 04:00:03'),
(168, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:00:16'),
(169, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer22@gmail.com', '2025-11-07 04:00:31'),
(170, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer22x@gmail.com', '2025-11-07 04:02:58'),
(171, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 04:05:02'),
(172, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:05:09'),
(173, 2, NULL, 2, NULL, NULL, 'Email Feature status changed to Enabled', '2025-11-07 04:05:25'),
(174, 2, NULL, 2, NULL, NULL, 'Email Feature status changed to Disabled', '2025-11-07 04:05:33'),
(175, 2, NULL, 2, NULL, NULL, 'WhatsApp Feature status changed to Enabled', '2025-11-07 04:05:33'),
(176, 2, NULL, 2, NULL, NULL, 'WhatsApp Feature status changed to Disabled', '2025-11-07 04:05:38'),
(177, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2x@gmail.com', '2025-11-07 04:06:03'),
(178, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:06:30'),
(179, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 04:06:38'),
(180, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2z@gmail.com', '2025-11-07 04:06:48'),
(181, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:07:00'),
(182, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 04:08:33'),
(183, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2x@gmail.com', '2025-11-07 04:08:41'),
(184, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2y@gmail.com', '2025-11-07 04:08:55'),
(185, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:09:01'),
(186, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer@gmail.com', '2025-11-07 04:09:11'),
(187, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:09:19'),
(188, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2cc@gmail.com', '2025-11-07 04:09:45'),
(189, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:09:52'),
(190, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2yu@gmail.com', '2025-11-07 04:09:59'),
(191, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2@gmail.com', '2025-11-07 04:10:05'),
(192, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2x@gmail.com', '2025-11-07 04:10:51'),
(193, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2xy@gmail.com', '2025-11-07 04:11:01'),
(194, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2x@gmail.com', '2025-11-07 04:14:39'),
(195, 2, NULL, 2, NULL, NULL, 'Customer email is updated to customer2xx@gmail.com', '2025-11-07 04:14:46'),
(196, 2, NULL, 2, NULL, NULL, 'New Reminder added for 14 Nov 2025 10:00 AM', '2025-11-07 04:43:35'),
(197, 2, NULL, 2, NULL, NULL, 'New Reminder added for 08 Nov 2025 10:00 AM', '2025-11-07 04:47:15'),
(198, 2, NULL, 2, NULL, NULL, 'New Reminder added for 14 Nov 2025 10:00 AM', '2025-11-07 04:47:43'),
(199, 2, NULL, 2, NULL, NULL, 'New Reminder added for 14 Nov 2025 10:00 AM', '2025-11-07 04:48:49'),
(200, 2, NULL, 2, NULL, NULL, 'New Reminder added for 14 Nov 2025 10:00 AM', '2025-11-07 04:49:57'),
(201, 2, NULL, 2, NULL, NULL, 'Reminder updated to 14 Nov 2025 10:00 AM', '2025-11-07 05:37:55'),
(202, 2, NULL, 2, NULL, NULL, 'Reminder updated to 14 Nov 2025 10:00 AM', '2025-11-07 05:42:17'),
(203, 2, NULL, 2, NULL, NULL, 'Reminder updated to 14 Nov 2025 10:00 AM', '2025-11-07 05:46:27'),
(204, 2, NULL, 2, NULL, NULL, 'Reminder updated to 14 Nov 2025 10:00 AM', '2025-11-07 05:47:07'),
(205, 2, NULL, 2, NULL, NULL, 'Reminder updated to 14 Nov 2025 10:00 AM', '2025-11-07 05:47:15'),
(206, 2, NULL, NULL, NULL, NULL, 'Note added for Contact Customer 2[2] (Note: test6636private)', '2025-11-07 06:19:29'),
(207, 2, NULL, 2, NULL, NULL, 'Note added (Note: testprivate636)', '2025-11-07 06:53:01'),
(208, 2, NULL, 2, NULL, NULL, 'Note updated (Note: test6636privatex)', '2025-11-07 06:56:15'),
(209, 2, NULL, 2, NULL, NULL, 'Note updated (Note: test6636privatex)', '2025-11-07 07:00:47'),
(210, 2, NULL, 2, NULL, NULL, 'Note updated (Note: test6636privatex)', '2025-11-07 07:00:52'),
(211, 2, NULL, 2, NULL, NULL, 'Note updated (Note: Email Feature changed from \'Disabled\' to \'Enabled\')', '2025-11-07 07:01:15'),
(212, 2, NULL, 2, NULL, NULL, 'Quotation ref #QTN2511062820 (v4) created', '2025-11-07 07:13:16'),
(213, 2, NULL, 2, NULL, NULL, 'Added new Document idcard ()', '2025-11-07 07:18:33'),
(214, 2, NULL, 2, NULL, NULL, 'Added new Document idcard (1762519750_bc-15.jpg)', '2025-11-07 07:19:09'),
(215, 2, NULL, NULL, NULL, NULL, 'Deleted Document idcard (1762519713_bc-15.jpg)', '2025-11-07 07:19:49'),
(216, 2, NULL, 2, NULL, NULL, 'Added new Document ewfsdfsd (1762519890_bc-13.jpg)', '2025-11-07 07:21:30'),
(217, 2, NULL, 2, NULL, NULL, 'Deleted Document ewfsdfsd (1762519890_bc-13.jpg)', '2025-11-07 07:21:49'),
(218, 2, NULL, 2, NULL, NULL, 'Updated contact Nithin[2] (32784788488 nithin@gmail.com Head)', '2025-11-07 07:30:20'),
(219, 2, NULL, 2, NULL, NULL, 'Added new contact with tectc (2364632 tetsh@jsdfhgf.fdg Des)', '2025-11-07 07:30:44'),
(220, 2, NULL, 2, NULL, NULL, 'Updated job requirement Tets65 (2 x 25) on date 2025-11-03', '2025-11-07 10:51:17'),
(221, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Added job requirement Construction Worker (10 x 1) on date 2025-11-09', '2025-11-07 11:58:36'),
(222, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Added job requirement General Helper (20 x 2) on date 2025-11-10', '2025-11-07 11:58:36'),
(223, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Added job requirement Security Guard (1 x 2) on date 2025-11-11', '2025-11-07 12:04:11'),
(224, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Added job requirement Test (2 x 4) on date 2025-11-12', '2025-11-07 12:04:11'),
(225, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Updated job requirement General Helper (20 x 2) on date 2025-11-05', '2025-11-07 12:04:22'),
(226, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Updated job requirement Construction Worker (10 x 1) on date 2025-11-09', '2025-11-07 12:04:32'),
(227, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Note added (Note: testprivate1)', '2025-11-07 12:10:31'),
(228, 2, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Note added (Note: testpublic1)', '2025-11-07 12:10:44'),
(229, 4, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 3, NULL, NULL, 'Note added (Note: Test note22)', '2025-11-07 12:25:40'),
(230, 4, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 2, NULL, NULL, 'Note added (Note: This is a test note774)', '2025-11-07 12:25:59'),
(231, 4, '{\"id\":1,\"name\":\"Admin\",\"email\":\"crmleads@jrjapp.com\",\"role\":\"admin\"}', 1, NULL, NULL, 'Status of Customer is updated to Work in progress', '2025-11-07 12:26:58'),
(232, 2, NULL, 2, NULL, NULL, 'Updated contact Nithin[2] (32784788488 nithin@gmail.com Head)', '2025-11-07 12:33:09'),
(233, 2, NULL, 2, NULL, NULL, 'Updated contact Nithin[2] (32784788488 nithin@gmail.com Head)', '2025-11-07 12:34:07');

-- --------------------------------------------------------

--
-- Table structure for table `recruiters`
--

DROP TABLE IF EXISTS `recruiters`;
CREATE TABLE IF NOT EXISTS `recruiters` (
  `id` int NOT NULL AUTO_INCREMENT,
  `agent_id` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `company` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `phones` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'all phone numbers',
  `phone` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `whatsapp` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
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
  `fil_domains` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter domains comma seperated',
  `fil_emails` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci COMMENT 'email filter emails comma seperated',
  `created_at` datetime NOT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recruiters_logs`
--

DROP TABLE IF EXISTS `recruiters_logs`;
CREATE TABLE IF NOT EXISTS `recruiters_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `recruiter_id` int NOT NULL,
  `name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `notes` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recruiters_reminders`
--

DROP TABLE IF EXISTS `recruiters_reminders`;
CREATE TABLE IF NOT EXISTS `recruiters_reminders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `customer_id` int NOT NULL,
  `reminder_at` datetime NOT NULL,
  `type` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'callback/followup/send quote etc',
  `note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  `completed` int NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'Admin', 'crmleads@jrjapp.com', '$2y$10$p44vxeYhUDJ3MtMCOFy/DuhsctK8QhNW56eykICtffeq0ApdQBZvC', 'admin', NULL, '2025-11-07 17:57:29', '2025-09-02 15:04:35');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jan 08, 2026 at 05:10 AM
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
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `applicants`
--

INSERT INTO `applicants` (`id`, `ref_no`, `full_name`, `mobile`, `email`, `nationality`, `current_location`, `city`, `position_category`, `other_position`, `years_experience`, `preferred_work_location`, `availability`, `visa_status`, `notice_period`, `expected_salary_aed`, `communication_preference`, `consent`, `lead_source`, `status`, `ip_address`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 'AN-20260105-000001', 'Joice George', '+919562498181', 'joicekurups@gmail.com', 'India', 'Outside UAE', 'Kochi', 'General Helper', NULL, 3, 'Abu Dhabi', 'Within 7 days', NULL, '15 days', 2400, 'Phone', 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-05 17:19:20', '2026-01-05 17:19:20'),
(2, 'AN-20260105-000002', 'Joice George', '+919562498181', 'joicekurups@gmail.com', 'India', 'Outside UAE', 'Kochi', 'General Helper', NULL, 3, 'Abu Dhabi', 'Within 7 days', NULL, '15 days', 2400, 'Phone', 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-05 17:28:33', '2026-01-05 17:28:33'),
(3, 'AN-20260105-000003', 'dfgdfg', '345345334', NULL, 'dfgdfg', 'Outside UAE', 'dfgdfg', 'Driver', NULL, NULL, NULL, 'Within 7 days', NULL, NULL, NULL, NULL, 1, 'Website', 'UNDER_REVIEW', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-05 17:33:20', '2026-01-05 18:54:51'),
(4, 'AN-20260106-000004', 'Joice G', '+919562498181', 'joicekurups@gmail.com', 'India', 'Outside UAE', 'Kottayam', 'Cleaner', NULL, 4, 'Dubai', 'Within 30 days', NULL, '15 days', 3500, 'Phone', 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:30:12', '2026-01-06 12:30:12'),
(5, 'AN-20260106-000005', 'Joice G1', '+919562498181', 'joicekurups@gmail.com', 'India', 'Outside UAE', 'Kottayam', 'Cleaner', NULL, 4, 'Dubai', 'Within 30 days', NULL, '15 days', 3500, 'Phone', 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:31:06', '2026-01-06 12:31:06'),
(6, 'AN-20260106-000006', 'Joice G1', '+919562498181', 'joicekurups@gmail.com', 'India', 'Outside UAE', 'Kottayam', 'Cleaner', NULL, 4, 'Dubai', 'Within 30 days', NULL, '15 days', 3500, 'Phone', 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:34:28', '2026-01-06 12:34:28'),
(7, 'AN-20260106-000007', 'Joice G2', '+919562498181', 'joicekurups@gmail.com', 'India', 'Outside UAE', 'Kottayam', 'Cleaner', NULL, 4, 'Dubai', 'Within 30 days', NULL, '15 days', 3500, 'Phone', 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:38:08', '2026-01-06 12:38:08'),
(8, '260106008', 'Joice G2', '+919562498181', 'joicekurups@gmail.com', 'India', 'Outside UAE', 'Kottayam', 'Cleaner', NULL, 4, 'Dubai', 'Within 30 days', NULL, '15 days', 3500, 'Phone', 1, 'Website', 'OFFERED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:40:45', '2026-01-06 17:32:04'),
(9, '260106009', 'sdfsdhf', '3242342343', NULL, 'Idnai', 'UAE', '324234', 'Driver', NULL, NULL, NULL, 'Within 15 days', 'Cancelled', NULL, NULL, NULL, 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:50:47', '2026-01-06 12:50:47'),
(10, '260106010', 'sdfsdhf', '3242342343', NULL, 'Idnai', 'UAE', '324234', 'Driver', NULL, NULL, NULL, 'Within 15 days', 'Cancelled', NULL, NULL, NULL, 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:51:05', '2026-01-06 12:51:05'),
(11, '260106011', 'sdfsdhf', '3242342343', NULL, 'Idnai', 'UAE', '324234', 'Driver', NULL, NULL, NULL, 'Within 15 days', 'Cancelled', NULL, NULL, NULL, 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:51:44', '2026-01-06 12:51:44'),
(12, '260106012', 'sdfsdhf111', '3242342343', NULL, 'Idnai', 'UAE', '324234', 'Driver', NULL, NULL, NULL, 'Within 15 days', 'Cancelled', NULL, NULL, NULL, 1, 'Website', 'SHORTLISTED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 12:52:20', '2026-01-06 17:29:46'),
(13, '260106013', 'fdgdfg', '7734577344', NULL, 'dfgdfgdf', 'Outside UAE', 'ssdfjghf', 'General Helper', NULL, 3, NULL, 'Immediately', NULL, NULL, 1500, NULL, 1, 'Website', 'CV_RECEIVED', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/143.0.0.0 Safari/537.36', '2026-01-06 20:01:06', '2026-01-06 20:01:06');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicants_interviews`
--

INSERT INTO `applicants_interviews` (`id`, `applicant_id`, `interview_at`, `mode`, `location`, `interviewer`, `notes`, `status`, `created_at`, `updated_at`) VALUES
(1, 3, '2026-01-07 10:25:00', 'Phone', '324234', 'Manager', 'this is test interview1', 'COMPLETED', '2026-01-06 12:23:49', '2026-01-06 12:24:36'),
(2, 8, '2026-01-06 19:29:00', 'Phone', '', 'Manger', 'this is test interview', 'COMPLETED', '2026-01-06 17:31:12', '2026-01-06 17:31:43');

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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `applicants_reminders`
--

INSERT INTO `applicants_reminders` (`id`, `applicant_id`, `reminder_at`, `type`, `contact_id`, `note`, `created_at`, `updated_at`, `completed`) VALUES
(1, 3, '2026-01-07 08:25:00', 'Meeting', NULL, 'Interview reminder (Phone)', '2026-01-06 12:23:49', NULL, 0),
(2, 12, '2026-01-07 19:21:00', 'General', NULL, 'test toe', '2026-01-06 17:21:45', NULL, 0),
(3, 12, '2026-01-07 10:00:00', 'Call', NULL, 'Call candidate', '2026-01-06 17:21:52', NULL, 0),
(4, 12, '2026-01-06 11:52:20', 'Email', NULL, 'Request documents', '2026-01-06 17:22:20', NULL, 0),
(5, 8, '2026-01-06 17:29:00', 'Meeting', NULL, 'Interview reminder (Phone)', '2026-01-06 17:31:13', NULL, 0),
(6, 8, '2026-01-07 10:00:00', 'Call', NULL, 'Call candidate', '2026-01-06 17:31:51', NULL, 0);

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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `applicant_activity_logs`
--

INSERT INTO `applicant_activity_logs` (`id`, `applicant_id`, `activity_type`, `title`, `details`, `meta_json`, `created_by`, `created_at`) VALUES
(1, 3, 'STATUS_CHANGED', 'Status changed to UNDER_REVIEW', NULL, NULL, 'Nithin1', '2026-01-05 18:54:51'),
(2, 3, 'NOTE', 'Internal Note', 'this is timeline', NULL, 'Nithin1', '2026-01-05 18:55:12'),
(3, 3, 'NOTE', 'Internal Note', 'This is a test note to the applicant', NULL, 'Nithin1', '2026-01-06 10:04:45'),
(4, 3, 'NOTE', 'Internal Note', 'This is a test note2 to the applicant', NULL, 'Nithin1', '2026-01-06 10:04:55'),
(5, 3, 'NOTE', 'Internal Note', 'This is a test note3 to the applicant', NULL, 'Nithin1', '2026-01-06 10:05:02'),
(6, 3, 'INTERVIEW_SCHEDULED', 'Interview scheduled', 'Phone interview at 07 Jan 2026 10:25', NULL, NULL, '2026-01-06 12:23:49'),
(7, 3, 'NOTE', 'Call attempted', 'No answer', NULL, NULL, '2026-01-06 12:26:16'),
(8, 3, 'NOTE', 'Call connected', 'Candidate answered', NULL, NULL, '2026-01-06 12:26:29'),
(9, 7, 'CREATED', 'Application submitted', 'Lead source: Website', NULL, NULL, '2026-01-06 12:38:08'),
(10, 7, 'DOC_UPLOADED', 'CV uploaded', 'While application submission', NULL, NULL, '2026-01-06 12:38:08'),
(11, 8, 'CREATED', 'Application submitted', 'Lead source: Website', NULL, NULL, '2026-01-06 12:40:45'),
(12, 8, 'DOC_UPLOADED', 'CV uploaded', 'While application submission', NULL, NULL, '2026-01-06 12:40:45'),
(13, 9, 'CREATED', 'Application submitted', 'Lead source: Website', NULL, NULL, '2026-01-06 12:50:47'),
(14, 9, 'DOC_UPLOADED', 'CV uploaded', 'While application submission', NULL, NULL, '2026-01-06 12:50:47'),
(15, 10, 'CREATED', 'Application submitted', 'Lead source: Website', NULL, NULL, '2026-01-06 12:51:05'),
(16, 10, 'DOC_UPLOADED', 'CV uploaded', 'While application submission', NULL, NULL, '2026-01-06 12:51:05'),
(17, 11, 'CREATED', 'Application submitted', 'Lead source: Website', NULL, NULL, '2026-01-06 12:51:44'),
(18, 11, 'DOC_UPLOADED', 'CV uploaded', 'While application submission', NULL, NULL, '2026-01-06 12:51:44'),
(19, 12, 'CREATED', 'Application submitted', 'Lead source: Website', NULL, NULL, '2026-01-06 12:52:20'),
(20, 12, 'DOC_UPLOADED', 'CV uploaded', 'While application submission', NULL, NULL, '2026-01-06 12:52:20'),
(21, 12, 'NOTE', 'Reminder added', 'General – test toe (2026-01-07T19:21)', NULL, NULL, '2026-01-06 17:21:45'),
(22, 12, 'NOTE', 'Reminder set', 'Call tomorrow at 10:00', NULL, NULL, '2026-01-06 17:21:52'),
(23, 12, 'NOTE', 'Documents requested', NULL, NULL, NULL, '2026-01-06 17:22:20'),
(24, 12, 'NOTE', 'Candidate shortlisted', NULL, NULL, NULL, '2026-01-06 17:29:47'),
(25, 8, 'INTERVIEW_SCHEDULED', 'Interview scheduled', 'Phone interview at 06 Jan 2026 19:29', NULL, NULL, '2026-01-06 17:31:13'),
(26, 8, 'NOTE', 'Reminder set', 'Call tomorrow at 10:00', NULL, NULL, '2026-01-06 17:31:51'),
(27, 8, 'NOTE', 'Offer sent to candidate', NULL, NULL, NULL, '2026-01-06 17:32:04'),
(28, 13, 'CREATED', 'Application submitted', 'Lead source: Website', NULL, NULL, '2026-01-06 20:01:06'),
(29, 13, 'DOC_UPLOADED', 'CV uploaded', 'While application submission', NULL, NULL, '2026-01-06 20:01:06');

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
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `applicant_documents`
--

INSERT INTO `applicant_documents` (`id`, `applicant_id`, `doc_type`, `doc_label`, `original_filename`, `stored_filename`, `file_path`, `mime_type`, `size_bytes`, `sha256`, `uploaded_at`) VALUES
(1, 1, 'CV', 'CV', 'sample - Copy.pdf', 'CV_0854bc0fd08a2ac3c4a0_1767613760.pdf', 'uploads/CV_0854bc0fd08a2ac3c4a0_1767613760.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-05 17:19:20'),
(2, 1, 'OTHER', 'Additional Document', 'bc-15.jpg', 'DOC_a5fc49d8d485e07b4915_1767613760.jpg', 'uploads/DOC_a5fc49d8d485e07b4915_1767613760.jpg', 'image/jpeg', 673064, 'c75eaf8e578929d18bce864998f0d950c15250cb658d7b6efebabcf568909a12', '2026-01-05 17:19:20'),
(3, 1, 'OTHER', 'Additional Document', 'sample.pdf', 'DOC_c78880755d3aa5c6a050_1767613760.pdf', 'uploads/DOC_c78880755d3aa5c6a050_1767613760.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-05 17:19:20'),
(4, 2, 'CV', 'CV', 'sample - Copy.pdf', 'CV_45b6ca352303de5f19f4_1767614313.pdf', 'uploads/CV_45b6ca352303de5f19f4_1767614313.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-05 17:28:34'),
(5, 2, 'OTHER', 'Additional Document', 'bc-15.jpg', 'DOC_62035604f42cdcfbf622_1767614313.jpg', 'uploads/DOC_62035604f42cdcfbf622_1767614313.jpg', 'image/jpeg', 673064, 'c75eaf8e578929d18bce864998f0d950c15250cb658d7b6efebabcf568909a12', '2026-01-05 17:28:34'),
(6, 2, 'OTHER', 'Additional Document', 'sample.pdf', 'DOC_ea15d46c8f5888705b3a_1767614313.pdf', 'uploads/DOC_ea15d46c8f5888705b3a_1767614313.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-05 17:28:34'),
(7, 3, 'CV', 'CV', 'sample.pdf', 'CV_484012b67b116ab2be1f_1767614600.pdf', 'uploads/CV_484012b67b116ab2be1f_1767614600.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-05 17:33:20'),
(8, 4, 'CV', 'CV', 'sample - Copy.pdf', 'CV_bd6dc1d2a6b7944a90c3_1767682812.pdf', 'uploads/CV_bd6dc1d2a6b7944a90c3_1767682812.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:30:12'),
(9, 4, 'OTHER', 'Additional Document', 'bc-15.jpg', 'DOC_b3c3bdedf86428dd006d_1767682812.jpg', 'uploads/DOC_b3c3bdedf86428dd006d_1767682812.jpg', 'image/jpeg', 673064, 'c75eaf8e578929d18bce864998f0d950c15250cb658d7b6efebabcf568909a12', '2026-01-06 12:30:12'),
(10, 4, 'OTHER', 'Additional Document', 'sample.pdf', 'DOC_c84ee3430c57e3d4673d_1767682812.pdf', 'uploads/DOC_c84ee3430c57e3d4673d_1767682812.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:30:12'),
(11, 5, 'CV', 'CV', 'sample.pdf', 'CV_241861aa9776f77e8572_1767682866.pdf', 'uploads/CV_241861aa9776f77e8572_1767682866.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:31:06'),
(12, 5, 'OTHER', 'Additional Document', 'bc-15.jpg', 'DOC_840d26becdb03227302d_1767682866.jpg', 'uploads/DOC_840d26becdb03227302d_1767682866.jpg', 'image/jpeg', 673064, 'c75eaf8e578929d18bce864998f0d950c15250cb658d7b6efebabcf568909a12', '2026-01-06 12:31:06'),
(13, 5, 'OTHER', 'Additional Document', 'sample - Copy (2).pdf', 'DOC_0a3a1427784dde847455_1767682866.pdf', 'uploads/DOC_0a3a1427784dde847455_1767682866.pdf', 'application/pdf', 1702, '75d168283e0abc080b5d90e76a05c89aff44a788bd44582659459d7e0f97c18c', '2026-01-06 12:31:06'),
(14, 6, 'CV', 'CV', 'sample - Copy.pdf', 'CV_4ac7d033f3d45c1ced62_1767683068.pdf', 'uploads/CV_4ac7d033f3d45c1ced62_1767683068.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:34:28'),
(15, 6, 'OTHER', 'Additional Document', 'Sample.png', 'DOC_f3971f5001bca69a6074_1767683068.png', 'uploads/DOC_f3971f5001bca69a6074_1767683068.png', 'image/png', 914753, 'e27437abe5fb792f40e243095aa5b0a62a408973b462b6571507cc8f0dccf108', '2026-01-06 12:34:28'),
(16, 6, 'OTHER', 'Additional Document', 'sample - Copy.pdf', 'DOC_2380d7fe43d4aaa6e57f_1767683068.pdf', 'uploads/DOC_2380d7fe43d4aaa6e57f_1767683068.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:34:28'),
(17, 7, 'CV', 'CV', 'sample - Copy.pdf', 'CV_100e175c0c4f8c748cef_1767683288.pdf', 'uploads/CV_100e175c0c4f8c748cef_1767683288.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:38:08'),
(18, 7, 'OTHER', 'Additional Document', 'bc-15.jpg', 'DOC_6925b33dd7c8564322dd_1767683288.jpg', 'uploads/DOC_6925b33dd7c8564322dd_1767683288.jpg', 'image/jpeg', 673064, 'c75eaf8e578929d18bce864998f0d950c15250cb658d7b6efebabcf568909a12', '2026-01-06 12:38:08'),
(19, 7, 'OTHER', 'Additional Document', 'sample - Copy.pdf', 'DOC_144528b15150ce3b1315_1767683288.pdf', 'uploads/DOC_144528b15150ce3b1315_1767683288.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:38:08'),
(20, 7, 'OTHER', 'Additional Document', 'sample_user (1).jpg', 'DOC_259e2f477305efd6a6de_1767683288.jpg', 'uploads/DOC_259e2f477305efd6a6de_1767683288.jpg', 'image/jpeg', 53131, '330eef45c935902a6085f4e659ef4a47bdca57715abe052968a101418d95b346', '2026-01-06 12:38:08'),
(21, 8, 'CV', 'CV', 'sample - Copy.pdf', 'CV_8ed0276e7eb1cb4ee395_1767683445.pdf', 'uploads/CV_8ed0276e7eb1cb4ee395_1767683445.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:40:45'),
(22, 8, 'OTHER', 'Additional Document', 'bc-15.jpg', 'DOC_7eafc2e521aa1d877e81_1767683445.jpg', 'uploads/DOC_7eafc2e521aa1d877e81_1767683445.jpg', 'image/jpeg', 673064, 'c75eaf8e578929d18bce864998f0d950c15250cb658d7b6efebabcf568909a12', '2026-01-06 12:40:45'),
(23, 8, 'OTHER', 'Additional Document', 'sample - Copy.pdf', 'DOC_5dbc6229e03c8d6a7e51_1767683445.pdf', 'uploads/DOC_5dbc6229e03c8d6a7e51_1767683445.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:40:45'),
(24, 8, 'OTHER', 'Additional Document', 'sample_user (1).jpg', 'DOC_14e11df418a06aada014_1767683445.jpg', 'uploads/DOC_14e11df418a06aada014_1767683445.jpg', 'image/jpeg', 53131, '330eef45c935902a6085f4e659ef4a47bdca57715abe052968a101418d95b346', '2026-01-06 12:40:45'),
(25, 9, 'CV', 'CV', 'sample.pdf', 'CV_7c3c86aa2f73e4daef60_1767684047.pdf', 'uploads/CV_7c3c86aa2f73e4daef60_1767684047.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:50:47'),
(26, 10, 'CV', 'CV', 'sample.pdf', 'CV_ed85ce809936f074eb23_1767684065.pdf', 'uploads/CV_ed85ce809936f074eb23_1767684065.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:51:05'),
(27, 11, 'CV', 'CV', 'sample.pdf', 'CV_f0d8cd255cd502bb2098_1767684104.pdf', 'uploads/CV_f0d8cd255cd502bb2098_1767684104.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 12:51:44'),
(28, 12, 'CV', 'CV', 'sample - Copy (2).pdf', 'CV_4391823fb2e8926d15c4_1767684140.pdf', 'uploads/CV_4391823fb2e8926d15c4_1767684140.pdf', 'application/pdf', 1702, '75d168283e0abc080b5d90e76a05c89aff44a788bd44582659459d7e0f97c18c', '2026-01-06 12:52:20'),
(29, 13, 'CV', 'CV', 'sample - Copy.pdf', 'CV_58844e193e5bffd1529e_1767709866.pdf', 'uploads/CV_58844e193e5bffd1529e_1767709866.pdf', 'application/pdf', 3028, '8decc8571946d4cd70a024949e033a2a2a54377fe9f1c1b944c20f9ee11a9e51', '2026-01-06 20:01:06');

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
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `applicant_status_history`
--

INSERT INTO `applicant_status_history` (`id`, `applicant_id`, `old_status`, `new_status`, `note`, `changed_by`, `created_at`) VALUES
(1, 3, 'CV_RECEIVED', 'UNDER_REVIEW', '', 'Nithin1', '2026-01-05 18:54:51'),
(2, 12, 'CV_RECEIVED', 'SHORTLISTED', NULL, NULL, '2026-01-06 17:29:47'),
(3, 8, 'CV_RECEIVED', 'OFFERED', NULL, NULL, '2026-01-06 17:32:04');

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
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb3;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `agent_id`, `name`, `company`, `phone`, `email`, `photo`, `photo1`, `enable_email`, `enable_whatsapp`, `archived`, `date_added`) VALUES
(1, NULL, 'Nithin New', '', '97145698456', '', 'DdXLMmlyvUN7r7IZKjeuRxycXZ5EcgeYAAGlKUGr251210042516.png', NULL, 0, 0, 0, '2025-12-10 04:23:45'),
(2, NULL, 'WATERGY', '', '', 'watergy@gmail.com', NULL, NULL, 0, 0, 0, '2025-12-11 08:12:02'),
(3, NULL, 'Watergy1', '', '3245235456', 'watergy1@gmail.com', NULL, NULL, 0, 0, 0, '2025-12-16 12:07:21'),
(4, NULL, 'Watergy2', '', '', 'watergy2@gmail.com', NULL, NULL, 0, 1, 0, '2025-12-16 12:26:11');

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
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts_contacts`
--

INSERT INTO `contacts_contacts` (`id`, `contact_id`, `name`, `phone`, `email`, `photo1`, `photo2`, `designation`, `created_by`, `created_at`) VALUES
(1, 1, 'cname', '326462364', 'contact@sdf.dfg', NULL, NULL, 'des', NULL, '2025-12-15 09:37:05'),
(2, 1, 'ncontact', '7234723784', 'shdfjhsdbf@sdf.sdf', NULL, NULL, 'des2', NULL, '2025-12-16 08:54:18');

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts_logs`
--

INSERT INTO `contacts_logs` (`id`, `contact_id`, `agent_id`, `name`, `notes`, `type`, `visibility`, `created_at`) VALUES
(1, 1, NULL, 'Nithin New', 'Channel: Walk-in. Scenario: Job Application. Summary: Job Enquiry. Notes: CV received in office. Assigned to: Kavitha. Priority: normal.', 'General', 'Public', '2025-12-10 00:23:45'),
(2, 2, NULL, 'WATERGY', 'Channel: Email. Scenario: PAYMENT RECEIPTS. Notes: RECIEVED ONLINE PAYMENT RECEIPT TOWARDS INVOICE. Assigned to: Adarsh. Priority: normal.', 'General', 'Public', '2025-12-11 04:12:02'),
(3, 1, NULL, 'cname', 'Channel: Email. Summary: dsfsdf. Notes: sdfsdfsd. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2025-12-15 05:02:23'),
(4, 1, NULL, 'cname', 'Channel: Email. Summary: dsfsdf. Notes: sdfsdfsd. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2025-12-15 05:09:50'),
(5, 2, 2, 'Nithin1', 'sdfsdfggg', 'Email', 'Public', '2025-12-16 00:35:40'),
(6, 2, 2, 'Nithin1', 'sdfsd', 'Meeting', 'Public', '2025-12-16 00:40:36'),
(7, 2, NULL, 'ncontact', 'Channel: Phone. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2025-12-16 03:24:36'),
(8, 1, NULL, 'newc', 'Channel: Phone. Scenario: Customer Request. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2025-12-16 06:35:30'),
(9, 3, NULL, 'Watergy', 'Channel: Phone. Scenario: dsfsdf. Summary: sdfsd. Notes: sdf. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2025-12-16 06:37:21'),
(10, 3, NULL, 'watergy1', 'Channel: Phone. Summary: sumgh. Notes: fdgdfg. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2025-12-16 06:55:15'),
(11, 4, NULL, 'Watergy2', 'Channel: Phone. Scenario: Job Application. Summary: summ. Notes: fdgdfg. Assigned to: Nithin1. Priority: normal.', 'General', 'Public', '2025-12-16 06:56:11'),
(12, 4, NULL, 'Nithin1', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-12-29 11:15:07');

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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts_reminders`
--

INSERT INTO `contacts_reminders` (`id`, `reminder_at`, `type`, `contact_id`, `note`, `created_at`, `updated_at`, `completed`) VALUES
(1, '2025-12-11 08:22:00', 'General', 1, 'Channel: Walk-in. Scenario: Job Application. Summary: Job Enquiry. Notes: CV received in office. Assigned to: Kavitha. Priority: normal.', '2025-12-10 04:23:45', NULL, 0),
(2, '2025-12-12 12:10:00', 'General', 2, 'Channel: Email. Scenario: PAYMENT RECEIPTS. Notes: RECIEVED ONLINE PAYMENT RECEIPT TOWARDS INVOICE. Assigned to: Adarsh. Priority: normal.', '2025-12-11 08:12:02', NULL, 0),
(3, '2025-12-16 05:32:00', 'General', 1, 'Channel: Email. Summary: dsfsdf. Notes: sdfsdfsd. Assigned to: Nithin1. Priority: normal.', '2025-12-15 10:32:23', NULL, 0),
(4, '2025-12-16 05:32:00', 'General', 1, 'Channel: Email. Summary: dsfsdf. Notes: sdfsdfsd. Assigned to: Nithin1. Priority: normal.', '2025-12-15 10:39:50', NULL, 0),
(5, '2025-12-09 13:48:06', 'General', 2, 'Channel: Phone. Assigned to: Nithin1. Priority: normal.', '2025-12-16 08:54:36', '2025-12-30 13:49:48', 0),
(6, '2025-12-09 13:48:06', 'General', 1, 'Channel: Phone. Scenario: Customer Request. Assigned to: Nithin1. Priority: normal.', '2025-12-16 12:05:30', '2025-12-30 13:49:48', 0),
(7, '2025-12-09 13:48:06', 'General', 3, 'Channel: Phone. Scenario: dsfsdf. Summary: sdfsd. Notes: sdf. Assigned to: Nithin1. Priority: normal.', '2025-12-16 12:07:21', '2025-12-30 13:49:48', 0),
(8, '2025-12-09 13:48:06', 'General', 3, 'Channel: Phone. Summary: sumgh. Notes: fdgdfg. Assigned to: Nithin1. Priority: normal.', '2025-12-16 12:25:15', '2025-12-30 13:49:48', 0),
(9, '2025-12-09 13:48:06', 'General', 4, 'Channel: Phone. Scenario: Job Application. Summary: summ. Notes: fdgdfg. Assigned to: Nithin1. Priority: normal.', '2025-12-16 12:26:11', '2025-12-30 13:49:48', 0);

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
(3, 'Existing Recruiter', 'employee', 'recruiters_view', '2025-12-08 14:58:34'),
(4, 'Existing Supplier', 'vendor', 'suppliers_view', '2025-12-08 14:58:34'),
(5, 'Existing Employee', 'existing-employee', 'employees_viewr', '2025-12-09 05:19:13'),
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
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `created_documents`
--

INSERT INTO `created_documents` (`id`, `template_version_id`, `entity_type`, `entity_identifier`, `title`, `content`, `add_to_profile`, `created_by`, `created_at`) VALUES
(1, 1, 'employee', '1', 'Offer letter E1', 'This is offer letter template', 1, NULL, '2025-12-09 05:45:38'),
(3, 2, 'employee', '1', 'Document', '\nHi Joice\nThis is offer letter template version 2\n\ndate : 2025-01-01', 1, NULL, '2025-12-09 06:08:47'),
(4, 3, 'employee', '2', 'test', 'Hi EmployeeName\nThis is offer letter template\n\ndate : 2026-02-01', 1, NULL, '2025-12-09 07:20:38'),
(5, 4, 'employee', '1', 'Employee11 - Document', '\r\nHi Employee11\r\nThis is offer letter template\r\n\r\nnew date\r\n\r\ndate : 2025-12-09', 1, NULL, '2025-12-09 09:11:42'),
(9, 4, 'employee', '3', 'Testemp1 - Document', '\r\nHi Testemp1\r\nThis is offer letter template\r\n\r\n345345newdat33\r\n\r\n237463264\r\ndate : 2025-12-09', 0, NULL, '2025-12-09 12:55:23'),
(10, 4, 'other', '', 'Document8', '\r\nHi hdfhhj\r\nThis is offer letter template\r\n\r\nHJHJH\r\n\r\n72367367\r\ndate : 2025-12-09', 0, NULL, '2025-12-09 13:03:40'),
(11, 5, 'employee', '2', 'Emp2 - Document', '\r\nHi Emp2\r\nThis is offer letter template v4\r\n\r\nnewdata value\r\n\r\n7832264623\r\ndate : 2025-12-11', 1, NULL, '2025-12-11 05:27:29'),
(12, 5, 'employee', '', 'Document', '\r\nHi \r\nThis is offer letter template v4\r\n\r\n\r\n\r\n\r\ndate : 2025-12-11', 0, NULL, '2025-12-11 06:34:41'),
(13, 6, 'employee', '1', 'Employee11 - Document', 'To  \r\nThe Manager  \r\nALNASR GENERAL SERVICE EST  \r\nABU DHABI – UAE\r\n\r\nDate: 2025-12-11\r\n\r\nSub:     Request for resignation\r\n\r\nDear Sir,\r\n\r\nWith due respect, I, Employee11 (Emirates ID No: ), have been working in your esteemed organization as a  for  years. My visa is expiring on . I regret to inform you that I hereby tender my resignation effective from  due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.\r\n\r\nI humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully,  \r\nEmployee11  \r\nMob: 6366734672\r\n', 0, NULL, '2025-12-11 08:53:35'),
(14, 6, 'employee', '1', 'Employee11 - Document', 'To  \r\nThe Manager  \r\nALNASR GENERAL SERVICE EST  \r\nABU DHABI – UAE\r\n\r\nDate: 2025-12-11\r\n\r\nSub:     Request for resignation\r\n\r\nDear Sir,\r\n\r\nWith due respect, I, Employee11 (Emirates ID No: ), have been working in your esteemed organization as a  for  years. My visa is expiring on . I regret to inform you that I hereby tender my resignation effective from  due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.\r\n\r\nI humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully,  \r\n\r\n\r\n\r\nEmployee11  \r\nMob: 6366734672\r\n', 0, NULL, '2025-12-11 08:54:52'),
(15, 7, 'employee', '1', 'Employee11 - Document', '<!doctype html>\r\n<html lang=\"en\">\r\n<head>\r\n  <meta charset=\"utf-8\" />\r\n  <title>Resignation Letter</title>\r\n  <meta name=\"viewport\" content=\"width=device-width,initial-scale=1\" />\r\n  <style>\r\n    /* A4 page sizing for print */\r\n    @page { size: A4; margin: 28mm 20mm; }\r\n    html, body {\r\n      height: 100%;\r\n      margin: 0;\r\n      padding: 0;\r\n      font-family: \"DejaVu Sans\", \"Arial\", \"Helvetica\", sans-serif;\r\n      color: #111;\r\n      -webkit-font-smoothing: antialiased;\r\n      -moz-osx-font-smoothing: grayscale;\r\n    }\r\n\r\n    /* Container that keeps content inside printable area */\r\n    .page {\r\n      box-sizing: border-box;\r\n      width: 210mm;\r\n      min-height: 297mm;\r\n      padding: 20mm;\r\n      margin: 0 auto;\r\n      background: white;\r\n    }\r\n\r\n    .to-block {\r\n      margin-bottom: 12px;\r\n      line-height: 1.35;\r\n    }\r\n\r\n    .date-right {\r\n      float: right;\r\n      text-align: right;\r\n      font-weight: 600;\r\n    }\r\n\r\n    .subject {\r\n      margin-top: 18px;\r\n      margin-bottom: 18px;\r\n      letter-spacing: 0.2px;\r\n      font-weight: 600;\r\n    }\r\n\r\n    p {\r\n      margin: 0 0 12px 0;\r\n      line-height: 1.55;\r\n      text-align: justify;\r\n    }\r\n\r\n    .signature-block {\r\n      margin-top: 34px;\r\n    }\r\n\r\n    .signature-line {\r\n      margin-top: 48px;\r\n      height: 2.2cm; /* space for handwritten signature */\r\n    }\r\n\r\n    .name {\r\n      margin-top: 8px;\r\n      font-weight: 700;\r\n    }\r\n\r\n    .phone {\r\n      margin-top: 2px;\r\n    }\r\n\r\n    /* ensure the floated date doesn\'t collide on small screens */\r\n    @media print, screen and (min-width: 480px) {\r\n      .date-right { float: right; }\r\n    }\r\n  </style>\r\n</head>\r\n<body>\r\n  <div class=\"page\">\r\n    <div style=\"overflow: hidden;\">\r\n      <!-- Left side \"To\" block -->\r\n      <div class=\"to-block\" style=\"width:60%; float:left;\">\r\n        <div>To</div>\r\n        <div><strong>The Manager</strong></div>\r\n        <div><strong>ALNASR GENERAL SERVICE EST</strong></div>\r\n        <div><strong>ABU DHABI – UAE</strong></div>\r\n      </div>\r\n\r\n      <!-- Right side date -->\r\n      <div class=\"date-right\">\r\n        Date: <span>2025-12-11</span>\r\n      </div>\r\n    </div>\r\n\r\n    <div style=\"clear: both;\"></div>\r\n\r\n    <div class=\"subject\">\r\n      Sub: &nbsp;&nbsp;&nbsp;&nbsp; Request for resignation\r\n    </div>\r\n\r\n    <p>Dear Sir,</p>\r\n\r\n    <p>\r\n      With due respect, I, <strong>Employee11</strong> (Emirates ID No: <strong></strong>), have been working in your esteemed organization as a <strong></strong> for <strong></strong> years. My visa is expiring on <strong></strong>. I regret to inform you that I hereby tender my resignation effective from <strong></strong> due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.\r\n    </p>\r\n\r\n    <p>\r\n      I humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n    </p>\r\n\r\n    <p>Thanking you,</p>\r\n\r\n    <p>Yours faithfully,</p>\r\n\r\n    <div class=\"signature-block\">\r\n      <div class=\"signature-line\"></div>\r\n      <div class=\"name\">Employee11</div>\r\n      <div class=\"phone\">Mob: 6366734672</div>\r\n    </div>\r\n  </div>\r\n</body>\r\n</html>\r\n', 0, NULL, '2025-12-11 08:57:42'),
(16, 9, 'employee', '1', 'Employee11 - Document', 'To\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\n                                                                                 Date: 11-12-2025\r\n\r\nSub:        <strong>Request for resignation</strong>\r\n\r\nDear Sir,\r\n\r\n        With due respect, <strong>Employee11</strong>  (<strong>EMIRATES ID NO: </strong>) have been working in your esteemed organization as a <strong></strong> for  years. <strong>My visa is expired to the \r\ndate of </strong>. I regret to inform you that I hereby tender my resignation \r\n(<strong></strong>) due to some serious family problem. So, please consider \r\nthis letter as my prior notice for the same and send me back after settle my final accounts.\r\n\r\nI humbly request you to do the needful in this regard and I shall be grateful to you \r\nfor the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully\r\n\r\n\r\n\r\n<strong>Employee11</strong>\r\nMob: 6366734672\r\n', 0, NULL, '2025-12-11 09:10:51'),
(17, 10, 'employee', '1', 'Employee11 - Document', '<div style=\"display:flex; justify-content:space-between; width:100%;\">\r\n  <div>To</div>\r\n  <div>Date: 11-12-2025</div>\r\n</div>\r\n\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\nSub:        <strong>Request for resignation</strong>\r\n\r\nDear Sir,\r\n\r\n        With due respect, <strong>Employee11</strong> (<strong>EMIRATES ID NO: EM7234623</strong>) have been working in your esteemed \r\norganization as a <strong>Position</strong> for 3 years. <strong>My visa is expired to the \r\ndate of 12-10-2025</strong>. I regret to inform you that I hereby tender my resignation \r\n(<strong>30-12-2025</strong>) due to some serious family problem. So, please consider \r\nthis letter as my prior notice for the same and send me back after settle my final accounts.\r\n\r\nI humbly request you to do the needful in this regard and I shall be grateful to you \r\nfor the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully\r\n\r\n\r\n<strong>Employee11</strong>\r\nMob: 6366734672\r\n', 0, NULL, '2025-12-11 09:17:48'),
(18, 9, 'employee', '1', 'Employee11 - Document', 'To\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\n                                                                                 Date: 11-12-2025\r\n\r\nSub:        <strong>Request for resignation</strong>\r\n\r\nDear Sir,\r\n\r\n        With due respect, <strong>Employee11</strong>  (<strong>EMIRATES ID NO: EM36363636</strong>) have been working in your esteemed organization as a <strong>Position</strong> for 3 years. <strong>My visa is expired to the \r\ndate of 11-10-2025</strong>. I regret to inform you that I hereby tender my resignation \r\n(<strong>10-12-2025</strong>) due to some serious family problem. So, please consider \r\nthis letter as my prior notice for the same and send me back after settle my final accounts.\r\n\r\nI humbly request you to do the needful in this regard and I shall be grateful to you \r\nfor the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully\r\n\r\n\r\n\r\n<strong>Employee11</strong>\r\nMob: 6366734672\r\n', 0, NULL, '2025-12-11 09:18:48'),
(19, 11, 'employee', '1', 'Employee11 - Document', '\r\nTo\r\nThe Manager\r\nALNASR GENERAL SERVICE EST\r\nABU DHABI – UAE\r\n\r\nDate: 11-12-2025\r\n\r\nSub: <strong>Request for resignation</strong>\r\n\r\nDear Sir,\r\n\r\n<div style=\"text-align: justify;\">With due respect, I, <strong>Employee11</strong> (Emirates ID No: <strong>EM34767373633</strong>), have been working in your esteemed organization as a <strong>Position</strong> for <strong>3</strong> years. My visa is expiring on <strong>11-11-2025</strong>. I regret to inform you that I hereby tender my resignation effective from <strong>10-11-2025</strong> due to some serious family problems. Therefore, please consider this letter as my prior notice and kindly process my final settlement.</div>\r\n\r\nI humbly request you to do the needful in this regard, and I shall be grateful to you for the same.\r\n\r\nThanking you,\r\n\r\nYours faithfully,\r\n\r\n\r\n\r\n\r\n<strong>Employee11</strong>\r\nMob: 6366734672\r\n', 0, NULL, '2025-12-11 09:24:31'),
(20, 12, 'employee', '1', 'Employee11 - Document', 'Ref No: REF1223123\r\nDate: 11-12-2025\r\n\r\nTo,\r\n\r\nMr. EmployeeName\r\nEmp. ID: 2767\r\nPassport No: PASSMD78IP\r\nNationality: Indian\r\n\r\nSubject: - <strong>Warning Letter For Absenteeism</strong>\r\n\r\nAttention: <strong>EmployeeName</strong>\r\n\r\nI am writing to bring to your attention that you have been absent from the workplace\r\nevery week. Please understand that this absence directly impacts the productivity of our\r\norganization and we are receiving complaints from the clients that it affects their\r\nregular site work as well.\r\n\r\nThis is the official warning letter from the management regarding your absenteeism at\r\nwork, as we have received complaint from your site manager.\r\nWe hereby warn you not to repeat such incidence in future, or else the company will\r\ntake necessary action further. The absenteeism will result of\r\n<strong>AED 20/- deduction per day</strong> which will be affected in your monthly pay.\r\n\r\nWe kindly request that you contact us by the next working day upon receiving this letter\r\nand give us an explanation on this regard.\r\n\r\nThanks & Regards,\r\n\r\n<strong>Manager</strong>  \r\nOperation Manager  \r\nM/s. Alnasr General Services Est.\r\n', 0, NULL, '2025-12-11 09:38:29'),
(21, 13, 'employee', '1', 'Employee11 - Document', '\r\nRef No: <strong>REF42542626</strong>\r\nDate: <strong>11-12-2025</strong>\r\n\r\nTo,\r\nMr. <strong>Employee11</strong>\r\nEmp. ID: <strong>2760</strong>\r\nPassport No: <strong>p2377324</strong>\r\nNationality: <strong>Indian</strong>\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Warning Letter For Absenteeism</u></strong>\r\n</div>\r\nAttention: <strong>Employee11</strong>\r\n<div style=\"display:block; text-align:justify;\">\r\nI am writing to bring to your attention that you have been absent from the workplace every week. Please understand that this absence directly impacts the productivity of our organization and we are receiving complaints from the clients that it affects their regular site work as well.\r\n</div><div style=\"display:block; text-align:justify;\">\r\nThis is the official warning letter from the management regarding your absenteeism at work, as we have received complaint from your site manager. We hereby warn you not to repeat such incidence in future, or else the company will take necessary action further. The absenteeism will result of <strong>AED 20/- deduction per day</strong> which will be affected in your monthly pay.\r\n</div><div style=\"display:block; text-align:justify;\">\r\nWe kindly request that you contact us by the next working day upon receiving this letter and give us an explanation on this regard.\r\n</div>\r\n\r\nThanks & Regards,\r\n\r\n\r\n\r\n\r\nMnaagerX\r\nOperation Manager\r\nM/s. Alnasr General Services Est.\r\n', 0, NULL, '2025-12-11 09:59:52'),
(22, 13, 'employee', '1', 'Employee11 - Document', '\r\nRef No: <strong>REF6262</strong>\r\nDate: <strong>11-12-2025</strong>\r\n\r\nTo,\r\nMr. <strong>Employee11</strong>\r\nEmp. ID: <strong>2760</strong>\r\nPassport No: <strong>p2377324</strong>\r\nNationality: <strong>Indian</strong>\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Warning Letter For Absenteeism</u></strong>\r\n</div>\r\nAttention: <strong>Employee11</strong>\r\n<div style=\"display:block; text-align:justify;\">\r\nI am writing to bring to your attention that you have been absent from the workplace every week. Please understand that this absence directly impacts the productivity of our organization and we are receiving complaints from the clients that it affects their regular site work as well.</div><div style=\"display:block; text-align:justify;\">\r\nThis is the official warning letter from the management regarding your absenteeism at work, as we have received complaint from your site manager. We hereby warn you not to repeat such incidence in future, or else the company will take necessary action further. The absenteeism will result of <strong>AED 25/- deduction per day</strong> which will be affected in your monthly pay. </div><div style=\"display:block; text-align:justify;\">\r\nWe kindly request that you contact us by the next working day upon receiving this letter and give us an explanation on this regard.\r\n</div>\r\n\r\nThanks & Regards,\r\n\r\n\r\n\r\n\r\nManhggsgr\r\nOperation Manager\r\nM/s. Alnasr General Services Est.\r\n', 0, NULL, '2025-12-11 10:01:12'),
(23, 15, 'employee', '1', 'Employee11 - Document', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>Employee11</strong>                     BADGE#: <strong>2760</strong>\r\nCATEGORY: <strong>CategoryX</strong>            NATIONALITY: <strong>Indian</strong>\r\nLOCATION: <strong>Location</strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Request For Annual / Sick / Emergency Leave</u></strong>\r\n</div>\r\n\r\nDear Sir,\r\nPlease grant me Annual/Sick/Emergency Leave for <strong>10</strong> Days maximum with effect from <strong>12-10-2025</strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong>11-10-2025</strong>\r\nReturn Date: <strong>12-12-2025</strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong>Addressjhjhhj</strong>\r\n<strong>Addresjknjkn</strong>\r\n\r\nTelephone No. in Home Country: <strong>672342364</strong>\r\n\r\nOffice Use Only\r\n\r\n<div style=\"display:flex; justify-content:space-between; width:100%;\">\r\n  <div>Last Leave Date: 12-06-2025</div>\r\n  <div>From: 12-10-2025</div>\r\n  <div>To: 12-10-2025</div>\r\n  <div>Total Days: 14</div>\r\n</div>\r\nWP Card Exp: <strong>12-10-2025</strong>\r\nVisa Exp: <strong>12-10-2025</strong>\r\n\r\n\r\n\r\nSignature of Applicant:\r\n<strong>Name</strong>\r\n\r\nApproved:\r\n\r\n\r\nGeneral Manager\r\n\r\n', 0, NULL, '2025-12-11 10:20:25'),
(24, 15, 'employee', '1', 'Employee11 - Document', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>Employee11</strong>                     BADGE#: <strong>2760</strong>\r\nCATEGORY: <strong>dsgfsdfg</strong>            NATIONALITY: <strong>Indian</strong>\r\nLOCATION: <strong>sdfsdf</strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Request For Annual / Sick / Emergency Leave</u></strong>\r\n</div>\r\n\r\nDear Sir,\r\nPlease grant me Annual/Sick/Emergency Leave for <strong>23</strong> Days maximum with effect from <strong>11-12-2025</strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong>11-12-2025</strong>\r\nReturn Date: <strong>11-12-2025</strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong>sdfsdfsdfsdfsd</strong>\r\n<strong>ffsdfsdfsdfgdfhfgh</strong>\r\n\r\nTelephone No. in Home Country: <strong>34234234234</strong>\r\n\r\nOffice Use Only\r\n\r\nLast Leave Date: 11-12-2025    From: 11-12-2025    To: 11-12-2025    Total Days: 32\r\n\r\nWP Card Exp: <strong>11-12-2025</strong>\r\nVisa Exp: <strong>11-12-2025</strong>\r\n\r\n\r\n\r\nSignature of Applicant:\r\n<strong>Namem</strong>\r\n\r\nApproved:\r\n\r\n\r\nGeneral Manager\r\n\r\n', 0, NULL, '2025-12-11 10:22:25'),
(25, 15, 'employee', '1', 'Employee11 - Document', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>Employee11</strong>                     BADGE#: <strong>2760</strong>\r\nCATEGORY: <strong>dsfsdfsdf</strong>            NATIONALITY: <strong>Indian</strong>\r\nLOCATION: <strong>11-12-2025</strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Request For Annual / Sick / Emergency Leave</u></strong>\r\n</div>\r\n\r\nDear Sir,\r\nPlease grant me Annual/Sick/Emergency Leave for <strong>34</strong> Days maximum with effect from <strong>11-12-2025</strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong>11-12-2025</strong>\r\nReturn Date: <strong>11-12-2025</strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong>fyhjfgvjhgfvjhg</strong>\r\n<strong>jhgfvjhgfv</strong>\r\n\r\nTelephone No. in Home Country: <strong>jhgvjhmgv</strong>\r\n\r\nOffice Use Only\r\n\r\nLast Leave Date: 11-12-2025 | From: 11-12-2025 | To: 11-12-2025 | Total Days: 23\r\n\r\nWP Card Exp: <strong>11-12-2025</strong>\r\nVisa Exp: <strong>11-12-2025</strong>\r\n\r\n\r\n\r\nSignature of Applicant:\r\n<strong>Employee11</strong>\r\n\r\nApproved:\r\n\r\n\r\nGeneral Manager\r\n\r\n', 0, NULL, '2025-12-11 10:24:40'),
(26, 15, 'employee', '1', 'Employee11 - Document', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>Employee11</strong>                     BADGE#: <strong>2760</strong>\r\nCATEGORY: <strong></strong>            NATIONALITY: <strong>Indian</strong>\r\nLOCATION: <strong></strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Request For Annual / Sick / Emergency Leave</u></strong>\r\n</div>\r\n\r\nDear Sir,\r\nPlease grant me Annual/Sick/Emergency Leave for <strong></strong> Days maximum with effect from <strong></strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong></strong>\r\nReturn Date: <strong></strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong></strong>\r\n<strong></strong>\r\n\r\nTelephone No. in Home Country: <strong></strong>\r\n\r\nOffice Use Only\r\n\r\nLast Leave Date:  | From:  | To:  | Total Days: \r\n\r\nWP Card Exp: <strong></strong>\r\nVisa Exp: <strong></strong>\r\n\r\n<table style=\"width:100%; border-collapse:collapse;\"><tr><td style=\"vertical-align:top; width:50%;\">Signature of Applicant:<br><br><strong>Employee11</strong></td><td style=\"vertical-align:top; text-align:right; width:50%;\">Approved:<br><br>General Manager</td></tr></table>\r\n\r\n', 0, NULL, '2025-12-11 10:33:36'),
(27, 15, 'employee', '1', 'Employee11 - Document', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>Employee11</strong>                     BADGE#: <strong>2760</strong>\r\nCATEGORY: <strong></strong>            NATIONALITY: <strong>Indian</strong>\r\nLOCATION: <strong></strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Request For Annual / Sick / Emergency Leave</u></strong>\r\n</div>\r\n\r\nDear Sir,\r\nPlease grant me Annual/Sick/Emergency Leave for <strong></strong> Days maximum with effect from <strong></strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong></strong>\r\nReturn Date: <strong></strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong></strong>\r\n<strong></strong>\r\n\r\nTelephone No. in Home Country: <strong></strong>\r\n\r\nOffice Use Only\r\n\r\nLast Leave Date:  | From:  | To:  | Total Days: \r\n\r\nWP Card Exp: <strong></strong>\r\nVisa Exp: <strong></strong>\r\n\r\n<table style=\"width:100%; border-collapse:collapse;\"><tr><td style=\"vertical-align:top; width:50%;\">Signature of Applicant:<br><br>\r\n<strong>Employee11</strong></td><td style=\"vertical-align:top; text-align:right; width:50%;\">Approved:<br><br>\r\nGeneral Manager</td></tr></table>\r\n\r\n', 0, NULL, '2025-12-11 10:34:42'),
(28, 15, 'employee', '1', 'Employee11 - Document', '\r\n<div style=\"text-align:center; font-weight:bold; font-size:16px;\">\r\n  LEAVE REQUEST FORM\r\n</div>\r\n\r\nNAME: <strong>Employee11</strong>                     BADGE#: <strong>2760</strong>\r\nCATEGORY: <strong>Cartteykjh</strong>            NATIONALITY: <strong>Indian</strong>\r\nLOCATION: <strong>Lociauygug</strong>\r\n\r\nTO:\r\nThe Personal Manager\r\nAl Nasr General Services Est.\r\nAbu Dhabi, UAE\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Request For Annual / Sick / Emergency Leave</u></strong>\r\n</div>\r\nDear Sir,\r\nPlease grant me Annual/Sick/Emergency Leave for <strong>34</strong> Days maximum with effect from <strong>11-12-2025</strong>.\r\nI do hereby undertake to return to the Company and report for duty within the agreed period,\r\notherwise the Company reserves the full right to take appropriate legal measures against me\r\nas deemed fit.\r\n\r\nLast Day of Work: <strong>11-12-2025</strong>\r\nReturn Date: <strong>11-12-2025</strong>\r\n\r\nAddress of Home Country (as per passport):\r\n<strong>sdfsdf</strong>\r\n<strong>gfhfghfg</strong>\r\n\r\nTelephone No. in Home Country: <strong>324234234</strong>\r\n\r\nOffice Use Only\r\n\r\nLast Leave Date: 11-12-2025 | From: 11-12-2025 | To: 11-12-2025 | Total Days: 23\r\n\r\nWP Card Exp: <strong>11-12-2025</strong>\r\nVisa Exp: <strong>11-12-2025</strong>\r\n\r\n<table style=\"width:100%; border-collapse:collapse;\"><tr><td style=\"vertical-align:top; width:50%;\">Signature of Applicant:<br><br>\r\n\r\n<strong>Employee11</strong></td><td style=\"vertical-align:top; text-align:right; width:50%;\">Approved:<br><br>\r\n\r\nGeneral Manager</td></tr></table>\r\n\r\n', 0, NULL, '2025-12-11 10:36:32'),
(29, 17, 'employee', '1', 'Employee11 - Document', '\r\nRef No: <strong></strong>\r\nDate: <strong>11-12-2025</strong>\r\n\r\nTo,\r\n\r\nMr. <strong>Employee11</strong>\r\nPassport No: <strong>p2377324</strong>\r\nMob: <strong></strong>\r\n<div style=\"margin-top:10px;\"><strong>Subject: Offer Letter</strong></div>\r\nDear Mr. <strong>Employee11</strong>,\r\n<div style=\"display:block; text-align:justify;\">Further to the evaluation of your CV, we are pleased to offer you the following position in our organization on the below mentioned terms & conditions:</div><table style=\"width:100%; border-collapse:collapse; margin-top:10px;\"><tr><td style=\"width:40%;\">Job Title</td><td><strong></strong></td></tr><tr><td>Basic Salary</td><td>AED: /-</td></tr><tr><td>Additional Allowances</td><td>AED: /-</td></tr><tr><td>Total Offered Salary</td><td><strong>AED: /-</strong></td></tr><tr><td>Location</td><td></td></tr><tr><td>Probation Period</td><td></td></tr><tr><td>Contract Duration</td><td></td></tr><tr><td>Working Hours</td><td></td></tr><tr><td>Leave Salary</td><td></td></tr><tr><td>End of Service Benefit</td><td></td></tr><tr><td>Medical</td><td>pending</td></tr></table>\r\n<div style=\"display:block; text-align:justify;\">All other terms and conditions of services shall be in accordance with UAE Labour Law.\r\nThe individual contracts will be signed after obtaining the employment visas only.</div>\r\n<div style=\"display:block; text-align:justify;\">Please also note that, your qualification and experience is considered as true and fair as stated in your CV. Any untrue or misstatement in the CV will be considered as breach of basic criteria upon which your selection is made.</div>\r\n<div style=\"display:block; text-align:justify;\">In case of resignation by the employee during the initial 2-year period, the employee shall bear all the training charges.</div>\r\n<div style=\"display:block; text-align:justify;\">If you agree with the above terms and conditions, please sign in the space provided and send us back immediately.</div>\r\nBest Regards,\r\n\r\n\r\n\r\n<table style=\"width:100%; border-collapse:collapse; margin-top:20px;\"><tr><!-- LEFT: Employer Section --><td style=\"width:50%; vertical-align:top;\"><strong></strong><br>Seal and signature of the Employer</td><!-- RIGHT: Employee Section --><td style=\"width:50%; text-align:right; vertical-align:top;\">Name and Signature of the Employee<br><br>I accept above terms and conditions<br>Date: ______________________</td></tr></table>\r\n\r\n', 0, NULL, '2025-12-11 11:01:09'),
(30, 17, 'employee', '1', 'Employee11 - Document', '\r\nRef No: <strong></strong>\r\nDate: <strong>11-12-2025</strong>\r\n\r\nTo,\r\n\r\nMr. <strong>Employee11</strong>\r\nPassport No: <strong>p2377324</strong>\r\nMob: <strong></strong>\r\n<div style=\"margin-top:10px;\"><strong>Subject: Offer Letter</strong></div>\r\nDear Mr. <strong>Employee11</strong>,\r\n<div style=\"display:block; text-align:justify;\">Further to the evaluation of your CV, we are pleased to offer you the following position in our organization on the below mentioned terms & conditions:</div>\r\n<table style=\"width:100%; border-collapse:collapse; margin-top:10px;\"><tr><td style=\"width:40%;\">Job Title</td><td><strong></strong></td></tr><tr><td>Basic Salary</td><td>AED: /-</td></tr><tr><td>Additional Allowances</td><td>AED: /-</td></tr><tr><td>Total Offered Salary</td><td><strong>AED: /-</strong></td></tr><tr><td>Location</td><td></td></tr><tr><td>Probation Period</td><td></td></tr><tr><td>Contract Duration</td><td></td></tr><tr><td>Working Hours</td><td></td></tr><tr><td>Leave Salary</td><td></td></tr><tr><td>End of Service Benefit</td><td></td></tr><tr><td>Medical</td><td>pending</td></tr></table>\r\n<div style=\"display:block; text-align:justify;\">All other terms and conditions of services shall be in accordance with UAE Labour Law.\r\nThe individual contracts will be signed after obtaining the employment visas only.</div><div style=\"display:block; text-align:justify;\">Please also note that, your qualification and experience is considered as true and fair as stated in your CV. Any untrue or misstatement in the CV will be considered as breach of basic criteria upon which your selection is made.</div><div style=\"display:block; text-align:justify;\">In case of resignation by the employee during the initial 2-year period, the employee shall bear all the training charges.</div><div style=\"display:block; text-align:justify;\">If you agree with the above terms and conditions, please sign in the space provided and send us back immediately.</div>\r\nBest Regards,\r\n\r\n\r\n\r\n<table style=\"width:100%; border-collapse:collapse; margin-top:20px;\"><tr><!-- LEFT: Employer Section --><td style=\"width:50%; vertical-align:top;\"><strong></strong><br>Seal and signature of the Employer</td><!-- RIGHT: Employee Section --><td style=\"width:50%; text-align:right; vertical-align:top;\">Name and Signature of the Employee<br><br>I accept above terms and conditions<br>Date: ______________________</td></tr></table>\r\n\r\n', 0, NULL, '2025-12-11 11:02:55'),
(31, 13, 'employee', '1', 'Employee11 - Document', '\r\nRef No: <strong></strong>\r\nDate: <strong>15-12-2025</strong>\r\n\r\nTo,\r\nMr. <strong>Employee11</strong>\r\nEmp. ID: <strong>2760</strong>\r\nPassport No: <strong>p2377324</strong>\r\nNationality: <strong>Indian</strong>\r\n<div style=\"width: 100%; text-align: center;\">\r\n  <strong><u>Subject: - Warning Letter For Absenteeism</u></strong>\r\n</div>\r\nAttention: <strong>Employee11</strong>\r\n<div style=\"display:block; text-align:justify;\">\r\nI am writing to bring to your attention that you have been absent from the workplace every week. Please understand that this absence directly impacts the productivity of our organization and we are receiving complaints from the clients that it affects their regular site work as well.</div><div style=\"display:block; text-align:justify;\">\r\nThis is the official warning letter from the management regarding your absenteeism at work, as we have received complaint from your site manager. We hereby warn you not to repeat such incidence in future, or else the company will take necessary action further. The absenteeism will result of <strong>AED /- deduction per day</strong> which will be affected in your monthly pay. </div><div style=\"display:block; text-align:justify;\">\r\nWe kindly request that you contact us by the next working day upon receiving this letter and give us an explanation on this regard.\r\n</div>\r\n\r\nThanks & Regards,\r\n\r\n\r\n\r\n\r\n\r\nOperation Manager\r\nM/s. Alnasr General Services Est.\r\n', 1, NULL, '2025-12-15 06:54:05');

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
  `label` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `file_type` enum('pdf','image') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `expiry_date` date DEFAULT NULL,
  `created_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_documents`
--

INSERT INTO `customers_documents` (`id`, `customer_id`, `label`, `file_name`, `file_type`, `expiry_date`, `created_by`, `created_at`) VALUES
(1, 2, 'Experience', '1762338667_sample.pdf', 'pdf', NULL, 'Nithin', '2025-11-05 10:31:07'),
(2, 2, 'Residence ID', '1762354987_Sample-png-Image-for-Testing.png', 'image', NULL, 'Nithin', '2025-11-05 15:03:07'),
(4, 2, 'Test ID', '1762441601_Sample.png', 'image', '2025-11-28', 'Nithin', '2025-11-06 15:06:41'),
(8, 2, 'TestId', '1763017497_sample.pdf', 'pdf', '2025-12-25', 'Nithin1', '2025-11-13 07:04:57'),
(6, 2, 'idcard', '1762519750_bc-15.jpg', 'image', NULL, 'Nithin', '2025-11-07 12:49:10'),
(9, 2, 'TestId', '1763017515_sample.pdf', 'pdf', '2025-12-24', 'Nithin1', '2025-11-13 07:05:15'),
(10, 1, 'Promotion Letter', 'doc_19_1765797108.pdf', 'pdf', NULL, 'Nithin1', '2025-12-15 11:11:48'),
(11, 1, 'sdfsdg333', 'doc_22_1765800274.jpg', 'image', NULL, 'Nithin1', '2025-12-15 12:04:34'),
(12, 1, 'sdfsdg333', 'doc_23_1765800308.jpg', 'image', NULL, 'Nithin1', '2025-12-15 12:05:08'),
(13, 2, 'Insurance Card', 'doc_26_1765804561.jpg', 'image', NULL, 'Nithin1', '2025-12-15 13:16:01'),
(14, 2, 'Document', 'doc_44_1765888929.pdf', 'pdf', NULL, 'Nithin1', '2025-12-16 12:42:09');

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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_invoices`
--

INSERT INTO `customers_invoices` (`id`, `customer_id`, `invoice_date`, `due_date`, `invoice_amount`, `vat_amount`, `type`, `category`, `payment_status`, `document`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-11-10', '2025-11-26', 24.00, 0.00, 'Received', 'Sim Card', 'Unpaid', 'inv_690df81d1a6ce.pdf', 'test3', 2, '2025-11-07 19:16:05', '2025-11-19 10:52:50'),
(2, 2, '2025-11-19', '2025-11-26', 20.00, 0.00, 'Received', 'Newcat4', 'Unpaid', '', 'Invoice for AED 20 - Newcat4.', 2, '2025-11-07 19:37:14', '2025-11-24 14:17:30'),
(3, 3, '2025-11-14', '2025-12-29', 20.00, 10.00, '', '', 'Paid', 'inv_690e2ed842edd.pdf', 'this is test invoice', 2, '2025-11-07 23:09:21', '2025-12-22 21:20:14'),
(5, 2, '2025-11-15', '2025-11-26', 12.00, 0.00, 'Received', 'Cat35', 'Unpaid', '', 'Invoice for AED 12 - Cat35.', 2, '2025-11-14 23:04:02', '2025-11-19 10:52:41'),
(6, 2, '2025-11-16', '2025-11-26', 33.99, 0.00, 'Sent', 'Sim Card', 'Unpaid', '', 'Invoice for AED 34 - newcat.', 2, '2025-11-14 23:44:36', '2025-11-19 10:52:39'),
(7, 2, '2025-11-15', '2025-11-26', 34.00, 0.00, 'Received', 'icat7', 'Unpaid', '', 'Invoice for AED 34 - Paperwork.', 2, '2025-11-15 14:11:19', '2025-11-19 10:52:55'),
(8, 0, '2025-11-16', '2025-11-26', 124.00, 0.00, '', 'newcat2', 'Unpaid', 'inv_691864c3774b1.jpg', 'Invoice for AED 123 - newcat2.', 2, '2025-11-15 17:01:25', '2025-11-19 11:17:15'),
(9, 2, '2025-11-18', '2025-11-30', 24.00, 0.00, 'Sent', '', 'Unpaid', '', 'Invoice for AED 24.', 2, '2025-11-19 11:10:48', '2025-11-19 11:10:48'),
(10, 1, '2025-11-24', '2025-11-21', 34.02, 0.00, 'Received', 'new76', 'Unpaid', '', 'Invoice for AED 34 - New63 updated.', 2, '2025-11-24 10:27:20', '2025-11-24 14:17:26'),
(11, 4, '2025-11-24', '2025-11-30', 36.00, 0.00, 'Received', 'Newcat4', 'Unpaid', 'inv_69242771bbba80.19263326.pdf', 'Invoice for AED 36.', 2, '2025-11-24 14:39:16', '2025-11-24 15:07:53'),
(12, 2, '2025-12-22', '2025-12-29', 23.00, 0.00, 'Sent', '', 'Unpaid', '', 'Invoice for AED 23.', 2, '2025-12-22 17:12:16', '2025-12-22 17:12:16'),
(13, 3, '2025-12-22', '2025-12-29', 25.00, 1.01, 'Received', '', 'Unpaid', '', 'Invoice for AED 25.', 2, '2025-12-22 21:17:23', '2025-12-22 21:25:15'),
(14, 3, '2025-12-22', '2025-12-29', 52.00, 2.60, 'Received', '', 'Unpaid', '', 'Invoice for AED 52.', 2, '2025-12-22 21:29:30', '2025-12-24 13:25:37');

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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(30, 1, NULL, 'Naren Jayabalu', 'Status changed from \'Active\' to \'Work in progress\'.\n\nNote: tetsttst', 'General', 'Public', '2025-11-07 12:26:58'),
(31, 2, NULL, 'Nithin1', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-09 23:42:49'),
(32, 2, NULL, 'Nithin1', 'Email Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-09 23:43:00'),
(33, 3, NULL, 'Nithin1', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-12-10 06:16:37'),
(34, 3, NULL, 'Nithin1', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-12-10 06:16:37'),
(35, 2, NULL, 'Nithin1', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-12-29 10:57:18'),
(36, 3, NULL, 'Nithin1', 'Status changed from \'Suspect\' to \'Active\'.\n\nNote: dfgdf', 'General', 'Public', '2025-12-30 09:58:40');

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
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_payments`
--

INSERT INTO `customers_payments` (`id`, `customer_id`, `type`, `category`, `invoice_date`, `payment_status`, `invoice_amount`, `invoice_partial`, `invoice_payment_method`, `reclaim_by`, `reimbursable`, `card_last4`, `cheque_bank`, `cheque_issuer`, `reimbursement_amount`, `document`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 2, 'Expense', 'Paperwork', '2025-11-18', 'Paid', 20.00, 0.00, 'Cheque', 'Company', 'No', '4534', '', '', 0.00, '[]', '', 2, '2025-11-17 17:36:08', '2025-11-19 11:53:00'),
(5, 1, 'Income', 'newcat2', '2025-11-18', 'Unpaid', 30.00, 0.00, '', 'Company', 'No', '3453', '', '', 0.00, '[]', 'Paid AED 30 for Medical using Bank Transfer.', 2, '2025-11-17 20:06:45', '2025-11-18 11:24:29'),
(3, 2, 'Expense', '', '2025-11-18', 'Paid', 10.00, 0.00, 'Card', 'Company', 'Yes', '3455', 'dsdf', '234', 10.00, '[]', 'Received AED 10 for newcat2 using Cheque.', 2, '2025-11-15 16:47:51', '2025-11-19 11:38:39'),
(6, 2, 'Income', 'newcat4', '2025-11-19', 'Unpaid', 30.00, 0.00, '', '0', 'No', '', '', '', 0.00, '[]', '', 2, '2025-11-19 11:37:11', '2025-11-20 14:01:48'),
(7, 4, 'Expense', 'Sim Expire Fine', '2025-11-20', 'Unpaid', 10.00, 0.00, '', '0', 'No', '', '', '', 0.00, '[]', 'Pending receipt of AED 10 for newcat45.', 2, '2025-11-20 14:04:57', '2025-11-27 18:13:38'),
(8, 3, 'Income', 'Invoice', '2025-12-31', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' Invoice No: 5 Reference number: REF5235', 2, '2026-01-07 12:46:17', '2026-01-07 14:16:17'),
(9, 3, 'Income', 'Invoice', '2025-12-31', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, ' Invoice No: 5 Reference number: Invoice Payment Received', 2, '2026-01-07 12:46:51', '2026-01-07 14:16:51'),
(10, 3, 'Income', 'Invoice', '2025-12-31', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Invoice Payment Received1 Invoice No: 5 Reference number: REF26637', 2, '2026-01-07 12:47:33', '2026-01-07 14:17:33'),
(11, 3, 'Income', 'Invoice', '2025-12-31', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Invoice Payment Received1 Invoice No: 5 Reference number: REF26637', 2, '2026-01-07 12:47:55', '2026-01-07 14:17:55'),
(12, 3, 'Income', 'Invoice', '2025-12-31', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Invoice Payment Received Invoice No: 5', 2, '2026-01-07 12:51:39', '2026-01-07 14:21:39'),
(13, 3, 'Income', 'Invoice', '2025-12-31', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Invoice Payment Received Invoice No: 5 Reference number: ryyy', 2, '2026-01-07 12:51:56', '2026-01-07 14:21:56'),
(14, 3, 'Income', 'Invoice', '2025-12-31', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Invoice Payment Received2 Invoice No: 5 Reference number: Ref63343', 2, '2026-01-07 12:58:33', '2026-01-07 14:28:33'),
(15, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Invoice Payment Received Invoice No: 6', 2, '2026-01-07 13:00:09', '2026-01-07 14:30:09'),
(16, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Invoice Payment Received3 Invoice No: 100/2026 Reference number: ref53', 2, '2026-01-07 13:35:36', '2026-01-07 15:05:36'),
(17, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 2000.00, 2000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Reference number: dfgfdg', 2, '2026-01-07 13:36:51', '2026-01-07 15:06:51'),
(18, 3, 'Income', 'Invoice', '2026-01-07', '', 2216.47, 216.47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: Pr553', 2, '2026-01-07 13:48:32', '2026-01-07 15:18:32'),
(19, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received balance Invoice No: 100/2026 Ref: refss', 2, '2026-01-07 14:00:17', '2026-01-07 15:30:17'),
(20, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 2216.47, 216.47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: p1', 2, '2026-01-07 14:07:59', '2026-01-07 15:37:59'),
(21, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: p11', 2, '2026-01-07 14:12:55', '2026-01-07 15:42:55'),
(22, 3, 'Income', 'Invoice', '2026-01-07', '', 2000.00, 216.47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received p1 Invoice No: 100/2026 Ref: p1', 2, '2026-01-07 14:17:43', '2026-01-07 15:47:43'),
(23, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 216.47, 216.47, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received p2 Invoice No: 100/2026 Ref: p2', 2, '2026-01-07 14:19:34', '2026-01-07 15:49:34'),
(24, 3, 'Income', 'Invoice', '2026-01-07', 'Partial Paid', 2000.00, 2000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: p1', 2, '2026-01-07 14:21:42', '2026-01-07 15:51:42'),
(25, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: p2', 2, '2026-01-07 14:22:41', '2026-01-07 15:52:41'),
(26, 3, 'Income', 'Invoice', '2026-01-07', 'Partial Paid', 2000.00, 2000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: ref1234', 2, '2026-01-07 15:40:00', '2026-01-07 17:10:00'),
(27, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: ref1235', 2, '2026-01-07 15:40:15', '2026-01-07 17:10:15'),
(28, 3, 'Income', 'Invoice', '2026-01-07', 'Partial Paid', 2000.00, 2000.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: r1234', 2, '2026-01-07 15:41:51', '2026-01-07 17:11:51'),
(29, 3, 'Income', 'Invoice', '2026-01-05', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026 Ref: ref44', 2, '2026-01-07 17:12:02', '2026-01-07 18:42:02'),
(30, 3, 'Income', 'Invoice', '2026-01-07', 'Paid', 2216.47, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'Timesheet Invoice Payment Received Invoice No: 100/2026', 2, '2026-01-07 17:17:21', '2026-01-07 18:47:21');

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
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(26, 2, 'QTN2511062820', 4, 'Test Quotationwe', '2025-11-07', 'Manager', 'This is a subjectnew3', 'This si smesahguy23ut64q', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation22', 'draft', 'Nithin', '2025-11-07 12:43:16', '2025-11-07 12:43:16'),
(27, 2, 'QTN2511062820', 5, 'Test Quotationwe', '2025-11-14', 'Manager', 'This is a subject', 'This si smesahguy23ut64q', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation22', 'draft', 'Nithin1', '2025-11-14 05:43:06', '2025-11-14 05:43:06');

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
) ENGINE=InnoDB AUTO_INCREMENT=98 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(64, 2, '2025-11-14 10:00:00', 'Email', 2, 'ytuytu89xt1', '2025-11-07 10:19:57', '2025-11-07 11:17:15', 0),
(65, 2, '2025-11-25 10:00:00', 'General', NULL, 'Document TestId expiry on 25 Dec 2025', '2025-11-13 07:05:15', NULL, 0),
(66, 2, '2025-12-01 10:00:00', 'Email', 1, 'tes5666', '2025-11-24 10:25:32', '2025-11-24 16:07:54', 1),
(67, 2, '2025-12-24 10:00:00', 'Callback', NULL, 'fdghjhds334', '2025-11-24 10:38:21', '2025-11-24 11:03:33', 0),
(68, 2, '2025-12-01 10:00:00', 'Email', 1, 'tes663', '2025-11-24 11:03:52', '2025-11-24 16:34:08', 1),
(69, 4, '2025-12-05 10:00:00', 'Email', NULL, 'test56', '2025-12-04 12:58:53', '2025-12-04 13:05:33', 0),
(93, 2, '2025-12-09 13:48:06', 'General', 2, 'Channel: Phone. Scenario: Customer Request. Assigned to: Nithin1. Priority: normal.', '2025-12-19 16:21:01', '2025-12-30 13:48:08', 0),
(94, 2, '2025-12-02 13:48:02', 'General', 2, 'Channel: Phone. Scenario: Customer Request. Assigned to: Nithin1. Priority: normal.', '2025-12-19 16:23:35', '2025-12-30 13:48:04', 0),
(95, 2, '2025-12-23 13:47:56', 'General', 2, 'Channel: Phone. Scenario: Customer Request. Assigned to: Nithin1. Priority: normal.', '2025-12-22 09:31:00', '2025-12-30 13:48:00', 0),
(96, 2, '2025-12-25 07:15:00', 'General', 2, 'Channel: Phone. Scenario: Customer Request. Assigned to: Nithin1. Priority: normal.', '2025-12-22 09:41:50', NULL, 0),
(97, 2, '2025-12-31 17:45:00', 'General', 2, 'Channel: Phone. Scenario: Customer Request. Assigned to: Nithin1. Priority: normal.', '2025-12-30 12:15:32', NULL, 0);

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
) ENGINE=MyISAM AUTO_INCREMENT=26 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_requirements`
--

INSERT INTO `customers_requirements` (`id`, `customer_id`, `job_title`, `num_employees`, `rate_pay`, `start_date`, `expiry`, `expiry_alert`, `req_type`, `accommodation`, `transport`, `overtime`, `created_by`, `created_at`, `attachment`, `accommodation_details`, `transport_details`, `overtime_policies`) VALUES
(1, 2, 'General Helper', 2, 14.01, '2025-11-06', NULL, 0, 'Enquiry', 1, 0, 0, 'Nithin', '2025-11-05 10:06:58', NULL, NULL, NULL, NULL),
(2, 2, 'Security Guard', 6, 25.00, '2025-11-13', NULL, 0, 'Enquiry', 1, 0, 0, 'Nithin', '2025-11-05 10:08:05', NULL, NULL, NULL, NULL),
(3, 2, 'Construction Worker', 5, 25.00, '2025-10-29', NULL, 0, 'Enquiry', 1, 1, 1, 'Nithin', '2025-11-07 09:06:22', 'req_690e03e69abdb.pdf', 'adetails', 'tdetails', '[{\"rate\": \"29\", \"policy\": \"p1\"}, {\"rate\": \"23\", \"policy\": \"p2\"}]'),
(4, 2, 'Test Job', 5, 25.00, '2025-10-26', NULL, 0, 'Enquiry', 1, 0, 1, 'Nithin', '2025-11-07 09:25:42', 'req_690e13ac368de.jpg', 'adetails', '', '[{\"rate\": \"29\", \"policy\": \"p1\"}, {\"rate\": \"23\", \"policy\": \"p2\"}]'),
(5, 2, 'General Helper', 1, 4.00, '0000-00-00', NULL, 0, 'Enquiry', 1, 1, 1, 'Nithin', '2025-11-07 10:21:25', 'req_690e157d52b9c.jpg', '', '', '[{\"rate\": \"4\", \"policy\": \"p1\"}]'),
(6, 2, 'Tets65', 2, 25.00, '2025-11-03', NULL, 0, 'Enquiry', 0, 1, 0, 'Nithin', '2025-11-07 10:21:25', 'req_690e157d5579c.pdf', '', '', '[{\"rate\": \"4\", \"policy\": \"p1\"}]'),
(7, 3, 'Construction Worker', 10, 1.00, '2025-11-09', NULL, 0, 'Enquiry', 1, 1, 1, 'Nithin1', '2025-11-07 11:58:36', 'req_690e2c4434fa1.png', '', '', '[{\"rate\": \"19\", \"policy\": \"p1\"}]'),
(8, 3, 'General Helper', 20, 2.00, '2025-11-05', NULL, 0, 'Enquiry', 0, 0, 0, 'Nithin1', '2025-11-07 11:58:36', 'req_690e2c4437fe9.pdf', '', '', '[{\"rate\": \"19\", \"policy\": \"p1\"}]'),
(9, 3, 'Security Guard', 1, 2.00, '2025-11-11', NULL, 0, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-07 12:04:11', '', '', 'tr', NULL),
(10, 3, 'Test', 2, 4.00, '2025-11-12', NULL, 0, 'Enquiry', 0, 0, 0, 'Nithin1', '2025-11-07 12:04:11', 'req_690e2d93ca5f5.png', 'acc', '', NULL),
(11, 2, 'Truck Operator', 2, 15.00, '2025-11-16', NULL, 0, 'Enquiry', 1, 1, 1, 'Nithin1', '2025-11-09 23:22:02', 'req_69116f727449c.pdf', 'acc3673', '', '[{\"rate\": \"15\", \"policy\": \"p1\"}, {\"rate\": \"17\", \"policy\": \"p2\"}]'),
(12, 2, 'Construction Worker', 1, 10.00, '2025-11-16', NULL, 0, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-09 23:22:02', 'req_69116f728000a.png', 'fdg', 'trac', '[{\"rate\": \"15\", \"policy\": \"p1\"}, {\"rate\": \"17\", \"policy\": \"p2\"}]'),
(13, 2, 'Construction Worker', 1, 10.00, '2025-11-16', '2025-11-05', 0, 'Expired', 1, 1, 0, 'Nithin1', '2025-11-13 11:50:39', '', NULL, NULL, NULL),
(14, 2, 'General Helper', 1, 10.01, '2025-11-16', '2025-11-27', 0, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-13 11:50:50', '', NULL, NULL, NULL),
(15, 2, 'General Helper', 1, 10.01, '2025-11-16', '2025-11-27', 0, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-13 11:51:13', '', NULL, NULL, NULL),
(16, 2, 'General Helper', 1, 10.01, '2025-11-16', '2025-11-27', 0, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-13 12:04:54', '', '', '', NULL),
(17, 2, 'General Helper', 1, 9.99, '2025-11-16', '2025-11-27', 1, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-13 12:05:12', '', '', '', NULL),
(18, 2, 'General Helper', 1, 9.99, '2025-11-16', '2025-11-27', 0, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-13 12:06:27', '', '', '', NULL),
(19, 2, 'General Helper', 1, 9.99, '2025-11-16', '2025-11-27', 0, 'Enquiry', 1, 1, 0, 'Nithin1', '2025-11-13 12:06:42', '', '', '', NULL),
(20, 2, 'General Helper', 1, 10.02, '2025-11-16', '2025-11-26', 1, 'Active', 1, 1, 1, 'Nithin1', '2025-11-13 12:08:20', 'req_692953b87de7f.jpg', 'a1up', 't1', '[{\"rate\": \"345\", \"policy\": \"dfd1\"}]'),
(21, 2, 'Construction Worker', 3, 34.00, '2025-11-30', '2025-12-12', 0, 'Active', 0, 0, 0, 'Nithin1', '2025-11-28 02:21:30', 'req_6929548206df0.jpg', '0', '', NULL),
(22, 2, 'current trade1', 2, 35.00, '2025-11-30', '2025-12-12', 0, 'Enquiry', 0, 0, 0, 'Nithin1', '2025-11-28 02:21:30', 'req_69295482070ad.pdf', '0', '', NULL),
(23, 2, 'General Helper', 1, 45.00, '2025-11-30', '2025-12-12', 1, 'Active', 0, 0, 0, 'Nithin1', '2025-11-28 02:27:17', NULL, '0', '', NULL),
(24, 2, 'Security Guard', 1, 46.00, '2025-11-30', '2025-12-13', 1, 'Enquiry', 0, 0, 0, 'Nithin1', '2025-11-28 02:28:55', NULL, '0', '', NULL),
(25, 2, 'Truck Operator', 2, 47.00, '2025-11-30', '2025-12-14', 0, 'Active', 0, 0, 0, 'Nithin1', '2025-11-28 02:28:55', NULL, '0', '', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=43 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_sites`
--

INSERT INTO `customers_sites` (`id`, `customer_id`, `site_name`, `site_contact`, `site_address`, `site_location`, `created_by`, `created_at`, `updated_at`) VALUES
(5, 2, 'Site 1', 'contact1', 'address of site1', 'location1', 'Admin', '2025-11-06 15:56:35', '2025-11-14 10:15:24'),
(6, 2, 'Site2 Name 6234h', 'sitecontat2', 'site 2 address', 'location 2', 'Admin', '2025-11-06 15:56:43', '2025-11-14 05:55:59'),
(9, 2, 'Site 3', 'contact 3', 'this is address of site3', 'Location 3', 'Admin', '2025-11-06 15:58:55', '2025-11-06 16:03:22'),
(11, 2, 'name', 'cotnact', 'test address', '', 'Admin', '2025-11-07 04:39:08', NULL),
(15, 1, 'BAUER-1263', '', '', '', NULL, '2025-11-21 07:06:48', '2025-12-23 05:27:28'),
(16, 1, 'BAUER-1262 & 1251', NULL, NULL, NULL, NULL, '2025-11-21 07:06:48', NULL),
(17, 1, 'GEBAL', NULL, NULL, NULL, NULL, '2025-11-21 07:06:48', NULL),
(18, 1, 'BAUER-D0503', NULL, NULL, NULL, NULL, '2025-11-21 07:06:48', NULL),
(19, 1, 'CAMP', NULL, NULL, NULL, NULL, '2025-11-21 07:06:48', NULL),
(20, 1, 'WATERGY', NULL, NULL, NULL, NULL, '2025-11-21 07:06:48', NULL),
(21, 1, 'OFFICE', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(22, 1, 'DSV', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(23, 1, 'VACATION', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(24, 1, 'BIN MOOSA', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(25, 1, 'NBQ', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(26, 1, 'BAUER-1258,1260', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(27, 1, 'VACCATION / EL', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(28, 1, 'BAUER-D0501,0504', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(29, 1, 'AL AIN', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(30, 1, 'NEST', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(31, 1, 'ABSONS', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(32, 1, 'BAUER-0501&0504', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(33, 1, 'BAUER-1256 & 0054', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(34, 1, 'FIXSHINE TECHNICAL', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(35, 1, 'BUGHASA', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(36, 1, 'FIXSHINE GENERAL', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(37, 1, 'ATLANTIC OIL SERVICES', NULL, NULL, NULL, NULL, '2025-11-21 07:06:49', NULL),
(38, 3, 'Site X', 'df343', 'addresdhsdfjh', '', 'Admin', '2025-12-23 04:31:09', '2025-12-23 06:31:56'),
(39, 3, 'Site Y', 'dsfsdf', 'site y address', '', 'Admin', '2025-12-23 06:31:49', NULL),
(40, 4, 'New Site1', 'dfgdfg', 'dfgdfg', '', 'Admin', '2025-12-23 10:19:43', NULL),
(41, 4, 'New Site2', 'dfgdfg', '', '', 'Admin', '2025-12-23 10:20:11', NULL),
(42, 4, 'New Site3', '', '', '', 'Admin', '2025-12-23 10:21:26', NULL);

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
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers_soa`
--

INSERT INTO `customers_soa` (`id`, `customer_id`, `date`, `invoiceno`, `type`, `amount`, `ref_no`, `created_at`) VALUES
(2, 3, NULL, '100/2026', 'Payment', 2000.00, 'r1234', '2026-01-07 10:11:51'),
(5, 3, '2026-01-05', '100/2026', 'Payment', 2216.47, 'ref44', '2026-01-07 11:42:02'),
(6, 3, '2025-12-31', '100/2026', 'Invoice', 2216.47, 'ANGS/100/2026', '2026-01-07 11:44:22'),
(7, 3, '2026-01-07', '100/2026', 'Payment', 2216.47, '', '2026-01-07 11:47:21');

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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_invoices`
--

INSERT INTO `customer_invoices` (`id`, `customer_id`, `month`, `invoice_no`, `reference_no`, `invoice_date`, `subtotal`, `vat_amount`, `total_amount`, `paid`, `created_at`, `balance_amount`) VALUES
(18, 3, '2025-12', '100/2026', 'ANGS/100/2026', '2025-12-31', 2110.92, 105.55, 2216.47, 1, '2026-01-07 18:44:22', 0.00);

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
) ENGINE=MyISAM AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_invoice_timesheets`
--

INSERT INTO `customer_invoice_timesheets` (`id`, `invoice_id`, `timesheet_id`) VALUES
(18, 18, 75);

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
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_site_trades`
--

INSERT INTO `customer_site_trades` (`id`, `site_id`, `trade_id`) VALUES
(39, 38, 2),
(42, 38, 1),
(40, 38, 3),
(16, 15, 1),
(23, 39, 9),
(24, 39, 10),
(41, 38, 6),
(43, 38, 11),
(46, 40, 2),
(47, 41, 1);

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
) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_timesheets`
--

INSERT INTO `customer_timesheets` (`id`, `invoice_no`, `customer_id`, `month`, `site_id`, `subtotal`, `vat_amount`, `total_amount`, `created_at`) VALUES
(76, '176/2026', 3, '2025-12', 38, 2022.92, 101.15, 2124.07, '2026-01-05 14:02:06'),
(8, NULL, 3, '2025-12', 39, 305.00, 15.25, 320.25, '2025-12-30 18:06:54'),
(75, '109/2026', 3, '2025-12', NULL, 2110.92, 105.55, 2216.47, '2026-01-05 11:53:54');

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
) ENGINE=MyISAM AUTO_INCREMENT=132 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_timesheet_items`
--

INSERT INTO `customer_timesheet_items` (`id`, `timesheet_id`, `employee_id`, `emp_code`, `employee_name`, `trade_id`, `trade`, `normal_hours`, `ot_hours`, `hot_hours`, `rate_normal`, `rate_ot`, `rate_hot`, `amount`) VALUES
(131, 76, 3, '2762', 'Testemp1', 2, 'Bricklayer', 8.02, 0.98, 0.00, 251.01, 10.02, 11.02, 2022.92),
(10, 8, 1, '2760', 'Employee11', 1, 'Carpenter', 8.00, 2.00, 9.00, 15.00, 16.00, 17.00, 305.00),
(130, 75, 3, '2762', 'Testemp1', 2, 'Bricklayer', 8.02, 0.98, 0.00, 251.01, 10.02, 11.02, 2022.92),
(129, 75, 1, '2760', 'Employee11', 9, 'TradeY1', 16.00, 4.00, 0.00, 5.00, 2.00, 3.00, 88.00);

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
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_trade_rates`
--

INSERT INTO `customer_trade_rates` (`id`, `customer_id`, `site_id`, `trade_id`, `rate_per_hour`, `is_fixed_rate`, `food_allowance`, `travel_allowance`, `accommodation_allowance`, `allow_overtime`, `not_rate`, `hot_rate`, `phot_rate`, `default_hours`) VALUES
(1, 3, 38, 3, 15.000, 0, 0, 0, 0, 1, NULL, NULL, NULL, 8.00),
(2, 3, 38, 6, 15.000, 0, 0, 0, 0, 1, NULL, NULL, NULL, 8.00),
(3, 3, 39, 9, 5.000, 0, 0, 0, 0, 1, 2.000, 3.000, NULL, 8.00),
(4, 3, 39, 10, 5.000, 0, 0, 0, 0, 1, 2.000, 3.000, NULL, 8.00),
(5, 2, 9, 5, 1.000, 0, 0, 0, 0, 1, 2.010, 3.010, NULL, 8.00),
(6, 2, 9, 2, 1.000, 0, 0, 0, 0, 1, 2.000, 3.000, NULL, 8.00),
(7, 2, 9, 1, 1.000, 0, 0, 0, 0, 1, 2.000, 3.000, NULL, 8.00),
(11, 3, 38, 1, 15.000, 0, 0, 0, 0, 1, 8.000, 10.000, 11.000, 8.00),
(10, 3, 38, 2, 251.010, 0, 1, 1, 1, 1, 10.020, 11.020, 12.020, 8.02),
(12, 3, 38, 11, 15.000, 0, 0, 0, 0, 1, NULL, NULL, NULL, 8.00),
(13, 4, 40, 2, 2.000, 0, 1, 0, 1, 1, 1.000, 1.500, NULL, 8.00),
(14, 4, 41, 1, 10.000, 0, 0, 0, 0, 1, 11.000, 12.000, 13.000, 8.00);

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
) ENGINE=InnoDB AUTO_INCREMENT=69 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `interactions`
--

INSERT INTO `interactions` (`id`, `itype`, `date`, `time`, `contact_name`, `channel_id`, `contact_type_id`, `scenario_id`, `subject`, `notes`, `department`, `owner_id`, `assigned_to`, `status`, `priority`, `nature`, `follow_date`, `follow_time`, `document_label`, `document_file`, `created_at`, `updated_at`, `contact_entity_id`, `contact_phone`, `contact_email`, `entity_contact_id`, `related_employee_ids`, `related_customer_id`) VALUES
(1, NULL, '2025-12-10', '08:22:00', 'Nithin New', 4, 2, 3, 'Job Enquiry', 'CV received in office', NULL, 9, NULL, '', 'normal', NULL, '2025-12-11', '08:22:00', NULL, NULL, '2025-12-10 04:23:45', '2025-12-10 04:23:45', 1, '97145698456', '', '', '', ''),
(2, NULL, '2025-12-11', '12:10:00', 'WATERGY', 3, NULL, 5, '', 'RECIEVED ONLINE PAYMENT RECEIPT TOWARDS INVOICE', NULL, 8, NULL, '', 'normal', NULL, '2025-12-12', '12:10:00', NULL, NULL, '2025-12-11 08:12:02', '2025-12-11 08:12:02', 2, '', '', '', '', ''),
(3, NULL, '2025-12-11', '12:58:00', 'Hardeep Singh', 1, 5, 4, '', 'called office to check about his cancellation paper request - either to cancel or increase salary... ', NULL, 12, NULL, '', '', NULL, '2025-12-12', '12:58:00', NULL, NULL, '2025-12-11 09:00:33', '2025-12-11 09:00:33', 24, '971569684293', '', '', '', ''),
(4, NULL, '0000-00-00', '00:00:00', 'Nithin2', NULL, 1, 2, 'Leave Request', 'leave', NULL, 1, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-12 06:17:42', '2025-12-12 06:17:42', 3, '09846370769', 'nithinpjose19901@gmail.com', '', '', ''),
(5, NULL, '0000-00-00', '00:00:00', 'Nithin1', 2, 1, 2, 'Leave Request', 'test', NULL, 1, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-12 08:17:55', '2025-12-12 08:17:55', 1, '09846370759', 'nithinpjose1990@gmail.com', '', '', ''),
(6, NULL, '2025-12-12', '16:06:00', 'Nithin1', 2, 1, 2, 'Leave Request1', 'leave1', NULL, 1, 4, '', NULL, NULL, NULL, NULL, NULL, NULL, '2025-12-12 12:06:52', '2025-12-12 15:09:27', 1, '09846370759', 'nithinpjose1990@gmail.com', NULL, NULL, NULL),
(7, NULL, '2025-12-12', '16:08:00', 'Nithin1', 4, 1, 2, 'Enquiry for new job', 'test', NULL, 1, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-12 12:10:22', '2025-12-12 12:10:22', 1, '09846370759', 'nithinpjose1990@gmail.com', '1', '157', ''),
(8, NULL, '2025-12-14', '16:10:00', 'Nithin2', 2, 1, 2, 'test', 'test5', NULL, 1, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-12 12:11:26', '2025-12-15 05:42:44', 2, '09846370769', 'nithinpjose19901@gmail.com', '2', '1,2', ''),
(9, NULL, '2025-12-15', '16:11:00', 'Nithin', 4, 5, NULL, 'test employee1', 'test1', NULL, 1, 1, 'working', NULL, NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-12 12:12:40', '2025-12-15 07:31:10', 235, '', '', '', '', '2'),
(10, NULL, '2025-12-15', '13:59:00', 'cname', 3, 6, NULL, 'dsfsdf', 'sdfsdfsd', NULL, 2, 1, NULL, NULL, NULL, '2025-12-16', '05:32:00', NULL, NULL, '2025-12-15 10:32:23', '2025-12-16 06:32:06', 1, '326462364', 'contact@sdf.dfg', '1', '', ''),
(11, NULL, '2025-12-15', '13:59:00', 'cname', 3, 6, NULL, 'dsfsdf', 'sdfsdfsd', NULL, 2, NULL, '', 'normal', NULL, '2025-12-16', '05:32:00', NULL, NULL, '2025-12-15 10:39:50', '2025-12-15 10:39:50', 1, '326462364', 'contact@sdf.dfg', '1', '', ''),
(12, NULL, '2025-12-15', '14:53:00', 'Employee11', 3, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '2025-12-23', '19:26:00', NULL, NULL, '2025-12-15 10:56:16', '2025-12-15 10:56:16', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(13, NULL, '2025-12-15', '14:53:00', 'Employee11', 3, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '2025-12-23', '19:26:00', NULL, 'interaction_13_1765796512.jpg', '2025-12-15 11:01:52', '2025-12-15 11:01:52', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(14, NULL, '2025-12-15', '14:53:00', 'Employee11', 3, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '2025-12-23', '19:26:00', NULL, 'interaction_14_1765796588.jpg', '2025-12-15 11:03:08', '2025-12-15 11:03:08', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(15, NULL, '2025-12-15', '14:53:00', 'Employee11', 3, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '2025-12-23', '19:26:00', NULL, 'interaction_15_1765796770.jpg', '2025-12-15 11:06:10', '2025-12-15 11:06:10', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(16, NULL, '2025-12-15', '14:53:00', 'Employee11', 3, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '2025-12-23', '19:26:00', NULL, 'interaction_16_1765796811.jpg', '2025-12-15 11:06:51', '2025-12-15 11:06:51', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(17, NULL, '2025-12-15', '14:53:00', 'Employee11', 3, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '2025-12-23', '19:26:00', NULL, 'interaction_17_1765796951.jpg', '2025-12-15 11:09:11', '2025-12-15 11:09:11', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(18, NULL, '2025-12-15', '14:53:00', 'Employee11', 3, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '2025-12-23', '19:26:00', NULL, 'interaction_18_1765796965.jpg', '2025-12-15 11:09:25', '2025-12-15 11:09:25', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(19, NULL, '2025-12-15', '15:11:00', 'Joice1', 3, 1, 2, '', '', NULL, 2, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, 'interaction_19_1765797108.pdf', '2025-12-15 11:11:48', '2025-12-15 11:11:48', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(20, NULL, '2025-12-15', '15:59:00', 'Joice1', 3, 1, 2, '', '', NULL, 2, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-15 11:59:45', '2025-12-15 11:59:45', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(21, NULL, '2025-12-15', '15:59:00', 'Joice1', 3, 1, 2, '', '', NULL, 2, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-15 12:00:50', '2025-12-15 12:00:50', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(22, NULL, '2025-12-15', '16:03:00', 'Joice1', 3, 1, 2, '', '', NULL, 2, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-15 12:04:34', '2025-12-15 12:04:34', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(23, NULL, '2025-12-15', '16:03:00', 'Joice1', 3, 1, 2, '', '', NULL, 2, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', 'sdfsdg333', 'interaction_23_1765800308.jpg', '2025-12-15 12:05:08', '2025-12-15 12:05:08', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(24, NULL, '2025-12-15', '16:03:00', 'dfgdf', 3, 3, NULL, '', '', NULL, 2, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Personal checklist form', 'interaction_24_1765800426.pdf', '2025-12-15 12:07:06', '2025-12-15 12:07:06', 1, 'dfgdfgdfg', 'dfgdfg', '1', '', ''),
(25, NULL, '2025-12-15', '16:49:00', 'Employee11', 1, 5, 12, '', '', NULL, 2, NULL, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Insurance Card', 'interaction_25_1765802967.pdf', '2025-12-15 12:49:27', '2025-12-15 12:49:28', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(26, NULL, '2025-12-15', '17:14:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 4, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Insurance Card', 'interaction_26_1765804561.jpg', '2025-12-15 13:16:01', '2025-12-15 13:16:01', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(27, NULL, '2025-12-16', '10:10:00', 'Joice1', 3, 1, 2, 'summmm', 'note7623264', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 06:11:14', '2025-12-16 06:11:14', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(28, NULL, '2025-12-16', '10:41:00', 'Joice1', 1, 1, 2, 'summ3u37', 'sdfsdfsd', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 06:47:52', '2025-12-16 06:47:52', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(29, NULL, '2025-12-16', '10:41:00', 'Joice1', 1, 1, 2, 'summ3u37', 'sdfsdfsd', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 06:48:22', '2025-12-16 06:48:22', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(30, NULL, '2025-12-16', '11:20:00', 'Joice1', 1, 1, 2, 'sdfsdf', 'sdfsdf', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 07:20:26', '2025-12-16 07:20:26', 1, '2364562364', 'joice@gmail.com', '1', '', ''),
(31, NULL, '2025-12-16', '11:45:00', 'Nithin', 1, 1, 2, 'sdfsd', 'sdfsd', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 07:48:00', '2025-12-16 07:48:00', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(32, NULL, '2025-12-16', '12:49:00', 'dfgdfg', 1, 4, 3, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 08:53:37', '2025-12-16 08:53:37', 4, '', '', '4', '', ''),
(33, NULL, '2025-12-16', '12:53:00', 'ncontact', 1, 6, NULL, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 08:54:36', '2025-12-16 08:54:36', 2, '7234723784', 'shdfjhsdbf@sdf.sdf', '2', '', ''),
(34, NULL, '2025-12-16', '13:22:00', 'tectc', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 09:22:43', '2025-12-16 09:22:43', 2, '2364632', 'tetsh@jsdfhgf.fdg', '3', '', ''),
(35, NULL, '2025-12-16', '13:34:00', 'tectc', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 09:34:26', '2025-12-16 09:34:26', 2, '2364632', 'tetsh@jsdfhgf.fdg', '3', '', ''),
(36, NULL, '2025-12-16', '14:54:00', 'Joice1', 1, 1, 2, NULL, NULL, NULL, 2, 2, 'closed', NULL, NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 10:54:32', '2025-12-16 10:55:24', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(37, NULL, '2025-12-16', '14:55:00', 'Employee11', 1, 5, 12, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 10:56:10', '2025-12-16 10:56:10', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(38, NULL, '2025-12-16', '14:55:00', 'sdfsdf', 1, 5, 12, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 10:56:54', '2025-12-16 10:56:54', 9, '3242342354', '', '', '', ''),
(39, NULL, '2025-12-16', '16:05:00', 'newc', 1, 2, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 12:05:29', '2025-12-16 12:05:30', 1, '97145698456', 'nithin@gmail.com', '', '', ''),
(40, NULL, '2025-12-16', '16:05:00', 'Watergy', 1, 2, 8, 'sdfsd', 'sdf', NULL, 2, 2, 'closed', NULL, 'nat12', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 12:07:21', '2025-12-19 15:56:50', 3, '3245235456', 'watergy@gmail.com', '', '', ''),
(41, NULL, '2025-12-16', '16:24:00', 'watergy1', 1, 2, NULL, 'sumgh', 'fdgdfg', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 12:25:15', '2025-12-16 12:25:15', 3, '34343434343', 'watergy1@gmail.com', '', '', ''),
(42, NULL, '2025-12-16', '16:25:00', 'Watergy2', 1, 2, 3, 'summ', 'fdgdfg', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 12:26:11', '2025-12-16 12:26:11', 4, '', 'watergy2@gmail.com', '', '', ''),
(43, NULL, '2025-12-16', '16:41:00', 'Joice1', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-16 12:41:37', '2025-12-16 12:41:37', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(44, NULL, '2025-12-16', '16:41:00', 'Joice1', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Document', 'interaction_44_1765888929.pdf', '2025-12-16 12:42:09', '2025-12-16 12:42:09', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(45, NULL, '2025-12-17', '10:18:00', 'Employee11', 1, 5, 12, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-17 06:18:42', '2025-12-17 06:18:42', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(46, NULL, '2025-12-17', '14:54:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-17 10:54:55', '2025-12-17 10:54:55', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(47, 'OUT', '2025-12-17', '14:57:00', 'tectc', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Document', 'interaction_47_1765969313.jpg', '2025-12-17 11:01:53', '2025-12-17 11:01:53', 2, '2364632', 'tetsh@jsdfhgf.fdg', '3', '', ''),
(48, 'IN', '2025-12-17', '16:22:00', 'tectc', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-17 12:23:37', '2025-12-17 12:23:37', 2, '2364632', 'tetsh@jsdfhgf.fdg', '3', '', ''),
(49, 'IN', '2025-12-17', '16:22:00', 'tectc', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Final Settlement', 'interaction_49_1765974269.mp4', '2025-12-17 12:24:29', '2025-12-17 12:24:29', 2, '2364632', 'tetsh@jsdfhgf.fdg', '3', '', ''),
(50, 'IN', '2025-12-17', '16:25:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Labour card', 'interaction_50_1765974321.jpg', '2025-12-17 12:25:21', '2025-12-17 12:25:21', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(51, 'IN', '2025-12-17', '16:30:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', 'New label', 'interaction_51_1765974618.jpg', '2025-12-17 12:30:18', '2025-12-17 12:30:18', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(52, 'IN', '2025-12-17', '16:31:00', 'Employee11', 1, 5, 19, 'sumamr', 'gsdhf', NULL, 2, 2, 'open', NULL, NULL, '0000-00-00', '00:00:00', 'TestL', 'interaction_52_1765974707.mp4', '2025-12-17 12:31:47', '2025-12-18 11:33:22', 1, '6366734672', 'employee1@gmail.com', '', '', ''),
(53, 'IN', '2025-12-17', '16:33:00', 'Joice1', 1, 1, 2, 'rrrvideo8', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-17 12:33:34', '2025-12-17 12:33:34', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(54, 'IN', '2025-12-17', '16:33:00', 'Joice1', 1, 1, 2, 'rrrvideo8', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Document', 'interaction_54_1765974896.mp4', '2025-12-17 12:34:56', '2025-12-17 12:34:56', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(55, 'IN', '2025-12-17', '16:42:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, '', 'normal', NULL, '0000-00-00', '00:00:00', 'Visa', 'interaction_55_1765975349.mp4', '2025-12-17 12:42:29', '2025-12-17 12:42:29', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(56, 'IN', '2025-12-18', '12:03:00', 'Nithin', 1, 1, 2, 'TestOpe1', 'shdbfj', NULL, 2, 2, NULL, NULL, NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-18 08:04:07', '2025-12-18 11:17:52', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(57, 'IN', '2025-12-18', '12:03:00', 'Nithin', 1, 1, 2, 'TestOpen1', 'shdbfj', NULL, 2, 2, 'open', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-18 08:06:00', '2025-12-18 08:06:00', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(58, 'IN', '2025-12-19', '20:20:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', NULL, '0000-00-00', '00:00:00', NULL, NULL, '2025-12-19 16:21:01', '2025-12-19 16:21:01', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(59, 'IN', '2025-12-19', '20:22:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'marketing', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-19 16:23:35', '2025-12-19 16:23:35', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(60, 'IN', '2025-12-22', '13:30:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'job_enquiry', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-22 09:31:00', '2025-12-22 09:31:00', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(61, 'IN', '2025-12-22', '13:32:00', 'Joice1', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'marketing', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-22 09:33:00', '2025-12-22 09:33:00', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(62, 'IN', '2025-12-22', '13:32:00', 'Joice1', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'marketing', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-22 09:33:07', '2025-12-22 09:33:07', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(63, 'IN', '2025-12-22', '13:32:00', 'Joice1', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'employee', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-22 09:35:11', '2025-12-22 09:35:11', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(64, 'IN', '2025-12-22', '13:32:00', 'Joice1', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'customer', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-22 09:36:49', '2025-12-22 09:36:49', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(65, 'IN', '2025-12-22', '13:32:00', 'Joice1', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'seminar_event_exhibition', '0000-00-00', '00:00:00', NULL, NULL, '2025-12-22 09:38:24', '2025-12-22 09:38:24', 2, '2364562364', 'joice@gmail.com', '1', '', ''),
(66, 'IN', '2025-12-22', '13:32:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'marketing', '2025-12-25', '07:15:00', NULL, NULL, '2025-12-22 09:41:50', '2025-12-22 09:41:50', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(67, 'IN', '2025-12-26', '09:29:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'employee', '0000-00-00', '00:00:00', 'Document', 'interaction_67_1766727082.mp4', '2025-12-26 05:31:22', '2025-12-26 05:31:22', 2, '32784788488', 'nithin@gmail.com', '2', '', ''),
(68, 'IN', '2025-12-30', '12:15:00', 'Nithin', 1, 1, 2, '', '', NULL, 2, 2, 'open', 'normal', 'enquiry', '2025-12-31', '17:45:00', NULL, NULL, '2025-12-30 08:15:32', '2025-12-30 08:15:32', 2, '32784788488', 'nithin@gmail.com', '2', '', '');

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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `interactions_documents`
--

INSERT INTO `interactions_documents` (`id`, `interaction_id`, `label`, `file_name`, `file_type`, `expiry_date`, `created_by`, `created_at`) VALUES
(1, 47, 'TestL', 'interaction_47_69429d8db9dc2.jpg', 'image', NULL, 'Nithin1', '2025-12-17 17:39:49'),
(2, 47, 'New label', 'interaction_47_69429d9c998e3.pdf', 'pdf', NULL, 'Nithin1', '2025-12-17 17:40:04'),
(3, 47, 'ee', 'interaction_47_69429e071a134.mp4', 'video', NULL, 'Nithin1', '2025-12-17 17:41:51'),
(4, 49, 'Final Settlement', 'interaction_49_1765974269.mp4', 'image', NULL, 'Nithin1', '2025-12-17 17:54:29'),
(5, 50, 'Labour card', 'interaction_50_1765974321.jpg', 'image', NULL, 'Nithin1', '2025-12-17 17:55:21'),
(6, 51, 'New label', 'interaction_51_1765974618.jpg', 'image', NULL, 'Nithin1', '2025-12-17 18:00:18'),
(7, 51, 'ee', 'interaction_51_6942a2970fc95.mp4', 'video', NULL, 'Nithin1', '2025-12-17 18:01:19'),
(8, 52, 'TestL', 'interaction_52_1765974707.mp4', 'image', NULL, 'Nithin1', '2025-12-17 18:01:47'),
(9, 54, 'Document', 'interaction_54_1765974896.mp4', 'video', NULL, 'Nithin1', '2025-12-17 18:04:56'),
(10, 55, 'Visa', 'interaction_55_1765975349.mp4', 'video', NULL, 'Nithin1', '2025-12-17 18:12:29'),
(11, 56, 'Emirates ID', 'interaction_56_6943f60455da7.jpg', 'image', NULL, 'Nithin1', '2025-12-18 18:09:32'),
(12, 67, 'Document', 'interaction_67_1766727082.mp4', 'video', NULL, 'Nithin1', '2025-12-26 11:01:22');

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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `interactions_followup`
--

INSERT INTO `interactions_followup` (`id`, `interaction_id`, `note_text`, `created_by`, `created_by_name`, `created_at`) VALUES
(1, 9, 'testnote2', 2, 'Nithin1', '2025-12-15 13:37:06'),
(2, 9, 'testnote2', 2, 'Nithin1', '2025-12-15 13:52:53'),
(3, 9, 'testnote2', 2, 'Nithin1', '2025-12-15 13:54:00'),
(4, 9, 'testnote2', 2, 'Nithin1', '2025-12-15 13:54:58'),
(5, 9, 'testnote2', 2, 'Nithin1', '2025-12-15 13:56:36'),
(6, 9, 'testnote34', 2, 'Nithin1', '2025-12-15 13:57:17'),
(7, 9, 'rtrdgfd', 2, 'Nithin1', '2025-12-15 14:01:50'),
(8, 9, 'test653653', 2, 'Nithin1', '2025-12-15 14:06:44'),
(9, 47, 'followupnotes122', 2, 'Nithin1', '2025-12-17 16:32:42'),
(10, 46, 'new followup', 2, 'Nithin1', '2025-12-17 16:37:08'),
(11, 46, 'dfghdfgh', 2, 'Nithin1', '2025-12-17 16:37:13'),
(12, 46, 'sdfsdf', 2, 'Nithin1', '2025-12-17 16:37:20'),
(13, 46, 'gg', 2, 'Nithin1', '2025-12-17 16:37:23'),
(14, 56, 'tesdfg', 2, 'Nithin1', '2025-12-18 16:48:17'),
(15, 56, 'ttt', 2, 'Nithin1', '2025-12-18 16:48:24'),
(16, 56, 'sdfsdfghfhfghdf\r\ngfhfghfghfghfghfghfg\r\nfghfghfg', 2, 'Nithin1', '2025-12-18 16:48:33');

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
(1, 'Dileep', 'mail.mediatel.com', 'dileep@mediatel.com', 'JRJGlobal_123', 465, 'ssl', 'Inbox', 'Sent', 1, '2025-10-17 06:25:47', 'mail.mediatel.com', 'dileep_out', 'JRJGlobal_123', 993, 'ssl', 'INBOX', 1),
(2, 'Nithin', 'mail.mediatel.com', 'nithin@mediatel.com', 'JRJGlobal_321', 465, 'ssl', 'Inbox', 'Sent', 1, '2025-10-17 10:25:10', 'mail.mediatel.com', 'nithin_out', 'JRJGlobal_321', 993, 'ssl', 'INBOX', 2);

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
  `supplier_id` int DEFAULT NULL,
  `contact_id` int DEFAULT NULL,
  `log` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci,
  `type` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'general' COMMENT 'general/timeline',
  `ip` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(1, 'New Business Enquiry', 'new-business', 'customer', '2025-12-08 14:58:34'),
(2, 'Customer Request', 'customer-request', 'customer', '2025-12-08 14:58:34'),
(3, 'Job Application', 'job-application', 'supplier', '2025-12-08 14:58:34'),
(4, 'Request from Employee', 'request-from-employee', 'supplier', '2025-12-09 08:59:16'),
(5, 'PAYMENT RECEIPTS', 'payment-receipts', 'supplier', '2025-12-11 08:11:09'),
(6, 'Scenario1', 'scenario1', '', '2025-12-15 09:37:25'),
(7, 'Scenario2', 'scenario2', '', '2025-12-15 09:37:33'),
(8, 'dsfsdf', 'dsfsdf', '', '2025-12-15 09:51:50'),
(9, 'scene1', 'scene1', '', '2025-12-15 09:58:53'),
(10, 'sdf', 'sdf', '', '2025-12-15 09:59:52'),
(12, 'Scenarioxs', 'scenarioxs', 'employee', '2025-12-15 05:11:30'),
(13, 'newScen', 'newscen', '', '2025-12-16 06:52:40'),
(14, 'TestS1', 'tests1', 'employee', '2025-12-17 00:34:55'),
(15, 'TestS2', 'tests2', 'employee', '2025-12-17 00:40:01'),
(16, 'TestS3', 'tests3', 'employee', '2025-12-17 00:41:42'),
(17, 'dfdf', 'dfdf', 'employee', '2025-12-17 00:43:45'),
(18, 'S2fgdfg', 's2fgdfg', 'employee', '2025-12-17 00:44:04'),
(19, 'NewS', 'news', 'employee', '2025-12-17 00:51:36');

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
) ENGINE=MyISAM AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers_documents`
--

INSERT INTO `suppliers_documents` (`id`, `supplier_id`, `label`, `file_name`, `file_type`, `expiry_date`, `created_by`, `created_at`) VALUES
(1, 2, 'Experience', '1762338667_sample.pdf', 'pdf', NULL, 'Nithin', '2025-11-05 10:31:07'),
(2, 2, 'Residence ID', '1762354987_Sample-png-Image-for-Testing.png', 'image', NULL, 'Nithin', '2025-11-05 15:03:07'),
(4, 2, 'Test ID', '1762441601_Sample.png', 'image', '2025-11-28', 'Nithin', '2025-11-06 15:06:41'),
(8, 2, 'TestId', '1763017497_sample.pdf', 'pdf', '2025-12-25', 'Nithin1', '2025-11-13 07:04:57'),
(6, 2, 'idcard', '1762519750_bc-15.jpg', 'image', NULL, 'Nithin', '2025-11-07 12:49:10'),
(9, 2, 'TestId', '1763017515_sample.pdf', 'pdf', '2025-12-25', 'Nithin1', '2025-11-13 07:05:15');

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
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers_invoices`
--

INSERT INTO `suppliers_invoices` (`id`, `supplier_id`, `invoice_date`, `due_date`, `invoice_amount`, `type`, `category`, `payment_status`, `document`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, '2025-11-10', '2025-11-26', 24.00, 'Received', 'Sim Card', 'Unpaid', 'inv_690df81d1a6ce.pdf', 'test3', 2, '2025-11-07 19:16:05', '2025-11-19 10:52:50'),
(2, 2, '2025-11-19', '2025-11-26', 20.00, 'Received', 'Newcat4', 'Unpaid', '', 'Invoice for AED 20 - Newcat4.', 2, '2025-11-07 19:37:14', '2025-11-24 14:17:30'),
(3, 3, '2025-11-14', NULL, 0.00, '', '', 'Paid', 'inv_690e2ed842edd.pdf', 'this is test invoice', 2, '2025-11-07 23:09:21', '2025-11-07 23:09:36'),
(5, 2, '2025-11-15', '2025-11-26', 12.00, 'Received', 'Cat35', 'Unpaid', '', 'Invoice for AED 12 - Cat35.', 2, '2025-11-14 23:04:02', '2025-11-19 10:52:41'),
(6, 2, '2025-11-16', '2025-11-26', 33.99, 'Sent', 'Sim Card', 'Unpaid', '', 'Invoice for AED 34 - newcat.', 2, '2025-11-14 23:44:36', '2025-11-19 10:52:39'),
(7, 2, '2025-11-15', '2025-11-26', 34.00, 'Received', 'icat7', 'Unpaid', '', 'Invoice for AED 34 - Paperwork.', 2, '2025-11-15 14:11:19', '2025-11-19 10:52:55'),
(8, 0, '2025-11-16', '2025-11-26', 124.00, '', 'newcat2', 'Unpaid', 'inv_691864c3774b1.jpg', 'Invoice for AED 123 - newcat2.', 2, '2025-11-15 17:01:25', '2025-11-19 11:17:15'),
(9, 2, '2025-11-18', '2025-11-30', 24.00, 'Sent', '', 'Unpaid', '', 'Invoice for AED 24.', 2, '2025-11-19 11:10:48', '2025-11-19 11:10:48'),
(10, 1, '2025-11-24', '2025-11-21', 34.02, 'Received', 'new76', 'Unpaid', '', 'Invoice for AED 34 - New63 updated.', 2, '2025-11-24 10:27:20', '2025-11-24 14:17:26'),
(11, 4, '2025-11-24', '2025-11-30', 36.00, 'Received', 'Newcat4', 'Unpaid', 'inv_69242771bbba80.19263326.pdf', 'Invoice for AED 36.', 2, '2025-11-24 14:39:16', '2025-11-24 15:07:53');

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
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers_logs`
--

INSERT INTO `suppliers_logs` (`id`, `supplier_id`, `agent_id`, `name`, `notes`, `type`, `visibility`, `created_at`) VALUES
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
(30, 1, NULL, 'Naren Jayabalu', 'Status changed from \'Active\' to \'Work in progress\'.\n\nNote: tetsttst', 'General', 'Public', '2025-11-07 12:26:58'),
(31, 2, NULL, 'Nithin1', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-11-09 23:42:49'),
(32, 2, NULL, 'Nithin1', 'Email Feature changed from \'Enabled\' to \'Disabled\'', 'General', 'Public', '2025-11-09 23:43:00'),
(33, 3, NULL, 'Nithin1', 'Email Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-12-10 06:16:37'),
(34, 3, NULL, 'Nithin1', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-12-10 06:16:37'),
(35, 4, 2, 'Nithin1', 'sdfsdfgnote44', 'Email', 'Public', '2025-12-10 07:34:50'),
(36, 4, NULL, 'Nithin1', 'WhatsApp Feature changed from \'Disabled\' to \'Enabled\'', 'General', 'Public', '2025-12-29 11:14:51');

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
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers_payments`
--

INSERT INTO `suppliers_payments` (`id`, `supplier_id`, `type`, `category`, `invoice_date`, `payment_status`, `invoice_amount`, `invoice_partial`, `invoice_payment_method`, `reclaim_by`, `reimbursable`, `card_last4`, `cheque_bank`, `cheque_issuer`, `reimbursement_amount`, `document`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(4, 2, 'Expense', 'Paperwork', '2025-11-18', 'Paid', 20.00, 0.00, 'Cheque', 'Company', 'No', '4534', '', '', 0.00, '[]', '', 2, '2025-11-17 17:36:08', '2025-11-19 11:53:00'),
(5, 1, 'Income', 'newcat2', '2025-11-18', 'Unpaid', 30.00, 0.00, '', 'Company', 'No', '3453', '', '', 0.00, '[]', 'Paid AED 30 for Medical using Bank Transfer.', 2, '2025-11-17 20:06:45', '2025-11-18 11:24:29'),
(3, 2, 'Expense', '', '2025-11-18', 'Paid', 10.00, 0.00, 'Card', 'Company', 'Yes', '3455', 'dsdf', '234', 10.00, '[]', 'Received AED 10 for newcat2 using Cheque.', 2, '2025-11-15 16:47:51', '2025-11-19 11:38:39'),
(6, 2, 'Income', 'newcat4', '2025-11-19', 'Unpaid', 30.00, 0.00, '', '0', 'No', '', '', '', 0.00, '[]', '', 2, '2025-11-19 11:37:11', '2025-11-20 14:01:48'),
(7, 4, 'Expense', 'Sim Expire Fine', '2025-11-20', 'Unpaid', 10.00, 0.00, '', '0', 'No', '', '', '', 0.00, '[]', 'Pending receipt of AED 10 for newcat45.', 2, '2025-11-20 14:04:57', '2025-11-27 18:13:38');

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
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers_quotations`
--

INSERT INTO `suppliers_quotations` (`id`, `supplier_id`, `ref_no`, `version`, `quotation_name`, `quotation_date`, `attention`, `subject`, `message`, `jobs_json`, `terms_json`, `closing`, `status`, `created_by`, `created_at`, `updated_at`) VALUES
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
(26, 2, 'QTN2511062820', 4, 'Test Quotationwe', '2025-11-07', 'Manager', 'This is a subjectnew3', 'This si smesahguy23ut64q', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation22', 'draft', 'Nithin', '2025-11-07 12:43:16', '2025-11-07 12:43:16'),
(27, 2, 'QTN2511062820', 5, 'Test Quotationwe', '2025-11-14', 'Manager', 'This is a subject', 'This si smesahguy23ut64q', '[{\"rate_pay\": \"14\", \"job_title\": \"General Helper\", \"start_date\": \"2025-11-06\", \"num_employees\": \"2\"}, {\"rate_pay\": \"24.99\", \"job_title\": \"Security Guard\", \"start_date\": \"2025-11-13\", \"num_employees\": \"6\"}]', '[{\"text\": \"1 Year (extendable)22x\", \"title\": \"Duration of Contract\"}, {\"text\": \"Applicable 5% on Invoice Amountx\", \"title\": \"VAT\"}, {\"text\": \"Provided by M/s Al Nasr General Services Est.\", \"title\": \"Accommodation, Food & Transportation\"}, {\"text\": \"Basic safety items provided by M/s Al Nasr General Services Est.\", \"title\": \"Safety Equipment\"}, {\"text\": \"Daily work hours minimum 11 hours at 6 days a week.\", \"title\": \"Working Hours\"}, {\"text\": \"Payment within 30 days upon receiving certified timesheets and invoice.\", \"title\": \"Payment of Invoice\"}]', 'this is closing of the quotation22', 'draft', 'Nithin1', '2025-11-14 05:43:06', '2025-11-14 05:43:06');

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
) ENGINE=InnoDB AUTO_INCREMENT=72 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers_reminders`
--

INSERT INTO `suppliers_reminders` (`id`, `supplier_id`, `reminder_at`, `type`, `contact_id`, `note`, `created_at`, `updated_at`, `completed`) VALUES
(56, 3, '2025-11-12 10:00:00', 'General', NULL, 'reminder333', '2025-11-05 13:36:23', '2025-11-07 14:41:34', 0),
(57, 2, '2025-11-12 10:00:00', 'General', NULL, 'test remidner1', '2025-11-05 09:34:56', '2025-11-07 14:41:34', 0),
(58, 2, '2025-11-12 10:00:00', 'General', NULL, 'test remidner2', '2025-11-05 09:37:21', '2025-11-07 14:41:34', 0),
(59, 2, '2025-11-06 10:00:00', 'General', NULL, 'test remidner3', '2025-11-05 09:37:57', '2025-11-07 14:41:34', 0),
(60, 2, '2025-11-14 10:00:00', 'Email', 1, 'test remidner334', '2025-11-07 10:13:35', '2025-11-07 15:47:29', 0),
(61, 2, '2025-11-08 10:00:00', 'Call', 1, 'tesgh66211', '2025-11-07 10:17:15', NULL, 0),
(62, 2, '2025-11-14 10:00:00', 'Call', 1, 'te63563', '2025-11-07 10:17:43', '2025-11-07 15:50:08', 0),
(63, 2, '2025-11-14 10:00:00', 'Call', 1, 'trtr55', '2025-11-07 10:18:49', '2025-11-07 15:50:10', 0),
(64, 2, '2025-11-14 10:00:00', 'Email', 2, 'ytuytu89xt1', '2025-11-07 10:19:57', '2025-11-07 11:17:15', 0),
(65, 2, '2025-11-25 10:00:00', 'General', NULL, 'Document TestId expiry on 25 Dec 2025', '2025-11-13 07:05:15', NULL, 0),
(66, 2, '2025-12-01 10:00:00', 'Email', 1, 'tes5666', '2025-11-24 10:25:32', '2025-11-24 16:07:54', 1),
(67, 2, '2025-12-24 10:00:00', 'Callback', NULL, 'fdghjhds334', '2025-11-24 10:38:21', '2025-11-24 11:03:33', 0),
(68, 2, '2025-12-01 10:00:00', 'Email', 1, 'tes663', '2025-11-24 11:03:52', '2025-11-24 16:34:08', 1),
(69, 4, '2025-12-05 10:00:00', 'Email', NULL, 'test56', '2025-12-04 12:58:53', '2025-12-04 13:05:33', 0),
(70, 4, '2025-12-17 10:00:00', 'Email', NULL, 'dsfsdfg33', '2025-12-10 13:04:19', NULL, 0),
(71, 4, '2025-12-17 13:58:47', 'General', 4, 'Channel: Phone. Scenario: Job Application. Assigned to: Nithin1. Priority: normal.', '2025-12-16 08:53:37', '2025-12-30 13:58:51', 0);

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

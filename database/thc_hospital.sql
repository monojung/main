-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 09:46 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `thc_hospital`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `old_values` text DEFAULT NULL,
  `new_values` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 8, 'admin_login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-18 09:50:28'),
(2, 8, 'admin_login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-18 11:06:24'),
(3, 8, 'admin_login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-18 11:20:06'),
(4, 8, 'admin_login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:07:13'),
(5, 10, 'login_failed', 'users', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:17:58'),
(6, 8, 'login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:21:07'),
(7, 8, 'login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:21:45'),
(8, 8, 'login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:24:37'),
(9, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:25:10'),
(10, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:28:39'),
(11, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:29:07'),
(12, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 ม.3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-580-xxx\",\"hospital_fax\":\"053-580-110\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"053-580-999\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:34:07'),
(13, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 ม.3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:34:33'),
(14, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:35:06'),
(15, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:56:17'),
(16, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 03:56:21'),
(17, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:05:08'),
(18, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:05:14'),
(19, 9, 'login_failed', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:05:30'),
(20, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:05:36'),
(21, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:06:33'),
(22, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:06:44'),
(23, 8, 'news_deleted', 'news', 4, '{\"title\":\"การให้บริการฉีดวัคซีนไข้หวัดใหญ่ ประจำปี 2568\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:07:15'),
(24, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:08:49'),
(25, 10, 'login_success', 'users', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:09:04'),
(26, 10, 'logout', 'users', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:09:22'),
(27, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:09:39'),
(28, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:09:55'),
(29, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:12:14'),
(30, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000\",\"hospital_phone\":\"053-580-100\",\"hospital_fax\":\"053-580-110\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:25:27'),
(31, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:26:18'),
(32, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:29:31'),
(33, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:44:03'),
(34, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:46:41'),
(35, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:47:15'),
(36, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:47:28'),
(37, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 04:51:32'),
(38, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 05:07:51'),
(39, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 05:08:24'),
(40, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:01:05'),
(41, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:14:06'),
(42, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:14:18'),
(43, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:14:50'),
(44, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:17:44'),
(45, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"123 ถนนหลัก ตำบลทุ่งหัวช้าง อำเภอเมือง จังหวัดลำพูน 51000\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:18:09'),
(46, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:18:36'),
(47, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:19:41'),
(48, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:37:38'),
(49, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:49:32'),
(50, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:56:49'),
(51, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-19 09:59:37'),
(52, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 02:20:33'),
(53, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:07:37'),
(54, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:09:44'),
(55, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:12:07'),
(56, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:12:15'),
(57, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:12:48'),
(58, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:12:56'),
(59, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:34:16'),
(60, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:34:26'),
(61, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:34:54'),
(62, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:36:32'),
(63, 8, 'system_settings_updated', 'settings', NULL, NULL, '{\"maintenance_mode\":\"1\",\"maintenance_message\":\"ระบบอยู่ในช่วงปรับปรุง กรุณาลองใหม่ภายหลัง\",\"session_timeout\":120,\"max_login_attempts\":5,\"login_lockout_time\":30,\"password_min_length\":6,\"require_password_complexity\":\"0\",\"enable_registration\":\"0\",\"enable_api\":\"0\",\"log_retention_days\":90,\"backup_retention_days\":30,\"auto_backup_enabled\":\"0\",\"backup_frequency\":\"weekly\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:37:12'),
(64, 8, 'system_settings_updated', 'settings', NULL, NULL, '{\"maintenance_mode\":\"0\",\"maintenance_message\":\"ระบบอยู่ในช่วงปรับปรุง กรุณาลองใหม่ภายหลัง\",\"session_timeout\":120,\"max_login_attempts\":5,\"login_lockout_time\":30,\"password_min_length\":6,\"require_password_complexity\":\"1\",\"enable_registration\":\"1\",\"enable_api\":\"1\",\"log_retention_days\":90,\"backup_retention_days\":30,\"auto_backup_enabled\":\"0\",\"backup_frequency\":\"weekly\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:37:39'),
(65, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:37:50'),
(66, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:38:06'),
(67, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง11\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"working_hours_start\":\"08:00\",\"working_hours_end\":\"16:30\",\"weekend_hours_start\":\"08:00\",\"weekend_hours_end\":\"12:00\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:38:20'),
(68, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง11\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"working_hours_start\":\"08:00\",\"working_hours_end\":\"16:30\",\"weekend_hours_start\":\"08:00\",\"weekend_hours_end\":\"12:00\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:38:24'),
(69, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง11\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"16699\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"working_hours_start\":\"08:00\",\"working_hours_end\":\"16:30\",\"weekend_hours_start\":\"08:00\",\"weekend_hours_end\":\"12:00\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:39:32'),
(70, 8, 'database_backup_initiated', 'system', NULL, NULL, '{\"backup_name\":\"backup_2025-06-23_16-40-23.sql\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:40:23'),
(71, 8, 'email_test', 'system', NULL, NULL, '{\"email\":\"thunghuachang11143@gmail.com\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:40:54'),
(72, 8, 'logs_cleared', 'activity_logs', NULL, NULL, '{\"days\":30,\"affected\":0}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:41:44'),
(73, 8, 'news_created', 'news', 6, NULL, '{\"title\":\"ทดสอบ\",\"category\":\"general\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:41:59'),
(74, 8, 'news_created', 'news', 7, NULL, '{\"title\":\"ทดสอบทดสอบทดสอบ\",\"category\":\"general\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:42:11'),
(75, 8, 'news_updated', 'news', 6, '{\"title\":\"ทดสอบ\",\"status\":\"draft\"}', '{\"title\":\"ทดสอบ\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:42:17'),
(76, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:53:30'),
(77, 10, 'login_success', 'users', 10, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 09:53:35'),
(79, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-23 10:00:32'),
(80, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 02:36:06'),
(81, 8, 'login_failed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:04:14'),
(82, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:04:18'),
(83, 8, 'news_created', 'news', 8, NULL, '{\"title\":\"test\",\"category\":\"announcement\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:34:02'),
(84, 8, 'news_deleted', 'news', 8, '{\"title\":\"test\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:34:10'),
(85, 8, 'news_created', 'news', 9, NULL, '{\"title\":\"test\",\"category\":\"announcement\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:35:22'),
(86, 8, 'news_deleted', 'news', 9, '{\"title\":\"test\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:37:00'),
(87, 8, 'news_created', 'news', 10, NULL, '{\"title\":\"s\",\"category\":\"announcement\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:48:11'),
(88, 8, 'news_updated', 'news', 10, '{\"title\":\"s\",\"status\":\"draft\"}', '{\"title\":\"s\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:48:33'),
(89, 8, 'news_updated', 'news', 10, '{\"title\":\"s\",\"status\":\"draft\"}', '{\"title\":\"s\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:49:09'),
(90, 8, 'news_deleted', 'news', 10, '{\"title\":\"s\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:54:33'),
(91, 8, 'news_created', 'news', 11, NULL, '{\"title\":\"test\",\"category\":\"announcement\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:59:28'),
(92, 8, 'news_created', 'news', 12, NULL, '{\"title\":\"mglv[\",\"category\":\"announcement\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 03:59:46'),
(93, 8, 'news_created', 'news', 13, NULL, '{\"title\":\"test\",\"category\":\"jobs\",\"status\":\"draft\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:00:35'),
(94, 8, 'news_updated', 'news', 13, '{\"title\":\"test\",\"status\":\"draft\"}', '{\"title\":\"test\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:00:50'),
(95, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง11ddd\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"16699\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"working_hours_start\":\"08:00\",\"working_hours_end\":\"16:30\",\"weekend_hours_start\":\"08:00\",\"weekend_hours_end\":\"12:00\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:09:47'),
(96, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:31:58'),
(97, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:40:42'),
(98, 8, 'website_settings_updated', 'settings', NULL, NULL, '{\"website_title\":\"โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน\",\"website_description\":\"โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน\",\"website_keywords\":\"โรงพยาบาล, ลำพูน, ทุ่งหัวช้าง, สุขภาพ, แพทย์, รักษา\",\"facebook_url\":\"\",\"line_id\":\"\",\"google_analytics_id\":\"\",\"show_statistics\":\"1\",\"show_doctors\":\"1\",\"news_per_page\":8,\"allow_comments\":\"1\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:43:45'),
(99, 8, 'news_updated', 'news', 13, '{\"title\":\"test\",\"status\":\"published\"}', '{\"title\":\"test\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:46:13'),
(100, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:49:37'),
(101, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:53:02'),
(102, 8, 'news_created', 'news', 14, NULL, '{\"title\":\"testttt\",\"category\":\"jobs\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:53:35'),
(103, 8, 'news_updated', 'news', 14, '{\"title\":\"testttt\",\"status\":\"published\"}', '{\"title\":\"testttt\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:54:16'),
(104, 8, 'news_updated', 'news', 14, '{\"title\":\"testttt\",\"status\":\"published\"}', '{\"title\":\"testttt\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:54:28'),
(105, 8, 'news_updated', 'news', 14, '{\"title\":\"testttt\",\"status\":\"published\"}', '{\"title\":\"testttt\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:54:34'),
(106, 8, 'news_updated', 'news', 14, '{\"title\":\"testttt\",\"status\":\"published\"}', '{\"title\":\"testttt\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:54:41'),
(107, 8, 'news_updated', 'news', 14, '{\"title\":\"testttt\",\"status\":\"published\"}', '{\"title\":\"testttt\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:55:55'),
(108, 8, 'news_updated', 'news', 14, '{\"title\":\"testttt\",\"status\":\"published\"}', '{\"title\":\"testttt\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:56:02'),
(109, 8, 'news_updated', 'news', 14, '{\"title\":\"testttt\",\"status\":\"published\"}', '{\"title\":\"testttt\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 04:56:07'),
(110, 8, 'security_event', 'security', NULL, '{\"event\":\"session_timeout\",\"details\":\"\",\"url\":\"\\/main\\/admin\\/news.php\",\"referer\":\"http:\\/\\/localhost\\/main\\/admin\\/dashboard.php\"}', NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 09:14:25'),
(111, 8, 'session_destroyed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 09:14:25'),
(112, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 09:14:32'),
(113, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 09:14:41'),
(114, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 09:14:47'),
(115, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-24 09:21:34'),
(116, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-26 08:35:45'),
(117, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-26 08:35:48'),
(118, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-06-26 08:35:58'),
(119, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 03:04:58'),
(120, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง11ddd\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"16699\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"working_hours_start\":\"08:00\",\"working_hours_end\":\"16:00\",\"weekend_hours_start\":\"08:00\",\"weekend_hours_end\":\"12:00\",\"timezone\":\"Asia\\/Bangkok\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 03:05:27'),
(128, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 04:57:07'),
(129, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 04:57:11'),
(130, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 04:57:35'),
(131, 8, 'news_created', 'news', 15, NULL, '{\"title\":\"123\",\"category\":\"announcement\",\"status\":\"published\"}', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 06:38:18'),
(132, 9, 'login_success', 'users', 9, NULL, NULL, '192.168.2.252', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 Edg/138.0.0.0', '2025-07-01 06:58:05'),
(133, 8, 'session_destroyed', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 06:58:09'),
(134, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 06:58:20'),
(135, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 07:23:12'),
(136, 8, 'login_success', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 09:32:08'),
(137, 8, 'logout', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 09:35:06'),
(138, 8, 'login_success', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 11:33:05'),
(139, 8, 'settings_updated', 'settings', NULL, NULL, '{\"hospital_name\":\"โรงพยาบาลทุ่งหัวช้าง\",\"hospital_name_en\":\"Tung Hua Chang Hospital\",\"hospital_address\":\"48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160\",\"hospital_phone\":\"053-975-201\",\"hospital_fax\":\"053-975-200\",\"hospital_email\":\"info@thchospital.go.th\",\"emergency_phone\":\"1669\",\"website_url\":\"https:\\/\\/www.thchospital.go.th\",\"working_hours_start\":\"08:00\",\"working_hours_end\":\"16:00\",\"weekend_hours_start\":\"08:00\",\"weekend_hours_end\":\"12:00\",\"timezone\":\"Asia\\/Bangkok\"}', '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 11:35:25'),
(140, 8, 'logout', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 12:44:50'),
(141, 8, 'login_success', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 12:54:42'),
(142, 8, 'logout', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 14:00:28'),
(143, 8, 'login_success', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 16:12:25'),
(144, 8, 'logout', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', '2025-07-01 16:14:47'),
(145, 8, 'login_success', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Linux; Android 6.0; Nexus 5 Build/MRA58N) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Mobile Safari/537.36', '2025-07-01 16:14:54'),
(146, 8, 'security_event', 'security', NULL, '{\"event\":\"suspicious_activity\",\"details\":\"user_agent_change\",\"url\":\"\\/admin\\/dashboard.php\",\"referer\":\"https:\\/\\/thchospital.moph.go.th\\/index.php\"}', NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 16:32:37'),
(147, 8, 'session_destroyed', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 16:32:37'),
(148, 8, 'login_success', 'users', 8, NULL, NULL, '203.157.100.2', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 16:32:41'),
(149, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 16:37:15'),
(150, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 18:31:12'),
(151, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 18:31:24'),
(152, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:19:21'),
(153, 9, 'login_success', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:19:27'),
(154, 9, 'logout', 'users', 9, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:19:29'),
(155, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:19:39'),
(156, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:35:58'),
(157, 8, 'admin_login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:36:07'),
(158, 8, 'logout', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:40:01'),
(159, 8, 'login_success', 'users', 8, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:44:38'),
(160, 8, 'settings_updated', 'system_config', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:44:54'),
(161, 8, 'settings_updated', 'system_config', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:44:54'),
(162, 8, 'settings_updated', 'system_config', NULL, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/137.0.0.0 Safari/537.36', '2025-07-01 19:44:54');

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `slug` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `excerpt` text DEFAULT NULL,
  `category` enum('general','announcement','procurement','service','health_tips') DEFAULT 'general',
  `featured_image` varchar(255) DEFAULT NULL,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `author_id` int(11) DEFAULT NULL,
  `publish_date` datetime DEFAULT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `views` int(11) DEFAULT 0,
  `tags` text DEFAULT NULL,
  `meta_description` varchar(160) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  `is_urgent` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `slug`, `content`, `excerpt`, `category`, `featured_image`, `attachments`, `author_id`, `publish_date`, `status`, `views`, `tags`, `meta_description`, `is_featured`, `is_urgent`, `created_at`, `updated_at`) VALUES
(11, 'test', 'test', 'test', '', 'announcement', NULL, '[]', 8, '2025-06-24 10:59:28', 'published', 1, '', NULL, 0, 1, '2025-06-24 03:59:28', '2025-06-24 04:01:12'),
(12, 'mglv[', 'mglv', 'mflv[', '', 'announcement', NULL, '[]', 8, '2025-06-24 10:59:46', 'published', 2, '', NULL, 1, 0, '2025-06-24 03:59:46', '2025-06-24 04:44:01'),
(13, 'test', 'test-1750737635', 'test', '', 'procurement', NULL, '[]', 8, '2025-06-24 11:00:50', 'published', 2, '', NULL, 0, 1, '2025-06-24 04:00:35', '2025-06-24 04:48:10'),
(14, 'testttt', 'testttt', 'testttt', '', 'general', NULL, '[]', 8, '2025-06-24 11:53:35', 'published', 2, '', NULL, 1, 0, '2025-06-24 04:53:35', '2025-07-01 04:19:52'),
(15, '123', '123', 'ะำหะๆไำกดหกด', '', 'announcement', NULL, '[]', 8, '2025-07-01 13:38:18', 'published', 2, '', NULL, 0, 0, '2025-07-01 06:38:18', '2025-07-01 12:44:37');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','number','boolean','json') DEFAULT 'string',
  `description` text DEFAULT NULL,
  `is_public` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `is_public`, `created_at`, `updated_at`) VALUES
(1, 'hospital_name', 'โรงพยาบาลทุ่งหัวช้าง', 'string', 'Hospital Hospital name', 1, '2025-06-18 09:22:57', '2025-07-01 11:35:25'),
(2, 'hospital_name_en', 'Tung Hua Chang Hospital', 'string', 'Hospital Hospital name en', 1, '2025-06-18 09:22:57', '2025-07-01 11:35:25'),
(3, 'hospital_address', '48 หมู่ที่ 3 ตำบลทุ่งหัวช้าง อำเภอทุ่งหัวช้าง จังหวัดลำพูน 51160', 'string', 'Hospital Hospital address', 1, '2025-06-18 09:22:57', '2025-07-01 11:35:25'),
(4, 'hospital_phone', '053-975-201', 'string', 'Hospital Hospital phone', 1, '2025-06-18 09:22:57', '2025-07-01 11:35:25'),
(5, 'hospital_emergency', '053-580-xxx', 'string', 'เบอร์โทรศัพท์ฉุกเฉิน', 1, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(6, 'hospital_email', 'info@thchospital.go.th', 'string', 'Hospital Hospital email', 1, '2025-06-18 09:22:57', '2025-07-01 11:35:25'),
(7, 'operating_hours_weekday', '08:00-16:30', 'string', 'เวลาทำการวันธรรมดา', 1, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(8, 'operating_hours_weekend', '08:00-12:00', 'string', 'เวลาทำการวันหยุด', 1, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(9, 'appointment_advance_days', '30', 'number', 'จำนวนวันที่สามารถนัดล่วงหน้าได้', 0, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(10, 'appointment_slots_per_hour', '4', 'number', 'จำนวนคิวต่อชั่วโมง', 0, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(11, 'max_appointments_per_day', '50', 'number', 'จำนวนนัดหมายสูงสุดต่อวัน', 0, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(12, 'email_notifications', 'true', 'boolean', 'เปิดใช้การแจ้งเตือนทางอีเมล', 0, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(13, 'sms_notifications', 'false', 'boolean', 'เปิดใช้การแจ้งเตือนทาง SMS', 0, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(14, 'auto_confirm_appointments', '0', 'boolean', 'ยืนยันนัดหมายอัตโนมัติ', 0, '2025-06-18 09:22:57', '2025-06-19 04:24:52'),
(15, 'website_maintenance', 'false', 'boolean', 'โหมดปิดปรับปรุงเว็บไซต์', 0, '2025-06-18 09:22:57', '2025-06-18 09:22:57'),
(36, 'hospital_fax', '053-975-200', 'string', 'Hospital Hospital fax', 0, '2025-06-19 03:34:07', '2025-07-01 11:35:25'),
(38, 'emergency_phone', '1669', 'string', 'Hospital Emergency phone', 0, '2025-06-19 03:34:07', '2025-07-01 11:35:25'),
(39, 'website_url', 'https://www.thchospital.go.th', 'string', 'Hospital Website url', 0, '2025-06-19 03:34:07', '2025-07-01 11:35:25'),
(40, 'timezone', 'Asia/Bangkok', 'string', 'Hospital Timezone', 0, '2025-06-19 03:34:07', '2025-07-01 11:35:25'),
(50, 'max_advance_days', '30', 'number', 'จำนวนวันที่สามารถนัดล่วงหน้าได้', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(51, 'min_advance_hours', '24', 'number', 'จำนวนชั่วโมงขั้นต่ำที่ต้องนัดล่วงหน้า', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(52, 'appointment_duration', '30', 'number', 'ระยะเวลาการนัดหมาย (นาที)', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(53, 'send_sms_notifications', '0', 'boolean', 'ส่งการแจ้งเตือนผ่าน SMS', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(54, 'send_email_notifications', '1', 'boolean', 'ส่งการแจ้งเตือนผ่านอีเมล', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(55, 'working_days', '1,2,3,4,5', 'string', 'วันทำการ (1=จันทร์, 7=อาทิตย์)', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(56, 'working_hours_start', '08:00', 'string', 'เวลาเริ่มงาน', 0, '2025-06-19 04:24:52', '2025-07-01 11:35:25'),
(57, 'working_hours_end', '16:00', 'string', 'เวลาเลิกงาน', 0, '2025-06-19 04:24:52', '2025-07-01 11:35:25'),
(58, 'weekend_hours_start', '08:00', 'string', 'เวลาเริ่มงานวันหยุด', 0, '2025-06-19 04:24:52', '2025-07-01 11:35:25'),
(59, 'weekend_hours_end', '12:00', 'string', 'เวลาเลิกงานวันหยุด', 0, '2025-06-19 04:24:52', '2025-07-01 11:35:25'),
(60, 'lunch_break_start', '12:00', 'string', 'เวลาเริ่มพักกลางวัน', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(61, 'lunch_break_end', '13:00', 'string', 'เวลาเลิกพักกลางวัน', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(62, 'enable_online_booking', '1', 'boolean', 'เปิดใช้งานการจองออนไลน์', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(63, 'maintenance_mode', '0', 'boolean', 'โหมดบำรุงรักษา', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(64, 'maintenance_message', 'ระบบอยู่ในช่วงปรับปรุง กรุณาลองใหม่ภายหลัง', 'string', 'ข้อความโหมดบำรุงรักษา', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(65, 'session_timeout', '120', 'number', 'เวลาหมดอายุเซสชัน (นาที)', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(66, 'max_login_attempts', '5', 'number', 'จำนวนครั้งการเข้าสู่ระบบผิดสูงสุด', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(67, 'login_lockout_time', '30', 'number', 'เวลาล็อคบัญชี (นาที)', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(68, 'password_min_length', '6', 'number', 'ความยาวรหัสผ่านขั้นต่ำ', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(69, 'require_password_complexity', '1', 'boolean', 'บังคับใช้รหัสผ่านที่ซับซ้อน', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(70, 'enable_registration', '1', 'boolean', 'เปิดใช้งานการสมัครสมาชิก', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:38'),
(71, 'enable_api', '1', 'boolean', 'เปิดใช้งาน API', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:39'),
(72, 'log_retention_days', '90', 'number', 'จำนวนวันเก็บ log', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:39'),
(73, 'backup_retention_days', '30', 'number', 'จำนวนวันเก็บไฟล์สำรอง', 0, '2025-06-19 04:24:52', '2025-06-23 09:37:39'),
(74, 'enable_two_factor', '0', 'boolean', 'เปิดใช้งาน Two-Factor Authentication', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(75, 'force_https', '0', 'boolean', 'บังคับใช้ HTTPS', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(76, 'smtp_host', '', 'string', 'SMTP Server', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(77, 'smtp_port', '587', 'number', 'SMTP Port', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(78, 'smtp_username', '', 'string', 'SMTP Username', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(79, 'smtp_password', '', 'string', 'SMTP Password', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(80, 'smtp_encryption', 'tls', 'string', 'SMTP Encryption', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(81, 'sms_provider', '', 'string', 'SMS Provider', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(82, 'sms_api_key', '', 'string', 'SMS API Key', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(83, 'sms_sender_name', '', 'string', 'SMS Sender Name', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(84, 'notification_email', '', 'string', 'อีเมลสำหรับการแจ้งเตือน', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(85, 'enable_appointment_reminders', '1', 'boolean', 'เปิดใช้งานการแจ้งเตือนนัดหมาย', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(86, 'reminder_hours_before', '24', 'number', 'แจ้งเตือนก่อนนัดหมาย (ชั่วโมง)', 0, '2025-06-19 04:24:52', '2025-06-19 04:24:52'),
(145, 'auto_backup_enabled', '0', 'number', 'System Auto backup enabled', 0, '2025-06-23 09:37:12', '2025-06-23 09:37:39'),
(146, 'backup_frequency', 'weekly', 'string', 'System Backup frequency', 0, '2025-06-23 09:37:12', '2025-06-23 09:37:39'),
(212, 'website_title', 'โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน', 'string', 'Website Website title', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(213, 'website_description', 'โรงพยาบาลทุ่งหัวช้าง จังหวัดลำพูน ให้บริการด้วยใจ เพื่อสุขภาพที่ดีของประชาชน', 'string', 'Website Website description', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(214, 'website_keywords', 'โรงพยาบาล, ลำพูน, ทุ่งหัวช้าง, สุขภาพ, แพทย์, รักษา', 'string', 'Website Website keywords', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(215, 'facebook_url', '', 'string', 'Website Facebook url', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(216, 'line_id', '', 'string', 'Website Line id', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(217, 'google_analytics_id', '', 'string', 'Website Google analytics id', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(218, 'show_statistics', '1', 'number', 'Website Show statistics', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(219, 'show_doctors', '1', 'number', 'Website Show doctors', 0, '2025-06-24 04:43:44', '2025-06-24 04:43:44'),
(220, 'news_per_page', '8', 'number', 'Website News per page', 0, '2025-06-24 04:43:45', '2025-06-24 04:43:45'),
(221, 'allow_comments', '1', 'number', 'Website Allow comments', 0, '2025-06-24 04:43:45', '2025-06-24 04:43:45');

-- --------------------------------------------------------

--
-- Table structure for table `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(100) DEFAULT NULL,
  `config_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;

--
-- Dumping data for table `system_config`
--

INSERT INTO `system_config` (`id`, `config_key`, `config_value`, `updated_at`) VALUES
(1, 'site_name', 'โรงพยาบาลทุ่งหัวช้าง', '2025-07-01 19:44:54'),
(2, 'site_description', 'ระบบจัดการโรงพยาบาล', '2025-07-01 19:44:54'),
(3, 'admin_email', 'admin@tunghuachang-hospital.com', '2025-07-01 19:44:54'),
(4, 'timezone', 'Asia/Bangkok', '2025-07-01 19:44:54'),
(5, 'per_page', '10', '2025-07-01 19:44:54');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','doctor','nurse','staff') NOT NULL,
  `department_id` int(11) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `last_login` timestamp NULL DEFAULT NULL,
  `login_attempts` int(11) DEFAULT 0,
  `locked_until` timestamp NULL DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `first_name`, `last_name`, `role`, `department_id`, `phone`, `last_login`, `login_attempts`, `locked_until`, `is_active`, `created_at`, `updated_at`) VALUES
(8, 'admin', 'admin@thchospital.go.th', '$2a$12$IzNc/QZs.JQEncZKOIjjyO3kHGCDSKLsyXmgSLxZli/950zUP8ZGi', 'ผู้ดูแล', 'ระบบ', 'admin', NULL, NULL, '2025-07-01 19:44:38', 0, NULL, 1, '2025-06-18 09:25:25', '2025-07-01 19:44:38'),
(9, 'staff', 'staff@thchospital.go.th', '$2a$12$IzNc/QZs.JQEncZKOIjjyO3kHGCDSKLsyXmgSLxZli/950zUP8ZGi', 'เจ้าหน้าที่', 'ทั่วไป', 'staff', 1, NULL, '2025-07-01 19:19:27', 0, NULL, 1, '2025-06-18 09:25:25', '2025-07-01 19:19:27'),
(10, 'doctor', 'doctor@thchospital.go.th', '$2a$12$IzNc/QZs.JQEncZKOIjjyO3kHGCDSKLsyXmgSLxZli/950zUP8ZGi', 'แพทย์', 'ทั่วไป', 'doctor', 1, NULL, '2025-06-23 09:53:35', 0, NULL, 1, '2025-06-18 09:25:25', '2025-06-23 09:53:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_date` (`user_id`,`created_at`),
  ADD KEY `idx_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_created_at` (`created_at`),
  ADD KEY `idx_activity_logs_created_at` (`created_at`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`),
  ADD KEY `idx_status_publish` (`status`,`publish_date`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_featured` (`is_featured`),
  ADD KEY `idx_news_category_status` (`category`,`status`),
  ADD KEY `idx_news_publish_date` (`publish_date`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `department_id` (`department_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=163;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=287;

--
-- AUTO_INCREMENT for table `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

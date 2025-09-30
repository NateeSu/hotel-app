-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 30, 2025 at 05:16 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Database: `hotel_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(10) UNSIGNED DEFAULT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) DEFAULT NULL,
  `record_id` int(10) UNSIGNED DEFAULT NULL,
  `old_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_values`)),
  `new_values` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_values`)),
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `table_name`, `record_id`, `old_values`, `new_values`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:45:33'),
(2, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36 Edg/140.0.0.0', '2025-09-27 05:50:55'),
(3, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:54:23'),
(4, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 05:54:59'),
(5, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 14:23:53'),
(6, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 17:11:04'),
(7, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 17:11:17'),
(8, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 17:17:37'),
(9, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 17:17:55'),
(10, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 17:50:02'),
(11, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-27 17:50:29'),
(12, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 07:55:21'),
(13, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:31:20'),
(14, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 13:31:32'),
(15, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 17:18:01'),
(16, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-28 22:47:10'),
(17, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 05:38:15'),
(18, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-29 13:13:41'),
(19, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 01:56:18'),
(20, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:20:42'),
(21, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 03:20:54'),
(22, 1, 'logout', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:54:49'),
(23, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 08:55:04'),
(24, 1, 'login', 'users', 1, NULL, NULL, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/140.0.0.0 Safari/537.36', '2025-09-30 15:04:35');

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_code` varchar(20) DEFAULT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `guest_phone` varchar(20) DEFAULT NULL,
  `guest_id_number` varchar(20) DEFAULT NULL,
  `guest_count` int(11) DEFAULT 1,
  `plan_type` enum('short','overnight') NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `transfer_count` int(11) DEFAULT 0,
  `checkin_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `checkout_at` timestamp NULL DEFAULT NULL,
  `base_amount` decimal(10,2) DEFAULT 0.00,
  `extra_amount` decimal(10,2) DEFAULT 0.00,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `payment_method` enum('cash','card','transfer') DEFAULT 'cash',
  `payment_status` enum('pending','paid','partial') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `booking_code`, `room_id`, `guest_name`, `guest_phone`, `guest_id_number`, `guest_count`, `plan_type`, `status`, `transfer_count`, `checkin_at`, `checkout_at`, `base_amount`, `extra_amount`, `total_amount`, `payment_method`, `payment_status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'BK25092701', 2, 'คุณสมชาย ใจดี', '0812345678', NULL, 1, 'short', 'completed', 0, '2025-09-27 14:25:18', '2025-09-27 17:16:19', 300.00, 0.00, 300.00, 'cash', 'paid', 'Check-in 14:00\nCheck-out: 2025-09-28 00:16:19', 1, '2025-09-27 14:25:18', '2025-09-27 17:16:19'),
(2, 'BK25092702', 7, 'คุณสมหญิง รักดี', '0891234567', NULL, 1, 'overnight', 'completed', 0, '2025-09-27 14:25:18', '2025-09-27 17:38:20', 800.00, 0.00, 800.00, 'cash', 'paid', 'Check-in 20:00\nCheck-out: 2025-09-28 00:38:20', 1, '2025-09-27 14:25:18', '2025-09-27 17:38:20'),
(3, 'BK25092703', 11, 'คุณสมปอง มีสุข', '0823456789', NULL, 1, 'overnight', 'completed', 0, '2025-09-27 14:25:18', '2025-09-27 17:41:04', 800.00, 0.00, 800.00, 'card', 'paid', 'VIP guest\nCheck-out: 2025-09-28 00:41:04', 1, '2025-09-27 14:25:18', '2025-09-27 17:41:04'),
(4, 'BK25092704', 17, 'คุณสมศรี อยู่ดี', '0834567890', NULL, 1, 'overnight', 'completed', 0, '2025-09-27 14:25:18', '2025-09-27 17:41:51', 800.00, 0.00, 800.00, 'transfer', 'paid', 'Regular customer\nCheck-out: 2025-09-28 00:41:51', 1, '2025-09-27 14:25:18', '2025-09-27 17:41:51'),
(5, 'BK250927746', 1, 'ทดสอบ', '0810473257', '', 2, 'short', 'completed', 0, '2025-09-27 15:13:01', '2025-09-27 17:35:05', 300.00, 0.00, 300.00, 'cash', 'paid', '\nCheck-out: 2025-09-28 00:35:05', 1, '2025-09-27 15:13:01', '2025-09-27 17:35:05'),
(6, 'BK250928840', 4, 'สมชาย', '0811231234', '', 1, 'overnight', 'completed', 0, '2025-09-27 17:13:43', '2025-09-27 17:35:58', 800.00, 0.00, 800.00, 'cash', 'paid', '\nCheck-out: 2025-09-28 00:35:58', 1, '2025-09-27 17:13:43', '2025-09-27 17:35:58'),
(7, 'BK250928161', 6, 'ใจดี', '0811231234', '', 1, 'short', 'completed', 0, '2025-09-27 17:18:25', '2025-09-27 17:18:52', 300.00, 0.00, 300.00, 'transfer', 'paid', '\nCheck-out: 2025-09-28 00:18:52', 1, '2025-09-27 17:18:25', '2025-09-27 17:18:52'),
(8, 'BK250928201', 18, 'ทดสอบเช็คอิน', '0810473257', '', 1, 'overnight', 'completed', 0, '2025-09-27 17:42:33', '2025-09-27 17:42:49', 800.00, 0.00, 800.00, 'cash', 'paid', '\nCheck-out: 2025-09-28 00:42:49', 1, '2025-09-27 17:42:33', '2025-09-27 17:42:49'),
(9, 'BK250928019', 8, 'ทดสอบ', '0810473257', '', 1, 'overnight', 'completed', 0, '2025-09-27 17:46:04', '2025-09-27 17:46:39', 800.00, 100.00, 900.00, 'card', 'paid', '\nCheck-out: 2025-09-28 00:46:39', 1, '2025-09-27 17:46:04', '2025-09-27 17:46:39'),
(10, 'BK001', 1, 'คุณสมชาย ใจดี', '081-234-5678', NULL, 1, 'short', 'completed', 0, '2025-09-28 03:42:51', '2025-09-28 06:12:51', 300.00, 0.00, 300.00, 'cash', 'paid', NULL, 1, '2025-09-28 06:42:51', '2025-09-28 06:42:51'),
(11, 'BK002', 3, 'คุณสมหญิง สวยงาม', '089-876-5432', NULL, 1, 'overnight', 'completed', 0, '2025-09-27 20:42:51', '2025-09-28 05:42:51', 800.00, 0.00, 900.00, 'cash', 'paid', NULL, 1, '2025-09-28 06:42:51', '2025-09-28 06:42:51'),
(12, 'BK003', 2, 'Mr. John Smith', '095-555-1234', NULL, 1, 'short', 'completed', 0, '2025-09-28 04:42:51', '2025-09-28 06:27:51', 300.00, 0.00, 400.00, 'cash', 'paid', NULL, 1, '2025-09-28 06:42:51', '2025-09-28 06:42:51'),
(13, 'BK250928317', 10, 'นางแม่บ้าน', '0811231234', '', 1, 'short', 'completed', 0, '2025-09-28 07:55:54', '2025-09-28 07:56:04', 300.00, 0.00, 300.00, 'cash', 'paid', '\nCheck-out: 2025-09-28 14:56:04', 1, '2025-09-28 07:55:54', '2025-09-28 07:56:04'),
(14, NULL, 1, 'ลูกค้าทดสอบ', '0812345678', NULL, 1, 'short', 'completed', 0, '2025-09-28 05:57:57', '2025-09-28 07:57:57', 300.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 07:57:57', '2025-09-28 07:57:57'),
(15, NULL, 1, 'Integration Test Guest', '0987654321', NULL, 1, 'overnight', 'completed', 0, '2025-09-27 23:58:00', '2025-09-28 07:58:00', 800.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 07:58:00', '2025-09-28 07:58:00'),
(16, NULL, 1, 'ลูกค้าทดสอบ', '0812345678', NULL, 1, 'short', 'completed', 0, '2025-09-28 06:06:00', '2025-09-28 08:06:00', 300.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:06:00', '2025-09-28 08:06:00'),
(17, NULL, 1, 'Integration Test Guest', '0987654321', NULL, 1, 'overnight', 'completed', 0, '2025-09-28 00:06:03', '2025-09-28 08:06:03', 800.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:06:03', '2025-09-28 08:06:03'),
(18, NULL, 1, 'ลูกค้าทดสอบ', '0812345678', NULL, 1, 'short', 'completed', 0, '2025-09-28 06:07:38', '2025-09-28 08:07:38', 300.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:07:38', '2025-09-28 08:07:38'),
(19, NULL, 1, 'Integration Test Guest', '0987654321', NULL, 1, 'overnight', 'completed', 0, '2025-09-28 00:07:42', '2025-09-28 08:07:42', 800.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:07:42', '2025-09-28 08:07:42'),
(20, NULL, 1, 'ลูกค้าทดสอบ', '0812345678', NULL, 1, 'short', 'completed', 0, '2025-09-28 06:08:07', '2025-09-28 08:08:07', 300.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:08:07', '2025-09-28 08:08:07'),
(21, NULL, 1, 'Integration Test Guest', '0987654321', NULL, 1, 'overnight', 'completed', 0, '2025-09-28 00:08:11', '2025-09-28 08:08:11', 800.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:08:11', '2025-09-28 08:08:11'),
(22, NULL, 1, 'ลูกค้าทดสอบ', '0812345678', NULL, 1, 'short', 'completed', 0, '2025-09-28 06:16:47', '2025-09-28 08:16:47', 300.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:16:47', '2025-09-28 08:16:47'),
(23, NULL, 1, 'Integration Test Guest', '0987654321', NULL, 1, 'overnight', 'completed', 0, '2025-09-28 00:16:51', '2025-09-28 08:16:51', 800.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 08:16:51', '2025-09-28 08:16:51'),
(24, 'BK250928870', 3, 'สมควร', '0811231234', '', 1, 'short', 'completed', 0, '2025-09-28 08:23:59', '2025-09-28 08:24:06', 300.00, 0.00, 300.00, 'cash', 'paid', '\nCheck-out: 2025-09-28 15:24:06', 1, '2025-09-28 08:23:59', '2025-09-28 08:24:06'),
(25, 'BK250928015', 1, 'ใจดี', '0810473257', '', 1, 'short', 'active', 1, '2025-09-28 09:15:05', '2025-09-28 12:15:05', 300.00, 0.00, 300.00, 'transfer', 'pending', '', 1, '2025-09-28 09:15:05', '2025-09-28 14:05:12'),
(26, 'BK250928673', 1, 'สมหมาย', '0811231234', '', 1, 'short', 'completed', 0, '2025-09-28 13:43:09', '2025-09-28 14:06:50', 300.00, 0.00, 300.00, 'transfer', 'paid', '\nCheck-out: 2025-09-28 21:06:50', 1, '2025-09-28 13:43:09', '2025-09-28 14:06:50'),
(27, NULL, 1, 'ลูกค้าทดสอบ', '0812345678', NULL, 1, 'short', 'completed', 0, '2025-09-28 12:01:29', '2025-09-28 14:01:29', 300.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 14:01:29', '2025-09-28 14:01:29'),
(28, NULL, 1, 'Integration Test Guest', '0987654321', NULL, 1, 'overnight', 'completed', 0, '2025-09-28 06:01:33', '2025-09-28 14:01:33', 800.00, 0.00, 0.00, 'cash', 'pending', NULL, 0, '2025-09-28 14:01:33', '2025-09-28 14:01:33'),
(29, 'BK250928817', 1, 'ใจดี', '0811231234', '', 1, 'short', 'completed', 0, '2025-09-28 14:17:20', '2025-09-28 14:17:40', 300.00, 0.00, 300.00, 'transfer', 'paid', '\nCheck-out: 2025-09-28 21:17:40', 1, '2025-09-28 14:17:20', '2025-09-28 14:17:40'),
(30, 'BK250928178', 1, 'ทดสอบ', '0810473257', '', 1, 'short', 'completed', 0, '2025-09-28 15:08:14', '2025-09-28 15:09:57', 300.00, 0.00, 300.00, 'cash', 'paid', '\nCheck-out: 2025-09-28 22:09:57', 1, '2025-09-28 15:08:14', '2025-09-28 15:09:57'),
(31, 'BK250929472', 1, 'สมควร', '0811231234', '', 1, 'short', 'completed', 0, '2025-09-28 22:53:28', '2025-09-28 22:53:35', 300.00, 0.00, 300.00, 'cash', 'paid', '\nCheck-out: 2025-09-29 05:53:35', 1, '2025-09-28 22:53:28', '2025-09-28 22:53:35'),
(32, 'BK250929338', 3, 'ใจดี', '0810473257', '', 1, 'short', 'completed', 0, '2025-09-29 05:38:56', '2025-09-29 05:39:20', 300.00, 0.00, 300.00, 'cash', 'paid', '\nCheck-out: 2025-09-29 12:39:20', 1, '2025-09-29 05:38:56', '2025-09-29 05:39:20'),
(33, 'BK250930932', 4, 'ทดสอบ', '0811231234', '', 1, 'overnight', 'active', 1, '2025-09-30 02:15:59', '2025-10-02 05:00:00', 1600.00, 0.00, 1600.00, 'cash', 'pending', '', 1, '2025-09-30 02:15:59', '2025-09-30 03:42:21'),
(34, 'BK250930645', 3, 'ทดสอบเวลา', '0810473257', '', 1, 'overnight', 'active', 0, '2025-09-30 03:39:34', '2025-10-01 05:00:00', 800.00, 0.00, 800.00, 'cash', 'pending', '', 1, '2025-09-30 03:39:34', '2025-09-30 03:39:34');

-- --------------------------------------------------------

--
-- Stand-in structure for view `daily_transfer_stats`
-- (See below for the actual view)
--
CREATE TABLE `daily_transfer_stats` (
`transfer_date` date
,`total_transfers` bigint(21)
,`upgrades` decimal(22,0)
,`downgrades` decimal(22,0)
,`maintenance_moves` decimal(22,0)
,`guest_requests` decimal(22,0)
,`total_revenue_impact` decimal(32,2)
,`avg_adjustment` decimal(14,6)
);

-- --------------------------------------------------------

--
-- Table structure for table `hotel_settings`
--

CREATE TABLE `hotel_settings` (
  `id` int(10) UNSIGNED NOT NULL,
  `setting_key` varchar(255) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_by` int(10) UNSIGNED DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `hotel_settings`
--

INSERT INTO `hotel_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `description`, `created_at`, `updated_by`, `updated_at`) VALUES
(1, 'telegram_bot_token', '7142045288:AAEoWsFzuHaFSY9ifPtt2iGSGcuY7hZ59tg', 'text', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 06:53:07'),
(2, 'default_housekeeping_chat_id', '83617332', 'text', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 08:04:15'),
(3, 'notification_enabled', 'true', 'boolean', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 06:42:51'),
(4, 'housekeeping_notification_template', '🧹 งานทำความสะอาดใหม่!', 'text', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 06:42:51'),
(5, 'transfer_reasons', '{\"upgrade\":\"อัพเกรดห้อง\",\"downgrade\":\"ดาวน์เกรดห้อง\",\"maintenance\":\"ซ่อมบำรุงห้อง\",\"guest_request\":\"ตามความต้องการแขก\",\"overbooking\":\"จองเกิน\",\"room_issue\":\"ปัญหาห้อง\",\"other\":\"อื่นๆ\"}', 'json', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 09:14:16'),
(6, 'transfer_tax_rate', '0.07', 'number', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 09:14:16'),
(7, 'transfer_service_charge_rate', '0.10', 'number', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 09:14:16'),
(8, 'auto_notify_guest', '1', 'boolean', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 09:14:16'),
(9, 'auto_notify_housekeeping', '1', 'boolean', NULL, '2025-09-28 22:51:27', NULL, '2025-09-28 09:14:16'),
(11, 'hotel_name', 'กระนวนรีสอร์ท', 'text', NULL, '2025-09-28 22:51:59', NULL, '2025-09-28 22:51:59'),
(12, 'hotel_address', 'อ.กระนวน จ.ขอนแก่น', 'text', NULL, '2025-09-28 22:51:59', NULL, '2025-09-28 22:51:59'),
(13, 'hotel_phone', '044123123', 'text', NULL, '2025-09-28 22:51:59', NULL, '2025-09-28 22:51:59'),
(14, 'hotel_email', '', 'text', NULL, '2025-09-28 22:51:59', NULL, '2025-09-28 22:51:59');

-- --------------------------------------------------------

--
-- Table structure for table `housekeeping_jobs`
--

CREATE TABLE `housekeeping_jobs` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED DEFAULT NULL,
  `job_type` enum('cleaning','maintenance','inspection') NOT NULL DEFAULT 'cleaning',
  `task_type` enum('checkout_cleaning','maintenance','inspection') DEFAULT 'checkout_cleaning',
  `status` enum('pending','in_progress','completed') NOT NULL DEFAULT 'pending',
  `priority` enum('low','normal','high','urgent') NOT NULL DEFAULT 'normal',
  `description` text DEFAULT NULL,
  `assigned_to` int(10) UNSIGNED DEFAULT NULL,
  `completed_by` int(10) UNSIGNED DEFAULT NULL,
  `started_at` timestamp NULL DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `completion_notes` text DEFAULT NULL,
  `estimated_duration` int(11) DEFAULT NULL COMMENT 'Estimated duration in minutes',
  `actual_duration` int(11) DEFAULT NULL COMMENT 'Actual duration in minutes',
  `notes` text DEFAULT NULL,
  `special_notes` text DEFAULT NULL,
  `telegram_sent` tinyint(1) DEFAULT 0,
  `created_by` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `housekeeping_jobs`
--

INSERT INTO `housekeeping_jobs` (`id`, `room_id`, `booking_id`, `job_type`, `task_type`, `status`, `priority`, `description`, `assigned_to`, `completed_by`, `started_at`, `completed_at`, `completion_notes`, `estimated_duration`, `actual_duration`, `notes`, `special_notes`, `telegram_sent`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 2, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', 1, NULL, '2025-09-28 08:15:39', '2025-09-28 08:15:46', NULL, NULL, 0, NULL, ' - เสร็จโดย: ผู้ดูแลระบบ', 0, 1, '2025-09-27 17:16:19', '2025-09-28 08:15:46'),
(2, 6, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 08:37:59', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:18:52', '2025-09-28 08:37:59'),
(3, 1, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 15:13:04', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:35:05', '2025-09-28 15:13:04'),
(4, 4, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 08:37:18', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:35:58', '2025-09-28 08:37:18'),
(5, 7, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 08:39:56', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:38:20', '2025-09-28 08:39:56'),
(6, 11, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 13:32:55', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:41:04', '2025-09-28 13:32:55'),
(7, 17, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-29 13:15:20', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:41:51', '2025-09-29 13:15:20'),
(8, 18, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 08:40:22', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:42:49', '2025-09-28 08:40:22'),
(9, 8, NULL, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 08:39:51', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-27 17:46:39', '2025-09-28 08:39:51'),
(10, 1, 8, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out ห้อง 101', NULL, NULL, NULL, '2025-09-28 15:13:04', NULL, NULL, NULL, '', 'แขกทิ้งผ้าเปียกในห้องน้ำ', 0, 1, '2025-09-28 06:42:51', '2025-09-28 15:13:04'),
(11, 3, 9, 'cleaning', 'checkout_cleaning', 'completed', 'high', 'ทำความสะอาดหลัง check-out ห้อง 103', 1, NULL, '2025-09-28 05:57:51', '2025-09-28 08:16:00', NULL, NULL, 138, NULL, 'ห้องค้างคืน ต้องเปลี่ยนผ้าปูที่นอน - เสร็จโดย: ผู้ดูแลระบบ', 1, 1, '2025-09-28 06:42:51', '2025-09-28 08:16:00'),
(12, 2, 10, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out ห้อง 102', 2, NULL, '2025-09-28 05:12:51', '2025-09-28 06:32:51', NULL, NULL, 80, NULL, 'ทำความสะอาดเรียบร้อยแล้ว', 1, 1, '2025-09-28 06:42:51', '2025-09-28 06:42:51'),
(13, 10, 13, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-29 13:15:03', NULL, NULL, NULL, '', NULL, 1, 1, '2025-09-28 07:56:04', '2025-09-29 13:15:03'),
(14, 1, 14, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบระบบแจ้งเตือน', 1, NULL, '2025-09-28 07:58:00', '2025-09-28 07:58:00', NULL, NULL, 0, NULL, NULL, 0, 1, '2025-09-28 07:57:57', '2025-09-28 07:58:00'),
(15, 1, 15, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบครบวงจร', 1, NULL, '2025-09-28 07:58:04', '2025-09-28 07:58:04', NULL, NULL, 25, NULL, NULL, 0, 1, '2025-09-28 07:58:00', '2025-09-28 07:58:04'),
(16, 1, 16, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบระบบแจ้งเตือน', 1, NULL, '2025-09-28 08:06:03', '2025-09-28 08:06:03', NULL, NULL, 0, NULL, NULL, 0, 1, '2025-09-28 08:06:00', '2025-09-28 08:06:03'),
(17, 1, 17, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบครบวงจร', 1, NULL, '2025-09-28 08:06:06', '2025-09-28 08:06:06', NULL, NULL, 25, NULL, NULL, 0, 1, '2025-09-28 08:06:03', '2025-09-28 08:06:06'),
(18, 1, 18, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบระบบแจ้งเตือน', 1, NULL, '2025-09-28 08:07:42', '2025-09-28 08:07:42', NULL, NULL, 0, NULL, NULL, 0, 1, '2025-09-28 08:07:38', '2025-09-28 08:07:42'),
(19, 1, 19, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบครบวงจร', 1, NULL, '2025-09-28 08:07:45', '2025-09-28 08:07:45', NULL, NULL, 25, NULL, NULL, 0, 1, '2025-09-28 08:07:42', '2025-09-28 08:07:45'),
(20, 1, 20, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบระบบแจ้งเตือน', 1, NULL, '2025-09-28 08:08:11', '2025-09-28 08:08:11', NULL, NULL, 0, NULL, NULL, 0, 1, '2025-09-28 08:08:07', '2025-09-28 08:08:11'),
(21, 1, 21, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบครบวงจร', 1, NULL, '2025-09-28 08:08:14', '2025-09-28 08:08:14', NULL, NULL, 25, NULL, NULL, 0, 1, '2025-09-28 08:08:11', '2025-09-28 08:08:14'),
(22, 1, 22, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบระบบแจ้งเตือน', 1, NULL, '2025-09-28 08:16:51', '2025-09-28 08:16:51', NULL, NULL, 0, NULL, NULL, 0, 1, '2025-09-28 08:16:47', '2025-09-28 08:16:51'),
(23, 1, 23, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบครบวงจร', 1, NULL, '2025-09-28 08:16:55', '2025-09-28 08:16:55', NULL, NULL, 25, NULL, NULL, 0, 1, '2025-09-28 08:16:51', '2025-09-28 08:16:55'),
(24, 3, 24, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', 1, NULL, '2025-09-28 08:24:34', '2025-09-28 08:24:39', NULL, NULL, 0, NULL, ' - เสร็จโดย: ผู้ดูแลระบบ', 1, 1, '2025-09-28 08:24:06', '2025-09-28 08:24:39'),
(25, 1, 27, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบระบบแจ้งเตือน', 1, NULL, '2025-09-28 14:01:33', '2025-09-28 14:01:33', NULL, NULL, 0, NULL, NULL, 0, 1, '2025-09-28 14:01:29', '2025-09-28 14:01:33'),
(26, 1, 28, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทดสอบครบวงจร', 1, NULL, '2025-09-28 14:01:38', '2025-09-28 14:01:38', NULL, NULL, 25, NULL, NULL, 0, 1, '2025-09-28 14:01:33', '2025-09-28 14:01:38'),
(27, 2, 25, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'Room transfer cleanup', NULL, NULL, NULL, '2025-09-28 14:05:41', NULL, NULL, NULL, '', NULL, 0, 1, '2025-09-28 14:05:16', '2025-09-28 14:05:41'),
(28, 1, 26, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', 1, NULL, '2025-09-28 14:07:49', '2025-09-28 14:08:07', NULL, NULL, 0, NULL, ' - เสร็จโดย: ผู้ดูแลระบบ', 1, 1, '2025-09-28 14:06:50', '2025-09-28 14:08:07'),
(29, 1, 29, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', 1, NULL, '2025-09-28 14:18:31', '2025-09-28 14:44:15', NULL, NULL, 25, NULL, ' - เสร็จโดย: ผู้ดูแลระบบ', 1, 1, '2025-09-28 14:17:40', '2025-09-28 14:44:15'),
(30, 1, 30, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-28 15:13:04', NULL, NULL, NULL, '', NULL, 1, 1, '2025-09-28 15:09:57', '2025-09-28 15:13:04'),
(31, 1, 31, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', NULL, NULL, NULL, '2025-09-29 05:41:03', NULL, NULL, NULL, '', NULL, 1, 1, '2025-09-28 22:53:35', '2025-09-29 05:41:03'),
(32, 3, 32, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'ทำความสะอาดหลัง check-out', 1, NULL, '2025-09-29 05:40:36', '2025-09-29 05:40:50', NULL, NULL, 0, NULL, ' - เสร็จโดย: ผู้ดูแลระบบ', 1, 1, '2025-09-29 05:39:20', '2025-09-29 05:40:50'),
(33, 2, 33, 'cleaning', 'checkout_cleaning', 'completed', 'normal', 'Room transfer cleanup', 1, NULL, '2025-09-30 09:44:09', '2025-09-30 09:44:13', NULL, NULL, 0, NULL, ' - เสร็จโดย: ผู้ดูแลระบบ', 0, 1, '2025-09-30 03:42:25', '2025-09-30 09:44:13');

-- --------------------------------------------------------

--
-- Stand-in structure for view `housekeeping_performance`
-- (See below for the actual view)
--
CREATE TABLE `housekeeping_performance` (
`id` int(10) unsigned
,`room_id` int(10) unsigned
,`room_number` varchar(10)
,`task_type` varchar(17)
,`priority` enum('low','normal','high','urgent')
,`created_at` timestamp
,`started_at` timestamp
,`completed_at` timestamp
,`duration_minutes` bigint(21)
,`current_status` varchar(11)
,`assigned_to` int(10) unsigned
,`assigned_to_name` varchar(100)
,`telegram_sent` int(4)
);

-- --------------------------------------------------------

--
-- Table structure for table `rates`
--

CREATE TABLE `rates` (
  `id` int(10) UNSIGNED NOT NULL,
  `rate_type` varchar(50) NOT NULL,
  `description` varchar(100) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rates`
--

INSERT INTO `rates` (`id`, `rate_type`, `description`, `price`, `duration_hours`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'short_3h', 'Short-term stay (3 hours)', 300.00, 3, 1, '2025-09-27 05:30:56', '2025-09-27 05:30:56'),
(2, 'overnight', 'Overnight stay', 800.00, 12, 1, '2025-09-27 05:30:56', '2025-09-27 05:30:56'),
(3, 'extended', 'Extended hourly rate', 100.00, 1, 1, '2025-09-27 05:30:56', '2025-09-27 05:30:56');

-- --------------------------------------------------------

--
-- Table structure for table `receipts`
--

CREATE TABLE `receipts` (
  `id` int(10) UNSIGNED NOT NULL,
  `receipt_number` varchar(20) NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `booking_code` varchar(20) NOT NULL,
  `guest_name` varchar(255) NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card','transfer') NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `generated_by` int(10) UNSIGNED DEFAULT NULL,
  `receipt_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`receipt_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `receipts`
--

INSERT INTO `receipts` (`id`, `receipt_number`, `booking_id`, `booking_code`, `guest_name`, `room_number`, `total_amount`, `payment_method`, `generated_at`, `generated_by`, `receipt_data`) VALUES
(1, 'RC2509280005', 5, 'BK250927746', 'ทดสอบ', '101', 300.00, 'cash', '2025-09-27 17:35:05', 1, '{\"receipt_number\":\"RC2509280005\",\"date\":\"28\\/09\\/2025\",\"time\":\"00:35:05\",\"booking_code\":\"BK250927746\",\"room_number\":\"101\",\"room_type\":\"short\",\"guest_name\":\"ทดสอบ\",\"guest_phone\":\"0810473257\",\"guest_id_number\":\"\",\"guest_count\":2,\"checkin_time\":\"27\\/09\\/2025 22:13\",\"checkout_time\":\"28\\/09\\/2025 00:35\",\"duration\":\"2.4 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(2, 'RC2509280006', 6, 'BK250928840', 'สมชาย', '104', 800.00, 'cash', '2025-09-27 17:35:58', 1, '{\"receipt_number\":\"RC2509280006\",\"date\":\"28\\/09\\/2025\",\"time\":\"00:35:58\",\"booking_code\":\"BK250928840\",\"room_number\":\"104\",\"room_type\":\"short\",\"guest_name\":\"สมชาย\",\"guest_phone\":\"0811231234\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 00:13\",\"checkout_time\":\"28\\/09\\/2025 00:35\",\"duration\":\"0.4 ชั่วโมง\",\"plan_type\":\"overnight\",\"plan_type_text\":\"รายคืน\",\"base_duration\":12,\"base_amount\":800,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":800,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(3, 'RC2509280002', 2, 'BK25092702', 'คุณสมหญิง รักดี', '202', 800.00, 'cash', '2025-09-27 17:38:20', 1, '{\"receipt_number\":\"RC2509280002\",\"date\":\"28\\/09\\/2025\",\"time\":\"00:38:20\",\"booking_code\":\"BK25092702\",\"room_number\":\"202\",\"room_type\":\"overnight\",\"guest_name\":\"คุณสมหญิง รักดี\",\"guest_phone\":\"0891234567\",\"guest_id_number\":\"-\",\"guest_count\":1,\"checkin_time\":\"27\\/09\\/2025 21:25\",\"checkout_time\":\"28\\/09\\/2025 00:38\",\"duration\":\"3.2 ชั่วโมง\",\"plan_type\":\"overnight\",\"plan_type_text\":\"รายคืน\",\"base_duration\":12,\"base_amount\":800,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":800,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(4, 'RC2509280003', 3, 'BK25092703', 'คุณสมปอง มีสุข', '206', 800.00, 'card', '2025-09-27 17:41:04', 1, '{\"receipt_number\":\"RC2509280003\",\"date\":\"28\\/09\\/2025\",\"time\":\"00:41:04\",\"booking_code\":\"BK25092703\",\"room_number\":\"206\",\"room_type\":\"overnight\",\"guest_name\":\"คุณสมปอง มีสุข\",\"guest_phone\":\"0823456789\",\"guest_id_number\":\"-\",\"guest_count\":1,\"checkin_time\":\"27\\/09\\/2025 21:25\",\"checkout_time\":\"28\\/09\\/2025 00:41\",\"duration\":\"3.3 ชั่วโมง\",\"plan_type\":\"overnight\",\"plan_type_text\":\"รายคืน\",\"base_duration\":12,\"base_amount\":800,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":800,\"payment_method\":\"card\",\"payment_method_text\":\"บัตรเครดิต\\/เดบิต\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(5, 'RC2509280004', 4, 'BK25092704', 'คุณสมศรี อยู่ดี', '212', 800.00, 'transfer', '2025-09-27 17:41:51', 1, '{\"receipt_number\":\"RC2509280004\",\"date\":\"28\\/09\\/2025\",\"time\":\"00:41:51\",\"booking_code\":\"BK25092704\",\"room_number\":\"212\",\"room_type\":\"overnight\",\"guest_name\":\"คุณสมศรี อยู่ดี\",\"guest_phone\":\"0834567890\",\"guest_id_number\":\"-\",\"guest_count\":1,\"checkin_time\":\"27\\/09\\/2025 21:25\",\"checkout_time\":\"28\\/09\\/2025 00:41\",\"duration\":\"3.3 ชั่วโมง\",\"plan_type\":\"overnight\",\"plan_type_text\":\"รายคืน\",\"base_duration\":12,\"base_amount\":800,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":800,\"payment_method\":\"transfer\",\"payment_method_text\":\"โอนเงิน\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(6, 'RC2509280008', 8, 'BK250928201', 'ทดสอบเช็คอิน', '213', 800.00, 'cash', '2025-09-27 17:42:49', 1, '{\"receipt_number\":\"RC2509280008\",\"date\":\"28\\/09\\/2025\",\"time\":\"00:42:49\",\"booking_code\":\"BK250928201\",\"room_number\":\"213\",\"room_type\":\"overnight\",\"guest_name\":\"ทดสอบเช็คอิน\",\"guest_phone\":\"0810473257\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 00:42\",\"checkout_time\":\"28\\/09\\/2025 00:42\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"overnight\",\"plan_type_text\":\"รายคืน\",\"base_duration\":12,\"base_amount\":800,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":800,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(7, 'RC2509280009', 9, 'BK250928019', 'ทดสอบ', '203', 900.00, 'card', '2025-09-27 17:46:39', 1, '{\"receipt_number\":\"RC2509280009\",\"date\":\"28\\/09\\/2025\",\"time\":\"00:46:39\",\"booking_code\":\"BK250928019\",\"room_number\":\"203\",\"room_type\":\"overnight\",\"guest_name\":\"ทดสอบ\",\"guest_phone\":\"0810473257\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 00:46\",\"checkout_time\":\"28\\/09\\/2025 00:46\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"overnight\",\"plan_type_text\":\"รายคืน\",\"base_duration\":12,\"base_amount\":800,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":100,\"extra_notes\":\"\",\"total_amount\":900,\"payment_method\":\"card\",\"payment_method_text\":\"บัตรเครดิต\\/เดบิต\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(8, 'RC2509280013', 13, 'BK250928317', 'นางแม่บ้าน', '205', 300.00, 'cash', '2025-09-28 07:56:10', 1, '{\"receipt_number\":\"RC2509280013\",\"date\":\"28\\/09\\/2025\",\"time\":\"14:56:10\",\"booking_code\":\"BK250928317\",\"room_number\":\"205\",\"room_type\":\"overnight\",\"guest_name\":\"นางแม่บ้าน\",\"guest_phone\":\"0811231234\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 14:55\",\"checkout_time\":\"28\\/09\\/2025 14:56\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(9, 'RC2509280024', 24, 'BK250928870', 'สมควร', '103', 300.00, 'cash', '2025-09-28 08:24:10', 1, '{\"receipt_number\":\"RC2509280024\",\"date\":\"28\\/09\\/2025\",\"time\":\"15:24:10\",\"booking_code\":\"BK250928870\",\"room_number\":\"103\",\"room_type\":\"short\",\"guest_name\":\"สมควร\",\"guest_phone\":\"0811231234\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 15:23\",\"checkout_time\":\"28\\/09\\/2025 15:24\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(10, 'RC2509280026', 26, 'BK250928673', 'สมหมาย', '101', 300.00, 'transfer', '2025-09-28 14:06:54', 1, '{\"receipt_number\":\"RC2509280026\",\"date\":\"28\\/09\\/2025\",\"time\":\"21:06:54\",\"booking_code\":\"BK250928673\",\"room_number\":\"101\",\"room_type\":\"short\",\"guest_name\":\"สมหมาย\",\"guest_phone\":\"0811231234\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 20:43\",\"checkout_time\":\"28\\/09\\/2025 21:06\",\"duration\":\"0.4 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"transfer\",\"payment_method_text\":\"โอนเงิน\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(11, 'RC2509280029', 29, 'BK250928817', 'ใจดี', '101', 300.00, 'transfer', '2025-09-28 14:17:50', 1, '{\"receipt_number\":\"RC2509280029\",\"date\":\"28\\/09\\/2025\",\"time\":\"21:17:50\",\"booking_code\":\"BK250928817\",\"room_number\":\"101\",\"room_type\":\"short\",\"guest_name\":\"ใจดี\",\"guest_phone\":\"0811231234\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 21:17\",\"checkout_time\":\"28\\/09\\/2025 21:17\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"transfer\",\"payment_method_text\":\"โอนเงิน\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(12, 'RC2509280030', 30, 'BK250928178', 'ทดสอบ', '101', 300.00, 'cash', '2025-09-28 15:10:00', 1, '{\"receipt_number\":\"RC2509280030\",\"date\":\"28\\/09\\/2025\",\"time\":\"22:10:00\",\"booking_code\":\"BK250928178\",\"room_number\":\"101\",\"room_type\":\"short\",\"guest_name\":\"ทดสอบ\",\"guest_phone\":\"0810473257\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"28\\/09\\/2025 22:08\",\"checkout_time\":\"28\\/09\\/2025 22:09\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"HOTEL MANAGEMENT SYSTEM\",\"hotel_address\":\"123 ถนนสุขุมวิท แขวงคลองเตย เขตคลองเตย กรุงเทพฯ 10110\",\"hotel_phone\":\"02-123-4567\",\"hotel_email\":\"info@hotel.com\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(13, 'RC2509290031', 31, 'BK250929472', 'สมควร', '101', 300.00, 'cash', '2025-09-28 22:53:38', 1, '{\"receipt_number\":\"RC2509290031\",\"date\":\"29\\/09\\/2025\",\"time\":\"05:53:38\",\"booking_code\":\"BK250929472\",\"room_number\":\"101\",\"room_type\":\"short\",\"guest_name\":\"สมควร\",\"guest_phone\":\"0811231234\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"29\\/09\\/2025 05:53\",\"checkout_time\":\"29\\/09\\/2025 05:53\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"กระนวนรีสอร์ท\",\"hotel_address\":\"อ.กระนวน จ.ขอนแก่น\",\"hotel_phone\":\"044123123\",\"hotel_email\":\"\",\"tax_id\":\"0-1234-56789-01-2\"}'),
(14, 'RC2509290032', 32, 'BK250929338', 'ใจดี', '103', 300.00, 'cash', '2025-09-29 05:39:20', 1, '{\"receipt_number\":\"RC2509290032\",\"date\":\"29\\/09\\/2025\",\"time\":\"12:39:20\",\"booking_code\":\"BK250929338\",\"room_number\":\"103\",\"room_type\":\"short\",\"guest_name\":\"ใจดี\",\"guest_phone\":\"0810473257\",\"guest_id_number\":\"\",\"guest_count\":1,\"checkin_time\":\"29\\/09\\/2025 12:38\",\"checkout_time\":\"29\\/09\\/2025 12:39\",\"duration\":\"0.0 ชั่วโมง\",\"plan_type\":\"short\",\"plan_type_text\":\"รายชั่วโมง\",\"base_duration\":3,\"base_amount\":300,\"overtime_hours\":0,\"overtime_amount\":0,\"extra_amount\":0,\"extra_notes\":\"\",\"total_amount\":300,\"payment_method\":\"cash\",\"payment_method_text\":\"เงินสด\",\"processed_by\":\"ผู้ดูแลระบบ\",\"hotel_name\":\"กระนวนรีสอร์ท\",\"hotel_address\":\"อ.กระนวน จ.ขอนแก่น\",\"hotel_phone\":\"044123123\",\"hotel_email\":\"\",\"tax_id\":\"0-1234-56789-01-2\"}');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_number` varchar(10) NOT NULL,
  `room_type` enum('short','overnight') NOT NULL DEFAULT 'short',
  `status` enum('available','occupied','cleaning','maintenance') NOT NULL DEFAULT 'available',
  `floor` int(11) DEFAULT 1,
  `max_occupancy` int(11) DEFAULT 2,
  `amenities` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `last_transfer_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`id`, `room_number`, `room_type`, `status`, `floor`, `max_occupancy`, `amenities`, `notes`, `created_at`, `updated_at`, `last_transfer_date`) VALUES
(1, '101', 'short', 'available', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room', '2025-09-27 05:30:56', '2025-09-29 05:41:03', '2025-09-28 21:05:12'),
(2, '102', 'short', 'available', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room', '2025-09-27 05:30:56', '2025-09-30 09:44:13', NULL),
(3, '103', 'short', 'occupied', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room', '2025-09-27 05:30:56', '2025-09-30 03:39:34', NULL),
(4, '104', 'short', 'occupied', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room', '2025-09-27 05:30:56', '2025-09-30 03:42:21', '2025-09-30 10:42:21'),
(5, '105', 'short', 'maintenance', 1, 2, 'Air conditioning, TV, WiFi', 'Ground floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL),
(6, '201', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-28 08:37:59', NULL),
(7, '202', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-28 08:39:56', NULL),
(8, '203', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-28 08:39:51', NULL),
(9, '204', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-28 15:13:22', NULL),
(10, '205', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-29 13:15:03', NULL),
(11, '206', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-28 13:32:55', NULL),
(12, '207', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL),
(13, '208', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL),
(14, '209', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-29 13:15:14', NULL),
(15, '210', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL),
(16, '211', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL),
(17, '212', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-29 13:15:20', NULL),
(18, '213', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-28 08:40:22', NULL),
(19, '214', 'overnight', 'maintenance', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL),
(20, '215', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL),
(21, '216', 'overnight', 'available', 2, 2, 'Air conditioning, TV, WiFi, Refrigerator', 'Second floor room', '2025-09-27 05:30:56', '2025-09-27 05:30:56', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `room_rates`
--

CREATE TABLE `room_rates` (
  `id` int(10) UNSIGNED NOT NULL,
  `rate_type` enum('short','overnight') NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `duration_hours` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_rates`
--

INSERT INTO `room_rates` (`id`, `rate_type`, `price`, `duration_hours`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'short', 250.00, 3, 1, '2025-09-28 16:59:21', '2025-09-28 17:00:06'),
(2, 'overnight', 400.00, 12, 1, '2025-09-28 16:59:21', '2025-09-28 17:00:06');

-- --------------------------------------------------------

--
-- Table structure for table `room_status_logs`
--

CREATE TABLE `room_status_logs` (
  `id` int(10) UNSIGNED NOT NULL,
  `room_id` int(10) UNSIGNED NOT NULL,
  `previous_status` enum('available','occupied','cleaning','maintenance','out_of_order') NOT NULL,
  `new_status` enum('available','occupied','cleaning','maintenance','out_of_order') NOT NULL,
  `changed_by` int(10) UNSIGNED NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_status_logs`
--

INSERT INTO `room_status_logs` (`id`, `room_id`, `previous_status`, `new_status`, `changed_by`, `changed_at`, `notes`) VALUES
(1, 4, 'cleaning', 'available', 1, '2025-09-28 08:37:18', 'Cleaning completed: '),
(2, 4, 'available', 'available', 1, '2025-09-28 08:37:46', 'Cleaning completed: '),
(3, 6, 'cleaning', 'available', 1, '2025-09-28 08:37:59', 'Cleaning completed: '),
(4, 8, 'cleaning', 'available', 1, '2025-09-28 08:39:51', 'Cleaning completed: '),
(5, 7, 'cleaning', 'available', 1, '2025-09-28 08:39:56', 'Cleaning completed: '),
(6, 18, 'cleaning', 'available', 1, '2025-09-28 08:40:22', 'Cleaning completed: '),
(7, 11, 'cleaning', 'available', 1, '2025-09-28 13:32:55', 'Cleaning completed: '),
(8, 2, 'cleaning', 'available', 1, '2025-09-28 14:05:41', 'Cleaning completed: '),
(9, 1, 'cleaning', 'available', 1, '2025-09-28 15:13:04', 'Cleaning completed: '),
(10, 9, 'cleaning', 'available', 1, '2025-09-28 15:13:22', 'Cleaning completed: '),
(11, 1, 'cleaning', 'available', 1, '2025-09-29 05:41:03', 'Cleaning completed: '),
(12, 10, 'cleaning', 'available', 1, '2025-09-29 13:15:03', 'Cleaning completed: '),
(13, 14, 'cleaning', 'available', 1, '2025-09-29 13:15:14', 'Cleaning completed: '),
(14, 17, 'cleaning', 'available', 1, '2025-09-29 13:15:20', 'Cleaning completed: ');

-- --------------------------------------------------------

--
-- Table structure for table `room_transfers`
--

CREATE TABLE `room_transfers` (
  `id` int(10) UNSIGNED NOT NULL,
  `booking_id` int(10) UNSIGNED NOT NULL,
  `from_room_id` int(10) UNSIGNED NOT NULL,
  `to_room_id` int(10) UNSIGNED NOT NULL,
  `transfer_date` datetime NOT NULL DEFAULT current_timestamp(),
  `transfer_reason` enum('upgrade','downgrade','maintenance','guest_request','overbooking','room_issue','other') NOT NULL,
  `price_difference` decimal(10,2) DEFAULT 0.00,
  `additional_charges` decimal(10,2) DEFAULT 0.00,
  `total_adjustment` decimal(10,2) DEFAULT 0.00,
  `transferred_by` int(10) UNSIGNED NOT NULL,
  `guest_notified` tinyint(1) DEFAULT 0,
  `housekeeping_notified` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `status` enum('pending','completed','cancelled') DEFAULT 'pending',
  `completed_at` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `room_transfers`
--

INSERT INTO `room_transfers` (`id`, `booking_id`, `from_room_id`, `to_room_id`, `transfer_date`, `transfer_reason`, `price_difference`, `additional_charges`, `total_adjustment`, `transferred_by`, `guest_notified`, `housekeeping_notified`, `notes`, `status`, `completed_at`, `created_at`, `updated_at`) VALUES
(2, 25, 2, 1, '2025-09-28 21:05:12', 'guest_request', 0.00, 0.00, 0.00, 1, 0, 1, '', 'completed', NULL, '2025-09-28 14:05:12', '2025-09-28 14:05:12'),
(3, 33, 2, 4, '2025-09-30 10:42:21', 'guest_request', 0.00, 0.00, 0.00, 1, 0, 1, '', 'completed', NULL, '2025-09-30 03:42:21', '2025-09-30 03:42:21');

-- --------------------------------------------------------

--
-- Table structure for table `telegram_notifications`
--

CREATE TABLE `telegram_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `housekeeping_job_id` int(10) UNSIGNED DEFAULT NULL,
  `chat_id` varchar(255) NOT NULL,
  `message_text` text DEFAULT NULL,
  `sent_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('sent','failed','delivered','read') DEFAULT 'sent',
  `response_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`response_data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `telegram_notifications`
--

INSERT INTO `telegram_notifications` (`id`, `housekeeping_job_id`, `chat_id`, `message_text`, `sent_at`, `status`, `response_data`) VALUES
(1, 11, '123456789', '🧹 งานทำความสะอาดใหม่!\n\n🏠 ห้อง: 103 (overnight)\n👤 แขกเช็คเอาท์: คุณสมหญิง สวยงาม', '2025-09-28 06:42:51', 'sent', NULL),
(2, 1, '987654321', '🧹 งานทำความสะอาดใหม่!\n\n🏠 ห้อง: 102 (short)', '2025-09-28 06:42:51', 'sent', NULL),
(3, 13, '123456789', 'Housekeeping job notification', '2025-09-28 07:56:09', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(4, 13, '987654321', 'Housekeeping job notification', '2025-09-28 07:56:10', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(5, 14, '123456789', 'Housekeeping job notification', '2025-09-28 07:57:59', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(6, 14, '987654321', 'Housekeeping job notification', '2025-09-28 07:58:00', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(7, 15, '123456789', 'Housekeeping job notification', '2025-09-28 07:58:02', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(8, 15, '987654321', 'Housekeeping job notification', '2025-09-28 07:58:04', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(9, 16, '8324712085', 'Housekeeping job notification', '2025-09-28 08:06:01', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":6,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759046760,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e25\\u0e39\\u0e01\\u0e04\\u0e49\\u0e32\\u0e17\\u0e14\\u0e2a\\u0e2d\\u0e1a\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:06\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=16\"}},\"http_code\":200}'),
(10, 16, '123456789', 'Housekeeping job notification', '2025-09-28 08:06:02', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(11, 16, '987654321', 'Housekeeping job notification', '2025-09-28 08:06:03', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(12, 17, '8324712085', 'Housekeeping job notification', '2025-09-28 08:06:04', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":7,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759046763,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: Integration Test Guest\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:06\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=17\"}},\"http_code\":200}'),
(13, 17, '123456789', 'Housekeeping job notification', '2025-09-28 08:06:05', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(14, 17, '987654321', 'Housekeeping job notification', '2025-09-28 08:06:06', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(15, 18, '8324712085', 'Housekeeping job notification', '2025-09-28 08:07:39', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":8,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759046859,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e25\\u0e39\\u0e01\\u0e04\\u0e49\\u0e32\\u0e17\\u0e14\\u0e2a\\u0e2d\\u0e1a\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:07\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=18\"}},\"http_code\":200}'),
(16, 18, '123456789', 'Housekeeping job notification', '2025-09-28 08:07:40', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(17, 18, '987654321', 'Housekeeping job notification', '2025-09-28 08:07:42', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(18, 19, '8324712085', 'Housekeeping job notification', '2025-09-28 08:07:43', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":9,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759046862,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: Integration Test Guest\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:07\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=19\"}},\"http_code\":200}'),
(19, 19, '123456789', 'Housekeeping job notification', '2025-09-28 08:07:44', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(20, 19, '987654321', 'Housekeeping job notification', '2025-09-28 08:07:45', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(21, 20, '8324712085', 'Housekeeping job notification', '2025-09-28 08:08:08', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":10,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759046888,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e25\\u0e39\\u0e01\\u0e04\\u0e49\\u0e32\\u0e17\\u0e14\\u0e2a\\u0e2d\\u0e1a\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:08\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=20\"}},\"http_code\":200}'),
(22, 20, '123456789', 'Housekeeping job notification', '2025-09-28 08:08:09', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(23, 20, '987654321', 'Housekeeping job notification', '2025-09-28 08:08:11', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(24, 21, '8324712085', 'Housekeeping job notification', '2025-09-28 08:08:12', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":11,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759046891,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: Integration Test Guest\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:08\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=21\"}},\"http_code\":200}'),
(25, 21, '123456789', 'Housekeeping job notification', '2025-09-28 08:08:13', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(26, 21, '987654321', 'Housekeeping job notification', '2025-09-28 08:08:14', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(27, 22, '8324712085', 'Housekeeping job notification', '2025-09-28 08:16:48', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":12,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759047408,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e25\\u0e39\\u0e01\\u0e04\\u0e49\\u0e32\\u0e17\\u0e14\\u0e2a\\u0e2d\\u0e1a\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:16\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=22\"}},\"http_code\":200}'),
(28, 22, '123456789', 'Housekeeping job notification', '2025-09-28 08:16:49', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(29, 22, '987654321', 'Housekeeping job notification', '2025-09-28 08:16:51', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(30, 23, '8324712085', 'Housekeeping job notification', '2025-09-28 08:16:52', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":13,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759047411,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: Integration Test Guest\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:16\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e41\\u0e25\\u0e30\\u0e23\\u0e32\\u0e22\\u0e07\\u0e32\\u0e19\\u0e04\\u0e27\\u0e32\\u0e21\\u0e04\\u0e37\\u0e1a\\u0e2b\\u0e19\\u0e49\\u0e32:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=23\"}},\"http_code\":200}'),
(31, 23, '123456789', 'Housekeeping job notification', '2025-09-28 08:16:53', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(32, 23, '987654321', 'Housekeeping job notification', '2025-09-28 08:16:55', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(33, 24, '8324712085', 'Housekeeping job notification', '2025-09-28 08:24:07', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":14,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759047846,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 103 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e2a\\u0e21\\u0e04\\u0e27\\u0e23\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 15:24\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e07\\u0e32\\u0e19:\\n\\ud83d\\uddb1\\ufe0f \\u0e40\\u0e1b\\u0e34\\u0e14\\u0e2b\\u0e19\\u0e49\\u0e32\\u0e07\\u0e32\\u0e19 #24\\n\\n\\ud83d\\udcf1 \\u0e2b\\u0e23\\u0e37\\u0e2d\\u0e04\\u0e31\\u0e14\\u0e25\\u0e2d\\u0e01 URL \\u0e19\\u0e35\\u0e49:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=24\",\"entities\":[{\"offset\":3,\"length\":19,\"type\":\"bold\"},{\"offset\":33,\"length\":3,\"type\":\"bold\"},{\"offset\":164,\"length\":25,\"type\":\"bold\"},{\"offset\":214,\"length\":19,\"type\":\"italic\"},{\"offset\":234,\"length\":52,\"type\":\"code\"}]}},\"http_code\":200}'),
(34, 24, '123456789', 'Housekeeping job notification', '2025-09-28 08:24:08', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(35, 24, '987654321', 'Housekeeping job notification', '2025-09-28 08:24:10', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(36, 25, '8324712085', 'Housekeeping job notification', '2025-09-28 14:01:31', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":15,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759068091,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e25\\u0e39\\u0e01\\u0e04\\u0e49\\u0e32\\u0e17\\u0e14\\u0e2a\\u0e2d\\u0e1a\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 21:01\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e07\\u0e32\\u0e19:\\n\\ud83d\\uddb1\\ufe0f \\u0e40\\u0e1b\\u0e34\\u0e14\\u0e2b\\u0e19\\u0e49\\u0e32\\u0e07\\u0e32\\u0e19 #25\\n\\n\\ud83d\\udcf1 \\u0e2b\\u0e23\\u0e37\\u0e2d\\u0e04\\u0e31\\u0e14\\u0e25\\u0e2d\\u0e01 URL \\u0e19\\u0e35\\u0e49:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=25\",\"entities\":[{\"offset\":3,\"length\":19,\"type\":\"bold\"},{\"offset\":33,\"length\":3,\"type\":\"bold\"},{\"offset\":170,\"length\":25,\"type\":\"bold\"},{\"offset\":220,\"length\":19,\"type\":\"italic\"},{\"offset\":240,\"length\":52,\"type\":\"code\"}]}},\"http_code\":200}'),
(37, 25, '123456789', 'Housekeeping job notification', '2025-09-28 14:01:32', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(38, 25, '987654321', 'Housekeeping job notification', '2025-09-28 14:01:33', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(39, 26, '8324712085', 'Housekeeping job notification', '2025-09-28 14:01:35', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":16,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759068094,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: Integration Test Guest\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 21:01\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e07\\u0e32\\u0e19:\\n\\ud83d\\uddb1\\ufe0f \\u0e40\\u0e1b\\u0e34\\u0e14\\u0e2b\\u0e19\\u0e49\\u0e32\\u0e07\\u0e32\\u0e19 #26\\n\\n\\ud83d\\udcf1 \\u0e2b\\u0e23\\u0e37\\u0e2d\\u0e04\\u0e31\\u0e14\\u0e25\\u0e2d\\u0e01 URL \\u0e19\\u0e35\\u0e49:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=26\",\"entities\":[{\"offset\":3,\"length\":19,\"type\":\"bold\"},{\"offset\":33,\"length\":3,\"type\":\"bold\"},{\"offset\":181,\"length\":25,\"type\":\"bold\"},{\"offset\":231,\"length\":19,\"type\":\"italic\"},{\"offset\":251,\"length\":52,\"type\":\"code\"}]}},\"http_code\":200}'),
(40, 26, '123456789', 'Housekeeping job notification', '2025-09-28 14:01:37', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(41, 26, '987654321', 'Housekeeping job notification', '2025-09-28 14:01:38', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(42, 28, '8324712085', 'Housekeeping job notification', '2025-09-28 14:06:51', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":18,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759068411,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e2a\\u0e21\\u0e2b\\u0e21\\u0e32\\u0e22\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 21:06\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e07\\u0e32\\u0e19:\\n\\ud83d\\uddb1\\ufe0f \\u0e40\\u0e1b\\u0e34\\u0e14\\u0e2b\\u0e19\\u0e49\\u0e32\\u0e07\\u0e32\\u0e19 #28\\n\\n\\ud83d\\udcf1 \\u0e2b\\u0e23\\u0e37\\u0e2d\\u0e04\\u0e31\\u0e14\\u0e25\\u0e2d\\u0e01 URL \\u0e19\\u0e35\\u0e49:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=28\",\"entities\":[{\"offset\":3,\"length\":19,\"type\":\"bold\"},{\"offset\":33,\"length\":3,\"type\":\"bold\"},{\"offset\":165,\"length\":25,\"type\":\"bold\"},{\"offset\":215,\"length\":19,\"type\":\"italic\"},{\"offset\":235,\"length\":52,\"type\":\"code\"}]}},\"http_code\":200}'),
(43, 28, '123456789', 'Housekeeping job notification', '2025-09-28 14:06:52', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(44, 28, '987654321', 'Housekeeping job notification', '2025-09-28 14:06:54', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(45, 29, '8324712085', 'Housekeeping job notification', '2025-09-28 14:17:41', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":19,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759069061,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e43\\u0e08\\u0e14\\u0e35\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 21:17\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e07\\u0e32\\u0e19:\\n\\ud83d\\uddb1\\ufe0f \\u0e40\\u0e1b\\u0e34\\u0e14\\u0e2b\\u0e19\\u0e49\\u0e32\\u0e07\\u0e32\\u0e19 #29\\n\\n\\ud83d\\udcf1 \\u0e2b\\u0e23\\u0e37\\u0e2d\\u0e04\\u0e31\\u0e14\\u0e25\\u0e2d\\u0e01 URL \\u0e19\\u0e35\\u0e49:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=29\",\"entities\":[{\"offset\":3,\"length\":19,\"type\":\"bold\"},{\"offset\":33,\"length\":3,\"type\":\"bold\"},{\"offset\":163,\"length\":25,\"type\":\"bold\"},{\"offset\":213,\"length\":19,\"type\":\"italic\"},{\"offset\":233,\"length\":52,\"type\":\"code\"}]}},\"http_code\":200}'),
(46, 29, '123456789', 'Housekeeping job notification', '2025-09-28 14:17:43', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(47, 29, '987654321', 'Housekeeping job notification', '2025-09-28 14:17:50', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(48, 30, '8324712085', 'Housekeeping job notification', '2025-09-28 15:09:58', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":20,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759072198,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e17\\u0e14\\u0e2a\\u0e2d\\u0e1a\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 28\\/09\\/2025 22:09\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e07\\u0e32\\u0e19:\\n\\ud83d\\uddb1\\ufe0f \\u0e40\\u0e1b\\u0e34\\u0e14\\u0e2b\\u0e19\\u0e49\\u0e32\\u0e07\\u0e32\\u0e19 #30\\n\\n\\ud83d\\udcf1 \\u0e2b\\u0e23\\u0e37\\u0e2d\\u0e04\\u0e31\\u0e14\\u0e25\\u0e2d\\u0e01 URL \\u0e19\\u0e35\\u0e49:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=30\",\"entities\":[{\"offset\":3,\"length\":19,\"type\":\"bold\"},{\"offset\":33,\"length\":3,\"type\":\"bold\"},{\"offset\":164,\"length\":25,\"type\":\"bold\"},{\"offset\":214,\"length\":19,\"type\":\"italic\"},{\"offset\":234,\"length\":52,\"type\":\"code\"}]}},\"http_code\":200}'),
(49, 30, '123456789', 'Housekeeping job notification', '2025-09-28 15:09:59', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(50, 30, '987654321', 'Housekeeping job notification', '2025-09-28 15:10:00', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(51, 31, '8324712085', 'Housekeeping job notification', '2025-09-28 22:53:36', 'sent', '{\"success\":true,\"response\":{\"ok\":true,\"result\":{\"message_id\":21,\"from\":{\"id\":7142045288,\"is_bot\":true,\"first_name\":\"Hotel Housekeeping Bot\",\"username\":\"hotel_housekeeping_bot\"},\"chat\":{\"id\":8324712085,\"first_name\":\"Natee\",\"type\":\"private\"},\"date\":1759100016,\"text\":\"\\ud83e\\uddf9 \\u0e07\\u0e32\\u0e19\\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e43\\u0e2b\\u0e21\\u0e48!\\n\\n\\ud83c\\udfe0 \\u0e2b\\u0e49\\u0e2d\\u0e07: 101 (short)\\n\\ud83d\\udc64 \\u0e41\\u0e02\\u0e01\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: \\u0e2a\\u0e21\\u0e04\\u0e27\\u0e23\\n\\u23f0 \\u0e40\\u0e27\\u0e25\\u0e32\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c: 29\\/09\\/2025 05:53\\n\\ud83d\\udccb \\u0e1b\\u0e23\\u0e30\\u0e40\\u0e20\\u0e17\\u0e07\\u0e32\\u0e19: \\u0e17\\u0e33\\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e30\\u0e2d\\u0e32\\u0e14\\u0e2b\\u0e25\\u0e31\\u0e07\\u0e40\\u0e0a\\u0e47\\u0e04\\u0e40\\u0e2d\\u0e32\\u0e17\\u0e4c\\n\\ud83c\\udfaf \\u0e04\\u0e27\\u0e32\\u0e21\\u0e2a\\u0e33\\u0e04\\u0e31\\u0e0d: \\u0e1b\\u0e01\\u0e15\\u0e34\\n\\n\\ud83d\\udd17 \\u0e04\\u0e25\\u0e34\\u0e01\\u0e40\\u0e1e\\u0e37\\u0e48\\u0e2d\\u0e14\\u0e39\\u0e23\\u0e32\\u0e22\\u0e25\\u0e30\\u0e40\\u0e2d\\u0e35\\u0e22\\u0e14\\u0e07\\u0e32\\u0e19:\\n\\ud83d\\uddb1\\ufe0f \\u0e40\\u0e1b\\u0e34\\u0e14\\u0e2b\\u0e19\\u0e49\\u0e32\\u0e07\\u0e32\\u0e19 #31\\n\\n\\ud83d\\udcf1 \\u0e2b\\u0e23\\u0e37\\u0e2d\\u0e04\\u0e31\\u0e14\\u0e25\\u0e2d\\u0e01 URL \\u0e19\\u0e35\\u0e49:\\nhttp:\\/\\/localhost\\/hotel-app\\/?r=housekeeping.job&id=31\",\"entities\":[{\"offset\":3,\"length\":19,\"type\":\"bold\"},{\"offset\":33,\"length\":3,\"type\":\"bold\"},{\"offset\":164,\"length\":25,\"type\":\"bold\"},{\"offset\":214,\"length\":19,\"type\":\"italic\"},{\"offset\":234,\"length\":52,\"type\":\"code\"}]}},\"http_code\":200}'),
(52, 31, '123456789', 'Housekeeping job notification', '2025-09-28 22:53:37', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(53, 31, '987654321', 'Housekeeping job notification', '2025-09-28 22:53:38', 'failed', '{\"success\":false,\"response\":{\"ok\":false,\"error_code\":400,\"description\":\"Bad Request: chat not found\"},\"http_code\":400}'),
(54, 32, '8324712085', 'Housekeeping job notification', '2025-09-29 05:39:20', 'failed', '{\"success\":false,\"response\":null,\"http_code\":0}'),
(55, 32, '123456789', 'Housekeeping job notification', '2025-09-29 05:39:20', 'failed', '{\"success\":false,\"response\":null,\"http_code\":0}'),
(56, 32, '987654321', 'Housekeeping job notification', '2025-09-29 05:39:20', 'failed', '{\"success\":false,\"response\":null,\"http_code\":0}');

-- --------------------------------------------------------

--
-- Table structure for table `transfer_billing`
--

CREATE TABLE `transfer_billing` (
  `id` int(10) UNSIGNED NOT NULL,
  `transfer_id` int(10) UNSIGNED NOT NULL,
  `original_rate` decimal(10,2) NOT NULL,
  `new_rate` decimal(10,2) NOT NULL,
  `rate_difference` decimal(10,2) NOT NULL,
  `nights_affected` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `tax_amount` decimal(10,2) DEFAULT 0.00,
  `service_charge` decimal(10,2) DEFAULT 0.00,
  `total_adjustment` decimal(10,2) NOT NULL,
  `payment_status` enum('pending','paid','waived','refunded') DEFAULT 'pending',
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_reference` varchar(100) DEFAULT NULL,
  `payment_date` datetime DEFAULT NULL,
  `processed_by` int(10) UNSIGNED DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transfer_notifications`
--

CREATE TABLE `transfer_notifications` (
  `id` int(10) UNSIGNED NOT NULL,
  `transfer_id` int(10) UNSIGNED NOT NULL,
  `notification_type` enum('guest_sms','guest_email','telegram_housekeeping','telegram_reception','system_alert') NOT NULL,
  `recipient` varchar(255) NOT NULL,
  `subject` varchar(255) DEFAULT NULL,
  `message` text NOT NULL,
  `sent_at` datetime DEFAULT NULL,
  `delivery_status` enum('pending','sent','delivered','failed') DEFAULT 'pending',
  `response_data` text DEFAULT NULL,
  `retry_count` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Stand-in structure for view `transfer_summary`
-- (See below for the actual view)
--
CREATE TABLE `transfer_summary` (
`id` int(10) unsigned
,`transfer_date` datetime
,`guest_name` varchar(255)
,`guest_phone` varchar(20)
,`from_room` varchar(10)
,`from_room_type` enum('short','overnight')
,`to_room` varchar(10)
,`to_room_type` enum('short','overnight')
,`transfer_reason` enum('upgrade','downgrade','maintenance','guest_request','overbooking','room_issue','other')
,`total_adjustment` decimal(10,2)
,`status` enum('pending','completed','cancelled')
,`transferred_by_name` varchar(100)
,`payment_status` varchar(8)
);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','reception','housekeeping') NOT NULL DEFAULT 'reception',
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `telegram_chat_id` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `full_name`, `role`, `email`, `phone`, `is_active`, `created_at`, `updated_at`, `telegram_chat_id`) VALUES
(1, 'admin', '$2y$10$VwDKRnjTLzF0HVkpYszM4OgIlnPBL5bLD72S3OlEzAafLk18AulTq', 'ผู้ดูแลระบบ', 'admin', 'admin@hotel.com', '02-123-4567', 1, '2025-09-27 05:30:56', '2025-09-30 15:04:35', NULL),
(2, 'reception', '$2y$10$VwDKRnjTLzF0HVkpYszM4OgIlnPBL5bLD72S3OlEzAafLk18AulTq', 'พนักงานต้อนรับ', 'reception', 'reception@hotel.com', '02-123-4568', 1, '2025-09-27 05:30:56', '2025-09-27 05:42:26', NULL),
(3, 'housekeeping', '$2y$10$VwDKRnjTLzF0HVkpYszM4OgIlnPBL5bLD72S3OlEzAafLk18AulTq', 'พนักงานแม่บ้าน', 'housekeeping', 'housekeeping@hotel.com', '02-123-4569', 1, '2025-09-27 05:30:56', '2025-09-28 08:05:39', '8324712085'),
(4, 'housekeeper1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'นาง สะอาด ใจดี', 'housekeeping', NULL, NULL, 1, '2025-09-28 06:42:51', '2025-09-28 06:42:51', '123456789'),
(5, 'housekeeper2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'นาย ถู ข้น', 'housekeeping', NULL, NULL, 1, '2025-09-28 06:42:51', '2025-09-28 06:42:51', '987654321'),
(6, 'reception1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'นางสาว ต้อนรับ ยิ้มแย้ม', 'reception', NULL, NULL, 1, '2025-09-28 06:42:51', '2025-09-28 06:42:51', NULL);

-- --------------------------------------------------------

--
-- Structure for view `daily_transfer_stats`
--
DROP TABLE IF EXISTS `daily_transfer_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `daily_transfer_stats`  AS SELECT cast(`room_transfers`.`transfer_date` as date) AS `transfer_date`, count(0) AS `total_transfers`, sum(case when `room_transfers`.`transfer_reason` = 'upgrade' then 1 else 0 end) AS `upgrades`, sum(case when `room_transfers`.`transfer_reason` = 'downgrade' then 1 else 0 end) AS `downgrades`, sum(case when `room_transfers`.`transfer_reason` = 'maintenance' then 1 else 0 end) AS `maintenance_moves`, sum(case when `room_transfers`.`transfer_reason` = 'guest_request' then 1 else 0 end) AS `guest_requests`, sum(`room_transfers`.`total_adjustment`) AS `total_revenue_impact`, avg(`room_transfers`.`total_adjustment`) AS `avg_adjustment` FROM `room_transfers` WHERE `room_transfers`.`status` = 'completed' GROUP BY cast(`room_transfers`.`transfer_date` as date) ;

-- --------------------------------------------------------

--
-- Structure for view `housekeeping_performance`
--
DROP TABLE IF EXISTS `housekeeping_performance`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `housekeeping_performance`  AS SELECT `hj`.`id` AS `id`, `hj`.`room_id` AS `room_id`, `r`.`room_number` AS `room_number`, coalesce(`hj`.`task_type`,`hj`.`job_type`) AS `task_type`, `hj`.`priority` AS `priority`, `hj`.`created_at` AS `created_at`, `hj`.`started_at` AS `started_at`, `hj`.`completed_at` AS `completed_at`, CASE WHEN `hj`.`completed_at` is not null AND `hj`.`started_at` is not null THEN timestampdiff(MINUTE,`hj`.`started_at`,`hj`.`completed_at`) ELSE `hj`.`actual_duration` END AS `duration_minutes`, CASE WHEN `hj`.`completed_at` is not null THEN 'completed' WHEN `hj`.`started_at` is not null THEN 'in_progress' ELSE 'pending' END AS `current_status`, `hj`.`assigned_to` AS `assigned_to`, `u`.`full_name` AS `assigned_to_name`, coalesce(`hj`.`telegram_sent`,0) AS `telegram_sent` FROM ((`housekeeping_jobs` `hj` join `rooms` `r` on(`hj`.`room_id` = `r`.`id`)) left join `users` `u` on(`hj`.`assigned_to` = `u`.`id`)) ORDER BY `hj`.`created_at` DESC ;

-- --------------------------------------------------------

--
-- Structure for view `transfer_summary`
--
DROP TABLE IF EXISTS `transfer_summary`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `transfer_summary`  AS SELECT `rt`.`id` AS `id`, `rt`.`transfer_date` AS `transfer_date`, `b`.`guest_name` AS `guest_name`, `b`.`guest_phone` AS `guest_phone`, `r_from`.`room_number` AS `from_room`, `r_from`.`room_type` AS `from_room_type`, `r_to`.`room_number` AS `to_room`, `r_to`.`room_type` AS `to_room_type`, `rt`.`transfer_reason` AS `transfer_reason`, `rt`.`total_adjustment` AS `total_adjustment`, `rt`.`status` AS `status`, `u`.`full_name` AS `transferred_by_name`, coalesce(`tb`.`payment_status`,'none') AS `payment_status` FROM (((((`room_transfers` `rt` join `bookings` `b` on(`rt`.`booking_id` = `b`.`id`)) join `rooms` `r_from` on(`rt`.`from_room_id` = `r_from`.`id`)) join `rooms` `r_to` on(`rt`.`to_room_id` = `r_to`.`id`)) join `users` `u` on(`rt`.`transferred_by` = `u`.`id`)) left join `transfer_billing` `tb` on(`rt`.`id` = `tb`.`transfer_id`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_logs_user` (`user_id`),
  ADD KEY `idx_logs_action` (`action`),
  ADD KEY `idx_logs_table_record` (`table_name`,`record_id`),
  ADD KEY `idx_logs_created` (`created_at`);

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_room_status` (`room_id`,`status`),
  ADD KEY `idx_checkin_date` (`checkin_at`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_booking_code` (`booking_code`);

--
-- Indexes for table `hotel_settings`
--
ALTER TABLE `hotel_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`),
  ADD KEY `idx_setting_key` (`setting_key`);

--
-- Indexes for table `housekeeping_jobs`
--
ALTER TABLE `housekeeping_jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_housekeeping_status_room` (`status`,`room_id`),
  ADD KEY `idx_housekeeping_assigned` (`assigned_to`),
  ADD KEY `idx_housekeeping_priority` (`priority`),
  ADD KEY `idx_housekeeping_type` (`job_type`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_housekeeping_completed_by` (`completed_by`);

--
-- Indexes for table `rates`
--
ALTER TABLE `rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_rates_type` (`rate_type`),
  ADD KEY `idx_rates_active` (`is_active`);

--
-- Indexes for table `receipts`
--
ALTER TABLE `receipts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `receipt_number` (`receipt_number`),
  ADD KEY `idx_receipt_number` (`receipt_number`),
  ADD KEY `idx_booking_code` (`booking_code`),
  ADD KEY `idx_generated_at` (`generated_at`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_rooms_number` (`room_number`),
  ADD KEY `idx_rooms_status` (`status`),
  ADD KEY `idx_rooms_type` (`room_type`),
  ADD KEY `idx_rooms_floor` (`floor`);

--
-- Indexes for table `room_rates`
--
ALTER TABLE `room_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rate_type` (`rate_type`,`is_active`),
  ADD KEY `idx_rate_type` (`rate_type`);

--
-- Indexes for table `room_status_logs`
--
ALTER TABLE `room_status_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `changed_by` (`changed_by`),
  ADD KEY `idx_room_status_logs_room_id` (`room_id`),
  ADD KEY `idx_room_status_logs_changed_at` (`changed_at`);

--
-- Indexes for table `room_transfers`
--
ALTER TABLE `room_transfers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `to_room_id` (`to_room_id`),
  ADD KEY `transferred_by` (`transferred_by`),
  ADD KEY `idx_booking_transfer` (`booking_id`),
  ADD KEY `idx_transfer_date` (`transfer_date`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_rooms` (`from_room_id`,`to_room_id`);

--
-- Indexes for table `telegram_notifications`
--
ALTER TABLE `telegram_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_housekeeping_job` (`housekeeping_job_id`),
  ADD KEY `idx_chat_id` (`chat_id`),
  ADD KEY `idx_sent_at` (`sent_at`);

--
-- Indexes for table `transfer_billing`
--
ALTER TABLE `transfer_billing`
  ADD PRIMARY KEY (`id`),
  ADD KEY `processed_by` (`processed_by`),
  ADD KEY `idx_transfer_billing` (`transfer_id`),
  ADD KEY `idx_payment_status` (`payment_status`),
  ADD KEY `idx_payment_date` (`payment_date`);

--
-- Indexes for table `transfer_notifications`
--
ALTER TABLE `transfer_notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transfer_notifications` (`transfer_id`),
  ADD KEY `idx_delivery_status` (`delivery_status`),
  ADD KEY `idx_notification_type` (`notification_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_users_username` (`username`),
  ADD UNIQUE KEY `uk_users_email` (`email`),
  ADD KEY `idx_users_role` (`role`),
  ADD KEY `idx_users_active` (`is_active`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `hotel_settings`
--
ALTER TABLE `hotel_settings`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `housekeeping_jobs`
--
ALTER TABLE `housekeeping_jobs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `rates`
--
ALTER TABLE `rates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `receipts`
--
ALTER TABLE `receipts`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `rooms`
--
ALTER TABLE `rooms`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `room_rates`
--
ALTER TABLE `room_rates`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `room_status_logs`
--
ALTER TABLE `room_status_logs`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `room_transfers`
--
ALTER TABLE `room_transfers`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `telegram_notifications`
--
ALTER TABLE `telegram_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT for table `transfer_billing`
--
ALTER TABLE `transfer_billing`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transfer_notifications`
--
ALTER TABLE `transfer_notifications`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `housekeeping_jobs`
--
ALTER TABLE `housekeeping_jobs`
  ADD CONSTRAINT `fk_housekeeping_completed_by` FOREIGN KEY (`completed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `housekeeping_jobs_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `housekeeping_jobs_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `housekeeping_jobs_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_status_logs`
--
ALTER TABLE `room_status_logs`
  ADD CONSTRAINT `room_status_logs_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_status_logs_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `room_transfers`
--
ALTER TABLE `room_transfers`
  ADD CONSTRAINT `room_transfers_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_transfers_ibfk_2` FOREIGN KEY (`from_room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_transfers_ibfk_3` FOREIGN KEY (`to_room_id`) REFERENCES `rooms` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `room_transfers_ibfk_4` FOREIGN KEY (`transferred_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transfer_billing`
--
ALTER TABLE `transfer_billing`
  ADD CONSTRAINT `transfer_billing_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `room_transfers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transfer_billing_ibfk_2` FOREIGN KEY (`processed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `transfer_notifications`
--
ALTER TABLE `transfer_notifications`
  ADD CONSTRAINT `transfer_notifications_ibfk_1` FOREIGN KEY (`transfer_id`) REFERENCES `room_transfers` (`id`) ON DELETE CASCADE;
COMMIT;-- Paste your SQL dump here
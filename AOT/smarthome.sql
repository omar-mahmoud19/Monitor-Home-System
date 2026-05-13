-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 12, 2026 at 07:28 PM
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
-- Database: `smarthome`
--

-- --------------------------------------------------------

--
-- Table structure for table `action_log`
--

CREATE TABLE `action_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `action_log`
--

INSERT INTO `action_log` (`id`, `user_id`, `action_type`, `description`, `ip_address`, `user_agent`, `created_at`) VALUES
(1, 1, 'LOGIN', 'Owner logged into dashboard', '127.0.0.1', 'Chrome', '2026-05-12 08:03:45'),
(2, 1, 'DEVICE_CONTROL', 'Turned on Living Room AC', '127.0.0.1', 'Chrome', '2026-05-12 08:03:45'),
(3, 2, 'RULE_TRIGGER', 'Gaming PC alert triggered', '127.0.0.1', 'Chrome', '2026-05-12 08:03:45'),
(4, 3, 'VIEW_ONLY', 'Guest viewed dashboard', '127.0.0.1', 'Chrome', '2026-05-12 08:03:45'),
(5, 1, 'device_create', 'Added device: Solar Cell', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:36:12'),
(6, 1, 'device_update', 'Updated device #4', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:36:39'),
(7, 1, 'device_toggle', 'Washing Machine turned ON', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:36:42'),
(8, 1, 'device_toggle', 'Water Heater turned ON', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:36:49'),
(9, 1, 'device_update', 'Updated device #6', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:36:56'),
(10, 1, 'device_toggle', 'Gaming PC turned OFF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:37:03'),
(11, 1, 'device_toggle', 'Guest Room Fan turned OFF', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:37:08'),
(12, 1, 'device_toggle', 'Solar Cell turned ON', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:37:18'),
(13, 1, 'device_create', 'Added device: Gas stove', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:38:29'),
(14, 1, 'device_toggle', 'Gas stove turned ON', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:38:34'),
(15, 1, 'device_create', 'Added device: Sharp', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 09:39:11'),
(16, 1, 'device_create', 'Added device: Sharp', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 13:36:54'),
(17, 1, 'device_toggle', 'Sharp turned ON', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 13:37:01'),
(18, 1, 'device_delete', 'Removed device: Gas stove', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 13:37:15'),
(19, 1, 'goal_create', 'Created monthly goal for electricity', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', '2026-05-12 13:37:31');

-- --------------------------------------------------------

--
-- Table structure for table `alerts`
--

CREATE TABLE `alerts` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `type` enum('overload','maintenance','budget','goal','info') DEFAULT 'info',
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `title` varchar(150) NOT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `alerts`
--

INSERT INTO `alerts` (`id`, `user_id`, `device_id`, `type`, `priority`, `title`, `message`, `is_read`, `created_at`) VALUES
(1, 1, 1, 'overload', 'high', 'High AC Consumption', 'Living Room AC exceeded the recommended daily limit.', 0, '2026-05-12 07:43:45'),
(2, 1, 6, 'maintenance', 'medium', 'Water Heater Maintenance', 'Water heater efficiency dropped below threshold.', 0, '2026-05-12 05:03:45'),
(3, 2, 8, 'budget', 'medium', 'Gaming PC Power Usage', 'Gaming PC used more energy than expected today.', 0, '2026-05-12 07:03:45'),
(4, 3, 10, 'info', 'low', 'Guest Fan Active', 'Guest room fan has been running for 5 hours.', 1, '2026-05-12 03:03:45');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) DEFAULT 'generic',
  `category` varchar(50) DEFAULT 'other',
  `status` enum('on','off','standby') DEFAULT 'off',
  `location` varchar(100) DEFAULT 'Home',
  `icon` varchar(10) DEFAULT '?',
  `daily_usage_kwh` decimal(8,3) DEFAULT 0.000,
  `monthly_cost` decimal(8,2) DEFAULT 0.00,
  `standby_watts` decimal(8,2) DEFAULT 0.00,
  `efficiency_level` decimal(5,2) DEFAULT 100.00,
  `efficiency_threshold` decimal(5,2) DEFAULT 80.00,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `user_id`, `name`, `type`, `category`, `status`, `location`, `icon`, `daily_usage_kwh`, `monthly_cost`, `standby_watts`, `efficiency_level`, `efficiency_threshold`, `created_at`) VALUES
(1, 1, 'Living Room AC', 'ac', 'climate', 'on', 'Living Room', '❄️', 12.400, 58.20, 6.50, 91.00, 80.00, '2026-05-12 08:03:45'),
(2, 1, 'Smart TV', 'tv', 'entertainment', 'on', 'Living Room', '📺', 2.100, 9.30, 3.00, 88.00, 75.00, '2026-05-12 08:03:45'),
(3, 1, 'Refrigerator', 'fridge', 'kitchen', 'on', 'Kitchen', '🧊', 5.400, 21.50, 8.00, 96.00, 85.00, '2026-05-12 08:03:45'),
(4, 1, 'Washing Machine', 'washer', 'laundry', 'on', 'Laundry', '🧺', 1.900, 8.40, 1.20, 83.00, 75.00, '2026-05-12 08:03:45'),
(5, 1, 'Kitchen Lights', 'lights', 'lighting', 'on', 'Kitchen', '💡', 0.700, 3.00, 0.50, 98.00, 80.00, '2026-05-12 08:03:45'),
(6, 1, 'Water Heater', 'heater', 'bathroom', 'on', 'Bathroom', '🚿', 7.200, 30.00, 4.50, 76.00, 80.00, '2026-05-12 08:03:45'),
(7, 2, 'Bedroom AC', 'ac', 'climate', 'on', 'Bedroom', '❄️', 9.200, 42.00, 5.00, 86.00, 80.00, '2026-05-12 08:03:45'),
(8, 2, 'Gaming PC', 'computer', 'office', 'off', 'Bedroom', '🖥️', 6.800, 29.90, 4.20, 79.00, 75.00, '2026-05-12 08:03:45'),
(9, 2, 'Desk Lamp', 'lights', 'lighting', 'off', 'Bedroom', '💡', 0.200, 1.10, 0.20, 97.00, 80.00, '2026-05-12 08:03:45'),
(10, 3, 'Guest Room Fan', 'fan', 'climate', 'off', 'Guest Room', '🌀', 1.300, 4.50, 0.70, 90.00, 80.00, '2026-05-12 08:03:45'),
(11, 3, 'Phone Charger', 'charger', 'electronics', 'standby', 'Guest Room', '🔌', 0.080, 0.60, 0.10, 99.00, 80.00, '2026-05-12 08:03:45'),
(12, 1, 'Solar Cell', 'solar_panel', 'solar', 'on', 'Roof', '☀️', 0.000, 0.00, 0.00, 100.00, 80.00, '2026-05-12 09:36:12'),
(14, 1, 'Sharp', 'ac', 'cooling', 'on', 'Living Room', '❄️', 0.000, 0.00, 0.00, 100.00, 80.00, '2026-05-12 09:39:11'),
(15, 1, 'Sharp', 'ac', 'cooling', 'off', 'Living Room', '❄️', 0.000, 0.00, 0.00, 100.00, 80.00, '2026-05-12 13:36:54');

-- --------------------------------------------------------

--
-- Table structure for table `device_resources`
--

CREATE TABLE `device_resources` (
  `id` int(11) NOT NULL,
  `device_id` int(11) NOT NULL,
  `resource_type` enum('electricity','water','gas','solar') NOT NULL,
  `consumption_rate` decimal(10,4) DEFAULT 0.0000,
  `unit` varchar(20) DEFAULT 'kWh'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `device_resources`
--

INSERT INTO `device_resources` (`id`, `device_id`, `resource_type`, `consumption_rate`, `unit`) VALUES
(1, 1, 'electricity', 1.8000, 'kWh'),
(2, 2, 'electricity', 0.3000, 'kWh'),
(3, 3, 'electricity', 0.9000, 'kWh'),
(5, 5, 'electricity', 0.1000, 'kWh'),
(7, 7, 'electricity', 1.4000, 'kWh'),
(8, 8, 'electricity', 1.1000, 'kWh'),
(9, 9, 'electricity', 0.0500, 'kWh'),
(10, 10, 'electricity', 0.2000, 'kWh'),
(11, 11, 'electricity', 0.0100, 'kWh'),
(12, 12, 'solar', 0.8000, 'kWh'),
(13, 4, 'electricity', 0.6000, 'kWh'),
(14, 4, 'water', 1.0000, 'L'),
(15, 6, 'electricity', 1.2000, 'kWh'),
(16, 6, 'water', 3.0000, 'L'),
(19, 14, 'electricity', 11.0000, 'kWh'),
(20, 15, 'electricity', 1.5000, 'kWh');

-- --------------------------------------------------------

--
-- Table structure for table `goals`
--

CREATE TABLE `goals` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_type` enum('electricity','water','gas','solar','cost') NOT NULL,
  `period` enum('daily','weekly','monthly','annual') DEFAULT 'monthly',
  `target_value` decimal(10,2) NOT NULL,
  `current_value` decimal(10,2) DEFAULT 0.00,
  `unit` varchar(20) DEFAULT 'kWh',
  `status` enum('active','achieved','failed') DEFAULT 'active',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `goals`
--

INSERT INTO `goals` (`id`, `user_id`, `resource_type`, `period`, `target_value`, `current_value`, `unit`, `status`, `created_at`) VALUES
(1, 1, 'electricity', 'monthly', 450.00, 312.00, 'kWh', 'active', '2026-05-12 08:03:45'),
(2, 1, 'water', 'monthly', 5000.00, 3200.00, 'L', 'active', '2026-05-12 08:03:45'),
(3, 1, 'gas', 'monthly', 120.00, 74.00, 'm³', 'active', '2026-05-12 08:03:45'),
(4, 2, 'electricity', 'monthly', 300.00, 210.00, 'kWh', 'active', '2026-05-12 08:03:45'),
(5, 2, 'cost', 'monthly', 150.00, 92.00, 'USD', 'active', '2026-05-12 08:03:45'),
(6, 3, 'electricity', 'monthly', 80.00, 18.00, 'kWh', 'active', '2026-05-12 08:03:45'),
(7, 1, 'electricity', 'monthly', 450.00, 0.00, '$', 'active', '2026-05-12 13:37:31');

-- --------------------------------------------------------

--
-- Table structure for table `homes`
--

CREATE TABLE `homes` (
  `id` int(11) NOT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `name` varchar(255) NOT NULL,
  `home_code` varchar(10) NOT NULL,
  `home_password` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `homes`
--

INSERT INTO `homes` (`id`, `owner_id`, `name`, `home_code`, `home_password`, `created_at`) VALUES
(1, 1, 'MyHome', 'ED523DD6', '$2y$10$7AvnqHqZfkK7SBMo2TPrOOspTT0F/Ad8V7Y1/JIMOQnP6mur/YaG6', '2026-05-12 07:56:37');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `type` varchar(50) DEFAULT 'custom',
  `period_from` date DEFAULT NULL,
  `period_to` date DEFAULT NULL,
  `data_json` longtext DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reports`
--

INSERT INTO `reports` (`id`, `user_id`, `title`, `type`, `period_from`, `period_to`, `data_json`, `created_at`) VALUES
(1, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"total_cost\":36.42,\"co2_kg\":55.69}]}', '2026-05-12 10:29:02'),
(2, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"total_cost\":36.42,\"co2_kg\":55.69}]}', '2026-05-12 10:31:16'),
(3, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"total_cost\":36.42,\"co2_kg\":55.69}]}', '2026-05-12 10:38:21'),
(4, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 10:44:06'),
(5, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 10:44:07'),
(6, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 10:44:07'),
(7, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 10:44:07'),
(8, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 10:44:07'),
(9, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 10:44:07'),
(10, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 11:49:56'),
(11, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 11:54:01'),
(12, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 11:59:28'),
(13, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":32.46,\"co2_kg\":52.129999999999995}]}', '2026-05-12 13:03:16'),
(14, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":39.39,\"co2_kg\":55.69,\"budget_pct\":81}]}', '2026-05-12 13:07:13'),
(15, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":95.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":39.39,\"co2_kg\":55.69,\"budget_pct\":83}]}', '2026-05-12 13:07:48'),
(16, 1, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":106.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":42.47,\"co2_kg\":58.25,\"budget_pct\":66}]}', '2026-05-12 13:38:00'),
(17, 2, 'Report 2026-05-12', 'monthly', '2025-11-12', '2026-05-12', '{\"monthly\":[{\"label\":\"May 2026\",\"electricity_kwh\":106.4,\"water_liters\":466,\"gas_m3\":16.4,\"solar_kwh\":19.8,\"total_cost\":42.47,\"co2_kg\":58.25,\"budget_pct\":67}]}', '2026-05-12 13:43:10');

-- --------------------------------------------------------

--
-- Table structure for table `resource_usage`
--

CREATE TABLE `resource_usage` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `device_id` int(11) DEFAULT NULL,
  `resource_type` enum('electricity','water','gas','solar') NOT NULL,
  `value` decimal(10,4) DEFAULT 0.0000,
  `unit` varchar(20) DEFAULT 'kWh',
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `resource_usage`
--

INSERT INTO `resource_usage` (`id`, `user_id`, `device_id`, `resource_type`, `value`, `unit`, `recorded_at`) VALUES
(1, 1, 1, 'electricity', 14.2000, 'kWh', '2026-05-12 07:03:45'),
(2, 1, 2, 'electricity', 2.8000, 'kWh', '2026-05-12 06:03:45'),
(3, 1, 3, 'electricity', 5.1000, 'kWh', '2026-05-12 05:03:45'),
(4, 1, 6, 'electricity', 7.3000, 'kWh', '2026-05-12 03:03:45'),
(6, 1, NULL, 'gas', 4.6000, 'm³', '2026-05-12 07:03:45'),
(7, 1, NULL, 'solar', 9.5000, 'kWh', '2026-05-12 07:33:45'),
(8, 2, 7, 'electricity', 10.4000, 'kWh', '2026-05-12 07:03:45'),
(9, 2, 8, 'electricity', 6.7000, 'kWh', '2026-05-12 05:03:45'),
(10, 2, NULL, 'water', 120.0000, 'L', '2026-05-12 06:03:45'),
(11, 2, NULL, 'gas', 2.1000, 'm³', '2026-05-12 04:03:45'),
(12, 3, 10, 'electricity', 1.7000, 'kWh', '2026-05-12 06:03:45'),
(13, 3, NULL, 'water', 45.0000, 'L', '2026-05-12 07:03:45'),
(14, 1, 4, 'electricity', 0.6000, 'kWh', '2026-05-12 09:36:42'),
(15, 1, 4, 'water', 1.0000, 'L', '2026-05-12 09:36:42'),
(16, 1, 12, 'solar', 0.8000, 'kWh', '2026-05-12 09:37:18'),
(17, 1, NULL, 'electricity', 0.1000, 'kWh', '2026-05-12 09:38:34'),
(18, 1, NULL, 'gas', 3.0000, 'm³', '2026-05-12 09:38:34'),
(19, 1, 1, 'electricity', 14.2000, 'kWh', '2026-05-12 09:46:07'),
(20, 1, 2, 'electricity', 2.8000, 'kWh', '2026-05-12 09:46:07'),
(21, 1, 3, 'electricity', 5.1000, 'kWh', '2026-05-12 09:46:07'),
(22, 1, 6, 'electricity', 7.3000, 'kWh', '2026-05-12 09:46:07'),
(23, 1, NULL, 'water', 180.0000, 'L', '2026-05-12 09:46:07'),
(24, 1, NULL, 'gas', 4.6000, 'm³', '2026-05-12 09:46:07'),
(25, 1, NULL, 'solar', 9.5000, 'kWh', '2026-05-12 09:46:07'),
(26, 2, 7, 'electricity', 10.4000, 'kWh', '2026-05-12 09:46:07'),
(27, 2, 8, 'electricity', 6.7000, 'kWh', '2026-05-12 09:46:07'),
(28, 2, NULL, 'water', 120.0000, 'L', '2026-05-12 09:46:07'),
(29, 2, NULL, 'gas', 2.1000, 'm³', '2026-05-12 09:46:07'),
(30, 1, 14, 'electricity', 11.0000, 'kWh', '2026-05-12 13:37:01');

-- --------------------------------------------------------

--
-- Table structure for table `rules`
--

CREATE TABLE `rules` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `active` tinyint(1) DEFAULT 1,
  `trigger_type` varchar(50) DEFAULT NULL,
  `condition` varchar(20) DEFAULT NULL,
  `threshold` varchar(100) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `last_triggered_at` datetime DEFAULT NULL,
  `device_id` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rules`
--

INSERT INTO `rules` (`id`, `user_id`, `name`, `active`, `trigger_type`, `condition`, `threshold`, `action_type`, `is_active`, `last_triggered_at`, `device_id`, `created_at`) VALUES
(1, 1, 'Auto AC Shutdown', 1, 'electricity', '>', '15', 'turn_off', 1, NULL, 1, '2026-05-12 08:03:45'),
(2, 1, 'Night Energy Saver', 1, 'time', '=', '23:00', 'standby_mode', 1, NULL, NULL, '2026-05-12 08:03:45'),
(3, 2, 'Gaming PC Protection', 1, 'electricity', '>', '10', 'send_alert', 1, NULL, 8, '2026-05-12 08:03:45'),
(4, 3, 'Guest Fan Timer', 1, 'time', '>', '5h', 'turn_off', 1, NULL, 10, '2026-05-12 08:03:45'),
(5, 1, 'Turn Off AC if Usage High', 1, 'electricity', '>', '700', 'turn_off', 1, NULL, 2, '2026-05-12 10:37:38'),
(6, 1, 'Water Leak Detection', 1, 'water', '>', '1000', 'send_alert', 1, NULL, 4, '2026-05-12 10:37:38'),
(7, 1, 'Emergency Gas Shutdown', 1, 'gas', '>', '350', 'turn_off', 1, NULL, 7, '2026-05-12 10:37:38');

-- --------------------------------------------------------

--
-- Table structure for table `solar_tracker`
--

CREATE TABLE `solar_tracker` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `generated_kwh` decimal(10,4) DEFAULT 0.0000,
  `exported_kwh` decimal(10,4) DEFAULT 0.0000,
  `recorded_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `solar_tracker`
--

INSERT INTO `solar_tracker` (`id`, `user_id`, `generated_kwh`, `exported_kwh`, `recorded_at`) VALUES
(1, 1, 12.6000, 3.1000, '2026-05-12 07:03:45'),
(2, 1, 10.4000, 2.4000, '2026-05-11 08:03:45'),
(3, 1, 11.8000, 2.9000, '2026-05-10 08:03:45'),
(4, 1, 0.8000, 0.0000, '2026-05-12 09:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `tariffs`
--

CREATE TABLE `tariffs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `resource_type` varchar(20) NOT NULL,
  `rate` decimal(10,6) DEFAULT 0.000000,
  `peak_rate` decimal(10,6) DEFAULT 0.280000,
  `offpeak_rate` decimal(10,6) DEFAULT 0.120000,
  `peak_start` varchar(5) DEFAULT '18:00',
  `peak_end` varchar(5) DEFAULT '22:00',
  `solar_capacity` decimal(8,2) DEFAULT 4.50,
  `currency` varchar(5) DEFAULT 'USD',
  `unit_label` varchar(20) DEFAULT 'kWh',
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tariffs`
--

INSERT INTO `tariffs` (`id`, `user_id`, `resource_type`, `rate`, `peak_rate`, `offpeak_rate`, `peak_start`, `peak_end`, `solar_capacity`, `currency`, `unit_label`, `effective_from`, `effective_to`) VALUES
(1, 1, 'electricity', 0.280000, 0.350000, 0.180000, '18:00', '22:00', 4.50, 'USD', 'kWh', '2026-01-01', NULL),
(2, 1, 'water', 0.005000, 0.005000, 0.005000, '18:00', '22:00', 4.50, 'USD', 'L', '2026-01-01', NULL),
(3, 1, 'gas', 0.450000, 0.450000, 0.450000, '18:00', '22:00', 4.50, 'USD', 'm³', '2026-01-01', NULL),
(4, 2, 'electricity', 0.280000, 0.350000, 0.180000, '18:00', '22:00', 4.50, 'USD', 'kWh', '2026-01-01', NULL),
(5, 3, 'electricity', 0.280000, 0.350000, 0.180000, '18:00', '22:00', 4.50, 'USD', 'kWh', '2026-01-01', NULL),
(9, 2, 'water', 0.005000, 0.005000, 0.005000, '18:00', '22:00', 4.50, 'USD', 'L', '2026-01-01', NULL),
(10, 2, 'gas', 0.450000, 0.450000, 0.450000, '18:00', '22:00', 4.50, 'USD', 'm³', '2026-01-01', NULL),
(11, 3, 'water', 0.005000, 0.005000, 0.005000, '18:00', '22:00', 4.50, 'USD', 'L', '2026-01-01', NULL),
(12, 3, 'gas', 0.450000, 0.450000, 0.450000, '18:00', '22:00', 4.50, 'USD', 'm³', '2026-01-01', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstName` varchar(50) NOT NULL,
  `lastName` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `homeName` varchar(100) DEFAULT NULL,
  `avatar` varchar(5) DEFAULT 'AO',
  `role` enum('owner','tenant','guest') DEFAULT 'owner',
  `home_id` int(11) DEFAULT NULL,
  `currency` varchar(5) DEFAULT 'USD',
  `units` enum('metric','imperial') DEFAULT 'metric',
  `address` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `vacation_mode` tinyint(1) DEFAULT 0,
  `sim_speed` int(11) DEFAULT 5,
  `retention` int(11) DEFAULT 30,
  `toggle_benchmarking` tinyint(1) DEFAULT 1,
  `toggle_weather` tinyint(1) DEFAULT 1,
  `toggle_audit` tinyint(1) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updatedAt` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstName`, `lastName`, `email`, `password`, `homeName`, `avatar`, `role`, `home_id`, `currency`, `units`, `address`, `status`, `vacation_mode`, `sim_speed`, `retention`, `toggle_benchmarking`, `toggle_weather`, `toggle_audit`, `created_at`, `updatedAt`) VALUES
(1, 'Home', 'Owner', 'owner@gmail.com', '$2y$10$1zAV.bnWU1TZNIr9/UAZIeGKUYw8YTKmsuRfbJOsIxlcM0qY.4Q1S', 'MyHome', 'HO', 'owner', 1, 'USD', 'metric', NULL, 'active', 1, 5, 30, 1, 1, 1, '2026-05-12 07:56:37', '2026-05-12 10:32:49'),
(2, 'First', 'Tenant', 'tenant@gmail.com', '$2y$10$kuP/4iuutBytdoPjbU1VDeXHs2s1aCFiKclIYX7mRoAOjd.U.K2MG', 'MyHome', 'FT', 'tenant', 1, 'USD', 'metric', NULL, 'active', 0, 5, 30, 1, 1, 1, '2026-05-12 07:58:39', '2026-05-12 07:58:39'),
(3, 'First', 'Guest', 'guest@gmail.com', '$2y$10$ilHHXJBeSu1T0zCdYHLVx.xlQwkUj.PCCpjCNOPJW02E/U4gjn80W', 'MyHome', 'FG', 'guest', 1, 'USD', 'metric', NULL, 'active', 0, 5, 30, 1, 1, 1, '2026-05-12 07:59:22', '2026-05-12 07:59:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `action_log`
--
ALTER TABLE `action_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `alerts`
--
ALTER TABLE `alerts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `device_resources`
--
ALTER TABLE `device_resources`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`);

--
-- Indexes for table `goals`
--
ALTER TABLE `goals`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `homes`
--
ALTER TABLE `homes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `home_code` (`home_code`),
  ADD KEY `homes_ibfk_1` (`owner_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `resource_usage`
--
ALTER TABLE `resource_usage`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `idx_usage_user_date` (`user_id`,`recorded_at`),
  ADD KEY `idx_usage_type` (`resource_type`);

--
-- Indexes for table `rules`
--
ALTER TABLE `rules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `device_id` (`device_id`),
  ADD KEY `idx_rules_user` (`user_id`),
  ADD KEY `idx_rules_active` (`is_active`);

--
-- Indexes for table `solar_tracker`
--
ALTER TABLE `solar_tracker`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_solar_user_date` (`user_id`,`recorded_at`);

--
-- Indexes for table `tariffs`
--
ALTER TABLE `tariffs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_tariffs_user_type` (`user_id`,`resource_type`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `home_id` (`home_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `action_log`
--
ALTER TABLE `action_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `alerts`
--
ALTER TABLE `alerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `device_resources`
--
ALTER TABLE `device_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `goals`
--
ALTER TABLE `goals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `homes`
--
ALTER TABLE `homes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `resource_usage`
--
ALTER TABLE `resource_usage`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `rules`
--
ALTER TABLE `rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `solar_tracker`
--
ALTER TABLE `solar_tracker`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tariffs`
--
ALTER TABLE `tariffs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `action_log`
--
ALTER TABLE `action_log`
  ADD CONSTRAINT `action_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `alerts`
--
ALTER TABLE `alerts`
  ADD CONSTRAINT `alerts_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `alerts_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `device_resources`
--
ALTER TABLE `device_resources`
  ADD CONSTRAINT `device_resources_ibfk_1` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `goals`
--
ALTER TABLE `goals`
  ADD CONSTRAINT `goals_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `homes`
--
ALTER TABLE `homes`
  ADD CONSTRAINT `homes_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `resource_usage`
--
ALTER TABLE `resource_usage`
  ADD CONSTRAINT `resource_usage_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `resource_usage_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `rules`
--
ALTER TABLE `rules`
  ADD CONSTRAINT `rules_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rules_ibfk_2` FOREIGN KEY (`device_id`) REFERENCES `devices` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `solar_tracker`
--
ALTER TABLE `solar_tracker`
  ADD CONSTRAINT `solar_tracker_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tariffs`
--
ALTER TABLE `tariffs`
  ADD CONSTRAINT `tariffs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`home_id`) REFERENCES `homes` (`id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
<<<<<<< HEAD
-- Generation Time: May 08, 2025 at 05:54 PM
=======
-- Generation Time: May 04, 2025 at 06:23 AM
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df
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
-- Database: `my_database`
--

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `content`, `created_at`) VALUES
(1, 'ICT CONGRESS', 'BUY NOW', '2025-03-02 12:19:27'),
(2, 'ako', 'asda', '2025-03-02 12:24:40'),
(3, 'ict ', 'buy now', '2025-03-11 11:52:50');

-- --------------------------------------------------------

--
-- Table structure for table `computer_control`
--

CREATE TABLE `computer_control` (
  `id` int(11) NOT NULL,
  `pc_number` varchar(20) NOT NULL,
  `lab_name` varchar(50) NOT NULL,
  `status` enum('available','reserved','in_use','offline','maintenance') NOT NULL DEFAULT 'available',
  `reservation_id` int(11) DEFAULT NULL,
<<<<<<< HEAD
  `last_update` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `computer_control`
--

INSERT INTO `computer_control` (`id`, `pc_number`, `lab_name`, `status`, `reservation_id`, `last_update`) VALUES
(1, 'PC-1', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(2, 'PC-2', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(3, 'PC-3', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(4, 'PC-4', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(5, 'PC-5', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(6, 'PC-6', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(7, 'PC-7', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(8, 'PC-8', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(9, 'PC-9', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(10, 'PC-10', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(11, 'PC-11', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(12, 'PC-12', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(13, 'PC-13', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(14, 'PC-14', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(15, 'PC-15', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(16, 'PC-16', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(17, 'PC-17', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(18, 'PC-18', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(19, 'PC-19', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(20, 'PC-20', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(21, 'PC-21', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(22, 'PC-22', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(23, 'PC-23', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(24, 'PC-24', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(25, 'PC-25', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(26, 'PC-26', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(27, 'PC-27', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(28, 'PC-28', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(29, 'PC-29', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(30, 'PC-30', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(31, 'PC-31', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(32, 'PC-32', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(33, 'PC-33', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(34, 'PC-34', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(35, 'PC-35', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(36, 'PC-36', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(37, 'PC-37', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(38, 'PC-38', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(39, 'PC-39', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(40, 'PC-40', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(41, 'PC-41', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(42, 'PC-42', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(43, 'PC-43', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(44, 'PC-44', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(45, 'PC-45', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(46, 'PC-46', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(47, 'PC-47', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(48, 'PC-48', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(49, 'PC-49', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57'),
(50, 'PC-50', 'Lab 524', 'available', NULL, '2025-05-08 23:52:57');

=======
  `last_update` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  CONSTRAINT `computer_control_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df
-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `sit_in_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL,
  `comments` text NOT NULL,
  `submitted_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `feedback`
--

INSERT INTO `feedback` (`id`, `user_id`, `sit_in_id`, `rating`, `comments`, `submitted_at`) VALUES
(1, '1010', 2, 0, 'aaaa', '2025-03-10 08:41:08'),
(2, '1010', 8, 0, 'opaw', '2025-03-11 20:34:36'),
(3, '1010', 17, 0, 'thanks', '2025-03-17 21:10:14'),
(4, '1010', 22, 0, 'okay kaayo', '2025-04-09 20:35:19'),
(5, '2020', 23, 0, 'thannnk you', '2025-04-09 20:39:09');

-- --------------------------------------------------------

--
-- Table structure for table `lab_resources`
--

CREATE TABLE `lab_resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_resources`
--

INSERT INTO `lab_resources` (`id`, `title`, `description`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `is_active`, `uploaded_at`) VALUES
(6, 'capstone', 'killer', 'CAPSTONE PROJECT 1 MANUSCRIPT OUTLINE.docx.pdf', 'Uploads/lab_resources/68047b07730f6_CAPSTONE PROJECT 1 MANUSCRIPT OUTLINE.docx.pdf', 'pdf', 64605, '3030', 0, '2025-04-20 04:41:43'),
(7, 'CAPSTONE', 'ASD', 'CAPSTONE PROJECT 1 MANUSCRIPT OUTLINE.docx.pdf', 'Uploads/lab_resources/680e417a27f59_CAPSTONE PROJECT 1 MANUSCRIPT OUTLINE.docx.pdf', 'pdf', 64605, '3030', 1, '2025-04-27 14:38:50');

-- --------------------------------------------------------

--
-- Table structure for table `lab_schedules`
--

CREATE TABLE `lab_schedules` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `file_type` varchar(50) NOT NULL,
  `file_size` int(11) NOT NULL,
  `uploaded_by` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `schedule_date` date DEFAULT NULL,
  `schedule_end_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lab_schedules`
--

INSERT INTO `lab_schedules` (`id`, `title`, `description`, `file_name`, `file_path`, `file_type`, `file_size`, `uploaded_by`, `is_active`, `uploaded_at`, `schedule_date`, `schedule_end_date`) VALUES
(1, 'techno', 'ako', 'PURISIMA-CREATION.pdf', 'Uploads/lab_schedules/680ce47d7e859_PURISIMA-CREATION.pdf', 'pdf', 908924, '3030', 1, '2025-04-26 13:49:49', '2025-04-26', '2025-04-26');

-- --------------------------------------------------------

--
<<<<<<< HEAD
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `type` enum('reservation','acceptance','decline') NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notifications`
--

INSERT INTO `notifications` (`id`, `user_id`, `message`, `type`, `reference_id`, `is_read`, `created_at`) VALUES
(1, '2', 'Your reservation for Lab 524 - PC-2 has been accepted', 'acceptance', NULL, 0, '2025-05-07 22:14:02'),
(2, '1010', 'Your reservation for Lab 528 - PC 00 has been accepted', 'acceptance', NULL, 0, '2025-05-07 23:28:21'),
(3, '1', 'Your reservation for Lab 524 - PC 00 has been accepted', 'acceptance', NULL, 0, '2025-05-07 23:40:40'),
(4, '3', 'Your reservation for Lab 524 - PC PC-00 has been accepted', 'acceptance', NULL, 0, '2025-05-07 23:54:09'),
(5, 'admin', 'New reservation request from johnlouie nacaytuna purisima\nLab: 524\nPC: PC-4\nPurpose: C#\nDate: May 8, 2025\nTime: 10:12 AM', 'reservation', 24, 0, '2025-05-08 10:12:07'),
(6, 'admin', 'New reservation request from ako budoy killer\nLab: Lab 524\nPC: PC-17\nPurpose: C#\nDate: May 8, 2025\nTime: 10:26 AM', 'reservation', 25, 0, '2025-05-08 10:26:14'),
(7, 'admin', 'New reservation request from ako budoy killer\nLab: Lab 526\nPC: PC-20\nPurpose: C#\nDate: May 8, 2025\nTime: 10:34 AM', 'reservation', 26, 0, '2025-05-08 10:34:03'),
(8, 'admin', 'New reservation request from durano doblas doblas\nLab: Lab 542\nPC: PC-50\nPurpose: C Programming\nDate: May 8, 2025\nTime: 10:39 AM', 'reservation', 27, 1, '2025-05-08 10:39:38'),
(15, 'admin', 'New reservation request from ako budoy killer\nLab: Lab 524\nPC: PC-2\nPurpose: C#\nDate: May 8, 2025\nTime: 11:18 PM', 'reservation', 28, 1, '2025-05-08 23:18:32');

-- --------------------------------------------------------

--
-- Table structure for table `pc_availability`
--

CREATE TABLE `pc_availability` (
  `id` int(11) NOT NULL,
  `lab_name` varchar(50) NOT NULL,
  `pc_number` varchar(20) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `reservation_id` int(11) DEFAULT NULL,
  `reserved_from` datetime DEFAULT NULL,
  `reserved_to` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
=======
-- Table structure for table `pc_availability`
--

CREATE TABLE `pc_availability` (
  `id` int(11) NOT NULL,
  `lab_name` varchar(50) NOT NULL,
  `pc_number` varchar(20) NOT NULL,
  `is_available` tinyint(1) DEFAULT 1,
  `reservation_id` int(11) DEFAULT NULL,
  `reserved_from` datetime DEFAULT NULL,
  `reserved_to` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df
-- Dumping data for table `pc_availability`
--

INSERT INTO `pc_availability` (`id`, `lab_name`, `pc_number`, `is_available`, `reservation_id`, `reserved_from`, `reserved_to`) VALUES
<<<<<<< HEAD
(1, 'Lab 524', 'PC-1', 1, NULL, NULL, NULL),
(2, 'Lab 524', 'PC-10', 1, NULL, NULL, NULL),
(3, 'Lab 524', 'PC-11', 1, NULL, NULL, NULL),
(4, 'Lab 524', 'PC-12', 1, NULL, NULL, NULL),
(5, 'Lab 524', 'PC-13', 1, NULL, NULL, NULL),
(6, 'Lab 524', 'PC-14', 1, NULL, NULL, NULL),
(7, 'Lab 524', 'PC-15', 1, NULL, NULL, NULL),
(8, 'Lab 524', 'PC-16', 1, NULL, NULL, NULL),
(9, 'Lab 524', 'PC-17', 1, NULL, NULL, NULL),
(10, 'Lab 524', 'PC-18', 1, NULL, NULL, NULL),
(11, 'Lab 524', 'PC-19', 1, NULL, NULL, NULL),
(12, 'Lab 524', 'PC-2', 1, NULL, NULL, NULL),
(13, 'Lab 524', 'PC-20', 1, NULL, NULL, NULL),
(14, 'Lab 524', 'PC-21', 1, NULL, NULL, NULL),
(15, 'Lab 524', 'PC-22', 1, NULL, NULL, NULL),
(16, 'Lab 524', 'PC-23', 1, NULL, NULL, NULL),
(17, 'Lab 524', 'PC-24', 1, NULL, NULL, NULL),
(18, 'Lab 524', 'PC-25', 1, NULL, NULL, NULL),
(19, 'Lab 524', 'PC-26', 1, NULL, NULL, NULL),
(20, 'Lab 524', 'PC-27', 1, NULL, NULL, NULL),
(21, 'Lab 524', 'PC-28', 1, NULL, NULL, NULL),
(22, 'Lab 524', 'PC-29', 1, NULL, NULL, NULL),
(23, 'Lab 524', 'PC-3', 1, NULL, NULL, NULL),
(24, 'Lab 524', 'PC-30', 1, NULL, NULL, NULL),
(25, 'Lab 524', 'PC-31', 1, NULL, NULL, NULL),
(26, 'Lab 524', 'PC-32', 1, NULL, NULL, NULL),
(27, 'Lab 524', 'PC-33', 1, NULL, NULL, NULL),
(28, 'Lab 524', 'PC-34', 1, NULL, NULL, NULL),
(29, 'Lab 524', 'PC-35', 1, NULL, NULL, NULL),
(30, 'Lab 524', 'PC-36', 1, NULL, NULL, NULL),
(31, 'Lab 524', 'PC-37', 1, NULL, NULL, NULL),
(32, 'Lab 524', 'PC-38', 1, NULL, NULL, NULL),
(33, 'Lab 524', 'PC-39', 1, NULL, NULL, NULL),
(34, 'Lab 524', 'PC-4', 1, NULL, NULL, NULL),
(35, 'Lab 524', 'PC-40', 1, NULL, NULL, NULL),
(36, 'Lab 524', 'PC-41', 1, NULL, NULL, NULL),
(37, 'Lab 524', 'PC-42', 1, NULL, NULL, NULL),
(38, 'Lab 524', 'PC-43', 1, NULL, NULL, NULL),
(39, 'Lab 524', 'PC-44', 1, NULL, NULL, NULL),
(40, 'Lab 524', 'PC-45', 1, NULL, NULL, NULL),
(41, 'Lab 524', 'PC-46', 1, NULL, NULL, NULL),
(42, 'Lab 524', 'PC-47', 1, NULL, NULL, NULL),
(43, 'Lab 524', 'PC-48', 1, NULL, NULL, NULL),
(44, 'Lab 524', 'PC-49', 1, NULL, NULL, NULL),
(45, 'Lab 524', 'PC-5', 1, NULL, NULL, NULL),
(46, 'Lab 524', 'PC-50', 1, NULL, NULL, NULL),
(47, 'Lab 524', 'PC-6', 1, NULL, NULL, NULL),
(48, 'Lab 524', 'PC-7', 1, NULL, NULL, NULL),
(49, 'Lab 524', 'PC-8', 1, NULL, NULL, NULL),
(50, 'Lab 524', 'PC-9', 1, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pc_management`
--

CREATE TABLE `pc_management` (
  `id` int(11) NOT NULL,
  `lab_name` varchar(50) NOT NULL,
  `pc_number` varchar(10) NOT NULL,
  `status` enum('available','reserved','in_use','offline','maintenance') DEFAULT 'available',
  `reservation_id` int(11) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `last_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pc_management`
--

INSERT INTO `pc_management` (`id`, `lab_name`, `pc_number`, `status`, `reservation_id`, `student_id`, `last_update`) VALUES
(1, 'Lab 524', 'PC-01', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(2, 'Lab 524', 'PC-02', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(3, 'Lab 524', 'PC-03', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(4, 'Lab 524', 'PC-04', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(5, 'Lab 524', 'PC-05', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(6, 'Lab 524', 'PC-06', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(7, 'Lab 524', 'PC-07', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(8, 'Lab 524', 'PC-08', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(9, 'Lab 524', 'PC-09', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(10, 'Lab 524', 'PC-10', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(11, 'Lab 524', 'PC-11', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(12, 'Lab 524', 'PC-12', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(13, 'Lab 524', 'PC-13', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(14, 'Lab 524', 'PC-14', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(15, 'Lab 524', 'PC-15', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(16, 'Lab 524', 'PC-16', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(17, 'Lab 524', 'PC-17', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(18, 'Lab 524', 'PC-18', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(19, 'Lab 524', 'PC-19', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(20, 'Lab 524', 'PC-20', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(21, 'Lab 524', 'PC-21', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(22, 'Lab 524', 'PC-22', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(23, 'Lab 524', 'PC-23', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(24, 'Lab 524', 'PC-24', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(25, 'Lab 524', 'PC-25', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(26, 'Lab 524', 'PC-26', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(27, 'Lab 524', 'PC-27', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(28, 'Lab 524', 'PC-28', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(29, 'Lab 524', 'PC-29', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(30, 'Lab 524', 'PC-30', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(31, 'Lab 524', 'PC-31', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(32, 'Lab 524', 'PC-32', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(33, 'Lab 524', 'PC-33', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(34, 'Lab 524', 'PC-34', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(35, 'Lab 524', 'PC-35', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(36, 'Lab 524', 'PC-36', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(37, 'Lab 524', 'PC-37', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(38, 'Lab 524', 'PC-38', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(39, 'Lab 524', 'PC-39', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(40, 'Lab 524', 'PC-40', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(41, 'Lab 524', 'PC-41', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(42, 'Lab 524', 'PC-42', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(43, 'Lab 524', 'PC-43', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(44, 'Lab 524', 'PC-44', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(45, 'Lab 524', 'PC-45', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(46, 'Lab 524', 'PC-46', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(47, 'Lab 524', 'PC-47', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(48, 'Lab 524', 'PC-48', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(49, 'Lab 524', 'PC-49', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(50, 'Lab 524', 'PC-50', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(51, 'Lab 526', 'PC-01', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(52, 'Lab 526', 'PC-02', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(53, 'Lab 526', 'PC-03', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(54, 'Lab 526', 'PC-04', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(55, 'Lab 526', 'PC-05', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(56, 'Lab 526', 'PC-06', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(57, 'Lab 526', 'PC-07', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(58, 'Lab 526', 'PC-08', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(59, 'Lab 526', 'PC-09', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(60, 'Lab 526', 'PC-10', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(61, 'Lab 526', 'PC-11', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(62, 'Lab 526', 'PC-12', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(63, 'Lab 526', 'PC-13', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(64, 'Lab 526', 'PC-14', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(65, 'Lab 526', 'PC-15', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(66, 'Lab 526', 'PC-16', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(67, 'Lab 526', 'PC-17', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(68, 'Lab 526', 'PC-18', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(69, 'Lab 526', 'PC-19', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(70, 'Lab 526', 'PC-20', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(71, 'Lab 526', 'PC-21', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(72, 'Lab 526', 'PC-22', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(73, 'Lab 526', 'PC-23', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(74, 'Lab 526', 'PC-24', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(75, 'Lab 526', 'PC-25', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(76, 'Lab 526', 'PC-26', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(77, 'Lab 526', 'PC-27', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(78, 'Lab 526', 'PC-28', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(79, 'Lab 526', 'PC-29', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(80, 'Lab 526', 'PC-30', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(81, 'Lab 526', 'PC-31', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(82, 'Lab 526', 'PC-32', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(83, 'Lab 526', 'PC-33', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(84, 'Lab 526', 'PC-34', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(85, 'Lab 526', 'PC-35', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(86, 'Lab 526', 'PC-36', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(87, 'Lab 526', 'PC-37', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(88, 'Lab 526', 'PC-38', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(89, 'Lab 526', 'PC-39', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(90, 'Lab 526', 'PC-40', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(91, 'Lab 526', 'PC-41', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(92, 'Lab 526', 'PC-42', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(93, 'Lab 526', 'PC-43', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(94, 'Lab 526', 'PC-44', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(95, 'Lab 526', 'PC-45', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(96, 'Lab 526', 'PC-46', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(97, 'Lab 526', 'PC-47', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(98, 'Lab 526', 'PC-48', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(99, 'Lab 526', 'PC-49', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(100, 'Lab 526', 'PC-50', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(101, 'Lab 542', 'PC-01', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(102, 'Lab 542', 'PC-02', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(103, 'Lab 542', 'PC-03', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(104, 'Lab 542', 'PC-04', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(105, 'Lab 542', 'PC-05', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(106, 'Lab 542', 'PC-06', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(107, 'Lab 542', 'PC-07', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(108, 'Lab 542', 'PC-08', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(109, 'Lab 542', 'PC-09', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(110, 'Lab 542', 'PC-10', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(111, 'Lab 542', 'PC-11', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(112, 'Lab 542', 'PC-12', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(113, 'Lab 542', 'PC-13', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(114, 'Lab 542', 'PC-14', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(115, 'Lab 542', 'PC-15', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(116, 'Lab 542', 'PC-16', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(117, 'Lab 542', 'PC-17', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(118, 'Lab 542', 'PC-18', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(119, 'Lab 542', 'PC-19', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(120, 'Lab 542', 'PC-20', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(121, 'Lab 542', 'PC-21', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(122, 'Lab 542', 'PC-22', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(123, 'Lab 542', 'PC-23', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(124, 'Lab 542', 'PC-24', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(125, 'Lab 542', 'PC-25', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(126, 'Lab 542', 'PC-26', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(127, 'Lab 542', 'PC-27', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(128, 'Lab 542', 'PC-28', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(129, 'Lab 542', 'PC-29', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(130, 'Lab 542', 'PC-30', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(131, 'Lab 542', 'PC-31', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(132, 'Lab 542', 'PC-32', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(133, 'Lab 542', 'PC-33', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(134, 'Lab 542', 'PC-34', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(135, 'Lab 542', 'PC-35', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(136, 'Lab 542', 'PC-36', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(137, 'Lab 542', 'PC-37', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(138, 'Lab 542', 'PC-38', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(139, 'Lab 542', 'PC-39', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(140, 'Lab 542', 'PC-40', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(141, 'Lab 542', 'PC-41', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(142, 'Lab 542', 'PC-42', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(143, 'Lab 542', 'PC-43', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(144, 'Lab 542', 'PC-44', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(145, 'Lab 542', 'PC-45', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(146, 'Lab 542', 'PC-46', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(147, 'Lab 542', 'PC-47', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(148, 'Lab 542', 'PC-48', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(149, 'Lab 542', 'PC-49', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(150, 'Lab 542', 'PC-50', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(151, 'Lab 544', 'PC-01', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(152, 'Lab 544', 'PC-02', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(153, 'Lab 544', 'PC-03', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(154, 'Lab 544', 'PC-04', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(155, 'Lab 544', 'PC-05', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(156, 'Lab 544', 'PC-06', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(157, 'Lab 544', 'PC-07', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(158, 'Lab 544', 'PC-08', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(159, 'Lab 544', 'PC-09', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(160, 'Lab 544', 'PC-10', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(161, 'Lab 544', 'PC-11', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(162, 'Lab 544', 'PC-12', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(163, 'Lab 544', 'PC-13', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(164, 'Lab 544', 'PC-14', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(165, 'Lab 544', 'PC-15', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(166, 'Lab 544', 'PC-16', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(167, 'Lab 544', 'PC-17', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(168, 'Lab 544', 'PC-18', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(169, 'Lab 544', 'PC-19', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(170, 'Lab 544', 'PC-20', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(171, 'Lab 544', 'PC-21', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(172, 'Lab 544', 'PC-22', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(173, 'Lab 544', 'PC-23', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(174, 'Lab 544', 'PC-24', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(175, 'Lab 544', 'PC-25', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(176, 'Lab 544', 'PC-26', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(177, 'Lab 544', 'PC-27', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(178, 'Lab 544', 'PC-28', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(179, 'Lab 544', 'PC-29', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(180, 'Lab 544', 'PC-30', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(181, 'Lab 544', 'PC-31', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(182, 'Lab 544', 'PC-32', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(183, 'Lab 544', 'PC-33', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(184, 'Lab 544', 'PC-34', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(185, 'Lab 544', 'PC-35', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(186, 'Lab 544', 'PC-36', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(187, 'Lab 544', 'PC-37', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(188, 'Lab 544', 'PC-38', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(189, 'Lab 544', 'PC-39', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(190, 'Lab 544', 'PC-40', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(191, 'Lab 544', 'PC-41', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(192, 'Lab 544', 'PC-42', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(193, 'Lab 544', 'PC-43', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(194, 'Lab 544', 'PC-44', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(195, 'Lab 544', 'PC-45', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(196, 'Lab 544', 'PC-46', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(197, 'Lab 544', 'PC-47', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(198, 'Lab 544', 'PC-48', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(199, 'Lab 544', 'PC-49', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(200, 'Lab 544', 'PC-50', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(201, 'Lab 517', 'PC-01', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(202, 'Lab 517', 'PC-02', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(203, 'Lab 517', 'PC-03', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(204, 'Lab 517', 'PC-04', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(205, 'Lab 517', 'PC-05', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(206, 'Lab 517', 'PC-06', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(207, 'Lab 517', 'PC-07', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(208, 'Lab 517', 'PC-08', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(209, 'Lab 517', 'PC-09', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(210, 'Lab 517', 'PC-10', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(211, 'Lab 517', 'PC-11', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(212, 'Lab 517', 'PC-12', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(213, 'Lab 517', 'PC-13', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(214, 'Lab 517', 'PC-14', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(215, 'Lab 517', 'PC-15', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(216, 'Lab 517', 'PC-16', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(217, 'Lab 517', 'PC-17', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(218, 'Lab 517', 'PC-18', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(219, 'Lab 517', 'PC-19', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(220, 'Lab 517', 'PC-20', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(221, 'Lab 517', 'PC-21', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(222, 'Lab 517', 'PC-22', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(223, 'Lab 517', 'PC-23', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(224, 'Lab 517', 'PC-24', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(225, 'Lab 517', 'PC-25', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(226, 'Lab 517', 'PC-26', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(227, 'Lab 517', 'PC-27', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(228, 'Lab 517', 'PC-28', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(229, 'Lab 517', 'PC-29', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(230, 'Lab 517', 'PC-30', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(231, 'Lab 517', 'PC-31', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(232, 'Lab 517', 'PC-32', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(233, 'Lab 517', 'PC-33', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(234, 'Lab 517', 'PC-34', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(235, 'Lab 517', 'PC-35', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(236, 'Lab 517', 'PC-36', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(237, 'Lab 517', 'PC-37', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(238, 'Lab 517', 'PC-38', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(239, 'Lab 517', 'PC-39', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(240, 'Lab 517', 'PC-40', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(241, 'Lab 517', 'PC-41', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(242, 'Lab 517', 'PC-42', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(243, 'Lab 517', 'PC-43', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(244, 'Lab 517', 'PC-44', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(245, 'Lab 517', 'PC-45', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(246, 'Lab 517', 'PC-46', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(247, 'Lab 517', 'PC-47', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(248, 'Lab 517', 'PC-48', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(249, 'Lab 517', 'PC-49', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(250, 'Lab 517', 'PC-50', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(251, 'Lab 528', 'PC-01', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(252, 'Lab 528', 'PC-02', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(253, 'Lab 528', 'PC-03', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(254, 'Lab 528', 'PC-04', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(255, 'Lab 528', 'PC-05', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(256, 'Lab 528', 'PC-06', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(257, 'Lab 528', 'PC-07', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(258, 'Lab 528', 'PC-08', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(259, 'Lab 528', 'PC-09', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(260, 'Lab 528', 'PC-10', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(261, 'Lab 528', 'PC-11', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(262, 'Lab 528', 'PC-12', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(263, 'Lab 528', 'PC-13', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(264, 'Lab 528', 'PC-14', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(265, 'Lab 528', 'PC-15', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(266, 'Lab 528', 'PC-16', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(267, 'Lab 528', 'PC-17', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(268, 'Lab 528', 'PC-18', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(269, 'Lab 528', 'PC-19', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(270, 'Lab 528', 'PC-20', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(271, 'Lab 528', 'PC-21', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(272, 'Lab 528', 'PC-22', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(273, 'Lab 528', 'PC-23', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(274, 'Lab 528', 'PC-24', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(275, 'Lab 528', 'PC-25', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(276, 'Lab 528', 'PC-26', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(277, 'Lab 528', 'PC-27', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(278, 'Lab 528', 'PC-28', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(279, 'Lab 528', 'PC-29', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(280, 'Lab 528', 'PC-30', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(281, 'Lab 528', 'PC-31', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(282, 'Lab 528', 'PC-32', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(283, 'Lab 528', 'PC-33', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(284, 'Lab 528', 'PC-34', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(285, 'Lab 528', 'PC-35', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(286, 'Lab 528', 'PC-36', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(287, 'Lab 528', 'PC-37', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(288, 'Lab 528', 'PC-38', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(289, 'Lab 528', 'PC-39', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(290, 'Lab 528', 'PC-40', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(291, 'Lab 528', 'PC-41', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(292, 'Lab 528', 'PC-42', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(293, 'Lab 528', 'PC-43', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(294, 'Lab 528', 'PC-44', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(295, 'Lab 528', 'PC-45', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(296, 'Lab 528', 'PC-46', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(297, 'Lab 528', 'PC-47', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(298, 'Lab 528', 'PC-48', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(299, 'Lab 528', 'PC-49', 'available', NULL, NULL, '2025-05-07 16:06:06'),
(300, 'Lab 528', 'PC-50', 'available', NULL, NULL, '2025-05-07 16:06:06');
=======
(1, 'Lab 2', 'PC-50', 0, 7, '2025-05-04 12:07:56', '2025-05-04 14:07:56');
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df

-- --------------------------------------------------------

--
-- Table structure for table `profiles`
--

CREATE TABLE `profiles` (
  `id` int(11) NOT NULL,
  `idno` varchar(50) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) DEFAULT NULL,
  `course` varchar(50) NOT NULL,
  `year` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `student_name` varchar(100) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `lab` varchar(50) NOT NULL,
  `pc_number` varchar(20) DEFAULT NULL,
  `time_in` time NOT NULL,
  `reservation_date` date NOT NULL,
  `remaining_session` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Declined') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `student_name`, `purpose`, `lab`, `pc_number`, `time_in`, `reservation_date`, `remaining_session`, `status`) VALUES
(1, '1010', 'john louie purisima', 'Java', '25', NULL, '10:06:00', '0000-00-00', 26, 'Declined'),
(2, '1010', 'johnlouie nacaytuna purisima', 'APS.NET', '524', NULL, '10:06:00', '2025-03-20', 30, 'Declined'),
(3, '1010', 'johnlouie nacaytuna purisima', 'APS.NET', '55', NULL, '10:06:00', '2025-03-20', 30, 'Accepted'),
(4, '2020', 'shao weak lugay', 'Java', '555', NULL, '10:06:00', '2025-04-09', 30, 'Accepted'),
(5, '1010', 'johnlouie nacaytuna purisima', 'Java', 'Lab 3', 'PC-49', '14:09:00', '2025-05-02', 20, 'Accepted'),
(6, '1010', 'johnlouie nacaytuna purisima', 'C programming', 'Lab 1', 'PC-50', '12:40:00', '2025-05-04', 20, 'Accepted'),
<<<<<<< HEAD
(7, '1010', 'johnlouie nacaytuna purisima', 'APS.NET', 'Lab 2', 'PC-50', '12:07:00', '2025-05-04', 20, 'Accepted'),
(8, '1010', 'johnlouie nacaytuna purisima', 'Java', 'Lab 3', 'PC-50', '13:52:00', '2025-05-04', 20, 'Accepted'),
(9, '1010', 'johnlouie nacaytuna purisima', 'Java', 'Lab 4', 'PC-50', '13:59:00', '2025-05-04', 20, 'Accepted'),
(10, '1010', 'johnlouie nacaytuna purisima', 'C#', 'Lab 1', 'PC-1', '23:56:00', '2025-05-04', 20, 'Accepted'),
(11, '2020', 'shao weak lugay', 'APS.NET', 'Lab 1', 'PC-50', '00:03:00', '2025-05-05', 30, 'Accepted'),
(12, '2020', 'shao weak lugay', 'APS.NET', 'Lab 2', 'PC-50', '00:10:00', '2025-05-05', 30, 'Accepted'),
(13, '1', 'justin ako abao', 'Java', 'Lab 2', 'PC-2', '22:12:00', '2025-05-05', 30, 'Accepted'),
(14, '1010', 'johnlouie nacaytuna purisima', 'ASP.NET', 'Lab 524', 'PC-1', '21:44:00', '2025-05-07', 20, 'Accepted'),
(15, '2', 'durano doblas doblas', 'ASP.NET', 'Lab 524', 'PC-2', '21:56:00', '2025-05-07', 30, 'Accepted'),
(16, '2', 'durano doblas doblas', 'ASP.NET', 'Lab 526', 'PC-1', '22:30:00', '2025-05-07', 29, 'Accepted'),
(17, '1010', 'johnlouie nacaytuna purisima', 'ASP.NET', 'Lab 524', 'PC-5', '23:06:00', '2025-05-07', 20, 'Accepted'),
(18, '1010', 'johnlouie nacaytuna purisima', 'ASP.NET', 'Lab 528', 'PC-50', '23:25:00', '2025-05-07', 20, 'Accepted'),
(19, '1', 'justin ako abao', 'C Programming', 'Lab 524', 'PC-20', '23:40:00', '2025-05-07', 29, 'Accepted'),
(20, '3', 'ako budoy killer', 'C#', 'Lab 524', 'PC-15', '23:53:00', '2025-05-07', 29, 'Accepted'),
(21, '1010', 'johnlouie nacaytuna purisima', 'C#', 'Lab 524', 'PC-50', '08:35:00', '2025-05-08', 20, 'Accepted'),
(22, '1010', 'johnlouie nacaytuna purisima', 'Java Programming', 'Lab 524', 'PC-22', '08:46:00', '2025-05-08', 20, 'Accepted'),
(23, '1010', 'johnlouie nacaytuna purisima', 'ASP.NET', 'Lab 524', 'PC-49', '08:57:00', '2025-05-08', 20, 'Accepted'),
(24, '1010', 'johnlouie nacaytuna purisima', 'C#', '524', 'PC-4', '10:12:00', '2025-05-08', 20, 'Declined'),
(25, '3', 'ako budoy killer', 'C#', 'Lab 524', 'PC-17', '10:26:00', '2025-05-08', 29, 'Accepted'),
(26, '3', 'ako budoy killer', 'C#', 'Lab 526', 'PC-20', '10:34:00', '2025-05-08', 29, 'Declined'),
(27, '2', 'durano doblas doblas', 'C Programming', 'Lab 542', 'PC-50', '10:39:00', '2025-05-08', 29, 'Accepted'),
(28, '3', 'ako budoy killer', 'C#', 'Lab 524', 'PC-2', '23:18:00', '2025-05-08', 29, 'Accepted');

-- --------------------------------------------------------

--
-- Table structure for table `reservation_logs`
--

CREATE TABLE `reservation_logs` (
  `id` int(11) NOT NULL,
  `reservation_id` int(11) NOT NULL,
  `action` enum('created','accepted','declined','cancelled') NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
=======

>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df

--
-- Dumping data for table `reservation_logs`
--

INSERT INTO `reservation_logs` (`id`, `reservation_id`, `action`, `user_id`, `details`, `created_at`) VALUES
(1, 15, 'accepted', '3030', 'Reservation accepted for Lab 524 - PC-2', '2025-05-07 22:14:02'),
(2, 18, 'accepted', '3030', 'Reservation accepted for Lab 528 - 00', '2025-05-07 23:28:21'),
(3, 19, 'accepted', '3030', 'Reservation accepted for Lab 524 - 00', '2025-05-07 23:40:40'),
(4, 20, 'accepted', '3030', 'Reservation accepted for Lab 524 - PC PC-00', '2025-05-07 23:54:09'),
(5, 26, 'accepted', '3030', 'Reservation accepted for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:34:15'),
(6, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:34:36'),
(7, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:35:49'),
(8, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:35:49'),
(9, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:35:49'),
(10, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:35:50'),
(11, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:38:05'),
(12, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:38:06'),
(13, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:38:06'),
(14, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:38:06'),
(15, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:38:06'),
(16, 26, 'declined', '3030', 'Reservation declined for ako budoy killer\nLab: Lab 526\nPC: PC-20\nDate: May 8, 2025\nTime: 10:34 AM', '2025-05-08 10:38:09'),
(17, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:39:52'),
(18, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:24'),
(19, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:24'),
(20, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:24'),
(21, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:24'),
(22, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:24'),
(23, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:24'),
(24, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:25'),
(25, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:25'),
(26, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:25'),
(27, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:25'),
(28, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:25'),
(29, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:32'),
(30, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:32'),
(31, 27, 'accepted', '3030', 'Reservation accepted for durano doblas doblas\nLab: Lab 542\nPC: PC-50\nDate: May 8, 2025\nTime: 10:39 AM', '2025-05-08 10:42:32'),
(32, 28, 'accepted', '3030', 'Reservation accepted for ako budoy killer\nLab: Lab 524\nPC: PC-2\nDate: May 8, 2025\nTime: 11:18 PM', '2025-05-08 23:18:39'),
(33, 28, 'accepted', '3030', 'Reservation accepted for ako budoy killer\nLab: Lab 524\nPC: PC-2\nDate: May 8, 2025\nTime: 11:18 PM', '2025-05-08 23:19:09');

-- --------------------------------------------------------

--
-- Table structure for table `reward_transactions`
--

CREATE TABLE `reward_transactions` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `points_change` int(11) NOT NULL,
  `transaction_type` enum('earn','redeem','admin_adjust') NOT NULL,
  `description` varchar(255) NOT NULL,
  `admin_id` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reward_transactions`
--

INSERT INTO `reward_transactions` (`id`, `student_id`, `points_change`, `transaction_type`, `description`, `admin_id`, `created_at`) VALUES
(1, '1010', 1, 'admin_adjust', 'good', '3030', '2025-05-04 04:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `sit_in_history`
--

CREATE TABLE `sit_in_history` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `purpose` varchar(255) NOT NULL,
  `lab` varchar(255) NOT NULL,
  `session_start` datetime NOT NULL,
  `session_end` datetime DEFAULT NULL,
  `date_time` datetime DEFAULT NULL,
  `status` enum('pending','accepted','declined') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sit_in_history`
--

INSERT INTO `sit_in_history` (`id`, `user_id`, `purpose`, `lab`, `session_start`, `session_end`, `date_time`, `status`) VALUES
(13, '1010', 'C#', '10', '2025-03-17 19:49:08', '2025-03-17 19:49:16', '2025-03-17 19:49:16', 'pending'),
(14, '1010', 'C#', '5', '2025-03-17 20:07:04', '2025-03-17 20:13:11', NULL, 'pending'),
(15, '1010', 'C programming', '524', '2025-03-17 20:13:26', '2025-03-17 20:13:28', NULL, 'pending'),
(16, '1010', 'Java', '524', '2025-03-17 20:40:34', '2025-03-17 20:40:36', NULL, 'pending'),
(17, '1010', 'Java', '555', '2025-03-17 20:44:24', '2025-03-20 09:58:30', NULL, 'pending'),
(18, '1010', 'APS.NET', '5', '2025-03-20 09:59:35', '2025-03-20 09:59:39', NULL, 'pending'),
(19, '1010', 'java', '555', '2025-03-20 09:59:58', '2025-03-20 10:00:02', NULL, 'pending'),
(20, '1010', 'Java', '555', '2025-03-20 10:03:10', '2025-03-20 10:03:16', NULL, 'pending'),
(21, '1010', 'APS.NET', '555', '2025-03-22 19:35:26', '2025-03-22 19:35:27', NULL, 'pending'),
(22, '1010', 'ASP', '555', '2025-04-05 16:47:05', '2025-04-05 16:47:07', NULL, 'pending'),
(23, '2020', 'C', '555', '2025-04-09 20:38:49', '2025-05-07 22:18:54', NULL, 'pending'),
(24, '1', 'ASP', '1', '2025-05-05 22:13:16', '2025-05-07 22:18:53', NULL, 'pending'),
(25, '2', 'ASP', '2', '2025-05-05 22:38:24', '2025-05-07 22:18:51', NULL, 'pending'),
(26, '3', 'ASP', '3', '2025-05-05 22:38:35', '2025-05-07 22:18:50', NULL, 'pending'),
(27, '2', 'ASP.NET', 'Lab 524', '2025-05-07 22:14:02', NULL, NULL, 'accepted'),
(28, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:26:46', NULL, NULL, 'accepted'),
(29, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:26:47', NULL, NULL, 'accepted'),
(30, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:26:47', NULL, NULL, 'accepted'),
(31, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:26:47', NULL, NULL, 'accepted'),
(32, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:26:47', NULL, NULL, 'accepted'),
(33, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:26:48', NULL, NULL, 'accepted'),
(34, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:26:48', NULL, NULL, 'accepted'),
(35, '1010', 'ASP.NET', 'Lab 528', '2025-05-07 23:28:21', NULL, NULL, 'accepted'),
(36, '1', 'C Programming', 'Lab 524', '2025-05-07 23:40:40', NULL, NULL, 'accepted'),
(37, '3', 'C#', 'Lab 524', '2025-05-07 23:54:09', NULL, NULL, 'accepted'),
(38, '1010', 'Java Programming', 'Lab 524', '2025-05-08 08:46:41', NULL, NULL, 'accepted'),
(39, '3', 'C#', 'Lab 524', '2025-05-08 10:30:23', NULL, NULL, 'accepted'),
(40, '1010', 'ASP.NET', 'Lab 524', '2025-05-08 10:30:28', NULL, NULL, 'accepted'),
(41, '3', 'C#', 'Lab 526', '2025-05-08 10:34:15', NULL, NULL, 'accepted'),
(42, '2', 'C Programming', 'Lab 542', '2025-05-08 10:39:52', NULL, NULL, 'accepted'),
(43, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:24', NULL, NULL, 'accepted'),
(44, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:24', NULL, NULL, 'accepted'),
(45, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:24', NULL, NULL, 'accepted'),
(46, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:24', NULL, NULL, 'accepted'),
(47, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:24', NULL, NULL, 'accepted'),
(48, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:24', NULL, NULL, 'accepted'),
(49, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:25', NULL, NULL, 'accepted'),
(50, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:25', NULL, NULL, 'accepted'),
(51, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:25', NULL, NULL, 'accepted'),
(52, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:25', NULL, NULL, 'accepted'),
(53, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:25', NULL, NULL, 'accepted'),
(54, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:32', NULL, NULL, 'accepted'),
(55, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:32', NULL, NULL, 'accepted'),
(56, '2', 'C Programming', 'Lab 542', '2025-05-08 10:42:32', NULL, NULL, 'accepted'),
(57, '3', 'C#', 'Lab 524', '2025-05-08 23:18:39', NULL, NULL, 'accepted'),
(58, '3', 'C#', 'Lab 524', '2025-05-08 23:19:09', NULL, NULL, 'accepted');

-- --------------------------------------------------------

--
-- Table structure for table `student_rewards`
--

CREATE TABLE `student_rewards` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `total_points` int(11) NOT NULL DEFAULT 0,
  `free_sessions_available` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_rewards`
--

INSERT INTO `student_rewards` (`id`, `student_id`, `total_points`, `free_sessions_available`, `last_updated`) VALUES
(1, '1010', 1, 0, '2025-05-04 04:01:20');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `idno` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `middlename` varchar(50) DEFAULT NULL,
  `course` varchar(10) NOT NULL,
  `year` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin','staff','student') NOT NULL,
  `sessions_remaining` int(11) DEFAULT 30,
  `total_points` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `idno`, `lastname`, `firstname`, `middlename`, `course`, `year`, `email`, `role`, `sessions_remaining`, `total_points`) VALUES
<<<<<<< HEAD
(7, '', '$2y$10$/uWrtMQtN0IG5t/CqmCbKu1mvVjufaKBTYFAn43PomoHvsCXafubq', '1010', 'purisima', 'johnlouie', 'nacaytuna', 'BSIT', 3, 'purisimajohnlouie@gmail.com', 'student', 29, 12),
(9, '', '$2y$10$SwsFnur78ZwlzdKqGrWwtejAaK34WgUVvFoime.SKGIk55GIuSeK.', '2020', 'lugay', 'shao', 'weak', 'BSECE', 3, 'user@uc.com', 'student', 29, 13),
(10, '', '$2y$10$ALnp4a5yuGN7fQBMSgoy0uLLTQDf2wtuRuN8VRR7cRGx19nFpoKSu', '3030', 'opaw', 'me', 'you', 'BSIT', 1, 'admin@uc.com', 'admin', 30, 0),
(11, '', '$2y$10$ka64stgi3rz5TQ1iBjyP1OAoRYWeaYQFbVZRu.rYSoyfO1XjxDZZe', '1', 'abao', 'justin', 'ako', 'BSIT', 1, 'abao@gmail.com', 'student', 30, 3),
(12, '', '$2y$10$crgTVPK1eomhHEqb9WJCnO2au.6wXs7yeZJpMhYxpnWE0S9o8OTJ6', '2', 'doblas', 'durano', 'doblas', 'BSIT', 1, 'doblas@gmail.com', 'student', 30, 1),
(13, '', '$2y$10$xLLe.8tb1iNmqBTB80AhCO0B4OGt/6ca4p9IiJ6/NkBOFxMXBhzby', '3', 'killer', 'ako', 'budoy', 'BSIT', 1, 'me@gmail.com', 'student', 30, 4);
=======
(7, '', '$2y$10$/uWrtMQtN0IG5t/CqmCbKu1mvVjufaKBTYFAn43PomoHvsCXafubq', '1010', 'purisima', 'johnlouie', 'nacaytuna', 'BSIT', 3, 'purisimajohnlouie@gmail.com', 'student', 29, 10),
(9, '', '$2y$10$SwsFnur78ZwlzdKqGrWwtejAaK34WgUVvFoime.SKGIk55GIuSeK.', '2020', 'lugay', 'shao', 'weak', 'BSECE', 3, 'user@uc.com', 'student', 29, 11),
(10, '', '$2y$10$ALnp4a5yuGN7fQBMSgoy0uLLTQDf2wtuRuN8VRR7cRGx19nFpoKSu', '3030', 'opaw', 'me', 'you', 'BSIT', 1, 'admin@uc.com', 'admin', 30, 0);
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `id` int(11) NOT NULL,
  `user_id` varchar(50) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_points`
--

INSERT INTO `user_points` (`id`, `user_id`, `points`, `last_updated`) VALUES
(1, '1010', 13, '2025-04-20 05:08:34'),
(2, '3', 41, '2025-05-07 14:18:50'),
(3, '2', 41, '2025-05-07 14:18:51'),
(4, '1', 42, '2025-05-07 14:18:53'),
(5, '2020', 667, '2025-05-07 14:18:54');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `session_start` datetime NOT NULL,
  `session_end` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `computer_control`
--
ALTER TABLE `computer_control`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_pc` (`lab_name`,`pc_number`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_resources`
--
ALTER TABLE `lab_resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `lab_schedules`
--
ALTER TABLE `lab_schedules`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `type` (`type`),
  ADD KEY `is_read` (`is_read`);

--
-- Indexes for table `pc_availability`
--
ALTER TABLE `pc_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_pc` (`lab_name`,`pc_number`);

--
-- Indexes for table `pc_management`
--
ALTER TABLE `pc_management`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_lab_pc` (`lab_name`,`pc_number`),
  ADD KEY `reservation_id` (`reservation_id`);
=======
-- Indexes for table `pc_availability`
--
ALTER TABLE `pc_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_pc` (`lab_name`,`pc_number`);
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df

--
-- Indexes for table `profiles`
--
ALTER TABLE `profiles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`);

--
<<<<<<< HEAD
-- Indexes for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `reservation_id` (`reservation_id`),
  ADD KEY `action` (`action`),
  ADD KEY `user_id` (`user_id`);

--
=======
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df
-- Indexes for table `reward_transactions`
--
ALTER TABLE `reward_transactions`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `student_rewards`
--
ALTER TABLE `student_rewards`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_id` (`student_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idno` (`idno`);

--
-- Indexes for table `user_points`
--
ALTER TABLE `user_points`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `computer_control`
--
ALTER TABLE `computer_control`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `lab_resources`
--
ALTER TABLE `lab_resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `lab_schedules`
--
ALTER TABLE `lab_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
<<<<<<< HEAD
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `pc_availability`
--
ALTER TABLE `pc_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `pc_management`
--
ALTER TABLE `pc_management`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=301;
=======
-- AUTO_INCREMENT for table `pc_availability`
--
ALTER TABLE `pc_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
<<<<<<< HEAD
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `reservation_logs`
--
ALTER TABLE `reservation_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
=======
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
>>>>>>> 0b362b59c5036c0ec101a83250dc596be07607df

--
-- AUTO_INCREMENT for table `reward_transactions`
--
ALTER TABLE `reward_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `student_rewards`
--
ALTER TABLE `student_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `user_points`
--
ALTER TABLE `user_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `pc_management`
--
ALTER TABLE `pc_management`
  ADD CONSTRAINT `pc_management_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  ADD CONSTRAINT `sit_in_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`idno`);

--
-- Constraints for table `user_points`
--
ALTER TABLE `user_points`
  ADD CONSTRAINT `user_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`idno`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

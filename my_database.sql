-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 22, 2025 at 12:38 PM
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
(3, '1010', 17, 0, 'thanks', '2025-03-17 21:10:14');

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
  `time_in` time NOT NULL,
  `reservation_date` date NOT NULL,
  `remaining_session` int(11) NOT NULL,
  `status` enum('Pending','Accepted','Declined') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `student_name`, `purpose`, `lab`, `time_in`, `reservation_date`, `remaining_session`, `status`) VALUES
(1, '1010', 'john louie purisima', 'Java', '25', '10:06:00', '0000-00-00', 26, 'Declined'),
(2, '1010', 'johnlouie nacaytuna purisima', 'APS.NET', '524', '10:06:00', '2025-03-20', 30, 'Declined'),
(3, '1010', 'johnlouie nacaytuna purisima', 'APS.NET', '55', '10:06:00', '2025-03-20', 30, 'Accepted');

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
(21, '1010', 'APS.NET', '555', '2025-03-22 19:35:26', '2025-03-22 19:35:27', NULL, 'pending');

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
(7, '', '$2y$10$/uWrtMQtN0IG5t/CqmCbKu1mvVjufaKBTYFAn43PomoHvsCXafubq', '1010', 'purisima', 'johnlouie', 'nacaytuna', 'BSIT', 3, 'purisimajohnlouie@gmail.com', 'student', 29, 0),
(8, '', '$2y$10$PIuJTtHqBx5SOMtsp7D7uufI1cBrQ/5fgZs5CetXNGY5HOHzOdBXm', '2020', 'abao', 'opaw', 'pisot', 'BSIT', 4, 'user@uc.com', 'admin', 30, 0);

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
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `idno` (`idno`);

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
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  ADD CONSTRAINT `sit_in_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`idno`);

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

-- --------------------------------------------------------

--
-- Table structure for table `user_points`
--

CREATE TABLE `user_points` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` varchar(50) NOT NULL,
  `points` int(11) NOT NULL DEFAULT 0,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_id` (`user_id`),
  CONSTRAINT `user_points_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`idno`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `lab_resources`
--

CREATE TABLE `lab_resources` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `lab` varchar(50) NOT NULL,
  `resource_name` varchar(100) NOT NULL,
  `status` enum('available','unavailable') NOT NULL DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `lab` (`lab`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for table `lab_resources`
--
ALTER TABLE `lab_resources`
  ADD KEY `lab` (`lab`);

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

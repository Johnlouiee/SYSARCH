-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 04, 2025 at 06:23 AM
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
  `last_update` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `reservation_id` (`reservation_id`),
  CONSTRAINT `computer_control_ibfk_1` FOREIGN KEY (`reservation_id`) REFERENCES `reservations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Dumping data for table `pc_availability`
--

INSERT INTO `pc_availability` (`id`, `lab_name`, `pc_number`, `is_available`, `reservation_id`, `reserved_from`, `reserved_to`) VALUES
(1, 'Lab 2', 'PC-50', 0, 7, '2025-05-04 12:07:56', '2025-05-04 14:07:56');

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
(23, '2020', 'C', '555', '2025-04-09 20:38:49', NULL, NULL, 'pending');

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
(7, '', '$2y$10$/uWrtMQtN0IG5t/CqmCbKu1mvVjufaKBTYFAn43PomoHvsCXafubq', '1010', 'purisima', 'johnlouie', 'nacaytuna', 'BSIT', 3, 'purisimajohnlouie@gmail.com', 'student', 29, 10),
(9, '', '$2y$10$SwsFnur78ZwlzdKqGrWwtejAaK34WgUVvFoime.SKGIk55GIuSeK.', '2020', 'lugay', 'shao', 'weak', 'BSECE', 3, 'user@uc.com', 'student', 29, 11),
(10, '', '$2y$10$ALnp4a5yuGN7fQBMSgoy0uLLTQDf2wtuRuN8VRR7cRGx19nFpoKSu', '3030', 'opaw', 'me', 'you', 'BSIT', 1, 'admin@uc.com', 'admin', 30, 0);

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
(1, '1010', 13, '2025-04-20 05:08:34');

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
-- Indexes for table `pc_availability`
--
ALTER TABLE `pc_availability`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `lab_pc` (`lab_name`,`pc_number`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT for table `pc_availability`
--
ALTER TABLE `pc_availability`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `profiles`
--
ALTER TABLE `profiles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `reward_transactions`
--
ALTER TABLE `reward_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sit_in_history`
--
ALTER TABLE `sit_in_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `student_rewards`
--
ALTER TABLE `student_rewards`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `user_points`
--
ALTER TABLE `user_points`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

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

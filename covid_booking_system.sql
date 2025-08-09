-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 03, 2025 at 09:14 PM
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
-- Database: `covid_booking_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `password`, `full_name`, `created_at`) VALUES
(1, 'admin', 'admin@covidbook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '2025-08-03 11:04:01');

-- --------------------------------------------------------

--
-- Table structure for table `hospitals`
--

CREATE TABLE `hospitals` (
  `id` int(11) NOT NULL,
  `hospital_name` varchar(200) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `license_number` varchar(100) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `test_available` tinyint(1) DEFAULT 1,
  `vaccine_available` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `hospital_name`, `email`, `password`, `phone`, `address`, `city`, `state`, `pincode`, `license_number`, `status`, `test_available`, `vaccine_available`, `created_at`) VALUES
(1, 'Aga Khan University Hospital', 'akuu@karachi.com', '$2y$10$S7xC98sLW0nymx5Z9FgHeOkm1IY4arrlEPwaes4Ntlg7kt/LKYWUW', '021111111111', 'Stadium Road', 'Karachi', 'Sindh', '74800', 'PAK-LIC-001', 'approved', 1, 1, '2025-08-03 11:04:01'),
(2, 'Jinnah Postgraduate Medical Centre', 'jpmc@karachi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '02199212600', 'Rafiqui Shaheed Road', 'Karachi', 'Sindh', '75510', 'PAK-LIC-002', 'approved', 1, 1, '2025-08-03 11:04:01'),
(3, 'Liaquat National Hospital', 'lnh@karachi.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '021111222333', 'National Stadium Rd', 'Karachi', 'Sindh', '74800', 'PAK-LIC-003', 'approved', 1, 1, '2025-08-03 11:04:01'),
(4, 'Shaukat Khanum Memorial Hospital', 'skmh@lahore.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '04235905000', '7A Block R3', 'Lahore', 'Punjab', '54000', 'PAK-LIC-004', 'approved', 1, 1, '2025-08-03 11:04:01'),
(5, 'Pakistan Institute of Medical Sciences', 'pims@isb.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0519261170', 'Sector G-8/3', 'Islamabad', 'Federal', '44000', 'PAK-LIC-005', 'approved', 1, 1, '2025-08-03 11:04:01'),
(6, 'Civil Hospital', 'civil@quetta.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812840381', 'Mezan Chowk', 'Quetta', 'Balochistan', '87300', 'PAK-LIC-006', 'approved', 1, 1, '2025-08-03 11:04:01');

-- --------------------------------------------------------

--
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `date_of_birth` date NOT NULL,
  `gender` enum('male','female','other') NOT NULL,
  `address` text NOT NULL,
  `city` varchar(100) NOT NULL,
  `state` varchar(100) NOT NULL,
  `pincode` varchar(10) NOT NULL,
  `cnic` varchar(15) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `full_name`, `email`, `password`, `phone`, `date_of_birth`, `gender`, `address`, `city`, `state`, `pincode`, `cnic`, `created_at`) VALUES
(8, 'Ayesha', 'ayesha@gmail.com', '$2y$10$CrCFgajxBeXZOkmybULedOeZOTdSnwZdNg7I4ufcbnA7rQkda.FAq', '03268193702', '2006-03-15', 'female', 'North Nazimabad', 'Karachi', 'abc', '123456', '4220107083451', '2025-08-03 14:36:52'),
(9, 'Mehak', 'mehak@gmail.com', '$2y$10$XG6qNUwQxub/7KwpTbKEc.bml6m6xhvT5qs6uvUJFwYEAlUt1Z/T.', '03166441752', '2006-07-12', 'female', 'North Nazimabad', 'Karachi', 'abc', '123456', '4220107086405', '2025-08-03 14:40:39'),
(12, 'Sheeza', 'sheeza@gmail.com', '$2y$10$WeKcO1Hq1vuLFybXR74nQuo0pK612ezZueki3f1g6uG4dbIzz0Fiy', '03171056981', '2005-07-28', 'female', 'Clifton', 'Karachi', 'abc', '123456', '4220107083467', '2025-08-03 14:47:38'),
(13, 'Bilal Khan', 'bilal@gmail.com', '$2y$10$o2l9LEeQ2nx8Eeb.otJFreuwoIFlKJlRqWg56Eb0brB7j4gsMarem', '03172948371', '1998-11-26', 'male', 'Gulshan e Johar', 'Karachi', 'abc', '123456', '4220107086390', '2025-08-03 14:52:42'),
(14, 'Hamza', 'hamza@gmail.com', '$2y$10$O0t1u56ZvLkxYSackoxx5.2Id6PZ7hrUnJlDUv5vYKjpNVhBs8Zby', '03402689325', '2001-07-30', 'male', 'DHA', 'Karachi', 'abc', '123456', '4220107086276', '2025-08-03 16:01:40'),
(15, 'Mahateer', 'mahateer@gmail.com', '$2y$10$EtKrOHcuA9Opr.kvmx5BH.jdVAcDd8bDZk4AJjMTU/odSVF2Wr3uy', '03287042781', '1992-11-03', 'male', 'Lyari', 'Karachi', 'abc', '123456', '4220107086890', '2025-08-03 16:05:43'),
(16, 'Muhammad Talha', 'toyub368@gmail.com', '$2y$10$bEFsfE8HvELAgjZd5ZkIPOaXraGOkZvVRshQj9/HQXxIJAyBmBTLy', '03171046981', '2006-09-05', 'male', 'Naval Colony', '', 'abc', '123456', '4220107086201', '2025-08-03 19:09:39');

-- --------------------------------------------------------

--
-- Table structure for table `test_bookings`
--

CREATE TABLE `test_bookings` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `test_type` enum('RT-PCR','Rapid Antigen','Antibody') NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `test_result` enum('positive','negative','pending') DEFAULT 'pending',
  `result_date` date DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `test_bookings`
--

INSERT INTO `test_bookings` (`id`, `patient_id`, `hospital_id`, `test_type`, `booking_date`, `booking_time`, `status`, `test_result`, `result_date`, `price`, `created_at`) VALUES
(20, 8, 1, 'Rapid Antigen', '2025-08-03', '14:00:00', 'completed', 'positive', '2025-08-03', 300.00, '2025-08-03 14:37:23'),
(21, 9, 6, 'Rapid Antigen', '2025-08-04', '14:00:00', 'completed', 'negative', '2025-08-03', 300.00, '2025-08-03 14:41:38'),
(22, 12, 2, 'Rapid Antigen', '2025-08-19', '15:00:00', 'completed', 'positive', '2025-08-03', 300.00, '2025-08-03 14:48:11'),
(23, 13, 3, 'Antibody', '2025-08-31', '14:00:00', 'completed', 'positive', '2025-08-03', 400.00, '2025-08-03 14:55:15'),
(24, 14, 5, 'Antibody', '2025-08-12', '14:00:00', 'completed', 'negative', '2025-08-03', 400.00, '2025-08-03 16:02:15'),
(25, 15, 4, 'Rapid Antigen', '2025-08-25', '10:00:00', 'completed', 'positive', '2025-08-03', 300.00, '2025-08-03 16:07:02'),
(26, 16, 1, 'Antibody', '2025-08-20', '12:00:00', 'completed', 'negative', '2025-08-03', 400.00, '2025-08-03 19:10:39');

-- --------------------------------------------------------

--
-- Table structure for table `vaccination_bookings`
--

CREATE TABLE `vaccination_bookings` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `vaccine_name` varchar(100) NOT NULL,
  `dose_number` enum('1','2','booster') NOT NULL,
  `booking_date` date NOT NULL,
  `booking_time` time NOT NULL,
  `status` enum('pending','approved','rejected','completed') DEFAULT 'pending',
  `vaccination_date` date DEFAULT NULL,
  `certificate_number` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccination_bookings`
--

INSERT INTO `vaccination_bookings` (`id`, `patient_id`, `hospital_id`, `vaccine_name`, `dose_number`, `booking_date`, `booking_time`, `status`, `vaccination_date`, `certificate_number`, `created_at`) VALUES
(20, 8, 1, 'Sinopharm', '2', '2025-08-03', '14:00:00', 'completed', '2025-08-03', 'CERT2025250066', '2025-08-03 14:37:39'),
(21, 9, 6, 'Sinopharm', 'booster', '2025-08-10', '14:00:00', 'completed', '2025-08-03', 'CERT2025948257', '2025-08-03 14:41:53'),
(22, 9, 6, 'Sinopharm', '2', '2025-08-05', '16:00:00', 'completed', '2025-08-03', 'CERT2025214515', '2025-08-03 14:42:16'),
(23, 12, 2, 'Sinovac', '2', '2025-08-12', '11:00:00', 'completed', '2025-08-03', 'CERT2025283136', '2025-08-03 14:48:30'),
(24, 13, 3, 'Sputnik V', 'booster', '2025-08-30', '16:00:00', 'completed', '2025-08-03', 'CERT2025453887', '2025-08-03 14:53:30'),
(25, 14, 5, 'Novavax', 'booster', '2025-09-15', '15:00:00', 'completed', '2025-08-03', 'CERT2025398672', '2025-08-03 16:02:36'),
(26, 15, 4, 'Covishield', '1', '2025-10-06', '09:00:00', 'completed', '2025-08-03', 'CERT2025859286', '2025-08-03 16:07:19'),
(27, 16, 1, 'Sinopharm', '2', '2025-08-18', '10:00:00', 'completed', '2025-08-03', 'CERT2025354653', '2025-08-03 19:10:59');

-- --------------------------------------------------------

--
-- Table structure for table `vaccine_inventory`
--

CREATE TABLE `vaccine_inventory` (
  `id` int(11) NOT NULL,
  `hospital_id` int(11) NOT NULL,
  `vaccine_name` varchar(100) NOT NULL,
  `available_doses` int(11) DEFAULT 0,
  `price` decimal(10,2) DEFAULT 0.00,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vaccine_inventory`
--

INSERT INTO `vaccine_inventory` (`id`, `hospital_id`, `vaccine_name`, `available_doses`, `price`, `updated_at`) VALUES
(1, 1, 'Pfizer', 200, 2500.00, '2025-08-03 16:45:46'),
(2, 1, 'Sinopharm', 149, 1800.00, '2025-08-03 19:13:25'),
(3, 2, 'Sinovac', 179, 2000.00, '2025-08-03 14:49:52'),
(4, 2, 'Cansino', 120, 2200.00, '2025-08-03 11:04:01'),
(5, 3, 'AstraZeneca', 170, 2800.00, '2025-08-03 11:04:01'),
(6, 3, 'Sputnik V', 89, 3200.00, '2025-08-03 14:57:26'),
(7, 4, 'Moderna', 140, 3500.00, '2025-08-03 11:04:01'),
(8, 4, 'Covishield', 99, 2800.00, '2025-08-03 16:08:28'),
(9, 5, 'Novavax', 159, 3000.00, '2025-08-03 16:03:43'),
(10, 5, 'Johnson & Johnson', 130, 3200.00, '2025-08-03 11:04:01'),
(11, 6, 'Sinopharm', 109, 1800.00, '2025-08-03 14:43:53'),
(12, 6, 'Pfizer', 85, 2500.00, '2025-08-03 11:04:01');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `hospitals`
--
ALTER TABLE `hospitals`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `license_number` (`license_number`);

--
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cnic` (`cnic`);

--
-- Indexes for table `test_bookings`
--
ALTER TABLE `test_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `vaccination_bookings`
--
ALTER TABLE `vaccination_bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- Indexes for table `vaccine_inventory`
--
ALTER TABLE `vaccine_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hospital_id` (`hospital_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hospitals`
--
ALTER TABLE `hospitals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `test_bookings`
--
ALTER TABLE `test_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `vaccination_bookings`
--
ALTER TABLE `vaccination_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `vaccine_inventory`
--
ALTER TABLE `vaccine_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `test_bookings`
--
ALTER TABLE `test_bookings`
  ADD CONSTRAINT `test_bookings_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `test_bookings_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaccination_bookings`
--
ALTER TABLE `vaccination_bookings`
  ADD CONSTRAINT `vaccination_bookings_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patients` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `vaccination_bookings_ibfk_2` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `vaccine_inventory`
--
ALTER TABLE `vaccine_inventory`
  ADD CONSTRAINT `vaccine_inventory_ibfk_1` FOREIGN KEY (`hospital_id`) REFERENCES `hospitals` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

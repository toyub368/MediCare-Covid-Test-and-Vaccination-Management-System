-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 30, 2025 at 05:08 PM
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
(1, 'admin', 'admin@covidbook.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'System Administrator', '2025-07-21 04:11:02');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `specialties` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `established_year` int(11) DEFAULT NULL,
  `bed_capacity` int(11) DEFAULT NULL,
  `emergency_services` tinyint(1) DEFAULT 0,
  `ambulance_services` tinyint(1) DEFAULT 0,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hospitals`
--

INSERT INTO `hospitals` (`id`, `hospital_name`, `email`, `password`, `phone`, `address`, `city`, `state`, `pincode`, `license_number`, `status`, `test_available`, `vaccine_available`, `created_at`, `specialties`, `description`, `contact_person`, `website`, `established_year`, `bed_capacity`, `emergency_services`, `ambulance_services`, `updated_at`) VALUES
(2, 'Metro Medical Center', 'metro@hospital.com', '$2y$10$mNWRBkLOJkAuPSGj.tB28uFHGaXYue0iRZ6K0o/qhCn2uxzzlpGAC', '9876543211', '456 Park Avenue', 'Peshawar', 'abc', '110001', 'LIC002', 'approved', 1, 1, '2025-07-21 04:11:02', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(3, 'Apollo Health Care', 'apollo@hospital.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543212', '789 Health Street', 'Quetta', 'abc', '560001', 'LIC003', 'approved', 1, 1, '2025-07-21 04:11:02', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL),
(4, 'Liaquat National', 'liaquat@national.com', '$2y$10$74wK8hd8L.11g9O8.5OPcOnSIJy5OXpDbHJzRYavepEiey18hn8gW', '9123455614', 'abc', 'abc', 'abc', '1223', 'LCO34', 'approved', 1, 1, '2025-07-29 02:50:54', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, NULL);

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
  `aadhar_number` varchar(12) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `patients`
--

INSERT INTO `patients` (`id`, `full_name`, `email`, `password`, `phone`, `date_of_birth`, `gender`, `address`, `city`, `state`, `pincode`, `aadhar_number`, `created_at`) VALUES
(2, 'Jane Smith', 'jane@patient.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9876543213', '1985-05-15', 'female', '456 Pine Avenue', 'Delhi', 'Delhi', '110002', '123456789013', '2025-07-21 04:11:02'),
(3, 'talha', 'toyub345@gmail.com', '$2y$10$FbNXkLbDj2xRtFqbLFujnegNEuswAnkBzGk0U2CMxpUqA7.lIPYbe', '9123455612', '2025-07-23', 'male', 'naval', 'karachi', 'abc', '1223', '112222333333', '2025-07-22 04:34:35'),
(4, 'hamza', 'hamza@gmail.com', '$2y$10$NpgMUFw0rw5RKjwu.LhGIuqVYFx9y/sn92caoa43Q33Z9SupvefMq', '9134567891', '2025-07-10', 'male', 'abc', 'abc', 'abc', '123', '1289493493', '2025-07-22 23:38:10');

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
(2, 2, 2, 'Rapid Antigen', '2024-01-16', '14:00:00', 'completed', 'positive', '2025-07-23', 300.00, '2025-07-21 04:11:02'),
(3, 3, 3, 'RT-PCR', '2025-07-23', '10:00:00', 'pending', 'pending', NULL, 500.00, '2025-07-22 04:35:34'),
(4, 3, 2, 'Antibody', '2025-07-23', '11:00:00', 'rejected', 'pending', NULL, 400.00, '2025-07-22 04:36:40'),
(5, 3, 3, 'Antibody', '2025-07-24', '14:00:00', 'pending', 'pending', NULL, 400.00, '2025-07-22 04:37:05'),
(6, 4, 2, 'Antibody', '2025-07-24', '17:00:00', 'completed', 'positive', '2025-07-23', 400.00, '2025-07-22 23:38:48'),
(9, 4, 4, 'Antibody', '2025-07-31', '14:00:00', 'completed', 'positive', '2025-07-29', 400.00, '2025-07-29 02:53:03');

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
(2, 2, 2, 'Covaxin', '1', '2024-01-12', '15:00:00', 'completed', '2025-07-21', 'CERT2025221726', '2025-07-21 04:11:02'),
(3, 4, 2, 'Sputnik V', '2', '2025-07-24', '17:00:00', 'completed', '2025-07-23', 'CERT2025817554', '2025-07-22 23:39:07'),
(5, 4, 3, 'Covaxin', '2', '2025-07-30', '14:00:00', 'completed', '2025-07-29', 'CERT2025205194', '2025-07-29 01:56:08'),
(6, 4, 4, 'Sputnik V', '1', '2025-07-31', '16:00:00', 'completed', '2025-07-29', 'CERT2025655270', '2025-07-29 02:55:43');

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
(3, 2, 'Covishield', 150, 250.00, '2025-07-21 04:11:02'),
(4, 2, 'Sputnik V', 49, 400.00, '2025-07-22 23:40:39'),
(5, 3, 'Covaxin', 120, 500.00, '2025-07-30 13:49:44'),
(6, 3, 'Covishield', 200, 250.00, '2025-07-21 04:11:02'),
(7, 4, 'Sputnik V', 22, 300.00, '2025-07-29 02:58:14'),
(8, 3, 'Covishield', 12, 400.00, '2025-07-30 13:42:38');

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
  ADD UNIQUE KEY `aadhar_number` (`aadhar_number`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `test_bookings`
--
ALTER TABLE `test_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `vaccination_bookings`
--
ALTER TABLE `vaccination_bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vaccine_inventory`
--
ALTER TABLE `vaccine_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

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

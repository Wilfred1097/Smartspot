-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Nov 15, 2024 at 04:14 AM
-- Server version: 10.11.9-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u136659995_smartpot`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `coordinate_image` text NOT NULL,
  `rstp_url` text NOT NULL,
  `live_feed_url` text NOT NULL,
  `2_wheeler_rate` varchar(10) NOT NULL,
  `3_wheeler_rate` varchar(10) NOT NULL,
  `4_wheeler_rate` varchar(10) NOT NULL,
  `2_wheeler_overnight_rate` varchar(10) NOT NULL,
  `3_wheeler_overnight_rate` varchar(10) NOT NULL,
  `4_wheeler_overnight_rate` varchar(10) NOT NULL,
  `vacant_space` int(11) NOT NULL,
  `occupied_space` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `config`
--

INSERT INTO `config` (`id`, `coordinate_image`, `rstp_url`, `live_feed_url`, `2_wheeler_rate`, `3_wheeler_rate`, `4_wheeler_rate`, `2_wheeler_overnight_rate`, `3_wheeler_overnight_rate`, `4_wheeler_overnight_rate`, `vacant_space`, `occupied_space`) VALUES
(1, 'parking2.jpg', 'rtsp://mr.daotz97@gmail.com:Wilfred.912816@192.168.8.103:554/stream1', 'http://127.0.0.1:5000', '12', '18', '22', '168', '168', '168', 3, 0);

-- --------------------------------------------------------

--
-- Table structure for table `images`
--

CREATE TABLE `images` (
  `id` int(11) NOT NULL,
  `path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overnight_parking`
--

CREATE TABLE `overnight_parking` (
  `id` int(11) NOT NULL,
  `owner_name` varchar(100) NOT NULL,
  `dob` varchar(100) NOT NULL,
  `license` varchar(100) NOT NULL,
  `address` varchar(100) NOT NULL,
  `contact_num` varchar(100) NOT NULL,
  `vehicle_type` varchar(100) NOT NULL,
  `make_model` varchar(100) NOT NULL,
  `color` varchar(100) NOT NULL,
  `cert_reg` varchar(100) NOT NULL,
  `vin_num` varchar(100) NOT NULL,
  `date_in` varchar(100) NOT NULL,
  `time_in` varchar(100) NOT NULL,
  `date_out` varchar(100) NOT NULL,
  `time_out` varchar(100) NOT NULL,
  `payment_type` varchar(100) NOT NULL,
  `amount` varchar(50) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `parking_only`
--

CREATE TABLE `parking_only` (
  `id` int(11) NOT NULL,
  `vehicle_owner` text NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `make_model` varchar(50) NOT NULL,
  `license_num` varchar(50) NOT NULL,
  `color` varchar(50) NOT NULL,
  `date_in` varchar(50) NOT NULL,
  `Time_in` varchar(50) NOT NULL,
  `date_out` text NOT NULL,
  `Time_out` text NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `amount` int(50) NOT NULL,
  `parking_duration` text NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_only`
--

INSERT INTO `parking_only` (`id`, `vehicle_owner`, `vehicle_type`, `make_model`, `license_num`, `color`, `date_in`, `Time_in`, `date_out`, `Time_out`, `payment_type`, `amount`, `parking_duration`, `date_added`) VALUES
(27, '', '4', 'Ford Raptor', '1906MC', 'Green', '2024-11-12', '15:11', '2024-11-12', '15:34', 'cash', 20, '1', '2024-11-12 07:11:46'),
(28, '', '4', 'Toyota Innova', '1906JC', 'black', '2024-11-12', '15:12', '2024-11-12', '16:21', 'cash', 40, '2', '2024-11-12 07:12:28'),
(29, 'John Doe', '4', 'Honda Elantra', '1906JC', 'black', '2024-11-13', '09:14', '2024-11-14', '07:29', 'cash', 820, '47', '2024-11-13 01:14:30'),
(30, 'John Doe', '4', 'Mercedez Benz', '2341LW', 'Silver', '2024-11-14', '09:14', '2024-11-14', '07:21', 'cash', 1220, '71', '2024-11-13 01:14:54');

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `mname` varchar(50) NOT NULL,
  `lname` varchar(50) NOT NULL,
  `address` varchar(50) NOT NULL,
  `gender` varchar(50) NOT NULL,
  `cont` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `password` text NOT NULL,
  `otp` varchar(50) NOT NULL,
  `status` varchar(50) NOT NULL,
  `user_type` varchar(255) NOT NULL,
  `profile` text NOT NULL,
  `registration_date` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `fname`, `mname`, `lname`, `address`, `gender`, `cont`, `email`, `password`, `otp`, `status`, `user_type`, `profile`, `registration_date`) VALUES
(5, 'Heraclio', 'Limbaga', 'Catalan', 'P-4 Bag-ong Dalaguete, Mahayag, Zamboanga del Sur', 'Male', '09054100152', 'ikecatalan277@gmail.com', '$2y$10$KhTcgXOVwx/.W4yF8a6smeOMZ74CWdaGdMtzcyTXbi43Vib1y0bES', '277585', '', 'admin', 'admin.jpg', '2024-09-11 14:52:30'),
(7, 'Noel', '', 'Placido', 'New labangan labangan zambuanga del sur', 'Male', '09123456789', 'noelplacido224@gmail.com', '$2y$10$fbR4rjDtFzcRqGP1olbkAuPDVdfQhDfV2eA6q/Jq.cufh348diwR.', '', 'active', 'staff', '', '2024-09-17 11:30:24'),
(17, 'Wilfredo', 'Dumon', 'Catalan', 'P-4 Bag-ong Dalaguete, Mahayag, Zamboanga del Sur', 'Male', '09054100152', 'catalanwilfredo97@gmail.com', '$2y$10$EpYe7z9m5xYEyvu5.XUdEee9Z.OGDsyWCp0NXJfl/SCYQBSDE1qFm', '682674', 'active', 'admin', 'IMG_20211220_204950.jpg', '2024-10-16 08:36:04'),
(20, 'Noel', '', 'Placido', 'New labangan labangan zambuanga del sur', 'male', '19670236643', 'noelplacido224@mail.com', '$2y$10$H4Msiv98A3vJEWd.UX3o0eplOYFliAX6EG4GuABTt.x4nR6.M.00W', '', 'active', 'staff', 'inbound6298900768836653517.jpg', '2024-11-11 03:54:50'),
(22, 'Stefany', '', 'Dizon', 'Katipunan', 'female', '09123456789', 'dizonstefany01@gmail.com', '$2y$10$ZpjfL47CU.q1FfiSt3BBBegxHYqyqT3GcLuANdm/84Hs0aYz34SYW', '', 'active', 'staff', '441485208_854886016687540_9039637611578954329_n.jpg', '2024-11-13 02:08:41');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_category`
--

CREATE TABLE `vehicle_category` (
  `id` int(11) NOT NULL,
  `vehicle_type` text NOT NULL,
  `amount` int(11) NOT NULL,
  `overtime` int(11) NOT NULL,
  `ate_added` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vehicle_category`
--

INSERT INTO `vehicle_category` (`id`, `vehicle_type`, `amount`, `overtime`, `ate_added`) VALUES
(6, '4', 20, 200, '2024-11-10 09:08:45');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `config`
--
ALTER TABLE `config`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `images`
--
ALTER TABLE `images`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overnight_parking`
--
ALTER TABLE `overnight_parking`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `parking_only`
--
ALTER TABLE `parking_only`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `vehicle_category`
--
ALTER TABLE `vehicle_category`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `config`
--
ALTER TABLE `config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `images`
--
ALTER TABLE `images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `overnight_parking`
--
ALTER TABLE `overnight_parking`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `parking_only`
--
ALTER TABLE `parking_only`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `vehicle_category`
--
ALTER TABLE `vehicle_category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

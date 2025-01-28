-- phpMyAdmin SQL Dump
-- version 5.1.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 18, 2024 at 03:38 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smartspot`
--

-- --------------------------------------------------------

--
-- Table structure for table `config`
--

CREATE TABLE `config` (
  `id` int(11) NOT NULL,
  `coordinate_image` text NOT NULL,
  `rstp_url` text NOT NULL,
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

INSERT INTO `config` (`id`, `coordinate_image`, `rstp_url`, `2_wheeler_rate`, `3_wheeler_rate`, `4_wheeler_rate`, `2_wheeler_overnight_rate`, `3_wheeler_overnight_rate`, `4_wheeler_overnight_rate`, `vacant_space`, `occupied_space`) VALUES
(1, 'parking2.jpg', 'rtsp://mr.daotz97@gmail.com:Wilfred.912816@192.168.8.103:554/stream1', '12', '18', '22', '168', '168', '168', 5, 0);

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

--
-- Dumping data for table `overnight_parking`
--

INSERT INTO `overnight_parking` (`id`, `owner_name`, `dob`, `license`, `address`, `contact_num`, `vehicle_type`, `make_model`, `color`, `cert_reg`, `vin_num`, `date_in`, `time_in`, `date_out`, `time_out`, `payment_type`, `amount`, `date_added`) VALUES
(1, 'John Doe', '1983-12-10', '1906MC', '1600 Fake Street', '09054100152', '4', 'Honda Civic', 'Black', '30012931', '093124', '2024-10-18', '07:38', '2024-10-18', '14:26', 'cash', '168', '2024-10-18 14:26:22'),
(2, 'Wilfred', '1997-12-10', '1906MC', 'P-4 Bag-ong Dalaguete, Mahayag, Zamboanga del Sur', '09054100152', '4', 'Honda Civic', 'Black', '30012931', '093124', '2024-09-22', '20:40', '2024-09-23', '20:29', 'cash', '168', '2024-09-22 22:37:19'),
(3, 'Wilfred Catalan', '1997-12-10', '1906JC', 'P-4 Bag-ong Dalaguete, Mahayag, Zamboanga del Sur', '09054100152', '2', 'Honda Wave 125', 'Black', '30012931', '093124', '2024-09-23', '21:26', '2024-09-24', '21:29', 'cash', '336', '2024-09-24 22:37:19'),
(7, 'John Doe', '1987-02-02', '1906MC', 'Rua Inexistente, 2000', '09054100152', '4', 'Honda Civic', 'White', '30012931', '093124', '2024-10-18', '08:22', '2024-10-19', '08:24', 'cash', '168', '2024-10-18 08:23:20'),
(8, 'John Doe', '1976-05-02', '1906JC', '1600 Fake Street', '09054100152', '4', 'Honda Civic', 'White', '30012931', '093124', '2024-10-18', '08:23', '2024-10-18', '14:12', 'cash', '168', '2024-10-18 14:12:07');

-- --------------------------------------------------------

--
-- Table structure for table `parking_only`
--

CREATE TABLE `parking_only` (
  `id` int(11) NOT NULL,
  `vehicle_type` varchar(50) NOT NULL,
  `make_model` varchar(50) NOT NULL,
  `license_num` varchar(50) NOT NULL,
  `color` varchar(50) NOT NULL,
  `date_in` varchar(50) NOT NULL,
  `Time_in` varchar(50) NOT NULL,
  `payment_type` varchar(50) NOT NULL,
  `amount` varchar(50) NOT NULL,
  `date_added` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `parking_only`
--

INSERT INTO `parking_only` (`id`, `vehicle_type`, `make_model`, `license_num`, `color`, `date_in`, `Time_in`, `payment_type`, `amount`, `date_added`) VALUES
(1, '2', 'Honda Wave', '1906MC', 'black', '2024-09-23', '19:19', 'cash', '12', '2024-09-23 22:39:43'),
(2, '4', 'Suzuki Raider', '2093JS', 'Black', '2024-09-23', '19:20', 'cash', '22', '2024-09-23 22:39:43'),
(3, '4', 'Honda Civic', '2321MK', 'White', '2024-09-23', '19:22', 'e-wallet', '22', '2024-09-23 22:39:43'),
(4, '4', 'Mercedez Benz', '2341LW', 'Green', '2024-09-23', '19:25', 'cash', '22', '2024-09-23 22:39:43'),
(5, '2', 'Honda Beat', '2312BG', 'Black', '2024-09-23', '19:29', 'cash', '12', '2024-09-23 22:39:43'),
(6, '4', 'Honda Elantra', '2312BG', 'Blue', '2024-09-23', '20:40', 'cash', '22', '2024-09-23 22:39:43'),
(7, '2', 'Honda Wave 123', '1906JC', 'black', '2024-09-24', '21:20', 'cash', '12', '2024-09-24 22:39:43'),
(8, '2', 'Suzuki Raider', '1906MC', 'Gray', '2024-10-12', '11:04', 'cash', '12', '2024-10-12 22:39:43'),
(9, '2', 'Honda Wave 125', '1906JC', 'black', '2024-10-16', '22:49', 'cash', '12', '2024-10-16 22:49:35');

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
(7, 'Kent John', 'Dumon', 'Catalan', 'P-4 Bag-ong Dalaguete, Mahayag, Zamboanga del Sur', 'male', '6019521325', 'kent.john@gmail.com', '$2y$10$CnRKbCf03TdHIcqL7Np9jutffcENUU/yWaaj.2uvWjOwXdUvoo85a', '', 'verified', 'customer', '', '2024-09-17 11:30:24'),
(10, 'Anthony', 'Dumon', 'Catalan', 'P-4 Bag-ong Dalaguete, Mahayag, Zamboanga del Sur', 'Male', '09054100152', 'catalanwilfredo96@gmail.com', '$2y$10$UiypB.a37Sz8p9ISWvMa.ebOwKUPILUqR.X7kaCynB5c3hviMFPwy', '102497', 'active', 'staff', '', '2024-09-20 22:06:51'),
(11, 'Jon', 'Stewart', 'Doe', '1600 Fake Street', 'male', '3121286800', 'john.doe@gmail.com', '$2y$10$Ofo.iIidM/hF15b71cvKc.Pt1t4fa6BoRLUFHkm6m86xud64u/r7S', '', 'inactive', 'staff', '', '2024-10-06 00:18:32'),
(17, 'Wilfredo', 'Dumon', 'Catalan', 'P-4 Bag-ong Dalaguete, Mahayag, Zamboanga del Sur', 'male', '09054100152', 'catalanwilfredo97@gmail.com', '$2y$10$weyntYgfm9EGd9x079cEueIlnfV7qfMolgt297WXswinfqPEf0Osq', '682674', 'active', 'staff', 'IMG_20211220_204950.jpg', '2024-10-16 08:36:04');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

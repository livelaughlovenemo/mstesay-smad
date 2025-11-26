-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 18, 2025 at 04:13 PM
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
-- Database: `smad_init`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `inv_date` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `supplier` varchar(50) NOT NULL,
  `kilos` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `inv_date`, `category`, `product_name`, `supplier`, `kilos`, `created_at`) VALUES
(1, '2025-09-17', '', 'Whole Chicken', 'Marcela', 358.52, '2025-09-17 05:31:20'),
(2, '2025-09-17', '', 'Whole Chicken', 'Marcela', 358.52, '2025-09-17 05:31:25'),
(3, '2025-09-17', '', 'Whole Chicken', 'Marcela', 358.52, '2025-09-17 05:32:20'),
(4, '2025-09-17', '', 'Whole Chicken', 'Manay', 198.52, '2025-09-17 05:32:20'),
(5, '2025-09-17', '', 'Whole Chicken', 'Remaining', 275.87, '2025-09-17 05:32:20'),
(6, '2025-09-17', '', 'Whole Chicken', 'Lexzoes', 181.02, '2025-09-17 05:32:20'),
(7, '2025-09-17', '', 'Whole Chicken', 'Pick-Ups', 47.01, '2025-09-17 05:32:20'),
(8, '2025-09-17', '', 'BackBones', 'Marcela', 306.10, '2025-09-17 05:32:20'),
(9, '2025-09-17', '', 'BackBones', 'Remaining', 93.15, '2025-09-17 05:32:20'),
(10, '2025-09-17', '', 'Neck', 'Marcela', 100.00, '2025-09-17 05:32:20'),
(11, '2025-09-17', '', 'Neck', 'Remaining', 19.55, '2025-09-17 05:32:20'),
(12, '2025-09-17', '', 'SKT Bones', 'Marcela', 82.08, '2025-09-17 05:32:20'),
(13, '2025-09-17', '', 'SKT Bones', 'Remaining', 5.15, '2025-09-17 05:32:20'),
(14, '2025-09-17', '', 'Skin', 'Marcela', 20.74, '2025-09-17 05:32:20'),
(15, '2025-09-17', '', 'Whole Chicken', 'Marcela', 100.00, '2025-09-17 05:33:34'),
(16, '2025-09-17', '', 'Whole Chicken', 'Manay', 100.00, '2025-09-17 05:33:34'),
(17, '2025-09-17', '', 'Whole Chicken', 'Remaining', 100.00, '2025-09-17 05:33:34'),
(18, '2025-09-17', '', 'Whole Chicken', 'Lexzoes', 100.00, '2025-09-17 05:33:34'),
(19, '2025-09-17', '', 'Whole Chicken', 'Wella', 100.00, '2025-09-17 05:33:34'),
(20, '2025-09-17', '', 'Whole Chicken', 'Pick-Ups', 100.00, '2025-09-17 05:33:34');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(12,2) NOT NULL,
  `sale_datetime` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$MKbL0kZNhAEmtAoa1s3jtuB1847LDFLqfJBOhBz46BZ0aefqGgQjK', '2025-09-17 05:00:50'),
(2, 'adminadmin', 'adminadmin', '2025-11-18 14:44:36');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 02, 2025 at 06:08 PM
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
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `table_name` varchar(50) NOT NULL,
  `record_id` int(11) DEFAULT NULL,
  `action_time` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `daily_inventory`
--

CREATE TABLE `daily_inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `stock_in` decimal(10,2) DEFAULT 0.00,
  `remaining` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) GENERATED ALWAYS AS (`stock_in` + `remaining`) STORED,
  `price` decimal(10,2) DEFAULT 0.00,
  `amount` decimal(12,2) GENERATED ALWAYS AS (`total` * `price`) STORED,
  `record_date` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `inv_date` date NOT NULL,
  `category` varchar(50) NOT NULL,
  `product_name` varchar(100) NOT NULL,
  `supplier` varchar(50) NOT NULL,
  `location_id` int(11) DEFAULT NULL,
  `kilos` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` decimal(10,2) DEFAULT 0.00,
  `inv_type` enum('add','subtract') DEFAULT 'add'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `product_id`, `inv_date`, `category`, `product_name`, `supplier`, `location_id`, `kilos`, `created_at`, `quantity`, `inv_type`) VALUES
(1, 1, '2025-09-17', '', 'Whole Chicken', 'Marcela', NULL, 358.52, '2025-09-17 05:31:20', 0.00, 'add'),
(2, 1, '2025-09-17', '', 'Whole Chicken', 'Marcela', NULL, 358.52, '2025-09-17 05:31:25', 0.00, 'add'),
(3, 1, '2025-09-17', '', 'Whole Chicken', 'Marcela', NULL, 358.52, '2025-09-17 05:32:20', 0.00, 'add'),
(4, 1, '2025-09-17', '', 'Whole Chicken', 'Manay', NULL, 198.52, '2025-09-17 05:32:20', 0.00, 'add'),
(5, 1, '2025-09-17', '', 'Whole Chicken', 'Remaining', NULL, 275.87, '2025-09-17 05:32:20', 0.00, 'add'),
(6, 1, '2025-09-17', '', 'Whole Chicken', 'Lexzoes', NULL, 181.02, '2025-09-17 05:32:20', 0.00, 'add'),
(7, 1, '2025-09-17', '', 'Whole Chicken', 'Pick-Ups', NULL, 47.01, '2025-09-17 05:32:20', 0.00, 'add'),
(8, 0, '2025-09-17', '', 'BackBones', 'Marcela', NULL, 306.10, '2025-09-17 05:32:20', 0.00, 'add'),
(9, 0, '2025-09-17', '', 'BackBones', 'Remaining', NULL, 93.15, '2025-09-17 05:32:20', 0.00, 'add'),
(10, 0, '2025-09-17', '', 'Neck', 'Marcela', NULL, 100.00, '2025-09-17 05:32:20', 0.00, 'add'),
(11, 0, '2025-09-17', '', 'Neck', 'Remaining', NULL, 19.55, '2025-09-17 05:32:20', 0.00, 'add'),
(12, 0, '2025-09-17', '', 'SKT Bones', 'Marcela', NULL, 82.08, '2025-09-17 05:32:20', 0.00, 'add'),
(13, 0, '2025-09-17', '', 'SKT Bones', 'Remaining', NULL, 5.15, '2025-09-17 05:32:20', 0.00, 'add'),
(14, 0, '2025-09-17', '', 'Skin', 'Marcela', NULL, 20.74, '2025-09-17 05:32:20', 0.00, 'add'),
(15, 1, '2025-09-17', '', 'Whole Chicken', 'Marcela', NULL, 100.00, '2025-09-17 05:33:34', 0.00, 'add'),
(16, 1, '2025-09-17', '', 'Whole Chicken', 'Manay', NULL, 100.00, '2025-09-17 05:33:34', 0.00, 'add'),
(17, 1, '2025-09-17', '', 'Whole Chicken', 'Remaining', NULL, 100.00, '2025-09-17 05:33:34', 0.00, 'add'),
(18, 1, '2025-09-17', '', 'Whole Chicken', 'Lexzoes', NULL, 100.00, '2025-09-17 05:33:34', 0.00, 'add'),
(19, 1, '2025-09-17', '', 'Whole Chicken', 'Wella', NULL, 100.00, '2025-09-17 05:33:34', 0.00, 'add'),
(20, 1, '2025-09-17', '', 'Whole Chicken', 'Pick-Ups', NULL, 100.00, '2025-09-17 05:33:34', 0.00, 'add'),
(21, 35, '2025-12-02', '', 'Bilog', 'Lexzoes', 4, 100.00, '2025-12-02 15:24:25', 0.00, 'add'),
(22, 35, '2025-12-02', '', '', '', NULL, 0.00, '2025-12-02 15:52:52', 120.00, 'add'),
(23, 35, '2025-12-02', '', '', '', NULL, 0.00, '2025-12-02 15:53:02', 60.00, 'subtract');

-- --------------------------------------------------------

--
-- Table structure for table `locations`
--

CREATE TABLE `locations` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `locations`
--

INSERT INTO `locations` (`id`, `name`) VALUES
(4, 'Lexzoes'),
(2, 'Manay'),
(1, 'Marcela'),
(6, 'Pick-Ups'),
(3, 'Remaining'),
(5, 'Wella');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `stock` decimal(10,2) DEFAULT 0.00,
  `availability` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `category`, `price`, `created_at`, `stock`, `availability`) VALUES
(1, 'Whole Chicken', 'chicken', 150.00, '2025-11-26 05:21:48', 0.00, 1),
(2, 'Chicken Wings', 'chicken', 180.00, '2025-11-26 05:21:48', 0.00, 1),
(3, 'Chicken Drumsticks', 'chicken', 160.00, '2025-11-26 05:21:48', 0.00, 1),
(4, 'Chicken Thighs', 'chicken', 170.00, '2025-11-26 05:21:48', 0.00, 1),
(5, 'Chicken Breast', 'chicken', 200.00, '2025-11-26 05:21:48', 0.00, 1),
(6, 'Chicken Legs', 'chicken', 155.00, '2025-11-26 05:21:48', 0.00, 1),
(7, 'Chicken Liver', 'chicken', 90.00, '2025-11-26 05:21:48', 0.00, 1),
(8, 'Champion Hotdog Jumbo 1Kilo', 'frozen', 250.00, '2025-11-26 05:24:05', 0.00, 1),
(9, 'Champion Hotdog Jumbo 250G', 'frozen', 70.00, '2025-11-26 05:24:05', 0.00, 1),
(10, 'Champion Hotdog Mini 250G', 'frozen', 60.00, '2025-11-26 05:24:05', 0.00, 1),
(11, 'Booster Hotdog Jumbo 1k', 'frozen', 240.00, '2025-11-26 05:24:05', 0.00, 1),
(12, 'Booster Hotdog Jumbo 240G', 'frozen', 65.00, '2025-11-26 05:24:05', 0.00, 1),
(13, 'Booster Hotdog Regular 240G', 'frozen', 60.00, '2025-11-26 05:24:05', 0.00, 1),
(14, 'BS Hotdog Classic KingSize 1K', 'frozen', 230.00, '2025-11-26 05:24:05', 0.00, 1),
(15, 'BS Hotdog Classic Jumbo 1K', 'frozen', 220.00, '2025-11-26 05:24:05', 0.00, 1),
(16, 'BS Hotdog Cheese KingSize 1K', 'frozen', 250.00, '2025-11-26 05:24:05', 0.00, 1),
(17, 'BS Hotdog Cheese Jumbo 1K', 'frozen', 240.00, '2025-11-26 05:24:05', 0.00, 1),
(18, 'Champion Pork Longganiza', 'frozen', 180.00, '2025-11-26 05:24:05', 0.00, 1),
(19, 'Champion Chicken Longganiza', 'frozen', 185.00, '2025-11-26 05:24:05', 0.00, 1),
(20, 'Winner Cooked Ham', 'frozen', 200.00, '2025-11-26 05:24:05', 0.00, 1),
(21, 'Winner Sweet Ham', 'frozen', 195.00, '2025-11-26 05:24:05', 0.00, 1),
(22, 'EL RANCHO Corned Beef', 'frozen', 160.00, '2025-11-26 05:24:05', 0.00, 1),
(23, 'Virginia Pork Tocino', 'frozen', 180.00, '2025-11-26 05:24:05', 0.00, 1),
(24, 'Champion Chicken Loaf', 'frozen', 170.00, '2025-11-26 05:24:05', 0.00, 1),
(25, 'Champion Chicken Hotdog', 'frozen', 150.00, '2025-11-26 05:24:05', 0.00, 1),
(26, 'Virginia Chicken Hotdog', 'frozen', 145.00, '2025-11-26 05:24:05', 0.00, 1),
(27, 'Champion Cheese Hotdog', 'frozen', 160.00, '2025-11-26 05:24:05', 0.00, 1),
(28, 'Winner Bola-bola', 'frozen', 155.00, '2025-11-26 05:24:05', 0.00, 1),
(29, 'Kings Longganiza', 'frozen', 175.00, '2025-11-26 05:24:05', 0.00, 1),
(30, 'IQF Longganiza', 'frozen', 170.00, '2025-11-26 05:24:05', 0.00, 1),
(31, 'Luncheon Meat', 'frozen', 180.00, '2025-11-26 05:24:05', 0.00, 1),
(32, 'Tocino Roll', 'frozen', 190.00, '2025-11-26 05:24:05', 0.00, 1),
(33, 'Smoke Longganiza', 'frozen', 200.00, '2025-11-26 05:24:05', 0.00, 1),
(34, 'Longga Dog', 'frozen', 150.00, '2025-11-26 05:24:05', 0.00, 1),
(35, 'Bilog', 'frozen', 140.00, '2025-11-26 05:24:05', 60.00, 1),
(36, 'Calderon', 'frozen', 135.00, '2025-11-26 05:24:05', 0.00, 1),
(37, 'K - Patties', 'frozen', 120.00, '2025-11-26 05:24:05', 0.00, 1),
(38, 'Ganado', 'frozen', 130.00, '2025-11-26 05:24:05', 0.00, 1),
(39, 'TJ Classic', 'frozen', 160.00, '2025-11-26 05:24:05', 0.00, 1),
(40, 'TJ Cheesedog Regular', 'frozen', 165.00, '2025-11-26 05:24:05', 0.00, 1),
(41, 'TJ Cheesedog Jumbo', 'frozen', 180.00, '2025-11-26 05:24:05', 0.00, 1),
(42, 'TJ Cocktail', 'frozen', 150.00, '2025-11-26 05:24:05', 0.00, 1),
(43, 'Lumpia Shanghai', 'frozen', 140.00, '2025-11-26 05:24:05', 0.00, 1),
(44, 'Bologna', 'frozen', 130.00, '2025-11-26 05:24:05', 0.00, 1),
(45, 'Ginaling', 'frozen', 125.00, '2025-11-26 05:24:05', 0.00, 1),
(46, 'STYRO FOAM', 'frozen', 50.00, '2025-11-26 05:24:05', 0.00, 1),
(47, 'Virginia Tocino Roll', 'frozen', 185.00, '2025-11-26 05:24:05', 0.00, 1),
(48, 'Bulgogi', 'frozen', 220.00, '2025-11-26 05:24:05', 0.00, 1),
(49, 'BS Spicy Hotdog', 'frozen', 165.00, '2025-11-26 05:24:05', 0.00, 1),
(50, 'Sisig', 'frozen', 170.00, '2025-11-26 05:24:05', 0.00, 1),
(51, 'TEST', 'RAW', 67.00, '2025-12-02 15:17:44', 0.00, 0);

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

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `quantity`, `total_price`, `sale_datetime`) VALUES
(2, 35, 12, 1680.00, '2025-12-02 23:17:08');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `role` enum('Admin','Manager','Staff') NOT NULL DEFAULT 'Staff'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `created_at`, `role`) VALUES
(1, 'admin', '$2y$10$MKbL0kZNhAEmtAoa1s3jtuB1847LDFLqfJBOhBz46BZ0aefqGgQjK', '2025-09-17 05:00:50', 'Admin'),
(13, 'staff', '$2y$10$r6doG/ZxBc.xus7towCnA.T12hdY3prPcOMReznygvdf5XqxwebIu', '2025-12-02 15:01:16', 'Staff'),
(14, 'manager', '$2y$10$hDX3a5cuRMV9jfW/gQ48Zuv7RoJkATd2iJUXRo6.2po8QwlX/7G8K', '2025-12-02 15:01:29', 'Staff');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `daily_inventory`
--
ALTER TABLE `daily_inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_inventory_location` (`location_id`),
  ADD KEY `idx_inventory_product_id` (`product_id`),
  ADD KEY `idx_inventory_date` (`inv_date`);

--
-- Indexes for table `locations`
--
ALTER TABLE `locations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

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
  ADD KEY `product_id` (`product_id`),
  ADD KEY `idx_sales_product_id` (`product_id`),
  ADD KEY `idx_sales_datetime` (`sale_datetime`);

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
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_inventory`
--
ALTER TABLE `daily_inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `locations`
--
ALTER TABLE `locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=52;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `daily_inventory`
--
ALTER TABLE `daily_inventory`
  ADD CONSTRAINT `daily_inventory_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `daily_inventory_ibfk_2` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Constraints for table `inventory`
--
ALTER TABLE `inventory`
  ADD CONSTRAINT `fk_inventory_location` FOREIGN KEY (`location_id`) REFERENCES `locations` (`id`);

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

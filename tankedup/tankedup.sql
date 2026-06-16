-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 09, 2025 at 04:13 PM
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
-- Database: `tankedup`
--

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `fish_name` varchar(255) NOT NULL,
  `fish_price` decimal(10,2) UNSIGNED NOT NULL,
  `fish_quantity` int(10) UNSIGNED NOT NULL,
  `fish_type` enum('Freshwater','Saltwater','Brackish','Special','Invertebrates') NOT NULL,
  `fish_origin` varchar(100) NOT NULL,
  `fish_size` varchar(20) DEFAULT NULL,
  `tank_size` int(10) UNSIGNED NOT NULL,
  `fish_temperament` enum('Peaceful','Semi-Aggressive','Aggressive','Highly-Aggresive') NOT NULL,
  `ph_range` varchar(255) DEFAULT NULL,
  `temp_range` varchar(255) DEFAULT NULL,
  `fish_description` text DEFAULT NULL,
  `fish_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `fish_name`, `fish_price`, `fish_quantity`, `fish_type`, `fish_origin`, `fish_size`, `tank_size`, `fish_temperament`, `ph_range`, `temp_range`, `fish_description`, `fish_image`) VALUES
(56, 'Tibby', 0.01, 50, 'Special', 'Scotland', '5-15', 50, 'Peaceful', '7.5-8.0', '25-28', 'a', '61547458_2332475793476822_8844881346724626432_n.jpg'),
(57, 'Dani Mi Purrfect Babby <3333', 99999.00, 25, 'Invertebrates', 'Philippines', '7-8', 10, 'Semi-Aggressive', '8-9.0', '25-28', 'MI LOVER BABBA', '1340e5a0-880d-47c3-ae8e-63dbee4802bf.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `order_quantity` int(11) NOT NULL DEFAULT 1,
  `status` enum('Pending','Completed','Shipped','Cancelled') DEFAULT 'Pending',
  `total_cost` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_id`, `product_id`, `order_date`, `order_quantity`, `status`, `total_cost`) VALUES
(10, 23, 56, '2025-03-08 19:17:30', 1, 'Pending', 0.00),
(11, 23, 56, '2025-03-08 21:05:50', 1, 'Pending', 0.00),
(12, 23, 56, '2025-03-08 21:11:09', 2, 'Pending', 0.02),
(13, 23, 56, '2025-03-08 21:11:15', 6, 'Pending', 0.06),
(14, 23, 56, '2025-03-08 21:52:19', 1, 'Pending', 0.01),
(15, 23, 56, '2025-03-08 21:54:07', 1, 'Pending', 0.01),
(16, 23, 56, '2025-03-08 22:00:27', 1, 'Pending', 0.01),
(17, 23, 56, '2025-03-08 22:05:18', 1, 'Pending', 0.01),
(18, 23, 56, '2025-03-08 22:05:26', 1, 'Pending', 0.01),
(19, 24, 56, '2025-03-09 12:32:30', 2, 'Pending', 0.02),
(20, 24, 56, '2025-03-09 12:33:53', 2, 'Pending', 0.02),
(21, 24, 56, '2025-03-09 12:34:14', 2, 'Pending', 0.02),
(22, 24, 57, '2025-03-09 13:42:14', 10, 'Pending', 999990.00),
(23, 24, 57, '2025-03-09 14:03:50', 1, 'Pending', 99999.00),
(24, 24, 57, '2025-03-09 14:05:03', 1, 'Pending', 99999.00);

-- --------------------------------------------------------

--
-- Table structure for table `order_history`
--

CREATE TABLE `order_history` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `old_status` enum('Pending','Completed','Shipped','Cancelled') NOT NULL,
  `new_status` enum('Pending','Completed','Shipped','Cancelled') NOT NULL,
  `changed_by` int(11) NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_history`
--

INSERT INTO `order_history` (`id`, `order_id`, `old_status`, `new_status`, `changed_by`, `changed_at`) VALUES
(1, 10, 'Pending', 'Shipped', 23, '2025-03-08 20:54:27'),
(2, 10, 'Shipped', 'Completed', 23, '2025-03-08 20:54:30'),
(3, 10, 'Completed', 'Cancelled', 23, '2025-03-08 20:54:34'),
(4, 11, 'Pending', 'Shipped', 23, '2025-03-09 12:31:05'),
(5, 19, 'Pending', 'Shipped', 23, '2025-03-09 13:56:13'),
(6, 19, 'Shipped', 'Completed', 23, '2025-03-09 13:56:17'),
(7, 19, 'Completed', 'Cancelled', 23, '2025-03-09 13:56:19'),
(8, 19, 'Cancelled', 'Pending', 23, '2025-03-09 13:56:43'),
(9, 10, 'Cancelled', 'Cancelled', 23, '2025-03-09 13:58:05'),
(10, 10, 'Cancelled', 'Pending', 23, '2025-03-09 13:58:08'),
(11, 11, 'Shipped', 'Pending', 23, '2025-03-09 13:58:10'),
(12, 21, 'Pending', 'Cancelled', 23, '2025-03-09 14:11:51'),
(13, 21, 'Cancelled', 'Pending', 23, '2025-03-09 14:11:57'),
(14, 16, 'Pending', 'Cancelled', 23, '2025-03-09 14:12:00'),
(15, 16, 'Cancelled', 'Pending', 23, '2025-03-09 14:12:03'),
(16, 17, 'Pending', 'Cancelled', 23, '2025-03-09 14:13:24'),
(17, 17, 'Cancelled', 'Pending', 23, '2025-03-09 14:13:28'),
(18, 18, 'Cancelled', 'Pending', 23, '2025-03-09 14:13:31'),
(19, 10, 'Cancelled', 'Pending', 23, '2025-03-09 14:19:25'),
(20, 10, 'Pending', 'Shipped', 23, '2025-03-09 14:19:31'),
(21, 10, 'Shipped', 'Completed', 23, '2025-03-09 14:19:34'),
(22, 10, 'Completed', 'Pending', 23, '2025-03-09 14:19:38'),
(23, 10, 'Cancelled', 'Pending', 23, '2025-03-09 14:24:49'),
(24, 11, 'Cancelled', 'Pending', 23, '2025-03-09 14:24:51'),
(25, 10, 'Pending', 'Shipped', 23, '2025-03-09 14:35:35'),
(26, 10, 'Shipped', 'Pending', 23, '2025-03-09 14:35:38'),
(27, 10, 'Pending', 'Completed', 23, '2025-03-09 14:35:47'),
(28, 10, 'Completed', 'Pending', 23, '2025-03-09 14:35:56'),
(29, 10, 'Cancelled', 'Pending', 23, '2025-03-09 14:42:33');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `email`) VALUES
(23, 'admin', '$2y$10$47grs/jgDHneRz4XlthGOu159nTqAw.6tlGedtT2HP2OgZ7624Iju', 'admin', 'admin@admin.com'),
(24, 'user', '$2y$10$.orfgJo1bkZ2.lIa1DreSuk8nPDTcddKa6.cVsvd40BJ/Zy1hMesa', 'user', 'user@user.com'),
(27, 'test', '$2y$10$2uehsTa3zNFt5wX.PtS2du4hI0vCBkDpHs8cUv3UJWsmS4GDQAfly', 'user', 'test@test.com'),
(28, 'test2', '$2y$10$2s/E/MOq/MaCjh7fHnjdIeQIteDfrRCkjLVtkubErpqeAVzrijWp2', 'user', 'test2@test.com'),
(29, 'test3', '$2y$10$9O26tnyozYBwxtl4.oMyfuiwgr2qg3zRRCNzPTOhDW1JMNVBpHYeS', 'user', 'test3@test.com'),
(30, 'test4', '$2y$10$AzOh8oEY6fkcjf2drXwxreljUmlT0Jin1qqDRP3O6Ux.NPr4dWfay', 'user', 'test4@test.com'),
(31, 'test5', '$2y$10$oTC98T393Puj3lb5vZqqr.I1b.VzSRa6vnG1XvTlDjk0.FTFCuqly', 'user', 'test5@test.com'),
(32, 'test6', '$2y$10$WnLRNiJ88CQO/CxODTpBn.djRCiIIEY2Zn6B/xy1JyVX3eaevA1Va', 'user', 'test6@test.com'),
(33, 'test7', '$2y$10$SlOp981/Ky4K.7iHRE4bt.axqGYBxkjk52DOiTcr3Z0oJ602Uhr36', 'user', 'test7@test.com'),
(34, 'test8', '$2y$10$fXzwpAG3e4.GZRba4tA8CObxJyyDZ85jbTZvhzkzYUC3hqXtE8h8u', 'user', 'test8@test.com'),
(35, 'test9', '$2y$10$dKQ6u2FxKWtUOpgAegSFnu1S5VkZf81Lil7d/42HiigaxtwI/dRYC', 'user', 'test9@test.com');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fish_name` (`fish_name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `fish_id` (`product_id`);

--
-- Indexes for table `order_history`
--
ALTER TABLE `order_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `changed_by` (`changed_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `order_history`
--
ALTER TABLE `order_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_history`
--
ALTER TABLE `order_history`
  ADD CONSTRAINT `order_history_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_history_ibfk_2` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

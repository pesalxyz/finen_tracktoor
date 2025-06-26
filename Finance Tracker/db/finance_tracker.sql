-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 20, 2025 at 03:29 PM
-- Server version: 10.4.17-MariaDB
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `finance_tracker`
--

-- --------------------------------------------------------

--
-- Table structure for table `daily_targets`
--

CREATE TABLE `daily_targets` (
  `id` int(11) NOT NULL,
  `target_amount` decimal(10,2) NOT NULL,
  `target_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `daily_targets`
--

INSERT INTO `daily_targets` (`id`, `target_amount`, `target_date`, `created_at`, `updated_at`) VALUES
(2, '150.00', '2025-06-20', '2025-06-20 09:53:12', '2025-06-20 09:53:12');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `type` enum('income','withdrawal','savings') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `type`, `amount`, `description`, `transaction_date`, `created_at`, `updated_at`) VALUES
(1, 'income', '1.00', '1', '2025-06-20', '2025-06-19 23:31:12', '2025-06-19 23:31:12'),
(2, 'savings', '30.00', '-', '2025-06-20', '2025-06-19 23:31:37', '2025-06-19 23:31:37'),
(3, 'income', '150.00', '-', '2025-06-20', '2025-06-19 23:37:18', '2025-06-19 23:37:18'),
(5, 'income', '1.00', '', '2025-06-20', '2025-06-19 23:52:26', '2025-06-19 23:52:26'),
(6, 'income', '2.00', '', '2025-06-20', '2025-06-19 23:52:29', '2025-06-19 23:52:29'),
(7, 'income', '3.00', '', '2025-06-20', '2025-06-19 23:52:33', '2025-06-19 23:52:33'),
(8, 'income', '1.00', '-', '2025-06-21', '2025-06-19 23:55:51', '2025-06-19 23:55:51'),
(9, 'income', '2.00', '-', '2025-06-21', '2025-06-19 23:56:01', '2025-06-19 23:56:01'),
(10, 'income', '50.00', '-', '2025-06-25', '2025-06-20 00:03:34', '2025-06-20 00:58:19'),
(11, 'income', '65.00', 'lp', '2025-06-21', '2025-06-20 09:54:28', '2025-06-20 09:54:28'),
(12, 'income', '-50.00', 'LP', '2025-06-20', '2025-06-20 09:58:53', '2025-06-20 09:58:53'),
(13, 'income', '50.00', 'LP', '2025-06-20', '2025-06-20 09:59:07', '2025-06-20 09:59:07');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `daily_targets`
--
ALTER TABLE `daily_targets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `target_date` (`target_date`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `daily_targets`
--
ALTER TABLE `daily_targets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

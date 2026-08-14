-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Aug 13, 2026 at 03:25 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `bank_mini`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `count_customers` (OUT `p_total` INT)   BEGIN
    SELECT COUNT(*)
    INTO p_total
    FROM customers;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `view_balance` (IN `p_account_id` INT, OUT `p_balance` DECIMAL(15,2))   BEGIN
    SELECT balance
    INTO p_balance
    FROM bank_accounts
    WHERE id = p_account_id;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `add_balance` (IN `p_account_id` BIGINT, IN `p_amount` DECIMAL(15,2))   BEGIN
    UPDATE bank_accounts
    SET balance = balance + p_amount
    WHERE id = p_account_id;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `customer_accounts`
--

CREATE TABLE `customer_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `username` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin` varchar(6) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Stored hashed',
  `first_login` tinyint(1) NOT NULL DEFAULT '1',
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `journal_entries`
--

CREATE TABLE `journal_entries` (
  `id` bigint UNSIGNED NOT NULL,
  `transaction_id` bigint UNSIGNED NOT NULL,
  `account_code` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '101=Cash, 201=Saving',
  `type` enum('debit','credit') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` bigint NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `journal_entries`
--

INSERT INTO `journal_entries` (`id`, `transaction_id`, `account_code`, `type`, `amount`, `created_at`) VALUES
(1, 1, '101', 'debit', 500000, '2026-08-12 03:01:24'),
(2, 1, '201', 'credit', 500000, '2026-08-12 03:01:24');

-- --------------------------------------------------------

--
-- Table structure for table `daily_reports`
--

CREATE TABLE `daily_reports` (
  `id` bigint UNSIGNED NOT NULL,
  `teller_id` bigint UNSIGNED NOT NULL,
  `supervisor_id` bigint UNSIGNED DEFAULT NULL,
  `report_date` date NOT NULL,
  `opening_cash` bigint NOT NULL DEFAULT '0',
  `total_deposit` bigint NOT NULL DEFAULT '0',
  `total_withdrawal` bigint NOT NULL DEFAULT '0',
  `closing_cash` bigint NOT NULL DEFAULT '0',
  `status` enum('draft','approved') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `daily_reports`
--

INSERT INTO `daily_reports` (`id`, `teller_id`, `supervisor_id`, `report_date`, `opening_cash`, `total_deposit`, `total_withdrawal`, `closing_cash`, `status`, `approved_at`, `created_at`) VALUES
(1, 2, 4, '2026-08-12', 1000000, 900000, 0, 1900000, 'approved', '2026-08-12 03:01:24', '2026-08-12 03:01:24'),
(2, 3, NULL, '2026-08-12', 500000, 750000, 150000, 1100000, 'draft', NULL, '2026-08-12 03:01:24');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` bigint UNSIGNED NOT NULL,
  `nis` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `class` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `nis`, `name`, `class`, `phone`, `created_at`, `updated_at`) VALUES
(1, '2024001', 'Ahmad Fauzi', 'XII DKV', '081234567890', '2026-08-12 03:01:24', '2026-08-13 00:40:30'),
(2, '2024002', 'Siti Aisyah', 'X AKL', '081234567891', '2026-08-12 03:01:24', '2026-08-13 00:40:30'),
(3, '2024003', 'Budi Santoso', 'X TKJ', '081234567892', '2026-08-12 03:01:24', '2026-08-13 00:40:30'),
(4, '2024004', 'Dewi Lestari', 'XII AKL', '081234567893', '2026-08-12 03:01:24', '2026-08-13 00:40:30'),
(5, '2024005', 'Rizky Pratama', 'XI BR', '081234567894', '2026-08-12 03:01:24', '2026-08-13 00:40:30');

-- --------------------------------------------------------

--
-- Table structure for table `withdrawal_requests`
--

CREATE TABLE `withdrawal_requests` (
  `id` bigint UNSIGNED NOT NULL,
  `bank_account_id` bigint UNSIGNED NOT NULL,
  `teller_id` bigint UNSIGNED NOT NULL,
  `amount` bigint NOT NULL,
  `status` enum('waiting','approved','rejected','expired') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'waiting',
  `expires_at` timestamp NULL DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `withdrawal_requests`
--

INSERT INTO `withdrawal_requests` (`id`, `bank_account_id`, `teller_id`, `amount`, `status`, `expires_at`, `approved_at`, `created_at`) VALUES
(1, 1, 2, 100000, 'approved', '2026-08-12 03:06:24', '2026-08-12 03:01:24', '2026-08-12 03:01:24'),
(2, 2, 3, 50000, 'approved', '2026-08-12 03:06:24', '2026-08-12 03:01:24', '2026-08-12 03:01:24'),
(3, 3, 2, 25000, 'waiting', '2026-08-12 03:06:24', NULL, '2026-08-12 03:01:24'),
(4, 4, 3, 200000, 'expired', '2026-08-12 02:01:24', NULL, '2026-08-12 03:01:24');

-- --------------------------------------------------------

--
-- Table structure for table `bank_accounts`
--

CREATE TABLE `bank_accounts` (
  `id` bigint UNSIGNED NOT NULL,
  `customer_id` bigint UNSIGNED NOT NULL,
  `account_number` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `balance` bigint NOT NULL DEFAULT '0',
  `qr_code` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `bank_accounts`
--

INSERT INTO `bank_accounts` (`id`, `customer_id`, `account_number`, `balance`, `qr_code`, `created_at`, `updated_at`) VALUES
(1, 1, 'REK-2024001', 562000, 'QR-REK-2024001', '2026-08-12 03:01:24', '2026-08-12 03:21:22'),
(2, 2, 'REK-2024002', 272000, 'QR-REK-2024002', '2026-08-12 03:01:24', '2026-08-12 03:38:15'),
(3, 3, 'REK-2024003', 110000, 'QR-REK-2024003', '2026-08-12 03:01:24', '2026-08-12 03:39:22'),
(4, 4, 'REK-2024004', 750000, 'QR-REK-2024004', '2026-08-12 03:01:24', '2026-08-12 03:01:24'),
(5, 5, 'REK-2024005', 0, 'QR-REK-2024005', '2026-08-12 03:01:24', '2026-08-12 03:01:24');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` bigint UNSIGNED NOT NULL,
  `bank_account_id` bigint UNSIGNED NOT NULL,
  `teller_id` bigint UNSIGNED NOT NULL,
  `type` enum('deposit','withdrawal') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `bank_account_id`, `teller_id`, `type`, `amount`, `created_at`) VALUES
(1, 1, 2, 'deposit', 500000.00, '2026-08-12 03:01:24'),
(2, 2, 2, 'deposit', 300000.00, '2026-08-12 03:01:24'),
(3, 2, 3, 'withdrawal', 50000.00, '2026-08-12 03:01:24'),
(4, 3, 2, 'deposit', 100000.00, '2026-08-12 03:01:24'),
(5, 4, 3, 'deposit', 750000.00, '2026-08-12 03:01:24'),
(6, 1, 3, 'withdrawal', 100000.00, '2026-08-12 03:01:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint UNSIGNED NOT NULL,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `username` varchar(25) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('administrator','teller','supervisor') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `role`, `status`, `created_at`, `updated_at`) VALUES
(2, 'Teller Satu', 'teller1', 'sigmakabeh', 'teller', 'active', '2026-08-12 03:01:24', '2026-08-12 03:53:48'),
(3, 'Teller Dua', 'teller2', 'sigma', 'teller', 'active', '2026-08-12 03:01:24', '2026-08-12 03:53:48'),
(4, 'Supervisor Satu', 'supervisor1', 'sigma', 'supervisor', 'active', '2026-08-12 03:01:24', '2026-08-12 03:53:48'),
(5, 'afnaan', 'admin', 'admin123', 'administrator', 'active', '2026-08-13 01:26:40', '2026-08-13 01:26:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customer_accounts_username` (`username`),
  ADD UNIQUE KEY `uq_customer_accounts_customer_id` (`customer_id`);

--
-- Indexes for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_journal_entries_transaction` (`transaction_id`),
  ADD KEY `idx_journal_entries_account_code` (`account_code`);

--
-- Indexes for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_daily_reports_teller_date` (`teller_id`, `report_date`),
  ADD KEY `idx_daily_reports_supervisor` (`supervisor_id`),
  ADD KEY `idx_daily_reports_report_date` (`report_date`),
  ADD KEY `idx_daily_reports_status` (`status`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_customers_nis` (`nis`);

--
-- Indexes for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_withdrawal_requests_bank_account` (`bank_account_id`),
  ADD KEY `idx_withdrawal_requests_teller` (`teller_id`),
  ADD KEY `idx_withdrawal_requests_status` (`status`);

--
-- Indexes for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_bank_accounts_account_number` (`account_number`),
  ADD UNIQUE KEY `uq_bank_accounts_customer_id` (`customer_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_transactions_bank_account` (`bank_account_id`),
  ADD KEY `idx_transactions_teller` (`teller_id`),
  ADD KEY `idx_transactions_type` (`type`),
  ADD KEY `idx_transactions_created_at` (`created_at`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_users_username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `journal_entries`
--
ALTER TABLE `journal_entries`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `daily_reports`
--
ALTER TABLE `daily_reports`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_accounts`
--
ALTER TABLE `customer_accounts`
  ADD CONSTRAINT `fk_customer_accounts_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `journal_entries`
--
ALTER TABLE `journal_entries`
  ADD CONSTRAINT `fk_journal_entries_transaction` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `daily_reports`
--
ALTER TABLE `daily_reports`
  ADD CONSTRAINT `fk_daily_reports_supervisor` FOREIGN KEY (`supervisor_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_daily_reports_teller` FOREIGN KEY (`teller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `withdrawal_requests`
--
ALTER TABLE `withdrawal_requests`
  ADD CONSTRAINT `fk_withdrawal_requests_bank_account` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_withdrawal_requests_teller` FOREIGN KEY (`teller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `bank_accounts`
--
ALTER TABLE `bank_accounts`
  ADD CONSTRAINT `fk_bank_accounts_customer` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_transactions_bank_account` FOREIGN KEY (`bank_account_id`) REFERENCES `bank_accounts` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_transactions_teller` FOREIGN KEY (`teller_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;   

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

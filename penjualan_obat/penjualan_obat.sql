-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 01, 2025 at 02:49 PM
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
-- Database: `penjualan_obat`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `activity_type` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `description`, `created_at`, `updated_at`) VALUES
(2, 'Vitamin ', 'Suplemen untuk memenuhi kebutuhan vitamin dan mineral tubuh. (Contoh obat: Vitamin C, Vitamin B Complex)', '2025-06-09 18:21:42', '2025-06-09 18:21:42'),
(3, 'Antiinflamasi ', 'Obat yang berfungsi mengurangi peradangan. (Contoh obat: Natrium Diklofenak)', '2025-06-09 18:22:13', '2025-06-09 18:22:13'),
(4, 'Antihistamin', 'melawan reaksi alergi', '2025-06-09 18:48:48', '2025-06-09 18:48:48'),
(5, 'Analgesik', 'untuk meredakan nyeri', '2025-06-09 18:49:13', '2025-06-09 18:49:13'),
(6, 'Obat Batuk', 'untuk meredakan batuk', '2025-06-09 18:49:49', '2025-06-09 18:49:49'),
(7, 'Antibiotik', 'infeksi bakteri', '2025-06-09 18:50:46', '2025-06-09 18:50:46'),
(8, 'Obat Pencahar', 'obat yang digunakan untuk mengatasi sembelit atau sulit buang air besar', '2025-06-11 08:13:59', '2025-06-11 08:13:59'),
(9, 'Antidepresan      ', 'Mengobati gangguan depresi dan kecemasan.', '2025-06-11 08:15:38', '2025-06-11 08:15:38'),
(10, 'Antihipertensi', 'Menurunkan tekanan darah tinggi.', '2025-06-11 08:16:01', '2025-06-11 08:16:01'),
(11, 'Obat Lambung', '', '2025-06-29 08:24:12', '2025-06-29 08:24:12'),
(12, 'Obat Gatal/Alergi', '', '2025-06-29 08:26:52', '2025-06-29 08:26:52'),
(13, 'Penurun Gula', '', '2025-06-29 08:28:10', '2025-06-29 08:28:10');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(255) NOT NULL,
  `category_id` int(11) NOT NULL,
  `product_price` decimal(10,2) DEFAULT NULL,
  `product_stock` int(11) DEFAULT NULL,
  `unit_id` int(11) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `updated_by` int(11) DEFAULT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `selling_price` decimal(10,2) NOT NULL,
  `min_stock` int(11) DEFAULT 10,
  `expiration_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `product_purchase_price` decimal(10,2) DEFAULT 0.00,
  `product_code` varchar(20) DEFAULT NULL,
  `product_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `product_name`, `category_id`, `product_price`, `product_stock`, `unit_id`, `created_by`, `is_active`, `updated_by`, `supplier_id`, `selling_price`, `min_stock`, `expiration_date`, `created_at`, `updated_at`, `image`, `product_purchase_price`, `product_code`, `product_description`) VALUES
(14, 'Paracetamol', 5, 10000.00, 42, 2, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:18:59', '2025-07-01 12:18:31', NULL, 7000.00, '01', 'meredakan nyeri ringan hingga sedang'),
(15, 'Ibuprofen', 3, 12000.00, 47, 2, 4, 0, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:21:03', '2025-07-01 12:17:33', NULL, 8000.00, '02', 'meredakan nyeri, mengurangi peradangan'),
(16, 'Amoxcilin', 7, 10000.00, 68, 2, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:22:09', '2025-06-29 09:05:14', NULL, 6000.00, '03', 'mengobati infeksi'),
(17, 'Omeprazole', 11, 23000.00, 14, 4, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:23:56', '2025-07-01 12:18:31', NULL, 19000.00, '04', 'obat lambung'),
(18, 'Loratadine', 4, 15000.00, 65, 2, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:25:18', '2025-06-29 09:05:45', NULL, 9000.00, '05', 'meredakan gejala alergi'),
(19, 'Mycoral', 12, 7000.00, 28, 1, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:26:37', '2025-06-29 09:05:14', NULL, 5000.00, '06', 'obat gatal/alergi'),
(20, 'Metformin', 13, 5000.00, 30, 2, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:30:06', '2025-07-01 12:20:49', NULL, 3000.00, '07', 'penurun gula darah'),
(21, 'Sanmol Syrup', 5, 32000.00, 14, 4, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:33:16', '2025-06-29 09:03:21', NULL, 27000.00, '08', 'pereda nyeri'),
(22, 'Proris Suspensi', 3, 33000.00, 20, 4, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:34:34', '2025-06-29 08:34:34', NULL, 25000.00, '09', ''),
(23, 'OBH Combi', 6, 18000.00, 34, 4, 4, 1, 4, NULL, 0.00, 10, NULL, '2025-06-29 08:35:31', '2025-06-29 09:05:45', NULL, 13000.00, '10', 'obat batuk');

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

CREATE TABLE `purchases` (
  `purchase_id` int(11) NOT NULL,
  `purchase_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_cost` decimal(10,2) NOT NULL,
  `supplier_id` int(11) NOT NULL,
  `invoice_number` varchar(100) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`purchase_id`, `purchase_date`, `total_cost`, `supplier_id`, `invoice_number`, `user_id`, `notes`, `created_at`, `updated_at`) VALUES
(2, '2025-06-29 17:05:13', 170000.00, 3, NULL, 4, NULL, '2025-06-29 17:05:13', '2025-06-29 17:05:13'),
(3, '2025-06-29 17:05:45', 480000.00, 2, NULL, 4, NULL, '2025-06-29 17:05:45', '2025-06-29 17:05:45'),
(4, '2025-07-01 20:20:49', 25000.00, 1, NULL, 4, NULL, '2025-07-01 20:20:49', '2025-07-01 20:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_items`
--

CREATE TABLE `purchase_items` (
  `purchase_item_id` int(11) NOT NULL,
  `purchase_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `purchase_price` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_items`
--

INSERT INTO `purchase_items` (`purchase_item_id`, `purchase_id`, `product_id`, `quantity`, `purchase_price`, `created_at`) VALUES
(3, 2, 19, 10, 7000.00, '2025-06-29 17:05:14'),
(4, 2, 16, 10, 10000.00, '2025-06-29 17:05:14'),
(5, 3, 23, 10, 18000.00, '2025-06-29 17:05:45'),
(6, 3, 18, 20, 15000.00, '2025-06-29 17:05:45'),
(7, 4, 20, 5, 5000.00, '2025-07-01 20:20:49');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `po_id` int(11) NOT NULL,
  `po_number` varchar(100) DEFAULT NULL,
  `supplier_id` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `expected_delivery_date` date DEFAULT NULL,
  `total_amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `status` enum('pending','ordered','received','canceled') DEFAULT 'pending',
  `user_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_details`
--

CREATE TABLE `purchase_order_details` (
  `pod_id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `received_quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL,
  `role_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`role_id`, `role_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Administrator', 'Hak akses penuh ke semua fitur sistem.', '2025-06-09 17:57:11', '2025-06-09 17:57:11');

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `movement_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `movement_type` enum('in','out','adjustment') NOT NULL,
  `quantity` int(11) NOT NULL,
  `current_stock_after_movement` int(11) NOT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `reference_table` varchar(50) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `movement_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `supplier_name` varchar(200) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `contact_person`, `phone_number`, `email`, `address`, `created_at`, `updated_at`) VALUES
(1, 'PT ABC Pharma', 'Fauzan Al-Ghifari', '0813-4676-7738', 'bangzhan2@gmail.com', 'Jl.Raya Farmasi No. 3, Wahau', '2025-06-09 18:35:57', '2025-06-09 18:35:57'),
(2, 'PT. Multi Sehat', 'Samhudi', '082120788617', 'samhudi73@gmail.com', 'Jl. Duren 2, Bontang', '2025-06-09 18:37:43', '2025-06-09 18:37:43'),
(3, 'PT. Semoga Sehat', 'Nada Shazqueena', '083529673475', NULL, 'Jl. Alhamdulillah, Jakarta', '2025-06-09 20:24:09', '2025-06-09 20:24:09');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `transaction_code` varchar(100) DEFAULT NULL,
  `transaction_date` datetime NOT NULL DEFAULT current_timestamp(),
  `total_amount` decimal(12,2) NOT NULL,
  `paid_amount` decimal(12,2) NOT NULL,
  `change_amount` decimal(12,2) NOT NULL,
  `user_id` int(11) NOT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `payment_method` enum('cash','card','transfer') DEFAULT 'cash',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `transaction_code`, `transaction_date`, `total_amount`, `paid_amount`, `change_amount`, `user_id`, `customer_name`, `payment_method`, `created_at`, `updated_at`) VALUES
(7, NULL, '2025-06-29 17:02:01', 264000.00, 0.00, 0.00, 4, 'Imaa', 'cash', '2025-06-29 09:02:01', '2025-06-29 09:02:01'),
(8, NULL, '2025-06-29 17:02:33', 300000.00, 0.00, 0.00, 4, 'Erlina', 'cash', '2025-06-29 09:02:33', '2025-06-29 09:02:33'),
(9, NULL, '2025-06-29 17:03:21', 336000.00, 0.00, 0.00, 4, 'Rosa', 'cash', '2025-06-29 09:03:21', '2025-06-29 09:03:21'),
(10, NULL, '2025-07-01 11:56:14', 69000.00, 0.00, 0.00, 4, 'Hida', 'cash', '2025-07-01 03:56:14', '2025-07-01 03:56:14'),
(11, NULL, '2025-07-01 20:18:31', 89000.00, 0.00, 0.00, 4, 'Hawa', 'cash', '2025-07-01 12:18:31', '2025-07-01 12:18:31');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_details`
--

CREATE TABLE `transaction_details` (
  `transaction_detail_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `transaction_item_id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price_at_sale` decimal(10,2) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`transaction_item_id`, `transaction_id`, `product_id`, `quantity`, `price_at_sale`, `created_at`) VALUES
(1, 1, 2, 3, 17000.00, '2025-06-11 01:06:39'),
(2, 1, 5, 4, 26000.00, '2025-06-11 01:06:39'),
(3, 2, 3, 3, 8000.00, '2025-06-11 15:09:36'),
(4, 2, 3, 5, 8000.00, '2025-06-11 15:09:36'),
(5, 3, 3, 7, 8000.00, '2025-06-11 16:07:43'),
(6, 4, 11, 14, 16000.00, '2025-06-11 18:28:45'),
(7, 5, 10, 5, 10000.00, '2025-06-12 17:14:26'),
(8, 5, 13, 2, 70000.00, '2025-06-12 17:14:26'),
(9, 6, 13, 2, 70000.00, '2025-06-19 21:07:18'),
(10, 7, 15, 3, 12000.00, '2025-06-29 17:02:01'),
(11, 7, 23, 6, 18000.00, '2025-06-29 17:02:01'),
(12, 7, 16, 12, 10000.00, '2025-06-29 17:02:01'),
(13, 8, 18, 15, 15000.00, '2025-06-29 17:02:33'),
(14, 8, 20, 15, 5000.00, '2025-06-29 17:02:33'),
(15, 9, 19, 12, 7000.00, '2025-06-29 17:03:21'),
(16, 9, 14, 6, 10000.00, '2025-06-29 17:03:21'),
(17, 9, 21, 6, 32000.00, '2025-06-29 17:03:21'),
(18, 10, 17, 3, 23000.00, '2025-07-01 11:56:14'),
(19, 11, 14, 2, 10000.00, '2025-07-01 20:18:31'),
(20, 11, 17, 3, 23000.00, '2025-07-01 20:18:31');

-- --------------------------------------------------------

--
-- Table structure for table `units`
--

CREATE TABLE `units` (
  `unit_id` int(11) NOT NULL,
  `unit_name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `units`
--

INSERT INTO `units` (`unit_id`, `unit_name`, `description`, `created_at`, `updated_at`) VALUES
(1, 'tablet ', 'Satuan per butir tablet', '2025-06-09 18:32:40', '2025-06-09 18:32:40'),
(2, 'strip', 'Satuan per strip (biasanya 10 tablet)', '2025-06-09 18:32:40', '2025-06-09 18:32:40'),
(3, 'box', 'Satuan per kotak besar\r\n', '2025-06-09 18:32:40', '2025-06-09 18:32:40'),
(4, 'botol', 'Satuan per botol cairan', '2025-06-09 18:32:40', '2025-06-09 18:32:40'),
(5, 'Bungkus', 'Satuan per bungkus', '2025-06-09 20:03:39', '2025-06-09 20:03:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(200) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `phone_number`, `address`, `role_id`, `is_active`, `created_at`, `updated_at`) VALUES
(4, 'NidaHidayati', '$2y$10$QvC.1aSEuLSACKd2f3Qcr.UGiN4STjZSbUJySBTbdgrqtwasXS/Oa', 'Administrator Utama', NULL, NULL, NULL, 1, NULL, '2025-06-09 18:00:05', '2025-06-29 08:36:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `fk_products_units` (`unit_id`),
  ADD KEY `fk_products_suppliers` (`supplier_id`),
  ADD KEY `fk_products_categories` (`category_id`);

--
-- Indexes for table `purchases`
--
ALTER TABLE `purchases`
  ADD PRIMARY KEY (`purchase_id`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD PRIMARY KEY (`purchase_item_id`),
  ADD KEY `purchase_id` (`purchase_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`po_id`),
  ADD UNIQUE KEY `po_number` (`po_number`),
  ADD KEY `supplier_id` (`supplier_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD PRIMARY KEY (`pod_id`),
  ADD UNIQUE KEY `po_id` (`po_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`role_id`),
  ADD UNIQUE KEY `role_name` (`role_name`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD UNIQUE KEY `transaction_code` (`transaction_code`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD PRIMARY KEY (`transaction_detail_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`transaction_item_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `units`
--
ALTER TABLE `units`
  ADD PRIMARY KEY (`unit_id`),
  ADD UNIQUE KEY `unit_name` (`unit_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `fk_users_roles` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `purchases`
--
ALTER TABLE `purchases`
  MODIFY `purchase_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_items`
--
ALTER TABLE `purchase_items`
  MODIFY `purchase_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `po_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  MODIFY `pod_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `role_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `transaction_details`
--
ALTER TABLE `transaction_details`
  MODIFY `transaction_detail_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `transaction_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `units`
--
ALTER TABLE `units`
  MODIFY `unit_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `fk_products_categories` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_suppliers` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_products_units` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`category_id`),
  ADD CONSTRAINT `products_ibfk_2` FOREIGN KEY (`unit_id`) REFERENCES `units` (`unit_id`),
  ADD CONSTRAINT `products_ibfk_3` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Constraints for table `purchases`
--
ALTER TABLE `purchases`
  ADD CONSTRAINT `purchases_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `purchases_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON UPDATE CASCADE;

--
-- Constraints for table `purchase_items`
--
ALTER TABLE `purchase_items`
  ADD CONSTRAINT `purchase_items_ibfk_1` FOREIGN KEY (`purchase_id`) REFERENCES `purchases` (`purchase_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `purchase_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `purchase_orders_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `purchase_order_details`
--
ALTER TABLE `purchase_order_details`
  ADD CONSTRAINT `purchase_order_details_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_orders` (`po_id`),
  ADD CONSTRAINT `purchase_order_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `stock_movements_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `transaction_details`
--
ALTER TABLE `transaction_details`
  ADD CONSTRAINT `transaction_details_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`),
  ADD CONSTRAINT `transaction_details_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_users_roles` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`role_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

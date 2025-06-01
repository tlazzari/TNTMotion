-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 10, 2024 at 05:53 AM
-- Server version: 10.6.19-MariaDB-cll-lve
-- PHP Version: 8.1.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `TNT_Db`
--

-- --------------------------------------------------------

--
-- Table structure for table `rfq_product`
--

CREATE TABLE `rfq_product` (
  `rfq_product_id` int(10) NOT NULL,
  `product_id` int(10) NOT NULL,
  `quantity` varchar(100) DEFAULT NULL,
  `specifications` varchar(300) DEFAULT NULL,
  `notes` varchar(300) DEFAULT NULL,
  `drawing_number` varchar(20) DEFAULT NULL,
  `target_price` varchar(10) DEFAULT NULL,
  `best_quotation` varchar(100) DEFAULT NULL,
  `backup_quotation` varchar(100) DEFAULT NULL,
  `winning_supplier_id` int(10) DEFAULT NULL,
  `backup_supplier_id` int(10) DEFAULT NULL,
  `last_update` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

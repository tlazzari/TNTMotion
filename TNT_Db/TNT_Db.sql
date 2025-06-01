-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 10, 2024 at 06:30 AM
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
-- Table structure for table `client`
--

CREATE TABLE `client` (
  `client_id` int(11) NOT NULL,
  `main_contact` int(11) DEFAULT NULL,
  `address` varchar(200) DEFAULT NULL,
  `vat_number` int(11) DEFAULT NULL,
  `source` int(11) DEFAULT NULL,
  `client_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `invoice_id` int(11) NOT NULL,
  `invoice_number` int(11) NOT NULL,
  `invoice_amount` float(10,2) NOT NULL,
  `vat_applicable` tinyint(1) DEFAULT NULL,
  `pro_forma` tinyint(1) DEFAULT NULL,
  `seta_company_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `invoice_date` date DEFAULT NULL,
  `invoice_description` varchar(250) DEFAULT NULL,
  `payment_terms` varchar(100) DEFAULT NULL,
  `vat_amount` float(10,2) DEFAULT NULL,
  `total` float(10,2) DEFAULT NULL,
  `Vat_percentage` int(11) DEFAULT NULL,
  `Expenses` float(10,2) DEFAULT NULL,
  `currency` varchar(45) DEFAULT NULL,
  `expenses_description` varchar(280) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`invoice_id`, `invoice_number`, `invoice_amount`, `vat_applicable`, `pro_forma`, `seta_company_id`, `client_id`, `invoice_date`, `invoice_description`, `payment_terms`, `vat_amount`, `total`, `Vat_percentage`, `Expenses`, `currency`, `expenses_description`) VALUES
(166, 26101, 5000.00, 0, 0, 358, 172, '2018-01-24', 'Servizi Mensili Apr-Ago2021- Gestione Servizio Clienti, studio delle strategie di marketing, studio strategia prezzi, filing approvazioni dei prodotti', NULL, 1100.00, 6100.00, 0, NULL, 'Eur', NULL),
(167, 26101, 5000.00, 0, 0, 358, 172, '2018-01-24', 'Servizi Mensili Apr-Ago2021- Gestione Servizio Clienti, studio delle strategie di marketing, studio strategia prezzi, filing approvazioni dei prodotti', NULL, 1100.00, 6100.00, 0, NULL, 'Eur', NULL),
(168, 26102, 7000.00, 0, 22, 358, 360, '2018-02-05', 'First payment according to the contract between Promeco\nS.p.A. and Seta Capital Srls on 5th May 2017', NULL, 1540.00, 8540.00, 0, NULL, 'Eur', NULL),
(169, 0, 10000.00, 1, 22, 359, 373, '2018-04-08', 'First Payment according to the engagement Letter dated 24-01-2018', NULL, 2200.00, 12200.00, 1, NULL, 'Eur', 'At 10 days from invoice receipt'),
(170, 0, 9000.00, 1, 22, 358, 554, '2018-04-08', 'First Payment according to the contract dated 28-03-2018', NULL, 1980.00, 10980.00, 1, NULL, 'Eur', 'At Invoice Receipt'),
(171, 26103, 10000.00, 1, 22, 358, 373, '2018-04-24', 'First  Payment according to the engagement Letter dated 24-01-2018', NULL, 2200.00, 12200.00, 0, NULL, 'Eur', 'At 10 days from invoice receipt'),
(172, 26104, 9000.00, 1, 22, 358, 554, '2018-05-21', 'First Payment according to the contract date ', NULL, 1980.00, 10980.00, 0, NULL, 'Eur', 'At Invoice Receipt'),
(173, 26105, 10000.00, 1, 22, 358, 373, '2018-05-21', 'Second  Payment according to the engagement Letter dated 24-01-2018', NULL, 2200.00, 12200.00, 0, NULL, 'Eur', 'At 10 days form invoice receipt'),
(174, 0, 7500.00, 0, NULL, 358, 727, '2018-06-01', 'Second Part of the Payment for 2017 according to the contract Signed on the ', NULL, NULL, 7500.00, 1, NULL, 'Eur', 'At Invoice Receipt'),
(175, 26106, 10000.00, 1, 22, 358, 373, '2018-06-21', 'Third  Payment according to the engagement Letter dated 24-01-2018', NULL, 2200.00, 12200.00, 0, NULL, 'Eur', 'At 10 Days invoice receipt'),
(176, 26107, 14386.00, 1, 22, 358, 373, '2018-06-22', 'Fourth  Payment according to the engagement Letter dated 24-01-2018 and Travel Expenses ', NULL, 3195.92, 17550.92, 0, NULL, 'Eur', 'At 10 Days Invoice Receipt'),
(177, 26108, 10000.00, 1, 22, 358, 373, '2018-09-12', 'Fourth  Payment according to the engagement Letter dated 24-01-2018 and Expenses ', '4315.13', 3149.33, 17464.46, 0, NULL, 'Eur', 'At 10 days from invoice receipt'),
(178, 26101, 10000.00, 0, NULL, 359, 335, '2018-09-29', 'Payment upon the Target accepting the a Letter of Interest or equivalent according to the engagement Letter Dated 15 Dec 2017', '1052.74', NULL, 11052.74, 0, NULL, 'Eur', 'At Invoice Receipt'),
(179, 26109, 641.28, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 641.28, 0, NULL, 'Eur', 'At Invoice Receipt'),
(180, 26110, 644.54, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 644.54, 0, NULL, 'Eur', 'At invoice Receipt'),
(181, 26111, 641.75, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 641.75, 0, NULL, 'Eur', 'At Invoice Receipt'),
(182, 26112, 670.07, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 670.07, 0, NULL, 'Eur', 'At Invoice Receipt'),
(183, 26113, 681.37, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 681.37, 0, NULL, 'Eur', 'At Invoice Receipt'),
(184, 26114, 680.10, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 680.10, 0, NULL, 'Eur', 'At Invoice Receipt'),
(185, 26115, 694.75, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 694.75, 0, NULL, 'Eur', 'At Invoice Receipt'),
(186, 26116, 675.73, 0, NULL, 358, 792, '2018-09-29', 'Expenses reimbursement as per contract signed november 24 2016', NULL, NULL, 675.73, 0, NULL, 'Eur', 'At invoice Receipt'),
(187, 26102, 2013.91, 0, NULL, 359, 793, '2018-11-27', '12 Hrs of consulting service regarding Chinese Market for Coal', NULL, NULL, 2013.91, 0, NULL, 'Eur', 'At invoice Receipt'),
(188, 27101, 10000.00, 0, NULL, 359, 335, '2019-01-07', 'Payment upon the end of the Due Diligence according to the engagement Letter Dated 15 Dec 2017', NULL, NULL, 10000.00, 0, NULL, 'Eur', 'At invoice Receipt'),
(189, 27102, 25000.00, 0, NULL, 359, 204, '2019-01-07', 'Payment At the Signature of the Engagment Letter according to the Contract dated 28-11-2018', NULL, NULL, 25000.00, 0, NULL, 'Eur', 'At Invoice Receipt'),
(190, 26117, 682.07, 0, NULL, 358, 792, '2018-10-08', 'Payment At the Signature of the Engagment Letter according to the Contract dated 28-11-2018', NULL, NULL, 682.07, 0, NULL, 'Eur', 'At Invoice Receipt'),
(191, 26118, 693.60, 0, NULL, 358, 792, '2018-11-23', 'Payment upon the end of the Signing of the agreement according to the engagement Letter Dated 15 Dec 2017', NULL, NULL, 693.60, 0, NULL, 'Eur', 'At Invoice Receipt'),
(192, 27102, 20000.00, 0, NULL, 359, 335, '2019-01-28', 'Payment upon signing of the contract', NULL, NULL, 20000.00, 0, NULL, 'Eur', 'At Invoice receipt'),
(193, 29101, 694.00, 0, NULL, 358, 792, '2019-01-22', 'FInal payment of the contract', NULL, NULL, 694.00, 0, NULL, 'Eur', 'At invoice Receipt'),
(194, 30101, 58016.00, 1, NULL, 4592, 4591, '2019-05-29', '2018 Xintai Italia fees', NULL, 1741.00, 59757.00, 0, NULL, 'CNY', 'Payment upon receipt of invoice'),
(195, 27103, 105000.00, 0, NULL, 359, 335, '2019-05-30', 'Carioca project success fee and related services', NULL, NULL, 105000.00, 0, NULL, 'Eur', 'Payment upon receipt of invoice'),
(196, 29103, 0.00, 0, NULL, 358, 373, '2019-06-14', 'Expenses Related to Price negotiation in March 2019, Frankfurt SPA Negotiatio December 2018 and Qijing Visit ', '6886.17', 1514.96, 8401.13, 0, NULL, 'Eur', 'Payment upon receipt of invoice'),
(197, 27104, 22000.00, 0, NULL, 359, 358, '2019-02-23', 'Services related to Tenova Deal and negotiation first insallment', '2000.00', NULL, 24000.00, 0, NULL, 'Eur', 'Payment upon receipt of Invoice'),
(198, 27109, 20000.00, 0, NULL, 359, 358, '2019-03-24', 'Services of Corporate governance Administration and comunication', '4000.00', NULL, 24000.00, 0, NULL, 'Eur', 'Payment upon receipt of Invoice'),
(199, 27105, 3000.00, 0, NULL, 359, 5001, '2019-11-27', 'First Milestone of the Mandate', NULL, NULL, 3000.00, 0, NULL, 'Eur', 'Payment upon receipt of Invoice'),
(200, 27106, 4500.00, NULL, NULL, 359, 4590, '2019-11-27', 'First Milestone of the Mandate', NULL, NULL, 4500.00, NULL, NULL, 'Eur', 'Payment upon receipt of Invoice'),
(201, 27107, 4500.00, NULL, NULL, 359, 4588, '2019-11-27', 'First Milestone of the Mandate', NULL, NULL, 4500.00, NULL, NULL, 'Eur', 'Payment upon receipt of Invoice'),
(202, 27110, 25000.00, 0, NULL, 359, 358, '2019-07-19', 'Services related to Tenova Deal and negotiation Second Installment', NULL, NULL, 25000.00, 0, NULL, 'Eur', 'Payment upon receipt of Invoice'),
(203, 27111, 30000.00, 0, NULL, 359, 358, '2019-09-19', 'Brand Royalties', NULL, NULL, 30000.00, 0, NULL, 'Eur', 'Payment upon receipt of Invoice'),
(204, 27001, 0.00, 0, NULL, 359, 5001, '2020-01-09', NULL, '774.58', NULL, 774.58, 0, NULL, 'Eur', NULL),
(205, 27002, 0.00, 0, 0, 359, 5034, '2020-01-30', '', '447.00', 0.00, 447.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(206, 27003, 871.01, 0, 0, 359, 5158, '2020-03-22', '200 Protective masks FFP2', '0.00', 0.00, 871.01, NULL, 0.00, 'Eur', 'At Goods Received'),
(207, 28001, 100.00, 0, 0, 358, 5167, '2020-04-03', 'Proforma - 200 Mascherine per Donazione', '0.00', 0.00, 100.00, NULL, 0.00, 'USD', ''),
(208, 28002, 100.00, 0, 0, 358, 5167, '2020-04-03', 'Proforma - 200 Mascherine per Donazione', '0.00', 0.00, 100.00, NULL, 0.00, 'USD', ''),
(209, 28003, 100.00, 0, 0, 358, 5167, '2020-04-03', 'Proforma - 200 Mascherine per Donazione', '0.00', 0.00, 100.00, NULL, 0.00, 'USD', ''),
(210, 28004, 100.00, 0, 0, 358, 5167, '2020-04-03', 'Proforma - 200 Mascherine per Donazione', '0.00', 0.00, 100.00, NULL, 0.00, 'USD', ''),
(211, 28005, 100.00, 0, 0, 358, 5167, '2020-04-03', 'Proforma - 200 Mascherine per Donazione', '0.00', 0.00, 100.00, NULL, 0.00, 'USD', ''),
(212, 27004, 3850.00, 0, 0, 359, 5169, '2020-04-06', '1000 Mascherine FFP2', '0.00', 0.00, 3850.00, NULL, 0.00, 'Eur', 'At goods Receipt'),
(213, 27005, 192.50, 0, 0, 359, 5170, '2020-04-07', '50xFace Masks FFP2', '0.00', 0.00, 192.50, NULL, 0.00, 'Eur', 'At Goods Receipt'),
(214, 27006, 17800.00, 0, 0, 359, 5267, '2020-04-21', '24,000 Mascherine Chirurgiche impacchettate singolarmente con consegna DDP.', '0.00', 0.00, 17800.00, NULL, 0.00, 'Eur', 'CFA Cina'),
(215, 27007, 3000.00, 0, 0, 359, 5163, '2020-05-06', 'Sales agency services as per the contract dated 26-03-2020', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', ''),
(216, 2708, 600.00, 0, 0, 359, 6240, '2020-05-27', '1000 mascherine chirurgiche per uso civile', '0.00', 0.00, 600.00, NULL, 0.00, 'Eur', 'At invoice receipt '),
(217, 28004, 5000.00, 0, 0, 358, 5157, '2020-05-29', 'Long List Report according to the mandate signed on the 23-03-2020', '0.00', 0.00, 5000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(218, 30001, 30000.00, 0, 0, 4592, 358, '2020-03-20', 'Services related to the Tenova SpA service for 2019', '0.00', 0.00, 30000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(219, 30002, 20000.00, 0, 0, 4592, 6129, '2020-06-02', 'First Milestone - Mandate dated 13 January 2020', '0.00', 0.00, 20000.00, NULL, 0.00, 'CNY', 'At Invoice Recipt'),
(220, 27011, 680.00, 0, 0, 359, 5170, '2020-06-04', '200 Masks FFP2 - CE Marked', '0.00', 0.00, 680.00, NULL, 0.00, 'Eur', 'at Invoice receipt'),
(221, 27009, 4200.00, 0, 0, 359, 5159, '2020-06-04', '1000 Maschere KN95 per Uso Civile', '0.00', 0.00, 4200.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(222, 27010, 3000.00, 0, 0, 359, 5163, '2020-06-04', 'Sales agency services as per the contract dated 26-03-2020', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(223, 30003, 23803.00, 0, 0, 4592, 6414, '2020-06-08', 'First Milestone as per the contract signed on the 19 Nov 2019', '0.00', 0.00, 23803.00, NULL, 0.00, 'CNY', 'At invoice Receipt'),
(224, 28005, 1050.00, 0, 0, 358, 5170, '2020-06-25', '700 FFP2 - CE Certified', '0.00', 0.00, 1050.00, NULL, 0.00, 'Eur', 'At invoice Receipt'),
(225, 28006, 1520.00, 0, 0, 358, 5170, '2020-06-25', '4000 Surgical Mask - For Non Medical use', '0.00', 0.00, 1520.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(226, 27012, 2156.00, 0, 0, 359, 6422, '2020-07-09', '980 x FFP2 CE Certified', '0.00', 0.00, 2156.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(227, 28007, 822.50, 0, 0, 358, 6423, '2020-07-10', '2350 x Surgical Masks, non medical Use', '0.00', 0.00, 822.50, NULL, 0.00, 'Eur', 'At invoice receipt'),
(228, 27013, 3000.00, 0, 0, 359, 5163, '2020-07-10', 'Sales agency services as per the contract dated 26-03-2020', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(229, 28008, 50000.00, 1, 22, 358, 373, '2020-07-17', 'Earn Out Payment as per our contract with Tenova SpA', '0.00', 11000.00, 61000.00, NULL, 0.00, 'Eur', 'at Invoice Receipt'),
(230, 28009, 5000.00, 0, 0, 358, 6457, '2020-07-23', 'First Milestone as per the contract signed on the 23 Jan 2020', '0.00', 0.00, 5000.00, NULL, 0.00, 'Eur', 'Upon receipt of invoice'),
(231, 28010, 9500.00, 0, 0, 358, 5157, '2020-08-06', 'Payment for Market Report and Short List Report per Contract dated 23-03-2020', '0.00', 0.00, 9500.00, NULL, 0.00, 'Eur', 'Payment upon invoice'),
(232, 27015, 3000.00, 0, 0, 359, 5163, '2020-08-10', 'Sales agency services as per the contract dated 26-03-2020', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(233, 28011, 2550.00, 0, 0, 358, 5159, '2020-08-10', '600 x FFP2 Certificate 3 Eur\r\n500 x KN95 per uso civile 1.5 Eur', '0.00', 0.00, 2550.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(234, 30004, 7969.39, 1, 5, 4592, 727, '2020-08-13', 'SUPPLEMENTARY AGREEMENT TO THE MANDATE TO SETA CAPITAL dated 13-08-2020', '0.00', 398.47, 8367.86, NULL, 0.00, 'CNY', 'At invoice receipt'),
(235, 30005, 90000.00, 0, 0, 4592, 6468, '2020-08-24', '90% of Quarter 1 Service Fee per contract dated 1 August 2020', '0.00', 0.00, 90000.00, NULL, 0.00, 'CNY', 'At Invoice Receipt'),
(236, 30006, 5000.00, 1, 3, 4592, 6457, '2020-08-25', 'First Milestone as per the contract signed on the 23 Jan 2020', '0.00', 150.00, 5150.00, NULL, 0.00, 'Eur', 'Upon receipt'),
(237, 27016, 3000.00, 0, 0, 359, 5163, '2020-09-18', 'Sales agency services as per the contract dated 26-03-2020', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(238, 27017, 3100.00, 0, 0, 359, 5170, '2020-09-24', '1520xSemi-masks FFP2 delivered to Wharehouse', '0.00', 0.00, 3100.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(239, 27018, 3000.00, 0, 0, 359, 5163, '2020-10-10', 'Sales agency services as per the contract dated 26-03-2020', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(240, 830007, 20000.00, 0, 3, 4592, 5168, '2020-10-10', 'Upon the Advisor completing the English pitch presentation of Pricetag and the Company list according to the requirements specified in the scope of work under Section 1', '0.00', 600.00, 20600.00, NULL, 0.00, 'CNY', ''),
(241, 30008, 206500.00, 1, 3, 4592, 6457, '2020-11-03', 'Due diligence agreement (signed 2020-08-31) fee of EUR 20000 upon completion of report;\r\nCompany Incorporation agreement (signed 2020-09-05) fee of EUR 6500 upon signing the mandate', '0.00', 6195.00, 212695.00, NULL, 0.00, 'CNY', 'Upon receipt of invoice'),
(242, 27019, 7500.00, 0, 0, 359, 5030, '2020-11-04', 'Upon client approval of the Long List of Potential targets in Europe according to the mandate signed on the 3rd November 2020', '0.00', 0.00, 7500.00, NULL, 0.00, 'Eur', 'At Milestone Achieved'),
(243, 30009, 100000.00, 0, 0, 4592, 6468, '2020-11-10', '10% of Quarter 1 Service Fee and 90% of Quarter 2 Service Fee', '0.00', 0.00, 100000.00, NULL, 0.00, 'CNY', 'Upon receipt'),
(244, 30010, 681.50, 0, 0, 4592, 6468, '2020-11-10', 'Expenses of Ms. Tanya Wen during business trip to Shenzhen for 22-25 September 2020', '0.00', 0.00, 681.50, NULL, 0.00, 'CNY', 'upon receipt'),
(245, 27020, 1000.00, 0, 0, 359, 6422, '2020-11-16', '4000 Surgical masks', '0.00', 0.00, 1000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(246, 30010, 19417.48, 1, 3, 4592, 6129, '2020-12-21', 'Second payment per service contract dated 13/01/2020', '0.00', 582.52, 20000.00, NULL, 0.00, 'CNY', 'Upon receipt'),
(247, 27021, 38550.00, 0, 0, 359, 4592, '2020-03-30', '房租2020年1-3月', '0.00', 0.00, 38550.00, NULL, 0.00, 'CNY', 'n.a.'),
(248, 27022, 38550.00, 0, 0, 359, 4592, '2020-09-30', '房租2020年7-9月', '0.00', 0.00, 38550.00, NULL, 0.00, 'CNY', 'n.a.'),
(249, 27023, 38550.00, 0, 0, 359, 4592, '2020-12-26', '房租2020年4-6月', '0.00', 0.00, 38550.00, NULL, 0.00, 'CNY', 'n.a.'),
(250, 27024, 12500.00, 0, 0, 359, 4592, '2020-12-31', '赛嗒研发项目咨询服务2020年1月到12月', '0.00', 0.00, 12500.00, NULL, 0.00, 'Eur', ''),
(251, 27025, 4500.00, 0, 0, 359, 6874, '2020-12-31', 'Prima Parte dell\'incarico Datato 2 dicembre 2020', '0.00', 0.00, 4500.00, NULL, 0.00, 'Eur', ''),
(252, 27026, 6050.00, 0, 0, 359, 5522, '2020-12-31', 'Creation of the Cross-Border Ecommerce Platform and support for October, November and December 2020', '0.00', 0.00, 6050.00, NULL, 0.00, 'Eur', ''),
(253, 30011, 118636.00, 1, 3, 4592, 6414, '2021-01-04', '根据2019年11月19日双方协议中第二和第三个里程碑费用，共计欧元15000，根据账单日期汇率折合等值人民币', '0.00', 3559.08, 122195.08, NULL, 0.00, 'CNY', 'Upon receipt'),
(254, 27027, 3000.00, 0, 0, 359, 4592, '2020-12-31', '赛嗒研发项目咨询服务2020年1月到12月补充部分', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'Upon delivery'),
(255, 30012, 7956.00, 1, 3, 4592, 6879, '2021-01-06', '验收服务费第二笔，根据2020年12月11日签署的合同', '0.00', 238.68, 8194.68, NULL, 0.00, 'CNY', 'Upon receipt'),
(256, 30013, 25703.00, 1, 3, 4592, 6457, '2021-01-12', '根据2020-9-3的新设公司协议中第二部分收费，3250欧元，折合成人民币25703人民币', '0.00', 771.09, 26474.09, NULL, 0.00, 'CNY', 'Upon receipt'),
(257, 27001, 1950.00, 0, 0, 359, 6895, '2021-02-18', '50% of Wechat Store opening fee as per contract signed on 5th Feb 2021', '2500.00', 0.00, 4450.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(258, 30014, 99009.90, 1, 1, 4592, 6468, '2021-02-23', '10% of Quarter 2 Service Fee and 90% of Quarter 3 Service Fee', '0.00', 990.10, 100000.00, NULL, 0.00, 'CNY', 'Upon receipt'),
(259, 30015, 3096.00, 1, 1, 4592, 6457, '2021-03-17', '意大利认证工作律师费 Legalization support from Italian lawyer', '0.00', 30.96, 3126.96, NULL, 0.00, 'CNY', 'Upon receipt'),
(260, 27002, 15000.00, 0, 0, 359, 6996, '2021-03-23', '根据2019年11月19日双方协议中第二和第三个里程碑费用，共计欧元15000，折合等值澳币为23200。', '0.00', 0.00, 15000.00, NULL, 0.00, 'Eur', 'Upon receipt'),
(261, 27003, 500.00, 0, 0, 359, 5034, '2021-03-31', 'Fattura come da mandato firmato il 20022020', '154.00', 0.00, 654.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(262, 30016, 0.00, 0, 0, 4592, 6468, '2021-03-31', '', '1065.98', 0.00, 1065.98, NULL, 0.00, 'CNY', 'Upon receipt'),
(263, 30017, 0.00, 0, 0, 4592, 6468, '2021-03-31', '', '415.27', 0.00, 415.27, NULL, 0.00, 'CNY', 'Upon receipt'),
(264, 30018, 0.00, 0, 0, 4592, 6468, '2021-03-31', '', '356.88', 0.00, 356.88, NULL, 0.00, 'CNY', 'Upon receipt'),
(265, 27004, 1950.00, 0, 0, 359, 4463, '2021-04-26', 'First Milestone as per contract SIgned on the 11-03-2021', '0.00', 0.00, 1950.00, NULL, 0.00, 'Eur', 'At invoice Receipt'),
(266, 27005, 1950.00, 0, 0, 359, 6895, '2021-05-14', 'Second Milestone for Wechat store Opening and Testing ', '1904.00', 0.00, 3854.00, NULL, 0.00, 'Eur', 'At Invoice receipt'),
(267, 27006, 7500.00, 0, 0, 359, 5030, '2021-05-18', 'Upon Client approval of to the Client the Short List Report of potential targets. ', '0.00', 0.00, 7500.00, NULL, 0.00, 'Eur', 'At Invoice'),
(268, 30019, 10994.00, 0, 0, 4592, 7048, '2021-05-26', 'Coffee Delivery - November, December, January, February, March, April, May 2021', '0.00', 0.00, 10994.00, NULL, 0.00, 'CNY', 'At invoice receipt'),
(269, 30020, 99009.90, 1, 1, 4592, 6468, '2021-05-31', '10% of Quarter 3 Service Fee and 90% of Quarter 4 Service Fee', '0.00', 990.10, 100000.00, NULL, 0.00, 'CNY', 'Upon receipt'),
(270, 27007, 4500.00, 0, 0, 359, 6874, '2021-06-11', 'Seconda Parte dell\'incarico Datato 2 dicembre 2020', '0.00', 0.00, 4500.00, NULL, 0.00, 'Eur', 'At invoice reeipt'),
(271, 27008, 6200.00, 0, 0, 359, 6874, '2021-06-11', 'Costituzione della societ&agrave; Shenyang YIDA', '0.00', 0.00, 6200.00, NULL, 0.00, 'Eur', 'At Invoice receipt'),
(272, 30021, 99009.90, 1, 1, 4592, 6468, '2021-08-23', '10% of Quarter 4 Service Fee and 90% of Quarter 5 Service Fee', '0.00', 990.10, 100000.00, NULL, 0.00, 'CNY', 'Upon receipt'),
(273, 27009, 7000.00, 0, 0, 359, 6417, '2021-09-13', 'La Retainer Fee da pagarsi al momento della firma del Mandato datato 16 Luglio 2020.', '0.00', 0.00, 7000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(274, 28001, 5000.00, 1, 22, 358, 6440, '2021-09-13', 'Retainer Fee da pagarsi al momento della firma del contratto.', '0.00', 1100.00, 6100.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(275, 27010, 2400.00, 0, 0, 359, 4463, '2021-09-27', 'Servizi Mensili - AprAgo 2021-Gestione Servizio Clienti, studio delle strategie di marketing, studio strategia prezzi, filing approvazioni dei prodotti', '104.80', 0.00, 2504.80, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(276, 28002, 7500.00, 0, 0, 358, 5157, '2021-10-25', 'According to the Agreement dated 19-05-2021, the first milestone fee is due', '0.00', 0.00, 7500.00, NULL, 0.00, 'Eur', 'Upon receipt'),
(277, 27011, 3550.00, 0, 0, 359, 4463, '2021-11-01', 'Seconda Milestone della creazione delle piattaforma Wechat e servizio di gestione settembre-ottobre 2021', '0.00', 0.00, 3550.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(278, 27012, 8550.00, 0, 0, 359, 5522, '2021-11-01', 'Ecommerce Services January 2021- September 2021', '3581.90', 0.00, 12131.90, NULL, 0.00, 'Eur', 'At invoice receipt'),
(279, 27013, 3000.00, 0, 0, 359, 6895, '2021-11-01', 'Services As per our contract from June-October 2021', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(280, 27014, 0.00, 0, 0, 359, 6895, '2021-11-05', '', '10819.00', 0.00, 10819.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(281, 27015, 4000.00, 0, 0, 359, 7053, '2021-11-15', 'First Milestone of the contract &quot;Mandato Esplorativo&quot; signed on the 27 october 2021', '0.00', 0.00, 4000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(282, 30022, 99009.90, 1, 1, 4592, 6468, '2021-11-18', '10% of Quarter 5 Service Fee and 90% of Quarter 6 Service Fee', '0.00', 990.10, 100000.00, NULL, 0.00, 'CNY', 'Upon receipt'),
(283, 30023, 5380.00, 0, 0, 4592, 7048, '2021-12-03', '40kg of Beans + 6 boxes of Nespresso Capsules. Deliveries from June to November 2021', '0.00', 0.00, 5380.00, NULL, 0.00, 'CNY', 'At Invoice Receipt'),
(284, 27016, 800.00, 0, 0, 359, 4463, '2021-12-03', 'Servizio Gestione e assistenza piattaforme e mercato Cinese Novembre 2021', '0.00', 0.00, 800.00, NULL, 0.00, 'Eur', 'At invoice Receipt'),
(285, 27017, 1000.00, 0, 0, 359, 6895, '2021-12-03', 'Services As per our contract from November 2021', '0.00', 0.00, 1000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(286, 27018, 1000.00, 0, 0, 359, 6895, '2021-12-30', 'Services As per our contract for December 2021', '0.00', 0.00, 1000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(287, 27019, 5000.00, 0, 0, 359, 4463, '2021-12-30', 'Consulenza a supporto delle attività di onboarding della piattaforma', '0.00', 0.00, 5000.00, NULL, 0.00, 'Eur', 'At invoice Receipt'),
(288, 30024, 0.00, 0, 0, 4592, 6468, '2022-02-09', '', '1872.61', 0.00, 1872.61, NULL, 0.00, 'CNY', 'Upon receipt'),
(289, 27020, 2850.00, 0, 0, 359, 5522, '2022-02-24', 'Ecommerce Services October 2021- December 2021	', '-1967.00', 0.00, 883.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(290, 27021, 4000.00, 0, 0, 359, 7053, '2022-02-24', 'Second Milestone of the contract &quot;Mandato Esplorativo&quot; signed on the 27 october 2021', '0.00', 0.00, 4000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(291, 27022, 2000.00, 0, 0, 359, 6895, '2022-02-24', 'Services As per our contract for January and February 2022', '0.00', 0.00, 2000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(292, 27023, 10000.00, 0, 0, 359, 4463, '2022-02-24', 'Consulenza e supporto delle attività di onboarding della piattaforma', '0.00', 0.00, 10000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(293, 27024, 4410.00, 0, 0, 359, 4463, '2022-03-13', 'Annual Fee TMall International Store', '7250.00', 0.00, 11660.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(294, 27025, 4000.00, 0, 0, 359, 7053, '2022-03-15', 'Fattura di chiusura mandato esplorativo ', '0.00', 0.00, 4000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(295, 27026, 1000.00, 0, 0, 359, 7120, '2022-03-22', 'First Milestone relevant to the offer dated 15 Marzo 2022', '0.00', 0.00, 1000.00, NULL, 0.00, 'Eur', 'At Invoice receipt'),
(296, 30025, 10000.00, 0, 0, 4592, 6468, '2022-03-25', '10% of Quarter 6 Service Fee (Contract No. LE100820/2830/20)', '0.00', 0.00, 10000.00, NULL, 0.00, 'CNY', 'Upon receipt'),
(297, 30026, 17100.00, 0, 0, 4592, 6468, '2022-03-25', 'Bonus for Database, Meetings, Contracts and Major Projects (Contract No. LE100820/2830/20)', '0.00', 0.00, 17100.00, NULL, 0.00, 'Eur', 'Upon receipt'),
(298, 30027, 7500.00, 0, 0, 4592, 6468, '2022-03-25', 'Bonus for Leads (Contract No. LE100820/2830/20)', '0.00', 0.00, 7500.00, NULL, 0.00, 'Eur', 'Upon receipt'),
(299, 27027, 2000.00, 0, 0, 359, 6895, '2022-05-05', 'Services As per our contract for March and April 2022', '0.00', 0.00, 2000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(300, 27028, 10000.00, 0, 0, 359, 4463, '2022-05-05', 'Consulenza e supporto delle attivit&agrave; di onboarding della piattaforma - Marzo Aprile', '0.00', 0.00, 10000.00, NULL, 0.00, 'Eur', 'At Invoice Receipt'),
(301, 27029, 1000.00, 0, 0, 359, 6895, '2022-06-04', 'Services As per our contract for May 2022', '0.00', 0.00, 1000.00, NULL, 0.00, 'Eur', 'At Invoice receipt'),
(302, 27030, 5000.00, 0, 0, 359, 4463, '2022-06-04', 'Consulenza e supporto delle attivit&agrave; di onboarding della piattaforma - Maggio', '0.00', 0.00, 5000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(303, 27031, 3000.00, 0, 0, 359, 7121, '2022-07-29', 'Prima Milestone alla firma del contratto', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'At invoice receipts'),
(304, 29105, 100000.00, 1, 22, 358, 373, '2019-10-23', 'CONTRATTO DATATO 2901- PRIMO INCENTIVO', '0.00', 22000.00, 122000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(305, 27032, 2000.00, 0, 0, 359, 7121, '2022-10-24', 'Seconda Milestone all\'approvazione della ricerca di mercato', '0.00', 0.00, 2000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(306, 27033, 1500.00, 0, 0, 359, 7120, '2022-11-07', 'Payment of the second milestone according to the mandate signed on 23rd March 2022', '0.00', 0.00, 1500.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(307, 30026, 2000.00, 0, 0, 4592, 7124, '2022-11-20', '2,000 Euro alla firma del contratto, come da mandato firmato 31-10-2022', '0.00', 0.00, 2000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(308, 28003, 5000.00, 0, 0, 358, 7123, '2022-12-09', 'Monthly retainer for Mandate signed 25 Oct 2022 (1st month)', '0.00', 0.00, 5000.00, NULL, 0.00, 'Eur', 'Upon receipt of Invoice'),
(309, 27034, 7306.00, 0, 0, 359, 6895, '2022-12-21', 'Total operating expenses for 2022, excluding TMall annual fee', '0.00', 0.00, 7306.00, NULL, 0.00, 'Eur', 'Upon receipt of invoice'),
(310, 27035, 4074.00, 0, 0, 359, 6895, '2022-12-21', '2023 TMall annual fee', '0.00', 0.00, 4074.00, NULL, 0.00, 'Eur', 'Upon receipt of invoice'),
(311, 30027, 7000.00, 0, 0, 4592, 359, '2022-11-30', '电商运营服务费\r\neCommerce operations service fee', '0.00', 0.00, 7000.00, NULL, 0.00, 'Eur', 'Upon receipt'),
(312, 28003, 10000.00, 0, 0, 358, 7130, '2023-01-13', 'Monthly retainer for Mandate signed 25 Oct 2022 (1st and 2nd months)', '50.00', 0.00, 10050.00, NULL, 0.00, 'Eur', 'Upon receipt'),
(313, 30028, 3000.00, 0, 0, 4592, 7124, '2023-02-08', '3,000 Euro alla consegna dei documenti, come da mandato firmato 31-10-2022', '0.00', 0.00, 3000.00, NULL, 0.00, 'Eur', 'Na'),
(314, 30029, 0.00, 0, 0, 4592, 7131, '2023-02-27', '意大利Rimini出差报销', '4395.00', 0.00, 4395.00, NULL, 0.00, 'CNY', 'Upon delivery'),
(315, 27036, 6000.00, 0, 0, 359, 6895, '2023-03-01', 'Services Ecommerce management As per our contract for June-Dec 2022', '0.00', 0.00, 6000.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(316, 28015, 10000.00, 0, 0, 358, 7130, '2023-03-06', 'Monthly retainer for Mandate signed 25 Oct 2022 (3rd and 4th months)', '50.00', 0.00, 10050.00, NULL, 0.00, 'Eur', 'Upon delivery'),
(317, 30030, 5000.00, 0, 0, 4592, 359, '2023-02-28', '电商运营服务费', '0.00', 0.00, 5000.00, NULL, 0.00, 'Eur', 'Upon delivery'),
(318, 30031, 4600.00, 0, 0, 4592, 6874, '2023-03-31', 'Dichiarazione mensile tasse per YiDa presso l\'ufficio delle tasse. A partire da Marzo 2021 fino ad Marzo 2023 compresi.', '0.00', 0.00, 4600.00, NULL, 0.00, 'Eur', 'At invoice receipt'),
(319, 28016, 10050.00, 0, 0, 358, 7130, '2023-08-16', 'Monthly retainer for Mandate signed 25 Oct 2022 (5th and 6th months)', '0.00', 0.00, 10050.00, NULL, 0.00, 'Eur', 'Na'),
(320, 28017, 5000.00, 0, 0, 358, 7130, '2023-09-14', 'Due Diligence first payment per Mandate signed 25 Oct 2022 ', '681.00', 0.00, 5681.00, NULL, 0.00, 'Eur', 'Upon Receipt');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `product_name` varchar(300) NOT NULL,
  `drawing` varchar(100) DEFAULT NULL,
  `specifications` varchar(300) DEFAULT NULL,
  `notes` varchar(300) DEFAULT NULL,
  `product_family` varchar(100) DEFAULT NULL,
  `last_update` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_family`
--

CREATE TABLE `product_family` (
  `product_family_id` int(11) NOT NULL,
  `product_family_name` varchar(100) NOT NULL,
  `description` varchar(200) DEFAULT NULL,
  `notes` varchar(200) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `rfq_client`
--

CREATE TABLE `rfq_client` (
  `rfq_client_id` int(10) NOT NULL,
  `client_id` int(10) NOT NULL,
  `notes` varchar(300) DEFAULT NULL,
  `last_update` date NOT NULL DEFAULT current_timestamp(),
  `rfq_date` date NOT NULL DEFAULT current_timestamp(),
  `status` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(10) NOT NULL,
  `supplier_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `supplier_address` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_audited` date DEFAULT NULL,
  `supplier_rating` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `supplier_main_contact` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `last_update` date DEFAULT current_timestamp(),
  `notes` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `brand` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `primary_product` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `factory_size` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `clients` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `equipment` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `website` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `source` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `database_ranking` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_bin;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `supplier_name`, `supplier_address`, `last_audited`, `supplier_rating`, `supplier_main_contact`, `last_update`, `notes`, `brand`, `primary_product`, `factory_size`, `clients`, `equipment`, `website`, `source`, `database_ranking`) VALUES
(1, '临清一马ma', 'mi临清往东', '2013-08-24', NULL, '一马', NULL, 'automatic assembly line, no checking of the clearance or vibrations online, no grinding of rings, has hardness testing device (see photo), cousin has vibrometer', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, 'Tom found at Canton fair', NULL),
(2, '临清一马', '临清往东', '2013-08-24', NULL, '一马', NULL, 'automatic assembly line, no checking of the clearance or vibrations online, no grinding of rings, has hardness testing device (see photo), cousin has vibrometer', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, 'Tom found at Canton fair', NULL),
(3, '临清越动', '临清烟店', '2013-08-24', NULL, '越动 /  纳祥', NULL, 'give them C3 value; no export certificate; can give drawing; ring grinding and automatic line mounting no clearance online or vibration online checking, very dirty - small dgbb only; 6310 individual c', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(4, '启卓轴承', '临清烟店', '2014-08-24', NULL, '启卓轴承', NULL, '6210 2RS: 10（不含税运）；6310 开式无油脂：17（不含税运）; just began assemble bearings. very decent ring grinding and honing, manual assembly, no clearance checking no ring matching no vibration measurements； husband a', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(5, '临清嘉斯特', '临清烟店', '2014-08-24', NULL, '临清嘉斯特', NULL, 'really bad factory for thin walled bearings; no measuring device; very dirty grinding measuring machines almost unused and very unstructured, assembly with no clearance measurements or ball selection,', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(6, '鑫泰轴承', '临清烟店', '2014-08-24', NULL, '鑫泰轴承', NULL, 'brother/father of 启卓，assemble smaller size DGBB, quoting cheap bearings, no ring grinding and no automatic assembly, no clearance and ball selection, vibration measurements machine extensively used. S', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(7, '力博威', '临清烟店', '2014-08-24', NULL, '力博威', NULL, 'distributor', NULL, 'pillow', NULL, NULL, NULL, NULL, NULL, NULL),
(8, '卓廷', '临清烟店', '2014-08-24', NULL, '卓廷', NULL, 'quoted 200 for pillow bearing; manual assembly, no checking for clearance, no checking for ring matching, middle man\'s wechat. ', NULL, 'pillow', NULL, NULL, NULL, NULL, NULL, NULL),
(9, '华铭', NULL, NULL, NULL, '镁铭', NULL, '59/set incl. tax and transportation. Own brand', NULL, 'pillow', NULL, NULL, NULL, NULL, '1688', NULL),
(10, '展延', NULL, NULL, NULL, '展延', NULL, '45/set', NULL, 'pillow', NULL, NULL, NULL, NULL, '1688', NULL),
(11, '王易', NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'pillow', NULL, NULL, NULL, NULL, NULL, NULL),
(12, '讯飞', NULL, NULL, NULL, NULL, NULL, 'quoted for roller bearings in august', NULL, '2 (SBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(13, '绿远', NULL, NULL, NULL, NULL, NULL, 'quoted for roller bearings in august', NULL, '2 (SBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(14, '无锡鸿洲', NULL, NULL, NULL, NULL, NULL, 'quoted for roller bearings in august', NULL, '2 (SBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(15, '聊城燕马', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '7 (TRB)', NULL, NULL, NULL, NULL, NULL, NULL),
(16, '伟刚', '杭州', NULL, NULL, NULL, NULL, NULL, NULL, '万向节', NULL, NULL, NULL, NULL, NULL, NULL),
(17, '本宇', '杭州', NULL, NULL, NULL, NULL, NULL, NULL, '万向节', NULL, NULL, NULL, NULL, NULL, NULL),
(18, '神州十字轴', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '万向节', NULL, NULL, NULL, NULL, NULL, NULL),
(19, '迅飞', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '万向节', NULL, NULL, NULL, NULL, NULL, NULL),
(20, '万向', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '万向节', NULL, NULL, NULL, NULL, NULL, NULL),
(21, '鑫泰报价', NULL, NULL, NULL, NULL, NULL, 'quoted for cheap bearings', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(22, '德尔玛', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(23, '山东海乐', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(24, '上海仁趣(安徽', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(25, '临清市帅旭', NULL, NULL, NULL, NULL, NULL, 'refused factory visit', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(26, '山东鲁泰', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(27, '漳州', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(28, '青岛瑞科特', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(29, '一诺', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(30, '上海轴姆轴承', NULL, NULL, NULL, NULL, NULL, 'distributor?', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(31, '捷亚特', NULL, NULL, NULL, '捷亚特', NULL, 'FZB distributor', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(32, '恩凯弗', NULL, NULL, NULL, NULL, NULL, 'expensive quote', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(33, '博发', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, NULL, NULL),
(34, '欣悦', NULL, NULL, NULL, NULL, NULL, NULL, NULL, '千类 (thin section)', NULL, NULL, NULL, NULL, NULL, NULL),
(35, '振豪', '临清市潘庄镇', NULL, NULL, '振豪', NULL, 'Refused to produce. Never to work with them again', NULL, 'pillow', NULL, NULL, NULL, NULL, '1688', NULL),
(36, '宏钺轴承', '临清烟店', NULL, NULL, NULL, NULL, NULL, NULL, '3 (SRB)', NULL, NULL, NULL, NULL, NULL, NULL),
(37, 'ZMH轴美特', NULL, NULL, NULL, 'Kenny', NULL, NULL, NULL, 'All', NULL, NULL, NULL, NULL, 'Bearing fair', NULL),
(38, '慈溪市吉鹏轴承有限公司', '慈溪', NULL, NULL, '罗成', NULL, NULL, NULL, '0 (DGBB)', 'a three fl', 'ISB Italy', 'no clearance measuring, no roundness measuring', NULL, NULL, NULL),
(39, '慈溪市耐基轴承厂 (ZMH)', '慈溪', '2018-11-24', NULL, NULL, NULL, 'purchase steel from 新澄钢配; measured C&U super speed bearing on roundness machine', NULL, '0 (DGBB)', 'a three fl', NULL, 'quality factory with measuring devices, measures groove radius, clearance, super fineturning smoothness check (by eye then by microscope)', NULL, NULL, NULL),
(40, '锡迈克轴承有限公司 (ZMH)', '临清-唐园', '2020-11-24', NULL, NULL, NULL, 'all the drawings in place; gave us steel inspection reports; spherical roller bearing ZV standard and clearance tables; two types of rollers', 'WXMK', '3 (SRB)', NULL, 'C&U, LZ, international tier 2', 'full range (vibrometer, profilometer, measure clearance 100% using calibrated strips), even steel microscope', NULL, NULL, '986'),
(41, '山东朗澈轴承有限公司 (ZMH)', '临清-潘庄镇', '2020-11-24', NULL, NULL, NULL, 'speed can reach 9000', NULL, '0 (DGBB)', 'huge and w', NULL, NULL, NULL, NULL, '309'),
(42, '临清市卓奥轴承有限公司 (ZMH)', '临清-潘庄镇', '2020-11-24', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 'fatigue life test (5000rpm, 10000rpm)', NULL, NULL, '1368'),
(43, '山东一迈轴承科技有限公司 (ZMH)', '临清-潘庄镇', '2020-11-24', NULL, NULL, NULL, 'testing cross roller bearing and speed reducer unit', NULL, NULL, NULL, NULL, '3D Zeiss', NULL, NULL, '756'),
(44, '曲周县康神轴承有限公司', '邯郸', '2021-11-24', NULL, '康神', NULL, 'next door factories: DEMANSH (EV?), e bikes, cold forging', NULL, NULL, '15000', NULL, 'half factory for machining and half for bearings; 3D machine; going to put vibrometer at the end of cleaning line', NULL, 'database', '3731'),
(45, '山东瓦亿轴承有限公司', '烟店', '2021-11-24', NULL, '瓦亿', NULL, 'UKS branded', 'ZWYW', 'pillow', NULL, NULL, NULL, NULL, NULL, '1770'),
(46, '山东热河轴承科技有限公司', '烟店', '2021-11-24', NULL, '热河', NULL, 'Husband/wife greeted us', 'RHZ', '0 (DGBB)', '18000', 'used to supply to C&U and ISB (Shanghai exporter) with payment terms 90 days', 'vibrometer and radius checking in assembly line; claims can arrange fatigue life test', NULL, NULL, '321'),
(47, '山东德凯孚精密轴承有限公司', '烟店', '2022-11-24', NULL, 'SDKF', NULL, 'trained quality personel; able to produce P0/6, P5, even P4; konwledgable of bearing features and processes; P5 quality roughness 0.07, roundness 0.68', 'SDKF', '千类 (thin section)', '2000', NULL, '100% check clearance while assmbling', NULL, 'Bearing fair', NULL),
(48, '临清市奥兴轴承有限公司', '烟店', '2022-11-24', NULL, '奥兴', NULL, 'only uses non contact seal', NULL, '千类 (thin section)', 'moving to ', 'C&U, fake HRB, ISB', 'inner ring groove incorrectly supported in the groove', NULL, 'introduced by 热河', '1723'),
(49, '山东博特轴承有限公司', '烟店', '2022-11-24', NULL, '博特', NULL, '300m rmb revenue, 300 staff, automated production but clearance not checked 100%; moving into new factory, next year also produce smaller srb', 'BOTE', '千类 (thin section)', '18000', 'used to oem for ISB', '300', NULL, NULL, '32'),
(50, '邯郸市哈瑞本轴承有限公司', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '60mu', NULL, NULL, NULL, 'database', NULL),
(51, 'YKBearing亿凯晟', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1688', NULL),
(52, '山东调心轴承有限公司', '临清-唐园', NULL, NULL, NULL, NULL, 'mainly spherical roller bearings', NULL, NULL, NULL, NULL, NULL, NULL, '1688', NULL),
(53, '巴尔巴', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1688', NULL),
(54, '航天轴承', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '1688', NULL),
(55, '临清市凯旋轴承有限公司', NULL, NULL, NULL, NULL, NULL, 'customer service was a bitch', NULL, NULL, NULL, NULL, NULL, NULL, '1688', '2289'),
(56, '宁波博纳轴承有限公司', '慈溪', NULL, NULL, NULL, NULL, '13506743510', NULL, '千类 (thin section)', NULL, NULL, NULL, NULL, 'drove by', NULL),
(57, '河北玉良轴承集团有限公司', '临西', NULL, NULL, '玉良', NULL, NULL, NULL, '3 (SRB)', '200 people', NULL, NULL, 'www.ylgdt.com', 'Bearing fair', '145'),
(58, '宁波市恪诚轴承有限公司', '慈溪', NULL, NULL, '恪诚', NULL, 'high precision P5, P4, ZV4', 'CJS', '千类 (thin section)', NULL, NULL, NULL, 'www.cxnjs.com', 'Bearing fair', '570'),
(59, '山东唯工轴承科技有限公司', '聊城', NULL, NULL, 'VGB', NULL, 'can oem', 'VGB', 'pillow', '300 sqm', NULL, NULL, 'www.vgb-bearing.com', 'Bearing fair', '580'),
(60, '河北精拓轴承', '馆陶县', NULL, NULL, 'JITO', NULL, NULL, 'JITO', NULL, NULL, NULL, NULL, 'www.jito.cc', 'Bearing fair', NULL),
(61, '河北佳顺不锈钢轴承制造有限公司', '邢台', NULL, NULL, '佳顺', NULL, NULL, 'CASUN', 'stainless steel', NULL, NULL, NULL, 'http://www.jiashunbearing.com', 'Bearing fair', '1097'),
(62, '利美克轴承传动（洛阳）有限公司', '洛阳', NULL, NULL, '利美克', NULL, NULL, 'NPB', 'Agri Hub', '20000', NULL, NULL, 'npbbearing.com', 'Bearing fair', '232'),
(63, '宁波迈锐机械有限公司', '余姚', NULL, NULL, '迈锐', NULL, '6005 tested at 16k/rpm ', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, 'Bearing fair', NULL),
(64, '山东欧宇轴承有限公司', '聊城', NULL, NULL, '欧宇', NULL, 'Has many oem agri machinery clients', 'JB', 'Agri Hub', 'fewer than', NULL, NULL, 'www.jvvb.com', 'Bearing fair', NULL),
(65, '宁波爱健轴承有限公司', '慈溪', NULL, NULL, '爱健', NULL, NULL, NULL, '千类 (thin section)', NULL, NULL, NULL, NULL, 'Bearing fair', 'B187'),
(66, '山东华工轴承有限公司', '聊城', NULL, NULL, '华工', NULL, NULL, 'HGF', '3 (SRB)', NULL, NULL, NULL, NULL, 'Bearing fair', '78'),
(67, '响水县腾达轴承座有限公司', '江苏响水', NULL, NULL, '腾达', NULL, '范龙龙15950309481', NULL, '轴承座', NULL, NULL, NULL, 'www.xs-td.com', 'Bearing fair', NULL),
(68, '河北亿泰克轴承有限公司', '临西', NULL, NULL, 'ETK', NULL, NULL, 'ETK', 'pillow', NULL, NULL, NULL, 'www.etkbearing.com', 'Bearing fair', '355'),
(69, '泉州国兴轴承有限公司', '福建泉州', NULL, NULL, '国兴', NULL, NULL, NULL, 'pillow', NULL, NULL, NULL, NULL, 'Bearing fair', '254'),
(70, '山东省迪伯特机械有限公司', '聊城', NULL, NULL, 'DBK', NULL, NULL, 'DBK', 'pillow', NULL, NULL, NULL, NULL, 'Bearing fair', NULL),
(71, '山东永恒轴承有限公司', '冠县', NULL, NULL, '永恒', NULL, NULL, 'YHZ', '千类 (thin section)', NULL, NULL, NULL, 'www.yhzcbeaing.com', 'Bearing fair', NULL),
(72, '河北优特杰轴承有限公司', '临西', NULL, NULL, '优特杰', NULL, NULL, NULL, '6 (角接触)', '11122', NULL, NULL, NULL, 'Bearing fair', '846'),
(73, '福达轴承集团有限公司', '慈溪', NULL, NULL, '福达', NULL, NULL, 'CBB', '0 (DGBB)', NULL, NULL, NULL, 'www.fudabearings.com', 'Bearing fair', 'B2'),
(74, '杭州竞舟轴承有限公司', '杭州', NULL, NULL, '竞舟', NULL, NULL, NULL, '7 (TRB)', NULL, NULL, NULL, NULL, 'Bearing fair', 'B202'),
(75, '宁波人和机械轴承有限公司', NULL, NULL, NULL, NULL, NULL, 'supplier to motorbikes but speed not tested', NULL, '0 (DGBB)', NULL, NULL, NULL, NULL, 'Bearing fair', 'B398'),
(76, '浙江西密克轴承股份有限公司', '浙江新昌', NULL, NULL, '西密克', NULL, NULL, 'CMI', 'Agri Hub', NULL, NULL, NULL, NULL, 'Bearing fair', 'B68'),
(77, '常州环聍轴承制造有限公司', '常州', NULL, NULL, 'HN', NULL, NULL, NULL, '4 (needle)', NULL, NULL, NULL, NULL, 'Bearing fair', NULL),
(78, '江苏光扬轴承股份有限公司', NULL, NULL, NULL, '光扬', NULL, NULL, 'JSGY', 'combination', NULL, NULL, NULL, NULL, 'Bearing fair', 'B493'),
(79, '常州云帆轴承有限公司', NULL, NULL, NULL, '云帆', NULL, NULL, NULL, '4 (needle)', NULL, NULL, NULL, NULL, 'Bearing fair', 'B638'),
(80, '中山市盈科轴承制造有限公司', '广东中山', NULL, NULL, NULL, NULL, NULL, 'TCB', NULL, NULL, NULL, NULL, 'www.tcbbearing.com', 'Bearing fair', '33'),
(81, '宁波宇洪轴承有限公司', '宁波', NULL, NULL, 'KJYJ', NULL, NULL, 'KJYJ', '0 (DGBB)', '200 people', NULL, NULL, 'www.jhbearing.com', 'Bearing fair', 'B937'),
(82, '余姚市新丰轴承有限公司', '余姚', NULL, NULL, 'bobo', NULL, NULL, NULL, '0 (DGBB)', '230 people', NULL, NULL, NULL, 'Bearing fair', 'B917'),
(83, '山东省万凯轴承制造有限公司', '聊城', NULL, NULL, '万凯', NULL, 'called woman who used to work in 华工', NULL, '3 (SRB)', NULL, NULL, NULL, NULL, 'Bearing fair', '520'),
(84, '临清市德佳轴承有限公司', '烟店', NULL, NULL, NULL, NULL, NULL, NULL, '3 (SRB)', NULL, NULL, NULL, NULL, 'bearing association', '1484'),
(85, '浙江中精轴承有限公司', '浙江常山', NULL, NULL, 'alice', NULL, NULL, NULL, '7 (TRB)', '10000', NULL, NULL, NULL, 'Bearing fair', '96'),
(86, '南通嘉安机械有限公司', '南通', NULL, NULL, '嘉安', NULL, NULL, NULL, '锁紧螺母', NULL, NULL, NULL, NULL, 'Bearing fair', NULL),
(87, '深圳市合诚润滑材料有限公司', '深圳', NULL, NULL, '合诚', NULL, 'medium size in qcc', NULL, 'Grease', '286 people', NULL, NULL, 'http://www.hcrhz.com', 'Bearing fair', NULL),
(88, '山东意吉希精密制造有限公司', '聊城', NULL, NULL, 'EGC', NULL, NULL, 'EGC', 'Cages', NULL, NULL, NULL, NULL, 'Bearing fair', NULL),
(89, '江苏光扬轴承股份有限公司', '江苏', NULL, NULL, '光扬', NULL, NULL, NULL, '4 (needle)', '300 people', NULL, NULL, NULL, 'Bearing fair', 'B493'),
(90, '浙江新昌振新密封件有限公司', '浙江新昌', NULL, NULL, '振新密封', NULL, NULL, NULL, 'sealing', '163 people', NULL, NULL, NULL, 'Bearing fair', NULL),
(91, '常州浩瀚轴承厂', '常州', NULL, NULL, '常州浩瀚', NULL, NULL, NULL, '4 (needle)', NULL, NULL, NULL, 'www.hh-b.net', 'Bearing fair', NULL),
(92, '常州杰安轴承', '常州', NULL, NULL, '杰安', NULL, NULL, NULL, '4 (needle)', NULL, NULL, NULL, NULL, 'Automechanica', 'B740'),
(93, '嘉善华阳轴承有限公司\'', '嘉善', NULL, NULL, '华阳', NULL, NULL, 'HY', '4 (needle)', '17 people ', NULL, NULL, 'https://www.hybearing.com', 'Automechanica', 'B1669'),
(94, '江苏南方精工股份有限公司', '常州', NULL, NULL, '南方', NULL, NULL, NULL, '4 (needle)', '970 people', NULL, NULL, NULL, 'fair', '17'),
(95, '中国轴承', '聊城', NULL, NULL, NULL, NULL, NULL, 'ZY', NULL, NULL, NULL, NULL, NULL, NULL, NULL),
(96, 'pippo', 'pappo', '0000-00-00', NULL, '', '0000-00-00', '', '', '', '', '', '', '', '', ''),
(97, '', '', '0000-00-00', NULL, '', '0000-00-00', '', '', '', '', '', '', '', '', ''),
(98, '临清市华旭轴承有限公司', '聊城', '0000-00-00', NULL, '', '0000-00-00', '', '', 'wheel hubs', '', '', '', 'www.dhxbbearing.com', 'Auto Meckanika', '840');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `client`
--
ALTER TABLE `client`
  ADD PRIMARY KEY (`client_id`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`invoice_id`);

--
-- Indexes for table `product_family`
--
ALTER TABLE `product_family`
  ADD PRIMARY KEY (`product_family_id`);

--
-- Indexes for table `rfq_client`
--
ALTER TABLE `rfq_client`
  ADD PRIMARY KEY (`rfq_client_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `client`
--
ALTER TABLE `client`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `invoice_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=321;

--
-- AUTO_INCREMENT for table `product_family`
--
ALTER TABLE `product_family`
  MODIFY `product_family_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rfq_client`
--
ALTER TABLE `rfq_client`
  MODIFY `rfq_client_id` int(10) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

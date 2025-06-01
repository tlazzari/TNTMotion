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
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(10) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 08, 2026 at 03:13 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u815853083_power_team`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `region` varchar(100) NOT NULL,
  `chapter` varchar(100) NOT NULL,
  `powerteam` varchar(100) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `region`, `chapter`, `powerteam`, `created_at`, `updated_at`) VALUES
(1, 'mani', '$2y$10$ZXl.Em4SAdAxhj97.GH98eC5UXnSuxZVIJ7xiury8XTWeO4wS5jOG', '1', '1', '1', NULL, NULL),
(9, '8939329644', '$2y$10$dLHU./FcoREYs0ZY8VmZru/jNM1uzJ.aHez6AHrfvedTRR6rwA2ze', '1', '4', '1', NULL, NULL),
(10, '9841664091', '$2y$10$fQjxpp9tMudSbxYhy4New.f8f3r7YaAJTwnsM84qdkd2.pKQu.HLy', '1', '3', '4', NULL, NULL),
(11, '9080123737', '$2y$10$KpL6uQ2KVTZ0hU7AIjJWW.AHCaKHD/MhbRHo9IHYiD64CKmoq1vBW', '1', '4', '8', NULL, NULL),
(12, '9841326341', '$2y$10$bBv.Hz418ikOdsQjdSrFXOG5tUUSrA/RkM0l1idUgQBmWcTEaWo7.', '1', '5', '1', NULL, NULL),
(13, 'vigneshwaran', '$2y$10$oXdyUWkBZBhXW3woowXvUelUZPj5CaMHzINjLsbiSmOzF.pQzgwvm', '1', '5', '1', NULL, NULL),
(14, '9941859792', '$2y$10$aZntU6zxybOF8T9UF6way.GTCDbcMowLQwm4.8aWnO9ubmUm8SaIC', '1', '5', '1', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `meeting_date` date DEFAULT NULL,
  `present` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `member_id`, `meeting_date`, `present`) VALUES
(45, 18, '2025-06-26', 'Absent'),
(46, 22, '2025-06-26', 'Present'),
(47, 23, '2025-06-26', 'Absent'),
(48, 25, '2025-06-26', 'Present'),
(49, 26, '2025-06-26', 'Present'),
(50, 27, '2025-06-26', 'Present'),
(51, 28, '2025-06-26', 'Present'),
(52, 29, '2025-06-26', 'Absent'),
(53, 30, '2025-06-26', 'Absent'),
(54, 31, '2025-06-26', 'Present'),
(55, 32, '2025-06-26', 'Present'),
(56, 33, '2025-06-19', 'Present'),
(57, 34, '2025-06-19', 'Present'),
(58, 35, '2025-06-19', 'Present'),
(59, 36, '2025-06-19', 'Present'),
(60, 37, '2025-06-19', 'Present'),
(61, 38, '2025-06-19', 'Absent'),
(62, 39, '2025-06-19', 'Absent'),
(63, 40, '2025-06-19', 'Absent'),
(64, 41, '2025-06-19', 'Absent'),
(65, 33, '2025-07-24', 'Present'),
(66, 34, '2025-07-24', 'Present'),
(67, 35, '2025-07-24', 'Present'),
(68, 36, '2025-07-24', 'Present'),
(69, 37, '2025-07-24', 'Absent'),
(70, 38, '2025-07-24', 'Present'),
(71, 39, '2025-07-24', 'Present'),
(72, 40, '2025-07-24', 'Present'),
(73, 41, '2025-07-24', 'Absent'),
(74, 18, '2025-09-04', 'Late'),
(75, 22, '2025-09-04', 'Absent'),
(76, 23, '2025-09-04', 'Absent'),
(77, 25, '2025-09-04', 'Absent'),
(78, 26, '2025-09-04', 'Present'),
(79, 27, '2025-09-04', 'Absent'),
(80, 28, '2025-09-04', 'Present'),
(81, 29, '2025-09-04', 'Present'),
(82, 30, '2025-09-04', 'Present'),
(83, 31, '2025-09-04', 'Absent'),
(84, 32, '2025-09-04', 'Present'),
(85, 18, '2025-10-06', 'Present'),
(86, 22, '2025-10-06', 'Present'),
(87, 23, '2025-10-06', 'Present'),
(88, 25, '2025-10-06', 'Present'),
(89, 26, '2025-10-06', 'Present'),
(90, 27, '2025-10-06', 'Present'),
(91, 28, '2025-10-06', 'Present'),
(92, 29, '2025-10-06', 'Present'),
(93, 30, '2025-10-06', 'Present'),
(94, 31, '2025-10-06', 'Present'),
(95, 32, '2025-10-06', 'Present'),
(96, 46, '2025-10-09', 'Absent'),
(97, 47, '2025-10-09', 'Present'),
(98, 43, '2025-10-10', 'Absent'),
(100, 43, '2025-10-15', 'Absent'),
(102, 43, '2025-10-01', 'Late'),
(103, 56, '2025-10-01', 'Absent'),
(104, 18, '2025-10-23', 'Present'),
(105, 22, '2025-10-23', 'Absent'),
(106, 23, '2025-10-23', 'Absent'),
(107, 25, '2025-10-23', 'Present'),
(108, 26, '2025-10-23', 'Absent'),
(109, 27, '2025-10-23', 'Present'),
(110, 28, '2025-10-23', 'Absent'),
(111, 29, '2025-10-23', 'Present'),
(112, 30, '2025-10-23', 'Absent'),
(113, 31, '2025-10-23', 'Absent'),
(114, 32, '2025-10-23', 'Present'),
(115, 33, '2025-10-28', 'Present'),
(116, 34, '2025-10-28', 'Absent'),
(117, 35, '2025-10-28', 'Present'),
(118, 36, '2025-10-28', 'Late'),
(119, 37, '2025-10-28', 'Absent'),
(120, 38, '2025-10-28', 'Present'),
(121, 39, '2025-10-28', 'Absent'),
(122, 40, '2025-10-28', 'Present'),
(123, 41, '2025-10-28', 'Absent'),
(124, 18, '2025-11-13', 'Present'),
(125, 22, '2025-11-13', 'Present'),
(126, 25, '2025-11-13', 'Present'),
(127, 26, '2025-11-13', 'Present'),
(128, 27, '2025-11-13', 'Absent'),
(129, 29, '2025-11-13', 'Absent'),
(130, 31, '2025-11-13', 'Present'),
(131, 32, '2025-11-13', 'Present'),
(132, 58, '2025-11-13', 'Present'),
(133, 59, '2025-12-30', 'Present'),
(134, 60, '2025-12-30', 'Absent'),
(135, 61, '2025-12-30', 'Late'),
(136, 62, '2026-01-09', 'Present'),
(137, 65, '2026-01-09', 'Present'),
(138, 66, '2026-01-09', 'Absent'),
(139, 67, '2026-01-09', 'Present'),
(140, 68, '2026-01-09', 'Absent'),
(141, 69, '2026-01-09', 'Present'),
(142, 70, '2026-01-09', 'Present'),
(143, 71, '2026-01-09', 'Present'),
(144, 72, '2026-01-09', 'Present'),
(145, 18, '2026-02-26', 'Present'),
(146, 22, '2026-02-26', 'Absent'),
(147, 25, '2026-02-26', 'Absent'),
(148, 26, '2026-02-26', 'Present'),
(149, 27, '2026-02-26', 'Absent'),
(150, 29, '2026-02-26', 'Absent'),
(151, 31, '2026-02-26', 'Present'),
(152, 32, '2026-02-26', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `chapters`
--

CREATE TABLE `chapters` (
  `id` int(20) NOT NULL,
  `svalue` varchar(20) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `sub_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chapters`
--

INSERT INTO `chapters` (`id`, `svalue`, `image`, `sub_date`) VALUES
(1, 'Sparkles', '1760519979_bni.png', '2026-01-25'),
(2, 'Tiara', NULL, '2026-01-25'),
(3, 'Ascend', NULL, '2026-01-25'),
(4, 'Bravo', '1760520108_bni_bravo_chennai_north_logo.jfif', '2026-01-31'),
(5, 'Thozhirchalai', 'chapter_699d6f58af29f0.66402116.jpg', '2026-02-24');

-- --------------------------------------------------------

--
-- Table structure for table `meeting_planner`
--

CREATE TABLE `meeting_planner` (
  `id` int(11) NOT NULL,
  `meeting_date` date NOT NULL,
  `member_id` int(11) NOT NULL,
  `meeting_type` enum('Powerteam','Global') NOT NULL,
  `agenda` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `remarks` text DEFAULT NULL,
  `status` enum('Completed','Not happened') DEFAULT NULL,
  `updateAt` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `region` varchar(100) DEFAULT NULL,
  `powerteam` varchar(100) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `chapter` varchar(100) DEFAULT NULL,
  `chapter_access` tinyint(1) NOT NULL DEFAULT 0,
  `password` varchar(255) NOT NULL,
  `status` int(11) NOT NULL DEFAULT 1,
  `sub_date` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`id`, `name`, `region`, `powerteam`, `category`, `email`, `mobile`, `chapter`, `chapter_access`, `password`, `status`, `sub_date`) VALUES
(18, 'Mani', '1', '1', 'Chapter', 'mani@gmail.com', '8838995745', '1', 1, '$2y$10$ZXl.Em4SAdAxhj97.GH98eC5UXnSuxZVIJ7xiury8XTWeO4wS5jOG', 1, '2026-12-31'),
(22, 'Deepak Rajaram', '1', '1', 'IT Rental', 'star@starengineering.biz', '9789990235', '1', 0, '$2y$10$E1HHATNh/zh2KwlzBf1Wye5USdEYn0A/EDBl9215wPzo4PHxuBq0m', 1, '2026-12-31'),
(25, 'Mukundh Bhuvaraghavan', '1', '1', 'ERP Software', 'bmukund@altrockstech.com', '9840027655', '1', 1, '$2y$10$p1qZdC2Nau/My8c2r1R/OeT2OZ2B3fd/3.TKyI2CbefrNa6Mb0X4O', 1, '2026-12-31'),
(26, 'Nagendrababu Babu', '1', '1', 'IT Hardware', 'qspacesystem@gmail.com', '9884190590', '1', 0, '$2y$10$Dy72QU/tiA.OG5r8AZo/WO2RtrOSqCdaklmtlkiHRy5wO2xXjBrHW', 1, '2026-12-31'),
(27, 'Prakash Dhanapalan', '1', '1', 'Field Force Automation', 'prakash.dhanapalan@mavens-i.com', '9940090134', '1', 1, '$2y$10$VXQ2SmydQxK/Z7gz2PJ.KerixeXbe3AvKHCbfJGt41jPJ5pFu75my', 1, '2026-12-31'),
(29, 'Sudhendra Devi', '1', '1', 'Mobile APP', 'ramesh@softsuave.com', '9677224083', '1', 0, '$2y$10$haAG8PnQzzwbsRN/y1.wseC08rVhEYavwfQ.bJwq8E4RZFbCfvIGq', 1, '2026-12-31'),
(31, 'Vasantha kumar SP', '1', '1', 'Cybersecurity', 'vasanth@pynesec.com', '6380320525', '1', 0, '$2y$10$rclEzplVRoV9RcCof0MXHO060va.VEb3V4cYIMjAZK6DyvM3SC8XK', 1, '2026-12-31'),
(32, 'Vidhya Chandrasekaran', '1', '1', 'HR Portal', 'vidhya@iscalepro.com', '9894093201', '1', 1, '$2y$10$kI0X0N7DHFgFDIggfXTdL.pieIeLE0W8WhRJb3xXVzoTW7H.wkCtu', 1, '2026-12-31'),
(33, 'Madhu Sudhan Gundepudi', '1', '7', 'Fire Protection', 'madhusudhan983@gmail.com', '9840948101', '1', 0, '$2y$10$979vvgc5ObYpJYP46E73o.4OpCyUePj23d.jYMCdqz0JYbqaEeCcK', 1, '2026-12-31'),
(34, 'Samson Irudayaraj', '1', '7', 'IoT', 'samson23i@gmail.com', '9994840432', '1', 0, '$2y$10$jb61PZTUXcu3NOgLk4K5hOrjAEhaLKXUq1I8.ovSFou6TZ3TSOU7K', 1, '2026-12-31'),
(35, 'Channappan .K.V', '1', '7', 'General Insurance', 'kanjichan@gmail.com', '9791017052', '1', 1, '$2y$10$MbdMSxwLygrF4w1jKI3axeCHEfAaCXzjlbuqcN62RPJpSjVgOC0LS', 1, '2026-12-31'),
(36, 'Srinivasan Krishnamurthi', '1', '7', 'Manufacturing, Machinery & Equipment Manufacture', 'srinibnisparkles@gmail.com', '9940664019', '1', 1, '$2y$10$j8lj769q.ZB32c4Rid54i.yLmIeL2Iqyo/gsozPd/YmjekF.H3w8e', 1, '2026-12-31'),
(37, 'Nirmal Raj', '1', '7', 'Industrial Automation Projects', 'nirmal@gmail.com', '9884082620', '1', 0, '$2y$10$EUmousQYSx.4DzL94x2vg.M7uwJKtHh8tuS.ybLKOdcVjGXRE6vhm', 1, '2026-12-31'),
(38, 'HUSSAIN EZZY', '1', '7', ' Plumbing', 'makenggcorp@gmail.com', '9790755844', '1', 0, '$2y$10$TnbqZ3Ck8RD7M5VTL.mXNesW2n73k4Plgqg3mDL6.goRIIh.GCsWq', 1, '2026-12-31'),
(39, 'Hussain K Pardawala', '1', '7', 'Construction', 'uniqueelectrodes@yahoo.co.in', '9841180451', '1', 0, '$2y$10$GWRns4soVfZzvLWr9EKYTO6CuSRlJ2yRAo2juX0rV8UjXZTCwWAfi', 1, '2026-12-31'),
(40, 'Hussain Sunelwala', '1', '7', 'Welding Equipments', 'al_hutaibtc@yahoo.co.in', '9003137392', '1', 0, '$2y$10$weVk56XDbXm.jdaWwIDnluaqgO1AiW7ACxxkd7HIP16AoFMM8TISi', 1, '2026-12-31'),
(41, 'Natarajan Subramaniam', '1', '7', 'Metal Fabrication - CNC Machining', 'ns@atturinternational.com', '9841736050', '1', 1, '$2y$10$w51gEZsmG3LzColroFuEBuEi58V4/MUh4VQzyH8vIU3OVp2Nb6ZGy', 1, '2026-12-31'),
(43, 'vinoth moorthy', '1', '1', 'website development', 'vinoth@blackbolt.in', '8939329644', '4', 1, '$2y$10$RPY.kQbdWAVP3gwQzVhq4uYPktY8Oq9hCiziTdTyb3hd8LHquccfO', 1, '2026-12-31'),
(46, 'Manikandan A', '1', '13', 'Digital Marketing', 'mani2@gmail.com', '9080123737', '4', 1, '$2y$10$wS6hfIGlWeqy4YRkrfdeW.9ESVx0R6f5bBy2KRjY9zpkU3j1r/RKq', 1, '2026-12-31'),
(47, 'Bhuvana', '1', '13', 'Digital Marketing', 'bhuvana@gmail.com', '9884735745', '4', 0, '$2y$10$sH7wHP1TMwSMBQ1a8zTXvu.hW7AcdTKL44toDbckng6rpH8y80ly2', 1, '2026-12-31'),
(48, 'Saradha testing', '1', '1', 'CFO Services', 'saradha@ekameva.com', '9840159937', '4', 1, '$2y$10$6hLVwdPlAWld592P.FkWnORdQrFMutziuLshQT4zLb91MygD.CkHO', 0, '2026-12-31'),
(56, 'vinoth moorthy', '1', '1', 'website development', 'vinothmoorthyn@gmail.com', '9884203338', '4', 1, '$2y$10$4GEab1H5usFLfgDz5nSZkukmqAIy0Z1vIqE9X1Ks4H1zJuLIQrfIm', 1, '2026-12-31'),
(57, 'sowmya', '1', '1', 'content', 'sowmya@gmail.com', '12121212121', '4', 0, '$2y$10$6bJC1O3XjjUK5m/Y9PTyLuSY2AH7On8pv.kLtVDpBfPINero8OLE.', 1, '2026-12-31'),
(59, 'Maaris', '1', '15', 'Advertising', 'maaris.s@saltcity.in', '7200115844', '1', 1, '$2y$10$WPUQH0ylkCWc4qEziJMPp.LhByTLdBa9ocmrfVpVaCJ5YlpG1KaZ.', 1, '2026-12-31'),
(60, 'Parthasarathy', '1', '15', 'Wedding Photography', 'studioananyaa2020@gmail.com', '9952871541', '1', 0, '$2y$10$SZZVafvBWfGFgwQc8RQ7veA8Q5Ig755KAyIXnm61Txc2ysGcQZphG', 1, '2026-12-31'),
(61, 'Akshya S', '1', '15', 'Digital Marketing', 'zywinmedia@gmail.com', '6385145122', '1', 0, '$2y$10$mBthP3FG6rf5fXmxc7vJFeOECjbEKKd7/1rdB5yiqMe4.YwaHXY1C', 1, '2026-12-31'),
(62, 'Shobana M R', '1', '16', 'Research Publications', 'shobana.mallisetti@scribezonee.com', '9841240966', '1', 1, '$2y$10$tzLUsctmrnYVl0kiGLMMHuc7MzZKr2e.JJcU7ileh8T4QxRouGHRW', 1, '2026-12-31'),
(63, 'Premnath', '1', '18', 'Home Automation', 'premcontrolls@gmail.com', '9789841405', '1', 1, '$2y$10$MvrjN3Ph.o9h2od56DtD8.DLH4hGpEsyQQELfsyfHxsP06UBrvuDe', 1, '2026-12-31'),
(64, 'Sujan B', '1', '19', 'Solar', 'sujan_sb@ejp.net.in', '9363614603', '1', 1, '$2y$10$xmqkQhP2lcTayqLDPwq8POmS.bWD2kCdjR06M5yf29IxYovfzy2A2', 1, '2026-12-31'),
(65, 'Raja prabhu Paramasivam', '1', '16', 'Legal & Accounting | Tax Advisor', 'info@rajaprabhuca.com', '9884082100', '1', 0, '$2y$10$ICxLCbdiDMSTZYZJFA07fOxBDc1811oiBj1e0a.CxWQYNiWpOO/KC', 1, '2026-01-09'),
(66, 'Devi Vinod', '1', '16', 'Training & Coaching | Training & Coaching', 'coachdevism@gmail.com', '6379316536', '1', 0, '$2y$10$rZarI6tOrU5eLl3Cznc5S.D0PXBiW4YaEHL0Eb3y4SqgLwhgTZgCa', 1, '2026-01-09'),
(67, 'Goutham D', '1', '16', 'Finance & Insurance | Finance & Insurance', 'goutham@gojiro.in', '7305670678', '1', 0, '$2y$10$yfrlMwft35tBs02Yn5R0Ju.BISR3R8CJ..JmQU7mmbLZM/yoha5.y', 1, '2026-01-09'),
(68, 'MANICKABHARATHI GNANAPRAKASAM', '1', '16', 'Organizations & Others, Non-Profits/Fundraising Organizations', 'Manickabharathi@gmail.com', '9444334487', '1', 0, '$2y$10$FzEXU1Kt5b0Zvil6Aguw8e3xhWJOnQpyEkZNOdOMc5WPbusJqoeAO', 1, '2026-01-09'),
(69, 'Shyam Sundar', '1', '16', 'Finance & Insurance | Finance & Insurance', 'Shyam.firstassists@gmail.com', '7397302302', '1', 0, '$2y$10$i6cXWKEZBm4PU5E.8umu4eTbr2kuQiKc9EVC9r7TJCyxlxyejmDES', 1, '2026-01-09'),
(70, 'Sriraman Santhanam', '1', '16', 'Finance & Insurance | Financial Investments', 'srilaks1@gmail.com', '9894229677', '1', 0, '$2y$10$OhLf3MhOTGbnjVd0dnn5Nu3/i6KMRvOKoM7FXqS5iGSixzyQ4/ByO', 1, '2026-01-09'),
(71, 'Yusuf S Q', '1', '16', 'Legal & Accounting | Civil Law', 'thelawyeryusuf@gmail.com', '9840533308', '1', 0, '$2y$10$cHlY/mMSgBym0DVzfyQ.Q.OGmFNdPIReaG71tWtGpg9tYU9YzbGFa', 1, '2026-01-09'),
(72, 'Dr. Arun Chandran', '1', '16', 'Health & Wellness', 'drcarun@gmail.com', '9994070593', '1', 0, '$2y$10$Jeke/M/mXU.F50ptSL.q7eeSWCQcQVrZk6ZGy2LKChJvb9rFaG6pm', 1, '2026-01-09'),
(73, 'DINESH. A', '1', '20', 'Industrial Catering', 'dinesh.saaralhotels@gmail.com', '9841405500', '5', 0, '$2y$10$JPfGYD.OjCu.CGbwA1jMn.4ilPfALH7U9PWR6lBmj9gz6VTLZPu2S', 1, '2026-02-24'),
(75, 'Divya', '1', '1', 'software', 'divyal@mdqualityapps.com', '9841326341', '5', 1, '$2y$10$bNFDtCE/U6bBtihG2cS3SekO3BJQxkWTGmSuxzLIoaGxN6Y3v0pxK', 1, '2026-04-07'),
(76, 'Kamala Malar', '1', '20', 'IT Hardware', 'santhosh@invisibleitech.in', '9353672996', '5', 0, '$2y$10$IEncu.phmKvxQdu3rVnNge4nT58OObbFSiLYMk0YniWHg4qXnUCNW', 1, '2026-04-07'),
(77, 'Vigneshwaran', '1', '20', 'Corporate Gifts', 'vigneshwaran@gmail.com', '7200705803', '5', 0, '$2y$10$XOc.hvZtJ8qGbjL3V6dLdu7NItIxLlwkQenr2XHNubqXLbFlXD2vi', 1, '2026-04-07'),
(78, 'Naresh Babu', '1', '20', 'Man Power Consultancy', 'nareshbabu@gmail.com', '9940343951', '5', 0, '$2y$10$.YL6z3573WSRGULkns8T3eLVTcIYSaUed6vRcvvbYqy50Ll/8gary', 1, '2026-04-07'),
(79, 'Daniel Mathew', '1', '20', 'Events Management', 'daniel@gmail.com', '9841326341', '5', 0, '$2y$10$1ozpHE6dUgi9846ho/BpJ.l1YjgN7TZpTeoxaHsxctUB4zChKcyQu', 1, '2026-04-07');

-- --------------------------------------------------------

--
-- Table structure for table `powerteam`
--

CREATE TABLE `powerteam` (
  `id` int(30) NOT NULL,
  `pvalue` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `powerteam`
--

INSERT INTO `powerteam` (`id`, `pvalue`) VALUES
(1, 'IT'),
(4, 'Construction'),
(5, 'Marketing'),
(6, 'CxO'),
(7, 'Manufacturing'),
(10, 'HR, L&D & Admin'),
(11, 'Marketing, Finance & IT'),
(12, 'Real Estate'),
(13, 'Retail & Events'),
(14, 'International Power Team'),
(15, 'Advertising and Marketing'),
(16, 'Finance, HR, Training'),
(17, 'Civil A'),
(18, 'Civil B'),
(19, 'Capex'),
(20, 'Corporate');

-- --------------------------------------------------------

--
-- Table structure for table `power_dates`
--

CREATE TABLE `power_dates` (
  `id` int(11) NOT NULL,
  `organiser_id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `industry` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `date` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('Completed','Not Completed') DEFAULT 'Not Completed',
  `remarks` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `power_dates`
--

INSERT INTO `power_dates` (`id`, `organiser_id`, `company_name`, `industry`, `location`, `date`, `created_at`, `status`, `remarks`) VALUES
(3, 41, 'PVG Indo Tech Transformer', 'Electrical Transformers', 'Thirumudivakkam', '2025-08-29', '2025-09-02 12:13:11', 'Completed', 'Meeting completed'),
(4, 26, 'ORIENT', 'Interactive Panel', 'T.Nagar, Chennai', '2025-09-10', '2025-09-04 07:57:30', 'Not Completed', NULL),
(5, 26, 'Pondicherry University', 'Univeristy', 'Pondicherry', '2025-09-16', '2025-09-04 07:58:34', 'Not Completed', NULL),
(6, 36, 'TES', 'Shared Workspace', 'Guindy', '2025-09-02', '2025-09-04 08:00:23', 'Completed', 'Nagendrababu, Gopi visited TES'),
(7, 26, 'Heat and Control', 'Food Processing', 'Chengalpat', '2025-09-16', '2025-09-18 07:54:40', 'Completed', 'Divesh visited H&C'),
(8, 48, 'hyundai', 'automobile', 'sriperambadur', '2025-10-19', '2025-10-09 12:57:11', 'Completed', ''),
(9, 46, 'testing', 'it', 'test', '2025-10-01', '2025-10-10 05:51:08', 'Completed', 'connected cfo'),
(10, 78, 'Vanakam HR', 'HR', 'Trade center', '2026-07-11', '2026-04-07 12:44:57', 'Not Completed', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `power_date_members`
--

CREATE TABLE `power_date_members` (
  `id` int(11) NOT NULL,
  `power_date_id` int(11) NOT NULL,
  `member_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `power_date_members`
--

INSERT INTO `power_date_members` (`id`, `power_date_id`, `member_id`) VALUES
(6, 3, 36),
(7, 4, 31),
(8, 5, 28),
(9, 5, 32),
(10, 6, 26),
(11, 7, 23),
(12, 8, 43),
(13, 8, 46),
(14, 8, 47),
(15, 9, 47),
(16, 9, 49),
(17, 10, 73),
(18, 10, 75),
(19, 10, 76),
(20, 10, 77),
(21, 10, 78),
(22, 10, 79);

-- --------------------------------------------------------

--
-- Table structure for table `referrals`
--

CREATE TABLE `referrals` (
  `id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `company_name` varchar(150) DEFAULT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_email` varchar(100) DEFAULT NULL,
  `contact_phone` varchar(20) DEFAULT NULL,
  `referred_on` date DEFAULT NULL,
  `referral_type` enum('Specific Ask','Specific Give') NOT NULL DEFAULT 'Specific Ask',
  `assigned_member` int(11) DEFAULT NULL,
  `mobile` varchar(10) NOT NULL,
  `remarks` text DEFAULT NULL,
  `referred_member_id` int(11) DEFAULT NULL,
  `approve_status` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referrals`
--

INSERT INTO `referrals` (`id`, `member_id`, `company_name`, `contact_name`, `contact_email`, `contact_phone`, `referred_on`, `referral_type`, `assigned_member`, `mobile`, `remarks`, `referred_member_id`, `approve_status`) VALUES
(15, 22, 'Any co working space IT Manager', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 23, '', 'connected to the client', NULL, 0),
(16, 23, 'T Rajkumar - Chairman Indian Textile industry', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 22, '', NULL, NULL, 0),
(17, 23, 'Erode', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 25, '', '', NULL, 0),
(18, 23, 'KEMS Forging', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 25, '', '', NULL, 0),
(19, 23, 'Indian Terrain', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 22, '', '', NULL, 0),
(20, 18, 'JMIL - Rubber fabrication', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(21, 25, 'Steel Companies  / Pharma Company', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(22, 26, 'NIOT', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 25, '', 'Mukundh to check with Aruneshwar if he knows', NULL, 0),
(23, 26, 'Kovai', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 25, '', '', NULL, 0),
(24, 27, 'Intas Pharma Ahmedabad', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(25, 27, 'Stedman Chennai - ', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 22, '', '', NULL, 0),
(26, 27, 'Jubilant Pharma', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', 25, '', '', NULL, 0),
(27, 28, 'Dr Bernad Shar Napolean - SRM Research Dean', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(28, 28, 'Dr Vedantham Srinivasan - research head - SIMS', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(29, 28, 'Rubber, Steel and Pharma companies - R&D', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(30, 28, 'Ashok Leyland R&D (Babu & Deepak too need it)', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(31, 29, 'Swastik corporation', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(32, 29, 'ABC Infra', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(33, 29, 'Navayuga Engineering ', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(34, 30, 'Students or colleges (11th and 12th )', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(35, 32, 'Any IT Company that does campus interview', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(36, 32, 'HR Head of Virtusa', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(37, 32, 'HR Head Azentio', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(38, 32, 'HR Head NOVAC Technologies Pvt', NULL, NULL, NULL, '2025-06-25', 'Specific Ask', NULL, '', NULL, NULL, 0),
(39, 39, 'Jai Balaji Fabricators', 'Sripathy Dinash', NULL, '9841727727', '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(40, 39, 'Kamal Auto Shade Applicator', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(41, 39, 'TCF (Twin City Fan) Manufacturer', 'Anantha Kumar', NULL, '+91 73582 48444', '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(42, 39, 'ARR', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(43, 39, 'Eco Care Engineering', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(44, 38, 'Kamal Auto Shade Applicator', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(45, 36, 'Raj Petro', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(46, 36, 'Sugar Mills', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(47, 36, 'Eco Care Engineering', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(48, 36, 'LOESCHE GmbH', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(49, 36, 'S&J ', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(50, 36, 'PVG Indo Tech Transformer', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', 41, '', 'Connected to MD', NULL, 0),
(51, 36, 'Pepperl+Fuchs', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(52, 34, 'Dr Reddys', 'MD', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(53, 34, 'CIPLA', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(54, 34, 'SHRI YATA Pvt Ltd', 'P', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(55, 33, 'DSSI ', NULL, NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(56, 33, 'CBRE', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(57, 38, 'Muruggapa Oregano', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(58, 38, 'PICO WaterChem Pvt Ltd', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(59, 40, 'Jai Balaji Fabricators', 'MD', NULL, NULL, '2025-09-02', 'Specific Ask', NULL, '', NULL, NULL, 0),
(60, 40, 'Sri Sakthi Industries', 'Purchase Head', NULL, NULL, '2025-09-02', 'Specific Ask', 36, '', 'Connected to MD', NULL, 0),
(61, 36, 'NLC corporation', 'Maintenance Head', NULL, NULL, '2025-09-02', 'Specific Ask', 35, '', 'connected to SriKrishna 9246567166 Maintenance Head ', NULL, 0),
(62, 26, 'Pondicherry University', 'Mahesh', NULL, '9944757565', '2025-09-04', 'Specific Give', 18, '9999999999', 'erw', NULL, 0),
(63, 26, 'ORIENT Electronics', 'Venkatesh', NULL, '9524733999', '2025-09-04', 'Specific Give', NULL, '', NULL, NULL, 0),
(64, 26, 'NIFT', 'Purchase Head', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(65, 26, 'NIOT', 'Purchase Head', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(66, 32, 'Drivestream ', 'Sheik', NULL, '9962789412', '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(67, 32, 'CIPLA', 'HR', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(68, 32, 'PRACTO', 'HR', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(69, 29, 'SWASTIK Operations', 'IT Head', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(70, 29, 'VAHWAN Cybertec', 'IT HEAD', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(71, 29, 'AZIRO ', 'IT Head', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(72, 30, 'Annaadarsh College', 'Placement Officer / HoD', NULL, NULL, '2025-09-04', 'Specific Ask', NULL, '', NULL, NULL, 0),
(73, 30, 'Rajalakshmi Eng College', NULL, NULL, NULL, '2025-09-04', 'Specific Ask', 29, '', NULL, NULL, 0),
(74, 18, 'Yanmar', 'IT Head', NULL, NULL, '2025-09-04', 'Specific Ask', 18, '9500909030', '1', NULL, 0),
(75, 18, 'Casy Forge Pvt Ltd ', NULL, NULL, NULL, '2025-09-18', 'Specific Ask', 32, '', NULL, NULL, 0),
(76, 22, 'Auro BPO Services', NULL, NULL, NULL, '2025-09-18', 'Specific Ask', 18, '2345678907', '', NULL, 0),
(77, 25, 'Ucal Pvt ltd', 'MD', NULL, NULL, '2025-09-18', 'Specific Ask', NULL, '', NULL, NULL, 0),
(78, 32, 'Lupin Pharma', NULL, NULL, NULL, '2025-09-18', 'Specific Ask', NULL, '', NULL, NULL, 0),
(79, 28, 'Sri venkateswara dental college, Pondicherry', 'Dr JOYSON MOSES', NULL, '9444178136', '2025-09-18', 'Specific Give', 18, '9876543210', '', NULL, 0),
(82, 47, 'tcs', '99319383838', NULL, NULL, '2025-10-09', 'Specific Ask', 43, '9941859792', 'CEO', NULL, 2),
(83, 43, 'ibm', 'moorthy', 'moorthy@gmail.com', '1234567890', '2025-10-09', 'Specific Ask', 47, '1212121212', 'tescribe sowmya', NULL, 0),
(84, 43, 'tcs', 'xyz', 'test@gmail.com', '1234567889', '2025-10-09', 'Specific Give', NULL, '', NULL, NULL, 0),
(85, 43, 'testing', 'gives', 'testing@gmail.com', '323232323', '2025-10-09', 'Specific Give', NULL, '', NULL, NULL, 0),
(86, 46, 'hyundai', 'vinoth', 'test@gmail.com', '121212121', '2025-10-10', 'Specific Give', 47, '0883899574', '', NULL, 0),
(87, 47, 'HCL', 'HCL', NULL, NULL, '2025-10-15', 'Specific Give', NULL, '', NULL, NULL, 0),
(88, 47, 'xyz', 'abc', 'abc@gmail.com', '987654321', '2025-10-17', 'Specific Ask', 43, '3453453456', 'teting', NULL, 0),
(89, 56, 'oooooo', 'vvvvvv', 'vvvv@gmail.com', '33333333333', '2025-10-17', 'Specific Give', 43, '9999999777', '97676767', NULL, 0),
(90, 18, 'ELGI Equipments', NULL, NULL, NULL, '2025-10-23', 'Specific Ask', NULL, '', NULL, NULL, 0),
(91, 25, 'The Eye Foundation', NULL, NULL, NULL, '2025-10-23', 'Specific Ask', NULL, '', NULL, NULL, 0),
(92, 27, 'FDC Pharmaceuticals Mumbai', 'IT Head', NULL, NULL, '2025-10-23', 'Specific Ask', NULL, '', NULL, NULL, 0),
(93, 29, 'MyKrane India', 'IT, CxO', NULL, NULL, '2025-10-23', 'Specific Ask', NULL, '', NULL, NULL, 0),
(94, 32, 'Sutherland Pvt Ltd', NULL, NULL, NULL, '2025-10-23', 'Specific Ask', NULL, '', NULL, NULL, 0),
(95, 57, 'tesla', 'elon musk', NULL, NULL, '2025-10-24', 'Specific Ask', 43, '1233445458', '22343432', NULL, 0),
(96, 56, 'abc', 'testing 1', 'testing@gmail.com', '2345', '2025-10-24', 'Specific Give', NULL, '', NULL, NULL, 0),
(97, 56, 'cdef', 'yanni', 'yanni@gmail.com', '989898989898', '2025-10-24', 'Specific Give', NULL, '', NULL, NULL, 0),
(98, 40, 'KEC international', NULL, NULL, NULL, '2025-10-28', 'Specific Ask', NULL, '', NULL, NULL, 0),
(99, 40, 'Crednza Engineering pvt ltd', NULL, NULL, NULL, '2025-10-28', 'Specific Ask', NULL, '', NULL, NULL, 0),
(100, 58, 'MDQuality Apps Solutions', 'test', 'test@gmail.com', '34546567788', '2025-10-31', 'Specific Ask', NULL, '', NULL, NULL, 0),
(101, 27, 'mdq', 'ut', '', '', '2025-10-31', 'Specific Ask', NULL, '', NULL, NULL, 0),
(102, 22, 'E-Care India', NULL, NULL, NULL, '2025-11-13', 'Specific Ask', 18, '9941859792', 'COO', NULL, 0),
(103, 26, 'Jhost India Pvt Ltd', NULL, NULL, NULL, '2025-11-13', 'Specific Ask', NULL, '', NULL, NULL, 0),
(104, 31, 'Smarther ', NULL, NULL, NULL, '2025-11-13', 'Specific Ask', NULL, '', NULL, NULL, 0),
(105, 59, 'St Particks School, Pondy', 'Salomon', NULL, '7060102039', '2025-12-30', 'Specific Give', NULL, '', NULL, NULL, 0),
(106, 71, 'SBI', 'Yashna Bafna', NULL, NULL, '2026-01-09', 'Specific Ask', 67, '', '', NULL, 0),
(107, 67, 'Tokyo Semi conductors', NULL, NULL, NULL, '2026-01-09', 'Specific Give', NULL, '', NULL, NULL, 0),
(108, 67, 'Proton cancer research', NULL, NULL, NULL, '2026-01-09', 'Specific Give', NULL, '', NULL, NULL, 0),
(109, 62, 'Proton cancer research', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(110, 65, 'RBI AGM', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', 71, '', '', NULL, 0),
(111, 62, 'OP JINDAL UNIVERSITY, HARYANA', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(112, 62, 'National law school of India university, Bangalore', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(113, 62, 'Asian African Chamber of Commerce', 'Jani Jermans', NULL, NULL, '2026-01-09', 'Specific Give', NULL, '', NULL, NULL, 0),
(114, 69, 'Farmland tieup', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(115, 69, 'Gokul Steel', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(116, 69, 'NA', 'Sushil', NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(117, 72, 'Good shephard school', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(118, 72, 'L&T', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(119, 62, 'Billroth Hospitals', NULL, NULL, NULL, '2026-01-09', 'Specific Ask', NULL, '', NULL, NULL, 0),
(120, 75, 'Tata', 'Suresh', NULL, '9876543210', '2026-04-07', 'Specific Ask', 73, '1234567890', 'test', NULL, 0),
(121, 78, 'IT Company', NULL, NULL, NULL, '2026-04-07', 'Specific Ask', NULL, '', NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `referral_connections`
--

CREATE TABLE `referral_connections` (
  `id` int(11) NOT NULL,
  `referral_id` int(11) NOT NULL,
  `connected_by` int(11) DEFAULT NULL,
  `assigned_member` int(11) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `designation` varchar(250) DEFAULT NULL,
  `email` varchar(250) DEFAULT NULL,
  `read` int(11) NOT NULL DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `referral_connections`
--

INSERT INTO `referral_connections` (`id`, `referral_id`, `connected_by`, `assigned_member`, `mobile`, `remarks`, `designation`, `email`, `read`, `created_at`, `updated_at`) VALUES
(104, 100, NULL, 58, '34546567788', NULL, 'HR', 'test@gmail.com', 0, '2025-10-31 06:17:20', '2025-10-31 06:17:20'),
(105, 100, 18, 58, '8765765834', 'tre', 'Admin', 'admin@gmail.com', 0, '2025-10-31 06:18:21', '2025-10-31 06:18:21'),
(106, 100, 18, 58, '6857646534', 'fg', 'Ceo', 'ceo@gmail.com', 0, '2025-10-31 06:20:19', '2025-10-31 06:20:19'),
(107, 62, 18, 26, '9897654324', 'test', 'Admin', 'admin@gmail.com', 0, '2025-10-31 06:23:04', '2025-10-31 06:23:04'),
(108, 62, 18, 26, '9897654324', 'test', 'Admin', 'admin@gmail.com', 0, '2025-10-31 06:23:04', '2025-10-31 06:23:04'),
(109, 62, 18, 26, '9897654324', 'test', 'Admin', 'admin@gmail.com', 0, '2025-10-31 06:23:06', '2025-10-31 06:23:06'),
(110, 62, 18, 26, '9897654324', 'test', 'Admin', 'admin@gmail.com', 0, '2025-10-31 06:23:06', '2025-10-31 06:23:06'),
(111, 62, 18, 26, '9897654324', 'test', 'Admin', 'admin@gmail.com', 0, '2025-10-31 06:23:06', '2025-10-31 06:23:06'),
(112, 101, 18, 27, '', NULL, 'hr', NULL, 0, '2025-10-31 12:58:23', '2025-10-31 12:58:23'),
(113, 101, 34, 27, '0987654365', '', 'admin', 'Ganesh@gmail.com', 0, '2025-10-31 12:59:15', '2025-10-31 12:59:15');

-- --------------------------------------------------------

--
-- Table structure for table `region`
--

CREATE TABLE `region` (
  `id` int(20) NOT NULL,
  `rvalue` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `region`
--

INSERT INTO `region` (`id`, `rvalue`) VALUES
(1, 'North');

-- --------------------------------------------------------

--
-- Table structure for table `request_referral`
--

CREATE TABLE `request_referral` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `referral_id` int(11) NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 1,
  `connections_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `request_referral`
--

INSERT INTO `request_referral` (`id`, `user_id`, `referral_id`, `status`, `connections_id`, `created_at`, `updated_at`) VALUES
(11, 18, 100, 2, 106, '2025-10-31 06:20:19', '2025-10-31 06:20:19'),
(12, 18, 62, 2, 107, '2025-10-31 06:23:04', '2025-10-31 06:23:04'),
(13, 18, 62, 2, 108, '2025-10-31 06:23:04', '2025-10-31 06:23:04'),
(14, 18, 62, 2, 109, '2025-10-31 06:23:06', '2025-10-31 06:23:06'),
(15, 18, 62, 2, 110, '2025-10-31 06:23:06', '2025-10-31 06:23:06'),
(16, 18, 62, 2, 111, '2025-10-31 06:23:06', '2025-10-31 06:23:06'),
(17, 58, 62, 1, 111, '2025-10-31 06:24:13', '2025-10-31 06:24:13'),
(18, 18, 101, 2, 113, '2025-10-31 12:59:15', '2025-10-31 12:59:15');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `username_2` (`username`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `chapters`
--
ALTER TABLE `chapters`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `meeting_planner`
--
ALTER TABLE `meeting_planner`
  ADD PRIMARY KEY (`id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `powerteam`
--
ALTER TABLE `powerteam`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `power_dates`
--
ALTER TABLE `power_dates`
  ADD PRIMARY KEY (`id`),
  ADD KEY `organiser_id` (`organiser_id`);

--
-- Indexes for table `power_date_members`
--
ALTER TABLE `power_date_members`
  ADD PRIMARY KEY (`id`),
  ADD KEY `power_date_id` (`power_date_id`);

--
-- Indexes for table `referrals`
--
ALTER TABLE `referrals`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `referral_connections`
--
ALTER TABLE `referral_connections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `referral_id` (`referral_id`);

--
-- Indexes for table `region`
--
ALTER TABLE `region`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `request_referral`
--
ALTER TABLE `request_referral`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `chapters`
--
ALTER TABLE `chapters`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `meeting_planner`
--
ALTER TABLE `meeting_planner`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `powerteam`
--
ALTER TABLE `powerteam`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `power_dates`
--
ALTER TABLE `power_dates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `power_date_members`
--
ALTER TABLE `power_date_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `referrals`
--
ALTER TABLE `referrals`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=122;

--
-- AUTO_INCREMENT for table `referral_connections`
--
ALTER TABLE `referral_connections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

--
-- AUTO_INCREMENT for table `region`
--
ALTER TABLE `region`
  MODIFY `id` int(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `request_referral`
--
ALTER TABLE `request_referral`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `meeting_planner`
--
ALTER TABLE `meeting_planner`
  ADD CONSTRAINT `meeting_planner_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `power_dates`
--
ALTER TABLE `power_dates`
  ADD CONSTRAINT `power_dates_ibfk_1` FOREIGN KEY (`organiser_id`) REFERENCES `members` (`id`);

--
-- Constraints for table `power_date_members`
--
ALTER TABLE `power_date_members`
  ADD CONSTRAINT `power_date_members_ibfk_1` FOREIGN KEY (`power_date_id`) REFERENCES `power_dates` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `request_referral`
--
ALTER TABLE `request_referral`
  ADD CONSTRAINT `fk_referral` FOREIGN KEY (`referral_id`) REFERENCES `referrals` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

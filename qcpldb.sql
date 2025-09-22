-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 04:08 PM
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
-- Database: `qcpldb`
--

-- --------------------------------------------------------

--
-- Table structure for table `annoucements`
--

CREATE TABLE `annoucements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `announcement` text NOT NULL,
  `video` varchar(255) DEFAULT NULL,
  `expiry` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `annoucements`
--

INSERT INTO `annoucements` (`id`, `title`, `announcement`, `video`, `expiry`, `created_at`, `updated_at`) VALUES
(6, 'Chainsaw Man - The Movie: Reze Arc', 'Denji encounters a new romantic interest, Reze, who works at a coffee café.', NULL, '2025-09-23 15:00:00', '2025-09-22 21:32:36', '2025-09-22 21:33:34');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `id` int(11) NOT NULL,
  `title` varchar(250) DEFAULT NULL,
  `author` varchar(250) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `category` enum('Adventure','Anthropology','Art & Architecture','Autobiography','Biography','Business & Economics','Classic Literature','Cooking/Food','Dystopian','Fantasy','Graphic Novels & Comics','Horror','History','') NOT NULL,
  `coverpic` blob NOT NULL,
  `description` varchar(500) NOT NULL,
  `expiry` datetime NOT NULL,
  `status` enum('Available','Borrowed','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`id`, `title`, `author`, `cover`, `category`, `coverpic`, `description`, `expiry`, `status`) VALUES
(22, 'Chainsaw Man - The Movie: Reze Arc', 'Tatsuki Fujimoto & Hiroshi Seko', 'Chainsaw_Man_-_The_Movie_Reze_Arc_1758548151.jpg', 'Graphic Novels & Comics', 0x436861696e7361775f4d616e5f2d5f5468655f4d6f7669655f52657a655f4172635f313735383534383135312e6a7067, '', '2025-09-24 15:00:00', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `footer`
--

CREATE TABLE `footer` (
  `id` int(11) NOT NULL,
  `message` varchar(250) NOT NULL,
  `expiry` datetime NOT NULL,
  `scroll_speed` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `footer`
--

INSERT INTO `footer` (`id`, `message`, `expiry`, `scroll_speed`) VALUES
(3, 'Denji encounters a new romantic interest, Reze, who works at a coffee café', '2025-09-24 15:00:00', 50);

-- --------------------------------------------------------

--
-- Table structure for table `login_tbl`
--

CREATE TABLE `login_tbl` (
  `ID` int(11) NOT NULL,
  `Admin_name` varchar(250) NOT NULL,
  `District` varchar(250) NOT NULL,
  `Branch` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `login_tbl`
--

INSERT INTO `login_tbl` (`ID`, `Admin_name`, `District`, `Branch`) VALUES
(1, 'admin1', 'District 1', 'District Library - Project 8 Branch'),
(2, 'admin2', 'District 1', 'Nayong Kanluran Branch'),
(3, 'admin3', 'District 1', 'Bagong Pag-Asa Branch (Under Renovation)'),
(4, 'admin4', 'District 1', 'Balingasa Branch'),
(5, 'admin5', 'District 1', 'Masambong Branch'),
(6, 'admin6', 'District 1', 'Veterans Library/Project 7 Branch'),
(7, 'admin7', 'District 2', 'District Library - Payatas Lupang Pangako Branch'),
(8, 'admin8', 'District 2', 'Payatas Landfill Branch (Under Renovation)'),
(9, 'admin9', 'District 2', 'Bagong Silangan Branch'),
(10, 'admin10', 'District 2', 'Holy Spirit Branch'),
(11, 'admin11', 'District 3', 'District Library - Greater Project 4 Branch'),
(12, 'admin12', 'District 3', 'Escopa 2 Branch'),
(13, 'admin13', 'District 3', 'Escopa 3 Branch (Under Renovation)'),
(14, 'admin14', 'District 3', 'Tagumpay Branch'),
(15, 'admin15', 'District 3', 'Libis Branch'),
(16, 'admin16', 'District 3', 'Matandang Balara Branch'),
(17, 'admin17', 'District 4', 'District Library - Cubao Branch'),
(18, 'admin18', 'District 4', 'Krus Na Ligas Branch'),
(19, 'admin19', 'District 4', 'Roxas Branch'),
(20, 'admin20', 'District 4', 'San Isidro-Galas Branch'),
(21, 'admin21', 'District 4', 'UP Pook Amorsolo Branch'),
(22, 'admin22', 'District 4', 'UP Pook Dagohoy Branch'),
(23, 'admin23', 'District 4', 'Camp Karingal Women\'s Dormitory Branch'),
(24, 'admin24', 'District 5', 'District Library - Lagro Branch'),
(25, 'admin25', 'District 5', 'Novaliches Branch (Under Renovation)'),
(26, 'admin26', 'District 5', 'North Fairview Branch'),
(27, 'admin27', 'District 6', 'District Library - Pasong Tamo Branch'),
(28, 'admin28', 'District 6', 'Talipapa Branch'),
(29, 'admin29', 'District 6', 'Sagana Homes 1 Branch'),
(30, 'MBAdmin', 'Main Branch', 'Main Branch');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_videos`
--

CREATE TABLE `tbl_videos` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `video` varchar(255) NOT NULL,
  `expiry` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `tbl_videos`
--

INSERT INTO `tbl_videos` (`id`, `title`, `description`, `video`, `expiry`, `created_at`) VALUES
(4, 'Chainsaw Man - The Movie: Reze Arc', 'Denji encounters a new romantic interest, Reze, who works at a coffee café.', 'vid_68d151503a2e83.50910731.mp4', '2025-09-24 15:00:00', '2025-09-22 21:38:24');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annoucements`
--
ALTER TABLE `annoucements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `footer`
--
ALTER TABLE `footer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `login_tbl`
--
ALTER TABLE `login_tbl`
  ADD PRIMARY KEY (`ID`);

--
-- Indexes for table `tbl_videos`
--
ALTER TABLE `tbl_videos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `annoucements`
--
ALTER TABLE `annoucements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `footer`
--
ALTER TABLE `footer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tbl_videos`
--
ALTER TABLE `tbl_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

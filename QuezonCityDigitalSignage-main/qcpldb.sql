-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 22, 2025 at 02:03 PM
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
(2, 'bobo ka a', 'asdsad', NULL, '2025-09-21 19:44:00', '2025-09-22 15:44:14', '2025-09-22 15:44:35'),
(3, 'asdasd', 'sadas', NULL, NULL, '2025-09-22 16:55:07', '2025-09-22 16:55:07'),
(4, 'asdas', 'asdasd', NULL, '2025-09-22 05:30:00', '2025-09-22 17:30:14', '2025-09-22 17:30:14'),
(5, 'asdas', 'asdasd', NULL, NULL, '2025-09-22 19:42:03', '2025-09-22 19:42:03');

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
(15, 'sada', 'asda', 'Isagi_Yoichi_1758534308.jpg', 'Anthropology', 0x49736167695f596f696368695f313735383533343330382e6a7067, '', '2025-09-22 17:44:00', 'Borrowed'),
(16, '1', '1', 'Isagi_Yoichi_1758534334.jpg', 'Adventure', 0x49736167695f596f696368695f313735383533343333342e6a7067, '', '2025-09-22 17:45:00', 'Available'),
(17, '3', '3', 'Uzen_Kyoka_1758534506.jpg', 'Adventure', 0x557a656e5f4b796f6b615f313735383533343530362e6a7067, '', '0000-00-00 00:00:00', 'Available'),
(18, '4', '4', '_________________________1758535212.jpg', 'Adventure', 0x5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f5f313735383533353231322e6a7067, '', '2025-09-22 18:00:00', 'Available'),
(19, '5', '5', 'Kurapika_icon_1758535423.jpg', 'Adventure', 0x4b75726170696b615f69636f6e5f313735383533353432332e6a7067, '', '0000-00-00 00:00:00', 'Available'),
(20, 'Puff', 'Supafly', 'download__1__1758538228.jpg', 'Classic Literature', 0x646f776e6c6f61645f5f315f5f313735383533383232382e6a7067, '', '2025-10-01 06:00:00', 'Borrowed'),
(21, 'Meditation', 'Marcus Aurelius', 'Marcus_1758538533.jpg', 'History', 0x4d61726375735f313735383533383533332e6a7067, '', '2025-09-24 07:00:00', 'Available');

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
(1, 'WEHH', '2025-09-22 19:14:00', 20),
(2, 'Bad apple', '2025-09-22 19:40:00', 50);

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
(29, 'admin29', 'District 6', 'Sagana Homes 1 Branch');

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
(2, 'asdas', 'asdas', 'vid_68d117459b3df3.68726972.mp4', '2025-09-22 17:30:00', '2025-09-22 17:30:45'),
(3, 'asdas', 'asdasd', 'vid_68d136240169c2.91425508.mp4', NULL, '2025-09-22 19:42:28');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `footer`
--
ALTER TABLE `footer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tbl_videos`
--
ALTER TABLE `tbl_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

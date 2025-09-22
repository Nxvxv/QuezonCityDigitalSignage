-- phpMyAdmin SQL Dump
-- version 4.7.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 19, 2025 at 10:04 AM
-- Server version: 10.1.30-MariaDB
-- PHP Version: 7.2.1

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `title` varchar(250) NOT NULL,
  `author` varchar(250) NOT NULL,
  `cover` varchar(255) DEFAULT NULL,
  `category` enum('Adventure','Anthropology','Art & Architecture','Autobiography','Biography','Business & Economics','Classic Literature','Cooking/Food','Dystopian','Fantasy','Graphic Novels & Comics','Horror','History','') NOT NULL,
  `coverpic` blob NOT NULL,
  `description` varchar(500) NOT NULL,
  `expiry` datetime NOT NULL,
  `status` enum('Available','Borrowed','','') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`title`, `author`, `cover`, `category`, `coverpic`, `description`, `expiry`, `status`) VALUES
('asasasa', 'asasa', 'cover_54bd48b9a84591d4.png', 'Dystopian', '', '', '2025-09-19 16:32:00', 'Available'),
('sasdada', 'dasdasdsa', 'cover_6be2a316a30e8574.png', 'Fantasy', '', '', '2025-09-19 17:33:00', 'Borrowed'),
('fdfwdf', 'dfdsfdsfds', 'cover_5d54c9f9cea79050.png', 'Fantasy', '', '', '2025-09-25 14:34:00', 'Available'),
('asdasd', 'sasdas', 'cover_f5b581efb2b0f78c.jpg', 'Graphic Novels & Comics', '', '', '2025-09-19 06:46:00', 'Borrowed'),
('sadas', 'sadas', NULL, 'Graphic Novels & Comics', '', '', '2025-09-19 05:56:00', 'Available'),
('sasasa', 'asasasas', 'cover_75c53fccb017b0f3.png', 'Horror', '', '', '2025-09-19 18:08:00', 'Available'),
('tite', 'zszsasa', 'cover_5a16479a13299928.png', 'Graphic Novels & Comics', '', '', '2025-09-19 20:45:00', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `footer`
--

CREATE TABLE `footer` (
  `message` varchar(250) NOT NULL,
  `expiry` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Table structure for table `login_tbl`
--

CREATE TABLE `login_tbl` (
  `ID` int(11) NOT NULL,
  `Admin_name` varchar(250) NOT NULL,
  `District` varchar(250) NOT NULL,
  `Branch` varchar(250) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `annoucements`
--
ALTER TABLE `annoucements`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `login_tbl`
--
ALTER TABLE `login_tbl`
  MODIFY `ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `tbl_videos`
--
ALTER TABLE `tbl_videos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

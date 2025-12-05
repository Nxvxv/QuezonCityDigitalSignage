-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 05, 2025 at 01:31 AM
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
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `AnnouncementId` int(11) NOT NULL,
  `BranchId` int(11) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Content` text DEFAULT NULL,
  `ExpiryDate` datetime DEFAULT NULL,
  `TextSize` enum('SMALL','MEDIUM','LARGE') NOT NULL DEFAULT 'MEDIUM',
  `DatePosted` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`AnnouncementId`, `BranchId`, `Title`, `Content`, `ExpiryDate`, `TextSize`, `DatePosted`) VALUES
(1, 1, 'Chainsaman', 'The Chainsaw Man: The Movie: Reze Arc movie is about the teenage devil hunter Denji as he gets involved with a mysterious girl named Reze, which leads to a brutal conflict between devil hunters and secret enemies. The film is a direct continuation of the first anime season, focusing on Denji\'s new romantic interest while he is still torn between her and his crush, Makima. It is described as a high-octane, chaotic experience with a slower, dialogue-heavy first half followed by intense, action-packed mayhem in the second half', '2025-12-24 05:13:00', '', '2025-12-01 13:13:52'),
(20, 9, 'sadas', 'sadas', NULL, 'MEDIUM', '2025-12-03 14:44:30');

-- --------------------------------------------------------

--
-- Table structure for table `books`
--

CREATE TABLE `books` (
  `BookId` int(11) NOT NULL,
  `BranchId` int(11) NOT NULL,
  `Title` varchar(255) DEFAULT NULL,
  `Author` varchar(255) DEFAULT NULL,
  `Category` varchar(255) DEFAULT NULL,
  `Description` text DEFAULT NULL,
  `Availability` enum('Available','Unavailable') NOT NULL DEFAULT 'Available',
  `ExpiryDate` datetime DEFAULT NULL,
  `CoverImage` blob DEFAULT NULL,
  `YearPublished` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `books`
--

INSERT INTO `books` (`BookId`, `BranchId`, `Title`, `Author`, `Category`, `Description`, `Availability`, `ExpiryDate`, `CoverImage`, `YearPublished`) VALUES
(4, 1, 'sad', 'asda', 'ddasd', 'das', 'Available', NULL, 0x626f6f6b5f36393264346231643937613831332e36333631313732372e6a7067, NULL),
(6, 1, 'Meditation', 'Marcus Aurelius', 'Philosophy', 'Five of the main themes in this book are: change, death and the shortness of life; the role and importance of the rational mind and will; dealing with others and accepting their shortcomings; avoiding the chase for pleasure and fame; and living according to nature and fully accepting its course.', 'Available', NULL, 0x626f6f6b5f36393266643963663739333937332e32343630383339352e6a706567, NULL),
(7, 1, 'Adventure time', 'N/A', 'Comics', 'Adventure time', 'Available', NULL, 0x626f6f6b5f36393266643966396130653638332e33333134323236362e6a7067, NULL),
(8, 1, 'Bible', 'God', 'Religion', '', 'Available', NULL, 0x626f6f6b5f36393266646161303736623435372e31363232333735352e6a7067, NULL),
(9, 1, 'Chainsaw man', 'Fujimoto', 'Comics', '', 'Available', NULL, 0x626f6f6b5f36393266646164306233326164392e33393831393539312e6a7067, NULL),
(10, 1, 'The Art of War', 'Sun Tzu', 'History and Philosophy', 'The Art of War', 'Available', NULL, 0x626f6f6b5f36393266646166663534336436322e31373236303634392e6a7067, NULL),
(11, 1, 'The art of not giving of f *ck', 'Mark Manson', 'Self help book', 'Teaching of not giving of f*ck', 'Available', NULL, 0x626f6f6b5f36393266646235636337346161302e33353030393733352e6a7067, NULL),
(12, 1, 'The Dark Tower: The Gunslinger', 'Stephen King', 'Fantas y', 'The book tells the story of The Gunslinger, Roland of Gilead, and his quest to catch the man in black, the first of many steps toward Roland\'s ultimate destination, The Dark Tower', 'Available', NULL, 0x626f6f6b5f36393266646237383433613234382e30313239383635382e6a7067, NULL),
(13, 1, 'Bluelock', 'Ego', 'Manga', 'Isagi dream about to become a great striker, using his iq in field', 'Available', NULL, 0x626f6f6b5f36393266653033393762623332332e33373336303438312e6a7067, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branches`
--

CREATE TABLE `branches` (
  `BranchId` int(11) NOT NULL,
  `DistrictId` int(11) NOT NULL,
  `BranchName` varchar(300) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `branches`
--

INSERT INTO `branches` (`BranchId`, `DistrictId`, `BranchName`) VALUES
(1, 1, 'Bagong Pag-Asa Branch (Under Renovation)'),
(2, 2, 'Bagong Silangan Branch'),
(3, 1, 'Balingasa Branch'),
(4, 4, 'Camp Karingal Women\'s Dormitory Branch'),
(5, 4, 'District Library - Cubao Branch'),
(6, 3, 'District Library - Greater Project 4 Branch'),
(7, 5, 'District Library - Lagro Branch'),
(8, 6, 'District Library - Pasong Tamo Branch'),
(9, 2, 'District Library - Payatas Lupang Pangako Branch'),
(10, 1, 'District Library - Project 8 Branch'),
(11, 3, 'Escopa 2 Branch'),
(12, 3, 'Escopa 3 Branch (Under Renovation)'),
(13, 2, 'Holy Spirit Branch'),
(14, 4, 'Krus Na Ligas Branch'),
(15, 3, 'Libis Branch'),
(16, 7, 'Main Branch'),
(17, 1, 'Masambong Branch'),
(18, 3, 'Matandang Balara Branch'),
(19, 1, 'Nayong Kanluran Branch'),
(20, 5, 'North Fairview Branch'),
(21, 5, 'Novaliches Branch (Under Renovation)'),
(22, 2, 'Payatas Landfill Branch (Under Renovation)'),
(23, 4, 'Roxas Branch'),
(24, 6, 'Sagana Homes 1 Branch');

-- --------------------------------------------------------

--
-- Table structure for table `districts`
--

CREATE TABLE `districts` (
  `DistrictId` int(11) NOT NULL,
  `DistrictName` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `districts`
--

INSERT INTO `districts` (`DistrictId`, `DistrictName`) VALUES
(1, 'District 1'),
(2, 'District 2'),
(3, 'District 3'),
(4, 'District 4'),
(5, 'District 5'),
(6, 'District 6'),
(7, 'Main Branch');

-- --------------------------------------------------------

--
-- Table structure for table `footer`
--

CREATE TABLE `footer` (
  `FooterId` int(11) NOT NULL,
  `BranchId` int(11) NOT NULL,
  `ScrollSpeed` tinyint(1) NOT NULL DEFAULT 2,
  `ExpiryDate` datetime DEFAULT NULL,
  `Content` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `footer`
--

INSERT INTO `footer` (`FooterId`, `BranchId`, `ScrollSpeed`, `ExpiryDate`, `Content`) VALUES
(15, 1, 12, NULL, 'Chainsaw man movie premier');

-- --------------------------------------------------------

--
-- Table structure for table `logincredentials`
--

CREATE TABLE `logincredentials` (
  `LoginId` int(11) NOT NULL,
  `Username` varchar(100) NOT NULL,
  `PasswordHash` varchar(255) NOT NULL,
  `DistrictId` int(11) DEFAULT NULL,
  `BranchId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logincredentials`
--

INSERT INTO `logincredentials` (`LoginId`, `Username`, `PasswordHash`, `DistrictId`, `BranchId`) VALUES
(1, 'd1b1', '1234', 1, 1),
(2, 'd1b2', '1234', 1, 3),
(3, 'd1b3', '1234', 1, 10),
(4, 'd1b4', '1234', 1, 17),
(5, 'd1b5', '1234', 1, 19),
(6, 'd2b1', '1234', 2, 2),
(7, 'd2b2', '1234', 2, 9),
(8, 'd2b3', '1234', 2, 13),
(9, 'd2b4', '1234', 2, 22),
(10, 'd3b1', '1234', 3, 6),
(11, 'd3b2', '1234', 3, 11),
(12, 'd3b3', '1234', 3, 12),
(13, 'd3b4', '1234', 3, 15),
(14, 'd3b5', '1234', 3, 18),
(15, 'd4b1', '1234', 4, 4),
(16, 'd4b2', '1234', 4, 5),
(17, 'd4b3', '1234', 4, 14),
(18, 'd4b4', '1234', 4, 23),
(19, 'd5b1', '1234', 5, 7),
(20, 'd5b2', '1234', 5, 20),
(21, 'd5b3', '1234', 5, 21),
(22, 'd6b1', '1234', 6, 8),
(23, 'd6b2', '1234', 6, 24),
(24, 'd7b1', '1234', 7, 16);

-- --------------------------------------------------------

--
-- Table structure for table `videos`
--

CREATE TABLE `videos` (
  `VideoId` int(11) NOT NULL,
  `BranchId` int(11) NOT NULL,
  `Title` varchar(255) NOT NULL,
  `VideoFile` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `videos`
--

INSERT INTO `videos` (`VideoId`, `BranchId`, `Title`, `VideoFile`) VALUES
(10, 1, 'asd', 'vid_692feb8ada3bd1.51133649.mp4');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`AnnouncementId`),
  ADD KEY `fk_announcement_branch` (`BranchId`);

--
-- Indexes for table `books`
--
ALTER TABLE `books`
  ADD PRIMARY KEY (`BookId`),
  ADD KEY `fk_books_branch` (`BranchId`);

--
-- Indexes for table `branches`
--
ALTER TABLE `branches`
  ADD PRIMARY KEY (`BranchId`),
  ADD KEY `fk_branches_districts` (`DistrictId`);

--
-- Indexes for table `districts`
--
ALTER TABLE `districts`
  ADD PRIMARY KEY (`DistrictId`);

--
-- Indexes for table `footer`
--
ALTER TABLE `footer`
  ADD PRIMARY KEY (`FooterId`),
  ADD KEY `fk_footer_branch` (`BranchId`);

--
-- Indexes for table `logincredentials`
--
ALTER TABLE `logincredentials`
  ADD PRIMARY KEY (`LoginId`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `fk_login_district` (`DistrictId`),
  ADD KEY `fk_login_branch` (`BranchId`);

--
-- Indexes for table `videos`
--
ALTER TABLE `videos`
  ADD PRIMARY KEY (`VideoId`),
  ADD KEY `fk_videos_branch` (`BranchId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `AnnouncementId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `books`
--
ALTER TABLE `books`
  MODIFY `BookId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `branches`
--
ALTER TABLE `branches`
  MODIFY `BranchId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `districts`
--
ALTER TABLE `districts`
  MODIFY `DistrictId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `footer`
--
ALTER TABLE `footer`
  MODIFY `FooterId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `logincredentials`
--
ALTER TABLE `logincredentials`
  MODIFY `LoginId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `videos`
--
ALTER TABLE `videos`
  MODIFY `VideoId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `announcements`
--
ALTER TABLE `announcements`
  ADD CONSTRAINT `fk_announcement_branch` FOREIGN KEY (`BranchId`) REFERENCES `branches` (`BranchId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `books`
--
ALTER TABLE `books`
  ADD CONSTRAINT `fk_books_branch` FOREIGN KEY (`BranchId`) REFERENCES `branches` (`BranchId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `branches`
--
ALTER TABLE `branches`
  ADD CONSTRAINT `fk_branches_districts` FOREIGN KEY (`DistrictId`) REFERENCES `districts` (`DistrictId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `footer`
--
ALTER TABLE `footer`
  ADD CONSTRAINT `fk_footer_branch` FOREIGN KEY (`BranchId`) REFERENCES `branches` (`BranchId`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `logincredentials`
--
ALTER TABLE `logincredentials`
  ADD CONSTRAINT `fk_login_branch` FOREIGN KEY (`BranchId`) REFERENCES `branches` (`BranchId`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_login_district` FOREIGN KEY (`DistrictId`) REFERENCES `districts` (`DistrictId`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `videos`
--
ALTER TABLE `videos`
  ADD CONSTRAINT `fk_videos_branch` FOREIGN KEY (`BranchId`) REFERENCES `branches` (`BranchId`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

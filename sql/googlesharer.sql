-- phpMyAdmin SQL Dump
-- version 4.8.5
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Dec 13, 2019 at 07:31 AM
-- Server version: 10.3.13-MariaDB-log
-- PHP Version: 7.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `googlesharer`
--

-- --------------------------------------------------------

--
-- Table structure for table `mydrive`
--

CREATE TABLE `mydrive` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_size` varchar(255) NOT NULL,
  `file_mimeType` varchar(255) NOT NULL,
  `file_thumbnail` text DEFAULT NULL,
  `file_viewLink` mediumtext NOT NULL,
  `file_contentLink` mediumtext NOT NULL,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `data` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 ROW_FORMAT=COMPACT;

--
-- Dumping data for table `settings`
--

INSERT INTO `settings` (`id`, `name`, `data`) VALUES
(88, 'general', '{\"site_name\":\"GDSharer\",\"site_title\":\"Sharing files has never been this easy\",\"site_description\":\"Create permalinks easily by browsing your Google Drive files and folders. Just click the button below to connect.\",\"site_keywords\":\"bypas limit, google drive, google sharer, file sharer, share\",\"geonamesorg_username\":\"xxx\",\"logomark\":\"\",\"logotype\":\"\"}');

-- --------------------------------------------------------

--
-- Table structure for table `shared`
--

CREATE TABLE `shared` (
  `id` int(11) NOT NULL,
  `user_id` varchar(255) NOT NULL,
  `file_id` varchar(255) NOT NULL,
  `file_name` varchar(225) NOT NULL,
  `file_size` varchar(225) NOT NULL,
  `file_mimeType` varchar(225) NOT NULL,
  `file_thumbnail` text DEFAULT NULL,
  `file_viewLink` mediumtext NOT NULL,
  `file_contentLink` mediumtext NOT NULL,
  `slug` varchar(255) NOT NULL,
  `download_count` int(11) NOT NULL DEFAULT 0,
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` varchar(225) NOT NULL,
  `account_type` varchar(20) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(200) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `timezone` varchar(100) NOT NULL DEFAULT '''Asia/Jakarta''',
  `time_format` int(2) NOT NULL DEFAULT 24,
  `date_format` varchar(100) NOT NULL DEFAULT 'd F Y',
  `picture` varchar(225) NOT NULL DEFAULT '''avatar.png''',
  `token` text DEFAULT NULL,
  `is_active` int(11) NOT NULL,
  `last_login` datetime DEFAULT current_timestamp(),
  `modified` datetime NOT NULL DEFAULT current_timestamp(),
  `created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `mydrive`
--
ALTER TABLE `mydrive`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_id_2` (`file_id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `shared`
--
ALTER TABLE `shared`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `file_id_2` (`file_id`),
  ADD KEY `file_id` (`file_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `id` (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `mydrive`
--
ALTER TABLE `mydrive`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `settings`
--
ALTER TABLE `settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=90;

--
-- AUTO_INCREMENT for table `shared`
--
ALTER TABLE `shared`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

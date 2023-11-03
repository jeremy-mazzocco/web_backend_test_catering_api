-- phpMyAdmin SQL Dump
-- version 5.1.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 12, 2023 at 06:27 PM
-- Server version: 5.7.24
-- PHP Version: 8.1.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `catering_api`
--

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `facility_id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `employee`
--

-- Dumping data for table `employee`
INSERT INTO `employee` (`id`, `first_name`, `last_name`, `role_id`, `facility_id`, `email`) VALUES
(1, 'John', 'Doe', 1, 1, 'john.doe@example.com'),
(2, 'Jane', 'Doe', 2, 2, 'jane.doe@example.com'),
(3, 'Jim', 'Beam', 3, 3, 'jim.beam@example.com'),
(4, 'Jack', 'Daniels', 4, 4, 'jack.daniels@example.com'),
(5, 'Mary', 'Poppins', 5, 5, 'mary.poppins@example.com'),
(6, 'Harry', 'Potter', 7, 1, 'harry.potter@example.com'),
(7, 'Hermione', 'Granger', 7, 7, 'hermione.granger@example.com'),
(8, 'Ron', 'Weasley', 2, 1, 'ron.weasley@example.com'),
(9, 'Draco', 'Malfoy', 3, 2, 'draco.malfoy@example.com'),
(10, 'Neville', 'Longbottom', 4, 3, 'neville.longbottom@example.com'),
(11, 'Luna', 'Lovegood', 5, 4, 'luna.lovegood@example.com'),
(12, 'Ginny', 'Weasley', 1, 5, 'ginny.weasley@example.com'),
(13, 'Jeremy', 'Mazzocco', 1, 5, 'jeremy.mazzocco@example.com'),
(14, 'Fiona', 'Smith', 1, 6, 'fiona.smith@example.com'),
(15, 'Leonardo', 'Ricci', 1, 7, 'leonardo.ricci@example.com'),
(16, 'Sophie', 'Mercier', 4, 1, 'sophie.mercier@example.com'),
(17, 'Luis', 'Garcia', 3, 2, 'luis.garcia@example.com'),
(18, 'Anya', 'Ivanova', 2, 3, 'anya.ivanova@example.com'),
(19, 'Luke', 'Muller', 5, 4, 'luke.muller@example.com'),
(20, 'Isabella', 'Santos', 1, 5, 'isabella.santos@example.com'),
(21, 'Emily', 'Wang', 6, 6, 'emily.wang@example.com'),
(22, 'Madison', 'Johnson', 5, 7, 'madison.johnson@example.com'),
(23, 'Liam', 'OBrien', 1, 1, 'liam.obrien@example.com'),
(24, 'Olivia', 'Martinez', 1, 1, 'olivia.martinez@example.com'),
(25, 'Ethan', 'Zhang', 7, 3, 'ethan.zhang@example.com'),
(26, 'Ava', 'Magnusson', 6, 4, 'ava.magnusson@example.com'),
(27, 'Noah', 'Kumar', 5, 5, 'noah.kumar@example.com'),
(28, 'Emma', 'Van de Berg', 2, 7, 'emma.vandeberg@example.com'),
(29, 'Logan', 'Haddad', 5, 7, 'logan.haddad@example.com'),
(30, 'Charlotte', 'Wilson', 1, 1, 'charlotte.wilson@example.com'),
(31, 'Benjamin', 'Novak', 2, 2, 'benjamin.novak@example.com'),
(32, 'Mia', 'Horvat', 5, 1, 'mia.horvat@example.com'),
(33, 'Lucas', 'Silva', 4, 4, 'lucas.silva@example.com'),
(34, 'Amelia', 'Schmidt', 1, 5, 'amelia.schmidt@example.com'),
(35, 'Jacob', 'Papadopoulos', 1, 7, 'jacob.papadopoulos@example.com'),
(36, 'Harper', 'Nakamura', 6, 7, 'harper.nakamura@example.com'),
(37, 'Evelyn', 'Müller', 4, 1, 'evelyn.muller@example.com'),
(38, 'Matthew', 'Rossi', 3, 1, 'matthew.rossi@example.com'),
(39, 'Ella', 'Johansson', 2, 1, 'ella.johansson@example.com'),
(40, 'Alexander', 'Nielsen', 5, 4, 'alexander.nielsen@example.com'),
(41, 'Aria', 'García', 1, 5, 'aria.garcia@example.com'),
(42, 'Mason', 'Petrov', 6, 6, 'mason.petrov@example.com'),
(43, 'Victoria', 'Chen', 5, 7, 'victoria.chen@example.com');


-- --------------------------------------------------------

--
-- Table structure for table `role`
--

CREATE TABLE `role` (
  `id` int(11) NOT NULL,
  `position` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `role`
--

INSERT INTO `role` (`id`, `position`) VALUES
(7, 'Dishwasher'),
(6, 'Bartender'),
(5, 'Barista'),
(4, 'Waiter'),
(3, 'Chef'),
(2, 'Cook'),
(1, 'Menager');

-- --------------------------------------------------------

--
-- Table structure for table `facility`
--

CREATE TABLE `facility` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `creation_date` date NOT NULL,
  `location_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `facility`
--

INSERT INTO `facility` (`id`, `name`, `creation_date`, `location_id`) VALUES
(1, 'Amsterdam Riverside Venue', '2019-05-10', 1),
(2, 'Rotterdam Skyline Hall', '2017-07-07', 2),
(3, 'Utrecht Medieval Hall', '2018-09-15', 3),
(4, 'Eindhoven Modern Lounge', '2020-01-20', 4),
(5, 'The Hague Royal Banquet', '2016-03-30', 5),
(6, 'Groningen Market Venue', '2021-08-08', 6),
(7, 'Maastricht Classic Venue', '2019-10-10', 7),
(8, 'Amsterdam Floating Venue', '2022-04-04', 1),
(9, 'Rotterdam Harbor Events', '2018-02-02', 2),
(10, 'Utrecht Garden Parties', '2017-12-12', 3),
(11, 'Eindhoven Tech Events', '2020-06-16', 4),
(12, 'The Hague Beach Parties', '2019-11-11', 5),
(13, 'Groningen Rooftop Lounge', '2021-07-17', 6),
(14, 'Maastricht Underground Venue', '2018-08-18', 7),
(15, 'Amsterdam Museum Venue', '2017-05-05', 1),
(16, 'Amsterdam Light Venue', '2017-05-05', 1),
(17, 'Amsterdam Alley Venue', '2017-05-05', 1),
(18, 'Amsterdam Toni Venue', '2017-05-05', 1);

-- --------------------------------------------------------

--
-- Table structure for table `facility_tag`
--

CREATE TABLE `facility_tag` (
  `facility_id` int(11) NOT NULL,
  `tag_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `facility_tag`
--

INSERT INTO `facility_tag` (`facility_id`, `tag_id`) VALUES
(3, 1),
(5, 1),
(7, 1),
(9, 1),
(12, 1),
(2, 2),
(4, 2),
(8, 2),
(11, 2),
(12, 2),
(1, 3),
(9, 3),
(10, 3),
(11, 3),
(12, 3),
(6, 4),
(10, 4),
(8, 5),
(11, 5),
(12, 5),
(15, 5),
(16, 5),
(17, 5),
(18, 5);


-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `id` int(11) NOT NULL,
  `city` varchar(255) NOT NULL,
  `address` varchar(255) NOT NULL,
  `zip_code` varchar(10) NOT NULL,
  `country_code` varchar(3) NOT NULL,
  `phone_number` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `location`
--

INSERT INTO `location` (`id`, `city`, `address`, `zip_code`, `country_code`, `phone_number`) VALUES
(1, 'Amsterdam', '1 Dam Square', '1012 JL', 'NL', '+31-20-555-1234'),
(2, 'Rotterdam', '10 Maas Tower', '3011 AD', 'NL', '+31-10-555-5678'),
(3, 'Utrecht', '5 Dom Tower', '3512 JK', 'NL', '+31-30-555-9101'),
(4, 'Eindhoven', '20 Strijp-S', '5617 AB', 'NL', '+31-40-555-1213'),
(5, 'The Hague', '8 Binnenhof', '2513 AA', 'NL', '+31-70-555-1415'),
(6, 'Groningen', '18 Grote Market', '9712 CN', 'NL', '+31-50-555-1617'),
(7, 'Maastricht', '12 Vrijthof', '6211 LD', 'NL', '+31-43-555-1819');

-- --------------------------------------------------------

--
-- Table structure for table `tag`
--

CREATE TABLE `tag` (
  `id` int(11) NOT NULL,
  `name` varchar(64) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `tag`
--

INSERT INTO `tag` (`id`, `name`) VALUES
(4, 'Birthdays'),
(2, 'Corporate Events'),
(5, 'Outdoor'),
(3, 'Private Parties'),
(1, 'Weddings');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE users (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `username` VARCHAR(255) NOT NULL UNIQUE,
    `password_hash` VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`) VALUES
(1, 'test', '$2y$1cdcac');

-- --------------------------------------------------------

--
-- Table structure for table `access_tokens`
--

CREATE TABLE `access_tokens` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `token` VARCHAR(255) NOT NULL UNIQUE,
    `user_id` INT NOT NULL,
    `expiry` DATETIME NOT NULL,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Indexes for dumped tables
--
--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD KEY `facility_id` (`facility_id`);

--
-- Indexes for table `role`
--
ALTER TABLE `role`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `facility`
--
ALTER TABLE `facility`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`);

--
-- Indexes for table `facility_tag`
--
ALTER TABLE `facility_tag`
  ADD PRIMARY KEY (`facility_id`,`tag_id`),
  ADD KEY `tag_id` (`tag_id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tag`
--
ALTER TABLE `tag`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `role`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `facility`
--
ALTER TABLE `facility`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tag`
--
ALTER TABLE `tag`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--
--
-- Constraints for table `employee`
--
ALTER TABLE `employee`
  ADD CONSTRAINT `employee_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facility` (`id`),
   ADD CONSTRAINT `employee_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `role` (`id`);

--
-- Constraints for table `facility`
--
ALTER TABLE `facility`
  ADD CONSTRAINT `facility_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `location` (`id`);

--
-- Constraints for table `facility_tag`
--
ALTER TABLE `facility_tag`
  ADD CONSTRAINT `facility_tag_ibfk_1` FOREIGN KEY (`facility_id`) REFERENCES `facility` (`id`),
  ADD CONSTRAINT `facility_tag_ibfk_2` FOREIGN KEY (`tag_id`) REFERENCES `tag` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

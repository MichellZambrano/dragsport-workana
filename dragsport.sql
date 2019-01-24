-- phpMyAdmin SQL Dump
-- version 4.8.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 24, 2019 at 05:24 AM
-- Server version: 10.1.33-MariaDB
-- PHP Version: 7.2.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `dragsport`
--

-- --------------------------------------------------------

--
-- Table structure for table `social`
--

CREATE TABLE `social` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_social` varchar(100) DEFAULT NULL,
  `is_logged` enum('on','off') NOT NULL DEFAULT 'off',
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `social`
--

INSERT INTO `social` (`id_user`, `id_social`, `is_logged`, `created_at`) VALUES
(1, 'kwyv1e2e8fzg8bs87suk7pyx1', 'on', 1545137044);

-- --------------------------------------------------------

--
-- Table structure for table `sports`
--

CREATE TABLE `sports` (
  `id_sport` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `sports`
--

INSERT INTO `sports` (`id_sport`, `name`, `created_at`) VALUES
(1, 'Football', 1544880004),
(2, 'Basketball', 1544880011),
(3, 'Soccer', 1544880060),
(4, 'Kickball', 1544880141),
(5, 'Baseball', 1544880155);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(150) NOT NULL,
  `pass` varchar(90) DEFAULT NULL,
  `birthdate` int(10) UNSIGNED DEFAULT NULL,
  `gender` enum('male','female') NOT NULL DEFAULT 'male',
  `login_with` enum('email','fb','tw','tc') NOT NULL DEFAULT 'email',
  `social_id` varchar(150) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tmp_pass` varchar(90) DEFAULT NULL,
  `token` varchar(90) DEFAULT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id_user`, `first_name`, `last_name`, `email`, `pass`, `birthdate`, `gender`, `login_with`, `social_id`, `image`, `tmp_pass`, `token`, `created_at`) VALUES
(1, 'test', 'test', 'test@demo.com', '$2a$10$53b0c0ed679eabd8c2834OhWLHmbPHNik81JgFUX0Hmq7hSuOmTtO', 1544932800, 'male', 'email', NULL, NULL, NULL, NULL, 1544951171),
(2, 'bb9d201a4b', 'bb9d201a4b', 'bb9d201a4b@nicemail.pro', '$2a$10$d7fdc37456393ade274ebeTGkyXRPp0760Lq4duKXR0HLw3JXUAa6', 946699200, 'male', 'tc', '254800000', 'https://static-cdn.jtvnw.net/user-default-pictures/bb97f7e6-f11a-4194-9708-52bf5a5125e8-profile_image-300x300.jpg', NULL, NULL, 1545778457),
(3, 'DRASPORTS', 'dragsport', 'dragsport@gmail.com', '$2a$10$3e728ce6b64207b17cca8ugL0ovuT6Web9eEtMdXrEzcgjC0ZH8TK', 946699200, 'male', 'tw', '1072184041015779328', 'https://avatars.io/twitter/DRASPORTS/original', NULL, NULL, 1545779587),
(4, 'Armando', 'Amaya', 'armjaxd@hotmail.com', '$2a$10$4ecdbf274dfaf36dd0417ukZHsHfEE7Toq2M1enXUfPqmuc9C1WtC', 946699200, 'male', 'fb', '10218449797824110', 'https://graph.facebook.com/10218449797824110/picture?type=large', NULL, NULL, 1545781256);

-- --------------------------------------------------------

--
-- Table structure for table `user_sport`
--

CREATE TABLE `user_sport` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_sport` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `user_sport`
--

INSERT INTO `user_sport` (`id_user`, `id_sport`) VALUES
(1, 1),
(1, 2),
(1, 3),
(1, 4),
(1, 5),
(2, 1),
(2, 4),
(2, 5),
(3, 1),
(3, 4),
(3, 5),
(4, 2),
(4, 4);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `social`
--
ALTER TABLE `social`
  ADD KEY `id_user` (`id_user`);

--
-- Indexes for table `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id_sport`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- Indexes for table `user_sport`
--
ALTER TABLE `user_sport`
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_sport` (`id_sport`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `sports`
--
ALTER TABLE `sports`
  MODIFY `id_sport` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `social`
--
ALTER TABLE `social`
  ADD CONSTRAINT `social_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

--
-- Constraints for table `user_sport`
--
ALTER TABLE `user_sport`
  ADD CONSTRAINT `user_sport_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_sport_ibfk_2` FOREIGN KEY (`id_sport`) REFERENCES `sports` (`id_sport`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

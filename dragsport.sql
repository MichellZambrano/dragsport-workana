-- phpMyAdmin SQL Dump
-- version 4.8.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 09-12-2018 a las 04:37:53
-- Versión del servidor: 10.1.33-MariaDB
-- Versión de PHP: 7.2.6

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `dragsport`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
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
  `image` varchar(100) DEFAULT NULL,
  `tmp_pass` varchar(90) DEFAULT NULL,
  `token` varchar(90) DEFAULT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `first_name`, `last_name`, `email`, `pass`, `birthdate`, `gender`, `login_with`, `social_id`, `image`, `tmp_pass`, `token`, `created_at`) VALUES
(1, 'test', 'test', 'test@demo.com', '$2a$10$b65a06983a40dff7b1f7euys9aDoxG9OZF40MxpT9hYq861pb/M8K', 796363200, 'male', 'email', NULL, NULL, NULL, NULL, 1544101380),
(2, 'Armando', 'Amaya', 'armjaxd@hotmail.com', '$2a$10$7145785b291d2970bd0e9u4oh4qdcr48OtE0C3bIsIGoHrVMoVuIa', 796363200, 'male', 'fb', '10218449797824110', NULL, NULL, NULL, 1544294091),
(3, 'bb9d201a4b', 'bbbba', 'bb9d201a4b@nicemail.pro', '$2a$10$21e040f96f4346cc08accOKnREHC6K28FolR2lGR7I3qwKCouX4CG', 946699200, 'male', 'tc', '254800000', NULL, NULL, NULL, 1544297223);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

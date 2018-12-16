-- phpMyAdmin SQL Dump
-- version 4.8.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-12-2018 a las 08:25:17
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
-- Estructura de tabla para la tabla `sports`
--

CREATE TABLE `sports` (
  `id_sport` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `sports`
--

INSERT INTO `sports` (`id_sport`, `name`, `created_at`) VALUES
(1, 'Football', 1544880004),
(2, 'Basketball', 1544880011),
(3, 'Soccer', 1544880060),
(4, 'Kickball', 1544880141),
(5, 'Baseball', 1544880155);

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

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_sport`
--

CREATE TABLE `user_sport` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_sport` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `sports`
--
ALTER TABLE `sports`
  ADD PRIMARY KEY (`id_sport`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id_user`);

--
-- Indices de la tabla `user_sport`
--
ALTER TABLE `user_sport`
  ADD KEY `id_user` (`id_user`),
  ADD KEY `id_sport` (`id_sport`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `sports`
--
ALTER TABLE `sports`
  MODIFY `id_sport` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `user_sport`
--
ALTER TABLE `user_sport`
  ADD CONSTRAINT `user_sport_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_sport_ibfk_2` FOREIGN KEY (`id_sport`) REFERENCES `sports` (`id_sport`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

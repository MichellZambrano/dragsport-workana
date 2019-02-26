-- phpMyAdmin SQL Dump
-- version 4.8.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-02-2019 a las 15:26:16
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
-- Estructura de tabla para la tabla `social`
--

CREATE TABLE `social` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_social` varchar(100) DEFAULT NULL,
  `is_logged` enum('on','off') NOT NULL DEFAULT 'off',
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `social`
--

INSERT INTO `social` (`id_user`, `id_social`, `is_logged`, `created_at`) VALUES
(1, 'kwyv1e2e8fzg8bs87suk7pyx1', 'on', 1545137044);

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
  `image` varchar(255) DEFAULT NULL,
  `is_admin` enum('0','1') NOT NULL DEFAULT '0',
  `tmp_pass` varchar(90) DEFAULT NULL,
  `token` varchar(90) DEFAULT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id_user`, `first_name`, `last_name`, `email`, `pass`, `birthdate`, `gender`, `login_with`, `social_id`, `image`, `is_admin`, `tmp_pass`, `token`, `created_at`) VALUES
(1, 'test', 'test', 'test@demo.com', '$2a$10$53b0c0ed679eabd8c2834OhWLHmbPHNik81JgFUX0Hmq7hSuOmTtO', 1544932800, 'male', 'email', NULL, NULL, '0', NULL, NULL, 1544951171),
(2, 'bb9d201a4b', 'bb9d201a4b', 'bb9d201a4b@nicemail.pro', '$2a$10$d7fdc37456393ade274ebeTGkyXRPp0760Lq4duKXR0HLw3JXUAa6', 946699200, 'male', 'tc', '254800000', 'https://static-cdn.jtvnw.net/user-default-pictures/bb97f7e6-f11a-4194-9708-52bf5a5125e8-profile_image-300x300.jpg', '0', NULL, NULL, 1545778457),
(3, 'DRASPORTS', 'dragsport', 'dragsport@gmail.com', '$2a$10$3e728ce6b64207b17cca8ugL0ovuT6Web9eEtMdXrEzcgjC0ZH8TK', 946699200, 'male', 'tw', '1072184041015779328', 'https://avatars.io/twitter/DRASPORTS/original', '0', NULL, NULL, 1545779587),
(4, 'Armando', 'Amaya', 'armjaxd@hotmail.com', '$2a$10$4ecdbf274dfaf36dd0417ukZHsHfEE7Toq2M1enXUfPqmuc9C1WtC', 946699200, 'male', 'fb', '10218449797824110', 'https://graph.facebook.com/10218449797824110/picture?type=large', '0', NULL, NULL, 1545781256);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_sport`
--

CREATE TABLE `user_sport` (
  `id_user` int(10) UNSIGNED NOT NULL,
  `id_sport` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `user_sport`
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
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `social`
--
ALTER TABLE `social`
  ADD KEY `id_user` (`id_user`);

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
  MODIFY `id_user` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `social`
--
ALTER TABLE `social`
  ADD CONSTRAINT `social_ibfk_1` FOREIGN KEY (`id_user`) REFERENCES `users` (`id_user`) ON DELETE CASCADE;

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

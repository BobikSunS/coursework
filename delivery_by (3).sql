-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1
-- Время создания: Дек 10 2025 г., 23:01
-- Версия сервера: 10.4.32-MariaDB
-- Версия PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `delivery_by`
--

-- --------------------------------------------------------

--
-- Структура таблицы `carriers`
--

CREATE TABLE `carriers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `color` varchar(7) NOT NULL,
  `max_weight` decimal(6,2) NOT NULL,
  `base_cost` decimal(8,2) NOT NULL,
  `cost_per_kg` decimal(8,3) NOT NULL,
  `cost_per_km` decimal(8,4) NOT NULL,
  `speed_kmh` decimal(6,2) NOT NULL,
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `carriers`
--

INSERT INTO `carriers` (`id`, `name`, `color`, `max_weight`, `base_cost`, `cost_per_kg`, `cost_per_km`, `speed_kmh`, `description`) VALUES
(1, 'Белпочта', '#d32f2f', 30.00, 4.50, 0.250, 0.0080, 60.00, 'Государственная почта'),
(2, 'DPD', '#0066cc', 30.00, 9.00, 0.800, 0.0180, 95.00, 'Международная доставка'),
(3, 'СДЭК (CDEK)', '#ff9800', 20.00, 8.50, 0.700, 0.0200, 90.00, 'Курьерская служба'),
(4, 'Европочта', '#4caf50', 25.00, 6.50, 0.500, 0.0120, 80.00, 'Пункты выдачи'),
(5, 'Boxberry', '#9c27b0', 15.00, 8.00, 0.900, 0.0220, 85.00, 'Пункты выдачи'),
(6, 'Autolight Express', '#e91e63', 30.00, 9.50, 0.750, 0.0190, 92.00, 'Быстрая доставка');

-- --------------------------------------------------------

--
-- Структура таблицы `offices`
--

CREATE TABLE `offices` (
  `id` int(11) NOT NULL,
  `carrier_id` int(11) DEFAULT NULL,
  `city` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `offices`
--

INSERT INTO `offices` (`id`, `carrier_id`, `city`, `address`) VALUES
(1, 1, 'Минск', 'пр-т Независимости, 10'),
(2, 1, 'Гомель', 'ул. Советская, 21'),
(3, 1, 'Брест', 'ул. Советская, 46'),
(4, 1, 'Гродно', 'ул. Ожешко, 1'),
(5, 1, 'Витебск', 'ул. Ленина, 20'),
(6, 1, 'Могилёв', 'ул. Первомайская, 42'),
(7, 1, 'Борисов', 'ул. 3 Интернационала, 15'),
(8, 1, 'Барановичи', 'ул. Советская, 89'),
(9, 2, 'Минск', 'ул. Притыцкого, 29'),
(10, 2, 'Минск', 'ул. Немига, 5'),
(11, 2, 'Гомель', 'пр-т Ленина, 10'),
(12, 2, 'Брест', 'ул. Гоголя, 15'),
(13, 2, 'Гродно', 'ул. Горького, 50'),
(14, 2, 'Минск', 'ТЦ Dana Mall'),
(15, 3, 'Минск', 'ТЦ Столица'),
(16, 3, 'Минск', 'ТЦ Galleria'),
(17, 3, 'Гомель', 'ТЦ Секрет'),
(18, 3, 'Брест', 'ТЦ Евроопт'),
(19, 3, 'Витебск', 'ТЦ Беларусь'),
(20, 3, 'Могилёв', 'ТЦ Перекрёсток'),
(21, 4, 'Минск', 'ул. Кульман, 9'),
(22, 4, 'Минск', 'ст.м. Площадь Победы'),
(23, 4, 'Гродно', 'ул. Поповича, 5'),
(24, 4, 'Минск', 'ул. Сурганова, 57'),
(25, 4, 'Брест', 'ул. Московская, 202'),
(26, 5, 'Минск', 'ул. Притыцкого, 156'),
(27, 5, 'Минск', 'ТЦ Galileo'),
(28, 5, 'Гомель', 'ул. Ильича, 33'),
(29, 5, 'Могилёв', 'пр-т Мира, 21'),
(30, 6, 'Минск', 'ул. Тимирязева, 123'),
(31, 6, 'Минск', 'ул. Победителей, 89'),
(32, 6, 'Гомель', 'ул. Крестьянская, 12'),
(33, 6, 'Брест', 'ул. 17 Сентября, 10'),
(34, 6, 'Минск', 'ТЦ Экспобел');

-- --------------------------------------------------------

--
-- Структура таблицы `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `carrier_id` int(11) DEFAULT NULL,
  `from_office` int(11) DEFAULT NULL,
  `to_office` int(11) DEFAULT NULL,
  `weight` decimal(8,3) DEFAULT NULL,
  `cost` decimal(10,2) DEFAULT NULL,
  `delivery_hours` decimal(8,2) DEFAULT NULL,
  `track_number` varchar(20) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `orders`
--

INSERT INTO `orders` (`id`, `user_id`, `carrier_id`, `from_office`, `to_office`, `weight`, `cost`, `delivery_hours`, `track_number`, `created_at`) VALUES
(1, 2, 4, 23, 22, 30.000, 49.28, 1.60, '7493FC43F2BC', '2025-12-04 03:43:01'),
(2, 1, 1, 8, 3, 1.000, 8.60, 8.00, '3E93445DFB40', '2025-12-10 21:10:10'),
(3, 1, 2, 11, 13, 1.000, 19.12, 5.50, '14DA538AF5EC', '2025-12-10 21:10:14'),
(4, 1, 2, 11, 13, 1.000, 19.12, 5.50, '614C04987B6D', '2025-12-10 21:22:43'),
(5, 1, 2, 11, 13, 1.000, 19.12, 5.50, '793FA52FC4C1', '2025-12-10 21:22:45'),
(6, 2, 3, 18, 17, 20.000, 34.68, 6.40, 'BE1E0039F0E1', '2025-12-10 23:06:46'),
(7, 2, 5, 26, 29, 15.000, 37.69, 8.70, '28BD4368DF28', '2025-12-10 23:07:14'),
(8, 1, 2, 12, 11, 25.000, 34.89, 3.40, 'F3F6FDC39031', '2025-12-10 23:37:25'),
(9, 1, 5, 28, 27, 1.000, 16.12, 3.90, 'A05B842C6F32', '2025-12-10 23:37:30'),
(10, 1, 1, 7, 5, 20.000, 13.45, 8.20, '5C570435A40B', '2025-12-10 23:45:07'),
(11, 1, 2, 12, 13, 14.000, 27.85, 4.50, '74AF25CBE2BB', '2025-12-10 23:56:26'),
(12, 1, 5, 28, 26, 1.000, 18.10, 4.90, 'F08FBD9B5A40', '2025-12-10 23:56:32'),
(13, 1, 5, NULL, NULL, 1.000, 8.90, NULL, '697BF70D4F5E', '2025-12-11 00:21:05'),
(14, 1, 2, 11, 12, 1.000, 15.69, 3.40, '5C12CA9A2444', '2025-12-11 00:28:32'),
(15, 2, 3, NULL, NULL, 1.000, 9.20, NULL, 'D0EF1CFA0C3D', '2025-12-11 00:51:10');

-- --------------------------------------------------------

--
-- Структура таблицы `routes`
--

CREATE TABLE `routes` (
  `from_office` int(11) NOT NULL,
  `to_office` int(11) NOT NULL,
  `distance_km` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `routes`
--

INSERT INTO `routes` (`from_office`, `to_office`, `distance_km`) VALUES
(1, 3, 266),
(1, 4, 238),
(1, 11, 221),
(1, 12, 333),
(1, 13, 297),
(1, 16, 242),
(1, 23, 451),
(1, 28, 525),
(1, 33, 423),
(2, 17, 198),
(2, 19, 499),
(2, 21, 243),
(2, 22, 537),
(3, 4, 369),
(3, 5, 310),
(3, 10, 413),
(3, 12, 144),
(3, 15, 257),
(3, 16, 639),
(3, 19, 77),
(3, 22, 538),
(3, 24, 295),
(3, 26, 547),
(3, 27, 459),
(3, 34, 301),
(4, 15, 584),
(4, 16, 439),
(4, 17, 236),
(4, 19, 147),
(4, 24, 561),
(4, 28, 73),
(4, 29, 523),
(4, 32, 164),
(5, 11, 79),
(5, 16, 326),
(5, 19, 637),
(5, 22, 222),
(5, 23, 423),
(5, 25, 476),
(5, 27, 95),
(5, 28, 519),
(5, 30, 163),
(6, 11, 622),
(6, 17, 62),
(6, 20, 628),
(6, 21, 612),
(6, 22, 135),
(6, 23, 210),
(6, 26, 454),
(6, 29, 525),
(6, 33, 505),
(6, 34, 435),
(7, 11, 415),
(7, 25, 499),
(7, 27, 474),
(7, 28, 249),
(8, 12, 488),
(8, 13, 300),
(8, 21, 170),
(8, 26, 538),
(8, 27, 433),
(8, 28, 548),
(8, 30, 59),
(8, 31, 625),
(8, 33, 118),
(9, 10, 204),
(9, 11, 320),
(9, 13, 443),
(9, 19, 537),
(9, 27, 82),
(9, 34, 51),
(10, 14, 193),
(10, 23, 525),
(10, 24, 490),
(10, 25, 540),
(10, 26, 605),
(10, 28, 624),
(10, 29, 394),
(10, 33, 232),
(11, 12, 422),
(11, 17, 584),
(11, 18, 77),
(12, 16, 387),
(12, 19, 359),
(12, 22, 172),
(12, 24, 377),
(12, 25, 405),
(12, 26, 255),
(12, 27, 153),
(12, 29, 481),
(12, 30, 646),
(12, 34, 512),
(13, 22, 253),
(13, 28, 483),
(14, 15, 631),
(14, 25, 454),
(14, 31, 159),
(15, 25, 454),
(15, 26, 396),
(15, 30, 165),
(15, 32, 617),
(16, 29, 250),
(16, 34, 521),
(17, 22, 203),
(17, 23, 266),
(17, 27, 450),
(17, 28, 560),
(17, 30, 392),
(17, 32, 502),
(17, 33, 608),
(18, 26, 265),
(18, 28, 334),
(18, 31, 164),
(19, 20, 63),
(19, 24, 572),
(19, 25, 609),
(19, 30, 353),
(19, 32, 500),
(20, 24, 236),
(20, 26, 636),
(20, 28, 525),
(20, 31, 385),
(20, 33, 332),
(21, 25, 524),
(21, 30, 138),
(21, 34, 258),
(22, 23, 179),
(22, 26, 192),
(22, 27, 600),
(22, 28, 226),
(22, 31, 261),
(24, 30, 543),
(25, 30, 627),
(25, 33, 385),
(26, 34, 476),
(27, 29, 579),
(27, 34, 107),
(28, 29, 424),
(28, 31, 237),
(28, 34, 221),
(29, 30, 498),
(29, 33, 365),
(30, 31, 537),
(31, 32, 555),
(31, 33, 66);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `login` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `login`, `password`, `name`, `email`, `phone`, `role`, `created_at`) VALUES
(1, 'admin', 'admin', 'Администратор', NULL, NULL, 'admin', '2025-12-04 03:19:52'),
(2, 'Aliaksandr', '123456', 'Александр', 'szhurko005@gmail.com', NULL, 'user', '2025-12-04 03:41:51');

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `carriers`
--
ALTER TABLE `carriers`
  ADD PRIMARY KEY (`id`);

--
-- Индексы таблицы `offices`
--
ALTER TABLE `offices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `carrier_id` (`carrier_id`);

--
-- Индексы таблицы `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `track_number` (`track_number`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `carrier_id` (`carrier_id`),
  ADD KEY `from_office` (`from_office`),
  ADD KEY `to_office` (`to_office`);

--
-- Индексы таблицы `routes`
--
ALTER TABLE `routes`
  ADD PRIMARY KEY (`from_office`,`to_office`),
  ADD KEY `to_office` (`to_office`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `login` (`login`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `carriers`
--
ALTER TABLE `carriers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT для таблицы `offices`
--
ALTER TABLE `offices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT для таблицы `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `offices`
--
ALTER TABLE `offices`
  ADD CONSTRAINT `offices_ibfk_1` FOREIGN KEY (`carrier_id`) REFERENCES `carriers` (`id`) ON DELETE CASCADE;

--
-- Ограничения внешнего ключа таблицы `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`carrier_id`) REFERENCES `carriers` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`from_office`) REFERENCES `offices` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`to_office`) REFERENCES `offices` (`id`) ON DELETE SET NULL;

--
-- Ограничения внешнего ключа таблицы `routes`
--
ALTER TABLE `routes`
  ADD CONSTRAINT `routes_ibfk_1` FOREIGN KEY (`from_office`) REFERENCES `offices` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `routes_ibfk_2` FOREIGN KEY (`to_office`) REFERENCES `offices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

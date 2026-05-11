-- PHPMyAdmin 4.8.5 Compatibility Version
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Структура таблицы `airports`
--
CREATE TABLE `airports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city` varchar(100) NOT NULL,
  `name` varchar(100) NOT NULL,
  `code` varchar(10) NOT NULL,
  `country` varchar(100) NOT NULL,
  `timezone` varchar(50) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `idx_city` (`city`),
  KEY `idx_country` (`country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Дамп данных `airports`
INSERT INTO `airports` (`id`, `city`, `name`, `code`, `country`, `timezone`, `created_at`) VALUES
(1, 'Москва', 'Шереметьево', 'SVO', 'Россия', NULL, '2026-01-29 06:16:25'),
(2, 'Москва', 'Домодедово', 'DME', 'Россия', NULL, '2026-01-29 06:16:25'),
(3, 'Москва', 'Внуково', 'VKO', 'Россия', NULL, '2026-01-29 06:16:25'),
(4, 'Санкт-Петербург', 'Пулково', 'LED', 'Россия', NULL, '2026-01-29 06:16:25'),
(5, 'Париж', 'Шарль-де-Голль', 'CDG', 'Франция', NULL, '2026-01-29 06:16:25'),
(6, 'Париж', 'Орли', 'ORY', 'Франция', NULL, '2026-01-29 06:16:25'),
(7, 'Нью-Йорк', 'John F. Kennedy', 'JFK', 'США', NULL, '2026-01-29 06:16:25'),
(8, 'Нью-Йорк', 'Newark Liberty', 'EWR', 'США', NULL, '2026-01-29 06:16:25'),
(9, 'Стамбул', 'Ататюрк', 'IST', 'Турция', NULL, '2026-01-29 06:16:25'),
(10, 'Дубай', 'Международный аэропорт Дубай', 'DXB', 'ОАЭ', NULL, '2026-01-29 06:16:25'),
(11, 'Лондон', 'Хитроу', 'LHR', 'Великобритания', NULL, '2026-01-29 06:16:25'),
(12, 'Токио', 'Нарита', 'NRT', 'Япония', NULL, '2026-01-29 06:16:25'),
(13, 'Пекин', 'Столичный', 'PEK', 'Китай', NULL, '2026-01-29 06:16:25'),
(14, 'Бали', 'Нгура Рай', 'DPS', 'Индонезия', NULL, '2026-01-29 06:16:25'),
(15, 'Барселона', 'Эль Прат', 'BCN', 'Испания', NULL, '2026-01-29 06:16:25');

--
-- Структура таблицы `flights`
--
CREATE TABLE `flights` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `airline` varchar(100) NOT NULL,
  `flight_number` varchar(20) NOT NULL,
  `departure_city` varchar(100) NOT NULL,
  `departure_airport` varchar(100) NOT NULL,
  `arrival_city` varchar(100) NOT NULL,
  `arrival_airport` varchar(100) NOT NULL,
  `departure_time` time NOT NULL,
  `arrival_time` time NOT NULL,
  `duration` varchar(20) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `class` enum('economy','business','first') DEFAULT 'economy',
  `available_seats` int(11) NOT NULL,
  `departure_date` date NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Дамп данных `flights`
INSERT INTO `flights` (`id`, `airline`, `flight_number`, `departure_city`, `departure_airport`, `arrival_city`, `arrival_airport`, `departure_time`, `arrival_time`, `duration`, `price`, `class`, `available_seats`, `departure_date`, `created_at`) VALUES
(1, 'Аэрофлот', 'SU 1001', 'Москва', 'Шереметьево', 'Париж', 'Шарль-де-Голль', '08:30:00', '11:45:00', '4ч 15м', '12500.00', 'economy', 45, '2023-12-20', '2026-01-29 06:16:25'),
(2, 'Air France', 'AF 1234', 'Москва', 'Домодедово', 'Париж', 'Шарль-де-Голль', '14:20:00', '17:35:00', '4ч 15м', '13800.00', 'economy', 32, '2023-12-20', '2026-01-29 06:16:25'),
(3, 'Аэрофлот', 'SU 2002', 'Москва', 'Шереметьево', 'Нью-Йорк', 'JFK', '10:00:00', '13:30:00', '10ч 30м', '35000.00', 'economy', 27, '2023-12-21', '2026-01-29 06:16:25'),
(4, 'Delta Airlines', 'DL 456', 'Москва', 'Шереметьево', 'Нью-Йорк', 'JFK', '16:45:00', '20:15:00', '10ч 30м', '32500.00', 'business', 15, '2023-12-21', '2026-01-29 06:16:25'),
(5, 'Turkish Airlines', 'TK 789', 'Москва', 'Внуково', 'Стамбул', 'Ататюрк', '09:15:00', '11:30:00', '3ч 15м', '8900.00', 'economy', 67, '2023-12-19', '2026-01-29 06:16:25'),
(6, 'Emirates', 'EK 131', 'Москва', 'Домодедово', 'Дубай', 'Международный аэропорт Дубай', '22:40:00', '04:15:00', '5ч 35м', '24500.00', 'business', 12, '2023-12-22', '2026-01-29 06:16:25'),
(7, 'АвиаИркутск', 'CO 288 337', 'Иркутск', 'Новосибирск', 'Москва', 'Питер', '00:20:26', '00:20:26', '6 ч 5 м', '25000.00', 'first', 24, '2024-01-01', '2026-01-29 08:23:41');

--
-- Структура таблицы `users`
--
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fullname` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` tinyint(4) DEFAULT '0',
  `status` tinyint(4) DEFAULT '1',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `last_login` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Дамп данных `users`
INSERT INTO `users` (`id`, `fullname`, `email`, `phone`, `password`, `role`, `status`, `created_at`, `last_login`) VALUES
(1, 'Саловаров Роман Петрович', 'romanpetrosan@gmail.com', '+7955502221', '$2y$10$bzQ5IeAhEM29mpUSfzxje.aGKmUmWM2QYZVdcr9lKx6qlB8J3mwxu', 1, 1, '2026-01-29 06:16:25', '2026-01-30 08:35:52'),
(3, '001', '001@gmail.com', '795222112333', '$2y$10$oMsias95WeqbLWzd3FcNFOKnQkCOfvBpPT7gPMl6SHNoanGnDUsYm', 0, 1, '2026-01-29 06:16:25', NULL),
(5, '002', '002@gmail.com', '792221123133', '$2y$10$16xDnkRDdtfA06LGqDh8K.lCGnc1n/OPfNEgkd9FkW.MValdQykuS', 0, 0, '2026-01-29 06:16:25', NULL);

--
-- Структура таблицы `bookings`
--
CREATE TABLE `bookings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_reference` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `flight_id` int(11) NOT NULL,
  `passengers` int(11) NOT NULL,
  `booking_date` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('confirmed','pending','cancelled') DEFAULT 'confirmed',
  `payment_status` enum('paid','unpaid') DEFAULT 'unpaid',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `passenger_name` varchar(100) DEFAULT NULL,
  `passenger_email` varchar(100) DEFAULT NULL,
  `passenger_phone` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_flight_id` (`flight_id`),
  KEY `idx_booking_reference` (`booking_reference`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`flight_id`) REFERENCES `flights` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Дамп данных `bookings`
INSERT INTO `bookings` (`id`, `booking_reference`, `user_id`, `flight_id`, `passengers`, `booking_date`, `total_price`, `status`, `payment_status`, `created_at`, `passenger_name`, `passenger_email`, `passenger_phone`) VALUES
(1, 'BK202601307860', 1, 7, 1, '2026-01-30 09:55:04', '25000.00', 'confirmed', 'unpaid', '2026-01-30 09:55:04', 'Саловаров Роман Петрович', 'romanpetrosan@gmail.com', '+7 (523) 543-54-35'),
(2, 'BK202601309626', 1, 7, 1, '2026-01-30 09:55:38', '25000.00', 'confirmed', 'unpaid', '2026-01-30 09:55:38', 'Саловаров Роман Петрович', 'romanpetrosan@gmail.com', '+7 (345) 435-34-53'),
(3, 'BK202601305767', 1, 7, 1, '2026-01-30 09:59:21', '25000.00', 'confirmed', 'unpaid', '2026-01-30 09:59:21', 'Саловаров Роман Петрович', 'romanpetrosan@gmail.com', '+7 (645) 645-64-56');

--
-- Структура таблицы `passenger_details`
--
CREATE TABLE `passenger_details` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `booking_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `birth_date` date DEFAULT NULL,
  `document_number` varchar(50) DEFAULT NULL,
  `seat_number` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

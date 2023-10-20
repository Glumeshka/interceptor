-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Хост: 127.0.0.1:3306
-- Время создания: Окт 20 2023 г., 23:58
-- Версия сервера: 8.0.30
-- Версия PHP: 8.1.9

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- База данных: `interceptor`
--

-- --------------------------------------------------------

--
-- Структура таблицы `clicklogs`
--

CREATE TABLE `clicklogs` (
  `id` int NOT NULL,
  `click_time` datetime NOT NULL,
  `done` tinyint NOT NULL,
  `offer_id` int NOT NULL,
  `webmaster_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `clicklogs`
--

INSERT INTO `clicklogs` (`id`, `click_time`, `done`, `offer_id`, `webmaster_id`) VALUES
(1, '2023-10-14 18:46:57', 1, 1, 1),
(2, '2023-10-13 18:48:00', 1, 1, 1),
(3, '2023-10-12 18:48:52', 1, 1, 1),
(4, '2023-10-16 19:05:09', 1, 2, 4),
(5, '2023-10-16 19:06:44', 1, 1, 4),
(6, '2023-10-16 19:07:10', 1, 2, 1),
(7, '2023-10-17 19:28:33', 1, 1, 4),
(8, '2023-10-17 19:29:04', 0, 2, 4),
(9, '2023-10-12 17:14:05', 1, 1, 4),
(10, '2023-10-20 23:34:02', 1, 3, 1),
(11, '2023-10-20 23:34:03', 1, 3, 1),
(12, '2023-10-20 23:34:03', 1, 3, 1),
(13, '2023-10-20 23:34:04', 1, 3, 1),
(14, '2023-10-20 23:34:46', 1, 3, 1),
(15, '2023-10-20 23:35:07', 0, 2, 1),
(16, '2023-10-20 23:43:28', 0, 2, 1);

-- --------------------------------------------------------

--
-- Структура таблицы `offers`
--

CREATE TABLE `offers` (
  `id` int NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `topic` varchar(255) COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'не указана',
  `cost_per_click` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `original_url` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `advertiser_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `offers`
--

INSERT INTO `offers` (`id`, `name`, `topic`, `cost_per_click`, `active`, `original_url`, `advertiser_id`) VALUES
(1, 'Прикол', 'не указана', '20.20', 1, 'https://www.youtube.com/watch?v=AUSYML0hXo0', 2),
(2, 'Цвета', 'Нюхательные аппараты', '2.00', 0, 'https://www.youtube.com/watch?v=IXXZCZAKLns', 2),
(3, 'Чекатило', 'Музыка', '5.53', 1, 'https://www.youtube.com/watch?v=ilLTcTXTI0E', 2);

-- --------------------------------------------------------

--
-- Структура таблицы `subscriptions`
--

CREATE TABLE `subscriptions` (
  `id` int NOT NULL,
  `sub_time` datetime NOT NULL,
  `webmaster_id` int NOT NULL,
  `offer_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `subscriptions`
--

INSERT INTO `subscriptions` (`id`, `sub_time`, `webmaster_id`, `offer_id`) VALUES
(5, '2023-10-16 18:52:55', 4, 1),
(8, '2023-10-20 16:38:56', 1, 2),
(10, '2023-10-20 17:32:37', 1, 1),
(11, '2023-10-20 23:23:09', 1, 3);

-- --------------------------------------------------------

--
-- Структура таблицы `users`
--

CREATE TABLE `users` (
  `id` int NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `role` enum('admin','webmaster','advertiser') COLLATE utf8mb4_general_ci NOT NULL,
  `banned` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Дамп данных таблицы `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `banned`) VALUES
(1, '123@123.ru', '$argon2id$v=19$m=65536,t=4,p=1$YkNpSzBNVHVWZDRPNTcxRg$FAqJ25xtg9XFoEuJaE/dCktOctNn8cvVenING32l2rA', 'webmaster', 0),
(2, 'rzl1985@yandex.ru', '$argon2id$v=19$m=65536,t=4,p=1$Q0lhUXFQeW5HbDNhUDR5cQ$DVQrgNB7OaBMF9Fn0I4fxmwl069iA+nfuO43G90MCGg', 'advertiser', 0),
(3, 'rzl1985@gmail.com', '$argon2id$v=19$m=65536,t=4,p=1$bTF5MFc5S21lbTkwV2suWA$Dv1CFc8AWEbF1ma0L583Fm/OU1kXA3MEiuwwhiD0jyk', 'admin', 0),
(4, '321@321.ru', '$argon2id$v=19$m=65536,t=4,p=1$OUp6d0Z1RXlyd1EzenRyNg$rc260itUrvmyIXQaEHQqSG/OQevLEPnzT0usc8ZNWyA', 'webmaster', 0),
(26, '1234@1234.ru', '$argon2id$v=19$m=65536,t=4,p=1$MXBURzlVLnlPclpJcjFGcg$CK6hU168GGdw+EfPz0bYSqmirhxdtZYmlCeBUc0IHEw', 'webmaster', 0);

--
-- Индексы сохранённых таблиц
--

--
-- Индексы таблицы `clicklogs`
--
ALTER TABLE `clicklogs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_clicklogs_offers` (`offer_id`),
  ADD KEY `FK_clicklogs_users` (`webmaster_id`);

--
-- Индексы таблицы `offers`
--
ALTER TABLE `offers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_offers_users` (`advertiser_id`);

--
-- Индексы таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `FK_subscriptions_offers` (`offer_id`),
  ADD KEY `FK_subscriptions_users` (`webmaster_id`);

--
-- Индексы таблицы `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT для сохранённых таблиц
--

--
-- AUTO_INCREMENT для таблицы `clicklogs`
--
ALTER TABLE `clicklogs`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT для таблицы `offers`
--
ALTER TABLE `offers`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT для таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT для таблицы `users`
--
ALTER TABLE `users`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Ограничения внешнего ключа сохраненных таблиц
--

--
-- Ограничения внешнего ключа таблицы `clicklogs`
--
ALTER TABLE `clicklogs`
  ADD CONSTRAINT `FK_clicklogs_offers` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`),
  ADD CONSTRAINT `FK_clicklogs_users` FOREIGN KEY (`webmaster_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `offers`
--
ALTER TABLE `offers`
  ADD CONSTRAINT `FK_offers_users` FOREIGN KEY (`advertiser_id`) REFERENCES `users` (`id`);

--
-- Ограничения внешнего ключа таблицы `subscriptions`
--
ALTER TABLE `subscriptions`
  ADD CONSTRAINT `FK_subscriptions_offers` FOREIGN KEY (`offer_id`) REFERENCES `offers` (`id`),
  ADD CONSTRAINT `FK_subscriptions_users` FOREIGN KEY (`webmaster_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

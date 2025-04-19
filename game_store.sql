-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 19, 2025 at 07:48 AM
-- Server version: 8.2.0
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `game_store`
--

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
CREATE TABLE IF NOT EXISTS `cache` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
CREATE TABLE IF NOT EXISTS `cache_locks` (
  `key` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
CREATE TABLE IF NOT EXISTS `cart` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `c_userId` bigint UNSIGNED NOT NULL,
  `c_gameId` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `c_price` decimal(10,2) NOT NULL,
  `c_discount` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `cart_c_userid_foreign` (`c_userId`),
  KEY `cart_c_gameid_foreign` (`c_gameId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `games`
--

DROP TABLE IF EXISTS `games`;
CREATE TABLE IF NOT EXISTS `games` (
  `g_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `g_title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `g_description` text COLLATE utf8mb4_unicode_ci,
  `g_price` decimal(10,2) NOT NULL,
  `g_discount` decimal(5,2) NOT NULL DEFAULT '0.00',
  `g_developerId` bigint UNSIGNED NOT NULL,
  `g_status` enum('verified','reported','pending','removed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `g_mainImage` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `g_exImg1` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `g_exImg2` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `g_exImg3` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `g_overallRate` decimal(3,2) NOT NULL DEFAULT '0.00',
  `g_language` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `g_category` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`g_id`),
  KEY `games_g_developerid_foreign` (`g_developerId`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `games`
--

INSERT INTO `games` (`g_id`, `g_title`, `g_description`, `g_price`, `g_discount`, `g_developerId`, `g_status`, `g_mainImage`, `g_exImg1`, `g_exImg2`, `g_exImg3`, `g_overallRate`, `g_language`, `g_category`, `created_at`, `updated_at`) VALUES
(1, 'Angrybird', 'This is a game', 30.00, 15.00, 2, 'verified', 'games/QiODKbfTaFhj9Vd67H75i2KOgw1RCwjqCUAabFxI.jpg', 'games/ZdhLR4beiqXMzZpYgXmbEVYqU05k3EDbnuqYRHj8.png', 'games/ZpXa5KZ31ef26IJm5dJabwIcSF5EuKVyV75WQpqx.jpg', NULL, 0.00, 'English', 'Strategy', '2025-04-18 21:27:04', '2025-04-18 21:27:50'),
(2, 'Minecraft', 'Sand box game', 35.00, 10.00, 3, 'verified', 'games/ZWfnzmK21F5UbQT0NYUJPRfKKDWFvqUg4iv3EKgZ.png', 'games/anqkd5X6RUCLuCZGFUAgCMinPnpcAtfjr4eF4coV.jpg', 'games/5fm5B9uN1FdUwEMgYprXwhWzkVFhmXc23mg5PYBl.png', NULL, 0.00, 'English', 'Adventure', '2025-04-18 22:32:54', '2025-04-18 22:33:18'),
(3, 'Grand Theft Auto V', 'Grand Theft Auto V (GTA 5) is an open-world action-adventure game developed by Rockstar Games. Set in the sprawling fictional city of Los Santos, GTA 5 follows the lives of three unique protagonists — Michael, a retired bank robber; Franklin, a street-smart hustler; and Trevor, a volatile criminal — as they pull off a series of daring heists in pursuit of wealth and survival.', 50.00, 12.00, 4, 'pending', 'games/6S0Z8FQNNwnjQF5qwIydaoUYvMrXiMZ7ejV0Bdsq.jpg', 'games/lB6KkaJnv3LN9UcBl82UhOiFzxb2iCPpCqZ9oxjj.jpg', 'games/cz2bDB6DR79ddNK7m6hmgfiVhCSG7Vd8EGyreyG9.jpg', 'games/ebGWro9BQhqrr4WCb18F0ZIoXH8wRtTw5MTt5eHM.jpg', 0.00, 'English', 'RPG', '2025-04-18 23:16:47', '2025-04-18 23:16:47'),
(4, 'Dead By Daylight', 'Dead by Daylight is a multiplayer (4vs1) horror game where one player takes on the role of a terrifying Killer, and the other four play as Survivors, trying to escape the Killer and avoid being caught, tortured, and killed.\n\n', 20.00, 0.00, 4, 'verified', 'games/JCHIvk2JhGgTdiEz5dCmPp7YeanUI5PaXwWKINCz.jpg', 'games/ycTyiPyXuqspm0QP8gUakVauZBhRARnd7dg4TG2b.jpg', 'games/3rIUgtGpxkA9jyDqvFnSWr9kgBNdAnD55YBLhnTq.jpg', 'games/v38yqg7JvFcTlexPhcyAvZEsUWbnuawaF25Z80u5.jpg', 0.00, 'Chinese', 'Horror', '2025-04-18 23:19:16', '2025-04-18 23:20:13'),
(5, 'Elden Ring', 'Elden Ring is an epic dark fantasy action RPG developed by FromSoftware and published by Bandai Namco Entertainment, in collaboration with George R. R. Martin, who helped craft the game’s mythos.\n', 70.00, 25.00, 8, 'verified', 'games/mfjeS0YPqXabge9ZHqNg1ODdbhkaRKGYybUeS7Cn.jpg', 'games/v4JRMPEGYSHixvOwWBhZvU83yJiaFgKa6CLSfnfm.jpg', 'games/Zb86OfbLnwIZj6KUKZiCmK1JmmqBNYBM8zuEWogW.jpg', 'games/bxvR7f7qkJ17uFRRByeCKjikwHYELeJlODMYD77c.jpg', 0.00, 'Portuguese', 'RPG', '2025-04-18 23:34:08', '2025-04-18 23:42:07'),
(6, 'Forza Horizon 5', 'Forza Horizon 5 is an open-world racing game developed by Playground Games and published by Xbox Game Studios. Set in a vibrant, ever-evolving representation of Mexico, it offers the largest and most diverse world in the Horizon series — from lush jungles and arid deserts to sprawling cities and ancient ruins.\n', 55.00, 10.00, 8, 'verified', 'games/z8rM1tIYOQ5jWDt5LbcOgUuZDaB2nmfu0qWcj0fU.jpg', 'games/2xJXIQpBanvaaNfUUvy014u4U9X9abigNyNPpLHR.jpg', 'games/2olhKfdU1kL6PUHZZepJGgcTMprP2yCoI9f9V6yd.jpg', 'games/1HNPPtd2hKnp03zuzBZUqWJ0yEqwawOzN8yAB7pw.jpg', 0.00, 'Multiple Languages', 'Racing', '2025-04-18 23:36:19', '2025-04-18 23:42:05'),
(7, 'Valorant', 'The Sims 4 is a life simulation game developed by Maxis and published by Electronic Arts. In this highly popular sandbox game, players create and control virtual characters, known as Sims, and guide them through a variety of life stages — from childhood to adulthood, building relationships, careers, and pursuing aspirations.\n\n', 10.00, 0.00, 8, 'pending', 'games/zb5rWkiu4XRGDdSHaiLvz2M1DSfIt0oLhnRSAAzL.jpg', 'games/rf5bEuV6VOf1tpBeIAxrZK04PZPqXMJ02MJetyZ9.jpg', 'games/poUfBCEs1DR8ho4g8PmL7r0vBBpv2hKAZCBOnoju.jpg', 'games/c2SUJ6ZCqCswTHkLnPcw0OYbaikKtFdhPE6feFPN.jpg', 0.00, 'Multiple Languages', 'Simulation', '2025-04-18 23:39:38', '2025-04-18 23:39:38');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` int UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '2025_04_11_100544_create_games_table', 1),
(3, '2025_04_11_101127_create_purchases_table', 1),
(4, '2025_04_11_103115_create_reviews_table', 1),
(5, '2025_04_11_103427_create_user_lib_table', 1),
(6, '2025_04_11_104039_create_wishlist_table', 1),
(7, '2025_04_12_000000_create_sessions_table', 1),
(8, '2025_04_12_121323_create_personal_access_tokens_table', 1),
(9, '2025_04_16_170611_create_cache_table', 1),
(10, '2025_04_17_145816_remove_download_count', 1),
(11, '2025_04_17_175837_create_cart_table', 1),
(12, '2025_04_17_185531_add_is_banned_to_users_table', 1),
(13, '2025_04_17_195837_add_unique_constraint_to_u_name_column', 1);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint UNSIGNED NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchases`
--

DROP TABLE IF EXISTS `purchases`;
CREATE TABLE IF NOT EXISTS `purchases` (
  `p_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `p_gameName` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `p_userId` bigint UNSIGNED NOT NULL,
  `p_gameId` bigint UNSIGNED NOT NULL,
  `p_purchasePrice` decimal(8,2) NOT NULL,
  `p_purchaseDate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `p_receiptNumber` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`p_id`),
  UNIQUE KEY `purchases_p_receiptnumber_unique` (`p_receiptNumber`),
  KEY `purchases_p_userid_foreign` (`p_userId`),
  KEY `purchases_p_gameid_foreign` (`p_gameId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `purchases`
--

INSERT INTO `purchases` (`p_id`, `p_gameName`, `p_userId`, `p_gameId`, `p_purchasePrice`, `p_purchaseDate`, `p_receiptNumber`, `created_at`, `updated_at`) VALUES
(1, 'Angrybird', 6, 1, 25.50, '2025-04-18 22:40:32', 'REC-4hbomsq72h-1', '2025-04-18 22:40:32', '2025-04-18 22:40:32'),
(2, 'Minecraft', 6, 2, 31.50, '2025-04-18 22:40:32', 'REC-4hbomsq72h-2', '2025-04-18 22:40:32', '2025-04-18 22:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

DROP TABLE IF EXISTS `reviews`;
CREATE TABLE IF NOT EXISTS `reviews` (
  `r_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `r_userId` bigint UNSIGNED NOT NULL,
  `r_gameId` bigint UNSIGNED NOT NULL,
  `r_reviewText` text COLLATE utf8mb4_unicode_ci,
  `r_rating` int NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`r_id`),
  UNIQUE KEY `reviews_r_userid_r_gameid_unique` (`r_userId`,`r_gameId`),
  KEY `reviews_r_gameid_foreign` (`r_gameId`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE IF NOT EXISTS `sessions` (
  `id` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('ikoRQUDbmtwOBgTuo3B8VA9k50aq6qqcxpFjNS3i', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/135.0.0.0 Safari/537.36 Edg/135.0.0.0', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHFLcWxMdTF0V2RSdFZpYTBaU3ltNEJ6ZmhmaGxpRDk1SjhEZ3FIOSI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMC9hcGkvZ2FtZXMvMiI7fX0=', 1745048880);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `u_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `u_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `u_email` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `u_password` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `u_birthdate` date DEFAULT NULL,
  `u_role` enum('admin','developer','user') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'user',
  `u_profileImagePath` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `u_isBanned` enum('true','false') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'false',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`u_id`),
  UNIQUE KEY `users_u_email_unique` (`u_email`),
  UNIQUE KEY `users_u_name_unique` (`u_name`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`u_id`, `u_name`, `u_email`, `u_password`, `u_birthdate`, `u_role`, `u_profileImagePath`, `remember_token`, `u_isBanned`, `created_at`, `updated_at`) VALUES
(1, 'Admin User', 'admin@example.com', '$2y$12$GVHNDFoPbs2WeIxDb3UYuuj80hooZGL7VPFPeJHUBnnMbf7oTyD4m', '1990-01-01', 'admin', NULL, 'UrlvvpBVZJFYhYIMreenbdVkngCmozsiBIGRB0rxjrBVKQJQRlq0jEwbvvyq', 'false', '2025-04-18 21:14:52', '2025-04-18 21:14:52'),
(2, 'Naughty Dog Dev', 'naughtydog@dev.com', '$2y$12$i80DwET5Qn37IIqMJ4zEeuwu1948sFzGT40fhdoDgilHjizU9BfHS', '1985-03-15', 'developer', NULL, 'Hr3sAnF2SxJK2qgf2hZI87sQskQlX0olXAMIwl3JOtm29Usd7vebRkrTPfVY', 'false', '2025-04-18 21:14:52', '2025-04-18 21:14:52'),
(3, 'FromSoftware Dev', 'fromsoftware@dev.com', '$2y$12$UWSFtLMmvK/W8YAb7mzXEedXlpLuUAnhWvtISBdgLQmKfPc52bAdG', '1988-07-22', 'developer', NULL, 'eAfMVZhHTlsSS0h9V4r9oZw7JbWllq1BGJ4cNXmxM9gFsAmappdm76U1Su7n', 'false', '2025-04-18 21:14:52', '2025-04-18 21:14:52'),
(4, 'Rockstar Dev', 'rockstar@dev.com', '$2y$12$xnrSdtg8qzhGJH9GblhafuNuub9QhmD5FYwLTWm3gLRZLWWt77oCS', '1982-11-08', 'developer', NULL, 'eSqrlGRs031I3V1RKEinFZDGs5GmUUsVwTCsxxdJfeU5ZBbNqo9KkXNYVLCq', 'false', '2025-04-18 21:14:53', '2025-04-18 21:14:53'),
(5, 'John Doe', 'john@example.com', '$2y$12$jQ4xTWMoK1vTKFP5uEF/xOmtbWEWOUOtyDcIAiYbd3CQpYGMbjbkq', '1995-05-20', 'user', NULL, 'zgjhLj4e6ZIzvmWhaj125faZkodamwRgXhs0rAxY1YhSbJNTvNMeC8cYYtoJ', 'false', '2025-04-18 21:14:53', '2025-04-18 21:14:53'),
(6, 'Jane Smith', 'jane@example.com', '$2y$12$1kTKFgw2FD3jvF2bN45o7uTzpvWdfaMt8y3MnikttckA8cF/f99ci', '1998-09-12', 'user', 'images/user_profile/339761fd-1310-4edb-ac3b-4e55844ce511.jpeg', '5WAjTV45MtGDRKR6jndPASwJ9mt331MzS9wYYe9CtihasrNvKnOGStuuKh3D', 'false', '2025-04-18 21:14:53', '2025-04-18 23:23:13'),
(7, 'Mike Johnson', 'mike@example.com', '$2y$12$WveMtpxFmNzZbnwnZEHcsebz09TM3OgTGHR5id7gJuybQZMT5.Ve6', '1992-12-30', 'user', NULL, 'vB9ZDGj5HqsLvhDiHc3JZtH0wkXOsSwZLumJsDNpH6IewZGSq7Ay7vYYQPuR', 'false', '2025-04-18 21:14:53', '2025-04-18 21:14:53'),
(8, 'Vincent', 'vincent@example.com', '$2y$12$U3ATzudiHcD79ZtFjgnHdONRBfCWJ0rAG6/e8UZs.iVFLKT3LI9sK', '2016-05-09', 'developer', NULL, 'okW7BgsmJDxy3N55uXyAT7ZlGKLMal6Q75N19i8yXMBvydKIpzBB2hQL4D4U', 'false', '2025-04-18 23:29:45', '2025-04-18 23:29:45');

-- --------------------------------------------------------

--
-- Table structure for table `user_lib`
--

DROP TABLE IF EXISTS `user_lib`;
CREATE TABLE IF NOT EXISTS `user_lib` (
  `ul_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `ul_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ul_gameId` bigint UNSIGNED NOT NULL,
  `ul_userId` bigint UNSIGNED NOT NULL,
  `ul_status` enum('owned','installed','removed') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'owned',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ul_id`),
  UNIQUE KEY `user_lib_ul_userid_ul_gameid_unique` (`ul_userId`,`ul_gameId`),
  KEY `user_lib_ul_gameid_foreign` (`ul_gameId`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_lib`
--

INSERT INTO `user_lib` (`ul_id`, `ul_name`, `ul_gameId`, `ul_userId`, `ul_status`, `created_at`, `updated_at`) VALUES
(1, 'Angrybird', 1, 6, 'installed', '2025-04-18 22:40:32', '2025-04-18 22:58:37'),
(2, 'Minecraft', 2, 6, 'installed', '2025-04-18 22:40:32', '2025-04-18 22:41:38');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

DROP TABLE IF EXISTS `wishlist`;
CREATE TABLE IF NOT EXISTS `wishlist` (
  `wl_id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `wl_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `wl_gameId` bigint UNSIGNED NOT NULL,
  `wl_userId` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`wl_id`),
  UNIQUE KEY `wishlist_wl_userid_wl_gameid_unique` (`wl_userId`,`wl_gameId`),
  KEY `wishlist_wl_gameid_foreign` (`wl_gameId`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`wl_id`, `wl_name`, `wl_gameId`, `wl_userId`, `created_at`, `updated_at`) VALUES
(1, 'Angrybird', 1, 6, '2025-04-18 21:53:10', '2025-04-18 21:53:10'),
(3, 'Minecraft', 2, 6, '2025-04-18 23:01:13', '2025-04-18 23:01:13');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : mar. 03 juin 2025 à 08:38
-- Version du serveur : 8.3.0
-- Version de PHP : 8.2.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `db_market`
--

-- --------------------------------------------------------

--
-- Structure de la table `password_resets`
--

DROP TABLE IF EXISTS `password_resets`;
CREATE TABLE IF NOT EXISTS `password_resets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `email` (`email`(250))
) ENGINE=MyISAM AUTO_INCREMENT=34 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expires`) VALUES
(23, 'didi07@live.be', 'b6525eff720be81c60af0f46e2fd90bf0568ee38e1f0553c9af7d2a1c281a20e', '2025-05-26 09:48:24'),
(24, 'didi07@live.be', 'db3f9cee04ad93fec86d82a649a4f83006e77bef85150ada9ff466261e6a98eb', '2025-05-26 10:23:15'),
(25, 'didi07@live.be', 'b3ccff8d812336fcd15abb4b0154b539406df945ff11404d0230306eb54d38d6', '2025-05-26 10:24:31'),
(26, 'didi07@live.be', '00cf3aea85f83c10176c56318e566e33b0b02e40a35fbf60d3179f4a1d25f655', '2025-05-26 10:38:14'),
(27, 'didi07@live.be', 'afa3417425e7189878254b62924276fb055ea313945797a285609e1bbead066d', '2025-05-26 10:55:07'),
(28, 'didi07@live.be', '6fc47a2cde463e76a19df22f7349a4307c9de6dd425f5c16c67246443a02409e', '2025-05-26 11:30:49'),
(29, 'didi07@live.be', '4b62d21ddeabc34a886e623903c149a34e4fa90732e4f8291537ba80ce5e67d9', '2025-05-26 11:33:01'),
(30, 'didi07@live.be', '2fb7ff432cc11f94a537ecd50c0fa676907fdec591a089a049b0dd40c395d576', '2025-05-26 11:33:22'),
(31, 'didi07@live.be', '146887b972bfc9a2f46eb3f5df46664908e97e8ad2d7b6d562a17917a0a3c08a', '2025-05-26 11:35:48'),
(32, 'didi07@live.be', 'ce0351f2ae7f19a632ddb38a90796123673a02bfbc9b7b6a92c11f66c6e771e9', '2025-05-26 11:36:48'),
(33, 'didi07@live.be', '743f9b942e0bf8b4422daa8307153d3a4ec42cf595aa66e67e22e8b519c66fa6', '2025-05-26 11:46:09');

-- --------------------------------------------------------

--
-- Structure de la table `sub_categories`
--

DROP TABLE IF EXISTS `sub_categories`;
CREATE TABLE IF NOT EXISTS `sub_categories` (
  `id_sub_category` int NOT NULL AUTO_INCREMENT,
  `nom_sub_category` varchar(100) NOT NULL,
  `id_category` int NOT NULL,
  `active_sub_category` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_sub_category`),
  KEY `id_category` (`id_category`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tbl_category`
--

DROP TABLE IF EXISTS `tbl_category`;
CREATE TABLE IF NOT EXISTS `tbl_category` (
  `id_category` int NOT NULL AUTO_INCREMENT,
  `nom_category` varchar(30) NOT NULL,
  `active_category` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_category`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tbl_category`
--

INSERT INTO `tbl_category` (`id_category`, `nom_category`, `active_category`) VALUES
(1, 'Homme', 1),
(2, 'Femme', 1),
(3, 'Maison', 1);

-- --------------------------------------------------------

--
-- Structure de la table `tbl_item`
--

DROP TABLE IF EXISTS `tbl_item`;
CREATE TABLE IF NOT EXISTS `tbl_item` (
  `id_item` int NOT NULL AUTO_INCREMENT,
  `order_id` varchar(40) NOT NULL,
  `produit_id` varchar(40) NOT NULL,
  `quantity_item` int NOT NULL,
  `prix_item` int NOT NULL,
  `vendeur_id` varchar(40) NOT NULL,
  PRIMARY KEY (`id_item`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tbl_message`
--

DROP TABLE IF EXISTS `tbl_message`;
CREATE TABLE IF NOT EXISTS `tbl_message` (
  `id_message` int NOT NULL AUTO_INCREMENT,
  `exp_id` varchar(40) NOT NULL,
  `receive_id` varchar(40) NOT NULL,
  `content_message` text NOT NULL,
  `statut_message` int NOT NULL DEFAULT '0',
  `createdate_message` datetime NOT NULL,
  PRIMARY KEY (`id_message`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tbl_message`
--

INSERT INTO `tbl_message` (`id_message`, `exp_id`, `receive_id`, `content_message`, `statut_message`, `createdate_message`) VALUES
(1, '5', '18', 'salut cv', 1, '2025-05-05 08:33:54'),
(2, '18', '5', 'incroyable', 1, '2025-05-05 08:34:39'),
(3, '5', '18', 'probleme avec mon reseau', 1, '2025-05-05 11:12:55'),
(4, '5', '15', 'bonjour comment aller vous', 1, '2025-05-05 11:21:07'),
(5, '5', '18', 'coucou', 1, '2025-05-12 12:47:42'),
(6, '18', '5', 'bonjour', 1, '2025-05-12 13:06:43'),
(7, '18', '5', 'comment vas tu', 1, '2025-05-12 13:52:06'),
(8, '5', '18', 'cv', 1, '2025-05-12 13:53:54'),
(9, '19', '5', 'Bonjour, je suis intéressée par votre Tshirt, serait-il possible de baisser le prix ? Merci :)', 1, '2025-05-19 15:24:37'),
(10, '19', '5', 'A modifier : Tous les articles (filtre) faute, Aperçu dans le panier et dans les catégories (voir)', 1, '2025-05-19 15:27:10'),
(11, '18', '5', 'hello', 1, '2025-05-26 07:47:50'),
(12, '18', '5', 'bonjour', 1, '2025-05-26 08:17:17'),
(13, '5', '18', 'ok ca marche', 1, '2025-05-26 08:17:34'),
(14, '18', '5', 'bonjour', 1, '2025-05-26 13:55:58'),
(27, '18', '5', 'bonjour', 1, '2025-06-02 11:15:01'),
(26, '5', '18', 'ededde', 1, '2025-06-02 07:46:29'),
(25, '18', '5', 'cecererd', 1, '2025-06-02 07:46:21'),
(24, '18', '5', 'cc', 1, '2025-06-02 07:26:18'),
(23, '18', '5', 'bonjour', 1, '2025-06-02 07:25:46');

-- --------------------------------------------------------

--
-- Structure de la table `tbl_order`
--

DROP TABLE IF EXISTS `tbl_order`;
CREATE TABLE IF NOT EXISTS `tbl_order` (
  `id_order` int NOT NULL AUTO_INCREMENT,
  `acheteur_id` varchar(40) NOT NULL,
  `total_order` int NOT NULL,
  `createdate_order` datetime NOT NULL,
  `statut_order` varchar(20) NOT NULL,
  PRIMARY KEY (`id_order`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tbl_order`
--

INSERT INTO `tbl_order` (`id_order`, `acheteur_id`, `total_order`, `createdate_order`, `statut_order`) VALUES
(1, '5', 855, '2025-05-26 14:52:18', 'Confirmée'),
(2, '5', 966, '2025-06-02 13:53:05', 'Confirmée'),
(3, '21', 87, '2025-06-03 09:57:08', 'Confirmée');

-- --------------------------------------------------------

--
-- Structure de la table `tbl_order_items`
--

DROP TABLE IF EXISTS `tbl_order_items`;
CREATE TABLE IF NOT EXISTS `tbl_order_items` (
  `id_order_item` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `produit_id` int NOT NULL,
  `quantity` int NOT NULL,
  `prix` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_order_item`),
  KEY `order_id` (`order_id`),
  KEY `produit_id` (`produit_id`)
) ENGINE=MyISAM AUTO_INCREMENT=45 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tbl_order_items`
--

INSERT INTO `tbl_order_items` (`id_order_item`, `order_id`, `produit_id`, `quantity`, `prix`) VALUES
(1, 4, 2, 1, 765.00),
(2, 5, 3, 1, 500.00),
(3, 5, 4, 1, 5.57),
(4, 6, 1, 3, 87.00),
(5, 1, 4, 2, 5.57),
(6, 2, 3, 2, 500.00),
(7, 3, 2, 2, 765.00),
(8, 4, 3, 3, 500.00),
(9, 4, 4, 1, 5.57),
(10, 5, 1, 1, 87.00),
(11, 5, 4, 1, 5.57),
(12, 5, 3, 1, 500.00),
(13, 6, 2, 1, 765.00),
(14, 6, 3, 1, 500.00),
(15, 6, 1, 1, 87.00),
(16, 7, 2, 1, 765.00),
(17, 7, 1, 2, 87.00),
(18, 7, 3, 1, 500.00),
(19, 8, 4, 2, 5.57),
(20, 8, 3, 1, 500.00),
(21, 9, 3, 2, 500.00),
(22, 9, 4, 2, 5.57),
(23, 10, 1, 1, 87.00),
(24, 10, 6, 2, 76.00),
(25, 11, 8, 1, 555.00),
(26, 12, 4, 1, 5.57),
(27, 12, 3, 1, 500.00),
(28, 12, 6, 1, 90.00),
(29, 14, 3, 1, 500.00),
(30, 14, 2, 1, 765.00),
(31, 14, 7, 1, 876.00),
(32, 15, 3, 1, 500.00),
(33, 15, 7, 1, 876.00),
(34, 16, 2, 1, 765.00),
(35, 19, 3, 1, 500.00),
(36, 19, 6, 2, 90.00),
(37, 19, 7, 3, 876.00),
(38, 25, 8, 1, 555.00),
(39, 25, 9, 1, 67.00),
(40, 1, 2, 1, 765.00),
(41, 1, 6, 1, 90.00),
(42, 2, 7, 1, 876.00),
(43, 2, 6, 1, 90.00),
(44, 3, 1, 1, 87.00);

-- --------------------------------------------------------

--
-- Structure de la table `tbl_panier`
--

DROP TABLE IF EXISTS `tbl_panier`;
CREATE TABLE IF NOT EXISTS `tbl_panier` (
  `id_panier` int NOT NULL AUTO_INCREMENT,
  `client_id` varchar(40) NOT NULL,
  `produit_id` varchar(40) NOT NULL,
  `quantity_panier` int NOT NULL,
  `date_panier` date NOT NULL,
  PRIMARY KEY (`id_panier`)
) ENGINE=MyISAM AUTO_INCREMENT=81 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tbl_produit`
--

DROP TABLE IF EXISTS `tbl_produit`;
CREATE TABLE IF NOT EXISTS `tbl_produit` (
  `id_produit` int NOT NULL AUTO_INCREMENT,
  `vendeur_id` varchar(40) NOT NULL,
  `nom_produit` varchar(40) NOT NULL,
  `desc_produit` text NOT NULL,
  `prix_produit` float NOT NULL,
  `photo_produit` varchar(500) NOT NULL,
  `category_id` varchar(250) NOT NULL,
  `statut_produit` int NOT NULL DEFAULT '1',
  `stock_produit` int NOT NULL,
  `createdate_produit` datetime NOT NULL,
  `majdate_produit` datetime NOT NULL,
  `active_produit` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_produit`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tbl_produit`
--

INSERT INTO `tbl_produit` (`id_produit`, `vendeur_id`, `nom_produit`, `desc_produit`, `prix_produit`, `photo_produit`, `category_id`, `statut_produit`, `stock_produit`, `createdate_produit`, `majdate_produit`, `active_produit`) VALUES
(1, '19', 'Pull', 'Un beau pull avec des trous', 87, 'https://images-na.ssl-images-amazon.com/images/I/71MlLusjVKL._AC_UX679_.jpg', '1', 1, 6, '2025-03-24 00:00:00', '2025-03-24 14:08:11', 1),
(2, '19', 'T-shirt rayé', 'T-shirt style Zèbre', 765, 'https://ae01.alicdn.com/kf/HTB1C7J5OXXXXXXXXpXXq6xXFXXXR/Men-s-Short-Sleeve-O-Neck-T-Shirt-Zebra-3D-Printed-Tshirt-Men-Plus-Size-S.jpg', '1', 1, 0, '2025-03-24 00:00:00', '2025-03-24 14:15:31', 0),
(3, '18', 'Canapé', 'Canapé rouge', 500, 'https://www.iziva.com/articles-10-21/canape-Lazare-rouge.jpg', '3', 1, 1, '2025-03-31 08:32:33', '2025-03-31 08:32:33', 1),
(4, '19', 'Tshirt', 'tshirt blanc', 5.57, 'https://www.maisonstandards.com/15655-product_zoom/t-shirt-coton-lourd.jpg', '2', 1, 5, '2025-04-07 11:43:58', '2025-04-07 11:56:42', 0),
(6, '18', 'Chaise', 'Chaise Comfortable', 90, 'https://www.meubles-bois-massif.fr/hd/557-CH01-BOIS-PASSIONS_0001765.jpg', '3', 1, 1, '2025-05-05 13:27:47', '2025-05-12 12:52:28', 1),
(7, '18', 'TV', 'TV 4K Ultra HD', 876, '/marketplace/uploads/article/prod_6818bcf01cb592.58138270_tv.jpg', '3', 1, 3, '2025-05-05 13:28:16', '2025-05-05 13:28:16', 1),
(8, '18', 'Bureau', 'Bureau Bois resistant', 555, '/marketplace/uploads/article/prod_6818bd73380933.43128364_bureau.jpg', '3', 1, 7, '2025-05-05 13:30:27', '2025-05-05 13:30:27', 1),
(9, '18', 'Pull Femme', 'Pull chaud ', 67, '/marketplace/uploads/article/prod_6821eeafacf5c7.29005595_pullfemme.jpg', '2', 1, 7, '2025-05-12 12:50:55', '2025-05-12 12:50:55', 1);

-- --------------------------------------------------------

--
-- Structure de la table `tbl_roles`
--

DROP TABLE IF EXISTS `tbl_roles`;
CREATE TABLE IF NOT EXISTS `tbl_roles` (
  `id_role` int NOT NULL,
  `nom_role` varchar(50) NOT NULL,
  PRIMARY KEY (`id_role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tbl_roles`
--

INSERT INTO `tbl_roles` (`id_role`, `nom_role`) VALUES
(0, 'Buyer'),
(1, 'Seller'),
(2, 'Admin');

-- --------------------------------------------------------

--
-- Structure de la table `tbl_users`
--

DROP TABLE IF EXISTS `tbl_users`;
CREATE TABLE IF NOT EXISTS `tbl_users` (
  `id_user` int NOT NULL AUTO_INCREMENT,
  `nom_user` varchar(40) NOT NULL,
  `prenom_user` varchar(40) NOT NULL,
  `surname_user` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `mail_user` varchar(50) NOT NULL,
  `phone_user` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `birthday_user` date DEFAULT NULL,
  `password_user` varchar(250) NOT NULL,
  `role_user` int NOT NULL DEFAULT '0',
  `avatar_user` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `createdate_user` datetime NOT NULL,
  `modifdate_user` datetime NOT NULL,
  `active_user` int NOT NULL DEFAULT '1',
  PRIMARY KEY (`id_user`),
  UNIQUE KEY `mail_user` (`mail_user`),
  UNIQUE KEY `unique_mail_user` (`mail_user`),
  KEY `fk_role` (`role_user`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Déchargement des données de la table `tbl_users`
--

INSERT INTO `tbl_users` (`id_user`, `nom_user`, `prenom_user`, `surname_user`, `mail_user`, `phone_user`, `birthday_user`, `password_user`, `role_user`, `avatar_user`, `createdate_user`, `modifdate_user`, `active_user`) VALUES
(19, 'Chauvier', 'Joana', NULL, 'joanachauvier@hotmail.com', NULL, NULL, '$2y$10$3Hmv9Usk9BZL/Mrgy1.qQezd6Kvu8G85gw9whxc1jMY8glnaAGecu', 2, NULL, '2025-05-19 15:22:35', '2025-05-19 15:22:35', 1),
(5, 'Deroey', 'Didier', 'didouche', 'didi07@live.be', '0470806932', '2001-01-19', '$2y$10$fVaTcp7W4.qjIsvTZOPRjej8.6IhvHU5ffEh82PEW0yLdXNmbIKBu', 0, '1746443776_68189e00f075e_5.jpg', '2025-02-24 08:08:46', '2025-05-26 13:05:01', 1),
(15, 'Admin', 'Admin', '', 'admin@gmail.com', NULL, NULL, '$2y$10$.Sb5KIy1Kcsksix3nXKR7u9lxBX6U5F4KtcjE0DF.uk9pg5R4fH4a', 2, '1746439404_68188cec1d9fe_15.jpg', '2025-05-05 07:00:01', '2025-05-05 12:03:24', 1),
(18, 'Fadoua', 'Fadoua', '', 'fadoua@gmail.com', NULL, NULL, '$2y$10$wlHKjraBbd47aHxz/DB23O.7qDwQPwqgvM8/OT.v1avMXQ3ZVXu8m', 1, '1746443195_68189bbbc08b0_18.jpg', '2025-05-05 07:24:30', '2025-05-05 13:06:35', 1),
(21, 'saoud', 'fad', NULL, 'fad@gmail.com', NULL, NULL, '$2y$10$isZ9bG8vCrDO82QDmHW.8eYFgaNvyIoIOHGZqGP6QxYrFzRLtjLIS', 0, NULL, '2025-06-03 07:53:48', '2025-06-03 07:53:48', 1);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

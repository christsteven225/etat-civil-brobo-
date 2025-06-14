-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 14 juin 2025 à 13:15
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `mairie_brobo`
--

-- --------------------------------------------------------

--
-- Structure de la table `administrateurs`
--

CREATE TABLE `administrateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `administrateurs`
--

INSERT INTO `administrateurs` (`id`, `nom`, `email`, `mot_de_passe`, `date_inscription`) VALUES
(1, 'keke', 'kekeadmin@gmail.com', '$2y$10$Vt9uuE8PTf79OlEDzwl5HO4H0kxH7U1j4DZSi0Xb0BwhfIGxltw/S', '2025-05-09 18:01:55');

-- --------------------------------------------------------

--
-- Structure de la table `citoyens`
--

CREATE TABLE `citoyens` (
  `id` int(11) NOT NULL,
  `nom` varchar(255) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `date_inscription` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `citoyens`
--

INSERT INTO `citoyens` (`id`, `nom`, `prenom`, `email`, `mot_de_passe`, `date_inscription`) VALUES
(1, 'jean', 'marc', 'jeanmarc@gmail.com', '$2y$10$WdO2xaFufP4S14O/5A32N.l/./9cElbnTPvUWcUpVpmtyDJx.qecK', '2025-05-09 17:49:33'),
(2, 'JUNIOR', 'TOKPA', 'juniortokpa@gmail.com', '$2y$10$WAmRjG6NpEPu8Yk7Kz7ZyuJrxH6odp3A2LP41rXvNan5NE.GPE2.W', '2025-05-09 21:12:38'),
(3, 'Goulizan', 'Christ', 'stevenchrist857@gmail.com', '$2y$10$VN25sj/NEOzqZWuAL1/eE.AnXeyCx8hd1u6BsA9AZjBohIwRvxQtS', '2025-06-09 22:53:33'),
(4, 'Goulizan', 'Christ', 'christ@gmail.com', '$2y$10$lWIQVwWASZwct94VxS6ae.IjfDfV.CuzXRuTidGThkedzkqAJkVJe', '2025-06-10 09:12:25');

-- --------------------------------------------------------

--
-- Structure de la table `deces`
--

CREATE TABLE `deces` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `nom_defunt` varchar(255) DEFAULT NULL,
  `nom_pere_defunt` varchar(255) DEFAULT NULL,
  `nom_mere_defunt` varchar(255) DEFAULT NULL,
  `date_deces` date DEFAULT NULL,
  `lieu_deces` varchar(255) DEFAULT NULL,
  `cause_deces` varchar(255) DEFAULT NULL,
  `piece_identite_defunt` text DEFAULT NULL,
  `piece_identite_declarant` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `declarations`
--

CREATE TABLE `declarations` (
  `id` int(11) NOT NULL,
  `citoyen_id` int(11) NOT NULL,
  `type_acte` varchar(255) NOT NULL,
  `statut` enum('en_attente','accepte','rejete') DEFAULT 'en_attente',
  `date_declaration` datetime DEFAULT current_timestamp(),
  `fichiers` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `declarations`
--

INSERT INTO `declarations` (`id`, `citoyen_id`, `type_acte`, `statut`, `date_declaration`, `fichiers`) VALUES
(26, 3, 'naissance', 'accepte', '2025-06-13 19:02:48', 'file_684c75d8ca11d7.81131498.png,file_684c75d8ca4d06.66828277.png,file_684c75d8ca90e1.97110091.png,file_684c75d8cadb98.02356225.png'),
(27, 3, 'naissance', 'accepte', '2025-06-14 09:19:27', 'file_684d3e9f0a28e7.85668406.png,file_684d3e9f0a6732.01894454.png,file_684d3e9f0ab010.81347356.png,file_684d3e9f0afec7.19711580.png'),
(28, 3, 'naissance', 'accepte', '2025-06-14 10:23:26', 'file_684d4d9e432087.97464429.png,file_684d4d9e437674.62540149.png,file_684d4d9e43c5c8.89687128.png,file_684d4d9e475ef8.80600028.png'),
(29, 3, 'naissance', 'accepte', '2025-06-14 10:38:02', 'file_684d510a572d61.56821247.png,file_684d510a577ff9.73862921.png,file_684d510a57d983.18460138.png,file_684d510a5c8035.62072707.png');

-- --------------------------------------------------------

--
-- Structure de la table `declarations_deces`
--

CREATE TABLE `declarations_deces` (
  `id` int(11) NOT NULL,
  `citoyen_id` int(11) NOT NULL,
  `nom_defunt` varchar(255) NOT NULL,
  `nom_pere_defunt` varchar(255) NOT NULL,
  `nom_mere_defunt` varchar(255) NOT NULL,
  `date_deces` date NOT NULL,
  `lieu_deces` varchar(255) NOT NULL,
  `cause_deces` varchar(255) NOT NULL,
  `fichiers` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `declarations_deces`
--

INSERT INTO `declarations_deces` (`id`, `citoyen_id`, `nom_defunt`, `nom_pere_defunt`, `nom_mere_defunt`, `date_deces`, `lieu_deces`, `cause_deces`, `fichiers`, `created_at`, `updated_at`) VALUES
(1, 3, 'Christ', 'Goulizan', 'Christ Goulizan', '5004-04-24', 'QBHEZ FB', 'hgqrhyr', '', '2025-06-13 11:43:28', '2025-06-13 11:43:28'),
(2, 3, 'Christ', 'Goulizan', 'Christ Goulizan', '7976-05-24', 'zghqa', 'hgqrhyr', '', '2025-06-13 16:37:12', '2025-06-13 16:37:12');

-- --------------------------------------------------------

--
-- Structure de la table `declarations_naissance`
--

CREATE TABLE `declarations_naissance` (
  `id` int(11) NOT NULL,
  `citoyen_id` int(11) NOT NULL,
  `nom_complet` varchar(255) NOT NULL,
  `sex` varchar(10) NOT NULL,
  `nom_pere` varchar(255) NOT NULL,
  `nom_mere` varchar(255) NOT NULL,
  `date_naissance` date NOT NULL,
  `lieu_naissance` varchar(255) NOT NULL,
  `fichiers` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `declarations_naissance`
--

INSERT INTO `declarations_naissance` (`id`, `citoyen_id`, `nom_complet`, `sex`, `nom_pere`, `nom_mere`, `date_naissance`, `lieu_naissance`, `fichiers`, `created_at`, `updated_at`) VALUES
(1, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2007-09-20', 'dfhbeshjnsr', 'file_684c0e74e763a1.53930321.png,file_684c0e74e7c4f1.18426645.png,file_684c0e74e84fd7.62550746.png,file_684c0e74e8a6a2.53249889.png', '2025-06-13 11:41:41', '2025-06-13 11:41:41'),
(2, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2008-04-14', 'brobo', 'file_684c17c47c5043.17160357.png,file_684c17c47cb060.32217833.png,file_684c17c480b784.49105993.png,file_684c17c48118d1.22235407.png', '2025-06-13 12:21:24', '2025-06-13 12:21:24'),
(3, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '1999-08-03', 'brobo', 'file_684c53d9c801b9.70667642.png,file_684c53d9c8b253.49189496.png,file_684c53d9c96ce6.87438665.png,file_684c53d9ca15d3.23317043.png', '2025-06-13 16:37:45', '2025-06-13 16:37:45'),
(4, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2023-09-09', 'brobo', 'file_684c68addb2de5.12890339.png,file_684c68addb77d7.89447796.png,file_684c68addbca70.89365197.png,file_684c68addc1eb0.81871294.png', '2025-06-13 18:06:38', '2025-06-13 18:06:38'),
(5, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2020-09-09', 'brobo', 'file_684c6b25d2abc6.33725742.png,file_684c6b25d38cc5.95604445.png,file_684c6b25d46219.43043395.png,file_684c6b25d58034.96452136.png', '2025-06-13 18:17:09', '2025-06-13 18:17:09'),
(6, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2009-09-09', 'brobo', 'file_684c6d3d21cfe3.15138417.png,file_684c6d3d2247a3.86901420.png,file_684c6d3d22a447.03828163.png,file_684c6d3d2e5428.95646289.png', '2025-06-13 18:26:05', '2025-06-13 18:26:05'),
(7, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2003-09-09', 'Brobo', 'file_684c75d8ca11d7.81131498.png,file_684c75d8ca4d06.66828277.png,file_684c75d8ca90e1.97110091.png,file_684c75d8cadb98.02356225.png', '2025-06-13 19:02:48', '2025-06-13 19:02:48'),
(8, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2004-07-09', 'brobo', 'file_684d3e9f0a28e7.85668406.png,file_684d3e9f0a6732.01894454.png,file_684d3e9f0ab010.81347356.png,file_684d3e9f0afec7.19711580.png', '2025-06-14 09:19:27', '2025-06-14 09:19:27'),
(9, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2009-05-01', 'brobo', 'file_684d4d9e432087.97464429.png,file_684d4d9e437674.62540149.png,file_684d4d9e43c5c8.89687128.png,file_684d4d9e475ef8.80600028.png', '2025-06-14 10:23:26', '2025-06-14 10:23:26'),
(10, 3, 'Christ Goulizan', 'masculin', 'Christ', 'Goulizan', '2003-09-09', 'brobo', 'file_684d510a572d61.56821247.png,file_684d510a577ff9.73862921.png,file_684d510a57d983.18460138.png,file_684d510a5c8035.62072707.png', '2025-06-14 10:38:02', '2025-06-14 10:38:02');

-- --------------------------------------------------------

--
-- Structure de la table `dec_reconnaissance`
--

CREATE TABLE `dec_reconnaissance` (
  `id` int(11) NOT NULL,
  `citoyen_id` int(11) NOT NULL,
  `piece_identite_pere` varchar(255) NOT NULL,
  `piece_identite_mere` varchar(255) NOT NULL,
  `extrait_enfant` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Structure de la table `demandes_actes`
--

CREATE TABLE `demandes_actes` (
  `id` int(11) NOT NULL,
  `citoyen_id` int(11) NOT NULL,
  `type_acte` enum('naissance','mariage','deces') NOT NULL,
  `statut` enum('en_attente','accepte','rejete') DEFAULT 'en_attente',
  `date_demande` timestamp NOT NULL DEFAULT current_timestamp(),
  `fichiers` text DEFAULT NULL,
  `acte_valide` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `demandes_actes`
--

INSERT INTO `demandes_actes` (`id`, `citoyen_id`, `type_acte`, `statut`, `date_demande`, `fichiers`, `acte_valide`) VALUES
(57, 3, 'naissance', 'accepte', '2025-06-13 18:42:50', 'file_684c712a34c255.02274270.png,file_684c712a3513e2.15218904.png', 'acte_46.pdf'),
(58, 3, 'naissance', 'accepte', '2025-06-14 08:34:19', 'file_684d340bed5326.17190842.png,file_684d340bed9631.05567048.png', 'declaration_17.pdf'),
(59, 3, 'naissance', 'accepte', '2025-06-14 08:36:12', 'file_684d347cb5b388.81516977.png,file_684d347cb638f7.05658184.png', 'declaration_20.pdf'),
(60, 3, 'naissance', 'accepte', '2025-06-14 08:45:59', 'file_684d36c7edc118.75953363.png,file_684d36c7ee1cf8.69845026.png', 'declaration_19.pdf'),
(61, 3, 'naissance', 'en_attente', '2025-06-14 10:06:55', 'file_684d49bf2c9e06.17205595.png,file_684d49bf2d18b0.26694080.png', NULL),
(62, 3, 'naissance', 'accepte', '2025-06-14 10:09:58', 'file_684d4a76e43591.38911401.png,file_684d4a76e49607.83877166.png', 'declaration_10.pdf'),
(63, 3, 'mariage', 'accepte', '2025-06-14 10:15:47', '', NULL),
(64, 3, 'naissance', 'en_attente', '2025-06-14 10:29:43', 'file_684d4f17319983.84477755.png,file_684d4f1731f7f0.72658400.png', NULL),
(65, 3, 'naissance', 'accepte', '2025-06-14 10:42:20', 'file_684d520c585c88.50324141.pdf,file_684d520c58c431.14732281.pdf', 'declaration_29.pdf');

-- --------------------------------------------------------

--
-- Structure de la table `mariages`
--

CREATE TABLE `mariages` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `nom_epoux` varchar(255) DEFAULT NULL,
  `nom_epouse` varchar(255) DEFAULT NULL,
  `temoin_epoux` varchar(255) DEFAULT NULL,
  `temoin_epouse` varchar(255) DEFAULT NULL,
  `date_mariage` date DEFAULT NULL,
  `lieu_mariage` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `mariages`
--

INSERT INTO `mariages` (`id`, `demande_id`, `nom_epoux`, `nom_epouse`, `temoin_epoux`, `temoin_epouse`, `date_mariage`, `lieu_mariage`) VALUES
(8, 63, 'junior', 'belle', 'bht', 'tey', '2020-09-09', 'brobo');

-- --------------------------------------------------------

--
-- Structure de la table `naissances`
--

CREATE TABLE `naissances` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `nom_complet` varchar(255) DEFAULT NULL,
  `sex` varchar(255) DEFAULT NULL,
  `nom_pere` varchar(255) DEFAULT NULL,
  `nom_mere` varchar(255) DEFAULT NULL,
  `lieu_naissance` varchar(255) DEFAULT NULL,
  `date_naissance` date DEFAULT NULL,
  `pdfCnaissance` text DEFAULT NULL,
  `piece_identite_mere` text DEFAULT NULL,
  `piece_identite_pere` text DEFAULT NULL,
  `acte_mariage` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `naissances`
--

INSERT INTO `naissances` (`id`, `demande_id`, `nom_complet`, `sex`, `nom_pere`, `nom_mere`, `lieu_naissance`, `date_naissance`, `pdfCnaissance`, `piece_identite_mere`, `piece_identite_pere`, `acte_mariage`, `created_at`, `updated_at`) VALUES
(33, 57, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-13 18:42:50', '2025-06-13 18:42:50'),
(34, 58, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-14 08:34:20', '2025-06-14 08:34:20'),
(35, 59, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-14 08:36:12', '2025-06-14 08:36:12'),
(36, 60, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-14 08:46:00', '2025-06-14 08:46:00'),
(37, 61, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-14 10:06:55', '2025-06-14 10:06:55'),
(38, 62, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-14 10:09:59', '2025-06-14 10:09:59'),
(39, 64, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-14 10:29:43', '2025-06-14 10:29:43'),
(40, 65, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2025-06-14 10:42:20', '2025-06-14 10:42:20');

-- --------------------------------------------------------

--
-- Structure de la table `paiements`
--

CREATE TABLE `paiements` (
  `id` int(11) NOT NULL,
  `demande_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `operateur` enum('mtn','orange','moov','wave') DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `statut` enum('en_attente','effectue','echoue') DEFAULT 'en_attente',
  `date_paiement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiements`
--

INSERT INTO `paiements` (`id`, `demande_id`, `montant`, `operateur`, `numero`, `statut`, `date_paiement`) VALUES
(29, 57, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 08:27:10'),
(30, 58, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 08:34:59'),
(31, 60, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 08:47:17'),
(32, 62, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 10:14:40'),
(33, 63, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 10:16:38'),
(34, 65, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 10:48:38');

-- --------------------------------------------------------

--
-- Structure de la table `paiements_declarations`
--

CREATE TABLE `paiements_declarations` (
  `id` int(11) NOT NULL,
  `declaration_id` int(11) NOT NULL,
  `montant` decimal(10,2) NOT NULL,
  `operateur` enum('mtn','orange','moov','wave') DEFAULT NULL,
  `numero` varchar(20) DEFAULT NULL,
  `statut` enum('en_attente','effectue','echoue') DEFAULT 'en_attente',
  `date_paiement` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `paiements_declarations`
--

INSERT INTO `paiements_declarations` (`id`, `declaration_id`, `montant`, `operateur`, `numero`, `statut`, `date_paiement`) VALUES
(9, 26, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 07:41:32'),
(10, 28, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 10:26:07'),
(11, 29, 1000.00, 'mtn', '0515205214', 'effectue', '2025-06-14 10:41:21');

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `citoyens`
--
ALTER TABLE `citoyens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Index pour la table `deces`
--
ALTER TABLE `deces`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `demande_id` (`demande_id`);

--
-- Index pour la table `declarations`
--
ALTER TABLE `declarations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citoyen_id` (`citoyen_id`);

--
-- Index pour la table `declarations_deces`
--
ALTER TABLE `declarations_deces`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citoyen_id` (`citoyen_id`);

--
-- Index pour la table `declarations_naissance`
--
ALTER TABLE `declarations_naissance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citoyen_id` (`citoyen_id`);

--
-- Index pour la table `dec_reconnaissance`
--
ALTER TABLE `dec_reconnaissance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citoyen_id` (`citoyen_id`);

--
-- Index pour la table `demandes_actes`
--
ALTER TABLE `demandes_actes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `citoyen_id` (`citoyen_id`);

--
-- Index pour la table `mariages`
--
ALTER TABLE `mariages`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `demande_id` (`demande_id`);

--
-- Index pour la table `naissances`
--
ALTER TABLE `naissances`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `demande_id` (`demande_id`);

--
-- Index pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `demande_id` (`demande_id`);

--
-- Index pour la table `paiements_declarations`
--
ALTER TABLE `paiements_declarations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `declaration_id` (`declaration_id`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `administrateurs`
--
ALTER TABLE `administrateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `citoyens`
--
ALTER TABLE `citoyens`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `deces`
--
ALTER TABLE `deces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `declarations`
--
ALTER TABLE `declarations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `declarations_deces`
--
ALTER TABLE `declarations_deces`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `declarations_naissance`
--
ALTER TABLE `declarations_naissance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT pour la table `dec_reconnaissance`
--
ALTER TABLE `dec_reconnaissance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT pour la table `demandes_actes`
--
ALTER TABLE `demandes_actes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

--
-- AUTO_INCREMENT pour la table `mariages`
--
ALTER TABLE `mariages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT pour la table `naissances`
--
ALTER TABLE `naissances`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT pour la table `paiements`
--
ALTER TABLE `paiements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT pour la table `paiements_declarations`
--
ALTER TABLE `paiements_declarations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `deces`
--
ALTER TABLE `deces`
  ADD CONSTRAINT `deces_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_actes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `declarations`
--
ALTER TABLE `declarations`
  ADD CONSTRAINT `declarations_ibfk_1` FOREIGN KEY (`citoyen_id`) REFERENCES `citoyens` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `declarations_deces`
--
ALTER TABLE `declarations_deces`
  ADD CONSTRAINT `declarations_deces_ibfk_1` FOREIGN KEY (`citoyen_id`) REFERENCES `citoyens` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `declarations_naissance`
--
ALTER TABLE `declarations_naissance`
  ADD CONSTRAINT `declarations_naissance_ibfk_1` FOREIGN KEY (`citoyen_id`) REFERENCES `citoyens` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `dec_reconnaissance`
--
ALTER TABLE `dec_reconnaissance`
  ADD CONSTRAINT `dec_reconnaissance_ibfk_1` FOREIGN KEY (`citoyen_id`) REFERENCES `citoyens` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `demandes_actes`
--
ALTER TABLE `demandes_actes`
  ADD CONSTRAINT `demandes_actes_ibfk_1` FOREIGN KEY (`citoyen_id`) REFERENCES `citoyens` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `mariages`
--
ALTER TABLE `mariages`
  ADD CONSTRAINT `mariages_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_actes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `naissances`
--
ALTER TABLE `naissances`
  ADD CONSTRAINT `naissances_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_actes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements`
--
ALTER TABLE `paiements`
  ADD CONSTRAINT `paiements_ibfk_1` FOREIGN KEY (`demande_id`) REFERENCES `demandes_actes` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `paiements_declarations`
--
ALTER TABLE `paiements_declarations`
  ADD CONSTRAINT `paiements_declarations_ibfk_1` FOREIGN KEY (`declaration_id`) REFERENCES `declarations` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

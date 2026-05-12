-- ================================================
-- TinyTrack — Module Gestion Reclamation
-- Author : Mahdi Ben Slimene
-- ================================================

USE tinytrack;

-- Table des reclamations
CREATE TABLE IF NOT EXISTS `reclamations` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nom_client` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `sujet` VARCHAR(200) NOT NULL,
    `description` TEXT NOT NULL,
    `statut` ENUM('En attente', 'Traité') DEFAULT 'En attente',
    `sentiment` ENUM('positif','neutre','negatif') DEFAULT NULL,
    `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des reponses
CREATE TABLE IF NOT EXISTS `reponses` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_reclamation` INT(11) NOT NULL,
    `message` TEXT NOT NULL,
    `auteur` VARCHAR(100) DEFAULT 'Admin',
    `date_reponse` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    CONSTRAINT `fk_reclamation` FOREIGN KEY (`id_reclamation`) REFERENCES `reclamations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

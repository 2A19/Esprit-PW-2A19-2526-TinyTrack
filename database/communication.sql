-- ================================================
-- TinyTrack — Module Communication parents
-- Author : Rajhi Amen Allah
-- A executer dans phpMyAdmin sur la base tinytrack.
-- ================================================

USE tinytrack;

-- Table des conversations parent <-> staff (educateur ou admin)
CREATE TABLE IF NOT EXISTS `conversation` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `parent_id` INT(11) NOT NULL,
    `staff_id` INT(11) NOT NULL,
    `status` VARCHAR(20) DEFAULT 'open',
    `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    `needs_admin_attention` TINYINT(1) NOT NULL DEFAULT 0,
    `admin_alert_at` DATETIME DEFAULT NULL,
    `admin_alert_by_role` VARCHAR(20) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_conversation_parent` (`parent_id`),
    KEY `fk_conversation_staff` (`staff_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Table des messages echanges dans chaque conversation
-- Nommee chat_message pour eviter conflit avec la table 'message' deja
-- presente dans tinytrack (utilisee pour les notifications generales).
CREATE TABLE IF NOT EXISTS `chat_message` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `conversation_id` INT(11) NOT NULL,
    `sender_id` INT(11) NOT NULL,
    `sender_role` VARCHAR(20) DEFAULT NULL,
    `body` TEXT NOT NULL,
    `read_at` DATETIME DEFAULT NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT current_timestamp(),
    `needs_admin_attention` TINYINT(1) NOT NULL DEFAULT 0,
    `admin_alert_at` DATETIME DEFAULT NULL,
    `admin_alert_by_role` VARCHAR(20) DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `fk_chat_message_conv` (`conversation_id`),
    CONSTRAINT `fk_chat_message_conv` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

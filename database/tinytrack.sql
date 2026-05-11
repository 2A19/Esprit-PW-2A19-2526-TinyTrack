-- ================================================
-- TinyTrack — Base de données
-- Module : Gestion des enfants & inscriptions
-- Responsable : Eya Belhaj Mabrouk
-- ESPRIT 2A19 — 2025-2026
-- ================================================

CREATE DATABASE IF NOT EXISTS tinytrack
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE tinytrack;

-- ================================================
-- Table : user (table partagée — tous les acteurs)
-- ================================================
CREATE TABLE IF NOT EXISTS user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_unique VARCHAR(20) UNIQUE DEFAULT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL DEFAULT '',
    role ENUM('admin', 'educateur', 'parent') NOT NULL,
    telephone VARCHAR(20) DEFAULT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    date_naissance DATE DEFAULT NULL,
    adresse VARCHAR(255) DEFAULT NULL,
    cin VARCHAR(20) DEFAULT NULL,
    date_embauche DATE DEFAULT NULL,
    specialite VARCHAR(100) DEFAULT NULL,
    diplome VARCHAR(100) DEFAULT NULL,
    statut ENUM('actif', 'inactif', 'en_attente') NOT NULL DEFAULT 'actif',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- Table : groupe
-- ================================================
CREATE TABLE IF NOT EXISTS groupe (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    niveau ENUM('petit', 'moyen', 'grand') NOT NULL,
    capacite INT NOT NULL DEFAULT 20,
    educateur_id INT DEFAULT NULL,
    CONSTRAINT fk_groupe_educateur
        FOREIGN KEY (educateur_id) REFERENCES user(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- Table : enfant
-- ================================================
CREATE TABLE IF NOT EXISTS enfant (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_unique VARCHAR(20) UNIQUE DEFAULT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    date_naissance DATE NOT NULL,
    sexe ENUM('M', 'F') NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    groupe_id INT DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    date_inscription DATE NOT NULL DEFAULT (CURRENT_DATE),
    statut ENUM('actif', 'archive') NOT NULL DEFAULT 'actif',
    CONSTRAINT fk_enfant_groupe
        FOREIGN KEY (groupe_id) REFERENCES groupe(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_enfant_parent
        FOREIGN KEY (parent_id) REFERENCES user(id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- Table : dossier_medical (1:1 avec enfant)
-- ================================================
CREATE TABLE IF NOT EXISTS dossier_medical (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enfant_id INT NOT NULL UNIQUE,
    groupe_sanguin VARCHAR(5) DEFAULT NULL,
    allergies TEXT DEFAULT NULL,
    maladies_chroniques TEXT DEFAULT NULL,
    vaccinations TEXT DEFAULT NULL,
    medecin_traitant VARCHAR(100) DEFAULT NULL,
    telephone_urgence VARCHAR(20) DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    CONSTRAINT fk_dossier_enfant
        FOREIGN KEY (enfant_id) REFERENCES enfant(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- Table : message
-- ================================================
CREATE TABLE IF NOT EXISTS message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    type ENUM('message','annonce','urgence') DEFAULT 'message',
    lu BOOLEAN DEFAULT FALSE,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (expediteur_id) REFERENCES user(id),
    FOREIGN KEY (destinataire_id) REFERENCES user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- Données de test : Users
-- Admin a un mot de passe par défaut (123456)
-- Éducateurs et parents : mot de passe VIDE — ils doivent s'inscrire pour en recevoir un par email
-- ================================================
INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, role, telephone, date_naissance, adresse, cin, date_embauche, specialite, diplome) VALUES
('TT-1001', 'Admin', 'TinyTrack', 'admin@tinytrack.tn', '$2y$10$aIbOx7k.MaCt7qHtxHlhJ.YrPNTOj.O7W00GhdVINrXgHrAIxIpRy', 'admin', '+216 71 000 000', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-1002', 'Chtioui', 'Fatma', 'eya.belhajmabrrouk@gmail.com', '', 'educateur', '+216 22 111 111', '1992-06-15', 'Rue de la Liberté, Ariana, Tunisie', '09876543', '2023-09-01', 'Petite enfance & Éveil', 'Licence en Sciences de l éducation'),
('TT-1003', 'Sassi', 'Rim', 'rim.educatrice@tinytrack.tn', '', 'educateur', '+216 22 222 222', '1995-03-22', 'Avenue Habib Bourguiba, Tunis', '12345678', '2024-01-15', 'Activités motrices & Sport', 'Master en Psychologie de l enfant'),
('TT-1004', 'Dridi', 'Houda', 'houda.educatrice@tinytrack.tn', '', 'educateur', '+216 22 333 333', '1990-11-08', 'Cité El Ghazala, Ariana', '07654321', '2022-09-01', 'Arts & Créativité', 'Licence en Arts plastiques'),
('TT-2001', 'Ben Ali', 'Sana', 'eya.belhajmabrouk@esprit.tn', '', 'parent', '+216 55 100 100', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-2002', 'Trabelsi', 'Ahmed', 'ahmed.trabelsi@gmail.com', '', 'parent', '+216 55 200 200', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-2003', 'Mansouri', 'Leila', 'leila.mansouri@gmail.com', '', 'parent', '+216 55 300 300', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-2004', 'Bouazizi', 'Karim', 'karim.bouazizi@gmail.com', '', 'parent', '+216 55 400 400', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-2005', 'Chaabane', 'Mona', 'mona.chaabane@gmail.com', '', 'parent', '+216 55 500 500', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-2006', 'Meddeb', 'Anis', 'anis.meddeb@gmail.com', '', 'parent', '+216 55 600 600', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-2007', 'Hammami', 'Samira', 'samira.hammami@gmail.com', '', 'parent', '+216 55 700 700', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-2008', 'Jebali', 'Nabil', 'nabil.jebali@gmail.com', '', 'parent', '+216 55 800 800', NULL, NULL, NULL, NULL, NULL, NULL);

-- ================================================
-- Données de test : Groupes
-- ================================================
INSERT INTO groupe (nom, niveau, capacite, educateur_id) VALUES
('Les Papillons', 'petit', 15, 2),
('Les Etoiles', 'moyen', 20, 3),
('Les Champions', 'grand', 20, 4);

-- ================================================
-- Données de test : Enfants (avec code_unique, groupe_id et parent_id)
-- ================================================
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut) VALUES
('TT-3001', 'Ben Ali', 'Yassine', '2022-03-15', 'M', 1, 5, '2025-09-01', 'actif'),
('TT-3002', 'Trabelsi', 'Lina', '2021-07-22', 'F', 2, 6, '2025-09-01', 'actif'),
('TT-3003', 'Mansouri', 'Adam', '2023-01-10', 'M', 1, 7, '2025-09-15', 'actif'),
('TT-3004', 'Bouazizi', 'Nour', '2022-11-05', 'F', 2, 8, '2025-09-01', 'actif'),
('TT-3005', 'Chaabane', 'Sami', '2021-04-18', 'M', 3, 9, '2025-10-01', 'actif'),
('TT-3006', 'Meddeb', 'Aya', '2023-06-30', 'F', 1, 10, '2026-01-10', 'actif'),
('TT-3007', 'Hammami', 'Rayan', '2022-08-12', 'M', 3, 11, '2026-02-01', 'actif'),
('TT-3008', 'Jebali', 'Ines', '2021-12-25', 'F', 2, 12, '2025-09-01', 'archive');

-- ================================================
-- Données de test : Dossiers médicaux
-- ================================================
INSERT INTO dossier_medical (enfant_id, groupe_sanguin, allergies, maladies_chroniques, vaccinations, medecin_traitant, telephone_urgence, notes) VALUES
(1, 'A+', 'Arachides', '', 'BCG, DTC, ROR', 'Dr. Ben Salah', '+216 71 123 456', 'Eviter les noix à la cantine'),
(2, 'O+', 'Aucune', '', 'BCG, DTC, ROR, Hépatite B', 'Dr. Khelifi', '+216 71 234 567', ''),
(3, 'B+', 'Lait de vache', 'Asthme léger', 'BCG, DTC', 'Dr. Mansour', '+216 71 345 678', 'Ventoline dans le sac'),
(4, 'AB-', 'Aucune', '', 'BCG, DTC, ROR', 'Dr. Zouari', '+216 71 456 789', ''),
(5, 'O-', 'Poussière', '', 'BCG, DTC, ROR, Varicelle', 'Dr. Hamdi', '+216 71 567 890', 'Rhinite allergique saisonnière'),
(6, 'A+', 'Aucune', '', 'BCG, DTC', 'Dr. Bahri', '+216 71 678 901', 'Vaccination ROR prévue en avril'),
(7, 'B-', 'Gluten', '', 'BCG, DTC, ROR', 'Dr. Saidi', '+216 71 789 012', 'Régime sans gluten strict');

-- ================================================
-- Données de test : Messages
-- ================================================
INSERT INTO message (expediteur_id, destinataire_id, sujet, contenu, type, lu, date_envoi) VALUES
(2, 5, 'Rapport du jour - Yassine', 'Bonjour Mme Ben Ali,\nYassine a passe une excellente journee.', 'message', 0, '2026-04-11 16:30:00'),
(1, 5, 'Reunion parents - Samedi 19 avril', 'Chers parents,\nNous organisons une reunion le samedi 19 avril a 10h.', 'annonce', 0, '2026-04-10 09:00:00'),
(2, 5, 'Allergie - Rappel cantine', 'Nous avons bien note l allergie aux arachides de Yassine.', 'message', 1, '2026-04-08 14:15:00'),
(1, 5, 'Bienvenue sur TinyTrack', 'Bienvenue sur la plateforme TinyTrack !', 'message', 1, '2026-04-01 08:00:00'),
(1, 2, 'Planning semaine prochaine', 'Veuillez prendre note du planning modifie.', 'message', 0, '2026-04-11 10:00:00'),
(5, 2, 'Question sur Yassine', 'Est-ce que Yassine a bien dormi a la sieste ?', 'message', 1, '2026-04-09 17:30:00');

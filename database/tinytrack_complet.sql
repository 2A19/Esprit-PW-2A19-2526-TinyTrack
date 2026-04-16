-- ================================================
-- TinyTrack — Base de données COMPLÈTE
-- Tous les modules intégrés
-- ESPRIT 2A19 — 2025-2026
-- ================================================

DROP DATABASE IF EXISTS tinytrack;

CREATE DATABASE tinytrack
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE tinytrack;

-- ================================================
-- MODULE : Gestion des accès (Eya Belhaj Mabrouk)
-- ================================================

CREATE TABLE user (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code_unique VARCHAR(20) UNIQUE DEFAULT NULL,
    nom VARCHAR(50) NOT NULL,
    prenom VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    mot_de_passe VARCHAR(255) NOT NULL DEFAULT '',
    mdp_temp VARCHAR(255) DEFAULT NULL,
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

CREATE TABLE groupe (
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
-- MODULE : Gestion des enfants (Eya Belhaj Mabrouk)
-- ================================================

CREATE TABLE enfant (
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

CREATE TABLE dossier_medical (
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
-- MODULE : Communication (Amen Allah Rajhi)
-- ================================================

CREATE TABLE message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    expediteur_id INT NOT NULL,
    destinataire_id INT NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    contenu TEXT NOT NULL,
    type ENUM('message','annonce','urgence') DEFAULT 'message',
    lu BOOLEAN DEFAULT FALSE,
    date_envoi DATETIME DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_message_expediteur FOREIGN KEY (expediteur_id) REFERENCES user(id),
    CONSTRAINT fk_message_destinataire FOREIGN KEY (destinataire_id) REFERENCES user(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    sender_role VARCHAR(50) NOT NULL,
    body TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- MODULE : Gestion des événements (Rayen Ajili)
-- ================================================

CREATE TABLE evenement (
    id INT AUTO_INCREMENT PRIMARY KEY,
    titre VARCHAR(120) NOT NULL,
    description TEXT DEFAULT NULL,
    date DATE NOT NULL,
    heure_debut TIME NOT NULL,
    heure_fin TIME NOT NULL,
    type ENUM('concert','conference','sport','atelier','festival','formation','exposition','autre') NOT NULL,
    lieu VARCHAR(255) NOT NULL,
    capacite_max INT NOT NULL DEFAULT 50,
    prix DECIMAL(8,2) NOT NULL DEFAULT 0,
    groupe_id INT DEFAULT NULL,
    statut ENUM('planifie','en_cours','termine','annule') NOT NULL DEFAULT 'planifie'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE reservation (
    id INT AUTO_INCREMENT PRIMARY KEY,
    evenement_id INT NOT NULL,
    enfant_id INT DEFAULT NULL,
    parent_id INT DEFAULT NULL,
    nb_accompagnants INT NOT NULL DEFAULT 0,
    commentaire TEXT DEFAULT NULL,
    date_reservation DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    statut ENUM('confirmee','en_attente','annulee') NOT NULL DEFAULT 'en_attente',
    paiement ENUM('paye','non_paye') NOT NULL DEFAULT 'non_paye',
    CONSTRAINT fk_reservation_evenement
        FOREIGN KEY (evenement_id) REFERENCES evenement(id)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- MODULE : Journal de bord (Mohamed Fadhlaoui)
-- ================================================

CREATE TABLE activite (
    id_activite INT AUTO_INCREMENT PRIMARY KEY,
    nom_activite VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    date_activite DATE NOT NULL,
    heure_activite TIME NOT NULL,
    id_educateur INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE rapport (
    id_rapport INT AUTO_INCREMENT PRIMARY KEY,
    contenu_rapport TEXT NOT NULL,
    date_rapport DATE NOT NULL,
    id_activite INT NOT NULL,
    id_educateur INT DEFAULT NULL,
    CONSTRAINT fk_rapport_activite
        FOREIGN KEY (id_activite) REFERENCES activite(id_activite)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ================================================
-- DONNÉES DE TEST : Users
-- Admin mot de passe : 123456
-- Éducateurs/Parents : mot de passe VIDE (inscription requise)
-- ================================================

INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, role, telephone, date_naissance, adresse, cin, date_embauche, specialite, diplome) VALUES
('TT-1001', 'Admin', 'TinyTrack', 'admin@tinytrack.tn', '$2y$10$aIbOx7k.MaCt7qHtxHlhJ.YrPNTOj.O7W00GhdVINrXgHrAIxIpRy', 'admin', '+216 71 000 000', NULL, NULL, NULL, NULL, NULL, NULL),
('TT-1002', 'Chtioui', 'Fatma', 'eya.belhajmabrrouk@gmail.com', '', 'educateur', '+216 22 111 111', '1992-06-15', 'Rue de la Liberté, Ariana', '09876543', '2023-09-01', 'Petite enfance & Éveil', 'Licence en Sciences de l éducation'),
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
-- DONNÉES DE TEST : Groupes
-- ================================================

INSERT INTO groupe (nom, niveau, capacite, educateur_id) VALUES
('Les Papillons', 'petit', 15, 2),
('Les Etoiles', 'moyen', 20, 3),
('Les Champions', 'grand', 20, 4);

-- ================================================
-- DONNÉES DE TEST : Enfants
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
-- DONNÉES DE TEST : Dossiers médicaux
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
-- DONNÉES DE TEST : Messages
-- ================================================

INSERT INTO message (expediteur_id, destinataire_id, sujet, contenu, type, lu, date_envoi) VALUES
(2, 5, 'Rapport du jour - Yassine', 'Bonjour Mme Ben Ali,\nYassine a passe une excellente journee.', 'message', 0, '2026-04-11 16:30:00'),
(1, 5, 'Reunion parents - Samedi 19 avril', 'Chers parents,\nNous organisons une reunion le samedi 19 avril a 10h.', 'annonce', 0, '2026-04-10 09:00:00'),
(2, 5, 'Allergie - Rappel cantine', 'Nous avons bien note l allergie aux arachides de Yassine.', 'message', 1, '2026-04-08 14:15:00'),
(1, 5, 'Bienvenue sur TinyTrack', 'Bienvenue sur la plateforme TinyTrack !', 'message', 1, '2026-04-01 08:00:00'),
(1, 2, 'Planning semaine prochaine', 'Veuillez prendre note du planning modifie.', 'message', 0, '2026-04-11 10:00:00'),
(5, 2, 'Question sur Yassine', 'Est-ce que Yassine a bien dormi a la sieste ?', 'message', 1, '2026-04-09 17:30:00');

-- ================================================
-- DONNÉES DE TEST : Messages (chat - Amen Allah)
-- ================================================

INSERT INTO messages (conversation_id, sender_id, sender_role, body, created_at) VALUES
(1, 5, 'parent', 'Bonjour, comment va Yassine aujourd hui ?', '2026-04-10 08:30:00'),
(1, 2, 'educateur', 'Bonjour Mme Ben Ali ! Yassine va tres bien, il est de bonne humeur ce matin.', '2026-04-10 09:15:00'),
(1, 5, 'parent', 'Est-ce qu il a bien mange a la cantine ?', '2026-04-10 12:30:00'),
(1, 2, 'educateur', 'Oui il a tout mange ! Il a meme redemande du dessert.', '2026-04-10 13:00:00'),
(1, 1, 'admin', 'Rappel : la reunion parents est prevue samedi a 10h.', '2026-04-10 14:00:00'),
(1, 5, 'parent', 'Merci pour l information, nous serons presents.', '2026-04-10 14:30:00');

-- ================================================
-- DONNÉES DE TEST : Événements
-- ================================================

INSERT INTO evenement (titre, description, date, heure_debut, heure_fin, type, lieu, capacite_max, prix, statut) VALUES
('Fete de Printemps', 'Grande fete pour celebrer le printemps avec les enfants', '2026-04-25', '09:00', '12:00', 'festival', 'Jardin TinyTrack, Ariana', 50, 0, 'planifie'),
('Atelier Peinture', 'Atelier creatif pour les petits artistes', '2026-04-20', '14:00', '16:00', 'atelier', 'Salle Creative, TinyTrack', 20, 5.00, 'planifie'),
('Journee Sportive', 'Mini-olympiades pour les enfants de 3 a 6 ans', '2026-05-01', '08:30', '11:30', 'sport', 'Terrain de sport, Ariana', 40, 0, 'planifie'),
('Spectacle de Marionnettes', 'Spectacle interactif avec les marionnettes du pays', '2026-04-18', '10:00', '11:00', 'concert', 'Salle des fetes, TinyTrack', 60, 3.00, 'en_cours'),
('Formation Parents', 'Seance de sensibilisation sur la nutrition infantile', '2026-04-15', '17:00', '19:00', 'formation', 'Salle de reunion, TinyTrack', 30, 0, 'termine');

-- ================================================
-- DONNÉES DE TEST : Réservations
-- ================================================

INSERT INTO reservation (evenement_id, enfant_id, parent_id, nb_accompagnants, commentaire, statut, paiement) VALUES
(1, 1, 5, 2, 'Nous viendrons avec les grands-parents', 'confirmee', 'paye'),
(1, 2, 6, 1, '', 'confirmee', 'paye'),
(1, 3, 7, 0, 'Adam est allergique aux arachides', 'en_attente', 'non_paye'),
(2, 4, 8, 1, 'Nour adore la peinture', 'confirmee', 'paye'),
(2, 6, 10, 0, '', 'en_attente', 'non_paye'),
(3, 5, 9, 2, '', 'confirmee', 'non_paye'),
(4, 1, 5, 1, '', 'confirmee', 'paye'),
(4, 7, 11, 0, 'Rayan est un peu timide', 'en_attente', 'non_paye');

-- ================================================
-- DONNÉES DE TEST : Activités
-- ================================================

INSERT INTO activite (nom_activite, description, date_activite, heure_activite, id_educateur) VALUES
('Peinture libre', 'Atelier de peinture avec les doigts', '2026-04-10', '09:30', 2),
('Jeux en plein air', 'Course et jeux de ballon dans le jardin', '2026-04-10', '10:30', 2),
('Lecture de contes', 'Lecture interactive avec marionnettes', '2026-04-11', '09:00', 3),
('Eveil musical', 'Decouverte des instruments de musique', '2026-04-11', '14:00', 3),
('Jeux de construction', 'Lego et puzzles pour la motricite', '2026-04-12', '09:30', 4);

-- ================================================
-- DONNÉES DE TEST : Rapports
-- ================================================

INSERT INTO rapport (contenu_rapport, date_rapport, id_activite, id_educateur) VALUES
('Yassine a adore la peinture, tres creatif et concentre', '2026-04-10', 1, 2),
('Lina etait un peu fatiguee mais a participe aux jeux', '2026-04-10', 2, 2),
('Adam a ecoute le conte attentivement et a pose des questions', '2026-04-11', 3, 3),
('Sami etait agite pendant la musique mais a fini par se calmer', '2026-04-11', 4, 3),
('Yassine a construit une tour impressionnante avec les Lego', '2026-04-12', 5, 4);

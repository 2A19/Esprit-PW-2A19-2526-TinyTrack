-- ================================================
-- TinyTrack — Module Événements
-- Responsable : Rayen Ajili
-- ESPRIT 2A19 — 2025-2026
-- ================================================

CREATE DATABASE IF NOT EXISTS evenement
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE evenement;

CREATE TABLE IF NOT EXISTS evenement (
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

CREATE TABLE IF NOT EXISTS reservation (
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

-- Données de test
INSERT INTO evenement (titre, description, date, heure_debut, heure_fin, type, lieu, capacite_max, prix, statut) VALUES
('Fete de Printemps', 'Grande fete pour celebrer le printemps avec les enfants', '2026-04-25', '09:00', '12:00', 'festival', 'Jardin TinyTrack, Ariana', 50, 0, 'planifie'),
('Atelier Peinture', 'Atelier creatif pour les petits artistes', '2026-04-20', '14:00', '16:00', 'atelier', 'Salle Creative, TinyTrack', 20, 5.00, 'planifie'),
('Journee Sportive', 'Mini-olympiades pour les enfants de 3 a 6 ans', '2026-05-01', '08:30', '11:30', 'sport', 'Terrain de sport, Ariana', 40, 0, 'planifie'),
('Spectacle de Marionnettes', 'Spectacle interactif avec les marionnettes du pays', '2026-04-18', '10:00', '11:00', 'concert', 'Salle des fetes, TinyTrack', 60, 3.00, 'en_cours'),
('Formation Parents', 'Seance de sensibilisation sur la nutrition infantile', '2026-04-15', '17:00', '19:00', 'formation', 'Salle de reunion, TinyTrack', 30, 0, 'termine');

INSERT INTO reservation (evenement_id, enfant_id, parent_id, nb_accompagnants, commentaire, statut, paiement) VALUES
(1, 1, 5, 2, 'Nous viendrons avec les grands-parents', 'confirmee', 'paye'),
(1, 2, 6, 1, '', 'confirmee', 'paye'),
(1, 3, 7, 0, 'Adam est allergique aux arachides', 'en_attente', 'non_paye'),
(2, 4, 8, 1, 'Nour adore la peinture', 'confirmee', 'paye'),
(2, 6, 10, 0, '', 'en_attente', 'non_paye'),
(3, 5, 9, 2, '', 'confirmee', 'non_paye'),
(4, 1, 5, 1, '', 'confirmee', 'paye'),
(4, 7, 11, 0, 'Rayan est un peu timide', 'en_attente', 'non_paye');

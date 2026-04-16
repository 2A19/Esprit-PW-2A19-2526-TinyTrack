-- ================================================
-- TinyTrack — Module Journal de Bord
-- Responsable : Mohamed Fadhlaoui
-- ESPRIT 2A19 — 2025-2026
-- ================================================

CREATE DATABASE IF NOT EXISTS tinytrack
CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE tinytrack;

CREATE TABLE IF NOT EXISTS activite (
    id_activite INT AUTO_INCREMENT PRIMARY KEY,
    nom_activite VARCHAR(100) NOT NULL,
    description TEXT DEFAULT NULL,
    date_activite DATE NOT NULL,
    heure_activite TIME NOT NULL,
    id_educateur INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS rapport (
    id_rapport INT AUTO_INCREMENT PRIMARY KEY,
    contenu_rapport TEXT NOT NULL,
    date_rapport DATE NOT NULL,
    id_activite INT NOT NULL,
    id_educateur INT DEFAULT NULL,
    CONSTRAINT fk_rapport_activite
        FOREIGN KEY (id_activite) REFERENCES activite(id_activite)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Données de test
INSERT INTO activite (nom_activite, description, date_activite, heure_activite, id_educateur) VALUES
('Peinture libre', 'Atelier de peinture avec les doigts', '2026-04-10', '09:30', 2),
('Jeux en plein air', 'Course et jeux de ballon dans le jardin', '2026-04-10', '10:30', 2),
('Lecture de contes', 'Lecture interactive avec marionnettes', '2026-04-11', '09:00', 3),
('Eveil musical', 'Decouverte des instruments de musique', '2026-04-11', '14:00', 3),
('Jeux de construction', 'Lego et puzzles pour la motricite', '2026-04-12', '09:30', 4);

INSERT INTO rapport (contenu_rapport, date_rapport, id_activite, id_educateur) VALUES
('Yassine a adore la peinture, tres creatif et concentre', '2026-04-10', 1, 2),
('Lina etait un peu fatiguee mais a participe aux jeux', '2026-04-10', 2, 2),
('Adam a ecoute le conte attentivement et a pose des questions', '2026-04-11', 3, 3),
('Sami etait agite pendant la musique mais a fini par se calmer', '2026-04-11', 4, 3),
('Yassine a construit une tour impressionnante avec les Lego', '2026-04-12', 5, 4);

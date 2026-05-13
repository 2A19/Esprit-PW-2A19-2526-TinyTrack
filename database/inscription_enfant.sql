CREATE DATABASE IF NOT EXISTS tinytrack;
USE tinytrack;

-- Table des classes
CREATE TABLE IF NOT EXISTS classes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(50) NOT NULL,
    couleur VARCHAR(20) NOT NULL UNIQUE,
    description TEXT,
    age_minimum INT,
    age_maximum INT,
    capacite_max INT DEFAULT 25,
    educateur_principal VARCHAR(100),
    salle VARCHAR(50),
    horaires TEXT,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insertion des classes par défaut
INSERT INTO classes (nom, couleur, description, age_minimum, age_maximum, capacite_max, salle) VALUES
('Très Petite Section', 'Jaune', 'Accueil des tout-petits de 2 à 3 ans', 2, 3, 15, 'Salle A'),
('Petite Section', 'Bleu', 'Première année de maternelle', 3, 4, 20, 'Salle B'),
('Moyenne Section', 'Vert', 'Deuxième année de maternelle', 4, 5, 25, 'Salle C'),
('Grande Section', 'Rouge', 'Dernière année de maternelle', 5, 6, 25, 'Salle D');

CREATE TABLE IF NOT EXISTS enfants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enfant_nom VARCHAR(100) NOT NULL,
    enfant_prenom VARCHAR(100) NOT NULL,
    enfant_date_naissance DATE NOT NULL,
    classe_id INT NOT NULL,
    
    -- 🏥 Dossier Médical
    groupe_sanguin VARCHAR(10),
    allergies_alimentaires TEXT,
    allergies_medicales TEXT,
    maladies_chroniques TEXT,
    vaccinations TEXT,
    notes_sante TEXT,
    
    -- 👨‍👩‍👧 Contact d'Urgence
    contact_urgence_nom VARCHAR(100),
    contact_urgence_lien VARCHAR(50),
    contact_urgence_telephone VARCHAR(20),
    
    -- 📋 Préférences & Autorisations
    autorise_photos BOOLEAN DEFAULT true,
    autorise_sorties BOOLEAN DEFAULT true,
    aliments_preferes TEXT,
    aliments_interdits TEXT,
    horaire_sieste VARCHAR(100),
    notes_speciales TEXT,
    
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (classe_id) REFERENCES classes(id) ON DELETE RESTRICT
);

-- Table de tracking GPS
CREATE TABLE IF NOT EXISTS positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enfant_id INT NOT NULL,
    latitude DECIMAL(10, 8) NOT NULL,
    longitude DECIMAL(11, 8) NOT NULL,
    precision_metres FLOAT,
    timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enfant_id) REFERENCES enfants(id) ON DELETE CASCADE
);

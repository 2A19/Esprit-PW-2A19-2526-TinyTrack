-- ================================================
-- TinyTrack — Migration : Integration fixes
-- Colonnes ajoutees pour faire fonctionner l'integration
-- des modules user, rapport, evenement.
-- A executer dans phpMyAdmin sur la base tinytrack.
-- ================================================

USE tinytrack;

-- Colonne mdp_temp : stocke le mot de passe en clair pendant la phase
-- "en_attente" pour que l'admin puisse l'envoyer par email a l'approbation.
ALTER TABLE user
    ADD COLUMN IF NOT EXISTS mdp_temp VARCHAR(255) DEFAULT NULL AFTER mot_de_passe;

-- Colonne id_enfant dans rapport : permet de lier un rapport a un enfant
-- specifique (utilise par le module Gestion rapport de Mohamed).
ALTER TABLE rapport
    ADD COLUMN IF NOT EXISTS id_enfant INT(11) DEFAULT NULL AFTER id_activite,
    ADD INDEX idx_rapport_enfant (id_enfant);

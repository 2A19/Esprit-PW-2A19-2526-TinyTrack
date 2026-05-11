-- ================================================
-- TinyTrack — Migration : Système d'avis + statut 'complet'
-- ================================================

USE tinytrack;

-- 1. Ajouter colonne avis (JSON list of {parent_id, note, commentaire, date})
ALTER TABLE evenement
    ADD COLUMN IF NOT EXISTS avis JSON DEFAULT NULL;

-- 2. Étendre l'enum statut pour inclure 'complet'
ALTER TABLE evenement
    MODIFY COLUMN statut ENUM('planifie','en_cours','termine','annule','complet') NOT NULL DEFAULT 'planifie';

-- ================================================
-- TinyTrack — Migration : Reconnaissance faciale
-- Ajoute le stockage du descripteur facial (128 floats en JSON).
-- A executer dans phpMyAdmin sur la base tinytrack.
-- ================================================

USE tinytrack;

-- On stocke un descripteur face-api.js (Float32Array de 128) en JSON.
-- Le descripteur est IRREVERSIBLE : impossible de reconstruire le visage a partir.
ALTER TABLE user
    ADD COLUMN face_descriptor TEXT DEFAULT NULL,
    ADD COLUMN face_enrolled_at DATETIME DEFAULT NULL;

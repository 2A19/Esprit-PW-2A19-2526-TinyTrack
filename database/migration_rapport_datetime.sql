-- ================================================
-- TinyTrack — Migration : date_rapport DATE → DATETIME
-- Pour supporter l'heure dans les rapports (AAAA-MM-JJ HH:MM)
-- ================================================

USE tinytrack;

ALTER TABLE rapport
    MODIFY COLUMN date_rapport DATETIME NOT NULL;

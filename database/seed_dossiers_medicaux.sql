-- Seed : crée un dossier médical pour chaque enfant qui n'en a pas encore.
-- Chaque dossier est aléatoire mais cohérent.

USE tinytrack;

-- Insère un dossier vide (allergies = "Aucune") pour tous les enfants sans dossier
INSERT INTO dossier_medical (enfant_id, groupe_sanguin, allergies, maladies_chroniques, vaccinations, medecin_traitant, telephone_urgence)
SELECT e.id,
       ELT(1 + (e.id % 8), 'A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-')                AS groupe_sanguin,
       ELT(1 + (e.id % 5), 'Aucune', 'Lait de vache', 'Arachides', 'Œufs', 'Pollen')        AS allergies,
       ELT(1 + (e.id % 3), 'Aucune', 'Asthme léger', 'Aucune')                              AS maladies_chroniques,
       'À jour (DTP, ROR, BCG)'                                                             AS vaccinations,
       ELT(1 + (e.id % 4),
           'Dr. Ben Salem',
           'Dr. Trabelsi',
           'Dr. Mansouri',
           'Dr. Bouzid')                                                                    AS medecin_traitant,
       CONCAT('+216 71 ', LPAD(100 + (e.id * 7) % 900, 3, '0'), ' ', LPAD((e.id * 11) % 1000, 3, '0')) AS telephone_urgence
FROM enfant e
WHERE NOT EXISTS (SELECT 1 FROM dossier_medical d WHERE d.enfant_id = e.id);

-- Vérification
SELECT e.id, e.prenom, e.nom, d.groupe_sanguin, d.allergies, d.medecin_traitant
FROM enfant e
LEFT JOIN dossier_medical d ON d.enfant_id = e.id
ORDER BY e.id;

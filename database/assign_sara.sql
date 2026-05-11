-- Assigne sara ajili (TT-3009) au groupe "Les Papillons" (id=1, niveau petit).
-- L'éducatrice du groupe est automatiquement liée via groupe.educateur_id.

USE tinytrack;

UPDATE enfant
SET groupe_id = 1
WHERE code_unique = 'TT-3009';

-- Vérification
SELECT e.id, e.code_unique, e.prenom, e.nom,
       g.nom AS groupe_nom, g.niveau,
       u.prenom AS educateur_prenom, u.nom AS educateur_nom
FROM enfant e
LEFT JOIN groupe g ON g.id = e.groupe_id
LEFT JOIN user u ON u.id = g.educateur_id
WHERE e.code_unique = 'TT-3009';

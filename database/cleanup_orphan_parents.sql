-- Cleanup : supprimer les parents qui n'ont aucun enfant dans la table `enfant`.
-- Règle métier : un parent ne peut exister sans au moins un enfant.

USE tinytrack;

-- 1. Aperçu : liste les parents orphelins (à exécuter d'abord pour vérifier)
SELECT u.id, u.code_unique, u.nom, u.prenom, u.email, u.statut
FROM user u
WHERE u.role = 'parent'
  AND NOT EXISTS (SELECT 1 FROM enfant e WHERE e.parent_id = u.id);

-- 2. Suppression effective
DELETE u FROM user u
WHERE u.role = 'parent'
  AND NOT EXISTS (SELECT 1 FROM enfant e WHERE e.parent_id = u.id);

-- ================================================
-- TinyTrack — Reset de tous les face IDs enregistrés
-- ================================================

USE tinytrack;

UPDATE user
   SET face_descriptor = NULL,
       face_enrolled_at = NULL
 WHERE face_descriptor IS NOT NULL
    OR face_enrolled_at IS NOT NULL;

-- Vérification
SELECT COUNT(*) AS users_avec_face_apres_reset
  FROM user
 WHERE face_descriptor IS NOT NULL;

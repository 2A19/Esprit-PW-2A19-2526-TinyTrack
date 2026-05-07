-- Seed : ajoute 5 parents avec chacun 2 ou 3 enfants.
-- Mot de passe par défaut pour tous les parents : password123
--
-- Codes générés à partir des plus hauts codes existants pour éviter les collisions.
-- À lancer dans phpMyAdmin sur la base `tinytrack`.

USE tinytrack;

SET @hash := '$2y$10$yBx9O6gjv8zit1lNGZMjhenHrA4dboJis0OV6KgsU/mc89vAD28Ce';

-- ============================================================
-- 1. Insertion des PARENTS (codes TT-2XXX auto-incrémentés)
-- ============================================================

-- Parent 1 : Rania Mejri (2 enfants)
INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, role, telephone, statut)
VALUES (CONCAT('TT-2', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM user WHERE code_unique LIKE 'TT-2%') t), 3, '0')),
        'Mejri', 'Rania', 'rania.mejri@gmail.com', @hash, 'parent', '+216 55 901 901', 'actif');
SET @p1 := LAST_INSERT_ID();

-- Parent 2 : Khaled Souissi (3 enfants)
INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, role, telephone, statut)
VALUES (CONCAT('TT-2', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM user WHERE code_unique LIKE 'TT-2%') t), 3, '0')),
        'Souissi', 'Khaled', 'khaled.souissi@gmail.com', @hash, 'parent', '+216 55 902 902', 'actif');
SET @p2 := LAST_INSERT_ID();

-- Parent 3 : Amel Gharbi (2 enfants)
INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, role, telephone, statut)
VALUES (CONCAT('TT-2', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM user WHERE code_unique LIKE 'TT-2%') t), 3, '0')),
        'Gharbi', 'Amel', 'amel.gharbi@gmail.com', @hash, 'parent', '+216 55 903 903', 'actif');
SET @p3 := LAST_INSERT_ID();

-- Parent 4 : Mohamed Triki (3 enfants)
INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, role, telephone, statut)
VALUES (CONCAT('TT-2', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM user WHERE code_unique LIKE 'TT-2%') t), 3, '0')),
        'Triki', 'Mohamed', 'mohamed.triki@gmail.com', @hash, 'parent', '+216 55 904 904', 'actif');
SET @p4 := LAST_INSERT_ID();

-- Parent 5 : Leila Bouhajra (2 enfants)
INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, role, telephone, statut)
VALUES (CONCAT('TT-2', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM user WHERE code_unique LIKE 'TT-2%') t), 3, '0')),
        'Bouhajra', 'Leila', 'leila.bouhajra@gmail.com', @hash, 'parent', '+216 55 905 905', 'actif');
SET @p5 := LAST_INSERT_ID();

-- ============================================================
-- 2. Insertion des ENFANTS (codes TT-3XXX auto-incrémentés)
-- groupe_id : 1=Papillons (petit), 2=Etoiles (moyen), 3=Champions (grand)
-- ============================================================

-- Enfants de Rania Mejri (2)
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Mejri', 'Eya', '2023-04-12', 'F', 1, @p1, '2026-02-01', 'actif');
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Mejri', 'Karim', '2021-09-25', 'M', 3, @p1, '2025-09-01', 'actif');

-- Enfants de Khaled Souissi (3)
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Souissi', 'Aziz', '2022-09-08', 'M', 2, @p2, '2025-09-15', 'actif');
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Souissi', 'Maya', '2024-01-20', 'F', 1, @p2, '2026-03-15', 'actif');
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Souissi', 'Hamza', '2021-06-14', 'M', 3, @p2, '2025-09-01', 'actif');

-- Enfants de Amel Gharbi (2)
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Gharbi', 'Youssef', '2021-11-03', 'M', 3, @p3, '2025-09-01', 'actif');
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Gharbi', 'Sarra', '2023-02-18', 'F', 1, @p3, '2026-01-10', 'actif');

-- Enfants de Mohamed Triki (3)
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Triki', 'Selim', '2022-05-17', 'M', 2, @p4, '2025-09-01', 'actif');
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Triki', 'Lina', '2023-08-29', 'F', 1, @p4, '2026-01-15', 'actif');
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Triki', 'Adam', '2021-11-30', 'M', 3, @p4, '2025-09-01', 'actif');

-- Enfants de Leila Bouhajra (2)
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Bouhajra', 'Yasmine', '2022-12-11', 'F', 2, @p5, '2025-10-01', 'actif');
INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, groupe_id, parent_id, date_inscription, statut)
VALUES (CONCAT('TT-3', LPAD((SELECT COALESCE(MAX(CAST(SUBSTRING(t.code_unique, 5) AS UNSIGNED)),0)+1 FROM (SELECT code_unique FROM enfant) t), 3, '0')),
        'Bouhajra', 'Omar', '2023-05-22', 'M', 1, @p5, '2026-02-01', 'actif');

-- ============================================================
-- 3. Vérification
-- ============================================================
SELECT u.id, u.code_unique, u.nom, u.prenom,
       (SELECT COUNT(*) FROM enfant e WHERE e.parent_id = u.id) AS nb_enfants,
       (SELECT GROUP_CONCAT(e.prenom SEPARATOR ', ') FROM enfant e WHERE e.parent_id = u.id) AS enfants
FROM user u
WHERE u.id IN (@p1, @p2, @p3, @p4, @p5);

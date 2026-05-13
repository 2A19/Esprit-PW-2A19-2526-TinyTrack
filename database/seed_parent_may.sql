-- ================================================
-- TinyTrack — Demo data pour le compte parent May OUESLATI (id=18)
-- enfant : Aylan Aouini (id=21)
-- educateur affecte : Fatma Chtioui (id=2, groupe id=1 "Les Papillons")
-- ================================================

USE tinytrack;

-- 1. Assigner Aylan au groupe "Les Papillons" gere par Fatma
UPDATE enfant SET groupe_id = 1 WHERE id = 21;

-- 2. Reservations evenement pour May (parent_id=18, enfant_id=21)
INSERT INTO reservation (evenement_id, enfant_id, parent_id, nb_accompagnants, commentaire, date_reservation, statut, paiement) VALUES
(1, 21, 18, 1, 'Avec moi et le papa, on a hâte !',       '2026-04-22 18:30:00', 'confirmee', 'paye'),
(3, 21, 18, 0, 'Juste Aylan',                            '2026-04-29 09:15:00', 'confirmee', 'non_paye'),
(2, 21, 18, 1, 'Avec sa grande sœur si possible',        '2026-04-19 14:00:00', 'en_attente', 'non_paye');

-- 3. Rapports journaliers pour Aylan (rapport sur ses activites quotidiennes)
INSERT INTO rapport (contenu_rapport, date_rapport, id_activite, id_enfant, id_educateur) VALUES
('Aylan a tres bien dormi pendant la sieste et a mange tout son gouter. Il a participe joyeusement aux jeux de construction avec ses camarades.',
 '2026-05-12 16:30:00', 5, 21, 2),
('Aylan a peint un magnifique dessin aujourdhui. Il commence a tenir le pinceau avec plus de precision. Il a montre fierement son œuvre a tous les enfants.',
 '2026-05-09 17:00:00', 1, 21, 2),
('Belle journee pour Aylan : il a chante "Frere Jacques" pendant l eveil musical et a danse au rythme du tambourin. Tres souriant aujourdhui.',
 '2026-05-07 16:45:00', 4, 21, 2),
('Aylan a passe une bonne journee. Il a joue dehors avec ses copains et a particulierement aime la course aux ballons.',
 '2026-05-05 17:15:00', 2, 21, 2);

-- 4. Conversation May <-> Fatma + messages
INSERT INTO conversation (parent_id, staff_id, status, created_at) VALUES (18, 2, 'open', '2026-05-08 09:00:00');
SET @conv_id := LAST_INSERT_ID();

INSERT INTO chat_message (conversation_id, sender_id, sender_role, body, created_at) VALUES
(@conv_id, 18, 'parent',    'Bonjour Fatma, comment se passe l adaptation d Aylan dans le groupe Les Papillons ?', '2026-05-08 09:00:00'),
(@conv_id, 2,  'educateur', 'Bonjour Madame OUESLATI ! Aylan s adapte tres bien, il est tres souriant et joue deja avec les autres. Aucun souci a signaler.', '2026-05-08 10:15:00'),
(@conv_id, 18, 'parent',    'Super, ça me rassure beaucoup ! Est-ce qu il y a quelque chose que je dois preparer pour la sortie de demain ?', '2026-05-08 10:30:00'),
(@conv_id, 2,  'educateur', 'Juste une gourde d eau et un petit chapeau. On lui fournit le reste. A demain !', '2026-05-08 11:00:00');

-- 5. Reclamations soumises par May
INSERT INTO reclamations (nom_client, email, sujet, description, statut, sentiment, date_creation) VALUES
('May OUESLATI', 'May.OUESLATI@esprit.tn', 'Question sur le menu',
 'Bonjour, est-il possible d avoir une copie du menu de la semaine ? Aylan a quelques restrictions alimentaires (pas de lactose).',
 'Traité', 'neutre', '2026-05-06 08:30:00'),
('May OUESLATI', 'May.OUESLATI@esprit.tn', 'Merci pour la fete de printemps',
 'Je voulais vous remercier pour l organisation impeccable de la fete de printemps. Aylan en parle encore !',
 'Traité', 'positif', '2026-04-26 19:45:00');

-- 6. Reponse admin a la reclamation menu
INSERT INTO reponses (id_reclamation, message, auteur, date_reponse)
SELECT id, 'Bonjour Madame OUESLATI, le menu detaille de la semaine est affiche dans l entree et envoye chaque dimanche par email. Pour les restrictions, nous adaptons sans probleme. Bonne journee !', 'Admin', '2026-05-06 11:00:00'
FROM reclamations WHERE nom_client='May OUESLATI' AND sujet='Question sur le menu';

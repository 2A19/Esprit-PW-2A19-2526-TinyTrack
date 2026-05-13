-- ================================================
-- TinyTrack — Donnees de demo pour le module Communication
-- ================================================

USE tinytrack;

-- 6 conversations (parent <-> staff)
INSERT INTO `conversation` (`parent_id`, `staff_id`, `status`, `created_at`, `needs_admin_attention`) VALUES
(5, 2, 'open',     '2026-05-10 09:15:00', 0),  -- Sana <-> Fatma
(6, 2, 'open',     '2026-05-11 14:30:00', 1),  -- Ahmed <-> Fatma (alerte admin)
(7, 3, 'open',     '2026-05-12 08:45:00', 0),  -- Leila <-> Rim
(8, 3, 'archived', '2026-05-08 16:20:00', 0),  -- Karim <-> Rim (archivee)
(9, 4, 'open',     '2026-05-12 11:00:00', 0),  -- Mona <-> Houda
(10, 2, 'open',    '2026-05-13 07:50:00', 0);  -- Anis <-> Fatma

-- Messages dans ces conversations
INSERT INTO `chat_message` (`conversation_id`, `sender_id`, `sender_role`, `body`, `created_at`, `needs_admin_attention`) VALUES
-- Conv 1 : Sana <-> Fatma (3 messages)
(1, 5, 'parent',    'Bonjour, comment s''est passee la journee de mon fils Yassine ?', '2026-05-10 09:15:00', 0),
(1, 2, 'educateur', 'Bonjour Sana, Yassine a passe une excellente journee ! Il a beaucoup joue aux Lego.', '2026-05-10 09:32:00', 0),
(1, 5, 'parent',    'Super, merci pour l''info ! Il vous a parle de la sortie de vendredi ?', '2026-05-10 09:35:00', 0),

-- Conv 2 : Ahmed <-> Fatma (besoin attention admin - urgent)
(2, 6, 'parent',    'Probleme urgent : ma fille est tombee a la creche, elle a un bleu. Que s''est-il passe ?', '2026-05-11 14:30:00', 1),
(2, 2, 'educateur', 'Bonjour Ahmed, je suis vraiment desolee. Elle a glisse dans la cour pendant la recreation. Le bleu est superficiel, j''ai mis de l''arnica.', '2026-05-11 14:45:00', 0),
(2, 6, 'parent',    'J''aimerais en parler avec l''admin svp.', '2026-05-11 15:10:00', 1),

-- Conv 3 : Leila <-> Rim
(3, 7, 'parent',    'Est-ce que je peux apporter le gouter d''anniversaire de Yasmine demain ?', '2026-05-12 08:45:00', 0),
(3, 3, 'educateur', 'Bien sur ! Vers 15h c''est parfait. Combien d''enfants au menu ?', '2026-05-12 09:10:00', 0),
(3, 7, 'parent',    '15 enfants. Merci beaucoup Rim !', '2026-05-12 09:15:00', 0),

-- Conv 4 : Karim <-> Rim (archivee)
(4, 8, 'parent',    'Bonjour, ma fille a oublie son doudou. Pouvez-vous verifier ?', '2026-05-08 16:20:00', 0),
(4, 3, 'educateur', 'Trouve ! Il etait dans le coin lecture. Je le mets dans son sac.', '2026-05-08 16:35:00', 0),
(4, 8, 'parent',    'Parfait, merci beaucoup !', '2026-05-08 16:40:00', 0),

-- Conv 5 : Mona <-> Houda
(5, 9, 'parent',    'Bonjour Houda, mon fils a une legere fievre ce matin, je le garde a la maison aujourd''hui.', '2026-05-12 11:00:00', 0),
(5, 4, 'educateur', 'Compris, je note. Bon retablissement a Adam !', '2026-05-12 11:05:00', 0),

-- Conv 6 : Anis <-> Fatma (toute fraiche)
(6, 10, 'parent', 'Bonjour, j''aimerais discuter du progres de Tarek en lecture.', '2026-05-13 07:50:00', 0);

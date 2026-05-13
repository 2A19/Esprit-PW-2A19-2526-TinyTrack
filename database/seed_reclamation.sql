-- ================================================
-- TinyTrack — Donnees de demo pour le module Reclamation
-- ================================================

USE tinytrack;

-- 10 reclamations avec sentiments et statuts varies
INSERT INTO `reclamations` (`nom_client`, `email`, `sujet`, `description`, `statut`, `sentiment`, `date_creation`) VALUES
('Sana Ben Ali',       'sana.benali@gmail.com',     'Retard de remise de l''enfant',         'Bonjour, hier ma fille Aya a ete remise avec 30 minutes de retard, ce qui m''a fait manquer mon rendez-vous medical. Pouvez-vous me dire pourquoi ?', 'En attente', 'negatif', '2026-05-08 09:15:00'),
('Ahmed Trabelsi',     'ahmed.trabelsi@gmail.com',  'Repas non adapte',                       'Mon fils est allergique aux arachides et le menu de mardi contenait des cacahuetes. C''est tres dangereux.', 'En attente', 'negatif', '2026-05-09 14:22:00'),
('Leila Mansouri',     'leila.mansouri@gmail.com',  'Excellent suivi',                        'Je voulais vraiment feliciter l''equipe pour le suivi de ma fille Yasmine. Les rapports journaliers sont detailles et rassurants. Bravo !', 'Traité', 'positif', '2026-05-05 18:30:00'),
('Karim Bouazizi',     'karim.bouazizi@gmail.com',  'Demande de changement de groupe',         'Mon fils Nour aimerait changer de groupe pour etre avec son cousin. Est-ce possible ?', 'Traité', 'neutre', '2026-05-03 10:45:00'),
('Mona Chaabane',      'mona.chaabane@gmail.com',   'Probleme de facturation',                'J''ai recu une facture de 450 dinars mais le tarif convenu etait 400. Pouvez-vous verifier ?', 'En attente', 'negatif', '2026-05-10 11:00:00'),
('Anis Meddeb',        'anis.meddeb@gmail.com',     'Activites du week-end',                  'Y aurait-il moyen d''organiser des activites extra-scolaires le week-end ? Beaucoup de parents seraient interesses.', 'En attente', 'positif', '2026-05-11 16:20:00'),
('Samira Hammami',     'samira.hammami@gmail.com',  'Mauvaise hygiene constatee',             'J''ai remarque que les toilettes etaient sales hier matin. Ce n''est pas acceptable pour une creche.', 'Traité', 'negatif', '2026-05-04 08:15:00'),
('Nabil Jebali',       'nabil.jebali@gmail.com',    'Merci pour l''aide d''hier',             'Merci infiniment a Houda pour avoir gere la crise de mon fils hier. Elle a ete vraiment professionnelle.', 'Traité', 'positif', '2026-05-07 19:00:00'),
('Sana Ben Ali',       'sana.benali@gmail.com',     'Photos sur le mur',                      'Pouvez-vous me dire ou je peux voir les photos prises lors de la fete d''anniversaire ?', 'En attente', 'neutre', '2026-05-12 09:50:00'),
('Ahmed Trabelsi',     'ahmed.trabelsi@gmail.com',  'Probleme de securite a la sortie',       'La porte principale est restee ouverte sans surveillance hier soir. C''est un risque enorme pour les enfants !', 'En attente', 'negatif', '2026-05-12 17:30:00');

-- Reponses de l'admin sur les reclamations traitees
INSERT INTO `reponses` (`id_reclamation`, `message`, `auteur`, `date_reponse`) VALUES
(3, 'Merci beaucoup pour vos retours positifs Madame Mansouri ! Je transmets a toute l''equipe.', 'Admin', '2026-05-06 09:00:00'),
(4, 'Bonjour M. Bouazizi, nous avons etudie votre demande. Le changement est possible a partir de la semaine prochaine. Nour rejoindra le groupe de son cousin.', 'Admin', '2026-05-04 14:30:00'),
(7, 'Madame Hammami, nous sommes desoles de cet incident. Le nettoyage a ete renforce et un controle quotidien est mis en place. Merci de nous avoir signale.', 'Admin', '2026-05-05 11:00:00'),
(8, 'Merci pour votre message M. Jebali, c''est tres apprecie. Nous transmettons a Houda.', 'Admin', '2026-05-08 10:15:00');

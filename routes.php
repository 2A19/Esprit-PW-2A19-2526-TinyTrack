<?php

$router = new Router();

// ===== Auth =====
$router->get('/',                    'AuthController', 'showLogin');
$router->get('/login',               'AuthController', 'showLogin');
$router->post('/login',              'AuthController', 'doLogin');
$router->any('/logout',              'AuthController', 'logoutAction');

$router->get('/register',            'AuthController', 'showRegister');
$router->post('/register',           'AuthController', 'doRegister');

$router->get('/forgot-password',     'AuthController', 'showForgotPassword');
$router->post('/forgot-password',    'AuthController', 'doForgotPassword');

$router->get('/reset-password',      'AuthController', 'showResetPassword');
$router->post('/reset-password',     'AuthController', 'doResetPassword');

$router->post('/face-login',         'AuthController', 'faceLoginAction');
$router->post('/face-enroll',        'AuthController', 'faceEnrollAction');

// ===== Dashboards =====
$router->get('/dashboard',           'DashboardController', 'admin');
$router->get('/dashboard/admin',     'DashboardController', 'admin');
$router->get('/dashboard/educateur', 'DashboardController', 'educateur');

// ===== Enfants (BackOffice admin) =====
$router->get('/enfants',                'EnfantController', 'index');
$router->any('/enfants/add',            'EnfantController', 'add');
$router->any('/enfants/edit/{id}',      'EnfantController', 'edit');
$router->post('/enfants/delete/{id}',   'EnfantController', 'delete');
$router->post('/enfants/archive/{id}',  'EnfantController', 'archive');
$router->post('/enfants/activate/{id}', 'EnfantController', 'activate');

// FrontOffice (parent / educateur / admin) — same controller, different action
$router->get('/mes-enfants',         'EnfantController', 'mesEnfants');

// ===== Activités =====
$router->get('/activites',                'ActiviteController', 'index');
$router->get('/activites/statistiques',   'ActiviteController', 'statistiques');
$router->get('/activites/expertise',      'ActiviteController', 'expertise');
$router->any('/activites/add',            'ActiviteController', 'add');
$router->any('/activites/edit/{id}',      'ActiviteController', 'edit');
$router->post('/activites/delete/{id}',   'ActiviteController', 'delete');

// ===== Événements (BackOffice) =====
$router->get('/evenements',               'EvenementController', 'index');
$router->get('/evenements/dashboard',     'EvenementController', 'dashboard');
$router->get('/evenements/geocode',       'EvenementController', 'geocode');
$router->post('/evenements/avis/submit',  'EvenementController', 'submitAvis');
$router->any('/evenements/add',           'EvenementController', 'add');
$router->any('/evenements/edit/{id}',     'EvenementController', 'edit');
$router->post('/evenements/delete/{id}',  'EvenementController', 'delete');
$router->any('/evenements/reserver/{id}', 'ReservationController', 'reserver');
$router->get('/evenements/{id}/reservations', 'EvenementController', 'reservations');

// FrontOffice
$router->get('/evenements/parent',        'EvenementController', 'frontofficeParent');
$router->get('/mes-reservations',         'EvenementController', 'frontofficeReservationsParent');

// ===== Réservations =====
$router->get('/reservations',                'ReservationController', 'index');
$router->any('/reservations/add',            'ReservationController', 'add');
$router->any('/reservations/edit/{id}',      'ReservationController', 'edit');
$router->post('/reservations/delete/{id}',   'ReservationController', 'delete');

// ===== Rapports =====
$router->get('/rapports',                'RapportController', 'index');
$router->any('/rapports/edit/{id}',      'RapportController', 'edit');
$router->post('/rapports/delete/{id}',   'RapportController', 'delete');
$router->any('/rapports/add',            'RapportController', 'add');
$router->get('/rapports/parent',         'RapportController', 'parent');
$router->get('/rapports/pdf/{id}',       'RapportController', 'exportPdf');
$router->get('/rapports/educateur/{id}', 'RapportController', 'activitesEducateur');

// ===== Messages =====
$router->get('/messages',                'MessageController', 'index');

// ===== Profil =====
$router->any('/profil',                  'ProfilController', 'index');

// ===== Educateurs / Parents (admin views) =====
$router->get('/educateurs',                  'EducateurController', 'index');
$router->any('/educateurs/profil',           'EducateurController', 'monProfil');
$router->post('/educateurs/archive/{id}',    'EducateurController', 'archive');
$router->post('/educateurs/activate/{id}',   'EducateurController', 'activate');
$router->get('/parents',                     'EducateurController', 'parents');

// ===== Approbation =====
$router->get('/approbation',               'ApprobationController', 'index');
$router->post('/approbation/approuver/{id}', 'ApprobationController', 'approuverAction');
$router->post('/approbation/rejeter/{id}',   'ApprobationController', 'rejeterAction');

return $router;

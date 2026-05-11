# TinyTrack – Plateforme de Digitalisation d'un Jardin d'Enfants

> *"Chaque petit pas compte."*

![Esprit School of Engineering](https://img.shields.io/badge/Esprit%20School%20of%20Engineering-Tunisia-green)
![Academic Year](https://img.shields.io/badge/Annee%20Universitaire-2025--2026-blue)
![PHP](https://img.shields.io/badge/Backend-PHP-777BB4)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1)
![JavaScript](https://img.shields.io/badge/Frontend-JavaScript-F7DF1E)

---

## Table des Matieres

- [Apercu](#apercu)
- [Problematique](#problematique)
- [Fonctionnalites](#fonctionnalites)
- [Stack Technique](#stack-technique)
- [Architecture](#architecture)
- [Installation](#installation)
- [Equipe](#equipe)
- [Contexte Academique](#contexte-academique)

---

## Apercu

**TinyTrack** est une application web full-stack dediee a la digitalisation complete d'un jardin d'enfants / creche en Tunisie.

Elle centralise l'inscription des enfants, l'organisation des evenements, les rapports journaliers, la communication entre l'etablissement et les parents, ainsi que la gestion des reclamations — offrant transparence, tracabilite et securite a toutes les parties prenantes.

Projet developpe a **Esprit School of Engineering** – Tunisie, dans le cadre du module **Projet Technologies Web (PW)**, Classe 2A19, Annee Universitaire 2025–2026.

---

## Problematique

En Tunisie, la majorite des jardins d'enfants fonctionnent encore de maniere entierement manuelle :

- **2 800+** jardins d'enfants en Tunisie
- **73%** gerent encore avec du papier
- **82%** des parents veulent un suivi digital

Les defis concrets :

- Les parents **ne savent pas** ce que fait leur enfant pendant la journee
- Les **inscriptions** se font sur papier, avec un risque eleve d'erreur
- La **communication** creche ↔ parents est archaique (appels, cahiers de liaison)
- Les **evenements** (fetes, sorties) sont mal coordonnes : pas de reservation centralisee
- Les **dossiers medicaux** sont sur papier : risque d'erreur en cas d'urgence
- Les **reclamations** des familles ne sont pas tracees ni suivies efficacement
- L'**acces** aux comptes utilisateurs n'est pas securise (mots de passe partages, pas de verification)

Suite aux scandales recents dans des etablissements tunisiens, la question de la **securite et de la transparence** est devenue une priorite absolue pour les familles.

**TinyTrack** repond a cette problematique en digitalisant entierement la gestion de la creche et en offrant aux parents une **visibilite en temps reel** sur la vie de leur enfant.

---

## Fonctionnalites

| Module | Description |
|--------|-------------|
| Gestion user | Comptes admin/educateur/parent, approbation, profil, mot de passe oublie (token email), connexion Google OAuth, reconnaissance faciale |
| Inscription enfant | Inscription, fiche enfant, dossier medical, archivage |
| Gestion evenements | Creation d'evenements, reservations parents, calendrier, gestion BackOffice |
| Gestion rapport | Rapports journaliers, activites (jeux, repas, sieste, humeur), suivi par educateur |
| Communication parents | Messagerie interne, notifications, annonces, alertes urgence |
| Gestion reclamation | Soumission de reclamations, suivi des reponses, statistiques |

### Fonctionnalites transverses (module Gestion user)

- **Authentification multi-facteur** : mot de passe classique, Google Sign-In (OIDC + JWT), reconnaissance faciale (face-api.js + descripteur 128-D)
- **Mot de passe oublie** : token cryptographique SHA-256, expiration 30 min, usage unique, envoi par Gmail SMTP
- **Approbation admin** : les comptes educateur/parent restent en attente jusqu'a validation
- **Recherche / Tri / Statistiques** : sur enfants, educateurs et parents avec filtres dynamiques et graphiques Chart.js
- **Design "kids-store"** : interface pastel responsive, animations SVG, formes ludiques

---

## Stack Technique

### Frontend
- HTML5 / CSS3 (theme kids-store custom)
- JavaScript Vanilla
- Bootstrap 5 (layout)
- Chart.js (graphiques de statistiques)
- face-api.js (reconnaissance faciale, vladmandic fork)
- Google Identity Services (Sign in with Google)

### Backend
- PHP (POO, architecture MVC)
- PDO (acces base de donnees, requetes preparees)
- Gmail SMTP (envoi de mails transactionnels)

### Base de Donnees
- MySQL / MariaDB (utf8mb4)
- Migrations versionnees dans `database/`

### Outils
- XAMPP (Apache + MySQL + PHP)
- phpMyAdmin
- Git & GitHub

---

## Architecture

```
TinyTrack/
├── index.php                          # Point d'entree (redirige vers login)
├── assets/
│   ├── css/playful.css                # Theme kids-store
│   ├── js/face-auth.js                # Module face-api.js
│   └── images/                        # Logo, mascotte
├── config/
│   ├── db.php                         # Connexion PDO MySQL
│   ├── mailer.php                     # Envoi SMTP Gmail
│   ├── google_oauth.php               # Verification JWT Google
│   ├── secrets.example.php            # Template des secrets (commite)
│   └── secrets.php                    # Secrets reels (gitignored)
├── Controller/                        # Logique metier (MVC)
│   ├── AuthController.php             # Login / register / reset / Google / face
│   ├── ApprobationController.php      # Validation des comptes par admin
│   ├── EnfantController.php           # CRUD + recherche/tri/stats enfants
│   ├── EducateurController.php        # CRUD + recherche/tri/stats educateurs
│   ├── EvenementController.php        # CRUD evenements
│   ├── ReservationController.php      # Reservations parents
│   ├── RapportController.php          # Rapports journaliers
│   ├── ActiviteController.php         # Activites quotidiennes
│   ├── MessageController.php          # Messagerie parents/educateurs
│   ├── ProfilController.php           # Profil utilisateur
│   └── DashboardController.php        # Statistiques globales
├── Model/                             # Acces aux donnees (PDO)
├── View/
│   ├── auth/                          # Login, register, forgot/reset password, face/google
│   ├── FrontOffice/                   # Portail parents/educateurs
│   │   ├── dashboard.php
│   │   ├── enfants/                   # Liste + recherche/tri/stats
│   │   ├── educateurs/
│   │   ├── parents/
│   │   ├── evenements/
│   │   ├── rapports/
│   │   ├── messages.php
│   │   ├── profil.php
│   │   ├── approbation.php
│   │   └── template/                  # Header / footer partages
│   └── BackOffice/                    # Administration
└── database/
    ├── tinytrack.sql                          # Schema initial
    ├── evenement.sql
    ├── rapport.sql
    ├── migration_password_reset.sql           # Table tokens reset mdp
    └── migration_face_recognition.sql         # Colonnes descripteur facial
```

---

## Installation

### Prerequis

- XAMPP (Apache + MySQL + PHP 8.0+)
- Navigateur web moderne avec webcam (pour la reconnaissance faciale)
- Connexion internet (CDN Bootstrap / Chart.js / face-api.js)

### Etapes

1. **Clonez le repository :**

```bash
git clone https://github.com/2A19/Esprit-PW-2A19-2526-TinyTrack.git
cd Esprit-PW-2A19-2526-TinyTrack
```

2. **Placez le projet dans XAMPP :**

   - Copiez le contenu dans `C:\xampp\htdocs\TinyTrack\` (Windows) ou `/opt/lampp/htdocs/TinyTrack/` (Linux)
   - Demarrez **Apache** et **MySQL** depuis le panneau XAMPP

3. **Creez la base de donnees :**

   - Ouvrez phpMyAdmin via `http://localhost/phpmyadmin`
   - Creez une base de donnees nommee `tinytrack` (utf8mb4)
   - Importez dans l'ordre :
     1. `database/tinytrack.sql` (schema initial)
     2. `database/evenement.sql`
     3. `database/rapport.sql`
     4. `database/migration_password_reset.sql`
     5. `database/migration_face_recognition.sql`

4. **Configurez les secrets :**

   - Copiez `config/secrets.example.php` vers `config/secrets.php`
   - Remplissez vos propres valeurs :
     - `SMTP_USER` / `SMTP_PASS` : compte Gmail + App Password ([myaccount.google.com/apppasswords](https://myaccount.google.com/apppasswords))
     - `GOOGLE_CLIENT_ID` : Client ID OAuth ([console.cloud.google.com](https://console.cloud.google.com) → Credentials → Web application)
     - `APP_BASE_URL` : URL de base de l'app (defaut `http://localhost/TinyTrack`)

5. **Verifiez la connexion BDD :**

   Ouvrez `config/db.php` et ajustez si besoin :

```php
$host = "localhost";
$db   = "tinytrack";
$user = "root";
$pass = "";
```

6. **Accedez a l'application :**

```
http://localhost/TinyTrack
```

> **Note securite** : `config/secrets.php` est dans `.gitignore` — ne commitez jamais vos vrais identifiants sur GitHub.

---

## Equipe

| Membre | Module | Entites (avec jointure) |
|--------|--------|-------------------------|
| Belhaj Mabrouk Eya | Gestion user | User ↔ PasswordReset |
| Ajili Rayen | Gestion evenements | Evenement ↔ Reservation |
| Fadhlaoui Mohamed | Gestion rapport | Rapport ↔ Activite |
| Rajhi Amen Allah | Communication parents | Message ↔ Notification |
| Ben Khalifa Youssef | Inscription enfant | Enfant ↔ DossierMedical |
| Ben Slimene Mahdi | Gestion reclamation | Reclamation ↔ Reponse |

---

## Contexte Academique

Projet developpe a **Esprit School of Engineering** – Tunisie

- **Module :** Projet Technologies Web (PW)
- **Classe :** 2A19
- **Annee Universitaire :** 2025–2026

---

&copy; 2026 TinyTrack – Esprit School of Engineering

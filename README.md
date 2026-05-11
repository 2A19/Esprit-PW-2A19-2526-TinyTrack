# TinyTrack – Plateforme de Digitalisation d'un Jardin d'Enfants

> *"Chaque petit pas compte."*

![Esprit School of Engineering](https://img.shields.io/badge/Esprit%20School%20of%20Engineering-Tunisia-green)
![Academic Year](https://img.shields.io/badge/Annee%20Universitaire-2025--2026-blue)
![PHP](https://img.shields.io/badge/Backend-PHP-777BB4)
![MySQL](https://img.shields.io/badge/Database-MySQL-4479A1)
![JavaScript](https://img.shields.io/badge/Frontend-JavaScript-F7DF1E)

---

## Description

**TinyTrack** est une application web full-stack dediee a la digitalisation complete d'un jardin d'enfants / creche en Tunisie.

### Objectif

Centraliser l'inscription des enfants, l'organisation des evenements, les rapports journaliers, la communication entre l'etablissement et les parents, ainsi que la gestion des reclamations — offrant transparence, tracabilite et securite a toutes les parties prenantes.

### Probleme resolu

En Tunisie, la majorite des jardins d'enfants fonctionnent encore de maniere entierement manuelle :

- **2 800+** jardins d'enfants en Tunisie
- **73%** gerent encore avec du papier
- **82%** des parents veulent un suivi digital

TinyTrack repond a ces defis en remplacant le papier par une plateforme web securisee qui offre aux parents une **visibilite en temps reel** sur la vie de leur enfant.

### Principales fonctionnalites

- **Gestion user** : authentification multi-facteur (mot de passe, Google OAuth, reconnaissance faciale), mot de passe oublie par email, approbation admin, recherche/tri/statistiques
- **Inscription enfant** : fiche enfant, dossier medical, archivage
- **Gestion evenements** : creation d'evenements, reservations parents, calendrier
- **Gestion rapport** : rapports journaliers et activites par educateur
- **Communication parents** : messagerie interne, notifications, alertes
- **Gestion reclamation** : soumission, suivi des reponses, statistiques

---

## Table des Matieres

- [Description](#description)
- [Installation](#installation)
- [Utilisation](#utilisation)
- [Architecture](#architecture)
- [Stack Technique](#stack-technique)
- [Equipe](#equipe)
- [Contribution](#contribution)
- [Licence](#licence)
- [Contexte Academique](#contexte-academique)

---

## Installation

### Prerequis

- **XAMPP** (Apache + MySQL + PHP 8.0+)
- **Navigateur web** moderne avec webcam (pour la reconnaissance faciale)
- **Connexion internet** (CDN Bootstrap / Chart.js / face-api.js)

### Etapes

1. **Clonez le repository :**

```bash
git clone https://github.com/2A19/Esprit-PW-2A19-2526-TinyTrack.git
cd Esprit-PW-2A19-2526-TinyTrack
```

2. **Placez le projet dans XAMPP :**

* Copiez le contenu dans `C:\xampp\htdocs\TinyTrack\` (Windows) ou `/opt/lampp/htdocs/TinyTrack/` (Linux)
* Demarrez **Apache** et **MySQL** depuis le panneau XAMPP

3. **Creez la base de donnees :**

* Ouvrez phpMyAdmin via [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
* Creez une base de donnees nommee `tinytrack` (utf8mb4)
* Importez dans l'ordre :
  1. `database/tinytrack.sql`
  2. `database/evenement.sql`
  3. `database/rapport.sql`
  4. `database/migration_password_reset.sql`
  5. `database/migration_face_recognition.sql`

4. **Configurez les secrets :**

* Copiez `config/secrets.example.php` vers `config/secrets.php`
* Remplissez vos identifiants :
  * `SMTP_USER` / `SMTP_PASS` : compte Gmail + App Password (voir [App Passwords Google](https://myaccount.google.com/apppasswords))
  * `GOOGLE_CLIENT_ID` : Client ID OAuth ([Google Cloud Console](https://console.cloud.google.com) → Credentials → OAuth Client ID)
  * `APP_BASE_URL` : URL de base de l'app (defaut `http://localhost/TinyTrack`)

> **Note securite** : `config/secrets.php` est dans `.gitignore` — ne commitez jamais vos vrais identifiants sur GitHub.

5. **Verifiez la connexion BDD :**

   Ouvrez `config/db.php` et ajustez si necessaire :

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

---

## Utilisation

### Installation de PHP

PHP est inclus dans XAMPP. Si vous voulez l'installer separement :

1. Telechargez PHP depuis [PHP - Telechargement](https://www.php.net/downloads.php).
2. Installez PHP en suivant les instructions specifiques a votre systeme d'exploitation :

   * Pour **Windows**, vous pouvez utiliser [XAMPP](https://www.apachefriends.org/fr/index.html) ou [WampServer](http://www.wampserver.com/).
   * Pour **macOS**, vous pouvez utiliser [Homebrew](https://brew.sh/), puis executer la commande suivante dans le terminal :

     ```bash
     brew install php
     ```
   * Pour **Linux**, installez PHP via le gestionnaire de paquets. Par exemple, sur Ubuntu :

     ```bash
     sudo apt update
     sudo apt install php
     ```

3. Verifiez l'installation de PHP en executant la commande suivante :

   ```bash
   php -v
   ```

### Installation de MySQL

MySQL est inclus dans XAMPP. Pour une installation separee :

* **Windows** : [MySQL Community Server](https://dev.mysql.com/downloads/mysql/) ou utilisez MariaDB via XAMPP
* **macOS** : `brew install mysql`
* **Linux (Ubuntu)** : `sudo apt install mysql-server`

### Premiere connexion

Une fois XAMPP demarre et l'application accessible sur [http://localhost/TinyTrack](http://localhost/TinyTrack) :

1. **Compte admin par defaut** : `admin@tinytrack.tn` (mot de passe defini lors du seed initial)
2. **Inscription parent / educateur** : page `register.php`, le compte reste en attente jusqu'a approbation par l'admin
3. **Connexion alternative** :
   * **Google** : bouton "Continuer avec Google" (necessite un Client ID configure)
   * **Reconnaissance faciale** : depuis le profil, activer puis utiliser le bouton "Se connecter avec mon visage" sur la page de login

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
│   ├── ApprobationController.php
│   ├── EnfantController.php
│   ├── EducateurController.php
│   ├── EvenementController.php
│   ├── ReservationController.php
│   ├── RapportController.php
│   ├── ActiviteController.php
│   ├── MessageController.php
│   ├── ProfilController.php
│   └── DashboardController.php
├── Model/                             # Acces aux donnees (PDO)
├── View/
│   ├── auth/                          # Login, register, forgot/reset password
│   ├── FrontOffice/                   # Portail parents/educateurs
│   └── BackOffice/                    # Administration
└── database/                          # Schemas + migrations SQL
```

---

## Stack Technique

### Frontend

* HTML5 / CSS3 (theme kids-store custom)
* JavaScript Vanilla
* [Bootstrap 5](https://getbootstrap.com/) (layout responsive)
* [Chart.js](https://www.chartjs.org/) (graphiques de statistiques)
* [face-api.js](https://github.com/vladmandic/face-api) (reconnaissance faciale)
* [Google Identity Services](https://developers.google.com/identity/gsi/web) (Sign in with Google)

### Backend

* **PHP** (POO, architecture MVC)
* **PDO** (acces base de donnees, requetes preparees, anti-injection)
* **Gmail SMTP** (envoi de mails transactionnels)

### Base de Donnees

* **MySQL / MariaDB** (utf8mb4)
* Migrations versionnees dans `database/`

### Outils

* [XAMPP](https://www.apachefriends.org/) (Apache + MySQL + PHP)
* [phpMyAdmin](https://www.phpmyadmin.net/)
* [Git](https://git-scm.com/) & [GitHub](https://github.com/)

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

## Contribution

Nous remercions tous ceux qui ont contribue a ce projet !

### Contributeurs

Les personnes suivantes ont contribue a ce projet en developpant les differents modules de la plateforme :

- [Eya Belhaj Mabrouk](https://github.com/eyabelhaj616) — Module Gestion user (authentification multi-facteur, recherche/tri/statistiques, design kids-store)
- Ajili Rayen — Module Gestion evenements
- Fadhlaoui Mohamed — Module Gestion rapport
- Rajhi Amen Allah — Module Communication parents
- Ben Khalifa Youssef — Module Inscription enfant
- Ben Slimene Mahdi — Module Gestion reclamation

Si vous souhaitez contribuer, suivez les etapes ci-dessous pour faire un **fork**, creer une nouvelle branche et soumettre une **pull request**.

### Comment contribuer ?

1. **Fork le projet** : Allez sur la page GitHub du projet et cliquez sur le bouton **Fork** dans le coin superieur droit pour creer une copie du projet dans votre propre compte GitHub.

2. **Clonez votre fork** : Clonez le fork sur votre machine locale :

   ```bash
   git clone https://github.com/votre-utilisateur/Esprit-PW-2A19-2526-TinyTrack.git
   cd Esprit-PW-2A19-2526-TinyTrack
   ```

3. **Creez une nouvelle branche** pour votre fonctionnalite :

   ```bash
   git checkout -b feature/ma-fonctionnalite
   ```

4. **Effectuez vos modifications**, puis commitez :

   ```bash
   git add .
   git commit -m "Ajout de ma fonctionnalite"
   ```

5. **Poussez votre branche** sur votre fork :

   ```bash
   git push origin feature/ma-fonctionnalite
   ```

6. **Ouvrez une Pull Request** depuis votre fork vers `2A19/Esprit-PW-2A19-2526-TinyTrack` branche `main` sur GitHub.

---

## Licence

Ce projet est sous la licence **MIT**. Pour plus de details, consultez le fichier [LICENSE](./LICENSE).

### Details sur la licence MIT

La licence MIT est une licence de logiciel libre permissive qui permet :

* Une utilisation commerciale et privee
* La modification du code source
* La distribution sous toute forme
* L'utilisation pour des projets personnels ou professionnels

Sous reserve que la mention de copyright et la licence MIT soient incluses dans toutes les copies ou portions substantielles du logiciel.

---

## Contexte Academique

Projet developpe a **Esprit School of Engineering** – Tunisie

- **Module :** Projet Technologies Web (PW)
- **Classe :** 2A19
- **Annee Universitaire :** 2025–2026

---

&copy; 2026 TinyTrack – Esprit School of Engineering

<?php
/**
 * TinyTrack — Secrets template
 *
 * COPIEZ ce fichier vers config/secrets.php (qui est dans .gitignore)
 * et remplacez les valeurs par vos propres credentials.
 *
 * NE COMMITEZ JAMAIS config/secrets.php avec de vraies valeurs.
 */

// === Gmail SMTP (utilise pour envoi de mails) ===
// Generez un "App Password" : myaccount.google.com -> Securite -> Mots de passe d'application
if (!defined('SMTP_USER'))     define('SMTP_USER',     'votre.adresse@gmail.com');
if (!defined('SMTP_PASS'))     define('SMTP_PASS',     'XXXXXXXXXXXXXXXX');

// === Google OAuth (Sign in with Google) ===
// Cree dans Google Cloud Console : APIs & Services -> Credentials -> OAuth Client ID
if (!defined('GOOGLE_CLIENT_ID')) define('GOOGLE_CLIENT_ID', 'XXXXXXXXXXXX-xxxxxxxxxxxxxxxx.apps.googleusercontent.com');

// === Application base URL (utilise dans les liens email) ===
if (!defined('APP_BASE_URL')) define('APP_BASE_URL', 'http://localhost/TinyTrack');

// === Groq API (LLM pour analyse IA des rapports) ===
// Obtenez votre cle gratuite sur : https://console.groq.com/keys
if (!defined('GROQ_API_KEY')) define('GROQ_API_KEY', 'gsk_XXXXXXXXXXXXXXXXXXXXXXXXXXXXXX');

// === OpenAI API (module Gestion Reclamation : analyse sentiment + suggestion reponse) ===
// Obtenez votre cle sur : https://platform.openai.com/api-keys
if (!defined('OPENAI_API_KEY')) define('OPENAI_API_KEY', 'sk-XXXXXXXXXXXXXXXXXXXXXXXX');
if (!defined('OPENAI_MODEL'))   define('OPENAI_MODEL',   'gpt-4o-mini');

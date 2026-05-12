<?php
/**
 * SMTP config pour le module Gestion Reclamation (MailService via PHPMailer).
 * Credentials externalises dans config/secrets.php (gitignored).
 */
require_once __DIR__ . '/secrets.php';
return [
    'host'       => 'smtp.gmail.com',
    'port'       => 587,
    'encryption' => 'tls',
    'username'   => defined('SMTP_USER_RECLAMATION') ? SMTP_USER_RECLAMATION : (defined('SMTP_USER') ? SMTP_USER : ''),
    'password'   => defined('SMTP_PASS_RECLAMATION') ? SMTP_PASS_RECLAMATION : (defined('SMTP_PASS') ? SMTP_PASS : ''),
    'from'       => defined('SMTP_USER_RECLAMATION') ? SMTP_USER_RECLAMATION : (defined('SMTP_USER') ? SMTP_USER : ''),
    'from_name'  => 'TinyTrack — Support',
];

<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef
 *
 * Adaptateur DB pour les API de Youssef : expose $pdo en utilisant la
 * connexion centrale de TinyTrack (Database singleton, config/db.php).
 */
require_once __DIR__ . '/../../config/db.php';
$pdo = Database::getInstance()->getConnection();

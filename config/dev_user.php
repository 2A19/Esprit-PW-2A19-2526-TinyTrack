<?php
/**
 * Module : Communication parents — Adaptateur user pour l'integration.
 * Remplace l'ancien dev_user.php d'Amen : au lieu de pull un user random
 * depuis la BDD, on lit l'utilisateur connecte via la session existante.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/login');
    exit;
}

$userId      = (int) $_SESSION['user_id'];
$userRole    = $_SESSION['user_role'] ?? 'parent';
$displayName = $_SESSION['user_nom'] ?? 'Utilisateur';

return [
    'id'   => $userId,
    'name' => $displayName,
    'role' => $userRole,
    'page' => $userRole === 'admin' ? 'back' : 'front',
];

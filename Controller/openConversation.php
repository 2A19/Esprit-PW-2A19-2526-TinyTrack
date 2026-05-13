<?php
/**
 * Module : Communication parents
 * Endpoint : ouvre (ou cree) une conversation entre un staff (educateur/admin)
 * et un parent, puis redirige vers la page de chat appropriee.
 */
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/login');
    exit;
}

$parentId = isset($_GET['parent_id']) ? (int) $_GET['parent_id'] : 0;
if ($parentId <= 0) {
    header('Location: /TinyTrack/parents');
    exit;
}

$staffId = (int) $_SESSION['user_id'];
$role    = $_SESSION['user_role'] ?? '';

// Seuls educateur et admin peuvent ouvrir une conversation depuis ici.
if ($role !== 'educateur' && $role !== 'admin') {
    header('Location: /TinyTrack/login');
    exit;
}

require_once __DIR__ . '/../config/database_comm.php';
$db = (new CommDatabase())->connect();

// Cherche une conversation existante entre ce staff et ce parent.
$stmt = $db->prepare("SELECT id FROM conversation WHERE parent_id = ? AND staff_id = ? ORDER BY id DESC LIMIT 1");
$stmt->execute([$parentId, $staffId]);
$existing = $stmt->fetch();

if ($existing) {
    $convId = (int) $existing->id;
} else {
    // Cree une nouvelle conversation
    $stmt = $db->prepare("INSERT INTO conversation (parent_id, staff_id, status, created_at) VALUES (?, ?, 'open', NOW())");
    $stmt->execute([$parentId, $staffId]);
    $convId = (int) $db->lastInsertId();
}

// Redirige vers la page de chat avec la conversation selectionnee.
header('Location: /TinyTrack/View/BackOffice/communication/communication_Backend.php?id=' . $convId);
exit;

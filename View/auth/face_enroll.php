<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../Controller/AuthController.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Non authentifie']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$action = $body['action'] ?? 'save';

$auth = new AuthController();

if ($action === 'remove') {
    echo json_encode($auth->removeFaceDescriptor($_SESSION['user_id']));
    exit;
}

$descriptor = $body['descriptor'] ?? null;
echo json_encode($auth->saveFaceDescriptor($_SESSION['user_id'], $descriptor));

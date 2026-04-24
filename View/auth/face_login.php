<?php
session_start();
header('Content-Type: application/json');
require_once __DIR__ . '/../../Controller/AuthController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$body = json_decode(file_get_contents('php://input'), true);
$descriptor = $body['descriptor'] ?? null;

$auth = new AuthController();
$result = $auth->loginWithFace($descriptor);
echo json_encode($result);

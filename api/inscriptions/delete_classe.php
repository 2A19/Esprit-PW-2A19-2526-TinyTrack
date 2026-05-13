<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../../Controller/ClasseController.php';

$id = $_POST['id'] ?? '';

if (empty($id) || !is_numeric($id)) {
    echo json_encode(['succes' => false, 'message' => 'ID invalide']);
    exit;
}

$controller = new ClasseController($pdo);
echo json_encode($controller->supprimer($id));
?>

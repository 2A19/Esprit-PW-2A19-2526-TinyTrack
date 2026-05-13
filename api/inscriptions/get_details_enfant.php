<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../../Model/InscriptionEnfant.php';

$id = $_GET['id'] ?? '';

if (empty($id) || !is_numeric($id)) {
    echo json_encode(['succes' => false, 'message' => 'ID invalide']);
    exit;
}

$enfant = new InscriptionEnfant($pdo);
$result = $enfant->obtenirParId($id);

echo $result
    ? json_encode(['succes' => true, 'enfant' => $result])
    : json_encode(['succes' => false, 'message' => 'Enfant non trouvé']);
?>

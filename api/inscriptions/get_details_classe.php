<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../../Model/Classe.php';

$id = $_GET['id'] ?? '';

if (empty($id) || !is_numeric($id)) {
    echo json_encode(['succes' => false, 'message' => 'ID invalide']);
    exit;
}

$classe = new Classe($pdo);
$result = $classe->obtenirAvecEnfants($id);

echo $result
    ? json_encode(['succes' => true, 'classe' => $result])
    : json_encode(['succes' => false, 'message' => 'Classe non trouvée']);
?>

<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/_bootstrap.php';

$enfant_id = $_POST['enfant_id'] ?? '';
$latitude  = $_POST['latitude']  ?? '';
$longitude = $_POST['longitude'] ?? '';
$precision = $_POST['precision'] ?? null;

if (empty($enfant_id) || !is_numeric($enfant_id) || empty($latitude) || empty($longitude)) {
    echo json_encode(['succes' => false, 'message' => 'Données invalides']);
    exit;
}

// Vérifier que l'enfant existe
$stmt = $pdo->prepare("SELECT id FROM enfants WHERE id = :id");
$stmt->execute([':id' => (int)$enfant_id]);
if (!$stmt->fetch()) {
    echo json_encode(['succes' => false, 'message' => 'Enfant introuvable']);
    exit;
}

// Insérer la position
$stmt = $pdo->prepare("
    INSERT INTO positions (enfant_id, latitude, longitude, precision_metres)
    VALUES (:enfant_id, :latitude, :longitude, :precision)
");
$stmt->execute([
    ':enfant_id' => (int)$enfant_id,
    ':latitude'  => (float)$latitude,
    ':longitude' => (float)$longitude,
    ':precision' => $precision ? (float)$precision : null
]);

echo json_encode(['succes' => true, 'message' => 'Position enregistrée']);
?>

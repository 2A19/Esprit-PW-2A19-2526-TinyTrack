<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/_bootstrap.php';

$enfant_id = $_POST['enfant_id'] ?? '';
$step      = (int)($_POST['step'] ?? 0);

if (empty($enfant_id) || !is_numeric($enfant_id)) {
    echo json_encode(['succes' => false, 'message' => 'ID invalide']);
    exit;
}

// Vérifier que l'enfant existe
$stmt = $pdo->prepare("SELECT id FROM enfants WHERE id = :id");
$stmt->execute([':id' => (int)$enfant_id]);
if (!$stmt->fetch()) {
    echo json_encode(['succes' => false, 'message' => 'Enfant introuvable']);
    exit;
}

// Trajet simulé autour de Tunis (quartier Belvédère → Lac → Centre)
// Chaque step = une position différente sur le trajet
$trajet = [
    ['lat' => 36.8190, 'lng' => 10.1658, 'label' => 'Jardin d\'enfants TinyTrack'],
    ['lat' => 36.8205, 'lng' => 10.1680, 'label' => 'Rue de la Liberté'],
    ['lat' => 36.8220, 'lng' => 10.1710, 'label' => 'Avenue Habib Bourguiba'],
    ['lat' => 36.8235, 'lng' => 10.1740, 'label' => 'Place de la République'],
    ['lat' => 36.8250, 'lng' => 10.1770, 'label' => 'Parc du Belvédère'],
    ['lat' => 36.8265, 'lng' => 10.1800, 'label' => 'Lac de Tunis'],
    ['lat' => 36.8250, 'lng' => 10.1830, 'label' => 'Les Berges du Lac'],
    ['lat' => 36.8235, 'lng' => 10.1800, 'label' => 'Retour vers le centre'],
    ['lat' => 36.8220, 'lng' => 10.1770, 'label' => 'Avenue de France'],
    ['lat' => 36.8205, 'lng' => 10.1740, 'label' => 'Médina de Tunis'],
    ['lat' => 36.8190, 'lng' => 10.1658, 'label' => 'Retour au jardin d\'enfants'],
];

// Ajouter un léger bruit aléatoire pour simuler le mouvement GPS réel
$pos = $trajet[$step % count($trajet)];
$lat = $pos['lat'] + (mt_rand(-50, 50) / 100000); // ±0.0005 degrés ≈ ±50m
$lng = $pos['lng'] + (mt_rand(-50, 50) / 100000);

$stmt = $pdo->prepare("
    INSERT INTO positions (enfant_id, latitude, longitude, precision_metres)
    VALUES (:enfant_id, :latitude, :longitude, :precision)
");
$stmt->execute([
    ':enfant_id' => (int)$enfant_id,
    ':latitude'  => $lat,
    ':longitude' => $lng,
    ':precision' => mt_rand(5, 25) // précision GPS simulée entre 5 et 25m
]);

echo json_encode([
    'succes'    => true,
    'step'      => $step,
    'label'     => $pos['label'],
    'latitude'  => $lat,
    'longitude' => $lng
]);
?>

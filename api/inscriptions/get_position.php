<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('ngrok-skip-browser-warning: 1');

require_once __DIR__ . '/_bootstrap.php';

$enfant_id = $_GET['enfant_id'] ?? '';

if (empty($enfant_id) || !is_numeric($enfant_id)) {
    echo json_encode(['succes' => false, 'message' => 'ID invalide']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT p.latitude, p.longitude, p.precision_metres, p.timestamp,
           e.enfant_nom, e.enfant_prenom, c.nom as classe_nom
    FROM positions p
    JOIN enfants e ON p.enfant_id = e.id
    JOIN classes c ON e.classe_id = c.id
    WHERE p.enfant_id = :enfant_id
    ORDER BY p.timestamp DESC
    LIMIT 1
");
$stmt->execute([':enfant_id' => (int)$enfant_id]);
$position = $stmt->fetch();

if (!$position) {
    echo json_encode(['succes' => false, 'message' => 'Aucune position disponible']);
    exit;
}

echo json_encode(['succes' => true, 'position' => $position]);
?>

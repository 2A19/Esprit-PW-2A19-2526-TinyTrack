<?php
/**
 * submit_avis.php
 * À placer dans : views/front/submit_avis.php
 * 
 * Structure attendue du projet :
 *   ProjetEvenements/
 *   ├── config.php          ← racine
 *   └── views/
 *       └── front/
 *           ├── index.php
 *           └── submit_avis.php  ← CE FICHIER
 */

// Remonte 2 niveaux depuis views/front/ pour atteindre la racine du projet
$config_path = dirname(dirname(dirname(__FILE__))) . '/config.php';

if (!file_exists($config_path)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Fichier config.php introuvable. Chemin testé : ' . $config_path
    ]);
    exit;
}

require_once $config_path;

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

$evenement_id = intval($_POST['evenement_id'] ?? 0);
$parent_id    = intval($_POST['parent_id']    ?? 0);
$note         = intval($_POST['note']         ?? 0);
$commentaire  = trim($_POST['commentaire']    ?? '');

if ($evenement_id <= 0 || $parent_id <= 0 || $note < 1 || $note > 5 || empty($commentaire)) {
    echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires.']);
    exit;
}

if (strlen($commentaire) > 500) {
    echo json_encode(['success' => false, 'message' => 'Commentaire trop long (max 500 caractères).']);
    exit;
}

try {
    $pdo = config::getConnexion();

    $stmt = $pdo->prepare("SELECT avis FROM evenement WHERE id = ?");
    $stmt->execute([$evenement_id]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'Événement introuvable.']);
        exit;
    }

    $avis_list = json_decode($row['avis'] ?? '[]', true) ?: [];

    // Un parent = un seul avis par événement
    foreach ($avis_list as $a) {
        if (intval($a['parent_id']) === $parent_id) {
            echo json_encode(['success' => false, 'message' => 'Vous avez déjà laissé un avis pour cet événement.']);
            exit;
        }
    }

    $nouvel_avis = [
        'parent_id'   => $parent_id,
        'note'        => $note,
        'commentaire' => htmlspecialchars($commentaire, ENT_QUOTES, 'UTF-8'),
        'date'        => date('Y-m-d H:i:s'),
    ];
    $avis_list[] = $nouvel_avis;

    $update = $pdo->prepare("UPDATE evenement SET avis = ? WHERE id = ?");
    $update->execute([json_encode($avis_list, JSON_UNESCAPED_UNICODE), $evenement_id]);

    $moyenne = round(array_sum(array_column($avis_list, 'note')) / count($avis_list), 1);

    echo json_encode([
        'success'     => true,
        'message'     => 'Avis publié !',
        'moyenne'     => $moyenne,
        'nb_avis'     => count($avis_list),
        'nouvel_avis' => $nouvel_avis,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Erreur BDD : ' . $e->getMessage()]);
}
?>
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

$controller = new ClasseController($pdo);

echo json_encode($controller->sauvegarder(
    trim($_POST['nom']                ?? ''),
    trim($_POST['couleur']            ?? ''),
    trim($_POST['description']        ?? ''),
    (int)($_POST['age_minimum']       ?? 0),
    (int)($_POST['age_maximum']       ?? 0),
    (int)($_POST['capacite_max']      ?? 25),
    trim($_POST['educateur_principal'] ?? ''),
    trim($_POST['salle']              ?? ''),
    trim($_POST['horaires']           ?? '')
));
?>

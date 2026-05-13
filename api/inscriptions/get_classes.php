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

$classe = new Classe($pdo);
echo json_encode(['succes' => true, 'classes' => $classe->obtenirTous()]);
?>

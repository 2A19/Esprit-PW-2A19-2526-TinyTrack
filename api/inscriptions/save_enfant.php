<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

require_once __DIR__ . '/_bootstrap.php';
require_once __DIR__ . '/../../Controller/InscriptionController.php';

$controller = new InscriptionController($pdo);

echo json_encode($controller->sauvegarder(
    trim($_POST['enfant_nom']            ?? ''),
    trim($_POST['enfant_prenom']         ?? ''),
    trim($_POST['enfant_date_naissance'] ?? ''),
    (int)($_POST['classe_id']            ?? 0),
    trim($_POST['groupe_sanguin']        ?? ''),
    trim($_POST['allergies_alimentaires']?? ''),
    trim($_POST['allergies_medicales']   ?? ''),
    trim($_POST['maladies_chroniques']   ?? ''),
    trim($_POST['vaccinations']          ?? ''),
    trim($_POST['contact_urgence_nom']   ?? ''),
    trim($_POST['contact_urgence_lien']  ?? ''),
    trim($_POST['contact_urgence_telephone'] ?? ''),
    trim($_POST['aliments_preferes']     ?? ''),
    trim($_POST['aliments_interdits']    ?? ''),
    trim($_POST['horaire_sieste']        ?? ''),
    (int)($_POST['autorise_photos']      ?? 0),
    (int)($_POST['autorise_sorties']     ?? 0),
    trim($_POST['notes_sante']           ?? ''),
    trim($_POST['notes_speciales']       ?? '')
));
?>

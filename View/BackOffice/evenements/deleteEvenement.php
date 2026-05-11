<?php
require_once(__DIR__ . '/../../controller/evenementController.php');

if (isset($_GET['id'])) {

    $id = $_GET['id'];

    $controller = new EvenementController();
    $controller->supprimer($id);

    header('Location: evenementList.php');
    exit;

} else {
    die("ID manquant");
}
?>
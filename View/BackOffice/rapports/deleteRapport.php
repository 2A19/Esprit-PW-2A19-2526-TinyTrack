<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once __DIR__ . '/../../../Controller/RapportController.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $controller = new RapportController();
    $controller->deleteRapport($_GET['id']);
    header('Location:listRapports.php?success=deleted');
    exit;
} else {
    header('Location:listRapports.php?error=invalid_id');
    exit;
}
?>

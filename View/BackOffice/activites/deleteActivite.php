<?php
require_once __DIR__ . '/../../../Controller/ActiviteController.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $controller = new ActiviteController();
    $controller->deleteActivite($_GET['id']);
    header('Location: listActivites.php?success=delete');
    exit;
} else {
    header('Location: listActivites.php');
    exit;
}

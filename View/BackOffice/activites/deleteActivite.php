<?php
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once '../../controller/ActiviteController.php';
require_once '../../config/config.php';

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = $_GET['id'];
    $db = Config::getConnexion();

    $sql = "SELECT COUNT(*) as total FROM rapport WHERE id_activite = :id";
    $query = $db->prepare($sql);
    $query->execute(['id' => $id]);
    $result = $query->fetch();

    if ($result['total'] > 0) {
        header('Location:listActivites.php?error=has_reports');
        exit;
    } else {
        $controller = new ActiviteController();
        $controller->deleteActivite($id);
        header('Location:listActivites.php?success=deleted');
        exit;
    }
} else {
    header('Location:listActivites.php?error=invalid_id');
    exit;
}
?>

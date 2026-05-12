<?php
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Rapport.php';

class RapportController {

    /**
     * Router-compatible adapter methods (delegate to existing view files).
     * Keeps Mohamed's controller logic intact while plugging into the central Router.
     */
    public function index() {
        if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
        if ($_SESSION['user_role'] === 'parent') {
            header('Location: /TinyTrack/View/FrontOffice/rapports/listRapportsParent.php');
        } else {
            header('Location: /TinyTrack/View/BackOffice/rapports/listRapports.php');
        }
        exit;
    }
    public function add() {
        if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
        header('Location: /TinyTrack/View/FrontOffice/rapports/addRapport.php');
        exit;
    }
    public function edit($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
        header('Location: /TinyTrack/View/BackOffice/rapports/editRapport.php?id=' . (int)$id);
        exit;
    }
    public function delete($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
        $this->deleteRapport((int)$id);
        header('Location: /TinyTrack/View/BackOffice/rapports/listRapports.php');
        exit;
    }
    public function parent() {
        if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
        header('Location: /TinyTrack/View/FrontOffice/rapports/listRapportsParent.php');
        exit;
    }
    public function exportPdf($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
        header('Location: /TinyTrack/View/FrontOffice/rapports/exportRapportPdf.php?id=' . (int)$id);
        exit;
    }
    public function activitesEducateur($id) {
        if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
        header('Location: /TinyTrack/View/FrontOffice/rapports/listActivitesEducateur.php?id_educateur=' . (int)$id);
        exit;
    }

    public function listRapports() {
        $sql = "SELECT * FROM rapport";
        $db = Config::getConnexion();
        return $db->query($sql);
    }

    public function listRapportsWithActivite($tri = 'desc') {
        $ordre = 'DESC';

        if ($tri === 'asc') {
            $ordre = 'ASC';
        }

        $sql = "SELECT r.*, a.nom_activite
                FROM rapport r
                INNER JOIN activite a ON r.id_activite = a.id_activite
                ORDER BY r.date_rapport $ordre";

        $db = Config::getConnexion();
        return $db->query($sql);
    }

    /**
     * Liste les rapports d'un educateur donne (avec nom d'activite).
     */
    public function listRapportsByEducateur($id_educateur, $tri = 'desc') {
        $ordre = ($tri === 'asc') ? 'ASC' : 'DESC';
        $sql = "SELECT r.*, a.nom_activite
                FROM rapport r
                INNER JOIN activite a ON r.id_activite = a.id_activite
                WHERE r.id_educateur = :id
                ORDER BY r.date_rapport $ordre";
        $db = Config::getConnexion();
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => (int)$id_educateur]);
        return $stmt;
    }

    public function addRapport($rapport) {
        $sql = "INSERT INTO rapport (contenu_rapport, date_rapport, id_enfant, id_activite, id_educateur)
                VALUES (:contenu_rapport, :date_rapport, :id_enfant, :id_activite, :id_educateur)";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'contenu_rapport' => $rapport->getContenuRapport(),
            'date_rapport' => $rapport->getDateRapport(),
            'id_enfant' => $rapport->getIdEnfant(),
            'id_activite' => $rapport->getIdActivite(),
            'id_educateur' => $rapport->getIdEducateur()
        ]);
    }

    public function deleteRapport($id) {
        $sql = "DELETE FROM rapport WHERE id_rapport = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
    }

    public function showRapport($id) {
        $sql = "SELECT * FROM rapport WHERE id_rapport = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    public function updateRapport($rapport, $id) {
        $sql = "UPDATE rapport SET
                contenu_rapport = :contenu_rapport,
                date_rapport = :date_rapport,
                id_enfant = :id_enfant,
                id_activite = :id_activite,
                id_educateur = :id_educateur
                WHERE id_rapport = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'id' => $id,
            'contenu_rapport' => $rapport->getContenuRapport(),
            'date_rapport' => $rapport->getDateRapport(),
            'id_enfant' => $rapport->getIdEnfant(),
            'id_activite' => $rapport->getIdActivite(),
            'id_educateur' => $rapport->getIdEducateur()
        ]);
    }
}
?>

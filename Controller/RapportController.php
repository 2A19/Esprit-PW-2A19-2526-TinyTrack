<?php
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Rapport.php';

class RapportController {

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

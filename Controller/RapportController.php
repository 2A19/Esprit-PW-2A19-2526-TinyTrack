<?php
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../Model/Rapport.php';

class RapportController {

    public function listRapports() {
        $sql = "SELECT * FROM rapport";
        $db = Config::getConnexion();
        return $db->query($sql);
    }

    public function listRapportsWithActivite() {
        $sql = "SELECT r.*, a.nom_activite
                FROM rapport r
                INNER JOIN activite a ON r.id_activite = a.id_activite
                ORDER BY r.date_rapport DESC";
        $db = Config::getConnexion();
        return $db->query($sql);
    }

    public function addRapport($rapport) {
        $sql = "INSERT INTO rapport (contenu_rapport, date_rapport, id_activite, id_educateur)
                VALUES (:contenu_rapport, :date_rapport, :id_activite, :id_educateur)";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'contenu_rapport' => $rapport->getContenuRapport(),
            'date_rapport' => $rapport->getDateRapport(),
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
                id_activite = :id_activite,
                id_educateur = :id_educateur
                WHERE id_rapport = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'id' => $id,
            'contenu_rapport' => $rapport->getContenuRapport(),
            'date_rapport' => $rapport->getDateRapport(),
            'id_activite' => $rapport->getIdActivite(),
            'id_educateur' => $rapport->getIdEducateur()
        ]);
    }
}
?>
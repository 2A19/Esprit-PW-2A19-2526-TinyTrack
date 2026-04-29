<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Activite.php';

class ActiviteController {

    public function listActivites() {
        $sql = "SELECT * FROM activite";
        $db = Config::getConnexion();
        return $db->query($sql);
    }

    public function listActivitesByEducateur($id_educateur) {
        $sql = "SELECT * FROM activite WHERE id_educateur = :id_educateur";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'id_educateur' => $id_educateur
        ]);
        return $query->fetchAll();
    }

    public function addActivite($activite) {
        $sql = "INSERT INTO activite (nom_activite, description, date_activite, heure_activite, id_educateur)
                VALUES (:nom_activite, :description, :date_activite, :heure_activite, :id_educateur)";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'nom_activite' => $activite->getNomActivite(),
            'description' => $activite->getDescription(),
            'date_activite' => $activite->getDateActivite(),
            'heure_activite' => $activite->getHeureActivite(),
            'id_educateur' => $activite->getIdEducateur()
        ]);
    }

    public function deleteActivite($id) {
        $sql = "DELETE FROM activite WHERE id_activite = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
    }

    public function showActivite($id) {
        $sql = "SELECT * FROM activite WHERE id_activite = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute(['id' => $id]);
        return $query->fetch();
    }

    public function updateActivite($activite, $id) {
        $sql = "UPDATE activite SET
                nom_activite = :nom_activite,
                description = :description,
                date_activite = :date_activite,
                heure_activite = :heure_activite,
                id_educateur = :id_educateur
                WHERE id_activite = :id";
        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([
            'id' => $id,
            'nom_activite' => $activite->getNomActivite(),
            'description' => $activite->getDescription(),
            'date_activite' => $activite->getDateActivite(),
            'heure_activite' => $activite->getHeureActivite(),
            'id_educateur' => $activite->getIdEducateur()
        ]);
    }

    public function statistiquesActivitesParEducateur() {
        $sql = "SELECT id_educateur, COUNT(*) AS total_activites
                FROM activite
                GROUP BY id_educateur
                ORDER BY total_activites DESC";

        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }

    public function expertiseEducateurs() {
        $sql = "SELECT id_educateur, nom_activite, COUNT(*) AS total
                FROM activite
                GROUP BY id_educateur, nom_activite
                ORDER BY id_educateur ASC, total DESC";

        $db = Config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute();

        return $query->fetchAll();
    }
}
?>
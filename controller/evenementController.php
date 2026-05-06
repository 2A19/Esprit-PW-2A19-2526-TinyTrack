<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/evenement.php');

class EvenementController {

    // ✅ Méthode utilitaire pour garantir un JSON valide pour le champ avis
    private function formatAvis(?string $avis): string {
        if ($avis === null || trim($avis) === '') {
            return '[]'; // Valeur par défaut JSON vide
        }
        // Vérifier si c'est déjà un JSON valide
        json_decode($avis);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $avis;
        }
        // Sinon encapsuler dans un tableau JSON
        return json_encode([trim($avis)]);
    }

    // afficher() — list all events
    public function afficher() {
        $sql = "SELECT * FROM evenement";
        $db = config::getConnexion();
        try {
            $list = $db->query($sql);
            return $list;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // afficherParId($id) — get one event by ID
    public function afficherParId($id) {
        $sql = "SELECT * FROM evenement WHERE id = :id";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        try {
            $query->execute([':id' => $id]); // ✅ Utilisation de paramètre lié (sécurisé)
            $evenement = $query->fetch();
            return $evenement;
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // ajouter() — insert a new event
    public function ajouter(Evenement $evenement) {
        $sql = "INSERT INTO evenement (titre, description, date, heure_debut, heure_fin, type, lieu, capacite_max, prix, groupe_id, statut, avis) 
                VALUES (:titre, :description, :date, :heure_debut, :heure_fin, :type, :lieu, :capacite_max, :prix, :groupe_id, :statut, :avis)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'titre'        => $evenement->getTitre(),
                'description'  => $evenement->getDescription(),
                'date'         => $evenement->getDate() ? $evenement->getDate()->format('Y-m-d') : null,
                'heure_debut'  => $evenement->getHeureDebut(),
                'heure_fin'    => $evenement->getHeureFin(),
                'type'         => $evenement->getType(),
                'lieu'         => $evenement->getLieu(),
                'capacite_max' => $evenement->getCapaciteMax(),
                'prix'         => $evenement->getPrix(),
                'groupe_id'    => $evenement->getGroupeId(),
                'statut'       => $evenement->getStatut(),
                'avis'         => $this->formatAvis($evenement->getAvis()) // ✅ FIX
            ]);
        } catch (Exception $e) {
            die('Erreur SQL : ' . $e->getMessage());
        }
    }

    // supprimer($id) — delete an event by ID
    public function supprimer($id) {
        $sql = "DELETE FROM evenement WHERE id = :id";
        $db = config::getConnexion();
        $req = $db->prepare($sql);
        $req->bindValue(':id', $id);
        try {
            $req->execute();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    // modifier($evenement, $id) — update an existing event
    public function modifier(Evenement $evenement, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE evenement SET
                    titre        = :titre,
                    description  = :description,
                    date         = :date,
                    heure_debut  = :heure_debut,
                    heure_fin    = :heure_fin,
                    type         = :type,
                    lieu         = :lieu,
                    capacite_max = :capacite_max,
                    prix         = :prix,
                    groupe_id    = :groupe_id,
                    statut       = :statut,
                    avis         = :avis
                WHERE id = :id'
            );
            $query->execute([
                'id'           => $id,
                'titre'        => $evenement->getTitre(),
                'description'  => $evenement->getDescription(),
                'date'         => $evenement->getDate() ? $evenement->getDate()->format('Y-m-d') : null,
                'heure_debut'  => $evenement->getHeureDebut(),
                'heure_fin'    => $evenement->getHeureFin(),
                'type'         => $evenement->getType(),
                'lieu'         => $evenement->getLieu(),
                'capacite_max' => $evenement->getCapaciteMax(),
                'prix'         => $evenement->getPrix(),
                'groupe_id'    => $evenement->getGroupeId(),
                'statut'       => $evenement->getStatut(),
                'avis'         => $this->formatAvis($evenement->getAvis()) 
            ]);
        } catch (PDOException $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }
}
?>
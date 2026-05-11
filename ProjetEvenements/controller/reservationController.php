<?php
require_once(__DIR__ . '/../config.php');
require_once(__DIR__ . '/../models/reservation.php');

class ReservationController {

    public function afficher() {
        $sql = "SELECT r.*, e.titre AS evenement_titre FROM reservation r
                LEFT JOIN evenement e ON r.evenement_id = e.id
                ORDER BY r.date_reservation DESC";
        $db = config::getConnexion();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function afficherParId($id) {
        $sql = "SELECT r.*, e.titre AS evenement_titre FROM reservation r
                LEFT JOIN evenement e ON r.evenement_id = e.id
                WHERE r.id = :id";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function afficherParEvenement($evenement_id) {
        $sql = "SELECT * FROM reservation WHERE evenement_id = :eid ORDER BY date_reservation DESC";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':eid' => $evenement_id]);
            return $query->fetchAll();
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function ajouter(Reservation $reservation) {
        $sql = "INSERT INTO reservation VALUES (NULL, :evenement_id, :enfant_id, :parent_id, :nb_accompagnants, :commentaire, :date_reservation, :statut, :paiement)";
        $db = config::getConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                'evenement_id'    => $reservation->getEvenementId(),
                'enfant_id'       => $reservation->getEnfantId(),
                'parent_id'       => $reservation->getParentId(),
                'nb_accompagnants'=> $reservation->getNbAccompagnants(),
                'commentaire'     => $reservation->getCommentaire(),
                'date_reservation'=> $reservation->getDateReservation()->format('Y-m-d H:i:s'),
                'statut'          => $reservation->getStatut(),
                'paiement'        => $reservation->getPaiement()
            ]);
        } catch (Exception $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    public function supprimer($id) {
        $sql = "DELETE FROM reservation WHERE id = :id";
        $db = config::getConnexion();
        try {
            $req = $db->prepare($sql);
            $req->execute([':id' => $id]);
        } catch (Exception $e) {
            die('Erreur: ' . $e->getMessage());
        }
    }

    public function modifier(Reservation $reservation, $id) {
        $db = config::getConnexion();
        try {
            $query = $db->prepare(
                'UPDATE reservation SET
                    evenement_id     = :evenement_id,
                    enfant_id        = :enfant_id,
                    parent_id        = :parent_id,
                    nb_accompagnants = :nb_accompagnants,
                    commentaire      = :commentaire,
                    statut           = :statut,
                    paiement         = :paiement
                WHERE id = :id'
            );
            $query->execute([
                'id'              => $id,
                'evenement_id'    => $reservation->getEvenementId(),
                'enfant_id'       => $reservation->getEnfantId(),
                'parent_id'       => $reservation->getParentId(),
                'nb_accompagnants'=> $reservation->getNbAccompagnants(),
                'commentaire'     => $reservation->getCommentaire(),
                'statut'          => $reservation->getStatut(),
                'paiement'        => $reservation->getPaiement()
            ]);
        } catch (PDOException $e) {
            echo 'Erreur: ' . $e->getMessage();
        }
    }

    public function compterParEvenement($evenement_id) {
        $sql = "SELECT COUNT(*) as total FROM reservation WHERE evenement_id = :eid AND statut != 'annulee'";
        $db = config::getConnexion();
        $query = $db->prepare($sql);
        $query->execute([':eid' => $evenement_id]);
        return $query->fetch()['total'];
    }
}

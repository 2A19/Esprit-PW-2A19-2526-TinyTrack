<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Reponse.php';
require_once __DIR__ . '/../service/MailService.php';

class ReponseController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    
    // Insère une réponse, passe la réclamation à "Traité", puis notifie le client par email
    public function addReponse($id_reclamation, $message) {
        if (empty($message)) return ['message' => 'Le message ne peut pas être vide.'];

        $sql = "INSERT INTO reponses (id_reclamation, message) VALUES (?, ?)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_reclamation, $message]);

        $sqlStatus = "UPDATE reclamations SET statut = 'Traité' WHERE id = ?";
        $stmtStatus = $this->db->prepare($sqlStatus);
        $stmtStatus->execute([$id_reclamation]);

        // email — notification de réponse au client concerné
        $rec = $this->db->prepare("SELECT nom_client, email, sujet FROM reclamations WHERE id = ?");
        $rec->execute([$id_reclamation]);
        $row = $rec->fetch();
        if ($row) {
            $mail = new MailService();
            $mail->sendReponseNotification(
                $row['email'],
                $row['nom_client'],
                $row['sujet'],
                $message,
                (int) $id_reclamation
            );
        }

        return true;
    }

    
    public function updateReponse($id, $message) {
        if (empty($message)) return ['message' => 'Le message ne peut pas être vide.'];

        $sql = "UPDATE reponses SET message = ? WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$message, $id]);
        return true;
    }

    
    public function deleteReponse($id) {
        $sql = "DELETE FROM reponses WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return true;
    }

    // Retourne toutes les réponses d'une réclamation triées par date croissante (tri)
    public function getReponsesByReclamation($id_reclamation) {
        $sql = "SELECT * FROM reponses WHERE id_reclamation = ? ORDER BY date_reponse ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_reclamation]);
        $reponses = [];
        while ($row = $stmt->fetch()) {
            $reponses[] = new Reponse($row['id'], $row['id_reclamation'], $row['message'], $row['auteur'], $row['date_reponse']);
        }
        return $reponses;
    }

   
    public function getAllReponses() {
        $sql = "SELECT r.*, rec.nom_client, rec.sujet
                FROM reponses r
                JOIN reclamations rec ON r.id_reclamation = rec.id
                ORDER BY r.date_reponse DESC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // stat : retourne le nombre total de réponses
    public function getTotalReponses() {
        return (int) $this->db->query("SELECT COUNT(*) FROM reponses")->fetchColumn();
    }

    // stat : calcule la moyenne de réponses par réclamation
    public function getAvgReponsesParReclamation() {
        $total    = $this->getTotalReponses();
        $totalRec = (int) $this->db->query("SELECT COUNT(*) FROM reclamations")->fetchColumn();
        if ($totalRec == 0) return 0;
        return round($total / $totalRec, 1);
    }

    // stat : retourne les auteurs ayant répondu le plus, groupés et triés par volume
    public function getTopAuteurs($limit = 5) {
        $sql = "SELECT auteur, COUNT(*) as total FROM reponses GROUP BY auteur ORDER BY total DESC LIMIT ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$limit]);
        return $stmt->fetchAll();
    }
}

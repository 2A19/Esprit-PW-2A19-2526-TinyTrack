<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../model/Reclamation.php';
require_once __DIR__ . '/../service/MailService.php';
require_once __DIR__ . '/../service/SentimentService.php';

class ReclamationController {
    private $db;

    public function __construct() {
        $this->db = Config::getConnexion();
    }

    
    /**
     * Liste les reclamations soumises par les parents dont AU MOINS un enfant
     * est dans un groupe gere par l'educateur donne. Sert au BackOffice pour
     * qu'un educateur ne voit que les reclamations qui le concernent.
     */
    public function listReclamationsByEducateur($educateurId) {
        $sql = "SELECT r.* FROM reclamations r
                WHERE r.email IN (
                    SELECT DISTINCT u.email FROM user u
                    JOIN enfant e ON e.parent_id = u.id
                    JOIN groupe g ON g.id = e.groupe_id
                    WHERE g.educateur_id = :eduid
                )
                ORDER BY r.date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':eduid' => (int)$educateurId]);
        $reclamations = [];
        while ($row = $stmt->fetch()) {
            $reclamations[] = new Reclamation(
                $row['id'], $row['nom_client'], $row['email'],
                $row['sujet'], $row['description'], $row['statut'], $row['date_creation'], $row['sentiment'] ?? null
            );
        }
        return $reclamations;
    }

    /**
     * Stats reclamations limitees aux parents lies a un educateur.
     */
    public function getStatsByEducateur($educateurId) {
        $base = "FROM reclamations r WHERE r.email IN (
                    SELECT DISTINCT u.email FROM user u
                    JOIN enfant e ON e.parent_id = u.id
                    JOIN groupe g ON g.id = e.groupe_id
                    WHERE g.educateur_id = :eduid
                )";
        $stats = [];
        $stmt = $this->db->prepare("SELECT COUNT(*) $base"); $stmt->execute([':eduid'=>(int)$educateurId]); $stats['total'] = $stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) $base AND r.statut='En attente'"); $stmt->execute([':eduid'=>(int)$educateurId]); $stats['pending'] = $stmt->fetchColumn();
        $stmt = $this->db->prepare("SELECT COUNT(*) $base AND r.statut='Traité'"); $stmt->execute([':eduid'=>(int)$educateurId]); $stats['solved'] = $stmt->fetchColumn();
        return $stats;
    }

    /**
     * Liste les reclamations soumises par UN parent precis (filtre par email).
     * Utilise sur la page FrontOffice pour que chaque parent ne voie QUE ses
     * propres reclamations.
     */
    public function listReclamationsByEmail($email) {
        $sql = "SELECT * FROM reclamations WHERE email = :email ORDER BY date_creation DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $reclamations = [];
        while ($row = $stmt->fetch()) {
            $reclamations[] = new Reclamation(
                $row['id'], $row['nom_client'], $row['email'],
                $row['sujet'], $row['description'], $row['statut'], $row['date_creation'], $row['sentiment'] ?? null
            );
        }
        return $reclamations;
    }

    public function listReclamations() {
        $sql = "SELECT * FROM reclamations ORDER BY date_creation DESC";
        $stmt = $this->db->query($sql);
        $reclamations = [];
        while ($row = $stmt->fetch()) {
            $reclamations[] = new Reclamation(
                $row['id'], $row['nom_client'], $row['email'],
                $row['sujet'], $row['description'], $row['statut'], $row['date_creation'], $row['sentiment'] ?? null
            );
        }
        return $reclamations;
    }

  
    // Valide et insère une nouvelle réclamation, puis envoie un email de confirmation au client et analyse le sentiment via GPT
    public function addReclamation($data) {
        $errors = $this->validate($data);
        if (empty($errors)) {
            $sql = "INSERT INTO reclamations (nom_client, email, sujet, description) VALUES (?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['nom_client'], $data['email'], $data['sujet'], $data['description']]);
            $newId = (int) $this->db->lastInsertId();

            // email — accusé de réception au client
            $mail = new MailService();
            $mail->sendConfirmation(
                $data['email'],
                $data['nom_client'],
                $data['sujet'],
                $data['description'],
                $newId
            );

            // stat — analyse de sentiment via OpenAI (silencieux en cas d'erreur)
            $this->analyzeSentiment($newId, $data['description']);

            return true;
        }
        return $errors;
    }

    // Détecte le sentiment par mots-clés et le persiste en base
    private function analyzeSentiment(int $id, string $description): void {
        $sentiment = SentimentService::analyze($description);
        $stmt = $this->db->prepare("UPDATE reclamations SET sentiment = ? WHERE id = ?");
        $stmt->execute([$sentiment, $id]);
    }

   
    public function updateReclamation($id, $data) {
        $errors = $this->validate($data);
        if (empty($errors)) {
            $sql = "UPDATE reclamations SET nom_client = ?, email = ?, sujet = ?, description = ? WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$data['nom_client'], $data['email'], $data['sujet'], $data['description'], $id]);
            return true;
        }
        return $errors;
    }

    
    private function validate($data) {
        $errors = [];
        if (empty($data['nom_client'])) $errors['nom_client'] = "Le nom est requis.";
        if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors['email'] = "Email invalide.";
        if (empty($data['sujet'])) $errors['sujet'] = "Le sujet est requis.";
        if (empty($data['description'])) $errors['description'] = "La description est requise.";
        return $errors;
    }

    
    public function deleteReclamation($id) {
        $sql = "DELETE FROM reclamations WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return true;
    }

    // Recherche et retourne une réclamation par son identifiant
    public function getReclamationById($id) {
        $sql = "SELECT * FROM reclamations WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if ($row) return new Reclamation(
            $row['id'], $row['nom_client'], $row['email'],
            $row['sujet'], $row['description'], $row['statut'], $row['date_creation'], $row['sentiment'] ?? null
        );
        return null;
    }

    // stat : compte le total, les en attente et les traitées
    public function getStats() {
        $stats = [];
        $stats['total']   = $this->db->query("SELECT COUNT(*) FROM reclamations")->fetchColumn();
        $stats['pending'] = $this->db->query("SELECT COUNT(*) FROM reclamations WHERE statut = 'En attente'")->fetchColumn();
        $stats['solved']  = $this->db->query("SELECT COUNT(*) FROM reclamations WHERE statut = 'Traité'")->fetchColumn();
        return $stats;
    }

    // stat : retourne les sujets les plus récurrents groupés et triés par fréquence
    public function getTopSubjects($limit = 5) {
        // LIMIT en parametre prepare = string par defaut -> erreur SQL.
        // On cast en int et on inline (safe : valeur numerique uniquement).
        $limit = (int) $limit;
        if ($limit <= 0) $limit = 5;
        $sql = "SELECT sujet, COUNT(*) as total FROM reclamations GROUP BY sujet ORDER BY total DESC LIMIT $limit";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    // stat : calcule le pourcentage de réclamations traitées sur le total
    public function getResolutionRate() {
        $total = $this->db->query("SELECT COUNT(*) FROM reclamations")->fetchColumn();
        if ($total == 0) return 0;
        $solved = $this->db->query("SELECT COUNT(*) FROM reclamations WHERE statut = 'Traité'")->fetchColumn();
        return round(($solved / $total) * 100, 1);
    }
}

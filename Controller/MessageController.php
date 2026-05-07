<?php
require_once __DIR__ . '/../config/db.php';

class MessageController extends Controller {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // GET /messages
    public function index(): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $this->render('FrontOffice/messages', [
            'recus'   => $this->getMessagesRecus($userId),
            'envoyes' => $this->getMessagesEnvoyes($userId),
        ]);
    }

    /**
     * Get messages received by a user.
     */
    public function getMessagesRecus($userId) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.prenom AS exp_prenom, u.nom AS exp_nom, u.role AS exp_role
            FROM message m
            JOIN user u ON m.expediteur_id = u.id
            WHERE m.destinataire_id = :uid
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetchAll();
    }

    /**
     * Get messages sent by a user.
     */
    public function getMessagesEnvoyes($userId) {
        $stmt = $this->db->prepare("
            SELECT m.*, u.prenom AS dest_prenom, u.nom AS dest_nom
            FROM message m
            JOIN user u ON m.destinataire_id = u.id
            WHERE m.expediteur_id = :uid
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([':uid' => (int)$userId]);
        return $stmt->fetchAll();
    }
}

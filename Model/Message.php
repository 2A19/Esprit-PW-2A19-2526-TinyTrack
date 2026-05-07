<?php
require_once __DIR__ . '/../config/db.php';

class Message {

    public function recus($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT m.*, u.prenom AS exp_prenom, u.nom AS exp_nom, u.role AS exp_role
            FROM message m
            JOIN user u ON m.expediteur_id = u.id
            WHERE m.destinataire_id = :uid
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function envoyes($userId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT m.*, u.prenom AS dest_prenom, u.nom AS dest_nom
            FROM message m
            JOIN user u ON m.destinataire_id = u.id
            WHERE m.expediteur_id = :uid
            ORDER BY m.date_envoi DESC
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetchAll();
    }

    public function compterNonLus() {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT COUNT(*) FROM message WHERE lu = 0")->fetchColumn();
    }
}

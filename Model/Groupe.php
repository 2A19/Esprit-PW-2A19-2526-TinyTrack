<?php
require_once __DIR__ . '/../config/db.php';

class Groupe {

    public function afficher() {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT id, nom, niveau FROM groupe ORDER BY nom")->fetchAll();
    }

    public function afficherParId($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM groupe WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function afficherParEducateur($educateurId) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM groupe WHERE educateur_id = :id");
        $stmt->execute([':id' => $educateurId]);
        return $stmt->fetch();
    }

    public function compter() {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT COUNT(*) FROM groupe")->fetchColumn();
    }
}

<?php
require_once __DIR__ . '/../config/db.php';

class ProfilController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Get a user's profile by ID.
     */
    public function getProfil($id) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    /**
     * Update user profile.
     */
    public function updateProfil($id, $data) {
        $errors = [];
        $lettres = '/^[a-zA-ZÀ-ÿ\s\-]+$/';

        if (empty(trim($data['nom'] ?? ''))) $errors[] = "Le nom est obligatoire.";
        elseif (!preg_match($lettres, $data['nom'])) $errors[] = "Le nom ne doit contenir que des lettres.";
        if (empty(trim($data['prenom'] ?? ''))) $errors[] = "Le prénom est obligatoire.";
        elseif (!preg_match($lettres, $data['prenom'])) $errors[] = "Le prénom ne doit contenir que des lettres.";

        if (!empty($data['telephone'])) {
            $tel = preg_replace('/\s/', '', $data['telephone']);
            if (!preg_match('/^(\+216)?\d{8}$/', $tel)) $errors[] = "Numéro de téléphone invalide.";
        }
        if (!empty($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email invalide.";
        }

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $stmt = $this->db->prepare("UPDATE user SET nom = :nom, prenom = :prenom, email = :email, telephone = :tel WHERE id = :id");
        $stmt->execute([
            ':nom' => htmlspecialchars(trim($data['nom'])),
            ':prenom' => htmlspecialchars(trim($data['prenom'])),
            ':email' => trim($data['email'] ?? ''),
            ':tel' => !empty($data['telephone']) ? trim($data['telephone']) : null,
            ':id' => (int)$id
        ]);

        $_SESSION['user_nom'] = trim($data['prenom']) . ' ' . trim($data['nom']);
        return ['success' => true];
    }

    /**
     * Get children belonging to a parent, with their group name.
     */
    public function getEnfantsParent($parentId) {
        $stmt = $this->db->prepare("SELECT e.*, g.nom AS groupe_nom FROM enfant e LEFT JOIN groupe g ON e.groupe_id = g.id WHERE e.parent_id = :pid ORDER BY e.prenom");
        $stmt->execute([':pid' => (int)$parentId]);
        return $stmt->fetchAll();
    }
}

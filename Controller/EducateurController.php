<?php
require_once __DIR__ . '/../config/db.php';

class EducateurController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * List all educateurs with their group info and child count.
     */
    public function listerEducateurs() {
        $stmt = $this->db->query("
            SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.statut,
                   g.nom AS groupe_nom, g.niveau AS groupe_niveau,
                   (SELECT COUNT(*) FROM enfant e WHERE e.groupe_id = g.id AND e.statut = 'actif') AS nb_enfants
            FROM user u
            LEFT JOIN groupe g ON g.educateur_id = u.id
            WHERE u.role = 'educateur'
            ORDER BY u.nom
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get an educateur's profile by ID.
     */
    public function getProfil($id) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = :id AND role = 'educateur'");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    /**
     * Get the group assigned to an educateur.
     */
    public function getGroupe($educateurId) {
        $stmt = $this->db->prepare("SELECT * FROM groupe WHERE educateur_id = :id");
        $stmt->execute([':id' => (int)$educateurId]);
        return $stmt->fetch();
    }

    /**
     * Archive (deactivate) a user account.
     */
    public function archiverCompte($id) {
        $stmt = $this->db->prepare("UPDATE user SET statut = 'inactif' WHERE id = :id AND role != 'admin'");
        $stmt->execute([':id' => (int)$id]);
        return true;
    }

    /**
     * Reactivate a user account.
     */
    public function activerCompte($id) {
        $stmt = $this->db->prepare("UPDATE user SET statut = 'actif' WHERE id = :id AND role != 'admin'");
        $stmt->execute([':id' => (int)$id]);
        return true;
    }

    /**
     * List all parents with their children.
     */
    public function listerParents() {
        $stmt = $this->db->query("
            SELECT u.*,
                   (SELECT GROUP_CONCAT(e.prenom SEPARATOR ', ') FROM enfant e WHERE e.parent_id = u.id AND e.statut = 'actif') AS enfants_noms,
                   (SELECT COUNT(*) FROM enfant e WHERE e.parent_id = u.id AND e.statut = 'actif') AS nb_enfants
            FROM user u
            WHERE u.role = 'parent'
            ORDER BY u.nom
        ");
        return $stmt->fetchAll();
    }

    /**
     * Update educateur profile.
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

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $stmt = $this->db->prepare("UPDATE user SET nom = :nom, prenom = :prenom, telephone = :tel, adresse = :adresse, specialite = :specialite WHERE id = :id AND role = 'educateur'");
        $stmt->execute([
            ':nom' => htmlspecialchars(trim($data['nom'])),
            ':prenom' => htmlspecialchars(trim($data['prenom'])),
            ':tel' => !empty($data['telephone']) ? htmlspecialchars(trim($data['telephone'])) : null,
            ':adresse' => !empty($data['adresse']) ? htmlspecialchars(trim($data['adresse'])) : null,
            ':specialite' => !empty($data['specialite']) ? htmlspecialchars(trim($data['specialite'])) : null,
            ':id' => (int)$id
        ]);

        // Update session name
        $_SESSION['user_nom'] = trim($data['prenom']) . ' ' . trim($data['nom']);

        return ['success' => true];
    }

    /**
     * Get active children in a group, with their medical info.
     */
    public function getEnfantsGroupe($groupeId) {
        $stmt = $this->db->prepare("
            SELECT e.*, d.groupe_sanguin, d.allergies, d.medecin_traitant, d.telephone_urgence
            FROM enfant e
            LEFT JOIN dossier_medical d ON d.enfant_id = e.id
            WHERE e.groupe_id = :groupe_id AND e.statut = 'actif'
            ORDER BY e.nom
        ");
        $stmt->execute([':groupe_id' => (int)$groupeId]);
        return $stmt->fetchAll();
    }
}

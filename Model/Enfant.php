<?php
require_once __DIR__ . '/../config/db.php';

class Enfant {
    private $id;
    private $nom;
    private $prenom;
    private $date_naissance;
    private $sexe;
    private $photo;
    private $groupe_id;
    private $parent_id;
    private $date_inscription;
    private $statut;

    // Constructeur
    public function __construct($nom = "", $prenom = "", $date_naissance = "", $sexe = "", $photo = "", $groupe_id = null, $parent_id = null, $date_inscription = "", $statut = "actif") {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->date_naissance = $date_naissance;
        $this->sexe = $sexe;
        $this->photo = $photo;
        $this->groupe_id = $groupe_id;
        $this->parent_id = $parent_id;
        $this->date_inscription = $date_inscription;
        $this->statut = $statut;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getDateNaissance() { return $this->date_naissance; }
    public function getSexe() { return $this->sexe; }
    public function getPhoto() { return $this->photo; }
    public function getGroupeId() { return $this->groupe_id; }
    public function getParentId() { return $this->parent_id; }
    public function getDateInscription() { return $this->date_inscription; }
    public function getStatut() { return $this->statut; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setDateNaissance($date_naissance) { $this->date_naissance = $date_naissance; }
    public function setSexe($sexe) { $this->sexe = $sexe; }
    public function setPhoto($photo) { $this->photo = $photo; }
    public function setGroupeId($groupe_id) { $this->groupe_id = $groupe_id; }
    public function setParentId($parent_id) { $this->parent_id = $parent_id; }
    public function setDateInscription($date_inscription) { $this->date_inscription = $date_inscription; }
    public function setStatut($statut) { $this->statut = $statut; }

    // CREATE
    public function ajouter() {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO enfant (nom, prenom, date_naissance, sexe, photo, groupe_id, parent_id, date_inscription, statut)
                VALUES (:nom, :prenom, :date_naissance, :sexe, :photo, :groupe_id, :parent_id, :date_inscription, :statut)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $this->nom,
            ':prenom' => $this->prenom,
            ':date_naissance' => $this->date_naissance,
            ':sexe' => $this->sexe,
            ':photo' => $this->photo ?: null,
            ':groupe_id' => (!empty($this->groupe_id)) ? (int)$this->groupe_id : null,
            ':parent_id' => (!empty($this->parent_id)) ? (int)$this->parent_id : null,
            ':date_inscription' => $this->date_inscription,
            ':statut' => $this->statut
        ]);
        return $db->lastInsertId();
    }

    // READ ALL
    public function afficher() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM enfant ORDER BY id DESC";
        $stmt = $db->query($sql);
        return $stmt->fetchAll();
    }

    // READ ONE
    public function afficherParId($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM enfant WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    // UPDATE
    public function modifier($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE enfant SET nom = :nom, prenom = :prenom, date_naissance = :date_naissance,
                sexe = :sexe, photo = :photo, groupe_id = :groupe_id, parent_id = :parent_id,
                date_inscription = :date_inscription, statut = :statut
                WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([
            ':id' => $id,
            ':nom' => $this->nom,
            ':prenom' => $this->prenom,
            ':date_naissance' => $this->date_naissance,
            ':sexe' => $this->sexe,
            ':photo' => $this->photo ?: null,
            ':groupe_id' => (!empty($this->groupe_id)) ? (int)$this->groupe_id : null,
            ':parent_id' => (!empty($this->parent_id)) ? (int)$this->parent_id : null,
            ':date_inscription' => $this->date_inscription,
            ':statut' => $this->statut
        ]);
    }

    // DELETE
    public function supprimer($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "DELETE FROM enfant WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // ARCHIVE (soft delete)
    public function archiver($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE enfant SET statut = 'archive' WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // READ by parent_id (FrontOffice — parent sees only his children)
    public function afficherParParent($parent_id) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM enfant WHERE parent_id = :parent_id ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':parent_id' => $parent_id]);
        return $stmt->fetchAll();
    }

    // SEARCH
    public function rechercher($keyword) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM enfant WHERE nom LIKE :kw OR prenom LIKE :kw ORDER BY id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute([':kw' => "%$keyword%"]);
        return $stmt->fetchAll();
    }

    // COUNT
    public function compter() {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as total FROM enfant WHERE statut = 'actif'";
        $stmt = $db->query($sql);
        return $stmt->fetch()['total'];
    }
}

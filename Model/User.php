<?php
require_once __DIR__ . '/../config/db.php';

class User {
    private $id;
    private $nom;
    private $prenom;
    private $email;
    private $mot_de_passe;
    private $role;
    private $telephone;
    private $photo;
    private $statut;

    public function __construct($nom = "", $prenom = "", $email = "", $mot_de_passe = "", $role = "parent", $telephone = "", $photo = "", $statut = "actif") {
        $this->nom = $nom;
        $this->prenom = $prenom;
        $this->email = $email;
        $this->mot_de_passe = $mot_de_passe;
        $this->role = $role;
        $this->telephone = $telephone;
        $this->photo = $photo;
        $this->statut = $statut;
    }

    // Getters
    public function getId() { return $this->id; }
    public function getNom() { return $this->nom; }
    public function getPrenom() { return $this->prenom; }
    public function getEmail() { return $this->email; }
    public function getRole() { return $this->role; }
    public function getTelephone() { return $this->telephone; }
    public function getStatut() { return $this->statut; }

    // Setters
    public function setId($id) { $this->id = $id; }
    public function setNom($nom) { $this->nom = $nom; }
    public function setPrenom($prenom) { $this->prenom = $prenom; }
    public function setEmail($email) { $this->email = $email; }
    public function setMotDePasse($mot_de_passe) { $this->mot_de_passe = $mot_de_passe; }
    public function setRole($role) { $this->role = $role; }
    public function setTelephone($telephone) { $this->telephone = $telephone; }
    public function setStatut($statut) { $this->statut = $statut; }

    // CREATE (inscription)
    public function inscrire() {
        $db = Database::getInstance()->getConnection();
        $sql = "INSERT INTO user (nom, prenom, email, mot_de_passe, role, telephone, statut)
                VALUES (:nom, :prenom, :email, :mot_de_passe, :role, :telephone, :statut)";
        $stmt = $db->prepare($sql);
        $stmt->execute([
            ':nom' => $this->nom,
            ':prenom' => $this->prenom,
            ':email' => $this->email,
            ':mot_de_passe' => password_hash($this->mot_de_passe, PASSWORD_BCRYPT),
            ':role' => $this->role,
            ':telephone' => $this->telephone ?: null,
            ':statut' => $this->statut
        ]);
        return $db->lastInsertId();
    }

    // LOGIN
    public function login($email, $mot_de_passe) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM user WHERE email = :email AND statut = 'actif'";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            return $user;
        }
        return false;
    }

    // Check email exists
    public function emailExiste($email) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT COUNT(*) as total FROM user WHERE email = :email";
        $stmt = $db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return $stmt->fetch()['total'] > 0;
    }

    // Get by ID
    public function afficherParId($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "SELECT * FROM user WHERE id = :id";
        $stmt = $db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}

<?php
require_once __DIR__ . '/../config/db.php';

class Activite {
    private ?int $id_activite;
    private ?string $nom_activite;
    private ?string $description;
    private ?string $date_activite;
    private ?string $heure_activite;
    private ?int $id_educateur;

    public function __construct(?int $id = null, ?string $nom_activite = null, ?string $description = null, ?string $date_activite = null, ?string $heure_activite = null, ?int $id_educateur = null) {
        $this->id_activite = $id;
        $this->nom_activite = $nom_activite;
        $this->description = $description;
        $this->date_activite = $date_activite;
        $this->heure_activite = $heure_activite;
        $this->id_educateur = $id_educateur;
    }

    public function getIdActivite() { return $this->id_activite; }
    public function getNomActivite() { return $this->nom_activite; }
    public function getDescription() { return $this->description; }
    public function getDateActivite() { return $this->date_activite; }
    public function getHeureActivite() { return $this->heure_activite; }
    public function getIdEducateur() { return $this->id_educateur; }

    // ======== CRUD ========

    public function lister(): array {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT * FROM activite ORDER BY date_activite DESC")->fetchAll();
    }

    public function listerParEducateur($idEducateur): array {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM activite WHERE id_educateur = :id ORDER BY date_activite DESC");
        $stmt->execute([':id' => $idEducateur]);
        return $stmt->fetchAll();
    }

    public function afficherParId($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM activite WHERE id_activite = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function ajouter(): int {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO activite (nom_activite, description, date_activite, heure_activite, id_educateur)
            VALUES (:nom, :desc, :date, :heure, :ed)
        ");
        $stmt->execute([
            ':nom'   => $this->nom_activite,
            ':desc'  => $this->description,
            ':date'  => $this->date_activite,
            ':heure' => $this->heure_activite,
            ':ed'    => $this->id_educateur,
        ]);
        return (int)$db->lastInsertId();
    }

    public function modifier($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE activite SET
                nom_activite   = :nom,
                description    = :desc,
                date_activite  = :date,
                heure_activite = :heure,
                id_educateur   = :ed
            WHERE id_activite = :id
        ");
        return $stmt->execute([
            ':id'    => $id,
            ':nom'   => $this->nom_activite,
            ':desc'  => $this->description,
            ':date'  => $this->date_activite,
            ':heure' => $this->heure_activite,
            ':ed'    => $this->id_educateur,
        ]);
    }

    public function supprimer($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM activite WHERE id_activite = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ======== Statistiques & Expertise ========

    public function statistiquesParEducateur(): array {
        $db = Database::getInstance()->getConnection();
        $sql = "
            SELECT a.id_educateur,
                   CONCAT_WS(' ', u.prenom, u.nom) AS educateur_nom,
                   COUNT(*) AS total_activites
            FROM activite a
            LEFT JOIN user u ON u.id = a.id_educateur AND u.role = 'educateur'
            GROUP BY a.id_educateur, u.prenom, u.nom
            ORDER BY total_activites DESC
        ";
        return $db->query($sql)->fetchAll();
    }

    public function expertiseParEducateur(): array {
        $db = Database::getInstance()->getConnection();
        $sql = "
            SELECT a.id_educateur,
                   CONCAT_WS(' ', u.prenom, u.nom) AS educateur_nom,
                   a.nom_activite,
                   COUNT(*) AS total
            FROM activite a
            LEFT JOIN user u ON u.id = a.id_educateur AND u.role = 'educateur'
            GROUP BY a.id_educateur, u.prenom, u.nom, a.nom_activite
            ORDER BY a.id_educateur ASC, total DESC
        ";
        return $db->query($sql)->fetchAll();
    }
}

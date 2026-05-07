<?php
require_once __DIR__ . '/../config/db.php';

class Rapport {
    private ?int $id_rapport;
    private ?string $contenu_rapport;
    private ?string $date_rapport;
    private ?int $id_activite;
    private ?int $id_educateur;

    public function __construct(?int $id = null, ?string $contenu_rapport = null, ?string $date_rapport = null, ?int $id_activite = null, ?int $id_educateur = null) {
        $this->id_rapport = $id;
        $this->contenu_rapport = $contenu_rapport;
        $this->date_rapport = $date_rapport;
        $this->id_activite = $id_activite;
        $this->id_educateur = $id_educateur;
    }

    public function getIdRapport() { return $this->id_rapport; }
    public function getContenuRapport() { return $this->contenu_rapport; }
    public function getDateRapport() { return $this->date_rapport; }
    public function getIdActivite() { return $this->id_activite; }
    public function getIdEducateur() { return $this->id_educateur; }

    // ======== CRUD ========

    public function lister(): array {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT * FROM rapport ORDER BY date_rapport DESC")->fetchAll();
    }

    public function listerAvecActivite(string $tri = 'desc'): array {
        $ordre = strtolower($tri) === 'asc' ? 'ASC' : 'DESC';
        $db = Database::getInstance()->getConnection();
        return $db->query("
            SELECT r.*, a.nom_activite
            FROM rapport r
            INNER JOIN activite a ON r.id_activite = a.id_activite
            ORDER BY r.date_rapport $ordre
        ")->fetchAll();
    }

    public function afficherParId($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM rapport WHERE id_rapport = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function ajouter(): int {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO rapport (contenu_rapport, date_rapport, id_activite, id_educateur)
            VALUES (:contenu, :date, :act, :ed)
        ");
        $stmt->execute([
            ':contenu' => $this->contenu_rapport,
            ':date'    => $this->date_rapport,
            ':act'     => $this->id_activite,
            ':ed'      => $this->id_educateur,
        ]);
        return (int)$db->lastInsertId();
    }

    public function modifier($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE rapport SET
                contenu_rapport = :contenu,
                date_rapport    = :date,
                id_activite     = :act,
                id_educateur    = :ed
            WHERE id_rapport = :id
        ");
        return $stmt->execute([
            ':id'      => $id,
            ':contenu' => $this->contenu_rapport,
            ':date'    => $this->date_rapport,
            ':act'     => $this->id_activite,
            ':ed'      => $this->id_educateur,
        ]);
    }

    public function supprimer($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM rapport WHERE id_rapport = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function listerPourParent($parentId, string $tri = 'desc'): array {
        $ordre = strtolower($tri) === 'asc' ? 'ASC' : 'DESC';
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT r.*, a.nom_activite
            FROM rapport r
            INNER JOIN activite a ON r.id_activite = a.id_activite
            WHERE r.id_educateur IN (
                SELECT DISTINCT g.educateur_id
                FROM enfant e
                JOIN groupe g ON e.groupe_id = g.id
                WHERE e.parent_id = :pid AND e.statut = 'actif'
            )
            ORDER BY r.date_rapport $ordre
        ");
        $stmt->execute([':pid' => $parentId]);
        return $stmt->fetchAll();
    }

    public function afficherAvecActivite($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT r.*, a.nom_activite
            FROM rapport r
            INNER JOIN activite a ON r.id_activite = a.id_activite
            WHERE r.id_rapport = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }
}

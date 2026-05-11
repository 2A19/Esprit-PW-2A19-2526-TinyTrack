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

    // ACTIVATE (reactivate after archive)
    public function activer($id) {
        $db = Database::getInstance()->getConnection();
        $sql = "UPDATE enfant SET statut = 'actif' WHERE id = :id";
        $stmt = $db->prepare($sql);
        return $stmt->execute([':id' => $id]);
    }

    // Enrich each enfant row with its groupe + educateur (avoids N+1 in view).
    public function enrichirAvecGroupeEtEducateur(array $enfants): array {
        if (empty($enfants)) return [];
        $db = Database::getInstance()->getConnection();

        $ids = array_filter(array_column($enfants, 'groupe_id'));
        if (empty($ids)) {
            foreach ($enfants as &$e) { $e['groupe'] = null; $e['educateur'] = null; }
            return $enfants;
        }

        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $stmt = $db->prepare("
            SELECT g.id, g.nom, g.niveau, g.educateur_id,
                   u.prenom AS edu_prenom, u.nom AS edu_nom, u.telephone AS edu_telephone
            FROM groupe g
            LEFT JOIN user u ON u.id = g.educateur_id AND u.role = 'educateur'
            WHERE g.id IN ($placeholders)
        ");
        $stmt->execute(array_values($ids));
        $rows = $stmt->fetchAll();

        $byGroupId = [];
        foreach ($rows as $r) {
            $byGroupId[$r['id']] = [
                'groupe' => ['id' => $r['id'], 'nom' => $r['nom'], 'niveau' => $r['niveau']],
                'educateur' => $r['edu_prenom'] ? [
                    'prenom'    => $r['edu_prenom'],
                    'nom'       => $r['edu_nom'],
                    'telephone' => $r['edu_telephone'],
                ] : null,
            ];
        }

        foreach ($enfants as &$e) {
            $gid = $e['groupe_id'] ?? null;
            $e['groupe']    = $gid && isset($byGroupId[$gid]) ? $byGroupId[$gid]['groupe']    : null;
            $e['educateur'] = $gid && isset($byGroupId[$gid]) ? $byGroupId[$gid]['educateur'] : null;
        }
        return $enfants;
    }

    // FILTERED LIST with sorting (replaces controller-side SQL)
    public function listerFiltered(array $filters = [], string $sortBy = 'date_inscription', string $sortDir = 'desc'): array {
        $db = Database::getInstance()->getConnection();

        $validSort = [
            'nom'              => 'e.nom',
            'prenom'           => 'e.prenom',
            'age'              => 'e.date_naissance',
            'date_inscription' => 'e.date_inscription',
        ];
        $sortCol = $validSort[$sortBy] ?? 'e.date_inscription';
        $sortDir = strtolower($sortDir) === 'asc' ? 'ASC' : 'DESC';
        if ($sortBy === 'age') {
            $sortDir = ($sortDir === 'ASC') ? 'DESC' : 'ASC';
        }

        $where = [];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(e.nom LIKE :q1 OR e.prenom LIKE :q2 OR e.code_unique LIKE :q3)";
            $kw = '%' . $filters['q'] . '%';
            $params[':q1'] = $kw;
            $params[':q2'] = $kw;
            $params[':q3'] = $kw;
        }
        if (!empty($filters['sexe']) && in_array($filters['sexe'], ['M', 'F'])) {
            $where[] = "e.sexe = :sexe";
            $params[':sexe'] = $filters['sexe'];
        }
        if (!empty($filters['statut']) && in_array($filters['statut'], ['actif', 'archive'])) {
            $where[] = "e.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }
        if (!empty($filters['niveau']) && in_array($filters['niveau'], ['petit', 'moyen', 'grand'])) {
            $where[] = "g.niveau = :niveau";
            $params[':niveau'] = $filters['niveau'];
        }
        if (!empty($filters['parent_id'])) {
            $where[] = "e.parent_id = :pid";
            $params[':pid'] = (int)$filters['parent_id'];
        }
        if (!empty($filters['educateur_id'])) {
            $where[] = "g.educateur_id = :eid";
            $params[':eid'] = (int)$filters['educateur_id'];
        }

        $sql = "SELECT e.* FROM enfant e LEFT JOIN groupe g ON e.groupe_id = g.id";
        if (!empty($where)) $sql .= " WHERE " . implode(' AND ', $where);
        $sql .= " ORDER BY $sortCol $sortDir";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
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

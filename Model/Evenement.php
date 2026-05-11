<?php
require_once __DIR__ . '/../config/db.php';

class Evenement {
    private ?int $id;
    private ?string $titre;
    private ?string $description;
    private ?DateTime $date;
    private ?string $heure_debut;
    private ?string $heure_fin;
    private ?string $type;
    private ?string $lieu;
    private ?int $capacite_max;
    private ?float $prix;
    private ?int $groupe_id;
    private ?string $statut;
    private ?string $avis;

    // Constructor
public function __construct(
    ?int $id = null,
    ?string $titre = null,
    ?string $description = null,
    ?DateTime $date = null,
    ?string $heure_debut = null,
    ?string $heure_fin = null,
    ?string $type = null,
    ?string $lieu = null,
    ?int $capacite_max = null,
    ?float $prix = null,
    ?int $groupe_id = null,
    ?string $statut = null,
    ?string $avis = null
) {
        $this->id          = $id;
        $this->titre       = $titre;
        $this->description = $description;
        $this->date        = $date;
        $this->heure_debut = $heure_debut;
        $this->heure_fin   = $heure_fin;
        $this->type        = $type;
        $this->lieu        = $lieu;
        $this->capacite_max = $capacite_max;
        $this->prix        = $prix;
        $this->groupe_id   = $groupe_id;
        $this->statut      = $statut;
        $this->avis        = $avis;
    }

    public function show(): void {
        echo "<table border='1' cellpadding='5'>";
        echo "<tr>
                <th>ID</th><th>Titre</th><th>Description</th><th>Date</th>
                <th>Heure Début</th><th>Heure Fin</th><th>Type</th><th>Lieu</th>
                <th>Capacité Max</th><th>Prix</th><th>Groupe ID</th><th>Statut</th>
              </tr>";
        echo "<tr>";
        echo "<td>{$this->id}</td>";
        echo "<td>{$this->titre}</td>";
        echo "<td>{$this->description}</td>";
        echo "<td>" . ($this->date ? $this->date->format('Y-m-d') : '') . "</td>";
        echo "<td>{$this->heure_debut}</td>";
        echo "<td>{$this->heure_fin}</td>";
        echo "<td>{$this->type}</td>";
        echo "<td>{$this->lieu}</td>";
        echo "<td>{$this->capacite_max}</td>";
        echo "<td>{$this->prix}</td>";
        echo "<td>{$this->groupe_id}</td>";
        echo "<td>{$this->statut}</td>";
        echo "</tr>";
        echo "</table>";
    }

    // Getters and Setters
    public function getId(): ?int { return $this->id; }
    public function setId(?int $id): void { $this->id = $id; }

    public function getTitre(): ?string { return $this->titre; }
    public function setTitre(?string $titre): void { $this->titre = $titre; }

    public function getDescription(): ?string { return $this->description; }
    public function setDescription(?string $description): void { $this->description = $description; }

    public function getDate(): ?DateTime { return $this->date; }
    public function setDate(?DateTime $date): void { $this->date = $date; }

    public function getHeureDebut(): ?string { return $this->heure_debut; }
    public function setHeureDebut(?string $heure_debut): void { $this->heure_debut = $heure_debut; }

    public function getHeureFin(): ?string { return $this->heure_fin; }
    public function setHeureFin(?string $heure_fin): void { $this->heure_fin = $heure_fin; }

    public function getType(): ?string { return $this->type; }
    public function setType(?string $type): void { $this->type = $type; }

    public function getLieu(): ?string { return $this->lieu; }
    public function setLieu(?string $lieu): void { $this->lieu = $lieu; }

    public function getCapaciteMax(): ?int { return $this->capacite_max; }
    public function setCapaciteMax(?int $capacite_max): void { $this->capacite_max = $capacite_max; }

    public function getPrix(): ?float { return $this->prix; }
    public function setPrix(?float $prix): void { $this->prix = $prix; }

    public function getGroupeId(): ?int { return $this->groupe_id; }
    public function setGroupeId(?int $groupe_id): void { $this->groupe_id = $groupe_id; }

    public function getStatut(): ?string { return $this->statut; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }

    public function getAvis(): ?string { return $this->avis; }
    public function setAvis(?string $avis): void { $this->avis = $avis; }

    /** Retourne avis sous forme lisible : "Aucun" ou liste textuelle. */
    public function getAvisFormatted(): string {
        if ($this->avis === null || $this->avis === '[]' || $this->avis === '') return 'Aucun';
        $decoded = json_decode($this->avis, true);
        if (is_array($decoded) && !empty($decoded)) {
            $parts = [];
            foreach ($decoded as $a) {
                if (is_array($a) && isset($a['note'])) {
                    $parts[] = ($a['note'] ?? '?') . '★ — ' . ($a['commentaire'] ?? '');
                } elseif (is_string($a)) {
                    $parts[] = $a;
                }
            }
            return implode(' | ', $parts);
        }
        return $this->avis;
    }

    /** Format avis pour stockage : '[]' si vide/null, sinon JSON valide. */
    private function normalizeAvis(): string {
        if ($this->avis === null || trim($this->avis) === '') return '[]';
        json_decode($this->avis);
        if (json_last_error() === JSON_ERROR_NONE) return $this->avis;
        return json_encode([trim($this->avis)]);
    }

    // ======== CRUD ========

    public function lister(): array {
        $db = Database::getInstance()->getConnection();
        return $db->query("SELECT * FROM evenement ORDER BY date DESC")->fetchAll();
    }

    public function afficherParId($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM evenement WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function ajouter(): int {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO evenement (titre, description, date, heure_debut, heure_fin, type, lieu, capacite_max, prix, groupe_id, statut, avis)
            VALUES (:titre, :description, :date, :heure_debut, :heure_fin, :type, :lieu, :capacite_max, :prix, :groupe_id, :statut, :avis)
        ");
        $stmt->execute([
            ':titre'        => $this->titre,
            ':description'  => $this->description,
            ':date'         => $this->date ? $this->date->format('Y-m-d') : null,
            ':heure_debut'  => $this->heure_debut,
            ':heure_fin'    => $this->heure_fin,
            ':type'         => $this->type,
            ':lieu'         => $this->lieu,
            ':capacite_max' => $this->capacite_max,
            ':prix'         => $this->prix,
            ':groupe_id'    => $this->groupe_id,
            ':statut'       => $this->statut,
            ':avis'         => $this->normalizeAvis(),
        ]);
        return (int)$db->lastInsertId();
    }

    public function modifier($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE evenement SET
                titre        = :titre,
                description  = :description,
                date         = :date,
                heure_debut  = :heure_debut,
                heure_fin    = :heure_fin,
                type         = :type,
                lieu         = :lieu,
                capacite_max = :capacite_max,
                prix         = :prix,
                groupe_id    = :groupe_id,
                statut       = :statut,
                avis         = :avis
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'           => $id,
            ':titre'        => $this->titre,
            ':description'  => $this->description,
            ':date'         => $this->date ? $this->date->format('Y-m-d') : null,
            ':heure_debut'  => $this->heure_debut,
            ':heure_fin'    => $this->heure_fin,
            ':type'         => $this->type,
            ':lieu'         => $this->lieu,
            ':capacite_max' => $this->capacite_max,
            ':prix'         => $this->prix,
            ':groupe_id'    => $this->groupe_id,
            ':statut'       => $this->statut,
            ':avis'         => $this->normalizeAvis(),
        ]);
    }

    public function supprimer($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM evenement WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    /** Met à jour uniquement le champ avis (utilisé par submitAvis). */
    public function mettreAJourAvis($id, string $avisJson): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE evenement SET avis = :avis WHERE id = :id");
        return $stmt->execute([':avis' => $avisJson, ':id' => $id]);
    }

    /** Met à jour uniquement le statut (utilisé pour marquer 'complet'). */
    public function mettreAJourStatut($id, string $statut): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE evenement SET statut = :statut WHERE id = :id");
        return $stmt->execute([':statut' => $statut, ':id' => $id]);
    }
}
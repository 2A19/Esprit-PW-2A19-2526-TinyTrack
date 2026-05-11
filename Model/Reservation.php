<?php
require_once __DIR__ . '/../config/db.php';

class Reservation {
    private ?int $id;
    private ?int $evenement_id;
    private ?int $enfant_id;
    private ?int $parent_id;
    private ?int $nb_accompagnants;
    private ?string $commentaire;
    private ?DateTime $date_reservation;
    private ?string $statut;
    private ?string $paiement;

    public function __construct(
        ?int $id = null,
        ?int $evenement_id = null,
        ?int $enfant_id = null,
        ?int $parent_id = null,
        ?int $nb_accompagnants = 0,
        ?string $commentaire = null,
        ?DateTime $date_reservation = null,
        ?string $statut = 'en_attente',
        ?string $paiement = 'non_paye'
    ) {
        $this->id = $id;
        $this->evenement_id = $evenement_id;
        $this->enfant_id = $enfant_id;
        $this->parent_id = $parent_id;
        $this->nb_accompagnants = $nb_accompagnants;
        $this->commentaire = $commentaire;
        $this->date_reservation = $date_reservation ?? new DateTime();
        $this->statut = $statut;
        $this->paiement = $paiement;
    }

    public function getId(): ?int { return $this->id; }
    public function getEvenementId(): ?int { return $this->evenement_id; }
    public function getEnfantId(): ?int { return $this->enfant_id; }
    public function getParentId(): ?int { return $this->parent_id; }
    public function getNbAccompagnants(): ?int { return $this->nb_accompagnants; }
    public function getCommentaire(): ?string { return $this->commentaire; }
    public function getDateReservation(): ?DateTime { return $this->date_reservation; }
    public function getStatut(): ?string { return $this->statut; }
    public function getPaiement(): ?string { return $this->paiement; }

    public function setId(?int $id): void { $this->id = $id; }
    public function setEvenementId(?int $evenement_id): void { $this->evenement_id = $evenement_id; }
    public function setEnfantId(?int $enfant_id): void { $this->enfant_id = $enfant_id; }
    public function setParentId(?int $parent_id): void { $this->parent_id = $parent_id; }
    public function setNbAccompagnants(?int $nb_accompagnants): void { $this->nb_accompagnants = $nb_accompagnants; }
    public function setCommentaire(?string $commentaire): void { $this->commentaire = $commentaire; }
    public function setDateReservation(?DateTime $date_reservation): void { $this->date_reservation = $date_reservation; }
    public function setStatut(?string $statut): void { $this->statut = $statut; }
    public function setPaiement(?string $paiement): void { $this->paiement = $paiement; }

    // ======== CRUD ========

    public function lister(): array {
        $db = Database::getInstance()->getConnection();
        return $db->query("
            SELECT r.*, e.titre AS evenement_titre
            FROM reservation r
            LEFT JOIN evenement e ON r.evenement_id = e.id
            ORDER BY r.date_reservation DESC
        ")->fetchAll();
    }

    public function afficherParId($id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT r.*, e.titre AS evenement_titre
            FROM reservation r
            LEFT JOIN evenement e ON r.evenement_id = e.id
            WHERE r.id = :id
        ");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    }

    public function listerParEvenement($evenement_id): array {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM reservation WHERE evenement_id = :eid ORDER BY date_reservation DESC");
        $stmt->execute([':eid' => $evenement_id]);
        return $stmt->fetchAll();
    }

    public function ajouter(): int {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            INSERT INTO reservation (evenement_id, enfant_id, parent_id, nb_accompagnants, commentaire, date_reservation, statut, paiement)
            VALUES (:evenement_id, :enfant_id, :parent_id, :nb, :com, :date, :statut, :paiement)
        ");
        $stmt->execute([
            ':evenement_id' => $this->evenement_id,
            ':enfant_id'    => $this->enfant_id,
            ':parent_id'    => $this->parent_id,
            ':nb'           => $this->nb_accompagnants,
            ':com'          => $this->commentaire,
            ':date'         => $this->date_reservation->format('Y-m-d H:i:s'),
            ':statut'       => $this->statut,
            ':paiement'     => $this->paiement,
        ]);
        return (int)$db->lastInsertId();
    }

    public function modifier($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            UPDATE reservation SET
                evenement_id     = :evenement_id,
                enfant_id        = :enfant_id,
                parent_id        = :parent_id,
                nb_accompagnants = :nb,
                commentaire      = :com,
                statut           = :statut,
                paiement         = :paiement
            WHERE id = :id
        ");
        return $stmt->execute([
            ':id'           => $id,
            ':evenement_id' => $this->evenement_id,
            ':enfant_id'    => $this->enfant_id,
            ':parent_id'    => $this->parent_id,
            ':nb'           => $this->nb_accompagnants,
            ':com'          => $this->commentaire,
            ':statut'       => $this->statut,
            ':paiement'     => $this->paiement,
        ]);
    }

    public function supprimer($id): bool {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM reservation WHERE id = :id");
        return $stmt->execute([':id' => $id]);
    }

    public function compterParEvenement($evenement_id): int {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM reservation WHERE evenement_id = :eid AND statut != 'annulee'");
        $stmt->execute([':eid' => $evenement_id]);
        return (int)$stmt->fetchColumn();
    }
}

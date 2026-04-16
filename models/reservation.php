<?php
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
}

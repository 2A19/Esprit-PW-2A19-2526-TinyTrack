<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
class Reponse {
    private ?int $id;
    private ?int $id_reclamation;
    private ?string $message;
    private ?string $auteur;
    private ?string $date_reponse;

    public function __construct($id = null, $id_reclamation = null, $message = null, $auteur = null, $date_reponse = null) {
        $this->id = $id;
        $this->id_reclamation = $id_reclamation;
        $this->message = $message;
        $this->auteur = $auteur;
        $this->date_reponse = $date_reponse;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getIdReclamation(): ?int { return $this->id_reclamation; }
    public function getMessage(): ?string { return $this->message; }
    public function getAuteur(): ?string { return $this->auteur; }
    public function getDateReponse(): ?string { return $this->date_reponse; }

    // Setters
    public function setIdReclamation(int $id_reclamation): void { $this->id_reclamation = $id_reclamation; }
    public function setMessage(string $message): void { $this->message = $message; }
    public function setAuteur(string $auteur): void { $this->auteur = $auteur; }
}
?>

<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
class Reclamation {
    private ?int    $id;
    private ?string $nom_client;
    private ?string $email;
    private ?string $sujet;
    private ?string $description;
    private ?string $statut;
    private ?string $date_creation;
    private ?string $sentiment;

    public function __construct($id = null, $nom_client = null, $email = null, $sujet = null, $description = null, $statut = null, $date_creation = null, $sentiment = null) {
        $this->id            = $id;
        $this->nom_client    = $nom_client;
        $this->email         = $email;
        $this->sujet         = $sujet;
        $this->description   = $description;
        $this->statut        = $statut;
        $this->date_creation = $date_creation;
        $this->sentiment     = $sentiment;
    }

    public function getId(): ?int            { return $this->id; }
    public function getNomClient(): ?string  { return $this->nom_client; }
    public function getEmail(): ?string      { return $this->email; }
    public function getSujet(): ?string      { return $this->sujet; }
    public function getDescription(): ?string{ return $this->description; }
    public function getStatut(): ?string     { return $this->statut; }
    public function getDateCreation(): ?string{ return $this->date_creation; }
    public function getSentiment(): ?string  { return $this->sentiment; }

    public function setNomClient(string $v): void   { $this->nom_client  = $v; }
    public function setEmail(string $v): void        { $this->email       = $v; }
    public function setSujet(string $v): void        { $this->sujet       = $v; }
    public function setDescription(string $v): void  { $this->description = $v; }
    public function setStatut(string $v): void       { $this->statut      = $v; }
    public function setSentiment(?string $v): void   { $this->sentiment   = $v; }
}

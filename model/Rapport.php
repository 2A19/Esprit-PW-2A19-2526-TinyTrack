<?php
class Rapport {
    private ?int $id_rapport;
    private ?string $contenu_rapport;
    private ?string $date_rapport;
    private ?int $id_enfant;
    private ?int $id_activite;
    private ?int $id_educateur;

    public function __construct($id = null, $contenu_rapport, $date_rapport, $id_enfant, $id_activite, $id_educateur) {
        $this->id_rapport = $id;
        $this->contenu_rapport = $contenu_rapport;
        $this->date_rapport = $date_rapport;
        $this->id_enfant = $id_enfant;
        $this->id_activite = $id_activite;
        $this->id_educateur = $id_educateur;
    }

    public function getIdRapport() {
        return $this->id_rapport;
    }

    public function getContenuRapport() {
        return $this->contenu_rapport;
    }

    public function getDateRapport() {
        return $this->date_rapport;
    }

    public function getIdEnfant() {
        return $this->id_enfant;
    }

    public function getIdActivite() {
        return $this->id_activite;
    }

    public function getIdEducateur() {
        return $this->id_educateur;
    }
}
?>
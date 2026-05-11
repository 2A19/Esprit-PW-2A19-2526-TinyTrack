<?php
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
class Activite {
    private ?int $id_activite;
    private ?string $nom_activite;
    private ?string $description;
    private ?string $date_activite;
    private ?string $heure_activite;
    private ?int $id_educateur;

    public function __construct($id = null, $nom_activite, $description, $date_activite, $heure_activite, $id_educateur) {
        $this->id_activite = $id;
        $this->nom_activite = $nom_activite;
        $this->description = $description;
        $this->date_activite = $date_activite;
        $this->heure_activite = $heure_activite;
        $this->id_educateur = $id_educateur;
    }

    public function getIdActivite() {
        return $this->id_activite;
    }

    public function getNomActivite() {
        return $this->nom_activite;
    }

    public function getDescription() {
        return $this->description;
    }

    public function getDateActivite() {
        return $this->date_activite;
    }

    public function getHeureActivite() {
        return $this->heure_activite;
    }

    public function getIdEducateur() {
        return $this->id_educateur;
    }
}
?>

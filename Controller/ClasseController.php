<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
require_once __DIR__ . '/../Model/Classe.php';

class ClasseController {
    private $classe;

    public function __construct($pdo) {
        $this->classe = new Classe($pdo);
    }

    public function sauvegarder($nom, $couleur, $description, $age_minimum, $age_maximum, $capacite_max, $educateur_principal, $salle, $horaires) {
        // Validation côté serveur
        if (empty($nom) || empty($couleur)) {
            return ['succes' => false, 'message' => 'Nom et couleur obligatoires'];
        }

        $ok = $this->classe->ajouter($nom, $couleur, $description, $age_minimum, $age_maximum, $capacite_max, $educateur_principal, $salle, $horaires);

        return $ok
            ? ['succes' => true,  'message' => 'Classe créée avec succès!']
            : ['succes' => false, 'message' => " Cette couleur est déjà utilisée"];
    }

    public function modifier($id, $nom, $couleur, $description, $age_minimum, $age_maximum, $capacite_max, $educateur_principal, $salle, $horaires) {
        if (empty($nom) || empty($couleur)) {
            return ['succes' => false, 'message' => 'Nom et couleur obligatoires'];
        }

        $ok = $this->classe->modifier($id, $nom, $couleur, $description, $age_minimum, $age_maximum, $capacite_max, $educateur_principal, $salle, $horaires);

        return $ok
            ? ['succes' => true,  'message' => 'Classe modifiée avec succès!']
            : ['succes' => false, 'message' => " Erreur lors de la modification"];
    }

    public function supprimer($id) {
        $ok = $this->classe->supprimer($id);
        
        if ($ok === false) {
            return ['succes' => false, 'message' => " Impossible de supprimer une classe contenant des enfants"];
        }
        
        return $ok
            ? ['succes' => true,  'message' => 'Classe supprimée']
            : ['succes' => false, 'message' => " Erreur lors de la suppression"];
    }

    public function obtenirTous() {
        return $this->classe->obtenirTous();
    }

    public function obtenirParId($id) {
        return $this->classe->obtenirParId($id);
    }

    public function obtenirAvecEnfants($id) {
        return $this->classe->obtenirAvecEnfants($id);
    }
}
?>

<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
require_once __DIR__ . '/../Model/InscriptionEnfant.php';

class InscriptionController {
    private $enfant;

    public function __construct($pdo) {
        $this->enfant = new InscriptionEnfant($pdo);
    }

    public function sauvegarder($nom, $prenom, $date_naissance, $classe_id, $groupe_sanguin,
        $allergies_alimentaires, $allergies_medicales, $maladies_chroniques, $vaccinations,
        $contact_urgence_nom, $contact_urgence_lien, $contact_urgence_telephone,
        $aliments_preferes, $aliments_interdits, $horaire_sieste,
        $autorise_photos, $autorise_sorties, $notes_sante, $notes_speciales) {

        // Validation côté serveur (sécurité minimale)
        if (empty($nom) || empty($prenom) || empty($date_naissance) || empty($classe_id)) {
            return ['succes' => false, 'message' => 'Champs obligatoires manquants'];
        }

        $ok = $this->enfant->ajouter(
            $nom, $prenom, $date_naissance, $classe_id, $groupe_sanguin,
            $allergies_alimentaires, $allergies_medicales, $maladies_chroniques, $vaccinations,
            $contact_urgence_nom, $contact_urgence_lien, $contact_urgence_telephone,
            $aliments_preferes, $aliments_interdits, $horaire_sieste,
            $autorise_photos, $autorise_sorties, $notes_sante, $notes_speciales
        );

        return $ok
            ? ['succes' => true,  'message' => 'Enfant inscrit avec succès!']
            : ['succes' => false, 'message' => " Cet enfant est déjà inscrit"];
    }

    public function obtenirTous() {
        return $this->enfant->obtenirTous();
    }

    public function supprimer($id) {
        if (empty($id) || !is_numeric($id)) {
            return ['succes' => false, 'message' => 'ID invalide'];
        }
        $ok = $this->enfant->supprimer($id);
        return $ok
            ? ['succes' => true,  'message' => 'Enfant supprimé']
            : ['succes' => false, 'message' => 'Erreur lors de la suppression'];
    }
}
?>

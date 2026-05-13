<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
class InscriptionEnfant {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function ajouter($nom, $prenom, $date_naissance, $classe_id, $groupe_sanguin,
        $allergies_alimentaires, $allergies_medicales, $maladies_chroniques, $vaccinations,
        $contact_urgence_nom, $contact_urgence_lien, $contact_urgence_telephone,
        $aliments_preferes, $aliments_interdits, $horaire_sieste,
        $autorise_photos, $autorise_sorties, $notes_sante, $notes_speciales) {

        // Vérifier si l'enfant existe déjà (même nom, prénom et date de naissance)
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM enfants WHERE enfant_nom = :nom AND enfant_prenom = :prenom AND enfant_date_naissance = :date_naissance");
        $stmt->execute([
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':date_naissance' => $date_naissance
        ]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Enfant déjà inscrit
        }

        $sql = "INSERT INTO enfants (
            enfant_nom, enfant_prenom, enfant_date_naissance, classe_id,
            groupe_sanguin, allergies_alimentaires, allergies_medicales, maladies_chroniques,
            vaccinations, contact_urgence_nom, contact_urgence_lien, contact_urgence_telephone,
            aliments_preferes, aliments_interdits, horaire_sieste,
            autorise_photos, autorise_sorties, notes_sante, notes_speciales
        ) VALUES (
            :nom, :prenom, :date_naissance, :classe_id,
            :groupe_sanguin, :allergies_alimentaires, :allergies_medicales, :maladies_chroniques,
            :vaccinations, :contact_urgence_nom, :contact_urgence_lien, :contact_urgence_telephone,
            :aliments_preferes, :aliments_interdits, :horaire_sieste,
            :autorise_photos, :autorise_sorties, :notes_sante, :notes_speciales
        )";

        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom'                      => $nom,
            ':prenom'                   => $prenom,
            ':date_naissance'           => $date_naissance,
            ':classe_id'                => (int)$classe_id,
            ':groupe_sanguin'           => $groupe_sanguin,
            ':allergies_alimentaires'   => $allergies_alimentaires,
            ':allergies_medicales'      => $allergies_medicales,
            ':maladies_chroniques'      => $maladies_chroniques,
            ':vaccinations'             => $vaccinations,
            ':contact_urgence_nom'      => $contact_urgence_nom,
            ':contact_urgence_lien'     => $contact_urgence_lien,
            ':contact_urgence_telephone'=> $contact_urgence_telephone,
            ':aliments_preferes'        => $aliments_preferes,
            ':aliments_interdits'       => $aliments_interdits,
            ':horaire_sieste'           => $horaire_sieste,
            ':autorise_photos'          => (int)$autorise_photos,
            ':autorise_sorties'         => (int)$autorise_sorties,
            ':notes_sante'              => $notes_sante,
            ':notes_speciales'          => $notes_speciales,
        ]);
    }

    public function obtenirTous() {
        $sql = "SELECT e.*, c.nom as classe_nom, c.couleur as classe_couleur 
                FROM enfants e 
                JOIN classes c ON e.classe_id = c.id 
                ORDER BY e.date_inscription DESC";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenirParId($id) {
        $sql = "SELECT e.*, c.nom as classe_nom, c.couleur as classe_couleur 
                FROM enfants e 
                JOIN classes c ON e.classe_id = c.id 
                WHERE e.id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    public function supprimer($id) {
        $stmt = $this->pdo->prepare("DELETE FROM enfants WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }
}
?>

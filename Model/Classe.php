<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
class Classe {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function ajouter($nom, $couleur, $description, $age_minimum, $age_maximum, $capacite_max, $educateur_principal, $salle, $horaires) {
        // Vérifier si la couleur existe déjà
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM classes WHERE couleur = :couleur");
        $stmt->execute([':couleur' => $couleur]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Couleur déjà utilisée
        }
        
        $sql = "INSERT INTO classes (nom, couleur, description, age_minimum, age_maximum, capacite_max, educateur_principal, salle, horaires) 
                VALUES (:nom, :couleur, :description, :age_minimum, :age_maximum, :capacite_max, :educateur_principal, :salle, :horaires)";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':nom' => $nom,
            ':couleur' => $couleur,
            ':description' => $description,
            ':age_minimum' => (int)$age_minimum,
            ':age_maximum' => (int)$age_maximum,
            ':capacite_max' => (int)$capacite_max,
            ':educateur_principal' => $educateur_principal,
            ':salle' => $salle,
            ':horaires' => $horaires
        ]);
    }

    public function obtenirTous() {
        $sql = "SELECT c.*, 
                       COUNT(e.id) as nb_enfants,
                       ROUND(AVG(YEAR(CURDATE()) - YEAR(e.enfant_date_naissance)), 1) as age_moyen
                FROM classes c 
                LEFT JOIN enfants e ON c.id = e.classe_id 
                GROUP BY c.id 
                ORDER BY c.nom";
        $stmt = $this->pdo->query($sql);
        return $stmt->fetchAll();
    }

    public function obtenirParId($id) {
        $stmt = $this->pdo->prepare("SELECT * FROM classes WHERE id = :id");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    public function obtenirAvecEnfants($id) {
        // Détails de la classe
        $classe = $this->obtenirParId($id);
        if (!$classe) return null;

        // Enfants de cette classe
        $sql = "SELECT e.*, c.nom as classe_nom, c.couleur as classe_couleur 
                FROM enfants e 
                JOIN classes c ON e.classe_id = c.id 
                WHERE c.id = :id 
                ORDER BY e.enfant_nom, e.enfant_prenom";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => (int)$id]);
        $classe['enfants'] = $stmt->fetchAll();

        return $classe;
    }

    public function modifier($id, $nom, $couleur, $description, $age_minimum, $age_maximum, $capacite_max, $educateur_principal, $salle, $horaires) {
        $sql = "UPDATE classes SET 
                nom = :nom, couleur = :couleur, description = :description, 
                age_minimum = :age_minimum, age_maximum = :age_maximum, 
                capacite_max = :capacite_max, educateur_principal = :educateur_principal, 
                salle = :salle, horaires = :horaires 
                WHERE id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':id' => (int)$id,
            ':nom' => $nom,
            ':couleur' => $couleur,
            ':description' => $description,
            ':age_minimum' => (int)$age_minimum,
            ':age_maximum' => (int)$age_maximum,
            ':capacite_max' => (int)$capacite_max,
            ':educateur_principal' => $educateur_principal,
            ':salle' => $salle,
            ':horaires' => $horaires
        ]);
    }

    public function supprimer($id) {
        // Vérifier s'il y a des enfants dans cette classe
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM enfants WHERE classe_id = :id");
        $stmt->execute([':id' => (int)$id]);
        if ($stmt->fetchColumn() > 0) {
            return false; // Ne peut pas supprimer une classe avec des enfants
        }

        $stmt = $this->pdo->prepare("DELETE FROM classes WHERE id = :id");
        return $stmt->execute([':id' => (int)$id]);
    }

    public function obtenirPourSelect() {
        $stmt = $this->pdo->query("SELECT id, nom, couleur FROM classes ORDER BY nom");
        return $stmt->fetchAll();
    }
}
?>

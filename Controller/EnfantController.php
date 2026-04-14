<?php
require_once __DIR__ . '/../Model/Enfant.php';

class EnfantController {

    // List all enfants
    public function listerEnfants() {
        $enfant = new Enfant();
        return $enfant->afficher();
    }

    // Get one enfant
    public function getEnfant($id) {
        $enfant = new Enfant();
        return $enfant->afficherParId($id);
    }

    // Add enfant
    public function ajouterEnfant($data) {
        $errors = $this->valider($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $enfant = new Enfant(
            htmlspecialchars(trim($data['nom'])),
            htmlspecialchars(trim($data['prenom'])),
            $data['date_naissance'],
            $data['sexe'],
            $data['photo'] ?? '',
            !empty($data['groupe_id']) ? $data['groupe_id'] : null,
            !empty($data['parent_id']) ? $data['parent_id'] : null,
            $data['date_inscription'] ?? date('Y-m-d'),
            $data['statut'] ?? 'actif'
        );

        $id = $enfant->ajouter();
        return ['success' => true, 'id' => $id];
    }

    // Update enfant
    public function modifierEnfant($id, $data) {
        $errors = $this->valider($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $enfant = new Enfant(
            htmlspecialchars(trim($data['nom'])),
            htmlspecialchars(trim($data['prenom'])),
            $data['date_naissance'],
            $data['sexe'],
            $data['photo'] ?? '',
            !empty($data['groupe_id']) ? $data['groupe_id'] : null,
            !empty($data['parent_id']) ? $data['parent_id'] : null,
            $data['date_inscription'] ?? date('Y-m-d'),
            $data['statut'] ?? 'actif'
        );

        $enfant->modifier($id);
        return ['success' => true];
    }

    // List enfants by parent (FrontOffice)
    public function listerEnfantsParParent($parent_id) {
        $enfant = new Enfant();
        return $enfant->afficherParParent($parent_id);
    }

    // Delete enfant
    public function supprimerEnfant($id) {
        $enfant = new Enfant();
        return $enfant->supprimer($id);
    }

    // Archive enfant
    public function archiverEnfant($id) {
        $enfant = new Enfant();
        return $enfant->archiver($id);
    }

    // Search
    public function rechercherEnfants($keyword) {
        $enfant = new Enfant();
        return $enfant->rechercher($keyword);
    }

    // Count
    public function compterEnfants() {
        $enfant = new Enfant();
        return $enfant->compter();
    }

    // Server-side validation
    private function valider($data) {
        $errors = [];

        if (empty(trim($data['nom'] ?? ''))) {
            $errors[] = "Le nom est obligatoire.";
        } elseif (strlen(trim($data['nom'])) < 2) {
            $errors[] = "Le nom doit contenir au moins 2 caractères.";
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $data['nom'])) {
            $errors[] = "Le nom ne doit contenir que des lettres.";
        }

        if (empty(trim($data['prenom'] ?? ''))) {
            $errors[] = "Le prénom est obligatoire.";
        } elseif (strlen(trim($data['prenom'])) < 2) {
            $errors[] = "Le prénom doit contenir au moins 2 caractères.";
        } elseif (!preg_match('/^[a-zA-ZÀ-ÿ\s\-]+$/', $data['prenom'])) {
            $errors[] = "Le prénom ne doit contenir que des lettres.";
        }

        if (empty($data['date_naissance'] ?? '')) {
            $errors[] = "La date de naissance est obligatoire.";
        } else {
            $dob = new DateTime($data['date_naissance']);
            $now = new DateTime();
            $age = $now->diff($dob)->y;
            if ($age < 0 || $age > 6) {
                $errors[] = "L'enfant doit avoir entre 0 et 6 ans.";
            }
            if ($dob > $now) {
                $errors[] = "La date de naissance ne peut pas être dans le futur.";
            }
        }

        if (empty($data['sexe'] ?? '') || !in_array($data['sexe'], ['M', 'F'])) {
            $errors[] = "Le sexe doit être M ou F.";
        }

        return $errors;
    }
}

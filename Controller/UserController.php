<?php
require_once __DIR__ . '/../Model/User.php';

class UserController {

    public function inscrire($data) {
        $errors = $this->validerInscription($data);
        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $user = new User(
            htmlspecialchars(trim($data['nom'])),
            htmlspecialchars(trim($data['prenom'])),
            trim($data['email']),
            $data['mot_de_passe'],
            'parent',
            htmlspecialchars(trim($data['telephone'] ?? ''))
        );

        $id = $user->inscrire();
        return ['success' => true, 'id' => $id];
    }

    public function login($email, $mot_de_passe) {
        $user = new User();
        $result = $user->login($email, $mot_de_passe);
        if ($result) {
            session_start();
            $_SESSION['user_id'] = $result['id'];
            $_SESSION['user_nom'] = $result['prenom'] . ' ' . $result['nom'];
            $_SESSION['user_role'] = $result['role'];
            $_SESSION['user_email'] = $result['email'];
            return ['success' => true, 'user' => $result];
        }
        return ['success' => false, 'errors' => ['Email ou mot de passe incorrect.']];
    }

    public function logout() {
        session_start();
        session_destroy();
    }

    private function validerInscription($data) {
        $errors = [];
        $lettres = '/^[a-zA-ZÀ-ÿ\s\-]+$/';

        if (empty(trim($data['nom'] ?? ''))) {
            $errors[] = "Le nom est obligatoire.";
        } elseif (!preg_match($lettres, $data['nom'])) {
            $errors[] = "Le nom ne doit contenir que des lettres.";
        }

        if (empty(trim($data['prenom'] ?? ''))) {
            $errors[] = "Le prénom est obligatoire.";
        } elseif (!preg_match($lettres, $data['prenom'])) {
            $errors[] = "Le prénom ne doit contenir que des lettres.";
        }

        if (empty(trim($data['email'] ?? ''))) {
            $errors[] = "L'email est obligatoire.";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "L'email n'est pas valide.";
        } else {
            $user = new User();
            if ($user->emailExiste($data['email'])) {
                $errors[] = "Cet email est déjà utilisé.";
            }
        }

        if (empty($data['mot_de_passe'] ?? '')) {
            $errors[] = "Le mot de passe est obligatoire.";
        } elseif (strlen($data['mot_de_passe']) < 6) {
            $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        }

        if (($data['mot_de_passe'] ?? '') !== ($data['confirm_mdp'] ?? '')) {
            $errors[] = "Les mots de passe ne correspondent pas.";
        }

        if (!empty($data['telephone'])) {
            if (!preg_match('/^[0-9\+\s\-]{8,15}$/', $data['telephone'])) {
                $errors[] = "Le numéro de téléphone n'est pas valide.";
            }
        }

        return $errors;
    }
}

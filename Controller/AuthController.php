<?php
require_once __DIR__ . '/../config/db.php';

class AuthController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    /**
     * Login admin by email + password
     */
    public function loginAdmin($email, $mot_de_passe) {
        if (empty($email) || empty($mot_de_passe)) {
            return ['success' => false, 'error' => 'Tous les champs sont obligatoires.'];
        }

        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = :email AND role = 'admin' AND statut = 'actif'");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $this->createSession($user);
            return ['success' => true, 'redirect' => '/TinyTrack/View/FrontOffice/dashboard.php'];
        }
        return ['success' => false, 'error' => 'Email ou mot de passe incorrect.'];
    }

    /**
     * Login educateur by code_unique + password
     */
    public function loginEducateur($code, $mot_de_passe) {
        if (empty($code) || empty($mot_de_passe)) {
            return ['success' => false, 'error' => 'Tous les champs sont obligatoires.'];
        }

        // Check active account
        $stmt = $this->db->prepare("SELECT * FROM user WHERE code_unique = :code AND role = 'educateur' AND statut = 'actif'");
        $stmt->execute([':code' => $code]);
        $user = $stmt->fetch();

        if (!$user) {
            // Check if pending
            $stmt2 = $this->db->prepare("SELECT * FROM user WHERE code_unique = :code AND role = 'educateur' AND statut = 'en_attente'");
            $stmt2->execute([':code' => $code]);
            if ($stmt2->fetch()) {
                return ['success' => false, 'error' => "Votre compte est en attente d'approbation par l'administration."];
            }
            return ['success' => false, 'error' => 'Aucun éducateur trouvé avec ce code.'];
        }

        if (empty($user['mot_de_passe'])) {
            return ['success' => false, 'error' => "Vous n'êtes pas encore inscrit. Cliquez sur 'S'inscrire'."];
        }

        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $this->createSession($user);
            return ['success' => true, 'redirect' => '/TinyTrack/View/FrontOffice/educateurs/profil.php'];
        }
        return ['success' => false, 'error' => 'Mot de passe incorrect.'];
    }

    /**
     * Login parent by enfant code_unique + password
     */
    public function loginParent($enfant_code, $mot_de_passe) {
        if (empty($enfant_code) || empty($mot_de_passe)) {
            return ['success' => false, 'error' => 'Tous les champs sont obligatoires.'];
        }

        $stmt = $this->db->prepare("SELECT u.* FROM user u JOIN enfant e ON e.parent_id = u.id WHERE e.code_unique = :code AND u.role = 'parent' AND u.statut = 'actif'");
        $stmt->execute([':code' => $enfant_code]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => "Aucun compte trouvé. Vérifiez votre code ou attendez l'approbation."];
        }

        if (empty($user['mot_de_passe'])) {
            return ['success' => false, 'error' => "Vous n'êtes pas encore inscrit. Cliquez sur 'S'inscrire'."];
        }

        if (password_verify($mot_de_passe, $user['mot_de_passe'])) {
            $this->createSession($user);
            return ['success' => true, 'redirect' => '/TinyTrack/View/FrontOffice/enfants/list.php'];
        }
        return ['success' => false, 'error' => 'Mot de passe incorrect.'];
    }

    /**
     * Register a new educateur or parent account (status: en_attente)
     */
    public function register($data) {
        $errors = [];
        $lettresRegex = '/^[a-zA-ZÀ-ÿ\s\-]+$/';
        $role = $data['role'] ?? '';
        $nom = trim($data['nom'] ?? '');
        $prenom = trim($data['prenom'] ?? '');
        $email = trim($data['email'] ?? '');
        $telephone = trim($data['telephone'] ?? '');
        $mot_de_passe = $data['mot_de_passe'] ?? '';
        $confirm_mdp = $data['confirm_mdp'] ?? '';

        // Validation
        if (empty($nom)) $errors[] = "Le nom est obligatoire.";
        elseif (!preg_match($lettresRegex, $nom)) $errors[] = "Le nom ne doit contenir que des lettres.";
        if (empty($prenom)) $errors[] = "Le prénom est obligatoire.";
        elseif (!preg_match($lettresRegex, $prenom)) $errors[] = "Le prénom ne doit contenir que des lettres.";
        if (empty($email)) $errors[] = "L'email est obligatoire.";
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "L'email n'est pas valide.";
        else {
            $stmt = $this->db->prepare("SELECT COUNT(*) FROM user WHERE email = :email");
            $stmt->execute([':email' => $email]);
            if ($stmt->fetchColumn() > 0) $errors[] = "Cet email est déjà utilisé.";
        }
        if (empty($mot_de_passe)) $errors[] = "Le mot de passe est obligatoire.";
        elseif (strlen($mot_de_passe) < 6) $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
        if ($mot_de_passe !== $confirm_mdp) $errors[] = "Les mots de passe ne correspondent pas.";
        if (!in_array($role, ['educateur', 'parent'])) $errors[] = "Rôle invalide.";

        // Telephone
        $telClean = preg_replace('/\s/', '', $telephone);
        if (!empty($telClean) && !preg_match('/^\d{8}$/', $telClean)) {
            $errors[] = "Le numéro de téléphone doit contenir exactement 8 chiffres.";
        }
        if (!empty($telClean)) $telephone = '+216 ' . $telClean;

        // Parent: validate enfant(s) info
        if ($role === 'parent') {
            $enfants = $data['enfants'] ?? [];
            if (empty($enfants)) {
                $errors[] = "Vous devez inscrire au moins un enfant.";
            } else {
                foreach ($enfants as $i => $enf) {
                    $num = $i + 1;
                    $enom = trim($enf['nom'] ?? '');
                    $eprenom = trim($enf['prenom'] ?? '');
                    $ejour = $enf['jour'] ?? '';
                    $emois = $enf['mois'] ?? '';
                    $eannee = $enf['annee'] ?? '';

                    if (empty($enom)) $errors[] = "Enfant {$num} : le nom est obligatoire.";
                    elseif (!preg_match($lettresRegex, $enom)) $errors[] = "Enfant {$num} : le nom ne doit contenir que des lettres.";
                    if (empty($eprenom)) $errors[] = "Enfant {$num} : le prénom est obligatoire.";
                    elseif (!preg_match($lettresRegex, $eprenom)) $errors[] = "Enfant {$num} : le prénom ne doit contenir que des lettres.";
                    if (empty($ejour) || empty($emois) || empty($eannee)) {
                        $errors[] = "Enfant {$num} : la date de naissance est obligatoire.";
                    } else {
                        $edob = $eannee . '-' . str_pad($emois, 2, '0', STR_PAD_LEFT) . '-' . str_pad($ejour, 2, '0', STR_PAD_LEFT);
                        $dob = new DateTime($edob);
                        $now = new DateTime();
                        $age = $now->diff($dob)->y;
                        if ($dob > $now) $errors[] = "Enfant {$num} : date dans le futur.";
                        elseif ($age > 6) $errors[] = "Enfant {$num} : ne doit pas dépasser 6 ans (âge: {$age} ans).";
                    }
                }
            }
        }

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        // Create account
        $hash = password_hash($mot_de_passe, PASSWORD_BCRYPT);
        $prefix = ($role === 'educateur') ? 'TT-1' : 'TT-2';
        $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(code_unique, 5) AS UNSIGNED)) as max_num FROM user WHERE code_unique LIKE '{$prefix}%'");
        $maxNum = $stmt->fetch()['max_num'] ?? 0;
        $code = $prefix . str_pad($maxNum + 1, 3, '0', STR_PAD_LEFT);

        $stmt = $this->db->prepare("INSERT INTO user (code_unique, nom, prenom, email, mot_de_passe, mdp_temp, role, telephone, statut) VALUES (:code, :nom, :prenom, :email, :mdp, :mdp_temp, :role, :tel, 'en_attente')");
        $stmt->execute([
            ':code' => $code,
            ':nom' => $nom,
            ':prenom' => $prenom,
            ':email' => $email,
            ':mdp' => $hash,
            ':mdp_temp' => $mot_de_passe,
            ':role' => $role,
            ':tel' => !empty($telephone) ? $telephone : null
        ]);

        $parentId = $this->db->lastInsertId();

        // If parent, create enfants in database
        if ($role === 'parent' && !empty($data['enfants'])) {
            foreach ($data['enfants'] as $enf) {
                $enom = trim($enf['nom'] ?? '');
                $eprenom = trim($enf['prenom'] ?? '');
                $ejour = $enf['jour'] ?? '';
                $emois = $enf['mois'] ?? '';
                $eannee = $enf['annee'] ?? '';

                if (empty($enom) || empty($eprenom) || empty($ejour) || empty($emois) || empty($eannee)) continue;

                $edob = $eannee . '-' . str_pad($emois, 2, '0', STR_PAD_LEFT) . '-' . str_pad($ejour, 2, '0', STR_PAD_LEFT);
                $sexe = (isset($enf['sexe']) && $enf['sexe'] === 'F') ? 'F' : 'M';

                // Generate enfant code
                $stmt = $this->db->query("SELECT MAX(CAST(SUBSTRING(code_unique, 5) AS UNSIGNED)) as max_num FROM enfant WHERE code_unique LIKE 'TT-3%'");
                $maxEnfant = $stmt->fetch()['max_num'] ?? 0;
                $enfantCode = 'TT-3' . str_pad($maxEnfant + 1, 3, '0', STR_PAD_LEFT);

                $stmt = $this->db->prepare("INSERT INTO enfant (code_unique, nom, prenom, date_naissance, sexe, parent_id, statut) VALUES (:code, :nom, :prenom, :dob, :sexe, :parent_id, 'actif')");
                $stmt->execute([
                    ':code' => $enfantCode,
                    ':nom' => $enom,
                    ':prenom' => $eprenom,
                    ':dob' => $edob,
                    ':sexe' => $sexe,
                    ':parent_id' => $parentId
                ]);
            }
        }

        return ['success' => true, 'role' => $role];
    }

    /**
     * Handle login — decides which method to call based on role
     */
    public function handleLogin($postData) {
        $role = $postData['role'] ?? '';
        $activeTab = $role ?: 'admin';

        if ($role === 'admin') {
            $result = $this->loginAdmin($postData['email'] ?? '', $postData['mot_de_passe'] ?? '');
        } elseif ($role === 'educateur') {
            $result = $this->loginEducateur($postData['educateur_id'] ?? '', $postData['mot_de_passe'] ?? '');
        } elseif ($role === 'parent') {
            $result = $this->loginParent($postData['enfant_id'] ?? '', $postData['mot_de_passe'] ?? '');
        } else {
            $result = ['success' => false, 'error' => 'Rôle invalide.'];
        }

        return [
            'result' => $result,
            'activeTab' => $activeTab
        ];
    }

    /**
     * Logout
     */
    public function logout() {
        session_start();
        session_destroy();
    }

    /**
     * Create session for authenticated user
     */
    private function createSession($user) {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_nom'] = $user['prenom'] . ' ' . $user['nom'];
        $_SESSION['user_role'] = $user['role'];
    }
}

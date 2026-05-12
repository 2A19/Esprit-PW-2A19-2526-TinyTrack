<?php
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/google_oauth.php';

class AuthController extends Controller {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // ======== ACTIONS (routes) ========

    // GET /login or GET /
    public function showLogin(): void {
        if (!empty($_SESSION['user_id'])) {
            $this->redirect($this->dashboardFor($_SESSION['user_role'] ?? 'admin'));
        }
        $this->render('auth/login', [
            'errors'    => [],
            'activeTab' => $_GET['tab'] ?? 'admin',
            'old'       => [],
        ]);
    }

    // POST /login
    public function doLogin(): void {
        $errors = [];
        $activeTab = $_POST['role'] ?? 'admin';

        if (!empty($_POST['google_jwt'])) {
            $result = $this->loginWithGoogle($_POST['google_jwt']);
        } else {
            $response = $this->handleLogin($_POST);
            $activeTab = $response['activeTab'];
            $result = $response['result'];
        }

        if ($result['success']) {
            $this->redirect($result['redirect']);
        }

        $errors[] = $result['error'];
        $this->render('auth/login', [
            'errors'    => $errors,
            'activeTab' => $activeTab,
            'old'       => $_POST,
        ]);
    }

    // GET|POST /logout
    public function logoutAction(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'],
                $params['secure'], $params['httponly']);
        }
        session_destroy();
        $this->redirect('/login');
    }

    // POST /face-login (JSON API)
    public function faceLoginAction(): void {
        if (!$this->isPost()) {
            $this->jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
        }
        $body = json_decode(file_get_contents('php://input'), true);
        $descriptor = $body['descriptor'] ?? null;
        $this->jsonResponse($this->loginWithFace($descriptor));
    }

    // POST /face-enroll (JSON API)
    public function faceEnrollAction(): void {
        if (empty($_SESSION['user_id'])) {
            $this->jsonResponse(['success' => false, 'error' => 'Non authentifié'], 401);
        }
        if (!$this->isPost()) {
            $this->jsonResponse(['success' => false, 'error' => 'Method not allowed'], 405);
        }
        $body = json_decode(file_get_contents('php://input'), true);
        $action = $body['action'] ?? 'save';

        if ($action === 'remove') {
            $this->jsonResponse($this->removeFaceDescriptor($_SESSION['user_id']));
        }
        $this->jsonResponse($this->saveFaceDescriptor($_SESSION['user_id'], $body['descriptor'] ?? null));
    }

    private function dashboardFor(string $role): string {
        return match ($role) {
            'admin'     => '/dashboard',
            'educateur' => '/dashboard/educateur',
            'parent'    => '/mes-enfants',
            default     => '/login',
        };
    }

    // GET /register
    public function showRegister(): void {
        $this->render('auth/register', [
            'activeTab' => $_GET['tab'] ?? 'educateur',
            'errors'    => [],
            'success'   => null,
            'old'       => [],
        ]);
    }

    // POST /register
    public function doRegister(): void {
        $result = $this->register($_POST);
        $this->render('auth/register', [
            'activeTab' => $_POST['role'] ?? 'educateur',
            'errors'    => $result['success'] ? [] : ($result['errors'] ?? []),
            'success'   => $result['success'] ? ($result['role'] ?? null) : null,
            'old'       => $_POST,
        ]);
    }

    // GET /forgot-password
    public function showForgotPassword(): void {
        $this->render('auth/forgot_password', [
            'error'      => null,
            'submitted'  => false,
            'sentTo'     => null,
            'mailFailed' => false,
        ]);
    }

    // POST /forgot-password
    public function doForgotPassword(): void {
        require_once __DIR__ . '/../config/mailer.php';

        $email  = trim($_POST['email'] ?? '');
        $result = $this->requestReset($email);

        $error = null;
        $submitted = false;
        $sentTo = null;
        $mailFailed = false;

        if (!$result['success']) {
            $error = $result['error'];
        } else {
            $resetUrl = APP_BASE_URL . '/reset-password?token=' . urlencode($result['token']);
            $ok = envoyerLienReset($email, $result['prenom'], $resetUrl);
            if ($ok) {
                $submitted = true;
                $sentTo = $email;
            } else {
                $mailFailed = true;
                $error = "L'envoi de l'email a échoué. Réessayez ou contactez l'administration.";
            }
        }

        $this->render('auth/forgot_password', compact('error', 'submitted', 'sentTo', 'mailFailed'));
    }

    // GET /reset-password
    public function showResetPassword(): void {
        $this->render('auth/reset_password', [
            'token'   => $_GET['token'] ?? '',
            'error'   => null,
            'success' => false,
        ]);
    }

    // POST /reset-password
    public function doResetPassword(): void {
        $token = $_POST['token'] ?? $_GET['token'] ?? '';
        $result = $this->resetPassword(
            $token,
            $_POST['mot_de_passe'] ?? '',
            $_POST['confirm_mdp'] ?? ''
        );

        $this->render('auth/reset_password', [
            'token'   => $token,
            'error'   => $result['success'] ? null : $result['error'],
            'success' => $result['success'],
        ]);
    }

    // ======== BUSINESS LOGIC (inchangé) ========

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
            return ['success' => true, 'redirect' => '/TinyTrack/dashboard'];
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
            return ['success' => true, 'redirect' => '/TinyTrack/dashboard/educateur'];
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
            return ['success' => true, 'redirect' => '/TinyTrack/mes-enfants'];
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

        // If the signup came through "Continuer avec Google", the hidden
        // google_jwt field contains the ID token. We re-verify it here so
        // the name/email cannot be spoofed via the client.
        if (!empty($data['google_jwt'])) {
            $claims = verifyGoogleToken($data['google_jwt']);
            if ($claims) {
                $data['nom']    = $claims['family_name'] ?? ($data['nom'] ?? '');
                $data['prenom'] = $claims['given_name']  ?? ($data['prenom'] ?? '');
                $data['email']  = $claims['email'];
            }
        }

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
     * Login via face recognition.
     * - $descriptor : array of 128 floats produced by face-api.js client-side.
     * - Compares against every enrolled user's stored descriptor (Euclidean distance).
     * - The closest match wins if its distance is below FACE_MATCH_THRESHOLD.
     */
    const FACE_MATCH_THRESHOLD = 0.45;

    public function loginWithFace($descriptor) {
        if (!is_array($descriptor) || count($descriptor) !== 128) {
            return ['success' => false, 'error' => "Descripteur facial invalide."];
        }
        // Cast every value to float (safety against malformed input).
        foreach ($descriptor as $v) {
            if (!is_numeric($v)) {
                return ['success' => false, 'error' => "Descripteur facial invalide."];
            }
        }
        $descriptor = array_map('floatval', $descriptor);

        $rows = $this->db->query(
            "SELECT id, nom, prenom, role, statut, face_descriptor
             FROM user
             WHERE face_descriptor IS NOT NULL"
        )->fetchAll();

        $bestUser = null;
        $bestDistance = INF;
        foreach ($rows as $r) {
            $stored = json_decode($r['face_descriptor'], true);
            if (!is_array($stored) || count($stored) !== 128) continue;
            $d = $this->faceDistance($descriptor, $stored);
            if ($d < $bestDistance) {
                $bestDistance = $d;
                $bestUser = $r;
            }
        }

        if (!$bestUser || $bestDistance > self::FACE_MATCH_THRESHOLD) {
            return [
                'success' => false,
                'error'   => "Aucun visage reconnu (distance " . number_format($bestDistance, 3) . " > seuil " . self::FACE_MATCH_THRESHOLD . "). Essayez avec votre mot de passe.",
                'distance' => $bestDistance,
            ];
        }
        if ($bestUser['statut'] !== 'actif') {
            return ['success' => false, 'error' => "Votre compte n'est pas actif."];
        }

        $this->createSession($bestUser);
        $redirect = match ($bestUser['role']) {
            'admin'     => '/TinyTrack/dashboard',
            'educateur' => '/TinyTrack/dashboard/educateur',
            'parent'    => '/TinyTrack/mes-enfants',
            default     => '/TinyTrack/dashboard',
        };
        return ['success' => true, 'redirect' => $redirect, 'distance' => $bestDistance];
    }

    /**
     * Save (or replace) the face descriptor for a user.
     */
    public function saveFaceDescriptor($userId, $descriptor) {
        if (!is_array($descriptor) || count($descriptor) !== 128) {
            return ['success' => false, 'error' => "Descripteur invalide."];
        }
        foreach ($descriptor as $v) {
            if (!is_numeric($v)) {
                return ['success' => false, 'error' => "Descripteur invalide."];
            }
        }
        $descriptor = array_map('floatval', $descriptor);
        $stmt = $this->db->prepare(
            "UPDATE user SET face_descriptor = :desc, face_enrolled_at = NOW() WHERE id = :id"
        );
        $stmt->execute([':desc' => json_encode($descriptor), ':id' => (int)$userId]);
        return ['success' => true];
    }

    /**
     * Remove a user's face descriptor (disables face login for this user).
     */
    public function removeFaceDescriptor($userId) {
        $stmt = $this->db->prepare(
            "UPDATE user SET face_descriptor = NULL, face_enrolled_at = NULL WHERE id = :id"
        );
        $stmt->execute([':id' => (int)$userId]);
        return ['success' => true];
    }

    /**
     * Euclidean distance between two 128-D face descriptors.
     * (Same metric face-api.js uses internally.)
     */
    private function faceDistance(array $a, array $b) {
        $sum = 0.0;
        for ($i = 0; $i < 128; $i++) {
            $diff = $a[$i] - $b[$i];
            $sum += $diff * $diff;
        }
        return sqrt($sum);
    }

    /**
     * Login via a Google Identity Services ID token (JWT).
     * - Verifies the token is genuine and targeted at our app.
     * - Matches the Google email against our user table.
     * - Requires the account to be active (admin approval done).
     */
    public function loginWithGoogle($idToken) {
        $claims = verifyGoogleToken($idToken);
        if (!$claims) {
            return ['success' => false, 'error' => "Jeton Google invalide ou expire."];
        }

        $email = $claims['email'];
        $stmt = $this->db->prepare("SELECT * FROM user WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => "Aucun compte TinyTrack n'est associe a ce Gmail. Inscrivez-vous d'abord."];
        }
        if ($user['statut'] !== 'actif') {
            return ['success' => false, 'error' => "Votre compte est en attente d'approbation par l'administration."];
        }

        $this->createSession($user);
        $redirect = match ($user['role']) {
            'admin'     => '/TinyTrack/dashboard',
            'educateur' => '/TinyTrack/dashboard/educateur',
            'parent'    => '/TinyTrack/mes-enfants',
            default     => '/TinyTrack/dashboard',
        };
        return ['success' => true, 'redirect' => $redirect];
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
     * Request a password reset.
     * - Verifies the email exists and the account is active.
     * - Generates a cryptographically-random token (32 bytes).
     * - Stores only its SHA-256 hash (never the token itself).
     * - Token is single-use and expires after 30 minutes.
     * Returns the plain token + user's prenom on success so the caller can send the email.
     */
    public function requestReset($email) {
        $email = trim($email);
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'error' => "Veuillez saisir un email valide."];
        }

        $stmt = $this->db->prepare("SELECT id, prenom, statut FROM user WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if (!$user) {
            return ['success' => false, 'error' => "Aucun compte associe a cet email."];
        }
        if ($user['statut'] !== 'actif') {
            return ['success' => false, 'error' => "Ce compte n'est pas encore actif. Contactez l'administration."];
        }

        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        $expires = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

        // Invalidate any previous pending tokens for this user.
        $this->db->prepare("UPDATE password_reset SET used = 1 WHERE user_id = :uid AND used = 0")
                 ->execute([':uid' => $user['id']]);

        $stmt = $this->db->prepare("INSERT INTO password_reset (user_id, token_hash, expires_at) VALUES (:uid, :hash, :exp)");
        $stmt->execute([':uid' => $user['id'], ':hash' => $hash, ':exp' => $expires]);

        return ['success' => true, 'token' => $token, 'prenom' => $user['prenom']];
    }

    /**
     * Consume a reset token and set a new password.
     * Validates: token exists, not used, not expired, new password matches confirm, min length 6.
     */
    public function resetPassword($token, $newPassword, $confirmPassword) {
        $token = trim((string)$token);
        if ($token === '' || !ctype_xdigit($token)) {
            return ['success' => false, 'error' => "Lien de reinitialisation invalide."];
        }
        if (empty($newPassword) || strlen($newPassword) < 6) {
            return ['success' => false, 'error' => "Le mot de passe doit contenir au moins 6 caracteres."];
        }
        if ($newPassword !== $confirmPassword) {
            return ['success' => false, 'error' => "Les deux mots de passe ne correspondent pas."];
        }

        $hash = hash('sha256', $token);
        $stmt = $this->db->prepare(
            "SELECT id, user_id, expires_at, used FROM password_reset WHERE token_hash = :hash LIMIT 1"
        );
        $stmt->execute([':hash' => $hash]);
        $row = $stmt->fetch();

        if (!$row) {
            return ['success' => false, 'error' => "Lien invalide ou deja utilise."];
        }
        if ((int)$row['used'] === 1) {
            return ['success' => false, 'error' => "Ce lien a deja ete utilise."];
        }
        if (strtotime($row['expires_at']) < time()) {
            return ['success' => false, 'error' => "Ce lien a expire. Demandez-en un nouveau."];
        }

        // Apply the new password and burn the token (single transaction).
        $this->db->beginTransaction();
        try {
            $pwdHash = password_hash($newPassword, PASSWORD_BCRYPT);
            $this->db->prepare("UPDATE user SET mot_de_passe = :pwd WHERE id = :uid")
                     ->execute([':pwd' => $pwdHash, ':uid' => $row['user_id']]);
            $this->db->prepare("UPDATE password_reset SET used = 1 WHERE id = :id")
                     ->execute([':id' => $row['id']]);
            $this->db->commit();
            return ['success' => true];
        } catch (Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'error' => "Erreur technique, reessayez."];
        }
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

<?php
require_once __DIR__ . '/../config/db.php';

class EducateurController extends Controller {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // GET /educateurs
    public function index(): void {
        $this->requireAuth();
        $filters = [
            'q'      => $_GET['q'] ?? '',
            'statut' => $_GET['statut'] ?? '',
            'niveau' => $_GET['niveau'] ?? '',
        ];
        $sortBy  = $_GET['sort'] ?? $_GET['sortBy'] ?? 'nom';
        $sortDir = $_GET['dir']  ?? $_GET['sortDir'] ?? 'asc';

        $list = $this->listerEducateursFiltered($filters, $sortBy, $sortDir);
        $stats = $this->statsEducateurs($list);

        $this->render('FrontOffice/educateurs/list', [
            'educateurs' => $list,
            'stats'      => $stats,
            'filters'    => $filters,
            'sortBy'     => $sortBy,
            'sortDir'    => $sortDir,
        ]);
    }

    // GET /educateurs/profil
    public function monProfil(): void {
        $this->requireAuth();
        $userId = $_SESSION['user_id'];
        $errors = [];
        $success = false;

        if ($this->isPost()) {
            $result = $this->updateProfil($userId, $_POST);
            if ($result['success']) $success = true;
            else $errors = $result['errors'];
        }

        $groupe = $this->getGroupe($userId);
        $this->render('FrontOffice/educateurs/profil', [
            'profil'           => $this->getProfil($userId),
            'groupe'           => $groupe,
            'enfants'          => $groupe ? $this->getEnfantsGroupe($groupe['id']) : [],
            'parentsContacts'  => $this->getParentsContacts($userId),
            'rapports'         => $this->getRapportsEducateur($userId),
            'errors'           => $errors,
            'success'          => $success,
        ]);
    }

    // POST /educateurs/archive/{id}
    public function archive($id): void {
        $this->requireAuth();
        $this->archiverCompte($id);
        $this->redirect('/educateurs');
    }

    // POST /educateurs/activate/{id}
    public function activate($id): void {
        $this->requireAuth();
        $this->activerCompte($id);
        $this->redirect('/educateurs');
    }

    // GET /parents
    public function parents(): void {
        $this->requireAuth();
        $filters = [
            'q'      => $_GET['q'] ?? '',
            'statut' => $_GET['statut'] ?? '',
        ];
        $sortBy  = $_GET['sort'] ?? $_GET['sortBy'] ?? 'nom';
        $sortDir = $_GET['dir']  ?? $_GET['sortDir'] ?? 'asc';

        $list = $this->listerParentsFiltered($filters, $sortBy, $sortDir);
        $stats = $this->statsParents($list);

        $this->render('BackOffice/parents/list', [
            'parents' => $list,
            'stats'   => $stats,
            'filters' => $filters,
            'sortBy'  => $sortBy,
            'sortDir' => $sortDir,
        ]);
    }

    /**
     * List all educateurs with their group info and child count.
     */
    public function listerEducateurs() {
        $stmt = $this->db->query("
            SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.statut,
                   g.nom AS groupe_nom, g.niveau AS groupe_niveau,
                   (SELECT COUNT(*) FROM enfant e WHERE e.groupe_id = g.id AND e.statut = 'actif') AS nb_enfants
            FROM user u
            LEFT JOIN groupe g ON g.educateur_id = u.id
            WHERE u.role = 'educateur'
            ORDER BY u.nom
        ");
        return $stmt->fetchAll();
    }

    /**
     * Get an educateur's profile by ID.
     */
    public function getProfil($id) {
        $stmt = $this->db->prepare("SELECT * FROM user WHERE id = :id AND role = 'educateur'");
        $stmt->execute([':id' => (int)$id]);
        return $stmt->fetch();
    }

    /**
     * Get the group assigned to an educateur.
     */
    public function getGroupe($educateurId) {
        $stmt = $this->db->prepare("SELECT * FROM groupe WHERE educateur_id = :id");
        $stmt->execute([':id' => (int)$educateurId]);
        return $stmt->fetch();
    }

    /**
     * Archive (deactivate) a user account.
     */
    public function archiverCompte($id) {
        $stmt = $this->db->prepare("UPDATE user SET statut = 'inactif' WHERE id = :id AND role != 'admin'");
        $stmt->execute([':id' => (int)$id]);
        return true;
    }

    /**
     * Reactivate a user account.
     */
    public function activerCompte($id) {
        $stmt = $this->db->prepare("UPDATE user SET statut = 'actif' WHERE id = :id AND role != 'admin'");
        $stmt->execute([':id' => (int)$id]);
        return true;
    }

    /**
     * List all parents with their children.
     */
    public function listerParents() {
        $stmt = $this->db->query("
            SELECT u.*,
                   (SELECT GROUP_CONCAT(e.prenom SEPARATOR ', ') FROM enfant e WHERE e.parent_id = u.id AND e.statut = 'actif') AS enfants_noms,
                   (SELECT COUNT(*) FROM enfant e WHERE e.parent_id = u.id AND e.statut = 'actif') AS nb_enfants
            FROM user u
            WHERE u.role = 'parent'
            ORDER BY u.nom
        ");
        return $stmt->fetchAll();
    }

    /**
     * List educateurs with combined search + filters + sort.
     */
    public function listerEducateursFiltered($filters = [], $sortBy = 'nom', $sortDir = 'asc') {
        $validSort = ['nom' => 'u.nom', 'prenom' => 'u.prenom', 'nb_enfants' => 'nb_enfants'];
        $sortCol = $validSort[$sortBy] ?? 'u.nom';
        $sortDir = strtolower($sortDir) === 'desc' ? 'DESC' : 'ASC';

        $where = ["u.role = 'educateur'"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(u.nom LIKE :q1 OR u.prenom LIKE :q2 OR u.email LIKE :q3 OR u.code_unique LIKE :q4)";
            $kw = '%' . $filters['q'] . '%';
            $params[':q1'] = $kw;
            $params[':q2'] = $kw;
            $params[':q3'] = $kw;
            $params[':q4'] = $kw;
        }
        if (!empty($filters['statut']) && in_array($filters['statut'], ['actif', 'inactif', 'en_attente'])) {
            $where[] = "u.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }
        if (!empty($filters['niveau']) && in_array($filters['niveau'], ['petit', 'moyen', 'grand'])) {
            $where[] = "g.niveau = :niveau";
            $params[':niveau'] = $filters['niveau'];
        }

        $sql = "SELECT u.id, u.nom, u.prenom, u.email, u.telephone, u.statut,
                       g.nom AS groupe_nom, g.niveau AS groupe_niveau,
                       (SELECT COUNT(*) FROM enfant e WHERE e.groupe_id = g.id AND e.statut = 'actif') AS nb_enfants
                FROM user u
                LEFT JOIN groupe g ON g.educateur_id = u.id
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $sortCol $sortDir";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * List parents with combined search + filters + sort.
     */
    public function listerParentsFiltered($filters = [], $sortBy = 'nom', $sortDir = 'asc') {
        $validSort = ['nom' => 'u.nom', 'prenom' => 'u.prenom', 'nb_enfants' => 'nb_enfants'];
        $sortCol = $validSort[$sortBy] ?? 'u.nom';
        $sortDir = strtolower($sortDir) === 'desc' ? 'DESC' : 'ASC';

        $where = ["u.role = 'parent'"];
        $params = [];

        if (!empty($filters['q'])) {
            $where[] = "(u.nom LIKE :q1 OR u.prenom LIKE :q2 OR u.email LIKE :q3)";
            $kw = '%' . $filters['q'] . '%';
            $params[':q1'] = $kw;
            $params[':q2'] = $kw;
            $params[':q3'] = $kw;
        }
        if (!empty($filters['statut']) && in_array($filters['statut'], ['actif', 'inactif', 'en_attente'])) {
            $where[] = "u.statut = :statut";
            $params[':statut'] = $filters['statut'];
        }

        $sql = "SELECT u.*,
                       (SELECT GROUP_CONCAT(e.prenom SEPARATOR ', ') FROM enfant e WHERE e.parent_id = u.id) AS enfants_noms,
                       (SELECT COUNT(*) FROM enfant e WHERE e.parent_id = u.id) AS nb_enfants,
                       (SELECT COUNT(*) FROM enfant e WHERE e.parent_id = u.id AND e.statut = 'actif') AS nb_enfants_actifs
                FROM user u
                WHERE " . implode(' AND ', $where) . "
                ORDER BY $sortCol $sortDir";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    /**
     * Stats aggregated over an educateurs list.
     */
    public function statsEducateurs($list) {
        $total = count($list);
        $actifs = 0; $inactifs = 0; $sansGroupe = 0; $totalEnfants = 0;
        foreach ($list as $u) {
            if ($u['statut'] === 'actif') $actifs++; else $inactifs++;
            if (empty($u['groupe_nom'])) $sansGroupe++;
            $totalEnfants += (int)($u['nb_enfants'] ?? 0);
        }
        return compact('total', 'actifs', 'inactifs', 'sansGroupe', 'totalEnfants');
    }

    /**
     * Stats aggregated over a parents list.
     */
    public function statsParents($list) {
        $total = count($list);
        $actifs = 0; $enAttente = 0; $inactifs = 0; $totalEnfants = 0;
        foreach ($list as $u) {
            if ($u['statut'] === 'actif') $actifs++;
            elseif ($u['statut'] === 'en_attente') $enAttente++;
            else $inactifs++;
            $totalEnfants += (int)($u['nb_enfants'] ?? 0);
        }
        return compact('total', 'actifs', 'enAttente', 'inactifs', 'totalEnfants');
    }

    /**
     * Update educateur profile.
     */
    public function updateProfil($id, $data) {
        $errors = [];
        $lettres = '/^[a-zA-ZÀ-ÿ\s\-]+$/';

        if (empty(trim($data['nom'] ?? ''))) $errors[] = "Le nom est obligatoire.";
        elseif (!preg_match($lettres, $data['nom'])) $errors[] = "Le nom ne doit contenir que des lettres.";
        if (empty(trim($data['prenom'] ?? ''))) $errors[] = "Le prénom est obligatoire.";
        elseif (!preg_match($lettres, $data['prenom'])) $errors[] = "Le prénom ne doit contenir que des lettres.";
        if (!empty($data['telephone'])) {
            $tel = preg_replace('/\s/', '', $data['telephone']);
            if (!preg_match('/^(\+216)?\d{8}$/', $tel)) $errors[] = "Numéro de téléphone invalide.";
        }

        if (!empty($errors)) return ['success' => false, 'errors' => $errors];

        $stmt = $this->db->prepare("UPDATE user SET nom = :nom, prenom = :prenom, telephone = :tel, adresse = :adresse, specialite = :specialite WHERE id = :id AND role = 'educateur'");
        $stmt->execute([
            ':nom' => htmlspecialchars(trim($data['nom'])),
            ':prenom' => htmlspecialchars(trim($data['prenom'])),
            ':tel' => !empty($data['telephone']) ? htmlspecialchars(trim($data['telephone'])) : null,
            ':adresse' => !empty($data['adresse']) ? htmlspecialchars(trim($data['adresse'])) : null,
            ':specialite' => !empty($data['specialite']) ? htmlspecialchars(trim($data['specialite'])) : null,
            ':id' => (int)$id
        ]);

        // Update session name
        $_SESSION['user_nom'] = trim($data['prenom']) . ' ' . trim($data['nom']);

        return ['success' => true];
    }

    /**
     * Get active children in a group, with their medical info.
     */
    public function getEnfantsGroupe($groupeId) {
        $stmt = $this->db->prepare("
            SELECT e.*, d.groupe_sanguin, d.allergies, d.medecin_traitant, d.telephone_urgence
            FROM enfant e
            LEFT JOIN dossier_medical d ON d.enfant_id = e.id
            WHERE e.groupe_id = :groupe_id AND e.statut = 'actif'
            ORDER BY e.nom
        ");
        $stmt->execute([':groupe_id' => (int)$groupeId]);
        return $stmt->fetchAll();
    }

    /**
     * Parents (uniques) des enfants du groupe d'un educateur, avec le(s) prenom(s) de leur(s) enfant(s).
     */
    public function getParentsContacts($educateurId) {
        $stmt = $this->db->prepare("
            SELECT p.id, p.nom, p.prenom, p.email, p.telephone,
                   GROUP_CONCAT(DISTINCT e.prenom ORDER BY e.prenom SEPARATOR ', ') AS enfants_noms
            FROM groupe g
            JOIN enfant e ON e.groupe_id = g.id AND e.statut = 'actif'
            JOIN user p ON p.id = e.parent_id AND p.role = 'parent'
            WHERE g.educateur_id = :id
            GROUP BY p.id, p.nom, p.prenom, p.email, p.telephone
            ORDER BY p.nom
        ");
        $stmt->execute([':id' => (int)$educateurId]);
        return $stmt->fetchAll();
    }

    /**
     * Historique des rapports rediges par un educateur (avec activite associee).
     */
    public function getRapportsEducateur($educateurId) {
        $stmt = $this->db->prepare("
            SELECT r.id_rapport, r.contenu_rapport, r.date_rapport,
                   a.nom_activite, a.date_activite, a.heure_activite
            FROM rapport r
            LEFT JOIN activite a ON a.id_activite = r.id_activite
            WHERE r.id_educateur = :id
            ORDER BY r.date_rapport DESC, r.id_rapport DESC
        ");
        $stmt->execute([':id' => (int)$educateurId]);
        return $stmt->fetchAll();
    }
}

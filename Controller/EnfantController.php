<?php
require_once __DIR__ . '/../Model/Enfant.php';
require_once __DIR__ . '/../Model/Groupe.php';
require_once __DIR__ . '/../Model/User.php';

class EnfantController extends Controller {

    private Enfant $enfantModel;
    private Groupe $groupeModel;
    private User $userModel;

    public function __construct() {
        $this->enfantModel = new Enfant();
        $this->groupeModel = new Groupe();
        $this->userModel   = new User();
    }

    // GET /enfants — list (with optional ?search=)
    public function index(): void {
        $this->requireAuth();

        $search = trim($_GET['search'] ?? '');
        $enfants = $search !== ''
            ? $this->enfantModel->rechercher($search)
            : $this->enfantModel->afficher();

        $this->render('BackOffice/enfants/list', [
            'enfants'      => $enfants,
            'totalEnfants' => $this->enfantModel->compter(),
            'search'       => $search,
            'msg'          => $_GET['msg'] ?? null,
        ]);
    }

    // GET|POST /enfants/add
    public function add(): void {
        $this->requireAuth();

        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST);
            if (empty($errors)) {
                $this->buildEnfant($_POST)->ajouter();
                $this->redirect('/enfants?msg=ajoute');
            }
            $old = $_POST;
        }

        $this->render('BackOffice/enfants/add', [
            'errors'  => $errors,
            'groupes' => $this->groupeModel->afficher(),
            'parents' => $this->userModel->listerParents(),
            'old'     => $old,
        ]);
    }

    // GET|POST /enfants/edit/{id}
    public function edit($id): void {
        $this->requireAuth();

        $enfant = $this->enfantModel->afficherParId($id);
        if (!$enfant) $this->redirect('/enfants');

        $errors = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST);
            if (empty($errors)) {
                $this->buildEnfant($_POST)->modifier($id);
                $this->redirect('/enfants?msg=modifie');
            }
            $enfant = array_merge($enfant, $_POST);
        }

        $this->render('BackOffice/enfants/edit', [
            'enfant'  => $enfant,
            'errors'  => $errors,
            'groupes' => $this->groupeModel->afficher(),
            'parents' => $this->userModel->listerParents(),
        ]);
    }

    // POST /enfants/delete/{id}
    public function delete($id): void {
        $this->requireAuth();
        $enfant = $this->enfantModel->afficherParId($id);
        $parentId = $enfant['parent_id'] ?? null;

        $this->enfantModel->supprimer($id);

        // Règle métier : un parent ne peut pas exister sans au moins un enfant.
        // Si on vient de supprimer son dernier enfant → supprimer aussi le parent.
        if ($parentId) $this->cleanupOrphanParent($parentId);

        $this->redirect('/enfants?msg=supprime');
    }

    // POST /enfants/archive/{id}
    public function archive($id): void {
        $this->requireAuth();
        $enfant = $this->enfantModel->afficherParId($id);
        $parentId = $enfant['parent_id'] ?? null;

        $this->enfantModel->archiver($id);

        // Si tous les enfants du parent sont archivés/supprimés → désactiver le parent.
        if ($parentId) $this->deactivateParentIfNoActiveChildren($parentId);

        $this->redirect('/enfants?msg=archive');
    }

    private function cleanupOrphanParent($parentId): void {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM enfant WHERE parent_id = :pid");
        $stmt->execute([':pid' => $parentId]);
        if ((int)$stmt->fetchColumn() === 0) {
            $db->prepare("DELETE FROM user WHERE id = :id AND role = 'parent'")
               ->execute([':id' => $parentId]);
        }
    }

    private function deactivateParentIfNoActiveChildren($parentId): void {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT COUNT(*) FROM enfant WHERE parent_id = :pid AND statut = 'actif'");
        $stmt->execute([':pid' => $parentId]);
        if ((int)$stmt->fetchColumn() === 0) {
            $db->prepare("UPDATE user SET statut = 'inactif' WHERE id = :id AND role = 'parent'")
               ->execute([':id' => $parentId]);
        }
    }

    // POST /enfants/activate/{id}
    public function activate($id): void {
        $this->requireAuth();
        $this->enfantModel->activer($id);
        $this->redirect('/enfants?msg=actif');
    }

    // GET /mes-enfants — FrontOffice (parent / educateur / admin)
    public function mesEnfants(): void {
        $this->requireAuth();
        $role   = $_SESSION['user_role'] ?? '';
        $userId = $_SESSION['user_id'] ?? null;

        $filters = [
            'q'      => trim($_GET['q'] ?? ''),
            'sexe'   => $_GET['sexe'] ?? '',
            'statut' => $_GET['statut'] ?? '',
            'niveau' => $_GET['niveau'] ?? '',
        ];
        if ($role === 'parent')     $filters['parent_id']    = $userId;
        if ($role === 'educateur')  $filters['educateur_id'] = $userId;

        $sortBy  = $_GET['sort'] ?? 'date_inscription';
        $sortDir = $_GET['dir']  ?? 'desc';

        $enfants = $this->enfantModel->listerFiltered($filters, $sortBy, $sortDir);
        $enfants = $this->enfantModel->enrichirAvecGroupeEtEducateur($enfants);
        $stats   = $this->statsEnfants($enfants);

        $this->render('FrontOffice/enfants/list', [
            'enfants'          => $enfants,
            'stats'            => $stats,
            'filters'          => $filters,
            'sortBy'           => $sortBy,
            'sortDir'          => $sortDir,
            'parentNom'        => $_SESSION['user_nom'] ?? 'Parent',
            'hasActiveFilters' => !empty($filters['q']) || !empty($filters['sexe']) || !empty($filters['statut']) || !empty($filters['niveau']),
        ]);
    }

    private function statsEnfants(array $enfants): array {
        $total = count($enfants);
        $garcons = $filles = 0;
        $ages = ['0-2' => 0, '3-4' => 0, '5-6' => 0];
        $now = new DateTime();

        foreach ($enfants as $e) {
            if (($e['sexe'] ?? '') === 'M') $garcons++; else $filles++;
            try {
                $dob = new DateTime($e['date_naissance']);
                $age = $now->diff($dob)->y;
                if ($age <= 2) $ages['0-2']++;
                elseif ($age <= 4) $ages['3-4']++;
                else $ages['5-6']++;
            } catch (Exception $ex) {}
        }

        return ['total' => $total, 'garcons' => $garcons, 'filles' => $filles, 'ages' => $ages];
    }

    // ---------- Helpers privés ----------

    private function buildEnfant(array $data): Enfant {
        return new Enfant(
            htmlspecialchars(trim($data['nom'] ?? '')),
            htmlspecialchars(trim($data['prenom'] ?? '')),
            $data['date_naissance'] ?? '',
            $data['sexe'] ?? '',
            $data['photo'] ?? '',
            !empty($data['groupe_id']) ? $data['groupe_id'] : null,
            !empty($data['parent_id']) ? $data['parent_id'] : null,
            $data['date_inscription'] ?? date('Y-m-d'),
            $data['statut'] ?? 'actif'
        );
    }

    private function validate(array $data): array {
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
            try {
                $dob = new DateTime($data['date_naissance']);
                $now = new DateTime();
                $age = $now->diff($dob)->y;
                if ($age < 0 || $age > 6) {
                    $errors[] = "L'enfant doit avoir entre 0 et 6 ans.";
                }
                if ($dob > $now) {
                    $errors[] = "La date de naissance ne peut pas être dans le futur.";
                }
            } catch (Exception $e) {
                $errors[] = "Date de naissance invalide.";
            }
        }

        if (empty($data['sexe'] ?? '') || !in_array($data['sexe'], ['M', 'F'])) {
            $errors[] = "Le sexe doit être M ou F.";
        }

        return $errors;
    }
}

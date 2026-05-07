<?php
require_once __DIR__ . '/../Model/Rapport.php';
require_once __DIR__ . '/../Model/Activite.php';

class RapportController extends Controller {

    private Rapport $model;
    private Activite $activiteModel;

    public function __construct() {
        $this->model = new Rapport();
        $this->activiteModel = new Activite();
    }

    // GET /rapports — BackOffice
    public function index(): void {
        $this->requireAuth();
        $tri = ($_GET['tri'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $this->render('BackOffice/rapports/listRapports', [
            'rapports' => $this->model->listerAvecActivite($tri),
            'tri'      => $tri,
            'msg'      => $_GET['success'] ?? null,
        ]);
    }

    // GET|POST /rapports/edit/{id}
    public function edit($id): void {
        $this->requireAuth();
        $data = $this->model->afficherParId($id);
        if (!$data) $this->redirect('/rapports');

        $errors = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST, false);
            if (empty($errors)) {
                $this->buildRapport($_POST)->modifier($id);
                $this->redirect('/rapports?success=edit');
            }
            $data = array_merge($data, $_POST);
        }

        $this->render('BackOffice/rapports/editRapport', [
            'data'      => $data,
            'errors'    => $errors,
            'id'        => $id,
            'activites' => $this->activiteModel->lister(),
        ]);
    }

    // POST /rapports/delete/{id}
    public function delete($id): void {
        $this->requireAuth();
        $this->model->supprimer($id);
        $this->redirect('/rapports?success=deleted');
    }

    // GET|POST /rapports/add — FrontOffice (educateur)
    public function add(): void {
        $this->requireAuth();
        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST, true);
            if (empty($errors)) {
                $this->buildRapport($_POST)->ajouter();
                $this->redirect('/rapports/parent?success=add');
            }
            $old = $_POST;
        }

        $this->render('FrontOffice/rapports/addRapport', [
            'errors'    => $errors,
            'old'       => $old,
            'activites' => $this->activiteModel->lister(),
        ]);
    }

    // GET /rapports/parent — FrontOffice (parent view)
    public function parent(): void {
        $this->requireAuth();
        $tri = ($_GET['tri'] ?? 'desc') === 'asc' ? 'asc' : 'desc';
        $rapports = (($_SESSION['user_role'] ?? '') === 'parent')
            ? $this->model->listerPourParent($_SESSION['user_id'], $tri)
            : $this->model->listerAvecActivite($tri);

        $this->render('FrontOffice/rapports/listRapportsParent', [
            'rapports' => $rapports,
            'tri'      => $tri,
        ]);
    }

    // GET /rapports/pdf/{id} — Export PDF (print-friendly)
    public function exportPdf($id): void {
        $this->requireAuth();
        $rapport = $this->model->afficherAvecActivite($id);
        if (!$rapport) $this->redirect('/rapports/parent');

        $this->render('FrontOffice/rapports/exportRapportPdf', [
            'rapport' => $rapport,
        ]);
    }

    // GET /rapports/educateur/{id} — FrontOffice (activités d'un éducateur)
    public function activitesEducateur($id_educateur = null): void {
        $this->requireAuth();
        $activites = ($id_educateur !== null && is_numeric($id_educateur) && $id_educateur > 0)
            ? $this->activiteModel->listerParEducateur($id_educateur)
            : [];

        $this->render('FrontOffice/rapports/listActivitesEducateur', [
            'activites'    => $activites,
            'id_educateur' => $id_educateur,
        ]);
    }

    // ---------- helpers ----------

    private function buildRapport(array $data): Rapport {
        $date = trim($data['date_rapport'] ?? '');
        // Format soumis : 'AAAA-MM-JJ HH:MM' → stocker en 'Y-m-d H:i:s'
        if ($date !== '' && preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date)) {
            $date .= ':00';
        }
        return new Rapport(
            null,
            trim($data['contenu_rapport'] ?? ''),
            $date,
            !empty($data['id_activite']) ? (int)$data['id_activite'] : null,
            !empty($data['id_educateur']) ? (int)$data['id_educateur'] : null
        );
    }

    /**
     * @param bool $rejectPast Refuser une date dans le passé (true à l'ajout, false à l'édition)
     */
    private function validate(array $data, bool $rejectPast = true): array {
        $errors = [];

        $contenu = trim($data['contenu_rapport'] ?? '');
        if ($contenu === '') {
            $errors[] = "Le contenu est obligatoire.";
        } elseif (strlen($contenu) < 5) {
            $errors[] = "Le contenu est trop court (min 5 caractères).";
        }

        $date = trim($data['date_rapport'] ?? '');
        if ($date === '') {
            $errors[] = "La date est obligatoire.";
        } elseif (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date)) {
            $errors[] = "Le format de la date doit être AAAA-MM-JJ HH:MM.";
        } else {
            date_default_timezone_set('Africa/Tunis');
            $dateSaisie = DateTime::createFromFormat('Y-m-d H:i', $date);
            if (!$dateSaisie) {
                $errors[] = "La date saisie est invalide.";
            } elseif ($rejectPast && $dateSaisie < new DateTime()) {
                $errors[] = "La date du rapport ne peut pas être dans le passé.";
            }
        }

        $idAct = trim($data['id_activite'] ?? '');
        if ($idAct === '') {
            $errors[] = "L'activité est obligatoire.";
        } elseif (!ctype_digit($idAct)) {
            $errors[] = "L'activité sélectionnée est invalide.";
        } else {
            $exists = $this->activiteModel->afficherParId((int)$idAct);
            if (!$exists) {
                $errors[] = "L'activité sélectionnée n'existe pas.";
            }
        }

        $idEd = trim($data['id_educateur'] ?? '');
        if ($idEd !== '' && !ctype_digit($idEd)) {
            $errors[] = "L'identifiant de l'éducateur doit être numérique.";
        }

        return $errors;
    }
}

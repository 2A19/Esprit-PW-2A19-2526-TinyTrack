<?php
require_once __DIR__ . '/../Model/Activite.php';

class ActiviteController extends Controller {

    private Activite $model;

    public function __construct() {
        $this->model = new Activite();
    }

    // GET /activites
    public function index(): void {
        $this->requireAuth();
        $this->render('BackOffice/activites/listActivites', [
            'activites' => $this->model->lister(),
            'msg'       => $_GET['success'] ?? null,
        ]);
    }

    // GET|POST /activites/add
    public function add(): void {
        $this->requireAuth();
        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST);
            if (empty($errors)) {
                $this->buildActivite($_POST)->ajouter();
                $this->redirect('/activites?success=add');
            }
            $old = $_POST;
        }

        $this->render('BackOffice/activites/addActivite', [
            'errors' => $errors,
            'old'    => $old,
        ]);
    }

    // GET|POST /activites/edit/{id}
    public function edit($id): void {
        $this->requireAuth();
        $data = $this->model->afficherParId($id);
        if (!$data) $this->redirect('/activites');

        $errors = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST);
            if (empty($errors)) {
                $this->buildActivite($_POST)->modifier($id);
                $this->redirect('/activites?success=edit');
            }
            $data = array_merge($data, $_POST);
        }

        $this->render('BackOffice/activites/editActivite', [
            'data'   => $data,
            'errors' => $errors,
        ]);
    }

    // POST /activites/delete/{id}
    public function delete($id): void {
        $this->requireAuth();
        $this->model->supprimer($id);
        $this->redirect('/activites?success=delete');
    }

    // GET /activites/statistiques
    public function statistiques(): void {
        $this->requireAuth();
        $this->render('BackOffice/activites/statistiquesActivites', [
            'stats' => $this->model->statistiquesParEducateur(),
        ]);
    }

    // GET /activites/expertise
    public function expertise(): void {
        $this->requireAuth();
        $this->render('BackOffice/activites/expertiseActivites', [
            'expertises' => $this->model->expertiseParEducateur(),
        ]);
    }

    // ---------- helpers ----------

    private function buildActivite(array $data): Activite {
        return new Activite(
            null,
            trim($data['nom_activite'] ?? ''),
            trim($data['description'] ?? ''),
            trim($data['date_activite'] ?? ''),
            trim($data['heure_activite'] ?? ''),
            !empty($data['id_educateur']) ? (int)$data['id_educateur'] : null
        );
    }

    private function validate(array $data): array {
        $errors = [];
        if (trim($data['nom_activite'] ?? '') === '') $errors[] = "Le nom est obligatoire.";
        if (trim($data['date_activite'] ?? '') === '') $errors[] = "La date est obligatoire.";
        elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['date_activite'])) $errors[] = "Date invalide (AAAA-MM-JJ).";
        if (trim($data['heure_activite'] ?? '') === '') $errors[] = "L'heure est obligatoire.";
        elseif (!preg_match('/^\d{2}:\d{2}(:\d{2})?$/', $data['heure_activite'])) $errors[] = "Heure invalide (HH:MM).";
        return $errors;
    }
}

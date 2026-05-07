<?php
require_once __DIR__ . '/../Model/Reservation.php';
require_once __DIR__ . '/../Model/Evenement.php';

class ReservationController extends Controller {

    private Reservation $model;
    private Evenement $evModel;

    public function __construct() {
        $this->model   = new Reservation();
        $this->evModel = new Evenement();
    }

    // GET /reservations
    public function index(): void {
        $this->requireAuth();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        $this->render('BackOffice/reservations/list', [
            'reservations' => $this->model->lister(),
            'flash'        => $flash,
        ]);
    }

    // GET|POST /reservations/add
    public function add(): void {
        $this->requireAuth();
        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST);

            $evenement_id = (int)($_POST['evenement_id'] ?? 0);
            if (empty($errors) && $evenement_id > 0) {
                $ev = $this->evModel->afficherParId($evenement_id);
                if ($ev && $this->placesRestantes($evenement_id, (int)$ev['capacite_max']) <= 0) {
                    $errors['evenement_id'] = "Cet événement est complet. Plus de places disponibles.";
                }
            }

            if (empty($errors)) {
                $this->buildReservation($_POST)->ajouter();
                $this->verifierComplet($evenement_id);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Réservation ajoutée.'];
                $this->redirect('/reservations');
            }
            $old = $_POST;
        }

        $this->render('BackOffice/reservations/add', [
            'evenements' => $this->evModel->lister(),
            'errors'     => $errors,
            'old'        => $old,
        ]);
    }

    // GET|POST /evenements/reserver/{id} — formulaire de réservation parent
    public function reserver($id = null): void {
        $this->requireAuth();
        $evenement_id = (int)($_POST['evenement_id'] ?? $id ?? 0);
        $event = $evenement_id ? $this->evModel->afficherParId($evenement_id) : null;

        // Pour un parent : charger SES enfants pour le select
        $mesEnfants = [];
        if (($_SESSION['user_role'] ?? '') === 'parent' && !empty($_SESSION['user_id'])) {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, nom, prenom, date_naissance FROM enfant WHERE parent_id = :pid AND statut = 'actif' ORDER BY prenom");
            $stmt->execute([':pid' => (int)$_SESSION['user_id']]);
            $mesEnfants = $stmt->fetchAll();
        }

        $errors = [];
        $old    = [];
        $success = false;

        if ($this->isPost()) {
            $old = $_POST;
            $enfant_id   = trim($_POST['enfant_id']        ?? '');
            $parent_id   = trim($_POST['parent_id']        ?? (string)($_SESSION['user_id'] ?? ''));
            $nb_acc      = trim($_POST['nb_accompagnants'] ?? '0');
            $commentaire = trim($_POST['commentaire']      ?? '');

            if (!$event)                                       $errors[] = "Événement introuvable.";
            if ($enfant_id === '')                             $errors[] = "L'ID enfant est obligatoire.";
            if ($parent_id === '')                             $errors[] = "L'ID parent est obligatoire.";
            if (!is_numeric($nb_acc) || (int)$nb_acc < 0)      $errors[] = "Nombre d'accompagnants invalide.";
            if ($event && $event['statut'] === 'complet')      $errors[] = "Cet événement est complet.";
            if ($event && $event['statut'] === 'termine')      $errors[] = "Cet événement est terminé.";
            if ($event && $event['statut'] === 'annule')       $errors[] = "Cet événement est annulé.";

            // Vérification capacité par comptage des réservations actives
            if ($event && empty($errors)) {
                $places = $this->placesRestantes($evenement_id, (int)$event['capacite_max']);
                if ($places <= 0) $errors[] = "Plus aucune place disponible — événement complet.";
            }

            if (empty($errors)) {
                $res = new Reservation(
                    null,
                    $evenement_id,
                    (int)$enfant_id,
                    (int)$parent_id,
                    (int)$nb_acc,
                    $commentaire,
                    new DateTime(),
                    'en_attente',
                    'non_paye'
                );
                $res->ajouter();
                $this->verifierComplet($evenement_id);
                $event = $this->evModel->afficherParId($evenement_id);
                $success = true;
            }
        }

        $placesRestantes = $event ? $this->placesRestantes($evenement_id, (int)$event['capacite_max']) : null;

        $this->render('FrontOffice/evenements/reserver', [
            'event'           => $event,
            'allEvents'       => $this->evModel->lister(),
            'errors'          => $errors,
            'old'             => $old,
            'success'         => $success,
            'placesRestantes' => $placesRestantes,
            'mesEnfants'      => $mesEnfants,
        ]);
    }

    /** Calcule les places restantes (capacité max - réservations actives). */
    private function placesRestantes(int $evenement_id, int $capaciteMax): int {
        return max(0, $capaciteMax - $this->model->compterParEvenement($evenement_id));
    }

    /** Marque l'événement comme 'complet' si le nombre de réservations actives atteint la capacité. */
    private function verifierComplet(int $evenement_id): void {
        if ($evenement_id <= 0) return;
        $event = $this->evModel->afficherParId($evenement_id);
        if (!$event) return;
        $count = $this->model->compterParEvenement($evenement_id);
        if ($count >= (int)$event['capacite_max'] && $event['statut'] !== 'complet') {
            $this->evModel->mettreAJourStatut($evenement_id, 'complet');
        }
    }

    // GET|POST /reservations/edit/{id}
    public function edit($id): void {
        $this->requireAuth();
        $reservation = $this->model->afficherParId($id);
        if (!$reservation) $this->redirect('/reservations');

        $errors = [];

        if ($this->isPost()) {
            $errors = $this->validateForEdit($_POST);
            if (empty($errors)) {
                $this->buildReservation($_POST)->modifier($id);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Réservation modifiée.'];
                $this->redirect('/reservations');
            }
            $reservation = array_merge($reservation, $_POST);
        }

        $this->render('BackOffice/reservations/edit', [
            'reservation' => $reservation,
            'evenements'  => $this->evModel->lister(),
            'errors'      => $errors,
        ]);
    }

    // POST /reservations/delete/{id}
    public function delete($id): void {
        $this->requireAuth();
        $this->model->supprimer($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Réservation supprimée.'];
        $this->redirect('/reservations');
    }

    // ---------- helpers ----------

    public function getAll(): array {
        return $this->model->lister();
    }

    private function buildReservation(array $data): Reservation {
        return new Reservation(
            null,
            (int)($data['evenement_id'] ?? 0),
            !empty($data['enfant_id']) ? (int)$data['enfant_id'] : null,
            !empty($data['parent_id']) ? (int)$data['parent_id'] : null,
            (int)($data['nb_accompagnants'] ?? 0),
            trim($data['commentaire'] ?? ''),
            new DateTime(),
            $data['statut'] ?? 'en_attente',
            $data['paiement'] ?? 'non_paye'
        );
    }

    private function validate(array $data): array {
        $errors = [];
        if (trim($data['evenement_id'] ?? '') === '') $errors['evenement_id'] = "L'événement est obligatoire.";
        if (!empty($data['enfant_id']) && !ctype_digit((string)$data['enfant_id'])) $errors['enfant_id'] = "Doit être un nombre.";
        if (!empty($data['parent_id']) && !ctype_digit((string)$data['parent_id'])) $errors['parent_id'] = "Doit être un nombre.";
        return $errors;
    }

    private function validateForEdit(array $data): array {
        $errors = [];
        if (trim($data['evenement_id'] ?? '') === '') $errors[] = "Événement obligatoire.";
        return $errors;
    }
}

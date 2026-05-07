<?php
require_once __DIR__ . '/../Model/Evenement.php';

class EvenementController extends Controller {

    private Evenement $model;

    public function __construct() {
        $this->model = new Evenement();
    }

    // GET /evenements
    public function index(): void {
        $this->requireAuth();

        $evenements = $this->model->lister();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);

        // Comptage des réservations actives par événement
        require_once __DIR__ . '/../Model/Reservation.php';
        $resModel = new Reservation();
        foreach ($evenements as &$ev) {
            $ev['nb_reservations'] = $resModel->compterParEvenement((int)$ev['id']);
        }
        unset($ev);

        $this->render('BackOffice/evenements/evenementList', [
            'evenements' => $evenements,
            'flash'      => $flash,
            'total'      => count($evenements),
            'planifies'  => count(array_filter($evenements, fn($e) => $e['statut'] === 'planifie')),
            'en_cours'   => count(array_filter($evenements, fn($e) => $e['statut'] === 'en_cours')),
            'termines'   => count(array_filter($evenements, fn($e) => $e['statut'] === 'termine')),
        ]);
    }

    // GET|POST /evenements/add
    public function add(): void {
        $this->requireAuth();
        $errors = [];
        $old = [];

        if ($this->isPost()) {
            $errors = $this->validate($_POST);
            if (empty($errors)) {
                $this->buildEvenement($_POST)->ajouter();
                $_SESSION['flash'] = ['type' => 'success', 'message' => "L'événement « " . htmlspecialchars($_POST['titre']) . " » a été ajouté."];
                $this->redirect('/evenements');
            }
            $old = $_POST;
        }

        $this->render('BackOffice/evenements/ajouterevenement', [
            'errors' => $errors,
            'old'    => $old,
        ]);
    }

    // GET|POST /evenements/edit/{id}
    public function edit($id): void {
        $this->requireAuth();
        $eventData = $this->model->afficherParId($id);
        if (!$eventData) $this->redirect('/evenements');

        $errors = [];
        $val = $eventData;

        if ($this->isPost()) {
            $errors = $this->validate($_POST);
            if (empty($errors)) {
                $this->buildEvenement($_POST)->modifier($id);
                $_SESSION['flash'] = ['type' => 'success', 'message' => 'Événement modifié avec succès.'];
                $this->redirect('/evenements');
            }
            $val = array_merge($eventData, $_POST);
        }

        $this->render('BackOffice/evenements/editEvenement', [
            'eventData' => $eventData,
            'errors'    => $errors,
            'val'       => $val,
        ]);
    }

    // POST /evenements/delete/{id}
    public function delete($id): void {
        $this->requireAuth();
        $this->model->supprimer($id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Événement supprimé.'];
        $this->redirect('/evenements');
    }

    // GET /evenements/{id}/reservations — liste des réservations d'un événement (admin)
    public function reservations($id): void {
        $this->requireAuth();
        $event = $this->model->afficherParId($id);
        if (!$event) $this->redirect('/evenements');

        require_once __DIR__ . '/../Model/Reservation.php';
        $resModel = new Reservation();
        $reservations = $resModel->listerParEvenement((int)$id);

        // Statistiques
        $total       = count($reservations);
        $confirmees  = count(array_filter($reservations, fn($r) => ($r['statut']  ?? '') === 'confirmee'));
        $enAttente   = count(array_filter($reservations, fn($r) => ($r['statut']  ?? '') === 'en_attente'));
        $annulees    = count(array_filter($reservations, fn($r) => ($r['statut']  ?? '') === 'annulee'));
        $payees      = count(array_filter($reservations, fn($r) => ($r['paiement'] ?? '') === 'paye'));
        $actives     = $total - $annulees;
        $capaciteMax = (int)$event['capacite_max'];
        $restantes   = max(0, $capaciteMax - $actives);

        $this->render('BackOffice/evenements/reservationsEvenement', [
            'event'        => $event,
            'reservations' => $reservations,
            'total'        => $total,
            'confirmees'   => $confirmees,
            'enAttente'    => $enAttente,
            'annulees'     => $annulees,
            'payees'       => $payees,
            'actives'      => $actives,
            'restantes'    => $restantes,
            'capaciteMax'  => $capaciteMax,
        ]);
    }

    // GET /evenements/parent — vue parent FrontOffice
    public function frontofficeParent(): void {
        $this->requireAuth();
        $events = $this->model->lister();

        // Stats avis globales + comptage réservations par événement
        require_once __DIR__ . '/../Model/Reservation.php';
        $resModel = new Reservation();
        $totalNotes = 0;
        $totalNb = 0;
        foreach ($events as &$ev) {
            $avisList = json_decode($ev['avis'] ?? '[]', true) ?: [];
            if (!empty($avisList)) {
                $totalNotes += array_sum(array_column($avisList, 'note'));
                $totalNb    += count($avisList);
            }
            $reservees = $resModel->compterParEvenement((int)$ev['id']);
            $ev['places_reservees'] = $reservees;
            $ev['places_restantes'] = max(0, (int)$ev['capacite_max'] - $reservees);
        }
        unset($ev);
        $moyenneGlobale = $totalNb > 0 ? round($totalNotes / $totalNb, 1) : 0;

        $this->render('FrontOffice/evenements/list', [
            'events'         => $events,
            'moyenneGlobale' => $moyenneGlobale,
            'totalAvis'      => $totalNb,
        ]);
    }

    // GET /reservations/parent — vue parent FrontOffice (ses réservations)
    public function frontofficeReservationsParent(): void {
        $this->requireAuth();
        require_once __DIR__ . '/../Model/Reservation.php';
        $resModel = new Reservation();
        $parentId = $_SESSION['user_id'] ?? 0;
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("
            SELECT r.*, e.titre, e.date, e.lieu
            FROM reservation r
            LEFT JOIN evenement e ON r.evenement_id = e.id
            WHERE r.parent_id = :pid
            ORDER BY r.date_reservation DESC
        ");
        $stmt->execute([':pid' => $parentId]);
        $this->render('FrontOffice/evenements/reservations', [
            'reservations' => $stmt->fetchAll(),
        ]);
    }

    // GET /evenements/dashboard
    public function dashboard(): void {
        $this->requireAuth();
        require_once __DIR__ . '/ReservationController.php';
        $resCtrl = new ReservationController();

        $allEvents = $this->model->lister();
        $totalPrix = array_sum(array_column($allEvents, 'prix'));
        $countByStatut = function($s) use ($allEvents) {
            return count(array_filter($allEvents, fn($e) => $e['statut'] === $s));
        };

        $this->render('BackOffice/evenements/dashboard', [
            'allEvents'      => $allEvents,
            'totalEvents'    => count($allEvents),
            'totalPrix'      => $totalPrix,
            'countPlanifie'  => $countByStatut('planifie'),
            'countEnCours'   => $countByStatut('en_cours'),
            'countTermine'   => $countByStatut('termine'),
            'countAnnule'    => $countByStatut('annule'),
            'allRes'         => method_exists($resCtrl, 'getAll') ? $resCtrl->getAll() : [],
        ]);
    }

    // ---------- helpers ----------

    private function buildEvenement(array $data): Evenement {
        return new Evenement(
            null,
            trim($data['titre'] ?? ''),
            trim($data['description'] ?? ''),
            !empty($data['date']) ? new DateTime($data['date']) : null,
            trim($data['heure_debut'] ?? ''),
            trim($data['heure_fin'] ?? ''),
            trim($data['type'] ?? ''),
            trim($data['lieu'] ?? ''),
            !empty($data['capacite_max']) ? (int)$data['capacite_max'] : null,
            !empty($data['prix']) ? (float)$data['prix'] : 0.0,
            !empty($data['groupe_id']) ? (int)$data['groupe_id'] : null,
            trim($data['statut'] ?? ''),
            $data['avis'] ?? null
        );
    }

    // ============== Avis (système d'évaluation) ==============

    /** POST /evenements/avis/submit — endpoint AJAX, retourne JSON. Une parent ne peut laisser qu'un seul avis par événement. */
    public function submitAvis(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        header('Content-Type: application/json; charset=utf-8');

        if (!$this->isPost()) {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
            return;
        }
        if (empty($_SESSION['user_id'])) {
            echo json_encode(['success' => false, 'message' => 'Authentification requise.']);
            return;
        }

        $evenement_id = (int)($_POST['evenement_id'] ?? 0);
        $parent_id    = (int)($_POST['parent_id']    ?? $_SESSION['user_id']);
        $note         = (int)($_POST['note']         ?? 0);
        $commentaire  = trim($_POST['commentaire']   ?? '');

        if ($evenement_id <= 0 || $parent_id <= 0 || $note < 1 || $note > 5 || $commentaire === '') {
            echo json_encode(['success' => false, 'message' => 'Tous les champs sont obligatoires (note 1-5).']);
            return;
        }
        if (strlen($commentaire) > 500) {
            echo json_encode(['success' => false, 'message' => 'Commentaire trop long (max 500 caractères).']);
            return;
        }

        $event = $this->model->afficherParId($evenement_id);
        if (!$event) {
            echo json_encode(['success' => false, 'message' => 'Événement introuvable.']);
            return;
        }
        if (($event['statut'] ?? '') !== 'termine') {
            echo json_encode(['success' => false, 'message' => "Vous ne pouvez laisser un avis que sur un événement terminé."]);
            return;
        }

        $avisList = json_decode($event['avis'] ?? '[]', true) ?: [];
        foreach ($avisList as $a) {
            if ((int)($a['parent_id'] ?? 0) === $parent_id) {
                echo json_encode(['success' => false, 'message' => 'Vous avez déjà laissé un avis pour cet événement.']);
                return;
            }
        }

        $nouvelAvis = [
            'parent_id'   => $parent_id,
            'note'        => $note,
            'commentaire' => htmlspecialchars($commentaire, ENT_QUOTES, 'UTF-8'),
            'date'        => date('Y-m-d H:i:s'),
        ];
        $avisList[] = $nouvelAvis;

        $this->model->mettreAJourAvis($evenement_id, json_encode($avisList, JSON_UNESCAPED_UNICODE));

        $moyenne = round(array_sum(array_column($avisList, 'note')) / count($avisList), 1);

        echo json_encode([
            'success'     => true,
            'message'     => 'Avis publié !',
            'moyenne'     => $moyenne,
            'nb_avis'     => count($avisList),
            'nouvel_avis' => $nouvelAvis,
        ]);
    }

    // ============== Géocodage (proxy Nominatim/Photon/fallback) ==============

    /** GET /evenements/geocode?q=... — proxy de géocodage en cascade pour les lieux tunisiens. */
    public function geocode(): void {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');

        $raw = trim($_GET['q'] ?? '');
        if ($raw === '') { echo json_encode(['found' => false, 'query' => '']); return; }

        $FALLBACK = [
            'tunis'=>[36.8065,10.1815],'sfax'=>[34.7397,10.7600],'sousse'=>[35.8256,10.6369],
            'kairouan'=>[35.6712,10.1005],'bizerte'=>[37.2744,9.8739],'gabes'=>[33.8814,10.0982],
            'ariana'=>[36.8625,10.1956],'gafsa'=>[34.4250,8.7842],'monastir'=>[35.7643,10.8113],
            'nabeul'=>[36.4564,10.7354],'hammamet'=>[36.3998,10.6135],'mahdia'=>[35.5047,11.0622],
            'beja'=>[36.7256,9.1817],'jendouba'=>[36.5011,8.7757],'kef'=>[36.1822,8.7149],
            'siliana'=>[36.0844,9.3708],'kasserine'=>[35.1676,8.8365],'sidi bouzid'=>[35.0382,9.4849],
            'tozeur'=>[33.9197,8.1335],'kebili'=>[33.7043,8.9687],'tataouine'=>[32.9211,10.4511],
            'medenine'=>[33.3549,10.5055],'zaghouan'=>[36.4029,10.1429],'manouba'=>[36.8095,10.0977],
            'ben arous'=>[36.7535,10.2282],'kelibia'=>[36.8468,11.0951],'djerba'=>[33.8333,10.9167],
            'zarzis'=>[33.5032,11.1121],'tabarka'=>[36.9544,8.7576],'ain draham'=>[36.7833,8.6833],
            'el menzah'=>[36.8474,10.2021],'la marsa'=>[36.8783,10.3245],'carthage'=>[36.8531,10.3242],
            'sidi bou said'=>[36.8701,10.3417],'le bardo'=>[36.8094,10.1406],'la goulette'=>[36.8183,10.3053],
            'hammam lif'=>[36.7281,10.3347],'el aouina'=>[36.8431,10.2269],'el mourouj'=>[36.6994,10.2042],
            'megrine'=>[36.7628,10.2356],'raoued'=>[36.9167,10.1667],'mornag'=>[36.6831,10.2858],
            'borj cedria'=>[36.7153,10.3267],'soliman'=>[36.7000,10.4833],'grombalia'=>[36.5833,10.5000],
            'el haouaria'=>[37.0512,11.0120],'korba'=>[36.5762,10.8609],'menzel temime'=>[36.7794,10.9767],
        ];

        $nominatim = function(string $q) {
            $url = 'https://nominatim.openstreetmap.org/search?q=' . urlencode($q)
                . '&format=json&limit=1&accept-language=fr&countrycodes=tn';
            $ctx = stream_context_create(['http' => ['header' => "User-Agent: TinyTrack-App/1.0\r\n", 'timeout' => 5]]);
            $raw = @file_get_contents($url, false, $ctx);
            if (!$raw) return null;
            $data = json_decode($raw, true);
            if (!empty($data)) return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon'], 'display' => $data[0]['display_name']];
            return null;
        };

        $photon = function(string $q) {
            $url = 'https://photon.komoot.io/api/?q=' . urlencode($q . ' Tunisie')
                . '&limit=1&lang=fr&bbox=7.5,30.2,11.6,37.5';
            $ctx = stream_context_create(['http' => ['timeout' => 5]]);
            $raw = @file_get_contents($url, false, $ctx);
            if (!$raw) return null;
            $data = json_decode($raw, true);
            if (!empty($data['features'])) {
                $c = $data['features'][0]['geometry']['coordinates'];
                return ['lat' => (float)$c[1], 'lng' => (float)$c[0], 'display' => $q];
            }
            return null;
        };

        $cleanAddress = function(string $a) {
            $stop = ['maison des jeunes','salle des fêtes','salle fêtes','centre culturel','stade',
                'terrain de sport','terrain sport','jardin','complexe','école','lycée','collège',
                'faculté','université','hôpital','clinique','pharmacie','mosquée','église','place',
                'avenue','rue','boulevard','route','allée','impasse','cité','résidence','immeuble',
                'bloc','étage','appartement','villa','tinytrack','tiny track','notre','notre établissement'];
            $a = strtolower($a);
            foreach ($stop as $s) $a = str_replace($s, '', $a);
            return trim(preg_replace('/\s+/', ' ', $a));
        };

        $extractCity = function(string $a) {
            foreach (array_reverse(array_map('trim', explode(',', $a))) as $p) {
                if (strlen($p) > 2) return $p;
            }
            return $a;
        };

        $fallbackLookup = function(string $a, array $t) {
            $a = strtolower(trim($a));
            if (isset($t[$a])) return ['lat' => $t[$a][0], 'lng' => $t[$a][1], 'display' => $a . ' (fallback)'];
            foreach ($t as $k => $c) {
                if (strlen($k) > 3 && strpos($a, $k) !== false) {
                    return ['lat' => $c[0], 'lng' => $c[1], 'display' => $k . ' (fallback)'];
                }
            }
            return null;
        };

        $result = null; $strategy = '';
        if (($r = $nominatim($raw . ', Tunisie')) !== null)            { $result = $r; $strategy = 'nominatim_full'; }
        if (!$result) {
            $cleaned = $cleanAddress($raw);
            if ($cleaned !== strtolower($raw) && strlen($cleaned) > 2 && ($r = $nominatim($cleaned . ', Tunisie')) !== null) {
                $result = $r; $strategy = 'nominatim_cleaned';
            }
        }
        if (!$result) {
            $city = $extractCity($raw);
            if ($city !== $raw && ($r = $nominatim($city . ', Tunisie')) !== null) { $result = $r; $strategy = 'nominatim_city'; }
        }
        if (!$result && ($r = $photon($raw)) !== null) { $result = $r; $strategy = 'photon_full'; }
        if (!$result) {
            $cleaned = $cleanAddress($raw);
            if (strlen($cleaned) > 2 && ($r = $photon($cleaned)) !== null) { $result = $r; $strategy = 'photon_cleaned'; }
        }
        if (!$result && ($r = $fallbackLookup($raw, $FALLBACK)) !== null) { $result = $r; $strategy = 'manual_fallback'; }
        if (!$result) {
            $city = $extractCity($raw);
            if (($r = $fallbackLookup($city, $FALLBACK)) !== null) { $result = $r; $strategy = 'manual_fallback_city'; }
        }

        if ($result) {
            echo json_encode(['found' => true, 'lat' => $result['lat'], 'lng' => $result['lng'], 'display' => $result['display'], 'strategy' => $strategy, 'query' => $raw]);
        } else {
            echo json_encode(['found' => false, 'query' => $raw, 'strategy' => 'all_failed']);
        }
    }

    private function validate(array $data): array {
        $errors = [];
        if (trim($data['titre'] ?? '') === '')       $errors['titre'] = 'Le titre est obligatoire.';
        if (trim($data['date'] ?? '') === '')        $errors['date'] = 'La date est obligatoire.';
        if (trim($data['heure_debut'] ?? '') === '') $errors['heure_debut'] = "L'heure de début est obligatoire.";
        if (trim($data['heure_fin'] ?? '') === '')   $errors['heure_fin'] = "L'heure de fin est obligatoire.";
        if (!empty($data['heure_debut']) && !empty($data['heure_fin']) && $data['heure_fin'] <= $data['heure_debut'])
            $errors['heure_fin'] = "L'heure de fin doit être après l'heure de début.";
        if (trim($data['type'] ?? '') === '')        $errors['type'] = 'Le type est obligatoire.';
        if (trim($data['lieu'] ?? '') === '')        $errors['lieu'] = 'Le lieu est obligatoire.';
        $cap = trim($data['capacite_max'] ?? '');
        if ($cap === '' || !ctype_digit($cap) || (int)$cap < 1)
            $errors['capacite_max'] = 'La capacité doit être un entier positif.';
        $prix = trim($data['prix'] ?? '');
        if ($prix === '' || !is_numeric($prix) || (float)$prix < 0)
            $errors['prix'] = 'Le prix doit être un nombre positif ou nul.';
        if (trim($data['statut'] ?? '') === '')      $errors['statut'] = 'Le statut est obligatoire.';
        return $errors;
    }
}

<?php
// Vue passive — données injectées par EvenementController::frontofficeParent()
// Variables : $events, $moyenneGlobale, $totalAvis

// Helper : étoiles HTML
function starsHtml(float $note): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($note >= $i)           $html .= '<span class="s-on">★</span>';
        elseif ($note >= $i - 0.5) $html .= '<span class="s-half">★</span>';
        else                       $html .= '<span class="s-off">★</span>';
    }
    return $html;
}

include __DIR__ . '/../template/header.php';
?>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css"/>
<style>
    /* Tabs filter bar */
    .tabs-filter{background:#fff;border-radius:50px;padding:0.4rem;box-shadow:0 4px 20px rgba(0,0,0,0.06);display:inline-flex;gap:0.3rem;flex-wrap:wrap;justify-content:center}
    .tab-pill{background:transparent;border:none;color:#777;font-weight:700;padding:0.6rem 1.4rem;border-radius:30px;cursor:pointer;transition:all 0.25s;font-size:0.9rem;display:inline-flex;align-items:center;gap:0.4rem;font-family:'Nunito',sans-serif}
    .tab-pill:hover{background:rgba(0,0,0,0.04);color:#333}
    .tab-pill.active{color:#fff;box-shadow:0 4px 12px rgba(0,0,0,0.15)}
    .tab-pill.active[data-tab="venir"]{background:linear-gradient(135deg,var(--kider-green),#66BB6A)}
    .tab-pill.active[data-tab="cours"]{background:linear-gradient(135deg,var(--kider-blue),#42A5F5)}
    .tab-pill.active[data-tab="termine"]{background:linear-gradient(135deg,var(--kider-orange),#FFB74D)}
    .tab-pill.active[data-tab="annule"]{background:linear-gradient(135deg,#9E9E9E,#757575)}
    .tab-pill .count{background:rgba(0,0,0,0.08);color:inherit;font-size:0.75rem;padding:0.1rem 0.55rem;border-radius:12px;min-width:24px;text-align:center}
    .tab-pill.active .count{background:rgba(255,255,255,0.25);color:#fff}

    .tab-pane{display:none;animation:fadeIn 0.4s ease}
    .tab-pane.active{display:block}
    @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}

    /* État "en cours" — pulse */
    .live-dot{display:inline-block;width:8px;height:8px;border-radius:50%;background:#EF5350;margin-right:0.3rem;animation:pulse 1.5s infinite}
    @keyframes pulse{0%,100%{opacity:1;transform:scale(1)}50%{opacity:0.5;transform:scale(1.3)}}

    /* Cartes terminées — tonalité plus calme + rating mis en avant */
    .event-card.is-termine{background:#FAFAFA}
    .event-card.is-termine .rating-line{background:linear-gradient(135deg,#FFF3CD,#FFFBEA);padding:0.7rem;border-radius:12px;margin-top:0.8rem}
    .event-card.is-termine .stars{font-size:1.3rem}

    .capacity-block{margin-top:0.6rem;background:#FAFAFA;border-radius:12px;padding:0.6rem 0.8rem}
    .capacity-text{display:flex;justify-content:space-between;align-items:center;font-size:0.8rem;color:#666;margin-bottom:0.4rem}
    .capacity-rest{color:#999;font-size:0.75rem}
    .capacity-bar{height:6px;background:#E8E8E8;border-radius:3px;overflow:hidden}
    .capacity-fill{height:100%;border-radius:3px;transition:width 0.4s ease}

    .empty-state{padding:4rem 2rem;text-align:center;background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.04)}
    .empty-state i{font-size:3rem;color:#ddd;margin-bottom:1rem}
    .empty-state h5{font-family:'Fredoka One',cursive;color:#999}

    .hero{background:linear-gradient(135deg,#E8F5E9 0%,#FFF9F0 50%,#E3F2FD 100%);padding:4rem 0;text-align:center;position:relative;overflow:hidden}
    .hero h1{font-family:'Fredoka One',cursive;font-size:2.5rem;color:#2D3436}
    .hero p{font-size:1.1rem;color:#666;max-width:600px;margin:0.5rem auto}

    .stats-bar{background:#fff;border-radius:20px;padding:1.5rem;box-shadow:0 4px 20px rgba(0,0,0,0.06);margin:1rem 0}
    .stats-bar .stat{text-align:center}
    .stats-bar .stat .num{font-family:'Fredoka One',cursive;font-size:2rem;color:var(--kider-green)}
    .stats-bar .stat .lbl{color:#999;font-size:0.85rem;text-transform:uppercase;letter-spacing:0.05em}

    .event-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;transition:all 0.3s;border:none;position:relative}
    .event-card:hover{transform:translateY(-5px);box-shadow:0 8px 30px rgba(0,0,0,0.1)}
    .event-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;border-radius:20px 20px 0 0}
    .event-card:nth-child(3n+1)::before{background:linear-gradient(90deg,var(--kider-green),var(--kider-blue))}
    .event-card:nth-child(3n+2)::before{background:linear-gradient(90deg,var(--kider-pink),var(--kider-orange))}
    .event-card:nth-child(3n+3)::before{background:linear-gradient(90deg,var(--kider-yellow),var(--kider-green))}
    .event-card .card-body{padding:1.5rem}
    .event-card h5{font-family:'Fredoka One',cursive;font-size:1.1rem;margin-bottom:0.5rem}
    .event-type{display:inline-block;padding:0.2rem 0.7rem;border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase}
    .type-concert{background:#FCE4EC;color:#C62828}
    .type-conference{background:#E3F2FD;color:#1565C0}
    .type-sport{background:#E8F5E9;color:#2E7D32}
    .type-atelier{background:#FFF3E0;color:#E65100}
    .type-festival{background:#F3E5F5;color:#6A1B9A}
    .type-formation{background:#E0F7FA;color:#00695C}
    .type-exposition{background:#FFF8E1;color:#F57F17}
    .type-autre{background:#F5F5F5;color:#616161}
    .event-meta{font-size:0.85rem;color:#888;display:flex;align-items:center;gap:0.4rem;margin-bottom:0.3rem}
    .event-meta i{width:16px;text-align:center}
    .event-prix{font-family:'Fredoka One',cursive;font-size:1.2rem;color:var(--kider-green)}
    .event-statut{padding:0.2rem 0.6rem;border-radius:15px;font-size:0.7rem;font-weight:700}
    .statut-planifie{background:#FFF3CD;color:#856404}
    .statut-en_cours{background:#D1E7DD;color:#0F5132}
    .statut-termine{background:#F8D7DA;color:#842029}
    .statut-annule{background:#E2E3E5;color:#41464B}
    .statut-complet{background:#FDE2E4;color:#9F1239}

    /* Étoiles */
    .stars{color:#ddd;font-size:1rem;display:inline-block}
    .stars .s-on,.stars .s-half{color:#FFB400}
    .rating-line{font-size:0.85rem;color:#666;margin-top:0.5rem}
    .nb-avis{color:#999;font-size:0.75rem;margin-left:0.3rem}

    /* Carte aperçu + modal itinéraire */
    .leaflet-popup-content{font-family:'Nunito',sans-serif;font-size:0.9rem}
    .map-preview{height:320px;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden}
    .map-preview-wrapper{background:#fff;border-radius:20px;padding:1rem;box-shadow:0 4px 20px rgba(0,0,0,0.04)}
    .map-preview-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:0.8rem;padding:0 0.5rem}
    .map-preview-header h3{font-family:'Fredoka One',cursive;color:var(--kider-green);font-size:1.1rem;margin:0;display:flex;align-items:center;gap:0.5rem}
    .map-preview-header .badge-count{background:linear-gradient(135deg,var(--kider-green),#66BB6A);color:#fff;padding:0.25rem 0.7rem;border-radius:15px;font-size:0.8rem;font-weight:700}

    /* Modal avis */
    .star-rating{font-size:2rem;color:#ddd;cursor:pointer;letter-spacing:0.1rem}
    .star-rating .star.active{color:#FFB400}

  </style>

<section class="hero">
  <div class="container">
    <h1><i class="fas fa-calendar-star"></i> Nos Événements</h1>
    <p>Découvrez les activités et événements organisés pour les enfants de TinyTrack</p>

    <?php if (($totalAvis ?? 0) > 0): ?>
      <div class="stats-bar mt-4 d-inline-block">
        <div class="d-flex align-items-center gap-4 flex-wrap justify-content-center">
          <div class="stat"><div class="num"><?= number_format($moyenneGlobale, 1) ?>★</div><div class="lbl">Note moyenne</div></div>
          <div class="stat"><div class="num"><?= (int)$totalAvis ?></div><div class="lbl">Avis publiés</div></div>
          <div class="stat"><div class="num"><?= count($events) ?></div><div class="lbl">Événements</div></div>
        </div>
      </div>
    <?php endif; ?>
  </div>
</section>

<?php
// JSON événements pour aperçu carte (lat/lng résolus côté client via /evenements/geocode)
$events_json = json_encode(array_map(function($ev) {
    $avisList = json_decode($ev['avis'] ?? '[]', true) ?: [];
    $nb  = count($avisList);
    $moy = $nb > 0 ? round(array_sum(array_column($avisList,'note'))/$nb,1) : 0;
    return [
        'id'          => $ev['id'],
        'titre'       => $ev['titre'],
        'lieu'        => $ev['lieu'],
        'date'        => date('d/m/Y', strtotime($ev['date'])),
        'heure_debut' => substr($ev['heure_debut'],0,5),
        'heure_fin'   => substr($ev['heure_fin'],0,5),
        'statut'      => $ev['statut'],
        'prix'        => $ev['prix'] > 0 ? number_format($ev['prix'],2).' TND' : 'Gratuit',
        'note'        => $moy,
        'nb_avis'     => $nb,
    ];
}, $events), JSON_UNESCAPED_UNICODE);

// Regrouper par catégorie temporelle
$evAVenir   = [];
$evEnCours  = [];
$evTermines = [];
$evAnnules  = [];
foreach ($events as $ev) {
    switch ($ev['statut']) {
        case 'en_cours': $evEnCours[]  = $ev; break;
        case 'termine':  $evTermines[] = $ev; break;
        case 'annule':   $evAnnules[]  = $ev; break;
        case 'planifie':
        case 'complet':
        default:         $evAVenir[]   = $ev; break;
    }
}

// Closure pour rendre une carte événement (factorisation)
$renderCard = function(array $ev) {
    $avisList = json_decode($ev['avis'] ?? '[]', true) ?: [];
    $nbAvis   = count($avisList);
    $moyEv    = $nbAvis > 0 ? round(array_sum(array_column($avisList,'note'))/$nbAvis,1) : 0;
    $statutLabels = ['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé','complet'=>'Complet'];
    $cardClass = 'event-card';
    if ($ev['statut'] === 'termine') $cardClass .= ' is-termine';
    ?>
    <div class="col-md-6 col-lg-4">
      <div class="<?= $cardClass ?>">
        <div class="card-body">
          <span class="event-type type-<?= $ev['type'] ?>"><?= htmlspecialchars($ev['type']) ?></span>
          <?php if ($ev['statut'] === 'en_cours'): ?>
            <span class="event-statut statut-en_cours ms-1"><span class="live-dot"></span>En direct</span>
          <?php else: ?>
            <span class="event-statut statut-<?= $ev['statut'] ?> ms-1"><?= $statutLabels[$ev['statut']] ?? ucfirst($ev['statut']) ?></span>
          <?php endif; ?>

          <h5 class="mt-2"><?= htmlspecialchars($ev['titre']) ?></h5>

          <?php if ($ev['description']): ?>
            <p style="font-size:0.85rem;color:#666;margin-bottom:0.8rem;"><?= htmlspecialchars(substr($ev['description'], 0, 100)) ?><?= strlen($ev['description']) > 100 ? '...' : '' ?></p>
          <?php endif; ?>

          <div class="event-meta"><i class="fas fa-calendar" style="color:var(--kider-blue);"></i> <?= date('d/m/Y', strtotime($ev['date'])) ?></div>
          <div class="event-meta"><i class="fas fa-clock" style="color:var(--kider-orange);"></i> <?= substr($ev['heure_debut'],0,5) ?> — <?= substr($ev['heure_fin'],0,5) ?></div>
          <div class="event-meta"><i class="fas fa-map-marker-alt" style="color:var(--kider-pink);"></i> <?= htmlspecialchars($ev['lieu']) ?></div>

          <?php
            $reservees = (int)($ev['places_reservees'] ?? 0);
            $capMax    = (int)$ev['capacite_max'];
            $restantes = (int)($ev['places_restantes'] ?? max(0, $capMax - $reservees));
            $pct       = $capMax > 0 ? min(100, round($reservees / $capMax * 100)) : 0;
            $barColor  = $pct >= 100 ? '#EF5350' : ($pct >= 75 ? '#FFA726' : '#4CAF50');
          ?>
          <div class="capacity-block">
            <div class="capacity-text">
              <span><i class="fas fa-users" style="color:var(--kider-purple);"></i> <strong><?= $reservees ?></strong> / <?= $capMax ?> réservés</span>
              <span class="capacity-rest"><?= $restantes ?> place<?= $restantes > 1 ? 's' : '' ?> restante<?= $restantes > 1 ? 's' : '' ?></span>
            </div>
            <div class="capacity-bar"><div class="capacity-fill" style="width:<?= $pct ?>%;background:<?= $barColor ?>;"></div></div>
          </div>

          <div class="rating-line">
            <span class="stars"><?= starsHtml($moyEv) ?></span>
            <strong><?= $moyEv > 0 ? number_format($moyEv,1) : '—' ?></strong>
            <span class="nb-avis">(<?= $nbAvis ?> avis)</span>
          </div>

          <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:1px solid #f0f0f0;">
            <div class="event-prix">
              <?= $ev['prix'] > 0 ? number_format($ev['prix'], 2) . ' TND' : 'Gratuit' ?>
            </div>
            <div class="d-flex gap-2 flex-wrap">
              <button type="button" class="btn btn-sm btn-outline-info" style="border-radius:20px;" onclick="ouvrirItineraire(<?= (int)$ev['id'] ?>, '<?= htmlspecialchars(addslashes($ev['titre']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($ev['lieu']), ENT_QUOTES) ?>')">
                <i class="fas fa-route"></i> Itinéraire
              </button>
              <?php if ($ev['statut'] === 'termine'): ?>
                <button type="button" class="btn btn-sm btn-outline-warning" style="border-radius:20px;" onclick="ouvrirAvis(<?= (int)$ev['id'] ?>, '<?= htmlspecialchars(addslashes($ev['titre']), ENT_QUOTES) ?>')">
                  <i class="fas fa-star"></i> Avis
                </button>
              <?php endif; ?>
              <?php if (!in_array($ev['statut'], ['termine','annule','complet'], true) && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'educateur')): ?>
                <a href="/TinyTrack/evenements/reserver/<?= $ev['id'] ?>" class="btn btn-sm" style="background:linear-gradient(135deg,var(--kider-green),#66BB6A);color:#fff;border-radius:20px;font-weight:700;padding:0.4rem 1.2rem;"><i class="fas fa-ticket-alt"></i> Réserver</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
    <?php
};
?>

<!-- Onglets de filtrage -->
<section class="py-5">
  <div class="container">

    <?php if (empty($events)): ?>
      <div class="empty-state">
        <i class="fas fa-calendar-times"></i>
        <h5>Aucun événement pour le moment</h5>
        <p class="text-muted">Revenez bientôt pour découvrir nos prochains événements.</p>
      </div>
    <?php else: ?>

      <!-- Aperçu carte -->
      <div class="map-preview-wrapper mb-4">
        <div class="map-preview-header">
          <h3><i class="fas fa-map-marked-alt"></i> Aperçu — où ont lieu nos événements ?</h3>
          <span class="badge-count"><?= count($events) ?> lieux</span>
        </div>
        <div id="mapPreview" class="map-preview"></div>
      </div>

      <!-- Tabs filter -->
      <div class="text-center mb-5">
        <div class="tabs-filter">
          <button type="button" class="tab-pill active" data-tab="venir">
            <i class="fas fa-sparkles"></i> À venir <span class="count"><?= count($evAVenir) ?></span>
          </button>
          <button type="button" class="tab-pill" data-tab="cours">
            <i class="fas fa-bolt"></i> En cours <span class="count"><?= count($evEnCours) ?></span>
          </button>
          <button type="button" class="tab-pill" data-tab="termine">
            <i class="fas fa-check-circle"></i> Terminés <span class="count"><?= count($evTermines) ?></span>
          </button>
          <?php if (!empty($evAnnules)): ?>
            <button type="button" class="tab-pill" data-tab="annule">
              <i class="fas fa-times-circle"></i> Annulés <span class="count"><?= count($evAnnules) ?></span>
            </button>
          <?php endif; ?>
        </div>
      </div>

      <!-- Tab panes -->
      <div id="tab-venir" class="tab-pane active">
        <?php if (empty($evAVenir)): ?>
          <div class="empty-state"><i class="fas fa-sparkles"></i><h5>Aucun événement à venir</h5><p class="text-muted">Restez à l'affût, de nouveaux événements arrivent bientôt !</p></div>
        <?php else: ?>
          <div class="row g-4"><?php foreach ($evAVenir as $ev) $renderCard($ev); ?></div>
        <?php endif; ?>
      </div>

      <div id="tab-cours" class="tab-pane">
        <?php if (empty($evEnCours)): ?>
          <div class="empty-state"><i class="fas fa-bolt"></i><h5>Aucun événement en cours</h5><p class="text-muted">Aucune activité ne se déroule en ce moment.</p></div>
        <?php else: ?>
          <div class="row g-4"><?php foreach ($evEnCours as $ev) $renderCard($ev); ?></div>
        <?php endif; ?>
      </div>

      <div id="tab-termine" class="tab-pane">
        <?php if (empty($evTermines)): ?>
          <div class="empty-state"><i class="fas fa-check-circle"></i><h5>Aucun événement terminé</h5><p class="text-muted">Les événements passés apparaîtront ici.</p></div>
        <?php else: ?>
          <div class="text-center mb-3"><p class="text-muted small"><i class="fas fa-star text-warning"></i> Cliquez sur <strong>Avis</strong> pour partager votre expérience</p></div>
          <div class="row g-4"><?php foreach ($evTermines as $ev) $renderCard($ev); ?></div>
        <?php endif; ?>
      </div>

      <?php if (!empty($evAnnules)): ?>
        <div id="tab-annule" class="tab-pane">
          <div class="row g-4" style="opacity:0.75;"><?php foreach ($evAnnules as $ev) $renderCard($ev); ?></div>
        </div>
      <?php endif; ?>

    <?php endif; ?>

  </div>
</section>

<!-- Modal Itinéraire -->
<div class="modal fade" id="itineraireModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px;border:none;">
      <div class="modal-header" style="border-bottom:none;">
        <h5 class="modal-title" style="font-family:'Fredoka One',cursive;color:var(--kider-green);">
          <i class="fas fa-route text-success"></i> Itinéraire — <span id="itinTitre"></span>
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="row g-2 mb-3">
          <div class="col-md-9">
            <label class="form-label small text-muted mb-1">Point de départ</label>
            <div class="input-group">
              <input type="text" id="itinDepartInput" class="form-control" placeholder="Adresse (ex: Tunis), ou utilisez votre position GPS →" style="border-radius:12px 0 0 12px;border:2px solid #E8E8E8;">
              <button class="btn btn-outline-secondary" id="btnGeoloc" type="button" title="Utiliser ma position actuelle" style="border-radius:0 12px 12px 0;border:2px solid #E8E8E8;border-left:none;">
                <i class="fas fa-location-arrow"></i> Ma position
              </button>
            </div>
          </div>
          <div class="col-md-3 d-flex align-items-end">
            <button class="btn w-100" id="btnTracerRoute" type="button" style="background:linear-gradient(135deg,var(--kider-green),#66BB6A);color:#fff;border-radius:12px;font-weight:700;border:none;padding:0.55rem;">
              <i class="fas fa-route"></i> Tracer
            </button>
          </div>
        </div>
        <div id="itinDestInfo" class="small text-muted mb-2"></div>
        <div id="itinMap" style="height:450px;border-radius:12px;background:#f0f0f0;"></div>
        <div id="itinResume" class="mt-3"></div>
      </div>
    </div>
  </div>
</div>

<!-- Modal Avis -->
<div class="modal fade" id="avisModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px;border:none;">
      <div class="modal-header" style="border-bottom:none;">
        <h5 class="modal-title" style="font-family:'Fredoka One',cursive;color:var(--kider-green);"><i class="fas fa-star text-warning"></i> Laisser un avis</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted" id="avisEvenementTitre"></p>
        <input type="hidden" id="avis_evenement_id">
        <div class="mb-3 text-center">
          <label class="form-label">Votre note</label>
          <div class="star-rating" id="starRating">
            <span class="star" data-val="1">★</span><span class="star" data-val="2">★</span><span class="star" data-val="3">★</span><span class="star" data-val="4">★</span><span class="star" data-val="5">★</span>
          </div>
          <input type="hidden" id="avis_note" value="0">
        </div>
        <div class="mb-3">
          <label class="form-label">Votre commentaire</label>
          <textarea id="avis_commentaire" class="form-control" rows="4" maxlength="500" style="border-radius:12px;border:2px solid #E8E8E8;" placeholder="Partagez votre expérience..."></textarea>
        </div>
        <div id="avis_msg"></div>
      </div>
      <div class="modal-footer" style="border-top:none;">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal" style="border-radius:25px;">Annuler</button>
        <button type="button" class="btn btn-success" id="btnSubmitAvis" style="border-radius:25px;background:linear-gradient(135deg,var(--kider-green),#66BB6A);border:none;font-weight:700;">Publier</button>
      </div>
    </div>
  </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.js"></script>
<script>
const PARENT_ID = <?= (int)($_SESSION['user_id'] ?? 0) ?>;
const EVENTS = <?= $events_json ?? '[]' ?>;

// ================= Aperçu carte (tous les événements) =================
(async () => {
  const mapEl = document.getElementById('mapPreview');
  if (!mapEl || !EVENTS.length) return;

  const previewMap = L.map('mapPreview', { scrollWheelZoom: false }).setView([34.0, 9.5], 7);
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; OpenStreetMap', maxZoom: 19
  }).addTo(previewMap);

  // Couleurs par statut
  const statutColors = {
    planifie: '#4CAF50', en_cours: '#5B9BD5', termine: '#FFA726',
    annule:   '#9E9E9E', complet:  '#FF8FAB'
  };

  const bounds = [];
  for (const ev of EVENTS) {
    try {
      const res = await fetch('/TinyTrack/evenements/geocode?q=' + encodeURIComponent(ev.lieu));
      const data = await res.json();
      if (!data.found) continue;

      const color = statutColors[ev.statut] || '#4CAF50';
      const stars = '★'.repeat(Math.round(ev.note)) + '☆'.repeat(5 - Math.round(ev.note));
      const popup = `
        <div style="min-width:180px;">
          <strong style="color:${color};font-size:0.95rem;">${ev.titre}</strong><br>
          <small style="color:#888;">${ev.lieu}</small><br>
          <span style="color:#666;font-size:0.8rem;">${ev.date} • ${ev.heure_debut}–${ev.heure_fin}</span><br>
          <span style="color:#FFB400;">${stars}</span>
          <small style="color:#999;">(${ev.nb_avis})</small><br>
          <strong style="color:#4CAF50;">${ev.prix}</strong>
        </div>
      `;

      // Marker coloré selon statut
      const icon = L.divIcon({
        html: `<i class="fas fa-map-marker-alt fa-2x" style="color:${color};text-shadow:0 1px 3px rgba(0,0,0,0.3);"></i>`,
        className: '', iconSize: [30, 30], iconAnchor: [15, 30]
      });
      L.marker([data.lat, data.lng], { icon }).addTo(previewMap).bindPopup(popup);
      bounds.push([data.lat, data.lng]);
    } catch (e) {}
  }
  if (bounds.length) previewMap.fitBounds(bounds, { padding: [30, 30], maxZoom: 11 });

  // Activer zoom à la molette au clic (sinon ça gêne le scroll de page)
  previewMap.on('click', () => previewMap.scrollWheelZoom.enable());
  previewMap.on('mouseout', () => previewMap.scrollWheelZoom.disable());
})();

// ================= Tabs =================
document.querySelectorAll('.tab-pill').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-pill').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.remove('active'));
    btn.classList.add('active');
    const target = document.getElementById('tab-' + btn.dataset.tab);
    if (target) target.classList.add('active');
  });
});

async function geocodeViaProxy(query) {
  try {
    const res = await fetch('/TinyTrack/evenements/geocode?q=' + encodeURIComponent(query));
    const data = await res.json();
    return data.found ? { lat: data.lat, lng: data.lng } : null;
  } catch(e) { return null; }
}

// ================= Modal Itinéraire (par événement) =================
let itinMap = null;
let itinControl = null;
let itinDestMarker = null;
let itinDestCoords = null;

async function ouvrirItineraire(eventId, titre, lieu) {
  document.getElementById('itinTitre').textContent = titre;
  document.getElementById('itinDestInfo').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Localisation de la destination...';
  document.getElementById('itinDepartInput').value = '';
  document.getElementById('itinResume').innerHTML = '';
  itinDestCoords = null;

  const modal = new bootstrap.Modal(document.getElementById('itineraireModal'));
  modal.show();

  setTimeout(async () => {
    if (!itinMap) {
      itinMap = L.map('itinMap').setView([34.0, 9.5], 7);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap', maxZoom: 19
      }).addTo(itinMap);
    } else {
      itinMap.invalidateSize();
      if (itinControl) { itinMap.removeControl(itinControl); itinControl = null; }
      if (itinDestMarker) { itinMap.removeLayer(itinDestMarker); itinDestMarker = null; }
    }

    const dest = await geocodeViaProxy(lieu);
    if (dest) {
      itinDestCoords = [dest.lat, dest.lng];
      itinDestMarker = L.marker(itinDestCoords).addTo(itinMap)
        .bindPopup('<strong>' + titre + '</strong><br>' + lieu).openPopup();
      itinMap.setView(itinDestCoords, 13);
      document.getElementById('itinDestInfo').innerHTML =
        '<i class="fas fa-map-marker-alt text-success"></i> <strong>Destination :</strong> ' + titre + ' — ' + lieu;
    } else {
      document.getElementById('itinDestInfo').innerHTML =
        '<i class="fas fa-exclamation-triangle text-warning"></i> Impossible de localiser : <strong>' + lieu + '</strong>. Le tracé d\'itinéraire ne sera pas possible.';
    }
  }, 300);
}

document.getElementById('btnGeoloc').addEventListener('click', () => {
  if (!navigator.geolocation) { alert("Géolocalisation non supportée par votre navigateur."); return; }
  const btn = document.getElementById('btnGeoloc');
  btn.disabled = true;
  btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
  navigator.geolocation.getCurrentPosition(
    pos => {
      document.getElementById('itinDepartInput').value =
        pos.coords.latitude.toFixed(6) + ',' + pos.coords.longitude.toFixed(6);
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position';
    },
    err => {
      alert("Géolocalisation refusée ou indisponible : " + err.message);
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-location-arrow"></i> Ma position';
    },
    { timeout: 10000, maximumAge: 60000 }
  );
});

document.getElementById('btnTracerRoute').addEventListener('click', async () => {
  const input = document.getElementById('itinDepartInput').value.trim();
  const resumeDiv = document.getElementById('itinResume');

  if (!input) {
    resumeDiv.innerHTML = '<div class="alert alert-warning" style="border-radius:12px;">Veuillez saisir un point de départ ou utiliser votre position.</div>';
    return;
  }
  if (!itinDestCoords) {
    resumeDiv.innerHTML = '<div class="alert alert-danger" style="border-radius:12px;">Destination non géocodée — itinéraire impossible.</div>';
    return;
  }

  resumeDiv.innerHTML = '<div class="text-muted small"><i class="fas fa-spinner fa-spin"></i> Calcul de l\'itinéraire...</div>';

  let depCoords = null;
  const m = input.match(/^(-?\d+\.?\d*)\s*,\s*(-?\d+\.?\d*)$/);
  if (m) {
    depCoords = [parseFloat(m[1]), parseFloat(m[2])];
  } else {
    const r = await geocodeViaProxy(input);
    if (!r) {
      resumeDiv.innerHTML = '<div class="alert alert-danger" style="border-radius:12px;">Lieu de départ introuvable : <strong>' + input + '</strong></div>';
      return;
    }
    depCoords = [r.lat, r.lng];
  }

  if (itinControl) { itinMap.removeControl(itinControl); }

  itinControl = L.Routing.control({
    waypoints: [
      L.latLng(depCoords[0], depCoords[1]),
      L.latLng(itinDestCoords[0], itinDestCoords[1])
    ],
    routeWhileDragging: false,
    lineOptions: { styles: [{ color: '#4CAF50', opacity: 0.85, weight: 5 }] },
    show: false,
    addWaypoints: false,
    draggableWaypoints: false,
    fitSelectedRoutes: true,
    createMarker: function(i, wp) {
      const isStart = i === 0;
      return L.marker(wp.latLng, {
        icon: L.divIcon({
          html: isStart
            ? '<i class="fas fa-map-marker-alt fa-2x" style="color:#5B9BD5;text-shadow:0 1px 3px rgba(0,0,0,0.3);"></i>'
            : '<i class="fas fa-flag-checkered fa-2x" style="color:#4CAF50;text-shadow:0 1px 3px rgba(0,0,0,0.3);"></i>',
          className: '', iconSize: [30, 30], iconAnchor: [15, 30]
        })
      });
    }
  }).addTo(itinMap);

  itinControl.on('routesfound', e => {
    const r = e.routes[0];
    const km = (r.summary.totalDistance / 1000).toFixed(1);
    const min = Math.round(r.summary.totalTime / 60);
    resumeDiv.innerHTML =
      '<div class="alert alert-success" style="border-radius:12px;">' +
      '<i class="fas fa-route"></i> <strong>Distance :</strong> ' + km + ' km · ' +
      '<strong>Temps estimé :</strong> ~' + min + ' min ' +
      '<small class="text-muted">(en voiture)</small></div>';
  });

  itinControl.on('routingerror', e => {
    resumeDiv.innerHTML = '<div class="alert alert-danger" style="border-radius:12px;">Impossible de calculer l\'itinéraire entre ces deux points.</div>';
  });
});

// ================= Modal Avis =================
const avisModal = new bootstrap.Modal(document.getElementById('avisModal'));
const stars = document.querySelectorAll('#starRating .star');
const noteInput = document.getElementById('avis_note');

stars.forEach(s => {
  s.addEventListener('mouseenter', () => updateStars(parseInt(s.dataset.val)));
  s.addEventListener('mouseleave', () => updateStars(parseInt(noteInput.value) || 0));
  s.addEventListener('click', () => { noteInput.value = s.dataset.val; updateStars(parseInt(s.dataset.val)); });
});
function updateStars(n) {
  stars.forEach(s => s.classList.toggle('active', parseInt(s.dataset.val) <= n));
}

function ouvrirAvis(eventId, titre) {
  document.getElementById('avis_evenement_id').value = eventId;
  document.getElementById('avisEvenementTitre').textContent = titre;
  document.getElementById('avis_commentaire').value = '';
  noteInput.value = 0;
  updateStars(0);
  document.getElementById('avis_msg').innerHTML = '';
  avisModal.show();
}

document.getElementById('btnSubmitAvis').addEventListener('click', async () => {
  const evid = document.getElementById('avis_evenement_id').value;
  const note = parseInt(noteInput.value);
  const com  = document.getElementById('avis_commentaire').value.trim();
  const msg  = document.getElementById('avis_msg');

  if (!evid || note < 1 || note > 5 || !com) {
    msg.innerHTML = '<div class="alert alert-warning" style="border-radius:12px;">Note (1-5) et commentaire obligatoires.</div>';
    return;
  }
  if (com.length > 500) {
    msg.innerHTML = '<div class="alert alert-warning" style="border-radius:12px;">Commentaire trop long (max 500).</div>';
    return;
  }

  const fd = new FormData();
  fd.append('evenement_id', evid);
  fd.append('parent_id', PARENT_ID);
  fd.append('note', note);
  fd.append('commentaire', com);

  try {
    const res = await fetch('/TinyTrack/evenements/avis/submit', { method: 'POST', body: fd });
    const data = await res.json();
    if (data.success) {
      msg.innerHTML = '<div class="alert alert-success" style="border-radius:12px;">' + data.message + ' Moyenne : ' + data.moyenne + '★</div>';
      setTimeout(() => location.reload(), 1500);
    } else {
      msg.innerHTML = '<div class="alert alert-danger" style="border-radius:12px;">' + (data.message || 'Erreur.') + '</div>';
    }
  } catch (e) {
    msg.innerHTML = '<div class="alert alert-danger" style="border-radius:12px;">Erreur réseau.</div>';
  }
});
</script>
<?php include __DIR__ . '/../template/footer.php'; ?>

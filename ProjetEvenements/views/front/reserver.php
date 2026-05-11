<?php
session_start();
require_once(__DIR__ . '/../../controller/evenementController.php');
require_once(__DIR__ . '/../../controller/reservationController.php');
require_once(__DIR__ . '/../../models/reservation.php');
require_once(__DIR__ . '/../../config.php');

$evController  = new EvenementController();
$resController = new ReservationController();

$evenement_id = isset($_GET['evenement_id']) ? (int)$_GET['evenement_id'] : 0;
$event        = $evenement_id ? $evController->afficherParId($evenement_id) : null;

$errors  = [];
$old     = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old          = $_POST;
    $evenement_id = (int)($_POST['evenement_id'] ?? 0);
    $enfant_id    = trim($_POST['enfant_id']       ?? '');
    $parent_id    = trim($_POST['parent_id']        ?? '');
    $nb_acc       = trim($_POST['nb_accompagnants'] ?? '0');
    $commentaire  = trim($_POST['commentaire']       ?? '');

    $event = $evController->afficherParId($evenement_id);

    if (!$event)                                        $errors[] = "Événement introuvable.";
    if ($enfant_id === '')                              $errors[] = "L'ID enfant est obligatoire.";
    if ($parent_id === '')                              $errors[] = "L'ID parent est obligatoire.";
    if (!is_numeric($nb_acc) || (int)$nb_acc < 0)      $errors[] = "Nombre d'accompagnants invalide.";
    if ($event && $event['statut'] === 'complet')       $errors[] = "Cet événement est complet.";
    if ($event && $event['statut'] === 'termine')       $errors[] = "Cet événement est terminé.";
    if ($event && $event['statut'] === 'annule')        $errors[] = "Cet événement est annulé.";
    if ($event && (int)$event['capacite_max'] <= 0)    $errors[] = "Plus aucune place disponible.";

    if (empty($errors)) {
        // 1. Enregistrer la réservation
        $res = new Reservation();
        $res->setEvenementId($evenement_id);
        $res->setEnfantId((int)$enfant_id);
        $res->setParentId((int)$parent_id);
        $res->setNbAccompagnants((int)$nb_acc);
        $res->setCommentaire($commentaire);
        $res->setDateReservation(new DateTime());
        $res->setStatut('en_attente');
        $res->setPaiement('non_paye');
        $resController->ajouter($res);

        // 2. Décrémenter capacite_max et passer à "complet" si = 0
        try {
            $pdo            = config::getConnexion();
            $nouvelle_cap   = max(0, (int)$event['capacite_max'] - 1);
            $nouveau_statut = ($nouvelle_cap === 0) ? 'complet' : $event['statut'];
            $stmt = $pdo->prepare("UPDATE evenement SET capacite_max=:cap, statut=:statut WHERE id=:id");
            $stmt->execute([':cap' => $nouvelle_cap, ':statut' => $nouveau_statut, ':id' => $evenement_id]);
        } catch (Exception $e) {
            error_log('[TinyTrack] Erreur capacité : ' . $e->getMessage());
        }

        $event   = $evController->afficherParId($evenement_id);
        $success = true;
    }
}

$allEvents = $evController->afficher()->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>TinyTrack — Réserver</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css"/>
  <style>
    :root{--green:#4CAF50;--blue:#5B9BD5;--yellow:#FFD93D;--pink:#FF8FAB;--orange:#FFA726;--purple:#9C7CDB}
    body{font-family:'Nunito',sans-serif;background:#FFF9F0;color:#333}
    .navbar-kider{background:#fff;box-shadow:0 3px 20px rgba(0,0,0,.06);padding:.8rem 0;border-bottom:4px solid transparent;border-image:linear-gradient(90deg,var(--green),var(--blue),var(--yellow),var(--pink),var(--orange)) 1}
    .navbar-kider .navbar-brand{font-family:'Fredoka One',cursive;color:var(--green);font-size:1.6rem;display:flex;align-items:center;gap:10px}
    .navbar-kider .navbar-brand img{height:42px;width:42px;object-fit:contain}
    .navbar-kider .nav-link{font-weight:700;color:#555;padding:.5rem 1rem;border-radius:25px;margin:0 2px;transition:all .2s}
    .navbar-kider .nav-link:hover{color:var(--green);background:rgba(76,175,80,.08)}
    .section-title{font-family:'Fredoka One',cursive;color:var(--green);font-size:2rem;position:relative;display:inline-block}
    .section-title::after{content:'';position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:linear-gradient(90deg,var(--yellow),var(--orange));border-radius:2px}
    .form-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,.06);padding:2rem;border:none}
    .form-control,.form-select{border-radius:12px;border:2px solid #E8E8E8;transition:all .25s}
    .form-control:focus,.form-select:focus{border-color:var(--green);box-shadow:0 0 0 4px rgba(76,175,80,.12)}
    label{font-weight:700;font-size:.9rem;color:#555}
    .btn-reserver{background:linear-gradient(135deg,var(--green),#66BB6A);color:#fff;border:none;border-radius:25px;font-weight:700;padding:.7rem 2rem;font-size:1rem;box-shadow:0 3px 10px rgba(76,175,80,.2)}
    .btn-reserver:hover{background:linear-gradient(135deg,#388E3C,var(--green));color:#fff;transform:translateY(-2px)}

    /* Event info card */
    .event-info{background:linear-gradient(135deg,#E8F5E9,#FFF9F0);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem;position:relative}
    .cap-badge{display:inline-flex;align-items:center;gap:6px;padding:.25rem .75rem;border-radius:20px;font-size:.8rem;font-weight:700}
    .cap-ok{background:#E8F5E9;color:#2E7D32}
    .cap-low{background:#FFF3E0;color:#E65100}
    .cap-zero{background:#FFEBEE;color:#C62828}

    /* Path button */
    .btn-path{
      background:linear-gradient(135deg,var(--blue),var(--purple));
      color:#fff;border:none;border-radius:20px;
      font-weight:700;font-size:.85rem;
      padding:.45rem 1.1rem;cursor:pointer;
      display:inline-flex;align-items:center;gap:6px;
      transition:all .2s;box-shadow:0 3px 10px rgba(91,155,213,.3);
    }
    .btn-path:hover{transform:translateY(-2px);box-shadow:0 5px 16px rgba(91,155,213,.4);color:#fff}

    .alert{border-radius:16px;border:none;font-weight:600}
    footer{background:linear-gradient(135deg,var(--green),#388E3C);color:#fff;padding:2.5rem 0;margin-top:4rem;position:relative;overflow:hidden}
    footer::before{content:'';position:absolute;top:-20px;left:50%;transform:translateX(-50%);width:120%;height:40px;background:#FFF9F0;border-radius:0 0 50% 50%}
    footer .footer-logo{font-family:'Fredoka One',cursive;font-size:1.4rem}
    .leaflet-routing-container{display:none !important}
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="images/logo.png" alt="TinyTrack"> TinyTrack
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navKider">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navKider">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
        <li class="nav-item"><a class="nav-link" href="mesreservations.php"><i class="fas fa-ticket-alt"></i> Mes Réservations</a></li>
        <li class="nav-item"><a class="nav-link" href="../back/index.php"><i class="fas fa-lock"></i> BackOffice</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="section-title"><i class="fas fa-ticket-alt"></i> Réserver</h2>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">

        <?php if ($success): ?>
          <!-- Succès -->
          <div class="alert text-center" style="background:linear-gradient(135deg,#E8F5E9,#C8E6C9);color:#2E7D32">
            <i class="fas fa-check-circle fa-2x mb-2"></i><br>
            <strong>Réservation enregistrée avec succès !</strong><br>
            Votre réservation est en attente de confirmation.
            <?php if ($event): ?>
              <hr style="border-color:rgba(46,125,50,.2)">
              <div style="font-size:.85rem;color:#388E3C">
                <?php $cap = (int)$event['capacite_max'];
                  if ($cap === 0): ?>
                    <i class="fas fa-ban"></i> <strong>Événement complet — plus aucune place disponible.</strong>
                  <?php elseif ($cap <= 5): ?>
                    <i class="fas fa-fire" style="color:#E65100"></i> Plus que <strong><?= $cap ?> place<?= $cap>1?'s':'' ?></strong> disponible<?= $cap>1?'s':'' ?> !
                  <?php else: ?>
                    <i class="fas fa-users"></i> <?= $cap ?> place<?= $cap>1?'s':'' ?> encore disponible<?= $cap>1?'s':'' ?>.
                <?php endif; ?>
              </div>
            <?php endif; ?>
            <div class="mt-3">
              <a href="index.php" class="btn btn-sm btn-outline-success me-2"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
              <a href="mesreservations.php" class="btn btn-sm btn-success"><i class="fas fa-list"></i> Mes réservations</a>
            </div>
          </div>

        <?php else: ?>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="background:linear-gradient(135deg,#FFEBEE,#FFCDD2);color:#C62828">
              <strong><i class="fas fa-exclamation-triangle"></i> Erreurs :</strong>
              <ul class="mb-0 mt-1"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>

          <?php if ($event): ?>
            <div class="event-info">
              <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <h5 style="font-family:'Fredoka One',cursive;margin-bottom:.5rem">
                  <i class="fas fa-calendar-alt text-success"></i> <?= htmlspecialchars($event['titre']) ?>
                </h5>
                <!-- BOUTON PATH -->
                <button class="btn-path"
                  onclick="openPathModal('<?= addslashes(htmlspecialchars($event['titre'])) ?>', '<?= addslashes(htmlspecialchars($event['lieu'])) ?>')">
                  <i class="fas fa-route"></i> Path
                </button>
              </div>
              <div style="font-size:.9rem;color:#666;margin-bottom:.8rem">
                <i class="fas fa-calendar" style="color:var(--blue)"></i> <?= date('d/m/Y',strtotime($event['date'])) ?>
                &nbsp;|&nbsp; <i class="fas fa-clock" style="color:var(--orange)"></i> <?= substr($event['heure_debut'],0,5) ?> — <?= substr($event['heure_fin'],0,5) ?>
                &nbsp;|&nbsp; <i class="fas fa-map-marker-alt" style="color:var(--pink)"></i> <?= htmlspecialchars($event['lieu']) ?>
                &nbsp;|&nbsp; <strong style="color:var(--green)"><?= $event['prix']>0 ? number_format($event['prix'],2).' TND' : 'Gratuit' ?></strong>
              </div>
              <?php $cap = (int)$event['capacite_max']; ?>
              <?php if ($cap === 0): ?>
                <span class="cap-badge cap-zero"><i class="fas fa-ban"></i> Complet — 0 place</span>
              <?php elseif ($cap <= 5): ?>
                <span class="cap-badge cap-low"><i class="fas fa-fire"></i> Dernières places : <?= $cap ?> restante<?= $cap>1?'s':'' ?></span>
              <?php else: ?>
                <span class="cap-badge cap-ok"><i class="fas fa-users"></i> <?= $cap ?> place<?= $cap>1?'s':'' ?> disponible<?= $cap>1?'s':'' ?></span>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <div class="form-card">
            <form method="POST">
              <div class="mb-3">
                <label for="evenement_id"><i class="fas fa-calendar-alt text-info"></i> Événement <span class="text-danger">*</span></label>
                <select name="evenement_id" id="evenement_id" class="form-select" required>
                  <option value="">-- Choisir un événement --</option>
                  <?php foreach ($allEvents as $ev):
                    if (in_array($ev['statut'], ['termine','annule','complet'])) continue; ?>
                    <option value="<?= $ev['id'] ?>" <?= ($evenement_id==$ev['id'])?'selected':'' ?>>
                      <?= htmlspecialchars($ev['titre']) ?> — <?= date('d/m/Y',strtotime($ev['date'])) ?> (<?= $ev['capacite_max'] ?> place<?= $ev['capacite_max']>1?'s':'' ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="enfant_id"><i class="fas fa-child text-warning"></i> ID Enfant <span class="text-danger">*</span></label>
                  <input type="number" name="enfant_id" id="enfant_id" class="form-control" placeholder="Ex: 1" min="1" value="<?= htmlspecialchars($old['enfant_id']??'') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="parent_id"><i class="fas fa-user text-primary"></i> ID Parent <span class="text-danger">*</span></label>
                  <input type="number" name="parent_id" id="parent_id" class="form-control" placeholder="Ex: 1" min="1" value="<?= htmlspecialchars($old['parent_id']??'') ?>" required>
                </div>
              </div>
              <div class="mb-3">
                <label for="nb_accompagnants"><i class="fas fa-users" style="color:var(--purple)"></i> Nombre d'accompagnants</label>
                <input type="number" name="nb_accompagnants" id="nb_accompagnants" class="form-control" min="0" value="<?= htmlspecialchars($old['nb_accompagnants']??'0') ?>">
              </div>
              <div class="mb-4">
                <label for="commentaire"><i class="fas fa-comment text-info"></i> Commentaire <small class="text-muted">(optionnel)</small></label>
                <textarea name="commentaire" id="commentaire" class="form-control" rows="3" placeholder="Informations supplémentaires..."><?= htmlspecialchars($old['commentaire']??'') ?></textarea>
              </div>
              <div class="text-center">
                <button type="submit" class="btn btn-reserver"><i class="fas fa-check-circle"></i> Confirmer la réservation</button>
                <a href="index.php" class="btn btn-outline-secondary ms-2" style="border-radius:25px;font-weight:700">Annuler</a>
              </div>
            </form>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- ════════════════════════════════
     MODAL ITINÉRAIRE PATH
════════════════════════════════ -->
<div class="modal fade" id="pathModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;overflow:hidden;border:none">
      <div class="modal-header" style="background:linear-gradient(135deg,var(--blue),var(--purple));border:none;padding:1.2rem 1.5rem">
        <div>
          <h5 class="modal-title" style="font-family:'Fredoka One',cursive;color:#fff;margin:0">
            <i class="fas fa-route"></i> Itinéraire vers l'événement
          </h5>
          <div id="pathEventName" style="color:rgba(255,255,255,.8);font-size:.85rem;margin-top:2px"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <!-- Status -->
        <div id="pathStatus" style="background:#F8F9FA;padding:.7rem 1.2rem;font-size:.85rem;color:#666;border-bottom:1px solid #eee;display:flex;align-items:center;gap:8px">
          <i class="fas fa-spinner fa-spin" style="color:var(--blue)"></i>
          <span id="pathStatusText">Détection de votre position GPS…</span>
        </div>
        <!-- Info route -->
        <div id="routeInfo" style="display:none;background:linear-gradient(90deg,#E3F2FD,#EDE7F6);padding:.7rem 1.2rem;font-size:.85rem;border-bottom:1px solid #eee;align-items:center">
          <span id="routeDistance" style="font-weight:700;color:var(--blue)"></span>
          &nbsp;·&nbsp;
          <span id="routeDuration" style="font-weight:700;color:var(--purple)"></span>
          &nbsp;·&nbsp;
          <span id="routeDestination" style="color:#666"></span>
        </div>
        <div id="path-map" style="height:480px;width:100%"></div>
      </div>
      <div class="modal-footer" style="border:none;background:#F8F9FA;padding:.8rem 1.2rem">
        <small class="text-muted"><i class="fas fa-info-circle"></i> © OpenStreetMap — Itinéraire via OSRM (gratuit)</small>
        <button type="button" class="btn btn-sm ms-auto" data-bs-dismiss="modal"
          style="background:var(--blue);color:#fff;border-radius:20px;font-weight:700;border:none;padding:.4rem 1.2rem">
          Fermer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- Footer -->
<footer class="text-center">
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem">
    <div class="footer-logo mb-2">TinyTrack</div>
    <p style="opacity:.8;font-size:.95rem">Chaque petit pas compte</p>
    <p style="opacity:.5;font-size:.8rem">ESPRIT 2A19 &copy; 2026 — Rayen Ajili</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>
<script>
let pathMap     = null;
let routingCtrl = null;

function createColorMarker(color) {
  return L.divIcon({
    className:'',
    html:`<div style="width:34px;height:34px;border-radius:50% 50% 50% 0;background:${color};transform:rotate(-45deg);box-shadow:0 4px 14px rgba(0,0,0,.25);border:3px solid #fff"></div>`,
    iconSize:[34,34],iconAnchor:[17,34],popupAnchor:[0,-36]
  });
}
function createUserMarker() {
  return L.divIcon({
    className:'',
    html:`<div style="width:18px;height:18px;border-radius:50%;background:#FF5252;border:3px solid #fff;box-shadow:0 0 0 6px rgba(255,82,82,.25)"></div>`,
    iconSize:[18,18],iconAnchor:[9,9]
  });
}

async function geocodeAddress(address) {
  const query = encodeURIComponent(address + ', Tunisie');
  const url   = `https://nominatim.openstreetmap.org/search?q=${query}&format=json&limit=1&accept-language=fr`;
  try {
    const res  = await fetch(url, { headers:{'User-Agent':'TinyTrack-App/1.0'} });
    const data = await res.json();
    if (data && data.length > 0) return { lat: parseFloat(data[0].lat), lng: parseFloat(data[0].lon) };
  } catch(e) {}
  return null;
}

async function openPathModal(titre, lieu) {
  document.getElementById('pathEventName').textContent  = '📍 ' + lieu;
  document.getElementById('pathStatusText').textContent = 'Détection de votre position GPS…';
  document.getElementById('routeInfo').style.display    = 'none';

  const modal = new bootstrap.Modal(document.getElementById('pathModal'));
  modal.show();

  setTimeout(async () => {
    if (!pathMap) {
      pathMap = L.map('path-map').setView([36.8065,10.1815],11);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{attribution:'© OpenStreetMap',maxZoom:19}).addTo(pathMap);
    } else {
      if (routingCtrl) { pathMap.removeControl(routingCtrl); routingCtrl = null; }
      pathMap.eachLayer(l => { if (l instanceof L.Marker) pathMap.removeLayer(l); });
    }

    document.getElementById('pathStatusText').textContent = '🔍 Géolocalisation de l\'événement…';
    const destCoords = await geocodeAddress(lieu);

    if (!destCoords) {
      document.getElementById('pathStatusText').textContent = '❌ Adresse introuvable : ' + lieu + '. Vérifiez le champ "lieu" de l\'événement.';
      return;
    }

    // Marqueur destination
    L.marker([destCoords.lat, destCoords.lng], { icon: createColorMarker('#5B9BD5') })
      .addTo(pathMap)
      .bindPopup(`<b style="font-family:'Fredoka One',cursive">${titre}</b><br><small style="color:#888">📍 ${lieu}</small>`)
      .openPopup();

    document.getElementById('pathStatusText').textContent = '📡 En attente de votre permission GPS…';

    if (!navigator.geolocation) {
      document.getElementById('pathStatusText').textContent = '❌ Géolocalisation non supportée par ce navigateur.';
      pathMap.setView([destCoords.lat, destCoords.lng], 14);
      return;
    }

    navigator.geolocation.getCurrentPosition(
      (pos) => {
        const userLat = pos.coords.latitude;
        const userLng = pos.coords.longitude;

        document.getElementById('pathStatusText').textContent = '🗺 Calcul de l\'itinéraire…';

        // Marqueur utilisateur
        L.marker([userLat, userLng], { icon: createUserMarker(), zIndexOffset: 1000 })
          .addTo(pathMap)
          .bindPopup('<b style="font-family:Nunito,sans-serif">📍 Votre position actuelle</b>');

        // Routing OSRM
        routingCtrl = L.Routing.control({
          waypoints: [ L.latLng(userLat, userLng), L.latLng(destCoords.lat, destCoords.lng) ],
          routeWhileDragging: false,
          showAlternatives: false,
          lineOptions: { styles: [{ color: '#5B9BD5', weight: 5, opacity: .9 }] },
          createMarker: () => null,
          router: L.Routing.osrmv1({ serviceUrl: 'https://router.project-osrm.org/route/v1' }),
        }).addTo(pathMap);

        routingCtrl.on('routesfound', function(e) {
          const route  = e.routes[0];
          const dist   = (route.summary.totalDistance / 1000).toFixed(1);
          const mins   = Math.round(route.summary.totalTime / 60);
          const hrs    = Math.floor(mins / 60);
          const minR   = mins % 60;
          const dur    = hrs > 0 ? `${hrs}h${String(minR).padStart(2,'0')}` : `${mins} min`;

          document.getElementById('pathStatusText').textContent  = '✅ Itinéraire trouvé !';
          document.getElementById('routeDistance').textContent   = `🚗 ${dist} km`;
          document.getElementById('routeDuration').textContent   = `⏱ ${dur}`;
          document.getElementById('routeDestination').textContent = `📍 ${lieu}`;
          document.getElementById('routeInfo').style.display     = 'flex';
        });

        routingCtrl.on('routingerror', () => {
          document.getElementById('pathStatusText').textContent = '⚠ Itinéraire impossible — destination affichée uniquement.';
          pathMap.setView([destCoords.lat, destCoords.lng], 14);
        });

        pathMap.fitBounds([[userLat,userLng],[destCoords.lat,destCoords.lng]], { padding:[60,60] });
      },
      (err) => {
        const msgs = {1:'❌ Permission GPS refusée.',2:'❌ Position GPS indisponible.',3:'❌ Délai GPS expiré.'};
        document.getElementById('pathStatusText').textContent = msgs[err.code] || '❌ Erreur GPS inconnue.';
        pathMap.setView([destCoords.lat, destCoords.lng], 14);
      },
      { enableHighAccuracy: true, timeout: 15000 }
    );
  }, 450);
}

document.getElementById('pathModal').addEventListener('shown.bs.modal', () => {
  if (pathMap) pathMap.invalidateSize();
});

// Refresh page on event change to show capacity badge
document.getElementById('evenement_id')?.addEventListener('change', function() {
  if (this.value) window.location.href = 'reserver.php?evenement_id=' + this.value;
});
</script>
</body>
</html>
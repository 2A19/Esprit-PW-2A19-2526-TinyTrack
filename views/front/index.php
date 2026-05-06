<?php
include(__DIR__ . '/../../controller/evenementController.php');
$controller = new EvenementController();
$events = $controller->afficher()->fetchAll();

// URL absolue pour submit_avis
$submit_url = rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/submit_avis.php';

// Stats globales avis
$total_notes_global = 0;
$total_nb_global    = 0;
foreach ($events as $ev) {
    $al = json_decode($ev['avis'] ?? '[]', true) ?: [];
    if (count($al) > 0) {
        $total_notes_global += array_sum(array_column($al, 'note'));
        $total_nb_global    += count($al);
    }
}
$moyenne_globale = $total_nb_global > 0 ? round($total_notes_global / $total_nb_global, 1) : 0;

function starsHtml(float $note): string {
    $html = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($note >= $i)           $html .= '<span class="s-on">★</span>';
        elseif ($note >= $i - 0.5) $html .= '<span class="s-half">★</span>';
        else                       $html .= '<span class="s-off">★</span>';
    }
    return $html;
}
function avatarColor(int $id): string {
    $colors = ['#4CAF50','#5B9BD5','#FF8FAB','#9C7CDB','#FFA726','#00BCD4','#FF7043','#66BB6A'];
    return $colors[$id % count($colors)];
}

// Préparer les événements en JSON pour Leaflet (lieu = adresse texte)
$events_json = json_encode(array_map(function($ev) {
    $avis_list = json_decode($ev['avis'] ?? '[]', true) ?: [];
    $nb  = count($avis_list);
    $moy = $nb > 0 ? round(array_sum(array_column($avis_list,'note'))/$nb,1) : 0;
    return [
        'id'          => $ev['id'],
        'titre'       => $ev['titre'],
        'lieu'        => $ev['lieu'],
        'date'        => date('d/m/Y', strtotime($ev['date'])),
        'heure_debut' => substr($ev['heure_debut'],0,5),
        'heure_fin'   => substr($ev['heure_fin'],0,5),
        'type'        => $ev['type'],
        'statut'      => $ev['statut'],
        'prix'        => $ev['prix'] > 0 ? number_format($ev['prix'],2).' TND' : 'Gratuit',
        'capacite'    => $ev['capacite_max'],
        'note'        => $moy,
        'nb_avis'     => $nb,
    ];
}, $events), JSON_UNESCAPED_UNICODE);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>TinyTrack — Événements</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <!-- Leaflet CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <!-- Leaflet Routing Machine CSS -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.css"/>
  <style>
    :root{
      --green:#4CAF50;--blue:#5B9BD5;--yellow:#FFD93D;
      --pink:#FF8FAB;--orange:#FFA726;--purple:#9C7CDB;
    }
    body{font-family:'Nunito',sans-serif;background:#FFF9F0;color:#333}

    /* Navbar */
    .navbar-kider{background:#fff;box-shadow:0 3px 20px rgba(0,0,0,.06);padding:.8rem 0;border-bottom:4px solid transparent;border-image:linear-gradient(90deg,var(--green),var(--blue),var(--yellow),var(--pink),var(--orange)) 1}
    .navbar-kider .navbar-brand{font-family:'Fredoka One',cursive;color:var(--green);font-size:1.6rem;display:flex;align-items:center;gap:10px}
    .navbar-kider .navbar-brand img{height:42px;width:42px;object-fit:contain}
    .navbar-kider .nav-link{font-weight:700;color:#555;padding:.5rem 1rem;border-radius:25px;margin:0 2px;transition:all .2s}
    .navbar-kider .nav-link:hover{color:var(--green);background:rgba(76,175,80,.08)}

    /* Section title */
    .section-title{font-family:'Fredoka One',cursive;color:var(--green);font-size:2rem;position:relative;display:inline-block}
    .section-title::after{content:'';position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:linear-gradient(90deg,var(--yellow),var(--orange));border-radius:2px}

    /* Hero */
    .hero{background:linear-gradient(135deg,#E8F5E9 0%,#FFF9F0 50%,#E3F2FD 100%);padding:4rem 0;text-align:center;position:relative;overflow:hidden}
    .hero h1{font-family:'Fredoka One',cursive;font-size:2.5rem;color:#2D3436}
    .hero p{font-size:1.1rem;color:#666;max-width:600px;margin:.5rem auto}

    /* Event card */
    .event-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,.06);overflow:hidden;transition:all .3s;border:none;position:relative;height:100%}
    .event-card:hover{transform:translateY(-5px);box-shadow:0 8px 30px rgba(0,0,0,.1)}
    .event-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;border-radius:20px 20px 0 0}
    .event-card:nth-child(3n+1)::before{background:linear-gradient(90deg,var(--green),var(--blue))}
    .event-card:nth-child(3n+2)::before{background:linear-gradient(90deg,var(--pink),var(--orange))}
    .event-card:nth-child(3n+3)::before{background:linear-gradient(90deg,var(--yellow),var(--green))}
    .event-card .card-body{padding:1.5rem}
    .event-card h5{font-family:'Fredoka One',cursive;font-size:1.1rem;margin-bottom:.5rem}
    .event-type{display:inline-block;padding:.2rem .7rem;border-radius:20px;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.05em}
    .type-concert{background:#FCE4EC;color:#C62828}.type-conference{background:#E3F2FD;color:#1565C0}
    .type-sport{background:#E8F5E9;color:#2E7D32}.type-atelier{background:#FFF3E0;color:#E65100}
    .type-festival{background:#F3E5F5;color:#6A1B9A}.type-formation{background:#E0F7FA;color:#00695C}
    .type-exposition{background:#FFF8E1;color:#F57F17}.type-autre{background:#F5F5F5;color:#616161}
    .event-meta{font-size:.85rem;color:#888;display:flex;align-items:center;gap:.4rem;margin-bottom:.3rem}
    .event-meta i{width:16px;text-align:center}
    .event-prix{font-family:'Fredoka One',cursive;font-size:1.2rem;color:var(--green)}
    .event-statut{padding:.2rem .6rem;border-radius:15px;font-size:.7rem;font-weight:700}
    .statut-planifie{background:#FFF3CD;color:#856404}.statut-en_cours{background:#D1E7DD;color:#0F5132}
    .statut-termine{background:#F8D7DA;color:#842029}.statut-annule{background:#E2E3E5;color:#41464B}
    .statut-complet{background:#F3E5F5;color:#6A1B9A}

    /* ══════════════════════════════════════
       MAP SECTION
    ══════════════════════════════════════ */
    .map-section{
      background:linear-gradient(160deg,#E3F2FD 0%,#FFF9F0 100%);
      padding:5rem 0;
    }
    .map-section .section-title{color:var(--blue)}
    .map-section .section-title::after{background:linear-gradient(90deg,var(--blue),var(--purple))}

    #live-map{
      width:100%;height:520px;border-radius:24px;
      box-shadow:0 12px 50px rgba(91,155,213,.2);
      border:3px solid #fff;
      overflow:hidden;
      z-index:1;
    }

    /* Legend */
    .map-legend{
      display:flex;flex-wrap:wrap;gap:10px;
      justify-content:center;margin-top:1.2rem;
    }
    .legend-item{
      display:flex;align-items:center;gap:6px;
      font-size:.8rem;font-weight:700;color:#666;
      background:#fff;padding:.3rem .8rem;
      border-radius:20px;box-shadow:0 2px 8px rgba(0,0,0,.06);
    }
    .legend-dot{width:12px;height:12px;border-radius:50%;flex-shrink:0}

    /* Sidebar événements sur la carte */
    .map-sidebar{
      max-height:520px;overflow-y:auto;
      display:flex;flex-direction:column;gap:10px;
      padding-right:4px;
    }
    .map-sidebar::-webkit-scrollbar{width:4px}
    .map-sidebar::-webkit-scrollbar-track{background:#f0f0f0;border-radius:2px}
    .map-sidebar::-webkit-scrollbar-thumb{background:var(--blue);border-radius:2px}

    .map-ev-card{
      background:#fff;border-radius:14px;padding:12px 14px;
      border:1.5px solid #E3F2FD;cursor:pointer;
      transition:all .2s;box-shadow:0 2px 10px rgba(0,0,0,.04);
    }
    .map-ev-card:hover,.map-ev-card.active{
      border-color:var(--blue);
      box-shadow:0 4px 18px rgba(91,155,213,.2);
      transform:translateX(3px);
    }
    .map-ev-card .ev-name{font-family:'Fredoka One',cursive;font-size:.95rem;color:#2D3436;margin-bottom:3px}
    .map-ev-card .ev-lieu{font-size:.78rem;color:#999;display:flex;align-items:center;gap:4px}
    .map-ev-card .ev-date{font-size:.75rem;color:#bbb}

    /* Pulse animation pour marqueur actif */
    @keyframes pulse{0%{transform:scale(1)}50%{transform:scale(1.3)}100%{transform:scale(1)}}
    .marker-pulse{animation:pulse 1.5s infinite}

    /* Loader carte */
    .map-loader{
      position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);
      text-align:center;color:#666;z-index:10;
      background:rgba(255,255,255,.9);padding:1.5rem 2rem;border-radius:16px;
      box-shadow:0 4px 20px rgba(0,0,0,.1);
    }
    .map-wrapper{position:relative}

    /* Geocoding status */
    .geocode-status{
      font-size:.78rem;color:#999;text-align:center;
      margin-top:.5rem;min-height:18px;
    }

    /* ══════════════════════════════════════
       AVIS SECTION
    ══════════════════════════════════════ */
    .avis-section{background:linear-gradient(160deg,#F0FFF4 0%,#FFF9F0 60%,#EEF4FF 100%);padding:5rem 0}
    .global-score-card{background:#fff;border-radius:28px;box-shadow:0 12px 50px rgba(76,175,80,.12);padding:2.5rem 2rem;text-align:center;border:none;position:relative;overflow:hidden}
    .global-score-card::before{content:'';position:absolute;top:-40px;right:-40px;width:160px;height:160px;background:radial-gradient(circle,rgba(76,175,80,.08) 0%,transparent 70%);border-radius:50%}
    .score-number{font-family:'Fredoka One',cursive;font-size:5rem;line-height:1;background:linear-gradient(135deg,#2D3436,var(--green));-webkit-background-clip:text;-webkit-text-fill-color:transparent}
    .score-stars{font-size:2rem;letter-spacing:3px;margin:.5rem 0}
    .score-stars .s-on,.stars-mini .s-on{color:#FFD93D}
    .score-stars .s-half,.stars-mini .s-half{color:#FFD93D;opacity:.6}
    .score-stars .s-off,.stars-mini .s-off{color:#E0E0E0}
    .score-label{font-size:.9rem;color:#999;font-weight:700}
    .distrib-bar-wrap{display:flex;align-items:center;gap:8px;margin-bottom:5px}
    .distrib-label{font-size:.8rem;color:#888;width:14px;text-align:right;flex-shrink:0}
    .distrib-bar-bg{flex:1;height:8px;background:#F0F0F0;border-radius:4px;overflow:hidden}
    .distrib-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--yellow),var(--orange));transition:width .6s ease}
    .distrib-count{font-size:.75rem;color:#bbb;width:20px;text-align:left;flex-shrink:0}
    .avis-accord .accordion-item{border:1.5px solid #E8F5E9;border-radius:18px !important;margin-bottom:12px;overflow:hidden;box-shadow:0 2px 12px rgba(76,175,80,.05);background:#fff}
    .avis-accord .accordion-button{font-family:'Fredoka One',cursive;font-size:1rem;background:#fff;color:#2D3436;border-radius:18px !important;padding:1rem 1.3rem}
    .avis-accord .accordion-button:not(.collapsed){background:linear-gradient(90deg,#E8F5E9,#F0FFF4);color:var(--green);box-shadow:none}
    .avis-accord .accordion-body{background:#F8FFF8;padding:1.3rem 1.5rem}
    .avis-card{background:#fff;border-radius:16px;padding:1.1rem 1.3rem;margin-bottom:10px;border:1px solid #EEF7EE;box-shadow:0 2px 12px rgba(0,0,0,.04);transition:box-shadow .2s}
    .avis-card:hover{box-shadow:0 4px 20px rgba(76,175,80,.1)}
    .avis-card-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem}
    .avis-author{display:flex;align-items:center;gap:10px}
    .avis-avatar{width:40px;height:40px;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;font-size:.95rem;flex-shrink:0;box-shadow:0 3px 10px rgba(0,0,0,.12)}
    .avis-author-name{font-weight:800;font-size:.9rem;color:#2D3436}
    .avis-author-id{font-size:.75rem;color:#bbb}
    .avis-right{text-align:right}
    .avis-date{font-size:.72rem;color:#ccc;margin-top:2px}
    .avis-text{font-size:.875rem;color:#555;line-height:1.6;margin:0;padding-top:.5rem;border-top:1px solid #F0F0F0}
    .form-avis{background:linear-gradient(135deg,#F0FFF4,#FFF9F0);border-radius:20px;padding:1.5rem;border:2px dashed #A5D6A7;margin-top:1.2rem}
    .form-avis-title{font-family:'Fredoka One',cursive;color:var(--green);font-size:1rem;margin-bottom:1.1rem;display:flex;align-items:center;gap:8px}
    .form-input{border:1.5px solid #E0E0E0;border-radius:12px;padding:.5rem .9rem;font-family:'Nunito',sans-serif;font-size:.9rem;width:100%;background:#fff;transition:border-color .2s;outline:none}
    .form-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(76,175,80,.12)}
    .star-picker-wrap{margin-bottom:.8rem}
    .star-picker-label{font-size:.82rem;color:#777;font-weight:700;margin-bottom:.4rem}
    .star-picker{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:3px}
    .star-picker input{display:none}
    .star-picker label{font-size:2rem;color:#E0E0E0;cursor:pointer;transition:color .12s,transform .12s;line-height:1}
    .star-picker label:hover{transform:scale(1.15)}
    .star-picker input:checked ~ label,.star-picker label:hover,.star-picker label:hover ~ label{color:#FFD93D}
    .btn-avis-submit{background:linear-gradient(135deg,var(--green),#66BB6A);color:#fff;border:none;border-radius:20px;padding:.55rem 1.6rem;font-weight:800;font-size:.9rem;font-family:'Nunito',sans-serif;transition:all .2s;display:inline-flex;align-items:center;gap:7px}
    .btn-avis-submit:hover{transform:translateY(-2px);color:#fff;box-shadow:0 6px 20px rgba(76,175,80,.35)}
    .btn-avis-submit:disabled{opacity:.6;transform:none}
    .avis-feedback{font-size:.85rem;font-weight:700;margin-bottom:.5rem;display:none;padding:.4rem .8rem;border-radius:10px}
    .avis-feedback.ok{background:#E8F5E9;color:#2E7D32}
    .avis-feedback.err{background:#FFEBEE;color:#C62828}
    .avis-empty{text-align:center;padding:2rem 1rem;color:#ccc}
    .avis-empty i{font-size:2rem;margin-bottom:.6rem;display:block;color:#ddd}
    .avis-badge{display:inline-flex;align-items:center;gap:4px;font-size:.75rem;font-weight:700;padding:.15rem .6rem;border-radius:20px;background:#FFF9C4;color:#856404;font-family:'Nunito',sans-serif;margin-left:8px}

    /* Footer */
    footer{background:linear-gradient(135deg,var(--green),#388E3C);color:#fff;padding:2.5rem 0;position:relative;overflow:hidden}
    footer::before{content:'';position:absolute;top:-20px;left:50%;transform:translateX(-50%);width:120%;height:40px;background:#FFF9F0;border-radius:0 0 50% 50%}
    footer .footer-logo{font-family:'Fredoka One',cursive;font-size:1.4rem}

    @keyframes slideIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
    .avis-card-new{animation:slideIn .35s ease}
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

<!-- Hero -->
<section class="hero">
  <div class="container">
    <h1><i class="fas fa-calendar-star"></i> Nos Événements</h1>
    <p>Découvrez les activités et événements organisés pour les enfants de TinyTrack</p>
  </div>
</section>

<!-- Events Grid -->
<section class="py-5">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title"><i class="fas fa-sparkles"></i> Événements à venir</h2>
    </div>
    <div class="row g-4">
      <?php if (empty($events)): ?>
        <div class="col-12 text-center">
          <div class="event-card p-5">
            <i class="fas fa-calendar-times fa-3x" style="color:#ddd"></i>
            <h5 style="color:#999;margin-top:1rem">Aucun événement pour le moment</h5>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($events as $ev): ?>
          <div class="col-md-6 col-lg-4">
            <div class="event-card">
              <div class="card-body d-flex flex-column">
                <div>
                  <span class="event-type type-<?= $ev['type'] ?>"><?= htmlspecialchars($ev['type']) ?></span>
                  <span class="event-statut statut-<?= $ev['statut'] ?> ms-1"><?= $ev['statut'] === 'en_cours' ? 'En cours' : ucfirst($ev['statut']) ?></span>
                  <h5 class="mt-2"><?= htmlspecialchars($ev['titre']) ?></h5>
                  <?php if ($ev['description']): ?>
                    <p style="font-size:.85rem;color:#666;margin-bottom:.8rem"><?= htmlspecialchars(substr($ev['description'],0,100)) ?><?= strlen($ev['description'])>100?'...':'' ?></p>
                  <?php endif; ?>
                  <div class="event-meta"><i class="fas fa-calendar" style="color:var(--blue)"></i> <?= date('d/m/Y',strtotime($ev['date'])) ?></div>
                  <div class="event-meta"><i class="fas fa-clock" style="color:var(--orange)"></i> <?= substr($ev['heure_debut'],0,5) ?> — <?= substr($ev['heure_fin'],0,5) ?></div>
                  <div class="event-meta"><i class="fas fa-map-marker-alt" style="color:var(--pink)"></i> <?= htmlspecialchars($ev['lieu']) ?></div>
                  <div class="event-meta"><i class="fas fa-users" style="color:var(--purple)"></i>
                    <?php if ((int)$ev['capacite_max'] === 0 || $ev['statut'] === 'complet'): ?>
                      <span style="color:#C62828;font-weight:700">Complet</span>
                    <?php elseif ((int)$ev['capacite_max'] <= 5): ?>
                      <span style="color:#E65100;font-weight:700">⚡ <?= $ev['capacite_max'] ?> place<?= $ev['capacite_max']>1?'s':'' ?> restante<?= $ev['capacite_max']>1?'s':'' ?></span>
                    <?php else: ?>
                      <?= $ev['capacite_max'] ?> places disponibles
                    <?php endif; ?>
                  </div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-auto pt-3" style="border-top:1px solid #f0f0f0">
                  <div class="event-prix"><?= $ev['prix']>0 ? number_format($ev['prix'],2).' TND' : 'Gratuit' ?></div>
                  <div class="d-flex gap-2">
                    <!-- Bouton Itinéraire -->
                    <button class="btn btn-sm btn-path"
                      style="background:linear-gradient(135deg,var(--blue),var(--purple));color:#fff;border-radius:20px;font-weight:700;padding:.4rem 1rem;border:none;"
                      onclick="openPathModal('<?= addslashes(htmlspecialchars($ev['titre'])) ?>', '<?= addslashes(htmlspecialchars($ev['lieu'])) ?>')">
                      <i class="fas fa-route"></i> Path
                    </button>
                    <!-- Bouton Réserver -->
                    <?php if ($ev['statut'] !== 'termine' && $ev['statut'] !== 'annule' && $ev['statut'] !== 'complet' && (int)$ev['capacite_max'] > 0): ?>
                      <a href="reserver.php?evenement_id=<?= $ev['id'] ?>" class="btn btn-sm"
                        style="background:linear-gradient(135deg,var(--green),#66BB6A);color:#fff;border-radius:20px;font-weight:700;padding:.4rem 1rem">
                        <i class="fas fa-ticket-alt"></i> Réserver
                      </a>
                    <?php else: ?>
                      <button class="btn btn-sm" disabled
                        style="background:#E0E0E0;color:#999;border-radius:20px;font-weight:700;padding:.4rem 1rem;border:none">
                        <i class="fas fa-ban"></i> Complet
                      </button>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════
     SECTION CARTE EN TEMPS RÉEL — Leaflet + OSM
══════════════════════════════════════════════ -->
<section class="map-section">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="section-title"><i class="fas fa-map-marked-alt"></i> Carte des Événements</h2>
      <p class="text-muted mt-3" style="font-size:.95rem">
        Tous nos événements en temps réel — cliquez sur un marqueur pour les détails
      </p>
    </div>

    <div class="row g-4 align-items-start">
      <!-- Sidebar liste -->
      <div class="col-lg-3">
        <div class="map-sidebar" id="mapSidebar">
          <?php foreach ($events as $ev): ?>
            <div class="map-ev-card" id="sidebar-<?= $ev['id'] ?>"
                 onclick="focusEvent(<?= $ev['id'] ?>)">
              <div class="d-flex justify-content-between align-items-start">
                <div class="ev-name"><?= htmlspecialchars($ev['titre']) ?></div>
                <span class="event-statut statut-<?= $ev['statut'] ?>" style="font-size:.65rem;flex-shrink:0;margin-left:4px">
                  <?= $ev['statut'] === 'en_cours' ? 'En cours' : ucfirst($ev['statut']) ?>
                </span>
              </div>
              <div class="ev-lieu"><i class="fas fa-map-pin" style="color:var(--blue)"></i><?= htmlspecialchars($ev['lieu']) ?></div>
              <div class="ev-date"><i class="fas fa-calendar-alt" style="font-size:.65rem"></i> <?= date('d/m/Y',strtotime($ev['date'])) ?></div>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Carte -->
      <div class="col-lg-9">
        <div class="map-wrapper">
          <div class="map-loader" id="mapLoader">
            <i class="fas fa-spinner fa-spin fa-2x mb-2" style="color:var(--blue)"></i><br>
            <span style="font-weight:700">Chargement de la carte…</span><br>
            <small>Géolocalisation des événements en cours</small>
          </div>
          <div id="live-map"></div>
        </div>
        <div class="geocode-status" id="geocodeStatus"></div>
        <!-- Légende -->
        <div class="map-legend mt-3">
          <div class="legend-item"><div class="legend-dot" style="background:#4CAF50"></div>Planifié</div>
          <div class="legend-item"><div class="legend-dot" style="background:#5B9BD5"></div>En cours</div>
          <div class="legend-item"><div class="legend-dot" style="background:#9C7CDB"></div>Complet</div>
          <div class="legend-item"><div class="legend-dot" style="background:#FF8FAB"></div>Terminé</div>
          <div class="legend-item"><div class="legend-dot" style="background:#9E9E9E"></div>Annulé</div>
          <div class="legend-item"><div class="legend-dot" style="background:#FF5252"></div>Votre position</div>
        </div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════════════
     MODAL ITINÉRAIRE — Path vers événement
══════════════════════════════════════════════ -->
<div class="modal fade" id="pathModal" tabindex="-1">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content" style="border-radius:24px;overflow:hidden;border:none">
      <div class="modal-header" style="background:linear-gradient(135deg,var(--blue),var(--purple));border:none;padding:1.2rem 1.5rem">
        <div>
          <h5 class="modal-title" style="font-family:'Fredoka One',cursive;color:#fff;margin:0">
            <i class="fas fa-route"></i> Itinéraire
          </h5>
          <div id="pathEventName" style="color:rgba(255,255,255,.8);font-size:.85rem;margin-top:2px"></div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0" style="position:relative">
        <!-- Status bar -->
        <div id="pathStatus" style="background:#F8F9FA;padding:.7rem 1.2rem;font-size:.85rem;color:#666;border-bottom:1px solid #eee;display:flex;align-items:center;gap:8px">
          <i class="fas fa-spinner fa-spin" style="color:var(--blue)"></i>
          <span id="pathStatusText">Détection de votre position GPS…</span>
        </div>
        <!-- Info route -->
        <div id="routeInfo" style="display:none;background:linear-gradient(90deg,#E3F2FD,#EDE7F6);padding:.7rem 1.2rem;font-size:.85rem;border-bottom:1px solid #eee">
          <span id="routeDistance" style="font-weight:700;color:var(--blue)"></span>
          &nbsp;·&nbsp;
          <span id="routeDuration" style="font-weight:700;color:var(--purple)"></span>
          &nbsp;·&nbsp;
          <span id="routeDestination" style="color:#666"></span>
        </div>
        <!-- Carte itinéraire -->
        <div id="path-map" style="height:480px;width:100%"></div>
      </div>
      <div class="modal-footer" style="border:none;background:#F8F9FA;padding:.8rem 1.2rem">
        <small class="text-muted"><i class="fas fa-info-circle"></i> Données cartographiques © OpenStreetMap — Itinéraire via OSRM</small>
        <button type="button" class="btn btn-sm ms-auto" data-bs-dismiss="modal"
          style="background:var(--blue);color:#fff;border-radius:20px;font-weight:700;border:none;padding:.4rem 1.2rem">
          Fermer
        </button>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     SECTION AVIS DES PARENTS
══════════════════════════════════════════════ -->
<section class="avis-section">
  <div class="container">
    <div class="text-center mb-5">
      <h2 class="section-title"><i class="fas fa-star"></i> Avis des Parents</h2>
      <p class="text-muted mt-3" style="font-size:.95rem">Ce que les familles pensent de nos événements</p>
    </div>

    <!-- Score global -->
    <div class="row justify-content-center mb-5">
      <div class="col-md-5 col-lg-4">
        <div class="global-score-card">
          <div class="score-number"><?= $moyenne_globale > 0 ? number_format($moyenne_globale,1) : '—' ?></div>
          <div class="score-stars"><?= starsHtml($moyenne_globale) ?></div>
          <div class="score-label mb-3">
            <?= $total_nb_global > 0
              ? $total_nb_global.' avis · '.count($events).' événement'.(count($events)>1?'s':'')
              : 'Soyez les premiers à noter !' ?>
          </div>
          <?php
            $distrib = [5=>0,4=>0,3=>0,2=>0,1=>0];
            foreach ($events as $ev2) {
              foreach ((json_decode($ev2['avis']??'[]',true)?:[]) as $a) {
                $n = intval($a['note']);
                if (isset($distrib[$n])) $distrib[$n]++;
              }
            }
          ?>
          <?php if ($total_nb_global > 0): ?>
          <div class="mt-2">
            <?php foreach ([5,4,3,2,1] as $star): ?>
              <div class="distrib-bar-wrap">
                <div class="distrib-label"><?= $star ?></div>
                <div class="distrib-bar-bg">
                  <div class="distrib-bar-fill" style="width:<?= $total_nb_global>0?round($distrib[$star]/$total_nb_global*100):0 ?>%"></div>
                </div>
                <div class="distrib-count"><?= $distrib[$star] ?></div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Accordéon avis par événement -->
    <?php if (!empty($events)): ?>
    <div class="accordion avis-accord" id="accordAvis">
      <?php foreach ($events as $ev):
        $al  = json_decode($ev['avis'] ?? '[]', true) ?: [];
        $nb  = count($al);
        $moy = $nb > 0 ? round(array_sum(array_column($al,'note'))/$nb, 1) : 0;
      ?>
      <div class="accordion-item">
        <h2 class="accordion-header">
          <button class="accordion-button collapsed" type="button"
                  data-bs-toggle="collapse" data-bs-target="#avis<?= $ev['id'] ?>">
            <span style="flex:1;display:flex;align-items:center;flex-wrap:wrap;gap:4px">
              <?= htmlspecialchars($ev['titre']) ?>
              <?php if ($nb > 0): ?>
                <span class="avis-badge"><i class="fas fa-star"></i> <?= number_format($moy,1) ?> · <?= $nb ?> avis</span>
              <?php else: ?>
                <span class="avis-badge" style="background:#F5F5F5;color:#aaa"><i class="fas fa-star" style="color:#ddd"></i> Aucun avis</span>
              <?php endif; ?>
            </span>
          </button>
        </h2>
        <div id="avis<?= $ev['id'] ?>" class="accordion-collapse collapse" data-bs-parent="#accordAvis">
          <div class="accordion-body">
            <div class="avis-list-<?= $ev['id'] ?>">
            <?php if ($nb > 0): ?>
              <?php foreach (array_reverse($al) as $av):
                $pid   = intval($av['parent_id']);
                $color = avatarColor($pid);
              ?>
                <div class="avis-card">
                  <div class="avis-card-top">
                    <div class="avis-author">
                      <div class="avis-avatar" style="background:<?= $color ?>">P<?= $pid ?></div>
                      <div>
                        <div class="avis-author-name">Parent #<?= $pid ?></div>
                        <div class="avis-author-id"><i class="fas fa-id-badge" style="font-size:.7rem"></i> ID <?= $pid ?></div>
                      </div>
                    </div>
                    <div class="avis-right">
                      <div class="stars-mini" style="font-size:1rem;letter-spacing:1px"><?= starsHtml($av['note']) ?></div>
                      <div class="avis-date"><i class="fas fa-clock" style="font-size:.65rem"></i> <?= date('d/m/Y H:i',strtotime($av['date'])) ?></div>
                    </div>
                  </div>
                  <p class="avis-text"><i class="fas fa-quote-left" style="color:#E0E0E0;font-size:.8rem;margin-right:4px"></i><?= htmlspecialchars($av['commentaire']) ?></p>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="avis-empty" id="empty<?= $ev['id'] ?>">
                <i class="fas fa-comments"></i>
                <span>Aucun avis. Soyez le premier !</span>
              </div>
            <?php endif; ?>
            </div>

            <div class="form-avis">
              <div class="form-avis-title"><i class="fas fa-pen-nib"></i> Laisser un avis</div>
              <form class="avis-form" data-event-id="<?= $ev['id'] ?>">
                <div class="mb-3">
                  <label style="font-size:.82rem;color:#777;font-weight:700;margin-bottom:.3rem;display:block">
                    <i class="fas fa-id-card" style="color:var(--blue)"></i> Votre ID Parent *
                  </label>
                  <input type="number" name="parent_id" class="form-input" placeholder="Ex : 12" min="1" required>
                </div>
                <div class="star-picker-wrap">
                  <div class="star-picker-label"><i class="fas fa-star" style="color:var(--yellow)"></i> Note *</div>
                  <div class="star-picker">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                      <input type="radio" name="note" id="s<?= $ev['id'] ?>_<?= $i ?>" value="<?= $i ?>" required>
                      <label for="s<?= $ev['id'] ?>_<?= $i ?>" title="<?= $i ?> étoile<?= $i>1?'s':'' ?>">★</label>
                    <?php endfor; ?>
                  </div>
                </div>
                <div class="mb-3">
                  <label style="font-size:.82rem;color:#777;font-weight:700;margin-bottom:.3rem;display:block">
                    <i class="fas fa-comment-dots" style="color:var(--green)"></i> Commentaire *
                  </label>
                  <textarea name="commentaire" class="form-input" rows="3"
                    placeholder="Partagez votre expérience..." required maxlength="500" style="resize:none"></textarea>
                </div>
                <div class="avis-feedback"></div>
                <button type="submit" class="btn-avis-submit">
                  <i class="fas fa-paper-plane"></i> Publier mon avis
                </button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>
</section>

<!-- Footer -->
<footer class="text-center">
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem">
    <div class="footer-logo mb-2">TinyTrack</div>
    <p style="opacity:.8;font-size:.95rem">Chaque petit pas compte</p>
    <p style="opacity:.5;font-size:.8rem">ESPRIT 2A19 &copy; 2026 — Rayen Ajili</p>
  </div>
</footer>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<!-- Leaflet Routing Machine -->
<script src="https://unpkg.com/leaflet-routing-machine@3.2.12/dist/leaflet-routing-machine.min.js"></script>

<script>
// ═══════════════════════════════════════════════════
//  DATA — événements depuis PHP
// ═══════════════════════════════════════════════════
const EVENTS_DATA = <?= $events_json ?>;
const SUBMIT_URL  = '<?= htmlspecialchars($submit_url, ENT_QUOTES) ?>';

// ═══════════════════════════════════════════════════
//  COULEURS par statut
// ═══════════════════════════════════════════════════
const STATUS_COLORS = {
  'planifie'  : '#4CAF50',
  'en_cours'  : '#5B9BD5',
  'complet'   : '#9C7CDB',
  'termine'   : '#FF8FAB',
  'annule'    : '#9E9E9E',
};

// ═══════════════════════════════════════════════════
//  GEOCODING — Proxy PHP multi-stratégies
// ═══════════════════════════════════════════════════
const GEOCODE_URL = SUBMIT_URL.replace('submit_avis.php', 'geocode.php');

async function geocodeAddress(address) {
  try {
    const res  = await fetch(GEOCODE_URL + '?q=' + encodeURIComponent(address));
    const data = await res.json();
    if (data.found) {
      console.log('[Geocode]', data.strategy, '→', address, '→', data.lat, data.lng);
      return { lat: data.lat, lng: data.lng };
    }
    console.warn('[Geocode] Introuvable :', address);
  } catch(e) { console.warn('[Geocode] Erreur :', address, e); }
  return null;
}

// ═══════════════════════════════════════════════════
//  CARTE PRINCIPALE — Live map
// ═══════════════════════════════════════════════════
let liveMap       = null;
let liveMarkers   = {};  // eventId → marker
let userMarker    = null;

function createColorMarker(color) {
  return L.divIcon({
    className: '',
    html: `<div style="
      width:34px;height:34px;border-radius:50% 50% 50% 0;
      background:${color};transform:rotate(-45deg);
      box-shadow:0 4px 14px rgba(0,0,0,.25);
      border:3px solid #fff;
    "></div>`,
    iconSize: [34, 34],
    iconAnchor: [17, 34],
    popupAnchor: [0, -36],
  });
}

function createUserMarker() {
  return L.divIcon({
    className: '',
    html: `<div style="
      width:18px;height:18px;border-radius:50%;
      background:#FF5252;border:3px solid #fff;
      box-shadow:0 0 0 6px rgba(255,82,82,.25);
    "></div>`,
    iconSize: [18, 18],
    iconAnchor: [9, 9],
  });
}

function starsText(note) {
  let s = '';
  for (let i = 1; i <= 5; i++) s += i <= note ? '★' : '☆';
  return s;
}

async function initLiveMap() {
  // Centre par défaut : Tunis
  liveMap = L.map('live-map', { zoomControl: true }).setView([36.8065, 10.1815], 11);

  // Tiles OpenStreetMap
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19,
  }).addTo(liveMap);

  const statusEl  = document.getElementById('geocodeStatus');
  const loaderEl  = document.getElementById('mapLoader');
  const bounds    = [];

  // Géocoder chaque événement avec un délai (Nominatim rate limit = 1 req/sec)
  for (let i = 0; i < EVENTS_DATA.length; i++) {
    const ev = EVENTS_DATA[i];
    statusEl.textContent = `Localisation : "${ev.lieu}" (${i+1}/${EVENTS_DATA.length})…`;

    const coords = await geocodeAddress(ev.lieu);
    await new Promise(r => setTimeout(r, 1100)); // respecter le rate limit Nominatim

    if (!coords) {
      console.warn('Impossible de géolocaliser :', ev.lieu);
      continue;
    }

    const color  = STATUS_COLORS[ev.statut] || '#9E9E9E';
    const marker = L.marker([coords.lat, coords.lng], { icon: createColorMarker(color) });

    // Popup riche
    const stars   = ev.note > 0 ? `<span style="color:#FFD93D">${starsText(ev.note)}</span> <span style="color:#888;font-size:.75rem">${ev.note}/5 (${ev.nb_avis} avis)</span>` : '<span style="color:#ccc;font-size:.8rem">Aucun avis</span>';
    const capHtml = ev.capacite === 0
      ? `<span style="color:#C62828;font-weight:700">Complet</span>`
      : `<span style="color:#2E7D32">${ev.capacite} place${ev.capacite > 1 ? 's' : ''}</span>`;

    marker.bindPopup(`
      <div style="font-family:'Nunito',sans-serif;min-width:220px">
        <div style="font-family:'Fredoka One',cursive;font-size:1.05rem;color:#2D3436;margin-bottom:6px">${ev.titre}</div>
        <div style="font-size:.8rem;color:#888;margin-bottom:4px"><i style="color:${color}">●</i> ${ev.statut === 'en_cours' ? 'En cours' : ev.statut.charAt(0).toUpperCase() + ev.statut.slice(1)}</div>
        <div style="font-size:.82rem;color:#666;margin-bottom:2px">📅 ${ev.date} &nbsp; ⏰ ${ev.heure_debut} – ${ev.heure_fin}</div>
        <div style="font-size:.82rem;color:#666;margin-bottom:2px">📍 ${ev.lieu}</div>
        <div style="font-size:.82rem;margin-bottom:4px">👥 ${capHtml} &nbsp; 💰 ${ev.prix}</div>
        <div style="font-size:.82rem;margin-bottom:8px">${stars}</div>
        <a href="reserver.php?evenement_id=${ev.id}"
           style="display:inline-block;background:linear-gradient(135deg,#4CAF50,#66BB6A);color:#fff;padding:4px 14px;border-radius:14px;font-size:.8rem;font-weight:700;text-decoration:none">
          🎟 Réserver
        </a>
      </div>
    `, { maxWidth: 280 });

    marker.addTo(liveMap);
    liveMarkers[ev.id] = { marker, coords };
    bounds.push([coords.lat, coords.lng]);

    // Mettre à jour sidebar
    const sideEl = document.getElementById('sidebar-' + ev.id);
    if (sideEl) sideEl.dataset.lat = coords.lat, sideEl.dataset.lng = coords.lng;
  }

  // Ajuster la vue sur tous les marqueurs
  if (bounds.length > 0) liveMap.fitBounds(bounds, { padding: [40, 40] });

  loaderEl.style.display = 'none';
  statusEl.textContent   = `✅ ${Object.keys(liveMarkers).length} événement(s) localisé(s) sur ${EVENTS_DATA.length}`;

  // Ajouter la position de l'utilisateur si autorisé
  if (navigator.geolocation) {
    navigator.geolocation.watchPosition(pos => {
      const { latitude: lat, longitude: lng } = pos.coords;
      if (!userMarker) {
        userMarker = L.marker([lat, lng], { icon: createUserMarker(), zIndexOffset: 1000 })
          .addTo(liveMap)
          .bindPopup('<b style="font-family:Nunito">📍 Votre position</b>');
      } else {
        userMarker.setLatLng([lat, lng]);
      }
    }, null, { enableHighAccuracy: true, maximumAge: 10000 });
  }
}

// Focus depuis la sidebar
function focusEvent(eventId) {
  const data = liveMarkers[eventId];
  if (!data) return;
  document.querySelectorAll('.map-ev-card').forEach(c => c.classList.remove('active'));
  document.getElementById('sidebar-' + eventId)?.classList.add('active');
  liveMap.setView([data.coords.lat, data.coords.lng], 15, { animate: true });
  data.marker.openPopup();
}

// ═══════════════════════════════════════════════════
//  MODAL ITINÉRAIRE — Path
// ═══════════════════════════════════════════════════
let pathMap       = null;
let routingCtrl   = null;

function openPathModal(titre, lieu) {
  // Reset status
  document.getElementById('pathEventName').textContent  = lieu;
  document.getElementById('pathStatusText').textContent = 'Détection de votre position GPS…';
  document.getElementById('routeInfo').style.display    = 'none';

  const modal = new bootstrap.Modal(document.getElementById('pathModal'));
  modal.show();

  // Initialiser la carte du modal (une seule fois)
  setTimeout(async () => {
    if (!pathMap) {
      pathMap = L.map('path-map').setView([36.8065, 10.1815], 11);
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap',
        maxZoom: 19,
      }).addTo(pathMap);
    } else {
      // Nettoyer le routing précédent
      if (routingCtrl) { pathMap.removeControl(routingCtrl); routingCtrl = null; }
    }

    // Géocoder la destination
    document.getElementById('pathStatusText').textContent = 'Géolocalisation de l\'événement…';
    const destCoords = await geocodeAddress(lieu);
    if (!destCoords) {
      document.getElementById('pathStatusText').textContent = '❌ Impossible de localiser l\'adresse : ' + lieu;
      return;
    }

    // Marqueur destination
    L.marker([destCoords.lat, destCoords.lng], { icon: createColorMarker('#5B9BD5') })
      .addTo(pathMap)
      .bindPopup(`<b style="font-family:Fredoka One">${titre}</b><br><small>${lieu}</small>`)
      .openPopup();

    // Obtenir la position GPS de l'utilisateur
    document.getElementById('pathStatusText').textContent = 'En attente de votre permission GPS…';

    if (!navigator.geolocation) {
      document.getElementById('pathStatusText').textContent = '❌ Géolocalisation non supportée par votre navigateur.';
      pathMap.setView([destCoords.lat, destCoords.lng], 14);
      return;
    }

    navigator.geolocation.getCurrentPosition(
      async (pos) => {
        const userLat = pos.coords.latitude;
        const userLng = pos.coords.longitude;

        document.getElementById('pathStatusText').textContent = 'Calcul de l\'itinéraire en cours…';

        // Marqueur utilisateur
        L.marker([userLat, userLng], { icon: createUserMarker(), zIndexOffset: 1000 })
          .addTo(pathMap)
          .bindPopup('<b style="font-family:Nunito">📍 Votre position</b>')
          .openPopup();

        // Routing via OSRM (gratuit, open source)
        routingCtrl = L.Routing.control({
          waypoints: [
            L.latLng(userLat, userLng),
            L.latLng(destCoords.lat, destCoords.lng),
          ],
          routeWhileDragging: false,
          showAlternatives: false,
          lineOptions: {
            styles: [{ color: '#5B9BD5', weight: 5, opacity: .85 }],
          },
          createMarker: () => null, // On utilise nos propres marqueurs
          router: L.Routing.osrmv1({
            serviceUrl: 'https://router.project-osrm.org/route/v1',
          }),
        }).addTo(pathMap);

        routingCtrl.on('routesfound', function(e) {
          const route    = e.routes[0];
          const dist     = (route.summary.totalDistance / 1000).toFixed(1);
          const mins     = Math.round(route.summary.totalTime / 60);
          const hrs      = Math.floor(mins / 60);
          const minRem   = mins % 60;
          const duration = hrs > 0 ? `${hrs}h${minRem.toString().padStart(2,'0')}min` : `${mins} min`;

          document.getElementById('pathStatusText').textContent  = '✅ Itinéraire trouvé !';
          document.getElementById('routeDistance').textContent   = `🚗 ${dist} km`;
          document.getElementById('routeDuration').textContent   = `⏱ ${duration}`;
          document.getElementById('routeDestination').textContent = `📍 ${lieu}`;
          document.getElementById('routeInfo').style.display     = 'flex';
          document.getElementById('routeInfo').style.alignItems  = 'center';
        });

        routingCtrl.on('routingerror', function() {
          document.getElementById('pathStatusText').textContent = '⚠ Itinéraire impossible — affichage de la destination uniquement.';
          pathMap.setView([destCoords.lat, destCoords.lng], 14);
        });

        // Adapter la vue
        pathMap.fitBounds([
          [userLat, userLng],
          [destCoords.lat, destCoords.lng],
        ], { padding: [60, 60] });
      },
      (err) => {
        let msg = '❌ Impossible d\'obtenir votre position.';
        if (err.code === 1) msg = '❌ Permission GPS refusée. Autorisez la géolocalisation dans votre navigateur.';
        if (err.code === 2) msg = '❌ Position indisponible. Vérifiez votre GPS.';
        if (err.code === 3) msg = '❌ Délai GPS expiré. Réessayez.';
        document.getElementById('pathStatusText').textContent = msg;
        pathMap.setView([destCoords.lat, destCoords.lng], 14);
      },
      { enableHighAccuracy: true, timeout: 15000 }
    );
  }, 400); // délai pour laisser le modal s'ouvrir
}

// Invalider la taille des cartes à l'ouverture du modal
document.getElementById('pathModal').addEventListener('shown.bs.modal', () => {
  if (pathMap) pathMap.invalidateSize();
});

// ═══════════════════════════════════════════════════
//  AVIS — Soumission AJAX
// ═══════════════════════════════════════════════════
const AVATAR_COLORS = ['#4CAF50','#5B9BD5','#FF8FAB','#9C7CDB','#FFA726','#00BCD4','#FF7043','#66BB6A'];

document.querySelectorAll('.avis-form').forEach(form => {
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const eventId  = this.dataset.eventId;
    const feedback = this.querySelector('.avis-feedback');
    const btn      = this.querySelector('.btn-avis-submit');

    if (!this.querySelector('input[name=note]:checked')) {
      showFeedback(feedback, '⚠ Veuillez sélectionner une note.', false); return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi…';

    const data = new FormData(this);
    data.append('evenement_id', eventId);

    try {
      const res  = await fetch(SUBMIT_URL, { method: 'POST', body: data });
      const text = await res.text();
      let json;
      try { json = JSON.parse(text); } catch { throw new Error('Réponse serveur invalide : ' + text.substring(0,200)); }

      if (json.success) {
        showFeedback(feedback, '✅ Avis publié avec succès !', true);
        const av    = json.nouvel_avis;
        const color = AVATAR_COLORS[av.parent_id % AVATAR_COLORS.length];
        const stars = [1,2,3,4,5].map(i => `<span style="color:${i<=av.note?'#FFD93D':'#E0E0E0'}">${i<=av.note?'★':'★'}</span>`).join('');
        const now   = new Date().toLocaleDateString('fr-FR') + ' ' + new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});
        const card  = `
          <div class="avis-card avis-card-new">
            <div class="avis-card-top">
              <div class="avis-author">
                <div class="avis-avatar" style="background:${color}">P${av.parent_id}</div>
                <div>
                  <div class="avis-author-name">Parent #${av.parent_id}</div>
                  <div class="avis-author-id"><i class="fas fa-id-badge" style="font-size:.7rem"></i> ID ${av.parent_id}</div>
                </div>
              </div>
              <div class="avis-right">
                <div style="font-size:1rem;letter-spacing:1px">${stars}</div>
                <div class="avis-date"><i class="fas fa-clock" style="font-size:.65rem"></i> ${now}</div>
              </div>
            </div>
            <p class="avis-text"><i class="fas fa-quote-left" style="color:#E0E0E0;font-size:.8rem;margin-right:4px"></i>${av.commentaire}</p>
          </div>`;
        const list  = document.querySelector('.avis-list-' + eventId);
        const empty = list.querySelector('[id^=empty]');
        if (empty) empty.remove();
        list.insertAdjacentHTML('afterbegin', card);
        this.reset();
        btn.innerHTML = '<i class="fas fa-check"></i> Publié !';
        setTimeout(() => { btn.disabled=false; btn.innerHTML='<i class="fas fa-paper-plane"></i> Publier mon avis'; feedback.style.display='none'; }, 3500);
      } else {
        showFeedback(feedback, '❌ ' + json.message, false);
        btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier mon avis';
      }
    } catch(err) {
      showFeedback(feedback, '❌ ' + err.message, false);
      btn.disabled = false; btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier mon avis';
    }
  });
});

function showFeedback(el, msg, ok) {
  el.textContent = msg;
  el.className   = 'avis-feedback ' + (ok ? 'ok' : 'err');
  el.style.display = 'block';
}

// ═══════════════════════════════════════════════════
//  INIT
// ═══════════════════════════════════════════════════
window.addEventListener('load', initLiveMap);
</script>
<style>
  @keyframes slideIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
  .avis-card-new{animation:slideIn .35s ease}
  /* Masquer le panneau texte du routing machine (on a notre propre UI) */
  .leaflet-routing-container{display:none !important}
</style>
</body>
</html>
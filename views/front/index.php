<?php
include(__DIR__ . '/../../controller/evenementController.php');
$controller = new EvenementController();
$events = $controller->afficher()->fetchAll();

// URL absolue vers submit_avis.php (même dossier que index.php)
$submit_url = rtrim(dirname($_SERVER['PHP_SELF']), '/') . '/submit_avis.php';

// Stats globales
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

// Helper : rendu étoiles grandes
function starsHtml(float $note, string $size = 'big'): string {
    $filled = $size === 'big' ? '★' : '★';
    $html   = '';
    for ($i = 1; $i <= 5; $i++) {
        if ($note >= $i)           $html .= "<span class=\"s-on\">$filled</span>";
        elseif ($note >= $i - 0.5) $html .= "<span class=\"s-half\">$filled</span>";
        else                       $html .= "<span class=\"s-off\">$filled</span>";
    }
    return $html;
}

// Couleurs avatar par parent_id
function avatarColor(int $id): string {
    $colors = ['#4CAF50','#5B9BD5','#FF8FAB','#9C7CDB','#FFA726','#00BCD4','#FF7043','#66BB6A'];
    return $colors[$id % count($colors)];
}
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
  <style>
    :root{
      --green:#4CAF50;--blue:#5B9BD5;--yellow:#FFD93D;
      --pink:#FF8FAB;--orange:#FFA726;--purple:#9C7CDB;
    }
    body{font-family:'Nunito',sans-serif;background:#FFF9F0;color:#333}

    /* ── Navbar ── */
    .navbar-kider{background:#fff;box-shadow:0 3px 20px rgba(0,0,0,.06);padding:.8rem 0;border-bottom:4px solid transparent;border-image:linear-gradient(90deg,var(--green),var(--blue),var(--yellow),var(--pink),var(--orange)) 1}
    .navbar-kider .navbar-brand{font-family:'Fredoka One',cursive;color:var(--green);font-size:1.6rem;display:flex;align-items:center;gap:10px}
    .navbar-kider .navbar-brand img{height:42px;width:42px;object-fit:contain}
    .navbar-kider .nav-link{font-weight:700;color:#555;padding:.5rem 1rem;border-radius:25px;margin:0 2px;transition:all .2s}
    .navbar-kider .nav-link:hover{color:var(--green);background:rgba(76,175,80,.08)}

    /* ── Section title ── */
    .section-title{font-family:'Fredoka One',cursive;color:var(--green);font-size:2rem;position:relative;display:inline-block}
    .section-title::after{content:'';position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:linear-gradient(90deg,var(--yellow),var(--orange));border-radius:2px}

    /* ── Hero ── */
    .hero{background:linear-gradient(135deg,#E8F5E9 0%,#FFF9F0 50%,#E3F2FD 100%);padding:4rem 0;text-align:center;position:relative;overflow:hidden}
    .hero h1{font-family:'Fredoka One',cursive;font-size:2.5rem;color:#2D3436}
    .hero p{font-size:1.1rem;color:#666;max-width:600px;margin:.5rem auto}

    /* ── Event card ── */
    .event-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,.06);overflow:hidden;transition:all .3s;border:none;position:relative}
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

    /* ═══════════════════════════════════════
       SECTION AVIS — design amélioré
    ═══════════════════════════════════════ */
    .avis-section{
      background:linear-gradient(160deg,#F0FFF4 0%,#FFF9F0 60%,#EEF4FF 100%);
      padding:5rem 0;margin-top:0;
    }

    /* Score global */
    .global-score-card{
      background:#fff;border-radius:28px;
      box-shadow:0 12px 50px rgba(76,175,80,.12);
      padding:2.5rem 2rem;text-align:center;
      border:none;position:relative;overflow:hidden;
    }
    .global-score-card::before{
      content:'';position:absolute;top:-40px;right:-40px;
      width:160px;height:160px;
      background:radial-gradient(circle,rgba(76,175,80,.08) 0%,transparent 70%);
      border-radius:50%;
    }
    .score-number{
      font-family:'Fredoka One',cursive;font-size:5rem;
      line-height:1;color:#2D3436;
      background:linear-gradient(135deg,#2D3436,var(--green));
      -webkit-background-clip:text;-webkit-text-fill-color:transparent;
    }
    .score-stars{font-size:2rem;letter-spacing:3px;margin:.5rem 0}
    .score-stars .s-on{color:#FFD93D}
    .score-stars .s-half{color:#FFD93D;opacity:.6}
    .score-stars .s-off{color:#E0E0E0}
    .score-label{font-size:.9rem;color:#999;font-weight:700;letter-spacing:.03em}

    /* Barres de distribution */
    .distrib-bar-wrap{display:flex;align-items:center;gap:8px;margin-bottom:5px}
    .distrib-label{font-size:.8rem;color:#888;width:14px;text-align:right;flex-shrink:0}
    .distrib-bar-bg{flex:1;height:8px;background:#F0F0F0;border-radius:4px;overflow:hidden}
    .distrib-bar-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--yellow),var(--orange));transition:width .6s ease}
    .distrib-count{font-size:.75rem;color:#bbb;width:20px;text-align:left;flex-shrink:0}

    /* Accordéon */
    .avis-accord .accordion-item{
      border:1.5px solid #E8F5E9;border-radius:18px !important;
      margin-bottom:12px;overflow:hidden;
      box-shadow:0 2px 12px rgba(76,175,80,.05);
      background:#fff;
    }
    .avis-accord .accordion-button{
      font-family:'Fredoka One',cursive;font-size:1rem;
      background:#fff;color:#2D3436;border-radius:18px !important;
      padding:1rem 1.3rem;
    }
    .avis-accord .accordion-button:not(.collapsed){
      background:linear-gradient(90deg,#E8F5E9,#F0FFF4);
      color:var(--green);box-shadow:none;
    }
    .avis-accord .accordion-body{background:#F8FFF8;padding:1.3rem 1.5rem}

    /* Étoiles mini */
    .stars-mini .s-on{color:#FFD93D}
    .stars-mini .s-half{color:#FFD93D;opacity:.6}
    .stars-mini .s-off{color:#E0E0E0}

    /* Carte avis individuel */
    .avis-card{
      background:#fff;border-radius:16px;
      padding:1.1rem 1.3rem;margin-bottom:10px;
      border:1px solid #EEF7EE;
      box-shadow:0 2px 12px rgba(0,0,0,.04);
      transition:box-shadow .2s;
    }
    .avis-card:hover{box-shadow:0 4px 20px rgba(76,175,80,.1)}
    .avis-card-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:.6rem}
    .avis-author{display:flex;align-items:center;gap:10px}
    .avis-avatar{
      width:40px;height:40px;border-radius:50%;
      display:flex;align-items:center;justify-content:center;
      color:#fff;font-weight:800;font-size:.95rem;flex-shrink:0;
      box-shadow:0 3px 10px rgba(0,0,0,.12);
    }
    .avis-author-info{}
    .avis-author-name{font-weight:800;font-size:.9rem;color:#2D3436}
    .avis-author-id{font-size:.75rem;color:#bbb}
    .avis-right{text-align:right}
    .avis-date{font-size:.72rem;color:#ccc;margin-top:2px}
    .avis-text{font-size:.875rem;color:#555;line-height:1.6;margin:0;padding-top:.5rem;border-top:1px solid #F0F0F0}

    /* Formulaire avis */
    .form-avis{
      background:linear-gradient(135deg,#F0FFF4,#FFF9F0);
      border-radius:20px;padding:1.5rem;
      border:2px dashed #A5D6A7;margin-top:1.2rem;
    }
    .form-avis-title{
      font-family:'Fredoka One',cursive;color:var(--green);
      font-size:1rem;margin-bottom:1.1rem;
      display:flex;align-items:center;gap:8px;
    }
    .form-input{
      border:1.5px solid #E0E0E0;border-radius:12px;
      padding:.5rem .9rem;font-family:'Nunito',sans-serif;
      font-size:.9rem;width:100%;background:#fff;
      transition:border-color .2s;outline:none;
    }
    .form-input:focus{border-color:var(--green);box-shadow:0 0 0 3px rgba(76,175,80,.12)}

    /* Sélecteur étoiles interactif */
    .star-picker-wrap{margin-bottom:.8rem}
    .star-picker-label{font-size:.82rem;color:#777;font-weight:700;margin-bottom:.4rem}
    .star-picker{display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:3px}
    .star-picker input{display:none}
    .star-picker label{
      font-size:2rem;color:#E0E0E0;cursor:pointer;
      transition:color .12s,transform .12s;line-height:1;
    }
    .star-picker label:hover{transform:scale(1.15)}
    .star-picker input:checked ~ label,
    .star-picker label:hover,
    .star-picker label:hover ~ label{color:#FFD93D}

    /* Bouton envoyer */
    .btn-avis-submit{
      background:linear-gradient(135deg,var(--green),#66BB6A);
      color:#fff;border:none;border-radius:20px;
      padding:.55rem 1.6rem;font-weight:800;font-size:.9rem;
      font-family:'Nunito',sans-serif;
      transition:all .2s;display:inline-flex;align-items:center;gap:7px;
    }
    .btn-avis-submit:hover{
      transform:translateY(-2px);color:#fff;
      box-shadow:0 6px 20px rgba(76,175,80,.35);
    }
    .btn-avis-submit:disabled{opacity:.6;transform:none}

    .avis-feedback{font-size:.85rem;font-weight:700;margin-bottom:.5rem;display:none;padding:.4rem .8rem;border-radius:10px}
    .avis-feedback.ok{background:#E8F5E9;color:#2E7D32}
    .avis-feedback.err{background:#FFEBEE;color:#C62828}

    .avis-empty{
      text-align:center;padding:2rem 1rem;color:#ccc;
    }
    .avis-empty i{font-size:2rem;margin-bottom:.6rem;display:block;color:#ddd}
    .avis-empty span{font-size:.9rem;display:block}

    /* Badge étoiles dans header accordéon */
    .avis-badge{
      display:inline-flex;align-items:center;gap:4px;
      font-size:.75rem;font-weight:700;
      padding:.15rem .6rem;border-radius:20px;
      background:#FFF9C4;color:#856404;
      font-family:'Nunito',sans-serif;margin-left:8px;
    }
    .avis-badge i{color:#FFD93D;font-size:.7rem}

    /* Footer */
    footer{background:linear-gradient(135deg,var(--green),#388E3C);color:#fff;padding:2.5rem 0;margin-top:0;position:relative;overflow:hidden}
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
              <div class="card-body">
                <span class="event-type type-<?= $ev['type'] ?>"><?= htmlspecialchars($ev['type']) ?></span>
                <span class="event-statut statut-<?= $ev['statut'] ?> ms-1"><?= $ev['statut'] === 'en_cours' ? 'En cours' : ucfirst($ev['statut']) ?></span>
                <h5 class="mt-2"><?= htmlspecialchars($ev['titre']) ?></h5>
                <?php if ($ev['description']): ?>
                  <p style="font-size:.85rem;color:#666;margin-bottom:.8rem"><?= htmlspecialchars(substr($ev['description'],0,100)) ?><?= strlen($ev['description'])>100?'...':'' ?></p>
                <?php endif; ?>
                <div class="event-meta"><i class="fas fa-calendar" style="color:var(--blue)"></i> <?= date('d/m/Y',strtotime($ev['date'])) ?></div>
                <div class="event-meta"><i class="fas fa-clock" style="color:var(--orange)"></i> <?= substr($ev['heure_debut'],0,5) ?> — <?= substr($ev['heure_fin'],0,5) ?></div>
                <div class="event-meta"><i class="fas fa-map-marker-alt" style="color:var(--pink)"></i> <?= htmlspecialchars($ev['lieu']) ?></div>
                <div class="event-meta"><i class="fas fa-users" style="color:var(--purple)"></i> Capacité : <?= $ev['capacite_max'] ?> places</div>
                <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:1px solid #f0f0f0">
                  <div class="event-prix"><?= $ev['prix']>0 ? number_format($ev['prix'],2).' TND' : 'Gratuit' ?></div>
                  <?php if ($ev['statut']!=='termine' && $ev['statut']!=='annule'): ?>
                    <a href="reserver.php?evenement_id=<?= $ev['id'] ?>" class="btn btn-sm" style="background:linear-gradient(135deg,var(--green),#66BB6A);color:#fff;border-radius:20px;font-weight:700;padding:.4rem 1.2rem">
                      <i class="fas fa-ticket-alt"></i> Réserver
                    </a>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════════════════
     SECTION AVIS DES PARENTS
═══════════════════════════════════════════ -->
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
          // Distribution des notes 5→1
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

    <!-- Accordéon par événement -->
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

            <!-- Liste des avis -->
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
                      <div class="avis-author-info">
                        <div class="avis-author-name">Parent #<?= $pid ?></div>
                        <div class="avis-author-id"><i class="fas fa-id-badge" style="font-size:.7rem"></i> ID <?= $pid ?></div>
                      </div>
                    </div>
                    <div class="avis-right">
                      <div class="stars-mini" style="font-size:1rem;letter-spacing:1px">
                        <?= starsHtml($av['note'],'mini') ?>
                      </div>
                      <div class="avis-date"><i class="fas fa-clock" style="font-size:.65rem"></i> <?= date('d/m/Y H:i', strtotime($av['date'])) ?></div>
                    </div>
                  </div>
                  <p class="avis-text"><i class="fas fa-quote-left" style="color:#E0E0E0;font-size:.8rem;margin-right:4px"></i><?= htmlspecialchars($av['commentaire']) ?></p>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="avis-empty" id="empty<?= $ev['id'] ?>">
                <i class="fas fa-comments"></i>
                <span>Aucun avis pour cet événement.<br>Soyez le premier !</span>
              </div>
            <?php endif; ?>
            </div>

            <!-- Formulaire -->
            <div class="form-avis">
              <div class="form-avis-title">
                <i class="fas fa-pen-nib" style="color:var(--green)"></i> Laisser un avis
              </div>
              <form class="avis-form" data-event-id="<?= $ev['id'] ?>">

                <!-- ID Parent -->
                <div class="mb-3">
                  <label style="font-size:.82rem;color:#777;font-weight:700;margin-bottom:.3rem;display:block">
                    <i class="fas fa-id-card" style="color:var(--blue)"></i> Votre ID Parent *
                  </label>
                  <input type="number" name="parent_id" class="form-input" placeholder="Ex : 12" min="1" required>
                </div>

                <!-- Étoiles -->
                <div class="star-picker-wrap">
                  <div class="star-picker-label"><i class="fas fa-star" style="color:var(--yellow)"></i> Note *</div>
                  <div class="star-picker" id="picker<?= $ev['id'] ?>">
                    <?php for ($i = 5; $i >= 1; $i--): ?>
                      <input type="radio" name="note" id="s<?= $ev['id'] ?>_<?= $i ?>" value="<?= $i ?>" required>
                      <label for="s<?= $ev['id'] ?>_<?= $i ?>" title="<?= $i ?> étoile<?= $i>1?'s':'' ?>">★</label>
                    <?php endfor; ?>
                  </div>
                </div>

                <!-- Commentaire -->
                <div class="mb-3">
                  <label style="font-size:.82rem;color:#777;font-weight:700;margin-bottom:.3rem;display:block">
                    <i class="fas fa-comment-dots" style="color:var(--green)"></i> Commentaire *
                  </label>
                  <textarea name="commentaire" class="form-input" rows="3"
                    placeholder="Partagez votre expérience..." required maxlength="500"
                    style="resize:none"></textarea>
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

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const SUBMIT_URL = '<?= htmlspecialchars($submit_url, ENT_QUOTES) ?>';
document.querySelectorAll('.avis-form').forEach(form => {
  form.addEventListener('submit', async function(e) {
    e.preventDefault();

    const eventId  = this.dataset.eventId;
    const feedback = this.querySelector('.avis-feedback');
    const btn      = this.querySelector('.btn-avis-submit');

    // Vérif note sélectionnée
    if (!this.querySelector('input[name=note]:checked')) {
      showFeedback(feedback, '⚠ Veuillez sélectionner une note.', false);
      return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours…';

    const data = new FormData(this);
    data.append('evenement_id', eventId);

    try {
      const res  = await fetch(SUBMIT_URL, { method: 'POST', body: data });

      // Vérif que la réponse est du JSON
      const text = await res.text();
      let json;
      try { json = JSON.parse(text); }
      catch { throw new Error('Réponse invalide du serveur : ' + text.substring(0, 200)); }

      if (json.success) {
        showFeedback(feedback, '✅ Avis publié avec succès !', true);

        // Injecter la carte sans rechargement
        const av     = json.nouvel_avis;
        const colors = ['#4CAF50','#5B9BD5','#FF8FAB','#9C7CDB','#FFA726','#00BCD4','#FF7043','#66BB6A'];
        const color  = colors[av.parent_id % colors.length];
        const stars  = [1,2,3,4,5].map(i =>
          `<span class="${i<=av.note?'s-on':'s-off'}" style="color:${i<=av.note?'#FFD93D':'#E0E0E0'}">★</span>`
        ).join('');
        const now    = new Date().toLocaleDateString('fr-FR') + ' ' + new Date().toLocaleTimeString('fr-FR',{hour:'2-digit',minute:'2-digit'});

        const card = `
          <div class="avis-card avis-card-new">
            <div class="avis-card-top">
              <div class="avis-author">
                <div class="avis-avatar" style="background:${color}">P${av.parent_id}</div>
                <div class="avis-author-info">
                  <div class="avis-author-name">Parent #${av.parent_id}</div>
                  <div class="avis-author-id"><i class="fas fa-id-badge" style="font-size:.7rem"></i> ID ${av.parent_id}</div>
                </div>
              </div>
              <div class="avis-right">
                <div class="stars-mini" style="font-size:1rem">${stars}</div>
                <div class="avis-date"><i class="fas fa-clock" style="font-size:.65rem"></i> ${now}</div>
              </div>
            </div>
            <p class="avis-text"><i class="fas fa-quote-left" style="color:#E0E0E0;font-size:.8rem;margin-right:4px"></i>${av.commentaire}</p>
          </div>`;

        const list = document.querySelector(`.avis-list-${eventId}`);
        const empty = list.querySelector('[id^=empty]');
        if (empty) empty.remove();
        list.insertAdjacentHTML('afterbegin', card);

        this.reset();
        btn.innerHTML = '<i class="fas fa-check"></i> Publié !';
        setTimeout(() => {
          btn.disabled = false;
          btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier mon avis';
          feedback.style.display = 'none';
        }, 3500);

      } else {
        showFeedback(feedback, '❌ ' + json.message, false);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier mon avis';
      }

    } catch (err) {
      showFeedback(feedback, '❌ ' + err.message, false);
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-paper-plane"></i> Publier mon avis';
    }
  });
});

function showFeedback(el, msg, ok) {
  el.textContent = msg;
  el.className   = 'avis-feedback ' + (ok ? 'ok' : 'err');
  el.style.display = 'block';
}
</script>
</body>
</html>
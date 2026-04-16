<?php
session_start();
include(__DIR__ . '/../../../Controller/evenementController.php');
$controller = new EvenementController();
$events = $controller->afficher()->fetchAll();
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
    :root{--kider-green:#4CAF50;--kider-blue:#5B9BD5;--kider-yellow:#FFD93D;--kider-pink:#FF8FAB;--kider-orange:#FFA726;--kider-purple:#9C7CDB}
    body{font-family:'Nunito',sans-serif;background:#FFF9F0;color:#333}
    .navbar-kider{background:#fff;box-shadow:0 3px 20px rgba(0,0,0,0.06);padding:0.8rem 0;border-bottom:4px solid transparent;border-image:linear-gradient(90deg,var(--kider-green),var(--kider-blue),var(--kider-yellow),var(--kider-pink),var(--kider-orange)) 1}
    .navbar-kider .navbar-brand{font-family:'Fredoka One',cursive;color:var(--kider-green);font-size:1.6rem;display:flex;align-items:center;gap:10px}
    .navbar-kider .navbar-brand img{height:42px;width:42px;object-fit:contain}
    .navbar-kider .nav-link{font-weight:700;color:#555;padding:0.5rem 1rem;border-radius:25px;margin:0 2px;transition:all 0.2s}
    .navbar-kider .nav-link:hover{color:var(--kider-green);background:rgba(76,175,80,0.08)}
    .section-title{font-family:'Fredoka One',cursive;color:var(--kider-green);font-size:2rem;position:relative;display:inline-block}
    .section-title::after{content:'';position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:linear-gradient(90deg,var(--kider-yellow),var(--kider-orange));border-radius:2px}

    /* Hero */
    .hero{background:linear-gradient(135deg,#E8F5E9 0%,#FFF9F0 50%,#E3F2FD 100%);padding:4rem 0;text-align:center;position:relative;overflow:hidden}
    .hero::before{content:'';position:absolute;top:-50px;right:-50px;width:200px;height:200px;background:rgba(76,175,80,0.06);border-radius:50%}
    .hero h1{font-family:'Fredoka One',cursive;font-size:2.5rem;color:#2D3436}
    .hero p{font-size:1.1rem;color:#666;max-width:600px;margin:0.5rem auto}

    /* Event card */
    .event-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;transition:all 0.3s;border:none;position:relative}
    .event-card:hover{transform:translateY(-5px);box-shadow:0 8px 30px rgba(0,0,0,0.1)}
    .event-card::before{content:'';position:absolute;top:0;left:0;right:0;height:4px;border-radius:20px 20px 0 0}
    .event-card:nth-child(3n+1)::before{background:linear-gradient(90deg,var(--kider-green),var(--kider-blue))}
    .event-card:nth-child(3n+2)::before{background:linear-gradient(90deg,var(--kider-pink),var(--kider-orange))}
    .event-card:nth-child(3n+3)::before{background:linear-gradient(90deg,var(--kider-yellow),var(--kider-green))}
    .event-card .card-body{padding:1.5rem}
    .event-card h5{font-family:'Fredoka One',cursive;font-size:1.1rem;margin-bottom:0.5rem}
    .event-type{display:inline-block;padding:0.2rem 0.7rem;border-radius:20px;font-size:0.7rem;font-weight:700;text-transform:uppercase;letter-spacing:0.05em}
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

    /* Footer */
    footer{background:linear-gradient(135deg,var(--kider-green),#388E3C);color:#fff;padding:2.5rem 0;margin-top:4rem;position:relative;overflow:hidden}
    footer::before{content:'';position:absolute;top:-20px;left:50%;transform:translateX(-50%);width:120%;height:40px;background:#FFF9F0;border-radius:0 0 50% 50%}
    footer .footer-logo{font-family:'Fredoka One',cursive;font-size:1.4rem}
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/TinyTrack/View/FrontOffice/enfants/list.php">
      <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack">
      TinyTrack
    </a>
    <div class="ms-auto d-flex gap-2">
      <a href="/TinyTrack/View/FrontOffice/enfants/list.php" class="btn" style="background:linear-gradient(135deg,#4CAF50,#66BB6A);color:#fff;border-radius:25px;font-weight:700;padding:0.4rem 1.2rem;font-size:0.85rem;"><i class="fas fa-arrow-left"></i> Page principale</a>
      <a href="/TinyTrack/View/auth/logout.php" class="btn" style="background:#dc3545;color:#fff;border-radius:25px;font-weight:700;padding:0.4rem 1.2rem;font-size:0.85rem;" onclick="return confirm('Se déconnecter ?')"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
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
            <i class="fas fa-calendar-times fa-3x" style="color:#ddd;"></i>
            <h5 style="color:#999;margin-top:1rem;">Aucun événement pour le moment</h5>
          </div>
        </div>
      <?php else: ?>
        <?php foreach ($events as $ev): ?>
          <div class="col-md-6 col-lg-4">
            <div class="event-card">
              <div class="card-body">
                <!-- Type badge -->
                <span class="event-type type-<?= $ev['type'] ?>"><?= htmlspecialchars($ev['type']) ?></span>
                <span class="event-statut statut-<?= $ev['statut'] ?> ms-1"><?= $ev['statut'] === 'en_cours' ? 'En cours' : ucfirst($ev['statut']) ?></span>

                <h5 class="mt-2"><?= htmlspecialchars($ev['titre']) ?></h5>

                <?php if ($ev['description']): ?>
                  <p style="font-size:0.85rem;color:#666;margin-bottom:0.8rem;"><?= htmlspecialchars(substr($ev['description'], 0, 100)) ?><?= strlen($ev['description']) > 100 ? '...' : '' ?></p>
                <?php endif; ?>

                <div class="event-meta"><i class="fas fa-calendar" style="color:var(--kider-blue);"></i> <?= date('d/m/Y', strtotime($ev['date'])) ?></div>
                <div class="event-meta"><i class="fas fa-clock" style="color:var(--kider-orange);"></i> <?= substr($ev['heure_debut'],0,5) ?> — <?= substr($ev['heure_fin'],0,5) ?></div>
                <div class="event-meta"><i class="fas fa-map-marker-alt" style="color:var(--kider-pink);"></i> <?= htmlspecialchars($ev['lieu']) ?></div>
                <div class="event-meta"><i class="fas fa-users" style="color:var(--kider-purple);"></i> Capacité : <?= $ev['capacite_max'] ?> places</div>

                <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:1px solid #f0f0f0;">
                  <div class="event-prix">
                    <?= $ev['prix'] > 0 ? number_format($ev['prix'], 2) . ' TND' : 'Gratuit' ?>
                  </div>
                  <?php if ($ev['statut'] !== 'termine' && $ev['statut'] !== 'annule' && (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'educateur')): ?>
                    <a href="reserver.php?evenement_id=<?= $ev['id'] ?>" class="btn btn-sm" style="background:linear-gradient(135deg,var(--kider-green),#66BB6A);color:#fff;border-radius:20px;font-weight:700;padding:0.4rem 1.2rem;"><i class="fas fa-ticket-alt"></i> Réserver</a>
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

<!-- Footer -->
<footer class="text-center">
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem;">
    <div class="footer-logo mb-2">TinyTrack</div>
    <p style="opacity:0.8;font-size:0.95rem;">Chaque petit pas compte</p>
    <p style="opacity:0.5;font-size:0.8rem;">ESPRIT 2A19 &copy; 2026 — Rayen Ajili</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

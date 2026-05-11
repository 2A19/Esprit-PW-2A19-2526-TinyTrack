<?php
session_start();
require_once(__DIR__ . '/../../controller/reservationController.php');

$resController = new ReservationController();
$allRes = $resController->afficher();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>TinyTrack — Mes Réservations</title>
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
    .res-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);overflow:hidden;transition:all 0.3s;border:none;padding:1.5rem}
    .res-card:hover{transform:translateY(-3px);box-shadow:0 8px 30px rgba(0,0,0,0.1)}
    .res-statut{padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700}
    .statut-confirmee{background:#D1E7DD;color:#0F5132}
    .statut-en_attente{background:#FFF3CD;color:#856404}
    .statut-annulee{background:#F8D7DA;color:#842029}
    .paiement-paye{background:#D1E7DD;color:#0F5132}
    .paiement-non_paye{background:#E2E3E5;color:#41464B}
    footer{background:linear-gradient(135deg,var(--kider-green),#388E3C);color:#fff;padding:2.5rem 0;margin-top:4rem;position:relative;overflow:hidden}
    footer::before{content:'';position:absolute;top:-20px;left:50%;transform:translateX(-50%);width:120%;height:40px;background:#FFF9F0;border-radius:0 0 50% 50%}
    footer .footer-logo{font-family:'Fredoka One',cursive;font-size:1.4rem}
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="images/logo.png" alt="TinyTrack">
      TinyTrack
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navKider"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navKider">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
        <li class="nav-item"><a class="nav-link active" href="mesreservations.php"><i class="fas fa-ticket-alt"></i> Mes Réservations</a></li>
        <li class="nav-item"><a class="nav-link" href="../back/index.php"><i class="fas fa-lock"></i> BackOffice</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="section-title"><i class="fas fa-ticket-alt"></i> Mes Réservations</h2>
      <p class="mt-3" style="color:#888;">Consultez l'état de vos réservations</p>
    </div>

    <div class="text-end mb-3">
      <a href="reserver.php" class="btn" style="background:linear-gradient(135deg,var(--kider-green),#66BB6A);color:#fff;border-radius:25px;font-weight:700;padding:0.5rem 1.5rem;"><i class="fas fa-plus-circle"></i> Nouvelle réservation</a>
    </div>

    <?php if (empty($allRes)): ?>
      <div class="text-center py-5">
        <div class="res-card d-inline-block p-5">
          <i class="fas fa-ticket-alt fa-3x" style="color:#ddd;"></i>
          <h5 style="color:#999;margin-top:1rem;">Aucune réservation pour le moment</h5>
          <a href="reserver.php" class="btn btn-sm mt-2" style="background:var(--kider-green);color:#fff;border-radius:20px;font-weight:700;">Faire une réservation</a>
        </div>
      </div>
    <?php else: ?>
      <div class="row g-4">
        <?php foreach ($allRes as $res): ?>
          <div class="col-md-6 col-lg-4">
            <div class="res-card">
              <div class="d-flex justify-content-between align-items-start mb-2">
                <h5 style="font-family:'Fredoka One',cursive;font-size:1rem;margin:0;">
                  <i class="fas fa-calendar-alt text-success"></i> <?= htmlspecialchars($res['evenement_titre'] ?? 'Événement supprimé') ?>
                </h5>
                <span class="res-statut statut-<?= $res['statut'] ?>">
                  <?php
                    $sl = ['confirmee'=>'Confirmée','en_attente'=>'En attente','annulee'=>'Annulée'];
                    echo $sl[$res['statut']] ?? $res['statut'];
                  ?>
                </span>
              </div>

              <div style="font-size:0.85rem;color:#888;line-height:1.8;">
                <div><i class="fas fa-child" style="color:var(--kider-orange);width:18px;"></i> Enfant ID : <strong><?= $res['enfant_id'] ?? '-' ?></strong></div>
                <div><i class="fas fa-user" style="color:var(--kider-blue);width:18px;"></i> Parent ID : <strong><?= $res['parent_id'] ?? '-' ?></strong></div>
                <div><i class="fas fa-users" style="color:var(--kider-purple);width:18px;"></i> Accompagnants : <strong><?= $res['nb_accompagnants'] ?></strong></div>
                <div><i class="fas fa-clock" style="color:var(--kider-pink);width:18px;"></i> <?= date('d/m/Y H:i', strtotime($res['date_reservation'])) ?></div>
                <?php if (!empty($res['commentaire'])): ?>
                  <div class="mt-1"><i class="fas fa-comment" style="color:var(--kider-green);width:18px;"></i> <?= htmlspecialchars(substr($res['commentaire'], 0, 80)) ?><?= strlen($res['commentaire']) > 80 ? '...' : '' ?></div>
                <?php endif; ?>
              </div>

              <div class="d-flex justify-content-between align-items-center mt-3 pt-2" style="border-top:1px solid #f0f0f0;">
                <span class="res-statut paiement-<?= $res['paiement'] ?>">
                  <i class="fas fa-<?= $res['paiement'] === 'paye' ? 'check-circle' : 'clock' ?>"></i>
                  <?= $res['paiement'] === 'paye' ? 'Payé' : 'Non payé' ?>
                </span>
                <small style="color:#ccc;">#<?= $res['id'] ?></small>
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
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem;">
    <div class="footer-logo mb-2">TinyTrack</div>
    <p style="opacity:0.8;font-size:0.95rem;">Chaque petit pas compte</p>
    <p style="opacity:0.5;font-size:0.8rem;">ESPRIT 2A19 &copy; 2026 — Rayen Ajili</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

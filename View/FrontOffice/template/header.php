<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Portail Parent</title>
  <!-- Bootstrap 5 -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <!-- Google Fonts (Kider playful) -->
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <!-- Playful Theme -->
  <link rel="stylesheet" href="/TinyTrack/assets/css/playful.css?v=3">
  <style>
    :root {
      --kider-green: #4CAF50;
      --kider-blue: #5B9BD5;
      --kider-yellow: #FFD93D;
      --kider-pink: #FF8FAB;
      --kider-orange: #FFA726;
      --kider-purple: #9C7CDB;
      --kider-bg: #FFF9F0;
    }

    body {
      font-family: 'Nunito', sans-serif;
      background: var(--kider-bg);
      color: #333;
      position: relative;
      overflow-x: hidden;
    }

    /* === DECORATIVE BACKGROUND === */
    body::before {
      content: '';
      position: fixed;
      top: -120px;
      right: -80px;
      width: 300px;
      height: 300px;
      background: rgba(91,155,213,0.08);
      border-radius: 50%;
      pointer-events: none;
      z-index: 0;
    }
    body::after {
      content: '';
      position: fixed;
      bottom: -100px;
      left: -60px;
      width: 250px;
      height: 250px;
      background: rgba(255,143,171,0.08);
      border-radius: 50%;
      pointer-events: none;
      z-index: 0;
    }

    /* === NAVBAR === */
    .navbar-kider {
      background: #fff;
      box-shadow: 0 3px 20px rgba(0,0,0,0.06);
      padding: 0.8rem 0;
      border-bottom: 4px solid transparent;
      border-image: linear-gradient(90deg, var(--kider-green), var(--kider-blue), var(--kider-yellow), var(--kider-pink), var(--kider-orange)) 1;
    }
    .navbar-kider .navbar-brand {
      font-family: 'Fredoka One', cursive;
      color: var(--kider-green);
      font-size: 1.6rem;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .navbar-kider .navbar-brand img {
      height: 42px;
      width: 42px;
      object-fit: contain;
    }
    .navbar-kider .nav-link {
      font-weight: 700;
      color: #555;
      padding: 0.5rem 1rem;
      border-radius: 25px;
      margin: 0 2px;
      transition: all 0.2s;
      font-size: 0.9rem;
    }
    .navbar-kider .nav-link:hover {
      color: var(--kider-green);
      background: rgba(76,175,80,0.08);
    }
    .navbar-kider .nav-link.active {
      color: var(--kider-green) !important;
      background: rgba(76,175,80,0.12);
      font-weight: 800;
      box-shadow: 0 2px 0 var(--kider-green);
    }
    .navbar-kider .nav-link i {
      margin-right: 4px;
    }
    .navbar-kider .nav-link-profil {
      width: 42px; height: 42px; padding: 0 !important;
      border-radius: 50%; display: inline-flex; align-items: center; justify-content: center;
      background: linear-gradient(135deg, var(--kider-blue), #90CAF9);
      color: #fff !important;
      box-shadow: 0 3px 10px rgba(91,155,213,0.25);
      transition: all 0.2s;
    }
    .navbar-kider .nav-link-profil:hover {
      transform: translateY(-2px); box-shadow: 0 5px 14px rgba(91,155,213,0.35);
      background: linear-gradient(135deg, #1976D2, var(--kider-blue));
    }
    .navbar-kider .nav-link-profil i { margin: 0 !important; font-size: 1.1rem; }
    .navbar-kider .nav-link-profil.active { background: linear-gradient(135deg, var(--kider-green), #66BB6A); color: #fff !important; box-shadow: 0 3px 10px rgba(76,175,80,0.3) !important; }

    /* === HEADINGS === */
    .section-title {
      font-family: 'Fredoka One', cursive;
      color: var(--kider-green);
      font-size: 2rem;
      position: relative;
      display: inline-block;
    }
    .section-title::after {
      content: '';
      position: absolute;
      bottom: -6px;
      left: 50%;
      transform: translateX(-50%);
      width: 60px;
      height: 4px;
      background: linear-gradient(90deg, var(--kider-yellow), var(--kider-orange));
      border-radius: 2px;
    }

    /* === CARDS === */
    .card-kider {
      border: none;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      transition: all 0.3s;
      background: #fff;
      position: relative;
      overflow: hidden;
    }
    .card-kider::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--kider-green), var(--kider-blue));
      border-radius: 20px 20px 0 0;
    }
    .card-kider:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    }

    /* Color variants for cards */
    .card-kider:nth-child(3n+1)::before { background: linear-gradient(90deg, var(--kider-green), var(--kider-blue)); }
    .card-kider:nth-child(3n+2)::before { background: linear-gradient(90deg, var(--kider-pink), var(--kider-orange)); }
    .card-kider:nth-child(3n+3)::before { background: linear-gradient(90deg, var(--kider-yellow), var(--kider-green)); }

    /* === CHILD AVATAR === */
    .avatar-circle {
      width: 85px;
      height: 85px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      position: relative;
    }
    .avatar-circle.boy {
      background: linear-gradient(135deg, #E3F2FD, #BBDEFB);
    }
    .avatar-circle.girl {
      background: linear-gradient(135deg, #FCE4EC, #F8BBD0);
    }
    .avatar-circle i {
      font-size: 2.2rem;
    }
    .avatar-circle::after {
      content: '';
      position: absolute;
      inset: -4px;
      border-radius: 50%;
      border: 3px dashed rgba(0,0,0,0.06);
    }

    /* === BADGES === */
    .badge-boy {
      background: linear-gradient(135deg, #5B9BD5, #64B5F6);
      color: #fff;
      border-radius: 20px;
      padding: 0.3rem 0.8rem;
      font-weight: 700;
      font-size: 0.75rem;
    }
    .badge-girl {
      background: linear-gradient(135deg, #FF8FAB, #F48FB1);
      color: #fff;
      border-radius: 20px;
      padding: 0.3rem 0.8rem;
      font-weight: 700;
      font-size: 0.75rem;
    }

    /* === MEDICAL INFO BOX === */
    .medical-box {
      background: linear-gradient(135deg, #F5F5F5, #FAFAFA);
      border-radius: 14px;
      padding: 1rem;
      font-size: 0.85rem;
      border: 1px solid rgba(0,0,0,0.04);
    }
    .medical-box p {
      margin-bottom: 0.4rem;
    }
    .medical-box i {
      width: 18px;
      text-align: center;
    }

    /* === BUTTONS === */
    .btn-kider {
      background: linear-gradient(135deg, var(--kider-green), #66BB6A);
      color: #fff;
      border: none;
      border-radius: 25px;
      padding: 0.6rem 1.8rem;
      font-weight: 700;
      font-family: 'Nunito', sans-serif;
      transition: all 0.2s;
      box-shadow: 0 4px 15px rgba(76,175,80,0.2);
    }
    .btn-kider:hover {
      background: linear-gradient(135deg, #388E3C, #4CAF50);
      color: #fff;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(76,175,80,0.3);
    }

    /* === FOOTER === */
    footer {
      background: linear-gradient(135deg, var(--kider-green), #388E3C);
      color: #fff;
      padding: 2.5rem 0;
      margin-top: 4rem;
      position: relative;
      overflow: hidden;
    }
    footer::before {
      content: '';
      position: absolute;
      top: -30px;
      left: 50%;
      transform: translateX(-50%);
      width: 120%;
      height: 60px;
      background: var(--kider-bg);
      border-radius: 0 0 50% 50%;
    }
    footer a { color: #fff; }
    footer .footer-logo {
      font-family: 'Fredoka One', cursive;
      font-size: 1.4rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    footer .footer-logo img {
      height: 35px;
      width: 35px;
      object-fit: contain;
    }

    /* === EMPTY STATE === */
    .empty-state {
      padding: 3rem;
    }
    .empty-state i {
      color: #ddd;
    }

    /* === FLOATING DECORATIONS === */
    .deco-star {
      position: fixed;
      pointer-events: none;
      z-index: 0;
      opacity: 0.05;
      font-size: 3rem;
      color: var(--kider-yellow);
    }

    /* === STATUS BADGE === */
    .status-inscrit {
      background: linear-gradient(135deg, #4CAF50, #81C784);
      color: #fff;
      border-radius: 20px;
      padding: 0.25rem 0.8rem;
      font-size: 0.75rem;
      font-weight: 700;
    }
    .status-archive {
      background: #B0BEC5;
      color: #fff;
      border-radius: 20px;
      padding: 0.25rem 0.8rem;
      font-size: 0.75rem;
      font-weight: 700;
    }
  </style>
</head>
<body>

<!-- Playful Background (ambiance) -->
<div class="playful-bg">
  <div class="bubble bubble-1"></div>
  <div class="bubble bubble-2"></div>
  <div class="bubble bubble-3"></div>
  <div class="bubble bubble-4"></div>
  <div class="bubble bubble-5"></div>
  <div class="sparkle sparkle-1"></div>
  <div class="sparkle sparkle-2"></div>
  <div class="sparkle sparkle-3"></div>
  <div class="sparkle sparkle-4"></div>
  <div class="sparkle sparkle-5"></div>
  <div class="sparkle sparkle-6"></div>
  <div class="sparkle sparkle-7"></div>
  <div class="sparkle sparkle-8"></div>
</div>

<!-- Kids-store decorative shapes (SVG stars, arcs, blobs) -->
<div class="kids-shape shape-star-1">
  <svg viewBox="0 0 24 24" fill="#FFD93D"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg>
</div>
<div class="kids-shape shape-star-2">
  <svg viewBox="0 0 24 24" fill="#FF8FAB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg>
</div>
<div class="kids-shape shape-star-3">
  <svg viewBox="0 0 24 24" fill="#5B9BD5"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg>
</div>
<div class="kids-shape shape-star-4">
  <svg viewBox="0 0 24 24" fill="#9C7CDB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg>
</div>
<div class="kids-shape shape-arc-1">
  <svg viewBox="0 0 90 60"><path d="M5 55 A40 40 0 0 1 85 55" fill="none" stroke="#FF8FAB" stroke-width="8" stroke-linecap="round"/><path d="M18 55 A27 27 0 0 1 72 55" fill="none" stroke="#FFD93D" stroke-width="8" stroke-linecap="round"/><path d="M30 55 A15 15 0 0 1 60 55" fill="none" stroke="#26A69A" stroke-width="8" stroke-linecap="round"/></svg>
</div>
<div class="kids-shape shape-arc-2">
  <svg viewBox="0 0 110 70"><path d="M5 65 A50 50 0 0 1 105 65" fill="none" stroke="#9C7CDB" stroke-width="9" stroke-linecap="round"/><path d="M22 65 A33 33 0 0 1 88 65" fill="none" stroke="#FFA726" stroke-width="9" stroke-linecap="round"/><path d="M38 65 A17 17 0 0 1 72 65" fill="none" stroke="#5B9BD5" stroke-width="9" stroke-linecap="round"/></svg>
</div>
<div class="kids-shape shape-blob-1">
  <svg viewBox="0 0 200 200"><path fill="#FFD93D" d="M43.8,-58.6C56.4,-49.3,65.8,-35.1,70.2,-19.6C74.6,-4.1,74,12.7,67.6,27.1C61.1,41.5,48.7,53.5,34.2,61.5C19.8,69.5,3.2,73.5,-13.3,71.4C-29.8,69.3,-46.3,61.2,-58,48.3C-69.7,35.4,-76.6,17.7,-75.9,0.4C-75.2,-16.9,-66.8,-33.8,-54.2,-43.4C-41.5,-53,-24.7,-55.4,-8.8,-44.9C7.1,-34.4,31.2,-67.9,43.8,-58.6Z" transform="translate(100 100)"/></svg>
</div>
<div class="kids-shape shape-blob-2">
  <svg viewBox="0 0 200 200"><path fill="#FF8FAB" d="M41.3,-60.1C52.1,-51.9,58.2,-37.4,63.6,-22.3C69,-7.2,73.7,8.5,70.5,22.8C67.2,37.1,56,50,42.3,58.6C28.6,67.1,12.5,71.3,-3.5,75.5C-19.5,79.8,-39,84.1,-51.3,75.4C-63.6,66.8,-68.7,45.2,-72.5,25.3C-76.3,5.4,-78.7,-12.9,-71.4,-25.9C-64.2,-38.9,-47.3,-46.7,-32.4,-53.8C-17.5,-60.8,-4.6,-67.2,8.5,-68.8C21.5,-70.3,30.5,-68.2,41.3,-60.1Z" transform="translate(100 100)"/></svg>
</div>
<div class="kids-shape shape-dot-1"></div>
<div class="kids-shape shape-dot-2"></div>
<div class="kids-shape shape-dot-3"></div>
<div class="kids-shape shape-squiggle-1">
  <svg viewBox="0 0 70 28"><path d="M2 14 Q10 2 20 14 T40 14 T60 14 T68 14" stroke="#26A69A" stroke-width="4" fill="none" stroke-linecap="round"/></svg>
</div>
<div class="kids-shape shape-squiggle-2">
  <svg viewBox="0 0 80 28"><path d="M2 14 Q12 2 22 14 T44 14 T66 14 T78 14" stroke="#FFA726" stroke-width="4" fill="none" stroke-linecap="round"/></svg>
</div>

<?php
// Determine which navbar link is "active" based on the current URL path.
// Returns 'active' + custom style if the current page matches the given key.
$__navPath = strtolower($_SERVER['REQUEST_URI'] ?? '');
function navActive(array $patterns, $currentPath) {
    foreach ($patterns as $p) {
        if (strpos($currentPath, $p) !== false) return true;
    }
    return false;
}
?>
<!-- Navbar Kider -->
<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/TinyTrack/mes-enfants">
      <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack">
      TinyTrack
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarKider">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarKider">
      <ul class="navbar-nav ms-auto">
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <!-- Admin : navbar groupée en dropdowns -->
        <?php
          $gestionActive = navActive(['/enfants','/educateurs','/parents','/approbation'], $__navPath);
          $modulesActive = navActive(['/evenements','/rapports','/activites'], $__navPath);
          $commActive    = navActive(['reclamationback','reclamationfront','reponsesback','communication_backend','communication.php'], $__navPath);
        ?>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $gestionActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-users-cog"></i> Gestion
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);">
            <li><a class="dropdown-item" href="/TinyTrack/enfants"><i class="fas fa-child" style="color:#4CAF50;width:20px;"></i> Enfants</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/View/BackOffice/inscriptions/gestion.php"><i class="fas fa-user-plus" style="color:#26A69A;width:20px;"></i> Inscriptions</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/educateurs"><i class="fas fa-chalkboard-teacher" style="color:#5B9BD5;width:20px;"></i> Éducateurs</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/parents"><i class="fas fa-users" style="color:#FF8FAB;width:20px;"></i> Parents</a></li>
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="/TinyTrack/approbation"><i class="fas fa-hourglass-half" style="color:#FFA726;width:20px;"></i> En attente</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $modulesActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-puzzle-piece"></i> Modules
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);">
            <li><a class="dropdown-item" href="/TinyTrack/evenements"><i class="fas fa-calendar-alt" style="color:#FFA726;width:20px;"></i> Événements</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/rapports"><i class="fas fa-book" style="color:#9C7CDB;width:20px;"></i> Rapports</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/activites"><i class="fas fa-paint-brush" style="color:#FFD93D;width:20px;"></i> Activités</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $commActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-comments"></i> Communication
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);">
            <li><a class="dropdown-item" href="/TinyTrack/View/BackOffice/communication/communication_Backend.php"><i class="fas fa-comments" style="color:#5B9BD5;width:20px;"></i> Messagerie</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/View/BackOffice/reclamations/reclamationBack.php"><i class="fas fa-exclamation-circle" style="color:#EF5350;width:20px;"></i> Réclamations</a></li>
          </ul>
        </li>
        <li class="nav-item ms-2"><a class="nav-link nav-link-profil <?= navActive(['/profil'], $__navPath) ? 'active' : '' ?>" href="/TinyTrack/profil" title="Mon profil"><i class="fas fa-user-circle"></i></a></li>
        <?php elseif ($_SESSION['user_role'] === 'educateur'): ?>
        <!-- Éducateur : navbar groupée en dropdowns -->
        <?php
          $eduWorkActive    = navActive(['/evenements/','mesrapports','editrapport','deleterapport','analyserapport','addrapport','listactiviteseducateur','/activites'], $__navPath);
          $eduContactActive = navActive(['/parents','communication_backend','communication.php','reclamationback','reponsesback'], $__navPath);
        ?>
        <li class="nav-item"><a class="nav-link <?= navActive(['/dashboard_educateur.php'], $__navPath) ? 'active' : '' ?>" href="/TinyTrack/dashboard/educateur"><i class="fas fa-th-large"></i> Tableau de bord</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $eduWorkActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-briefcase"></i> Mon travail
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);">
            <li><a class="dropdown-item" href="/TinyTrack/evenements/parent"><i class="fas fa-calendar-alt" style="color:#FFA726;width:20px;"></i> Événements</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/View/FrontOffice/rapports/mesRapports.php"><i class="fas fa-book" style="color:#9C7CDB;width:20px;"></i> Mes rapports</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/View/FrontOffice/rapports/listActivitesEducateur.php?id_educateur=<?= (int)$_SESSION['user_id'] ?>"><i class="fas fa-paint-brush" style="color:#FFD93D;width:20px;"></i> Mes activités</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $eduContactActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-comments"></i> Contacts
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);">
            <li><a class="dropdown-item" href="/TinyTrack/parents"><i class="fas fa-users" style="color:#FF8FAB;width:20px;"></i> Parents</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/View/BackOffice/communication/communication_Backend.php"><i class="fas fa-comments" style="color:#5B9BD5;width:20px;"></i> Communication</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/View/BackOffice/reclamations/reclamationBack.php"><i class="fas fa-exclamation-circle" style="color:#EF5350;width:20px;"></i> Réclamations</a></li>
          </ul>
        </li>
        <li class="nav-item ms-2"><a class="nav-link nav-link-profil <?= navActive(['/profil'], $__navPath) ? 'active' : '' ?>" href="/TinyTrack/educateurs/profil" title="Mon profil"><i class="fas fa-user-circle"></i></a></li>
        <?php else: ?>
        <!-- Parent : navbar groupée en dropdowns -->
        <?php
          $parentActiveActive  = navActive(['/evenements/','/mes-reservations','/rapports/'], $__navPath);
          $parentContactActive = navActive(['reclamationfront','/messages.php','communication.php'], $__navPath);
        ?>
        <li class="nav-item"><a class="nav-link <?= navActive(['/enfants/'], $__navPath) ? 'active' : '' ?>" href="/TinyTrack/mes-enfants"><i class="fas fa-child"></i> Mon enfant</a></li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $parentActiveActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-calendar-day"></i> Vie de la crèche
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);">
            <li><a class="dropdown-item" href="/TinyTrack/evenements/parent"><i class="fas fa-calendar-alt" style="color:#FFA726;width:20px;"></i> Événements</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/mes-reservations"><i class="fas fa-ticket-alt" style="color:#FFD93D;width:20px;"></i> Mes réservations</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/rapports/parent"><i class="fas fa-book" style="color:#9C7CDB;width:20px;"></i> Rapports</a></li>
          </ul>
        </li>
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle <?= $parentContactActive ? 'active' : '' ?>" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="fas fa-comments"></i> Communication
          </a>
          <ul class="dropdown-menu dropdown-menu-end" style="border-radius:16px;box-shadow:0 8px 24px rgba(0,0,0,0.1);">
            <li><a class="dropdown-item" href="/TinyTrack/View/FrontOffice/communication/communication.php"><i class="fas fa-comments" style="color:#5B9BD5;width:20px;"></i> Messagerie</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/messages"><i class="fas fa-envelope" style="color:#26A69A;width:20px;"></i> Notifications</a></li>
            <li><a class="dropdown-item" href="/TinyTrack/View/FrontOffice/reclamations/reclamationFront.php"><i class="fas fa-exclamation-circle" style="color:#EF5350;width:20px;"></i> Mes réclamations</a></li>
          </ul>
        </li>
        <li class="nav-item"><a class="nav-link <?= navActive(['/profil.php'], $__navPath) ? 'active' : '' ?>" href="/TinyTrack/profil"><i class="fas fa-user-circle"></i> Mon profil</a></li>
        <?php endif; ?>
        <li class="nav-item ms-2">
          <a class="nav-link nav-link-logout" href="/TinyTrack/logout" onclick="return confirm('Se déconnecter ?')">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

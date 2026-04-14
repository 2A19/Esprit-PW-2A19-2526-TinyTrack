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
  <link rel="stylesheet" href="/TinyTrack/assets/css/playful.css?v=2">
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
    .navbar-kider .nav-link i {
      margin-right: 4px;
    }

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

<!-- Playful Background -->
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
  <div class="deco-emoji deco-emoji-1">&#x1F476;</div>
  <div class="deco-emoji deco-emoji-2">&#x2B50;</div>
  <div class="deco-emoji deco-emoji-3">&#x1F338;</div>
  <div class="deco-emoji deco-emoji-4">&#x2764;</div>
</div>

<!-- Navbar Kider -->
<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/TinyTrack/View/FrontOffice/enfants/list.php">
      <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack">
      TinyTrack
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarKider">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarKider">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="/TinyTrack/View/FrontOffice/enfants/list.php"><i class="fas fa-child"></i> Enfants</a></li>
        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
        <li class="nav-item"><a class="nav-link" href="/TinyTrack/View/FrontOffice/educateurs/list.php"><i class="fas fa-chalkboard-teacher"></i> Éducateurs</a></li>
        <li class="nav-item"><a class="nav-link" href="/TinyTrack/View/FrontOffice/approbation.php" style="color:#FFA726;"><i class="fas fa-user-check"></i> Approbation</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="/TinyTrack/View/FrontOffice/messages.php"><i class="fas fa-envelope"></i> Messages</a></li>
        <li class="nav-item"><a class="nav-link" href="/TinyTrack/View/FrontOffice/profil.php"><i class="fas fa-user-circle"></i> Mon profil</a></li>
        <li class="nav-item ms-1">
          <a class="nav-link" href="/TinyTrack/View/auth/logout.php" style="background:#dc3545;color:#fff;border-radius:25px;padding:0.4rem 1rem;" onclick="return confirm('Se déconnecter ?')">
            <i class="fas fa-sign-out-alt"></i> Déconnexion
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>

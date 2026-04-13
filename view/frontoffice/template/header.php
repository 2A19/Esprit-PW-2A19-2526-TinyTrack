<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Journal de Bord</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --kider-green: #4CAF50;
      --kider-blue: #5B9BD5;
      --kider-yellow: #FFD93D;
      --kider-pink: #FF8FAB;
      --kider-orange: #FFA726;
      --kider-purple: #9C7CDB;
    }

    body {
      font-family: 'Nunito', sans-serif;
      background: #FFF9F0;
      color: #333;
    }

    .navbar-kider {
      background: #fff;
      box-shadow: 0 3px 20px rgba(0,0,0,0.06);
      padding: 0.8rem 0;
      border-bottom: 4px solid transparent;
      border-image: linear-gradient(
        90deg,
        var(--kider-green),
        var(--kider-blue),
        var(--kider-yellow),
        var(--kider-pink),
        var(--kider-orange)
      ) 1;
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
      transition: all 0.2s;
    }

    .navbar-kider .nav-link:hover {
      color: var(--kider-green);
      background: rgba(76, 175, 80, 0.08);
    }

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

    .card-kider {
      border: none;
      border-radius: 20px;
      box-shadow: 0 4px 20px rgba(0,0,0,0.06);
      transition: all 0.3s;
      background: #fff;
      position: relative;
      overflow: hidden;
    }

    .card-kider:hover {
      transform: translateY(-5px);
      box-shadow: 0 8px 30px rgba(0,0,0,0.1);
    }

    .card-kider::before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--kider-green), var(--kider-blue));
    }

    .medical-box {
      background: linear-gradient(135deg, #F5F5F5, #FAFAFA);
      border-radius: 14px;
      padding: 1rem;
      font-size: 0.85rem;
      border: 1px solid rgba(0,0,0,0.04);
    }

    .badge {
      border-radius: 20px;
      padding: 0.3rem 0.8rem;
      font-weight: 700;
    }

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
      top: -20px;
      left: 50%;
      transform: translateX(-50%);
      width: 120%;
      height: 40px;
      background: #FFF9F0;
      border-radius: 0 0 50% 50%;
    }

    footer .footer-logo {
      font-family: 'Fredoka One', cursive;
      font-size: 1.4rem;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/ProjetRapport/tinytrack/view/frontoffice/listRapportsParent.php">
      <img src="/ProjetRapport/tinytrack/assets/images/logo.png" alt="TinyTrack"> TinyTrack
    </a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navK" aria-controls="navK" aria-expanded="false" aria-label="Ouvrir le menu">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navK">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="/ProjetRapport/tinytrack/view/frontoffice/listRapportsParent.php">
            <i class="fas fa-book"></i> Rapports
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/ProjetRapport/tinytrack/view/frontoffice/listActivitesEducateur.php">
            <i class="fas fa-paint-brush"></i> Activités
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="/ProjetRapport/tinytrack/view/frontoffice/addRapport.php">
            <i class="fas fa-plus"></i> Ajouter rapport
          </a>
        </li>

        <li class="nav-item ms-2">
          <a class="nav-link" href="/ProjetRapport/tinytrack/view/backoffice/listRapports.php" style="background:var(--kider-green);color:#fff;border-radius:25px;padding:0.4rem 1rem;">
            <i class="fas fa-lock"></i> BackOffice
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
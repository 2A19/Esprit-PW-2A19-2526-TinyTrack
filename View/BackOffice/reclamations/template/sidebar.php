<aside class="main-sidebar elevation-4">
  <a href="/TinyTrack/View/BackOffice/reclamations/reclamationBack.php" class="brand-link">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" class="brand-image">
    <span class="brand-text">TinyTrack</span>
  </a>
  <div class="sidebar">
    <?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image pt-1"><i class="fas fa-user-shield fa-lg" style="color:rgba(255,255,255,0.7);"></i></div>
      <div class="info">
        <a href="#" class="d-block" style="color:#fff;"><?= htmlspecialchars($_SESSION['user_nom'] ?? 'Utilisateur') ?></a>
        <span style="color:rgba(255,255,255,0.4);font-size:0.75rem;"><?= ucfirst($_SESSION['user_role'] ?? '') ?></span>
      </div>
    </div>
    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
        <li class="nav-header">RÉCLAMATIONS</li>
        <li class="nav-item"><a href="reclamationBack.php" class="nav-link"><i class="nav-icon fas fa-exclamation-triangle" style="color:#FFD93D;"></i><p style="color:#fff;">Liste réclamations</p></a></li>
        <li class="nav-header">RÉPONSES</li>
        <li class="nav-item"><a href="reponsesBack.php" class="nav-link"><i class="nav-icon fas fa-reply-all" style="color:#81C784;"></i><p style="color:#fff;">Liste réponses</p></a></li>
        <li class="nav-header">NAVIGATION</li>
        <li class="nav-item"><a href="/TinyTrack/dashboard" class="nav-link"><i class="nav-icon fas fa-arrow-left" style="color:#FFD93D;"></i><p style="color:#fff;">Revenir au dashboard</p></a></li>
      </ul>
    </nav>
  </div>
</aside>

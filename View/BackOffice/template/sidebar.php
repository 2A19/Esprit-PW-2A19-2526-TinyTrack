<?php
// Detect current section from URL
$currentPath = $_SERVER['REQUEST_URI'] ?? '';
$section = 'enfants'; // default
if (strpos($currentPath, '/evenements/') !== false || strpos($currentPath, '/reservations/') !== false) {
    $section = 'evenements';
} elseif (strpos($currentPath, '/rapports/') !== false || strpos($currentPath, '/activites/') !== false) {
    $section = 'rapports';
}
?>
<!-- Main Sidebar -->
<aside class="main-sidebar elevation-4">
  <a href="/TinyTrack/dashboard" class="brand-link">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" class="brand-image">
    <span class="brand-text">TinyTrack</span>
  </a>

  <div class="sidebar">
    <div class="user-panel mt-3 pb-3 mb-3 d-flex">
      <div class="image pt-1">
        <i class="fas fa-user-shield fa-lg" style="color:rgba(255,255,255,0.7);"></i>
      </div>
      <div class="info">
        <a href="#" class="d-block" style="color:#fff;">Admin TinyTrack</a>
        <span style="color:rgba(255,255,255,0.4); font-size:0.75rem;">Administrateur</span>
      </div>
    </div>

    <nav class="mt-2">
      <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">

        <?php if ($section === 'enfants'): ?>

        <li class="nav-header">ENFANTS</li>
        <li class="nav-item">
          <a href="/TinyTrack/enfants" class="nav-link">
            <i class="nav-icon fas fa-child" style="color:#FFD93D;"></i>
            <p style="color:#fff;">Liste des enfants</p>
          </a>
        </li>

        <?php elseif ($section === 'evenements'): ?>

        <li class="nav-header">ÉVÉNEMENTS</li>
        <li class="nav-item">
          <a href="/TinyTrack/evenements" class="nav-link">
            <i class="nav-icon fas fa-calendar-alt" style="color:#81C784;"></i>
            <p style="color:#fff;">Liste événements</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/TinyTrack/evenements/add" class="nav-link">
            <i class="nav-icon fas fa-calendar-plus" style="color:#90CAF9;"></i>
            <p style="color:#fff;">Ajouter événement</p>
          </a>
        </li>

        <li class="nav-header">RÉSERVATIONS</li>
        <li class="nav-item">
          <a href="/TinyTrack/reservations" class="nav-link">
            <i class="nav-icon fas fa-ticket-alt" style="color:#FF8FAB;"></i>
            <p style="color:#fff;">Liste réservations</p>
          </a>
        </li>

        <?php elseif ($section === 'rapports'): ?>

        <li class="nav-header">RAPPORTS</li>
        <li class="nav-item">
          <a href="/TinyTrack/rapports" class="nav-link">
            <i class="nav-icon fas fa-book" style="color:#FFA726;"></i>
            <p style="color:#fff;">Rapports</p>
          </a>
        </li>

        <li class="nav-header">ACTIVITÉS</li>
        <li class="nav-item">
          <a href="/TinyTrack/activites" class="nav-link">
            <i class="nav-icon fas fa-paint-brush" style="color:#9C7CDB;"></i>
            <p style="color:#fff;">Activités</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/TinyTrack/activites/add" class="nav-link">
            <i class="nav-icon fas fa-plus-circle" style="color:#90CAF9;"></i>
            <p style="color:#fff;">Ajouter activité</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/TinyTrack/activites/statistiques" class="nav-link">
            <i class="nav-icon fas fa-chart-pie" style="color:#17a2b8;"></i>
            <p style="color:#fff;">Statistiques</p>
          </a>
        </li>
        <li class="nav-item">
          <a href="/TinyTrack/activites/expertise" class="nav-link">
            <i class="nav-icon fas fa-brain" style="color:#FF8FAB;"></i>
            <p style="color:#fff;">Expertise éducateurs</p>
          </a>
        </li>

        <?php endif; ?>

        <li class="nav-header">NAVIGATION</li>
        <li class="nav-item">
          <a href="/TinyTrack/dashboard" class="nav-link">
            <i class="nav-icon fas fa-th-large" style="color:#FFA726;"></i>
            <p style="color:#fff;">Revenir au Dashboard</p>
          </a>
        </li>

      </ul>
    </nav>
  </div>
</aside>

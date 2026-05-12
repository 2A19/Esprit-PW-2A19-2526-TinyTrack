<?php
// Vue passive — données injectées par DashboardController::admin()
// Variables : $stats
$totalEnfants     = $stats['totalEnfants'];
$totalEducateurs  = $stats['totalEducateurs'];
$totalParents     = $stats['totalParents'];
$pendingAccounts  = $stats['pendingAccounts'];
$totalGroupes     = $stats['totalGroupes'];
$unreadMessages   = $stats['unreadMessages'];
$totalEvents      = $stats['totalEvents'];
$totalRapports    = $stats['totalRapports'];
include __DIR__ . '/template/header.php';
?>

<div class="container py-5" style="position:relative;z-index:1;">

  <!-- HERO -->
  <section class="hero-kids mb-5">
    <div class="row align-items-center">
      <div class="col-md-8">
        <span class="badge" style="background:#fff;color:#26A69A;border-radius:50px;padding:0.4rem 1rem;font-weight:800;box-shadow:0 3px 10px rgba(0,0,0,0.05);">
          &#x2728; Tableau de bord admin
        </span>
        <h1 class="mt-3">
          Bonjour <span class="accent-pink"><?= htmlspecialchars($_SESSION['user_nom']) ?></span>,<br>
          <span class="accent-yellow">spark</span> la journée <span class="accent-teal">!</span>
        </h1>
        <p class="lead">Un coup d'œil rapide sur votre creche — enfants, equipe, evenements et demandes en attente. Tout est la, au chaud.</p>
        <div class="d-flex flex-wrap gap-2">
          <a href="/TinyTrack/mes-enfants" class="btn-chunky">
            <i class="fas fa-child"></i> Voir les enfants
          </a>
          <?php if ($pendingAccounts > 0): ?>
            <a href="/TinyTrack/approbation" class="btn-chunky btn-chunky-pink">
              <i class="fas fa-bell"></i> <?= $pendingAccounts ?> en attente
            </a>
          <?php endif; ?>
        </div>
      </div>
      <div class="col-md-4 position-relative d-none d-md-block">
        <div class="hero-sticker">
          <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack">
        </div>
      </div>
    </div>
  </section>

  <div class="text-center mb-4">
    <h2 class="section-title">Vue d'ensemble</h2>
    <div class="rainbow-divider"></div>
  </div>

  <!-- TILES -->
  <div class="row g-4 justify-content-center">

    <!-- Enfants -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/mes-enfants" class="tile-kids tile-mint">
        <div class="tile-emoji">&#x1F476;</div>
        <h3 class="tile-number"><?= $totalEnfants ?></h3>
        <p class="tile-label">Enfants</p>
      </a>
    </div>

    <!-- Éducateurs -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/educateurs" class="tile-kids tile-sky">
        <div class="tile-emoji">&#x1F469;&#x200D;&#x1F3EB;</div>
        <h3 class="tile-number"><?= $totalEducateurs ?></h3>
        <p class="tile-label">Éducateurs</p>
      </a>
    </div>

    <!-- Événements -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/evenements" class="tile-kids tile-coral">
        <div class="tile-emoji">&#x1F389;</div>
        <h3 class="tile-number"><?= $totalEvents ?></h3>
        <p class="tile-label">Événements</p>
      </a>
    </div>

    <!-- Rapports -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/rapports" class="tile-kids tile-grape">
        <div class="tile-emoji">&#x1F4DD;</div>
        <h3 class="tile-number"><?= $totalRapports ?></h3>
        <p class="tile-label">Rapports</p>
      </a>
    </div>

    <!-- Activités -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/activites" class="tile-kids tile-sun">
        <div class="tile-emoji">&#x1F3A8;</div>
        <h3 class="tile-number"><?= $totalActivites ?? '—' ?></h3>
        <p class="tile-label">Activités</p>
      </a>
    </div>

    <!-- Réclamations -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/View/BackOffice/reclamations/reclamationBack.php" class="tile-kids tile-rose">
        <div class="tile-emoji">&#x1F4E2;</div>
        <h3 class="tile-number"><?= $totalReclamations ?? '—' ?></h3>
        <p class="tile-label">Réclamations</p>
      </a>
    </div>

    <!-- Approbation -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/approbation" class="tile-kids <?= $pendingAccounts > 0 ? 'is-alert' : 'tile-sun' ?>">
        <div class="tile-emoji">&#x23F3;</div>
        <h3 class="tile-number"><?= $pendingAccounts ?></h3>
        <p class="tile-label">En attente</p>
      </a>
    </div>

    <!-- Parents -->
    <div class="col-md-4 col-sm-6 col-12">
      <a href="/TinyTrack/parents" class="tile-kids tile-rose">
        <div class="tile-emoji">&#x1F46A;</div>
        <h3 class="tile-number"><?= $totalParents ?></h3>
        <p class="tile-label">Parents</p>
      </a>
    </div>

  </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>

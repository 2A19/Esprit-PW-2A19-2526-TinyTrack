<?php
session_start();
require_once __DIR__ . '/../../Controller/DashboardController.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}

$dashboardCtrl = new DashboardController();
$stats = $dashboardCtrl->getStats();

$totalEnfants = $stats['totalEnfants'];
$totalEducateurs = $stats['totalEducateurs'];
$totalParents = $stats['totalParents'];
$pendingAccounts = $stats['pendingAccounts'];
$totalGroupes = $stats['totalGroupes'];
$unreadMessages = $stats['unreadMessages'];
$totalEvents = $stats['totalEvents'];
$totalRapports = $stats['totalRapports'];

include 'template/header.php';
?>

<div class="container py-5" style="position:relative;z-index:1;">

  <div class="text-center mb-5">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" style="height:80px;margin-bottom:10px;">
    <h2 style="font-family:'Fredoka One',cursive;color:#333;font-size:2rem;">Tableau de bord</h2>
    <p class="text-muted">Bonjour <strong style="color:#4CAF50;"><?= htmlspecialchars($_SESSION['user_nom']) ?></strong></p>
  </div>

  <div class="row g-4 justify-content-center">

    <!-- Enfants -->
    <div class="col-md-4 col-6">
      <a href="/TinyTrack/View/FrontOffice/enfants/list.php" style="text-decoration:none;">
        <div class="card-kider p-4 text-center" style="cursor:pointer;border-left:5px solid #4CAF50;">
          <div style="font-size:3rem;margin-bottom:0.5rem;">&#x1F476;</div>
          <h3 style="font-family:'Fredoka One',cursive;color:#4CAF50;font-size:2.2rem;margin:0;"><?= $totalEnfants ?></h3>
          <p style="color:#666;font-weight:700;margin:0.3rem 0 0;">Enfants</p>
        </div>
      </a>
    </div>

    <!-- Éducateurs -->
    <div class="col-md-4 col-6">
      <a href="/TinyTrack/View/FrontOffice/educateurs/list.php" style="text-decoration:none;">
        <div class="card-kider p-4 text-center" style="cursor:pointer;border-left:5px solid #5B9BD5;">
          <div style="font-size:3rem;margin-bottom:0.5rem;">&#x1F469;&#x200D;&#x1F3EB;</div>
          <h3 style="font-family:'Fredoka One',cursive;color:#5B9BD5;font-size:2.2rem;margin:0;"><?= $totalEducateurs ?></h3>
          <p style="color:#666;font-weight:700;margin:0.3rem 0 0;">Éducateurs</p>
        </div>
      </a>
    </div>


    <!-- Événements -->
    <div class="col-md-4 col-6">
      <a href="/TinyTrack/View/BackOffice/evenements/evenementList.php" style="text-decoration:none;">
        <div class="card-kider p-4 text-center" style="cursor:pointer;border-left:5px solid #FFA726;">
          <div style="font-size:3rem;margin-bottom:0.5rem;">&#x1F389;</div>
          <h3 style="font-family:'Fredoka One',cursive;color:#FFA726;font-size:2.2rem;margin:0;"><?= $totalEvents ?></h3>
          <p style="color:#666;font-weight:700;margin:0.3rem 0 0;">Événements</p>
        </div>
      </a>
    </div>

    <!-- Rapports -->
    <div class="col-md-4 col-6">
      <a href="/TinyTrack/View/BackOffice/rapports/listRapports.php" style="text-decoration:none;">
        <div class="card-kider p-4 text-center" style="cursor:pointer;border-left:5px solid #9C7CDB;">
          <div style="font-size:3rem;margin-bottom:0.5rem;">&#x1F4DD;</div>
          <h3 style="font-family:'Fredoka One',cursive;color:#9C7CDB;font-size:2.2rem;margin:0;"><?= $totalRapports ?></h3>
          <p style="color:#666;font-weight:700;margin:0.3rem 0 0;">Rapports</p>
        </div>
      </a>
    </div>

    <!-- Approbation -->
    <div class="col-md-4 col-6">
      <a href="/TinyTrack/View/FrontOffice/approbation.php" style="text-decoration:none;">
        <div class="card-kider p-4 text-center" style="cursor:pointer;border-left:5px solid <?= $pendingAccounts > 0 ? '#EF5350' : '#4CAF50' ?>;">
          <div style="font-size:3rem;margin-bottom:0.5rem;">&#x23F3;</div>
          <h3 style="font-family:'Fredoka One',cursive;color:<?= $pendingAccounts > 0 ? '#EF5350' : '#4CAF50' ?>;font-size:2.2rem;margin:0;"><?= $pendingAccounts ?></h3>
          <p style="color:#666;font-weight:700;margin:0.3rem 0 0;">En attente</p>
        </div>
      </a>
    </div>




  </div>
</div>

<?php include 'template/footer.php'; ?>

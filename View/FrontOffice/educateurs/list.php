<?php
session_start();
require_once __DIR__ . '/../../../Controller/EducateurController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}
// Only admin can see all educateurs
if ($_SESSION['user_role'] === 'educateur') {
    header('Location: /TinyTrack/View/FrontOffice/educateurs/profil.php');
    exit;
}
if ($_SESSION['user_role'] === 'parent') {
    header('Location: /TinyTrack/View/FrontOffice/enfants/list.php');
    exit;
}

$educateurCtrl = new EducateurController();

// Handle archive/activate
if (isset($_GET['archive']) && is_numeric($_GET['archive']) && $_SESSION['user_role'] === 'admin') {
    $educateurCtrl->archiverCompte((int)$_GET['archive']);
    header('Location: list.php?msg=archived');
    exit;
}
if (isset($_GET['activate']) && is_numeric($_GET['activate']) && $_SESSION['user_role'] === 'admin') {
    $educateurCtrl->activerCompte((int)$_GET['activate']);
    header('Location: list.php?msg=activated');
    exit;
}

$educateurs = $educateurCtrl->listerEducateurs();

include '../template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <?php if (isset($_GET['msg'])): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <?php if ($_GET['msg'] === 'archived'): ?>
        <div class="alert alert-warning" style="border-radius:14px;border:none;text-align:center;"><i class="fas fa-archive"></i> Compte archivé — l'accès est coupé.</div>
      <?php elseif ($_GET['msg'] === 'activated'): ?>
        <div class="alert alert-success" style="border-radius:14px;border:none;text-align:center;"><i class="fas fa-check-circle"></i> Compte réactivé.</div>
      <?php endif; ?>
    </div></div>
  <?php endif; ?>

  <div class="text-center mb-5">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" style="height:70px; margin-bottom:15px;">
    <h2 class="section-title"><i class="fas fa-chalkboard-teacher"></i> Nos Éducateurs</h2>
    <p class="text-muted mt-3" style="font-size:1.05rem;">L'équipe qui prend soin de vos enfants chaque jour</p>
  </div>

  <div class="row g-4 justify-content-center">
    <?php foreach ($educateurs as $edu): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-kider p-4 text-center">

          <!-- Avatar -->
          <div class="mb-3">
            <div class="avatar-circle boy d-inline-flex" style="width:90px;height:90px;background:linear-gradient(135deg,#E3F2FD,#BBDEFB);">
              <i class="fas fa-user-tie" style="font-size:2.2rem;color:#5B9BD5;"></i>
            </div>
          </div>

          <!-- Name -->
          <h5 style="font-family:'Fredoka One',cursive; margin-bottom:0.3rem;">
            <?= htmlspecialchars($edu['prenom'] . ' ' . $edu['nom']) ?>
          </h5>

          <!-- Status -->
          <?php if ($edu['statut'] === 'actif'): ?>
            <span class="status-inscrit mb-2 d-inline-block"><i class="fas fa-check-circle"></i> Actif</span>
          <?php else: ?>
            <span class="status-archive mb-2 d-inline-block"><i class="fas fa-pause-circle"></i> Inactif</span>
          <?php endif; ?>

          <!-- Info -->
          <div class="medical-box text-start mt-3">
            <p><i class="fas fa-envelope" style="color:#5B9BD5;"></i> <strong>Email :</strong> <?= htmlspecialchars($edu['email']) ?></p>
            <p><i class="fas fa-phone" style="color:#4CAF50;"></i> <strong>Tél :</strong> <?= htmlspecialchars($edu['telephone'] ?: '—') ?></p>
            <p><i class="fas fa-users" style="color:#FFA726;"></i> <strong>Groupe :</strong> <?= htmlspecialchars($edu['groupe_nom'] ?: 'Non affecté') ?>
              <?php if ($edu['groupe_niveau']): ?>
                <span style="background:#E8F5E9;color:#2E7D32;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.7rem;font-weight:700;margin-left:4px;"><?= $edu['groupe_niveau'] ?></span>
              <?php endif; ?>
            </p>
            <p class="mb-0"><i class="fas fa-child" style="color:#FF8FAB;"></i> <strong>Enfants :</strong> <?= $edu['nb_enfants'] ?? 0 ?> enfant(s)</p>
          </div>

          <!-- Admin actions -->
          <?php if ($_SESSION['user_role'] === 'admin'): ?>
          <div style="margin-top:0.8rem;display:flex;gap:0.5rem;justify-content:center;flex-wrap:wrap;">
            <span style="background:#E3F2FD;color:#1565C0;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;">
              <i class="fas fa-id-badge"></i> ID : <?= $edu['id'] ?>
            </span>
            <?php if ($edu['statut'] === 'actif'): ?>
              <a href="list.php?archive=<?= $edu['id'] ?>" onclick="return confirm('Archiver ce compte ? L\'éducateur ne pourra plus se connecter.')" style="background:#FFEBEE;color:#C62828;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;text-decoration:none;">
                <i class="fas fa-archive"></i> Archiver
              </a>
            <?php else: ?>
              <a href="list.php?activate=<?= $edu['id'] ?>" onclick="return confirm('Réactiver ce compte ?')" style="background:#E8F5E9;color:#2E7D32;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;text-decoration:none;">
                <i class="fas fa-check-circle"></i> Réactiver
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include '../template/footer.php'; ?>

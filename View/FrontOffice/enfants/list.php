<?php
session_start();
require_once __DIR__ . '/../../../config/db.php';
require_once __DIR__ . '/../../../Controller/EnfantController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();
$enfantCtrl = new EnfantController();

if ($_SESSION['user_role'] === 'parent') {
    $enfants = $enfantCtrl->listerEnfantsParParent($_SESSION['user_id']);
} else {
    $enfants = $enfantCtrl->listerEnfants();
}

$parentNom = $_SESSION['user_nom'] ?? 'Parent';

include '../template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <div class="text-center mb-5">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" style="height:70px; margin-bottom:15px;">
    <h2 class="section-title"><i class="fas fa-child"></i> Enfants</h2>
    <p class="text-muted mt-3">
      Bonjour <strong style="color:#4CAF50;"><?= htmlspecialchars($parentNom) ?></strong>, consultez les fiches des enfants
    </p>
  </div>

  <?php foreach ($enfants as $e): ?>
    <?php
      // Get groupe info
      $groupe = null;
      if ($e['groupe_id']) {
          $stmt = $db->prepare("SELECT * FROM groupe WHERE id = :id");
          $stmt->execute([':id' => $e['groupe_id']]);
          $groupe = $stmt->fetch();
      }

      // Get educateur info
      $educateur = null;
      if ($groupe && $groupe['educateur_id']) {
          $stmt = $db->prepare("SELECT prenom, nom, telephone FROM user WHERE id = :id AND role = 'educateur'");
          $stmt->execute([':id' => $groupe['educateur_id']]);
          $educateur = $stmt->fetch();
      }
    ?>

    <div class="row justify-content-center mb-4">
      <div class="col-md-10">
        <div class="card-kider p-0" style="overflow:hidden;">

          <div style="background:linear-gradient(135deg,<?= $e['sexe'] === 'M' ? '#5B9BD5,#90CAF9' : '#FF8FAB,#F48FB1' ?>);padding:1.5rem 2rem;display:flex;align-items:center;gap:1.5rem;position:relative;">
            <div class="avatar-circle d-inline-flex" style="width:70px;height:70px;background:rgba(255,255,255,0.25);border:3px solid rgba(255,255,255,0.5);flex-shrink:0;">
              <?php if ($e['sexe'] === 'M'): ?>
                <i class="fas fa-child" style="font-size:2rem;color:#fff;"></i>
              <?php else: ?>
                <i class="fas fa-child-dress" style="font-size:2rem;color:#fff;"></i>
              <?php endif; ?>
            </div>
            <div>
              <h4 style="font-family:'Fredoka One',cursive;color:#fff;margin:0;"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></h4>
              <div style="display:flex;gap:0.5rem;margin-top:0.3rem;flex-wrap:wrap;">
                <span style="background:rgba(255,255,255,0.25);color:#fff;padding:0.2rem 0.7rem;border-radius:15px;font-size:0.75rem;font-weight:700;">
                  <i class="fas fa-birthday-cake"></i> <?= date('d/m/Y', strtotime($e['date_naissance'])) ?>
                </span>
                <span style="background:rgba(255,255,255,0.25);color:#fff;padding:0.2rem 0.7rem;border-radius:15px;font-size:0.75rem;font-weight:700;">
                  <?= $e['sexe'] === 'M' ? 'Garçon' : 'Fille' ?>
                </span>
              </div>
            </div>
          </div>

          <div style="padding:1.2rem 2rem;">
            <div class="row">
              <div class="col-md-6 mb-3">
                <div class="medical-box" style="background:linear-gradient(135deg,#FFF8E1,#FFFDE7);border:1px solid #FFE082;">
                  <p style="font-weight:800;color:#FFA726;margin-bottom:0.5rem;"><i class="fas fa-users"></i> Groupe</p>
                  <?php if ($groupe): ?>
                    <p><i class="fas fa-star" style="color:#FFD93D;"></i> <strong><?= htmlspecialchars($groupe['nom']) ?></strong>
                      <span style="background:#E8F5E9;color:#2E7D32;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.75rem;font-weight:700;margin-left:4px;"><?= $groupe['niveau'] ?></span>
                    </p>
                  <?php else: ?>
                    <p style="color:#999;">Non assigné</p>
                  <?php endif; ?>
                </div>
              </div>
              <div class="col-md-6 mb-3">
                <div class="medical-box" style="background:linear-gradient(135deg,#E3F2FD,#E8F0FE);border:1px solid #BBDEFB;">
                  <p style="font-weight:800;color:#5B9BD5;margin-bottom:0.5rem;"><i class="fas fa-chalkboard-teacher"></i> Éducateur(trice)</p>
                  <?php if ($educateur): ?>
                    <p><i class="fas fa-user-tie" style="color:#5B9BD5;"></i> <strong><?= htmlspecialchars($educateur['prenom'] . ' ' . $educateur['nom']) ?></strong></p>
                    <p class="mb-0"><i class="fas fa-phone" style="color:#4CAF50;"></i> <?= htmlspecialchars($educateur['telephone'] ?: '—') ?></p>
                  <?php else: ?>
                    <p style="color:#999;">Non assigné</p>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (empty($enfants)): ?>
    <div class="row justify-content-center">
      <div class="col-md-8 text-center">
        <div class="card-kider p-5">
          <i class="fas fa-baby fa-4x" style="color:#ddd;"></i>
          <h5 style="font-family:'Fredoka One',cursive;color:#999;margin-top:1rem;">Aucun enfant</h5>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include '../template/footer.php'; ?>

<?php
session_start();
require_once __DIR__ . '/../../Controller/ProfilController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}

$profilCtrl = new ProfilController();
$errors = [];
$successMsg = null;

// Handle edit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_profil'])) {
    $result = $profilCtrl->updateProfil($_SESSION['user_id'], $_POST);
    if ($result['success']) {
        $successMsg = "Profil mis à jour avec succès !";
    } else {
        $errors = $result['errors'];
    }
}

$user = $profilCtrl->getProfil($_SESSION['user_id']);
$role = $_SESSION['user_role'];
$editMode = isset($_GET['edit']);

$enfants = [];
if ($role === 'parent') {
    $enfants = $profilCtrl->getEnfantsParent($_SESSION['user_id']);
}

include 'template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">
  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card-kider p-0" style="overflow:hidden;">

        <!-- Header -->
        <div style="background:linear-gradient(135deg,<?= $role === 'parent' ? '#FFA726,#FFB74D' : ($role === 'educateur' ? '#5B9BD5,#90CAF9' : '#4CAF50,#81C784') ?>);padding:2rem;text-align:center;position:relative;">
          <div style="position:absolute;top:-10px;right:-10px;width:80px;height:80px;background:rgba(255,255,255,0.1);border-radius:50%;"></div>
          <div class="avatar-circle d-inline-flex" style="width:100px;height:100px;background:rgba(255,255,255,0.25);border:3px solid rgba(255,255,255,0.5);">
            <?php if ($role === 'parent'): ?>
              <i class="fas fa-user" style="font-size:2.8rem;color:#fff;"></i>
            <?php elseif ($role === 'educateur'): ?>
              <i class="fas fa-user-tie" style="font-size:2.8rem;color:#fff;"></i>
            <?php else: ?>
              <i class="fas fa-user-shield" style="font-size:2.8rem;color:#fff;"></i>
            <?php endif; ?>
          </div>
          <h3 style="font-family:'Fredoka One',cursive;color:#fff;margin-top:0.8rem;font-size:1.5rem;">
            <?= htmlspecialchars($user['prenom'] . ' ' . $user['nom']) ?>
          </h3>
          <span style="background:rgba(255,255,255,0.2);color:#fff;padding:0.3rem 1rem;border-radius:20px;font-size:0.8rem;font-weight:700;">
            <?php if ($role === 'parent'): ?>
              <i class="fas fa-heart"></i> Parent
            <?php elseif ($role === 'educateur'): ?>
              <i class="fas fa-chalkboard-teacher"></i> Éducateur
            <?php else: ?>
              <i class="fas fa-shield-alt"></i> Administrateur
            <?php endif; ?>
          </span>
          <div style="margin-top:0.8rem;">
            <?php if (!$editMode): ?>
              <a href="profil.php?edit=1" style="background:rgba(255,255,255,0.25);color:#fff;padding:0.4rem 1.2rem;border-radius:20px;font-size:0.8rem;font-weight:700;text-decoration:none;"><i class="fas fa-edit"></i> Modifier mon profil</a>
            <?php else: ?>
              <a href="profil.php" style="background:rgba(255,255,255,0.25);color:#fff;padding:0.4rem 1.2rem;border-radius:20px;font-size:0.8rem;font-weight:700;text-decoration:none;"><i class="fas fa-times"></i> Annuler</a>
            <?php endif; ?>
          </div>
        </div>

        <?php if ($successMsg): ?>
          <div style="padding:0.8rem 2rem 0;"><div class="alert alert-success" style="border-radius:14px;border:none;"><i class="fas fa-check-circle"></i> <?= $successMsg ?></div></div>
        <?php endif; ?>
        <?php if (!empty($errors)): ?>
          <div style="padding:0.8rem 2rem 0;"><div class="alert alert-danger" style="border-radius:14px;border:none;"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div></div>
        <?php endif; ?>

        <?php if ($editMode): ?>
        <!-- EDIT FORM -->
        <div style="padding:1.5rem 2rem;">
          <form method="POST" novalidate>
            <input type="hidden" name="edit_profil" value="1">
            <div class="row">
              <div class="col-md-6 mb-3">
                <label style="font-weight:700;font-size:0.85rem;color:#555;">Nom</label>
                <input type="text" name="nom" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" value="<?= htmlspecialchars($user['nom']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label style="font-weight:700;font-size:0.85rem;color:#555;">Prénom</label>
                <input type="text" name="prenom" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" value="<?= htmlspecialchars($user['prenom']) ?>">
              </div>
            </div>
            <div class="row">
              <div class="col-md-6 mb-3">
                <label style="font-weight:700;font-size:0.85rem;color:#555;">Email</label>
                <input type="text" name="email" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" value="<?= htmlspecialchars($user['email']) ?>">
              </div>
              <div class="col-md-6 mb-3">
                <label style="font-weight:700;font-size:0.85rem;color:#555;">Téléphone</label>
                <input type="text" name="telephone" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" value="<?= htmlspecialchars($user['telephone'] ?? '') ?>">
              </div>
            </div>
            <button type="submit" class="btn btn-success w-100" style="border-radius:25px;padding:0.7rem;font-weight:700;"><i class="fas fa-save"></i> Enregistrer</button>
          </form>
        </div>
        <?php else: ?>
        <!-- Infos (read only) -->
        <div style="padding:1.5rem 2rem;">
          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="medical-box">
                <p style="font-weight:800;color:#5B9BD5;margin-bottom:0.6rem;"><i class="fas fa-id-badge"></i> Informations personnelles</p>
                <p><i class="fas fa-hashtag" style="color:#FFA726;"></i> <strong>ID :</strong> <?= $user['id'] ?></p>
                <p><i class="fas fa-user" style="color:#5B9BD5;"></i> <strong>Nom :</strong> <?= htmlspecialchars($user['nom']) ?></p>
                <p><i class="fas fa-user" style="color:#FF8FAB;"></i> <strong>Prénom :</strong> <?= htmlspecialchars($user['prenom']) ?></p>
                <p class="mb-0"><i class="fas fa-envelope" style="color:#9C7CDB;"></i> <strong>Email :</strong> <?= htmlspecialchars($user['email']) ?></p>
              </div>
            </div>
            <div class="col-md-6 mb-3">
              <div class="medical-box">
                <p style="font-weight:800;color:#4CAF50;margin-bottom:0.6rem;"><i class="fas fa-address-card"></i> Contact & Statut</p>
                <p><i class="fas fa-phone" style="color:#4CAF50;"></i> <strong>Téléphone :</strong> <?= htmlspecialchars($user['telephone'] ?: '—') ?></p>
                <?php if (!empty($user['adresse'])): ?>
                  <p><i class="fas fa-map-marker-alt" style="color:#EF5350;"></i> <strong>Adresse :</strong> <?= htmlspecialchars($user['adresse']) ?></p>
                <?php endif; ?>
                <?php if (!empty($user['date_naissance'])): ?>
                  <p><i class="fas fa-birthday-cake" style="color:#FF8FAB;"></i> <strong>Né(e) le :</strong> <?= date('d/m/Y', strtotime($user['date_naissance'])) ?></p>
                <?php endif; ?>
                <p class="mb-0"><i class="fas fa-toggle-on" style="color:#4CAF50;"></i> <strong>Statut :</strong>
                  <span style="background:#E8F5E9;color:#2E7D32;padding:0.15rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= $user['statut'] ?></span>
                </p>
              </div>
            </div>
          </div>

          <!-- Enfants (parent only) -->
          <?php if ($role === 'parent' && !empty($enfants)): ?>
            <div class="medical-box mt-2" style="background:linear-gradient(135deg,#FFF8E1,#FFFDE7);border:1px solid #FFE082;">
              <p style="font-weight:800;color:#FFA726;margin-bottom:0.6rem;"><i class="fas fa-child"></i> Mes enfants</p>
              <?php foreach ($enfants as $enf): ?>
                <div style="display:flex;align-items:center;gap:0.8rem;padding:0.4rem 0;border-bottom:1px solid rgba(0,0,0,0.04);">
                  <?php if ($enf['sexe'] === 'M'): ?>
                    <span style="background:#E3F2FD;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="fas fa-child" style="color:#5B9BD5;font-size:0.8rem;"></i></span>
                  <?php else: ?>
                    <span style="background:#FCE4EC;width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;"><i class="fas fa-child-dress" style="color:#FF8FAB;font-size:0.8rem;"></i></span>
                  <?php endif; ?>
                  <div>
                    <strong><?= htmlspecialchars($enf['prenom'] . ' ' . $enf['nom']) ?></strong>
                    <span style="font-size:0.8rem;color:#888;margin-left:0.5rem;"><?= $enf['groupe_nom'] ? '(' . htmlspecialchars($enf['groupe_nom']) . ')' : '' ?></span>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          <?php endif; ?>

          <!-- Membre depuis -->
          <div class="text-center mt-3">
            <p style="color:#bbb;font-size:0.8rem;"><i class="fas fa-clock"></i> Membre depuis <?= date('d/m/Y', strtotime($user['created_at'])) ?></p>
          </div>
        </div>
        <?php endif; ?>

      </div>
    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>

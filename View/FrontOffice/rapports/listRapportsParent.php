<?php
session_start();
require_once __DIR__ . '/../../../Controller/RapportController.php';
require_once __DIR__ . '/../../../config/db.php';

$controller = new RapportController();
$allRapports = $controller->listRapportsWithActivite()->fetchAll();

// Filter: parent sees only rapports from educateurs of their children's groups
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'parent') {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->prepare("SELECT DISTINCT g.educateur_id FROM enfant e JOIN groupe g ON e.groupe_id = g.id WHERE e.parent_id = :pid AND e.statut = 'actif'");
    $stmt->execute([':pid' => $_SESSION['user_id']]);
    $educateurIds = array_column($stmt->fetchAll(), 'educateur_id');

    $rapports = array_filter($allRapports, function($r) use ($educateurIds) {
        return in_array($r['id_educateur'], $educateurIds);
    });
} else {
    $rapports = $allRapports;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Rapports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/TinyTrack/assets/css/playful.css?v=2">
  <style>
    body{font-family:'Nunito',sans-serif;background:#FFF9F0;color:#333}
    .navbar-kider{background:#fff;box-shadow:0 3px 20px rgba(0,0,0,0.06);padding:0.8rem 0;border-bottom:4px solid transparent;border-image:linear-gradient(90deg,#4CAF50,#5B9BD5,#FFD93D,#FF8FAB,#FFA726) 1}
    .navbar-kider .navbar-brand{font-family:'Fredoka One',cursive;color:#4CAF50;font-size:1.6rem;display:flex;align-items:center;gap:10px}
    .navbar-kider .navbar-brand img{height:42px;width:42px;object-fit:contain}
    .section-title{font-family:'Fredoka One',cursive;color:#4CAF50;font-size:2rem;position:relative;display:inline-block}
    .section-title::after{content:'';position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:linear-gradient(90deg,#FFD93D,#FFA726);border-radius:2px}
    .card-kider{border:none;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);transition:all 0.3s;background:#fff;position:relative;overflow:hidden}
    .card-kider:hover{transform:translateY(-5px);box-shadow:0 8px 30px rgba(0,0,0,0.1)}
    .card-kider::before{content:'';position:absolute;top:0;left:0;right:0;height:3px;background:linear-gradient(90deg,#4CAF50,#5B9BD5,#FFD93D,#FF8FAB,#9C7CDB)}
    .medical-box{background:linear-gradient(135deg,#F5F5F5,#FAFAFA);border-radius:14px;padding:1rem;font-size:0.85rem;border:1px solid rgba(0,0,0,0.04)}
    footer{background:linear-gradient(135deg,#4CAF50,#388E3C);color:#fff;padding:2.5rem 0;margin-top:4rem;position:relative;overflow:hidden}
    footer::before{content:'';position:absolute;top:-20px;left:50%;transform:translateX(-50%);width:120%;height:40px;background:#FFF9F0;border-radius:0 0 50% 50%}
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/TinyTrack/View/FrontOffice/enfants/list.php">
      <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack">
      TinyTrack
    </a>
    <div class="ms-auto d-flex gap-2">
      <a href="/TinyTrack/View/FrontOffice/enfants/list.php" class="btn" style="background:linear-gradient(135deg,#4CAF50,#66BB6A);color:#fff;border-radius:25px;font-weight:700;padding:0.4rem 1.2rem;font-size:0.85rem;"><i class="fas fa-arrow-left"></i> Page principale</a>
      <a href="/TinyTrack/View/auth/logout.php" class="btn" style="background:#dc3545;color:#fff;border-radius:25px;font-weight:700;padding:0.4rem 1.2rem;font-size:0.85rem;" onclick="return confirm('Se déconnecter ?')"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="text-center mb-5">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" style="height:70px;margin-bottom:15px;">
    <h2 class="section-title"><i class="fas fa-book"></i> Rapports Journaliers</h2>
    <p class="text-muted mt-3">Consultez les rapports quotidiens de vos enfants</p>
  </div>

  <div class="row g-4">
    <?php if (empty($rapports)): ?>
      <div class="col-12 text-center"><div class="card-kider p-5"><i class="fas fa-book fa-3x" style="color:#ddd;"></i><h5 style="font-family:'Fredoka One',cursive;color:#999;margin-top:1rem;">Aucun rapport</h5></div></div>
    <?php else: ?>
      <?php foreach ($rapports as $r): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card-kider p-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 style="font-family:'Fredoka One',cursive;margin-bottom:0;"><?= htmlspecialchars($r['nom_activite'] ?? 'Activité') ?></h5>
              <span class="badge bg-info" style="font-size:0.7rem;">#<?= $r['id_rapport'] ?></span>
            </div>
            <p style="font-size:0.85rem;color:#666;margin-bottom:0.8rem;"><?= htmlspecialchars(substr($r['contenu_rapport'], 0, 120)) ?><?= strlen($r['contenu_rapport']) > 120 ? '...' : '' ?></p>
            <div class="medical-box">
              <p><i class="fas fa-calendar" style="color:var(--kider-blue);"></i> <strong>Date :</strong> <?= $r['date_rapport'] ?></p>
              <p class="mb-0"><i class="fas fa-user-tie" style="color:var(--kider-green);"></i> <strong>Éducateur ID :</strong> <?= $r['id_educateur'] ?? '—' ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<footer class="text-center">
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem;">
    <p style="font-family:'Fredoka One',cursive;font-size:1.2rem;margin-bottom:0.3rem;">TinyTrack</p>
    <p style="opacity:0.8;font-size:0.85rem;">Chaque petit pas compte</p>
  </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

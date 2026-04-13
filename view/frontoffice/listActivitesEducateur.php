<?php
require_once '../../controller/ActiviteController.php';
$controller = new ActiviteController();
$activites = [];

if (isset($_GET['id_educateur']) && is_numeric($_GET['id_educateur']) && $_GET['id_educateur'] > 0) {
    $id_educateur = $_GET['id_educateur'];
    $activites = $controller->listActivitesByEducateur($id_educateur);
} else {
    $id_educateur = null;
}

include 'template/header.php';
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <h2 class="section-title"><i class="fas fa-paint-brush"></i> Activités</h2>
    <p class="text-muted mt-3">
      <?php if ($id_educateur): ?>
        Activités de l'éducateur #<?= htmlspecialchars($id_educateur) ?>
      <?php else: ?>
        Sélectionnez un éducateur pour voir ses activités
      <?php endif; ?>
    </p>
  </div>

  <?php if (!$id_educateur): ?>
    <div class="row justify-content-center">
      <div class="col-md-6">
        <div class="card-kider p-4 text-center">
          <h5 style="font-family:'Fredoka One',cursive;">Chercher par éducateur</h5>
          <form method="GET" class="d-flex gap-2 mt-3 justify-content-center">
            <input type="text" name="id_educateur" class="form-control" style="max-width:200px;border-radius:12px;" placeholder="ID éducateur (ex: 2)">
            <button type="submit" class="btn btn-success" style="border-radius:12px;"><i class="fas fa-search"></i> Chercher</button>
          </form>
        </div>
      </div>
    </div>
  <?php elseif (empty($activites)): ?>
    <div class="text-center"><div class="card-kider p-5 d-inline-block"><i class="fas fa-paint-brush fa-3x" style="color:#ddd;"></i><h5 style="color:#999;margin-top:1rem;">Aucune activité trouvée</h5></div></div>
  <?php else: ?>
    <div class="row g-4">
      <?php foreach ($activites as $a): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card-kider p-4">
            <h5 style="font-family:'Fredoka One',cursive;"><?= htmlspecialchars($a['nom_activite']) ?></h5>
            <p style="font-size:0.85rem;color:#666;"><?= htmlspecialchars($a['description'] ?? '') ?></p>
            <div class="medical-box">
              <p><i class="fas fa-calendar" style="color:var(--kider-blue);"></i> <strong>Date :</strong> <?= $a['date_activite'] ?></p>
              <p class="mb-0"><i class="fas fa-clock" style="color:var(--kider-orange);"></i> <strong>Heure :</strong> <?= substr($a['heure_activite'], 0, 5) ?></p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include 'template/footer.php'; ?>

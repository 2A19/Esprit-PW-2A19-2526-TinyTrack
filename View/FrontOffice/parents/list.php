<?php
session_start();
require_once __DIR__ . '/../../../Controller/EducateurController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}
if ($_SESSION['user_role'] !== 'admin' && $_SESSION['user_role'] !== 'educateur') {
    header('Location: /TinyTrack/View/FrontOffice/enfants/list.php');
    exit;
}

$ctrl = new EducateurController();

if ($_SESSION['user_role'] === 'admin') {
    if (isset($_GET['archive']) && is_numeric($_GET['archive'])) {
        $ctrl->archiverCompte((int)$_GET['archive']);
        header('Location: list.php?msg=archived');
        exit;
    }
    if (isset($_GET['activate']) && is_numeric($_GET['activate'])) {
        $ctrl->activerCompte((int)$_GET['activate']);
        header('Location: list.php?msg=activated');
        exit;
    }
}

$filters = [
    'q'      => trim($_GET['q'] ?? ''),
    'statut' => $_GET['statut'] ?? '',
];
$sortBy  = $_GET['sort'] ?? 'nom';
$sortDir = $_GET['dir']  ?? 'asc';

$parents = $ctrl->listerParentsFiltered($filters, $sortBy, $sortDir);
$stats   = $ctrl->statsParents($parents);

$hasActiveFilters = !empty($filters['q']) || !empty($filters['statut']);

include '../template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <?php if (isset($_GET['msg'])): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <?php if ($_GET['msg'] === 'archived'): ?>
        <div class="alert alert-warning" style="border-radius:14px;border:none;text-align:center;"><i class="fas fa-archive"></i> Compte archivé.</div>
      <?php elseif ($_GET['msg'] === 'activated'): ?>
        <div class="alert alert-success" style="border-radius:14px;border:none;text-align:center;"><i class="fas fa-check-circle"></i> Compte réactivé.</div>
      <?php endif; ?>
    </div></div>
  <?php endif; ?>

  <div class="text-center mb-4">
    <h2 class="section-title"><i class="fas fa-users"></i> Parents</h2>
    <div class="rainbow-divider"></div>
    <p class="text-muted mt-2">Les familles que nous accompagnons</p>
  </div>

  <div class="mini-stats-row">
    <div class="mini-stat ms-rose">
      <div class="ms-icon"><i class="fas fa-users"></i></div>
      <div><p class="ms-value"><?= $stats['total'] ?></p><p class="ms-label">Résultats</p></div>
    </div>
    <div class="mini-stat ms-mint">
      <div class="ms-icon"><i class="fas fa-check-circle"></i></div>
      <div><p class="ms-value"><?= $stats['actifs'] ?></p><p class="ms-label">Actifs</p></div>
    </div>
    <div class="mini-stat ms-sun">
      <div class="ms-icon"><i class="fas fa-hourglass-half"></i></div>
      <div><p class="ms-value"><?= $stats['enAttente'] ?></p><p class="ms-label">En attente</p></div>
    </div>
    <div class="mini-stat ms-sky">
      <div class="ms-icon"><i class="fas fa-child"></i></div>
      <div><p class="ms-value"><?= $stats['totalEnfants'] ?></p><p class="ms-label">Enfants liés</p></div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <form method="GET" class="filters-toolbar">
        <div class="toolbar-row">
          <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="filter-search" placeholder="Rechercher nom, prénom, email..." value="<?= htmlspecialchars($filters['q']) ?>">
          </div>
          <select name="statut" class="filter-select" onchange="this.form.submit()">
            <option value="">Tous statuts</option>
            <option value="actif"      <?= $filters['statut']==='actif'?'selected':'' ?>>Actifs</option>
            <option value="en_attente" <?= $filters['statut']==='en_attente'?'selected':'' ?>>En attente</option>
            <option value="inactif"    <?= $filters['statut']==='inactif'?'selected':'' ?>>Inactifs</option>
          </select>
          <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="nom"        <?= $sortBy==='nom'?'selected':'' ?>>Tri : nom</option>
            <option value="prenom"     <?= $sortBy==='prenom'?'selected':'' ?>>Tri : prénom</option>
            <option value="nb_enfants" <?= $sortBy==='nb_enfants'?'selected':'' ?>>Tri : # enfants</option>
          </select>
          <select name="dir" class="filter-select" onchange="this.form.submit()">
            <option value="asc"  <?= $sortDir==='asc'?'selected':'' ?>>↑ Asc</option>
            <option value="desc" <?= $sortDir==='desc'?'selected':'' ?>>↓ Desc</option>
          </select>
          <button type="submit" class="btn-chunky" style="padding:0.7rem 1.4rem;font-size:0.9rem;"><i class="fas fa-filter"></i> Filtrer</button>
          <?php if ($hasActiveFilters): ?>
            <a href="list.php" class="btn-reset"><i class="fas fa-times"></i> Réinitialiser</a>
          <?php endif; ?>
        </div>
      </form>
      <p class="results-count"><strong><?= $stats['total'] ?></strong> parent(s) affiché(s)<?= $hasActiveFilters ? ' — filtres actifs' : '' ?></p>
    </div>

    <div class="col-lg-4">
      <div class="chart-card">
        <h5><i class="fas fa-chart-pie" style="color:#FF8FAB"></i> Répartition statut</h5>
        <canvas id="chart-statuts"></canvas>
      </div>
    </div>
  </div>

  <div class="row g-4 justify-content-center">
    <?php foreach ($parents as $p): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-kider p-4 text-center">
          <div class="mb-3">
            <div class="avatar-circle girl d-inline-flex" style="width:90px;height:90px;background:linear-gradient(135deg,#FCE4EC,#F8BBD0);">
              <i class="fas fa-user" style="font-size:2.2rem;color:#C2185B;"></i>
            </div>
          </div>

          <h5 style="font-family:'Fredoka One',cursive; margin-bottom:0.3rem;">
            <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?>
          </h5>

          <?php if ($p['statut'] === 'actif'): ?>
            <span class="status-inscrit mb-2 d-inline-block"><i class="fas fa-check-circle"></i> Actif</span>
          <?php elseif ($p['statut'] === 'en_attente'): ?>
            <span class="status-archive mb-2 d-inline-block" style="background:#FFF3E0;color:#E65100;"><i class="fas fa-hourglass-half"></i> En attente</span>
          <?php else: ?>
            <span class="status-archive mb-2 d-inline-block"><i class="fas fa-pause-circle"></i> Inactif</span>
          <?php endif; ?>

          <div class="medical-box text-start mt-3">
            <p><i class="fas fa-envelope" style="color:#5B9BD5;"></i> <strong>Email :</strong> <?= htmlspecialchars($p['email']) ?></p>
            <p><i class="fas fa-phone" style="color:#4CAF50;"></i> <strong>Tél :</strong> <?= htmlspecialchars($p['telephone'] ?: '—') ?></p>
            <p class="mb-0"><i class="fas fa-child" style="color:#FF8FAB;"></i> <strong><?= (int)$p['nb_enfants'] ?> enfant(s)</strong>
              <?php if (!empty($p['enfants_noms'])): ?>
                <br><span style="color:#888;font-size:0.8rem;"><?= htmlspecialchars($p['enfants_noms']) ?></span>
              <?php endif; ?>
            </p>
          </div>

          <?php if ($_SESSION['user_role'] === 'admin'): ?>
          <div style="margin-top:0.8rem;display:flex;gap:0.5rem;justify-content:center;flex-wrap:wrap;">
            <span style="background:#FCE4EC;color:#AD1457;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;">
              <i class="fas fa-id-badge"></i> ID : <?= $p['id'] ?>
            </span>
            <?php if ($p['statut'] === 'actif'): ?>
              <a href="list.php?archive=<?= $p['id'] ?>" onclick="return confirm('Archiver ce compte parent ?')" style="background:#FFEBEE;color:#C62828;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;text-decoration:none;">
                <i class="fas fa-archive"></i> Archiver
              </a>
            <?php else: ?>
              <a href="list.php?activate=<?= $p['id'] ?>" onclick="return confirm('Réactiver ce compte parent ?')" style="background:#E8F5E9;color:#2E7D32;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;text-decoration:none;">
                <i class="fas fa-check-circle"></i> Réactiver
              </a>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <?php if (empty($parents)): ?>
    <div class="row justify-content-center">
      <div class="col-md-8 text-center">
        <div class="card-kider p-5">
          <i class="fas fa-search fa-4x" style="color:#ddd;"></i>
          <h5 style="font-family:'Fredoka One',cursive;color:#999;margin-top:1rem;">Aucun parent ne correspond aux filtres</h5>
          <?php if ($hasActiveFilters): ?>
            <a href="list.php" class="btn-reset mt-3"><i class="fas fa-times"></i> Réinitialiser les filtres</a>
          <?php endif; ?>
        </div>
      </div>
    </div>
  <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
(function(){
  var ctx = document.getElementById('chart-statuts');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Actifs', 'En attente', 'Inactifs'],
      datasets: [{
        data: [<?= $stats['actifs'] ?>, <?= $stats['enAttente'] ?>, <?= $stats['inactifs'] ?>],
        backgroundColor: ['#4CAF50', '#FFA726', '#EF5350'],
        borderWidth: 4,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { position: 'bottom', labels: { font: { family: 'Nunito', weight: '700', size: 12 }, boxWidth: 14, padding: 12 } } },
      cutout: '65%'
    }
  });
})();
</script>

<?php include '../template/footer.php'; ?>

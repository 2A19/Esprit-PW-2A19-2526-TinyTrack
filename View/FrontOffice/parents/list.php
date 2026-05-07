<?php
// Vue passive — données injectées par EducateurController::parents()
// Variables : $parents, $stats, $filters, $sortBy, $sortDir
$hasActiveFilters = !empty($filters['q']) || !empty($filters['statut']);
include __DIR__ . '/../template/header.php';
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
  </div>

  <div class="row">
    <div class="col-lg-8">
      <?php
        $searchActive = !empty($filters['q']);
        $filtreCount  = !empty($filters['statut']) ? 1 : 0;
        $sortActive   = isset($_GET['sort']) || isset($_GET['dir']) || ($sortBy !== 'nom' || $sortDir !== 'asc');
      ?>
      <form method="GET">

        <!-- 3 TOGGLE BUTTONS -->
        <div class="toggle-bar">
          <button type="button" data-target="panel-search" class="toggle-btn <?= $searchActive ? 'has-active is-open' : '' ?>">
            <i class="fas fa-search"></i> Recherche
            <i class="fas fa-chevron-down chev"></i>
          </button>
          <button type="button" data-target="panel-filter" class="toggle-btn <?= $filtreCount > 0 ? 'has-active is-open' : '' ?>">
            <i class="fas fa-sliders-h"></i> Filtration
            <?php if ($filtreCount > 0): ?><span class="count-badge"><?= $filtreCount ?></span><?php endif; ?>
            <i class="fas fa-chevron-down chev"></i>
          </button>
          <button type="button" data-target="panel-sort" class="toggle-btn <?= $sortActive ? 'has-active is-open' : '' ?>">
            <i class="fas fa-sort"></i> Tri
            <i class="fas fa-chevron-down chev"></i>
          </button>
        </div>

        <!-- PANEL : RECHERCHE -->
        <div id="panel-search" class="collapse-panel <?= $searchActive ? 'open' : '' ?>">
          <div class="search-input-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" name="q" class="filter-search" placeholder="Nom, prénom ou email du parent..." value="<?= htmlspecialchars($filters['q']) ?>">
          </div>
          <button type="submit" class="btn-chunky" style="padding:0.65rem 1.2rem;font-size:0.88rem;">
            <i class="fas fa-search"></i> Rechercher
          </button>
        </div>

        <!-- PANEL : FILTRATION -->
        <div id="panel-filter" class="collapse-panel <?= $filtreCount > 0 ? 'open' : '' ?>">
          <select name="statut" class="filter-select" onchange="this.form.submit()">
            <option value="">Statut : tous</option>
            <option value="actif"      <?= $filters['statut']==='actif'?'selected':'' ?>>Actifs</option>
            <option value="en_attente" <?= $filters['statut']==='en_attente'?'selected':'' ?>>En attente</option>
            <option value="inactif"    <?= $filters['statut']==='inactif'?'selected':'' ?>>Inactifs</option>
          </select>
          <div class="cp-spacer"></div>
          <?php if ($hasActiveFilters): ?>
            <a href="list.php" class="btn-reset"><i class="fas fa-times"></i> Réinitialiser</a>
          <?php endif; ?>
        </div>

        <!-- PANEL : TRI -->
        <div id="panel-sort" class="collapse-panel <?= $sortActive ? 'open' : '' ?>">
          <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="nom"        <?= $sortBy==='nom'?'selected':'' ?>>Trier par : nom</option>
            <option value="prenom"     <?= $sortBy==='prenom'?'selected':'' ?>>Trier par : prénom</option>
            <option value="nb_enfants" <?= $sortBy==='nb_enfants'?'selected':'' ?>>Trier par : # enfants</option>
          </select>
          <select name="dir" class="filter-select" onchange="this.form.submit()">
            <option value="asc"  <?= $sortDir==='asc'?'selected':'' ?>>↑ Croissant</option>
            <option value="desc" <?= $sortDir==='desc'?'selected':'' ?>>↓ Décroissant</option>
          </select>
        </div>

      </form>

      <p class="results-line"><strong><?= $stats['total'] ?></strong> parent(s) affiché(s)</p>

      <script>
        document.querySelectorAll('.toggle-btn').forEach(function (btn) {
          btn.addEventListener('click', function () {
            var target = document.getElementById(btn.dataset.target);
            if (!target) return;
            target.classList.toggle('open');
            btn.classList.toggle('is-open');
          });
        });
      </script>
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
              <form method="POST" action="/TinyTrack/educateurs/archive/<?= (int)$p['id'] ?>" style="display:inline" onsubmit="return confirm('Archiver ce compte parent ?')">
                <button type="submit" style="background:#FFEBEE;color:#C62828;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;border:none;cursor:pointer;">
                  <i class="fas fa-archive"></i> Archiver
                </button>
              </form>
            <?php else: ?>
              <form method="POST" action="/TinyTrack/educateurs/activate/<?= (int)$p['id'] ?>" style="display:inline" onsubmit="return confirm('Réactiver ce compte parent ?')">
                <button type="submit" style="background:#E8F5E9;color:#2E7D32;padding:0.3rem 0.8rem;border-radius:20px;font-size:0.75rem;font-weight:700;border:none;cursor:pointer;">
                  <i class="fas fa-check-circle"></i> Réactiver
                </button>
              </form>
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

<?php include __DIR__ . '/../template/footer.php'; ?>

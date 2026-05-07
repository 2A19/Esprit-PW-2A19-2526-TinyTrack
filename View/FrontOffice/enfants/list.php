<?php
// Vue passive — données injectées par EnfantController::mesEnfants()
// Variables : $enfants, $stats, $filters, $sortBy, $sortDir, $parentNom, $hasActiveFilters
include __DIR__ . '/../template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <?php if (isset($_GET['msg'])): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <?php if ($_GET['msg'] === 'archived'): ?>
        <div class="alert alert-warning" style="border-radius:14px;border:none;text-align:center;"><i class="fas fa-archive"></i> Enfant archivé.</div>
      <?php elseif ($_GET['msg'] === 'activated'): ?>
        <div class="alert alert-success" style="border-radius:14px;border:none;text-align:center;"><i class="fas fa-check-circle"></i> Enfant réactivé.</div>
      <?php endif; ?>
    </div></div>
  <?php endif; ?>

  <div class="text-center mb-4">
    <h2 class="section-title">
      <i class="fas fa-child"></i>
      <?php if ($_SESSION['user_role'] === 'parent'): ?>
        <?= count($enfants) > 1 ? 'Mes enfants' : 'Mon enfant' ?>
      <?php else: ?>
        Enfants
      <?php endif; ?>
    </h2>
    <div class="rainbow-divider"></div>
    <p class="text-muted mt-2">
      <?php if ($_SESSION['user_role'] === 'parent'): ?>
        Bonjour <strong style="color:#4CAF50;"><?= htmlspecialchars($parentNom) ?></strong> — voici <?= count($enfants) > 1 ? 'vos enfants' : 'votre enfant' ?>
      <?php else: ?>
        Bonjour <strong style="color:#4CAF50;"><?= htmlspecialchars($parentNom) ?></strong>
      <?php endif; ?>
    </p>
  </div>

  <?php if ($_SESSION['user_role'] !== 'parent'): ?>
  <!-- MINI STATS -->
  <div class="mini-stats-row">
    <div class="mini-stat ms-mint">
      <div class="ms-icon"><i class="fas fa-child"></i></div>
      <div>
        <p class="ms-value"><?= $stats['total'] ?></p>
        <p class="ms-label">Résultats</p>
      </div>
    </div>
    <div class="mini-stat ms-sky">
      <div class="ms-icon"><i class="fas fa-male"></i></div>
      <div>
        <p class="ms-value"><?= $stats['garcons'] ?></p>
        <p class="ms-label">Garçons</p>
      </div>
    </div>
    <div class="mini-stat ms-rose">
      <div class="ms-icon"><i class="fas fa-female"></i></div>
      <div>
        <p class="ms-value"><?= $stats['filles'] ?></p>
        <p class="ms-label">Filles</p>
      </div>
    </div>
    <div class="mini-stat ms-sun">
      <div class="ms-icon"><i class="fas fa-birthday-cake"></i></div>
      <div>
        <p class="ms-value"><?= $stats['ages']['0-2'] ?></p>
        <p class="ms-label">0-2 ans</p>
      </div>
    </div>
    <div class="mini-stat ms-grape">
      <div class="ms-icon"><i class="fas fa-star"></i></div>
      <div>
        <p class="ms-value"><?= $stats['ages']['3-4'] ?></p>
        <p class="ms-label">3-4 ans</p>
      </div>
    </div>
    <div class="mini-stat ms-coral" style="border-left-color:#FFA726;background:#fff;">
      <div class="ms-icon" style="background:#FFF3E0;color:#E65100;"><i class="fas fa-graduation-cap"></i></div>
      <div>
        <p class="ms-value" style="color:#E65100;"><?= $stats['ages']['5-6'] ?></p>
        <p class="ms-label">5-6 ans</p>
      </div>
    </div>
  </div>

  <div class="row">
    <div class="col-lg-8">
      <?php
        // Count what's active in each section, to highlight buttons + auto-open panels.
        // Educateur only has 1 meaningful filter (sexe) ; admin has the full set.
        $availableFilters = ($_SESSION['user_role'] === 'admin') ? ['sexe', 'statut', 'niveau'] : ['sexe'];
        $searchActive = !empty($filters['q']);
        $filtreCount = 0;
        foreach ($availableFilters as $k) {
            if (!empty($filters[$k])) $filtreCount++;
        }
        $sortActive = isset($_GET['sort']) || isset($_GET['dir']) || ($sortBy !== 'date_inscription' || $sortDir !== 'desc');
      ?>
      <form method="GET" id="enfants-form">

        <!-- 3 TOGGLE BUTTONS -->
        <div class="toggle-bar">
          <button type="button" data-target="panel-search" class="toggle-btn <?= $searchActive ? 'has-active is-open' : '' ?>">
            <i class="fas fa-search"></i> Recherche
            <i class="fas fa-chevron-down chev"></i>
          </button>
          <button type="button" data-target="panel-filter" class="toggle-btn <?= $filtreCount > 0 ? 'has-active is-open' : '' ?>">
            <i class="fas fa-sliders-h"></i> Filtration
            <?php if ($filtreCount > 0): ?>
              <span class="count-badge"><?= $filtreCount ?></span>
            <?php endif; ?>
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
            <input type="text" name="q" class="filter-search" placeholder="Nom, prénom ou code de l'enfant..." value="<?= htmlspecialchars($filters['q']) ?>">
          </div>
          <button type="submit" class="btn-chunky" style="padding:0.65rem 1.2rem;font-size:0.88rem;">
            <i class="fas fa-search"></i> Rechercher
          </button>
        </div>

        <!-- PANEL : FILTRATION -->
        <div id="panel-filter" class="collapse-panel <?= $filtreCount > 0 ? 'open' : '' ?>">
          <select name="sexe" class="filter-select" onchange="this.form.submit()">
            <option value="">Sexe : tous</option>
            <option value="M" <?= $filters['sexe']==='M'?'selected':'' ?>>Garçons</option>
            <option value="F" <?= $filters['sexe']==='F'?'selected':'' ?>>Filles</option>
          </select>
          <?php if ($_SESSION['user_role'] === 'admin'): ?>
          <select name="statut" class="filter-select" onchange="this.form.submit()">
            <option value="">Statut : tous</option>
            <option value="actif"   <?= $filters['statut']==='actif'?'selected':'' ?>>Actifs</option>
            <option value="archive" <?= $filters['statut']==='archive'?'selected':'' ?>>Archivés</option>
          </select>
          <select name="niveau" class="filter-select" onchange="this.form.submit()">
            <option value="">Niveau : tous</option>
            <option value="petit" <?= $filters['niveau']==='petit'?'selected':'' ?>>Petit</option>
            <option value="moyen" <?= $filters['niveau']==='moyen'?'selected':'' ?>>Moyen</option>
            <option value="grand" <?= $filters['niveau']==='grand'?'selected':'' ?>>Grand</option>
          </select>
          <?php endif; ?>
          <div class="cp-spacer"></div>
          <?php if ($hasActiveFilters): ?>
            <a href="list.php" class="btn-reset"><i class="fas fa-times"></i> Réinitialiser</a>
          <?php endif; ?>
        </div>

        <!-- PANEL : TRI -->
        <div id="panel-sort" class="collapse-panel <?= $sortActive ? 'open' : '' ?>">
          <select name="sort" class="filter-select" onchange="this.form.submit()">
            <option value="date_inscription" <?= $sortBy==='date_inscription'?'selected':'' ?>>Trier par : inscription</option>
            <option value="nom"              <?= $sortBy==='nom'?'selected':'' ?>>Trier par : nom</option>
            <option value="prenom"           <?= $sortBy==='prenom'?'selected':'' ?>>Trier par : prénom</option>
            <option value="age"              <?= $sortBy==='age'?'selected':'' ?>>Trier par : âge</option>
          </select>
          <select name="dir" class="filter-select" onchange="this.form.submit()">
            <option value="desc" <?= $sortDir==='desc'?'selected':'' ?>>↓ Décroissant</option>
            <option value="asc"  <?= $sortDir==='asc'?'selected':'' ?>>↑ Croissant</option>
          </select>
        </div>

      </form>

      <p class="results-line"><strong><?= $stats['total'] ?></strong> enfant(s) affiché(s)</p>

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
      <!-- CHART -->
      <div class="chart-card">
        <h5><i class="fas fa-chart-pie" style="color:#26A69A"></i> Répartition par âge</h5>
        <canvas id="chart-ages"></canvas>
      </div>
    </div>
  </div>
  <?php endif; // mini-stats + filters + chart : admin/educateur only ?>

  <!-- LIST -->
  <?php foreach ($enfants as $e): ?>
    <?php
      $groupe    = $e['groupe']    ?? null;
      $educateur = $e['educateur'] ?? null;
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
                <?php if (!empty($e['code_unique'])): ?>
                <span style="background:rgba(0,0,0,0.15);color:#fff;padding:0.2rem 0.7rem;border-radius:15px;font-size:0.75rem;font-weight:700;font-family:'Courier New',monospace;">
                  <?= htmlspecialchars($e['code_unique']) ?>
                </span>
                <?php endif; ?>
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

          <?php if ($_SESSION['user_role'] === 'admin'): ?>
          <div style="padding:0 2rem 1rem;text-align:center;">
            <?php if ($e['statut'] === 'actif'): ?>
              <form method="POST" action="/TinyTrack/enfants/archive/<?= (int)$e['id'] ?>" style="display:inline" onsubmit="return confirm('Archiver cet enfant ?')">
                <button type="submit" class="btn btn-sm" style="background:#FFEBEE;color:#C62828;border-radius:20px;font-weight:700;font-size:0.8rem;border:none;">
                  <i class="fas fa-archive"></i> Archiver
                </button>
              </form>
            <?php else: ?>
              <form method="POST" action="/TinyTrack/enfants/activate/<?= (int)$e['id'] ?>" style="display:inline" onsubmit="return confirm('Réactiver cet enfant ?')">
                <button type="submit" class="btn btn-sm" style="background:#E8F5E9;color:#2E7D32;border-radius:20px;font-weight:700;font-size:0.8rem;border:none;">
                  <i class="fas fa-check-circle"></i> Réactiver
                </button>
              </form>
              <span style="background:#FFEBEE;color:#C62828;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.7rem;font-weight:700;margin-left:0.3rem;"><i class="fas fa-ban"></i> Archivé</span>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  <?php endforeach; ?>

  <?php if (empty($enfants)): ?>
    <div class="row justify-content-center">
      <div class="col-md-8 text-center">
        <div class="card-kider p-5">
          <i class="fas fa-search fa-4x" style="color:#ddd;"></i>
          <h5 style="font-family:'Fredoka One',cursive;color:#999;margin-top:1rem;">Aucun enfant ne correspond aux filtres</h5>
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
  var ctx = document.getElementById('chart-ages');
  if (!ctx) return;
  new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['0-2 ans', '3-4 ans', '5-6 ans'],
      datasets: [{
        data: [<?= $stats['ages']['0-2'] ?>, <?= $stats['ages']['3-4'] ?>, <?= $stats['ages']['5-6'] ?>],
        backgroundColor: ['#FFD93D', '#9C7CDB', '#FFA726'],
        borderWidth: 4,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      plugins: {
        legend: { position: 'bottom', labels: { font: { family: 'Nunito', weight: '700', size: 12 }, boxWidth: 14, padding: 12 } }
      },
      cutout: '65%'
    }
  });
})();
</script>

<?php include __DIR__ . '/../template/footer.php'; ?>

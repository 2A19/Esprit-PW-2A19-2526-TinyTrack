<?php
// Vue passive — données injectées par DashboardController::educateur()
// Variables : $groupe, $enfants, $profil, $parents, $rapports
include __DIR__ . '/template/header.php';
?>

<div class="container py-5" style="position:relative;z-index:1;">

  <!-- HERO -->
  <section class="hero-kids mb-4">
    <div class="row align-items-center">
      <div class="col-md-8">
        <span class="badge" style="background:#fff;color:#5B9BD5;border-radius:50px;padding:0.4rem 1rem;font-weight:800;box-shadow:0 3px 10px rgba(0,0,0,0.05);">
          &#x1F4DA; Tableau de bord &Eacute;ducateur
        </span>
        <h1 class="mt-3">
          Bonjour <span class="accent-pink"><?= htmlspecialchars($profil['prenom']) ?></span>,
          <span class="accent-yellow">bonne journ&eacute;e</span> <span class="accent-teal">!</span>
        </h1>
        <p class="lead">
          <?php if ($groupe): ?>
            Vous &ecirc;tes en charge du groupe <strong><?= htmlspecialchars($groupe['nom']) ?></strong>
            (niveau <?= htmlspecialchars($groupe['niveau']) ?>) &mdash; <?= count($enfants) ?> enfant(s).
          <?php else: ?>
            Aucun groupe ne vous est encore assign&eacute;. Contactez l'administration.
          <?php endif; ?>
        </p>
      </div>
    </div>
  </section>

  <!-- QUICK STATS -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-6">
      <div class="card-kider p-3 text-center">
        <div style="font-size:2rem;color:#5B9BD5;"><i class="fas fa-child"></i></div>
        <div style="font-family:'Fredoka One',cursive;font-size:1.6rem;color:#2D3436;"><?= count($enfants) ?></div>
        <div style="font-weight:700;color:#888;font-size:0.85rem;">Mes enfants</div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card-kider p-3 text-center">
        <div style="font-size:2rem;color:#FF8FAB;"><i class="fas fa-users"></i></div>
        <div style="font-family:'Fredoka One',cursive;font-size:1.6rem;color:#2D3436;"><?= count($parents) ?></div>
        <div style="font-weight:700;color:#888;font-size:0.85rem;">Parents</div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card-kider p-3 text-center">
        <div style="font-size:2rem;color:#FFA726;"><i class="fas fa-book"></i></div>
        <div style="font-family:'Fredoka One',cursive;font-size:1.6rem;color:#2D3436;"><?= count($rapports) ?></div>
        <div style="font-weight:700;color:#888;font-size:0.85rem;">Rapports r&eacute;dig&eacute;s</div>
      </div>
    </div>
    <div class="col-md-3 col-6">
      <div class="card-kider p-3 text-center">
        <div style="font-size:2rem;color:#9C7CDB;"><i class="fas fa-layer-group"></i></div>
        <div style="font-family:'Fredoka One',cursive;font-size:1.4rem;color:#2D3436;"><?= $groupe ? htmlspecialchars($groupe['nom']) : '&mdash;' ?></div>
        <div style="font-weight:700;color:#888;font-size:0.85rem;">Mon groupe</div>
      </div>
    </div>
  </div>

  <!-- MON PROFIL -->
  <div class="card-kider p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h3 style="font-family:'Fredoka One',cursive;color:#5B9BD5;margin:0;"><i class="fas fa-user-tie"></i> Mon profil</h3>
      <a href="/TinyTrack/profil" class="btn-kider"><i class="fas fa-edit"></i> Voir / Modifier</a>
    </div>
    <div class="row">
      <div class="col-md-6 mb-2">
        <div class="medical-box">
          <p><i class="fas fa-id-badge" style="color:#5B9BD5;"></i> <strong>Code :</strong> <?= htmlspecialchars($profil['code_unique'] ?? '') ?></p>
          <p><i class="fas fa-user" style="color:#5B9BD5;"></i> <strong>Nom complet :</strong> <?= htmlspecialchars($profil['prenom'] . ' ' . $profil['nom']) ?></p>
          <p class="mb-0"><i class="fas fa-envelope" style="color:#9C7CDB;"></i> <strong>Email :</strong> <?= htmlspecialchars($profil['email']) ?></p>
        </div>
      </div>
      <div class="col-md-6 mb-2">
        <div class="medical-box">
          <p><i class="fas fa-phone" style="color:#4CAF50;"></i> <strong>T&eacute;l&eacute;phone :</strong> <?= htmlspecialchars($profil['telephone'] ?: '—') ?></p>
          <?php if (!empty($profil['specialite'])): ?>
            <p><i class="fas fa-star" style="color:#FFA726;"></i> <strong>Sp&eacute;cialit&eacute; :</strong> <?= htmlspecialchars($profil['specialite']) ?></p>
          <?php endif; ?>
          <p class="mb-0"><i class="fas fa-toggle-on" style="color:#4CAF50;"></i> <strong>Statut :</strong>
            <span style="background:#E8F5E9;color:#2E7D32;padding:0.15rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= htmlspecialchars($profil['statut']) ?></span>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- MES ENFANTS -->
  <div class="card-kider p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h3 style="font-family:'Fredoka One',cursive;color:#FF8FAB;margin:0;"><i class="fas fa-child"></i> Mes enfants assign&eacute;s</h3>
      <a href="/TinyTrack/mes-enfants" class="btn-kider"><i class="fas fa-list"></i> Voir tout</a>
    </div>
    <?php if (empty($enfants)): ?>
      <div class="empty-state text-center">
        <i class="fas fa-child fa-3x mb-2"></i>
        <p style="color:#888;font-weight:700;">Aucun enfant assign&eacute; pour le moment.</p>
      </div>
    <?php else: ?>
      <div class="row g-3">
        <?php foreach ($enfants as $e): ?>
          <div class="col-md-4 col-sm-6">
            <div style="background:#FAFAFA;border-radius:14px;padding:1rem;text-align:center;border:1px solid rgba(0,0,0,0.04);">
              <div class="avatar-circle <?= $e['sexe'] === 'M' ? 'boy' : 'girl' ?> mx-auto mb-2" style="width:60px;height:60px;">
                <i class="fas <?= $e['sexe'] === 'M' ? 'fa-child' : 'fa-child-dress' ?>" style="font-size:1.6rem;color:<?= $e['sexe'] === 'M' ? '#5B9BD5' : '#FF8FAB' ?>;"></i>
              </div>
              <div style="font-weight:800;color:#2D3436;"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></div>
              <div style="font-size:0.78rem;color:#888;">
                <?= htmlspecialchars($e['code_unique']) ?>
              </div>
              <?php if (!empty($e['date_naissance'])): ?>
                <div style="font-size:0.78rem;color:#999;margin-top:0.3rem;">
                  <i class="fas fa-birthday-cake"></i> <?= date('d/m/Y', strtotime($e['date_naissance'])) ?>
                </div>
              <?php endif; ?>
              <?php if (!empty($e['allergies'])): ?>
                <div style="margin-top:0.4rem;font-size:0.72rem;color:#C62828;background:#FFEBEE;border-radius:8px;padding:0.2rem 0.5rem;">
                  <i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($e['allergies']) ?>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <!-- CONTACTS PARENTS -->
  <div class="card-kider p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h3 style="font-family:'Fredoka One',cursive;color:#FFA726;margin:0;"><i class="fas fa-address-book"></i> Contacts des parents</h3>
      <?php if (!empty($parents)): ?>
        <div class="search-input-wrapper" style="flex:0 1 280px;max-width:100%;">
          <i class="fas fa-search"></i>
          <input type="text" id="search-parent-by-child" class="filter-search" placeholder="Tapez le nom de l'enfant...">
        </div>
      <?php endif; ?>
    </div>
    <?php if (empty($parents)): ?>
      <div class="empty-state text-center">
        <i class="fas fa-users fa-3x mb-2"></i>
        <p style="color:#888;font-weight:700;">Aucun parent &agrave; contacter.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle" id="parents-table">
          <thead>
            <tr style="background:#FFF8E1;">
              <th style="border:none;font-weight:800;color:#F57F17;">Parent</th>
              <th style="border:none;font-weight:800;color:#F57F17;">Enfant(s)</th>
              <th style="border:none;font-weight:800;color:#F57F17;"><i class="fas fa-envelope"></i> Email</th>
              <th style="border:none;font-weight:800;color:#F57F17;"><i class="fas fa-phone"></i> T&eacute;l&eacute;phone</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($parents as $p): ?>
              <tr data-enfants="<?= htmlspecialchars(mb_strtolower($p['enfants_noms'] ?? '')) ?>">
                <td style="font-weight:700;"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></td>
                <td><span style="background:#FCE4EC;color:#C2185B;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= htmlspecialchars($p['enfants_noms']) ?></span></td>
                <td>
                  <?php if (!empty($p['email'])): ?>
                    <a href="mailto:<?= htmlspecialchars($p['email']) ?>" style="color:#9C7CDB;font-weight:700;text-decoration:none;"><?= htmlspecialchars($p['email']) ?></a>
                  <?php else: ?>
                    <span style="color:#bbb;">&mdash;</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if (!empty($p['telephone'])): ?>
                    <a href="tel:<?= htmlspecialchars($p['telephone']) ?>" style="color:#4CAF50;font-weight:700;text-decoration:none;"><?= htmlspecialchars($p['telephone']) ?></a>
                  <?php else: ?>
                    <span style="color:#bbb;">&mdash;</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p id="parents-no-result" style="display:none;color:#888;text-align:center;font-style:italic;margin-top:0.5rem;">
          <i class="fas fa-search"></i> Aucun parent trouv&eacute; pour cet enfant.
        </p>
      </div>
      <script>
        (function () {
          var input = document.getElementById('search-parent-by-child');
          var table = document.getElementById('parents-table');
          var noResult = document.getElementById('parents-no-result');
          if (!input || !table) return;
          input.addEventListener('input', function () {
            var q = this.value.trim().toLowerCase();
            var rows = table.querySelectorAll('tbody tr');
            var visibleCount = 0;
            rows.forEach(function (r) {
              var hay = r.dataset.enfants || '';
              var match = q === '' || hay.indexOf(q) !== -1;
              r.style.display = match ? '' : 'none';
              if (match) visibleCount++;
            });
            noResult.style.display = (visibleCount === 0 && q !== '') ? 'block' : 'none';
          });
        })();
      </script>
    <?php endif; ?>
  </div>

  <!-- HISTORIQUE RAPPORTS -->
  <div class="card-kider p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
      <h3 style="font-family:'Fredoka One',cursive;color:#9C7CDB;margin:0;"><i class="fas fa-book"></i> Mes rapports r&eacute;dig&eacute;s</h3>
      <a href="/TinyTrack/rapports/add" class="btn-kider"><i class="fas fa-plus"></i> Nouveau rapport</a>
    </div>
    <?php if (empty($rapports)): ?>
      <div class="empty-state text-center">
        <i class="fas fa-book fa-3x mb-2"></i>
        <p style="color:#888;font-weight:700;">Aucun rapport r&eacute;dig&eacute; pour le moment.</p>
      </div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr style="background:#F3E8FF;">
              <th style="border:none;font-weight:800;color:#6A1B9A;">Date</th>
              <th style="border:none;font-weight:800;color:#6A1B9A;">Activit&eacute;</th>
              <th style="border:none;font-weight:800;color:#6A1B9A;">Contenu</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rapports as $r): ?>
              <tr>
                <td style="white-space:nowrap;font-weight:700;color:#5B9BD5;">
                  <i class="fas fa-calendar-day"></i> <?= !empty($r['date_rapport']) ? date('d/m/Y', strtotime($r['date_rapport'])) : '—' ?>
                </td>
                <td>
                  <?php if (!empty($r['nom_activite'])): ?>
                    <span style="background:#E0F4F1;color:#00796B;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= htmlspecialchars($r['nom_activite']) ?></span>
                  <?php else: ?>
                    <span style="color:#bbb;">&mdash;</span>
                  <?php endif; ?>
                </td>
                <td style="font-size:0.9rem;color:#555;"><?= nl2br(htmlspecialchars($r['contenu_rapport'])) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php include __DIR__ . '/template/footer.php'; ?>

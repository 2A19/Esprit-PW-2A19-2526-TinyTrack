<?php
require_once '../../controller/RapportController.php';

$controller = new RapportController();

$tri = isset($_GET['tri']) ? $_GET['tri'] : 'desc';

if ($tri !== 'asc' && $tri !== 'desc') {
    $tri = 'desc';
}

$rapports = $controller->listRapportsWithActivite($tri)->fetchAll();

include 'template/header.php';
?>

<style>
.custom-tri-dropdown {
    position: relative;
    display: inline-block;
}

.custom-tri-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 110%;
    min-width: 180px;
    background: #ffffff;
    border: 1px solid #ddd;
    border-radius: 8px;
    box-shadow: 0 6px 15px rgba(0,0,0,0.15);
    z-index: 99999;
    overflow: hidden;
}

.custom-tri-menu a {
    display: block;
    padding: 10px 14px;
    color: #212529;
    text-decoration: none;
    font-weight: 600;
    text-align: left;
}

.custom-tri-menu a:hover {
    background: #28a745;
    color: #ffffff;
    text-decoration: none;
}
</style>

<div class="container py-5">
  <div class="text-center mb-5">
    <img src="/ProjetRapport/tinytrack/assets/images/logo.png" alt="TinyTrack" style="height:70px;margin-bottom:15px;">
    <h2 class="section-title"><i class="fas fa-book"></i> Rapports Journaliers</h2>
    <p class="text-muted mt-3">Consultez les rapports quotidiens de vos enfants</p>

    <div class="mt-3">
      <div class="custom-tri-dropdown">
        <button type="button" class="btn btn-success" onclick="toggleTriMenu(event)">
          <i class="fas fa-sort"></i> Trier par ▾
        </button>

        <div id="triMenu" class="custom-tri-menu">
          <a href="listRapportsParent.php?tri=desc">
            <i class="fas fa-sort-amount-down"></i> Plus récent
          </a>

          <a href="listRapportsParent.php?tri=asc">
            <i class="fas fa-sort-amount-up"></i> Plus ancien
          </a>
        </div>
      </div>
    </div>
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

            <p style="font-size:0.85rem;color:#666;margin-bottom:0.8rem;">
              <?= htmlspecialchars(substr($r['contenu_rapport'], 0, 120)) ?><?= strlen($r['contenu_rapport']) > 120 ? '...' : '' ?>
            </p>

            <div class="medical-box">
              <p><i class="fas fa-child" style="color:var(--kider-orange);"></i> <strong>ID Enfant :</strong> <?= $r['id_enfant'] ?? '—' ?></p>
              <p><i class="fas fa-calendar" style="color:var(--kider-blue);"></i> <strong>Date :</strong> <?= $r['date_rapport'] ?></p>
              <p class="mb-0"><i class="fas fa-user-tie" style="color:var(--kider-green);"></i> <strong>Éducateur ID :</strong> <?= $r['id_educateur'] ?? '—' ?></p>
            </div>

            <div class="mt-3 text-center">
              <a 
                href="/ProjetRapport/tinytrack/view/frontoffice/exportRapportPdf.php?id=<?= $r['id_rapport'] ?>" 
                class="btn btn-danger btn-sm"
                style="border-radius:25px;padding:0.45rem 1.2rem;"
              >
                <i class="fas fa-file-pdf"></i> Exporter PDF
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</div>

<script>
function toggleTriMenu(event) {
    event.stopPropagation();

    var menu = document.getElementById('triMenu');

    if (menu.style.display === 'block') {
        menu.style.display = 'none';
    } else {
        menu.style.display = 'block';
    }
}

document.addEventListener('click', function(event) {
    var dropdown = document.querySelector('.custom-tri-dropdown');
    var menu = document.getElementById('triMenu');

    if (dropdown && menu && !dropdown.contains(event.target)) {
        menu.style.display = 'none';
    }
});
</script>

<?php include 'template/footer.php'; ?>
<?php
// Vue passive — données injectées par EducateurController::parents()
// Variables : $parents, $stats, $filters, $sortBy, $sortDir
include __DIR__ . '/../../FrontOffice/template/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/css/dataTables.bootstrap5.min.css">

<style>
  .ev-stat {
    background:#fff; border-radius:24px; padding:1.5rem 1.8rem;
    box-shadow:0 4px 20px rgba(0,0,0,0.06);
    transition:all 0.3s; display:flex; align-items:center; justify-content:space-between;
  }
  .ev-stat:hover { transform:translateY(-4px); box-shadow:0 10px 30px rgba(0,0,0,0.1); }
  .ev-stat .num { font-family:'Fredoka One',cursive; font-size:2.4rem; line-height:1; }
  .ev-stat .lbl { color:#666; font-weight:700; font-size:0.9rem; margin-top:0.3rem; }
  .ev-stat .ic  { font-size:2.2rem; opacity:0.25; }
  .ev-stat.mint   { background:linear-gradient(135deg,#E8F5E9,#FFFFFF); border:1px solid #C8E6C9; }
  .ev-stat.mint   .num, .ev-stat.mint   .ic { color:#2E7D32; }
  .ev-stat.sun    { background:linear-gradient(135deg,#FFF8E1,#FFFFFF); border:1px solid #FFE082; }
  .ev-stat.sun    .num, .ev-stat.sun    .ic { color:#E65100; }
  .ev-stat.coral  { background:linear-gradient(135deg,#FCE4EC,#FFFFFF); border:1px solid #F8BBD0; }
  .ev-stat.coral  .num, .ev-stat.coral  .ic { color:#C2185B; }
  .ev-stat.grey   { background:linear-gradient(135deg,#ECEFF1,#FFFFFF); border:1px solid #CFD8DC; }
  .ev-stat.grey   .num, .ev-stat.grey   .ic { color:#546E7A; }

  .ev-card {
    background:#fff; border-radius:24px; padding:1.5rem;
    box-shadow:0 4px 20px rgba(0,0,0,0.06); border:none; position:relative; overflow:hidden;
  }
  .ev-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:4px;
    background:linear-gradient(90deg,#4CAF50,#5B9BD5,#FFD93D,#FF8FAB,#FFA726);
    border-radius:24px 24px 0 0;
  }

  .ev-search { border-radius:50px; border:2px solid #E8E8E8; padding:0.6rem 1.2rem 0.6rem 2.5rem; width:100%; font-family:'Nunito',sans-serif; font-weight:600; transition:all 0.2s; }
  .ev-search:focus { border-color:#FF8FAB; outline:none; box-shadow:0 0 0 4px rgba(255,143,171,0.15); }
  .ev-search-wrap { position:relative; }
  .ev-search-wrap i { position:absolute; left:0.9rem; top:50%; transform:translateY(-50%); color:#999; }

  .btn-reset {
    background:#fff; color:#666 !important; border:2px solid #E8E8E8;
    border-radius:50px; padding:0.55rem 1.1rem; font-weight:700;
    text-decoration:none; display:inline-flex; align-items:center; gap:0.4rem; transition:all 0.2s;
  }
  .btn-reset:hover { border-color:#FF8FAB; color:#C2185B !important; }

  .ev-table { font-family:'Nunito',sans-serif; }
  .ev-table thead th {
    background:linear-gradient(135deg,#FFF9F0,#FFFFFF) !important;
    color:#555 !important; font-weight:800; border:none !important;
    padding:1rem 0.8rem; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;
  }
  .ev-table tbody td { padding:0.9rem 0.8rem; vertical-align:middle; border-color:#F5F5F5 !important; font-size:0.9rem; }
  .ev-table tbody tr:hover { background:rgba(255,143,171,0.04); }

  .pill-statut {
    display:inline-block; padding:0.3rem 0.8rem; border-radius:20px;
    font-size:0.75rem; font-weight:800;
  }
  .pill-statut.actif      { background:#E8F5E9; color:#2E7D32; }
  .pill-statut.en_attente { background:#FFF3E0; color:#E65100; }
  .pill-statut.inactif    { background:#E2E3E5; color:#41464B; }

  .pill-enfants {
    display:inline-flex; align-items:center; gap:0.3rem;
    padding:0.3rem 0.7rem; border-radius:14px;
    font-size:0.78rem; font-weight:800;
    background:#FFF3E0; color:#E65100;
  }

  .btn-act {
    width:34px; height:34px; border-radius:50%; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    transition:all 0.2s; cursor:pointer; margin:0 2px; text-decoration:none;
  }
  .btn-act.archive    { background:#E2E3E5; color:#41464B; }
  .btn-act.unarchive  { background:#E8F5E9; color:#2E7D32; }
  .btn-act:hover { transform:scale(1.1); }

  .breadcrumb-kider { background:transparent; padding:0; font-size:0.85rem; color:#999; font-weight:700; }
  .breadcrumb-kider a { color:#5B9BD5; text-decoration:none; }
  .breadcrumb-kider a:hover { text-decoration:underline; }

  .parent-name { font-family:'Fredoka One',cursive; color:#333; }

  .parent-avatar {
    width:38px; height:38px; border-radius:50%;
    background:linear-gradient(135deg,#FCE4EC,#F8BBD0);
    display:inline-flex; align-items:center; justify-content:center;
    color:#C2185B; font-weight:800; margin-right:0.6rem;
    box-shadow:0 2px 6px rgba(255,143,171,0.25);
  }

  .email-link { color:#5B9BD5; text-decoration:none; font-weight:600; }
  .email-link:hover { color:#1565C0; text-decoration:underline; }
</style>

<div class="container py-5" style="position:relative;z-index:1;">

  <?php if (isset($_GET['msg'])): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <div class="alert alert-<?= $_GET['msg'] === 'archived' ? 'warning' : 'success' ?>" style="border-radius:16px;border:none;text-align:center;">
        <?= $_GET['msg'] === 'archived' ? 'Compte archivé.' : 'Compte réactivé.' ?>
      </div>
    </div></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
    <div>
      <nav class="breadcrumb-kider mb-1"><a href="/TinyTrack/dashboard">Dashboard</a> / Parents</nav>
      <h2 class="section-title" style="margin:0;color:#FF8FAB;"><i class="fas fa-users"></i> Liste des parents</h2>
      <div class="rainbow-divider mt-2"></div>
    </div>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat mint">
        <div><div class="num"><?= (int)$stats['actifs'] ?></div><div class="lbl">Parents actifs</div></div>
        <i class="fas fa-user-check ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat coral">
        <div><div class="num"><?= (int)$stats['total'] ?></div><div class="lbl">Résultats</div></div>
        <i class="fas fa-list ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat sun">
        <div><div class="num"><?= (int)$stats['enAttente'] ?></div><div class="lbl">En attente</div></div>
        <i class="fas fa-hourglass-half ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat grey">
        <div><div class="num"><?= (int)($stats['inactifs'] ?? 0) ?></div><div class="lbl">Inactifs</div></div>
        <i class="fas fa-pause-circle ic"></i>
      </div>
    </div>
  </div>

  <!-- Card -->
  <div class="ev-card">
    <div class="row align-items-center mb-3 g-2">
      <div class="col-md-5">
        <form method="GET" action="/TinyTrack/parents" class="ev-search-wrap">
          <i class="fas fa-search"></i>
          <input type="text" name="q" class="ev-search" placeholder="Nom, prénom ou email..." value="<?= htmlspecialchars($filters['q'] ?? '') ?>" id="searchInput">
        </form>
      </div>
      <div class="col-md-4 text-md-center"><span style="color:#999;font-weight:700;font-size:0.9rem;">Liste complète</span></div>
      <div class="col-md-3 text-md-end">
        <?php if (!empty($filters['q']) || !empty($filters['statut'])): ?>
          <a href="/TinyTrack/parents" class="btn-reset"><i class="fas fa-times"></i> Réinitialiser</a>
        <?php endif; ?>
      </div>
    </div>

    <div class="table-responsive">
      <table id="tableParents" class="table ev-table">
        <thead>
          <tr><th>#</th><th>Parent</th><th>Email</th><th>Téléphone</th><th class="text-center">Enfants</th><th>Statut</th><th class="text-center">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($parents as $p):
            $initiale = strtoupper(substr($p['prenom'] ?? '?', 0, 1));
            $statutLabels = ['actif'=>'Actif', 'en_attente'=>'En attente', 'inactif'=>'Inactif'];
            $statutIcons  = ['actif'=>'check-circle', 'en_attente'=>'hourglass-half', 'inactif'=>'pause-circle'];
          ?>
          <tr>
            <td><strong style="color:#999;">#<?= (int)$p['id'] ?></strong></td>
            <td>
              <div class="d-flex align-items-center">
                <span class="parent-avatar"><?= htmlspecialchars($initiale) ?></span>
                <div>
                  <div class="parent-name"><?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?></div>
                </div>
              </div>
            </td>
            <td><a class="email-link" href="mailto:<?= htmlspecialchars($p['email']) ?>"><?= htmlspecialchars($p['email']) ?></a></td>
            <td style="color:#666;"><?= !empty($p['telephone']) ? htmlspecialchars($p['telephone']) : '<span style="color:#ccc;">—</span>' ?></td>
            <td class="text-center">
              <span class="pill-enfants"><i class="fas fa-child"></i> <?= (int)$p['nb_enfants'] ?></span>
              <?php if (!empty($p['enfants_noms'])): ?>
                <div style="color:#999;font-size:0.75rem;margin-top:0.2rem;"><?= htmlspecialchars($p['enfants_noms']) ?></div>
              <?php endif; ?>
            </td>
            <td>
              <span class="pill-statut <?= $p['statut'] ?>"><i class="fas fa-<?= $statutIcons[$p['statut']] ?? 'circle' ?>"></i> <?= $statutLabels[$p['statut']] ?? $p['statut'] ?></span>
            </td>
            <td class="text-center" style="white-space:nowrap;">
              <?php if ($p['statut'] === 'actif'): ?>
                <form method="POST" action="/TinyTrack/educateurs/archive/<?= (int)$p['id'] ?>" style="display:inline" onsubmit="return confirm('Archiver ce compte parent ?');">
                  <button type="submit" class="btn-act archive" title="Archiver"><i class="fas fa-archive"></i></button>
                </form>
              <?php else: ?>
                <form method="POST" action="/TinyTrack/educateurs/activate/<?= (int)$p['id'] ?>" style="display:inline">
                  <button type="submit" class="btn-act unarchive" title="Réactiver"><i class="fas fa-undo"></i></button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../../FrontOffice/template/footer.php'; ?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function(){
  var table = $('#tableParents').DataTable({
    "language":{"emptyTable":"Aucun parent trouvé","info":"_START_ à _END_ sur _TOTAL_ parents","infoEmpty":"Aucun","lengthMenu":"Afficher _MENU_","zeroRecords":"Aucun résultat","paginate":{"next":"Suivant","previous":"Précédent"}},
    "pageLength":10, "order":[[1,"asc"]], "dom":"lrtip"
  });
  $('#searchInput').on('keyup', function(){ table.search(this.value).draw(); });
});
</script>

<?php
// Vue passive — données injectées par EnfantController::index()
// Variables disponibles : $enfants, $totalEnfants, $search, $msg
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
  .ev-stat.sky    { background:linear-gradient(135deg,#E3F2FD,#FFFFFF); border:1px solid #BBDEFB; }
  .ev-stat.sky    .num, .ev-stat.sky    .ic { color:#1565C0; }
  .ev-stat.coral  { background:linear-gradient(135deg,#FCE4EC,#FFFFFF); border:1px solid #F8BBD0; }
  .ev-stat.coral  .num, .ev-stat.coral  .ic { color:#C2185B; }
  .ev-stat.grape  { background:linear-gradient(135deg,#F3E5F5,#FFFFFF); border:1px solid #E1BEE7; }
  .ev-stat.grape  .num, .ev-stat.grape  .ic { color:#6A1B9A; }

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
  .ev-search:focus { border-color:#4CAF50; outline:none; box-shadow:0 0 0 4px rgba(76,175,80,0.1); }
  .ev-search-wrap { position:relative; }
  .ev-search-wrap i { position:absolute; left:0.9rem; top:50%; transform:translateY(-50%); color:#999; }

  .btn-ev-add {
    background:linear-gradient(135deg,#4CAF50,#66BB6A); color:#fff !important;
    border:none; border-radius:50px; padding:0.65rem 1.6rem;
    font-weight:800; box-shadow:0 4px 14px rgba(76,175,80,0.3); transition:all 0.2s;
    text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;
  }
  .btn-ev-add:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(76,175,80,0.4); color:#fff !important; }

  .ev-table { font-family:'Nunito',sans-serif; }
  .ev-table thead th {
    background:linear-gradient(135deg,#FFF9F0,#FFFFFF) !important;
    color:#555 !important; font-weight:800; border:none !important;
    padding:1rem 0.8rem; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;
  }
  .ev-table tbody td { padding:0.9rem 0.8rem; vertical-align:middle; border-color:#F5F5F5 !important; font-size:0.9rem; }
  .ev-table tbody tr:hover { background:rgba(76,175,80,0.04); }

  .pill-sex {
    display:inline-block; padding:0.3rem 0.8rem; border-radius:20px;
    font-size:0.75rem; font-weight:800;
  }
  .pill-sex.M { background:#E3F2FD; color:#1565C0; }
  .pill-sex.F { background:#FCE4EC; color:#C2185B; }

  .pill-statut {
    display:inline-block; padding:0.3rem 0.8rem; border-radius:20px;
    font-size:0.75rem; font-weight:800;
  }
  .pill-statut.actif   { background:#E8F5E9; color:#2E7D32; }
  .pill-statut.archive { background:#E2E3E5; color:#41464B; }

  .btn-act {
    width:34px; height:34px; border-radius:50%; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    transition:all 0.2s; cursor:pointer; margin:0 2px; text-decoration:none;
  }
  .btn-act.edit    { background:#FFF3E0; color:#E65100; }
  .btn-act.archive { background:#E2E3E5; color:#41464B; }
  .btn-act.unarchive { background:#E8F5E9; color:#2E7D32; }
  .btn-act.del     { background:#FFEBEE; color:#C62828; }
  .btn-act:hover { transform:scale(1.1); }

  .breadcrumb-kider { background:transparent; padding:0; font-size:0.85rem; color:#999; font-weight:700; }
  .breadcrumb-kider a { color:#5B9BD5; text-decoration:none; }
  .breadcrumb-kider a:hover { text-decoration:underline; }

  .child-name { font-family:'Fredoka One',cursive; color:#333; }
</style>

<div class="container py-5" style="position:relative;z-index:1;">

  <?php if (!empty($msg)): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <div class="alert alert-success" style="border-radius:16px;border:none;text-align:center;">
        <?php
          $messages = ['ajoute'=>"Enfant inscrit avec succès.", 'modifie'=>"Fiche enfant mise à jour.", 'supprime'=>"Enfant supprimé.", 'archive'=>"Enfant archivé.", 'actif'=>"Enfant réactivé."];
          echo htmlspecialchars($messages[$msg] ?? '');
        ?>
      </div>
    </div></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
    <div>
      <nav class="breadcrumb-kider mb-1"><a href="/TinyTrack/dashboard">Dashboard</a> / Enfants</nav>
      <h2 class="section-title" style="margin:0;"><i class="fas fa-child"></i> Liste des enfants</h2>
      <div class="rainbow-divider mt-2"></div>
    </div>
    <a href="/TinyTrack/enfants/add" class="btn-ev-add">
      <i class="fas fa-user-plus"></i> Inscrire un enfant
    </a>
  </div>

  <!-- Stats -->
  <?php
    $actifs    = 0;
    $garcons   = 0;
    $filles    = 0;
    $archives  = 0;
    foreach ($enfants as $e) {
        if ($e['statut'] === 'actif') $actifs++;
        else $archives++;
        if ($e['sexe'] === 'M') $garcons++;
        else $filles++;
    }
  ?>
  <div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat mint">
        <div><div class="num"><?= (int)$totalEnfants ?></div><div class="lbl">Enfants actifs</div></div>
        <i class="fas fa-child ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat sky">
        <div><div class="num"><?= $garcons ?></div><div class="lbl">Garçons</div></div>
        <i class="fas fa-male ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat coral">
        <div><div class="num"><?= $filles ?></div><div class="lbl">Filles</div></div>
        <i class="fas fa-female ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat grape">
        <div><div class="num"><?= count($enfants) ?></div><div class="lbl">Résultats</div></div>
        <i class="fas fa-list ic"></i>
      </div>
    </div>
  </div>

  <!-- Card -->
  <div class="ev-card">
    <div class="row align-items-center mb-3 g-2">
      <div class="col-md-5">
        <form method="GET" action="/TinyTrack/enfants" class="ev-search-wrap">
          <i class="fas fa-search"></i>
          <input type="text" name="search" class="ev-search" placeholder="Rechercher un enfant..." value="<?= htmlspecialchars($search ?? '') ?>" id="searchInput">
        </form>
      </div>
      <div class="col-md-7 text-md-end"><span style="color:#999;font-weight:700;font-size:0.9rem;">Liste complète</span></div>
    </div>

    <div class="table-responsive">
      <table id="tableEnfants" class="table ev-table">
        <thead>
          <tr><th>#</th><th>Nom</th><th>Prénom</th><th>Date naissance</th><th>Sexe</th><th>Statut</th><th>Inscription</th><th class="text-center">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($enfants as $e): ?>
          <tr>
            <td><strong style="color:#999;">#<?= (int)$e['id'] ?></strong></td>
            <td><span class="child-name"><?= htmlspecialchars($e['nom']) ?></span></td>
            <td style="font-weight:700;color:#555;"><?= htmlspecialchars($e['prenom']) ?></td>
            <td style="color:#666;"><?= htmlspecialchars($e['date_naissance']) ?></td>
            <td>
              <span class="pill-sex <?= $e['sexe'] ?>">
                <?php if ($e['sexe'] === 'M'): ?>
                  <i class="fas fa-mars"></i> Garçon
                <?php else: ?>
                  <i class="fas fa-venus"></i> Fille
                <?php endif; ?>
              </span>
            </td>
            <td>
              <?php if ($e['statut'] === 'actif'): ?>
                <span class="pill-statut actif"><i class="fas fa-check-circle"></i> Actif</span>
              <?php else: ?>
                <span class="pill-statut archive"><i class="fas fa-archive"></i> Archivé</span>
              <?php endif; ?>
            </td>
            <td style="color:#999;font-size:0.85rem;"><?= htmlspecialchars($e['date_inscription']) ?></td>
            <td class="text-center" style="white-space:nowrap;">
              <a href="/TinyTrack/enfants/edit/<?= (int)$e['id'] ?>" class="btn-act edit" title="Modifier"><i class="fas fa-pen"></i></a>
              <?php if ($e['statut'] === 'actif'): ?>
                <form method="POST" action="/TinyTrack/enfants/archive/<?= (int)$e['id'] ?>" style="display:inline" onsubmit="return confirm('Archiver cet enfant ?');">
                  <button type="submit" class="btn-act archive" title="Archiver"><i class="fas fa-archive"></i></button>
                </form>
              <?php else: ?>
                <form method="POST" action="/TinyTrack/enfants/activate/<?= (int)$e['id'] ?>" style="display:inline">
                  <button type="submit" class="btn-act unarchive" title="Réactiver"><i class="fas fa-undo"></i></button>
                </form>
              <?php endif; ?>
              <form method="POST" action="/TinyTrack/enfants/delete/<?= (int)$e['id'] ?>" style="display:inline" onsubmit="return confirm('Supprimer définitivement ?');">
                <button type="submit" class="btn-act del" title="Supprimer"><i class="fas fa-trash"></i></button>
              </form>
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
  var table = $('#tableEnfants').DataTable({
    "language":{"emptyTable":"Aucun enfant trouvé","info":"_START_ à _END_ sur _TOTAL_ enfants","infoEmpty":"Aucun","lengthMenu":"Afficher _MENU_","zeroRecords":"Aucun résultat","paginate":{"next":"Suivant","previous":"Précédent"}},
    "pageLength":10, "order":[[0,"desc"]], "dom":"lrtip"
  });
  $('#searchInput').on('keyup', function(){ table.search(this.value).draw(); });
});
</script>

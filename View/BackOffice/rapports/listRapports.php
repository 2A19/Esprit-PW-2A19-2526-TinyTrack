<?php
// Vue passive — données injectées par RapportController::index()
// Variables : $rapports, $tri, $msg
$tri = $tri ?? 'desc';
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
  .ev-stat.sky    { background:linear-gradient(135deg,#E3F2FD,#FFFFFF); border:1px solid #BBDEFB; }
  .ev-stat.sky    .num, .ev-stat.sky    .ic { color:#1565C0; }
  .ev-stat.coral  { background:linear-gradient(135deg,#FCE4EC,#FFFFFF); border:1px solid #F8BBD0; }
  .ev-stat.coral  .num, .ev-stat.coral  .ic { color:#C2185B; }

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

  .btn-ev-sort {
    background:#fff; color:#5B9BD5 !important; border:2px solid #BBDEFB;
    border-radius:50px; padding:0.55rem 1.2rem; font-weight:800;
    text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem; transition:all 0.2s;
  }
  .btn-ev-sort:hover { background:#E3F2FD; color:#1565C0 !important; }

  .ev-table { font-family:'Nunito',sans-serif; }
  .ev-table thead th {
    background:linear-gradient(135deg,#FFF9F0,#FFFFFF) !important;
    color:#555 !important; font-weight:800; border:none !important;
    padding:1rem 0.8rem; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;
  }
  .ev-table tbody td { padding:0.9rem 0.8rem; vertical-align:middle; border-color:#F5F5F5 !important; font-size:0.9rem; }
  .ev-table tbody tr:hover { background:rgba(76,175,80,0.04); }

  .pill-act {
    display:inline-block; padding:0.3rem 0.8rem; border-radius:20px;
    font-size:0.75rem; font-weight:800; background:#E0F7FA; color:#00695C;
  }
  .pill-edu {
    display:inline-block; padding:0.2rem 0.7rem; border-radius:14px;
    font-size:0.78rem; font-weight:800; background:#F3E5F5; color:#6A1B9A;
  }

  .btn-act {
    width:34px; height:34px; border-radius:50%; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    transition:all 0.2s; cursor:pointer; margin:0 2px; text-decoration:none;
  }
  .btn-act.pdf  { background:#FFEBEE; color:#C62828; }
  .btn-act.edit { background:#FFF3E0; color:#E65100; }
  .btn-act.del  { background:#FCE4EC; color:#AD1457; }
  .btn-act:hover { transform:scale(1.1); }

  .breadcrumb-kider { background:transparent; padding:0; font-size:0.85rem; color:#999; font-weight:700; }
  .breadcrumb-kider a { color:#5B9BD5; text-decoration:none; }
  .breadcrumb-kider a:hover { text-decoration:underline; }

  .dropdown-menu { border-radius:18px !important; border:none !important; box-shadow:0 6px 24px rgba(0,0,0,0.1) !important; padding:0.5rem !important; }
  .dropdown-menu .dropdown-item { border-radius:12px; padding:0.5rem 0.8rem; font-weight:700; }
  .dropdown-menu .dropdown-item:hover { background:#E8F5E9; color:#2E7D32; }
</style>

<div class="container py-5" style="position:relative;z-index:1;">

  <?php if (isset($_GET['success'])): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <div class="alert alert-success" style="border-radius:16px;border:none;text-align:center;">
        <?php
          $msgs = ['add'=>'Rapport ajouté !', 'edit'=>'Rapport modifié !', 'delete'=>'Rapport supprimé !', 'deleted'=>'Rapport supprimé !'];
          echo htmlspecialchars($msgs[$_GET['success']] ?? '');
        ?>
      </div>
    </div></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
    <div>
      <nav class="breadcrumb-kider mb-1"><a href="/TinyTrack/dashboard">Dashboard</a> / Rapports</nav>
      <h2 class="section-title" style="margin:0;"><i class="fas fa-book"></i> Rapports Journaliers</h2>
      <div class="rainbow-divider mt-2"></div>
    </div>
    <a href="/TinyTrack/rapports/add" class="btn-ev-add">
      <i class="fas fa-plus-circle"></i> Ajouter un rapport
    </a>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat mint">
        <div><div class="num"><?= count($rapports) ?></div><div class="lbl">Total rapports</div></div>
        <i class="fas fa-book ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat sun">
        <?php
          $aujourdhui = 0;
          $today = date('Y-m-d');
          foreach ($rapports as $r) {
              if (strpos($r['date_rapport'] ?? '', $today) === 0) $aujourdhui++;
          }
        ?>
        <div><div class="num"><?= $aujourdhui ?></div><div class="lbl">Aujourd'hui</div></div>
        <i class="fas fa-calendar-day ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat sky">
        <?php
          $semaine = 0;
          $debutSemaine = date('Y-m-d', strtotime('monday this week'));
          foreach ($rapports as $r) {
              $d = substr($r['date_rapport'] ?? '', 0, 10);
              if ($d >= $debutSemaine) $semaine++;
          }
        ?>
        <div><div class="num"><?= $semaine ?></div><div class="lbl">Cette semaine</div></div>
        <i class="fas fa-calendar-week ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat coral">
        <?php
          $educateurs = array_unique(array_filter(array_column($rapports, 'id_educateur')));
        ?>
        <div><div class="num"><?= count($educateurs) ?></div><div class="lbl">Éducateurs actifs</div></div>
        <i class="fas fa-user-tie ic"></i>
      </div>
    </div>
  </div>

  <!-- Card -->
  <div class="ev-card">
    <div class="row align-items-center mb-3 g-2">
      <div class="col-md-5">
        <div class="ev-search-wrap">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" class="ev-search" placeholder="Rechercher un rapport...">
        </div>
      </div>
      <div class="col-md-4 text-md-center"><span style="color:#999;font-weight:700;font-size:0.9rem;">Liste complète</span></div>
      <div class="col-md-3 text-md-end">
        <div class="dropdown d-inline-block">
          <button type="button" class="btn-ev-sort dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-sort"></i> Trier
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a class="dropdown-item" href="/TinyTrack/rapports?tri=desc"><i class="fas fa-sort-amount-down"></i> Plus récent</a>
            <a class="dropdown-item" href="/TinyTrack/rapports?tri=asc"><i class="fas fa-sort-amount-up"></i> Plus ancien</a>
          </div>
        </div>
      </div>
    </div>

    <div class="table-responsive">
      <table id="tableRapports" class="table ev-table">
        <thead>
          <tr><th>#</th><th>Contenu</th><th>Activité</th><th>Date</th><th>Éducateur</th><th class="text-center">Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($rapports as $r): ?>
          <tr>
            <td><strong style="color:#999;">#<?= $r['id_rapport'] ?></strong></td>
            <td style="color:#666;max-width:320px;"><?= htmlspecialchars(substr($r['contenu_rapport'], 0, 90)) ?><?= strlen($r['contenu_rapport']) > 90 ? '…' : '' ?></td>
            <td><span class="pill-act"><?= htmlspecialchars($r['nom_activite'] ?? '—') ?></span></td>
            <td style="font-weight:700;color:#555;"><?= htmlspecialchars($r['date_rapport']) ?></td>
            <td><?= !empty($r['id_educateur']) ? '<span class="pill-edu"><i class="fas fa-user-tie"></i> #' . (int)$r['id_educateur'] . '</span>' : '<span style="color:#ccc;">—</span>' ?></td>
            <td class="text-center" style="white-space:nowrap;">
              <a href="/TinyTrack/rapports/pdf/<?= (int)$r['id_rapport'] ?>" class="btn-act pdf" target="_blank" title="Exporter PDF"><i class="fas fa-file-pdf"></i></a>
              <a href="/TinyTrack/rapports/edit/<?= (int)$r['id_rapport'] ?>" class="btn-act edit" title="Modifier"><i class="fas fa-pen"></i></a>
              <form method="POST" action="/TinyTrack/rapports/delete/<?= (int)$r['id_rapport'] ?>" style="display:inline" onsubmit="return confirm('Supprimer ce rapport ?')">
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
  var table = $('#tableRapports').DataTable({
    "language":{"emptyTable":"Aucun rapport","info":"_START_ à _END_ sur _TOTAL_","lengthMenu":"Afficher _MENU_","zeroRecords":"Aucun résultat","paginate":{"next":"Suivant","previous":"Précédent"}},
    "pageLength":10, "order":[], "dom":"lrtip"
  });
  $('#searchInput').on('keyup', function(){ table.search(this.value).draw(); });
});
</script>

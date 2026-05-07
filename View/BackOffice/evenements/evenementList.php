<?php
// Vue passive — données injectées par EvenementController::index()
// Variables : $evenements, $flash, $total, $planifies, $en_cours, $termines
include __DIR__ . '/../../FrontOffice/template/header.php';
?>

<!-- DataTables CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/css/dataTables.bootstrap5.min.css">

<style>
  .ev-stat {
    background:#fff; border-radius:24px; padding:1.5rem 1.8rem;
    box-shadow:0 4px 20px rgba(0,0,0,0.06);
    transition:all 0.3s; position:relative; overflow:hidden;
    display:flex; align-items:center; justify-content:space-between;
  }
  .ev-stat:hover { transform:translateY(-4px); box-shadow:0 10px 30px rgba(0,0,0,0.1); }
  .ev-stat .num { font-family:'Fredoka One',cursive; font-size:2.4rem; line-height:1; }
  .ev-stat .lbl { color:#666; font-weight:700; font-size:0.9rem; margin-top:0.3rem; }
  .ev-stat .ic { font-size:2.2rem; opacity:0.25; }
  .ev-stat.mint    { background:linear-gradient(135deg,#E8F5E9,#FFFFFF); border:1px solid #C8E6C9; }
  .ev-stat.mint    .num, .ev-stat.mint    .ic { color:#2E7D32; }
  .ev-stat.sun     { background:linear-gradient(135deg,#FFF8E1,#FFFFFF); border:1px solid #FFE082; }
  .ev-stat.sun     .num, .ev-stat.sun     .ic { color:#E65100; }
  .ev-stat.sky     { background:linear-gradient(135deg,#E3F2FD,#FFFFFF); border:1px solid #BBDEFB; }
  .ev-stat.sky     .num, .ev-stat.sky     .ic { color:#1565C0; }
  .ev-stat.coral   { background:linear-gradient(135deg,#FCE4EC,#FFFFFF); border:1px solid #F8BBD0; }
  .ev-stat.coral   .num, .ev-stat.coral   .ic { color:#C2185B; }

  .ev-card {
    background:#fff; border-radius:24px; padding:1.5rem;
    box-shadow:0 4px 20px rgba(0,0,0,0.06);
    border:none; position:relative; overflow:hidden;
  }
  .ev-card::before {
    content:''; position:absolute; top:0; left:0; right:0; height:4px;
    background:linear-gradient(90deg,#4CAF50,#5B9BD5,#FFD93D,#FF8FAB,#FFA726);
    border-radius:24px 24px 0 0;
  }

  .ev-search {
    border-radius:50px; border:2px solid #E8E8E8;
    padding:0.6rem 1.2rem 0.6rem 2.5rem;
    width:100%; font-family:'Nunito',sans-serif; font-weight:600;
    transition:all 0.2s;
  }
  .ev-search:focus { border-color:#4CAF50; outline:none; box-shadow:0 0 0 4px rgba(76,175,80,0.1); }
  .ev-search-wrap { position:relative; }
  .ev-search-wrap i { position:absolute; left:0.9rem; top:50%; transform:translateY(-50%); color:#999; }

  .btn-ev-add {
    background:linear-gradient(135deg,#4CAF50,#66BB6A); color:#fff !important;
    border:none; border-radius:50px; padding:0.65rem 1.6rem;
    font-weight:800; font-family:'Nunito',sans-serif;
    box-shadow:0 4px 14px rgba(76,175,80,0.3); transition:all 0.2s;
    text-decoration:none; display:inline-flex; align-items:center; gap:0.5rem;
  }
  .btn-ev-add:hover { transform:translateY(-2px); box-shadow:0 6px 20px rgba(76,175,80,0.4); color:#fff !important; }

  /* Pastel badges */
  .pill-type {
    display:inline-block; padding:0.3rem 0.8rem; border-radius:20px;
    font-size:0.75rem; font-weight:800; text-transform:capitalize;
  }
  .pill-type.formation  { background:#E0F7FA; color:#00695C; }
  .pill-type.concert    { background:#FCE4EC; color:#C2185B; }
  .pill-type.sport      { background:#E8F5E9; color:#2E7D32; }
  .pill-type.atelier    { background:#FFF3E0; color:#E65100; }
  .pill-type.festival   { background:#F3E5F5; color:#6A1B9A; }
  .pill-type.conference { background:#E3F2FD; color:#1565C0; }
  .pill-type.exposition { background:#FFF8E1; color:#F57F17; }
  .pill-type.autre      { background:#F5F5F5; color:#616161; }

  .pill-statut {
    display:inline-block; padding:0.3rem 0.8rem; border-radius:20px;
    font-size:0.75rem; font-weight:800;
  }
  .pill-statut.planifie { background:#FFF3CD; color:#856404; }
  .pill-statut.en_cours { background:#D1E7DD; color:#0F5132; }
  .pill-statut.termine  { background:#F8D7DA; color:#842029; }
  .pill-statut.annule   { background:#E2E3E5; color:#41464B; }
  .pill-statut.complet  { background:#FDE2E4; color:#9F1239; }

  .pill-cap {
    display:inline-block; padding:0.3rem 0.7rem; border-radius:14px;
    font-size:0.78rem; font-weight:800;
  }
  .pill-cap.ok    { background:#E8F5E9; color:#2E7D32; }
  .pill-cap.warn  { background:#FFF3E0; color:#E65100; }
  .pill-cap.full  { background:#FFEBEE; color:#C62828; }

  .ev-table { font-family:'Nunito',sans-serif; }
  .ev-table thead th {
    background:linear-gradient(135deg,#FFF9F0,#FFFFFF) !important;
    color:#555 !important; font-weight:800; border:none !important;
    padding:1rem 0.8rem; font-size:0.85rem; text-transform:uppercase; letter-spacing:0.05em;
  }
  .ev-table tbody td {
    padding:0.9rem 0.8rem; vertical-align:middle; border-color:#F5F5F5 !important;
    font-size:0.9rem;
  }
  .ev-table tbody tr:hover { background:rgba(76,175,80,0.04); }
  .ev-table .price-free { color:#2E7D32; font-weight:800; }
  .ev-table .price-paid { color:#333; font-weight:800; }

  .btn-act {
    width:34px; height:34px; border-radius:50%; border:none;
    display:inline-flex; align-items:center; justify-content:center;
    transition:all 0.2s; cursor:pointer; margin:0 2px;
  }
  .btn-act.view { background:#E3F2FD; color:#1565C0; }
  .btn-act.edit { background:#FFF3E0; color:#E65100; }
  .btn-act.del  { background:#FFEBEE; color:#C62828; }
  .btn-act:hover { transform:scale(1.1); }

  .breadcrumb-kider {
    background:transparent; padding:0; font-size:0.85rem; color:#999; font-weight:700;
  }
  .breadcrumb-kider a { color:#5B9BD5; text-decoration:none; }
  .breadcrumb-kider a:hover { text-decoration:underline; }
</style>

<div class="container py-5" style="position:relative;z-index:1;">

  <?php if ($flash): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <div class="alert alert-<?= $flash['type'] ?>" style="border-radius:16px;border:none;text-align:center;">
        <?= htmlspecialchars($flash['message']) ?>
      </div>
    </div></div>
  <?php endif; ?>

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-end mb-4 flex-wrap gap-3">
    <div>
      <nav class="breadcrumb-kider mb-1"><a href="/TinyTrack/dashboard">Dashboard</a> / Événements</nav>
      <h2 class="section-title" style="margin:0;"><i class="fas fa-calendar-alt"></i> Événements</h2>
      <div class="rainbow-divider mt-2"></div>
    </div>
    <a href="/TinyTrack/evenements/add" class="btn-ev-add">
      <i class="fas fa-calendar-plus"></i> Ajouter un événement
    </a>
  </div>

  <!-- Stats -->
  <div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat mint">
        <div><div class="num"><?= $total ?></div><div class="lbl">Total</div></div>
        <i class="fas fa-calendar ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat sun">
        <div><div class="num"><?= $planifies ?></div><div class="lbl">Planifiés</div></div>
        <i class="fas fa-clock ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat sky">
        <div><div class="num"><?= $en_cours ?></div><div class="lbl">En cours</div></div>
        <i class="fas fa-play-circle ic"></i>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="ev-stat coral">
        <div><div class="num"><?= $termines ?></div><div class="lbl">Terminés</div></div>
        <i class="fas fa-check-circle ic"></i>
      </div>
    </div>
  </div>

  <!-- Search + Table card -->
  <div class="ev-card">

    <div class="row align-items-center mb-3">
      <div class="col-md-5">
        <div class="ev-search-wrap">
          <i class="fas fa-search"></i>
          <input type="text" id="searchInput" class="ev-search" placeholder="Rechercher un événement...">
        </div>
      </div>
      <div class="col-md-7 text-md-end mt-2 mt-md-0">
        <span style="color:#999;font-weight:700;font-size:0.9rem;">Liste complète</span>
      </div>
    </div>

    <div class="table-responsive">
      <table id="tableEvents" class="table ev-table">
        <thead>
          <tr>
            <th>#</th><th>Titre</th><th>Date</th><th>Horaire</th><th>Type</th>
            <th>Lieu</th><th>Capacité</th><th>Prix</th><th>Statut</th><th class="text-center">Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($evenements as $ev):
            $nbRes = (int)($ev['nb_reservations'] ?? 0);
            $capMax = (int)$ev['capacite_max'];
            $pct = $capMax > 0 ? min(100, round($nbRes / $capMax * 100)) : 0;
            $capCls = $pct >= 100 ? 'full' : ($pct >= 75 ? 'warn' : 'ok');
            $statutLabels = ['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé','complet'=>'Complet'];
          ?>
            <tr>
              <td><strong style="color:#999;">#<?= $ev['id'] ?></strong></td>
              <td><strong style="font-family:'Fredoka One',cursive;color:#333;"><?= htmlspecialchars($ev['titre']) ?></strong></td>
              <td><?= date('d/m/Y', strtotime($ev['date'])) ?></td>
              <td><?= substr($ev['heure_debut'],0,5) ?> — <?= substr($ev['heure_fin'],0,5) ?></td>
              <td><span class="pill-type <?= $ev['type'] ?>"><?= htmlspecialchars($ev['type']) ?></span></td>
              <td style="color:#666;"><?= htmlspecialchars($ev['lieu']) ?></td>
              <td><span class="pill-cap <?= $capCls ?>"><?= $nbRes ?> / <?= $capMax ?></span></td>
              <td>
                <?php if ($ev['prix'] > 0): ?>
                  <span class="price-paid"><?= number_format($ev['prix'],2) ?> TND</span>
                <?php else: ?>
                  <span class="price-free">Gratuit</span>
                <?php endif; ?>
              </td>
              <td><span class="pill-statut <?= $ev['statut'] ?>"><?= $statutLabels[$ev['statut']] ?? $ev['statut'] ?></span></td>
              <td class="text-center" style="white-space:nowrap;">
                <a href="/TinyTrack/evenements/<?= (int)$ev['id'] ?>/reservations" class="btn-act view" title="Voir réservations"><i class="fas fa-ticket-alt"></i></a>
                <a href="/TinyTrack/evenements/edit/<?= (int)$ev['id'] ?>" class="btn-act edit" title="Modifier"><i class="fas fa-pen"></i></a>
                <form method="POST" action="/TinyTrack/evenements/delete/<?= (int)$ev['id'] ?>" style="display:inline" onsubmit="return confirm('Supprimer cet événement ?')">
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

<!-- jQuery + DataTables (BS5) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net-bs5@1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function(){
  var table = $('#tableEvents').DataTable({
    "language":{
      "emptyTable":"Aucun événement",
      "info":"_START_ à _END_ sur _TOTAL_",
      "infoEmpty":"Aucun",
      "lengthMenu":"Afficher _MENU_",
      "zeroRecords":"Aucun résultat",
      "paginate":{"next":"Suivant","previous":"Précédent"}
    },
    "pageLength":10,
    "order":[[0,"desc"]],
    "dom":"lrtip"
  });
  $('#searchInput').on('keyup', function(){ table.search(this.value).draw(); });
});
</script>

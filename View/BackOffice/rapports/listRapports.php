<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once __DIR__ . '/../../../Controller/RapportController.php';

$controller = new RapportController();

$tri = isset($_GET['tri']) ? $_GET['tri'] : 'desc';

if ($tri !== 'asc' && $tri !== 'desc') {
    $tri = 'desc';
}

$rapports = $controller->listRapportsWithActivite($tri)->fetchAll();

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<style>
/* Fix dropdown display cleanly */
.dropdown-menu {
    z-index: 9999 !important;
}

.dropdown-menu .dropdown-item {
    color: #212529 !important;
    background-color: #ffffff !important;
}

.dropdown-menu .dropdown-item:hover {
    background-color: #28a745 !important;
    color: #ffffff !important;
}
</style>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-book text-success"></i> Rapports Journaliers</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            <li class="breadcrumb-item active">Rapports</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <?php
          if ($_GET['success'] == 'add') echo "Rapport ajouté !";
          elseif ($_GET['success'] == 'edit') echo "Rapport modifié !";
          elseif ($_GET['success'] == 'delete') echo "Rapport supprimé !";
          ?>
        </div>
      <?php endif; ?>

      <div class="row mb-3">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= count($rapports) ?></h3>
              <p>Rapports</p>
            </div>
            <div class="icon"><i class="fas fa-book"></i></div>
          </div>
        </div>
      </div>

      <div class="card card-success">
        <div class="card-header">
          <div class="row align-items-center">

            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher..." style="border-radius:10px 0 0 10px;">
                <div class="input-group-append">
                  <button class="btn btn-success" id="searchBtn" style="border-radius:0 10px 10px 0;">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="col-md-4 text-center">
              <h3 class="card-title mb-0">Liste complète</h3>
            </div>

            <div class="col-md-4 text-right">

              <div class="btn-group">
                <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
                  <i class="fas fa-sort"></i> Trier par
                </button>

                <div class="dropdown-menu dropdown-menu-right">
                  <a class="dropdown-item" href="listRapports.php?tri=desc">
                    <i class="fas fa-sort-amount-down"></i> Plus récent
                  </a>

                  <a class="dropdown-item" href="listRapports.php?tri=asc">
                    <i class="fas fa-sort-amount-up"></i> Plus ancien
                  </a>
                </div>
              </div>

              <a href="/TinyTrack/View/FrontOffice/rapports/addRapport.php" class="btn btn-success ml-2">
                <i class="fas fa-plus"></i> Ajouter rapport
              </a>

            </div>
          </div>
        </div>

        <div class="card-body">
          <table id="tableRapports" class="table table-bordered table-hover table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Contenu</th>
                <th>Activité</th>
                <th>ID Enfant</th>
                <th>Date</th>
                <th>Éducateur ID</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($rapports as $r): ?>
              <tr>
                <td><?= $r['id_rapport'] ?></td>
                <td><?= htmlspecialchars(substr($r['contenu_rapport'], 0, 80)) ?><?= strlen($r['contenu_rapport']) > 80 ? '...' : '' ?></td>
                <td><span class="badge bg-info"><?= htmlspecialchars($r['nom_activite'] ?? '—') ?></span></td>
                <td><?= $r['id_enfant'] ?? '—' ?></td>
                <td><?= $r['date_rapport'] ?></td>
                <td><?= $r['id_educateur'] ?? '—' ?></td>
                <td>

                  <!-- ✅ AI BUTTON ADDED -->
                  <a href="analyseRapportIA.php?id=<?= $r['id_rapport'] ?>" class="btn btn-sm btn-info">
                    <i class="fas fa-brain"></i>
                  </a>

                  <a href="editRapport.php?id=<?= $r['id_rapport'] ?>" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                  </a>

                  <a href="deleteRapport.php?id=<?= $r['id_rapport'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')">
                    <i class="fas fa-trash"></i>
                  </a>

                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>

          </table>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

<script>
$(document).ready(function(){
  var table=$('#tableRapports').DataTable({
    "language":{
      "emptyTable":"Aucun rapport",
      "info":"_START_ à _END_ sur _TOTAL_",
      "lengthMenu":"Afficher _MENU_",
      "zeroRecords":"Aucun résultat",
      "paginate":{"next":"Suivant","previous":"Précédent"}
    },
    "pageLength":10,
    "order":[],
    "dom":"lrtip"
  });

  $('#searchInput').on('keyup',function(){
    table.search(this.value).draw();
  });

  $('#searchBtn').on('click',function(){
    table.search($('#searchInput').val()).draw();
  });
});
</script>

<?php
require_once __DIR__ . '/../../../Controller/EnfantController.php';

$controller = new EnfantController();

// Search or list all
if (!empty($_GET['search'])) {
    $enfants = $controller->rechercherEnfants($_GET['search']);
} else {
    $enfants = $controller->listerEnfants();
}

$totalEnfants = $controller->compterEnfants();

include '../template/header.php';
include '../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-child text-success"></i> Liste des enfants</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            <li class="breadcrumb-item active">Enfants</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- Stats cards -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $totalEnfants ?></h3>
              <p>Enfants actifs</p>
            </div>
            <div class="icon"><i class="fas fa-child"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= count($enfants) ?></h3>
              <p>Résultats affichés</p>
            </div>
            <div class="icon"><i class="fas fa-list"></i></div>
          </div>
        </div>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un enfant..." style="border-radius:10px 0 0 10px;">
                <div class="input-group-append">
                  <button class="btn btn-success" type="button" id="searchBtn" style="border-radius:0 10px 10px 0;"><i class="fas fa-search"></i> Rechercher</button>
                </div>
              </div>
            </div>
            <div class="col-md-4 text-center">
              <h3 class="card-title mb-0" style="font-family:'Fredoka One',cursive;">Liste complète</h3>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table id="tableEnfants" class="table table-bordered table-hover table-striped">
            <thead class="thead-dark">
              <tr>
                <th>#</th>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Date naissance</th>
                <th>Sexe</th>
                <th>Statut</th>
                <th>Date inscription</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($enfants as $e): ?>
              <tr>
                <td><?= $e['id'] ?></td>
                <td><?= htmlspecialchars($e['nom']) ?></td>
                <td><?= htmlspecialchars($e['prenom']) ?></td>
                <td><?= $e['date_naissance'] ?></td>
                <td>
                  <?php if ($e['sexe'] === 'M'): ?>
                    <span class="badge badge-primary">Garçon</span>
                  <?php else: ?>
                    <span class="badge badge-danger">Fille</span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($e['statut'] === 'actif'): ?>
                    <span class="badge badge-success">Actif</span>
                  <?php else: ?>
                    <span class="badge badge-secondary">Archivé</span>
                  <?php endif; ?>
                </td>
                <td><?= $e['date_inscription'] ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include '../template/footer.php'; ?>

<script>
$(document).ready(function() {
  var table = $('#tableEnfants').DataTable({
    "language": {
      "emptyTable": "Aucun enfant trouvé",
      "info": "Affichage de _START_ à _END_ sur _TOTAL_ enfants",
      "infoEmpty": "Aucun enfant",
      "infoFiltered": "(filtré sur _MAX_ au total)",
      "lengthMenu": "Afficher _MENU_ enfants",
      "search": "Rechercher :",
      "zeroRecords": "Aucun résultat",
      "paginate": { "first": "Premier", "last": "Dernier", "next": "Suivant", "previous": "Précédent" }
    },
    "pageLength": 10,
    "order": [[0, "desc"]],
    "dom": 'lrtip'
  });

  $('#searchInput').on('keyup', function() {
    table.search(this.value).draw();
  });

  $('#searchBtn').on('click', function() {
    table.search($('#searchInput').val()).draw();
  });
});
</script>

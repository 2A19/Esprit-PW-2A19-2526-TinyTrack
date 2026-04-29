<?php
// Interface de démonstration du module Réclamations

$reclamations = [
    ['id'=>5,'nom'=>'Ali Ben Salah','email'=>'ali@gmail.com','sujet'=>'Problème paiement','statut'=>'En attente','date'=>'2026-04-12'],
    ['id'=>4,'nom'=>'Sara Trabelsi','email'=>'sara@gmail.com','sujet'=>'Erreur inscription','statut'=>'Traité','date'=>'2026-04-11'],
    ['id'=>3,'nom'=>'Mehdi K','email'=>'mehdi@gmail.com','sujet'=>'Bug plateforme','statut'=>'En attente','date'=>'2026-04-10'],
    ['id'=>2,'nom'=>'Yasmine A','email'=>'yas@gmail.com','sujet'=>'Demande info','statut'=>'Traité','date'=>'2026-04-09'],
    ['id'=>1,'nom'=>'Omar F','email'=>'omar@gmail.com','sujet'=>'Connexion impossible','statut'=>'En attente','date'=>'2026-04-08']
];

$total = count($reclamations);
$pending = count(array_filter($reclamations, function($r){ return $r['statut']=='En attente'; }));
$solved = count(array_filter($reclamations, function($r){ return $r['statut']=='Traité'; }));

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-exclamation-circle text-danger"></i> Réclamations
          </h1>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- STATS -->
      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?= $total ?></h3>
              <p>Total</p>
            </div>
            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?= $pending ?></h3>
              <p>En attente</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
          </div>
        </div>

        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= $solved ?></h3>
              <p>Traitées</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
          </div>
        </div>
      </div>

      <!-- TABLE -->
      <div class="card card-success">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
                <div class="input-group-append">
                  <button class="btn btn-success" id="searchBtn">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="col-md-4 text-center">
              <h3 class="card-title mb-0">Liste des réclamations</h3>
            </div>

            <div class="col-md-4 text-right"></div>
          </div>
        </div>

        <div class="card-body">
          <table id="tableRec" class="table table-bordered table-hover table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Client</th>
                <th>Email</th>
                <th>Sujet</th>
                <th>Statut</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($reclamations as $rec): ?>
              <tr>
                <td><?= $rec['id'] ?></td>
                <td><strong><?= $rec['nom'] ?></strong></td>
                <td><?= $rec['email'] ?></td>
                <td><?= $rec['sujet'] ?></td>

                <td>
                  <?php if ($rec['statut'] == 'En attente'): ?>
                    <span class="badge bg-warning text-dark">En attente</span>
                  <?php else: ?>
                    <span class="badge bg-success">Traité</span>
                  <?php endif; ?>
                </td>

                <td><?= $rec['date'] ?></td>

                <td>
                  <a href="#" class="btn btn-sm btn-info">
                    <i class="fas fa-reply"></i>
                  </a>

                  <a href="#" class="btn btn-sm btn-warning">
                    <i class="fas fa-edit"></i>
                  </a>

                  <a href="#" class="btn btn-sm btn-danger">
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

<?php include 'template/footer.php'; ?>

<script>
$(document).ready(function(){
  var table=$('#tableRec').DataTable({
    "language":{
      "emptyTable":"Aucune réclamation",
      "info":"_START_ à _END_ sur _TOTAL_",
      "lengthMenu":"Afficher _MENU_",
      "zeroRecords":"Aucun résultat",
      "paginate":{"next":"Suivant","previous":"Précédent"}
    },
    "pageLength":10,
    "order":[[0,"desc"]],
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
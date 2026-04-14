<?php
session_start();
require_once(__DIR__ . '/../../controller/evenementController.php');
$controller = new EvenementController();
$evenements = $controller->afficher()->fetchAll();

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$total = count($evenements);
$planifies = count(array_filter($evenements, fn($e) => $e['statut'] === 'planifie'));
$en_cours = count(array_filter($evenements, fn($e) => $e['statut'] === 'en_cours'));
$termines = count(array_filter($evenements, fn($e) => $e['statut'] === 'termine'));

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-calendar-alt text-success"></i> Événements</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="index.php">Dashboard</a></li><li class="breadcrumb-item active">Événements</li></ol></div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php if ($flash): ?>
        <div class="alert alert-<?= $flash['type'] ?> alert-dismissible fade show">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <?= htmlspecialchars($flash['message']) ?>
        </div>
      <?php endif; ?>

      <div class="row">
        <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?= $total ?></h3><p>Total</p></div><div class="icon"><i class="fas fa-calendar"></i></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?= $planifies ?></h3><p>Planifiés</p></div><div class="icon"><i class="fas fa-clock"></i></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?= $en_cours ?></h3><p>En cours</p></div><div class="icon"><i class="fas fa-play-circle"></i></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?= $termines ?></h3><p>Terminés</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
      </div>

      <div class="card card-success">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher..." style="border-radius:10px 0 0 10px;">
                <div class="input-group-append"><button class="btn btn-success" id="searchBtn" style="border-radius:0 10px 10px 0;"><i class="fas fa-search"></i></button></div>
              </div>
            </div>
            <div class="col-md-4 text-center"><h3 class="card-title mb-0">Liste complète</h3></div>
            <div class="col-md-4 text-right"><a href="ajouterevenement.php" class="btn btn-success"><i class="fas fa-calendar-plus"></i> Ajouter</a></div>
          </div>
        </div>
        <div class="card-body">
          <table id="tableEvents" class="table table-bordered table-hover table-striped">
            <thead>
              <tr><th>#</th><th>Titre</th><th>Date</th><th>Horaire</th><th>Type</th><th>Lieu</th><th>Capacité</th><th>Prix</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach ($evenements as $ev): ?>
              <tr>
                <td><?= $ev['id'] ?></td>
                <td><strong><?= htmlspecialchars($ev['titre']) ?></strong></td>
                <td><?= $ev['date'] ?></td>
                <td><?= substr($ev['heure_debut'],0,5) ?> — <?= substr($ev['heure_fin'],0,5) ?></td>
                <td><span class="badge bg-primary"><?= htmlspecialchars($ev['type']) ?></span></td>
                <td><?= htmlspecialchars($ev['lieu']) ?></td>
                <td><?= $ev['capacite_max'] ?></td>
                <td><?= $ev['prix'] > 0 ? number_format($ev['prix'],2).' TND' : '<span class="text-success fw-bold">Gratuit</span>' ?></td>
                <td>
                  <?php $sc=['planifie'=>'warning','en_cours'=>'info','termine'=>'danger','annule'=>'secondary']; $sl=['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé']; ?>
                  <span class="badge bg-<?= $sc[$ev['statut']] ?? 'secondary' ?>"><?= $sl[$ev['statut']] ?? $ev['statut'] ?></span>
                </td>
                <td>
                  <a href="editEvenement.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                  <a href="deleteEvenement.php?id=<?= $ev['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
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
  var table=$('#tableEvents').DataTable({
    "language":{"emptyTable":"Aucun événement","info":"_START_ à _END_ sur _TOTAL_","infoEmpty":"Aucun","lengthMenu":"Afficher _MENU_","zeroRecords":"Aucun résultat","paginate":{"next":"Suivant","previous":"Précédent"}},
    "pageLength":10,"order":[[0,"desc"]],"dom":"lrtip"
  });
  $('#searchInput').on('keyup',function(){table.search(this.value).draw();});
  $('#searchBtn').on('click',function(){table.search($('#searchInput').val()).draw();});
});
</script>

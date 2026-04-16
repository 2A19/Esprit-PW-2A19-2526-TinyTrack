<?php
session_start();
require_once(__DIR__ . '/../../../Controller/reservationController.php');
require_once(__DIR__ . '/../../../Controller/evenementController.php');

$resCtrl = new ReservationController();

if (isset($_GET['action']) && $_GET['action'] === 'supprimer' && isset($_GET['id'])) {
    $resCtrl->supprimer($_GET['id']);
    $_SESSION['flash'] = ['type' => 'success', 'message' => 'Réservation supprimée.'];
    header('Location: list.php');
    exit;
}

$reservations = $resCtrl->afficher();
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-ticket-alt text-info"></i> Réservations</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="../index.php">Dashboard</a></li><li class="breadcrumb-item active">Réservations</li></ol></div>
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

      <div class="card card-info">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchRes" class="form-control" placeholder="Rechercher..." style="border-radius:10px 0 0 10px;">
                <div class="input-group-append"><button class="btn btn-info" id="searchResBtn" style="border-radius:0 10px 10px 0;"><i class="fas fa-search"></i></button></div>
              </div>
            </div>
            <div class="col-md-4 text-center"><h3 class="card-title mb-0">Liste des réservations</h3></div>
            <div class="col-md-4 text-right"><a href="add.php" class="btn btn-success"><i class="fas fa-plus-circle"></i> Ajouter</a></div>
          </div>
        </div>
        <div class="card-body">
          <table id="tableRes" class="table table-bordered table-hover table-striped">
            <thead>
              <tr><th>#</th><th>Événement</th><th>Enfant ID</th><th>Parent ID</th><th>Accompagnants</th><th>Date</th><th>Statut</th><th>Paiement</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php if (empty($reservations)): ?>
                <tr><td colspan="9" class="text-center text-muted">Aucune réservation</td></tr>
              <?php else: ?>
                <?php foreach ($reservations as $r): ?>
                <tr>
                  <td><?= $r['id'] ?></td>
                  <td><strong><?= htmlspecialchars($r['evenement_titre'] ?? '—') ?></strong></td>
                  <td><?= $r['enfant_id'] ?? '—' ?></td>
                  <td><?= $r['parent_id'] ?? '—' ?></td>
                  <td><?= $r['nb_accompagnants'] ?></td>
                  <td><?= date('d/m/Y H:i', strtotime($r['date_reservation'])) ?></td>
                  <td>
                    <?php if ($r['statut']==='confirmee'): ?><span class="badge bg-success">Confirmée</span>
                    <?php elseif ($r['statut']==='en_attente'): ?><span class="badge bg-warning">En attente</span>
                    <?php else: ?><span class="badge bg-danger">Annulée</span><?php endif; ?>
                  </td>
                  <td>
                    <?php if ($r['paiement']==='paye'): ?><span class="badge bg-success">Payé</span>
                    <?php else: ?><span class="badge bg-secondary">Non payé</span><?php endif; ?>
                  </td>
                  <td>
                    <a href="edit.php?id=<?= $r['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <a href="list.php?action=supprimer&id=<?= $r['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer ?')"><i class="fas fa-trash"></i></a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
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
  var table=$('#tableRes').DataTable({
    "language":{"emptyTable":"Aucune réservation","info":"_START_ à _END_ sur _TOTAL_","lengthMenu":"Afficher _MENU_","zeroRecords":"Aucun résultat","paginate":{"next":"Suivant","previous":"Précédent"}},
    "pageLength":10,"order":[[0,"desc"]],"dom":"lrtip"
  });
  $('#searchRes').on('keyup',function(){table.search(this.value).draw();});
  $('#searchResBtn').on('click',function(){table.search($('#searchRes').val()).draw();});
});
</script>

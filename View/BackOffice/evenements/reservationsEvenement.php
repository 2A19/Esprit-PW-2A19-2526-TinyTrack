<?php
// Vue passive — données injectées par EvenementController::reservations($id)
// Variables : $event, $reservations, $total, $confirmees, $enAttente, $annulees, $payees, $actives, $restantes, $capaciteMax
$pct = $capaciteMax > 0 ? min(100, round($actives / $capaciteMax * 100)) : 0;
$barColor = $pct >= 100 ? '#dc3545' : ($pct >= 75 ? '#ffc107' : '#28a745');
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-ticket-alt text-info"></i> Réservations — <?= htmlspecialchars($event['titre']) ?></h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="/TinyTrack/dashboard">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/TinyTrack/evenements">Événements</a></li>
            <li class="breadcrumb-item active">Réservations</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- Infos événement -->
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-calendar-alt"></i> <?= htmlspecialchars($event['titre']) ?></h3>
          <div class="card-tools">
            <a href="/TinyTrack/evenements" class="btn btn-sm btn-default"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
            <a href="/TinyTrack/evenements/edit/<?= (int)$event['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i> Modifier</a>
          </div>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-3"><i class="fas fa-calendar text-info"></i> <strong>Date :</strong> <?= htmlspecialchars($event['date']) ?></div>
            <div class="col-md-3"><i class="fas fa-clock text-warning"></i> <strong>Horaire :</strong> <?= substr($event['heure_debut'],0,5) ?> — <?= substr($event['heure_fin'],0,5) ?></div>
            <div class="col-md-3"><i class="fas fa-map-marker-alt text-danger"></i> <strong>Lieu :</strong> <?= htmlspecialchars($event['lieu']) ?></div>
            <div class="col-md-3"><i class="fas fa-tag text-success"></i> <strong>Prix :</strong> <?= $event['prix'] > 0 ? number_format($event['prix'],2).' TND' : 'Gratuit' ?></div>
          </div>
        </div>
      </div>

      <!-- Stats réservations -->
      <div class="row">
        <div class="col-lg-3 col-6"><div class="small-box bg-info"><div class="inner"><h3><?= $total ?></h3><p>Total réservations</p></div><div class="icon"><i class="fas fa-ticket-alt"></i></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-success"><div class="inner"><h3><?= $confirmees ?></h3><p>Confirmées</p></div><div class="icon"><i class="fas fa-check-circle"></i></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-warning"><div class="inner"><h3><?= $enAttente ?></h3><p>En attente</p></div><div class="icon"><i class="fas fa-hourglass-half"></i></div></div></div>
        <div class="col-lg-3 col-6"><div class="small-box bg-danger"><div class="inner"><h3><?= $annulees ?></h3><p>Annulées</p></div><div class="icon"><i class="fas fa-times-circle"></i></div></div></div>
      </div>

      <!-- Capacité -->
      <div class="card">
        <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-users text-purple"></i> Capacité</h3></div>
        <div class="card-body">
          <div class="d-flex justify-content-between mb-2" style="font-size:0.95rem;">
            <span><strong><?= $actives ?></strong> / <?= $capaciteMax ?> places réservées (hors annulations)</span>
            <span class="text-muted"><strong><?= $restantes ?></strong> place<?= $restantes > 1 ? 's' : '' ?> restante<?= $restantes > 1 ? 's' : '' ?> · <?= $payees ?> payée<?= $payees > 1 ? 's' : '' ?></span>
          </div>
          <div class="progress" style="height:14px;border-radius:7px;">
            <div class="progress-bar" role="progressbar" style="width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:7px;"><?= $pct ?>%</div>
          </div>
        </div>
      </div>

      <!-- Liste réservations -->
      <div class="card card-info">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-list"></i> Liste des réservations</h3></div>
        <div class="card-body">
          <?php if (empty($reservations)): ?>
            <div class="text-center py-4 text-muted">
              <i class="fas fa-ticket-alt fa-3x mb-2" style="color:#ddd;"></i>
              <p>Aucune réservation pour cet événement.</p>
            </div>
          <?php else: ?>
            <table id="tableRes" class="table table-bordered table-hover table-striped">
              <thead>
                <tr><th>#</th><th>Enfant ID</th><th>Parent ID</th><th>Accomp.</th><th>Date réservation</th><th>Commentaire</th><th>Statut</th><th>Paiement</th><th>Actions</th></tr>
              </thead>
              <tbody>
                <?php foreach ($reservations as $r):
                  $rsc = ['confirmee'=>'success','en_attente'=>'warning','annulee'=>'danger'];
                  $rsl = ['confirmee'=>'Confirmée','en_attente'=>'En attente','annulee'=>'Annulée'];
                  $pc  = ['paye'=>'success','non_paye'=>'secondary'];
                  $pl  = ['paye'=>'Payé','non_paye'=>'Non payé'];
                ?>
                  <tr>
                    <td><?= (int)$r['id'] ?></td>
                    <td><?= $r['enfant_id'] ?? '—' ?></td>
                    <td><?= $r['parent_id'] ?? '—' ?></td>
                    <td><?= (int)$r['nb_accompagnants'] ?></td>
                    <td><?= htmlspecialchars($r['date_reservation']) ?></td>
                    <td><?= !empty($r['commentaire']) ? htmlspecialchars(substr($r['commentaire'], 0, 50)) . (strlen($r['commentaire']) > 50 ? '…' : '') : '<span class="text-muted">—</span>' ?></td>
                    <td><span class="badge bg-<?= $rsc[$r['statut']] ?? 'secondary' ?>"><?= $rsl[$r['statut']] ?? $r['statut'] ?></span></td>
                    <td><span class="badge bg-<?= $pc[$r['paiement']] ?? 'secondary' ?>"><?= $pl[$r['paiement']] ?? $r['paiement'] ?></span></td>
                    <td>
                      <a href="/TinyTrack/reservations/edit/<?= (int)$r['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                      <form method="POST" action="/TinyTrack/reservations/delete/<?= (int)$r['id'] ?>" style="display:inline" onsubmit="return confirm('Supprimer cette réservation ?')">
                        <button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                      </form>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
<?php if (!empty($reservations)): ?>
<script>
$(document).ready(function(){
  $('#tableRes').DataTable({
    "language":{"emptyTable":"Aucune réservation","info":"_START_ à _END_ sur _TOTAL_","lengthMenu":"Afficher _MENU_","zeroRecords":"Aucun résultat","paginate":{"next":"Suivant","previous":"Précédent"}},
    "pageLength":10,"order":[[4,"desc"]]
  });
});
</script>
<?php endif; ?>

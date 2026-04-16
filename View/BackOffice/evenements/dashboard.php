<?php
session_start();
include(__DIR__ . '/../../../Controller/EvenementController.php');
include(__DIR__ . '/../../../Controller/reservationController.php');
$controller = new EvenementController();
$events = $controller->afficher();
$allEvents = [];
$totalPrix = 0;
$countPlanifie = 0;
$countEnCours = 0;
$countTermine = 0;
$countAnnule = 0;
while ($row = $events->fetch()) {
    $allEvents[] = $row;
    $totalPrix += $row['prix'];
    if ($row['statut'] === 'planifie') $countPlanifie++;
    if ($row['statut'] === 'en_cours') $countEnCours++;
    if ($row['statut'] === 'termine') $countTermine++;
    if ($row['statut'] === 'annule') $countAnnule++;
}
$totalEvents = count($allEvents);

// Réservations
$resController = new ReservationController();
$allRes = $resController->afficher();
$totalRes = count($allRes);
$countConfirmee = 0;
$countEnAttente = 0;
$countResAnnulee = 0;
$countPaye = 0;
foreach ($allRes as $r) {
    if ($r['statut'] === 'confirmee') $countConfirmee++;
    if ($r['statut'] === 'en_attente') $countEnAttente++;
    if ($r['statut'] === 'annulee') $countResAnnulee++;
    if ($r['paiement'] === 'paye') $countPaye++;
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-pie text-success"></i> Dashboard Événements</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item active">Dashboard</li></ol></div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- Stats -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner"><h3><?= $totalEvents ?></h3><p>Total Événements</p></div>
            <div class="icon"><i class="fas fa-calendar-alt"></i></div>
            <a href="evenementList.php" class="small-box-footer">Voir tout <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner"><h3><?= number_format($totalPrix, 2) ?></h3><p>Revenus (TND)</p></div>
            <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            <a href="#" class="small-box-footer">Tous les événements <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner"><h3><?= $countPlanifie ?></h3><p>Planifiés</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="evenementList.php" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner"><h3><?= $countTermine ?></h3><p>Terminés</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="evenementList.php" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Quick actions -->
      <div class="row mb-3">
        <div class="col-md-12">
          <a href="ajouterevenement.php" class="btn btn-success mr-2"><i class="fas fa-calendar-plus"></i> Nouvel événement</a>
          <a href="reservations/add.php" class="btn btn-info mr-2"><i class="fas fa-ticket-alt"></i> Nouvelle réservation</a>
          <a href="reservations/list.php" class="btn btn-warning"><i class="fas fa-list"></i> Réservations</a>
        </div>
      </div>

      <!-- Recent events table -->
      <div class="card card-success">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history"></i> Derniers événements</h3>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-hover table-striped">
            <thead>
              <tr><th>#</th><th>Titre</th><th>Date</th><th>Type</th><th>Lieu</th><th>Prix</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($allEvents, 0, 5) as $ev): ?>
              <tr>
                <td><?= $ev['id'] ?></td>
                <td><strong><?= htmlspecialchars($ev['titre']) ?></strong></td>
                <td><?= $ev['date'] ?></td>
                <td><span class="badge bg-primary"><?= htmlspecialchars($ev['type']) ?></span></td>
                <td><?= htmlspecialchars($ev['lieu']) ?></td>
                <td><?= $ev['prix'] > 0 ? number_format($ev['prix'],2).' TND' : '<span class="text-success">Gratuit</span>' ?></td>
                <td>
                  <?php
                    $sc=['planifie'=>'warning','en_cours'=>'info','termine'=>'danger','annule'=>'secondary'];
                    $sl=['planifie'=>'Planifié','en_cours'=>'En cours','termine'=>'Terminé','annule'=>'Annulé'];
                  ?>
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
          <?php if ($totalEvents > 5): ?>
            <div class="text-center mt-2"><a href="evenementList.php" class="btn btn-default">Voir tous les <?= $totalEvents ?> événements</a></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- ===== RÉSERVATIONS ===== -->
      <h3 class="mt-4 mb-3" style="font-family:'Fredoka One',cursive;color:#2D3436;"><i class="fas fa-ticket-alt text-info"></i> Réservations</h3>

      <!-- Stats réservations -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner"><h3><?= $totalRes ?></h3><p>Total Réservations</p></div>
            <div class="icon"><i class="fas fa-ticket-alt"></i></div>
            <a href="reservations/list.php" class="small-box-footer">Voir tout <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner"><h3><?= $countConfirmee ?></h3><p>Confirmées</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="reservations/list.php" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner"><h3><?= $countEnAttente ?></h3><p>En attente</p></div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="reservations/list.php" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner"><h3><?= $countResAnnulee ?></h3><p>Annulées</p></div>
            <div class="icon"><i class="fas fa-times-circle"></i></div>
            <a href="reservations/list.php" class="small-box-footer">Détails <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- Quick actions réservations -->
      <div class="row mb-3">
        <div class="col-md-12">
          <a href="reservations/add.php" class="btn btn-info mr-2"><i class="fas fa-plus-circle"></i> Nouvelle réservation</a>
          <a href="reservations/list.php" class="btn btn-default"><i class="fas fa-list"></i> Toutes les réservations</a>
        </div>
      </div>

      <!-- Dernières réservations -->
      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title"><i class="fas fa-history"></i> Dernières réservations</h3>
        </div>
        <div class="card-body">
          <table class="table table-bordered table-hover table-striped">
            <thead>
              <tr><th>#</th><th>Événement</th><th>Enfant</th><th>Parent</th><th>Accompagnants</th><th>Date</th><th>Statut</th><th>Paiement</th><th>Actions</th></tr>
            </thead>
            <tbody>
              <?php foreach (array_slice($allRes, 0, 5) as $res): ?>
              <tr>
                <td><?= $res['id'] ?></td>
                <td><strong><?= htmlspecialchars($res['evenement_titre'] ?? 'N/A') ?></strong></td>
                <td><?= $res['enfant_id'] ?? '-' ?></td>
                <td><?= $res['parent_id'] ?? '-' ?></td>
                <td><?= $res['nb_accompagnants'] ?></td>
                <td><?= $res['date_reservation'] ?></td>
                <td>
                  <?php
                    $rsc = ['confirmee'=>'success','en_attente'=>'warning','annulee'=>'danger'];
                    $rsl = ['confirmee'=>'Confirmée','en_attente'=>'En attente','annulee'=>'Annulée'];
                  ?>
                  <span class="badge bg-<?= $rsc[$res['statut']] ?? 'secondary' ?>"><?= $rsl[$res['statut']] ?? $res['statut'] ?></span>
                </td>
                <td>
                  <?php $pc = ['paye'=>'success','non_paye'=>'secondary']; $pl = ['paye'=>'Payé','non_paye'=>'Non payé']; ?>
                  <span class="badge bg-<?= $pc[$res['paiement']] ?? 'secondary' ?>"><?= $pl[$res['paiement']] ?? $res['paiement'] ?></span>
                </td>
                <td>
                  <a href="reservations/edit.php?id=<?= $res['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
          <?php if ($totalRes > 5): ?>
            <div class="text-center mt-2"><a href="reservations/list.php" class="btn btn-default">Voir toutes les <?= $totalRes ?> réservations</a></div>
          <?php endif; ?>
        </div>
      </div>

      <!-- Stats summary -->
      <div class="row">
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-chart-bar text-info"></i> Répartition par statut</h3></div>
            <div class="card-body">
              <div class="mb-2">
                <span class="badge bg-warning" style="font-size:0.85rem;">Planifiés : <?= $countPlanifie ?></span>
                <div class="progress mt-1" style="height:12px;border-radius:8px;">
                  <div class="progress-bar bg-warning" style="width:<?= $totalEvents ? ($countPlanifie/$totalEvents*100) : 0 ?>%;border-radius:8px;"></div>
                </div>
              </div>
              <div class="mb-2">
                <span class="badge bg-info" style="font-size:0.85rem;">En cours : <?= $countEnCours ?></span>
                <div class="progress mt-1" style="height:12px;border-radius:8px;">
                  <div class="progress-bar bg-info" style="width:<?= $totalEvents ? ($countEnCours/$totalEvents*100) : 0 ?>%;border-radius:8px;"></div>
                </div>
              </div>
              <div class="mb-2">
                <span class="badge bg-danger" style="font-size:0.85rem;">Terminés : <?= $countTermine ?></span>
                <div class="progress mt-1" style="height:12px;border-radius:8px;">
                  <div class="progress-bar bg-danger" style="width:<?= $totalEvents ? ($countTermine/$totalEvents*100) : 0 ?>%;border-radius:8px;"></div>
                </div>
              </div>
              <div class="mb-2">
                <span class="badge bg-secondary" style="font-size:0.85rem;">Annulés : <?= $countAnnule ?></span>
                <div class="progress mt-1" style="height:12px;border-radius:8px;">
                  <div class="progress-bar bg-secondary" style="width:<?= $totalEvents ? ($countAnnule/$totalEvents*100) : 0 ?>%;border-radius:8px;"></div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card">
            <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-info-circle text-success"></i> Résumé</h3></div>
            <div class="card-body">
              <table class="table table-sm">
                <tr><td><i class="fas fa-calendar text-success"></i> Total événements</td><td class="text-right"><strong><?= $totalEvents ?></strong></td></tr>
                <tr><td><i class="fas fa-money-bill text-info"></i> Revenus totaux</td><td class="text-right"><strong><?= number_format($totalPrix, 2) ?> TND</strong></td></tr>
                <tr><td><i class="fas fa-clock text-warning"></i> À venir</td><td class="text-right"><strong><?= $countPlanifie + $countEnCours ?></strong></td></tr>
                <tr><td><i class="fas fa-check text-danger"></i> Passés</td><td class="text-right"><strong><?= $countTermine + $countAnnule ?></strong></td></tr>
                <tr><td><i class="fas fa-calculator text-purple"></i> Prix moyen</td><td class="text-right"><strong><?= $totalEvents ? number_format($totalPrix/$totalEvents, 2) : '0.00' ?> TND</strong></td></tr>
              </table>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

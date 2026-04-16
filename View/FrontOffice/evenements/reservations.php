<?php
session_start();
require_once __DIR__ . '/../../../Controller/reservationController.php';
require_once __DIR__ . '/../../../Controller/evenementController.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}

$resCtrl = new ReservationController();
$reservations = $resCtrl->afficher();

include '../template/header.php';
?>

<div class="container py-5" style="position:relative;z-index:1;">

  <div class="text-center mb-5">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" style="height:70px;margin-bottom:15px;">
    <h2 class="section-title"><i class="fas fa-ticket-alt"></i> Réservations</h2>
    <p class="text-muted mt-3">Consultez les réservations aux événements</p>
  </div>

  <div class="row g-4">
    <?php if (empty($reservations)): ?>
      <div class="col-12 text-center">
        <div class="card-kider p-5">
          <i class="fas fa-ticket-alt fa-3x" style="color:#ddd;"></i>
          <h5 style="font-family:'Fredoka One',cursive;color:#999;margin-top:1rem;">Aucune réservation</h5>
        </div>
      </div>
    <?php else: ?>
      <?php foreach ($reservations as $r): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card-kider p-4">
            <div class="d-flex justify-content-between align-items-start mb-2">
              <h5 style="font-family:'Fredoka One',cursive;margin:0;font-size:1rem;"><?= htmlspecialchars($r['evenement_titre'] ?? 'Événement') ?></h5>
              <span class="badge bg-<?= $r['statut']==='confirmee'?'success':($r['statut']==='en_attente'?'warning text-dark':'danger') ?>" style="border-radius:20px;font-size:0.7rem;"><?= ucfirst($r['statut']) ?></span>
            </div>
            <div class="medical-box mt-2">
              <p><i class="fas fa-child" style="color:#FF8FAB;"></i> <strong>Enfant ID :</strong> <?= $r['enfant_id'] ?? '—' ?></p>
              <p><i class="fas fa-users" style="color:#FFA726;"></i> <strong>Accompagnants :</strong> <?= $r['nb_accompagnants'] ?></p>
              <p><i class="fas fa-calendar" style="color:#5B9BD5;"></i> <strong>Date :</strong> <?= date('d/m/Y H:i', strtotime($r['date_reservation'])) ?></p>
              <p class="mb-0"><i class="fas fa-money-bill" style="color:#4CAF50;"></i> <strong>Paiement :</strong>
                <?php if ($r['paiement'] === 'paye'): ?>
                  <span style="background:#E8F5E9;color:#2E7D32;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.75rem;font-weight:700;">Payé</span>
                <?php else: ?>
                  <span style="background:#FFF3E0;color:#E65100;padding:0.15rem 0.5rem;border-radius:10px;font-size:0.75rem;font-weight:700;">Non payé</span>
                <?php endif; ?>
              </p>
            </div>
            <?php if (!empty($r['commentaire'])): ?>
              <div style="margin-top:0.5rem;padding:0.5rem;background:#f8f9fa;border-radius:8px;font-size:0.8rem;color:#666;">
                <i class="fas fa-comment" style="color:#9C7CDB;"></i> <?= htmlspecialchars($r['commentaire']) ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>

  <div class="text-center mt-4">
    <a href="/TinyTrack/View/FrontOffice/evenements/list.php" class="btn btn-success" style="border-radius:25px;padding:0.5rem 1.5rem;font-weight:700;"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
  </div>
</div>

<?php include '../template/footer.php'; ?>

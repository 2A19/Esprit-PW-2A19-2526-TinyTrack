<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
require_once __DIR__ . '/../../../Controller/ReclamationController.php';
require_once __DIR__ . '/../../../Controller/ReponseController.php';
require_once __DIR__ . '/../../../config/db.php';
$reclamationController = new ReclamationController();
$reponseController = new ReponseController();

// Recupere l'email du parent connecte pour filtrer
$parentEmail = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = Database::getInstance()->getConnection()
        ->prepare("SELECT email FROM user WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$_SESSION['user_id']]);
    $row = $stmt->fetch();
    $parentEmail = $row['email'] ?? null;
}
$reclamations = $parentEmail ? $reclamationController->listReclamationsByEmail($parentEmail) : [];
$recentResponses = [];
foreach ($reclamations as $rec) {
    $reps = $reponseController->getReponsesByReclamation($rec->getId());
    foreach ($reps as $r) { $recentResponses[] = ['rep' => $r, 'sujet' => $rec->getSujet(), 'statut' => $rec->getStatut()]; }
}
include 'template/header.php';
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" style="height:70px;margin-bottom:15px;">
    <h2 class="section-title"><i class="fas fa-reply-all"></i> Suivi & Réponses</h2>
    <p class="text-muted mt-3">Consultez les réponses de notre équipe</p>
  </div>

  <!-- Tracking -->
  <div class="text-center mb-4"><h3 style="font-family:'Fredoka One',cursive;color:#5B9BD5;"><i class="fas fa-clipboard-list"></i> Vos demandes</h3></div>
  <div class="row g-4 mb-5">
    <?php foreach ($reclamations as $rec): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-kider p-4">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h6 style="font-family:'Fredoka One',cursive;margin:0;"><?= htmlspecialchars($rec->getSujet()) ?></h6>
            <span class="badge bg-<?= $rec->getStatut()=='En attente'?'warning text-dark':'success' ?>" style="border-radius:20px;font-size:0.7rem;"><?= $rec->getStatut() ?></span>
          </div>
          <p style="font-size:0.8rem;color:#999;">Ticket #<?= $rec->getId() ?></p>
        </div>
      </div>
    <?php endforeach; ?>
  </div>

  <!-- Responses -->
  <div class="text-center mb-4"><h3 style="font-family:'Fredoka One',cursive;color:#4CAF50;"><i class="fas fa-comments"></i> Réponses de nos experts</h3></div>
  <?php if (empty($recentResponses)): ?>
    <div class="row justify-content-center"><div class="col-md-8 text-center"><div class="card-kider p-5"><i class="fas fa-inbox fa-3x" style="color:#ddd;"></i><h5 style="font-family:'Fredoka One',cursive;color:#999;margin-top:1rem;">Aucune réponse pour le moment</h5></div></div></div>
  <?php else: ?>
    <div class="row justify-content-center">
      <div class="col-md-8">
        <?php foreach (array_reverse($recentResponses) as $item): ?>
          <div class="card-kider p-4 mb-3" style="border-left:4px solid #4CAF50;">
            <h6 style="font-family:'Fredoka One',cursive;color:#333;"><?= htmlspecialchars($item['sujet']) ?></h6>
            <div class="medical-box mt-2">
              <p style="line-height:1.8;"><i class="fas fa-comment-dots" style="color:#4CAF50;"></i> <?= nl2br(htmlspecialchars($item['rep']->getMessage())) ?></p>
              <p class="mb-0 text-end" style="font-size:0.75rem;color:#999;"><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($item['rep']->getDateReponse())) ?></p>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  <?php endif; ?>
</div>

<?php include 'template/footer.php'; ?>

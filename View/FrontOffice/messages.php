<?php
session_start();
require_once __DIR__ . '/../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Get messages received by this user
$stmt = $db->prepare("
    SELECT m.*, u.prenom AS exp_prenom, u.nom AS exp_nom, u.role AS exp_role
    FROM message m
    JOIN user u ON m.expediteur_id = u.id
    WHERE m.destinataire_id = :uid
    ORDER BY m.date_envoi DESC
");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$messagesRecus = $stmt->fetchAll();

// Get messages sent by this user
$stmt = $db->prepare("
    SELECT m.*, u.prenom AS dest_prenom, u.nom AS dest_nom
    FROM message m
    JOIN user u ON m.destinataire_id = u.id
    WHERE m.expediteur_id = :uid
    ORDER BY m.date_envoi DESC
");
$stmt->execute([':uid' => $_SESSION['user_id']]);
$messagesEnvoyes = $stmt->fetchAll();

include 'template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <div class="text-center mb-4">
    <h2 class="section-title"><i class="fas fa-envelope"></i> Messagerie</h2>
    <p class="text-muted mt-3">Vos échanges avec l'équipe TinyTrack</p>
  </div>

  <div class="row justify-content-center">
    <div class="col-md-10">

      <!-- Tabs -->
      <ul class="nav nav-tabs mb-3" style="border:none;">
        <li class="nav-item">
          <a class="nav-link active" href="#recus" data-bs-toggle="tab" style="border-radius:12px 12px 0 0;font-weight:700;">
            <i class="fas fa-inbox" style="color:#4CAF50;"></i> Reçus
            <?php if (count($messagesRecus) > 0): ?>
              <span style="background:#4CAF50;color:#fff;border-radius:50%;padding:0.1rem 0.5rem;font-size:0.7rem;margin-left:0.3rem;"><?= count($messagesRecus) ?></span>
            <?php endif; ?>
          </a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#envoyes" data-bs-toggle="tab" style="border-radius:12px 12px 0 0;font-weight:700;">
            <i class="fas fa-paper-plane" style="color:#5B9BD5;"></i> Envoyés
            <?php if (count($messagesEnvoyes) > 0): ?>
              <span style="background:#5B9BD5;color:#fff;border-radius:50%;padding:0.1rem 0.5rem;font-size:0.7rem;margin-left:0.3rem;"><?= count($messagesEnvoyes) ?></span>
            <?php endif; ?>
          </a>
        </li>
      </ul>

      <div class="tab-content">

        <!-- RECEIVED -->
        <div class="tab-pane fade show active" id="recus">
          <?php if (empty($messagesRecus)): ?>
            <div class="card-kider p-5 text-center">
              <div style="font-size:3rem;margin-bottom:0.5rem;">&#x1F4ED;</div>
              <h5 style="font-family:'Fredoka One',cursive;color:#999;">Aucun message reçu</h5>
              <p class="text-muted">Votre boîte de réception est vide pour le moment</p>
            </div>
          <?php else: ?>
            <?php foreach ($messagesRecus as $msg): ?>
              <div class="card-kider p-3 mb-2" style="border-left:4px solid <?= $msg['lu'] ? '#E0E0E0' : '#4CAF50' ?>;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
                  <div>
                    <strong style="font-size:0.9rem;">
                      <?php if ($msg['exp_role'] === 'admin'): ?>
                        <i class="fas fa-shield-alt" style="color:#4CAF50;"></i>
                      <?php elseif ($msg['exp_role'] === 'educateur'): ?>
                        <i class="fas fa-user-tie" style="color:#5B9BD5;"></i>
                      <?php else: ?>
                        <i class="fas fa-user" style="color:#FFA726;"></i>
                      <?php endif; ?>
                      <?= htmlspecialchars($msg['exp_prenom'] . ' ' . $msg['exp_nom']) ?>
                    </strong>
                    <?php if (!$msg['lu']): ?>
                      <span style="background:#4CAF50;color:#fff;padding:0.1rem 0.4rem;border-radius:8px;font-size:0.6rem;font-weight:700;margin-left:0.3rem;">NOUVEAU</span>
                    <?php endif; ?>
                  </div>
                  <span style="font-size:0.75rem;color:#999;"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></span>
                </div>
                <p style="font-weight:700;font-size:0.85rem;margin-bottom:0.2rem;color:#333;"><?= htmlspecialchars($msg['sujet']) ?></p>
                <p style="font-size:0.85rem;color:#666;margin-bottom:0;"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

        <!-- SENT -->
        <div class="tab-pane fade" id="envoyes">
          <?php if (empty($messagesEnvoyes)): ?>
            <div class="card-kider p-5 text-center">
              <div style="font-size:3rem;margin-bottom:0.5rem;">&#x1F4E4;</div>
              <h5 style="font-family:'Fredoka One',cursive;color:#999;">Aucun message envoyé</h5>
              <p class="text-muted">Vous n'avez envoyé aucun message pour le moment</p>
            </div>
          <?php else: ?>
            <?php foreach ($messagesEnvoyes as $msg): ?>
              <div class="card-kider p-3 mb-2" style="border-left:4px solid #5B9BD5;">
                <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.3rem;">
                  <strong style="font-size:0.9rem;">
                    <i class="fas fa-arrow-right" style="color:#5B9BD5;"></i>
                    À : <?= htmlspecialchars($msg['dest_prenom'] . ' ' . $msg['dest_nom']) ?>
                  </strong>
                  <span style="font-size:0.75rem;color:#999;"><?= date('d/m/Y H:i', strtotime($msg['date_envoi'])) ?></span>
                </div>
                <p style="font-weight:700;font-size:0.85rem;margin-bottom:0.2rem;color:#333;"><?= htmlspecialchars($msg['sujet']) ?></p>
                <p style="font-size:0.85rem;color:#666;margin-bottom:0;"><?= nl2br(htmlspecialchars($msg['contenu'])) ?></p>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>

      </div>

    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>

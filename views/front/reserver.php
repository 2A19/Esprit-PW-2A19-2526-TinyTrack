<?php
session_start();
require_once(__DIR__ . '/../../controller/evenementController.php');
require_once(__DIR__ . '/../../controller/reservationController.php');
require_once(__DIR__ . '/../../models/reservation.php');

$evController = new EvenementController();
$resController = new ReservationController();

$evenement_id = isset($_GET['evenement_id']) ? (int)$_GET['evenement_id'] : 0;
$event = $evenement_id ? $evController->afficherParId($evenement_id) : null;

$errors = [];
$old = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $evenement_id = (int)($_POST['evenement_id'] ?? 0);
    $enfant_id    = trim($_POST['enfant_id'] ?? '');
    $parent_id    = trim($_POST['parent_id'] ?? '');
    $nb_acc       = trim($_POST['nb_accompagnants'] ?? '0');
    $commentaire  = trim($_POST['commentaire'] ?? '');

    $event = $evController->afficherParId($evenement_id);

    if (!$event) $errors[] = "Événement introuvable.";
    if ($enfant_id === '') $errors[] = "L'ID enfant est obligatoire.";
    if ($parent_id === '') $errors[] = "L'ID parent est obligatoire.";
    if (!is_numeric($nb_acc) || (int)$nb_acc < 0) $errors[] = "Nombre d'accompagnants invalide.";

    if (empty($errors)) {
        $res = new Reservation();
        $res->setEvenementId($evenement_id);
        $res->setEnfantId((int)$enfant_id);
        $res->setParentId((int)$parent_id);
        $res->setNbAccompagnants((int)$nb_acc);
        $res->setCommentaire($commentaire);
        $res->setDateReservation(new DateTime());
        $res->setStatut('en_attente');
        $res->setPaiement('non_paye');

        $resController->ajouter($res);
        $success = true;
    }
}

$allEvents = $evController->afficher()->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <title>TinyTrack — Réserver</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root{--kider-green:#4CAF50;--kider-blue:#5B9BD5;--kider-yellow:#FFD93D;--kider-pink:#FF8FAB;--kider-orange:#FFA726;--kider-purple:#9C7CDB}
    body{font-family:'Nunito',sans-serif;background:#FFF9F0;color:#333}
    .navbar-kider{background:#fff;box-shadow:0 3px 20px rgba(0,0,0,0.06);padding:0.8rem 0;border-bottom:4px solid transparent;border-image:linear-gradient(90deg,var(--kider-green),var(--kider-blue),var(--kider-yellow),var(--kider-pink),var(--kider-orange)) 1}
    .navbar-kider .navbar-brand{font-family:'Fredoka One',cursive;color:var(--kider-green);font-size:1.6rem;display:flex;align-items:center;gap:10px}
    .navbar-kider .navbar-brand img{height:42px;width:42px;object-fit:contain}
    .navbar-kider .nav-link{font-weight:700;color:#555;padding:0.5rem 1rem;border-radius:25px;margin:0 2px;transition:all 0.2s}
    .navbar-kider .nav-link:hover{color:var(--kider-green);background:rgba(76,175,80,0.08)}
    .section-title{font-family:'Fredoka One',cursive;color:var(--kider-green);font-size:2rem;position:relative;display:inline-block}
    .section-title::after{content:'';position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:linear-gradient(90deg,var(--kider-yellow),var(--kider-orange));border-radius:2px}
    .form-card{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;border:none}
    .form-control,.form-select{border-radius:12px;border:2px solid #E8E8E8;transition:all 0.25s}
    .form-control:focus,.form-select:focus{border-color:var(--kider-green);box-shadow:0 0 0 4px rgba(76,175,80,0.12)}
    label{font-weight:700;font-size:0.9rem;color:#555}
    .btn-reserver{background:linear-gradient(135deg,var(--kider-green),#66BB6A);color:#fff;border:none;border-radius:25px;font-weight:700;padding:0.7rem 2rem;font-size:1rem;box-shadow:0 3px 10px rgba(76,175,80,0.2)}
    .btn-reserver:hover{background:linear-gradient(135deg,#388E3C,var(--kider-green));color:#fff;transform:translateY(-2px)}
    .event-info{background:linear-gradient(135deg,#E8F5E9,#FFF9F0);border-radius:16px;padding:1.5rem;margin-bottom:1.5rem}
    .alert{border-radius:16px;border:none;font-weight:600}
    footer{background:linear-gradient(135deg,var(--kider-green),#388E3C);color:#fff;padding:2.5rem 0;margin-top:4rem;position:relative;overflow:hidden}
    footer::before{content:'';position:absolute;top:-20px;left:50%;transform:translateX(-50%);width:120%;height:40px;background:#FFF9F0;border-radius:0 0 50% 50%}
    footer .footer-logo{font-family:'Fredoka One',cursive;font-size:1.4rem}
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="index.php">
      <img src="images/logo.png" alt="TinyTrack">
      TinyTrack
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navKider"><span class="navbar-toggler-icon"></span></button>
    <div class="collapse navbar-collapse" id="navKider">
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-calendar-alt"></i> Événements</a></li>
        <li class="nav-item"><a class="nav-link" href="mesreservations.php"><i class="fas fa-ticket-alt"></i> Mes Réservations</a></li>
        <li class="nav-item"><a class="nav-link" href="../back/index.php"><i class="fas fa-lock"></i> BackOffice</a></li>
      </ul>
    </div>
  </div>
</nav>

<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="section-title"><i class="fas fa-ticket-alt"></i> Réserver</h2>
    </div>

    <div class="row justify-content-center">
      <div class="col-md-8 col-lg-6">

        <?php if ($success): ?>
          <div class="alert alert-success text-center" style="background:linear-gradient(135deg,#E8F5E9,#C8E6C9);color:#2E7D32;">
            <i class="fas fa-check-circle fa-2x mb-2"></i><br>
            <strong>Réservation enregistrée avec succès !</strong><br>
            Votre réservation est en attente de confirmation.
            <div class="mt-3">
              <a href="index.php" class="btn btn-sm btn-outline-success me-2"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
              <a href="mesreservations.php" class="btn btn-sm btn-success"><i class="fas fa-list"></i> Mes réservations</a>
            </div>
          </div>
        <?php else: ?>

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="background:linear-gradient(135deg,#FFEBEE,#FFCDD2);color:#C62828;">
              <strong><i class="fas fa-exclamation-triangle"></i> Erreurs :</strong>
              <ul class="mb-0 mt-1"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
            </div>
          <?php endif; ?>

          <?php if ($event): ?>
            <div class="event-info">
              <h5 style="font-family:'Fredoka One',cursive;margin-bottom:0.5rem;"><i class="fas fa-calendar-alt text-success"></i> <?= htmlspecialchars($event['titre']) ?></h5>
              <div style="font-size:0.9rem;color:#666;">
                <i class="fas fa-calendar" style="color:var(--kider-blue);"></i> <?= date('d/m/Y', strtotime($event['date'])) ?>
                &nbsp;|&nbsp; <i class="fas fa-clock" style="color:var(--kider-orange);"></i> <?= substr($event['heure_debut'],0,5) ?> — <?= substr($event['heure_fin'],0,5) ?>
                &nbsp;|&nbsp; <i class="fas fa-map-marker-alt" style="color:var(--kider-pink);"></i> <?= htmlspecialchars($event['lieu']) ?>
                &nbsp;|&nbsp; <strong style="color:var(--kider-green);"><?= $event['prix'] > 0 ? number_format($event['prix'],2).' TND' : 'Gratuit' ?></strong>
              </div>
            </div>
          <?php endif; ?>

          <div class="form-card">
            <form method="POST">
              <div class="mb-3">
                <label for="evenement_id"><i class="fas fa-calendar-alt text-info"></i> Événement <span class="text-danger">*</span></label>
                <select name="evenement_id" id="evenement_id" class="form-select" required>
                  <option value="">-- Choisir un événement --</option>
                  <?php foreach ($allEvents as $ev): ?>
                    <?php if ($ev['statut'] !== 'termine' && $ev['statut'] !== 'annule'): ?>
                      <option value="<?= $ev['id'] ?>" <?= ($evenement_id == $ev['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ev['titre']) ?> — <?= date('d/m/Y', strtotime($ev['date'])) ?></option>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="enfant_id"><i class="fas fa-child text-warning"></i> ID Enfant <span class="text-danger">*</span></label>
                  <input type="number" name="enfant_id" id="enfant_id" class="form-control" placeholder="Ex: 1" value="<?= htmlspecialchars($old['enfant_id'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="parent_id"><i class="fas fa-user text-primary"></i> ID Parent <span class="text-danger">*</span></label>
                  <input type="number" name="parent_id" id="parent_id" class="form-control" placeholder="Ex: 1" value="<?= htmlspecialchars($old['parent_id'] ?? '') ?>" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="nb_accompagnants"><i class="fas fa-users text-purple"></i> Nombre d'accompagnants</label>
                <input type="number" name="nb_accompagnants" id="nb_accompagnants" class="form-control" min="0" value="<?= htmlspecialchars($old['nb_accompagnants'] ?? '0') ?>">
              </div>

              <div class="mb-4">
                <label for="commentaire"><i class="fas fa-comment text-info"></i> Commentaire <small class="text-muted">(optionnel)</small></label>
                <textarea name="commentaire" id="commentaire" class="form-control" rows="3" placeholder="Informations supplémentaires..."><?= htmlspecialchars($old['commentaire'] ?? '') ?></textarea>
              </div>

              <div class="text-center">
                <button type="submit" class="btn btn-reserver"><i class="fas fa-check-circle"></i> Confirmer la réservation</button>
                <a href="index.php" class="btn btn-outline-secondary ms-2" style="border-radius:25px;font-weight:700;">Annuler</a>
              </div>
            </form>
          </div>

        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<!-- Footer -->
<footer class="text-center">
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem;">
    <div class="footer-logo mb-2">TinyTrack</div>
    <p style="opacity:0.8;font-size:0.95rem;">Chaque petit pas compte</p>
    <p style="opacity:0.5;font-size:0.8rem;">ESPRIT 2A19 &copy; 2026 — Rayen Ajili</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

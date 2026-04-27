<?php
session_start();
require_once(__DIR__ . '/../../controller/evenementController.php');
require_once(__DIR__ . '/../../controller/reservationController.php');
require_once(__DIR__ . '/../../models/reservation.php');
require_once(__DIR__ . '/../../config.php');

$evController  = new EvenementController();
$resController = new ReservationController();

$evenement_id = isset($_GET['evenement_id']) ? (int)$_GET['evenement_id'] : 0;
$event        = $evenement_id ? $evController->afficherParId($evenement_id) : null;

$errors  = [];
$old     = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old          = $_POST;
    $evenement_id = (int)($_POST['evenement_id'] ?? 0);
    $enfant_id    = trim($_POST['enfant_id']        ?? '');
    $parent_id    = trim($_POST['parent_id']         ?? '');
    $nb_acc       = trim($_POST['nb_accompagnants']  ?? '0');
    $commentaire  = trim($_POST['commentaire']        ?? '');

    // Recharger l'événement depuis la BDD (données fraîches)
    $event = $evController->afficherParId($evenement_id);

    // ── Validations ──────────────────────────────────────────
    if (!$event)                                          $errors[] = "Événement introuvable.";
    if ($enfant_id === '')                                $errors[] = "L'ID enfant est obligatoire.";
    if ($parent_id === '')                                $errors[] = "L'ID parent est obligatoire.";
    if (!is_numeric($nb_acc) || (int)$nb_acc < 0)        $errors[] = "Nombre d'accompagnants invalide.";

    if ($event) {
        if ($event['statut'] === 'complet')               $errors[] = "Cet événement est complet, aucune place disponible.";
        if ($event['statut'] === 'termine')               $errors[] = "Cet événement est terminé.";
        if ($event['statut'] === 'annule')                $errors[] = "Cet événement est annulé.";
        if ((int)$event['capacite_max'] <= 0)             $errors[] = "Plus aucune place disponible pour cet événement.";
    }

    // ── Traitement si aucune erreur ──────────────────────────
    if (empty($errors)) {
        // 1. Enregistrer la réservation
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

        // 2. Décrémenter capacite_max et passer à "complet" si nécessaire
        try {
            $pdo             = config::getConnexion();
            $nouvelle_cap    = max(0, (int)$event['capacite_max'] - 1);
            $nouveau_statut  = ($nouvelle_cap === 0) ? 'complet' : $event['statut'];

            $stmt = $pdo->prepare("
                UPDATE evenement
                SET capacite_max = :cap,
                    statut       = :statut
                WHERE id         = :id
            ");
            $stmt->execute([
                ':cap'    => $nouvelle_cap,
                ':statut' => $nouveau_statut,
                ':id'     => $evenement_id,
            ]);

        } catch (Exception $e) {
            // La réservation est déjà enregistrée, on logue juste l'erreur
            error_log('[TinyTrack] Erreur décrémentation capacité : ' . $e->getMessage());
        }

        // Recharger l'event pour afficher les infos à jour dans le message de succès
        $event   = $evController->afficherParId($evenement_id);
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

    /* ── Badge capacité ── */
    .cap-badge{
      display:inline-flex;align-items:center;gap:6px;
      padding:.25rem .75rem;border-radius:20px;font-size:.8rem;font-weight:700;
    }
    .cap-ok   { background:#E8F5E9;color:#2E7D32; }
    .cap-low  { background:#FFF3E0;color:#E65100; }
    .cap-zero { background:#FFEBEE;color:#C62828; }

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
      <img src="images/logo.png" alt="TinyTrack"> TinyTrack
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navKider">
      <span class="navbar-toggler-icon"></span>
    </button>
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
          <!-- ── Succès ── -->
          <div class="alert text-center" style="background:linear-gradient(135deg,#E8F5E9,#C8E6C9);color:#2E7D32;">
            <i class="fas fa-check-circle fa-2x mb-2"></i><br>
            <strong>Réservation enregistrée avec succès !</strong><br>
            Votre réservation est en attente de confirmation.

            <?php if ($event): ?>
              <hr style="border-color:rgba(46,125,50,.2)">
              <div style="font-size:.85rem;color:#388E3C">
                <?php
                  $cap_restante = (int)$event['capacite_max'];
                  if ($cap_restante === 0): ?>
                    <i class="fas fa-ban"></i> <strong>Événement complet</strong> — Plus aucune place disponible.
                  <?php elseif ($cap_restante <= 5): ?>
                    <i class="fas fa-exclamation-triangle" style="color:#E65100"></i>
                    Plus que <strong><?= $cap_restante ?> place<?= $cap_restante > 1 ? 's' : '' ?></strong> disponible<?= $cap_restante > 1 ? 's' : '' ?> !
                  <?php else: ?>
                    <i class="fas fa-users"></i> <?= $cap_restante ?> place<?= $cap_restante > 1 ? 's' : '' ?> encore disponible<?= $cap_restante > 1 ? 's' : '' ?>.
                <?php endif; ?>
              </div>
            <?php endif; ?>

            <div class="mt-3">
              <a href="index.php" class="btn btn-sm btn-outline-success me-2">
                <i class="fas fa-arrow-left"></i> Retour aux événements
              </a>
              <a href="mesreservations.php" class="btn btn-sm btn-success">
                <i class="fas fa-list"></i> Mes réservations
              </a>
            </div>
          </div>

        <?php else: ?>

          <!-- ── Erreurs ── -->
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" style="background:linear-gradient(135deg,#FFEBEE,#FFCDD2);color:#C62828;">
              <strong><i class="fas fa-exclamation-triangle"></i> Erreurs :</strong>
              <ul class="mb-0 mt-1">
                <?php foreach ($errors as $e): ?>
                  <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <!-- ── Info événement sélectionné ── -->
          <?php if ($event): ?>
            <div class="event-info">
              <h5 style="font-family:'Fredoka One',cursive;margin-bottom:.5rem">
                <i class="fas fa-calendar-alt text-success"></i> <?= htmlspecialchars($event['titre']) ?>
              </h5>
              <div style="font-size:.9rem;color:#666;margin-bottom:.7rem">
                <i class="fas fa-calendar" style="color:var(--kider-blue)"></i> <?= date('d/m/Y',strtotime($event['date'])) ?>
                &nbsp;|&nbsp;
                <i class="fas fa-clock" style="color:var(--kider-orange)"></i> <?= substr($event['heure_debut'],0,5) ?> — <?= substr($event['heure_fin'],0,5) ?>
                &nbsp;|&nbsp;
                <i class="fas fa-map-marker-alt" style="color:var(--kider-pink)"></i> <?= htmlspecialchars($event['lieu']) ?>
                &nbsp;|&nbsp;
                <strong style="color:var(--kider-green)"><?= $event['prix'] > 0 ? number_format($event['prix'],2).' TND' : 'Gratuit' ?></strong>
              </div>

              <!-- Badge capacité dynamique -->
              <?php
                $cap = (int)$event['capacite_max'];
                if ($cap === 0):
              ?>
                <span class="cap-badge cap-zero">
                  <i class="fas fa-ban"></i> Complet — 0 place disponible
                </span>
              <?php elseif ($cap <= 5): ?>
                <span class="cap-badge cap-low">
                  <i class="fas fa-fire"></i> Dernières places : <?= $cap ?> restante<?= $cap > 1 ? 's' : '' ?>
                </span>
              <?php else: ?>
                <span class="cap-badge cap-ok">
                  <i class="fas fa-users"></i> <?= $cap ?> place<?= $cap > 1 ? 's' : '' ?> disponible<?= $cap > 1 ? 's' : '' ?>
                </span>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <!-- ── Formulaire ── -->
          <div class="form-card">
            <form method="POST">

              <div class="mb-3">
                <label for="evenement_id">
                  <i class="fas fa-calendar-alt text-info"></i> Événement <span class="text-danger">*</span>
                </label>
                <select name="evenement_id" id="evenement_id" class="form-select" required>
                  <option value="">-- Choisir un événement --</option>
                  <?php foreach ($allEvents as $ev): ?>
                    <?php
                      // Masquer les événements terminés, annulés ET complets
                      if (in_array($ev['statut'], ['termine','annule','complet'])) continue;
                    ?>
                    <option value="<?= $ev['id'] ?>"
                      <?= ($evenement_id == $ev['id']) ? 'selected' : '' ?>>
                      <?= htmlspecialchars($ev['titre']) ?>
                      — <?= date('d/m/Y', strtotime($ev['date'])) ?>
                      (<?= $ev['capacite_max'] ?> place<?= $ev['capacite_max'] > 1 ? 's' : '' ?>)
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>

              <div class="row">
                <div class="col-md-6 mb-3">
                  <label for="enfant_id">
                    <i class="fas fa-child text-warning"></i> ID Enfant <span class="text-danger">*</span>
                  </label>
                  <input type="number" name="enfant_id" id="enfant_id" class="form-control"
                    placeholder="Ex: 1" min="1"
                    value="<?= htmlspecialchars($old['enfant_id'] ?? '') ?>" required>
                </div>
                <div class="col-md-6 mb-3">
                  <label for="parent_id">
                    <i class="fas fa-user text-primary"></i> ID Parent <span class="text-danger">*</span>
                  </label>
                  <input type="number" name="parent_id" id="parent_id" class="form-control"
                    placeholder="Ex: 1" min="1"
                    value="<?= htmlspecialchars($old['parent_id'] ?? '') ?>" required>
                </div>
              </div>

              <div class="mb-3">
                <label for="nb_accompagnants">
                  <i class="fas fa-users" style="color:var(--kider-purple)"></i> Nombre d'accompagnants
                </label>
                <input type="number" name="nb_accompagnants" id="nb_accompagnants"
                  class="form-control" min="0"
                  value="<?= htmlspecialchars($old['nb_accompagnants'] ?? '0') ?>">
              </div>

              <div class="mb-4">
                <label for="commentaire">
                  <i class="fas fa-comment text-info"></i> Commentaire
                  <small class="text-muted">(optionnel)</small>
                </label>
                <textarea name="commentaire" id="commentaire" class="form-control" rows="3"
                  placeholder="Informations supplémentaires..."><?= htmlspecialchars($old['commentaire'] ?? '') ?></textarea>
              </div>

              <div class="text-center">
                <button type="submit" class="btn btn-reserver">
                  <i class="fas fa-check-circle"></i> Confirmer la réservation
                </button>
                <a href="index.php" class="btn btn-outline-secondary ms-2" style="border-radius:25px;font-weight:700">
                  Annuler
                </a>
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
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem">
    <div class="footer-logo mb-2">TinyTrack</div>
    <p style="opacity:.8;font-size:.95rem">Chaque petit pas compte</p>
    <p style="opacity:.5;font-size:.8rem">ESPRIT 2A19 &copy; 2026 — Rayen Ajili</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Mise à jour du badge capacité quand on change d'événement dans le select
document.getElementById('evenement_id')?.addEventListener('change', function() {
  if (this.value) {
    window.location.href = 'reserver.php?evenement_id=' + this.value;
  }
});
</script>
</body>
</html> 
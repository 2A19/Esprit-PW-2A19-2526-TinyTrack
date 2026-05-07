<?php
// Vue passive — données injectées par ReservationController::reserver()
// Variables : $event, $allEvents, $errors, $old, $success, $placesRestantes, $mesEnfants
$event = $event ?? null;
$old = $old ?? [];
$placesRestantes = $placesRestantes ?? null;
$mesEnfants = $mesEnfants ?? [];
$estParent = (($_SESSION['user_role'] ?? '') === 'parent');
$estComplet = $event && ($event['statut'] === 'complet' || ($placesRestantes !== null && $placesRestantes <= 0));
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
    :root{--green:#4CAF50;--blue:#5B9BD5;--yellow:#FFD93D;--pink:#FF8FAB;--orange:#FFA726;--purple:#9C7CDB}
    body{font-family:'Nunito',sans-serif;background:#FFF9F0;color:#333}
    .navbar-kider{background:#fff;box-shadow:0 3px 20px rgba(0,0,0,0.06);padding:0.8rem 0;border-bottom:4px solid transparent;border-image:linear-gradient(90deg,var(--green),var(--blue),var(--yellow),var(--pink),var(--orange)) 1}
    .navbar-kider .navbar-brand{font-family:'Fredoka One',cursive;color:var(--green);font-size:1.6rem;display:flex;align-items:center;gap:10px}
    .navbar-kider .navbar-brand img{height:42px;width:42px;object-fit:contain}
    .section-title{font-family:'Fredoka One',cursive;color:var(--green);font-size:2rem;position:relative;display:inline-block}
    .section-title::after{content:'';position:absolute;bottom:-6px;left:50%;transform:translateX(-50%);width:60px;height:4px;background:linear-gradient(90deg,var(--yellow),var(--orange));border-radius:2px}
    .card-kider{background:#fff;border-radius:20px;box-shadow:0 4px 20px rgba(0,0,0,0.06);padding:2rem;border:none}
    .ev-info{background:linear-gradient(135deg,#E8F5E9,#FFF9F0);padding:1rem 1.5rem;border-radius:12px;margin-bottom:1.5rem}
    .ev-info p{margin:0.3rem 0;font-size:0.9rem}
    footer{background:linear-gradient(135deg,var(--green),#388E3C);color:#fff;padding:2.5rem 0;margin-top:4rem;position:relative;overflow:hidden}
    footer::before{content:'';position:absolute;top:-20px;left:50%;transform:translateX(-50%);width:120%;height:40px;background:#FFF9F0;border-radius:0 0 50% 50%}
  </style>
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-kider sticky-top">
  <div class="container">
    <a class="navbar-brand" href="/TinyTrack/evenements/parent">
      <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack">
      TinyTrack
    </a>
    <div class="ms-auto d-flex gap-2">
      <a href="/TinyTrack/evenements/parent" class="btn" style="background:linear-gradient(135deg,var(--green),#66BB6A);color:#fff;border-radius:25px;font-weight:700;padding:0.4rem 1.2rem;font-size:0.85rem;"><i class="fas fa-arrow-left"></i> Événements</a>
      <a href="/TinyTrack/mes-reservations" class="btn" style="background:linear-gradient(135deg,var(--orange),var(--pink));color:#fff;border-radius:25px;font-weight:700;padding:0.4rem 1.2rem;font-size:0.85rem;"><i class="fas fa-ticket-alt"></i> Mes réservations</a>
    </div>
  </div>
</nav>

<section class="py-5">
  <div class="container">
    <div class="text-center mb-4">
      <h2 class="section-title"><i class="fas fa-ticket-alt"></i> Réserver une place</h2>
    </div>

    <?php if ($success): ?>
      <div class="row justify-content-center"><div class="col-md-8">
        <div class="alert alert-success" style="border-radius:16px;border:none;">
          <h5 style="font-family:'Fredoka One',cursive;color:#0F5132;"><i class="fas fa-check-circle"></i> Réservation enregistrée !</h5>
          <p class="mb-2">Votre réservation pour <strong><?= htmlspecialchars($event['titre'] ?? '') ?></strong> est <em>en attente</em> de confirmation.</p>
          <a href="/TinyTrack/mes-reservations" class="btn btn-sm btn-success" style="border-radius:20px;">Voir mes réservations</a>
          <a href="/TinyTrack/evenements/parent" class="btn btn-sm btn-light" style="border-radius:20px;">Retour aux événements</a>
        </div>
      </div></div>
    <?php endif; ?>

    <?php if (!empty($errors)): ?>
      <div class="row justify-content-center"><div class="col-md-8">
        <div class="alert alert-danger" style="border-radius:16px;border:none;">
          <ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
      </div></div>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="card-kider">

          <?php if ($event):
            $capMax = (int)$event['capacite_max'];
            $restantes = $placesRestantes ?? $capMax;
            $reservees = max(0, $capMax - $restantes);
            $pct = $capMax > 0 ? min(100, round($reservees / $capMax * 100)) : 0;
            $barColor = $pct >= 100 ? '#EF5350' : ($pct >= 75 ? '#FFA726' : '#4CAF50');
          ?>
            <div class="ev-info">
              <h5 style="font-family:'Fredoka One',cursive;color:var(--green);"><i class="fas fa-calendar-star"></i> <?= htmlspecialchars($event['titre']) ?></h5>
              <p><i class="fas fa-calendar" style="color:var(--blue);"></i> <strong>Date :</strong> <?= date('d/m/Y', strtotime($event['date'])) ?></p>
              <p><i class="fas fa-clock" style="color:var(--orange);"></i> <strong>Horaire :</strong> <?= substr($event['heure_debut'],0,5) ?> — <?= substr($event['heure_fin'],0,5) ?></p>
              <p><i class="fas fa-map-marker-alt" style="color:var(--pink);"></i> <strong>Lieu :</strong> <?= htmlspecialchars($event['lieu']) ?></p>

              <div style="margin:0.8rem 0;">
                <div style="display:flex;justify-content:space-between;font-size:0.9rem;margin-bottom:0.3rem;">
                  <span><i class="fas fa-users" style="color:var(--purple);"></i> <strong><?= $reservees ?></strong> / <?= $capMax ?> places réservées</span>
                  <span style="color:#666;font-weight:700;"><?= $restantes ?> restante<?= $restantes > 1 ? 's' : '' ?></span>
                </div>
                <div style="height:8px;background:#E8E8E8;border-radius:4px;overflow:hidden;">
                  <div style="height:100%;width:<?= $pct ?>%;background:<?= $barColor ?>;border-radius:4px;transition:width 0.4s ease;"></div>
                </div>
              </div>

              <p><strong style="color:var(--green);"><?= $event['prix'] > 0 ? number_format($event['prix'],2).' TND' : 'Gratuit' ?></strong></p>

              <?php if ($estComplet): ?>
                <div class="alert alert-danger mt-2 mb-0" style="border-radius:12px;border:none;">
                  <i class="fas fa-times-circle"></i> <strong>Événement complet</strong> — toutes les places ont été réservées.
                </div>
              <?php endif; ?>
            </div>
          <?php endif; ?>

          <?php if (!$estComplet): ?>
          <form method="POST" action="/TinyTrack/evenements/reserver/<?= $event['id'] ?? '' ?>">
            <input type="hidden" name="evenement_id" value="<?= $event['id'] ?? '' ?>">

            <?php if (!$event): ?>
              <div class="mb-3">
                <label style="font-weight:700;">Événement <span style="color:#EF5350;">*</span></label>
                <select name="evenement_id" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;">
                  <option value="">-- Choisir un événement --</option>
                  <?php foreach (($allEvents ?? []) as $e):
                    if (in_array($e['statut'], ['termine','annule','complet'], true)) continue;
                  ?>
                    <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['titre']) ?> — <?= date('d/m/Y', strtotime($e['date'])) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            <?php endif; ?>

            <?php if ($estParent): ?>
              <!-- Parent : select de SES enfants + parent_id en hidden -->
              <input type="hidden" name="parent_id" value="<?= (int)($_SESSION['user_id'] ?? 0) ?>">
              <div class="mb-3">
                <label style="font-weight:700;"><i class="fas fa-child" style="color:var(--pink);"></i> Pour quel enfant ? <span style="color:#EF5350;">*</span></label>
                <?php if (empty($mesEnfants)): ?>
                  <div class="alert alert-warning mt-2" style="border-radius:12px;border:none;">
                    <i class="fas fa-exclamation-triangle"></i> Aucun enfant rattaché à votre compte. Contactez l'administrateur.
                  </div>
                <?php else: ?>
                  <select name="enfant_id" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" required>
                    <option value="">-- Choisir votre enfant --</option>
                    <?php foreach ($mesEnfants as $enf):
                      $age = $enf['date_naissance'] ? floor((strtotime('today') - strtotime($enf['date_naissance'])) / (365.25 * 86400)) : null;
                    ?>
                      <option value="<?= (int)$enf['id'] ?>" <?= ((string)$enf['id'] === (string)($old['enfant_id'] ?? '')) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($enf['prenom'] . ' ' . $enf['nom']) ?><?= $age !== null ? ' — ' . $age . ' ans' : '' ?>
                      </option>
                    <?php endforeach; ?>
                  </select>
                <?php endif; ?>
              </div>
            <?php else: ?>
              <!-- Admin/educateur : saisie libre de l'ID enfant + ID parent -->
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label style="font-weight:700;">ID Enfant <span style="color:#EF5350;">*</span></label>
                  <input type="text" name="enfant_id" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" placeholder="Ex: 1" value="<?= htmlspecialchars($old['enfant_id'] ?? '') ?>">
                </div>
                <div class="col-md-6 mb-3">
                  <label style="font-weight:700;">ID Parent <span style="color:#EF5350;">*</span></label>
                  <input type="text" name="parent_id" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" placeholder="Ex: 5" value="<?= htmlspecialchars($old['parent_id'] ?? '') ?>">
                </div>
              </div>
            <?php endif; ?>

            <div class="mb-3">
              <label style="font-weight:700;">Nombre d'accompagnants</label>
              <input type="number" name="nb_accompagnants" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" min="0" value="<?= htmlspecialchars($old['nb_accompagnants'] ?? '0') ?>">
            </div>

            <div class="mb-3">
              <label style="font-weight:700;">Commentaire (optionnel)</label>
              <textarea name="commentaire" class="form-control" rows="3" style="border-radius:12px;border:2px solid #E8E8E8;" placeholder="Allergies, demandes particulières..."><?= htmlspecialchars($old['commentaire'] ?? '') ?></textarea>
            </div>

            <div class="d-flex justify-content-between mt-4">
              <a href="/TinyTrack/evenements/parent" class="btn btn-light" style="border-radius:25px;"><i class="fas fa-arrow-left"></i> Retour</a>
              <button type="submit" class="btn" style="background:linear-gradient(135deg,var(--green),#66BB6A);color:#fff;border-radius:25px;font-weight:700;padding:0.5rem 2rem;"><i class="fas fa-check"></i> Confirmer la réservation</button>
            </div>
          </form>
          <?php else: ?>
            <div class="text-center mt-3">
              <a href="/TinyTrack/evenements/parent" class="btn" style="background:linear-gradient(135deg,var(--green),#66BB6A);color:#fff;border-radius:25px;font-weight:700;padding:0.5rem 2rem;"><i class="fas fa-arrow-left"></i> Retour aux événements</a>
            </div>
          <?php endif; ?>

        </div>
      </div>
    </div>
  </div>
</section>

<footer class="text-center">
  <div class="container" style="position:relative;z-index:1;padding-top:1.5rem;">
    <p style="font-family:'Fredoka One',cursive;font-size:1.2rem;margin-bottom:0.3rem;">TinyTrack</p>
    <p style="opacity:0.8;font-size:0.85rem;">Chaque petit pas compte</p>
  </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

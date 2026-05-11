<?php
// Vue passive — données injectées par ReservationController::edit($id)
// Variables : $reservation, $evenements, $errors
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit text-warning"></i> Modifier Réservation #<?= $id ?></h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="/TinyTrack/reservations">Réservations</a></li><li class="breadcrumb-item active">Modifier</li></ol></div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div>
      <?php endif; ?>

      <div class="row justify-content-center">
        <div class="col-md-8">
          <div class="card card-warning">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-ticket-alt"></i> Modifier réservation</h3></div>
            <form method="POST" novalidate>
              <div class="card-body">
                <div class="form-group">
                  <label>Événement</label>
                  <select name="evenement_id" class="form-control">
                    <?php foreach ($evenements as $ev): ?>
                      <option value="<?= $ev['id'] ?>" <?= ($reservation['evenement_id'] == $ev['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ev['titre']) ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label>ID Enfant</label><input type="text" name="enfant_id" class="form-control" value="<?= htmlspecialchars($reservation['enfant_id'] ?? '') ?>"></div></div>
                  <div class="col-md-6"><div class="form-group"><label>ID Parent</label><input type="text" name="parent_id" class="form-control" value="<?= htmlspecialchars($reservation['parent_id'] ?? '') ?>"></div></div>
                </div>
                <div class="form-group"><label>Nb accompagnants</label><input type="text" name="nb_accompagnants" class="form-control" value="<?= htmlspecialchars($reservation['nb_accompagnants'] ?? '0') ?>"></div>
                <div class="form-group"><label>Commentaire</label><textarea name="commentaire" class="form-control" rows="3"><?= htmlspecialchars($reservation['commentaire'] ?? '') ?></textarea></div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label>Statut</label><select name="statut" class="form-control"><option value="en_attente" <?= ($reservation['statut']==='en_attente')?'selected':'' ?>>En attente</option><option value="confirmee" <?= ($reservation['statut']==='confirmee')?'selected':'' ?>>Confirmée</option><option value="annulee" <?= ($reservation['statut']==='annulee')?'selected':'' ?>>Annulée</option></select></div></div>
                  <div class="col-md-6"><div class="form-group"><label>Paiement</label><select name="paiement" class="form-control"><option value="non_paye" <?= ($reservation['paiement']==='non_paye')?'selected':'' ?>>Non payé</option><option value="paye" <?= ($reservation['paiement']==='paye')?'selected':'' ?>>Payé</option></select></div></div>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="/TinyTrack/reservations" class="btn btn-default float-right"><i class="fas fa-arrow-left"></i> Retour</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

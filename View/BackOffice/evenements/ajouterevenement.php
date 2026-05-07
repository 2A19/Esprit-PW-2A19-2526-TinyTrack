<?php
// Vue passive — données injectées par EvenementController::add()
// Variables : $errors, $old
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-calendar-plus text-success"></i> Ajouter un Événement</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="/TinyTrack/evenements">Événements</a></li><li class="breadcrumb-item active">Ajouter</li></ol></div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
          <button type="button" class="close" data-dismiss="alert">&times;</button>
          <strong><i class="fas fa-exclamation-triangle"></i> Erreurs :</strong>
          <ul class="mb-0 mt-1"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul>
        </div>
      <?php endif; ?>

      <form method="POST" action="/TinyTrack/evenements/add" novalidate onsubmit="return validerEvenement()">
        <div class="row">

          <!-- LEFT COLUMN -->
          <div class="col-lg-8">

            <!-- Infos générales -->
            <div class="card card-success">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Informations générales</h3></div>
              <div class="card-body">
                <div class="form-group">
                  <label for="titre">Titre <span class="text-danger">*</span></label>
                  <input type="text" class="form-control <?= isset($errors['titre']) ? 'is-invalid' : '' ?>" id="titre" name="titre" placeholder="Ex : Fête de la musique 2026" value="<?= htmlspecialchars($old['titre'] ?? '') ?>">
                  <div class="invalid-feedback" id="err_titre"><?= $errors['titre'] ?? '' ?></div>
                </div>
                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea class="form-control" id="description" name="description" rows="3" placeholder="Décrivez l'événement..."><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>
              </div>
            </div>

            <!-- Date & Horaires -->
            <div class="card card-info">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-clock"></i> Date & Horaires</h3></div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="date">Date <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>" id="date" name="date" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($old['date'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_date"><?= $errors['date'] ?? '' ?></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="heure_debut">Heure début <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['heure_debut']) ? 'is-invalid' : '' ?>" id="heure_debut" name="heure_debut" placeholder="HH:MM" value="<?= htmlspecialchars($old['heure_debut'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_heure_debut"><?= $errors['heure_debut'] ?? '' ?></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="heure_fin">Heure fin <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['heure_fin']) ? 'is-invalid' : '' ?>" id="heure_fin" name="heure_fin" placeholder="HH:MM" value="<?= htmlspecialchars($old['heure_fin'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_heure_fin"><?= $errors['heure_fin'] ?? '' ?></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Lieu & Capacité -->
            <div class="card card-warning">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Lieu & Capacité</h3></div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label for="lieu">Lieu <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['lieu']) ? 'is-invalid' : '' ?>" id="lieu" name="lieu" placeholder="Ex : Salle des fêtes, TinyTrack" value="<?= htmlspecialchars($old['lieu'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_lieu"><?= $errors['lieu'] ?? '' ?></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="capacite_max">Capacité max <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['capacite_max']) ? 'is-invalid' : '' ?>" id="capacite_max" name="capacite_max" placeholder="Ex : 50" value="<?= htmlspecialchars($old['capacite_max'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_capacite_max"><?= $errors['capacite_max'] ?? '' ?></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <!-- RIGHT COLUMN -->
          <div class="col-lg-4">

            <!-- Type -->
            <div class="card">
              <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-tag text-info"></i> Type <span class="text-danger">*</span></h3></div>
              <div class="card-body">
                <?php if (isset($errors['type'])): ?><div class="alert alert-danger py-1 px-2" style="font-size:0.8rem;"><?= $errors['type'] ?></div><?php endif; ?>
                <select name="type" class="form-control">
                  <option value="">-- Choisir --</option>
                  <?php foreach(['concert'=>'Concert','conference'=>'Conférence','sport'=>'Sport','atelier'=>'Atelier','festival'=>'Festival','formation'=>'Formation','exposition'=>'Exposition','autre'=>'Autre'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= (($old['type'] ?? '') === $v) ? 'selected' : '' ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <!-- Prix -->
            <div class="card">
              <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-money-bill-wave text-success"></i> Prix <span class="text-danger">*</span></h3></div>
              <div class="card-body">
                <div class="form-group">
                  <label for="prix">Prix (TND)</label>
                  <input type="text" class="form-control <?= isset($errors['prix']) ? 'is-invalid' : '' ?>" id="prix" name="prix" placeholder="0.00" value="<?= htmlspecialchars($old['prix'] ?? '') ?>">
                  <div class="invalid-feedback" id="err_prix"><?= $errors['prix'] ?? '' ?></div>
                </div>
                <div class="d-flex gap-1 flex-wrap mt-2">
                  <button type="button" class="btn btn-sm btn-default qp" data-p="0">Gratuit</button>
                  <button type="button" class="btn btn-sm btn-default qp" data-p="5">5 TND</button>
                  <button type="button" class="btn btn-sm btn-default qp" data-p="10">10 TND</button>
                  <button type="button" class="btn btn-sm btn-default qp" data-p="25">25 TND</button>
                </div>
              </div>
            </div>

            <!-- Groupe -->
            <div class="card">
              <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-users text-purple"></i> Groupe</h3></div>
              <div class="card-body">
                <div class="form-group">
                  <label>ID du groupe <small class="text-muted">(optionnel)</small></label>
                  <input type="text" name="groupe_id" class="form-control" placeholder="Laisser vide si aucun" value="<?= htmlspecialchars($old['groupe_id'] ?? '') ?>">
                </div>
              </div>
            </div>

            <!-- Statut -->
            <div class="card">
              <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-flag text-warning"></i> Statut <span class="text-danger">*</span></h3></div>
              <div class="card-body">
                <?php if (isset($errors['statut'])): ?><div class="alert alert-danger py-1 px-2" style="font-size:0.8rem;"><?= $errors['statut'] ?></div><?php endif; ?>
                <select name="statut" class="form-control">
                  <option value="planifie" <?= (($old['statut'] ?? 'planifie') === 'planifie') ? 'selected' : '' ?>>Planifié</option>
                  <option value="en_cours" <?= (($old['statut'] ?? '') === 'en_cours') ? 'selected' : '' ?>>En cours</option>
                  <option value="termine" <?= (($old['statut'] ?? '') === 'termine') ? 'selected' : '' ?>>Terminé</option>
                  <option value="annule" <?= (($old['statut'] ?? '') === 'annule') ? 'selected' : '' ?>>Annulé</option>
                </select>
              </div>
            </div>

            <!-- Actions -->
            <div class="card">
              <div class="card-body">
                <button type="submit" class="btn btn-success btn-block mb-2"><i class="fas fa-save"></i> Enregistrer l'événement</button>
                <a href="/TinyTrack/evenements" class="btn btn-default btn-block"><i class="fas fa-arrow-left"></i> Annuler</a>
              </div>
            </div>

          </div>

        </div>
      </form>

    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
<script src="/ProjetEvenements/assets/js/validation.js?v=2"></script>
<script>
document.querySelectorAll('.qp').forEach(function(btn){
  btn.addEventListener('click',function(){ document.getElementById('prix').value=this.dataset.p; });
});
</script>

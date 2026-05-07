<?php
// Vue passive — données injectées par EnfantController::add()
// Variables disponibles : $errors, $groupes, $parents, $old
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-user-plus text-success"></i> Inscrire un enfant</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="/TinyTrack/enfants">Enfants</a></li>
            <li class="breadcrumb-item active">Inscription</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">
      <div class="row justify-content-center">
        <div class="col-md-8">

          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
              <strong><i class="fas fa-exclamation-triangle"></i> Erreurs :</strong>
              <ul class="mb-0 mt-1">
                <?php foreach ($errors as $err): ?>
                  <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
              </ul>
            </div>
          <?php endif; ?>

          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-child"></i> Formulaire d'inscription</h3>
            </div>

            <form method="POST" id="formEnfant" novalidate onsubmit="return validerFormEnfant()">
              <div class="card-body">

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="nom">Nom <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="nom" name="nom" placeholder="Nom de famille" value="<?= htmlspecialchars($old['nom'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_nom"></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="prenom">Prénom <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="prenom" name="prenom" placeholder="Prénom" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_prenom"></div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_naissance">Date de naissance <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="date_naissance" name="date_naissance" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($old['date_naissance'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_date_naissance"></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="sexe">Sexe <span class="text-danger">*</span></label>
                      <select class="form-control" id="sexe" name="sexe">
                        <option value="">-- Sélectionner --</option>
                        <option value="M" <?= (($old['sexe'] ?? '') === 'M') ? 'selected' : '' ?>>Garçon</option>
                        <option value="F" <?= (($old['sexe'] ?? '') === 'F') ? 'selected' : '' ?>>Fille</option>
                      </select>
                      <div class="invalid-feedback" id="err_sexe"></div>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="date_inscription">Date d'inscription</label>
                      <input type="text" class="form-control" id="date_inscription" name="date_inscription" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($old['date_inscription'] ?? date('Y-m-d')) ?>">
                      <div class="invalid-feedback" id="err_date_inscription"></div>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="statut">Statut</label>
                      <select class="form-control" id="statut" name="statut">
                        <option value="actif" <?= (($old['statut'] ?? 'actif') === 'actif') ? 'selected' : '' ?>>Actif</option>
                        <option value="archive" <?= (($old['statut'] ?? '') === 'archive') ? 'selected' : '' ?>>Archivé</option>
                      </select>
                    </div>
                  </div>
                </div>

                <div class="row">
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="groupe_id">Groupe</label>
                      <select class="form-control" id="groupe_id" name="groupe_id">
                        <option value="">-- Aucun groupe --</option>
                        <?php foreach ($groupes as $g): ?>
                          <option value="<?= (int)$g['id'] ?>" <?= (($old['groupe_id'] ?? '') == $g['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($g['nom']) ?> (<?= htmlspecialchars($g['niveau']) ?>)
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="col-md-6">
                    <div class="form-group">
                      <label for="parent_id">Parent</label>
                      <select class="form-control" id="parent_id" name="parent_id">
                        <option value="">-- Aucun parent --</option>
                        <?php foreach ($parents as $p): ?>
                          <option value="<?= (int)$p['id'] ?>" <?= (($old['parent_id'] ?? '') == $p['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($p['prenom'] . ' ' . $p['nom']) ?> — <?= htmlspecialchars($p['email']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                  </div>
                </div>

              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Inscrire l'enfant</button>
                <a href="/TinyTrack/enfants" class="btn btn-default float-right"><i class="fas fa-arrow-left"></i> Retour</a>
              </div>
            </form>
          </div>

        </div>
      </div>
    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

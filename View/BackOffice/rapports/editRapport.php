<?php
// Vue passive — données injectées par RapportController::edit($id)
// Variables : $data, $errors, $id, $activites
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit text-warning"></i> Modifier Rapport #<?= $id ?></h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="/TinyTrack/rapports">Rapports</a></li><li class="breadcrumb-item active">Modifier</li></ol></div>
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
            <div class="card-header"><h3 class="card-title"><i class="fas fa-book"></i> Modifier rapport</h3></div>
            <form method="POST" action="" onsubmit="return validerRapport();">
              <div class="card-body">

                <div class="form-group">
                  <label for="contenu_rapport">Contenu <span class="text-danger">*</span></label>
                  <textarea name="contenu_rapport" id="contenu_rapport" class="form-control" rows="4" placeholder="Saisir le contenu du rapport"><?= htmlspecialchars($data['contenu_rapport'] ?? '') ?></textarea>
                  <small id="err_contenu_rapport" class="text-danger"></small>
                </div>

                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="date_rapport">Date <span class="text-danger">*</span></label>
                      <input type="text" name="date_rapport" id="date_rapport" class="form-control" placeholder="AAAA-MM-JJ HH:MM"
                             value="<?= htmlspecialchars(!empty($data['date_rapport']) ? date('Y-m-d H:i', strtotime($data['date_rapport'])) : '') ?>">
                      <small id="err_date_rapport" class="text-danger"></small>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="id_activite">Activité <span class="text-danger">*</span></label>
                      <select name="id_activite" id="id_activite" class="form-control">
                        <option value="">-- Choisir une activité --</option>
                        <?php foreach ($activites as $a): ?>
                          <option value="<?= (int)$a['id_activite'] ?>" <?= ((string)$a['id_activite'] === (string)($data['id_activite'] ?? '')) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($a['nom_activite']) ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                      <small id="err_id_activite" class="text-danger"></small>
                    </div>
                  </div>

                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="id_educateur">Éducateur ID</label>
                      <input type="text" name="id_educateur" id="id_educateur" class="form-control" placeholder="Ex: 2"
                             value="<?= htmlspecialchars($data['id_educateur'] ?? '') ?>">
                      <small id="err_id_educateur" class="text-danger"></small>
                    </div>
                  </div>
                </div>

              </div>

              <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="/TinyTrack/rapports" class="btn btn-default float-right"><i class="fas fa-arrow-left"></i> Retour</a>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

<script>
function setErreur(id, message) {
  var champ = document.getElementById(id);
  var zoneErreur = document.getElementById('err_' + id);
  if (champ) champ.style.borderColor = '#dc3545';
  if (zoneErreur) zoneErreur.innerHTML = message;
}
function clearErreur(id) {
  var champ = document.getElementById(id);
  var zoneErreur = document.getElementById('err_' + id);
  if (champ) champ.style.borderColor = '';
  if (zoneErreur) zoneErreur.innerHTML = '';
}
function validerRapport() {
  var ok = true;
  ['contenu_rapport','date_rapport','id_activite','id_educateur'].forEach(clearErreur);

  var contenu = document.getElementById('contenu_rapport').value.replace(/^\s+|\s+$/g, '');
  var date = document.getElementById('date_rapport').value.replace(/^\s+|\s+$/g, '');
  var idActivite = document.getElementById('id_activite').value.replace(/^\s+|\s+$/g, '');
  var idEducateur = document.getElementById('id_educateur').value.replace(/^\s+|\s+$/g, '');

  if (contenu === '') { setErreur('contenu_rapport', 'Le contenu est obligatoire.'); ok = false; }
  else if (contenu.length < 5) { setErreur('contenu_rapport', 'Min 5 caractères.'); ok = false; }

  if (date === '') { setErreur('date_rapport', 'La date est obligatoire.'); ok = false; }
  else if (!/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(date)) { setErreur('date_rapport', 'Format attendu : AAAA-MM-JJ HH:MM.'); ok = false; }

  if (idActivite === '') { setErreur('id_activite', "Veuillez choisir une activité."); ok = false; }

  if (idEducateur !== '' && !/^[0-9]+$/.test(idEducateur)) {
    setErreur('id_educateur', "L'identifiant de l'éducateur doit être numérique.");
    ok = false;
  }

  return ok;
}
</script>

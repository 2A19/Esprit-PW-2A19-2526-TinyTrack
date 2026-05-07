<?php
// Vue passive — données injectées par ActiviteController::edit($id)
// Variables : $data, $errors
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit text-warning"></i> Modifier Activité</h1></div></div></div></div>

  <section class="content"><div class="container-fluid">
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="row justify-content-center"><div class="col-md-8">
      <div class="card card-warning">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-paint-brush"></i> Modifier</h3></div>
        <form method="POST" novalidate onsubmit="return validerActivite()">
          <div class="card-body">
            <div class="form-group"><label>Nom <span class="text-danger">*</span></label><input type="text" name="nom_activite" id="nom_activite" class="form-control" value="<?= htmlspecialchars($data['nom_activite']) ?>"><div class="invalid-feedback" id="err_nom_activite"></div></div>
            <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($data['description'] ?? '') ?></textarea></div>
            <div class="row">
              <div class="col-md-4"><div class="form-group"><label>Date <span class="text-danger">*</span></label><input type="text" name="date_activite" id="date_activite" class="form-control" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($data['date_activite']) ?>"><div class="invalid-feedback" id="err_date_activite"></div></div></div>
              <div class="col-md-4"><div class="form-group"><label>Heure <span class="text-danger">*</span></label><input type="text" name="heure_activite" id="heure_activite" class="form-control" placeholder="HH:MM" value="<?= htmlspecialchars($data['heure_activite']) ?>"><div class="invalid-feedback" id="err_heure_activite"></div></div></div>
              <div class="col-md-4"><div class="form-group"><label>Éducateur ID</label><input type="text" name="id_educateur" class="form-control" value="<?= htmlspecialchars($data['id_educateur'] ?? '') ?>"></div></div>
            </div>
          </div>
          <div class="card-footer"><button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button><a href="/TinyTrack/activites" class="btn btn-default float-right"><i class="fas fa-arrow-left"></i> Retour</a></div>
        </form>
      </div>
    </div></div>
  </div></section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>
<script>
function showE(id,msg){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.add('is-invalid');if(e){e.textContent=msg;e.style.display='block';}}
function clearE(id){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.remove('is-invalid');if(e)e.style.display='none';}
function validerActivite(){var ok=true;['nom_activite','date_activite','heure_activite'].forEach(clearE);if(!document.getElementById('nom_activite').value.trim()){showE('nom_activite','Obligatoire');ok=false;}var d=document.getElementById('date_activite').value.trim();if(!d){showE('date_activite','Obligatoire');ok=false;}else if(!/^\d{4}-\d{2}-\d{2}$/.test(d)){showE('date_activite','Format: AAAA-MM-JJ');ok=false;}var h=document.getElementById('heure_activite').value.trim();if(!h){showE('heure_activite','Obligatoire');ok=false;}else if(!/^\d{2}:\d{2}$/.test(h)){showE('heure_activite','Format: HH:MM');ok=false;}return ok;}
</script>

<?php
// Vue passive — données injectées par ReservationController::add()
// Variables : $evenements, $errors, $old
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-plus-circle text-success"></i> Ajouter Réservation</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="/TinyTrack/reservations">Réservations</a></li><li class="breadcrumb-item active">Ajouter</li></ol></div>
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
          <div class="card card-success">
            <div class="card-header"><h3 class="card-title"><i class="fas fa-ticket-alt"></i> Nouvelle réservation</h3></div>
            <form method="POST" action="/TinyTrack/reservations/add" novalidate onsubmit="return validerRes()">
              <div class="card-body">
                <div class="form-group">
                  <label>Événement <span class="text-danger">*</span></label>
                  <select name="evenement_id" id="evenement_id" class="form-control">
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($evenements as $ev): ?>
                      <option value="<?= $ev['id'] ?>" <?= (($old['evenement_id'] ?? '') == $ev['id']) ? 'selected' : '' ?>><?= htmlspecialchars($ev['titre']) ?> (<?= $ev['date'] ?>)</option>
                    <?php endforeach; ?>
                  </select>
                  <div class="invalid-feedback" id="err_evenement_id"></div>
                </div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label>ID Enfant</label><input type="text" name="enfant_id" id="enfant_id" class="form-control" placeholder="Ex: 1" value="<?= htmlspecialchars($old['enfant_id'] ?? '') ?>"><div class="invalid-feedback" id="err_enfant_id"></div></div></div>
                  <div class="col-md-6"><div class="form-group"><label>ID Parent</label><input type="text" name="parent_id" id="parent_id" class="form-control" placeholder="Ex: 5" value="<?= htmlspecialchars($old['parent_id'] ?? '') ?>"><div class="invalid-feedback" id="err_parent_id"></div></div></div>
                </div>
                <div class="form-group"><label>Nb accompagnants</label><input type="text" name="nb_accompagnants" class="form-control" placeholder="0" value="<?= htmlspecialchars($old['nb_accompagnants'] ?? '0') ?>"></div>
                <div class="form-group"><label>Commentaire</label><textarea name="commentaire" class="form-control" rows="3"><?= htmlspecialchars($old['commentaire'] ?? '') ?></textarea></div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label>Statut</label><select name="statut" class="form-control"><option value="en_attente" <?= (($old['statut']??'')==='en_attente')?'selected':'' ?>>En attente</option><option value="confirmee" <?= (($old['statut']??'')==='confirmee')?'selected':'' ?>>Confirmée</option><option value="annulee" <?= (($old['statut']??'')==='annulee')?'selected':'' ?>>Annulée</option></select></div></div>
                  <div class="col-md-6"><div class="form-group"><label>Paiement</label><select name="paiement" class="form-control"><option value="non_paye" <?= (($old['paiement']??'')==='non_paye')?'selected':'' ?>>Non payé</option><option value="paye" <?= (($old['paiement']??'')==='paye')?'selected':'' ?>>Payé</option></select></div></div>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Enregistrer</button>
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
<script>
function showE(id,msg){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.add('is-invalid');if(e){e.textContent=msg;e.style.display='block';}}
function clearE(id){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.remove('is-invalid');if(e)e.style.display='none';}
function validerRes(){
  var ok=true;['evenement_id','enfant_id','parent_id'].forEach(clearE);
  if(!document.getElementById('evenement_id').value){showE('evenement_id','Obligatoire');ok=false;}
  var eid=document.getElementById('enfant_id').value.trim();
  if(eid&&!/^\d+$/.test(eid)){showE('enfant_id','Nombre requis');ok=false;}
  var pid=document.getElementById('parent_id').value.trim();
  if(pid&&!/^\d+$/.test(pid)){showE('parent_id','Nombre requis');ok=false;}
  return ok;
}
</script>

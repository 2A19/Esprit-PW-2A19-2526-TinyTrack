<?php
session_start();
require_once(__DIR__ . '/../../controller/evenementController.php');
require_once(__DIR__ . '/../../models/evenement.php');

$controller = new EvenementController();
$errors = [];

if (!isset($_GET['id'])) { header('Location: evenementList.php'); exit; }
$id = $_GET['id'];
$eventData = $controller->afficherParId($id);
if (!$eventData) { header('Location: evenementList.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $titre       = trim($_POST['titre'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['date'] ?? '');
    $heure_debut = trim($_POST['heure_debut'] ?? '');
    $heure_fin   = trim($_POST['heure_fin'] ?? '');
    $type        = trim($_POST['type'] ?? '');
    $lieu        = trim($_POST['lieu'] ?? '');
    $capacite    = trim($_POST['capacite_max'] ?? '');
    $prix        = trim($_POST['prix'] ?? '');
    $statut      = trim($_POST['statut'] ?? '');
    $groupe_id   = trim($_POST['groupe_id'] ?? '');

    if ($titre === '') $errors[] = 'Titre obligatoire';
    if ($date === '') $errors[] = 'Date obligatoire';
    if ($heure_debut === '') $errors[] = 'Heure début obligatoire';
    if ($heure_fin === '') $errors[] = 'Heure fin obligatoire';
    if ($lieu === '') $errors[] = 'Lieu obligatoire';

    if (empty($errors)) {
        $ev = new Evenement();
        $ev->setTitre($titre);
        $ev->setDescription($description);
        $ev->setDate(new DateTime($date));
        $ev->setHeureDebut($heure_debut);
        $ev->setHeureFin($heure_fin);
        $ev->setType($type);
        $ev->setLieu($lieu);
        $ev->setCapaciteMax((int)$capacite);
        $ev->setPrix((float)$prix);
        $ev->setGroupeId($groupe_id !== '' ? (int)$groupe_id : null);
        $ev->setStatut($statut);
        $controller->modifier($ev, $id);
        $_SESSION['flash'] = ['type' => 'success', 'message' => 'Événement modifié avec succès.'];
        header('Location: evenementList.php');
        exit;
    }
}

$val = $_POST ?? $eventData;
if (!isset($val['titre'])) $val = $eventData;

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit text-warning"></i> Modifier : <?= htmlspecialchars($eventData['titre']) ?></h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="evenementList.php">Événements</a></li><li class="breadcrumb-item active">Modifier</li></ol></div>
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
            <div class="card-header"><h3 class="card-title"><i class="fas fa-calendar-alt"></i> Modifier événement</h3></div>
            <form method="POST" novalidate onsubmit="return validerEvenement()">
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8"><div class="form-group"><label>Titre <span class="text-danger">*</span></label><input type="text" name="titre" id="titre" class="form-control" value="<?= htmlspecialchars($val['titre']) ?>"><div class="invalid-feedback" id="err_titre"></div></div></div>
                  <div class="col-md-4"><div class="form-group"><label>Type</label><select name="type" class="form-control"><?php foreach(['concert','conference','sport','atelier','festival','formation','exposition','autre'] as $t): ?><option value="<?=$t?>" <?=($val['type']==$t)?'selected':''?>><?=ucfirst($t)?></option><?php endforeach; ?></select></div></div>
                </div>
                <div class="form-group"><label>Description</label><textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($val['description'] ?? '') ?></textarea></div>
                <div class="row">
                  <div class="col-md-4"><div class="form-group"><label>Date <span class="text-danger">*</span></label><input type="text" name="date" id="date" class="form-control" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($val['date']) ?>"><div class="invalid-feedback" id="err_date"></div></div></div>
                  <div class="col-md-4"><div class="form-group"><label>Heure début <span class="text-danger">*</span></label><input type="text" name="heure_debut" id="heure_debut" class="form-control" placeholder="HH:MM" value="<?= htmlspecialchars($val['heure_debut']) ?>"><div class="invalid-feedback" id="err_heure_debut"></div></div></div>
                  <div class="col-md-4"><div class="form-group"><label>Heure fin <span class="text-danger">*</span></label><input type="text" name="heure_fin" id="heure_fin" class="form-control" placeholder="HH:MM" value="<?= htmlspecialchars($val['heure_fin']) ?>"><div class="invalid-feedback" id="err_heure_fin"></div></div></div>
                </div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label>Lieu <span class="text-danger">*</span></label><input type="text" name="lieu" id="lieu" class="form-control" value="<?= htmlspecialchars($val['lieu']) ?>"><div class="invalid-feedback" id="err_lieu"></div></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Capacité</label><input type="text" name="capacite_max" id="capacite_max" class="form-control" value="<?= htmlspecialchars($val['capacite_max']) ?>"><div class="invalid-feedback" id="err_capacite_max"></div></div></div>
                  <div class="col-md-3"><div class="form-group"><label>Prix (TND)</label><input type="text" name="prix" id="prix" class="form-control" value="<?= htmlspecialchars($val['prix']) ?>"><div class="invalid-feedback" id="err_prix"></div></div></div>
                </div>
                <div class="row">
                  <div class="col-md-6"><div class="form-group"><label>Groupe ID</label><input type="text" name="groupe_id" class="form-control" value="<?= htmlspecialchars($val['groupe_id'] ?? '') ?>"></div></div>
                  <div class="col-md-6"><div class="form-group"><label>Statut</label><select name="statut" class="form-control"><option value="planifie" <?=($val['statut']=='planifie')?'selected':''?>>Planifié</option><option value="en_cours" <?=($val['statut']=='en_cours')?'selected':''?>>En cours</option><option value="termine" <?=($val['statut']=='termine')?'selected':''?>>Terminé</option><option value="annule" <?=($val['statut']=='annule')?'selected':''?>>Annulé</option></select></div></div>
                </div>
              </div>
              <div class="card-footer">
                <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="evenementList.php" class="btn btn-default float-right"><i class="fas fa-arrow-left"></i> Retour</a>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include 'template/footer.php'; ?>
<script src="/ProjetEvenements/assets/js/validation.js?v=2"></script>

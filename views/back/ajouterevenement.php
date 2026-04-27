<?php
session_start();
require_once(__DIR__ . '/../../controller/evenementController.php');
require_once(__DIR__ . '/../../models/evenement.php');
$errors = [];
$old    = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $titre       = trim($_POST['titre']       ?? '');
    $description = trim($_POST['description'] ?? '');
    $date        = trim($_POST['date']        ?? '');
    $heure_debut = trim($_POST['heure_debut'] ?? '');
    $heure_fin   = trim($_POST['heure_fin']   ?? '');
    $type        = trim($_POST['type']        ?? '');
    $lieu        = trim($_POST['lieu']        ?? '');
    $capacite    = trim($_POST['capacite_max']?? '');
    $prix        = trim($_POST['prix']        ?? '');
    $statut      = trim($_POST['statut']      ?? '');
    $groupe_id   = trim($_POST['groupe_id']   ?? '');
    $avisRaw     = trim($_POST['avis']        ?? '');

    // ✅ FIX: Formatage correct du champ avis en JSON valide
    if ($avisRaw === '') {
        $avis = '[]'; // Valeur par défaut JSON vide
    } else {
        // Si c'est déjà un JSON valide, on garde
        json_decode($avisRaw);
        if (json_last_error() === JSON_ERROR_NONE) {
            $avis = $avisRaw;
        } else {
            // Sinon on encapsule en tableau JSON
            $avis = json_encode([$avisRaw]);
        }
    }

    // Validations
    if ($titre === '')       $errors['titre'] = 'Le titre est obligatoire.';
    if ($date === '')        $errors['date'] = 'La date est obligatoire.';
    if ($heure_debut === '') $errors['heure_debut'] = "L'heure de début est obligatoire.";
    if ($heure_fin === '')   $errors['heure_fin'] = "L'heure de fin est obligatoire.";
    if ($heure_debut && $heure_fin && $heure_fin <= $heure_debut)
        $errors['heure_fin'] = "L'heure de fin doit être après l'heure de début.";
    if ($type === '')        $errors['type'] = 'Le type est obligatoire.';
    if ($lieu === '')        $errors['lieu'] = 'Le lieu est obligatoire.';
    if ($capacite === '' || !ctype_digit($capacite) || (int)$capacite < 1)
        $errors['capacite_max'] = 'La capacité doit être un entier positif.';
    if ($prix === '' || !is_numeric($prix) || (float)$prix < 0)
        $errors['prix'] = 'Le prix doit être un nombre positif ou nul.';
    if ($statut === '')      $errors['statut'] = 'Le statut est obligatoire.';

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
        $ev->setAvis($avis); // ✅ Valeur JSON valide garantie

        $controller = new EvenementController();
        $controller->ajouter($ev);

        $_SESSION['flash'] = ['type' => 'success', 'message' => "L'événement « {$titre} » a été ajouté avec succès."];
        header('Location: evenementList.php');
        exit;
    }
}

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-calendar-plus text-success"></i> Ajouter un Événement</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="evenementList.php">Événements</a></li><li class="breadcrumb-item active">Ajouter</li></ol></div>
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

      <form method="POST" novalidate onsubmit="return validerEvenement()">
        <div class="row">

          <div class="col-lg-8">
            <div class="card card-success">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-info-circle"></i> Informations générales</h3></div>
              <div class="card-body">
                <div class="form-group">
                  <label for="titre">Titre <span class="text-danger">*</span></label>
                  <input type="text" class="form-control <?= isset($errors['titre']) ? 'is-invalid' : '' ?>" id="titre" name="titre" value="<?= htmlspecialchars($old['titre'] ?? '') ?>">
                  <div class="invalid-feedback" id="err_titre"><?= $errors['titre'] ?? '' ?></div>
                </div>
                <div class="form-group">
                  <label for="description">Description</label>
                  <textarea class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($old['description'] ?? '') ?></textarea>
                </div>
                <div class="form-group">
                  <label for="avis">Avis / Notes supplémentaires</label>
                  <!-- ✅ Placeholder mis à jour pour guider l'utilisateur -->
                  <textarea class="form-control" id="avis" name="avis" rows="2"
                    placeholder='Ex: ["parental_guidance", "accessible"] ou laisser vide'><?= htmlspecialchars($old['avis'] ?? '') ?></textarea>
                  <small class="form-text text-muted">Laisser vide ou entrer un tableau JSON. Ex: ["accessible", "tout public"]</small>
                </div>
              </div>
            </div>

            <div class="card card-info">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-clock"></i> Date & Horaires</h3></div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="date">Date <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['date']) ? 'is-invalid' : '' ?>" id="date" name="date" value="<?= htmlspecialchars($old['date'] ?? '') ?>">
                      <div class="invalid-feedback" id="err_date"><?= $errors['date'] ?? '' ?></div>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="heure_debut">Heure début <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['heure_debut']) ? 'is-invalid' : '' ?>" id="heure_debut" name="heure_debut" value="<?= htmlspecialchars($old['heure_debut'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="heure_fin">Heure fin <span class="text-danger">*</span></label>
                      <input type="text" class="form-control <?= isset($errors['heure_fin']) ? 'is-invalid' : '' ?>" id="heure_fin" name="heure_fin" value="<?= htmlspecialchars($old['heure_fin'] ?? '') ?>">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div class="card card-warning">
              <div class="card-header"><h3 class="card-title"><i class="fas fa-map-marker-alt"></i> Lieu & Capacité</h3></div>
              <div class="card-body">
                <div class="row">
                  <div class="col-md-8">
                    <div class="form-group">
                      <label for="lieu">Lieu <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="lieu" name="lieu" value="<?= htmlspecialchars($old['lieu'] ?? '') ?>">
                    </div>
                  </div>
                  <div class="col-md-4">
                    <div class="form-group">
                      <label for="capacite_max">Capacité max <span class="text-danger">*</span></label>
                      <input type="text" class="form-control" id="capacite_max" name="capacite_max" value="<?= htmlspecialchars($old['capacite_max'] ?? '') ?>">
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="card">
              <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-tag text-info"></i> Type</h3></div>
              <div class="card-body">
                <select name="type" class="form-control">
                  <option value="">-- Choisir --</option>
                  <?php foreach(['concert'=>'Concert','conference'=>'Conférence','sport'=>'Sport','atelier'=>'Atelier'] as $v=>$l): ?>
                    <option value="<?= $v ?>" <?= (($old['type'] ?? '') === $v) ? 'selected' : '' ?>><?= $l ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>

            <div class="card">
              <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-money-bill-wave text-success"></i> Prix</h3></div>
              <div class="card-body">
                <input type="text" class="form-control" id="prix" name="prix" value="<?= htmlspecialchars($old['prix'] ?? '') ?>">
              </div>
            </div>

            <div class="card">
              <div class="card-header bg-white"><h3 class="card-title"><i class="fas fa-flag text-warning"></i> Statut</h3></div>
              <div class="card-body">
                <select name="statut" class="form-control">
                  <option value="planifie" <?= (($old['statut'] ?? 'planifie') === 'planifie') ? 'selected' : '' ?>>Planifié</option>
                  <option value="en_cours" <?= (($old['statut'] ?? '') === 'en_cours') ? 'selected' : '' ?>>En cours</option>
                </select>
              </div>
            </div>

            <div class="card">
              <div class="card-body">
                <button type="submit" class="btn btn-success btn-block mb-2"><i class="fas fa-save"></i> Enregistrer</button>
                <a href="evenementList.php" class="btn btn-default btn-block">Annuler</a>
              </div>
            </div>
          </div>

        </div>
      </form>
    </div>
  </section>
</div>
<?php include 'template/footer.php'; ?>
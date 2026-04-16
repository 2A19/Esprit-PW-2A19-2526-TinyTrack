<?php
require_once __DIR__ . '/../../../Controller/RapportController.php';
require_once __DIR__ . '/../../../Model/Rapport.php';

$controller = new RapportController();
$errors = [];

if (!isset($_GET['id'])) { header('Location: listRapports.php'); exit; }
$id = $_GET['id'];
$data = $controller->showRapport($id);
if (!$data) { header('Location: listRapports.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = trim($_POST['contenu_rapport'] ?? '');
    $date = trim($_POST['date_rapport'] ?? '');
    $id_activite = trim($_POST['id_activite'] ?? '');
    $id_educateur = trim($_POST['id_educateur'] ?? '');

    if ($contenu === '') $errors[] = 'Le contenu est obligatoire.';
    if ($date === '') $errors[] = 'La date est obligatoire.';
    if ($id_activite === '') $errors[] = "L'activité est obligatoire.";

    if (empty($errors)) {
        $rapport = new Rapport($id, $contenu, $date, (int)$id_activite, $id_educateur !== '' ? (int)$id_educateur : null);
        $controller->updateRapport($rapport, $id);
        header('Location: listRapports.php?success=edit');
        exit;
    }
    $data = array_merge($data, $_POST);
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header"><div class="container-fluid"><div class="row mb-2"><div class="col-sm-6"><h1 class="m-0"><i class="fas fa-edit text-warning"></i> Modifier Rapport #<?= $id ?></h1></div></div></div></div>

  <section class="content"><div class="container-fluid">
    <?php if (!empty($errors)): ?><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <div class="row justify-content-center"><div class="col-md-8">
      <div class="card card-warning">
        <div class="card-header"><h3 class="card-title"><i class="fas fa-book"></i> Modifier rapport</h3></div>
        <form method="POST" novalidate>
          <div class="card-body">
            <div class="form-group"><label>Contenu <span class="text-danger">*</span></label><textarea name="contenu_rapport" class="form-control" rows="4"><?= htmlspecialchars($data['contenu_rapport']) ?></textarea></div>
            <div class="row">
              <div class="col-md-4"><div class="form-group"><label>Date <span class="text-danger">*</span></label><input type="text" name="date_rapport" class="form-control" placeholder="AAAA-MM-JJ" value="<?= htmlspecialchars($data['date_rapport']) ?>"></div></div>
              <div class="col-md-4"><div class="form-group"><label>Activité ID <span class="text-danger">*</span></label><input type="text" name="id_activite" class="form-control" value="<?= htmlspecialchars($data['id_activite']) ?>"></div></div>
              <div class="col-md-4"><div class="form-group"><label>Éducateur ID</label><input type="text" name="id_educateur" class="form-control" value="<?= htmlspecialchars($data['id_educateur'] ?? '') ?>"></div></div>
            </div>
          </div>
          <div class="card-footer"><button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Enregistrer</button><a href="listRapports.php" class="btn btn-default float-right"><i class="fas fa-arrow-left"></i> Retour</a></div>
        </form>
      </div>
    </div></div>
  </div></section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

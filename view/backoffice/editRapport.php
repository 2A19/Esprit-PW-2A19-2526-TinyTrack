<?php
require_once '../../controller/RapportController.php';
require_once '../../controller/ActiviteController.php';
require_once '../../model/Rapport.php';

$controller = new RapportController();
$activiteController = new ActiviteController();
$activites = $activiteController->listActivites()->fetchAll();

$errors = array();

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: /ProjetRapport/tinytrack/view/backoffice/listRapports.php');
    exit;
}

$id = (int) $_GET['id'];
$data = $controller->showRapport($id);

if (!$data) {
    header('Location: /ProjetRapport/tinytrack/view/backoffice/listRapports.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $contenu = isset($_POST['contenu_rapport']) ? trim($_POST['contenu_rapport']) : '';
    $date = isset($_POST['date_rapport']) ? trim($_POST['date_rapport']) : '';
    $id_activite = isset($_POST['id_activite']) ? trim($_POST['id_activite']) : '';
    $id_educateur = isset($_POST['id_educateur']) ? trim($_POST['id_educateur']) : '';

    if ($contenu === '') {
        $errors[] = 'Le contenu est obligatoire.';
    }

    if ($date === '') {
        $errors[] = 'La date est obligatoire.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Le format de la date doit être AAAA-MM-JJ.';
    }

    if ($id_activite === '') {
        $errors[] = "L'activité est obligatoire.";
    } elseif (!ctype_digit($id_activite)) {
        $errors[] = "L'activité sélectionnée est invalide.";
    } else {
        $activiteExiste = false;
        foreach ($activites as $act) {
            if ((int)$act['id_activite'] === (int)$id_activite) {
                $activiteExiste = true;
                break;
            }
        }

        if (!$activiteExiste) {
            $errors[] = "L'activité sélectionnée n'existe pas.";
        }
    }

    if ($id_educateur !== '' && !ctype_digit($id_educateur)) {
        $errors[] = "L'identifiant de l'éducateur doit être numérique.";
    }

    if (empty($errors)) {
        $rapport = new Rapport(
            $id,
            $contenu,
            $date,
            (int)$id_activite,
            $id_educateur !== '' ? (int)$id_educateur : null
        );

        $controller->updateRapport($rapport, $id);

        header('Location: /ProjetRapport/tinytrack/view/backoffice/listRapports.php?success=edit');
        exit;
    }

    $data = array_merge($data, $_POST);
}

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-edit text-warning"></i> Modifier Rapport #<?php echo $id; ?>
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="/ProjetRapport/tinytrack/view/backoffice/listRapports.php">Rapports</a>
                        </li>
                        <li class="breadcrumb-item active">Modifier</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        <?php foreach ($errors as $e): ?>
                            <li><?php echo htmlspecialchars($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="card card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-book"></i> Modifier rapport
                            </h3>
                        </div>

                        <form method="POST" action="" onsubmit="return validerRapport();">
                            <div class="card-body">

                                <div class="form-group">
                                    <label for="contenu_rapport">
                                        Contenu <span class="text-danger">*</span>
                                    </label>
                                    <textarea
                                        name="contenu_rapport"
                                        id="contenu_rapport"
                                        class="form-control"
                                        rows="4"
                                        placeholder="Saisir le contenu du rapport"
                                    ><?php echo htmlspecialchars(isset($data['contenu_rapport']) ? $data['contenu_rapport'] : ''); ?></textarea>
                                    <small id="err_contenu_rapport" class="text-danger"></small>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_rapport">
                                                Date <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                name="date_rapport"
                                                id="date_rapport"
                                                class="form-control"
                                                placeholder="AAAA-MM-JJ"
                                                value="<?php echo htmlspecialchars(isset($data['date_rapport']) ? $data['date_rapport'] : ''); ?>"
                                            >
                                            <small id="err_date_rapport" class="text-danger"></small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_activite">
                                                Activité <span class="text-danger">*</span>
                                            </label>
                                            <select name="id_activite" id="id_activite" class="form-control">
                                                <option value="">-- Choisir une activité --</option>
                                                <?php foreach ($activites as $act): ?>
                                                    <option
                                                        value="<?php echo $act['id_activite']; ?>"
                                                        <?php echo ((string)$act['id_activite'] === (string)($data['id_activite'] ?? '')) ? 'selected' : ''; ?>
                                                    >
                                                        <?php echo htmlspecialchars($act['nom_activite']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <small id="err_id_activite" class="text-danger"></small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="id_educateur">Éducateur ID</label>
                                            <input
                                                type="text"
                                                name="id_educateur"
                                                id="id_educateur"
                                                class="form-control"
                                                placeholder="Ex: 2"
                                                value="<?php echo htmlspecialchars(isset($data['id_educateur']) ? $data['id_educateur'] : ''); ?>"
                                            >
                                            <small id="err_id_educateur" class="text-danger"></small>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>

                                <a href="/ProjetRapport/tinytrack/view/backoffice/listRapports.php" class="btn btn-default float-right">
                                    <i class="fas fa-arrow-left"></i> Retour
                                </a>
                            </div>
                        </form>

                    </div>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include 'template/footer.php'; ?>

<script>
function setErreur(id, message) {
    var champ = document.getElementById(id);
    var zoneErreur = document.getElementById('err_' + id);

    if (champ) {
        champ.style.borderColor = '#dc3545';
    }

    if (zoneErreur) {
        zoneErreur.innerHTML = message;
    }
}

function clearErreur(id) {
    var champ = document.getElementById(id);
    var zoneErreur = document.getElementById('err_' + id);

    if (champ) {
        champ.style.borderColor = '';
    }

    if (zoneErreur) {
        zoneErreur.innerHTML = '';
    }
}

function validerRapport() {
    var ok = true;

    clearErreur('contenu_rapport');
    clearErreur('date_rapport');
    clearErreur('id_activite');
    clearErreur('id_educateur');

    var contenu = document.getElementById('contenu_rapport').value.replace(/^\s+|\s+$/g, '');
    var date = document.getElementById('date_rapport').value.replace(/^\s+|\s+$/g, '');
    var idActivite = document.getElementById('id_activite').value.replace(/^\s+|\s+$/g, '');
    var idEducateur = document.getElementById('id_educateur').value.replace(/^\s+|\s+$/g, '');

    if (contenu === '') {
        setErreur('contenu_rapport', 'Le contenu est obligatoire.');
        ok = false;
    }

    if (date === '') {
        setErreur('date_rapport', 'La date est obligatoire.');
        ok = false;
    } else {
        var regexDate = /^\d{4}-\d{2}-\d{2}$/;
        if (!regexDate.test(date)) {
            setErreur('date_rapport', 'Format attendu : AAAA-MM-JJ.');
            ok = false;
        }
    }

    if (idActivite === '') {
        setErreur('id_activite', "Veuillez choisir une activité.");
        ok = false;
    }

    if (idEducateur !== '') {
        var regexNombre = /^[0-9]+$/;
        if (!regexNombre.test(idEducateur)) {
            setErreur('id_educateur', "L'identifiant de l'éducateur doit être numérique.");
            ok = false;
        }
    }

    return ok;
}
</script>
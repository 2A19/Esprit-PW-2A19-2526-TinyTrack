<?php
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once '../../controller/ActiviteController.php';
require_once '../../model/Activite.php';

$errors = array();
$old = array();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $nom = isset($_POST['nom_activite']) ? trim($_POST['nom_activite']) : '';
    $desc = isset($_POST['description']) ? trim($_POST['description']) : '';
    $date = isset($_POST['date_activite']) ? trim($_POST['date_activite']) : '';
    $heure = isset($_POST['heure_activite']) ? trim($_POST['heure_activite']) : '';
    $educateur = isset($_POST['id_educateur']) ? trim($_POST['id_educateur']) : '';

    if ($nom === '') {
        $errors[] = 'Le nom est obligatoire.';
    }

    if ($date === '') {
        $errors[] = 'La date est obligatoire.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        $errors[] = 'Le format de la date doit être AAAA-MM-JJ.';
    }

    if ($heure === '') {
        $errors[] = "L'heure est obligatoire.";
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $heure)) {
        $errors[] = "Le format de l'heure doit être HH:MM.";
    }

    if ($date !== '' && $heure !== '' && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) && preg_match('/^\d{2}:\d{2}$/', $heure)) {
        date_default_timezone_set('Africa/Tunis');

        $timestampActivite = strtotime($date . ' ' . $heure);
        $timestampActuel = time();

        if ($timestampActivite === false) {
            $errors[] = "La date ou l'heure de l'activité est invalide.";
        } elseif ($timestampActivite <= $timestampActuel) {
            $errors[] = "La date et l'heure de l'activité doivent être supérieures à la date actuelle.";
        }
    }

    if ($educateur !== '' && !ctype_digit($educateur)) {
        $errors[] = "L'identifiant de l'éducateur doit être numérique.";
    }

    if (empty($errors)) {
        $activite = new Activite(
            null,
            $nom,
            $desc,
            $date,
            $heure,
            $educateur !== '' ? (int)$educateur : null
        );

        $controller = new ActiviteController();
        $controller->addActivite($activite);

        header('Location: /ProjetRapport/tinytrack/view/backoffice/listActivites.php?success=add');
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
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-plus-circle text-success"></i> Ajouter Activité
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="/ProjetRapport/tinytrack/view/backoffice/listActivites.php">Activités</a>
                        </li>
                        <li class="breadcrumb-item active">Ajouter</li>
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
                    <div class="card card-success">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-paint-brush"></i> Nouvelle activité
                            </h3>
                        </div>

                        <form method="POST" action="" onsubmit="return validerActivite();">
                            <div class="card-body">

                                <div class="form-group">
                                    <label for="nom_activite">
                                        Nom <span class="text-danger">*</span>
                                    </label>
                                    <input
                                        type="text"
                                        name="nom_activite"
                                        id="nom_activite"
                                        class="form-control"
                                        placeholder="Ex: Peinture libre"
                                        value="<?php echo htmlspecialchars(isset($old['nom_activite']) ? $old['nom_activite'] : ''); ?>"
                                    >
                                    <small id="err_nom_activite" class="text-danger"></small>
                                </div>

                                <div class="form-group">
                                    <label for="description">Description</label>
                                    <textarea
                                        name="description"
                                        id="description"
                                        class="form-control"
                                        rows="3"
                                        placeholder="Description de l'activité"
                                    ><?php echo htmlspecialchars(isset($old['description']) ? $old['description'] : ''); ?></textarea>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="date_activite">
                                                Date <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                name="date_activite"
                                                id="date_activite"
                                                class="form-control"
                                                placeholder="AAAA-MM-JJ"
                                                value="<?php echo htmlspecialchars(isset($old['date_activite']) ? $old['date_activite'] : ''); ?>"
                                            >
                                            <small id="err_date_activite" class="text-danger"></small>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="heure_activite">
                                                Heure <span class="text-danger">*</span>
                                            </label>
                                            <input
                                                type="text"
                                                name="heure_activite"
                                                id="heure_activite"
                                                class="form-control"
                                                placeholder="HH:MM"
                                                value="<?php echo htmlspecialchars(isset($old['heure_activite']) ? $old['heure_activite'] : ''); ?>"
                                            >
                                            <small id="err_heure_activite" class="text-danger"></small>
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
                                                value="<?php echo htmlspecialchars(isset($old['id_educateur']) ? $old['id_educateur'] : ''); ?>"
                                            >
                                            <small id="err_id_educateur" class="text-danger"></small>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="card-footer">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-save"></i> Enregistrer
                                </button>

                                <a href="/ProjetRapport/tinytrack/view/backoffice/listActivites.php" class="btn btn-default float-right">
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

function validerActivite() {
    var ok = true;

    clearErreur('nom_activite');
    clearErreur('date_activite');
    clearErreur('heure_activite');
    clearErreur('id_educateur');

    var nom = document.getElementById('nom_activite').value.replace(/^\s+|\s+$/g, '');
    var date = document.getElementById('date_activite').value.replace(/^\s+|\s+$/g, '');
    var heure = document.getElementById('heure_activite').value.replace(/^\s+|\s+$/g, '');
    var educateur = document.getElementById('id_educateur').value.replace(/^\s+|\s+$/g, '');

    if (nom === '') {
        setErreur('nom_activite', 'Le nom est obligatoire.');
        ok = false;
    }

    if (date === '') {
        setErreur('date_activite', 'La date est obligatoire.');
        ok = false;
    } else {
        var regexDate = /^\d{4}-\d{2}-\d{2}$/;
        if (!regexDate.test(date)) {
            setErreur('date_activite', 'Format attendu : AAAA-MM-JJ.');
            ok = false;
        }
    }

    if (heure === '') {
        setErreur('heure_activite', "L'heure est obligatoire.");
        ok = false;
    } else {
        var regexHeure = /^\d{2}:\d{2}$/;
        if (!regexHeure.test(heure)) {
            setErreur('heure_activite', 'Format attendu : HH:MM.');
            ok = false;
        }
    }

    if (educateur !== '') {
        var regexNombre = /^[0-9]+$/;
        if (!regexNombre.test(educateur)) {
            setErreur('id_educateur', "L'identifiant de l'éducateur doit être numérique.");
            ok = false;
        }
    }

    return ok;
}
</script>

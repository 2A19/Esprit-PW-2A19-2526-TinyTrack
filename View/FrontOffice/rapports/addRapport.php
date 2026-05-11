<?php
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once '../../controller/RapportController.php';
require_once '../../controller/ActiviteController.php';
require_once '../../model/Rapport.php';

$errors = [];
$old = [];

$activiteController = new ActiviteController();
$activites = $activiteController->listActivites()->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;

    $contenu = trim($_POST['contenu_rapport'] ?? '');
    $date = trim($_POST['date_rapport'] ?? '');
    $id_enfant = trim($_POST['id_enfant'] ?? '');
    $id_activite = trim($_POST['id_activite'] ?? '');
    $id_educateur = trim($_POST['id_educateur'] ?? '');

    if ($contenu === '') {
        $errors[] = 'Le contenu est obligatoire.';
    } elseif (strlen($contenu) < 5) {
        $errors[] = 'Le contenu est trop court (min 5 caractères).';
    }

    if ($date === '') {
        $errors[] = 'La date est obligatoire.';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $date)) {
        $errors[] = 'Le format de la date doit être AAAA-MM-JJ HH:MM.';
    } else {
        date_default_timezone_set('Africa/Tunis');

        $dateSaisie = DateTime::createFromFormat('Y-m-d H:i', $date);
        $dateActuelle = new DateTime();

        if (!$dateSaisie) {
            $errors[] = 'La date saisie est invalide.';
        } elseif ($dateSaisie < $dateActuelle) {
            $errors[] = 'La date du rapport ne peut pas être dans le passé.';
        } else {
            $date = $dateSaisie->format('Y-m-d H:i:s');
        }
    }

    if ($id_enfant === '') {
        $errors[] = "L'identifiant de l'enfant est obligatoire.";
    } elseif (!ctype_digit($id_enfant)) {
        $errors[] = "L'identifiant de l'enfant doit être numérique.";
    }

    if ($id_activite === '') {
        $errors[] = "L'activité est obligatoire.";
    } elseif (!ctype_digit($id_activite)) {
        $errors[] = "L'activité sélectionnée est invalide.";
    } else {
        $activiteExiste = false;
        foreach ($activites as $a) {
            if ((string)$a['id_activite'] === (string)$id_activite) {
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
            null,
            $contenu,
            $date,
            (int)$id_enfant,
            (int)$id_activite,
            $id_educateur !== '' ? (int)$id_educateur : null
        );

        $controller = new RapportController();
        $controller->addRapport($rapport);

        header('Location: /ProjetRapport/tinytrack/view/frontoffice/listRapportsParent.php?success=add');
        exit;
    }
}

include 'template/header.php';
?>

<div class="container py-5">
  <div class="text-center mb-4">
    <h2 class="section-title">
      <i class="fas fa-plus-circle"></i> Ajouter un Rapport
    </h2>
  </div>

  <?php if (!empty($errors)): ?>
    <div class="row justify-content-center">
      <div class="col-md-8">
        <div class="alert alert-danger" style="border-radius:16px;border:none;">
          <ul class="mb-0">
            <?php foreach ($errors as $e): ?>
              <li><?= htmlspecialchars($e) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <div class="row justify-content-center">
    <div class="col-md-8">
      <div class="card-kider p-4">
        <form method="POST" action="" onsubmit="return validerRapport();">
          <div class="mb-3">
            <label style="font-weight:700;">
              Contenu du rapport <span style="color:#EF5350;">*</span>
            </label>
            <textarea
              name="contenu_rapport"
              id="contenu_rapport"
              class="form-control"
              rows="4"
              style="border-radius:12px;border:2px solid #E8E8E8;"
              placeholder="Décrivez la journée de l'enfant..."
            ><?= htmlspecialchars($old['contenu_rapport'] ?? '') ?></textarea>
            <div class="invalid-feedback" id="err_contenu_rapport" style="display:none;font-size:0.8rem;color:#EF5350;font-weight:700;"></div>
          </div>

          <div class="row">
            <div class="col-md-3 mb-3">
              <label style="font-weight:700;">
                Date <span style="color:#EF5350;">*</span>
              </label>
              <input
                type="text"
                name="date_rapport"
                id="date_rapport"
                class="form-control"
                style="border-radius:12px;border:2px solid #E8E8E8;"
                placeholder="AAAA-MM-JJ HH:MM"
                value="<?= htmlspecialchars($old['date_rapport'] ?? '') ?>"
              >
              <div class="invalid-feedback" id="err_date_rapport" style="display:none;font-size:0.8rem;color:#EF5350;font-weight:700;"></div>
            </div>

            <div class="col-md-3 mb-3">
              <label style="font-weight:700;">
                ID Enfant <span style="color:#EF5350;">*</span>
              </label>
              <input
                type="text"
                name="id_enfant"
                id="id_enfant"
                class="form-control"
                style="border-radius:12px;border:2px solid #E8E8E8;"
                placeholder="Ex: 1"
                value="<?= htmlspecialchars($old['id_enfant'] ?? '') ?>"
              >
              <div class="invalid-feedback" id="err_id_enfant" style="display:none;font-size:0.8rem;color:#EF5350;font-weight:700;"></div>
            </div>

            <div class="col-md-3 mb-3">
              <label style="font-weight:700;">
                Activité <span style="color:#EF5350;">*</span>
              </label>
              <select
                name="id_activite"
                id="id_activite"
                class="form-control"
                style="border-radius:12px;border:2px solid #E8E8E8;"
              >
                <option value="">-- Choisir une activité --</option>
                <?php foreach ($activites as $a): ?>
                  <option
                    value="<?= $a['id_activite'] ?>"
                    <?= ((string)($old['id_activite'] ?? ($_GET['id_activite'] ?? '')) === (string)$a['id_activite']) ? 'selected' : '' ?>
                  >
                    <?= htmlspecialchars($a['nom_activite']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <div class="invalid-feedback" id="err_id_activite" style="display:none;font-size:0.8rem;color:#EF5350;font-weight:700;"></div>
            </div>

            <div class="col-md-3 mb-3">
              <label style="font-weight:700;">Éducateur ID</label>
              <input
                type="text"
                name="id_educateur"
                id="id_educateur"
                class="form-control"
                style="border-radius:12px;border:2px solid #E8E8E8;"
                placeholder="Ex: 2"
                value="<?= htmlspecialchars($old['id_educateur'] ?? ($_GET['id_educateur'] ?? '')) ?>"
              >
              <div class="invalid-feedback" id="err_id_educateur" style="display:none;font-size:0.8rem;color:#EF5350;font-weight:700;"></div>
            </div>
          </div>

          <div class="d-flex justify-content-between mt-3">
            <a href="/ProjetRapport/tinytrack/view/frontoffice/listRapportsParent.php" class="btn btn-default" style="border-radius:25px;">
              <i class="fas fa-arrow-left"></i> Retour
            </a>

            <button type="submit" class="btn btn-success" style="border-radius:25px;padding:0.5rem 2rem;">
              <i class="fas fa-save"></i> Enregistrer
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>

<?php include 'template/footer.php'; ?>

<script>
function showE(id, msg) {
  var f = document.getElementById(id);
  var e = document.getElementById('err_' + id);

  if (f) {
    f.classList.add('is-invalid');
  }

  if (e) {
    e.textContent = msg;
    e.style.display = 'block';
  }
}

function clearE(id) {
  var f = document.getElementById(id);
  var e = document.getElementById('err_' + id);

  if (f) {
    f.classList.remove('is-invalid');
  }

  if (e) {
    e.style.display = 'none';
  }
}

function validerRapport() {
  var ok = true;

  ['contenu_rapport', 'date_rapport', 'id_enfant', 'id_activite', 'id_educateur'].forEach(clearE);

  var c = document.getElementById('contenu_rapport').value.trim();
  var d = document.getElementById('date_rapport').value.trim();
  var enf = document.getElementById('id_enfant').value.trim();
  var a = document.getElementById('id_activite').value.trim();
  var e = document.getElementById('id_educateur').value.trim();

  if (!c) {
    showE('contenu_rapport', 'Obligatoire');
    ok = false;
  } else if (c.length < 5) {
    showE('contenu_rapport', 'Min 5 caractères');
    ok = false;
  }

  if (!d) {
    showE('date_rapport', 'Obligatoire');
    ok = false;
  } else if (!/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(d)) {
    showE('date_rapport', 'Format: AAAA-MM-JJ HH:MM');
    ok = false;
  }

  if (!enf) {
    showE('id_enfant', 'Obligatoire');
    ok = false;
  } else if (!/^\d+$/.test(enf)) {
    showE('id_enfant', 'Doit être un nombre');
    ok = false;
  }

  if (!a) {
    showE('id_activite', 'Veuillez choisir une activité');
    ok = false;
  }

  if (e && !/^\d+$/.test(e)) {
    showE('id_educateur', 'Doit être un nombre');
    ok = false;
  }

  return ok;
}
</script>

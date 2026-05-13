<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
require_once __DIR__ . '/../../../Controller/ReclamationController.php';
require_once __DIR__ . '/../../../Controller/ReponseController.php';

$reclamationController = new ReclamationController();
$reponseController     = new ReponseController();
$errors   = [];
$showModal = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action_reply'])) {
        $result = $reponseController->addReponse($_POST['id_reclamation'], $_POST['message']);
        if ($result === true) { header('Location: reclamationBack.php'); exit; }
        else { $errors = $result; $showModal = 'reply'; }
    }
    if (isset($_POST['action_edit_rec'])) {
        $result = $reclamationController->updateReclamation($_POST['id'], $_POST);
        if ($result === true) { header('Location: reclamationBack.php'); exit; }
        else { $errors = $result; $showModal = 'edit_rec'; }
    }
}

if (isset($_GET['delete_id'])) {
    $reclamationController->deleteReclamation((int)$_GET['delete_id']);
    header('Location: reclamationBack.php'); exit;
}
if (isset($_GET['id_manage'])) $showModal = 'reply';
if (isset($_GET['id_edit']))   $showModal = 'edit_rec';

// Educateur : ne voit que les reclamations de SES parents (ceux dont un enfant
// est dans son groupe). Admin : voit tout.
if (($_SESSION['user_role'] ?? '') === 'educateur') {
    $eduId        = (int) $_SESSION['user_id'];
    $reclamations = $reclamationController->listReclamationsByEducateur($eduId);
    $stats        = $reclamationController->getStatsByEducateur($eduId);
} else {
    $reclamations = $reclamationController->listReclamations();
    $stats        = $reclamationController->getStats();
}
$topSubjects  = $reclamationController->getTopSubjects(6);
$tauxRes      = $reclamationController->getResolutionRate();

$managedId  = $_GET['id_manage'] ?? $_POST['id_reclamation'] ?? null;
$managedRec = $managedId ? $reclamationController->getReclamationById($managedId) : null;
$managedReps= $managedId ? $reponseController->getReponsesByReclamation($managedId) : [];

$editId  = $_GET['id_edit'] ?? $_POST['id'] ?? null;
$editRec = $editId ? $reclamationController->getReclamationById($editId) : null;

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">

  <!-- ===== EN-TÊTE ===== -->
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-exclamation-circle text-danger mr-2"></i>Réclamations
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="#">Dashboard</a></li>
            <li class="breadcrumb-item active">Réclamations</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- stat -->
      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner"><h3><?= $stats['total'] ?></h3><p>Total réclamations</p></div>
            <div class="icon"><i class="fas fa-clipboard-list"></i></div>
            <a href="#tableRec" class="small-box-footer">Voir liste <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner"><h3><?= $stats['pending'] ?></h3><p>En attente</p></div>
            <div class="icon"><i class="fas fa-clock"></i></div>
            <a href="#tableRec" class="small-box-footer">Voir liste <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner"><h3><?= $stats['solved'] ?></h3><p>Traitées</p></div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
            <a href="#tableRec" class="small-box-footer">Voir liste <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner"><h3><?= $tauxRes ?>%</h3><p>Taux de résolution</p></div>
            <div class="icon"><i class="fas fa-percent"></i></div>
            <a href="#stats" class="small-box-footer">Voir stats <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- stat -->
      <div class="row" id="stats">
        <!-- Donut statut -->
        <div class="col-md-5">
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Répartition par statut</h3>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center" style="min-height:280px;">
              <div style="position:relative;width:260px;height:260px;">
                <canvas id="chartStatut"></canvas>
                <div id="chartCenter" style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                  <div style="font-family:'Fredoka One',cursive;font-size:2rem;color:#2D3436;line-height:1;"><?= $stats['total'] ?></div>
                  <div style="font-size:0.75rem;color:#888;font-weight:700;">TOTAL</div>
                </div>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-around" style="background:rgba(0,0,0,0.03);">
              <span><i class="fas fa-circle text-warning mr-1"></i><strong><?= $stats['pending'] ?></strong> En attente</span>
              <span><i class="fas fa-circle text-info mr-1"></i><strong><?= $stats['solved'] ?></strong> Traitées</span>
            </div>
          </div>
        </div>

        <!-- Barre sujets -->
        <div class="col-md-7">
          <div class="card card-success">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-chart-bar mr-2"></i>Top sujets récurrents</h3>
            </div>
            <div class="card-body" style="min-height:280px;">
              <canvas id="chartSujets" style="max-height:260px;"></canvas>
            </div>
          </div>
        </div>
      </div>

      <!-- recherche | tri | pdf -->
      <div class="card card-success" id="tableRec">
        <div class="card-header">
          <div class="row align-items-center">
            <!-- Recherche -->
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher un client, sujet..." style="border-radius:10px 0 0 10px;">
                <div class="input-group-append">
                  <button class="btn btn-success" id="searchBtn" style="border-radius:0 10px 10px 0;">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>
            <!-- Titre -->
            <div class="col-md-3 text-center">
              <h3 class="card-title mb-0">Tous les tickets</h3>
            </div>
            <!-- Filtre statut + export -->
            <div class="col-md-5 text-right d-flex justify-content-end align-items-center flex-wrap gap-2" style="gap:8px;">
              <select id="filterStatut" class="form-control form-control-sm" style="width:140px;border-radius:10px;">
                <option value="">Tous statuts</option>
                <option value="En attente">En attente</option>
                <option value="Traité">Traité</option>
              </select>
              <button id="btnPDF" class="btn btn-sm btn-danger ml-2" title="Exporter en PDF">
                <i class="fas fa-file-pdf mr-1"></i>PDF
              </button>
            </div>
          </div>
        </div>

        <div class="card-body table-responsive p-0">
          <table id="dtRec" class="table table-bordered table-hover table-striped mb-0">
            <thead>
              <tr>
                <th>Client</th>
                <th>Email</th>
                <th>Sujet</th>
                <th style="width:120px;">Statut</th>
                <th style="width:110px;">Sentiment</th>
                <th style="width:160px;">Date</th>
                <th style="width:160px;text-align:center;">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($reclamations as $rec): ?>
              <tr>
                <td><i class="fas fa-user-circle text-success mr-1"></i><?= htmlspecialchars($rec->getNomClient()) ?></td>
                <td><small><?= htmlspecialchars($rec->getEmail()) ?></small></td>
                <td><?= htmlspecialchars($rec->getSujet()) ?></td>
                <td>
                  <?php if ($rec->getStatut() === 'En attente'): ?>
                    <span class="badge badge-warning" style="font-size:0.8rem;padding:0.4rem 0.8rem;">
                      <i class="fas fa-clock mr-1"></i>En attente
                    </span>
                  <?php else: ?>
                    <span class="badge badge-info" style="font-size:0.8rem;padding:0.4rem 0.8rem;">
                      <i class="fas fa-check mr-1"></i>Traité
                    </span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php
                    $sent = $rec->getSentiment();
                    if ($sent === 'positif'):
                  ?>
                    <span class="badge" style="background:#d4edda;color:#155724;font-size:0.78rem;padding:0.35rem 0.65rem;border-radius:20px;">
                      😊 Positif
                    </span>
                  <?php elseif ($sent === 'negatif'): ?>
                    <span class="badge" style="background:#f8d7da;color:#721c24;font-size:0.78rem;padding:0.35rem 0.65rem;border-radius:20px;">
                      😠 Négatif
                    </span>
                  <?php elseif ($sent === 'neutre'): ?>
                    <span class="badge" style="background:#fff3cd;color:#856404;font-size:0.78rem;padding:0.35rem 0.65rem;border-radius:20px;">
                      😐 Neutre
                    </span>
                  <?php else: ?>
                    <span class="badge badge-secondary" style="font-size:0.78rem;padding:0.35rem 0.65rem;border-radius:20px;opacity:0.6;">
                      — N/A
                    </span>
                  <?php endif; ?>
                </td>
                <td><small><i class="fas fa-calendar-alt mr-1 text-muted"></i><?= htmlspecialchars($rec->getDateCreation()) ?></small></td>
                <td class="text-center">
                  <a href="reclamationBack.php?id_manage=<?= $rec->getId() ?>" class="btn btn-sm btn-info" title="Répondre">
                    <i class="fas fa-reply"></i>
                  </a>
                  <button class="btn btn-sm btn-ia-row"
                          title="Générer une réponse avec l'IA"
                          style="background:linear-gradient(135deg,#6a11cb,#2575fc);color:#fff;border:none;"
                          data-id="<?= $rec->getId() ?>"
                          data-client="<?= htmlspecialchars($rec->getNomClient(), ENT_QUOTES) ?>"
                          data-sujet="<?= htmlspecialchars($rec->getSujet(), ENT_QUOTES) ?>"
                          data-description="<?= htmlspecialchars($rec->getDescription(), ENT_QUOTES) ?>">
                    <i class="fas fa-magic"></i>
                  </button>
                  <a href="reclamationBack.php?id_edit=<?= $rec->getId() ?>" class="btn btn-sm btn-warning" title="Modifier">
                    <i class="fas fa-edit"></i>
                  </a>
                  <a href="reclamationBack.php?delete_id=<?= $rec->getId() ?>" class="btn btn-sm btn-danger" title="Supprimer"
                     onclick="return confirm('Supprimer définitivement cette réclamation ?')">
                    <i class="fas fa-trash"></i>
                  </a>
                </td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<!-- modal IA — fenêtre de génération et d'envoi de réponse automatique -->
<div id="modalIA" style="display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.55);z-index:9999;">
  <div style="background:#fff;border-radius:20px;overflow:hidden;max-width:620px;width:90%;margin:5vh auto;box-shadow:0 20px 60px rgba(0,0,0,0.3);">
    <div style="background:linear-gradient(135deg,#6a11cb,#2575fc);padding:1.2rem 1.5rem;display:flex;justify-content:space-between;align-items:center;">
      <h5 style="margin:0;color:#fff;font-family:'Fredoka One',cursive;font-size:1.2rem;">
        <i class="fas fa-magic mr-2"></i>Réponse générée par IA
      </h5>
      <span onclick="fermerModalIA()" style="color:#fff;font-size:1.6rem;cursor:pointer;line-height:1;">&times;</span>
    </div>
    <div style="padding:1.5rem;">
      <!-- Infos réclamation -->
      <div style="background:#f4f1ff;border-radius:12px;padding:0.9rem 1rem;margin-bottom:1rem;border-left:4px solid #6a11cb;">
        <small style="color:#6a11cb;font-weight:700;display:block;margin-bottom:4px;"><i class="fas fa-user mr-1"></i><span id="iaClient"></span></small>
        <strong id="iaSujet" style="color:#2D3436;"></strong>
      </div>
      <!-- Spinner -->
      <div id="iaLoader" style="text-align:center;padding:1.5rem;display:none;">
        <i class="fas fa-circle-notch fa-spin fa-2x" style="color:#6a11cb;"></i>
        <p style="color:#888;margin-top:0.5rem;font-size:0.9rem;">Génération en cours...</p>
      </div>
      <!-- Réponse générée -->
      <div id="iaResult" style="display:none;">
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.4rem;">
          <label style="font-weight:700;color:#2D3436;margin:0;"><i class="fas fa-pen mr-1"></i>Réponse suggérée <small class="text-muted">(modifiable)</small></label>
          <button id="btnChangerMsg" type="button"
                  style="background:#f0ebff;color:#6a11cb;border:2px solid #c9b8ff;border-radius:20px;font-size:0.8rem;font-weight:700;padding:0.3rem 1rem;cursor:pointer;">
            <i class="fas fa-sync-alt mr-1"></i>Changer message
          </button>
        </div>
        <textarea id="iaTexte" rows="6" style="width:100%;border:2px solid #e0d4ff;border-radius:12px;padding:0.8rem;font-size:0.92rem;resize:vertical;outline:none;"></textarea>
        <form method="POST" style="margin-top:0.8rem;">
          <input type="hidden" name="action_reply" value="1">
          <input type="hidden" name="id_reclamation" id="iaIdRec" value="">
          <textarea name="message" id="iaMessageHidden" style="display:none;"></textarea>
          <button type="submit" class="btn btn-block" onclick="document.getElementById('iaMessageHidden').value=document.getElementById('iaTexte').value;"
                  style="background:linear-gradient(135deg,#6a11cb,#2575fc);color:#fff;border:none;border-radius:12px;font-weight:700;padding:0.7rem;">
            <i class="fas fa-paper-plane mr-2"></i>Envoyer cette réponse
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<!-- ===== MODAL : RÉPONDRE ===== -->
<?php if ($showModal === 'reply' && $managedRec): ?>
<div class="modal fade show" style="display:block;background:rgba(0,0,0,0.55);">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px;overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(135deg,#5B9BD5,#90CAF9);">
        <h5 class="modal-title text-white" style="font-family:'Fredoka One',cursive;">
          <i class="fas fa-reply mr-2"></i>Répondre — <?= htmlspecialchars($managedRec->getSujet()) ?>
        </h5>
        <a href="reclamationBack.php" class="text-white" style="font-size:1.5rem;text-decoration:none;line-height:1;">&times;</a>
      </div>
      <div class="modal-body">
        <!-- Infos réclamation -->
        <div class="row mb-3">
          <div class="col-md-6">
            <small class="text-muted d-block"><i class="fas fa-user mr-1"></i>Client</small>
            <strong><?= htmlspecialchars($managedRec->getNomClient()) ?></strong>
          </div>
          <div class="col-md-6">
            <small class="text-muted d-block"><i class="fas fa-envelope mr-1"></i>Email</small>
            <strong><?= htmlspecialchars($managedRec->getEmail()) ?></strong>
          </div>
        </div>
        <div class="alert" style="background:#f0f7ff;border-left:4px solid #5B9BD5;border-radius:10px;">
          <small class="text-muted d-block mb-1"><i class="fas fa-align-left mr-1"></i>Description</small>
          <?= nl2br(htmlspecialchars($managedRec->getDescription())) ?>
        </div>
        <!-- Réponses existantes -->
        <?php if (!empty($managedReps)): ?>
        <div class="mb-3">
          <small class="text-muted" style="font-weight:700;"><i class="fas fa-comments mr-1"></i>Réponses précédentes (<?= count($managedReps) ?>)</small>
          <?php foreach ($managedReps as $rep): ?>
          <div class="mt-2 p-2" style="background:#f8f9fa;border-radius:10px;border-left:3px solid #4CAF50;font-size:0.88rem;">
            <small class="text-muted"><?= htmlspecialchars($rep->getAuteur()) ?> — <?= htmlspecialchars($rep->getDateReponse()) ?></small>
            <div><?= nl2br(htmlspecialchars($rep->getMessage())) ?></div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <!-- Formulaire réponse -->
        <form method="POST">
          <input type="hidden" name="action_reply" value="1">
          <input type="hidden" name="id_reclamation" value="<?= $managedRec->getId() ?>">
          <div class="form-group mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
              <label class="mb-0"><i class="fas fa-pen mr-1"></i>Votre réponse</label>
              <button type="button" id="btnGenererIA" class="btn btn-sm"
                      style="background:linear-gradient(135deg,#6a11cb,#2575fc);color:#fff;border-radius:20px;font-size:0.8rem;padding:0.3rem 0.9rem;border:none;"
                      data-sujet="<?= htmlspecialchars($managedRec->getSujet()) ?>"
                      data-description="<?= htmlspecialchars($managedRec->getDescription()) ?>">
                <i class="fas fa-magic mr-1"></i>Générer avec IA
              </button>
            </div>
            <textarea name="message" id="replyMessage" class="form-control<?= isset($errors['message']) ? ' is-invalid' : '' ?>" rows="4"
                      placeholder="Rédigez votre réponse..."><?= htmlspecialchars($_POST['message'] ?? '') ?></textarea>
            <div id="iaSpinner" class="text-center mt-2" style="display:none;">
              <small class="text-muted"><i class="fas fa-circle-notch fa-spin mr-1"></i>Génération en cours...</small>
            </div>
            <?php if (isset($errors['message'])): ?>
              <div class="invalid-feedback d-block"><?= $errors['message'] ?></div>
            <?php endif; ?>
          </div>
          <button type="submit" class="btn btn-info btn-block" style="border-radius:12px;font-weight:700;">
            <i class="fas fa-paper-plane mr-2"></i>Envoyer la réponse
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ===== MODAL : MODIFIER ===== -->
<?php if ($showModal === 'edit_rec' && $editRec): ?>
<div class="modal fade show" style="display:block;background:rgba(0,0,0,0.55);">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:20px;overflow:hidden;">
      <div class="modal-header" style="background:linear-gradient(135deg,#FFA726,#FFCC80);">
        <h5 class="modal-title" style="font-family:'Fredoka One',cursive;">
          <i class="fas fa-edit mr-2"></i>Modifier réclamation #<?= $editRec->getId() ?>
        </h5>
        <a href="reclamationBack.php" style="font-size:1.5rem;text-decoration:none;line-height:1;">&times;</a>
      </div>
      <div class="modal-body">
        <form method="POST">
          <input type="hidden" name="action_edit_rec" value="1">
          <input type="hidden" name="id" value="<?= $editRec->getId() ?>">
          <input type="hidden" name="email" value="<?= htmlspecialchars($editRec->getEmail()) ?>">
          <div class="form-group">
            <label><i class="fas fa-user mr-1"></i>Client</label>
            <input type="text" name="nom_client" class="form-control<?= isset($errors['nom_client']) ? ' is-invalid' : '' ?>"
                   value="<?= htmlspecialchars($_POST['nom_client'] ?? $editRec->getNomClient()) ?>">
            <?php if (isset($errors['nom_client'])): ?><div class="invalid-feedback d-block"><?= $errors['nom_client'] ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label><i class="fas fa-tag mr-1"></i>Sujet</label>
            <input type="text" name="sujet" class="form-control<?= isset($errors['sujet']) ? ' is-invalid' : '' ?>"
                   value="<?= htmlspecialchars($_POST['sujet'] ?? $editRec->getSujet()) ?>">
            <?php if (isset($errors['sujet'])): ?><div class="invalid-feedback d-block"><?= $errors['sujet'] ?></div><?php endif; ?>
          </div>
          <div class="form-group">
            <label><i class="fas fa-align-left mr-1"></i>Description</label>
            <textarea name="description" class="form-control<?= isset($errors['description']) ? ' is-invalid' : '' ?>" rows="4"><?= htmlspecialchars($_POST['description'] ?? $editRec->getDescription()) ?></textarea>
            <?php if (isset($errors['description'])): ?><div class="invalid-feedback d-block"><?= $errors['description'] ?></div><?php endif; ?>
          </div>
          <button type="submit" class="btn btn-warning btn-block" style="border-radius:12px;font-weight:700;">
            <i class="fas fa-save mr-2"></i>Mettre à jour
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

<?php include 'template/footer.php'; ?>

<script>
$(document).ready(function () {

  // tri
  var table = $('#dtRec').DataTable({
    language: {
      emptyTable:    "Aucune réclamation",
      info:          "_START_ à _END_ sur _TOTAL_ réclamations",
      infoEmpty:     "0 réclamation",
      lengthMenu:    "Afficher _MENU_ lignes",
      zeroRecords:   "Aucun résultat trouvé",
      search:        "Recherche :",
      paginate:      { next: "Suivant", previous: "Précédent" }
    },
    pageLength: 10,
    order: [[5, "desc"]],
    dom: "lrtip",
    buttons: [
      {
        extend:    'pdfHtml5',
        text:      '<i class="fas fa-file-pdf mr-1"></i>PDF',
        className: 'btn btn-sm btn-danger',
        title:     'Liste des Réclamations — TinyTrack',
        exportOptions: { columns: [0,1,2,3,4,5] },
        customize: function(doc) {
          doc.styles.tableHeader.fillColor = '#4CAF50';
          doc.defaultStyle.fontSize = 10;
          doc.styles.title.color = '#2D3436';
        }
      },
    ]
  });

  // pdf
  $('#btnPDF').on('click', function () { table.button('.buttons-pdf').trigger(); });

  // recherche
  $('#searchInput').on('keyup', function () { table.search(this.value).draw(); });
  $('#searchBtn').on('click', function () { table.search($('#searchInput').val()).draw(); });
  $('#filterStatut').on('change', function () { table.column(3).search(this.value).draw(); }); // col 3 = Statut

  // stat
  var ctxStatut = document.getElementById('chartStatut').getContext('2d');
  new Chart(ctxStatut, {
    type: 'doughnut',
    data: {
      labels: ['En attente', 'Traitées'],
      datasets: [{
        data: [<?= $stats['pending'] ?>, <?= $stats['solved'] ?>],
        backgroundColor: ['#FFA726', '#5B9BD5'],
        borderColor:     ['#FF8F00', '#1976D2'],
        borderWidth: 2,
        hoverOffset: 8
      }]
    },
    options: {
      cutout: '70%',
      responsive: true,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(ctx) {
              var total = ctx.dataset.data.reduce((a, b) => a + b, 0);
              var pct = total > 0 ? ((ctx.parsed / total) * 100).toFixed(1) : 0;
              return ' ' + ctx.label + ' : ' + ctx.parsed + ' (' + pct + '%)';
            }
          }
        }
      }
    }
  });

  /* ---- Chart barres top sujets ---- */
  var ctxSujets = document.getElementById('chartSujets').getContext('2d');
  new Chart(ctxSujets, {
    type: 'bar',
    data: {
      labels: [<?php foreach ($topSubjects as $s): echo '"' . addslashes(mb_substr($s['sujet'], 0, 25)) . '", '; endforeach; ?>],
      datasets: [{
        label: 'Nombre de réclamations',
        data:  [<?php foreach ($topSubjects as $s): echo $s['total'] . ', '; endforeach; ?>],
        backgroundColor: [
          'rgba(76,175,80,0.75)','rgba(91,155,213,0.75)','rgba(255,167,38,0.75)',
          'rgba(239,83,80,0.75)','rgba(156,124,219,0.75)','rgba(38,198,218,0.75)'
        ],
        borderColor: [
          '#4CAF50','#5B9BD5','#FFA726','#EF5350','#9C7CDB','#26C6DA'
        ],
        borderWidth: 2,
        borderRadius: 8
      }]
    },
    options: {
      responsive: true,
      indexAxis: 'y',
      plugins: {
        legend: { display: false }
      },
      scales: {
        x: {
          beginAtZero: true,
          ticks: { stepSize: 1, precision: 0 },
          grid: { color: 'rgba(0,0,0,0.05)' }
        },
        y: {
          grid: { display: false }
        }
      }
    }
  });

  // IA — bouton Générer dans le modal répondre (reply modal)
  $('#btnGenererIA').on('click', function () {
    var sujet       = $(this).data('sujet');
    var description = $(this).data('description');
    $('#btnGenererIA').prop('disabled', true);
    $('#iaSpinner').show();
    $('#replyMessage').val('');

    $.ajax({
      url:    '/TinyTrack/api/suggest_response.php',
      method: 'POST',
      data:   { sujet: sujet, description: description },
      success: function (res) {
        if (res.suggestion) {
          $('#replyMessage').val(res.suggestion).focus();
        } else {
          alert('Erreur : ' + (res.error || 'Réponse invalide.'));
        }
      },
      error: function () { alert('Erreur réseau.'); },
      complete: function () {
        $('#btnGenererIA').prop('disabled', false);
        $('#iaSpinner').hide();
      }
    });
  });

});

// ia — stockage du contexte (sujet, description, client) de la réclamation ouverte
var iaContexte = { sujet: '', description: '', client: '' };

// ia — appel AJAX vers suggest_response.php et affichage de la réponse dans le textarea
function genererReponseIA() {
  $('#iaLoader').show();
  $('#iaResult').hide();
  $('#iaTexte').val('');

  $.ajax({
    url:    '/TinyTrack/api/suggest_response.php',
    method: 'POST',
    data:   { sujet: iaContexte.sujet, description: iaContexte.description, nom_client: iaContexte.client },
    success: function (res) {
      $('#iaTexte').val(res.suggestion || 'Erreur de génération.');
    },
    error: function () {
      $('#iaTexte').val('Erreur réseau.');
    },
    complete: function () {
      $('#iaLoader').hide();
      $('#iaResult').show();
    }
  });
}

// ia — délégation de clic sur les boutons ✨ de chaque ligne du tableau
$(document).on('click', '.btn-ia-row', function () {
  iaContexte.id          = $(this).data('id');
  iaContexte.client      = $(this).data('client');
  iaContexte.sujet       = $(this).data('sujet');
  iaContexte.description = $(this).data('description');

  $('#modalIA').show();
  $('#iaClient').text(iaContexte.client);
  $('#iaSujet').text(iaContexte.sujet);
  $('#iaIdRec').val(iaContexte.id);

  genererReponseIA();
});

// ia — bouton "Changer message" : régénère une variante aléatoire sans fermer le modal
$('#btnChangerMsg').on('click', function () {
  genererReponseIA();
});

function fermerModalIA() {
  $('#modalIA').hide();
}
</script>

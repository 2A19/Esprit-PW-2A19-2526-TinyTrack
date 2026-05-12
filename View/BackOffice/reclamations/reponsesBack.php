<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
require_once __DIR__ . '/../../controller/ReclamationController.php';
require_once __DIR__ . '/../../controller/ReponseController.php';

$reclamationController = new ReclamationController();
$reponseController     = new ReponseController();

if (isset($_GET['delete_rep_id'])) {
    $reponseController->deleteReponse((int)$_GET['delete_rep_id']);
    header('Location: reponsesBack.php'); exit;
}

$allRows  = $reponseController->getAllReponses();
$totalRep = $reponseController->getTotalReponses();
$avgRep   = $reponseController->getAvgReponsesParReclamation();
$statsRec = $reclamationController->getStats();
$avecRep  = $statsRec['solved'];
$sansRep  = $statsRec['pending'];

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">

  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2 align-items-center">
        <div class="col-sm-6">
          <h1 class="m-0"><i class="fas fa-reply text-info mr-2"></i>Réponses</h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item"><a href="reclamationBack.php">Réclamations</a></li>
            <li class="breadcrumb-item active">Réponses</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- stat -->
      <div class="row">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner"><h3><?= $totalRep ?></h3><p>Total réponses</p></div>
            <div class="icon"><i class="fas fa-comments"></i></div>
            <a href="#tableRep" class="small-box-footer">Voir liste <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-success">
            <div class="inner"><h3><?= $avgRep ?></h3><p>Moy. réponses / réclamation</p></div>
            <div class="icon"><i class="fas fa-balance-scale"></i></div>
            <a href="#stats" class="small-box-footer">Voir stats <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
        <div class="col-lg-4 col-6">
          <div class="small-box bg-warning">
            <div class="inner"><h3><?= $sansRep ?></h3><p>Réclamations sans réponse</p></div>
            <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            <a href="reclamationBack.php" class="small-box-footer">Voir réclamations <i class="fas fa-arrow-circle-right"></i></a>
          </div>
        </div>
      </div>

      <!-- stat -->
      <div class="row" id="stats">
        <div class="col-md-5">
          <div class="card card-info">
            <div class="card-header">
              <h3 class="card-title"><i class="fas fa-chart-pie mr-2"></i>Réclamations répondues vs en attente</h3>
            </div>
            <div class="card-body d-flex justify-content-center align-items-center" style="min-height:280px;">
              <div style="position:relative;width:260px;height:260px;">
                <canvas id="chartCoverage"></canvas>
                <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;pointer-events:none;">
                  <div style="font-family:'Fredoka One',cursive;font-size:2rem;color:#2D3436;line-height:1;"><?= $statsRec['total'] ?></div>
                  <div style="font-size:0.75rem;color:#888;font-weight:700;">TOTAL</div>
                </div>
              </div>
            </div>
            <div class="card-footer d-flex justify-content-around" style="background:rgba(0,0,0,0.03);">
              <span><i class="fas fa-circle text-info mr-1"></i><strong><?= $avecRep ?></strong> Répondues</span>
              <span><i class="fas fa-circle text-warning mr-1"></i><strong><?= $sansRep ?></strong> En attente</span>
            </div>
          </div>
        </div>
      </div>

      <!-- recherche | tri | pdf -->
      <div class="card card-info" id="tableRep">
        <div class="card-header">
          <div class="row align-items-center">
            <!-- recherche -->
            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchRep" class="form-control" placeholder="Rechercher client, sujet..." style="border-radius:10px 0 0 10px;">
                <div class="input-group-append">
                  <button class="btn btn-info" id="searchRepBtn" style="border-radius:0 10px 10px 0;">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>
            <div class="col-md-3 text-center">
              <h3 class="card-title mb-0">Journal des réponses</h3>
            </div>
            <!-- pdf -->
            <div class="col-md-5 text-right d-flex justify-content-end align-items-center" style="gap:8px;">
              <button id="btnPDF" class="btn btn-sm btn-danger ml-1" title="Exporter en PDF">
                <i class="fas fa-file-pdf mr-1"></i>PDF
              </button>
            </div>
          </div>
        </div>

        <div class="card-body table-responsive p-0">
          <!-- tri -->
          <table id="dtRep" class="table table-bordered table-hover table-striped mb-0">
            <thead>
              <tr>
                <th>Client</th>
                <th>Sujet réclamation</th>
                <th>Message</th>
                <th>Auteur</th>
                <th style="width:160px;">Date</th>
                <th style="width:70px;text-align:center;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php if (empty($allRows)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">
                  <i class="fas fa-inbox fa-2x mb-2 d-block"></i>Aucune réponse enregistrée
                </td></tr>
              <?php else: ?>
                <?php foreach ($allRows as $row): ?>
                <tr>
                  <td><i class="fas fa-user-circle text-info mr-1"></i><?= htmlspecialchars($row['nom_client']) ?></td>
                  <td><?= htmlspecialchars($row['sujet']) ?></td>
                  <td>
                    <span title="<?= htmlspecialchars($row['message']) ?>">
                      <?= htmlspecialchars(mb_substr($row['message'], 0, 70)) ?><?= mb_strlen($row['message']) > 70 ? '…' : '' ?>
                    </span>
                  </td>
                  <td>
                    <span class="badge badge-success" style="font-size:0.8rem;padding:0.35rem 0.75rem;">
                      <i class="fas fa-user-shield mr-1"></i><?= htmlspecialchars($row['auteur']) ?>
                    </span>
                  </td>
                  <td><small><i class="fas fa-calendar-alt mr-1 text-muted"></i><?= htmlspecialchars($row['date_reponse']) ?></small></td>
                  <td class="text-center">
                    <a href="reponsesBack.php?delete_rep_id=<?= $row['id'] ?>" class="btn btn-sm btn-danger" title="Supprimer"
                       onclick="return confirm('Supprimer cette réponse ?')">
                      <i class="fas fa-trash"></i>
                    </a>
                  </td>
                </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include 'template/footer.php'; ?>

<script>
$(document).ready(function () {

  // tri
  var table = $('#dtRep').DataTable({
    language: {
      emptyTable:  "Aucune réponse",
      info:        "_START_ à _END_ sur _TOTAL_ réponses",
      infoEmpty:   "0 réponse",
      lengthMenu:  "Afficher _MENU_ lignes",
      zeroRecords: "Aucun résultat trouvé",
      search:      "Recherche :",
      paginate:    { next: "Suivant", previous: "Précédent" }
    },
    pageLength: 10,
    order: [[4, "desc"]],
    dom: "lrtip",
    buttons: [{
      extend:    'pdfHtml5',
      text:      '<i class="fas fa-file-pdf mr-1"></i>PDF',
      className: 'btn btn-sm btn-danger',
      title:     'Journal des Réponses — TinyTrack',
      exportOptions: { columns: [0,1,2,3,4] },
      customize: function(doc) {
        doc.styles.tableHeader.fillColor = '#5B9BD5';
        doc.defaultStyle.fontSize = 10;
      }
    }]
  });

  // pdf
  $('#btnPDF').on('click', function () { table.button('.buttons-pdf').trigger(); });

  // recherche
  $('#searchRep').on('keyup', function () { table.search(this.value).draw(); });
  $('#searchRepBtn').on('click', function () { table.search($('#searchRep').val()).draw(); });

  // stat
  var ctxCov = document.getElementById('chartCoverage').getContext('2d');
  new Chart(ctxCov, {
    type: 'doughnut',
    data: {
      labels: ['Répondues', 'En attente'],
      datasets: [{
        data: [<?= $avecRep ?>, <?= $sansRep ?>],
        backgroundColor: ['#5B9BD5', '#FFA726'],
        borderColor:     ['#1976D2', '#FF8F00'],
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

});
</script>

<?php
// Vue passive — données injectées par ActiviteController::statistiques()
// Variables : $stats
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-chart-pie text-info"></i> Statistiques des activités</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="/TinyTrack/dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="/TinyTrack/activites">Activités</a></li><li class="breadcrumb-item active">Statistiques</li></ol></div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <div class="card card-info">
        <div class="card-header">
          <h3 class="card-title">Répartition des activités par éducateur</h3>
        </div>
        <div class="card-body">
          <a href="/TinyTrack/activites" class="btn btn-default mb-3"><i class="fas fa-arrow-left"></i> Retour</a>

          <?php if (empty($stats)): ?>
            <div class="alert alert-warning text-center"><i class="fas fa-info-circle"></i> Aucune donnée disponible.</div>
          <?php else: ?>
            <div class="row">
              <div class="col-md-7">
                <canvas id="educateurPieChart" style="max-height:400px;"></canvas>
              </div>
              <div class="col-md-5">
                <table class="table table-bordered table-striped mt-3">
                  <thead class="bg-info">
                    <tr><th>Éducateur</th><th class="text-right">Total activités</th></tr>
                  </thead>
                  <tbody>
                    <?php foreach ($stats as $s): ?>
                      <tr>
                        <td><?= htmlspecialchars(trim($s['educateur_nom']) !== '' ? $s['educateur_nom'] : ('Éducateur #' . ($s['id_educateur'] ?? '—'))) ?></td>
                        <td class="text-right"><strong><?= (int)$s['total_activites'] ?></strong></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endif; ?>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

<?php if (!empty($stats)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
(function(){
  var labels = [
    <?php foreach ($stats as $s): ?>
      <?= json_encode(trim($s['educateur_nom']) !== '' ? $s['educateur_nom'] : ('Éducateur #' . ($s['id_educateur'] ?? '—'))) ?>,
    <?php endforeach; ?>
  ];
  var data = [
    <?php foreach ($stats as $s): ?>
      <?= (int)$s['total_activites'] ?>,
    <?php endforeach; ?>
  ];

  var ctx = document.getElementById('educateurPieChart');
  if (ctx) {
    new Chart(ctx, {
      type: 'pie',
      data: {
        labels: labels,
        datasets: [{
          data: data,
          backgroundColor: ['#17a2b8','#28a745','#ffc107','#dc3545','#6f42c1','#fd7e14','#20c997','#e83e8c','#6610f2']
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          title: { display: true, text: 'Répartition des activités par éducateur' }
        }
      }
    });
  }
})();
</script>
<?php endif; ?>

<?php
require_once '../../controller/ActiviteController.php';

$controller = new ActiviteController();
$statsEducateurs = $controller->statistiquesActivitesParEducateur();

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">
                <i class="fas fa-chart-pie text-info"></i> Statistiques des activités
            </h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        Répartition des activités par éducateur
                    </h3>
                </div>

                <div class="card-body">
                    <a href="/ProjetRapport/tinytrack/view/backoffice/listActivites.php" class="btn btn-default mb-3">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>

                    <canvas id="educateurPieChart" style="max-height:350px;"></canvas>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include 'template/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
var labelsEducateurs = [
<?php foreach ($statsEducateurs as $s): ?>
    "Éducateur <?= htmlspecialchars($s['id_educateur'] ?? 'Non défini'); ?>",
<?php endforeach; ?>
];

var dataEducateurs = [
<?php foreach ($statsEducateurs as $s): ?>
    <?= (int)$s['total_activites']; ?>,
<?php endforeach; ?>
];

var ctx = document.getElementById('educateurPieChart');

if (ctx) {
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: labelsEducateurs,
            datasets: [{
                data: dataEducateurs,
                backgroundColor: [
                    '#17a2b8',
                    '#28a745',
                    '#ffc107',
                    '#dc3545',
                    '#6f42c1',
                    '#fd7e14',
                    '#20c997'
                ]
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom' },
                title: {
                    display: true,
                    text: 'Répartition des activités par éducateur'
                }
            }
        }
    });
}
</script>
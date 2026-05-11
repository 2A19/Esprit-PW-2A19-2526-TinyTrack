<?php
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once '../../controller/ActiviteController.php';

$controller = new ActiviteController();
$expertises = $controller->expertiseEducateurs();

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="m-0">
                <i class="fas fa-brain text-dark"></i> Analyse des compétences des éducateurs
            </h1>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="card card-secondary">
                <div class="card-header">
                    <h3 class="card-title">Niveau d'expertise par activité</h3>
                </div>

                <div class="card-body">
                    <a href="/ProjetRapport/tinytrack/view/backoffice/listActivites.php" class="btn btn-default mb-3">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>

                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Éducateur ID</th>
                                <th>Activité</th>
                                <th>Nombre d'affectations</th>
                                <th>Niveau</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($expertises)): ?>
                                <?php foreach ($expertises as $e): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($e['id_educateur'] ?? 'Non défini'); ?></td>
                                        <td><?php echo htmlspecialchars($e['nom_activite']); ?></td>
                                        <td><?php echo (int)$e['total']; ?></td>
                                        <td>
                                            <?php
                                            if ((int)$e['total'] >= 3) {
                                                echo '<span class="badge bg-success">Expert</span>';
                                            } elseif ((int)$e['total'] == 2) {
                                                echo '<span class="badge bg-warning">Intermédiaire</span>';
                                            } else {
                                                echo '<span class="badge bg-secondary">Débutant</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="text-center">Aucune donnée d'expertise disponible.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </section>
</div>

<?php include 'template/footer.php'; ?>

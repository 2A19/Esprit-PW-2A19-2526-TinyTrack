<?php
require_once '../../controller/ActiviteController.php';

$controller = new ActiviteController();
$activites = $controller->listActivites()->fetchAll();

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">
                        <i class="fas fa-paint-brush text-info"></i> Activités
                    </h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item">
                            <a href="/ProjetRapport/tinytrack/view/backoffice/listRapports.php">Dashboard</a>
                        </li>
                        <li class="breadcrumb-item active">Activités</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                    <?php
                    if ($_GET['success'] === 'add') {
                        echo "Activité ajoutée !";
                    } elseif ($_GET['success'] === 'edit') {
                        echo "Activité modifiée !";
                    } elseif ($_GET['success'] === 'delete') {
                        echo "Activité supprimée !";
                    }
                    ?>
                </div>
            <?php endif; ?>

            <div class="card card-info">
                <div class="card-header">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <div class="input-group">
                                <input
                                    type="text"
                                    id="searchAct"
                                    class="form-control"
                                    placeholder="Rechercher..."
                                    style="border-radius:10px 0 0 10px;"
                                >
                                <div class="input-group-append">
                                    <button
                                        type="button"
                                        class="btn btn-info"
                                        id="searchActBtn"
                                        style="border-radius:0 10px 10px 0;"
                                    >
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4 text-center">
                            <h3 class="card-title mb-0">Liste des activités</h3>
                        </div>

                        <div class="col-md-4 text-right">
                            <a href="/ProjetRapport/tinytrack/view/backoffice/addActivite.php" class="btn btn-success">
                                <i class="fas fa-plus-circle"></i> Ajouter
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                    <table id="tableActivites" class="table table-bordered table-hover table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nom</th>
                                <th>Description</th>
                                <th>Date</th>
                                <th>Heure</th>
                                <th>Éducateur ID</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($activites)): ?>
                                <?php foreach ($activites as $a): ?>
                                    <tr>
                                        <td><?php echo $a['id_activite']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($a['nom_activite']); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                            $description = $a['description'] ?? '';
                                            if (strlen($description) > 60) {
                                                echo htmlspecialchars(substr($description, 0, 60)) . '...';
                                            } else {
                                                echo htmlspecialchars($description);
                                            }
                                            ?>
                                        </td>
                                        <td><?php echo $a['date_activite']; ?></td>
                                        <td><?php echo substr($a['heure_activite'], 0, 5); ?></td>
                                        <td><?php echo isset($a['id_educateur']) ? $a['id_educateur'] : '—'; ?></td>
                                        <td>
                                            <a href="/ProjetRapport/tinytrack/view/backoffice/editActivite.php?id=<?php echo $a['id_activite']; ?>" class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i>
                                            </a>

                                            <a href="/ProjetRapport/tinytrack/view/backoffice/deleteActivite.php?id=<?php echo $a['id_activite']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Supprimer cette activité ?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">Aucune activité trouvée.</td>
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

<script>
$(document).ready(function () {
    var table = $('#tableActivites').DataTable({
        language: {
            emptyTable: "Aucune activité",
            info: "_START_ à _END_ sur _TOTAL_",
            lengthMenu: "Afficher _MENU_",
            zeroRecords: "Aucun résultat",
            paginate: {
                next: "Suivant",
                previous: "Précédent"
            }
        },
        pageLength: 10,
        order: [[0, "desc"]],
        dom: "lrtip"
    });

    $('#searchAct').on('keyup', function () {
        table.search($(this).val()).draw();
    });

    $('#searchActBtn').on('click', function () {
        table.search($('#searchAct').val()).draw();
    });
});
</script>
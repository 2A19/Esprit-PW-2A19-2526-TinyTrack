<?php
// Vue passive — données injectées par ActiviteController::expertise()
// Variables : $expertises
include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6"><h1 class="m-0"><i class="fas fa-brain text-dark"></i> Analyse des compétences des éducateurs</h1></div>
        <div class="col-sm-6"><ol class="breadcrumb float-sm-right"><li class="breadcrumb-item"><a href="/TinyTrack/dashboard">Dashboard</a></li><li class="breadcrumb-item"><a href="/TinyTrack/activites">Activités</a></li><li class="breadcrumb-item active">Expertise</li></ol></div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <div class="card card-secondary">
        <div class="card-header">
          <h3 class="card-title">Niveau d'expertise par activité</h3>
        </div>
        <div class="card-body">
          <a href="/TinyTrack/activites" class="btn btn-default mb-3"><i class="fas fa-arrow-left"></i> Retour</a>

          <table class="table table-bordered table-striped">
            <thead>
              <tr>
                <th>Éducateur</th>
                <th>Activité</th>
                <th class="text-center">Nombre d'affectations</th>
                <th class="text-center">Niveau</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($expertises)): ?>
                <?php foreach ($expertises as $e):
                  $total = (int)$e['total'];
                  if ($total >= 3) { $badge = 'success'; $label = 'Expert'; }
                  elseif ($total === 2) { $badge = 'warning'; $label = 'Intermédiaire'; }
                  else { $badge = 'secondary'; $label = 'Débutant'; }
                  $nom = trim($e['educateur_nom'] ?? '');
                  if ($nom === '') $nom = 'Éducateur #' . ($e['id_educateur'] ?? '—');
                ?>
                  <tr>
                    <td><?= htmlspecialchars($nom) ?></td>
                    <td><?= htmlspecialchars($e['nom_activite']) ?></td>
                    <td class="text-center"><strong><?= $total ?></strong></td>
                    <td class="text-center"><span class="badge badge-<?= $badge ?> bg-<?= $badge ?>"><?= $label ?></span></td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr><td colspan="4" class="text-center text-muted">Aucune donnée d'expertise disponible.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

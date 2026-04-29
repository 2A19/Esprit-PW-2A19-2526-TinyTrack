<?php
// Interface de démonstration du module Événements
// Aucune logique métier ni connexion BD nécessaire

$evenements = [
    [
        'id' => 5,
        'titre' => 'Forum Entreprises 2026',
        'date' => '2026-04-20',
        'heure_debut' => '09:00:00',
        'heure_fin' => '17:00:00',
        'type' => 'Professionnel',
        'lieu' => 'ESPRIT Ariana',
        'capacite_max' => 300,
        'prix' => 0,
        'statut' => 'planifie'
    ],
    [
        'id' => 4,
        'titre' => 'Hackathon Innovation',
        'date' => '2026-04-18',
        'heure_debut' => '08:30:00',
        'heure_fin' => '22:00:00',
        'type' => 'Compétition',
        'lieu' => 'Campus Central',
        'capacite_max' => 120,
        'prix' => 15,
        'statut' => 'en_cours'
    ],
    [
        'id' => 3,
        'titre' => 'Conférence IA & Data',
        'date' => '2026-04-15',
        'heure_debut' => '10:00:00',
        'heure_fin' => '13:00:00',
        'type' => 'Conférence',
        'lieu' => 'Salle B12',
        'capacite_max' => 180,
        'prix' => 20,
        'statut' => 'termine'
    ],
    [
        'id' => 2,
        'titre' => 'Journée Clubs',
        'date' => '2026-04-10',
        'heure_debut' => '11:00:00',
        'heure_fin' => '16:00:00',
        'type' => 'Campus',
        'lieu' => 'Cour principale',
        'capacite_max' => 250,
        'prix' => 0,
        'statut' => 'planifie'
    ],
    [
        'id' => 1,
        'titre' => 'Atelier UI/UX',
        'date' => '2026-04-08',
        'heure_debut' => '14:00:00',
        'heure_fin' => '17:00:00',
        'type' => 'Atelier',
        'lieu' => 'Lab Design',
        'capacite_max' => 40,
        'prix' => 10,
        'statut' => 'annule'
    ]
];

$total = count($evenements);
$planifies = count(array_filter($evenements, function ($e) { return $e['statut'] === 'planifie'; }));
$en_cours = count(array_filter($evenements, function ($e) { return $e['statut'] === 'en_cours'; }));
$termines = count(array_filter($evenements, function ($e) { return $e['statut'] === 'termine'; }));

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-calendar-alt text-success"></i> Événements
          </h1>
        </div>
        <div class="col-sm-6">
          <ol class="breadcrumb float-sm-right">
            <li class="breadcrumb-item">
              <a href="/ProjetRapport/tinytrack/view/backoffice/evenements.php">Dashboard</a>
            </li>
            <li class="breadcrumb-item active">Événements</li>
          </ol>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <div class="row">
        <div class="col-lg-3 col-6">
          <div class="small-box bg-success">
            <div class="inner">
              <h3><?php echo $total; ?></h3>
              <p>Total</p>
            </div>
            <div class="icon"><i class="fas fa-calendar"></i></div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-warning">
            <div class="inner">
              <h3><?php echo $planifies; ?></h3>
              <p>Planifiés</p>
            </div>
            <div class="icon"><i class="fas fa-clock"></i></div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?php echo $en_cours; ?></h3>
              <p>En cours</p>
            </div>
            <div class="icon"><i class="fas fa-play-circle"></i></div>
          </div>
        </div>

        <div class="col-lg-3 col-6">
          <div class="small-box bg-danger">
            <div class="inner">
              <h3><?php echo $termines; ?></h3>
              <p>Terminés</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
          </div>
        </div>
      </div>

      <div class="card card-success">
        <div class="card-header">
          <div class="row align-items-center">
            <div class="col-md-4">
              <div class="input-group">
                <input
                  type="text"
                  id="searchInput"
                  class="form-control"
                  placeholder="Rechercher..."
                  style="border-radius:10px 0 0 10px;"
                >
                <div class="input-group-append">
                  <button
                    type="button"
                    class="btn btn-success"
                    id="searchBtn"
                    style="border-radius:0 10px 10px 0;"
                  >
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="col-md-4 text-center">
              <h3 class="card-title mb-0">Liste complète</h3>
            </div>

            <div class="col-md-4 text-right">
              <a href="#" class="btn btn-success">
                <i class="fas fa-calendar-plus"></i> Ajouter
              </a>
            </div>
          </div>
        </div>

        <div class="card-body">
          <table id="tableEvents" class="table table-bordered table-hover table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Titre</th>
                <th>Date</th>
                <th>Horaire</th>
                <th>Type</th>
                <th>Lieu</th>
                <th>Capacité</th>
                <th>Prix</th>
                <th>Statut</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($evenements as $ev): ?>
                <tr>
                  <td><?php echo $ev['id']; ?></td>
                  <td><strong><?php echo htmlspecialchars($ev['titre']); ?></strong></td>
                  <td><?php echo $ev['date']; ?></td>
                  <td>
                    <?php echo substr($ev['heure_debut'], 0, 5); ?>
                    —
                    <?php echo substr($ev['heure_fin'], 0, 5); ?>
                  </td>
                  <td>
                    <span class="badge bg-primary"><?php echo htmlspecialchars($ev['type']); ?></span>
                  </td>
                  <td><?php echo htmlspecialchars($ev['lieu']); ?></td>
                  <td><?php echo $ev['capacite_max']; ?></td>
                  <td>
                    <?php
                      if ($ev['prix'] > 0) {
                          echo number_format($ev['prix'], 2) . ' TND';
                      } else {
                          echo '<span class="text-success font-weight-bold">Gratuit</span>';
                      }
                    ?>
                  </td>
                  <td>
                    <?php
                      $statusColors = [
                          'planifie' => 'warning',
                          'en_cours' => 'info',
                          'termine' => 'danger',
                          'annule' => 'secondary'
                      ];

                      $statusLabels = [
                          'planifie' => 'Planifié',
                          'en_cours' => 'En cours',
                          'termine' => 'Terminé',
                          'annule' => 'Annulé'
                      ];
                    ?>
                    <span class="badge bg-<?php echo $statusColors[$ev['statut']] ?? 'secondary'; ?>">
                      <?php echo $statusLabels[$ev['statut']] ?? htmlspecialchars($ev['statut']); ?>
                    </span>
                  </td>
                  <td>
                    <a href="#" class="btn btn-sm btn-warning">
                      <i class="fas fa-edit"></i>
                    </a>
                    <a href="#" class="btn btn-sm btn-danger">
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

<?php include 'template/footer.php'; ?>

<script>
$(document).ready(function () {
  var table = $('#tableEvents').DataTable({
    language: {
      emptyTable: "Aucun événement",
      info: "_START_ à _END_ sur _TOTAL_",
      infoEmpty: "Aucun",
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

  $('#searchInput').on('keyup', function () {
    table.search(this.value).draw();
  });

  $('#searchBtn').on('click', function () {
    table.search($('#searchInput').val()).draw();
  });
});
</script>
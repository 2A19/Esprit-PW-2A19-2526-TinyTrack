<?php
// Interface de démonstration Messages (sans backend)

$messages = [
    (object)[
        'id'=>5,
        'conversation_id'=>101,
        'sender_id'=>1,
        'sender_role'=>'admin',
        'body'=>'Bonjour, votre demande a été traitée avec succès.',
        'created_at'=>'2026-04-12 10:20'
    ],
    (object)[
        'id'=>4,
        'conversation_id'=>102,
        'sender_id'=>3,
        'sender_role'=>'educateur',
        'body'=>'Merci pour votre retour, nous allons vérifier cela.',
        'created_at'=>'2026-04-11 14:10'
    ],
    (object)[
        'id'=>3,
        'conversation_id'=>103,
        'sender_id'=>7,
        'sender_role'=>'parent',
        'body'=>'Je rencontre un problème avec l’application.',
        'created_at'=>'2026-04-10 09:30'
    ]
];

include 'template/header.php';
include 'template/sidebar.php';
?>

<div class="content-wrapper">
  <div class="content-header">
    <div class="container-fluid">
      <div class="row mb-2">
        <div class="col-sm-6">
          <h1 class="m-0">
            <i class="fas fa-envelope text-info"></i> Messages
          </h1>
        </div>
      </div>
    </div>
  </div>

  <section class="content">
    <div class="container-fluid">

      <!-- STATS -->
      <div class="row mb-3">
        <div class="col-lg-4 col-6">
          <div class="small-box bg-info">
            <div class="inner">
              <h3><?= count($messages) ?></h3>
              <p>Total messages</p>
            </div>
            <div class="icon"><i class="fas fa-envelope"></i></div>
          </div>
        </div>
      </div>

      <!-- TABLE -->
      <div class="card card-info">
        <div class="card-header">
          <div class="row align-items-center">

            <div class="col-md-4">
              <div class="input-group">
                <input type="text" id="searchInput" class="form-control" placeholder="Rechercher...">
                <div class="input-group-append">
                  <button class="btn btn-info" id="searchBtn">
                    <i class="fas fa-search"></i>
                  </button>
                </div>
              </div>
            </div>

            <div class="col-md-4 text-center">
              <h3 class="card-title mb-0">Tous les messages</h3>
            </div>

          </div>
        </div>

        <div class="card-body">
          <table id="tableMessages" class="table table-bordered table-hover table-striped">
            <thead>
              <tr>
                <th>#</th>
                <th>Conv.</th>
                <th>Expéditeur</th>
                <th>Rôle</th>
                <th>Message</th>
                <th>Date</th>
                <th>Actions</th>
              </tr>
            </thead>

            <tbody>
              <?php foreach ($messages as $msg): ?>
              <tr>
                <td><?= $msg->id ?></td>

                <td>
                  <span class="badge bg-primary">#<?= $msg->conversation_id ?></span>
                </td>

                <td>ID: <?= $msg->sender_id ?></td>

                <td>
                  <?php if ($msg->sender_role === 'admin'): ?>
                    <span class="badge bg-success">Admin</span>
                  <?php elseif ($msg->sender_role === 'educateur'): ?>
                    <span class="badge bg-info">Éducateur</span>
                  <?php else: ?>
                    <span class="badge bg-warning text-dark">Parent</span>
                  <?php endif; ?>
                </td>

                <td>
                  <?= htmlspecialchars(substr($msg->body, 0, 80)) ?>
                </td>

                <td><?= $msg->created_at ?></td>

                <td>
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
$(document).ready(function(){
  var table=$('#tableMessages').DataTable({
    "language":{
      "emptyTable":"Aucun message",
      "info":"_START_ à _END_ sur _TOTAL_",
      "lengthMenu":"Afficher _MENU_",
      "zeroRecords":"Aucun résultat",
      "paginate":{"next":"Suivant","previous":"Précédent"}
    },
    "pageLength":10,
    "order":[[0,"desc"]],
    "dom":"lrtip"
  });

  $('#searchInput').on('keyup',function(){
    table.search(this.value).draw();
  });

  $('#searchBtn').on('click',function(){
    table.search($('#searchInput').val()).draw();
  });
});
</script>
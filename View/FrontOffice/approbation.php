<?php
// Vue passive — données injectées par ApprobationController::index()
// Variables : $enAttente, $actifs
$pendingUsers = $enAttente;
$activeUsers = $actifs;
include __DIR__ . '/template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <div class="text-center mb-4">
    <h2 class="section-title"><i class="fas fa-user-check"></i> Approbation des comptes</h2>
    <p class="text-muted mt-3">Approuvez ou refusez les demandes d'inscription</p>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="row justify-content-center"><div class="col-md-8">
      <?php if ($_GET['msg'] === 'approved'): ?>
        <div class="alert alert-success" style="border-radius:16px;border:none;text-align:center;">
          <i class="fas fa-check-circle"></i> Compte approuvé et email de confirmation envoyé !
        </div>
      <?php elseif ($_GET['msg'] === 'rejected'): ?>
        <div class="alert alert-danger" style="border-radius:16px;border:none;text-align:center;">
          <i class="fas fa-times-circle"></i> Demande refusée et supprimée.
        </div>
      <?php endif; ?>
    </div></div>
  <?php endif; ?>

  <!-- PENDING -->
  <div class="row justify-content-center mb-5">
    <div class="col-md-10">
      <div class="card-kider p-0" style="overflow:hidden;">
        <div style="background:linear-gradient(135deg,#FFA726,#FFB74D);padding:1rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
          <h5 style="font-family:'Fredoka One',cursive;color:#fff;margin:0;"><i class="fas fa-clock"></i> En attente d'approbation</h5>
          <span style="background:rgba(255,255,255,0.25);color:#fff;padding:0.3rem 0.8rem;border-radius:20px;font-weight:700;font-size:0.85rem;"><?= count($pendingUsers) ?></span>
        </div>

        <?php if (empty($pendingUsers)): ?>
          <div class="p-4 text-center">
            <i class="fas fa-inbox fa-3x" style="color:#ddd;"></i>
            <p class="text-muted mt-2">Aucune demande en attente</p>
          </div>
        <?php else: ?>
          <div class="table-responsive p-3">
            <table class="table table-hover" style="margin:0;">
              <thead>
                <tr style="font-family:'Fredoka One',cursive;font-size:0.85rem;color:#666;">
                  <th>Code</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Tél</th><th>Date</th><th>Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($pendingUsers as $u): ?>
                <tr>
                  <td><span style="background:#E3F2FD;color:#1565C0;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= htmlspecialchars($u['code_unique'] ?? '—') ?></span></td>
                  <td><strong><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></strong></td>
                  <td style="font-size:0.85rem;"><?= htmlspecialchars($u['email']) ?></td>
                  <td>
                    <?php if ($u['role'] === 'educateur'): ?>
                      <span style="background:#EDE7F6;color:#7B5EA7;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.75rem;font-weight:700;">Éducateur</span>
                    <?php else: ?>
                      <span style="background:#FCE4EC;color:#AD5D7E;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.75rem;font-weight:700;">Parent</span>
                    <?php endif; ?>
                  </td>
                  <td style="font-size:0.85rem;"><?= htmlspecialchars($u['telephone'] ?? '—') ?></td>
                  <td style="font-size:0.8rem;color:#999;"><?= date('d/m/Y H:i', strtotime($u['created_at'])) ?></td>
                  <td>
                    <form method="POST" action="/TinyTrack/approbation/approuver/<?= (int)$u['id'] ?>" style="display:inline" onsubmit="return confirm('Approuver ce compte ?')">
                      <button type="submit" class="btn btn-sm btn-success" style="border-radius:20px;">
                        <i class="fas fa-check"></i> Approuver
                      </button>
                    </form>
                    <form method="POST" action="/TinyTrack/approbation/rejeter/<?= (int)$u['id'] ?>" style="display:inline" onsubmit="return confirm('Refuser et supprimer ce compte ?')">
                      <button type="submit" class="btn btn-sm btn-danger" style="border-radius:20px;">
                        <i class="fas fa-times"></i>
                      </button>
                    </form>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ACTIVE USERS -->
  <div class="row justify-content-center">
    <div class="col-md-10">
      <div class="card-kider p-0" style="overflow:hidden;">
        <div style="background:linear-gradient(135deg,#4CAF50,#81C784);padding:1rem 1.5rem;">
          <h5 style="font-family:'Fredoka One',cursive;color:#fff;margin:0;"><i class="fas fa-users"></i> Comptes actifs récents</h5>
        </div>
        <div class="table-responsive p-3">
          <table class="table table-hover" style="margin:0;">
            <thead>
              <tr style="font-family:'Fredoka One',cursive;font-size:0.85rem;color:#666;">
                <th>Code</th><th>Nom</th><th>Email</th><th>Rôle</th><th>Statut</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($activeUsers as $u): ?>
              <tr>
                <td><span style="background:#E8F5E9;color:#2E7D32;padding:0.2rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= htmlspecialchars($u['code_unique'] ?? '—') ?></span></td>
                <td><?= htmlspecialchars($u['prenom'] . ' ' . $u['nom']) ?></td>
                <td style="font-size:0.85rem;"><?= htmlspecialchars($u['email']) ?></td>
                <td style="font-size:0.8rem;"><?= ucfirst($u['role']) ?></td>
                <td><span style="background:#E8F5E9;color:#2E7D32;padding:0.2rem 0.5rem;border-radius:10px;font-size:0.75rem;font-weight:700;"><i class="fas fa-check-circle"></i> Actif</span></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/template/footer.php'; ?>

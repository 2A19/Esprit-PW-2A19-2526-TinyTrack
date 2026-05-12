<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Gestion Rapport — Vue Éducateur
 * Liste les rapports créés par l'éducateur connecté avec CRUD.
 */
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'educateur') {
    header('Location: /TinyTrack/login');
    exit;
}

require_once __DIR__ . '/../../../Controller/RapportController.php';

$controller = new RapportController();
$tri = isset($_GET['tri']) && in_array($_GET['tri'], ['asc', 'desc'], true) ? $_GET['tri'] : 'desc';
$rapports = $controller->listRapportsByEducateur($_SESSION['user_id'], $tri)->fetchAll();

include __DIR__ . '/../template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <div class="text-center mb-4">
    <h2 class="section-title"><i class="fas fa-book"></i> Mes rapports</h2>
    <div class="rainbow-divider"></div>
    <p class="text-muted mt-2">Liste des rapports que vous avez rédigés</p>
  </div>

  <?php if (isset($_GET['success'])): ?>
    <div class="row justify-content-center mb-3"><div class="col-md-8">
      <?php
        $msg = '';
        if ($_GET['success'] === 'add') $msg = 'Rapport ajouté avec succès !';
        elseif ($_GET['success'] === 'edit') $msg = 'Rapport modifié !';
        elseif ($_GET['success'] === 'delete') $msg = 'Rapport supprimé.';
      ?>
      <?php if ($msg): ?>
        <div class="alert alert-success" style="border-radius:14px;border:none;text-align:center;">
          <i class="fas fa-check-circle"></i> <?= $msg ?>
        </div>
      <?php endif; ?>
    </div></div>
  <?php endif; ?>

  <!-- TOOLBAR : count + sort + add -->
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <div class="mini-stat ms-grape" style="flex:0 0 auto;">
      <div class="ms-icon"><i class="fas fa-book"></i></div>
      <div><p class="ms-value"><?= count($rapports) ?></p><p class="ms-label">Mes rapports</p></div>
    </div>
    <div class="d-flex gap-2 flex-wrap">
      <select onchange="window.location.href='?tri='+this.value" class="filter-select">
        <option value="desc" <?= $tri==='desc'?'selected':'' ?>>Plus récent</option>
        <option value="asc"  <?= $tri==='asc'?'selected':'' ?>>Plus ancien</option>
      </select>
      <a href="/TinyTrack/View/FrontOffice/rapports/addRapport.php" class="btn-chunky" style="padding:0.6rem 1.2rem;font-size:0.9rem;">
        <i class="fas fa-plus"></i> Nouveau rapport
      </a>
    </div>
  </div>

  <?php if (empty($rapports)): ?>
    <div class="row justify-content-center">
      <div class="col-md-8 text-center">
        <div class="card-kider p-5">
          <i class="fas fa-book fa-4x" style="color:#ddd;"></i>
          <h5 style="font-family:'Fredoka One',cursive;color:#999;margin-top:1rem;">Aucun rapport pour l'instant</h5>
          <a href="/TinyTrack/View/FrontOffice/rapports/addRapport.php" class="btn-chunky mt-3" style="display:inline-flex;padding:0.7rem 1.5rem;">
            <i class="fas fa-plus"></i> Créer mon premier rapport
          </a>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="row g-3">
      <?php foreach ($rapports as $r): ?>
        <div class="col-md-6">
          <div class="card-kider p-3">
            <div class="d-flex justify-content-between align-items-start mb-2 flex-wrap gap-2">
              <div>
                <span style="background:#EDE7F6;color:#6A1B9A;padding:0.2rem 0.7rem;border-radius:12px;font-size:0.78rem;font-weight:700;">
                  <i class="fas fa-puzzle-piece"></i> <?= htmlspecialchars($r['nom_activite'] ?? '—') ?>
                </span>
                <span style="background:#E8F5E9;color:#2E7D32;padding:0.2rem 0.7rem;border-radius:12px;font-size:0.78rem;font-weight:700;margin-left:4px;">
                  <i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($r['date_rapport'])) ?>
                </span>
              </div>
              <div>
                <a href="/TinyTrack/View/BackOffice/rapports/editRapport.php?id=<?= $r['id_rapport'] ?>" class="btn btn-sm" style="background:#FFF8E1;color:#E65100;border-radius:10px;font-weight:700;padding:0.3rem 0.7rem;text-decoration:none;">
                  <i class="fas fa-edit"></i>
                </a>
                <a href="/TinyTrack/View/BackOffice/rapports/deleteRapport.php?id=<?= $r['id_rapport'] ?>&back=mes" class="btn btn-sm" style="background:#FFEBEE;color:#C62828;border-radius:10px;font-weight:700;padding:0.3rem 0.7rem;text-decoration:none;" onclick="return confirm('Supprimer ce rapport ?');">
                  <i class="fas fa-trash"></i>
                </a>
                <a href="/TinyTrack/View/BackOffice/rapports/analyseRapportIA.php?id=<?= $r['id_rapport'] ?>" class="btn btn-sm" style="background:#E3F2FD;color:#1565C0;border-radius:10px;font-weight:700;padding:0.3rem 0.7rem;text-decoration:none;" title="Analyse IA">
                  <i class="fas fa-brain"></i>
                </a>
              </div>
            </div>
            <p style="color:#555;line-height:1.5;margin:0;">
              <?= nl2br(htmlspecialchars(mb_substr($r['contenu_rapport'], 0, 250))) ?><?= mb_strlen($r['contenu_rapport']) > 250 ? '…' : '' ?>
            </p>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

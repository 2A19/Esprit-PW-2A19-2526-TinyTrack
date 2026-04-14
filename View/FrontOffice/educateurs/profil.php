<?php
session_start();
require_once __DIR__ . '/../../../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'educateur') {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Get educateur info
$stmt = $db->prepare("SELECT * FROM user WHERE id = :id AND role = 'educateur'");
$stmt->execute([':id' => $_SESSION['user_id']]);
$educateur = $stmt->fetch();

// Get assigned group
$stmt = $db->prepare("SELECT * FROM groupe WHERE educateur_id = :id");
$stmt->execute([':id' => $_SESSION['user_id']]);
$groupe = $stmt->fetch();

// Get children in the group
$enfants = [];
if ($groupe) {
    $stmt = $db->prepare("
        SELECT e.*, d.groupe_sanguin, d.allergies, d.medecin_traitant, d.telephone_urgence
        FROM enfant e
        LEFT JOIN dossier_medical d ON d.enfant_id = e.id
        WHERE e.groupe_id = :groupe_id AND e.statut = 'actif'
        ORDER BY e.nom
    ");
    $stmt->execute([':groupe_id' => $groupe['id']]);
    $enfants = $stmt->fetchAll();
}

include '../template/header.php';
?>

<div class="container py-5" style="position:relative; z-index:1;">

  <!-- PROFILE CARD -->
  <div class="row justify-content-center mb-5">
    <div class="col-md-8">
      <div class="card-kider p-0" style="overflow:hidden;">

        <!-- Profile header -->
        <div style="background:linear-gradient(135deg,#5B9BD5,#90CAF9);padding:2rem;text-align:center;position:relative;">
          <div style="position:absolute;top:-10px;right:-10px;width:80px;height:80px;background:rgba(255,255,255,0.1);border-radius:50%;"></div>
          <div style="position:absolute;bottom:-15px;left:20px;width:50px;height:50px;background:rgba(255,255,255,0.08);border-radius:50%;"></div>

          <div class="avatar-circle d-inline-flex" style="width:100px;height:100px;background:rgba(255,255,255,0.25);border:3px solid rgba(255,255,255,0.5);">
            <i class="fas fa-user-tie" style="font-size:2.8rem;color:#fff;"></i>
          </div>
          <h3 style="font-family:'Fredoka One',cursive;color:#fff;margin-top:0.8rem;font-size:1.6rem;">
            <?= htmlspecialchars($educateur['prenom'] . ' ' . $educateur['nom']) ?>
          </h3>
          <span style="background:rgba(255,255,255,0.2);color:#fff;padding:0.3rem 1rem;border-radius:20px;font-size:0.8rem;font-weight:700;">
            <i class="fas fa-chalkboard-teacher"></i> Éducatrice
          </span>
        </div>

        <!-- Profile info -->
        <div style="padding:1.5rem 2rem;">
          <div class="row">

            <!-- Infos personnelles -->
            <div class="col-md-6 mb-3">
              <div class="medical-box">
                <p style="font-weight:800;color:#5B9BD5;margin-bottom:0.5rem;font-size:0.95rem;"><i class="fas fa-id-badge"></i> Informations personnelles</p>
                <p><i class="fas fa-hashtag" style="color:#FFA726;"></i> <strong>ID :</strong> <?= $educateur['id'] ?></p>
                <p><i class="fas fa-envelope" style="color:#5B9BD5;"></i> <strong>Email :</strong> <?= htmlspecialchars($educateur['email']) ?></p>
                <p><i class="fas fa-phone" style="color:#4CAF50;"></i> <strong>Tél :</strong> <?= htmlspecialchars($educateur['telephone'] ?: '—') ?></p>
                <p><i class="fas fa-birthday-cake" style="color:#FF8FAB;"></i> <strong>Date de naissance :</strong> <?= $educateur['date_naissance'] ? date('d/m/Y', strtotime($educateur['date_naissance'])) : '—' ?></p>
                <p><i class="fas fa-id-card" style="color:#9C7CDB;"></i> <strong>CIN :</strong> <?= htmlspecialchars($educateur['cin'] ?: '—') ?></p>
                <p class="mb-0"><i class="fas fa-map-marker-alt" style="color:#EF5350;"></i> <strong>Adresse :</strong> <?= htmlspecialchars($educateur['adresse'] ?: '—') ?></p>
              </div>
            </div>

            <!-- Infos professionnelles -->
            <div class="col-md-6 mb-3">
              <div class="medical-box">
                <p style="font-weight:800;color:#4CAF50;margin-bottom:0.5rem;font-size:0.95rem;"><i class="fas fa-briefcase"></i> Informations professionnelles</p>
                <p><i class="fas fa-graduation-cap" style="color:#FFA726;"></i> <strong>Diplôme :</strong> <?= htmlspecialchars($educateur['diplome'] ?: '—') ?></p>
                <p><i class="fas fa-star" style="color:#FFD93D;"></i> <strong>Spécialité :</strong> <?= htmlspecialchars($educateur['specialite'] ?: '—') ?></p>
                <p><i class="fas fa-calendar-check" style="color:#5B9BD5;"></i> <strong>Date d'embauche :</strong> <?= $educateur['date_embauche'] ? date('d/m/Y', strtotime($educateur['date_embauche'])) : '—' ?></p>
                <p><i class="fas fa-clock" style="color:#9C7CDB;"></i> <strong>Ancienneté :</strong>
                  <?php
                    if ($educateur['date_embauche']) {
                      $diff = (new DateTime())->diff(new DateTime($educateur['date_embauche']));
                      echo $diff->y . ' an(s) et ' . $diff->m . ' mois';
                    } else {
                      echo '—';
                    }
                  ?>
                </p>
                <p class="mb-0"><i class="fas fa-toggle-on" style="color:#4CAF50;"></i> <strong>Statut :</strong>
                  <span style="background:#E8F5E9;color:#2E7D32;padding:0.15rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= $educateur['statut'] ?></span>
                </p>
              </div>
            </div>

            <!-- Mon groupe -->
            <div class="col-12 mb-3">
              <div class="medical-box" style="background:linear-gradient(135deg,#FFF8E1,#FFFDE7);border:1px solid #FFE082;">
                <p style="font-weight:800;color:#FFA726;margin-bottom:0.5rem;font-size:0.95rem;"><i class="fas fa-users"></i> Mon groupe assigné</p>
                <?php if ($groupe): ?>
                  <div class="row">
                    <div class="col-md-4">
                      <p><i class="fas fa-star" style="color:#FFD93D;"></i> <strong>Nom :</strong> <?= htmlspecialchars($groupe['nom']) ?></p>
                    </div>
                    <div class="col-md-4">
                      <p><i class="fas fa-layer-group" style="color:#9C7CDB;"></i> <strong>Niveau :</strong>
                        <span style="background:#E8F5E9;color:#2E7D32;padding:0.15rem 0.6rem;border-radius:10px;font-size:0.8rem;font-weight:700;"><?= $groupe['niveau'] ?></span>
                      </p>
                    </div>
                    <div class="col-md-4">
                      <p><i class="fas fa-child" style="color:#FF8FAB;"></i> <strong>Enfants :</strong> <?= count($enfants) ?> enfant(s) / <?= $groupe['capacite'] ?> places</p>
                    </div>
                  </div>
                <?php else: ?>
                  <p class="mb-0" style="color:#999;"><i class="fas fa-info-circle"></i> Aucun groupe assigné pour le moment</p>
                <?php endif; ?>
              </div>
            </div>

          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- CHILDREN IN MY GROUP -->
  <?php if ($groupe && !empty($enfants)): ?>
  <div class="text-center mb-4">
    <h3 style="font-family:'Fredoka One',cursive;color:#4CAF50;">
      <i class="fas fa-child"></i> Les enfants de mon groupe — <?= htmlspecialchars($groupe['nom']) ?>
    </h3>
  </div>

  <div class="row g-4">
    <?php foreach ($enfants as $e): ?>
      <div class="col-md-6 col-lg-4">
        <div class="card-kider p-4 text-center">
          <div class="mb-3">
            <?php if ($e['sexe'] === 'M'): ?>
              <div class="avatar-circle boy d-inline-flex">
                <i class="fas fa-child" style="color:#5B9BD5;font-size:2.2rem;"></i>
              </div>
            <?php else: ?>
              <div class="avatar-circle girl d-inline-flex">
                <i class="fas fa-child-dress" style="color:#FF8FAB;font-size:2.2rem;"></i>
              </div>
            <?php endif; ?>
          </div>

          <h5 style="font-family:'Fredoka One',cursive;"><?= htmlspecialchars($e['prenom'] . ' ' . $e['nom']) ?></h5>
          <p class="text-muted" style="font-size:0.85rem;">
            <i class="fas fa-birthday-cake" style="color:var(--kider-orange);"></i>
            <?= date('d/m/Y', strtotime($e['date_naissance'])) ?>
            &mdash;
            <?php if ($e['sexe'] === 'M'): ?>
              <span class="badge-boy">Garçon</span>
            <?php else: ?>
              <span class="badge-girl">Fille</span>
            <?php endif; ?>
          </p>

          <div class="medical-box text-start mt-3">
            <p><i class="fas fa-tint" style="color:var(--kider-orange);"></i> <strong>Sang :</strong> <?= htmlspecialchars($e['groupe_sanguin'] ?: '—') ?></p>
            <p><i class="fas fa-allergies" style="color:#EF5350;"></i> <strong>Allergies :</strong> <?= htmlspecialchars($e['allergies'] ?: 'Aucune') ?></p>
            <p class="mb-0"><i class="fas fa-phone" style="color:var(--kider-green);"></i> <strong>Urgence :</strong> <?= htmlspecialchars($e['telephone_urgence'] ?: '—') ?></p>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

</div>

<?php include '../template/footer.php'; ?>

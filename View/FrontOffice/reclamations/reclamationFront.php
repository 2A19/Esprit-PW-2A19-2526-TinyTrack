<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
require_once __DIR__ . '/../../../Controller/ReclamationController.php';
require_once __DIR__ . '/../../../config/db.php';
$reclamationController = new ReclamationController();
$errors = []; $success = false;
$edit_id = $_GET['edit_id'] ?? null;
$edit_rec = $edit_id ? $reclamationController->getReclamationById($edit_id) : null;

// Recupere l'email du parent connecte pour ne lister QUE ses reclamations.
$parentEmail = null;
$parentNom   = null;
if (!empty($_SESSION['user_id'])) {
    $stmt = Database::getInstance()->getConnection()
        ->prepare("SELECT email, prenom, nom FROM user WHERE id = :id LIMIT 1");
    $stmt->execute([':id' => (int)$_SESSION['user_id']]);
    $row = $stmt->fetch();
    if ($row) {
        $parentEmail = $row['email'];
        $parentNom   = trim($row['prenom'] . ' ' . $row['nom']);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_reclamation'])) {
    if (isset($_POST['id']) && !empty($_POST['id'])) {
        $result = $reclamationController->updateReclamation($_POST['id'], $_POST);
        if ($result === true) { $success = "Réclamation mise à jour !"; $edit_rec = null; } else { $errors = $result; }
    } else {
        $result = $reclamationController->addReclamation($_POST);
        if ($result === true) { $success = "Réclamation envoyée !"; $_POST = []; } else { $errors = $result; }
    }
}
// Filtre par email du parent connecte (sinon liste vide pour eviter la fuite)
$reclamations = $parentEmail ? $reclamationController->listReclamationsByEmail($parentEmail) : [];
include 'template/header.php';
?>

<div class="container py-5">
  <div class="text-center mb-5">
    <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" style="height:70px;margin-bottom:15px;">
    <h2 class="section-title"><i class="fas fa-headset"></i> Espace Réclamation</h2>
    <p class="text-muted mt-3">Déposez votre réclamation, notre équipe vous répond rapidement</p>
  </div>

  <div class="row justify-content-center mb-5" id="formulaire">
    <div class="col-md-8">
      <div class="card-kider p-4">
        <h5 style="font-family:'Fredoka One',cursive;margin-bottom:1rem;"><?= $edit_rec ? '<i class="fas fa-edit"></i> Modifier' : '<i class="fas fa-paper-plane"></i> Nouvelle réclamation' ?></h5>
        <?php if ($success): ?><div class="alert alert-success" style="border-radius:14px;border:none;"><i class="fas fa-check-circle"></i> <?= $success ?></div><?php endif; ?>
        <form method="POST" action="reclamationFront.php#formulaire" novalidate onsubmit="return validerRec()">
          <input type="hidden" name="submit_reclamation" value="1">
          <?php if ($edit_rec): ?><input type="hidden" name="id" value="<?= $edit_rec->getId() ?>"><?php endif; ?>
          <div class="row">
            <div class="col-md-6 mb-3"><label style="font-weight:700;">Nom <span style="color:#EF5350;">*</span></label><input type="text" name="nom_client" id="nom_client" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" placeholder="Votre nom" value="<?= htmlspecialchars($_POST['nom_client'] ?? ($edit_rec ? $edit_rec->getNomClient() : '')) ?>"><div class="invalid-feedback" id="err_nom_client"></div><?php if(isset($errors['nom_client'])): ?><div class="text-danger mt-1" style="font-size:0.8rem;"><?= $errors['nom_client'] ?></div><?php endif; ?></div>
            <div class="col-md-6 mb-3"><label style="font-weight:700;">Email <span style="color:#EF5350;">*</span></label><input type="text" name="email" id="email" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? ($edit_rec ? $edit_rec->getEmail() : '')) ?>"><div class="invalid-feedback" id="err_email"></div><?php if(isset($errors['email'])): ?><div class="text-danger mt-1" style="font-size:0.8rem;"><?= $errors['email'] ?></div><?php endif; ?></div>
          </div>
          <div class="mb-3"><label style="font-weight:700;">Sujet <span style="color:#EF5350;">*</span></label><input type="text" name="sujet" id="sujet" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" placeholder="Objet" value="<?= htmlspecialchars($_POST['sujet'] ?? ($edit_rec ? $edit_rec->getSujet() : '')) ?>"><div class="invalid-feedback" id="err_sujet"></div><?php if(isset($errors['sujet'])): ?><div class="text-danger mt-1" style="font-size:0.8rem;"><?= $errors['sujet'] ?></div><?php endif; ?></div>
          <div class="mb-3"><label style="font-weight:700;">Description <span style="color:#EF5350;">*</span></label><textarea name="description" id="description" class="form-control" style="border-radius:12px;border:2px solid #E8E8E8;" rows="4" placeholder="Décrivez votre problème..."><?= htmlspecialchars($_POST['description'] ?? ($edit_rec ? $edit_rec->getDescription() : '')) ?></textarea><div class="invalid-feedback" id="err_description"></div><?php if(isset($errors['description'])): ?><div class="text-danger mt-1" style="font-size:0.8rem;"><?= $errors['description'] ?></div><?php endif; ?></div>
          <button type="submit" class="btn btn-success w-100" style="border-radius:25px;padding:0.7rem;font-weight:700;"><i class="fas fa-paper-plane"></i> <?= $edit_rec ? 'Enregistrer' : 'Envoyer' ?></button>
          <?php if ($edit_rec): ?><a href="reclamationFront.php" class="btn btn-default w-100 mt-2" style="border-radius:25px;">Annuler</a><?php endif; ?>
        </form>
      </div>
    </div>
  </div>

  <div class="text-center mb-4"><h3 style="font-family:'Fredoka One',cursive;color:#4CAF50;"><i class="fas fa-list"></i> Vos réclamations</h3></div>
  <div class="row g-4">
    <?php foreach ($reclamations as $rec): ?>
      <div class="col-md-6">
        <div class="card-kider p-4">
          <div class="d-flex justify-content-between align-items-start mb-2">
            <h5 style="font-family:'Fredoka One',cursive;margin:0;"><?= htmlspecialchars($rec->getSujet()) ?></h5>
            <span class="badge bg-<?= $rec->getStatut()=='En attente'?'warning text-dark':'success' ?>" style="border-radius:20px;"><?= $rec->getStatut() ?></span>
          </div>
          <p style="font-size:0.85rem;color:#666;"><?= htmlspecialchars(substr($rec->getDescription(),0,100)) ?><?= strlen($rec->getDescription())>100?'...':'' ?></p>
          <div class="medical-box"><p><i class="fas fa-user" style="color:#5B9BD5;"></i> <strong><?= htmlspecialchars($rec->getNomClient()) ?></strong></p><p class="mb-0"><i class="fas fa-clock" style="color:#FFA726;"></i> <?= $rec->getDateCreation() ?></p></div>
          <?php if ($rec->getStatut()=='En attente'): ?><a href="reclamationFront.php?edit_id=<?= $rec->getId() ?>#formulaire" class="btn btn-sm btn-warning mt-2" style="border-radius:20px;"><i class="fas fa-edit"></i> Modifier</a><?php endif; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php include 'template/footer.php'; ?>
<script>
function showE(id,msg){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.add('is-invalid');if(e){e.textContent=msg;e.style.display='block';}}
function clearE(id){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.remove('is-invalid');if(e)e.style.display='none';}
function validerRec(){var ok=true;['nom_client','email','sujet','description'].forEach(clearE);
if(!document.getElementById('nom_client').value.trim()){showE('nom_client','Nom obligatoire');ok=false;}
var em=document.getElementById('email').value.trim();if(!em){showE('email','Email obligatoire');ok=false;}else if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(em)){showE('email','Email invalide');ok=false;}
if(!document.getElementById('sujet').value.trim()){showE('sujet','Sujet obligatoire');ok=false;}
if(!document.getElementById('description').value.trim()){showE('description','Description obligatoire');ok=false;}
return ok;}
</script>

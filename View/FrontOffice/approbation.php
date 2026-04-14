<?php
session_start();
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../config/mailer.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: /TinyTrack/View/auth/login.php');
    exit;
}

$db = Database::getInstance()->getConnection();

// Approve account
if (isset($_GET['approve']) && is_numeric($_GET['approve'])) {
    $id = (int)$_GET['approve'];
    $db->prepare("UPDATE user SET statut = 'actif' WHERE id = :id AND statut = 'en_attente'")->execute([':id' => $id]);

    // Get user info
    $stmt = $db->prepare("SELECT * FROM user WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();

    if ($user) {
        // Send email with credentials
        $roleLabel = ($user['role'] === 'educateur') ? 'Éducateur' : 'Parent';
        $code = $user['code_unique'];
        $subject = '=?UTF-8?B?' . base64_encode('TinyTrack - Compte approuvé !') . '?=';

        $body = '<html><body style="font-family:Arial,sans-serif;margin:0;padding:0;">';
        $body .= '<div style="max-width:500px;margin:20px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,0.08);">';
        $body .= '<div style="background:linear-gradient(135deg,#4CAF50,#81C784);padding:24px;text-align:center;">';
        $body .= '<h1 style="color:#fff;font-size:24px;margin:0;">TinyTrack</h1>';
        $body .= '<p style="color:rgba(255,255,255,0.85);margin:4px 0 0;font-size:14px;">Chaque petit pas compte</p>';
        $body .= '</div>';
        $body .= '<div style="padding:24px;">';
        $body .= '<h2 style="color:#2D3436;font-size:18px;">Bonjour ' . htmlspecialchars($user['prenom']) . ' !</h2>';
        $body .= '<p style="color:#555;font-size:15px;">Votre compte <strong>' . $roleLabel . '</strong> a ete approuve par l\'administration.</p>';
        $body .= '<p style="color:#555;font-size:15px;">Voici vos coordonnees de connexion :</p>';
        $body .= '<div style="background:#E8F5E9;border-radius:12px;padding:16px;margin:16px 0;">';
        $body .= '<p style="margin:0 0 8px;font-size:14px;color:#555;"><strong>Identifiant :</strong></p>';
        $body .= '<div style="font-family:Courier New,monospace;font-size:22px;font-weight:bold;color:#2E7D32;letter-spacing:2px;">' . htmlspecialchars($code) . '</div>';
        $body .= '</div>';
        $body .= '<div style="background:#FFF3E0;border-radius:12px;padding:16px;margin:12px 0;">';
        $body .= '<p style="margin:0 0 8px;font-size:14px;color:#555;"><strong>Mot de passe :</strong></p>';
        $body .= '<div style="font-family:Courier New,monospace;font-size:20px;font-weight:bold;color:#E65100;letter-spacing:2px;">' . htmlspecialchars($user['mdp_temp'] ?? '(choisi à l\'inscription)') . '</div>';
        $body .= '</div>';
        $body .= '<p style="color:#555;font-size:14px;">Connectez-vous sur TinyTrack avec ces coordonnees.</p>';
        $body .= '</div>';
        $body .= '<div style="background:#f8f9fa;padding:12px;text-align:center;font-size:12px;color:#888;">TinyTrack 2026 - ESPRIT 2A19</div>';
        $body .= '</div></body></html>';

        // Send via SMTP
        $smtp_user = 'eya.belhajmabrrouk@gmail.com';
        $smtp_pass = 'owclpimeecveksjf';

        $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false]]);
        $socket = @stream_socket_client('ssl://smtp.gmail.com:465', $errno, $errstr, 10, STREAM_CLIENT_CONNECT, $ctx);
        if ($socket) {
            fgets($socket, 515);
            fputs($socket, "EHLO localhost\r\n");
            while ($line = fgets($socket, 515)) { if (substr($line, 3, 1) == ' ') break; }
            fputs($socket, "AUTH LOGIN\r\n"); fgets($socket, 515);
            fputs($socket, base64_encode($smtp_user) . "\r\n"); fgets($socket, 515);
            fputs($socket, base64_encode($smtp_pass) . "\r\n"); fgets($socket, 515);
            fputs($socket, "MAIL FROM:<{$smtp_user}>\r\n"); fgets($socket, 515);
            fputs($socket, "RCPT TO:<{$user['email']}>\r\n"); fgets($socket, 515);
            fputs($socket, "DATA\r\n"); fgets($socket, 515);
            $msg = "From: TinyTrack <{$smtp_user}>\r\nTo: {$user['email']}\r\nSubject: {$subject}\r\nMIME-Version: 1.0\r\nContent-Type: text/html; charset=utf-8\r\n\r\n{$body}\r\n.\r\n";
            fputs($socket, $msg); fgets($socket, 515);
            fputs($socket, "QUIT\r\n"); fclose($socket);
        }
    }

    // Clear temp password for security
    $db->prepare("UPDATE user SET mdp_temp = NULL WHERE id = :id")->execute([':id' => $id]);

    header('Location: approbation.php?msg=approved');
    exit;
}

// Reject account
if (isset($_GET['reject']) && is_numeric($_GET['reject'])) {
    $id = (int)$_GET['reject'];
    $db->prepare("DELETE FROM user WHERE id = :id AND statut = 'en_attente'")->execute([':id' => $id]);
    header('Location: approbation.php?msg=rejected');
    exit;
}

// Get pending accounts
$stmt = $db->query("SELECT * FROM user WHERE statut = 'en_attente' ORDER BY created_at DESC");
$pendingUsers = $stmt->fetchAll();

// Get recently approved
$stmt = $db->query("SELECT * FROM user WHERE statut = 'actif' AND role != 'admin' ORDER BY created_at DESC LIMIT 10");
$activeUsers = $stmt->fetchAll();

include 'template/header.php';
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
                    <a href="approbation.php?approve=<?= $u['id'] ?>" class="btn btn-sm btn-success" style="border-radius:20px;" onclick="return confirm('Approuver ce compte ?')">
                      <i class="fas fa-check"></i> Approuver
                    </a>
                    <a href="approbation.php?reject=<?= $u['id'] ?>" class="btn btn-sm btn-danger" style="border-radius:20px;" onclick="return confirm('Refuser et supprimer ce compte ?')">
                      <i class="fas fa-times"></i>
                    </a>
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

<?php include 'template/footer.php'; ?>

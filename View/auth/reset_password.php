<?php
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';

$token = $_GET['token'] ?? $_POST['token'] ?? '';
$error = null;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $authCtrl = new AuthController();
    $result = $authCtrl->resetPassword(
        $token,
        $_POST['mot_de_passe'] ?? '',
        $_POST['confirm_mdp'] ?? ''
    );

    if ($result['success']) {
        $success = true;
    } else {
        $error = $result['error'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Réinitialiser le mot de passe</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/TinyTrack/assets/css/playful.css?v=3">
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{
      font-family:'Nunito',sans-serif;min-height:100vh;
      background:linear-gradient(135deg,#FFF4D6 0%,#FFE8EC 45%,#E0F4F1 100%);
      display:flex;align-items:center;justify-content:center;
      padding:2rem 1rem;position:relative;overflow-x:hidden
    }
    .bg-deco{position:fixed;pointer-events:none;z-index:0}
    .bg-deco svg{display:block;width:100%;height:100%}
    .bd-blob-1{top:-120px;right:-120px;width:360px;height:360px;opacity:0.42;animation:morph 20s ease-in-out infinite}
    .bd-blob-2{bottom:-140px;left:-140px;width:400px;height:400px;opacity:0.35;animation:morph 24s ease-in-out infinite 3s}
    .bd-star-1{top:8%;left:6%;width:62px;height:62px;opacity:0.55;animation:spinSlow 22s linear infinite}
    .bd-star-2{bottom:12%;right:8%;width:50px;height:50px;opacity:0.5;animation:spinSlow 26s linear infinite reverse}

    .rp-card{
      position:relative;z-index:10;width:480px;max-width:100%;
      background:#fff;border-radius:32px;
      box-shadow:0 25px 60px rgba(0,0,0,0.1);overflow:hidden;
      animation:cardIn 0.6s cubic-bezier(0.34,1.56,0.64,1)
    }
    @keyframes cardIn{0%{opacity:0;transform:translateY(20px)}100%{opacity:1;transform:translateY(0)}}

    .card-top{
      position:relative;overflow:hidden;text-align:center;
      padding:2.2rem 2rem 2.4rem;
      background:linear-gradient(135deg,#FFE8EC 0%,#FFF4D6 55%,#E0F4F1 100%);
    }
    .card-top::before{content:'';position:absolute;top:-40px;right:-40px;width:150px;height:150px;background:radial-gradient(circle at 30% 30%,#FFD93D 0%,#FFA726 70%);border-radius:50%;opacity:0.35}
    .card-top::after{content:'';position:absolute;bottom:-40px;left:-30px;width:130px;height:130px;background:radial-gradient(circle at 70% 70%,#FF8FAB 0%,#9C7CDB 80%);border-radius:50%;opacity:0.3}
    .card-top > *{position:relative;z-index:1}
    .icon-top{
      width:72px;height:72px;border-radius:50%;background:#fff;
      display:inline-flex;align-items:center;justify-content:center;
      font-size:2rem;box-shadow:0 6px 16px rgba(0,0,0,0.08);
      animation:logoFloat 3.5s ease-in-out infinite
    }
    @keyframes logoFloat{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-6px) rotate(4deg)}}
    .card-top h2{font-family:'Fredoka One',cursive;color:#2D3436;font-size:1.7rem;margin-top:0.7rem}
    .card-top h2 .accent{color:#FF6B9D}
    .card-top p{color:#555;font-size:0.9rem;font-weight:600;margin-top:0.4rem}

    .rp-body{padding:1.6rem 2rem 1.4rem}

    .form-group-r{margin-bottom:0.9rem}
    .form-group-r label{font-weight:800;font-size:0.78rem;color:#666;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;margin-bottom:0.4rem}
    .form-group-r .form-control{
      border:2px solid #EAEAEA;border-radius:16px;padding:0.85rem 1.1rem;
      font-size:0.98rem;background:#FAFAFA;width:100%;
      font-family:'Nunito',sans-serif;font-weight:600;transition:all 0.25s
    }
    .form-group-r .form-control:focus{
      outline:none;background:#fff;
      border-color:#FFD93D;box-shadow:0 0 0 4px rgba(255,217,61,0.18)
    }

    .strength-bar{margin-top:0.45rem;display:flex;gap:3px;align-items:center}
    .strength-bar .bar{flex:1;height:4px;border-radius:2px;background:#E0E0E0;transition:background 0.25s}
    .strength-bar span{font-size:0.72rem;font-weight:800;margin-left:0.3rem;color:#999}

    .btn-chunky-submit{
      width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
      background:#FF8FAB;color:#fff;border:none;border-radius:50px;
      padding:0.95rem;font-weight:800;font-size:1rem;cursor:pointer;
      font-family:'Nunito',sans-serif;transition:all 0.15s ease;
      box-shadow:0 6px 0 #E91E63,0 10px 20px rgba(233,30,99,0.25);
      margin-top:0.4rem
    }
    .btn-chunky-submit:hover{transform:translateY(2px);box-shadow:0 4px 0 #E91E63,0 6px 15px rgba(233,30,99,0.3)}
    .btn-chunky-submit:active{transform:translateY(4px);box-shadow:0 2px 0 #E91E63}

    .btn-go-login{
      display:inline-flex;align-items:center;gap:8px;margin-top:1.2rem;
      background:#26A69A;color:#fff;border:none;border-radius:50px;
      padding:0.85rem 2rem;font-weight:800;font-size:0.95rem;
      cursor:pointer;text-decoration:none;font-family:'Nunito',sans-serif;
      box-shadow:0 6px 0 #00796B,0 10px 20px rgba(0,121,107,0.25);
      transition:all 0.15s ease
    }
    .btn-go-login:hover{transform:translateY(2px);box-shadow:0 4px 0 #00796B;color:#fff}

    .alert{border-radius:14px;border:none;font-weight:700;font-size:0.85rem;padding:0.7rem 1rem;margin-bottom:1rem;display:flex;align-items:flex-start;gap:8px}
    .alert-danger{background:#FFEBEE;color:#C62828}

    .success-box{text-align:center;padding:1rem 0}
    .success-box .icon{font-size:4rem;margin-bottom:0.6rem;animation:sb 1s cubic-bezier(0.34,1.56,0.64,1)}
    @keyframes sb{0%{transform:scale(0) rotate(-180deg)}100%{transform:scale(1) rotate(0)}}
    .success-box h3{font-family:'Fredoka One',cursive;color:#2E7D32;font-size:1.4rem;margin-bottom:0.5rem}
    .success-box p{color:#555;font-size:0.9rem;line-height:1.5}

    .match-note{font-size:0.74rem;font-weight:800;margin-top:0.3rem;min-height:1em}
  </style>
</head>
<body>

<div class="bg-deco bd-blob-1"><svg viewBox="0 0 200 200"><path fill="#FFD93D" d="M43.8,-58.6C56.4,-49.3,65.8,-35.1,70.2,-19.6C74.6,-4.1,74,12.7,67.6,27.1C61.1,41.5,48.7,53.5,34.2,61.5C19.8,69.5,3.2,73.5,-13.3,71.4C-29.8,69.3,-46.3,61.2,-58,48.3C-69.7,35.4,-76.6,17.7,-75.9,0.4C-75.2,-16.9,-66.8,-33.8,-54.2,-43.4C-41.5,-53,-24.7,-55.4,-8.8,-44.9C7.1,-34.4,31.2,-67.9,43.8,-58.6Z" transform="translate(100 100)"/></svg></div>
<div class="bg-deco bd-blob-2"><svg viewBox="0 0 200 200"><path fill="#FF8FAB" d="M41.3,-60.1C52.1,-51.9,58.2,-37.4,63.6,-22.3C69,-7.2,73.7,8.5,70.5,22.8C67.2,37.1,56,50,42.3,58.6C28.6,67.1,12.5,71.3,-3.5,75.5C-19.5,79.8,-39,84.1,-51.3,75.4C-63.6,66.8,-68.7,45.2,-72.5,25.3C-76.3,5.4,-78.7,-12.9,-71.4,-25.9C-64.2,-38.9,-47.3,-46.7,-32.4,-53.8C-17.5,-60.8,-4.6,-67.2,8.5,-68.8C21.5,-70.3,30.5,-68.2,41.3,-60.1Z" transform="translate(100 100)"/></svg></div>
<div class="bg-deco bd-star-1"><svg viewBox="0 0 24 24" fill="#FFD93D"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="bg-deco bd-star-2"><svg viewBox="0 0 24 24" fill="#9C7CDB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>

<div class="rp-card">
  <div class="card-top">
    <div class="icon-top">&#x1F510;</div>
    <h2>Nouveau <span class="accent">mot de passe</span></h2>
    <p>Choisissez un mot de passe solide pour votre compte TinyTrack.</p>
  </div>

  <div class="rp-body">
    <?php if ($success): ?>
      <div class="success-box">
        <div class="icon">&#x2705;</div>
        <h3>Mot de passe mis a jour !</h3>
        <p>Vous pouvez maintenant vous connecter avec votre nouveau mot de passe.</p>
        <a href="login.php" class="btn-go-login"><i class="fas fa-sign-in-alt"></i> Se connecter</a>
      </div>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle" style="margin-top:2px"></i> <span><?= htmlspecialchars($error) ?></span></div>
      <?php endif; ?>

      <form method="POST" novalidate>
        <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

        <div class="form-group-r">
          <label><i class="fas fa-lock" style="color:#FF8FAB"></i> Nouveau mot de passe</label>
          <input type="password" name="mot_de_passe" id="mdp" class="form-control" placeholder="Au moins 6 caracteres" oninput="checkStrength()" autofocus>
          <div class="strength-bar" id="strength-wrap">
            <div class="bar" id="bar1"></div>
            <div class="bar" id="bar2"></div>
            <div class="bar" id="bar3"></div>
            <div class="bar" id="bar4"></div>
            <span id="strength-text"></span>
          </div>
        </div>

        <div class="form-group-r">
          <label><i class="fas fa-lock" style="color:#FF8FAB"></i> Confirmer</label>
          <input type="password" name="confirm_mdp" id="confirm" class="form-control" placeholder="Retapez le mot de passe" oninput="checkMatch()">
          <div class="match-note" id="match-note"></div>
        </div>

        <button type="submit" class="btn-chunky-submit"><i class="fas fa-check"></i> Mettre a jour</button>
      </form>
    <?php endif; ?>
  </div>
</div>

<script>
function checkStrength(){
  var pwd = document.getElementById('mdp').value;
  var score = 0;
  if (pwd.length >= 6) score++;
  if (pwd.length >= 10) score++;
  if (/[A-Z]/.test(pwd) && /[a-z]/.test(pwd)) score++;
  if (/[0-9]/.test(pwd)) score++;
  if (/[^A-Za-z0-9]/.test(pwd)) score++;

  var level = 0;
  if (score <= 1) level = 1;
  else if (score <= 2) level = 2;
  else if (score <= 3) level = 3;
  else level = 4;
  if (pwd.length === 0) level = 0;

  var colors = ['#E0E0E0','#EF5350','#FFA726','#FFD93D','#4CAF50'];
  var labels = ['','Faible','Moyen','Bon','Excellent'];

  for (var i = 1; i <= 4; i++) {
    document.getElementById('bar'+i).style.background = (i <= level) ? colors[level] : '#E0E0E0';
  }
  var txt = document.getElementById('strength-text');
  txt.textContent = labels[level];
  txt.style.color = colors[level] || '#999';
  checkMatch();
}
function checkMatch(){
  var a = document.getElementById('mdp').value;
  var b = document.getElementById('confirm').value;
  var note = document.getElementById('match-note');
  if (b.length === 0) { note.innerHTML = ''; return; }
  if (a === b) note.innerHTML = '<span style="color:#4CAF50"><i class="fas fa-check-circle"></i> Correspond</span>';
  else note.innerHTML = '<span style="color:#EF5350"><i class="fas fa-times-circle"></i> Ne correspond pas</span>';
}
</script>
</body>
</html>

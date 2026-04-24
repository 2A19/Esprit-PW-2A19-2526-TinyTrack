<?php
require_once __DIR__ . '/../../Controller/AuthController.php';

$activeTab = $_POST['role'] ?? ($_GET['tab'] ?? 'educateur');
$errors = [];
$success = null;
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old = $_POST;
    $authCtrl = new AuthController();
    $result = $authCtrl->register($_POST);

    if ($result['success']) {
        $success = $result['role'];
    } else {
        $errors = $result['errors'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Inscription</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/TinyTrack/assets/css/playful.css?v=3">
  <?php require_once __DIR__ . '/../../config/google_oauth.php'; ?>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{
      font-family:'Nunito',sans-serif;min-height:100vh;overflow-y:auto;
      background:linear-gradient(135deg,#FFF4D6 0%,#FFE8EC 45%,#E0F4F1 100%);
      display:flex;align-items:flex-start;justify-content:center;
      padding:2rem 1rem;position:relative
    }

    /* === KIDS-STORE DECO === */
    .reg-deco{position:fixed;pointer-events:none;z-index:0}
    .reg-deco svg{display:block;width:100%;height:100%}
    .rd-blob-1{top:-120px;right:-120px;width:340px;height:340px;opacity:0.42;animation:morph 20s ease-in-out infinite}
    .rd-blob-2{bottom:-140px;left:-140px;width:400px;height:400px;opacity:0.35;animation:morph 24s ease-in-out infinite 3s}
    .rd-star-1{top:6%;left:5%;width:62px;height:62px;opacity:0.55;animation:spinSlow 22s linear infinite}
    .rd-star-2{top:20%;right:6%;width:46px;height:46px;opacity:0.5;animation:spinSlow 26s linear infinite reverse}
    .rd-star-3{bottom:10%;right:12%;width:52px;height:52px;opacity:0.5;animation:spinSlow 24s linear infinite}
    .rd-arc-1{top:45%;left:2%;width:100px;height:65px;opacity:0.5;animation:float 10s ease-in-out infinite}
    .rd-arc-2{top:70%;right:3%;width:120px;height:75px;opacity:0.5;animation:float 12s ease-in-out infinite 2s}
    .rd-squiggle-1{top:35%;right:20%;width:80px;height:28px;opacity:0.45;animation:float 11s ease-in-out infinite 1s}
    .rd-dot-1{top:28%;left:45%;width:12px;height:12px;border-radius:50%;background:#FFD93D;opacity:0.5;animation:float 7s ease-in-out infinite}
    .rd-dot-2{top:78%;left:38%;width:10px;height:10px;border-radius:50%;background:#FF8FAB;opacity:0.5;animation:float 8s ease-in-out infinite 1s}

    /* === CARD === */
    .reg-card{
      position:relative;z-index:10;width:560px;max-width:100%;
      background:#fff;border-radius:32px;
      box-shadow:0 25px 60px rgba(0,0,0,0.1),0 0 0 1px rgba(255,255,255,0.8);
      overflow:hidden;
      animation:ci 0.7s cubic-bezier(0.34,1.56,0.64,1)
    }
    @keyframes ci{0%{opacity:0;transform:scale(0.95) translateY(20px)}100%{opacity:1;transform:scale(1) translateY(0)}}

    /* Card top — pastel gradient + decorative circles */
    .card-top{
      position:relative;overflow:hidden;text-align:center;
      padding:2rem 2rem 2.4rem;
      background:linear-gradient(135deg,#FFE8EC 0%,#FFF4D6 55%,#E0F4F1 100%);
    }
    .card-top::before{
      content:'';position:absolute;top:-40px;right:-40px;
      width:150px;height:150px;
      background:radial-gradient(circle at 30% 30%,#FFD93D 0%,#FFA726 70%);
      border-radius:50%;opacity:0.35
    }
    .card-top::after{
      content:'';position:absolute;bottom:-40px;left:-30px;
      width:130px;height:130px;
      background:radial-gradient(circle at 70% 70%,#FF8FAB 0%,#9C7CDB 80%);
      border-radius:50%;opacity:0.3
    }
    .card-top > *{position:relative;z-index:1}
    .card-top h2{
      font-family:'Fredoka One',cursive;color:#2D3436;
      font-size:1.8rem;margin-top:0.2rem;line-height:1
    }
    .card-top h2 .accent-pink{color:#FF6B9D}
    .card-top p{color:#555;font-size:0.88rem;font-weight:600;margin-top:0.4rem}
    .card-top .emoji-top{font-size:2.6rem}

    /* === PILL TABS === */
    .reg-tabs{
      display:flex;padding:1rem 1.8rem 0;gap:0.5rem
    }
    .reg-tab{
      flex:1;padding:0.75rem 0.5rem;cursor:pointer;
      font-weight:800;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.06em;
      color:#888;border:2px solid transparent;border-radius:18px;
      transition:all 0.25s ease;background:#F7F7F9;outline:none;
      display:flex;flex-direction:column;align-items:center;gap:0.2rem
    }
    .reg-tab:hover{color:#555;background:#EFEFF3;transform:translateY(-1px)}
    .reg-tab .tab-emoji{font-size:1.5rem;transition:transform 0.25s}
    .reg-tab:hover .tab-emoji{transform:scale(1.15) rotate(-5deg)}
    .tab-edu.active{background:#F3E8FF;color:#6A1B9A!important;border-color:#9C7CDB;box-shadow:0 4px 0 #6A1B9A}
    .tab-par.active{background:#FFE4EC;color:#C2185B!important;border-color:#FF8FAB;box-shadow:0 4px 0 #C2185B}

    /* === PANELS === */
    .reg-panel{display:none;padding:1.3rem 2rem}
    .reg-panel.active{display:block;animation:fi 0.35s cubic-bezier(0.34,1.56,0.64,1)}
    @keyframes fi{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

    /* === FORMS === */
    .form-group-r{margin-bottom:0.85rem}
    .form-group-r label{
      font-weight:800;font-size:0.76rem;color:#666;
      display:flex;align-items:center;gap:6px;margin-bottom:0.3rem;
      text-transform:uppercase;letter-spacing:0.04em
    }
    .form-group-r .form-control{
      border:2px solid #EAEAEA;border-radius:14px;
      padding:0.7rem 1rem;font-size:0.92rem;
      transition:all 0.25s;background:#FAFAFA;width:100%;
      font-family:'Nunito',sans-serif;font-weight:600
    }
    .form-group-r .form-control:focus{
      outline:none;background:#fff;
      border-color:#FFD93D;box-shadow:0 0 0 4px rgba(255,217,61,0.18)
    }
    .form-control.is-invalid{border-color:#EF5350!important;background:#FFEBEE}

    /* === CHUNKY REGISTER BUTTONS === */
    .btn-register{
      color:#fff;border:none;border-radius:50px;
      padding:0.95rem 1rem;font-weight:800;font-size:1rem;width:100%;
      cursor:pointer;transition:all 0.15s ease;
      font-family:'Nunito',sans-serif;
      display:flex;align-items:center;justify-content:center;gap:8px
    }
    .btn-register:hover{transform:translateY(2px)}
    .btn-register:active{transform:translateY(4px)}
    .btn-edu{
      background:#9C7CDB;
      box-shadow:0 6px 0 #6A1B9A,0 10px 20px rgba(106,27,154,0.25)
    }
    .btn-edu:hover{box-shadow:0 4px 0 #6A1B9A,0 6px 15px rgba(106,27,154,0.3)}
    .btn-edu:active{box-shadow:0 2px 0 #6A1B9A}
    .btn-par{
      background:#FF8FAB;
      box-shadow:0 6px 0 #E91E63,0 10px 20px rgba(233,30,99,0.25)
    }
    .btn-par:hover{box-shadow:0 4px 0 #E91E63,0 6px 15px rgba(233,30,99,0.3)}
    .btn-par:active{box-shadow:0 2px 0 #E91E63}

    .alert{border-radius:14px;border:none;font-weight:700;font-size:0.85rem}
    .alert-danger{background:#FFEBEE;color:#C62828}

    /* === SUCCESS === */
    .success-box{text-align:center;padding:2.4rem 2rem 1.8rem}
    .success-box .icon{font-size:4rem;margin-bottom:0.7rem;animation:sb 1s ease}
    @keyframes sb{0%{transform:scale(0) rotate(-180deg)}60%{transform:scale(1.2) rotate(10deg)}100%{transform:scale(1) rotate(0)}}
    .success-box h3{font-family:'Fredoka One',cursive;color:#2E7D32;font-size:1.5rem;margin-bottom:0.6rem}
    .success-box p{color:#555;font-size:0.9rem;line-height:1.5}
    .success-box .code-display{background:#E8F5E9;border:3px dashed #4CAF50;border-radius:18px;padding:1rem;font-family:'Courier New',monospace;font-size:1.6rem;font-weight:800;color:#2E7D32;margin:1rem 0}
    .success-box .warning{
      background:#FFF8E1;border-radius:14px;padding:0.7rem 1rem;
      font-size:0.82rem;color:#F57F17;margin-top:0.8rem;font-weight:700;
      border:1px solid #FFE082;display:inline-flex;align-items:center;gap:6px
    }
    .btn-go-login{
      display:inline-flex;align-items:center;gap:8px;margin-top:1.2rem;
      background:#26A69A;color:#fff;border:none;border-radius:50px;
      padding:0.85rem 2rem;font-weight:800;font-size:0.95rem;
      cursor:pointer;text-decoration:none;font-family:'Nunito',sans-serif;
      box-shadow:0 6px 0 #00796B,0 10px 20px rgba(0,121,107,0.25);
      transition:all 0.15s ease
    }
    .btn-go-login:hover{transform:translateY(2px);box-shadow:0 4px 0 #00796B,0 6px 15px rgba(0,121,107,0.3);color:#fff}
    .btn-go-login:active{transform:translateY(4px);box-shadow:0 2px 0 #00796B}

    .card-footer-r{text-align:center;padding:0.6rem 2rem 1.4rem;font-size:0.88rem;color:#888;font-weight:600}
    .card-footer-r a{
      color:#26A69A;font-weight:800;text-decoration:none;position:relative
    }
    .card-footer-r a::after{
      content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;
      background:#26A69A;transition:width 0.3s
    }
    .card-footer-r a:hover::after{width:100%}

    @media(max-width:600px){
      .card-top{padding:1.6rem 1.2rem 2rem}
      .card-top h2{font-size:1.5rem}
      .reg-panel{padding:1.2rem 1.3rem}
      .reg-tabs{padding:0.8rem 1.2rem 0}
    }

    /* === OR DIVIDER + GOOGLE BUTTON === */
    .or-divider{
      display:flex;align-items:center;gap:12px;
      color:#aaa;font-size:0.75rem;font-weight:800;
      text-transform:uppercase;letter-spacing:0.1em;
      margin:1rem 0 0.9rem
    }
    .or-divider::before, .or-divider::after{
      content:'';flex:1;height:1px;background:linear-gradient(90deg,transparent,#ddd,transparent)
    }
    .btn-google{
      width:100%;display:flex;align-items:center;justify-content:center;gap:10px;
      background:#fff;color:#3c4043;border:1px solid #dadce0;border-radius:50px;
      padding:0.8rem 1rem;font-family:'Nunito',sans-serif;font-weight:700;font-size:0.95rem;
      cursor:pointer;transition:all 0.15s ease;
      box-shadow:0 3px 0 #dadce0,0 6px 14px rgba(0,0,0,0.05)
    }
    .btn-google:hover{transform:translateY(1px);box-shadow:0 2px 0 #dadce0,0 4px 10px rgba(0,0,0,0.08);background:#f8f9fa}
    .btn-google:active{transform:translateY(2px);box-shadow:0 1px 0 #dadce0}
    .google-locked{
      background:#F0F9F7;border:2px dashed #26A69A;border-radius:14px;
      padding:0.7rem 1rem;font-size:0.8rem;color:#00796B;
      font-weight:700;margin:0.8rem 0;display:flex;align-items:center;gap:8px
    }
  </style>
</head>
<body>

<!-- Kids-store decorations -->
<div class="reg-deco rd-blob-1">
  <svg viewBox="0 0 200 200"><path fill="#FFD93D" d="M43.8,-58.6C56.4,-49.3,65.8,-35.1,70.2,-19.6C74.6,-4.1,74,12.7,67.6,27.1C61.1,41.5,48.7,53.5,34.2,61.5C19.8,69.5,3.2,73.5,-13.3,71.4C-29.8,69.3,-46.3,61.2,-58,48.3C-69.7,35.4,-76.6,17.7,-75.9,0.4C-75.2,-16.9,-66.8,-33.8,-54.2,-43.4C-41.5,-53,-24.7,-55.4,-8.8,-44.9C7.1,-34.4,31.2,-67.9,43.8,-58.6Z" transform="translate(100 100)"/></svg>
</div>
<div class="reg-deco rd-blob-2">
  <svg viewBox="0 0 200 200"><path fill="#FF8FAB" d="M41.3,-60.1C52.1,-51.9,58.2,-37.4,63.6,-22.3C69,-7.2,73.7,8.5,70.5,22.8C67.2,37.1,56,50,42.3,58.6C28.6,67.1,12.5,71.3,-3.5,75.5C-19.5,79.8,-39,84.1,-51.3,75.4C-63.6,66.8,-68.7,45.2,-72.5,25.3C-76.3,5.4,-78.7,-12.9,-71.4,-25.9C-64.2,-38.9,-47.3,-46.7,-32.4,-53.8C-17.5,-60.8,-4.6,-67.2,8.5,-68.8C21.5,-70.3,30.5,-68.2,41.3,-60.1Z" transform="translate(100 100)"/></svg>
</div>
<div class="reg-deco rd-star-1"><svg viewBox="0 0 24 24" fill="#FFD93D"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="reg-deco rd-star-2"><svg viewBox="0 0 24 24" fill="#FF8FAB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="reg-deco rd-star-3"><svg viewBox="0 0 24 24" fill="#9C7CDB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="reg-deco rd-arc-1">
  <svg viewBox="0 0 100 65"><path d="M5 60 A45 45 0 0 1 95 60" fill="none" stroke="#FF8FAB" stroke-width="8" stroke-linecap="round"/><path d="M20 60 A30 30 0 0 1 80 60" fill="none" stroke="#FFD93D" stroke-width="8" stroke-linecap="round"/><path d="M34 60 A16 16 0 0 1 66 60" fill="none" stroke="#26A69A" stroke-width="8" stroke-linecap="round"/></svg>
</div>
<div class="reg-deco rd-arc-2">
  <svg viewBox="0 0 120 75"><path d="M5 70 A55 55 0 0 1 115 70" fill="none" stroke="#9C7CDB" stroke-width="9" stroke-linecap="round"/><path d="M22 70 A38 38 0 0 1 98 70" fill="none" stroke="#FFA726" stroke-width="9" stroke-linecap="round"/><path d="M40 70 A20 20 0 0 1 80 70" fill="none" stroke="#5B9BD5" stroke-width="9" stroke-linecap="round"/></svg>
</div>
<div class="reg-deco rd-squiggle-1">
  <svg viewBox="0 0 80 28"><path d="M2 14 Q12 2 22 14 T44 14 T66 14 T78 14" stroke="#26A69A" stroke-width="4" fill="none" stroke-linecap="round"/></svg>
</div>
<div class="reg-deco rd-dot-1"></div>
<div class="reg-deco rd-dot-2"></div>

<div class="reg-card">
  <div class="card-top">
    <div class="emoji-top">&#x1F4DD;</div>
    <h2>Créer un <span class="accent-pink">compte</span></h2>
    <p>Remplissez le formulaire — votre compte sera activé par l'administration</p>
  </div>

  <?php if ($success): ?>
    <div class="success-box">
      <div class="icon">&#x2705;</div>
      <h3>Demande envoyée !</h3>
      <p>Votre compte <strong><?= $success === 'educateur' ? 'Éducateur' : 'Parent' ?></strong> a été créé avec succès.</p>
      <p>Il est actuellement <strong style="color:#E65100;">en attente d'approbation</strong> par l'administration.</p>
      <div class="warning"><i class="fas fa-info-circle"></i> Vous recevrez un email dès que votre compte sera activé.</div>
      <a href="login.php" class="btn-go-login"><i class="fas fa-sign-in-alt"></i> Retour au login</a>
    </div>
  <?php else: ?>

    <div class="reg-tabs">
      <button class="reg-tab tab-edu <?= $activeTab === 'educateur' ? 'active' : '' ?>" onclick="switchTab('educateur')"><span class="tab-emoji">&#x1F4DA;</span>Éducateur</button>
      <button class="reg-tab tab-par <?= $activeTab === 'parent' ? 'active' : '' ?>" onclick="switchTab('parent')"><span class="tab-emoji">&#x1F468;&#x200D;&#x1F467;</span>Parent</button>
    </div>

    <!-- Google Sign-Up : prefills nom/prenom/email from verified Google profile -->
    <div style="padding:1rem 2rem 0">
      <div class="g_id_signin"
           data-type="standard"
           data-shape="pill"
           data-theme="outline"
           data-text="signup_with"
           data-size="large"
           data-locale="fr"
           data-width="400"></div>
      <div class="or-divider">ou remplir manuellement</div>
    </div>

    <?php if (!empty($errors)): ?>
      <div style="padding:0.8rem 2rem 0;"><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div></div>
    <?php endif; ?>

    <!-- EDUCATEUR FORM -->
    <div class="reg-panel <?= $activeTab === 'educateur' ? 'active' : '' ?>" id="rpanel-educateur">
      <form method="POST" novalidate onsubmit="return validerForm()">
        <input type="hidden" name="role" value="educateur">
        <input type="hidden" name="google_jwt" id="google_jwt_edu">
        <div class="row">
          <div class="col-6"><div class="form-group-r"><label><i class="fas fa-user" style="color:#9C7CDB;"></i> Nom</label><input type="text" name="nom" id="nom" class="form-control" placeholder="Votre nom" oninput="checkName(this)" value="<?= htmlspecialchars($old['nom'] ?? '') ?>"></div></div>
          <div class="col-6"><div class="form-group-r"><label><i class="fas fa-user" style="color:#9C7CDB;"></i> Prénom</label><input type="text" name="prenom" id="prenom" class="form-control" placeholder="Votre prénom" oninput="checkName(this)" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>"></div></div>
        </div>
        <div class="form-group-r"><label><i class="fas fa-envelope" style="color:#5B9BD5;"></i> Email</label><input type="text" name="email" id="email" class="form-control" placeholder="votre@email.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>"></div>
        <div class="form-group-r">
          <label><i class="fas fa-phone" style="color:#4CAF50;"></i> Téléphone</label>
          <div style="display:flex;gap:0;">
            <span style="background:#E8F5E9;color:#2E7D32;padding:0.65rem 0.8rem;border-radius:14px 0 0 14px;border:2px solid #C8E6C9;border-right:none;font-weight:700;font-size:0.85rem;">+216</span>
            <input type="text" name="telephone" id="tel_edu" class="form-control" placeholder="XX XXX XXX" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>" style="border-radius:0 14px 14px 0;" oninput="checkTel('edu')">
          </div>
          <div id="tel_err_edu" style="font-size:0.72rem;font-weight:700;margin-top:0.2rem;"></div>
        </div>
        <div class="form-group-r">
          <label><i class="fas fa-lock" style="color:#FFA726;"></i> Mot de passe</label>
          <div style="position:relative;">
            <input type="password" name="mot_de_passe" id="mdp_edu" class="form-control" placeholder="Tapez ou générez un mot de passe" oninput="checkStrength('edu')" style="padding-right:90px;">
            <div style="position:absolute;right:5px;top:50%;transform:translateY(-50%);display:flex;gap:3px;">
              <button type="button" onclick="genMdp('edu')" class="btn btn-sm" style="background:#E8F5E9;color:#2E7D32;border-radius:8px;font-size:0.7rem;font-weight:700;border:1px solid #C8E6C9;padding:0.3rem 0.5rem;" title="Générer"><i class="fas fa-magic"></i></button>
              <button type="button" onclick="toggleVis('edu')" class="btn btn-sm" style="background:#F5F5F5;color:#666;border-radius:8px;font-size:0.7rem;border:1px solid #E0E0E0;padding:0.3rem 0.5rem;" title="Afficher"><i class="fas fa-eye" id="eye_edu"></i></button>
            </div>
          </div>
          <div class="strength-bar" id="strength_edu" style="margin-top:0.4rem;display:flex;gap:3px;align-items:center;">
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar1_edu"></div>
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar2_edu"></div>
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar3_edu"></div>
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar4_edu"></div>
            <span id="strength_text_edu" style="font-size:0.7rem;font-weight:700;margin-left:0.3rem;color:#999;"></span>
          </div>
        </div>
        <div class="form-group-r">
          <label><i class="fas fa-lock" style="color:#FFA726;"></i> Confirmer</label>
          <input type="password" name="confirm_mdp" id="confirm_edu" class="form-control" placeholder="Retapez le mot de passe">
          <div id="match_edu" style="font-size:0.72rem;font-weight:700;margin-top:0.2rem;"></div>
        </div>
        <button type="submit" class="btn-register btn-edu mt-2"><i class="fas fa-user-plus"></i> Créer mon compte éducateur</button>
      </form>
    </div>

    <!-- PARENT FORM -->
    <div class="reg-panel <?= $activeTab === 'parent' ? 'active' : '' ?>" id="rpanel-parent">
      <form method="POST" novalidate onsubmit="return validerForm()">
        <input type="hidden" name="role" value="parent">
        <input type="hidden" name="google_jwt" id="google_jwt_par">
        <div style="padding:0.5rem 0.8rem;background:linear-gradient(135deg,#FCE4EC,#FFF0F5);border-radius:10px;margin-bottom:0.8rem;font-size:0.78rem;color:#AD5D7E;border:1px solid #F8BBD0;"><i class="fas fa-info-circle"></i> Informations du parent</div>
        <div class="row">
          <div class="col-6"><div class="form-group-r"><label><i class="fas fa-user" style="color:#D4849B;"></i> Nom du parent</label><input type="text" name="nom" id="nom2" class="form-control" placeholder="Votre nom" oninput="checkName(this)" value="<?= htmlspecialchars($old['nom'] ?? '') ?>"></div></div>
          <div class="col-6"><div class="form-group-r"><label><i class="fas fa-user" style="color:#D4849B;"></i> Prénom du parent</label><input type="text" name="prenom" id="prenom2" class="form-control" placeholder="Votre prénom" oninput="checkName(this)" value="<?= htmlspecialchars($old['prenom'] ?? '') ?>"></div></div>
        </div>
        <div class="form-group-r"><label><i class="fas fa-envelope" style="color:#5B9BD5;"></i> Email</label><input type="text" name="email" id="email2" class="form-control" placeholder="votre@email.com" value="<?= htmlspecialchars($old['email'] ?? '') ?>"></div>
        <div class="form-group-r">
          <label><i class="fas fa-phone" style="color:#4CAF50;"></i> Téléphone</label>
          <div style="display:flex;gap:0;">
            <span style="background:#E8F5E9;color:#2E7D32;padding:0.65rem 0.8rem;border-radius:14px 0 0 14px;border:2px solid #C8E6C9;border-right:none;font-weight:700;font-size:0.85rem;">+216</span>
            <input type="text" name="telephone" id="tel_par" class="form-control" placeholder="XX XXX XXX" value="<?= htmlspecialchars($old['telephone'] ?? '') ?>" style="border-radius:0 14px 14px 0;" oninput="checkTel('par')">
          </div>
          <div id="tel_err_par" style="font-size:0.72rem;font-weight:700;margin-top:0.2rem;"></div>
        </div>
        <div style="padding:0.5rem 0.8rem;background:linear-gradient(135deg,#E3F2FD,#E8EAF6);border-radius:10px;margin-bottom:0.8rem;font-size:0.78rem;color:#5B7FA5;border:1px solid #BBDEFB;display:flex;justify-content:space-between;align-items:center;">
          <span><i class="fas fa-child"></i> Enfant(s) à inscrire</span>
          <button type="button" onclick="addEnfant()" style="background:#5B9BD5;color:#fff;border:none;border-radius:15px;padding:0.2rem 0.7rem;font-size:0.7rem;font-weight:700;cursor:pointer;"><i class="fas fa-plus"></i> Ajouter un enfant</button>
        </div>
        <div id="enfants-container">
          <div class="enfant-block" style="background:#f8f9fa;border-radius:12px;padding:0.8rem;margin-bottom:0.6rem;border:1px solid #eee;position:relative;">
            <div style="font-size:0.75rem;font-weight:700;color:#5B9BD5;margin-bottom:0.5rem;"><i class="fas fa-baby"></i> Enfant 1</div>
            <div class="row">
              <div class="col-3"><div class="form-group-r"><input type="text" name="enfants[0][nom]" class="form-control" placeholder="Nom" oninput="checkName(this)" style="font-size:0.85rem;padding:0.5rem 0.7rem;"></div></div>
              <div class="col-3"><div class="form-group-r"><input type="text" name="enfants[0][prenom]" class="form-control" placeholder="Prénom" oninput="checkName(this)" style="font-size:0.85rem;padding:0.5rem 0.7rem;"></div></div>
              <div class="col-2"><div class="form-group-r"><select name="enfants[0][sexe]" class="form-control" style="font-size:0.8rem;padding:0.5rem 0.3rem;"><option value="M">&#x1F466; Garçon</option><option value="F">&#x1F467; Fille</option></select></div></div>
              <div class="col-4"><div class="form-group-r">
                <div style="display:flex;gap:0.2rem;">
                  <select name="enfants[0][jour]" class="form-control" style="font-size:0.8rem;padding:0.4rem;">
                    <option value="">Jour</option>
                    <?php for($d=1;$d<=31;$d++): ?><option value="<?= $d ?>"><?= str_pad($d,2,'0',STR_PAD_LEFT) ?></option><?php endfor; ?>
                  </select>
                  <select name="enfants[0][mois]" class="form-control" style="font-size:0.8rem;padding:0.4rem;">
                    <option value="">Mois</option>
                    <?php for($m=1;$m<=12;$m++): ?><option value="<?= $m ?>"><?= str_pad($m,2,'0',STR_PAD_LEFT) ?></option><?php endfor; ?>
                  </select>
                  <select name="enfants[0][annee]" class="form-control" style="font-size:0.8rem;padding:0.4rem;">
                    <option value="">Année</option>
                    <?php for($y=date('Y');$y>=date('Y')-7;$y--): ?><option value="<?= $y ?>"><?= $y ?></option><?php endfor; ?>
                  </select>
                </div>
              </div></div>
            </div>
          </div>
        </div>
        <div class="form-group-r">
          <label><i class="fas fa-lock" style="color:#FFA726;"></i> Mot de passe</label>
          <div style="position:relative;">
            <input type="password" name="mot_de_passe" id="mdp_par" class="form-control" placeholder="Tapez ou générez un mot de passe" oninput="checkStrength('par')" style="padding-right:90px;">
            <div style="position:absolute;right:5px;top:50%;transform:translateY(-50%);display:flex;gap:3px;">
              <button type="button" onclick="genMdp('par')" class="btn btn-sm" style="background:#FCE4EC;color:#AD5D7E;border-radius:8px;font-size:0.7rem;font-weight:700;border:1px solid #F8BBD0;padding:0.3rem 0.5rem;" title="Générer"><i class="fas fa-magic"></i></button>
              <button type="button" onclick="toggleVis('par')" class="btn btn-sm" style="background:#F5F5F5;color:#666;border-radius:8px;font-size:0.7rem;border:1px solid #E0E0E0;padding:0.3rem 0.5rem;" title="Afficher"><i class="fas fa-eye" id="eye_par"></i></button>
            </div>
          </div>
          <div class="strength-bar" id="strength_par" style="margin-top:0.4rem;display:flex;gap:3px;align-items:center;">
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar1_par"></div>
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar2_par"></div>
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar3_par"></div>
            <div style="flex:1;height:4px;border-radius:2px;background:#E0E0E0;" id="bar4_par"></div>
            <span id="strength_text_par" style="font-size:0.7rem;font-weight:700;margin-left:0.3rem;color:#999;"></span>
          </div>
        </div>
        <div class="form-group-r">
          <label><i class="fas fa-lock" style="color:#FFA726;"></i> Confirmer</label>
          <input type="password" name="confirm_mdp" id="confirm_par" class="form-control" placeholder="Retapez le mot de passe">
          <div id="match_par" style="font-size:0.72rem;font-weight:700;margin-top:0.2rem;"></div>
        </div>
        <button type="submit" class="btn-register btn-par mt-2"><i class="fas fa-user-plus"></i> Créer mon compte parent</button>
      </form>
    </div>

  <?php endif; ?>

  <div class="card-footer-r">Déjà un compte ? <a href="login.php">Se connecter</a></div>
</div>

<script>
function switchTab(role){
  document.querySelectorAll('.reg-tab').forEach(t=>t.classList.remove('active'));
  document.querySelectorAll('.reg-panel').forEach(p=>p.classList.remove('active'));
  document.querySelector('.tab-'+(role==='educateur'?'edu':'par')).classList.add('active');
  document.getElementById('rpanel-'+role).classList.add('active');
}

// Generate secure password
function generatePassword(){
  var upper='ABCDEFGHJKLMNPQRSTUVWXYZ';
  var lower='abcdefghjkmnpqrstuvwxyz';
  var nums='23456789';
  var symbols='@#$!&*';
  var all=upper+lower+nums+symbols;
  // Ensure at least one of each type
  var pwd=upper[Math.floor(Math.random()*upper.length)]
    +lower[Math.floor(Math.random()*lower.length)]
    +nums[Math.floor(Math.random()*nums.length)]
    +symbols[Math.floor(Math.random()*symbols.length)];
  for(var i=0;i<8;i++) pwd+=all[Math.floor(Math.random()*all.length)];
  // Shuffle
  pwd=pwd.split('').sort(function(){return 0.5-Math.random()}).join('');
  return pwd;
}

function genMdp(type){
  var pwd=generatePassword();
  var field=document.getElementById('mdp_'+type);
  field.value=pwd;
  field.type='text'; // Show generated password
  document.getElementById('eye_'+type).className='fas fa-eye-slash';
  checkStrength(type);
  // Also fill confirm
  document.getElementById('confirm_'+type).value=pwd;
  document.getElementById('match_'+type).innerHTML='<span style="color:#4CAF50;"><i class="fas fa-check-circle"></i> Correspond</span>';
}

function toggleVis(type){
  var field=document.getElementById('mdp_'+type);
  var eye=document.getElementById('eye_'+type);
  if(field.type==='password'){field.type='text';eye.className='fas fa-eye-slash';}
  else{field.type='password';eye.className='fas fa-eye';}
}

// Password strength checker
function checkStrength(type){
  var pwd=document.getElementById('mdp_'+type).value;
  var score=0;
  if(pwd.length>=6) score++;
  if(pwd.length>=10) score++;
  if(/[A-Z]/.test(pwd)&&/[a-z]/.test(pwd)) score++;
  if(/[0-9]/.test(pwd)) score++;
  if(/[^A-Za-z0-9]/.test(pwd)) score++;

  var colors=['#E0E0E0','#EF5350','#FFA726','#FFD93D','#4CAF50'];
  var labels=['','Faible','Moyen','Bon','Excellent'];
  var labelColors=['','#EF5350','#FFA726','#FFD93D','#4CAF50'];

  var level=0;
  if(score<=1) level=1;
  else if(score<=2) level=2;
  else if(score<=3) level=3;
  else level=4;

  if(pwd.length===0) level=0;

  for(var i=1;i<=4;i++){
    document.getElementById('bar'+i+'_'+type).style.background=(i<=level)?colors[level]:'#E0E0E0';
    document.getElementById('bar'+i+'_'+type).style.transition='background 0.3s';
  }
  document.getElementById('strength_text_'+type).textContent=labels[level];
  document.getElementById('strength_text_'+type).style.color=labelColors[level];

  // Check confirm match
  checkMatch(type);
}

function checkMatch(type){
  var mdp=document.getElementById('mdp_'+type).value;
  var confirm=document.getElementById('confirm_'+type).value;
  var div=document.getElementById('match_'+type);
  if(confirm.length===0){div.innerHTML='';return;}
  if(mdp===confirm) div.innerHTML='<span style="color:#4CAF50;"><i class="fas fa-check-circle"></i> Correspond</span>';
  else div.innerHTML='<span style="color:#EF5350;"><i class="fas fa-times-circle"></i> Ne correspond pas</span>';
}

// Listen to confirm field + init date mask
document.addEventListener('DOMContentLoaded',function(){
  var c1=document.getElementById('confirm_edu');
  var c2=document.getElementById('confirm_par');
  if(c1) c1.addEventListener('input',function(){checkMatch('edu');});
  if(c2) c2.addEventListener('input',function(){checkMatch('par');});

});

// Check age from dropdown selects
function checkAgeDrop(){
  var jour=document.getElementById('enfant_jour').value;
  var mois=document.getElementById('enfant_mois').value;
  var annee=document.getElementById('enfant_annee').value;
  var div=document.getElementById('age_err_par');
  var hidden=document.getElementById('enfant_dob_par');

  if(!jour||!mois||!annee){div.innerHTML='';hidden.value='';return;}

  var dateStr=annee+'-'+String(mois).padStart(2,'0')+'-'+String(jour).padStart(2,'0');
  hidden.value=dateStr;

  var dob=new Date(dateStr);
  var now=new Date();

  if(isNaN(dob.getTime())||dob>now){
    div.innerHTML='<span style="color:#EF5350;"><i class="fas fa-times-circle"></i> Date invalide</span>';
    return;
  }

  var age=now.getFullYear()-dob.getFullYear();
  var m=now.getMonth()-dob.getMonth();
  if(m<0||(m===0&&now.getDate()<dob.getDate())) age--;

  if(age>6){
    div.innerHTML='<span style="color:#EF5350;"><i class="fas fa-times-circle"></i> L\'enfant ne doit pas dépasser 6 ans (âge: '+age+' ans)</span>';
  } else {
    var months=now.getMonth()-dob.getMonth()+(now.getFullYear()-dob.getFullYear())*12;
    if(months<0) months=0;
    var ageText=age>0?age+' an'+(age>1?'s':''):(months+' mois');
    div.innerHTML='<span style="color:#4CAF50;"><i class="fas fa-check-circle"></i> '+ageText+' — Éligible &#x2714;</span>';
  }
}

// Name validation (letters only)
function checkName(field){
  var val=field.value.trim();
  var regex=/^[a-zA-ZÀ-ÿ\s\-]+$/;
  if(val.length===0){field.style.borderColor='#E8E8E8';return;}
  if(!regex.test(val)){
    field.style.borderColor='#EF5350';
    field.style.boxShadow='0 0 0 3px rgba(239,68,68,0.1)';
  } else {
    field.style.borderColor='#4CAF50';
    field.style.boxShadow='0 0 0 3px rgba(76,175,80,0.1)';
  }
}

// Telephone validation (+216 + 8 digits)
function checkTel(type){
  var field=document.getElementById('tel_'+type);
  var div=document.getElementById('tel_err_'+type);
  var val=field.value.replace(/\s/g,'');
  if(val.length===0){div.innerHTML='';field.style.borderColor='#E8E8E8';return;}
  if(!/^\d{8}$/.test(val)){
    div.innerHTML='<span style="color:#EF5350;"><i class="fas fa-times-circle"></i> Le numéro doit contenir exactement 8 chiffres</span>';
    field.style.borderColor='#EF5350';
  } else {
    div.innerHTML='<span style="color:#4CAF50;"><i class="fas fa-check-circle"></i> Numéro valide</span>';
    field.style.borderColor='#4CAF50';
  }
}

// Add enfant dynamically
var enfantCount = 1;
function addEnfant() {
  enfantCount++;
  var container = document.getElementById('enfants-container');
  var years = '';
  var currentYear = new Date().getFullYear();
  for (var y = currentYear; y >= currentYear - 7; y--) { years += '<option value="' + y + '">' + y + '</option>'; }
  var jours = '';
  for (var d = 1; d <= 31; d++) { jours += '<option value="' + d + '">' + String(d).padStart(2, '0') + '</option>'; }
  var mois = '';
  for (var m = 1; m <= 12; m++) { mois += '<option value="' + m + '">' + String(m).padStart(2, '0') + '</option>'; }

  var idx = enfantCount - 1;
  var html = '<div class="enfant-block" style="background:#f8f9fa;border-radius:12px;padding:0.8rem;margin-bottom:0.6rem;border:1px solid #eee;position:relative;animation:fi 0.3s ease;">'
    + '<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:0.5rem;">'
    + '<span style="font-size:0.75rem;font-weight:700;color:#5B9BD5;"><i class="fas fa-baby"></i> Enfant ' + enfantCount + '</span>'
    + '<button type="button" onclick="removeEnfant(this)" style="background:#FFEBEE;color:#C62828;border:none;border-radius:50%;width:22px;height:22px;font-size:0.7rem;cursor:pointer;display:flex;align-items:center;justify-content:center;"><i class="fas fa-times"></i></button>'
    + '</div>'
    + '<div class="row">'
    + '<div class="col-3"><div class="form-group-r"><input type="text" name="enfants[' + idx + '][nom]" class="form-control" placeholder="Nom" oninput="checkName(this)" style="font-size:0.85rem;padding:0.5rem 0.7rem;"></div></div>'
    + '<div class="col-3"><div class="form-group-r"><input type="text" name="enfants[' + idx + '][prenom]" class="form-control" placeholder="Prénom" oninput="checkName(this)" style="font-size:0.85rem;padding:0.5rem 0.7rem;"></div></div>'
    + '<div class="col-2"><div class="form-group-r"><select name="enfants[' + idx + '][sexe]" class="form-control" style="font-size:0.8rem;padding:0.5rem 0.3rem;"><option value="M">&#x1F466; Garçon</option><option value="F">&#x1F467; Fille</option></select></div></div>'
    + '<div class="col-4"><div class="form-group-r"><div style="display:flex;gap:0.2rem;">'
    + '<select name="enfants[' + idx + '][jour]" class="form-control" style="font-size:0.8rem;padding:0.4rem;"><option value="">Jour</option>' + jours + '</select>'
    + '<select name="enfants[' + idx + '][mois]" class="form-control" style="font-size:0.8rem;padding:0.4rem;"><option value="">Mois</option>' + mois + '</select>'
    + '<select name="enfants[' + idx + '][annee]" class="form-control" style="font-size:0.8rem;padding:0.4rem;"><option value="">Année</option>' + years + '</select>'
    + '</div></div></div></div></div>';

  container.insertAdjacentHTML('beforeend', html);
}

function removeEnfant(btn) {
  btn.closest('.enfant-block').remove();
  // Re-number labels
  var blocks = document.querySelectorAll('.enfant-block');
  blocks.forEach(function(b, i) {
    var label = b.querySelector('span');
    if (label) label.innerHTML = '<i class="fas fa-baby"></i> Enfant ' + (i + 1);
  });
  enfantCount = blocks.length;
}

function validerForm(){
  // Check all enfant dates are not in the future
  var blocks = document.querySelectorAll('#enfants-container .enfant-block');
  if (blocks.length === 0) return true;

  var today = new Date();
  today.setHours(0,0,0,0);

  for (var i = 0; i < blocks.length; i++) {
    var selects = blocks[i].querySelectorAll('select');
    var jour = null, mois = null, annee = null;
    selects.forEach(function(s) {
      var name = s.getAttribute('name') || '';
      if (name.indexOf('[jour]') !== -1) jour = s.value;
      if (name.indexOf('[mois]') !== -1) mois = s.value;
      if (name.indexOf('[annee]') !== -1) annee = s.value;
    });

    if (jour && mois && annee) {
      var dob = new Date(annee, mois - 1, jour);
      if (dob > today) {
        alert('Enfant ' + (i+1) + ' : la date de naissance ne peut pas être dans le futur.');
        return false;
      }
      var age = today.getFullYear() - dob.getFullYear();
      var m = today.getMonth() - dob.getMonth();
      if (m < 0 || (m === 0 && today.getDate() < dob.getDate())) age--;
      if (age > 6) {
        alert('Enfant ' + (i+1) + ' : ne doit pas dépasser 6 ans (âge: ' + age + ' ans).');
        return false;
      }
    }
  }
  return true;
}

// === Google Sign-Up ===
// Called by Google Identity Services after the user picks an account.
// Prefills nom/prenom/email in BOTH forms (educateur and parent) and stores
// the raw JWT in a hidden field so the server can re-verify it.
function handleGoogleSignup(response){
  var jwt = response.credential;
  // Naive client-side decode — the server will re-verify signature+aud before trust.
  var parts = jwt.split('.');
  if (parts.length !== 3) return;
  var payload;
  try { payload = JSON.parse(atob(parts[1].replace(/-/g,'+').replace(/_/g,'/'))); }
  catch(e){ return; }

  var prenom = payload.given_name || '';
  var nom    = payload.family_name || '';
  var email  = payload.email || '';

  // Educateur form
  var fldSet1 = [['nom',nom],['prenom',prenom],['email',email]];
  fldSet1.forEach(function(p){ var el = document.getElementById(p[0]); if (el) { el.value = p[1]; el.readOnly = true; checkName(el); } });
  document.getElementById('google_jwt_edu').value = jwt;

  // Parent form
  var fldSet2 = [['nom2',nom],['prenom2',prenom],['email2',email]];
  fldSet2.forEach(function(p){ var el = document.getElementById(p[0]); if (el) { el.value = p[1]; el.readOnly = true; checkName(el); } });
  document.getElementById('google_jwt_par').value = jwt;

  // Visual confirmation
  var locked = document.createElement('div');
  locked.className = 'google-locked';
  locked.innerHTML = '<i class="fas fa-lock"></i> Identite verifiee via Google : <strong>' + email + '</strong>';
  var containers = document.querySelectorAll('.reg-panel');
  containers.forEach(function(c){
    if (!c.querySelector('.google-locked')) c.insertBefore(locked.cloneNode(true), c.firstChild);
  });
}

</script>

<!-- Google Identity Services : hidden initializer -->
<div id="g_id_onload"
     data-client_id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
     data-callback="handleGoogleSignup"
     data-auto_prompt="false"></div>
</body>
</html>

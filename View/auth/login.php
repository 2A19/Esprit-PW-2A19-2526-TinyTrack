<?php
// Vue passive — données injectées par AuthController::showLogin() / doLogin()
// Variables disponibles : $errors, $activeTab, $old
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Connexion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/TinyTrack/assets/css/playful.css?v=3">
  <?php require_once __DIR__ . '/../../config/google_oauth.php'; ?>
  <script src="https://accounts.google.com/gsi/client" async defer></script>
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{
      font-family:'Nunito',sans-serif;
      min-height:100vh;
      background:linear-gradient(135deg,#FFF4D6 0%,#FFE8EC 45%,#E0F4F1 100%);
      display:flex;align-items:center;justify-content:center;
      padding:2rem 1rem;position:relative;overflow-x:hidden;
    }

    /* === KIDS-STORE DECO (local instances, bigger than dashboard) === */
    .login-deco{position:fixed;pointer-events:none;z-index:0}
    .login-deco svg{display:block;width:100%;height:100%}
    .ld-blob-1{top:-120px;right:-120px;width:360px;height:360px;opacity:0.45;animation:morph 20s ease-in-out infinite}
    .ld-blob-2{bottom:-140px;left:-140px;width:420px;height:420px;opacity:0.38;animation:morph 24s ease-in-out infinite 3s}
    .ld-star-1{top:8%;left:6%;width:70px;height:70px;opacity:0.6;animation:spinSlow 22s linear infinite}
    .ld-star-2{top:18%;right:8%;width:50px;height:50px;opacity:0.55;animation:spinSlow 26s linear infinite reverse}
    .ld-star-3{bottom:14%;left:10%;width:56px;height:56px;opacity:0.55;animation:spinSlow 24s linear infinite}
    .ld-star-4{bottom:22%;right:12%;width:42px;height:42px;opacity:0.5;animation:spinSlow 28s linear infinite reverse}
    .ld-arc-1{top:38%;left:3%;width:110px;height:70px;opacity:0.55;animation:float 10s ease-in-out infinite}
    .ld-arc-2{top:62%;right:4%;width:130px;height:80px;opacity:0.55;animation:float 12s ease-in-out infinite 2s}
    .ld-squiggle-1{top:75%;left:15%;width:90px;height:30px;opacity:0.5;animation:float 11s ease-in-out infinite}
    .ld-dot-1{top:24%;left:40%;width:14px;height:14px;border-radius:50%;background:#FFD93D;opacity:0.5;animation:float 7s ease-in-out infinite}
    .ld-dot-2{top:72%;left:52%;width:12px;height:12px;border-radius:50%;background:#FF8FAB;opacity:0.5;animation:float 8s ease-in-out infinite 1s}
    .ld-dot-3{top:44%;right:30%;width:10px;height:10px;border-radius:50%;background:#26A69A;opacity:0.5;animation:float 9s ease-in-out infinite 2s}

    /* === LAYOUT === */
    .login-wrapper{display:flex;align-items:center;gap:0;z-index:10;position:relative;max-width:900px;width:100%}

    .char-side{
      width:260px;flex-shrink:0;margin-right:-40px;z-index:15;
      opacity:0;animation:charIn 1s cubic-bezier(0.34,1.56,0.64,1) forwards;
      filter:drop-shadow(0 12px 24px rgba(0,0,0,0.12))
    }
    .char-side img{width:100%;height:auto;animation:charIdle 3.5s ease-in-out infinite}
    @keyframes charIn{0%{opacity:0;transform:translateX(-100px) scale(0.85)}100%{opacity:1;transform:translateX(0) scale(1)}}
    @keyframes charIdle{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}

    /* === LOGIN CARD === */
    .login-card{
      position:relative;z-index:10;flex:1;max-width:500px;
      background:#fff;border-radius:32px;
      box-shadow:0 25px 60px rgba(0,0,0,0.1),0 0 0 1px rgba(255,255,255,0.8);
      overflow:hidden;
      opacity:0;transform:translateX(40px);
      animation:cardIn 0.7s cubic-bezier(0.34,1.56,0.64,1) 0.6s forwards
    }
    @keyframes cardIn{0%{opacity:0;transform:translateX(40px) scale(0.97)}100%{opacity:1;transform:translateX(0) scale(1)}}

    /* Card header — pastel gradient matching hero */
    .card-top{
      position:relative;overflow:hidden;text-align:center;
      padding:2.2rem 2rem 2.8rem;
      background:linear-gradient(135deg,#FFE8EC 0%,#FFF4D6 55%,#E0F4F1 100%);
    }
    .card-top::before{
      content:'';position:absolute;top:-40px;right:-40px;
      width:160px;height:160px;
      background:radial-gradient(circle at 30% 30%,#FFD93D 0%,#FFA726 70%);
      border-radius:50%;opacity:0.35
    }
    .card-top::after{
      content:'';position:absolute;bottom:-40px;left:-30px;
      width:140px;height:140px;
      background:radial-gradient(circle at 70% 70%,#FF8FAB 0%,#9C7CDB 80%);
      border-radius:50%;opacity:0.3
    }
    .card-top > *{position:relative;z-index:1}
    .logo-img{
      width:80px;height:80px;object-fit:contain;
      filter:drop-shadow(0 6px 14px rgba(0,0,0,0.15));
      animation:logoFloat 3.5s ease-in-out infinite
    }
    @keyframes logoFloat{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-8px) rotate(3deg)}}
    .card-top h2{
      font-family:'Fredoka One',cursive;color:#2D3436;
      font-size:2.3rem;margin-top:0.5rem;line-height:1
    }
    .card-top h2 .accent-pink{color:#FF6B9D}
    .card-top h2 .accent-teal{color:#26A69A}
    .card-top .tagline{
      color:#555;font-size:0.9rem;font-weight:700;margin-top:0.35rem
    }

    /* === ROLE TABS (pill style) === */
    .role-tabs{
      display:flex;padding:1rem 1.8rem 0;gap:0.45rem
    }
    .role-tab{
      flex:1;padding:0.7rem 0.4rem;cursor:pointer;
      font-weight:800;color:#888;border:2px solid transparent;
      border-radius:18px;transition:all 0.25s ease;
      background:#F7F7F9;outline:none;
      display:flex;flex-direction:column;align-items:center;gap:0.15rem
    }
    .role-tab:hover{color:#555;background:#EFEFF3;transform:translateY(-1px)}
    .role-tab .tab-emoji{font-size:1.4rem;transition:transform 0.25s}
    .role-tab:hover .tab-emoji{transform:scale(1.15) rotate(-5deg)}
    .role-tab .tab-label{font-size:0.68rem;font-weight:800;text-transform:uppercase;letter-spacing:0.06em}

    .tab-admin.active{
      background:#E0F4F1;color:#00796B!important;
      border-color:#26A69A;box-shadow:0 4px 0 #00796B
    }
    .tab-educateur.active{
      background:#F3E8FF;color:#6A1B9A!important;
      border-color:#9C7CDB;box-shadow:0 4px 0 #6A1B9A
    }
    .tab-parent.active{
      background:#FFE4EC;color:#C2185B!important;
      border-color:#FF8FAB;box-shadow:0 4px 0 #C2185B
    }

    /* === FORM PANELS === */
    .form-panel{display:none;padding:1.3rem 2rem 1.2rem}
    .form-panel.active{display:block;animation:panelIn 0.35s cubic-bezier(0.34,1.56,0.64,1)}
    @keyframes panelIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

    .form-group-p{margin-bottom:0.9rem}
    .form-group-p label{
      font-weight:800;font-size:0.78rem;color:#666;
      display:flex;align-items:center;gap:6px;margin-bottom:0.35rem;
      text-transform:uppercase;letter-spacing:0.05em
    }
    .form-group-p .form-control{
      border:2px solid #EAEAEA;border-radius:16px;
      padding:0.85rem 1.1rem;font-size:0.98rem;
      transition:all 0.25s;background:#FAFAFA;width:100%;
      font-family:'Nunito',sans-serif;font-weight:600
    }
    .form-group-p .form-control:focus{
      outline:none;background:#fff;transform:translateY(-1px);
      border-color:#FFD93D;box-shadow:0 0 0 4px rgba(255,217,61,0.18)
    }
    .form-control.is-invalid{border-color:#EF5350!important;background:#FFEBEE}
    .invalid-feedback{display:none;font-size:0.75rem;color:#C62828;font-weight:700;margin-top:0.3rem}
    .form-control.is-invalid~.invalid-feedback{display:block}

    .hint-box{
      border-radius:14px;padding:0.6rem 0.9rem;font-size:0.78rem;
      margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;
      border:1px solid transparent;font-weight:600
    }
    .hint-admin{background:#E0F4F1;color:#00796B;border-color:#B2DFDB}
    .hint-educateur{background:#F3E8FF;color:#6A1B9A;border-color:#D1C4E9}
    .hint-parent{background:#FFE4EC;color:#AD1457;border-color:#F8BBD0}

    /* === CHUNKY LOGIN BUTTONS (reuse .btn-chunky from playful.css) === */
    .btn-login{
      width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
      border:none;border-radius:50px;padding:0.95rem 1rem;
      font-family:'Nunito',sans-serif;font-weight:800;font-size:1rem;
      cursor:pointer;transition:all 0.15s ease;color:#fff;
      margin-top:0.3rem
    }
    .btn-login:hover{transform:translateY(2px)}
    .btn-login:active{transform:translateY(4px)}

    .btn-admin{
      background:#26A69A;
      box-shadow:0 6px 0 #00796B,0 10px 20px rgba(0,121,107,0.25)
    }
    .btn-admin:hover{box-shadow:0 4px 0 #00796B,0 6px 15px rgba(0,121,107,0.3)}
    .btn-admin:active{box-shadow:0 2px 0 #00796B}

    .btn-educateur{
      background:#9C7CDB;
      box-shadow:0 6px 0 #6A1B9A,0 10px 20px rgba(106,27,154,0.25)
    }
    .btn-educateur:hover{box-shadow:0 4px 0 #6A1B9A,0 6px 15px rgba(106,27,154,0.3)}
    .btn-educateur:active{box-shadow:0 2px 0 #6A1B9A}

    .btn-parent{
      background:#FF8FAB;
      box-shadow:0 6px 0 #E91E63,0 10px 20px rgba(233,30,99,0.25)
    }
    .btn-parent:hover{box-shadow:0 4px 0 #E91E63,0 6px 15px rgba(233,30,99,0.3)}
    .btn-parent:active{box-shadow:0 2px 0 #E91E63}

    .alert{border-radius:14px;border:none;font-weight:700;font-size:0.85rem}
    .alert-danger{background:#FFEBEE;color:#C62828}

    .card-footer-login{
      text-align:center;padding:0.5rem 2rem 1.4rem;
      font-size:0.85rem;color:#999;font-weight:600
    }
    .card-footer-login a{
      color:#26A69A;font-weight:800;text-decoration:none;position:relative
    }
    .card-footer-login a::after{
      content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;
      background:#26A69A;transition:width 0.3s
    }
    .card-footer-login a:hover::after{width:100%}

    @media(max-width:900px){
      .login-wrapper{flex-direction:column}
      .char-side{width:160px;margin-right:0;margin-bottom:-30px}
      .login-card{width:100%}
    }
    @media(max-width:500px){
      .card-top{padding:1.8rem 1.2rem 2.2rem}
      .card-top h2{font-size:1.9rem}
      .form-panel{padding:1.2rem 1.4rem}
      .role-tabs{padding:0.8rem 1.2rem 0}
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
    .btn-google:hover{
      transform:translateY(1px);
      box-shadow:0 2px 0 #dadce0,0 4px 10px rgba(0,0,0,0.08);
      background:#f8f9fa
    }
    .btn-google:active{transform:translateY(2px);box-shadow:0 1px 0 #dadce0}
    .btn-google svg{flex-shrink:0}
  </style>
</head>
<body>

<!-- Kids-store decorations -->
<div class="login-deco ld-blob-1">
  <svg viewBox="0 0 200 200"><path fill="#FFD93D" d="M43.8,-58.6C56.4,-49.3,65.8,-35.1,70.2,-19.6C74.6,-4.1,74,12.7,67.6,27.1C61.1,41.5,48.7,53.5,34.2,61.5C19.8,69.5,3.2,73.5,-13.3,71.4C-29.8,69.3,-46.3,61.2,-58,48.3C-69.7,35.4,-76.6,17.7,-75.9,0.4C-75.2,-16.9,-66.8,-33.8,-54.2,-43.4C-41.5,-53,-24.7,-55.4,-8.8,-44.9C7.1,-34.4,31.2,-67.9,43.8,-58.6Z" transform="translate(100 100)"/></svg>
</div>
<div class="login-deco ld-blob-2">
  <svg viewBox="0 0 200 200"><path fill="#FF8FAB" d="M41.3,-60.1C52.1,-51.9,58.2,-37.4,63.6,-22.3C69,-7.2,73.7,8.5,70.5,22.8C67.2,37.1,56,50,42.3,58.6C28.6,67.1,12.5,71.3,-3.5,75.5C-19.5,79.8,-39,84.1,-51.3,75.4C-63.6,66.8,-68.7,45.2,-72.5,25.3C-76.3,5.4,-78.7,-12.9,-71.4,-25.9C-64.2,-38.9,-47.3,-46.7,-32.4,-53.8C-17.5,-60.8,-4.6,-67.2,8.5,-68.8C21.5,-70.3,30.5,-68.2,41.3,-60.1Z" transform="translate(100 100)"/></svg>
</div>
<div class="login-deco ld-star-1"><svg viewBox="0 0 24 24" fill="#FFD93D"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="login-deco ld-star-2"><svg viewBox="0 0 24 24" fill="#FF8FAB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="login-deco ld-star-3"><svg viewBox="0 0 24 24" fill="#5B9BD5"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="login-deco ld-star-4"><svg viewBox="0 0 24 24" fill="#9C7CDB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="login-deco ld-arc-1">
  <svg viewBox="0 0 110 70"><path d="M5 65 A50 50 0 0 1 105 65" fill="none" stroke="#FF8FAB" stroke-width="9" stroke-linecap="round"/><path d="M22 65 A33 33 0 0 1 88 65" fill="none" stroke="#FFD93D" stroke-width="9" stroke-linecap="round"/><path d="M38 65 A17 17 0 0 1 72 65" fill="none" stroke="#26A69A" stroke-width="9" stroke-linecap="round"/></svg>
</div>
<div class="login-deco ld-arc-2">
  <svg viewBox="0 0 130 80"><path d="M5 75 A60 60 0 0 1 125 75" fill="none" stroke="#9C7CDB" stroke-width="10" stroke-linecap="round"/><path d="M25 75 A40 40 0 0 1 105 75" fill="none" stroke="#FFA726" stroke-width="10" stroke-linecap="round"/><path d="M45 75 A20 20 0 0 1 85 75" fill="none" stroke="#5B9BD5" stroke-width="10" stroke-linecap="round"/></svg>
</div>
<div class="login-deco ld-squiggle-1">
  <svg viewBox="0 0 90 30"><path d="M2 15 Q12 2 22 15 T44 15 T66 15 T88 15" stroke="#26A69A" stroke-width="5" fill="none" stroke-linecap="round"/></svg>
</div>
<div class="login-deco ld-dot-1"></div>
<div class="login-deco ld-dot-2"></div>
<div class="login-deco ld-dot-3"></div>

<div class="login-wrapper">
  <div class="char-side">
    <img src="/TinyTrack/assets/images/character.png" alt="character">
  </div>

  <div class="login-card">
    <div class="card-top">
      <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" class="logo-img">
      <h2>Tiny<span class="accent-pink">Track</span></h2>
      <p class="tagline">&#x2728; Chaque petit pas <span style="color:#26A69A">compte</span> &#x1F43E;</p>
    </div>

    <div class="role-tabs">
      <button class="role-tab tab-admin <?= $activeTab==='admin'?'active':'' ?>" onclick="switchTab('admin')"><span class="tab-emoji">&#x1F6E1;</span><span class="tab-label">Admin</span></button>
      <button class="role-tab tab-educateur <?= $activeTab==='educateur'?'active':'' ?>" onclick="switchTab('educateur')"><span class="tab-emoji">&#x1F4DA;</span><span class="tab-label">Éducateur</span></button>
      <button class="role-tab tab-parent <?= $activeTab==='parent'?'active':'' ?>" onclick="switchTab('parent')"><span class="tab-emoji">&#x1F468;&#x200D;&#x1F467;</span><span class="tab-label">Parent</span></button>
    </div>

    <?php if (!empty($errors)): ?>
      <div style="padding:0.9rem 2rem 0;"><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors[0]) ?></div></div>
    <?php endif; ?>

    <div class="form-panel <?= $activeTab==='admin'?'active':'' ?>" id="panel-admin">
      <div class="hint-box hint-admin"><i class="fas fa-info-circle"></i> Connectez-vous avec votre email administrateur</div>
      <form method="POST" action="/TinyTrack/login" novalidate onsubmit="return validerAdmin()">
        <input type="hidden" name="role" value="admin">
        <div class="form-group-p"><label><i class="fas fa-envelope" style="color:#26A69A;"></i> Email</label><input type="text" class="form-control" id="admin_email" name="email" placeholder="Adresse email" value="<?= htmlspecialchars($old['email'] ?? '') ?>"><div class="invalid-feedback" id="err_admin_email"></div></div>
        <div class="form-group-p"><label><i class="fas fa-lock" style="color:#26A69A;"></i> Mot de passe</label><input type="password" class="form-control" id="admin_mdp" name="mot_de_passe" placeholder="••••••••"><div class="invalid-feedback" id="err_admin_mdp"></div></div>
        <button type="submit" class="btn-login btn-admin"><i class="fas fa-sign-in-alt"></i> Connexion Admin</button>
        <div style="text-align:center;margin-top:0.8rem;"><a href="/TinyTrack/forgot-password" style="color:#888;font-size:0.82rem;font-weight:700;text-decoration:none;"><i class="fas fa-key"></i> Mot de passe oublié ?</a></div>
      </form>
    </div>

    <div class="form-panel <?= $activeTab==='educateur'?'active':'' ?>" id="panel-educateur">
      <div class="hint-box hint-educateur"><i class="fas fa-info-circle"></i> Entrez votre code éducateur</div>
      <form method="POST" action="/TinyTrack/login" novalidate onsubmit="return validerEducateur()">
        <input type="hidden" name="role" value="educateur">
        <div class="form-group-p"><label><i class="fas fa-id-badge" style="color:#9C7CDB;"></i> Code Éducateur</label><input type="text" class="form-control" id="educateur_id" name="educateur_id" placeholder="TT-XXXX" value="<?= htmlspecialchars($old['educateur_id'] ?? '') ?>"><div class="invalid-feedback" id="err_educateur_id"></div></div>
        <div class="form-group-p"><label><i class="fas fa-lock" style="color:#9C7CDB;"></i> Mot de passe</label><input type="password" class="form-control" id="educateur_mdp" name="mot_de_passe" placeholder="••••••••"><div class="invalid-feedback" id="err_educateur_mdp"></div></div>
        <button type="submit" class="btn-login btn-educateur"><i class="fas fa-sign-in-alt"></i> Connexion Éducateur</button>
        <div style="text-align:center;margin-top:0.8rem;"><a href="/TinyTrack/forgot-password" style="color:#888;font-size:0.82rem;font-weight:700;text-decoration:none;"><i class="fas fa-key"></i> Mot de passe oublié ?</a></div>
      </form>
    </div>

    <div class="form-panel <?= $activeTab==='parent'?'active':'' ?>" id="panel-parent">
      <div class="hint-box hint-parent"><i class="fas fa-info-circle"></i> Entrez le code de votre enfant</div>
      <form method="POST" action="/TinyTrack/login" novalidate onsubmit="return validerParent()">
        <input type="hidden" name="role" value="parent">
        <div class="form-group-p"><label><i class="fas fa-child" style="color:#FF6B9D;"></i> Code Enfant</label><input type="text" class="form-control" id="enfant_id" name="enfant_id" placeholder="TT-XXXX" value="<?= htmlspecialchars($old['enfant_id'] ?? '') ?>"><div class="invalid-feedback" id="err_enfant_id"></div></div>
        <div class="form-group-p"><label><i class="fas fa-lock" style="color:#FF6B9D;"></i> Mot de passe</label><input type="password" class="form-control" id="parent_mdp" name="mot_de_passe" placeholder="••••••••"><div class="invalid-feedback" id="err_parent_mdp"></div></div>
        <button type="submit" class="btn-login btn-parent"><i class="fas fa-sign-in-alt"></i> Connexion Parent</button>
        <div style="text-align:center;margin-top:0.8rem;"><a href="/TinyTrack/forgot-password" style="color:#888;font-size:0.82rem;font-weight:700;text-decoration:none;"><i class="fas fa-key"></i> Mot de passe oublié ?</a></div>
      </form>
    </div>

    <!-- Google Sign-In (shared for all 3 roles — match by email) -->
    <div style="padding:0 2rem;">
      <div class="or-divider">ou</div>
      <div class="g_id_signin"
           data-type="standard"
           data-shape="pill"
           data-theme="outline"
           data-text="continue_with"
           data-size="large"
           data-locale="fr"
           data-width="400"></div>
      <button type="button" id="btn-face-login" onclick="openFaceLogin()" class="btn-google" style="margin-top:0.7rem;">
        <i class="fas fa-camera" style="color:#26A69A;font-size:1.1rem;"></i>
        Se connecter avec mon visage
      </button>
    </div>

    <!-- Hidden form posted after a successful Google sign-in -->
    <form id="google-form" method="POST" action="/TinyTrack/login" style="display:none">
      <input type="hidden" name="google_jwt" id="google-jwt-field">
    </form>

    <div class="card-footer-login" id="footer-register" style="<?= $activeTab==='admin'?'display:none':'' ?>">
      Pas encore de compte ? <a href="#" id="register-link" onclick="goRegister()">S'inscrire</a>
    </div>
  </div>
</div>

<!-- Google Identity Services : hidden auto-initializer -->
<div id="g_id_onload"
     data-client_id="<?= htmlspecialchars(GOOGLE_CLIENT_ID) ?>"
     data-callback="handleGoogleLogin"
     data-auto_prompt="false"></div>

<!-- Face login modal -->
<div id="face-modal" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:9999;align-items:center;justify-content:center;padding:1rem;">
  <div style="background:#fff;border-radius:24px;padding:1.8rem;max-width:520px;width:100%;box-shadow:0 25px 60px rgba(0,0,0,0.3);">
    <div style="text-align:center;margin-bottom:1rem;">
      <h4 style="font-family:'Fredoka One',cursive;color:#00796B;margin:0;"><i class="fas fa-camera"></i> Connexion par visage</h4>
      <p id="face-step" style="color:#666;font-size:0.9rem;margin-top:0.4rem;">Chargement…</p>
    </div>
    <div style="position:relative;border-radius:18px;overflow:hidden;background:#000;aspect-ratio:4/3;">
      <video id="face-video" autoplay muted playsinline style="width:100%;height:100%;object-fit:cover;transform:scaleX(-1);"></video>
      <div id="face-overlay" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:800;background:rgba(0,0,0,0.5);">
        Préparation…
      </div>
    </div>
    <div style="display:flex;gap:0.7rem;justify-content:center;margin-top:1.2rem;flex-wrap:wrap;">
      <button type="button" id="btn-scan" disabled style="opacity:0.5;background:#26A69A;color:#fff;border:none;border-radius:50px;padding:0.85rem 2rem;font-weight:800;font-family:'Nunito',sans-serif;cursor:pointer;box-shadow:0 6px 0 #00796B;"><i class="fas fa-camera"></i> Me reconnaître</button>
      <button type="button" onclick="closeFaceLogin()" class="btn-reset"><i class="fas fa-times"></i> Annuler</button>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.min.js"></script>
<script src="/TinyTrack/assets/js/face-auth.js"></script>
<script>
let faceState = { stream: null, modal: null, video: null, overlay: null, btn: null, step: null };

async function openFaceLogin() {
  faceState.modal = document.getElementById('face-modal');
  faceState.video = document.getElementById('face-video');
  faceState.overlay = document.getElementById('face-overlay');
  faceState.btn = document.getElementById('btn-scan');
  faceState.step = document.getElementById('face-step');

  faceState.modal.style.display = 'flex';
  faceState.btn.disabled = true;
  faceState.btn.style.opacity = 0.5;
  faceState.btn.onclick = doFaceLogin;
  faceState.step.textContent = 'Chargement des modèles…';
  faceState.overlay.textContent = 'Chargement des modèles…';
  faceState.overlay.style.background = 'rgba(0,0,0,0.5)';

  try {
    await FaceAuth.loadModels();
    faceState.stream = await FaceAuth.startCamera(faceState.video);
    faceState.overlay.textContent = '';
    faceState.overlay.style.background = 'transparent';
    faceState.btn.disabled = false;
    faceState.btn.style.opacity = 1;
    faceState.step.textContent = 'Placez votre visage face à la caméra puis cliquez ci-dessous';
  } catch (e) {
    faceState.overlay.textContent = 'Erreur caméra : ' + (e.message || e);
  }
}

async function doFaceLogin() {
  faceState.btn.disabled = true;
  faceState.step.textContent = 'Détection…';
  const desc = await FaceAuth.captureDescriptor(faceState.video);
  if (!desc) {
    faceState.step.textContent = 'Aucun visage détecté. Réessayez.';
    faceState.btn.disabled = false;
    return;
  }
  faceState.step.textContent = 'Recherche du compte…';
  const r = await FaceAuth.postJSON('/TinyTrack/face-login', { descriptor: desc });
  if (r.success) {
    faceState.step.innerHTML = '<i class="fas fa-check-circle" style="color:#4CAF50"></i> Reconnu — redirection…';
    setTimeout(() => { window.location.href = r.redirect; }, 600);
  } else {
    faceState.step.textContent = r.error || 'Échec de la reconnaissance.';
    faceState.btn.disabled = false;
  }
}

function closeFaceLogin() {
  FaceAuth.stopCamera(faceState.stream);
  faceState.modal.style.display = 'none';
}
</script>

<script>
function goRegister(){if(document.querySelector('.tab-educateur.active'))window.location.href='/TinyTrack/register?tab=educateur';else if(document.querySelector('.tab-parent.active'))window.location.href='/TinyTrack/register?tab=parent';return false;}
function switchTab(role){document.querySelectorAll('.role-tab').forEach(t=>t.classList.remove('active'));document.querySelectorAll('.form-panel').forEach(p=>p.classList.remove('active'));document.querySelector('.tab-'+role).classList.add('active');document.getElementById('panel-'+role).classList.add('active');var f=document.getElementById('footer-register');var l=document.getElementById('register-link');if(role==='admin'){f.style.display='none';}else{f.style.display='block';l.textContent=role==='educateur'?"S'inscrire en tant qu'éducateur":"S'inscrire en tant que parent";}}
function showE(id,msg){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.add('is-invalid');if(e){e.textContent=msg;e.style.display='block';}}
function clearE(id){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.remove('is-invalid');if(e)e.style.display='none';}
function validerAdmin(){var ok=true;clearE('admin_email');clearE('admin_mdp');if(!document.getElementById('admin_email').value.trim()){showE('admin_email','Email obligatoire');ok=false;}if(!document.getElementById('admin_mdp').value){showE('admin_mdp','Mot de passe obligatoire');ok=false;}return ok;}
function validerEducateur(){var ok=true;clearE('educateur_id');clearE('educateur_mdp');if(!document.getElementById('educateur_id').value.trim()){showE('educateur_id','Code obligatoire');ok=false;}if(!document.getElementById('educateur_mdp').value){showE('educateur_mdp','Mot de passe obligatoire');ok=false;}return ok;}
function validerParent(){var ok=true;clearE('enfant_id');clearE('parent_mdp');if(!document.getElementById('enfant_id').value.trim()){showE('enfant_id','Code obligatoire');ok=false;}if(!document.getElementById('parent_mdp').value){showE('parent_mdp','Mot de passe obligatoire');ok=false;}return ok;}

// === Google Sign-In ===
// Called by Google Identity Services after the user picks an account.
function handleGoogleLogin(response){
  document.getElementById('google-jwt-field').value = response.credential;
  document.getElementById('google-form').submit();
}
</script>
</body>
</html>

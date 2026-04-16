<?php
// Front Controller: handles all logic before rendering
session_start();
require_once __DIR__ . '/../../Controller/AuthController.php';

$authCtrl = new AuthController();
$errors = [];
$activeTab = 'admin';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $response = $authCtrl->handleLogin($_POST);
    $activeTab = $response['activeTab'];

    if ($response['result']['success']) {
        header('Location: ' . $response['result']['redirect']);
        exit;
    } else {
        $errors[] = $response['result']['error'];
    }
}
// View starts here — only display, no logic
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
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Nunito',sans-serif;min-height:100vh;overflow:hidden;background:linear-gradient(170deg,#C9E8FF 0%,#E8F4FF 20%,#FFF0F5 40%,#FFE4EC 55%,#FDDDE6 70%,#E8C8D8 100%);display:flex;align-items:center;justify-content:center}

    .scene{position:fixed;inset:0;pointer-events:none;z-index:0}
    .sun{position:absolute;top:25px;right:60px;width:90px;height:90px;background:radial-gradient(circle,#FFF8E1 25%,#FFE0B2 50%,rgba(255,224,178,0) 70%);border-radius:50%;animation:sunPulse 5s ease-in-out infinite}
    .sun::before{content:'';position:absolute;inset:-25px;background:radial-gradient(circle,rgba(255,224,178,0.2) 30%,transparent 70%);border-radius:50%}
    @keyframes sunPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.08)}}

    .cloud{position:absolute;background:linear-gradient(180deg,#fff 60%,#FFE4EC 100%);border-radius:50px;filter:drop-shadow(0 3px 8px rgba(200,150,180,0.15))}
    .cloud::before,.cloud::after{content:'';position:absolute;background:inherit;border-radius:50%}
    .cloud-1{width:140px;height:45px;top:55px;left:8%;animation:cd 35s linear infinite}
    .cloud-1::before{width:60px;height:60px;top:-32px;left:22px}.cloud-1::after{width:72px;height:72px;top:-40px;left:55px}
    .cloud-2{width:100px;height:35px;top:110px;left:50%;animation:cd 45s linear infinite;animation-delay:12s;opacity:0.8}
    .cloud-2::before{width:46px;height:46px;top:-24px;left:14px}.cloud-2::after{width:54px;height:54px;top:-30px;left:38px}
    .cloud-3{width:110px;height:38px;top:25px;left:32%;animation:cd 38s linear infinite;animation-delay:6s;opacity:0.65}
    .cloud-3::before{width:50px;height:50px;top:-26px;left:16px}.cloud-3::after{width:60px;height:60px;top:-34px;left:42px}
    @keyframes cd{0%{transform:translateX(0)}100%{transform:translateX(-120vw)}}

    .rainbow{position:absolute;top:-70px;left:3%;width:320px;height:160px;border-radius:160px 160px 0 0;background:transparent;border-top:7px solid #FFB3C1;border-left:7px solid #FFB3C1;border-right:7px solid #FFB3C1;opacity:0.4;box-shadow:inset 0 0 0 7px #FFCBA4,inset 0 0 0 14px #FFE5A0,inset 0 0 0 21px #A8E6CF,inset 0 0 0 28px #A8D8EA,inset 0 0 0 35px #D4A5E5}

    .ground{position:absolute;bottom:0;left:0;right:0;height:45%;background:linear-gradient(180deg,#D4E7F7 0%,#C8DFF0 15%,#BFCFE6 40%,#B5C4DB 100%);border-radius:50% 50% 0 0 / 30px 30px 0 0}

    .flower{position:absolute;font-size:1.5rem;animation:fs 4s ease-in-out infinite;opacity:0.7}
    .flower-1{left:4%;bottom:44%;font-size:1.7rem}.flower-2{left:12%;bottom:41%;font-size:1.2rem;animation-delay:0.5s}
    .flower-3{right:5%;bottom:43%;font-size:1.6rem;animation-delay:1s}.flower-4{right:16%;bottom:40%;font-size:1.3rem;animation-delay:1.5s}
    .flower-5{left:25%;bottom:38%;font-size:1rem;animation-delay:0.8s}.flower-6{right:28%;bottom:42%;font-size:1.4rem;animation-delay:2s}
    @keyframes fs{0%,100%{transform:rotate(0) scale(1)}25%{transform:rotate(4deg) scale(1.02)}75%{transform:rotate(-4deg) scale(0.98)}}

    .butterfly{position:absolute;font-size:1.4rem;animation:bf 18s ease-in-out infinite;opacity:0.65}
    .butterfly-1{top:22%;left:18%}.butterfly-2{top:32%;right:22%;animation-delay:6s;font-size:1.1rem}
    @keyframes bf{0%{transform:translate(0,0)}25%{transform:translate(60px,-35px)}50%{transform:translate(110px,15px)}75%{transform:translate(40px,-20px)}100%{transform:translate(0,0)}}

    .tree{position:absolute;bottom:28%;opacity:0.6;filter:drop-shadow(0 3px 6px rgba(150,130,180,0.12))}
    .tree-1{left:2%;font-size:3.5rem}.tree-2{right:2%;font-size:2.8rem;bottom:30%}.tree-3{right:8%;font-size:2rem;bottom:32%;opacity:0.5}

    .bird{position:absolute;font-size:1.2rem;animation:birdFly 14s linear infinite;opacity:0.55}
    .bird-1{top:8%;left:-30px}.bird-2{top:14%;left:-30px;animation-duration:18s;animation-delay:5s;font-size:0.9rem}
    @keyframes birdFly{0%{transform:translateX(0)}100%{transform:translateX(110vw)}}

    .balloon{position:absolute;font-size:2.2rem;animation:balloonRise 22s ease-in-out infinite;opacity:0.45}
    .balloon-1{bottom:-50px;left:18%}.balloon-2{bottom:-50px;left:72%;animation-delay:8s;font-size:1.8rem}
    @keyframes balloonRise{0%{transform:translateY(0);opacity:0}8%{opacity:0.45}85%{opacity:0.45}100%{transform:translateY(-115vh) translateX(50px);opacity:0}}

    .kids-playing{position:absolute;bottom:26%;left:40%;display:flex;gap:6px;font-size:1.5rem;opacity:0.3}
    .kids-playing span{animation:kidJump 2.5s ease-in-out infinite}
    .kids-playing span:nth-child(1){animation-delay:0s}.kids-playing span:nth-child(2){animation-delay:0.35s}.kids-playing span:nth-child(3){animation-delay:0.7s}
    @keyframes kidJump{0%,100%{transform:translateY(0)}50%{transform:translateY(-14px)}}

    .twinkle{position:absolute;background:linear-gradient(135deg,#FFD700,#FFB6C1);border-radius:50%;animation:starTwinkle 4s ease-in-out infinite}
    .twinkle-1{width:5px;height:5px;top:12%;left:22%}.twinkle-2{width:3px;height:3px;top:6%;left:42%;animation-delay:1.2s}
    .twinkle-3{width:4px;height:4px;top:18%;right:28%;animation-delay:2.4s}.twinkle-4{width:3px;height:3px;top:4%;right:12%;animation-delay:0.6s}
    @keyframes starTwinkle{0%,100%{opacity:0.15;transform:scale(1)}50%{opacity:0.9;transform:scale(2.2)}}

    .house{position:absolute;bottom:27%;right:10%;font-size:2.5rem;opacity:0.45}

    /* === LAYOUT === */
    .login-wrapper{display:flex;align-items:center;gap:0;z-index:10;position:relative}

    .char-side{width:280px;flex-shrink:0;margin-right:-30px;z-index:15;opacity:0;animation:charIn 1.2s cubic-bezier(0.34,1.56,0.64,1) forwards;filter:drop-shadow(0 8px 20px rgba(0,0,0,0.12))}
    .char-side img{width:100%;height:auto;animation:charIdle 3s ease-in-out infinite}
    @keyframes charIn{0%{opacity:0;transform:translateX(-120px) scale(0.8)}100%{opacity:1;transform:translateX(0) scale(1)}}
    @keyframes charIdle{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}

    .login-card{position:relative;z-index:10;width:500px;background:rgba(255,255,255,0.92);backdrop-filter:blur(20px);border-radius:32px;box-shadow:0 25px 70px rgba(0,0,0,0.12),0 0 0 1px rgba(255,255,255,0.6);overflow:hidden;opacity:0;transform:translateX(60px);animation:cardIn 0.8s cubic-bezier(0.34,1.56,0.64,1) 0.8s forwards}
    @keyframes cardIn{0%{opacity:0;transform:translateX(60px) scale(0.95)}100%{opacity:1;transform:translateX(0) scale(1)}}

    .card-top{background:linear-gradient(135deg,#7BA7CC 0%,#A8C8E8 30%,#D4A5C8 70%,#E8B4C8 100%);padding:2rem 2rem 2.5rem;text-align:center;position:relative;overflow:hidden}
    .card-top::before{content:'';position:absolute;top:-30px;left:-30px;width:100px;height:100px;background:rgba(255,255,255,0.1);border-radius:50%}
    .card-top::after{content:'';position:absolute;bottom:-22px;left:50%;transform:translateX(-50%);width:120%;height:44px;background:rgba(255,255,255,0.92);border-radius:50%}
    .card-top .deco-circle{position:absolute;bottom:10px;right:20px;width:60px;height:60px;background:rgba(255,255,255,0.06);border-radius:50%}
    .logo-img{width:90px;height:90px;object-fit:contain;filter:drop-shadow(0 6px 16px rgba(0,0,0,0.2));animation:logoFloat 3.5s ease-in-out infinite;position:relative;z-index:1}
    @keyframes logoFloat{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-10px) rotate(2deg)}}
    .card-top h2{font-family:'Fredoka One',cursive;color:#fff;font-size:2.3rem;margin-top:0.4rem;position:relative;z-index:1;text-shadow:0 2px 10px rgba(0,0,0,0.1)}
    .card-top .tagline{color:rgba(255,255,255,0.9);font-size:0.88rem;font-weight:600;position:relative;z-index:1}

    .role-tabs{display:flex;padding:0.6rem 2rem 0;gap:0.5rem}
    .role-tab{flex:1;padding:0.8rem 0.5rem;text-align:center;cursor:pointer;font-weight:700;color:#bbb;border:none;border-radius:14px;transition:all 0.35s;background:transparent;outline:none;display:flex;flex-direction:column;align-items:center;gap:0.1rem}
    .role-tab:hover{color:#777;background:rgba(0,0,0,0.03)}
    .role-tab .tab-emoji{font-size:1.5rem;transition:transform 0.3s}
    .role-tab:hover .tab-emoji{transform:scale(1.15)}
    .role-tab .tab-label{font-size:0.7rem;font-weight:800;text-transform:uppercase;letter-spacing:0.06em}

    .tab-admin.active{background:linear-gradient(135deg,rgba(123,167,204,0.15),rgba(123,167,204,0.06));color:#7BA7CC!important;box-shadow:0 2px 8px rgba(123,167,204,0.2)}
    .tab-educateur.active{background:linear-gradient(135deg,rgba(156,124,219,0.15),rgba(156,124,219,0.06));color:#9C7CDB!important;box-shadow:0 2px 8px rgba(156,124,219,0.2)}
    .tab-parent.active{background:linear-gradient(135deg,rgba(232,180,200,0.15),rgba(232,180,200,0.06));color:#D4849B!important;box-shadow:0 2px 8px rgba(232,180,200,0.2)}

    .form-panel{display:none;padding:1.5rem 2.5rem 1.2rem}
    .form-panel.active{display:block;animation:panelIn 0.4s cubic-bezier(0.34,1.56,0.64,1)}
    @keyframes panelIn{from{opacity:0;transform:translateY(12px) scale(0.98)}to{opacity:1;transform:translateY(0) scale(1)}}

    .form-group-p{margin-bottom:0.9rem}
    .form-group-p label{font-weight:800;font-size:0.8rem;color:#666;display:flex;align-items:center;gap:5px;margin-bottom:0.35rem}
    .form-group-p .form-control{border:2px solid #E8E8E8;border-radius:16px;padding:0.9rem 1.2rem;font-size:1rem;transition:all 0.3s;background:#F8F9FA;width:100%;font-family:'Nunito',sans-serif}
    .form-group-p .form-control:focus{outline:none;border-color:#A8C8E8;box-shadow:0 0 0 4px rgba(168,200,232,0.15);background:#fff;transform:translateY(-1px)}
    .form-control.is-invalid{border-color:#EF5350!important}
    .invalid-feedback{display:none;font-size:0.75rem;color:#EF5350;font-weight:700;margin-top:0.2rem}
    .form-control.is-invalid~.invalid-feedback{display:block}

    .hint-box{border-radius:12px;padding:0.55rem 0.8rem;font-size:0.76rem;margin-bottom:1rem;display:flex;align-items:center;gap:0.5rem;border:1px solid transparent}
    .hint-admin{background:linear-gradient(135deg,#E3F2FD,#EDE7F6);color:#5B7FA5;border-color:#BBDEFB}
    .hint-educateur{background:linear-gradient(135deg,#EDE7F6,#F3E5F5);color:#7B5EA7;border-color:#D1C4E9}
    .hint-parent{background:linear-gradient(135deg,#FCE4EC,#FFF0F5);color:#AD5D7E;border-color:#F8BBD0}

    .btn-login{color:#fff;border:none;border-radius:18px;padding:1rem;font-weight:800;font-size:1.05rem;width:100%;cursor:pointer;transition:all 0.3s;position:relative;overflow:hidden;font-family:'Nunito',sans-serif}
    .btn-login:hover{transform:translateY(-3px)}
    .btn-login:active{transform:translateY(0)}
    .btn-login::before{content:'';position:absolute;top:0;left:0;right:0;height:50%;background:linear-gradient(180deg,rgba(255,255,255,0.15),transparent);border-radius:18px 18px 0 0}
    .btn-login::after{content:'';position:absolute;top:-50%;left:-50%;width:200%;height:200%;background:linear-gradient(45deg,transparent 42%,rgba(255,255,255,0.15) 50%,transparent 58%);animation:btnShine 4s ease-in-out infinite}
    @keyframes btnShine{0%,100%{transform:translateX(-100%)}50%{transform:translateX(100%)}}

    .btn-admin{background:linear-gradient(135deg,#7BA7CC,#A8C8E8);box-shadow:0 6px 20px rgba(123,167,204,0.3)}
    .btn-educateur{background:linear-gradient(135deg,#9C7CDB,#B39DDB);box-shadow:0 6px 20px rgba(156,124,219,0.3)}
    .btn-parent{background:linear-gradient(135deg,#D4849B,#E8A8B8);box-shadow:0 6px 20px rgba(212,132,155,0.3)}

    .alert{border-radius:14px;border:none;font-weight:600;font-size:0.83rem}
    .alert-danger{background:rgba(255,235,238,0.9);color:#C62828}
    .alert-success{background:rgba(232,245,233,0.9);color:#2E7D32}

    .card-footer-login{text-align:center;padding:0.4rem 2rem 1.2rem;font-size:0.83rem;color:#999}
    .card-footer-login a{color:#9C7CDB;font-weight:800;text-decoration:none;position:relative}
    .card-footer-login a::after{content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;background:#9C7CDB;transition:width 0.3s}
    .card-footer-login a:hover::after{width:100%}

    .footprints{display:flex;justify-content:center;gap:10px;padding:0.2rem 0;opacity:0.12;font-size:0.85rem}
    .footprints span{animation:stepBounce 2.5s ease-in-out infinite}
    .footprints span:nth-child(1){animation-delay:0s}.footprints span:nth-child(2){animation-delay:0.25s}
    .footprints span:nth-child(3){animation-delay:0.5s}.footprints span:nth-child(4){animation-delay:0.75s}.footprints span:nth-child(5){animation-delay:1s}
    @keyframes stepBounce{0%,100%{transform:translateY(0);opacity:0.12}50%{transform:translateY(-5px);opacity:0.25}}

    @media(max-width:900px){.login-wrapper{flex-direction:column}.char-side{width:150px;margin-right:0;margin-bottom:-20px}.login-card{width:95%}}
  </style>
</head>
<body>

<div class="scene">
  <div class="sun"></div>
  <div class="cloud cloud-1"></div>
  <div class="cloud cloud-2"></div>
  <div class="cloud cloud-3"></div>
  <div class="rainbow"></div>
  <div class="twinkle twinkle-1"></div>
  <div class="twinkle twinkle-2"></div>
  <div class="twinkle twinkle-3"></div>
  <div class="twinkle twinkle-4"></div>
  <div class="butterfly butterfly-1">&#x1F98B;</div>
  <div class="butterfly butterfly-2">&#x1F98B;</div>
  <div class="bird bird-1">&#x1F426;</div>
  <div class="bird bird-2">&#x1F426;</div>
  <div class="balloon balloon-1">&#x1F388;</div>
  <div class="balloon balloon-2">&#x1F388;</div>
  <div class="ground"></div>
  <div class="tree tree-1">&#x1F333;</div>
  <div class="tree tree-2">&#x1F332;</div>
  <div class="tree tree-3">&#x1F333;</div>
  <div class="house">&#x1F3E0;</div>
  <div class="kids-playing"><span>&#x1F466;</span><span>&#x1F467;</span><span>&#x1F466;</span></div>
  <div class="flower flower-1">&#x1F33B;</div>
  <div class="flower flower-2">&#x1F337;</div>
  <div class="flower flower-3">&#x1F33A;</div>
  <div class="flower flower-4">&#x1F338;</div>
  <div class="flower flower-5">&#x1F33C;</div>
  <div class="flower flower-6">&#x1F33B;</div>
</div>

<div class="login-wrapper">
  <div class="char-side">
    <img src="/TinyTrack/assets/images/character.png" alt="character">
  </div>

  <div class="login-card">
    <div class="card-top">
      <div class="deco-circle"></div>
      <img src="/TinyTrack/assets/images/logo.png" alt="TinyTrack" class="logo-img">
      <h2>TinyTrack</h2>
      <p class="tagline">&#x2728; Chaque petit pas compte &#x1F43E;</p>
    </div>

    <div class="role-tabs">
      <button class="role-tab tab-admin <?= $activeTab==='admin'?'active':'' ?>" onclick="switchTab('admin')"><span class="tab-emoji">&#x1F6E1;</span><span class="tab-label">Admin</span></button>
      <button class="role-tab tab-educateur <?= $activeTab==='educateur'?'active':'' ?>" onclick="switchTab('educateur')"><span class="tab-emoji">&#x1F4DA;</span><span class="tab-label">Éducateur</span></button>
      <button class="role-tab tab-parent <?= $activeTab==='parent'?'active':'' ?>" onclick="switchTab('parent')"><span class="tab-emoji">&#x1F468;&#x200D;&#x1F467;</span><span class="tab-label">Parent</span></button>
    </div>

    <?php if (!empty($errors)): ?>
      <div style="padding:0.8rem 2rem 0;"><div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($errors[0]) ?></div></div>
    <?php endif; ?>

    <div class="form-panel <?= $activeTab==='admin'?'active':'' ?>" id="panel-admin">
      <div class="hint-box hint-admin"><i class="fas fa-info-circle"></i> Connectez-vous avec votre email administrateur</div>
      <form method="POST" novalidate onsubmit="return validerAdmin()">
        <input type="hidden" name="role" value="admin">
        <div class="form-group-p"><label><i class="fas fa-envelope" style="color:#7BA7CC;"></i> Email</label><input type="text" class="form-control" id="admin_email" name="email" placeholder="Adresse email" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"><div class="invalid-feedback" id="err_admin_email"></div></div>
        <div class="form-group-p"><label><i class="fas fa-lock" style="color:#7BA7CC;"></i> Mot de passe</label><input type="password" class="form-control" id="admin_mdp" name="mot_de_passe" placeholder="••••••••"><div class="invalid-feedback" id="err_admin_mdp"></div></div>
        <button type="submit" class="btn-login btn-admin"><i class="fas fa-sign-in-alt"></i> Connexion Admin</button>
      </form>
    </div>

    <div class="form-panel <?= $activeTab==='educateur'?'active':'' ?>" id="panel-educateur">
      <div class="hint-box hint-educateur"><i class="fas fa-info-circle"></i> Entrez votre code éducateur</div>
      <form method="POST" novalidate onsubmit="return validerEducateur()">
        <input type="hidden" name="role" value="educateur">
        <div class="form-group-p"><label><i class="fas fa-id-badge" style="color:#9C7CDB;"></i> Code Éducateur</label><input type="text" class="form-control" id="educateur_id" name="educateur_id" placeholder="TT-XXXX" value="<?= htmlspecialchars($_POST['educateur_id'] ?? '') ?>"><div class="invalid-feedback" id="err_educateur_id"></div></div>
        <div class="form-group-p"><label><i class="fas fa-lock" style="color:#9C7CDB;"></i> Mot de passe</label><input type="password" class="form-control" id="educateur_mdp" name="mot_de_passe" placeholder="••••••••"><div class="invalid-feedback" id="err_educateur_mdp"></div></div>
        <button type="submit" class="btn-login btn-educateur"><i class="fas fa-sign-in-alt"></i> Connexion Éducateur</button>
      </form>
    </div>

    <div class="form-panel <?= $activeTab==='parent'?'active':'' ?>" id="panel-parent">
      <div class="hint-box hint-parent"><i class="fas fa-info-circle"></i> Entrez le code de votre enfant</div>
      <form method="POST" novalidate onsubmit="return validerParent()">
        <input type="hidden" name="role" value="parent">
        <div class="form-group-p"><label><i class="fas fa-child" style="color:#D4849B;"></i> Code Enfant</label><input type="text" class="form-control" id="enfant_id" name="enfant_id" placeholder="TT-XXXX" value="<?= htmlspecialchars($_POST['enfant_id'] ?? '') ?>"><div class="invalid-feedback" id="err_enfant_id"></div></div>
        <div class="form-group-p"><label><i class="fas fa-lock" style="color:#D4849B;"></i> Mot de passe</label><input type="password" class="form-control" id="parent_mdp" name="mot_de_passe" placeholder="••••••••"><div class="invalid-feedback" id="err_parent_mdp"></div></div>
        <button type="submit" class="btn-login btn-parent"><i class="fas fa-sign-in-alt"></i> Connexion Parent</button>
      </form>
    </div>

    <div class="footprints"><span>&#x1F43E;</span><span>&#x1F43E;</span><span>&#x1F43E;</span><span>&#x1F43E;</span><span>&#x1F43E;</span></div>

    <div class="card-footer-login" id="footer-register" style="<?= $activeTab==='admin'?'display:none':'' ?>">
      Pas encore de compte ? <a href="#" id="register-link" onclick="goRegister()">S'inscrire</a>
    </div>
  </div>
</div>

<script>
function goRegister(){if(document.querySelector('.tab-educateur.active'))window.location.href='register.php?tab=educateur';else if(document.querySelector('.tab-parent.active'))window.location.href='register.php?tab=parent';return false;}
function switchTab(role){document.querySelectorAll('.role-tab').forEach(t=>t.classList.remove('active'));document.querySelectorAll('.form-panel').forEach(p=>p.classList.remove('active'));document.querySelector('.tab-'+role).classList.add('active');document.getElementById('panel-'+role).classList.add('active');var f=document.getElementById('footer-register');var l=document.getElementById('register-link');if(role==='admin'){f.style.display='none';}else{f.style.display='block';l.textContent=role==='educateur'?"S'inscrire en tant qu'éducateur":"S'inscrire en tant que parent";}}
function showE(id,msg){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.add('is-invalid');if(e){e.textContent=msg;e.style.display='block';}}
function clearE(id){var f=document.getElementById(id);var e=document.getElementById('err_'+id);if(f)f.classList.remove('is-invalid');if(e)e.style.display='none';}
function validerAdmin(){var ok=true;clearE('admin_email');clearE('admin_mdp');if(!document.getElementById('admin_email').value.trim()){showE('admin_email','Email obligatoire');ok=false;}if(!document.getElementById('admin_mdp').value){showE('admin_mdp','Mot de passe obligatoire');ok=false;}return ok;}
function validerEducateur(){var ok=true;clearE('educateur_id');clearE('educateur_mdp');if(!document.getElementById('educateur_id').value.trim()){showE('educateur_id','Code obligatoire');ok=false;}if(!document.getElementById('educateur_mdp').value){showE('educateur_mdp','Mot de passe obligatoire');ok=false;}return ok;}
function validerParent(){var ok=true;clearE('enfant_id');clearE('parent_mdp');if(!document.getElementById('enfant_id').value.trim()){showE('enfant_id','Code obligatoire');ok=false;}if(!document.getElementById('parent_mdp').value){showE('parent_mdp','Mot de passe obligatoire');ok=false;}return ok;}
</script>
</body>
</html>

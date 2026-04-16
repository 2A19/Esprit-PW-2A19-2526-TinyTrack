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
  <style>
    *{margin:0;padding:0;box-sizing:border-box}
    body{font-family:'Nunito',sans-serif;min-height:100vh;overflow-y:auto;background:linear-gradient(170deg,#C9E8FF 0%,#E8F4FF 20%,#FFF0F5 40%,#FFE4EC 55%,#FDDDE6 70%,#E8C8D8 100%);display:flex;align-items:center;justify-content:center;padding:2rem 1rem}

    .reg-card{position:relative;z-index:10;width:520px;background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);border-radius:30px;box-shadow:0 20px 60px rgba(0,0,0,0.1);overflow:hidden;animation:ci 0.8s cubic-bezier(0.34,1.56,0.64,1)}
    @keyframes ci{0%{opacity:0;transform:scale(0.8) translateY(30px)}100%{opacity:1;transform:scale(1) translateY(0)}}

    .card-top{background:linear-gradient(135deg,#7BA7CC 0%,#A8C8E8 30%,#D4A5C8 70%,#E8B4C8 100%);padding:1.5rem 2rem 2rem;text-align:center;position:relative}
    .card-top::after{content:'';position:absolute;bottom:-18px;left:50%;transform:translateX(-50%);width:120%;height:36px;background:rgba(255,255,255,0.95);border-radius:50%}
    .card-top h2{font-family:'Fredoka One',cursive;color:#fff;font-size:1.5rem;position:relative;z-index:1}
    .card-top p{color:rgba(255,255,255,0.85);font-size:0.85rem;position:relative;z-index:1}
    .card-top .emoji-top{font-size:2.5rem;position:relative;z-index:1}

    .reg-tabs{display:flex;border-bottom:2px solid #f0f0f0;padding:0 1rem}
    .reg-tab{flex:1;padding:0.7rem;text-align:center;cursor:pointer;font-weight:800;font-size:0.72rem;text-transform:uppercase;letter-spacing:0.05em;color:#bbb;border:none;border-bottom:3px solid transparent;transition:all 0.3s;background:none;outline:none;display:flex;flex-direction:column;align-items:center;gap:0.15rem}
    .reg-tab:hover{color:#777;background:#fafafa}
    .reg-tab .tab-emoji{font-size:1.4rem}
    .tab-edu.active{border-bottom:3px solid #9C7CDB!important;color:#9C7CDB!important}
    .tab-par.active{border-bottom:3px solid #D4849B!important;color:#D4849B!important}

    .reg-panel{display:none;padding:1.2rem 2rem}
    .reg-panel.active{display:block;animation:fi 0.3s ease}
    @keyframes fi{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}

    .form-group-r{margin-bottom:0.8rem}
    .form-group-r label{font-weight:800;font-size:0.8rem;color:#555;display:flex;align-items:center;gap:5px;margin-bottom:0.3rem}
    .form-group-r .form-control{border:2px solid #E8E8E8;border-radius:14px;padding:0.65rem 1rem;font-size:0.9rem;transition:all 0.3s;background:#FAFAFA;width:100%;font-family:'Nunito',sans-serif}
    .form-group-r .form-control:focus{outline:none;border-color:#A8C8E8;box-shadow:0 0 0 4px rgba(168,200,232,0.15);background:#fff}
    .form-control.is-invalid{border-color:#EF5350!important}

    .btn-register{color:#fff;border:none;border-radius:16px;padding:0.8rem;font-weight:800;font-size:0.95rem;width:100%;cursor:pointer;transition:all 0.3s;font-family:'Nunito',sans-serif;box-shadow:0 6px 20px rgba(0,0,0,0.15)}
    .btn-register:hover{transform:translateY(-3px)}
    .btn-edu{background:linear-gradient(135deg,#9C7CDB,#B39DDB)}
    .btn-par{background:linear-gradient(135deg,#D4849B,#E8A8B8)}

    .alert{border-radius:14px;border:none;font-weight:600;font-size:0.85rem}
    .alert-danger{background:#FFEBEE;color:#C62828}

    .success-box{text-align:center;padding:2rem 2rem 1.5rem}
    .success-box .icon{font-size:3.5rem;margin-bottom:0.5rem;animation:sb 1s ease}
    @keyframes sb{0%{transform:scale(0)}50%{transform:scale(1.2)}100%{transform:scale(1)}}
    .success-box h3{font-family:'Fredoka One',cursive;color:#2E7D32;font-size:1.15rem;margin-bottom:0.5rem}
    .success-box p{color:#555;font-size:0.88rem}
    .success-box .code-display{background:#E8F5E9;border:2px dashed #4CAF50;border-radius:14px;padding:0.8rem;font-family:'Courier New',monospace;font-size:1.5rem;font-weight:800;color:#2E7D32;margin:0.8rem 0}
    .success-box .warning{background:#FFF3E0;border-radius:10px;padding:0.5rem 0.8rem;font-size:0.78rem;color:#E65100;margin-top:0.5rem}
    .btn-go-login{display:inline-block;margin-top:1rem;background:linear-gradient(135deg,#7BA7CC,#A8C8E8);color:#fff;border:none;border-radius:25px;padding:0.6rem 2rem;font-weight:800;font-size:0.9rem;cursor:pointer;text-decoration:none;font-family:'Nunito',sans-serif}

    .card-footer-r{text-align:center;padding:0.5rem 2rem 1.2rem;font-size:0.85rem;color:#888}
    .card-footer-r a{color:#9C7CDB;font-weight:800;text-decoration:none}
  </style>
</head>
<body>

<div class="reg-card">
  <div class="card-top">
    <div class="emoji-top">&#x1F4DD;</div>
    <h2>Créer un compte</h2>
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

    <?php if (!empty($errors)): ?>
      <div style="padding:0.8rem 2rem 0;"><div class="alert alert-danger"><ul class="mb-0"><?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?></ul></div></div>
    <?php endif; ?>

    <!-- EDUCATEUR FORM -->
    <div class="reg-panel <?= $activeTab === 'educateur' ? 'active' : '' ?>" id="rpanel-educateur">
      <form method="POST" novalidate onsubmit="return validerForm()">
        <input type="hidden" name="role" value="educateur">
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
</script>
</body>
</html>

<?php
// Vue passive — données injectées par AuthController::showForgotPassword() / doForgotPassword()
// Variables disponibles : $error, $submitted, $sentTo, $mailFailed
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Mot de passe oublié</title>
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
    .bd-arc-1{top:55%;left:3%;width:110px;height:70px;opacity:0.5;animation:float 10s ease-in-out infinite}

    .fp-card{
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
    .card-top p{color:#555;font-size:0.9rem;font-weight:600;margin-top:0.4rem;max-width:340px;margin-inline:auto}

    .fp-body{padding:1.6rem 2rem 1.4rem}

    .form-group-f{margin-bottom:1rem}
    .form-group-f label{font-weight:800;font-size:0.78rem;color:#666;text-transform:uppercase;letter-spacing:0.05em;display:flex;align-items:center;gap:6px;margin-bottom:0.4rem}
    .form-group-f .form-control{
      border:2px solid #EAEAEA;border-radius:16px;padding:0.85rem 1.1rem;
      font-size:0.98rem;background:#FAFAFA;width:100%;
      font-family:'Nunito',sans-serif;font-weight:600;transition:all 0.25s
    }
    .form-group-f .form-control:focus{
      outline:none;background:#fff;
      border-color:#FFD93D;box-shadow:0 0 0 4px rgba(255,217,61,0.18)
    }

    .btn-chunky-submit{
      width:100%;display:flex;align-items:center;justify-content:center;gap:8px;
      background:#26A69A;color:#fff;border:none;border-radius:50px;
      padding:0.95rem;font-weight:800;font-size:1rem;cursor:pointer;
      font-family:'Nunito',sans-serif;transition:all 0.15s ease;
      box-shadow:0 6px 0 #00796B,0 10px 20px rgba(0,121,107,0.25)
    }
    .btn-chunky-submit:hover{transform:translateY(2px);box-shadow:0 4px 0 #00796B,0 6px 15px rgba(0,121,107,0.3)}
    .btn-chunky-submit:active{transform:translateY(4px);box-shadow:0 2px 0 #00796B}

    .alert{border-radius:14px;border:none;font-weight:700;font-size:0.85rem;padding:0.7rem 1rem;margin-bottom:1rem}
    .alert-danger{background:#FFEBEE;color:#C62828}

    .success-box{text-align:center;padding:0.5rem 0}
    .success-box .icon{font-size:3.6rem;margin-bottom:0.6rem;animation:sb 1s cubic-bezier(0.34,1.56,0.64,1)}
    @keyframes sb{0%{transform:scale(0) rotate(-180deg)}100%{transform:scale(1) rotate(0)}}
    .success-box h3{font-family:'Fredoka One',cursive;color:#2E7D32;font-size:1.4rem;margin-bottom:0.6rem}
    .success-box .info-note{
      background:#FFF8E1;border:1px solid #FFE082;border-radius:14px;
      padding:0.7rem 1rem;font-size:0.8rem;color:#F57F17;
      font-weight:700;margin:1.2rem 0;text-align:left;
      display:flex;align-items:flex-start;gap:8px;line-height:1.5
    }
    .success-box .btn-go-back{
      display:inline-flex;align-items:center;gap:8px;
      background:#26A69A;color:#fff;border:none;border-radius:50px;
      padding:0.85rem 2rem;font-weight:800;font-size:0.95rem;
      cursor:pointer;text-decoration:none;font-family:'Nunito',sans-serif;
      box-shadow:0 6px 0 #00796B,0 10px 20px rgba(0,121,107,0.25);
      transition:all 0.15s ease;margin-top:0.3rem
    }
    .success-box .btn-go-back:hover{transform:translateY(2px);box-shadow:0 4px 0 #00796B;color:#fff}
    .success-box .btn-go-back:active{transform:translateY(4px);box-shadow:0 2px 0 #00796B}

    .fp-footer{text-align:center;padding:0.4rem 2rem 1.4rem;font-size:0.88rem;color:#888;font-weight:600}
    .fp-footer a{color:#26A69A;font-weight:800;text-decoration:none;position:relative}
    .fp-footer a::after{content:'';position:absolute;bottom:-2px;left:0;width:0;height:2px;background:#26A69A;transition:width 0.3s}
    .fp-footer a:hover::after{width:100%}
  </style>
</head>
<body>

<div class="bg-deco bd-blob-1"><svg viewBox="0 0 200 200"><path fill="#FFD93D" d="M43.8,-58.6C56.4,-49.3,65.8,-35.1,70.2,-19.6C74.6,-4.1,74,12.7,67.6,27.1C61.1,41.5,48.7,53.5,34.2,61.5C19.8,69.5,3.2,73.5,-13.3,71.4C-29.8,69.3,-46.3,61.2,-58,48.3C-69.7,35.4,-76.6,17.7,-75.9,0.4C-75.2,-16.9,-66.8,-33.8,-54.2,-43.4C-41.5,-53,-24.7,-55.4,-8.8,-44.9C7.1,-34.4,31.2,-67.9,43.8,-58.6Z" transform="translate(100 100)"/></svg></div>
<div class="bg-deco bd-blob-2"><svg viewBox="0 0 200 200"><path fill="#FF8FAB" d="M41.3,-60.1C52.1,-51.9,58.2,-37.4,63.6,-22.3C69,-7.2,73.7,8.5,70.5,22.8C67.2,37.1,56,50,42.3,58.6C28.6,67.1,12.5,71.3,-3.5,75.5C-19.5,79.8,-39,84.1,-51.3,75.4C-63.6,66.8,-68.7,45.2,-72.5,25.3C-76.3,5.4,-78.7,-12.9,-71.4,-25.9C-64.2,-38.9,-47.3,-46.7,-32.4,-53.8C-17.5,-60.8,-4.6,-67.2,8.5,-68.8C21.5,-70.3,30.5,-68.2,41.3,-60.1Z" transform="translate(100 100)"/></svg></div>
<div class="bg-deco bd-star-1"><svg viewBox="0 0 24 24" fill="#FFD93D"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="bg-deco bd-star-2"><svg viewBox="0 0 24 24" fill="#9C7CDB"><path d="M12 2l2.4 6.9L22 10l-5.6 4.6L18 22l-6-3.7L6 22l1.6-7.4L2 10l7.6-1.1z"/></svg></div>
<div class="bg-deco bd-arc-1"><svg viewBox="0 0 110 70"><path d="M5 65 A50 50 0 0 1 105 65" fill="none" stroke="#FF8FAB" stroke-width="9" stroke-linecap="round"/><path d="M22 65 A33 33 0 0 1 88 65" fill="none" stroke="#FFD93D" stroke-width="9" stroke-linecap="round"/><path d="M38 65 A17 17 0 0 1 72 65" fill="none" stroke="#26A69A" stroke-width="9" stroke-linecap="round"/></svg></div>

<div class="fp-card">
  <div class="card-top">
    <div class="icon-top">&#x1F511;</div>
    <h2>Mot de passe <span class="accent">oublié</span> ?</h2>
    <p>Pas de panique — saisissez votre email, on genere un lien pour reinitialiser votre mot de passe.</p>
  </div>

  <div class="fp-body">
    <?php if ($submitted): ?>
      <div class="success-box">
        <div class="icon">&#x1F4E7;</div>
        <h3>Email envoye !</h3>
        <p style="color:#555;font-size:0.95rem;line-height:1.5">
          Un lien de reinitialisation a ete envoye a<br>
          <strong style="color:#26A69A"><?= htmlspecialchars($sentTo) ?></strong>
        </p>
        <div class="info-note">
          <i class="fas fa-clock" style="margin-top:2px"></i>
          <span>Le lien est valable <strong>30 minutes</strong> et ne peut etre utilise qu'une seule fois. Verifiez aussi vos spams.</span>
        </div>
        <a href="/TinyTrack/login" class="btn-go-back"><i class="fas fa-arrow-left"></i> Retour au login</a>
      </div>
    <?php else: ?>
      <?php if ($error): ?>
        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="/TinyTrack/forgot-password" novalidate>
        <div class="form-group-f">
          <label><i class="fas fa-envelope" style="color:#26A69A"></i> Email</label>
          <input type="text" name="email" class="form-control" placeholder="votre@email.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" autofocus>
        </div>
        <button type="submit" class="btn-chunky-submit"><i class="fas fa-paper-plane"></i> Envoyer le lien</button>
      </form>
    <?php endif; ?>
  </div>

  <?php if (!$submitted): ?>
  <div class="fp-footer">
    <a href="/TinyTrack/login"><i class="fas fa-arrow-left"></i> Retour au login</a>
  </div>
  <?php endif; ?>
</div>
</body>
</html>

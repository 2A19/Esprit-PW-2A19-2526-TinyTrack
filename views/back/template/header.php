<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TinyTrack — Gestion Événements</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/admin-lte@3.2/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Fredoka+One&family=Nunito:wght@400;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap4.min.css">
  <style>
    :root {
      --tt-green:#4CAF50;--tt-green-dark:#388E3C;--tt-blue:#5B9BD5;
      --tt-yellow:#FFD93D;--tt-pink:#FF8FAB;--tt-orange:#FFA726;
      --tt-purple:#9C7CDB;--tt-bg:#F0F7FF;
    }
    body { font-family:'Nunito',sans-serif; background:var(--tt-bg); }

    .main-sidebar { background:linear-gradient(180deg,#2E7D32 0%,#1B5E20 100%) !important; }
    .brand-link { background:rgba(255,255,255,0.1) !important; border-bottom:1px solid rgba(255,255,255,0.15) !important; padding:12px 15px !important; display:flex !important; align-items:center !important; gap:10px; }
    .brand-link .brand-text { font-family:'Fredoka One',cursive !important; color:#fff !important; font-size:1.3rem; }
    .brand-link .brand-image { width:38px;height:38px;border-radius:10px;object-fit:contain;margin-left:0;margin-right:5px;max-height:38px; }
    .sidebar .nav-link { font-weight:600; border-radius:12px !important; margin:3px 8px; transition:all 0.25s; }
    .sidebar .nav-link:hover { background:rgba(255,255,255,0.15) !important; transform:translateX(5px); }
    .sidebar .nav-header { color:rgba(255,255,255,0.5) !important; font-family:'Fredoka One',cursive; font-size:0.7rem; letter-spacing:0.1em; padding-left:20px; }
    .user-panel .info a { font-family:'Fredoka One',cursive; }

    .main-header { border-bottom:4px solid transparent; border-image:linear-gradient(90deg,var(--tt-green),var(--tt-blue),var(--tt-yellow),var(--tt-pink)) 1; background:#fff; }
    .content-wrapper { background:var(--tt-bg) !important; }
    .content-header h1 { font-family:'Fredoka One',cursive; color:#2D3436; }

    .card { border:none; border-radius:20px; box-shadow:0 4px 20px rgba(0,0,0,0.05); overflow:hidden; transition:transform 0.2s,box-shadow 0.2s; }
    .card:hover { transform:translateY(-2px); box-shadow:0 6px 25px rgba(0,0,0,0.08); }
    .card-header { border-radius:20px 20px 0 0 !important; }
    .card-success .card-header { background:linear-gradient(135deg,#4CAF50,#81C784) !important; }
    .card-warning .card-header { background:linear-gradient(135deg,#FFA726,#FFCC80) !important; }
    .card-info .card-header { background:linear-gradient(135deg,#5B9BD5,#90CAF9) !important; }
    .card-title { font-family:'Fredoka One',cursive; font-size:1rem; }

    .small-box { border-radius:20px; overflow:hidden; transition:transform 0.2s; position:relative; }
    .small-box:hover { transform:scale(1.03); }
    .small-box .inner h3 { font-family:'Fredoka One',cursive; font-size:2.5rem; }
    .small-box::before { content:''; position:absolute; top:-15px;right:-15px; width:60px;height:60px; background:rgba(255,255,255,0.15); border-radius:50%; }
    .small-box.bg-success { background:linear-gradient(135deg,#4CAF50,#81C784) !important; }
    .small-box.bg-info { background:linear-gradient(135deg,#5B9BD5,#90CAF9) !important; }
    .small-box.bg-warning { background:linear-gradient(135deg,#FFA726,#FFCC80) !important; }
    .small-box.bg-danger { background:linear-gradient(135deg,#EF5350,#E57373) !important; }

    .table { border-radius:14px; overflow:hidden; }
    .table thead th { font-family:'Fredoka One',cursive; font-weight:400; font-size:0.85rem; background:#2D3436; color:#fff; border:none; padding:0.8rem; }
    .table tbody td { vertical-align:middle; padding:0.7rem 0.8rem; }
    .table-hover tbody tr:hover { background:rgba(76,175,80,0.08); }

    .badge-success,.badge.bg-success { background:linear-gradient(135deg,#4CAF50,#81C784) !important; border-radius:20px; }
    .badge-warning,.badge.bg-warning { background:linear-gradient(135deg,#FFD93D,#FFE082) !important; color:#333; border-radius:20px; }
    .badge-danger,.badge.bg-danger { background:linear-gradient(135deg,#EF5350,#E57373) !important; border-radius:20px; }
    .badge-secondary,.badge.bg-secondary { background:linear-gradient(135deg,#B0BEC5,#CFD8DC) !important; border-radius:20px; }
    .badge-primary,.badge.bg-primary { background:linear-gradient(135deg,#5B9BD5,#64B5F6) !important; border-radius:20px; }
    .badge-info,.badge.bg-info { background:linear-gradient(135deg,#5B9BD5,#90CAF9) !important; border-radius:20px; }

    .btn-success { background:linear-gradient(135deg,#4CAF50,#66BB6A); border:none; border-radius:25px; font-weight:700; box-shadow:0 3px 10px rgba(76,175,80,0.2); }
    .btn-success:hover { background:linear-gradient(135deg,#388E3C,#4CAF50); transform:translateY(-2px); }
    .btn-warning { border-radius:25px; font-weight:700; border:none; background:linear-gradient(135deg,#FFA726,#FFB74D); }
    .btn-info { background:linear-gradient(135deg,#5B9BD5,#64B5F6); border:none; border-radius:25px; font-weight:700; }
    .btn-danger { border-radius:25px; font-weight:700; border:none; background:linear-gradient(135deg,#EF5350,#E57373); }
    .btn-default { border-radius:25px; }
    .btn-sm { border-radius:20px !important; }

    .form-control,.form-select { border-radius:12px; border:2px solid #E8E8E8; transition:all 0.25s; }
    .form-control:focus,.form-select:focus { border-color:var(--tt-green); box-shadow:0 0 0 4px rgba(76,175,80,0.12); }
    .form-control.is-invalid { border-color:#FF6B6B; }
    label { font-weight:700; font-size:0.9rem; color:#555; }

    .alert { border-radius:16px; border:none; font-weight:600; }
    .alert-success { background:linear-gradient(135deg,#E8F5E9,#C8E6C9); color:#2E7D32; }
    .alert-danger { background:linear-gradient(135deg,#FFEBEE,#FFCDD2); color:#C62828; }

    .info-box { border-radius:18px; }
    .main-footer { background:#fff; border-top:4px solid transparent; border-image:linear-gradient(90deg,var(--tt-green),var(--tt-blue),var(--tt-yellow),var(--tt-pink)) 1; font-weight:600; color:#888; }

    .invalid-feedback { display:none; font-size:0.8rem; color:#EF5350; font-weight:700; }
    .form-control.is-invalid + .invalid-feedback,
    .form-control.is-invalid ~ .invalid-feedback,
    .form-select.is-invalid + .invalid-feedback { display:block; }
  </style>
</head>
<body class="hold-transition sidebar-mini">
<div class="wrapper">

<nav class="main-header navbar navbar-expand navbar-white navbar-light">
  <ul class="navbar-nav">
    <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
    <li class="nav-item d-none d-sm-inline-block">
      <a href="/ProjetEvenements/views/back/index.php" class="nav-link" style="font-family:'Fredoka One',cursive;color:var(--tt-green);"><i class="fas fa-home"></i> Dashboard</a>
    </li>
  </ul>
  <ul class="navbar-nav ml-auto">
    <li class="nav-item"><span class="nav-link" style="font-weight:700;"><i class="fas fa-user-shield text-success"></i> Administrateur</span></li>
  </ul>
</nav>

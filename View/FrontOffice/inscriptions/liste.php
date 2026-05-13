<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
if (!isset($_SESSION['user_id'])) { header('Location: /TinyTrack/login'); exit; }
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> TinyTrack - Liste des enfants</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 0;
            position: relative;
            overflow-x: hidden;
        }

        /* ── Navbar ── */
        .navbar {
            background: rgba(255,255,255,0.15);
            backdrop-filter: blur(10px);
            padding: 0 30px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 60px;
            position: sticky;
            top: 0;
            z-index: 100;
            border-bottom: 1px solid rgba(255,255,255,0.2);
        }

        .navbar-brand {
            color: white;
            font-size: 20px;
            font-weight: 700;
            text-decoration: none;
        }

        .navbar-links {
            display: flex;
            gap: 6px;
        }

        .navbar-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 500;
            transition: background 0.2s, color 0.2s;
        }

        .navbar-links a:hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }

        .navbar-links a.active {
            background: rgba(255,255,255,0.3);
            color: white;
        }

        .page-wrapper {
            padding: 30px 20px;
        }
        
        body::before {
            content: '';
            position: fixed;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 50px 50px;
            animation: backgroundMove 20s linear infinite;
            pointer-events: none;
        }
        
        @keyframes backgroundMove {
            0% { transform: translate(0, 0); }
            100% { transform: translate(50px, 50px); }
        }
        
        .container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 100%;
            padding: 40px;
            margin: 0 auto;
            position: relative;
            animation: slideIn 0.5s ease-out;
        }
        
        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            font-size: 32px;
        }
        
        .nav-links {
            display: flex;
            gap: 15px;
        }
        
        .nav-links a {
            padding: 10px 20px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: all 0.3s;
        }
        
        .nav-links a:hover {
            background: #764ba2;
            transform: translateY(-2px);
        }
        
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .search-box {
            margin-bottom: 30px;
        }
        
        .search-box input {
            width: 100%;
            padding: 14px 20px;
            border: 2px solid #e0e0e0;
            border-radius: 30px;
            font-size: 14px;
            transition: all 0.3s;
            background: #f8f9fa;
        }
        
        .search-box input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
            transform: translateY(-2px);
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: bold;
            font-size: 14px;
        }
        
        td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        
        tr:hover {
            background: #f9f9f9;
        }
        
        .btn-view {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            width: auto;
            margin-top: 0;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .btn-back {
            background: #6c63ff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: bold;
            margin-bottom: 20px;
            width: auto;
            display: inline-block;
        }
        
        .btn-back:hover {
            transform: translateX(-5px);
            background: #5a52d5;
        }
        
        .empty-message {
            text-align: center;
            color: #999;
            padding: 40px 20px;
            font-size: 16px;
        }
        
        .view {
            display: none;
        }
        
        .view.active {
            display: block;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 60px;
            margin: 0 auto 20px;
            box-shadow: 0 8px 20px rgba(245, 87, 108, 0.3);
        }
        
        .header h1 {
            font-size: 28px;
            margin: 10px 0;
        }
        
        .card {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
        }
        
        .card.urgent {
            border-left-color: #dc3545;
        }
        
        .card h2 {
            color: #667eea;
            font-size: 18px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .card.urgent h2 {
            color: #dc3545;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            padding: 12px 0;
            border-bottom: 1px solid rgba(255,255,255,0.5);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .label {
            font-weight: bold;
            color: #333;
            flex: 0 0 40%;
        }
        
        .value {
            color: #555;
            font-size: 15px;
            flex: 1;
            text-align: right;
        }
        
        .age-badge {
            display: inline-block;
            background: #ffd700;
            color: #333;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: bold;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="navbar-brand" href="../index.html"> TinyTrack</a>
        <div class="navbar-links">
            <a href="../index.html">Inscription</a>
            <a href="liste.html" class="active">Liste des enfants</a>
            <a href="../backoffice/gestion.html">Gestion</a>
            <a href="../stats/dashboard.html">Statistiques</a>
            <a href="../tracker/suivre.html"> Tracker</a>
        </div>
    </nav>

    <div class="page-wrapper">
    <div class="container">
        <div class="header-top">
            <div>
                <h1> TinyTrack</h1>
                <p class="subtitle">Liste des enfants inscrits</p>
            </div>
        </div>
        
        <div id="listView" class="view active">
            <div class="search-box">
                <input type="text" id="searchInput" placeholder=" Rechercher..." onkeyup="filtrerEnfants()">
            </div>
            
            <table id="tableEnfants">
                <thead>
                    <tr>
                        <th>Enfant</th>
                        <th>Date Naissance</th>
                        <th>Classe</th>
                        <th>Groupe Sanguin</th>
                        <th>Allergies</th>
                        <th>Détails</th>
                    </tr>
                </thead>
                <tbody id="enfantsBody">
                    <tr><td colspan="6" class="empty-message">Chargement...</td></tr>
                </tbody>
            </table>
        </div>
        
        <div id="detailsView" class="view">
            <button class="btn-back" onclick="afficherListe()">← Retour</button>
            <div id="detailsContent"></div>
        </div>
    </div>
    </div><!-- end page-wrapper -->
    
    <script src="/TinyTrack/assets/js/inscriptions/frontoffice.js?v=2"></script>
</body>
</html>

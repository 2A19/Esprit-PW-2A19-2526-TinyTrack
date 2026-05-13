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
    <title> TinyTrack - Gestion</title>
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
            justify-content: center;
            align-items: center;
            margin-bottom: 20px;
        }
        
        h1 {
            color: #333;
            font-size: 32px;
        }
        
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
            font-size: 14px;
        }
        
        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 30px;
            border-bottom: 2px solid #eee;
        }
        
        .tab-btn {
            padding: 12px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            color: #999;
            border-bottom: 3px solid transparent;
            transition: all 0.3s;
        }
        
        .tab-btn.active {
            color: #667eea;
            border-bottom-color: #667eea;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .form-row-3 {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 15px;
        }
        
        .form-full {
            grid-column: 1 / -1;
        }
        
        label {
            display: block;
            margin-bottom: 6px;
            color: #555;
            font-weight: 600;
            font-size: 14px;
        }
        
        input, select, textarea {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            font-family: inherit;
            background: #f8f9fa;
        }
        
        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            background: white;
            transform: translateY(-2px);
        }
        
        input:invalid:not(:placeholder-shown), select:invalid:not(:placeholder-shown) {
            border-color: #dc3545;
        }
        
        input:valid:not(:placeholder-shown), select:valid:not(:placeholder-shown) {
            border-color: #28a745;
        }
        
        textarea {
            resize: vertical;
            min-height: 80px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #667eea;
            margin-top: 25px;
            margin-bottom: 15px;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
            position: relative;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 0;
            width: 60px;
            height: 3px;
            background: linear-gradient(90deg, #667eea, #764ba2);
            border-radius: 3px;
        }
        
        .checkbox-group {
            display: flex;
            gap: 20px;
            align-items: center;
        }
        
        .checkbox-item {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
        }
        
        button {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 14px 35px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
            margin-top: 20px;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
            position: relative;
            overflow: hidden;
        }
        
        button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }
        
        button:hover::before {
            width: 300px;
            height: 300px;
        }
        
        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.6);
        }
        
        button:active {
            transform: translateY(-1px);
        }
        
        .message {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            display: none;
            animation: slideIn 0.3s ease;
        }
        
        @keyframes slideIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .message.success {
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        
        .message.error {
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        
        .message.show {
            display: block;
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
            margin-right: 5px;
            width: auto;
            margin-top: 0;
        }
        
        .btn-view:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(40, 167, 69, 0.3);
        }
        
        .btn-delete {
            background: linear-gradient(135deg, #dc3545 0%, #e74c3c 100%);
            color: white;
            padding: 8px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 12px;
            width: auto;
            margin-top: 0;
        }
        
        .btn-delete:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
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
            margin-top: 0;
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
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            border-left: 5px solid #667eea;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
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
        
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
            justify-content: center;
        }
        
        .action-buttons button {
            width: auto;
            margin-top: 0;
            padding: 10px 25px;
        }
        
        /* Classes Card Layout */
        .classe-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s;
            border-top: 5px solid #667eea;
            position: relative;
            overflow: hidden;
        }
        
        .classe-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .classe-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .classe-title {
            font-size: 20px;
            font-weight: bold;
            color: #333;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .classe-color-badge {
            display: inline-block;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .classe-info {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 15px;
            font-size: 13px;
        }
        
        .classe-info-item {
            display: flex;
            justify-content: space-between;
            padding: 8px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .classe-info-label {
            font-weight: 600;
            color: #667eea;
        }
        
        .classe-info-value {
            color: #555;
        }
        
        .classe-children {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 2px solid #f0f0f0;
        }
        
        .classe-children-title {
            font-size: 14px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .children-list {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }
        
        .child-item {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 12px;
            border-radius: 8px;
            font-size: 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            cursor: pointer;
            transition: all 0.2s;
        }
        
        .child-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }
        
        .child-name {
            font-weight: 600;
        }
        
        .child-age {
            background: rgba(255,255,255,0.2);
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
        }
        
        .no-children {
            text-align: center;
            color: #999;
            font-size: 12px;
            padding: 10px;
            font-style: italic;
        }
        
        .classe-actions {
            display: flex;
            gap: 8px;
            margin-top: 12px;
            justify-content: flex-end;
        }
        
        .classe-actions button {
            padding: 6px 12px;
            font-size: 11px;
            width: auto;
            margin-top: 0;
            border-radius: 6px;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <a class="navbar-brand" href="../index.html"> TinyTrack</a>
        <div class="navbar-links">
            <a href="../index.html">Inscription</a>
            <a href="../frontoffice/liste.html">Liste des enfants</a>
            <a href="gestion.html" class="active">Gestion</a>
            <a href="../stats/dashboard.html">Statistiques</a>
            <a href="../tracker/suivre.html"> Tracker</a>
        </div>
    </nav>

    <div class="page-wrapper">
    <div class="container">
        <div class="header-top">
            <div>
                <h1> TinyTrack</h1>
                <p class="subtitle">Espace de gestion</p>
            </div>
        </div>
        
        <div id="formView" class="view active">
            <div class="tabs">
                <button class="tab-btn active" onclick="switchTab(event, 'list')"> Enfants</button>
                <button class="tab-btn" onclick="switchTab(event, 'classes')"> Classes</button>
            </div>
            
            <div id="message" class="message"></div>
            
            <form id="inscriptionForm" style="display: none;">
                <button type="button" class="btn-back" onclick="cacherFormulaireEnfant()" style="margin-bottom:20px;">← Retour à la liste</button>
                <div class="section-title"> Enfant</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="enfant_prenom">Prénom *</label>
                        <input type="text" id="enfant_prenom" name="enfant_prenom" placeholder="Prénom de l'enfant">
                    </div>
                    <div class="form-group">
                        <label for="enfant_nom">Nom *</label>
                        <input type="text" id="enfant_nom" name="enfant_nom" placeholder="Nom de famille">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="enfant_date_naissance">Date de Naissance *</label>
                        <input type="date" id="enfant_date_naissance" name="enfant_date_naissance">
                    </div>
                    <div class="form-group">
                        <label for="classe_id">Classe *</label>
                        <select id="classe_id" name="classe_id">
                            <option value="">-- Sélectionner --</option>
                        </select>
                    </div>
                </div>
                
                <div class="section-title"> Dossier Médical</div>
                <div class="form-row-3">
                    <div class="form-group">
                        <label for="groupe_sanguin">Groupe Sanguin</label>
                        <select id="groupe_sanguin" name="groupe_sanguin">
                            <option value="">-- Non renseigné --</option>
                            <option value="O+">O+</option>
                            <option value="O-">O-</option>
                            <option value="A+">A+</option>
                            <option value="A-">A-</option>
                            <option value="B+">B+</option>
                            <option value="B-">B-</option>
                            <option value="AB+">AB+</option>
                            <option value="AB-">AB-</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group form-full">
                    <label for="allergies_alimentaires">Allergies Alimentaires</label>
                    <textarea id="allergies_alimentaires" name="allergies_alimentaires" placeholder="Ex: Arachides, Fruits secs..."></textarea>
                </div>
                
                <div class="form-group form-full">
                    <label for="allergies_medicales">Allergies Médicales</label>
                    <textarea id="allergies_medicales" name="allergies_medicales" placeholder="Ex: Pénicilline..."></textarea>
                </div>
                
                <div class="form-group form-full">
                    <label for="maladies_chroniques">Maladies Chroniques</label>
                    <textarea id="maladies_chroniques" name="maladies_chroniques" placeholder="Ex: Asthme, Eczéma..."></textarea>
                </div>
                
                <div class="form-group form-full">
                    <label for="vaccinations">État des Vaccinations</label>
                    <textarea id="vaccinations" name="vaccinations" placeholder="Ex: DTP à jour, ROR le 15/03/2024..."></textarea>
                </div>
                
                <div class="form-group form-full">
                    <label for="notes_sante">Notes de Santé Importantes</label>
                    <textarea id="notes_sante" name="notes_sante" placeholder="Tout ce qui peut être important pour la santé de l'enfant..."></textarea>
                </div>
                
                <div class="section-title"> Contact d'Urgence</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_urgence_nom">Nom du Contact</label>
                        <input type="text" id="contact_urgence_nom" name="contact_urgence_nom">
                    </div>
                    <div class="form-group">
                        <label for="contact_urgence_lien">Lien de Parenté</label>
                        <input type="text" id="contact_urgence_lien" name="contact_urgence_lien" placeholder="Ex: Mère, Père, Grand-mère...">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="contact_urgence_telephone">Téléphone d'Urgence</label>
                    <input type="tel" id="contact_urgence_telephone" name="contact_urgence_telephone">
                </div>
                
                <div class="section-title"> Préférences & Habitudes</div>
                <div class="form-group form-full">
                    <label for="aliments_preferes">Aliments Préférés</label>
                    <textarea id="aliments_preferes" name="aliments_preferes" placeholder="Ex: Pâtes, Fromage, Fruits rouges..."></textarea>
                </div>
                
                <div class="form-group form-full">
                    <label for="aliments_interdits">Aliments à Éviter</label>
                    <textarea id="aliments_interdits" name="aliments_interdits" placeholder="Ex: Épinards, Poisson..."></textarea>
                </div>
                
                <div class="form-group">
                    <label for="horaire_sieste">Horaire de Sieste</label>
                    <input type="text" id="horaire_sieste" name="horaire_sieste" placeholder="Ex: 13h00 à 15h00">
                </div>
                
                <div class="section-title"> Autorisations</div>
                <div class="checkbox-group">
                    <div class="checkbox-item">
                        <input type="checkbox" id="autorise_photos" name="autorise_photos" checked>
                        <label for="autorise_photos" style="margin-bottom: 0;"> Autorisé à être photographié</label>
                    </div>
                    <div class="checkbox-item">
                        <input type="checkbox" id="autorise_sorties" name="autorise_sorties" checked>
                        <label for="autorise_sorties" style="margin-bottom: 0;"> Autorisé aux sorties</label>
                    </div>
                </div>
                
                <div class="section-title"> Notes Spéciales</div>
                <div class="form-group form-full">
                    <label for="notes_speciales">Observations & Remarques</label>
                    <textarea id="notes_speciales" name="notes_speciales" placeholder="Toute information particulière que nous devrions connaître..."></textarea>
                </div>
                
                <button type="submit"> Inscrire</button>
            </form>
            
            <div id="tableView" style="display: block;">
                <div style="margin-bottom: 20px;">
                    <button class="btn-view" onclick="afficherNouvelleInscription()" style="margin-top: 0;"> Ajouter un enfant</button>
                </div>
                <table id="tableEnfants">
                    <thead>
                        <tr>
                            <th>Enfant</th>
                            <th>Date Naissance</th>
                            <th>Classe</th>
                            <th>Groupe Sanguin</th>
                            <th>Allergies</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="inscriptionsBody">
                        <tr><td colspan="6" class="empty-message">Aucune inscription</td></tr>
                    </tbody>
                </table>
            </div>
            
            <div id="classesView" style="display: none;">
                <div style="margin-bottom: 20px;">
                    <button class="btn-view" onclick="afficherFormulaireClasse()" style="margin-top: 0;"> Ajouter une classe</button>
                </div>
                
                <div id="classesContainer" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(350px, 1fr)); gap: 20px;">
                    <!-- Classes will be rendered here -->
                </div>
            </div>
        </div>
        
        <div id="detailsView" class="view">
            <button class="btn-back" onclick="afficherFormulaire()">← Retour</button>
            <div id="detailsContent"></div>
        </div>
        
        <div id="classeFormView" class="view">
            <button class="btn-back" onclick="afficherClasses()">← Retour</button>
            
            <div id="messageClasse" class="message"></div>
            
            <form id="classeForm">
                <div class="section-title"> Classe</div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="classe_nom">Nom de la Classe *</label>
                        <input type="text" id="classe_nom" name="nom" placeholder="Ex: Très Petite Section">
                    </div>
                    <div class="form-group">
                        <label for="classe_couleur">Couleur *</label>
                        <select id="classe_couleur" name="couleur">
                            <option value="">-- Sélectionner --</option>
                            <option value="Jaune"> Jaune</option>
                            <option value="Bleu"> Bleu</option>
                            <option value="Vert"> Vert</option>
                            <option value="Rouge"> Rouge</option>
                            <option value="Orange"> Orange</option>
                            <option value="Violet"> Violet</option>
                            <option value="Rose"> Rose</option>
                        </select>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" placeholder="Description de la classe..."></textarea>
                </div>
                
                <div class="form-row-3">
                    <div class="form-group">
                        <label for="age_minimum">Âge Minimum</label>
                        <input type="number" id="age_minimum" name="age_minimum" placeholder="2">
                    </div>
                    <div class="form-group">
                        <label for="age_maximum">Âge Maximum</label>
                        <input type="number" id="age_maximum" name="age_maximum" placeholder="6">
                    </div>
                    <div class="form-group">
                        <label for="capacite_max">Capacité Max</label>
                        <input type="number" id="capacite_max" name="capacite_max" value="25">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="educateur_principal">Éducateur Principal</label>
                        <input type="text" id="educateur_principal" name="educateur_principal" placeholder="Nom de l'éducateur">
                    </div>
                    <div class="form-group">
                        <label for="salle">Salle</label>
                        <input type="text" id="salle" name="salle" placeholder="Ex: Salle A">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="horaires">Horaires</label>
                    <textarea id="horaires" name="horaires" placeholder="Ex: Lundi-Vendredi 8h30-16h30"></textarea>
                </div>
                
                <button type="submit"> Créer</button>
            </form>
        </div>
        
        <div id="classeDetailsView" class="view">
            <button class="btn-back" onclick="afficherClasses()">← Retour</button>
            <div id="classeDetailsContent"></div>
        </div>
    </div>
    </div><!-- end page-wrapper -->
    
    <script src="/TinyTrack/assets/js/inscriptions/inscription.js?v=2"></script>
</body>
</html>

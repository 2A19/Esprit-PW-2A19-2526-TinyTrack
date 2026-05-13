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
    <title>TinyTrack - Suivi GPS</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 40px 30px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            text-align: center;
        }

        h1 { color: #333; font-size: 24px; margin-bottom: 6px; }
        .subtitle { color: #888; font-size: 14px; margin-bottom: 30px; }

        .form-group { margin-bottom: 20px; text-align: left; }
        label { display: block; font-weight: 600; color: #555; margin-bottom: 6px; font-size: 14px; }

        select, input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            font-size: 14px;
            font-family: inherit;
            background: #f8f9fa;
            transition: border-color 0.2s;
        }
        select:focus, input:focus {
            outline: none;
            border-color: #667eea;
            background: white;
        }

        .btn {
            width: 100%;
            padding: 14px;
            border: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        .btn-start {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            box-shadow: 0 8px 25px rgba(40,167,69,0.4);
        }
        .btn-start:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(40,167,69,0.5); }

        .btn-stop {
            background: linear-gradient(135deg, #dc3545, #e74c3c);
            color: white;
            box-shadow: 0 8px 25px rgba(220,53,69,0.4);
            display: none;
        }
        .btn-stop:hover { transform: translateY(-2px); }

        /* Status indicator */
        .status-box {
            margin-top: 25px;
            padding: 15px;
            border-radius: 12px;
            font-size: 14px;
            display: none;
        }
        .status-box.active {
            display: block;
            background: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .status-box.error {
            display: block;
            background: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .status-box.waiting {
            display: block;
            background: #fff3cd;
            border: 2px solid #ffc107;
            color: #856404;
        }

        .pulse {
            display: inline-block;
            width: 12px;
            height: 12px;
            background: #28a745;
            border-radius: 50%;
            margin-right: 8px;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 0 rgba(40,167,69,0.6); }
            70%  { box-shadow: 0 0 0 10px rgba(40,167,69,0); }
            100% { box-shadow: 0 0 0 0 rgba(40,167,69,0); }
        }

        .coords {
            font-family: monospace;
            font-size: 12px;
            color: #666;
            margin-top: 8px;
        }

        .counter {
            font-size: 12px;
            color: #888;
            margin-top: 6px;
        }

        .nav-back {
            display: block;
            margin-top: 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            font-size: 14px;
        }
        .nav-back:hover { color: white; }
    </style>
</head>
<body>

    <div class="card">
        <h1> Suivi GPS</h1>
        <p class="subtitle">Page éducateur — envoie la position en temps réel</p>

        <div class="form-group">
            <label for="enfant_id">Enfant à suivre</label>
            <select id="enfant_id">
                <option value="">-- Sélectionner un enfant --</option>
            </select>
        </div>

        <div class="form-group">
            <label for="intervalle">Fréquence d'envoi</label>
            <select id="intervalle">
                <option value="10000">Toutes les 10 secondes</option>
                <option value="30000" selected>Toutes les 30 secondes</option>
                <option value="60000">Toutes les minutes</option>
            </select>
        </div>

        <button class="btn btn-start" id="btnStart" onclick="demarrer()">
            ▶ Démarrer le suivi
        </button>
        <button class="btn btn-stop" id="btnStop" onclick="arreter()">
            ⏹ Arrêter le suivi
        </button>

        <div class="status-box waiting" id="statusBox">
            En attente...
        </div>
    </div>

    <a class="nav-back" href="../index.html">← Retour à l'accueil</a>

    <script>
        let intervalId = null;
        let envoisCount = 0;

        // Header requis pour bypasser la page d'avertissement ngrok
        const NGROK_HEADERS = { 'ngrok-skip-browser-warning': '1' };

        // Charger la liste des enfants
        fetch('../api/get_enfants.php', { headers: NGROK_HEADERS })
            .then(r => r.json())
            .then(d => {
                const select = document.getElementById('enfant_id');
                if (d.succes && d.enfants) {
                    d.enfants.forEach(e => {
                        select.innerHTML += `<option value="${e.id}">${e.enfant_prenom} ${e.enfant_nom} — ${e.classe_nom}</option>`;
                    });
                }
            })
            .catch(() => {
                document.getElementById('statusBox').className = 'status-box error';
                document.getElementById('statusBox').textContent = ' Impossible de charger les enfants. Vérifiez le serveur.';
            });

        function envoyerPosition() {
            const enfantId = document.getElementById('enfant_id').value;
            if (!enfantId) return;

            if (!navigator.geolocation) {
                setStatus('error', ' La géolocalisation n\'est pas supportée par ce navigateur.');
                arreter();
                return;
            }

            navigator.geolocation.getCurrentPosition(
                function(pos) {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    const acc = pos.coords.accuracy;
                    envoisCount++;

                    const data = new FormData();
                    data.append('enfant_id', enfantId);
                    data.append('latitude',  lat);
                    data.append('longitude', lng);
                    data.append('precision', acc);

                    fetch('../api/save_position.php', {
                        method: 'POST',
                        headers: { 'ngrok-skip-browser-warning': '1' },
                        body: data
                    })
                        .then(r => r.json())
                        .then(d => {
                            if (d.succes) {
                                const now = new Date().toLocaleTimeString('fr-FR');
                                document.getElementById('statusBox').innerHTML = `
                                    <span class="pulse"></span><strong>Suivi actif</strong><br>
                                    <div class="coords">Lat: ${lat.toFixed(6)} | Lng: ${lng.toFixed(6)}</div>
                                    <div class="coords">Précision: ±${Math.round(acc)}m</div>
                                    <div class="counter">Dernier envoi: ${now} — Total: ${envoisCount} envois</div>
                                `;
                            }
                        })
                        .catch(() => setStatus('error', ' Erreur réseau lors de l\'envoi.'));
                },
                function(err) {
                    const msgs = {
                        1: ' Permission GPS refusée. Autorisez la localisation dans votre navigateur.',
                        2: ' Position indisponible. Vérifiez que le GPS est activé.',
                        3: ' Délai dépassé pour obtenir la position.'
                    };
                    setStatus('error', msgs[err.code] || ' Erreur GPS inconnue.');
                    arreter();
                },
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 0 }
            );
        }

        function demarrer() {
            const enfantId = document.getElementById('enfant_id').value;
            if (!enfantId) {
                setStatus('error', ' Veuillez sélectionner un enfant.');
                return;
            }

            const intervalle = parseInt(document.getElementById('intervalle').value);
            envoisCount = 0;

            document.getElementById('btnStart').style.display = 'none';
            document.getElementById('btnStop').style.display = 'block';
            document.getElementById('enfant_id').disabled = true;
            document.getElementById('intervalle').disabled = true;

            setStatus('waiting', '⏳ Obtention de la position GPS...');

            // Premier envoi immédiat
            envoyerPosition();
            intervalId = setInterval(envoyerPosition, intervalle);
        }

        function arreter() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
            }
            document.getElementById('btnStart').style.display = 'block';
            document.getElementById('btnStop').style.display = 'none';
            document.getElementById('enfant_id').disabled = false;
            document.getElementById('intervalle').disabled = false;
            setStatus('waiting', '⏹ Suivi arrêté. ' + (envoisCount > 0 ? envoisCount + ' positions enregistrées.' : ''));
        }

        function setStatus(type, msg) {
            const box = document.getElementById('statusBox');
            box.className = 'status-box ' + type;
            box.innerHTML = msg;
        }
    </script>
</body>
</html>

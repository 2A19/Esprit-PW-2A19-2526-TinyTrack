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
    <title>TinyTrack - Localisation</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f0f2f5; min-height: 100vh; display: flex; flex-direction: column; }

        .navbar {
            background: #1a73e8; padding: 0 20px; height: 56px;
            display: flex; align-items: center; justify-content: space-between; flex-shrink: 0;
        }
        .navbar-brand { color: white; font-size: 18px; font-weight: 700; text-decoration: none; }
        .navbar a { color: rgba(255,255,255,0.85); text-decoration: none; font-size: 13px; padding: 6px 12px; border-radius: 6px; }
        .navbar a:hover { background: rgba(255,255,255,0.15); color: white; }

        .top-panel {
            background: white; padding: 14px 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex; gap: 10px; align-items: center; flex-wrap: wrap; flex-shrink: 0;
        }

        select {
            padding: 10px 14px; border: 2px solid #e0e0e0; border-radius: 8px;
            font-size: 14px; font-family: inherit; background: #f8f9fa; cursor: pointer; min-width: 220px;
        }
        select:focus { outline: none; border-color: #1a73e8; background: white; }

        .btn {
            padding: 10px 18px; border: none; border-radius: 8px;
            font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.2s;
        }
        .btn:disabled { opacity: 0.5; cursor: not-allowed; }
        .btn-blue   { background: #1a73e8; color: white; }
        .btn-blue:hover:not(:disabled)   { background: #1557b0; }
        .btn-red    { background: #dc3545; color: white; }
        .btn-red:hover    { background: #b02a37; }
        .btn-purple { background: #6f42c1; color: white; }
        .btn-purple:hover:not(:disabled) { background: #5a32a3; }

        .badge {
            padding: 6px 14px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .badge-green  { background: #d4edda; color: #155724; }
        .badge-red    { background: #f8d7da; color: #721c24; }
        .badge-yellow { background: #fff3cd; color: #856404; }
        .badge-purple { background: #e9d8fd; color: #5a32a3; }

        .info-card {
            background: #f8f9fa; border-radius: 10px; padding: 10px 14px;
            font-size: 13px; border-left: 4px solid #1a73e8;
        }
        .info-card strong { color: #1a73e8; }
        .info-card .meta { color: #888; margin-top: 3px; font-size: 12px; }

        #map { flex: 1; min-height: 450px; }

        .bottom-bar {
            background: white; padding: 8px 20px; font-size: 12px; color: #888;
            border-top: 1px solid #eee; display: flex; justify-content: space-between; flex-shrink: 0;
        }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="../index.html"> TinyTrack</a>
    <div style="display:flex; gap:4px;">
        <a href="../index.html">Inscription</a>
        <a href="../frontoffice/liste.html">Liste</a>
        <a href="../backoffice/gestion.html">Gestion</a>
        <a href="../stats/dashboard.html">Statistiques</a>
        <a href="envoyer.html">Mode éducateur</a>
    </div>
</nav>

<div class="top-panel">
    <select id="enfant_id" onchange="onEnfantChange()">
        <option value="">-- Sélectionner un enfant --</option>
    </select>

    <button class="btn btn-blue"   id="btnSuivre" onclick="demarrerSuivi()"     disabled> Suivre en direct</button>
    <button class="btn btn-purple" id="btnSim"    onclick="demarrerSimulation()" disabled> Simuler trajet</button>
    <button class="btn btn-red"    id="btnStop"   onclick="arreter()"            style="display:none">⏹ Arrêter</button>

    <span id="badge" class="badge badge-yellow" style="display:none"></span>

    <div id="infoCard" class="info-card" style="display:none">
        <strong id="infoNom"></strong>
        <div class="meta" id="infoClasse"></div>
        <div class="meta" id="infoCoords"></div>
        <div class="meta" id="infoHeure"></div>
    </div>
</div>

<div id="map"></div>

<div class="bottom-bar">
    <span id="statusText">Sélectionnez un enfant puis cliquez "Suivre en direct" ou "Simuler trajet".</span>
    <span id="refreshCount"></span>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
// ── Carte centrée sur Tunis ───────────────────────────────────────────────────
const JARDIN_LAT = 36.8190;
const JARDIN_LNG = 10.1658;

const map = L.map('map').setView([JARDIN_LAT, JARDIN_LNG], 14);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
    maxZoom: 19
}).addTo(map);

// Marqueur fixe jardin d'enfants
const iconJardin = L.divIcon({
    html: `<div style="background:#ff6b35;width:40px;height:40px;border-radius:50%;border:3px solid white;
                box-shadow:0 4px 12px rgba(0,0,0,0.3);display:flex;align-items:center;justify-content:center;font-size:20px;"></div>`,
    iconSize: [40, 40], iconAnchor: [20, 20], className: ''
});
L.marker([JARDIN_LAT, JARDIN_LNG], { icon: iconJardin })
    .addTo(map)
    .bindPopup('<strong> TinyTrack</strong><br>Jardin d\'enfants — Tunis')
    .openPopup();

// Icône enfant
const iconEnfant = L.divIcon({
    html: `<div style="background:#1a73e8;width:36px;height:36px;border-radius:50% 50% 50% 0;
                transform:rotate(-45deg);border:3px solid white;box-shadow:0 4px 12px rgba(0,0,0,0.3);
                display:flex;align-items:center;justify-content:center;">
                <span style="transform:rotate(45deg);font-size:16px;"></span></div>`,
    iconSize: [36, 36], iconAnchor: [18, 36], className: ''
});

let marker      = null;
let cercle      = null;
let intervalId  = null;
let simInterval = null;
let simStep     = 0;
let refreshCount = 0;
let firstLoad   = true;
let currentId   = null;

// ── Charger enfants ───────────────────────────────────────────────────────────
fetch('../api/get_enfants.php')
    .then(r => r.json())
    .then(d => {
        const sel = document.getElementById('enfant_id');
        if (d.succes && d.enfants && d.enfants.length > 0) {
            d.enfants.forEach(e => {
                sel.innerHTML += `<option value="${e.id}">${e.enfant_prenom} ${e.enfant_nom} — ${e.classe_nom}</option>`;
            });
        } else {
            sel.innerHTML = '<option value="">Aucun enfant inscrit</option>';
        }
    })
    .catch(() => setStatus('Erreur : impossible de charger les enfants.'));

function onEnfantChange() {
    const id = document.getElementById('enfant_id').value;
    document.getElementById('btnSuivre').disabled = !id;
    document.getElementById('btnSim').disabled    = !id;
}

// ── Suivi réel ────────────────────────────────────────────────────────────────
function demarrerSuivi() {
    currentId = document.getElementById('enfant_id').value;
    if (!currentId) return;
    firstLoad = true; refreshCount = 0;
    showControls('running');
    setBadge('yellow', '⏳ Connexion...');
    setStatus('Connexion au serveur...');
    actualiser();
    intervalId = setInterval(actualiser, 15000);
}

// ── Simulation trajet Tunis ───────────────────────────────────────────────────
const TRAJET = [
    { lat: 36.8190, lng: 10.1658, label: 'Jardin d\'enfants TinyTrack' },
    { lat: 36.8205, lng: 10.1680, label: 'Rue de la Liberté' },
    { lat: 36.8220, lng: 10.1710, label: 'Avenue Habib Bourguiba' },
    { lat: 36.8235, lng: 10.1745, label: 'Place de la République' },
    { lat: 36.8255, lng: 10.1775, label: 'Parc du Belvédère' },
    { lat: 36.8270, lng: 10.1810, label: 'Lac de Tunis' },
    { lat: 36.8255, lng: 10.1840, label: 'Les Berges du Lac' },
    { lat: 36.8235, lng: 10.1810, label: 'Retour vers le centre' },
    { lat: 36.8215, lng: 10.1775, label: 'Avenue de France' },
    { lat: 36.8200, lng: 10.1720, label: 'Médina de Tunis' },
    { lat: 36.8190, lng: 10.1658, label: 'Retour au jardin d\'enfants' },
];

function demarrerSimulation() {
    currentId = document.getElementById('enfant_id').value;
    if (!currentId) return;
    simStep = 0; firstLoad = true; refreshCount = 0;
    showControls('running');
    setBadge('purple', ' Simulation en cours...');
    setStatus('Simulation du trajet à Tunis...');
    envoyerEtape();
    simInterval = setInterval(() => {
        simStep++;
        if (simStep >= TRAJET.length) {
            clearInterval(simInterval);
            simInterval = null;
            setBadge('green', ' Simulation terminée');
            setStatus('Trajet complet — retour au jardin d\'enfants.');
            showControls('stopped');
            return;
        }
        envoyerEtape();
    }, 3000);
}

function envoyerEtape() {
    const pos = TRAJET[simStep];
    // Petit bruit aléatoire ±0.0003° ≈ ±30m pour simuler GPS réel
    const lat = pos.lat + (Math.random() - 0.5) * 0.0006;
    const lng = pos.lng + (Math.random() - 0.5) * 0.0006;
    const acc = Math.floor(Math.random() * 20) + 5; // 5-25m

    const data = new FormData();
    data.append('enfant_id', currentId);
    data.append('latitude',  lat);
    data.append('longitude', lng);
    data.append('precision', acc);

    fetch('../api/save_position.php', { method: 'POST', body: data })
        .then(r => r.json())
        .then(d => {
            if (d.succes) {
                refreshCount++;
                placerMarqueur(lat, lng, acc, pos.label);
                setBadge('purple', ` ${pos.label}`);
                setStatus(`Étape ${simStep + 1}/${TRAJET.length} — ${pos.label}`);
                document.getElementById('refreshCount').textContent = `Positions envoyées : ${refreshCount}`;
            }
        })
        .catch(() => setStatus('Erreur réseau lors de la simulation.'));
}

// ── Actualiser depuis la DB (suivi réel) ──────────────────────────────────────
function actualiser() {
    if (!currentId) return;
    fetch(`../api/get_position.php?enfant_id=${currentId}`)
        .then(r => r.json())
        .then(d => {
            if (!d.succes) {
                setBadge('red', ' Aucune position');
                setStatus('Aucune position disponible. L\'éducateur doit démarrer le suivi sur son téléphone.');
                return;
            }
            const pos = d.position;
            const lat = parseFloat(pos.latitude);
            const lng = parseFloat(pos.longitude);
            const acc = pos.precision_metres ? Math.round(pos.precision_metres) : null;
            const heure = new Date(pos.timestamp).toLocaleTimeString('fr-FR');
            const date  = new Date(pos.timestamp).toLocaleDateString('fr-FR');
            refreshCount++;

            placerMarqueur(lat, lng, acc, `${pos.enfant_prenom} ${pos.enfant_nom}`);

            document.getElementById('infoCard').style.display = 'block';
            document.getElementById('infoNom').textContent    = `${pos.enfant_prenom} ${pos.enfant_nom}`;
            document.getElementById('infoClasse').textContent = pos.classe_nom;
            document.getElementById('infoCoords').textContent = ` ${lat.toFixed(5)}, ${lng.toFixed(5)}${acc ? ` (±${acc}m)` : ''}`;
            document.getElementById('infoHeure').textContent  = ` ${date} à ${heure}`;

            setBadge('green', ' En direct');
            setStatus(`Dernière mise à jour : ${heure}`);
            document.getElementById('refreshCount').textContent = `Actualisations : ${refreshCount}`;
        })
        .catch(() => {
            setBadge('red', ' Erreur réseau');
            setStatus('Impossible de contacter le serveur. Vérifiez que XAMPP est démarré.');
        });
}

// ── Placer/déplacer le marqueur enfant ────────────────────────────────────────
function placerMarqueur(lat, lng, acc, label) {
    if (marker) {
        marker.setLatLng([lat, lng]);
        marker.getPopup().setContent(`<strong> ${label}</strong>`);
    } else {
        marker = L.marker([lat, lng], { icon: iconEnfant })
            .addTo(map)
            .bindPopup(`<strong> ${label}</strong>`);
    }
    if (acc) {
        if (cercle) {
            cercle.setLatLng([lat, lng]).setRadius(acc);
        } else {
            cercle = L.circle([lat, lng], {
                radius: acc, color: '#1a73e8',
                fillColor: '#1a73e8', fillOpacity: 0.12, weight: 1
            }).addTo(map);
        }
    }
    if (firstLoad) {
        map.setView([lat, lng], 16);
        firstLoad = false;
    }
}

// ── Arrêter tout ──────────────────────────────────────────────────────────────
function arreter() {
    if (intervalId)  { clearInterval(intervalId);  intervalId  = null; }
    if (simInterval) { clearInterval(simInterval); simInterval = null; }
    showControls('stopped');
    setBadge('red', '⏹ Arrêté');
    setStatus('Suivi arrêté.');
}

// ── UI helpers ────────────────────────────────────────────────────────────────
function showControls(state) {
    const btnSuivre = document.getElementById('btnSuivre');
    const btnSim    = document.getElementById('btnSim');
    const btnStop   = document.getElementById('btnStop');
    const sel       = document.getElementById('enfant_id');
    if (state === 'running') {
        btnSuivre.style.display = 'none';
        btnSim.style.display    = 'none';
        btnStop.style.display   = 'inline-block';
        sel.disabled = true;
    } else {
        btnSuivre.style.display = 'inline-block';
        btnSim.style.display    = 'inline-block';
        btnStop.style.display   = 'none';
        sel.disabled = false;
    }
}

function setBadge(color, text) {
    const b = document.getElementById('badge');
    b.style.display = 'inline-block';
    b.className = `badge badge-${color}`;
    b.textContent = text;
}

function setStatus(text) {
    document.getElementById('statusText').textContent = text;
}
</script>
</body>
</html>

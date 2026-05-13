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
    <title>TinyTrack — Tableau de bord</title>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f0f2f5;
            min-height: 100vh;
        }

        /* ── Navbar ── */
        .navbar {
            background: #1a73e8;
            padding: 0 24px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .navbar-brand { color: white; font-size: 20px; font-weight: 700; text-decoration: none; }
        .navbar-links { display: flex; gap: 4px; }
        .navbar-links a {
            color: rgba(255,255,255,0.85);
            text-decoration: none;
            padding: 8px 14px;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 500;
            transition: background 0.2s;
        }
        .navbar-links a:hover { background: rgba(255,255,255,0.15); color: white; }
        .navbar-links a.active { background: rgba(255,255,255,0.25); color: white; }

        /* ── Page wrapper ── */
        .page { padding: 28px 24px; max-width: 1200px; margin: 0 auto; }

        .page-title {
            font-size: 26px;
            font-weight: 700;
            color: #202124;
            margin-bottom: 6px;
        }
        .page-subtitle { color: #888; font-size: 14px; margin-bottom: 28px; }

        /* ── KPI cards ── */
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 16px;
            margin-bottom: 28px;
        }

        .kpi-card {
            background: white;
            border-radius: 14px;
            padding: 22px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
            display: flex;
            align-items: center;
            gap: 16px;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .kpi-card:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.1); }

        .kpi-icon {
            width: 52px; height: 52px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px;
            flex-shrink: 0;
        }
        .kpi-icon.blue   { background: #e8f0fe; }
        .kpi-icon.green  { background: #e6f4ea; }
        .kpi-icon.orange { background: #fef3e2; }
        .kpi-icon.red    { background: #fce8e6; }
        .kpi-icon.purple { background: #f3e8fd; }

        .kpi-value { font-size: 28px; font-weight: 700; color: #202124; line-height: 1; }
        .kpi-label { font-size: 13px; color: #888; margin-top: 4px; }

        /* ── Charts grid ── */
        .charts-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .charts-grid.full { grid-template-columns: 1fr; }

        @media (max-width: 768px) {
            .charts-grid { grid-template-columns: 1fr; }
        }

        .chart-card {
            background: white;
            border-radius: 14px;
            padding: 24px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.07);
        }

        .chart-title {
            font-size: 16px;
            font-weight: 600;
            color: #202124;
            margin-bottom: 4px;
        }
        .chart-subtitle { font-size: 12px; color: #aaa; margin-bottom: 20px; }

        .chart-wrapper { position: relative; height: 260px; }

        /* ── Taux de remplissage table ── */
        .fill-table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        .fill-table th {
            text-align: left;
            font-size: 12px;
            color: #888;
            font-weight: 600;
            padding: 8px 10px;
            border-bottom: 2px solid #f0f0f0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .fill-table td { padding: 12px 10px; border-bottom: 1px solid #f8f8f8; font-size: 14px; }
        .fill-table tr:last-child td { border-bottom: none; }

        .progress-bar-wrap {
            background: #f0f0f0;
            border-radius: 20px;
            height: 10px;
            overflow: hidden;
            min-width: 100px;
        }
        .progress-bar {
            height: 100%;
            border-radius: 20px;
            transition: width 0.8s ease;
        }

        .badge-taux {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-taux.low    { background: #e6f4ea; color: #137333; }
        .badge-taux.medium { background: #fff3cd; color: #856404; }
        .badge-taux.high   { background: #fce8e6; color: #c5221f; }

        /* ── Color dot ── */
        .color-dot {
            display: inline-block;
            width: 12px; height: 12px;
            border-radius: 50%;
            margin-right: 8px;
            vertical-align: middle;
        }

        /* ── Loading ── */
        .loading {
            text-align: center;
            padding: 60px;
            color: #888;
            font-size: 16px;
        }
        .spinner {
            width: 40px; height: 40px;
            border: 4px solid #e0e0e0;
            border-top-color: #1a73e8;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
            margin: 0 auto 16px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
    </style>
</head>
<body>

<nav class="navbar">
    <a class="navbar-brand" href="../index.html"> TinyTrack</a>
    <div class="navbar-links">
        <a href="../index.html">Inscription</a>
        <a href="../frontoffice/liste.html">Liste</a>
        <a href="../backoffice/gestion.html">Gestion</a>
        <a href="dashboard.html" class="active">Statistiques</a>
        <a href="../tracker/suivre.html"> Tracker</a>
    </div>
</nav>

<div class="page">
    <div class="page-title">Tableau de bord</div>
    <div class="page-subtitle">Vue d'ensemble du jardin d'enfants — mise à jour en temps réel</div>

    <div id="loading" class="loading">
        <div class="spinner"></div>
        Chargement des statistiques...
    </div>

    <div id="content" style="display:none">

        <!-- KPI Cards -->
        <div class="kpi-grid" id="kpiGrid"></div>

        <!-- Row 1: Remplissage + Groupes sanguins -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">Taux de remplissage par classe</div>
                <div class="chart-subtitle">Nombre d'enfants inscrits vs capacité maximale</div>
                <table class="fill-table" id="fillTable">
                    <thead>
                        <tr>
                            <th>Classe</th>
                            <th>Inscrits</th>
                            <th>Remplissage</th>
                            <th>Taux</th>
                        </tr>
                    </thead>
                    <tbody id="fillTableBody"></tbody>
                </table>
            </div>

            <div class="chart-card">
                <div class="chart-title">Répartition des groupes sanguins</div>
                <div class="chart-subtitle">Enfants avec groupe sanguin renseigné</div>
                <div class="chart-wrapper">
                    <canvas id="chartGroupesSanguins"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 2: Enfants par classe (barres) + Âge moyen -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">Enfants inscrits par classe</div>
                <div class="chart-subtitle">Comparaison du nombre d'inscrits</div>
                <div class="chart-wrapper">
                    <canvas id="chartEnfantsParClasse"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">Âge moyen par classe</div>
                <div class="chart-subtitle">Moyenne d'âge des enfants inscrits</div>
                <div class="chart-wrapper">
                    <canvas id="chartAgeMoyen"></canvas>
                </div>
            </div>
        </div>

        <!-- Row 3: Autorisations (pleine largeur) -->
        <div class="charts-grid">
            <div class="chart-card">
                <div class="chart-title">Autorisations photos</div>
                <div class="chart-subtitle">Enfants autorisés à être photographiés</div>
                <div class="chart-wrapper">
                    <canvas id="chartPhotos"></canvas>
                </div>
            </div>

            <div class="chart-card">
                <div class="chart-title">Autorisations sorties</div>
                <div class="chart-subtitle">Enfants autorisés aux sorties scolaires</div>
                <div class="chart-wrapper">
                    <canvas id="chartSorties"></canvas>
                </div>
            </div>
        </div>

    </div><!-- end #content -->
</div>

<script>
// Couleurs par couleur de classe
const COULEURS = {
    'Jaune':  '#ffc107',
    'Bleu':   '#1a73e8',
    'Vert':   '#28a745',
    'Rouge':  '#dc3545',
    'Orange': '#fd7e14',
    'Violet': '#6f42c1',
    'Rose':   '#e83e8c'
};

function getCouleur(nom, index) {
    return COULEURS[nom] || ['#667eea','#764ba2','#f093fb','#f5576c','#4facfe','#43e97b'][index % 6];
}

fetch('../api/get_stats.php')
    .then(r => r.json())
    .then(data => {
        document.getElementById('loading').style.display = 'none';
        document.getElementById('content').style.display = 'block';

        if (!data.succes) return;

        // ── KPI Cards ──────────────────────────────────────────────────────
        const classePleine = data.classe_pleine;
        const tauxGlobal = data.total_enfants > 0
            ? Math.round(data.classes.reduce((s, c) => s + parseFloat(c.nb_enfants), 0) /
                         data.classes.reduce((s, c) => s + parseFloat(c.capacite_max), 0) * 100)
            : 0;

        document.getElementById('kpiGrid').innerHTML = `
            <div class="kpi-card">
                <div class="kpi-icon blue"></div>
                <div>
                    <div class="kpi-value">${data.total_enfants}</div>
                    <div class="kpi-label">Enfants inscrits</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon green"></div>
                <div>
                    <div class="kpi-value">${data.total_classes}</div>
                    <div class="kpi-label">Classes actives</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon orange"></div>
                <div>
                    <div class="kpi-value">${tauxGlobal}%</div>
                    <div class="kpi-label">Taux de remplissage global</div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon red"></div>
                <div>
                    <div class="kpi-value">${classePleine ? classePleine.nb_enfants : 0}</div>
                    <div class="kpi-label">Classe la plus remplie<br><small style="color:#aaa">${classePleine ? classePleine.nom : '—'}</small></div>
                </div>
            </div>
            <div class="kpi-card">
                <div class="kpi-icon purple"></div>
                <div>
                    <div class="kpi-value">${data.groupes_sanguins.length > 0 ? data.groupes_sanguins[0].groupe_sanguin : '—'}</div>
                    <div class="kpi-label">Groupe sanguin le plus fréquent</div>
                </div>
            </div>
        `;

        // ── Table remplissage ──────────────────────────────────────────────
        document.getElementById('fillTableBody').innerHTML = data.classes.map(c => {
            const taux = parseFloat(c.taux_remplissage) || 0;
            const couleurBarre = taux >= 90 ? '#dc3545' : taux >= 60 ? '#ffc107' : '#28a745';
            const badgeClass   = taux >= 90 ? 'high' : taux >= 60 ? 'medium' : 'low';
            const dot = COULEURS[c.couleur] || '#888';
            return `
                <tr>
                    <td>
                        <span class="color-dot" style="background:${dot}"></span>
                        ${c.nom}
                    </td>
                    <td><strong>${c.nb_enfants}</strong> / ${c.capacite_max}</td>
                    <td>
                        <div class="progress-bar-wrap">
                            <div class="progress-bar" style="width:${Math.min(taux,100)}%; background:${couleurBarre}"></div>
                        </div>
                    </td>
                    <td><span class="badge-taux ${badgeClass}">${taux}%</span></td>
                </tr>
            `;
        }).join('');

        // ── Chart: Enfants par classe (barres) ─────────────────────────────
        new Chart(document.getElementById('chartEnfantsParClasse'), {
            type: 'bar',
            data: {
                labels: data.classes.map(c => c.nom),
                datasets: [{
                    label: 'Enfants inscrits',
                    data: data.classes.map(c => c.nb_enfants),
                    backgroundColor: data.classes.map(c => getCouleur(c.couleur, 0) + 'cc'),
                    borderColor:     data.classes.map(c => getCouleur(c.couleur, 0)),
                    borderWidth: 2,
                    borderRadius: 8
                }, {
                    label: 'Capacité max',
                    data: data.classes.map(c => c.capacite_max),
                    backgroundColor: 'rgba(0,0,0,0.05)',
                    borderColor: 'rgba(0,0,0,0.15)',
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } },
                scales: {
                    y: { beginAtZero: true, grid: { color: '#f0f0f0' } },
                    x: { grid: { display: false } }
                }
            }
        });

        // ── Chart: Groupes sanguins (donut) ───────────────────────────────
        if (data.groupes_sanguins.length > 0) {
            new Chart(document.getElementById('chartGroupesSanguins'), {
                type: 'doughnut',
                data: {
                    labels: data.groupes_sanguins.map(g => g.groupe_sanguin),
                    datasets: [{
                        data: data.groupes_sanguins.map(g => g.nb),
                        backgroundColor: ['#dc3545','#1a73e8','#28a745','#ffc107','#6f42c1','#fd7e14','#e83e8c','#20c997'],
                        borderWidth: 2,
                        borderColor: 'white'
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right' },
                        tooltip: {
                            callbacks: {
                                label: ctx => ` ${ctx.label} : ${ctx.raw} enfant(s)`
                            }
                        }
                    },
                    cutout: '60%'
                }
            });
        } else {
            document.getElementById('chartGroupesSanguins').parentElement.innerHTML =
                '<div style="text-align:center;padding:60px;color:#aaa;font-size:14px;">Aucun groupe sanguin renseigné</div>';
        }

        // ── Chart: Âge moyen par classe (barres horizontales) ─────────────
        if (data.age_moyen_par_classe.length > 0) {
            new Chart(document.getElementById('chartAgeMoyen'), {
                type: 'bar',
                data: {
                    labels: data.age_moyen_par_classe.map(c => c.nom),
                    datasets: [{
                        label: 'Âge moyen (ans)',
                        data: data.age_moyen_par_classe.map(c => c.age_moyen),
                        backgroundColor: '#667eeacc',
                        borderColor: '#667eea',
                        borderWidth: 2,
                        borderRadius: 8
                    }]
                },
                options: {
                    indexAxis: 'y',
                    responsive: true, maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        x: { beginAtZero: true, max: 7, grid: { color: '#f0f0f0' },
                             title: { display: true, text: 'Âge (années)' } },
                        y: { grid: { display: false } }
                    }
                }
            });
        } else {
            document.getElementById('chartAgeMoyen').parentElement.innerHTML =
                '<div style="text-align:center;padding:60px;color:#aaa;font-size:14px;">Aucune donnée disponible</div>';
        }

        // ── Chart: Autorisations photos (donut) ───────────────────────────
        const auth = data.autorisations;
        if (auth && data.total_enfants > 0) {
            new Chart(document.getElementById('chartPhotos'), {
                type: 'doughnut',
                data: {
                    labels: ['Autorisé', 'Non autorisé'],
                    datasets: [{
                        data: [auth.photos_oui, auth.photos_non],
                        backgroundColor: ['#28a745cc', '#dc3545cc'],
                        borderColor: ['#28a745', '#dc3545'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label} : ${ctx.raw} enfant(s)` } }
                    },
                    cutout: '55%'
                }
            });

            new Chart(document.getElementById('chartSorties'), {
                type: 'doughnut',
                data: {
                    labels: ['Autorisé', 'Non autorisé'],
                    datasets: [{
                        data: [auth.sorties_oui, auth.sorties_non],
                        backgroundColor: ['#1a73e8cc', '#fd7e14cc'],
                        borderColor: ['#1a73e8', '#fd7e14'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'bottom' },
                        tooltip: { callbacks: { label: ctx => ` ${ctx.label} : ${ctx.raw} enfant(s)` } }
                    },
                    cutout: '55%'
                }
            });
        }
    })
    .catch(err => {
        document.getElementById('loading').innerHTML =
            '<div style="color:#dc3545"> Impossible de charger les statistiques. Vérifiez que le serveur est démarré.</div>';
    });
</script>
</body>
</html>

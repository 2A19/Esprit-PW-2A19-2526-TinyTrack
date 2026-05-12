<?php
if (session_status() === PHP_SESSION_NONE) session_start();
/**
 * Module : Gestion Rapport
 * @author Mohamed Fadhlaoui <fadhlaoui1212@gmail.com>
 */
require_once __DIR__ . '/../../../Controller/RapportController.php';

if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    header('Location: listRapports.php');
    exit;
}

$id = (int) $_GET['id'];

$controller = new RapportController();
$rapports = $controller->listRapportsWithActivite()->fetchAll();

$rapport = null;

foreach ($rapports as $r) {
    if ((int)$r['id_rapport'] === $id) {
        $rapport = $r;
        break;
    }
}

if (!$rapport) {
    header('Location: listRapports.php');
    exit;
}

require_once __DIR__ . '/../../../config/secrets.php';
$apiKey = defined('GROQ_API_KEY') ? GROQ_API_KEY : getenv('GROQ_API_KEY');

$analyse = null;
$error = null;

if (!$apiKey) {
    $error = "Clé GROQ introuvable.";
} else {

    $prompt = "
Tu es un expert pédagogique.

Analyse ce rapport d'enfant et donne une réponse claire, professionnelle et bien structurée.

Règles:
- Réponse de longueur moyenne, ni trop courte ni trop longue.
- Phrases simples, lisibles et utiles.
- Maximum 2 lignes par section.
- Ne mets pas de texte inutile avant ou après l'analyse.

Format obligatoire:

1. Niveau d'adaptation : Adaptée / Moyenne / Non adaptée
2. Analyse : ...
3. Points positifs : ...
4. Points à améliorer : ...
5. Recommandation pédagogique : ...

Données:
Enfant ID: {$rapport['id_enfant']}
Activité: {$rapport['nom_activite']}
Contenu: {$rapport['contenu_rapport']}
";

    $data = [
        "model" => "llama-3.3-70b-versatile",
        "messages" => [
            ["role" => "user", "content" => $prompt]
        ]
    ];

    $ch = curl_init("https://api.groq.com/openai/v1/chat/completions");

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Content-Type: application/json",
        "Authorization: Bearer " . $apiKey
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
    } else {
        $result = json_decode($response, true);

        if (isset($result['choices'][0]['message']['content'])) {
            $analyse = $result['choices'][0]['message']['content'];
        } else {
            $error = "Réponse GROQ invalide";
            echo "<pre>";
            print_r($result);
            echo "</pre>";
            exit;
        }
    }

    curl_close($ch);
}

$niveau = "Moyenne";
$score = 60;
$niveauClass = "medium";

if ($analyse) {
    $analyseLower = strtolower($analyse);

    if (strpos($analyseLower, 'adaptée') !== false && strpos($analyseLower, 'non adaptée') === false) {
        $niveau = "Adaptée";
        $score = 88;
        $niveauClass = "good";
    } elseif (strpos($analyseLower, 'non adaptée') !== false) {
        $niveau = "Non adaptée";
        $score = 35;
        $niveauClass = "bad";
    } elseif (strpos($analyseLower, 'moyenne') !== false) {
        $niveau = "Moyenne";
        $score = 60;
        $niveauClass = "medium";
    }
}

include __DIR__ . '/../template/header.php';
include __DIR__ . '/../template/sidebar.php';
?>

<style>
.ai-page-title {
    font-weight: 900;
    color: #243447;
    margin-bottom: 5px;
}

.ai-subtitle {
    color: #6c757d;
    font-size: 15px;
}

.ai-card {
    border: none;
    border-radius: 20px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.08);
    margin-bottom: 22px;
}

.ai-card-header {
    padding: 18px 24px;
    font-weight: 900;
    font-size: 20px;
    color: white;
}

.ai-info-header {
    background: linear-gradient(135deg, #17a2b8, #4fc3dc);
}

.ai-result-header {
    background: linear-gradient(135deg, #28a745, #70d88b);
}

.ai-error-header {
    background: linear-gradient(135deg, #dc3545, #ff6b6b);
}

.ai-info-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
    margin-bottom: 18px;
}

.ai-info-box {
    background: #f8fbff;
    border: 1px solid #e5eef7;
    border-radius: 16px;
    padding: 16px;
}

.ai-info-box span {
    display: block;
    font-size: 13px;
    color: #6c757d;
    margin-bottom: 6px;
    font-weight: 700;
}

.ai-info-box strong {
    font-size: 19px;
    color: #243447;
}

.ai-content-box {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 16px;
    padding: 18px;
    line-height: 1.7;
}

.ai-badge {
    display: inline-block;
    background: #e9f7ef;
    color: #28a745;
    border-radius: 25px;
    padding: 7px 14px;
    font-weight: 800;
    font-size: 13px;
    margin-bottom: 14px;
}

.ai-dashboard {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 18px;
    margin-bottom: 20px;
}

.ai-score-card {
    border-radius: 18px;
    padding: 22px;
    color: white;
    background: linear-gradient(135deg, #343a40, #495057);
}

.ai-score-card.good {
    background: linear-gradient(135deg, #28a745, #78d98f);
}

.ai-score-card.medium {
    background: linear-gradient(135deg, #ffc107, #ffda6a);
    color: #3b2f00;
}

.ai-score-card.bad {
    background: linear-gradient(135deg, #dc3545, #ff7676);
}

.ai-score-title {
    font-size: 14px;
    font-weight: 800;
    opacity: 0.9;
}

.ai-score-value {
    font-size: 42px;
    font-weight: 900;
    margin: 8px 0;
}

.ai-progress {
    height: 12px;
    border-radius: 20px;
    background: rgba(255,255,255,0.35);
    overflow: hidden;
}

.ai-progress-fill {
    height: 100%;
    background: white;
    border-radius: 20px;
}

.ai-status-card {
    background: #ffffff;
    border: 1px solid #e9ecef;
    border-radius: 18px;
    padding: 22px;
}

.ai-status-label {
    color: #6c757d;
    font-size: 14px;
    font-weight: 800;
}

.ai-status-pill {
    display: inline-block;
    margin-top: 10px;
    padding: 10px 18px;
    border-radius: 25px;
    font-size: 18px;
    font-weight: 900;
}

.ai-status-pill.good {
    background: #e9f7ef;
    color: #28a745;
}

.ai-status-pill.medium {
    background: #fff6d9;
    color: #b88700;
}

.ai-status-pill.bad {
    background: #fde8ea;
    color: #dc3545;
}

.ai-result-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.ai-insight-card {
    background: #ffffff;
    border: 1px solid #edf0f3;
    border-radius: 18px;
    padding: 18px;
    min-height: 120px;
    border-left: 5px solid #28a745;
}

.ai-insight-card h4 {
    font-size: 22px;
    font-weight: 900;
    margin-bottom: 14px;
    color: #0d6efd;
    display: flex;
    align-items: center;
    gap: 10px;
    letter-spacing: 0.3px;
}
.analysis h4 {
    color: #17a2b8;
}

.positive h4 {
    color: #28a745;
}

.warning h4 {
    color: #ffc107;
}

.reco h4 {
    color: #6f42c1;
}

.ai-insight-card p {
    color: #212529;
    line-height: 1.7;
    margin: 0;
}

.ai-insight-card.analysis {
    border-left-color: #17a2b8;
}

.ai-insight-card.positive {
    border-left-color: #28a745;
}

.ai-insight-card.warning {
    border-left-color: #ffc107;
}

.ai-insight-card.reco {
    border-left-color: #6f42c1;
    grid-column: span 2;
}

.ai-back-btn {
    border-radius: 25px;
    padding: 10px 22px;
    font-weight: 800;
}

@media (max-width: 768px) {
    .ai-info-grid,
    .ai-dashboard,
    .ai-result-grid {
        grid-template-columns: 1fr;
    }

    .ai-insight-card.reco {
        grid-column: span 1;
    }
}
</style>

<?php
$sections = [
    'analyse' => '',
    'positifs' => '',
    'ameliorer' => '',
    'recommandation' => ''
];

if ($analyse) {
    $lines = explode("\n", $analyse);

    foreach ($lines as $line) {
        $line = trim($line);

        if ($line === '') {
            continue;
        }

        if (stripos($line, 'Analyse') !== false && strpos($line, ':') !== false) {
            $sections['analyse'] = trim(explode(':', $line, 2)[1]);
        } elseif (stripos($line, 'Points positifs') !== false && strpos($line, ':') !== false) {
            $sections['positifs'] = trim(explode(':', $line, 2)[1]);
        } elseif (stripos($line, 'Points à améliorer') !== false && strpos($line, ':') !== false) {
            $sections['ameliorer'] = trim(explode(':', $line, 2)[1]);
        } elseif (stripos($line, 'Recommandation') !== false && strpos($line, ':') !== false) {
            $sections['recommandation'] = trim(explode(':', $line, 2)[1]);
        }
    }
}
?>

<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <h1 class="ai-page-title">
                <i class="fas fa-brain text-info"></i> Tableau de bord IA - Rapport #<?= htmlspecialchars($rapport['id_rapport']) ?>
            </h1>
            <p class="ai-subtitle">
                Analyse intelligente générée avec l’API Groq pour aider l’éducateur à comprendre l’adaptation de l’activité à l’enfant.
            </p>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <div class="card ai-card">
                <div class="ai-card-header ai-info-header">
                    <i class="fas fa-clipboard-list"></i> Données du rapport analysé
                </div>

                <div class="card-body">
                    <div class="ai-info-grid">
                        <div class="ai-info-box">
                            <span>ID Enfant</span>
                            <strong><?= htmlspecialchars($rapport['id_enfant']) ?></strong>
                        </div>

                        <div class="ai-info-box">
                            <span>Activité</span>
                            <strong><?= htmlspecialchars($rapport['nom_activite']) ?></strong>
                        </div>

                        <div class="ai-info-box">
                            <span>Rapport</span>
                            <strong>#<?= htmlspecialchars($rapport['id_rapport']) ?></strong>
                        </div>
                    </div>

                    <div class="ai-content-box">
                        <span class="ai-badge">
                            <i class="fas fa-file-alt"></i> Contenu du rapport
                        </span>
                        <p class="mb-0"><?= htmlspecialchars($rapport['contenu_rapport']) ?></p>
                    </div>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="card ai-card">
                    <div class="ai-card-header ai-error-header">
                        <i class="fas fa-exclamation-triangle"></i> Erreur API
                    </div>
                    <div class="card-body">
                        <div class="alert alert-danger mb-0">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="card ai-card">
                    <div class="ai-card-header ai-result-header">
                        <i class="fas fa-robot"></i> Résultat de l’analyse IA
                    </div>

                    <div class="card-body">
                        <span class="ai-badge">
                            <i class="fas fa-check-circle"></i> Analyse générée avec Groq
                        </span>

                        <div class="ai-dashboard">
                            <div class="ai-score-card <?= $niveauClass ?>">
                                <div class="ai-score-title">Score d’adaptation estimé</div>
                                <div class="ai-score-value"><?= $score ?>%</div>
                                <div class="ai-progress">
                                    <div class="ai-progress-fill" style="width: <?= $score ?>%;"></div>
                                </div>
                            </div>

                            <div class="ai-status-card">
                                <div class="ai-status-label">Niveau d’adaptation</div>
                                <span class="ai-status-pill <?= $niveauClass ?>">
                                    <?= htmlspecialchars($niveau) ?>
                                </span>
                            </div>
                        </div>

                        <div class="ai-result-grid">
                            <div class="ai-insight-card analysis">
                                <h4><i class="fas fa-search"></i> Analyse du comportement</h4>
                                <p><?= htmlspecialchars($sections['analyse'] ?: 'Analyse non disponible.') ?></p>
                            </div>

                            <div class="ai-insight-card positive">
                                <h4><i class="fas fa-check-circle"></i> Points positifs</h4>
                                <p><?= htmlspecialchars($sections['positifs'] ?: 'Aucun point positif spécifique détecté.') ?></p>
                            </div>

                            <div class="ai-insight-card warning">
                                <h4><i class="fas fa-exclamation-triangle"></i> Points à améliorer</h4>
                                <p><?= htmlspecialchars($sections['ameliorer'] ?: 'Aucun point à améliorer spécifique détecté.') ?></p>
                            </div>

                            <div class="ai-insight-card reco">
                                <h4><i class="fas fa-lightbulb"></i> Recommandation pédagogique</h4>
                                <p><?= htmlspecialchars($sections['recommandation'] ?: 'Recommandation non disponible.') ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <a href="listRapports.php" class="btn btn-secondary ai-back-btn">
                <i class="fas fa-arrow-left"></i> Retour
            </a>

        </div>
    </section>
</div>

<?php include __DIR__ . '/../template/footer.php'; ?>

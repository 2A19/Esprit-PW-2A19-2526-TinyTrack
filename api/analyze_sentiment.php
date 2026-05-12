<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
// Endpoint AJAX — analyse le sentiment d'une réclamation et le persiste en base via OpenAI
header('Content-Type: application/json; charset=utf-8');

// Vérifie que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

// Récupération des paramètres : texte de la réclamation et son identifiant
$description = trim($_POST['description'] ?? '');
$id          = (int) ($_POST['id'] ?? 0);

if (empty($description) || $id <= 0) {
    http_response_code(400);
    echo json_encode(['error' => 'Paramètres manquants.']);
    exit;
}

// Chargement de la configuration OpenAI
$config = require __DIR__ . '/../config/openai.php';
$apiKey = $config['api_key'];
$model  = $config['model'];

// Prompt strict : une seule réponse parmi positif / neutre / negatif
$prompt = "Analyse le sentiment de cette réclamation client : \"$description\". "
        . "Réponds UNIQUEMENT par un seul mot parmi : positif, neutre, negatif. "
        . "Rien d'autre, juste ce mot.";

// Construction du payload pour l'API Chat Completions
$payload = json_encode([
    'model'       => $model,
    'messages'    => [
        ['role' => 'system', 'content' => 'Tu analyses des sentiments de textes. Réponds uniquement par : positif, neutre, ou negatif.'],
        ['role' => 'user',   'content' => $prompt],
    ],
    'max_tokens'  => 5,
    'temperature' => 0,
]);

// Appel curl vers l'API OpenAI avec SSL désactivé pour XAMPP local
$ch = curl_init('https://api.openai.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => $payload,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $apiKey,
    ],
    CURLOPT_TIMEOUT        => 15,
]);

$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

// En cas d'erreur réseau ou API, retourne null sans planter
if ($curlError || $httpCode !== 200) {
    echo json_encode(['sentiment' => null]);
    exit;
}

// Extraction et validation de la réponse GPT
$data      = json_decode($response, true);
$rawResult = strtolower(trim($data['choices'][0]['message']['content'] ?? ''));

// Valeur par défaut si GPT répond hors liste attendue
$allowed   = ['positif', 'neutre', 'negatif'];
$sentiment = in_array($rawResult, $allowed) ? $rawResult : 'neutre';

// Persistance du sentiment détecté en base de données
require_once __DIR__ . '/../config/config.php';
$db   = Config::getConnexion();
$stmt = $db->prepare("UPDATE reclamations SET sentiment = ? WHERE id = ?");
$stmt->execute([$sentiment, $id]);

echo json_encode(['sentiment' => $sentiment]);

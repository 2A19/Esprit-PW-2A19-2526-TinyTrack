<?php
/**
 * geocode.php — Proxy de géocodage intelligent

 *
 * Stratégies en cascade :
 *  1. Adresse complète + Tunisie
 *  2. Nettoyage (suppression mots génériques) + Tunisie
 *  3. Extraction de la ville uniquement
 *  4. Photon API (Komoot) — alternative à Nominatim
 *  5. Fallback manuel sur villes tunisiennes connues
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$raw = trim($_GET['q'] ?? '');
if (empty($raw)) {
    echo json_encode(['found' => false, 'query' => '']);
    exit;
}

// ─────────────────────────────────────────────
// Table de fallback manuelle — villes & lieux
// tunisiens courants introuvables par Nominatim
// ─────────────────────────────────────────────
$FALLBACK_COORDS = [
    // Grandes villes
    'tunis'           => [36.8065, 10.1815],
    'sfax'            => [34.7397, 10.7600],
    'sousse'          => [35.8256, 10.6369],
    'kairouan'        => [35.6712, 10.1005],
    'bizerte'         => [37.2744, 9.8739],
    'gabes'           => [33.8814, 10.0982],
    'ariana'          => [36.8625, 10.1956],
    'gafsa'           => [34.4250, 8.7842],
    'monastir'        => [35.7643, 10.8113],
    'nabeul'          => [36.4564, 10.7354],
    'hammamet'        => [36.3998, 10.6135],
    'mahdia'          => [35.5047, 11.0622],
    'beja'            => [36.7256, 9.1817],
    'jendouba'        => [36.5011, 8.7757],
    'kef'             => [36.1822, 8.7149],
    'siliana'         => [36.0844, 9.3708],
    'kasserine'       => [35.1676, 8.8365],
    'sidi bouzid'     => [35.0382, 9.4849],
    'tozeur'          => [33.9197, 8.1335],
    'kebili'          => [33.7043, 8.9687],
    'tataouine'       => [32.9211, 10.4511],
    'medenine'        => [33.3549, 10.5055],
    'zaghouan'        => [36.4029, 10.1429],
    'manouba'         => [36.8095, 10.0977],
    'ben arous'       => [36.7535, 10.2282],
    'kelibia'         => [36.8468, 11.0951],
    'nabeul kelibia'  => [36.8468, 11.0951],
    'djerba'          => [33.8333, 10.9167],
    'zarzis'          => [33.5032, 11.1121],
    'tabarka'         => [36.9544, 8.7576],
    'ain draham'      => [36.7833, 8.6833],
    'bou arada'       => [36.3617, 9.5328],
    'el menzah'       => [36.8474, 10.2021],
    'la marsa'        => [36.8783, 10.3245],
    'carthage'        => [36.8531, 10.3242],
    'sidi bou said'   => [36.8701, 10.3417],
    'le bardo'        => [36.8094, 10.1406],
    'la goulette'     => [36.8183, 10.3053],
    'hammam lif'      => [36.7281, 10.3347],
    'ben khalled'     => [36.6894, 10.2536],
    'el aouina'       => [36.8431, 10.2269],
    'el mourouj'      => [36.6994, 10.2042],
    'megrine'         => [36.7628, 10.2356],
    'ezzouhour'       => [36.8333, 10.1833],
    'raoued'          => [36.9167, 10.1667],
    'mornag'          => [36.6831, 10.2858],
    'borj cedria'     => [36.7153, 10.3267],
    'soliman'         => [36.7000, 10.4833],
    'grombalia'       => [36.5833, 10.5000],
    'el haouaria'     => [37.0512, 11.0120],
    'korba'           => [36.5762, 10.8609],
    'korbous'         => [36.7794, 10.5672],
    'menzel temime'   => [36.7794, 10.9767],
    'sport'           => [36.8065, 10.1815],
    'terrain'         => [36.8065, 10.1815],
    'salle'           => [36.8065, 10.1815],
];

// ─────────────────────────────────────────────
// Fonction Nominatim
// ─────────────────────────────────────────────
function nominatim(string $q): ?array {
    $url = 'https://nominatim.openstreetmap.org/search?q='
        . urlencode($q)
        . '&format=json&limit=1&accept-language=fr&countrycodes=tn';
    $ctx = stream_context_create(['http' => [
        'header'  => "User-Agent: TinyTrack-App/1.0\r\n",
        'timeout' => 5,
    ]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!empty($data)) return ['lat' => (float)$data[0]['lat'], 'lng' => (float)$data[0]['lon'], 'display' => $data[0]['display_name']];
    return null;
}

// ─────────────────────────────────────────────
// Fonction Photon (Komoot) — alternative libre
// ─────────────────────────────────────────────
function photon(string $q): ?array {
    $url = 'https://photon.komoot.io/api/?q='
        . urlencode($q . ' Tunisie')
        . '&limit=1&lang=fr&bbox=7.5,30.2,11.6,37.5'; // bounding box Tunisie
    $ctx = stream_context_create(['http' => ['timeout' => 5]]);
    $raw = @file_get_contents($url, false, $ctx);
    if (!$raw) return null;
    $data = json_decode($raw, true);
    if (!empty($data['features'])) {
        $coords = $data['features'][0]['geometry']['coordinates'];
        return ['lat' => (float)$coords[1], 'lng' => (float)$coords[0], 'display' => $q];
    }
    return null;
}

// ─────────────────────────────────────────────
// Nettoyage et extraction de mots-clés
// ─────────────────────────────────────────────
function cleanAddress(string $addr): string {
    // Supprimer mots génériques qui gênent le geocoding
    $stopwords = ['maison des jeunes', 'salle des fêtes', 'salle fêtes', 'centre culturel',
        'stade', 'terrain de sport', 'terrain sport', 'jardin', 'complexe',
        'école', 'lycée', 'collège', 'faculté', 'université', 'hôpital',
        'clinique', 'pharmacie', 'mosquée', 'église', 'synagogue', 'place',
        'avenue', 'rue', 'boulevard', 'route', 'allée', 'impasse', 'cité',
        'résidence', 'immeuble', 'bloc', 'étage', 'appartement', 'villa',
        'tinytrack', 'tiny track', 'notre', 'notre établissement'];
    $addr = strtolower($addr);
    foreach ($stopwords as $sw) $addr = str_replace($sw, '', $addr);
    return trim(preg_replace('/\s+/', ' ', $addr));
}

function extractCity(string $addr): string {
    // Prendre le dernier mot significatif (souvent la ville après la virgule)
    $parts = array_map('trim', explode(',', $addr));
    // Renvoyer le dernier segment non vide
    foreach (array_reverse($parts) as $p) {
        $p = trim($p);
        if (strlen($p) > 2) return $p;
    }
    return $addr;
}

// ─────────────────────────────────────────────
// Recherche dans la table fallback manuelle
// ─────────────────────────────────────────────
function fallbackLookup(string $addr, array $table): ?array {
    $addr = strtolower(trim($addr));
    // Recherche exacte
    if (isset($table[$addr])) return ['lat' => $table[$addr][0], 'lng' => $table[$addr][1], 'display' => $addr . ' (fallback)'];
    // Recherche partielle : le nom de la table est contenu dans l'adresse
    foreach ($table as $key => $coords) {
        if (strlen($key) > 3 && strpos($addr, $key) !== false) {
            return ['lat' => $coords[0], 'lng' => $coords[1], 'display' => $key . ' (fallback)'];
        }
    }
    return null;
}

// ─────────────────────────────────────────────
// Cascade de tentatives
// ─────────────────────────────────────────────
$result   = null;
$strategy = '';

// 1. Adresse complète + Tunisie
$result = nominatim($raw . ', Tunisie');
if ($result) { $strategy = 'nominatim_full'; }

// 2. Adresse nettoyée + Tunisie
if (!$result) {
    $cleaned = cleanAddress($raw);
    if ($cleaned !== strtolower($raw) && strlen($cleaned) > 2) {
        $result = nominatim($cleaned . ', Tunisie');
        if ($result) $strategy = 'nominatim_cleaned';
    }
}

// 3. Extraction ville uniquement via Nominatim
if (!$result) {
    $city = extractCity($raw);
    if ($city !== $raw) {
        $result = nominatim($city . ', Tunisie');
        if ($result) $strategy = 'nominatim_city';
    }
}

// 4. Photon API (alternative à Nominatim)
if (!$result) {
    $result = photon($raw);
    if ($result) $strategy = 'photon_full';
}

// 5. Photon avec adresse nettoyée
if (!$result) {
    $cleaned = cleanAddress($raw);
    if (strlen($cleaned) > 2) {
        $result = photon($cleaned);
        if ($result) $strategy = 'photon_cleaned';
    }
}

// 6. Table de fallback manuelle
if (!$result) {
    $result = fallbackLookup($raw, $FALLBACK_COORDS);
    if ($result) $strategy = 'manual_fallback';
}

// 7. Fallback sur la ville extraite dans la table manuelle
if (!$result) {
    $city = extractCity($raw);
    $result = fallbackLookup($city, $FALLBACK_COORDS);
    if ($result) $strategy = 'manual_fallback_city';
}

// ─────────────────────────────────────────────
// Réponse
// ─────────────────────────────────────────────
if ($result) {
    echo json_encode([
        'found'    => true,
        'lat'      => $result['lat'],
        'lng'      => $result['lng'],
        'display'  => $result['display'],
        'strategy' => $strategy,
        'query'    => $raw,
    ]);
} else {
    echo json_encode([
        'found'    => false,
        'query'    => $raw,
        'strategy' => 'all_failed',
    ]);
}
?>
<?php
header('Content-Type: application/json');

$host     = "localhost";
$user     = "root";
$password = "";

try {
    // Connect without database first
    $pdo = new PDO("mysql:host=$host;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read setup.sql
    $sqlFile = '../setup.sql';
    if (!file_exists($sqlFile)) {
        echo json_encode(['succes' => false, 'message' => 'Fichier setup.sql introuvable']);
        exit;
    }
    $sql = file_get_contents($sqlFile);
    if ($sql === false) {
        echo json_encode(['succes' => false, 'message' => 'Impossible de lire setup.sql']);
        exit;
    }
    
    // Execute setup
    $pdo->exec($sql);
    
    echo json_encode(['succes' => true, 'message' => 'Base de données créée avec succès!']);
} catch (PDOException $e) {
    echo json_encode(['succes' => false, 'message' => 'Erreur: ' . $e->getMessage()]);
}
?>

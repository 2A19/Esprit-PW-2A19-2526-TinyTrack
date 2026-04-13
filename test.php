<?php
require_once 'config/config.php';

try {
    $db = Config::getConnexion();
    echo "Connexion réussie !";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage();
}
?>
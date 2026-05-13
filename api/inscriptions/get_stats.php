<?php
/**
 * Module : Inscription enfant
 * @author Ben Khalifa Youssef <youssef.benkhalifa@esprit.tn>
 */
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/_bootstrap.php';

// 1. Enfants par classe (taux de remplissage)
$stmt = $pdo->query("
    SELECT c.nom, c.couleur, c.capacite_max,
           COUNT(e.id) as nb_enfants,
           ROUND(COUNT(e.id) * 100.0 / c.capacite_max, 1) as taux_remplissage
    FROM classes c
    LEFT JOIN enfants e ON c.id = e.classe_id
    GROUP BY c.id
    ORDER BY nb_enfants DESC
");
$classes = $stmt->fetchAll();

// 2. Total enfants inscrits
$stmt = $pdo->query("SELECT COUNT(*) as total FROM enfants");
$totalEnfants = $stmt->fetch()['total'];

// 3. Total classes
$stmt = $pdo->query("SELECT COUNT(*) as total FROM classes");
$totalClasses = $stmt->fetch()['total'];

// 4. Classe la plus remplie
$classePleine = $classes[0] ?? null;

// 5. Répartition groupes sanguins
$stmt = $pdo->query("
    SELECT groupe_sanguin, COUNT(*) as nb
    FROM enfants
    WHERE groupe_sanguin IS NOT NULL AND groupe_sanguin != ''
    GROUP BY groupe_sanguin
    ORDER BY nb DESC
");
$groupesSanguins = $stmt->fetchAll();

// 6. Inscriptions par mois (6 derniers mois)
$stmt = $pdo->query("
    SELECT DATE_FORMAT(date_inscription, '%Y-%m') as mois,
           DATE_FORMAT(date_inscription, '%b %Y') as mois_label,
           COUNT(*) as nb
    FROM enfants
    WHERE date_inscription >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY mois
    ORDER BY mois ASC
");
$inscriptionsParMois = $stmt->fetchAll();

// 7. Taux d'autorisations
$stmt = $pdo->query("
    SELECT
        SUM(autorise_photos) as photos_oui,
        COUNT(*) - SUM(autorise_photos) as photos_non,
        SUM(autorise_sorties) as sorties_oui,
        COUNT(*) - SUM(autorise_sorties) as sorties_non
    FROM enfants
");
$autorisations = $stmt->fetch();

// 8. Âge moyen par classe
$stmt = $pdo->query("
    SELECT c.nom,
           ROUND(AVG(TIMESTAMPDIFF(YEAR, e.enfant_date_naissance, CURDATE())), 1) as age_moyen
    FROM classes c
    JOIN enfants e ON c.id = e.classe_id
    GROUP BY c.id
    ORDER BY age_moyen ASC
");
$ageMoyenParClasse = $stmt->fetchAll();

echo json_encode([
    'succes'               => true,
    'total_enfants'        => (int)$totalEnfants,
    'total_classes'        => (int)$totalClasses,
    'classes'              => $classes,
    'classe_pleine'        => $classePleine,
    'groupes_sanguins'     => $groupesSanguins,
    'inscriptions_mois'    => $inscriptionsParMois,
    'autorisations'        => $autorisations,
    'age_moyen_par_classe' => $ageMoyenParClasse
]);
?>

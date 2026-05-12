<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
// Endpoint AJAX — génère une réponse professionnelle à une réclamation par mots-clés
header('Content-Type: application/json; charset=utf-8');

// Vérifie que la requête est bien en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Méthode non autorisée.']);
    exit;
}

// Récupération et normalisation des paramètres reçus
$sujet       = mb_strtolower(trim($_POST['sujet']       ?? ''), 'UTF-8');
$description = mb_strtolower(trim($_POST['description'] ?? ''), 'UTF-8');

if (empty($sujet) && empty($description)) {
    http_response_code(400);
    echo json_encode(['error' => 'Données manquantes.']);
    exit;
}

// Texte combiné utilisé pour la détection de catégorie
$text = "$sujet $description";

// Vérifie si le texte contient au moins un mot de la liste
function contains(string $text, array $mots): bool {
    foreach ($mots as $mot) {
        if (mb_strpos($text, $mot) !== false) return true;
    }
    return false;
}

// Dictionnaire de catégories : mots-clés déclencheurs + 10 variantes de réponse par catégorie
$templates = [

    // Catégorie : problèmes de livraison
    'livraison' => [
        'mots' => ['livraison','livré','colis','délai','retard','expédition','commande','reçu','arrivée','transport','envoi'],
        'variantes' => [
            "Nous avons bien pris connaissance de votre réclamation concernant la livraison de votre commande et nous vous présentons nos sincères excuses pour ce désagrément. Notre équipe logistique a été immédiatement informée et procède à une vérification complète de l'état de votre envoi. Vous recevrez une mise à jour dans les 24 à 48 heures ouvrables. Nous mettons tout en œuvre pour résoudre cette situation rapidement et vous remercions de votre patience.",
            "Nous sommes désolés d'apprendre que votre commande n'a pas été livrée dans les délais prévus. Votre dossier a été transmis en priorité à notre service logistique afin de localiser votre colis et d'identifier la cause du retard. Nous vous informerons de l'évolution de la situation dans les meilleurs délais. Encore toutes nos excuses pour la gêne occasionnée.",
            "Nous comprenons l'importance que revêt la réception de votre commande dans les temps impartis et nous regrettons sincèrement ce contretemps. Une enquête a été ouverte auprès de notre transporteur pour retrouver votre colis et vous apporter une solution rapide : réexpédition ou remboursement selon votre souhait. Merci de votre confiance et de votre compréhension.",
            "Votre réclamation relative à la livraison a été enregistrée avec la priorité la plus haute. Nous avons contacté notre partenaire logistique pour obtenir des informations précises sur l'état de votre colis. Une réponse complète vous sera communiquée sous 24 heures. Nous vous remercions de votre patience et nous excusons pour ce retard inacceptable.",
            "Nous accusons bonne réception de votre signalement concernant votre livraison et nous en sommes sincèrement désolés. Votre dossier a été confié à notre responsable logistique qui supervisera personnellement le suivi de votre colis jusqu'à sa livraison. Nous vous tiendrons informé à chaque étape et nous engageons à trouver une solution satisfaisante dans les plus brefs délais.",
            "Nous regrettons profondément les difficultés que vous rencontrez avec la livraison de votre commande. Notre centre logistique a été alerté et procède en ce moment même à une recherche active de votre colis. Si celui-ci ne peut être retrouvé dans les 48 heures, nous procéderons à un réenvoi immédiat ou à un remboursement intégral à votre convenance.",
            "Votre satisfaction est notre priorité et nous sommes sincèrement navrés que la livraison de votre commande n'ait pas répondu à vos attentes. Nous avons ouvert un litige auprès du transporteur et suivons l'affaire de près. Vous serez tenu informé de l'avancement par email. Merci pour votre patience et votre compréhension.",
            "Nous prenons très au sérieux votre signalement concernant votre livraison et nous vous prions de nous excuser pour cette mauvaise expérience. Notre équipe est mobilisée pour résoudre ce problème dans les meilleurs délais. Nous vous contacterons directement avec une solution concrète dans les 24 heures suivant ce message.",
            "Suite à votre réclamation, nous avons immédiatement lancé une investigation auprès de notre service de transport pour identifier l'origine du problème lié à votre livraison. Nous mettons tout en œuvre pour régulariser la situation dans les plus brefs délais. Nous vous remercions d'avoir porté ce cas à notre attention et nous vous présentons toutes nos excuses.",
            "Nous sommes vraiment désolés pour le problème de livraison que vous avez subi. Une alerte a été envoyée à notre équipe de suivi des expéditions avec votre dossier marqué urgent. Nous vous promettons un retour complet d'ici 24 heures ouvrables avec les actions prises pour résoudre votre situation. Merci de votre patience et de votre fidélité.",
        ],
    ],

    // Catégorie : problèmes de facturation ou paiement
    'facturation' => [
        'mots' => ['remboursement','rembourser','facture','facturation','paiement','payé','prix','tarif','montant','double débit','prélèvement','trop-perçu','avoir'],
        'variantes' => [
            "Nous avons bien reçu votre réclamation relative à votre facturation et nous nous excusons pour la confusion. Notre service comptabilité a ouvert un dossier à votre nom et analyse votre situation en priorité. Si une erreur est confirmée, un remboursement ou un avoir sera traité sous 5 à 7 jours ouvrables. Nous vous tiendrons informé de l'issue très prochainement.",
            "Nous prenons très au sérieux votre signalement concernant un problème de facturation. Notre équipe financière procède à une vérification approfondie de votre compte afin d'identifier toute anomalie. En cas d'erreur avérée, un correctif sera appliqué immédiatement et vous en serez notifié par email. Merci de nous avoir alertés et toutes nos excuses pour ce désagrément.",
            "Votre réclamation concernant le montant facturé a bien été enregistrée et nous vous en remercions. Nous procédons actuellement à l'audit de votre dossier de paiement pour confirmer ou infirmer l'erreur signalée. Le cas échéant, un remboursement intégral sera initié dans les plus brefs délais. Nous restons à votre disposition pour tout complément d'information.",
            "Nous avons bien noté votre problème de facturation et nous en sommes sincèrement désolés. Notre équipe a immédiatement gelé tout prélèvement supplémentaire lié à votre dossier le temps de l'enquête. Vous recevrez une réponse officielle avec la décision prise sous 48 heures ouvrables. Nous vous remercions de votre compréhension.",
            "Merci de nous avoir signalé cette anomalie de facturation. Nous avons transmis votre dossier à notre responsable comptable qui procédera à une vérification complète de vos transactions. Si un trop-perçu est constaté, un remboursement sera effectué sous 5 jours ouvrables directement sur votre mode de paiement initial. Encore toutes nos excuses.",
            "Votre signalement relatif à la facturation a été pris en compte et nous vous remercions d'avoir pris le soin de nous contacter. Une analyse détaillée de votre historique de paiement est en cours. Nous nous engageons à vous communiquer les résultats de cette vérification ainsi que les mesures correctives dans un délai maximum de 48 heures.",
            "Nous accusons réception de votre réclamation de facturation et nous nous excusons vivement pour cette erreur. Notre département finance a été saisi en urgence pour traiter votre dossier. Un avoir ou un remboursement vous sera proposé dès que l'analyse sera finalisée. Merci de votre patience et de votre confiance en nos services.",
            "Nous sommes navrés d'apprendre que vous avez rencontré un problème lié à votre facturation. Notre équipe de gestion financière examine votre dossier avec la plus grande attention afin de corriger cette situation au plus vite. Nous vous présenterons une solution claire et un plan d'action d'ici la fin de la journée ouvrée. Encore toutes nos excuses.",
            "La question de facturation que vous soulevez mérite une attention immédiate, et nous vous remercions de nous en avoir informés. Votre compte a été mis sous surveillance prioritaire par notre service comptabilité. Nous vous garantissons une réponse complète avec les mesures correctives sous 24 heures. Merci pour votre vigilance.",
            "Nous avons bien reçu votre réclamation et nous sommes vraiment désolés pour cette erreur de facturation. Un audit complet de votre compte sera effectué dans les prochaines heures. Si une anomalie est confirmée, nous procéderons au remboursement intégral dans les 5 jours ouvrables et mettrons en place des mesures pour éviter que cela ne se reproduise.",
        ],
    ],

    // Catégorie : dysfonctionnements techniques
    'technique' => [
        'mots' => ['technique','bug','erreur','application','logiciel','plantage','connexion','accès','compte','mot de passe','login','panne','dysfonctionnement','ne fonctionne','ne marche','crash','lent','lenteur'],
        'variantes' => [
            "Nous avons pris note de votre signalement technique et nous nous excusons pour la gêne occasionnée. Notre équipe support a été alertée et travaille activement à la résolution du problème. Pourriez-vous nous préciser le message d'erreur exact et l'appareil utilisé afin d'accélérer le traitement ? Nous vous tiendrons informé de l'avancement dans les meilleurs délais.",
            "Votre incident technique a été enregistré et transmis à notre équipe d'ingénieurs avec la priorité maximale. Nous mettons tout en œuvre pour identifier la cause racine et déployer un correctif dans les plus brefs délais. En attendant, n'hésitez pas à réessayer ou à vider le cache de votre navigateur. Nous nous excusons pour cette interruption de service.",
            "Nous sommes navrés d'apprendre que vous rencontrez des difficultés techniques avec notre solution. Un ticket d'assistance a été ouvert à votre nom et notre équipe technique l'examine en ce moment même. Nous vous enverrons une notification dès que la correction sera déployée. Merci de votre patience et de votre fidélité.",
            "Votre signalement technique a été reçu et classé en priorité haute dans notre système de gestion des incidents. Notre équipe d'experts analyse la situation afin de vous proposer une solution rapide et durable. Nous vous recommandons de noter le numéro de votre dossier pour tout suivi ultérieur. Nous vous tenons informé dans les 24 heures.",
            "Nous nous excusons sincèrement pour les difficultés techniques que vous rencontrez. Notre pôle technique a été mobilisé pour diagnostiquer le problème et le corriger dans les meilleurs délais. Si le problème persiste, nous vous invitons à redémarrer l'application ou à nous transmettre une capture d'écran pour faciliter le diagnostic. Merci de votre compréhension.",
            "Nous prenons votre signalement très au sérieux et nous excusons pour la gêne causée par ce dysfonctionnement. Notre équipe informatique a reproduit le scénario et travaille activement sur un correctif. Vous serez averti par email dès que la solution sera déployée. Merci pour votre patience et votre collaboration.",
            "Suite à votre signalement, un incident technique a été ouvert avec une priorité critique. Nos ingénieurs ont commencé l'analyse du problème et espèrent avoir une solution déployée d'ici les prochaines heures. Nous vous enverrons un récapitulatif des actions effectuées dès la résolution. Encore toutes nos excuses pour ce désagrément.",
            "Nous comprenons votre frustration face à ce problème technique et nous vous assurons que tout est mis en œuvre pour y remédier rapidement. Notre équipe support a escaladé votre dossier au niveau technique supérieur pour une résolution accélérée. Nous vous contacterons personnellement pour confirmer que le problème est résolu à votre satisfaction.",
            "Votre rapport de bug a bien été reçu et intégré dans notre système de suivi des incidents. Nos développeurs ont identifié la zone concernée et travaillent sur un correctif qui sera déployé lors de la prochaine mise à jour. En attendant, nous pouvons vous proposer une solution de contournement si nécessaire. N'hésitez pas à nous le faire savoir.",
            "Nous sommes sincèrement désolés pour ce problème technique qui perturbe votre utilisation de notre service. Un ticket urgent a été créé et confié à notre équipe senior. Nous effectuons des tests en ce moment et espérons vous fournir une solution définitive dans les 24 heures. Merci de nous avoir signalé ce problème, cela nous aide à améliorer notre service.",
        ],
    ],

    // Catégorie : défauts ou non-conformité produit
    'qualite' => [
        'mots' => ['qualité','produit','défectueux','défaut','cassé','abîmé','endommagé','conforme','non conforme','détérioré','rouillé','incomplet','manquant'],
        'variantes' => [
            "Nous vous remercions d'avoir signalé ce problème de qualité et nous vous présentons nos excuses pour cette expérience décevante. Un contrôle qualité sera effectué sur ce produit afin d'éviter que d'autres clients ne soient concernés. Nous allons procéder au remplacement ou au remboursement selon votre préférence. Merci de nous indiquer la solution qui vous convient.",
            "Votre retour concernant la qualité de notre produit est précieux pour nous et nous sommes sincèrement désolés pour cette déconvenue. Votre réclamation a été transmise à notre responsable qualité qui ouvrira une enquête interne. Entre-temps, nous vous proposons un échange immédiat ou un remboursement total selon votre choix. Merci de votre confiance.",
            "Nous accusons réception de votre signalement relatif à un défaut de qualité et nous vous présentons toutes nos excuses. Ce type de situation est inacceptable et ne reflète pas nos standards habituels. Notre service qualité traitera votre dossier en priorité et vous contactera sous 48 heures pour convenir d'une solution satisfaisante.",
            "Nous sommes vraiment désolés d'apprendre que le produit reçu ne correspond pas à vos attentes. Notre équipe qualité a été informée et procède à une inspection de notre stock pour identifier l'origine du problème. Nous vous proposerons un remplacement immédiat dès confirmation du défaut. Votre satisfaction est notre priorité absolue.",
            "Merci de nous avoir signalé ce problème de conformité produit. Nous l'avons immédiatement remonté à notre département qualité pour investigation. En attendant la résolution, nous vous offrons soit un échange express, soit un avoir sur votre prochain achat. Votre retour nous aide à améliorer continuellement nos standards.",
            "Votre réclamation concernant la qualité du produit a bien été enregistrée et nous en sommes sincèrement navrés. Un responsable qualité prendra personnellement en charge votre dossier et vous contactera sous 24 heures pour décider ensemble de la meilleure solution. Nous vous garantissons un traitement rapide et équitable.",
            "Nous comprenons votre déception face à un produit ne répondant pas aux standards attendus et nous vous en présentons toutes nos excuses. Notre service qualité a ouvert une enquête et procèdera à une vérification de l'ensemble du lot concerné. Une solution de remplacement ou de remboursement vous sera proposée dans les plus brefs délais.",
            "Nous sommes profondément désolés pour la mauvaise expérience que vous avez vécue avec notre produit. Votre retour a été transmis en urgence à notre cellule qualité. Nous vous enverrons une étiquette de retour prépayée pour récupérer le produit défectueux et procéderons à un remplacement immédiat dès réception.",
            "La qualité de nos produits est un engagement fondamental pour nous, et nous sommes sincèrement désolés que cet engagement n'ait pas été respecté dans votre cas. Votre signalement a été transmis à notre directeur qualité. Nous vous contacterons dans les 24 heures avec une proposition concrète : remplacement, remboursement ou avoir.",
            "Nous prenons très au sérieux votre signalement relatif à la qualité du produit reçu. Une procédure de rappel qualité a été initiée sur ce référence. Nous allons procéder au remplacement de votre produit en express et vous enverrons un bon de transport pour le retour sans frais. Encore toutes nos excuses pour cette situation.",
        ],
    ],

    // Catégorie : insatisfaction liée au service ou comportement du personnel
    'service' => [
        'mots' => ['service','accueil','personnel','employé','agent','réponse','injoignable','attente','rappel','impoli','manque','comportement','traitement'],
        'variantes' => [
            "Nous avons pris bonne note de votre retour concernant la qualité de notre service et nous vous présentons nos sincères excuses. Ce type de situation ne reflète pas les standards que nous nous imposons et votre remarque est prise très au sérieux. Une action corrective a été engagée auprès de l'équipe concernée. Nous nous engageons à vous offrir un service à la hauteur de vos attentes.",
            "Nous sommes profondément désolés pour la qualité de prise en charge que vous avez vécue. Votre expérience a été remontée à notre responsable de service qui fera le nécessaire pour que cela ne se reproduise pas. Nous aimerions vous offrir une expérience améliorée lors de votre prochain contact. Merci de nous donner l'occasion de nous rattraper.",
            "Votre satisfaction est notre priorité absolue, et nous regrettons vivement que vous n'ayez pas reçu le niveau de service attendu. Ce retour a été transmis au manager de l'équipe concernée afin de mettre en place les mesures correctives appropriées. Nous vous présentons nos excuses les plus sincères et restons à votre entière disposition.",
            "Nous sommes sincèrement navrés par votre expérience avec notre service client. Ce type de comportement est contraire à nos valeurs et à notre charte de service. Votre dossier a été escaladé à la direction qui s'assurera personnellement qu'une réponse adaptée vous soit apportée dans les plus brefs délais.",
            "Merci de nous avoir fait part de votre mécontentement concernant la qualité de notre service. Nous prenons ce retour avec le plus grand sérieux car nous visons l'excellence dans chacune de nos interactions. Une session de formation a été planifiée pour l'équipe concernée. Nous vous présentons nos excuses et espérons avoir l'occasion de vous offrir une meilleure expérience.",
            "Nous accusons réception de votre retour concernant notre service et nous vous remercions de votre franchise. La situation que vous décrivez est inacceptable et nous en sommes profondément désolés. Un responsable prendra contact avec vous dans les 24 heures pour s'assurer que votre problème est résolu à votre entière satisfaction.",
            "Votre témoignage concernant la qualité de notre service nous touche et nous engage à agir. Notre directeur de la relation client a été informé de votre situation. Nous vous garantissons un suivi personnalisé de votre dossier et mettrons tout en œuvre pour regagner votre confiance. Encore toutes nos excuses pour cette expérience décevante.",
            "Nous regrettons sincèrement que votre expérience avec notre équipe n'ait pas été à la hauteur de vos attentes. Un bilan interne a été déclenché suite à votre réclamation. Nous vous assurons que des mesures correctives seront mises en place immédiatement pour éviter que cela ne se reproduise. Merci de nous permettre de nous améliorer.",
            "Nous sommes vraiment désolés pour le traitement que vous avez reçu. Ce n'est absolument pas le standard de service que nous souhaitons offrir à nos clients. Une formation de rappel a été organisée pour l'équipe et un suivi individuel sera effectué. Nous espérons sincèrement vous offrir une bien meilleure expérience lors de votre prochain contact.",
            "Votre insatisfaction concernant notre service est pleinement justifiée et nous vous en présentons nos plus sincères excuses. Votre réclamation a déclenché une révision immédiate de nos procédures de service client. Un responsable senior sera dédié à votre dossier pour s'assurer que vous obtenez la réponse et le traitement que vous méritez.",
        ],
    ],

    // Catégorie par défaut — réclamation sans contexte identifiable
    'generique' => [
        'mots' => [],
        'variantes' => [
            "Nous avons bien reçu votre réclamation et nous vous remercions de nous en avoir informés. Votre dossier a été enregistré et transmis au service compétent qui prendra en charge votre demande dans les plus brefs délais. Nous mettons tout en œuvre pour vous apporter une réponse satisfaisante et nous nous excusons pour tout inconvénient causé.",
            "Nous accusons réception de votre réclamation et vous remercions de l'attention que vous portez à la qualité de nos services. Votre demande est en cours de traitement et un membre de notre équipe vous contactera prochainement avec une réponse détaillée. Nous vous prions d'accepter nos excuses pour la gêne occasionnée.",
            "Merci d'avoir pris le temps de nous soumettre votre réclamation. Nous prenons votre retour très au sérieux et avons immédiatement ouvert un dossier à votre nom. Notre équipe analyse votre situation et reviendra vers vous avec une solution adaptée dans les 48 heures. Nous vous remercions de votre patience et de votre confiance.",
            "Votre réclamation a bien été enregistrée dans notre système et nous vous en remercions sincèrement. Notre équipe dédiée prend en charge votre dossier avec la plus grande attention. Nous nous engageons à vous fournir une réponse claire et des actions concrètes dans les meilleurs délais. Encore toutes nos excuses pour ce désagrément.",
            "Nous avons bien pris note de votre réclamation et nous sommes sincèrement désolés pour les inconvénients rencontrés. Un conseiller spécialisé a été désigné pour traiter votre dossier en priorité. Vous recevrez une réponse complète et personnalisée dans un délai maximum de 24 heures ouvrables.",
            "Votre message a retenu toute notre attention et nous vous remercions de nous avoir contactés. La situation que vous décrivez nécessite une investigation de notre part, et nous y procédons immédiatement. Nous vous tiendrons informé de l'avancement de votre dossier et vous proposerons une solution adaptée dans les plus brefs délais.",
            "Nous prenons acte de votre réclamation et vous assurons de notre entière mobilisation pour y apporter une réponse satisfaisante. Votre dossier a été marqué prioritaire et confié à un expert de notre équipe. Un retour complet vous sera communiqué sous 48 heures ouvrables. Merci de votre patience.",
            "Suite à votre réclamation, nous avons immédiatement engagé les vérifications nécessaires auprès des services concernés. Nous comprenons votre frustration et y sommes pleinement attentifs. Une solution vous sera proposée dès que l'enquête interne sera finalisée, soit dans les 24 à 48 heures. Merci de nous faire confiance.",
            "Nous avons bien reçu votre demande et souhaitons vous assurer que votre satisfaction reste notre priorité numéro un. Notre équipe d'assistance a pris en charge votre dossier et travaille activement à l'identification d'une solution adaptée. Nous vous tiendrons informé à chaque étape du traitement de votre réclamation.",
            "Votre réclamation nous a été transmise et nous vous remercions de la confiance que vous nous accordez en nous faisant part de votre situation. Une analyse approfondie de votre dossier est en cours. Notre objectif est de vous apporter une réponse précise, juste et rapide dans un délai ne dépassant pas 48 heures ouvrables.",
        ],
    ],
];

// Parcourt les catégories et retient la première dont un mot-clé correspond au texte
$categorie = 'generique';
foreach ($templates as $cat => $data) {
    if (!empty($data['mots']) && contains($text, $data['mots'])) {
        $categorie = $cat;
        break;
    }
}

// Sélectionne une variante aléatoire parmi les 10 disponibles
$variantes  = $templates[$categorie]['variantes'];
$suggestion = $variantes[array_rand($variantes)];

echo json_encode(['suggestion' => $suggestion]);

<?php
/**
 * Module : Gestion Reclamation
 * @author Mahdi Ben Slimene <mahdibenslimene2005@gmail.com>
 */
// Analyse de sentiment basée sur des mots-clés français — sans API externe
class SentimentService {

    private static array $negatif = [
        'problème','probleme','problèmes','problemes','mauvais','mauvaise','défaut','defaut',
        'cassé','casse','cassée','cassee','nul','nulle','terrible','horrible','horrible',
        'déçu','decu','déçue','decue','déception','deception','insatisfait','insatisfaite',
        'urgent','urgence','grave','graves','erreur','erreurs','impossible','refus','refusé',
        'refusee','dysfonctionnement','panne','pannes','vol','arnaquer','arnaque','scandale',
        'inacceptable','honte','colère','colere','énervé','enerve','furieux','furieuse',
        'mécontent','mecontent','mécontente','mecontente','plainte','plaintes','jamais',
        'aucune réponse','aucune reponse','pas de réponse','pas de reponse','ne fonctionne pas',
        'ne marche pas','ne répond pas','ne repond pas','ne livré','non livré','non livre',
        'retard','retards','délai dépassé','delai depasse','facturation incorrecte',
        'incompétent','incompetent','lamentable','désastreux','desastreux','catastrophique',
        'pire','abusif','abusive','frauduleux','frauduleuse','trompeur','trompeuse',
        'rupture','manque','absent','absente','indisponible','introuvable',
    ];

    private static array $positif = [
        'satisfait','satisfaite','bien','merci','excellent','excellente','parfait','parfaite',
        'super','content','contente','bon','bonne','apprécier','apprecier','féliciter',
        'feliciter','bravo','génial','genial','top','rapide','efficace','professionnel',
        'professionnelle','agréable','agreable','recommande','recommander','impressionné',
        'impressionne','qualité','qualite','service impeccable','très bien','tres bien',
    ];

    public static function analyze(string $text): string {
        $text  = mb_strtolower($text, 'UTF-8');
        $score = 0;

        foreach (self::$negatif as $mot) {
            if (mb_strpos($text, $mot) !== false) $score--;
        }
        foreach (self::$positif as $mot) {
            if (mb_strpos($text, $mot) !== false) $score++;
        }

        if ($score > 0)  return 'positif';
        if ($score < 0)  return 'negatif';
        return 'neutre';
    }
}

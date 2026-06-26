<?php

/**
 * Compétences officielles par matière (cahier des charges §31).
 * La somme des poids par matière doit être exactement 100 %.
 */
return [
    'francais' => [
        ['name' => 'Lecture et compréhension', 'weight' => 30],
        ['name' => 'Écriture', 'weight' => 25],
        ['name' => 'Grammaire', 'weight' => 15],
        ['name' => 'Orthographe', 'weight' => 10],
        ['name' => 'Vocabulaire', 'weight' => 10],
        ['name' => 'Communication orale', 'weight' => 10],
    ],
    'mathematiques' => [
        ['name' => 'Arithmétique', 'weight' => 25],
        ['name' => 'Résolution de problèmes', 'weight' => 25],
        ['name' => 'Géométrie', 'weight' => 20],
        ['name' => 'Mesures', 'weight' => 10],
        ['name' => 'Fractions', 'weight' => 10],
        ['name' => 'Logique', 'weight' => 10],
    ],
    'sciences' => [
        ['name' => 'Observation', 'weight' => 20],
        ['name' => 'Expérimentation', 'weight' => 25],
        ['name' => 'Univers vivant', 'weight' => 20],
        ['name' => 'Univers matériel', 'weight' => 20],
        ['name' => 'Terre et espace', 'weight' => 15],
    ],
    'histoire' => [
        ['name' => 'Temps historique', 'weight' => 30],
        ['name' => 'Civilisations', 'weight' => 30],
        ['name' => 'Sociétés', 'weight' => 20],
        ['name' => 'Analyse historique', 'weight' => 20],
    ],
    'geographie' => [
        ['name' => 'Territoires', 'weight' => 35],
        ['name' => 'Cartes', 'weight' => 35],
        ['name' => 'Environnement', 'weight' => 15],
        ['name' => 'Population', 'weight' => 15],
    ],
    'islam' => [
        ['name' => 'Lecture', 'weight' => 20],
        ['name' => 'Compréhension', 'weight' => 25],
        ['name' => 'Histoire islamique', 'weight' => 25],
        ['name' => 'Mémorisation', 'weight' => 15],
        ['name' => 'Valeurs', 'weight' => 15],
    ],
    'natation' => [
        ['name' => 'Flottaison', 'weight' => 20],
        ['name' => 'Respiration', 'weight' => 20],
        ['name' => 'Déplacements', 'weight' => 20],
        ['name' => 'Techniques de nage', 'weight' => 25],
        ['name' => 'Sécurité aquatique', 'weight' => 15],
    ],
    'education-physique' => [
        ['name' => 'Mouvement', 'weight' => 25],
        ['name' => 'Coordination', 'weight' => 25],
        ['name' => 'Santé', 'weight' => 25],
        ['name' => 'Activité physique', 'weight' => 25],
    ],
];

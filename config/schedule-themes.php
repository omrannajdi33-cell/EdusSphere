<?php

/**
 * Thèmes visuels de l'espace élève selon l'horaire.
 * Les couleurs/icônes viennent des matières ; ce fichier ajoute déco et messages.
 */
return [
    'default' => [
        'slug' => 'default',
        'greeting' => null,
        'tagline' => null,
        'emoji' => null,
        'deco' => [],
        'gradient' => ['#eef2ff', '#e0f2fe', '#f8fafc'],
        'color' => '#4f46e5',
    ],

    'weekend' => [
        'slug' => 'weekend',
        'name' => 'Week-end',
        'greeting' => 'Va te reposer !',
        'tagline' => 'Profite bien de ton week-end — on se retrouve lundi.',
        'emoji' => '🛏️',
        'deco' => ['☁️', '🌙', '✨', '🧸'],
        'gradient' => ['#faf5ff', '#fce7f3', '#f0f9ff'],
        'color' => '#a855f7',
    ],

    'subjects' => [
        'francais' => [
            'greeting' => 'Mode Français',
            'tagline' => 'Lecture, écriture et belles histoires.',
            'emoji' => '📚',
            'deco' => ['✏️', '📖', '💬'],
        ],
        'mathematiques' => [
            'greeting' => 'Mode Mathématiques',
            'tagline' => 'Calcule, raisonne, résous !',
            'emoji' => '🔢',
            'deco' => ['➕', '📐', '🧮'],
        ],
        'sciences' => [
            'greeting' => 'Mode Sciences',
            'tagline' => 'Observe, expérimente, découvre.',
            'emoji' => '🔬',
            'deco' => ['⚗️', '🧪', '🌱'],
        ],
        'histoire' => [
            'greeting' => 'Mode Histoire',
            'tagline' => 'Voyage dans le temps.',
            'emoji' => '🏛️',
            'deco' => ['⏳', '📜', '👑'],
        ],
        'geographie' => [
            'greeting' => 'Mode Géographie',
            'tagline' => 'Explore le monde autour de toi.',
            'emoji' => '🌍',
            'deco' => ['🗺️', '🧭', '🏔️'],
        ],
        'islam' => [
            'greeting' => 'Mode Islam',
            'tagline' => 'Apprends avec sérénité.',
            'emoji' => '🌙',
            'deco' => ['✨', '📿', '🕌'],
        ],
        'natation' => [
            'greeting' => 'Mode Natation',
            'tagline' => 'Glisse dans l\'eau !',
            'emoji' => '🏊',
            'deco' => ['💧', '🌊', '🏅'],
        ],
        'education-physique' => [
            'greeting' => 'Mode Éducation physique',
            'tagline' => 'Bouge, joue, donne le meilleur de toi !',
            'emoji' => '⚽',
            'deco' => ['🏃', '🤸', '🎾'],
        ],
    ],
];

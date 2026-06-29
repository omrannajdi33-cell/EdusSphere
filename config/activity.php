<?php

return [
    'statuses' => [
        'draft' => 'Brouillon',
        'published' => 'Publié',
        'archived' => 'Archivé',
    ],

    'homework_slots' => [
        'during_school' => 'Pendant l\'école',
        'after_school' => 'Après l\'école',
    ],

    'workflow_statuses' => [
        'in_progress' => 'En cours',
        'submitted' => 'Soumise',
        'returned' => 'Renvoyée',
        'corrected' => 'Corrigée',
    ],

    'correction_statuses' => [
        'to_correct' => 'À corriger',
        'submitted' => 'Soumise',
        'corrected' => 'Corrigée',
        'returned' => 'Renvoyée',
        'validated' => 'Validée',
    ],

    'correction_actions' => [
        'submitted' => 'Soumission élève',
        'validated' => 'Validation professeur',
        'returned' => 'Renvoyée à l\'élève',
    ],

    /*
    | Types d'étape dans une activité (choix du professeur, étape par étape)
    */
    'page_types' => [
        'pdf_worksheet' => [
            'label' => 'Feuille PDF',
            'description' => 'L\'élève écrit et dessine sur ton PDF. Tu corriges à l\'encre rouge après soumission.',
            'icon' => '📄',
            'color' => '#0ea5e9',
        ],
        'free_write' => [
            'label' => 'Zone d\'écriture',
            'description' => 'Consignes sur le site. L\'élève rédige et dessine librement, sans fichier.',
            'icon' => '✍️',
            'color' => '#10b981',
        ],
        'interactive' => [
            'label' => 'Questions interactives',
            'description' => '10 formats de questions créées directement sur le site.',
            'icon' => '💬',
            'color' => '#4f46e5',
        ],
        'reading_comprehension' => [
            'label' => 'Compréhension de lecture',
            'description' => 'Texte à lire, écoute audio, masquer/réafficher le texte.',
            'icon' => '📖',
            'color' => '#2563eb',
            'subject' => 'francais',
        ],
        'recitation' => [
            'label' => 'Récitation / Coran',
            'description' => 'Texte sacré, écoute, masquer pour mémoriser.',
            'icon' => '🕌',
            'color' => '#059669',
            'subject' => 'islam',
        ],
        'oral_recording' => [
            'label' => 'Oral (audio / vidéo)',
            'description' => 'L\'élève enregistre sa voix ou une courte vidéo.',
            'icon' => '🎤',
            'color' => '#db2777',
        ],
        'rich_document' => [
            'label' => 'Document d\'écriture',
            'description' => 'Texte riche (comme un doc) ou dessin — l\'élève choisit.',
            'icon' => '📝',
            'color' => '#d97706',
        ],
        'math_scroll' => [
            'label' => 'Zone maths infinie',
            'description' => 'Grande feuille blanche scrollable — écrire et dessiner librement.',
            'icon' => '∞',
            'color' => '#7c3aed',
            'subject' => 'mathematiques',
        ],
    ],

    /*
    | 10 formats de questions interactives (créées sur le site, sans import)
    */
    'question_types' => [
        'mcq' => [
            'label' => 'QCM',
            'description' => 'Une bonne réponse parmi plusieurs choix.',
            'icon' => '🔘',
        ],
        'true_false' => [
            'label' => 'Vrai / Faux',
            'description' => 'Affirmation vraie ou fausse.',
            'icon' => '✓✗',
        ],
        'multi_select' => [
            'label' => 'Cases à cocher',
            'description' => 'Plusieurs bonnes réponses.',
            'icon' => '☑️',
        ],
        'short_text' => [
            'label' => 'Réponse courte',
            'description' => 'Un mot ou une phrase.',
            'icon' => '📝',
        ],
        'long_text' => [
            'label' => 'Réponse longue',
            'description' => 'Paragraphe, rédaction.',
            'icon' => '📄',
        ],
        'numeric' => [
            'label' => 'Numérique',
            'description' => 'Un nombre avec tolérance.',
            'icon' => '🔢',
        ],
        'fill_blank' => [
            'label' => 'Texte à trous',
            'description' => 'Phrase avec ___ à compléter.',
            'icon' => '___',
        ],
        'ordering' => [
            'label' => 'Ordre',
            'description' => 'Classer des éléments.',
            'icon' => '↕️',
        ],
        'matching' => [
            'label' => 'Associer',
            'description' => 'Relier deux colonnes.',
            'icon' => '🔗',
        ],
        'choice_cards' => [
            'label' => 'Cartes',
            'description' => 'Cartes colorées à choisir.',
            'icon' => '🃏',
        ],
    ],
];

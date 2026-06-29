<?php

return [
    'statuses' => [
        'draft' => 'Brouillon',
        'published' => 'Publié',
        'archived' => 'Archivé',
    ],

    'project_types' => [
        'research' => [
            'label' => 'Recherche documentaire',
            'icon' => '🔍',
            'description' => 'Enquête, analyse de documents et synthèse.',
        ],
        'report' => [
            'label' => 'Compte rendu',
            'icon' => '📝',
            'description' => 'Rédaction structurée d\'un compte rendu ou d\'un travail écrit.',
        ],
        'creative' => [
            'label' => 'Projet créatif',
            'icon' => '🎨',
            'description' => 'Création, dossier visuel ou projet artistique.',
        ],
        'presentation' => [
            'label' => 'Exposé / présentation',
            'icon' => '🎤',
            'description' => 'Préparation d\'un exposé avec support écrit.',
        ],
    ],

    'submission_formats' => [
        'online' => [
            'label' => 'Rédiger sur le site',
            'icon' => '✍️',
            'description' => 'L\'élève écrit directement dans EduSphere.',
        ],
        'upload' => [
            'label' => 'Téléverser un fichier',
            'icon' => '📎',
            'description' => 'L\'élève dépose un PDF ou un document.',
        ],
        'both' => [
            'label' => 'Rédiger ou téléverser',
            'icon' => '📄',
            'description' => 'L\'élève choisit : écrire en ligne ou déposer un fichier.',
        ],
    ],

    'workflow_statuses' => [
        'in_progress' => 'En cours',
        'submitted' => 'Soumis',
        'returned' => 'Renvoyé',
        'corrected' => 'Corrigé',
    ],

    'source_types' => [
        'book' => 'Livre',
        'article' => 'Article',
        'website' => 'Site web',
        'video' => 'Vidéo',
        'interview' => 'Interview',
        'document' => 'Document',
        'other' => 'Autre',
    ],

    'bibliography_types' => [
        'book' => 'Livre',
        'article' => 'Article de revue',
        'website' => 'Site web',
        'video' => 'Vidéo',
        'thesis' => 'Mémoire / thèse',
        'other' => 'Autre',
    ],

    'correction_actions' => [
        'submitted' => 'Soumission élève',
        'validated' => 'Validation professeur',
        'returned' => 'Renvoi à l\'élève',
    ],
];

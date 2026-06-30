<?php

return [
    /*
    | Matériel requis pour une activité ou un examen (répartition tablettes / ordinateurs)
    */
    'device_types' => [
        'tablet' => [
            'label' => 'Tablette',
            'icon' => '📱',
            'hint' => 'Écriture ou dessin au stylet, enregistrement audio/vidéo.',
        ],
        'computer' => [
            'label' => 'Ordinateur',
            'icon' => '💻',
            'hint' => 'Questions interactives, lecture, rédaction clavier.',
        ],
    ],

    /** Types d'étape activité/examen nécessitant une tablette (stylet, vidéo, canvas). */
    'tablet_page_types' => [
        'pdf_worksheet',
        'free_write',
        'oral_recording',
        'recitation',
        'rich_document',
        'math_scroll',
    ],
];

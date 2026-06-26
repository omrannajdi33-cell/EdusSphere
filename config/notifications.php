<?php

return [
    'labels' => [
        'activity_submitted' => 'Nouvelle soumission d\'activité',
        'activity_corrected' => 'Activité corrigée',
        'activity_returned' => 'Travail renvoyé',
        'exam_submitted' => 'Examen soumis',
        'exam_corrected' => 'Examen corrigé',
        'exam_hand_raise' => 'Main levée en examen',
    ],

    'messages' => [
        'activity_submitted' => fn (array $d) => ($d['student_name'] ?? 'Un élève').' a soumis « '.($d['activity_title'] ?? '').' »',
        'activity_corrected' => fn (array $d) => '« '.($d['activity_title'] ?? 'Ton activité').' » a été corrigée'.(isset($d['score']) ? ' · '.$d['score'].' %' : ''),
        'activity_returned' => fn (array $d) => '« '.($d['activity_title'] ?? 'Ton activité').' » a été renvoyée — tu peux la modifier',
        'exam_submitted' => fn (array $d) => ($d['student_name'] ?? 'Un élève').' a soumis « '.($d['exam_title'] ?? '').' »',
        'exam_corrected' => fn (array $d) => '« '.($d['exam_title'] ?? 'Ton examen').' » a été corrigé'.(isset($d['score']) ? ' · '.$d['score'].' %' : ''),
        'exam_hand_raise' => fn (array $d) => ($d['student_name'] ?? 'Un élève').' lève la main pendant « '.($d['exam_title'] ?? 'examen').' »',
    ],
];

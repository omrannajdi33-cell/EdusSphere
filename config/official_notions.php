<?php

/**
 * Référentiel de notions officielles par niveau scolaire (PFEQ, primaire).
 *
 * Production : php artisan db:seed --class=OfficialNotionsSeeder
 */
return [
    'Primaire 2' => require __DIR__.'/catalog_primaire_2_notions.php',
    'Primaire 5' => require __DIR__.'/catalog_primaire_5_notions.php',
];

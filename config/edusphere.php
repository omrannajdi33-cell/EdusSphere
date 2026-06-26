<?php

return [
    'session_idle_minutes' => (int) env('SESSION_IDLE_MINUTES', 30),

    'avatar' => [
        'max_kb' => 5120,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
];

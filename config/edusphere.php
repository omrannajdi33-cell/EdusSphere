<?php

return [
    'session_idle_minutes' => (int) env('SESSION_IDLE_MINUTES', 30),

    'login_email_placeholder' => env('ADMIN_EMAIL', 'admin@ontech.com'),

    'avatar' => [
        'max_kb' => 5120,
        'mimes' => ['jpg', 'jpeg', 'png', 'webp'],
    ],
];

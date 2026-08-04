<?php

$frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');

$origins = array_values(array_filter(array_unique([
    $frontendUrl,
    'http://localhost:5173',
    'http://127.0.0.1:5173',
])));

// Comma-separated extra origins, e.g. https://movies-flix-kh.vercel.app,https://www.example.com
$extra = env('CORS_ALLOWED_ORIGINS');
if ($extra) {
    foreach (explode(',', $extra) as $origin) {
        $origin = trim($origin);
        if ($origin !== '') {
            $origins[] = $origin;
        }
    }
    $origins = array_values(array_unique($origins));
}

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'],

    'allowed_origins' => $origins,

    'allowed_origins_patterns' => [
        '#^https://.*\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];

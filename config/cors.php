<?php

/*
|--------------------------------------------------------------------------
| Cross-Origin Resource Sharing (CORS)
|--------------------------------------------------------------------------
|
| The React app (http://localhost:5173) and this API (http://localhost:8000) are different
| "origins", so the browser asks the API whether the app may call it. These settings say yes.
|
*/

return [

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    // Must be the exact frontend address (not '*') because the sign-in cookie is sent along.
    // Several frontends? Put them in CORS_ALLOWED_ORIGINS, comma separated.
    'allowed_origins' => array_map('trim', explode(',', env('CORS_ALLOWED_ORIGINS', env('BASE_URL', 'http://localhost:5173')))),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Lets the browser send and receive the auth_token cookie.
    'supports_credentials' => true,

];

<?php

return [
    'secret' => env('JWT_SECRET') ?: env('APP_KEY'),
    'algorithm' => env('JWT_ALGORITHM', 'HS256'),
    'issuer' => env('JWT_ISSUER', env('APP_URL', 'http://localhost')),
    'audience' => env('JWT_AUDIENCE', 'heymo-campaign-web'),
    'ttl_minutes' => (int) env('JWT_TTL_MINUTES', 120),
];

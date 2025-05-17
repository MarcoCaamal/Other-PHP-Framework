<?php

return [
    'method' => 'session',
    'jwt_options' => [
        'digest_alg' => 'HS256',
        'max_age' => 3600,
        'leeway' => 60,
    ],
];

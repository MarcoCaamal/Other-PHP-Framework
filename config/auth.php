<?php

return [
    'method' => 'session',
    'jwt_options' => [
        'digest_alg' => 'HS256',
        'private_key_bits' => 1024,
        'max_age' => 3600,
        'leeway' => 60,
        'path_to_private_key' => '', // Only needed if algorithm is RS*
    ],
];

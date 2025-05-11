<?php

return [
    'allowed_origins' => ['https://allowed-domain.com'],
    'allowed_methods' => ['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
    'exposed_headers' => ['X-Custom-Header'],
    'allow_credentials' => 'true',
    'max_age' => 3600
];
<?php

return [
    // Método de autenticación por defecto
    'method' => 'session',

    // Modelo y campos de usuario
    'user_model' => '\App\Models\User',
    'identifier_field' => 'email',     // Campo usado para identificar al usuario
    'password_field' => 'password',    // Campo que almacena la contraseña

    // Configuración de JWT
    'jwt_options' => [
        'digest_alg' => 'HS256',
        'max_age' => 3600,
        'leeway' => 60,
    ],

    // Configuración de sesión
    'session' => [
        'remember_ttl' => 5184000,     // 60 días para sesiones "recordadas"
        'refresh_ttl' => 120,          // 2 minutos para refrescar la sesión
    ],
];

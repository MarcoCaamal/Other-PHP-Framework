<?php

return [
    // El motor de vista a utilizar
    'engine' => 'LightWeight',

    // Ruta donde se encuentran las vistas
    'path' => resourcesDirectory() . '/views',

    // Layout predeterminado para todas las vistas
    'default_layout' => 'main',

    // Marcador de contenido que será reemplazado por el contenido de la vista
    'content_annotation' => '@content',

    // Configuración de caché
    'cache' => [
        'enabled' => env('VIEW_CACHE_ENABLED', false),
        'path' => storagePath('views/cache')
    ],

    // Auto-escapado de variables para prevenir XSS
    'auto_escape' => true,
];

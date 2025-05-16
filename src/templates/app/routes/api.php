<?php

use LightWeight\Routing\Route;

// Ejemplo de uso de grupos de middleware para API
Route::prefix('/api', function () {
    // Todas estas rutas usarán el grupo de middleware 'api'
    
    Route::get('/users', function () {
        return json([
            'users' => [
                ['id' => 1, 'name' => 'Juan Pérez'],
                ['id' => 2, 'name' => 'María González'],
            ]
        ]);
    })->setMiddlewareGroups(['api']);
    
    Route::get('/products', function () {
        return json([
            'products' => [
                ['id' => 1, 'name' => 'Producto A', 'price' => 19.99],
                ['id' => 2, 'name' => 'Producto B', 'price' => 29.99],
            ]
        ]);
    })->setMiddlewareGroups(['api']);
    
    // Rutas que requieren autenticación
    Route::get('/profile', function () {
        return json([
            'user' => [
                'id' => 1,
                'name' => 'Juan Pérez',
                'email' => 'juan@example.com'
            ]
        ]);
    })->setMiddlewareGroups(['api', 'auth']);
});

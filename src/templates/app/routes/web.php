<?php

use LightWeight\Routing\Route;

// Ejemplo de uso de grupos de middleware
Route::get('/', function () {
    return view('welcome');
})->setMiddlewareGroups(['web']);

// Rutas que requieren autenticación
Route::get('/dashboard', function () {
    return view('dashboard');
})->setMiddlewareGroups(['web', 'auth']);

// Ejemplo de agrupación con prefijo de ruta
Route::prefix('/admin', function () {
    Route::get('/users', function () {
        return view('admin.users');
    })->setMiddlewareGroups(['web', 'auth']);

    Route::get('/settings', function () {
        return view('admin.settings');
    })->setMiddlewareGroups(['web', 'auth']);
});

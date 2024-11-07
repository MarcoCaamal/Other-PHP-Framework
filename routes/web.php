<?php

use App\Controllers\Auth\LoginController;
use App\Controllers\Auth\RegisterController;
use App\Models\User;
use LightWeight\Crypto\Contracts\HasherContract;
use LightWeight\Http\Request;
use LightWeight\Http\Response;
use LightWeight\Routing\Route;

Route::get('/', function () {
    if(isGuest()) {
        return Response::text('guest');
    }
    return Response::text(auth()->name);
});
Route::get('/login', [LoginController::class, 'create']);
Route::post('/login', [LoginController::class, 'store']);
Route::get('/logout', [LoginController::class, 'destroy']);
Route::get('/register', [RegisterController::class, 'create'])->setName('register.create');
Route::post('/register', [RegisterController::class, 'register'])->setName('register.create');

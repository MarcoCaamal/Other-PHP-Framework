<?php

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
Route::get('/login', fn (Request $request) => view('auth.login'));
Route::post('/login', function (Request $request) {
    $data = $request->validate([
        "email" => ["required", "email"],
        "password" => "required",
    ]);

    $user = User::firstWhere('email', $data['email']);

    if (is_null($user) || !app(HasherContract::class)->verify($data["password"], $user->password)) {
        return back()->withErrors([
            'email' => ['email' => 'Credentials do not match']
        ]);
    }
    $user->login();

    return redirect('/');
});
Route::get('/logout', function () {
    auth()->logout();
    return redirect('/');
});
Route::get('/register', [RegisterController::class, 'create']);
Route::post('/register', function (Request $request) {
    $data = $request->validate([
        "email" => ["required", "email"],
        "name" => "required",
        "lastname" => "required",
        "password" => "required",
        "confirm_password" => "required",
    ]);
    if ($data["password"] !== $data["confirm_password"]) {
        return back()->withErrors([
            "confirm_password" => ["confirm_password" => "Passwords do not match"]
        ]);
    }
    $data["password"] = app(HasherContract::class)->hash($data["password"]);
    User::create($data);
    $user = User::firstWhere('email', $data['email']);
    $user->login();
    return redirect('/');
});

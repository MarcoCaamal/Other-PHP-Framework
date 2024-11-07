<?php

namespace App\Controllers\Auth;

use App\Models\User;
use LightWeight\Crypto\Contracts\HasherContract;
use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;

class LoginController extends ControllerBase
{
    public function __construct(
        private HasherContract $hasherService
    ) {
        $this->hasherService = $hasherService;
    }

    public function create()
    {
        return view('auth.login');
    }
    public function store(Request $request)
    {
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
    }
    public function destroy()
    {
        auth()->logout();
        return redirect('/');
    }
}

<?php

namespace App\Controllers\Auth;

use App\Models\User;
use LightWeight\Crypto\Contracts\HasherContract;
use LightWeight\Http\ControllerBase;
use LightWeight\Http\Request;

class RegisterController extends ControllerBase
{
    public function create(Request $request)
    {
        return view('auth/register');
    }
    public function store(Request $request)
    {
        $data = $request->validate([
            "email" => ["required", "email"],
            "name" => "required",
            "password" => "required",
            "confirm_password" => "required",
        ]);

        if ($data["password"] !== $data["confirm_password"]) {
            return back()->withErrors([
                "confirm_password" => ["confirm_password" => "Passwords do not match"]
            ]);
        }

        $data["password"] = app(HasherContract::class)->hash($data["password"]);

        $user = User::create($data);
        $user->login();

        return redirect('/');
    }
}

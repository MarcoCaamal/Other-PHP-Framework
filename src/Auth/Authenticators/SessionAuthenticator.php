<?php

namespace LightWeight\Auth\Authenticators;

use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Auth\Authenticatable;

class SessionAuthenticator implements AuthenticatorContract
{
    public function login(Authenticatable $authenticatable)
    {
        session()->set('_auth', $authenticatable);
    }
    public function logout(Authenticatable $authenticatable)
    {
        session()->remove("_auth");
    }
    public function isAuthenticated(Authenticatable $authenticatable): bool
    {
        return session()->get("_auth")?->id() === $authenticatable->id();
    }
    public function resolve(): ?Authenticatable
    {
        return session()->get("_auth");
    }
}

<?php

namespace LightWeight\Auth\Contracts\Authenticators;

use LightWeight\Auth\Authenticatable;

interface AuthenticatorContract
{
    public function login(Authenticatable $authenticable);
    public function logout(Authenticatable $authenticable);
    public function isAuthenticated(Authenticatable $authenticable): bool;
    public function resolve(): ?Authenticatable;
}

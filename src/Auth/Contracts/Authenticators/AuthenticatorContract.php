<?php

namespace LightWeight\Auth\Contracts\Authenticators;

use LightWeight\Auth\Authenticatable;

interface AuthenticatorContract
{
    public function login(Authenticatable $authenticable, bool $remember = false);
    public function logout(Authenticatable $authenticable);
    public function isAuthenticated(Authenticatable $authenticable): bool;
    public function resolve(): ?Authenticatable;
    public function attempt(array $credentials, bool $remember = false): bool;
    public function validate(array $credentials): bool;
    public function retrieveByCredentials(array $credentials): ?Authenticatable;
}

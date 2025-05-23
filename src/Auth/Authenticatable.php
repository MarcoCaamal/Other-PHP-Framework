<?php

namespace LightWeight\Auth;

use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Database\ORM\Model;

class Authenticatable extends Model
{
    public function id(): int|string
    {
        return $this->{$this->primaryKey};
    }

    /**
     * Log the user in
     *
     * @param bool $remember Whether to "remember" the login
     * @return void
     */
    public function login(bool $remember = false)
    {
        app(AuthenticatorContract::class)->login($this, $remember);
    }

    /**
     * Log the user out
     *
     * @return void
     */
    public function logout()
    {
        app(AuthenticatorContract::class)->logout($this);
    }

    public function isAuthenticated()
    {
        app(AuthenticatorContract::class)->isAuthenticated($this);
    }
}

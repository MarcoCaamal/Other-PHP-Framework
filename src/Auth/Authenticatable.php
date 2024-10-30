<?php

namespace SMFramework\Auth;

use SMFramework\Auth\Contracts\Authenticators\AuthenticatorContract;
use SMFramework\Database\ORM\Model;

class Authenticatable extends Model
{
    public function id(): int|string
    {
        return $this->{$this->primaryKey};
    }

    public function login()
    {
        app(AuthenticatorContract::class)->login($this);
    }
    public function logout()
    {
        app(AuthenticatorContract::class)->logout($this);
    }
    public function isAuthenticated()
    {
        app(AuthenticatorContract::class)->isAuthenticated($this);
    }
}

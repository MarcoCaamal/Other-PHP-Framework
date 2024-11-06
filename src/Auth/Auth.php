<?php

namespace LightWeight\Auth;

use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;

class Auth
{
    public static function user(): ?Authenticatable
    {
        return app(AuthenticatorContract::class)->resolve();
    }
    public static function isGuest(): bool
    {
        return is_null(self::user());
    }
}

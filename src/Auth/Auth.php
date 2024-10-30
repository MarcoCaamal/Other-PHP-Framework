<?php

namespace SMFramework\Auth;

use SMFramework\Auth\Contracts\Authenticators\AuthenticatorContract;

class Auth
{
    public static function user(): ?AuthenticatorContract
    {
        return app(AuthenticatorContract::class)->resolve();
    }
    public static function isGuest(): bool
    {
        return is_null(self::user());
    }
}

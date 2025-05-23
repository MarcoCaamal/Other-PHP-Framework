<?php

namespace LightWeight\Auth;

use LightWeight\Auth\Contracts\Authenticators\AuthenticatorContract;
use LightWeight\Events\AuthAttemptEvent;
use LightWeight\Events\AuthLoginEvent;
use LightWeight\Events\AuthLogoutEvent;

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

    /**
     * Attempt to authenticate a user using the given credentials
     *
     * @param array $credentials
     * @param bool $remember
     * @return bool
     */
    public static function attempt(array $credentials, bool $remember = false): bool
    {
        $authenticator = app(AuthenticatorContract::class);
        $successful = false;
        $user = null;

        // Evento inicial de intento
        if (function_exists('event')) {
            event(new AuthAttemptEvent([
                'credentials' => $credentials,
                'remember' => $remember,
                'successful' => false
            ]));
        }

        // Validar credenciales
        if (method_exists($authenticator, 'validate')) {
            $successful = $authenticator->validate($credentials);
        } elseif (method_exists($authenticator, 'attempt')) {
            // fallback para autenticadores con método attempt
            $successful = $authenticator->attempt($credentials);
        }

        if ($successful && method_exists($authenticator, 'retrieveByCredentials')) {
            $user = $authenticator->retrieveByCredentials($credentials);
        }

        // Evento final con resultado
        if (function_exists('event')) {
            event(new AuthAttemptEvent([
                'credentials' => $credentials,
                'remember' => $remember,
                'successful' => $successful
            ]));
        }

        if ($successful && $user instanceof Authenticatable) {
            // Evento de login exitoso
            if (function_exists('event')) {
                event(new AuthLoginEvent([
                    'user' => $user,
                    'remember' => $remember
                ]));
            }
            // Ejecutar login real
            $user->login($remember);
            return true;
        }

        return false;
    }

    /**
     * Cierra la sesión del usuario autenticado
     */
    public static function logout(): void
    {
        $user = self::user();
        if ($user instanceof Authenticatable) {
            // Evento de logout
            if (function_exists('event')) {
                event(new AuthLogoutEvent([
                    'user' => $user
                ]));
            }
            $user->logout();
        }
    }
}

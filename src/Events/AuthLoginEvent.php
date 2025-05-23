<?php

namespace LightWeight\Events;

/**
 * Event fired when a user logs in
 */
class AuthLoginEvent extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'auth.login';
    }

    /**
     * Get the authenticated user
     *
     * @return \LightWeight\Auth\Authenticatable
     */
    public function getUser()
    {
        return $this->data['user'] ?? null;
    }

    /**
     * Get whether this is a "remember me" login
     *
     * @return bool
     */
    public function isRemembered(): bool
    {
        return $this->data['remember'] ?? false;
    }
}

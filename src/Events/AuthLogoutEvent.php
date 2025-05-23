<?php

namespace LightWeight\Events;

/**
 * Event fired when a user logs out
 */
class AuthLogoutEvent extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'auth.logout';
    }

    /**
     * Get the authenticated user that is logging out
     *
     * @return \LightWeight\Auth\Authenticatable
     */
    public function getUser()
    {
        return $this->data['user'] ?? null;
    }
}

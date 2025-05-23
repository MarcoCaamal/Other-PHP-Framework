<?php

namespace LightWeight\Events;

/**
 * Event fired when a login attempt is made
 */
class AuthAttemptEvent extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'auth.attempt';
    }

    /**
     * Get the credentials used for the login attempt
     *
     * @return array
     */
    public function getCredentials(): array
    {
        return $this->data['credentials'] ?? [];
    }

    /**
     * Get whether the login attempt was successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->data['successful'] ?? false;
    }

    /**
     * Get whether this was a "remember me" login attempt
     *
     * @return bool
     */
    public function isRemembered(): bool
    {
        return $this->data['remember'] ?? false;
    }
}

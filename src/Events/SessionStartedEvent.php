<?php

namespace LightWeight\Events;

/**
 * Event fired when a session is started
 */
class SessionStartedEvent extends Event
{
    /**
     * Get the name of the event
     *
     * @return string
     */
    public function getName(): string
    {
        return 'session.started';
    }
    
    /**
     * Get the session ID
     *
     * @return string
     */
    public function getSessionId(): string
    {
        return $this->data['session_id'] ?? '';
    }
    
    /**
     * Get whether this is a new session
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return $this->data['is_new'] ?? false;
    }
    
    /**
     * Get the session data
     *
     * @return array
     */
    public function getSessionData(): array
    {
        return $this->data['session_data'] ?? [];
    }
}

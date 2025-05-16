<?php

namespace App\Exceptions;

use LightWeight\Exceptions\LightWeightException;

/**
 * Exception for critical system errors that require immediate attention
 */
class CriticalException extends LightWeightException
{
    /**
     * System component that failed
     * 
     * @var string
     */
    protected string $component;

    /**
     * Severity level of the exception
     * 
     * @var string
     */
    protected string $severity;

    /**
     * List of notification channels to use
     * 
     * @var array
     */
    protected array $notificationChannels = [];

    /**
     * Create a new critical exception
     * 
     * @param string $message Error message
     * @param string $component System component that failed
     * @param string $severity Severity level (critical, high, medium, low)
     * @param array $notificationChannels Channels to send notifications to
     * @param int $code Error code
     * @param \Throwable|null $previous Previous exception
     */
    public function __construct(
        string $message,
        string $component,
        string $severity = 'critical',
        array $notificationChannels = ['email', 'log'],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->component = $component;
        $this->severity = $severity;
        $this->notificationChannels = $notificationChannels;
    }

    /**
     * Get the system component that failed
     * 
     * @return string
     */
    public function getComponent(): string
    {
        return $this->component;
    }

    /**
     * Get the severity level
     * 
     * @return string
     */
    public function getSeverity(): string
    {
        return $this->severity;
    }

    /**
     * Get notification channels
     * 
     * @return array
     */
    public function getNotificationChannels(): array
    {
        return $this->notificationChannels;
    }
}

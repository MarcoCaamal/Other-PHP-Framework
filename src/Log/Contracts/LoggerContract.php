<?php

namespace LightWeight\Log\Contracts;

use Psr\Log\LoggerInterface;

/**
 * Logger contract for the LightWeight framework
 */
interface LoggerContract extends LoggerInterface
{
    /**
     * Get the underlying logger instance
     *
     * @return mixed
     */
    public function getLogger(): mixed;

    /**
     * Add a handler to the logger
     *
     * @param mixed $handler
     * @return self
     */
    public function pushHandler(mixed $handler): self;
}

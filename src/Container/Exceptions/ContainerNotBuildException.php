<?php

namespace LightWeight\Container\Exceptions;

use LightWeight\Exceptions\LightWeightException;

class ContainerNotBuildException extends LightWeightException
{
    protected string $message = "The container was named before it was built";
    public function __construct()
    {
    }
}

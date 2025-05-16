<?php

namespace LightWeight\Container\Exceptions;

use LightWeight\Exceptions\LightWeightException;

class ContainerNotBuildException extends LightWeightException
{
    public function __construct()
    {
        parent::__construct("The container was named before it was built");
    }
}

<?php

namespace LightWeight\Routing\Exception;

use LightWeight\Exceptions\LightWeightException;

class RouteDuplicatedNameException extends LightWeightException
{
    public function __construct(string $routeName)
    {
        $this->message = "The route name $routeName already exists";
    }
}

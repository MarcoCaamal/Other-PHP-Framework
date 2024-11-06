<?php

namespace LightWeight\Providers\Contracts;

use DI\Container as DIContainer;

interface ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer);
}

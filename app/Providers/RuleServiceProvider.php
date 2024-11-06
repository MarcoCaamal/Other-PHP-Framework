<?php

namespace App\Providers;

use DI\Container;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Validation\Rule;

class RuleServiceProvider implements ServiceProviderContract
{
    public function registerServices(Container $serviceContainer)
    {
        Rule::loadDefaultRules();
    }
}

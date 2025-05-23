<?php

namespace App\Providers;

use LightWeight\Container\Container;
use LightWeight\Validation\Rule;

class RuleServiceProvider extends \LightWeight\Providers\ServiceProvider
{
    public function registerServices(Container $serviceContainer)
    {
        Rule::loadDefaultRules();
    }
}

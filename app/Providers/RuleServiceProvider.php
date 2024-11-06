<?php

namespace App\Providers;

use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Validation\Rule;

class RuleServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        Rule::loadDefaultRules();
    }
}

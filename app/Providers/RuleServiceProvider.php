<?php

namespace App\Providers;

use SMFramework\Providers\Contracts\ServiceProviderContract;
use SMFramework\Validation\Rule;

class RuleServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        Rule::loadDefaultRules();
    }
}

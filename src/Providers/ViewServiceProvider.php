<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\View\Contracts\ViewContract;
use LightWeight\View\ViewEngine;

class ViewServiceProvider implements ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer)
    {
        $definitions = $this->definitions[config("view.engine", "LightWeight")] ?? [];
        match(config('view.engine', 'LightWeight')) {
            'LightWeight' => $serviceContainer->set(ViewContract::class, \DI\create(ViewEngine::class)->constructor(config('view.path')))
        };
    }
}

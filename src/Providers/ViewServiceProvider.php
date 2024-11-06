<?php

namespace LightWeight\Providers;

use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\View\Contracts\ViewContract;
use LightWeight\View\ViewEngine;

class ViewServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("view.engine", "LightWeight")) {
            "LightWeight" => singleton(ViewContract::class, fn () => new ViewEngine(config("view.path"))),
        };
    }
}

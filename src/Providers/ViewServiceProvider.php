<?php

namespace SMFramework\Providers;

use SMFramework\Providers\Contracts\ServiceProviderContract;
use SMFramework\View\Contracts\ViewContract;
use SMFramework\View\ViewEngine;

class ViewServiceProvider implements ServiceProviderContract
{
    public function registerServices()
    {
        match (config("view.engine", "smframework")) {
            "smframework" => singleton(ViewContract::class, fn () => new ViewEngine(config("view.path"))),
        };
    }
}

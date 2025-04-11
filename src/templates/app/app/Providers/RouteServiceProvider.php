<?php

namespace App\Providers;

use DI\Container;
use LightWeight\App;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Routing\Route;

class RouteServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices(Container $serviceContainer)
    {
        Route::load(App::$root . '/routes');
    }
}

<?php

namespace App\Providers;

use LightWeight\App;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\Routing\Route;

class RouteServiceProvider implements ServiceProviderContract
{
    /**
     * @inheritDoc
     */
    public function registerServices()
    {
        Route::load(App::$root . '/routes');
    }
}

<?php

namespace App\Providers;

use SMFramework\App;
use SMFramework\Providers\Contracts\ServiceProviderContract;
use SMFramework\Routing\Route;

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

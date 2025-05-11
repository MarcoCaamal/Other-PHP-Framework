<?php

namespace LightWeight\Http\Contracts;

use LightWeight\Http\Contracts\RequestContract;

interface MiddlewareContract
{
    public function handle(RequestContract $request, \Closure $next);
}

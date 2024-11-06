<?php

namespace LightWeight\Http\Contracts;

use LightWeight\Http\Request;

interface MiddlewareContract
{
    public function handle(Request $request, \Closure $next);
}

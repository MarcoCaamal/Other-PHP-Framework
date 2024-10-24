<?php

namespace SMFramework\Http\Contracts;

use SMFramework\Http\Request;

interface MiddlewareContract
{
    public function handle(Request $request, \Closure $next);
}

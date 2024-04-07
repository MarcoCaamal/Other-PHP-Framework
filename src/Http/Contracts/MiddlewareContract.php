<?php

namespace Junk\Http\Contracts;

use Junk\Http\Request;

interface MiddlewareContract
{
    public function handle(Request $request, \Closure $next);
}

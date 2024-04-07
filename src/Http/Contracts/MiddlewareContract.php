<?php

namespace Junk\Http\Contracts;

interface MiddlewareContract
{
    public function handle($request, \Closure $next);
}

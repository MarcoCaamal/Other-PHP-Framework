<?php

namespace OtherPHPFramework\Http\Contracts;

use OtherPHPFramework\Http\Request;

interface MiddlewareContract
{
    public function handle(Request $request, \Closure $next);
}

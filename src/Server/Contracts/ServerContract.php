<?php

namespace LightWeight\Server\Contracts;

use LightWeight\Http\Contracts\RequestContract;
use LightWeight\Http\Contracts\ResponseContract;

/**
 * Similar to PHP `$_SERVER` but having an interface allows us to mock these
 * global variables, useful for testing.
 */
interface ServerContract
{
    /**
     * Get request sent by the client
     *
     * @return RequestContract
     */
    public function getRequest(): RequestContract;

    /**
     * Send the response to the client
     *
     * @return void
     */
    public function sendResponse(ResponseContract $response);

    /**
     * Check if the current request needs to be redirected for HTTPS or WWW enforcement
     *
     * @param RequestContract $request The current request
     * @return ResponseContract|null A redirect response if needed, null otherwise
     */
    public function checkRedirects(RequestContract $request): ?ResponseContract;
}

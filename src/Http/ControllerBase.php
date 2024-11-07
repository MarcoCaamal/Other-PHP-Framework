<?php

namespace LightWeight\Http;

class ControllerBase
{
    //
    /**
     * HTTP middlewares.
     *
     * @var \LightWeight\Http\Contracts\MiddlewareContract[]
     */
    protected array $middlewares = [];
    /**
     * Get all HTTP middlewares for this route.
     *
     * @return \LightWeight\Http\Contracts\MiddlewareContract[]
     */
    public function middlewares(): array
    {
        return $this->middlewares;
    }
    public function setMiddlewares(array $middlewares): self
    {
        $this->middlewares = array_map(fn ($middleware) => new $middleware(), $middlewares);
        return $this;
    }
}

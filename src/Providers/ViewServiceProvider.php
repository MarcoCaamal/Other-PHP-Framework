<?php

namespace LightWeight\Providers;

use DI\Container as DIContainer;
use LightWeight\Providers\Contracts\ServiceProviderContract;
use LightWeight\View\Contracts\ViewContract;
use LightWeight\View\LightEngine;

class ViewServiceProvider implements ServiceProviderContract
{
    public function registerServices(DIContainer $serviceContainer)
    {
        match(config('view.engine', 'LightWeight')) {
            'LightWeight' => $this->registerLightEngine($serviceContainer),
            default => throw new \RuntimeException("View engine not supported: " . config('view.engine'))
        };
    }
    
    /**
     * Register the LightEngine view engine with its configuration
     *
     * @param DIContainer $serviceContainer
     * @return void
     */
    protected function registerLightEngine(DIContainer $serviceContainer): void
    {
        // Create instance with path
        $viewEngine = new LightEngine(config('view.path'));
        
        // Configure default layout
        if ($defaultLayout = config('view.default_layout')) {
            $viewEngine->setDefaultLayout($defaultLayout);
        }
        
        // Configure content annotation
        if ($contentAnnotation = config('view.content_annotation')) {
            $viewEngine->setContentAnnotation($contentAnnotation);
        }
        
        // Configure cache
        if (config('view.cache.enabled', false)) {
            $viewEngine->setCache(
                true, 
                config('view.cache.path', storagePath('views/cache'))
            );
        }
        
        // Configure auto-escape
        if (isset(config('view')['auto_escape'])) {
            $viewEngine->setAutoEscape(config('view.auto_escape'));
        }
        
        // Register the instance
        $serviceContainer->set(ViewContract::class, $viewEngine);
    }
}

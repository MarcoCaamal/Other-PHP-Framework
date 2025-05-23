<?php

namespace LightWeight\Providers;

use LightWeight\Container\Container;
use LightWeight\View\Contracts\ViewContract;
use LightWeight\View\LightEngine;

class ViewServiceProvider extends ServiceProvider
{
    /**
     * Proporciona definiciones para el contenedor antes de su compilación
     * 
     * @return array
     */
    public function getDefinitions(): array
    {
        return [
            ViewContract::class => \DI\factory(function () {
                $engine = match(config('view.engine', 'LightWeight')) {
                    'LightWeight' => new LightEngine(config('view.path')),
                    default => throw new \RuntimeException("View engine not supported: " . config('view.engine'))
                };
                
                return $engine;
            })
        ];
    }
    
    public function registerServices(Container $serviceContainer)
    {
        match(config('view.engine', 'LightWeight')) {
            'LightWeight' => $this->registerLightEngine($serviceContainer),
            default => throw new \RuntimeException("View engine not supported: " . config('view.engine'))
        };
    }
      /**
     * Register the LightEngine view engine with its configuration
     *
     * @param Container $serviceContainer
     * @return void
     */
    protected function registerLightEngine(Container $serviceContainer): void
    {
        // Obtenemos el motor de vistas ya instanciado
        $viewEngine = $serviceContainer->get(ViewContract::class);
        
        // Configure default layout
        if ($defaultLayout = config('view.default_layout')) {
            $viewEngine->setDefaultLayout($defaultLayout);
        }
        
        // Configure content annotation
        if ($contentAnnotation = config('view.content_annotation')) {
            $viewEngine->setContentAnnotation($contentAnnotation);
        }
        
        // Configure cache
        // Verify cache is enabled, properly handling string values like "false" from .env files
        $cacheEnabled = filter_var(config('view.cache.enabled', false), FILTER_VALIDATE_BOOLEAN);
        if ($cacheEnabled) {
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

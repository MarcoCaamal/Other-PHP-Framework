<?php

namespace LightWeight\View;

use LightWeight\App;
use LightWeight\Events\Contracts\EventDispatcherInterface;
use LightWeight\Events\ViewRenderedEvent;
use LightWeight\Events\ViewRenderingEvent;
use LightWeight\View\Contracts\ViewContract;

class LightEngine implements ViewContract
{
    protected string $viewsDirectory;
    protected string $defaultLayout = 'main';
    protected string $contentAnotation = '@content';
    
    // Cache settings
    protected bool $cacheEnabled = false;
    protected string $cachePath = '';
    
    // Security settings
    protected bool $autoEscape = true;
    
    // Sections
    protected array $sections = [];
    protected ?string $currentSection = null;
    
    // Current view parameters
    protected ?array $currentViewParams = null;
    
    /**
     * Constructor
     * 
     * @param string $viewsDirectory Directory containing views
     */
    public function __construct(string $viewsDirectory)
    {
        $this->viewsDirectory = rtrim($viewsDirectory, '/');
    }
    
    /**
     * Set default layout
     * 
     * @param string $layout Layout name
     * @return self
     */
    public function setDefaultLayout(string $layout): self
    {
        $this->defaultLayout = $layout;
        return $this;
    }
    
    /**
     * Set content annotation marker
     * 
     * @param string $annotation The string marker that will be replaced with content
     * @return self
     */
    public function setContentAnnotation(string $annotation): self
    {
        $this->contentAnotation = $annotation;
        return $this;
    }
    
    /**
     * Set cache configuration
     *
     * @param bool $enabled Whether to enable caching
     * @param string $path Cache directory path
     * @return self
     */
    public function setCache(bool $enabled, string $path = ''): self
    {
        $this->cacheEnabled = $enabled;
        if ($path) {
            $this->cachePath = rtrim($path, '/');
        }
        return $this;
    }
    
    /**
     * Set auto-escaping configuration
     *
     * @param bool $enabled Whether to auto-escape output
     * @return self
     */
    public function setAutoEscape(bool $enabled): self
    {
        $this->autoEscape = $enabled;
        return $this;
    }
    
    /**
     * Set views directory
     *
     * @param string $directory The directory containing views
     * @return self
     */
    public function setViewsDirectory(string $directory): self
    {
        $this->viewsDirectory = rtrim($directory, '/');
        return $this;
    }
    
    /**
     * Find a view file in either user directory or default templates
     * 
     * @param string $path Relative path to the view
     * @return string Full path to the found view file or null if not found
     */
    protected function findViewFile(string $path): ?string
    {
        // Check if the file exists in the configured views directory
        $viewPath = "{$this->viewsDirectory}/$path.php";
        
        if (file_exists($viewPath)) {
            return $viewPath;
        }
        
        // Check in resources/views as a fallback
        $resourcesViewsPath = __DIR__ . '/../../templates/default/views';
        $resourceViewPath = "$resourcesViewsPath/$path.php";
        
        if (is_dir($resourcesViewsPath) && file_exists($resourceViewPath)) {
            return $resourceViewPath;
        }
        
        return null;
    }
    
    /**
     * Dispatch the view.rendering event
     *
     * @param string $view View name
     * @param array $params View parameters
     * @param string|bool|null $layout Layout
     * @return void
     */
    protected function dispatchViewRenderingEvent(string $view, array $params, $layout): void
    {
        // Use the global 'event' helper function if available
        if (function_exists('event')) {
            event(new ViewRenderingEvent([
                'view' => $view,
                'params' => $params,
                'layout' => $layout
            ]));
        }
    }
    
    /**
     * Dispatch the view.rendered event
     *
     * @param string $view View name
     * @param array $params View parameters
     * @param string|bool|null $layout Layout
     * @param string $content Rendered content
     * @return void
     */
    protected function dispatchViewRenderedEvent(string $view, array $params, $layout, string $content): void
    {
        // Use the global 'event' helper function if available
        if (function_exists('event')) {
            event(new ViewRenderedEvent([
                'view' => $view,
                'params' => $params,
                'layout' => $layout,
                'content' => $content
            ]));
        }
    }
    
    /**
     * Render a view with the given parameters
     *
     * @param string $view View name (with dot notation)
     * @param array $params Parameters to pass to the view
     * @param string|bool|null $layout Layout to use (null for default, false for no layout)
     * @return string Rendered content
     * @throws \RuntimeException If view or layout file doesn't exist
     */
    public function render(string $view, array $params = [], $layout = null): string
    {
        // Reset sections
        $this->sections = [];
        
        $view = str_replace('.', '/', $view);
        $viewPath = $this->findViewFile($view);
        
        if (!$viewPath) {
            throw new \RuntimeException("View file not found: $view.php");
        }
        
        // Dispatch view.rendering event
        $this->dispatchViewRenderingEvent($view, $params, $layout);
        
        $viewContent = $this->renderView($view, $params);
        
        // No layout
        if ($layout === false) {
            // Dispatch view.rendered event
            $this->dispatchViewRenderedEvent($view, $params, $layout, $viewContent);
            
            return $viewContent;
        }
        
        // With layout - only use defaultLayout if layout is null (not false or empty string)
        $layoutName = $layout === null ? $this->defaultLayout : $layout;
        $layoutContent = $this->renderLayout($layoutName);
        $finalContent = str_replace($this->contentAnotation, $viewContent, $layoutContent);
        
        // Dispatch view.rendered event
        $this->dispatchViewRenderedEvent($view, $params, $layout, $finalContent);
        
        return $finalContent;
    }
    
    /**
     * Render a view file
     *
     * @param string $view View name
     * @param array $params Parameters to pass to the view
     * @return string Rendered content
     */
    public function renderView(string $view, array $params = [])
    {
        $viewPath = $this->findViewFile($view);
        
        if (!$viewPath) {
            throw new \RuntimeException(
                "View file not found: $view.php"
            );
        }
        
        if ($this->cacheEnabled && $this->cachePath) {
            $cacheKey = md5($view . serialize($params));
            $cachePath = "{$this->cachePath}/$cacheKey.php";
            
            // Use cached version if it exists and is newer than view file
            if (file_exists($cachePath) && filemtime($cachePath) > filemtime($viewPath)) {
                return include $cachePath;
            }
            
            // Generate and cache the view
            $content = $this->phpFileOutput($viewPath, $params);
            
            if (!is_dir($this->cachePath)) {
                mkdir($this->cachePath, 0755, true);
            }
            
            // Escapamos las comillas simples para evitar problemas con el código PHP
            $escapedContent = str_replace("'", "\\'", $content);
            file_put_contents($cachePath, "<?php return '" . $escapedContent . "';");
            return $content;
        }
        
        return $this->phpFileOutput($viewPath, $params);
    }
    
    /**
     * Render a layout file
     *
     * @param string $layout Layout name
     * @return string Rendered layout
     * @throws \RuntimeException If layout file doesn't exist
     */
    public function renderLayout(string $layout): string
    {
        // Try user layouts directory first
        $userLayoutPath = "{$this->viewsDirectory}/layouts/$layout.php";
        
        if (file_exists($userLayoutPath)) {
            return $this->phpFileOutput($userLayoutPath);
        }
        
        // Check in resources/views as a fallback
        $resourcesViewsPath = rtrim(dirname(dirname(dirname(__DIR__))), '/') . '/resources/views';
        $resourceLayoutPath = "$resourcesViewsPath/layouts/$layout.php";
        
        if (is_dir($resourcesViewsPath) && file_exists($resourceLayoutPath)) {
            return $this->phpFileOutput($resourceLayoutPath);
        }
        
        throw new \RuntimeException(
            "Layout file not found: $layout.php\n" .
            "Searched locations:\n" .
            "- $userLayoutPath\n" .
            "- $resourceLayoutPath"
        );
    }
    
    /**
     * Render a PHP file with extracted parameters
     *
     * @param string $phpFile File path
     * @param array $params Parameters to extract
     * @return string Output content
     * @throws \RuntimeException If the file doesn't exist
     */
    public function phpFileOutput(string $phpFile, array $params = [])
    {
        if (!file_exists($phpFile)) {
            throw new \RuntimeException("File not found: $phpFile");
        }
        
        // Store current params for includes
        $previousParams = $this->currentViewParams;
        $this->currentViewParams = $params;
        
        $params = $this->prepareParams($params);
        
        // Add view helper functions to scope
        $view = $this;
        
        extract($params);
        
        ob_start();
        include $phpFile;
        $content = ob_get_clean();
        
        // Restore previous params
        $this->currentViewParams = $previousParams;
        
        return $content;
    }
    
    /**
     * Prepare parameters with auto-escaping if enabled
     *
     * @param array $params Raw parameters
     * @return array Processed parameters
     */
    protected function prepareParams(array $params): array
    {
        if (!$this->autoEscape) {
            return $params;
        }
        
        $escapedParams = [];
        foreach ($params as $key => $value) {
            // Don't escape objects and arrays, only scalar values
            if (is_scalar($value) && !is_bool($value)) {
                $escapedParams[$key] = $this->e($value);
            } else {
                $escapedParams[$key] = $value;
            }
        }
        
        // Add the escape helper function to params
        $escapedParams['e'] = [$this, 'e'];
        
        return $escapedParams;
    }
    
    /**
     * Start a new section
     *
     * @param string $name Section name
     * @return void
     */
    public function startSection(string $name): void
    {
        $this->currentSection = $name;
        ob_start();
    }
    
    /**
     * End the current section
     *
     * @return void
     */
    public function endSection(): void
    {
        if ($this->currentSection) {
            $this->sections[$this->currentSection] = ob_get_clean();
            $this->currentSection = null;
        }
    }
    
    /**
     * Yield a section's content
     *
     * @param string $name Section name
     * @param string $default Default content if section not found
     * @return string
     */
    public function yieldSection(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }
    
    /**
     * Include another view within current view
     *
     * @param string $view View name
     * @param array $params Parameters to pass to the view
     * @return string
     */
    public function include(string $view, array $params = []): string
    {
        return $this->renderView(str_replace('.', '/', $view), array_merge($this->currentViewParams ?? [], $params));
    }
    
    /**
     * Escape HTML special characters
     *
     * @param mixed $value Value to escape
     * @return string Escaped string
     */
    public function e($value): string
    {
        return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Get the current URI
     *
     * @return string
     */
    public function currentUri(): string
    {
        return $_SERVER['REQUEST_URI'] ?? '/';
    }
    
    /**
     * Check if the current request matches a URI pattern
     *
     * @param string $pattern URI pattern to match
     * @return bool
     */
    public function isActive(string $pattern): bool
    {
        $currentUri = $this->currentUri();
        return $pattern === $currentUri || 
               ($pattern !== '/' && strpos($currentUri, $pattern) === 0);
    }
}

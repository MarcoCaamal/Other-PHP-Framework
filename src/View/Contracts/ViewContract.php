<?php

namespace LightWeight\View\Contracts;

interface ViewContract
{
    /**
     * Render a view with the given parameters
     *
     * @param string $view View name (with dot notation)
     * @param array $params Parameters to pass to the view
     * @param string|bool|null $layout Layout to use (null for default, false for no layout)
     * @return string Rendered content
     */
    public function render(string $view, array $params = [], $layout = null): string;
    
    /**
     * Render a view file
     *
     * @param string $view View name
     * @param array $params Parameters to pass to the view
     * @return string Rendered content
     */
    public function renderView(string $view, array $params = []);
    
    /**
     * Render a layout file
     *
     * @param string $layout Layout name
     * @return string Rendered layout
     */
    public function renderLayout(string $layout): string;
    
    /**
     * Set default layout
     *
     * @param string $layout Layout name
     * @return self
     */
    public function setDefaultLayout(string $layout): self;
    
    /**
     * Set content annotation marker
     *
     * @param string $annotation The string marker that will be replaced with content
     * @return self
     */
    public function setContentAnnotation(string $annotation): self;
    
    /**
     * Set cache configuration
     *
     * @param bool $enabled Whether to enable caching
     * @param string $path Cache directory path
     * @return self
     */
    public function setCache(bool $enabled, string $path = ''): self;
    
    /**
     * Set auto-escaping configuration
     *
     * @param bool $enabled Whether to auto-escape output
     * @return self
     */
    public function setAutoEscape(bool $enabled): self;
    
    /**
     * Start a new section
     *
     * @param string $name Section name
     * @return void
     */
    public function startSection(string $name): void;
    
    /**
     * End the current section
     *
     * @return void
     */
    public function endSection(): void;
    
    /**
     * Yield a section's content
     *
     * @param string $name Section name
     * @param string $default Default content if section not found
     * @return string
     */
    public function yieldSection(string $name, string $default = ''): string;
    
    /**
     * Include another view within current view
     *
     * @param string $view View name
     * @param array $params Parameters to pass to the view
     * @return string
     */
    public function include(string $view, array $params = []): string;
    
    /**
     * Escape HTML special characters
     *
     * @param mixed $value Value to escape
     * @return string Escaped string
     */
    public function e($value): string;

    /**
     * Set views directory
     *
     * @param string $directory The directory containing views
     * @return self
     */
    public function setViewsDirectory(string $directory): self;
}

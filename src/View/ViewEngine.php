<?php

namespace LightWeight\View;

use LightWeight\View\Contracts\ViewContract;

class ViewEngine implements ViewContract
{
    protected string $viewsDirectory;
    protected string $defaultLayout = 'main';
    protected string $contentAnotation = '@content';
    public function __construct(string $viewsDirectory)
    {
        $this->viewsDirectory = $viewsDirectory;
    }

    /**
     *
     * @param string $view
     * @return string
     */
    public function render(string $view, array $params = [], ?string $layout = null): string
    {
        $layoutContent = $this->renderLayout($layout ?? $this->defaultLayout);
        $viewContent = $this->renderView($view, $params);

        return str_replace($this->contentAnotation, $viewContent, $layoutContent);
    }

    public function renderView(string $view, array $params = [])
    {
        return $this->phpFileOutput("{$this->viewsDirectory}/$view.php", $params);
    }
    public function renderLayout(string $layout): string
    {
        return $this->phpFileOutput("{$this->viewsDirectory}/layouts/$layout.php");
    }
    public function phpFileOutput(string $phpFile, array $params = [])
    {
        foreach ($params as $key => $value) {
            $$key = $value;
        }

        ob_start();

        include_once $phpFile;

        return ob_get_clean();
    }
}

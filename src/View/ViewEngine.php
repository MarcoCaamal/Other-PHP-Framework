<?php

namespace Junk\View;

use Junk\View\Contracts\ViewContract;

class ViewEngine implements ViewContract
{
    protected string $viewDirectory;
    public function __construct(string $viewDirectory)
    {
        $this->viewDirectory = $viewDirectory;
    }

    /**
     *
     * @param string $view
     * @return string
     */
    public function render(string $view): string
    {
        $phpFile = "{$this->viewDirectory}/{$view}.php";

        ob_start();

        include_once $phpFile;

        return ob_get_clean();
    }
}

<?php

namespace SMFramework\View\Contracts;

interface ViewContract
{
    public function render(string $view, array $params = [], ?string $layout = null): string;
}

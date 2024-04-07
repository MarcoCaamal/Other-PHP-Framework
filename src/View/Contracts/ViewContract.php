<?php

namespace Junk\View\Contracts;

interface ViewContract
{
    public function render(string $view): string;
}

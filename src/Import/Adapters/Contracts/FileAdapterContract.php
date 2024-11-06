<?php

namespace LightWeight\Import\Adapters\Contracts;

interface FileAdapterContract
{
    public function readFile(string $fileRoot): array;
}

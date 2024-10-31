<?php

namespace SMFramework\Crypto\Contracts;

interface HasherContract
{
    public function hash(string $input): string;
    public function verify(string $input, string $hash): bool;
}

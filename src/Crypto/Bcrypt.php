<?php

namespace SMFramework\Crypto;

use SMFramework\Crypto\Contracts\HasherContract;

class Bcrypt implements HasherContract
{
    public function hash(string $input): string
    {
        return password_hash($input, PASSWORD_BCRYPT);
    }
    public function verify(string $input, string $hash): bool
    {
        return password_verify($input, $hash);
    }
}

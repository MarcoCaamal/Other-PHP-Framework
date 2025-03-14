<?php

namespace Src\Auth\JWT;

use Src\Auth\JWT\Contracts\JWTServiceContract;

class JWTService implements JWTServiceContract
{
    private \Ahc\Jwt\JWT $jwt;
    public function __construct(
        string $key,
        string $digestAlg,
        int $KeyBits,
        int $maxAge,
        int $leeway,
    ) {
        $this->jwt = new \Ahc\Jwt\JWT($key, $digestAlg, $KeyBits, $maxAge, $leeway);
    }
    /**
     * @inheritDoc
     */
    public function decode(string $token): array 
    {
        return $this->jwt->decode($token);
    }
    
    /**
     * @inheritDoc
     */
    public function encode(array $payload): string 
    {
        return $this->jwt->encode($payload);
    }
}
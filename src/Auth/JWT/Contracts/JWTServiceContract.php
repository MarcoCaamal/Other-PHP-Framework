<?php

namespace LightWeight\Auth\JWT\Contracts;

interface JWTServiceContract
{
    public function encode(array $payload): string;
    public function decode(string $token): array;
}
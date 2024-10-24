<?php

namespace OtherPHPFramework\Database;

interface DatabaseDriverContract
{
    public function connect(
        string $protocol,
        string $host,
        int $port,
        string $database,
        string $username,
        string $password
    );
    public function close();
    public function statement(string $query, array $bind = []): array;
    public function execute(string $query, array $bind = []): bool;
}

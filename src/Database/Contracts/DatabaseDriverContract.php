<?php

namespace LightWeight\Database\Contracts;

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
    public function lastInsertId();

    /**
     * Begin a database transaction
     *
     * @return bool
     */
    public function beginTransaction(): bool;

    /**
     * Commit the active database transaction
     *
     * @return bool
     */
    public function commit(): bool;

    /**
     * Rollback the active database transaction
     *
     * @return bool
     */
    public function rollback(): bool;
}

<?php

namespace LightWeight\Database;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\QueryBuilder\Builder;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;

class DB
{
    public static function statement(string $query, array $bind = [])
    {
        return app(DatabaseDriverContract::class)->statement($query, $bind);
    }

    /**
     * Create a new query builder instance
     *
     * @param string $table Table name
     * @return Builder
     */
    public static function table(string $table): Builder
    {
        $builder = app(QueryBuilderContract::class);
        return (new Builder($builder))->table($table);
    }

    /**
     * Execute a raw query against the database
     *
     * @param string $query
     * @param array $bindings
     * @return array
     */
    public static function raw(string $query, array $bindings = []): array
    {
        return self::statement($query, $bindings);
    }

    /**
     * Execute a select query against the database
     *
     * @param string $query
     * @param array $bindings
     * @return array
     */
    public static function select(string $query, array $bindings = []): array
    {
        return self::statement($query, $bindings);
    }

    /**
     * Begin a transaction
     */
    public static function beginTransaction(): bool
    {
        // Si tu DatabaseDriverContract no tiene este método, deberías agregarlo
        if (method_exists(app(DatabaseDriverContract::class), 'beginTransaction')) {
            return app(DatabaseDriverContract::class)->beginTransaction();
        }

        return false;
    }

    /**
     * Commit a transaction
     */
    public static function commit(): bool
    {
        if (method_exists(app(DatabaseDriverContract::class), 'commit')) {
            return app(DatabaseDriverContract::class)->commit();
        }

        return false;
    }

    /**
     * Rollback a transaction
     */
    public static function rollback(): bool
    {
        if (method_exists(app(DatabaseDriverContract::class), 'rollback')) {
            return app(DatabaseDriverContract::class)->rollback();
        }

        return false;
    }
}

<?php

namespace LightWeight\Database\QueryBuilder\Contracts;

use Closure;
use LightWeight\Database\QueryBuilder\Metadata\Column;

interface QueryBuilderContract
{
    // Métodos existentes...
    public function table(string $table): static;
    public function select(array $columns = ['*']): static;

    public function where(string $column, string $operator, mixed $value, string $boolean = 'AND'): static;
    public function orWhere(string $column, string $operator, mixed $value): static;
    public function whereIn(string $column, array $values, string $boolean = 'AND'): static;
    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): static;
    public function whereNull(string $column, string $boolean = 'AND'): static;
    public function whereNotNull(string $column, string $boolean = 'AND'): static;
    public function whereBetween(string $column, mixed $value1, mixed $value2, string $boolean = 'AND'): static;
    public function whereNotBetween(string $column, mixed $value1, mixed $value2, string $boolean = 'AND'): static;
    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'AND'): static;
    public function orWhereRaw(string $sql, array $bindings = []): static;
    public function whereGroup(Closure $callback, string $boolean = 'AND'): static;
    public function orWhereGroup(Closure $callback): static;

    /**
     * Add a where clause with a callback function
     * 
     * @param Closure $callback Function that receives the query builder
     * @param string $boolean The boolean operator (AND/OR)
     * @return static
     */
    public function whereCallback(Closure $callback, string $boolean = 'AND'): static;

    public function orderBy(string $column, string $direction = 'asc'): static;
    public function limit(int $limit): static;
    public function offset(int $offset): static;
    public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): static;
    public function leftJoin(string $table, string $first, string $operator, string $second): static;
    public function rightJoin(string $table, string $first, string $operator, string $second): static;
    public function get(): array;
    public function first(): array|null;
    public function insert(array $data): bool;
    public function update(array $data): bool;
    public function delete(): bool;
    public function lastInsertId(): int|string;

    public function sum(string $column): float|int;
    public function avg(string $column): float|int;
    public function min(string $column): float|int;
    public function max(string $column): float|int;
    public function count(string $column = '*'): int;

    public function paginate(int $perPage = 15, int $page = 1): array;
    public function subQuery(Closure $callback, string $alias): static;

    public function increment(string $column, int $value = 1): int;
    public function decrement(string $column, int $value = 1): int;

    public function toSql(): string;
    
    /**
     * Get the current table name.
     *
     * @return string
     */
    public function getTable(): string;
    
    /**
     * Get the database connection instance.
     *
     * @return \LightWeight\Database\Contracts\DatabaseDriverContract
     */
    public function getConnection();
    
    /**
     * Get the where clause for the current query.
     * Used to extract constraints in subqueries for relationship queries.
     *
     * @return string
     */
    public function getWhereClause(): string;

    /**
     * Reset the query builder state.
     * Clears all conditions, joins, orders, limits, etc.
     *
     * @return static
     */
    public function reset(): static;

    /**
     * Get information columns of table
     * @return Column[]
     */
    public function getMetadataOfTableColumns(): array;

    /**
     * Add a raw select expression to the query.
     *
     * @param string $expression The raw SQL expression to add to the select clause
     * @return static
     */
    public function selectRaw(string $expression): static;
}

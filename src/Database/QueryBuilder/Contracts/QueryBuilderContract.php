<?php

namespace SMFramework\Database\QueryBuilder\Contracts;

use Closure;
use SMFramework\Database\Contracts\DatabaseDriverContract;
use SMFramework\Database\ORM\Model;

interface QueryBuilderContract
{
    public function first(): string;
    public function toSql(): string;
    public function table(string $table): QueryBuilderContract;
    public function insert(array $data): string;
    public function update(array $data): string;
    public function distinct(): QueryBuilderContract;
    public function delete(): string;
    public function select(array $columns): QueryBuilderContract;
    public function take(int $rows): QueryBuilderContract;
    public function skip(int $rows): QueryBuilderContract;
    public function find(int|string $id): string;
    public function where(string|Closure $column, mixed $operator = "=", mixed $value = null, string $condition = "AND"): QueryBuilderContract;
    public function orWhere(string|Closure $column, mixed $operator = '=', mixed $value = null): QueryBuilderContract;
    public function whereNot(string|Closure $column, mixed $operator = "=", mixed $value = null, string $condition = 'AND'): QueryBuilderContract;
    public function whereAny(array $columns, string $operator, mixed $value, string $condition = 'AND'): QueryBuilderContract;
    public function whereAll(array $columns, string $operator, mixed $value, string $condition = 'AND'): QueryBuilderContract;
    public function whereNone(array $columns, string $operator, mixed $value, string $condition = 'AND'): QueryBuilderContract;
    public function join(string $table, string $first, string $operator, string $second, $type = 'INNER'): QueryBuilderContract;
    public function leftJoin(string $table, string $first, string $operator, string $second): QueryBuilderContract;
    public function rightJoin(string $table, string $first, string $operator, string $second): QueryBuilderContract;
    public function groupBy(string $column): static;
    public function orderBy(string $column, string $direction = 'ASC'): static;
    public function getValues(): array;
    public function setPrimaryKey(?string $primaryKey): static;
}

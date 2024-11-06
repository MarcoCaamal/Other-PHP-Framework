<?php

namespace LightWeight\Database\QueryBuilder;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;

class QueryBuilder
{
    private ?string $primaryKey;
    public function __construct(
        private QueryBuilderContract $builder,
        private DatabaseDriverContract $driver
    ) {
        $this->builder = $builder;
        $this->driver = $driver;
    }
    public function table(string $table): static
    {
        $this->builder->table($table);
        return $this;
    }
    public function select(array $columns): static
    {
        $this->builder->select($columns);
        return $this;
    }
    public function find(string|int $id): array
    {
        $sql = $this->builder->setPrimaryKey($this->primaryKey)->find($id);
        return $this->driver->statement($sql, $this->builder->getValues());
    }
    public function setPrimaryKey(string $primaryKey): static
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }
    public function update(array $data): bool
    {
        return $this->driver
            ->execute($this->builder->update($data), $this->builder->getValues());
    }
    public function delete(): bool
    {
        return $this->driver->execute($this->builder->delete(), $this->builder->getValues());
    }
    public function get(array $columns = ['*']): array
    {
        if(func_num_args() === 1) {
            $this->builder->select($columns);
        }
        return $this->driver->statement($this->builder->toSql(), $this->builder->getValues());
    }
    public function first(): array
    {
        return $this->driver->statement($this->builder->first(), $this->builder->getValues());
    }
    public function join(string $table, string $first, string $operator, string $second, $type = 'INNER'): static
    {
        $this->join($table, $first, $operator, $second, $type);
        return $this;
    }
    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        $this->leftJoin($table, $first, $operator, $second);
        return $this;
    }
    public function rightJoin(string $table, string $first, string $operator, string $second): static
    {
        $this->rightJoin($table, $first, $operator, $second);
        return $this;
    }
    public function where(string|\Closure $column, mixed $operator = "=", mixed $value = null, string $condition = "AND"): static
    {
        $numArgs = func_num_args();
        match(true) {
            $column instanceof \Closure && $numArgs === 1 => $this->builder->where($column),
            is_string($column) && $numArgs === 2 => $this->builder->where($column, $operator),
            is_string($column) && $numArgs === 3 && $value !== null => $this->where($column, $operator, $value),
            is_string($column) && $numArgs === 4 => $this->builder->where($column, $operator, $condition)
        };
        return $this;
    }
    public function orWhere(string|\Closure $column, mixed $operator = '=', mixed $value = null): static
    {
        $numArgs = func_num_args();
        match(true) {
            $column instanceof \Closure && $numArgs === 1 => $this->builder->whereNot($column),
            is_string($column) && $numArgs === 2 => $this->builder->whereNot($column, $operator),
            is_string($column) && $numArgs === 3 && $value !== null => $this->builder->whereNot($column, $operator, $value),
        };
        return $this;
    }
    public function whereAny(array $columns, string $operator, mixed $value, string $condition = 'AND'): static
    {
        $this->builder->whereAny($columns, $operator, $value, $condition);
        return $this;
    }
    public function whereAll(array $columns, string $operator, mixed $value, string $condition = 'AND'): static
    {
        $this->builder->whereAll($columns, $operator, $value, $condition);
        return $this;
    }
    public function whereNone(array $columns, string $operator, mixed $value, string $condition = 'AND'): static
    {
        $this->builder->whereNone($columns, $operator, $value, $condition);
        return $this;
    }
    public function groupBy(string $column): static
    {
        $this->builder->groupBy($column);
        return $this;
    }
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->builder->orderBy($column, $direction);
        return $this;
    }
    public function take(int $rows): static
    {
        $this->builder->take($rows);
        return $this;
    }
    public function skip(int $rows): static
    {
        $this->builder->skip($rows);
        return $this;
    }
}

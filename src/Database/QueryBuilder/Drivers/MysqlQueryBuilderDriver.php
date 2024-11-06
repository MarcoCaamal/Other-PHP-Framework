<?php

namespace LightWeight\Database\QueryBuilder\Drivers;

use PharIo\Manifest\Type;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;
use LightWeight\Database\QueryBuilder\Exceptions\QueryBuilderException;
use LightWeight\Database\QueryBuilder\TypeWhere;

class MysqlQueryBuilderDriver implements QueryBuilderContract
{
    private ?string $table;
    private ?string $primaryKey = null;
    private array $selectedColumns = ['*'];
    private array $conditions = [];
    private array $values = [];
    private array $orderBy = [];
    private array $groupBy = [];
    private array $joins = [];
    private int $limit = 0;
    private int $offset = 0;
    private bool $useDistinct = false;
    private bool $isWhereWrapped = false;
    /**
     * @inheritDoc
     */
    public function first(): string
    {
        if($this->table === null) {
            throw new QueryBuilderException("Can't use first() if you don't select table with table()");
        }
        $this->take(1);
        $this->skip(0);
        return trim($this->buildSelect()
        . $this->buildJoins()
        . $this->buildWheres()
        . $this->buildGroupBy()
        . $this->buildOrderBy()
         . $this->buildLimit());
    }

    /**
     * @inheritDoc
     */
    public function insert(array $data): string
    {
        if(!isset($data[0]) && !is_array($data[0])) {
            $columns = array_keys($data);
            $values = array_fill(0, count($data), '?');
            $this->values = array_values($data);

            return "INSERT INTO {$this->table} (" . implode(", ", $columns) . ") VALUES (" . implode(', ', $values) . ")";
        }
        $columns = array_keys($data[0]);
        $sql = "INSERT INTO {$this->table} (" . implode(", ", $columns) . ") VALUES ";
        foreach($data as $insert) {
            $this->values = array_merge($this->values, array_values($insert));
            $values = array_fill(0, count($insert), '?');
            $sql .= "(" . implode(', ', $values) . "), ";
        }
        return rtrim($sql, ", ");
    }

    /**
     * @inheritDoc
     */
    public function join(string $table, string $first, string $operator, string $second, $type = 'INNER'): QueryBuilderContract
    {
        $this->joins[] = [
            'type' => $type,
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): QueryBuilderContract
    {
        $this->join($table, $first, $operator, $second, 'RIGHT');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orWhere(string|\Closure $column, mixed $operator = '=', mixed $value = null): QueryBuilderContract
    {
        $numArgs = func_num_args();
        if($column instanceof \Closure && $numArgs === 1) {
            $subQuery = new static();
            $column($subQuery);
            $subSql = $subQuery->isWhereWrapped()->toSql();
            $this->values = array_merge($this->values, $subQuery->getValues());
            $this->conditions[] = [
                'type' => TypeWhere::WRAPPED,
                'sql' => "($subSql)",
                'condition' => "OR"
            ];
        }
        if(is_string($column) && $numArgs === 2) {
            $this->conditions[] = [
                'type' => TypeWhere::NORMAL,
                'column' => $column,
                'operator' => '=',
                'condition' => "OR"
            ];
            $this->values[] = $value;
        }
        if(is_string($column) && is_string($operator) && ($numArgs === 3 || $numArgs === 4)) {
            $this->conditions[] = [
                'type' => TypeWhere::NORMAL,
                'column' => $column,
                'operator' => $operator,
                'condition' => "OR"
            ];
            $this->values[] = $value;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): QueryBuilderContract
    {
        $this->join($table, $first, $operator, $second, 'RIGHT');
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function select(array $columns): QueryBuilderContract
    {
        $this->selectedColumns = $columns;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function table(string $table): QueryBuilderContract
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function toSql(): string
    {
        if($this->table === null) {
            throw new QueryBuilderException("Can't use toSql() if you don't select table with table()");
        }
        if($this->isWhereWrapped) {
            return $this->buildWheres();
        }
        return trim($this->buildSelect()
        . $this->buildJoins() . ' '
        . $this->buildWheres() . ' '
        . $this->buildGroupBy() . ' '
        . $this->buildOrderBy() . ' '
         . $this->buildLimit());
    }
    /**
     * @inheritDoc
     */
    public function where(string|\Closure $column, mixed $operator = '=', mixed $value = null, string $condition = 'AND'): QueryBuilderContract
    {
        $numArgs = func_num_args();
        if($column instanceof \Closure && $numArgs === 1) {
            $subQuery = new static();
            $column($subQuery);
            // var_dump($subQuery);
            $subSql = $subQuery->isWhereWrapped()->toSql();
            $this->values = array_merge($this->values, $subQuery->getValues());
            $this->conditions[] = [
                'type' => TypeWhere::WRAPPED,
                'sql' => "($subSql)",
                'condition' => $condition
            ];
        }
        if(is_string($column) && $numArgs === 2) {
            $this->conditions[] = [
                'type' => TypeWhere::NORMAL,
                'column' => $column,
                'operator' => '=',
                'condition' => $condition
            ];
            $this->values[] = $operator;
        }
        if(is_string($column) && is_string($operator) && ($numArgs === 3 || $numArgs === 4)) {
            $this->conditions[] = [
                'type' => TypeWhere::NORMAL,
                'column' => $column,
                'operator' => $operator,
                'condition' => $condition
            ];
            $this->values[] = $value;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereAll(array $columns, string $operator, mixed $value, string $condition = 'AND'): QueryBuilderContract
    {
        $this->conditions[] = [
            'type' => TypeWhere::ALL,
            'columns' => $columns,
            'operator' => $operator,
            'condition' => $condition
        ];
        for($i = 0; $i < count($columns); $i++) {
            $this->values[] = $value;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereAny(array $columns, string $operator, mixed $value, string $condition = 'AND'): QueryBuilderContract
    {
        $this->conditions[] = [
            'type' => TypeWhere::ANY,
            'columns' => $columns,
            'operator' => $operator,
            'condition' => $condition
        ];
        for($i = 0; $i < count($columns); $i++) {
            $this->values[] = $value;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereNone(array $columns, string $operator, mixed $value, string $condition = 'AND'): QueryBuilderContract
    {
        $this->conditions[] = [
            'type' => TypeWhere::NONE,
            'columns' => $columns,
            'operator' => $operator,
            'condition' => $condition
        ];
        for($i = 0; $i < count($columns); $i++) {
            $this->values[] = $value;
        }
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereNot(string|\Closure $column, mixed $operator = "=", mixed $value = null, string $condition = "AND"): QueryBuilderContract
    {
        $numArgs = func_num_args();
        if($column instanceof \Closure && $numArgs === 1) {
            $subQuery = new static();
            $column($subQuery);
            $subSql = $subQuery->isWhereWrapped()->toSql();
            $this->conditions[] = [
                'type' => TypeWhere::WRAPPED,
                'sql' => "NOT ($subSql)",
                'condition' => $condition
            ];
            $this->values = array_merge($this->values, $subQuery->getValues());
        }
        if(is_string($column) && $numArgs === 2) {
            print("\nEntro en el dos params");
            $this->conditions[] = [
                'type' => TypeWhere::NORMAL,
                'column' => $column,
                'operator' => '=',
                'condition' => $condition
            ];
            $this->values[] = $operator;
        }
        if(is_string($column) && is_string($operator) && ($numArgs === 3 || $numArgs === 4)) {
            $this->conditions[] = [
                'type' => TypeWhere::NORMAL,
                'column' => $column,
                'operator' => $operator,
                'condition' => $condition
            ];
            $this->values[] = $value;
        }
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function delete(): string
    {
        if($this->table === null) {
            throw new QueryBuilderException("Can't use delete() if you don't select table with table()");
        }
        return "DELETE FROM {$this->table} " . $this->buildJoins() . $this->buildWheres();
    }

    /**
     * @inheritDoc
     */
    public function update(array $data): string
    {
        if($this->table === null) {
            throw new QueryBuilderException("Can't use update() if you don't select table with table()");
        }
        $columns = array_keys($data);
        $this->values = array_merge(array_values($data), $this->values);
        return rtrim("UPDATE {$this->table} SET " . implode(" = ?, ", $columns) . " = ? " . $this->buildWheres());
    }
    /**
     * @inheritDoc
     */
    public function distinct(): QueryBuilderContract
    {
        $this->useDistinct = true;
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function take(int $rows): QueryBuilderContract
    {
        $this->limit = $rows;
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function skip(int $rows): QueryBuilderContract
    {
        $this->offset = $rows;
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function groupBy(string|array $column): static
    {
        if(is_array($column)) {
            $this->groupBy = array_merge($this->groupBy, $column);
        } else {
            $this->groupBy[] = $column;
        }
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function orderBy(string $column, string $direction = 'ASC'): static
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => $direction
        ];
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function find(int|string $id): string
    {
        if($this->table === null) {
            throw new QueryBuilderException("Can't use first() if you don't select table with table()");
        }
        $this->where($this->primaryKey, $id);
        return $this->buildSelect() . $this->buildWheres();
    }
    public function getValues(): array
    {
        return [...$this->values];
    }
    private function isWhereWrapped()
    {
        $this->isWhereWrapped = true;
        return $this;
    }
    private function buildSelect(): string
    {
        $select = "SELECT ";
        $select .= $this->useDistinct ? " DISTINCT " : "";
        $select .= implode(',', $this->selectedColumns);
        $select .= " FROM {$this->table} ";
        return ltrim($select);
    }

    private function buildJoins(): string
    {
        if(empty($this->joins)) {
            return "";
        }
        $joins = " ";
        foreach($this->joins as $join) {
            $joins .= "{$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']} ";
        }
        $joins = ltrim($joins);
        return $joins;
    }
    private function buildWheres(): string
    {
        if(empty($this->conditions)) {
            return '';
        }
        $where = $this->isWhereWrapped ? "" : "WHERE";
        $this->conditions[0]['condition'] = '';
        foreach($this->conditions as $condition) {
            $where .= match($condition['type']) {
                TypeWhere::NORMAL => "{$condition['condition']} {$condition['column']} {$condition['operator']} ? ",
                TypeWhere::WRAPPED => "{$condition['condition']} {$condition['sql']} ",
                TypeWhere::NONE, TypeWhere::ALL, TypeWhere::ANY => $this->buildWhereAllOrAnyOrNone($condition),
                default => ""
            };
        }
        return $where;
    }
    public function buildWhereAllOrAnyOrNone(array $condition = []): string
    {
        // Inicializar la cláusula WHERE dependiendo del tipo
        $where = match($condition['type']) {
            TypeWhere::NONE => "{$condition['condition']} NOT (",
            TypeWhere::ALL, TypeWhere::ANY => "{$condition['condition']} (",
            default => ""
        };

        // Comprobar si hay columnas para evitar errores
        if (empty($condition['columns'])) {
            // Si no hay columnas, retornar una cláusula vacía (no válida)
            return $where . ')';
        }

        // Crear las cláusulas individuales
        $clauses = [];

        foreach ($condition['columns'] as $column) {
            // Agregar el operador y el placeholder
            $clauses[] = "$column {$condition['operator']} ?";
        }

        // Unir las cláusulas con AND para ALL, OR para ANY
        if ($condition['type'] === TypeWhere::ALL) {
            $where .= implode(' AND ', $clauses);
        } elseif ($condition['type'] === TypeWhere::ANY) {
            $where .= implode(' OR ', $clauses);
        } elseif ($condition['type'] === TypeWhere::NONE) {
            // Para NONE, se une con OR pero se agrega NOT al inicio
            $where .= implode(' OR ', $clauses);
        }

        $where .= ') '; // Cerrar el paréntesis

        return $where; // Retornar la cláusula construida
    }
    private function buildGroupBy(): string
    {
        if(empty($this->groupBy)) {
            return "";
        }
        $groupBy = "GROUP BY ";
        $groupBy .= implode(", ", $this->groupBy);
        return $groupBy . " ";
    }
    private function buildOrderBy(): string
    {
        if(empty($this->orderBy)) {
            return "";
        }
        $ordersBy = "ORDER BY ";
        foreach($this->orderBy as $orderBy) {
            $ordersBy .= "{$orderBy['column']} {$orderBy['direction']}";
        }
        return trim($ordersBy);
    }
    private function buildLimit(): string
    {
        if($this->limit === 0 && $this->offset === 0) {
            return "";
        }
        return ($this->limit > 0 ? " LIMIT {$this->limit}" : "") . ($this->offset > 0 ? " OFFSET {$this->offset}" : "");
    }
    /**
     * @inheritDoc
     */
    public function setPrimaryKey(?string $primaryKey): static
    {
        $this->primaryKey = $primaryKey;
        return $this;
    }
}

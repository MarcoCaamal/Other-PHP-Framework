<?php

namespace LightWeight\Database\QueryBuilder\Drivers;

use LightWeight\Database\QueryBuilder\Metadata\Column;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;

class MySQLQueryBuilder implements QueryBuilderContract
{
    public function __construct(protected DatabaseDriverContract $db)
    {
        $this->db = $db;
    }

    protected ?string $table = null;
    protected array $columns = ['*'];
    protected array $wheres = [];
    protected array $orderBy = [];
    protected ?int $limit = null;
    protected ?int $offset = null;
    protected array $joins = [];
    protected array $bindings = [];
    protected string $queryType = 'select';
    protected array $groups = [];
    protected array $havings = [];
    protected bool $distinct = false;
    protected array $unions = [];
    protected array $aggregate = [];

    // Helper methods
    protected function reset(): void
    {
        $this->columns = ['*'];
        $this->wheres = [];
        $this->orderBy = [];
        $this->limit = null;
        $this->offset = null;
        $this->joins = [];
        $this->bindings = [];
        $this->queryType = 'select';
        $this->groups = [];
        $this->havings = [];
        $this->distinct = false;
        $this->unions = [];
        $this->aggregate = [];
    }

    protected function compileSelect(): string
    {
        $sql = 'SELECT ';

        if ($this->distinct) {
            $sql .= 'DISTINCT ';
        }

        $sql .= implode(', ', $this->columns) . ' FROM ' . $this->table;

        // Joins
        foreach ($this->joins as $join) {
            $sql .= " {$join['type']} JOIN {$join['table']} ON {$join['first']} {$join['operator']} {$join['second']}";
        }

        // Wheres
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Añadir GROUP BY si existe
        if (!empty($this->groups)) {
            $sql .= ' GROUP BY ' . implode(', ', $this->groups);
        }

        // Añadir HAVING si existe
        if (!empty($this->havings)) {
            $sql .= ' HAVING ' . $this->compileHavings();
        }

        // Order by
        if (!empty($this->orderBy)) {
            $sql .= ' ORDER BY ';
            $orders = [];
            foreach ($this->orderBy as $order) {
                $orders[] = "{$order['column']} {$order['direction']}";
            }
            $sql .= implode(', ', $orders);
        }

        // Limit/Offset
        if ($this->limit !== null) {
            $sql .= " LIMIT {$this->limit}";

            if ($this->offset !== null) {
                $sql .= " OFFSET {$this->offset}";
            }
        }

        return $sql;
    }

    protected function compileWheres(): string
    {
        return $this->compileWheresRecursive($this->wheres);
    }

    protected function compileWheresRecursive(array $wheres, bool $isNested = false): string
    {
        $whereClauses = [];

        foreach ($wheres as $where) {
            $boolean = $where['boolean'] ?? 'AND';

            switch ($where['type']) {
                case 'basic':
                    $columnName = $this->quoteIdentifier($where['column']);
                    $whereClauses[] = "{$boolean} {$columnName} {$where['operator']} ?";
                    break;
                case 'in':
                    $placeholders = implode(', ', array_fill(0, count($where['values']), '?'));
                    $not = $where['not'] ? 'NOT ' : '';
                    $columnName = $this->quoteIdentifier($where['column']);
                    $whereClauses[] = "{$boolean} {$columnName} {$not} IN ({$placeholders})";
                    break;

                case 'null':
                    $notClause = $where['not'] ? 'IS NOT NULL' : 'IS NULL';
                    $columnName = $this->quoteIdentifier($where['column']);
                    $whereClauses[] = "{$boolean} {$columnName} {$notClause}";
                    break;

                case 'raw':
                    $whereClauses[] = "{$boolean} {$where['sql']}";
                    break;

                case 'group':
                    // Handle the nested group separately and correctly
                    $nestedClauses = $this->compileWheresRecursive($where['wheres'], true);
                    // Remove leading boolean operators from the nested query
                    $nestedClauses = ltrim($nestedClauses, 'AND ');
                    $nestedClauses = ltrim($nestedClauses, 'OR ');
                    
                    $whereClauses[] = "{$boolean} ({$nestedClauses})";
                    break;
                case 'between':
                    $not = $where['not'] ? 'NOT ' : '';
                    $columnName = $this->quoteIdentifier($where['column']);
                    $whereClauses[] = "{$boolean} {$columnName} {$not} BETWEEN ? AND ?";
                    break;
            }
        }

        // Remove the first boolean for top level
        if (!$isNested && !empty($whereClauses)) {
            $first = array_shift($whereClauses);
            $first = ltrim($first, 'AND ');
            $first = ltrim($first, 'OR ');
            array_unshift($whereClauses, $first);
        }

        return implode(' ', $whereClauses);
    }

    protected function compileHavings(): string
    {
        $havingClauses = [];

        foreach ($this->havings as $having) {
            $havingClauses[] = "{$having['boolean']} {$having['column']} {$having['operator']} ?";
        }

        // Remove the first boolean (AND/OR)
        if (!empty($havingClauses)) {
            $first = array_shift($havingClauses);
            $first = ltrim($first, 'AND ');
            $first = ltrim($first, 'OR ');
            array_unshift($havingClauses, $first);
        }

        return implode(' ', $havingClauses);
    }


    /**
     * @inheritDoc
     */
    public function delete(): bool
    {
        $this->queryType = 'delete';

        $sql = "DELETE FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Convertir los bindings de array asociativo a array indexado
        $bindingValues = array_values($this->bindings);
        
        $result = $this->db->execute($sql, $bindingValues);
        $this->reset();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function first(): array|null
    {
        $result = $this->limit(1)->get();
        $this->reset();
        return $result ? $result[0] : null;
    }

    /**
     * @inheritDoc
     */
    public function get(): array
    {
        $sql = $this->compileSelect();
        
        // Convertir los bindings de array asociativo a array indexado
        $bindingValues = array_values($this->bindings);
        
        $result = $this->db->statement($sql, $bindingValues);
        $this->reset();
        return $result;
    }

    /**
     * @inheritDoc
     */
    public function insert(array $data): bool
    {
        $this->queryType = 'insert';
        
        // Usar interrogaciones (?) en lugar de marcadores con nombre
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        
        $result = $this->db->execute($sql, array_values($data));
        $this->reset();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function join(string $table, string $first, string $operator, string $second, string $type = 'inner'): static
    {
        $this->joins[] = [
            'table' => $table,
            'first' => $first,
            'operator' => $operator,
            'second' => $second,
            'type' => $type
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function lastInsertId(): int|string
    {
        return $this->db->lastInsertId();
    }

    /**
     * @inheritDoc
     */
    public function leftJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'left');
    }

    /**
     * @inheritDoc
     */
    public function limit(int $limit): static
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function offset(int $offset): static
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orderBy(string $column, string $direction = 'asc'): static
    {
        $this->orderBy[] = [
            'column' => $column,
            'direction' => strtolower($direction) === 'asc' ? 'ASC' : 'DESC'
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function orWhere(string $column, string $operator, mixed $value): static
    {
        return $this->where($column, $operator, $value, 'OR');
    }

    /**
     * @inheritDoc
     */
    public function orWhereGroup(\Closure $callback): static
    {
        return $this->whereGroup($callback, 'OR');
    }

    /**
     * @inheritDoc
     */
    public function rightJoin(string $table, string $first, string $operator, string $second): static
    {
        return $this->join($table, $first, $operator, $second, 'right');
    }

    /**
     * @inheritDoc
     */
    public function select(array $columns = ['*']): static
    {
        $this->columns = $columns;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function table(string $table): static
    {
        $this->table = $table;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function update(array $data): bool
    {
        $this->queryType = 'update';
        
        $sets = [];
        $updateValues = [];
        
        foreach ($data as $column => $value) {
            $sets[] = "{$column} = ?";
            $updateValues[] = $value;
        }

        $sql = "UPDATE {$this->table} SET " . implode(', ', $sets);

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }
        
        // Combinar los valores del SET con los valores de WHERE
        $allValues = array_merge($updateValues, array_values($this->bindings));
        
        $result = $this->db->execute($sql, $allValues);
        $this->reset();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function where(string $column, string $operator, mixed $value, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        $this->bindings[] = $value;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereBetween(string $column, mixed $value1, mixed $value2, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'value1' => $value1,
            'value2' => $value2,
            'boolean' => $boolean,
            'not' => false
        ];

        $this->bindings[] = $value1;
        $this->bindings[] = $value2;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereGroup(\Closure $callback, string $boolean = 'AND'): static
    {
        // Guardar el estado actual de los wheres
        $currentWheres = $this->wheres;
        $this->wheres = [];

        // Ejecutar el callback para agregar condiciones al grupo
        $callback($this);
        
        // Guardar las condiciones del grupo
        $groupWheres = $this->wheres;
        
        // Restaurar las condiciones originales
        $this->wheres = $currentWheres;
        
        // Agregar el grupo de condiciones
        if (!empty($groupWheres)) {
            $this->wheres[] = [
                'type' => 'group',
                'wheres' => $groupWheres,
                'boolean' => $boolean
            ];
        }

        return $this;
    }
    /**
     * @inheritDoc
     */
    public function whereIn(string $column, array $values, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
            'not' => false
        ];

        foreach ($values as $value) {
            $this->bindings[] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereNotBetween(string $column, mixed $value1, mixed $value2, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'between',
            'column' => $column,
            'value1' => $value1,
            'value2' => $value2,
            'boolean' => $boolean,
            'not' => true
        ];

        $this->bindings[] = $value1;
        $this->bindings[] = $value2;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereNotIn(string $column, array $values, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => $values,
            'boolean' => $boolean,
            'not' => true
        ];

        foreach ($values as $value) {
            $this->bindings[] = $value;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereNotNull(string $column, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean,
            'not' => true
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereNull(string $column, string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'null',
            'column' => $column,
            'boolean' => $boolean,
            'not' => false
        ];
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function whereRaw(string $sql, array $bindings = [], string $boolean = 'AND'): static
    {
        $this->wheres[] = [
            'type' => 'raw',
            'sql' => $sql,
            'boolean' => $boolean,
            'bindings' => $bindings
        ];
        
        if (!empty($bindings)) {
            $this->bindings = array_merge($this->bindings, $bindings);
        }
        
        return $this;
    }
    /**
     * @inheritDoc
     */
    public function orWhereRaw(string $sql, array $bindings = []): static
    {
        return $this->whereRaw($sql, $bindings, 'OR');
    }

    /**
     * @inheritDoc
     */
    public function avg(string $column): float|int
    {
        $sql = "SELECT AVG({$column}) AS avg_result FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Convertir los bindings de array asociativo a array indexado
        $bindingValues = array_values($this->bindings);
        
        $result = $this->db->statement($sql, $bindingValues);

        return $result[0]['avg_result'] !== null ? (float)$result[0]['avg_result'] : 0;
    }

    /**
     * @inheritDoc
     */
    public function count(string $column = '*'): int
    {
        // Construimos la consulta COUNT directamente
        $sql = "SELECT COUNT({$column}) AS count_result FROM {$this->table}";

        // Añadimos condiciones WHERE si existen
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Convertir los bindings de array asociativo a array indexado
        $bindingValues = array_values($this->bindings);
        
        // Ejecutamos la consulta
        $result = $this->db->statement($sql, $bindingValues);

        return (int) ($result[0]['count_result'] ?? 0);
    }

    /**
     * @inheritDoc
     */
    public function decrement(string $column, int $value = 1): int
    {
        // Validación de parámetros
        if ($value <= 0) {
            throw new \InvalidArgumentException('Decrement value must be positive');
        }

        $this->queryType = 'update';

        // Construimos la sentencia SQL con placeholder de interrogación
        $sql = "UPDATE {$this->table} SET {$column} = {$column} - ?";
        
        // Valores para los placeholders
        $values = [$value];

        // Añadimos condiciones WHERE si existen
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Combinar los valores
        $allValues = array_merge($values, array_values($this->bindings));
        
        // Ejecutamos la consulta
        $affectedRows = $this->db->execute($sql, $allValues);

        $this->reset();

        return $affectedRows;
    }

    /**
     * @inheritDoc
     */
    public function distinct(): static
    {
        $this->distinct = true;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getBindings(): array
    {
        return $this->bindings;
    }

    /**
     * @inheritDoc
     */
    public function groupBy(array|string $columns): static
    {
        // Convertir string a array para manejo uniforme
        $columns = is_string($columns) ? [$columns] : $columns;

        // Validar que las columnas no estén vacías
        if (empty($columns)) {
            throw new \InvalidArgumentException('Group by columns cannot be empty');
        }

        // Añadir las columnas al agrupamiento
        foreach ($columns as $column) {
            if (!is_string($column)) {
                throw new \InvalidArgumentException('Group by column must be a string');
            }
            $this->groups[] = $column;
        }

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function having(string $column, string $operator, mixed $value, string $boolean = 'AND'): static
    {
        $this->havings[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
            'boolean' => $boolean
        ];

        $this->bindings[] = $value;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function increment(string $column, int $value = 1): int
    {
        // Validación de parámetros
        if ($value <= 0) {
            throw new \InvalidArgumentException('Increment value must be positive');
        }

        $this->queryType = 'update';

        // Construimos la sentencia SQL con placeholder de interrogación
        $sql = "UPDATE {$this->table} SET {$column} = {$column} + ?";
        
        // Valores para los placeholders
        $values = [$value];

        // Añadimos condiciones WHERE si existen
        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Combinar los valores
        $allValues = array_merge($values, array_values($this->bindings));
        
        // Ejecutamos la consulta
        $affectedRows = $this->db->execute($sql, $allValues);

        $this->reset();

        return $affectedRows;
    }

    /**
     * @inheritDoc
     */
    public function insertBatch(array $data): int
    {
        if (empty($data)) {
            return 0;
        }

        // Validar que todos los elementos tengan las mismas columnas
        $columns = array_keys($data[0]);
        foreach ($data as $row) {
            if (array_keys($row) !== $columns) {
                throw new \InvalidArgumentException('All rows must have the same columns');
            }
        }

        $this->queryType = 'insert';
        $placeholders = [];
        $values = [];
        $columnsStr = implode(', ', $columns);

        foreach ($data as $row) {
            $rowPlaceholders = array_fill(0, count($row), '?');
            $placeholders[] = '(' . implode(', ', $rowPlaceholders) . ')';
            
            // Agregar valores al array de valores
            foreach ($row as $value) {
                $values[] = $value;
            }
        }

        $sql = "INSERT INTO {$this->table} ({$columnsStr}) VALUES " . implode(', ', $placeholders);
        
        $affectedRows = $this->db->execute($sql, $values);
        $this->reset();

        return $affectedRows;
    }

    /**
     * @inheritDoc
     */
    public function insertOrUpdate(array $data): bool
    {
        $this->queryType = 'insert';

        $columns = array_keys($data);
        $columnsStr = implode(', ', $columns);

        // Valores a pasar a la consulta
        $values = [];
        
        // Preparar valores y placeholders para INSERT
        $insertPlaceholders = array_fill(0, count($data), '?');
        $valuesStr = implode(', ', $insertPlaceholders);
        
        // Agregar valores de INSERT al array de valores
        foreach ($data as $value) {
            $values[] = $value;
        }

        // Preparar valores para UPDATE
        $updates = [];
        foreach ($data as $column => $value) {
            $updates[] = "{$column} = ?";
            // Agregar valores de UPDATE al array de valores
            $values[] = $value;
        }
        $updatesStr = implode(', ', $updates);

        $sql = "INSERT INTO {$this->table} ({$columnsStr}) 
                VALUES ({$valuesStr})
                ON DUPLICATE KEY UPDATE {$updatesStr}";
        
        $result = $this->db->execute($sql, $values);
        $this->reset();

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function max(string $column): float|int
    {
        $sql = "SELECT MAX({$column}) AS max_result FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Convertir los bindings de array asociativo a array indexado
        $bindingValues = array_values($this->bindings);
        
        $result = $this->db->statement($sql, $bindingValues);

        return $result[0]['max_result'] !== null ? (float)$result[0]['max_result'] : 0;
    }

    /**
     * @inheritDoc
     */
    public function min(string $column): float|int
    {
        $sql = "SELECT MIN({$column}) AS min_result FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Convertir los bindings de array asociativo a array indexado
        $bindingValues = array_values($this->bindings);
        
        $result = $this->db->statement($sql, $bindingValues);

        return $result[0]['min_result'] !== null ? (float)$result[0]['min_result'] : 0;
    }

    /**
     * @inheritDoc
     */
    public function orHaving(string $column, string $operator, mixed $value): static
    {
        return $this->having($column, $operator, $value, 'OR');
    }

    /**
     * @inheritDoc
     */
    public function paginate(int $perPage = 15, int $page = 1): array
    {
        $page = max(1, $page);
        $offset = ($page - 1) * $perPage;

        $total = $this->count();
        $results = $this->limit($perPage)->offset($offset)->get();

        return [
            'data' => $results,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($total / $perPage)
        ];
    }

    /**
     * @inheritDoc
     */
    public function subQuery(\Closure $callback, string $alias): static
    {
        // Crear una nueva instancia del query builder con el mismo driver de base de datos
        $subQuery = new self($this->db);

        // Ejecutar el callback para construir la subconsulta
        $callback($subQuery);

        // Compilar la subconsulta y envolverla en paréntesis
        $subQuerySql = '(' . $subQuery->compileSelect() . ') AS ' . $alias;

        // Agregar la subconsulta como una columna
        $this->columns[] = $subQuerySql;

        // Agregar los bindings de la subconsulta a los bindings principales
        $this->bindings = array_merge($this->bindings, $subQuery->bindings);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function sum(string $column): float|int
    {
        $sql = "SELECT SUM({$column}) AS sum_result FROM {$this->table}";

        if (!empty($this->wheres)) {
            $sql .= ' WHERE ' . $this->compileWheres();
        }

        // Convertir los bindings de array asociativo a array indexado
        $bindingValues = array_values($this->bindings);
        
        $result = $this->db->statement($sql, $bindingValues);

        return $result[0]['sum_result'] !== null ? (float)$result[0]['sum_result'] : 0;
    }

    /**
     * @inheritDoc
     */
    public function toSql(): string
    {
        return $this->compileSelect();
    }
    /**
     * @inheritDoc
     */
    public function getMetadataOfTableColumns(): array
    {
        $columns = $this->db->statement("SHOW FULL COLUMNS FROM {$this->table}");
        $columnsMetadata = [];

        foreach($columns as $column) {
            $columnMetadata = new Column($column);
            $columnsMetadata[] = $columnMetadata;
        }

        return $columnsMetadata;
    }

    /**
     * Quote a column identifier to prevent SQL injection
     * and handle special characters in column names
     *
     * @param string $identifier
     * @return string
     */
    protected function quoteIdentifier(string $identifier): string
    {
        // Don't quote if it's already a valid MySQL identifier
        if (preg_match('/^[a-zA-Z0-9_]+$/', $identifier)) {
            return $identifier;
        }
        
        // If it contains a dot, we need to quote the parts separately
        if (strpos($identifier, '.') !== false) {
            $parts = array_map(function($part) {
                return '`' . str_replace('`', '``', $part) . '`';
            }, explode('.', $identifier));
            return implode('.', $parts);
        }
        
        // Simple case - just quote the identifier
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    /**
     * Get the current table name.
     *
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * Get the where clause for the current query.
     * Used to extract constraints in subqueries for relationship queries.
     *
     * @return string
     */
    public function getWhereClause(): string
    {
        if (empty($this->wheres)) {
            return '';
        }
        
        return $this->compileWheres();
    }
}

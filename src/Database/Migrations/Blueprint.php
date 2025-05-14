<?php

namespace LightWeight\Database\Migrations;

use LightWeight\Database\Migrations\ForeignKeyDefinition;

/**
 * Blueprint for building database tables
 */
class Blueprint
{
    /**
     * The table the blueprint describes
     *
     * @var string
     */
    protected string $table;
    
    /**
     * The commands that should be run for the table
     *
     * @var array
     */
    protected array $commands = [];
    
    /**
     * The columns that should be added to the table
     *
     * @var array
     */
    protected array $columns = [];
    
    /**
     * The storage engine that should be used for the table
     *
     * @var string
     */
    protected string $engine = 'innodb';
    
    /**
     * The default character set that should be used for the table
     */
    protected ?string $charset = null;
    
    /**
     * The collation that should be used for the table
     */
    protected ?string $collation = null;
    
    /**
     * The type of blueprint (create or alter)
     */
    protected string $type;
    
    /**
     * Create a new blueprint instance
     *
     * @param string $table
     * @param string $type
     * @return void
     */
    public function __construct(string $table, string $type = 'create')
    {
        $this->table = $table;
        $this->type = $type;
    }
    
    /**
     * Add a new column to the blueprint
     *
     * @param string $type Column type
     * @param string $name Column name
     * @param array $parameters Additional parameters
     * @return $this
     */
    protected function addColumn(string $type, string $name, array $parameters = []): self
    {
        $this->columns[] = [
            'name' => $name,
            'type' => $type,
            'parameters' => $parameters,
            'nullable' => false,
            'default' => null,
            'unique' => false,
            'primary' => false,
            'autoIncrement' => false,
        ];

        // If the column is nullable, set default to NULL
        $lastIndex = count($this->columns) - 1;
        if ($this->columns[$lastIndex]['nullable']) {
            $this->columns[$lastIndex]['default'] = null;
        }

        return $this;
    }
    
    /**
     * Check if the blueprint has any commands
     *
     * @return bool
     */
    public function hasCommands(): bool
    {
        return !empty($this->commands) || !empty($this->columns);
    }
    
    /**
     * Create an auto-incrementing integer ID column
     *
     * @param string $column
     * @return $this
     */
    public function id(string $column = 'id'): self
    {
        $this->addColumn('int', $column);
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['autoIncrement'] = true;
        $this->columns[$index]['primary'] = true;
        
        return $this;
    }
    
    /**
     * Create a string column
     *
     * @param string $column
     * @param int $length
     * @return $this
     */
    public function string(string $column, int $length = 255): self
    {
        return $this->addColumn('varchar', $column, ['length' => $length]);
    }
    
    /**
     * Create an integer column
     *
     * @param string $column
     * @return $this
     */
    public function integer(string $column): self
    {
        return $this->addColumn('int', $column);
    }
    
    /**
     * Create a boolean column
     *
     * @param string $column
     * @return $this
     */
    public function boolean(string $column): self
    {
        return $this->addColumn('tinyint', $column, ['length' => 1]);
    }
    
    /**
     * Create a text column
     *
     * @param string $column
     * @return $this
     */
    public function text(string $column): self
    {
        return $this->addColumn('text', $column);
    }
    
    /**
     * Create a decimal column
     *
     * @param string $column
     * @param int $precision
     * @param int $scale
     * @return $this
     */
    public function decimal(string $column, int $precision = 8, int $scale = 2): self
    {
        return $this->addColumn('decimal', $column, [
            'precision' => $precision,
            'scale' => $scale
        ]);
    }
    
    /**
     * Create a timestamp column
     *
     * @param string $column
     * @return $this
     */
    public function timestamp(string $column): self
    {
        return $this->addColumn('timestamp', $column);
    }
    
    /**
     * Create a datetime column
     *
     * @param string $column
     * @return $this
     */
    public function datetime(string $column): self
    {
        return $this->addColumn('datetime', $column);
    }
    
    /**
     * Create a date column
     *
     * @param string $column
     * @return $this
     */
    public function date(string $column): self
    {
        return $this->addColumn('date', $column);
    }
    
    /**
     * Create an enum column
     *
     * @param string $column
     * @param array $values
     * @return $this
     */
    public function enum(string $column, array $values): self
    {
        return $this->addColumn('enum', $column, ['values' => $values]);
    }
    
    /**
     * Set the default value for the last column
     *
     * @param mixed $value
     * @return $this
     */
    public function default($value): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['default'] = $value;
        
        return $this;
    }
    
    /**
     * Make the last column nullable
     *
     * @param bool $value
     * @return $this
     */
    public function nullable(bool $value = true): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['nullable'] = $value;

        // If the column is nullable, set default to NULL
        if ($value) {
            $this->columns[$index]['default'] = null;
        }

        return $this;
    }
    
    /**
     * Make the last column unique
     *
     * @return $this
     */
    public function unique(): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['unique'] = true;
        
        return $this;
    }
    
    /**
     * Make the last column unsigned (for integer columns)
     *
     * @return $this
     */
    public function unsigned(): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['parameters']['unsigned'] = true;
        
        return $this;
    }
    
    /**
     * Set the column to auto-increment
     *
     * @return $this
     */
    public function autoIncrement(): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['autoIncrement'] = true;
        
        // An auto-increment column must be a key, so make it a primary key if not already
        // set as a key through other means
        if (!isset($this->columns[$index]['parameters']['primary']) || $this->columns[$index]['parameters']['primary'] !== true) {
            $this->columns[$index]['parameters']['primary'] = true;
        }
        
        return $this;
    }
    
    /**
     * Add a comment to the column
     *
     * @param string $comment
     * @return $this
     */
    public function comment(string $comment): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['parameters']['comment'] = $comment;
        
        return $this;
    }
    
    /**
     * Set the charset for the column
     *
     * @param string $charset
     * @return $this
     */
    public function columnCharset(string $charset): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['parameters']['charset'] = $charset;
        
        return $this;
    }
    
    /**
     * Set the collation for the column
     *
     * @param string $collation
     * @return $this
     */
    public function columnCollation(string $collation): self
    {
        if (empty($this->columns)) {
            return $this;
        }
        
        $index = count($this->columns) - 1;
        $this->columns[$index]['parameters']['collation'] = $collation;
        
        return $this;
    }
    
    /**
     * Add timestamps (created_at, updated_at) columns
     *
     * @return $this
     */
    public function timestamps(): self
    {
        $this->datetime('created_at');
        $this->datetime('updated_at')->nullable();
        
        return $this;
    }
    
    /**
     * Add a foreign key constraint
     *
     * @param string|array $columns Column(s) that reference the foreign key
     * @return \LightWeight\Database\Migrations\ForeignKeyDefinition
     */
    public function foreign($columns): ForeignKeyDefinition
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
        return new ForeignKeyDefinition($this, $columns);
    }
    
    /**
     * Add a foreign key command to the blueprint
     *
     * @param array $columns
     * @param string $table
     * @param array $foreignColumns
     * @return $this
     */
    public function addForeignKeyCommand(array $columns, string $table, array $foreignColumns): self
    {
        $this->commands[] = [
            'type' => 'foreign',
            'columns' => $columns,
            'table' => $table,
            'foreignColumns' => $foreignColumns,
        ];
        
        return $this;
    }
    
    /**
     * Add a dropColumn command
     *
     * @param string|array $columns
     * @return $this
     */
    public function dropColumn($columns): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        
        foreach ($columns as $column) {
            $this->commands[] = [
                'type' => 'drop_column',
                'column' => $column
            ];
        }
        
        return $this;
    }
    
    /**
     * Add a renameColumn command
     *
     * @param string $from Current column name
     * @param string $to New column name
     * @param string|null $type Optional column type (VARCHAR, INT, etc.)
     * @param array $options Optional additional options
     * @return $this
     */
    public function renameColumn(string $from, string $to, ?string $type = null, array $options = []): self
    {
        // Procesar tipo y opciones específicas
        $columnType = $type;
        if ($type === 'DECIMAL' || $type === 'decimal') {
            $precision = $options['precision'] ?? 10;
            $scale = $options['scale'] ?? 2;
            $columnType = "DECIMAL($precision,$scale)";
        }
        
        $this->commands[] = [
            'type' => 'rename_column',
            'from' => $from,
            'to' => $to,
            'column_type' => $columnType,
            'options' => $options
        ];
        
        return $this;
    }

    /**
     * Add an index to the table
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function index($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: $this->createIndexName('index', $columns);
        
        $this->commands[] = [
            'type' => 'index',
            'columns' => $columns,
            'name' => $name
        ];
        
        return $this;
    }
    
    /**
     * Add a primary key to the table
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function primary($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: $this->createIndexName('primary', $columns);
        
        $this->commands[] = [
            'type' => 'primary',
            'columns' => $columns,
            'name' => $name
        ];
        
        return $this;
    }
    
    /**
     * Add a unique index to the table
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function uniqueIndex($columns, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: $this->createIndexName('unique', $columns);
        
        $this->commands[] = [
            'type' => 'unique',
            'columns' => $columns,
            'name' => $name
        ];
        
        return $this;
    }
    
    /**
     * Create a default index name for the table
     *
     * @param string $type
     * @param array $columns
     * @return string
     */
    protected function createIndexName(string $type, array $columns): string
    {
        return strtolower($this->table . '_' . implode('_', $columns) . '_' . $type);
    }
    
    /**
     * Drop an index from the table
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function dropIndex($columns = null, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: $this->createIndexName('index', $columns);
        
        $this->commands[] = [
            'type' => 'drop_index',
            'name' => $name
        ];
        
        return $this;
    }
    
    /**
     * Drop a primary key from the table
     *
     * @return $this
     */
    public function dropPrimary(?string $name = null): self
    {
        $name = $name ?: "{$this->table}_pkey";
        
        $this->commands[] = [
            'type' => 'drop_primary',
            'name' => $name
        ];
        
        return $this;
    }
    
    /**
     * Drop a unique index from the table
     *
     * @param string|array $columns
     * @param string|null $name
     * @return $this
     */
    public function dropUnique($columns = null, ?string $name = null): self
    {
        $columns = is_array($columns) ? $columns : [$columns];
        $name = $name ?: $this->createIndexName('unique', $columns);
        
        $this->commands[] = [
            'type' => 'drop_unique',
            'name' => $name
        ];
        
        return $this;
    }
    
    /**
     * Rename an index
     *
     * @param string $from Current index name
     * @param string $to New index name
     * @return $this
     */
    public function renameIndex(string $from, string $to): self
    {
        $this->commands[] = [
            'type' => 'rename_index',
            'from' => $from,
            'to' => $to
        ];
        
        return $this;
    }
    
    /**
     * Modify a column (change its type, constraints, etc.)
     *
     * @param string $column Column to modify
     * @param string $type New column type
     * @param array $parameters Additional parameters
     * @return $this
     */
    public function change(string $column, string $type, array $parameters = []): self
    {
        $options = [
            'nullable' => $parameters['nullable'] ?? false,
            'default' => $parameters['default'] ?? null,
        ];

        $this->commands[] = [
            'type' => 'change_column',
            'column' => $column,
            'new_type' => $type,
            'parameters' => $parameters,
            'options' => $options
        ];
        
        return $this;
    }
    
    /**
     * Change column to allow NULL values
     *
     * @param string $column Column name
     * @return $this
     */
    public function changeToNullable(string $column): self
    {
        return $this->change($column, 'current', ['nullable' => true]);
    }
    
    /**
     * Change column to NOT NULL
     *
     * @param string $column Column name
     * @return $this
     */
    public function changeToNotNull(string $column): self
    {
        return $this->change($column, 'current', ['nullable' => false]);
    }
    
    /**
     * Generate the SQL for this blueprint
     *
     * @return string
     */
    public function toSql(): string
    {
        if ($this->type === 'create') {
            return $this->compileCreate();
        }
        
        return $this->compileAlter();
    }
    
    /**
     * Compile the create table SQL
     *
     * @return string
     */
    protected function compileCreate(): string
    {
        $columnDefinitions = [];
        
        foreach ($this->columns as $column) {
            $columnDefinitions[] = $this->compileColumn($column);
        }
        
        // Add any constraints and indexes
        foreach ($this->commands as $command) {
            if ($command['type'] === 'foreign') {
                $columnDefinitions[] = $this->compileForeignKey($command);
            } elseif ($command['type'] === 'primary') {
                $columnDefinitions[] = $this->compilePrimaryKey($command);
            } elseif ($command['type'] === 'unique') {
                $columnDefinitions[] = $this->compileUniqueIndex($command);
            } elseif ($command['type'] === 'index') {
                $columnDefinitions[] = $this->compileIndex($command);
            }
        }
        
        $sql = "CREATE TABLE {$this->table} (\n    " . 
               implode(",\n    ", $columnDefinitions) . 
               "\n)";
        
        // Add engine
        if ($this->engine) {
            $sql .= " ENGINE={$this->engine}";
        }
        
        // Add charset and collation
        if ($this->charset) {
            $sql .= " DEFAULT CHARACTER SET {$this->charset}";
            
            if ($this->collation) {
                $sql .= " COLLATE {$this->collation}";
            }
        }
        
        return $sql;
    }
    
    /**
     * Compile the alter table SQL
     *
     * @return string
     */
    protected function compileAlter(): string
    {
        $commands = [];
        
        // Add columns
        foreach ($this->columns as $column) {
            $commands[] = "ADD COLUMN " . $this->compileColumn($column);
        }
        
        // Add any constraints or other commands
        foreach ($this->commands as $command) {
            if ($command['type'] === 'foreign') {
                $commands[] = "ADD " . $this->compileForeignKey($command);
            } elseif ($command['type'] === 'drop_column') {
                $commands[] = "DROP COLUMN `{$command['column']}`";
            } elseif ($command['type'] === 'primary') {
                $columns = implode(', ', array_map(fn($col) => "`$col`", $command['columns']));
                $commands[] = "ADD PRIMARY KEY ($columns)";
            } elseif ($command['type'] === 'unique') {
                $columns = implode(', ', array_map(fn($col) => "`$col`", $command['columns']));
                $commands[] = "ADD UNIQUE KEY `{$command['name']}` ($columns)";
            } elseif ($command['type'] === 'index') {
                $columns = implode(', ', array_map(fn($col) => "`$col`", $command['columns']));
                $commands[] = "ADD INDEX `{$command['name']}` ($columns)";
            } elseif ($command['type'] === 'drop_index') {
                $commands[] = "DROP INDEX `{$command['name']}`";
            } elseif ($command['type'] === 'drop_primary') {
                $commands[] = "DROP PRIMARY KEY";
            } elseif ($command['type'] === 'drop_unique') {
                $commands[] = "DROP INDEX `{$command['name']}`";
            } elseif ($command['type'] === 'rename_column') {
                $columnType = $command['column_type'] ?? 'VARCHAR(255)';
                $columnType = strtoupper($columnType);
                
                // Procesar opciones adicionales para el tipo (como para DECIMAL)
                if (!empty($command['options'])) {
                    if ($columnType === 'DECIMAL' && isset($command['options']['precision'], $command['options']['scale'])) {
                        $precision = $command['options']['precision'];
                        $scale = $command['options']['scale'];
                        $columnType .= "($precision,$scale)";
                    }
                }
                
                $commands[] = "CHANGE COLUMN `{$command['from']}` `{$command['to']}` $columnType";
            } elseif ($command['type'] === 'rename_index') {
                $commands[] = "RENAME INDEX `{$command['from']}` TO `{$command['to']}`";
            } elseif ($command['type'] === 'change_column') {
                $type = strtoupper($command['new_type']);
                
                // Manejar parámetros adicionales para el tipo de columna
                if (!empty($command['parameters'])) {
                    switch ($type) {
                        case 'VARCHAR':
                            $length = $command['parameters']['length'] ?? 255;
                            $type .= "($length)";
                            break;
                        case 'DECIMAL':
                            $precision = $command['parameters']['precision'] ?? 8;
                            $scale = $command['parameters']['scale'] ?? 2;
                            $type .= "($precision,$scale)";
                            break;
                        case 'ENUM':
                            $values = array_map(
                                fn($val) => "'$val'", 
                                $command['parameters']['values'] ?? []
                            );
                            $type .= "(" . implode(', ', $values) . ")";
                            break;
                    }
                }
                
                $commands[] = "MODIFY COLUMN `{$command['column']}` $type";
            }
        }
        
        if (empty($commands)) {
            return "";
        }
        
        return "ALTER TABLE {$this->table}\n    " . implode(",\n    ", $commands);
    }
    
    /**
     * Compile a column definition
     *
     * @param array $column
     * @return string
     */
    protected function compileColumn(array $column): string
    {
        $sql = "`{$column['name']}` " . $this->compileType($column);
        
        // Add nullable
        if (!$column['nullable']) {
            $sql .= " NOT NULL";
        } else {
            $sql .= " NULL";
        }
        
        // Add default value
        if ($column['default'] !== null) {
            $sql .= " DEFAULT " . $this->quoteDefaultValue($column['default']);
        } else if ($column['nullable']) {
            $sql .= " DEFAULT NULL";
        }
        
        // Add auto increment
        if ($column['autoIncrement'] ?? false) {
            $sql .= " AUTO_INCREMENT";
        }
        
        // Add primary key
        if ($column['primary'] ?? false) {
            $sql .= " PRIMARY KEY";
        }
        
        // Add unique constraint
        if ($column['unique']) {
            $sql .= " UNIQUE";
        }
        
        // Add comment if specified
        if (isset($column['parameters']['comment'])) {
            $sql .= " COMMENT '" . str_replace("'", "''", $column['parameters']['comment']) . "'";
        }
        
        return $sql;
    }
    
    /**
     * Compile a column type definition
     *
     * @param array $column
     * @return string
     */
    protected function compileType(array $column): string
    {
        $parameters = $column['parameters'] ?? [];
        $type = '';
        
        switch ($column['type']) {
            case 'varchar':
                $length = $parameters['length'] ?? 255;
                $type = "VARCHAR($length)";
                break;
            
            case 'int':
                $type = "INT";
                break;
            
            case 'tinyint':
                $length = $parameters['length'] ?? 1;
                $type = "TINYINT($length)";
                break;
            
            case 'decimal':
                $precision = $parameters['precision'] ?? 8;
                $scale = $parameters['scale'] ?? 2;
                $type = "DECIMAL($precision,$scale)";
                break;
            
            case 'enum':
                $values = array_map(fn($val) => "'$val'", $parameters['values'] ?? []);
                $type = "ENUM(" . implode(', ', $values) . ")";
                break;
                
            default:
                $type = strtoupper($column['type']);
                break;
        }
        
        // Add unsigned modifier if specified
        if (isset($parameters['unsigned']) && $parameters['unsigned'] === true) {
            $type .= ' UNSIGNED';
        }
        
        return $type;
    }
    
    /**
     * Quote a default value for use in a SQL statement
     *
     * @param mixed $value
     * @return string
     */
    protected function quoteDefaultValue($value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        
        if (is_numeric($value)) {
            return (string) $value;
        }
        
        return "'" . str_replace("'", "''", $value) . "'";
    }
    
    /**
     * Compile a foreign key constraint
     *
     * @param array $command
     * @return string
     */
    protected function compileForeignKey(array $command): string
    {
        $columns = implode(', ', array_map(fn($col) => "`$col`", $command['columns']));
        $foreignColumns = implode(', ', array_map(fn($col) => "`$col`", $command['foreignColumns']));
        
        return "CONSTRAINT `fk_{$this->table}_{$command['table']}_" . implode('_', $command['columns']) . "` " .
               "FOREIGN KEY ($columns) " .
               "REFERENCES {$command['table']}($foreignColumns)";
    }
    
    /**
     * Compile a primary key constraint
     *
     * @param array $command
     * @return string
     */
    protected function compilePrimaryKey(array $command): string
    {
        $columns = implode(', ', array_map(fn($col) => "`$col`", $command['columns']));
        return "PRIMARY KEY ($columns)";
    }
    
    /**
     * Compile a unique index constraint
     *
     * @param array $command
     * @return string
     */
    protected function compileUniqueIndex(array $command): string
    {
        $columns = implode(', ', array_map(fn($col) => "`$col`", $command['columns']));
        return "UNIQUE KEY `{$command['name']}` ($columns)";
    }
    
    /**
     * Compile a standard index
     *
     * @param array $command
     * @return string
     */
    protected function compileIndex(array $command): string
    {
        $columns = implode(', ', array_map(fn($col) => "`$col`", $command['columns']));
        return "INDEX `{$command['name']}` ($columns)";
    }
    
    /**
     * Set the storage engine for the table
     *
     * @param string $engine
     * @return $this
     */
    public function engine(string $engine): self
    {
        $this->engine = $engine;
        return $this;
    }
    
    /**
     * Set the character set for the table
     *
     * @param string $charset
     * @return $this
     */
    public function charset(string $charset): self
    {
        $this->charset = $charset;
        return $this;
    }
    
    /**
     * Set the collation for the table
     *
     * @param string $collation
     * @return $this
     */
    public function collation(string $collation): self
    {
        $this->collation = $collation;
        return $this;
    }
}

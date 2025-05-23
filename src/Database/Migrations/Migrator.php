<?php

namespace LightWeight\Database\Migrations;

use LightWeight\Database\Contracts\DatabaseDriverContract;
use Symfony\Component\Console\Output\ConsoleOutput;

class Migrator
{
    private ConsoleOutput $output;
    public function __construct(
        private string $migrationsDirectory,
        private ?string $templatesDirectory = null,
        private ?DatabaseDriverContract $driver = null,
        private bool $logProgress = true
    ) {
        $this->migrationsDirectory = $migrationsDirectory;
        $this->templatesDirectory = $templatesDirectory ?? $this->getDefaultTemplatesPath();
        $this->driver = $driver;
        $this->logProgress = $logProgress;
        $this->output = new ConsoleOutput();
    }

    /**
     * Get the default templates directory path
     *
     * @return string
     */
    private function getDefaultTemplatesPath(): string
    {
        return dirname(dirname(dirname(__DIR__))) . '/templates';
    }

    private function log(string $message)
    {
        if ($this->logProgress) {
            print($message . PHP_EOL);
        }
    }
    public function createMigrationsTableIfNotExists()
    {
        $this->driver->statement("CREATE TABLE IF NOT EXISTS migrations (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(256))ENGINE=innodb");
    }
    public function migrate()
    {
        $this->createMigrationsTableIfNotExists();
        $migrated = $this->driver->statement("SELECT * FROM migrations");
        $migrations = glob("$this->migrationsDirectory/*.php");
        if (count($migrated) >= count($migrations)) {
            $this->log("<comment>Nothing to migrate</comment>");
            return;
        }
        foreach (array_slice($migrations, count($migrated)) as $file) {
            $migration = require $file;
            $migration->up();
            $name = basename($file);
            $this->driver->statement("INSERT INTO migrations (name) VALUES (?)", [$name]);
            $this->log("<comment>Migrated => $name</comment>");
        }
    }
    public function rollback(?int $steps = null)
    {
        $this->createMigrationsTableIfNotExists();
        $migrated = $this->driver->statement("SELECT * FROM migrations");
        $pending = count($migrated);
        if ($pending == 0) {
            $this->log("Nothing to rollback");
            return;
        }
        if (is_null($steps) || $steps > $pending) {
            $steps = $pending;
        }
        $migrations = array_slice(array_reverse(glob("$this->migrationsDirectory/*.php")), -$pending);
        foreach ($migrations as $file) {
            $migration = require $file;
            $migration->down();
            $name = basename($file);
            $this->driver->statement("DELETE FROM migrations WHERE name = ?", [$name]);
            $this->log("Rollback => $name");
            if (--$steps == 0) {
                break;
            }
        }
    }
    /**
     * Create a new migration file
     *
     * @param string $migrationName The name of the migration (e.g. create_users_table)
     * @param array $options Additional options for the migration
     * @return string The name of the created migration file
     */
    public function make(string $migrationName, array $options = [])
    {
        $migrationName = snakeCase($migrationName);
        $template = file_get_contents("$this->templatesDirectory/migration.template");

        // Extract table name from migration name if not provided
        $table = $options['table'] ?? null;

        if (preg_match("/create_.*_table/", $migrationName)) {
            // Create table migration
            if (!$table) {
                $table = preg_replace_callback("/create_(.*)_table/", fn ($match) => $match[1], $migrationName);
            }

            $upStatement = $this->generateCreateTableStatement($table, $options['fields'] ?? []);
            $downStatement = "Schema::dropIfExists('$table');";

            $template = str_replace('UP_STATEMENT_PLACEHOLDER', $upStatement, $template);
            $template = str_replace('DOWN_STATEMENT_PLACEHOLDER', $downStatement, $template);
        } elseif (preg_match("/add_(.*)_to_(.*)_table/", $migrationName, $matches)) {
            // Add columns migration
            if (!$table) {
                $table = $matches[2];
            }

            // Extract field name from migration name if not provided
            $fieldName = $matches[1];
            $fieldType = 'string'; // Default type

            // Create fields array if none provided
            if (empty($options['fields'])) {
                $options['fields'] = [
                    [
                        'name' => $fieldName,
                        'type' => $fieldType
                    ]
                ];
            }

            $upStatement = $this->generateAlterTableAddStatement($table, $options['fields'] ?? []);
            $downStatement = $this->generateAlterTableDropStatement($table, $options['fields'] ?? []);

            $template = str_replace('UP_STATEMENT_PLACEHOLDER', $upStatement, $template);
            $template = str_replace('DOWN_STATEMENT_PLACEHOLDER', $downStatement, $template);
        } elseif (preg_match("/remove_(.*)_from_(.*)_table/", $migrationName, $matches)) {
            // Remove column migration
            if (!$table) {
                $table = $matches[2];
            }

            // Extract field name from migration name
            $fieldName = $matches[1];

            // Create fields array if none provided
            if (empty($options['fields'])) {
                $options['fields'] = [
                    [
                        'name' => $fieldName,
                        'type' => 'string' // Asumimos string para el caso down
                    ]
                ];
            }

            // Para eliminar columna, intercambiamos up/down, ya que up elimina y down restaura
            $upStatement = "Schema::table('$table', function (Blueprint \$table) {\n" .
                         "            \$table->dropColumn('$fieldName');\n" .
                         "        });";

            $downStatement = "Schema::table('$table', function (Blueprint \$table) {\n" .
                            "            \$table->string('$fieldName');\n" .
                            "        });";

            $template = str_replace('UP_STATEMENT_PLACEHOLDER', $upStatement, $template);
            $template = str_replace('DOWN_STATEMENT_PLACEHOLDER', $downStatement, $template);
        } elseif (preg_match("/.*(from|to)_(.*)_table/", $migrationName)) {
            // Generic alter table migration
            if (!$table) {
                $table = preg_replace_callback("/.*(from|to)_(.*)_table/", fn ($match) => $match[2], $migrationName);
            }

            $upStatement = "Schema::table('$table', function (Blueprint \$table) {\n" .
                         "            // Add your migration code here\n" .
                         "        });";

            $downStatement = "Schema::table('$table', function (Blueprint \$table) {\n" .
                           "            // Add your rollback code here\n" .
                           "        });";

            $template = str_replace('UP_STATEMENT_PLACEHOLDER', $upStatement, $template);
            $template = str_replace('DOWN_STATEMENT_PLACEHOLDER', $downStatement, $template);
        } else {
            // Custom migration
            $template = str_replace('UP_STATEMENT_PLACEHOLDER', "// Add your custom migration logic here", $template);
            $template = str_replace('DOWN_STATEMENT_PLACEHOLDER', "// Add your rollback logic here", $template);
        }

        // Create migration file
        $date = date("Y_m_d");
        $id = 0;

        foreach (glob("$this->migrationsDirectory/*.php") as $file) {
            if (str_starts_with(basename($file), $date)) {
                $id++;
            }
        }

        $fileName = sprintf("%s_%06d_%s.php", $date, $id, $migrationName);
        file_put_contents("$this->migrationsDirectory/$fileName", $template);

        $this->log("Created migration => $fileName");
        return $fileName;
    }

    /**
     * Generate SQL statement for creating a table
     *
     * @param string $table The table name
     * @param array $fields Field definitions
     * @return string SQL statement
     */
    protected function generateCreateTableStatement(string $table, array $fields): string
    {
        // If no fields are specified, generate a minimal Schema call with timestamps
        if (empty($fields)) {
            return "Schema::create('$table', function (Blueprint \$table) {\n" .
                   "            \$table->id();\n" .
                   "            \$table->timestamps();\n" .
                   "        });";
        }

        // Generate Schema call with fields
        $schemaCode = "Schema::create('$table', function (Blueprint \$table) {\n";

        // Check if we need an ID
        $hasId = false;
        foreach ($fields as $field) {
            if ($field['name'] === 'id' || $field['type'] === 'id') {
                $hasId = true;
                break;
            }
        }

        // Add ID if needed
        if (!$hasId) {
            $schemaCode .= "            \$table->id();\n";
        }

        // Add each field
        foreach ($fields as $field) {
            $schemaCode .= "            " . $this->generateColumnMethod($field) . ";\n";
        }

        $schemaCode .= "        });";

        return $schemaCode;
    }

    /**
     * Generate SQL statement for adding columns to a table
     *
     * @param string $table The table name
     * @param array $fields Field definitions
     * @return string SQL statement
     */
    protected function generateAlterTableAddStatement(string $table, array $fields): string
    {
        if (empty($fields)) {
            return "Schema::table('$table', function (Blueprint \$table) {\n" .
                   "            \$table->string('example_column');\n" .
                   "        });";
        }

        $schemaCode = "Schema::table('$table', function (Blueprint \$table) {\n";

        foreach ($fields as $field) {
            $schemaCode .= "            " . $this->generateColumnMethod($field) . ";\n";
        }

        $schemaCode .= "        });";

        return $schemaCode;
    }

    /**
     * Generate SQL statement for dropping columns from a table
     *
     * @param string $table The table name
     * @param array $fields Field definitions
     * @return string SQL statement
     */
    protected function generateAlterTableDropStatement(string $table, array $fields): string
    {
        if (empty($fields)) {
            return "Schema::table('$table', function (Blueprint \$table) {\n" .
                   "            \$table->dropColumn('example_column');\n" .
                   "        });";
        }

        $schemaCode = "Schema::table('$table', function (Blueprint \$table) {\n";

        foreach ($fields as $field) {
            $schemaCode .= "            \$table->dropColumn('{$field['name']}');\n";
        }

        $schemaCode .= "        });";

        return $schemaCode;
    }

    /**
     * Generate a column definition from a field specification
     *
     * @param array $field Field definition
     * @return string Column definition SQL
     */
    protected function generateColumnDefinition(array $field): string
    {
        $name = $field['name'];
        $type = strtolower($field['type']);
        $parameters = $field['parameters'] ?? [];

        // Map friendly types to SQL types
        $columnType = match($type) {
            'id' => "INT AUTO_INCREMENT PRIMARY KEY",
            'string' => isset($parameters[0]) ? "VARCHAR($parameters[0])" : "VARCHAR(255)",
            'integer', 'int' => "INT",
            'decimal', 'float' => isset($parameters[0]) && isset($parameters[1])
                ? "DECIMAL($parameters[0],$parameters[1])"
                : "DECIMAL(10,2)",
            'boolean', 'bool' => "TINYINT(1)",
            'text' => "TEXT",
            'date' => "DATE",
            'datetime' => "DATETIME",
            'timestamp' => "TIMESTAMP",
            'enum' => "ENUM(" . implode(', ', array_map(fn ($val) => "'$val'", $parameters)) . ")",
            default => $type // Use as is if it's a native SQL type
        };

        return "$name $columnType";
    }

    /**
     * Generate a Blueprint column method call from a field specification
     *
     * @param array $field Field definition
     * @return string Blueprint method call
     */
    protected function generateColumnMethod(array $field): string
    {
        $name = $field['name'];
        $type = strtolower($field['type']);
        $parameters = $field['parameters'] ?? [];

        // Special case for ID
        if ($type === 'id' && $name === 'id') {
            return '$table->id()';
        }

        // Maps field types to Blueprint methods
        $method = match($type) {
            'id' => 'integer',
            'string' => 'string',
            'integer', 'int' => 'integer',
            'decimal', 'float' => 'decimal',
            'boolean', 'bool' => 'boolean',
            'text' => 'text',
            'date' => 'date',
            'datetime' => 'datetime',
            'timestamp' => 'timestamp',
            'enum' => 'enum',
            default => 'string' // Default to string for unknown types
        };

        // Start building the method call
        $methodCall = "\$table->$method('$name'";

        // Add parameters if needed
        if ($type === 'string' && isset($parameters[0])) {
            $methodCall .= ", " . $parameters[0];
        } elseif ($type === 'decimal' || $type === 'float') {
            $precision = $parameters[0] ?? 10;
            $scale = $parameters[1] ?? 2;
            $methodCall .= ", $precision, $scale";
        } elseif ($type === 'enum') {
            $values = array_map(fn ($val) => "'$val'", $parameters);
            $methodCall .= ", [" . implode(', ', $values) . "]";
        }

        // Close the method call
        $methodCall .= ')';

        return $methodCall;
    }
}

<?php

namespace LightWeight\Database\Migrations;

/**
 * Foreign key definition for fluent interface
 */
class ForeignKeyDefinition
{
    /**
     * The Blueprint instance
     */
    protected Blueprint $blueprint;
    
    /**
     * The columns
     */
    protected array $columns;
    
    /**
     * The referenced table
     */
    protected ?string $table = null;
    
    /**
     * The referenced columns
     */
    protected ?array $foreignColumns = null;
    
    /**
     * Create a new foreign key definition
     *
     * @param Blueprint $blueprint
     * @param array $columns
     */
    public function __construct(Blueprint $blueprint, array $columns)
    {
        $this->blueprint = $blueprint;
        $this->columns = $columns;
    }
    
    /**
     * Specify the referenced table
     *
     * @param string $table
     * @return $this
     */
    public function references($foreignColumns): self
    {
        $this->foreignColumns = is_array($foreignColumns) ? $foreignColumns : [$foreignColumns];
        
        return $this;
    }
    
    /**
     * Specify the referenced table
     *
     * @param string $table
     * @return $this
     */
    public function on(string $table): self
    {
        $this->table = $table;
        
        // Add the foreign key command to the blueprint
        $this->blueprint->addForeignKeyCommand($this->columns, $this->table, $this->foreignColumns ?? ['id']);
        
        return $this;
    }
}

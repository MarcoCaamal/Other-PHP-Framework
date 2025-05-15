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
     * The on delete action
     */
    protected ?string $onDelete = null;
    
    /**
     * The on update action
     */
    protected ?string $onUpdate = null;
    
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
     * Specify the action to take when the referenced row is deleted
     *
     * @param string $action (CASCADE, SET NULL, NO ACTION, RESTRICT, SET DEFAULT)
     * @return $this
     */
    public function onDelete(string $action): self
    {
        $this->onDelete = $this->validateReferentialAction($action);
        
        return $this;
    }
    
    /**
     * Specify the action to take when the referenced row is updated
     *
     * @param string $action (CASCADE, SET NULL, NO ACTION, RESTRICT, SET DEFAULT)
     * @return $this
     */
    public function onUpdate(string $action): self
    {
        $this->onUpdate = $this->validateReferentialAction($action);
        
        return $this;
    }
    
    /**
     * Validate that the referential action is valid
     *
     * @param string $action
     * @return string
     * @throws \InvalidArgumentException
     */
    protected function validateReferentialAction(string $action): string
    {
        // Normalizar la acción para la validación
        $normalizedAction = strtolower(trim($action));
        
        // Mapeo de acciones válidas y su formato correcto para SQL
        $validActions = [
            'cascade' => 'CASCADE',
            'set null' => 'SET NULL',
            'setnull' => 'SET NULL',
            'no action' => 'NO ACTION',
            'noaction' => 'NO ACTION',
            'restrict' => 'RESTRICT',
            'set default' => 'SET DEFAULT',
            'setdefault' => 'SET DEFAULT'
        ];
        
        // Verificar si la acción es válida
        if (!isset($validActions[$normalizedAction])) {
            throw new \InvalidArgumentException(
                "Invalid referential action: $action. Valid actions are: CASCADE, SET NULL, NO ACTION, RESTRICT, SET DEFAULT"
            );
        }
        
        // Devolver el formato correcto para SQL
        return $validActions[$normalizedAction];
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
        $this->blueprint->addForeignKeyCommand(
            $this->columns, 
            $this->table, 
            $this->foreignColumns ?? ['id'],
            $this->onDelete,
            $this->onUpdate
        );
        
        return $this;
    }
}

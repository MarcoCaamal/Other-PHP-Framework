<?php

namespace LightWeight\Database\ORM;

use JsonSerializable;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Exceptions\DatabaseException;
use LightWeight\Database\ORM\Relations\BelongsTo;
use LightWeight\Database\ORM\Relations\HasMany;
use LightWeight\Database\ORM\Relations\HasOne;
use LightWeight\Database\ORM\Relations\Relation;
use LightWeight\Database\QueryBuilder\Builder;
use LightWeight\Database\QueryBuilder\Contracts\QueryBuilderContract;
use LightWeight\Database\QueryBuilder\Exceptions\QueryBuilderException;

/**
 * Model Class
 *
 * @method static Builder<static> where(string $column, string $operator, mixed $value, string $boolean = 'AND')
 * @method static Builder<static> select(array $columns = ['*']);

 */
abstract class Model implements JsonSerializable
{
    /**
     *
     * @var ?string
     */
    protected ?string $table = null;
    protected string $primaryKey = 'id';
    protected array $hidden = [];
    protected array $fillable = [];
    protected array $attributes = [];
    protected bool $insertTimestamps = true;
    /**
     * Store loaded relationships
     * @var array<string, mixed>
     */
    protected array $relations = [];
    /**
     *
     * @var \LightWeight\Database\QueryBuilder\Metadata\Column[]|null
     */
    protected static ?array $columns = [];
    
    /**
     * Get the database driver from the container (transient, new instance)
     * 
     * @return DatabaseDriverContract
     * @throws DatabaseException If the database driver is not available
     */
    protected static function getDatabaseDriver(): DatabaseDriverContract
    {
        try {
            return app(DatabaseDriverContract::class);
        } catch (\Exception $e) {
            throw new DatabaseException("Database driver not available: {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * Get the query builder from the container (transient, new instance)
     * 
     * @return QueryBuilderContract
     * @throws DatabaseException If the query builder is not available
     */
    protected static function getQueryBuilder(): QueryBuilderContract
    {
        try {
            return make(QueryBuilderContract::class);
        } catch (\Exception $e) {
            throw new DatabaseException("Query builder not available: {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * @deprecated Use getDatabaseDriver() instead
     */
    public static function setDatabaseDriver(DatabaseDriverContract $driver)
    {
        // Esta función se mantiene solo para compatibilidad con código existente
        // y ahora no hace nada ya que obtenemos el driver del contenedor
    }
    
    /**
     * @deprecated Use getQueryBuilder() instead
     */
    public static function setBuilderDriver(QueryBuilderContract $builder)
    {
        // Esta función se mantiene solo para compatibilidad con código existente
        // y ahora no hace nada ya que obtenemos el builder del contenedor
    }
    public function __construct()
    {
        if(is_null($this->table)) {
            $subclass = new \ReflectionClass(static::class);
            $this->table = snakeCase("{$subclass->getShortName()}s");
        }
        
        try {
            $queryBuilder = static::getQueryBuilder();
            
            if(static::$columns === null) {
                $query = new Builder($queryBuilder, static::class);
                static::$columns = $query->getMetadataOfTableColumns();
            }
            
            // Initialize attributes with defaults from table schema
            foreach(static::$columns as $column) {
                // Only set defaults if the attribute doesn't already exist
                if (!isset($this->attributes[$column->name])) {
                    $this->attributes[$column->name] = $column->default;
                }
            }
        } catch (\Exception $e) {
            throw new DatabaseException("Error initializing model: {$e->getMessage()}", 0, $e);
        }
    }
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    public function __get($name)
    {
        // Check if the attribute exists
        if (isset($this->attributes[$name])) {
            return $this->attributes[$name];
        }
        
        // Check if the relation exists and has been loaded
        if (isset($this->relations[$name])) {
            return $this->relations[$name];
        }
        
        // Check if the relation method exists
        if (method_exists($this, $name)) {
            // Load the relation
            $relation = $this->$name();
            
            if ($relation instanceof Relation) {
                // Cache the relationship result
                return $this->relations[$name] = $relation->getResults();
            }
        }
        
        return null;
    }
    public function __sleep()
    {
        foreach ($this->hidden as $hide) {
            unset($this->attributes[$hide]);
        }
        return array_keys(get_object_vars($this));
    }
    public function __call($method, $args)
    {
        // Forward to query builder if needed
        $query = $this->newQuery();
        if (method_exists($query, $method)) {
            return $query->$method(...$args);
        }
        
        throw new \BadMethodCallException(sprintf(
            'Call to undefined method %s::%s()', static::class, $method
        ));
    }
    public static function __callStatic($method, $args)
    {
        try {
            $queryBuilder = static::getQueryBuilder();
            
            if (!method_exists($queryBuilder, $method)) {
                throw new QueryBuilderException("Method $method is not defined.");
            }

            $instance = new static();

            return (new Builder($queryBuilder, static::class))
                ->table($instance->getTable())
                ->{$method}(...$args);
        } catch (\Exception $e) {
            throw new QueryBuilderException("Error in static call: {$e->getMessage()}", 0, $e);
        }
    }
    public function getTable(): ?string
    {
        return $this->table;
    }
    public function getPrimaryKeyName(): string
    {
        return $this->primaryKey;
    }
    
    /**
     * Get the value of the model's primary key.
     *
     * @return mixed
     */
    public function getKey()
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }
    public function jsonSerialize(): mixed
    {
        // Create a copy of attributes and remove the hidden ones
        $newData = $this->attributes;
        foreach ($this->hidden as $hide) {
            unset($newData[$hide]);
        }
        
        return $newData;
    }
    public function fill(array $attributes): static
    {
        if (count($this->fillable) == 0) {
            throw new \Error("Model " . static::class . " does not have fillable attributes");
        }
        foreach ($attributes as $key => $value) {
            // Check if the key is in the fillable array or is a special field like primary key
            if (in_array($key, $this->fillable) || $key === $this->primaryKey || $key === 'created_at' || $key === 'updated_at') {
                $this->__set($key, $value);
            }
        }
        return $this;
    }
    public function toArray(): array
    {
        $array = array_filter(
            $this->attributes,
            fn ($value, $key) => !in_array($key, $this->hidden),
            ARRAY_FILTER_USE_BOTH
        );
        
        return $array;
    }
    
    /**
     * Set multiple attributes directly (for internal use)
     * 
     * @param array $attributes Attributes to set
     * @return $this
     */
    public function setRawAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }
    
    /**
     * Get all attributes of the model (for debugging)
     * 
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
    
    /**
     * Get column metadata (for debugging)
     * 
     * @return array
     */
    public static function getColumns(): array
    {
        return static::$columns ?? [];
    }
    public function save(): static
    {
        // Make a copy of the attributes before we modify them
        $attributesToSave = $this->attributes;
        
        // Process boolean values to ensure they are properly stored in MySQL
        foreach ($attributesToSave as $key => $value) {
            // Convert boolean values to integers for MySQL
            if (is_bool($value)) {
                $attributesToSave[$key] = $value ? 1 : 0;
            } elseif ($value === '') {
                // Check if this column is a boolean field in the schema
                $isBoolean = false;
                foreach(static::$columns as $column) {
                    if ($column->name === $key && ($column->type->name === 'tinyint' || $column->type->name === 'boolean')) {
                        $isBoolean = true;
                        break;
                    }
                }
                
                // If it's a boolean field, convert empty string to 0
                if ($isBoolean) {
                    $attributesToSave[$key] = 0;
                }
            }
        }
        
        // Only add timestamps if they don't exist already
        if ($this->insertTimestamps && !isset($attributesToSave["created_at"])) {
            $attributesToSave["created_at"] = date("Y-m-d H:i:s");
        }
        
        try {
            $queryBuilder = static::getQueryBuilder();
            $builder = new Builder($queryBuilder);
            $builder->table($this->table);
            
            // Use the copied attributes for the insert
            if ($builder->insert($attributesToSave)) {
                $this->{$this->primaryKey} = $builder->lastInsertId();
            }
            
            return $this;
        } catch (\Exception $e) {
            throw new DatabaseException("Error saving model: {$e->getMessage()}", 0, $e);
        }
    }
    
    public function update(): static
    {
        if ($this->insertTimestamps) {
            $this->attributes["updated_at"] = date("Y-m-d H:i:s");
        }
        
        $primaryKey = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryKey === null) {
            throw new \RuntimeException("Cannot update a model without a primary key value");
        }
        
        // Process boolean values for MySQL
        $attributesToUpdate = $this->attributes;
        foreach ($attributesToUpdate as $key => $value) {
            // Convert boolean values to integers for MySQL
            if (is_bool($value)) {
                $attributesToUpdate[$key] = $value ? 1 : 0;
            } elseif ($value === '') {
                // Check if this column is a boolean field in the schema
                $isBoolean = false;
                foreach(static::$columns as $column) {
                    if ($column->name === $key && ($column->type->name === 'tinyint' || $column->type->name === 'boolean')) {
                        $isBoolean = true;
                        break;
                    }
                }
                
                // If it's a boolean field, convert empty string to 0
                if ($isBoolean) {
                    $attributesToUpdate[$key] = 0;
                }
            }
        }
        
        try {
            $queryBuilder = static::getQueryBuilder();
            $builder = new Builder($queryBuilder);
            $builder->table($this->table)
                    ->where($this->primaryKey, '=', $primaryKey)
                    ->update($attributesToUpdate);
            
            return $this;
        } catch (\Exception $e) {
            throw new DatabaseException("Error updating model: {$e->getMessage()}", 0, $e);
        }
    }
    
    public function delete(): static
    {
        $primaryKey = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryKey === null) {
            throw new \RuntimeException("Cannot delete a model without a primary key value");
        }
        
        try {
            $queryBuilder = static::getQueryBuilder();
            $builder = new Builder($queryBuilder);
            $builder->table($this->table)
                    ->where($this->primaryKey, '=', $primaryKey)
                    ->delete();
            
            return $this;
        } catch (\Exception $e) {
            throw new DatabaseException("Error deleting model: {$e->getMessage()}", 0, $e);
        }
    }
    public static function create(array $attributes): static
    {
        return (new static())->fill($attributes)->save();
    }
    /**
     *
     * @param string|int $id
     * @return static|null
     */
    public static function find(string|int $id): ?static
    {
        try {
            $queryBuilder = static::getQueryBuilder();
            $query = new Builder($queryBuilder, static::class);
            $instance = new static();
            return $query
                ->table($instance->getTable())
                ->where($instance->getPrimaryKeyName(), '=', $id)
                ->first();
        } catch (\Exception $e) {
            throw new DatabaseException("Error finding model: {$e->getMessage()}", 0, $e);
        }
    }
    /**
     * Return all models
     * @return static[]
     */
    public static function all(): array
    {
        try {
            $queryBuilder = static::getQueryBuilder();
            $query = new Builder($queryBuilder, static::class);
            $instance = new static();
            return $query
                ->table($instance->getTable())
                ->get();
        } catch (\Exception $e) {
            throw new DatabaseException("Error retrieving all models: {$e->getMessage()}", 0, $e);
        }
    }
    /**
     * Create a new instance of the Builder for this model
     * 
     * @return Builder<static>
     */
    public static function query(): Builder
    {
        try {
            $queryBuilder = static::getQueryBuilder();
            $instance = new static();
            return (new Builder($queryBuilder, static::class))
                ->table($instance->getTable());
        } catch (\Exception $e) {
            throw new QueryBuilderException("Error creating query: {$e->getMessage()}", 0, $e);
        }
    }
    
    /**
     * Create a new query for the Model
     * 
     * @return Builder<static>
     */
    protected function newQuery(): Builder
    {
        try {
            $queryBuilder = static::getQueryBuilder();
            return (new Builder($queryBuilder, static::class))
                ->table($this->getTable());
        } catch (\Exception $e) {
            throw new QueryBuilderException("Error creating new query: {$e->getMessage()}", 0, $e);
        }
    }

    /**
     * Define a one-to-one relationship.
     *
     * @template TRelatedModel of Model
     * @param class-string<TRelatedModel> $related Related model class name
     * @param string|null $foreignKey Foreign key on the related model
     * @param string|null $localKey Local key on the parent model
     * @return HasOne<TRelatedModel>
     */
    public function hasOne(string $related, ?string $foreignKey = null, ?string $localKey = null): HasOne
    {
        // Create a new instance of the related model
        $instance = new $related;
        
        // Determine the foreign key
        if (is_null($foreignKey)) {
            // Get the "snake case" version of the calling model's class name + _id
            $reflectionClass = new \ReflectionClass(static::class);
            // Remove "Model" suffix if present when creating the foreign key
            $className = $reflectionClass->getShortName();
            $className = preg_replace('/Model$/', '', $className);
            $foreignKey = snakeCase($className) . '_id';
        }
        
        // Determine the local key (default is primary key of this model)
        if (is_null($localKey)) {
            $localKey = $this->getPrimaryKeyName();
        }
        
        // Create a query builder for the related model
        $query = $instance->query();
        
        // Return a new HasOne relation
        return new HasOne($query, $this, $foreignKey, $localKey);
    }

    /**
     * Define a one-to-many relationship.
     *
     * @template TRelatedModel of Model
     * @param class-string<TRelatedModel> $related Related model class name
     * @param string|null $foreignKey Foreign key on the related model
     * @param string|null $localKey Local key on the parent model
     * @return HasMany<TRelatedModel>
     */
    public function hasMany(string $related, ?string $foreignKey = null, ?string $localKey = null): HasMany
    {
        // Create a new instance of the related model
        $instance = new $related;
        
        // Determine the foreign key
        if (is_null($foreignKey)) {
            // Get the "snake case" version of the calling model's class name + _id
            $reflectionClass = new \ReflectionClass(static::class);
            // Remove "Model" suffix if present when creating the foreign key
            $className = $reflectionClass->getShortName();
            $className = preg_replace('/Model$/', '', $className);
            $foreignKey = snakeCase($className) . '_id';
        }
        
        // Determine the local key (default is primary key of this model)
        if (is_null($localKey)) {
            $localKey = $this->getPrimaryKeyName();
        }
        
        // Create a query builder for the related model
        $query = $instance->query();
        
        // Return a new HasMany relation
        return new HasMany($query, $this, $foreignKey, $localKey);
    }

    /**
     * Define an inverse one-to-one or many relationship.
     *
     * @template TRelatedModel of Model
     * @param class-string<TRelatedModel> $related Related model class name
     * @param string|null $foreignKey Foreign key on this model
     * @param string|null $ownerKey Primary key on the related model
     * @return BelongsTo<TRelatedModel>
     */
    public function belongsTo(string $related, ?string $foreignKey = null, ?string $ownerKey = null): BelongsTo
    {
        // Create a new instance of the related model
        $instance = new $related;
        
        // Determine the foreign key
        if (is_null($foreignKey)) {
            // Get the "snake case" version of the related model's class name + _id
            $reflectionClass = new \ReflectionClass($related);
            // Remove "Model" suffix if present when creating the foreign key
            $className = $reflectionClass->getShortName();
            $className = preg_replace('/Model$/', '', $className);
            $foreignKey = snakeCase($className) . '_id';
        }
        
        // Determine the owner key (default is primary key of the related model)
        if (is_null($ownerKey)) {
            $ownerKey = $instance->getPrimaryKeyName();
        }
        
        // Create a query builder for the related model
        $query = $instance->query();
        
        // Return a new BelongsTo relation
        return new BelongsTo($query, $this, $foreignKey, $ownerKey);
    }

    /**
     * Get relationship method from the dynamic method
     * 
     * @param string $method Method name
     * @return mixed The relationship if it exists, null otherwise
     */
    public function getRelationValue(string $method)
    {
        // Check if the relation already exists in the relations array
        if (array_key_exists($method, $this->relations)) {
            return $this->relations[$method];
        }

        // Check if a relationship method exists
        if (method_exists($this, $method)) {
            $relation = $this->$method();
            
            if ($relation instanceof Relation) {
                // Get and cache the relationship value
                return $this->relations[$method] = $relation->getResults();
            }
        }
        
        return null;
    }
    
    /**
     * Load a relationship by name
     * 
     * @param string $name Name of the relationship method
     * @return $this
     */
    public function load(string $name): static 
    {
        if (method_exists($this, $name)) {
            $relation = $this->$name();
            
            if ($relation instanceof Relation) {
                $this->relations[$name] = $relation->getResults();
            }
        }
        
        return $this;
    }
}

<?php

namespace LightWeight\Database\ORM;

use JsonSerializable;
use LightWeight\Database\Contracts\DatabaseDriverContract;
use LightWeight\Database\Exceptions\DatabaseException;
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
     *
     * @var \LightWeight\Database\QueryBuilder\Metadata\Column[]|null
     */
    protected static ?array $columns = [];
    private static ?DatabaseDriverContract $driver = null;
    private static ?QueryBuilderContract $builder = null;
    

    
    public static function setDatabaseDriver(DatabaseDriverContract $driver)
    {
        self::$driver = $driver;
    }
    public static function setBuilderDriver(QueryBuilderContract $builder)
    {
        self::$builder = $builder;
    }
    public function __construct()
    {
        if(is_null($this->table)) {
            $subclass = new \ReflectionClass(static::class);
            $this->table = snakeCase("{$subclass->getShortName()}s");
        }
        if(self::$builder === null) {
            throw new DatabaseException("Builder must be QueryBuilderContract");
        }
        if(static::$columns === null) {
            $query = new Builder(self::$builder, static::class);
            static::$columns = $query->getMetadataOfTableColumns();
        }
        // Initialize attributes with defaults from table schema
        foreach(static::$columns as $column) {
            // Only set defaults if the attribute doesn't already exist
            if (!isset($this->attributes[$column->name])) {
                $this->attributes[$column->name] = $column->default;
            }
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
        if (!method_exists(self::$builder, $method)) {
            throw new QueryBuilderException("Method $method is not defined.");
        }

        $instance = new static();

        return (new Builder(self::$builder, static::class))
            ->table($instance->getTable())
            ->{$method}(...$args);
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
        
        if (self::$builder === null) {
            throw new QueryBuilderException("QueryBuilder is not initialized");
        }
        
        $builder = new Builder(self::$builder);
        $builder->table($this->table);
        
        // Use the copied attributes for the insert
        if ($builder->insert($attributesToSave)) {
            $this->{$this->primaryKey} = $builder->lastInsertId();
        }
        
        return $this;
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
        
        if (self::$builder === null) {
            throw new QueryBuilderException("QueryBuilder is not initialized");
        }
        
        $builder = new Builder(self::$builder);
        $builder->table($this->table)
                ->where($this->primaryKey, '=', $primaryKey)
                ->update($attributesToUpdate);
        
        return $this;
    }
    
    public function delete(): static
    {
        $primaryKey = $this->attributes[$this->primaryKey] ?? null;
        if ($primaryKey === null) {
            throw new \RuntimeException("Cannot delete a model without a primary key value");
        }
        
        if (self::$builder === null) {
            throw new QueryBuilderException("QueryBuilder is not initialized");
        }
        
        $builder = new Builder(self::$builder);
        $builder->table($this->table)
                ->where($this->primaryKey, '=', $primaryKey)
                ->delete();
        
        return $this;
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
        $query = new Builder(self::$builder, static::class);
        $instance = new static();
        return $query
            ->table($instance->getTable())
            ->where($instance->getPrimaryKeyName(), '=', $id)
            ->first();
    }
    /**
     * Return all models
     * @return static[]
     */
    public static function all(): array
    {
        $query = new Builder(self::$builder, static::class);
        $instance = new static();
        return $query
            ->table($instance->getTable())
            ->get();
    }
    /**
     * Create a new instance of the Builder for this model
     * 
     * @return Builder<static>
     */
    public static function query(): Builder
    {
        if(self::$builder === null) {
            throw new QueryBuilderException("Not provided QueryBuilderContract.");
        }
        $instance = new static();
        return (new Builder(self::$builder, static::class))
            ->table($instance->getTable());
    }
    

}

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
        foreach(static::$columns as $column) {
            $this->attributes[$column->name] = $column->default;
        }
    }
    public function __set($name, $value)
    {
        $this->attributes[$name] = $value;
    }
    public function __get($name)
    {
        return $this->attributes[$name] ?? null;
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

    }
    public static function __callStatic($method, $args)
    {
        if (!method_exists(self::$builder, $method)) {
            throw new QueryBuilderException("Method $method is not defined.");
        }

        $instance = new static();

        return (new Builder(static::$builder, static::class))
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
    public function jsonSerialize(): mixed
    {
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
            if (in_array($key, $this->fillable)) {
                $this->__set($key, $value);
            }
        }
        return $this;
    }
    public function toArray(): array
    {
        return array_filter(
            $this->attributes,
            fn ($attr) => !in_array($attr, $this->hidden)
        );
    }
    public function save(): static
    {
        if ($this->insertTimestamps) {
            $this->attributes["created_at"] = date("Y-m-d H:m:s");
        }
        $databaseColumns = implode(",", array_keys($this->attributes));
        $bind = implode(",", array_fill(0, count($this->attributes), "?"));
        self::$driver->statement(
            "INSERT INTO $this->table ($databaseColumns) VALUES ($bind)",
            array_values($this->attributes)
        );
        $this->{$this->primaryKey} = self::$driver->lastInsertId();
        return $this;
    }
    public function update(): static
    {
        if ($this->insertTimestamps) {
            $this->attributes["updated_at"] = date("Y-m-d H:m:s");
        }
        $databaseColumns = array_keys($this->attributes);
        $bind = implode(",", array_map(fn ($column) => "$column = ?", $databaseColumns));
        $id = $this->attributes[$this->primaryKey];
        self::$driver->statement(
            "UPDATE $this->table SET $bind WHERE $this->primaryKey = $id",
            array_values($this->attributes)
        );
        return $this;
    }
    public function delete(): static
    {
        self::$driver->statement(
            "DELETE FROM $this->table WHERE $this->primaryKey = {$this->attributes[$this->primaryKey]}"
        );
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

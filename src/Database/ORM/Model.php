<?php

namespace OtherPHPFramework\Database\ORM;

use OtherPHPFramework\Database\DatabaseDriverContract;

abstract class Model
{
    protected ?string $table = null;
    protected string $primaryKey = 'id';
    protected array $hidden = [];
    protected array $fillable = [];
    protected array $attributes = [];
    private static ?DatabaseDriverContract $driver = null;

    public static function setDatabaseDriver(DatabaseDriverContract $driver)
    {
        self::$driver = $driver;
    }

    public function __construct()
    {
        if(is_null($this->table)) {
            $subclass = new \ReflectionClass(static::class);
            $this->table = snakeCase("{$subclass->getShortName()}s");
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
    public function save()
    {
        $databaseColumns = implode(",", array_keys($this->attributes));
        $bind = implode(",", array_fill(0, count($this->attributes), "?"));
        self::$driver->statement(
            "INSERT INTO $this->table ($databaseColumns) VALUES ($bind)",
            array_values($this->attributes)
        );
    }
}

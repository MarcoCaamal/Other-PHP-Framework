<?php

namespace SMFramework\Database;

use SMFramework\Database\Contracts\DatabaseDriverContract;

class DB
{
    public static DatabaseDriverContract $driver;
    public static function setDatabaseDriver(DatabaseDriverContract $driver)
    {
        self::$driver = $driver;
    }
    public static function statement(string $query, array $bind = [])
    {
        return self::$driver->statement($query, $bind);
    }
}

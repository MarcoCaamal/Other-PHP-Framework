<?php

namespace SMFramework\Database;

class DB
{
    public static function statement(string $query, array $bind = [])
    {
        return app(DatabaseDriverContract::class)->statement($query, $bind);
    }
}

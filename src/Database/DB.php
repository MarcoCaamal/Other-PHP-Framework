<?php

namespace LightWeight\Database;

use LightWeight\Database\Contracts\DatabaseDriverContract;

class DB
{
    public static function statement(string $query, array $bind = [])
    {
        return app(DatabaseDriverContract::class)->statement($query, $bind);
    }
}

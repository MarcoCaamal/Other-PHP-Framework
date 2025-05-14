<?php

namespace LightWeight\Database\Migrations;

use LightWeight\Database\DB;

/**
 * Schema builder helper for migrations
 */
class Schema
{
    /**
     * Create a new table
     *
     * @param string $table Table name
     * @param callable $callback Function that receives a Blueprint instance
     * @return void
     */
    public static function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);
        
        // Execute the blueprint
        DB::statement($blueprint->toSql());
    }
    
    /**
     * Rename a table
     *
     * @param string $from Current table name
     * @param string $to New table name
     * @return void
     */
    public static function rename(string $from, string $to): void
    {
        DB::statement("RENAME TABLE `$from` TO `$to`");
    }
    
    /**
     * Drop a table if it exists
     *
     * @param string $table Table name
     * @return void
     */
    public static function dropIfExists(string $table): void
    {
        DB::statement("DROP TABLE IF EXISTS $table");
    }

    /**
     * Drop a table
     *
     * @param string $table Table name
     * @return void
     */
    public static function drop(string $table): void
    {
        DB::statement("DROP TABLE $table");
    }
    
    /**
     * Alter an existing table
     *
     * @param string $table Table name
     * @param callable $callback Function that receives a Blueprint instance
     * @return void
     */
    public static function table(string $table, callable $callback): void
    {
        $blueprint = new Blueprint($table, 'alter');
        $callback($blueprint);
        
        // Execute the blueprint
        if ($blueprint->hasCommands()) {
            DB::statement($blueprint->toSql());
        }
    }
    
}

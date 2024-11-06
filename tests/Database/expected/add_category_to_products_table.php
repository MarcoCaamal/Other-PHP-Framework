<?php

use LightWeight\Database\DB;
use LightWeight\Database\Migrations\Contracts\MigrationContract;

return new class () implements MigrationContract {
    public function up()
    {
        DB::statement("ALTER TABLE products");
    }
    public function down()
    {
        DB::statement("ALTER TABLE products");
    }
};

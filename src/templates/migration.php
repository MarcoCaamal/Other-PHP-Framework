<?php

use LightWeight\Database\Migrations\Contracts\MigrationContract;
use LightWeight\Database\DB;

return new class () implements MigrationContract {
    public function up()
    {
        DB::statement("\$UP");
    }
    public function down()
    {
        DB::statement("\$DOWN");
    }
};
